<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Push_notification
{
	protected $end_point = 'https://fcm.googleapis.com/fcm/send';
	//protected $api_key = 'AAAAINAvAa8:APA91bF04M6leXxu9POPsF54_FBwh_lY-sFlCRPDjMk6b3RxVLSZsOg18OTet-zjCAqrC0YuM1Ky7cvMyREB8F0vcqRULvm9IlpqbXioHTbnJGD1v0hH3x0uT92R6mbtB7a00D74YrqU';
        protected $api_key = SERVER_API_KEY;

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
		
                try {
                    $result = curl_exec($ch);
                    if ($result === FALSE) {
                        $err = curl_error($ch);
                        log_message('error', 'FCM Send Error(push_notification_iphone): '. $err);
                        return $err;
                    }
                    $Data = $data['data'];
                    if($Data['ToUserID'] ==  1) {
                        log_message('error', 'push_notification_iphone Reciever - '.$Data['ToUserID'].' Token - '.$to.' NotificationTypeID - '.$Data['NotificationTypeID']);
                        log_message('error', $result);
                    }
                    curl_close($ch);
                    return $result;	
                } catch (Exception $e) {
                    log_message('error', 'Error push_notification_iphone - '.$e->getMessage());
                    return 0;	
                }		
	}
}

/* End of file push_notification.php */
/* Location: ./application/libraries/push_notification.php */