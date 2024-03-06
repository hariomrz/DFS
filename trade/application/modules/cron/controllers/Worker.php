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
        $queue_name = "trade_league";
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
        $queue_name = "trade_team";
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
        $queue_name = "trade_season";
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
    public function process_player() 
    {
        $queue_name = "trade_player";
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
        $queue_name = "trade_score";
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
    public function process_cron() 
    {
        $queue_name = "trade_cron";
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
     * Used for process prize queue
     * @param 
     * @return boolean
     */
    public function process_prize() 
    {
        $queue_name = "trade_prize";
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
     * Used for matchup used
     * @param 
     * @return boolean
     */
    public function process_matchup() 
    {
        // $data = '{"action_type":"matchup","question_id":"6967","user_id":"1076","type":"2","entry_fee":"4.0"}';
        // $data =  json_decode($data,true);
        // $this->load->model('Cron_model');
        // $this->Cron_model->anwser_matchup($data); 

        $queue_name = "trade_matchup";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                log_message("error",format_date()." trade_matchup data = ".json_encode($data));
                if (!empty($data)) 
                {   
                    switch($data['action_type'])
                    {
                        case "matchup":
                            $this->load->model('Cron_model');
                            $this->Cron_model->anwser_matchup($data); 
                        break;
                        case "cancel":
                            $this->load->model('Cron_model');
                            $this->Cron_model->anwser_cancel($data); 
                        break;
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
     * Used for node broadcast queue
     * @param 
     * @return boolean
     */
    public function process_node_emitter() 
    {
        //$data = array('action_type'=>'trade_update','question_id'=>'6876','user_id'=>'2353,1076');
        
        $queue_name = "trade_node_emitter";
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C '.ENVIRONMENT, "\n";
        $callback = function($msg)
        {
            try{
                $data = json_decode($msg->body, TRUE);
                log_message("error"," process_node_emitter data = ".json_encode($data).format_date());
                if (!empty($data)) 
                {
                    $this->load->model('lobby/Lobby_model');	
                    $this->load->library('Node');
                    $question_id = $data['question_id'];
                    switch($data['action_type'])
                    {
                        case 'trade_update':     
                            $res = array();
                            $res['order_book'] = $this->Lobby_model->get_order_book($question_id);
                            
                            $user_res = array();
                            $user_res['user_ids'] = explode(',',$data['user_id']);
                            $user_res['question_id'] = $question_id;
                            $user_trade = $this->Lobby_model->question_trade_count_for_node($user_res);
                            // total
                            $total_arr = array_column($user_trade, "user_total_trade");
                            $total_trade = array_sum($total_arr);
                            // unmatched
                            $unmatched_total_arr = array_column($user_trade, "user_unmatched_trade");
                            $unmatched_total = array_sum($unmatched_total_arr);
                            // matched
                            $matched_total_arr = array_column($user_trade, "user_matched_trade");
                            $matched_total = array_sum($matched_total_arr);
            
                            $res['trade_data']['total_unmatched'] = $unmatched_total;
                            $res['trade_data']['total_matched'] = $matched_total;
                            $res['trade_data']['total_trade'] = $total_trade;
            
                            $req_data = array();
                            $req_data[$question_id] = $res;
                           
                            $node = new node(array("route" => 'QestnTradeOT', "postData" => array("data" =>$req_data))); 
                            
                            // question trade update
                            $node = new node(array("route" => 'UserQestnTradeOT', "postData" => array("data" =>$user_trade))); 
            
                        break;
                        case "matchup":
                            
                            $req_matchup = array();
                            $req_matchup['user_id'] = $data['user_id'];
                            $req_matchup['question_id'] = $data['question_id'];
                            $req_matchup['matchup_count'] = $data['matchup_count'];
                            $node = new node(array("route" => 'UserMatchupTradeOT', "postData" => array("data" =>$req_matchup))); 
                        break;
                        case "cancel":
                            
                            // $req_matchup = array();
                            // $req_matchup['user_id'] = $data['user_id'];
                            // $req_matchup['question_id'] = $data['question_id'];
                            // $req_matchup['matchup_count'] = $data['matchup_count'];
                            // $node = new node(array("route" => 'UserMatchupTradeOT', "postData" => array("data" =>$req_matchup))); 
                        break;
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


    public function process_auto_question_consumer() 
    {
        $exchange = "feed_prediction_exchange";
        $consumer_id = isset($argv[1]) ? $argv[1] : 'c1';
        $connection = new AMQPStreamConnection(FEED_MQ_HOST, FEED_MQ_PORT, FEED_MQ_USER, FEED_MQ_PASSWORD);
        $channel = $connection->channel();
        $channel->exchange_declare($exchange, 'fanout', false, true, false);
        list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);
        $channel->queue_bind($queue_name, $exchange);
        echo " [*] Consumer {$consumer_id} is waiting for messages. To exit press CTRL+C\n";
        $callback = function ($msg) use ($consumer_id) 
        {
            try{
                $data = json_decode($msg->body, TRUE);
                //echo "<pre>";print_r($data['question']);die;
                if(!empty($data['question']))
                {
                    //echo "<pre>";print_r($data);
                    $this->load->model('cron/Cron_model');
                    $this->Cron_model->add_auto_question_exchange($data['question']);
                    //echo "<pre>";print_r($data);die;
                } 

                if(!empty($data['answer']))
                {
                    //echo "<pre>";print_r($data);
                    $this->load->model('cron/Cron_model');
                    $this->Cron_model->update_answer_exchange($data['answer']);
                    //echo "<pre>";print_r($data);die;
                }

            }   
            //catch exception
            catch(Exception $e) 
            {
              return false;
            }
        };
        
        //$channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            try {
                $channel->wait(null, false, 10);
            } catch (AMQPTimeoutException $e) {
                $connection->checkHeartBeat();
                continue;
            } catch (Exception $e) {
                //$logger->error((string) $e);
                //exit;
                return true;
            }
        }

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        return true;
    }


}