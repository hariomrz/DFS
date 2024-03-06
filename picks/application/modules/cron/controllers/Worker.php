<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
class Worker extends MY_Controller
{   
    public $server_name;
    function __construct()
    {
        parent::__construct();
        $this->load->helper('queue');
        $this->server_name = get_server_host_name();
    }
     
    public function index() {
        
    }

    /**
     * Used for process cron queue
     * @param 
     * @return boolean
     */
    public function process_cron() {

        $queue_name = "picks_cron";
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
        $queue_name = "picks_contest";
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
     * Used for process season cancellation queue
     * @param 
     * @return boolean
     */
    public function process_game_cancellation() {
        $queue_name = "picks_game_cancel";
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
                    } else if($data['action'] == "cancel_season"){            
                        $this->load->model('cron/Cron_model');                      
                        $this->Cron_model->season_cancel_by_id($data);
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
    * Used for process point_update_cron queue
    * @param 
    * @return boolean
    */
    public function process_score_update_cron() {

        $queue_name = "picks_score_update_cron";
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

        $queue_name = "picks_contest_close";
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
            $content = array();
            $content['url'] = $this->server_name."/picks/cron/prize_distribution";
            add_data_in_queue($content, 'picks_prize_distribution');

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

        $queue_name = "picks_prize_distribution";
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

        $queue_name = "picks_prize_notification";
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

        $queue_name = "picks_tds";
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

        $queue_name = "picks_gst";
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
    
    public  function process_email() {
        $queue_name = "picks_email";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data)){
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


}