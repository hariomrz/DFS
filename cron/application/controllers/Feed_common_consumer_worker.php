<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Feed_common_consumer_worker extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function index() {
        
    }  
    

    public function process_feed_common_consumer() {
        $exchange = "feed_common_exchange";
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
                //echo '<pre>';print_r($data);die;
                if(isset($data['response']) && !empty($data['response']))
                {
                    $sports_id = @$data['response']['sports_id'];
                    $type = @$data['response']['type'];
                    switch ($sports_id) 
                    {
                        case CRICKET_SPORTS_ID:
                            $this->load->model('cricket/vinfotech_model');
                            if($type == 'score')
                            {
                                $this->vinfotech_model->get_scores(CRICKET_SPORTS_ID,json_encode($data));
                            }elseif($type == 'lineupout')
                            {
                                $season_game_uid = @$data['response']['data']['season_game_uid'];
                                $this->vinfotech_model->get_season_details($season_game_uid,CRICKET_SPORTS_ID,json_encode($data));
                            }    
                            break;
                        case SOCCER_SPORTS_ID:
                            //Soccer code here
                            break;
                        default:
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
        
        //$channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);
        // for regular connection mentaion
        while ($channel->is_consuming()) {
            try {
                $channel->wait(null, false, 10);
            } catch (AMQPTimeoutException $e) {
                $connection->checkHeartBeat();
                continue;
            } catch (Exception $e) {
                $logger->error((string) $e);
                exit;
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
