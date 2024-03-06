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
     * Used for process cron queue
     * @param 
     * @return boolean
     */
    public function process_cron() {

        $queue_name = "lf_cron";
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

        $queue_name = "lf_prize_distribution";
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
        $queue_name = "lf_game_cancel";
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
                        $this->load->model('Cron_model');
                        $this->Cron_model->game_cancellation_by_id($data);
                    }
                    if($data['action'] == "cancel_collection"){            
                        $this->load->model('Cron_model');
                        $this->Cron_model->collection_cancel_by_id($data);
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
     * Used for process contest queue
     * @param 
     * @return boolean
     */
    public function process_contest() {
        $queue_name = "lf_contest";
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
     * Used for process email queue and send email to user
     * @param 
     * @return boolean
     */
    public function process_email(){
        $queue_name = "lf_email";
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
                    $this->load->model('user/Nodb_model');
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
                            if(empty($data['subject']))
                            {
                                $data['subject'] = $template_data[$notification_type]['subject'];
                            }
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

    function push_queue_process()
    {
        $queue_name = 'lf_push';
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
    * Used for tracking user
    * @param 
    * @return boolean
    */
    public function process_host_rake() {
        $queue_name = "lf_host_rake";
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
     * Used for process gst queue
     * @param 
     * @return boolean
     */
    public function process_gst() {

        $queue_name = "lf_gst";
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
     * method to manage reports send on mail
     * @param Array
     * @response boolean
     */
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
                if(!empty($data['report_type']))
                {   
                    /* Generate Data according to report type*/
                    switch($data['report_type'])
                    {
                        case 'LF_contest_report':
                            $title =  "Live Fantasy contest Report";
                            $export_data = $this->contest_report($data);
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
   
}