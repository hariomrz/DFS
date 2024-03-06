<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Worker extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
    }
     
    public function index() 
    {
        
    }

    /**
     * Used for process league queue
     * @param 
     * @return boolean
     */
    public function process_league() 
    {
        $queue_name = "pickem_league";
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
     * Used for process team queue
     * @param 
     * @return boolean
     */
    public function process_team() 
    {
        $queue_name = "pickem_team";
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
     * Used for process season queue
     * @param 
     * @return boolean
     */
    public function process_season() 
    {
        $queue_name = "pickem_season";
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
     * Used for process score queue
     * @param 
     * @return boolean
     */
    public function process_score() 
    {
        $queue_name = "pickem_score";
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
                    if(isset($data['url']) && $data['url'] != ""){
                        $curl_handle = curl_init();
                        curl_setopt($curl_handle,CURLOPT_URL,$data['url']);
                        curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
                        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
                        $buffer = curl_exec($curl_handle);
                        curl_close($curl_handle);
                    }else{
                        $this->load->model('Tournament_model');
                        $this->Tournament_model->update_tournament_score($data);
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
     * Used for process contest prize distribution cron
     * @param 
     * @return boolean
     */
    public function process_prize_distribution() 
    {
        $queue_name = "pickem_prize_distribution";
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
     * Used for process contest prize distribution cron
     * @param 
     * @return boolean
     */
    public function process_prize_notification() 
    {
        $queue_name = "pickem_prize_notification";
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

        $queue_name = "pickem_tds";
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
    * Used for process tournament prize distribution
    * @param 
    * @return boolean
    */
    public function process_tournament() {

        $queue_name = "pickem_tournament";
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
     * Used for Cancel tournament and fee refund
     * @param 
     * @return boolean
     */
    public function process_cancel_tournament() 
    {
        $queue_name = "pickem_cancel_tournament";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if (!empty($data)){
                  $this->load->model('Tournament_model');
                  $this->Tournament_model->cancel_tournament($data);
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
        $queue_name = "pickem_email";
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
                        $this->load->model('cron/Tournament_model');
                        $email_template = $this->Tournament_model->get_email_template_list();
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

    /**
    * Used for process season score update on user team
    * @param 
    * @return boolean
    */
    public function pickem_cron() {

        $queue_name = "pickem_cron";
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