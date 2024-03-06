<?php
/**
 * Used for return user db records
 * @package     User
 * @category    User
 */
class User_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        $this->user_db = $this->load->database('user_db', TRUE);
    }

    function __destruct() {
        $this->user_db->close();
    }

    /**
     * used for validate user session key
     * @param string $key
     * @return array
     */
    public function check_user_key($key) {
        $sql = $this->user_db->select("U.user_id,user_unique_id,date_created,first_name,last_name,email,user_name,status,U.bonus_balance,U.winning_balance,U.balance,U.referral_code,AL.role,AL.device_type,U.point_balance,AL.device_id,U.language,U.phone_no")
                ->from(ACTIVE_LOGIN . ' AS AL')
                ->join(USER . ' AS U', 'U.user_id = AL.user_id')
                ->where("AL.key", $key)
                ->where("AL.role",1)
                ->limit(1)
                ->get();
        $result = $sql->row_array();
        //echo $this->user_db->last_query();
        return ($result) ? $result : array();
    }



    /**
     * used for get user balance
     * @param int $user_id
     * @return array
     */
    public function get_user_balance($user_id) {
        if (!$user_id)
            return false;

        $result = $this->user_db->select("user_id,balance,bonus_balance,winning_balance,point_balance")
                        ->from(USER)
                        ->where("user_id", $user_id)
                        ->limit(1)
                        ->get()->row_array();

        return array(
            "bonus_amount" => $result["bonus_balance"] ? $result["bonus_balance"] : 0,
            "real_amount" => $result["balance"] ? $result["balance"] : 0,
            "winning_amount" => $result["winning_balance"] ? $result["winning_balance"] : 0,
            "point_balance" => $result["point_balance"] ? $result["point_balance"] : 0
        );
    }

    /**
     * Used for generate order unique id
     * @return string
     */
    public function _generate_order_key() {
        $this->load->helper('security');
        do {
            $salt = do_hash(time() . mt_rand());
            $new_key = substr($salt, 0, 10);
        }

        // Already in the DB? Fail. Try again
        while (self::_order_key_exists($new_key));

        return $new_key;
    }

    /**
     * Used for check order unique id exist or not
     * @param string $key
     * @return int
     */
    private function _order_key_exists($key) {
        $this->user_db->select('order_id');
        $this->user_db->where('order_unique_id', $key);
        $this->user_db->limit(1);
        $query = $this->user_db->get(ORDER);
        $num = $query->num_rows();
        if ($num > 0) {
            return true;
        }
        return false;
    }


    /**
     * Used for save data in oder table on contest join
     * @param array $data_arr
     * @return array
     */
    public function withdraw($data_arr) {
        $current_date = format_date();
        $amount = $data_arr["amount"];
        $order_data = array();
        $order_data["user_id"] = $data_arr['user_id'];
        $order_data["source"] = $data_arr['source'];
        $order_data["source_id"] = $data_arr['source_id'];
        $order_data["reference_id"] = isset($data_arr['reference_id']) ? $data_arr['reference_id'] : 0;
        $order_data["type"] = 1;
        $order_data["date_added"] = $current_date;
        $order_data["modified_date"] = $current_date;
        $order_data["plateform"] = 1;
        $order_data["season_type"] = 1;
        $order_data["status"] = 1;
        $order_data["real_amount"] = 0;
        $order_data["bonus_amount"] = 0;
        $order_data["winning_amount"] = 0;
        $order_data["points"] = 0;
        $order_data["withdraw_method"] = 0;
        $order_data["reason"] = !empty($data_arr['reason']) ? $data_arr['reason'] : '';
        $custom_data = array();

        if(isset($data_arr['custom_data']))
        {
            $custom_data = $data_arr['custom_data'];
        }
        $user_balance = $this->get_user_balance($data_arr["user_id"]);
        //bonus_amount,real_amount,winning_amount,point_balance
        if ($data_arr['cash_type'] != 3 && ($user_balance["real_amount"] + $user_balance["bonus_amount"] + $user_balance["winning_amount"] < $amount)) {
            return array("result" => "0", "message" => "Insufficient amount in winnings!");
        }

        if ($data_arr['cash_type'] == 3 && $user_balance["point_balance"] < $amount) {
            return array("result" => "0", "message" => "Insufficient Coins");
        }
        if($data_arr['cash_type'] == 3){
            $order_data["points"] = $amount;
        }else{
            // Use both cash (bouns+real) only for join game. 
            $max_bonus_allowed = MAX_BONUS_PERCEN_USE;
            if (isset($data_arr['max_bonus_allowed']) && $data_arr['max_bonus_allowed'] != "") {
                $max_bonus_allowed = $data_arr['max_bonus_allowed'];
            }
            $max_bouns = ($amount * $max_bonus_allowed) / 100;
            // Deduct Max 10% of entry fee from bonus amount.
            $order_data["bonus_amount"] = $max_bouns;
            if ($max_bouns > $user_balance["bonus_amount"]) {
                $order_data["bonus_amount"] = $user_balance["bonus_amount"];
            }
            
            if($order_data["source"] == CONTEST_JOIN_SOURCE && MAX_CONTEST_BONUS > 0 && $order_data["bonus_amount"] > MAX_CONTEST_BONUS) { // Game join max bonus cap check
                $order_data["bonus_amount"] = MAX_CONTEST_BONUS;
            }
                
            $remain_amt = number_format(($amount - $order_data["bonus_amount"]),"2",".","");
            // Deduct reamining amount from real amount.
            $order_data["real_amount"] = $remain_amt;
            if ($remain_amt > $user_balance["real_amount"]) {
                $order_data["real_amount"] = $user_balance["real_amount"];
            }
            $remain_amt = number_format(($remain_amt - $order_data["real_amount"]),"2",".","");
            if ($remain_amt > 0 && $remain_amt > $user_balance["winning_amount"]) {
                return array("result" => "0", "message" => "Insufficient amount in winnings!");
            }

            // Deduct Remaining amount from winning amount
            $order_data["winning_amount"] = number_format($remain_amt,"2",".","");
            $order_data["real_amount"] = number_format($order_data["real_amount"],2,".","");
            $order_data["bonus_amount"] = number_format($order_data["bonus_amount"],2,".","");

           
            //calculate profit
            if(isset($data_arr['source']) && $data_arr['source'] == CONTEST_JOIN_SOURCE){
                $site_rake = DEFAULT_SITE_RAKE;
                if (isset($data_arr['site_rake']) && $data_arr['site_rake'] != "") {
                    $site_rake = $data_arr['site_rake'];
                }
                $total_real_entry = $order_data["real_amount"] + $order_data["winning_amount"];
                $profit = number_format(($total_real_entry * $site_rake)/100,2,'.','');
                $custom_data['profit'] = $profit;
            }
        }

        $order_data['custom_data'] = json_encode($custom_data);
        $order_data['order_unique_id'] = $this->_generate_order_key();
        try
        {
            //Start Transaction
            $this->user_db->trans_strict(TRUE);
            $this->user_db->trans_start();

            $this->user_db->insert(ORDER, $order_data);
            $order_id = $this->user_db->insert_id();
            if ($order_id) {
                //deduct user balance after contest join
                $this->user_db->where('user_id', $data_arr['user_id']);
                if($order_data["real_amount"] > 0){
                    $this->user_db->where('balance >= ', $order_data["real_amount"]);
                }
                if($order_data["bonus_amount"] > 0){
                    $this->user_db->where('bonus_balance >= ', $order_data["bonus_amount"]);
                }
                if($order_data["winning_amount"] > 0){
                    $this->user_db->where('winning_balance >= ', $order_data["winning_amount"]);
                }
                if($order_data["points"] > 0){
                    $this->user_db->where('point_balance >= ', $order_data["points"]);
                }
                $this->user_db->set('balance', 'balance - ' . $order_data["real_amount"], FALSE);
                $this->user_db->set('bonus_balance', 'bonus_balance - ' . $order_data["bonus_amount"], FALSE);
                $this->user_db->set('winning_balance', 'winning_balance - ' . $order_data["winning_amount"], FALSE);
                $this->user_db->set('point_balance', 'point_balance - ' . $order_data["points"], FALSE);
                $this->user_db->update(USER);
                $afftected_rows = $this->user_db->affected_rows();
                if($afftected_rows == 0 && $data_arr['is_promo_code'] ==0){
                    throw new Exception("Something went wrong during contest join.");
                }else{
                    //Trasaction End
                    $this->user_db->trans_complete();
                    if ($this->user_db->trans_status() === FALSE )
                    {
                      throw new Exception("Something went wrong during contest join.");
                    }
                    else
                    {
                        $this->user_db->trans_commit();
                        if($order_data["bonus_amount"] > 0) {
                            $this->load->helper('queue_helper');
                            // $bonus_data = array('oprator' => 'withdraw', 'user_id' => $order_data["user_id"], 'total_bonus' => $order_data["bonus_amount"], 'bonus_date' => format_date("today", "Y-m-d"));
                            // add_data_in_queue($bonus_data, 'user_bonus');
                        }
                        return array("result" => "1", "message" => "Contest joined successfully.","order_id"=>$order_id);
                    }
                }
            } else {
                throw new Exception("Something went wrong during contest join.");
            }
        } catch (Exception $e)
        {
            $this->user_db->trans_rollback();
            return array("result" => "0", "message" => "Something went wrong during contest join.");
        }
    }


    /**
     * used for get lobby banner referral amount
     * @param 
     * @return array
     */
    public function get_lobby_banner_referral_data() {
        $lobby_data = array();
        $lobby_data['2'] = array("amount" => 0, "currency_type" => CURRENCY);
        $lobby_data['3'] = array("amount" => 0, "currency_type" => CURRENCY);

        $this->user_db->select("affiliate_master_id,real_amount,bonus_amount,coin_amount,user_real,user_bonus,user_coin");
        $this->user_db->from(AFFILIATE_MASTER);
        $this->user_db->where_in("affiliate_master_id", array(LOBBY_REFER_BANNER_AFF_ID, LOBBY_DEPOSIT_BANNER_AFF_ID));
        $result = $this->user_db->get()->result_array();

        $result = array_column($result, NULL, "affiliate_master_id");
        $refer_data = isset($result[LOBBY_REFER_BANNER_AFF_ID]) ? $result[LOBBY_REFER_BANNER_AFF_ID] : array();
        if (!empty($refer_data)) {
            if (isset($refer_data['real_amount']) && $refer_data['real_amount'] > 0) {
                $lobby_data['2']['amount'] = $refer_data['real_amount'];
            } else if (isset($refer_data['bonus_amount']) && $refer_data['bonus_amount'] > 0) {
                $lobby_data['2']['amount'] = $refer_data['bonus_amount'];
                $lobby_data['2']['currency_type'] = "Bonus";
            } else if (isset($refer_data['coin_amount']) && $refer_data['coin_amount'] > 0) {
                $lobby_data['2']['amount'] = $refer_data['coin_amount'];
                $lobby_data['2']['currency_type'] = "Coin";
            }
        }

        $deposit_data = isset($result[LOBBY_DEPOSIT_BANNER_AFF_ID]) ? $result[LOBBY_DEPOSIT_BANNER_AFF_ID] : array();
        if (!empty($deposit_data)) {
            if (isset($deposit_data['user_real']) && $deposit_data['user_real'] > 0) {
                $lobby_data['3']['amount'] = $deposit_data['user_real'];
            } else if (isset($deposit_data['user_bonus']) && $deposit_data['user_bonus'] > 0) {
                $lobby_data['3']['amount'] = $deposit_data['user_bonus'];
                $lobby_data['3']['currency_type'] = "Bonus";
            } else if (isset($deposit_data['user_coin']) && $deposit_data['user_coin'] > 0) {
                $lobby_data['3']['amount'] = $deposit_data['user_coin'];
                $lobby_data['3']['currency_type'] = "Coin";
            }
        }

        return $lobby_data;
    }

   /**
     * used for get contest participant users list
     * @param int $user_id
     * @return array
     */
    public function get_participant_user_details($user_ids,$allow_xp_point=0) {

        $select = "U.user_id,IFNULL(U.user_name, 'U.first_name') AS name,IFNULL(U.image,'') AS image";

         $this->user_db->select($select)
                ->from(USER.' U');

        if($allow_xp_point == 1)
        {
            $this->user_db->select('XU.level_id,XLP.level_number,B.badge_id, B.badge_name,B.badge_icon');
            $this->user_db->join(XP_USERS.' XU','XU.user_id=U.user_id','LEFT');
            $this->user_db->join(XP_LEVEL_POINTS.' XLP','XLP.level_pt_id=XU.level_id','LEFT');
            $this->user_db->join(XP_LEVEL_REWARDS.' R','R.level_number=XLP.level_number','LEFT');
            $this->user_db->join(XP_BADGE_MASTER.' B','B.badge_id=R.badge_id','LEFT');
        }

        $result = $this->user_db->where_in("U.user_id", $user_ids)
                ->order_by('U.user_name', 'ASC')
                ->get()
                ->result_array();

        return $result;
    }

   /**
    * Used to check promo code
    * @param array $input
    * @return array
    */
    public function check_promo_code_details($promo_code) {
        $current_date = format_date("today", "Y-m-d H:i:s");
        $sql = $this->user_db->select("PC.*,count(PCE.promo_code_earning_id) as total_used", FALSE)
                ->from(PROMO_CODE . " AS PC")
                ->join(PROMO_CODE_EARNING . " AS PCE", "PCE.promo_code_id = PC.promo_code_id AND PCE.is_processed='1' AND PCE.user_id='" . $this->user_id . "'", "LEFT")
                ->where("status", "1")
                ->where("promo_code", $promo_code)
                ->where("start_date <= ", $current_date)
                ->where("expiry_date >= ", $current_date)
                ->group_by("PC.promo_code_id")
                ->get();
        return $sql->row_array();
    }

    /**
     * Used to check and get user promo code earn info
     * @param array $input
     * @return array
     */
    public function get_user_promo_code_earn_info($where_condition) {
        $sql = $this->user_db->select("*", FALSE)
                ->from(PROMO_CODE_EARNING)
                ->where($where_condition)
                ->get();
        return $sql->row_array();
    }

    /**
     * Used for save promo code earning
     * @param array $data_arr
     * @param int $promo_code_earning_id
     * @return array
     */
    public function save_promo_code_earning_details($data_arr) {

        $this->user_db->insert(PROMO_CODE_EARNING, $data_arr);
        return $this->user_db->insert_id();
    }

    /**
     * Used for update promo code earning
     * @param array $data_arr
     * @param int $promo_code_earning_id
     * @return array
     */
    public function update_promo_code_earning_details($data_arr, $promo_code_earning_id) {
        $this->user_db->where('promo_code_earning_id', $promo_code_earning_id)->update(PROMO_CODE_EARNING, $data_arr);
        return $this->db->affected_rows();
    }

     /**
     * Used for save data in oder table on withdraw request
     * @param array $data_arr
     * @return array
     */
    function withdraw_coins($data_arr) {
        $current_date = format_date();
        $amount = $data_arr["amount"];
        $order_data = array();
        $order_data["user_id"] = $data_arr['user_id'];
        $order_data["source"] = $data_arr['source'];
        $order_data["source_id"] = $data_arr['source_id'];
        $order_data["type"] = 1;
        $order_data["date_added"] = $current_date;
        $order_data["modified_date"] = $current_date;
        $order_data["plateform"] = 1;
        $order_data["season_type"] = 1;
        $order_data["status"] = $data_arr['status'];
        $order_data["real_amount"] = 0;
        $order_data["bonus_amount"] = 0;
        $order_data["winning_amount"] = 0;
        $order_data["points"] = $amount;
        $order_data["withdraw_method"] = 1;
        $order_data["reason"] = !empty($data_arr['reason']) ? $data_arr['reason'] : '';
        if (!empty($data_arr['email'])) {
            $order_data["email"] = $data_arr['email'];
        }
        $user_balance = $this->get_user_balance($data_arr["user_id"]);

        $custom_data = array();

        if(isset($data_arr['custom_data']))
        {
            $custom_data = $data_arr['custom_data'];
        }
        if(isset($data_arr['entry_type']))
        {
            $custom_data['entry_type'] = $data_arr['entry_type'];
        }
        
        $order_data["custom_data"] = json_encode($custom_data);

        //If requested amount is greater then total amount.
        if (($user_balance["point_balance"] < $amount)) {
            throw new Exception($this->lang->line("prediction_insufficent_coins_err"));
        }

        $this->db = $this->user_db;
        $this->db->trans_start();
        $order_data['order_unique_id'] = $this->_generate_order_key();
        $this->db->insert(ORDER, $order_data);
        $order_id = $this->db->insert_id();
        if ($order_id) {
            $real_bal = $user_balance['real_amount'] - $order_data["real_amount"];
            $bonus_bal = $user_balance['bonus_amount'] - $order_data["bonus_amount"];
            $winning_bal = $user_balance['winning_amount'] - $order_data["winning_amount"];
            $point_bal = $user_balance['point_balance'] - $order_data["points"];
            // update user balance!
            $this->update_user_balance($order_data["user_id"], $order_data, 'withdraw');
            //$this->update_user_balance($order_data["user_id"], $real_bal, $bonus_bal, $winning_bal, $point_bal);

            $withdraw_method_arr = array('0' => 'NA', '1' => 'Bank', '2' => 'PayTm', '3' => 'Paypal', '4' => 'Admin');
            $withdraw_method = ($withdraw_method_arr[$order_data["withdraw_method"]]) ? $withdraw_method_arr[$order_data["withdraw_method"]] : '';
            if (!empty($withdraw_method)) {
                $this->load->model('User_nosql_model');

                $input["reason"] = 'Admin ';
                $input['payment_option'] = $withdraw_method;
                if (isset($order_data['reason']) && $order_data['reason'] != "") {
                    $input['reason'] = $order_data['reason'];
                }

                $user_detail = $this->get_single_row('email, user_name', USER, array("user_id" => $data_arr["user_id"]));
                $input["user_name"] = $user_detail['user_name'];
                $input["user_id"] = $user_detail['user_name'];
                $input["amount"] = $amount;

                if(!empty($data_arr['event']))
                {
                    $input["event"] = $data_arr['event'];
                }

                $tmp = array();
                $tmp["source_id"] = $order_data["source_id"];
                $tmp["user_id"] = $data_arr['user_id'];
                $tmp["to"] = $user_detail['email'];
                $tmp["user_name"] = $user_detail['user_name'];
                $tmp["added_date"] = $current_date;
                $tmp["modified_date"] = $current_date;
                $tmp["content"] = json_encode($input);

                // $notify_data = $this->notify_type_by_source($data_arr["source"]);

                // if(!empty($notify_data))
                // {
                //     $tmp['notification_type'] = $notify_data['notification_type'];
                //     $tmp['subject'] = $notify_data['subject'];
                //     $tmp['notification_destination'] = $notify_data['notification_destination'];
                //     $this->User_nosql_model->send_notification($tmp);
                // }    
               
            }
        } else {
            $this->db->trans_rollback();
            throw new Exception('Something went wrong during contest join.');
        }
        $this->db->trans_complete();
        $this->db->trans_strict(FALSE);
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();

            $user_balance['point_balance'] = $point_bal;
            $user_balance['order_id'] = $order_id;
            return $user_balance;
        }
    }

 /**  Used to update user balance 
     * @param int $user_id
     * @param float $real_bal
     * @param float $bonus_bal
     * @param float $winning_bal
     * @param float $point_bal  
     * @return int
     */
    function update_user_balance($user_id, $balance_arr,$oprator='add') {
        if(empty($balance_arr)){
            return false;
        }
        if(isset($balance_arr['real_amount']) && $balance_arr['real_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('balance', 'balance - '.$balance_arr['real_amount'], FALSE);
            }else{
                $this->db->set('balance', 'balance + '.$balance_arr['real_amount'], FALSE);
            }
            if(isset($balance_arr['source']) && $balance_arr['source'] == "7" && $oprator == 'add'){
                $this->db->set('total_deposit', 'total_deposit + '.$balance_arr['real_amount'], FALSE);
            }
        }
        if(isset($balance_arr['bonus_amount']) && $balance_arr['bonus_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('bonus_balance', 'bonus_balance - '.$balance_arr['bonus_amount'], FALSE);
            }else{
                $this->db->set('bonus_balance', 'bonus_balance + '.$balance_arr['bonus_amount'], FALSE);
            }
        }
        if(isset($balance_arr['winning_amount']) && $balance_arr['winning_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('winning_balance', 'winning_balance - '.$balance_arr['winning_amount'], FALSE);
            }else{
                $this->db->set('winning_balance', 'winning_balance + '.$balance_arr['winning_amount'], FALSE);
            }
        }
        if(isset($balance_arr['points']) && $balance_arr['points'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('point_balance', 'point_balance - '.$balance_arr['points'], FALSE);
            }else{
                $this->db->set('point_balance', 'point_balance + '.$balance_arr['points'], FALSE);
            }
        }
        $this->db->where('user_id', $user_id);
        $this->db->update(USER);
        return $this->db->affected_rows();
    }

    public function get_users_by_ids($user_ids)
	{
		if(empty($user_ids))
		{
			return array();
		}
		$sql = $this->user_db->select('user_id,email,phone_no,phone_code,user_name,user_unique_id,image')
						->from(USER)
						->where_in('user_id', $user_ids)
						->get();
		$result = $sql->result_array();
		return $result;
    }
    
     /**
     * Used for get sports hub data
     * @param NA
     * @return Array
     */
    public function get_sports_hub()
    {
        return $this->user_db->select("*")
                    ->from(SPORTS_HUB)
                    ->order_by('display_order','ASC')
                    ->get()->result_array();
    }

    function get_all_table_data($table, $select = '*', $where = "") {
        $this->user_db->select($select);
        $this->user_db->from($table);
        if ($where != "") {
            $this->user_db->where($where);
        }
        $query = $this->user_db->get();
        return $query->result_array();
    }

    function get_admin_access_list($admin_id)
    {
        $sql = $this->user_db->select("AD.admin_id,AD.privilege,AD.status,AD.email,AD.role as admin_role,CONCAT_WS(' ', AD.firstname, AD.lastname) AS full_name,ARR.right_ids,AD.access_list,AD.firstname as first_name,AD.username", FALSE)
        ->from(ADMIN . " AS AD")
        ->join(ADMIN_ROLES_RIGHTS . " AS ARR", "ARR.role_id = AD.role", "inner")
        ->where("AD.admin_id", $admin_id)
        ->where("AD.status", 1)
        ->get();
        $data = $sql->row_array();
        return $data;
    }

    /**
	 * [check_user_key description]
	 * @MethodName check_user_key
	 * @Summary This function used for check user key exist
	 * @param      [varchar]  [Login key]
	 * @return     [array]
	 */
	public function check_user_key_admin($key)
	{
		$sql = $this->user_db->select("*")
						->from(ACTIVE_LOGIN)
						->where("key",$key)
						->where("role",2)
						->get();
		$result = $sql->row_array();
		return ($result)?$result:array();
	}

     /** 
     * used to get app config data 
     * @param string    $select
     * @return	array
     */
	function get_app_config_data($select = '*') {
		$this->user_db->select($select);
		$this->user_db->from(APP_CONFIG);
		$query = $this->user_db->get();
        return $query->result_array();
    }

    public function check_self_exclusion($self_exclusion_data) {
        
        $user_id = $self_exclusion_data['user_id'];
        $contest_ids = $self_exclusion_data['contest_ids'];
        $max_limit = $self_exclusion_data['max_limit'];
        $entry_fee = $self_exclusion_data['entry_fee'];
        log_message('error', "check_self_exclusion user_id => ".$user_id." max_limit => ".$max_limit." entry_fee => ".$entry_fee);
        log_message('error', "contest_ids => ".json_encode($contest_ids));
        #user contest joined entry fee
        //SELECT SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount FROM `vi_order` where source=1 and user_id=3062 and reference_id in(3645,3675,3676,3678,3679,3681,3684,3685,3710,3734,3752,3810,3811,3812,3827,3828,3831,3836)
        $sql = $this->user_db->select('SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount')
        ->from(ORDER)
        ->where('user_id', $user_id)
        ->where_in('source', array(1,240))
        ->where('status', 1)
        ->where_in('reference_id', $contest_ids)
        ->get();
        $result = $sql->row_array();        
        if(!empty($result)) {
            $this->user_db->select('max_limit');
            $this->user_db->from(USER_SELF_EXCLUSION);
            $this->user_db->where('user_id', $user_id);
            $this->user_db->limit(1);
            $query = $this->user_db->get();            
            if($query->num_rows() > 0) {
                $row = $query->row_array();	
                if(!empty($row['max_limit'])) {
                    $max_limit = $row['max_limit'];
                }	               
            } 

            $total_invested_amount = 0;
            if(!empty($result['real_amount'])) {
                $total_invested_amount = $result['real_amount'];
            }
            if(!empty($result['winning_amount'])) {
                $total_invested_amount = $total_invested_amount + $result['winning_amount'];
            }
            log_message('error', "total_invested_amount => ".$total_invested_amount." max_limit => ".$max_limit);
            if($total_invested_amount > 0 ) {
                #user contest refund entry fee
                #SELECT SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount FROM `vi_order` where source=2 and user_id=3062 and reference_id in(2617,2618,2619,2620,2621,2633)
                $sql = $this->user_db->select('SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount')
                        ->from(ORDER)
                        ->where('user_id', $user_id)
                        ->where('status', 1)
                        ->where_in('source', array(2,242))
                        ->where_in('reference_id', $contest_ids)
                        ->get();
                $result = $sql->row_array();  
                if(!empty($result)) {
                    $total_refund_amount = 0;
                    if(!empty($result['real_amount'])) {
                        $total_refund_amount = $result['real_amount'];
                    }
                    if(!empty($result['winning_amount'])) {
                        $total_refund_amount = $total_refund_amount + $result['winning_amount'];
                    }
                    $total_invested_amount = $total_invested_amount - $total_refund_amount;
                }

                #user contest winning amount
                #SELECT SUM(winning_amount) as winning_amount FROM `vi_order` where source=3 and user_id=3062 and reference_id in(2617,2618,2619,2620,2621,2633)
                $sql = $this->user_db->select('SUM(winning_amount) as winning_amount')
                        ->from(ORDER)
                        ->where('user_id', $user_id)
                        ->where('status', 1)
                        ->where_in('source', array(3,241))
                        ->where_in('reference_id', $contest_ids)
                        ->get();
                $result = $sql->row_array();  
                if(!empty($result)) {
                    $total_winning_amount = $result['winning_amount'];
                    if(!empty($total_winning_amount)) {
                        $total_invested_amount = $total_invested_amount - $total_winning_amount;
                    }
                    
                }
            }

            log_message('error', "final_invested_amount => ".$total_invested_amount." max_limit => ".$max_limit);
            if($total_invested_amount > 0  && ($total_invested_amount + $entry_fee) > $max_limit) {
                return 0;
            } else if($total_invested_amount > 0  && ($total_invested_amount + $entry_fee) <= $max_limit) {
                return 1;
            } else if($total_invested_amount <= 0  && ($total_invested_amount + $entry_fee) <= $max_limit) {                
                return 1;
            } else {
                return 0;
            }
            //$total_invested_amount > 0 //loss
        }
        return 1;
    }

    /**
	 * [get_user_detail_by_user_id description]
	 * @MethodName get_user_detail_by_user_id
	 * @Summary This function used for get user Detail
	 * @param      [int]  [User Id]
	 * @return     [array]
	 */
	public function get_user_detail_by_user_id($user_id)
	{
		$result = $this->user_db->select("''as 'team_name',U.master_state_id,U.master_country_id,U.facebook_id,U.address,U.user_unique_id,U.user_id,U.first_name,U.last_name,U.image,MC.country_name,U.balance,U.email,DATE_FORMAT(U.dob,'%d-%b-%Y') as dob,U.city,U.language,U.status,U.added_date,U.user_name,MS.name as state_name,IFNULL(U.zip_code,'--') As zip_code,IFNULL(U.phone_no,'--') AS phone_no,IFNULL(U.gender,'--') As gender",FALSE)
						->from(USER." AS U")
						->join(MASTER_COUNTRY." AS MC","MC.master_country_id = U.master_country_id","left")
						->join(MASTER_STATE." AS MS","MS.master_state_id = U.master_state_id","left")						
						//->join(TEAM." AS T","T.team_id=  U.team_id","left")
						->where("U.user_id",$user_id)
						->get()->row_array();
		return ($result)?$result:array();
	}

    /**
     * This function used get user referral count
     * @param      
     * @return     [int]
     */
    function get_user_referral_count($user_id) {
        $affiliate_type = array(1,19,20,21);
        $query = $this->user_db->select(
                "sum(case when status = 1 and affiliate_type IN(" . implode(',', $affiliate_type) . ") then 1 else 0 end) as total_joined", FALSE)
                ->from(USER_AFFILIATE_HISTORY)
                ->where("user_id", $user_id)
                ->where("is_referral", 1)
                ->get();
        $num = $query->num_rows();
        $total_joined = 0;
        if($num > 0) {
            $row= $query->row_array();
            $total_joined   = $row['total_joined'];            
        }        
        return $total_joined;
    }

     /**
    * Function used for get banned state list
    * @param void
    * @return array
    */
    public function get_banned_state_list()
    {
        $this->user_db->select("BS.master_state_id as state_id,MS.name,MS.pos_code", FALSE);
        $this->user_db->from(BANNED_STATE." AS BS");
        $this->user_db->join(MASTER_STATE." AS MS","MS.master_state_id = BS.master_state_id","INNER");
        $this->user_db->order_by('BS.id','ASC');
        $result = $this->user_db->get()->result_array();
        return $result;

    }

    public function get_country_state_ids() {
       return $this->user_db->select('MC.master_country_id as country_id,MS.master_state_id as state_id,MS.pos_code as state_code,MC.pos_code as country_code')
                ->from(MASTER_COUNTRY. " MC")
                ->join(MASTER_STATE. " MS","MS.master_country_id=MC.master_country_id")
                ->get()->result_array();
    } 

}
