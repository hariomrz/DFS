<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once 'Cron_model.php';
class Coins_model extends Cron_model {
    
    public $db_user ;
    public $db_fantasy ;
    public $testingNode = FALSE;

    public function __construct() 
    {
       	parent::__construct();
		$this->db_user		= $this->load->database('db_user', TRUE);
		$this->db_fantasy	= $this->load->database('db_fantasy', TRUE);
    }

    /** 
     * Used to get notification type details by source id
     * @param int $source
     * @return array
     */
    function notify_type_by_source($source) {
        $arr = array(
            '144' => ["notification_type" => 138, "subject" => $this->finance_lang["deposit_email_subject"], "notification_destination" => 3]
            );

            if(isset($arr[$source]))
            {
               return  $arr[$source];
            }
            else
            {
                return 0;
            }

    }

    /**
     * @method daily_streak_notification  
     * @uses to send push notification for daily streak
     * @since Dec 2019
     * 
     * ***/
    public function daily_streak_notification()
    {
        $this->load->helper('queue');
        $current_date = format_date('today','Y-m-d');

        $master_daily_streak = $this->get_daily_streak_coins();

        if(empty($master_daily_streak))
        {
            return true;
        }

        $previos_date = date('Y-m-d',strtotime($current_date.' -1 day'));
        $from_date = $previos_date.' 00:00:00';
        $to_date = $current_date.' 23:59:59';
        $pre_sql ="(SELECT user_id,device_id,device_type FROM ".$this->db_user->dbprefix(ACTIVE_LOGIN)." ORDER BY keys_id DESC) ";
        $result = $this->db_user->select('O.user_id,al.device_id,O.custom_data,O.date_added,al.device_type,U.phone_code,U.phone_no')
        ->from(USER.' U')
        ->join(ORDER.' O','O.user_id=U.user_id')
        ->join( $pre_sql.' al','O.user_id=al.user_id')
        ->where('O.source',144)
        ->where('O.date_added>=',$from_date)
        ->where('O.date_added<=',$to_date)
        ->group_by('O.user_id')
        ->where('al.device_id IS NOT NULL')
        ->get()->result_array();

        echo "<pre>";
        echo $this->db_user->last_query();
                    print_r($result);
        $master_coins  =json_decode($master_daily_streak[0]['daily_coins_data']);
       $days = count($master_coins);
      
      $subject = "Continue your streak 🤩";
       $text = "Don't miss the #ordinal# day streak bonus, login now and get #coins# coins Claim now 💰";
      //$text = "hello Ankit12";
        foreach($result as $row)
        {
            if(!empty($row['custom_data']))
            {
                $custom_data = json_decode($row['custom_data'],true);

                if($days == $custom_data['day_number'])
                {
                    continue;
                }

                $ordinal = ordinal($custom_data['day_number']+1);
                $day_coins =$master_coins[$custom_data['day_number']];
                $text = str_replace('#ordinal#',$ordinal,$text);
                $text = str_replace('#coins#',$day_coins,$text);
               
                $content = array(
                    'user_id' => $row['user_id'],
                    'SITE_TITLE' => SITE_TITLE,
                    'device_id' => $row['device_id'],
                    'device_type' => $row['device_type']
                    );
    
                $content['custom_notification_subject'] = $subject ;
                $content['custom_notification_text'] = $text ;
                //
                $notification = array();
                $notification['user_id']                  = $row['user_id'];
                $notification['custom_notification_text']        = $text;
                $notification['custom_notification_subject'] = $subject;
                $notification['content']                  = json_encode($content);
             
                //if(!empty($row['device_id']))
                {
                    $notification['device_type'] = $row['device_type'];
                    $notification['device_id'] = $row['device_id'];
                    print_r($notification);
                    rabbit_mq_push($notification, 'push');

                  
                    //$push_notification_data[] = $notification;
                }
            } 
        }
       
        die('dfd');
    }

     /**
     * @method daily_streak_notification  
     * @uses to send push notification for daily streak
     * @since Dec 2019
     * 
     * ***/
    public function daily_streak_sms()
    {
        $this->load->helper('queue');
        $current_date = format_date();

        $master_daily_streak = $this->get_daily_streak_coins();

        $previos_date = date('Y-m-d',strtotime($current_date.' -2 day'));
        $from_date = $previos_date.' 00:00:00';
        $to_date = $previos_date.' 23:59:59';
        $pre_sql ="(SELECT user_id,device_id,device_type FROM ".$this->db_user->dbprefix(ACTIVE_LOGIN)." ORDER BY keys_id DESC) ";
        $result = $this->db_user->select('O.user_id,al.device_id,O.custom_data,O.date_added,al.device_type,U.phone_code,U.phone_no')
        ->from(USER.' U')
        ->join(ORDER.' O','O.user_id=U.user_id')
        ->join( $pre_sql.' al','O.user_id=al.user_id')
        ->where('O.source',144)
        ->where('O.date_added>=',$from_date)
        ->where('O.date_added<=',$to_date)
        ->group_by('O.user_id')
        ->get()->result_array();

        $master_coins  =json_decode($master_daily_streak[0]['daily_coins_data']);
        $days = count($master_coins);
      
     
     
      $text = "Dont miss the your #ordinal# day streak bonus, login now and get #coins# coins. Click here #url# to claim";
      $url=WEBSITE_URL.'/rewards';
      $text = str_replace('#url#',$url,$text);
      //$text = "hello Ankit12";
        foreach($result as $row)
        {
            if(!empty($row['custom_data']))
            {
                $custom_data = json_decode($row['custom_data'],true);

                if($days == $custom_data['day_number'])
                {
                    continue;
                }

                $ordinal = ordinal($custom_data['day_number']+1);
                $day_coins =$master_coins[$custom_data['day_number']];
                $text = str_replace('#ordinal#',$ordinal,$text);
                $text = str_replace('#coins#',$day_coins,$text);
               
                //send sms 
                $sms_temp = array();
               // $sms_temp['number'] = array(8358854524);
                $sms_temp['number'] = array($row['phone_no']);
                $sms_temp['message_body'] =$text;
                $sms_temp['user_id'] = $row['user_id'];
                $sms_temp_array[] = $sms_temp;
                
            } 
        }

        if(!empty($sms_temp_array))
        {
            rabbit_mq_push($sms_temp_array, 'coins_sms');
        }

    }

    function get_daily_streak_coins(){
        
        $result = $this->db_user->select('SS.*')
        ->from(MODULE_SETTING.' MS')
        ->join(SUBMODULE_SETTING.' SS','SS.module_setting_id=MS.module_setting_id')
        ->where('MS.module_setting_id',1)
        ->where('SS.submodule_setting_id',4)
        ->where('SS.status',1)->get()->result_array();

       return $result;
    }

     /**
     * @method coin_redeem_sms  
     * @uses to send sms for coin redeem
     * @since Dec 2019
     * 
     * ***/
    public function coin_redeem_sms()
    {
        $this->load->helper('queue');
       
        $result = $this->db_user->select('user_id,point_balance,phone_code,phone_no')
        ->from(USER)
        ->where('point_balance>',COINS_BALANCE_CLAIM)
        ->get()->result_array();

       // echo $this->db_user->last_query();die;
//Your coins balance is 142,692 as on 26-Dec-2019. Click here https://cricjam.com/coinbalance to claim rewards like bonus cash, gift coupons and more.
      $sample_text = "Your coins balance is #coins# as on #date#. Click here #url# to claim rewards like bonus cash, gift coupons and more.";
      $current_date = format_date();
      $url=WEBSITE_URL.'/rewards';
      $date = date('d-M-Y',strtotime($current_date));
      $sample_text = str_replace('#date#',$date,$sample_text);
      $sample_text = str_replace('#url#',$url,$sample_text);
      //$text = "hello Ankit12";
        foreach($result as $row)
        {
            $text = str_replace('#coins#',$row['point_balance'],$sample_text);
            //send sms 
            $sms_temp = array();
            $sms_temp['number'] = array($row['phone_no']);
            $sms_temp['message_body'] =$text;
            $sms_temp['user_id'] = $row['user_id'];
            $sms_temp_array[] = $sms_temp;   
        }

        if(!empty($sms_temp_array))
        {
            rabbit_mq_push($sms_temp_array, 'coins_sms');
        }

    }

    /**
     * @method coin_redeem_notification  
     * @uses to send push notification for coin redeem
     * @since Dec 2019
     * 
     * ***/
    public function coin_redeem_notification()
    {
        //get daily streak status and add condition
        $daily_streak = $this->get_daily_streak_coins();
        if(empty($daily_streak))
        {
            return true;
        }

        $this->load->helper('queue');
        $current_date = format_date();
        
        $pre_sql ="(SELECT DISTINCT user_id,device_id,device_type FROM ".$this->db_user->dbprefix(ACTIVE_LOGIN)." ORDER BY keys_id DESC) ";
        $result = $this->db_user->select('U.user_id,al.device_type,al.device_id,U.point_balance,U.phone_code,U.phone_no')
        ->from(USER.' U')
        ->join( $pre_sql.' al','U.user_id=al.user_id')
        ->group_by('al.user_id')
        ->where('al.device_id IS NOT NULL')
        ->get()->result_array();

      
        $subject = "Balance Alert ‼️";
        //      "Your coin balance is 580.Earn more to redeem bigger rewards. (dollar eye, dollar bundle)"
        $text = "Your coin balance is #coins# as on #date#. Earn more to redeem bigger rewards.🤑💰";
        $current_date = format_date();
        $date = date('d-M-Y',strtotime($current_date));
        $text = str_replace('#date#',$date,$text);
      //$text = "hello Ankit12";
        foreach($result as $row)
        {
                $updated_text = str_replace('#coins#',$row['point_balance'],$text);
                $content = array(
                    'user_id' => $row['user_id'],
                    'SITE_TITLE' => SITE_TITLE,
                    'device_id' => $row['device_id'],
                    'device_type' => $row['device_type']
                    );
    
                $content['custom_notification_subject'] = $subject ;
                $content['custom_notification_text'] = $updated_text ;
                //
                $notification = array();
                $notification['user_id']                  = $row['user_id'];
                $notification['custom_notification_text']        = $updated_text;
                $notification['custom_notification_subject'] = $subject;
                $notification['content']                  = json_encode($content);
             
                //if(!empty($row['device_id']))
                {
                    $notification['device_type'] = $row['device_type'];
                    if(isset($row['device_type']) && $row['device_type']=='1' )
                    {
                        $notification['deviceIDS'] = $row['device_id'];
                    }
    
                    if(isset($row['device_type']) && $row['device_type']=='2' )
                    {
                        $notification['ios_device_ids'] = $row['device_id'];
                    }
                    $notification['notification_type'] = 0;
                    //print_r($notification);
                    rabbit_mq_push($notification, 'push');
                    //$push_notification_data[] = $notification;
                } 
        }
       
       
    }

    public function get_last_daily_streak_coin($user_id)
    {
        $result = $this->db_user->select('*')
        ->from(ORDER)
        ->where('source',144)
        ->where('status',1)
        ->where('user_id',$user_id)
        ->order_by('date_added','DESC')
        ->limit(1)
        ->get()->row_array();

        return $result;    
    }

    function dateDiffInDays($date1, $date2)  
    { 
        // Calulating the difference in timestamps 
        $diff = strtotime($date2) - strtotime($date1); 
        
        // 1 day = 24 hours 
        // 24 * 60 * 60 = 86400 seconds 
        return abs(round($diff / 86400)); 
    } 


    /**
     * Used to deposit fund in user account
     * @param array $input
     * @return int
     */
    function deposit_fund($input) {
        $this->finance_lang = $this->config->item('finance_lang');

        $order_id = $this->create_order($input);

        if ($order_id) { // Add notification
            if ($input["source"] != 7 && $input["source"] != 15) {
                $this->load->model('notification/Notify_nosql_model');
                $today = format_date();
                $tmp = array();
                $this->db= $this->db_user;
                $user_detail = $this->get_single_row('email, user_name', USER, array("user_id" => $input["user_id"]));
                $input["user_name"] = $user_detail['user_name'];

                $tmp["subject"] = $this->finance_lang["deposit_email_subject"];
                $tmp["source_id"] = $input["source_id"];
                $tmp["notification_destination"] = (!empty($tmp["notification_destination"])) ? $tmp["notification_destination"] : 7; //  Web, Push, Email

                $tmp["user_id"] = $input['user_id'];
                $tmp["to"] = $user_detail['email'];
                $tmp["user_name"] = $user_detail['user_name'];
                $tmp["added_date"] = $today;
                $tmp["modified_date"] = $today;
                $tmp["content"] = json_encode($input);
                $notify_data = $this->notify_type_by_source($input["source"]);

                $tmp['notification_type'] = $notify_data['notification_type'];
                $tmp['subject'] = $notify_data['subject'];
                $tmp['notification_destination'] = $notify_data['notification_destination'];

                $this->Notify_nosql_model->send_notification($tmp);
            }
        } else {
            return 0;
        }
        return $order_id;
    }

     /**
     * Used to create order
     * @param array $input
     * @return int
     */
    function create_order($input) {
        $today = format_date();
        $orderData = array();
        $orderData["user_id"] = $input['user_id'];
        $orderData["source"] = $input['source'];
        $orderData["source_id"] = $input['source_id'];
        $orderData["type"] = 0;
        $orderData["date_added"] = $today;
        $orderData["modified_date"] = $today;
        $orderData["status"] = 0;
        $orderData["real_amount"] = 0;
        $orderData["bonus_amount"] = 0;
        $orderData["winning_amount"] = 0;
        $orderData["points"] = 0;
        $orderData["reason"] = isset($input['reason']) ? $input['reason'] : '';

        if(!empty($input['custom_data']))
        {
            $orderData["custom_data"] = $input['custom_data'];
        }

        $amount = $input['amount'];

        switch ($input['cash_type']) {
            case 0:
                $orderData["real_amount"] = $amount; // Real Money
                break;
            case 1:
                $orderData["bonus_amount"] = $amount; // Bonus Money 
                break;
            case 2:
                $orderData["points"] = $amount; // Point Balance
                break;
            case 4:
                $orderData["winning_amount"] = $amount; // Winning Balance 
                break;
            default:
                return FALSE;
                break;
        }

        /* Add source for which status will be set to one */
        $status_one_srs = [0, 2, 4, 9, 10, 12, 13, 14, 16, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 30, 31, 32, 102, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 105, 106, 107, 135, 136, 137,144,145,146,147,153,154,155,156,157,158,159,160,161,162,163,164,165,166,167,168,169,170,171,172,173];

        if (in_array($orderData["source"], $status_one_srs)) {
            $orderData["status"] = 1;
        } else if ($orderData["source"] == 3) {
            $orderData["real_amount"] = 0;
            $orderData["bonus_amount"] = 0;
            $orderData["winning_amount"] = $amount;
            $orderData["status"] = 1;
        }

        //$this->db->trans_start();
        $orderData['order_unique_id'] = $this->_generate_order_key();
        $this->db_user->insert(ORDER, $orderData);
        $order_id = $this->db_user->insert_id();

        if (!$order_id) {
            return FALSE;
        }else{
            // if($orderData["points"] > 0)
            // {
            //     $this->load->helper('queue_helper');
            //     $coin_data = array(
            //         'oprator' => 'add', 
            //         'user_id' => $input['user_id'], 
            //         'total_coins' => $orderData["points"], 
            //         'bonus_date' => format_date("today", "Y-m-d")
            //     );
            //     add_data_in_queue($coin_data, 'user_coins');
            // }
        }

        /*$user_balance = $this->get_user_balance($orderData["user_id"]);
        $real_bal = $user_balance['real_amount'] + $orderData["real_amount"];
        $bonus_bal = $user_balance['bonus_amount'] + $orderData["bonus_amount"];
        $winning_bal = $user_balance['winning_amount'] + $orderData["winning_amount"];
        $point_bal = $user_balance['point_balance'] + $orderData["points"];   // update point balance
        */
        // Update User balance for order with completed status .
        if ($orderData["status"] == 1) {
            $this->update_user_balance($orderData["user_id"], $orderData, 'add');
        }
        //$this->db->trans_complete();
        return $order_id;
    }

     /**
     * Used for generate order unique id
     * @return string
     */
    function _generate_order_key() {
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
     * @method new_record_entry
     * @uses method to make a new entry for daily streak coins
     * @since Nov 2019
     * @param Int $day_number day for which entry has to be done
     * @param Int $coins amount of coins has to be claimed
     * @param Int $daily_streak_length number of days for daily streak coins
     * @return json array
    */
    function new_record_entry($user_id,$day_number=1,$coins=0,$daily_streak_length=3)
    {
        $deposit_data_friend = array(
            "user_id" => $user_id,
            "amount" => $coins,
            "source" => 144, //daily_streak coins
            "source_id" => 0,
            "plateform" => 1,
            "cash_type" => 2, // for coins 
            "link" => FRONT_APP_PATH . 'my-wallet',
            "custom_data"=> json_encode(array(
                'day_number' => $day_number,
                'daily_streak_length' =>$daily_streak_length
            ))
        );

       return $this->deposit_fund($deposit_data_friend);
    }
    /**
     * @method claim_coins  
     * @uses function to claim coins
     * @since May 2020
     * ***/
    function claim_coins($data)
    {
        if(empty($data['user_id']))
        {
            return;
        }
        
        $master_coin_data_key = "master_coin_data";
        $master_coin_data = $this->get_cache_data($master_coin_data_key);
        if(empty($master_coin_data))
        {
            $master_coin_data = $this->get_daily_streak_coins();
            $this->set_cache_data($master_coin_data_key, $master_coin_data, REDIS_2_DAYS);
        }
        $daily_streak_coins = json_decode($master_coin_data[0]['daily_coins_data'],TRUE);
        $record = $this->get_last_daily_streak_coin($data['user_id']);
        
        $daily_streak_length =count($daily_streak_coins);
        if(!empty($record))
        {
            $daily_streak_coins_data = json_decode($record['custom_data'],TRUE);
            
            //$now = date('Y-m-d',strtotime(format_date()));  // or your date as well
            // $entry_date = date('Y-m-d',strtotime($record['date_added']));
            $now =convert_to_client_timezone(format_date(),'Y-m-d');
            $entry_date = convert_to_client_timezone($record['date_added'],'Y-m-d');
            $days = $this->dateDiffInDays($entry_date,$now);


            //$days =round($datediff / (60 * 60 * 24));
            $new_day = $daily_streak_coins_data['day_number'] +1;
            $current_day_coins = 0;
            if($days == 1 && $new_day <= $daily_streak_length )
            {
                //consecutive benefit
                $order_id= $this->new_record_entry($data['user_id'],$new_day,$daily_streak_coins[$daily_streak_coins_data['day_number']],$daily_streak_length); 
                $current_day_coins = $daily_streak_coins[$daily_streak_coins_data['day_number']];
            }
            else if($days > 1 )
            {
                //reset from day number 1
                $order_id=$this->new_record_entry($data['user_id'],1,$daily_streak_coins[0],$daily_streak_length);
                $current_day_coins = $daily_streak_coins[0];
            }
            else
            {
               //coins already claimed
                // $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                // $this->api_response_arry['message'] = $this->lang->line('coins_aleady_claimed_msg');
                // $this->api_response(); 
            }
        }
        else{
            $order_id = $this->new_record_entry($data['user_id'],1,$daily_streak_coins[0],$daily_streak_length);
            $current_day_coins = $daily_streak_coins[0];
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
                $this->db_user->set('balance', 'balance - '.$balance_arr['real_amount'], FALSE);
            }else{
                $this->db_user->set('balance', 'balance + '.$balance_arr['real_amount'], FALSE);
            }
            if(isset($balance_arr['source']) && $balance_arr['source'] == "7" && $oprator == 'add'){
                $this->db_user->set('total_deposit', 'total_deposit + '.$balance_arr['real_amount'], FALSE);
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
        $this->db_user->where('user_id', $user_id);
        $this->db_user->update(USER);

        $this->delete_cache_data('user_balance_'.$user_id);
        
        return $this->db_user->affected_rows();
    }
}