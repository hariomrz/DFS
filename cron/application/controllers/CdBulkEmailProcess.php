<?php

if (!isset($argv[1])) {
    exit('Please set environment variable.');
}

$environment = $argv[1];

if (!in_array($environment, array('development', 'testing', 'production'))) {
    exit('Invalid environment set.');
}

include(__DIR__ . '/../../../all_config/demaon_hosts.php');

unset($argv);
$_SERVER['argv'] = array();
$_SERVER['argc'] = array();

$SERVER_NAME = 'localhost';

switch ($environment) {
    case 'development':
        $SERVER_NAME = CRON_DEVELOPMENT_HOST;
        break;
    case 'testing':
        $SERVER_NAME = CRON_TESTING_HOST;
        break;
    case 'production':
        $SERVER_NAME = CRON_PRODUCTION_HOST;
        break;
    default:
        break;
}
ob_start();
$_SERVER['SERVER_NAME'] = $SERVER_NAME;
$_SERVER['CI_ENV'] = $environment;
include(__DIR__ . '/../../index.php');
ob_end_clean();
$ci = & get_instance();
$ci->load->helper('cd_mail');


require_once FCPATH . '../vendor/autoload.php';
  
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
//check();


$queue_name = CD_BULK_EMAIL_QUEUE;

$connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
$channel = $connection->channel();


$channel->basic_qos(null, 1, null);
//$channel->basic_consume($queue_name, '', false, false, false, false, $callback);


/* Invation from admin*/

$callback_inivte = function($inv_msg)
{   
    
    $emails = json_decode($inv_msg->body, TRUE);  

    try{
        if(!empty($emails))
        {
            $connection2 = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
            $channel2 = $connection2->channel();

            foreach ($emails as $email_data) {



                $push_data = json_encode($email_data);
                $message = new AMQPMessage($push_data, array('delivery_mode' => 1, 'content_type' => 'application/json')); # make message persistent as 2
                $channel2->basic_publish($message,'', CD_EMAIL_QUEUE);

             }

            $channel2->close();
            $connection2->close();
        }

    }
    //catch exception
    catch(Exception $e) 
    {
      return false;
    }

    $inv_msg->delivery_info['channel']->basic_ack($inv_msg->delivery_info['delivery_tag']);
};

$queue_invite = CD_BULK_EMAIL_QUEUE;
$channel->basic_consume($queue_invite, '', false, false, false, false, $callback_inivte);


/**/

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
/* End of file mnotificationque.php */
/* Location: ./application/controllers/mnotificationque.php */