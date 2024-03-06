<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once getcwd() . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/*
  |--------------------------------------------------------------------------
  | Use To send chat push messages
  |--------------------------------------------------------------------------
 */

function SendPushMsg($entityID, $message, $Data = array())
{
    //log_message('error', 'Sender-' . $Data['UserID'] . " Reciever-" . $Data['ToUserID'] . " NotificationTypeID-" . $Data['NotificationTypeID']);
    if ($Data['NotificationTypeID'] !== 82 && trim($Data['UserID']) == trim($Data['ToUserID'])) {
        return;
    }

    $obj = &get_instance();
    $Query = $obj->db->query("SELECT DeviceToken, DeviceTypeID FROM `ActiveLogins` WHERE UserID='$entityID' AND DeviceToken!='' AND IsValidToken=1 GROUP BY DeviceToken, DeviceTypeID ORDER BY ActiveLoginID DESC ");
    if ($Query->num_rows() > 0) {
        $notification_data = $Data;
        $obj->load->model(array('notification_model')); //, 'users/friend_model'
        if ($notification_data['NotificationTypeID'] == 154) {
            $Data['TotalUnread'] = $obj->notification_model->get_unread_count($entityID);
        }

        /* if($notification_data['NotificationTypeID'] == 153) {
            $is_notification_send = $obj->notification_model->post_notification_log($notification_data);            
            if(!$is_notification_send) {
                //log_message('error', 'Already Sent');
                return FALSE;
            }
            //log_message('error', 'Sent');
        } */

        //$Data['IncomingRequestCount'] = $obj->friend_model->incoming_request_count($entityID, $entityID);

        $params = array();
        if (isset($notification_data['NotificationID']) && !empty($notification_data['NotificationID'])) {
            $params = $obj->notification_model->get_params($notification_data['NotificationID']);
        }

        $entity_type = '';
        $PageID = 0;
        if ($params) {
            foreach ($params as $param) {
                $key = 'P' . $param['NotificationParamName'];
                $param_details = json_decode($param['NotificationParamValue'], true);

                if (in_array($notification_data['NotificationTypeKey'], array('make_group_admin', 'make_event_admin', 'make_page_admin'))) {
                    $entity_type = $param_details['Type'];
                    $PageID = $param_details['ReferenceID'];
                }
                // Start
                if ($key == 'P2' && in_array($notification_data['NotificationTypeID'], array(46, 48, 61, 52, 53))) {
                    $PageID = $param_details['ReferenceID'];
                }

                if ($notification_data['NotificationTypeKey'] == 'review_marked_helpful' || $notification_data['NotificationTypeID'] == 63 || $notification_data['NotificationTypeID'] == 64) {
                    $PageID = $obj->db->select('ModuleEntityID')->from(RATINGS)->where('RatingID', $notification_data['RefrenceID'])->get()->row()->ModuleEntityID;
                }
                // Ends
            }
        }
        $Data['PushNotification'] = $obj->notification_model->get_notification_link_phone($notification_data['NotificationTypeID'], $notification_data['RefrenceID'], $notification_data['UserID'], $notification_data['ToUserID'], $PageID, $notification_data['NotificationTypeKey'], $entity_type, $notification_data['Params']);
        

        //log_message('error', 'Sender-' . $Data['UserID'] . " Reciever-" . $Data['ToUserID'] . " NotificationTypeID-" . $Data['NotificationTypeID']);
        foreach ($Query->result_array() as $Notifications) {
            $Data['DeviceTypeID'] = $Notifications['DeviceTypeID'];
            send_push_notification($Notifications['DeviceToken'], $message, 1, $Data);

           /* if ($Notifications['DeviceTypeID'] == 2) {
                push_notification_iphone($Notifications['DeviceToken'], $message, 1, $Data);
            } elseif ($Notifications['DeviceTypeID'] == 3) {
                push_notification_android(array($Notifications['DeviceToken']), $message, 0, $Data);
            }
            */
            
        }
    }
}


function send_push_notification($device_token = '', $message = '', $badge = 1, $data = array()) {
    $ci = &get_instance();
    $ci->load->library('Push_notification');

    $body = '';
    if (isset($data['Summary']) && !empty($data['Summary']))  {
        $data['Summary'] = html_entity_decode($data['Summary']);
        $data['Summary'] = strip_tags($data['Summary']);
        $data['Summary'] = mb_substr($data['Summary'], 0, 160,'UTF-8');

        $body = $data['Summary'];
    }

   
    $device_type_id = $data['DeviceTypeID'];
    unset($data['DeviceTypeID']);
    unset($data['FromUserDetails']);
    unset($data['ToUserDetails']);
    unset($data['UserID']);
    unset($data['RefrenceID']);

    //Prepare payload for both IOS and Android.  
    $fields = array();
    $fields['notification']             = $data;

    $fields['notification']['body']     = $body;
    $fields['notification']['title']    = $message;
    $fields['notification']['sound']    = "default";
    $fields['notification']['badge']    = $badge;
    if($device_type_id == 2) {
        $fields['mutable_content']    = true; 
        $fields['category']    = 'myNotificationCategory';

		$fields['content-available'] = true;
		$fields['mutable-content'] = true;
    }  else if($device_type_id == 3) {
        $fields['notification']['message']    = $body;
        $fields['notification']['android_channel_id']    = "test-channel";
        $fields['notification']['largeIcon']    = 'large_icon';
        $fields['notification']['smallIcon']    = 'small_icon';
        $fields['notification']['subtitle']    = '';
        $fields['notification']['tickerText']    = '';
        $fields['notification']['vibrate']    = 1;   

        $fields['data'] = $fields['notification'];
       // $fields['android']    = array('priority' => 'high');
       // $fields['webpush']    = array('headers' => array('Urgency' => 'high'));  
        $fields['delay_while_idle']    = false; 
    }      
    
    //$fields['time_to_live']    = 50;
    $fields['device_type_id'] =	$device_type_id;
    $fields['priority'] =   'high';
    $fields['show_in_foreground'] =	true;
    $fields['content_available'] =	true;   

    $ci->push_notification->send($fields, $device_token);
}


function send_topic_push_notification($topic, $message = '', $badge = 1, $data = array()) {
    $ci = &get_instance();
    $ci->load->library('Push_notification');
    
    $body = '';
    if (isset($data['Summary']) && !empty($data['Summary'])) {
        $body = $data['Summary'];
    }
    
    unset($data['DeviceTypeID']);
    unset($data['FromUserDetails']);
    unset($data['ToUserDetails']);
    unset($data['UserID']);
    unset($data['RefrenceID']);
    
    //Prepare payload for both IOS and Android.
    $fields = array();
    $fields['notification']             = $data;    
    $fields['notification']['message']    = $message;
    $fields['notification']['android_channel_id']    = "test-channel";
    
    
    $fields['notification']['body']     = $body;
    $fields['notification']['title']    = $message;
    $fields['notification']['sound']    = "default";
    $fields['notification']['badge']    = $badge;

    $fields['notification']['largeIcon']    = 'large_icon';
    $fields['notification']['smallIcon']    = 'small_icon';
    $fields['notification']['subtitle']     = '';
    $fields['notification']['tickerText']   = '';
    $fields['notification']['vibrate']      = 1;  

    $fields['data'] = $fields['notification'];
    $fields['android']    = array('priority' => 'high');  
    $fields['delay_while_idle']             = false; 

    //$fields['time_to_live']         = 50;
    $fields['priority']             = 'high';
    $fields['show_in_foreground']   = true;
    $fields['content_available']    = true; 
    $fields['mutable_content']      = true; 
    $fields['category']             = 'myNotificationCategory';

    $fields['content-available'] = true;
    $fields['mutable-content'] = true;
    
    $fields['to']    = '/topics/'.$topic;  

    $ci->push_notification->topic($fields);
}

/*
  |--------------------------------------------------------------------------
  | Push Notification for Iphone
  |--------------------------------------------------------------------------
 */

function push_notification_iphone($deviceToken = '', $message = '', $badge = 1, $Data = array()) {
    $ci = &get_instance();
    $ci->load->library('Push_notification');

    $device_ids = $deviceToken;

    $body = '';
    if (isset($Data['Summary']) && !empty($Data['Summary'])) {
        $body = $Data['Summary'];
    }

    

    //Prepare payload for both IOS and Android.
    $fields = array();
    $fields['notification']             = $Data;
    $fields['notification']['body']     = $body;
    $fields['notification']['title']    = $message;
    $fields['notification']['sound']    = "default";
    $fields['notification']['badge']    = 1;

    $fields['time_to_live']    = 4500;
    $fields['priority'] =	'high';
    $fields['show_in_foreground'] =	true;
    $fields['content_available'] =	true;

    $fields['content-available'] = true;
    $fields['mutable-content'] = true;
    

   /* $badge_arr = array("badge" => 1);
    $aps_arr = array("alert" => '', "badge" => 1);
    $fields['notification']['ios']      = $badge_arr;
    $fields['notification']['aps']      = $aps_arr;
    */
   

    $ci->push_notification->init($fields, $device_ids);
}

/*
  |--------------------------------------------------------------------------
  | Push Notification for Android
  |--------------------------------------------------------------------------
 */

function push_notification_android($registatoin_ids = array(), $message = '', $badge = 1, $Data)
{
    if ($badge == 0) {
        $badge = 1;
    }
    $body = '';
    if (isset($Data['Summary']) && !empty($Data['Summary'])) {
        $body = $Data['Summary'];
    }
    // prep the bundle
  $msg = array(
        'message' => $message,
        'title' => $message,
        'body' => $body,
        'subtitle' => '',
        'tickerText' => '',
        'vibrate' => 1,
        'sound' => 1,
        'largeIcon' => 'large_icon',
        'smallIcon' => 'small_icon',
        'badge' => (int) $badge,
        "android_channel_id" => "test-channel"
    ); 

    $fields = array_merge($msg, $Data);
  
   /* $fields = array();
    $fields['notification']         = $Data;
    $fields['notification']['body'] = $body;
    $fields['notification']['title'] = $message;
    $fields['notification']['sound'] = 1;
    $fields['notification']['largeIcon'] = 'large_icon';
    $fields['notification']['smallIcon'] = 'smallIcon';
    $fields['notification']['android_channel_id'] = 'test-channel';
    $fields['notification']['badge'] = $badge;
    */

    $notification_msg = array(
        'registration_ids' => $registatoin_ids,
        'data' => $fields,
        'notification' => $fields,
        'priority' => 'high',
        'show_in_foreground' => true,
        'content_available' => true,
        'time_to_live' => 4500,
        'delay_while_idle' => false
    );
    $headers = array(
        'Authorization: key=' . SERVER_API_KEY,
        'Content-Type: application/json',
    );

    log_message('error', 'Push Notification Androird PayLoad: ' . json_encode($notification_msg, JSON_NUMERIC_CHECK));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification_msg, JSON_NUMERIC_CHECK));
    try {
        $result = curl_exec($ch);
        if ($result === FALSE) {
            log_message('error', 'FCM Send Error: ' . curl_error($ch));
        }
        /* if ($Data['ToUserID'] ==  1) { 
            log_message('error', 'Reciever - ' . $Data['ToUserID'] . ' Token - ' . $registatoin_ids[0]);
            log_message('error', $result);
        }
        */
        curl_close($ch);
    } catch (Exception $e) {
        log_message('error', 'Error push_notification_android - ' . $e->getMessage());
    }
}

if (!function_exists('is_mobile')) {

    function is_mobile()
    {
        $mobile_browser = 0;

        $userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android)/i', $userAgent)) {
            $mobile_browser++;
        }

        $acceptingContentType = !empty($_SERVER['HTTP_AGENT']) ? strtolower($_SERVER['HTTP_ACCEPT']) : '';

        if ((strpos($acceptingContentType, 'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $mobile_browser++;
        }

        $mobile_ua = substr($userAgent, 0, 4);
        $mobile_agents = array(
            'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac', 'blaz',
            'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno', 'ipaq', 'java', 'jigs', 'kddi', 'keji',
            'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-', 'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp',
            'nec-', 'newt', 'noki', 'oper', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox', 'qwap', 'sage', 'sams',
            'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar', 'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb',
            't-mo', 'teli', 'tim-', 'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp', 'wapr',
            'webc', 'winw', 'winw', 'xda ', 'xda-'
        );

        if (in_array($mobile_ua, $mobile_agents)) {
            $mobile_browser++;
        }

        $IISHttpHeaders = !empty($_SERVER['ALL_HTTP']) ? strtolower($_SERVER['ALL_HTTP']) : '';

        if (strpos($IISHttpHeaders, 'OperaMini') > 0) {
            $mobile_browser++;
        }

        if (strpos($userAgent, 'windows') > 0) {
            $mobile_browser = 0;
        }
        return $mobile_browser;
    }
}

if (!function_exists('aasort')) {

    function aasort(&$array, $key)
    {
        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = $ret;
        $array = array_reverse($array);
    }
}

/**
 * Get full url path for image file.
 * @param string $type
 * @param string $user_unique_id
 * @param $name
 * @param string $height
 * @param string $width
 * @param string $size
 * @return string
 */
if (!function_exists('get_full_path')) {

    function get_full_path($type = 'profile_image', $user_unique_id = '', $name, $height = '', $width = '', $size = '')
    {
        $final_image_path = '';
        $thumb_prefix = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER;

        if ($name != '0' && $name != '') {
            // Check for imported twitter, facebook or linked in image
            if ((strpos($name, 'twimg.com') > 0) || (strpos($name, 'facebook.com') > 0) || (strpos($name, 'licdn.com') > 0 || strpos($name, 'linkedin.com') > 0)) {
                $final_image_path = $name;
            } elseif (strpos($name, 'googleusercontent.com') > 0) { // Check for imported google image
                $final_image_path = $name;
                if ($width != '' && $height != '') {
                    $final_image_path = $name . '?sz=' . $width;
                }
            } else { // Native image
                if ($type == 'course_image') {
                    $final_image_path = $thumb_prefix . 'course/' . $width . 'x' . $height . '/' . $name;
                } elseif ($type == 'wall_image') {
                    $final_image_path = $thumb_prefix . 'wall/' . $width . 'x' . $height . '/' . $name;
                } elseif ($type == 'profile_image') {
                    $final_image_path = $thumb_prefix . $user_unique_id . 'profile/' . $width . 'x' . $height . '/' . $name;
                } elseif ($type == 'group_image') {
                    $final_image_path = $thumb_prefix . 'group/' . $width . 'x' . $height . '/' . $name;
                }
            }
        }
        if (!empty($final_image_path) && @getimagesize($final_image_path)) {
            //Do Nothing
        } else {
            // System default image path
            if ($type == 'profile_image') {
                $final_image_path = ASSET_BASE_URL . 'img/profiles/user_default.jpg';
            } elseif ($type == 'group_image') {
                $final_image_path = ASSET_BASE_URL . 'img/profiles/user_default.jpg';
            } elseif ($type == 'exp_gallery') {
                $final_image_path = ASSET_BASE_URL . 'img/hands-col.jpg';
            } else {
                $final_image_path = ASSET_BASE_URL . 'img/profiles/user_default.jpg';
            }
        }
        return $final_image_path;
    }
}

if (!function_exists('get_profile_cover')) {

    function get_profile_cover($image_name)
    {
        if ($image_name == '' || $image_name == 'default.jpg') {
            $image_path = IMAGE_HTTP_PATH . '../assets/img/cover.jpg';
        } else {
            $image_path = IMAGE_SERVER_PATH . 'upload/profilebanner' . THUMB_profilebanner . $image_name;
        }
        return $image_path;
    }
}

/**
 * @param $url
 * @param string $method
 * @param null $header
 * @param null $postdata
 * @param bool $includeheader
 * @param int $timeout
 * @return mixed
 */
if (!function_exists('curl')) {

    function curl($url, $method = 'get', $header = NULL, $postdata = NULL, $includeheader = FALSE, $timeout = 60)
    {
        $s = curl_init();

        curl_setopt($s, CURLOPT_URL, $url);
        if ($header) {
            curl_setopt($s, CURLOPT_HTTPHEADER, $header);
        }

        /* if ($this->debug) */
        curl_setopt($s, CURLOPT_VERBOSE, FALSE);

        curl_setopt($s, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($s, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($s, CURLOPT_MAXREDIRS, 3);
        curl_setopt($s, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($s, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($s, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($s, CURLOPT_COOKIEFILE, 'cookie.txt');

        if (strtolower($method) == 'post') {
            curl_setopt($s, CURLOPT_POST, TRUE);
            curl_setopt($s, CURLOPT_POSTFIELDS, $postdata);
        } else if (strtolower($method) == 'delete') {
            curl_setopt($s, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } else if (strtolower($method) == 'put') {
            curl_setopt($s, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($s, CURLOPT_POSTFIELDS, $postdata);
        }

        if ($includeheader) {
            curl_setopt($s, CURLOPT_HEADER, $includeheader);
        }
        curl_setopt($s, CURLOPT_SSL_VERIFYPEER, FALSE);

        $html = curl_exec($s);
        $status = curl_getinfo($s, CURLINFO_HTTP_CODE);

        curl_close($s);

        return $html;
    }
}

if (!function_exists('ExecuteCurl')) {

    function ExecuteCurl($url, $jsondata = '', $post = '', $headerData = [])
    {
        $ch = curl_init();
        $headers = array('Accept: application/json', 'Content-Type: application/json', 'APPVERSION: v2');
        if (!empty($headerData) && is_array($headerData)) {
            foreach ($headerData as $key => $value) {
                $headers[$key] = $value;
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($jsondata != '') {
            $count = count(json_decode($jsondata, true));
            curl_setopt($ch, CURLOPT_POST, $count);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        if ($post != '') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $post);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

if (!function_exists('EscapeString')) {

    function EscapeString($string)
    {
        $string = trim($string);
        $CI = &get_instance();
        return $CI->db->escape_str($string);
    }
}

if (!function_exists('getRealIpAddr')) {

    function getRealIpAddr()
    {
        if (ENVIRONMENT == 'development') {
            return '103.21.54.66';
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR']; //'103.15.66.178';//
        }
        return $ip;
    }
}

if (!function_exists('valid_email')) {

    function valid_email($address)
    {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? FALSE : TRUE;
    }
}

/**
 * Create GUID
 * @return string
 */
if (!function_exists('get_guid')) {

    function get_guid()
    {
        if (function_exists('com_create_guid')) {
            return strtolower(com_create_guid());
        } else {
            mt_srand((float) microtime() * 10000); //optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
            return strtolower($uuid);
        }
    }
}

/**
 *
 * @access  public
 * @param   entity_id  // Entity ID
 * @param   entity_type  // Entity Type
 * @return  string //The string returned is the Entity URL
 */
if (!function_exists('get_entity_url')) {

    function get_entity_url($entity_id, $entity_type = "User", $is_url = 0)
    {
        $uri = '';
        $CI = &get_instance();
        if ($entity_type == "User" && !empty($entity_id)) {
            if (CACHE_ENABLE) {
                $userdata = $CI->cache->get('user_profile_' . $entity_id);
                if (!is_array($userdata)) {
                    $userdata = '';
                }
                if (!empty($userdata)) {
                    $uri = isset($userdata['ProfileURL']) ? $userdata['ProfileURL'] : '';
                }
            }
        }
        if (!empty($entity_id) && empty($uri)) {
            $CI->db->select('Url');
            $CI->db->from(PROFILEURL);
            $CI->db->where(array('EntityID' => $entity_id, 'EntityType' => $entity_type));
            $CI->db->limit('1');
            $query = $CI->db->get();
            if ($query->num_rows() > 0) {
                $result = $query->row();
                if ($result->Url) {
                    $uri = $result->Url;
                }
            }
        }

        if(empty($uri))
        {
            $CI->db->select('UserGUID');
            $CI->db->from(USERS);
            $CI->db->where(array('UserID' => $entity_id));
            $CI->db->limit('1');
            $query = $CI->db->get();
            if ($query->num_rows() > 0) {
                $result = $query->row();
                if ($result->UserGUID) {
                    $uri = $result->UserGUID;
                }
            }
        }

        if (!$is_url) {
            $uri = site_url() . $uri;
        }
        return $uri;
    }
}

if (!function_exists('get_user_id_by_loginsessionkey')) {

    function get_user_id_by_loginsessionkey($login_session_key)
    {
        $CI = &get_instance();
        $CI->db->select('UserID');
        $CI->db->from(ACTIVELOGINS);
        $CI->db->where('LoginSessionKey', $login_session_key);
        $CI->db->limit('1');
        $query = $CI->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
            if ($result->UserID) {
                return $result->UserID;
            }
        }
        return 0;
    }
}

/**
 *
 * @access  public
 * @param   entity_guid  // Entity GUID
 * @param   module_id  // Module ID
 * @param   select_field  // comma separated list of  Field
 * @param   response_type  // 1 for string, 2 for array
 * @return           // depend on response_type
 */
if (!function_exists('get_detail_by_guid')) {

    function get_detail_by_guid($entity_guid, $module_id = 0, $select_field = "", $response_type = 1)
    {
        if (!empty($entity_guid)) {
            $CI = &get_instance();
            $db_obj = $CI->db;
            if(in_array($module_id, array(0,20,13,21,30))) {
                $CI->load->model(array('users/user_model'));
                $db_obj = $CI->user_model->current_db;
            }
            switch ($module_id) {
                case '1':
                    $table_name = GROUPS;
                    $select_fields = ($select_field) ? $select_field : "GroupID";
                    $condition = array("GroupGUID" => $entity_guid);
                    break;
                case '13':
                    $table_name = ALBUMS;
                    $select_fields = ($select_field) ? $select_field : "AlbumID";
                    $condition = array("AlbumGUID" => $entity_guid);
                    break;
                case '3':
                    $table_name = USERS;
                    $select_fields = ($select_field) ? $select_field : "UserID";
                    $condition = array("UserGUID" => $entity_guid);
                    break;
                case '20':
                    $table_name = POSTCOMMENTS;
                    $select_fields = ($select_field) ? $select_field : "PostCommentID";
                    $condition = array("PostCommentGUID" => $entity_guid);
                    break;
                case '14':
                    $table_name = EVENTS;
                    $select_fields = ($select_field) ? $select_field : "EventID";
                    $condition = array("EventGUID" => $entity_guid);
                    break;
                case '18':
                    $table_name = PAGES;
                    $select_fields = ($select_field) ? $select_field : "PageID";
                    $condition = array("PageGUID" => $entity_guid);
                    break;
                case '21':
                    $table_name = MEDIA;
                    $select_fields = ($select_field) ? $select_field : "MediaID";
                    $condition = array("MediaGUID" => $entity_guid);
                    break;
                case '23':
                    $table_name = RATINGS;
                    $select_fields = ($select_field) ? $select_field : "RatingID";
                    $condition = array('RatingGUID' => $entity_guid);
                    break;
                case '24':
                    $table_name = BLOG;
                    $select_fields = ($select_field) ? $select_field : "BlogID";
                    $condition = array("BlogGUID" => $entity_guid);
                    break;
                case '28':
                    $table_name = REMINDER;
                    $select_fields = ($select_field) ? $select_field : "ReminderID";
                    $condition = array("ReminderGUID" => $entity_guid);
                    break;
                case '30':
                    $table_name = POLL;
                    $select_fields = ($select_field) ? $select_field : "PollID";
                    $condition = array("PollGUID" => $entity_guid);
                    break;
                case '33':
                    $table_name = FORUM;
                    $select_fields = ($select_field) ? $select_field : "ForumID";
                    $condition = array("ForumGUID" => $entity_guid);
                    break;
                case '34':
                    $table_name = FORUMCATEGORY;
                    $select_fields = ($select_field) ? $select_field : "ForumCategoryID";
                    $condition = array("ForumCategoryGUID" => $entity_guid);
                    break;
                case '47':
                    $table_name = QUIZ;
                    $select_fields = ($select_field) ? $select_field : "QuizID";
                    $condition = array("QuizGUID" => $entity_guid);
                    break;
                default:
                    $table_name = ACTIVITY;
                    $select_fields = ($select_field) ? $select_field : "ActivityID";
                    $condition = array("ActivityGUID" => $entity_guid);
                    break;
            }
            $db_obj->select($select_fields, FALSE);
            $db_obj->from($table_name);
            $db_obj->where($condition);
            $db_obj->limit('1');
            $query = $db_obj->get();
            if ($query->num_rows() > 0) {
                $result = $query->row_array();

                if ($module_id == '1') {
                    foreach ($result as $key => $val) {
                        if ($key == 'GroupName' && $val == '') {
                            $CI->load->model('group/group_model');
                            $result[$key] = $CI->group_model->get_informal_group_name($entity_guid, 0, 1);
                        }
                    }
                }

                if ($response_type == 2) {
                    return $result;
                } else {
                    return $result[$select_fields];
                }
            }
        }
        return 0;
    }
}

/**
 *
 * @access  public
 * @param   entity_id  // Entity ID
 * @param   module_id  // Module ID
 * @param   select_field  // comma separated list of  Field
 * @param   response_type  // 1 for string, 2 for array
 * @return           // depend on response_type
 */
if (!function_exists('get_detail_by_id')) {

    function get_detail_by_id($entity_id, $module_id = 0, $select_field = "", $response_type = 1)
    {
        if (!empty($entity_id) && !is_null($entity_id) && (is_string($entity_id) || is_int($entity_id))) {
            $CI = &get_instance();
            $db_obj = $CI->db;
            if(in_array($module_id, array(0,20,13,21,30))) {
                $CI->load->model(array('users/user_model'));
                $db_obj = $CI->user_model->current_db;
            }

            switch ($module_id) {
                case '1':
                    $table_name = GROUPS;
                    $select_fields = ($select_field) ? $select_field : "GroupGUID";
                    $condition = array("GroupID" => $entity_id);
                    break;
                case '3':                    
                    $table_name = USERS;
                    $select_fields = ($select_field) ? $select_field : "UserGUID";
                    $condition = array("UserID" => $entity_id);
                    break;
                case '20':
                    $table_name = POSTCOMMENTS;
                    $select_fields = ($select_field) ? $select_field : "PostCommentGUID";
                    $condition = array("PostCommentID" => $entity_id);
                    break;
                case '13':
                    $table_name = ALBUMS;
                    $select_fields = ($select_field) ? $select_field : "AlbumGUID";
                    $condition = array("AlbumID" => $entity_id);
                    break;
                case '14':
                    $table_name = EVENTS;
                    $select_fields = ($select_field) ? $select_field : "EventGUID";
                    $condition = array("EventID" => $entity_id);
                    break;
                case '18':
                    $table_name = PAGES;
                    $select_fields = ($select_field) ? $select_field : "PageGUID";
                    $condition = array("PageID" => $entity_id);
                    break;
                case '21':
                    $table_name = MEDIA;
                    $select_fields = ($select_field) ? $select_field : "MediaGUID";
                    $condition = array("MediaID" => $entity_id);
                    break;
                case '23':
                    $table_name = RATINGS;
                    $select_fields = ($select_field) ? $select_field : "RatingGUID";
                    $condition = array("RatingID" => $entity_id);
                    break;
                case '24':
                    $table_name = BLOG;
                    $select_fields = ($select_field) ? $select_field : "BlogGUID";
                    $condition = array("BlogID" => $entity_id);
                    break;
                case '25':
                    $table_name = N_MESSAGES;
                    $select_fields = ($select_field) ? $select_field : "MessageGUID";
                    $condition = array("MessageID" => $entity_id);
                    break;
                case '27':
                    $table_name = CATEGORYMASTER;
                    $select_fields = ($select_field) ? $select_field : "CategoryID";
                    $condition = array("CategoryID" => $entity_id);
                    break;
                case '28':
                    $table_name = REMINDER;
                    $select_fields = ($select_field) ? $select_field : "MessageGUID";
                    $condition = array("ReminderID" => $entity_id);
                    break;
                case '30':
                    $table_name = POLL;
                    $select_fields = ($select_field) ? $select_field : "PollGUID";
                    $condition = array("PollID" => $entity_id);
                    break;
                case '33':
                    $table_name = FORUM;
                    $select_fields = ($select_field) ? $select_field : "ForumGUID";
                    $condition = array("ForumID" => $entity_id);
                    break;
                case '34':
                    $table_name = FORUMCATEGORY;
                    $select_fields = ($select_field) ? $select_field : "ForumCategoryGUID";
                    $condition = array("ForumCategoryID" => $entity_id);
                    break;
                case '47':
                    $table_name = QUIZ;
                    $select_fields = ($select_field) ? $select_field : "QuizGUID";
                    $condition = array("QuizID" => $entity_id);
                    break;
                default:
                    $table_name = ACTIVITY;
                    $select_fields = ($select_field) ? $select_field : "ActivityGUID";
                    $condition = array("ActivityID" => $entity_id);
                    break;
            }

            /* --Added condition if needed all the fields-- */
            if ($response_type == 2 && empty($select_field)) {
                $select_fields = "*";
            }
            /* -------------------------------------------- */
            $result = array();
            if ($module_id == 3) {
                if (!empty($select_fields) && $select_fields != '*' && CACHE_ENABLE) {
                    $user_file_data = $CI->cache->get('user_profile_' . $entity_id);
                    if (!is_array($user_file_data)) {
                        $user_file_data = "";
                    }
                    if (!empty($user_file_data)) {
                        $field_array = explode(',', $select_fields);
                        foreach ($field_array as $field_item) {
                            $field_item = trim($field_item);
                            if ($field_item == 'UserID') {
                                $result['UserID'] = $entity_id;
                            } else {
                                if (isset($user_file_data[$field_item])) {
                                    $result[$field_item] = $user_file_data[$field_item];
                                }
                            }
                        }
                    } else {
                        initiate_worker_job('profile_cache', array('user_id' => $entity_id));
                    }
                }
            } else if ($module_id == 0 || $module_id == '') {
                if (!empty($select_fields) && $select_fields != '*' && CACHE_ENABLE) {
                    $cache_data = $CI->cache->get('activity_' . $entity_id);

                    if (!is_array($cache_data)) {
                        $cache_data = "";
                    }

                    if (!empty($cache_data)) {
                        $field_array = explode(',', $select_fields);
                        foreach ($field_array as $field_item) {
                            $field_item = trim($field_item);
                            if ($field_item == 'ActivityID') {
                                $result['ActivityID'] = $entity_id;
                            } else {
                                if (isset($cache_data[$field_item])) {
                                    $result[$field_item] = $cache_data[$field_item];
                                }
                            }
                        }
                    } else {
                        initiate_worker_job('activity_cache', array('ActivityID' => $entity_id));
                    }
                }
            } else if ($module_id == 18) {
                if (!empty($select_fields) && $select_fields != '*' && CACHE_ENABLE) {
                    $cache_data = $CI->cache->get('page_' . $entity_id);
                    if (!empty($cache_data)) {
                        $field_array = explode(',', $select_fields);
                        foreach ($field_array as $field_item) {
                            $field_item = trim($field_item);
                            if ($field_item == 'Title AS Name') {
                                $result['Title'] = $cache_data['Title'];
                            } else if ($field_item == 'PageID') {
                                $result['PageID'] = $entity_id;
                            } else {
                                $result[$field_item] = $cache_data[$field_item];
                            }
                        }
                    } else {
                        initiate_worker_job('page_cache', array('PageID' => $entity_id));
                    }
                }
            } else if ($module_id == 1) {
                if (!empty($select_fields) && $select_fields != '*' && CACHE_ENABLE) {
                    $cache_data = $CI->cache->get('group_cache_' . $entity_id);
                    if (!is_array($cache_data)) {
                        $cache_data = "";
                    }
                    if (!empty($cache_data)) {
                        $field_array = explode(',', $select_fields);
                        foreach ($field_array as $field_item) {
                            $field_item = trim($field_item);
                            if ($field_item == 'GroupID') {
                                $result['GroupID'] = $entity_id;
                            } else if ($field_item == 'CreatedBy') {
                                $user_temp = get_detail_by_id($cache_data['CreatedByID'], 3, "FirstName,LastName", 2);
                                if (!empty($user_temp)) {
                                    $result['CreatedBy'] = trim($user_temp['FirstName'] . ' ' . $user_temp['LastName']);
                                }
                            }
                            if ($field_item == 'GroupName' && $cache_data['GroupName'] == '') {
                                $CI->load->model('group/group_model');
                                $result['GroupName'] = $CI->group_model->get_informal_group_name($entity_id, $CI->UserID);
                            } else {
                                $result[$field_item] = $cache_data[$field_item];
                            }
                        }
                    } else {
                        initiate_worker_job('group_cache', array('group_id' => $entity_id));
                    }
                }
            }
            if (empty($result)) {
                $query = array();
                $db_obj->select($select_fields, FALSE);
                $db_obj->from($table_name);
                $db_obj->where($condition);
                $db_obj->limit(1);
                $query = $db_obj->get();
                if ($query->num_rows() > 0) {
                    $result = $query->row_array();

                    if ($module_id == '1') {
                        foreach ($result as $key => $val) {
                            if ($key == 'GroupName' && $val == '') {
                                $CI->load->model('group/group_model');
                                $result[$key] = $CI->group_model->get_informal_group_name($entity_id, $CI->UserID);
                            }
                        }
                    }
                }
            }
            if ($response_type == 2) {
                return $result;
            } else {
                if ($select_fields == '*') {
                    return $result;
                }

                if (!empty($result)) {
                    return $result[$select_fields];
                }
            }
        }
        return 0;
    }
}

/**
 *
 * @access  public
 * @param   entity_id  // Entity ID
 * @param   module_id  // Module ID
 * @return  INT         // Entity GUID
 */
if (!function_exists('get_guid_by_id')) {

    function get_guid_by_id($entity_id, $module_id = 0)
    {
        if (!empty($entity_id)) {
            $CI = &get_instance();
            switch ($module_id) {
                case '1':
                    $table_name = GROUPS;
                    $select_field = "GroupGUID";
                    $condition = array("GroupID" => $entity_id);
                    break;
                case '3':
                    $table_name = USERS;
                    $select_field = "UserGUID";
                    $condition = array("UserID" => $entity_id);
                    break;
                case '20':
                    $table_name = POSTCOMMENTS;
                    $select_field = "PostCommentGUID";
                    $condition = array("PostCommentID" => $entity_id);
                    break;
                case '14':
                    $table_name = EVENTS;
                    $select_field = "EventGUID";
                    $condition = array("EventID" => $entity_id);
                    break;
                case '47':
                    $table_name = QUIZ;
                    $select_field = "QuizGUID";
                    $condition = array("QuizID" => $entity_id);
                    break;    
                default:
                    $table_name = ACTIVITY;
                    $select_field = "ActivityGUID";
                    $condition = array("ActivityID" => $entity_id);
                    break;
            }
            $CI->db->select($select_field);
            $CI->db->from($table_name);
            $CI->db->where($condition);
            $CI->db->limit('1');
            $query = $CI->db->get();
            if ($query->num_rows() > 0) {
                $result = $query->row();
                return $result->$select_field;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
}
/**
 * Function sendEmailAndSave for send email to user and save email html in DB
 * @params array $dataArr
 *  $dataArr['Subject'] Email subject
 *  $dataArr['TemplateName'] email template path with folder name from view
 *  $dataArr['Data'] data array which is used in template
 *  $dataArr['Email'] user email address(send to email)
 *  $dataArr['IsResend'] - For new email set 0, and for resend email set 1
 *  $dataArr['Message'] - set already send email html in this parameter from DB
 *  $dataArr['IsSave']  - (0/1) 0 - For only send email not save in DB, and 1- For send email and save DB both
 *  $dataArr['UserID'] - user id
 *  $dataArr['EmailTypeID'] - email type id like Registration, Forgot Password, Communication email type id from DB
 *  $dataArr['StatusMessage'] - status message
 * @return boolean
 */
if (!function_exists('sendEmailAndSave')) {

    function sendEmailAndSave($dataArr, $save_only = 0)
    {
        $CI = &get_instance();
        $CI->db->select('Name, StatusID');
        $CI->db->from(EMAILTYPES);
        $CI->db->where(array('EmailTypeID' => $dataArr['EmailTypeID']));
        $CI->db->limit('1');
        $query = $CI->db->get();
        $typaArr = $query->row_array();
        if ($typaArr['StatusID'] == 2) {

            $CI->lang->load('notification');
            $layout = $CI->config->item("email_layout");
            $global_settings = $CI->config->item("global_settings");

            $emailData = array("data" => $dataArr['Data']);
            $Subject = $dataArr['Subject'];
            $Template = $dataArr['TemplateName'];
            $Email = $dataArr['Email'];
            $FromUserID = isset($dataArr['FromUserID']) ? $dataArr['FromUserID'] : 0;

            $FromEmail = '';
            if ($dataArr['IsResend']) {
                $email_html = $dataArr['Message'];
            } else {
                $emailData['content_view'] = $Template;
                $email_html = $CI->load->view($layout, $emailData, TRUE);
            }

            if ($global_settings['send_mail_via_mandrill'] == 1) {
                $ESPID = 2;
            } else {
                $ESPID = 1;
            }
            if ($global_settings['global_email_sending'] == 1 && SEND_EMAIL_BY_CRON != 1) {
                $smtp_settings = $CI->config->item("smtp_settings");
                $smtpData = array();
                //CommonAdmin Code
                if (isset($smtp_settings[$dataArr['EmailTypeID']])) {
                    if ($smtp_settings[$dataArr['EmailTypeID']]['SmtpStatusID'] == 2) {
                        $smtpData = $smtp_settings[$dataArr['EmailTypeID']];
                    } else {
                        $smtpData = $smtp_settings['default'];
                    }

                    //add additional heade in case reply_to or any other header info have to be added
                    if (isset($dataArr['EntityGUID']) && !empty($dataArr['EntityGUID'])) {
                        $additionalHeader = array('reply_to' => $dataArr['EntityGUID'] . '-noreply@vinfotech.org');
                    } else {
                        $additionalHeader = Null;
                    }

                    $FromEmail = $smtpData['FromEmail'];
                    if (!$save_only || EMAIL_ANALYTICS == 0) {
                        $result = @sendMail($smtpData, $Email, $Subject, $email_html, $additionalHeader);
                        if (!$result) {
                            return 'invalid';
                        }
                    }
                } else {
                    return 'invalid';
                }
            } else if ($global_settings['send_mail_via_mandrill'] == 1 && SEND_EMAIL_BY_CRON != 1) {

                $mandrillArr = array();
                $mandrillArr['mandrill_api_key'] = ($global_settings['mandrill_api_key']) ? $global_settings['mandrill_api_key'] : MANDRILL_API_KEY;
                $mandrillArr['mandrill_from_email'] = $FromEmail = ($global_settings['mandrill_from_email']) ? $global_settings['mandrill_from_email'] : MANDRILL_FROM_EMAIL;
                $mandrillArr['mandrill_from_name'] = ($global_settings['mandrill_from_name']) ? $global_settings['mandrill_from_name'] : MANDRILL_FROM_NAME;
                $mandrillArr['subject'] = $Subject;
                $mandrillArr['email_html'] = $email_html;
                $mandrillArr['user_email'] = $Email;
                $mandrillArr['user_name'] = '';
                $mandrillArr['EmailTypeID'] = $dataArr['EmailTypeID'];
                $mandrillArr['tag'] = ($typaArr['Name']) ? $typaArr['Name'] : "Communication";

                if (!$save_only || EMAIL_ANALYTICS == 0) {
                    $result = @sendMandrillEmails($mandrillArr);
                    if (!$result) {
                        return 'invalid';
                    }
                }
            }

            //For save email data in communicatoin table
            if (EMAIL_ANALYTICS == 1) {
                $StatusMessage = '';
                if (isset($dataArr['StatusMessage'])) {
                    $StatusMessage = $dataArr['StatusMessage'];
                }
                if (strlen($Subject) >= 100) {
                    $Subject = substr($Subject, 0, 100);
                } else {
                    $Subject = $Subject;
                }

                if (SEND_EMAIL_BY_CRON == 1) {
                    $status = 1;
                } else {
                    $status = 2;
                }

                if ($save_only) {
                    $status = 1;
                }

                if (empty($FromEmail)) {
                    $FromEmail = FROM_EMAIL;
                }

                $communicationData = array(
                    'UserID' => $dataArr['UserID'],
                    'EmailTypeID' => $dataArr['EmailTypeID'],
                    'ESPID' => $ESPID,
                    'FromEmail' => $FromEmail,
                    'EmailTo' => $Email,
                    'Subject' => $Subject,
                    'Body' => $email_html,
                    'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                    'ProcessDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                    'StatusID' => $status,
                    'StatusMessage' => $StatusMessage,
                    'FromUserID' => $FromUserID,
                    'ReplyTo' => (isset($dataArr['EntityGUID']) && $dataArr['EntityGUID']) ? $dataArr['EntityGUID'] . '-noreply@vinfotech.org' : Null,
                );

                if (empty($dataArr['UserID'])) {
                    unset($communicationData['UserID']);
                }

                if (!empty($Email)) {
                    $CI->db->insert(COMMUNICATIONS, $communicationData);
                    return $CI->db->insert_id();
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
    }
}

/**
 * Function checkSMTPSettingViaSendEmail for check SMTP details is valid or not
 * @params array $dataArr
 * @return boolean
 */
if (!function_exists('checkSMTPSettingViaSendEmail')) {

    function checkSMTPSettingViaSendEmail($dataArr)
    {
        $CI = &get_instance();
        $CI->lang->load('notification');
        $layout = $CI->config->item("email_layout");

        $emailData = array();
        $Subject = $dataArr['Subject'];
        $Template = $dataArr['TemplateName'];
        $Email = $dataArr['Email'];

        $emailData['content_view'] = $Template;
        $email_html = $CI->load->view($layout, $emailData, TRUE);

        $smtpData = array();
        $smtpData['ServerName'] = $dataArr['ServerName'];
        $smtpData['UserName'] = $dataArr['UserName'];
        $smtpData['Password'] = $dataArr['Password'];
        $smtpData['SPortNo'] = $dataArr['SPortNo'];
        $smtpData['FromEmail'] = $dataArr['FromEmail'];
        $smtpData['FromName'] = $dataArr['FromName'];
        $smtpData['ReplyTo'] = $dataArr['ReplyTo'];
        $result = @sendMail($smtpData, $Email, $Subject, $email_html);
        if (!$result) {
            return 'invalid';
        } else {
            return 'valid';
        }
    }
}

/**
 * For send email via Mandrill API
 * @param array $dataArr
 */
if (!function_exists('sendMandrillEmails')) {

    function sendMandrillEmails($dataArr)
    {
        try {
            require_once APPPATH . '/libraries/Mandrill.php';
            $mandrill = new Mandrill($dataArr['mandrill_api_key']);

            if (isset($dataArr['type'])) {
                $type = $dataArr['type'];
            } else {
                $type = 'to';
            }
            $message = array(
                'subject' => $dataArr['subject'],
                'html' => $dataArr['email_html'], // or just use 'text' to support Text
                'from_email' => $dataArr['mandrill_from_email'],
                'from_name' => $dataArr['mandrill_from_name'], //optional
                'to' => array(
                    array( // add more sub-arrays for additional recipients
                        'email' => $dataArr['user_email'],
                        'name' => $dataArr['user_name'], // optional
                        'type' => $type, //optional. Default is 'to'. Other options: cc & bcc
                    ),
                ),
                "metadata" => array(
                    "EmailTypeID" => $dataArr['EmailTypeID']
                ),
                'tags' => array("EmailTypeID" => $dataArr['EmailTypeID']),
                /* Other API parameters (e.g., 'preserve_recipients => FALSE', 'track_opens => TRUE',
                      'track_clicks' => TRUE) go here */
            );

            $result = $mandrill->messages->send($message);
            if (is_array($result)) {
                return true;
            } else {
                return $result;
            }
        } catch (Mandrill_Error $e) {
            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
        }
    }
}

/**
 * [check_browser check the browser and return browser id or details based on full_details flag]
 * @param  integer $full_details [flag to return browser id or details]
 * @return [array/id]                [based on full_details]
 */
if (!function_exists('check_browser')) {

    function check_browser($full_details = 0)
    {
        $CI = &get_instance();
        $CI->load->library('user_agent');
        $version = $CI->agent->version();
        if ($CI->agent->is_browser()) {
            $agent = $CI->agent->browser();
        } elseif ($CI->agent->is_robot()) {
            $agent = $CI->agent->robot();
        } elseif ($CI->agent->is_mobile()) {
            $agent = $CI->agent->mobile();
        } else {
            $agent = 'Unidentified User Agent';
        }
        $query = $CI->db->get_where(BROWSERS, array('Name' => $agent));
        if ($query->num_rows()) {
            $browser_id = $query->row()->BrowserID;
        } else {
            $CI->db->insert(BROWSERS, array('Name' => $agent));
            $browser_id = $CI->db->insert_id();
        }
        if ($full_details) {
            return array("browser" => $agent, "version" => $version, "platform" => $CI->agent->platform(), 'agent' => $CI->agent->agent);
        }
        return $browser_id;
    }
}

/**
 *  @param $user_id
 *  @return TimeZone
 */
if (!function_exists('get_user_time_zone')) {

    function get_user_time_zone($user_id)
    {
        $CI = &get_instance();
        $time_zone = '';
        if (CACHE_ENABLE) {
            $userdata = $CI->cache->get('user_profile_' . $user_id);
            if (!is_array($userdata)) {
                $userdata = '';
            }
            if (!empty($userdata)) {
                if (isset($userdata['TimeZoneText'])) {
                    $time_zone = $userdata['TimeZoneText'];
                }
            }
        }
        if (empty($time_zone)) {
            $time_zone = 'UTC';
            $CI->db->select('TZ.StandardTime');
            $CI->db->from(TIMEZONES . ' TZ');
            $CI->db->join(USERDETAILS . ' UD', 'UD.TimeZoneID=TZ.TimeZoneID');
            $CI->db->where('UD.UserID', $user_id);
            $CI->db->limit('1');
            $query = $CI->db->get();
            if ($query->num_rows()) {
                $time_zone = $query->row()->StandardTime;
            }
        }
        return $time_zone;
    }
}

/**
 *
 * @param   date_format
 * @return  Current UTC Date
 */
if (!function_exists('get_current_date')) {

    function get_current_date($date_format, $timediff = 0, $plus = 0, $time = 0)
    {
        $CI = &get_instance();
        $CI->load->helper('date');
        $now = now();
        if ($time) {
            $now = $time;
        }
        if ($timediff) {
            if ($plus) {
                $now = $now + (24 * 60 * 60 * $timediff);
            } else {
                $now = $now - (24 * 60 * 60 * $timediff);
            }
        }
        return mdate($date_format, $now);
    }
}

/**
 *
 * @access  public
 * @param   UserID  // User ID
 * @param   ModuleID  // Module Type
 * @param   ModuleEntityID  // Module Entity ID
 * @param   Action  // like Sticky
 * @return  INT         // Entity ID
 */
if (!function_exists('checkPermission')) {

    function checkPermission($UserID, $ModuleID, $ModuleEntityID, $Action = "Sticky", $entity_module_id = 3, $entity_id = 0)
    {
        $CI = &get_instance();
        switch ($ModuleID) {
            case '3':
                if ($UserID == $ModuleEntityID) {
                    return true;
                } else {
                    $CI->load->model('users/user_model');
                    if ($CI->user_model->is_super_admin($UserID)) {
                        return true;
                    }
                }
                break;
            case '1':

                $CI->load->model('group/group_model');
                switch ($Action) {
                    case 'IsBlocked':
                        if (check_blocked_user($UserID, $ModuleID, $ModuleEntityID)) {
                            return true;
                        }
                        break;
                    case 'IsActive':
                        if ($CI->group_model->check_active_status($entity_id, $entity_module_id, $ModuleEntityID)) {
                            return true;
                        }
                        break;
                    case 'IsOwner':
                    case 'IsCreator':
                    case 'Sticky':
                    case 'IsDirectMember':
                        if ($CI->group_model->check_group_membership($entity_id, $entity_module_id, $ModuleEntityID, $Action)) {
                            return true;
                        }
                        break;
                    case 'IsMember':
                        if ($CI->group_model->check_membership($ModuleEntityID, $UserID)) {
                            return true;
                        }
                        break;

                    case 'IsPublic':
                        if ($CI->group_model->check_public_group($ModuleEntityID)) {
                            return true;
                        }
                        break;
                    case 'IsAccess':
                        return check_group_permissions($UserID, $ModuleEntityID, FALSE);
                        break;
                    default:
                        return false;
                }
                break;
            case '18':
                $CI->load->model('pages/page_model');
                switch ($Action) {
                    case 'IsBlocked':
                        if ($CI->page_model->check_blocked_user($UserID, $ModuleID, $ModuleEntityID)) {
                            return true;
                        }
                        break;
                    case 'user':
                        if ($CI->page_model->check_page_permission($UserID, $ModuleEntityID)) {
                            return true;
                        }
                        break;
                    case 'IsOwner':
                        if ($CI->page_model->check_page_owner($UserID, $ModuleEntityID)) {
                            return true;
                        }
                        break;
                    case 'IsMember':
                        if ($CI->page_model->check_member($ModuleEntityID, $UserID)) {
                            return true;
                        }
                        break;
                    case 'Sticky':
                        if ($CI->page_model->check_page_owner($UserID, $ModuleEntityID)) {
                            return true;
                        } else {
                            return false;
                        }
                        break;
                    default:
                        return false;
                }
                break;
            case '14':
                switch ($Action) {
                    case 'IsAccess':
                        return checkEventPermissions($UserID, $ModuleEntityID);
                        break;
                    default:
                        return true;
                }
                break;
            case '34':
                $CI->load->model('forum/forum_model');
                $permissions = $CI->forum_model->check_forum_category_permissions($UserID, $ModuleEntityID, FALSE);
                if ($permissions['IsAdmin']) {
                    return true;
                }
            default:
                return false;
        }
        return false;
    }
}

/**
 * [check_blocked_user  to check if user is blocked]
 * @param [int]      $user_id
 * @param [int]      $module_id           [ModuleID]
 * @param [String]   $module_entity_id     [ModuleEntityID]
 */
if (!function_exists('check_blocked_user')) {

    function check_blocked_user($user_id, $module_id, $module_entity_id)
    {
        $CI = &get_instance();
        $CI->db->select('StatusID');
        $CI->db->where('ModuleID', $module_id);
        if ($module_id == 3) {
            $CI->db->where('((UserID="' . $module_entity_id . '" AND EntityID="' . $user_id . '") OR (EntityID="' . $module_entity_id . '" AND UserID="' . $user_id . '"))', NULL, FALSE); //
        } else {
            $CI->db->where('(ModuleEntityID="' . $module_entity_id . '" AND EntityID="' . $user_id . '")', NULL, FALSE); //
        }

        $CI->db->limit('1');
        $sql = $CI->db->get(BLOCKUSER);
        $return_flag = false;
        if ($sql->num_rows() > 0) {
            $return_flag = true;
        }
        return $return_flag;
    }
}

/**
 * @Method : To check Event's permissions for logged in user(for internal use)
 * @params : UserID,ModuleEntityID
 * @Output : array
 */
if (!function_exists('checkEventPermissions')) {

    function checkEventPermissions($UserID, $ModuleEntityID)
    {
        $CI = &get_instance();

        $CI->load->model('events/event_model');

        $PermissionArr = array();
        //Check Permissions
        $IsAccess = $CI->event_model->checkPermissionWithDetail($UserID, $ModuleEntityID);
        // Set default permissions
        $PermissionArr['IsAccess'] = false;
        $PermissionArr['IsOwner'] = false;
        $PermissionArr['IsCreator'] = false;
        $PermissionArr['IsMember'] = false;
        $PermissionArr['IsActiveMember'] = false;
        $PermissionArr['IsFriend'] = false;
        $PermissionArr['access'] = false;
        $PermissionArr['IsAdmin'] = false;
        $PermissionArr['IsInvited'] = false;
        if (!empty($IsAccess)) { // If User can access the event set params
            $PermissionArr['IsAccess'] = true;

            // Check if logged user is already a member of requested event
            $Member = $CI->event_model->check_member($ModuleEntityID, $UserID, true);

            if ($IsAccess['Privacy'] == 'PUBLIC') { // If event is public than everyone can post
                $PermissionArr['IsFriend'] = true;
            }

            if (!empty($Member)) {
                // Set can post value if already a member
                $PermissionArr['IsFriend'] = $Member['CanPostOnWall'];

                if ($Member['Presence'] == 'INVITED') { // check if member is invited
                    $PermissionArr['IsInvited'] = 1;
                    $PermissionArr['access'] = 1;
                }

                $PermissionArr['IsMember'] = true;

                $PermissionArr['IsActiveMember'] = true;

                if ($Member['ModuleRoleID'] == 1) { // Check user type for owner or creator
                    $PermissionArr['IsOwner'] = true;
                    $PermissionArr['IsAdmin'] = true;
                    $PermissionArr['IsCreator'] = true;
                    $PermissionArr['IsFriend'] = true;
                } else if ($Member['ModuleRoleID'] == 2) { // Check user type for owner
                    $PermissionArr['IsOwner'] = true;
                    $PermissionArr['IsAdmin'] = true;
                    $PermissionArr['IsCreator'] = false;
                    $PermissionArr['IsFriend'] = true;
                }
            } else {
                // check if member is invited
                $IsInvited = $CI->event_model->is_invited($ModuleEntityID, $UserID);
                if (!empty($IsInvited)) { // check if member is invited
                    $PermissionArr['IsInvited'] = 1;
                    $PermissionArr['access'] = 1;
                }
            }
        }
        return $PermissionArr;
    }
}

/**
 * @Method : To check group permissions for logged in user(for internal use)
 * @params : UserID,ModuleEntityID
 * @Output : array
 */
if (!function_exists('check_group_permissions')) {

    function check_group_permissions($user_id, $group_id, $with_details = TRUE)
    {
        $CI = &get_instance();
        $CI->load->model('group/group_model');

        $permissions = array();
        // Set default permissions
        $permissions['IsAccess'] = FALSE;
        $permissions['IsOwner'] = FALSE;
        $permissions['IsCreator'] = FALSE;
        $permissions['IsMember'] = FALSE;
        $permissions['IsActiveMember'] = FALSE;
        $permissions['IsFriend'] = FALSE;
        $permissions['access'] = FALSE;
        $permissions['IsAdmin'] = FALSE;
        $permissions['IsInvited'] = FALSE; //User recieve JOIN request
        $permissions['IsInviteSent'] = FALSE; //User sent request to join group
        $permissions['IsExpert'] = 0;
        $permissions['CanPostOnWall'] = 0;
        $permissions['CanComment'] = 0;
        $permissions['CanCreateKnowledgeBase'] = 0;
        $permissions['DirectGroupMember'] = FALSE;
        $permissions['Type'] = '';
        $permissions['IsPublic'] = 1;

        $permissions['IsBlocked'] = check_blocked_user($user_id, 1, $group_id);
        if (!$permissions['IsBlocked']) {
            $group_details = $CI->group_model->details($group_id, $user_id);
            $permissions['Type'] = $group_details['Type'];
            $permissions['IsPublic'] = $group_details['IsPublic'];
            $permissions['GroupName'] = $group_details['GroupName'];
            if (!empty($group_details)) {
                if ($group_details['StatusID'] == 2) {
                    if ($with_details) {
                        $permissions['Details'] = $group_details;
                    }

                    if ($group_details['IsPublic'] != 2) { // public or closed
                        $permissions['IsAccess'] = TRUE;
                        if ($group_details['IsPublic'] == 1) {
                            $permissions['IsFriend'] = TRUE;
                        }
                    }

                    $membership = $CI->group_model->check_membership($group_id, $user_id, TRUE);
                    if (!empty($membership) && ($membership['StatusID'] == '1' || $membership['StatusID'] == '2' || $membership['StatusID'] == '18')) {
                        $permissions['IsAccess'] = TRUE;
                        $permissions['IsMember'] = TRUE;
                        if ($membership['StatusID'] == '2') {
                            $permissions['IsFriend'] = $membership['CanPostOnWall'];
                            $permissions['CanPostOnWall'] = $membership['CanPostOnWall'];
                            $permissions['IsExpert'] = $membership['IsExpert'];
                            $permissions['CanComment'] = $membership['CanComment'];
                            $permissions['IsActiveMember'] = TRUE;
                            $permissions['CanCreateKnowledgeBase'] = $membership['CanCreateKnowledgeBase'];
                        }

                        if ($membership['StatusID'] == 1) { // check if member is invited
                            $permissions['IsInvited'] = $membership['StatusID'];
                            $permissions['access'] = 1;
                        }
                        if ($membership['StatusID'] == 18) { // check if member request to join this group
                            $permissions['IsInviteSent'] = TRUE;
                        }
                        if ($membership['ModuleRoleID'] == 4) { // Check IF USER IS Creator
                            $permissions['IsCreator'] = TRUE;
                        }
                        if ($membership['ModuleRoleID'] != 6) {
                            $permissions['IsOwner'] = TRUE;
                            $permissions['IsAdmin'] = TRUE;
                            $permissions['IsFriend'] = TRUE;
                        }

                        if ($membership['ModuleID'] == 3 && $user_id == $membership['ModuleEntityID']) {
                            $permissions['DirectGroupMember'] = TRUE;
                        }
                    }
                }
            }
        }
        return $permissions;
    }
}

/**
 * [is_entity_exists description]
 * @param  [type]  $module_id        [Module ID]
 * @param  [type]  $module_entity_id [Module Entity ID]
 * @param  boolean $status_check     [status check]
 * @return boolean                   [true/false]
 */
if (!function_exists('is_entity_exists')) {

    function is_entity_exists($module_id, $module_entity_id, $status_check = TRUE)
    {
        $CI = &get_instance();
        if ($module_id == 3) {
            $table = USERS;
            if ($status_check) {
                $conditions = array('StatusID' => '2', 'UserID' => $module_entity_id);
            } else {
                $conditions = array('UserID' => $module_entity_id);
            }
        }
        if ($module_id == 1) {
            $table = GROUPS;
            $conditions = array('StatusID' => '2', 'GroupID' => $module_entity_id);
        }
        if ($module_id == 19) {
            $table = ACTIVITY;
            $conditions = array('StatusID' => '2', 'ActivityID' => $module_entity_id);
        }
        $result = $CI->db->get_where($table, $conditions);
        $return_flag = false;
        if ($result->num_rows() > 0) {
            $return_flag = true;
        }
        return $return_flag;
    }
}

/**
 * [get_album_id Used to get album id based on given parameter]
 * @param  [int] $user_id         [User ID]
 * @param  [string] $album_name   [Album Name]
 * @param  [int] $module_id       [Module ID]
 * @param  [int] $module_entity_id [Module Entity ID]
 * @return [int]                  [Album ID]
 */
if (!function_exists('get_album_id')) {

    function get_album_id($user_id, $album_name, $module_id, $module_entity_id)
    {
        $CI = &get_instance();
        $CI->db->select('AlbumID, ActivityID, AlbumGUID');
        $CI->db->where('AlbumName', $album_name);
        $CI->db->where('ModuleID', $module_id);
        $CI->db->where('ModuleEntityID', $module_entity_id);
        $CI->db->limit('1');
        $result = $CI->db->get(ALBUMS);
        if ($result->num_rows()) {
            if (empty($result->row()->ActivityID)) {
                $param = array('AlbumGUID' => $result->row()->AlbumGUID);
                $CI->load->model('activity/activity_model');
                $activity_id = $CI->activity_model->addActivity(3, $user_id, 13, $user_id, 0, '', 1, '', $param);
                $CI->load->model('album/album_model');
                $CI->album_model->update_album_activity_id($result->row()->AlbumGUID, $activity_id);
            }
            return $result->row()->AlbumID;
        } else {
            $albumType = (isset($album_name) && $album_name == DEFAULT_FILE_ALBUM) ? 'DOCUMENT' : 'PHOTO';
            $data = array('AlbumGUID' => get_guid(), 'AlbumName' => $album_name, 'UserID' => $user_id, 'AlbumType' => $albumType, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'Description' => '', 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id);
            if (strtolower($album_name) == DEFAULT_WALL_ALBUM) {
            }
            $CI->db->insert(ALBUMS, $data);
            $album_id = $CI->db->insert_id();

            $param = array('AlbumGUID' => $data['AlbumGUID']);
            $CI->load->model('activity/activity_model');
            $activity_id = $CI->activity_model->addActivity(3, $user_id, 13, $user_id, 0, '', 1, '', $param);

            $CI->load->model('album/album_model');
            $CI->album_model->update_album_activity_id($data['AlbumGUID'], $activity_id);
            return $album_id;
        }
    }
}

if (!function_exists('get_album_guid')) {

    function get_album_guid($user_id, $album_name)
    {
        $CI = &get_instance();
        $CI->db->select('AlbumGUID');
        $CI->db->where('AlbumName', $album_name);
        $CI->db->where('UserID', $user_id);
        $CI->db->limit('1');
        $result = $CI->db->get(ALBUMS);
        if ($result->num_rows()) {
            return $result->row()->AlbumGUID;
        }
    }
}

/**
 * [create_default_album Used to create default album]
 * @param  [int] $user_id         [User ID]
 * @param  [int] $module_id       [Module ID]
 * @param  [int] $module_entity_id [Module Entity ID]
 */
if (!function_exists('create_default_album')) {

    function create_default_album($user_id, $module_id, $module_entity_id, $activity_log_data = array())
    {
        get_album_id($user_id, DEFAULT_PROFILE_ALBUM, $module_id, $module_entity_id);
        get_album_id($user_id, DEFAULT_WALL_ALBUM, $module_id, $module_entity_id);
        if ($module_id != 34) {
            get_album_id($user_id, DEFAULT_PROFILECOVER_ALBUM, $module_id, $module_entity_id);
        }

        if (!isset($activity_log_data['is_add_log']) || !$activity_log_data['is_add_log']) {
            return;
        }

        $CI = &get_instance();
        $CI->load->model('log/user_activity_log_score_model');
        $score = $CI->user_activity_log_score_model->get_score_for_activity($activity_log_data['activity_type_id']);
        // Save user activity Log 
        $userActivityLog = array(
            'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id, 'UserID' => $user_id, 'ActivityTypeID' => $activity_log_data['activity_type_id'],
            'ActivityID' => 0, 'ActivityDate' => get_current_date('%Y-%m-%d'), 'PostAsModuleID' => 3, 'PostAsModuleEntityID' => $user_id, 'Score' => $score
        );
        $CI->user_activity_log_score_model->add_activity_log($userActivityLog);
    }
}

/**
 * [blocked_users get the blog user list]
 * @param  [int] $user_id [user id]
 * @return [array]        [blog user list]
 */
if (!function_exists('blocked_users')) {

    function blocked_users($user_id)
    {
        $user = array();
        $CI = &get_instance();
        $CI->db->where('UserID', $user_id);
        $CI->db->or_where('EntityID', $user_id);
        $query = $CI->db->get(BLOCKUSER);
        if ($query->num_rows()) {
            foreach ($query->result() as $blocked_user) {
                if ($blocked_user->ModuleEntityID == $user_id) {
                    $user[] = $blocked_user->EntityID;
                } else {
                    $user[] = $blocked_user->ModuleEntityID;
                }
            }
        }
        return $user;
    }
}

if (!function_exists('get_entity_view_count')) {

    function get_entity_view_count($entity_id, $entity_type)
    {
        if (!empty($entity_id) && !empty($entity_type)) {
            $CI = &get_instance();
            $CI->db->select('UserID');
            $CI->db->where(array('EntityID' => $entity_id, 'EntityType' => $entity_type));
            $result = $CI->db->get(ENTITYVIEW);
            return $result->num_rows();
        } else {
            return FALSE;
        }
    }
}

/**
 * Remove Setting Cache when update details of IP, Configuration and smtp setting
 * @param string $CacheFileName
 */
if (!function_exists('deleteCacheData')) {

    function deleteCacheData($CacheFileName)
    {
        $CI = &get_instance();
        $CI->load->driver('cache');
        if ($CacheFileName) {
            @$CI->cache->file->delete($CacheFileName);
        }
        return true;
    }
}

/**
 * [getClientIP : Get Client IP hidden behind proxies]
 * @return [string] IP
 */
if (!function_exists('getClientIP')) {

    function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}

/**
 * [getUserRolesData : Get logged in user roles]
 * @return array
 */
if (!function_exists('getUserRightsData')) {

    function getUserRightsData($DeviceType, $UserID = '')
    {
        if ($DeviceType == '1') {
            $CI = &get_instance();
            $arr = $CI->session->userdata('UserRights');
            if (isset($arr) && !empty($arr)) {
                return $arr;
            } else {
                if (!empty($UserID)) {
                    $CI->load->model(array('admin/roles_model'));
                    $result = $CI->roles_model->getUserRightsByUserId($UserID);
                    $CI->session->set_userdata('UserRights', $result);
                    return $result;
                } else {
                    return array();
                }
            }
        } else {
            return array();
        }
    }
}

/**
 * [getRightsId : Get rights id by key]
 * @return array
 */
if (!function_exists('getRightsId')) {

    function getRightsId($RightsKey)
    {
        $CI = &get_instance();
        $rightsKeyValArr = $CI->config->item("rightsKeyValArr");

        if (isset($rightsKeyValArr[$RightsKey])) {
            return $rightsKeyValArr[$RightsKey];
        } else {
            return 0;
        }
    }
}

/**
 * geneare random code string
 * @return string
 */
if (!function_exists('generateRandomCode')) {

    function generateRandomCode($l = 8)
    {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $code = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $l; $i++) {
            $n = rand(0, $alphaLength);
            $code[] = $alphabet[$n];
        }
        return implode($code); //turn the array into a string
    }
}

/**
 * [getRightsId : Get rights id by key]
 * @return array
 */
if (!function_exists('getUserDeviceName')) {

    function getUserDeviceName()
    {
        $CI = &get_instance();
        $CI->load->library('MobileDetect');
        $detect = new MobileDetect();
        if ($detect->isMobile()) {
            if ($detect->isiPhone()) {
                $deviceType = "IPhone";
            } else if ($detect->isiPad()) {
                $deviceType = "Ipad";
            } else if ($detect->isTablet() && $detect->isAndroidOS()) {
                $deviceType = "AndroidTablet";
            } else if ($detect->isAndroidOS()) {
                $deviceType = "AndroidPhone";
            } else {
                $deviceType = "OtherMobileDevice";
            }
        } else {
            $deviceType = "Native";
        }
        return $deviceType;
    }
}

if (!function_exists('is_valid_youtube_url')) {

    function is_valid_youtube_url($url)
    {
        $rx = '~
        ^(?:https?://)?                           # Optional protocol
        (?:www[.])?                              # Optional sub-domain
        (?:m[.])?                              # Optional sub-domain
        (?:youtube[.]com/watch[?]v=|youtu[.]be/) # Mandatory domain name (w/ query string in .com)
        ([^&]{11})                               # Video id of 11 characters as capture group 1
            ~x';

        $has_match = preg_match($rx, $url, $matches);
        return $has_match;
    }
}

/* ---------------------------------------------------------------
  | @Method       : get media extension
  | @param        : media_guid
  | @Output       : int/bool
  --------------------------------------------------------------- */
if (!function_exists('get_media_type')) {

    function get_media_type($media_guid)
    {
        $CI = &get_instance();
        $CI->db->select('MediaID,MediaTypeID');
        $CI->db->from(MEDIA . ' AS M');
        $CI->db->join(MEDIAEXTENSIONS . ' AS ME', 'ME.MediaExtensionID=M.MediaExtensionID');
        $res = $CI->db->where('M.MediaGUID', $media_guid)->limit(1)->get()->row_array();
        return $res['MediaTypeID'];
    }
}

if (!function_exists('get_entity_users')) {

    function get_entity_users($ModuleID, $ModuleEntityID)
    {
        $select = "UserID";
        switch ($ModuleID) {
            case '1':
                $entity_id = "GroupID";
                $status_field = 'StatusID';
                $status = '2';
                $table = GROUPMEMBERS;
                break;
            case '3':
                $entity_id = "FriendID";
                $status_field = 'Status';
                $status = '1';
                $table = FRIENDS;
                break;
            case '14':
                $entity_id = "EventID";
                $status_field = 'Presence';
                $status = 'ATTENDING';
                $table = EVENTUSERS;
                break;
            case '18':
                $entity_id = "PageID";
                $status_field = 'StatusID';
                $status = '2';
                $table = PAGEMEMBERS;
                break;

            default:
                return array();
                break;
        }
        $CI = &get_instance();
        $CI->db->select($select);
        $CI->db->from($table);
        $CI->db->where($status_field, $status);
        $CI->db->where($entity_id, $ModuleEntityID);
        $query = $CI->db->get();
        $data = array();
        if ($query->num_rows()) {
            foreach ($query->result() as $result) {
                $data[] = $result->$select;
            }
        }
        return $data;
    }
}

if (!function_exists('get_entity_admin')) {

    function get_entity_admin($ModuleID, $ModuleEntityID)
    {
        switch ($ModuleID) {
            case '1':
                $select = "CreatedBy";
                $entity_id = "GroupID";
                $table = GROUPS;
                break;
            case '3':
                $select = "UserID";
                $entity_id = "UserID";
                $table = USERS;
                break;
            case '14':
                $select = "CreatedBy";
                $entity_id = "EventID";
                $table = EVENTS;
                break;
            case '18':
                $select = "UserID";
                $entity_id = "PageID";
                $table = PAGES;
                break;

            default:
                return array();
                break;
        }
        $CI = &get_instance();
        $CI->db->select($select);
        $CI->db->from($table);
        $CI->db->where($entity_id, $ModuleEntityID);
        $query = $CI->db->get();
        $data = array();
        if ($query->num_rows()) {
            foreach ($query->result() as $result) {
                $data[] = $result->$select;
            }
        }
        if ($ModuleID == 18) {
            $CI->db->select('UserID');
            $CI->db->from(PAGEMEMBERS);
            $CI->db->where('PageID', $ModuleEntityID);
            $CI->db->where('ModuleRoleID', '8');
            $CI->db->where('StatusID', '2');
            $admin_q = $CI->db->get();
            if ($admin_q->num_rows()) {
                foreach ($admin_q->result() as $admin_d) {
                    $data[] = $admin_d->UserID;
                }
            }
        }
        return $data;
    }
}

if (!function_exists('get_user_relation')) {

    function get_user_relation($current_user_id, $visitor_user_id)
    {
        $data = array();
        $data[] = 'everyone';
        $friend_list = array();
        $CI = &get_instance();
        $CI->load->model('users/user_model');
        if ($current_user_id == $visitor_user_id) {
            $data[] = 'self';
        }
        /* if (CACHE_ENABLE && !empty($current_user_id)) {
            $temp_data = $CI->cache->get('user_friends_' . $current_user_id);
            if (!empty($temp_data)) {
                $friend_list = explode(',', $temp_data);
            }
        }
        if (empty($friend_list) && !empty($current_user_id)) {

            $temp_data = $CI->user_model->gerFriendsFollowersList($current_user_id, true, 1);
            if (!empty($temp_data['Friends'])) {
                $friend_list = $temp_data['Friends'];
            }
        }
        if (in_array($visitor_user_id, $friend_list)) {
            $data[] = 'network';
            $data[] = 'friend';
        } else {
            $visitor_friend_list = array();
            if (!empty($friend_list)) {
                $temp_data_visitor = $CI->user_model->gerFriendsFollowersList($visitor_user_id, true, 1);
                if (!empty($temp_data_visitor['Friends'])) {
                    $visitor_friend_list = $temp_data_visitor['Friends'];
                    if (!empty(array_intersect($visitor_friend_list, $friend_list))) {
                        $data[] = 'network';
                    }
                }
            }
        }
        */
        return $data;
    }
}

/**
 * [checkValueKeyExistInArray : For check value and key exist in multidimentional array]
 * @param array $dataArr
 * @param string $key
 * @param string $val
 * @return array
 */
if (!function_exists('checkValueKeyExistInArray')) {

    function checkValueKeyExistInArray($array, $key, $val)
    {
        foreach ($array as $item) {
            if (isset($item[$key]) && $item[$key] == $val) {
                return true;
            }
        }
        return false;
    }
}

/**
 * [set_user_language : Used to save selected language option for user.]
 * @param string $user_guid
 * @param string $lang
 */
if (!function_exists('set_user_language')) {

    function set_user_language($user_guid, $lang)
    {
        $CI = &get_instance();
        $CI->db->set('Language', $lang);
        $CI->db->where('UserGUID', $user_guid);
        $CI->db->update(USERS);
    }
}

if (!function_exists('set_video_autoplay')) {

    function set_video_autoplay($user_guid, $autoplay)
    {
        $user_id = get_detail_by_guid($user_guid, 3);
        $CI = &get_instance();
        $CI->db->set('VideoAutoplay', $autoplay);
        $CI->db->where('UserID', $user_id);
        $CI->db->update(USERDETAILS);
    }
}

if (!function_exists('html_substr')) {

    function html_substr($text, $length = 100, $ending = '...', $exact = true, $considerHtml = true)
    {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }

            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

            $total_length = strlen($ending);
            $open_tags = array();
            $truncate = '';

            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it’s an “empty element” with or without xhtml-conform closing slash (f.e.)
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) { // do nothing
                        // if tag is a closing tag (f.e.)
                    } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                        // if tag is an opening tag (f.e. )
                    } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    // add html-tag to $truncate’d text
                    $truncate .= $line_matchings[1];
                }

                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length + $content_length > $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }

                // if the maximum length is reached, get off the loop
                if ($total_length >= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }

        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }

        // add the defined ending to the text
        $truncate .= $ending;

        if ($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '';
            }
        }
        return $truncate;
    }
}

if (!function_exists('array_flatten')) {

    function array_flatten($array)
    {
        if (!is_array($array)) {
            return FALSE;
        }
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, array_flatten($value));
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}

if (!function_exists('get_media_thumb')) {

    function get_media_thumb($ImageName)
    {
        $image_arr = explode(".", $ImageName);
        $extension = end($image_arr);
        $extension = strtolower($extension);
        if (!in_array($extension, array('jpg', 'jpeg', 'png', 'bmp', 'gif'))) {
            $ImageName = str_replace('.' . $extension, '.jpg', $ImageName);
        }
        return $ImageName;
    }
}

if (!function_exists('percentile')) {

    function percentile($array)
    {
        arsort($array);
        $i = 0;
        $total = count($array);
        $percentiles = array();
        $previousValue = -1;
        $previousPercentile = -1;
        foreach ($array as $key => $value) {
            if ($previousValue == $value) {
                $percentile = $previousPercentile;
            } else {
                $percentile = 100 - $i * 100 / $total;
                $previousPercentile = $percentile;
            }
            $percentiles[$key] = $percentile;
            $previousValue = $value;
            $i++;
        }
        return $percentiles;
    }
}

/**
 * [version_control description]
 * @param  [type]  $v1         [description]
 * @return [type]              [description]
 */
if (!function_exists('version_control')) {
    function version_control($onlyVersion = false)
    {
        $v1 = "5.6";
        if ($onlyVersion) {
            return "?v=$v1";
        }
        echo "?v=$v1";
    }
}

if (!function_exists('notify_node')) {
    function notify_node($method, $data)
    {
        return;
        $CI = &get_instance();
        if (!empty($CI->DeviceTypeID) && $CI->DeviceTypeID == 1) {
            $CI->load->library('Node');
            $node = new node(array("route" => $method, "postData" => $data));
        }
    }
}

/**
 * [get_cover_image_state Used to get the cover image state]
 * @param  [int] $user_id          [Logged in User ID]
 * @param  [int] $module_entity_id [Module Entity ID]
 * @param  [int] $module_id        [Module ID]
 * @return [int]                   [Comer Image status]
 */
if (!function_exists('get_cover_image_state')) {

    function get_cover_image_state($user_id, $module_entity_id, $module_id)
    {
        if (!empty($module_entity_id)) {
            $CI = &get_instance();
            $CI->db->select('Status');
            $CI->db->where('UserID', $user_id);
            $CI->db->where('ModuleID', $module_id);
            $CI->db->where('ModuleEntityID', $module_entity_id);
            $CI->db->limit('1');
            $query = $CI->db->get(COVERIMAGESTATE);
            if ($query->num_rows()) {
                $result = $query->row_array();
                return $result['Status'];
            }
        }
        return 1;
    }
}

if (!function_exists('get_data_new')) {

    function get_data_new($InputArray)
    {

        $field = (isset($InputArray['field']) && $InputArray['field'] != '') ? $InputArray['field'] : '';
        $table = (isset($InputArray['table']) && $InputArray['table'] != '') ? $InputArray['table'] : '';
        $where = !empty($InputArray['where']) ? $InputArray['where'] : '';
        $limit = (isset($InputArray['limit']) && $InputArray['limit'] != '') ? $InputArray['limit'] : PAGE_NO;
        $offset = (isset($InputArray['offset']) && $InputArray['offset'] != '') ? $InputArray['offset'] : '0';
        $orderBy = !empty($InputArray['orderBy']) ? $InputArray['orderBy'] : '';
        $like = !empty($InputArray['like']) ? $InputArray['like'] : '';

        $CI = &get_instance();
        $CI->db->select($field);
        $CI->db->from($table);

        /* where */
        if ($where) {
            $CI->db->where($where);
        }

        /* like */
        if ($like) {
            $CI->db->like($like);
        }

        /* orderby */
        if ($orderBy) {
            $CI->db->order_by($orderBy);
        }

        /* limit */
        $CI->db->limit($limit, $offset);

        $sql = $CI->db->get();
        if ($sql->num_rows()) {
            $result = $sql->result_array();
            if ($limit == 1) {
                return $result[0];
            } else {
                return $result;
            }
        } else {
            return false;
        }
    }
}

if (!function_exists('isMultiArray')) {
    function isMultiArray($a)
    {
        foreach ($a as $v) {
            if (is_array($v)) {
                return TRUE;
            }
        }
        return FALSE;
    }
}

/**
 * [get_pagination_offset Used to calculate pagination offset]
 * @param  [int] $PageNo [current page number]
 * @param  [int] $Limit  [number of records]
 * @return [int]         [pagination offset]
 */
if (!function_exists('get_pagination_offset')) {
    function get_pagination_offset($PageNo, $Limit)
    {
        if (empty($PageNo)) {
            $PageNo = 1;
        }
        $offset = ($PageNo - 1) * $Limit;
        return $offset;
    }
}


if (!function_exists('get_entity_list')) {

    function get_entity_list($user_id, $module_id, $module_entity_id)
    {
        $ci = &get_instance();
        $entity = array();
        $ci->db->select('P.PageGUID as ModuleEntityGUID,P.Title as Name,  if(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture, "18" as ModuleID', false);
        $ci->db->from(PAGES . ' P');
        $ci->db->join(CATEGORYMASTER . ' CM', 'CM.CategoryID=P.CategoryID', 'left');
        $ci->db->where('P.UserID', $user_id);
        $ci->db->where('P.StatusID', 2);

        if ($module_id == 18) {
            $ci->db->where('PageID!=' . $module_entity_id, NULL, FALSE);
        }
        $query = $ci->db->get();

        if ($query->num_rows()) {
            $page_list = $query->result_array();
        }

        $ci->db->select('UserGUID as ModuleEntityGUID,CONCAT(FirstName," ",LastName) as Name, IF(ProfilePicture="","",ProfilePicture) as ProfilePicture, "3" as ModuleID', false);
        $ci->db->from(USERS);
        $ci->db->where('UserID', $user_id);
        $ci->db->where("(SELECT RatingID FROM " . RATINGS . " WHERE UserID='" . $user_id . "' AND ModuleID='" . $module_id . "' AND ModuleEntityID='" . $module_entity_id . "' AND PostAsModuleID is NULL AND PostAsModuleEntityID is NULL) is NULL", NULL, FALSE);
        $query = $ci->db->get();
        if ($query->num_rows()) {
            $user_detail = $query->row_array();
        }

        if (isset($page_list)) {
            $entity = $page_list;
        }
        if (isset($user_detail)) {
            $entity[] = $user_detail;
        }
        return $entity;
    }
}

/**
 *
 * @param   date_format
 * @return  Current UTC Date
 */
if (!function_exists('add_days_with_date')) {

    function add_days_with_date($date, $days, $date_format)
    {
        $CI = &get_instance();
        $CI->load->helper('date');
        $date = strtotime("+" . $days . " days", strtotime($date));
        return mdate($date_format, $date);
    }
}

if (!function_exists('get_short_url')) {

    function get_short_url($link) {
        $CI = &get_instance();
        $CI->load->library('bitly');
        $params = array();       
        $params['long_url'] = $link;
        $params['domain'] = 'bit.ly';
        $results = $CI->bitly->bitly_post('shorten', $params);
        if(isset($results['link'])) {
            return $results['link'];
        } else {
            log_message('error',json_encode($results));
            return $link;
        }
    }
}

/**
 * [initiate_worker_job Used to initiate job workoer in background for given method]
 * @param  [type] $method [Method name]
 * @param  [type] $data   [Associate array of parameter]
 */
if (!function_exists('initiate_worker_job')) {

    function initiate_worker_job($method, $data = array(), $exchange_name = '', $que_name = ENVIRONMENT)
    {
        if ($method) {
            try {
                if (JOBSERVER == "Rabbitmq") {
                    if($que_name == 'notification') {
                        $que_name = 'social_notification';
                    }
                    $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
                    $channel = $connection->channel();
                    $push_data = json_encode(array('method' => $method, 'data' => $data));
                    $message = new AMQPMessage($push_data, array('delivery_mode' => 2, 'content_type' => 'application/json')); # make message persistent as 2
                    $channel->basic_publish($message, $exchange_name, $que_name);
                    $channel->close();
                    $connection->close();
                }
                if (/* function_exists("gearman_version") && */extension_loaded('gearman') && JOBSERVER == "Gearman") {
                    $client = new GearmanClient();
                    $client->addServer();
                    $client->doBackground($method, json_encode($data));
                }
            } catch (Exception $e) {
                log_message("error", "Unable to connect to Job Server: {$e->getMessage()}");
            }
        }
    }
}

/**
 * @param $text
 * @return mixed
 */
if (!function_exists('link_it')) {

    function link_it($str, $attributes = array())
    {
        $str = str_replace("http://www", "www", $str);
        $str = str_replace("https://www", "www", $str);

        $attrs = '';
        foreach ($attributes as $attribute => $value) {
            $attrs .= " {$attribute}=\"{$value}\"";
        }
        $str = ' ' . $str;
        $str = preg_replace(
            '`([^"=\'>])((http|https|ftp)://[^\s<]+[^\s<\.)])`i',
            '$1<a href="$2"' . $attrs . '>$2</a>',
            $str
        );
        $str = preg_replace(
            '`([^"=\'>])((www).[^\s<]+[^\s<\.)])`i',
            '$1<a href="http://$2"' . $attrs . '>$2</a>',
            $str
        );
        $str = substr($str, 1);
        return $str;
    }
}


if (!function_exists('crypto_rand_secure')) {

    function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) {
            return $min;
        }
        // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }
}

if (!function_exists('getUniqueToken')) {

    function getUniqueToken($length)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[crypto_rand_secure(0, $max)];
        }

        return $token;
    }
}

if (!function_exists('getUniqueUrl')) {

    function getShortUrl($url, $check_first = 1)
    {
        $ci = &get_instance();
        if ($check_first) {
            $ci->db->select('TargetURL');
            $ci->db->from(SHARELINKS);
            $ci->db->where('LinkData', $url);
            $query = $ci->db->get();
            if ($query->num_rows()) {
                return $query->row()->TargetURL;
            }
        }

        $unique_token = getUniqueToken(6);
        $ci->db->select('LinkData');
        $ci->db->from(SHARELINKS);
        $ci->db->where('TargetURL', $unique_token);
        $query = $ci->db->get();
        if ($query->num_rows()) {
            getShortUrl($url, 0);
        } else {
            $short_url = site_url() . 'r/' . $unique_token;
            $ci->db->insert(SHARELINKS, array('LinkData' => $url, 'TargetURL' => $short_url, 'Created' => get_current_date('%Y-%m-%d %H:%i:%s')));
            return $short_url;
        }
    }
}

if (!function_exists('getLongUrl')) {

    function getLongUrl($url)
    {
        $ci = &get_instance();

        $ci->db->select('LinkData');
        $ci->db->from(SHARELINKS);
        $ci->db->where('TargetURL', $url);
        $query = $ci->db->get();
        if ($query->num_rows()) {
            return $query->row()->LinkData;
        }
    }
}

/**
 * safe_array_key
 * @return string
 */
if (!function_exists('safe_array_key')) {

    function safe_array_key($array, $key, $default = "")
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}

if (!function_exists('unique_random_string')) {

    function unique_random_string($table, $unique_colomn, $extra_where = [], $type = 'alnum', $len = 8)
    {
        $ci = &get_instance();
        while (1) {
            $random_string = random_string($type, $len);
            $ci->db->from($table);
            $ci->db->where($unique_colomn, $random_string);
            if (!empty($extra_where)) {
                $ci->db->where($extra_where);
            }
            $query = $ci->db->get();
            if ($query->num_rows() == 0) {
                break;
            }
        }
        return $random_string;
    }
}

// Encode as JSON - added by gautam
function json_encode_custom($data)
{
    $ci = &get_instance();
    if (!empty($ci->DeviceTypeID) && $ci->DeviceTypeID != 1) {
        $JSON = json_encode($data, JSON_PRESERVE_ZERO_FRACTION | JSON_NUMERIC_CHECK);
        $ci->db->where('DataID', $ci->InputID);
        $ci->db->update('jsondata', array("Output" => $JSON));
        return $JSON;
    } else {
        return json_encode($data);
    }
}

if (!function_exists('can_create_wiki')) {

    function can_create_wiki($user_id, $module_id, $module_entity_id)
    {
        $CI = &get_instance();
        if ($module_id == 34) {
            $CI->load->model('forum/forum_model');
            $permissions = $CI->forum_model->check_forum_category_permissions($user_id, $module_entity_id, FALSE);
            if ($permissions['IsAdmin']) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else if ($module_id == 1) {
            $CI->load->model('group/group_model');
            $permissions = $CI->group_model->get_group_permission($user_id);
            foreach ($permissions as $permission) {
                if ($permission['Value'] == 4) {
                    $result = $CI->group_model->check_can_create_knowledge_base($user_id, $module_entity_id);
                    if ($result)
                        return TRUE;
                    else
                        return FALSE;
                }
            }
            return FALSE;
        }
    }
}
/**
 * [get_analytics_id Used to get analytics id for given login session key]
 * @param  [string] $login_session_key [Login session key]
 * @param  [int] 	   [analytics id]
 */
if (!function_exists('get_analytics_id')) {

    function get_analytics_id($login_session_key, $user_id = 0, $select_field = "AnalyticLoginID", $response_type = 1)
    {
        $analytic_login_id = NULL;
        if ($login_session_key) {
            $ci = &get_instance();
            $ci->db->select($select_field);
            $ci->db->from(ANALYTICLOGINS);
            if ($user_id) {
                $ci->db->where('UserID', $user_id);
            } else {
                $ci->db->where('LoginSessionKey', $login_session_key);
            }
            $ci->db->order_by('AnalyticLoginID', 'DESC');
            $ci->db->limit(1);

            $query = $ci->db->get();
            if ($query->num_rows()) {
                $result = $query->row_array();
                switch ($response_type) {
                    case '2':
                        return $result;
                        break;
                    default:
                        return $result[$select_field];
                        break;
                }
            }
        }
        return $analytic_login_id;
    }
}

if (!function_exists('sortByOrder')) {

    function sortByOrder($a, $b)
    {
        return $a['Permissions']['IsMember'] - $b['Permissions']['IsMember'];
    }
}

if (!function_exists('percentage')) {

    function percentage($totalVal, $val, $cal_int = 'int')
    {
        $percentage = 0;
        if ($cal_int == 'int') {
            $percentage = (int) ($val * 100 / $totalVal);
        }

        return $percentage;
    }
}


if (!function_exists('time_calculate')) {

    function time_calculate($time_start = NULL)
    {
        return;

        if ($time_start == 'start') {
            $_POST['keep_debug_start_time'] = microtime_float();
        }

        if ($time_start == 'end') {
            $time_start = $_POST['keep_debug_start_time'];
        }

        if ($time_start == NULL) {
            return microtime_float();
        }
        $time_end = microtime_float();
        $time = $time_end - $time_start;

        return $time;
    }

    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }
}
if (!function_exists('random_username')) {

    function random_username($string)
    {
        $string = str_replace(' ', '-', $string);
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);

        $firstPart = $string;
        $nrRand = rand(0, 100);
        return trim($firstPart) . trim($nrRand);
    }
}

if (!function_exists('is_valid_url')) {
    function is_valid_url($url)
    {
        return (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://') ? $url : 'http://' . $url;
    }
}

if (!function_exists('getPresenceFromConfig')) {
    function getPresenceFromConfig($Item)
    {
        $Items = array('NOT_ATTENDING' => 'Not Attending', 'INVITED' => 'Invited', 'ARRIVED' => 'Arrived', 'MAY_BE' => 'May Be', 'ATTENDING' => 'Attending');
        if (!empty($Item)) {
            if (array_key_exists($Item, $Items)) {
                return $Items[$Item];
            }
        }
    }
}

function generateVersionLink($ReleaseVersion)
{
    if (!file_exists(ROOT_PATH . '/' . $ReleaseVersion)) {
        array_map('unlink', glob(ROOT_PATH . '/' . strstr($ReleaseVersion, '.', true) . '.*'));
        symlink(ROOT_PATH, $ReleaseVersion);
        $CI = &get_instance();
        $CI->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
        $CI->cache->clean();
    }
}

if (!function_exists('get_valid_url_str')) {
    function get_valid_url_str($url)
    {
        $url = preg_replace('~[^\\pL0-9_]+~u', '-', $url);
        $url = trim($url, "-");
        $url = iconv("utf-8", "us-ascii//TRANSLIT", $url);
        $url = strtolower($url);
        return preg_replace('~[^-a-z0-9_]+~', '', $url);
    }
}

if (!function_exists('convert_to_numeric_arr')) {
    function convert_to_numeric_arr($arr = [])
    {
        $temp_arr = $arr;
        $arr = [];
        foreach ($temp_arr as $item) {
            if (is_numeric($item)) {
                $arr[] = $item;
            }
        }
        return $arr;
    }
}

if (!function_exists('beliefmedia_ordinal')) {
    function beliefmedia_ordinal($cardinal)
    {
        $test_c = abs($cardinal) % 10;
        $extension = ((abs($cardinal) % 100 < 21 && abs($cardinal) % 100 > 4) ? 'th' : (($test_c < 4) ? ($test_c < 3) ? ($test_c < 2) ? ($test_c < 1) ? 'th' : 'st' : 'nd' : 'rd' : 'th'));
        return $cardinal . $extension;
    }
}

if (!function_exists('generate_password')) {
    function generate_password($password)
    {
        $options = [
            'cost' => 12
        ];
        $hash = password_hash($password, PASSWORD_BCRYPT, $options);
        return $hash;
    }
}

/**
 * Turn all URLs in clickable links.
 * 
 * @param string $value
 * @param array  $protocols  http/https, ftp, mail, twitter
 * @param array  $attributes
 * @return string
 */
if (!function_exists('linkify')) {
    function linkify($value, $protocols = array('http', 'https'), array $attributes = array())
    {
        $value = trim(strtr($value, array('&nbsp;' => ' ')));
        // Link attributes
        $attr = '';
        foreach ($attributes as $key => $val) {
            $attr .= ' ' . $key . '="' . htmlentities($val) . '"';
        }

        $links = array();

        // Extract existing links and tags
        $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) {
            return '<' . array_push($links, $match[1]) . '>';
        }, $value);

        // Extract text links for each protocol
        foreach ((array) $protocols as $protocol) {
            
            switch ($protocol) {
                case 'http':
                case 'https':
                    $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) {
                        $protocol_str = '';
                        if ($match[1]) {
                            $protocol = $match[1];
                            $protocol_str = $protocol.'://';
                        }
                        $link = $match[2] ?: $match[3];
                        if(is_valid_youtube_url($link)) {
                            $attr = "class='linkify'";
                        }                        
                        return '<' . array_push($links, "<a $attr href=\"$protocol://$link\">$protocol_str$link</a>") . '>';
                    }, $value);
                    break;
                case 'mail':
                    $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) {
                        return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>") . '>';
                    }, $value);
                    break;
                case 'twitter':
                    $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) {
                        return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . "\">{$match[0]}</a>") . '>';
                    }, $value);
                    break;
                default:
                    $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) {
                        return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\">$protocol://{$match[1]}</a>") . '>';
                    }, $value);
                    break;
            }
        }

        // Insert all link
        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) {
            return $links[$match[1] - 1];
        }, $value);
    }
}

/**
 * send msg91 ms
 * @param array $post_data
 * @return array
 */
if (!function_exists('send_msg91_sms')) {

    function send_msg91_sms($post_data = array())
    {
        $url = MSG91_API_BASE_URL . "api/sendhttp.php";
        $post_array = array(
            "route" => MSG91_ROUTE_ID,
            "sender" => MSG91_SENDER_ID,
            "authkey" => MSG91_AUTH_KEY,
            "country" => DEFAULT_PHONE_CODE,
            "DLT_TE_ID" => MSG91_DLT_TEMPLATE_ID,
            "mobiles" => $post_data['mobile'],
            "message" => isset($post_data['message']) ? $post_data['message'] : "",
            "encrypt" => "",
            "flash" => "",
            "unicode" => '1',
            "afterminutes" => "",
            "response" => "",
            "campaign" => "",
        );

        $query = http_build_query($post_array);
        $url = $url . "?" . $query;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            log_message('error', 'MSG91 Error: ' . $err);
            //return array("response" => $err);
        } else {
           // log_message('error', 'MSG91 Response: ' . $response);
            
        }
        return $response;
    }
}

if (!function_exists('get_module_settings')) {
    function get_module_settings($array = false)
    {
        if (realpath(SETTINGS_FILE)) {
            $fp = fopen(SETTINGS_FILE, 'r');
            $content = fread($fp, filesize(SETTINGS_FILE));
            fclose($fp);
            $content = json_decode($content, true);
            $arr = array();
            if ($content) {
                foreach ($content as $key => $val) {
                    $arr['m' . $key] = $val['Status'];
                }
            }
            if ($array) {
                return $arr;
            } else {
                return json_encode($arr);
            }
        }
    }
}

if (!function_exists('get_seo_friendly_activity_url')) {
    function get_seo_friendly_activity_url($activity)
    {
        $url = '';
        $default_title = 'title';
        $post_type = 'activity';
        $post_guid = $activity['ActivityGUID'];

        $title = $activity['PostTitle'];
        if(!$title) {
            $title = strip_tags($activity['PostContent']);
            $title = trim(substr($title,0,140), ' ');
        }
        if(!$title) {
            $title = $default_title;
        }

        $string = strtolower($title);

        //Make alphanumeric (removes all other characters)
        // $string = preg_replace("/[^a-z0-9_@~.:\s-]/", "", $string);
        $string = preg_replace("/[^a-z0-9\s-]/", "", $string);
        // $string = preg_replace("/[^a-z0-9]+/i", "", $string);

        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);

        $string = preg_replace("/\s+/", "", $string);
        $string = trim($string, "-");
        if (strlen($string) < 6) {
            $string = $activity['ActivityOwnerProfileURL'];
        }

        $url = "$post_type/$string/$post_guid";
        return $url;
    }
}
