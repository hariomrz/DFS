<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_bonus_cash_model extends MY_Model {
    
    public $db_user ;
    
    public function __construct() {
       	parent::__construct();
		$this->db_user		= $this->load->database('db_user', TRUE);
    }

    public function process_bonus($data) {
        $oprator = isset($data['oprator']) ? $data['oprator'] : '';
        unset($data['oprator']);
        if($oprator == 'withdraw') {
            $this->update_used_bonus($data);
        } else {
            $this->update_bonus($data);
        }
    }

    /**
     * Used to update user bonus value
     */
    public function update_bonus($data) {
        $this->db_user->select('bonus_cash_id, is_expired, total_bonus, used_bonus');
        $this->db_user->from(USER_BONUS_CASH);
        $this->db_user->where('user_id', $data['user_id']);
        $this->db_user->where('bonus_date', $data['bonus_date']);
        $this->db_user->limit(1);
        $query = $this->db_user->get();
        $modified_date = format_date();
        if($query->num_rows() > 0) {
            $row = $query->row_array();
            $total_bonus    = $row['total_bonus'];
            //$used_bonus     = $row['used_bonus'];
            $bonus_cash_id  = $row['bonus_cash_id'];

            $update_data = array();            
            $update_data['modified_date']    = $modified_date;
            $update_data['total_bonus']      = $total_bonus + $data['total_bonus'];
           // $update_data['used_bonus']       = $used_bonus + $data['used_bonus'];
           
            $this->db_user->where('bonus_cash_id', $bonus_cash_id);
            $this->db_user->update(USER_BONUS_CASH,$update_data);
        } else {
            $data['modified_date'] = $modified_date;
            $this->db_user->insert(USER_BONUS_CASH,$data);
        }
    }

    /**
     * Used to update user used bonus value
     */
    public function update_used_bonus($data) {
        $this->db_user->select('bonus_cash_id, is_expired, total_bonus, used_bonus');
        $this->db_user->from(USER_BONUS_CASH);
        $this->db_user->where('user_id', $data['user_id']);
        $this->db_user->where('is_expired', 1);
        $this->db_user->order_by('bonus_cash_id', 'ASC');
        $this->db_user->limit(1);
        $query = $this->db_user->get();
        $modified_date = format_date();
        if($query->num_rows() > 0) {
            $row = $query->row_array();
            $total_bonus    = $row['total_bonus'];
            $used_bonus     = $row['used_bonus'];
            $bonus_cash_id  = $row['bonus_cash_id'];

            $bonus_balance  = $total_bonus - $used_bonus;
            $update_data    = array();
            $update_data['modified_date']    = $modified_date;
            if($bonus_balance == $data['total_bonus']) {
                $update_data['used_bonus']    = $used_bonus + $data['total_bonus'];
                $update_data['is_expired']    = 2;

                $data['total_bonus'] = 0;
            } else if($bonus_balance >= $data['total_bonus']) {
                $update_data['used_bonus']    = $used_bonus + $data['total_bonus'];

                $data['total_bonus'] = 0;
            } else {
                $data['total_bonus'] = $data['total_bonus'] - $bonus_balance; 
                $update_data['used_bonus']    = $total_bonus;
                $update_data['is_expired']    = 2;
            }           
            $this->db_user->where('bonus_cash_id', $bonus_cash_id);
            $this->db_user->update(USER_BONUS_CASH, $update_data);

            if(!empty($data['total_bonus'])) {
                $this->update_used_bonus($data);
            }
        }
    }

    /**  Used to send bonus cash going to expire notification
     * @return array
     */
    function send_bonus_expiry_notification() {
        $today_date = format_date("today", "Y-m-d");
        $next_date = date("Y-m-d", strtotime('+7 day',strtotime($today_date)));

        $bonus_expiry_limit = -isset($this->app_config['bonus_expiry_limit'])?$this->app_config['bonus_expiry_limit']['key_value']:0;
        
        $bonus_start_date = date("Y-m-d", strtotime($bonus_expiry_limit.' day',strtotime($today_date)));
        $bonus_end_date = date("Y-m-d", strtotime($bonus_expiry_limit.' day',strtotime($next_date)));
        
        $this->db_user->select('IFNULL(SUM(UB.total_bonus-UB.used_bonus),0) as amount');
        $this->db_user->select('U.email, U.user_name, U.user_id');
        $this->db_user->from(USER_BONUS_CASH." UB");
        $this->db_user->join(USER." AS U","U.user_id = UB.user_id");
   
        $this->db_user->where('UB.is_expired', 1);
        $this->db_user->where('UB.bonus_date >= ', $bonus_start_date);
        $this->db_user->where('UB.bonus_date <= ', $bonus_end_date);
        $this->db_user->group_by('UB.user_id');
        $query = $this->db_user->get();        
        if($query->num_rows() > 0) {
            $results = $query->result_array();
            foreach($results as $result) {
                if($result['amount'] > 0) {
                    /* Send Notification */
                    $notify_data = array();
                    $notify_data['notification_type'] = 426; 
                    $notify_data['notification_destination'] = 1; //web
                    $notify_data["source_id"] = 0;
                    $notify_data["user_id"] = $result['user_id'];
                    $notify_data["to"] = $result['email'];
                    $notify_data["user_name"] = $result['user_name'];
                    $notify_data["added_date"] = format_date();
                    $notify_data["modified_date"] = format_date();
                    $notify_data["subject"] = "Bonus Expire";

                    $content = array();
                    $content['amount'] = $result['amount'];
                    $notify_data["content"] = json_encode($content);
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($notify_data); 
                }                
            }
        }
    }

    /**  Used to expire bonus
     * @return array
     */
    function bonus_expiry($page_no=1) {
        $limit = 20;
        $offset     = ($page_no-1)*$lmiit;
        $bonus_expiry_limit = isset($this->app_config['bonus_expiry_limit'])?$this->app_config['bonus_expiry_limit']['key_value']:0;
        $today_date = format_date("today", "Y-m-d");
        $expiry_date = date("Y-m-d", strtotime('-'.$bonus_expiry_limit.' day',strtotime($today_date)));

        $this->db_user->select('IFNULL(SUM(UB.total_bonus-UB.used_bonus),0) as amount');
        $this->db_user->select('UB.bonus_cash_id, UB.user_id');
        $this->db_user->from(USER_BONUS_CASH." UB");   
        $this->db_user->where('UB.is_expired', 1);
        $this->db_user->where('UB.bonus_date < ', $expiry_date);
        $this->db_user->group_by('UB.user_id');
        $this->db->limit($limit, $offset);
        $query = $this->db_user->get();        
        if($query->num_rows() > 0) {
            $results = $query->result_array();
            $modified_date = format_date();
            $update_data    = array();
            $update_data['modified_date']    = $modified_date;
            $update_data['is_expired']    = 2;
            foreach($results as $result) {
                $user_id  = $result['user_id'];
                $amount  = $result['amount'];
                $this->db_user->where('user_id', $user_id);
                $this->db_user->where('is_expired', 1);
                $this->db_user->where('bonus_date < ', $expiry_date);
                $this->db_user->update(USER_BONUS_CASH, $update_data);      
                if($amount > 0) {
                    $this->update_user_bonus_balance($user_id, $amount, 'withdraw');
                }                
            }
            $this->bonus_expiry(++$page_no);
        }
        return true;
    }

    /**  Used to update user bonus balance 
     * @param int $user_id
     * @param float $bonus_amount 
     * @return int
     */
    function update_user_bonus_balance($user_id, $bonus_amount, $oprator='add') {
        if($oprator=='withdraw'){
            $this->db_user->set('bonus_balance', 'bonus_balance - '.$bonus_amount, FALSE);
            $this->db_user->where('bonus_balance > ', 0);
        }else{
            $this->db_user->set('bonus_balance', 'bonus_balance + '.$bonus_amount, FALSE);
        }
        
        $this->db_user->where('user_id', $user_id);
        $this->db_user->update(USER);
    }

    //*************************************************** COIN EXPIRY MODULE REALTED METHODS *******************************************/
    public function process_coins($data) {
        $oprator = isset($data['oprator']) ? $data['oprator'] : '';
        unset($data['oprator']);
        if($oprator == 'withdraw') {
            $this->update_used_coins($data);
        } else {
            $this->update_coins($data);
        }
    }

    /**
     * Used to update user used coins value
     */
    public function update_used_coins($data) {
        $this->db_user->select('bonus_cash_id, is_coin_exp, total_coins, used_coins');
        $this->db_user->from(USER_BONUS_CASH);
        $this->db_user->where('user_id', $data['user_id']);
        $this->db_user->where('is_coin_exp', 1);
        $this->db_user->order_by('bonus_cash_id', 'ASC');
        $this->db_user->limit(1);
        $query = $this->db_user->get();
        $modified_date = format_date();
        if($query->num_rows() > 0) {
            $row = $query->row_array();
            $total_coins    = $row['total_coins'];
            $used_coins     = $row['used_coins'];
            $bonus_cash_id  = $row['bonus_cash_id'];

            $coin_balance  = $total_coins - $used_coins;
            $update_data    = array();
            $update_data['modified_date']    = $modified_date;
            if($coin_balance == $data['total_coins']) {
                $update_data['used_coins']    = $used_coins + $data['total_coins'];
                $update_data['is_coin_exp']    = 2;

                $data['total_coins'] = 0;
            } else if($coin_balance >= $data['total_coins']) {
                $update_data['used_coins']    = $used_coins + $data['total_coins'];

                $data['total_coins'] = 0;
            } else {
                $data['total_coins'] = $data['total_coins'] - $coin_balance; 
                $update_data['used_coins']    = $total_coins;
                $update_data['is_coin_exp']    = 2;
            }           
            $this->db_user->where('bonus_cash_id', $bonus_cash_id);
            $this->db_user->update(USER_BONUS_CASH, $update_data);

            if(!empty($data['total_coins'])) {
                $this->update_used_coins($data);
            }
        }
    }

    /**
     * Used to update user bonus value
     */
    public function update_coins($data) {
        $this->db_user->select('bonus_cash_id, is_coin_exp, total_coins, used_coins');
        $this->db_user->from(USER_BONUS_CASH);
        $this->db_user->where('user_id', $data['user_id']);
        $this->db_user->where('bonus_date', $data['bonus_date']);
        $this->db_user->limit(1);
        $query = $this->db_user->get();
        $modified_date = format_date();
        if($query->num_rows() > 0) {
            $row = $query->row_array();
            $total_coins    = $row['total_coins'];
            //$used_bonus     = $row['used_bonus'];
            $bonus_cash_id  = $row['bonus_cash_id'];

            $update_data = array();            
            $update_data['modified_date']    = $modified_date;
            $update_data['total_coins']      = $total_coins + $data['total_coins'];
           // $update_data['used_bonus']       = $used_bonus + $data['used_bonus'];
           
            $this->db_user->where('bonus_cash_id', $bonus_cash_id);
            $this->db_user->update(USER_BONUS_CASH,$update_data);
        } else {
            $data['modified_date'] = $modified_date;
            $this->db_user->insert(USER_BONUS_CASH,$data);
        }
    }

    /**  Used to send push notification while expiring user coins
     * @return array
     */
    function send_coins_expiry_notification() {

        $days  = $this->app_config['allow_coin_expiry']['custom_data']['push_before_days'];
        $today_date = format_date("today", "Y-m-d");
        $next_date = date("Y-m-d", strtotime('+'.$days.' day',strtotime($today_date)));
        $coin_expiry_limit = $this->app_config['allow_coin_expiry']['custom_data']['ce_days_limit'];
        $coins_start_date   = date("Y-m-d", strtotime(-$coin_expiry_limit.' day',strtotime($today_date)));
        $coins_end_date     = date("Y-m-d", strtotime(-$coin_expiry_limit.' day',strtotime($next_date)));

        
        // ->join(ACTIVE_LOGIN.' AL','AL.user_id=UB.user_id AND AL.device_id IS NOT NULL'),GROUP_CONCAT(AL.device_id) as device_ids
        $this->db_user->select('U.email, U.user_name, U.user_id,IFNULL(SUM(UB.total_coins-UB.used_coins),0) as amount')
        ->from(USER_BONUS_CASH." UB")
        ->join(USER." AS U","U.user_id = UB.user_id")
        ->where('UB.is_coin_exp', 1)
        ->where('UB.bonus_date >= ', $coins_start_date)
        ->where('UB.bonus_date <= ', $coins_end_date)
        ->group_by('UB.user_id');
        $query = $this->db_user->get();
        // echo $this->db_user->last_query();exit;        
        if($query->num_rows() > 0) {
            $results = $query->result_array();
            //$user_ids = array_unique(array_column($results,'user_id'));
            // $device_ids = array_column($device_ids,"device_id","user_id");
            $notify_data = array();
            $notify_data["source_id"] = 0;
            $notify_data['notification_destination'] = 2; //push
            $notify_data["added_date"] = format_date();
            $notify_data["modified_date"] = format_date();
            $notify_data['notification_type'] = 590; 
            $notify_data["subject"] = "Coins Expiring Soon‼️";

            foreach($results as $result) {
                if($result['amount'] > 0) {
                    /* Send Notification */
                    $device_ids = $this->db_user->select("AL.device_id,AL.device_type")
                    ->from(ACTIVE_LOGIN.' AL')
                    ->where("AL.device_id IS NOT NULL")
                    ->where("AL.user_id",$result['user_id'])
                    ->get()->result_array();

                    $android_device_ids = $ios_device_ids = array();
                    foreach($device_ids as $key=>$single_id)
                    {
                        if(isset($single_id['device_type']) && $single_id['device_type']=='1' )
                        {
                            $android_device_ids[] = $single_id['device_id'];
                        }

                        if(isset($single_id['device_type']) && $single_id['device_type']=='2' )
                        {
                            $ios_device_ids[] = $single_id['device_id'];
                        }
                    }

                    $notify_data["user_id"] = $result['user_id'];
                    $notify_data["device_ids"]  = $android_device_ids;            
                    $notify_data["ios_device_ids"]  = $ios_device_ids;            
                    $notify_data['custom_notification_text'] = "Your ".$result['amount']." coins are expiring in next".$days." days 😟. Use them now and gain benefits. 💰💵";
                    $notify_data["content"] = json_encode(array('user_id' => $result['user_id'], 'device_ids' => $device_ids,'message'=>$notify_data['custom_notification_text']));
                    // error_log("\n\n".format_date().' coin exp reminder: '.$notify_data.'<br>',3,'/var/www/html/cron/application/logs/sirius.log');
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($notify_data);
                }                
            }
        }
    }

    /**  Used to expire coins
     * @return array
     */
    function coin_expiry($page_no=1) {
        $limit = 200;
        $offset     = ($page_no-1)*$limit;
        $coin_expiry_limit = $this->app_config['allow_coin_expiry']['custom_data']['ce_days_limit'];
        $today_date = format_date("today", "Y-m-d");
        $expiry_date = date("Y-m-d", strtotime('-'.$coin_expiry_limit.' day',strtotime($today_date)));
        
        $this->db_user->select('IFNULL(SUM(UB.total_coins-UB.used_coins),0) as amount,UB.bonus_cash_id, UB.user_id')
        ->from(USER_BONUS_CASH." UB")
        ->where('UB.is_coin_exp', 1)
        ->where('UB.bonus_date < ', $expiry_date)
        ->group_by('UB.user_id')
        ->limit($limit, $offset);
        $query = $this->db_user->get();



        if($query->num_rows() > 0) {
            $results = $query->result_array();
            $modified_date = format_date();
            $update_data    = array();
            $update_data['modified_date']    = $modified_date;
            $update_data['is_coin_exp']    = 2;
            foreach($results as $result) {
                $user_id  = $result['user_id'];
                $amount  = $result['amount'];
                $this->db_user->where('user_id', $user_id)
                ->where('is_coin_exp', 1)
                ->where('bonus_date < ', $expiry_date)
                ->update(USER_BONUS_CASH, $update_data);     

                if($amount > 0) {
                    //************generating order */
                    
                    $orderData                      = array();
                    $orderData["user_id"]           = $user_id;
                    $orderData["source"]            = 475; // coin exirey source
                    $orderData["source_id"]         = 0;
                    $orderData["season_type"]       = 1;
                    $orderData["type"]              = 1;
                    $orderData["date_added"]        = format_date();
                    $orderData["modified_date"]     = format_date();
                    $orderData["plateform"]         = 1;
                    $orderData["status"]            = 1;
                    $orderData["real_amount"]       = 0;
                    $orderData["bonus_amount"]      = 0;
                    $orderData["winning_amount"]    = 0;
                    $orderData["points"]            = $amount;
                    $orderData['reason']            ="Coins expired";

                    if(!empty($custom_data)) {
                        $orderData["custom_data"] = json_decode($custom_data);
                    }

                    $this->load->model('Cron_model');
                    $orderData['order_unique_id'] = $this->Cron_model->_generate_order_key();
                    $this->db_user->insert(ORDER, $orderData);
                    $order_id = $this->db_user->insert_id();
                    
                    /*************generating order */
                    if($order_id)
                    {

                        $this->update_user_coin_balance($user_id, $amount, 'withdraw');
                        $current_balance = $this->db_user->select('point_balance')->where('user_id',$user_id)->get(USER)->row_array();

                        //commented because we are sending only in app nottificattion
                        
                        // $device_ids = $this->db_user->select("device_id,device_type")
                        // ->from(ACTIVE_LOGIN.' AL')
                        // ->where("AL.device_id IS NOT NULL")
                        // ->where("AL.user_id",$user_id)
                        // ->get()->row_array();

                        // $android_device_ids = $ios_device_ids = array();
                        // foreach($device_ids as $key=>$single_id)
                        // {
                        //     if(isset($single_id['device_type']) && $single_id['device_type']=='1' )
                        //     {
                        //         $android_device_ids[] = $single_id['device_id'];
                        //     }

                        //     if(isset($single_id['device_type']) && $single_id['device_type']=='2' )
                        //     {
                        //         $ios_device_ids[] = $single_id['device_id'];
                        //     }
                        // }

                        // if(!empty($device_ids['device_ids']))
                        // {
                            $notify_data                                    = array();
                            $notify_data["source_id"]                       = 0;
                            $notify_data['notification_destination']        = 1; //web
                            $notify_data["added_date"]                      = format_date();
                            $notify_data["modified_date"]                   = format_date();
                            $notify_data['notification_type']               = 589; 
                            $notify_data["subject"]                         = "Coins Expired! ☹️";
                            $notify_data["user_id"]                         = $user_id;
                            // $notify_data["device_ids"]                      = $device_ids['device_ids'];            
                            $notify_data["content"]                     	= json_encode(
                                                                                            array(
                                                                                                'user_id'       => $user_id, 
                                                                                                'device_ids'    => $device_ids['device_id'] ? $device_ids['device_id'] : "",
                                                                                                'amount'        =>$amount,
                                                                                                'coin_bal'      =>$current_balance['point_balance'],
                                                                                                'custom_data'   =>[
                                                                                                                    'amount'   =>$amount,
                                                                                                                    'coin_bal' =>$current_balance['point_balance'],
                                                                                                                ],
                                                                                                "exp_date"      =>$today_date,
                                                                                            )
                                                                                        );
                            $notify_data['custom_notification_text']        = "{$amount} coins expired on {$today_date}. Coin balance 💰 {$current_balance['point_balance']}";
                            // $notify_data["device_ids"]  = $android_device_ids;            
                            // $notify_data["ios_device_ids"]  = $ios_device_ids;
                            $this->load->model('notification/Notify_nosql_model');
                            $this->Notify_nosql_model->send_notification($notify_data);
                        //}
                    }
                    
                }                
            }
            $this->coin_expiry(++$page_no);
        }
        return true;
    }

    /**  Used to update user coin balance 
     * @param int $user_id
     * @param float $coin_amount 
     * @return int
     */
    function update_user_coin_balance($user_id, $coin_amount, $oprator='add') {

        $current_points = $this->db_user->select('user_id,point_balance')->where('user_id',$user_id)->get(USER)->row_array();

        $point_balance = $current_points['point_balance'];

        if($point_balance < 0 || $point_balance < $coin_amount ) {
            $this->db_user->set('point_balance', '0', FALSE);
           
        }else {
            if($oprator=='withdraw'){
                 $update_point_balance = $point_balance - $coin_amount;
                $this->db_user->set('point_balance', $update_point_balance, FALSE);
                $this->db_user->where('point_balance > ', 0);
            }else{
                $this->db_user->set('point_balance', 'point_balance + '.$coin_amount, FALSE);
                $this->db_user->where('point_balance > ', 0);
            }
           
        }

        $this->db_user->where('user_id', $user_id);
        $this->db_user->update(USER);
    }
    //*************************************************** COIN EXPIRY MODULE REALTED METHODS *******************************************/
    /**
     * method for selecting users to send push of few more coins away for next level redeem.
     */
    function notify_few_more_coins_away()
    {
        $current_date = format_date();

        $this->load->model("Cron_nosql_model");
        $coin_rewards = $this->Cron_nosql_model->select_nosql('coin_rewards',array('status'=>1),NULL,NULL,'redeem_coins','ASC');

        $user_detail = $this->db_user->select("U.user_id, U.user_name,U.point_balance,IF(AL.device_type=1,GROUP_CONCAT(AL.device_id),'') device_ids,IF(AL.device_type=2,GROUP_CONCAT(AL.device_id),'') ios_device_ids,AL.device_type",false)
        ->from(USER .' U')
        ->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id AND AL.device_id IS NOT NULL')
        ->where("U.is_systemuser",0)
        ->where("U.status",1)
        ->where("U.point_balance >=",$coin_rewards[0]['redeem_coins'])
        ->group_by('U.user_id')
        ->get()->result_array();


        $notify_data = array();            
        $notify_data["source_id"] = 0;
        $notify_data["notification_destination"] = 2; //Push
        $notify_data["added_date"] = $current_date;
        $notify_data["modified_date"] = $current_date;
        $notify_data["notification_type"] = 583;     
        $notify_data['custom_notification_subject'] = 'You are almost there 🤩🤩';

        $merchandise_name = "";
        $point_diff = 0;
        foreach($user_detail as $user) {
            $user_coin_balance = $user['point_balance'];
            foreach($coin_rewards as $coinr)
            {
                $point_diff  = $coinr['redeem_coins']-$user_coin_balance ;
                if($point_diff < 500 && $point_diff > 0)
                {
                    if(empty($user['device_ids']) && empty($user["ios_device_ids"])) {
                        continue;
                    } 
        
                    $notify_data["device_ids"]          = isset($user['device_ids']) ? $user['device_ids'] : '';            
                    $notify_data['ios_device_ids']      = isset($user["ios_device_ids"]) ? $user["ios_device_ids"] : '';

                    $merchandise_name = $coinr['detail'];
                    $notify_data["user_id"]     = $user['user_id'];
                    $notify_data["content"] 	= json_encode(array('user_id' => $user['user_id'], 'device_ids' => $user['device_ids']));
                    $notify_data['custom_notification_text'] = str_replace('{{merchandise_name}}',$merchandise_name,'Just a few coins more and you can get the {{merchandise_name}} 🛍️😵‍💫🛍️');
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($notify_data);
                }
            }
            
        }
                
    }

    /**
     * method to send gift claim notifications
     */
    function notify_gift_claim()
    {
        $current_date = format_date();

        $this->load->model("Cron_nosql_model");
        $coin_rewards = $this->Cron_nosql_model->select_nosql('coin_rewards',array('status'=>1),NULL,NULL,'redeem_coins','ASC');
        $reward_point_arr = array_column($coin_rewards,'detail','redeem_coins');

        $user_detail = $this->db_user->select("U.user_id, U.user_name,U.point_balance,IF(AL.device_type=1,GROUP_CONCAT(AL.device_id),'') device_ids,IF(AL.device_type=2,GROUP_CONCAT(AL.device_id),'') ios_device_ids,AL.device_type",false)
        ->from(USER .' U')
        ->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id AND AL.device_id IS NOT NULL')
        ->where("U.is_systemuser",0)
        ->where("U.status",1)
        ->where("U.point_balance >=",$coin_rewards[0]['redeem_coins'])
        ->group_by('U.user_id')
        ->get()->result_array();
        
        // get claim status by some query

        $notify_data = array();            
        $notify_data["source_id"] = 0;
        $notify_data["notification_destination"] = 2; //Push
        $notify_data["added_date"] = $current_date;
        $notify_data["modified_date"] = $current_date;
        $notify_data["notification_type"] = 584;     
        $notify_data['custom_notification_subject'] = 'Yay‼️';
        $push_notification_text = array(
            'You made it to a {{merchandise_name}} 🛍️😵‍💫🛍️ Claim it now.',
            'Are you waiting for a bigger gift? 🤔 See what you can claim right now. 🎁🎁',
        );

        $merchandise_name = "";
        foreach($user_detail as $user) {  
            $user_coin_balance = $user['point_balance'];
            foreach($coin_rewards as $key=>$coinr)
            {
                $point_diff  = $user_coin_balance - $coinr['redeem_coins'];
                if($point_diff < 0 && $key!=0)
                {
                    if(empty($user['device_ids']) && empty($user["ios_device_ids"])) {
                        continue;
                    } 
        
                    $notify_data["device_ids"]          = isset($user['device_ids']) ? $user['device_ids'] : '';            
                    $notify_data['ios_device_ids']      = isset($user["ios_device_ids"]) ? $user["ios_device_ids"] : '';

                    $merchandise_name = $coin_rewards[$key-1]['detail'];
                    $notify_data["user_id"]     = $user['user_id'];
                    $notify_data["content"] 	= json_encode(array('user_id' => $user['user_id'], 'device_ids' => $user['device_ids']));
                    $notify_data['custom_notification_text'] = $push_notification_text[array_rand($push_notification_text)];
                    $notify_data['custom_notification_text'] = str_replace('{{merchandise_name}}',$merchandise_name,$notify_data['custom_notification_text']);
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($notify_data);
                    break;
                }
            }
        }

    }

    public function notify_user_engage_noon()
    {
        $this->load->model("Cron_nosql_model");
        $this->load->library('mongo_db');

        $from_time_normal = date('Y-m-d H:i:s',strtotime('-36 hours',strtotime(format_date())));
        $to_time_normal = format_date();
        $from_time_mongo = $this->Cron_nosql_model->normal_to_mongo_date($from_time_normal);
        $to_time_mongo = $this->Cron_nosql_model->normal_to_mongo_date($to_time_normal);
        $this->mongo_db->where_gte('start_time', $from_time_mongo);
        $this->mongo_db->where_lte('end_time', $to_time_mongo);

        $coin_rewards = $this->Cron_nosql_model->select_nosql(SESSION_TRACK);
        $excluding_users = array_unique(array_column($coin_rewards,'user_id'));
        // unset($excluding_users[190]);
        $user_detail = $this->db_user->select("U.user_id, IFNULL(U.user_name,'') AS user_name,U.point_balance,IF(AL.device_type=1,GROUP_CONCAT(AL.device_id),'') device_ids,IF(AL.device_type=2,GROUP_CONCAT(AL.device_id),'') ios_device_ids,AL.device_type",false)
        ->from(USER .' U')
        ->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id AND AL.device_id IS NOT NULL')
        ->where("U.is_systemuser",0)
        ->where("U.status",1)
        ->where_not_in("U.user_id",$excluding_users)
        ->group_by('U.user_id')
        ->get()->result_array();

        $notify_data = array();            
        $notify_data["source_id"] = 0;
        $notify_data["notification_destination"] = 2; //Push
        $notify_data["added_date"] = $to_time_normal;
        $notify_data["modified_date"] = $to_time_normal;
        $notify_data["notification_type"] = 585;     
        $notify_data['custom_notification_subject'] = '✊🏻 Knock Knock‼️';
        $push_notification_text = array(
            "Hey {{username}}\nWe are missing you here. ☹️ Check out what's new for you.",
            "{{username}}\nCan you take a few minutes out of your schedule for us?",
        );

        $username = "";
        foreach($user_detail as $user) {
            if(empty($user['device_ids']) && empty($user["ios_device_ids"])) {
                continue;
            } 

            $notify_data["device_ids"]          = isset($user['device_ids']) ? $user['device_ids'] : '';            
            $notify_data['ios_device_ids']      = isset($user["ios_device_ids"]) ? $user["ios_device_ids"] : '';

            $username = $user['user_name'];
            $notify_data["user_id"]     = $user['user_id'];
            $notify_data["content"] 	= json_encode(array('user_id' => $user['user_id'], 'device_ids' => $user['device_ids']));
            $notify_data['custom_notification_text'] = $push_notification_text[array_rand($push_notification_text)];
            $notify_data['custom_notification_text'] = str_replace('{{username}}',$username,$notify_data['custom_notification_text']);
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($notify_data);
        }

    }

    public function notify_user_engage_evening()
    {

        $from_time = date('Y-m-d H:i:s',strtotime('-18 hours',strtotime(format_date())));
        $to_time = format_date();
        $module_source_arr = [144];

        $sub_query = $this->db_user->select('O.user_id')
        ->from(ORDER.' O')
        ->where_in('O.source',$module_source_arr)
        ->where("DATE_FORMAT(O.date_added,'%Y-%m-%d') >= '".$from_time."' and DATE_FORMAT(O.date_added,'%Y-%m-%d') <= '".$to_time."'")
        ->group_by('O.user_id')
        ->get_compiled_select();
        // ->get()->result_array();

        // $excluding_users = array_unique(array_column($played_users,'user_id'));
        // array_push($excluding_users,'199');
        $user_detail = $this->db_user->select("U.user_id, IFNULL(U.user_name,'') AS user_name,U.point_balance,GROUP_CONCAT(CASE WHEN AL.device_type = 1 THEN device_id  else NULL END SEPARATOR ' , ') device_ids,GROUP_CONCAT(case when AL.device_type = 2 then device_id  else NULL end SEPARATOR ' , ') ios_device_ids",false)
        ->from(USER .' U')
        ->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id AND AL.device_id IS NOT NULL')
        ->where("U.is_systemuser",0)
        ->where("U.status",1)
        ->where("U.user_id NOT IN($sub_query)")
        ->group_by('U.user_id')
        ->get()->result_array();
        // if(!empty($excluding_users)){
        // $this->db_user->where_not_in("U.user_id",$excluding_users);
        // }

        $notify_data = array();            
        $notify_data["source_id"] = 0;
        $notify_data["notification_destination"] = 2; //Push
        $notify_data["added_date"] = $to_time;
        $notify_data["modified_date"] = $to_time;
        $notify_data["notification_type"] = 586;     
        $notify_data['custom_notification_subject'] = 'Don\'t lose your rewards today! ';
        $push_notification_text = array(
            'Check out what is waiting for you.🤔🔮💰',
            'Will you earn 30%, 70% or 100% rewards today? Check the app and reveal your mystery before the day ends.🔮⏱️',
        );

        foreach($user_detail as $user) {
            if(empty($user['device_ids']) && empty($user["ios_device_ids"])) {
                continue;
            } 

            $notify_data["device_ids"]          = isset($user['device_ids']) ? $user['device_ids'] : '';            
            $notify_data['ios_device_ids']      = isset($user["ios_device_ids"]) ? $user["ios_device_ids"] : '';

            $username = $user['user_name'] ? $user['user_name'] : "";
            $notify_data["user_id"]     = $user['user_id'];
            $notify_data["content"] 	= json_encode(array('user_id' => $user['user_id'], 'device_ids' => $user['device_ids']));
            $notify_data['custom_notification_text'] = $push_notification_text[array_rand($push_notification_text)];
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($notify_data);
        }

    }

    public function notify_spin_user_engage()
    {
        $this->load->model("Cron_nosql_model");
        $this->load->library('mongo_db');

        $from_time_normal = date('Y-m-d H:i:s',strtotime('-24 hours',strtotime(format_date())));
        $to_time_normal = format_date();
        $from_time_mongo = $this->Cron_nosql_model->normal_to_mongo_date($from_time_normal);
        $to_time_mongo = $this->Cron_nosql_model->normal_to_mongo_date($to_time_normal);
        $this->mongo_db->where_gte('start_time', $from_time_mongo);
        $this->mongo_db->where_lte('end_time', $to_time_mongo);
        $coin_rewards = $this->Cron_nosql_model->select_nosql(SESSION_TRACK);

        $excluding_users = array_unique(array_column($coin_rewards,'user_id'));
        // unset($excluding_users[190]);
        $user_detail = $this->db_user->select("U.user_id, IFNULL(U.user_name,'') AS user_name,U.point_balance,IF(AL.device_type=1,GROUP_CONCAT(AL.device_id),'') device_ids,IF(AL.device_type=2,GROUP_CONCAT(AL.device_id),'') ios_device_ids,AL.device_type",false)
        ->from(USER .' U')
        ->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id AND AL.device_id IS NOT NULL')
        ->where("U.is_systemuser",0)
        ->where("U.status",1)
        ->where_not_in("U.user_id",$excluding_users)
        ->group_by('U.user_id')
        ->get()->result_array();

        $random_people = rand(500,2000);
        $random_earn_coin = rand(2000,5000);

        $notify_data = array();            
        $notify_data["source_id"] = 0;
        $notify_data["notification_destination"] = 2; //Push
        $notify_data["added_date"] = $to_time_normal;
        $notify_data["modified_date"] = $to_time_normal;
        $notify_data["notification_type"] = 587;     
        $notify_data['custom_notification_subject'] = $random_people.' people won '.$random_earn_coin.' coins 💰 yesterday';

        $username = "";
        foreach($user_detail as $user) {
            if(empty($user['device_ids']) && empty($user["ios_device_ids"])) {
                continue;
            } 

            $notify_data["device_ids"]          = isset($user['device_ids']) ? $user['device_ids'] : '';            
            $notify_data['ios_device_ids']      = isset($user["ios_device_ids"]) ? $user["ios_device_ids"] : '';

            $username = $user['user_name'];
            $notify_data["user_id"]     = $user['user_id'];
            $notify_data["content"] 	= json_encode(array('user_id' => $user['user_id'], 'device_ids' => $user['device_ids']));
            $notify_data['custom_notification_text'] = 'Grab your chance today to win big. 🤑💸';
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($notify_data);
        }

    }

    public function notify_not_played_thrice()
    {

        $from_time = date('Y-m-d H:i:s',strtotime('-18 hours',strtotime(format_date())));
        $to_time = format_date();
        $module_source_arr = [470,322,3,144];
        $module_map = array(
            322 =>"spin wheel",
            470=>"quiz",
            144=>"daily checkin",
            3=>"countest join",
        );
        $module_name = $module_map[array_rand($module_map,1)];

        $played_users = $this->db_user->select('O.user_id,GROUP_CONCAT(DISTINCT O.source) AS source')
        ->from(ORDER.' O')
        ->where_in('O.source',$module_source_arr)
        ->group_by('O.user_id')
        ->having('COUNT(O.user_id) > 2')
        ->get()->result_array();
        $user_source_arr = array_column($played_users,'source','user_id');
        $user_detail = $this->db_user->select("U.user_id, IFNULL(U.user_name,'') AS user_name,U.point_balance,IF(AL.device_type=1,GROUP_CONCAT(AL.device_id),'') device_ids,IF(AL.device_type=2,GROUP_CONCAT(AL.device_id),'') ios_device_ids,AL.device_type",false)
        ->from(USER .' U')
        ->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id AND AL.device_id IS NOT NULL',"INNER")
        ->group_by('U.user_id')
        ->where("U.is_systemuser",0)
        ->where("U.status",1);
        $user_detail = $this->db_user->get()->result_array();

        $notify_data = array();            
        $notify_data["source_id"] = 0;
        $notify_data["notification_destination"] = 2; //Push
        $notify_data["added_date"] = format_date();
        $notify_data["modified_date"] = format_date();
        $notify_data["notification_type"] = 588;     
        $notify_data['custom_notification_subject'] = 'You wanted it, we brought it 😎';
        foreach($user_detail as $user) {
            if(isset($user_source_arr[$user['user_id']]))
            {
                $temp_src_arr = array_diff($module_source_arr,explode(",",$user_source_arr[$user['user_id']]));
                if(empty($temp_src_arr)) continue;
                $module_name = $module_map[$temp_src_arr[array_rand($temp_src_arr,1)]];
            }
            if(empty($user['device_ids']) && empty($user["ios_device_ids"])) {
                continue;
            } 

            $notify_data["device_ids"]          = isset($user['device_ids']) ? $user['device_ids'] : '';            
            $notify_data['ios_device_ids']      = isset($user["ios_device_ids"]) ? $user["ios_device_ids"] : '';

            $username                   = $user['user_name'] ? $user['user_name'] : "";
            $notify_data["user_id"]     = $user['user_id'];
            $notify_data["content"] 	= json_encode(array('user_id' => $user['user_id'], 'device_ids' => $user['device_ids']));
            $notify_data['custom_notification_text'] = 'Try all new {{module}} and stay in game 😎';
            $notify_data['custom_notification_text'] = str_replace('{{module}}',$module_name,$notify_data['custom_notification_text']);
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($notify_data);
        }
    }
}

//🤑💵💶💴💸💷🔮💰⏱️✊🏻🛍️😵‍💫🤔🎁👀😎🤩‼️
