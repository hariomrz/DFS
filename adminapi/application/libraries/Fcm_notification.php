<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 */
class Fcm_notification {


	/**
	 * [send_fcm_message description]
	 * @method  send_fcm_message
	 * @Summary This function used send IOS and Andorid notification
	 * @param   [type]            [description]
	 * @param   [type]            [description]
	 * @return  [type]
	 */	
	public function send_fcm_message($data,$target)
	{
		if(empty($data) || empty($target))
		{
			return false;
		}

		//FCM api URL
		$fcm_url = 'https://fcm.googleapis.com/fcm/send';
	   
		//api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
		//$server_key = $this->fcm_server_key[$device_type];
		$server_key = FCM_SERVER_KEY;

		$fields = $data;
		//$fields['notification'] = $data;
		//$fields['notification']['body'] = "Dummy push notification msg.";
		//$fields['data'] = $data;
		if(is_array($target)) //for multiple recipients
		{
			$fields['registration_ids'] = $target;
		}
		else // for single recipient
		{
			$fields['to'] = $target;
		}

		$fields['priority'] = 10;

		//header with content_type api key
		$headers = array(
			'Content-Type:application/json',
			'Authorization:key='.$server_key
		);

		// return $fields;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $fcm_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);
		if($result === FALSE)
		{
			return curl_error($ch);
		}
		curl_close($ch);
		return $result;
	}
}
