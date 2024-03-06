<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Cron extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        echo "Welcome";die();
    }

    /**
     * Used for delete all cache keys
     * @param 
     * @return string
     */
    public function flush_cache(){
        $auth_key = $_REQUEST['auth_key'];
        if($auth_key && $auth_key == "VSPADMIN"){
            $this->load->model('Nodb_model');
            $this->Nodb_model->flush_cache_data();
            echo "done";
        }else{
            echo "access denied";
        }
        exit();
    }

    /**
     * Used for delete cache data by key
     * @param string $cache_key cache key
     * @return string
     */
    public function delete_cache_key(){
        $cache_key = $_REQUEST['cache_key'];
        if($cache_key && $cache_key != ""){
            $this->load->model('Nodb_model');
            $this->Nodb_model->delete_cache_data($cache_key);
            echo "done";
        }else{
            echo "not done";
        }
        exit();
    }

    /**
     * Used for update lobby fixture list on s3
     * @param int $sports_id
     * @return string
     */
    public function update_lobby_fixture_data($sports_id='')
    {   
        if (!empty($sports_id))
        {
            $input_arr = array();
            $input_arr['sports_id'] = $sports_id;
            $input_arr['is_cron_data'] = 1;
            $this->http_post_request("lobby/get_lobby_fixture",$input_arr,1);

            $allow_multigame =  isset($this->app_config['allow_multigame'])?$this->app_config['allow_multigame']['key_value']:0;
		
            if($allow_multigame == 1){
                $this->http_post_request("multigame/get_lobby_fixture",$input_arr,1);
            }

            //lobby filter
            $input_arr = array();
            $input_arr['sports_id'] = $sports_id;
            $input_arr['is_cron_data'] = 1;
            $this->http_post_request("lobby/get_lobby_filter",$input_arr,1);
            echo "done";
        }
        exit();
    }

    /**
     * Used for get users count and sports
     * @param 
     * @return string
     */
    public function get_user_count()
    {   
        $this->load->model('Cron_model');
        echo $this->Cron_model->get_user_count();
        exit();
    }

    /**
     * Used for update match playing11
     * @param 
     * @return string
     */
    public function update_match_playing_data()
    {
        $this->load->helper('queue');
        $this->load->model('Cron_model');
        $server_name = get_server_host_name();
        $match_list = $this->Cron_model->get_playing_upcoming_match();
        foreach($match_list as $match)
        {
            $sports_id = $match['sports_id'];
            $season_game_uid = $match['season_game_uid'];
            $content                  = array();
            if($sports_id == CRICKET_SPORTS_ID)
            {
                $content['url']           = $server_name."/cron/cricket/vinfotech/get_season_details/".$season_game_uid;
            }
            if($sports_id == KABADDI_SPORTS_ID)
            {
               $content['url']           = $server_name."/cron/kabaddi/vinfotech/get_season_details/".$season_game_uid;
            }
            if($sports_id == SOCCER_SPORTS_ID)
            {
               $content['url']           = $server_name."/cron/soccer/vinfotech/get_season_details/".$season_game_uid;
            }

            if($sports_id == BASEBALL_SPORTS_ID)
            {
               $content['url']           = $server_name."/cron/baseball/vinfotech/get_season_details/".$season_game_uid;
            }
            add_data_in_queue($content,'season_cron');
        }

        echo "done";
        exit();
    }

    /**
     * Used for pull single match details from feed
     * @param string $season_game_uid
     * @return string
     */
    public function update_feed_fixture_details()
    {
        $post_data = $_REQUEST;
        if(isset($post_data['season_game_uid']) && $post_data['season_game_uid'] != "" && isset($post_data['sports_id']) && $post_data['sports_id'] != "")
        {
            $this->load->model('Cron_model');
            $this->load->helper('queue');
            $server_name = get_server_host_name();
            //echo "<pre>";print_r($post_data);die;
            $sports_id = $post_data['sports_id'];
            $season_game_uid = $post_data['season_game_uid'];

            $content                  = array();
            if($sports_id == CRICKET_SPORTS_ID)
            {
                $content['url'] = $server_name."/cron/cricket/vinfotech/get_season_details/".$season_game_uid;
            }
            if($sports_id == KABADDI_SPORTS_ID)
            {
               $content['url'] = $server_name."/cron/kabaddi/vinfotech/get_season_details/".$season_game_uid;
            }
            if($sports_id == SOCCER_SPORTS_ID)
            {
               $content['url'] = $server_name."/cron/soccer/vinfotech/get_season_details/".$season_game_uid;
            }

            if($sports_id == BASEBALL_SPORTS_ID)
            {
               $content['url'] = $server_name."/cron/baseball/vinfotech/get_season_details/".$season_game_uid;
            }

            add_data_in_queue($content,'season_cron');
            
            echo "done";
        }else{
            echo "not done";
        }
        exit();
    }

    /**
     * Used for delete lobby banner s3 file
     * @param int $sports_id
     * @return string
     */
    public function delete_bucket_banner_data($sports_id='')
    {   
        if (!empty($sports_id))
        {
            $this->load->model('Cron_model');
            $this->Cron_model->delete_bucket_banner_data($sports_id);
            echo "done";
        }
        exit();
    }

    /**
     * Used for validate and process payment pending orders
     * @param coid
     * @return string
     */
    public function process_payment_pending_order()
    {
        $this->load->model('Cron_model');
        $this->benchmark->mark('code_start');
            $this->Cron_model->process_payment_pending_order();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for unblock user account
     * @param 
     * @return string
     */
    public function reset_blocked_user(){
        $this->benchmark->mark('code_start');
        if(WRONG_OTP_LIMIT > 0){
            $this->load->model('Cron_model');
            $this->Cron_model->reset_blocked_user();
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    

    /**
     * Used for sync bots teams according to lineup out
     * @param int $collection_master_id
     * @return string
     */
    public function sync_bot_teams($collection_master_id){
        if(PL_LOG_TX){
            log_message("error","IN SYSTEM BOT START : MATCH : $collection_master_id | TIME : ".format_date());
        }   
        $this->benchmark->mark('code_start');
        $pl_allow = isset($this->app_config['pl_allow'])?$this->app_config['pl_allow']['key_value']:0;
        if($pl_allow == 1 && $collection_master_id != ""){
            $this->load->model('Cron_model');
            $this->Cron_model->sync_bot_teams($collection_master_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        if(PL_LOG_TX){
            log_message("error","IN SYSTEM BOT END : MATCH : $collection_master_id | TIME : ".format_date());
        }  
        exit();
    }

    /**
     * Used for push contest list in queue for pull team from PL
     * @param int $lineup_event
     * @return string
     */
    public function pl_match_teams($season_id){
        $this->benchmark->mark('code_start');
        $pl_allow = isset($this->app_config['pl_allow'])?$this->app_config['pl_allow']['key_value']:0;
        if($pl_allow == 1 && $season_id != ""){
            $this->load->model('Cron_model');
            $lineup_event = isset($_REQUEST['lineup_out']) ? $_REQUEST['lineup_out'] : 0;
            $this->Cron_model->pl_match_teams($season_id,$lineup_event);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function process_lineupout_notification_for_game($season_id)
    {
       if(empty($season_id) || FCM_KEY == "" || LINEUP_OUT_PUSH_ENABLE == 0)
        {
            return true;
        }    

        $this->load->model('Cron_model');
        $season_details = $this->Cron_model->get_lineupout_game_details($season_id);
        //echo "<pre>";print_r($season_details);die;
        if(empty($season_details))
        {
            return true;
        }

        $sports_id = $season_details['sports_id'];
        //update notification flag.
        $update_array = array("notify_player_announce" => 1);
        $where_arr = array(
            "season_id" => $season_id,
            "league_id" => $season_details['league_id']
        );
        $this->Cron_model->update_season_for_lineupout_push($update_array,$where_arr);

        $all_users = $this->Cron_model->get_lineupout_game_users($season_details);
        //echo "<pre>";print_r($all_users);die;
        if(empty($all_users))
        {
            return true;
        }    

        $user_ids = array_unique(array_column($all_users, 'user_id')) ;
        $total_users = count($user_ids);
        $user_device_ids = $this->Cron_model->get_users_device_by_ids($user_ids);
        //echo "<pre>";print_r($user_device_ids);die; 
        if(empty($user_device_ids))
        {
            return true;
        }    

        $match_title = $season_details['home']." vs ".$season_details['away'];
        $push_title = $this->lang->line("lineup_out_push_title");
        $temp_msg = $this->lang->line("lineup_out_push_message");
        $push_message  = sprintf($temp_msg,$match_title);
        $common_content = array(
                            "template_data" => array("sports_id"=>$sports_id),
                            'custom_notification_subject' => $push_title,
                            'custom_notification_text' => $push_message,
                            'SITE_TITLE' => SITE_TITLE,
                            'home' => $season_details['home'],
                            'away' => $season_details['away']
                        );

        $chunks = array_chunk($user_device_ids, 999);
        //echo "<pre>";print_r($chunks);die;
        $device_ids =array();
        $current_date = format_date();
        $this->load->helper('queue');
        foreach($chunks as $key1 => $chunk) 
        {   
            $push_notification_data = array();
            $android_push_data = array();
            $ios_push_data = array();
            foreach ($chunk as $key2 => $email) 
            {
                if(!empty($email['device_id']) && !in_array($email['device_id'],$device_ids))
                {

                    $common_content["user_id"] = $email['user_id']; 
                    $common_content["device_id"] = $email['device_id']; 
                    $common_content["device_type"] = $email['device_type']; 
                    $common_content["user_name"] = $email['user_name'];

                    $notification = array();
                    $notification['user_id']                  = (int)$email['user_id'];
                    $notification['notification_status']      = '1';
                    $notification['content']                  = json_encode($common_content);
                    $notification['notification_destination'] = 2;
                    $notification['added_date']               = $current_date;
                    $notification['modified_date']            = $current_date;
                    $notification['device_type'] = $email['device_type'];
                    $notification['device_id'] = $email['device_id'];

                    //device type 1 = Android,2=IOS
                    if($email['device_type'] == 1)
                    {
                        $android_push_data[] = $notification;
                        $device_ids[] = $email['device_id'];
                    }    
                    else if($email['device_type'] == 2)
                    {
                        $ios_push_data[] = $notification;
                        $device_ids[] = $email['device_id'];
                    }
                }
            }

            if(!empty($android_push_data))
            {
                $android_push_data = $this->unique_key($android_push_data,'device_id');
                add_data_in_queue($android_push_data,'lineupout_push_queue');
            }

            if(!empty($ios_push_data))
            {
                $ios_push_data = $this->unique_key($ios_push_data,'device_id');
                add_data_in_queue($ios_push_data,'lineupout_push_queue');
            }   
            //echo "<pre>";print_r($push_notification_data);print_r($device_ids);
        }

        exit("Game $match_title ($season_id) has been processed for send push notifications to all respective users..");
    }

    //internal function used in lineup out push module
    function unique_key($array,$keyname){

     $new_array = array();
     foreach($array as $key=>$value){

       if(!isset($new_array[$value[$keyname]])){
         $new_array[$value[$keyname]] = $value;
       }

     }
     $new_array = array_values($new_array);
     return $new_array;
    }

    public function process_weekly_referral_benefits()
    {
        $this->load->model('Cron_model');
        $this->Cron_model->process_weekly_referral_benefits();
        exit("Processed...");
    }

    public function process_every_cash_contest_referral_benefits($contest_id="")
    {
        if(empty($contest_id))
        {
            exit("Invalid request!");
        }    
        $this->load->model('Cron_model');
        $this->Cron_model->add_every_cash_contest_referral_benefits($contest_id);
        exit("Processed...");
    }

    /**
     * Used to send weekly bonus expiry notification
     */
    public function send_bonus_expiry_notification() { 
        
        $allow_bonus_cash_expiry = isset($this->app_config['allow_bonus_cash_expiry'])?$this->app_config['allow_bonus_cash_expiry']['key_value']:0;
        if($allow_bonus_cash_expiry == 1) {
            $this->benchmark->mark('code_start');
            $this->load->helper('queue_helper');
            add_data_in_queue(array('ben' => 1), 'bonus_expiry_notification');         
            $this->benchmark->mark('code_end');
            echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        }        
    }

    /**
     * Used to expire bonus
     */
    public function bonus_expiry() {   
        $allow_bonus_cash_expiry = isset($this->app_config['allow_bonus_cash_expiry'])?$this->app_config['allow_bonus_cash_expiry']['key_value']:0;
        if($allow_bonus_cash_expiry == 1) {
            $this->benchmark->mark('code_start');
            $this->load->helper('queue_helper');
            add_data_in_queue(array('be' => 1), 'bonus_expiry');         
            $this->benchmark->mark('code_end');
            echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        }
    }

    /**
     * Update consolidated user_specific_active_session in DB {daily and for all Users}
     * @param void
     * @return string
     */
    public function user_daily_active_session()
    {
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');

        $currentDateStart = date('Y-m-d',strtotime("-1 days")) . " 00:00:00";
        $currentDateEnd = date('Y-m-d',strtotime("-1 days")) . " 23:59:59";

        // $currentDateStart = date('2021-04-05') . " 00:00:00";
        // $currentDateEnd = date('2021-04-05') . " 23:59:59";

        $all_records = $this->Cron_model->fetch_active_session_records($currentDateStart, $currentDateEnd);
        if (isset($all_records) && count($all_records) > 0)
        {
            $grouped_record = array();
            foreach ($all_records as $key => $value)
            {
                if(isset($grouped_record[$value['user_id']]))
                {
                    $tmp_arr = $grouped_record[$value['user_id']];
                    $tmp_arr['device_detail'] = json_decode($tmp_arr['device_detail'],TRUE);
                    $tmp_arr['total_seconds'] = $tmp_arr['total_seconds'] + $value['time_spent'];
                    if(isset($tmp_arr['device_detail'][$value['device_os']]))
                    {
                        $tmp_arr['device_detail'][$value['device_os']] = $tmp_arr['device_detail'][$value['device_os']] + $value['time_spent'];
                    }
                    else
                    {
                        $tmp_arr['device_detail'][$value['device_os']] = $value['time_spent'];
                    }
                }
                else
                {
                    $tmp_arr = array();
                    $tmp_arr['user_id'] = $value['user_id'];
                    $tmp_arr['session_date'] = date("Y-m-d",strtotime($currentDateStart));
                    $tmp_arr['total_seconds'] = $value['time_spent'];
                    $tmp_arr['device_detail'] = array();
                    $tmp_arr['device_detail'][$value['device_os']] = $value['time_spent'];
                }
                $tmp_arr['device_detail'] = json_encode($tmp_arr['device_detail']);
                $grouped_record[$value['user_id']] = $tmp_arr;
            }
            $grouped_record = array_values($grouped_record);
            $this->Cron_model->update_daily_active_session_records($grouped_record);
        }

        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function sync_team_user_name()
    {
        $this->load->model('Cron_model');
        $this->Cron_model->sync_team_user_name();
        exit("Processed...");
    }

    /**
     * track uninstalls of mobile app
     * @param void
     * @return string
     */
    public function track_app_uninstall()
    {
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');

        $all_users          = $this->Cron_model->get_all_users();
        $all_active_users   = $this->Cron_model->get_all_active_users();
        $user_list = array_merge($all_users, $all_active_users);
        $unique_user_list = array_values(array_unique($user_list));
        $uninstalled_user_ids = array();

        foreach ($unique_user_list as $key => $value)
        {
            $device_id = array();
            $device_id = $this->Cron_model->get_user_device_id($value);
            if ($device_id && is_array($device_id))
            {
                $device_id = $device_id['device_id'];

                $fields = array(
                    "to" => $device_id,
                    "data" => array("message" => "silent"),
                    "notification" => array("body" => "silent")
                );
                $headers = array(
                    'Authorization: key='.FCM_KEY,
                    'Content-Type: application/json',
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields, JSON_NUMERIC_CHECK));
                try
                {
                    $result = curl_exec($ch);

                    $log_data = array();
                    $log_data['user_id']        = $value;
                    $log_data['header_param']   = json_encode($headers, JSON_NUMERIC_CHECK);
                    $log_data['request_params'] = json_encode($fields, JSON_NUMERIC_CHECK);
                    $log_data['response_data']  = json_encode($result, JSON_NUMERIC_CHECK);

                    $this->load->model("Cron_nosql_model");
                    $this->Cron_nosql_model->insert_nosql('UNINSTALL_CHECK_LOGS', $log_data);

                    if (curl_error($ch))
                    {
                        $error_msg = curl_error($ch);
                    }
                    curl_close($ch);
                    $result = json_decode($result);
                    if (isset($result) && $result->success==0)
                    {
                        $uninstalled_user_ids[]=$value;
                    }
                }
                catch (Exception $e)
                {

                }
            }
        }
        if(!empty($uninstalled_user_ids)){
            $this->load->model("Cron_model");
            $this->Cron_model->update_uninstall_date($uninstalled_user_ids);  
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for deduct tds
     * @param int $contest_id
     * @return string
     */
    public function add_cash_contest_referral_bonus($contest_id = ""){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && $contest_id != ""){
            $this->load->model('Cron_model');
            $this->Cron_model->add_cash_contest_referral_bonus($contest_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
    }

    /**
     * Used for deduct tds
     * @param int $contest_id
     * @return string
     */
    public function add_every_cash_contest_referral_benefits($contest_id = ""){
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($prize_cron == "1" && $contest_id != ""){
            $this->load->model('Cron_model');
            $this->Cron_model->add_every_cash_contest_referral_benefits($contest_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
    } 

    /**
     * function to send the automatic push
     */
    public function send_fixture_push($collection_master_id="")
    {
        if(isset($collection_master_id))
        {
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->cron_model->send_fixture_push($collection_master_id);
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        }
        exit;
    }

     /**
     * Used to expire coin
     */
    public function coin_expiry() {   
        $allow_coin_expiry = isset($this->app_config['allow_coin_expiry'])?$this->app_config['allow_coin_expiry']['key_value']:0;
        $coin_system = isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
        if($allow_coin_expiry == 1 && $coin_system ==1) {
            $this->benchmark->mark('code_start');
            $this->load->model('User_bonus_cash_model',"UBCM");
            $this->UBCM->coin_expiry();
            $this->benchmark->mark('code_end');
            echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        }
    }


    public function user_points_update() {
        $this->load->model('Cron_model');
        $this->benchmark->mark('code_start');
        $user_points =  $this->Cron_model->points_update();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
       exit;
    }

    /**
     * Used for update h2h users level
     * @param string $entity_id
     * @return string
     */
    public function update_h2h_user_level($entity_id = ""){
        $h2h_challenge = isset($this->app_config['h2h_challenge'])?$this->app_config['h2h_challenge']['key_value']:0;
        $this->benchmark->mark('code_start');
        if($h2h_challenge == "1" && $entity_id != ""){
            $this->load->model('Cron_model');
            $this->Cron_model->update_h2h_user_level($entity_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit;
    }
    
    /*
     * Used to check if pending transaction in crypto payment gateway
     * @return string
     */
    public function process_crypto_pending_transaction(){
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->process_crypto_pending_transaction();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit;
    }

     /** this is one time cron to sync old users to social db */
     public function sync_old_users_in_social() {
        $this->load->model('Cron_model');
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $user_points =  $this->Cron_model->sync_old_users_in_social();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
       exit;
     }
     
    /**
     * method to finalize the pending withdraw transaction of pay.
     */
    public function process_new_payout_pending_order()
    {   
        $this->benchmark->mark('code_start');
        $this->load->model('Payout_model');
        $this->Payout_model->process_new_payout_pending_order();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit;
    }

    public function rp_test_cache($action="")
    {
      
        if($action=="set")
        {
            for($i=1;$i<=50;$i++)
            {
                $cache_key = "rp_test_".$i;
                $rp_new = array("name"=>"Rahul Parmar","Age"=>"32","City"=>"Indore","count"=>"Count : $i");
                $this->set_cache_data($cache_key,$rp_new,30000);
            }
            die("Cache set");
        }
        else if($action =="delete")
        {
            $cache_key = "rp_test_";
            $this->delete_wildcard_cache_data($cache_key);
            die("Cache deleted");
        }
        
        $check = $this->get_cache_data("rp_test_44");
        echo "<pre>";print_r($check);die;
        die("Invalid Action..");
        
    }


    
   /**
    * Subscribe to fcm topic
    * Bulk subscription of android and IOS device topken
    * @param topic name
    */
    public function subscribe_fcm_token($topic=FCM_TOPIC."stock")
    {
        $this->benchmark->mark('code_start');
        if(isset($topic) && $topic != ""){
            $this->load->model("Cron_model");
            $this->Cron_model->subscribe_fcm_token($topic);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function get_stuck_match_list(){
        $this->load->model("Cron_model");
        $result = $this->Cron_model->get_stuck_match_list();
        echo json_encode($result);exit;
    }

    /**
     * method to use for the gst report create.
     */
    public function process_deposit_gst_report()
    {   
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->process_deposit_gst_report();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit;
    }

}
