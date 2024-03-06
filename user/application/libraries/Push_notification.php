<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Push_notification
{
	protected $end_point = 'https://fcm.googleapis.com/fcm/send';
	protected $api_key = 'AAAAZs9Y-LQ:APA91bGAFPIzN063CkMgHnLUC8Hm9BXxNNYOlfPNc16hz5JR7OaiPwQ22p1Su8LMeFVLqLM3mhOAjE6oHiKuGlQ94VK5gl5HI7flJoCggLTZ15K7VuXOKBZ6MTIuUgxH8cDTipY1-C3E';

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
			return 	curl_error($ch);
		}
		curl_close($ch);
		return $result;	
	}
}

/* End of file push_notification.php */
/* Location: ./application/libraries/push_notification.php */
