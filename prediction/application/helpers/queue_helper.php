<?php if (!defined('BASEPATH')) { exit('No direct script access allowed');}

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

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

/**
 * Used for push data in queue
 * @param array $data
 * @param string $queue_name
 * @param string $exchange_name
 * @return boolean
 */
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