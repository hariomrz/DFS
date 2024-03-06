<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Open_predictor_worker extends CI_Controller
{
    var $prediction_func_map = array();
    function __construct()
    {
        parent::__construct();
        $this->prediction_func_map[0] = array('func' => "get_prediction_for_refund") ; //0 => delete prediction
        $this->prediction_func_map[1] = array('func' => "process_prediction_winning") ;//1 => prediction winning
        $this->prediction_func_map[2] = array('func' => "notify_user_on_new_prediction") ;//2 => notify on new prediction
    }
    
    public function index() {
        
    }  

     /**
     * Used for process_prediction_refund
     * @param 
     * @return boolean
     */
    public function process_prediction_refund() {
        $queue_name = "open_predictor_refund";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                $this->load->model('Open_predictor_model');

                // echo '<pre>';
                // var_dump($data);
                // die('dfd');
                $this->Open_predictor_model->refund_coins($data);
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
     * Used for process_prediction
     * @param 
     * @return boolean
     */
    public function process_prediction() {
        $queue_name = "open_predictor";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                $this->load->model('Open_predictor_model');

                $function_name = $this->prediction_func_map[$data['prediction_action']]['func'];
                $this->Open_predictor_model->$function_name($data);
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
            $this->http_post_request("prediction/get_lobby_fixture",$input_arr,1);

            //lobby filter
            // $input_arr = array();
            // $input_arr['sports_id'] = $sports_id;
            // $input_arr['is_cron_data'] = 1;
            // $this->http_post_request("lobby/get_lobby_filter",$input_arr,1);
            echo "done";
        }
        exit();
    }
}