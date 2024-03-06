<?php if ( ! defined('BASEPATH')) { exit('No direct script access allowed'); }

class Push_notification
{
	protected $end_point = 'https://fcm.googleapis.com/fcm/send';
	protected $iid_end_point = 'https://iid.googleapis.com/iid/v1'; //instance id service
	protected $api_key = SERVER_API_KEY;

	public function __construct()
	{
	}

	public function init($data, $to)
	{
		if (!$data || !$to) {
			return FALSE;
		}

		
		//api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key

		if (is_array($to)) { //for multiple recipients
			$data['registration_ids'] = $to;
		} else  { // for single recipient
			$data['to'] = $to;
		}

		$data['priority'] =	'high';
		$data['show_in_foreground'] =	true;
		$data['content_available'] =	true;

		log_message('error', 'PayLoad: ' . json_encode($data, JSON_NUMERIC_CHECK));
		
		//header with content_type api key
		$headers = array(
			'Content-Type:application/json',
			'Authorization:key=' . $this->api_key
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->end_point);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

		try {
			$result = curl_exec($ch);
			if ($result === FALSE) {
				$result = curl_error($ch);
				log_message('error', 'FCM Send Error(push_notification_iphone): ' . $result);				
			} else {
				$Data = $data['notification'];
				if (in_array($Data['ToUserID'], array(1, 2342, 10421, 10404, 10535))) {
					log_message('error', 'push_notification_iphone Reciever - ' . $Data['ToUserID'] . ' Token - ' . $to);
					log_message('error', $result);
				}
				curl_close($ch);
			}
			return $result;
		} catch (Exception $e) {
			log_message('error', 'Error push_notification_iphone - ' . $e->getMessage());
			return 0;
		}
	}

	public function send($data, $to) {		
		if (!$data || !$to) {
			return FALSE;
		}

		$notification = $data['notification'];
		$to_user_id = $notification['ToUserID'];
		$notification_type_id = isset($notification['NotificationTypeID']) ? $notification['NotificationTypeID'] : '';
		unset($notification['ToUserID']);
		$data['notification'] = $notification;
		
		if(isset($data['device_type_id']) && $data['device_type_id'] == "3"){
			unset($data['notification']);
		}
		
		//api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
		if (is_array($to)) { //for multiple recipients
			$data['registration_ids'] = $to;
		} else  { // for single recipient
			$data['to'] = $to;
		}
		
		try {
			$url = $this->end_point;
			$result = $this->sendToFCM($data, $url);
			if (in_array($to_user_id, array(1, 1554, 2342, 10662, 11003, 12255, 13557, 14145, 14151, 14155))) { //, 1, 1554, 10662, 11003, 12255, 13557, 14145, 14151, 14155
				log_message('error', 'Notification Reciever - ' . $to_user_id. ' Token - ' . $to); //. ' Token - ' . $to        
				log_message('error', 'Notification PayLoad: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
				log_message('error', 'FCM Push Notification: '.$result);				
			}
			
			/*$result = @json_decode($result);
			if(isset($result->failure) && $result->failure === 1) {
				log_message('error', 'FCM Token Invalid: '.$to);	
				 $obj = &get_instance();
				$obj->db->where('DeviceToken', $to);
				$obj->db->update(ACTIVELOGINS, array('IsValidToken' => 2));
				
			}*/
			
		} catch (Exception $e) {
			log_message('error', 'Send '.$e->getMessage());
		}		
	}

	public function topic($data) {
		if (!$data) {
			return FALSE;
		}

		log_message('error', 'Notification PayLoad: ' . json_encode($data, JSON_NUMERIC_CHECK));		
		try {
			$url = $this->end_point;
			$result = $this->sendToFCM($data, $url);
			log_message('error', 'FCM Topic Notification: '.$result);
		} catch (Exception $e) {
			log_message('error', 'Topic '.$e->getMessage());
		}
	}

	
	/**
     * @var array
     */
    private $devices = [];

    /**
     * @param string|array $deviceId
     *
     * @return self
     */
    public function addDevice($deviceId) {
        if (empty($deviceId)) {
            log_message('error','Device id is empty');
        }

        if (is_string($deviceId)) {
            $this->devices[] = $deviceId;
        }

        if (is_array($deviceId)) {
            $this->devices = array_merge($this->devices, $deviceId);
        }

        return $this;
	}

	public function removeDevice() {
		$this->devices = [];
	}
	
	public function subscribeToTopic($topic) {		
		$request_data = array();
		$request_data['to'] = '/topics/'.$topic;
		$request_data['registration_tokens'] = $this->devices;
		log_message('error', 'Subscribe To Topic: ' . json_encode($request_data, JSON_NUMERIC_CHECK));
		
		try {
			$url = $this->iid_end_point.':batchAdd';
			$result = $this->sendToFCM($request_data, $url);
			log_message('error', 'FCM Subscribe Topic: '.$result);
		} catch (Exception $e) {
			log_message('error', 'Subscribe '.$e->getMessage());
		}
	}

	public function unsubscribeToTopic($topic) {
		$request_data = array();
		$request_data['to'] = '/topics/'.$topic;
		$request_data['registration_tokens'] = $this->devices;
		try {
			$url = $this->iid_end_point.':batchRemove';
			$result = $this->sendToFCM($request_data, $url);
			log_message('error', 'FCM Unsubscribe Topic: '.$result);
		} catch (Exception $e) {
			log_message('error', 'Unsubscribe '.$e->getMessage());
		}
	}

	/**
     * @param $request
     *
     * @return array
     */
	protected function sendToFCM($request, $url) {
		$headers = array(
			'Content-Type:application/json',
			'Authorization:key=' . $this->api_key
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request, JSON_NUMERIC_CHECK));
		try {
			$result = curl_exec($ch);
			if ($result === FALSE) {
				$result = curl_error($ch);
				throw new Exception('FCM Error: '.$result);				
			} else {
				curl_close($ch);
			}
			return $result;
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
}

/* End of file push_notification.php */
/* Location: ./application/libraries/push_notification.php */
