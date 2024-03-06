<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron_model extends MY_Model {
    public $db_user;
    public $db_fantasy;
    public function __construct() 
    {
     	parent::__construct();
  		$this->db_user		= $this->load->database('db_user', TRUE);
  		$this->db_fantasy	= $this->load->database('db_fantasy', TRUE);
    }

    public function delete_bucket_banner_data($sports_id){
        $current_date = format_date();
        $sql = $this->db_user->select('BM.banner_id,BM.banner_unique_id,BM.banner_type_id,BM.name,BM.scheduled_date', FALSE)
          ->from(BANNER_MANAGEMENT . ' as BM')
          ->where("BM.is_deleted","0")
          ->where("BM.banner_type_id","1")
          ->where("BM.status","1")
          ->where("BM.scheduled_date IS NOT NULL")
          ->having("BM.scheduled_date <= ",$current_date);
        $result_record = $this->db_user->get()->result_array();
        if(!empty($result_record)){
            $banner_ids = array_column($result_record, "banner_id");
            if(!empty($banner_ids)){
                $this->db_user->where_in('banner_id', $banner_ids);
                $this->db_user->update(BANNER_MANAGEMENT, array('status' => '0'));
                $languages = $this->config->item('language_list');
                foreach($languages as $lang_abbr => $lang_value)
                {
                    //for delete s3 bucket file
                    $this->delete_cache_data('lobby_banner_list_'.$sports_id.'_'.$lang_abbr);
                    $this->delete_s3_bucket_file("lobby_banner_list_".$sports_id.'_'.$lang_abbr.'.json');                    
                }
                echo "done";
            }
        }
    }

    public function get_email_template_list(){

        $result = $this->db_user->select("template_name,template_path,notification_type,status,subject")
                    ->from(EMAIL_TEMPLATE)
                    ->where("status",1)
                    ->get()
                    ->result_array();

        return $result;
    }

    public function auto_recurring_contest($contest_unique_id)
    {
        if(!$contest_unique_id){
            return false;
        }

        $contest_data = $this->db_fantasy->select("*")
                    ->from(CONTEST)
                    ->where("contest_unique_id",$contest_unique_id)
                    ->get()
                    ->row_array();
        if(!empty($contest_data)){
            $game_data = $contest_data;
            $game_data['contest_unique_id'] = random_string('alnum', 9);
            $game_data['total_user_joined'] = 0;
            $game_data['total_system_user'] = 0;
            $prize_details = json_decode($contest_data['base_prize_details']);
            $game_data['prize_pool'] = $prize_details->prize_pool;
            $game_data['prize_distibution_detail'] = $prize_details->prize_distibution_detail;
            $game_data['salary_cap'] = SALARY_CAP;
            $game_data['added_date'] = format_date();
            $game_data['modified_date'] = format_date();
            unset($game_data["contest_id"]);
            unset($game_data["is_cancel"]);
            $this->db_fantasy->insert(CONTEST, $game_data);
        }
        return true;
    }

    function refund_cash($input_data) {
        $this->db = $this->db_user;
        // check for already refunded.
        $refundDetail = $this->get_order_by_source_id($input_data["source_id"], 2, $input_data["plateform"],$input_data["season_type"]);
        if ($refundDetail) {
            return false;
        }

        //get order details for source = 1-JoinGame
        $orderDetail = $this->get_order_by_source_id($input_data["source_id"], 1, $input_data["plateform"],$input_data["season_type"]);
        if (!$orderDetail) {
            return false;
        }

        $user_balance = $this->get_user_balance($orderDetail["user_id"]);
        $current_date = format_date(); 
        $orderData["user_id"]        = $orderDetail["user_id"];
        $orderData["reference_id"]   = isset($orderDetail["reference_id"]) ? $orderDetail["reference_id"] : 0;
        /* For Cancel game source    = 2 */
        $orderData["source"]         = 2;
        $orderData["source_id"]      = $orderDetail["source_id"];
        $orderData["season_type"]    = $orderDetail["season_type"];
        /* type                      = 0 For creadit amount */
        $orderData["type"]           = 0;
        $orderData["date_added"]     = $current_date;
        $orderData["modified_date"]  = $current_date;
        $orderData["plateform"]      = $orderDetail["plateform"];
        $orderData["status"]         = 1;
        $orderData["real_amount"]    = $orderDetail["real_amount"];
        $orderData["bonus_amount"]   = $orderDetail["bonus_amount"];
        $orderData["winning_amount"] = $orderDetail["winning_amount"];
        $orderData["points"]         = $orderDetail["points"];
        $orderData['order_unique_id'] = $this->_generate_order_key();
        
        $this->db_user->insert(ORDER, $orderData);
        $order_id = $this->db_user->insert_id();
        if (!$order_id) {
            return false;
        }

        $real_bal    = $user_balance['real_amount'] + $orderData["real_amount"];
        $bonus_bal   = $user_balance['bonus_amount'] + $orderData["bonus_amount"];
        $winning_bal = $user_balance['winning_amount'] + $orderData["winning_amount"];
        $point_balance = $user_balance['point_balance'] + $orderData["points"];

        $this->update_user_balance($orderData["user_id"], $orderData, "add");

        // Refund cash notification
        $tmp = array(); 
        $user_detail = $this->get_single_row('email, user_name', USER, array("user_id"=>$orderDetail["user_id"]));
  
        $tmp["notification_type"]        = 6; // 6-Deposit
       
        $input_data['amount']            = $orderData["real_amount"] + $orderData["bonus_amount"] + $orderData["winning_amount"];
        $tmp["source_id"]                = $input_data["source_id"];
        
        if($input_data["source"] == 2) {
            $tmp["notification_destination"] = 4; //  Web, Push, Email
        } else {
            $tmp["notification_destination"] = 7; //  Web, Push, Email
        }
        
        $tmp["user_id"]                  = $orderDetail["user_id"];
        $tmp["to"]                       = $user_detail['email'];
        $tmp["user_name"]                = $user_detail['user_name'];
        $tmp["added_date"]               = $current_date;
        $tmp["modified_date"]            = $current_date;
        $tmp["content"]                  = json_encode($input_data);
        $tmp["subject"]                  = "Amount deposited.";

        if($input_data['source'] != 2) {
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($tmp);
        }
        return true;
    }

    function get_order_by_source_id($source_id,$source,$plateform,$season_type)
    {
        $condition = array(
                                "source_id"     =>$source_id,
                                "source"        =>$source,
                                "season_type"   => $season_type,
                                "plateform"     =>$plateform
                            );
        return $this->db_user->where($condition)
            ->get(ORDER,1)
            ->row_array();
    }
    
    /**  Used to get user balance 
     * @param int $user_id
     * @return array
     */
    public function get_user_balance($user_id)
    {
        $result =   $this->db_user->select("user_id,balance as real_amount, bonus_balance as bonus_amount, winning_balance as winning_amount,point_balance")
                                    ->where(array("user_id" => $user_id))
                                    ->limit(1)
                                    ->get(USER)
                                    ->row_array();
        return array(
                        "bonus_amount"   => $result["bonus_amount"]?$result["bonus_amount"]:0,
                        "real_amount"    => $result["real_amount"]?$result["real_amount"]:0,
                        "winning_amount" => $result["winning_amount"]?$result["winning_amount"]:0,
                        "point_balance"  => $result["point_balance"]?$result["point_balance"]:0
                    );
    }

    /**
     * Used for generate order unique id
     * @return string
     */
    public function _generate_order_key() {
        $this->load->helper('security');
        $salt = do_hash(time() . mt_rand());
        $new_key = substr($salt, 0, 20);
        return $new_key;
    }

    /**
     * Used for check order unique id exist or not
     * @param string $key
     * @return int
     */
    private function _order_key_exists($key) {
        $this->db_user->select('order_id');
        $this->db_user->where('order_unique_id', $key);
        $this->db_user->limit(1);
        $query = $this->db_user->get(ORDER);
        $num = $query->num_rows();
        if ($num > 0) {
            return true;
        }
        return false;
    }

    /**
     * Function to Update user balance
     *  Params: $user_id,$real_balance,$bonus_balance
     *  
     */
    function update_user_balance($user_id,$balance_arr,$oprator='add')
    {
        if(empty($balance_arr)){
            return false;
        }
        if(isset($balance_arr['real_amount']) && $balance_arr['real_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('balance', 'balance - '.$balance_arr['real_amount'], FALSE);
            }else{
                $this->db_user->set('balance', 'balance + '.$balance_arr['real_amount'], FALSE);
            }
            if(isset($balance_arr['source']) && $balance_arr['source'] == "7" && $oprator == 'add'){
                $this->db->set('total_deposit', 'total_deposit + '.$balance_arr['real_amount'], FALSE);
            }
        }
        if(isset($balance_arr['bonus_amount']) && $balance_arr['bonus_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('bonus_balance', 'bonus_balance - '.$balance_arr['bonus_amount'], FALSE);
            }else{
                $this->db_user->set('bonus_balance', 'bonus_balance + '.$balance_arr['bonus_amount'], FALSE);
            }

            $this->load->helper('queue_helper');
            $bonus_data = array('oprator' => $oprator, 'user_id' => $user_id, 'total_bonus' => $balance_arr['bonus_amount'], 'bonus_date' => format_date("today", "Y-m-d"));
            add_data_in_queue($bonus_data, 'user_bonus');
        }
        if(isset($balance_arr['winning_amount']) && $balance_arr['winning_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('winning_balance', 'winning_balance - '.$balance_arr['winning_amount'], FALSE);
            }else{
                $this->db_user->set('winning_balance', 'winning_balance + '.$balance_arr['winning_amount'], FALSE);
            }
            if(isset($balance_arr['source']) && $balance_arr['source'] == "3" && $oprator == 'add'){
                $this->db_user->set('total_winning', 'total_winning + '.$balance_arr['winning_amount'], FALSE);
            }
        }
        if(isset($balance_arr['points']) && $balance_arr['points'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('point_balance', 'point_balance - '.$balance_arr['points'], FALSE);
            }else{
                $this->db_user->set('point_balance', 'point_balance + '.$balance_arr['points'], FALSE);
            }
            $this->load->helper('queue_helper');
            $coin_data = array('oprator' => $oprator, 'user_id' => $user_id, 'total_coins' => $balance_arr['points'], 'bonus_date' => format_date("today", "Y-m-d"));
            add_data_in_queue($coin_data, 'user_coins');
        }

        //for gst bonus amount update
        if(isset($balance_arr['cb_amount']) && $balance_arr['cb_amount'] > 0){
          if($oprator == "withdraw"){
            $this->db_user->set('cb_balance', 'cb_balance - '.$balance_arr['cb_amount'], FALSE);
          }else{
            $this->db_user->set('cb_balance', 'cb_balance + '.$balance_arr['cb_amount'], FALSE);
          }
        }

        $this->db_user->where('user_id', $user_id);
        $this->db_user->update(USER);
        
        return $this->db_user->affected_rows();  
    }

    function get_user_id_by_lineup_id($lineup_master_id)
    {
        $lineup_data = FALSE;
        $lineup_master_data = $this->db_fantasy->select('L.user_id')
                        ->from(LINEUP_MASTER." AS L")
                        ->where('L.lineup_master_id', $lineup_master_id)
                        ->get()
                        ->row_array();
        if($lineup_master_data)
        {
            $user_id = $lineup_master_data['user_id'];
            $lineup_data =  $this->db_user->select('user_id,email,user_name')
                            ->from(USER." AS U")
                            ->where('user_id', $user_id)
                            ->get()
                            ->row_array();
        }
        return $lineup_data;
    }
    
    public function withdraw($input_data) 
    { 
        $this->db = $this->db_user;
        $amount                       = $input_data["amount"];
        $orderData                    = array();
        $orderData["user_id"]         = $input_data["user_id"];
        $orderData["source"]          = $input_data["source"];
        $orderData["source_id"]       = $input_data["source_id"];
        $orderData["type"]            = 1;
        $orderData["date_added"]      = format_date();
        $orderData["modified_date"]   = format_date();
        $orderData["plateform"]       = $input_data["plateform"];
        $orderData["status"]          = 0;
        $orderData["real_amount"]     = 0;
        $orderData["bonus_amount"]    = 0;
        $orderData["winning_amount"]  = 0;
        $orderData["withdraw_method"] = isset($input_data["withdraw_method"]) ? $input_data["withdraw_method"]:0;
        $orderData["reason"] = !empty($input_data["reason"]) ? $input_data["reason"] : '';

        if(!empty($input_data['email']))
        {
            $orderData["email"] = $input_data['email']; 
        }

        $user_balance = $this->get_user_balance($orderData["user_id"]);
        // If requested amount is greater then total amount.
        if ($user_balance["real_amount"] + $user_balance["bonus_amount"] +  $user_balance["winning_amount"] < $amount) 
        {
            return false;
        }

        switch ($input_data["cash_type"]) 
        {
            case 0:
                if ($input_data["source"] == 11 && $user_balance["winning_amount"] < $amount) {
                    return false;
                }else if ($input_data["source"] != 8 && $input_data["source"] != 11 && $user_balance["real_amount"] < $amount) {
                    return false;
                }
                if ($input_data["source"] == 11){
                    $orderData["winning_amount"] = $amount;
                }else{
                    $orderData["real_amount"] = $amount;
                }
                break;
            case 1:
                if ($input_data["source"] != 8 && $user_balance["bonus_amount"] < $amount) 
                {
                    return false;
                }
                $orderData["bonus_amount"] = $amount;
                break;
            case 2:
                // Use both cash (bouns+real) only for join game. 
                $max_bouns = ($amount * MAX_BONUS_PERCEN_USE)/100;

                if ($orderData["source"] == 1) 
                {
                    // Deduct Max 10% of entry fee from bonus amount.
                    $orderData["bonus_amount"] = $max_bouns;
                    if($max_bouns>$user_balance["bonus_amount"])
                    {
                        $orderData["bonus_amount"] = $user_balance["bonus_amount"];
                    }
                    $remain_amt = $amount-$orderData["bonus_amount"];
                    // Deduct reamining amount from real amount.
                    $orderData["real_amount"] = $remain_amt;

                    if ($remain_amt > $user_balance["real_amount"]) 
                    {
                         $orderData["real_amount"] = $user_balance["real_amount"];
                    }
                    
                    $remain_amt =  $remain_amt - $orderData["real_amount"];
                    if($remain_amt > $user_balance["winning_amount"])
                    {
                        return false;                       
                    }
                    // Deduct Remaining amount from winning amount
                    $orderData["winning_amount"] =  $remain_amt;
                }
                break;
        }

        switch ($input_data["source"]) 
        {
            // admin
            case 0:
            // JoinGame
            case 1:
            // TDS on winning
            case 11:
            // BonusExpired
            case 5:
                $orderData["status"] = 1;
                break;
            // 8-Withdraw [ User can withdraw cash only from winning amount. ]  
           case 8:
                $orderData["real_amount"] = 0;
                $orderData["bonus_amount"] = 0;
                $orderData["winning_amount"] = $amount;                
                if($amount > $user_balance["winning_amount"])
                {
                    return false;
                }
                break;
        }
        $orderData['order_unique_id'] = $this->_generate_order_key();
        $this->db_user->insert(ORDER, $orderData);
        $order_id = $this->db_user->insert_id();
        if (!$order_id) {
            return false;
        }

        $real_bal    = $user_balance['real_amount'] - $orderData["real_amount"];
        $bonus_bal   = $user_balance['bonus_amount'] - $orderData["bonus_amount"];
        $winning_bal = $user_balance['winning_amount'] - $orderData["winning_amount"];
        $this->update_user_balance($orderData["user_id"], $orderData, "withdraw");
        // Add notification
        $tmp = array();
        $user_detail = $this->get_single_row('email, user_name', USER, array("user_id"=>$input_data["user_id"]));

        if($input_data["source"] !=1 && $input_data["source"] !=21)
        {   
            $subject = "Amount withdrawal";
            $input_data["reason"] = CRON_WITHDRAWL_NOTI1;
            $tmp["notification_destination"] = 7; //  Web, Push, Email
            $tmp["notification_type"] = 7; // 7-Withdraw
            if($input_data["source"] == 11)
            {
                $tmp["notification_destination"] = 1;
                $tmp["notification_type"] = 130;
                $subject = "TDS Deducted";
            }

            $tmp["source_id"]                = $input_data["source_id"];
            $tmp["user_id"]                  = $input_data["user_id"];
            $tmp["to"]                       = $user_detail['email'];
            $tmp["user_name"]                = $user_detail['user_name'];
            $tmp["added_date"]               = date("Y-m-d H:i:s");
            $tmp["modified_date"]            = date("Y-m-d H:i:s");
            $tmp["content"]                  = json_encode($input_data);
            $tmp["subject"]                  = $subject;
            $source = $input_data["source"];
            if($source != 7 && empty($input_data['ignore_deposit_noty']) )
            {
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp);
            }
        }
       return array(
            'transaction_id' => $order_id,
            'order_id'       => $order_id,
            'bonus_balance'  => $bonus_bal,
            'balance'        => $real_bal);
    }
    
    public function add_cash_contest_referral_bonus($contest_id)
    {
        //get contest basic details
        $query = $this->db_fantasy->select('entry_fee,contest_id,contest_unique_id,max_bonus_allowed')
                                ->where("contest_id",$contest_id)
                                ->where("user_id","0")
                                ->where("max_bonus_allowed < ","100")
                                ->get(CONTEST);
        $data = $query->row_array();
        
        if(!$data)
        {
            return TRUE;
        }
        $entry_fee = $data['entry_fee'];
        $contest_id = $data['contest_id'];
        // $bonus_given = ($percentage_of_entry_fee/100*$entry_fee);

        //if contest not a cash leageue then return.    
        if($entry_fee<=0)
        {
            return TRUE;
        }

        //get total joined users for this contest.
        $sql = "SELECT `user_id` FROM ".$this->db_fantasy->dbprefix(LINEUP_MASTER_CONTEST)." AS `LMC` INNER JOIN ".$this->db_fantasy->dbprefix(LINEUP_MASTER)." AS `LM` ON `LM`.`lineup_master_id` = `LMC`.`lineup_master_id` WHERE `contest_id` = '$contest_id'";
        $query = $this->db_fantasy->query($sql);
        $users = $query->result_array();
        if(empty($users))
        {
            return TRUE;
        }    


        $user_id_arr = array_column($users, 'user_id');

        //get all valid affiliate users and their details.
        $user_tbl = $this->db_user->dbprefix(USER);
        $query = $this->db_user->select('U1.email AS user_email,CONCAT(U1.first_name," ",U1.last_name) as name,U1.user_name as username1,CONCAT(U2.first_name," ",U2.last_name) as friendname, U2.user_name as username2, U2.email AS friend_email, UAH.user_id, UAH.friend_id, UAH.user_affiliate_history_id,UAH.source_type,U1.phone_no user_phone,U2.phone_no as friend_phone', False)
                                ->from(USER_AFFILIATE_HISTORY.' AS UAH')
                                ->join($user_tbl.' AS U1', 'U1.user_id = UAH.user_id', 'INNER', FALSE)
                                ->join($user_tbl.' AS U2', 'U2.user_id = UAH.friend_id', 'INNER', FALSE)
                                ->where_in('UAH.friend_id', $user_id_arr)
                                // ->where('user_bonus_cash <', $bonus_cash_limit)
                                ->where('UAH.status', '1')
                                ->where('UAH.affiliate_type', 1)
                                ->get();
        $refferal_data = $query->result_array();
        if(empty($refferal_data))
        {
            return TRUE;
        }    

       // echo '<pre>';print_r($refferal_data);die;
        $order_source_array = array(
            //1st cash contest order sources for both referred and referral users
            '10' => array(68,69,70,71,72,73),
            //5th cash contest order sources for both referred and referral users
            '11' => array(74,75,76,77,78,79),
            //10th cash contest order sources for both referred and referral users
            '12' => array(80,81,82,83,84,85)
        );
        $current_date = format_date();

        // echo "order source : ".$order_source_array[10][0];die;
       
      
        //process each valid referral users for cash contest referrals.
        foreach ($refferal_data as $key => $value)
        {
            $user_id          = $value['user_id'];
            $friend_id        = $value['friend_id'];
           // $user_bonus_cash  = $value['user_bonus_cash'];
            $refferal_id      = $value['user_affiliate_history_id'];
            $source_type      = $value['source_type'];
            $friend_phone     = $value['friend_phone'];
            $friend_email     = (!empty($value['friend_email']))? $value['friend_email'] : "";

            //get users total played cash contests.
            $cash_contest_count = $this->get_user_cash_contest_count($friend_id);
            if(empty($cash_contest_count))
            {
                continue;
            } 

            $affiliate_type_arr = array();

            if($cash_contest_count >= 1)
            {
                $affiliate_type_arr[] = 10;                
            } 

            if($cash_contest_count >= 5)
            {
                $affiliate_type_arr[] = 11;
            }

            if($cash_contest_count >= 10)
            {
                $affiliate_type_arr[] = 12;
            }    

            //echo '<pre>';print_r($affiliate_type_arr);die;
            if(empty($affiliate_type_arr))
            {
                continue;
            } 


            //process each active affiliate/referral types for this user.
            foreach ($affiliate_type_arr as $affiliate_type)
            {
                //check this type of referral/affilate available or not in system.
                $this->db = $this->db_user;
                $affililate_master_detail = $this->get_single_row('*',AFFILIATE_MASTER,array("affiliate_type"=>$affiliate_type));

               //echo '<pre>';print_r($affililate_master_detail);die("called...1");

                if(empty($affililate_master_detail))
                {
                    continue;
                }   
                
                //check if bonus already given for this referral/affiliate type
                $this->db = $this->db_user;
                $affililate_history = $this->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$friend_id,"status"=>1,"user_id" =>$user_id,"affiliate_type"=>$affiliate_type));
                if(!empty($affililate_history))
                {
                    continue;
                }    
                    
               // echo '<pre>';print_r($affililate_history);die("called...2");    
               //create a new entry in affiliate history for this referral type
                $affiliate_user_data                    = array();
                $affiliate_user_data["user_id"]         = $user_id;
                $affiliate_user_data["source_type"]     = $source_type;
                $affiliate_user_data["affiliate_type"]  = $affiliate_type;
                $affiliate_user_data["status"]          = 1;
                $affiliate_user_data["is_referral"]     = 1;
                $affiliate_user_data["created_date"]    = $current_date;
                $affiliate_user_data["friend_id"]       = $friend_id;
                $affiliate_user_data['friend_bonus_cash'] = (!empty($affililate_master_detail["user_bonus"])) ? $affililate_master_detail["user_bonus"] : 0;
                $affiliate_user_data['friend_real_cash'] = (!empty($affililate_master_detail["user_real"])) ? $affililate_master_detail["user_real"] : 0;
                $affiliate_user_data['user_bonus_cash'] = (!empty($affililate_master_detail["bonus_amount"])) ? $affililate_master_detail["bonus_amount"] : 0;
                $affiliate_user_data['user_real_cash'] = (!empty($affililate_master_detail["real_amount"])) ? $affililate_master_detail["real_amount"] : 0;
                $affiliate_user_data["bouns_condition"] = json_encode(array());
                $affiliate_user_data["friend_mobile"]   = $friend_phone;
                $affiliate_user_data["friend_email"]    = $friend_email;

                $this->db_user->insert(USER_AFFILIATE_HISTORY, $affiliate_user_data);
                $affililate_history_id = $this->db_user->insert_id();
                if(empty($affililate_history_id))
                {
                    continue;
                }

                /*############ Generate transactions for user who sent referral(referral code) #########*/  

                    //Entry on order table for bonus cash type
                    if($affililate_master_detail["bonus_amount"] > 0)
                    {
                        $deposit_data_friend = array(
                                            "user_id"   => $user_id, 
                                            "amount"    => $affililate_master_detail["bonus_amount"], 
                                            "source"    => $order_source_array[$affiliate_type][0],
                                            "source_id" => $affililate_history_id, 
                                            "plateform" => 1, 
                                            "cash_type" => 1,//for bonus cash 
                                            "season_type"=>1,
                                            "status"    =>1,
                                            "ignore_deposit_noty"=>1
                                        );
                        $return_data = $this->deposit($deposit_data_friend);

                        if($return_data)
                        {
                            /*Add Notification*/
                            $tmp = array();
                            $input = array(
                                'friend_name' => ($value['friendname']) ? $value['friendname'] : $value['friend_email'], 
                                'username'    => $value['username1'],
                                'amount'      => $affililate_master_detail["bonus_amount"],
                                'friend_email'=> $value['friend_email']
                            );
                            $tmp["notification_type"]        = $order_source_array[$affiliate_type][0];
                            $tmp["source_id"]                = $affililate_history_id;
                            $tmp["notification_destination"] = 3; //web, push, email
                            $tmp["user_id"]                  = $user_id;
                            $tmp["to"]                       = $value['user_email'];
                            $tmp["user_name"]                = $value['username1'];
                            $tmp["added_date"]               = $current_date;
                            $tmp["modified_date"]            = $current_date;
                            $tmp["content"]                  = json_encode($input);
                           // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                            //notification to user
                            $this->load->model('notification/Notify_nosql_model');
                            $this->Notify_nosql_model->send_notification($tmp); 
                            /* END Notification */            
                        }
                    }

                    //Entry on order table for real cash type
                    if($affililate_master_detail["real_amount"] > 0)
                    {
                       $deposit_data_friend = array(
                                            "user_id"   => $user_id, 
                                            "amount"    => $affililate_master_detail["real_amount"], 
                                            "source"    => $order_source_array[$affiliate_type][1],
                                            "source_id" => $affililate_history_id, 
                                            "plateform" => 1, 
                                            "cash_type" => 0,//for real cash 
                                            "season_type"=> 1,
                                            "status"    =>1,
                                            "ignore_deposit_noty"=>1
                                        );
                        $return_data = $this->deposit($deposit_data_friend);

                        if($return_data)
                        {
                            /*Add Notification*/
                            $tmp = array();
                            $input = array(
                                'friend_name' => ($value['friendname']) ? $value['friendname'] : $value['friend_email'], 
                                'username'    => $value['username1'],
                                'amount'      => $affililate_master_detail["real_amount"],
                                'friend_email'=> $value['friend_email']
                            );
                            $tmp["notification_type"]        = $order_source_array[$affiliate_type][1];
                            $tmp["source_id"]                = $affililate_history_id;
                            $tmp["notification_destination"] = 3; //web, push, email
                            $tmp["user_id"]                  = $user_id;
                            $tmp["to"]                       = $value['user_email'];
                            $tmp["user_name"]                = $value['username1'];
                            $tmp["added_date"]               = $current_date;
                            $tmp["modified_date"]            = $current_date;
                            $tmp["content"]                  = json_encode($input);
                           // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                            //notification to user
                            $this->load->model('notification/Notify_nosql_model');
                            $this->Notify_nosql_model->send_notification($tmp); 
                            /* END Notification */            
                        }
                    }

                   
                /*## Generate transactions for user who used referral code ###*/    

                    //Entry on order table for bonus cash type
                    if($affililate_master_detail["user_bonus"] > 0)
                    {
                       
                        $deposit_data_friend = array(
                                            "user_id"   => $friend_id, 
                                            "amount"    => $affililate_master_detail["user_bonus"], 
                                            "source"    => $order_source_array[$affiliate_type][3],
                                            "source_id" => $affililate_history_id, 
                                            "plateform" => 1, 
                                            "cash_type" => 1,//for bonus cash 
                                            "season_type"=> 1,
                                            "status"    =>1,
                                            "ignore_deposit_noty"=>1
                                        );
                        $return_data = $this->deposit($deposit_data_friend);

                        if($return_data)
                        {
                            /*Add Notification*/
                            $tmp = array();
                            $input = array(
                                'friend_name' => ($value['friendname'])?$value['friendname']:$value['friend_email'], 
                                'username'    => $value['username1'],
                                'amount'      => $affililate_master_detail["user_bonus"],
                                'friend_email'=> $value['friend_email']
                            );
                            $tmp["notification_type"]        = $order_source_array[$affiliate_type][3];
                            $tmp["source_id"]                = $affililate_history_id;
                            $tmp["notification_destination"] = 3; //web,push,email
                            $tmp["user_id"]                  = $friend_id;
                            $tmp["to"]                       = $value['friend_email'];
                            $tmp["user_name"]                = $value['username2'];
                            $tmp["added_date"]               = $current_date;
                            $tmp["modified_date"]            = $current_date;
                            $tmp["content"]                  = json_encode($input);
                           // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                            //notification to user
                            $this->load->model('notification/Notify_nosql_model');
                            $this->Notify_nosql_model->send_notification($tmp); 
                            /* END Notification */            
                        }
                    }

                    //Entry on order table for real cash type
                    if($affililate_master_detail["user_real"] > 0)
                    {
                        $deposit_data_friend = array(
                                            "user_id"   => $friend_id, 
                                            "amount"    => $affililate_master_detail["user_real"], 
                                            "source"    => $order_source_array[$affiliate_type][4],
                                            "source_id" => $affililate_history_id, 
                                            "plateform" => 1, 
                                            "cash_type" => 0,//for real cash 
                                            "season_type"=> 1,
                                            "status"    =>1,
                                            "ignore_deposit_noty"=>1
                                        );
                        $return_data = $this->deposit($deposit_data_friend);

                        if($return_data)
                        {
                            /*Add Notification*/
                            $tmp = array();
                            $input = array(
                                'friend_name' => ($value['friendname'])?$value['friendname']:$value['friend_email'], 
                                'username'    => $value['username1'],
                                'amount'      => $affililate_master_detail["user_real"],
                                'friend_email'=> $value['friend_email']
                            );
                            $tmp["notification_type"]        = $order_source_array[$affiliate_type][4];
                            $tmp["source_id"]                = $affililate_history_id;
                            $tmp["notification_destination"] = 3; //web,push,email
                            $tmp["user_id"]                  = $friend_id;
                            $tmp["to"]                       = $value['friend_email'];
                            $tmp["user_name"]                = $value['username2'];
                            $tmp["added_date"]               = $current_date;
                            $tmp["modified_date"]            = $current_date;
                            $tmp["content"]                  = json_encode($input);
                           // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                            //notification to user
                            $this->load->model('notification/Notify_nosql_model');
                            $this->Notify_nosql_model->send_notification($tmp); 
                            /* END Notification */            
                        }
                    }
            }
            

        }

      return TRUE;
    
    }


    //Get user cash count for referral bonus
    private function get_user_cash_contest_count($user_id)
    {
        $this->db = $this->db_fantasy;
        $total_cash_contest = 0;
        $this->db->select("count(LMC.lineup_master_contest_id ) as total_cash_contest",FALSE)
            ->from(CONTEST." C")
            ->join(LINEUP_MASTER_CONTEST." LMC","LMC.contest_id = C.contest_id","INNER")
            ->join(LINEUP_MASTER." LM","LMC.lineup_master_id= LM.lineup_master_id","INNER")
            ->where("LM.user_id>",0)
            ->where("C.entry_fee>",0)
            ->where("C.currency_type != ","2")
            //->where_in("C.status",array(2,3))
            ->where("LM.user_id", $user_id);
        $record = $this->db->get()->row_array();
        if(!empty($record['total_cash_contest']))
        {
            $total_cash_contest = $record['total_cash_contest'];
        }   

        return $total_cash_contest;
    }

    public function get_season_list_by_status($status, $status_overview=array(), $notification_sent=array())
    {
        $this->db_fantasy->select("season_id, season_game_uid, season_scheduled_date,home, away, status, status_overview, notification_sent,league_id,CONCAT(league_id,'_',season_game_uid) AS league_game_uid",FALSE)->from(SEASON." S");
        if(!empty($notification_sent))
        {
            $this->db_fantasy->where_in('notification_sent',$notification_sent);
        }
        else 
        {
            $this->db_fantasy->where('notification_sent',0);
        }
        if(!empty($status))
        {
            $this->db_fantasy->where_in('status', $status);
        }
        if(!empty($status_overview))
        {
            $this->db_fantasy->where_in('status_overview', $status_overview);
        }
        $sql = $this->db_fantasy->get();
        //echo $this->db_fantasy->last_query(); die;
        $season_result = $sql->result_array();
        return $season_result;
    }

    private function _update_paytm_transaction_status($transaction_id, $status_type,$update_data=array())
    {
        if(!$transaction_id)
        {
            return false;
        }
        
        $trnxn_rec = $this->db_user->select("T.*")
                    ->from(TRANSACTION. " T")
                    ->where("T.transaction_id",$transaction_id)
                    ->get()
                    ->row_array();
        if($status_type == 1) 
        {
            if(!empty($trnxn_rec))
            {
                $order_detail = array("order_id" => $trnxn_rec["order_id"],"status" => $status_type,"source_id" => $transaction_id,"reason"    => 'by your recent transaction.');
                $order_detail['update_transaction_data_paytm'] = $update_data;

                //change
                $orderData = $this->db->where('order_id',$order_id)
                                        ->where('status != ',1)
                                        ->get(ORDER,1)
                                        ->row_array();
                if (!$orderData) 
                {
                    return ['error' => $this->finance_lang["no_pending_order"], 'service_name' => 'withdraw'];
                }
                
                $user_balance = $this->get_user_balance($orderData["user_id"]);
                
                $paym_data = $update_data;//paytm response
                $payu_txn_id = $paym_data['payu_txn_id'];//payumoney response
                
                if($orderData['source'] == 7 && $status_type = 1)
                {
                    if(!empty($paym_data))
                    {
                        if($this->app_config['allow_paytm']['custom_data']['pg_mode']=='TEST')
                        {
                            $PAYTM_ORDER_STATUS_API       = PAYTM_ORDER_STATUS_API_TEST;
                        }else{
                            $PAYTM_ORDER_STATUS_API       = PAYTM_ORDER_STATUS_API_PRO;
                        }
                        $paytm_status_response= get_paytm_transaction_status($paym_data['MID'],$paym_data['ORDERID'],$paym_data['CHECKSUMHASH'],$PAYTM_ORDER_STATUS_API);
                                if(empty($paytm_status_response['STATUS']) || (!empty($paytm_status_response['STATUS']) && $paytm_status_response['STATUS']!=="TXN_SUCCESS") )
                        {
                            if(!empty($paytm_status_response['STATUS']))
                            {
                                return ['error' => $paytm_status_response['STATUS'], 'service_name' => 'update_order_status'];
                            }
                            else
                            {
                                return array('error' => "Problem while paytm status update.", 'service_name' => 'update_order_status');
                            }
                        }
                    }
                }
        
                if($orderData['source'] == 7 && $status_type ==1 && (empty($paym_data)))
                {
                    return array('error' => "Problem while status update.", 'service_name' => 'update_order_status');
                }

                // For Creadit payment.
                if ($orderData["type"] == 0 && $status_type == 1) 
                {
                    $real_bal = $user_balance['real_amount'] + $orderData["real_amount"];
                    $bonus_bal = $user_balance['bonus_amount'] + $orderData["bonus_amount"];
                    $this->update_user_balance($orderData["user_id"], $orderData, "add");
                }
            }
        }
        //echo "<pre>";print_r($update_data);die;
        // CALL Platform API to update transaction
        $data = array();
        $data['transaction_status'] = $status_type;
        if(!empty($update_data) && $update_data['txn_id'] != "")
        {
            $data['txn_id'] = $update_data['txn_id'];
            $data['txn_amount'] = $update_data['txn_amount'];
            $data['txn_date'] = $update_data['txn_date'];
            $data['responce_code'] = $update_data['responce_code'];
        }    
        
        if($trnxn_rec['transaction_status'] == 0)
        {
            $this->db_user->where('transaction_id', $transaction_id)->update(TRANSACTION, $data);
            $res = $this->db_user->affected_rows();
        }    
       
        // When Transaction has been failed , order status will also become fails
        if($status_type == 2) 
        {
            $sql = "UPDATE 
                        ".$this->db->dbprefix(ORDER)." AS O
                    INNER JOIN 
                        ".$this->db->dbprefix(TRANSACTION)." AS T ON T.order_id = O.order_id
                    SET 
                        O.status = T.transaction_status
                    WHERE 
                        T.transaction_id = $transaction_id AND O.status = 0 
                    ";
            $this->db_user->query($sql);
        }
        //if($finance_obj->updateTransacton($transaction_id, $status_type))
        if( $res){
            return $trnxn_rec;
        }else{
            //return $trnxn_rec;
        }
            
        return FALSE;
    }

    public function process_payment_pending_order() 
    {
        $failed_status_time = 20;//time in minute
        $current_date = format_date();
        $process_date_time = date('Y-m-d H:i:s',strtotime('-2 minutes',strtotime($current_date)));
        $last_date_time = date('Y-m-d H:i:s',strtotime('-24 hours',strtotime($current_date)));
        $this->db = $this->db_user;
        $payment_gateway_ids = array("1","2","3","7","8","10","13","14","16","17","18","27","33","34");
        $this->db->select("O.order_id,O.date_added,O.order_unique_id,T.transaction_id,O.real_amount,O.user_id,T.payment_gateway_id,T.pg_order_id,T.bank_txn_id,O.tds")
                    ->from(ORDER. " O")
                    ->join(TRANSACTION. " T","T.order_id = O.order_id")
                    ->where("O.status","0")
                    ->where("O.source","7")
                    ->where("O.type","0")
                    ->where("T.transaction_status","0")
                    ->where_in("T.payment_gateway_id",$payment_gateway_ids)
                    ->where("O.modified_date <= ",$process_date_time)
                    ->where("O.modified_date >= ",$last_date_time)
                    ->order_by("O.order_id","DESC")
                    ->limit(50);

        $query = $this->db->get();
        $result = $query->result_array(); 
        foreach($result as $order)
        {
            if($order['order_id'])
            {
              $txnid = $order['transaction_id'];
              //payumoney
              if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "1"){
                $config = array(
                  "VERSION"  => $this->app_config['allow_payumoney']['custom_data']['version'],
                  "MERCHANT_KEY"=>$this->app_config['allow_payumoney']['custom_data']['merchant_key'],
                  "TXN_VALIDATE_BASE_URL"=>($this->app_config['allow_payumoney']['custom_data']['pg_mode']=='TEST') ? PAYU_TXN_VALIDATE_BASE_URL_TEST : PAYU_TXN_VALIDATE_BASE_URL_PRO,
                  "AUTH_HEADER"=>$this->app_config['allow_payumoney']['custom_data']['auth_header']
              );
                $payment_data = payu_validate_transaction($txnid,$config);
                $res_data = $payment_data['result']['transaction_details'][$txnid];
                $success_status = array("money with payumoney", "settlement in process", "completed", "money settled","success");
                //status = 0 means success and -1 = failure
                if($config['VERSION']=='NEW')
                {
                      $update_txn = array(
                        "txn_id"=>isset($res_data['txnid']) ? $res_data['txnid'] : "",
                        "txn_amount"=>isset($res_data['amt']) ? $res_data['amt'] : 0,
                        "txn_date"=>format_date(),
                        "bank_txn_id"=>isset($res_data['bank_ref_num']) ? $res_data['bank_ref_num'] : "",
                        "payment_mode"=>isset($res_data['mode']) ? $res_data['mode'] : "",
                        "bank_name"=>isset($res_data['bankcode']) ? $res_data['bankcode'] : "",
                        "transaction_message"=>isset($res_data['field9']) ? $res_data['field9'] : "",
                        "gate_way_name"=>"PayU Money",
                      );

                  if($payment_data['status'] == "SUCCESS" && !empty($payment_data['result']['transaction_details'][$txnid]) && in_array(strtolower($payment_data['result']['transaction_details'][$txnid]['status']),$success_status)){
                      $txn_pg_amount = $payment_data['result']['transaction_details'][$txnid]['amt'];
                      $txn_pg_amount = number_format($txn_pg_amount, 2, '.', '');
                      $t_amount = $order['real_amount'] + $order['tds'];
                      //additional check for amount mismatch
                      if($txn_pg_amount == $t_amount){
                          // Update status=success to transaction table by calling API
                          $this->_update_payment_status($txnid, 1, $update_txn);
                      }else{
                        $this->_update_payment_status($txnid, 2, $update_txn);
                      }  
                  } else {
                        $this->_update_payment_status($txnid, 2, $update_txn);
                  }

                }else{
                  
                  if ($payment_data['status'] == "SUCCESS" && !empty($payment_data['result']) && $payment_data['result']['status'] == "0" && in_array(strtolower($payment_data['result']['result']['0']->status), $success_status)) {
                    $payment_data = (array)$payment_data['result']['result']['0'];
                    //additional check for amount mismatch
                    $pg_txn_amount = number_format($payment_data['amount'], 2, '.', '');
                    $t_amount = $order['real_amount'] + $order['tds'];
                    if($pg_txn_amount == $t_amount){
                      // Update status=success to transaction table by calling API
                      $update_txn = array();
                      $update_txn['txn_id'] = $payment_data['paymentId'];
                      $update_txn['txn_amount'] = $payment_data['amount'];
                      $update_txn['txn_date'] = format_date();
                      $update_txn['responce_code'] = "";
                      $update_txn['gate_way_name'] = "Payumoney";
                      $this->_update_payment_status($txnid, 1, $update_txn);
                    }else{
                      $this->_update_payment_status($txnid, 2, array());
                    }
                  }else{

                    $minutes = (strtotime(format_date()) - strtotime($order['date_added'])) / 60;
                    if($minutes > $failed_status_time)
                    {
                      $this->_update_payment_status($txnid, 2, array());
                    }
                  }
                }

              }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "2"){
                //paytm
                $paytmParams = array();
                $paytmParams["MID"] = $this->app_config['allow_paytm']['custom_data']['merchant_mid'];
                $paytmParams["ORDERID"] = $txnid;
                $varbl = "";
                foreach($paytmParams as $x => $x_value) 
                {
                  $varbl = $varbl.$x.$x_value;
                }
                $checksumhash = $varbl.$this->app_config['allow_paytm']['custom_data']['merchant_key'];
                $paytmParams['CHECKSUMHASH'] = md5($checksumhash);
                if($this->app_config['allow_paytm']['custom_data']['pg_mode']=='TEST')
                {
                    $PAYTM_ORDER_STATUS_API       = PAYTM_ORDER_STATUS_API_TEST;
                }else{
                    $PAYTM_ORDER_STATUS_API       = PAYTM_ORDER_STATUS_API_PRO;
                }
                $payment_data = get_paytm_transaction_status($paytmParams['MID'],$paytmParams['ORDERID'],$paytmParams['CHECKSUMHASH'],$PAYTM_ORDER_STATUS_API);
                if(isset($payment_data['STATUS']) && $payment_data['STATUS'] == "TXN_SUCCESS"){

                  //additional check for amount mismatch
                  $pg_txn_amount = number_format($payment_data['TXNAMOUNT'], 2, '.', '');
                  $t_amount = $order['real_amount'] + $order['tds'];
                  if($pg_txn_amount == $t_amount){
                    $update_txn = array();
                    $update_txn['txn_id'] = $payment_data['TXNID'];
                    $update_txn['txn_amount'] = $payment_data['TXNAMOUNT'];
                    $update_txn['txn_date'] = $payment_data['TXNDATE'];
                    $update_txn['responce_code'] = $payment_data['RESPCODE'];
                    $update_txn['gate_way_name'] = "Paytm";
                    $this->_update_payment_status($txnid, 1, $update_txn);
                  }else{
                    $this->_update_payment_status($txnid, 2, array());
                  }
                }else{
                  $minutes = (strtotime(format_date()) - strtotime($order['date_added'])) / 60;
                  if($minutes > $failed_status_time)
                  {
                    $this->_update_payment_status($txnid, 2, array());
                  }
                }

              }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "3"){
                // echo "reached";die;
                
                $config=array(
                  "mode" => $this->app_config['allow_mpesa']['custom_data']['mode'],
                  "password" => $this->app_config['allow_mpesa']['custom_data']['password'],
                  "key" => $this->app_config['allow_mpesa']['custom_data']['consumer_key'],
                  "secret" => $this->app_config['allow_mpesa']['custom_data']['consumer_secret'],
                  "short_code" => $this->app_config['allow_mpesa']['custom_data']['shortcode'],
                  "initiator" => $this->app_config['allow_mpesa']['custom_data']['initiator'],
                  "prefix" => $this->app_config['order_prefix']['key_value'],
                );
                
                // echo $url;die;
                $this->load->helper('payment');
                if(!isset($order['pg_order_id']) || $order['pg_order_id'] == '')
                {
                  $this->_update_payment_status($txnid, 2, array());
                }else{
                  $txn_status  = get_mpesa_txn_status($order['pg_order_id'],$config);
                  //a call back will be sent to user/mpesa/payment_cron_callback method ... finished here.
                  if($txn_status['ResponseCode']==0 || $txn_status['ResponseCode']=='0')
                  {
                    return true;
                  }else{
                    return false;
                  }
                }

              }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "7"){
                //Paystack
                $last_time = date('Y-m-d H:i:s',strtotime('+11 hours',strtotime($order['date_added'])));
                $config = array(
                  "secret"=>$this->app_config['allow_paystack']['custom_data']['secret'],
                );
                $payment_data = get_paystack_transaction_status($txnid,$config);
                if ($payment_data['status'] == "SUCCESS" && !empty($payment_data['result'])) {
                  $payment_data = $payment_data['result'];
                  // Update status=success to transaction table by calling API
                  //additional check for amount mismatch
                  $pg_txn_amount = number_format($payment_data['amount'], 2, '.', '');
                  if($pg_txn_amount == $order['real_amount']){
                    $update_txn = array();
                    $update_txn['txn_id'] = $payment_data['transaction_id'];
                    $update_txn['txn_amount'] = $payment_data['amount'];
                    $update_txn['bank_txn_id'] = $payment_data['authorization_code'];
                    $update_txn['bank_name'] = $payment_data['bank_name'];
                    $update_txn['gate_way_name'] = "Paystack";
                    $this->_update_payment_status($txnid, 1, $update_txn);
                  }else{
                    $this->_update_payment_status($txnid, 2, array());
                  }
                }else{
                  if($current_date > $last_time){
                    $this->_update_payment_status($txnid, 2, array());
                  }
                }
              }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "8"){
                //razor pay
                $config = array(
                  "r_key"=>$this->app_config['allow_razorpay']['custom_data']['key'],
                  "r_secret"=>$this->app_config['allow_razorpay']['custom_data']['secret'],
                  "r_currency"=>$this->app_config['currency_abbr']['key_value'], //[EX : INR,USD]
              );
                $payment_data = get_razorpay_txn_status($order['pg_order_id'],$config);
                //echo "<pre>";print_r($payment_data);die;
                //status = 0 means success and -1 = failure
                $success_status = array("captured","paid");
                $failed_status = array("failure","failed");
                if(isset($payment_data['items']) && isset($payment_data['items']['0']) && in_array(strtolower($payment_data['items']['0']['status']),$success_status)){
                  $payment_data = $payment_data['items']['0'];
                  // Update status=success to transaction table by calling API
                  $paid_amount = number_format(($payment_data['amount']/100),"2",".","");
                  $update_txn = array();
                  $update_txn['txn_id'] = $payment_data['id'];
                  $update_txn['txn_amount'] = $paid_amount;
                  $update_txn['txn_date'] = format_date();
                  $update_txn['responce_code'] = "";
                  $update_txn['gate_way_name'] = "Razorpay";

                  //additional check for amount mismatch
                  $pg_txn_amount = number_format($paid_amount, 2, '.', '');
                  $t_amount = $order['real_amount'] + $order['tds'];
                  if($pg_txn_amount == $t_amount){
                    $this->_update_payment_status($txnid, 1, $update_txn);
                  }else{
                    $this->_update_payment_status($txnid, 2, $update_txn);
                  }
                }else if(isset($payment_data['items']) && isset($payment_data['items']['0']) && in_array(strtolower($payment_data['items']['0']['status']),$failed_status)){
                  $payment_data = $payment_data['items']['0'];
                  $paid_amount = number_format(($payment_data['amount']/100),"2",".","");
                  $update_txn = array();
                  $update_txn['txn_id'] = $payment_data['id'];
                  $update_txn['txn_amount'] = $paid_amount;
                  $update_txn['txn_date'] = format_date();
                  $update_txn['responce_code'] = "";
                  $update_txn['gate_way_name'] = "Razorpay";
                  $this->_update_payment_status($txnid, 2, $update_txn);
                }else{
                  $minutes = (strtotime(format_date()) - strtotime($order['date_added'])) / 60;
                  if($minutes > $failed_status_time)
                  {
                    $this->_update_payment_status($txnid, 2, array());
                  }
                }
              }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "10"){
                $data = array(
                  'amount'=>$order['real_amount']*100,
                  'charge_id'=>$order['pg_order_id'],
                  'key'=>$this->app_config['allow_stripe']['custom_data']['s_key'],
                  "user_id"=>$order['user_id'],
                );
                if(isset($data['charge_id']) && $data['charge_id']!= null)
                {
                  $stripe_response = get_stripe_txn_status($data);
                  // print_r($stripe_response['result']);exit;
                  if($stripe_response['status']=='SUCCESS')
                  {
                    if($stripe_response['result']['paid']==1 && $stripe_response['result']['amount']==($order['real_amount']*100) && $stripe_response['result']['metadata']['user_id'] == $data['user_id'])
                    {
                        $update_data = array();
                        $update_data['txn_id'] = $stripe_response['result']['metadata']['txn_id'];
                        $update_data['txn_amount'] = $order['real_amount'];
                        $update_data['txn_date'] = $stripe_response['result']['created'] ? date( 'Y-m-d H:i:s' , $stripe_response['result']['created']) : format_date();
                        $update_data['bank_txn_id'] = $stripe_response['result']['balance_transaction'] ? $stripe_response['result']['balance_transaction'] : '';
                        $update_data['gate_way_name'] = "Stripe";
                        $update_data['currency'] = strtoupper($stripe_response['result']['currency']);
                        $update_data['transaction_message'] = isset($stripe_response['result']['receipt_url']) ? $stripe_response['result']['receipt_url']:'';

                        $this->_update_payment_status($txnid, 1, $update_data);

                    }else{
                      $this->_update_payment_status($txnid, 2, array());
                    }
                  }else{
                    $this->_update_payment_status($txnid, 2, array());
                  }
                }else{
                  $this->_update_payment_status($txnid, 2, array());
                }
              }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "13"){
                $config = array(
                  "v_mid"=>$this->app_config['allow_vpay']['custom_data']['mid'],
                  "v_base_url"=>($this->app_config['allow_vpay']['custom_data']['vpay_pg_mode']=="TEST") ? VPAY_BASE_URL_TEST:VPAY_BASE_URL_PRO,
                );
                $payment_data = vpay_validate_transaction($txnid,$config);
                //status = 0 means success and -1 = failure
                if($payment_data['status'] == "SUCCESS" && !empty($payment_data['result']) && $payment_data['result']['code'] == "200" && $payment_data['result']['data']->status == "success") {
                  $payment_data = (array)$payment_data['result']['data'];
                  //additional check for amount mismatch
                  $pg_txn_amount = number_format($payment_data['amount'], 2, '.', '');
                  $t_amount = $order['real_amount'] + $order['tds'];
                  if($pg_txn_amount == $t_amount){
                    // Update status=success to transaction table by calling API
                    $update_txn = array();
                    $update_txn['txn_id'] = $payment_data['pgtxnid'];
                    $update_txn['txn_amount'] = $payment_data['amount'];
                    $update_txn['payment_mode'] = isset($payment_data['payment_mode']) ? $payment_data['payment_mode'] : "";
                    $update_txn['bank_txn_id'] = isset($payment_data['bank_txn_id']) ? $payment_data['bank_txn_id'] : "";
                    $update_txn['txn_date'] = isset($payment_data['txn_date']) ? $payment_data['txn_date'] : NULL;
                    $update_txn['responce_code'] = "";
                    $update_txn['gate_way_name'] = "vPay";
                    $update_txn['transaction_message'] = isset($payment_data['status_msg']) ? $payment_data['status_msg'] : NULL;

                    $this->_update_payment_status($txnid, 1, $update_txn);
                  }else{
                    $this->_update_payment_status($txnid, 2, array());
                  }
                }else{

                  $minutes = (strtotime(format_date()) - strtotime($order['date_added'])) / 60;
                  if($minutes > $failed_status_time)
                  {
                    $this->_update_payment_status($txnid, 2, array());
                  }
                }

              }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "14"){
                $validate_data  = array(
                  "txnid"     => $txnid,
                  "key"       => $this->app_config['allow_ifantasy']['custom_data']['key'],
                  "member_id" => $this->app_config['allow_ifantasy']['custom_data']['member_id'],
              );
                $payment_data = get_ifantasy_txn_status($validate_data);
                //status = 0 means success and -1 = failure
                if($payment_data['status'] == "SUCCESS" && !empty($payment_data['result']) && strtoupper($payment_data['result']['status']) == "SUCCESSFUL") {
                  $payment_data = $payment_data['result'];
                  //additional check for amount mismatch
                  $pg_txn_amount = number_format($payment_data['txn_amount'], 2, '.', '');
                  if($pg_txn_amount == $order['real_amount']){
                    // Update status=success to transaction table by calling API
                    $update_txn = array();
                    $update_txn['txn_id'] = substr($payment_data['order_id'],6);
                    $update_txn['txn_amount'] = $payment_data['txn_amount'];
                    $update_txn['bank_txn_id'] = isset($payment_data['Txt_Ref']) ? $payment_data['Txt_Ref'] : "";
                    $update_txn['txn_date'] = isset($payment_data['txn_date']) ? date("Y-m-d H:i:s",$payment_data['txn_date']) : NULL;
                    $update_txn['gate_way_name'] = "Ifantasy";
                    $update_txn['transaction_message'] = isset($payment_data['status_msg']) ? $payment_data['status_msg'] : NULL;

                    $this->_update_payment_status($txnid, 1, $update_txn);
                  }else{
                    $this->_update_payment_status($txnid, 2, array());
                  }
                }else{

                  $minutes = (strtotime(format_date()) - strtotime($order['date_added'])) / 60;
                  if($minutes > $failed_status_time)
                  {
                    $this->_update_payment_status($txnid, 2, array());
                  }
                }

              }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "16"){

                $mode = $this->app_config['allow_cashierpay']['custom_data']['mode'];
                $payId = $this->app_config['allow_cashierpay']['custom_data']['payId'];
                $secretKey = $this->app_config['allow_cashierpay']['custom_data']['secretKey'];
                $currency = $this->app_config['allow_cashierpay']['custom_data']['currency'];
                $time_limit = date('Y-m-d H:i:s',strtotime('-11 hours',strtotime($current_date)));
                
                if($mode == 'PROD')
                {
                    $url = "https://enquiry.cashierpay.online/".CASHIERPAY_STATUS_URL;
                }else{
                    $url = "https://enquiry.cashierpay.online/".CASHIERPAY_STATUS_URL;
                }

                $success_code = [000];
                $pending_code = [003,006,011];
                // $txnid
                $order_prefix = isset($this->app_config['order_prefix']) ? $this->app_config['order_prefix']['key_value'] : '';
                $status_req = array(
                    "ORDER_ID"          => $order_prefix.$txnid,
                    "AMOUNT"            => $order['real_amount']*100,
                    "TXNTYPE"           => 'STATUS',
                    "CURRENCY_CODE"     => $currency,
                    "PAY_ID"            => $payId,
                );
                $status_req['HASH'] = get_cashierpay_hash($status_req,$secretKey);
                $txn_data = get_cashierpay_txn_status($status_req,$url);
                // print_r($txn_data);exit;
                if(
                  isset($txn_data['RESPONSE_CODE']) && in_array(strtoupper($txn_data['RESPONSE_CODE']),$success_code) && 
                  isset($txn_data['STATUS']) && strtoupper($txn_data['STATUS'])=='CAPTURED'
                  )
                  {
                    $amount = number_format($txn_data['AMOUNT'], 2, '.', '');
                    if($status_req['AMOUNT'] == $amount)
                    {
                      $data = array();
                      $data['txn_id'] = $txnid;
                      $data['pg_order_id'] = $txn_data['ORDER_ID'];
                      $data['bank_txn_id'] = isset($txn_data['RRN']) ? $txn_data['RRN'] : "";
                      $data['txn_amount'] = isset($txn_data['AMOUNT']) ? ($txn_data['AMOUNT']/100) : 0;
                      $data['txn_date'] = isset($txn_data['RESPONSE_DATE_TIME']) ? $txn_data['RESPONSE_DATE_TIME'] : NULL;
                      $data['gate_way_name'] = "Cashierpay";
                      $data['currency'] = isset($txn_data['CURRENCY_CODE']) ? $txn_data['CURRENCY_CODE'] : "";
                      $data['bank_name'] = isset($txn_data['CARD_MASK']) ? $txn_data['CARD_MASK'] : "";
                      $data['payment_mode'] = isset($txn_data['PAYMENT_TYPE']) ? $txn_data['PAYMENT_TYPE'] : "";
                      $data['transaction_message'] = isset($txn_data['HASH']) ? $txn_data['HASH'] : "";

                      $this->_update_payment_status($txnid, 1, $data);
                    }else{
                      $this->_update_payment_status($txnid, 2, array());  
                    }
                    //success
                  }
                  elseif(isset($txn_data['RESPONSE_CODE']) && in_array($txn_data['RESPONSE_CODE'],$pending_code)){
                        if($order['date_added'] < $time_limit)
                        {
                          $this->_update_payment_status($txnid, 2, array());      
                        }
                  }else{
                    $this->_update_payment_status($txnid, 2, array());
                  }
                }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "17")
              {
                $data = array();
                $data['ord_prefix']      = $this->app_config['order_prefix']['key_value'];
                $data['mode']       = $this->app_config['allow_cashfree']['custom_data']['mode'];
                $data['app_id']     = $this->app_config['allow_cashfree']['custom_data']['app_id'];
                $data['secret_key'] = $this->app_config['allow_cashfree']['custom_data']['secret_key'];
                $data['app_version'] = $this->app_config['allow_cashfree']['custom_data']['app_version'];

                $transaction_details = get_cashfree_txn_status($data['ord_prefix'].$txnid,$data);
                if($transaction_details['is_active']==1)
                {
                    continue;
                }else if($transaction_details['is_active']==2)
                {
                    $response['payment_status'] = 'FAILED';
                    $response['payment_amount'] = $order['real_amount'];
                }
                else if($transaction_details['is_active']==3){
                    $response = $transaction_details['response'];
                    // $header = $res['header'];
                }
                $t_amount = $order['real_amount'] + $order['tds'];
                if($response && $t_amount == $response['payment_amount'] && $response['payment_status']=='SUCCESS')
                {
                  $data = array();
                  $data['gate_way_name'] = "Cashfree_upgraded";
                  $data['txn_amount'] = $response['captured_amount'] ? $response['captured_amount']:'';
                  $data['bank_txn_id'] = isset($response['bank_reference']) ? $response['bank_reference'] : NULL;
                  $data['txn_date'] = isset($response['payment_time']) ? $response['payment_time'] : NULL;
                  $data['transaction_message'] = isset($response['payment_message']) ? $response['payment_message'] : '';
                  $data['currency'] = isset($response['payment_currency']) ? $response['payment_currency'] : '';
                  $data['mid'] = isset($response['cf_payment_id']) ? $response['cf_payment_id'] : "";
                  $data['pg_order_id'] = isset($response['order_id']) ? $response['order_id'] : "";
                  $data['txn_id'] = $txnid;

                  $this->_update_payment_status($txnid, 1, $data);
                }else if(in_array(strtolower($response['payment_status']),array("pending","flagged","not_attempted"))){
                  $minutes = (strtotime(format_date()) - strtotime($order['date_added'])) / 60;
                  if($minutes > $failed_status_time)
                  {
                    $this->_update_payment_status($txnid, 2, array());
                  }else{
                    continue;
                  }
                }else{
                  $this->_update_payment_status($txnid, 2, array());
                }
                
              }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "18")
              {
                $data = array();
                $data['app_id']   = $this->app_config['allow_paylogic']['custom_data']['app_id'];
                $data['salt'] = $this->app_config['allow_paylogic']['custom_data']['salt'];
                                
                $success_status = array("captured","ok");
                $failed_status = array("f","declined","acquirer_error","denied","timeout","authentication_unavailable","failed","duplicate","signature_mismatch","cancelled","recurring_payment_unsuccessfull","denied_by_risk","invalid_request","refund_insufficient_balance","txn_failed","failed_at_acquirer","validation_failed","payment_option_not_supported");
                $pending_status = array("processing","sent to bank","auto_reversal");
                
                $request_data=array(
                  "txn_id"          => $order['pg_order_id'],
                );

                $txn_data = get_paylogic_txn_status($request_data,$data['app_id']);
                $pg_response = paylogic_decrypt($txn_data,$data['salt']);

                
                if(isset($pg_response['trans_status']) && in_array(strtolower($pg_response['trans_status']),$success_status))
                {
                  $data = array();
                  $data['payment_mode'] = isset($pg_response['payment_mode']) ? $pg_response['payment_mode'] : "";
                  $data['bank_txn_id'] = isset($pg_response['bank_ref_id']) ? $pg_response['bank_ref_id'] : "";
                  $data['mid'] = isset($pg_response['pg_ref_id']) ? $pg_response['pg_ref_id'] : "";
                  $data['txn_id'] = isset($pg_response['txn_id']) ? $pg_response['txn_id'] : "";
                  $data['txn_amount'] = isset($pg_response['txn_amount']) ? $pg_response['txn_amount'] : 0;
                  $data['txn_date'] = isset($pg_response['resp_date_time']) ? date("Y-m-d H:i:s",$pg_response['resp_date_time']) : NULL;
                  $data['gate_way_name'] = "Paylogic";
                  $data['transaction_status'] = 1;

                  $this->_update_payment_status($txnid, 1, $data);
                }
                else if(isset($pg_response['trans_status']) && in_array(strtolower($pg_response['trans_status']),$failed_status))
                {
                  $this->_update_payment_status($txnid, 2, array());
                }
                else if(in_array(strtolower($pg_response['status']),$pending_status)){
                  $minutes = (strtotime(format_date()) - strtotime($order['date_added'])) / 60;
                  if($minutes > $failed_status_time)
                  {
                    $this->_update_payment_status($txnid, 2, array());
                  }else{
                    continue;
                  }
                }else{
                  $this->_update_payment_status($txnid, 2, array());
                }
                
              }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "27")
              {
                $mode       = $this->app_config['allow_directpay']['custom_data']['mode'];
                $appId      = $this->app_config['allow_directpay']['custom_data']['app_id'];
                $secretKey  = $this->app_config['allow_directpay']['custom_data']['secret_key'];
                $currency   = $this->app_config['currency_abbr']['key_value'];
                $order_prefix = isset($this->app_config['order_prefix']) ? $this->app_config['order_prefix']['key_value'] : '';
                $minutes = (strtotime(format_date()) - strtotime($order['date_added'])) / 60;

                $success_status = array('SUCCESS','success');
                $failed_status = array('FAILED','failed');
                $pending_status = array('PENDING','pending');
                
                $req_data = array(
                    "merchant_id"=>$appId,
                    "order_id"=>$order_prefix.$txnid,
                    );
                $encode_payload = base64_encode(json_encode($req_data));
                $signature = hash_hmac('sha256', $encode_payload, $secretKey);
                $response_data = get_directpay_txn_status($encode_payload,$signature,$mode);
                $response_data = json_decode($response_data,true);
                if($response_data['status']==200)
                {
                  $response_data = $response_data['data']['transaction'];
                  $amount = isset($response_data['amount']) ? ($response_data['amount'] / 100) : 0;
                  $data = array();
                  $data['pg_order_id'] = $order_prefix . $txnid;
                  $data['bank_txn_id'] = (isset($response_data['id'])) ? $response_data['id'] : "";
                  $data['txn_id'] = (isset($response_data['id'])) ? $response_data['id'] : "";
                  $data['txn_amount'] = $amount;
                  $data['txn_date'] = format_date();
                  $data['gate_way_name'] = "Directpay";
                  $data['transaction_status'] = 1;
                  $data['currency'] = $currency;
                  $data['payment_mode'] = isset($response_data['channel']) ? $response_data['channel'] : "";
                  $data['transaction_message'] = isset($response_data['description']) ? $response_data['description'] : "";
                  if(in_array(strtoupper($response_data['status']),$success_status))
                  {
                    $this->_update_payment_status($txnid, 1, $data); 
                  }elseif(in_array(strtoupper($response_data['status']),$failed_status)){
                    $this->_update_payment_status($txnid, 2, $data);
                  }elseif(in_array(strtoupper($response_data['status']),$pending_status) && $minutes > $failed_status_time){
                    $this->_update_payment_status($txnid, 2, $data);
                  }else{
                    continue;
                  }
                }elseif(in_array(strtoupper($response_data['status']),$failed_status)){
                  $this->_update_payment_status($txnid, 2, []);
                }elseif($minutes > $failed_status_time)
                {
                  $this->_update_payment_status($txnid, 2, array());
                }else{
                  continue;
                }
              }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "33") {
                    
                $this->load->helper('payment');
                 $phonepeParams = array();
                 $phonepeParams["mid"]      = $this->app_config['allow_phonepe']['custom_data']['merchent_key'];
                 $phonepeParams["txnid"]    = $txnid;
                 $phonepeParams["msalt"]    = $this->app_config['allow_phonepe']['custom_data']['salt'];
                 $mode                      = $this->app_config['allow_phonepe']['custom_data']['mode'];
                 $key_index                 = $this->app_config['allow_phonepe']['custom_data']['key_index'];

                 if($mode == "TEST") {
                    $endpoint_url = PHONEPE_STATUS_TEST_URL;
                } else {
                    $endpoint_url = PHONEPE_STATUS_PROD_URL;
                }
                $payment_data = get_phonepe_transaction_status($endpoint_url, $phonepeParams["mid"], $phonepeParams["txnid"], $phonepeParams["msalt"],$key_index);
                if(isset($payment_data['code']) && $payment_data['code'] == "PAYMENT_SUCCESS"){
                    //additional check for amount mismatch
                    $pg_txn_amount = number_format(($payment_data['data']['amount'] / 100),2,'.','');
                    
                    $t_amount = $order['real_amount'] + $order['tds'];
                    if($pg_txn_amount == $t_amount) { 
                        $pg_response = $payment_data;
                        $pg_response['data']['amount'] = $pg_txn_amount;
                        
                        $update_txn                         = array();
                        $update_txn['txn_id']               = $payment_data['data']['transactionId'];
                        $update_txn['txn_amount']           = number_format(($payment_data['data']['amount'] / 100),2,'.','');
                        $update_txn['txn_date']             = format_date();
                        $update_txn['responce_code']        = isset($payment_data['success']) ? $payment_data['success'] : '';
                        $update_txn['gate_way_name']        = "Phonepe";
                        
                        $payment_type = isset($payment_data['data']['paymentInstrument']['type']) ? $payment_data['data']['paymentInstrument']['type'] : '';
                        
                        $update_txn['payment_mode']         = $payment_type;
                        if(isset($payment_type) && $payment_type == "CARD"){
                            $update_txn['bank_txn_id']          = isset($payment_data['data']['paymentInstrument']['pgTransactionId']) ? $payment_data['data']['paymentInstrument']['pgTransactionId'] : "";
                            $update_txn['bank_name']            = isset($payment_data['data']['paymentInstrument']['bankId']) ? $payment_data['data']['paymentInstrument']['bankId'] : "";
                            $update_txn['pg_order_id']          = isset($payment_data['data']['paymentInstrument']['bankTransactionId']) ? $payment_data['data']['paymentInstrument']['bankTransactionId'] : "";
                        }elseif(isset($payment_type) && $payment_type == "UPI"){
                            $update_txn['bank_txn_id']          = isset($payment_data['data']['paymentInstrument']['utr']) ? $payment_data['data']['paymentInstrument']['utr'] : "";
                        }elseif(isset($payment_type) && $payment_type == "NETBANKING"){
                            $update_txn['bank_txn_id']          = isset($payment_data['data']['paymentInstrument']['pgTransactionId']) ? $payment_data['data']['paymentInstrument']['pgTransactionId'] : "";
                            $update_txn['bank_name']            = isset($payment_data['data']['paymentInstrument']['bankId']) ? $payment_data['data']['paymentInstrument']['bankId'] : "";
                            $update_txn['pg_order_id']          = isset($payment_data['data']['paymentInstrument']['pgServiceTransactionId']) ? $payment_data['data']['paymentInstrument']['pgServiceTransactionId'] : "";
                        }
                        
                        $update_txn['mid']                  = isset($payment_data['data']['merchantId']) ? $payment_data['data']['merchantId'] : "";
                        $update_txn['transaction_message']  = isset($payment_data['message']) ? $payment_data['message'] : "";
                        $this->_update_payment_status($txnid, 1, $update_txn);
                    } else {
                        $this->_update_payment_status($txnid, 2, array());
                    }
                } else {
                    $minutes = (strtotime(format_date()) - strtotime($order['date_added'])) / 60;
                    if($minutes > $failed_status_time)
                    {
                        $this->_update_payment_status($txnid, 2, array());
                    }
                }
              }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "34") { 
                $orderPrefix = $this->app_config['order_prefix']['key_value'];
                $this->load->helper('payment');
                 $juspayParams = array();
                 $juspayParams["mid"]      = $this->app_config['allow_juspay']['custom_data']['api_key'];
                 $juspayParams["txnid"]    = $orderPrefix.$txnid; 
                 $mode                     = $this->app_config['allow_juspay']['custom_data']['mode'];

                 if($mode == "TEST") {
                    $endpoint_url = JUSPAY_ORDER_TEST_URL;
                } else {
                    $endpoint_url = JUSPAY_ORDER_TEST_URL;
                }
                $payment_data = get_juspay_transaction_status($mode, $juspayParams["mid"], $juspayParams["txnid"]);
                //echo '<pre>'; print_r($payment_data); exit;
                if(isset($payment_data['status']) && $payment_data['status'] == "CHARGED") {
                    $t_amount = $order['real_amount'] + $order['tds'];  
                    //additional check for amount mismatch
                    if($payment_data['amount'] == $t_amount) {  
                        $pg_response = $payment_data;
                        $update_txn                           = array();
                        $update_txn['payment_mode']           = isset($pg_response['payment_method_type']) ? $pg_response['payment_method_type'] : "";
                        $update_txn['mid']                    = isset($pg_response['merchant_id']) ? $pg_response['merchant_id'] : "";
                        $update_txn['txn_id']                 = isset($pg_response['txn_id']) ? $pg_response['txn_id'] : "";
                        $update_txn['bank_txn_id']            = isset($pg_response['txn_uuid']) ? $pg_response['txn_uuid'] : "";
                        $update_txn['txn_amount']             = isset($pg_response['amount']) ? $pg_response['amount'] : 0;
                        $update_txn['txn_date']               = isset($pg_response['date_created']) ? date("Y-m-d H:i:s",strtotime($pg_response['date_created'])) : NULL;
                        $update_txn['gate_way_name']          = "Juspay";
                        $update_txn['responce_code']          = isset($payment_data['status']) ? $payment_data['status'] : '';
                        $update_txn['transaction_message']    = isset($pg_response['message']) ? $pg_response['message'] : "";
                        $update_txn['currency']               = isset($pg_response['currency']) ? $pg_response['currency'] : "";
                        $this->_update_payment_status($txnid, 1, $update_txn);
                    } else {
                        $this->_update_payment_status($txnid, 2, array());
                    }
                } else {
                        $minutes = (strtotime(format_date()) - strtotime($order['date_added'])) / 60;
                        if($minutes > $failed_status_time)
                        {
                            $this->_update_payment_status($txnid, 2, array());
                        }
                    }
                }
            }
        }
        return true;
    }

    private function _update_payment_status($transaction_id, $status_type,$update_data=array())
    {
        if(!$transaction_id)
        {
            return false;
        }
        
        $trnxn_rec = $this->db_user->select("T.*")
                    ->from(TRANSACTION. " T")
                    ->where("T.transaction_id",$transaction_id)
                    ->get()
                    ->row_array();
        $res = 0;
        if($status_type == 1) 
        {
            if(!empty($trnxn_rec))
            {
                //change
                $orderData = $this->db->where('order_id',$trnxn_rec['order_id'])
                                        ->where('status != ',1)
                                        ->get(ORDER,1)
                                        ->row_array();
                if (!$orderData) 
                {
                    return ['error' => "No pending orders.", 'service_name' => 'update_payment_status'];
                }
                
                $paym_data = $update_data;//payment response
                if($orderData['source'] == 7 && $status_type ==1 && (empty($paym_data)))
                {
                    return array('error' => "Problem while status update.", 'service_name' => 'update_payment_status');
                }

                if($trnxn_rec['transaction_status'] == 0)
                {
                  $user_balance = $this->get_user_balance($orderData["user_id"]);
                  // For Creadit payment.
                  if ($orderData["type"] == 0 && $status_type == 1) 
                  {
                      $real_bal = $user_balance['real_amount'] + $orderData["real_amount"];
                      $bonus_bal = $user_balance['bonus_amount'] + $orderData["bonus_amount"];
                      $this->update_user_balance($orderData["user_id"], $orderData, "add");

                      //update order status
                      $this->db_user->where('order_id', $orderData["order_id"])->update(ORDER, array("status"=>"1"));

                      //update transaction status
                      $data = array();
                      $data['transaction_status'] = $status_type;
                      if(!empty($update_data) && $update_data['txn_id'] != "")
                      {
                          $data['txn_id'] = $update_data['txn_id'];
                          $data['txn_amount'] = $update_data['txn_amount'];
                          $data['txn_date'] = $update_data['txn_date'];
                          $data['bank_txn_id'] = isset($update_data['bank_txn_id']) ? $update_data['bank_txn_id'] : "";
                          $data['bank_name'] = isset($update_data['bank_name']) ? $update_data['bank_name'] : "";
                          $data['responce_code'] = isset($update_data['responce_code']) ? $update_data['responce_code'] : "";
                          $data['gate_way_name'] = isset($update_data['gate_way_name']) ? $update_data['gate_way_name'] : "";
                          $data['payment_mode'] = isset($update_data['payment_mode']) ? $update_data['payment_mode'] : "";
                          $data['transaction_message'] = isset($update_data['transaction_message']) ? $update_data['transaction_message'] : "";
                      }
                      $this->db_user->where('transaction_id', $transaction_id)->update(TRANSACTION, $data);
                      $res = $this->db_user->affected_rows();

                      $promo_code_data = $this->get_order_promo_code_details($orderData["order_id"], $orderData['user_id']);
                      if (!empty($promo_code_data) && $promo_code_data['is_processed'] == 0) {
                          $code_earning = $this->get_single_row('COUNT(promo_code_earning_id) as total', PROMO_CODE_EARNING, array("promo_code_id" => $promo_code_data["promo_code_id"], "is_processed" => "1", "user_id" => $promo_code_data["user_id"]));
                          if (isset($code_earning['total']) && $code_earning['total'] >= $promo_code_data['per_user_allowed']) {
                              //marke as failed
                              $code_arr = array("is_processed" => "2");
                              $this->update_promo_code_earning_details($code_arr, $promo_code_data["promo_code_earning_id"]);
                          } else {
                              $code_arr = array("is_processed" => "1");
                              $code_result = $this->update_promo_code_earning_details($code_arr, $promo_code_data["promo_code_earning_id"]);
                              if ($code_result) {
                                  //check promo code cash or bonus type
                                  $promo_code_cash_type = 1; //bonus
                                  $cash_type = 'Bonus';
                                  if ($promo_code_data['cash_type'] == 1) {
                                      $promo_code_cash_type = 0; //real cash
                                      $cash_type = 'Cash';
                                  }
                                  $bonus_source = 6;
                                  if ($promo_code_data['type'] == 0) {
                                      $bonus_source = 30;
                                  } else if ($promo_code_data['type'] == 1) {
                                      $bonus_source = 31;
                                  } else if ($promo_code_data['type'] == 2) {
                                      $bonus_source = 32;
                                  }
                                  $custom_data = array('cash_type'=>$cash_type,'promo_code'=>$promo_code_data['promo_code']);
                                  $this->generate_order($promo_code_data['amount_received'],$promo_code_data["user_id"],$promo_code_cash_type,"1",$bonus_source,$promo_code_data['promo_code_earning_id'],"1","1",$custom_data);
                              }
                          }
                      }

                      // gst cashback
                      $this->add_gst_cashback($orderData["order_id"]);

                      //delete user balance cache data
                      $del_cache_key = "user_balance_".$orderData["user_id"];
                      $this->delete_cache_data($del_cache_key);
                  }

                  //check first deposit for tracking user activity
                  if(!empty(ACTIVE_USER_TRACKING)){
                    $track_data = $this->db_user->select('UT.affiliate_reference_id,UT.user_track_id')
                          ->from(ORDER." O")
                          ->join(USER_TRACK." AS UT","UT.user_id = O.user_id","INNER")
                          ->where('O.user_id', $orderData["user_id"])
                          ->where('O.status', 1)
                          ->where('O.source', 7)
                          ->where('O.type', 0)
                          ->get()->result_array();
                    if(count($track_data) == 1){
                        $this->load->helper('queue_helper');
                        $content = array();
                        $content['type'] = 'DEPOSIT';
                        $content['user_track_id'] = $track_data[0]['user_track_id'];
                        $content['affiliate_reference_id'] = $track_data[0]['affiliate_reference_id'];
                        $content['amount'] = $orderData["real_amount"];
                        $content['current_date'] = format_date();
                        add_data_in_queue($content,'track_user');
                    }
                  }
                  
                  //deals
                  $this->deal_redeem_on_update_status($trnxn_rec['order_id'],$orderData);

                }
            }
        }
        
        // When Transaction has been failed , order status will also become fails
        if($status_type == 2) 
        {
          //update transaction
          $this->db_user->where('transaction_id', $transaction_id)->update(TRANSACTION, array("transaction_status"=>"2"));

          //update order status
          $sql = "UPDATE 
                      ".$this->db->dbprefix(ORDER)." AS O
                  INNER JOIN 
                      ".$this->db->dbprefix(TRANSACTION)." AS T ON T.order_id = O.order_id
                  SET 
                      O.status = T.transaction_status
                  WHERE 
                      T.transaction_id = $transaction_id AND O.status = 0 
                  ";
          $this->db_user->query($sql);
        }

        if($res){
            return $trnxn_rec;
        }   
        return FALSE;
    }
    
  /**
   * Used for get order details
   * @param int $order_id Order ID
   * @param int $user_id User ID
   * @return array
   */
  function get_order_deal_details($order_id, $user_id) {
    $sql = $this->db_user->select("D.*,DE.deal_earning_id,DE.user_id,DE.order_id,DE.is_processed", FALSE)
            ->from(DEALS_EARNING . " AS DE")
            ->join(DEALS . " AS D", "D.deal_id = DE.deal_id", "INNER")
            ->where("DE.order_id", $order_id)
            ->where("DE.user_id", $user_id)
            ->where("D.status", 1)
            ->group_by("D.deal_id")
            ->get();
    return $sql->row_array();
  }

  /**
   * Used to update deal earning details
   * @param array $data_arr
   * @param int $deal_earning_id
   * @return int
   */
  function update_deal_earning_details($data_arr, $deal_earning_id) {
    $this->db_user->where('deal_earning_id', $deal_earning_id)->update(DEALS_EARNING, $data_arr);
    return $this->db_user->affected_rows();
  }

    function deal_redeem_on_update_status($order_id,$orderData)
    {
        $deal_data = $this->get_order_deal_details($order_id, $orderData['user_id']);
        if (!empty($deal_data) && $deal_data['is_processed'] == 0) {
            $deal_earning = $this->get_single_row('COUNT(deal_earning_id) as total', DEALS_EARNING, array("deal_id" => $deal_data["deal_id"], "is_processed" => "1", "user_id" => $deal_data["user_id"]));

            $deal_arr = array("is_processed" => "1");
            $deal_result = $this->update_deal_earning_details($deal_arr, $deal_data["deal_earning_id"]);
            if ($deal_result) {
                //check deal cash or bonus type or coins
                $custom_data = json_encode(array('deal'=>$deal_data['amount']));
                if(!empty($deal_data['bonus']) && $deal_data['bonus'] > 0)
                {
                    $this->generate_order($deal_data['bonus'], $deal_data["user_id"], 1, 1, 135, $deal_data['deal_earning_id'],"1","1",$custom_data);
                }

                if(!empty($deal_data['cash']) && $deal_data['cash'] > 0)
                {
                   $this->generate_order($deal_data['cash'], $deal_data["user_id"], 0, 1, 136, $deal_data['deal_earning_id'],"1","1",$custom_data);
                }

                if(!empty($deal_data['coin']) && $deal_data['coin'] > 0)
                {
                   $this->generate_order($deal_data['coin'], $deal_data["user_id"], 2, 1, 137, $deal_data['deal_earning_id'],"1","1",$custom_data);
                }

            }
        }
    }

    /**
     * Used for get order promo code details
     * @param int $order_id Order ID
     * @param int $user_id User ID
     * @return array
     */
    function get_order_promo_code_details($order_id, $user_id) {
        $sql = $this->db_user->select("PC.*,PCE.promo_code_earning_id,PCE.user_id,PCE.order_id,PCE.amount_received,PCE.is_processed", FALSE)
                ->from(PROMO_CODE_EARNING . " AS PCE")
                ->join(PROMO_CODE . " AS PC", "PC.promo_code_id = PCE.promo_code_id", "INNER")
                ->where("PCE.order_id", $order_id)
                ->where("PCE.user_id", $user_id)
                ->group_by("PC.promo_code_id")
                ->get();
        return $sql->row_array();
    }

    /**
     * Used to update promo code earning details
     * @param array $data_arr
     * @param int $promo_code_earning_id
     * @return int
     */
    function update_promo_code_earning_details($data_arr, $promo_code_earning_id) {
        $this->db_user->where('promo_code_earning_id', $promo_code_earning_id)->update(PROMO_CODE_EARNING, $data_arr);
        return $this->db_user->affected_rows();
    }

    public function get_user_count()
    {   
        $this->db_user->select('count(user_id) AS total_user')
                      ->from(USER)
                      ->where('is_systemuser', 0)
                      ->limit('1');
        $sql = $this->db_user->get();
        $total_user = $sql->row('total_user');
        //sports
        $this->db_fantasy->select('GROUP_CONCAT(sports_name separator ", ") AS sports')
                      ->from(MASTER_SPORTS)
                      ->where('active', 1);
        $sql = $this->db_fantasy->get();
        $sports = $sql->row('sports');
        //Last 30 dys user
        $current_date = format_date();
        $previous_date = date("Y-m-d H:i:s",strtotime('-30 days',strtotime($current_date)));
        $this->db_user->select('count(user_id) AS total_user')
                      ->from(USER)
                      ->where('is_systemuser', 0)
                      ->where('added_date BETWEEN "'. date('Y-m-d', strtotime($previous_date)). '" AND "'. date('Y-m-d', strtotime($current_date)).'"')
                      ->limit('1');
        $sql = $this->db_user->get();
        $user_last_30_days = $sql->row('total_user');

        $final_data = array("version" => CODE_VERSION,"total_user" => $total_user,"user_last_30_days" => $user_last_30_days,"sports" => $sports);
        return json_encode($final_data);
    }

   public function get_playing_upcoming_match($sports_id = '')
   {
       $current_time = format_date();
       $interval = 60;//minutes
       $this->db_fantasy->select("S.season_game_uid,L.sports_id")
                        ->from(SEASON . " AS S")
                        ->join(LEAGUE . " AS L", "L.league_id = S.league_id", "INNER")
                        ->where("L.active","1")
                        ->where("S.playing_announce","0")
                        ->where("S.season_scheduled_date > DATE_SUB('{$current_time}', INTERVAL S.delay_minute MINUTE)")
                        ->where("S.season_scheduled_date <= DATE_ADD('{$current_time}', INTERVAL ".$interval." MINUTE)");

       if(!empty($sports_id))
       {
           $this->db_fantasy->where("L.sports_id", $sports_id);
       }
       $this->db_fantasy->group_by("S.season_game_uid");
       $sql = $this->db_fantasy->get();
       $result = $sql->result_array();
       return $result;
   }

    public function deposit($input_data) 
    {
        $order_status = (!empty($input_data['status'])) ? $input_data['status'] : 0; 
        $result = $this->generate_order(
                $input_data["amount"], 
                $input_data["user_id"], 
                $input_data["cash_type"], 
                $input_data["plateform"], 
                $input_data["source"], 
                $input_data["source_id"],
                $input_data["season_type"],
                $order_status
                );
        if($result)
        {

            // Add notification
            $tmp = array(); 
            $this->db = $this->db_user;
            $user_detail = $this->get_single_row('email, user_name', USER, array("user_id"=>$input_data["user_id"]));

            if($input_data["cash_type"]==3)
            {
                 $tmp["notification_type"]        = 27; // 27-Deposit Coins
            }
            else
            {
                 $tmp["notification_type"]        = 6; // 6-Deposit
            }
            
            $tmp["source_id"]                = $input_data["source_id"];
            $tmp["notification_destination"] = 7; //  Web, Push, Email
            $tmp["user_id"]                  = $input_data["user_id"];
            $tmp["to"]                       = $user_detail['email'];
            $tmp["user_name"]                = $user_detail['user_name'];
            $tmp["added_date"]               = date("Y-m-d H:i:s");
            $tmp["modified_date"]            = date("Y-m-d H:i:s");
            $tmp["content"]                  = json_encode($input_data);
            $tmp["subject"]                  = "Amount deposited.";

            $source = $input_data["source"];
            if($source != 7 && empty($input_data['ignore_deposit_noty']) )
            {
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp);
            }          
        }
        return $result;   
    }

    public function generate_order($amount, $user_id, $cash_type, $plateform, $source, $source_id,$season_type,$status=0, $custom_data='')
    {
        
        $orderData                   = array();
        $orderData["user_id"]        = $user_id;
        $orderData["source"]         = $source;
        $orderData["source_id"]      = $source_id;
        $orderData["season_type"]    = $season_type;
        $orderData["type"]           = 0;
        $orderData["date_added"]     = format_date();
        $orderData["modified_date"]  = format_date();
        $orderData["plateform"]      = $plateform;
        $orderData["status"]         = $status;
        $orderData["real_amount"]    = 0;
        $orderData["bonus_amount"]   = 0;
        $orderData["winning_amount"] = 0;
        $orderData["points"] = 0;

        if(!empty($custom_data)) {
            $orderData["custom_data"] = $custom_data;
        }

        //set status 1 for all referral/affiliate types
        /*$affiliate_order_sources = array(68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85);
        if(in_array($source,$affiliate_order_sources))
        {
            $orderData["status"] = 1;
        }*/   

        switch ($cash_type) {
            // Real Money
            case 0:
                $orderData["real_amount"] = $amount;
                break;
            // Bonus Money 
            case 1:
                $orderData["bonus_amount"] = $amount;
                break;
           // Point Balance     
            case 3:
            case 2:
                $orderData["points"] = $amount;
                break;
            case 5:
              $orderData["cb_amount"] = $amount; // gst cashback 
              break;  
            default:
                return true;

                break;
        }

        switch ($source) {
            case 0:
            case 2:
            case 4:
            case 9:
            case 41:
            case 202:
            case 221:
            case 181:
            case 251:
            case 6:
            case 30:
            case 31:
            case 32:
            case 437:
                $orderData["status"] = 1;
                break;
            case 3:
                switch ($cash_type) {
                // Real Money
                case 0:
                    $orderData["winning_amount"] = $amount;
                    break;
                // Bonus Money 
                case 1:
                    $orderData["bonus_amount"] = $amount;
                    break;
               // Point Balance     
                default:
                    break;

                
                }

                $orderData["real_amount"] = 0;
                $orderData["status"] = 1;
                break;     
        }
        $orderData['order_unique_id'] = $this->_generate_order_key();
        $this->db_user->insert(ORDER, $orderData);
        $order_id = $this->db_user->insert_id();
        
        if (!$order_id) 
        {            
            return false;
        }

        /*$user_balance = $this->get_user_balance($orderData["user_id"]);
        $real_bal     = $user_balance['real_amount'] + $orderData["real_amount"];
        $bonus_bal    = $user_balance['bonus_amount'] + $orderData["bonus_amount"];
        $winning_bal  =  $user_balance['winning_amount'] + $orderData["winning_amount"];
        $point_bal =  $user_balance['point_balance'] + $orderData["points"];   // update point balance
        */
        // Update User balance for order with completed status .
        $orderData["status"] == 1 && $this->update_user_balance($orderData["user_id"], $orderData, "add");

        return $order_id;
    }

  /**
   * used to activate blocked users account
   * @param 
   * @return boolean
  */
    public function reset_blocked_user(){
        $current_date = format_date();
        $current_date_time = strtotime($current_date." -24 hours");
        $check_exist = $this->db_user->select('COUNT(user_id) as total', FALSE)
              ->from(USER)
              ->where('blocked_date <= ', date("Y-m-d H:i:s",$current_date_time))
              ->get()
              ->row_array();
        if(!empty($check_exist) && isset($check_exist['total']) && $check_exist['total'] > 0){
            $this->db_user->where('blocked_date <= ', date("Y-m-d H:i:s",$current_date_time));
            $this->db_user->set("blocked_date",NULL);
            $this->db_user->set("otp_attempt_count","0");
            $this->db_user->update(USER);
        }
        return true;
    }

  /**
  * Function used for winning deposit
  * @param array $input_data
  */
  public function winning_deposit($input_data) 
  {
    try
    {   
        $this->db = $this->db_user;
        $order_info = $this->get_single_row('order_id', ORDER, array('user_id' => $input_data['user_id'], 'source' => $input_data['source'], 'source_id' => $input_data['source_id'], "season_type" => $input_data['season_type']));
        // If prize is not alloted to user for the selected lineup contest 
        if(empty($order_info))
        {
          $orderData                   = array();
          $orderData["user_id"]        = $input_data['user_id'];
          $orderData["source"]         = $input_data['source'];
          $orderData["source_id"]      = $input_data['source_id'];
          $orderData["reference_id"] = isset($input_data['reference_id']) ? $input_data['reference_id'] : 0;
          $orderData["season_type"]    = $input_data['season_type'];
          $orderData["type"]           = 0;
          $orderData["status"]         = $input_data['status'];
          $orderData["real_amount"]    = $input_data['real_amount'];
          $orderData["bonus_amount"]   = $input_data['bonus_amount'];
          $orderData["winning_amount"] = $input_data['winning_amount'];
          $orderData["points"] = $input_data['points'];
          $orderData["custom_data"] = isset($input_data['custom_data']) ? $input_data['custom_data'] : array();
          $orderData["plateform"]      = $input_data['plateform'];
          $orderData["date_added"]     = format_date();
          $orderData["modified_date"]  = format_date();

          $orderData['order_unique_id'] = $this->_generate_order_key();
          $this->db_user->insert(ORDER, $orderData);
          $order_id = $this->db_user->insert_id();
          if (!$order_id) 
          {            
              return false;
          }
          // Update User balance for order with completed status .
          if(($input_data['real_amount'] > 0 || $input_data['bonus_amount'] > 0 || $input_data['winning_amount'] > 0 || $input_data['points'] > 0) && $orderData["status"] == 1){
            $this->update_user_balance($orderData["user_id"], $orderData, "add");

            if($input_data['points'] > 0) {
              $this->load->helper('queue_helper');
              $coin_data = array(
                  'oprator' => 'add', 
                  'user_id' => $input_data['user_id'], 
                  'total_coins' => $input_data['points'], 
                  'bonus_date' => format_date("today", "Y-m-d")
              );
              add_data_in_queue($coin_data, 'user_coins');    
            }
          }
          return $order_id;
        }else{
          return true;
        }
    } catch (Exception $e)
    {
        //echo 'Caught exception: '.  $e->getMessage(). "\n";
    } 
  }

  /**
   * used for get contest and push in queue for pl teams
   * @param 
   * @return boolean
  */
  public function sync_bot_teams($collection_master_id){
    $current_date = format_date();
    $bot_config = isset($this->app_config['pl_allow']['custom_data']) ? $this->app_config['pl_allow']['custom_data'] : array();
    if(empty($bot_config)){
        return false;
    }

    $this->db_fantasy->select("CM.*,CS.season_id,S.season_game_uid,L.sports_id,S.playing_announce,S.playing_list,S.substitute_list,MS.max_player_per_team",FALSE)
            ->from(COLLECTION_MASTER . " CM")
            ->join(COLLECTION_SEASON.' as CS', 'CS.collection_master_id = CM.collection_master_id',"INNER")
            ->join(LEAGUE.' as L', 'L.league_id = CM.league_id',"INNER")
            ->join(SEASON.' as S', 'S.season_id = CS.season_id AND S.league_id = L.league_id',"INNER")            
            ->join(MASTER_SPORTS.' as MS', 'MS.sports_id = L.sports_id',"INNER")
            ->where('CM.season_game_count', "1")
            ->where('CM.collection_master_id', $collection_master_id);
    $sql = $this->db_fantasy->get();
    $collection_info = $sql->row_array();
    if(empty($collection_info)){
        return false;
    }else if(!empty($collection_info) && $collection_info['playing_announce'] != "1"){
        return false;
    }
   
    $this->db_fantasy->select('LM.user_id,LM.team_name,LM.lineup_master_id,LM.team_data', FALSE)
            ->from(LINEUP_MASTER.' AS LM')
            ->where('LM.is_systemuser', '1')
            ->where('LM.collection_master_id', $collection_master_id)
            ->order_by("LM.lineup_master_id","ASC");
    $team_result = $this->db_fantasy->get()->result_array();
    if(empty($team_result)){
        return false;
    }
    //match players
    $this->db_fantasy->select('P.player_id,P.player_uid,P.full_name,PT.player_team_id,PT.team_id,PT.position,PT.salary,T.team_uid,T.team_uid as team,(CASE WHEN JSON_SEARCH(S.playing_list,"one",P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_playing,PT.last_match_played,S.substitute_list', FALSE)
        ->from(SEASON.' AS S')
        ->join(PLAYER_TEAM.' AS PT', 'PT.season_id = S.season_id')
        ->join(PLAYER.' AS P', 'P.player_id = PT.player_id')
        ->join(TEAM.' AS T', 'T.team_id = PT.team_id')
        ->where("PT.player_status","1")
        ->where("PT.is_published","1")
        ->where('S.league_id', $collection_info['league_id'])
        ->where('S.season_id', $collection_info['season_id'])
        ->group_by('P.player_id')
        ->having("is_playing","1");
    $player_list = $this->db_fantasy->get()->result_array();
    if(empty($player_list)){
        return false;
    }
    $pl_list = array();
    foreach($player_list as $row){
        $pl_list[$row['position']][] = $row;
    }
    
    $sports_id = $collection_info['sports_id'];
    $this->db_fantasy->select('master_lineup_position_id,position_name,number_of_players,max_player_per_position', FALSE)
            ->from(MASTER_LINEUP_POSITION)
            ->where("position_name != ","FLEX")
            ->where('sports_id', $sports_id);
        $position = $this->db_fantasy->get()->result_array();
      $formation = array();
      foreach($position as $pos){
          $formation['min_'.strtolower($pos['position_name'])] = $pos['number_of_players'];
          $formation['max_'.strtolower($pos['position_name'])] = $pos['max_player_per_position'];
      }

    // echo "<br/>".count($pl_list);die;
    $team_count = count($team_result);
    $this->load->helper('systemuser_helper');
    $skipped = array();
    $team_list = array();
    $team_pl_list = array();
    $repeat_limit = $team_count * 2;
    $j = 0;
    for($i=0;$i<$team_count;$i++){
      if($j > $repeat_limit){
        $i = $team_count;
      }
      $team_arr = systemuser_make_team($pl_list,$collection_info['max_player_per_team'],$formation,$sports_id);
      if(!empty($team_arr) && count($team_arr) == 11){
              $team_plr = array_column($team_arr,"player_team_id");
              $c_vc = array_rand($team_plr,2);
              $c_id = $team_plr[$c_vc['0']];
              $vc_id = $team_plr[$c_vc['1']];

              $team_pls = $team_plr;
              asort($team_pls);
              $team_pls[] = $c_id."_1";
              $team_pls[] = $vc_id."_2";
              $team_pl_str = implode("_",$team_pls);
              if(!in_array($team_pl_str,$team_pl_list)){
                $tmp_team = array("pl"=>$team_plr,"c_id"=>$c_id,"vc_id"=>$vc_id);
                $team_list[$i] = $tmp_team;
                $team_pl_list[] = $team_pl_str;
              }else{
              $i--;
              }
          }else{
            $i--;
          }
          $j++;
    }

    if(!empty($team_list)){
      $i = 0;
        $final_team_list = array();
      foreach($team_result as $team){
        if(isset($team_list[$i])){
          $final_team_list[] = array("lineup_master_id"=>$team['lineup_master_id'],"team_data"=>json_encode($team_list[$i]));
        }
            $i++;
      }

      //echo "<pre>";print_r($final_team_list);die;
      if(!empty($final_team_list)){
        $team_list_arr = array_chunk($final_team_list,1000);
        foreach($team_list_arr as $teams_arr){
            $this->db_fantasy->update_batch(LINEUP_MASTER, $teams_arr, 'lineup_master_id');
        }
      }
    }


    return true;
  }

  /**
   * used for get contest and push in queue for pl teams
   * @param 
   * @return boolean
  */
  public function pl_match_teams($season_id,$lineup_event=0){
    $current_date = format_date();
    if(PL_LOG_TX){
      log_message("error","PL MATCH TEAMS FUNCTION START : MATCH : ".$season_id." | TIME : ".format_date());
    }  


    $bot_contest_deadline = SYSTEM_USER_CONTEST_DEADLINE;
    if(isset($lineup_event) && $lineup_event == 1){
      $bot_contest_deadline = 120;
    }

    $this->db->select("CM.*,CS.season_id,L.sports_id,MS.max_player_per_team",FALSE)
            ->from(COLLECTION_SEASON . " CS")
            ->join(COLLECTION_MASTER.' as CM', 'CM.collection_master_id = CS.collection_master_id',"INNER")
            ->join(LEAGUE.' as L', 'L.league_id = CM.league_id',"INNER")
            ->join(MASTER_SPORTS.' as MS', 'MS.sports_id = L.sports_id',"INNER")
            ->where('CM.season_game_count', "1")
            ->where('CS.season_id', $season_id);
    $sql = $this->db->get();
    $collection_info = $sql->row_array();
    if(empty($collection_info)){
        return false;
    }

    $bot_config = isset($this->app_config['pl_allow']['custom_data']) ? $this->app_config['pl_allow']['custom_data'] : array();
    if(empty($bot_config)){
        return false;
    }

    $collection_master_id = $collection_info['collection_master_id'];
    $sports_id = $collection_info['sports_id'];
    $season_scheduled_date = $collection_info['season_scheduled_date'];

    $this->db_fantasy->select('LM.user_id,LM.team_name,LM.lineup_master_id', FALSE)
            ->from(LINEUP_MASTER.' AS LM')
            ->where('LM.is_systemuser', '1')
            ->where('LM.collection_master_id', $collection_master_id);
    $team_result = $this->db_fantasy->get()->result_array();
    if(!empty($team_result)){
        $this->db_fantasy->select('master_lineup_position_id,position_name,number_of_players,max_player_per_position', FALSE)
            ->from(MASTER_LINEUP_POSITION)
            ->where("max_player_per_position > ","0")
            ->where('sports_id', $sports_id);
        $position = $this->db_fantasy->get()->result_array();
        $formation = array();
        foreach($position as $pos){
            $formation['min_'.strtolower($pos['position_name'])] = $pos['number_of_players'];
            $formation['max_'.strtolower($pos['position_name'])] = $pos['max_player_per_position'];
        }

        //match players
        $this->db_fantasy->select('P.player_id,P.player_uid,PT.player_team_id,PT.team_id,PT.position,PT.salary,T.team_uid,(CASE WHEN JSON_SEARCH(S.playing_list,"one",P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_playing,PT.last_match_played,S.substitute_list,S.season_game_uid', FALSE)
            ->from(SEASON.' AS S')
            ->join(PLAYER_TEAM.' AS PT', 'PT.season_id = S.season_id')
            ->join(PLAYER.' AS P', 'P.player_id = PT.player_id')
            ->join(TEAM.' AS T', 'T.team_id = PT.team_id')
            ->where("PT.player_status","1")
            ->where("PT.is_published","1")
            ->where('S.season_id', $season_id);
        $player_list = $this->db_fantasy->get()->result_array();
        if(!empty($player_list)){
            //for remove non-playing players
            $playing11 = array_unique(array_column($player_list, 'is_playing'));
            $last_played_players = array_unique(array_column($player_list, 'last_match_played'));
            if(in_array("1", $playing11)){
              $player_list = array_filter($player_list, function($v) { return $v['is_playing'] == "1"; });
              $player_list = array_values($player_list);
            }else if(!empty($last_played_players) && in_array("1",$last_played_players)){
              $temp_list = array_filter($player_list, function($v) { return $v['last_match_played'] == "1"; });
              $temp_list = array_values($temp_list);
              if(!empty($temp_list) && count($temp_list) >= 20){
                $player_list = $temp_list;
              }
            }
            
            $position_post_array = array_column($position, 'master_lineup_position_id','position_name');

            $team_data = array();
            $team_data['sport_id'] = $sports_id;
            $team_data['website_id'] = $bot_config['website_id'];
            $team_data['token'] = $bot_config['token'];
            $team_data['number_of_lineups'] = count($team_result);
            $team_data['formation'] = $formation;
            //$team_data['max_player_per_team'] = $collection_info['max_player_per_team'];
            $team_data['team_player'] = $collection_info['max_player_per_team'];
            $team_data['season_game_uid'] = $player_list['0']['season_game_uid'];
            $team_data['fixture_players'] = $player_list;
            $api_url = $bot_config['api']."/api/system-teams";
            if($bot_config['version'] == "v2"){
                unset($team_data['formation']);
                unset($team_data['fixture_players']);
                $team_data['players'] = array_column($player_list,"player_uid");
                $api_url = $bot_config['api']."/api/generator/generate_bots";
            }
            if($sports_id == SOCCER_SPORTS_ID){
                $api_url = $bot_config['api']."/api/generator/generate_soccer_bots";
            }
            $post_data_json = json_encode($team_data);
            $header = array("Content-Type:application/json", "Accept:application/json","token:".$bot_config['token']);
            $start_date = format_date();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data_json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (ENVIRONMENT !== 'production'){
                curl_setopt($ch, CURLOPT_VERBOSE, true);
            }
            $output = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($output, true);
            //echo "<pre>";print_r($result);die;
            //pl log
            if(PL_LOG_TX) {
                $log_arr = array("start_date"=>$start_date,"end_date"=>format_date(),"request"=>$team_data,"team_count"=>"0","response"=>"");
                if(isset($result['lineups']) && !empty($result['lineups'])){
                    $log_arr['team_count'] = count($result['lineups']);
                }else{
                    $log_arr['response'] = json_encode($result);
                }
                $test_data = json_encode($log_arr);
                $this->db_user->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "1"));
            }

            if(isset($result['lineups']) && !empty($result['lineups'])){
                $team_list = $result['lineups'];
                $player_list = array_column($player_list,NULL,"player_uid");
                foreach($team_result as $key=>$team){
                    $current_date = format_date();
                    if(strtotime($season_scheduled_date) > strtotime($current_date)){
                        $lineup_master_id = $team['lineup_master_id'];
                        if(isset($team_list[$key])){
                            $team_data = array();
                            if($bot_config['version'] == "v2"){
                                $tmp_data = $team_list[$key];
                                $c_id = $player_list[$tmp_data["c_id"]]['player_team_id'];
                                $vc_id = $player_list[$tmp_data["vc_id"]]['player_team_id'];
                                $player_arr = array();
                                foreach($tmp_data["pl"] as $player_uid){
                                    $player_arr[] = $player_list[$player_uid]['player_team_id'];
                                }
                                $team_data = array("c_id"=>$c_id,"vc_id"=>$vc_id,"pl"=>$player_arr);
                            }else{
                                foreach($team_list[$key] as $player){
                                    $player_team_id = $player_list[$player['player_uid']]['player_team_id'];
                                    if($player['captain'] == 1){
                                        $team_data['c_id'] = $player_team_id;
                                    }else if($player['captain'] == 2){
                                        $team_data['vc_id'] = $player_team_id;
                                    }
                                    $team_data['pl'][] = $player_team_id;
                                }
                            }
                            //echo "<pre>";print_r($team_data);die;
                            //update captain and vice captain data
                            if(!empty($team_data)){
                                $this->db_fantasy->where('lineup_master_id', $lineup_master_id);
                                $this->db_fantasy->set("team_data",json_encode($team_data));
                                $this->db_fantasy->update(LINEUP_MASTER);
                            }
                        }
                    }
                }
            }else{
                $season_info = array("match"=>$collection_info['collection_name'],"collection_id"=>$collection_info['collection_master_id'],"season_game_uid"=>$season_game_uid,"season_scheduled_date"=>$collection_info['season_scheduled_date']);
                if(!empty($season_info) && $bot_config['error_email'] != ""){
                    $this->load->model('Nodb_model');
                    $subject = SITE_TITLE.': Team Generate Issue';
                    $tmp_path = "emailer/bot_error_email";
                    $message = $this->load->view($tmp_path, array("data"=>$season_info), TRUE);
                    @$this->Nodb_model->send_email($bot_config['error_email'],$subject,$message);
                }
            }

        }
    }

    if(PL_LOG_TX){
      log_message("error","PL MATCH TEAMS FUNCTION END : MATCH : ".$season_id." | TIME : ".format_date());
    }


    return true;
  }

    /**
    * Used for get match/season details for process game to send lineup out push 
    * @param int $sports_id
    * @param int $season_game_uid
    * @return array
    */
    public function get_lineupout_game_details($season_id)
    {
        $this->db->select("S.season_id,S.season_game_uid,S.league_id,S.season_scheduled_date, S.format,L.sports_id,L.league_abbr,L.league_display_name,S.home_id,S.away_id,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away")
              ->from(SEASON." AS S")
              ->join(LEAGUE." AS L", "L.league_id = S.league_id", "INNER")
              ->join(TEAM.' T1','T1.team_id=S.home_id','INNER')
              ->join(TEAM.' T2','T2.team_id=S.away_id','INNER')
              ->where("S.season_id",$season_id)
              ->where("S.playing_announce", '1')
              ->where("S.notify_player_announce", '0')
              ->where("L.active", '1')
              ->group_by("S.season_id");
        $sql = $this->db->get();
        $matches = $sql->row_array();
        return $matches;
    }

    /**
    * Used for update season/match flag and details regarding lineup out push 
    * @param array $update_arr
    * @param array $where_arr
    * @return boolean
    */
    public function update_season_for_lineupout_push($update_arr,$where_arr)
    {
        if(empty($update_arr) || empty($where_arr))
        {
            return true;
        }

        $this->db_fantasy->where($where_arr);
        $this->db_fantasy->update(SEASON,$update_arr);
        return true;
    }

    /**
    * Used for get all users of given game details to send lineup out push 
    * @param array $post_data
    * @return array
    */
    public function get_lineupout_game_users($post_data)
    {
        $users =$this->db_fantasy->select('LM.user_id')
            ->from(LINEUP_MASTER.' LM')
            ->join(COLLECTION_MASTER. ' CM','CM.collection_master_id=LM.collection_master_id')
            ->join(COLLECTION_SEASON. ' CS','CM.collection_master_id=CS.collection_master_id')
            ->where('CS.season_id',$post_data['season_id'])
            ->where('CM.league_id',$post_data['league_id'])
            ->where('CM.season_game_count',1)
            ->where('LM.is_systemuser',0)
            ->group_by('LM.user_id')
            ->get()->result_array();

        return $users;
    }

  /**
   * Used for get user's device ids to send lineup out push 
   * @param array $user_ids
   * @return array
   */
  public function get_users_device_by_ids($user_ids)
  {
    if(empty($user_ids))
    {
      return array();
    }
    $post = $this->input->post();
    $pre_query ="(SELECT DISTINCT user_id, keys_id,device_id,device_type FROM ".$this->db_user->dbprefix(ACTIVE_LOGIN)."  WHERE device_id IS NOT NULL ORDER BY keys_id DESC)";
    $this->db_user->select('U.user_id,U.email,U.phone_no,U.phone_code,U.user_name,AL.device_id,AL.device_type')
            ->from(USER.' U')
            ->join($pre_query.' AL','AL.user_id = U.user_id','INNER');

    $user_id_chunks = array_chunk($user_ids,500);
    if(!empty($user_id_chunks))
    {
        $this->db_user->group_start();
        foreach ($user_id_chunks as $chunk_key => $chunk_arr) 
        {
            if($chunk_key == 0)
            {
              $this->db_user->where_in('U.user_id',$chunk_arr);
            }
            else
            {
              $this->db_user->or_where_in('U.user_id',$chunk_arr);
            }  
        }
        $this->db_user->group_end();
    }

    $sql = $this->db_user->where('U.is_systemuser',0)
            ->where('AL.device_id !=',NULL)
            ->group_by('U.user_id')
            ->order_by('AL.device_type',"ASC")
            ->get();
    $rs = $sql->result_array();
    return $rs;
  }

  /**
   * Internal function used for add referral benefits to users when user's friend invested on any cash contest.
   * @param string $contest_id
   * @return boolean
   */
  public function add_every_cash_contest_referral_benefits($contest_id)
  {
        //get contest basic details
        $query = $this->db_fantasy->select('entry_fee,contest_id,contest_unique_id,max_bonus_allowed')
                                ->where("contest_id",$contest_id)
                                ->where("user_id","0")
                                ->where("max_bonus_allowed < ","100")
                                ->get(CONTEST);
        $data = $query->row_array();
        //echo "<pre>";print_r($data);die;
        if(!$data)
        {
            return TRUE;
        }
        $entry_fee = $data['entry_fee'];
        $contest_id = $data['contest_id'];
        // $bonus_given = ($percentage_of_entry_fee/100*$entry_fee);

        //if contest not a cash leageue then return.    
        if($entry_fee<=0)
        {
            return TRUE;
        }

        //get affiliate master data
        $this->db = $this->db_user;
        $affililate_master_detail = $this->get_single_row('*',AFFILIATE_MASTER,array("affiliate_type"=>22,"status"=>1));
        if(empty($affililate_master_detail))
        {
          return TRUE;
        }  
        $affiliate_type = $affililate_master_detail['affiliate_type'];

        //get total joined users for this contest.
        $sql = "SELECT `user_id`,`LM`.`lineup_master_id`,`LMC`.`lineup_master_contest_id` FROM ".$this->db_fantasy->dbprefix(LINEUP_MASTER_CONTEST)." AS `LMC` INNER JOIN ".$this->db_fantasy->dbprefix(LINEUP_MASTER)." AS `LM` ON `LM`.`lineup_master_id` = `LMC`.`lineup_master_id` WHERE `contest_id` = '$contest_id' AND `LM`.`is_systemuser`=0";
        $query = $this->db_fantasy->query($sql);
        $users = $query->result_array();
        if(empty($users))
        {
            return TRUE;
        }    


        $user_id_arr = array_column($users, 'user_id');
        $user_id_chunks = array_chunk($user_id_arr,500);
        
        //echo "<pre>";print_r($user_id_info);die;
        //get all valid affiliate users and their details.
        $user_tbl = $this->db_user->dbprefix(USER);
        $query = $this->db_user->select('U1.email AS user_email,CONCAT(U1.first_name," ",U1.last_name) as name,U1.user_name as username1,CONCAT(U2.first_name," ",U2.last_name) as friendname, U2.user_name as username2, U2.email AS friend_email, UAH.user_id, UAH.friend_id, UAH.user_affiliate_history_id,UAH.source_type,U1.phone_no user_phone,U2.phone_no as friend_phone', False)
                                ->from(USER_AFFILIATE_HISTORY.' AS UAH')
                                ->join($user_tbl.' AS U1', 'U1.user_id = UAH.user_id', 'INNER', FALSE)
                                ->join($user_tbl.' AS U2', 'U2.user_id = UAH.friend_id', 'INNER', FALSE)
                                ->where('UAH.status', '1')
                                ->where_in('UAH.affiliate_type',array(1,19,20,21));
        if(!empty($user_id_chunks))
        {
          $this->db_user->group_start();
          foreach ($user_id_chunks as $chunk_key => $chunk_arr) 
          {
              if($chunk_key == 0)
              {
                $this->db_user->where_in('UAH.friend_id',$chunk_arr);
              }
              else
              {
                $this->db_user->or_where_in('UAH.friend_id',$chunk_arr);
              }  
          }
          $this->db_user->group_end();
        }                        




                         $query = $this->db_user->get();
        $refferal_data = $query->result_array();
        if(empty($refferal_data))
        {
            return TRUE;
        }    

        $user_id_info = array_column($users,NULL,'user_id');
        $current_date = format_date();
        // echo '<pre>';print_r($refferal_data);die;
        $order_source_array = array(
            //every cash contest order sources for both referred and referral users
            '22' => array(270,271,272,273,274,275)
        );

        //process each valid referral users for cash contest referrals.
        foreach ($refferal_data as $key => $value)
        {
            $user_id          = $value['user_id'];
            $friend_id        = $value['friend_id'];
           // $user_bonus_cash  = $value['user_bonus_cash'];
            $refferal_id      = $value['user_affiliate_history_id'];
            $source_type      = $value['source_type'];
            $friend_phone     = $value['friend_phone'];
            $friend_email     = (!empty($value['friend_email']))? $value['friend_email'] : "";

            //get users total played cash contests.
            $input_data = array(
              "lineup_master_contest_ids" => array($user_id_info[$friend_id]['lineup_master_contest_id']),
              "user_id" => $friend_id
            );
            $cash_contest_amount = $this->get_user_cash_contest_amount($input_data);
            //die($cash_contest_amount." okokokokokok");
            if(empty($cash_contest_amount))
            {
                continue;
            } 

            //echo '<pre>';print_r($affililate_master_detail);die("called...1");
            //check if bonus already given for this referral/affiliate type
            $this->db = $this->db_user;
            $affililate_history = $this->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$friend_id,"status"=>1,"user_id" =>$user_id,"affiliate_type"=>$affiliate_type,"contest_id"=>$contest_id));
            if(!empty($affililate_history))
            {
              echo "Already exists for user : $user_id <br>";
              continue;
            }    
                    
           // echo '<pre>';print_r($affililate_history);die("called...2");    
           //create a new entry in affiliate history for this referral type

            $rf_input_data = $affililate_master_detail;
            $rf_input_data['user_invested_amount'] = $cash_contest_amount;
            $calculated_referral_amount = $this->calculate_referral_amount($rf_input_data);
            //echo "<pre>";print_r($calculated_referral_amount);die;


            $affiliate_user_data                    = array();
            $affiliate_user_data["user_id"]         = $user_id;
            $affiliate_user_data["source_type"]     = $source_type;
            $affiliate_user_data["affiliate_type"]  = $affiliate_type;
            $affiliate_user_data["status"]          = 1;
            $affiliate_user_data["is_referral"]     = 1;
            $affiliate_user_data["created_date"]    = $current_date;
            $affiliate_user_data["friend_id"]       = $friend_id;
            $affiliate_user_data['friend_real_cash'] = (!empty($calculated_referral_amount["user_real"])) ? $calculated_referral_amount["user_real"] : 0;
            $affiliate_user_data['friend_bonus_cash'] = (!empty($calculated_referral_amount["user_bonus"])) ? $calculated_referral_amount["user_bonus"] : 0;
            $affiliate_user_data['friend_coin'] = (!empty($calculated_referral_amount["user_coin"])) ? $calculated_referral_amount["user_coin"] : 0;
            
            $affiliate_user_data['user_real_cash'] = (!empty($calculated_referral_amount["real_amount"])) ? $calculated_referral_amount["real_amount"] : 0;
            $affiliate_user_data['user_bonus_cash'] = (!empty($calculated_referral_amount["bonus_amount"])) ? $calculated_referral_amount["bonus_amount"] : 0;
            $affiliate_user_data['user_coin'] = (!empty($calculated_referral_amount["coin_amount"])) ? $calculated_referral_amount["coin_amount"] : 0;
            $affiliate_user_data["bouns_condition"] = json_encode(array());
            $affiliate_user_data["friend_mobile"]   = $friend_phone;
            $affiliate_user_data["friend_email"]    = $friend_email;
            $affiliate_user_data["contest_id"]      = $contest_id;

            $this->db_user->insert(USER_AFFILIATE_HISTORY, $affiliate_user_data);
            $affililate_history_id = $this->db_user->insert_id();
            //echo $affililate_history_id;die;
            if(empty($affililate_history_id))
            {
                continue;
            }

          /*###### Generate transactions for user who sent referral(referral code) #####*/  
            //Entry on order table for real cash type
            if($calculated_referral_amount["real_amount"] > 0)
            {
               $deposit_data_friend = array(
                                    "user_id"   => $user_id, 
                                    "amount"    => $calculated_referral_amount["real_amount"], 
                                    "source"    => $order_source_array[$affiliate_type][0],
                                    "source_id" => $affililate_history_id, 
                                    "plateform" => 1, 
                                    "cash_type" => 0,//for real cash 
                                    "season_type"=> 1,
                                    "status"    =>1,
                                    "ignore_deposit_noty"=>1
                                );
                $return_data = $this->deposit($deposit_data_friend);

                if($return_data)
                {
                    /*Add Notification*/
                    $tmp = array();
                    $input = array(
                        'friend_name' => ($value['friendname']) ? $value['friendname'] : $value['username2'], 
                        'username'    => $value['username1'],
                        'amount'      => $calculated_referral_amount["real_amount"],
                        'friend_email'=> $value['friend_email']
                    );
                    $tmp["notification_type"]        = $order_source_array[$affiliate_type][0];
                    $tmp["source_id"]                = $affililate_history_id;
                    $tmp["notification_destination"] = 3; //web, push, email
                    $tmp["user_id"]                  = $user_id;
                    $tmp["to"]                       = $value['user_email'];
                    $tmp["user_name"]                = $value['username1'];
                    $tmp["added_date"]               = $current_date;
                    $tmp["modified_date"]            = $current_date;
                    $tmp["content"]                  = json_encode($input);
                   // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                    //notification to user
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($tmp); 
                    /* END Notification */            
                }
            }

            //Entry on order table for bonus cash type
            if($calculated_referral_amount["bonus_amount"] > 0)
            {
                $deposit_data_friend = array(
                                    "user_id"   => $user_id, 
                                    "amount"    => $calculated_referral_amount["bonus_amount"], 
                                    "source"    => $order_source_array[$affiliate_type][1],
                                    "source_id" => $affililate_history_id, 
                                    "plateform" => 1, 
                                    "cash_type" => 1,//for bonus cash 
                                    "season_type"=>1,
                                    "status"    =>1,
                                    "ignore_deposit_noty"=>1
                                );
                $return_data = $this->deposit($deposit_data_friend);

                if($return_data)
                {
                    /*Add Notification*/
                    $tmp = array();
                    $input = array(
                        'friend_name' => ($value['friendname']) ? $value['friendname'] : $value['username2'], 
                        'username'    => $value['username1'],
                        'amount'      => $calculated_referral_amount["bonus_amount"],
                        'friend_email'=> $value['friend_email']
                    );
                    $tmp["notification_type"]        = $order_source_array[$affiliate_type][1];
                    $tmp["source_id"]                = $affililate_history_id;
                    $tmp["notification_destination"] = 3; //web, push
                    $tmp["user_id"]                  = $user_id;
                    $tmp["to"]                       = $value['user_email'];
                    $tmp["user_name"]                = $value['username1'];
                    $tmp["added_date"]               = $current_date;
                    $tmp["modified_date"]            = $current_date;
                    $tmp["content"]                  = json_encode($input);
                   // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                    //notification to user
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($tmp); 
                    /* END Notification */            
                }
            }

            //Entry on order table for coin type
            if($calculated_referral_amount["coin_amount"] > 0)
            {
                $deposit_data_friend = array(
                                    "user_id"   => $user_id, 
                                    "amount"    => $calculated_referral_amount["coin_amount"], 
                                    "source"    => $order_source_array[$affiliate_type][2],
                                    "source_id" => $affililate_history_id, 
                                    "plateform" => 1, 
                                    "cash_type" => 3,//for coins 
                                    "season_type"=>1,
                                    "status"    =>1,
                                    "ignore_deposit_noty"=>1
                                );
                $return_data = $this->deposit($deposit_data_friend);

                if($return_data)
                {
                    /*Add Notification*/
                    $tmp = array();
                    $input = array(
                        'friend_name' => ($value['friendname']) ? $value['friendname'] : $value['username2'], 
                        'username'    => $value['username1'],
                        'amount'      => $calculated_referral_amount["coin_amount"],
                        'friend_email'=> $value['friend_email']
                    );
                    $tmp["notification_type"]        = $order_source_array[$affiliate_type][2];
                    $tmp["source_id"]                = $affililate_history_id;
                    $tmp["notification_destination"] = 3; //web, push
                    $tmp["user_id"]                  = $user_id;
                    $tmp["to"]                       = $value['user_email'];
                    $tmp["user_name"]                = $value['username1'];
                    $tmp["added_date"]               = $current_date;
                    $tmp["modified_date"]            = $current_date;
                    $tmp["content"]                  = json_encode($input);
                   // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                    //notification to user
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($tmp); 
                    /* END Notification */            
                }
            }

          /*############# Generate transactions for user who used referral code #########*/    
            //Entry on order table for real cash type
            if($calculated_referral_amount["user_real"] > 0)
            {
                $deposit_data_friend = array(
                                    "user_id"   => $friend_id, 
                                    "amount"    => $calculated_referral_amount["user_real"], 
                                    "source"    => $order_source_array[$affiliate_type][3],
                                    "source_id" => $affililate_history_id, 
                                    "plateform" => 1, 
                                    "cash_type" => 0,//for real cash 
                                    "season_type"=> 1,
                                    "status"    =>1,
                                    "ignore_deposit_noty"=>1
                                );
                $return_data = $this->deposit($deposit_data_friend);

                if($return_data)
                {
                    /*Add Notification*/
                    $tmp = array();
                    $input = array(
                        'friend_name' => ($value['friendname'])?$value['friendname']:$value['username2'], 
                        'username'    => $value['username1'],
                        'amount'      => $calculated_referral_amount["user_real"],
                        'friend_email'=> $value['friend_email']
                    );
                    $tmp["notification_type"]        = $order_source_array[$affiliate_type][3];
                    $tmp["source_id"]                = $affililate_history_id;
                    $tmp["notification_destination"] = 3; //web,push,email
                    $tmp["user_id"]                  = $friend_id;
                    $tmp["to"]                       = $value['friend_email'];
                    $tmp["user_name"]                = $value['username2'];
                    $tmp["added_date"]               = $current_date;
                    $tmp["modified_date"]            = $current_date;
                    $tmp["content"]                  = json_encode($input);
                   // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                    //notification to user
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($tmp); 
                    /* END Notification */            
                }
            }  

            //Entry on order table for bonus cash type
            if($calculated_referral_amount["user_bonus"] > 0)
            {
               
                $deposit_data_friend = array(
                                    "user_id"   => $friend_id, 
                                    "amount"    => $calculated_referral_amount["user_bonus"], 
                                    "source"    => $order_source_array[$affiliate_type][4],
                                    "source_id" => $affililate_history_id, 
                                    "plateform" => 1, 
                                    "cash_type" => 1,//for bonus cash 
                                    "season_type"=> 1,
                                    "status"    =>1,
                                    "ignore_deposit_noty"=>1
                                );
                $return_data = $this->deposit($deposit_data_friend);

                if($return_data)
                {
                    /*Add Notification*/
                    $tmp = array();
                    $input = array(
                        'friend_name' => ($value['friendname'])?$value['friendname']:$value['username2'], 
                        'username'    => $value['username1'],
                        'amount'      => $calculated_referral_amount["user_bonus"],
                        'friend_email'=> $value['friend_email']
                    );
                    $tmp["notification_type"]        = $order_source_array[$affiliate_type][4];
                    $tmp["source_id"]                = $affililate_history_id;
                    $tmp["notification_destination"] = 3; //web,push,email
                    $tmp["user_id"]                  = $friend_id;
                    $tmp["to"]                       = $value['friend_email'];
                    $tmp["user_name"]                = $value['username2'];
                    $tmp["added_date"]               = $current_date;
                    $tmp["modified_date"]            = $current_date;
                    $tmp["content"]                  = json_encode($input);
                   // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                    //notification to user
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($tmp); 
                    /* END Notification */            
                }
            }

            //Entry on order table for coins type
            if($calculated_referral_amount["user_coin"] > 0)
            {
               
                $deposit_data_friend = array(
                                    "user_id"   => $friend_id, 
                                    "amount"    => $calculated_referral_amount["user_coin"], 
                                    "source"    => $order_source_array[$affiliate_type][5],
                                    "source_id" => $affililate_history_id, 
                                    "plateform" => 1, 
                                    "cash_type" => 3,//for coins 
                                    "season_type"=> 1,
                                    "status"    =>1,
                                    "ignore_deposit_noty"=>1
                                );
                $return_data = $this->deposit($deposit_data_friend);

                if($return_data)
                {
                    /*Add Notification*/
                    $tmp = array();
                    $input = array(
                        'friend_name' => ($value['friendname'])?$value['friendname']:$value['username2'], 
                        'username'    => $value['username1'],
                        'amount'      => $calculated_referral_amount["user_coin"],
                        'friend_email'=> $value['friend_email']
                    );
                    $tmp["notification_type"]        = $order_source_array[$affiliate_type][5];
                    $tmp["source_id"]                = $affililate_history_id;
                    $tmp["notification_destination"] = 3; //web,push
                    $tmp["user_id"]                  = $friend_id;
                    $tmp["to"]                       = $value['friend_email'];
                    $tmp["user_name"]                = $value['username2'];
                    $tmp["added_date"]               = $current_date;
                    $tmp["modified_date"]            = $current_date;
                    $tmp["content"]                  = json_encode($input);
                   // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                    //notification to user
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($tmp); 
                    /* END Notification */            
                }
            }

            
        }

      return TRUE;
    
  }

  public function process_weekly_referral_benefits()
  {

    //get affiliate master data
    $this->db = $this->db_user;
    $affililate_master_detail = $this->get_single_row('*',AFFILIATE_MASTER,array("affiliate_type"=>23,"status"=>1));
    if(empty($affililate_master_detail))
    {
      return TRUE;
    }
    //echo "<pre>";print_r($affililate_master_detail);die;  
    $affiliate_type = $affililate_master_detail['affiliate_type'];
      
    $current_week_date = strtotime(format_date());
    $week_start_date   = date("Y-m-d H:i:s", strtotime("last week monday",$current_week_date));
    $week_end_date     = date("Y-m-d", strtotime("last week sunday",$current_week_date));
    $week_end_date     = $week_end_date." 23:59:00";

    //get total joined users for last week completed contests
    $sql = "SELECT `LM`.`user_id`,GROUP_CONCAT(`LMC`.`lineup_master_contest_id`) AS lmc_ids FROM ".$this->db_fantasy->dbprefix(LINEUP_MASTER_CONTEST)." AS `LMC` INNER JOIN ".$this->db_fantasy->dbprefix(LINEUP_MASTER)." AS `LM` ON `LM`.`lineup_master_id` = `LMC`.`lineup_master_id` INNER JOIN ".$this->db_fantasy->dbprefix(CONTEST)." AS `C` ON `C`.`contest_id` = `LMC`.`contest_id` WHERE `C`.`status` = 3 AND `C`.`entry_fee` > 0 AND `LM`.`is_systemuser`=0 AND `C`.`completed_date` is NOT NULL AND `C`.`completed_date` BETWEEN '$week_start_date' AND '$week_end_date' GROUP BY `LM`.`user_id`";
    $query = $this->db_fantasy->query($sql);
    $users = $query->result_array();
    //echo $this->db_fantasy->last_query();die;
    //echo "<pre>";print_r($users);die;
    if(empty($users))
    {
        return TRUE;
    }

    $user_id_arr = array_column($users, 'user_id');
    $user_id_chunks = array_chunk($user_id_arr,500);

    //echo "<pre>";print_r($user_id_chunks);die;
    //get all valid affiliate users and their details.
    $user_tbl = $this->db_user->dbprefix(USER);
    $this->db_user->select('U1.email AS user_email,CONCAT(U1.first_name," ",U1.last_name) as name,U1.user_name as username1,CONCAT(U2.first_name," ",U2.last_name) as friendname, U2.user_name as username2, U2.email AS friend_email, UAH.user_id, UAH.friend_id, UAH.user_affiliate_history_id,UAH.source_type,U1.phone_no user_phone,U2.phone_no as friend_phone', False)
                            ->from(USER_AFFILIATE_HISTORY.' AS UAH')
                            ->join($user_tbl.' AS U1', 'U1.user_id = UAH.user_id', 'INNER', FALSE)
                            ->join($user_tbl.' AS U2', 'U2.user_id = UAH.friend_id', 'INNER', FALSE)
                            ->where('UAH.status', '1')
                            ->where_in('UAH.affiliate_type',array(1,19,20,21));
                            
    if(!empty($user_id_chunks))
    {
      $this->db_user->group_start();
      foreach ($user_id_chunks as $chunk_key => $chunk_arr) 
      {
          if($chunk_key == 0)
          {
            $this->db_user->where_in('UAH.friend_id',$chunk_arr);
          }
          else
          {
            $this->db_user->or_where_in('UAH.friend_id',$chunk_arr);
          }  
      }
      $this->db_user->group_end();
    }                        

    $query =  $this->db_user->get();
    $refferal_data = $query->result_array();
    //echo $this->db_user->last_query();die;
    //echo '<pre>';print_r($refferal_data);die;
    if(empty($refferal_data))
    {
        return TRUE;
    } 

    $user_id_info = array_column($users,NULL,'user_id');   
    //echo "<pre>";  print_r($user_id_info);die;
    $current_date = format_date();
    $order_source_array = array(
        //weekly referral order sources for both referred and referral users
        '23' => array(276,277,278,279,280,281)
    );

    //process each valid referral users for cash contest referrals.
    foreach ($refferal_data as $key => $value)
    {
        $user_id          = $value['user_id'];
        $friend_id        = $value['friend_id'];
       // $user_bonus_cash  = $value['user_bonus_cash'];
        $refferal_id      = $value['user_affiliate_history_id'];
        $source_type      = $value['source_type'];
        $friend_phone     = $value['friend_phone'];
        $friend_email     = (!empty($value['friend_email']))? $value['friend_email'] : "";


        $cash_contest_amount = 0;
        $user_lmc_ids = (!empty($user_id_info[$friend_id]['lmc_ids'])) ? explode(",", $user_id_info[$friend_id]['lmc_ids']) : array();
        if(!empty($user_lmc_ids))
        {
            //get users total played cash contests.
            $input_data = array(
              "lineup_master_contest_ids" => $user_lmc_ids,
              "user_id" => $friend_id
            );
            $cash_contest_amount = $this->get_user_cash_contest_amount($input_data);  
        }  

        //echo "$friend_id  : $cash_contest_amount <br>";
        //continue;

        
        //die($cash_contest_amount." okokokokokok");
        if(empty($cash_contest_amount) || $cash_contest_amount < $affililate_master_detail['invest_money'])
        {
            continue;
        } 

        //$week_end_date = $current_date;
        //echo '<pre>';print_r($affililate_master_detail);die("called...1");
        //check if bonus already given for this referral/affiliate type
        $this->db = $this->db_user;
        $affililate_history = $this->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$friend_id,"status"=>1,"user_id" =>$user_id,"affiliate_type"=>$affiliate_type,"created_date"=>$week_end_date));
        if(!empty($affililate_history))
        {
          echo "Already exists for User : $user_id | Friend : $friend_id <br>";
          continue;
        }    
                
       // echo '<pre>';print_r($affililate_history);die("called...2");    
       //create a new entry in affiliate history for this referral type

        $rf_input_data = $affililate_master_detail;
        $rf_input_data['user_invested_amount'] = $cash_contest_amount;
        //echo "<pre>";print_r($rf_input_data);die;
        $calculated_referral_amount = $this->calculate_referral_amount($rf_input_data);
        //echo "<pre>";print_r($calculated_referral_amount);die;

        $affiliate_user_data                    = array();
        $affiliate_user_data["user_id"]         = $user_id;
        $affiliate_user_data["source_type"]     = $source_type;
        $affiliate_user_data["affiliate_type"]  = $affiliate_type;
        $affiliate_user_data["status"]          = 1;
        $affiliate_user_data["is_referral"]     = 1;
        $affiliate_user_data["created_date"]    = $week_end_date;
        $affiliate_user_data["friend_id"]       = $friend_id;
        $affiliate_user_data['friend_real_cash'] = (!empty($calculated_referral_amount["user_real"])) ? $calculated_referral_amount["user_real"] : 0;
        $affiliate_user_data['friend_bonus_cash'] = (!empty($calculated_referral_amount["user_bonus"])) ? $calculated_referral_amount["user_bonus"] : 0;
        $affiliate_user_data['friend_coin'] = (!empty($calculated_referral_amount["user_coin"])) ? $calculated_referral_amount["user_coin"] : 0;
        
        $affiliate_user_data['user_real_cash'] = (!empty($calculated_referral_amount["real_amount"])) ? $calculated_referral_amount["real_amount"] : 0;
        $affiliate_user_data['user_bonus_cash'] = (!empty($calculated_referral_amount["bonus_amount"])) ? $calculated_referral_amount["bonus_amount"] : 0;
        $affiliate_user_data['user_coin'] = (!empty($calculated_referral_amount["coin_amount"])) ? $calculated_referral_amount["coin_amount"] : 0;
        $affiliate_user_data["bouns_condition"] = json_encode(array());
        $affiliate_user_data["friend_mobile"]   = $friend_phone;
        $affiliate_user_data["friend_email"]    = $friend_email;
        //$affiliate_user_data["contest_id"]      = $contest_id;

        $this->db_user->insert(USER_AFFILIATE_HISTORY, $affiliate_user_data);
        $affililate_history_id = $this->db_user->insert_id();
        //echo $affililate_history_id;die;
        if(empty($affililate_history_id))
        {
            continue;
        }

        /*######## Generate transactions for user who sent referral(referral code) #########*/  
        //Entry on order table for real cash type
        if($calculated_referral_amount["real_amount"] > 0)
        {
           $deposit_data_friend = array(
                                "user_id"   => $user_id, 
                                "amount"    => $calculated_referral_amount["real_amount"], 
                                "source"    => $order_source_array[$affiliate_type][0],
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 0,//for real cash 
                                "season_type"=> 1,
                                "status"    =>1,
                                "ignore_deposit_noty"=>1
                            );
            $return_data = $this->deposit($deposit_data_friend);

            if($return_data)
            {
                /*Add Notification*/
                $tmp = array();
                $input = array(
                    'friend_name' => ($value['friendname']) ? $value['friendname'] : $value['username2'], 
                    'username'    => $value['username1'],
                    'amount'      => $calculated_referral_amount["real_amount"],
                    'friend_email'=> $value['friend_email']
                );
                $tmp["notification_type"]        = $order_source_array[$affiliate_type][0];
                $tmp["source_id"]                = $affililate_history_id;
                $tmp["notification_destination"] = 3; //web, push, email
                $tmp["user_id"]                  = $user_id;
                $tmp["to"]                       = $value['user_email'];
                $tmp["user_name"]                = $value['username1'];
                $tmp["added_date"]               = $current_date;
                $tmp["modified_date"]            = $current_date;
                $tmp["content"]                  = json_encode($input);
               // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                //notification to user
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp); 
                /* END Notification */            
            }
        }

        //Entry on order table for bonus cash type
        if($calculated_referral_amount["bonus_amount"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $user_id, 
                                "amount"    => $calculated_referral_amount["bonus_amount"], 
                                "source"    => $order_source_array[$affiliate_type][1],
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 1,//for bonus cash 
                                "season_type"=>1,
                                "status"    =>1,
                                "ignore_deposit_noty"=>1
                            );
            $return_data = $this->deposit($deposit_data_friend);

            if($return_data)
            {
                /*Add Notification*/
                $tmp = array();
                $input = array(
                    'friend_name' => ($value['friendname']) ? $value['friendname'] : $value['username2'], 
                    'username'    => $value['username1'],
                    'amount'      => $calculated_referral_amount["bonus_amount"],
                    'friend_email'=> $value['friend_email']
                );
                $tmp["notification_type"]        = $order_source_array[$affiliate_type][1];
                $tmp["source_id"]                = $affililate_history_id;
                $tmp["notification_destination"] = 3; //web, push
                $tmp["user_id"]                  = $user_id;
                $tmp["to"]                       = $value['user_email'];
                $tmp["user_name"]                = $value['username1'];
                $tmp["added_date"]               = $current_date;
                $tmp["modified_date"]            = $current_date;
                $tmp["content"]                  = json_encode($input);
               // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                //notification to user
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp); 
                /* END Notification */            
            }
        }

        //Entry on order table for coin type
        if($calculated_referral_amount["coin_amount"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $user_id, 
                                "amount"    => $calculated_referral_amount["coin_amount"], 
                                "source"    => $order_source_array[$affiliate_type][2],
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 3,//for coins 
                                "season_type"=>1,
                                "status"    =>1,
                                "ignore_deposit_noty"=>1
                            );
            $return_data = $this->deposit($deposit_data_friend);

            if($return_data)
            {
                /*Add Notification*/
                $tmp = array();
                $input = array(
                    'friend_name' => ($value['friendname']) ? $value['friendname'] : $value['username2'], 
                    'username'    => $value['username1'],
                    'amount'      => $calculated_referral_amount["coin_amount"],
                    'friend_email'=> $value['friend_email']
                );
                $tmp["notification_type"]        = $order_source_array[$affiliate_type][2];
                $tmp["source_id"]                = $affililate_history_id;
                $tmp["notification_destination"] = 3; //web, push
                $tmp["user_id"]                  = $user_id;
                $tmp["to"]                       = $value['user_email'];
                $tmp["user_name"]                = $value['username1'];
                $tmp["added_date"]               = $current_date;
                $tmp["modified_date"]            = $current_date;
                $tmp["content"]                  = json_encode($input);
               // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                //notification to user
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp); 
                /* END Notification */            
            }
        }

      /*############ Generate transactions for user who used referral code #############*/    
        //Entry on order table for real cash type
        if($calculated_referral_amount["user_real"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $friend_id, 
                                "amount"    => $calculated_referral_amount["user_real"], 
                                "source"    => $order_source_array[$affiliate_type][3],
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 0,//for real cash 
                                "season_type"=> 1,
                                "status"    =>1,
                                "ignore_deposit_noty"=>1
                            );
            $return_data = $this->deposit($deposit_data_friend);

            if($return_data)
            {
                /*Add Notification*/
                $tmp = array();
                $input = array(
                    'friend_name' => ($value['friendname'])?$value['friendname']:$value['username2'], 
                    'username'    => $value['username1'],
                    'amount'      => $calculated_referral_amount["user_real"],
                    'friend_email'=> $value['friend_email']
                );
                $tmp["notification_type"]        = $order_source_array[$affiliate_type][3];
                $tmp["source_id"]                = $affililate_history_id;
                $tmp["notification_destination"] = 3; //web,push,email
                $tmp["user_id"]                  = $friend_id;
                $tmp["to"]                       = $value['friend_email'];
                $tmp["user_name"]                = $value['username2'];
                $tmp["added_date"]               = $current_date;
                $tmp["modified_date"]            = $current_date;
                $tmp["content"]                  = json_encode($input);
               // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                //notification to user
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp); 
                /* END Notification */            
            }
        }  

        //Entry on order table for bonus cash type
        if($calculated_referral_amount["user_bonus"] > 0)
        {
           
            $deposit_data_friend = array(
                                "user_id"   => $friend_id, 
                                "amount"    => $calculated_referral_amount["user_bonus"], 
                                "source"    => $order_source_array[$affiliate_type][4],
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 1,//for bonus cash 
                                "season_type"=> 1,
                                "status"    =>1,
                                "ignore_deposit_noty"=>1
                            );
            $return_data = $this->deposit($deposit_data_friend);

            if($return_data)
            {
                /*Add Notification*/
                $tmp = array();
                $input = array(
                    'friend_name' => ($value['friendname'])?$value['friendname']:$value['username2'], 
                    'username'    => $value['username1'],
                    'amount'      => $calculated_referral_amount["user_bonus"],
                    'friend_email'=> $value['friend_email']
                );
                $tmp["notification_type"]        = $order_source_array[$affiliate_type][4];
                $tmp["source_id"]                = $affililate_history_id;
                $tmp["notification_destination"] = 3; //web,push,email
                $tmp["user_id"]                  = $friend_id;
                $tmp["to"]                       = $value['friend_email'];
                $tmp["user_name"]                = $value['username2'];
                $tmp["added_date"]               = $current_date;
                $tmp["modified_date"]            = $current_date;
                $tmp["content"]                  = json_encode($input);
               // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                //notification to user
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp); 
                /* END Notification */            
            }
        }

        //Entry on order table for coins type
        if($calculated_referral_amount["user_coin"] > 0)
        {
           
            $deposit_data_friend = array(
                                "user_id"   => $friend_id, 
                                "amount"    => $calculated_referral_amount["user_coin"], 
                                "source"    => $order_source_array[$affiliate_type][5],
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 3,//for coins 
                                "season_type"=> 1,
                                "status"    =>1,
                                "ignore_deposit_noty"=>1
                            );
            $return_data = $this->deposit($deposit_data_friend);

            if($return_data)
            {
                /*Add Notification*/
                $tmp = array();
                $input = array(
                    'friend_name' => ($value['friendname'])?$value['friendname']:$value['username2'], 
                    'username'    => $value['username1'],
                    'amount'      => $calculated_referral_amount["user_coin"],
                    'friend_email'=> $value['friend_email']
                );
                $tmp["notification_type"]        = $order_source_array[$affiliate_type][5];
                $tmp["source_id"]                = $affililate_history_id;
                $tmp["notification_destination"] = 3; //web,push
                $tmp["user_id"]                  = $friend_id;
                $tmp["to"]                       = $value['friend_email'];
                $tmp["user_name"]                = $value['username2'];
                $tmp["added_date"]               = $current_date;
                $tmp["modified_date"]            = $current_date;
                $tmp["content"]                  = json_encode($input);
               // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                //notification to user
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp); 
                /* END Notification */            
            }
        }

        
    }

    return TRUE;
  }

  private function get_user_cash_contest_amount($input_array=array())
  {
     if(empty($input_array['lineup_master_contest_ids']) || empty($input_array['user_id']))
     {
        return 0;
     } 

    $lineup_master_contest_ids = $input_array['lineup_master_contest_ids'];
    $this->db_user->select("O.source_id, O.user_id,SUM(O.real_amount+O.winning_amount) AS total_amount")
                    ->from(ORDER . " O");
    $lineup_master_contest_ids_chunk = array_chunk($lineup_master_contest_ids,500);
    if(!empty($lineup_master_contest_ids_chunk))
    {
      $this->db_user->group_start();
      foreach ($lineup_master_contest_ids_chunk as $chunk_key => $chunk_arr) 
      {
          if($chunk_key == 0)
          {
            $this->db_user->where_in('O.source_id',$chunk_arr);
          }
          else
          {
            $this->db_user->or_where_in('O.source_id',$chunk_arr);
          }  
      }
      $this->db_user->group_end();
    }  

    $this->db_user->where("O.source", 1);  // 1: game join
    $this->db_user->where("O.status", 1);  // 1: completed
    $this->db_user->where("O.user_id", $input_array['user_id']); 
    $this->db_user->group_by("O.user_id");
    $sql = $this->db_user->get();
    //echo $this->db_user->last_query();die;
    $order_rs = $sql->row_array();
    return (!empty($order_rs['total_amount'])) ? $order_rs['total_amount'] : 0; 

  }

  public function get_all_config()
	{
		$sql = $this->db_user->select('*')
						->from(APP_CONFIG)
						->get();
		$rs = $sql->result_array();
		return $rs;
	}

  private function calculate_referral_amount($rf_data=array())
  {
      if($rf_data['amount_type'] != 2)
      {
          return $rf_data;
      }  

      $user_invested_amount = (!empty($rf_data['user_invested_amount'])) ? $rf_data['user_invested_amount'] : 0;

       $max_earning_amount = $rf_data['max_earning_amount'];

       $bonus_amount = (!empty($rf_data['bonus_amount'])) ? number_format(($user_invested_amount*$rf_data['bonus_amount'])/100,2) : 0;
       $rf_data['bonus_amount'] =  (!empty($max_earning_amount) && $bonus_amount > $max_earning_amount) ? $max_earning_amount : $bonus_amount;

       $real_amount = (!empty($rf_data['real_amount'])) ? number_format(($user_invested_amount*$rf_data['real_amount'])/100,2) : 0;
       $rf_data['real_amount'] =  (!empty($max_earning_amount) && $real_amount > $max_earning_amount) ? $max_earning_amount : $real_amount;

      $coin_amount = (!empty($rf_data['coin_amount'])) ? number_format(($user_invested_amount*$rf_data['coin_amount'])/100,2) : 0; 
      $rf_data['coin_amount'] =  (!empty($max_earning_amount) && $coin_amount > $max_earning_amount) ? $max_earning_amount : $coin_amount;


      $user_bonus = (!empty($rf_data['user_bonus'])) ? number_format(($user_invested_amount*$rf_data['user_bonus'])/100,2) : 0;
      $rf_data['user_bonus'] =  (!empty($max_earning_amount) && $user_bonus > $max_earning_amount) ? $max_earning_amount : $user_bonus;


      $user_real = (!empty($rf_data['user_real'])) ? number_format(($user_invested_amount*$rf_data['user_real'])/100,2) : 0;
      $rf_data['user_real'] =  (!empty($max_earning_amount) && $user_real > $max_earning_amount) ? $max_earning_amount : $user_real;

      $user_coin = (!empty($rf_data['user_coin'])) ? number_format(($user_invested_amount*$rf_data['user_coin'])/100,2) : 0;
      $rf_data['user_coin'] =  (!empty($max_earning_amount) && $user_coin > $max_earning_amount) ? $max_earning_amount : $user_coin;


      return $rf_data;
  }

  /**
   * used for tracking user record
   * @param 
   * @return boolean
  */
  public function track_user_record($data)
  {
      $update_data = array();
      $url_data = array();
      if($data['type'] == 'SIGNUP'){
        $this->db = $this->db_user;
        $track_record = $this->get_single_row('affiliate_reference_id', USER_TRACK, array('user_track_id' => $data['user_track_id']));
        $update_data = array('user_id' => $data['user_id'],
                            'signup_date' => $data['current_date']);
        $url_data = array('transaction_id' => $track_record['affiliate_reference_id']);
      } 

      if($data['type'] == 'DEPOSIT'){
        $update_data = array('deposit_date' => $data['current_date']);
        $url_data = array(
            'transaction_id' => $data['affiliate_reference_id'],
            'event' => 'dep',
            'amount' => $data['amount']
          );
      }

      $this->db_user->where_in('user_track_id', $data['user_track_id']);
      $this->db_user->update(USER_TRACK, $update_data);

      $url = '';
      if(!empty(USER_TRACKING_URL)){
        $url = USER_TRACKING_URL.http_build_query($url_data);
      }
      return $url;
  }

  /**
   * Used for download user track report
   * @param array $post_data
   * @return boolean
   */
  public function download_user_track_report($post_data){
    if(empty($post_data)){
      return false;
    }

    $this->db_user->select("UT.affiliate_reference_id,UT.landing_date,UT.signup_date,UT.deposit_date,UT.user_id,IFNULL(U.phone_no,'') as mobile", FALSE)
          ->from(USER_TRACK . ' AS UT')
          ->join(USER . ' AS U', 'ON U.user_id = UT.user_id','LEFT')
          ->order_by("UT.user_track_id","ASC");
    if(isset($post_data['from_date']) && isset($post_data['to_date'])){
      $this->db_user->where("DATE_FORMAT(UT.landing_date,'%Y-%m-%d') >= ",$post_data['from_date']);
      $this->db_user->where("DATE_FORMAT(UT.landing_date,'%Y-%m-%d') <= ",$post_data['to_date']);
    } 
    $users_list = $this->db_user->get()->result_array();
    //echo "<pre>";print_r($users_list);die;

    header("Content-type: application/csv");
    header("Content-Disposition: attachment; filename=users_list.csv");
    $fp = fopen('php://output', 'w');
    fputcsv($fp, array("affiliate_reference_id","landing_date","signup_date","deposit_date","user_id","mobile"));
    foreach ($users_list as $row) {
      fputcsv($fp, $row);
    }
    fclose($fp);
    return true;
  }
  
  
  /**
   * [process_contest_host_rake description]
   * @Summary :- This function will reset the number of winners if joined count is less than selected no. of winners.
   * @return  [type]
   */
  public function process_contest_host_rake($contest_id)
  {
        set_time_limit(0);
        $current_date = format_date();
        $contest_details = $this->db_fantasy->select("C.contest_id, C.entry_fee, C.total_user_joined, C.minimum_size, C.prize_pool, C.host_rake, C.user_id as contest_host, C.contest_access_type, C.host_rake_awarded,IFNULL(NULLIF(C.contest_title, ''),C.contest_name) as contest_name,C.currency_type")
            ->from(CONTEST." AS C")
            ->where("C.status", 3)
            ->where("C.user_id > ",0)
            ->where("contest_id",$contest_id)
            ->where("C.contest_access_type", 1)
            ->where("C.host_rake_awarded", 0)
            ->where("total_user_joined >= ", 'minimum_size', FALSE)
            ->where("season_scheduled_date < ", $current_date)
            ->get()
            ->row_array();

        if(!empty($contest_details))
        {
            if($contest_details['entry_fee'] > 0 && $contest_details['currency_type'] == 1){
                $host_rake_per_entry = ($contest_details['entry_fee']*$contest_details['host_rake'])/100;
                $total_host_rake = $host_rake_per_entry * $contest_details['total_user_joined'];
                $user_id = $contest_details['contest_host'];
                $custom_data = array("contest_name"=>$contest_details['contest_name']);
                //user txn data
                $order_data = array();
                $order_data["order_unique_id"] = $this->_generate_order_unique_key();
                $order_data["user_id"]        = $user_id;
                $order_data["source"]         = 304;
                $order_data["source_id"]      = $contest_details['contest_id'];
                $order_data["reference_id"]   = $contest_details['contest_id'];
                $order_data["season_type"]    = 1;
                $order_data["type"]           = 0;
                $order_data["status"]         = 0;
                $order_data["real_amount"]    = $total_host_rake;
                $order_data["bonus_amount"]   = 0;
                $order_data["winning_amount"] = 0;
                $order_data["points"]         = 0;
                $order_data["custom_data"]    = json_encode($custom_data);
                $order_data["plateform"]      = PLATEFORM_FANTASY;
                $order_data["date_added"]     = format_date();
                $order_data["modified_date"]  = format_date();
                
                $this->db = $this->db_user;
                $check_exist = $this->db->select("order_id")
                        ->from(ORDER)
                        ->where("source", 304)
                        ->where("reference_id", $contest_details['contest_id'])
                        ->get()
                        ->row_array();
                if(!empty($check_exist)){
                    $this->db_fantasy->where("contest_id", $contest_details['contest_id']);
                    $this->db_fantasy->update(CONTEST, array("host_rake_awarded" => 1, "modified_date" => format_date()));
                }else{
                    $this->db->insert(ORDER, $order_data);

                    $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id 
                      SET U.balance = (U.balance + O.real_amount),O.status=1 
                      WHERE O.source = 304 AND O.type=0 AND O.status=0 AND O.reference_id='".$contest_details['contest_id']."' ";
                    $this->db->query($bal_sql);
                    if ($this->db->affected_rows() > 0)
                    {
                        $this->db_fantasy->where("contest_id", $contest_details['contest_id']);
                        $this->db_fantasy->update(CONTEST, array("host_rake_awarded" => 1, "modified_date" => format_date()));
                    }
                }
                $this->delete_cache_data('user_balance_'.$user_id);
            }else{
                $this->db_fantasy->where("contest_id", $contest_details['contest_id']);
                $this->db_fantasy->update(CONTEST, array("host_rake_awarded" => 1, "modified_date" => format_date()));
            }
        }
        return true;
    }

  public function fetch_active_session_records($start, $end)
  {

      $this->load->model("Cron_nosql_model");
      $this->load->library('mongo_db');

      $mongo_date_start = $this->Cron_nosql_model->normal_to_mongo_date($start);
      $mongo_date_end = $this->Cron_nosql_model->normal_to_mongo_date($end);

      $this->mongo_db->where_gt('start_time', $mongo_date_start);
      $this->mongo_db->where_lt('end_time', $mongo_date_end);

      $result = $this->Cron_nosql_model->select_nosql(SESSION_TRACK);

      return $result;
  }

  public function update_daily_active_session_records($grouped_record)
  {
      $this->db = $this->db_user;
      $this->replace_into_batch(DAILY_ACTIVE_SESSION, $grouped_record);
  }

  public function sync_team_user_name()
  {
    $this->db_fantasy->select("LM.user_id", FALSE);
    $this->db_fantasy->from(LINEUP_MASTER.' LM');
    $this->db_fantasy->where("(LM.user_name='' OR LM.user_name IS NULL)");
    $this->db_fantasy->group_by('LM.user_id');
    $this->db_fantasy->order_by('LM.lineup_master_id', "DESC");
    $this->db_fantasy->limit(100);
    $user_record = $this->db_fantasy->get()->result_array();
    $user_ids = array_column($user_record,"user_id");
    //echo "<pre>";print_r($user_ids);die;
    if(!empty($user_ids)){
      $this->db_user->select("U.user_id,U.user_name", FALSE);
      $this->db_user->from(USER.' U');
      $this->db_user->where("(U.user_name!='' OR U.user_name IS NOT NULL)");
      $this->db_user->where_in("U.user_id",$user_ids);
      $this->db_user->group_by('U.user_id');
      $this->db_user->order_by('U.user_id', "ASC");
      $user_list = $this->db_user->get()->result_array();
      //echo "<pre>";print_r($user_list);die;
      foreach($user_list as $user){
        //update team data
        $this->db_fantasy->where('user_id', $user['user_id']);
        $this->db_fantasy->where("(user_name = '' OR user_name IS NULL)");
        $this->db_fantasy->update(LINEUP_MASTER, array('user_name' => $user['user_name']));
      }
    }
    return true;
  }

  public function get_all_users()
  {
      $this->db = $this->db_user;

      $result = $this->db->select("user_id")
                    ->from(USER)
                    ->where("status", 1)
                    ->where("is_systemuser", 0)
                    ->where_in("device_type", array(1, 2))
                    // ->where("user_id", 2689)
                    ->get()
                    ->result_array();

      $user_ids = array_unique(array_column($result, 'user_id'));

      return $user_ids;
  }

  public function get_all_active_users()
  {
      $this->db = $this->db_user;

      $result = $this->db->select("user_id")
                    ->from(DAILY_ACTIVE_SESSION)
                    // ->where("user_id", 8466)
                    ->get()
                    ->result_array();

      $user_ids = array_unique(array_column($result, 'user_id'));

      return $user_ids;
  }

  public function get_user_device_id($user_id)
  {
      $this->db = $this->db_user;

      $this->db->select("device_id")
            ->from(ACTIVE_LOGIN)
            ->where("user_id", $user_id)
            ->where("device_id != ", "")
            ->order_by("keys_id","DESC")
            ->limit(1);

      $query = $this->db->get();
      if ($query->num_rows() > 0)
      {
          return $query->row_array();
      }
  }

  public function update_uninstall_date($user_ids){
    $this->db_user->where_in('user_id',$user_ids)
    ->update(USER,array("uninstall_date"=>format_date()));
    // echo $this->db_user->last_query();exit;
    return true;
  }

    /**
      * Used to process crypto pending payment  
      * @return boolean
    */
    public function process_crypto_pending_transaction() 
    {
        $failed_status_time = 60;//time in minute
        $current_date = format_date();
        $process_date_time = date('Y-m-d H:i:s',strtotime('-2 minutes',strtotime($current_date)));
        $last_date_time = date('Y-m-d H:i:s',strtotime('-48 hours',strtotime($current_date)));
        $this->db = $this->db_user;
        $payment_gateway_ids = array("15","19");
        $this->db->select("O.order_id,O.date_added,O.order_unique_id,T.transaction_id,O.real_amount,O.user_id,O.custom_data,T.payment_gateway_id,T.pg_order_id,T.bank_txn_id")
        ->from(ORDER. " O")
        ->join(TRANSACTION. " T","T.order_id = O.order_id")
        ->where("O.status","0")
        ->where("O.source","7")
        ->where("O.type","0")
        ->where("T.transaction_status","0")
        ->where_in("T.payment_gateway_id",$payment_gateway_ids)
        ->where("O.date_added <= ",$process_date_time)
        ->where("O.date_added >= ",$last_date_time)
        ->order_by("O.order_id","DESC")
        ->limit(50);

        $query = $this->db->get();
        $result = $query->result_array();
        // echo "<pre>";print_r($result);die;
        foreach($result as $order)
        {
            if($order['order_id'])
            {
                $txnid = $order['transaction_id'];
                $bank_txn_id = $order['bank_txn_id'];

                if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "15"){
                  
                  //Checking status by crypto api
                  $pg_info = json_decode($order['custom_data'],true);
                  $crypto_tran_id = $pg_info['tran_id'];
                  $client_tran_id = $pg_info['client_tran_id'];
                  $url_params = "?tran_id=$crypto_tran_id&client_tran_id=$client_tran_id";
                  $url = CRYPTO_URL.'deposit_status_check.php'.$url_params;

                  $curl = curl_init();
                  curl_setopt_array($curl, array(
                      CURLOPT_URL => $url,
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'GET',
                  ));
                  $response = curl_exec($curl);
                  curl_close($curl);
                  $res_arr = json_decode($response,true);

                  if ($res_arr['status']=='SUCCESS') {
                      // Update status=success to transaction table by calling API

                      $ord_cus_data = json_decode($order['custom_data'],true);
                      $ord_cus_data['hash_code'] = $res_arr['hash_code'];


                      $this->_update_crypto_payment_status($txnid,1,array('custom_data'=>json_encode($ord_cus_data)));                    
                  }else{

                      $minutes = (strtotime(format_date()) - strtotime($order['date_added'])) / 60;
                      if($minutes > $failed_status_time)
                      {
                          $this->_update_payment_status($txnid, 2, array());
                      }
                  }

                }else if(isset($order['payment_gateway_id']) && $order['payment_gateway_id'] == "19"){

                  if(!empty($bank_txn_id))
                  {
                    $APP_ID   = $this->app_config['allow_btcpay']['custom_data']['app_id'];
                    $STORE_ID = $this->app_config['allow_btcpay']['custom_data']['store_id'];
                    $URL      = sprintf(BTC_URL,$STORE_ID).'/'.$bank_txn_id;
  
                    $HEADER = array(
                      'Accept: application/json',
                      'Authorization: token '.$APP_ID
                    );
  
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                      CURLOPT_URL => $URL,
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'GET',
                      CURLOPT_HTTPHEADER => $HEADER,
                    ));
                    $response = curl_exec($curl);
                    curl_close($curl);
                    $response = json_decode($response,true);
                    // print_r($response);exit;
                    if(!empty($response))
                    {
                      $success_status   = ["settled"];
                      $pending_status   = ["new","processing"];
                      $fail_status      = ["expired","invalid"];
                      $minutes = (strtotime(format_date()) - strtotime($order['date_added'])) / 60;

                      if(in_array(strtolower($response['status']),$success_status))
                      {
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                          CURLOPT_URL => $URL.'/payment-methods',
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                          CURLOPT_HTTPHEADER => $HEADER,
                        ));
                        $success_res = curl_exec($curl);
                        $success_res = json_decode($success_res,true);
                        curl_close($curl);
                        if(!empty($success_res)  && isset($success_res[0]['payments'][0]))
                        {
                          $ord_custom_data = $success_res[0]['payments'][0];
                          $success_res['custom_data'] = array();
                          $success_res['custom_data']['id']           =$ord_custom_data['id'];
                          $success_res['custom_data']['fees']         =$ord_custom_data['fee'];
                          $success_res['custom_data']['crypto_amount']=$ord_custom_data['value'];
                          $success_res['custom_data']['destination']  =$ord_custom_data['destination'];
                          $success_res['custom_data']['received_date']=$ord_custom_data['receivedDate'];
                          $success_res['custom_data']['ord_status']=$ord_custom_data['status'];
                          $success_res['custom_data'] = json_encode($success_res['custom_data']);
                          // print_r($data);exit;
                          $this->_update_crypto_payment_status($txnid, 1, $success_res);
                          //delete user balance cache data
                          $del_cache_key = "user_balance_".$order['user_id'];
                          $this->delete_cache_data($del_cache_key);
                        }

                        // $this->_update_crypto_payment_status($txnid,1,array('custom_data'=>json_encode($ord_cus_data)));
                      }else if(in_array(strtolower($response['status']),$fail_status) || $minutes > $failed_status_time){
                        
                            $this->_update_crypto_payment_status($txnid, 2, array());
                        
                      }else if(in_array(strtolower($response['status']),$pending_status)){
                        // remains pending
                        continue;
                      }else{
                        //  will remain pending until failure time passes
                        continue;
                      }
                    }

                  }else{
                    // mark transaction failure.
                    $this->_update_crypto_payment_status($txnid, 2, array());
                  }
                }
            }
        }
        return true;
    }

    private function _update_crypto_payment_status($transaction_id, $status_type,$update_data=array())
    {
        if(!$transaction_id)
        {
            return false;
        }
        
        $trnxn_rec = $this->db_user->select("T.*")
                    ->from(TRANSACTION. " T")
                    ->where("T.transaction_id",$transaction_id)
                    ->get()
                    ->row_array();
        $res = 0;
        if($status_type == 1) 
        {
            if(!empty($trnxn_rec))
            {
                //change
                $orderData = $this->db->where('order_id',$trnxn_rec['order_id'])
                                        ->where('status != ',1)
                                        ->get(ORDER,1)
                                        ->row_array();
                if (!$orderData) 
                {
                    return ['error' => "No pending orders.", 'service_name' => 'update_payment_status'];
                }
                
                if($trnxn_rec['transaction_status'] == 0)
                {
                  $user_balance = $this->get_user_balance($orderData["user_id"]);
                  // For Creadit payment.
                  if ($orderData["type"] == 0 && $status_type == 1) 
                  {
                      $real_bal = $user_balance['real_amount'] + $orderData["real_amount"];
                      $bonus_bal = $user_balance['bonus_amount'] + $orderData["bonus_amount"];
                      $this->update_user_balance($orderData["user_id"], $orderData, "add");
                  }

                  //update order status
                  $this->db_user->where('order_id', $orderData["order_id"])->update(ORDER, array("status"=>"1",'custom_data'=>$update_data['custom_data']));

                  //update transaction status
                  $data = array();
                  $data['transaction_status'] = $status_type;
                  $this->db_user->where('transaction_id', $transaction_id)->update(TRANSACTION, $data);
                  $res = $this->db_user->affected_rows();
                  
                  //for promo code balance redeem on transaction success
                  $promo_code_data = $this->get_order_promo_code_details($orderData["order_id"], $orderData['user_id']);
                  if (!empty($promo_code_data) && $promo_code_data['is_processed'] == 0) {
                      $code_earning = $this->get_single_row('COUNT(promo_code_earning_id) as total', PROMO_CODE_EARNING, array("promo_code_id" => $promo_code_data["promo_code_id"], "is_processed" => "1", "user_id" => $promo_code_data["user_id"]));
                      if (isset($code_earning['total']) && $code_earning['total'] >= $promo_code_data['per_user_allowed']) {
                          //marke as failed
                          $code_arr = array("is_processed" => "2");
                          $this->update_promo_code_earning_details($code_arr, $promo_code_data["promo_code_earning_id"]);
                      } else {
                          $code_arr = array("is_processed" => "1");
                          $code_result = $this->update_promo_code_earning_details($code_arr, $promo_code_data["promo_code_earning_id"]);
                          if ($code_result) {
                              //check promo code cash or bonus type
                              $promo_code_cash_type = 1; //bonus
                              $cash_type = 'Bonus';
                              if ($promo_code_data['cash_type'] == 1) {
                                  $promo_code_cash_type = 0; //real cash
                                  $cash_type = 'Cash';
                              }
                              $bonus_source = 6;
                              if ($promo_code_data['type'] == 0) {
                                  $bonus_source = 30;
                              } else if ($promo_code_data['type'] == 1) {
                                  $bonus_source = 31;
                              } else if ($promo_code_data['type'] == 2) {
                                  $bonus_source = 32;
                              }
                              $custom_data = array('cash_type'=>$cash_type);
                              $this->generate_order($promo_code_data['amount_received'],$promo_code_data["user_id"],$promo_code_cash_type,"1",$bonus_source,$promo_code_data['promo_code_earning_id'],"1","1",$custom_data);
                          }
                      }
                  }

                  //deals
                  $this->deal_redeem_on_update_status($trnxn_rec['order_id'],$orderData);
                }
            }
        }
        
        // When Transaction has been failed , order status will also become fails
        if($status_type == 2) 
        {
          //update transaction
          $this->db_user->where('transaction_id', $transaction_id)->update(TRANSACTION, array("transaction_status"=>"2"));

          //update order status
          $sql = "UPDATE 
                      ".$this->db->dbprefix(ORDER)." AS O
                  INNER JOIN 
                      ".$this->db->dbprefix(TRANSACTION)." AS T ON T.order_id = O.order_id
                  SET 
                      O.status = T.transaction_status
                  WHERE 
                      T.transaction_id = $transaction_id AND O.status = 0 
                  ";
          $this->db_user->query($sql);
        }

        if($res){
            return $trnxn_rec;
        }   
        return FALSE;
    }

  public function send_fixture_push($data)
  { 

    $notification = $this->db_user->select("en_subject,message")
    ->from(NOTIFICATION_DESCRIPTION)
    ->where("notification_type",$data['notification_type'])
    ->get()
    ->row_array();
    
    // selecting only those users who joined contest for 15 min before push else we will select all uses
    if($data['notification_type']==441)
    {
    $user_detail = $this->db_fantasy->select("LM.user_id, LM.user_name,CM.collection_name")
    ->from(LINEUP_MASTER .' LM')
    ->join(COLLECTION_MASTER .' CM','CM.collection_master_id = LM.collection_master_id',"INNER")
    ->where("LM.collection_master_id",$data['cmid'])
    ->where("LM.is_systemuser",0)
    ->group_by('LM.user_id')
    ->get()->result_array();

    $notification['en_subject'] = str_replace('{{collection_name}}',$user_detail[0]['collection_name'],$notification['en_subject']);
    $notification['message'] = str_replace('{{collection_name}}',$user_detail[0]['collection_name'],$notification['message']);

    }
    else
    {
      // ->where("U.uninstall_date IS NULL")
      // ->where_in("U.device_type",[1,2])
      $user_detail = $this->db_user->select("U.user_id, U.user_name")
      ->from(USER .' U')
      ->where("U.is_systemuser",0)
      ->WHERE("U.status",1)
      ->get()->result_array();

      $collection_name = $this->db_fantasy->select("CM.collection_name")
      ->from(COLLECTION_MASTER .' CM')
      ->where("CM.collection_master_id",$data['cmid'])
      ->get()->row_array();

      $notification['en_subject'] = str_replace('{{collection_name}}',$collection_name['collection_name'],$notification['en_subject']);
      $notification['message'] = str_replace('{{collection_name}}',$collection_name['collection_name'],$notification['message']);
      $notification['message'] = str_replace('{{contest_name}}',$data['contest_name'],$notification['message']);
    }
    
    if(empty($user_detail)){
        return true;
    }else if(!empty($user_detail))
    {
        $user_ids = array_unique(array_column($user_detail,'user_id'));
        $user_names = array_unique(array_column($user_detail,'user_name','user_id'));
    }

    $pre_query ="(SELECT user_id,GROUP_CONCAT(device_id) as device_ids,GROUP_CONCAT(device_type) as device_types ,keys_id,device_id,device_type FROM ".$this->db->dbprefix(ACTIVE_LOGIN)."  WHERE device_id IS NOT NULL GROUP BY user_id ORDER BY keys_id DESC)";
		$device_detail = $this->db_user->select('U.user_id,U.email,U.phone_no,U.phone_code,U.user_name,AL.device_id,AL.device_type,AL.device_ids,AL.device_types')
						->from(USER.' U')
            ->join($pre_query.' AL','AL.user_id=U.user_id','LEFT')
            ->where_in('U.user_id', $user_ids)
            ->where('U.is_systemuser',0)
						->group_by('U.user_id')
            ->get()->result_array();
    
    $new_device_detail = array();
    foreach($device_detail as $key_device => $device)
    {
        $new_device_detail[$device['user_id']] = $device;
    }

    $chunks = array_chunk($user_ids, 200);
    foreach($chunks as $key=>$chunk)
    {
        $notification_data=array();
        $notification_data["notification_type"]   = $data['notification_type'];
        $notification_data["content"]             = array(
                "custom_notification_subject"   =>$notification['en_subject'],
                "custom_notification_text"      =>$notification['message'],
                "template_data"                 => array(
                  "season_scheduled_date"             => isset($data['season_scheduled_date']) ? $data['season_scheduled_date'] : "2021-09-07" ,
                  "sports_id"                         => isset($data['sports_id']) ? $data['sports_id'] : "7" ,
                  "collection_master_id"              => isset($data['cmid']) ? $data['cmid'] : "706",
                  "home"                              => isset($data['home']) ? $data['home'] : "IND",
                  "away"                              => isset($data['away']) ? $data['away'] : "ENG",
                  "notification_type"                 =>$data['notification_type'],
              ),

        );

        foreach($chunk as $sub_key => $user)
        {
          $notification_data['device_details']=array();
            $unique_device_ids = explode(',',$new_device_detail[$user]['device_ids']);
            $u_device_types = explode(',',$new_device_detail[$user]['device_types']);
                        // print_r($u_device_types);exit;
            if(!empty($unique_device_ids[0]))
            {
              foreach($unique_device_ids as $d_key => $d_value) 
              {
                if(!empty($d_value) && !in_array($d_value,[1,2]))
                {
                  $notification_data['device_details'][] =array(
                    'device_id' => $d_value,
                    'device_type' => $u_device_types[$d_key]) ;
                }
              }
              $notification_data["content"]['custom_notification_text'] = str_replace("{{username}}",$user_names[$user],$notification['message']);
              $new_notification_data[] =  $notification_data;
            } 
        }
    }
    $data = ["push_notification_data" => $new_notification_data];
    $this->load->helper('queue_helper');
    // add_data_in_queue($data, CD_PUSH_QUEUE);
    add_data_in_queue($data, DFS_AUTO_PUSH_QUEUE);
  }

  public function points_update() {
    $current_date = format_date("today", "Y-m-d");
    $query = 'UPDATE vi_user_bonus_cash UBC INNER JOIN vi_user U ON U.user_id = UBC.user_id SET 
        UBC.total_coins= (U.point_balance+UBC.total_coins),
        bonus_date = "'.$current_date.'",
        is_coin_exp =3,
        UBC.modified_date= "'.$current_date.'"
        WHERE UBC.bonus_date = "'.$current_date.'" AND UBC.is_coin_exp !=3 AND U.point_balance>0';
      
    $this->db_user->query($query);

    $sub_query = $this->db_user->select('user_id')->distinct()->from(USER_BONUS_CASH.' UBC')->where("UBC.bonus_date",$current_date)->where("is_coin_exp",3)->get_compiled_select();
    $user_list = $this->db_user->select("U.user_id,U.point_balance")
      ->from(USER.' U')
      ->where("U.user_id NOT IN($sub_query)")
      ->where("U.point_balance>",0)
      ->get()->result_array();

      if(!empty($user_list)) {
          $allusers_chunk = array_chunk($user_list,1000);
          $update_data = array();
         
          foreach ($allusers_chunk as $sub_user_list) {
            foreach ($sub_user_list as $key=>$user){
                  $update_data[] = array(
                    "user_id"=>$user['user_id'],
                    "total_coins"=>$user['point_balance'],
                    "bonus_date"=>$current_date,
                    "modified_date"=>$current_date,
                    "is_coin_exp"=>"3",
                  );
            }
          }
          $this->db_user->insert_batch(USER_BONUS_CASH, $update_data);
      }
      $this->db_user->set('is_coin_exp', '1');
      $this->db_user->where("bonus_date",$current_date);
      $this->db_user->where("is_coin_exp",3);
      $this->db_user->update(USER_BONUS_CASH);
      return true;
  }
    
  public function update_h2h_user_level($entity_id){
    $entity_arr = explode("_",$entity_id);
    if(!empty($entity_arr) && isset($entity_arr['1']) && $entity_arr['1'] != "" && in_array($entity_arr['0'],array("game","match"))){
      $type = $entity_arr['0'];
      $ele_id = $entity_arr['1'];
      $current_date = format_date();
      $h2h_data = $this->app_config['h2h_challenge']['custom_data'];
          $h2h_group_id = isset($h2h_data['group_id']) ? $h2h_data['group_id'] : 0;

      $this->db_fantasy->select('DISTINCT LM.user_id',FALSE)
            ->from(CONTEST.' C')
            ->join(LINEUP_MASTER_CONTEST.' LMC','LMC.contest_id = C.contest_id')
            ->join(LINEUP_MASTER.' LM','LM.lineup_master_id = LMC.lineup_master_id')
            ->where('C.group_id',$h2h_group_id)
            ->where('C.status',3);
      if($type == "game"){
        $this->db_fantasy->where('C.contest_id',$ele_id);
      }else{
        $this->db_fantasy->where('C.collection_master_id',$ele_id);
      }
      $result = $this->db_fantasy->get()->result_array();
      if(!empty($result)){
        $user_ids = array_unique(array_column($result,"user_id"));
        $this->db->select('LM.user_id,COUNT(DISTINCT LMC.contest_id) as total,SUM(CASE WHEN LMC.is_winner = 1 THEN 1 ELSE 0 END) as total_win',FALSE)
            ->from(CONTEST.' C')
            ->join(LINEUP_MASTER_CONTEST.' LMC','LMC.contest_id = C.contest_id')
            ->join(LINEUP_MASTER.' LM','LM.lineup_master_id = LMC.lineup_master_id')
            ->where('C.group_id',$h2h_group_id)
            ->where_in("LM.user_id",$user_ids)
            ->where('C.status',3)
            ->group_by('LM.user_id');
        $result = $this->db->get()->result_array();
        $user_h2h_arr = array();
        foreach($result as $row){
          $user_h2h_arr[] = array("user_id"=>$row['user_id'],"total"=>$row['total'],"total_win"=>$row['total_win'],"date_modified"=>$current_date);
        }

        if(!empty($user_h2h_arr)){
          $this->db = $this->db_fantasy;
          $user_h2h_data = array_chunk($user_h2h_arr, 999);
          foreach($user_h2h_data as $h2h_data){
            $this->replace_into_batch(H2H_USERS, $h2h_data);
          }
        }
      }
    }
    return true;
  }

  public function sync_old_users_in_social() {
    $user_list = $this->db_user->select("U.user_id,U.user_unique_id,U.device_type,U.user_name,U.phone_no,U.image,U.last_ip")
      ->from(USER.' U')
      ->get()->result_array();
      if(!empty($user_list)) {
          $allusers_chunk = array_chunk($user_list,300);
          $update_data = array();
         
          foreach ($allusers_chunk as $sub_user_list) {
            $final_sync_data = array();
            $temp_sync_data = array();
            foreach ($sub_user_list as $key=>$user){
              // $user_sync_data = array();
              $user_sync_data = array(
                  "UserID"          => $user['user_id'],
                  "UserGUID"        => $user['user_unique_id'],
                  "PhoneNumber"     => $user['phone_no'],
                  "FirstName"       => $user["user_name"],
                  "LastName"        => '',
                  "ProfilePicture"  => $user["image"],
                  "IPAddress"       => $user["last_ip"],
                  "DeviceTypeID"    => $user["device_type"],
              );
              $temp_sync_data[] = $user_sync_data;
            }
            // print_r($final_sync_data);exit;
            $final_sync_data['data'] = $temp_sync_data;
            $final_sync_data['data']['Action'] = "old_sync";
            $this->load->helper('queue_helper');
            add_data_in_queue($final_sync_data, 'user_sync');
          }
      }
      return true;
  }

  /**
   * Subscribe device token to FCM topic
   */ 
  public function subscribe_fcm_token($topic)
  {
      $this->db_user->select("A.device_type,A.device_id")
              ->from(ACTIVE_LOGIN." AS A")
              ->where("A.role",1)
              ->where_in("A.device_type",array("1","2"))
              ->where("A.device_id IS NOT NULL")
              ->group_by("A.device_id")
              ->order_by("A.date_created","DESC");
      $result = $this->db_user->get()->result_array();
      if(!empty($result)){
          $result = array_column($result,"device_id");
          $result = array_chunk($result, 999);
          $this->load->helper('queue');
          //echo "<pre>";print_r($result);die;
          foreach($result as $ids){
              $content = array("type"=>"subscribe","topic"=>$topic,"ids"=>$ids);
              add_data_in_queue($content,'fcm_topic');
          }
      }
      return true;
  }

    public function get_stuck_match_list()
    {
        $current_date = format_date();
        $this->db_fantasy->select("CM.collection_master_id as cm_id,CM.collection_name as name,CM.season_scheduled_date as scheduled_date")
            ->from(COLLECTION_MASTER." AS CM")
            ->join(CONTEST." AS C","C.collection_master_id=CM.collection_master_id AND C.status=0 AND C.total_user_joined >= C.minimum_size")
            ->where("CM.season_scheduled_date < ",$current_date)
            ->where("CM.is_lineup_processed","0")
            ->where("CM.season_game_count","1")
            ->group_by("CM.collection_master_id")
            ->order_by("CM.season_scheduled_date","DESC")
            ->limit("20");
        $result = $this->db_fantasy->get()->result_array();
        return $result;
    } 

  /* Function used for generate deposit gst report
  * @param int $contest_id
  * @return boolean
  */
  public function process_deposit_gst_report()
  {
    $current_date = format_date();
    
    $portal_state_id = isset($this->app_config['allow_gst']['custom_data']['state_id']) ? $this->app_config['allow_gst']['custom_data']['state_id'] : 0;
    $gst_rate = isset($this->app_config['allow_gst']['custom_data']['gst_rate']) ? $this->app_config['allow_gst']['custom_data']['gst_rate'] : 18;
    $cgst_value = number_format(($gst_rate / 2),2,".","");//value in percentage
    $sgst_value = number_format(($gst_rate / 2),2,".","");//value in percentage
    $igst_value = $gst_rate;//value in percentage
    $this->db = $this->db_user;
    
    $source = array(7,6,30,31,32,124,136);
    $users_list = $this->db_user->select("O.order_id,O.real_amount,O.source,O.source_id,O.reference_id,O.type,O.date_added,U.user_id,U.user_name,U.pan_no,IFNULL(U.master_state_id,'0') as state_id,IFNULL(MS.name,'') as state_name,O.tds,O.custom_data",FALSE)
        ->from(ORDER . " AS O")
        ->join(USER." AS U","U.user_id = O.user_id","INNER")
        ->join(MASTER_STATE." AS MS","MS.master_state_id = U.master_state_id","LEFT")
        ->where('O.real_amount >',0)
        ->where('O.status', 1)
        ->where_in('O.source',$source)
        ->where('O.is_process_gst',0)
        ->get()
        ->result_array();
    $gst_data = array();

    $order_ids= array();
    
    foreach($users_list as $row){
      $cgst = $sgst = $igst = 0;
      $txn_type = '';
      $rake_amount = 0;
      $gst_number = '';
      $custom_data = array();
      if(isset($row['custom_data']) && !empty($row['custom_data'])){
        $custom_data = json_decode($row['custom_data'],true);
        $gst_number = isset($custom_data['get_number'])?$custom_data['get_number']:'';
      }

      if($row['source'] == 7){
        $txn_amount = $row['real_amount'];
        $rake_amount = number_format($row['tds'],"2",".","");
        $txn_type = 2; //Deposit(User)
        $match_name = 'USER DEPOSIT';
        if($row['state_id'] == $portal_state_id){
            $cgst = number_format(($rake_amount / 2),2,".","");
            $sgst = number_format(($rake_amount / 2),2,".","");
        }else{
            $igst = $rake_amount;
        }
      }else{
        $txn_amount = number_format($row['real_amount'],"2",".","");
        $match_name = '';
        $txn_type = '';
        if($row['source'] == 136){
          $match_name = isset($custom_data['deal'])?$custom_data['deal']:'';
          $match_name = 'DEAL ('.$match_name.')';

          $txn_type = 5; // deal
        }elseif($row['source'] == 6 || $row['source'] == 30 || $row['source'] == 31 || $row['source'] == 32 || $row['source'] == 124){
          $match_name = isset($custom_data['promo_code'])?$custom_data['promo_code']:'';
          $match_name = 'PROMO ('.$match_name.')';
          $txn_type = 4; // promocode
        }

        if($row['state_id'] == $portal_state_id){
          $cgst = number_format((($txn_amount/100)*$cgst_value),2,".","");
          $sgst = number_format((($txn_amount/100)*$sgst_value),2,".","");
        }else{
          $igst = number_format((($txn_amount/100)*$igst_value),2,".","");
        }
      }
        
      //echo $cgst."===".$sgst."===".$igst;die;
      
      $tmp_arr = array();
      $tmp_arr['user_id'] = $row['user_id'];
      $tmp_arr['state_id'] = $row['state_id'];
      $tmp_arr['order_id'] = $row['order_id'];
      $tmp_arr['match_id'] = $row['source'];
      $tmp_arr['contest_id'] = 0;
      $tmp_arr['lmc_id'] = 0;
      $tmp_arr['user_name'] = $row['user_name'];
      $tmp_arr['pan_no'] = $row['pan_no'];
      $tmp_arr['state_name'] = $row['state_name'];
      $tmp_arr['txn_type'] = $txn_type;
      $tmp_arr['match_name'] = $match_name;
      $tmp_arr['contest_name'] = '';
      $tmp_arr['scheduled_date'] = $row['date_added'];
      $tmp_arr['txn_date'] = $row['date_added'];
      $tmp_arr['txn_amount'] = $txn_amount;
      $tmp_arr['site_rake'] = 0;
      $tmp_arr['entry_fee'] = 0;
      $tmp_arr['rake_amount'] = $rake_amount;//taxable amount
      $tmp_arr['gst_rate'] = $gst_rate;
      $tmp_arr['cgst'] = $cgst;
      $tmp_arr['sgst'] = $sgst;
      $tmp_arr['igst'] = $igst;
      $tmp_arr['status'] = 1;
      $tmp_arr['is_invoice_sent'] = 1;
      $tmp_arr['invoice_type'] = 1;  // 1:new, 0:old 
      $tmp_arr['date_added'] = $current_date;
      $tmp_arr['gst_number'] = $gst_number;
      $gst_data[] = $tmp_arr;
      
      $order_ids[]=$row['order_id'];
    }
    //echo "<pre>";print_r($gst_data);die;
    if(!empty($gst_data)){
      //Start Transaction
      $this->db->trans_strict(TRUE);
      $this->db->trans_start();
      
      $gst_data_arr = array_chunk($gst_data, 999);
      foreach($gst_data_arr as $chunk_data){
        $this->replace_into_batch(GST_REPORT, $chunk_data);
      }
      //Trasaction End
      $this->db->trans_complete();
      if ($this->db->trans_status() === FALSE )
      {
        $this->db->trans_rollback();
      }
      else
      {
        $this->db->trans_commit();
        //For primary key setting
        $this->set_auto_increment_key(GST_REPORT,'invoice_id');
        if($order_ids){
          $this->db_user->where_in('order_id', $order_ids);
          $this->db_user->update(ORDER, array('is_process_gst' => '1'));
        }
      }
    }
    return true;
  }

  /**
   *  this function used for the add gst bonus
   */
  private function add_gst_cashback($order_id){
    if(empty($order_id)){
      return false;
    }
    // gst cashback bonus
    if(isset($this->app_config['allow_gst']) && $this->app_config['allow_gst']['key_value'] == "1"){
      $gst_bonus = isset($this->app_config['allow_gst']['custom_data']['gst_bonus'])?$this->app_config['allow_gst']['custom_data']['gst_bonus']:0;
      
      if($gst_bonus > 0){
        $orderData = $this->db->select('order_id,user_id,real_amount,tds,custom_data')
          ->where('order_id',$order_id)
          ->where('status',1)
          ->get(ORDER,1)
          ->row_array();
        if(!empty($orderData) && isset($orderData['tds']) && $orderData['tds'] > 0){
            
          $custom_data = isset($orderData['custom_data'])?json_decode($orderData['custom_data'],true):array();
          $gst_rate = isset($custom_data['gst_rate'])?$custom_data['gst_rate']:0;

          $amount = number_format((($gst_bonus/100)*$orderData['real_amount']), 2, '.', '');
          $custom_data = json_encode(array('amount'=>$orderData['real_amount'],'gst_rate'=>$gst_rate,'gst_bonus'=>$gst_bonus));
          $cash_type = 5;
          $plateform = 1;
          $bonus_source = 550;
          $source_id = $orderData['order_id'];
          
          $this->generate_order($amount,$orderData['user_id'],$cash_type,$plateform,$bonus_source,$source_id,"1","1",$custom_data);
        }
      }
    }
  } 


}
