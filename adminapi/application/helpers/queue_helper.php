<?php if (!defined('BASEPATH')) { exit('No direct script access allowed');}

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
/**
 * Used to get_server_host_name
 * @param string $null
 * @param array $null
 * @return 
 */

function get_server_host_name(){
    $server_name = SERVER_IP.PROJECT_FOLDER_NAME;
    if(ENVIRONMENT == 'production') 
    {
        $server_name = "http://localhost";
    }
    return $server_name;
}

/**
 * Used for push data in queue
 * @param string $queue_name queue name
 * @param array $data api data
 * @return 
 */
function add_data_in_queue($data,$queue_name){
	if(empty($queue_name)){
		return true;
	}

	rabbit_mq_push($data, $queue_name);
}

function rabbit_mq_push($data, $queue_name, $exchange_name = '') {
    $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
    $channel = $connection->channel();
    $push_data = json_encode($data);
    $message = new AMQPMessage($push_data, array('delivery_mode' => 1, 'content_type' => 'application/json')); # make message persistent as 2
    $channel->basic_publish($message, $exchange_name, $queue_name);
    $channel->close();
    $connection->close();
    return true;
}

function put_into_delayed_q($data,$queue_name,$delay=60000,$exchange_name = 'delayed_exchange')
{
    try {
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
        $channel    = $connection->channel();
        $args       = new AMQPTable(['x-delayed-type' => 'fanout']);
            
        $channel->exchange_declare($exchange_name, 'x-delayed-message', false, true, false, false, false, $args);
        $args = new AMQPTable();
        $channel->queue_declare($queue_name, false, true, false, false, false, $args);
        $channel->queue_bind($queue_name, $exchange_name);
    
        $message = new AMQPMessage(json_encode($data), 
                                 [
                                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                                    'content_type'  => 'application/json'
                                ]);
    
        $headers = new AMQPTable(['x-delay' => $delay]);
        $message->set('application_headers', $headers);
        $channel->basic_publish($message, $exchange_name);
        
        $channel->close();
        $connection->close();
        return true;
    } catch (Exception $e) {
    	return false;
    }
}

/**
 * Used for push data in exchange
 * @param array $data
 * @param string $exchange_name
 * @return boolean
 */
function feed_mq_push($data) {
    $connection = new AMQPStreamConnection(FEED_MQ_HOST, FEED_MQ_PORT, FEED_MQ_USER, FEED_MQ_PASSWORD);
    $channel = $connection->channel();
    $exchange = 'prediction_exchange';
    $channel->exchange_declare($exchange, 'fanout', false, true, false);
    $push_data = json_encode($data);
    $msg = new AMQPMessage($push_data,array('delivery_mode' => 1, 'content_type' => 'application/json'));
    $channel->basic_publish($msg, $exchange);
    $channel->close();
    $connection->close();
    return true;
}