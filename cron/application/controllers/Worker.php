<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Worker extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function index() {
    }  
    
    /**
     * Used for process bucket queue and upload on s3
     * @param 
     * @return boolean
     */
    public function process_bucket() {
        $queue_name = "bucket";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data) && isset($data['file_name']) && BUCKET_STATIC_DATA_ALLOWED == 1){
                    $file_name = $data['file_name'].".json";
                    $filePath = BUCKET_STATIC_DATA_PATH.BUCKET_DATA_PREFIX.$file_name;
                    if(isset($data['action']) && $data['action'] == "delete"){
                        $data_arr = array();
                        $data_arr['file_path'] = $filePath;
                        $this->load->library('Uploadfile');
                        $upload_lib = new Uploadfile();
                        $upload_lib->delete_file($data_arr);
                    }else{
                        $json_data = json_encode($data['data']);
                        $json_file_path = "/tmp/".$file_name;
                        $new_json = @fopen($json_file_path, "w");
                        fwrite($new_json, $json_data);
                        fclose($new_json);

                        $data_arr = array();
                        $data_arr['file_path'] = $filePath;
                        $data_arr['source_path'] = $json_file_path;
                        $this->load->library('Uploadfile');
                        $upload_lib = new Uploadfile();
                        $is_uploaded = $upload_lib->upload_file($data_arr);
                        if($is_uploaded){
                            @unlink($json_file_path);
                        }
                    }
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process sms queue and send sms to user
     * @param 
     * @return boolean
     */
    public function process_sms() {
        $queue_name = "sms";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data)){
                    $this->load->helper('default');
                    //for india
                    $config = array();
                    if(isset($this->app_config['sms_config']['key_value']) && $this->app_config['sms_config']['key_value']==1)
                    {
                        $config['sms_gateway_api_endpoint'] = $this->app_config['sms_config']['custom_data']['sms_gateway_api_endpoint'];
                        $config['sms_gateway_auth_key'] = $this->app_config['sms_config']['custom_data']['sms_gateway_auth_key'];
                        $config['sms_gateway_sender_id'] = $this->app_config['sms_config']['custom_data']['sms_gateway_sender_id'];
                        $config['sms_gateway_route_id'] = $this->app_config['sms_config']['custom_data']['sms_gateway_route_id'];
                        $config['sms_gateway_template'] = $this->app_config['sms_config']['custom_data']['sms_gateway_template'];
                    }else{
                        log_message("error","\n\n\n some issue in process_msm method of worker, not getting proper configuration");
                        return false;
                    }
                        
                    if(isset($data['phone_code']) && $data['phone_code'] == "91"){
                        if($this->app_config['sms_config']['custom_data']['active_sms_gateway'] == "msg91"){
                            send_msg91_sms($data,$config);
                        }else if($this->app_config['sms_config']['custom_data']['active_sms_gateway'] == "bulksmspremium"){
                            send_bulksmspremium_sms($data,$config);
                        }else if($this->app_config['sms_config']['custom_data']['active_sms_gateway'] == "kaleyra"){
                            send_kaleyra_sms($data,$config);
                        }else if($this->app_config['sms_config']['custom_data']['active_sms_gateway'] == "onnsms"){
                            send_onnsms_sms($data,$config);
                        }else if($this->app_config['sms_config']['custom_data']['active_sms_gateway'] == "twilio"){
                            send_twilio_sms($data,$config);
                        }else{
                            if(TWO_FACTOR_BY_CURL == 1){
                                //this is for two factor transactional template using curl
                                send_two_factor_sms($data,1,$config);
                            }else{
                                $this->load->library('TwoFactorSMS');
                                $TwoF = new TwoFactorSMS($config['sms_gateway_auth_key'], $config['sms_gateway_api_endpoint']);
                                $result = $TwoF->SendSMSOTPCustomWithTemplate($data['mobile'], $data['otp'],$config['sms_gateway_template']);
                            }
                        }
                    }else{
                        if($this->app_config['sms_config']['custom_data']['active_sms_gateway'] == "msg91"){
                            send_msg91_sms($data,$config);
                        }else if($this->app_config['sms_config']['custom_data']['active_sms_gateway'] == "twilio"){
                            send_twilio_sms($data,$config);
                        }
                        //For internation SMS provider
                    }
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process cron queue
     * @param 
     * @return boolean
     */
    public function process_cron() {

        $queue_name = "cron";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }

            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }  

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process email queue and send email to user
     * @param 
     * @return boolean
     */
    public function process_email(){
        $queue_name = "email";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data)){
                    if (isset($data['content']) && !is_array($data['content'])) {
                        $data['content'] = json_decode($data['content'], TRUE);
                    }
                    $this->load->model('Nodb_model');
                    $notification_type = $data['notification_type'];
                    if($notification_type == "manual_deposit"){
                        $tmp_path = "emailer/manual_deposit.php";
                        $message  = $this->load->view($tmp_path, $data, TRUE);
                        if(empty($data['subject']))
                        {
                            $data['subject'] = "New Transaction Alert!";
                        }
                        $this->Nodb_model->send_email($data['email'], $data['subject'],$message);
                    }else{
                        $email_template_cache = 'cron_email_template';
                        $email_template = $this->Nodb_model->get_cache_data($email_template_cache);
                        if(!$email_template){
                            $this->load->model('Cron_model');
                            $email_template = $this->Cron_model->get_email_template_list();
                            $this->Nodb_model->set_cache_data($email_template_cache,$email_template,REDIS_30_DAYS);
                        }
                        $template_data = array_column($email_template, NULL, 'notification_type');
                        if(array_key_exists($notification_type, $template_data)){
                            if(isset($template_data[$notification_type]['template_path']) && $template_data[$notification_type]['template_path'] != ""){
                                $tmp_path = "emailer/".$template_data[$notification_type]['template_path'];
                                $message  = $this->load->view($tmp_path, $data, TRUE);
                                if(empty($data['subject']))
                                {
                                    $data['subject'] = $template_data[$notification_type]['subject'];
                                }
                                $this->Nodb_model->send_email($data['email'], $data['subject'],$message);
                            }
                        }
                    }
                    
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);
        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process email_otp queue and send email to user
     * @param 
     * @return boolean
     */
    public function process_email_otp(){
        $queue_name = "email_otp";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data)){
                    if (isset($data['content']) && !is_array($data['content'])) {
                        $data['content'] = json_decode($data['content'], TRUE);
                    }
                    $this->load->model('Nodb_model');
                    $notification_type = $data['notification_type'];
                    $email_template_cache = 'cron_email_template';
                    $email_template = $this->Nodb_model->get_cache_data($email_template_cache);
                    if(!$email_template){
                        $this->load->model('Cron_model');
                        $email_template = $this->Cron_model->get_email_template_list();
                        $this->Nodb_model->set_cache_data($email_template_cache,$email_template,REDIS_30_DAYS);
                    }
                    $template_data = array_column($email_template, NULL, 'notification_type');
                    if(array_key_exists($notification_type, $template_data)){
                        if(isset($template_data[$notification_type]['template_path']) && $template_data[$notification_type]['template_path'] != ""){
                            $tmp_path = "emailer/".$template_data[$notification_type]['template_path'];
                            $message  = $this->load->view($tmp_path, $data, TRUE);
                            $this->Nodb_model->send_email($data['email'], $data['subject'],$message);
                        }
                    }
                    
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);
        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process invite_email queue and send email
     * @param 
     * @return boolean
     */
    public function process_invite_email(){
        $queue_name = "invite_email";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data)){
                    $connection2 = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
                    $channel2 = $connection2->channel();
                    foreach ($data as $email_data) {
                        $push_data = json_encode($email_data);
                        $message = new AMQPMessage($push_data, array('delivery_mode' => 1, 'content_type' => 'application/json'));
                        $channel2->basic_publish($message,'', 'email');
                    }
                    $channel2->close();
                    $connection2->close();
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);
        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process contest queue
     * @param 
     * @return boolean
     */
    public function process_contest() {
        $queue_name = "contest";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data) && isset($data['action'])){
                    //contest auto recuring
                    if($data['action'] == "auto_recurring" && isset($data['data']['contest_unique_id'])){
                        $this->load->model('Cron_model');
                        $this->Cron_model->auto_recurring_contest($data['data']['contest_unique_id']);
                    }
                }

            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process cron queue
     * @param 
     * @return boolean
     */
    public function process_contestpdf() {

        $queue_name = "contestpdf";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }
    
    
    /**
     * Used for process game cancellation queue
     * @param 
     * @return boolean
     */
    public function process_game_cancellation() {
        $queue_name = "game_cancel";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg) 
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data) && isset($data['action'])){                
                    if($data['action'] == "cancel_game"){            
                        $this->load->model('Dfs_model');
                        $this->Dfs_model->game_cancellation_by_id($data);
                    }
                    if($data['action'] == "cancel_collection"){            
                        $this->load->model('Dfs_model');
                        $this->Dfs_model->collection_cancel_by_id($data);
                    }
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }

    function push_queue_process()
    {
    	$queue_name = 'push';
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
	       try{
                $notification_title="";
        	  	$data = json_decode($msg->body, TRUE);
        	  	if(!empty($data))
        	  	{
                   if(!empty($data['deviceIDS']))
                   {
                       if(is_array($data['deviceIDS']))
                       {
                        $device_ids = $data['deviceIDS'];
                       }
                       else
                       {
                        $device_ids = explode(',',$data['deviceIDS']);
                       }
                   }
                   else
                   {

                       $device_ids = array($data['device_id']);
                   }
                    // ios devide ids 
                    if(!empty($data['ios_device_ids']))
                    {
                        if(is_array($data['ios_device_ids']))
                        {
                            $ios_device_ids = $data['ios_device_ids'];
                        }
                        else
                        {
                            $ios_device_ids = explode(',',$data['ios_device_ids']);
                        }
                    }

        			$notification_data = $data;
                    // echo "<pre>";
                    // print_r($notification_data);die();
        			$message = $data['custom_notification_text'];
                    $notification_title = $message;
                    if(isset($data['custom_notification_subject']) && !empty($data['custom_notification_subject'])) {
                        $notification_title = $data['custom_notification_subject'];
                    }

        			if(isset($notification_data['content']))
        			{
        				$content = @json_decode($notification_data['content'], TRUE);
        			}
                    
        		    $fields = array();
        				unset($data['deviceIDS'],$data['ios_device_ids']);

        		    //$registatoin_ids = array(), $title = '', $message = '', $badge = 1, $Data
                     $this->load->library('push_notification');
        		     $result = $this->push_notification->push_notification_android($device_ids,$notification_title,$message,1,$content );
                     if(!empty($ios_device_ids))
                     {
                        $this->push_notification->push_notification_ios($ios_device_ids,$notification_title,$message,1,$content );
                     }
        	  	}
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

	       $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

         $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }

    public function report_queue_process() 
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $queue_name = 'admin_reports';
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);

        $callback = function($msg)
        {   
            try{
                $this->load->model('Report_model');
                $data = json_decode($msg->body, TRUE);
                $temp_convert_start = get_timezone(strtotime($data['from_date']),'Y-m-d',$this->app_config['timezone'],1);
                $temp_convert_end = get_timezone(strtotime($data['to_date']),'Y-m-d',$this->app_config['timezone'],1);
                $data['start_date'] = $temp_convert_start['date'];
                $data['end_date'] = $temp_convert_end['date'];
                if(!empty($data['report_type']))
                {   
                    /* Generate Data according to report type*/
                    switch($data['report_type'])
                    {
                        case 'user_report':
                            
                            $title =  "User Report";
                            $export_data = $this->Report_model->user_report($data);
                            
                        break;
                        case 'user_money_paid':
                        
                            $title =  "User Money Paid Report";
                            $export_data = $this->Report_model->user_money_paid($data);
                        
                        break;
                        case 'user_deposit':
                        
                            $title =  "User Deposit Report";
                            $export_data = $this->Report_model->user_deposit($data);
                        
                        break;
                        case 'referral':
                        
                            $title =  "Referral Report";
                            $export_data = $this->Report_model->referral($data);
                        
                        break;

                        case 'contest_report':
                        
                            $title =  "Contest Report";
                            $export_data = $this->Report_model->contest_report($data);
                        
                        break;

                        case 'user_list_report':
                        
                            $title =  "User list Report";
                            $export_data = $this->Report_model->user_list_report($data);
                        
                        break;

                        case 'tax_invoice_reports':
                        
                            $title =  "GST invoice report";
                            $user_info	= $this->Report_model->get_user_info($data);
                            $export_data = $this->Report_model->gst_invoice_report($user_info,$data);
                            // $export_data = $this->Report_model->gst_invoice_report($data);
                        break;
                        
                        case 'match_report':
                        
                            $title =  "Match Report";
                            $export_data = $this->Report_model->match_report($data);
                        
                        break;

                        case 'transaction_report':
                            $this->load->model('Nodb_model');
                            $title =  "Transaction Report";
                            $export_data = $this->Report_model->get_transaction_report($data);
                        
                        break;

                        case 'lf_user_money_paid':
                            $title =  "Live Fantasy User Money Paid";
                            $data['module_name'] = 'livefantasy';
                            $export_data = $this->Report_model->user_money_paid($data);
                        
                        break;

                        case 'LF_contest_report':
                            $this->load->helper('queue_helper');
                            add_data_in_queue($data,'lf_admin_reports');
                        break;

                    }


                    if(!empty($export_data))
                    {
                        /* Prepare CSV DATA*/
                        $header             = array_keys($export_data[0]);
                        $camelCaseHeader    = array_map("camelCaseString", $header);
                        $export_data        = array_merge(array($camelCaseHeader),$export_data);
                        
                        $file_name = $data['report_type'].'_'.round(microtime(true) * 1000).'.csv';
                        $csv_file_path = "/tmp/".$file_name;

                        //  Create & Write CSV -- W
                        $fp = fopen($csv_file_path, 'w');
                        foreach ($export_data as $fields)
                        {
                            fputcsv($fp, $fields);
                        }
                        fclose($fp);
                        
                        /* Put CSV from tmp to S3 Bucket*/

                        $filePath = BUCKET_REPORTS_PATH.BUCKET_DATA_PREFIX.$file_name;
                        try{
                            $data_arr = array();
                            $data_arr['file_path'] = $filePath;
                            $data_arr['file_type'] = 'csv';
                            $data_arr['source_path'] = $csv_file_path;
                            $this->load->library('Uploadfile');
                            $upload_lib = new Uploadfile();
                            $is_uploaded = $upload_lib->upload_file($data_arr);
                            if($is_uploaded){
                                /* Delete csv file from tmp directory*/
                                @unlink($csv_file_path);
                            }

                        }catch(Exception $e){
                            //echo 'Caught exception: '.  $e->getMessage(). "\n";
                            return false;
                        }
                        /* Send Report Link to admin*/
                        $link  = IMAGE_PATH.$filePath;
                        $this->load->model('Nodb_model');
                        $subject    = SITE_TITLE.': '.$title.' is ready to download';
                        $data       = array('link' => $link,'title' => $title);
                        $tmp_path   = "emailer/admin-report";
                        $message    = $this->load->view($tmp_path, $data, TRUE);
                        $this->Nodb_model->send_email(REPORT_ADMIN_EMAIL,$subject,$message);
                    }

                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
    
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);
    
        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process pl_teams queue
     * @param 
     * @return boolean
     */
    public function process_pl_teams() {

        $queue_name = "pl_teams";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    if(PL_LOG_TX){
                        log_message("error","PROCESS PL WORKER START: URL : ".$data['url']." | TIME : ".format_date());
                    } 

                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);

                    if(PL_LOG_TX){
                        log_message("error","PROCESS PL WORKER END: URL : ".$data['url']." | TIME : ".format_date());
                    }

                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process recent_league_cron queue
     * @param 
     * @return boolean
     */
    public function process_recent_league() {

        $queue_name = "recent_league_cron";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process team_cron queue
     * @param 
     * @return boolean
     */
    public function process_team_cron() {

        $queue_name = "team_cron";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process season_cron queue
     * @param 
     * @return boolean
     */
    public function process_season_cron() {

        $queue_name = "season_cron";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process player_cron queue
     * @param 
     * @return boolean
     */
    public function process_player_cron() {

        $queue_name = "player_cron";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process score_cricket queue
     * @param 
     * @return boolean
     */
    public function process_score_cricket() {

        $queue_name = "score_cricket";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process score_soccer queue
     * @param 
     * @return boolean
     */
    public function process_score_soccer() {

        $queue_name = "score_soccer";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process score_cron queue
     * @param 
     * @return boolean
     */
    public function process_score_cron() {

        $queue_name = "score_cron";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }
    
    /**
     * Used for process point_update_cron queue
     * @param 
     * @return boolean
     */
    public function process_sc_points_cron() {

        $queue_name = "point_update_cron";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process contest_close queue
     * @param 
     * @return boolean
     */
    public function process_contest_close() {

        $queue_name = "contest_close";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process prize_distribution queue
     * @param 
     * @return boolean
     */
    public function process_prize_cron() {

        $queue_name = "prize_distribution";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process prize_notification queue
     * @param 
     * @return boolean
     */
    public function process_prize_notify() {

        $queue_name = "prize_notification";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process score_baseball queue
     * @param 
     * @return boolean
     */
    public function process_score_baseball() {

        $queue_name = "score_baseball";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process game for lineup out notification 
     * @param 
     * @return boolean
     */
    public function process_lineupout_game() {

        $queue_name = "lineupout_game_process";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }


    /**
     * Used for send lineup out push notification 
     * @param 
     * @return boolean
     */
    public function process_lineupout_push() {
        $this->load->library('Push_notification');
        $queue_name = "lineupout_push_queue";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{   
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $device_ids = array_column($data, 'device_id');
                    $notification_data = $data[0];
                    $notification_type=0;
                    $message = "";
                    if(isset($notification_data['content']))
                    {
                        $content = @json_decode($notification_data['content'], TRUE);
                    }
                    $title = $content['custom_notification_subject'];
                    $message= $content['custom_notification_text'];
                    

                    if(empty($content['template_data']))
                    {
                        $content['template_data'] = array();
                    }

                    $content['template_data']['notification_type'] = $notification_type;

                    //$registatoin_ids = array(), $title = '', $message = '', $badge = 1, $Data
                    if(isset($notification_data['device_type']) && $notification_data['device_type'] == 2)
                    {
                        $result = $this->push_notification->push_notification_ios($device_ids,$title,$message,1,$content['template_data']);
                    }    
                    else
                    {
                        $result = $this->push_notification->push_notification_android($device_ids,$title,$message,1,$content['template_data']);
                    }    
                    
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }
    
/**
     * Used for tracking user
     * @param 
     * @return boolean
     */
    public function process_track_user() {
        $queue_name = "track_user";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg) {
            $data = json_decode($msg->body, TRUE);
            try {
                if(!empty($data)){                   
                    $this->load->model('Cron_model');
                    $url = $this->Cron_model->track_user_record($data);
                    if(!empty($url)){
                        $curl_handle = curl_init();
                        curl_setopt($curl_handle,CURLOPT_URL,$url);
                        curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                        $buffer = curl_exec($curl_handle);
                        curl_close($curl_handle);
                    }
                }

            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }

    
    /**
    * Used for process host rake
    * @param 
    * @return boolean
    */
    public function process_host_rake() {
        $queue_name = "host_rake";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";

        $callback = function($msg) {
            $data = json_decode($msg->body, TRUE);
            try {
                if(!empty($data)){
                    $this->load->model('Cron_model');
                    $this->Cron_model->process_contest_host_rake($data['contest_id']);
                }
            }
            //catch exception
            catch(Exception $e)
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

     /**
     * Used for process player_cron queue
     * @param 
     * @return boolean
     */
    public function process_lineup_move_cron() {

        $queue_name = "lineup_move_cron";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
    * Used for process bonus queue
    * @param
    * @return boolean
    */
    public function process_bonus() {

        $queue_name = "user_bonus";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            $data = json_decode($msg->body, TRUE);
            if (!empty($data))
            {
                $this->load->model('User_bonus_cash_model');
                $this->User_bonus_cash_model->process_bonus($data);
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }


    /**
    * Used for process bonus queue
    * @param
    * @return boolean
    */
    public function process_bonus_expiry_notification() {

        $queue_name = "bonus_expiry_notification";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            $data = json_decode($msg->body, TRUE);
            if (!empty($data))
            {
                $this->load->model('User_bonus_cash_model');
                $this->User_bonus_cash_model->send_bonus_expiry_notification();
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
    * Used for process bonus queue
    * @param
    * @return boolean
    */
    public function process_bonus_expiry() {

        $queue_name = "bonus_expiry";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            $data = json_decode($msg->body, TRUE);
            if (!empty($data))
            {
                $this->load->model('User_bonus_cash_model');
                $this->User_bonus_cash_model->bonus_expiry();
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process leaderboard queue
     * @param 
     * @return boolean
     */
    public function process_leaderboard() {

        $queue_name = "leaderboard";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }

            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }  

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process gst cron queue
     * @param 
     * @return boolean
     */
    public function process_gst() {

        $queue_name = "gst";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }

            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process prize revert queue
     * @param 
     * @return boolean
     */
    public function process_prize_revert() {

        $queue_name = "prizerevert";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }

            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process tds queue
     * @param 
     * @return boolean
     */
    public function process_tds() {

        $queue_name = "tds";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }

            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    
    /**
     * Used for process bucket queue and upload on s3
     * @param 
     * @return boolean
     */
    public function process_notification() {
        $queue_name = "notification";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg) {
            try {
                $data = json_decode($msg->body, TRUE);
                if(!empty($data) && isset($data['action'])) {                
                    if($data['action'] == "feedback") {            
                        $this->load->model('Feedback_model');
                        $this->Feedback_model->notify_user_on_new_feedback($data);
                    } else if($data['action'] == "more_referral") {            
                        $this->load->model('Referral_model');
                        $this->Referral_model->notify_user_for_more_referral();
                    } else if($data['action'] == "referral") {            
                        $this->load->model('Referral_model');
                        $this->Referral_model->notify_user_for_referral();
                    } else if($data['action'] == "affiliate") {            
                        $this->load->model('Affiliate_model');
                        $this->Affiliate_model->notify_user_for_affiliate_program();
                    } else if($data['action'] == "daily_quiz") {            
                        $this->load->model('Quiz_model');
                        $this->Quiz_model->notify_user_for_quiz($data);
                    } else if($data['action'] == "wfmc") {            
                        $this->load->model('User_bonus_cash_model','ubcm');
                        $this->ubcm->notify_few_more_coins_away();
                    }else if($data['action'] == "gift_claim") {            
                        $this->load->model('User_bonus_cash_model','ubcm');
                        $this->ubcm->notify_gift_claim();
                    }else if($data['action'] == "user_engage_noon") {            
                        $this->load->model('User_bonus_cash_model','ubcm');
                        $this->ubcm->notify_user_engage_noon();
                    }else if($data['action'] == "user_engage_evening") {            
                        $this->load->model('User_bonus_cash_model','ubcm');
                        $this->ubcm->notify_user_engage_evening();
                    }else if($data['action'] == "spin_user_engage") {            
                        $this->load->model('User_bonus_cash_model','ubcm');
                        $this->ubcm->notify_spin_user_engage();
                    }else if($data['action'] == "not_played_thrice") {            
                        $this->load->model('User_bonus_cash_model','ubcm');
                        $this->ubcm->notify_not_played_thrice();
                    }else if($data['action'] == "coin_exp_reminder") {            
                        $this->load->model('User_bonus_cash_model','ubcm');
                        $this->ubcm->send_coins_expiry_notification();
                    }else if($data['action'] == "s_predictor_live") {            
                        $this->load->model('Prediction_model','pm');
                        $this->pm->notify_sport_predictor_live();
                    }                  
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process referral queue
     * @param 
     * @return boolean
     */
    public function process_referral() {

        $queue_name = "referral";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }

            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * this function is part of  auto push sendig process while a fixture is published.
     */
    public function auto_push_process()
    {
        $queue_name = AUTO_PUSH_QUEUE;
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            $data = json_decode($msg->body, TRUE);
            if (!empty($data))
            {
                $this->process_push_data($data);
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    public function process_push_data($data)
    {
        $this->load->model('Cron_model');
        $this->Cron_model->send_fixture_push($data);
    }

    function dfs_auto_push_queue_process()
    {
    	$queue_name = DFS_AUTO_PUSH_QUEUE;
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C push queue '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
	  	$data = json_decode($msg->body, TRUE);
         
        try{
            if(!empty($data))
    	  	{ 
                $android_device_ids = array();
                $ios_device_ids = array();

                foreach($data['push_notification_data'] as $key => $data_item)
                {

                    $android_device_ids = $ios_device_ids = array();
                    foreach($data_item['device_details'] as $device_detail )
                    {
                        if(isset($device_detail['device_type']) && $device_detail['device_type']=='1' )
                        {
                            $android_device_ids[] = $device_detail['device_id'];
                        }

                        if(isset($device_detail['device_type']) && $device_detail['device_type']=='2' )
                        {
                            $ios_device_ids[] = $device_detail['device_id'];
                        }
                    }
                   
                    if (isset($data_item['content']))
                    {
                        $data['push_notification_data'][$key]['content'] = json_encode($data['push_notification_data'][$key]['content']);

                        $notification_type=0;
                        $message = "";

                        if(isset($data_item['content']))
                        {
                            $content = json_decode($data['push_notification_data'][$key]['content'], TRUE);
                        }
                        if(isset($data_item['notification_type']))
                        {
                            $notification_type = $data_item['notification_type'];
                        }
                        if(in_array($notification_type, [441,442,443]))
                        {
                            $notification_title = $content['custom_notification_subject'];
                            $message=  $content['custom_notification_text'];
                        }
                        if(empty($content['template_data']))
                        {
                            $content['template_data'] = array();
                            $content['template_data']['notification_type'] = $data_item['notification_type'];
                        }
                        //$registatoin_ids = array(), $title = '', $message = '', $badge = 1, $Data
                        $this->load->library('push_notification');
                        //echo '<pre>';var_dump($android_device_ids);var_dump($ios_device_ids);echo $notification_title;echo '#message#'.$message;print_R($content);die('dfd');
                        if(!empty($android_device_ids))
                        {
                            $result = $this->push_notification->push_notification_android($android_device_ids,$notification_title,$message,1,$content['template_data'] );
                        }
                        
                        if(!empty($ios_device_ids ))
                        {
                            $result = $this->push_notification->push_notification_ios($ios_device_ids,$notification_title,$message,'1',$content['template_data'] );
                        }
                    }
                }
    	  	}
        }
        //catch exception
        catch(Exception $e) 
        {
          return false;
        }
  		$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }

    /**
    * Used for process coin data update queue
    * @param
    * @return boolean
    */
    public function process_coinexpiry() {

        $queue_name = "user_coins";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            $data = json_decode($msg->body, TRUE);
            if (!empty($data))
            {
                $this->load->model('User_bonus_cash_model');
                $this->User_bonus_cash_model->process_coins($data);
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * common payout queue added at time of pay
     */
    public function process_payout() {

        $queue_name = "payout";
        
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            $data = json_decode($msg->body, TRUE);
            if(!empty($data) && isset($data['action'])){
                $this->load->model("Payout_model","pm");                
                switch($data['action'])
                {
                    case "cashfree_status_update":
                        $this->pm->cashfree_payout_status_update($data);
                        break;
                    case "mpesa":
                        $this->pm->mpesa_payout_status_update($data);
                        break;
                    case "payumoney_status_update";
                        $this->pm->payumoney_payout_status_update($data);
                        break;

                }
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process h2h queue
     * @param 
     * @return boolean
     */
    public function process_h2h() {

        $queue_name = "h2h";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }

            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }  

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process dfs lineup queue
     * @param 
     * @return boolean
     */
    public function process_dfs_lineup() {

        $queue_name = "dfs_lineup";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }

            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process dfs tds queue
     * @param 
     * @return boolean
     */
    public function process_dfs_tds() {

        $queue_name = "dfs_tds";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process notify node queue
     * @param 
     * @return boolean
     */
    public function process_notify_node() {

        $queue_name = "notify_node";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data) && isset($data['action'])){
                    $action = $data['action'];
                    unset($data['action']);
                    if($action == "update_rank"){  
                        $this->load->model('Cron_model');
 		                $this->Cron_model->update_match_rank($data);
                    } else if($action == "update_score"){  
                        $this->load->model('Cron_model');
 		                $this->Cron_model->update_match_score($data);
                    }
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process fcm topic queue
     * @param 
     * @return boolean
     */
    public function process_fcm_topic() {
        $queue_name = "fcm_topic";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data) && !empty($data['type'])) {
                    $this->load->library('fcm/Fcm');
                    $topic = $data['topic'];
                    if($data['type'] == "send"){
                        $msg_arr = array("topic"=>$topic,"data"=>$data);
                        $this->fcm->send($msg_arr); 
                    }
                    elseif($data['type'] == "subscribe" && !empty($data['ids'])) {
                        $topic_arr = array("topic"=>$topic,"device_ids"=>$data['ids']);
                        $this->fcm->subscribe($topic_arr);
                    }else if($data['type'] == "unsubscribe" && !empty($data['ids'])) {
                        $topic_arr = array("topic"=>$topic,"device_ids"=>$data['ids']);
                        $this->fcm->unsubscribe($topic_arr);
                    }
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process prize_distribution queue
     * @param 
     * @return boolean
     */
    public function process_dfs_tournament() {
        $queue_name = "dfs_tournament";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        return true;
    }

    /**
     * Used for process user match report
     * @param 
     * @return boolean
     */
    public function process_user_match_report() {
        $queue_name = "report_cron";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)) 
                {
                    $curl_handle = curl_init();
                    curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                    $buffer = curl_exec($curl_handle);
                    curl_close($curl_handle);
                }
            }
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }  
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);
        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }

}

