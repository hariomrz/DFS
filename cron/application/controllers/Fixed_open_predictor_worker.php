<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Fixed_open_predictor_worker extends CI_Controller
{
    var $prediction_func_map = array();
    function __construct()
    {
        parent::__construct();
        $this->prediction_func_map[1] = array('func' => "update_day_rank","func1" => 'update_day_prize_status') ; //0 => update day rank
        $this->prediction_func_map[2] = array('func' => "update_week_rank","func1" => 'update_week_prize_status') ;//1 => update week rank
        $this->prediction_func_map[3] = array('func' => "update_month_rank","func1" => 'update_month_prize_status') ;//2 => update_month_rank
    }
    
    public function index() {
        
    }      

    /**
     * Used for process_prediction
     * @param 
     * @return boolean
     */
    public function process_prediction() {
        $queue_name = "fixed_open_predictor";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            $data = json_decode($msg->body, TRUE);
            try{
                $this->load->model('Fixed_open_predictor_model');

                $function_name = $this->prediction_func_map[$data['prediction_action']]['func'];
                $this->Fixed_open_predictor_model->$function_name($data['deadline_date']);
                
                $function_name = $this->prediction_func_map[$data['prediction_action']]['func1'];
                $this->Fixed_open_predictor_model->$function_name($data);
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
            $this->http_post_request("fixed_open_prediction/get_lobby_fixture",$input_arr,1);

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