<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Network_fantasy_worker extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function index() {
        
    }  
    
        
    /**
     * Used for process network fantasy cron queue
     * @param 
     * @return boolean
     */
    public function process_network_cron() {

        $queue_name = "nw_cron";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
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
     * Used for process network fantasy email queue and send email to user
     * @param 
     * @return boolean
     */
    public function process_network_email(){
        $queue_name = "nw_email";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
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
