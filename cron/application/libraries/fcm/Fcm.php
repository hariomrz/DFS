<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once __DIR__.'/vendor/autoload.php';
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
class Fcm
{
	public $messaging;
	public function __construct()
	{
		$factory = (new Factory)->withServiceAccount(__DIR__.'/ServiceAccountKey.json');
		$this->messaging = $factory->createMessaging();
	}

	public function subscribe($data){
		if(empty($data) || empty($data['topic']) || empty($data['device_ids'])){
			return false;
		}

		$topic = $data['topic'];
		$device_ids = $data['device_ids'];
		$result = $this->messaging->subscribeToTopic($topic, $device_ids);
		//echo "<pre>";print_r($result);die;
		if($result){
			return true;
		}else{
			return false;
		}
	}

	public function unsubscribe($data){
		if(empty($data) || empty($data['topic']) || empty($data['device_ids'])){
			return false;
		}

		$topic = $data['topic'];
		$device_ids = $data['device_ids'];
		$result = $this->messaging->unsubscribeFromTopic($topic, $device_ids);
		//echo "<pre>";print_r($result);die;
		if($result){
			return true;
		}else{
			return false;
		}
	}

	public function send($data){
		if(empty($data) || empty($data['topic']) || empty($data['data'])) {
			return false;
		}
		
		$badge = 1;
		$topic = $data['topic'];
		$msg_arr = $data['data'];
		$msg = array(
					'title' => $msg_arr['subject'],
					'message' => $msg_arr['message'],
					'body' => $msg_arr['message'],
					'subtitle' => '',
					'tickerText' => '',
					'vibrate' => 1,
					'sound' => 1,
					'largeIcon' => 'large_icon',
					'smallIcon' => 'small_icon',
					'badge' => (int) $badge,
					'priority' => 'high',
					'show_in_foreground' => true,
					'content_available' => true,
					'content-available' => true,
					'mutable-content' => true
				);

		if(!empty($msg_arr['payload'])){
			$msg = array_merge($msg,$msg_arr['payload']);
		}

		$notification = $data = $msg;
		$message = CloudMessage::withTarget('topic', $topic)
			    ->withNotification($notification) // optional
			    ->withData($data); // optional
		$result = $this->messaging->send($message);
		if($result){
			return true;
		}else{
			return false;
		}
	}
}
/* End of file Fcm.php */
/* Location: ./application/libraries/fcm/Fcm.php */
