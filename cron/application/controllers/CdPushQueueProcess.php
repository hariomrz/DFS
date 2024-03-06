<?php

// error_reporting(E_ALL);

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
$ci =& get_instance();
$ci->load->library('Push_notification');

require_once FCPATH . '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$q = 'cd_push';

$connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);

$channel = $connection->channel();

// $channel->queue_declare('background_job', false, true, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";


$callback = function($msg){


	//notification action maping
    $notification_action_map = array();
    $notification_action_map[120] ="deposit_promotion_notify" ;
    $notification_action_map[121] ="contest_promotion_notify" ;
    $notification_action_map[123] ="promotion_notify" ;
    $notification_action_map[124] ="fixture_promotion_notify" ;//fixture promotion
    $notification_action_map[127] ="send_common_email" ;
    $notification_action_map[128] ="send_common_email" ;
    $notification_action_map[129] ="send_common_email" ;
    $notification_action_map[131] ="send_match_delay_email" ;
    $notification_action_map[132] ="send_lineup_announced_email" ;

	  $data = json_decode($msg->body, TRUE);
  	try{
      if(!empty($data))
    	{
    		
      		$device_ids = array_column($data, 'device_id');
      	  $ci = & get_instance();
  		    $notification_data = $data[0];
  		    $notification_type=0;
  		    $message = "";
      		if(isset($notification_data['content']))
      		{
      			$content = @json_decode($notification_data['content'], TRUE);
      		}


      		if(isset($notification_data['notification_type']))
      		{
      			$notification_type = $notification_data['notification_type'];
      		}

  		    $ci->load->model('Communication_dashboard_model');
          //get notification descrition
          if(!in_array($notification_type,array(134,135)))//for custom sms and notification
          {
              $noti_data = $ci->Communication_dashboard_model->get_notification_description( $notification_type);

              $message = $noti_data['message'];
              $message=  $notification_action_map[(int)$notification_type]($message ,$content);
              $title = $message;
          }
          else{
              $title = $content['custom_notification_subject'];
              $message= $content['custom_notification_text'];
          }

          if(empty($content['template_data']))
          {
            $content['template_data'] = array();
          }

          $content['template_data']['notification_type'] = $notification_type;

    	     $fields = array();
  		    unset($data['deviceIDS']);

          if($notification_type ==121)//for contest 
          {
             $contest_data = array();
             $contest_data['contest_unique_id'] = $content['template_data']['contest_unique_id'];
             $contest_data['sports_id'] = $content['template_data']['sports_id'];
             $contest_data['notification_type'] = $content['template_data']['notification_type'];
             $content['template_data'] = array();
             $content['template_data'] =  $contest_data;
          }
          //$registatoin_ids = array(), $title = '', $message = '', $badge = 1, $Data
          $result = $ci->push_notification->push_notification_android($device_ids,$title,$message,1,$content['template_data'] );
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
$channel->basic_consume($q, '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();


function deposit_promotion_notify($message,$data)
{
    $promocode_data = $data['template_data'];
    $message = str_replace("{{promo_code}}", $promocode_data['promo_code'], $message);
    $message = str_replace("{{offer_percentage}}", $promocode_data['discount'] , $message);
   	return $message;
}

function contest_promotion_notify($message,$data)
{
    $contest_data = $data['template_data'];
    $message = str_replace("{{contest_name}}", $contest_data['contest_name'], $message);
    $message = str_replace("{{collection_name}}", $contest_data['collection_name'], $message);
   	return $message;
}


function promotion_notify($message,$data)
{
    $template_data = $data['template_data'];
    $message = str_replace("{{CURRENCY_CODE_HTML}}", CURRENCY_CODE_HTML, $message);
    $message = str_replace("{{amount}}", $template_data['bonus_amount'], $message);
     $message = str_replace("{{SITE_TITLE}}", SITE_TITLE, $message);
	return $message;           
}

function fixture_promotion_notify($message,$data)
{
    $match_data = $data['template_data'];
    $message = str_replace("{{home}}", $match_data['home'], $message);
    $message = str_replace("{{away}}", $match_data['away'], $message);
    $message = str_replace("{{home_flag}}", $match_data['home_flag'], $message);
    $message = str_replace("{{away_flag}}", $match_data['away_flag'], $message);
    $message = str_replace("{{season_scheduled_date}}",$match_data['season_scheduled_date'] , $message);
	return $message;  
}

function send_match_delay_email($message,$data)
{
    $match_data = $data['template_data'];
     $message = str_replace("{{home}}", $match_data['home'], $message);
    $message = str_replace("{{away}}", $match_data['away'], $message);
    $message = str_replace("{{collection_name}}", $match_data['collection_name'], $message);
    $message = str_replace("{{season_scheduled_date}}",$match_data['season_scheduled_date'] , $message);
    $message = str_replace("{{MINUTES}}", $match_data['delay_minute'], $message);
    return $message;  
}

function send_lineup_announced_email($message,$data)
{ 
    $match_data = $data['template_data'];
    $message = str_replace("{{home}}", $match_data['home'], $message);
    $message = str_replace("{{away}}", $match_data['away'], $message);
    $message = str_replace("{{season_scheduled_date}}",$match_data['season_scheduled_date'] , $message);
    //$message = str_replace("{{user_name}}", $data['content']['user_name'], $message);
    $message = str_replace("{{collection_name}}", $match_data['collection_name'], $message);
    return $message;  
}

/* End of file mnotificationque.php */
/* Location: ./application/controllers/mnotificationque.php */