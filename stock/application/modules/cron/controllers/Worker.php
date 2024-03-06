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
    * Used for process kite connect api request
    * @param
    * @return boolean
    */
    public function process_kite_connect() {

        $queue_name = "kite_connect";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            $data = json_decode($msg->body, TRUE);
            if(!empty($data) && isset($data['action'])){                
                if($data['action'] == "instrument_list"){            
                    $this->load->model('cron/Cron_model');
                    $this->Cron_model->instrument_list();
                } else if($data['action'] == "update_stock_price"){            
                    $this->load->model('cron/Cron_model');
                    $this->Cron_model->update_stock_price($data);
                } else if($data['action'] == "update_stock_historical_data_day_wise"){            
                    $this->load->model('cron/Cron_model');
                    $this->Cron_model->update_stock_historical_data_day_wise($data);
                } else if($data['action'] == "update_stock_historical_data_minute_wise"){            
                    $this->load->model('cron/Cron_model');
                    $this->Cron_model->update_stock_historical_data_minute_wise($data);
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
    * Used for process twelvedata API request
    * @param
    * @return boolean
    */
    public function process_stock_data() {

        $queue_name = "stock_data";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            $data = json_decode($msg->body, TRUE);
            if(!empty($data) && isset($data['action'])){                
                if($data['action'] == "stock_list"){            
                    $this->load->model('cron/Cron_model');
                    $this->Cron_model->stock_list();
                } else if($data['action'] == "update_stock_latest_quote"){            
                    $this->load->model('cron/Cron_model');
                    $this->Cron_model->update_stock_latest_quote($data);
                } else if($data['action'] == "stock_historical_data_day_wise"){            
                    $this->load->model('cron/Cron_model');
                    $this->Cron_model->stock_historical_data_day_wise($data);
                } else if($data['action'] == "stock_historical_data_minute_wise"){            
                    $this->load->model('cron/Cron_model');
                    $this->Cron_model->stock_historical_data_minute_wise($data);
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
    * Used for process common feed api request
    * @param
    * @return boolean
    */
    public function process_stock_feed_data() {

        $queue_name = "stock_feed";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data) && isset($data['action'])){                
                    if($data['action'] == "stock_list"){            
                        $this->load->model('cron/Stock_feed_model');
                        $this->Stock_feed_model->stock_list();
                    } else if($data['action'] == "update_stock_latest_quote"){            
                        $this->load->model('cron/Stock_feed_model');
                        $this->Stock_feed_model->update_stock_latest_quote($data);
                    } else if($data['action'] == "stock_historical_data_day_wise"){            
                        $this->load->model('cron/Stock_feed_model');
                        $this->Stock_feed_model->stock_historical_data_day_wise($data);
                    } else if($data['action'] == "stock_historical_data_minute_wise"){            
                        $this->load->model('cron/Stock_feed_model');
                        $this->Stock_feed_model->stock_historical_data_minute_wise($data);
                    } else if($data['action'] == "holiday_list"){            
                        $this->load->model('cron/Stock_feed_model');
                        $this->Stock_feed_model->holiday_list();
                    }
                      else if($data['action'] == "stock_data_socket"){            
                        $this->load->model('cron/Stock_feed_model');
                        $this->Stock_feed_model->stock_data_socket();
                    }
                     else if($data['action'] == "update_last_close_price"){   
                        $this->load->model('cron/Stock_feed_model');
                        $this->Stock_feed_model->update_last_close_price($data);
                    }
                    else if($data['action'] == "update_collection_stock_rates"){   
                        $curl_handle = curl_init();
                        curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                        curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                        $buffer = curl_exec($curl_handle);
                        curl_close($curl_handle);
                    }
                }
            }
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
        $queue_name = "stock_game_cancel";
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
                        $this->load->model('cron/Cron_model');
                        $this->Cron_model->game_cancellation_by_id($data);
                    } else if($data['action'] == "cancel_collection"){            
                        $this->load->model('cron/Cron_model');
                        $this->Cron_model->collection_cancel_by_id($data);
                    } else if($data['action'] == "game_cancellation"){            
                        $this->load->model('cron/Cron_model');
                        $this->Cron_model->game_cancellation();
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
        $queue_name = "stock_email";
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
                        $this->load->model('cron/Cron_model');
                        $email_template = $this->Cron_model->get_email_template_list();
                        $this->Nodb_model->set_cache_data($email_template_cache,$email_template,REDIS_30_DAYS);
                    }
                    $template_data = array_column($email_template, NULL, 'notification_type');
                    if(array_key_exists($notification_type, $template_data)){
                        if(isset($template_data[$notification_type]['template_path']) && $template_data[$notification_type]['template_path'] != ""){
                            $tmp_path = trim("emailer/".$template_data[$notification_type]['template_path']);

                           
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


    public function report_queue_process() {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $queue_name = 'stock_admin_reports';
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);

        $callback = function($msg) {   
            try{
                $this->load->model('admin/Report_model');
                $data = json_decode($msg->body, TRUE);
                if(!empty($data['report_type'])) {   
                    /* Generate Data according to report type*/
                    $export_data = array();
                    switch($data['report_type']) {
                        case 'contest_report':                        
                            $title =  "Contest Report";
                            $export_data = $this->Report_model->contest_report($data);                        
                        break;
                    }

                    if(!empty($export_data)) {
                        /* Prepare CSV DATA*/
                        $header             = array_keys($export_data[0]);
                        $camelCaseHeader    = array_map("camelCaseString", $header);
                        $export_data        = array_merge(array($camelCaseHeader),$export_data);
                        
                        $file_name = $data['report_type'].'_'.round(microtime(true) * 1000).'.csv';
                        $csv_file_path = "/tmp/".$file_name;

                        //  Create & Write CSV -- W
                        $fp = fopen($csv_file_path, 'w');
                        foreach ($export_data as $fields) {
                            fputcsv($fp, $fields);
                        }
                        fclose($fp);
                        
                        /* Put CSV from tmp to S3 Bucket*/
                        $filePath = BUCKET_REPORTS_PATH.BUCKET_DATA_PREFIX.$file_name;
                        try {
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
                        } catch(Exception $e){
                            //echo 'Caught exception: '.  $e->getMessage(). "\n";
                            return false;
                        }

                        /* Send Report Link to admin*/
                        $link  = IMAGE_PATH.$filePath;
                        $this->load->model('user/Nodb_model');
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

        $queue_name = "stock_prize_distribution";
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

        $queue_name = "stock_prize_notification";
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

        $queue_name = "stock_point_update_cron";
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
     * Used for process contest queue
     * @param 
     * @return boolean
     */
    public function process_contest() {
        $queue_name = "stock_contest";
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
     * Used for process contest_close queue
     * @param 
     * @return boolean
     */
    public function process_contest_close() {

        $queue_name = "stock_contest_close";
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
     * Used for process cron queue
     * @param 
     * @return boolean
     */
    public function process_contestpdf() {

        $queue_name = "stock_contestpdf";
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
     * Used for process contest queue
     * @param 
     * @return boolean
     */
    public function process_score_calculation() {
        $queue_name = "stock_calculate_score";
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
                    $this->load->model('Cron_model');
                    if($data['action'] == "calculate_score" && isset($data['collection_id'])){                        
                        $this->Cron_model->update_scores_in_lineup_by_collection($data['collection_id']);
                    } else if($data['action'] == 'calculate_score_status' && isset($data['collections'])) {
                        $this->Cron_model->update_contest_status($data['collections']);
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
    public function process_remaining_cap() {
        $queue_name = "stock_remaining_cap";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data)){
                    //contest auto recuring
                    $this->load->model('Cron_model');
                    $this->Cron_model->process_remaining_cap($data['collection_id']);
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
     * method to process push notification
     * @param
     */
    public function push_queue_prepare()
    {
        $queue_name = 'stock_push';
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            //notification action maping
            $notification_action_map = array();
            $notification_action_map[441] ="common_method" ;
            $notification_action_map[442] ="common_method" ;
            $notification_action_map[443] ="common_method" ;
            $notification_action_map[560] ="process_holiday_notification" ;
            $notification_action_map[561] ="process_top_gainer_notification" ;
            $notification_action_map[562] ="process_looser_notification" ;
            $notification_action_map[563] ="process_new_stock_notification" ;
            $notification_action_map[564] ="process_update_stock_notification" ;
            $notification_action_map[566] ="process_fixture_publish_notification" ;
            $notification_action_map[567] ="process_contest_added_notification" ;
            $notification_action_map[568] ="process_reminder_notification" ;
            $notification_action_map[623] ="process_candel_publish_notification" ;
            $notification_action_map[624] ="process_contest_winner_notification" ;
            $notification_action_map[554] ="process_contest_winner_notification" ;

            $data = json_decode($msg->body, TRUE);
           
            if (!empty($data))
            {
                $this->load->model('cron/Cron_model','cm');
                $notification = $this->cm->get_notification_row($data['notification_type']);
                $data['subject'] = $notification['en_subject'];
                $data['message'] = $notification['message'];
                $data['topic']   = FCM_TOPIC."stock";
                $func_name = $notification_action_map[$data['notification_type']];
                $data['payload']['notification_type'] = $data['notification_type'];
                $data['payload']['stock_type'] = $data['stock_type'];
                $data['type'] = 'send';
                $message = $this->$func_name($data);
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

    //common method
    public function common_method()
    {
        return true;
    }
    
    //process_update_stock_notification
    public function process_update_stock_notification()
    {
        return true;
    }
    
    //for notification type 560
    public function process_holiday_notification($data)
    {
        $data['message'] = str_replace('{{username}}',"Hey",$data['message']);
        $data['message'] = str_replace('{{ocation}}',$data['ocation'],$data['message']);
        $data['message'] = str_replace('{{newline}}',"\n",$data['message']);
         $this->load->helper('queue_helper');
        add_data_in_queue($data, 'fcm_topic');

    }

    //for notification type 561
    public function process_top_gainer_notification($data)
    {   
        $data['message'] = str_replace('{{username}}',"Hey",$data['message']);
        $data['message'] = str_replace('{{gainer_names}}',$data['gainer'],$data['message']);
        $data['message'] = str_replace('{{newline}}',"\n",$data['message']);
         $this->load->helper('queue_helper');
        add_data_in_queue($data, 'fcm_topic');

    }

    //for notification type 562
    public function process_looser_notification($data)
    {   
        $data['message'] = str_replace('{{username}}',"Hey",$data['message']);
        $data['message'] = str_replace('{{loosers_name}}',$data['loosers'],$data['message']);
        $data['message'] = str_replace('{{newline}}',"\n",$data['message']);
        $this->load->helper('queue_helper');
        add_data_in_queue($data, 'fcm_topic');
    }

     //for notification type 563
     public function process_new_stock_notification($data)
     {  
        $data['message'] = str_replace('{{username}}',"Hey",$data['message']);
        $data['message'] = str_replace('{{stocks_name}}',$data['stock_name'],$data['message']);
        $data['message'] = str_replace('{{newline}}',"\n",$data['message']);
        $this->load->helper('queue_helper');
        add_data_in_queue($data, 'fcm_topic');
     }

    //for notification type 566
    public function process_fixture_publish_notification($data)
    {
        $exclude_collection_name = ["Daily","Weekly","Monthly"];
        $data['message'] = str_replace('{{username}}',"Hey",$data['message']);
        $data['message'] = str_replace('{{newline}}',"\n",$data['message']);
        $data['message'] = str_replace('{{category}}',$data['category'],$data['message']);
        if(isset($data['collection_name']) && !in_array($data['collection_name'],$exclude_collection_name))
        {
            $data['message'] = str_replace('{{collection_name}}',$data['collection_name']." in ",$data['message']);
        }else{
            $data['message'] = str_replace('{{collection_name}}',"",$data['message']);
        }
         $this->load->helper('queue_helper');
        add_data_in_queue($data, 'fcm_topic');
    }

     //for notification type 567
     //{{username}},{{newline}}New contest {{contest_name}} in {{collection_name}} {{category}} is waiting for you.It's time to bring your skills and win amazing prizes
     public function process_contest_added_notification($data)
     {
        $exclude_collection_name = ["Daily","Weekly","Monthly"];
        $data['message'] = str_replace('{{username}}',"Hey",$data['message']);
        $data['message'] = str_replace('{{newline}}',"\n",$data['message']);
        $data['message'] = str_replace('{{contest_name}}','"'.$data['contest_name'].'"',$data['message']);

        if(isset($data['cname']) && !in_array($data['cname'],$exclude_collection_name))
        {
            $data['message'] = str_replace('{{collection_name}}'," in ".$data['cname'],$data['message']);
            $data['message'] = str_replace('{{category}}'," of ".$data['category'],$data['message']);
        }else{
            $data['message'] = str_replace('{{collection_name}}',"",$data['message']);
            $data['message'] = str_replace('{{category}}'," of ".$data['category']." stock fantasy",$data['message']);
        }
        $this->load->helper('queue_helper');
       add_data_in_queue($data, 'fcm_topic'); 
     }

    //for notification type 568
    public function process_reminder_notification($data)
    {
        $exclude_collection_name = ["Daily","Weekly","Monthly"];
        $data['message'] = str_replace('{{username}}',"Hey",$data['message']);
        $data['message'] = str_replace('{{newline}}',"\n",$data['message']);
        $data['message'] = str_replace('{{category}}',$data['category'],$data['message']);
        
        if(isset($data['collection_name']) && !in_array($data['collection_name'],$exclude_collection_name))
        {
            $data['message'] = str_replace('{{collection_name}}',$data['collection_name']." in ",$data['message']);
            $data['subject'] = str_replace('{{collection_name}}',$data['collection_name']." in ",$data['subject']);
            $data['subject'] = str_replace('{{category}}',$data['category'],$data['subject']);
        }else{
            $data['message'] = str_replace('{{collection_name}}',"",$data['message']);
            $data['subject'] = str_replace('{{collection_name}}',"",$data['subject']);
            $data['subject']     = str_replace('{{category}}',$data['category']." stock fantasy",$data['subject']);
        }
        $this->load->helper('queue_helper'); 
       add_data_in_queue($data, 'fcm_topic');

    }


    /**
     * Used for process leaderboard queue
     * @param 
     * @return boolean
     */
    public function process_leaderboard() {

        $queue_name = "stock_leaderboard";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data) && isset($data['action'])){
                    $this->load->model('cron/Leaderboard_model');
                    if($data['action'] == "save_stock_leaderboard"){  
 		                $this->Leaderboard_model->save_stock_leaderboard();
                    } else if($data['action'] == 'update_stock_leaderboard') {
                        $this->Leaderboard_model->update_stock_leaderboard_status();
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
     * Used for process notify node queue
     * @param 
     * @return boolean
     */
    public function process_notify_node() {

        $queue_name = "stock_notify_node";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data) && isset($data['action'])){
                    $this->load->model('cron/Cron_model');
                    if($data['action'] == "collection_info"){  
 		                $this->Cron_model->notify_node_collection_info($data);
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

    function stock_auto_push_queue_process()
    {
    	$queue_name = 'stock_auto_push';
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
                        $notification_title = "";
                        if(isset($data_item['content']))
                        {
                            $content = json_decode($data['push_notification_data'][$key]['content'], TRUE);
                        }
                        if(isset($data_item['notification_type']))
                        {
                            $notification_type = $data_item['notification_type'];
                        }
                        if(in_array($notification_type, [560,561,562,563,566,567,568,623]))
                        {
                            $notification_title = $content['custom_notification_subject'];
                            $message=  $content['custom_notification_text'];
                        }elseif(isset($data_item['notification_data'])){
                            $notification_title = $data_item['notification_data']['en_subject'];
                            $message=  $data_item['notification_data']['message'];
                        }
                        if(empty($content['template_data']))
                        {

                            $content['template_data'] = array();
                            $content['template_data']['notification_type']  = $data_item['notification_type'];
                            $content['template_data']['stock_type']         = $data_item['stock_type'];
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
                echo "[*] Waiting finish for messages.\n";
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
     
    //for notification type 623
    public function process_candel_publish_notification($data)
    {
        $data['message'] = $data['message']."🤑";
        $this->load->helper('queue_helper');
        add_data_in_queue($data,'fcm_topic');
    }
    //need to complete 2655
    public function process_contest_winner_notification($data){
        $data['message'] = $data['message']."🥇";
        unset($data['deviceIDS']);
        $data['stock_type'] = "";
        if (isset($data['content']) && !is_array($data['content'])) {
            $content = json_decode($data['content'], TRUE);
            $data['stock_type'] = $content['stock_type'];
        }
        $data["content"]             = array(
                "custom_notification_subject"   =>$data['en_subject'],
                "custom_notification_text"      =>$data['message'],
                "template_data"=>array()
                
        );

        $new_notification_data[] =  $data;
        $data = ["push_notification_data" => $new_notification_data];
        $this->load->helper('queue_helper');
        add_data_in_queue($data, 'stock_auto_push');
        return true;
    }


     /**
     * Used for process Live stock fantasy crons
     * @param 
     * @return boolean
     */
    public function process_live_stock_fantasy_cron() {

        $queue_name = "live_stock_fantasy_update_cron";
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
