<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once FCPATH . '../vendor/autoload.php';
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
     * method to register, deposti & game join data save by queue
     * @param
     * @return boolean
     */

    public function process_campaign_user(){
        $queue_name = "af_camp_user";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";

        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                        $this->load->model('Cron_model');
                        $this->Cron_model->register_event($data);
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
        $queue_name = "af_email";
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
                    $type = $data['type'];
                            $tmp_path = "emailer/".$type;
                            $message  = $this->load->view($tmp_path, $data, TRUE);
                            if(empty($data['subject']))
                            {
                                $data['subject'] = "Affiliate mail";
                            }
                            $this->Nodb_model->send_email($data['email'], $data['subject'],$message);
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
     * function to get visit count of uses
     * process_visit_user
     */
    public function process_visit_user(){
        $queue_name = "af_visit";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";

        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                if(!empty($data)){                
                        $this->load->model('Cron_model');
                        $this->Cron_model->add_visit($data);
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
     * method to register, deposti & game join data save by queue
     * @param
     * @return boolean
     */

    public function process_deposit_user(){
        $queue_name = "af_deposit_user";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";

        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                        $this->load->model('Cron_model');
                        $this->Cron_model->deposit_event($data);
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
     * method to register, deposti & game join data save by queue
     * @param
     * @return boolean
     */

    public function process_game_user(){
        $queue_name = "af_game_user";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";

        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                        $this->load->model('Cron_model');
                        $this->Cron_model->game_event($data);
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