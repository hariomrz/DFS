<?php
error_reporting(E_ALL);
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('192.168.0.66', 5672, 'guest', 'guest');
// $connection = new AMQPStreamConnection('159.203.161.102', 5672, 'test', 'test');
$channel = $connection->channel();

// $channel->queue_declare('background_job', false, true, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";


$callback = function($msg){
	$data = json_decode($msg->body,TRUE);
	var_dump($data);
	echo "\n";
	echo "\n";
	echo "\n";
  	if(!empty($data['method']))
  	{
  		ob_start();
		include(__DIR__ .'/../../../../index.php');
		ob_end_clean();

		$ci =& get_instance();
		/**
		switch ($data['method']) 
  		{
	  		case 'calculate_activity_rank':
	  			if(!empty($data['data']['UserGUID']))
	  			{
	  				$ci->load->model('cron/cron_model');
	  				$ci->cron_model->calculate_rank($data['data']['UserGUID'],$data['data']['ENVIRONMENT']);
	  			}
	  			break;
	  		case 'check_activity_visibility':
	  			if(!empty($data['data']['ActivityGUID']) && !empty($data['data']['ENVIRONMENT']))
	  			{
	  				$ci->load->model('cron/cron_model');
	  				$ci->cron_model->check_activity_visibility($data['data']['ActivityGUID'],$data['data']['ENVIRONMENT']);
	  			}
	  			break;
	  		case 'group_member_add_notification':
	  			if(!empty($data['data']['members']) && !empty($data['data']['activity_id']))
	  			{
	  				$ci->load->model('group/group_model');
	  				$ci->group_model->send_instant_group_create_notification($data['data']['members'],$data['data']['group_id'],$data['data']['type'],$data['data']['activity_id']);
	  			}
	  			break;
	  		case 'get_groups_of_all_user':
	  				//$ci->load->model('group/group_model');
	  				//$ci->group_model->get_all_groups_associate_user();
	  				echo ' [*] get_groups_of_all_user', "\n";
	  				execute_curl("cron/insert_all_group_member", $data);
	  			break;	
	  		default:
	  			# code...
	  			break;
	  	}
	  	**/
  	}
  	//echo " [x] Received ", $msg->body, "\n";
  	//sleep(substr_count($msg->body, '.'));
  	//echo " [x] Done", "\n";
  	$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('push', '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();


function execute_curl($url, $data = '') 
{
    $env = isset($data['data']['ENVIRONMENT']) ? $data['data']['ENVIRONMENT'] : '';
 	$host = "http://localhost/600-giftedparents/";    
    switch ($env) {
		case 'development':
		case 'testing':
			$host = "http://dev.vcommonsocial.com/";
		break;	
		case 'production':
			$host = "https://www.vcommonsocial.com/";
		break;
		case 'demo':
			$host = "https://demo.vcommonsocial.com/";
		break;
		default:
			$host = "http://localhost/600-giftedparents/";
	}

	$url = $host.$url;
	echo ' [*] URL'.$url, "\n";
    $jsondata = json_encode($data);
    $ch = curl_init();
    $headers = array('Accept: application/json', 'Content-Type: application/json');
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($jsondata != '') 
    {
        curl_setopt($ch, CURLOPT_POST, count($jsondata));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
    }

    if($env == 'production' || $env == 'demo')
    {
        $username = 'vcommonsocial';
        $password = 'EasyPassword';                 
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);      
    }
    
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    $result = curl_exec($ch);
    
    curl_close($ch);       
    return $result;
}
?>
