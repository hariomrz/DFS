<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Push_notification
{
	protected $end_point = 'https://fcm.googleapis.com/fcm/send';
	protected $api_key = FCM_KEY;
	//protected $api_key = 'AAAASmQbZjc:APA91bH-EGvvl-slunyAHes4wVt0Kt3TTt44yhb3aT85BtkgwjwAx3deow2CTz1oHjmdqQqm5qihBHBwk4rclLSZL5XbKjlqYQKokfVJD-Jz6wKtuo4sp46XiqvdmNefcmvPseoHzqfq';

	public function __construct()
	{
	}

	public function init($data, $to)
	{
		if(!$data || !$to)
		{
			return FALSE;
		}

		//api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
					
		if(is_array($to)) //for multiple recipients
		{
			$data['registration_ids'] = $to;
		}
		else // for single recipient
		{
			$data['to'] = $to;
		}

		$data['priority'] =	'high';

		//header with content_type api key
		$headers = array(
			'Content-Type:application/json',
		  	'Authorization:key='.$this->api_key
		);

		// return $fields;			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->end_point);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		$result = curl_exec($ch);
		if ($result === FALSE) {

			$error_msg = curl_error($ch);
			return 	$error_msg;
		}
		curl_close($ch);
		return $result;	
	}

	function push_notification_android($registatoin_ids = array(), $title = '', $message = '', $badge = 1, $Data) {
		if ($badge == 0) {
		$badge = 1;
		}
		// prep the bundle
		$msg = array(
		'message' => $message,
		'title' => $title,
		'subtitle' => '',
		'tickerText' => '',
		'vibrate' => 1,
		'sound' => 1,
		'largeIcon' => 'large_icon',
		'smallIcon' => 'small_icon',
		'badge' => (int) $badge,
		);

		$msg = array_merge($msg, $Data);
		$fields = array(
		'registration_ids' => $registatoin_ids,
		'data' => $msg,
		);
		$headers = array(
		'Authorization: key='.$this->api_key,
		'Content-Type: application/json',
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->end_point);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields, JSON_NUMERIC_CHECK));
		try {
		$result = curl_exec($ch);
		if (curl_error($ch)) {
		$error_msg = curl_error($ch);
		}
		curl_close($ch);
		return $result;
		} catch (Exception $e) {
		}
	}

	function push_notification_ios($registatoin_id =array(), $title = '', $message = '', $badge = '1', $Data) {

		//Prepare payload for both IOS 
		$fields = array();
		$fields['data'] = $Data;
		$fields['notification']['body'] = $message;
		$fields['notification']['title'] = $title;
		$fields['notification']['sound'] = "default";
		$fields['notification']['badge'] ='1';
		$fields['priority'] =	'high';
		$fields['mutable_content'] =	true;
		$fields['content_available'] =	true;
		$fields['registration_ids'] =	$registatoin_id;
		
		$fields['show_in_foreground'] = true;
        $fields['content-available'] = true;
		$fields['mutable-content'] = true;
	
		// $notification = array('title' =>$title , 'text' => $message, 'sound' => 'default', 'badge' => '1');
		// $arrayToSend = array('to' => $tok	en, 'notification' => $notification,'priority'=>'high');
		$json = json_encode($fields);
		$headers = array();
	
	  
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: key='.$this->api_key;
		$ch = curl_init();
	
		//$url = "https://fcm.googleapis.com/fcm/send";
		curl_setopt($ch, CURLOPT_URL, $this->end_point);
	
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,
	
		"POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
		//Send the request
		try {
			$result = curl_exec($ch);
		   // var_dump($result);
			if (curl_error($ch)) {
			$error_msg = curl_error($ch);
			//log_message("error", "IOS Push Error = ".$error_msg);
			}
			curl_close($ch);
			return $result;
			} catch (Exception $e) {
		   // log_message("error", "IOS Push Exception Message: ".$e->getMessage());
			}
		
	}

}

/* End of file push_notification.php */
/* Location: ./application/libraries/push_notification.php */
