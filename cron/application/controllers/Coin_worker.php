<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Coin_worker extends CI_Controller
{
    var $coins_func_map = array();
    function __construct()
    {
        parent::__construct();
        $this->coins_func_map[0] = array('func' => "claim_coins") ; //0 => claim coins
        
    }
    
    public function index() {
        
    }  
 
    /**
     * Used for process_prediction
     * @param 
     * @return boolean
     */
    public function claim_coins() {
        $queue_name = "coins";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                $this->load->model('Coins_model');
                $function_name = $this->coins_func_map[0]['func'];
                $this->Coins_model->$function_name($data);
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