<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once getcwd(). '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// TODO: Codeigniter has a Agent library with an is_mobile() method in it.
/**
 * Is this a mobile device?
 * @return int
 */
/*
  |--------------------------------------------------------------------------
  | Use To send chat push messages
  |--------------------------------------------------------------------------
 */
function SendPushMsg($entityID, $message, $Data = array()) {

    if (!isset($Data['content_available'])) {
        $Data['content_available'] = 1;
    }
    
    //log_message('error', 'Sender-'.$Data['UserID']." Reciever-".$Data['ToUserID']." NotificationTypeID-".$Data['NotificationTypeID']); 
    if($Data['NotificationTypeID'] !== 82 && trim($Data['UserID']) == trim($Data['ToUserID'])) {
        return;
    }
    
    $obj = &get_instance();
    $Query = $obj->db->query("SELECT DeviceToken, DeviceTypeID FROM `ActiveLogins` WHERE UserID='$entityID' AND DeviceToken!='' GROUP BY DeviceToken, DeviceTypeID ORDER BY ActiveLoginID DESC LIMIT 1 ");
    if ($Query->num_rows() > 0) {
        $notification_data = $Data;
        $obj->load->model(array('notification_model')); //, 'users/friend_model'
        if($notification_data['NotificationTypeID'] == 154) {
            $Data['TotalUnread'] = $obj->notification_model->get_unread_count($entityID);
            //log_message('error', 'Sender-'.$Data['UserID']." Reciever-".$Data['ToUserID']." TotalUnread-".$Data['TotalUnread']);
        }
        //$Data['IncomingRequestCount'] = $obj->friend_model->incoming_request_count($entityID, $entityID);
        
        $params = array();
        if(isset($notification_data['NotificationID']) && !empty($notification_data['NotificationID'])) {
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
                if ($key == 'P2' && $notification_data['NotificationTypeID'] == 46) {
                    $PageID = $param_details['ReferenceID'];
                }
                if ($key == 'P2' && $notification_data['NotificationTypeID'] == 48) {
                    $PageID = $param_details['ReferenceID'];
                }
                if ($key == 'P2' && $notification_data['NotificationTypeID'] == 61) {
                    $PageID = $param_details['ReferenceID'];
                }
                if ($key == 'P2' && ($notification_data['NotificationTypeID'] == 52 || $notification_data['NotificationTypeID'] == 53)) {
                    $PageID = $param_details['ReferenceID'];
                }
                if ($notification_data['NotificationTypeKey'] == 'review_marked_helpful' || $notification_data['NotificationTypeID'] == 63 || $notification_data['NotificationTypeID'] == 64) {
                    $PageID = $obj->db->select('ModuleEntityID')->from(RATINGS)->where('RatingID', $notification_data['RefrenceID'])->get()->row()->ModuleEntityID;
                }
                // Ends
            }
        }
        $Data['PushNotification'] = $obj->notification_model->get_notification_link_phone($notification_data['NotificationTypeID'], $notification_data['RefrenceID'], $notification_data['UserID'], $notification_data['ToUserID'], $PageID, $notification_data['NotificationTypeKey'], $entity_type, $notification_data['Params']);

        foreach ($Query->result_array() as $Notifications) {
            
            //log_message('error', 'token-'.$Notifications['DeviceToken']." userId-".$entityID." NotificationTypeID-".$notification_data['NotificationID'].' RefrenceID-'.$notification_data['RefrenceID']);
            if ($Notifications['DeviceTypeID'] == 2) {
                /* Iphone */
                push_notification_iphone($Notifications['DeviceToken'], $message, 0, $Data);
            } elseif ($Notifications['DeviceTypeID'] == 3) {
                /* android */
                push_notification_android(array($Notifications['DeviceToken']), $message, 0, $Data);
            }
        }
    }
}
/*
  |--------------------------------------------------------------------------
  | Push Notification for Iphone
  |--------------------------------------------------------------------------
 */

function push_notification_iphone($deviceToken = '', $message = '', $badge = 1, $Data=array()) {
    $ci =& get_instance();
    $ci->load->library('Push_notification');

    $device_ids = $deviceToken;


    //Prepare payload for both IOS and Android.
    $fields = array();

    $fields['notification']         = $Data;
    $fields['notification']['body'] = $message;
    $fields['notification']['title'] = "";
    $fields['notification']['sound'] = "default";
    $fields['notification']['badge'] = $badge;

    $fields['show_in_foreground'] = true;
    $fields['content-available'] = true;
    $fields['mutable-content'] = true;

   /* $fields['data']                 = $Data;
    $fields['data']['body']         = $Data['Summary'];
    $fields['data']['title']         = $message;
    $fields['data']['sound'] = "default";
    $fields['data']['badge'] = $badge;
    * 
    */

    $ci->push_notification->init($fields, $device_ids); 
}
/*
  |--------------------------------------------------------------------------
  | Push Notification for Iphone
  |--------------------------------------------------------------------------
 */

function push_notification_iphone_old($deviceToken = '', $message = '', $badge = 1, $Data) {

    // return TRUE;
    // exit;

    if ($badge == 0) {
        $badge = 1;
    }
    if ($deviceToken != '') {
        $pass = '123456';
        $body['aps'] = $Data;
        $body['aps']['alert'] = $message;
        $body['aps']['badge'] = (int) $badge;
        $body['aps']['content-available'] = $Data['content_available'];
        // if ($sound)//$body['aps']['sound'] = $sound;
        /* End of Configurable Items */
        $ctx = @stream_context_create();
        // assume the private key passphase was removed.
        stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);
        if (ENVIRONMENT == 'production') {
            @stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck-live.pem');
            $fp = @stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx); //For Live
        } else {
            @stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck-dev.pem');
            $fp = @stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx); //For Testing
        }

        if (!$fp) {
            return "Failed to connect $err $errstr";
        } else {
            try {

                //$obj = & get_instance();
                /* Save Log */
                $PushData = array(
                    'PushData' => json_encode($body, 1),
                    'DeviceTypeID' => '123',
                    'PushReturn' => '234',
                    'CreatedDate' => date("Y-m-d H:i:s"),
                );
                //addEdit('pushdata', $PushData);

                $payload = @json_encode($body, JSON_NUMERIC_CHECK);
                $msg = @chr(0) . @pack("n", 32) . @pack('H*', @str_replace(' ', '', $deviceToken)) . @pack("n", @strlen($payload)) . $payload;
                @fwrite($fp, $msg);
                @fclose($fp);
            } catch (Exception $e) {
                return 'Caught exception';
            }
        }
    }
}

/*
  |--------------------------------------------------------------------------
  | Push Notification for Android
  |--------------------------------------------------------------------------
 */

function push_notification_android($registatoin_ids = array(), $message = '', $badge = 1, $Data) {
    if ($badge == 0) {
        $badge = 1;
    }
    // prep the bundle
    $msg = array(
        'message' => $message,
        'title' => '',
        'subtitle' => '',
        'tickerText' => '',
        'vibrate' => 1,
        'sound' => 1,
        'largeIcon' => 'large_icon',
        'smallIcon' => 'small_icon',
        'badge' => (int) $badge,
    );

    $msg = array_merge($msg, $Data);

    //$obj = & get_instance();
    /* Save Log */
    $PushData = array(
        'PushData' => json_encode($msg, 1),
        'DeviceTypeID' => '123',
        'PushReturn' => '234',
        'CreatedDate' => date("Y-m-d H:i:s"),
    );
    //addEdit('pushdata', $PushData);

    $fields = array(
        'registration_ids' => $registatoin_ids,
        'data' => $msg,
    );
    $headers = array(
        'Authorization: key='.SERVER_API_KEY,
        'Content-Type: application/json',
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields, JSON_NUMERIC_CHECK));
    try {
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    } catch (Exception $e) {
        
    }
}

if (!function_exists('is_mobile')) {

    function is_mobile() {
        $mobile_browser = 0;

        $userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android)/i', $userAgent)) {
            $mobile_browser++;
        }

        $acceptingContentType = !empty($_SERVER['HTTP_AGENT']) ? strtolower($_SERVER['HTTP_ACCEPT']) : '';

        if ((strpos($acceptingContentType, 'application/vnd.wap.xhtml+xml') > 0) or ( (isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $mobile_browser++;
        }

        $mobile_ua = substr($userAgent, 0, 4);
        $mobile_agents = array('w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac', 'blaz',
            'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno', 'ipaq', 'java', 'jigs', 'kddi', 'keji',
            'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-', 'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp',
            'nec-', 'newt', 'noki', 'oper', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox', 'qwap', 'sage', 'sams',
            'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar', 'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb',
            't-mo', 'teli', 'tim-', 'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp', 'wapr',
            'webc', 'winw', 'winw', 'xda ', 'xda-');

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

    function aasort(&$array, $key) {
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

if (!function_exists('is_valid_user')) {

    function is_valid_user($User, $Type = 'UserGUID') {
        $CI = &get_instance();
        $CI->db->where($Type, $User);
        $CI->db->where_not_in('StatusID', array(3, 4));
        $CI->db->limit('1');
        $query = $CI->db->get(USERS);
        if ($query->num_rows()) {
            return true;
        } else {
            return false;
        }
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

    function get_full_path($type = 'profile_image', $user_unique_id = '', $name, $height = '', $width = '', $size = '') {
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

    function get_profile_cover($image_name) {
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

    function curl($url, $method = 'get', $header = NULL, $postdata = NULL, $includeheader = FALSE, $timeout = 60) {
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

    function ExecuteCurl($url, $jsondata = '', $post = '', $headerData = []) {
        $ch = curl_init();
        $headers = array('Accept: application/json', 'Content-Type: application/json', 'APPVERSION: v1');
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

        /* if (ENVIRONMENT == 'production' || ENVIRONMENT == 'demo') {
            $username = 'vcommonsocial';
            $password = 'EasyPassword';
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        }
         * 
         */

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        if ($post != '') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $post);
        }
        $result = curl_exec($ch);
        // var_dump($result);die;
        curl_close($ch);
        return $result;
    }

}

if (!function_exists('EscapeString')) {

    function EscapeString($string) {
        $string = trim($string);
        $CI = &get_instance();
        return $CI->db->escape_str($string);
    }

}

if (!function_exists('getRealIpAddr')) {

    function getRealIpAddr() {
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

    function valid_email($address) {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? FALSE : TRUE;
    }

}

if (!function_exists('create_menu')) {

    function create_menu($menu_array) {
        //print_r(getUserRightsData('1'));
        //go through each top level menu item
        foreach ($menu_array as $menu) {
            $data_active = '';
            if (isset($menu['menu_id']) && $menu['menu_id'] != '') {
                $data_active = $menu['menu_id'];
            }
            //echo "<pre>";print_r($menu);

            $RightsId = getRightsId($menu['menu_key']);
            $genearteMenu = false;
            $generate_link = true;
            //For show/hide menu if not have permission
            if ($menu['menu_key'] == "UsersTab" && (in_array(getRightsId('registered_user'), getUserRightsData('1')) || in_array(getRightsId('blocked_user'), getUserRightsData('1')) || in_array(getRightsId('deleted_user'), getUserRightsData('1')) || in_array(getRightsId('waiting_for_approval'), getUserRightsData('1')) || in_array(getRightsId('dummy_user_manager'), getUserRightsData('1')) || in_array(getRightsId('manage_announcement'), getUserRightsData('1')) || in_array(getRightsId('profile_question'), getUserRightsData('1')) || in_array(getRightsId('profile_question'), getUserRightsData('1')) || in_array(getRightsId('newsletter_subscriber'), getUserRightsData('1')) || in_array(getRightsId('subscriber_list'), getUserRightsData('1')))) {
                $genearteMenu = true;
            } else if ($menu['menu_key'] == "MediaTab" && (in_array(getRightsId('media_list'), getUserRightsData('1')) || in_array(getRightsId('media_abusemedia'), getUserRightsData('1')))) {
                $genearteMenu = true;
            } else if ($menu['menu_key'] == "AnalyticsTab" && (in_array(getRightsId('login_analytics'), getUserRightsData('1')) || in_array(getRightsId('signup_analytics'), getUserRightsData('1')) || in_array(getRightsId('media_analytics'), getUserRightsData('1')) || in_array(getRightsId('most_active_users'), getUserRightsData('1')) || in_array(getRightsId('google_analytics'), getUserRightsData('1')) || in_array(getRightsId('email_analytics'), getUserRightsData('1')))) {
                $genearteMenu = true;
            } else if ($menu['menu_key'] == "EmailSettingTab" && (in_array(getRightsId('smtp_settings'), getUserRightsData('1')) || in_array(getRightsId('smtp_emails'), getUserRightsData('1')))) {
                $genearteMenu = true;
            } else if ($menu['menu_key'] == "ToolsTab" && (in_array(getRightsId('analytics_tool'), getUserRightsData('1')) || in_array(getRightsId('support_request_listing'), getUserRightsData('1')) || in_array(getRightsId('list_roles'), getUserRightsData('1')) || in_array(getRightsId('list_role_users'), getUserRightsData('1')) || in_array(getRightsId('list_permissions'), getUserRightsData('1')))) {
                $genearteMenu = true;
            } else if ($menu['menu_key'] == "IpsTab" && (in_array(getRightsId('ips_admin'), getUserRightsData('1')) || in_array(getRightsId('ips_user'), getUserRightsData('1')))) {
                $genearteMenu = true;
            } else if ($menu['menu_key'] == "DashboardTab" && (in_array(getRightsId('activity_dashboard'), getUserRightsData('1')) )) {
                $genearteMenu = true;
            } else if ($menu['menu_key'] == "category" && (in_array(getRightsId('category_admin'), getUserRightsData('1')) )) {
                $genearteMenu = true;
            } else if ($RightsId != 0 && in_array($RightsId, getUserRightsData('1'))) {
                $genearteMenu = true;
            } else if ($RightsId == 0 && $menu['menu_key'] != "UsersTab" && $menu['menu_key'] != "MediaTab" && $menu['menu_key'] != "AnalyticsTab" && $menu['menu_key'] != "EmailSettingTab" && $menu['menu_key'] != "ToolsTab" && $menu['menu_key'] != "IpsTab" && $menu['menu_key'] != "DashboardTab" && $menu['menu_key'] != "category") {
                $genearteMenu = true;
            }

            if ($menu['menu_key'] == "UsersTab" && !(in_array(getRightsId('registered_user'), getUserRightsData('1')) || in_array(getRightsId('blocked_user'), getUserRightsData('1')) || in_array(getRightsId('deleted_user'), getUserRightsData('1')) || in_array(getRightsId('waiting_for_approval'), getUserRightsData('1')) || in_array(getRightsId('manage_announcement'), getUserRightsData('1')) || in_array(getRightsId('profile_question'), getUserRightsData('1')) || in_array(getRightsId('profile_question'), getUserRightsData('1')))) {
                //$generate_link = false;
            }
            //echo "<pre>".$menu['menu_key']." - ".$generate_link."</pre>";


            if (array_key_exists('children', $menu)) {
                if ($genearteMenu) {
                    if ($generate_link) {

                        echo "<li data-active='{$data_active}' class='dropdown'><a target='_self' href='{$menu['url']}'>{$menu['name']}{$menu['icon']}</a>";
                    } else {
                        echo "<li data-active='{$data_active}' class='dropdown'><a target='_self' href='#'>{$menu['name']}{$menu['icon']}</a>";
                    }
                }
            } else {
                if ($genearteMenu) {
                    if ($generate_link) {

                        echo "<li data-active='{$data_active}'><a target='_self' href='{$menu['url']}'>{$menu['name']}</a>";
                    } else {
                        echo "<li data-active='{$data_active}'><a target='_self' href='#'>{$menu['name']}</a>";
                    }
                }
            }
            //see if this menu has children
            if (array_key_exists('children', $menu) && $genearteMenu == true) {
                echo '<ul>';
                //echo the child menu
                create_menu($menu['children']);
                echo '</ul>';
            }
            echo '</li>';
        }
    }

}

/**
 * [get_age_range get the age range of Age Group]
 * @param  [int]     $age_group_id   [Age Group ID]
 * @return [array]                   [Age range of Age Group]
 */
if (!function_exists('get_age_range')) {

    function get_age_range($age_group_id) {
        $CI = &get_instance();
        $CI->db->select('ValueRangeFrom,ValueRangeTo');
        $CI->db->from(AGEGROUPS);
        $CI->db->where('AgeGroupID', $age_group_id);
        $query = $CI->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return array('ValueRangeFrom' => (date('Y') - $row->ValueRangeTo), 'ValueRangeTo' => (date('Y') - $row->ValueRangeFrom));
        }
        return array('ValueRangeFrom' => 0, 'ValueRangeTo' => 999);
    }

}

/**
 * [get_age_group_id get the Age Group ID]
 * @param  [int]     $User ID   [User ID]
 * @return [int]               [Age Group ID]
 */
if (!function_exists('get_age_group_id')) {

    function get_age_group_id($user_id) {
        $CI = &get_instance();
        $CI->db->select("DATE_FORMAT(FROM_DAYS(DATEDIFF('" . get_current_date('%Y-%m-%d') . "',UD.DOB)), '%Y')+0 as Age", false);
        $CI->db->from(USERDETAILS . ' UD');
        $CI->db->where('UD.UserID', $user_id);
        $user_details_query = $CI->db->get();
        $user_details_result = $user_details_query->row_array();
        $age_group_id = NULL;
        if (isset($user_details_result['Age'])) {
            $CI->db->select('AgeGroupID');
            $CI->db->from(AGEGROUPS);
            $CI->db->where($user_details_result['Age'] . ' BETWEEN ValueRangeFrom AND ValueRangeTo', NULL, FALSE);
            $age_query = $CI->db->get();
            if ($age_query->num_rows()) {
                $age_group_id = $age_query->row()->AgeGroupID;
            }
        }
        return $age_group_id;
    }

}

/**
 * Create GUID
 * @return string
 */
if (!function_exists('get_guid')) {

    function get_guid() {
        if (function_exists('com_create_guid')) {
            return strtolower(com_create_guid());
        } else {
            mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
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

    function get_entity_url($entity_id, $entity_type = "User", $is_url = 0) {
        $uri = '';
        $CI = &get_instance();
        if ($entity_type == "User" && !empty($entity_id)) {
            if (CACHE_ENABLE) {
                $userdata = $CI->cache->get('user_profile_' . $entity_id);
                if(!is_array($userdata)){ 
                    $userdata = '';
                }
                if (!empty($userdata)) {
                    $uri = isset($userdata['ProfileURL'])?$userdata['ProfileURL']:'';
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

        if (!$is_url) {
            $uri = site_url() . $uri;
        }
        return $uri;
    }

}

if (!function_exists('get_user_id_by_loginsessionkey')) {

    function get_user_id_by_loginsessionkey($login_session_key) {
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

    function get_detail_by_guid($entity_guid, $module_id = 0, $select_field = "", $response_type = 1) {
        if (!empty($entity_guid)) {
            $CI = &get_instance();
            $select_fields = ($select_field) ? $select_field : "*";
            $table_name = ACTIVITY;
            $condition = array("ActivityGUID" => $entity_guid);
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
                default:
                    $table_name = ACTIVITY;
                    $select_fields = ($select_field) ? $select_field : "ActivityID";
                    $condition = array("ActivityGUID" => $entity_guid);
                    break;
            }
            $CI->db->select($select_fields, FALSE);
            $CI->db->from($table_name);
            $CI->db->where($condition);
            $CI->db->limit('1');
            $query = $CI->db->get();
            /* if($module_id==3)
              {
              echo $CI->db->last_query();die;

             */
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

                switch ($response_type) {
                    case '2':
                        return $result;
                        break;
                    default:
                        return $result[$select_fields];
                        break;
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

    function get_detail_by_id($entity_id, $module_id = 0, $select_field = "", $response_type = 1) {
        if (!empty($entity_id) && !is_null($entity_id) && (is_string($entity_id) || is_int($entity_id))) {
            $CI = &get_instance();
            $table_name = ACTIVITY;
            $condition = array("ActivityID" => $entity_id);
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
                if (!empty($select_fields) && $select_fields != '*') {
                    if (CACHE_ENABLE) {
                        $user_file_data = $CI->cache->get('user_profile_' . $entity_id);
                        if(!is_array($user_file_data)){ 
                            $user_file_data = '';
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
                }
            } else if ($module_id == 0 || $module_id == '') {
                if (!empty($select_fields) && $select_fields != '*') {
                    if (CACHE_ENABLE) {

                        $cache_data = $CI->cache->get('activity_' . $entity_id);
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
                }
            } else if ($module_id == 18) {
                if (!empty($select_fields) && $select_fields != '*') {
                    if (CACHE_ENABLE) {

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
                }
            } else if ($module_id == 1) {
                if (!empty($select_fields) && $select_fields != '*') {
                    if (CACHE_ENABLE) {

                        $cache_data = $CI->cache->get('group_cache_' . $entity_id);
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
//log_message('error',1);
                            initiate_worker_job('group_cache', array('group_id' => $entity_id));
                        }
                    }
                }
            }
            if (empty($result)) {
                $query = array();
                $CI->db->select($select_fields, FALSE);
                $CI->db->from($table_name);
                $CI->db->where($condition);
                $CI->db->limit('1');
                $query = $CI->db->get();
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
            switch ($response_type) {
                case '2':
                    return $result;
                    break;
                default:
                    if ($select_fields == '*') {
                        return $result;
                    }

                    if (!empty($result)) {
                        return $result[$select_fields];
                    }
                    break;
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

    function get_guid_by_id($entity_id, $module_id = 0) {
        if (!empty($entity_id)) {
            $CI = &get_instance();
            $select_field = "*";
            $table_name = ACTIVITY;
            $condition = array("ActivityID" => $entity_id);
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

    function sendEmailAndSave($dataArr, $save_only = 0) {
        $CI = &get_instance();
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
            //var_dump($smtp_settings[$dataArr['EmailTypeID']]);
            if (isset($smtp_settings[$dataArr['EmailTypeID']])) {
                if ($smtp_settings[$dataArr['EmailTypeID']]['SmtpStatusID'] == 2) {
                    $smtpData = $smtp_settings[$dataArr['EmailTypeID']];
                } else {
                    $smtpData = $smtp_settings['default'];
                }

                //add additional heade in case reply_to or any other header info have to be added
                if (isset($dataArr['EntityGUID']) && !empty($dataArr['EntityGUID'])) {
                    $additionalHeader = array('reply_to' => $dataArr['EntityGUID'] . '-noreply@vinfotech.org');
                    //$smtpData['ReplyTo']=$dataArr['EntityGUID'].'-noreply@vinfotech.org';
                    //$Email = 'abhishekg@vinfotech.com';
                } else {
                    $additionalHeader = Null;
                }

                $FromEmail = $smtpData['FromEmail'];
                //  echo $save_only;die;
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
            $CI->db->select('Name');
            $CI->db->from(EMAILTYPES);
            $CI->db->where(array('EmailTypeID' => $dataArr['EmailTypeID']));
            $query = $CI->db->get();
            $typaArr = $query->row_array();

            $mandrillArr = array();
            $mandrillArr['mandrill_api_key'] = ($global_settings['mandrill_api_key']) ? $global_settings['mandrill_api_key'] : MANDRILL_API_KEY;
            $mandrillArr['mandrill_from_email'] = $FromEmail = ($global_settings['mandrill_from_email']) ? $global_settings['mandrill_from_email'] : MANDRILL_FROM_EMAIL;
            $mandrillArr['mandrill_from_name'] = ($global_settings['mandrill_from_name']) ? $global_settings['mandrill_from_name'] : MANDRILL_FROM_NAME;
            $mandrillArr['subject'] = $Subject;
            $mandrillArr['email_html'] = $email_html;
            $mandrillArr['user_email'] = $Email;
            $mandrillArr['user_name'] = '';
            $mandrillArr['EmailTypeID'] = $dataArr['EmailTypeID'];
            //$mandrillArr['EmailTypeID'] = $dataArr['EmailTypeID'];
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

/**
 * Function checkSMTPSettingViaSendEmail for check SMTP details is valid or not
 * @params array $dataArr
 * @return boolean
 */
if (!function_exists('checkSMTPSettingViaSendEmail')) {

    function checkSMTPSettingViaSendEmail($dataArr) {
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

    function sendMandrillEmails($dataArr) {
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
                    array(// add more sub-arrays for additional recipients
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

    function check_browser($full_details = 0) {
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
            $browser = array("browser" => $agent, "version" => $version, "platform" => $CI->agent->platform(), 'agent' => $CI->agent->agent);
            return $browser;
        }
        return $browser_id;
    }

}


/**
 *  @param $user_id
 *  @return TimeZone
 */
if (!function_exists('get_user_time_zone')) {

    function get_user_time_zone($user_id) {
        $CI = &get_instance();
        $time_zone = '';
        if (CACHE_ENABLE) {
            $userdata = $CI->cache->get('user_profile_' . $user_id);
            if(!is_array($userdata)){ 
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

    function get_current_date($date_format, $timediff = 0, $plus = 0, $time = 0) {
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
        //echo mdate($date_format,$now);
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

    function checkPermission($UserID, $ModuleID, $ModuleEntityID, $Action = "Sticky", $entity_module_id = 3, $entity_id = 0) {
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
                if ($permissions['IsAdmin'])
                    return true;
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

    function check_blocked_user($user_id, $module_id, $module_entity_id) {
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
        //echo $CI->db->last_query();
        if ($sql->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

}

/**
 * @Method : To check Event's permissions for logged in user(for internal use)
 * @params : UserID,ModuleEntityID
 * @Output : array
 */
if (!function_exists('checkEventPermissions')) {

    function checkEventPermissions($UserID, $ModuleEntityID) {
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

    function check_group_permissions($user_id, $group_id, $with_details = TRUE) {
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

    function is_entity_exists($module_id, $module_entity_id, $status_check = TRUE) {
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
        if ($result->num_rows()) {
            return true;
        } else {
            return false;
        }
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

    function get_album_id($user_id, $album_name, $module_id, $module_entity_id) {
        $CI = &get_instance();
        $CI->db->select('AlbumID, ActivityID, AlbumGUID');
        $CI->db->where('AlbumName', $album_name);
        //$CI->db->where('UserID', $user_id);
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
                //$data['AlbumType']='VIDEO';
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

    function get_album_guid($user_id, $album_name) {
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

    function create_default_album($user_id, $module_id, $module_entity_id, $activity_log_data = array()) {
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

    function blocked_users($user_id) {
        $user = array();
        $CI = &get_instance();
        $CI->db->where('UserID', $user_id);
        $CI->db->or_where('EntityID', $user_id);
        $query = $CI->db->get(BLOCKUSER);
        //echo $CI->db->last_query();
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

    function get_entity_view_count($entity_id, $entity_type) {
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

    function deleteCacheData($CacheFileName) {
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

    function getClientIP() {
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

    function getUserRightsData($DeviceType, $UserID = '') {
        if ($DeviceType == '1') {
            $CI = &get_instance();
            //echo "<pre>";print_r($CI->session->userdata('UserRights'));
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

    function getRightsId($RightsKey) {
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
 * [accessDeniedHtml : Get access denied area html]
 * @return array
 */
if (!function_exists('accessDeniedHtml')) {

    function accessDeniedHtml() {
        $html = '';
        $html .= '<aside class="error-wrapper">
            <div class="clearfix"></div>
            <section class="error-region">
                <aside class="error-div">
                    <div class="icondiv"><img src="' . ASSET_BASE_URL . '/admin/img/access_denied.jpg"/></div>
                    <aside class="msg">
                        <div class="fsize40">ACCESS DENIED</div>
                        <div class="fsize20">' . lang('permission_denied_page') . '</div>
                    </aside>
                </aside>
            </section>
        </aside>';
        return $html;
    }

}

/**
 * [getClientOS : Get Client OS name]
 * @return array
 */
if (!function_exists('getClientOS')) {

    function getClientOS() {

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $os_platform = "Unknown OS Platform";

        $os_array = array(
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile',
        );

        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
            }
        }
        return $os_platform;
    }

}

/**
 * geneare random code string
 * @return string
 */
if (!function_exists('generateRandomCode')) {

    function generateRandomCode($l = 8) {
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
 * Function for get language list
 * @return array
 */
if (!function_exists('getLanguageList')) {

    function getLanguageList() {
        $LanguageArr = array(
            'af' => 'Afrikaans',
            'sq' => 'Albanian',
            'ar' => 'Arabic',
            'az' => 'Azerbaijani',
            'eu' => 'Basque',
            'bn' => 'Bengali',
            'be' => 'Belarusian',
            'bg' => 'Bulgarian',
            'ca' => 'Catalan',
            'zh-cn' => 'Chinese Simplified',
            'zh-tw' => 'Chinese Traditional',
            'hr' => 'Croatian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'en' => 'English',
            'eo' => 'Esperanto',
            'et' => 'Estonian',
            'tl' => 'Filipino',
            'fi' => 'Finnish',
            'fr' => 'French',
            'gl' => 'Galician',
            'ka' => 'Georgian',
            'de' => 'German',
            'el' => 'Greek',
            'gu' => 'Gujarati',
            'ht' => 'Haitian Creole',
            'iw' => 'Hebrew',
            'hi' => 'Hindi',
            'hu' => 'Hungarian',
            'is' => 'Icelandic',
            'id' => 'Indonesian',
            'ga' => 'Irish',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'kn' => 'Kannada',
            'ko' => 'Korean',
            'la' => 'Latin',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'mk' => 'Macedonian',
            'ms' => 'Malay',
            'mt' => 'Maltese',
            'no' => 'Norwegian',
            'fa' => 'Persian',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sa' => 'Serbian',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'es' => 'Spanish',
            'sw' => 'Swahili',
            'sv' => 'Swedish',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'vi' => 'Vietnamese',
            'cy' => 'Welsh',
            'yi' => 'Yiddish',
        );
        return $LanguageArr;
    }

}

/**
 * [getRightsId : Get rights id by key]
 * @return array
 */
if (!function_exists('getUserDeviceName')) {

    function getUserDeviceName() {
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

    function is_valid_youtube_url($url) {
        $rx = '~
        ^(?:https?://)?              # Optional protocol
         (?:www\.)?                  # Optional subdomain
         (?:youtube\.com|youtu\.be)  # Mandatory domain name
         /watch\?v=([^&]+)           # URI with video id as capture group 1
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

    function get_media_type($media_guid) {
        $CI = &get_instance();
        $CI->db->select('MediaID,MediaTypeID');
        $CI->db->from(MEDIA . ' AS M');
        $CI->db->join(MEDIAEXTENSIONS . ' AS ME', 'ME.MediaExtensionID=M.MediaExtensionID');
        $res = $CI->db->where('M.MediaGUID', $media_guid)->limit(1)->get()->row_array();
        return $res['MediaTypeID'];
    }

}

if (!function_exists('get_entity_users')) {

    function get_entity_users($ModuleID, $ModuleEntityID) {
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

    function get_entity_admin($ModuleID, $ModuleEntityID) {
        $select = "UserID";
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

    function get_user_relation($current_user_id, $visitor_user_id) {
        $data = array();
        $data[] = 'everyone';
        $friend_list = array();
        $CI = &get_instance();
        $CI->load->model('users/user_model');
        if ($current_user_id == $visitor_user_id) {
            $data[] = 'self';
        }
        if (CACHE_ENABLE) {
            $temp_data = $CI->cache->get('user_friends_' . $current_user_id);
            if (!empty($temp_data)) {
                $friend_list = explode(',', $temp_data);
            }
        }
        if (empty($friend_list)) {

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
        return $data;
    }

}

/**
 * [downloadExcelFile : used to download Excel File]
 * @param array $dataArr
 * @return array
 */
if (!function_exists('downloadExcelFile')) {

    function downloadExcelFile($dataArr) {
        $CI = &get_instance();

        //load our new PHPExcel library
        $CI->load->library('excel');
        $letters = range('A', 'Z');

        /* Load Global settings */
        $global_settings = $CI->config->item("global_settings");
        $exportDate = date($global_settings['date_format'] . " " . $global_settings['time_format']);

        $headerArray = $dataArr['headerArray'];
        $sheetTitle = $dataArr['sheetTitle'];
        $fileName = $dataArr['fileName'];
        $folderPath = $dataArr['folderPath'];
        $inputData = $dataArr['inputData'];
        $ReportHeader = $dataArr['ReportHeader'];

        //activate worksheet number 1
        $CI->excel->setActiveSheetIndex(0);
        //name the worksheet
        $CI->excel->getActiveSheet()->setTitle($sheetTitle);

        // set xls header and style
        $cell_count = count($headerArray) - 1;
        $styleArray = array('font' => array('bold' => true, 'color' => array('rgb' => 'ffffff'), 'size' => 12));

        $col = 1;
        if ($ReportHeader) {
            //For first row header
            $CI->excel->getActiveSheet()->getStyle('A1:' . $letters[$cell_count] . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $CI->excel->getActiveSheet()->getStyle('A1:' . $letters[$cell_count] . '1')->getFill()->getStartColor()->setRGB('12456B');
            $CI->excel->getActiveSheet()->getStyle('A1:G20')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $CI->excel->setActiveSheetIndex(0)->mergeCells('A1:' . $letters[$cell_count] . '1');
            $CI->excel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
            $CI->excel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
            $CI->excel->getActiveSheet()->setCellValue('A1', $ReportHeader['ReportName'] . "(" . $ReportHeader['dateFilterText'] . ")");

            //For Report date header
            $CI->excel->getActiveSheet()->getStyle('A2:' . $letters[$cell_count] . '2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $CI->excel->getActiveSheet()->getStyle('A2:' . $letters[$cell_count] . '2')->getFill()->getStartColor()->setRGB('12456B');
            $CI->excel->getActiveSheet()->getStyle('A2:G20')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $CI->excel->setActiveSheetIndex(0)->mergeCells('A2:' . $letters[$cell_count] . '2');
            $CI->excel->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
            $CI->excel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
            $CI->excel->getActiveSheet()->setCellValue('A2', "Export Date : " . $exportDate);

            $col = 3;
        }



        //For Fields header
        $CI->excel->getActiveSheet()->getStyle('A' . $col . ':' . $letters[$cell_count] . $col)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $CI->excel->getActiveSheet()->getStyle('A' . $col . ':' . $letters[$cell_count] . $col)->getFill()->getStartColor()->setRGB('185C8F');
        $CI->excel->getActiveSheet()->getStyle('A' . $col . ':G20')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $i = 0;
        foreach ($headerArray as $key => $value) {
            $cell_name = $letters[$i] . $col;
            $CI->excel->getActiveSheet()->setCellValue($cell_name, $value);
            //change the font size
            $CI->excel->getActiveSheet()->getStyle($cell_name)->applyFromArray($styleArray);
            $CI->excel->getActiveSheet()->getRowDimension(3)->setRowHeight(25);

            //make the font become bold
            $CI->excel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
            $CI->excel->getActiveSheet()->getColumnDimension($letters[$i])->setAutoSize(true);
            $CI->excel->getActiveSheet()->getStyle($cell_name)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $i++;
        }

        //Set dynamic data
        if (is_array($inputData) && !empty($inputData)) {
            // set xls body
            $j = 0;
            $col++;
            foreach ($inputData as $user) {
                $i = 0;
                foreach ($headerArray as $key => $value) {
                    $cell_name = $letters[$i];
                    $CI->excel->getActiveSheet()->setCellValue($cell_name . ($j + $col), $user[$key]);
                    $CI->excel->getActiveSheet()->getRowDimension($j + $col)->setRowHeight(18);
                    $i++;
                }
                $j++;
            }
        } else {
            $CI->excel->getActiveSheet()->setCellValue('A' . $col, 'No Record Found.');
        }

        header('Content-Type: application/vnd.ms-excel'); //mime type
        header('Content-Disposition: attachment;filename="' . $fileName . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
        $objWriter = PHPExcel_IOFactory::createWriter($CI->excel, 'Excel5');

        $xls_filename = $folderPath . $fileName;
        //force user to download the Excel file without writing it to server's HD
        $objWriter->save($xls_filename);
        return true;
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

    function checkValueKeyExistInArray($array, $key, $val) {
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

    function set_user_language($user_guid, $lang) {
        $CI = &get_instance();
        $CI->db->set('Language', $lang);
        $CI->db->where('UserGUID', $user_guid);
        $CI->db->update(USERS);
    }

}

if (!function_exists('set_video_autoplay')) {

	function set_video_autoplay($user_guid, $autoplay) {
		$user_id = get_detail_by_guid($user_guid,3);
		$CI = &get_instance();
		$CI->db->set('VideoAutoplay', $autoplay);
		$CI->db->where('UserID', $user_id);
		$CI->db->update(USERDETAILS);
	}

}

if (!function_exists('html_substr')) {

    function html_substr($text, $length = 100, $ending = '...', $exact = true, $considerHtml = true) {
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

if (!function_exists('add_update_relationship_score')) {

    function add_update_relationship_score($user_id, $module_id, $module_entity_id, $score) {
        if (!$module_entity_id) {
            return;
        }
        $CI = &get_instance();
        $query = $CI->db->get_where(RELATIONSHIPSCORE, array('UserID' => $user_id, 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id));
        if ($query->num_rows()) {
            //Update Query
            if ($module_id == 3 && $module_entity_id == $user_id) {
                $CI->db->set('Score', DEFAULT_RELATIONSHIP_SCORE, FALSE);
            } else {
                $CI->db->set('Score', "Score+($score)", FALSE);
            }
            $CI->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
            $CI->db->where('UserID', $user_id);
            $CI->db->where('ModuleID', $module_id);
            $CI->db->where('ModuleEntityID', $module_entity_id);
            $CI->db->update(RELATIONSHIPSCORE);
        } else {
            if ($module_id == 3 && $module_entity_id == $user_id) {
                $score = DEFAULT_RELATIONSHIP_SCORE;
            }
            //Insert Query
            $CI->db->insert(RELATIONSHIPSCORE, array('UserID' => $user_id, 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id, 'Score' => $score, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
        }
    }

}

if (!function_exists('array_flatten')) {

    function array_flatten($array) {
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

    function get_media_thumb($ImageName) {
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

    function percentile($array) {
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

    function version_control($onlyVersion = false) {

        $v1 = "5.6";

        if ($onlyVersion) {
            return "?v=$v1";
        }

        echo "?v=$v1";
    }

}

if (!function_exists('notify_node')) {

    function notify_node($method, $data) {
        $CI = &get_instance();
        if (!empty($CI->DeviceTypeID) && $CI->DeviceTypeID == 1) {
            $CI->load->library('Node');
            //$node = new node(array("route" => "liveFeed", "postData" => array('UserID'=>$user_id,'Type'=>$type)));
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

    function get_cover_image_state($user_id, $module_entity_id, $module_id) {
        if (!empty($module_entity_id)) {
            $CI = &get_instance();
            $CI->db->select('Status');
            $CI->db->where('UserID', $user_id);
            $CI->db->where('ModuleID', $module_id);
            $CI->db->where('ModuleEntityID', $module_entity_id);
            $CI->db->limit('1');
            $query = $CI->db->get(COVERIMAGESTATE);
            //echo $CI->db->last_query();die;
            if ($query->num_rows()) {
                $result = $query->row_array();
                return $result['Status'];
            }
        }
        return 1;
    }

}

if (!function_exists('get_data_new')) {

    function get_data_new($InputArray) {

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

    function isMultiArray($a) {
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

    function get_pagination_offset($PageNo, $Limit) {
        if (empty($PageNo)) {
            $PageNo = 1;
        }
        $offset = ($PageNo - 1) * $Limit;
        return $offset;
    }

}

if (!function_exists('random_user_agent')) {

    function random_user_agent() {
        $list_agent = array();
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.2.20) Gecko/20120820 Firefox/17.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_5 rv:6.0) Gecko/20130531 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5351 (KHTML, like Gecko) Chrome/14.0.870.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5352 (KHTML, like Gecko) Chrome/33.0.847.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5341 (KHTML, like Gecko) Chrome/20.0.814.0 Safari/5341";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.2.20) Gecko/20110308 Firefox/6.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.2.20) Gecko/20110206 Firefox/8.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5341 (KHTML, like Gecko) Chrome/37.0.805.0 Safari/5341";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5311 (KHTML, like Gecko) Chrome/16.0.868.0 Safari/5311";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_8_9) AppleWebKit/5341 (KHTML, like Gecko) Chrome/23.0.885.0 Safari/5341";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5332 (KHTML, like Gecko) Chrome/17.0.806.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.2.20) Gecko/20150205 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_9_1 rv:3.0; vi-VN) AppleWebKit/535.3.6 (KHTML, like Gecko) Version/5.0.1 Safari/535.3.6";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5330 (KHTML, like Gecko) Chrome/23.0.829.0 Safari/5330";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5322 (KHTML, like Gecko) Chrome/27.0.813.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.2.20) Gecko/20120421 Firefox/27.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.1.20) Gecko/20120116 Firefox/38.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5341 (KHTML, like Gecko) Chrome/32.0.876.0 Safari/5341";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5352 (KHTML, like Gecko) Chrome/42.0.853.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5330 (KHTML, like Gecko) Chrome/33.0.844.0 Safari/5330";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.0.20) Gecko/20110327 Firefox/26.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5320 (KHTML, like Gecko) Chrome/30.0.810.0 Safari/5320";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.1.20) Gecko/20110208 Firefox/13.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5322 (KHTML, like Gecko) Chrome/12.0.802.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.1.20) Gecko/20141127 Firefox/32.0";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_2 rv:2.0) Gecko/20130723 Firefox/26.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5351 (KHTML, like Gecko) Chrome/14.0.898.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5360 (KHTML, like Gecko) Chrome/30.0.886.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_9_9 rv:6.0; vi-VN) AppleWebKit/532.11.4 (KHTML, like Gecko) Version/5.0.1 Safari/532.11.4";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5332 (KHTML, like Gecko) Chrome/25.0.807.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.0.20) Gecko/20110122 Firefox/37.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5360 (KHTML, like Gecko) Chrome/33.0.863.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_5 rv:3.0; en-US) AppleWebKit/534.50.2 (KHTML, like Gecko) Version/5.0 Safari/534.50.2";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.1.20) Gecko/20130330 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5331 (KHTML, like Gecko) Chrome/16.0.831.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.1.20) Gecko/20120311 Firefox/29.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.0.20) Gecko/20130803 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5322 (KHTML, like Gecko) Chrome/22.0.866.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_8 rv:2.0) Gecko/20130209 Firefox/39.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5360 (KHTML, like Gecko) Chrome/14.0.827.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.2.20) Gecko/20110316 Firefox/37.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.2.20) Gecko/20100927 Firefox/3.6.10";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5310 (KHTML, like Gecko) Chrome/11.0.856.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_5 rv:5.0) Gecko/20150202 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.2.20) Gecko/20100727 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5310 (KHTML, like Gecko) Chrome/18.0.851.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5310 (KHTML, like Gecko) Chrome/29.0.867.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.2.20) Gecko/20110424 Firefox/30.0";
        $list_agent[] = "Mozilla/5.0 (Windows; U; Windows NT 6.2) AppleWebKit/535.31.6 (KHTML, like Gecko) Version/5.0.2 Safari/535.31.6";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5311 (KHTML, like Gecko) Chrome/37.0.896.0 Safari/5311";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.1.20) Gecko/20110918 Firefox/3.6.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.1.20) Gecko/20120331 Firefox/7.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5360 (KHTML, like Gecko) Chrome/23.0.857.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.0.20) Gecko/20130826 Firefox/3.6.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_8_8 rv:2.0; en-US) AppleWebKit/532.44.3 (KHTML, like Gecko) Version/4.0 Safari/532.44.3";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; vi-VN; rv:1.9.0.20) Gecko/20130415 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5332 (KHTML, like Gecko) Chrome/29.0.884.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5350 (KHTML, like Gecko) Chrome/10.0.869.0 Safari/5350";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.1.20) Gecko/20140305 Firefox/31.0.1";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_6_1 rv:3.0; en-US) AppleWebKit/531.10.4 (KHTML, like Gecko) Version/4.1 Safari/531.10.4";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.1.20) Gecko/20140523 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5320 (KHTML, like Gecko) Chrome/20.0.867.0 Safari/5320";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5332 (KHTML, like Gecko) Chrome/27.0.845.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5351 (KHTML, like Gecko) Chrome/10.0.826.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5361 (KHTML, like Gecko) Chrome/40.0.828.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5352 (KHTML, like Gecko) Chrome/41.0.843.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5321 (KHTML, like Gecko) Chrome/30.0.811.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5340 (KHTML, like Gecko) Chrome/15.0.866.0 Safari/5340";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_8_3 rv:4.0; vi-VN) AppleWebKit/533.32.4 (KHTML, like Gecko) Version/5.0.4 Safari/533.32.4";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.0.20) Gecko/20131019 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_6_7 rv:6.0; en-US) AppleWebKit/533.31.3 (KHTML, like Gecko) Version/4.0.1 Safari/533.31.3";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.1.20) Gecko/20130703 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_6 rv:4.0) Gecko/20110617 Firefox/3.6.5";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_5_6 rv:4.0; vi-VN) AppleWebKit/531.12.5 (KHTML, like Gecko) Version/4.0 Safari/531.12.5";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/5.1)";
        $list_agent[] = "Opera/9.30 (Windows NT 5.0; U; vi-VN) Presto/2.9.179 Version/10.00";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5312 (KHTML, like Gecko) Chrome/14.0.885.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5310 (KHTML, like Gecko) Chrome/14.0.869.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.1.20) Gecko/20130608 Firefox/5.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.0.20) Gecko/20121003 Firefox/20.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_9 rv:4.0; en-US) AppleWebKit/535.35.1 (KHTML, like Gecko) Version/4.0 Safari/535.35.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5321 (KHTML, like Gecko) Chrome/13.0.896.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5310 (KHTML, like Gecko) Chrome/17.0.882.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.0.20) Gecko/20140308 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.0.20) Gecko/20140901 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5361 (KHTML, like Gecko) Chrome/15.0.804.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.1.20) Gecko/20110803 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5312 (KHTML, like Gecko) Chrome/28.0.882.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5341 (KHTML, like Gecko) Chrome/17.0.879.0 Safari/5341";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5362 (KHTML, like Gecko) Chrome/28.0.805.0 Safari/5362";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5351 (KHTML, like Gecko) Chrome/28.0.877.0 Safari/5351";
        $list_agent[] = "Opera/9.74 (Windows NT 6.1; U; vi-VN) Presto/2.9.183 Version/11.00";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5361 (KHTML, like Gecko) Chrome/23.0.812.0 Safari/5361";
        $list_agent[] = "Opera/8.98 (Windows NT 6.1; U; en-US) Presto/2.9.189 Version/11.00";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_4_4 rv:5.0; en-US) AppleWebKit/534.10.3 (KHTML, like Gecko) Version/4.0.4 Safari/534.10.3";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5342 (KHTML, like Gecko) Chrome/32.0.878.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_7 rv:2.0) Gecko/20120704 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.2.20) Gecko/20141215 Firefox/15.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5312 (KHTML, like Gecko) Chrome/40.0.805.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5342 (KHTML, like Gecko) Chrome/38.0.893.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5312 (KHTML, like Gecko) Chrome/22.0.853.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_0 rv:5.0; vi-VN) AppleWebKit/531.1.6 (KHTML, like Gecko) Version/5.1 Safari/531.1.6";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.2.20) Gecko/20131220 Firefox/20.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5311 (KHTML, like Gecko) Chrome/25.0.815.0 Safari/5311";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.0.20) Gecko/20141117 Firefox/11.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.1.20) Gecko/20120527 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5322 (KHTML, like Gecko) Chrome/26.0.886.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5342 (KHTML, like Gecko) Chrome/20.0.870.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.0.20) Gecko/20140601 Firefox/23.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5311 (KHTML, like Gecko) Chrome/33.0.864.0 Safari/5311";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; vi-VN; rv:1.9.2.20) Gecko/20130312 Firefox/3.6.14";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.1.20) Gecko/20111212 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.2.20) Gecko/20120404 Firefox/26.0";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_7_4) AppleWebKit/5341 (KHTML, like Gecko) Chrome/14.0.847.0 Safari/5341";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.1.20) Gecko/20150201 Firefox/9.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5360 (KHTML, like Gecko) Chrome/38.0.861.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.2.20) Gecko/20110605 Firefox/40.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5322 (KHTML, like Gecko) Chrome/11.0.878.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_9_7 rv:3.0; en-US) AppleWebKit/535.20.3 (KHTML, like Gecko) Version/5.0 Safari/535.20.3";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.1.20) Gecko/20131222 Firefox/40.0";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_6_9 rv:3.0) Gecko/20131008 Firefox/14.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5311 (KHTML, like Gecko) Chrome/18.0.887.0 Safari/5311";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5362 (KHTML, like Gecko) Chrome/32.0.843.0 Safari/5362";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5310 (KHTML, like Gecko) Chrome/21.0.827.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5342 (KHTML, like Gecko) Chrome/16.0.843.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (Macintosh; PPC Mac OS X 10_9_5 rv:2.0) Gecko/20130810 Firefox/29.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_0 rv:2.0) Gecko/20130615 Firefox/9.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5352 (KHTML, like Gecko) Chrome/33.0.802.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.1.20) Gecko/20130929 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5361 (KHTML, like Gecko) Chrome/20.0.804.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.2.20) Gecko/20140530 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.2.20) Gecko/20130904 Firefox/3.6.15";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5351 (KHTML, like Gecko) Chrome/30.0.884.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (X11; Linux i686) AppleWebKit/5352 (KHTML, like Gecko) Chrome/12.0.871.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.0.20) Gecko/20120115 Firefox/24.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5350 (KHTML, like Gecko) Chrome/29.0.895.0 Safari/5350";
        $list_agent[] = "Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_1 rv:3.0) Gecko/20111222 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.1.20) Gecko/20130101 Firefox/3.6.12";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_6_2 rv:6.0; vi-VN) AppleWebKit/531.2.5 (KHTML, like Gecko) Version/4.0 Safari/531.2.5";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.1.20) Gecko/20130920 Firefox/3.6.7";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_5) AppleWebKit/5352 (KHTML, like Gecko) Chrome/37.0.851.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5310 (KHTML, like Gecko) Chrome/29.0.832.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_4_6 rv:6.0; vi-VN) AppleWebKit/535.18.3 (KHTML, like Gecko) Version/5.0.1 Safari/535.18.3";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5362 (KHTML, like Gecko) Chrome/29.0.863.0 Safari/5362";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_6_0 rv:4.0; vi-VN) AppleWebKit/532.45.1 (KHTML, like Gecko) Version/4.1 Safari/532.45.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5351 (KHTML, like Gecko) Chrome/30.0.887.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.2.20) Gecko/20150215 Firefox/3.6.20";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.1.20) Gecko/20110510 Firefox/3.6.15";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5331 (KHTML, like Gecko) Chrome/24.0.845.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5341 (KHTML, like Gecko) Chrome/38.0.846.0 Safari/5341";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.0.20) Gecko/20130716 Firefox/23.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5350 (KHTML, like Gecko) Chrome/42.0.873.0 Safari/5350";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5312 (KHTML, like Gecko) Chrome/41.0.837.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.2.20) Gecko/20110503 Firefox/28.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_6 rv:3.0; vi-VN) AppleWebKit/534.42.7 (KHTML, like Gecko) Version/4.0 Safari/534.42.7";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5341 (KHTML, like Gecko) Chrome/19.0.808.0 Safari/5341";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5321 (KHTML, like Gecko) Chrome/41.0.827.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.0.20) Gecko/20100812 Firefox/3.6.3";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_9_9 rv:4.0) Gecko/20140219 Firefox/29.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5311 (KHTML, like Gecko) Chrome/31.0.857.0 Safari/5311";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_7_9 rv:3.0; en-US) AppleWebKit/533.43.3 (KHTML, like Gecko) Version/4.0.1 Safari/533.43.3";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5342 (KHTML, like Gecko) Chrome/34.0.894.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.1.20) Gecko/20100406 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.0.20) Gecko/20130731 Firefox/34.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.0.20) Gecko/20120421 Firefox/6.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.0.20) Gecko/20150215 Firefox/3.6.10";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.1.20) Gecko/20131223 Firefox/27.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.2; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; PPC Mac OS X 10_4_0 rv:2.0) Gecko/20140614 Firefox/27.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.1.20) Gecko/20121104 Firefox/23.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.2.20) Gecko/20101118 Firefox/3.6.18";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_4_8 rv:5.0) Gecko/20140604 Firefox/3.6.12";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.2.20) Gecko/20131012 Firefox/5.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; vi-VN; rv:1.9.2.20) Gecko/20130814 Firefox/7.0";
        $list_agent[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5 rv:6.0) Gecko/20120510 Firefox/3.6.10";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.2.20) Gecko/20140324 Firefox/28.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.2.20) Gecko/20101009 Firefox/3.6.14";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_6_8) AppleWebKit/5361 (KHTML, like Gecko) Chrome/16.0.855.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; vi-VN; rv:1.9.0.20) Gecko/20140621 Firefox/3.6.15";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5361 (KHTML, like Gecko) Chrome/25.0.868.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5342 (KHTML, like Gecko) Chrome/21.0.843.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.0.20) Gecko/20130522 Firefox/13.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5322 (KHTML, like Gecko) Chrome/10.0.869.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_6 rv:2.0) Gecko/20130222 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5332 (KHTML, like Gecko) Chrome/12.0.811.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5351 (KHTML, like Gecko) Chrome/13.0.801.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.2.20) Gecko/20120609 Firefox/6.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5310 (KHTML, like Gecko) Chrome/37.0.889.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_4_6) AppleWebKit/5342 (KHTML, like Gecko) Chrome/24.0.824.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5351 (KHTML, like Gecko) Chrome/26.0.887.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.1.20) Gecko/20140728 Firefox/40.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5330 (KHTML, like Gecko) Chrome/37.0.831.0 Safari/5330";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_4_9) AppleWebKit/5321 (KHTML, like Gecko) Chrome/25.0.850.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.0.20) Gecko/20140715 Firefox/3.6.10";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_9_7 rv:5.0) Gecko/20141121 Firefox/35.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5332 (KHTML, like Gecko) Chrome/23.0.849.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5340 (KHTML, like Gecko) Chrome/29.0.816.0 Safari/5340";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_7 rv:3.0; en-US) AppleWebKit/533.11.5 (KHTML, like Gecko) Version/5.1 Safari/533.11.5";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5321 (KHTML, like Gecko) Chrome/35.0.880.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_8_2 rv:2.0; vi-VN) AppleWebKit/531.27.6 (KHTML, like Gecko) Version/4.1 Safari/531.27.6";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_8_3 rv:6.0; en-US) AppleWebKit/535.20.1 (KHTML, like Gecko) Version/4.0.5 Safari/535.20.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_5_7) AppleWebKit/5331 (KHTML, like Gecko) Chrome/25.0.882.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5331 (KHTML, like Gecko) Chrome/21.0.827.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_4_3 rv:5.0) Gecko/20120619 Firefox/19.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.0.20) Gecko/20120807 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; vi-VN; rv:1.9.1.20) Gecko/20110710 Firefox/3.6.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.0.20) Gecko/20141205 Firefox/3.6.4";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5340 (KHTML, like Gecko) Chrome/18.0.855.0 Safari/5340";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; vi-VN; rv:1.9.2.20) Gecko/20150103 Firefox/23.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.2.20) Gecko/20140217 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5311 (KHTML, like Gecko) Chrome/29.0.855.0 Safari/5311";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5321 (KHTML, like Gecko) Chrome/30.0.879.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5360 (KHTML, like Gecko) Chrome/29.0.858.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5331 (KHTML, like Gecko) Chrome/19.0.816.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5331 (KHTML, like Gecko) Chrome/29.0.848.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5310 (KHTML, like Gecko) Chrome/20.0.863.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5320 (KHTML, like Gecko) Chrome/31.0.897.0 Safari/5320";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_6_9) AppleWebKit/5350 (KHTML, like Gecko) Chrome/18.0.805.0 Safari/5350";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5322 (KHTML, like Gecko) Chrome/21.0.852.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5322 (KHTML, like Gecko) Chrome/36.0.850.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5360 (KHTML, like Gecko) Chrome/25.0.870.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_4) AppleWebKit/5331 (KHTML, like Gecko) Chrome/16.0.813.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_8_1) AppleWebKit/5332 (KHTML, like Gecko) Chrome/15.0.859.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5331 (KHTML, like Gecko) Chrome/11.0.839.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_9_0 rv:4.0) Gecko/20111028 Firefox/37.0.1";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_9_8) AppleWebKit/5331 (KHTML, like Gecko) Chrome/16.0.854.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5341 (KHTML, like Gecko) Chrome/16.0.831.0 Safari/5341";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.2.20) Gecko/20130504 Firefox/3.6.18";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.2.20) Gecko/20100203 Firefox/3.6.12";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5320 (KHTML, like Gecko) Chrome/12.0.844.0 Safari/5320";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.0.20) Gecko/20100502 Firefox/3.6.9";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.1.20) Gecko/20110118 Firefox/35.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5312 (KHTML, like Gecko) Chrome/28.0.832.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.2.20) Gecko/20140718 Firefox/3.6.18";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.0.20) Gecko/20110324 Firefox/3.6.4";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.2.20) Gecko/20130517 Firefox/7.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5342 (KHTML, like Gecko) Chrome/22.0.804.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5310 (KHTML, like Gecko) Chrome/18.0.837.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.2.20) Gecko/20110818 Firefox/3.6.16";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5330 (KHTML, like Gecko) Chrome/28.0.898.0 Safari/5330";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.0.20) Gecko/20110516 Firefox/20.0";
        $list_agent[] = "Mozilla/5.0 (X11; Linux i686) AppleWebKit/5332 (KHTML, like Gecko) Chrome/37.0.834.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5351 (KHTML, like Gecko) Chrome/33.0.895.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.2; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_4 rv:6.0) Gecko/20110819 Firefox/10.0.1";
        $list_agent[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_9 rv:2.0) Gecko/20130612 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.2.20) Gecko/20110313 Firefox/36.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Opera/9.31 (Windows NT 6.1; U; vi-VN) Presto/2.9.161 Version/11.00";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5352 (KHTML, like Gecko) Chrome/29.0.806.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.0.20) Gecko/20101209 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5322 (KHTML, like Gecko) Chrome/28.0.821.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5320 (KHTML, like Gecko) Chrome/23.0.848.0 Safari/5320";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_4 rv:2.0; vi-VN) AppleWebKit/534.17.4 (KHTML, like Gecko) Version/4.1 Safari/534.17.4";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_8_6 rv:4.0; vi-VN) AppleWebKit/532.32.3 (KHTML, like Gecko) Version/4.0.4 Safari/532.32.3";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5321 (KHTML, like Gecko) Chrome/33.0.819.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.2.20) Gecko/20140101 Firefox/3.6.2";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_3 rv:6.0) Gecko/20130809 Firefox/6.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.1.20) Gecko/20110324 Firefox/36.0.1";
        $list_agent[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3 rv:4.0) Gecko/20120409 Firefox/24.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5351 (KHTML, like Gecko) Chrome/15.0.899.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5330 (KHTML, like Gecko) Chrome/33.0.898.0 Safari/5330";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.2.20) Gecko/20110524 Firefox/3.6.14";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_7_2 rv:4.0; vi-VN) AppleWebKit/532.38.7 (KHTML, like Gecko) Version/5.0.1 Safari/532.38.7";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_9_5) AppleWebKit/5342 (KHTML, like Gecko) Chrome/32.0.828.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.1.20) Gecko/20130917 Firefox/11.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5351 (KHTML, like Gecko) Chrome/34.0.870.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.2.20) Gecko/20100120 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5322 (KHTML, like Gecko) Chrome/34.0.812.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5331 (KHTML, like Gecko) Chrome/32.0.823.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5340 (KHTML, like Gecko) Chrome/32.0.835.0 Safari/5340";
        $list_agent[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_5 rv:5.0) Gecko/20120212 Firefox/16.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5352 (KHTML, like Gecko) Chrome/37.0.893.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_6_3 rv:3.0; vi-VN) AppleWebKit/531.15.5 (KHTML, like Gecko) Version/4.1 Safari/531.15.5";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.2; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5350 (KHTML, like Gecko) Chrome/39.0.892.0 Safari/5350";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5341 (KHTML, like Gecko) Chrome/11.0.886.0 Safari/5341";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5340 (KHTML, like Gecko) Chrome/11.0.848.0 Safari/5340";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5351 (KHTML, like Gecko) Chrome/10.0.802.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.0.20) Gecko/20101015 Firefox/3.6.17";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_7_0) AppleWebKit/5361 (KHTML, like Gecko) Chrome/41.0.809.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5351 (KHTML, like Gecko) Chrome/16.0.863.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.1.20) Gecko/20100424 Firefox/3.6.7";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5312 (KHTML, like Gecko) Chrome/31.0.800.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.2.20) Gecko/20140210 Firefox/3.6.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5310 (KHTML, like Gecko) Chrome/24.0.890.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5352 (KHTML, like Gecko) Chrome/38.0.802.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.0.20) Gecko/20130108 Firefox/30.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.0.20) Gecko/20140817 Firefox/6.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.1.20) Gecko/20140109 Firefox/22.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.2; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5352 (KHTML, like Gecko) Chrome/15.0.818.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.1.20) Gecko/20100320 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5362 (KHTML, like Gecko) Chrome/32.0.813.0 Safari/5362";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5332 (KHTML, like Gecko) Chrome/37.0.820.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5312 (KHTML, like Gecko) Chrome/42.0.834.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.1.20) Gecko/20130629 Firefox/23.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.2; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_8_3) AppleWebKit/5331 (KHTML, like Gecko) Chrome/19.0.845.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5310 (KHTML, like Gecko) Chrome/35.0.851.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (Macintosh; PPC Mac OS X 10_4_0 rv:4.0) Gecko/20110608 Firefox/36.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5311 (KHTML, like Gecko) Chrome/11.0.882.0 Safari/5311";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5332 (KHTML, like Gecko) Chrome/27.0.805.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.2.20) Gecko/20110812 Firefox/3.6.18";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_9_7 rv:6.0; en-US) AppleWebKit/533.49.1 (KHTML, like Gecko) Version/5.0 Safari/533.49.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5341 (KHTML, like Gecko) Chrome/18.0.885.0 Safari/5341";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_5 rv:2.0; en-US) AppleWebKit/531.48.6 (KHTML, like Gecko) Version/4.0.3 Safari/531.48.6";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_6_0 rv:5.0; en-US) AppleWebKit/532.1.6 (KHTML, like Gecko) Version/5.0.2 Safari/532.1.6";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5360 (KHTML, like Gecko) Chrome/21.0.823.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_6 rv:3.0; en-US) AppleWebKit/533.9.6 (KHTML, like Gecko) Version/5.0 Safari/533.9.6";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5312 (KHTML, like Gecko) Chrome/24.0.855.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7) AppleWebKit/5351 (KHTML, like Gecko) Chrome/24.0.896.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.2.20) Gecko/20110715 Firefox/30.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.0.20) Gecko/20100915 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.2.20) Gecko/20100310 Firefox/3.6.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.1; Trident/4.1)";
        $list_agent[] = "Opera/8.67 (Windows NT 6.2; U; vi-VN) Presto/2.9.169 Version/11.00";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.2.20) Gecko/20120128 Firefox/19.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.0.20) Gecko/20100613 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.2.20) Gecko/20141021 Firefox/3.8";
        $list_agent[] = "Opera/8.35 (Windows NT 6.0; U; en-US) Presto/2.9.183 Version/11.00";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.0.20) Gecko/20150112 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.2; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.2; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5351 (KHTML, like Gecko) Chrome/15.0.843.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5361 (KHTML, like Gecko) Chrome/32.0.887.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.2.20) Gecko/20131121 Firefox/3.6.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5332 (KHTML, like Gecko) Chrome/33.0.842.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.0.20) Gecko/20140129 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_2 rv:2.0) Gecko/20140120 Firefox/40.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5351 (KHTML, like Gecko) Chrome/11.0.866.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/5.1)";
        $list_agent[] = "Opera/9.38 (Windows NT 5.2; U; vi-VN) Presto/2.9.174 Version/12.00";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5321 (KHTML, like Gecko) Chrome/19.0.895.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5331 (KHTML, like Gecko) Chrome/15.0.845.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5321 (KHTML, like Gecko) Chrome/15.0.822.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.1.20) Gecko/20150122 Firefox/18.0";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_6_7 rv:3.0; en-US) AppleWebKit/534.22.5 (KHTML, like Gecko) Version/4.0.3 Safari/534.22.5";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Opera/9.49 (Windows NT 5.2; U; en-US) Presto/2.9.176 Version/12.00";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.1.20) Gecko/20111111 Firefox/15.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.2.20) Gecko/20141224 Firefox/22.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5352 (KHTML, like Gecko) Chrome/30.0.839.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.2.20) Gecko/20140330 Firefox/3.6.3";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5362 (KHTML, like Gecko) Chrome/42.0.862.0 Safari/5362";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5310 (KHTML, like Gecko) Chrome/26.0.801.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.2.20) Gecko/20131115 Firefox/3.6.16";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.2.20) Gecko/20120104 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5361 (KHTML, like Gecko) Chrome/17.0.864.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.1.20) Gecko/20131007 Firefox/26.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (X11; Linux i686; rv:5.0) Gecko/20140416 Firefox/33.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5331 (KHTML, like Gecko) Chrome/16.0.804.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_8 rv:6.0; en-US) AppleWebKit/533.15.7 (KHTML, like Gecko) Version/5.0.1 Safari/533.15.7";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.2.20) Gecko/20121023 Firefox/6.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5342 (KHTML, like Gecko) Chrome/26.0.812.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.1.20) Gecko/20131118 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5351 (KHTML, like Gecko) Chrome/12.0.818.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_5 rv:4.0) Gecko/20130421 Firefox/14.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5360 (KHTML, like Gecko) Chrome/14.0.883.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5351 (KHTML, like Gecko) Chrome/27.0.811.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_5_1 rv:6.0; en-US) AppleWebKit/535.46.1 (KHTML, like Gecko) Version/4.0 Safari/535.46.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5332 (KHTML, like Gecko) Chrome/14.0.878.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_9_4 rv:4.0) Gecko/20130202 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.1.20) Gecko/20110224 Firefox/29.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_0 rv:2.0) Gecko/20110911 Firefox/3.6.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5352 (KHTML, like Gecko) Chrome/41.0.851.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.2.20) Gecko/20130625 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_0 rv:3.0) Gecko/20140228 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5331 (KHTML, like Gecko) Chrome/12.0.833.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_5_8) AppleWebKit/5320 (KHTML, like Gecko) Chrome/22.0.818.0 Safari/5320";
        $list_agent[] = "Opera/8.24 (Windows NT 6.0; U; en-US) Presto/2.9.160 Version/10.00";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5361 (KHTML, like Gecko) Chrome/19.0.808.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.1.20) Gecko/20100804 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5352 (KHTML, like Gecko) Chrome/40.0.869.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; vi-VN; rv:1.9.2.20) Gecko/20140427 Firefox/3.6.16";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5340 (KHTML, like Gecko) Chrome/32.0.881.0 Safari/5340";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5311 (KHTML, like Gecko) Chrome/28.0.891.0 Safari/5311";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.0.20) Gecko/20121107 Firefox/30.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.0.20) Gecko/20141016 Firefox/3.6.4";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5312 (KHTML, like Gecko) Chrome/30.0.822.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5321 (KHTML, like Gecko) Chrome/32.0.877.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_9_9) AppleWebKit/5321 (KHTML, like Gecko) Chrome/40.0.849.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5342 (KHTML, like Gecko) Chrome/38.0.866.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5332 (KHTML, like Gecko) Chrome/30.0.852.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5322 (KHTML, like Gecko) Chrome/30.0.804.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; vi-VN; rv:1.9.0.20) Gecko/20110312 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_9_7 rv:5.0; vi-VN) AppleWebKit/531.42.3 (KHTML, like Gecko) Version/5.0.1 Safari/531.42.3";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5342 (KHTML, like Gecko) Chrome/19.0.832.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.1.20) Gecko/20130322 Firefox/16.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5351 (KHTML, like Gecko) Chrome/33.0.851.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5360 (KHTML, like Gecko) Chrome/41.0.817.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5331 (KHTML, like Gecko) Chrome/31.0.857.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.0.20) Gecko/20140702 Firefox/19.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.1.20) Gecko/20140428 Firefox/27.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.0.20) Gecko/20130422 Firefox/12.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.2.20) Gecko/20120801 Firefox/19.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5351 (KHTML, like Gecko) Chrome/11.0.826.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5340 (KHTML, like Gecko) Chrome/42.0.881.0 Safari/5340";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5331 (KHTML, like Gecko) Chrome/26.0.815.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.2.20) Gecko/20131123 Firefox/23.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.0.20) Gecko/20150106 Firefox/3.6.16";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.2.20) Gecko/20120926 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.2.20) Gecko/20140706 Firefox/5.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.1.20) Gecko/20110705 Firefox/17.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.2.20) Gecko/20120702 Firefox/23.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5321 (KHTML, like Gecko) Chrome/10.0.884.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.1.20) Gecko/20120514 Firefox/3.6.9";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5361 (KHTML, like Gecko) Chrome/17.0.897.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_4_5 rv:5.0) Gecko/20111103 Firefox/13.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5341 (KHTML, like Gecko) Chrome/18.0.856.0 Safari/5341";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.1.20) Gecko/20100913 Firefox/3.6.5";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.2; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5361 (KHTML, like Gecko) Chrome/22.0.860.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.1.20) Gecko/20111016 Firefox/7.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5362 (KHTML, like Gecko) Chrome/13.0.822.0 Safari/5362";
        $list_agent[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5331 (KHTML, like Gecko) Chrome/22.0.806.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.1.20) Gecko/20141214 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.1.20) Gecko/20110408 Firefox/5.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8 rv:6.0) Gecko/20111229 Firefox/3.6.18";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_5_0) AppleWebKit/5331 (KHTML, like Gecko) Chrome/17.0.807.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; PPC Mac OS X 10_8_8 rv:6.0) Gecko/20130509 Firefox/13.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5331 (KHTML, like Gecko) Chrome/39.0.860.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5322 (KHTML, like Gecko) Chrome/37.0.812.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.0.20) Gecko/20140406 Firefox/38.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5312 (KHTML, like Gecko) Chrome/29.0.844.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5332 (KHTML, like Gecko) Chrome/30.0.867.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; PPC Mac OS X 10_4_1 rv:6.0) Gecko/20130617 Firefox/28.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5332 (KHTML, like Gecko) Chrome/23.0.873.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5360 (KHTML, like Gecko) Chrome/28.0.857.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5361 (KHTML, like Gecko) Chrome/16.0.805.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5360 (KHTML, like Gecko) Chrome/33.0.876.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5352 (KHTML, like Gecko) Chrome/29.0.854.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.2.20) Gecko/20120403 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.1.20) Gecko/20121019 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.1.20) Gecko/20111129 Firefox/39.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.2.20) Gecko/20120817 Firefox/10.0.1";
        $list_agent[] = "Opera/8.22 (Windows NT 6.1; U; en-US) Presto/2.9.160 Version/10.00";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.1.20) Gecko/20110609 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_6_1 rv:6.0; en-US) AppleWebKit/535.8.1 (KHTML, like Gecko) Version/5.0.5 Safari/535.8.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.1.20) Gecko/20131226 Firefox/3.6.10";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5342 (KHTML, like Gecko) Chrome/36.0.805.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5331 (KHTML, like Gecko) Chrome/23.0.893.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_6_4 rv:3.0; en-US) AppleWebKit/535.25.6 (KHTML, like Gecko) Version/5.1 Safari/535.25.6";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.863.0 Safari/5350";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5332 (KHTML, like Gecko) Chrome/37.0.816.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5361 (KHTML, like Gecko) Chrome/33.0.856.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5352 (KHTML, like Gecko) Chrome/25.0.825.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.1.20) Gecko/20141204 Firefox/3.6.13";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5342 (KHTML, like Gecko) Chrome/17.0.839.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.0.20) Gecko/20100605 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5322 (KHTML, like Gecko) Chrome/40.0.877.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5352 (KHTML, like Gecko) Chrome/33.0.857.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5351 (KHTML, like Gecko) Chrome/23.0.862.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.2.20) Gecko/20111129 Firefox/36.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.0.20) Gecko/20130220 Firefox/3.6.12";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5352 (KHTML, like Gecko) Chrome/41.0.834.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.0.20) Gecko/20100723 Firefox/3.6.11";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5330 (KHTML, like Gecko) Chrome/13.0.810.0 Safari/5330";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5330 (KHTML, like Gecko) Chrome/20.0.885.0 Safari/5330";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.1.20) Gecko/20131226 Firefox/37.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.1.20) Gecko/20121221 Firefox/21.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5312 (KHTML, like Gecko) Chrome/15.0.804.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.2; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.0.20) Gecko/20110621 Firefox/24.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_4 rv:6.0; en-US) AppleWebKit/532.1.5 (KHTML, like Gecko) Version/4.0 Safari/532.1.5";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5360 (KHTML, like Gecko) Chrome/40.0.857.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5352 (KHTML, like Gecko) Chrome/27.0.836.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_6_1 rv:4.0; vi-VN) AppleWebKit/531.2.3 (KHTML, like Gecko) Version/4.0.2 Safari/531.2.3";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_9 rv:4.0; vi-VN) AppleWebKit/535.39.7 (KHTML, like Gecko) Version/4.1 Safari/535.39.7";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.2.20) Gecko/20130722 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5361 (KHTML, like Gecko) Chrome/32.0.873.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.0.20) Gecko/20120130 Firefox/40.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.1.20) Gecko/20140612 Firefox/17.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.0.20) Gecko/20100221 Firefox/3.6.11";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.0.20) Gecko/20111011 Firefox/3.6.14";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5361 (KHTML, like Gecko) Chrome/11.0.876.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5352 (KHTML, like Gecko) Chrome/38.0.808.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.2.20) Gecko/20101021 Firefox/3.6.20";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.0.20) Gecko/20140209 Firefox/37.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.2; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.1.20) Gecko/20111023 Firefox/3.6.9";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Opera/8.47 (Windows NT 6.0; U; vi-VN) Presto/2.9.184 Version/11.00";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5351 (KHTML, like Gecko) Chrome/40.0.858.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.0.20) Gecko/20141031 Firefox/10.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5361 (KHTML, like Gecko) Chrome/35.0.898.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (X11; Linux x86_64; rv:6.0) Gecko/20111116 Firefox/21.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.0.20) Gecko/20121206 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5312 (KHTML, like Gecko) Chrome/25.0.834.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5340 (KHTML, like Gecko) Chrome/39.0.847.0 Safari/5340";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_4_7) AppleWebKit/5362 (KHTML, like Gecko) Chrome/18.0.832.0 Safari/5362";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.2.20) Gecko/20140301 Firefox/21.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.1.20) Gecko/20120607 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.0.20) Gecko/20120713 Firefox/38.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5350 (KHTML, like Gecko) Chrome/12.0.812.0 Safari/5350";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; en-US; rv:1.9.0.20) Gecko/20120428 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5351 (KHTML, like Gecko) Chrome/18.0.829.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.1.20) Gecko/20130912 Firefox/3.6.17";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5331 (KHTML, like Gecko) Chrome/38.0.892.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5312 (KHTML, like Gecko) Chrome/12.0.857.0 Safari/5312";
        $list_agent[] = "Opera/9.45 (Windows NT 5.1; U; en-US) Presto/2.9.176 Version/11.00";
        $list_agent[] = "Mozilla/5.0 (Windows; U; Windows NT 5.0) AppleWebKit/534.35.1 (KHTML, like Gecko) Version/4.0.5 Safari/534.35.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5311 (KHTML, like Gecko) Chrome/19.0.881.0 Safari/5311";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5342 (KHTML, like Gecko) Chrome/11.0.814.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5361 (KHTML, like Gecko) Chrome/31.0.865.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.1; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5312 (KHTML, like Gecko) Chrome/40.0.824.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.2.20) Gecko/20121101 Firefox/37.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.1.20) Gecko/20131119 Firefox/35.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.1)";
        $list_agent[] = "Opera/8.14 (Windows NT 5.1; U; en-US) Presto/2.9.186 Version/10.00";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.0.20) Gecko/20130713 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.0.20) Gecko/20150127 Firefox/6.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.2.20) Gecko/20111226 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_4_5) AppleWebKit/5311 (KHTML, like Gecko) Chrome/38.0.805.0 Safari/5311";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_7) AppleWebKit/5332 (KHTML, like Gecko) Chrome/28.0.814.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5361 (KHTML, like Gecko) Chrome/16.0.817.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5320 (KHTML, like Gecko) Chrome/21.0.805.0 Safari/5320";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5361 (KHTML, like Gecko) Chrome/20.0.828.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5320 (KHTML, like Gecko) Chrome/33.0.822.0 Safari/5320";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_6_9) AppleWebKit/5330 (KHTML, like Gecko) Chrome/12.0.821.0 Safari/5330";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5310 (KHTML, like Gecko) Chrome/28.0.868.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.1.20) Gecko/20121222 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.2.20) Gecko/20140831 Firefox/26.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.2; Trident/5.0)";
        $list_agent[] = "Opera/8.14 (Windows NT 6.1; U; vi-VN) Presto/2.9.177 Version/11.00";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; vi-VN; rv:1.9.1.20) Gecko/20120124 Firefox/9.0";
        $list_agent[] = "Opera/9.16 (Windows NT 6.0; U; vi-VN) Presto/2.9.176 Version/11.00";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5322 (KHTML, like Gecko) Chrome/29.0.837.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_9_7 rv:2.0) Gecko/20141015 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6 rv:3.0; en-US) AppleWebKit/534.30.7 (KHTML, like Gecko) Version/5.0.4 Safari/534.30.7";
        $list_agent[] = "Mozilla/5.0 (Macintosh; PPC Mac OS X 10_7_1 rv:3.0) Gecko/20150221 Firefox/26.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.2.20) Gecko/20140618 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5350 (KHTML, like Gecko) Chrome/31.0.887.0 Safari/5350";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.1.20) Gecko/20110421 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.2; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.0.20) Gecko/20120728 Firefox/3.6.4";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5320 (KHTML, like Gecko) Chrome/39.0.801.0 Safari/5320";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.1.20) Gecko/20121127 Firefox/26.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.2.20) Gecko/20141026 Firefox/6.0.1";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_6 rv:4.0) Gecko/20120427 Firefox/27.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.1; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.0.20) Gecko/20111229 Firefox/25.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5321 (KHTML, like Gecko) Chrome/40.0.850.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5342 (KHTML, like Gecko) Chrome/23.0.845.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; en-US; rv:1.9.2.20) Gecko/20100703 Firefox/3.6.14";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.2.20) Gecko/20111010 Firefox/5.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; PPC Mac OS X 10_8_1 rv:3.0) Gecko/20120818 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.2.20) Gecko/20110630 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.2.20) Gecko/20101005 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5341 (KHTML, like Gecko) Chrome/28.0.800.0 Safari/5341";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_7_4) AppleWebKit/5322 (KHTML, like Gecko) Chrome/22.0.877.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_9_4 rv:3.0) Gecko/20110218 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5342 (KHTML, like Gecko) Chrome/12.0.805.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.2.20) Gecko/20120129 Firefox/31.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5351 (KHTML, like Gecko) Chrome/35.0.843.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5320 (KHTML, like Gecko) Chrome/15.0.838.0 Safari/5320";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_1 rv:5.0; en-US) AppleWebKit/535.46.1 (KHTML, like Gecko) Version/4.0.3 Safari/535.46.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_4 rv:4.0) Gecko/20130511 Firefox/28.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_8_6 rv:2.0) Gecko/20140530 Firefox/29.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.0.20) Gecko/20110316 Firefox/3.6.17";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_7_7 rv:5.0; vi-VN) AppleWebKit/534.9.2 (KHTML, like Gecko) Version/4.1 Safari/534.9.2";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5351 (KHTML, like Gecko) Chrome/39.0.816.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_8_1) AppleWebKit/5331 (KHTML, like Gecko) Chrome/28.0.894.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.0.20) Gecko/20120826 Firefox/38.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.2; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5351 (KHTML, like Gecko) Chrome/10.0.869.0 Safari/5351";
        $list_agent[] = "Opera/9.99 (Windows NT 5.0; U; vi-VN) Presto/2.9.168 Version/10.00";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.0.20) Gecko/20140127 Firefox/31.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5350 (KHTML, like Gecko) Chrome/11.0.893.0 Safari/5350";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5342 (KHTML, like Gecko) Chrome/24.0.818.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.1.20) Gecko/20111002 Firefox/8.0.1";
        $list_agent[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_1 rv:6.0) Gecko/20130903 Firefox/3.6.10";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5352 (KHTML, like Gecko) Chrome/14.0.850.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1; vi-VN; rv:1.9.0.20) Gecko/20100111 Firefox/3.6.17";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5322 (KHTML, like Gecko) Chrome/40.0.878.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.1.20) Gecko/20101122 Firefox/3.6.17";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 5.0; Trident/5.0)";
        $list_agent[] = "Opera/9.81 (Windows NT 5.0; U; vi-VN) Presto/2.9.178 Version/11.00";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5311 (KHTML, like Gecko) Chrome/28.0.822.0 Safari/5311";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5342 (KHTML, like Gecko) Chrome/20.0.841.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5332 (KHTML, like Gecko) Chrome/21.0.817.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_9_2 rv:6.0; vi-VN) AppleWebKit/531.20.4 (KHTML, like Gecko) Version/5.0 Safari/531.20.4";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.1.20) Gecko/20140501 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5342 (KHTML, like Gecko) Chrome/23.0.849.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/3.0)";
        $list_agent[] = "Opera/9.72 (Windows NT 5.0; U; en-US) Presto/2.9.178 Version/10.00";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5352 (KHTML, like Gecko) Chrome/18.0.812.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.0.20) Gecko/20131129 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5310 (KHTML, like Gecko) Chrome/37.0.890.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_6_0 rv:4.0; vi-VN) AppleWebKit/533.29.6 (KHTML, like Gecko) Version/4.0.4 Safari/533.29.6";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; vi-VN; rv:1.9.1.20) Gecko/20120905 Firefox/3.6.9";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5362 (KHTML, like Gecko) Chrome/11.0.868.0 Safari/5362";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_5_0) AppleWebKit/5352 (KHTML, like Gecko) Chrome/21.0.838.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5340 (KHTML, like Gecko) Chrome/36.0.844.0 Safari/5340";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5361 (KHTML, like Gecko) Chrome/10.0.875.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5330 (KHTML, like Gecko) Chrome/13.0.860.0 Safari/5330";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_5_8 rv:6.0; en-US) AppleWebKit/535.19.2 (KHTML, like Gecko) Version/5.0.4 Safari/535.19.2";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5320 (KHTML, like Gecko) Chrome/33.0.817.0 Safari/5320";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.0.20) Gecko/20131129 Firefox/9.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5330 (KHTML, like Gecko) Chrome/29.0.890.0 Safari/5330";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.1.20) Gecko/20131024 Firefox/5.0.1";
        $list_agent[] = "Opera/8.12 (Windows NT 6.2; U; en-US) Presto/2.9.186 Version/12.00";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_9 rv:2.0; vi-VN) AppleWebKit/535.8.5 (KHTML, like Gecko) Version/5.1 Safari/535.8.5";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.1.20) Gecko/20110603 Firefox/38.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5340 (KHTML, like Gecko) Chrome/18.0.801.0 Safari/5340";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5352 (KHTML, like Gecko) Chrome/38.0.830.0 Safari/5352";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; vi-VN; rv:1.9.1.20) Gecko/20100609 Firefox/3.6.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5322 (KHTML, like Gecko) Chrome/34.0.862.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5332 (KHTML, like Gecko) Chrome/41.0.828.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5342 (KHTML, like Gecko) Chrome/35.0.868.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.0.20) Gecko/20140503 Firefox/28.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1; vi-VN; rv:1.9.2.20) Gecko/20121217 Firefox/37.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5342 (KHTML, like Gecko) Chrome/13.0.835.0 Safari/5342";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5360 (KHTML, like Gecko) Chrome/24.0.825.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5351 (KHTML, like Gecko) Chrome/37.0.854.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.0.20) Gecko/20141205 Firefox/40.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.2.20) Gecko/20111225 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_9_0 rv:4.0; en-US) AppleWebKit/533.46.5 (KHTML, like Gecko) Version/5.1 Safari/533.46.5";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5360 (KHTML, like Gecko) Chrome/42.0.881.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_9_8 rv:2.0; en-US) AppleWebKit/531.20.6 (KHTML, like Gecko) Version/4.1 Safari/531.20.6";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5311 (KHTML, like Gecko) Chrome/42.0.816.0 Safari/5311";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5312 (KHTML, like Gecko) Chrome/12.0.897.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5332 (KHTML, like Gecko) Chrome/15.0.823.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.1.20) Gecko/20130525 Firefox/3.6.15";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.1.20) Gecko/20110913 Firefox/22.0.1";
        $list_agent[] = "Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_8 rv:2.0) Gecko/20110202 Firefox/30.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows; U; Windows NT 6.1) AppleWebKit/532.21.6 (KHTML, like Gecko) Version/4.0 Safari/532.21.6";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5321 (KHTML, like Gecko) Chrome/13.0.868.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.1; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5322 (KHTML, like Gecko) Chrome/16.0.852.0 Safari/5322";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5350 (KHTML, like Gecko) Chrome/34.0.822.0 Safari/5350";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5310 (KHTML, like Gecko) Chrome/22.0.867.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5361 (KHTML, like Gecko) Chrome/42.0.837.0 Safari/5361";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; vi-VN; rv:1.9.2.20) Gecko/20111227 Firefox/40.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 5.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5310 (KHTML, like Gecko) Chrome/24.0.834.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.0; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.0.20) Gecko/20110710 Firefox/11.0";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5362 (KHTML, like Gecko) Chrome/13.0.834.0 Safari/5362";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5330 (KHTML, like Gecko) Chrome/11.0.855.0 Safari/5330";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; Intel Mac OS X 10_6_3 rv:2.0; en-US) AppleWebKit/533.20.7 (KHTML, like Gecko) Version/4.0.1 Safari/533.20.7";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; vi-VN; rv:1.9.2.20) Gecko/20110428 Firefox/37.0";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5330 (KHTML, like Gecko) Chrome/36.0.836.0 Safari/5330";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_9 rv:3.0) Gecko/20110519 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; U; PPC Mac OS X 10_9_3) AppleWebKit/5350 (KHTML, like Gecko) Chrome/21.0.817.0 Safari/5350";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.1; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2; en-US; rv:1.9.2.20) Gecko/20130311 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.0.20) Gecko/20130730 Firefox/17.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.2.20) Gecko/20130211 Firefox/3.6.11";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5331 (KHTML, like Gecko) Chrome/40.0.880.0 Safari/5331";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.2; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5360 (KHTML, like Gecko) Chrome/22.0.824.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; en-US; rv:1.9.2.20) Gecko/20100505 Firefox/3.6.17";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5350 (KHTML, like Gecko) Chrome/28.0.885.0 Safari/5350";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5340 (KHTML, like Gecko) Chrome/35.0.870.0 Safari/5340";
        $list_agent[] = "Opera/8.21 (Windows NT 5.1; U; vi-VN) Presto/2.9.173 Version/11.00";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5360 (KHTML, like Gecko) Chrome/12.0.844.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0; en-US; rv:1.9.2.20) Gecko/20111023 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_9 rv:3.0) Gecko/20110922 Firefox/39.0.1";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5351 (KHTML, like Gecko) Chrome/13.0.843.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5312 (KHTML, like Gecko) Chrome/31.0.822.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_9_4 rv:2.0; vi-VN) AppleWebKit/533.2.2 (KHTML, like Gecko) Version/4.0.1 Safari/533.2.2";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.2; Trident/4.0)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/5312 (KHTML, like Gecko) Chrome/26.0.844.0 Safari/5312";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.0.20) Gecko/20111121 Firefox/25.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5310 (KHTML, like Gecko) Chrome/19.0.810.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.0; Trident/5.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/5351 (KHTML, like Gecko) Chrome/23.0.831.0 Safari/5351";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/5321 (KHTML, like Gecko) Chrome/25.0.839.0 Safari/5321";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5330 (KHTML, like Gecko) Chrome/18.0.809.0 Safari/5330";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0; en-US; rv:1.9.1.20) Gecko/20120509 Firefox/39.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/3.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/5332 (KHTML, like Gecko) Chrome/35.0.841.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/5360 (KHTML, like Gecko) Chrome/35.0.874.0 Safari/5360";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.2) AppleWebKit/5332 (KHTML, like Gecko) Chrome/13.0.812.0 Safari/5332";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_7) AppleWebKit/5310 (KHTML, like Gecko) Chrome/13.0.823.0 Safari/5310";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5340 (KHTML, like Gecko) Chrome/17.0.876.0 Safari/5340";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 5.2; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/3.0)";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 12.0; Windows NT 6.0; Trident/5.0)";
        $list_agent[] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_4_9 rv:4.0) Gecko/20140610 Firefox/3.8";
        $list_agent[] = "Mozilla/5.0 (Windows NT 6.2; vi-VN; rv:1.9.2.20) Gecko/20120307 Firefox/34.0.1";
        $list_agent[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.0; Trident/4.1)";
        $list_agent[] = "Mozilla/5.0 (Windows NT 5.0) AppleWebKit/5340 (KHTML, like Gecko) Chrome/38.0.850.0 Safari/5340";
        shuffle($list_agent);
        return $list_agent[0];
    }

}

if (!function_exists('add_activity_log')) {

    function add_activity_log($data, $only_mongo = false, $type = '') { // This is deperecated function Please use   user_activity_log_score_model->add_activity_log($data);
        $ci = &get_instance();


//            if($type && 0) {
//               $ci->load->model(array('mongo/log/user_activity_log_mongo'));
//               $ci->user_activity_log_mongo->insert_log($data, $type); 
//            }

        $activity_type_id = (int) isset($data['ActivityTypeID']) ? $data['ActivityTypeID'] : 0;
        if (in_array($activity_type_id, array(21, 32))) {
            $ci->db->select('ID')
                    ->from(USERSACTIVITYLOG)
                    ->where('UserID', $data['UserID'])
                    ->where('ModuleID', $data['ModuleID'])
                    ->where('ModuleEntityID', $data['ModuleEntityID'])
                    ->where('ActivityDate', get_current_date('%Y-%m-%d'))
                    ->where('Score > 0', NULL, FALSE)
                    ->where('ActivityTypeID', $activity_type_id);

            $query = $ci->db->get();
            $activity_log = $query->row_array();

            // If score given to user for this activity.
            if (!empty($activity_log['ID'])) {
                return 0; // Don't save it many times
                $data['Score'] = 0;
            }
        }

        $ci->db->insert(USERSACTIVITYLOG, $data);
        return $ci->db->insert_id();
    }

}

if (!function_exists('get_entity_list')) {

    function get_entity_list($user_id, $module_id, $module_entity_id) {
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

    function add_days_with_date($date, $days, $date_format) {
        $CI = &get_instance();
        $CI->load->helper('date');
        $date = strtotime("+" . $days . " days", strtotime($date));
        return mdate($date_format, $date);
    }

}

if (!function_exists('get_short_link')) {

    function get_short_link($link) {
        //echo $link;
        /* $ci = &get_instance();

          $params = array('bitlyLogin'=>'sureshp','bitlyAPIKey'=>'R_9cbcbd82679cac126258e3919eb41849');
          $ci->load->library('ext_conv_link',$params);

         */

        return $link;
    }

}

/**
 * [initiate_worker_job Used to initiate job workoer in background for given method]
 * @param  [type] $method [Method name]
 * @param  [type] $data   [Associate array of parameter]
 */
if (!function_exists('initiate_worker_job')) {

    function initiate_worker_job($method, $data = array(),$exchange_name = '',$que_name = ENVIRONMENT) {
        if ($method) {
            try {
                if (JOBSERVER == "Rabbitmq") {
                    $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
                    $channel = $connection->channel();
                    $push_data = json_encode(array('method' => $method, 'data' => $data));
                    $message = new AMQPMessage($push_data, array('delivery_mode' => 2, 'content_type' => 'application/json')); # make message persistent as 2
                    $channel->basic_publish($message, $exchange_name, $que_name);
                    $channel->close();
                    $connection->close();
                }
                if (/* function_exists("gearman_version") && */ extension_loaded('gearman') && JOBSERVER == "Gearman") {
                    $client = new GearmanClient();
                    $client->addServer();
                    $client->doBackground($method, json_encode($data));
                }
            } catch (Exception $e) {
                // Do nothing
               
            }
        }
    }

}

/**
 * @param $text
 * @return mixed
 */
if (!function_exists('link_it')) {

    function link_it($str, $attributes = array()) {
        $str = str_replace("http://www", "www", $str);
        $str = str_replace("https://www", "www", $str);

        $attrs = '';
        foreach ($attributes as $attribute => $value) {
            $attrs .= " {$attribute}=\"{$value}\"";
        }
        $str = ' ' . $str;
        $str = preg_replace(
                '`([^"=\'>])((http|https|ftp)://[^\s<]+[^\s<\.)])`i', '$1<a href="$2"' . $attrs . '>$2</a>', $str
        );
        $str = preg_replace(
                '`([^"=\'>])((www).[^\s<]+[^\s<\.)])`i', '$1<a href="http://$2"' . $attrs . '>$2</a>', $str
        );
        $str = substr($str, 1);
        return $str;
    }

}

if (!function_exists('crypto_rand_secure')) {

    function crypto_rand_secure($min, $max) {
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

    function getUniqueToken($length) {
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

    function getShortUrl($url, $check_first = 1) {
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

    function getLongUrl($url) {
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

    function safe_array_key($array, $key, $default = "") {
        return isset($array[$key]) ? $array[$key] : $default;
    }

}

if (!function_exists('unique_random_string')) {

    function unique_random_string($table, $unique_colomn, $extra_where = [], $type = 'alnum', $len = 8) {
        $ci = &get_instance();
        while (1) {
            $random_string = random_string($type, $len);
            $ci->db->from($table);
            $ci->db->where($unique_colomn, $random_string);
            if (!empty($extra_where)) {
                $ci->db->where($extra_where);
            }
            if ($ci->db->count_all_results() == 0) {
                break;
            }
        }
        return $random_string;
    }

}

// Encode as JSON - added by gautam
function json_encode_custom($data) {
    $ci = &get_instance();
    if (!empty($ci->DeviceTypeID) && $ci->DeviceTypeID != 1) {
        $JSON = json_encode($data, JSON_PRESERVE_ZERO_FRACTION|JSON_NUMERIC_CHECK);
        $ci->db->where('DataID', $ci->InputID);
        $ci->db->update('jsondata', array("Output" => $JSON));
        return $JSON;
    } else {
        return json_encode($data);
    }
}

if (!function_exists('get_activity_title')) {

    function get_activity_title($activity_id = 0, $post_content = '') {
        $ci = &get_instance();
        $title = '';
        $post_content = $ci->activity_model->parse_tag($post_content, 0, 0);
        if ($post_content != '') {
            $title = substr(strip_tags(html_entity_decode($post_content)), 0, 140);
        } else {
            $activity_data = get_detail_by_id($activity_id, 0, "PostTitle,PostContent", 2);
            if (!empty($activity_data)) {
                if (!empty($activity_data['PostTitle'])) {
                    $title = $activity_data['PostTitle'];
                } else {
                    $title = substr(strip_tags(html_entity_decode($activity_data['PostContent'])), 0, 140);
                }
            }
        }
        return $title;
    }

}
if (!function_exists('get_request_note')) {

    function get_request_note($activity_id, $request_to) {
        $ci = &get_instance();

        $ci->db->select('Note');
        $ci->db->from(REQUESTFORANSWER);
        $ci->db->where('ActivityID', $activity_id);
        $ci->db->where('RequestTo', $request_to);
        $query = $ci->db->get();
        //echo $ci->db->last_query();die;
        if ($query->num_rows()) {
            return $query->row()->Note;
        } else {
            return '';
        }
    }

}
if (!function_exists('can_create_wiki')) {

    function can_create_wiki($user_id, $module_id, $module_entity_id) {
        $CI = &get_instance();
        if ($module_id == 34) {
            $CI->load->model('forum/forum_model');
            $permissions = $CI->forum_model->check_forum_category_permissions($user_id, $module_entity_id, FALSE);
            if ($permissions['IsAdmin'])
                return TRUE;
            else
                return FALSE;
        }
        else if ($module_id == 1) {
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

    function get_analytics_id($login_session_key, $user_id = 0, $select_field = "AnalyticLoginID", $response_type = 1) {
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

    function sortByOrder($a, $b) {
        return $a['Permissions']['IsMember'] - $b['Permissions']['IsMember'];
    }

}

if (!function_exists('percentage')) {

    function percentage($totalVal, $val, $cal_int = 'int') {
        $percentage = 0;
        if ($cal_int == 'int') {
            $percentage = (int) ($val * 100 / $totalVal);
        }

        return $percentage;
    }

}


if (!function_exists('time_calculate')) {

    function time_calculate($time_start = NULL) {
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

    function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

}
if (!function_exists('random_username')) {

    function random_username($string) {
        $pattern = "";
        //$firstPart = $string[rand(0, strlen($string)-1)];
        $string = str_replace(' ', '-', $string);
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);

        $firstPart = $string;
        //$secondPart = substr(strstr(strtolower($string), $pattern, false), 0,3);
        $nrRand = rand(0, 100);
        $username = trim($firstPart) . trim($nrRand);
        return $username;
    }

}
#
# WARNING:
# This code is auto-generated. Do not modify it manually.
#

	$GLOBALS['emoji_maps'] = array(
    'names' => array(
        "\xc2\xa9" => 'COPYRIGHT SIGN',
        "\xc2\xae" => 'REGISTERED SIGN',
        "\xe2\x80\xbc" => 'DOUBLE EXCLAMATION MARK',
        "\xe2\x81\x89" => 'EXCLAMATION QUESTION MARK',
        "\xe2\x84\xa2" => 'TRADE MARK SIGN',
        "\xe2\x84\xb9" => 'INFORMATION SOURCE',
        "\xe2\x86\x94" => 'LEFT RIGHT ARROW',
        "\xe2\x86\x95" => 'UP DOWN ARROW',
        "\xe2\x86\x96" => 'NORTH WEST ARROW',
        "\xe2\x86\x97" => 'NORTH EAST ARROW',
        "\xe2\x86\x98" => 'SOUTH EAST ARROW',
        "\xe2\x86\x99" => 'SOUTH WEST ARROW',
        "\xe2\x86\xa9" => 'LEFTWARDS ARROW WITH HOOK',
        "\xe2\x86\xaa" => 'RIGHTWARDS ARROW WITH HOOK',
        "\xe2\x8c\x9a" => 'WATCH',
        "\xe2\x8c\x9b" => 'HOURGLASS',
        "\xe2\x8c\xa8" => 'KEYBOARD',
        "\xe2\x8f\xa9" => 'BLACK RIGHT-POINTING DOUBLE TRIANGLE',
        "\xe2\x8f\xaa" => 'BLACK LEFT-POINTING DOUBLE TRIANGLE',
        "\xe2\x8f\xab" => 'BLACK UP-POINTING DOUBLE TRIANGLE',
        "\xe2\x8f\xac" => 'BLACK DOWN-POINTING DOUBLE TRIANGLE',
        "\xe2\x8f\xad" => 'BLACK RIGHT-POINTING DOUBLE TRIANGLE WITH VERTICAL BAR',
        "\xe2\x8f\xae" => 'BLACK LEFT-POINTING DOUBLE TRIANGLE WITH VERTICAL BAR',
        "\xe2\x8f\xaf" => 'BLACK RIGHT-POINTING TRIANGLE WITH DOUBLE VERTICAL BAR',
        "\xe2\x8f\xb0" => 'ALARM CLOCK',
        "\xe2\x8f\xb1" => 'STOPWATCH',
        "\xe2\x8f\xb2" => 'TIMER CLOCK',
        "\xe2\x8f\xb3" => 'HOURGLASS WITH FLOWING SAND',
        "\xe2\x8f\xb8" => 'DOUBLE VERTICAL BAR',
        "\xe2\x8f\xb9" => 'BLACK SQUARE FOR STOP',
        "\xe2\x8f\xba" => 'BLACK CIRCLE FOR RECORD',
        "\xe2\x93\x82" => 'CIRCLED LATIN CAPITAL LETTER M',
        "\xe2\x96\xaa" => 'BLACK SMALL SQUARE',
        "\xe2\x96\xab" => 'WHITE SMALL SQUARE',
        "\xe2\x96\xb6" => 'BLACK RIGHT-POINTING TRIANGLE',
        "\xe2\x97\x80" => 'BLACK LEFT-POINTING TRIANGLE',
        "\xe2\x97\xbb" => 'WHITE MEDIUM SQUARE',
        "\xe2\x97\xbc" => 'BLACK MEDIUM SQUARE',
        "\xe2\x97\xbd" => 'WHITE MEDIUM SMALL SQUARE',
        "\xe2\x97\xbe" => 'BLACK MEDIUM SMALL SQUARE',
        "\xe2\x98\x80" => 'BLACK SUN WITH RAYS',
        "\xe2\x98\x81" => 'CLOUD',
        "\xe2\x98\x82" => 'UMBRELLA',
        "\xe2\x98\x83" => 'SNOWMAN',
        "\xe2\x98\x84" => 'COMET',
        "\xe2\x98\x8e" => 'BLACK TELEPHONE',
        "\xe2\x98\x91" => 'BALLOT BOX WITH CHECK',
        "\xe2\x98\x94" => 'UMBRELLA WITH RAIN DROPS',
        "\xe2\x98\x95" => 'HOT BEVERAGE',
        "\xe2\x98\x98" => 'SHAMROCK',
        "\xe2\x98\x9d" => 'WHITE UP POINTING INDEX',
        "\xe2\x98\xa0" => 'SKULL AND CROSSBONES',
        "\xe2\x98\xa2" => 'RADIOACTIVE SIGN',
        "\xe2\x98\xa3" => 'BIOHAZARD SIGN',
        "\xe2\x98\xa6" => 'ORTHODOX CROSS',
        "\xe2\x98\xaa" => 'STAR AND CRESCENT',
        "\xe2\x98\xae" => 'PEACE SYMBOL',
        "\xe2\x98\xaf" => 'YIN YANG',
        "\xe2\x98\xb8" => 'WHEEL OF DHARMA',
        "\xe2\x98\xb9" => 'WHITE FROWNING FACE',
        "\xe2\x98\xba" => 'WHITE SMILING FACE',
        "\xe2\x99\x88" => 'ARIES',
        "\xe2\x99\x89" => 'TAURUS',
        "\xe2\x99\x8a" => 'GEMINI',
        "\xe2\x99\x8b" => 'CANCER',
        "\xe2\x99\x8c" => 'LEO',
        "\xe2\x99\x8d" => 'VIRGO',
        "\xe2\x99\x8e" => 'LIBRA',
        "\xe2\x99\x8f" => 'SCORPIUS',
        "\xe2\x99\x90" => 'SAGITTARIUS',
        "\xe2\x99\x91" => 'CAPRICORN',
        "\xe2\x99\x92" => 'AQUARIUS',
        "\xe2\x99\x93" => 'PISCES',
        "\xe2\x99\xa0" => 'BLACK SPADE SUIT',
        "\xe2\x99\xa3" => 'BLACK CLUB SUIT',
        "\xe2\x99\xa5" => 'BLACK HEART SUIT',
        "\xe2\x99\xa6" => 'BLACK DIAMOND SUIT',
        "\xe2\x99\xa8" => 'HOT SPRINGS',
        "\xe2\x99\xbb" => 'BLACK UNIVERSAL RECYCLING SYMBOL',
        "\xe2\x99\xbf" => 'WHEELCHAIR SYMBOL',
        "\xe2\x9a\x92" => 'HAMMER AND PICK',
        "\xe2\x9a\x93" => 'ANCHOR',
        "\xe2\x9a\x94" => 'CROSSED SWORDS',
        "\xe2\x9a\x96" => 'SCALES',
        "\xe2\x9a\x97" => 'ALEMBIC',
        "\xe2\x9a\x99" => 'GEAR',
        "\xe2\x9a\x9b" => 'ATOM SYMBOL',
        "\xe2\x9a\x9c" => 'FLEUR-DE-LIS',
        "\xe2\x9a\xa0" => 'WARNING SIGN',
        "\xe2\x9a\xa1" => 'HIGH VOLTAGE SIGN',
        "\xe2\x9a\xaa" => 'MEDIUM WHITE CIRCLE',
        "\xe2\x9a\xab" => 'MEDIUM BLACK CIRCLE',
        "\xe2\x9a\xb0" => 'COFFIN',
        "\xe2\x9a\xb1" => 'FUNERAL URN',
        "\xe2\x9a\xbd" => 'SOCCER BALL',
        "\xe2\x9a\xbe" => 'BASEBALL',
        "\xe2\x9b\x84" => 'SNOWMAN WITHOUT SNOW',
        "\xe2\x9b\x85" => 'SUN BEHIND CLOUD',
        "\xe2\x9b\x88" => 'THUNDER CLOUD AND RAIN',
        "\xe2\x9b\x8e" => 'OPHIUCHUS',
        "\xe2\x9b\x8f" => 'PICK',
        "\xe2\x9b\x91" => 'HELMET WITH WHITE CROSS',
        "\xe2\x9b\x93" => 'CHAINS',
        "\xe2\x9b\x94" => 'NO ENTRY',
        "\xe2\x9b\xa9" => 'SHINTO SHRINE',
        "\xe2\x9b\xaa" => 'CHURCH',
        "\xe2\x9b\xb0" => 'MOUNTAIN',
        "\xe2\x9b\xb1" => 'UMBRELLA ON GROUND',
        "\xe2\x9b\xb2" => 'FOUNTAIN',
        "\xe2\x9b\xb3" => 'FLAG IN HOLE',
        "\xe2\x9b\xb4" => 'FERRY',
        "\xe2\x9b\xb5" => 'SAILBOAT',
        "\xe2\x9b\xb7" => 'SKIER',
        "\xe2\x9b\xb8" => 'ICE SKATE',
        "\xe2\x9b\xb9" => 'PERSON WITH BALL',
        "\xe2\x9b\xba" => 'TENT',
        "\xe2\x9b\xbd" => 'FUEL PUMP',
        "\xe2\x9c\x82" => 'BLACK SCISSORS',
        "\xe2\x9c\x85" => 'WHITE HEAVY CHECK MARK',
        "\xe2\x9c\x88" => 'AIRPLANE',
        "\xe2\x9c\x89" => 'ENVELOPE',
        "\xe2\x9c\x8a" => 'RAISED FIST',
        "\xe2\x9c\x8b" => 'RAISED HAND',
        "\xe2\x9c\x8c" => 'VICTORY HAND',
        "\xe2\x9c\x8d" => 'WRITING HAND',
        "\xe2\x9c\x8f" => 'PENCIL',
        "\xe2\x9c\x92" => 'BLACK NIB',
        "\xe2\x9c\x94" => 'HEAVY CHECK MARK',
        "\xe2\x9c\x96" => 'HEAVY MULTIPLICATION X',
        "\xe2\x9c\x9d" => 'LATIN CROSS',
        "\xe2\x9c\xa1" => 'STAR OF DAVID',
        "\xe2\x9c\xa8" => 'SPARKLES',
        "\xe2\x9c\xb3" => 'EIGHT SPOKED ASTERISK',
        "\xe2\x9c\xb4" => 'EIGHT POINTED BLACK STAR',
        "\xe2\x9d\x84" => 'SNOWFLAKE',
        "\xe2\x9d\x87" => 'SPARKLE',
        "\xe2\x9d\x8c" => 'CROSS MARK',
        "\xe2\x9d\x8e" => 'NEGATIVE SQUARED CROSS MARK',
        "\xe2\x9d\x93" => 'BLACK QUESTION MARK ORNAMENT',
        "\xe2\x9d\x94" => 'WHITE QUESTION MARK ORNAMENT',
        "\xe2\x9d\x95" => 'WHITE EXCLAMATION MARK ORNAMENT',
        "\xe2\x9d\x97" => 'HEAVY EXCLAMATION MARK SYMBOL',
        "\xe2\x9d\xa3" => 'HEAVY HEART EXCLAMATION MARK ORNAMENT',
        "\xe2\x9d\xa4" => 'HEAVY BLACK HEART',
        "\xe2\x9e\x95" => 'HEAVY PLUS SIGN',
        "\xe2\x9e\x96" => 'HEAVY MINUS SIGN',
        "\xe2\x9e\x97" => 'HEAVY DIVISION SIGN',
        "\xe2\x9e\xa1" => 'BLACK RIGHTWARDS ARROW',
        "\xe2\x9e\xb0" => 'CURLY LOOP',
        "\xe2\x9e\xbf" => 'DOUBLE CURLY LOOP',
        "\xe2\xa4\xb4" => 'ARROW POINTING RIGHTWARDS THEN CURVING UPWARDS',
        "\xe2\xa4\xb5" => 'ARROW POINTING RIGHTWARDS THEN CURVING DOWNWARDS',
        "\xe2\xac\x85" => 'LEFTWARDS BLACK ARROW',
        "\xe2\xac\x86" => 'UPWARDS BLACK ARROW',
        "\xe2\xac\x87" => 'DOWNWARDS BLACK ARROW',
        "\xe2\xac\x9b" => 'BLACK LARGE SQUARE',
        "\xe2\xac\x9c" => 'WHITE LARGE SQUARE',
        "\xe2\xad\x90" => 'WHITE MEDIUM STAR',
        "\xe2\xad\x95" => 'HEAVY LARGE CIRCLE',
        "\xe3\x80\xb0" => 'WAVY DASH',
        "\xe3\x80\xbd" => 'PART ALTERNATION MARK',
        "\xe3\x8a\x97" => 'CIRCLED IDEOGRAPH CONGRATULATION',
        "\xe3\x8a\x99" => 'CIRCLED IDEOGRAPH SECRET',
        "\xf0\x9f\x80\x84" => 'MAHJONG TILE RED DRAGON',
        "\xf0\x9f\x83\x8f" => 'PLAYING CARD BLACK JOKER',
        "\xf0\x9f\x85\xb0" => 'NEGATIVE SQUARED LATIN CAPITAL LETTER A',
        "\xf0\x9f\x85\xb1" => 'NEGATIVE SQUARED LATIN CAPITAL LETTER B',
        "\xf0\x9f\x85\xbe" => 'NEGATIVE SQUARED LATIN CAPITAL LETTER O',
        "\xf0\x9f\x85\xbf" => 'NEGATIVE SQUARED LATIN CAPITAL LETTER P',
        "\xf0\x9f\x86\x8e" => 'NEGATIVE SQUARED AB',
        "\xf0\x9f\x86\x91" => 'SQUARED CL',
        "\xf0\x9f\x86\x92" => 'SQUARED COOL',
        "\xf0\x9f\x86\x93" => 'SQUARED FREE',
        "\xf0\x9f\x86\x94" => 'SQUARED ID',
        "\xf0\x9f\x86\x95" => 'SQUARED NEW',
        "\xf0\x9f\x86\x96" => 'SQUARED NG',
        "\xf0\x9f\x86\x97" => 'SQUARED OK',
        "\xf0\x9f\x86\x98" => 'SQUARED SOS',
        "\xf0\x9f\x86\x99" => 'SQUARED UP WITH EXCLAMATION MARK',
        "\xf0\x9f\x86\x9a" => 'SQUARED VS',
        "\xf0\x9f\x88\x81" => 'SQUARED KATAKANA KOKO',
        "\xf0\x9f\x88\x82" => 'SQUARED KATAKANA SA',
        "\xf0\x9f\x88\x9a" => 'SQUARED CJK UNIFIED IDEOGRAPH-7121',
        "\xf0\x9f\x88\xaf" => 'SQUARED CJK UNIFIED IDEOGRAPH-6307',
        "\xf0\x9f\x88\xb2" => 'SQUARED CJK UNIFIED IDEOGRAPH-7981',
        "\xf0\x9f\x88\xb3" => 'SQUARED CJK UNIFIED IDEOGRAPH-7A7A',
        "\xf0\x9f\x88\xb4" => 'SQUARED CJK UNIFIED IDEOGRAPH-5408',
        "\xf0\x9f\x88\xb5" => 'SQUARED CJK UNIFIED IDEOGRAPH-6E80',
        "\xf0\x9f\x88\xb6" => 'SQUARED CJK UNIFIED IDEOGRAPH-6709',
        "\xf0\x9f\x88\xb7" => 'SQUARED CJK UNIFIED IDEOGRAPH-6708',
        "\xf0\x9f\x88\xb8" => 'SQUARED CJK UNIFIED IDEOGRAPH-7533',
        "\xf0\x9f\x88\xb9" => 'SQUARED CJK UNIFIED IDEOGRAPH-5272',
        "\xf0\x9f\x88\xba" => 'SQUARED CJK UNIFIED IDEOGRAPH-55B6',
        "\xf0\x9f\x89\x90" => 'CIRCLED IDEOGRAPH ADVANTAGE',
        "\xf0\x9f\x89\x91" => 'CIRCLED IDEOGRAPH ACCEPT',
        "\xf0\x9f\x8c\x80" => 'CYCLONE',
        "\xf0\x9f\x8c\x81" => 'FOGGY',
        "\xf0\x9f\x8c\x82" => 'CLOSED UMBRELLA',
        "\xf0\x9f\x8c\x83" => 'NIGHT WITH STARS',
        "\xf0\x9f\x8c\x84" => 'SUNRISE OVER MOUNTAINS',
        "\xf0\x9f\x8c\x85" => 'SUNRISE',
        "\xf0\x9f\x8c\x86" => 'CITYSCAPE AT DUSK',
        "\xf0\x9f\x8c\x87" => 'SUNSET OVER BUILDINGS',
        "\xf0\x9f\x8c\x88" => 'RAINBOW',
        "\xf0\x9f\x8c\x89" => 'BRIDGE AT NIGHT',
        "\xf0\x9f\x8c\x8a" => 'WATER WAVE',
        "\xf0\x9f\x8c\x8b" => 'VOLCANO',
        "\xf0\x9f\x8c\x8c" => 'MILKY WAY',
        "\xf0\x9f\x8c\x8d" => 'EARTH GLOBE EUROPE-AFRICA',
        "\xf0\x9f\x8c\x8e" => 'EARTH GLOBE AMERICAS',
        "\xf0\x9f\x8c\x8f" => 'EARTH GLOBE ASIA-AUSTRALIA',
        "\xf0\x9f\x8c\x90" => 'GLOBE WITH MERIDIANS',
        "\xf0\x9f\x8c\x91" => 'NEW MOON SYMBOL',
        "\xf0\x9f\x8c\x92" => 'WAXING CRESCENT MOON SYMBOL',
        "\xf0\x9f\x8c\x93" => 'FIRST QUARTER MOON SYMBOL',
        "\xf0\x9f\x8c\x94" => 'WAXING GIBBOUS MOON SYMBOL',
        "\xf0\x9f\x8c\x95" => 'FULL MOON SYMBOL',
        "\xf0\x9f\x8c\x96" => 'WANING GIBBOUS MOON SYMBOL',
        "\xf0\x9f\x8c\x97" => 'LAST QUARTER MOON SYMBOL',
        "\xf0\x9f\x8c\x98" => 'WANING CRESCENT MOON SYMBOL',
        "\xf0\x9f\x8c\x99" => 'CRESCENT MOON',
        "\xf0\x9f\x8c\x9a" => 'NEW MOON WITH FACE',
        "\xf0\x9f\x8c\x9b" => 'FIRST QUARTER MOON WITH FACE',
        "\xf0\x9f\x8c\x9c" => 'LAST QUARTER MOON WITH FACE',
        "\xf0\x9f\x8c\x9d" => 'FULL MOON WITH FACE',
        "\xf0\x9f\x8c\x9e" => 'SUN WITH FACE',
        "\xf0\x9f\x8c\x9f" => 'GLOWING STAR',
        "\xf0\x9f\x8c\xa0" => 'SHOOTING STAR',
        "\xf0\x9f\x8c\xa1" => 'THERMOMETER',
        "\xf0\x9f\x8c\xa4" => 'WHITE SUN WITH SMALL CLOUD',
        "\xf0\x9f\x8c\xa5" => 'WHITE SUN BEHIND CLOUD',
        "\xf0\x9f\x8c\xa6" => 'WHITE SUN BEHIND CLOUD WITH RAIN',
        "\xf0\x9f\x8c\xa7" => 'CLOUD WITH RAIN',
        "\xf0\x9f\x8c\xa8" => 'CLOUD WITH SNOW',
        "\xf0\x9f\x8c\xa9" => 'CLOUD WITH LIGHTNING',
        "\xf0\x9f\x8c\xaa" => 'CLOUD WITH TORNADO',
        "\xf0\x9f\x8c\xab" => 'FOG',
        "\xf0\x9f\x8c\xac" => 'WIND BLOWING FACE',
        "\xf0\x9f\x8c\xad" => 'HOT DOG',
        "\xf0\x9f\x8c\xae" => 'TACO',
        "\xf0\x9f\x8c\xaf" => 'BURRITO',
        "\xf0\x9f\x8c\xb0" => 'CHESTNUT',
        "\xf0\x9f\x8c\xb1" => 'SEEDLING',
        "\xf0\x9f\x8c\xb2" => 'EVERGREEN TREE',
        "\xf0\x9f\x8c\xb3" => 'DECIDUOUS TREE',
        "\xf0\x9f\x8c\xb4" => 'PALM TREE',
        "\xf0\x9f\x8c\xb5" => 'CACTUS',
        "\xf0\x9f\x8c\xb6" => 'HOT PEPPER',
        "\xf0\x9f\x8c\xb7" => 'TULIP',
        "\xf0\x9f\x8c\xb8" => 'CHERRY BLOSSOM',
        "\xf0\x9f\x8c\xb9" => 'ROSE',
        "\xf0\x9f\x8c\xba" => 'HIBISCUS',
        "\xf0\x9f\x8c\xbb" => 'SUNFLOWER',
        "\xf0\x9f\x8c\xbc" => 'BLOSSOM',
        "\xf0\x9f\x8c\xbd" => 'EAR OF MAIZE',
        "\xf0\x9f\x8c\xbe" => 'EAR OF RICE',
        "\xf0\x9f\x8c\xbf" => 'HERB',
        "\xf0\x9f\x8d\x80" => 'FOUR LEAF CLOVER',
        "\xf0\x9f\x8d\x81" => 'MAPLE LEAF',
        "\xf0\x9f\x8d\x82" => 'FALLEN LEAF',
        "\xf0\x9f\x8d\x83" => 'LEAF FLUTTERING IN WIND',
        "\xf0\x9f\x8d\x84" => 'MUSHROOM',
        "\xf0\x9f\x8d\x85" => 'TOMATO',
        "\xf0\x9f\x8d\x86" => 'AUBERGINE',
        "\xf0\x9f\x8d\x87" => 'GRAPES',
        "\xf0\x9f\x8d\x88" => 'MELON',
        "\xf0\x9f\x8d\x89" => 'WATERMELON',
        "\xf0\x9f\x8d\x8a" => 'TANGERINE',
        "\xf0\x9f\x8d\x8b" => 'LEMON',
        "\xf0\x9f\x8d\x8c" => 'BANANA',
        "\xf0\x9f\x8d\x8d" => 'PINEAPPLE',
        "\xf0\x9f\x8d\x8e" => 'RED APPLE',
        "\xf0\x9f\x8d\x8f" => 'GREEN APPLE',
        "\xf0\x9f\x8d\x90" => 'PEAR',
        "\xf0\x9f\x8d\x91" => 'PEACH',
        "\xf0\x9f\x8d\x92" => 'CHERRIES',
        "\xf0\x9f\x8d\x93" => 'STRAWBERRY',
        "\xf0\x9f\x8d\x94" => 'HAMBURGER',
        "\xf0\x9f\x8d\x95" => 'SLICE OF PIZZA',
        "\xf0\x9f\x8d\x96" => 'MEAT ON BONE',
        "\xf0\x9f\x8d\x97" => 'POULTRY LEG',
        "\xf0\x9f\x8d\x98" => 'RICE CRACKER',
        "\xf0\x9f\x8d\x99" => 'RICE BALL',
        "\xf0\x9f\x8d\x9a" => 'COOKED RICE',
        "\xf0\x9f\x8d\x9b" => 'CURRY AND RICE',
        "\xf0\x9f\x8d\x9c" => 'STEAMING BOWL',
        "\xf0\x9f\x8d\x9d" => 'SPAGHETTI',
        "\xf0\x9f\x8d\x9e" => 'BREAD',
        "\xf0\x9f\x8d\x9f" => 'FRENCH FRIES',
        "\xf0\x9f\x8d\xa0" => 'ROASTED SWEET POTATO',
        "\xf0\x9f\x8d\xa1" => 'DANGO',
        "\xf0\x9f\x8d\xa2" => 'ODEN',
        "\xf0\x9f\x8d\xa3" => 'SUSHI',
        "\xf0\x9f\x8d\xa4" => 'FRIED SHRIMP',
        "\xf0\x9f\x8d\xa5" => 'FISH CAKE WITH SWIRL DESIGN',
        "\xf0\x9f\x8d\xa6" => 'SOFT ICE CREAM',
        "\xf0\x9f\x8d\xa7" => 'SHAVED ICE',
        "\xf0\x9f\x8d\xa8" => 'ICE CREAM',
        "\xf0\x9f\x8d\xa9" => 'DOUGHNUT',
        "\xf0\x9f\x8d\xaa" => 'COOKIE',
        "\xf0\x9f\x8d\xab" => 'CHOCOLATE BAR',
        "\xf0\x9f\x8d\xac" => 'CANDY',
        "\xf0\x9f\x8d\xad" => 'LOLLIPOP',
        "\xf0\x9f\x8d\xae" => 'CUSTARD',
        "\xf0\x9f\x8d\xaf" => 'HONEY POT',
        "\xf0\x9f\x8d\xb0" => 'SHORTCAKE',
        "\xf0\x9f\x8d\xb1" => 'BENTO BOX',
        "\xf0\x9f\x8d\xb2" => 'POT OF FOOD',
        "\xf0\x9f\x8d\xb3" => 'COOKING',
        "\xf0\x9f\x8d\xb4" => 'FORK AND KNIFE',
        "\xf0\x9f\x8d\xb5" => 'TEACUP WITHOUT HANDLE',
        "\xf0\x9f\x8d\xb6" => 'SAKE BOTTLE AND CUP',
        "\xf0\x9f\x8d\xb7" => 'WINE GLASS',
        "\xf0\x9f\x8d\xb8" => 'COCKTAIL GLASS',
        "\xf0\x9f\x8d\xb9" => 'TROPICAL DRINK',
        "\xf0\x9f\x8d\xba" => 'BEER MUG',
        "\xf0\x9f\x8d\xbb" => 'CLINKING BEER MUGS',
        "\xf0\x9f\x8d\xbc" => 'BABY BOTTLE',
        "\xf0\x9f\x8d\xbd" => 'FORK AND KNIFE WITH PLATE',
        "\xf0\x9f\x8d\xbe" => 'BOTTLE WITH POPPING CORK',
        "\xf0\x9f\x8d\xbf" => 'POPCORN',
        "\xf0\x9f\x8e\x80" => 'RIBBON',
        "\xf0\x9f\x8e\x81" => 'WRAPPED PRESENT',
        "\xf0\x9f\x8e\x82" => 'BIRTHDAY CAKE',
        "\xf0\x9f\x8e\x83" => 'JACK-O-LANTERN',
        "\xf0\x9f\x8e\x84" => 'CHRISTMAS TREE',
        "\xf0\x9f\x8e\x85" => 'FATHER CHRISTMAS',
        "\xf0\x9f\x8e\x86" => 'FIREWORKS',
        "\xf0\x9f\x8e\x87" => 'FIREWORK SPARKLER',
        "\xf0\x9f\x8e\x88" => 'BALLOON',
        "\xf0\x9f\x8e\x89" => 'PARTY POPPER',
        "\xf0\x9f\x8e\x8a" => 'CONFETTI BALL',
        "\xf0\x9f\x8e\x8b" => 'TANABATA TREE',
        "\xf0\x9f\x8e\x8c" => 'CROSSED FLAGS',
        "\xf0\x9f\x8e\x8d" => 'PINE DECORATION',
        "\xf0\x9f\x8e\x8e" => 'JAPANESE DOLLS',
        "\xf0\x9f\x8e\x8f" => 'CARP STREAMER',
        "\xf0\x9f\x8e\x90" => 'WIND CHIME',
        "\xf0\x9f\x8e\x91" => 'MOON VIEWING CEREMONY',
        "\xf0\x9f\x8e\x92" => 'SCHOOL SATCHEL',
        "\xf0\x9f\x8e\x93" => 'GRADUATION CAP',
        "\xf0\x9f\x8e\x96" => 'MILITARY MEDAL',
        "\xf0\x9f\x8e\x97" => 'REMINDER RIBBON',
        "\xf0\x9f\x8e\x99" => 'STUDIO MICROPHONE',
        "\xf0\x9f\x8e\x9a" => 'LEVEL SLIDER',
        "\xf0\x9f\x8e\x9b" => 'CONTROL KNOBS',
        "\xf0\x9f\x8e\x9e" => 'FILM FRAMES',
        "\xf0\x9f\x8e\x9f" => 'ADMISSION TICKETS',
        "\xf0\x9f\x8e\xa0" => 'CAROUSEL HORSE',
        "\xf0\x9f\x8e\xa1" => 'FERRIS WHEEL',
        "\xf0\x9f\x8e\xa2" => 'ROLLER COASTER',
        "\xf0\x9f\x8e\xa3" => 'FISHING POLE AND FISH',
        "\xf0\x9f\x8e\xa4" => 'MICROPHONE',
        "\xf0\x9f\x8e\xa5" => 'MOVIE CAMERA',
        "\xf0\x9f\x8e\xa6" => 'CINEMA',
        "\xf0\x9f\x8e\xa7" => 'HEADPHONE',
        "\xf0\x9f\x8e\xa8" => 'ARTIST PALETTE',
        "\xf0\x9f\x8e\xa9" => 'TOP HAT',
        "\xf0\x9f\x8e\xaa" => 'CIRCUS TENT',
        "\xf0\x9f\x8e\xab" => 'TICKET',
        "\xf0\x9f\x8e\xac" => 'CLAPPER BOARD',
        "\xf0\x9f\x8e\xad" => 'PERFORMING ARTS',
        "\xf0\x9f\x8e\xae" => 'VIDEO GAME',
        "\xf0\x9f\x8e\xaf" => 'DIRECT HIT',
        "\xf0\x9f\x8e\xb0" => 'SLOT MACHINE',
        "\xf0\x9f\x8e\xb1" => 'BILLIARDS',
        "\xf0\x9f\x8e\xb2" => 'GAME DIE',
        "\xf0\x9f\x8e\xb3" => 'BOWLING',
        "\xf0\x9f\x8e\xb4" => 'FLOWER PLAYING CARDS',
        "\xf0\x9f\x8e\xb5" => 'MUSICAL NOTE',
        "\xf0\x9f\x8e\xb6" => 'MULTIPLE MUSICAL NOTES',
        "\xf0\x9f\x8e\xb7" => 'SAXOPHONE',
        "\xf0\x9f\x8e\xb8" => 'GUITAR',
        "\xf0\x9f\x8e\xb9" => 'MUSICAL KEYBOARD',
        "\xf0\x9f\x8e\xba" => 'TRUMPET',
        "\xf0\x9f\x8e\xbb" => 'VIOLIN',
        "\xf0\x9f\x8e\xbc" => 'MUSICAL SCORE',
        "\xf0\x9f\x8e\xbd" => 'RUNNING SHIRT WITH SASH',
        "\xf0\x9f\x8e\xbe" => 'TENNIS RACQUET AND BALL',
        "\xf0\x9f\x8e\xbf" => 'SKI AND SKI BOOT',
        "\xf0\x9f\x8f\x80" => 'BASKETBALL AND HOOP',
        "\xf0\x9f\x8f\x81" => 'CHEQUERED FLAG',
        "\xf0\x9f\x8f\x82" => 'SNOWBOARDER',
        "\xf0\x9f\x8f\x83" => 'RUNNER',
        "\xf0\x9f\x8f\x84" => 'SURFER',
        "\xf0\x9f\x8f\x85" => 'SPORTS MEDAL',
        "\xf0\x9f\x8f\x86" => 'TROPHY',
        "\xf0\x9f\x8f\x87" => 'HORSE RACING',
        "\xf0\x9f\x8f\x88" => 'AMERICAN FOOTBALL',
        "\xf0\x9f\x8f\x89" => 'RUGBY FOOTBALL',
        "\xf0\x9f\x8f\x8a" => 'SWIMMER',
        "\xf0\x9f\x8f\x8b" => 'WEIGHT LIFTER',
        "\xf0\x9f\x8f\x8c" => 'GOLFER',
        "\xf0\x9f\x8f\x8d" => 'RACING MOTORCYCLE',
        "\xf0\x9f\x8f\x8e" => 'RACING CAR',
        "\xf0\x9f\x8f\x8f" => 'CRICKET BAT AND BALL',
        "\xf0\x9f\x8f\x90" => 'VOLLEYBALL',
        "\xf0\x9f\x8f\x91" => 'FIELD HOCKEY STICK AND BALL',
        "\xf0\x9f\x8f\x92" => 'ICE HOCKEY STICK AND PUCK',
        "\xf0\x9f\x8f\x93" => 'TABLE TENNIS PADDLE AND BALL',
        "\xf0\x9f\x8f\x94" => 'SNOW CAPPED MOUNTAIN',
        "\xf0\x9f\x8f\x95" => 'CAMPING',
        "\xf0\x9f\x8f\x96" => 'BEACH WITH UMBRELLA',
        "\xf0\x9f\x8f\x97" => 'BUILDING CONSTRUCTION',
        "\xf0\x9f\x8f\x98" => 'HOUSE BUILDINGS',
        "\xf0\x9f\x8f\x99" => 'CITYSCAPE',
        "\xf0\x9f\x8f\x9a" => 'DERELICT HOUSE BUILDING',
        "\xf0\x9f\x8f\x9b" => 'CLASSICAL BUILDING',
        "\xf0\x9f\x8f\x9c" => 'DESERT',
        "\xf0\x9f\x8f\x9d" => 'DESERT ISLAND',
        "\xf0\x9f\x8f\x9e" => 'NATIONAL PARK',
        "\xf0\x9f\x8f\x9f" => 'STADIUM',
        "\xf0\x9f\x8f\xa0" => 'HOUSE BUILDING',
        "\xf0\x9f\x8f\xa1" => 'HOUSE WITH GARDEN',
        "\xf0\x9f\x8f\xa2" => 'OFFICE BUILDING',
        "\xf0\x9f\x8f\xa3" => 'JAPANESE POST OFFICE',
        "\xf0\x9f\x8f\xa4" => 'EUROPEAN POST OFFICE',
        "\xf0\x9f\x8f\xa5" => 'HOSPITAL',
        "\xf0\x9f\x8f\xa6" => 'BANK',
        "\xf0\x9f\x8f\xa7" => 'AUTOMATED TELLER MACHINE',
        "\xf0\x9f\x8f\xa8" => 'HOTEL',
        "\xf0\x9f\x8f\xa9" => 'LOVE HOTEL',
        "\xf0\x9f\x8f\xaa" => 'CONVENIENCE STORE',
        "\xf0\x9f\x8f\xab" => 'SCHOOL',
        "\xf0\x9f\x8f\xac" => 'DEPARTMENT STORE',
        "\xf0\x9f\x8f\xad" => 'FACTORY',
        "\xf0\x9f\x8f\xae" => 'IZAKAYA LANTERN',
        "\xf0\x9f\x8f\xaf" => 'JAPANESE CASTLE',
        "\xf0\x9f\x8f\xb0" => 'EUROPEAN CASTLE',
        "\xf0\x9f\x8f\xb3" => 'WAVING WHITE FLAG',
        "\xf0\x9f\x8f\xb4" => 'WAVING BLACK FLAG',
        "\xf0\x9f\x8f\xb5" => 'ROSETTE',
        "\xf0\x9f\x8f\xb7" => 'LABEL',
        "\xf0\x9f\x8f\xb8" => 'BADMINTON RACQUET AND SHUTTLECOCK',
        "\xf0\x9f\x8f\xb9" => 'BOW AND ARROW',
        "\xf0\x9f\x8f\xba" => 'AMPHORA',
        "\xf0\x9f\x8f\xbb" => 'EMOJI MODIFIER FITZPATRICK TYPE-1-2',
        "\xf0\x9f\x8f\xbc" => 'EMOJI MODIFIER FITZPATRICK TYPE-3',
        "\xf0\x9f\x8f\xbd" => 'EMOJI MODIFIER FITZPATRICK TYPE-4',
        "\xf0\x9f\x8f\xbe" => 'EMOJI MODIFIER FITZPATRICK TYPE-5',
        "\xf0\x9f\x8f\xbf" => 'EMOJI MODIFIER FITZPATRICK TYPE-6',
        "\xf0\x9f\x90\x80" => 'RAT',
        "\xf0\x9f\x90\x81" => 'MOUSE',
        "\xf0\x9f\x90\x82" => 'OX',
        "\xf0\x9f\x90\x83" => 'WATER BUFFALO',
        "\xf0\x9f\x90\x84" => 'COW',
        "\xf0\x9f\x90\x85" => 'TIGER',
        "\xf0\x9f\x90\x86" => 'LEOPARD',
        "\xf0\x9f\x90\x87" => 'RABBIT',
        "\xf0\x9f\x90\x88" => 'CAT',
        "\xf0\x9f\x90\x89" => 'DRAGON',
        "\xf0\x9f\x90\x8a" => 'CROCODILE',
        "\xf0\x9f\x90\x8b" => 'WHALE',
        "\xf0\x9f\x90\x8c" => 'SNAIL',
        "\xf0\x9f\x90\x8d" => 'SNAKE',
        "\xf0\x9f\x90\x8e" => 'HORSE',
        "\xf0\x9f\x90\x8f" => 'RAM',
        "\xf0\x9f\x90\x90" => 'GOAT',
        "\xf0\x9f\x90\x91" => 'SHEEP',
        "\xf0\x9f\x90\x92" => 'MONKEY',
        "\xf0\x9f\x90\x93" => 'ROOSTER',
        "\xf0\x9f\x90\x94" => 'CHICKEN',
        "\xf0\x9f\x90\x95" => 'DOG',
        "\xf0\x9f\x90\x96" => 'PIG',
        "\xf0\x9f\x90\x97" => 'BOAR',
        "\xf0\x9f\x90\x98" => 'ELEPHANT',
        "\xf0\x9f\x90\x99" => 'OCTOPUS',
        "\xf0\x9f\x90\x9a" => 'SPIRAL SHELL',
        "\xf0\x9f\x90\x9b" => 'BUG',
        "\xf0\x9f\x90\x9c" => 'ANT',
        "\xf0\x9f\x90\x9d" => 'HONEYBEE',
        "\xf0\x9f\x90\x9e" => 'LADY BEETLE',
        "\xf0\x9f\x90\x9f" => 'FISH',
        "\xf0\x9f\x90\xa0" => 'TROPICAL FISH',
        "\xf0\x9f\x90\xa1" => 'BLOWFISH',
        "\xf0\x9f\x90\xa2" => 'TURTLE',
        "\xf0\x9f\x90\xa3" => 'HATCHING CHICK',
        "\xf0\x9f\x90\xa4" => 'BABY CHICK',
        "\xf0\x9f\x90\xa5" => 'FRONT-FACING BABY CHICK',
        "\xf0\x9f\x90\xa6" => 'BIRD',
        "\xf0\x9f\x90\xa7" => 'PENGUIN',
        "\xf0\x9f\x90\xa8" => 'KOALA',
        "\xf0\x9f\x90\xa9" => 'POODLE',
        "\xf0\x9f\x90\xaa" => 'DROMEDARY CAMEL',
        "\xf0\x9f\x90\xab" => 'BACTRIAN CAMEL',
        "\xf0\x9f\x90\xac" => 'DOLPHIN',
        "\xf0\x9f\x90\xad" => 'MOUSE FACE',
        "\xf0\x9f\x90\xae" => 'COW FACE',
        "\xf0\x9f\x90\xaf" => 'TIGER FACE',
        "\xf0\x9f\x90\xb0" => 'RABBIT FACE',
        "\xf0\x9f\x90\xb1" => 'CAT FACE',
        "\xf0\x9f\x90\xb2" => 'DRAGON FACE',
        "\xf0\x9f\x90\xb3" => 'SPOUTING WHALE',
        "\xf0\x9f\x90\xb4" => 'HORSE FACE',
        "\xf0\x9f\x90\xb5" => 'MONKEY FACE',
        "\xf0\x9f\x90\xb6" => 'DOG FACE',
        "\xf0\x9f\x90\xb7" => 'PIG FACE',
        "\xf0\x9f\x90\xb8" => 'FROG FACE',
        "\xf0\x9f\x90\xb9" => 'HAMSTER FACE',
        "\xf0\x9f\x90\xba" => 'WOLF FACE',
        "\xf0\x9f\x90\xbb" => 'BEAR FACE',
        "\xf0\x9f\x90\xbc" => 'PANDA FACE',
        "\xf0\x9f\x90\xbd" => 'PIG NOSE',
        "\xf0\x9f\x90\xbe" => 'PAW PRINTS',
        "\xf0\x9f\x90\xbf" => 'CHIPMUNK',
        "\xf0\x9f\x91\x80" => 'EYES',
        "\xf0\x9f\x91\x81" => 'EYE',
        "\xf0\x9f\x91\x82" => 'EAR',
        "\xf0\x9f\x91\x83" => 'NOSE',
        "\xf0\x9f\x91\x84" => 'MOUTH',
        "\xf0\x9f\x91\x85" => 'TONGUE',
        "\xf0\x9f\x91\x86" => 'WHITE UP POINTING BACKHAND INDEX',
        "\xf0\x9f\x91\x87" => 'WHITE DOWN POINTING BACKHAND INDEX',
        "\xf0\x9f\x91\x88" => 'WHITE LEFT POINTING BACKHAND INDEX',
        "\xf0\x9f\x91\x89" => 'WHITE RIGHT POINTING BACKHAND INDEX',
        "\xf0\x9f\x91\x8a" => 'FISTED HAND SIGN',
        "\xf0\x9f\x91\x8b" => 'WAVING HAND SIGN',
        "\xf0\x9f\x91\x8c" => 'OK HAND SIGN',
        "\xf0\x9f\x91\x8d" => 'THUMBS UP SIGN',
        "\xf0\x9f\x91\x8e" => 'THUMBS DOWN SIGN',
        "\xf0\x9f\x91\x8f" => 'CLAPPING HANDS SIGN',
        "\xf0\x9f\x91\x90" => 'OPEN HANDS SIGN',
        "\xf0\x9f\x91\x91" => 'CROWN',
        "\xf0\x9f\x91\x92" => 'WOMANS HAT',
        "\xf0\x9f\x91\x93" => 'EYEGLASSES',
        "\xf0\x9f\x91\x94" => 'NECKTIE',
        "\xf0\x9f\x91\x95" => 'T-SHIRT',
        "\xf0\x9f\x91\x96" => 'JEANS',
        "\xf0\x9f\x91\x97" => 'DRESS',
        "\xf0\x9f\x91\x98" => 'KIMONO',
        "\xf0\x9f\x91\x99" => 'BIKINI',
        "\xf0\x9f\x91\x9a" => 'WOMANS CLOTHES',
        "\xf0\x9f\x91\x9b" => 'PURSE',
        "\xf0\x9f\x91\x9c" => 'HANDBAG',
        "\xf0\x9f\x91\x9d" => 'POUCH',
        "\xf0\x9f\x91\x9e" => 'MANS SHOE',
        "\xf0\x9f\x91\x9f" => 'ATHLETIC SHOE',
        "\xf0\x9f\x91\xa0" => 'HIGH-HEELED SHOE',
        "\xf0\x9f\x91\xa1" => 'WOMANS SANDAL',
        "\xf0\x9f\x91\xa2" => 'WOMANS BOOTS',
        "\xf0\x9f\x91\xa3" => 'FOOTPRINTS',
        "\xf0\x9f\x91\xa4" => 'BUST IN SILHOUETTE',
        "\xf0\x9f\x91\xa5" => 'BUSTS IN SILHOUETTE',
        "\xf0\x9f\x91\xa6" => 'BOY',
        "\xf0\x9f\x91\xa7" => 'GIRL',
        "\xf0\x9f\x91\xa8" => 'MAN',
        "\xf0\x9f\x91\xa9" => 'WOMAN',
        "\xf0\x9f\x91\xaa" => 'FAMILY',
        "\xf0\x9f\x91\xab" => 'MAN AND WOMAN HOLDING HANDS',
        "\xf0\x9f\x91\xac" => 'TWO MEN HOLDING HANDS',
        "\xf0\x9f\x91\xad" => 'TWO WOMEN HOLDING HANDS',
        "\xf0\x9f\x91\xae" => 'POLICE OFFICER',
        "\xf0\x9f\x91\xaf" => 'WOMAN WITH BUNNY EARS',
        "\xf0\x9f\x91\xb0" => 'BRIDE WITH VEIL',
        "\xf0\x9f\x91\xb1" => 'PERSON WITH BLOND HAIR',
        "\xf0\x9f\x91\xb2" => 'MAN WITH GUA PI MAO',
        "\xf0\x9f\x91\xb3" => 'MAN WITH TURBAN',
        "\xf0\x9f\x91\xb4" => 'OLDER MAN',
        "\xf0\x9f\x91\xb5" => 'OLDER WOMAN',
        "\xf0\x9f\x91\xb6" => 'BABY',
        "\xf0\x9f\x91\xb7" => 'CONSTRUCTION WORKER',
        "\xf0\x9f\x91\xb8" => 'PRINCESS',
        "\xf0\x9f\x91\xb9" => 'JAPANESE OGRE',
        "\xf0\x9f\x91\xba" => 'JAPANESE GOBLIN',
        "\xf0\x9f\x91\xbb" => 'GHOST',
        "\xf0\x9f\x91\xbc" => 'BABY ANGEL',
        "\xf0\x9f\x91\xbd" => 'EXTRATERRESTRIAL ALIEN',
        "\xf0\x9f\x91\xbe" => 'ALIEN MONSTER',
        "\xf0\x9f\x91\xbf" => 'IMP',
        "\xf0\x9f\x92\x80" => 'SKULL',
        "\xf0\x9f\x92\x81" => 'INFORMATION DESK PERSON',
        "\xf0\x9f\x92\x82" => 'GUARDSMAN',
        "\xf0\x9f\x92\x83" => 'DANCER',
        "\xf0\x9f\x92\x84" => 'LIPSTICK',
        "\xf0\x9f\x92\x85" => 'NAIL POLISH',
        "\xf0\x9f\x92\x86" => 'FACE MASSAGE',
        "\xf0\x9f\x92\x87" => 'HAIRCUT',
        "\xf0\x9f\x92\x88" => 'BARBER POLE',
        "\xf0\x9f\x92\x89" => 'SYRINGE',
        "\xf0\x9f\x92\x8a" => 'PILL',
        "\xf0\x9f\x92\x8b" => 'KISS MARK',
        "\xf0\x9f\x92\x8c" => 'LOVE LETTER',
        "\xf0\x9f\x92\x8d" => 'RING',
        "\xf0\x9f\x92\x8e" => 'GEM STONE',
        "\xf0\x9f\x92\x8f" => 'KISS',
        "\xf0\x9f\x92\x90" => 'BOUQUET',
        "\xf0\x9f\x92\x91" => 'COUPLE WITH HEART',
        "\xf0\x9f\x92\x92" => 'WEDDING',
        "\xf0\x9f\x92\x93" => 'BEATING HEART',
        "\xf0\x9f\x92\x94" => 'BROKEN HEART',
        "\xf0\x9f\x92\x95" => 'TWO HEARTS',
        "\xf0\x9f\x92\x96" => 'SPARKLING HEART',
        "\xf0\x9f\x92\x97" => 'GROWING HEART',
        "\xf0\x9f\x92\x98" => 'HEART WITH ARROW',
        "\xf0\x9f\x92\x99" => 'BLUE HEART',
        "\xf0\x9f\x92\x9a" => 'GREEN HEART',
        "\xf0\x9f\x92\x9b" => 'YELLOW HEART',
        "\xf0\x9f\x92\x9c" => 'PURPLE HEART',
        "\xf0\x9f\x92\x9d" => 'HEART WITH RIBBON',
        "\xf0\x9f\x92\x9e" => 'REVOLVING HEARTS',
        "\xf0\x9f\x92\x9f" => 'HEART DECORATION',
        "\xf0\x9f\x92\xa0" => 'DIAMOND SHAPE WITH A DOT INSIDE',
        "\xf0\x9f\x92\xa1" => 'ELECTRIC LIGHT BULB',
        "\xf0\x9f\x92\xa2" => 'ANGER SYMBOL',
        "\xf0\x9f\x92\xa3" => 'BOMB',
        "\xf0\x9f\x92\xa4" => 'SLEEPING SYMBOL',
        "\xf0\x9f\x92\xa5" => 'COLLISION SYMBOL',
        "\xf0\x9f\x92\xa6" => 'SPLASHING SWEAT SYMBOL',
        "\xf0\x9f\x92\xa7" => 'DROPLET',
        "\xf0\x9f\x92\xa8" => 'DASH SYMBOL',
        "\xf0\x9f\x92\xa9" => 'PILE OF POO',
        "\xf0\x9f\x92\xaa" => 'FLEXED BICEPS',
        "\xf0\x9f\x92\xab" => 'DIZZY SYMBOL',
        "\xf0\x9f\x92\xac" => 'SPEECH BALLOON',
        "\xf0\x9f\x92\xad" => 'THOUGHT BALLOON',
        "\xf0\x9f\x92\xae" => 'WHITE FLOWER',
        "\xf0\x9f\x92\xaf" => 'HUNDRED POINTS SYMBOL',
        "\xf0\x9f\x92\xb0" => 'MONEY BAG',
        "\xf0\x9f\x92\xb1" => 'CURRENCY EXCHANGE',
        "\xf0\x9f\x92\xb2" => 'HEAVY DOLLAR SIGN',
        "\xf0\x9f\x92\xb3" => 'CREDIT CARD',
        "\xf0\x9f\x92\xb4" => 'BANKNOTE WITH YEN SIGN',
        "\xf0\x9f\x92\xb5" => 'BANKNOTE WITH DOLLAR SIGN',
        "\xf0\x9f\x92\xb6" => 'BANKNOTE WITH EURO SIGN',
        "\xf0\x9f\x92\xb7" => 'BANKNOTE WITH POUND SIGN',
        "\xf0\x9f\x92\xb8" => 'MONEY WITH WINGS',
        "\xf0\x9f\x92\xb9" => 'CHART WITH UPWARDS TREND AND YEN SIGN',
        "\xf0\x9f\x92\xba" => 'SEAT',
        "\xf0\x9f\x92\xbb" => 'PERSONAL COMPUTER',
        "\xf0\x9f\x92\xbc" => 'BRIEFCASE',
        "\xf0\x9f\x92\xbd" => 'MINIDISC',
        "\xf0\x9f\x92\xbe" => 'FLOPPY DISK',
        "\xf0\x9f\x92\xbf" => 'OPTICAL DISC',
        "\xf0\x9f\x93\x80" => 'DVD',
        "\xf0\x9f\x93\x81" => 'FILE FOLDER',
        "\xf0\x9f\x93\x82" => 'OPEN FILE FOLDER',
        "\xf0\x9f\x93\x83" => 'PAGE WITH CURL',
        "\xf0\x9f\x93\x84" => 'PAGE FACING UP',
        "\xf0\x9f\x93\x85" => 'CALENDAR',
        "\xf0\x9f\x93\x86" => 'TEAR-OFF CALENDAR',
        "\xf0\x9f\x93\x87" => 'CARD INDEX',
        "\xf0\x9f\x93\x88" => 'CHART WITH UPWARDS TREND',
        "\xf0\x9f\x93\x89" => 'CHART WITH DOWNWARDS TREND',
        "\xf0\x9f\x93\x8a" => 'BAR CHART',
        "\xf0\x9f\x93\x8b" => 'CLIPBOARD',
        "\xf0\x9f\x93\x8c" => 'PUSHPIN',
        "\xf0\x9f\x93\x8d" => 'ROUND PUSHPIN',
        "\xf0\x9f\x93\x8e" => 'PAPERCLIP',
        "\xf0\x9f\x93\x8f" => 'STRAIGHT RULER',
        "\xf0\x9f\x93\x90" => 'TRIANGULAR RULER',
        "\xf0\x9f\x93\x91" => 'BOOKMARK TABS',
        "\xf0\x9f\x93\x92" => 'LEDGER',
        "\xf0\x9f\x93\x93" => 'NOTEBOOK',
        "\xf0\x9f\x93\x94" => 'NOTEBOOK WITH DECORATIVE COVER',
        "\xf0\x9f\x93\x95" => 'CLOSED BOOK',
        "\xf0\x9f\x93\x96" => 'OPEN BOOK',
        "\xf0\x9f\x93\x97" => 'GREEN BOOK',
        "\xf0\x9f\x93\x98" => 'BLUE BOOK',
        "\xf0\x9f\x93\x99" => 'ORANGE BOOK',
        "\xf0\x9f\x93\x9a" => 'BOOKS',
        "\xf0\x9f\x93\x9b" => 'NAME BADGE',
        "\xf0\x9f\x93\x9c" => 'SCROLL',
        "\xf0\x9f\x93\x9d" => 'MEMO',
        "\xf0\x9f\x93\x9e" => 'TELEPHONE RECEIVER',
        "\xf0\x9f\x93\x9f" => 'PAGER',
        "\xf0\x9f\x93\xa0" => 'FAX MACHINE',
        "\xf0\x9f\x93\xa1" => 'SATELLITE ANTENNA',
        "\xf0\x9f\x93\xa2" => 'PUBLIC ADDRESS LOUDSPEAKER',
        "\xf0\x9f\x93\xa3" => 'CHEERING MEGAPHONE',
        "\xf0\x9f\x93\xa4" => 'OUTBOX TRAY',
        "\xf0\x9f\x93\xa5" => 'INBOX TRAY',
        "\xf0\x9f\x93\xa6" => 'PACKAGE',
        "\xf0\x9f\x93\xa7" => 'E-MAIL SYMBOL',
        "\xf0\x9f\x93\xa8" => 'INCOMING ENVELOPE',
        "\xf0\x9f\x93\xa9" => 'ENVELOPE WITH DOWNWARDS ARROW ABOVE',
        "\xf0\x9f\x93\xaa" => 'CLOSED MAILBOX WITH LOWERED FLAG',
        "\xf0\x9f\x93\xab" => 'CLOSED MAILBOX WITH RAISED FLAG',
        "\xf0\x9f\x93\xac" => 'OPEN MAILBOX WITH RAISED FLAG',
        "\xf0\x9f\x93\xad" => 'OPEN MAILBOX WITH LOWERED FLAG',
        "\xf0\x9f\x93\xae" => 'POSTBOX',
        "\xf0\x9f\x93\xaf" => 'POSTAL HORN',
        "\xf0\x9f\x93\xb0" => 'NEWSPAPER',
        "\xf0\x9f\x93\xb1" => 'MOBILE PHONE',
        "\xf0\x9f\x93\xb2" => 'MOBILE PHONE WITH RIGHTWARDS ARROW AT LEFT',
        "\xf0\x9f\x93\xb3" => 'VIBRATION MODE',
        "\xf0\x9f\x93\xb4" => 'MOBILE PHONE OFF',
        "\xf0\x9f\x93\xb5" => 'NO MOBILE PHONES',
        "\xf0\x9f\x93\xb6" => 'ANTENNA WITH BARS',
        "\xf0\x9f\x93\xb7" => 'CAMERA',
        "\xf0\x9f\x93\xb8" => 'CAMERA WITH FLASH',
        "\xf0\x9f\x93\xb9" => 'VIDEO CAMERA',
        "\xf0\x9f\x93\xba" => 'TELEVISION',
        "\xf0\x9f\x93\xbb" => 'RADIO',
        "\xf0\x9f\x93\xbc" => 'VIDEOCASSETTE',
        "\xf0\x9f\x93\xbd" => 'FILM PROJECTOR',
        "\xf0\x9f\x93\xbf" => 'PRAYER BEADS',
        "\xf0\x9f\x94\x80" => 'TWISTED RIGHTWARDS ARROWS',
        "\xf0\x9f\x94\x81" => 'CLOCKWISE RIGHTWARDS AND LEFTWARDS OPEN CIRCLE ARROWS',
        "\xf0\x9f\x94\x82" => 'CLOCKWISE RIGHTWARDS AND LEFTWARDS OPEN CIRCLE ARROWS WITH CIRCLED ONE OVERLAY',
        "\xf0\x9f\x94\x83" => 'CLOCKWISE DOWNWARDS AND UPWARDS OPEN CIRCLE ARROWS',
        "\xf0\x9f\x94\x84" => 'ANTICLOCKWISE DOWNWARDS AND UPWARDS OPEN CIRCLE ARROWS',
        "\xf0\x9f\x94\x85" => 'LOW BRIGHTNESS SYMBOL',
        "\xf0\x9f\x94\x86" => 'HIGH BRIGHTNESS SYMBOL',
        "\xf0\x9f\x94\x87" => 'SPEAKER WITH CANCELLATION STROKE',
        "\xf0\x9f\x94\x88" => 'SPEAKER',
        "\xf0\x9f\x94\x89" => 'SPEAKER WITH ONE SOUND WAVE',
        "\xf0\x9f\x94\x8a" => 'SPEAKER WITH THREE SOUND WAVES',
        "\xf0\x9f\x94\x8b" => 'BATTERY',
        "\xf0\x9f\x94\x8c" => 'ELECTRIC PLUG',
        "\xf0\x9f\x94\x8d" => 'LEFT-POINTING MAGNIFYING GLASS',
        "\xf0\x9f\x94\x8e" => 'RIGHT-POINTING MAGNIFYING GLASS',
        "\xf0\x9f\x94\x8f" => 'LOCK WITH INK PEN',
        "\xf0\x9f\x94\x90" => 'CLOSED LOCK WITH KEY',
        "\xf0\x9f\x94\x91" => 'KEY',
        "\xf0\x9f\x94\x92" => 'LOCK',
        "\xf0\x9f\x94\x93" => 'OPEN LOCK',
        "\xf0\x9f\x94\x94" => 'BELL',
        "\xf0\x9f\x94\x95" => 'BELL WITH CANCELLATION STROKE',
        "\xf0\x9f\x94\x96" => 'BOOKMARK',
        "\xf0\x9f\x94\x97" => 'LINK SYMBOL',
        "\xf0\x9f\x94\x98" => 'RADIO BUTTON',
        "\xf0\x9f\x94\x99" => 'BACK WITH LEFTWARDS ARROW ABOVE',
        "\xf0\x9f\x94\x9a" => 'END WITH LEFTWARDS ARROW ABOVE',
        "\xf0\x9f\x94\x9b" => 'ON WITH EXCLAMATION MARK WITH LEFT RIGHT ARROW ABOVE',
        "\xf0\x9f\x94\x9c" => 'SOON WITH RIGHTWARDS ARROW ABOVE',
        "\xf0\x9f\x94\x9d" => 'TOP WITH UPWARDS ARROW ABOVE',
        "\xf0\x9f\x94\x9e" => 'NO ONE UNDER EIGHTEEN SYMBOL',
        "\xf0\x9f\x94\x9f" => 'KEYCAP TEN',
        "\xf0\x9f\x94\xa0" => 'INPUT SYMBOL FOR LATIN CAPITAL LETTERS',
        "\xf0\x9f\x94\xa1" => 'INPUT SYMBOL FOR LATIN SMALL LETTERS',
        "\xf0\x9f\x94\xa2" => 'INPUT SYMBOL FOR NUMBERS',
        "\xf0\x9f\x94\xa3" => 'INPUT SYMBOL FOR SYMBOLS',
        "\xf0\x9f\x94\xa4" => 'INPUT SYMBOL FOR LATIN LETTERS',
        "\xf0\x9f\x94\xa5" => 'FIRE',
        "\xf0\x9f\x94\xa6" => 'ELECTRIC TORCH',
        "\xf0\x9f\x94\xa7" => 'WRENCH',
        "\xf0\x9f\x94\xa8" => 'HAMMER',
        "\xf0\x9f\x94\xa9" => 'NUT AND BOLT',
        "\xf0\x9f\x94\xaa" => 'HOCHO',
        "\xf0\x9f\x94\xab" => 'PISTOL',
        "\xf0\x9f\x94\xac" => 'MICROSCOPE',
        "\xf0\x9f\x94\xad" => 'TELESCOPE',
        "\xf0\x9f\x94\xae" => 'CRYSTAL BALL',
        "\xf0\x9f\x94\xaf" => 'SIX POINTED STAR WITH MIDDLE DOT',
        "\xf0\x9f\x94\xb0" => 'JAPANESE SYMBOL FOR BEGINNER',
        "\xf0\x9f\x94\xb1" => 'TRIDENT EMBLEM',
        "\xf0\x9f\x94\xb2" => 'BLACK SQUARE BUTTON',
        "\xf0\x9f\x94\xb3" => 'WHITE SQUARE BUTTON',
        "\xf0\x9f\x94\xb4" => 'LARGE RED CIRCLE',
        "\xf0\x9f\x94\xb5" => 'LARGE BLUE CIRCLE',
        "\xf0\x9f\x94\xb6" => 'LARGE ORANGE DIAMOND',
        "\xf0\x9f\x94\xb7" => 'LARGE BLUE DIAMOND',
        "\xf0\x9f\x94\xb8" => 'SMALL ORANGE DIAMOND',
        "\xf0\x9f\x94\xb9" => 'SMALL BLUE DIAMOND',
        "\xf0\x9f\x94\xba" => 'UP-POINTING RED TRIANGLE',
        "\xf0\x9f\x94\xbb" => 'DOWN-POINTING RED TRIANGLE',
        "\xf0\x9f\x94\xbc" => 'UP-POINTING SMALL RED TRIANGLE',
        "\xf0\x9f\x94\xbd" => 'DOWN-POINTING SMALL RED TRIANGLE',
        "\xf0\x9f\x95\x89" => 'OM SYMBOL',
        "\xf0\x9f\x95\x8a" => 'DOVE OF PEACE',
        "\xf0\x9f\x95\x8b" => 'KAABA',
        "\xf0\x9f\x95\x8c" => 'MOSQUE',
        "\xf0\x9f\x95\x8d" => 'SYNAGOGUE',
        "\xf0\x9f\x95\x8e" => 'MENORAH WITH NINE BRANCHES',
        "\xf0\x9f\x95\x90" => 'CLOCK FACE ONE OCLOCK',
        "\xf0\x9f\x95\x91" => 'CLOCK FACE TWO OCLOCK',
        "\xf0\x9f\x95\x92" => 'CLOCK FACE THREE OCLOCK',
        "\xf0\x9f\x95\x93" => 'CLOCK FACE FOUR OCLOCK',
        "\xf0\x9f\x95\x94" => 'CLOCK FACE FIVE OCLOCK',
        "\xf0\x9f\x95\x95" => 'CLOCK FACE SIX OCLOCK',
        "\xf0\x9f\x95\x96" => 'CLOCK FACE SEVEN OCLOCK',
        "\xf0\x9f\x95\x97" => 'CLOCK FACE EIGHT OCLOCK',
        "\xf0\x9f\x95\x98" => 'CLOCK FACE NINE OCLOCK',
        "\xf0\x9f\x95\x99" => 'CLOCK FACE TEN OCLOCK',
        "\xf0\x9f\x95\x9a" => 'CLOCK FACE ELEVEN OCLOCK',
        "\xf0\x9f\x95\x9b" => 'CLOCK FACE TWELVE OCLOCK',
        "\xf0\x9f\x95\x9c" => 'CLOCK FACE ONE-THIRTY',
        "\xf0\x9f\x95\x9d" => 'CLOCK FACE TWO-THIRTY',
        "\xf0\x9f\x95\x9e" => 'CLOCK FACE THREE-THIRTY',
        "\xf0\x9f\x95\x9f" => 'CLOCK FACE FOUR-THIRTY',
        "\xf0\x9f\x95\xa0" => 'CLOCK FACE FIVE-THIRTY',
        "\xf0\x9f\x95\xa1" => 'CLOCK FACE SIX-THIRTY',
        "\xf0\x9f\x95\xa2" => 'CLOCK FACE SEVEN-THIRTY',
        "\xf0\x9f\x95\xa3" => 'CLOCK FACE EIGHT-THIRTY',
        "\xf0\x9f\x95\xa4" => 'CLOCK FACE NINE-THIRTY',
        "\xf0\x9f\x95\xa5" => 'CLOCK FACE TEN-THIRTY',
        "\xf0\x9f\x95\xa6" => 'CLOCK FACE ELEVEN-THIRTY',
        "\xf0\x9f\x95\xa7" => 'CLOCK FACE TWELVE-THIRTY',
        "\xf0\x9f\x95\xaf" => 'CANDLE',
        "\xf0\x9f\x95\xb0" => 'MANTELPIECE CLOCK',
        "\xf0\x9f\x95\xb3" => 'HOLE',
        "\xf0\x9f\x95\xb4" => 'MAN IN BUSINESS SUIT LEVITATING',
        "\xf0\x9f\x95\xb5" => 'SLEUTH OR SPY',
        "\xf0\x9f\x95\xb6" => 'DARK SUNGLASSES',
        "\xf0\x9f\x95\xb7" => 'SPIDER',
        "\xf0\x9f\x95\xb8" => 'SPIDER WEB',
        "\xf0\x9f\x95\xb9" => 'JOYSTICK',
        "\xf0\x9f\x96\x87" => 'LINKED PAPERCLIPS',
        "\xf0\x9f\x96\x8a" => 'LOWER LEFT BALLPOINT PEN',
        "\xf0\x9f\x96\x8b" => 'LOWER LEFT FOUNTAIN PEN',
        "\xf0\x9f\x96\x8c" => 'LOWER LEFT PAINTBRUSH',
        "\xf0\x9f\x96\x8d" => 'LOWER LEFT CRAYON',
        "\xf0\x9f\x96\x90" => 'RAISED HAND WITH FINGERS SPLAYED',
        "\xf0\x9f\x96\x95" => 'REVERSED HAND WITH MIDDLE FINGER EXTENDED',
        "\xf0\x9f\x96\x96" => 'RAISED HAND WITH PART BETWEEN MIDDLE AND RING FINGERS',
        "\xf0\x9f\x96\xa5" => 'DESKTOP COMPUTER',
        "\xf0\x9f\x96\xa8" => 'PRINTER',
        "\xf0\x9f\x96\xb1" => 'THREE BUTTON MOUSE',
        "\xf0\x9f\x96\xb2" => 'TRACKBALL',
        "\xf0\x9f\x96\xbc" => 'FRAME WITH PICTURE',
        "\xf0\x9f\x97\x82" => 'CARD INDEX DIVIDERS',
        "\xf0\x9f\x97\x83" => 'CARD FILE BOX',
        "\xf0\x9f\x97\x84" => 'FILE CABINET',
        "\xf0\x9f\x97\x91" => 'WASTEBASKET',
        "\xf0\x9f\x97\x92" => 'SPIRAL NOTE PAD',
        "\xf0\x9f\x97\x93" => 'SPIRAL CALENDAR PAD',
        "\xf0\x9f\x97\x9c" => 'COMPRESSION',
        "\xf0\x9f\x97\x9d" => 'OLD KEY',
        "\xf0\x9f\x97\x9e" => 'ROLLED-UP NEWSPAPER',
        "\xf0\x9f\x97\xa1" => 'DAGGER KNIFE',
        "\xf0\x9f\x97\xa3" => 'SPEAKING HEAD IN SILHOUETTE',
        "\xf0\x9f\x97\xa8" => 'LEFT SPEECH BUBBLE',
        "\xf0\x9f\x97\xaf" => 'RIGHT ANGER BUBBLE',
        "\xf0\x9f\x97\xb3" => 'BALLOT BOX WITH BALLOT',
        "\xf0\x9f\x97\xba" => 'WORLD MAP',
        "\xf0\x9f\x97\xbb" => 'MOUNT FUJI',
        "\xf0\x9f\x97\xbc" => 'TOKYO TOWER',
        "\xf0\x9f\x97\xbd" => 'STATUE OF LIBERTY',
        "\xf0\x9f\x97\xbe" => 'SILHOUETTE OF JAPAN',
        "\xf0\x9f\x97\xbf" => 'MOYAI',
        "\xf0\x9f\x98\x80" => 'GRINNING FACE',
        "\xf0\x9f\x98\x81" => 'GRINNING FACE WITH SMILING EYES',
        "\xf0\x9f\x98\x82" => 'FACE WITH TEARS OF JOY',
        "\xf0\x9f\x98\x83" => 'SMILING FACE WITH OPEN MOUTH',
        "\xf0\x9f\x98\x84" => 'SMILING FACE WITH OPEN MOUTH AND SMILING EYES',
        "\xf0\x9f\x98\x85" => 'SMILING FACE WITH OPEN MOUTH AND COLD SWEAT',
        "\xf0\x9f\x98\x86" => 'SMILING FACE WITH OPEN MOUTH AND TIGHTLY-CLOSED EYES',
        "\xf0\x9f\x98\x87" => 'SMILING FACE WITH HALO',
        "\xf0\x9f\x98\x88" => 'SMILING FACE WITH HORNS',
        "\xf0\x9f\x98\x89" => 'WINKING FACE',
        "\xf0\x9f\x98\x8a" => 'SMILING FACE WITH SMILING EYES',
        "\xf0\x9f\x98\x8b" => 'FACE SAVOURING DELICIOUS FOOD',
        "\xf0\x9f\x98\x8c" => 'RELIEVED FACE',
        "\xf0\x9f\x98\x8d" => 'SMILING FACE WITH HEART-SHAPED EYES',
        "\xf0\x9f\x98\x8e" => 'SMILING FACE WITH SUNGLASSES',
        "\xf0\x9f\x98\x8f" => 'SMIRKING FACE',
        "\xf0\x9f\x98\x90" => 'NEUTRAL FACE',
        "\xf0\x9f\x98\x91" => 'EXPRESSIONLESS FACE',
        "\xf0\x9f\x98\x92" => 'UNAMUSED FACE',
        "\xf0\x9f\x98\x93" => 'FACE WITH COLD SWEAT',
        "\xf0\x9f\x98\x94" => 'PENSIVE FACE',
        "\xf0\x9f\x98\x95" => 'CONFUSED FACE',
        "\xf0\x9f\x98\x96" => 'CONFOUNDED FACE',
        "\xf0\x9f\x98\x97" => 'KISSING FACE',
        "\xf0\x9f\x98\x98" => 'FACE THROWING A KISS',
        "\xf0\x9f\x98\x99" => 'KISSING FACE WITH SMILING EYES',
        "\xf0\x9f\x98\x9a" => 'KISSING FACE WITH CLOSED EYES',
        "\xf0\x9f\x98\x9b" => 'FACE WITH STUCK-OUT TONGUE',
        "\xf0\x9f\x98\x9c" => 'FACE WITH STUCK-OUT TONGUE AND WINKING EYE',
        "\xf0\x9f\x98\x9d" => 'FACE WITH STUCK-OUT TONGUE AND TIGHTLY-CLOSED EYES',
        "\xf0\x9f\x98\x9e" => 'DISAPPOINTED FACE',
        "\xf0\x9f\x98\x9f" => 'WORRIED FACE',
        "\xf0\x9f\x98\xa0" => 'ANGRY FACE',
        "\xf0\x9f\x98\xa1" => 'POUTING FACE',
        "\xf0\x9f\x98\xa2" => 'CRYING FACE',
        "\xf0\x9f\x98\xa3" => 'PERSEVERING FACE',
        "\xf0\x9f\x98\xa4" => 'FACE WITH LOOK OF TRIUMPH',
        "\xf0\x9f\x98\xa5" => 'DISAPPOINTED BUT RELIEVED FACE',
        "\xf0\x9f\x98\xa6" => 'FROWNING FACE WITH OPEN MOUTH',
        "\xf0\x9f\x98\xa7" => 'ANGUISHED FACE',
        "\xf0\x9f\x98\xa8" => 'FEARFUL FACE',
        "\xf0\x9f\x98\xa9" => 'WEARY FACE',
        "\xf0\x9f\x98\xaa" => 'SLEEPY FACE',
        "\xf0\x9f\x98\xab" => 'TIRED FACE',
        "\xf0\x9f\x98\xac" => 'GRIMACING FACE',
        "\xf0\x9f\x98\xad" => 'LOUDLY CRYING FACE',
        "\xf0\x9f\x98\xae" => 'FACE WITH OPEN MOUTH',
        "\xf0\x9f\x98\xaf" => 'HUSHED FACE',
        "\xf0\x9f\x98\xb0" => 'FACE WITH OPEN MOUTH AND COLD SWEAT',
        "\xf0\x9f\x98\xb1" => 'FACE SCREAMING IN FEAR',
        "\xf0\x9f\x98\xb2" => 'ASTONISHED FACE',
        "\xf0\x9f\x98\xb3" => 'FLUSHED FACE',
        "\xf0\x9f\x98\xb4" => 'SLEEPING FACE',
        "\xf0\x9f\x98\xb5" => 'DIZZY FACE',
        "\xf0\x9f\x98\xb6" => 'FACE WITHOUT MOUTH',
        "\xf0\x9f\x98\xb7" => 'FACE WITH MEDICAL MASK',
        "\xf0\x9f\x98\xb8" => 'GRINNING CAT FACE WITH SMILING EYES',
        "\xf0\x9f\x98\xb9" => 'CAT FACE WITH TEARS OF JOY',
        "\xf0\x9f\x98\xba" => 'SMILING CAT FACE WITH OPEN MOUTH',
        "\xf0\x9f\x98\xbb" => 'SMILING CAT FACE WITH HEART-SHAPED EYES',
        "\xf0\x9f\x98\xbc" => 'CAT FACE WITH WRY SMILE',
        "\xf0\x9f\x98\xbd" => 'KISSING CAT FACE WITH CLOSED EYES',
        "\xf0\x9f\x98\xbe" => 'POUTING CAT FACE',
        "\xf0\x9f\x98\xbf" => 'CRYING CAT FACE',
        "\xf0\x9f\x99\x80" => 'WEARY CAT FACE',
        "\xf0\x9f\x99\x81" => 'SLIGHTLY FROWNING FACE',
        "\xf0\x9f\x99\x82" => 'SLIGHTLY SMILING FACE',
        "\xf0\x9f\x99\x83" => 'UPSIDE-DOWN FACE',
        "\xf0\x9f\x99\x84" => 'FACE WITH ROLLING EYES',
        "\xf0\x9f\x99\x85" => 'FACE WITH NO GOOD GESTURE',
        "\xf0\x9f\x99\x86" => 'FACE WITH OK GESTURE',
        "\xf0\x9f\x99\x87" => 'PERSON BOWING DEEPLY',
        "\xf0\x9f\x99\x88" => 'SEE-NO-EVIL MONKEY',
        "\xf0\x9f\x99\x89" => 'HEAR-NO-EVIL MONKEY',
        "\xf0\x9f\x99\x8a" => 'SPEAK-NO-EVIL MONKEY',
        "\xf0\x9f\x99\x8b" => 'HAPPY PERSON RAISING ONE HAND',
        "\xf0\x9f\x99\x8c" => 'PERSON RAISING BOTH HANDS IN CELEBRATION',
        "\xf0\x9f\x99\x8d" => 'PERSON FROWNING',
        "\xf0\x9f\x99\x8e" => 'PERSON WITH POUTING FACE',
        "\xf0\x9f\x99\x8f" => 'PERSON WITH FOLDED HANDS',
        "\xf0\x9f\x9a\x80" => 'ROCKET',
        "\xf0\x9f\x9a\x81" => 'HELICOPTER',
        "\xf0\x9f\x9a\x82" => 'STEAM LOCOMOTIVE',
        "\xf0\x9f\x9a\x83" => 'RAILWAY CAR',
        "\xf0\x9f\x9a\x84" => 'HIGH-SPEED TRAIN',
        "\xf0\x9f\x9a\x85" => 'HIGH-SPEED TRAIN WITH BULLET NOSE',
        "\xf0\x9f\x9a\x86" => 'TRAIN',
        "\xf0\x9f\x9a\x87" => 'METRO',
        "\xf0\x9f\x9a\x88" => 'LIGHT RAIL',
        "\xf0\x9f\x9a\x89" => 'STATION',
        "\xf0\x9f\x9a\x8a" => 'TRAM',
        "\xf0\x9f\x9a\x8b" => 'TRAM CAR',
        "\xf0\x9f\x9a\x8c" => 'BUS',
        "\xf0\x9f\x9a\x8d" => 'ONCOMING BUS',
        "\xf0\x9f\x9a\x8e" => 'TROLLEYBUS',
        "\xf0\x9f\x9a\x8f" => 'BUS STOP',
        "\xf0\x9f\x9a\x90" => 'MINIBUS',
        "\xf0\x9f\x9a\x91" => 'AMBULANCE',
        "\xf0\x9f\x9a\x92" => 'FIRE ENGINE',
        "\xf0\x9f\x9a\x93" => 'POLICE CAR',
        "\xf0\x9f\x9a\x94" => 'ONCOMING POLICE CAR',
        "\xf0\x9f\x9a\x95" => 'TAXI',
        "\xf0\x9f\x9a\x96" => 'ONCOMING TAXI',
        "\xf0\x9f\x9a\x97" => 'AUTOMOBILE',
        "\xf0\x9f\x9a\x98" => 'ONCOMING AUTOMOBILE',
        "\xf0\x9f\x9a\x99" => 'RECREATIONAL VEHICLE',
        "\xf0\x9f\x9a\x9a" => 'DELIVERY TRUCK',
        "\xf0\x9f\x9a\x9b" => 'ARTICULATED LORRY',
        "\xf0\x9f\x9a\x9c" => 'TRACTOR',
        "\xf0\x9f\x9a\x9d" => 'MONORAIL',
        "\xf0\x9f\x9a\x9e" => 'MOUNTAIN RAILWAY',
        "\xf0\x9f\x9a\x9f" => 'SUSPENSION RAILWAY',
        "\xf0\x9f\x9a\xa0" => 'MOUNTAIN CABLEWAY',
        "\xf0\x9f\x9a\xa1" => 'AERIAL TRAMWAY',
        "\xf0\x9f\x9a\xa2" => 'SHIP',
        "\xf0\x9f\x9a\xa3" => 'ROWBOAT',
        "\xf0\x9f\x9a\xa4" => 'SPEEDBOAT',
        "\xf0\x9f\x9a\xa5" => 'HORIZONTAL TRAFFIC LIGHT',
        "\xf0\x9f\x9a\xa6" => 'VERTICAL TRAFFIC LIGHT',
        "\xf0\x9f\x9a\xa7" => 'CONSTRUCTION SIGN',
        "\xf0\x9f\x9a\xa8" => 'POLICE CARS REVOLVING LIGHT',
        "\xf0\x9f\x9a\xa9" => 'TRIANGULAR FLAG ON POST',
        "\xf0\x9f\x9a\xaa" => 'DOOR',
        "\xf0\x9f\x9a\xab" => 'NO ENTRY SIGN',
        "\xf0\x9f\x9a\xac" => 'SMOKING SYMBOL',
        "\xf0\x9f\x9a\xad" => 'NO SMOKING SYMBOL',
        "\xf0\x9f\x9a\xae" => 'PUT LITTER IN ITS PLACE SYMBOL',
        "\xf0\x9f\x9a\xaf" => 'DO NOT LITTER SYMBOL',
        "\xf0\x9f\x9a\xb0" => 'POTABLE WATER SYMBOL',
        "\xf0\x9f\x9a\xb1" => 'NON-POTABLE WATER SYMBOL',
        "\xf0\x9f\x9a\xb2" => 'BICYCLE',
        "\xf0\x9f\x9a\xb3" => 'NO BICYCLES',
        "\xf0\x9f\x9a\xb4" => 'BICYCLIST',
        "\xf0\x9f\x9a\xb5" => 'MOUNTAIN BICYCLIST',
        "\xf0\x9f\x9a\xb6" => 'PEDESTRIAN',
        "\xf0\x9f\x9a\xb7" => 'NO PEDESTRIANS',
        "\xf0\x9f\x9a\xb8" => 'CHILDREN CROSSING',
        "\xf0\x9f\x9a\xb9" => 'MENS SYMBOL',
        "\xf0\x9f\x9a\xba" => 'WOMENS SYMBOL',
        "\xf0\x9f\x9a\xbb" => 'RESTROOM',
        "\xf0\x9f\x9a\xbc" => 'BABY SYMBOL',
        "\xf0\x9f\x9a\xbd" => 'TOILET',
        "\xf0\x9f\x9a\xbe" => 'WATER CLOSET',
        "\xf0\x9f\x9a\xbf" => 'SHOWER',
        "\xf0\x9f\x9b\x80" => 'BATH',
        "\xf0\x9f\x9b\x81" => 'BATHTUB',
        "\xf0\x9f\x9b\x82" => 'PASSPORT CONTROL',
        "\xf0\x9f\x9b\x83" => 'CUSTOMS',
        "\xf0\x9f\x9b\x84" => 'BAGGAGE CLAIM',
        "\xf0\x9f\x9b\x85" => 'LEFT LUGGAGE',
        "\xf0\x9f\x9b\x8b" => 'COUCH AND LAMP',
        "\xf0\x9f\x9b\x8c" => 'SLEEPING ACCOMMODATION',
        "\xf0\x9f\x9b\x8d" => 'SHOPPING BAGS',
        "\xf0\x9f\x9b\x8e" => 'BELLHOP BELL',
        "\xf0\x9f\x9b\x8f" => 'BED',
        "\xf0\x9f\x9b\x90" => 'PLACE OF WORSHIP',
        "\xf0\x9f\x9b\xa0" => 'HAMMER AND WRENCH',
        "\xf0\x9f\x9b\xa1" => 'SHIELD',
        "\xf0\x9f\x9b\xa2" => 'OIL DRUM',
        "\xf0\x9f\x9b\xa3" => 'MOTORWAY',
        "\xf0\x9f\x9b\xa4" => 'RAILWAY TRACK',
        "\xf0\x9f\x9b\xa5" => 'MOTOR BOAT',
        "\xf0\x9f\x9b\xa9" => 'SMALL AIRPLANE',
        "\xf0\x9f\x9b\xab" => 'AIRPLANE DEPARTURE',
        "\xf0\x9f\x9b\xac" => 'AIRPLANE ARRIVING',
        "\xf0\x9f\x9b\xb0" => 'SATELLITE',
        "\xf0\x9f\x9b\xb3" => 'PASSENGER SHIP',
        "\xf0\x9f\xa4\x90" => 'ZIPPER-MOUTH FACE',
        "\xf0\x9f\xa4\x91" => 'MONEY-MOUTH FACE',
        "\xf0\x9f\xa4\x92" => 'FACE WITH THERMOMETER',
        "\xf0\x9f\xa4\x93" => 'NERD FACE',
        "\xf0\x9f\xa4\x94" => 'THINKING FACE',
        "\xf0\x9f\xa4\x95" => 'FACE WITH HEAD-BANDAGE',
        "\xf0\x9f\xa4\x96" => 'ROBOT FACE',
        "\xf0\x9f\xa4\x97" => 'HUGGING FACE',
        "\xf0\x9f\xa4\x98" => 'SIGN OF THE HORNS',
        "\xf0\x9f\xa6\x80" => 'CRAB',
        "\xf0\x9f\xa6\x81" => 'LION FACE',
        "\xf0\x9f\xa6\x82" => 'SCORPION',
        "\xf0\x9f\xa6\x83" => 'TURKEY',
        "\xf0\x9f\xa6\x84" => 'UNICORN FACE',
        "\xf0\x9f\xa7\x80" => 'CHEESE WEDGE',
        "#\xe2\x83\xa3" => 'HASH KEY',
        "*\xe2\x83\xa3" => '',
        "0\xe2\x83\xa3" => 'KEYCAP 0',
        "1\xe2\x83\xa3" => 'KEYCAP 1',
        "2\xe2\x83\xa3" => 'KEYCAP 2',
        "3\xe2\x83\xa3" => 'KEYCAP 3',
        "4\xe2\x83\xa3" => 'KEYCAP 4',
        "5\xe2\x83\xa3" => 'KEYCAP 5',
        "6\xe2\x83\xa3" => 'KEYCAP 6',
        "7\xe2\x83\xa3" => 'KEYCAP 7',
        "8\xe2\x83\xa3" => 'KEYCAP 8',
        "9\xe2\x83\xa3" => 'KEYCAP 9',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xa8" => 'REGIONAL INDICATOR SYMBOL LETTERS AC',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xa9" => 'REGIONAL INDICATOR SYMBOL LETTERS AD',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS AE',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xab" => 'REGIONAL INDICATOR SYMBOL LETTERS AF',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS AG',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xae" => 'REGIONAL INDICATOR SYMBOL LETTERS AI',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb1" => 'REGIONAL INDICATOR SYMBOL LETTERS AL',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS AM',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb4" => 'REGIONAL INDICATOR SYMBOL LETTERS AO',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb6" => 'REGIONAL INDICATOR SYMBOL LETTERS AQ',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS AR',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb8" => 'REGIONAL INDICATOR SYMBOL LETTERS AS',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb9" => 'REGIONAL INDICATOR SYMBOL LETTERS AT',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xba" => 'REGIONAL INDICATOR SYMBOL LETTERS AU',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbc" => 'REGIONAL INDICATOR SYMBOL LETTERS AW',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbd" => 'REGIONAL INDICATOR SYMBOL LETTERS AX',
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbf" => 'REGIONAL INDICATOR SYMBOL LETTERS AZ',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS BA',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa7" => 'REGIONAL INDICATOR SYMBOL LETTERS BB',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa9" => 'REGIONAL INDICATOR SYMBOL LETTERS BD',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS BE',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xab" => 'REGIONAL INDICATOR SYMBOL LETTERS BF',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS BG',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xad" => 'REGIONAL INDICATOR SYMBOL LETTERS BH',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xae" => 'REGIONAL INDICATOR SYMBOL LETTERS BI',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xaf" => 'REGIONAL INDICATOR SYMBOL LETTERS BJ',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb1" => 'REGIONAL INDICATOR SYMBOL LETTERS BL',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS BM',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb3" => 'REGIONAL INDICATOR SYMBOL LETTERS BN',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb4" => 'REGIONAL INDICATOR SYMBOL LETTERS BO',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb6" => 'REGIONAL INDICATOR SYMBOL LETTERS BQ',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS BR',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb8" => 'REGIONAL INDICATOR SYMBOL LETTERS BS',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb9" => 'REGIONAL INDICATOR SYMBOL LETTERS BT',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbb" => 'REGIONAL INDICATOR SYMBOL LETTERS BV',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbc" => 'REGIONAL INDICATOR SYMBOL LETTERS BW',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbe" => 'REGIONAL INDICATOR SYMBOL LETTERS BY',
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbf" => 'REGIONAL INDICATOR SYMBOL LETTERS BZ',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS CA',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa8" => 'REGIONAL INDICATOR SYMBOL LETTERS CC',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa9" => 'REGIONAL INDICATOR SYMBOL LETTERS CD',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xab" => 'REGIONAL INDICATOR SYMBOL LETTERS CF',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS CG',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xad" => 'REGIONAL INDICATOR SYMBOL LETTERS CH',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xae" => 'REGIONAL INDICATOR SYMBOL LETTERS CI',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb0" => 'REGIONAL INDICATOR SYMBOL LETTERS CK',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb1" => 'REGIONAL INDICATOR SYMBOL LETTERS CL',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS CM',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb3" => 'REGIONAL INDICATOR SYMBOL LETTERS CN',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb4" => 'REGIONAL INDICATOR SYMBOL LETTERS CO',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb5" => 'REGIONAL INDICATOR SYMBOL LETTERS CP',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS CR',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xba" => 'REGIONAL INDICATOR SYMBOL LETTERS CU',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbb" => 'REGIONAL INDICATOR SYMBOL LETTERS CV',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbc" => 'REGIONAL INDICATOR SYMBOL LETTERS CW',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbd" => 'REGIONAL INDICATOR SYMBOL LETTERS CX',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbe" => 'REGIONAL INDICATOR SYMBOL LETTERS CY',
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbf" => 'REGIONAL INDICATOR SYMBOL LETTERS CZ',
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS DE',
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS DG',
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaf" => 'REGIONAL INDICATOR SYMBOL LETTERS DJ',
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb0" => 'REGIONAL INDICATOR SYMBOL LETTERS DK',
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS DM',
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb4" => 'REGIONAL INDICATOR SYMBOL LETTERS DO',
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xbf" => 'REGIONAL INDICATOR SYMBOL LETTERS DZ',
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS EA',
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xa8" => 'REGIONAL INDICATOR SYMBOL LETTERS EC',
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS EE',
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS EG',
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xad" => 'REGIONAL INDICATOR SYMBOL LETTERS EH',
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS ER',
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb8" => 'REGIONAL INDICATOR SYMBOL LETTERS ES',
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb9" => 'REGIONAL INDICATOR SYMBOL LETTERS ET',
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xba" => 'REGIONAL INDICATOR SYMBOL LETTERS EU',
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xae" => 'REGIONAL INDICATOR SYMBOL LETTERS FI',
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xaf" => 'REGIONAL INDICATOR SYMBOL LETTERS FJ',
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb0" => 'REGIONAL INDICATOR SYMBOL LETTERS FK',
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS FM',
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb4" => 'REGIONAL INDICATOR SYMBOL LETTERS FO',
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS FR',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS GA',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa7" => 'REGIONAL INDICATOR SYMBOL LETTERS GB',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa9" => 'REGIONAL INDICATOR SYMBOL LETTERS GD',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS GE',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xab" => 'REGIONAL INDICATOR SYMBOL LETTERS GF',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS GG',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xad" => 'REGIONAL INDICATOR SYMBOL LETTERS GH',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xae" => 'REGIONAL INDICATOR SYMBOL LETTERS GI',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb1" => 'REGIONAL INDICATOR SYMBOL LETTERS GL',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS GM',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb3" => 'REGIONAL INDICATOR SYMBOL LETTERS GN',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb5" => 'REGIONAL INDICATOR SYMBOL LETTERS GP',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb6" => 'REGIONAL INDICATOR SYMBOL LETTERS GQ',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS GR',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb8" => 'REGIONAL INDICATOR SYMBOL LETTERS GS',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb9" => 'REGIONAL INDICATOR SYMBOL LETTERS GT',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xba" => 'REGIONAL INDICATOR SYMBOL LETTERS GU',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xbc" => 'REGIONAL INDICATOR SYMBOL LETTERS GW',
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xbe" => 'REGIONAL INDICATOR SYMBOL LETTERS GY',
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb0" => 'REGIONAL INDICATOR SYMBOL LETTERS HK',
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS HM',
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb3" => 'REGIONAL INDICATOR SYMBOL LETTERS HN',
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS HR',
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb9" => 'REGIONAL INDICATOR SYMBOL LETTERS HT',
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xba" => 'REGIONAL INDICATOR SYMBOL LETTERS HU',
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xa8" => 'REGIONAL INDICATOR SYMBOL LETTERS IC',
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xa9" => 'REGIONAL INDICATOR SYMBOL LETTERS ID',
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS IE',
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb1" => 'REGIONAL INDICATOR SYMBOL LETTERS IL',
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS IM',
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb3" => 'REGIONAL INDICATOR SYMBOL LETTERS IN',
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb4" => 'REGIONAL INDICATOR SYMBOL LETTERS IO',
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb6" => 'REGIONAL INDICATOR SYMBOL LETTERS IQ',
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS IR',
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb8" => 'REGIONAL INDICATOR SYMBOL LETTERS IS',
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb9" => 'REGIONAL INDICATOR SYMBOL LETTERS IT',
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS JE',
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS JM',
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb4" => 'REGIONAL INDICATOR SYMBOL LETTERS JO',
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb5" => 'REGIONAL INDICATOR SYMBOL LETTERS JP',
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS KE',
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS KG',
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xad" => 'REGIONAL INDICATOR SYMBOL LETTERS KH',
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xae" => 'REGIONAL INDICATOR SYMBOL LETTERS KI',
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS KM',
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb3" => 'REGIONAL INDICATOR SYMBOL LETTERS KN',
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb5" => 'REGIONAL INDICATOR SYMBOL LETTERS KP',
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS KR',
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbc" => 'REGIONAL INDICATOR SYMBOL LETTERS KW',
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbe" => 'REGIONAL INDICATOR SYMBOL LETTERS KY',
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbf" => 'REGIONAL INDICATOR SYMBOL LETTERS KZ',
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS LA',
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa7" => 'REGIONAL INDICATOR SYMBOL LETTERS LB',
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa8" => 'REGIONAL INDICATOR SYMBOL LETTERS LC',
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xae" => 'REGIONAL INDICATOR SYMBOL LETTERS LI',
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb0" => 'REGIONAL INDICATOR SYMBOL LETTERS LK',
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS LR',
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb8" => 'REGIONAL INDICATOR SYMBOL LETTERS LS',
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb9" => 'REGIONAL INDICATOR SYMBOL LETTERS LT',
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xba" => 'REGIONAL INDICATOR SYMBOL LETTERS LU',
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xbb" => 'REGIONAL INDICATOR SYMBOL LETTERS LV',
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xbe" => 'REGIONAL INDICATOR SYMBOL LETTERS LY',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS MA',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa8" => 'REGIONAL INDICATOR SYMBOL LETTERS MC',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa9" => 'REGIONAL INDICATOR SYMBOL LETTERS MD',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS ME',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xab" => 'REGIONAL INDICATOR SYMBOL LETTERS MF',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS MG',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xad" => 'REGIONAL INDICATOR SYMBOL LETTERS MH',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb0" => 'REGIONAL INDICATOR SYMBOL LETTERS MK',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb1" => 'REGIONAL INDICATOR SYMBOL LETTERS ML',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS MM',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb3" => 'REGIONAL INDICATOR SYMBOL LETTERS MN',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb4" => 'REGIONAL INDICATOR SYMBOL LETTERS MO',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb5" => 'REGIONAL INDICATOR SYMBOL LETTERS MP',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb6" => 'REGIONAL INDICATOR SYMBOL LETTERS MQ',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS MR',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb8" => 'REGIONAL INDICATOR SYMBOL LETTERS MS',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb9" => 'REGIONAL INDICATOR SYMBOL LETTERS MT',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xba" => 'REGIONAL INDICATOR SYMBOL LETTERS MU',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbb" => 'REGIONAL INDICATOR SYMBOL LETTERS MV',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbc" => 'REGIONAL INDICATOR SYMBOL LETTERS MW',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbd" => 'REGIONAL INDICATOR SYMBOL LETTERS MX',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbe" => 'REGIONAL INDICATOR SYMBOL LETTERS MY',
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbf" => 'REGIONAL INDICATOR SYMBOL LETTERS MZ',
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS NA',
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xa8" => 'REGIONAL INDICATOR SYMBOL LETTERS NC',
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS NE',
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xab" => 'REGIONAL INDICATOR SYMBOL LETTERS NF',
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS NG',
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xae" => 'REGIONAL INDICATOR SYMBOL LETTERS NI',
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb1" => 'REGIONAL INDICATOR SYMBOL LETTERS NL',
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb4" => 'REGIONAL INDICATOR SYMBOL LETTERS NO',
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb5" => 'REGIONAL INDICATOR SYMBOL LETTERS NP',
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS NR',
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xba" => 'REGIONAL INDICATOR SYMBOL LETTERS NU',
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xbf" => 'REGIONAL INDICATOR SYMBOL LETTERS NZ',
        "\xf0\x9f\x87\xb4\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS OM',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS PA',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS PE',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xab" => 'REGIONAL INDICATOR SYMBOL LETTERS PF',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS PG',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xad" => 'REGIONAL INDICATOR SYMBOL LETTERS PH',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb0" => 'REGIONAL INDICATOR SYMBOL LETTERS PK',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb1" => 'REGIONAL INDICATOR SYMBOL LETTERS PL',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS PM',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb3" => 'REGIONAL INDICATOR SYMBOL LETTERS PN',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS PR',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb8" => 'REGIONAL INDICATOR SYMBOL LETTERS PS',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb9" => 'REGIONAL INDICATOR SYMBOL LETTERS PT',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xbc" => 'REGIONAL INDICATOR SYMBOL LETTERS PW',
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xbe" => 'REGIONAL INDICATOR SYMBOL LETTERS PY',
        "\xf0\x9f\x87\xb6\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS QA',
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS RE',
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xb4" => 'REGIONAL INDICATOR SYMBOL LETTERS RO',
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xb8" => 'REGIONAL INDICATOR SYMBOL LETTERS RS',
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xba" => 'REGIONAL INDICATOR SYMBOL LETTERS RU',
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xbc" => 'REGIONAL INDICATOR SYMBOL LETTERS RW',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS SA',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa7" => 'REGIONAL INDICATOR SYMBOL LETTERS SB',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa8" => 'REGIONAL INDICATOR SYMBOL LETTERS SC',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa9" => 'REGIONAL INDICATOR SYMBOL LETTERS SD',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS SE',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS SG',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xad" => 'REGIONAL INDICATOR SYMBOL LETTERS SH',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xae" => 'REGIONAL INDICATOR SYMBOL LETTERS SI',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xaf" => 'REGIONAL INDICATOR SYMBOL LETTERS SJ',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb0" => 'REGIONAL INDICATOR SYMBOL LETTERS SK',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb1" => 'REGIONAL INDICATOR SYMBOL LETTERS SL',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS SM',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb3" => 'REGIONAL INDICATOR SYMBOL LETTERS SN',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb4" => 'REGIONAL INDICATOR SYMBOL LETTERS SO',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS SR',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb8" => 'REGIONAL INDICATOR SYMBOL LETTERS SS',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb9" => 'REGIONAL INDICATOR SYMBOL LETTERS ST',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbb" => 'REGIONAL INDICATOR SYMBOL LETTERS SV',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbd" => 'REGIONAL INDICATOR SYMBOL LETTERS SX',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbe" => 'REGIONAL INDICATOR SYMBOL LETTERS SY',
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbf" => 'REGIONAL INDICATOR SYMBOL LETTERS SZ',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS TA',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa8" => 'REGIONAL INDICATOR SYMBOL LETTERS TC',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa9" => 'REGIONAL INDICATOR SYMBOL LETTERS TD',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xab" => 'REGIONAL INDICATOR SYMBOL LETTERS TF',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS TG',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xad" => 'REGIONAL INDICATOR SYMBOL LETTERS TH',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xaf" => 'REGIONAL INDICATOR SYMBOL LETTERS TJ',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb0" => 'REGIONAL INDICATOR SYMBOL LETTERS TK',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb1" => 'REGIONAL INDICATOR SYMBOL LETTERS TL',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS TM',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb3" => 'REGIONAL INDICATOR SYMBOL LETTERS TN',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb4" => 'REGIONAL INDICATOR SYMBOL LETTERS TO',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb7" => 'REGIONAL INDICATOR SYMBOL LETTERS TR',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb9" => 'REGIONAL INDICATOR SYMBOL LETTERS TT',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbb" => 'REGIONAL INDICATOR SYMBOL LETTERS TV',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbc" => 'REGIONAL INDICATOR SYMBOL LETTERS TW',
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbf" => 'REGIONAL INDICATOR SYMBOL LETTERS TZ',
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS UA',
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS UG',
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS UM',
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xb8" => 'REGIONAL INDICATOR SYMBOL LETTERS US',
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xbe" => 'REGIONAL INDICATOR SYMBOL LETTERS UY',
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xbf" => 'REGIONAL INDICATOR SYMBOL LETTERS UZ',
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS VA',
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xa8" => 'REGIONAL INDICATOR SYMBOL LETTERS VC',
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS VE',
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xac" => 'REGIONAL INDICATOR SYMBOL LETTERS VG',
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xae" => 'REGIONAL INDICATOR SYMBOL LETTERS VI',
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xb3" => 'REGIONAL INDICATOR SYMBOL LETTERS VN',
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xba" => 'REGIONAL INDICATOR SYMBOL LETTERS VU',
        "\xf0\x9f\x87\xbc\xf0\x9f\x87\xab" => 'REGIONAL INDICATOR SYMBOL LETTERS WF',
        "\xf0\x9f\x87\xbc\xf0\x9f\x87\xb8" => 'REGIONAL INDICATOR SYMBOL LETTERS WS',
        "\xf0\x9f\x87\xbd\xf0\x9f\x87\xb0" => 'REGIONAL INDICATOR SYMBOL LETTERS XK',
        "\xf0\x9f\x87\xbe\xf0\x9f\x87\xaa" => 'REGIONAL INDICATOR SYMBOL LETTERS YE',
        "\xf0\x9f\x87\xbe\xf0\x9f\x87\xb9" => 'REGIONAL INDICATOR SYMBOL LETTERS YT',
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xa6" => 'REGIONAL INDICATOR SYMBOL LETTERS ZA',
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xb2" => 'REGIONAL INDICATOR SYMBOL LETTERS ZM',
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xbc" => 'REGIONAL INDICATOR SYMBOL LETTERS ZW',
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa6" => '',
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => '',
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7" => '',
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => '',
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => '',
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => '',
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7" => '',
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => '',
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => '',
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x91\xa8" => '',
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x92\x8b\xe2\x80\x8d\xf0\x9f\x91\xa8" => '',
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6" => '',
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => '',
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7" => '',
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => '',
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => '',
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x91\xa9" => '',
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x92\x8b\xe2\x80\x8d\xf0\x9f\x91\xa9" => '',
    ),
    'kaomoji' => array(
    ),
    'unified_to_docomo' => array(
        "\xc2\xa9" => "\xee\x9c\xb1",
        "\xc2\xae" => "\xee\x9c\xb6",
        "\xe2\x80\xbc" => "\xee\x9c\x84",
        "\xe2\x81\x89" => "\xee\x9c\x83",
        "\xe2\x84\xa2" => "\xee\x9c\xb2",
        "\xe2\x84\xb9" => "",
        "\xe2\x86\x94" => "\xee\x9c\xbc",
        "\xe2\x86\x95" => "\xee\x9c\xbd",
        "\xe2\x86\x96" => "\xee\x9a\x97",
        "\xe2\x86\x97" => "\xee\x99\xb8",
        "\xe2\x86\x98" => "\xee\x9a\x96",
        "\xe2\x86\x99" => "\xee\x9a\xa5",
        "\xe2\x86\xa9" => "\xee\x9b\x9a",
        "\xe2\x86\xaa" => "",
        "\xe2\x8c\x9a" => "\xee\x9c\x9f",
        "\xe2\x8c\x9b" => "\xee\x9c\x9c",
        "\xe2\x8c\xa8" => "",
        "\xe2\x8f\xa9" => "",
        "\xe2\x8f\xaa" => "",
        "\xe2\x8f\xab" => "",
        "\xe2\x8f\xac" => "",
        "\xe2\x8f\xad" => "",
        "\xe2\x8f\xae" => "",
        "\xe2\x8f\xaf" => "",
        "\xe2\x8f\xb0" => "\xee\x9a\xba",
        "\xe2\x8f\xb1" => "",
        "\xe2\x8f\xb2" => "",
        "\xe2\x8f\xb3" => "\xee\x9c\x9c",
        "\xe2\x8f\xb8" => "",
        "\xe2\x8f\xb9" => "",
        "\xe2\x8f\xba" => "",
        "\xe2\x93\x82" => "\xee\x99\x9c",
        "\xe2\x96\xaa" => "",
        "\xe2\x96\xab" => "",
        "\xe2\x96\xb6" => "",
        "\xe2\x97\x80" => "",
        "\xe2\x97\xbb" => "",
        "\xe2\x97\xbc" => "",
        "\xe2\x97\xbd" => "",
        "\xe2\x97\xbe" => "",
        "\xe2\x98\x80" => "\xee\x98\xbe",
        "\xe2\x98\x81" => "\xee\x98\xbf",
        "\xe2\x98\x82" => "",
        "\xe2\x98\x83" => "",
        "\xe2\x98\x84" => "",
        "\xe2\x98\x8e" => "\xee\x9a\x87",
        "\xe2\x98\x91" => "",
        "\xe2\x98\x94" => "\xee\x99\x80",
        "\xe2\x98\x95" => "\xee\x99\xb0",
        "\xe2\x98\x98" => "",
        "\xe2\x98\x9d" => "",
        "\xe2\x98\xa0" => "",
        "\xe2\x98\xa2" => "",
        "\xe2\x98\xa3" => "",
        "\xe2\x98\xa6" => "",
        "\xe2\x98\xaa" => "",
        "\xe2\x98\xae" => "",
        "\xe2\x98\xaf" => "",
        "\xe2\x98\xb8" => "",
        "\xe2\x98\xb9" => "",
        "\xe2\x98\xba" => "\xee\x9b\xb0",
        "\xe2\x99\x88" => "\xee\x99\x86",
        "\xe2\x99\x89" => "\xee\x99\x87",
        "\xe2\x99\x8a" => "\xee\x99\x88",
        "\xe2\x99\x8b" => "\xee\x99\x89",
        "\xe2\x99\x8c" => "\xee\x99\x8a",
        "\xe2\x99\x8d" => "\xee\x99\x8b",
        "\xe2\x99\x8e" => "\xee\x99\x8c",
        "\xe2\x99\x8f" => "\xee\x99\x8d",
        "\xe2\x99\x90" => "\xee\x99\x8e",
        "\xe2\x99\x91" => "\xee\x99\x8f",
        "\xe2\x99\x92" => "\xee\x99\x90",
        "\xe2\x99\x93" => "\xee\x99\x91",
        "\xe2\x99\xa0" => "\xee\x9a\x8e",
        "\xe2\x99\xa3" => "\xee\x9a\x90",
        "\xe2\x99\xa5" => "\xee\x9a\x8d",
        "\xe2\x99\xa6" => "\xee\x9a\x8f",
        "\xe2\x99\xa8" => "\xee\x9b\xb7",
        "\xe2\x99\xbb" => "\xee\x9c\xb5",
        "\xe2\x99\xbf" => "\xee\x9a\x9b",
        "\xe2\x9a\x92" => "",
        "\xe2\x9a\x93" => "\xee\x99\xa1",
        "\xe2\x9a\x94" => "",
        "\xe2\x9a\x96" => "",
        "\xe2\x9a\x97" => "",
        "\xe2\x9a\x99" => "",
        "\xe2\x9a\x9b" => "",
        "\xe2\x9a\x9c" => "",
        "\xe2\x9a\xa0" => "\xee\x9c\xb7",
        "\xe2\x9a\xa1" => "\xee\x99\x82",
        "\xe2\x9a\xaa" => "\xee\x9a\x9c",
        "\xe2\x9a\xab" => "\xee\x9a\x9c",
        "\xe2\x9a\xb0" => "",
        "\xe2\x9a\xb1" => "",
        "\xe2\x9a\xbd" => "\xee\x99\x96",
        "\xe2\x9a\xbe" => "\xee\x99\x93",
        "\xe2\x9b\x84" => "\xee\x99\x81",
        "\xe2\x9b\x85" => "\xee\x98\xbe\xee\x98\xbf",
        "\xe2\x9b\x88" => "",
        "\xe2\x9b\x8e" => "",
        "\xe2\x9b\x8f" => "",
        "\xe2\x9b\x91" => "",
        "\xe2\x9b\x93" => "",
        "\xe2\x9b\x94" => "\xee\x9c\xaf",
        "\xe2\x9b\xa9" => "",
        "\xe2\x9b\xaa" => "",
        "\xe2\x9b\xb0" => "",
        "\xe2\x9b\xb1" => "",
        "\xe2\x9b\xb2" => "",
        "\xe2\x9b\xb3" => "\xee\x99\x94",
        "\xe2\x9b\xb4" => "",
        "\xe2\x9b\xb5" => "\xee\x9a\xa3",
        "\xe2\x9b\xb7" => "",
        "\xe2\x9b\xb8" => "",
        "\xe2\x9b\xb9" => "",
        "\xe2\x9b\xba" => "",
        "\xe2\x9b\xbd" => "\xee\x99\xab",
        "\xe2\x9c\x82" => "\xee\x99\xb5",
        "\xe2\x9c\x85" => "",
        "\xe2\x9c\x88" => "\xee\x99\xa2",
        "\xe2\x9c\x89" => "\xee\x9b\x93",
        "\xe2\x9c\x8a" => "\xee\x9a\x93",
        "\xe2\x9c\x8b" => "\xee\x9a\x95",
        "\xe2\x9c\x8c" => "\xee\x9a\x94",
        "\xe2\x9c\x8d" => "",
        "\xe2\x9c\x8f" => "\xee\x9c\x99",
        "\xe2\x9c\x92" => "\xee\x9a\xae",
        "\xe2\x9c\x94" => "",
        "\xe2\x9c\x96" => "",
        "\xe2\x9c\x9d" => "",
        "\xe2\x9c\xa1" => "",
        "\xe2\x9c\xa8" => "\xee\x9b\xba",
        "\xe2\x9c\xb3" => "\xee\x9b\xb8",
        "\xe2\x9c\xb4" => "\xee\x9b\xb8",
        "\xe2\x9d\x84" => "",
        "\xe2\x9d\x87" => "\xee\x9b\xba",
        "\xe2\x9d\x8c" => "",
        "\xe2\x9d\x8e" => "",
        "\xe2\x9d\x93" => "",
        "\xe2\x9d\x94" => "",
        "\xe2\x9d\x95" => "\xee\x9c\x82",
        "\xe2\x9d\x97" => "\xee\x9c\x82",
        "\xe2\x9d\xa3" => "",
        "\xe2\x9d\xa4" => "\xee\x9b\xac",
        "\xe2\x9e\x95" => "",
        "\xe2\x9e\x96" => "",
        "\xe2\x9e\x97" => "",
        "\xe2\x9e\xa1" => "",
        "\xe2\x9e\xb0" => "\xee\x9c\x8a",
        "\xe2\x9e\xbf" => "\xee\x9b\x9f",
        "\xe2\xa4\xb4" => "\xee\x9b\xb5",
        "\xe2\xa4\xb5" => "\xee\x9c\x80",
        "\xe2\xac\x85" => "",
        "\xe2\xac\x86" => "",
        "\xe2\xac\x87" => "",
        "\xe2\xac\x9b" => "",
        "\xe2\xac\x9c" => "",
        "\xe2\xad\x90" => "",
        "\xe2\xad\x95" => "\xee\x9a\xa0",
        "\xe3\x80\xb0" => "\xee\x9c\x89",
        "\xe3\x80\xbd" => "",
        "\xe3\x8a\x97" => "",
        "\xe3\x8a\x99" => "\xee\x9c\xb4",
        "\xf0\x9f\x80\x84" => "",
        "\xf0\x9f\x83\x8f" => "",
        "\xf0\x9f\x85\xb0" => "",
        "\xf0\x9f\x85\xb1" => "",
        "\xf0\x9f\x85\xbe" => "",
        "\xf0\x9f\x85\xbf" => "\xee\x99\xac",
        "\xf0\x9f\x86\x8e" => "",
        "\xf0\x9f\x86\x91" => "\xee\x9b\x9b",
        "\xf0\x9f\x86\x92" => "",
        "\xf0\x9f\x86\x93" => "\xee\x9b\x97",
        "\xf0\x9f\x86\x94" => "\xee\x9b\x98",
        "\xf0\x9f\x86\x95" => "\xee\x9b\x9d",
        "\xf0\x9f\x86\x96" => "\xee\x9c\xaf",
        "\xf0\x9f\x86\x97" => "\xee\x9c\x8b",
        "\xf0\x9f\x86\x98" => "",
        "\xf0\x9f\x86\x99" => "",
        "\xf0\x9f\x86\x9a" => "",
        "\xf0\x9f\x88\x81" => "",
        "\xf0\x9f\x88\x82" => "",
        "\xf0\x9f\x88\x9a" => "",
        "\xf0\x9f\x88\xaf" => "",
        "\xf0\x9f\x88\xb2" => "\xee\x9c\xb8",
        "\xf0\x9f\x88\xb3" => "\xee\x9c\xb9",
        "\xf0\x9f\x88\xb4" => "\xee\x9c\xba",
        "\xf0\x9f\x88\xb5" => "\xee\x9c\xbb",
        "\xf0\x9f\x88\xb6" => "",
        "\xf0\x9f\x88\xb7" => "",
        "\xf0\x9f\x88\xb8" => "",
        "\xf0\x9f\x88\xb9" => "",
        "\xf0\x9f\x88\xba" => "",
        "\xf0\x9f\x89\x90" => "",
        "\xf0\x9f\x89\x91" => "",
        "\xf0\x9f\x8c\x80" => "\xee\x99\x83",
        "\xf0\x9f\x8c\x81" => "\xee\x99\x84",
        "\xf0\x9f\x8c\x82" => "\xee\x99\x85",
        "\xf0\x9f\x8c\x83" => "\xee\x9a\xb3",
        "\xf0\x9f\x8c\x84" => "\xee\x98\xbe",
        "\xf0\x9f\x8c\x85" => "\xee\x98\xbe",
        "\xf0\x9f\x8c\x86" => "",
        "\xf0\x9f\x8c\x87" => "\xee\x98\xbe",
        "\xf0\x9f\x8c\x88" => "",
        "\xf0\x9f\x8c\x89" => "\xee\x9a\xb3",
        "\xf0\x9f\x8c\x8a" => "\xee\x9c\xbf",
        "\xf0\x9f\x8c\x8b" => "",
        "\xf0\x9f\x8c\x8c" => "\xee\x9a\xb3",
        "\xf0\x9f\x8c\x8d" => "",
        "\xf0\x9f\x8c\x8e" => "",
        "\xf0\x9f\x8c\x8f" => "",
        "\xf0\x9f\x8c\x90" => "",
        "\xf0\x9f\x8c\x91" => "\xee\x9a\x9c",
        "\xf0\x9f\x8c\x92" => "",
        "\xf0\x9f\x8c\x93" => "\xee\x9a\x9e",
        "\xf0\x9f\x8c\x94" => "\xee\x9a\x9d",
        "\xf0\x9f\x8c\x95" => "\xee\x9a\xa0",
        "\xf0\x9f\x8c\x96" => "",
        "\xf0\x9f\x8c\x97" => "",
        "\xf0\x9f\x8c\x98" => "",
        "\xf0\x9f\x8c\x99" => "\xee\x9a\x9f",
        "\xf0\x9f\x8c\x9a" => "",
        "\xf0\x9f\x8c\x9b" => "\xee\x9a\x9e",
        "\xf0\x9f\x8c\x9c" => "",
        "\xf0\x9f\x8c\x9d" => "",
        "\xf0\x9f\x8c\x9e" => "",
        "\xf0\x9f\x8c\x9f" => "",
        "\xf0\x9f\x8c\xa0" => "",
        "\xf0\x9f\x8c\xa1" => "",
        "\xf0\x9f\x8c\xa4" => "",
        "\xf0\x9f\x8c\xa5" => "",
        "\xf0\x9f\x8c\xa6" => "",
        "\xf0\x9f\x8c\xa7" => "",
        "\xf0\x9f\x8c\xa8" => "",
        "\xf0\x9f\x8c\xa9" => "",
        "\xf0\x9f\x8c\xaa" => "",
        "\xf0\x9f\x8c\xab" => "",
        "\xf0\x9f\x8c\xac" => "",
        "\xf0\x9f\x8c\xad" => "",
        "\xf0\x9f\x8c\xae" => "",
        "\xf0\x9f\x8c\xaf" => "",
        "\xf0\x9f\x8c\xb0" => "",
        "\xf0\x9f\x8c\xb1" => "\xee\x9d\x86",
        "\xf0\x9f\x8c\xb2" => "",
        "\xf0\x9f\x8c\xb3" => "",
        "\xf0\x9f\x8c\xb4" => "",
        "\xf0\x9f\x8c\xb5" => "",
        "\xf0\x9f\x8c\xb6" => "",
        "\xf0\x9f\x8c\xb7" => "\xee\x9d\x83",
        "\xf0\x9f\x8c\xb8" => "\xee\x9d\x88",
        "\xf0\x9f\x8c\xb9" => "",
        "\xf0\x9f\x8c\xba" => "",
        "\xf0\x9f\x8c\xbb" => "",
        "\xf0\x9f\x8c\xbc" => "",
        "\xf0\x9f\x8c\xbd" => "",
        "\xf0\x9f\x8c\xbe" => "",
        "\xf0\x9f\x8c\xbf" => "\xee\x9d\x81",
        "\xf0\x9f\x8d\x80" => "\xee\x9d\x81",
        "\xf0\x9f\x8d\x81" => "\xee\x9d\x87",
        "\xf0\x9f\x8d\x82" => "\xee\x9d\x87",
        "\xf0\x9f\x8d\x83" => "",
        "\xf0\x9f\x8d\x84" => "",
        "\xf0\x9f\x8d\x85" => "",
        "\xf0\x9f\x8d\x86" => "",
        "\xf0\x9f\x8d\x87" => "",
        "\xf0\x9f\x8d\x88" => "",
        "\xf0\x9f\x8d\x89" => "",
        "\xf0\x9f\x8d\x8a" => "",
        "\xf0\x9f\x8d\x8b" => "",
        "\xf0\x9f\x8d\x8c" => "\xee\x9d\x84",
        "\xf0\x9f\x8d\x8d" => "",
        "\xf0\x9f\x8d\x8e" => "\xee\x9d\x85",
        "\xf0\x9f\x8d\x8f" => "\xee\x9d\x85",
        "\xf0\x9f\x8d\x90" => "",
        "\xf0\x9f\x8d\x91" => "",
        "\xf0\x9f\x8d\x92" => "\xee\x9d\x82",
        "\xf0\x9f\x8d\x93" => "",
        "\xf0\x9f\x8d\x94" => "\xee\x99\xb3",
        "\xf0\x9f\x8d\x95" => "",
        "\xf0\x9f\x8d\x96" => "",
        "\xf0\x9f\x8d\x97" => "",
        "\xf0\x9f\x8d\x98" => "",
        "\xf0\x9f\x8d\x99" => "\xee\x9d\x89",
        "\xf0\x9f\x8d\x9a" => "\xee\x9d\x8c",
        "\xf0\x9f\x8d\x9b" => "",
        "\xf0\x9f\x8d\x9c" => "\xee\x9d\x8c",
        "\xf0\x9f\x8d\x9d" => "",
        "\xf0\x9f\x8d\x9e" => "\xee\x9d\x8d",
        "\xf0\x9f\x8d\x9f" => "",
        "\xf0\x9f\x8d\xa0" => "",
        "\xf0\x9f\x8d\xa1" => "",
        "\xf0\x9f\x8d\xa2" => "",
        "\xf0\x9f\x8d\xa3" => "",
        "\xf0\x9f\x8d\xa4" => "",
        "\xf0\x9f\x8d\xa5" => "\xee\x99\x83",
        "\xf0\x9f\x8d\xa6" => "",
        "\xf0\x9f\x8d\xa7" => "",
        "\xf0\x9f\x8d\xa8" => "",
        "\xf0\x9f\x8d\xa9" => "",
        "\xf0\x9f\x8d\xaa" => "",
        "\xf0\x9f\x8d\xab" => "",
        "\xf0\x9f\x8d\xac" => "",
        "\xf0\x9f\x8d\xad" => "",
        "\xf0\x9f\x8d\xae" => "",
        "\xf0\x9f\x8d\xaf" => "",
        "\xf0\x9f\x8d\xb0" => "\xee\x9d\x8a",
        "\xf0\x9f\x8d\xb1" => "",
        "\xf0\x9f\x8d\xb2" => "",
        "\xf0\x9f\x8d\xb3" => "",
        "\xf0\x9f\x8d\xb4" => "\xee\x99\xaf",
        "\xf0\x9f\x8d\xb5" => "\xee\x9c\x9e",
        "\xf0\x9f\x8d\xb6" => "\xee\x9d\x8b",
        "\xf0\x9f\x8d\xb7" => "\xee\x9d\x96",
        "\xf0\x9f\x8d\xb8" => "\xee\x99\xb1",
        "\xf0\x9f\x8d\xb9" => "\xee\x99\xb1",
        "\xf0\x9f\x8d\xba" => "\xee\x99\xb2",
        "\xf0\x9f\x8d\xbb" => "\xee\x99\xb2",
        "\xf0\x9f\x8d\xbc" => "",
        "\xf0\x9f\x8d\xbd" => "",
        "\xf0\x9f\x8d\xbe" => "",
        "\xf0\x9f\x8d\xbf" => "",
        "\xf0\x9f\x8e\x80" => "\xee\x9a\x84",
        "\xf0\x9f\x8e\x81" => "\xee\x9a\x85",
        "\xf0\x9f\x8e\x82" => "\xee\x9a\x86",
        "\xf0\x9f\x8e\x83" => "",
        "\xf0\x9f\x8e\x84" => "\xee\x9a\xa4",
        "\xf0\x9f\x8e\x85" => "",
        "\xf0\x9f\x8e\x86" => "",
        "\xf0\x9f\x8e\x87" => "",
        "\xf0\x9f\x8e\x88" => "",
        "\xf0\x9f\x8e\x89" => "",
        "\xf0\x9f\x8e\x8a" => "",
        "\xf0\x9f\x8e\x8b" => "",
        "\xf0\x9f\x8e\x8c" => "",
        "\xf0\x9f\x8e\x8d" => "",
        "\xf0\x9f\x8e\x8e" => "",
        "\xf0\x9f\x8e\x8f" => "",
        "\xf0\x9f\x8e\x90" => "",
        "\xf0\x9f\x8e\x91" => "",
        "\xf0\x9f\x8e\x92" => "",
        "\xf0\x9f\x8e\x93" => "",
        "\xf0\x9f\x8e\x96" => "",
        "\xf0\x9f\x8e\x97" => "",
        "\xf0\x9f\x8e\x99" => "",
        "\xf0\x9f\x8e\x9a" => "",
        "\xf0\x9f\x8e\x9b" => "",
        "\xf0\x9f\x8e\x9e" => "",
        "\xf0\x9f\x8e\x9f" => "",
        "\xf0\x9f\x8e\xa0" => "\xee\x99\xb9",
        "\xf0\x9f\x8e\xa1" => "",
        "\xf0\x9f\x8e\xa2" => "",
        "\xf0\x9f\x8e\xa3" => "\xee\x9d\x91",
        "\xf0\x9f\x8e\xa4" => "\xee\x99\xb6",
        "\xf0\x9f\x8e\xa5" => "\xee\x99\xb7",
        "\xf0\x9f\x8e\xa6" => "\xee\x99\xb7",
        "\xf0\x9f\x8e\xa7" => "\xee\x99\xba",
        "\xf0\x9f\x8e\xa8" => "\xee\x99\xbb",
        "\xf0\x9f\x8e\xa9" => "\xee\x99\xbc",
        "\xf0\x9f\x8e\xaa" => "\xee\x99\xbd",
        "\xf0\x9f\x8e\xab" => "\xee\x99\xbe",
        "\xf0\x9f\x8e\xac" => "\xee\x9a\xac",
        "\xf0\x9f\x8e\xad" => "",
        "\xf0\x9f\x8e\xae" => "\xee\x9a\x8b",
        "\xf0\x9f\x8e\xaf" => "",
        "\xf0\x9f\x8e\xb0" => "",
        "\xf0\x9f\x8e\xb1" => "",
        "\xf0\x9f\x8e\xb2" => "",
        "\xf0\x9f\x8e\xb3" => "",
        "\xf0\x9f\x8e\xb4" => "",
        "\xf0\x9f\x8e\xb5" => "\xee\x9b\xb6",
        "\xf0\x9f\x8e\xb6" => "\xee\x9b\xbf",
        "\xf0\x9f\x8e\xb7" => "",
        "\xf0\x9f\x8e\xb8" => "",
        "\xf0\x9f\x8e\xb9" => "",
        "\xf0\x9f\x8e\xba" => "",
        "\xf0\x9f\x8e\xbb" => "",
        "\xf0\x9f\x8e\xbc" => "\xee\x9b\xbf",
        "\xf0\x9f\x8e\xbd" => "\xee\x99\x92",
        "\xf0\x9f\x8e\xbe" => "\xee\x99\x95",
        "\xf0\x9f\x8e\xbf" => "\xee\x99\x97",
        "\xf0\x9f\x8f\x80" => "\xee\x99\x98",
        "\xf0\x9f\x8f\x81" => "\xee\x99\x99",
        "\xf0\x9f\x8f\x82" => "\xee\x9c\x92",
        "\xf0\x9f\x8f\x83" => "\xee\x9c\xb3",
        "\xf0\x9f\x8f\x84" => "\xee\x9c\x92",
        "\xf0\x9f\x8f\x85" => "",
        "\xf0\x9f\x8f\x86" => "",
        "\xf0\x9f\x8f\x87" => "",
        "\xf0\x9f\x8f\x88" => "",
        "\xf0\x9f\x8f\x89" => "",
        "\xf0\x9f\x8f\x8a" => "",
        "\xf0\x9f\x8f\x8b" => "",
        "\xf0\x9f\x8f\x8c" => "",
        "\xf0\x9f\x8f\x8d" => "",
        "\xf0\x9f\x8f\x8e" => "",
        "\xf0\x9f\x8f\x8f" => "",
        "\xf0\x9f\x8f\x90" => "",
        "\xf0\x9f\x8f\x91" => "",
        "\xf0\x9f\x8f\x92" => "",
        "\xf0\x9f\x8f\x93" => "",
        "\xf0\x9f\x8f\x94" => "",
        "\xf0\x9f\x8f\x95" => "",
        "\xf0\x9f\x8f\x96" => "",
        "\xf0\x9f\x8f\x97" => "",
        "\xf0\x9f\x8f\x98" => "",
        "\xf0\x9f\x8f\x99" => "",
        "\xf0\x9f\x8f\x9a" => "",
        "\xf0\x9f\x8f\x9b" => "",
        "\xf0\x9f\x8f\x9c" => "",
        "\xf0\x9f\x8f\x9d" => "",
        "\xf0\x9f\x8f\x9e" => "",
        "\xf0\x9f\x8f\x9f" => "",
        "\xf0\x9f\x8f\xa0" => "\xee\x99\xa3",
        "\xf0\x9f\x8f\xa1" => "\xee\x99\xa3",
        "\xf0\x9f\x8f\xa2" => "\xee\x99\xa4",
        "\xf0\x9f\x8f\xa3" => "\xee\x99\xa5",
        "\xf0\x9f\x8f\xa4" => "",
        "\xf0\x9f\x8f\xa5" => "\xee\x99\xa6",
        "\xf0\x9f\x8f\xa6" => "\xee\x99\xa7",
        "\xf0\x9f\x8f\xa7" => "\xee\x99\xa8",
        "\xf0\x9f\x8f\xa8" => "\xee\x99\xa9",
        "\xf0\x9f\x8f\xa9" => "\xee\x99\xa9\xee\x9b\xaf",
        "\xf0\x9f\x8f\xaa" => "\xee\x99\xaa",
        "\xf0\x9f\x8f\xab" => "\xee\x9c\xbe",
        "\xf0\x9f\x8f\xac" => "",
        "\xf0\x9f\x8f\xad" => "",
        "\xf0\x9f\x8f\xae" => "\xee\x9d\x8b",
        "\xf0\x9f\x8f\xaf" => "",
        "\xf0\x9f\x8f\xb0" => "",
        "\xf0\x9f\x8f\xb3" => "",
        "\xf0\x9f\x8f\xb4" => "",
        "\xf0\x9f\x8f\xb5" => "",
        "\xf0\x9f\x8f\xb7" => "",
        "\xf0\x9f\x8f\xb8" => "",
        "\xf0\x9f\x8f\xb9" => "",
        "\xf0\x9f\x8f\xba" => "",
        "\xf0\x9f\x8f\xbb" => "",
        "\xf0\x9f\x8f\xbc" => "",
        "\xf0\x9f\x8f\xbd" => "",
        "\xf0\x9f\x8f\xbe" => "",
        "\xf0\x9f\x8f\xbf" => "",
        "\xf0\x9f\x90\x80" => "",
        "\xf0\x9f\x90\x81" => "",
        "\xf0\x9f\x90\x82" => "",
        "\xf0\x9f\x90\x83" => "",
        "\xf0\x9f\x90\x84" => "",
        "\xf0\x9f\x90\x85" => "",
        "\xf0\x9f\x90\x86" => "",
        "\xf0\x9f\x90\x87" => "",
        "\xf0\x9f\x90\x88" => "",
        "\xf0\x9f\x90\x89" => "",
        "\xf0\x9f\x90\x8a" => "",
        "\xf0\x9f\x90\x8b" => "",
        "\xf0\x9f\x90\x8c" => "\xee\x9d\x8e",
        "\xf0\x9f\x90\x8d" => "",
        "\xf0\x9f\x90\x8e" => "\xee\x9d\x94",
        "\xf0\x9f\x90\x8f" => "",
        "\xf0\x9f\x90\x90" => "",
        "\xf0\x9f\x90\x91" => "",
        "\xf0\x9f\x90\x92" => "",
        "\xf0\x9f\x90\x93" => "",
        "\xf0\x9f\x90\x94" => "",
        "\xf0\x9f\x90\x95" => "",
        "\xf0\x9f\x90\x96" => "",
        "\xf0\x9f\x90\x97" => "",
        "\xf0\x9f\x90\x98" => "",
        "\xf0\x9f\x90\x99" => "",
        "\xf0\x9f\x90\x9a" => "",
        "\xf0\x9f\x90\x9b" => "",
        "\xf0\x9f\x90\x9c" => "",
        "\xf0\x9f\x90\x9d" => "",
        "\xf0\x9f\x90\x9e" => "",
        "\xf0\x9f\x90\x9f" => "\xee\x9d\x91",
        "\xf0\x9f\x90\xa0" => "\xee\x9d\x91",
        "\xf0\x9f\x90\xa1" => "\xee\x9d\x91",
        "\xf0\x9f\x90\xa2" => "",
        "\xf0\x9f\x90\xa3" => "\xee\x9d\x8f",
        "\xf0\x9f\x90\xa4" => "\xee\x9d\x8f",
        "\xf0\x9f\x90\xa5" => "\xee\x9d\x8f",
        "\xf0\x9f\x90\xa6" => "\xee\x9d\x8f",
        "\xf0\x9f\x90\xa7" => "\xee\x9d\x90",
        "\xf0\x9f\x90\xa8" => "",
        "\xf0\x9f\x90\xa9" => "\xee\x9a\xa1",
        "\xf0\x9f\x90\xaa" => "",
        "\xf0\x9f\x90\xab" => "",
        "\xf0\x9f\x90\xac" => "",
        "\xf0\x9f\x90\xad" => "",
        "\xf0\x9f\x90\xae" => "",
        "\xf0\x9f\x90\xaf" => "",
        "\xf0\x9f\x90\xb0" => "",
        "\xf0\x9f\x90\xb1" => "\xee\x9a\xa2",
        "\xf0\x9f\x90\xb2" => "",
        "\xf0\x9f\x90\xb3" => "",
        "\xf0\x9f\x90\xb4" => "\xee\x9d\x94",
        "\xf0\x9f\x90\xb5" => "",
        "\xf0\x9f\x90\xb6" => "\xee\x9a\xa1",
        "\xf0\x9f\x90\xb7" => "\xee\x9d\x95",
        "\xf0\x9f\x90\xb8" => "",
        "\xf0\x9f\x90\xb9" => "",
        "\xf0\x9f\x90\xba" => "\xee\x9a\xa1",
        "\xf0\x9f\x90\xbb" => "",
        "\xf0\x9f\x90\xbc" => "",
        "\xf0\x9f\x90\xbd" => "\xee\x9d\x95",
        "\xf0\x9f\x90\xbe" => "\xee\x9a\x98",
        "\xf0\x9f\x90\xbf" => "",
        "\xf0\x9f\x91\x80" => "\xee\x9a\x91",
        "\xf0\x9f\x91\x81" => "",
        "\xf0\x9f\x91\x82" => "\xee\x9a\x92",
        "\xf0\x9f\x91\x83" => "",
        "\xf0\x9f\x91\x84" => "\xee\x9b\xb9",
        "\xf0\x9f\x91\x85" => "\xee\x9c\xa8",
        "\xf0\x9f\x91\x86" => "",
        "\xf0\x9f\x91\x87" => "",
        "\xf0\x9f\x91\x88" => "",
        "\xf0\x9f\x91\x89" => "",
        "\xf0\x9f\x91\x8a" => "\xee\x9b\xbd",
        "\xf0\x9f\x91\x8b" => "\xee\x9a\x95",
        "\xf0\x9f\x91\x8c" => "\xee\x9c\x8b",
        "\xf0\x9f\x91\x8d" => "\xee\x9c\xa7",
        "\xf0\x9f\x91\x8e" => "\xee\x9c\x80",
        "\xf0\x9f\x91\x8f" => "",
        "\xf0\x9f\x91\x90" => "\xee\x9a\x95",
        "\xf0\x9f\x91\x91" => "\xee\x9c\x9a",
        "\xf0\x9f\x91\x92" => "",
        "\xf0\x9f\x91\x93" => "\xee\x9a\x9a",
        "\xf0\x9f\x91\x94" => "",
        "\xf0\x9f\x91\x95" => "\xee\x9c\x8e",
        "\xf0\x9f\x91\x96" => "\xee\x9c\x91",
        "\xf0\x9f\x91\x97" => "",
        "\xf0\x9f\x91\x98" => "",
        "\xf0\x9f\x91\x99" => "",
        "\xf0\x9f\x91\x9a" => "\xee\x9c\x8e",
        "\xf0\x9f\x91\x9b" => "\xee\x9c\x8f",
        "\xf0\x9f\x91\x9c" => "\xee\x9a\x82",
        "\xf0\x9f\x91\x9d" => "\xee\x9a\xad",
        "\xf0\x9f\x91\x9e" => "\xee\x9a\x99",
        "\xf0\x9f\x91\x9f" => "\xee\x9a\x99",
        "\xf0\x9f\x91\xa0" => "\xee\x99\xb4",
        "\xf0\x9f\x91\xa1" => "\xee\x99\xb4",
        "\xf0\x9f\x91\xa2" => "",
        "\xf0\x9f\x91\xa3" => "\xee\x9a\x98",
        "\xf0\x9f\x91\xa4" => "\xee\x9a\xb1",
        "\xf0\x9f\x91\xa5" => "",
        "\xf0\x9f\x91\xa6" => "\xee\x9b\xb0",
        "\xf0\x9f\x91\xa7" => "\xee\x9b\xb0",
        "\xf0\x9f\x91\xa8" => "\xee\x9b\xb0",
        "\xf0\x9f\x91\xa9" => "\xee\x9b\xb0",
        "\xf0\x9f\x91\xaa" => "",
        "\xf0\x9f\x91\xab" => "",
        "\xf0\x9f\x91\xac" => "",
        "\xf0\x9f\x91\xad" => "",
        "\xf0\x9f\x91\xae" => "",
        "\xf0\x9f\x91\xaf" => "",
        "\xf0\x9f\x91\xb0" => "",
        "\xf0\x9f\x91\xb1" => "",
        "\xf0\x9f\x91\xb2" => "",
        "\xf0\x9f\x91\xb3" => "",
        "\xf0\x9f\x91\xb4" => "",
        "\xf0\x9f\x91\xb5" => "",
        "\xf0\x9f\x91\xb6" => "",
        "\xf0\x9f\x91\xb7" => "",
        "\xf0\x9f\x91\xb8" => "",
        "\xf0\x9f\x91\xb9" => "",
        "\xf0\x9f\x91\xba" => "",
        "\xf0\x9f\x91\xbb" => "",
        "\xf0\x9f\x91\xbc" => "",
        "\xf0\x9f\x91\xbd" => "",
        "\xf0\x9f\x91\xbe" => "",
        "\xf0\x9f\x91\xbf" => "",
        "\xf0\x9f\x92\x80" => "",
        "\xf0\x9f\x92\x81" => "",
        "\xf0\x9f\x92\x82" => "",
        "\xf0\x9f\x92\x83" => "",
        "\xf0\x9f\x92\x84" => "\xee\x9c\x90",
        "\xf0\x9f\x92\x85" => "",
        "\xf0\x9f\x92\x86" => "",
        "\xf0\x9f\x92\x87" => "\xee\x99\xb5",
        "\xf0\x9f\x92\x88" => "",
        "\xf0\x9f\x92\x89" => "",
        "\xf0\x9f\x92\x8a" => "",
        "\xf0\x9f\x92\x8b" => "\xee\x9b\xb9",
        "\xf0\x9f\x92\x8c" => "\xee\x9c\x97",
        "\xf0\x9f\x92\x8d" => "\xee\x9c\x9b",
        "\xf0\x9f\x92\x8e" => "\xee\x9c\x9b",
        "\xf0\x9f\x92\x8f" => "\xee\x9b\xb9",
        "\xf0\x9f\x92\x90" => "",
        "\xf0\x9f\x92\x91" => "\xee\x9b\xad",
        "\xf0\x9f\x92\x92" => "",
        "\xf0\x9f\x92\x93" => "\xee\x9b\xad",
        "\xf0\x9f\x92\x94" => "\xee\x9b\xae",
        "\xf0\x9f\x92\x95" => "\xee\x9b\xaf",
        "\xf0\x9f\x92\x96" => "\xee\x9b\xac",
        "\xf0\x9f\x92\x97" => "\xee\x9b\xad",
        "\xf0\x9f\x92\x98" => "\xee\x9b\xac",
        "\xf0\x9f\x92\x99" => "\xee\x9b\xac",
        "\xf0\x9f\x92\x9a" => "\xee\x9b\xac",
        "\xf0\x9f\x92\x9b" => "\xee\x9b\xac",
        "\xf0\x9f\x92\x9c" => "\xee\x9b\xac",
        "\xf0\x9f\x92\x9d" => "\xee\x9b\xac",
        "\xf0\x9f\x92\x9e" => "\xee\x9b\xad",
        "\xf0\x9f\x92\x9f" => "\xee\x9b\xb8",
        "\xf0\x9f\x92\xa0" => "\xee\x9b\xb8",
        "\xf0\x9f\x92\xa1" => "\xee\x9b\xbb",
        "\xf0\x9f\x92\xa2" => "\xee\x9b\xbc",
        "\xf0\x9f\x92\xa3" => "\xee\x9b\xbe",
        "\xf0\x9f\x92\xa4" => "\xee\x9c\x81",
        "\xf0\x9f\x92\xa5" => "\xee\x9c\x85",
        "\xf0\x9f\x92\xa6" => "\xee\x9c\x86",
        "\xf0\x9f\x92\xa7" => "\xee\x9c\x87",
        "\xf0\x9f\x92\xa8" => "\xee\x9c\x88",
        "\xf0\x9f\x92\xa9" => "",
        "\xf0\x9f\x92\xaa" => "",
        "\xf0\x9f\x92\xab" => "",
        "\xf0\x9f\x92\xac" => "",
        "\xf0\x9f\x92\xad" => "",
        "\xf0\x9f\x92\xae" => "",
        "\xf0\x9f\x92\xaf" => "",
        "\xf0\x9f\x92\xb0" => "\xee\x9c\x95",
        "\xf0\x9f\x92\xb1" => "",
        "\xf0\x9f\x92\xb2" => "\xee\x9c\x95",
        "\xf0\x9f\x92\xb3" => "",
        "\xf0\x9f\x92\xb4" => "\xee\x9b\x96",
        "\xf0\x9f\x92\xb5" => "\xee\x9c\x95",
        "\xf0\x9f\x92\xb6" => "",
        "\xf0\x9f\x92\xb7" => "",
        "\xf0\x9f\x92\xb8" => "",
        "\xf0\x9f\x92\xb9" => "",
        "\xf0\x9f\x92\xba" => "\xee\x9a\xb2",
        "\xf0\x9f\x92\xbb" => "\xee\x9c\x96",
        "\xf0\x9f\x92\xbc" => "\xee\x9a\x82",
        "\xf0\x9f\x92\xbd" => "",
        "\xf0\x9f\x92\xbe" => "",
        "\xf0\x9f\x92\xbf" => "\xee\x9a\x8c",
        "\xf0\x9f\x93\x80" => "\xee\x9a\x8c",
        "\xf0\x9f\x93\x81" => "",
        "\xf0\x9f\x93\x82" => "",
        "\xf0\x9f\x93\x83" => "\xee\x9a\x89",
        "\xf0\x9f\x93\x84" => "\xee\x9a\x89",
        "\xf0\x9f\x93\x85" => "",
        "\xf0\x9f\x93\x86" => "",
        "\xf0\x9f\x93\x87" => "\xee\x9a\x83",
        "\xf0\x9f\x93\x88" => "",
        "\xf0\x9f\x93\x89" => "",
        "\xf0\x9f\x93\x8a" => "",
        "\xf0\x9f\x93\x8b" => "\xee\x9a\x89",
        "\xf0\x9f\x93\x8c" => "",
        "\xf0\x9f\x93\x8d" => "",
        "\xf0\x9f\x93\x8e" => "\xee\x9c\xb0",
        "\xf0\x9f\x93\x8f" => "",
        "\xf0\x9f\x93\x90" => "",
        "\xf0\x9f\x93\x91" => "\xee\x9a\x89",
        "\xf0\x9f\x93\x92" => "\xee\x9a\x83",
        "\xf0\x9f\x93\x93" => "\xee\x9a\x83",
        "\xf0\x9f\x93\x94" => "\xee\x9a\x83",
        "\xf0\x9f\x93\x95" => "\xee\x9a\x83",
        "\xf0\x9f\x93\x96" => "\xee\x9a\x83",
        "\xf0\x9f\x93\x97" => "\xee\x9a\x83",
        "\xf0\x9f\x93\x98" => "\xee\x9a\x83",
        "\xf0\x9f\x93\x99" => "\xee\x9a\x83",
        "\xf0\x9f\x93\x9a" => "\xee\x9a\x83",
        "\xf0\x9f\x93\x9b" => "",
        "\xf0\x9f\x93\x9c" => "\xee\x9c\x8a",
        "\xf0\x9f\x93\x9d" => "\xee\x9a\x89",
        "\xf0\x9f\x93\x9e" => "\xee\x9a\x87",
        "\xf0\x9f\x93\x9f" => "\xee\x99\x9a",
        "\xf0\x9f\x93\xa0" => "\xee\x9b\x90",
        "\xf0\x9f\x93\xa1" => "",
        "\xf0\x9f\x93\xa2" => "",
        "\xf0\x9f\x93\xa3" => "",
        "\xf0\x9f\x93\xa4" => "",
        "\xf0\x9f\x93\xa5" => "",
        "\xf0\x9f\x93\xa6" => "\xee\x9a\x85",
        "\xf0\x9f\x93\xa7" => "\xee\x9b\x93",
        "\xf0\x9f\x93\xa8" => "\xee\x9b\x8f",
        "\xf0\x9f\x93\xa9" => "\xee\x9b\x8f",
        "\xf0\x9f\x93\xaa" => "\xee\x99\xa5",
        "\xf0\x9f\x93\xab" => "\xee\x99\xa5",
        "\xf0\x9f\x93\xac" => "",
        "\xf0\x9f\x93\xad" => "",
        "\xf0\x9f\x93\xae" => "\xee\x99\xa5",
        "\xf0\x9f\x93\xaf" => "",
        "\xf0\x9f\x93\xb0" => "",
        "\xf0\x9f\x93\xb1" => "\xee\x9a\x88",
        "\xf0\x9f\x93\xb2" => "\xee\x9b\x8e",
        "\xf0\x9f\x93\xb3" => "",
        "\xf0\x9f\x93\xb4" => "",
        "\xf0\x9f\x93\xb5" => "",
        "\xf0\x9f\x93\xb6" => "",
        "\xf0\x9f\x93\xb7" => "\xee\x9a\x81",
        "\xf0\x9f\x93\xb8" => "",
        "\xf0\x9f\x93\xb9" => "\xee\x99\xb7",
        "\xf0\x9f\x93\xba" => "\xee\x9a\x8a",
        "\xf0\x9f\x93\xbb" => "",
        "\xf0\x9f\x93\xbc" => "",
        "\xf0\x9f\x93\xbd" => "",
        "\xf0\x9f\x93\xbf" => "",
        "\xf0\x9f\x94\x80" => "",
        "\xf0\x9f\x94\x81" => "",
        "\xf0\x9f\x94\x82" => "",
        "\xf0\x9f\x94\x83" => "\xee\x9c\xb5",
        "\xf0\x9f\x94\x84" => "",
        "\xf0\x9f\x94\x85" => "",
        "\xf0\x9f\x94\x86" => "",
        "\xf0\x9f\x94\x87" => "",
        "\xf0\x9f\x94\x88" => "",
        "\xf0\x9f\x94\x89" => "",
        "\xf0\x9f\x94\x8a" => "",
        "\xf0\x9f\x94\x8b" => "",
        "\xf0\x9f\x94\x8c" => "",
        "\xf0\x9f\x94\x8d" => "\xee\x9b\x9c",
        "\xf0\x9f\x94\x8e" => "\xee\x9b\x9c",
        "\xf0\x9f\x94\x8f" => "\xee\x9b\x99",
        "\xf0\x9f\x94\x90" => "\xee\x9b\x99",
        "\xf0\x9f\x94\x91" => "\xee\x9b\x99",
        "\xf0\x9f\x94\x92" => "\xee\x9b\x99",
        "\xf0\x9f\x94\x93" => "\xee\x9b\x99",
        "\xf0\x9f\x94\x94" => "\xee\x9c\x93",
        "\xf0\x9f\x94\x95" => "",
        "\xf0\x9f\x94\x96" => "",
        "\xf0\x9f\x94\x97" => "",
        "\xf0\x9f\x94\x98" => "",
        "\xf0\x9f\x94\x99" => "",
        "\xf0\x9f\x94\x9a" => "\xee\x9a\xb9",
        "\xf0\x9f\x94\x9b" => "\xee\x9a\xb8",
        "\xf0\x9f\x94\x9c" => "\xee\x9a\xb7",
        "\xf0\x9f\x94\x9d" => "",
        "\xf0\x9f\x94\x9e" => "",
        "\xf0\x9f\x94\x9f" => "",
        "\xf0\x9f\x94\xa0" => "",
        "\xf0\x9f\x94\xa1" => "",
        "\xf0\x9f\x94\xa2" => "",
        "\xf0\x9f\x94\xa3" => "",
        "\xf0\x9f\x94\xa4" => "",
        "\xf0\x9f\x94\xa5" => "",
        "\xf0\x9f\x94\xa6" => "\xee\x9b\xbb",
        "\xf0\x9f\x94\xa7" => "\xee\x9c\x98",
        "\xf0\x9f\x94\xa8" => "",
        "\xf0\x9f\x94\xa9" => "",
        "\xf0\x9f\x94\xaa" => "",
        "\xf0\x9f\x94\xab" => "",
        "\xf0\x9f\x94\xac" => "",
        "\xf0\x9f\x94\xad" => "",
        "\xf0\x9f\x94\xae" => "",
        "\xf0\x9f\x94\xaf" => "",
        "\xf0\x9f\x94\xb0" => "",
        "\xf0\x9f\x94\xb1" => "\xee\x9c\x9a",
        "\xf0\x9f\x94\xb2" => "\xee\x9a\x9c",
        "\xf0\x9f\x94\xb3" => "\xee\x9a\x9c",
        "\xf0\x9f\x94\xb4" => "\xee\x9a\x9c",
        "\xf0\x9f\x94\xb5" => "\xee\x9a\x9c",
        "\xf0\x9f\x94\xb6" => "",
        "\xf0\x9f\x94\xb7" => "",
        "\xf0\x9f\x94\xb8" => "",
        "\xf0\x9f\x94\xb9" => "",
        "\xf0\x9f\x94\xba" => "",
        "\xf0\x9f\x94\xbb" => "",
        "\xf0\x9f\x94\xbc" => "",
        "\xf0\x9f\x94\xbd" => "",
        "\xf0\x9f\x95\x89" => "",
        "\xf0\x9f\x95\x8a" => "",
        "\xf0\x9f\x95\x8b" => "",
        "\xf0\x9f\x95\x8c" => "",
        "\xf0\x9f\x95\x8d" => "",
        "\xf0\x9f\x95\x8e" => "",
        "\xf0\x9f\x95\x90" => "\xee\x9a\xba",
        "\xf0\x9f\x95\x91" => "\xee\x9a\xba",
        "\xf0\x9f\x95\x92" => "\xee\x9a\xba",
        "\xf0\x9f\x95\x93" => "\xee\x9a\xba",
        "\xf0\x9f\x95\x94" => "\xee\x9a\xba",
        "\xf0\x9f\x95\x95" => "\xee\x9a\xba",
        "\xf0\x9f\x95\x96" => "\xee\x9a\xba",
        "\xf0\x9f\x95\x97" => "\xee\x9a\xba",
        "\xf0\x9f\x95\x98" => "\xee\x9a\xba",
        "\xf0\x9f\x95\x99" => "\xee\x9a\xba",
        "\xf0\x9f\x95\x9a" => "\xee\x9a\xba",
        "\xf0\x9f\x95\x9b" => "\xee\x9a\xba",
        "\xf0\x9f\x95\x9c" => "",
        "\xf0\x9f\x95\x9d" => "",
        "\xf0\x9f\x95\x9e" => "",
        "\xf0\x9f\x95\x9f" => "",
        "\xf0\x9f\x95\xa0" => "",
        "\xf0\x9f\x95\xa1" => "",
        "\xf0\x9f\x95\xa2" => "",
        "\xf0\x9f\x95\xa3" => "",
        "\xf0\x9f\x95\xa4" => "",
        "\xf0\x9f\x95\xa5" => "",
        "\xf0\x9f\x95\xa6" => "",
        "\xf0\x9f\x95\xa7" => "",
        "\xf0\x9f\x95\xaf" => "",
        "\xf0\x9f\x95\xb0" => "",
        "\xf0\x9f\x95\xb3" => "",
        "\xf0\x9f\x95\xb4" => "",
        "\xf0\x9f\x95\xb5" => "",
        "\xf0\x9f\x95\xb6" => "",
        "\xf0\x9f\x95\xb7" => "",
        "\xf0\x9f\x95\xb8" => "",
        "\xf0\x9f\x95\xb9" => "",
        "\xf0\x9f\x96\x87" => "",
        "\xf0\x9f\x96\x8a" => "",
        "\xf0\x9f\x96\x8b" => "",
        "\xf0\x9f\x96\x8c" => "",
        "\xf0\x9f\x96\x8d" => "",
        "\xf0\x9f\x96\x90" => "",
        "\xf0\x9f\x96\x95" => "",
        "\xf0\x9f\x96\x96" => "",
        "\xf0\x9f\x96\xa5" => "",
        "\xf0\x9f\x96\xa8" => "",
        "\xf0\x9f\x96\xb1" => "",
        "\xf0\x9f\x96\xb2" => "",
        "\xf0\x9f\x96\xbc" => "",
        "\xf0\x9f\x97\x82" => "",
        "\xf0\x9f\x97\x83" => "",
        "\xf0\x9f\x97\x84" => "",
        "\xf0\x9f\x97\x91" => "",
        "\xf0\x9f\x97\x92" => "",
        "\xf0\x9f\x97\x93" => "",
        "\xf0\x9f\x97\x9c" => "",
        "\xf0\x9f\x97\x9d" => "",
        "\xf0\x9f\x97\x9e" => "",
        "\xf0\x9f\x97\xa1" => "",
        "\xf0\x9f\x97\xa3" => "",
        "\xf0\x9f\x97\xa8" => "",
        "\xf0\x9f\x97\xaf" => "",
        "\xf0\x9f\x97\xb3" => "",
        "\xf0\x9f\x97\xba" => "",
        "\xf0\x9f\x97\xbb" => "\xee\x9d\x80",
        "\xf0\x9f\x97\xbc" => "",
        "\xf0\x9f\x97\xbd" => "",
        "\xf0\x9f\x97\xbe" => "",
        "\xf0\x9f\x97\xbf" => "",
        "\xf0\x9f\x98\x80" => "",
        "\xf0\x9f\x98\x81" => "\xee\x9d\x93",
        "\xf0\x9f\x98\x82" => "\xee\x9c\xaa",
        "\xf0\x9f\x98\x83" => "\xee\x9b\xb0",
        "\xf0\x9f\x98\x84" => "\xee\x9b\xb0",
        "\xf0\x9f\x98\x85" => "\xee\x9c\xa2",
        "\xf0\x9f\x98\x86" => "\xee\x9c\xaa",
        "\xf0\x9f\x98\x87" => "",
        "\xf0\x9f\x98\x88" => "",
        "\xf0\x9f\x98\x89" => "\xee\x9c\xa9",
        "\xf0\x9f\x98\x8a" => "\xee\x9b\xb0",
        "\xf0\x9f\x98\x8b" => "\xee\x9d\x92",
        "\xf0\x9f\x98\x8c" => "\xee\x9c\xa1",
        "\xf0\x9f\x98\x8d" => "\xee\x9c\xa6",
        "\xf0\x9f\x98\x8e" => "",
        "\xf0\x9f\x98\x8f" => "\xee\x9c\xac",
        "\xf0\x9f\x98\x90" => "",
        "\xf0\x9f\x98\x91" => "",
        "\xf0\x9f\x98\x92" => "\xee\x9c\xa5",
        "\xf0\x9f\x98\x93" => "\xee\x9c\xa3",
        "\xf0\x9f\x98\x94" => "\xee\x9c\xa0",
        "\xf0\x9f\x98\x95" => "",
        "\xf0\x9f\x98\x96" => "\xee\x9b\xb3",
        "\xf0\x9f\x98\x97" => "",
        "\xf0\x9f\x98\x98" => "\xee\x9c\xa6",
        "\xf0\x9f\x98\x99" => "",
        "\xf0\x9f\x98\x9a" => "\xee\x9c\xa6",
        "\xf0\x9f\x98\x9b" => "",
        "\xf0\x9f\x98\x9c" => "\xee\x9c\xa8",
        "\xf0\x9f\x98\x9d" => "\xee\x9c\xa8",
        "\xf0\x9f\x98\x9e" => "\xee\x9b\xb2",
        "\xf0\x9f\x98\x9f" => "",
        "\xf0\x9f\x98\xa0" => "\xee\x9b\xb1",
        "\xf0\x9f\x98\xa1" => "\xee\x9c\xa4",
        "\xf0\x9f\x98\xa2" => "\xee\x9c\xae",
        "\xf0\x9f\x98\xa3" => "\xee\x9c\xab",
        "\xf0\x9f\x98\xa4" => "\xee\x9d\x93",
        "\xf0\x9f\x98\xa5" => "\xee\x9c\xa3",
        "\xf0\x9f\x98\xa6" => "",
        "\xf0\x9f\x98\xa7" => "",
        "\xf0\x9f\x98\xa8" => "\xee\x9d\x97",
        "\xf0\x9f\x98\xa9" => "\xee\x9b\xb3",
        "\xf0\x9f\x98\xaa" => "\xee\x9c\x81",
        "\xf0\x9f\x98\xab" => "\xee\x9c\xab",
        "\xf0\x9f\x98\xac" => "",
        "\xf0\x9f\x98\xad" => "\xee\x9c\xad",
        "\xf0\x9f\x98\xae" => "",
        "\xf0\x9f\x98\xaf" => "",
        "\xf0\x9f\x98\xb0" => "\xee\x9c\xa3",
        "\xf0\x9f\x98\xb1" => "\xee\x9d\x97",
        "\xf0\x9f\x98\xb2" => "\xee\x9b\xb4",
        "\xf0\x9f\x98\xb3" => "\xee\x9c\xaa",
        "\xf0\x9f\x98\xb4" => "",
        "\xf0\x9f\x98\xb5" => "\xee\x9b\xb4",
        "\xf0\x9f\x98\xb6" => "",
        "\xf0\x9f\x98\xb7" => "",
        "\xf0\x9f\x98\xb8" => "\xee\x9d\x93",
        "\xf0\x9f\x98\xb9" => "\xee\x9c\xaa",
        "\xf0\x9f\x98\xba" => "\xee\x9b\xb0",
        "\xf0\x9f\x98\xbb" => "\xee\x9c\xa6",
        "\xf0\x9f\x98\xbc" => "\xee\x9d\x93",
        "\xf0\x9f\x98\xbd" => "\xee\x9c\xa6",
        "\xf0\x9f\x98\xbe" => "\xee\x9c\xa4",
        "\xf0\x9f\x98\xbf" => "\xee\x9c\xae",
        "\xf0\x9f\x99\x80" => "\xee\x9b\xb3",
        "\xf0\x9f\x99\x81" => "",
        "\xf0\x9f\x99\x82" => "",
        "\xf0\x9f\x99\x83" => "",
        "\xf0\x9f\x99\x84" => "",
        "\xf0\x9f\x99\x85" => "\xee\x9c\xaf",
        "\xf0\x9f\x99\x86" => "\xee\x9c\x8b",
        "\xf0\x9f\x99\x87" => "",
        "\xf0\x9f\x99\x88" => "",
        "\xf0\x9f\x99\x89" => "",
        "\xf0\x9f\x99\x8a" => "",
        "\xf0\x9f\x99\x8b" => "",
        "\xf0\x9f\x99\x8c" => "",
        "\xf0\x9f\x99\x8d" => "\xee\x9b\xb3",
        "\xf0\x9f\x99\x8e" => "\xee\x9b\xb1",
        "\xf0\x9f\x99\x8f" => "",
        "\xf0\x9f\x9a\x80" => "",
        "\xf0\x9f\x9a\x81" => "",
        "\xf0\x9f\x9a\x82" => "",
        "\xf0\x9f\x9a\x83" => "\xee\x99\x9b",
        "\xf0\x9f\x9a\x84" => "\xee\x99\x9d",
        "\xf0\x9f\x9a\x85" => "\xee\x99\x9d",
        "\xf0\x9f\x9a\x86" => "",
        "\xf0\x9f\x9a\x87" => "\xee\x99\x9c",
        "\xf0\x9f\x9a\x88" => "",
        "\xf0\x9f\x9a\x89" => "",
        "\xf0\x9f\x9a\x8a" => "",
        "\xf0\x9f\x9a\x8b" => "",
        "\xf0\x9f\x9a\x8c" => "\xee\x99\xa0",
        "\xf0\x9f\x9a\x8d" => "",
        "\xf0\x9f\x9a\x8e" => "",
        "\xf0\x9f\x9a\x8f" => "",
        "\xf0\x9f\x9a\x90" => "",
        "\xf0\x9f\x9a\x91" => "",
        "\xf0\x9f\x9a\x92" => "",
        "\xf0\x9f\x9a\x93" => "",
        "\xf0\x9f\x9a\x94" => "",
        "\xf0\x9f\x9a\x95" => "\xee\x99\x9e",
        "\xf0\x9f\x9a\x96" => "",
        "\xf0\x9f\x9a\x97" => "\xee\x99\x9e",
        "\xf0\x9f\x9a\x98" => "",
        "\xf0\x9f\x9a\x99" => "\xee\x99\x9f",
        "\xf0\x9f\x9a\x9a" => "",
        "\xf0\x9f\x9a\x9b" => "",
        "\xf0\x9f\x9a\x9c" => "",
        "\xf0\x9f\x9a\x9d" => "",
        "\xf0\x9f\x9a\x9e" => "",
        "\xf0\x9f\x9a\x9f" => "",
        "\xf0\x9f\x9a\xa0" => "",
        "\xf0\x9f\x9a\xa1" => "",
        "\xf0\x9f\x9a\xa2" => "\xee\x99\xa1",
        "\xf0\x9f\x9a\xa3" => "",
        "\xf0\x9f\x9a\xa4" => "\xee\x9a\xa3",
        "\xf0\x9f\x9a\xa5" => "\xee\x99\xad",
        "\xf0\x9f\x9a\xa6" => "",
        "\xf0\x9f\x9a\xa7" => "",
        "\xf0\x9f\x9a\xa8" => "",
        "\xf0\x9f\x9a\xa9" => "\xee\x9b\x9e",
        "\xf0\x9f\x9a\xaa" => "\xee\x9c\x94",
        "\xf0\x9f\x9a\xab" => "\xee\x9c\xb8",
        "\xf0\x9f\x9a\xac" => "\xee\x99\xbf",
        "\xf0\x9f\x9a\xad" => "\xee\x9a\x80",
        "\xf0\x9f\x9a\xae" => "",
        "\xf0\x9f\x9a\xaf" => "",
        "\xf0\x9f\x9a\xb0" => "",
        "\xf0\x9f\x9a\xb1" => "",
        "\xf0\x9f\x9a\xb2" => "\xee\x9c\x9d",
        "\xf0\x9f\x9a\xb3" => "",
        "\xf0\x9f\x9a\xb4" => "",
        "\xf0\x9f\x9a\xb5" => "",
        "\xf0\x9f\x9a\xb6" => "\xee\x9c\xb3",
        "\xf0\x9f\x9a\xb7" => "",
        "\xf0\x9f\x9a\xb8" => "",
        "\xf0\x9f\x9a\xb9" => "",
        "\xf0\x9f\x9a\xba" => "",
        "\xf0\x9f\x9a\xbb" => "\xee\x99\xae",
        "\xf0\x9f\x9a\xbc" => "",
        "\xf0\x9f\x9a\xbd" => "\xee\x99\xae",
        "\xf0\x9f\x9a\xbe" => "\xee\x99\xae",
        "\xf0\x9f\x9a\xbf" => "",
        "\xf0\x9f\x9b\x80" => "\xee\x9b\xb7",
        "\xf0\x9f\x9b\x81" => "",
        "\xf0\x9f\x9b\x82" => "",
        "\xf0\x9f\x9b\x83" => "",
        "\xf0\x9f\x9b\x84" => "",
        "\xf0\x9f\x9b\x85" => "",
        "\xf0\x9f\x9b\x8b" => "",
        "\xf0\x9f\x9b\x8c" => "",
        "\xf0\x9f\x9b\x8d" => "",
        "\xf0\x9f\x9b\x8e" => "",
        "\xf0\x9f\x9b\x8f" => "",
        "\xf0\x9f\x9b\x90" => "",
        "\xf0\x9f\x9b\xa0" => "",
        "\xf0\x9f\x9b\xa1" => "",
        "\xf0\x9f\x9b\xa2" => "",
        "\xf0\x9f\x9b\xa3" => "",
        "\xf0\x9f\x9b\xa4" => "",
        "\xf0\x9f\x9b\xa5" => "",
        "\xf0\x9f\x9b\xa9" => "",
        "\xf0\x9f\x9b\xab" => "",
        "\xf0\x9f\x9b\xac" => "",
        "\xf0\x9f\x9b\xb0" => "",
        "\xf0\x9f\x9b\xb3" => "",
        "\xf0\x9f\xa4\x90" => "",
        "\xf0\x9f\xa4\x91" => "",
        "\xf0\x9f\xa4\x92" => "",
        "\xf0\x9f\xa4\x93" => "",
        "\xf0\x9f\xa4\x94" => "",
        "\xf0\x9f\xa4\x95" => "",
        "\xf0\x9f\xa4\x96" => "",
        "\xf0\x9f\xa4\x97" => "",
        "\xf0\x9f\xa4\x98" => "",
        "\xf0\x9f\xa6\x80" => "",
        "\xf0\x9f\xa6\x81" => "",
        "\xf0\x9f\xa6\x82" => "",
        "\xf0\x9f\xa6\x83" => "",
        "\xf0\x9f\xa6\x84" => "",
        "\xf0\x9f\xa7\x80" => "",
        "#\xe2\x83\xa3" => "\xee\x9b\xa0",
        "*\xe2\x83\xa3" => "",
        "0\xe2\x83\xa3" => "\xee\x9b\xab",
        "1\xe2\x83\xa3" => "\xee\x9b\xa2",
        "2\xe2\x83\xa3" => "\xee\x9b\xa3",
        "3\xe2\x83\xa3" => "\xee\x9b\xa4",
        "4\xe2\x83\xa3" => "\xee\x9b\xa5",
        "5\xe2\x83\xa3" => "\xee\x9b\xa6",
        "6\xe2\x83\xa3" => "\xee\x9b\xa7",
        "7\xe2\x83\xa3" => "\xee\x9b\xa8",
        "8\xe2\x83\xa3" => "\xee\x9b\xa9",
        "9\xe2\x83\xa3" => "\xee\x9b\xaa",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa7" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa7" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa7" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb4\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb6\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa7" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xbc\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xbc\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xbd\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xbe\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xbe\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x91\xa8" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x92\x8b\xe2\x80\x8d\xf0\x9f\x91\xa8" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x91\xa9" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x92\x8b\xe2\x80\x8d\xf0\x9f\x91\xa9" => "",
    ),
    'unified_to_kddi' => array(
        "\xc2\xa9" => "\xee\x95\x98",
        "\xc2\xae" => "\xee\x95\x99",
        "\xe2\x80\xbc" => "\xee\xac\xb0",
        "\xe2\x81\x89" => "\xee\xac\xaf",
        "\xe2\x84\xa2" => "\xee\x95\x8e",
        "\xe2\x84\xb9" => "\xee\x94\xb3",
        "\xe2\x86\x94" => "\xee\xad\xba",
        "\xe2\x86\x95" => "\xee\xad\xbb",
        "\xe2\x86\x96" => "\xee\x95\x8c",
        "\xe2\x86\x97" => "\xee\x95\x95",
        "\xe2\x86\x98" => "\xee\x95\x8d",
        "\xe2\x86\x99" => "\xee\x95\x96",
        "\xe2\x86\xa9" => "\xee\x95\x9d",
        "\xe2\x86\xaa" => "\xee\x95\x9c",
        "\xe2\x8c\x9a" => "\xee\x95\xba",
        "\xe2\x8c\x9b" => "\xee\x95\xbb",
        "\xe2\x8c\xa8" => "",
        "\xe2\x8f\xa9" => "\xee\x94\xb0",
        "\xe2\x8f\xaa" => "\xee\x94\xaf",
        "\xe2\x8f\xab" => "\xee\x95\x85",
        "\xe2\x8f\xac" => "\xee\x95\x84",
        "\xe2\x8f\xad" => "",
        "\xe2\x8f\xae" => "",
        "\xe2\x8f\xaf" => "",
        "\xe2\x8f\xb0" => "\xee\x96\x94",
        "\xe2\x8f\xb1" => "",
        "\xe2\x8f\xb2" => "",
        "\xe2\x8f\xb3" => "\xee\x91\xbc",
        "\xe2\x8f\xb8" => "",
        "\xe2\x8f\xb9" => "",
        "\xe2\x8f\xba" => "",
        "\xe2\x93\x82" => "\xee\x96\xbc",
        "\xe2\x96\xaa" => "\xee\x94\xb2",
        "\xe2\x96\xab" => "\xee\x94\xb1",
        "\xe2\x96\xb6" => "\xee\x94\xae",
        "\xe2\x97\x80" => "\xee\x94\xad",
        "\xe2\x97\xbb" => "\xee\x94\xb8",
        "\xe2\x97\xbc" => "\xee\x94\xb9",
        "\xe2\x97\xbd" => "\xee\x94\xb4",
        "\xe2\x97\xbe" => "\xee\x94\xb5",
        "\xe2\x98\x80" => "\xee\x92\x88",
        "\xe2\x98\x81" => "\xee\x92\x8d",
        "\xe2\x98\x82" => "",
        "\xe2\x98\x83" => "",
        "\xe2\x98\x84" => "",
        "\xe2\x98\x8e" => "\xee\x96\x96",
        "\xe2\x98\x91" => "\xee\xac\x82",
        "\xe2\x98\x94" => "\xee\x92\x8c",
        "\xe2\x98\x95" => "\xee\x96\x97",
        "\xe2\x98\x98" => "",
        "\xe2\x98\x9d" => "\xee\x93\xb6",
        "\xe2\x98\xa0" => "",
        "\xe2\x98\xa2" => "",
        "\xe2\x98\xa3" => "",
        "\xe2\x98\xa6" => "",
        "\xe2\x98\xaa" => "",
        "\xe2\x98\xae" => "",
        "\xe2\x98\xaf" => "",
        "\xe2\x98\xb8" => "",
        "\xe2\x98\xb9" => "",
        "\xe2\x98\xba" => "\xee\x93\xbb",
        "\xe2\x99\x88" => "\xee\x92\x8f",
        "\xe2\x99\x89" => "\xee\x92\x90",
        "\xe2\x99\x8a" => "\xee\x92\x91",
        "\xe2\x99\x8b" => "\xee\x92\x92",
        "\xe2\x99\x8c" => "\xee\x92\x93",
        "\xe2\x99\x8d" => "\xee\x92\x94",
        "\xe2\x99\x8e" => "\xee\x92\x95",
        "\xe2\x99\x8f" => "\xee\x92\x96",
        "\xe2\x99\x90" => "\xee\x92\x97",
        "\xe2\x99\x91" => "\xee\x92\x98",
        "\xe2\x99\x92" => "\xee\x92\x99",
        "\xe2\x99\x93" => "\xee\x92\x9a",
        "\xe2\x99\xa0" => "\xee\x96\xa1",
        "\xe2\x99\xa3" => "\xee\x96\xa3",
        "\xe2\x99\xa5" => "\xee\xaa\xa5",
        "\xe2\x99\xa6" => "\xee\x96\xa2",
        "\xe2\x99\xa8" => "\xee\x92\xbc",
        "\xe2\x99\xbb" => "\xee\xad\xb9",
        "\xe2\x99\xbf" => "\xee\x91\xbf",
        "\xe2\x9a\x92" => "",
        "\xe2\x9a\x93" => "\xee\x92\xa9",
        "\xe2\x9a\x94" => "",
        "\xe2\x9a\x96" => "",
        "\xe2\x9a\x97" => "",
        "\xe2\x9a\x99" => "",
        "\xe2\x9a\x9b" => "",
        "\xe2\x9a\x9c" => "",
        "\xe2\x9a\xa0" => "\xee\x92\x81",
        "\xe2\x9a\xa1" => "\xee\x92\x87",
        "\xe2\x9a\xaa" => "\xee\x94\xba",
        "\xe2\x9a\xab" => "\xee\x94\xbb",
        "\xe2\x9a\xb0" => "",
        "\xe2\x9a\xb1" => "",
        "\xe2\x9a\xbd" => "\xee\x92\xb6",
        "\xe2\x9a\xbe" => "\xee\x92\xba",
        "\xe2\x9b\x84" => "\xee\x92\x85",
        "\xe2\x9b\x85" => "\xee\x92\x8e",
        "\xe2\x9b\x88" => "",
        "\xe2\x9b\x8e" => "\xee\x92\x9b",
        "\xe2\x9b\x8f" => "",
        "\xe2\x9b\x91" => "",
        "\xe2\x9b\x93" => "",
        "\xe2\x9b\x94" => "\xee\x92\x84",
        "\xe2\x9b\xa9" => "",
        "\xe2\x9b\xaa" => "\xee\x96\xbb",
        "\xe2\x9b\xb0" => "",
        "\xe2\x9b\xb1" => "",
        "\xe2\x9b\xb2" => "\xee\x97\x8f",
        "\xe2\x9b\xb3" => "\xee\x96\x99",
        "\xe2\x9b\xb4" => "",
        "\xe2\x9b\xb5" => "\xee\x92\xb4",
        "\xe2\x9b\xb7" => "",
        "\xe2\x9b\xb8" => "",
        "\xe2\x9b\xb9" => "",
        "\xe2\x9b\xba" => "\xee\x97\x90",
        "\xe2\x9b\xbd" => "\xee\x95\xb1",
        "\xe2\x9c\x82" => "\xee\x94\x96",
        "\xe2\x9c\x85" => "\xee\x95\x9e",
        "\xe2\x9c\x88" => "\xee\x92\xb3",
        "\xe2\x9c\x89" => "\xee\x94\xa1",
        "\xe2\x9c\x8a" => "\xee\xae\x83",
        "\xe2\x9c\x8b" => "\xee\x96\xa7",
        "\xe2\x9c\x8c" => "\xee\x96\xa6",
        "\xe2\x9c\x8d" => "",
        "\xe2\x9c\x8f" => "\xee\x92\xa1",
        "\xe2\x9c\x92" => "\xee\xac\x83",
        "\xe2\x9c\x94" => "\xee\x95\x97",
        "\xe2\x9c\x96" => "\xee\x95\x8f",
        "\xe2\x9c\x9d" => "",
        "\xe2\x9c\xa1" => "",
        "\xe2\x9c\xa8" => "\xee\xaa\xab",
        "\xe2\x9c\xb3" => "\xee\x94\xbe",
        "\xe2\x9c\xb4" => "\xee\x91\xb9",
        "\xe2\x9d\x84" => "\xee\x92\x8a",
        "\xe2\x9d\x87" => "\xee\x91\xac",
        "\xe2\x9d\x8c" => "\xee\x95\x90",
        "\xe2\x9d\x8e" => "\xee\x95\x91",
        "\xe2\x9d\x93" => "\xee\x92\x83",
        "\xe2\x9d\x94" => "\xee\x92\x83",
        "\xe2\x9d\x95" => "\xee\x92\x82",
        "\xe2\x9d\x97" => "\xee\x92\x82",
        "\xe2\x9d\xa3" => "",
        "\xe2\x9d\xa4" => "\xee\x96\x95",
        "\xe2\x9e\x95" => "\xee\x94\xbc",
        "\xe2\x9e\x96" => "\xee\x94\xbd",
        "\xe2\x9e\x97" => "\xee\x95\x94",
        "\xe2\x9e\xa1" => "\xee\x95\x92",
        "\xe2\x9e\xb0" => "\xee\xac\xb1",
        "\xe2\x9e\xbf" => "",
        "\xe2\xa4\xb4" => "\xee\xac\xad",
        "\xe2\xa4\xb5" => "\xee\xac\xae",
        "\xe2\xac\x85" => "\xee\x95\x93",
        "\xe2\xac\x86" => "\xee\x94\xbf",
        "\xe2\xac\x87" => "\xee\x95\x80",
        "\xe2\xac\x9b" => "\xee\x95\x89",
        "\xe2\xac\x9c" => "\xee\x95\x88",
        "\xe2\xad\x90" => "\xee\x92\x8b",
        "\xe2\xad\x95" => "\xee\xaa\xad",
        "\xe3\x80\xb0" => "",
        "\xe3\x80\xbd" => "",
        "\xe3\x8a\x97" => "\xee\xaa\x99",
        "\xe3\x8a\x99" => "\xee\x93\xb1",
        "\xf0\x9f\x80\x84" => "\xee\x97\x91",
        "\xf0\x9f\x83\x8f" => "\xee\xad\xaf",
        "\xf0\x9f\x85\xb0" => "\xee\xac\xa6",
        "\xf0\x9f\x85\xb1" => "\xee\xac\xa7",
        "\xf0\x9f\x85\xbe" => "\xee\xac\xa8",
        "\xf0\x9f\x85\xbf" => "\xee\x92\xa6",
        "\xf0\x9f\x86\x8e" => "\xee\xac\xa9",
        "\xf0\x9f\x86\x91" => "\xee\x96\xab",
        "\xf0\x9f\x86\x92" => "\xee\xaa\x85",
        "\xf0\x9f\x86\x93" => "\xee\x95\xb8",
        "\xf0\x9f\x86\x94" => "\xee\xaa\x88",
        "\xf0\x9f\x86\x95" => "\xee\x96\xb5",
        "\xf0\x9f\x86\x96" => "",
        "\xf0\x9f\x86\x97" => "\xee\x96\xad",
        "\xf0\x9f\x86\x98" => "\xee\x93\xa8",
        "\xf0\x9f\x86\x99" => "\xee\x94\x8f",
        "\xf0\x9f\x86\x9a" => "\xee\x97\x92",
        "\xf0\x9f\x88\x81" => "",
        "\xf0\x9f\x88\x82" => "\xee\xaa\x87",
        "\xf0\x9f\x88\x9a" => "",
        "\xf0\x9f\x88\xaf" => "\xee\xaa\x8b",
        "\xf0\x9f\x88\xb2" => "",
        "\xf0\x9f\x88\xb3" => "\xee\xaa\x8a",
        "\xf0\x9f\x88\xb4" => "",
        "\xf0\x9f\x88\xb5" => "\xee\xaa\x89",
        "\xf0\x9f\x88\xb6" => "",
        "\xf0\x9f\x88\xb7" => "",
        "\xf0\x9f\x88\xb8" => "",
        "\xf0\x9f\x88\xb9" => "\xee\xaa\x86",
        "\xf0\x9f\x88\xba" => "\xee\xaa\x8c",
        "\xf0\x9f\x89\x90" => "\xee\x93\xb7",
        "\xf0\x9f\x89\x91" => "\xee\xac\x81",
        "\xf0\x9f\x8c\x80" => "\xee\x91\xa9",
        "\xf0\x9f\x8c\x81" => "\xee\x96\x98",
        "\xf0\x9f\x8c\x82" => "\xee\xab\xa8",
        "\xf0\x9f\x8c\x83" => "\xee\xab\xb1",
        "\xf0\x9f\x8c\x84" => "\xee\xab\xb4",
        "\xf0\x9f\x8c\x85" => "\xee\xab\xb4",
        "\xf0\x9f\x8c\x86" => "\xee\x97\x9a",
        "\xf0\x9f\x8c\x87" => "\xee\x97\x9a",
        "\xf0\x9f\x8c\x88" => "\xee\xab\xb2",
        "\xf0\x9f\x8c\x89" => "\xee\x92\xbf",
        "\xf0\x9f\x8c\x8a" => "\xee\xad\xbc",
        "\xf0\x9f\x8c\x8b" => "\xee\xad\x93",
        "\xf0\x9f\x8c\x8c" => "\xee\xad\x9f",
        "\xf0\x9f\x8c\x8d" => "",
        "\xf0\x9f\x8c\x8e" => "",
        "\xf0\x9f\x8c\x8f" => "\xee\x96\xb3",
        "\xf0\x9f\x8c\x90" => "",
        "\xf0\x9f\x8c\x91" => "\xee\x96\xa8",
        "\xf0\x9f\x8c\x92" => "",
        "\xf0\x9f\x8c\x93" => "\xee\x96\xaa",
        "\xf0\x9f\x8c\x94" => "\xee\x96\xa9",
        "\xf0\x9f\x8c\x95" => "",
        "\xf0\x9f\x8c\x96" => "",
        "\xf0\x9f\x8c\x97" => "",
        "\xf0\x9f\x8c\x98" => "",
        "\xf0\x9f\x8c\x99" => "\xee\x92\x86",
        "\xf0\x9f\x8c\x9a" => "",
        "\xf0\x9f\x8c\x9b" => "\xee\x92\x89",
        "\xf0\x9f\x8c\x9c" => "",
        "\xf0\x9f\x8c\x9d" => "",
        "\xf0\x9f\x8c\x9e" => "",
        "\xf0\x9f\x8c\x9f" => "\xee\x92\x8b",
        "\xf0\x9f\x8c\xa0" => "\xee\x91\xa8",
        "\xf0\x9f\x8c\xa1" => "",
        "\xf0\x9f\x8c\xa4" => "",
        "\xf0\x9f\x8c\xa5" => "",
        "\xf0\x9f\x8c\xa6" => "",
        "\xf0\x9f\x8c\xa7" => "",
        "\xf0\x9f\x8c\xa8" => "",
        "\xf0\x9f\x8c\xa9" => "",
        "\xf0\x9f\x8c\xaa" => "",
        "\xf0\x9f\x8c\xab" => "",
        "\xf0\x9f\x8c\xac" => "",
        "\xf0\x9f\x8c\xad" => "",
        "\xf0\x9f\x8c\xae" => "",
        "\xf0\x9f\x8c\xaf" => "",
        "\xf0\x9f\x8c\xb0" => "\xee\xac\xb8",
        "\xf0\x9f\x8c\xb1" => "\xee\xad\xbd",
        "\xf0\x9f\x8c\xb2" => "",
        "\xf0\x9f\x8c\xb3" => "",
        "\xf0\x9f\x8c\xb4" => "\xee\x93\xa2",
        "\xf0\x9f\x8c\xb5" => "\xee\xaa\x96",
        "\xf0\x9f\x8c\xb6" => "",
        "\xf0\x9f\x8c\xb7" => "\xee\x93\xa4",
        "\xf0\x9f\x8c\xb8" => "\xee\x93\x8a",
        "\xf0\x9f\x8c\xb9" => "\xee\x96\xba",
        "\xf0\x9f\x8c\xba" => "\xee\xaa\x94",
        "\xf0\x9f\x8c\xbb" => "\xee\x93\xa3",
        "\xf0\x9f\x8c\xbc" => "\xee\xad\x89",
        "\xf0\x9f\x8c\xbd" => "\xee\xac\xb6",
        "\xf0\x9f\x8c\xbe" => "",
        "\xf0\x9f\x8c\xbf" => "\xee\xae\x82",
        "\xf0\x9f\x8d\x80" => "\xee\x94\x93",
        "\xf0\x9f\x8d\x81" => "\xee\x93\x8e",
        "\xf0\x9f\x8d\x82" => "\xee\x97\x8d",
        "\xf0\x9f\x8d\x83" => "\xee\x97\x8d",
        "\xf0\x9f\x8d\x84" => "\xee\xac\xb7",
        "\xf0\x9f\x8d\x85" => "\xee\xaa\xbb",
        "\xf0\x9f\x8d\x86" => "\xee\xaa\xbc",
        "\xf0\x9f\x8d\x87" => "\xee\xac\xb4",
        "\xf0\x9f\x8d\x88" => "\xee\xac\xb2",
        "\xf0\x9f\x8d\x89" => "\xee\x93\x8d",
        "\xf0\x9f\x8d\x8a" => "\xee\xaa\xba",
        "\xf0\x9f\x8d\x8b" => "",
        "\xf0\x9f\x8d\x8c" => "\xee\xac\xb5",
        "\xf0\x9f\x8d\x8d" => "\xee\xac\xb3",
        "\xf0\x9f\x8d\x8e" => "\xee\xaa\xb9",
        "\xf0\x9f\x8d\x8f" => "\xee\xad\x9a",
        "\xf0\x9f\x8d\x90" => "",
        "\xf0\x9f\x8d\x91" => "\xee\xac\xb9",
        "\xf0\x9f\x8d\x92" => "\xee\x93\x92",
        "\xf0\x9f\x8d\x93" => "\xee\x93\x94",
        "\xf0\x9f\x8d\x94" => "\xee\x93\x96",
        "\xf0\x9f\x8d\x95" => "\xee\xac\xbb",
        "\xf0\x9f\x8d\x96" => "\xee\x93\x84",
        "\xf0\x9f\x8d\x97" => "\xee\xac\xbc",
        "\xf0\x9f\x8d\x98" => "\xee\xaa\xb3",
        "\xf0\x9f\x8d\x99" => "\xee\x93\x95",
        "\xf0\x9f\x8d\x9a" => "\xee\xaa\xb4",
        "\xf0\x9f\x8d\x9b" => "\xee\xaa\xb6",
        "\xf0\x9f\x8d\x9c" => "\xee\x96\xb4",
        "\xf0\x9f\x8d\x9d" => "\xee\xaa\xb5",
        "\xf0\x9f\x8d\x9e" => "\xee\xaa\xaf",
        "\xf0\x9f\x8d\x9f" => "\xee\xaa\xb1",
        "\xf0\x9f\x8d\xa0" => "\xee\xac\xba",
        "\xf0\x9f\x8d\xa1" => "\xee\xaa\xb2",
        "\xf0\x9f\x8d\xa2" => "\xee\xaa\xb7",
        "\xf0\x9f\x8d\xa3" => "\xee\xaa\xb8",
        "\xf0\x9f\x8d\xa4" => "\xee\xad\xb0",
        "\xf0\x9f\x8d\xa5" => "\xee\x93\xad",
        "\xf0\x9f\x8d\xa6" => "\xee\xaa\xb0",
        "\xf0\x9f\x8d\xa7" => "\xee\xab\xaa",
        "\xf0\x9f\x8d\xa8" => "\xee\xad\x8a",
        "\xf0\x9f\x8d\xa9" => "\xee\xad\x8b",
        "\xf0\x9f\x8d\xaa" => "\xee\xad\x8c",
        "\xf0\x9f\x8d\xab" => "\xee\xad\x8d",
        "\xf0\x9f\x8d\xac" => "\xee\xad\x8e",
        "\xf0\x9f\x8d\xad" => "\xee\xad\x8f",
        "\xf0\x9f\x8d\xae" => "\xee\xad\x96",
        "\xf0\x9f\x8d\xaf" => "\xee\xad\x99",
        "\xf0\x9f\x8d\xb0" => "\xee\x93\x90",
        "\xf0\x9f\x8d\xb1" => "\xee\xaa\xbd",
        "\xf0\x9f\x8d\xb2" => "\xee\xaa\xbe",
        "\xf0\x9f\x8d\xb3" => "\xee\x93\x91",
        "\xf0\x9f\x8d\xb4" => "\xee\x92\xac",
        "\xf0\x9f\x8d\xb5" => "\xee\xaa\xae",
        "\xf0\x9f\x8d\xb6" => "\xee\xaa\x97",
        "\xf0\x9f\x8d\xb7" => "\xee\x93\x81",
        "\xf0\x9f\x8d\xb8" => "\xee\x93\x82",
        "\xf0\x9f\x8d\xb9" => "\xee\xac\xbe",
        "\xf0\x9f\x8d\xba" => "\xee\x93\x83",
        "\xf0\x9f\x8d\xbb" => "\xee\xaa\x98",
        "\xf0\x9f\x8d\xbc" => "",
        "\xf0\x9f\x8d\xbd" => "",
        "\xf0\x9f\x8d\xbe" => "",
        "\xf0\x9f\x8d\xbf" => "",
        "\xf0\x9f\x8e\x80" => "\xee\x96\x9f",
        "\xf0\x9f\x8e\x81" => "\xee\x93\x8f",
        "\xf0\x9f\x8e\x82" => "\xee\x96\xa0",
        "\xf0\x9f\x8e\x83" => "\xee\xab\xae",
        "\xf0\x9f\x8e\x84" => "\xee\x93\x89",
        "\xf0\x9f\x8e\x85" => "\xee\xab\xb0",
        "\xf0\x9f\x8e\x86" => "\xee\x97\x8c",
        "\xf0\x9f\x8e\x87" => "\xee\xab\xab",
        "\xf0\x9f\x8e\x88" => "\xee\xaa\x9b",
        "\xf0\x9f\x8e\x89" => "\xee\xaa\x9c",
        "\xf0\x9f\x8e\x8a" => "\xee\x91\xaf",
        "\xf0\x9f\x8e\x8b" => "\xee\xac\xbd",
        "\xf0\x9f\x8e\x8c" => "\xee\x97\x99",
        "\xf0\x9f\x8e\x8d" => "\xee\xab\xa3",
        "\xf0\x9f\x8e\x8e" => "\xee\xab\xa4",
        "\xf0\x9f\x8e\x8f" => "\xee\xab\xa7",
        "\xf0\x9f\x8e\x90" => "\xee\xab\xad",
        "\xf0\x9f\x8e\x91" => "\xee\xab\xaf",
        "\xf0\x9f\x8e\x92" => "\xee\xab\xa6",
        "\xf0\x9f\x8e\x93" => "\xee\xab\xa5",
        "\xf0\x9f\x8e\x96" => "",
        "\xf0\x9f\x8e\x97" => "",
        "\xf0\x9f\x8e\x99" => "",
        "\xf0\x9f\x8e\x9a" => "",
        "\xf0\x9f\x8e\x9b" => "",
        "\xf0\x9f\x8e\x9e" => "",
        "\xf0\x9f\x8e\x9f" => "",
        "\xf0\x9f\x8e\xa0" => "",
        "\xf0\x9f\x8e\xa1" => "\xee\x91\xad",
        "\xf0\x9f\x8e\xa2" => "\xee\xab\xa2",
        "\xf0\x9f\x8e\xa3" => "\xee\xad\x82",
        "\xf0\x9f\x8e\xa4" => "\xee\x94\x83",
        "\xf0\x9f\x8e\xa5" => "\xee\x94\x97",
        "\xf0\x9f\x8e\xa6" => "\xee\x94\x97",
        "\xf0\x9f\x8e\xa7" => "\xee\x94\x88",
        "\xf0\x9f\x8e\xa8" => "\xee\x96\x9c",
        "\xf0\x9f\x8e\xa9" => "\xee\xab\xb5",
        "\xf0\x9f\x8e\xaa" => "\xee\x96\x9e",
        "\xf0\x9f\x8e\xab" => "\xee\x92\x9e",
        "\xf0\x9f\x8e\xac" => "\xee\x92\xbe",
        "\xf0\x9f\x8e\xad" => "\xee\x96\x9d",
        "\xf0\x9f\x8e\xae" => "\xee\x93\x86",
        "\xf0\x9f\x8e\xaf" => "\xee\x93\x85",
        "\xf0\x9f\x8e\xb0" => "\xee\x91\xae",
        "\xf0\x9f\x8e\xb1" => "\xee\xab\x9d",
        "\xf0\x9f\x8e\xb2" => "\xee\x93\x88",
        "\xf0\x9f\x8e\xb3" => "\xee\xad\x83",
        "\xf0\x9f\x8e\xb4" => "\xee\xad\xae",
        "\xf0\x9f\x8e\xb5" => "\xee\x96\xbe",
        "\xf0\x9f\x8e\xb6" => "\xee\x94\x85",
        "\xf0\x9f\x8e\xb7" => "",
        "\xf0\x9f\x8e\xb8" => "\xee\x94\x86",
        "\xf0\x9f\x8e\xb9" => "\xee\xad\x80",
        "\xf0\x9f\x8e\xba" => "\xee\xab\x9c",
        "\xf0\x9f\x8e\xbb" => "\xee\x94\x87",
        "\xf0\x9f\x8e\xbc" => "\xee\xab\x8c",
        "\xf0\x9f\x8e\xbd" => "",
        "\xf0\x9f\x8e\xbe" => "\xee\x92\xb7",
        "\xf0\x9f\x8e\xbf" => "\xee\xaa\xac",
        "\xf0\x9f\x8f\x80" => "\xee\x96\x9a",
        "\xf0\x9f\x8f\x81" => "\xee\x92\xb9",
        "\xf0\x9f\x8f\x82" => "\xee\x92\xb8",
        "\xf0\x9f\x8f\x83" => "\xee\x91\xab",
        "\xf0\x9f\x8f\x84" => "\xee\xad\x81",
        "\xf0\x9f\x8f\x85" => "",
        "\xf0\x9f\x8f\x86" => "\xee\x97\x93",
        "\xf0\x9f\x8f\x87" => "",
        "\xf0\x9f\x8f\x88" => "\xee\x92\xbb",
        "\xf0\x9f\x8f\x89" => "",
        "\xf0\x9f\x8f\x8a" => "\xee\xab\x9e",
        "\xf0\x9f\x8f\x8b" => "",
        "\xf0\x9f\x8f\x8c" => "",
        "\xf0\x9f\x8f\x8d" => "",
        "\xf0\x9f\x8f\x8e" => "",
        "\xf0\x9f\x8f\x8f" => "",
        "\xf0\x9f\x8f\x90" => "",
        "\xf0\x9f\x8f\x91" => "",
        "\xf0\x9f\x8f\x92" => "",
        "\xf0\x9f\x8f\x93" => "",
        "\xf0\x9f\x8f\x94" => "",
        "\xf0\x9f\x8f\x95" => "",
        "\xf0\x9f\x8f\x96" => "",
        "\xf0\x9f\x8f\x97" => "",
        "\xf0\x9f\x8f\x98" => "",
        "\xf0\x9f\x8f\x99" => "",
        "\xf0\x9f\x8f\x9a" => "",
        "\xf0\x9f\x8f\x9b" => "",
        "\xf0\x9f\x8f\x9c" => "",
        "\xf0\x9f\x8f\x9d" => "",
        "\xf0\x9f\x8f\x9e" => "",
        "\xf0\x9f\x8f\x9f" => "",
        "\xf0\x9f\x8f\xa0" => "\xee\x92\xab",
        "\xf0\x9f\x8f\xa1" => "\xee\xac\x89",
        "\xf0\x9f\x8f\xa2" => "\xee\x92\xad",
        "\xf0\x9f\x8f\xa3" => "\xee\x97\x9e",
        "\xf0\x9f\x8f\xa4" => "",
        "\xf0\x9f\x8f\xa5" => "\xee\x97\x9f",
        "\xf0\x9f\x8f\xa6" => "\xee\x92\xaa",
        "\xf0\x9f\x8f\xa7" => "\xee\x92\xa3",
        "\xf0\x9f\x8f\xa8" => "\xee\xaa\x81",
        "\xf0\x9f\x8f\xa9" => "\xee\xab\xb3",
        "\xf0\x9f\x8f\xaa" => "\xee\x92\xa4",
        "\xf0\x9f\x8f\xab" => "\xee\xaa\x80",
        "\xf0\x9f\x8f\xac" => "\xee\xab\xb6",
        "\xf0\x9f\x8f\xad" => "\xee\xab\xb9",
        "\xf0\x9f\x8f\xae" => "\xee\x92\xbd",
        "\xf0\x9f\x8f\xaf" => "\xee\xab\xb7",
        "\xf0\x9f\x8f\xb0" => "\xee\xab\xb8",
        "\xf0\x9f\x8f\xb3" => "",
        "\xf0\x9f\x8f\xb4" => "",
        "\xf0\x9f\x8f\xb5" => "",
        "\xf0\x9f\x8f\xb7" => "",
        "\xf0\x9f\x8f\xb8" => "",
        "\xf0\x9f\x8f\xb9" => "",
        "\xf0\x9f\x8f\xba" => "",
        "\xf0\x9f\x8f\xbb" => "",
        "\xf0\x9f\x8f\xbc" => "",
        "\xf0\x9f\x8f\xbd" => "",
        "\xf0\x9f\x8f\xbe" => "",
        "\xf0\x9f\x8f\xbf" => "",
        "\xf0\x9f\x90\x80" => "",
        "\xf0\x9f\x90\x81" => "",
        "\xf0\x9f\x90\x82" => "",
        "\xf0\x9f\x90\x83" => "",
        "\xf0\x9f\x90\x84" => "",
        "\xf0\x9f\x90\x85" => "",
        "\xf0\x9f\x90\x86" => "",
        "\xf0\x9f\x90\x87" => "",
        "\xf0\x9f\x90\x88" => "",
        "\xf0\x9f\x90\x89" => "",
        "\xf0\x9f\x90\x8a" => "",
        "\xf0\x9f\x90\x8b" => "",
        "\xf0\x9f\x90\x8c" => "\xee\xad\xbe",
        "\xf0\x9f\x90\x8d" => "\xee\xac\xa2",
        "\xf0\x9f\x90\x8e" => "\xee\x93\x98",
        "\xf0\x9f\x90\x8f" => "",
        "\xf0\x9f\x90\x90" => "",
        "\xf0\x9f\x90\x91" => "\xee\x92\x8f",
        "\xf0\x9f\x90\x92" => "\xee\x93\x99",
        "\xf0\x9f\x90\x93" => "",
        "\xf0\x9f\x90\x94" => "\xee\xac\xa3",
        "\xf0\x9f\x90\x95" => "",
        "\xf0\x9f\x90\x96" => "",
        "\xf0\x9f\x90\x97" => "\xee\xac\xa4",
        "\xf0\x9f\x90\x98" => "\xee\xac\x9f",
        "\xf0\x9f\x90\x99" => "\xee\x97\x87",
        "\xf0\x9f\x90\x9a" => "\xee\xab\xac",
        "\xf0\x9f\x90\x9b" => "\xee\xac\x9e",
        "\xf0\x9f\x90\x9c" => "\xee\x93\x9d",
        "\xf0\x9f\x90\x9d" => "\xee\xad\x97",
        "\xf0\x9f\x90\x9e" => "\xee\xad\x98",
        "\xf0\x9f\x90\x9f" => "\xee\x92\x9a",
        "\xf0\x9f\x90\xa0" => "\xee\xac\x9d",
        "\xf0\x9f\x90\xa1" => "\xee\x93\x93",
        "\xf0\x9f\x90\xa2" => "\xee\x97\x94",
        "\xf0\x9f\x90\xa3" => "\xee\x97\x9b",
        "\xf0\x9f\x90\xa4" => "\xee\x93\xa0",
        "\xf0\x9f\x90\xa5" => "\xee\xad\xb6",
        "\xf0\x9f\x90\xa6" => "\xee\x93\xa0",
        "\xf0\x9f\x90\xa7" => "\xee\x93\x9c",
        "\xf0\x9f\x90\xa8" => "\xee\xac\xa0",
        "\xf0\x9f\x90\xa9" => "\xee\x93\x9f",
        "\xf0\x9f\x90\xaa" => "",
        "\xf0\x9f\x90\xab" => "\xee\xac\xa5",
        "\xf0\x9f\x90\xac" => "\xee\xac\x9b",
        "\xf0\x9f\x90\xad" => "\xee\x97\x82",
        "\xf0\x9f\x90\xae" => "\xee\xac\xa1",
        "\xf0\x9f\x90\xaf" => "\xee\x97\x80",
        "\xf0\x9f\x90\xb0" => "\xee\x93\x97",
        "\xf0\x9f\x90\xb1" => "\xee\x93\x9b",
        "\xf0\x9f\x90\xb2" => "\xee\xac\xbf",
        "\xf0\x9f\x90\xb3" => "\xee\x91\xb0",
        "\xf0\x9f\x90\xb4" => "\xee\x93\x98",
        "\xf0\x9f\x90\xb5" => "\xee\x93\x99",
        "\xf0\x9f\x90\xb6" => "\xee\x93\xa1",
        "\xf0\x9f\x90\xb7" => "\xee\x93\x9e",
        "\xf0\x9f\x90\xb8" => "\xee\x93\x9a",
        "\xf0\x9f\x90\xb9" => "",
        "\xf0\x9f\x90\xba" => "\xee\x93\xa1",
        "\xf0\x9f\x90\xbb" => "\xee\x97\x81",
        "\xf0\x9f\x90\xbc" => "\xee\xad\x86",
        "\xf0\x9f\x90\xbd" => "\xee\xad\x88",
        "\xf0\x9f\x90\xbe" => "\xee\x93\xae",
        "\xf0\x9f\x90\xbf" => "",
        "\xf0\x9f\x91\x80" => "\xee\x96\xa4",
        "\xf0\x9f\x91\x81" => "",
        "\xf0\x9f\x91\x82" => "\xee\x96\xa5",
        "\xf0\x9f\x91\x83" => "\xee\xab\x90",
        "\xf0\x9f\x91\x84" => "\xee\xab\x91",
        "\xf0\x9f\x91\x85" => "\xee\xad\x87",
        "\xf0\x9f\x91\x86" => "\xee\xaa\x8d",
        "\xf0\x9f\x91\x87" => "\xee\xaa\x8e",
        "\xf0\x9f\x91\x88" => "\xee\x93\xbf",
        "\xf0\x9f\x91\x89" => "\xee\x94\x80",
        "\xf0\x9f\x91\x8a" => "\xee\x93\xb3",
        "\xf0\x9f\x91\x8b" => "\xee\xab\x96",
        "\xf0\x9f\x91\x8c" => "\xee\xab\x94",
        "\xf0\x9f\x91\x8d" => "\xee\x93\xb9",
        "\xf0\x9f\x91\x8e" => "\xee\xab\x95",
        "\xf0\x9f\x91\x8f" => "\xee\xab\x93",
        "\xf0\x9f\x91\x90" => "\xee\xab\x96",
        "\xf0\x9f\x91\x91" => "\xee\x97\x89",
        "\xf0\x9f\x91\x92" => "\xee\xaa\x9e",
        "\xf0\x9f\x91\x93" => "\xee\x93\xbe",
        "\xf0\x9f\x91\x94" => "\xee\xaa\x93",
        "\xf0\x9f\x91\x95" => "\xee\x96\xb6",
        "\xf0\x9f\x91\x96" => "\xee\xad\xb7",
        "\xf0\x9f\x91\x97" => "\xee\xad\xab",
        "\xf0\x9f\x91\x98" => "\xee\xaa\xa3",
        "\xf0\x9f\x91\x99" => "\xee\xaa\xa4",
        "\xf0\x9f\x91\x9a" => "\xee\x94\x8d",
        "\xf0\x9f\x91\x9b" => "\xee\x94\x84",
        "\xf0\x9f\x91\x9c" => "\xee\x92\x9c",
        "\xf0\x9f\x91\x9d" => "",
        "\xf0\x9f\x91\x9e" => "\xee\x96\xb7",
        "\xf0\x9f\x91\x9f" => "\xee\xac\xab",
        "\xf0\x9f\x91\xa0" => "\xee\x94\x9a",
        "\xf0\x9f\x91\xa1" => "\xee\x94\x9a",
        "\xf0\x9f\x91\xa2" => "\xee\xaa\x9f",
        "\xf0\x9f\x91\xa3" => "\xee\xac\xaa",
        "\xf0\x9f\x91\xa4" => "",
        "\xf0\x9f\x91\xa5" => "",
        "\xf0\x9f\x91\xa6" => "\xee\x93\xbc",
        "\xf0\x9f\x91\xa7" => "\xee\x93\xba",
        "\xf0\x9f\x91\xa8" => "\xee\x93\xbc",
        "\xf0\x9f\x91\xa9" => "\xee\x93\xba",
        "\xf0\x9f\x91\xaa" => "\xee\x94\x81",
        "\xf0\x9f\x91\xab" => "",
        "\xf0\x9f\x91\xac" => "",
        "\xf0\x9f\x91\xad" => "",
        "\xf0\x9f\x91\xae" => "\xee\x97\x9d",
        "\xf0\x9f\x91\xaf" => "\xee\xab\x9b",
        "\xf0\x9f\x91\xb0" => "\xee\xab\xa9",
        "\xf0\x9f\x91\xb1" => "\xee\xac\x93",
        "\xf0\x9f\x91\xb2" => "\xee\xac\x94",
        "\xf0\x9f\x91\xb3" => "\xee\xac\x95",
        "\xf0\x9f\x91\xb4" => "\xee\xac\x96",
        "\xf0\x9f\x91\xb5" => "\xee\xac\x97",
        "\xf0\x9f\x91\xb6" => "\xee\xac\x98",
        "\xf0\x9f\x91\xb7" => "\xee\xac\x99",
        "\xf0\x9f\x91\xb8" => "\xee\xac\x9a",
        "\xf0\x9f\x91\xb9" => "\xee\xad\x84",
        "\xf0\x9f\x91\xba" => "\xee\xad\x85",
        "\xf0\x9f\x91\xbb" => "\xee\x93\x8b",
        "\xf0\x9f\x91\xbc" => "\xee\x96\xbf",
        "\xf0\x9f\x91\xbd" => "\xee\x94\x8e",
        "\xf0\x9f\x91\xbe" => "\xee\x93\xac",
        "\xf0\x9f\x91\xbf" => "\xee\x93\xaf",
        "\xf0\x9f\x92\x80" => "\xee\x93\xb8",
        "\xf0\x9f\x92\x81" => "",
        "\xf0\x9f\x92\x82" => "",
        "\xf0\x9f\x92\x83" => "\xee\xac\x9c",
        "\xf0\x9f\x92\x84" => "\xee\x94\x89",
        "\xf0\x9f\x92\x85" => "\xee\xaa\xa0",
        "\xf0\x9f\x92\x86" => "\xee\x94\x8b",
        "\xf0\x9f\x92\x87" => "\xee\xaa\xa1",
        "\xf0\x9f\x92\x88" => "\xee\xaa\xa2",
        "\xf0\x9f\x92\x89" => "\xee\x94\x90",
        "\xf0\x9f\x92\x8a" => "\xee\xaa\x9a",
        "\xf0\x9f\x92\x8b" => "\xee\x93\xab",
        "\xf0\x9f\x92\x8c" => "\xee\xad\xb8",
        "\xf0\x9f\x92\x8d" => "\xee\x94\x94",
        "\xf0\x9f\x92\x8e" => "\xee\x94\x94",
        "\xf0\x9f\x92\x8f" => "\xee\x97\x8a",
        "\xf0\x9f\x92\x90" => "\xee\xaa\x95",
        "\xf0\x9f\x92\x91" => "\xee\xab\x9a",
        "\xf0\x9f\x92\x92" => "\xee\x96\xbb",
        "\xf0\x9f\x92\x93" => "\xee\xad\xb5",
        "\xf0\x9f\x92\x94" => "\xee\x91\xb7",
        "\xf0\x9f\x92\x95" => "\xee\x91\xb8",
        "\xf0\x9f\x92\x96" => "\xee\xaa\xa6",
        "\xf0\x9f\x92\x97" => "\xee\xad\xb5",
        "\xf0\x9f\x92\x98" => "\xee\x93\xaa",
        "\xf0\x9f\x92\x99" => "\xee\xaa\xa7",
        "\xf0\x9f\x92\x9a" => "\xee\xaa\xa8",
        "\xf0\x9f\x92\x9b" => "\xee\xaa\xa9",
        "\xf0\x9f\x92\x9c" => "\xee\xaa\xaa",
        "\xf0\x9f\x92\x9d" => "\xee\xad\x94",
        "\xf0\x9f\x92\x9e" => "\xee\x96\xaf",
        "\xf0\x9f\x92\x9f" => "\xee\x96\x95",
        "\xf0\x9f\x92\xa0" => "",
        "\xf0\x9f\x92\xa1" => "\xee\x91\xb6",
        "\xf0\x9f\x92\xa2" => "\xee\x93\xa5",
        "\xf0\x9f\x92\xa3" => "\xee\x91\xba",
        "\xf0\x9f\x92\xa4" => "\xee\x91\xb5",
        "\xf0\x9f\x92\xa5" => "\xee\x96\xb0",
        "\xf0\x9f\x92\xa6" => "\xee\x96\xb1",
        "\xf0\x9f\x92\xa7" => "\xee\x93\xa6",
        "\xf0\x9f\x92\xa8" => "\xee\x93\xb4",
        "\xf0\x9f\x92\xa9" => "\xee\x93\xb5",
        "\xf0\x9f\x92\xaa" => "\xee\x93\xa9",
        "\xf0\x9f\x92\xab" => "\xee\xad\x9c",
        "\xf0\x9f\x92\xac" => "\xee\x93\xbd",
        "\xf0\x9f\x92\xad" => "",
        "\xf0\x9f\x92\xae" => "\xee\x93\xb0",
        "\xf0\x9f\x92\xaf" => "\xee\x93\xb2",
        "\xf0\x9f\x92\xb0" => "\xee\x93\x87",
        "\xf0\x9f\x92\xb1" => "",
        "\xf0\x9f\x92\xb2" => "\xee\x95\xb9",
        "\xf0\x9f\x92\xb3" => "\xee\x95\xbc",
        "\xf0\x9f\x92\xb4" => "\xee\x95\xbd",
        "\xf0\x9f\x92\xb5" => "\xee\x96\x85",
        "\xf0\x9f\x92\xb6" => "",
        "\xf0\x9f\x92\xb7" => "",
        "\xf0\x9f\x92\xb8" => "\xee\xad\x9b",
        "\xf0\x9f\x92\xb9" => "\xee\x97\x9c",
        "\xf0\x9f\x92\xba" => "",
        "\xf0\x9f\x92\xbb" => "\xee\x96\xb8",
        "\xf0\x9f\x92\xbc" => "\xee\x97\x8e",
        "\xf0\x9f\x92\xbd" => "\xee\x96\x82",
        "\xf0\x9f\x92\xbe" => "\xee\x95\xa2",
        "\xf0\x9f\x92\xbf" => "\xee\x94\x8c",
        "\xf0\x9f\x93\x80" => "\xee\x94\x8c",
        "\xf0\x9f\x93\x81" => "\xee\x96\x8f",
        "\xf0\x9f\x93\x82" => "\xee\x96\x90",
        "\xf0\x9f\x93\x83" => "\xee\x95\xa1",
        "\xf0\x9f\x93\x84" => "\xee\x95\xa9",
        "\xf0\x9f\x93\x85" => "\xee\x95\xa3",
        "\xf0\x9f\x93\x86" => "\xee\x95\xaa",
        "\xf0\x9f\x93\x87" => "\xee\x95\xac",
        "\xf0\x9f\x93\x88" => "\xee\x95\xb5",
        "\xf0\x9f\x93\x89" => "\xee\x95\xb6",
        "\xf0\x9f\x93\x8a" => "\xee\x95\xb4",
        "\xf0\x9f\x93\x8b" => "\xee\x95\xa4",
        "\xf0\x9f\x93\x8c" => "\xee\x95\xad",
        "\xf0\x9f\x93\x8d" => "\xee\x95\xa0",
        "\xf0\x9f\x93\x8e" => "\xee\x92\xa0",
        "\xf0\x9f\x93\x8f" => "\xee\x95\xb0",
        "\xf0\x9f\x93\x90" => "\xee\x92\xa2",
        "\xf0\x9f\x93\x91" => "\xee\xac\x8b",
        "\xf0\x9f\x93\x92" => "\xee\x95\xae",
        "\xf0\x9f\x93\x93" => "\xee\x95\xab",
        "\xf0\x9f\x93\x94" => "\xee\x92\x9d",
        "\xf0\x9f\x93\x95" => "\xee\x95\xa8",
        "\xf0\x9f\x93\x96" => "\xee\x92\x9f",
        "\xf0\x9f\x93\x97" => "\xee\x95\xa5",
        "\xf0\x9f\x93\x98" => "\xee\x95\xa6",
        "\xf0\x9f\x93\x99" => "\xee\x95\xa7",
        "\xf0\x9f\x93\x9a" => "\xee\x95\xaf",
        "\xf0\x9f\x93\x9b" => "\xee\x94\x9d",
        "\xf0\x9f\x93\x9c" => "\xee\x95\x9f",
        "\xf0\x9f\x93\x9d" => "\xee\xaa\x92",
        "\xf0\x9f\x93\x9e" => "\xee\x94\x9e",
        "\xf0\x9f\x93\x9f" => "\xee\x96\x9b",
        "\xf0\x9f\x93\xa0" => "\xee\x94\xa0",
        "\xf0\x9f\x93\xa1" => "\xee\x92\xa8",
        "\xf0\x9f\x93\xa2" => "\xee\x94\x91",
        "\xf0\x9f\x93\xa3" => "\xee\x94\x91",
        "\xf0\x9f\x93\xa4" => "\xee\x96\x92",
        "\xf0\x9f\x93\xa5" => "\xee\x96\x93",
        "\xf0\x9f\x93\xa6" => "\xee\x94\x9f",
        "\xf0\x9f\x93\xa7" => "\xee\xad\xb1",
        "\xf0\x9f\x93\xa8" => "\xee\x96\x91",
        "\xf0\x9f\x93\xa9" => "\xee\xad\xa2",
        "\xf0\x9f\x93\xaa" => "\xee\x94\x9b",
        "\xf0\x9f\x93\xab" => "\xee\xac\x8a",
        "\xf0\x9f\x93\xac" => "",
        "\xf0\x9f\x93\xad" => "",
        "\xf0\x9f\x93\xae" => "\xee\x94\x9b",
        "\xf0\x9f\x93\xaf" => "",
        "\xf0\x9f\x93\xb0" => "\xee\x96\x8b",
        "\xf0\x9f\x93\xb1" => "\xee\x96\x88",
        "\xf0\x9f\x93\xb2" => "\xee\xac\x88",
        "\xf0\x9f\x93\xb3" => "\xee\xaa\x90",
        "\xf0\x9f\x93\xb4" => "\xee\xaa\x91",
        "\xf0\x9f\x93\xb5" => "",
        "\xf0\x9f\x93\xb6" => "\xee\xaa\x84",
        "\xf0\x9f\x93\xb7" => "\xee\x94\x95",
        "\xf0\x9f\x93\xb8" => "",
        "\xf0\x9f\x93\xb9" => "\xee\x95\xbe",
        "\xf0\x9f\x93\xba" => "\xee\x94\x82",
        "\xf0\x9f\x93\xbb" => "\xee\x96\xb9",
        "\xf0\x9f\x93\xbc" => "\xee\x96\x80",
        "\xf0\x9f\x93\xbd" => "",
        "\xf0\x9f\x93\xbf" => "",
        "\xf0\x9f\x94\x80" => "",
        "\xf0\x9f\x94\x81" => "",
        "\xf0\x9f\x94\x82" => "",
        "\xf0\x9f\x94\x83" => "\xee\xac\x8d",
        "\xf0\x9f\x94\x84" => "",
        "\xf0\x9f\x94\x85" => "",
        "\xf0\x9f\x94\x86" => "",
        "\xf0\x9f\x94\x87" => "",
        "\xf0\x9f\x94\x88" => "",
        "\xf0\x9f\x94\x89" => "",
        "\xf0\x9f\x94\x8a" => "\xee\x94\x91",
        "\xf0\x9f\x94\x8b" => "\xee\x96\x84",
        "\xf0\x9f\x94\x8c" => "\xee\x96\x89",
        "\xf0\x9f\x94\x8d" => "\xee\x94\x98",
        "\xf0\x9f\x94\x8e" => "\xee\xac\x85",
        "\xf0\x9f\x94\x8f" => "\xee\xac\x8c",
        "\xf0\x9f\x94\x90" => "\xee\xab\xbc",
        "\xf0\x9f\x94\x91" => "\xee\x94\x99",
        "\xf0\x9f\x94\x92" => "\xee\x94\x9c",
        "\xf0\x9f\x94\x93" => "\xee\x94\x9c",
        "\xf0\x9f\x94\x94" => "\xee\x94\x92",
        "\xf0\x9f\x94\x95" => "",
        "\xf0\x9f\x94\x96" => "\xee\xac\x87",
        "\xf0\x9f\x94\x97" => "\xee\x96\x8a",
        "\xf0\x9f\x94\x98" => "\xee\xac\x84",
        "\xf0\x9f\x94\x99" => "\xee\xac\x86",
        "\xf0\x9f\x94\x9a" => "",
        "\xf0\x9f\x94\x9b" => "",
        "\xf0\x9f\x94\x9c" => "",
        "\xf0\x9f\x94\x9d" => "",
        "\xf0\x9f\x94\x9e" => "\xee\xaa\x83",
        "\xf0\x9f\x94\x9f" => "\xee\x94\xab",
        "\xf0\x9f\x94\xa0" => "\xee\xab\xbd",
        "\xf0\x9f\x94\xa1" => "\xee\xab\xbe",
        "\xf0\x9f\x94\xa2" => "\xee\xab\xbf",
        "\xf0\x9f\x94\xa3" => "\xee\xac\x80",
        "\xf0\x9f\x94\xa4" => "\xee\xad\x95",
        "\xf0\x9f\x94\xa5" => "\xee\x91\xbb",
        "\xf0\x9f\x94\xa6" => "\xee\x96\x83",
        "\xf0\x9f\x94\xa7" => "\xee\x96\x87",
        "\xf0\x9f\x94\xa8" => "\xee\x97\x8b",
        "\xf0\x9f\x94\xa9" => "\xee\x96\x81",
        "\xf0\x9f\x94\xaa" => "\xee\x95\xbf",
        "\xf0\x9f\x94\xab" => "\xee\x94\x8a",
        "\xf0\x9f\x94\xac" => "",
        "\xf0\x9f\x94\xad" => "",
        "\xf0\x9f\x94\xae" => "\xee\xaa\x8f",
        "\xf0\x9f\x94\xaf" => "\xee\xaa\x8f",
        "\xf0\x9f\x94\xb0" => "\xee\x92\x80",
        "\xf0\x9f\x94\xb1" => "\xee\x97\x89",
        "\xf0\x9f\x94\xb2" => "\xee\x95\x8b",
        "\xf0\x9f\x94\xb3" => "\xee\x95\x8b",
        "\xf0\x9f\x94\xb4" => "\xee\x95\x8a",
        "\xf0\x9f\x94\xb5" => "\xee\x95\x8b",
        "\xf0\x9f\x94\xb6" => "\xee\x95\x86",
        "\xf0\x9f\x94\xb7" => "\xee\x95\x87",
        "\xf0\x9f\x94\xb8" => "\xee\x94\xb6",
        "\xf0\x9f\x94\xb9" => "\xee\x94\xb7",
        "\xf0\x9f\x94\xba" => "\xee\x95\x9a",
        "\xf0\x9f\x94\xbb" => "\xee\x95\x9b",
        "\xf0\x9f\x94\xbc" => "\xee\x95\x83",
        "\xf0\x9f\x94\xbd" => "\xee\x95\x82",
        "\xf0\x9f\x95\x89" => "",
        "\xf0\x9f\x95\x8a" => "",
        "\xf0\x9f\x95\x8b" => "",
        "\xf0\x9f\x95\x8c" => "",
        "\xf0\x9f\x95\x8d" => "",
        "\xf0\x9f\x95\x8e" => "",
        "\xf0\x9f\x95\x90" => "\xee\x96\x94",
        "\xf0\x9f\x95\x91" => "\xee\x96\x94",
        "\xf0\x9f\x95\x92" => "\xee\x96\x94",
        "\xf0\x9f\x95\x93" => "\xee\x96\x94",
        "\xf0\x9f\x95\x94" => "\xee\x96\x94",
        "\xf0\x9f\x95\x95" => "\xee\x96\x94",
        "\xf0\x9f\x95\x96" => "\xee\x96\x94",
        "\xf0\x9f\x95\x97" => "\xee\x96\x94",
        "\xf0\x9f\x95\x98" => "\xee\x96\x94",
        "\xf0\x9f\x95\x99" => "\xee\x96\x94",
        "\xf0\x9f\x95\x9a" => "\xee\x96\x94",
        "\xf0\x9f\x95\x9b" => "\xee\x96\x94",
        "\xf0\x9f\x95\x9c" => "",
        "\xf0\x9f\x95\x9d" => "",
        "\xf0\x9f\x95\x9e" => "",
        "\xf0\x9f\x95\x9f" => "",
        "\xf0\x9f\x95\xa0" => "",
        "\xf0\x9f\x95\xa1" => "",
        "\xf0\x9f\x95\xa2" => "",
        "\xf0\x9f\x95\xa3" => "",
        "\xf0\x9f\x95\xa4" => "",
        "\xf0\x9f\x95\xa5" => "",
        "\xf0\x9f\x95\xa6" => "",
        "\xf0\x9f\x95\xa7" => "",
        "\xf0\x9f\x95\xaf" => "",
        "\xf0\x9f\x95\xb0" => "",
        "\xf0\x9f\x95\xb3" => "",
        "\xf0\x9f\x95\xb4" => "",
        "\xf0\x9f\x95\xb5" => "",
        "\xf0\x9f\x95\xb6" => "",
        "\xf0\x9f\x95\xb7" => "",
        "\xf0\x9f\x95\xb8" => "",
        "\xf0\x9f\x95\xb9" => "",
        "\xf0\x9f\x96\x87" => "",
        "\xf0\x9f\x96\x8a" => "",
        "\xf0\x9f\x96\x8b" => "",
        "\xf0\x9f\x96\x8c" => "",
        "\xf0\x9f\x96\x8d" => "",
        "\xf0\x9f\x96\x90" => "",
        "\xf0\x9f\x96\x95" => "",
        "\xf0\x9f\x96\x96" => "",
        "\xf0\x9f\x96\xa5" => "",
        "\xf0\x9f\x96\xa8" => "",
        "\xf0\x9f\x96\xb1" => "",
        "\xf0\x9f\x96\xb2" => "",
        "\xf0\x9f\x96\xbc" => "",
        "\xf0\x9f\x97\x82" => "",
        "\xf0\x9f\x97\x83" => "",
        "\xf0\x9f\x97\x84" => "",
        "\xf0\x9f\x97\x91" => "",
        "\xf0\x9f\x97\x92" => "",
        "\xf0\x9f\x97\x93" => "",
        "\xf0\x9f\x97\x9c" => "",
        "\xf0\x9f\x97\x9d" => "",
        "\xf0\x9f\x97\x9e" => "",
        "\xf0\x9f\x97\xa1" => "",
        "\xf0\x9f\x97\xa3" => "",
        "\xf0\x9f\x97\xa8" => "",
        "\xf0\x9f\x97\xaf" => "",
        "\xf0\x9f\x97\xb3" => "",
        "\xf0\x9f\x97\xba" => "",
        "\xf0\x9f\x97\xbb" => "\xee\x96\xbd",
        "\xf0\x9f\x97\xbc" => "\xee\x93\x80",
        "\xf0\x9f\x97\xbd" => "",
        "\xf0\x9f\x97\xbe" => "\xee\x95\xb2",
        "\xf0\x9f\x97\xbf" => "\xee\xad\xac",
        "\xf0\x9f\x98\x80" => "",
        "\xf0\x9f\x98\x81" => "\xee\xae\x80",
        "\xf0\x9f\x98\x82" => "\xee\xad\xa4",
        "\xf0\x9f\x98\x83" => "\xee\x91\xb1",
        "\xf0\x9f\x98\x84" => "\xee\x91\xb1",
        "\xf0\x9f\x98\x85" => "\xee\x91\xb1\xee\x96\xb1",
        "\xf0\x9f\x98\x86" => "\xee\xab\x85",
        "\xf0\x9f\x98\x87" => "",
        "\xf0\x9f\x98\x88" => "",
        "\xf0\x9f\x98\x89" => "\xee\x97\x83",
        "\xf0\x9f\x98\x8a" => "\xee\xab\x8d",
        "\xf0\x9f\x98\x8b" => "\xee\xab\x8d",
        "\xf0\x9f\x98\x8c" => "\xee\xab\x85",
        "\xf0\x9f\x98\x8d" => "\xee\x97\x84",
        "\xf0\x9f\x98\x8e" => "",
        "\xf0\x9f\x98\x8f" => "\xee\xaa\xbf",
        "\xf0\x9f\x98\x90" => "",
        "\xf0\x9f\x98\x91" => "",
        "\xf0\x9f\x98\x92" => "\xee\xab\x89",
        "\xf0\x9f\x98\x93" => "\xee\x97\x86",
        "\xf0\x9f\x98\x94" => "\xee\xab\x80",
        "\xf0\x9f\x98\x95" => "",
        "\xf0\x9f\x98\x96" => "\xee\xab\x83",
        "\xf0\x9f\x98\x97" => "",
        "\xf0\x9f\x98\x98" => "\xee\xab\x8f",
        "\xf0\x9f\x98\x99" => "",
        "\xf0\x9f\x98\x9a" => "\xee\xab\x8e",
        "\xf0\x9f\x98\x9b" => "",
        "\xf0\x9f\x98\x9c" => "\xee\x93\xa7",
        "\xf0\x9f\x98\x9d" => "\xee\x93\xa7",
        "\xf0\x9f\x98\x9e" => "\xee\xab\x80",
        "\xf0\x9f\x98\x9f" => "",
        "\xf0\x9f\x98\xa0" => "\xee\x91\xb2",
        "\xf0\x9f\x98\xa1" => "\xee\xad\x9d",
        "\xf0\x9f\x98\xa2" => "\xee\xad\xa9",
        "\xf0\x9f\x98\xa3" => "\xee\xab\x82",
        "\xf0\x9f\x98\xa4" => "\xee\xab\x81",
        "\xf0\x9f\x98\xa5" => "\xee\x97\x86",
        "\xf0\x9f\x98\xa6" => "",
        "\xf0\x9f\x98\xa7" => "",
        "\xf0\x9f\x98\xa8" => "\xee\xab\x86",
        "\xf0\x9f\x98\xa9" => "\xee\xad\xa7",
        "\xf0\x9f\x98\xaa" => "\xee\xab\x84",
        "\xf0\x9f\x98\xab" => "\xee\x91\xb4",
        "\xf0\x9f\x98\xac" => "",
        "\xf0\x9f\x98\xad" => "\xee\x91\xb3",
        "\xf0\x9f\x98\xae" => "",
        "\xf0\x9f\x98\xaf" => "",
        "\xf0\x9f\x98\xb0" => "\xee\xab\x8b",
        "\xf0\x9f\x98\xb1" => "\xee\x97\x85",
        "\xf0\x9f\x98\xb2" => "\xee\xab\x8a",
        "\xf0\x9f\x98\xb3" => "\xee\xab\x88",
        "\xf0\x9f\x98\xb4" => "",
        "\xf0\x9f\x98\xb5" => "\xee\x96\xae",
        "\xf0\x9f\x98\xb6" => "",
        "\xf0\x9f\x98\xb7" => "\xee\xab\x87",
        "\xf0\x9f\x98\xb8" => "\xee\xad\xbf",
        "\xf0\x9f\x98\xb9" => "\xee\xad\xa3",
        "\xf0\x9f\x98\xba" => "\xee\xad\xa1",
        "\xf0\x9f\x98\xbb" => "\xee\xad\xa5",
        "\xf0\x9f\x98\xbc" => "\xee\xad\xaa",
        "\xf0\x9f\x98\xbd" => "\xee\xad\xa0",
        "\xf0\x9f\x98\xbe" => "\xee\xad\x9e",
        "\xf0\x9f\x98\xbf" => "\xee\xad\xa8",
        "\xf0\x9f\x99\x80" => "\xee\xad\xa6",
        "\xf0\x9f\x99\x81" => "",
        "\xf0\x9f\x99\x82" => "",
        "\xf0\x9f\x99\x83" => "",
        "\xf0\x9f\x99\x84" => "",
        "\xf0\x9f\x99\x85" => "\xee\xab\x97",
        "\xf0\x9f\x99\x86" => "\xee\xab\x98",
        "\xf0\x9f\x99\x87" => "\xee\xab\x99",
        "\xf0\x9f\x99\x88" => "\xee\xad\x90",
        "\xf0\x9f\x99\x89" => "\xee\xad\x92",
        "\xf0\x9f\x99\x8a" => "\xee\xad\x91",
        "\xf0\x9f\x99\x8b" => "\xee\xae\x85",
        "\xf0\x9f\x99\x8c" => "\xee\xae\x86",
        "\xf0\x9f\x99\x8d" => "\xee\xae\x87",
        "\xf0\x9f\x99\x8e" => "\xee\xae\x88",
        "\xf0\x9f\x99\x8f" => "\xee\xab\x92",
        "\xf0\x9f\x9a\x80" => "\xee\x97\x88",
        "\xf0\x9f\x9a\x81" => "",
        "\xf0\x9f\x9a\x82" => "",
        "\xf0\x9f\x9a\x83" => "\xee\x92\xb5",
        "\xf0\x9f\x9a\x84" => "\xee\x92\xb0",
        "\xf0\x9f\x9a\x85" => "\xee\x92\xb0",
        "\xf0\x9f\x9a\x86" => "",
        "\xf0\x9f\x9a\x87" => "\xee\x96\xbc",
        "\xf0\x9f\x9a\x88" => "",
        "\xf0\x9f\x9a\x89" => "\xee\xad\xad",
        "\xf0\x9f\x9a\x8a" => "",
        "\xf0\x9f\x9a\x8b" => "",
        "\xf0\x9f\x9a\x8c" => "\xee\x92\xaf",
        "\xf0\x9f\x9a\x8d" => "",
        "\xf0\x9f\x9a\x8e" => "",
        "\xf0\x9f\x9a\x8f" => "\xee\x92\xa7",
        "\xf0\x9f\x9a\x90" => "",
        "\xf0\x9f\x9a\x91" => "\xee\xab\xa0",
        "\xf0\x9f\x9a\x92" => "\xee\xab\x9f",
        "\xf0\x9f\x9a\x93" => "\xee\xab\xa1",
        "\xf0\x9f\x9a\x94" => "",
        "\xf0\x9f\x9a\x95" => "\xee\x92\xb1",
        "\xf0\x9f\x9a\x96" => "",
        "\xf0\x9f\x9a\x97" => "\xee\x92\xb1",
        "\xf0\x9f\x9a\x98" => "",
        "\xf0\x9f\x9a\x99" => "\xee\x92\xb1",
        "\xf0\x9f\x9a\x9a" => "\xee\x92\xb2",
        "\xf0\x9f\x9a\x9b" => "",
        "\xf0\x9f\x9a\x9c" => "",
        "\xf0\x9f\x9a\x9d" => "",
        "\xf0\x9f\x9a\x9e" => "",
        "\xf0\x9f\x9a\x9f" => "",
        "\xf0\x9f\x9a\xa0" => "",
        "\xf0\x9f\x9a\xa1" => "",
        "\xf0\x9f\x9a\xa2" => "\xee\xaa\x82",
        "\xf0\x9f\x9a\xa3" => "",
        "\xf0\x9f\x9a\xa4" => "\xee\x92\xb4",
        "\xf0\x9f\x9a\xa5" => "\xee\x91\xaa",
        "\xf0\x9f\x9a\xa6" => "",
        "\xf0\x9f\x9a\xa7" => "\xee\x97\x97",
        "\xf0\x9f\x9a\xa8" => "\xee\xad\xb3",
        "\xf0\x9f\x9a\xa9" => "\xee\xac\xac",
        "\xf0\x9f\x9a\xaa" => "",
        "\xf0\x9f\x9a\xab" => "\xee\x95\x81",
        "\xf0\x9f\x9a\xac" => "\xee\x91\xbd",
        "\xf0\x9f\x9a\xad" => "\xee\x91\xbe",
        "\xf0\x9f\x9a\xae" => "",
        "\xf0\x9f\x9a\xaf" => "",
        "\xf0\x9f\x9a\xb0" => "",
        "\xf0\x9f\x9a\xb1" => "",
        "\xf0\x9f\x9a\xb2" => "\xee\x92\xae",
        "\xf0\x9f\x9a\xb3" => "",
        "\xf0\x9f\x9a\xb4" => "",
        "\xf0\x9f\x9a\xb5" => "",
        "\xf0\x9f\x9a\xb6" => "\xee\xad\xb2",
        "\xf0\x9f\x9a\xb7" => "",
        "\xf0\x9f\x9a\xb8" => "",
        "\xf0\x9f\x9a\xb9" => "",
        "\xf0\x9f\x9a\xba" => "",
        "\xf0\x9f\x9a\xbb" => "\xee\x92\xa5",
        "\xf0\x9f\x9a\xbc" => "\xee\xac\x98",
        "\xf0\x9f\x9a\xbd" => "\xee\x92\xa5",
        "\xf0\x9f\x9a\xbe" => "\xee\x92\xa5",
        "\xf0\x9f\x9a\xbf" => "",
        "\xf0\x9f\x9b\x80" => "\xee\x97\x98",
        "\xf0\x9f\x9b\x81" => "",
        "\xf0\x9f\x9b\x82" => "",
        "\xf0\x9f\x9b\x83" => "",
        "\xf0\x9f\x9b\x84" => "",
        "\xf0\x9f\x9b\x85" => "",
        "\xf0\x9f\x9b\x8b" => "",
        "\xf0\x9f\x9b\x8c" => "",
        "\xf0\x9f\x9b\x8d" => "",
        "\xf0\x9f\x9b\x8e" => "",
        "\xf0\x9f\x9b\x8f" => "",
        "\xf0\x9f\x9b\x90" => "",
        "\xf0\x9f\x9b\xa0" => "",
        "\xf0\x9f\x9b\xa1" => "",
        "\xf0\x9f\x9b\xa2" => "",
        "\xf0\x9f\x9b\xa3" => "",
        "\xf0\x9f\x9b\xa4" => "",
        "\xf0\x9f\x9b\xa5" => "",
        "\xf0\x9f\x9b\xa9" => "",
        "\xf0\x9f\x9b\xab" => "",
        "\xf0\x9f\x9b\xac" => "",
        "\xf0\x9f\x9b\xb0" => "",
        "\xf0\x9f\x9b\xb3" => "",
        "\xf0\x9f\xa4\x90" => "",
        "\xf0\x9f\xa4\x91" => "",
        "\xf0\x9f\xa4\x92" => "",
        "\xf0\x9f\xa4\x93" => "",
        "\xf0\x9f\xa4\x94" => "",
        "\xf0\x9f\xa4\x95" => "",
        "\xf0\x9f\xa4\x96" => "",
        "\xf0\x9f\xa4\x97" => "",
        "\xf0\x9f\xa4\x98" => "",
        "\xf0\x9f\xa6\x80" => "",
        "\xf0\x9f\xa6\x81" => "",
        "\xf0\x9f\xa6\x82" => "",
        "\xf0\x9f\xa6\x83" => "",
        "\xf0\x9f\xa6\x84" => "",
        "\xf0\x9f\xa7\x80" => "",
        "#\xe2\x83\xa3" => "\xee\xae\x84",
        "*\xe2\x83\xa3" => "",
        "0\xe2\x83\xa3" => "\xee\x96\xac",
        "1\xe2\x83\xa3" => "\xee\x94\xa2",
        "2\xe2\x83\xa3" => "\xee\x94\xa3",
        "3\xe2\x83\xa3" => "\xee\x94\xa4",
        "4\xe2\x83\xa3" => "\xee\x94\xa5",
        "5\xe2\x83\xa3" => "\xee\x94\xa6",
        "6\xe2\x83\xa3" => "\xee\x94\xa7",
        "7\xe2\x83\xa3" => "\xee\x94\xa8",
        "8\xe2\x83\xa3" => "\xee\x94\xa9",
        "9\xe2\x83\xa3" => "\xee\x94\xaa",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa7" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb3" => "\xee\xac\x91",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaa" => "\xee\xac\x8e",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb8" => "\xee\x97\x95",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb7" => "\xee\xab\xba",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa7" => "\xee\xac\x90",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb9" => "\xee\xac\x8f",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb5" => "\xee\x93\x8c",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb7" => "\xee\xac\x92",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa7" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb4\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb6\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xba" => "\xee\x97\x96",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa7" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xb8" => "\xee\x95\xb3",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xbc\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xbc\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xbd\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xbe\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xbe\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x91\xa8" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x92\x8b\xe2\x80\x8d\xf0\x9f\x91\xa8" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x91\xa9" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x92\x8b\xe2\x80\x8d\xf0\x9f\x91\xa9" => "",
    ),
    'unified_to_softbank' => array(
        "\xc2\xa9" => "\xee\x89\x8e",
        "\xc2\xae" => "\xee\x89\x8f",
        "\xe2\x80\xbc" => "",
        "\xe2\x81\x89" => "",
        "\xe2\x84\xa2" => "\xee\x94\xb7",
        "\xe2\x84\xb9" => "",
        "\xe2\x86\x94" => "",
        "\xe2\x86\x95" => "",
        "\xe2\x86\x96" => "\xee\x88\xb7",
        "\xe2\x86\x97" => "\xee\x88\xb6",
        "\xe2\x86\x98" => "\xee\x88\xb8",
        "\xe2\x86\x99" => "\xee\x88\xb9",
        "\xe2\x86\xa9" => "",
        "\xe2\x86\xaa" => "",
        "\xe2\x8c\x9a" => "",
        "\xe2\x8c\x9b" => "",
        "\xe2\x8c\xa8" => "",
        "\xe2\x8f\xa9" => "\xee\x88\xbc",
        "\xe2\x8f\xaa" => "\xee\x88\xbd",
        "\xe2\x8f\xab" => "",
        "\xe2\x8f\xac" => "",
        "\xe2\x8f\xad" => "",
        "\xe2\x8f\xae" => "",
        "\xe2\x8f\xaf" => "",
        "\xe2\x8f\xb0" => "\xee\x80\xad",
        "\xe2\x8f\xb1" => "",
        "\xe2\x8f\xb2" => "",
        "\xe2\x8f\xb3" => "",
        "\xe2\x8f\xb8" => "",
        "\xe2\x8f\xb9" => "",
        "\xe2\x8f\xba" => "",
        "\xe2\x93\x82" => "\xee\x90\xb4",
        "\xe2\x96\xaa" => "\xee\x88\x9a",
        "\xe2\x96\xab" => "\xee\x88\x9b",
        "\xe2\x96\xb6" => "\xee\x88\xba",
        "\xe2\x97\x80" => "\xee\x88\xbb",
        "\xe2\x97\xbb" => "\xee\x88\x9b",
        "\xe2\x97\xbc" => "\xee\x88\x9a",
        "\xe2\x97\xbd" => "\xee\x88\x9b",
        "\xe2\x97\xbe" => "\xee\x88\x9a",
        "\xe2\x98\x80" => "\xee\x81\x8a",
        "\xe2\x98\x81" => "\xee\x81\x89",
        "\xe2\x98\x82" => "",
        "\xe2\x98\x83" => "",
        "\xe2\x98\x84" => "",
        "\xe2\x98\x8e" => "\xee\x80\x89",
        "\xe2\x98\x91" => "",
        "\xe2\x98\x94" => "\xee\x81\x8b",
        "\xe2\x98\x95" => "\xee\x81\x85",
        "\xe2\x98\x98" => "",
        "\xe2\x98\x9d" => "\xee\x80\x8f",
        "\xe2\x98\xa0" => "",
        "\xe2\x98\xa2" => "",
        "\xe2\x98\xa3" => "",
        "\xe2\x98\xa6" => "",
        "\xe2\x98\xaa" => "",
        "\xe2\x98\xae" => "",
        "\xe2\x98\xaf" => "",
        "\xe2\x98\xb8" => "",
        "\xe2\x98\xb9" => "",
        "\xe2\x98\xba" => "\xee\x90\x94",
        "\xe2\x99\x88" => "\xee\x88\xbf",
        "\xe2\x99\x89" => "\xee\x89\x80",
        "\xe2\x99\x8a" => "\xee\x89\x81",
        "\xe2\x99\x8b" => "\xee\x89\x82",
        "\xe2\x99\x8c" => "\xee\x89\x83",
        "\xe2\x99\x8d" => "\xee\x89\x84",
        "\xe2\x99\x8e" => "\xee\x89\x85",
        "\xe2\x99\x8f" => "\xee\x89\x86",
        "\xe2\x99\x90" => "\xee\x89\x87",
        "\xe2\x99\x91" => "\xee\x89\x88",
        "\xe2\x99\x92" => "\xee\x89\x89",
        "\xe2\x99\x93" => "\xee\x89\x8a",
        "\xe2\x99\xa0" => "\xee\x88\x8e",
        "\xe2\x99\xa3" => "\xee\x88\x8f",
        "\xe2\x99\xa5" => "\xee\x88\x8c",
        "\xe2\x99\xa6" => "\xee\x88\x8d",
        "\xe2\x99\xa8" => "\xee\x84\xa3",
        "\xe2\x99\xbb" => "",
        "\xe2\x99\xbf" => "\xee\x88\x8a",
        "\xe2\x9a\x92" => "",
        "\xe2\x9a\x93" => "\xee\x88\x82",
        "\xe2\x9a\x94" => "",
        "\xe2\x9a\x96" => "",
        "\xe2\x9a\x97" => "",
        "\xe2\x9a\x99" => "",
        "\xe2\x9a\x9b" => "",
        "\xe2\x9a\x9c" => "",
        "\xe2\x9a\xa0" => "\xee\x89\x92",
        "\xe2\x9a\xa1" => "\xee\x84\xbd",
        "\xe2\x9a\xaa" => "\xee\x88\x99",
        "\xe2\x9a\xab" => "\xee\x88\x99",
        "\xe2\x9a\xb0" => "",
        "\xe2\x9a\xb1" => "",
        "\xe2\x9a\xbd" => "\xee\x80\x98",
        "\xe2\x9a\xbe" => "\xee\x80\x96",
        "\xe2\x9b\x84" => "\xee\x81\x88",
        "\xe2\x9b\x85" => "\xee\x81\x8a\xee\x81\x89",
        "\xe2\x9b\x88" => "",
        "\xe2\x9b\x8e" => "\xee\x89\x8b",
        "\xe2\x9b\x8f" => "",
        "\xe2\x9b\x91" => "",
        "\xe2\x9b\x93" => "",
        "\xe2\x9b\x94" => "\xee\x84\xb7",
        "\xe2\x9b\xa9" => "",
        "\xe2\x9b\xaa" => "\xee\x80\xb7",
        "\xe2\x9b\xb0" => "",
        "\xe2\x9b\xb1" => "",
        "\xe2\x9b\xb2" => "\xee\x84\xa1",
        "\xe2\x9b\xb3" => "\xee\x80\x94",
        "\xe2\x9b\xb4" => "",
        "\xe2\x9b\xb5" => "\xee\x80\x9c",
        "\xe2\x9b\xb7" => "",
        "\xe2\x9b\xb8" => "",
        "\xe2\x9b\xb9" => "",
        "\xe2\x9b\xba" => "\xee\x84\xa2",
        "\xe2\x9b\xbd" => "\xee\x80\xba",
        "\xe2\x9c\x82" => "\xee\x8c\x93",
        "\xe2\x9c\x85" => "",
        "\xe2\x9c\x88" => "\xee\x80\x9d",
        "\xe2\x9c\x89" => "\xee\x84\x83",
        "\xe2\x9c\x8a" => "\xee\x80\x90",
        "\xe2\x9c\x8b" => "\xee\x80\x92",
        "\xe2\x9c\x8c" => "\xee\x80\x91",
        "\xe2\x9c\x8d" => "",
        "\xe2\x9c\x8f" => "\xee\x8c\x81",
        "\xe2\x9c\x92" => "",
        "\xe2\x9c\x94" => "",
        "\xe2\x9c\x96" => "\xee\x8c\xb3",
        "\xe2\x9c\x9d" => "",
        "\xe2\x9c\xa1" => "",
        "\xe2\x9c\xa8" => "\xee\x8c\xae",
        "\xe2\x9c\xb3" => "\xee\x88\x86",
        "\xe2\x9c\xb4" => "\xee\x88\x85",
        "\xe2\x9d\x84" => "",
        "\xe2\x9d\x87" => "\xee\x8c\xae",
        "\xe2\x9d\x8c" => "\xee\x8c\xb3",
        "\xe2\x9d\x8e" => "\xee\x8c\xb3",
        "\xe2\x9d\x93" => "\xee\x80\xa0",
        "\xe2\x9d\x94" => "\xee\x8c\xb6",
        "\xe2\x9d\x95" => "\xee\x8c\xb7",
        "\xe2\x9d\x97" => "\xee\x80\xa1",
        "\xe2\x9d\xa3" => "",
        "\xe2\x9d\xa4" => "\xee\x80\xa2",
        "\xe2\x9e\x95" => "",
        "\xe2\x9e\x96" => "",
        "\xe2\x9e\x97" => "",
        "\xe2\x9e\xa1" => "\xee\x88\xb4",
        "\xe2\x9e\xb0" => "",
        "\xe2\x9e\xbf" => "\xee\x88\x91",
        "\xe2\xa4\xb4" => "\xee\x88\xb6",
        "\xe2\xa4\xb5" => "\xee\x88\xb8",
        "\xe2\xac\x85" => "\xee\x88\xb5",
        "\xe2\xac\x86" => "\xee\x88\xb2",
        "\xe2\xac\x87" => "\xee\x88\xb3",
        "\xe2\xac\x9b" => "\xee\x88\x9a",
        "\xe2\xac\x9c" => "\xee\x88\x9b",
        "\xe2\xad\x90" => "\xee\x8c\xaf",
        "\xe2\xad\x95" => "\xee\x8c\xb2",
        "\xe3\x80\xb0" => "",
        "\xe3\x80\xbd" => "\xee\x84\xac",
        "\xe3\x8a\x97" => "\xee\x8c\x8d",
        "\xe3\x8a\x99" => "\xee\x8c\x95",
        "\xf0\x9f\x80\x84" => "\xee\x84\xad",
        "\xf0\x9f\x83\x8f" => "",
        "\xf0\x9f\x85\xb0" => "\xee\x94\xb2",
        "\xf0\x9f\x85\xb1" => "\xee\x94\xb3",
        "\xf0\x9f\x85\xbe" => "\xee\x94\xb5",
        "\xf0\x9f\x85\xbf" => "\xee\x85\x8f",
        "\xf0\x9f\x86\x8e" => "\xee\x94\xb4",
        "\xf0\x9f\x86\x91" => "",
        "\xf0\x9f\x86\x92" => "\xee\x88\x94",
        "\xf0\x9f\x86\x93" => "",
        "\xf0\x9f\x86\x94" => "\xee\x88\xa9",
        "\xf0\x9f\x86\x95" => "\xee\x88\x92",
        "\xf0\x9f\x86\x96" => "",
        "\xf0\x9f\x86\x97" => "\xee\x89\x8d",
        "\xf0\x9f\x86\x98" => "",
        "\xf0\x9f\x86\x99" => "\xee\x88\x93",
        "\xf0\x9f\x86\x9a" => "\xee\x84\xae",
        "\xf0\x9f\x88\x81" => "\xee\x88\x83",
        "\xf0\x9f\x88\x82" => "\xee\x88\xa8",
        "\xf0\x9f\x88\x9a" => "\xee\x88\x96",
        "\xf0\x9f\x88\xaf" => "\xee\x88\xac",
        "\xf0\x9f\x88\xb2" => "",
        "\xf0\x9f\x88\xb3" => "\xee\x88\xab",
        "\xf0\x9f\x88\xb4" => "",
        "\xf0\x9f\x88\xb5" => "\xee\x88\xaa",
        "\xf0\x9f\x88\xb6" => "\xee\x88\x95",
        "\xf0\x9f\x88\xb7" => "\xee\x88\x97",
        "\xf0\x9f\x88\xb8" => "\xee\x88\x98",
        "\xf0\x9f\x88\xb9" => "\xee\x88\xa7",
        "\xf0\x9f\x88\xba" => "\xee\x88\xad",
        "\xf0\x9f\x89\x90" => "\xee\x88\xa6",
        "\xf0\x9f\x89\x91" => "",
        "\xf0\x9f\x8c\x80" => "\xee\x91\x83",
        "\xf0\x9f\x8c\x81" => "",
        "\xf0\x9f\x8c\x82" => "\xee\x90\xbc",
        "\xf0\x9f\x8c\x83" => "\xee\x91\x8b",
        "\xf0\x9f\x8c\x84" => "\xee\x81\x8d",
        "\xf0\x9f\x8c\x85" => "\xee\x91\x89",
        "\xf0\x9f\x8c\x86" => "\xee\x85\x86",
        "\xf0\x9f\x8c\x87" => "\xee\x91\x8a",
        "\xf0\x9f\x8c\x88" => "\xee\x91\x8c",
        "\xf0\x9f\x8c\x89" => "\xee\x91\x8b",
        "\xf0\x9f\x8c\x8a" => "\xee\x90\xbe",
        "\xf0\x9f\x8c\x8b" => "",
        "\xf0\x9f\x8c\x8c" => "\xee\x91\x8b",
        "\xf0\x9f\x8c\x8d" => "",
        "\xf0\x9f\x8c\x8e" => "",
        "\xf0\x9f\x8c\x8f" => "",
        "\xf0\x9f\x8c\x90" => "",
        "\xf0\x9f\x8c\x91" => "",
        "\xf0\x9f\x8c\x92" => "",
        "\xf0\x9f\x8c\x93" => "\xee\x81\x8c",
        "\xf0\x9f\x8c\x94" => "\xee\x81\x8c",
        "\xf0\x9f\x8c\x95" => "",
        "\xf0\x9f\x8c\x96" => "",
        "\xf0\x9f\x8c\x97" => "",
        "\xf0\x9f\x8c\x98" => "",
        "\xf0\x9f\x8c\x99" => "\xee\x81\x8c",
        "\xf0\x9f\x8c\x9a" => "",
        "\xf0\x9f\x8c\x9b" => "\xee\x81\x8c",
        "\xf0\x9f\x8c\x9c" => "",
        "\xf0\x9f\x8c\x9d" => "",
        "\xf0\x9f\x8c\x9e" => "",
        "\xf0\x9f\x8c\x9f" => "\xee\x8c\xb5",
        "\xf0\x9f\x8c\xa0" => "",
        "\xf0\x9f\x8c\xa1" => "",
        "\xf0\x9f\x8c\xa4" => "",
        "\xf0\x9f\x8c\xa5" => "",
        "\xf0\x9f\x8c\xa6" => "",
        "\xf0\x9f\x8c\xa7" => "",
        "\xf0\x9f\x8c\xa8" => "",
        "\xf0\x9f\x8c\xa9" => "",
        "\xf0\x9f\x8c\xaa" => "",
        "\xf0\x9f\x8c\xab" => "",
        "\xf0\x9f\x8c\xac" => "",
        "\xf0\x9f\x8c\xad" => "",
        "\xf0\x9f\x8c\xae" => "",
        "\xf0\x9f\x8c\xaf" => "",
        "\xf0\x9f\x8c\xb0" => "",
        "\xf0\x9f\x8c\xb1" => "\xee\x84\x90",
        "\xf0\x9f\x8c\xb2" => "",
        "\xf0\x9f\x8c\xb3" => "",
        "\xf0\x9f\x8c\xb4" => "\xee\x8c\x87",
        "\xf0\x9f\x8c\xb5" => "\xee\x8c\x88",
        "\xf0\x9f\x8c\xb6" => "",
        "\xf0\x9f\x8c\xb7" => "\xee\x8c\x84",
        "\xf0\x9f\x8c\xb8" => "\xee\x80\xb0",
        "\xf0\x9f\x8c\xb9" => "\xee\x80\xb2",
        "\xf0\x9f\x8c\xba" => "\xee\x8c\x83",
        "\xf0\x9f\x8c\xbb" => "\xee\x8c\x85",
        "\xf0\x9f\x8c\xbc" => "\xee\x8c\x85",
        "\xf0\x9f\x8c\xbd" => "",
        "\xf0\x9f\x8c\xbe" => "\xee\x91\x84",
        "\xf0\x9f\x8c\xbf" => "\xee\x84\x90",
        "\xf0\x9f\x8d\x80" => "\xee\x84\x90",
        "\xf0\x9f\x8d\x81" => "\xee\x84\x98",
        "\xf0\x9f\x8d\x82" => "\xee\x84\x99",
        "\xf0\x9f\x8d\x83" => "\xee\x91\x87",
        "\xf0\x9f\x8d\x84" => "",
        "\xf0\x9f\x8d\x85" => "\xee\x8d\x89",
        "\xf0\x9f\x8d\x86" => "\xee\x8d\x8a",
        "\xf0\x9f\x8d\x87" => "",
        "\xf0\x9f\x8d\x88" => "",
        "\xf0\x9f\x8d\x89" => "\xee\x8d\x88",
        "\xf0\x9f\x8d\x8a" => "\xee\x8d\x86",
        "\xf0\x9f\x8d\x8b" => "",
        "\xf0\x9f\x8d\x8c" => "",
        "\xf0\x9f\x8d\x8d" => "",
        "\xf0\x9f\x8d\x8e" => "\xee\x8d\x85",
        "\xf0\x9f\x8d\x8f" => "\xee\x8d\x85",
        "\xf0\x9f\x8d\x90" => "",
        "\xf0\x9f\x8d\x91" => "",
        "\xf0\x9f\x8d\x92" => "",
        "\xf0\x9f\x8d\x93" => "\xee\x8d\x87",
        "\xf0\x9f\x8d\x94" => "\xee\x84\xa0",
        "\xf0\x9f\x8d\x95" => "",
        "\xf0\x9f\x8d\x96" => "",
        "\xf0\x9f\x8d\x97" => "",
        "\xf0\x9f\x8d\x98" => "\xee\x8c\xbd",
        "\xf0\x9f\x8d\x99" => "\xee\x8d\x82",
        "\xf0\x9f\x8d\x9a" => "\xee\x8c\xbe",
        "\xf0\x9f\x8d\x9b" => "\xee\x8d\x81",
        "\xf0\x9f\x8d\x9c" => "\xee\x8d\x80",
        "\xf0\x9f\x8d\x9d" => "\xee\x8c\xbf",
        "\xf0\x9f\x8d\x9e" => "\xee\x8c\xb9",
        "\xf0\x9f\x8d\x9f" => "\xee\x8c\xbb",
        "\xf0\x9f\x8d\xa0" => "",
        "\xf0\x9f\x8d\xa1" => "\xee\x8c\xbc",
        "\xf0\x9f\x8d\xa2" => "\xee\x8d\x83",
        "\xf0\x9f\x8d\xa3" => "\xee\x8d\x84",
        "\xf0\x9f\x8d\xa4" => "",
        "\xf0\x9f\x8d\xa5" => "",
        "\xf0\x9f\x8d\xa6" => "\xee\x8c\xba",
        "\xf0\x9f\x8d\xa7" => "\xee\x90\xbf",
        "\xf0\x9f\x8d\xa8" => "",
        "\xf0\x9f\x8d\xa9" => "",
        "\xf0\x9f\x8d\xaa" => "",
        "\xf0\x9f\x8d\xab" => "",
        "\xf0\x9f\x8d\xac" => "",
        "\xf0\x9f\x8d\xad" => "",
        "\xf0\x9f\x8d\xae" => "",
        "\xf0\x9f\x8d\xaf" => "",
        "\xf0\x9f\x8d\xb0" => "\xee\x81\x86",
        "\xf0\x9f\x8d\xb1" => "\xee\x8d\x8c",
        "\xf0\x9f\x8d\xb2" => "\xee\x8d\x8d",
        "\xf0\x9f\x8d\xb3" => "\xee\x85\x87",
        "\xf0\x9f\x8d\xb4" => "\xee\x81\x83",
        "\xf0\x9f\x8d\xb5" => "\xee\x8c\xb8",
        "\xf0\x9f\x8d\xb6" => "\xee\x8c\x8b",
        "\xf0\x9f\x8d\xb7" => "\xee\x81\x84",
        "\xf0\x9f\x8d\xb8" => "\xee\x81\x84",
        "\xf0\x9f\x8d\xb9" => "\xee\x81\x84",
        "\xf0\x9f\x8d\xba" => "\xee\x81\x87",
        "\xf0\x9f\x8d\xbb" => "\xee\x8c\x8c",
        "\xf0\x9f\x8d\xbc" => "",
        "\xf0\x9f\x8d\xbd" => "",
        "\xf0\x9f\x8d\xbe" => "",
        "\xf0\x9f\x8d\xbf" => "",
        "\xf0\x9f\x8e\x80" => "\xee\x8c\x94",
        "\xf0\x9f\x8e\x81" => "\xee\x84\x92",
        "\xf0\x9f\x8e\x82" => "\xee\x8d\x8b",
        "\xf0\x9f\x8e\x83" => "\xee\x91\x85",
        "\xf0\x9f\x8e\x84" => "\xee\x80\xb3",
        "\xf0\x9f\x8e\x85" => "\xee\x91\x88",
        "\xf0\x9f\x8e\x86" => "\xee\x84\x97",
        "\xf0\x9f\x8e\x87" => "\xee\x91\x80",
        "\xf0\x9f\x8e\x88" => "\xee\x8c\x90",
        "\xf0\x9f\x8e\x89" => "\xee\x8c\x92",
        "\xf0\x9f\x8e\x8a" => "",
        "\xf0\x9f\x8e\x8b" => "",
        "\xf0\x9f\x8e\x8c" => "\xee\x85\x83",
        "\xf0\x9f\x8e\x8d" => "\xee\x90\xb6",
        "\xf0\x9f\x8e\x8e" => "\xee\x90\xb8",
        "\xf0\x9f\x8e\x8f" => "\xee\x90\xbb",
        "\xf0\x9f\x8e\x90" => "\xee\x91\x82",
        "\xf0\x9f\x8e\x91" => "\xee\x91\x86",
        "\xf0\x9f\x8e\x92" => "\xee\x90\xba",
        "\xf0\x9f\x8e\x93" => "\xee\x90\xb9",
        "\xf0\x9f\x8e\x96" => "",
        "\xf0\x9f\x8e\x97" => "",
        "\xf0\x9f\x8e\x99" => "",
        "\xf0\x9f\x8e\x9a" => "",
        "\xf0\x9f\x8e\x9b" => "",
        "\xf0\x9f\x8e\x9e" => "",
        "\xf0\x9f\x8e\x9f" => "",
        "\xf0\x9f\x8e\xa0" => "",
        "\xf0\x9f\x8e\xa1" => "\xee\x84\xa4",
        "\xf0\x9f\x8e\xa2" => "\xee\x90\xb3",
        "\xf0\x9f\x8e\xa3" => "\xee\x80\x99",
        "\xf0\x9f\x8e\xa4" => "\xee\x80\xbc",
        "\xf0\x9f\x8e\xa5" => "\xee\x80\xbd",
        "\xf0\x9f\x8e\xa6" => "\xee\x94\x87",
        "\xf0\x9f\x8e\xa7" => "\xee\x8c\x8a",
        "\xf0\x9f\x8e\xa8" => "\xee\x94\x82",
        "\xf0\x9f\x8e\xa9" => "\xee\x94\x83",
        "\xf0\x9f\x8e\xaa" => "",
        "\xf0\x9f\x8e\xab" => "\xee\x84\xa5",
        "\xf0\x9f\x8e\xac" => "\xee\x8c\xa4",
        "\xf0\x9f\x8e\xad" => "\xee\x94\x83",
        "\xf0\x9f\x8e\xae" => "",
        "\xf0\x9f\x8e\xaf" => "\xee\x84\xb0",
        "\xf0\x9f\x8e\xb0" => "\xee\x84\xb3",
        "\xf0\x9f\x8e\xb1" => "\xee\x90\xac",
        "\xf0\x9f\x8e\xb2" => "",
        "\xf0\x9f\x8e\xb3" => "",
        "\xf0\x9f\x8e\xb4" => "",
        "\xf0\x9f\x8e\xb5" => "\xee\x80\xbe",
        "\xf0\x9f\x8e\xb6" => "\xee\x8c\xa6",
        "\xf0\x9f\x8e\xb7" => "\xee\x81\x80",
        "\xf0\x9f\x8e\xb8" => "\xee\x81\x81",
        "\xf0\x9f\x8e\xb9" => "",
        "\xf0\x9f\x8e\xba" => "\xee\x81\x82",
        "\xf0\x9f\x8e\xbb" => "",
        "\xf0\x9f\x8e\xbc" => "\xee\x8c\xa6",
        "\xf0\x9f\x8e\xbd" => "",
        "\xf0\x9f\x8e\xbe" => "\xee\x80\x95",
        "\xf0\x9f\x8e\xbf" => "\xee\x80\x93",
        "\xf0\x9f\x8f\x80" => "\xee\x90\xaa",
        "\xf0\x9f\x8f\x81" => "\xee\x84\xb2",
        "\xf0\x9f\x8f\x82" => "",
        "\xf0\x9f\x8f\x83" => "\xee\x84\x95",
        "\xf0\x9f\x8f\x84" => "\xee\x80\x97",
        "\xf0\x9f\x8f\x85" => "",
        "\xf0\x9f\x8f\x86" => "\xee\x84\xb1",
        "\xf0\x9f\x8f\x87" => "",
        "\xf0\x9f\x8f\x88" => "\xee\x90\xab",
        "\xf0\x9f\x8f\x89" => "",
        "\xf0\x9f\x8f\x8a" => "\xee\x90\xad",
        "\xf0\x9f\x8f\x8b" => "",
        "\xf0\x9f\x8f\x8c" => "",
        "\xf0\x9f\x8f\x8d" => "",
        "\xf0\x9f\x8f\x8e" => "",
        "\xf0\x9f\x8f\x8f" => "",
        "\xf0\x9f\x8f\x90" => "",
        "\xf0\x9f\x8f\x91" => "",
        "\xf0\x9f\x8f\x92" => "",
        "\xf0\x9f\x8f\x93" => "",
        "\xf0\x9f\x8f\x94" => "",
        "\xf0\x9f\x8f\x95" => "",
        "\xf0\x9f\x8f\x96" => "",
        "\xf0\x9f\x8f\x97" => "",
        "\xf0\x9f\x8f\x98" => "",
        "\xf0\x9f\x8f\x99" => "",
        "\xf0\x9f\x8f\x9a" => "",
        "\xf0\x9f\x8f\x9b" => "",
        "\xf0\x9f\x8f\x9c" => "",
        "\xf0\x9f\x8f\x9d" => "",
        "\xf0\x9f\x8f\x9e" => "",
        "\xf0\x9f\x8f\x9f" => "",
        "\xf0\x9f\x8f\xa0" => "\xee\x80\xb6",
        "\xf0\x9f\x8f\xa1" => "\xee\x80\xb6",
        "\xf0\x9f\x8f\xa2" => "\xee\x80\xb8",
        "\xf0\x9f\x8f\xa3" => "\xee\x85\x93",
        "\xf0\x9f\x8f\xa4" => "",
        "\xf0\x9f\x8f\xa5" => "\xee\x85\x95",
        "\xf0\x9f\x8f\xa6" => "\xee\x85\x8d",
        "\xf0\x9f\x8f\xa7" => "\xee\x85\x94",
        "\xf0\x9f\x8f\xa8" => "\xee\x85\x98",
        "\xf0\x9f\x8f\xa9" => "\xee\x94\x81",
        "\xf0\x9f\x8f\xaa" => "\xee\x85\x96",
        "\xf0\x9f\x8f\xab" => "\xee\x85\x97",
        "\xf0\x9f\x8f\xac" => "\xee\x94\x84",
        "\xf0\x9f\x8f\xad" => "\xee\x94\x88",
        "\xf0\x9f\x8f\xae" => "\xee\x8c\x8b",
        "\xf0\x9f\x8f\xaf" => "\xee\x94\x85",
        "\xf0\x9f\x8f\xb0" => "\xee\x94\x86",
        "\xf0\x9f\x8f\xb3" => "",
        "\xf0\x9f\x8f\xb4" => "",
        "\xf0\x9f\x8f\xb5" => "",
        "\xf0\x9f\x8f\xb7" => "",
        "\xf0\x9f\x8f\xb8" => "",
        "\xf0\x9f\x8f\xb9" => "",
        "\xf0\x9f\x8f\xba" => "",
        "\xf0\x9f\x8f\xbb" => "",
        "\xf0\x9f\x8f\xbc" => "",
        "\xf0\x9f\x8f\xbd" => "",
        "\xf0\x9f\x8f\xbe" => "",
        "\xf0\x9f\x8f\xbf" => "",
        "\xf0\x9f\x90\x80" => "",
        "\xf0\x9f\x90\x81" => "",
        "\xf0\x9f\x90\x82" => "",
        "\xf0\x9f\x90\x83" => "",
        "\xf0\x9f\x90\x84" => "",
        "\xf0\x9f\x90\x85" => "",
        "\xf0\x9f\x90\x86" => "",
        "\xf0\x9f\x90\x87" => "",
        "\xf0\x9f\x90\x88" => "",
        "\xf0\x9f\x90\x89" => "",
        "\xf0\x9f\x90\x8a" => "",
        "\xf0\x9f\x90\x8b" => "",
        "\xf0\x9f\x90\x8c" => "",
        "\xf0\x9f\x90\x8d" => "\xee\x94\xad",
        "\xf0\x9f\x90\x8e" => "\xee\x84\xb4",
        "\xf0\x9f\x90\x8f" => "",
        "\xf0\x9f\x90\x90" => "",
        "\xf0\x9f\x90\x91" => "\xee\x94\xa9",
        "\xf0\x9f\x90\x92" => "\xee\x94\xa8",
        "\xf0\x9f\x90\x93" => "",
        "\xf0\x9f\x90\x94" => "\xee\x94\xae",
        "\xf0\x9f\x90\x95" => "",
        "\xf0\x9f\x90\x96" => "",
        "\xf0\x9f\x90\x97" => "\xee\x94\xaf",
        "\xf0\x9f\x90\x98" => "\xee\x94\xa6",
        "\xf0\x9f\x90\x99" => "\xee\x84\x8a",
        "\xf0\x9f\x90\x9a" => "\xee\x91\x81",
        "\xf0\x9f\x90\x9b" => "\xee\x94\xa5",
        "\xf0\x9f\x90\x9c" => "",
        "\xf0\x9f\x90\x9d" => "",
        "\xf0\x9f\x90\x9e" => "",
        "\xf0\x9f\x90\x9f" => "\xee\x80\x99",
        "\xf0\x9f\x90\xa0" => "\xee\x94\xa2",
        "\xf0\x9f\x90\xa1" => "\xee\x80\x99",
        "\xf0\x9f\x90\xa2" => "",
        "\xf0\x9f\x90\xa3" => "\xee\x94\xa3",
        "\xf0\x9f\x90\xa4" => "\xee\x94\xa3",
        "\xf0\x9f\x90\xa5" => "\xee\x94\xa3",
        "\xf0\x9f\x90\xa6" => "\xee\x94\xa1",
        "\xf0\x9f\x90\xa7" => "\xee\x81\x95",
        "\xf0\x9f\x90\xa8" => "\xee\x94\xa7",
        "\xf0\x9f\x90\xa9" => "\xee\x81\x92",
        "\xf0\x9f\x90\xaa" => "",
        "\xf0\x9f\x90\xab" => "\xee\x94\xb0",
        "\xf0\x9f\x90\xac" => "\xee\x94\xa0",
        "\xf0\x9f\x90\xad" => "\xee\x81\x93",
        "\xf0\x9f\x90\xae" => "\xee\x94\xab",
        "\xf0\x9f\x90\xaf" => "\xee\x81\x90",
        "\xf0\x9f\x90\xb0" => "\xee\x94\xac",
        "\xf0\x9f\x90\xb1" => "\xee\x81\x8f",
        "\xf0\x9f\x90\xb2" => "",
        "\xf0\x9f\x90\xb3" => "\xee\x81\x94",
        "\xf0\x9f\x90\xb4" => "\xee\x80\x9a",
        "\xf0\x9f\x90\xb5" => "\xee\x84\x89",
        "\xf0\x9f\x90\xb6" => "\xee\x81\x92",
        "\xf0\x9f\x90\xb7" => "\xee\x84\x8b",
        "\xf0\x9f\x90\xb8" => "\xee\x94\xb1",
        "\xf0\x9f\x90\xb9" => "\xee\x94\xa4",
        "\xf0\x9f\x90\xba" => "\xee\x94\xaa",
        "\xf0\x9f\x90\xbb" => "\xee\x81\x91",
        "\xf0\x9f\x90\xbc" => "",
        "\xf0\x9f\x90\xbd" => "\xee\x84\x8b",
        "\xf0\x9f\x90\xbe" => "\xee\x94\xb6",
        "\xf0\x9f\x90\xbf" => "",
        "\xf0\x9f\x91\x80" => "\xee\x90\x99",
        "\xf0\x9f\x91\x81" => "",
        "\xf0\x9f\x91\x82" => "\xee\x90\x9b",
        "\xf0\x9f\x91\x83" => "\xee\x90\x9a",
        "\xf0\x9f\x91\x84" => "\xee\x90\x9c",
        "\xf0\x9f\x91\x85" => "\xee\x90\x89",
        "\xf0\x9f\x91\x86" => "\xee\x88\xae",
        "\xf0\x9f\x91\x87" => "\xee\x88\xaf",
        "\xf0\x9f\x91\x88" => "\xee\x88\xb0",
        "\xf0\x9f\x91\x89" => "\xee\x88\xb1",
        "\xf0\x9f\x91\x8a" => "\xee\x80\x8d",
        "\xf0\x9f\x91\x8b" => "\xee\x90\x9e",
        "\xf0\x9f\x91\x8c" => "\xee\x90\xa0",
        "\xf0\x9f\x91\x8d" => "\xee\x80\x8e",
        "\xf0\x9f\x91\x8e" => "\xee\x90\xa1",
        "\xf0\x9f\x91\x8f" => "\xee\x90\x9f",
        "\xf0\x9f\x91\x90" => "\xee\x90\xa2",
        "\xf0\x9f\x91\x91" => "\xee\x84\x8e",
        "\xf0\x9f\x91\x92" => "\xee\x8c\x98",
        "\xf0\x9f\x91\x93" => "",
        "\xf0\x9f\x91\x94" => "\xee\x8c\x82",
        "\xf0\x9f\x91\x95" => "\xee\x80\x86",
        "\xf0\x9f\x91\x96" => "",
        "\xf0\x9f\x91\x97" => "\xee\x8c\x99",
        "\xf0\x9f\x91\x98" => "\xee\x8c\xa1",
        "\xf0\x9f\x91\x99" => "\xee\x8c\xa2",
        "\xf0\x9f\x91\x9a" => "\xee\x80\x86",
        "\xf0\x9f\x91\x9b" => "",
        "\xf0\x9f\x91\x9c" => "\xee\x8c\xa3",
        "\xf0\x9f\x91\x9d" => "",
        "\xf0\x9f\x91\x9e" => "\xee\x80\x87",
        "\xf0\x9f\x91\x9f" => "\xee\x80\x87",
        "\xf0\x9f\x91\xa0" => "\xee\x84\xbe",
        "\xf0\x9f\x91\xa1" => "\xee\x8c\x9a",
        "\xf0\x9f\x91\xa2" => "\xee\x8c\x9b",
        "\xf0\x9f\x91\xa3" => "\xee\x94\xb6",
        "\xf0\x9f\x91\xa4" => "",
        "\xf0\x9f\x91\xa5" => "",
        "\xf0\x9f\x91\xa6" => "\xee\x80\x81",
        "\xf0\x9f\x91\xa7" => "\xee\x80\x82",
        "\xf0\x9f\x91\xa8" => "\xee\x80\x84",
        "\xf0\x9f\x91\xa9" => "\xee\x80\x85",
        "\xf0\x9f\x91\xaa" => "",
        "\xf0\x9f\x91\xab" => "\xee\x90\xa8",
        "\xf0\x9f\x91\xac" => "",
        "\xf0\x9f\x91\xad" => "",
        "\xf0\x9f\x91\xae" => "\xee\x85\x92",
        "\xf0\x9f\x91\xaf" => "\xee\x90\xa9",
        "\xf0\x9f\x91\xb0" => "",
        "\xf0\x9f\x91\xb1" => "\xee\x94\x95",
        "\xf0\x9f\x91\xb2" => "\xee\x94\x96",
        "\xf0\x9f\x91\xb3" => "\xee\x94\x97",
        "\xf0\x9f\x91\xb4" => "\xee\x94\x98",
        "\xf0\x9f\x91\xb5" => "\xee\x94\x99",
        "\xf0\x9f\x91\xb6" => "\xee\x94\x9a",
        "\xf0\x9f\x91\xb7" => "\xee\x94\x9b",
        "\xf0\x9f\x91\xb8" => "\xee\x94\x9c",
        "\xf0\x9f\x91\xb9" => "",
        "\xf0\x9f\x91\xba" => "",
        "\xf0\x9f\x91\xbb" => "\xee\x84\x9b",
        "\xf0\x9f\x91\xbc" => "\xee\x81\x8e",
        "\xf0\x9f\x91\xbd" => "\xee\x84\x8c",
        "\xf0\x9f\x91\xbe" => "\xee\x84\xab",
        "\xf0\x9f\x91\xbf" => "\xee\x84\x9a",
        "\xf0\x9f\x92\x80" => "\xee\x84\x9c",
        "\xf0\x9f\x92\x81" => "\xee\x89\x93",
        "\xf0\x9f\x92\x82" => "\xee\x94\x9e",
        "\xf0\x9f\x92\x83" => "\xee\x94\x9f",
        "\xf0\x9f\x92\x84" => "\xee\x8c\x9c",
        "\xf0\x9f\x92\x85" => "\xee\x8c\x9d",
        "\xf0\x9f\x92\x86" => "\xee\x8c\x9e",
        "\xf0\x9f\x92\x87" => "\xee\x8c\x9f",
        "\xf0\x9f\x92\x88" => "\xee\x8c\xa0",
        "\xf0\x9f\x92\x89" => "\xee\x84\xbb",
        "\xf0\x9f\x92\x8a" => "\xee\x8c\x8f",
        "\xf0\x9f\x92\x8b" => "\xee\x80\x83",
        "\xf0\x9f\x92\x8c" => "\xee\x84\x83\xee\x8c\xa8",
        "\xf0\x9f\x92\x8d" => "\xee\x80\xb4",
        "\xf0\x9f\x92\x8e" => "\xee\x80\xb5",
        "\xf0\x9f\x92\x8f" => "\xee\x84\x91",
        "\xf0\x9f\x92\x90" => "\xee\x8c\x86",
        "\xf0\x9f\x92\x91" => "\xee\x90\xa5",
        "\xf0\x9f\x92\x92" => "\xee\x90\xbd",
        "\xf0\x9f\x92\x93" => "\xee\x8c\xa7",
        "\xf0\x9f\x92\x94" => "\xee\x80\xa3",
        "\xf0\x9f\x92\x95" => "\xee\x8c\xa7",
        "\xf0\x9f\x92\x96" => "\xee\x8c\xa7",
        "\xf0\x9f\x92\x97" => "\xee\x8c\xa8",
        "\xf0\x9f\x92\x98" => "\xee\x8c\xa9",
        "\xf0\x9f\x92\x99" => "\xee\x8c\xaa",
        "\xf0\x9f\x92\x9a" => "\xee\x8c\xab",
        "\xf0\x9f\x92\x9b" => "\xee\x8c\xac",
        "\xf0\x9f\x92\x9c" => "\xee\x8c\xad",
        "\xf0\x9f\x92\x9d" => "\xee\x90\xb7",
        "\xf0\x9f\x92\x9e" => "\xee\x8c\xa7",
        "\xf0\x9f\x92\x9f" => "\xee\x88\x84",
        "\xf0\x9f\x92\xa0" => "",
        "\xf0\x9f\x92\xa1" => "\xee\x84\x8f",
        "\xf0\x9f\x92\xa2" => "\xee\x8c\xb4",
        "\xf0\x9f\x92\xa3" => "\xee\x8c\x91",
        "\xf0\x9f\x92\xa4" => "\xee\x84\xbc",
        "\xf0\x9f\x92\xa5" => "",
        "\xf0\x9f\x92\xa6" => "\xee\x8c\xb1",
        "\xf0\x9f\x92\xa7" => "\xee\x8c\xb1",
        "\xf0\x9f\x92\xa8" => "\xee\x8c\xb0",
        "\xf0\x9f\x92\xa9" => "\xee\x81\x9a",
        "\xf0\x9f\x92\xaa" => "\xee\x85\x8c",
        "\xf0\x9f\x92\xab" => "\xee\x90\x87",
        "\xf0\x9f\x92\xac" => "",
        "\xf0\x9f\x92\xad" => "",
        "\xf0\x9f\x92\xae" => "",
        "\xf0\x9f\x92\xaf" => "",
        "\xf0\x9f\x92\xb0" => "\xee\x84\xaf",
        "\xf0\x9f\x92\xb1" => "\xee\x85\x89",
        "\xf0\x9f\x92\xb2" => "\xee\x84\xaf",
        "\xf0\x9f\x92\xb3" => "",
        "\xf0\x9f\x92\xb4" => "",
        "\xf0\x9f\x92\xb5" => "\xee\x84\xaf",
        "\xf0\x9f\x92\xb6" => "",
        "\xf0\x9f\x92\xb7" => "",
        "\xf0\x9f\x92\xb8" => "",
        "\xf0\x9f\x92\xb9" => "\xee\x85\x8a",
        "\xf0\x9f\x92\xba" => "\xee\x84\x9f",
        "\xf0\x9f\x92\xbb" => "\xee\x80\x8c",
        "\xf0\x9f\x92\xbc" => "\xee\x84\x9e",
        "\xf0\x9f\x92\xbd" => "\xee\x8c\x96",
        "\xf0\x9f\x92\xbe" => "\xee\x8c\x96",
        "\xf0\x9f\x92\xbf" => "\xee\x84\xa6",
        "\xf0\x9f\x93\x80" => "\xee\x84\xa7",
        "\xf0\x9f\x93\x81" => "",
        "\xf0\x9f\x93\x82" => "",
        "\xf0\x9f\x93\x83" => "\xee\x8c\x81",
        "\xf0\x9f\x93\x84" => "\xee\x8c\x81",
        "\xf0\x9f\x93\x85" => "",
        "\xf0\x9f\x93\x86" => "",
        "\xf0\x9f\x93\x87" => "\xee\x85\x88",
        "\xf0\x9f\x93\x88" => "\xee\x85\x8a",
        "\xf0\x9f\x93\x89" => "",
        "\xf0\x9f\x93\x8a" => "\xee\x85\x8a",
        "\xf0\x9f\x93\x8b" => "\xee\x8c\x81",
        "\xf0\x9f\x93\x8c" => "",
        "\xf0\x9f\x93\x8d" => "",
        "\xf0\x9f\x93\x8e" => "",
        "\xf0\x9f\x93\x8f" => "",
        "\xf0\x9f\x93\x90" => "",
        "\xf0\x9f\x93\x91" => "\xee\x8c\x81",
        "\xf0\x9f\x93\x92" => "\xee\x85\x88",
        "\xf0\x9f\x93\x93" => "\xee\x85\x88",
        "\xf0\x9f\x93\x94" => "\xee\x85\x88",
        "\xf0\x9f\x93\x95" => "\xee\x85\x88",
        "\xf0\x9f\x93\x96" => "\xee\x85\x88",
        "\xf0\x9f\x93\x97" => "\xee\x85\x88",
        "\xf0\x9f\x93\x98" => "\xee\x85\x88",
        "\xf0\x9f\x93\x99" => "\xee\x85\x88",
        "\xf0\x9f\x93\x9a" => "\xee\x85\x88",
        "\xf0\x9f\x93\x9b" => "",
        "\xf0\x9f\x93\x9c" => "",
        "\xf0\x9f\x93\x9d" => "\xee\x8c\x81",
        "\xf0\x9f\x93\x9e" => "\xee\x80\x89",
        "\xf0\x9f\x93\x9f" => "",
        "\xf0\x9f\x93\xa0" => "\xee\x80\x8b",
        "\xf0\x9f\x93\xa1" => "\xee\x85\x8b",
        "\xf0\x9f\x93\xa2" => "\xee\x85\x82",
        "\xf0\x9f\x93\xa3" => "\xee\x8c\x97",
        "\xf0\x9f\x93\xa4" => "",
        "\xf0\x9f\x93\xa5" => "",
        "\xf0\x9f\x93\xa6" => "\xee\x84\x92",
        "\xf0\x9f\x93\xa7" => "\xee\x84\x83",
        "\xf0\x9f\x93\xa8" => "\xee\x84\x83",
        "\xf0\x9f\x93\xa9" => "\xee\x84\x83",
        "\xf0\x9f\x93\xaa" => "\xee\x84\x81",
        "\xf0\x9f\x93\xab" => "\xee\x84\x81",
        "\xf0\x9f\x93\xac" => "",
        "\xf0\x9f\x93\xad" => "",
        "\xf0\x9f\x93\xae" => "\xee\x84\x82",
        "\xf0\x9f\x93\xaf" => "",
        "\xf0\x9f\x93\xb0" => "",
        "\xf0\x9f\x93\xb1" => "\xee\x80\x8a",
        "\xf0\x9f\x93\xb2" => "\xee\x84\x84",
        "\xf0\x9f\x93\xb3" => "\xee\x89\x90",
        "\xf0\x9f\x93\xb4" => "\xee\x89\x91",
        "\xf0\x9f\x93\xb5" => "",
        "\xf0\x9f\x93\xb6" => "\xee\x88\x8b",
        "\xf0\x9f\x93\xb7" => "\xee\x80\x88",
        "\xf0\x9f\x93\xb8" => "",
        "\xf0\x9f\x93\xb9" => "\xee\x80\xbd",
        "\xf0\x9f\x93\xba" => "\xee\x84\xaa",
        "\xf0\x9f\x93\xbb" => "\xee\x84\xa8",
        "\xf0\x9f\x93\xbc" => "\xee\x84\xa9",
        "\xf0\x9f\x93\xbd" => "",
        "\xf0\x9f\x93\xbf" => "",
        "\xf0\x9f\x94\x80" => "",
        "\xf0\x9f\x94\x81" => "",
        "\xf0\x9f\x94\x82" => "",
        "\xf0\x9f\x94\x83" => "",
        "\xf0\x9f\x94\x84" => "",
        "\xf0\x9f\x94\x85" => "",
        "\xf0\x9f\x94\x86" => "",
        "\xf0\x9f\x94\x87" => "",
        "\xf0\x9f\x94\x88" => "",
        "\xf0\x9f\x94\x89" => "",
        "\xf0\x9f\x94\x8a" => "\xee\x85\x81",
        "\xf0\x9f\x94\x8b" => "",
        "\xf0\x9f\x94\x8c" => "",
        "\xf0\x9f\x94\x8d" => "\xee\x84\x94",
        "\xf0\x9f\x94\x8e" => "\xee\x84\x94",
        "\xf0\x9f\x94\x8f" => "\xee\x85\x84",
        "\xf0\x9f\x94\x90" => "\xee\x85\x84",
        "\xf0\x9f\x94\x91" => "\xee\x80\xbf",
        "\xf0\x9f\x94\x92" => "\xee\x85\x84",
        "\xf0\x9f\x94\x93" => "\xee\x85\x85",
        "\xf0\x9f\x94\x94" => "\xee\x8c\xa5",
        "\xf0\x9f\x94\x95" => "",
        "\xf0\x9f\x94\x96" => "",
        "\xf0\x9f\x94\x97" => "",
        "\xf0\x9f\x94\x98" => "",
        "\xf0\x9f\x94\x99" => "\xee\x88\xb5",
        "\xf0\x9f\x94\x9a" => "",
        "\xf0\x9f\x94\x9b" => "",
        "\xf0\x9f\x94\x9c" => "",
        "\xf0\x9f\x94\x9d" => "\xee\x89\x8c",
        "\xf0\x9f\x94\x9e" => "\xee\x88\x87",
        "\xf0\x9f\x94\x9f" => "",
        "\xf0\x9f\x94\xa0" => "",
        "\xf0\x9f\x94\xa1" => "",
        "\xf0\x9f\x94\xa2" => "",
        "\xf0\x9f\x94\xa3" => "",
        "\xf0\x9f\x94\xa4" => "",
        "\xf0\x9f\x94\xa5" => "\xee\x84\x9d",
        "\xf0\x9f\x94\xa6" => "",
        "\xf0\x9f\x94\xa7" => "",
        "\xf0\x9f\x94\xa8" => "\xee\x84\x96",
        "\xf0\x9f\x94\xa9" => "",
        "\xf0\x9f\x94\xaa" => "",
        "\xf0\x9f\x94\xab" => "\xee\x84\x93",
        "\xf0\x9f\x94\xac" => "",
        "\xf0\x9f\x94\xad" => "",
        "\xf0\x9f\x94\xae" => "\xee\x88\xbe",
        "\xf0\x9f\x94\xaf" => "\xee\x88\xbe",
        "\xf0\x9f\x94\xb0" => "\xee\x88\x89",
        "\xf0\x9f\x94\xb1" => "\xee\x80\xb1",
        "\xf0\x9f\x94\xb2" => "\xee\x88\x9a",
        "\xf0\x9f\x94\xb3" => "\xee\x88\x9b",
        "\xf0\x9f\x94\xb4" => "\xee\x88\x99",
        "\xf0\x9f\x94\xb5" => "\xee\x88\x9a",
        "\xf0\x9f\x94\xb6" => "\xee\x88\x9b",
        "\xf0\x9f\x94\xb7" => "\xee\x88\x9b",
        "\xf0\x9f\x94\xb8" => "\xee\x88\x9b",
        "\xf0\x9f\x94\xb9" => "\xee\x88\x9b",
        "\xf0\x9f\x94\xba" => "",
        "\xf0\x9f\x94\xbb" => "",
        "\xf0\x9f\x94\xbc" => "",
        "\xf0\x9f\x94\xbd" => "",
        "\xf0\x9f\x95\x89" => "",
        "\xf0\x9f\x95\x8a" => "",
        "\xf0\x9f\x95\x8b" => "",
        "\xf0\x9f\x95\x8c" => "",
        "\xf0\x9f\x95\x8d" => "",
        "\xf0\x9f\x95\x8e" => "",
        "\xf0\x9f\x95\x90" => "\xee\x80\xa4",
        "\xf0\x9f\x95\x91" => "\xee\x80\xa5",
        "\xf0\x9f\x95\x92" => "\xee\x80\xa6",
        "\xf0\x9f\x95\x93" => "\xee\x80\xa7",
        "\xf0\x9f\x95\x94" => "\xee\x80\xa8",
        "\xf0\x9f\x95\x95" => "\xee\x80\xa9",
        "\xf0\x9f\x95\x96" => "\xee\x80\xaa",
        "\xf0\x9f\x95\x97" => "\xee\x80\xab",
        "\xf0\x9f\x95\x98" => "\xee\x80\xac",
        "\xf0\x9f\x95\x99" => "\xee\x80\xad",
        "\xf0\x9f\x95\x9a" => "\xee\x80\xae",
        "\xf0\x9f\x95\x9b" => "\xee\x80\xaf",
        "\xf0\x9f\x95\x9c" => "",
        "\xf0\x9f\x95\x9d" => "",
        "\xf0\x9f\x95\x9e" => "",
        "\xf0\x9f\x95\x9f" => "",
        "\xf0\x9f\x95\xa0" => "",
        "\xf0\x9f\x95\xa1" => "",
        "\xf0\x9f\x95\xa2" => "",
        "\xf0\x9f\x95\xa3" => "",
        "\xf0\x9f\x95\xa4" => "",
        "\xf0\x9f\x95\xa5" => "",
        "\xf0\x9f\x95\xa6" => "",
        "\xf0\x9f\x95\xa7" => "",
        "\xf0\x9f\x95\xaf" => "",
        "\xf0\x9f\x95\xb0" => "",
        "\xf0\x9f\x95\xb3" => "",
        "\xf0\x9f\x95\xb4" => "",
        "\xf0\x9f\x95\xb5" => "",
        "\xf0\x9f\x95\xb6" => "",
        "\xf0\x9f\x95\xb7" => "",
        "\xf0\x9f\x95\xb8" => "",
        "\xf0\x9f\x95\xb9" => "",
        "\xf0\x9f\x96\x87" => "",
        "\xf0\x9f\x96\x8a" => "",
        "\xf0\x9f\x96\x8b" => "",
        "\xf0\x9f\x96\x8c" => "",
        "\xf0\x9f\x96\x8d" => "",
        "\xf0\x9f\x96\x90" => "",
        "\xf0\x9f\x96\x95" => "",
        "\xf0\x9f\x96\x96" => "",
        "\xf0\x9f\x96\xa5" => "",
        "\xf0\x9f\x96\xa8" => "",
        "\xf0\x9f\x96\xb1" => "",
        "\xf0\x9f\x96\xb2" => "",
        "\xf0\x9f\x96\xbc" => "",
        "\xf0\x9f\x97\x82" => "",
        "\xf0\x9f\x97\x83" => "",
        "\xf0\x9f\x97\x84" => "",
        "\xf0\x9f\x97\x91" => "",
        "\xf0\x9f\x97\x92" => "",
        "\xf0\x9f\x97\x93" => "",
        "\xf0\x9f\x97\x9c" => "",
        "\xf0\x9f\x97\x9d" => "",
        "\xf0\x9f\x97\x9e" => "",
        "\xf0\x9f\x97\xa1" => "",
        "\xf0\x9f\x97\xa3" => "",
        "\xf0\x9f\x97\xa8" => "",
        "\xf0\x9f\x97\xaf" => "",
        "\xf0\x9f\x97\xb3" => "",
        "\xf0\x9f\x97\xba" => "",
        "\xf0\x9f\x97\xbb" => "\xee\x80\xbb",
        "\xf0\x9f\x97\xbc" => "\xee\x94\x89",
        "\xf0\x9f\x97\xbd" => "\xee\x94\x9d",
        "\xf0\x9f\x97\xbe" => "",
        "\xf0\x9f\x97\xbf" => "",
        "\xf0\x9f\x98\x80" => "",
        "\xf0\x9f\x98\x81" => "\xee\x90\x84",
        "\xf0\x9f\x98\x82" => "\xee\x90\x92",
        "\xf0\x9f\x98\x83" => "\xee\x81\x97",
        "\xf0\x9f\x98\x84" => "\xee\x90\x95",
        "\xf0\x9f\x98\x85" => "\xee\x90\x95\xee\x8c\xb1",
        "\xf0\x9f\x98\x86" => "\xee\x90\x8a",
        "\xf0\x9f\x98\x87" => "",
        "\xf0\x9f\x98\x88" => "",
        "\xf0\x9f\x98\x89" => "\xee\x90\x85",
        "\xf0\x9f\x98\x8a" => "\xee\x81\x96",
        "\xf0\x9f\x98\x8b" => "\xee\x81\x96",
        "\xf0\x9f\x98\x8c" => "\xee\x90\x8a",
        "\xf0\x9f\x98\x8d" => "\xee\x84\x86",
        "\xf0\x9f\x98\x8e" => "",
        "\xf0\x9f\x98\x8f" => "\xee\x90\x82",
        "\xf0\x9f\x98\x90" => "",
        "\xf0\x9f\x98\x91" => "",
        "\xf0\x9f\x98\x92" => "\xee\x90\x8e",
        "\xf0\x9f\x98\x93" => "\xee\x84\x88",
        "\xf0\x9f\x98\x94" => "\xee\x90\x83",
        "\xf0\x9f\x98\x95" => "",
        "\xf0\x9f\x98\x96" => "\xee\x90\x87",
        "\xf0\x9f\x98\x97" => "",
        "\xf0\x9f\x98\x98" => "\xee\x90\x98",
        "\xf0\x9f\x98\x99" => "",
        "\xf0\x9f\x98\x9a" => "\xee\x90\x97",
        "\xf0\x9f\x98\x9b" => "",
        "\xf0\x9f\x98\x9c" => "\xee\x84\x85",
        "\xf0\x9f\x98\x9d" => "\xee\x90\x89",
        "\xf0\x9f\x98\x9e" => "\xee\x81\x98",
        "\xf0\x9f\x98\x9f" => "",
        "\xf0\x9f\x98\xa0" => "\xee\x81\x99",
        "\xf0\x9f\x98\xa1" => "\xee\x90\x96",
        "\xf0\x9f\x98\xa2" => "\xee\x90\x93",
        "\xf0\x9f\x98\xa3" => "\xee\x90\x86",
        "\xf0\x9f\x98\xa4" => "\xee\x90\x84",
        "\xf0\x9f\x98\xa5" => "\xee\x90\x81",
        "\xf0\x9f\x98\xa6" => "",
        "\xf0\x9f\x98\xa7" => "",
        "\xf0\x9f\x98\xa8" => "\xee\x90\x8b",
        "\xf0\x9f\x98\xa9" => "\xee\x90\x83",
        "\xf0\x9f\x98\xaa" => "\xee\x90\x88",
        "\xf0\x9f\x98\xab" => "\xee\x90\x86",
        "\xf0\x9f\x98\xac" => "",
        "\xf0\x9f\x98\xad" => "\xee\x90\x91",
        "\xf0\x9f\x98\xae" => "",
        "\xf0\x9f\x98\xaf" => "",
        "\xf0\x9f\x98\xb0" => "\xee\x90\x8f",
        "\xf0\x9f\x98\xb1" => "\xee\x84\x87",
        "\xf0\x9f\x98\xb2" => "\xee\x90\x90",
        "\xf0\x9f\x98\xb3" => "\xee\x90\x8d",
        "\xf0\x9f\x98\xb4" => "",
        "\xf0\x9f\x98\xb5" => "\xee\x90\x86",
        "\xf0\x9f\x98\xb6" => "",
        "\xf0\x9f\x98\xb7" => "\xee\x90\x8c",
        "\xf0\x9f\x98\xb8" => "\xee\x90\x84",
        "\xf0\x9f\x98\xb9" => "\xee\x90\x92",
        "\xf0\x9f\x98\xba" => "\xee\x81\x97",
        "\xf0\x9f\x98\xbb" => "\xee\x84\x86",
        "\xf0\x9f\x98\xbc" => "\xee\x90\x84",
        "\xf0\x9f\x98\xbd" => "\xee\x90\x98",
        "\xf0\x9f\x98\xbe" => "\xee\x90\x96",
        "\xf0\x9f\x98\xbf" => "\xee\x90\x93",
        "\xf0\x9f\x99\x80" => "\xee\x90\x83",
        "\xf0\x9f\x99\x81" => "",
        "\xf0\x9f\x99\x82" => "",
        "\xf0\x9f\x99\x83" => "",
        "\xf0\x9f\x99\x84" => "",
        "\xf0\x9f\x99\x85" => "\xee\x90\xa3",
        "\xf0\x9f\x99\x86" => "\xee\x90\xa4",
        "\xf0\x9f\x99\x87" => "\xee\x90\xa6",
        "\xf0\x9f\x99\x88" => "",
        "\xf0\x9f\x99\x89" => "",
        "\xf0\x9f\x99\x8a" => "",
        "\xf0\x9f\x99\x8b" => "\xee\x80\x92",
        "\xf0\x9f\x99\x8c" => "\xee\x90\xa7",
        "\xf0\x9f\x99\x8d" => "\xee\x90\x83",
        "\xf0\x9f\x99\x8e" => "\xee\x90\x96",
        "\xf0\x9f\x99\x8f" => "\xee\x90\x9d",
        "\xf0\x9f\x9a\x80" => "\xee\x84\x8d",
        "\xf0\x9f\x9a\x81" => "",
        "\xf0\x9f\x9a\x82" => "",
        "\xf0\x9f\x9a\x83" => "\xee\x80\x9e",
        "\xf0\x9f\x9a\x84" => "\xee\x90\xb5",
        "\xf0\x9f\x9a\x85" => "\xee\x80\x9f",
        "\xf0\x9f\x9a\x86" => "",
        "\xf0\x9f\x9a\x87" => "\xee\x90\xb4",
        "\xf0\x9f\x9a\x88" => "",
        "\xf0\x9f\x9a\x89" => "\xee\x80\xb9",
        "\xf0\x9f\x9a\x8a" => "",
        "\xf0\x9f\x9a\x8b" => "",
        "\xf0\x9f\x9a\x8c" => "\xee\x85\x99",
        "\xf0\x9f\x9a\x8d" => "",
        "\xf0\x9f\x9a\x8e" => "",
        "\xf0\x9f\x9a\x8f" => "\xee\x85\x90",
        "\xf0\x9f\x9a\x90" => "",
        "\xf0\x9f\x9a\x91" => "\xee\x90\xb1",
        "\xf0\x9f\x9a\x92" => "\xee\x90\xb0",
        "\xf0\x9f\x9a\x93" => "\xee\x90\xb2",
        "\xf0\x9f\x9a\x94" => "",
        "\xf0\x9f\x9a\x95" => "\xee\x85\x9a",
        "\xf0\x9f\x9a\x96" => "",
        "\xf0\x9f\x9a\x97" => "\xee\x80\x9b",
        "\xf0\x9f\x9a\x98" => "",
        "\xf0\x9f\x9a\x99" => "\xee\x90\xae",
        "\xf0\x9f\x9a\x9a" => "\xee\x90\xaf",
        "\xf0\x9f\x9a\x9b" => "",
        "\xf0\x9f\x9a\x9c" => "",
        "\xf0\x9f\x9a\x9d" => "",
        "\xf0\x9f\x9a\x9e" => "",
        "\xf0\x9f\x9a\x9f" => "",
        "\xf0\x9f\x9a\xa0" => "",
        "\xf0\x9f\x9a\xa1" => "",
        "\xf0\x9f\x9a\xa2" => "\xee\x88\x82",
        "\xf0\x9f\x9a\xa3" => "",
        "\xf0\x9f\x9a\xa4" => "\xee\x84\xb5",
        "\xf0\x9f\x9a\xa5" => "\xee\x85\x8e",
        "\xf0\x9f\x9a\xa6" => "",
        "\xf0\x9f\x9a\xa7" => "\xee\x84\xb7",
        "\xf0\x9f\x9a\xa8" => "\xee\x90\xb2",
        "\xf0\x9f\x9a\xa9" => "",
        "\xf0\x9f\x9a\xaa" => "",
        "\xf0\x9f\x9a\xab" => "",
        "\xf0\x9f\x9a\xac" => "\xee\x8c\x8e",
        "\xf0\x9f\x9a\xad" => "\xee\x88\x88",
        "\xf0\x9f\x9a\xae" => "",
        "\xf0\x9f\x9a\xaf" => "",
        "\xf0\x9f\x9a\xb0" => "",
        "\xf0\x9f\x9a\xb1" => "",
        "\xf0\x9f\x9a\xb2" => "\xee\x84\xb6",
        "\xf0\x9f\x9a\xb3" => "",
        "\xf0\x9f\x9a\xb4" => "",
        "\xf0\x9f\x9a\xb5" => "",
        "\xf0\x9f\x9a\xb6" => "\xee\x88\x81",
        "\xf0\x9f\x9a\xb7" => "",
        "\xf0\x9f\x9a\xb8" => "",
        "\xf0\x9f\x9a\xb9" => "\xee\x84\xb8",
        "\xf0\x9f\x9a\xba" => "\xee\x84\xb9",
        "\xf0\x9f\x9a\xbb" => "\xee\x85\x91",
        "\xf0\x9f\x9a\xbc" => "\xee\x84\xba",
        "\xf0\x9f\x9a\xbd" => "\xee\x85\x80",
        "\xf0\x9f\x9a\xbe" => "\xee\x8c\x89",
        "\xf0\x9f\x9a\xbf" => "",
        "\xf0\x9f\x9b\x80" => "\xee\x84\xbf",
        "\xf0\x9f\x9b\x81" => "",
        "\xf0\x9f\x9b\x82" => "",
        "\xf0\x9f\x9b\x83" => "",
        "\xf0\x9f\x9b\x84" => "",
        "\xf0\x9f\x9b\x85" => "",
        "\xf0\x9f\x9b\x8b" => "",
        "\xf0\x9f\x9b\x8c" => "",
        "\xf0\x9f\x9b\x8d" => "",
        "\xf0\x9f\x9b\x8e" => "",
        "\xf0\x9f\x9b\x8f" => "",
        "\xf0\x9f\x9b\x90" => "",
        "\xf0\x9f\x9b\xa0" => "",
        "\xf0\x9f\x9b\xa1" => "",
        "\xf0\x9f\x9b\xa2" => "",
        "\xf0\x9f\x9b\xa3" => "",
        "\xf0\x9f\x9b\xa4" => "",
        "\xf0\x9f\x9b\xa5" => "",
        "\xf0\x9f\x9b\xa9" => "",
        "\xf0\x9f\x9b\xab" => "",
        "\xf0\x9f\x9b\xac" => "",
        "\xf0\x9f\x9b\xb0" => "",
        "\xf0\x9f\x9b\xb3" => "",
        "\xf0\x9f\xa4\x90" => "",
        "\xf0\x9f\xa4\x91" => "",
        "\xf0\x9f\xa4\x92" => "",
        "\xf0\x9f\xa4\x93" => "",
        "\xf0\x9f\xa4\x94" => "",
        "\xf0\x9f\xa4\x95" => "",
        "\xf0\x9f\xa4\x96" => "",
        "\xf0\x9f\xa4\x97" => "",
        "\xf0\x9f\xa4\x98" => "",
        "\xf0\x9f\xa6\x80" => "",
        "\xf0\x9f\xa6\x81" => "",
        "\xf0\x9f\xa6\x82" => "",
        "\xf0\x9f\xa6\x83" => "",
        "\xf0\x9f\xa6\x84" => "",
        "\xf0\x9f\xa7\x80" => "",
        "#\xe2\x83\xa3" => "\xee\x88\x90",
        "*\xe2\x83\xa3" => "",
        "0\xe2\x83\xa3" => "\xee\x88\xa5",
        "1\xe2\x83\xa3" => "\xee\x88\x9c",
        "2\xe2\x83\xa3" => "\xee\x88\x9d",
        "3\xe2\x83\xa3" => "\xee\x88\x9e",
        "4\xe2\x83\xa3" => "\xee\x88\x9f",
        "5\xe2\x83\xa3" => "\xee\x88\xa0",
        "6\xe2\x83\xa3" => "\xee\x88\xa1",
        "7\xe2\x83\xa3" => "\xee\x88\xa2",
        "8\xe2\x83\xa3" => "\xee\x88\xa3",
        "9\xe2\x83\xa3" => "\xee\x88\xa4",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa7" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb3" => "\xee\x94\x93",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaa" => "\xee\x94\x8e",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb8" => "\xee\x94\x91",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb7" => "\xee\x94\x8d",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa7" => "\xee\x94\x90",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb9" => "\xee\x94\x8f",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb5" => "\xee\x94\x8b",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb7" => "\xee\x94\x94",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa7" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb4\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb6\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xba" => "\xee\x94\x92",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa7" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xb8" => "\xee\x94\x8c",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xbc\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xbc\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xbd\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xbe\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xbe\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x91\xa8" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x92\x8b\xe2\x80\x8d\xf0\x9f\x91\xa8" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x91\xa9" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x92\x8b\xe2\x80\x8d\xf0\x9f\x91\xa9" => "",
    ),
    'unified_to_google' => array(
        "\xc2\xa9" => "\xf3\xbe\xac\xa9",
        "\xc2\xae" => "\xf3\xbe\xac\xad",
        "\xe2\x80\xbc" => "\xf3\xbe\xac\x86",
        "\xe2\x81\x89" => "\xf3\xbe\xac\x85",
        "\xe2\x84\xa2" => "\xf3\xbe\xac\xaa",
        "\xe2\x84\xb9" => "\xf3\xbe\xad\x87",
        "\xe2\x86\x94" => "\xf3\xbe\xab\xb6",
        "\xe2\x86\x95" => "\xf3\xbe\xab\xb7",
        "\xe2\x86\x96" => "\xf3\xbe\xab\xb2",
        "\xe2\x86\x97" => "\xf3\xbe\xab\xb0",
        "\xe2\x86\x98" => "\xf3\xbe\xab\xb1",
        "\xe2\x86\x99" => "\xf3\xbe\xab\xb3",
        "\xe2\x86\xa9" => "\xf3\xbe\xae\x83",
        "\xe2\x86\xaa" => "\xf3\xbe\xae\x88",
        "\xe2\x8c\x9a" => "\xf3\xbe\x80\x9d",
        "\xe2\x8c\x9b" => "\xf3\xbe\x80\x9c",
        "\xe2\x8c\xa8" => "",
        "\xe2\x8f\xa9" => "\xf3\xbe\xab\xbe",
        "\xe2\x8f\xaa" => "\xf3\xbe\xab\xbf",
        "\xe2\x8f\xab" => "\xf3\xbe\xac\x83",
        "\xe2\x8f\xac" => "\xf3\xbe\xac\x82",
        "\xe2\x8f\xad" => "",
        "\xe2\x8f\xae" => "",
        "\xe2\x8f\xaf" => "",
        "\xe2\x8f\xb0" => "\xf3\xbe\x80\xaa",
        "\xe2\x8f\xb1" => "",
        "\xe2\x8f\xb2" => "",
        "\xe2\x8f\xb3" => "\xf3\xbe\x80\x9b",
        "\xe2\x8f\xb8" => "",
        "\xe2\x8f\xb9" => "",
        "\xe2\x8f\xba" => "",
        "\xe2\x93\x82" => "\xf3\xbe\x9f\xa1",
        "\xe2\x96\xaa" => "\xf3\xbe\xad\xae",
        "\xe2\x96\xab" => "\xf3\xbe\xad\xad",
        "\xe2\x96\xb6" => "\xf3\xbe\xab\xbc",
        "\xe2\x97\x80" => "\xf3\xbe\xab\xbd",
        "\xe2\x97\xbb" => "\xf3\xbe\xad\xb1",
        "\xe2\x97\xbc" => "\xf3\xbe\xad\xb2",
        "\xe2\x97\xbd" => "\xf3\xbe\xad\xaf",
        "\xe2\x97\xbe" => "\xf3\xbe\xad\xb0",
        "\xe2\x98\x80" => "\xf3\xbe\x80\x80",
        "\xe2\x98\x81" => "\xf3\xbe\x80\x81",
        "\xe2\x98\x82" => "",
        "\xe2\x98\x83" => "",
        "\xe2\x98\x84" => "",
        "\xe2\x98\x8e" => "\xf3\xbe\x94\xa3",
        "\xe2\x98\x91" => "\xf3\xbe\xae\x8b",
        "\xe2\x98\x94" => "\xf3\xbe\x80\x82",
        "\xe2\x98\x95" => "\xf3\xbe\xa6\x81",
        "\xe2\x98\x98" => "",
        "\xe2\x98\x9d" => "\xf3\xbe\xae\x98",
        "\xe2\x98\xa0" => "",
        "\xe2\x98\xa2" => "",
        "\xe2\x98\xa3" => "",
        "\xe2\x98\xa6" => "",
        "\xe2\x98\xaa" => "",
        "\xe2\x98\xae" => "",
        "\xe2\x98\xaf" => "",
        "\xe2\x98\xb8" => "",
        "\xe2\x98\xb9" => "",
        "\xe2\x98\xba" => "\xf3\xbe\x8c\xb6",
        "\xe2\x99\x88" => "\xf3\xbe\x80\xab",
        "\xe2\x99\x89" => "\xf3\xbe\x80\xac",
        "\xe2\x99\x8a" => "\xf3\xbe\x80\xad",
        "\xe2\x99\x8b" => "\xf3\xbe\x80\xae",
        "\xe2\x99\x8c" => "\xf3\xbe\x80\xaf",
        "\xe2\x99\x8d" => "\xf3\xbe\x80\xb0",
        "\xe2\x99\x8e" => "\xf3\xbe\x80\xb1",
        "\xe2\x99\x8f" => "\xf3\xbe\x80\xb2",
        "\xe2\x99\x90" => "\xf3\xbe\x80\xb3",
        "\xe2\x99\x91" => "\xf3\xbe\x80\xb4",
        "\xe2\x99\x92" => "\xf3\xbe\x80\xb5",
        "\xe2\x99\x93" => "\xf3\xbe\x80\xb6",
        "\xe2\x99\xa0" => "\xf3\xbe\xac\x9b",
        "\xe2\x99\xa3" => "\xf3\xbe\xac\x9d",
        "\xe2\x99\xa5" => "\xf3\xbe\xac\x9a",
        "\xe2\x99\xa6" => "\xf3\xbe\xac\x9c",
        "\xe2\x99\xa8" => "\xf3\xbe\x9f\xba",
        "\xe2\x99\xbb" => "\xf3\xbe\xac\xac",
        "\xe2\x99\xbf" => "\xf3\xbe\xac\xa0",
        "\xe2\x9a\x92" => "",
        "\xe2\x9a\x93" => "\xf3\xbe\x93\x81",
        "\xe2\x9a\x94" => "",
        "\xe2\x9a\x96" => "",
        "\xe2\x9a\x97" => "",
        "\xe2\x9a\x99" => "",
        "\xe2\x9a\x9b" => "",
        "\xe2\x9a\x9c" => "",
        "\xe2\x9a\xa0" => "\xf3\xbe\xac\xa3",
        "\xe2\x9a\xa1" => "\xf3\xbe\x80\x84",
        "\xe2\x9a\xaa" => "\xf3\xbe\xad\xa5",
        "\xe2\x9a\xab" => "\xf3\xbe\xad\xa6",
        "\xe2\x9a\xb0" => "",
        "\xe2\x9a\xb1" => "",
        "\xe2\x9a\xbd" => "\xf3\xbe\x9f\x94",
        "\xe2\x9a\xbe" => "\xf3\xbe\x9f\x91",
        "\xe2\x9b\x84" => "\xf3\xbe\x80\x83",
        "\xe2\x9b\x85" => "\xf3\xbe\x80\x8f",
        "\xe2\x9b\x88" => "",
        "\xe2\x9b\x8e" => "\xf3\xbe\x80\xb7",
        "\xe2\x9b\x8f" => "",
        "\xe2\x9b\x91" => "",
        "\xe2\x9b\x93" => "",
        "\xe2\x9b\x94" => "\xf3\xbe\xac\xa6",
        "\xe2\x9b\xa9" => "",
        "\xe2\x9b\xaa" => "\xf3\xbe\x92\xbb",
        "\xe2\x9b\xb0" => "",
        "\xe2\x9b\xb1" => "",
        "\xe2\x9b\xb2" => "\xf3\xbe\x92\xbc",
        "\xe2\x9b\xb3" => "\xf3\xbe\x9f\x92",
        "\xe2\x9b\xb4" => "",
        "\xe2\x9b\xb5" => "\xf3\xbe\x9f\xaa",
        "\xe2\x9b\xb7" => "",
        "\xe2\x9b\xb8" => "",
        "\xe2\x9b\xb9" => "",
        "\xe2\x9b\xba" => "\xf3\xbe\x9f\xbb",
        "\xe2\x9b\xbd" => "\xf3\xbe\x9f\xb5",
        "\xe2\x9c\x82" => "\xf3\xbe\x94\xbe",
        "\xe2\x9c\x85" => "\xf3\xbe\xad\x8a",
        "\xe2\x9c\x88" => "\xf3\xbe\x9f\xa9",
        "\xe2\x9c\x89" => "\xf3\xbe\x94\xa9",
        "\xe2\x9c\x8a" => "\xf3\xbe\xae\x93",
        "\xe2\x9c\x8b" => "\xf3\xbe\xae\x95",
        "\xe2\x9c\x8c" => "\xf3\xbe\xae\x94",
        "\xe2\x9c\x8d" => "",
        "\xe2\x9c\x8f" => "\xf3\xbe\x94\xb9",
        "\xe2\x9c\x92" => "\xf3\xbe\x94\xb6",
        "\xe2\x9c\x94" => "\xf3\xbe\xad\x89",
        "\xe2\x9c\x96" => "\xf3\xbe\xad\x93",
        "\xe2\x9c\x9d" => "",
        "\xe2\x9c\xa1" => "",
        "\xe2\x9c\xa8" => "\xf3\xbe\xad\xa0",
        "\xe2\x9c\xb3" => "\xf3\xbe\xad\xa2",
        "\xe2\x9c\xb4" => "\xf3\xbe\xad\xa1",
        "\xe2\x9d\x84" => "\xf3\xbe\x80\x8e",
        "\xe2\x9d\x87" => "\xf3\xbe\xad\xb7",
        "\xe2\x9d\x8c" => "\xf3\xbe\xad\x85",
        "\xe2\x9d\x8e" => "\xf3\xbe\xad\x86",
        "\xe2\x9d\x93" => "\xf3\xbe\xac\x89",
        "\xe2\x9d\x94" => "\xf3\xbe\xac\x8a",
        "\xe2\x9d\x95" => "\xf3\xbe\xac\x8b",
        "\xe2\x9d\x97" => "\xf3\xbe\xac\x84",
        "\xe2\x9d\xa3" => "",
        "\xe2\x9d\xa4" => "\xf3\xbe\xac\x8c",
        "\xe2\x9e\x95" => "\xf3\xbe\xad\x91",
        "\xe2\x9e\x96" => "\xf3\xbe\xad\x92",
        "\xe2\x9e\x97" => "\xf3\xbe\xad\x94",
        "\xe2\x9e\xa1" => "\xf3\xbe\xab\xba",
        "\xe2\x9e\xb0" => "\xf3\xbe\xac\x88",
        "\xe2\x9e\xbf" => "\xf3\xbe\xa0\xab",
        "\xe2\xa4\xb4" => "\xf3\xbe\xab\xb4",
        "\xe2\xa4\xb5" => "\xf3\xbe\xab\xb5",
        "\xe2\xac\x85" => "\xf3\xbe\xab\xbb",
        "\xe2\xac\x86" => "\xf3\xbe\xab\xb8",
        "\xe2\xac\x87" => "\xf3\xbe\xab\xb9",
        "\xe2\xac\x9b" => "\xf3\xbe\xad\xac",
        "\xe2\xac\x9c" => "\xf3\xbe\xad\xab",
        "\xe2\xad\x90" => "\xf3\xbe\xad\xa8",
        "\xe2\xad\x95" => "\xf3\xbe\xad\x84",
        "\xe3\x80\xb0" => "\xf3\xbe\xac\x87",
        "\xe3\x80\xbd" => "\xf3\xbe\xa0\x9b",
        "\xe3\x8a\x97" => "\xf3\xbe\xad\x83",
        "\xe3\x8a\x99" => "\xf3\xbe\xac\xab",
        "\xf0\x9f\x80\x84" => "\xf3\xbe\xa0\x8b",
        "\xf0\x9f\x83\x8f" => "\xf3\xbe\xa0\x92",
        "\xf0\x9f\x85\xb0" => "\xf3\xbe\x94\x8b",
        "\xf0\x9f\x85\xb1" => "\xf3\xbe\x94\x8c",
        "\xf0\x9f\x85\xbe" => "\xf3\xbe\x94\x8e",
        "\xf0\x9f\x85\xbf" => "\xf3\xbe\x9f\xb6",
        "\xf0\x9f\x86\x8e" => "\xf3\xbe\x94\x8d",
        "\xf0\x9f\x86\x91" => "\xf3\xbe\xae\x84",
        "\xf0\x9f\x86\x92" => "\xf3\xbe\xac\xb8",
        "\xf0\x9f\x86\x93" => "\xf3\xbe\xac\xa1",
        "\xf0\x9f\x86\x94" => "\xf3\xbe\xae\x81",
        "\xf0\x9f\x86\x95" => "\xf3\xbe\xac\xb6",
        "\xf0\x9f\x86\x96" => "\xf3\xbe\xac\xa8",
        "\xf0\x9f\x86\x97" => "\xf3\xbe\xac\xa7",
        "\xf0\x9f\x86\x98" => "\xf3\xbe\xad\x8f",
        "\xf0\x9f\x86\x99" => "\xf3\xbe\xac\xb7",
        "\xf0\x9f\x86\x9a" => "\xf3\xbe\xac\xb2",
        "\xf0\x9f\x88\x81" => "\xf3\xbe\xac\xa4",
        "\xf0\x9f\x88\x82" => "\xf3\xbe\xac\xbf",
        "\xf0\x9f\x88\x9a" => "\xf3\xbe\xac\xba",
        "\xf0\x9f\x88\xaf" => "\xf3\xbe\xad\x80",
        "\xf0\x9f\x88\xb2" => "\xf3\xbe\xac\xae",
        "\xf0\x9f\x88\xb3" => "\xf3\xbe\xac\xaf",
        "\xf0\x9f\x88\xb4" => "\xf3\xbe\xac\xb0",
        "\xf0\x9f\x88\xb5" => "\xf3\xbe\xac\xb1",
        "\xf0\x9f\x88\xb6" => "\xf3\xbe\xac\xb9",
        "\xf0\x9f\x88\xb7" => "\xf3\xbe\xac\xbb",
        "\xf0\x9f\x88\xb8" => "\xf3\xbe\xac\xbc",
        "\xf0\x9f\x88\xb9" => "\xf3\xbe\xac\xbe",
        "\xf0\x9f\x88\xba" => "\xf3\xbe\xad\x81",
        "\xf0\x9f\x89\x90" => "\xf3\xbe\xac\xbd",
        "\xf0\x9f\x89\x91" => "\xf3\xbe\xad\x90",
        "\xf0\x9f\x8c\x80" => "\xf3\xbe\x80\x85",
        "\xf0\x9f\x8c\x81" => "\xf3\xbe\x80\x86",
        "\xf0\x9f\x8c\x82" => "\xf3\xbe\x80\x87",
        "\xf0\x9f\x8c\x83" => "\xf3\xbe\x80\x88",
        "\xf0\x9f\x8c\x84" => "\xf3\xbe\x80\x89",
        "\xf0\x9f\x8c\x85" => "\xf3\xbe\x80\x8a",
        "\xf0\x9f\x8c\x86" => "\xf3\xbe\x80\x8b",
        "\xf0\x9f\x8c\x87" => "\xf3\xbe\x80\x8c",
        "\xf0\x9f\x8c\x88" => "\xf3\xbe\x80\x8d",
        "\xf0\x9f\x8c\x89" => "\xf3\xbe\x80\x90",
        "\xf0\x9f\x8c\x8a" => "\xf3\xbe\x80\xb8",
        "\xf0\x9f\x8c\x8b" => "\xf3\xbe\x80\xba",
        "\xf0\x9f\x8c\x8c" => "\xf3\xbe\x80\xbb",
        "\xf0\x9f\x8c\x8d" => "",
        "\xf0\x9f\x8c\x8e" => "",
        "\xf0\x9f\x8c\x8f" => "\xf3\xbe\x80\xb9",
        "\xf0\x9f\x8c\x90" => "",
        "\xf0\x9f\x8c\x91" => "\xf3\xbe\x80\x91",
        "\xf0\x9f\x8c\x92" => "",
        "\xf0\x9f\x8c\x93" => "\xf3\xbe\x80\x93",
        "\xf0\x9f\x8c\x94" => "\xf3\xbe\x80\x92",
        "\xf0\x9f\x8c\x95" => "\xf3\xbe\x80\x95",
        "\xf0\x9f\x8c\x96" => "",
        "\xf0\x9f\x8c\x97" => "",
        "\xf0\x9f\x8c\x98" => "",
        "\xf0\x9f\x8c\x99" => "\xf3\xbe\x80\x94",
        "\xf0\x9f\x8c\x9a" => "",
        "\xf0\x9f\x8c\x9b" => "\xf3\xbe\x80\x96",
        "\xf0\x9f\x8c\x9c" => "",
        "\xf0\x9f\x8c\x9d" => "",
        "\xf0\x9f\x8c\x9e" => "",
        "\xf0\x9f\x8c\x9f" => "\xf3\xbe\xad\xa9",
        "\xf0\x9f\x8c\xa0" => "\xf3\xbe\xad\xaa",
        "\xf0\x9f\x8c\xa1" => "",
        "\xf0\x9f\x8c\xa4" => "",
        "\xf0\x9f\x8c\xa5" => "",
        "\xf0\x9f\x8c\xa6" => "",
        "\xf0\x9f\x8c\xa7" => "",
        "\xf0\x9f\x8c\xa8" => "",
        "\xf0\x9f\x8c\xa9" => "",
        "\xf0\x9f\x8c\xaa" => "",
        "\xf0\x9f\x8c\xab" => "",
        "\xf0\x9f\x8c\xac" => "",
        "\xf0\x9f\x8c\xad" => "",
        "\xf0\x9f\x8c\xae" => "",
        "\xf0\x9f\x8c\xaf" => "",
        "\xf0\x9f\x8c\xb0" => "\xf3\xbe\x81\x8c",
        "\xf0\x9f\x8c\xb1" => "\xf3\xbe\x80\xbe",
        "\xf0\x9f\x8c\xb2" => "",
        "\xf0\x9f\x8c\xb3" => "",
        "\xf0\x9f\x8c\xb4" => "\xf3\xbe\x81\x87",
        "\xf0\x9f\x8c\xb5" => "\xf3\xbe\x81\x88",
        "\xf0\x9f\x8c\xb6" => "",
        "\xf0\x9f\x8c\xb7" => "\xf3\xbe\x80\xbd",
        "\xf0\x9f\x8c\xb8" => "\xf3\xbe\x81\x80",
        "\xf0\x9f\x8c\xb9" => "\xf3\xbe\x81\x81",
        "\xf0\x9f\x8c\xba" => "\xf3\xbe\x81\x85",
        "\xf0\x9f\x8c\xbb" => "\xf3\xbe\x81\x86",
        "\xf0\x9f\x8c\xbc" => "\xf3\xbe\x81\x8d",
        "\xf0\x9f\x8c\xbd" => "\xf3\xbe\x81\x8a",
        "\xf0\x9f\x8c\xbe" => "\xf3\xbe\x81\x89",
        "\xf0\x9f\x8c\xbf" => "\xf3\xbe\x81\x8e",
        "\xf0\x9f\x8d\x80" => "\xf3\xbe\x80\xbc",
        "\xf0\x9f\x8d\x81" => "\xf3\xbe\x80\xbf",
        "\xf0\x9f\x8d\x82" => "\xf3\xbe\x81\x82",
        "\xf0\x9f\x8d\x83" => "\xf3\xbe\x81\x83",
        "\xf0\x9f\x8d\x84" => "\xf3\xbe\x81\x8b",
        "\xf0\x9f\x8d\x85" => "\xf3\xbe\x81\x95",
        "\xf0\x9f\x8d\x86" => "\xf3\xbe\x81\x96",
        "\xf0\x9f\x8d\x87" => "\xf3\xbe\x81\x99",
        "\xf0\x9f\x8d\x88" => "\xf3\xbe\x81\x97",
        "\xf0\x9f\x8d\x89" => "\xf3\xbe\x81\x94",
        "\xf0\x9f\x8d\x8a" => "\xf3\xbe\x81\x92",
        "\xf0\x9f\x8d\x8b" => "",
        "\xf0\x9f\x8d\x8c" => "\xf3\xbe\x81\x90",
        "\xf0\x9f\x8d\x8d" => "\xf3\xbe\x81\x98",
        "\xf0\x9f\x8d\x8e" => "\xf3\xbe\x81\x91",
        "\xf0\x9f\x8d\x8f" => "\xf3\xbe\x81\x9b",
        "\xf0\x9f\x8d\x90" => "",
        "\xf0\x9f\x8d\x91" => "\xf3\xbe\x81\x9a",
        "\xf0\x9f\x8d\x92" => "\xf3\xbe\x81\x8f",
        "\xf0\x9f\x8d\x93" => "\xf3\xbe\x81\x93",
        "\xf0\x9f\x8d\x94" => "\xf3\xbe\xa5\xa0",
        "\xf0\x9f\x8d\x95" => "\xf3\xbe\xa5\xb5",
        "\xf0\x9f\x8d\x96" => "\xf3\xbe\xa5\xb2",
        "\xf0\x9f\x8d\x97" => "\xf3\xbe\xa5\xb6",
        "\xf0\x9f\x8d\x98" => "\xf3\xbe\xa5\xa9",
        "\xf0\x9f\x8d\x99" => "\xf3\xbe\xa5\xa1",
        "\xf0\x9f\x8d\x9a" => "\xf3\xbe\xa5\xaa",
        "\xf0\x9f\x8d\x9b" => "\xf3\xbe\xa5\xac",
        "\xf0\x9f\x8d\x9c" => "\xf3\xbe\xa5\xa3",
        "\xf0\x9f\x8d\x9d" => "\xf3\xbe\xa5\xab",
        "\xf0\x9f\x8d\x9e" => "\xf3\xbe\xa5\xa4",
        "\xf0\x9f\x8d\x9f" => "\xf3\xbe\xa5\xa7",
        "\xf0\x9f\x8d\xa0" => "\xf3\xbe\xa5\xb4",
        "\xf0\x9f\x8d\xa1" => "\xf3\xbe\xa5\xa8",
        "\xf0\x9f\x8d\xa2" => "\xf3\xbe\xa5\xad",
        "\xf0\x9f\x8d\xa3" => "\xf3\xbe\xa5\xae",
        "\xf0\x9f\x8d\xa4" => "\xf3\xbe\xa5\xbf",
        "\xf0\x9f\x8d\xa5" => "\xf3\xbe\xa5\xb3",
        "\xf0\x9f\x8d\xa6" => "\xf3\xbe\xa5\xa6",
        "\xf0\x9f\x8d\xa7" => "\xf3\xbe\xa5\xb1",
        "\xf0\x9f\x8d\xa8" => "\xf3\xbe\xa5\xb7",
        "\xf0\x9f\x8d\xa9" => "\xf3\xbe\xa5\xb8",
        "\xf0\x9f\x8d\xaa" => "\xf3\xbe\xa5\xb9",
        "\xf0\x9f\x8d\xab" => "\xf3\xbe\xa5\xba",
        "\xf0\x9f\x8d\xac" => "\xf3\xbe\xa5\xbb",
        "\xf0\x9f\x8d\xad" => "\xf3\xbe\xa5\xbc",
        "\xf0\x9f\x8d\xae" => "\xf3\xbe\xa5\xbd",
        "\xf0\x9f\x8d\xaf" => "\xf3\xbe\xa5\xbe",
        "\xf0\x9f\x8d\xb0" => "\xf3\xbe\xa5\xa2",
        "\xf0\x9f\x8d\xb1" => "\xf3\xbe\xa5\xaf",
        "\xf0\x9f\x8d\xb2" => "\xf3\xbe\xa5\xb0",
        "\xf0\x9f\x8d\xb3" => "\xf3\xbe\xa5\xa5",
        "\xf0\x9f\x8d\xb4" => "\xf3\xbe\xa6\x80",
        "\xf0\x9f\x8d\xb5" => "\xf3\xbe\xa6\x84",
        "\xf0\x9f\x8d\xb6" => "\xf3\xbe\xa6\x85",
        "\xf0\x9f\x8d\xb7" => "\xf3\xbe\xa6\x86",
        "\xf0\x9f\x8d\xb8" => "\xf3\xbe\xa6\x82",
        "\xf0\x9f\x8d\xb9" => "\xf3\xbe\xa6\x88",
        "\xf0\x9f\x8d\xba" => "\xf3\xbe\xa6\x83",
        "\xf0\x9f\x8d\xbb" => "\xf3\xbe\xa6\x87",
        "\xf0\x9f\x8d\xbc" => "",
        "\xf0\x9f\x8d\xbd" => "",
        "\xf0\x9f\x8d\xbe" => "",
        "\xf0\x9f\x8d\xbf" => "",
        "\xf0\x9f\x8e\x80" => "\xf3\xbe\x94\x8f",
        "\xf0\x9f\x8e\x81" => "\xf3\xbe\x94\x90",
        "\xf0\x9f\x8e\x82" => "\xf3\xbe\x94\x91",
        "\xf0\x9f\x8e\x83" => "\xf3\xbe\x94\x9f",
        "\xf0\x9f\x8e\x84" => "\xf3\xbe\x94\x92",
        "\xf0\x9f\x8e\x85" => "\xf3\xbe\x94\x93",
        "\xf0\x9f\x8e\x86" => "\xf3\xbe\x94\x95",
        "\xf0\x9f\x8e\x87" => "\xf3\xbe\x94\x9d",
        "\xf0\x9f\x8e\x88" => "\xf3\xbe\x94\x96",
        "\xf0\x9f\x8e\x89" => "\xf3\xbe\x94\x97",
        "\xf0\x9f\x8e\x8a" => "\xf3\xbe\x94\xa0",
        "\xf0\x9f\x8e\x8b" => "\xf3\xbe\x94\xa1",
        "\xf0\x9f\x8e\x8c" => "\xf3\xbe\x94\x94",
        "\xf0\x9f\x8e\x8d" => "\xf3\xbe\x94\x98",
        "\xf0\x9f\x8e\x8e" => "\xf3\xbe\x94\x99",
        "\xf0\x9f\x8e\x8f" => "\xf3\xbe\x94\x9c",
        "\xf0\x9f\x8e\x90" => "\xf3\xbe\x94\x9e",
        "\xf0\x9f\x8e\x91" => "\xf3\xbe\x80\x97",
        "\xf0\x9f\x8e\x92" => "\xf3\xbe\x94\x9b",
        "\xf0\x9f\x8e\x93" => "\xf3\xbe\x94\x9a",
        "\xf0\x9f\x8e\x96" => "",
        "\xf0\x9f\x8e\x97" => "",
        "\xf0\x9f\x8e\x99" => "",
        "\xf0\x9f\x8e\x9a" => "",
        "\xf0\x9f\x8e\x9b" => "",
        "\xf0\x9f\x8e\x9e" => "",
        "\xf0\x9f\x8e\x9f" => "",
        "\xf0\x9f\x8e\xa0" => "\xf3\xbe\x9f\xbc",
        "\xf0\x9f\x8e\xa1" => "\xf3\xbe\x9f\xbd",
        "\xf0\x9f\x8e\xa2" => "\xf3\xbe\x9f\xbe",
        "\xf0\x9f\x8e\xa3" => "\xf3\xbe\x9f\xbf",
        "\xf0\x9f\x8e\xa4" => "\xf3\xbe\xa0\x80",
        "\xf0\x9f\x8e\xa5" => "\xf3\xbe\xa0\x81",
        "\xf0\x9f\x8e\xa6" => "\xf3\xbe\xa0\x82",
        "\xf0\x9f\x8e\xa7" => "\xf3\xbe\xa0\x83",
        "\xf0\x9f\x8e\xa8" => "\xf3\xbe\xa0\x84",
        "\xf0\x9f\x8e\xa9" => "\xf3\xbe\xa0\x85",
        "\xf0\x9f\x8e\xaa" => "\xf3\xbe\xa0\x86",
        "\xf0\x9f\x8e\xab" => "\xf3\xbe\xa0\x87",
        "\xf0\x9f\x8e\xac" => "\xf3\xbe\xa0\x88",
        "\xf0\x9f\x8e\xad" => "\xf3\xbe\xa0\x89",
        "\xf0\x9f\x8e\xae" => "\xf3\xbe\xa0\x8a",
        "\xf0\x9f\x8e\xaf" => "\xf3\xbe\xa0\x8c",
        "\xf0\x9f\x8e\xb0" => "\xf3\xbe\xa0\x8d",
        "\xf0\x9f\x8e\xb1" => "\xf3\xbe\xa0\x8e",
        "\xf0\x9f\x8e\xb2" => "\xf3\xbe\xa0\x8f",
        "\xf0\x9f\x8e\xb3" => "\xf3\xbe\xa0\x90",
        "\xf0\x9f\x8e\xb4" => "\xf3\xbe\xa0\x91",
        "\xf0\x9f\x8e\xb5" => "\xf3\xbe\xa0\x93",
        "\xf0\x9f\x8e\xb6" => "\xf3\xbe\xa0\x94",
        "\xf0\x9f\x8e\xb7" => "\xf3\xbe\xa0\x95",
        "\xf0\x9f\x8e\xb8" => "\xf3\xbe\xa0\x96",
        "\xf0\x9f\x8e\xb9" => "\xf3\xbe\xa0\x97",
        "\xf0\x9f\x8e\xba" => "\xf3\xbe\xa0\x98",
        "\xf0\x9f\x8e\xbb" => "\xf3\xbe\xa0\x99",
        "\xf0\x9f\x8e\xbc" => "\xf3\xbe\xa0\x9a",
        "\xf0\x9f\x8e\xbd" => "\xf3\xbe\x9f\x90",
        "\xf0\x9f\x8e\xbe" => "\xf3\xbe\x9f\x93",
        "\xf0\x9f\x8e\xbf" => "\xf3\xbe\x9f\x95",
        "\xf0\x9f\x8f\x80" => "\xf3\xbe\x9f\x96",
        "\xf0\x9f\x8f\x81" => "\xf3\xbe\x9f\x97",
        "\xf0\x9f\x8f\x82" => "\xf3\xbe\x9f\x98",
        "\xf0\x9f\x8f\x83" => "\xf3\xbe\x9f\x99",
        "\xf0\x9f\x8f\x84" => "\xf3\xbe\x9f\x9a",
        "\xf0\x9f\x8f\x85" => "",
        "\xf0\x9f\x8f\x86" => "\xf3\xbe\x9f\x9b",
        "\xf0\x9f\x8f\x87" => "",
        "\xf0\x9f\x8f\x88" => "\xf3\xbe\x9f\x9d",
        "\xf0\x9f\x8f\x89" => "",
        "\xf0\x9f\x8f\x8a" => "\xf3\xbe\x9f\x9e",
        "\xf0\x9f\x8f\x8b" => "",
        "\xf0\x9f\x8f\x8c" => "",
        "\xf0\x9f\x8f\x8d" => "",
        "\xf0\x9f\x8f\x8e" => "",
        "\xf0\x9f\x8f\x8f" => "",
        "\xf0\x9f\x8f\x90" => "",
        "\xf0\x9f\x8f\x91" => "",
        "\xf0\x9f\x8f\x92" => "",
        "\xf0\x9f\x8f\x93" => "",
        "\xf0\x9f\x8f\x94" => "",
        "\xf0\x9f\x8f\x95" => "",
        "\xf0\x9f\x8f\x96" => "",
        "\xf0\x9f\x8f\x97" => "",
        "\xf0\x9f\x8f\x98" => "",
        "\xf0\x9f\x8f\x99" => "",
        "\xf0\x9f\x8f\x9a" => "",
        "\xf0\x9f\x8f\x9b" => "",
        "\xf0\x9f\x8f\x9c" => "",
        "\xf0\x9f\x8f\x9d" => "",
        "\xf0\x9f\x8f\x9e" => "",
        "\xf0\x9f\x8f\x9f" => "",
        "\xf0\x9f\x8f\xa0" => "\xf3\xbe\x92\xb0",
        "\xf0\x9f\x8f\xa1" => "\xf3\xbe\x92\xb1",
        "\xf0\x9f\x8f\xa2" => "\xf3\xbe\x92\xb2",
        "\xf0\x9f\x8f\xa3" => "\xf3\xbe\x92\xb3",
        "\xf0\x9f\x8f\xa4" => "",
        "\xf0\x9f\x8f\xa5" => "\xf3\xbe\x92\xb4",
        "\xf0\x9f\x8f\xa6" => "\xf3\xbe\x92\xb5",
        "\xf0\x9f\x8f\xa7" => "\xf3\xbe\x92\xb6",
        "\xf0\x9f\x8f\xa8" => "\xf3\xbe\x92\xb7",
        "\xf0\x9f\x8f\xa9" => "\xf3\xbe\x92\xb8",
        "\xf0\x9f\x8f\xaa" => "\xf3\xbe\x92\xb9",
        "\xf0\x9f\x8f\xab" => "\xf3\xbe\x92\xba",
        "\xf0\x9f\x8f\xac" => "\xf3\xbe\x92\xbd",
        "\xf0\x9f\x8f\xad" => "\xf3\xbe\x93\x80",
        "\xf0\x9f\x8f\xae" => "\xf3\xbe\x93\x82",
        "\xf0\x9f\x8f\xaf" => "\xf3\xbe\x92\xbe",
        "\xf0\x9f\x8f\xb0" => "\xf3\xbe\x92\xbf",
        "\xf0\x9f\x8f\xb3" => "",
        "\xf0\x9f\x8f\xb4" => "",
        "\xf0\x9f\x8f\xb5" => "",
        "\xf0\x9f\x8f\xb7" => "",
        "\xf0\x9f\x8f\xb8" => "",
        "\xf0\x9f\x8f\xb9" => "",
        "\xf0\x9f\x8f\xba" => "",
        "\xf0\x9f\x8f\xbb" => "",
        "\xf0\x9f\x8f\xbc" => "",
        "\xf0\x9f\x8f\xbd" => "",
        "\xf0\x9f\x8f\xbe" => "",
        "\xf0\x9f\x8f\xbf" => "",
        "\xf0\x9f\x90\x80" => "",
        "\xf0\x9f\x90\x81" => "",
        "\xf0\x9f\x90\x82" => "",
        "\xf0\x9f\x90\x83" => "",
        "\xf0\x9f\x90\x84" => "",
        "\xf0\x9f\x90\x85" => "",
        "\xf0\x9f\x90\x86" => "",
        "\xf0\x9f\x90\x87" => "",
        "\xf0\x9f\x90\x88" => "",
        "\xf0\x9f\x90\x89" => "",
        "\xf0\x9f\x90\x8a" => "",
        "\xf0\x9f\x90\x8b" => "",
        "\xf0\x9f\x90\x8c" => "\xf3\xbe\x86\xb9",
        "\xf0\x9f\x90\x8d" => "\xf3\xbe\x87\x93",
        "\xf0\x9f\x90\x8e" => "\xf3\xbe\x9f\x9c",
        "\xf0\x9f\x90\x8f" => "",
        "\xf0\x9f\x90\x90" => "",
        "\xf0\x9f\x90\x91" => "\xf3\xbe\x87\x8f",
        "\xf0\x9f\x90\x92" => "\xf3\xbe\x87\x8e",
        "\xf0\x9f\x90\x93" => "",
        "\xf0\x9f\x90\x94" => "\xf3\xbe\x87\x94",
        "\xf0\x9f\x90\x95" => "",
        "\xf0\x9f\x90\x96" => "",
        "\xf0\x9f\x90\x97" => "\xf3\xbe\x87\x95",
        "\xf0\x9f\x90\x98" => "\xf3\xbe\x87\x8c",
        "\xf0\x9f\x90\x99" => "\xf3\xbe\x87\x85",
        "\xf0\x9f\x90\x9a" => "\xf3\xbe\x87\x86",
        "\xf0\x9f\x90\x9b" => "\xf3\xbe\x87\x8b",
        "\xf0\x9f\x90\x9c" => "\xf3\xbe\x87\x9a",
        "\xf0\x9f\x90\x9d" => "\xf3\xbe\x87\xa1",
        "\xf0\x9f\x90\x9e" => "\xf3\xbe\x87\xa2",
        "\xf0\x9f\x90\x9f" => "\xf3\xbe\x86\xbd",
        "\xf0\x9f\x90\xa0" => "\xf3\xbe\x87\x89",
        "\xf0\x9f\x90\xa1" => "\xf3\xbe\x87\x99",
        "\xf0\x9f\x90\xa2" => "\xf3\xbe\x87\x9c",
        "\xf0\x9f\x90\xa3" => "\xf3\xbe\x87\x9d",
        "\xf0\x9f\x90\xa4" => "\xf3\xbe\x86\xba",
        "\xf0\x9f\x90\xa5" => "\xf3\xbe\x86\xbb",
        "\xf0\x9f\x90\xa6" => "\xf3\xbe\x87\x88",
        "\xf0\x9f\x90\xa7" => "\xf3\xbe\x86\xbc",
        "\xf0\x9f\x90\xa8" => "\xf3\xbe\x87\x8d",
        "\xf0\x9f\x90\xa9" => "\xf3\xbe\x87\x98",
        "\xf0\x9f\x90\xaa" => "",
        "\xf0\x9f\x90\xab" => "\xf3\xbe\x87\x96",
        "\xf0\x9f\x90\xac" => "\xf3\xbe\x87\x87",
        "\xf0\x9f\x90\xad" => "\xf3\xbe\x87\x82",
        "\xf0\x9f\x90\xae" => "\xf3\xbe\x87\x91",
        "\xf0\x9f\x90\xaf" => "\xf3\xbe\x87\x80",
        "\xf0\x9f\x90\xb0" => "\xf3\xbe\x87\x92",
        "\xf0\x9f\x90\xb1" => "\xf3\xbe\x86\xb8",
        "\xf0\x9f\x90\xb2" => "\xf3\xbe\x87\x9e",
        "\xf0\x9f\x90\xb3" => "\xf3\xbe\x87\x83",
        "\xf0\x9f\x90\xb4" => "\xf3\xbe\x86\xbe",
        "\xf0\x9f\x90\xb5" => "\xf3\xbe\x87\x84",
        "\xf0\x9f\x90\xb6" => "\xf3\xbe\x86\xb7",
        "\xf0\x9f\x90\xb7" => "\xf3\xbe\x86\xbf",
        "\xf0\x9f\x90\xb8" => "\xf3\xbe\x87\x97",
        "\xf0\x9f\x90\xb9" => "\xf3\xbe\x87\x8a",
        "\xf0\x9f\x90\xba" => "\xf3\xbe\x87\x90",
        "\xf0\x9f\x90\xbb" => "\xf3\xbe\x87\x81",
        "\xf0\x9f\x90\xbc" => "\xf3\xbe\x87\x9f",
        "\xf0\x9f\x90\xbd" => "\xf3\xbe\x87\xa0",
        "\xf0\x9f\x90\xbe" => "\xf3\xbe\x87\x9b",
        "\xf0\x9f\x90\xbf" => "",
        "\xf0\x9f\x91\x80" => "\xf3\xbe\x86\x90",
        "\xf0\x9f\x91\x81" => "",
        "\xf0\x9f\x91\x82" => "\xf3\xbe\x86\x91",
        "\xf0\x9f\x91\x83" => "\xf3\xbe\x86\x92",
        "\xf0\x9f\x91\x84" => "\xf3\xbe\x86\x93",
        "\xf0\x9f\x91\x85" => "\xf3\xbe\x86\x94",
        "\xf0\x9f\x91\x86" => "\xf3\xbe\xae\x99",
        "\xf0\x9f\x91\x87" => "\xf3\xbe\xae\x9a",
        "\xf0\x9f\x91\x88" => "\xf3\xbe\xae\x9b",
        "\xf0\x9f\x91\x89" => "\xf3\xbe\xae\x9c",
        "\xf0\x9f\x91\x8a" => "\xf3\xbe\xae\x96",
        "\xf0\x9f\x91\x8b" => "\xf3\xbe\xae\x9d",
        "\xf0\x9f\x91\x8c" => "\xf3\xbe\xae\x9f",
        "\xf0\x9f\x91\x8d" => "\xf3\xbe\xae\x97",
        "\xf0\x9f\x91\x8e" => "\xf3\xbe\xae\xa0",
        "\xf0\x9f\x91\x8f" => "\xf3\xbe\xae\x9e",
        "\xf0\x9f\x91\x90" => "\xf3\xbe\xae\xa1",
        "\xf0\x9f\x91\x91" => "\xf3\xbe\x93\x91",
        "\xf0\x9f\x91\x92" => "\xf3\xbe\x93\x94",
        "\xf0\x9f\x91\x93" => "\xf3\xbe\x93\x8e",
        "\xf0\x9f\x91\x94" => "\xf3\xbe\x93\x93",
        "\xf0\x9f\x91\x95" => "\xf3\xbe\x93\x8f",
        "\xf0\x9f\x91\x96" => "\xf3\xbe\x93\x90",
        "\xf0\x9f\x91\x97" => "\xf3\xbe\x93\x95",
        "\xf0\x9f\x91\x98" => "\xf3\xbe\x93\x99",
        "\xf0\x9f\x91\x99" => "\xf3\xbe\x93\x9a",
        "\xf0\x9f\x91\x9a" => "\xf3\xbe\x93\x9b",
        "\xf0\x9f\x91\x9b" => "\xf3\xbe\x93\x9c",
        "\xf0\x9f\x91\x9c" => "\xf3\xbe\x93\xb0",
        "\xf0\x9f\x91\x9d" => "\xf3\xbe\x93\xb1",
        "\xf0\x9f\x91\x9e" => "\xf3\xbe\x93\x8c",
        "\xf0\x9f\x91\x9f" => "\xf3\xbe\x93\x8d",
        "\xf0\x9f\x91\xa0" => "\xf3\xbe\x93\x96",
        "\xf0\x9f\x91\xa1" => "\xf3\xbe\x93\x97",
        "\xf0\x9f\x91\xa2" => "\xf3\xbe\x93\x98",
        "\xf0\x9f\x91\xa3" => "\xf3\xbe\x95\x93",
        "\xf0\x9f\x91\xa4" => "\xf3\xbe\x86\x9a",
        "\xf0\x9f\x91\xa5" => "",
        "\xf0\x9f\x91\xa6" => "\xf3\xbe\x86\x9b",
        "\xf0\x9f\x91\xa7" => "\xf3\xbe\x86\x9c",
        "\xf0\x9f\x91\xa8" => "\xf3\xbe\x86\x9d",
        "\xf0\x9f\x91\xa9" => "\xf3\xbe\x86\x9e",
        "\xf0\x9f\x91\xaa" => "\xf3\xbe\x86\x9f",
        "\xf0\x9f\x91\xab" => "\xf3\xbe\x86\xa0",
        "\xf0\x9f\x91\xac" => "",
        "\xf0\x9f\x91\xad" => "",
        "\xf0\x9f\x91\xae" => "\xf3\xbe\x86\xa1",
        "\xf0\x9f\x91\xaf" => "\xf3\xbe\x86\xa2",
        "\xf0\x9f\x91\xb0" => "\xf3\xbe\x86\xa3",
        "\xf0\x9f\x91\xb1" => "\xf3\xbe\x86\xa4",
        "\xf0\x9f\x91\xb2" => "\xf3\xbe\x86\xa5",
        "\xf0\x9f\x91\xb3" => "\xf3\xbe\x86\xa6",
        "\xf0\x9f\x91\xb4" => "\xf3\xbe\x86\xa7",
        "\xf0\x9f\x91\xb5" => "\xf3\xbe\x86\xa8",
        "\xf0\x9f\x91\xb6" => "\xf3\xbe\x86\xa9",
        "\xf0\x9f\x91\xb7" => "\xf3\xbe\x86\xaa",
        "\xf0\x9f\x91\xb8" => "\xf3\xbe\x86\xab",
        "\xf0\x9f\x91\xb9" => "\xf3\xbe\x86\xac",
        "\xf0\x9f\x91\xba" => "\xf3\xbe\x86\xad",
        "\xf0\x9f\x91\xbb" => "\xf3\xbe\x86\xae",
        "\xf0\x9f\x91\xbc" => "\xf3\xbe\x86\xaf",
        "\xf0\x9f\x91\xbd" => "\xf3\xbe\x86\xb0",
        "\xf0\x9f\x91\xbe" => "\xf3\xbe\x86\xb1",
        "\xf0\x9f\x91\xbf" => "\xf3\xbe\x86\xb2",
        "\xf0\x9f\x92\x80" => "\xf3\xbe\x86\xb3",
        "\xf0\x9f\x92\x81" => "\xf3\xbe\x86\xb4",
        "\xf0\x9f\x92\x82" => "\xf3\xbe\x86\xb5",
        "\xf0\x9f\x92\x83" => "\xf3\xbe\x86\xb6",
        "\xf0\x9f\x92\x84" => "\xf3\xbe\x86\x95",
        "\xf0\x9f\x92\x85" => "\xf3\xbe\x86\x96",
        "\xf0\x9f\x92\x86" => "\xf3\xbe\x86\x97",
        "\xf0\x9f\x92\x87" => "\xf3\xbe\x86\x98",
        "\xf0\x9f\x92\x88" => "\xf3\xbe\x86\x99",
        "\xf0\x9f\x92\x89" => "\xf3\xbe\x94\x89",
        "\xf0\x9f\x92\x8a" => "\xf3\xbe\x94\x8a",
        "\xf0\x9f\x92\x8b" => "\xf3\xbe\xa0\xa3",
        "\xf0\x9f\x92\x8c" => "\xf3\xbe\xa0\xa4",
        "\xf0\x9f\x92\x8d" => "\xf3\xbe\xa0\xa5",
        "\xf0\x9f\x92\x8e" => "\xf3\xbe\xa0\xa6",
        "\xf0\x9f\x92\x8f" => "\xf3\xbe\xa0\xa7",
        "\xf0\x9f\x92\x90" => "\xf3\xbe\xa0\xa8",
        "\xf0\x9f\x92\x91" => "\xf3\xbe\xa0\xa9",
        "\xf0\x9f\x92\x92" => "\xf3\xbe\xa0\xaa",
        "\xf0\x9f\x92\x93" => "\xf3\xbe\xac\x8d",
        "\xf0\x9f\x92\x94" => "\xf3\xbe\xac\x8e",
        "\xf0\x9f\x92\x95" => "\xf3\xbe\xac\x8f",
        "\xf0\x9f\x92\x96" => "\xf3\xbe\xac\x90",
        "\xf0\x9f\x92\x97" => "\xf3\xbe\xac\x91",
        "\xf0\x9f\x92\x98" => "\xf3\xbe\xac\x92",
        "\xf0\x9f\x92\x99" => "\xf3\xbe\xac\x93",
        "\xf0\x9f\x92\x9a" => "\xf3\xbe\xac\x94",
        "\xf0\x9f\x92\x9b" => "\xf3\xbe\xac\x95",
        "\xf0\x9f\x92\x9c" => "\xf3\xbe\xac\x96",
        "\xf0\x9f\x92\x9d" => "\xf3\xbe\xac\x97",
        "\xf0\x9f\x92\x9e" => "\xf3\xbe\xac\x98",
        "\xf0\x9f\x92\x9f" => "\xf3\xbe\xac\x99",
        "\xf0\x9f\x92\xa0" => "\xf3\xbe\xad\x95",
        "\xf0\x9f\x92\xa1" => "\xf3\xbe\xad\x96",
        "\xf0\x9f\x92\xa2" => "\xf3\xbe\xad\x97",
        "\xf0\x9f\x92\xa3" => "\xf3\xbe\xad\x98",
        "\xf0\x9f\x92\xa4" => "\xf3\xbe\xad\x99",
        "\xf0\x9f\x92\xa5" => "\xf3\xbe\xad\x9a",
        "\xf0\x9f\x92\xa6" => "\xf3\xbe\xad\x9b",
        "\xf0\x9f\x92\xa7" => "\xf3\xbe\xad\x9c",
        "\xf0\x9f\x92\xa8" => "\xf3\xbe\xad\x9d",
        "\xf0\x9f\x92\xa9" => "\xf3\xbe\x93\xb4",
        "\xf0\x9f\x92\xaa" => "\xf3\xbe\xad\x9e",
        "\xf0\x9f\x92\xab" => "\xf3\xbe\xad\x9f",
        "\xf0\x9f\x92\xac" => "\xf3\xbe\x94\xb2",
        "\xf0\x9f\x92\xad" => "",
        "\xf0\x9f\x92\xae" => "\xf3\xbe\xad\xba",
        "\xf0\x9f\x92\xaf" => "\xf3\xbe\xad\xbb",
        "\xf0\x9f\x92\xb0" => "\xf3\xbe\x93\x9d",
        "\xf0\x9f\x92\xb1" => "\xf3\xbe\x93\x9e",
        "\xf0\x9f\x92\xb2" => "\xf3\xbe\x93\xa0",
        "\xf0\x9f\x92\xb3" => "\xf3\xbe\x93\xa1",
        "\xf0\x9f\x92\xb4" => "\xf3\xbe\x93\xa2",
        "\xf0\x9f\x92\xb5" => "\xf3\xbe\x93\xa3",
        "\xf0\x9f\x92\xb6" => "",
        "\xf0\x9f\x92\xb7" => "",
        "\xf0\x9f\x92\xb8" => "\xf3\xbe\x93\xa4",
        "\xf0\x9f\x92\xb9" => "\xf3\xbe\x93\x9f",
        "\xf0\x9f\x92\xba" => "\xf3\xbe\x94\xb7",
        "\xf0\x9f\x92\xbb" => "\xf3\xbe\x94\xb8",
        "\xf0\x9f\x92\xbc" => "\xf3\xbe\x94\xbb",
        "\xf0\x9f\x92\xbd" => "\xf3\xbe\x94\xbc",
        "\xf0\x9f\x92\xbe" => "\xf3\xbe\x94\xbd",
        "\xf0\x9f\x92\xbf" => "\xf3\xbe\xa0\x9d",
        "\xf0\x9f\x93\x80" => "\xf3\xbe\xa0\x9e",
        "\xf0\x9f\x93\x81" => "\xf3\xbe\x95\x83",
        "\xf0\x9f\x93\x82" => "\xf3\xbe\x95\x84",
        "\xf0\x9f\x93\x83" => "\xf3\xbe\x95\x80",
        "\xf0\x9f\x93\x84" => "\xf3\xbe\x95\x81",
        "\xf0\x9f\x93\x85" => "\xf3\xbe\x95\x82",
        "\xf0\x9f\x93\x86" => "\xf3\xbe\x95\x89",
        "\xf0\x9f\x93\x87" => "\xf3\xbe\x95\x8d",
        "\xf0\x9f\x93\x88" => "\xf3\xbe\x95\x8b",
        "\xf0\x9f\x93\x89" => "\xf3\xbe\x95\x8c",
        "\xf0\x9f\x93\x8a" => "\xf3\xbe\x95\x8a",
        "\xf0\x9f\x93\x8b" => "\xf3\xbe\x95\x88",
        "\xf0\x9f\x93\x8c" => "\xf3\xbe\x95\x8e",
        "\xf0\x9f\x93\x8d" => "\xf3\xbe\x94\xbf",
        "\xf0\x9f\x93\x8e" => "\xf3\xbe\x94\xba",
        "\xf0\x9f\x93\x8f" => "\xf3\xbe\x95\x90",
        "\xf0\x9f\x93\x90" => "\xf3\xbe\x95\x91",
        "\xf0\x9f\x93\x91" => "\xf3\xbe\x95\x92",
        "\xf0\x9f\x93\x92" => "\xf3\xbe\x95\x8f",
        "\xf0\x9f\x93\x93" => "\xf3\xbe\x95\x85",
        "\xf0\x9f\x93\x94" => "\xf3\xbe\x95\x87",
        "\xf0\x9f\x93\x95" => "\xf3\xbe\x94\x82",
        "\xf0\x9f\x93\x96" => "\xf3\xbe\x95\x86",
        "\xf0\x9f\x93\x97" => "\xf3\xbe\x93\xbf",
        "\xf0\x9f\x93\x98" => "\xf3\xbe\x94\x80",
        "\xf0\x9f\x93\x99" => "\xf3\xbe\x94\x81",
        "\xf0\x9f\x93\x9a" => "\xf3\xbe\x94\x83",
        "\xf0\x9f\x93\x9b" => "\xf3\xbe\x94\x84",
        "\xf0\x9f\x93\x9c" => "\xf3\xbe\x93\xbd",
        "\xf0\x9f\x93\x9d" => "\xf3\xbe\x94\xa7",
        "\xf0\x9f\x93\x9e" => "\xf3\xbe\x94\xa4",
        "\xf0\x9f\x93\x9f" => "\xf3\xbe\x94\xa2",
        "\xf0\x9f\x93\xa0" => "\xf3\xbe\x94\xa8",
        "\xf0\x9f\x93\xa1" => "\xf3\xbe\x94\xb1",
        "\xf0\x9f\x93\xa2" => "\xf3\xbe\x94\xaf",
        "\xf0\x9f\x93\xa3" => "\xf3\xbe\x94\xb0",
        "\xf0\x9f\x93\xa4" => "\xf3\xbe\x94\xb3",
        "\xf0\x9f\x93\xa5" => "\xf3\xbe\x94\xb4",
        "\xf0\x9f\x93\xa6" => "\xf3\xbe\x94\xb5",
        "\xf0\x9f\x93\xa7" => "\xf3\xbe\xae\x92",
        "\xf0\x9f\x93\xa8" => "\xf3\xbe\x94\xaa",
        "\xf0\x9f\x93\xa9" => "\xf3\xbe\x94\xab",
        "\xf0\x9f\x93\xaa" => "\xf3\xbe\x94\xac",
        "\xf0\x9f\x93\xab" => "\xf3\xbe\x94\xad",
        "\xf0\x9f\x93\xac" => "",
        "\xf0\x9f\x93\xad" => "",
        "\xf0\x9f\x93\xae" => "\xf3\xbe\x94\xae",
        "\xf0\x9f\x93\xaf" => "",
        "\xf0\x9f\x93\xb0" => "\xf3\xbe\xa0\xa2",
        "\xf0\x9f\x93\xb1" => "\xf3\xbe\x94\xa5",
        "\xf0\x9f\x93\xb2" => "\xf3\xbe\x94\xa6",
        "\xf0\x9f\x93\xb3" => "\xf3\xbe\xa0\xb9",
        "\xf0\x9f\x93\xb4" => "\xf3\xbe\xa0\xba",
        "\xf0\x9f\x93\xb5" => "",
        "\xf0\x9f\x93\xb6" => "\xf3\xbe\xa0\xb8",
        "\xf0\x9f\x93\xb7" => "\xf3\xbe\x93\xaf",
        "\xf0\x9f\x93\xb8" => "",
        "\xf0\x9f\x93\xb9" => "\xf3\xbe\x93\xb9",
        "\xf0\x9f\x93\xba" => "\xf3\xbe\xa0\x9c",
        "\xf0\x9f\x93\xbb" => "\xf3\xbe\xa0\x9f",
        "\xf0\x9f\x93\xbc" => "\xf3\xbe\xa0\xa0",
        "\xf0\x9f\x93\xbd" => "",
        "\xf0\x9f\x93\xbf" => "",
        "\xf0\x9f\x94\x80" => "",
        "\xf0\x9f\x94\x81" => "",
        "\xf0\x9f\x94\x82" => "",
        "\xf0\x9f\x94\x83" => "\xf3\xbe\xae\x91",
        "\xf0\x9f\x94\x84" => "",
        "\xf0\x9f\x94\x85" => "",
        "\xf0\x9f\x94\x86" => "",
        "\xf0\x9f\x94\x87" => "",
        "\xf0\x9f\x94\x88" => "",
        "\xf0\x9f\x94\x89" => "",
        "\xf0\x9f\x94\x8a" => "\xf3\xbe\xa0\xa1",
        "\xf0\x9f\x94\x8b" => "\xf3\xbe\x93\xbc",
        "\xf0\x9f\x94\x8c" => "\xf3\xbe\x93\xbe",
        "\xf0\x9f\x94\x8d" => "\xf3\xbe\xae\x85",
        "\xf0\x9f\x94\x8e" => "\xf3\xbe\xae\x8d",
        "\xf0\x9f\x94\x8f" => "\xf3\xbe\xae\x90",
        "\xf0\x9f\x94\x90" => "\xf3\xbe\xae\x8a",
        "\xf0\x9f\x94\x91" => "\xf3\xbe\xae\x82",
        "\xf0\x9f\x94\x92" => "\xf3\xbe\xae\x86",
        "\xf0\x9f\x94\x93" => "\xf3\xbe\xae\x87",
        "\xf0\x9f\x94\x94" => "\xf3\xbe\x93\xb2",
        "\xf0\x9f\x94\x95" => "",
        "\xf0\x9f\x94\x96" => "\xf3\xbe\xae\x8f",
        "\xf0\x9f\x94\x97" => "\xf3\xbe\xad\x8b",
        "\xf0\x9f\x94\x98" => "\xf3\xbe\xae\x8c",
        "\xf0\x9f\x94\x99" => "\xf3\xbe\xae\x8e",
        "\xf0\x9f\x94\x9a" => "\xf3\xbe\x80\x9a",
        "\xf0\x9f\x94\x9b" => "\xf3\xbe\x80\x99",
        "\xf0\x9f\x94\x9c" => "\xf3\xbe\x80\x98",
        "\xf0\x9f\x94\x9d" => "\xf3\xbe\xad\x82",
        "\xf0\x9f\x94\x9e" => "\xf3\xbe\xac\xa5",
        "\xf0\x9f\x94\x9f" => "\xf3\xbe\xa0\xbb",
        "\xf0\x9f\x94\xa0" => "\xf3\xbe\xad\xbc",
        "\xf0\x9f\x94\xa1" => "\xf3\xbe\xad\xbd",
        "\xf0\x9f\x94\xa2" => "\xf3\xbe\xad\xbe",
        "\xf0\x9f\x94\xa3" => "\xf3\xbe\xad\xbf",
        "\xf0\x9f\x94\xa4" => "\xf3\xbe\xae\x80",
        "\xf0\x9f\x94\xa5" => "\xf3\xbe\x93\xb6",
        "\xf0\x9f\x94\xa6" => "\xf3\xbe\x93\xbb",
        "\xf0\x9f\x94\xa7" => "\xf3\xbe\x93\x89",
        "\xf0\x9f\x94\xa8" => "\xf3\xbe\x93\x8a",
        "\xf0\x9f\x94\xa9" => "\xf3\xbe\x93\x8b",
        "\xf0\x9f\x94\xaa" => "\xf3\xbe\x93\xba",
        "\xf0\x9f\x94\xab" => "\xf3\xbe\x93\xb5",
        "\xf0\x9f\x94\xac" => "",
        "\xf0\x9f\x94\xad" => "",
        "\xf0\x9f\x94\xae" => "\xf3\xbe\x93\xb7",
        "\xf0\x9f\x94\xaf" => "\xf3\xbe\x93\xb8",
        "\xf0\x9f\x94\xb0" => "\xf3\xbe\x81\x84",
        "\xf0\x9f\x94\xb1" => "\xf3\xbe\x93\x92",
        "\xf0\x9f\x94\xb2" => "\xf3\xbe\xad\xa4",
        "\xf0\x9f\x94\xb3" => "\xf3\xbe\xad\xa7",
        "\xf0\x9f\x94\xb4" => "\xf3\xbe\xad\xa3",
        "\xf0\x9f\x94\xb5" => "\xf3\xbe\xad\xa4",
        "\xf0\x9f\x94\xb6" => "\xf3\xbe\xad\xb3",
        "\xf0\x9f\x94\xb7" => "\xf3\xbe\xad\xb4",
        "\xf0\x9f\x94\xb8" => "\xf3\xbe\xad\xb5",
        "\xf0\x9f\x94\xb9" => "\xf3\xbe\xad\xb6",
        "\xf0\x9f\x94\xba" => "\xf3\xbe\xad\xb8",
        "\xf0\x9f\x94\xbb" => "\xf3\xbe\xad\xb9",
        "\xf0\x9f\x94\xbc" => "\xf3\xbe\xac\x81",
        "\xf0\x9f\x94\xbd" => "\xf3\xbe\xac\x80",
        "\xf0\x9f\x95\x89" => "",
        "\xf0\x9f\x95\x8a" => "",
        "\xf0\x9f\x95\x8b" => "",
        "\xf0\x9f\x95\x8c" => "",
        "\xf0\x9f\x95\x8d" => "",
        "\xf0\x9f\x95\x8e" => "",
        "\xf0\x9f\x95\x90" => "\xf3\xbe\x80\x9e",
        "\xf0\x9f\x95\x91" => "\xf3\xbe\x80\x9f",
        "\xf0\x9f\x95\x92" => "\xf3\xbe\x80\xa0",
        "\xf0\x9f\x95\x93" => "\xf3\xbe\x80\xa1",
        "\xf0\x9f\x95\x94" => "\xf3\xbe\x80\xa2",
        "\xf0\x9f\x95\x95" => "\xf3\xbe\x80\xa3",
        "\xf0\x9f\x95\x96" => "\xf3\xbe\x80\xa4",
        "\xf0\x9f\x95\x97" => "\xf3\xbe\x80\xa5",
        "\xf0\x9f\x95\x98" => "\xf3\xbe\x80\xa6",
        "\xf0\x9f\x95\x99" => "\xf3\xbe\x80\xa7",
        "\xf0\x9f\x95\x9a" => "\xf3\xbe\x80\xa8",
        "\xf0\x9f\x95\x9b" => "\xf3\xbe\x80\xa9",
        "\xf0\x9f\x95\x9c" => "",
        "\xf0\x9f\x95\x9d" => "",
        "\xf0\x9f\x95\x9e" => "",
        "\xf0\x9f\x95\x9f" => "",
        "\xf0\x9f\x95\xa0" => "",
        "\xf0\x9f\x95\xa1" => "",
        "\xf0\x9f\x95\xa2" => "",
        "\xf0\x9f\x95\xa3" => "",
        "\xf0\x9f\x95\xa4" => "",
        "\xf0\x9f\x95\xa5" => "",
        "\xf0\x9f\x95\xa6" => "",
        "\xf0\x9f\x95\xa7" => "",
        "\xf0\x9f\x95\xaf" => "",
        "\xf0\x9f\x95\xb0" => "",
        "\xf0\x9f\x95\xb3" => "",
        "\xf0\x9f\x95\xb4" => "",
        "\xf0\x9f\x95\xb5" => "",
        "\xf0\x9f\x95\xb6" => "",
        "\xf0\x9f\x95\xb7" => "",
        "\xf0\x9f\x95\xb8" => "",
        "\xf0\x9f\x95\xb9" => "",
        "\xf0\x9f\x96\x87" => "",
        "\xf0\x9f\x96\x8a" => "",
        "\xf0\x9f\x96\x8b" => "",
        "\xf0\x9f\x96\x8c" => "",
        "\xf0\x9f\x96\x8d" => "",
        "\xf0\x9f\x96\x90" => "",
        "\xf0\x9f\x96\x95" => "",
        "\xf0\x9f\x96\x96" => "",
        "\xf0\x9f\x96\xa5" => "",
        "\xf0\x9f\x96\xa8" => "",
        "\xf0\x9f\x96\xb1" => "",
        "\xf0\x9f\x96\xb2" => "",
        "\xf0\x9f\x96\xbc" => "",
        "\xf0\x9f\x97\x82" => "",
        "\xf0\x9f\x97\x83" => "",
        "\xf0\x9f\x97\x84" => "",
        "\xf0\x9f\x97\x91" => "",
        "\xf0\x9f\x97\x92" => "",
        "\xf0\x9f\x97\x93" => "",
        "\xf0\x9f\x97\x9c" => "",
        "\xf0\x9f\x97\x9d" => "",
        "\xf0\x9f\x97\x9e" => "",
        "\xf0\x9f\x97\xa1" => "",
        "\xf0\x9f\x97\xa3" => "",
        "\xf0\x9f\x97\xa8" => "",
        "\xf0\x9f\x97\xaf" => "",
        "\xf0\x9f\x97\xb3" => "",
        "\xf0\x9f\x97\xba" => "",
        "\xf0\x9f\x97\xbb" => "\xf3\xbe\x93\x83",
        "\xf0\x9f\x97\xbc" => "\xf3\xbe\x93\x84",
        "\xf0\x9f\x97\xbd" => "\xf3\xbe\x93\x86",
        "\xf0\x9f\x97\xbe" => "\xf3\xbe\x93\x87",
        "\xf0\x9f\x97\xbf" => "\xf3\xbe\x93\x88",
        "\xf0\x9f\x98\x80" => "",
        "\xf0\x9f\x98\x81" => "\xf3\xbe\x8c\xb3",
        "\xf0\x9f\x98\x82" => "\xf3\xbe\x8c\xb4",
        "\xf0\x9f\x98\x83" => "\xf3\xbe\x8c\xb0",
        "\xf0\x9f\x98\x84" => "\xf3\xbe\x8c\xb8",
        "\xf0\x9f\x98\x85" => "\xf3\xbe\x8c\xb1",
        "\xf0\x9f\x98\x86" => "\xf3\xbe\x8c\xb2",
        "\xf0\x9f\x98\x87" => "",
        "\xf0\x9f\x98\x88" => "",
        "\xf0\x9f\x98\x89" => "\xf3\xbe\x8d\x87",
        "\xf0\x9f\x98\x8a" => "\xf3\xbe\x8c\xb5",
        "\xf0\x9f\x98\x8b" => "\xf3\xbe\x8c\xab",
        "\xf0\x9f\x98\x8c" => "\xf3\xbe\x8c\xbe",
        "\xf0\x9f\x98\x8d" => "\xf3\xbe\x8c\xa7",
        "\xf0\x9f\x98\x8e" => "",
        "\xf0\x9f\x98\x8f" => "\xf3\xbe\x8d\x83",
        "\xf0\x9f\x98\x90" => "",
        "\xf0\x9f\x98\x91" => "",
        "\xf0\x9f\x98\x92" => "\xf3\xbe\x8c\xa6",
        "\xf0\x9f\x98\x93" => "\xf3\xbe\x8d\x84",
        "\xf0\x9f\x98\x94" => "\xf3\xbe\x8d\x80",
        "\xf0\x9f\x98\x95" => "",
        "\xf0\x9f\x98\x96" => "\xf3\xbe\x8c\xbf",
        "\xf0\x9f\x98\x97" => "",
        "\xf0\x9f\x98\x98" => "\xf3\xbe\x8c\xac",
        "\xf0\x9f\x98\x99" => "",
        "\xf0\x9f\x98\x9a" => "\xf3\xbe\x8c\xad",
        "\xf0\x9f\x98\x9b" => "",
        "\xf0\x9f\x98\x9c" => "\xf3\xbe\x8c\xa9",
        "\xf0\x9f\x98\x9d" => "\xf3\xbe\x8c\xaa",
        "\xf0\x9f\x98\x9e" => "\xf3\xbe\x8c\xa3",
        "\xf0\x9f\x98\x9f" => "",
        "\xf0\x9f\x98\xa0" => "\xf3\xbe\x8c\xa0",
        "\xf0\x9f\x98\xa1" => "\xf3\xbe\x8c\xbd",
        "\xf0\x9f\x98\xa2" => "\xf3\xbe\x8c\xb9",
        "\xf0\x9f\x98\xa3" => "\xf3\xbe\x8c\xbc",
        "\xf0\x9f\x98\xa4" => "\xf3\xbe\x8c\xa8",
        "\xf0\x9f\x98\xa5" => "\xf3\xbe\x8d\x85",
        "\xf0\x9f\x98\xa6" => "",
        "\xf0\x9f\x98\xa7" => "",
        "\xf0\x9f\x98\xa8" => "\xf3\xbe\x8c\xbb",
        "\xf0\x9f\x98\xa9" => "\xf3\xbe\x8c\xa1",
        "\xf0\x9f\x98\xaa" => "\xf3\xbe\x8d\x82",
        "\xf0\x9f\x98\xab" => "\xf3\xbe\x8d\x86",
        "\xf0\x9f\x98\xac" => "",
        "\xf0\x9f\x98\xad" => "\xf3\xbe\x8c\xba",
        "\xf0\x9f\x98\xae" => "",
        "\xf0\x9f\x98\xaf" => "",
        "\xf0\x9f\x98\xb0" => "\xf3\xbe\x8c\xa5",
        "\xf0\x9f\x98\xb1" => "\xf3\xbe\x8d\x81",
        "\xf0\x9f\x98\xb2" => "\xf3\xbe\x8c\xa2",
        "\xf0\x9f\x98\xb3" => "\xf3\xbe\x8c\xaf",
        "\xf0\x9f\x98\xb4" => "",
        "\xf0\x9f\x98\xb5" => "\xf3\xbe\x8c\xa4",
        "\xf0\x9f\x98\xb6" => "",
        "\xf0\x9f\x98\xb7" => "\xf3\xbe\x8c\xae",
        "\xf0\x9f\x98\xb8" => "\xf3\xbe\x8d\x89",
        "\xf0\x9f\x98\xb9" => "\xf3\xbe\x8d\x8a",
        "\xf0\x9f\x98\xba" => "\xf3\xbe\x8d\x88",
        "\xf0\x9f\x98\xbb" => "\xf3\xbe\x8d\x8c",
        "\xf0\x9f\x98\xbc" => "\xf3\xbe\x8d\x8f",
        "\xf0\x9f\x98\xbd" => "\xf3\xbe\x8d\x8b",
        "\xf0\x9f\x98\xbe" => "\xf3\xbe\x8d\x8e",
        "\xf0\x9f\x98\xbf" => "\xf3\xbe\x8d\x8d",
        "\xf0\x9f\x99\x80" => "\xf3\xbe\x8d\x90",
        "\xf0\x9f\x99\x81" => "",
        "\xf0\x9f\x99\x82" => "",
        "\xf0\x9f\x99\x83" => "",
        "\xf0\x9f\x99\x84" => "",
        "\xf0\x9f\x99\x85" => "\xf3\xbe\x8d\x91",
        "\xf0\x9f\x99\x86" => "\xf3\xbe\x8d\x92",
        "\xf0\x9f\x99\x87" => "\xf3\xbe\x8d\x93",
        "\xf0\x9f\x99\x88" => "\xf3\xbe\x8d\x94",
        "\xf0\x9f\x99\x89" => "\xf3\xbe\x8d\x96",
        "\xf0\x9f\x99\x8a" => "\xf3\xbe\x8d\x95",
        "\xf0\x9f\x99\x8b" => "\xf3\xbe\x8d\x97",
        "\xf0\x9f\x99\x8c" => "\xf3\xbe\x8d\x98",
        "\xf0\x9f\x99\x8d" => "\xf3\xbe\x8d\x99",
        "\xf0\x9f\x99\x8e" => "\xf3\xbe\x8d\x9a",
        "\xf0\x9f\x99\x8f" => "\xf3\xbe\x8d\x9b",
        "\xf0\x9f\x9a\x80" => "\xf3\xbe\x9f\xad",
        "\xf0\x9f\x9a\x81" => "",
        "\xf0\x9f\x9a\x82" => "",
        "\xf0\x9f\x9a\x83" => "\xf3\xbe\x9f\x9f",
        "\xf0\x9f\x9a\x84" => "\xf3\xbe\x9f\xa2",
        "\xf0\x9f\x9a\x85" => "\xf3\xbe\x9f\xa3",
        "\xf0\x9f\x9a\x86" => "",
        "\xf0\x9f\x9a\x87" => "\xf3\xbe\x9f\xa0",
        "\xf0\x9f\x9a\x88" => "",
        "\xf0\x9f\x9a\x89" => "\xf3\xbe\x9f\xac",
        "\xf0\x9f\x9a\x8a" => "",
        "\xf0\x9f\x9a\x8b" => "",
        "\xf0\x9f\x9a\x8c" => "\xf3\xbe\x9f\xa6",
        "\xf0\x9f\x9a\x8d" => "",
        "\xf0\x9f\x9a\x8e" => "",
        "\xf0\x9f\x9a\x8f" => "\xf3\xbe\x9f\xa7",
        "\xf0\x9f\x9a\x90" => "",
        "\xf0\x9f\x9a\x91" => "\xf3\xbe\x9f\xb3",
        "\xf0\x9f\x9a\x92" => "\xf3\xbe\x9f\xb2",
        "\xf0\x9f\x9a\x93" => "\xf3\xbe\x9f\xb4",
        "\xf0\x9f\x9a\x94" => "",
        "\xf0\x9f\x9a\x95" => "\xf3\xbe\x9f\xaf",
        "\xf0\x9f\x9a\x96" => "",
        "\xf0\x9f\x9a\x97" => "\xf3\xbe\x9f\xa4",
        "\xf0\x9f\x9a\x98" => "",
        "\xf0\x9f\x9a\x99" => "\xf3\xbe\x9f\xa5",
        "\xf0\x9f\x9a\x9a" => "\xf3\xbe\x9f\xb1",
        "\xf0\x9f\x9a\x9b" => "",
        "\xf0\x9f\x9a\x9c" => "",
        "\xf0\x9f\x9a\x9d" => "",
        "\xf0\x9f\x9a\x9e" => "",
        "\xf0\x9f\x9a\x9f" => "",
        "\xf0\x9f\x9a\xa0" => "",
        "\xf0\x9f\x9a\xa1" => "",
        "\xf0\x9f\x9a\xa2" => "\xf3\xbe\x9f\xa8",
        "\xf0\x9f\x9a\xa3" => "",
        "\xf0\x9f\x9a\xa4" => "\xf3\xbe\x9f\xae",
        "\xf0\x9f\x9a\xa5" => "\xf3\xbe\x9f\xb7",
        "\xf0\x9f\x9a\xa6" => "",
        "\xf0\x9f\x9a\xa7" => "\xf3\xbe\x9f\xb8",
        "\xf0\x9f\x9a\xa8" => "\xf3\xbe\x9f\xb9",
        "\xf0\x9f\x9a\xa9" => "\xf3\xbe\xac\xa2",
        "\xf0\x9f\x9a\xaa" => "\xf3\xbe\x93\xb3",
        "\xf0\x9f\x9a\xab" => "\xf3\xbe\xad\x88",
        "\xf0\x9f\x9a\xac" => "\xf3\xbe\xac\x9e",
        "\xf0\x9f\x9a\xad" => "\xf3\xbe\xac\x9f",
        "\xf0\x9f\x9a\xae" => "",
        "\xf0\x9f\x9a\xaf" => "",
        "\xf0\x9f\x9a\xb0" => "",
        "\xf0\x9f\x9a\xb1" => "",
        "\xf0\x9f\x9a\xb2" => "\xf3\xbe\x9f\xab",
        "\xf0\x9f\x9a\xb3" => "",
        "\xf0\x9f\x9a\xb4" => "",
        "\xf0\x9f\x9a\xb5" => "",
        "\xf0\x9f\x9a\xb6" => "\xf3\xbe\x9f\xb0",
        "\xf0\x9f\x9a\xb7" => "",
        "\xf0\x9f\x9a\xb8" => "",
        "\xf0\x9f\x9a\xb9" => "\xf3\xbe\xac\xb3",
        "\xf0\x9f\x9a\xba" => "\xf3\xbe\xac\xb4",
        "\xf0\x9f\x9a\xbb" => "\xf3\xbe\x94\x86",
        "\xf0\x9f\x9a\xbc" => "\xf3\xbe\xac\xb5",
        "\xf0\x9f\x9a\xbd" => "\xf3\xbe\x94\x87",
        "\xf0\x9f\x9a\xbe" => "\xf3\xbe\x94\x88",
        "\xf0\x9f\x9a\xbf" => "",
        "\xf0\x9f\x9b\x80" => "\xf3\xbe\x94\x85",
        "\xf0\x9f\x9b\x81" => "",
        "\xf0\x9f\x9b\x82" => "",
        "\xf0\x9f\x9b\x83" => "",
        "\xf0\x9f\x9b\x84" => "",
        "\xf0\x9f\x9b\x85" => "",
        "\xf0\x9f\x9b\x8b" => "",
        "\xf0\x9f\x9b\x8c" => "",
        "\xf0\x9f\x9b\x8d" => "",
        "\xf0\x9f\x9b\x8e" => "",
        "\xf0\x9f\x9b\x8f" => "",
        "\xf0\x9f\x9b\x90" => "",
        "\xf0\x9f\x9b\xa0" => "",
        "\xf0\x9f\x9b\xa1" => "",
        "\xf0\x9f\x9b\xa2" => "",
        "\xf0\x9f\x9b\xa3" => "",
        "\xf0\x9f\x9b\xa4" => "",
        "\xf0\x9f\x9b\xa5" => "",
        "\xf0\x9f\x9b\xa9" => "",
        "\xf0\x9f\x9b\xab" => "",
        "\xf0\x9f\x9b\xac" => "",
        "\xf0\x9f\x9b\xb0" => "",
        "\xf0\x9f\x9b\xb3" => "",
        "\xf0\x9f\xa4\x90" => "",
        "\xf0\x9f\xa4\x91" => "",
        "\xf0\x9f\xa4\x92" => "",
        "\xf0\x9f\xa4\x93" => "",
        "\xf0\x9f\xa4\x94" => "",
        "\xf0\x9f\xa4\x95" => "",
        "\xf0\x9f\xa4\x96" => "",
        "\xf0\x9f\xa4\x97" => "",
        "\xf0\x9f\xa4\x98" => "",
        "\xf0\x9f\xa6\x80" => "",
        "\xf0\x9f\xa6\x81" => "",
        "\xf0\x9f\xa6\x82" => "",
        "\xf0\x9f\xa6\x83" => "",
        "\xf0\x9f\xa6\x84" => "",
        "\xf0\x9f\xa7\x80" => "",
        "#\xe2\x83\xa3" => "\xf3\xbe\xa0\xac",
        "*\xe2\x83\xa3" => "",
        "0\xe2\x83\xa3" => "\xf3\xbe\xa0\xb7",
        "1\xe2\x83\xa3" => "\xf3\xbe\xa0\xae",
        "2\xe2\x83\xa3" => "\xf3\xbe\xa0\xaf",
        "3\xe2\x83\xa3" => "\xf3\xbe\xa0\xb0",
        "4\xe2\x83\xa3" => "\xf3\xbe\xa0\xb1",
        "5\xe2\x83\xa3" => "\xf3\xbe\xa0\xb2",
        "6\xe2\x83\xa3" => "\xf3\xbe\xa0\xb3",
        "7\xe2\x83\xa3" => "\xf3\xbe\xa0\xb4",
        "8\xe2\x83\xa3" => "\xf3\xbe\xa0\xb5",
        "9\xe2\x83\xa3" => "\xf3\xbe\xa0\xb6",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa7" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb3" => "\xf3\xbe\x93\xad",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaa" => "\xf3\xbe\x93\xa8",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb8" => "\xf3\xbe\x93\xab",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb7" => "\xf3\xbe\x93\xa7",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa7" => "\xf3\xbe\x93\xaa",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb9" => "\xf3\xbe\x93\xa9",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb5" => "\xf3\xbe\x93\xa5",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb7" => "\xf3\xbe\x93\xae",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa7" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb6" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb5" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb4\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb6\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xba" => "\xf3\xbe\x93\xac",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa7" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbd" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa9" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xad" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xaf" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb1" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb4" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb7" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbb" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xb8" => "\xf3\xbe\x93\xa6",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xbe" => "",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xbf" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xa8" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xac" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xae" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xb3" => "",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xba" => "",
        "\xf0\x9f\x87\xbc\xf0\x9f\x87\xab" => "",
        "\xf0\x9f\x87\xbc\xf0\x9f\x87\xb8" => "",
        "\xf0\x9f\x87\xbd\xf0\x9f\x87\xb0" => "",
        "\xf0\x9f\x87\xbe\xf0\x9f\x87\xaa" => "",
        "\xf0\x9f\x87\xbe\xf0\x9f\x87\xb9" => "",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xa6" => "",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xb2" => "",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xbc" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x91\xa8" => "",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x92\x8b\xe2\x80\x8d\xf0\x9f\x91\xa8" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x91\xa9" => "",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x92\x8b\xe2\x80\x8d\xf0\x9f\x91\xa9" => "",
    ),
    'docomo_to_unified' => array(
        "\xee\x9c\xb1" => "\xc2\xa9",
        "\xee\x9c\xb6" => "\xc2\xae",
        "\xee\x9c\x84" => "\xe2\x80\xbc",
        "\xee\x9c\x83" => "\xe2\x81\x89",
        "\xee\x9c\xb2" => "\xe2\x84\xa2",
        "\xee\x9c\xbc" => "\xe2\x86\x94",
        "\xee\x9c\xbd" => "\xe2\x86\x95",
        "\xee\x9a\x97" => "\xe2\x86\x96",
        "\xee\x99\xb8" => "\xe2\x86\x97",
        "\xee\x9a\x96" => "\xe2\x86\x98",
        "\xee\x9a\xa5" => "\xe2\x86\x99",
        "\xee\x9b\x9a" => "\xe2\x86\xa9",
        "\xee\x9c\x9f" => "\xe2\x8c\x9a",
        "\xee\x9c\x9c" => "\xe2\x8f\xb3",
        "\xee\x9a\xba" => "\xf0\x9f\x95\x9b",
        "\xee\x99\x9c" => "\xf0\x9f\x9a\x87",
        "\xee\x98\xbe" => "\xf0\x9f\x8c\x87",
        "\xee\x98\xbf" => "\xe2\x98\x81",
        "\xee\x9a\x87" => "\xf0\x9f\x93\x9e",
        "\xee\x99\x80" => "\xe2\x98\x94",
        "\xee\x99\xb0" => "\xe2\x98\x95",
        "\xee\x9b\xb0" => "\xf0\x9f\x98\xba",
        "\xee\x99\x86" => "\xe2\x99\x88",
        "\xee\x99\x87" => "\xe2\x99\x89",
        "\xee\x99\x88" => "\xe2\x99\x8a",
        "\xee\x99\x89" => "\xe2\x99\x8b",
        "\xee\x99\x8a" => "\xe2\x99\x8c",
        "\xee\x99\x8b" => "\xe2\x99\x8d",
        "\xee\x99\x8c" => "\xe2\x99\x8e",
        "\xee\x99\x8d" => "\xe2\x99\x8f",
        "\xee\x99\x8e" => "\xe2\x99\x90",
        "\xee\x99\x8f" => "\xe2\x99\x91",
        "\xee\x99\x90" => "\xe2\x99\x92",
        "\xee\x99\x91" => "\xe2\x99\x93",
        "\xee\x9a\x8e" => "\xe2\x99\xa0",
        "\xee\x9a\x90" => "\xe2\x99\xa3",
        "\xee\x9a\x8d" => "\xe2\x99\xa5",
        "\xee\x9a\x8f" => "\xe2\x99\xa6",
        "\xee\x9b\xb7" => "\xf0\x9f\x9b\x80",
        "\xee\x9c\xb5" => "\xf0\x9f\x94\x83",
        "\xee\x9a\x9b" => "\xe2\x99\xbf",
        "\xee\x99\xa1" => "\xf0\x9f\x9a\xa2",
        "\xee\x9c\xb7" => "\xe2\x9a\xa0",
        "\xee\x99\x82" => "\xe2\x9a\xa1",
        "\xee\x9a\x9c" => "\xf0\x9f\x94\xb5",
        "\xee\x99\x96" => "\xe2\x9a\xbd",
        "\xee\x99\x93" => "\xe2\x9a\xbe",
        "\xee\x99\x81" => "\xe2\x9b\x84",
        "\xee\x98\xbe\xee\x98\xbf" => "\xe2\x9b\x85",
        "\xee\x9c\xaf" => "\xf0\x9f\x99\x85",
        "\xee\x99\x94" => "\xe2\x9b\xb3",
        "\xee\x9a\xa3" => "\xf0\x9f\x9a\xa4",
        "\xee\x99\xab" => "\xe2\x9b\xbd",
        "\xee\x99\xb5" => "\xf0\x9f\x92\x87",
        "\xee\x99\xa2" => "\xe2\x9c\x88",
        "\xee\x9b\x93" => "\xf0\x9f\x93\xa7",
        "\xee\x9a\x93" => "\xe2\x9c\x8a",
        "\xee\x9a\x95" => "\xf0\x9f\x91\x90",
        "\xee\x9a\x94" => "\xe2\x9c\x8c",
        "\xee\x9c\x99" => "\xe2\x9c\x8f",
        "\xee\x9a\xae" => "\xe2\x9c\x92",
        "\xee\x9b\xba" => "\xe2\x9d\x87",
        "\xee\x9b\xb8" => "\xf0\x9f\x92\xa0",
        "\xee\x9c\x82" => "\xe2\x9d\x97",
        "\xee\x9b\xac" => "\xf0\x9f\x92\x9d",
        "\xee\x9c\x8a" => "\xf0\x9f\x93\x9c",
        "\xee\x9b\x9f" => "\xe2\x9e\xbf",
        "\xee\x9b\xb5" => "\xe2\xa4\xb4",
        "\xee\x9c\x80" => "\xf0\x9f\x91\x8e",
        "\xee\x9a\xa0" => "\xf0\x9f\x8c\x95",
        "\xee\x9c\x89" => "\xe3\x80\xb0",
        "\xee\x9c\xb4" => "\xe3\x8a\x99",
        "\xee\x99\xac" => "\xf0\x9f\x85\xbf",
        "\xee\x9b\x9b" => "\xf0\x9f\x86\x91",
        "\xee\x9b\x97" => "\xf0\x9f\x86\x93",
        "\xee\x9b\x98" => "\xf0\x9f\x86\x94",
        "\xee\x9b\x9d" => "\xf0\x9f\x86\x95",
        "\xee\x9c\x8b" => "\xf0\x9f\x99\x86",
        "\xee\x9c\xb8" => "\xf0\x9f\x9a\xab",
        "\xee\x9c\xb9" => "\xf0\x9f\x88\xb3",
        "\xee\x9c\xba" => "\xf0\x9f\x88\xb4",
        "\xee\x9c\xbb" => "\xf0\x9f\x88\xb5",
        "\xee\x99\x83" => "\xf0\x9f\x8d\xa5",
        "\xee\x99\x84" => "\xf0\x9f\x8c\x81",
        "\xee\x99\x85" => "\xf0\x9f\x8c\x82",
        "\xee\x9a\xb3" => "\xf0\x9f\x8c\x8c",
        "\xee\x9c\xbf" => "\xf0\x9f\x8c\x8a",
        "\xee\x9a\x9e" => "\xf0\x9f\x8c\x9b",
        "\xee\x9a\x9d" => "\xf0\x9f\x8c\x94",
        "\xee\x9a\x9f" => "\xf0\x9f\x8c\x99",
        "\xee\x9d\x86" => "\xf0\x9f\x8c\xb1",
        "\xee\x9d\x83" => "\xf0\x9f\x8c\xb7",
        "\xee\x9d\x88" => "\xf0\x9f\x8c\xb8",
        "\xee\x9d\x81" => "\xf0\x9f\x8d\x80",
        "\xee\x9d\x87" => "\xf0\x9f\x8d\x82",
        "\xee\x9d\x84" => "\xf0\x9f\x8d\x8c",
        "\xee\x9d\x85" => "\xf0\x9f\x8d\x8f",
        "\xee\x9d\x82" => "\xf0\x9f\x8d\x92",
        "\xee\x99\xb3" => "\xf0\x9f\x8d\x94",
        "\xee\x9d\x89" => "\xf0\x9f\x8d\x99",
        "\xee\x9d\x8c" => "\xf0\x9f\x8d\x9c",
        "\xee\x9d\x8d" => "\xf0\x9f\x8d\x9e",
        "\xee\x9d\x8a" => "\xf0\x9f\x8d\xb0",
        "\xee\x99\xaf" => "\xf0\x9f\x8d\xb4",
        "\xee\x9c\x9e" => "\xf0\x9f\x8d\xb5",
        "\xee\x9d\x8b" => "\xf0\x9f\x8f\xae",
        "\xee\x9d\x96" => "\xf0\x9f\x8d\xb7",
        "\xee\x99\xb1" => "\xf0\x9f\x8d\xb9",
        "\xee\x99\xb2" => "\xf0\x9f\x8d\xbb",
        "\xee\x9a\x84" => "\xf0\x9f\x8e\x80",
        "\xee\x9a\x85" => "\xf0\x9f\x93\xa6",
        "\xee\x9a\x86" => "\xf0\x9f\x8e\x82",
        "\xee\x9a\xa4" => "\xf0\x9f\x8e\x84",
        "\xee\x99\xb9" => "\xf0\x9f\x8e\xa0",
        "\xee\x9d\x91" => "\xf0\x9f\x90\xa1",
        "\xee\x99\xb6" => "\xf0\x9f\x8e\xa4",
        "\xee\x99\xb7" => "\xf0\x9f\x93\xb9",
        "\xee\x99\xba" => "\xf0\x9f\x8e\xa7",
        "\xee\x99\xbb" => "\xf0\x9f\x8e\xa8",
        "\xee\x99\xbc" => "\xf0\x9f\x8e\xa9",
        "\xee\x99\xbd" => "\xf0\x9f\x8e\xaa",
        "\xee\x99\xbe" => "\xf0\x9f\x8e\xab",
        "\xee\x9a\xac" => "\xf0\x9f\x8e\xac",
        "\xee\x9a\x8b" => "\xf0\x9f\x8e\xae",
        "\xee\x9b\xb6" => "\xf0\x9f\x8e\xb5",
        "\xee\x9b\xbf" => "\xf0\x9f\x8e\xbc",
        "\xee\x99\x92" => "\xf0\x9f\x8e\xbd",
        "\xee\x99\x95" => "\xf0\x9f\x8e\xbe",
        "\xee\x99\x97" => "\xf0\x9f\x8e\xbf",
        "\xee\x99\x98" => "\xf0\x9f\x8f\x80",
        "\xee\x99\x99" => "\xf0\x9f\x8f\x81",
        "\xee\x9c\x92" => "\xf0\x9f\x8f\x84",
        "\xee\x9c\xb3" => "\xf0\x9f\x9a\xb6",
        "\xee\x99\xa3" => "\xf0\x9f\x8f\xa1",
        "\xee\x99\xa4" => "\xf0\x9f\x8f\xa2",
        "\xee\x99\xa5" => "\xf0\x9f\x93\xae",
        "\xee\x99\xa6" => "\xf0\x9f\x8f\xa5",
        "\xee\x99\xa7" => "\xf0\x9f\x8f\xa6",
        "\xee\x99\xa8" => "\xf0\x9f\x8f\xa7",
        "\xee\x99\xa9" => "\xf0\x9f\x8f\xa8",
        "\xee\x99\xa9\xee\x9b\xaf" => "\xf0\x9f\x8f\xa9",
        "\xee\x99\xaa" => "\xf0\x9f\x8f\xaa",
        "\xee\x9c\xbe" => "\xf0\x9f\x8f\xab",
        "\xee\x9d\x8e" => "\xf0\x9f\x90\x8c",
        "\xee\x9d\x94" => "\xf0\x9f\x90\xb4",
        "\xee\x9d\x8f" => "\xf0\x9f\x90\xa6",
        "\xee\x9d\x90" => "\xf0\x9f\x90\xa7",
        "\xee\x9a\xa1" => "\xf0\x9f\x90\xba",
        "\xee\x9a\xa2" => "\xf0\x9f\x90\xb1",
        "\xee\x9d\x95" => "\xf0\x9f\x90\xbd",
        "\xee\x9a\x98" => "\xf0\x9f\x91\xa3",
        "\xee\x9a\x91" => "\xf0\x9f\x91\x80",
        "\xee\x9a\x92" => "\xf0\x9f\x91\x82",
        "\xee\x9b\xb9" => "\xf0\x9f\x92\x8f",
        "\xee\x9c\xa8" => "\xf0\x9f\x98\x9d",
        "\xee\x9b\xbd" => "\xf0\x9f\x91\x8a",
        "\xee\x9c\xa7" => "\xf0\x9f\x91\x8d",
        "\xee\x9c\x9a" => "\xf0\x9f\x94\xb1",
        "\xee\x9a\x9a" => "\xf0\x9f\x91\x93",
        "\xee\x9c\x8e" => "\xf0\x9f\x91\x9a",
        "\xee\x9c\x91" => "\xf0\x9f\x91\x96",
        "\xee\x9c\x8f" => "\xf0\x9f\x91\x9b",
        "\xee\x9a\x82" => "\xf0\x9f\x92\xbc",
        "\xee\x9a\xad" => "\xf0\x9f\x91\x9d",
        "\xee\x9a\x99" => "\xf0\x9f\x91\x9f",
        "\xee\x99\xb4" => "\xf0\x9f\x91\xa1",
        "\xee\x9a\xb1" => "\xf0\x9f\x91\xa4",
        "\xee\x9c\x90" => "\xf0\x9f\x92\x84",
        "\xee\x9c\x97" => "\xf0\x9f\x92\x8c",
        "\xee\x9c\x9b" => "\xf0\x9f\x92\x8e",
        "\xee\x9b\xad" => "\xf0\x9f\x92\x9e",
        "\xee\x9b\xae" => "\xf0\x9f\x92\x94",
        "\xee\x9b\xaf" => "\xf0\x9f\x92\x95",
        "\xee\x9b\xbb" => "\xf0\x9f\x94\xa6",
        "\xee\x9b\xbc" => "\xf0\x9f\x92\xa2",
        "\xee\x9b\xbe" => "\xf0\x9f\x92\xa3",
        "\xee\x9c\x81" => "\xf0\x9f\x98\xaa",
        "\xee\x9c\x85" => "\xf0\x9f\x92\xa5",
        "\xee\x9c\x86" => "\xf0\x9f\x92\xa6",
        "\xee\x9c\x87" => "\xf0\x9f\x92\xa7",
        "\xee\x9c\x88" => "\xf0\x9f\x92\xa8",
        "\xee\x9c\x95" => "\xf0\x9f\x92\xb5",
        "\xee\x9b\x96" => "\xf0\x9f\x92\xb4",
        "\xee\x9a\xb2" => "\xf0\x9f\x92\xba",
        "\xee\x9c\x96" => "\xf0\x9f\x92\xbb",
        "\xee\x9a\x8c" => "\xf0\x9f\x93\x80",
        "\xee\x9a\x89" => "\xf0\x9f\x93\x9d",
        "\xee\x9a\x83" => "\xf0\x9f\x93\x9a",
        "\xee\x9c\xb0" => "\xf0\x9f\x93\x8e",
        "\xee\x99\x9a" => "\xf0\x9f\x93\x9f",
        "\xee\x9b\x90" => "\xf0\x9f\x93\xa0",
        "\xee\x9b\x8f" => "\xf0\x9f\x93\xa9",
        "\xee\x9a\x88" => "\xf0\x9f\x93\xb1",
        "\xee\x9b\x8e" => "\xf0\x9f\x93\xb2",
        "\xee\x9a\x81" => "\xf0\x9f\x93\xb7",
        "\xee\x9a\x8a" => "\xf0\x9f\x93\xba",
        "\xee\x9b\x9c" => "\xf0\x9f\x94\x8e",
        "\xee\x9b\x99" => "\xf0\x9f\x94\x93",
        "\xee\x9c\x93" => "\xf0\x9f\x94\x94",
        "\xee\x9a\xb9" => "\xf0\x9f\x94\x9a",
        "\xee\x9a\xb8" => "\xf0\x9f\x94\x9b",
        "\xee\x9a\xb7" => "\xf0\x9f\x94\x9c",
        "\xee\x9c\x98" => "\xf0\x9f\x94\xa7",
        "\xee\x9d\x80" => "\xf0\x9f\x97\xbb",
        "\xee\x9d\x93" => "\xf0\x9f\x98\xbc",
        "\xee\x9c\xaa" => "\xf0\x9f\x98\xb9",
        "\xee\x9c\xa2" => "\xf0\x9f\x98\x85",
        "\xee\x9c\xa9" => "\xf0\x9f\x98\x89",
        "\xee\x9d\x92" => "\xf0\x9f\x98\x8b",
        "\xee\x9c\xa1" => "\xf0\x9f\x98\x8c",
        "\xee\x9c\xa6" => "\xf0\x9f\x98\xbd",
        "\xee\x9c\xac" => "\xf0\x9f\x98\x8f",
        "\xee\x9c\xa5" => "\xf0\x9f\x98\x92",
        "\xee\x9c\xa3" => "\xf0\x9f\x98\xb0",
        "\xee\x9c\xa0" => "\xf0\x9f\x98\x94",
        "\xee\x9b\xb3" => "\xf0\x9f\x99\x8d",
        "\xee\x9b\xb2" => "\xf0\x9f\x98\x9e",
        "\xee\x9b\xb1" => "\xf0\x9f\x99\x8e",
        "\xee\x9c\xa4" => "\xf0\x9f\x98\xbe",
        "\xee\x9c\xae" => "\xf0\x9f\x98\xbf",
        "\xee\x9c\xab" => "\xf0\x9f\x98\xab",
        "\xee\x9d\x97" => "\xf0\x9f\x98\xb1",
        "\xee\x9c\xad" => "\xf0\x9f\x98\xad",
        "\xee\x9b\xb4" => "\xf0\x9f\x98\xb5",
        "\xee\x99\x9b" => "\xf0\x9f\x9a\x83",
        "\xee\x99\x9d" => "\xf0\x9f\x9a\x85",
        "\xee\x99\xa0" => "\xf0\x9f\x9a\x8c",
        "\xee\x99\x9e" => "\xf0\x9f\x9a\x97",
        "\xee\x99\x9f" => "\xf0\x9f\x9a\x99",
        "\xee\x99\xad" => "\xf0\x9f\x9a\xa5",
        "\xee\x9b\x9e" => "\xf0\x9f\x9a\xa9",
        "\xee\x9c\x94" => "\xf0\x9f\x9a\xaa",
        "\xee\x99\xbf" => "\xf0\x9f\x9a\xac",
        "\xee\x9a\x80" => "\xf0\x9f\x9a\xad",
        "\xee\x9c\x9d" => "\xf0\x9f\x9a\xb2",
        "\xee\x99\xae" => "\xf0\x9f\x9a\xbe",
        "\xee\x9b\xa0" => "#\xe2\x83\xa3",
        "\xee\x9b\xab" => "0\xe2\x83\xa3",
        "\xee\x9b\xa2" => "1\xe2\x83\xa3",
        "\xee\x9b\xa3" => "2\xe2\x83\xa3",
        "\xee\x9b\xa4" => "3\xe2\x83\xa3",
        "\xee\x9b\xa5" => "4\xe2\x83\xa3",
        "\xee\x9b\xa6" => "5\xe2\x83\xa3",
        "\xee\x9b\xa7" => "6\xe2\x83\xa3",
        "\xee\x9b\xa8" => "7\xe2\x83\xa3",
        "\xee\x9b\xa9" => "8\xe2\x83\xa3",
        "\xee\x9b\xaa" => "9\xe2\x83\xa3",
    ),
    'kddi_to_unified' => array(
        "\xee\x95\x98" => "\xc2\xa9",
        "\xee\x95\x99" => "\xc2\xae",
        "\xee\xac\xb0" => "\xe2\x80\xbc",
        "\xee\xac\xaf" => "\xe2\x81\x89",
        "\xee\x95\x8e" => "\xe2\x84\xa2",
        "\xee\x94\xb3" => "\xe2\x84\xb9",
        "\xee\xad\xba" => "\xe2\x86\x94",
        "\xee\xad\xbb" => "\xe2\x86\x95",
        "\xee\x95\x8c" => "\xe2\x86\x96",
        "\xee\x95\x95" => "\xe2\x86\x97",
        "\xee\x95\x8d" => "\xe2\x86\x98",
        "\xee\x95\x96" => "\xe2\x86\x99",
        "\xee\x95\x9d" => "\xe2\x86\xa9",
        "\xee\x95\x9c" => "\xe2\x86\xaa",
        "\xee\x95\xba" => "\xe2\x8c\x9a",
        "\xee\x95\xbb" => "\xe2\x8c\x9b",
        "\xee\x94\xb0" => "\xe2\x8f\xa9",
        "\xee\x94\xaf" => "\xe2\x8f\xaa",
        "\xee\x95\x85" => "\xe2\x8f\xab",
        "\xee\x95\x84" => "\xe2\x8f\xac",
        "\xee\x96\x94" => "\xf0\x9f\x95\x9b",
        "\xee\x91\xbc" => "\xe2\x8f\xb3",
        "\xee\x96\xbc" => "\xf0\x9f\x9a\x87",
        "\xee\x94\xb2" => "\xe2\x96\xaa",
        "\xee\x94\xb1" => "\xe2\x96\xab",
        "\xee\x94\xae" => "\xe2\x96\xb6",
        "\xee\x94\xad" => "\xe2\x97\x80",
        "\xee\x94\xb8" => "\xe2\x97\xbb",
        "\xee\x94\xb9" => "\xe2\x97\xbc",
        "\xee\x94\xb4" => "\xe2\x97\xbd",
        "\xee\x94\xb5" => "\xe2\x97\xbe",
        "\xee\x92\x88" => "\xe2\x98\x80",
        "\xee\x92\x8d" => "\xe2\x98\x81",
        "\xee\x96\x96" => "\xe2\x98\x8e",
        "\xee\xac\x82" => "\xe2\x98\x91",
        "\xee\x92\x8c" => "\xe2\x98\x94",
        "\xee\x96\x97" => "\xe2\x98\x95",
        "\xee\x93\xb6" => "\xe2\x98\x9d",
        "\xee\x93\xbb" => "\xe2\x98\xba",
        "\xee\x92\x8f" => "\xf0\x9f\x90\x91",
        "\xee\x92\x90" => "\xe2\x99\x89",
        "\xee\x92\x91" => "\xe2\x99\x8a",
        "\xee\x92\x92" => "\xe2\x99\x8b",
        "\xee\x92\x93" => "\xe2\x99\x8c",
        "\xee\x92\x94" => "\xe2\x99\x8d",
        "\xee\x92\x95" => "\xe2\x99\x8e",
        "\xee\x92\x96" => "\xe2\x99\x8f",
        "\xee\x92\x97" => "\xe2\x99\x90",
        "\xee\x92\x98" => "\xe2\x99\x91",
        "\xee\x92\x99" => "\xe2\x99\x92",
        "\xee\x92\x9a" => "\xf0\x9f\x90\x9f",
        "\xee\x96\xa1" => "\xe2\x99\xa0",
        "\xee\x96\xa3" => "\xe2\x99\xa3",
        "\xee\xaa\xa5" => "\xe2\x99\xa5",
        "\xee\x96\xa2" => "\xe2\x99\xa6",
        "\xee\x92\xbc" => "\xe2\x99\xa8",
        "\xee\xad\xb9" => "\xe2\x99\xbb",
        "\xee\x91\xbf" => "\xe2\x99\xbf",
        "\xee\x92\xa9" => "\xe2\x9a\x93",
        "\xee\x92\x81" => "\xe2\x9a\xa0",
        "\xee\x92\x87" => "\xe2\x9a\xa1",
        "\xee\x94\xba" => "\xe2\x9a\xaa",
        "\xee\x94\xbb" => "\xe2\x9a\xab",
        "\xee\x92\xb6" => "\xe2\x9a\xbd",
        "\xee\x92\xba" => "\xe2\x9a\xbe",
        "\xee\x92\x85" => "\xe2\x9b\x84",
        "\xee\x92\x8e" => "\xe2\x9b\x85",
        "\xee\x92\x9b" => "\xe2\x9b\x8e",
        "\xee\x92\x84" => "\xe2\x9b\x94",
        "\xee\x96\xbb" => "\xf0\x9f\x92\x92",
        "\xee\x97\x8f" => "\xe2\x9b\xb2",
        "\xee\x96\x99" => "\xe2\x9b\xb3",
        "\xee\x92\xb4" => "\xf0\x9f\x9a\xa4",
        "\xee\x97\x90" => "\xe2\x9b\xba",
        "\xee\x95\xb1" => "\xe2\x9b\xbd",
        "\xee\x94\x96" => "\xe2\x9c\x82",
        "\xee\x95\x9e" => "\xe2\x9c\x85",
        "\xee\x92\xb3" => "\xe2\x9c\x88",
        "\xee\x94\xa1" => "\xe2\x9c\x89",
        "\xee\xae\x83" => "\xe2\x9c\x8a",
        "\xee\x96\xa7" => "\xe2\x9c\x8b",
        "\xee\x96\xa6" => "\xe2\x9c\x8c",
        "\xee\x92\xa1" => "\xe2\x9c\x8f",
        "\xee\xac\x83" => "\xe2\x9c\x92",
        "\xee\x95\x97" => "\xe2\x9c\x94",
        "\xee\x95\x8f" => "\xe2\x9c\x96",
        "\xee\xaa\xab" => "\xe2\x9c\xa8",
        "\xee\x94\xbe" => "\xe2\x9c\xb3",
        "\xee\x91\xb9" => "\xe2\x9c\xb4",
        "\xee\x92\x8a" => "\xe2\x9d\x84",
        "\xee\x91\xac" => "\xe2\x9d\x87",
        "\xee\x95\x90" => "\xe2\x9d\x8c",
        "\xee\x95\x91" => "\xe2\x9d\x8e",
        "\xee\x92\x83" => "\xe2\x9d\x94",
        "\xee\x92\x82" => "\xe2\x9d\x97",
        "\xee\x96\x95" => "\xf0\x9f\x92\x9f",
        "\xee\x94\xbc" => "\xe2\x9e\x95",
        "\xee\x94\xbd" => "\xe2\x9e\x96",
        "\xee\x95\x94" => "\xe2\x9e\x97",
        "\xee\x95\x92" => "\xe2\x9e\xa1",
        "\xee\xac\xb1" => "\xe2\x9e\xb0",
        "\xee\xac\xad" => "\xe2\xa4\xb4",
        "\xee\xac\xae" => "\xe2\xa4\xb5",
        "\xee\x95\x93" => "\xe2\xac\x85",
        "\xee\x94\xbf" => "\xe2\xac\x86",
        "\xee\x95\x80" => "\xe2\xac\x87",
        "\xee\x95\x89" => "\xe2\xac\x9b",
        "\xee\x95\x88" => "\xe2\xac\x9c",
        "\xee\x92\x8b" => "\xf0\x9f\x8c\x9f",
        "\xee\xaa\xad" => "\xe2\xad\x95",
        "\xee\xaa\x99" => "\xe3\x8a\x97",
        "\xee\x93\xb1" => "\xe3\x8a\x99",
        "\xee\x97\x91" => "\xf0\x9f\x80\x84",
        "\xee\xad\xaf" => "\xf0\x9f\x83\x8f",
        "\xee\xac\xa6" => "\xf0\x9f\x85\xb0",
        "\xee\xac\xa7" => "\xf0\x9f\x85\xb1",
        "\xee\xac\xa8" => "\xf0\x9f\x85\xbe",
        "\xee\x92\xa6" => "\xf0\x9f\x85\xbf",
        "\xee\xac\xa9" => "\xf0\x9f\x86\x8e",
        "\xee\x96\xab" => "\xf0\x9f\x86\x91",
        "\xee\xaa\x85" => "\xf0\x9f\x86\x92",
        "\xee\x95\xb8" => "\xf0\x9f\x86\x93",
        "\xee\xaa\x88" => "\xf0\x9f\x86\x94",
        "\xee\x96\xb5" => "\xf0\x9f\x86\x95",
        "\xee\x96\xad" => "\xf0\x9f\x86\x97",
        "\xee\x93\xa8" => "\xf0\x9f\x86\x98",
        "\xee\x94\x8f" => "\xf0\x9f\x86\x99",
        "\xee\x97\x92" => "\xf0\x9f\x86\x9a",
        "\xee\xaa\x87" => "\xf0\x9f\x88\x82",
        "\xee\xaa\x8b" => "\xf0\x9f\x88\xaf",
        "\xee\xaa\x8a" => "\xf0\x9f\x88\xb3",
        "\xee\xaa\x89" => "\xf0\x9f\x88\xb5",
        "\xee\xaa\x86" => "\xf0\x9f\x88\xb9",
        "\xee\xaa\x8c" => "\xf0\x9f\x88\xba",
        "\xee\x93\xb7" => "\xf0\x9f\x89\x90",
        "\xee\xac\x81" => "\xf0\x9f\x89\x91",
        "\xee\x91\xa9" => "\xf0\x9f\x8c\x80",
        "\xee\x96\x98" => "\xf0\x9f\x8c\x81",
        "\xee\xab\xa8" => "\xf0\x9f\x8c\x82",
        "\xee\xab\xb1" => "\xf0\x9f\x8c\x83",
        "\xee\xab\xb4" => "\xf0\x9f\x8c\x85",
        "\xee\x97\x9a" => "\xf0\x9f\x8c\x87",
        "\xee\xab\xb2" => "\xf0\x9f\x8c\x88",
        "\xee\x92\xbf" => "\xf0\x9f\x8c\x89",
        "\xee\xad\xbc" => "\xf0\x9f\x8c\x8a",
        "\xee\xad\x93" => "\xf0\x9f\x8c\x8b",
        "\xee\xad\x9f" => "\xf0\x9f\x8c\x8c",
        "\xee\x96\xb3" => "\xf0\x9f\x8c\x8f",
        "\xee\x96\xa8" => "\xf0\x9f\x8c\x91",
        "\xee\x96\xaa" => "\xf0\x9f\x8c\x93",
        "\xee\x96\xa9" => "\xf0\x9f\x8c\x94",
        "\xee\x92\x86" => "\xf0\x9f\x8c\x99",
        "\xee\x92\x89" => "\xf0\x9f\x8c\x9b",
        "\xee\x91\xa8" => "\xf0\x9f\x8c\xa0",
        "\xee\xac\xb8" => "\xf0\x9f\x8c\xb0",
        "\xee\xad\xbd" => "\xf0\x9f\x8c\xb1",
        "\xee\x93\xa2" => "\xf0\x9f\x8c\xb4",
        "\xee\xaa\x96" => "\xf0\x9f\x8c\xb5",
        "\xee\x93\xa4" => "\xf0\x9f\x8c\xb7",
        "\xee\x93\x8a" => "\xf0\x9f\x8c\xb8",
        "\xee\x96\xba" => "\xf0\x9f\x8c\xb9",
        "\xee\xaa\x94" => "\xf0\x9f\x8c\xba",
        "\xee\x93\xa3" => "\xf0\x9f\x8c\xbb",
        "\xee\xad\x89" => "\xf0\x9f\x8c\xbc",
        "\xee\xac\xb6" => "\xf0\x9f\x8c\xbd",
        "\xee\xae\x82" => "\xf0\x9f\x8c\xbf",
        "\xee\x94\x93" => "\xf0\x9f\x8d\x80",
        "\xee\x93\x8e" => "\xf0\x9f\x8d\x81",
        "\xee\x97\x8d" => "\xf0\x9f\x8d\x83",
        "\xee\xac\xb7" => "\xf0\x9f\x8d\x84",
        "\xee\xaa\xbb" => "\xf0\x9f\x8d\x85",
        "\xee\xaa\xbc" => "\xf0\x9f\x8d\x86",
        "\xee\xac\xb4" => "\xf0\x9f\x8d\x87",
        "\xee\xac\xb2" => "\xf0\x9f\x8d\x88",
        "\xee\x93\x8d" => "\xf0\x9f\x8d\x89",
        "\xee\xaa\xba" => "\xf0\x9f\x8d\x8a",
        "\xee\xac\xb5" => "\xf0\x9f\x8d\x8c",
        "\xee\xac\xb3" => "\xf0\x9f\x8d\x8d",
        "\xee\xaa\xb9" => "\xf0\x9f\x8d\x8e",
        "\xee\xad\x9a" => "\xf0\x9f\x8d\x8f",
        "\xee\xac\xb9" => "\xf0\x9f\x8d\x91",
        "\xee\x93\x92" => "\xf0\x9f\x8d\x92",
        "\xee\x93\x94" => "\xf0\x9f\x8d\x93",
        "\xee\x93\x96" => "\xf0\x9f\x8d\x94",
        "\xee\xac\xbb" => "\xf0\x9f\x8d\x95",
        "\xee\x93\x84" => "\xf0\x9f\x8d\x96",
        "\xee\xac\xbc" => "\xf0\x9f\x8d\x97",
        "\xee\xaa\xb3" => "\xf0\x9f\x8d\x98",
        "\xee\x93\x95" => "\xf0\x9f\x8d\x99",
        "\xee\xaa\xb4" => "\xf0\x9f\x8d\x9a",
        "\xee\xaa\xb6" => "\xf0\x9f\x8d\x9b",
        "\xee\x96\xb4" => "\xf0\x9f\x8d\x9c",
        "\xee\xaa\xb5" => "\xf0\x9f\x8d\x9d",
        "\xee\xaa\xaf" => "\xf0\x9f\x8d\x9e",
        "\xee\xaa\xb1" => "\xf0\x9f\x8d\x9f",
        "\xee\xac\xba" => "\xf0\x9f\x8d\xa0",
        "\xee\xaa\xb2" => "\xf0\x9f\x8d\xa1",
        "\xee\xaa\xb7" => "\xf0\x9f\x8d\xa2",
        "\xee\xaa\xb8" => "\xf0\x9f\x8d\xa3",
        "\xee\xad\xb0" => "\xf0\x9f\x8d\xa4",
        "\xee\x93\xad" => "\xf0\x9f\x8d\xa5",
        "\xee\xaa\xb0" => "\xf0\x9f\x8d\xa6",
        "\xee\xab\xaa" => "\xf0\x9f\x8d\xa7",
        "\xee\xad\x8a" => "\xf0\x9f\x8d\xa8",
        "\xee\xad\x8b" => "\xf0\x9f\x8d\xa9",
        "\xee\xad\x8c" => "\xf0\x9f\x8d\xaa",
        "\xee\xad\x8d" => "\xf0\x9f\x8d\xab",
        "\xee\xad\x8e" => "\xf0\x9f\x8d\xac",
        "\xee\xad\x8f" => "\xf0\x9f\x8d\xad",
        "\xee\xad\x96" => "\xf0\x9f\x8d\xae",
        "\xee\xad\x99" => "\xf0\x9f\x8d\xaf",
        "\xee\x93\x90" => "\xf0\x9f\x8d\xb0",
        "\xee\xaa\xbd" => "\xf0\x9f\x8d\xb1",
        "\xee\xaa\xbe" => "\xf0\x9f\x8d\xb2",
        "\xee\x93\x91" => "\xf0\x9f\x8d\xb3",
        "\xee\x92\xac" => "\xf0\x9f\x8d\xb4",
        "\xee\xaa\xae" => "\xf0\x9f\x8d\xb5",
        "\xee\xaa\x97" => "\xf0\x9f\x8d\xb6",
        "\xee\x93\x81" => "\xf0\x9f\x8d\xb7",
        "\xee\x93\x82" => "\xf0\x9f\x8d\xb8",
        "\xee\xac\xbe" => "\xf0\x9f\x8d\xb9",
        "\xee\x93\x83" => "\xf0\x9f\x8d\xba",
        "\xee\xaa\x98" => "\xf0\x9f\x8d\xbb",
        "\xee\x96\x9f" => "\xf0\x9f\x8e\x80",
        "\xee\x93\x8f" => "\xf0\x9f\x8e\x81",
        "\xee\x96\xa0" => "\xf0\x9f\x8e\x82",
        "\xee\xab\xae" => "\xf0\x9f\x8e\x83",
        "\xee\x93\x89" => "\xf0\x9f\x8e\x84",
        "\xee\xab\xb0" => "\xf0\x9f\x8e\x85",
        "\xee\x97\x8c" => "\xf0\x9f\x8e\x86",
        "\xee\xab\xab" => "\xf0\x9f\x8e\x87",
        "\xee\xaa\x9b" => "\xf0\x9f\x8e\x88",
        "\xee\xaa\x9c" => "\xf0\x9f\x8e\x89",
        "\xee\x91\xaf" => "\xf0\x9f\x8e\x8a",
        "\xee\xac\xbd" => "\xf0\x9f\x8e\x8b",
        "\xee\x97\x99" => "\xf0\x9f\x8e\x8c",
        "\xee\xab\xa3" => "\xf0\x9f\x8e\x8d",
        "\xee\xab\xa4" => "\xf0\x9f\x8e\x8e",
        "\xee\xab\xa7" => "\xf0\x9f\x8e\x8f",
        "\xee\xab\xad" => "\xf0\x9f\x8e\x90",
        "\xee\xab\xaf" => "\xf0\x9f\x8e\x91",
        "\xee\xab\xa6" => "\xf0\x9f\x8e\x92",
        "\xee\xab\xa5" => "\xf0\x9f\x8e\x93",
        "\xee\x91\xad" => "\xf0\x9f\x8e\xa1",
        "\xee\xab\xa2" => "\xf0\x9f\x8e\xa2",
        "\xee\xad\x82" => "\xf0\x9f\x8e\xa3",
        "\xee\x94\x83" => "\xf0\x9f\x8e\xa4",
        "\xee\x94\x97" => "\xf0\x9f\x8e\xa6",
        "\xee\x94\x88" => "\xf0\x9f\x8e\xa7",
        "\xee\x96\x9c" => "\xf0\x9f\x8e\xa8",
        "\xee\xab\xb5" => "\xf0\x9f\x8e\xa9",
        "\xee\x96\x9e" => "\xf0\x9f\x8e\xaa",
        "\xee\x92\x9e" => "\xf0\x9f\x8e\xab",
        "\xee\x92\xbe" => "\xf0\x9f\x8e\xac",
        "\xee\x96\x9d" => "\xf0\x9f\x8e\xad",
        "\xee\x93\x86" => "\xf0\x9f\x8e\xae",
        "\xee\x93\x85" => "\xf0\x9f\x8e\xaf",
        "\xee\x91\xae" => "\xf0\x9f\x8e\xb0",
        "\xee\xab\x9d" => "\xf0\x9f\x8e\xb1",
        "\xee\x93\x88" => "\xf0\x9f\x8e\xb2",
        "\xee\xad\x83" => "\xf0\x9f\x8e\xb3",
        "\xee\xad\xae" => "\xf0\x9f\x8e\xb4",
        "\xee\x96\xbe" => "\xf0\x9f\x8e\xb5",
        "\xee\x94\x85" => "\xf0\x9f\x8e\xb6",
        "\xee\x94\x86" => "\xf0\x9f\x8e\xb8",
        "\xee\xad\x80" => "\xf0\x9f\x8e\xb9",
        "\xee\xab\x9c" => "\xf0\x9f\x8e\xba",
        "\xee\x94\x87" => "\xf0\x9f\x8e\xbb",
        "\xee\xab\x8c" => "\xf0\x9f\x8e\xbc",
        "\xee\x92\xb7" => "\xf0\x9f\x8e\xbe",
        "\xee\xaa\xac" => "\xf0\x9f\x8e\xbf",
        "\xee\x96\x9a" => "\xf0\x9f\x8f\x80",
        "\xee\x92\xb9" => "\xf0\x9f\x8f\x81",
        "\xee\x92\xb8" => "\xf0\x9f\x8f\x82",
        "\xee\x91\xab" => "\xf0\x9f\x8f\x83",
        "\xee\xad\x81" => "\xf0\x9f\x8f\x84",
        "\xee\x97\x93" => "\xf0\x9f\x8f\x86",
        "\xee\x92\xbb" => "\xf0\x9f\x8f\x88",
        "\xee\xab\x9e" => "\xf0\x9f\x8f\x8a",
        "\xee\x92\xab" => "\xf0\x9f\x8f\xa0",
        "\xee\xac\x89" => "\xf0\x9f\x8f\xa1",
        "\xee\x92\xad" => "\xf0\x9f\x8f\xa2",
        "\xee\x97\x9e" => "\xf0\x9f\x8f\xa3",
        "\xee\x97\x9f" => "\xf0\x9f\x8f\xa5",
        "\xee\x92\xaa" => "\xf0\x9f\x8f\xa6",
        "\xee\x92\xa3" => "\xf0\x9f\x8f\xa7",
        "\xee\xaa\x81" => "\xf0\x9f\x8f\xa8",
        "\xee\xab\xb3" => "\xf0\x9f\x8f\xa9",
        "\xee\x92\xa4" => "\xf0\x9f\x8f\xaa",
        "\xee\xaa\x80" => "\xf0\x9f\x8f\xab",
        "\xee\xab\xb6" => "\xf0\x9f\x8f\xac",
        "\xee\xab\xb9" => "\xf0\x9f\x8f\xad",
        "\xee\x92\xbd" => "\xf0\x9f\x8f\xae",
        "\xee\xab\xb7" => "\xf0\x9f\x8f\xaf",
        "\xee\xab\xb8" => "\xf0\x9f\x8f\xb0",
        "\xee\xad\xbe" => "\xf0\x9f\x90\x8c",
        "\xee\xac\xa2" => "\xf0\x9f\x90\x8d",
        "\xee\x93\x98" => "\xf0\x9f\x90\xb4",
        "\xee\x93\x99" => "\xf0\x9f\x90\xb5",
        "\xee\xac\xa3" => "\xf0\x9f\x90\x94",
        "\xee\xac\xa4" => "\xf0\x9f\x90\x97",
        "\xee\xac\x9f" => "\xf0\x9f\x90\x98",
        "\xee\x97\x87" => "\xf0\x9f\x90\x99",
        "\xee\xab\xac" => "\xf0\x9f\x90\x9a",
        "\xee\xac\x9e" => "\xf0\x9f\x90\x9b",
        "\xee\x93\x9d" => "\xf0\x9f\x90\x9c",
        "\xee\xad\x97" => "\xf0\x9f\x90\x9d",
        "\xee\xad\x98" => "\xf0\x9f\x90\x9e",
        "\xee\xac\x9d" => "\xf0\x9f\x90\xa0",
        "\xee\x93\x93" => "\xf0\x9f\x90\xa1",
        "\xee\x97\x94" => "\xf0\x9f\x90\xa2",
        "\xee\x97\x9b" => "\xf0\x9f\x90\xa3",
        "\xee\x93\xa0" => "\xf0\x9f\x90\xa6",
        "\xee\xad\xb6" => "\xf0\x9f\x90\xa5",
        "\xee\x93\x9c" => "\xf0\x9f\x90\xa7",
        "\xee\xac\xa0" => "\xf0\x9f\x90\xa8",
        "\xee\x93\x9f" => "\xf0\x9f\x90\xa9",
        "\xee\xac\xa5" => "\xf0\x9f\x90\xab",
        "\xee\xac\x9b" => "\xf0\x9f\x90\xac",
        "\xee\x97\x82" => "\xf0\x9f\x90\xad",
        "\xee\xac\xa1" => "\xf0\x9f\x90\xae",
        "\xee\x97\x80" => "\xf0\x9f\x90\xaf",
        "\xee\x93\x97" => "\xf0\x9f\x90\xb0",
        "\xee\x93\x9b" => "\xf0\x9f\x90\xb1",
        "\xee\xac\xbf" => "\xf0\x9f\x90\xb2",
        "\xee\x91\xb0" => "\xf0\x9f\x90\xb3",
        "\xee\x93\xa1" => "\xf0\x9f\x90\xba",
        "\xee\x93\x9e" => "\xf0\x9f\x90\xb7",
        "\xee\x93\x9a" => "\xf0\x9f\x90\xb8",
        "\xee\x97\x81" => "\xf0\x9f\x90\xbb",
        "\xee\xad\x86" => "\xf0\x9f\x90\xbc",
        "\xee\xad\x88" => "\xf0\x9f\x90\xbd",
        "\xee\x93\xae" => "\xf0\x9f\x90\xbe",
        "\xee\x96\xa4" => "\xf0\x9f\x91\x80",
        "\xee\x96\xa5" => "\xf0\x9f\x91\x82",
        "\xee\xab\x90" => "\xf0\x9f\x91\x83",
        "\xee\xab\x91" => "\xf0\x9f\x91\x84",
        "\xee\xad\x87" => "\xf0\x9f\x91\x85",
        "\xee\xaa\x8d" => "\xf0\x9f\x91\x86",
        "\xee\xaa\x8e" => "\xf0\x9f\x91\x87",
        "\xee\x93\xbf" => "\xf0\x9f\x91\x88",
        "\xee\x94\x80" => "\xf0\x9f\x91\x89",
        "\xee\x93\xb3" => "\xf0\x9f\x91\x8a",
        "\xee\xab\x96" => "\xf0\x9f\x91\x90",
        "\xee\xab\x94" => "\xf0\x9f\x91\x8c",
        "\xee\x93\xb9" => "\xf0\x9f\x91\x8d",
        "\xee\xab\x95" => "\xf0\x9f\x91\x8e",
        "\xee\xab\x93" => "\xf0\x9f\x91\x8f",
        "\xee\x97\x89" => "\xf0\x9f\x94\xb1",
        "\xee\xaa\x9e" => "\xf0\x9f\x91\x92",
        "\xee\x93\xbe" => "\xf0\x9f\x91\x93",
        "\xee\xaa\x93" => "\xf0\x9f\x91\x94",
        "\xee\x96\xb6" => "\xf0\x9f\x91\x95",
        "\xee\xad\xb7" => "\xf0\x9f\x91\x96",
        "\xee\xad\xab" => "\xf0\x9f\x91\x97",
        "\xee\xaa\xa3" => "\xf0\x9f\x91\x98",
        "\xee\xaa\xa4" => "\xf0\x9f\x91\x99",
        "\xee\x94\x8d" => "\xf0\x9f\x91\x9a",
        "\xee\x94\x84" => "\xf0\x9f\x91\x9b",
        "\xee\x92\x9c" => "\xf0\x9f\x91\x9c",
        "\xee\x96\xb7" => "\xf0\x9f\x91\x9e",
        "\xee\xac\xab" => "\xf0\x9f\x91\x9f",
        "\xee\x94\x9a" => "\xf0\x9f\x91\xa1",
        "\xee\xaa\x9f" => "\xf0\x9f\x91\xa2",
        "\xee\xac\xaa" => "\xf0\x9f\x91\xa3",
        "\xee\x93\xbc" => "\xf0\x9f\x91\xa8",
        "\xee\x93\xba" => "\xf0\x9f\x91\xa9",
        "\xee\x94\x81" => "\xf0\x9f\x91\xaa",
        "\xee\x97\x9d" => "\xf0\x9f\x91\xae",
        "\xee\xab\x9b" => "\xf0\x9f\x91\xaf",
        "\xee\xab\xa9" => "\xf0\x9f\x91\xb0",
        "\xee\xac\x93" => "\xf0\x9f\x91\xb1",
        "\xee\xac\x94" => "\xf0\x9f\x91\xb2",
        "\xee\xac\x95" => "\xf0\x9f\x91\xb3",
        "\xee\xac\x96" => "\xf0\x9f\x91\xb4",
        "\xee\xac\x97" => "\xf0\x9f\x91\xb5",
        "\xee\xac\x98" => "\xf0\x9f\x9a\xbc",
        "\xee\xac\x99" => "\xf0\x9f\x91\xb7",
        "\xee\xac\x9a" => "\xf0\x9f\x91\xb8",
        "\xee\xad\x84" => "\xf0\x9f\x91\xb9",
        "\xee\xad\x85" => "\xf0\x9f\x91\xba",
        "\xee\x93\x8b" => "\xf0\x9f\x91\xbb",
        "\xee\x96\xbf" => "\xf0\x9f\x91\xbc",
        "\xee\x94\x8e" => "\xf0\x9f\x91\xbd",
        "\xee\x93\xac" => "\xf0\x9f\x91\xbe",
        "\xee\x93\xaf" => "\xf0\x9f\x91\xbf",
        "\xee\x93\xb8" => "\xf0\x9f\x92\x80",
        "\xee\xac\x9c" => "\xf0\x9f\x92\x83",
        "\xee\x94\x89" => "\xf0\x9f\x92\x84",
        "\xee\xaa\xa0" => "\xf0\x9f\x92\x85",
        "\xee\x94\x8b" => "\xf0\x9f\x92\x86",
        "\xee\xaa\xa1" => "\xf0\x9f\x92\x87",
        "\xee\xaa\xa2" => "\xf0\x9f\x92\x88",
        "\xee\x94\x90" => "\xf0\x9f\x92\x89",
        "\xee\xaa\x9a" => "\xf0\x9f\x92\x8a",
        "\xee\x93\xab" => "\xf0\x9f\x92\x8b",
        "\xee\xad\xb8" => "\xf0\x9f\x92\x8c",
        "\xee\x94\x94" => "\xf0\x9f\x92\x8e",
        "\xee\x97\x8a" => "\xf0\x9f\x92\x8f",
        "\xee\xaa\x95" => "\xf0\x9f\x92\x90",
        "\xee\xab\x9a" => "\xf0\x9f\x92\x91",
        "\xee\xad\xb5" => "\xf0\x9f\x92\x97",
        "\xee\x91\xb7" => "\xf0\x9f\x92\x94",
        "\xee\x91\xb8" => "\xf0\x9f\x92\x95",
        "\xee\xaa\xa6" => "\xf0\x9f\x92\x96",
        "\xee\x93\xaa" => "\xf0\x9f\x92\x98",
        "\xee\xaa\xa7" => "\xf0\x9f\x92\x99",
        "\xee\xaa\xa8" => "\xf0\x9f\x92\x9a",
        "\xee\xaa\xa9" => "\xf0\x9f\x92\x9b",
        "\xee\xaa\xaa" => "\xf0\x9f\x92\x9c",
        "\xee\xad\x94" => "\xf0\x9f\x92\x9d",
        "\xee\x96\xaf" => "\xf0\x9f\x92\x9e",
        "\xee\x91\xb6" => "\xf0\x9f\x92\xa1",
        "\xee\x93\xa5" => "\xf0\x9f\x92\xa2",
        "\xee\x91\xba" => "\xf0\x9f\x92\xa3",
        "\xee\x91\xb5" => "\xf0\x9f\x92\xa4",
        "\xee\x96\xb0" => "\xf0\x9f\x92\xa5",
        "\xee\x96\xb1" => "\xf0\x9f\x92\xa6",
        "\xee\x93\xa6" => "\xf0\x9f\x92\xa7",
        "\xee\x93\xb4" => "\xf0\x9f\x92\xa8",
        "\xee\x93\xb5" => "\xf0\x9f\x92\xa9",
        "\xee\x93\xa9" => "\xf0\x9f\x92\xaa",
        "\xee\xad\x9c" => "\xf0\x9f\x92\xab",
        "\xee\x93\xbd" => "\xf0\x9f\x92\xac",
        "\xee\x93\xb0" => "\xf0\x9f\x92\xae",
        "\xee\x93\xb2" => "\xf0\x9f\x92\xaf",
        "\xee\x93\x87" => "\xf0\x9f\x92\xb0",
        "\xee\x95\xb9" => "\xf0\x9f\x92\xb2",
        "\xee\x95\xbc" => "\xf0\x9f\x92\xb3",
        "\xee\x95\xbd" => "\xf0\x9f\x92\xb4",
        "\xee\x96\x85" => "\xf0\x9f\x92\xb5",
        "\xee\xad\x9b" => "\xf0\x9f\x92\xb8",
        "\xee\x97\x9c" => "\xf0\x9f\x92\xb9",
        "\xee\x96\xb8" => "\xf0\x9f\x92\xbb",
        "\xee\x97\x8e" => "\xf0\x9f\x92\xbc",
        "\xee\x96\x82" => "\xf0\x9f\x92\xbd",
        "\xee\x95\xa2" => "\xf0\x9f\x92\xbe",
        "\xee\x94\x8c" => "\xf0\x9f\x93\x80",
        "\xee\x96\x8f" => "\xf0\x9f\x93\x81",
        "\xee\x96\x90" => "\xf0\x9f\x93\x82",
        "\xee\x95\xa1" => "\xf0\x9f\x93\x83",
        "\xee\x95\xa9" => "\xf0\x9f\x93\x84",
        "\xee\x95\xa3" => "\xf0\x9f\x93\x85",
        "\xee\x95\xaa" => "\xf0\x9f\x93\x86",
        "\xee\x95\xac" => "\xf0\x9f\x93\x87",
        "\xee\x95\xb5" => "\xf0\x9f\x93\x88",
        "\xee\x95\xb6" => "\xf0\x9f\x93\x89",
        "\xee\x95\xb4" => "\xf0\x9f\x93\x8a",
        "\xee\x95\xa4" => "\xf0\x9f\x93\x8b",
        "\xee\x95\xad" => "\xf0\x9f\x93\x8c",
        "\xee\x95\xa0" => "\xf0\x9f\x93\x8d",
        "\xee\x92\xa0" => "\xf0\x9f\x93\x8e",
        "\xee\x95\xb0" => "\xf0\x9f\x93\x8f",
        "\xee\x92\xa2" => "\xf0\x9f\x93\x90",
        "\xee\xac\x8b" => "\xf0\x9f\x93\x91",
        "\xee\x95\xae" => "\xf0\x9f\x93\x92",
        "\xee\x95\xab" => "\xf0\x9f\x93\x93",
        "\xee\x92\x9d" => "\xf0\x9f\x93\x94",
        "\xee\x95\xa8" => "\xf0\x9f\x93\x95",
        "\xee\x92\x9f" => "\xf0\x9f\x93\x96",
        "\xee\x95\xa5" => "\xf0\x9f\x93\x97",
        "\xee\x95\xa6" => "\xf0\x9f\x93\x98",
        "\xee\x95\xa7" => "\xf0\x9f\x93\x99",
        "\xee\x95\xaf" => "\xf0\x9f\x93\x9a",
        "\xee\x94\x9d" => "\xf0\x9f\x93\x9b",
        "\xee\x95\x9f" => "\xf0\x9f\x93\x9c",
        "\xee\xaa\x92" => "\xf0\x9f\x93\x9d",
        "\xee\x94\x9e" => "\xf0\x9f\x93\x9e",
        "\xee\x96\x9b" => "\xf0\x9f\x93\x9f",
        "\xee\x94\xa0" => "\xf0\x9f\x93\xa0",
        "\xee\x92\xa8" => "\xf0\x9f\x93\xa1",
        "\xee\x94\x91" => "\xf0\x9f\x94\x8a",
        "\xee\x96\x92" => "\xf0\x9f\x93\xa4",
        "\xee\x96\x93" => "\xf0\x9f\x93\xa5",
        "\xee\x94\x9f" => "\xf0\x9f\x93\xa6",
        "\xee\xad\xb1" => "\xf0\x9f\x93\xa7",
        "\xee\x96\x91" => "\xf0\x9f\x93\xa8",
        "\xee\xad\xa2" => "\xf0\x9f\x93\xa9",
        "\xee\x94\x9b" => "\xf0\x9f\x93\xae",
        "\xee\xac\x8a" => "\xf0\x9f\x93\xab",
        "\xee\x96\x8b" => "\xf0\x9f\x93\xb0",
        "\xee\x96\x88" => "\xf0\x9f\x93\xb1",
        "\xee\xac\x88" => "\xf0\x9f\x93\xb2",
        "\xee\xaa\x90" => "\xf0\x9f\x93\xb3",
        "\xee\xaa\x91" => "\xf0\x9f\x93\xb4",
        "\xee\xaa\x84" => "\xf0\x9f\x93\xb6",
        "\xee\x94\x95" => "\xf0\x9f\x93\xb7",
        "\xee\x95\xbe" => "\xf0\x9f\x93\xb9",
        "\xee\x94\x82" => "\xf0\x9f\x93\xba",
        "\xee\x96\xb9" => "\xf0\x9f\x93\xbb",
        "\xee\x96\x80" => "\xf0\x9f\x93\xbc",
        "\xee\xac\x8d" => "\xf0\x9f\x94\x83",
        "\xee\x96\x84" => "\xf0\x9f\x94\x8b",
        "\xee\x96\x89" => "\xf0\x9f\x94\x8c",
        "\xee\x94\x98" => "\xf0\x9f\x94\x8d",
        "\xee\xac\x85" => "\xf0\x9f\x94\x8e",
        "\xee\xac\x8c" => "\xf0\x9f\x94\x8f",
        "\xee\xab\xbc" => "\xf0\x9f\x94\x90",
        "\xee\x94\x99" => "\xf0\x9f\x94\x91",
        "\xee\x94\x9c" => "\xf0\x9f\x94\x93",
        "\xee\x94\x92" => "\xf0\x9f\x94\x94",
        "\xee\xac\x87" => "\xf0\x9f\x94\x96",
        "\xee\x96\x8a" => "\xf0\x9f\x94\x97",
        "\xee\xac\x84" => "\xf0\x9f\x94\x98",
        "\xee\xac\x86" => "\xf0\x9f\x94\x99",
        "\xee\xaa\x83" => "\xf0\x9f\x94\x9e",
        "\xee\x94\xab" => "\xf0\x9f\x94\x9f",
        "\xee\xab\xbd" => "\xf0\x9f\x94\xa0",
        "\xee\xab\xbe" => "\xf0\x9f\x94\xa1",
        "\xee\xab\xbf" => "\xf0\x9f\x94\xa2",
        "\xee\xac\x80" => "\xf0\x9f\x94\xa3",
        "\xee\xad\x95" => "\xf0\x9f\x94\xa4",
        "\xee\x91\xbb" => "\xf0\x9f\x94\xa5",
        "\xee\x96\x83" => "\xf0\x9f\x94\xa6",
        "\xee\x96\x87" => "\xf0\x9f\x94\xa7",
        "\xee\x97\x8b" => "\xf0\x9f\x94\xa8",
        "\xee\x96\x81" => "\xf0\x9f\x94\xa9",
        "\xee\x95\xbf" => "\xf0\x9f\x94\xaa",
        "\xee\x94\x8a" => "\xf0\x9f\x94\xab",
        "\xee\xaa\x8f" => "\xf0\x9f\x94\xaf",
        "\xee\x92\x80" => "\xf0\x9f\x94\xb0",
        "\xee\x95\x8b" => "\xf0\x9f\x94\xb5",
        "\xee\x95\x8a" => "\xf0\x9f\x94\xb4",
        "\xee\x95\x86" => "\xf0\x9f\x94\xb6",
        "\xee\x95\x87" => "\xf0\x9f\x94\xb7",
        "\xee\x94\xb6" => "\xf0\x9f\x94\xb8",
        "\xee\x94\xb7" => "\xf0\x9f\x94\xb9",
        "\xee\x95\x9a" => "\xf0\x9f\x94\xba",
        "\xee\x95\x9b" => "\xf0\x9f\x94\xbb",
        "\xee\x95\x83" => "\xf0\x9f\x94\xbc",
        "\xee\x95\x82" => "\xf0\x9f\x94\xbd",
        "\xee\x96\xbd" => "\xf0\x9f\x97\xbb",
        "\xee\x93\x80" => "\xf0\x9f\x97\xbc",
        "\xee\x95\xb2" => "\xf0\x9f\x97\xbe",
        "\xee\xad\xac" => "\xf0\x9f\x97\xbf",
        "\xee\xae\x80" => "\xf0\x9f\x98\x81",
        "\xee\xad\xa4" => "\xf0\x9f\x98\x82",
        "\xee\x91\xb1" => "\xf0\x9f\x98\x84",
        "\xee\x91\xb1\xee\x96\xb1" => "\xf0\x9f\x98\x85",
        "\xee\xab\x85" => "\xf0\x9f\x98\x8c",
        "\xee\x97\x83" => "\xf0\x9f\x98\x89",
        "\xee\xab\x8d" => "\xf0\x9f\x98\x8b",
        "\xee\x97\x84" => "\xf0\x9f\x98\x8d",
        "\xee\xaa\xbf" => "\xf0\x9f\x98\x8f",
        "\xee\xab\x89" => "\xf0\x9f\x98\x92",
        "\xee\x97\x86" => "\xf0\x9f\x98\xa5",
        "\xee\xab\x80" => "\xf0\x9f\x98\x9e",
        "\xee\xab\x83" => "\xf0\x9f\x98\x96",
        "\xee\xab\x8f" => "\xf0\x9f\x98\x98",
        "\xee\xab\x8e" => "\xf0\x9f\x98\x9a",
        "\xee\x93\xa7" => "\xf0\x9f\x98\x9d",
        "\xee\x91\xb2" => "\xf0\x9f\x98\xa0",
        "\xee\xad\x9d" => "\xf0\x9f\x98\xa1",
        "\xee\xad\xa9" => "\xf0\x9f\x98\xa2",
        "\xee\xab\x82" => "\xf0\x9f\x98\xa3",
        "\xee\xab\x81" => "\xf0\x9f\x98\xa4",
        "\xee\xab\x86" => "\xf0\x9f\x98\xa8",
        "\xee\xad\xa7" => "\xf0\x9f\x98\xa9",
        "\xee\xab\x84" => "\xf0\x9f\x98\xaa",
        "\xee\x91\xb4" => "\xf0\x9f\x98\xab",
        "\xee\x91\xb3" => "\xf0\x9f\x98\xad",
        "\xee\xab\x8b" => "\xf0\x9f\x98\xb0",
        "\xee\x97\x85" => "\xf0\x9f\x98\xb1",
        "\xee\xab\x8a" => "\xf0\x9f\x98\xb2",
        "\xee\xab\x88" => "\xf0\x9f\x98\xb3",
        "\xee\x96\xae" => "\xf0\x9f\x98\xb5",
        "\xee\xab\x87" => "\xf0\x9f\x98\xb7",
        "\xee\xad\xbf" => "\xf0\x9f\x98\xb8",
        "\xee\xad\xa3" => "\xf0\x9f\x98\xb9",
        "\xee\xad\xa1" => "\xf0\x9f\x98\xba",
        "\xee\xad\xa5" => "\xf0\x9f\x98\xbb",
        "\xee\xad\xaa" => "\xf0\x9f\x98\xbc",
        "\xee\xad\xa0" => "\xf0\x9f\x98\xbd",
        "\xee\xad\x9e" => "\xf0\x9f\x98\xbe",
        "\xee\xad\xa8" => "\xf0\x9f\x98\xbf",
        "\xee\xad\xa6" => "\xf0\x9f\x99\x80",
        "\xee\xab\x97" => "\xf0\x9f\x99\x85",
        "\xee\xab\x98" => "\xf0\x9f\x99\x86",
        "\xee\xab\x99" => "\xf0\x9f\x99\x87",
        "\xee\xad\x90" => "\xf0\x9f\x99\x88",
        "\xee\xad\x92" => "\xf0\x9f\x99\x89",
        "\xee\xad\x91" => "\xf0\x9f\x99\x8a",
        "\xee\xae\x85" => "\xf0\x9f\x99\x8b",
        "\xee\xae\x86" => "\xf0\x9f\x99\x8c",
        "\xee\xae\x87" => "\xf0\x9f\x99\x8d",
        "\xee\xae\x88" => "\xf0\x9f\x99\x8e",
        "\xee\xab\x92" => "\xf0\x9f\x99\x8f",
        "\xee\x97\x88" => "\xf0\x9f\x9a\x80",
        "\xee\x92\xb5" => "\xf0\x9f\x9a\x83",
        "\xee\x92\xb0" => "\xf0\x9f\x9a\x85",
        "\xee\xad\xad" => "\xf0\x9f\x9a\x89",
        "\xee\x92\xaf" => "\xf0\x9f\x9a\x8c",
        "\xee\x92\xa7" => "\xf0\x9f\x9a\x8f",
        "\xee\xab\xa0" => "\xf0\x9f\x9a\x91",
        "\xee\xab\x9f" => "\xf0\x9f\x9a\x92",
        "\xee\xab\xa1" => "\xf0\x9f\x9a\x93",
        "\xee\x92\xb1" => "\xf0\x9f\x9a\x99",
        "\xee\x92\xb2" => "\xf0\x9f\x9a\x9a",
        "\xee\xaa\x82" => "\xf0\x9f\x9a\xa2",
        "\xee\x91\xaa" => "\xf0\x9f\x9a\xa5",
        "\xee\x97\x97" => "\xf0\x9f\x9a\xa7",
        "\xee\xad\xb3" => "\xf0\x9f\x9a\xa8",
        "\xee\xac\xac" => "\xf0\x9f\x9a\xa9",
        "\xee\x95\x81" => "\xf0\x9f\x9a\xab",
        "\xee\x91\xbd" => "\xf0\x9f\x9a\xac",
        "\xee\x91\xbe" => "\xf0\x9f\x9a\xad",
        "\xee\x92\xae" => "\xf0\x9f\x9a\xb2",
        "\xee\xad\xb2" => "\xf0\x9f\x9a\xb6",
        "\xee\x92\xa5" => "\xf0\x9f\x9a\xbe",
        "\xee\x97\x98" => "\xf0\x9f\x9b\x80",
        "\xee\xae\x84" => "#\xe2\x83\xa3",
        "\xee\x96\xac" => "0\xe2\x83\xa3",
        "\xee\x94\xa2" => "1\xe2\x83\xa3",
        "\xee\x94\xa3" => "2\xe2\x83\xa3",
        "\xee\x94\xa4" => "3\xe2\x83\xa3",
        "\xee\x94\xa5" => "4\xe2\x83\xa3",
        "\xee\x94\xa6" => "5\xe2\x83\xa3",
        "\xee\x94\xa7" => "6\xe2\x83\xa3",
        "\xee\x94\xa8" => "7\xe2\x83\xa3",
        "\xee\x94\xa9" => "8\xe2\x83\xa3",
        "\xee\x94\xaa" => "9\xe2\x83\xa3",
        "\xee\xac\x91" => "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb3",
        "\xee\xac\x8e" => "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaa",
        "\xee\x97\x95" => "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb8",
        "\xee\xab\xba" => "\xf0\x9f\x87\xab\xf0\x9f\x87\xb7",
        "\xee\xac\x90" => "\xf0\x9f\x87\xac\xf0\x9f\x87\xa7",
        "\xee\xac\x8f" => "\xf0\x9f\x87\xae\xf0\x9f\x87\xb9",
        "\xee\x93\x8c" => "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb5",
        "\xee\xac\x92" => "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb7",
        "\xee\x97\x96" => "\xf0\x9f\x87\xb7\xf0\x9f\x87\xba",
        "\xee\x95\xb3" => "\xf0\x9f\x87\xba\xf0\x9f\x87\xb8",
    ),
    'softbank_to_unified' => array(
        "\xee\x89\x8e" => "\xc2\xa9",
        "\xee\x89\x8f" => "\xc2\xae",
        "\xee\x94\xb7" => "\xe2\x84\xa2",
        "\xee\x88\xb7" => "\xe2\x86\x96",
        "\xee\x88\xb6" => "\xe2\xa4\xb4",
        "\xee\x88\xb8" => "\xe2\xa4\xb5",
        "\xee\x88\xb9" => "\xe2\x86\x99",
        "\xee\x88\xbc" => "\xe2\x8f\xa9",
        "\xee\x88\xbd" => "\xe2\x8f\xaa",
        "\xee\x80\xad" => "\xf0\x9f\x95\x99",
        "\xee\x90\xb4" => "\xf0\x9f\x9a\x87",
        "\xee\x88\x9a" => "\xf0\x9f\x94\xb5",
        "\xee\x88\x9b" => "\xf0\x9f\x94\xb9",
        "\xee\x88\xba" => "\xe2\x96\xb6",
        "\xee\x88\xbb" => "\xe2\x97\x80",
        "\xee\x81\x8a" => "\xe2\x98\x80",
        "\xee\x81\x89" => "\xe2\x98\x81",
        "\xee\x80\x89" => "\xf0\x9f\x93\x9e",
        "\xee\x81\x8b" => "\xe2\x98\x94",
        "\xee\x81\x85" => "\xe2\x98\x95",
        "\xee\x80\x8f" => "\xe2\x98\x9d",
        "\xee\x90\x94" => "\xe2\x98\xba",
        "\xee\x88\xbf" => "\xe2\x99\x88",
        "\xee\x89\x80" => "\xe2\x99\x89",
        "\xee\x89\x81" => "\xe2\x99\x8a",
        "\xee\x89\x82" => "\xe2\x99\x8b",
        "\xee\x89\x83" => "\xe2\x99\x8c",
        "\xee\x89\x84" => "\xe2\x99\x8d",
        "\xee\x89\x85" => "\xe2\x99\x8e",
        "\xee\x89\x86" => "\xe2\x99\x8f",
        "\xee\x89\x87" => "\xe2\x99\x90",
        "\xee\x89\x88" => "\xe2\x99\x91",
        "\xee\x89\x89" => "\xe2\x99\x92",
        "\xee\x89\x8a" => "\xe2\x99\x93",
        "\xee\x88\x8e" => "\xe2\x99\xa0",
        "\xee\x88\x8f" => "\xe2\x99\xa3",
        "\xee\x88\x8c" => "\xe2\x99\xa5",
        "\xee\x88\x8d" => "\xe2\x99\xa6",
        "\xee\x84\xa3" => "\xe2\x99\xa8",
        "\xee\x88\x8a" => "\xe2\x99\xbf",
        "\xee\x88\x82" => "\xf0\x9f\x9a\xa2",
        "\xee\x89\x92" => "\xe2\x9a\xa0",
        "\xee\x84\xbd" => "\xe2\x9a\xa1",
        "\xee\x88\x99" => "\xf0\x9f\x94\xb4",
        "\xee\x80\x98" => "\xe2\x9a\xbd",
        "\xee\x80\x96" => "\xe2\x9a\xbe",
        "\xee\x81\x88" => "\xe2\x9b\x84",
        "\xee\x81\x8a\xee\x81\x89" => "\xe2\x9b\x85",
        "\xee\x89\x8b" => "\xe2\x9b\x8e",
        "\xee\x84\xb7" => "\xf0\x9f\x9a\xa7",
        "\xee\x80\xb7" => "\xe2\x9b\xaa",
        "\xee\x84\xa1" => "\xe2\x9b\xb2",
        "\xee\x80\x94" => "\xe2\x9b\xb3",
        "\xee\x80\x9c" => "\xe2\x9b\xb5",
        "\xee\x84\xa2" => "\xe2\x9b\xba",
        "\xee\x80\xba" => "\xe2\x9b\xbd",
        "\xee\x8c\x93" => "\xe2\x9c\x82",
        "\xee\x80\x9d" => "\xe2\x9c\x88",
        "\xee\x84\x83" => "\xf0\x9f\x93\xa9",
        "\xee\x80\x90" => "\xe2\x9c\x8a",
        "\xee\x80\x92" => "\xf0\x9f\x99\x8b",
        "\xee\x80\x91" => "\xe2\x9c\x8c",
        "\xee\x8c\x81" => "\xf0\x9f\x93\x9d",
        "\xee\x8c\xb3" => "\xe2\x9d\x8e",
        "\xee\x8c\xae" => "\xe2\x9d\x87",
        "\xee\x88\x86" => "\xe2\x9c\xb3",
        "\xee\x88\x85" => "\xe2\x9c\xb4",
        "\xee\x80\xa0" => "\xe2\x9d\x93",
        "\xee\x8c\xb6" => "\xe2\x9d\x94",
        "\xee\x8c\xb7" => "\xe2\x9d\x95",
        "\xee\x80\xa1" => "\xe2\x9d\x97",
        "\xee\x80\xa2" => "\xe2\x9d\xa4",
        "\xee\x88\xb4" => "\xe2\x9e\xa1",
        "\xee\x88\x91" => "\xe2\x9e\xbf",
        "\xee\x88\xb5" => "\xf0\x9f\x94\x99",
        "\xee\x88\xb2" => "\xe2\xac\x86",
        "\xee\x88\xb3" => "\xe2\xac\x87",
        "\xee\x8c\xaf" => "\xe2\xad\x90",
        "\xee\x8c\xb2" => "\xe2\xad\x95",
        "\xee\x84\xac" => "\xe3\x80\xbd",
        "\xee\x8c\x8d" => "\xe3\x8a\x97",
        "\xee\x8c\x95" => "\xe3\x8a\x99",
        "\xee\x84\xad" => "\xf0\x9f\x80\x84",
        "\xee\x94\xb2" => "\xf0\x9f\x85\xb0",
        "\xee\x94\xb3" => "\xf0\x9f\x85\xb1",
        "\xee\x94\xb5" => "\xf0\x9f\x85\xbe",
        "\xee\x85\x8f" => "\xf0\x9f\x85\xbf",
        "\xee\x94\xb4" => "\xf0\x9f\x86\x8e",
        "\xee\x88\x94" => "\xf0\x9f\x86\x92",
        "\xee\x88\xa9" => "\xf0\x9f\x86\x94",
        "\xee\x88\x92" => "\xf0\x9f\x86\x95",
        "\xee\x89\x8d" => "\xf0\x9f\x86\x97",
        "\xee\x88\x93" => "\xf0\x9f\x86\x99",
        "\xee\x84\xae" => "\xf0\x9f\x86\x9a",
        "\xee\x88\x83" => "\xf0\x9f\x88\x81",
        "\xee\x88\xa8" => "\xf0\x9f\x88\x82",
        "\xee\x88\x96" => "\xf0\x9f\x88\x9a",
        "\xee\x88\xac" => "\xf0\x9f\x88\xaf",
        "\xee\x88\xab" => "\xf0\x9f\x88\xb3",
        "\xee\x88\xaa" => "\xf0\x9f\x88\xb5",
        "\xee\x88\x95" => "\xf0\x9f\x88\xb6",
        "\xee\x88\x97" => "\xf0\x9f\x88\xb7",
        "\xee\x88\x98" => "\xf0\x9f\x88\xb8",
        "\xee\x88\xa7" => "\xf0\x9f\x88\xb9",
        "\xee\x88\xad" => "\xf0\x9f\x88\xba",
        "\xee\x88\xa6" => "\xf0\x9f\x89\x90",
        "\xee\x91\x83" => "\xf0\x9f\x8c\x80",
        "\xee\x90\xbc" => "\xf0\x9f\x8c\x82",
        "\xee\x91\x8b" => "\xf0\x9f\x8c\x8c",
        "\xee\x81\x8d" => "\xf0\x9f\x8c\x84",
        "\xee\x91\x89" => "\xf0\x9f\x8c\x85",
        "\xee\x85\x86" => "\xf0\x9f\x8c\x86",
        "\xee\x91\x8a" => "\xf0\x9f\x8c\x87",
        "\xee\x91\x8c" => "\xf0\x9f\x8c\x88",
        "\xee\x90\xbe" => "\xf0\x9f\x8c\x8a",
        "\xee\x81\x8c" => "\xf0\x9f\x8c\x9b",
        "\xee\x8c\xb5" => "\xf0\x9f\x8c\x9f",
        "\xee\x84\x90" => "\xf0\x9f\x8d\x80",
        "\xee\x8c\x87" => "\xf0\x9f\x8c\xb4",
        "\xee\x8c\x88" => "\xf0\x9f\x8c\xb5",
        "\xee\x8c\x84" => "\xf0\x9f\x8c\xb7",
        "\xee\x80\xb0" => "\xf0\x9f\x8c\xb8",
        "\xee\x80\xb2" => "\xf0\x9f\x8c\xb9",
        "\xee\x8c\x83" => "\xf0\x9f\x8c\xba",
        "\xee\x8c\x85" => "\xf0\x9f\x8c\xbc",
        "\xee\x91\x84" => "\xf0\x9f\x8c\xbe",
        "\xee\x84\x98" => "\xf0\x9f\x8d\x81",
        "\xee\x84\x99" => "\xf0\x9f\x8d\x82",
        "\xee\x91\x87" => "\xf0\x9f\x8d\x83",
        "\xee\x8d\x89" => "\xf0\x9f\x8d\x85",
        "\xee\x8d\x8a" => "\xf0\x9f\x8d\x86",
        "\xee\x8d\x88" => "\xf0\x9f\x8d\x89",
        "\xee\x8d\x86" => "\xf0\x9f\x8d\x8a",
        "\xee\x8d\x85" => "\xf0\x9f\x8d\x8f",
        "\xee\x8d\x87" => "\xf0\x9f\x8d\x93",
        "\xee\x84\xa0" => "\xf0\x9f\x8d\x94",
        "\xee\x8c\xbd" => "\xf0\x9f\x8d\x98",
        "\xee\x8d\x82" => "\xf0\x9f\x8d\x99",
        "\xee\x8c\xbe" => "\xf0\x9f\x8d\x9a",
        "\xee\x8d\x81" => "\xf0\x9f\x8d\x9b",
        "\xee\x8d\x80" => "\xf0\x9f\x8d\x9c",
        "\xee\x8c\xbf" => "\xf0\x9f\x8d\x9d",
        "\xee\x8c\xb9" => "\xf0\x9f\x8d\x9e",
        "\xee\x8c\xbb" => "\xf0\x9f\x8d\x9f",
        "\xee\x8c\xbc" => "\xf0\x9f\x8d\xa1",
        "\xee\x8d\x83" => "\xf0\x9f\x8d\xa2",
        "\xee\x8d\x84" => "\xf0\x9f\x8d\xa3",
        "\xee\x8c\xba" => "\xf0\x9f\x8d\xa6",
        "\xee\x90\xbf" => "\xf0\x9f\x8d\xa7",
        "\xee\x81\x86" => "\xf0\x9f\x8d\xb0",
        "\xee\x8d\x8c" => "\xf0\x9f\x8d\xb1",
        "\xee\x8d\x8d" => "\xf0\x9f\x8d\xb2",
        "\xee\x85\x87" => "\xf0\x9f\x8d\xb3",
        "\xee\x81\x83" => "\xf0\x9f\x8d\xb4",
        "\xee\x8c\xb8" => "\xf0\x9f\x8d\xb5",
        "\xee\x8c\x8b" => "\xf0\x9f\x8f\xae",
        "\xee\x81\x84" => "\xf0\x9f\x8d\xb9",
        "\xee\x81\x87" => "\xf0\x9f\x8d\xba",
        "\xee\x8c\x8c" => "\xf0\x9f\x8d\xbb",
        "\xee\x8c\x94" => "\xf0\x9f\x8e\x80",
        "\xee\x84\x92" => "\xf0\x9f\x93\xa6",
        "\xee\x8d\x8b" => "\xf0\x9f\x8e\x82",
        "\xee\x91\x85" => "\xf0\x9f\x8e\x83",
        "\xee\x80\xb3" => "\xf0\x9f\x8e\x84",
        "\xee\x91\x88" => "\xf0\x9f\x8e\x85",
        "\xee\x84\x97" => "\xf0\x9f\x8e\x86",
        "\xee\x91\x80" => "\xf0\x9f\x8e\x87",
        "\xee\x8c\x90" => "\xf0\x9f\x8e\x88",
        "\xee\x8c\x92" => "\xf0\x9f\x8e\x89",
        "\xee\x85\x83" => "\xf0\x9f\x8e\x8c",
        "\xee\x90\xb6" => "\xf0\x9f\x8e\x8d",
        "\xee\x90\xb8" => "\xf0\x9f\x8e\x8e",
        "\xee\x90\xbb" => "\xf0\x9f\x8e\x8f",
        "\xee\x91\x82" => "\xf0\x9f\x8e\x90",
        "\xee\x91\x86" => "\xf0\x9f\x8e\x91",
        "\xee\x90\xba" => "\xf0\x9f\x8e\x92",
        "\xee\x90\xb9" => "\xf0\x9f\x8e\x93",
        "\xee\x84\xa4" => "\xf0\x9f\x8e\xa1",
        "\xee\x90\xb3" => "\xf0\x9f\x8e\xa2",
        "\xee\x80\x99" => "\xf0\x9f\x90\xa1",
        "\xee\x80\xbc" => "\xf0\x9f\x8e\xa4",
        "\xee\x80\xbd" => "\xf0\x9f\x93\xb9",
        "\xee\x94\x87" => "\xf0\x9f\x8e\xa6",
        "\xee\x8c\x8a" => "\xf0\x9f\x8e\xa7",
        "\xee\x94\x82" => "\xf0\x9f\x8e\xa8",
        "\xee\x94\x83" => "\xf0\x9f\x8e\xad",
        "\xee\x84\xa5" => "\xf0\x9f\x8e\xab",
        "\xee\x8c\xa4" => "\xf0\x9f\x8e\xac",
        "\xee\x84\xb0" => "\xf0\x9f\x8e\xaf",
        "\xee\x84\xb3" => "\xf0\x9f\x8e\xb0",
        "\xee\x90\xac" => "\xf0\x9f\x8e\xb1",
        "\xee\x80\xbe" => "\xf0\x9f\x8e\xb5",
        "\xee\x8c\xa6" => "\xf0\x9f\x8e\xbc",
        "\xee\x81\x80" => "\xf0\x9f\x8e\xb7",
        "\xee\x81\x81" => "\xf0\x9f\x8e\xb8",
        "\xee\x81\x82" => "\xf0\x9f\x8e\xba",
        "\xee\x80\x95" => "\xf0\x9f\x8e\xbe",
        "\xee\x80\x93" => "\xf0\x9f\x8e\xbf",
        "\xee\x90\xaa" => "\xf0\x9f\x8f\x80",
        "\xee\x84\xb2" => "\xf0\x9f\x8f\x81",
        "\xee\x84\x95" => "\xf0\x9f\x8f\x83",
        "\xee\x80\x97" => "\xf0\x9f\x8f\x84",
        "\xee\x84\xb1" => "\xf0\x9f\x8f\x86",
        "\xee\x90\xab" => "\xf0\x9f\x8f\x88",
        "\xee\x90\xad" => "\xf0\x9f\x8f\x8a",
        "\xee\x80\xb6" => "\xf0\x9f\x8f\xa1",
        "\xee\x80\xb8" => "\xf0\x9f\x8f\xa2",
        "\xee\x85\x93" => "\xf0\x9f\x8f\xa3",
        "\xee\x85\x95" => "\xf0\x9f\x8f\xa5",
        "\xee\x85\x8d" => "\xf0\x9f\x8f\xa6",
        "\xee\x85\x94" => "\xf0\x9f\x8f\xa7",
        "\xee\x85\x98" => "\xf0\x9f\x8f\xa8",
        "\xee\x94\x81" => "\xf0\x9f\x8f\xa9",
        "\xee\x85\x96" => "\xf0\x9f\x8f\xaa",
        "\xee\x85\x97" => "\xf0\x9f\x8f\xab",
        "\xee\x94\x84" => "\xf0\x9f\x8f\xac",
        "\xee\x94\x88" => "\xf0\x9f\x8f\xad",
        "\xee\x94\x85" => "\xf0\x9f\x8f\xaf",
        "\xee\x94\x86" => "\xf0\x9f\x8f\xb0",
        "\xee\x94\xad" => "\xf0\x9f\x90\x8d",
        "\xee\x84\xb4" => "\xf0\x9f\x90\x8e",
        "\xee\x94\xa9" => "\xf0\x9f\x90\x91",
        "\xee\x94\xa8" => "\xf0\x9f\x90\x92",
        "\xee\x94\xae" => "\xf0\x9f\x90\x94",
        "\xee\x94\xaf" => "\xf0\x9f\x90\x97",
        "\xee\x94\xa6" => "\xf0\x9f\x90\x98",
        "\xee\x84\x8a" => "\xf0\x9f\x90\x99",
        "\xee\x91\x81" => "\xf0\x9f\x90\x9a",
        "\xee\x94\xa5" => "\xf0\x9f\x90\x9b",
        "\xee\x94\xa2" => "\xf0\x9f\x90\xa0",
        "\xee\x94\xa3" => "\xf0\x9f\x90\xa5",
        "\xee\x94\xa1" => "\xf0\x9f\x90\xa6",
        "\xee\x81\x95" => "\xf0\x9f\x90\xa7",
        "\xee\x94\xa7" => "\xf0\x9f\x90\xa8",
        "\xee\x81\x92" => "\xf0\x9f\x90\xb6",
        "\xee\x94\xb0" => "\xf0\x9f\x90\xab",
        "\xee\x94\xa0" => "\xf0\x9f\x90\xac",
        "\xee\x81\x93" => "\xf0\x9f\x90\xad",
        "\xee\x94\xab" => "\xf0\x9f\x90\xae",
        "\xee\x81\x90" => "\xf0\x9f\x90\xaf",
        "\xee\x94\xac" => "\xf0\x9f\x90\xb0",
        "\xee\x81\x8f" => "\xf0\x9f\x90\xb1",
        "\xee\x81\x94" => "\xf0\x9f\x90\xb3",
        "\xee\x80\x9a" => "\xf0\x9f\x90\xb4",
        "\xee\x84\x89" => "\xf0\x9f\x90\xb5",
        "\xee\x84\x8b" => "\xf0\x9f\x90\xbd",
        "\xee\x94\xb1" => "\xf0\x9f\x90\xb8",
        "\xee\x94\xa4" => "\xf0\x9f\x90\xb9",
        "\xee\x94\xaa" => "\xf0\x9f\x90\xba",
        "\xee\x81\x91" => "\xf0\x9f\x90\xbb",
        "\xee\x94\xb6" => "\xf0\x9f\x91\xa3",
        "\xee\x90\x99" => "\xf0\x9f\x91\x80",
        "\xee\x90\x9b" => "\xf0\x9f\x91\x82",
        "\xee\x90\x9a" => "\xf0\x9f\x91\x83",
        "\xee\x90\x9c" => "\xf0\x9f\x91\x84",
        "\xee\x90\x89" => "\xf0\x9f\x98\x9d",
        "\xee\x88\xae" => "\xf0\x9f\x91\x86",
        "\xee\x88\xaf" => "\xf0\x9f\x91\x87",
        "\xee\x88\xb0" => "\xf0\x9f\x91\x88",
        "\xee\x88\xb1" => "\xf0\x9f\x91\x89",
        "\xee\x80\x8d" => "\xf0\x9f\x91\x8a",
        "\xee\x90\x9e" => "\xf0\x9f\x91\x8b",
        "\xee\x90\xa0" => "\xf0\x9f\x91\x8c",
        "\xee\x80\x8e" => "\xf0\x9f\x91\x8d",
        "\xee\x90\xa1" => "\xf0\x9f\x91\x8e",
        "\xee\x90\x9f" => "\xf0\x9f\x91\x8f",
        "\xee\x90\xa2" => "\xf0\x9f\x91\x90",
        "\xee\x84\x8e" => "\xf0\x9f\x91\x91",
        "\xee\x8c\x98" => "\xf0\x9f\x91\x92",
        "\xee\x8c\x82" => "\xf0\x9f\x91\x94",
        "\xee\x80\x86" => "\xf0\x9f\x91\x9a",
        "\xee\x8c\x99" => "\xf0\x9f\x91\x97",
        "\xee\x8c\xa1" => "\xf0\x9f\x91\x98",
        "\xee\x8c\xa2" => "\xf0\x9f\x91\x99",
        "\xee\x8c\xa3" => "\xf0\x9f\x91\x9c",
        "\xee\x80\x87" => "\xf0\x9f\x91\x9f",
        "\xee\x84\xbe" => "\xf0\x9f\x91\xa0",
        "\xee\x8c\x9a" => "\xf0\x9f\x91\xa1",
        "\xee\x8c\x9b" => "\xf0\x9f\x91\xa2",
        "\xee\x80\x81" => "\xf0\x9f\x91\xa6",
        "\xee\x80\x82" => "\xf0\x9f\x91\xa7",
        "\xee\x80\x84" => "\xf0\x9f\x91\xa8",
        "\xee\x80\x85" => "\xf0\x9f\x91\xa9",
        "\xee\x90\xa8" => "\xf0\x9f\x91\xab",
        "\xee\x85\x92" => "\xf0\x9f\x91\xae",
        "\xee\x90\xa9" => "\xf0\x9f\x91\xaf",
        "\xee\x94\x95" => "\xf0\x9f\x91\xb1",
        "\xee\x94\x96" => "\xf0\x9f\x91\xb2",
        "\xee\x94\x97" => "\xf0\x9f\x91\xb3",
        "\xee\x94\x98" => "\xf0\x9f\x91\xb4",
        "\xee\x94\x99" => "\xf0\x9f\x91\xb5",
        "\xee\x94\x9a" => "\xf0\x9f\x91\xb6",
        "\xee\x94\x9b" => "\xf0\x9f\x91\xb7",
        "\xee\x94\x9c" => "\xf0\x9f\x91\xb8",
        "\xee\x84\x9b" => "\xf0\x9f\x91\xbb",
        "\xee\x81\x8e" => "\xf0\x9f\x91\xbc",
        "\xee\x84\x8c" => "\xf0\x9f\x91\xbd",
        "\xee\x84\xab" => "\xf0\x9f\x91\xbe",
        "\xee\x84\x9a" => "\xf0\x9f\x91\xbf",
        "\xee\x84\x9c" => "\xf0\x9f\x92\x80",
        "\xee\x89\x93" => "\xf0\x9f\x92\x81",
        "\xee\x94\x9e" => "\xf0\x9f\x92\x82",
        "\xee\x94\x9f" => "\xf0\x9f\x92\x83",
        "\xee\x8c\x9c" => "\xf0\x9f\x92\x84",
        "\xee\x8c\x9d" => "\xf0\x9f\x92\x85",
        "\xee\x8c\x9e" => "\xf0\x9f\x92\x86",
        "\xee\x8c\x9f" => "\xf0\x9f\x92\x87",
        "\xee\x8c\xa0" => "\xf0\x9f\x92\x88",
        "\xee\x84\xbb" => "\xf0\x9f\x92\x89",
        "\xee\x8c\x8f" => "\xf0\x9f\x92\x8a",
        "\xee\x80\x83" => "\xf0\x9f\x92\x8b",
        "\xee\x84\x83\xee\x8c\xa8" => "\xf0\x9f\x92\x8c",
        "\xee\x80\xb4" => "\xf0\x9f\x92\x8d",
        "\xee\x80\xb5" => "\xf0\x9f\x92\x8e",
        "\xee\x84\x91" => "\xf0\x9f\x92\x8f",
        "\xee\x8c\x86" => "\xf0\x9f\x92\x90",
        "\xee\x90\xa5" => "\xf0\x9f\x92\x91",
        "\xee\x90\xbd" => "\xf0\x9f\x92\x92",
        "\xee\x8c\xa7" => "\xf0\x9f\x92\x9e",
        "\xee\x80\xa3" => "\xf0\x9f\x92\x94",
        "\xee\x8c\xa8" => "\xf0\x9f\x92\x97",
        "\xee\x8c\xa9" => "\xf0\x9f\x92\x98",
        "\xee\x8c\xaa" => "\xf0\x9f\x92\x99",
        "\xee\x8c\xab" => "\xf0\x9f\x92\x9a",
        "\xee\x8c\xac" => "\xf0\x9f\x92\x9b",
        "\xee\x8c\xad" => "\xf0\x9f\x92\x9c",
        "\xee\x90\xb7" => "\xf0\x9f\x92\x9d",
        "\xee\x88\x84" => "\xf0\x9f\x92\x9f",
        "\xee\x84\x8f" => "\xf0\x9f\x92\xa1",
        "\xee\x8c\xb4" => "\xf0\x9f\x92\xa2",
        "\xee\x8c\x91" => "\xf0\x9f\x92\xa3",
        "\xee\x84\xbc" => "\xf0\x9f\x92\xa4",
        "\xee\x8c\xb1" => "\xf0\x9f\x92\xa7",
        "\xee\x8c\xb0" => "\xf0\x9f\x92\xa8",
        "\xee\x81\x9a" => "\xf0\x9f\x92\xa9",
        "\xee\x85\x8c" => "\xf0\x9f\x92\xaa",
        "\xee\x90\x87" => "\xf0\x9f\x98\x96",
        "\xee\x84\xaf" => "\xf0\x9f\x92\xb5",
        "\xee\x85\x89" => "\xf0\x9f\x92\xb1",
        "\xee\x85\x8a" => "\xf0\x9f\x93\x8a",
        "\xee\x84\x9f" => "\xf0\x9f\x92\xba",
        "\xee\x80\x8c" => "\xf0\x9f\x92\xbb",
        "\xee\x84\x9e" => "\xf0\x9f\x92\xbc",
        "\xee\x8c\x96" => "\xf0\x9f\x92\xbe",
        "\xee\x84\xa6" => "\xf0\x9f\x92\xbf",
        "\xee\x84\xa7" => "\xf0\x9f\x93\x80",
        "\xee\x85\x88" => "\xf0\x9f\x93\x9a",
        "\xee\x80\x8b" => "\xf0\x9f\x93\xa0",
        "\xee\x85\x8b" => "\xf0\x9f\x93\xa1",
        "\xee\x85\x82" => "\xf0\x9f\x93\xa2",
        "\xee\x8c\x97" => "\xf0\x9f\x93\xa3",
        "\xee\x84\x81" => "\xf0\x9f\x93\xab",
        "\xee\x84\x82" => "\xf0\x9f\x93\xae",
        "\xee\x80\x8a" => "\xf0\x9f\x93\xb1",
        "\xee\x84\x84" => "\xf0\x9f\x93\xb2",
        "\xee\x89\x90" => "\xf0\x9f\x93\xb3",
        "\xee\x89\x91" => "\xf0\x9f\x93\xb4",
        "\xee\x88\x8b" => "\xf0\x9f\x93\xb6",
        "\xee\x80\x88" => "\xf0\x9f\x93\xb7",
        "\xee\x84\xaa" => "\xf0\x9f\x93\xba",
        "\xee\x84\xa8" => "\xf0\x9f\x93\xbb",
        "\xee\x84\xa9" => "\xf0\x9f\x93\xbc",
        "\xee\x85\x81" => "\xf0\x9f\x94\x8a",
        "\xee\x84\x94" => "\xf0\x9f\x94\x8e",
        "\xee\x85\x84" => "\xf0\x9f\x94\x92",
        "\xee\x80\xbf" => "\xf0\x9f\x94\x91",
        "\xee\x85\x85" => "\xf0\x9f\x94\x93",
        "\xee\x8c\xa5" => "\xf0\x9f\x94\x94",
        "\xee\x89\x8c" => "\xf0\x9f\x94\x9d",
        "\xee\x88\x87" => "\xf0\x9f\x94\x9e",
        "\xee\x84\x9d" => "\xf0\x9f\x94\xa5",
        "\xee\x84\x96" => "\xf0\x9f\x94\xa8",
        "\xee\x84\x93" => "\xf0\x9f\x94\xab",
        "\xee\x88\xbe" => "\xf0\x9f\x94\xaf",
        "\xee\x88\x89" => "\xf0\x9f\x94\xb0",
        "\xee\x80\xb1" => "\xf0\x9f\x94\xb1",
        "\xee\x80\xa4" => "\xf0\x9f\x95\x90",
        "\xee\x80\xa5" => "\xf0\x9f\x95\x91",
        "\xee\x80\xa6" => "\xf0\x9f\x95\x92",
        "\xee\x80\xa7" => "\xf0\x9f\x95\x93",
        "\xee\x80\xa8" => "\xf0\x9f\x95\x94",
        "\xee\x80\xa9" => "\xf0\x9f\x95\x95",
        "\xee\x80\xaa" => "\xf0\x9f\x95\x96",
        "\xee\x80\xab" => "\xf0\x9f\x95\x97",
        "\xee\x80\xac" => "\xf0\x9f\x95\x98",
        "\xee\x80\xae" => "\xf0\x9f\x95\x9a",
        "\xee\x80\xaf" => "\xf0\x9f\x95\x9b",
        "\xee\x80\xbb" => "\xf0\x9f\x97\xbb",
        "\xee\x94\x89" => "\xf0\x9f\x97\xbc",
        "\xee\x94\x9d" => "\xf0\x9f\x97\xbd",
        "\xee\x90\x84" => "\xf0\x9f\x98\xbc",
        "\xee\x90\x92" => "\xf0\x9f\x98\xb9",
        "\xee\x81\x97" => "\xf0\x9f\x98\xba",
        "\xee\x90\x95" => "\xf0\x9f\x98\x84",
        "\xee\x90\x95\xee\x8c\xb1" => "\xf0\x9f\x98\x85",
        "\xee\x90\x8a" => "\xf0\x9f\x98\x8c",
        "\xee\x90\x85" => "\xf0\x9f\x98\x89",
        "\xee\x81\x96" => "\xf0\x9f\x98\x8b",
        "\xee\x84\x86" => "\xf0\x9f\x98\xbb",
        "\xee\x90\x82" => "\xf0\x9f\x98\x8f",
        "\xee\x90\x8e" => "\xf0\x9f\x98\x92",
        "\xee\x84\x88" => "\xf0\x9f\x98\x93",
        "\xee\x90\x83" => "\xf0\x9f\x99\x8d",
        "\xee\x90\x98" => "\xf0\x9f\x98\xbd",
        "\xee\x90\x97" => "\xf0\x9f\x98\x9a",
        "\xee\x84\x85" => "\xf0\x9f\x98\x9c",
        "\xee\x81\x98" => "\xf0\x9f\x98\x9e",
        "\xee\x81\x99" => "\xf0\x9f\x98\xa0",
        "\xee\x90\x96" => "\xf0\x9f\x99\x8e",
        "\xee\x90\x93" => "\xf0\x9f\x98\xbf",
        "\xee\x90\x86" => "\xf0\x9f\x98\xb5",
        "\xee\x90\x81" => "\xf0\x9f\x98\xa5",
        "\xee\x90\x8b" => "\xf0\x9f\x98\xa8",
        "\xee\x90\x88" => "\xf0\x9f\x98\xaa",
        "\xee\x90\x91" => "\xf0\x9f\x98\xad",
        "\xee\x90\x8f" => "\xf0\x9f\x98\xb0",
        "\xee\x84\x87" => "\xf0\x9f\x98\xb1",
        "\xee\x90\x90" => "\xf0\x9f\x98\xb2",
        "\xee\x90\x8d" => "\xf0\x9f\x98\xb3",
        "\xee\x90\x8c" => "\xf0\x9f\x98\xb7",
        "\xee\x90\xa3" => "\xf0\x9f\x99\x85",
        "\xee\x90\xa4" => "\xf0\x9f\x99\x86",
        "\xee\x90\xa6" => "\xf0\x9f\x99\x87",
        "\xee\x90\xa7" => "\xf0\x9f\x99\x8c",
        "\xee\x90\x9d" => "\xf0\x9f\x99\x8f",
        "\xee\x84\x8d" => "\xf0\x9f\x9a\x80",
        "\xee\x80\x9e" => "\xf0\x9f\x9a\x83",
        "\xee\x90\xb5" => "\xf0\x9f\x9a\x84",
        "\xee\x80\x9f" => "\xf0\x9f\x9a\x85",
        "\xee\x80\xb9" => "\xf0\x9f\x9a\x89",
        "\xee\x85\x99" => "\xf0\x9f\x9a\x8c",
        "\xee\x85\x90" => "\xf0\x9f\x9a\x8f",
        "\xee\x90\xb1" => "\xf0\x9f\x9a\x91",
        "\xee\x90\xb0" => "\xf0\x9f\x9a\x92",
        "\xee\x90\xb2" => "\xf0\x9f\x9a\xa8",
        "\xee\x85\x9a" => "\xf0\x9f\x9a\x95",
        "\xee\x80\x9b" => "\xf0\x9f\x9a\x97",
        "\xee\x90\xae" => "\xf0\x9f\x9a\x99",
        "\xee\x90\xaf" => "\xf0\x9f\x9a\x9a",
        "\xee\x84\xb5" => "\xf0\x9f\x9a\xa4",
        "\xee\x85\x8e" => "\xf0\x9f\x9a\xa5",
        "\xee\x8c\x8e" => "\xf0\x9f\x9a\xac",
        "\xee\x88\x88" => "\xf0\x9f\x9a\xad",
        "\xee\x84\xb6" => "\xf0\x9f\x9a\xb2",
        "\xee\x88\x81" => "\xf0\x9f\x9a\xb6",
        "\xee\x84\xb8" => "\xf0\x9f\x9a\xb9",
        "\xee\x84\xb9" => "\xf0\x9f\x9a\xba",
        "\xee\x85\x91" => "\xf0\x9f\x9a\xbb",
        "\xee\x84\xba" => "\xf0\x9f\x9a\xbc",
        "\xee\x85\x80" => "\xf0\x9f\x9a\xbd",
        "\xee\x8c\x89" => "\xf0\x9f\x9a\xbe",
        "\xee\x84\xbf" => "\xf0\x9f\x9b\x80",
        "\xee\x88\x90" => "#\xe2\x83\xa3",
        "\xee\x88\xa5" => "0\xe2\x83\xa3",
        "\xee\x88\x9c" => "1\xe2\x83\xa3",
        "\xee\x88\x9d" => "2\xe2\x83\xa3",
        "\xee\x88\x9e" => "3\xe2\x83\xa3",
        "\xee\x88\x9f" => "4\xe2\x83\xa3",
        "\xee\x88\xa0" => "5\xe2\x83\xa3",
        "\xee\x88\xa1" => "6\xe2\x83\xa3",
        "\xee\x88\xa2" => "7\xe2\x83\xa3",
        "\xee\x88\xa3" => "8\xe2\x83\xa3",
        "\xee\x88\xa4" => "9\xe2\x83\xa3",
        "\xee\x94\x93" => "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb3",
        "\xee\x94\x8e" => "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaa",
        "\xee\x94\x91" => "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb8",
        "\xee\x94\x8d" => "\xf0\x9f\x87\xab\xf0\x9f\x87\xb7",
        "\xee\x94\x90" => "\xf0\x9f\x87\xac\xf0\x9f\x87\xa7",
        "\xee\x94\x8f" => "\xf0\x9f\x87\xae\xf0\x9f\x87\xb9",
        "\xee\x94\x8b" => "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb5",
        "\xee\x94\x94" => "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb7",
        "\xee\x94\x92" => "\xf0\x9f\x87\xb7\xf0\x9f\x87\xba",
        "\xee\x94\x8c" => "\xf0\x9f\x87\xba\xf0\x9f\x87\xb8",
    ),
    'google_to_unified' => array(
        "\xf3\xbe\xac\xa9" => "\xc2\xa9",
        "\xf3\xbe\xac\xad" => "\xc2\xae",
        "\xf3\xbe\xac\x86" => "\xe2\x80\xbc",
        "\xf3\xbe\xac\x85" => "\xe2\x81\x89",
        "\xf3\xbe\xac\xaa" => "\xe2\x84\xa2",
        "\xf3\xbe\xad\x87" => "\xe2\x84\xb9",
        "\xf3\xbe\xab\xb6" => "\xe2\x86\x94",
        "\xf3\xbe\xab\xb7" => "\xe2\x86\x95",
        "\xf3\xbe\xab\xb2" => "\xe2\x86\x96",
        "\xf3\xbe\xab\xb0" => "\xe2\x86\x97",
        "\xf3\xbe\xab\xb1" => "\xe2\x86\x98",
        "\xf3\xbe\xab\xb3" => "\xe2\x86\x99",
        "\xf3\xbe\xae\x83" => "\xe2\x86\xa9",
        "\xf3\xbe\xae\x88" => "\xe2\x86\xaa",
        "\xf3\xbe\x80\x9d" => "\xe2\x8c\x9a",
        "\xf3\xbe\x80\x9c" => "\xe2\x8c\x9b",
        "\xf3\xbe\xab\xbe" => "\xe2\x8f\xa9",
        "\xf3\xbe\xab\xbf" => "\xe2\x8f\xaa",
        "\xf3\xbe\xac\x83" => "\xe2\x8f\xab",
        "\xf3\xbe\xac\x82" => "\xe2\x8f\xac",
        "\xf3\xbe\x80\xaa" => "\xe2\x8f\xb0",
        "\xf3\xbe\x80\x9b" => "\xe2\x8f\xb3",
        "\xf3\xbe\x9f\xa1" => "\xe2\x93\x82",
        "\xf3\xbe\xad\xae" => "\xe2\x96\xaa",
        "\xf3\xbe\xad\xad" => "\xe2\x96\xab",
        "\xf3\xbe\xab\xbc" => "\xe2\x96\xb6",
        "\xf3\xbe\xab\xbd" => "\xe2\x97\x80",
        "\xf3\xbe\xad\xb1" => "\xe2\x97\xbb",
        "\xf3\xbe\xad\xb2" => "\xe2\x97\xbc",
        "\xf3\xbe\xad\xaf" => "\xe2\x97\xbd",
        "\xf3\xbe\xad\xb0" => "\xe2\x97\xbe",
        "\xf3\xbe\x80\x80" => "\xe2\x98\x80",
        "\xf3\xbe\x80\x81" => "\xe2\x98\x81",
        "\xf3\xbe\x94\xa3" => "\xe2\x98\x8e",
        "\xf3\xbe\xae\x8b" => "\xe2\x98\x91",
        "\xf3\xbe\x80\x82" => "\xe2\x98\x94",
        "\xf3\xbe\xa6\x81" => "\xe2\x98\x95",
        "\xf3\xbe\xae\x98" => "\xe2\x98\x9d",
        "\xf3\xbe\x8c\xb6" => "\xe2\x98\xba",
        "\xf3\xbe\x80\xab" => "\xe2\x99\x88",
        "\xf3\xbe\x80\xac" => "\xe2\x99\x89",
        "\xf3\xbe\x80\xad" => "\xe2\x99\x8a",
        "\xf3\xbe\x80\xae" => "\xe2\x99\x8b",
        "\xf3\xbe\x80\xaf" => "\xe2\x99\x8c",
        "\xf3\xbe\x80\xb0" => "\xe2\x99\x8d",
        "\xf3\xbe\x80\xb1" => "\xe2\x99\x8e",
        "\xf3\xbe\x80\xb2" => "\xe2\x99\x8f",
        "\xf3\xbe\x80\xb3" => "\xe2\x99\x90",
        "\xf3\xbe\x80\xb4" => "\xe2\x99\x91",
        "\xf3\xbe\x80\xb5" => "\xe2\x99\x92",
        "\xf3\xbe\x80\xb6" => "\xe2\x99\x93",
        "\xf3\xbe\xac\x9b" => "\xe2\x99\xa0",
        "\xf3\xbe\xac\x9d" => "\xe2\x99\xa3",
        "\xf3\xbe\xac\x9a" => "\xe2\x99\xa5",
        "\xf3\xbe\xac\x9c" => "\xe2\x99\xa6",
        "\xf3\xbe\x9f\xba" => "\xe2\x99\xa8",
        "\xf3\xbe\xac\xac" => "\xe2\x99\xbb",
        "\xf3\xbe\xac\xa0" => "\xe2\x99\xbf",
        "\xf3\xbe\x93\x81" => "\xe2\x9a\x93",
        "\xf3\xbe\xac\xa3" => "\xe2\x9a\xa0",
        "\xf3\xbe\x80\x84" => "\xe2\x9a\xa1",
        "\xf3\xbe\xad\xa5" => "\xe2\x9a\xaa",
        "\xf3\xbe\xad\xa6" => "\xe2\x9a\xab",
        "\xf3\xbe\x9f\x94" => "\xe2\x9a\xbd",
        "\xf3\xbe\x9f\x91" => "\xe2\x9a\xbe",
        "\xf3\xbe\x80\x83" => "\xe2\x9b\x84",
        "\xf3\xbe\x80\x8f" => "\xe2\x9b\x85",
        "\xf3\xbe\x80\xb7" => "\xe2\x9b\x8e",
        "\xf3\xbe\xac\xa6" => "\xe2\x9b\x94",
        "\xf3\xbe\x92\xbb" => "\xe2\x9b\xaa",
        "\xf3\xbe\x92\xbc" => "\xe2\x9b\xb2",
        "\xf3\xbe\x9f\x92" => "\xe2\x9b\xb3",
        "\xf3\xbe\x9f\xaa" => "\xe2\x9b\xb5",
        "\xf3\xbe\x9f\xbb" => "\xe2\x9b\xba",
        "\xf3\xbe\x9f\xb5" => "\xe2\x9b\xbd",
        "\xf3\xbe\x94\xbe" => "\xe2\x9c\x82",
        "\xf3\xbe\xad\x8a" => "\xe2\x9c\x85",
        "\xf3\xbe\x9f\xa9" => "\xe2\x9c\x88",
        "\xf3\xbe\x94\xa9" => "\xe2\x9c\x89",
        "\xf3\xbe\xae\x93" => "\xe2\x9c\x8a",
        "\xf3\xbe\xae\x95" => "\xe2\x9c\x8b",
        "\xf3\xbe\xae\x94" => "\xe2\x9c\x8c",
        "\xf3\xbe\x94\xb9" => "\xe2\x9c\x8f",
        "\xf3\xbe\x94\xb6" => "\xe2\x9c\x92",
        "\xf3\xbe\xad\x89" => "\xe2\x9c\x94",
        "\xf3\xbe\xad\x93" => "\xe2\x9c\x96",
        "\xf3\xbe\xad\xa0" => "\xe2\x9c\xa8",
        "\xf3\xbe\xad\xa2" => "\xe2\x9c\xb3",
        "\xf3\xbe\xad\xa1" => "\xe2\x9c\xb4",
        "\xf3\xbe\x80\x8e" => "\xe2\x9d\x84",
        "\xf3\xbe\xad\xb7" => "\xe2\x9d\x87",
        "\xf3\xbe\xad\x85" => "\xe2\x9d\x8c",
        "\xf3\xbe\xad\x86" => "\xe2\x9d\x8e",
        "\xf3\xbe\xac\x89" => "\xe2\x9d\x93",
        "\xf3\xbe\xac\x8a" => "\xe2\x9d\x94",
        "\xf3\xbe\xac\x8b" => "\xe2\x9d\x95",
        "\xf3\xbe\xac\x84" => "\xe2\x9d\x97",
        "\xf3\xbe\xac\x8c" => "\xe2\x9d\xa4",
        "\xf3\xbe\xad\x91" => "\xe2\x9e\x95",
        "\xf3\xbe\xad\x92" => "\xe2\x9e\x96",
        "\xf3\xbe\xad\x94" => "\xe2\x9e\x97",
        "\xf3\xbe\xab\xba" => "\xe2\x9e\xa1",
        "\xf3\xbe\xac\x88" => "\xe2\x9e\xb0",
        "\xf3\xbe\xa0\xab" => "\xe2\x9e\xbf",
        "\xf3\xbe\xab\xb4" => "\xe2\xa4\xb4",
        "\xf3\xbe\xab\xb5" => "\xe2\xa4\xb5",
        "\xf3\xbe\xab\xbb" => "\xe2\xac\x85",
        "\xf3\xbe\xab\xb8" => "\xe2\xac\x86",
        "\xf3\xbe\xab\xb9" => "\xe2\xac\x87",
        "\xf3\xbe\xad\xac" => "\xe2\xac\x9b",
        "\xf3\xbe\xad\xab" => "\xe2\xac\x9c",
        "\xf3\xbe\xad\xa8" => "\xe2\xad\x90",
        "\xf3\xbe\xad\x84" => "\xe2\xad\x95",
        "\xf3\xbe\xac\x87" => "\xe3\x80\xb0",
        "\xf3\xbe\xa0\x9b" => "\xe3\x80\xbd",
        "\xf3\xbe\xad\x83" => "\xe3\x8a\x97",
        "\xf3\xbe\xac\xab" => "\xe3\x8a\x99",
        "\xf3\xbe\xa0\x8b" => "\xf0\x9f\x80\x84",
        "\xf3\xbe\xa0\x92" => "\xf0\x9f\x83\x8f",
        "\xf3\xbe\x94\x8b" => "\xf0\x9f\x85\xb0",
        "\xf3\xbe\x94\x8c" => "\xf0\x9f\x85\xb1",
        "\xf3\xbe\x94\x8e" => "\xf0\x9f\x85\xbe",
        "\xf3\xbe\x9f\xb6" => "\xf0\x9f\x85\xbf",
        "\xf3\xbe\x94\x8d" => "\xf0\x9f\x86\x8e",
        "\xf3\xbe\xae\x84" => "\xf0\x9f\x86\x91",
        "\xf3\xbe\xac\xb8" => "\xf0\x9f\x86\x92",
        "\xf3\xbe\xac\xa1" => "\xf0\x9f\x86\x93",
        "\xf3\xbe\xae\x81" => "\xf0\x9f\x86\x94",
        "\xf3\xbe\xac\xb6" => "\xf0\x9f\x86\x95",
        "\xf3\xbe\xac\xa8" => "\xf0\x9f\x86\x96",
        "\xf3\xbe\xac\xa7" => "\xf0\x9f\x86\x97",
        "\xf3\xbe\xad\x8f" => "\xf0\x9f\x86\x98",
        "\xf3\xbe\xac\xb7" => "\xf0\x9f\x86\x99",
        "\xf3\xbe\xac\xb2" => "\xf0\x9f\x86\x9a",
        "\xf3\xbe\xac\xa4" => "\xf0\x9f\x88\x81",
        "\xf3\xbe\xac\xbf" => "\xf0\x9f\x88\x82",
        "\xf3\xbe\xac\xba" => "\xf0\x9f\x88\x9a",
        "\xf3\xbe\xad\x80" => "\xf0\x9f\x88\xaf",
        "\xf3\xbe\xac\xae" => "\xf0\x9f\x88\xb2",
        "\xf3\xbe\xac\xaf" => "\xf0\x9f\x88\xb3",
        "\xf3\xbe\xac\xb0" => "\xf0\x9f\x88\xb4",
        "\xf3\xbe\xac\xb1" => "\xf0\x9f\x88\xb5",
        "\xf3\xbe\xac\xb9" => "\xf0\x9f\x88\xb6",
        "\xf3\xbe\xac\xbb" => "\xf0\x9f\x88\xb7",
        "\xf3\xbe\xac\xbc" => "\xf0\x9f\x88\xb8",
        "\xf3\xbe\xac\xbe" => "\xf0\x9f\x88\xb9",
        "\xf3\xbe\xad\x81" => "\xf0\x9f\x88\xba",
        "\xf3\xbe\xac\xbd" => "\xf0\x9f\x89\x90",
        "\xf3\xbe\xad\x90" => "\xf0\x9f\x89\x91",
        "\xf3\xbe\x80\x85" => "\xf0\x9f\x8c\x80",
        "\xf3\xbe\x80\x86" => "\xf0\x9f\x8c\x81",
        "\xf3\xbe\x80\x87" => "\xf0\x9f\x8c\x82",
        "\xf3\xbe\x80\x88" => "\xf0\x9f\x8c\x83",
        "\xf3\xbe\x80\x89" => "\xf0\x9f\x8c\x84",
        "\xf3\xbe\x80\x8a" => "\xf0\x9f\x8c\x85",
        "\xf3\xbe\x80\x8b" => "\xf0\x9f\x8c\x86",
        "\xf3\xbe\x80\x8c" => "\xf0\x9f\x8c\x87",
        "\xf3\xbe\x80\x8d" => "\xf0\x9f\x8c\x88",
        "\xf3\xbe\x80\x90" => "\xf0\x9f\x8c\x89",
        "\xf3\xbe\x80\xb8" => "\xf0\x9f\x8c\x8a",
        "\xf3\xbe\x80\xba" => "\xf0\x9f\x8c\x8b",
        "\xf3\xbe\x80\xbb" => "\xf0\x9f\x8c\x8c",
        "\xf3\xbe\x80\xb9" => "\xf0\x9f\x8c\x8f",
        "\xf3\xbe\x80\x91" => "\xf0\x9f\x8c\x91",
        "\xf3\xbe\x80\x93" => "\xf0\x9f\x8c\x93",
        "\xf3\xbe\x80\x92" => "\xf0\x9f\x8c\x94",
        "\xf3\xbe\x80\x95" => "\xf0\x9f\x8c\x95",
        "\xf3\xbe\x80\x94" => "\xf0\x9f\x8c\x99",
        "\xf3\xbe\x80\x96" => "\xf0\x9f\x8c\x9b",
        "\xf3\xbe\xad\xa9" => "\xf0\x9f\x8c\x9f",
        "\xf3\xbe\xad\xaa" => "\xf0\x9f\x8c\xa0",
        "\xf3\xbe\x81\x8c" => "\xf0\x9f\x8c\xb0",
        "\xf3\xbe\x80\xbe" => "\xf0\x9f\x8c\xb1",
        "\xf3\xbe\x81\x87" => "\xf0\x9f\x8c\xb4",
        "\xf3\xbe\x81\x88" => "\xf0\x9f\x8c\xb5",
        "\xf3\xbe\x80\xbd" => "\xf0\x9f\x8c\xb7",
        "\xf3\xbe\x81\x80" => "\xf0\x9f\x8c\xb8",
        "\xf3\xbe\x81\x81" => "\xf0\x9f\x8c\xb9",
        "\xf3\xbe\x81\x85" => "\xf0\x9f\x8c\xba",
        "\xf3\xbe\x81\x86" => "\xf0\x9f\x8c\xbb",
        "\xf3\xbe\x81\x8d" => "\xf0\x9f\x8c\xbc",
        "\xf3\xbe\x81\x8a" => "\xf0\x9f\x8c\xbd",
        "\xf3\xbe\x81\x89" => "\xf0\x9f\x8c\xbe",
        "\xf3\xbe\x81\x8e" => "\xf0\x9f\x8c\xbf",
        "\xf3\xbe\x80\xbc" => "\xf0\x9f\x8d\x80",
        "\xf3\xbe\x80\xbf" => "\xf0\x9f\x8d\x81",
        "\xf3\xbe\x81\x82" => "\xf0\x9f\x8d\x82",
        "\xf3\xbe\x81\x83" => "\xf0\x9f\x8d\x83",
        "\xf3\xbe\x81\x8b" => "\xf0\x9f\x8d\x84",
        "\xf3\xbe\x81\x95" => "\xf0\x9f\x8d\x85",
        "\xf3\xbe\x81\x96" => "\xf0\x9f\x8d\x86",
        "\xf3\xbe\x81\x99" => "\xf0\x9f\x8d\x87",
        "\xf3\xbe\x81\x97" => "\xf0\x9f\x8d\x88",
        "\xf3\xbe\x81\x94" => "\xf0\x9f\x8d\x89",
        "\xf3\xbe\x81\x92" => "\xf0\x9f\x8d\x8a",
        "\xf3\xbe\x81\x90" => "\xf0\x9f\x8d\x8c",
        "\xf3\xbe\x81\x98" => "\xf0\x9f\x8d\x8d",
        "\xf3\xbe\x81\x91" => "\xf0\x9f\x8d\x8e",
        "\xf3\xbe\x81\x9b" => "\xf0\x9f\x8d\x8f",
        "\xf3\xbe\x81\x9a" => "\xf0\x9f\x8d\x91",
        "\xf3\xbe\x81\x8f" => "\xf0\x9f\x8d\x92",
        "\xf3\xbe\x81\x93" => "\xf0\x9f\x8d\x93",
        "\xf3\xbe\xa5\xa0" => "\xf0\x9f\x8d\x94",
        "\xf3\xbe\xa5\xb5" => "\xf0\x9f\x8d\x95",
        "\xf3\xbe\xa5\xb2" => "\xf0\x9f\x8d\x96",
        "\xf3\xbe\xa5\xb6" => "\xf0\x9f\x8d\x97",
        "\xf3\xbe\xa5\xa9" => "\xf0\x9f\x8d\x98",
        "\xf3\xbe\xa5\xa1" => "\xf0\x9f\x8d\x99",
        "\xf3\xbe\xa5\xaa" => "\xf0\x9f\x8d\x9a",
        "\xf3\xbe\xa5\xac" => "\xf0\x9f\x8d\x9b",
        "\xf3\xbe\xa5\xa3" => "\xf0\x9f\x8d\x9c",
        "\xf3\xbe\xa5\xab" => "\xf0\x9f\x8d\x9d",
        "\xf3\xbe\xa5\xa4" => "\xf0\x9f\x8d\x9e",
        "\xf3\xbe\xa5\xa7" => "\xf0\x9f\x8d\x9f",
        "\xf3\xbe\xa5\xb4" => "\xf0\x9f\x8d\xa0",
        "\xf3\xbe\xa5\xa8" => "\xf0\x9f\x8d\xa1",
        "\xf3\xbe\xa5\xad" => "\xf0\x9f\x8d\xa2",
        "\xf3\xbe\xa5\xae" => "\xf0\x9f\x8d\xa3",
        "\xf3\xbe\xa5\xbf" => "\xf0\x9f\x8d\xa4",
        "\xf3\xbe\xa5\xb3" => "\xf0\x9f\x8d\xa5",
        "\xf3\xbe\xa5\xa6" => "\xf0\x9f\x8d\xa6",
        "\xf3\xbe\xa5\xb1" => "\xf0\x9f\x8d\xa7",
        "\xf3\xbe\xa5\xb7" => "\xf0\x9f\x8d\xa8",
        "\xf3\xbe\xa5\xb8" => "\xf0\x9f\x8d\xa9",
        "\xf3\xbe\xa5\xb9" => "\xf0\x9f\x8d\xaa",
        "\xf3\xbe\xa5\xba" => "\xf0\x9f\x8d\xab",
        "\xf3\xbe\xa5\xbb" => "\xf0\x9f\x8d\xac",
        "\xf3\xbe\xa5\xbc" => "\xf0\x9f\x8d\xad",
        "\xf3\xbe\xa5\xbd" => "\xf0\x9f\x8d\xae",
        "\xf3\xbe\xa5\xbe" => "\xf0\x9f\x8d\xaf",
        "\xf3\xbe\xa5\xa2" => "\xf0\x9f\x8d\xb0",
        "\xf3\xbe\xa5\xaf" => "\xf0\x9f\x8d\xb1",
        "\xf3\xbe\xa5\xb0" => "\xf0\x9f\x8d\xb2",
        "\xf3\xbe\xa5\xa5" => "\xf0\x9f\x8d\xb3",
        "\xf3\xbe\xa6\x80" => "\xf0\x9f\x8d\xb4",
        "\xf3\xbe\xa6\x84" => "\xf0\x9f\x8d\xb5",
        "\xf3\xbe\xa6\x85" => "\xf0\x9f\x8d\xb6",
        "\xf3\xbe\xa6\x86" => "\xf0\x9f\x8d\xb7",
        "\xf3\xbe\xa6\x82" => "\xf0\x9f\x8d\xb8",
        "\xf3\xbe\xa6\x88" => "\xf0\x9f\x8d\xb9",
        "\xf3\xbe\xa6\x83" => "\xf0\x9f\x8d\xba",
        "\xf3\xbe\xa6\x87" => "\xf0\x9f\x8d\xbb",
        "\xf3\xbe\x94\x8f" => "\xf0\x9f\x8e\x80",
        "\xf3\xbe\x94\x90" => "\xf0\x9f\x8e\x81",
        "\xf3\xbe\x94\x91" => "\xf0\x9f\x8e\x82",
        "\xf3\xbe\x94\x9f" => "\xf0\x9f\x8e\x83",
        "\xf3\xbe\x94\x92" => "\xf0\x9f\x8e\x84",
        "\xf3\xbe\x94\x93" => "\xf0\x9f\x8e\x85",
        "\xf3\xbe\x94\x95" => "\xf0\x9f\x8e\x86",
        "\xf3\xbe\x94\x9d" => "\xf0\x9f\x8e\x87",
        "\xf3\xbe\x94\x96" => "\xf0\x9f\x8e\x88",
        "\xf3\xbe\x94\x97" => "\xf0\x9f\x8e\x89",
        "\xf3\xbe\x94\xa0" => "\xf0\x9f\x8e\x8a",
        "\xf3\xbe\x94\xa1" => "\xf0\x9f\x8e\x8b",
        "\xf3\xbe\x94\x94" => "\xf0\x9f\x8e\x8c",
        "\xf3\xbe\x94\x98" => "\xf0\x9f\x8e\x8d",
        "\xf3\xbe\x94\x99" => "\xf0\x9f\x8e\x8e",
        "\xf3\xbe\x94\x9c" => "\xf0\x9f\x8e\x8f",
        "\xf3\xbe\x94\x9e" => "\xf0\x9f\x8e\x90",
        "\xf3\xbe\x80\x97" => "\xf0\x9f\x8e\x91",
        "\xf3\xbe\x94\x9b" => "\xf0\x9f\x8e\x92",
        "\xf3\xbe\x94\x9a" => "\xf0\x9f\x8e\x93",
        "\xf3\xbe\x9f\xbc" => "\xf0\x9f\x8e\xa0",
        "\xf3\xbe\x9f\xbd" => "\xf0\x9f\x8e\xa1",
        "\xf3\xbe\x9f\xbe" => "\xf0\x9f\x8e\xa2",
        "\xf3\xbe\x9f\xbf" => "\xf0\x9f\x8e\xa3",
        "\xf3\xbe\xa0\x80" => "\xf0\x9f\x8e\xa4",
        "\xf3\xbe\xa0\x81" => "\xf0\x9f\x8e\xa5",
        "\xf3\xbe\xa0\x82" => "\xf0\x9f\x8e\xa6",
        "\xf3\xbe\xa0\x83" => "\xf0\x9f\x8e\xa7",
        "\xf3\xbe\xa0\x84" => "\xf0\x9f\x8e\xa8",
        "\xf3\xbe\xa0\x85" => "\xf0\x9f\x8e\xa9",
        "\xf3\xbe\xa0\x86" => "\xf0\x9f\x8e\xaa",
        "\xf3\xbe\xa0\x87" => "\xf0\x9f\x8e\xab",
        "\xf3\xbe\xa0\x88" => "\xf0\x9f\x8e\xac",
        "\xf3\xbe\xa0\x89" => "\xf0\x9f\x8e\xad",
        "\xf3\xbe\xa0\x8a" => "\xf0\x9f\x8e\xae",
        "\xf3\xbe\xa0\x8c" => "\xf0\x9f\x8e\xaf",
        "\xf3\xbe\xa0\x8d" => "\xf0\x9f\x8e\xb0",
        "\xf3\xbe\xa0\x8e" => "\xf0\x9f\x8e\xb1",
        "\xf3\xbe\xa0\x8f" => "\xf0\x9f\x8e\xb2",
        "\xf3\xbe\xa0\x90" => "\xf0\x9f\x8e\xb3",
        "\xf3\xbe\xa0\x91" => "\xf0\x9f\x8e\xb4",
        "\xf3\xbe\xa0\x93" => "\xf0\x9f\x8e\xb5",
        "\xf3\xbe\xa0\x94" => "\xf0\x9f\x8e\xb6",
        "\xf3\xbe\xa0\x95" => "\xf0\x9f\x8e\xb7",
        "\xf3\xbe\xa0\x96" => "\xf0\x9f\x8e\xb8",
        "\xf3\xbe\xa0\x97" => "\xf0\x9f\x8e\xb9",
        "\xf3\xbe\xa0\x98" => "\xf0\x9f\x8e\xba",
        "\xf3\xbe\xa0\x99" => "\xf0\x9f\x8e\xbb",
        "\xf3\xbe\xa0\x9a" => "\xf0\x9f\x8e\xbc",
        "\xf3\xbe\x9f\x90" => "\xf0\x9f\x8e\xbd",
        "\xf3\xbe\x9f\x93" => "\xf0\x9f\x8e\xbe",
        "\xf3\xbe\x9f\x95" => "\xf0\x9f\x8e\xbf",
        "\xf3\xbe\x9f\x96" => "\xf0\x9f\x8f\x80",
        "\xf3\xbe\x9f\x97" => "\xf0\x9f\x8f\x81",
        "\xf3\xbe\x9f\x98" => "\xf0\x9f\x8f\x82",
        "\xf3\xbe\x9f\x99" => "\xf0\x9f\x8f\x83",
        "\xf3\xbe\x9f\x9a" => "\xf0\x9f\x8f\x84",
        "\xf3\xbe\x9f\x9b" => "\xf0\x9f\x8f\x86",
        "\xf3\xbe\x9f\x9d" => "\xf0\x9f\x8f\x88",
        "\xf3\xbe\x9f\x9e" => "\xf0\x9f\x8f\x8a",
        "\xf3\xbe\x92\xb0" => "\xf0\x9f\x8f\xa0",
        "\xf3\xbe\x92\xb1" => "\xf0\x9f\x8f\xa1",
        "\xf3\xbe\x92\xb2" => "\xf0\x9f\x8f\xa2",
        "\xf3\xbe\x92\xb3" => "\xf0\x9f\x8f\xa3",
        "\xf3\xbe\x92\xb4" => "\xf0\x9f\x8f\xa5",
        "\xf3\xbe\x92\xb5" => "\xf0\x9f\x8f\xa6",
        "\xf3\xbe\x92\xb6" => "\xf0\x9f\x8f\xa7",
        "\xf3\xbe\x92\xb7" => "\xf0\x9f\x8f\xa8",
        "\xf3\xbe\x92\xb8" => "\xf0\x9f\x8f\xa9",
        "\xf3\xbe\x92\xb9" => "\xf0\x9f\x8f\xaa",
        "\xf3\xbe\x92\xba" => "\xf0\x9f\x8f\xab",
        "\xf3\xbe\x92\xbd" => "\xf0\x9f\x8f\xac",
        "\xf3\xbe\x93\x80" => "\xf0\x9f\x8f\xad",
        "\xf3\xbe\x93\x82" => "\xf0\x9f\x8f\xae",
        "\xf3\xbe\x92\xbe" => "\xf0\x9f\x8f\xaf",
        "\xf3\xbe\x92\xbf" => "\xf0\x9f\x8f\xb0",
        "\xf3\xbe\x86\xb9" => "\xf0\x9f\x90\x8c",
        "\xf3\xbe\x87\x93" => "\xf0\x9f\x90\x8d",
        "\xf3\xbe\x9f\x9c" => "\xf0\x9f\x90\x8e",
        "\xf3\xbe\x87\x8f" => "\xf0\x9f\x90\x91",
        "\xf3\xbe\x87\x8e" => "\xf0\x9f\x90\x92",
        "\xf3\xbe\x87\x94" => "\xf0\x9f\x90\x94",
        "\xf3\xbe\x87\x95" => "\xf0\x9f\x90\x97",
        "\xf3\xbe\x87\x8c" => "\xf0\x9f\x90\x98",
        "\xf3\xbe\x87\x85" => "\xf0\x9f\x90\x99",
        "\xf3\xbe\x87\x86" => "\xf0\x9f\x90\x9a",
        "\xf3\xbe\x87\x8b" => "\xf0\x9f\x90\x9b",
        "\xf3\xbe\x87\x9a" => "\xf0\x9f\x90\x9c",
        "\xf3\xbe\x87\xa1" => "\xf0\x9f\x90\x9d",
        "\xf3\xbe\x87\xa2" => "\xf0\x9f\x90\x9e",
        "\xf3\xbe\x86\xbd" => "\xf0\x9f\x90\x9f",
        "\xf3\xbe\x87\x89" => "\xf0\x9f\x90\xa0",
        "\xf3\xbe\x87\x99" => "\xf0\x9f\x90\xa1",
        "\xf3\xbe\x87\x9c" => "\xf0\x9f\x90\xa2",
        "\xf3\xbe\x87\x9d" => "\xf0\x9f\x90\xa3",
        "\xf3\xbe\x86\xba" => "\xf0\x9f\x90\xa4",
        "\xf3\xbe\x86\xbb" => "\xf0\x9f\x90\xa5",
        "\xf3\xbe\x87\x88" => "\xf0\x9f\x90\xa6",
        "\xf3\xbe\x86\xbc" => "\xf0\x9f\x90\xa7",
        "\xf3\xbe\x87\x8d" => "\xf0\x9f\x90\xa8",
        "\xf3\xbe\x87\x98" => "\xf0\x9f\x90\xa9",
        "\xf3\xbe\x87\x96" => "\xf0\x9f\x90\xab",
        "\xf3\xbe\x87\x87" => "\xf0\x9f\x90\xac",
        "\xf3\xbe\x87\x82" => "\xf0\x9f\x90\xad",
        "\xf3\xbe\x87\x91" => "\xf0\x9f\x90\xae",
        "\xf3\xbe\x87\x80" => "\xf0\x9f\x90\xaf",
        "\xf3\xbe\x87\x92" => "\xf0\x9f\x90\xb0",
        "\xf3\xbe\x86\xb8" => "\xf0\x9f\x90\xb1",
        "\xf3\xbe\x87\x9e" => "\xf0\x9f\x90\xb2",
        "\xf3\xbe\x87\x83" => "\xf0\x9f\x90\xb3",
        "\xf3\xbe\x86\xbe" => "\xf0\x9f\x90\xb4",
        "\xf3\xbe\x87\x84" => "\xf0\x9f\x90\xb5",
        "\xf3\xbe\x86\xb7" => "\xf0\x9f\x90\xb6",
        "\xf3\xbe\x86\xbf" => "\xf0\x9f\x90\xb7",
        "\xf3\xbe\x87\x97" => "\xf0\x9f\x90\xb8",
        "\xf3\xbe\x87\x8a" => "\xf0\x9f\x90\xb9",
        "\xf3\xbe\x87\x90" => "\xf0\x9f\x90\xba",
        "\xf3\xbe\x87\x81" => "\xf0\x9f\x90\xbb",
        "\xf3\xbe\x87\x9f" => "\xf0\x9f\x90\xbc",
        "\xf3\xbe\x87\xa0" => "\xf0\x9f\x90\xbd",
        "\xf3\xbe\x87\x9b" => "\xf0\x9f\x90\xbe",
        "\xf3\xbe\x86\x90" => "\xf0\x9f\x91\x80",
        "\xf3\xbe\x86\x91" => "\xf0\x9f\x91\x82",
        "\xf3\xbe\x86\x92" => "\xf0\x9f\x91\x83",
        "\xf3\xbe\x86\x93" => "\xf0\x9f\x91\x84",
        "\xf3\xbe\x86\x94" => "\xf0\x9f\x91\x85",
        "\xf3\xbe\xae\x99" => "\xf0\x9f\x91\x86",
        "\xf3\xbe\xae\x9a" => "\xf0\x9f\x91\x87",
        "\xf3\xbe\xae\x9b" => "\xf0\x9f\x91\x88",
        "\xf3\xbe\xae\x9c" => "\xf0\x9f\x91\x89",
        "\xf3\xbe\xae\x96" => "\xf0\x9f\x91\x8a",
        "\xf3\xbe\xae\x9d" => "\xf0\x9f\x91\x8b",
        "\xf3\xbe\xae\x9f" => "\xf0\x9f\x91\x8c",
        "\xf3\xbe\xae\x97" => "\xf0\x9f\x91\x8d",
        "\xf3\xbe\xae\xa0" => "\xf0\x9f\x91\x8e",
        "\xf3\xbe\xae\x9e" => "\xf0\x9f\x91\x8f",
        "\xf3\xbe\xae\xa1" => "\xf0\x9f\x91\x90",
        "\xf3\xbe\x93\x91" => "\xf0\x9f\x91\x91",
        "\xf3\xbe\x93\x94" => "\xf0\x9f\x91\x92",
        "\xf3\xbe\x93\x8e" => "\xf0\x9f\x91\x93",
        "\xf3\xbe\x93\x93" => "\xf0\x9f\x91\x94",
        "\xf3\xbe\x93\x8f" => "\xf0\x9f\x91\x95",
        "\xf3\xbe\x93\x90" => "\xf0\x9f\x91\x96",
        "\xf3\xbe\x93\x95" => "\xf0\x9f\x91\x97",
        "\xf3\xbe\x93\x99" => "\xf0\x9f\x91\x98",
        "\xf3\xbe\x93\x9a" => "\xf0\x9f\x91\x99",
        "\xf3\xbe\x93\x9b" => "\xf0\x9f\x91\x9a",
        "\xf3\xbe\x93\x9c" => "\xf0\x9f\x91\x9b",
        "\xf3\xbe\x93\xb0" => "\xf0\x9f\x91\x9c",
        "\xf3\xbe\x93\xb1" => "\xf0\x9f\x91\x9d",
        "\xf3\xbe\x93\x8c" => "\xf0\x9f\x91\x9e",
        "\xf3\xbe\x93\x8d" => "\xf0\x9f\x91\x9f",
        "\xf3\xbe\x93\x96" => "\xf0\x9f\x91\xa0",
        "\xf3\xbe\x93\x97" => "\xf0\x9f\x91\xa1",
        "\xf3\xbe\x93\x98" => "\xf0\x9f\x91\xa2",
        "\xf3\xbe\x95\x93" => "\xf0\x9f\x91\xa3",
        "\xf3\xbe\x86\x9a" => "\xf0\x9f\x91\xa4",
        "\xf3\xbe\x86\x9b" => "\xf0\x9f\x91\xa6",
        "\xf3\xbe\x86\x9c" => "\xf0\x9f\x91\xa7",
        "\xf3\xbe\x86\x9d" => "\xf0\x9f\x91\xa8",
        "\xf3\xbe\x86\x9e" => "\xf0\x9f\x91\xa9",
        "\xf3\xbe\x86\x9f" => "\xf0\x9f\x91\xaa",
        "\xf3\xbe\x86\xa0" => "\xf0\x9f\x91\xab",
        "\xf3\xbe\x86\xa1" => "\xf0\x9f\x91\xae",
        "\xf3\xbe\x86\xa2" => "\xf0\x9f\x91\xaf",
        "\xf3\xbe\x86\xa3" => "\xf0\x9f\x91\xb0",
        "\xf3\xbe\x86\xa4" => "\xf0\x9f\x91\xb1",
        "\xf3\xbe\x86\xa5" => "\xf0\x9f\x91\xb2",
        "\xf3\xbe\x86\xa6" => "\xf0\x9f\x91\xb3",
        "\xf3\xbe\x86\xa7" => "\xf0\x9f\x91\xb4",
        "\xf3\xbe\x86\xa8" => "\xf0\x9f\x91\xb5",
        "\xf3\xbe\x86\xa9" => "\xf0\x9f\x91\xb6",
        "\xf3\xbe\x86\xaa" => "\xf0\x9f\x91\xb7",
        "\xf3\xbe\x86\xab" => "\xf0\x9f\x91\xb8",
        "\xf3\xbe\x86\xac" => "\xf0\x9f\x91\xb9",
        "\xf3\xbe\x86\xad" => "\xf0\x9f\x91\xba",
        "\xf3\xbe\x86\xae" => "\xf0\x9f\x91\xbb",
        "\xf3\xbe\x86\xaf" => "\xf0\x9f\x91\xbc",
        "\xf3\xbe\x86\xb0" => "\xf0\x9f\x91\xbd",
        "\xf3\xbe\x86\xb1" => "\xf0\x9f\x91\xbe",
        "\xf3\xbe\x86\xb2" => "\xf0\x9f\x91\xbf",
        "\xf3\xbe\x86\xb3" => "\xf0\x9f\x92\x80",
        "\xf3\xbe\x86\xb4" => "\xf0\x9f\x92\x81",
        "\xf3\xbe\x86\xb5" => "\xf0\x9f\x92\x82",
        "\xf3\xbe\x86\xb6" => "\xf0\x9f\x92\x83",
        "\xf3\xbe\x86\x95" => "\xf0\x9f\x92\x84",
        "\xf3\xbe\x86\x96" => "\xf0\x9f\x92\x85",
        "\xf3\xbe\x86\x97" => "\xf0\x9f\x92\x86",
        "\xf3\xbe\x86\x98" => "\xf0\x9f\x92\x87",
        "\xf3\xbe\x86\x99" => "\xf0\x9f\x92\x88",
        "\xf3\xbe\x94\x89" => "\xf0\x9f\x92\x89",
        "\xf3\xbe\x94\x8a" => "\xf0\x9f\x92\x8a",
        "\xf3\xbe\xa0\xa3" => "\xf0\x9f\x92\x8b",
        "\xf3\xbe\xa0\xa4" => "\xf0\x9f\x92\x8c",
        "\xf3\xbe\xa0\xa5" => "\xf0\x9f\x92\x8d",
        "\xf3\xbe\xa0\xa6" => "\xf0\x9f\x92\x8e",
        "\xf3\xbe\xa0\xa7" => "\xf0\x9f\x92\x8f",
        "\xf3\xbe\xa0\xa8" => "\xf0\x9f\x92\x90",
        "\xf3\xbe\xa0\xa9" => "\xf0\x9f\x92\x91",
        "\xf3\xbe\xa0\xaa" => "\xf0\x9f\x92\x92",
        "\xf3\xbe\xac\x8d" => "\xf0\x9f\x92\x93",
        "\xf3\xbe\xac\x8e" => "\xf0\x9f\x92\x94",
        "\xf3\xbe\xac\x8f" => "\xf0\x9f\x92\x95",
        "\xf3\xbe\xac\x90" => "\xf0\x9f\x92\x96",
        "\xf3\xbe\xac\x91" => "\xf0\x9f\x92\x97",
        "\xf3\xbe\xac\x92" => "\xf0\x9f\x92\x98",
        "\xf3\xbe\xac\x93" => "\xf0\x9f\x92\x99",
        "\xf3\xbe\xac\x94" => "\xf0\x9f\x92\x9a",
        "\xf3\xbe\xac\x95" => "\xf0\x9f\x92\x9b",
        "\xf3\xbe\xac\x96" => "\xf0\x9f\x92\x9c",
        "\xf3\xbe\xac\x97" => "\xf0\x9f\x92\x9d",
        "\xf3\xbe\xac\x98" => "\xf0\x9f\x92\x9e",
        "\xf3\xbe\xac\x99" => "\xf0\x9f\x92\x9f",
        "\xf3\xbe\xad\x95" => "\xf0\x9f\x92\xa0",
        "\xf3\xbe\xad\x96" => "\xf0\x9f\x92\xa1",
        "\xf3\xbe\xad\x97" => "\xf0\x9f\x92\xa2",
        "\xf3\xbe\xad\x98" => "\xf0\x9f\x92\xa3",
        "\xf3\xbe\xad\x99" => "\xf0\x9f\x92\xa4",
        "\xf3\xbe\xad\x9a" => "\xf0\x9f\x92\xa5",
        "\xf3\xbe\xad\x9b" => "\xf0\x9f\x92\xa6",
        "\xf3\xbe\xad\x9c" => "\xf0\x9f\x92\xa7",
        "\xf3\xbe\xad\x9d" => "\xf0\x9f\x92\xa8",
        "\xf3\xbe\x93\xb4" => "\xf0\x9f\x92\xa9",
        "\xf3\xbe\xad\x9e" => "\xf0\x9f\x92\xaa",
        "\xf3\xbe\xad\x9f" => "\xf0\x9f\x92\xab",
        "\xf3\xbe\x94\xb2" => "\xf0\x9f\x92\xac",
        "\xf3\xbe\xad\xba" => "\xf0\x9f\x92\xae",
        "\xf3\xbe\xad\xbb" => "\xf0\x9f\x92\xaf",
        "\xf3\xbe\x93\x9d" => "\xf0\x9f\x92\xb0",
        "\xf3\xbe\x93\x9e" => "\xf0\x9f\x92\xb1",
        "\xf3\xbe\x93\xa0" => "\xf0\x9f\x92\xb2",
        "\xf3\xbe\x93\xa1" => "\xf0\x9f\x92\xb3",
        "\xf3\xbe\x93\xa2" => "\xf0\x9f\x92\xb4",
        "\xf3\xbe\x93\xa3" => "\xf0\x9f\x92\xb5",
        "\xf3\xbe\x93\xa4" => "\xf0\x9f\x92\xb8",
        "\xf3\xbe\x93\x9f" => "\xf0\x9f\x92\xb9",
        "\xf3\xbe\x94\xb7" => "\xf0\x9f\x92\xba",
        "\xf3\xbe\x94\xb8" => "\xf0\x9f\x92\xbb",
        "\xf3\xbe\x94\xbb" => "\xf0\x9f\x92\xbc",
        "\xf3\xbe\x94\xbc" => "\xf0\x9f\x92\xbd",
        "\xf3\xbe\x94\xbd" => "\xf0\x9f\x92\xbe",
        "\xf3\xbe\xa0\x9d" => "\xf0\x9f\x92\xbf",
        "\xf3\xbe\xa0\x9e" => "\xf0\x9f\x93\x80",
        "\xf3\xbe\x95\x83" => "\xf0\x9f\x93\x81",
        "\xf3\xbe\x95\x84" => "\xf0\x9f\x93\x82",
        "\xf3\xbe\x95\x80" => "\xf0\x9f\x93\x83",
        "\xf3\xbe\x95\x81" => "\xf0\x9f\x93\x84",
        "\xf3\xbe\x95\x82" => "\xf0\x9f\x93\x85",
        "\xf3\xbe\x95\x89" => "\xf0\x9f\x93\x86",
        "\xf3\xbe\x95\x8d" => "\xf0\x9f\x93\x87",
        "\xf3\xbe\x95\x8b" => "\xf0\x9f\x93\x88",
        "\xf3\xbe\x95\x8c" => "\xf0\x9f\x93\x89",
        "\xf3\xbe\x95\x8a" => "\xf0\x9f\x93\x8a",
        "\xf3\xbe\x95\x88" => "\xf0\x9f\x93\x8b",
        "\xf3\xbe\x95\x8e" => "\xf0\x9f\x93\x8c",
        "\xf3\xbe\x94\xbf" => "\xf0\x9f\x93\x8d",
        "\xf3\xbe\x94\xba" => "\xf0\x9f\x93\x8e",
        "\xf3\xbe\x95\x90" => "\xf0\x9f\x93\x8f",
        "\xf3\xbe\x95\x91" => "\xf0\x9f\x93\x90",
        "\xf3\xbe\x95\x92" => "\xf0\x9f\x93\x91",
        "\xf3\xbe\x95\x8f" => "\xf0\x9f\x93\x92",
        "\xf3\xbe\x95\x85" => "\xf0\x9f\x93\x93",
        "\xf3\xbe\x95\x87" => "\xf0\x9f\x93\x94",
        "\xf3\xbe\x94\x82" => "\xf0\x9f\x93\x95",
        "\xf3\xbe\x95\x86" => "\xf0\x9f\x93\x96",
        "\xf3\xbe\x93\xbf" => "\xf0\x9f\x93\x97",
        "\xf3\xbe\x94\x80" => "\xf0\x9f\x93\x98",
        "\xf3\xbe\x94\x81" => "\xf0\x9f\x93\x99",
        "\xf3\xbe\x94\x83" => "\xf0\x9f\x93\x9a",
        "\xf3\xbe\x94\x84" => "\xf0\x9f\x93\x9b",
        "\xf3\xbe\x93\xbd" => "\xf0\x9f\x93\x9c",
        "\xf3\xbe\x94\xa7" => "\xf0\x9f\x93\x9d",
        "\xf3\xbe\x94\xa4" => "\xf0\x9f\x93\x9e",
        "\xf3\xbe\x94\xa2" => "\xf0\x9f\x93\x9f",
        "\xf3\xbe\x94\xa8" => "\xf0\x9f\x93\xa0",
        "\xf3\xbe\x94\xb1" => "\xf0\x9f\x93\xa1",
        "\xf3\xbe\x94\xaf" => "\xf0\x9f\x93\xa2",
        "\xf3\xbe\x94\xb0" => "\xf0\x9f\x93\xa3",
        "\xf3\xbe\x94\xb3" => "\xf0\x9f\x93\xa4",
        "\xf3\xbe\x94\xb4" => "\xf0\x9f\x93\xa5",
        "\xf3\xbe\x94\xb5" => "\xf0\x9f\x93\xa6",
        "\xf3\xbe\xae\x92" => "\xf0\x9f\x93\xa7",
        "\xf3\xbe\x94\xaa" => "\xf0\x9f\x93\xa8",
        "\xf3\xbe\x94\xab" => "\xf0\x9f\x93\xa9",
        "\xf3\xbe\x94\xac" => "\xf0\x9f\x93\xaa",
        "\xf3\xbe\x94\xad" => "\xf0\x9f\x93\xab",
        "\xf3\xbe\x94\xae" => "\xf0\x9f\x93\xae",
        "\xf3\xbe\xa0\xa2" => "\xf0\x9f\x93\xb0",
        "\xf3\xbe\x94\xa5" => "\xf0\x9f\x93\xb1",
        "\xf3\xbe\x94\xa6" => "\xf0\x9f\x93\xb2",
        "\xf3\xbe\xa0\xb9" => "\xf0\x9f\x93\xb3",
        "\xf3\xbe\xa0\xba" => "\xf0\x9f\x93\xb4",
        "\xf3\xbe\xa0\xb8" => "\xf0\x9f\x93\xb6",
        "\xf3\xbe\x93\xaf" => "\xf0\x9f\x93\xb7",
        "\xf3\xbe\x93\xb9" => "\xf0\x9f\x93\xb9",
        "\xf3\xbe\xa0\x9c" => "\xf0\x9f\x93\xba",
        "\xf3\xbe\xa0\x9f" => "\xf0\x9f\x93\xbb",
        "\xf3\xbe\xa0\xa0" => "\xf0\x9f\x93\xbc",
        "\xf3\xbe\xae\x91" => "\xf0\x9f\x94\x83",
        "\xf3\xbe\xa0\xa1" => "\xf0\x9f\x94\x8a",
        "\xf3\xbe\x93\xbc" => "\xf0\x9f\x94\x8b",
        "\xf3\xbe\x93\xbe" => "\xf0\x9f\x94\x8c",
        "\xf3\xbe\xae\x85" => "\xf0\x9f\x94\x8d",
        "\xf3\xbe\xae\x8d" => "\xf0\x9f\x94\x8e",
        "\xf3\xbe\xae\x90" => "\xf0\x9f\x94\x8f",
        "\xf3\xbe\xae\x8a" => "\xf0\x9f\x94\x90",
        "\xf3\xbe\xae\x82" => "\xf0\x9f\x94\x91",
        "\xf3\xbe\xae\x86" => "\xf0\x9f\x94\x92",
        "\xf3\xbe\xae\x87" => "\xf0\x9f\x94\x93",
        "\xf3\xbe\x93\xb2" => "\xf0\x9f\x94\x94",
        "\xf3\xbe\xae\x8f" => "\xf0\x9f\x94\x96",
        "\xf3\xbe\xad\x8b" => "\xf0\x9f\x94\x97",
        "\xf3\xbe\xae\x8c" => "\xf0\x9f\x94\x98",
        "\xf3\xbe\xae\x8e" => "\xf0\x9f\x94\x99",
        "\xf3\xbe\x80\x9a" => "\xf0\x9f\x94\x9a",
        "\xf3\xbe\x80\x99" => "\xf0\x9f\x94\x9b",
        "\xf3\xbe\x80\x98" => "\xf0\x9f\x94\x9c",
        "\xf3\xbe\xad\x82" => "\xf0\x9f\x94\x9d",
        "\xf3\xbe\xac\xa5" => "\xf0\x9f\x94\x9e",
        "\xf3\xbe\xa0\xbb" => "\xf0\x9f\x94\x9f",
        "\xf3\xbe\xad\xbc" => "\xf0\x9f\x94\xa0",
        "\xf3\xbe\xad\xbd" => "\xf0\x9f\x94\xa1",
        "\xf3\xbe\xad\xbe" => "\xf0\x9f\x94\xa2",
        "\xf3\xbe\xad\xbf" => "\xf0\x9f\x94\xa3",
        "\xf3\xbe\xae\x80" => "\xf0\x9f\x94\xa4",
        "\xf3\xbe\x93\xb6" => "\xf0\x9f\x94\xa5",
        "\xf3\xbe\x93\xbb" => "\xf0\x9f\x94\xa6",
        "\xf3\xbe\x93\x89" => "\xf0\x9f\x94\xa7",
        "\xf3\xbe\x93\x8a" => "\xf0\x9f\x94\xa8",
        "\xf3\xbe\x93\x8b" => "\xf0\x9f\x94\xa9",
        "\xf3\xbe\x93\xba" => "\xf0\x9f\x94\xaa",
        "\xf3\xbe\x93\xb5" => "\xf0\x9f\x94\xab",
        "\xf3\xbe\x93\xb7" => "\xf0\x9f\x94\xae",
        "\xf3\xbe\x93\xb8" => "\xf0\x9f\x94\xaf",
        "\xf3\xbe\x81\x84" => "\xf0\x9f\x94\xb0",
        "\xf3\xbe\x93\x92" => "\xf0\x9f\x94\xb1",
        "\xf3\xbe\xad\xa4" => "\xf0\x9f\x94\xb5",
        "\xf3\xbe\xad\xa7" => "\xf0\x9f\x94\xb3",
        "\xf3\xbe\xad\xa3" => "\xf0\x9f\x94\xb4",
        "\xf3\xbe\xad\xb3" => "\xf0\x9f\x94\xb6",
        "\xf3\xbe\xad\xb4" => "\xf0\x9f\x94\xb7",
        "\xf3\xbe\xad\xb5" => "\xf0\x9f\x94\xb8",
        "\xf3\xbe\xad\xb6" => "\xf0\x9f\x94\xb9",
        "\xf3\xbe\xad\xb8" => "\xf0\x9f\x94\xba",
        "\xf3\xbe\xad\xb9" => "\xf0\x9f\x94\xbb",
        "\xf3\xbe\xac\x81" => "\xf0\x9f\x94\xbc",
        "\xf3\xbe\xac\x80" => "\xf0\x9f\x94\xbd",
        "\xf3\xbe\x80\x9e" => "\xf0\x9f\x95\x90",
        "\xf3\xbe\x80\x9f" => "\xf0\x9f\x95\x91",
        "\xf3\xbe\x80\xa0" => "\xf0\x9f\x95\x92",
        "\xf3\xbe\x80\xa1" => "\xf0\x9f\x95\x93",
        "\xf3\xbe\x80\xa2" => "\xf0\x9f\x95\x94",
        "\xf3\xbe\x80\xa3" => "\xf0\x9f\x95\x95",
        "\xf3\xbe\x80\xa4" => "\xf0\x9f\x95\x96",
        "\xf3\xbe\x80\xa5" => "\xf0\x9f\x95\x97",
        "\xf3\xbe\x80\xa6" => "\xf0\x9f\x95\x98",
        "\xf3\xbe\x80\xa7" => "\xf0\x9f\x95\x99",
        "\xf3\xbe\x80\xa8" => "\xf0\x9f\x95\x9a",
        "\xf3\xbe\x80\xa9" => "\xf0\x9f\x95\x9b",
        "\xf3\xbe\x93\x83" => "\xf0\x9f\x97\xbb",
        "\xf3\xbe\x93\x84" => "\xf0\x9f\x97\xbc",
        "\xf3\xbe\x93\x86" => "\xf0\x9f\x97\xbd",
        "\xf3\xbe\x93\x87" => "\xf0\x9f\x97\xbe",
        "\xf3\xbe\x93\x88" => "\xf0\x9f\x97\xbf",
        "\xf3\xbe\x8c\xb3" => "\xf0\x9f\x98\x81",
        "\xf3\xbe\x8c\xb4" => "\xf0\x9f\x98\x82",
        "\xf3\xbe\x8c\xb0" => "\xf0\x9f\x98\x83",
        "\xf3\xbe\x8c\xb8" => "\xf0\x9f\x98\x84",
        "\xf3\xbe\x8c\xb1" => "\xf0\x9f\x98\x85",
        "\xf3\xbe\x8c\xb2" => "\xf0\x9f\x98\x86",
        "\xf3\xbe\x8d\x87" => "\xf0\x9f\x98\x89",
        "\xf3\xbe\x8c\xb5" => "\xf0\x9f\x98\x8a",
        "\xf3\xbe\x8c\xab" => "\xf0\x9f\x98\x8b",
        "\xf3\xbe\x8c\xbe" => "\xf0\x9f\x98\x8c",
        "\xf3\xbe\x8c\xa7" => "\xf0\x9f\x98\x8d",
        "\xf3\xbe\x8d\x83" => "\xf0\x9f\x98\x8f",
        "\xf3\xbe\x8c\xa6" => "\xf0\x9f\x98\x92",
        "\xf3\xbe\x8d\x84" => "\xf0\x9f\x98\x93",
        "\xf3\xbe\x8d\x80" => "\xf0\x9f\x98\x94",
        "\xf3\xbe\x8c\xbf" => "\xf0\x9f\x98\x96",
        "\xf3\xbe\x8c\xac" => "\xf0\x9f\x98\x98",
        "\xf3\xbe\x8c\xad" => "\xf0\x9f\x98\x9a",
        "\xf3\xbe\x8c\xa9" => "\xf0\x9f\x98\x9c",
        "\xf3\xbe\x8c\xaa" => "\xf0\x9f\x98\x9d",
        "\xf3\xbe\x8c\xa3" => "\xf0\x9f\x98\x9e",
        "\xf3\xbe\x8c\xa0" => "\xf0\x9f\x98\xa0",
        "\xf3\xbe\x8c\xbd" => "\xf0\x9f\x98\xa1",
        "\xf3\xbe\x8c\xb9" => "\xf0\x9f\x98\xa2",
        "\xf3\xbe\x8c\xbc" => "\xf0\x9f\x98\xa3",
        "\xf3\xbe\x8c\xa8" => "\xf0\x9f\x98\xa4",
        "\xf3\xbe\x8d\x85" => "\xf0\x9f\x98\xa5",
        "\xf3\xbe\x8c\xbb" => "\xf0\x9f\x98\xa8",
        "\xf3\xbe\x8c\xa1" => "\xf0\x9f\x98\xa9",
        "\xf3\xbe\x8d\x82" => "\xf0\x9f\x98\xaa",
        "\xf3\xbe\x8d\x86" => "\xf0\x9f\x98\xab",
        "\xf3\xbe\x8c\xba" => "\xf0\x9f\x98\xad",
        "\xf3\xbe\x8c\xa5" => "\xf0\x9f\x98\xb0",
        "\xf3\xbe\x8d\x81" => "\xf0\x9f\x98\xb1",
        "\xf3\xbe\x8c\xa2" => "\xf0\x9f\x98\xb2",
        "\xf3\xbe\x8c\xaf" => "\xf0\x9f\x98\xb3",
        "\xf3\xbe\x8c\xa4" => "\xf0\x9f\x98\xb5",
        "\xf3\xbe\x8c\xae" => "\xf0\x9f\x98\xb7",
        "\xf3\xbe\x8d\x89" => "\xf0\x9f\x98\xb8",
        "\xf3\xbe\x8d\x8a" => "\xf0\x9f\x98\xb9",
        "\xf3\xbe\x8d\x88" => "\xf0\x9f\x98\xba",
        "\xf3\xbe\x8d\x8c" => "\xf0\x9f\x98\xbb",
        "\xf3\xbe\x8d\x8f" => "\xf0\x9f\x98\xbc",
        "\xf3\xbe\x8d\x8b" => "\xf0\x9f\x98\xbd",
        "\xf3\xbe\x8d\x8e" => "\xf0\x9f\x98\xbe",
        "\xf3\xbe\x8d\x8d" => "\xf0\x9f\x98\xbf",
        "\xf3\xbe\x8d\x90" => "\xf0\x9f\x99\x80",
        "\xf3\xbe\x8d\x91" => "\xf0\x9f\x99\x85",
        "\xf3\xbe\x8d\x92" => "\xf0\x9f\x99\x86",
        "\xf3\xbe\x8d\x93" => "\xf0\x9f\x99\x87",
        "\xf3\xbe\x8d\x94" => "\xf0\x9f\x99\x88",
        "\xf3\xbe\x8d\x96" => "\xf0\x9f\x99\x89",
        "\xf3\xbe\x8d\x95" => "\xf0\x9f\x99\x8a",
        "\xf3\xbe\x8d\x97" => "\xf0\x9f\x99\x8b",
        "\xf3\xbe\x8d\x98" => "\xf0\x9f\x99\x8c",
        "\xf3\xbe\x8d\x99" => "\xf0\x9f\x99\x8d",
        "\xf3\xbe\x8d\x9a" => "\xf0\x9f\x99\x8e",
        "\xf3\xbe\x8d\x9b" => "\xf0\x9f\x99\x8f",
        "\xf3\xbe\x9f\xad" => "\xf0\x9f\x9a\x80",
        "\xf3\xbe\x9f\x9f" => "\xf0\x9f\x9a\x83",
        "\xf3\xbe\x9f\xa2" => "\xf0\x9f\x9a\x84",
        "\xf3\xbe\x9f\xa3" => "\xf0\x9f\x9a\x85",
        "\xf3\xbe\x9f\xa0" => "\xf0\x9f\x9a\x87",
        "\xf3\xbe\x9f\xac" => "\xf0\x9f\x9a\x89",
        "\xf3\xbe\x9f\xa6" => "\xf0\x9f\x9a\x8c",
        "\xf3\xbe\x9f\xa7" => "\xf0\x9f\x9a\x8f",
        "\xf3\xbe\x9f\xb3" => "\xf0\x9f\x9a\x91",
        "\xf3\xbe\x9f\xb2" => "\xf0\x9f\x9a\x92",
        "\xf3\xbe\x9f\xb4" => "\xf0\x9f\x9a\x93",
        "\xf3\xbe\x9f\xaf" => "\xf0\x9f\x9a\x95",
        "\xf3\xbe\x9f\xa4" => "\xf0\x9f\x9a\x97",
        "\xf3\xbe\x9f\xa5" => "\xf0\x9f\x9a\x99",
        "\xf3\xbe\x9f\xb1" => "\xf0\x9f\x9a\x9a",
        "\xf3\xbe\x9f\xa8" => "\xf0\x9f\x9a\xa2",
        "\xf3\xbe\x9f\xae" => "\xf0\x9f\x9a\xa4",
        "\xf3\xbe\x9f\xb7" => "\xf0\x9f\x9a\xa5",
        "\xf3\xbe\x9f\xb8" => "\xf0\x9f\x9a\xa7",
        "\xf3\xbe\x9f\xb9" => "\xf0\x9f\x9a\xa8",
        "\xf3\xbe\xac\xa2" => "\xf0\x9f\x9a\xa9",
        "\xf3\xbe\x93\xb3" => "\xf0\x9f\x9a\xaa",
        "\xf3\xbe\xad\x88" => "\xf0\x9f\x9a\xab",
        "\xf3\xbe\xac\x9e" => "\xf0\x9f\x9a\xac",
        "\xf3\xbe\xac\x9f" => "\xf0\x9f\x9a\xad",
        "\xf3\xbe\x9f\xab" => "\xf0\x9f\x9a\xb2",
        "\xf3\xbe\x9f\xb0" => "\xf0\x9f\x9a\xb6",
        "\xf3\xbe\xac\xb3" => "\xf0\x9f\x9a\xb9",
        "\xf3\xbe\xac\xb4" => "\xf0\x9f\x9a\xba",
        "\xf3\xbe\x94\x86" => "\xf0\x9f\x9a\xbb",
        "\xf3\xbe\xac\xb5" => "\xf0\x9f\x9a\xbc",
        "\xf3\xbe\x94\x87" => "\xf0\x9f\x9a\xbd",
        "\xf3\xbe\x94\x88" => "\xf0\x9f\x9a\xbe",
        "\xf3\xbe\x94\x85" => "\xf0\x9f\x9b\x80",
        "\xf3\xbe\xa0\xac" => "#\xe2\x83\xa3",
        "\xf3\xbe\xa0\xb7" => "0\xe2\x83\xa3",
        "\xf3\xbe\xa0\xae" => "1\xe2\x83\xa3",
        "\xf3\xbe\xa0\xaf" => "2\xe2\x83\xa3",
        "\xf3\xbe\xa0\xb0" => "3\xe2\x83\xa3",
        "\xf3\xbe\xa0\xb1" => "4\xe2\x83\xa3",
        "\xf3\xbe\xa0\xb2" => "5\xe2\x83\xa3",
        "\xf3\xbe\xa0\xb3" => "6\xe2\x83\xa3",
        "\xf3\xbe\xa0\xb4" => "7\xe2\x83\xa3",
        "\xf3\xbe\xa0\xb5" => "8\xe2\x83\xa3",
        "\xf3\xbe\xa0\xb6" => "9\xe2\x83\xa3",
        "\xf3\xbe\x93\xad" => "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb3",
        "\xf3\xbe\x93\xa8" => "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaa",
        "\xf3\xbe\x93\xab" => "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb8",
        "\xf3\xbe\x93\xa7" => "\xf0\x9f\x87\xab\xf0\x9f\x87\xb7",
        "\xf3\xbe\x93\xaa" => "\xf0\x9f\x87\xac\xf0\x9f\x87\xa7",
        "\xf3\xbe\x93\xa9" => "\xf0\x9f\x87\xae\xf0\x9f\x87\xb9",
        "\xf3\xbe\x93\xa5" => "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb5",
        "\xf3\xbe\x93\xae" => "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb7",
        "\xf3\xbe\x93\xac" => "\xf0\x9f\x87\xb7\xf0\x9f\x87\xba",
        "\xf3\xbe\x93\xa6" => "\xf0\x9f\x87\xba\xf0\x9f\x87\xb8",
    ),
    'unified_to_html' => array(
        "\xc2\xa9" => "a9",
        "\xc2\xae" => "ae",
        "\xe2\x80\xbc" => "203c",
        "\xe2\x81\x89" => "2049",
        "\xe2\x84\xa2" => "2122",
        "\xe2\x84\xb9" => "2139",
        "\xe2\x86\x94" => "2194",
        "\xe2\x86\x95" => "2195",
        "\xe2\x86\x96" => "2196",
        "\xe2\x86\x97" => "2197",
        "\xe2\x86\x98" => "2198",
        "\xe2\x86\x99" => "2199",
        "\xe2\x86\xa9" => "21a9",
        "\xe2\x86\xaa" => "21aa",
        "\xe2\x8c\x9a" => "231a",
        "\xe2\x8c\x9b" => "231b",
        "\xe2\x8c\xa8" => "2328",
        "\xe2\x8f\xa9" => "23e9",
        "\xe2\x8f\xaa" => "23ea",
        "\xe2\x8f\xab" => "23eb",
        "\xe2\x8f\xac" => "23ec",
        "\xe2\x8f\xad" => "23ed",
        "\xe2\x8f\xae" => "23ee",
        "\xe2\x8f\xaf" => "23ef",
        "\xe2\x8f\xb0" => "23f0",
        "\xe2\x8f\xb1" => "23f1",
        "\xe2\x8f\xb2" => "23f2",
        "\xe2\x8f\xb3" => "23f3",
        "\xe2\x8f\xb8" => "23f8",
        "\xe2\x8f\xb9" => "23f9",
        "\xe2\x8f\xba" => "23fa",
        "\xe2\x93\x82" => "24c2",
        "\xe2\x96\xaa" => "25aa",
        "\xe2\x96\xab" => "25ab",
        "\xe2\x96\xb6" => "25b6",
        "\xe2\x97\x80" => "25c0",
        "\xe2\x97\xbb" => "25fb",
        "\xe2\x97\xbc" => "25fc",
        "\xe2\x97\xbd" => "25fd",
        "\xe2\x97\xbe" => "25fe",
        "\xe2\x98\x80" => "2600",
        "\xe2\x98\x81" => "2601",
        "\xe2\x98\x82" => "2602",
        "\xe2\x98\x83" => "2603",
        "\xe2\x98\x84" => "2604",
        "\xe2\x98\x8e" => "260e",
        "\xe2\x98\x91" => "2611",
        "\xe2\x98\x94" => "2614",
        "\xe2\x98\x95" => "2615",
        "\xe2\x98\x98" => "2618",
        "\xe2\x98\x9d" => "261d",
        "\xe2\x98\xa0" => "2620",
        "\xe2\x98\xa2" => "2622",
        "\xe2\x98\xa3" => "2623",
        "\xe2\x98\xa6" => "2626",
        "\xe2\x98\xaa" => "262a",
        "\xe2\x98\xae" => "262e",
        "\xe2\x98\xaf" => "262f",
        "\xe2\x98\xb8" => "2638",
        "\xe2\x98\xb9" => "2639",
        "\xe2\x98\xba" => "263a",
        "\xe2\x99\x88" => "2648",
        "\xe2\x99\x89" => "2649",
        "\xe2\x99\x8a" => "264a",
        "\xe2\x99\x8b" => "264b",
        "\xe2\x99\x8c" => "264c",
        "\xe2\x99\x8d" => "264d",
        "\xe2\x99\x8e" => "264e",
        "\xe2\x99\x8f" => "264f",
        "\xe2\x99\x90" => "2650",
        "\xe2\x99\x91" => "2651",
        "\xe2\x99\x92" => "2652",
        "\xe2\x99\x93" => "2653",
        "\xe2\x99\xa0" => "2660",
        "\xe2\x99\xa3" => "2663",
        "\xe2\x99\xa5" => "2665",
        "\xe2\x99\xa6" => "2666",
        "\xe2\x99\xa8" => "2668",
        "\xe2\x99\xbb" => "267b",
        "\xe2\x99\xbf" => "267f",
        "\xe2\x9a\x92" => "2692",
        "\xe2\x9a\x93" => "2693",
        "\xe2\x9a\x94" => "2694",
        "\xe2\x9a\x96" => "2696",
        "\xe2\x9a\x97" => "2697",
        "\xe2\x9a\x99" => "2699",
        "\xe2\x9a\x9b" => "269b",
        "\xe2\x9a\x9c" => "269c",
        "\xe2\x9a\xa0" => "26a0",
        "\xe2\x9a\xa1" => "26a1",
        "\xe2\x9a\xaa" => "26aa",
        "\xe2\x9a\xab" => "26ab",
        "\xe2\x9a\xb0" => "26b0",
        "\xe2\x9a\xb1" => "26b1",
        "\xe2\x9a\xbd" => "26bd",
        "\xe2\x9a\xbe" => "26be",
        "\xe2\x9b\x84" => "26c4",
        "\xe2\x9b\x85" => "26c5",
        "\xe2\x9b\x88" => "26c8",
        "\xe2\x9b\x8e" => "26ce",
        "\xe2\x9b\x8f" => "26cf",
        "\xe2\x9b\x91" => "26d1",
        "\xe2\x9b\x93" => "26d3",
        "\xe2\x9b\x94" => "26d4",
        "\xe2\x9b\xa9" => "26e9",
        "\xe2\x9b\xaa" => "26ea",
        "\xe2\x9b\xb0" => "26f0",
        "\xe2\x9b\xb1" => "26f1",
        "\xe2\x9b\xb2" => "26f2",
        "\xe2\x9b\xb3" => "26f3",
        "\xe2\x9b\xb4" => "26f4",
        "\xe2\x9b\xb5" => "26f5",
        "\xe2\x9b\xb7" => "26f7",
        "\xe2\x9b\xb8" => "26f8",
        "\xe2\x9b\xb9" => "26f9",
        "\xe2\x9b\xba" => "26fa",
        "\xe2\x9b\xbd" => "26fd",
        "\xe2\x9c\x82" => "2702",
        "\xe2\x9c\x85" => "2705",
        "\xe2\x9c\x88" => "2708",
        "\xe2\x9c\x89" => "2709",
        "\xe2\x9c\x8a" => "270a",
        "\xe2\x9c\x8b" => "270b",
        "\xe2\x9c\x8c" => "270c",
        "\xe2\x9c\x8d" => "270d",
        "\xe2\x9c\x8f" => "270f",
        "\xe2\x9c\x92" => "2712",
        "\xe2\x9c\x94" => "2714",
        "\xe2\x9c\x96" => "2716",
        "\xe2\x9c\x9d" => "271d",
        "\xe2\x9c\xa1" => "2721",
        "\xe2\x9c\xa8" => "2728",
        "\xe2\x9c\xb3" => "2733",
        "\xe2\x9c\xb4" => "2734",
        "\xe2\x9d\x84" => "2744",
        "\xe2\x9d\x87" => "2747",
        "\xe2\x9d\x8c" => "274c",
        "\xe2\x9d\x8e" => "274e",
        "\xe2\x9d\x93" => "2753",
        "\xe2\x9d\x94" => "2754",
        "\xe2\x9d\x95" => "2755",
        "\xe2\x9d\x97" => "2757",
        "\xe2\x9d\xa3" => "2763",
        "\xe2\x9d\xa4" => "2764",
        "\xe2\x9e\x95" => "2795",
        "\xe2\x9e\x96" => "2796",
        "\xe2\x9e\x97" => "2797",
        "\xe2\x9e\xa1" => "27a1",
        "\xe2\x9e\xb0" => "27b0",
        "\xe2\x9e\xbf" => "27bf",
        "\xe2\xa4\xb4" => "2934",
        "\xe2\xa4\xb5" => "2935",
        "\xe2\xac\x85" => "2b05",
        "\xe2\xac\x86" => "2b06",
        "\xe2\xac\x87" => "2b07",
        "\xe2\xac\x9b" => "2b1b",
        "\xe2\xac\x9c" => "2b1c",
        "\xe2\xad\x90" => "2b50",
        "\xe2\xad\x95" => "2b55",
        "\xe3\x80\xb0" => "3030",
        "\xe3\x80\xbd" => "303d",
        "\xe3\x8a\x97" => "3297",
        "\xe3\x8a\x99" => "3299",
        "\xf0\x9f\x80\x84" => "1f004",
        "\xf0\x9f\x83\x8f" => "1f0cf",
        "\xf0\x9f\x85\xb0" => "1f170",
        "\xf0\x9f\x85\xb1" => "1f171",
        "\xf0\x9f\x85\xbe" => "1f17e",
        "\xf0\x9f\x85\xbf" => "1f17f",
        "\xf0\x9f\x86\x8e" => "1f18e",
        "\xf0\x9f\x86\x91" => "1f191",
        "\xf0\x9f\x86\x92" => "1f192",
        "\xf0\x9f\x86\x93" => "1f193",
        "\xf0\x9f\x86\x94" => "1f194",
        "\xf0\x9f\x86\x95" => "1f195",
        "\xf0\x9f\x86\x96" => "1f196",
        "\xf0\x9f\x86\x97" => "1f197",
        "\xf0\x9f\x86\x98" => "1f198",
        "\xf0\x9f\x86\x99" => "1f199",
        "\xf0\x9f\x86\x9a" => "1f19a",
        "\xf0\x9f\x88\x81" => "1f201",
        "\xf0\x9f\x88\x82" => "1f202",
        "\xf0\x9f\x88\x9a" => "1f21a",
        "\xf0\x9f\x88\xaf" => "1f22f",
        "\xf0\x9f\x88\xb2" => "1f232",
        "\xf0\x9f\x88\xb3" => "1f233",
        "\xf0\x9f\x88\xb4" => "1f234",
        "\xf0\x9f\x88\xb5" => "1f235",
        "\xf0\x9f\x88\xb6" => "1f236",
        "\xf0\x9f\x88\xb7" => "1f237",
        "\xf0\x9f\x88\xb8" => "1f238",
        "\xf0\x9f\x88\xb9" => "1f239",
        "\xf0\x9f\x88\xba" => "1f23a",
        "\xf0\x9f\x89\x90" => "1f250",
        "\xf0\x9f\x89\x91" => "1f251",
        "\xf0\x9f\x8c\x80" => "1f300",
        "\xf0\x9f\x8c\x81" => "1f301",
        "\xf0\x9f\x8c\x82" => "1f302",
        "\xf0\x9f\x8c\x83" => "1f303",
        "\xf0\x9f\x8c\x84" => "1f304",
        "\xf0\x9f\x8c\x85" => "1f305",
        "\xf0\x9f\x8c\x86" => "1f306",
        "\xf0\x9f\x8c\x87" => "1f307",
        "\xf0\x9f\x8c\x88" => "1f308",
        "\xf0\x9f\x8c\x89" => "1f309",
        "\xf0\x9f\x8c\x8a" => "1f30a",
        "\xf0\x9f\x8c\x8b" => "1f30b",
        "\xf0\x9f\x8c\x8c" => "1f30c",
        "\xf0\x9f\x8c\x8d" => "1f30d",
        "\xf0\x9f\x8c\x8e" => "1f30e",
        "\xf0\x9f\x8c\x8f" => "1f30f",
        "\xf0\x9f\x8c\x90" => "1f310",
        "\xf0\x9f\x8c\x91" => "1f311",
        "\xf0\x9f\x8c\x92" => "1f312",
        "\xf0\x9f\x8c\x93" => "1f313",
        "\xf0\x9f\x8c\x94" => "1f314",
        "\xf0\x9f\x8c\x95" => "1f315",
        "\xf0\x9f\x8c\x96" => "1f316",
        "\xf0\x9f\x8c\x97" => "1f317",
        "\xf0\x9f\x8c\x98" => "1f318",
        "\xf0\x9f\x8c\x99" => "1f319",
        "\xf0\x9f\x8c\x9a" => "1f31a",
        "\xf0\x9f\x8c\x9b" => "1f31b",
        "\xf0\x9f\x8c\x9c" => "1f31c",
        "\xf0\x9f\x8c\x9d" => "1f31d",
        "\xf0\x9f\x8c\x9e" => "1f31e",
        "\xf0\x9f\x8c\x9f" => "1f31f",
        "\xf0\x9f\x8c\xa0" => "1f320",
        "\xf0\x9f\x8c\xa1" => "1f321",
        "\xf0\x9f\x8c\xa4" => "1f324",
        "\xf0\x9f\x8c\xa5" => "1f325",
        "\xf0\x9f\x8c\xa6" => "1f326",
        "\xf0\x9f\x8c\xa7" => "1f327",
        "\xf0\x9f\x8c\xa8" => "1f328",
        "\xf0\x9f\x8c\xa9" => "1f329",
        "\xf0\x9f\x8c\xaa" => "1f32a",
        "\xf0\x9f\x8c\xab" => "1f32b",
        "\xf0\x9f\x8c\xac" => "1f32c",
        "\xf0\x9f\x8c\xad" => "1f32d",
        "\xf0\x9f\x8c\xae" => "1f32e",
        "\xf0\x9f\x8c\xaf" => "1f32f",
        "\xf0\x9f\x8c\xb0" => "1f330",
        "\xf0\x9f\x8c\xb1" => "1f331",
        "\xf0\x9f\x8c\xb2" => "1f332",
        "\xf0\x9f\x8c\xb3" => "1f333",
        "\xf0\x9f\x8c\xb4" => "1f334",
        "\xf0\x9f\x8c\xb5" => "1f335",
        "\xf0\x9f\x8c\xb6" => "1f336",
        "\xf0\x9f\x8c\xb7" => "1f337",
        "\xf0\x9f\x8c\xb8" => "1f338",
        "\xf0\x9f\x8c\xb9" => "1f339",
        "\xf0\x9f\x8c\xba" => "1f33a",
        "\xf0\x9f\x8c\xbb" => "1f33b",
        "\xf0\x9f\x8c\xbc" => "1f33c",
        "\xf0\x9f\x8c\xbd" => "1f33d",
        "\xf0\x9f\x8c\xbe" => "1f33e",
        "\xf0\x9f\x8c\xbf" => "1f33f",
        "\xf0\x9f\x8d\x80" => "1f340",
        "\xf0\x9f\x8d\x81" => "1f341",
        "\xf0\x9f\x8d\x82" => "1f342",
        "\xf0\x9f\x8d\x83" => "1f343",
        "\xf0\x9f\x8d\x84" => "1f344",
        "\xf0\x9f\x8d\x85" => "1f345",
        "\xf0\x9f\x8d\x86" => "1f346",
        "\xf0\x9f\x8d\x87" => "1f347",
        "\xf0\x9f\x8d\x88" => "1f348",
        "\xf0\x9f\x8d\x89" => "1f349",
        "\xf0\x9f\x8d\x8a" => "1f34a",
        "\xf0\x9f\x8d\x8b" => "1f34b",
        "\xf0\x9f\x8d\x8c" => "1f34c",
        "\xf0\x9f\x8d\x8d" => "1f34d",
        "\xf0\x9f\x8d\x8e" => "1f34e",
        "\xf0\x9f\x8d\x8f" => "1f34f",
        "\xf0\x9f\x8d\x90" => "1f350",
        "\xf0\x9f\x8d\x91" => "1f351",
        "\xf0\x9f\x8d\x92" => "1f352",
        "\xf0\x9f\x8d\x93" => "1f353",
        "\xf0\x9f\x8d\x94" => "1f354",
        "\xf0\x9f\x8d\x95" => "1f355",
        "\xf0\x9f\x8d\x96" => "1f356",
        "\xf0\x9f\x8d\x97" => "1f357",
        "\xf0\x9f\x8d\x98" => "1f358",
        "\xf0\x9f\x8d\x99" => "1f359",
        "\xf0\x9f\x8d\x9a" => "1f35a",
        "\xf0\x9f\x8d\x9b" => "1f35b",
        "\xf0\x9f\x8d\x9c" => "1f35c",
        "\xf0\x9f\x8d\x9d" => "1f35d",
        "\xf0\x9f\x8d\x9e" => "1f35e",
        "\xf0\x9f\x8d\x9f" => "1f35f",
        "\xf0\x9f\x8d\xa0" => "1f360",
        "\xf0\x9f\x8d\xa1" => "1f361",
        "\xf0\x9f\x8d\xa2" => "1f362",
        "\xf0\x9f\x8d\xa3" => "1f363",
        "\xf0\x9f\x8d\xa4" => "1f364",
        "\xf0\x9f\x8d\xa5" => "1f365",
        "\xf0\x9f\x8d\xa6" => "1f366",
        "\xf0\x9f\x8d\xa7" => "1f367",
        "\xf0\x9f\x8d\xa8" => "1f368",
        "\xf0\x9f\x8d\xa9" => "1f369",
        "\xf0\x9f\x8d\xaa" => "1f36a",
        "\xf0\x9f\x8d\xab" => "1f36b",
        "\xf0\x9f\x8d\xac" => "1f36c",
        "\xf0\x9f\x8d\xad" => "1f36d",
        "\xf0\x9f\x8d\xae" => "1f36e",
        "\xf0\x9f\x8d\xaf" => "1f36f",
        "\xf0\x9f\x8d\xb0" => "1f370",
        "\xf0\x9f\x8d\xb1" => "1f371",
        "\xf0\x9f\x8d\xb2" => "1f372",
        "\xf0\x9f\x8d\xb3" => "1f373",
        "\xf0\x9f\x8d\xb4" => "1f374",
        "\xf0\x9f\x8d\xb5" => "1f375",
        "\xf0\x9f\x8d\xb6" => "1f376",
        "\xf0\x9f\x8d\xb7" => "1f377",
        "\xf0\x9f\x8d\xb8" => "1f378",
        "\xf0\x9f\x8d\xb9" => "1f379",
        "\xf0\x9f\x8d\xba" => "1f37a",
        "\xf0\x9f\x8d\xbb" => "1f37b",
        "\xf0\x9f\x8d\xbc" => "1f37c",
        "\xf0\x9f\x8d\xbd" => "1f37d",
        "\xf0\x9f\x8d\xbe" => "1f37e",
        "\xf0\x9f\x8d\xbf" => "1f37f",
        "\xf0\x9f\x8e\x80" => "1f380",
        "\xf0\x9f\x8e\x81" => "1f381",
        "\xf0\x9f\x8e\x82" => "1f382",
        "\xf0\x9f\x8e\x83" => "1f383",
        "\xf0\x9f\x8e\x84" => "1f384",
        "\xf0\x9f\x8e\x85" => "1f385",
        "\xf0\x9f\x8e\x86" => "1f386",
        "\xf0\x9f\x8e\x87" => "1f387",
        "\xf0\x9f\x8e\x88" => "1f388",
        "\xf0\x9f\x8e\x89" => "1f389",
        "\xf0\x9f\x8e\x8a" => "1f38a",
        "\xf0\x9f\x8e\x8b" => "1f38b",
        "\xf0\x9f\x8e\x8c" => "1f38c",
        "\xf0\x9f\x8e\x8d" => "1f38d",
        "\xf0\x9f\x8e\x8e" => "1f38e",
        "\xf0\x9f\x8e\x8f" => "1f38f",
        "\xf0\x9f\x8e\x90" => "1f390",
        "\xf0\x9f\x8e\x91" => "1f391",
        "\xf0\x9f\x8e\x92" => "1f392",
        "\xf0\x9f\x8e\x93" => "1f393",
        "\xf0\x9f\x8e\x96" => "1f396",
        "\xf0\x9f\x8e\x97" => "1f397",
        "\xf0\x9f\x8e\x99" => "1f399",
        "\xf0\x9f\x8e\x9a" => "1f39a",
        "\xf0\x9f\x8e\x9b" => "1f39b",
        "\xf0\x9f\x8e\x9e" => "1f39e",
        "\xf0\x9f\x8e\x9f" => "1f39f",
        "\xf0\x9f\x8e\xa0" => "1f3a0",
        "\xf0\x9f\x8e\xa1" => "1f3a1",
        "\xf0\x9f\x8e\xa2" => "1f3a2",
        "\xf0\x9f\x8e\xa3" => "1f3a3",
        "\xf0\x9f\x8e\xa4" => "1f3a4",
        "\xf0\x9f\x8e\xa5" => "1f3a5",
        "\xf0\x9f\x8e\xa6" => "1f3a6",
        "\xf0\x9f\x8e\xa7" => "1f3a7",
        "\xf0\x9f\x8e\xa8" => "1f3a8",
        "\xf0\x9f\x8e\xa9" => "1f3a9",
        "\xf0\x9f\x8e\xaa" => "1f3aa",
        "\xf0\x9f\x8e\xab" => "1f3ab",
        "\xf0\x9f\x8e\xac" => "1f3ac",
        "\xf0\x9f\x8e\xad" => "1f3ad",
        "\xf0\x9f\x8e\xae" => "1f3ae",
        "\xf0\x9f\x8e\xaf" => "1f3af",
        "\xf0\x9f\x8e\xb0" => "1f3b0",
        "\xf0\x9f\x8e\xb1" => "1f3b1",
        "\xf0\x9f\x8e\xb2" => "1f3b2",
        "\xf0\x9f\x8e\xb3" => "1f3b3",
        "\xf0\x9f\x8e\xb4" => "1f3b4",
        "\xf0\x9f\x8e\xb5" => "1f3b5",
        "\xf0\x9f\x8e\xb6" => "1f3b6",
        "\xf0\x9f\x8e\xb7" => "1f3b7",
        "\xf0\x9f\x8e\xb8" => "1f3b8",
        "\xf0\x9f\x8e\xb9" => "1f3b9",
        "\xf0\x9f\x8e\xba" => "1f3ba",
        "\xf0\x9f\x8e\xbb" => "1f3bb",
        "\xf0\x9f\x8e\xbc" => "1f3bc",
        "\xf0\x9f\x8e\xbd" => "1f3bd",
        "\xf0\x9f\x8e\xbe" => "1f3be",
        "\xf0\x9f\x8e\xbf" => "1f3bf",
        "\xf0\x9f\x8f\x80" => "1f3c0",
        "\xf0\x9f\x8f\x81" => "1f3c1",
        "\xf0\x9f\x8f\x82" => "1f3c2",
        "\xf0\x9f\x8f\x83" => "1f3c3",
        "\xf0\x9f\x8f\x84" => "1f3c4",
        "\xf0\x9f\x8f\x85" => "1f3c5",
        "\xf0\x9f\x8f\x86" => "1f3c6",
        "\xf0\x9f\x8f\x87" => "1f3c7",
        "\xf0\x9f\x8f\x88" => "1f3c8",
        "\xf0\x9f\x8f\x89" => "1f3c9",
        "\xf0\x9f\x8f\x8a" => "1f3ca",
        "\xf0\x9f\x8f\x8b" => "1f3cb",
        "\xf0\x9f\x8f\x8c" => "1f3cc",
        "\xf0\x9f\x8f\x8d" => "1f3cd",
        "\xf0\x9f\x8f\x8e" => "1f3ce",
        "\xf0\x9f\x8f\x8f" => "1f3cf",
        "\xf0\x9f\x8f\x90" => "1f3d0",
        "\xf0\x9f\x8f\x91" => "1f3d1",
        "\xf0\x9f\x8f\x92" => "1f3d2",
        "\xf0\x9f\x8f\x93" => "1f3d3",
        "\xf0\x9f\x8f\x94" => "1f3d4",
        "\xf0\x9f\x8f\x95" => "1f3d5",
        "\xf0\x9f\x8f\x96" => "1f3d6",
        "\xf0\x9f\x8f\x97" => "1f3d7",
        "\xf0\x9f\x8f\x98" => "1f3d8",
        "\xf0\x9f\x8f\x99" => "1f3d9",
        "\xf0\x9f\x8f\x9a" => "1f3da",
        "\xf0\x9f\x8f\x9b" => "1f3db",
        "\xf0\x9f\x8f\x9c" => "1f3dc",
        "\xf0\x9f\x8f\x9d" => "1f3dd",
        "\xf0\x9f\x8f\x9e" => "1f3de",
        "\xf0\x9f\x8f\x9f" => "1f3df",
        "\xf0\x9f\x8f\xa0" => "1f3e0",
        "\xf0\x9f\x8f\xa1" => "1f3e1",
        "\xf0\x9f\x8f\xa2" => "1f3e2",
        "\xf0\x9f\x8f\xa3" => "1f3e3",
        "\xf0\x9f\x8f\xa4" => "1f3e4",
        "\xf0\x9f\x8f\xa5" => "1f3e5",
        "\xf0\x9f\x8f\xa6" => "1f3e6",
        "\xf0\x9f\x8f\xa7" => "1f3e7",
        "\xf0\x9f\x8f\xa8" => "1f3e8",
        "\xf0\x9f\x8f\xa9" => "1f3e9",
        "\xf0\x9f\x8f\xaa" => "1f3ea",
        "\xf0\x9f\x8f\xab" => "1f3eb",
        "\xf0\x9f\x8f\xac" => "1f3ec",
        "\xf0\x9f\x8f\xad" => "1f3ed",
        "\xf0\x9f\x8f\xae" => "1f3ee",
        "\xf0\x9f\x8f\xaf" => "1f3ef",
        "\xf0\x9f\x8f\xb0" => "1f3f0",
        "\xf0\x9f\x8f\xb3" => "1f3f3",
        "\xf0\x9f\x8f\xb4" => "1f3f4",
        "\xf0\x9f\x8f\xb5" => "1f3f5",
        "\xf0\x9f\x8f\xb7" => "1f3f7",
        "\xf0\x9f\x8f\xb8" => "1f3f8",
        "\xf0\x9f\x8f\xb9" => "1f3f9",
        "\xf0\x9f\x8f\xba" => "1f3fa",
        "\xf0\x9f\x8f\xbb" => "1f3fb",
        "\xf0\x9f\x8f\xbc" => "1f3fc",
        "\xf0\x9f\x8f\xbd" => "1f3fd",
        "\xf0\x9f\x8f\xbe" => "1f3fe",
        "\xf0\x9f\x8f\xbf" => "1f3ff",
        "\xf0\x9f\x90\x80" => "1f400",
        "\xf0\x9f\x90\x81" => "1f401",
        "\xf0\x9f\x90\x82" => "1f402",
        "\xf0\x9f\x90\x83" => "1f403",
        "\xf0\x9f\x90\x84" => "1f404",
        "\xf0\x9f\x90\x85" => "1f405",
        "\xf0\x9f\x90\x86" => "1f406",
        "\xf0\x9f\x90\x87" => "1f407",
        "\xf0\x9f\x90\x88" => "1f408",
        "\xf0\x9f\x90\x89" => "1f409",
        "\xf0\x9f\x90\x8a" => "1f40a",
        "\xf0\x9f\x90\x8b" => "1f40b",
        "\xf0\x9f\x90\x8c" => "1f40c",
        "\xf0\x9f\x90\x8d" => "1f40d",
        "\xf0\x9f\x90\x8e" => "1f40e",
        "\xf0\x9f\x90\x8f" => "1f40f",
        "\xf0\x9f\x90\x90" => "1f410",
        "\xf0\x9f\x90\x91" => "1f411",
        "\xf0\x9f\x90\x92" => "1f412",
        "\xf0\x9f\x90\x93" => "1f413",
        "\xf0\x9f\x90\x94" => "1f414",
        "\xf0\x9f\x90\x95" => "1f415",
        "\xf0\x9f\x90\x96" => "1f416",
        "\xf0\x9f\x90\x97" => "1f417",
        "\xf0\x9f\x90\x98" => "1f418",
        "\xf0\x9f\x90\x99" => "1f419",
        "\xf0\x9f\x90\x9a" => "1f41a",
        "\xf0\x9f\x90\x9b" => "1f41b",
        "\xf0\x9f\x90\x9c" => "1f41c",
        "\xf0\x9f\x90\x9d" => "1f41d",
        "\xf0\x9f\x90\x9e" => "1f41e",
        "\xf0\x9f\x90\x9f" => "1f41f",
        "\xf0\x9f\x90\xa0" => "1f420",
        "\xf0\x9f\x90\xa1" => "1f421",
        "\xf0\x9f\x90\xa2" => "1f422",
        "\xf0\x9f\x90\xa3" => "1f423",
        "\xf0\x9f\x90\xa4" => "1f424",
        "\xf0\x9f\x90\xa5" => "1f425",
        "\xf0\x9f\x90\xa6" => "1f426",
        "\xf0\x9f\x90\xa7" => "1f427",
        "\xf0\x9f\x90\xa8" => "1f428",
        "\xf0\x9f\x90\xa9" => "1f429",
        "\xf0\x9f\x90\xaa" => "1f42a",
        "\xf0\x9f\x90\xab" => "1f42b",
        "\xf0\x9f\x90\xac" => "1f42c",
        "\xf0\x9f\x90\xad" => "1f42d",
        "\xf0\x9f\x90\xae" => "1f42e",
        "\xf0\x9f\x90\xaf" => "1f42f",
        "\xf0\x9f\x90\xb0" => "1f430",
        "\xf0\x9f\x90\xb1" => "1f431",
        "\xf0\x9f\x90\xb2" => "1f432",
        "\xf0\x9f\x90\xb3" => "1f433",
        "\xf0\x9f\x90\xb4" => "1f434",
        "\xf0\x9f\x90\xb5" => "1f435",
        "\xf0\x9f\x90\xb6" => "1f436",
        "\xf0\x9f\x90\xb7" => "1f437",
        "\xf0\x9f\x90\xb8" => "1f438",
        "\xf0\x9f\x90\xb9" => "1f439",
        "\xf0\x9f\x90\xba" => "1f43a",
        "\xf0\x9f\x90\xbb" => "1f43b",
        "\xf0\x9f\x90\xbc" => "1f43c",
        "\xf0\x9f\x90\xbd" => "1f43d",
        "\xf0\x9f\x90\xbe" => "1f43e",
        "\xf0\x9f\x90\xbf" => "1f43f",
        "\xf0\x9f\x91\x80" => "1f440",
        "\xf0\x9f\x91\x81" => "1f441",
        "\xf0\x9f\x91\x82" => "1f442",
        "\xf0\x9f\x91\x83" => "1f443",
        "\xf0\x9f\x91\x84" => "1f444",
        "\xf0\x9f\x91\x85" => "1f445",
        "\xf0\x9f\x91\x86" => "1f446",
        "\xf0\x9f\x91\x87" => "1f447",
        "\xf0\x9f\x91\x88" => "1f448",
        "\xf0\x9f\x91\x89" => "1f449",
        "\xf0\x9f\x91\x8a" => "1f44a",
        "\xf0\x9f\x91\x8b" => "1f44b",
        "\xf0\x9f\x91\x8c" => "1f44c",
        "\xf0\x9f\x91\x8d" => "1f44d",
        "\xf0\x9f\x91\x8e" => "1f44e",
        "\xf0\x9f\x91\x8f" => "1f44f",
        "\xf0\x9f\x91\x90" => "1f450",
        "\xf0\x9f\x91\x91" => "1f451",
        "\xf0\x9f\x91\x92" => "1f452",
        "\xf0\x9f\x91\x93" => "1f453",
        "\xf0\x9f\x91\x94" => "1f454",
        "\xf0\x9f\x91\x95" => "1f455",
        "\xf0\x9f\x91\x96" => "1f456",
        "\xf0\x9f\x91\x97" => "1f457",
        "\xf0\x9f\x91\x98" => "1f458",
        "\xf0\x9f\x91\x99" => "1f459",
        "\xf0\x9f\x91\x9a" => "1f45a",
        "\xf0\x9f\x91\x9b" => "1f45b",
        "\xf0\x9f\x91\x9c" => "1f45c",
        "\xf0\x9f\x91\x9d" => "1f45d",
        "\xf0\x9f\x91\x9e" => "1f45e",
        "\xf0\x9f\x91\x9f" => "1f45f",
        "\xf0\x9f\x91\xa0" => "1f460",
        "\xf0\x9f\x91\xa1" => "1f461",
        "\xf0\x9f\x91\xa2" => "1f462",
        "\xf0\x9f\x91\xa3" => "1f463",
        "\xf0\x9f\x91\xa4" => "1f464",
        "\xf0\x9f\x91\xa5" => "1f465",
        "\xf0\x9f\x91\xa6" => "1f466",
        "\xf0\x9f\x91\xa7" => "1f467",
        "\xf0\x9f\x91\xa8" => "1f468",
        "\xf0\x9f\x91\xa9" => "1f469",
        "\xf0\x9f\x91\xaa" => "1f46a",
        "\xf0\x9f\x91\xab" => "1f46b",
        "\xf0\x9f\x91\xac" => "1f46c",
        "\xf0\x9f\x91\xad" => "1f46d",
        "\xf0\x9f\x91\xae" => "1f46e",
        "\xf0\x9f\x91\xaf" => "1f46f",
        "\xf0\x9f\x91\xb0" => "1f470",
        "\xf0\x9f\x91\xb1" => "1f471",
        "\xf0\x9f\x91\xb2" => "1f472",
        "\xf0\x9f\x91\xb3" => "1f473",
        "\xf0\x9f\x91\xb4" => "1f474",
        "\xf0\x9f\x91\xb5" => "1f475",
        "\xf0\x9f\x91\xb6" => "1f476",
        "\xf0\x9f\x91\xb7" => "1f477",
        "\xf0\x9f\x91\xb8" => "1f478",
        "\xf0\x9f\x91\xb9" => "1f479",
        "\xf0\x9f\x91\xba" => "1f47a",
        "\xf0\x9f\x91\xbb" => "1f47b",
        "\xf0\x9f\x91\xbc" => "1f47c",
        "\xf0\x9f\x91\xbd" => "1f47d",
        "\xf0\x9f\x91\xbe" => "1f47e",
        "\xf0\x9f\x91\xbf" => "1f47f",
        "\xf0\x9f\x92\x80" => "1f480",
        "\xf0\x9f\x92\x81" => "1f481",
        "\xf0\x9f\x92\x82" => "1f482",
        "\xf0\x9f\x92\x83" => "1f483",
        "\xf0\x9f\x92\x84" => "1f484",
        "\xf0\x9f\x92\x85" => "1f485",
        "\xf0\x9f\x92\x86" => "1f486",
        "\xf0\x9f\x92\x87" => "1f487",
        "\xf0\x9f\x92\x88" => "1f488",
        "\xf0\x9f\x92\x89" => "1f489",
        "\xf0\x9f\x92\x8a" => "1f48a",
        "\xf0\x9f\x92\x8b" => "1f48b",
        "\xf0\x9f\x92\x8c" => "1f48c",
        "\xf0\x9f\x92\x8d" => "1f48d",
        "\xf0\x9f\x92\x8e" => "1f48e",
        "\xf0\x9f\x92\x8f" => "1f48f",
        "\xf0\x9f\x92\x90" => "1f490",
        "\xf0\x9f\x92\x91" => "1f491",
        "\xf0\x9f\x92\x92" => "1f492",
        "\xf0\x9f\x92\x93" => "1f493",
        "\xf0\x9f\x92\x94" => "1f494",
        "\xf0\x9f\x92\x95" => "1f495",
        "\xf0\x9f\x92\x96" => "1f496",
        "\xf0\x9f\x92\x97" => "1f497",
        "\xf0\x9f\x92\x98" => "1f498",
        "\xf0\x9f\x92\x99" => "1f499",
        "\xf0\x9f\x92\x9a" => "1f49a",
        "\xf0\x9f\x92\x9b" => "1f49b",
        "\xf0\x9f\x92\x9c" => "1f49c",
        "\xf0\x9f\x92\x9d" => "1f49d",
        "\xf0\x9f\x92\x9e" => "1f49e",
        "\xf0\x9f\x92\x9f" => "1f49f",
        "\xf0\x9f\x92\xa0" => "1f4a0",
        "\xf0\x9f\x92\xa1" => "1f4a1",
        "\xf0\x9f\x92\xa2" => "1f4a2",
        "\xf0\x9f\x92\xa3" => "1f4a3",
        "\xf0\x9f\x92\xa4" => "1f4a4",
        "\xf0\x9f\x92\xa5" => "1f4a5",
        "\xf0\x9f\x92\xa6" => "1f4a6",
        "\xf0\x9f\x92\xa7" => "1f4a7",
        "\xf0\x9f\x92\xa8" => "1f4a8",
        "\xf0\x9f\x92\xa9" => "1f4a9",
        "\xf0\x9f\x92\xaa" => "1f4aa",
        "\xf0\x9f\x92\xab" => "1f4ab",
        "\xf0\x9f\x92\xac" => "1f4ac",
        "\xf0\x9f\x92\xad" => "1f4ad",
        "\xf0\x9f\x92\xae" => "1f4ae",
        "\xf0\x9f\x92\xaf" => "1f4af",
        "\xf0\x9f\x92\xb0" => "1f4b0",
        "\xf0\x9f\x92\xb1" => "1f4b1",
        "\xf0\x9f\x92\xb2" => "1f4b2",
        "\xf0\x9f\x92\xb3" => "1f4b3",
        "\xf0\x9f\x92\xb4" => "1f4b4",
        "\xf0\x9f\x92\xb5" => "1f4b5",
        "\xf0\x9f\x92\xb6" => "1f4b6",
        "\xf0\x9f\x92\xb7" => "1f4b7",
        "\xf0\x9f\x92\xb8" => "1f4b8",
        "\xf0\x9f\x92\xb9" => "1f4b9",
        "\xf0\x9f\x92\xba" => "1f4ba",
        "\xf0\x9f\x92\xbb" => "1f4bb",
        "\xf0\x9f\x92\xbc" => "1f4bc",
        "\xf0\x9f\x92\xbd" => "1f4bd",
        "\xf0\x9f\x92\xbe" => "1f4be",
        "\xf0\x9f\x92\xbf" => "1f4bf",
        "\xf0\x9f\x93\x80" => "1f4c0",
        "\xf0\x9f\x93\x81" => "1f4c1",
        "\xf0\x9f\x93\x82" => "1f4c2",
        "\xf0\x9f\x93\x83" => "1f4c3",
        "\xf0\x9f\x93\x84" => "1f4c4",
        "\xf0\x9f\x93\x85" => "1f4c5",
        "\xf0\x9f\x93\x86" => "1f4c6",
        "\xf0\x9f\x93\x87" => "1f4c7",
        "\xf0\x9f\x93\x88" => "1f4c8",
        "\xf0\x9f\x93\x89" => "1f4c9",
        "\xf0\x9f\x93\x8a" => "1f4ca",
        "\xf0\x9f\x93\x8b" => "1f4cb",
        "\xf0\x9f\x93\x8c" => "1f4cc",
        "\xf0\x9f\x93\x8d" => "1f4cd",
        "\xf0\x9f\x93\x8e" => "1f4ce",
        "\xf0\x9f\x93\x8f" => "1f4cf",
        "\xf0\x9f\x93\x90" => "1f4d0",
        "\xf0\x9f\x93\x91" => "1f4d1",
        "\xf0\x9f\x93\x92" => "1f4d2",
        "\xf0\x9f\x93\x93" => "1f4d3",
        "\xf0\x9f\x93\x94" => "1f4d4",
        "\xf0\x9f\x93\x95" => "1f4d5",
        "\xf0\x9f\x93\x96" => "1f4d6",
        "\xf0\x9f\x93\x97" => "1f4d7",
        "\xf0\x9f\x93\x98" => "1f4d8",
        "\xf0\x9f\x93\x99" => "1f4d9",
        "\xf0\x9f\x93\x9a" => "1f4da",
        "\xf0\x9f\x93\x9b" => "1f4db",
        "\xf0\x9f\x93\x9c" => "1f4dc",
        "\xf0\x9f\x93\x9d" => "1f4dd",
        "\xf0\x9f\x93\x9e" => "1f4de",
        "\xf0\x9f\x93\x9f" => "1f4df",
        "\xf0\x9f\x93\xa0" => "1f4e0",
        "\xf0\x9f\x93\xa1" => "1f4e1",
        "\xf0\x9f\x93\xa2" => "1f4e2",
        "\xf0\x9f\x93\xa3" => "1f4e3",
        "\xf0\x9f\x93\xa4" => "1f4e4",
        "\xf0\x9f\x93\xa5" => "1f4e5",
        "\xf0\x9f\x93\xa6" => "1f4e6",
        "\xf0\x9f\x93\xa7" => "1f4e7",
        "\xf0\x9f\x93\xa8" => "1f4e8",
        "\xf0\x9f\x93\xa9" => "1f4e9",
        "\xf0\x9f\x93\xaa" => "1f4ea",
        "\xf0\x9f\x93\xab" => "1f4eb",
        "\xf0\x9f\x93\xac" => "1f4ec",
        "\xf0\x9f\x93\xad" => "1f4ed",
        "\xf0\x9f\x93\xae" => "1f4ee",
        "\xf0\x9f\x93\xaf" => "1f4ef",
        "\xf0\x9f\x93\xb0" => "1f4f0",
        "\xf0\x9f\x93\xb1" => "1f4f1",
        "\xf0\x9f\x93\xb2" => "1f4f2",
        "\xf0\x9f\x93\xb3" => "1f4f3",
        "\xf0\x9f\x93\xb4" => "1f4f4",
        "\xf0\x9f\x93\xb5" => "1f4f5",
        "\xf0\x9f\x93\xb6" => "1f4f6",
        "\xf0\x9f\x93\xb7" => "1f4f7",
        "\xf0\x9f\x93\xb8" => "1f4f8",
        "\xf0\x9f\x93\xb9" => "1f4f9",
        "\xf0\x9f\x93\xba" => "1f4fa",
        "\xf0\x9f\x93\xbb" => "1f4fb",
        "\xf0\x9f\x93\xbc" => "1f4fc",
        "\xf0\x9f\x93\xbd" => "1f4fd",
        "\xf0\x9f\x93\xbf" => "1f4ff",
        "\xf0\x9f\x94\x80" => "1f500",
        "\xf0\x9f\x94\x81" => "1f501",
        "\xf0\x9f\x94\x82" => "1f502",
        "\xf0\x9f\x94\x83" => "1f503",
        "\xf0\x9f\x94\x84" => "1f504",
        "\xf0\x9f\x94\x85" => "1f505",
        "\xf0\x9f\x94\x86" => "1f506",
        "\xf0\x9f\x94\x87" => "1f507",
        "\xf0\x9f\x94\x88" => "1f508",
        "\xf0\x9f\x94\x89" => "1f509",
        "\xf0\x9f\x94\x8a" => "1f50a",
        "\xf0\x9f\x94\x8b" => "1f50b",
        "\xf0\x9f\x94\x8c" => "1f50c",
        "\xf0\x9f\x94\x8d" => "1f50d",
        "\xf0\x9f\x94\x8e" => "1f50e",
        "\xf0\x9f\x94\x8f" => "1f50f",
        "\xf0\x9f\x94\x90" => "1f510",
        "\xf0\x9f\x94\x91" => "1f511",
        "\xf0\x9f\x94\x92" => "1f512",
        "\xf0\x9f\x94\x93" => "1f513",
        "\xf0\x9f\x94\x94" => "1f514",
        "\xf0\x9f\x94\x95" => "1f515",
        "\xf0\x9f\x94\x96" => "1f516",
        "\xf0\x9f\x94\x97" => "1f517",
        "\xf0\x9f\x94\x98" => "1f518",
        "\xf0\x9f\x94\x99" => "1f519",
        "\xf0\x9f\x94\x9a" => "1f51a",
        "\xf0\x9f\x94\x9b" => "1f51b",
        "\xf0\x9f\x94\x9c" => "1f51c",
        "\xf0\x9f\x94\x9d" => "1f51d",
        "\xf0\x9f\x94\x9e" => "1f51e",
        "\xf0\x9f\x94\x9f" => "1f51f",
        "\xf0\x9f\x94\xa0" => "1f520",
        "\xf0\x9f\x94\xa1" => "1f521",
        "\xf0\x9f\x94\xa2" => "1f522",
        "\xf0\x9f\x94\xa3" => "1f523",
        "\xf0\x9f\x94\xa4" => "1f524",
        "\xf0\x9f\x94\xa5" => "1f525",
        "\xf0\x9f\x94\xa6" => "1f526",
        "\xf0\x9f\x94\xa7" => "1f527",
        "\xf0\x9f\x94\xa8" => "1f528",
        "\xf0\x9f\x94\xa9" => "1f529",
        "\xf0\x9f\x94\xaa" => "1f52a",
        "\xf0\x9f\x94\xab" => "1f52b",
        "\xf0\x9f\x94\xac" => "1f52c",
        "\xf0\x9f\x94\xad" => "1f52d",
        "\xf0\x9f\x94\xae" => "1f52e",
        "\xf0\x9f\x94\xaf" => "1f52f",
        "\xf0\x9f\x94\xb0" => "1f530",
        "\xf0\x9f\x94\xb1" => "1f531",
        "\xf0\x9f\x94\xb2" => "1f532",
        "\xf0\x9f\x94\xb3" => "1f533",
        "\xf0\x9f\x94\xb4" => "1f534",
        "\xf0\x9f\x94\xb5" => "1f535",
        "\xf0\x9f\x94\xb6" => "1f536",
        "\xf0\x9f\x94\xb7" => "1f537",
        "\xf0\x9f\x94\xb8" => "1f538",
        "\xf0\x9f\x94\xb9" => "1f539",
        "\xf0\x9f\x94\xba" => "1f53a",
        "\xf0\x9f\x94\xbb" => "1f53b",
        "\xf0\x9f\x94\xbc" => "1f53c",
        "\xf0\x9f\x94\xbd" => "1f53d",
        "\xf0\x9f\x95\x89" => "1f549",
        "\xf0\x9f\x95\x8a" => "1f54a",
        "\xf0\x9f\x95\x8b" => "1f54b",
        "\xf0\x9f\x95\x8c" => "1f54c",
        "\xf0\x9f\x95\x8d" => "1f54d",
        "\xf0\x9f\x95\x8e" => "1f54e",
        "\xf0\x9f\x95\x90" => "1f550",
        "\xf0\x9f\x95\x91" => "1f551",
        "\xf0\x9f\x95\x92" => "1f552",
        "\xf0\x9f\x95\x93" => "1f553",
        "\xf0\x9f\x95\x94" => "1f554",
        "\xf0\x9f\x95\x95" => "1f555",
        "\xf0\x9f\x95\x96" => "1f556",
        "\xf0\x9f\x95\x97" => "1f557",
        "\xf0\x9f\x95\x98" => "1f558",
        "\xf0\x9f\x95\x99" => "1f559",
        "\xf0\x9f\x95\x9a" => "1f55a",
        "\xf0\x9f\x95\x9b" => "1f55b",
        "\xf0\x9f\x95\x9c" => "1f55c",
        "\xf0\x9f\x95\x9d" => "1f55d",
        "\xf0\x9f\x95\x9e" => "1f55e",
        "\xf0\x9f\x95\x9f" => "1f55f",
        "\xf0\x9f\x95\xa0" => "1f560",
        "\xf0\x9f\x95\xa1" => "1f561",
        "\xf0\x9f\x95\xa2" => "1f562",
        "\xf0\x9f\x95\xa3" => "1f563",
        "\xf0\x9f\x95\xa4" => "1f564",
        "\xf0\x9f\x95\xa5" => "1f565",
        "\xf0\x9f\x95\xa6" => "1f566",
        "\xf0\x9f\x95\xa7" => "1f567",
        "\xf0\x9f\x95\xaf" => "1f56f",
        "\xf0\x9f\x95\xb0" => "1f570",
        "\xf0\x9f\x95\xb3" => "1f573",
        "\xf0\x9f\x95\xb4" => "1f574",
        "\xf0\x9f\x95\xb5" => "1f575",
        "\xf0\x9f\x95\xb6" => "1f576",
        "\xf0\x9f\x95\xb7" => "1f577",
        "\xf0\x9f\x95\xb8" => "1f578",
        "\xf0\x9f\x95\xb9" => "1f579",
        "\xf0\x9f\x96\x87" => "1f587",
        "\xf0\x9f\x96\x8a" => "1f58a",
        "\xf0\x9f\x96\x8b" => "1f58b",
        "\xf0\x9f\x96\x8c" => "1f58c",
        "\xf0\x9f\x96\x8d" => "1f58d",
        "\xf0\x9f\x96\x90" => "1f590",
        "\xf0\x9f\x96\x95" => "1f595",
        "\xf0\x9f\x96\x96" => "1f596",
        "\xf0\x9f\x96\xa5" => "1f5a5",
        "\xf0\x9f\x96\xa8" => "1f5a8",
        "\xf0\x9f\x96\xb1" => "1f5b1",
        "\xf0\x9f\x96\xb2" => "1f5b2",
        "\xf0\x9f\x96\xbc" => "1f5bc",
        "\xf0\x9f\x97\x82" => "1f5c2",
        "\xf0\x9f\x97\x83" => "1f5c3",
        "\xf0\x9f\x97\x84" => "1f5c4",
        "\xf0\x9f\x97\x91" => "1f5d1",
        "\xf0\x9f\x97\x92" => "1f5d2",
        "\xf0\x9f\x97\x93" => "1f5d3",
        "\xf0\x9f\x97\x9c" => "1f5dc",
        "\xf0\x9f\x97\x9d" => "1f5dd",
        "\xf0\x9f\x97\x9e" => "1f5de",
        "\xf0\x9f\x97\xa1" => "1f5e1",
        "\xf0\x9f\x97\xa3" => "1f5e3",
        "\xf0\x9f\x97\xa8" => "1f5e8",
        "\xf0\x9f\x97\xaf" => "1f5ef",
        "\xf0\x9f\x97\xb3" => "1f5f3",
        "\xf0\x9f\x97\xba" => "1f5fa",
        "\xf0\x9f\x97\xbb" => "1f5fb",
        "\xf0\x9f\x97\xbc" => "1f5fc",
        "\xf0\x9f\x97\xbd" => "1f5fd",
        "\xf0\x9f\x97\xbe" => "1f5fe",
        "\xf0\x9f\x97\xbf" => "1f5ff",
        "\xf0\x9f\x98\x80" => "1f600",
        "\xf0\x9f\x98\x81" => "1f601",
        "\xf0\x9f\x98\x82" => "1f602",
        "\xf0\x9f\x98\x83" => "1f603",
        "\xf0\x9f\x98\x84" => "1f604",
        "\xf0\x9f\x98\x85" => "1f605",
        "\xf0\x9f\x98\x86" => "1f606",
        "\xf0\x9f\x98\x87" => "1f607",
        "\xf0\x9f\x98\x88" => "1f608",
        "\xf0\x9f\x98\x89" => "1f609",
        "\xf0\x9f\x98\x8a" => "1f60a",
        "\xf0\x9f\x98\x8b" => "1f60b",
        "\xf0\x9f\x98\x8c" => "1f60c",
        "\xf0\x9f\x98\x8d" => "1f60d",
        "\xf0\x9f\x98\x8e" => "1f60e",
        "\xf0\x9f\x98\x8f" => "1f60f",
        "\xf0\x9f\x98\x90" => "1f610",
        "\xf0\x9f\x98\x91" => "1f611",
        "\xf0\x9f\x98\x92" => "1f612",
        "\xf0\x9f\x98\x93" => "1f613",
        "\xf0\x9f\x98\x94" => "1f614",
        "\xf0\x9f\x98\x95" => "1f615",
        "\xf0\x9f\x98\x96" => "1f616",
        "\xf0\x9f\x98\x97" => "1f617",
        "\xf0\x9f\x98\x98" => "1f618",
        "\xf0\x9f\x98\x99" => "1f619",
        "\xf0\x9f\x98\x9a" => "1f61a",
        "\xf0\x9f\x98\x9b" => "1f61b",
        "\xf0\x9f\x98\x9c" => "1f61c",
        "\xf0\x9f\x98\x9d" => "1f61d",
        "\xf0\x9f\x98\x9e" => "1f61e",
        "\xf0\x9f\x98\x9f" => "1f61f",
        "\xf0\x9f\x98\xa0" => "1f620",
        "\xf0\x9f\x98\xa1" => "1f621",
        "\xf0\x9f\x98\xa2" => "1f622",
        "\xf0\x9f\x98\xa3" => "1f623",
        "\xf0\x9f\x98\xa4" => "1f624",
        "\xf0\x9f\x98\xa5" => "1f625",
        "\xf0\x9f\x98\xa6" => "1f626",
        "\xf0\x9f\x98\xa7" => "1f627",
        "\xf0\x9f\x98\xa8" => "1f628",
        "\xf0\x9f\x98\xa9" => "1f629",
        "\xf0\x9f\x98\xaa" => "1f62a",
        "\xf0\x9f\x98\xab" => "1f62b",
        "\xf0\x9f\x98\xac" => "1f62c",
        "\xf0\x9f\x98\xad" => "1f62d",
        "\xf0\x9f\x98\xae" => "1f62e",
        "\xf0\x9f\x98\xaf" => "1f62f",
        "\xf0\x9f\x98\xb0" => "1f630",
        "\xf0\x9f\x98\xb1" => "1f631",
        "\xf0\x9f\x98\xb2" => "1f632",
        "\xf0\x9f\x98\xb3" => "1f633",
        "\xf0\x9f\x98\xb4" => "1f634",
        "\xf0\x9f\x98\xb5" => "1f635",
        "\xf0\x9f\x98\xb6" => "1f636",
        "\xf0\x9f\x98\xb7" => "1f637",
        "\xf0\x9f\x98\xb8" => "1f638",
        "\xf0\x9f\x98\xb9" => "1f639",
        "\xf0\x9f\x98\xba" => "1f63a",
        "\xf0\x9f\x98\xbb" => "1f63b",
        "\xf0\x9f\x98\xbc" => "1f63c",
        "\xf0\x9f\x98\xbd" => "1f63d",
        "\xf0\x9f\x98\xbe" => "1f63e",
        "\xf0\x9f\x98\xbf" => "1f63f",
        "\xf0\x9f\x99\x80" => "1f640",
        "\xf0\x9f\x99\x81" => "1f641",
        "\xf0\x9f\x99\x82" => "1f642",
        "\xf0\x9f\x99\x83" => "1f643",
        "\xf0\x9f\x99\x84" => "1f644",
        "\xf0\x9f\x99\x85" => "1f645",
        "\xf0\x9f\x99\x86" => "1f646",
        "\xf0\x9f\x99\x87" => "1f647",
        "\xf0\x9f\x99\x88" => "1f648",
        "\xf0\x9f\x99\x89" => "1f649",
        "\xf0\x9f\x99\x8a" => "1f64a",
        "\xf0\x9f\x99\x8b" => "1f64b",
        "\xf0\x9f\x99\x8c" => "1f64c",
        "\xf0\x9f\x99\x8d" => "1f64d",
        "\xf0\x9f\x99\x8e" => "1f64e",
        "\xf0\x9f\x99\x8f" => "1f64f",
        "\xf0\x9f\x9a\x80" => "1f680",
        "\xf0\x9f\x9a\x81" => "1f681",
        "\xf0\x9f\x9a\x82" => "1f682",
        "\xf0\x9f\x9a\x83" => "1f683",
        "\xf0\x9f\x9a\x84" => "1f684",
        "\xf0\x9f\x9a\x85" => "1f685",
        "\xf0\x9f\x9a\x86" => "1f686",
        "\xf0\x9f\x9a\x87" => "1f687",
        "\xf0\x9f\x9a\x88" => "1f688",
        "\xf0\x9f\x9a\x89" => "1f689",
        "\xf0\x9f\x9a\x8a" => "1f68a",
        "\xf0\x9f\x9a\x8b" => "1f68b",
        "\xf0\x9f\x9a\x8c" => "1f68c",
        "\xf0\x9f\x9a\x8d" => "1f68d",
        "\xf0\x9f\x9a\x8e" => "1f68e",
        "\xf0\x9f\x9a\x8f" => "1f68f",
        "\xf0\x9f\x9a\x90" => "1f690",
        "\xf0\x9f\x9a\x91" => "1f691",
        "\xf0\x9f\x9a\x92" => "1f692",
        "\xf0\x9f\x9a\x93" => "1f693",
        "\xf0\x9f\x9a\x94" => "1f694",
        "\xf0\x9f\x9a\x95" => "1f695",
        "\xf0\x9f\x9a\x96" => "1f696",
        "\xf0\x9f\x9a\x97" => "1f697",
        "\xf0\x9f\x9a\x98" => "1f698",
        "\xf0\x9f\x9a\x99" => "1f699",
        "\xf0\x9f\x9a\x9a" => "1f69a",
        "\xf0\x9f\x9a\x9b" => "1f69b",
        "\xf0\x9f\x9a\x9c" => "1f69c",
        "\xf0\x9f\x9a\x9d" => "1f69d",
        "\xf0\x9f\x9a\x9e" => "1f69e",
        "\xf0\x9f\x9a\x9f" => "1f69f",
        "\xf0\x9f\x9a\xa0" => "1f6a0",
        "\xf0\x9f\x9a\xa1" => "1f6a1",
        "\xf0\x9f\x9a\xa2" => "1f6a2",
        "\xf0\x9f\x9a\xa3" => "1f6a3",
        "\xf0\x9f\x9a\xa4" => "1f6a4",
        "\xf0\x9f\x9a\xa5" => "1f6a5",
        "\xf0\x9f\x9a\xa6" => "1f6a6",
        "\xf0\x9f\x9a\xa7" => "1f6a7",
        "\xf0\x9f\x9a\xa8" => "1f6a8",
        "\xf0\x9f\x9a\xa9" => "1f6a9",
        "\xf0\x9f\x9a\xaa" => "1f6aa",
        "\xf0\x9f\x9a\xab" => "1f6ab",
        "\xf0\x9f\x9a\xac" => "1f6ac",
        "\xf0\x9f\x9a\xad" => "1f6ad",
        "\xf0\x9f\x9a\xae" => "1f6ae",
        "\xf0\x9f\x9a\xaf" => "1f6af",
        "\xf0\x9f\x9a\xb0" => "1f6b0",
        "\xf0\x9f\x9a\xb1" => "1f6b1",
        "\xf0\x9f\x9a\xb2" => "1f6b2",
        "\xf0\x9f\x9a\xb3" => "1f6b3",
        "\xf0\x9f\x9a\xb4" => "1f6b4",
        "\xf0\x9f\x9a\xb5" => "1f6b5",
        "\xf0\x9f\x9a\xb6" => "1f6b6",
        "\xf0\x9f\x9a\xb7" => "1f6b7",
        "\xf0\x9f\x9a\xb8" => "1f6b8",
        "\xf0\x9f\x9a\xb9" => "1f6b9",
        "\xf0\x9f\x9a\xba" => "1f6ba",
        "\xf0\x9f\x9a\xbb" => "1f6bb",
        "\xf0\x9f\x9a\xbc" => "1f6bc",
        "\xf0\x9f\x9a\xbd" => "1f6bd",
        "\xf0\x9f\x9a\xbe" => "1f6be",
        "\xf0\x9f\x9a\xbf" => "1f6bf",
        "\xf0\x9f\x9b\x80" => "1f6c0",
        "\xf0\x9f\x9b\x81" => "1f6c1",
        "\xf0\x9f\x9b\x82" => "1f6c2",
        "\xf0\x9f\x9b\x83" => "1f6c3",
        "\xf0\x9f\x9b\x84" => "1f6c4",
        "\xf0\x9f\x9b\x85" => "1f6c5",
        "\xf0\x9f\x9b\x8b" => "1f6cb",
        "\xf0\x9f\x9b\x8c" => "1f6cc",
        "\xf0\x9f\x9b\x8d" => "1f6cd",
        "\xf0\x9f\x9b\x8e" => "1f6ce",
        "\xf0\x9f\x9b\x8f" => "1f6cf",
        "\xf0\x9f\x9b\x90" => "1f6d0",
        "\xf0\x9f\x9b\xa0" => "1f6e0",
        "\xf0\x9f\x9b\xa1" => "1f6e1",
        "\xf0\x9f\x9b\xa2" => "1f6e2",
        "\xf0\x9f\x9b\xa3" => "1f6e3",
        "\xf0\x9f\x9b\xa4" => "1f6e4",
        "\xf0\x9f\x9b\xa5" => "1f6e5",
        "\xf0\x9f\x9b\xa9" => "1f6e9",
        "\xf0\x9f\x9b\xab" => "1f6eb",
        "\xf0\x9f\x9b\xac" => "1f6ec",
        "\xf0\x9f\x9b\xb0" => "1f6f0",
        "\xf0\x9f\x9b\xb3" => "1f6f3",
        "\xf0\x9f\xa4\x90" => "1f910",
        "\xf0\x9f\xa4\x91" => "1f911",
        "\xf0\x9f\xa4\x92" => "1f912",
        "\xf0\x9f\xa4\x93" => "1f913",
        "\xf0\x9f\xa4\x94" => "1f914",
        "\xf0\x9f\xa4\x95" => "1f915",
        "\xf0\x9f\xa4\x96" => "1f916",
        "\xf0\x9f\xa4\x97" => "1f917",
        "\xf0\x9f\xa4\x98" => "1f918",
        "\xf0\x9f\xa6\x80" => "1f980",
        "\xf0\x9f\xa6\x81" => "1f981",
        "\xf0\x9f\xa6\x82" => "1f982",
        "\xf0\x9f\xa6\x83" => "1f983",
        "\xf0\x9f\xa6\x84" => "1f984",
        "\xf0\x9f\xa7\x80" => "1f9c0",
        "#\xe2\x83\xa3" => "2320e3",
        "*\xe2\x83\xa3" => "2a20e3",
        "0\xe2\x83\xa3" => "3020e3",
        "1\xe2\x83\xa3" => "3120e3",
        "2\xe2\x83\xa3" => "3220e3",
        "3\xe2\x83\xa3" => "3320e3",
        "4\xe2\x83\xa3" => "3420e3",
        "5\xe2\x83\xa3" => "3520e3",
        "6\xe2\x83\xa3" => "3620e3",
        "7\xe2\x83\xa3" => "3720e3",
        "8\xe2\x83\xa3" => "3820e3",
        "9\xe2\x83\xa3" => "3920e3",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xa8" => "1f1e61f1e8",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xa9" => "1f1e61f1e9",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xaa" => "1f1e61f1ea",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xab" => "1f1e61f1eb",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xac" => "1f1e61f1ec",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xae" => "1f1e61f1ee",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb1" => "1f1e61f1f1",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb2" => "1f1e61f1f2",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb4" => "1f1e61f1f4",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb6" => "1f1e61f1f6",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb7" => "1f1e61f1f7",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb8" => "1f1e61f1f8",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xb9" => "1f1e61f1f9",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xba" => "1f1e61f1fa",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbc" => "1f1e61f1fc",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbd" => "1f1e61f1fd",
        "\xf0\x9f\x87\xa6\xf0\x9f\x87\xbf" => "1f1e61f1ff",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa6" => "1f1e71f1e6",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa7" => "1f1e71f1e7",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xa9" => "1f1e71f1e9",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xaa" => "1f1e71f1ea",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xab" => "1f1e71f1eb",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xac" => "1f1e71f1ec",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xad" => "1f1e71f1ed",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xae" => "1f1e71f1ee",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xaf" => "1f1e71f1ef",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb1" => "1f1e71f1f1",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb2" => "1f1e71f1f2",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb3" => "1f1e71f1f3",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb4" => "1f1e71f1f4",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb6" => "1f1e71f1f6",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb7" => "1f1e71f1f7",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb8" => "1f1e71f1f8",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xb9" => "1f1e71f1f9",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbb" => "1f1e71f1fb",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbc" => "1f1e71f1fc",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbe" => "1f1e71f1fe",
        "\xf0\x9f\x87\xa7\xf0\x9f\x87\xbf" => "1f1e71f1ff",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa6" => "1f1e81f1e6",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa8" => "1f1e81f1e8",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xa9" => "1f1e81f1e9",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xab" => "1f1e81f1eb",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xac" => "1f1e81f1ec",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xad" => "1f1e81f1ed",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xae" => "1f1e81f1ee",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb0" => "1f1e81f1f0",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb1" => "1f1e81f1f1",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb2" => "1f1e81f1f2",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb3" => "1f1e81f1f3",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb4" => "1f1e81f1f4",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb5" => "1f1e81f1f5",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xb7" => "1f1e81f1f7",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xba" => "1f1e81f1fa",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbb" => "1f1e81f1fb",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbc" => "1f1e81f1fc",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbd" => "1f1e81f1fd",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbe" => "1f1e81f1fe",
        "\xf0\x9f\x87\xa8\xf0\x9f\x87\xbf" => "1f1e81f1ff",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaa" => "1f1e91f1ea",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xac" => "1f1e91f1ec",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xaf" => "1f1e91f1ef",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb0" => "1f1e91f1f0",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb2" => "1f1e91f1f2",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xb4" => "1f1e91f1f4",
        "\xf0\x9f\x87\xa9\xf0\x9f\x87\xbf" => "1f1e91f1ff",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xa6" => "1f1ea1f1e6",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xa8" => "1f1ea1f1e8",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xaa" => "1f1ea1f1ea",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xac" => "1f1ea1f1ec",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xad" => "1f1ea1f1ed",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb7" => "1f1ea1f1f7",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb8" => "1f1ea1f1f8",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xb9" => "1f1ea1f1f9",
        "\xf0\x9f\x87\xaa\xf0\x9f\x87\xba" => "1f1ea1f1fa",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xae" => "1f1eb1f1ee",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xaf" => "1f1eb1f1ef",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb0" => "1f1eb1f1f0",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb2" => "1f1eb1f1f2",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb4" => "1f1eb1f1f4",
        "\xf0\x9f\x87\xab\xf0\x9f\x87\xb7" => "1f1eb1f1f7",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa6" => "1f1ec1f1e6",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa7" => "1f1ec1f1e7",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xa9" => "1f1ec1f1e9",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xaa" => "1f1ec1f1ea",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xab" => "1f1ec1f1eb",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xac" => "1f1ec1f1ec",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xad" => "1f1ec1f1ed",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xae" => "1f1ec1f1ee",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb1" => "1f1ec1f1f1",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb2" => "1f1ec1f1f2",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb3" => "1f1ec1f1f3",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb5" => "1f1ec1f1f5",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb6" => "1f1ec1f1f6",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb7" => "1f1ec1f1f7",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb8" => "1f1ec1f1f8",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xb9" => "1f1ec1f1f9",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xba" => "1f1ec1f1fa",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xbc" => "1f1ec1f1fc",
        "\xf0\x9f\x87\xac\xf0\x9f\x87\xbe" => "1f1ec1f1fe",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb0" => "1f1ed1f1f0",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb2" => "1f1ed1f1f2",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb3" => "1f1ed1f1f3",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb7" => "1f1ed1f1f7",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xb9" => "1f1ed1f1f9",
        "\xf0\x9f\x87\xad\xf0\x9f\x87\xba" => "1f1ed1f1fa",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xa8" => "1f1ee1f1e8",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xa9" => "1f1ee1f1e9",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xaa" => "1f1ee1f1ea",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb1" => "1f1ee1f1f1",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb2" => "1f1ee1f1f2",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb3" => "1f1ee1f1f3",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb4" => "1f1ee1f1f4",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb6" => "1f1ee1f1f6",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb7" => "1f1ee1f1f7",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb8" => "1f1ee1f1f8",
        "\xf0\x9f\x87\xae\xf0\x9f\x87\xb9" => "1f1ee1f1f9",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xaa" => "1f1ef1f1ea",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb2" => "1f1ef1f1f2",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb4" => "1f1ef1f1f4",
        "\xf0\x9f\x87\xaf\xf0\x9f\x87\xb5" => "1f1ef1f1f5",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xaa" => "1f1f01f1ea",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xac" => "1f1f01f1ec",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xad" => "1f1f01f1ed",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xae" => "1f1f01f1ee",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb2" => "1f1f01f1f2",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb3" => "1f1f01f1f3",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb5" => "1f1f01f1f5",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xb7" => "1f1f01f1f7",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbc" => "1f1f01f1fc",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbe" => "1f1f01f1fe",
        "\xf0\x9f\x87\xb0\xf0\x9f\x87\xbf" => "1f1f01f1ff",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa6" => "1f1f11f1e6",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa7" => "1f1f11f1e7",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xa8" => "1f1f11f1e8",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xae" => "1f1f11f1ee",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb0" => "1f1f11f1f0",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb7" => "1f1f11f1f7",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb8" => "1f1f11f1f8",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xb9" => "1f1f11f1f9",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xba" => "1f1f11f1fa",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xbb" => "1f1f11f1fb",
        "\xf0\x9f\x87\xb1\xf0\x9f\x87\xbe" => "1f1f11f1fe",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa6" => "1f1f21f1e6",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa8" => "1f1f21f1e8",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xa9" => "1f1f21f1e9",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xaa" => "1f1f21f1ea",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xab" => "1f1f21f1eb",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xac" => "1f1f21f1ec",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xad" => "1f1f21f1ed",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb0" => "1f1f21f1f0",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb1" => "1f1f21f1f1",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb2" => "1f1f21f1f2",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb3" => "1f1f21f1f3",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb4" => "1f1f21f1f4",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb5" => "1f1f21f1f5",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb6" => "1f1f21f1f6",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb7" => "1f1f21f1f7",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb8" => "1f1f21f1f8",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xb9" => "1f1f21f1f9",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xba" => "1f1f21f1fa",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbb" => "1f1f21f1fb",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbc" => "1f1f21f1fc",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbd" => "1f1f21f1fd",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbe" => "1f1f21f1fe",
        "\xf0\x9f\x87\xb2\xf0\x9f\x87\xbf" => "1f1f21f1ff",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xa6" => "1f1f31f1e6",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xa8" => "1f1f31f1e8",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xaa" => "1f1f31f1ea",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xab" => "1f1f31f1eb",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xac" => "1f1f31f1ec",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xae" => "1f1f31f1ee",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb1" => "1f1f31f1f1",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb4" => "1f1f31f1f4",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb5" => "1f1f31f1f5",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xb7" => "1f1f31f1f7",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xba" => "1f1f31f1fa",
        "\xf0\x9f\x87\xb3\xf0\x9f\x87\xbf" => "1f1f31f1ff",
        "\xf0\x9f\x87\xb4\xf0\x9f\x87\xb2" => "1f1f41f1f2",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xa6" => "1f1f51f1e6",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xaa" => "1f1f51f1ea",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xab" => "1f1f51f1eb",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xac" => "1f1f51f1ec",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xad" => "1f1f51f1ed",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb0" => "1f1f51f1f0",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb1" => "1f1f51f1f1",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb2" => "1f1f51f1f2",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb3" => "1f1f51f1f3",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb7" => "1f1f51f1f7",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb8" => "1f1f51f1f8",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xb9" => "1f1f51f1f9",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xbc" => "1f1f51f1fc",
        "\xf0\x9f\x87\xb5\xf0\x9f\x87\xbe" => "1f1f51f1fe",
        "\xf0\x9f\x87\xb6\xf0\x9f\x87\xa6" => "1f1f61f1e6",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xaa" => "1f1f71f1ea",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xb4" => "1f1f71f1f4",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xb8" => "1f1f71f1f8",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xba" => "1f1f71f1fa",
        "\xf0\x9f\x87\xb7\xf0\x9f\x87\xbc" => "1f1f71f1fc",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa6" => "1f1f81f1e6",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa7" => "1f1f81f1e7",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa8" => "1f1f81f1e8",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xa9" => "1f1f81f1e9",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xaa" => "1f1f81f1ea",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xac" => "1f1f81f1ec",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xad" => "1f1f81f1ed",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xae" => "1f1f81f1ee",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xaf" => "1f1f81f1ef",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb0" => "1f1f81f1f0",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb1" => "1f1f81f1f1",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb2" => "1f1f81f1f2",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb3" => "1f1f81f1f3",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb4" => "1f1f81f1f4",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb7" => "1f1f81f1f7",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb8" => "1f1f81f1f8",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xb9" => "1f1f81f1f9",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbb" => "1f1f81f1fb",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbd" => "1f1f81f1fd",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbe" => "1f1f81f1fe",
        "\xf0\x9f\x87\xb8\xf0\x9f\x87\xbf" => "1f1f81f1ff",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa6" => "1f1f91f1e6",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa8" => "1f1f91f1e8",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xa9" => "1f1f91f1e9",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xab" => "1f1f91f1eb",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xac" => "1f1f91f1ec",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xad" => "1f1f91f1ed",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xaf" => "1f1f91f1ef",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb0" => "1f1f91f1f0",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb1" => "1f1f91f1f1",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb2" => "1f1f91f1f2",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb3" => "1f1f91f1f3",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb4" => "1f1f91f1f4",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb7" => "1f1f91f1f7",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xb9" => "1f1f91f1f9",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbb" => "1f1f91f1fb",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbc" => "1f1f91f1fc",
        "\xf0\x9f\x87\xb9\xf0\x9f\x87\xbf" => "1f1f91f1ff",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xa6" => "1f1fa1f1e6",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xac" => "1f1fa1f1ec",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xb2" => "1f1fa1f1f2",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xb8" => "1f1fa1f1f8",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xbe" => "1f1fa1f1fe",
        "\xf0\x9f\x87\xba\xf0\x9f\x87\xbf" => "1f1fa1f1ff",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xa6" => "1f1fb1f1e6",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xa8" => "1f1fb1f1e8",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xaa" => "1f1fb1f1ea",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xac" => "1f1fb1f1ec",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xae" => "1f1fb1f1ee",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xb3" => "1f1fb1f1f3",
        "\xf0\x9f\x87\xbb\xf0\x9f\x87\xba" => "1f1fb1f1fa",
        "\xf0\x9f\x87\xbc\xf0\x9f\x87\xab" => "1f1fc1f1eb",
        "\xf0\x9f\x87\xbc\xf0\x9f\x87\xb8" => "1f1fc1f1f8",
        "\xf0\x9f\x87\xbd\xf0\x9f\x87\xb0" => "1f1fd1f1f0",
        "\xf0\x9f\x87\xbe\xf0\x9f\x87\xaa" => "1f1fe1f1ea",
        "\xf0\x9f\x87\xbe\xf0\x9f\x87\xb9" => "1f1fe1f1f9",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xa6" => "1f1ff1f1e6",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xb2" => "1f1ff1f1f2",
        "\xf0\x9f\x87\xbf\xf0\x9f\x87\xbc" => "1f1ff1f1fc",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa6" => "1f468200d1f468200d1f466",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "1f468200d1f468200d1f466200d1f466",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7" => "1f468200d1f468200d1f467",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "1f468200d1f468200d1f467200d1f466",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "1f468200d1f468200d1f467200d1f467",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "1f468200d1f469200d1f466200d1f466",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7" => "1f468200d1f469200d1f467",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "1f468200d1f469200d1f467200d1f466",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "1f468200d1f469200d1f467200d1f467",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x91\xa8" => "1f468200d2764fe0f200d1f468",
        "\xf0\x9f\x91\xa8\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x92\x8b\xe2\x80\x8d\xf0\x9f\x91\xa8" => "1f468200d2764fe0f200d1f48b200d1f468",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6" => "1f469200d1f469200d1f466",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa6\xe2\x80\x8d\xf0\x9f\x91\xa6" => "1f469200d1f469200d1f466200d1f466",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7" => "1f469200d1f469200d1f467",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa6" => "1f469200d1f469200d1f467200d1f466",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa9\xe2\x80\x8d\xf0\x9f\x91\xa7\xe2\x80\x8d\xf0\x9f\x91\xa7" => "1f469200d1f469200d1f467200d1f467",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x91\xa9" => "1f469200d2764fe0f200d1f469",
        "\xf0\x9f\x91\xa9\xe2\x80\x8d\xe2\x9d\xa4\xef\xb8\x8f\xe2\x80\x8d\xf0\x9f\x92\x8b\xe2\x80\x8d\xf0\x9f\x91\xa9" => "1f469200d2764fe0f200d1f48b200d1f469",
    ),
    'prefixes' => array(
        "\xc2\xa9",
        "\xc2\xae",
        "\xe2\x80",
        "\xe2\x81",
        "\xe2\x84",
        "\xe2\x86",
        "\xe2\x8c",
        "\xe2\x8f",
        "\xe2\x93",
        "\xe2\x96",
        "\xe2\x97",
        "\xe2\x98",
        "\xe2\x99",
        "\xe2\x9a",
        "\xe2\x9b",
        "\xe2\x9c",
        "\xe2\x9d",
        "\xe2\x9e",
        "\xe2\xa4",
        "\xe2\xac",
        "\xe2\xad",
        "\xe3\x80",
        "\xe3\x8a",
        "\xf0\x9f",
        "#\xe2",
        "*\xe2",
        "0\xe2",
        "1\xe2",
        "2\xe2",
        "3\xe2",
        "4\xe2",
        "5\xe2",
        "6\xe2",
        "7\xe2",
        "8\xe2",
        "9\xe2",
    ),
    'unified_rx' => '!(\\xc2\\xa9|\\xc2\\xae|\\xe2\\x80\\xbc|\\xe2\\x81\\x89|\\xe2\\x84\\xa2|\\xe2\\x84\\xb9|\\xe2\\x86\\x94|\\xe2\\x86\\x95|\\xe2\\x86\\x96|\\xe2\\x86\\x97|\\xe2\\x86\\x98|\\xe2\\x86\\x99|\\xe2\\x86\\xa9|\\xe2\\x86\\xaa|\\xe2\\x8c\\x9a|\\xe2\\x8c\\x9b|\\xe2\\x8c\\xa8|\\xe2\\x8f\\xa9|\\xe2\\x8f\\xaa|\\xe2\\x8f\\xab|\\xe2\\x8f\\xac|\\xe2\\x8f\\xad|\\xe2\\x8f\\xae|\\xe2\\x8f\\xaf|\\xe2\\x8f\\xb0|\\xe2\\x8f\\xb1|\\xe2\\x8f\\xb2|\\xe2\\x8f\\xb3|\\xe2\\x8f\\xb8|\\xe2\\x8f\\xb9|\\xe2\\x8f\\xba|\\xe2\\x93\\x82|\\xe2\\x96\\xaa|\\xe2\\x96\\xab|\\xe2\\x96\\xb6|\\xe2\\x97\\x80|\\xe2\\x97\\xbb|\\xe2\\x97\\xbc|\\xe2\\x97\\xbd|\\xe2\\x97\\xbe|\\xe2\\x98\\x80|\\xe2\\x98\\x81|\\xe2\\x98\\x82|\\xe2\\x98\\x83|\\xe2\\x98\\x84|\\xe2\\x98\\x8e|\\xe2\\x98\\x91|\\xe2\\x98\\x94|\\xe2\\x98\\x95|\\xe2\\x98\\x98|\\xe2\\x98\\x9d|\\xe2\\x98\\xa0|\\xe2\\x98\\xa2|\\xe2\\x98\\xa3|\\xe2\\x98\\xa6|\\xe2\\x98\\xaa|\\xe2\\x98\\xae|\\xe2\\x98\\xaf|\\xe2\\x98\\xb8|\\xe2\\x98\\xb9|\\xe2\\x98\\xba|\\xe2\\x99\\x88|\\xe2\\x99\\x89|\\xe2\\x99\\x8a|\\xe2\\x99\\x8b|\\xe2\\x99\\x8c|\\xe2\\x99\\x8d|\\xe2\\x99\\x8e|\\xe2\\x99\\x8f|\\xe2\\x99\\x90|\\xe2\\x99\\x91|\\xe2\\x99\\x92|\\xe2\\x99\\x93|\\xe2\\x99\\xa0|\\xe2\\x99\\xa3|\\xe2\\x99\\xa5|\\xe2\\x99\\xa6|\\xe2\\x99\\xa8|\\xe2\\x99\\xbb|\\xe2\\x99\\xbf|\\xe2\\x9a\\x92|\\xe2\\x9a\\x93|\\xe2\\x9a\\x94|\\xe2\\x9a\\x96|\\xe2\\x9a\\x97|\\xe2\\x9a\\x99|\\xe2\\x9a\\x9b|\\xe2\\x9a\\x9c|\\xe2\\x9a\\xa0|\\xe2\\x9a\\xa1|\\xe2\\x9a\\xaa|\\xe2\\x9a\\xab|\\xe2\\x9a\\xb0|\\xe2\\x9a\\xb1|\\xe2\\x9a\\xbd|\\xe2\\x9a\\xbe|\\xe2\\x9b\\x84|\\xe2\\x9b\\x85|\\xe2\\x9b\\x88|\\xe2\\x9b\\x8e|\\xe2\\x9b\\x8f|\\xe2\\x9b\\x91|\\xe2\\x9b\\x93|\\xe2\\x9b\\x94|\\xe2\\x9b\\xa9|\\xe2\\x9b\\xaa|\\xe2\\x9b\\xb0|\\xe2\\x9b\\xb1|\\xe2\\x9b\\xb2|\\xe2\\x9b\\xb3|\\xe2\\x9b\\xb4|\\xe2\\x9b\\xb5|\\xe2\\x9b\\xb7|\\xe2\\x9b\\xb8|\\xe2\\x9b\\xb9|\\xe2\\x9b\\xba|\\xe2\\x9b\\xbd|\\xe2\\x9c\\x82|\\xe2\\x9c\\x85|\\xe2\\x9c\\x88|\\xe2\\x9c\\x89|\\xe2\\x9c\\x8a|\\xe2\\x9c\\x8b|\\xe2\\x9c\\x8c|\\xe2\\x9c\\x8d|\\xe2\\x9c\\x8f|\\xe2\\x9c\\x92|\\xe2\\x9c\\x94|\\xe2\\x9c\\x96|\\xe2\\x9c\\x9d|\\xe2\\x9c\\xa1|\\xe2\\x9c\\xa8|\\xe2\\x9c\\xb3|\\xe2\\x9c\\xb4|\\xe2\\x9d\\x84|\\xe2\\x9d\\x87|\\xe2\\x9d\\x8c|\\xe2\\x9d\\x8e|\\xe2\\x9d\\x93|\\xe2\\x9d\\x94|\\xe2\\x9d\\x95|\\xe2\\x9d\\x97|\\xe2\\x9d\\xa3|\\xe2\\x9d\\xa4|\\xe2\\x9e\\x95|\\xe2\\x9e\\x96|\\xe2\\x9e\\x97|\\xe2\\x9e\\xa1|\\xe2\\x9e\\xb0|\\xe2\\x9e\\xbf|\\xe2\\xa4\\xb4|\\xe2\\xa4\\xb5|\\xe2\\xac\\x85|\\xe2\\xac\\x86|\\xe2\\xac\\x87|\\xe2\\xac\\x9b|\\xe2\\xac\\x9c|\\xe2\\xad\\x90|\\xe2\\xad\\x95|\\xe3\\x80\\xb0|\\xe3\\x80\\xbd|\\xe3\\x8a\\x97|\\xe3\\x8a\\x99|\\xf0\\x9f\\x80\\x84|\\xf0\\x9f\\x83\\x8f|\\xf0\\x9f\\x85\\xb0|\\xf0\\x9f\\x85\\xb1|\\xf0\\x9f\\x85\\xbe|\\xf0\\x9f\\x85\\xbf|\\xf0\\x9f\\x86\\x8e|\\xf0\\x9f\\x86\\x91|\\xf0\\x9f\\x86\\x92|\\xf0\\x9f\\x86\\x93|\\xf0\\x9f\\x86\\x94|\\xf0\\x9f\\x86\\x95|\\xf0\\x9f\\x86\\x96|\\xf0\\x9f\\x86\\x97|\\xf0\\x9f\\x86\\x98|\\xf0\\x9f\\x86\\x99|\\xf0\\x9f\\x86\\x9a|\\xf0\\x9f\\x88\\x81|\\xf0\\x9f\\x88\\x82|\\xf0\\x9f\\x88\\x9a|\\xf0\\x9f\\x88\\xaf|\\xf0\\x9f\\x88\\xb2|\\xf0\\x9f\\x88\\xb3|\\xf0\\x9f\\x88\\xb4|\\xf0\\x9f\\x88\\xb5|\\xf0\\x9f\\x88\\xb6|\\xf0\\x9f\\x88\\xb7|\\xf0\\x9f\\x88\\xb8|\\xf0\\x9f\\x88\\xb9|\\xf0\\x9f\\x88\\xba|\\xf0\\x9f\\x89\\x90|\\xf0\\x9f\\x89\\x91|\\xf0\\x9f\\x8c\\x80|\\xf0\\x9f\\x8c\\x81|\\xf0\\x9f\\x8c\\x82|\\xf0\\x9f\\x8c\\x83|\\xf0\\x9f\\x8c\\x84|\\xf0\\x9f\\x8c\\x85|\\xf0\\x9f\\x8c\\x86|\\xf0\\x9f\\x8c\\x87|\\xf0\\x9f\\x8c\\x88|\\xf0\\x9f\\x8c\\x89|\\xf0\\x9f\\x8c\\x8a|\\xf0\\x9f\\x8c\\x8b|\\xf0\\x9f\\x8c\\x8c|\\xf0\\x9f\\x8c\\x8d|\\xf0\\x9f\\x8c\\x8e|\\xf0\\x9f\\x8c\\x8f|\\xf0\\x9f\\x8c\\x90|\\xf0\\x9f\\x8c\\x91|\\xf0\\x9f\\x8c\\x92|\\xf0\\x9f\\x8c\\x93|\\xf0\\x9f\\x8c\\x94|\\xf0\\x9f\\x8c\\x95|\\xf0\\x9f\\x8c\\x96|\\xf0\\x9f\\x8c\\x97|\\xf0\\x9f\\x8c\\x98|\\xf0\\x9f\\x8c\\x99|\\xf0\\x9f\\x8c\\x9a|\\xf0\\x9f\\x8c\\x9b|\\xf0\\x9f\\x8c\\x9c|\\xf0\\x9f\\x8c\\x9d|\\xf0\\x9f\\x8c\\x9e|\\xf0\\x9f\\x8c\\x9f|\\xf0\\x9f\\x8c\\xa0|\\xf0\\x9f\\x8c\\xa1|\\xf0\\x9f\\x8c\\xa4|\\xf0\\x9f\\x8c\\xa5|\\xf0\\x9f\\x8c\\xa6|\\xf0\\x9f\\x8c\\xa7|\\xf0\\x9f\\x8c\\xa8|\\xf0\\x9f\\x8c\\xa9|\\xf0\\x9f\\x8c\\xaa|\\xf0\\x9f\\x8c\\xab|\\xf0\\x9f\\x8c\\xac|\\xf0\\x9f\\x8c\\xad|\\xf0\\x9f\\x8c\\xae|\\xf0\\x9f\\x8c\\xaf|\\xf0\\x9f\\x8c\\xb0|\\xf0\\x9f\\x8c\\xb1|\\xf0\\x9f\\x8c\\xb2|\\xf0\\x9f\\x8c\\xb3|\\xf0\\x9f\\x8c\\xb4|\\xf0\\x9f\\x8c\\xb5|\\xf0\\x9f\\x8c\\xb6|\\xf0\\x9f\\x8c\\xb7|\\xf0\\x9f\\x8c\\xb8|\\xf0\\x9f\\x8c\\xb9|\\xf0\\x9f\\x8c\\xba|\\xf0\\x9f\\x8c\\xbb|\\xf0\\x9f\\x8c\\xbc|\\xf0\\x9f\\x8c\\xbd|\\xf0\\x9f\\x8c\\xbe|\\xf0\\x9f\\x8c\\xbf|\\xf0\\x9f\\x8d\\x80|\\xf0\\x9f\\x8d\\x81|\\xf0\\x9f\\x8d\\x82|\\xf0\\x9f\\x8d\\x83|\\xf0\\x9f\\x8d\\x84|\\xf0\\x9f\\x8d\\x85|\\xf0\\x9f\\x8d\\x86|\\xf0\\x9f\\x8d\\x87|\\xf0\\x9f\\x8d\\x88|\\xf0\\x9f\\x8d\\x89|\\xf0\\x9f\\x8d\\x8a|\\xf0\\x9f\\x8d\\x8b|\\xf0\\x9f\\x8d\\x8c|\\xf0\\x9f\\x8d\\x8d|\\xf0\\x9f\\x8d\\x8e|\\xf0\\x9f\\x8d\\x8f|\\xf0\\x9f\\x8d\\x90|\\xf0\\x9f\\x8d\\x91|\\xf0\\x9f\\x8d\\x92|\\xf0\\x9f\\x8d\\x93|\\xf0\\x9f\\x8d\\x94|\\xf0\\x9f\\x8d\\x95|\\xf0\\x9f\\x8d\\x96|\\xf0\\x9f\\x8d\\x97|\\xf0\\x9f\\x8d\\x98|\\xf0\\x9f\\x8d\\x99|\\xf0\\x9f\\x8d\\x9a|\\xf0\\x9f\\x8d\\x9b|\\xf0\\x9f\\x8d\\x9c|\\xf0\\x9f\\x8d\\x9d|\\xf0\\x9f\\x8d\\x9e|\\xf0\\x9f\\x8d\\x9f|\\xf0\\x9f\\x8d\\xa0|\\xf0\\x9f\\x8d\\xa1|\\xf0\\x9f\\x8d\\xa2|\\xf0\\x9f\\x8d\\xa3|\\xf0\\x9f\\x8d\\xa4|\\xf0\\x9f\\x8d\\xa5|\\xf0\\x9f\\x8d\\xa6|\\xf0\\x9f\\x8d\\xa7|\\xf0\\x9f\\x8d\\xa8|\\xf0\\x9f\\x8d\\xa9|\\xf0\\x9f\\x8d\\xaa|\\xf0\\x9f\\x8d\\xab|\\xf0\\x9f\\x8d\\xac|\\xf0\\x9f\\x8d\\xad|\\xf0\\x9f\\x8d\\xae|\\xf0\\x9f\\x8d\\xaf|\\xf0\\x9f\\x8d\\xb0|\\xf0\\x9f\\x8d\\xb1|\\xf0\\x9f\\x8d\\xb2|\\xf0\\x9f\\x8d\\xb3|\\xf0\\x9f\\x8d\\xb4|\\xf0\\x9f\\x8d\\xb5|\\xf0\\x9f\\x8d\\xb6|\\xf0\\x9f\\x8d\\xb7|\\xf0\\x9f\\x8d\\xb8|\\xf0\\x9f\\x8d\\xb9|\\xf0\\x9f\\x8d\\xba|\\xf0\\x9f\\x8d\\xbb|\\xf0\\x9f\\x8d\\xbc|\\xf0\\x9f\\x8d\\xbd|\\xf0\\x9f\\x8d\\xbe|\\xf0\\x9f\\x8d\\xbf|\\xf0\\x9f\\x8e\\x80|\\xf0\\x9f\\x8e\\x81|\\xf0\\x9f\\x8e\\x82|\\xf0\\x9f\\x8e\\x83|\\xf0\\x9f\\x8e\\x84|\\xf0\\x9f\\x8e\\x85|\\xf0\\x9f\\x8e\\x86|\\xf0\\x9f\\x8e\\x87|\\xf0\\x9f\\x8e\\x88|\\xf0\\x9f\\x8e\\x89|\\xf0\\x9f\\x8e\\x8a|\\xf0\\x9f\\x8e\\x8b|\\xf0\\x9f\\x8e\\x8c|\\xf0\\x9f\\x8e\\x8d|\\xf0\\x9f\\x8e\\x8e|\\xf0\\x9f\\x8e\\x8f|\\xf0\\x9f\\x8e\\x90|\\xf0\\x9f\\x8e\\x91|\\xf0\\x9f\\x8e\\x92|\\xf0\\x9f\\x8e\\x93|\\xf0\\x9f\\x8e\\x96|\\xf0\\x9f\\x8e\\x97|\\xf0\\x9f\\x8e\\x99|\\xf0\\x9f\\x8e\\x9a|\\xf0\\x9f\\x8e\\x9b|\\xf0\\x9f\\x8e\\x9e|\\xf0\\x9f\\x8e\\x9f|\\xf0\\x9f\\x8e\\xa0|\\xf0\\x9f\\x8e\\xa1|\\xf0\\x9f\\x8e\\xa2|\\xf0\\x9f\\x8e\\xa3|\\xf0\\x9f\\x8e\\xa4|\\xf0\\x9f\\x8e\\xa5|\\xf0\\x9f\\x8e\\xa6|\\xf0\\x9f\\x8e\\xa7|\\xf0\\x9f\\x8e\\xa8|\\xf0\\x9f\\x8e\\xa9|\\xf0\\x9f\\x8e\\xaa|\\xf0\\x9f\\x8e\\xab|\\xf0\\x9f\\x8e\\xac|\\xf0\\x9f\\x8e\\xad|\\xf0\\x9f\\x8e\\xae|\\xf0\\x9f\\x8e\\xaf|\\xf0\\x9f\\x8e\\xb0|\\xf0\\x9f\\x8e\\xb1|\\xf0\\x9f\\x8e\\xb2|\\xf0\\x9f\\x8e\\xb3|\\xf0\\x9f\\x8e\\xb4|\\xf0\\x9f\\x8e\\xb5|\\xf0\\x9f\\x8e\\xb6|\\xf0\\x9f\\x8e\\xb7|\\xf0\\x9f\\x8e\\xb8|\\xf0\\x9f\\x8e\\xb9|\\xf0\\x9f\\x8e\\xba|\\xf0\\x9f\\x8e\\xbb|\\xf0\\x9f\\x8e\\xbc|\\xf0\\x9f\\x8e\\xbd|\\xf0\\x9f\\x8e\\xbe|\\xf0\\x9f\\x8e\\xbf|\\xf0\\x9f\\x8f\\x80|\\xf0\\x9f\\x8f\\x81|\\xf0\\x9f\\x8f\\x82|\\xf0\\x9f\\x8f\\x83|\\xf0\\x9f\\x8f\\x84|\\xf0\\x9f\\x8f\\x85|\\xf0\\x9f\\x8f\\x86|\\xf0\\x9f\\x8f\\x87|\\xf0\\x9f\\x8f\\x88|\\xf0\\x9f\\x8f\\x89|\\xf0\\x9f\\x8f\\x8a|\\xf0\\x9f\\x8f\\x8b|\\xf0\\x9f\\x8f\\x8c|\\xf0\\x9f\\x8f\\x8d|\\xf0\\x9f\\x8f\\x8e|\\xf0\\x9f\\x8f\\x8f|\\xf0\\x9f\\x8f\\x90|\\xf0\\x9f\\x8f\\x91|\\xf0\\x9f\\x8f\\x92|\\xf0\\x9f\\x8f\\x93|\\xf0\\x9f\\x8f\\x94|\\xf0\\x9f\\x8f\\x95|\\xf0\\x9f\\x8f\\x96|\\xf0\\x9f\\x8f\\x97|\\xf0\\x9f\\x8f\\x98|\\xf0\\x9f\\x8f\\x99|\\xf0\\x9f\\x8f\\x9a|\\xf0\\x9f\\x8f\\x9b|\\xf0\\x9f\\x8f\\x9c|\\xf0\\x9f\\x8f\\x9d|\\xf0\\x9f\\x8f\\x9e|\\xf0\\x9f\\x8f\\x9f|\\xf0\\x9f\\x8f\\xa0|\\xf0\\x9f\\x8f\\xa1|\\xf0\\x9f\\x8f\\xa2|\\xf0\\x9f\\x8f\\xa3|\\xf0\\x9f\\x8f\\xa4|\\xf0\\x9f\\x8f\\xa5|\\xf0\\x9f\\x8f\\xa6|\\xf0\\x9f\\x8f\\xa7|\\xf0\\x9f\\x8f\\xa8|\\xf0\\x9f\\x8f\\xa9|\\xf0\\x9f\\x8f\\xaa|\\xf0\\x9f\\x8f\\xab|\\xf0\\x9f\\x8f\\xac|\\xf0\\x9f\\x8f\\xad|\\xf0\\x9f\\x8f\\xae|\\xf0\\x9f\\x8f\\xaf|\\xf0\\x9f\\x8f\\xb0|\\xf0\\x9f\\x8f\\xb3|\\xf0\\x9f\\x8f\\xb4|\\xf0\\x9f\\x8f\\xb5|\\xf0\\x9f\\x8f\\xb7|\\xf0\\x9f\\x8f\\xb8|\\xf0\\x9f\\x8f\\xb9|\\xf0\\x9f\\x8f\\xba|\\xf0\\x9f\\x8f\\xbb|\\xf0\\x9f\\x8f\\xbc|\\xf0\\x9f\\x8f\\xbd|\\xf0\\x9f\\x8f\\xbe|\\xf0\\x9f\\x8f\\xbf|\\xf0\\x9f\\x90\\x80|\\xf0\\x9f\\x90\\x81|\\xf0\\x9f\\x90\\x82|\\xf0\\x9f\\x90\\x83|\\xf0\\x9f\\x90\\x84|\\xf0\\x9f\\x90\\x85|\\xf0\\x9f\\x90\\x86|\\xf0\\x9f\\x90\\x87|\\xf0\\x9f\\x90\\x88|\\xf0\\x9f\\x90\\x89|\\xf0\\x9f\\x90\\x8a|\\xf0\\x9f\\x90\\x8b|\\xf0\\x9f\\x90\\x8c|\\xf0\\x9f\\x90\\x8d|\\xf0\\x9f\\x90\\x8e|\\xf0\\x9f\\x90\\x8f|\\xf0\\x9f\\x90\\x90|\\xf0\\x9f\\x90\\x91|\\xf0\\x9f\\x90\\x92|\\xf0\\x9f\\x90\\x93|\\xf0\\x9f\\x90\\x94|\\xf0\\x9f\\x90\\x95|\\xf0\\x9f\\x90\\x96|\\xf0\\x9f\\x90\\x97|\\xf0\\x9f\\x90\\x98|\\xf0\\x9f\\x90\\x99|\\xf0\\x9f\\x90\\x9a|\\xf0\\x9f\\x90\\x9b|\\xf0\\x9f\\x90\\x9c|\\xf0\\x9f\\x90\\x9d|\\xf0\\x9f\\x90\\x9e|\\xf0\\x9f\\x90\\x9f|\\xf0\\x9f\\x90\\xa0|\\xf0\\x9f\\x90\\xa1|\\xf0\\x9f\\x90\\xa2|\\xf0\\x9f\\x90\\xa3|\\xf0\\x9f\\x90\\xa4|\\xf0\\x9f\\x90\\xa5|\\xf0\\x9f\\x90\\xa6|\\xf0\\x9f\\x90\\xa7|\\xf0\\x9f\\x90\\xa8|\\xf0\\x9f\\x90\\xa9|\\xf0\\x9f\\x90\\xaa|\\xf0\\x9f\\x90\\xab|\\xf0\\x9f\\x90\\xac|\\xf0\\x9f\\x90\\xad|\\xf0\\x9f\\x90\\xae|\\xf0\\x9f\\x90\\xaf|\\xf0\\x9f\\x90\\xb0|\\xf0\\x9f\\x90\\xb1|\\xf0\\x9f\\x90\\xb2|\\xf0\\x9f\\x90\\xb3|\\xf0\\x9f\\x90\\xb4|\\xf0\\x9f\\x90\\xb5|\\xf0\\x9f\\x90\\xb6|\\xf0\\x9f\\x90\\xb7|\\xf0\\x9f\\x90\\xb8|\\xf0\\x9f\\x90\\xb9|\\xf0\\x9f\\x90\\xba|\\xf0\\x9f\\x90\\xbb|\\xf0\\x9f\\x90\\xbc|\\xf0\\x9f\\x90\\xbd|\\xf0\\x9f\\x90\\xbe|\\xf0\\x9f\\x90\\xbf|\\xf0\\x9f\\x91\\x80|\\xf0\\x9f\\x91\\x81|\\xf0\\x9f\\x91\\x82|\\xf0\\x9f\\x91\\x83|\\xf0\\x9f\\x91\\x84|\\xf0\\x9f\\x91\\x85|\\xf0\\x9f\\x91\\x86|\\xf0\\x9f\\x91\\x87|\\xf0\\x9f\\x91\\x88|\\xf0\\x9f\\x91\\x89|\\xf0\\x9f\\x91\\x8a|\\xf0\\x9f\\x91\\x8b|\\xf0\\x9f\\x91\\x8c|\\xf0\\x9f\\x91\\x8d|\\xf0\\x9f\\x91\\x8e|\\xf0\\x9f\\x91\\x8f|\\xf0\\x9f\\x91\\x90|\\xf0\\x9f\\x91\\x91|\\xf0\\x9f\\x91\\x92|\\xf0\\x9f\\x91\\x93|\\xf0\\x9f\\x91\\x94|\\xf0\\x9f\\x91\\x95|\\xf0\\x9f\\x91\\x96|\\xf0\\x9f\\x91\\x97|\\xf0\\x9f\\x91\\x98|\\xf0\\x9f\\x91\\x99|\\xf0\\x9f\\x91\\x9a|\\xf0\\x9f\\x91\\x9b|\\xf0\\x9f\\x91\\x9c|\\xf0\\x9f\\x91\\x9d|\\xf0\\x9f\\x91\\x9e|\\xf0\\x9f\\x91\\x9f|\\xf0\\x9f\\x91\\xa0|\\xf0\\x9f\\x91\\xa1|\\xf0\\x9f\\x91\\xa2|\\xf0\\x9f\\x91\\xa3|\\xf0\\x9f\\x91\\xa4|\\xf0\\x9f\\x91\\xa5|\\xf0\\x9f\\x91\\xa6|\\xf0\\x9f\\x91\\xa7|\\xf0\\x9f\\x91\\xa8|\\xf0\\x9f\\x91\\xa9|\\xf0\\x9f\\x91\\xaa|\\xf0\\x9f\\x91\\xab|\\xf0\\x9f\\x91\\xac|\\xf0\\x9f\\x91\\xad|\\xf0\\x9f\\x91\\xae|\\xf0\\x9f\\x91\\xaf|\\xf0\\x9f\\x91\\xb0|\\xf0\\x9f\\x91\\xb1|\\xf0\\x9f\\x91\\xb2|\\xf0\\x9f\\x91\\xb3|\\xf0\\x9f\\x91\\xb4|\\xf0\\x9f\\x91\\xb5|\\xf0\\x9f\\x91\\xb6|\\xf0\\x9f\\x91\\xb7|\\xf0\\x9f\\x91\\xb8|\\xf0\\x9f\\x91\\xb9|\\xf0\\x9f\\x91\\xba|\\xf0\\x9f\\x91\\xbb|\\xf0\\x9f\\x91\\xbc|\\xf0\\x9f\\x91\\xbd|\\xf0\\x9f\\x91\\xbe|\\xf0\\x9f\\x91\\xbf|\\xf0\\x9f\\x92\\x80|\\xf0\\x9f\\x92\\x81|\\xf0\\x9f\\x92\\x82|\\xf0\\x9f\\x92\\x83|\\xf0\\x9f\\x92\\x84|\\xf0\\x9f\\x92\\x85|\\xf0\\x9f\\x92\\x86|\\xf0\\x9f\\x92\\x87|\\xf0\\x9f\\x92\\x88|\\xf0\\x9f\\x92\\x89|\\xf0\\x9f\\x92\\x8a|\\xf0\\x9f\\x92\\x8b|\\xf0\\x9f\\x92\\x8c|\\xf0\\x9f\\x92\\x8d|\\xf0\\x9f\\x92\\x8e|\\xf0\\x9f\\x92\\x8f|\\xf0\\x9f\\x92\\x90|\\xf0\\x9f\\x92\\x91|\\xf0\\x9f\\x92\\x92|\\xf0\\x9f\\x92\\x93|\\xf0\\x9f\\x92\\x94|\\xf0\\x9f\\x92\\x95|\\xf0\\x9f\\x92\\x96|\\xf0\\x9f\\x92\\x97|\\xf0\\x9f\\x92\\x98|\\xf0\\x9f\\x92\\x99|\\xf0\\x9f\\x92\\x9a|\\xf0\\x9f\\x92\\x9b|\\xf0\\x9f\\x92\\x9c|\\xf0\\x9f\\x92\\x9d|\\xf0\\x9f\\x92\\x9e|\\xf0\\x9f\\x92\\x9f|\\xf0\\x9f\\x92\\xa0|\\xf0\\x9f\\x92\\xa1|\\xf0\\x9f\\x92\\xa2|\\xf0\\x9f\\x92\\xa3|\\xf0\\x9f\\x92\\xa4|\\xf0\\x9f\\x92\\xa5|\\xf0\\x9f\\x92\\xa6|\\xf0\\x9f\\x92\\xa7|\\xf0\\x9f\\x92\\xa8|\\xf0\\x9f\\x92\\xa9|\\xf0\\x9f\\x92\\xaa|\\xf0\\x9f\\x92\\xab|\\xf0\\x9f\\x92\\xac|\\xf0\\x9f\\x92\\xad|\\xf0\\x9f\\x92\\xae|\\xf0\\x9f\\x92\\xaf|\\xf0\\x9f\\x92\\xb0|\\xf0\\x9f\\x92\\xb1|\\xf0\\x9f\\x92\\xb2|\\xf0\\x9f\\x92\\xb3|\\xf0\\x9f\\x92\\xb4|\\xf0\\x9f\\x92\\xb5|\\xf0\\x9f\\x92\\xb6|\\xf0\\x9f\\x92\\xb7|\\xf0\\x9f\\x92\\xb8|\\xf0\\x9f\\x92\\xb9|\\xf0\\x9f\\x92\\xba|\\xf0\\x9f\\x92\\xbb|\\xf0\\x9f\\x92\\xbc|\\xf0\\x9f\\x92\\xbd|\\xf0\\x9f\\x92\\xbe|\\xf0\\x9f\\x92\\xbf|\\xf0\\x9f\\x93\\x80|\\xf0\\x9f\\x93\\x81|\\xf0\\x9f\\x93\\x82|\\xf0\\x9f\\x93\\x83|\\xf0\\x9f\\x93\\x84|\\xf0\\x9f\\x93\\x85|\\xf0\\x9f\\x93\\x86|\\xf0\\x9f\\x93\\x87|\\xf0\\x9f\\x93\\x88|\\xf0\\x9f\\x93\\x89|\\xf0\\x9f\\x93\\x8a|\\xf0\\x9f\\x93\\x8b|\\xf0\\x9f\\x93\\x8c|\\xf0\\x9f\\x93\\x8d|\\xf0\\x9f\\x93\\x8e|\\xf0\\x9f\\x93\\x8f|\\xf0\\x9f\\x93\\x90|\\xf0\\x9f\\x93\\x91|\\xf0\\x9f\\x93\\x92|\\xf0\\x9f\\x93\\x93|\\xf0\\x9f\\x93\\x94|\\xf0\\x9f\\x93\\x95|\\xf0\\x9f\\x93\\x96|\\xf0\\x9f\\x93\\x97|\\xf0\\x9f\\x93\\x98|\\xf0\\x9f\\x93\\x99|\\xf0\\x9f\\x93\\x9a|\\xf0\\x9f\\x93\\x9b|\\xf0\\x9f\\x93\\x9c|\\xf0\\x9f\\x93\\x9d|\\xf0\\x9f\\x93\\x9e|\\xf0\\x9f\\x93\\x9f|\\xf0\\x9f\\x93\\xa0|\\xf0\\x9f\\x93\\xa1|\\xf0\\x9f\\x93\\xa2|\\xf0\\x9f\\x93\\xa3|\\xf0\\x9f\\x93\\xa4|\\xf0\\x9f\\x93\\xa5|\\xf0\\x9f\\x93\\xa6|\\xf0\\x9f\\x93\\xa7|\\xf0\\x9f\\x93\\xa8|\\xf0\\x9f\\x93\\xa9|\\xf0\\x9f\\x93\\xaa|\\xf0\\x9f\\x93\\xab|\\xf0\\x9f\\x93\\xac|\\xf0\\x9f\\x93\\xad|\\xf0\\x9f\\x93\\xae|\\xf0\\x9f\\x93\\xaf|\\xf0\\x9f\\x93\\xb0|\\xf0\\x9f\\x93\\xb1|\\xf0\\x9f\\x93\\xb2|\\xf0\\x9f\\x93\\xb3|\\xf0\\x9f\\x93\\xb4|\\xf0\\x9f\\x93\\xb5|\\xf0\\x9f\\x93\\xb6|\\xf0\\x9f\\x93\\xb7|\\xf0\\x9f\\x93\\xb8|\\xf0\\x9f\\x93\\xb9|\\xf0\\x9f\\x93\\xba|\\xf0\\x9f\\x93\\xbb|\\xf0\\x9f\\x93\\xbc|\\xf0\\x9f\\x93\\xbd|\\xf0\\x9f\\x93\\xbf|\\xf0\\x9f\\x94\\x80|\\xf0\\x9f\\x94\\x81|\\xf0\\x9f\\x94\\x82|\\xf0\\x9f\\x94\\x83|\\xf0\\x9f\\x94\\x84|\\xf0\\x9f\\x94\\x85|\\xf0\\x9f\\x94\\x86|\\xf0\\x9f\\x94\\x87|\\xf0\\x9f\\x94\\x88|\\xf0\\x9f\\x94\\x89|\\xf0\\x9f\\x94\\x8a|\\xf0\\x9f\\x94\\x8b|\\xf0\\x9f\\x94\\x8c|\\xf0\\x9f\\x94\\x8d|\\xf0\\x9f\\x94\\x8e|\\xf0\\x9f\\x94\\x8f|\\xf0\\x9f\\x94\\x90|\\xf0\\x9f\\x94\\x91|\\xf0\\x9f\\x94\\x92|\\xf0\\x9f\\x94\\x93|\\xf0\\x9f\\x94\\x94|\\xf0\\x9f\\x94\\x95|\\xf0\\x9f\\x94\\x96|\\xf0\\x9f\\x94\\x97|\\xf0\\x9f\\x94\\x98|\\xf0\\x9f\\x94\\x99|\\xf0\\x9f\\x94\\x9a|\\xf0\\x9f\\x94\\x9b|\\xf0\\x9f\\x94\\x9c|\\xf0\\x9f\\x94\\x9d|\\xf0\\x9f\\x94\\x9e|\\xf0\\x9f\\x94\\x9f|\\xf0\\x9f\\x94\\xa0|\\xf0\\x9f\\x94\\xa1|\\xf0\\x9f\\x94\\xa2|\\xf0\\x9f\\x94\\xa3|\\xf0\\x9f\\x94\\xa4|\\xf0\\x9f\\x94\\xa5|\\xf0\\x9f\\x94\\xa6|\\xf0\\x9f\\x94\\xa7|\\xf0\\x9f\\x94\\xa8|\\xf0\\x9f\\x94\\xa9|\\xf0\\x9f\\x94\\xaa|\\xf0\\x9f\\x94\\xab|\\xf0\\x9f\\x94\\xac|\\xf0\\x9f\\x94\\xad|\\xf0\\x9f\\x94\\xae|\\xf0\\x9f\\x94\\xaf|\\xf0\\x9f\\x94\\xb0|\\xf0\\x9f\\x94\\xb1|\\xf0\\x9f\\x94\\xb2|\\xf0\\x9f\\x94\\xb3|\\xf0\\x9f\\x94\\xb4|\\xf0\\x9f\\x94\\xb5|\\xf0\\x9f\\x94\\xb6|\\xf0\\x9f\\x94\\xb7|\\xf0\\x9f\\x94\\xb8|\\xf0\\x9f\\x94\\xb9|\\xf0\\x9f\\x94\\xba|\\xf0\\x9f\\x94\\xbb|\\xf0\\x9f\\x94\\xbc|\\xf0\\x9f\\x94\\xbd|\\xf0\\x9f\\x95\\x89|\\xf0\\x9f\\x95\\x8a|\\xf0\\x9f\\x95\\x8b|\\xf0\\x9f\\x95\\x8c|\\xf0\\x9f\\x95\\x8d|\\xf0\\x9f\\x95\\x8e|\\xf0\\x9f\\x95\\x90|\\xf0\\x9f\\x95\\x91|\\xf0\\x9f\\x95\\x92|\\xf0\\x9f\\x95\\x93|\\xf0\\x9f\\x95\\x94|\\xf0\\x9f\\x95\\x95|\\xf0\\x9f\\x95\\x96|\\xf0\\x9f\\x95\\x97|\\xf0\\x9f\\x95\\x98|\\xf0\\x9f\\x95\\x99|\\xf0\\x9f\\x95\\x9a|\\xf0\\x9f\\x95\\x9b|\\xf0\\x9f\\x95\\x9c|\\xf0\\x9f\\x95\\x9d|\\xf0\\x9f\\x95\\x9e|\\xf0\\x9f\\x95\\x9f|\\xf0\\x9f\\x95\\xa0|\\xf0\\x9f\\x95\\xa1|\\xf0\\x9f\\x95\\xa2|\\xf0\\x9f\\x95\\xa3|\\xf0\\x9f\\x95\\xa4|\\xf0\\x9f\\x95\\xa5|\\xf0\\x9f\\x95\\xa6|\\xf0\\x9f\\x95\\xa7|\\xf0\\x9f\\x95\\xaf|\\xf0\\x9f\\x95\\xb0|\\xf0\\x9f\\x95\\xb3|\\xf0\\x9f\\x95\\xb4|\\xf0\\x9f\\x95\\xb5|\\xf0\\x9f\\x95\\xb6|\\xf0\\x9f\\x95\\xb7|\\xf0\\x9f\\x95\\xb8|\\xf0\\x9f\\x95\\xb9|\\xf0\\x9f\\x96\\x87|\\xf0\\x9f\\x96\\x8a|\\xf0\\x9f\\x96\\x8b|\\xf0\\x9f\\x96\\x8c|\\xf0\\x9f\\x96\\x8d|\\xf0\\x9f\\x96\\x90|\\xf0\\x9f\\x96\\x95|\\xf0\\x9f\\x96\\x96|\\xf0\\x9f\\x96\\xa5|\\xf0\\x9f\\x96\\xa8|\\xf0\\x9f\\x96\\xb1|\\xf0\\x9f\\x96\\xb2|\\xf0\\x9f\\x96\\xbc|\\xf0\\x9f\\x97\\x82|\\xf0\\x9f\\x97\\x83|\\xf0\\x9f\\x97\\x84|\\xf0\\x9f\\x97\\x91|\\xf0\\x9f\\x97\\x92|\\xf0\\x9f\\x97\\x93|\\xf0\\x9f\\x97\\x9c|\\xf0\\x9f\\x97\\x9d|\\xf0\\x9f\\x97\\x9e|\\xf0\\x9f\\x97\\xa1|\\xf0\\x9f\\x97\\xa3|\\xf0\\x9f\\x97\\xa8|\\xf0\\x9f\\x97\\xaf|\\xf0\\x9f\\x97\\xb3|\\xf0\\x9f\\x97\\xba|\\xf0\\x9f\\x97\\xbb|\\xf0\\x9f\\x97\\xbc|\\xf0\\x9f\\x97\\xbd|\\xf0\\x9f\\x97\\xbe|\\xf0\\x9f\\x97\\xbf|\\xf0\\x9f\\x98\\x80|\\xf0\\x9f\\x98\\x81|\\xf0\\x9f\\x98\\x82|\\xf0\\x9f\\x98\\x83|\\xf0\\x9f\\x98\\x84|\\xf0\\x9f\\x98\\x85|\\xf0\\x9f\\x98\\x86|\\xf0\\x9f\\x98\\x87|\\xf0\\x9f\\x98\\x88|\\xf0\\x9f\\x98\\x89|\\xf0\\x9f\\x98\\x8a|\\xf0\\x9f\\x98\\x8b|\\xf0\\x9f\\x98\\x8c|\\xf0\\x9f\\x98\\x8d|\\xf0\\x9f\\x98\\x8e|\\xf0\\x9f\\x98\\x8f|\\xf0\\x9f\\x98\\x90|\\xf0\\x9f\\x98\\x91|\\xf0\\x9f\\x98\\x92|\\xf0\\x9f\\x98\\x93|\\xf0\\x9f\\x98\\x94|\\xf0\\x9f\\x98\\x95|\\xf0\\x9f\\x98\\x96|\\xf0\\x9f\\x98\\x97|\\xf0\\x9f\\x98\\x98|\\xf0\\x9f\\x98\\x99|\\xf0\\x9f\\x98\\x9a|\\xf0\\x9f\\x98\\x9b|\\xf0\\x9f\\x98\\x9c|\\xf0\\x9f\\x98\\x9d|\\xf0\\x9f\\x98\\x9e|\\xf0\\x9f\\x98\\x9f|\\xf0\\x9f\\x98\\xa0|\\xf0\\x9f\\x98\\xa1|\\xf0\\x9f\\x98\\xa2|\\xf0\\x9f\\x98\\xa3|\\xf0\\x9f\\x98\\xa4|\\xf0\\x9f\\x98\\xa5|\\xf0\\x9f\\x98\\xa6|\\xf0\\x9f\\x98\\xa7|\\xf0\\x9f\\x98\\xa8|\\xf0\\x9f\\x98\\xa9|\\xf0\\x9f\\x98\\xaa|\\xf0\\x9f\\x98\\xab|\\xf0\\x9f\\x98\\xac|\\xf0\\x9f\\x98\\xad|\\xf0\\x9f\\x98\\xae|\\xf0\\x9f\\x98\\xaf|\\xf0\\x9f\\x98\\xb0|\\xf0\\x9f\\x98\\xb1|\\xf0\\x9f\\x98\\xb2|\\xf0\\x9f\\x98\\xb3|\\xf0\\x9f\\x98\\xb4|\\xf0\\x9f\\x98\\xb5|\\xf0\\x9f\\x98\\xb6|\\xf0\\x9f\\x98\\xb7|\\xf0\\x9f\\x98\\xb8|\\xf0\\x9f\\x98\\xb9|\\xf0\\x9f\\x98\\xba|\\xf0\\x9f\\x98\\xbb|\\xf0\\x9f\\x98\\xbc|\\xf0\\x9f\\x98\\xbd|\\xf0\\x9f\\x98\\xbe|\\xf0\\x9f\\x98\\xbf|\\xf0\\x9f\\x99\\x80|\\xf0\\x9f\\x99\\x81|\\xf0\\x9f\\x99\\x82|\\xf0\\x9f\\x99\\x83|\\xf0\\x9f\\x99\\x84|\\xf0\\x9f\\x99\\x85|\\xf0\\x9f\\x99\\x86|\\xf0\\x9f\\x99\\x87|\\xf0\\x9f\\x99\\x88|\\xf0\\x9f\\x99\\x89|\\xf0\\x9f\\x99\\x8a|\\xf0\\x9f\\x99\\x8b|\\xf0\\x9f\\x99\\x8c|\\xf0\\x9f\\x99\\x8d|\\xf0\\x9f\\x99\\x8e|\\xf0\\x9f\\x99\\x8f|\\xf0\\x9f\\x9a\\x80|\\xf0\\x9f\\x9a\\x81|\\xf0\\x9f\\x9a\\x82|\\xf0\\x9f\\x9a\\x83|\\xf0\\x9f\\x9a\\x84|\\xf0\\x9f\\x9a\\x85|\\xf0\\x9f\\x9a\\x86|\\xf0\\x9f\\x9a\\x87|\\xf0\\x9f\\x9a\\x88|\\xf0\\x9f\\x9a\\x89|\\xf0\\x9f\\x9a\\x8a|\\xf0\\x9f\\x9a\\x8b|\\xf0\\x9f\\x9a\\x8c|\\xf0\\x9f\\x9a\\x8d|\\xf0\\x9f\\x9a\\x8e|\\xf0\\x9f\\x9a\\x8f|\\xf0\\x9f\\x9a\\x90|\\xf0\\x9f\\x9a\\x91|\\xf0\\x9f\\x9a\\x92|\\xf0\\x9f\\x9a\\x93|\\xf0\\x9f\\x9a\\x94|\\xf0\\x9f\\x9a\\x95|\\xf0\\x9f\\x9a\\x96|\\xf0\\x9f\\x9a\\x97|\\xf0\\x9f\\x9a\\x98|\\xf0\\x9f\\x9a\\x99|\\xf0\\x9f\\x9a\\x9a|\\xf0\\x9f\\x9a\\x9b|\\xf0\\x9f\\x9a\\x9c|\\xf0\\x9f\\x9a\\x9d|\\xf0\\x9f\\x9a\\x9e|\\xf0\\x9f\\x9a\\x9f|\\xf0\\x9f\\x9a\\xa0|\\xf0\\x9f\\x9a\\xa1|\\xf0\\x9f\\x9a\\xa2|\\xf0\\x9f\\x9a\\xa3|\\xf0\\x9f\\x9a\\xa4|\\xf0\\x9f\\x9a\\xa5|\\xf0\\x9f\\x9a\\xa6|\\xf0\\x9f\\x9a\\xa7|\\xf0\\x9f\\x9a\\xa8|\\xf0\\x9f\\x9a\\xa9|\\xf0\\x9f\\x9a\\xaa|\\xf0\\x9f\\x9a\\xab|\\xf0\\x9f\\x9a\\xac|\\xf0\\x9f\\x9a\\xad|\\xf0\\x9f\\x9a\\xae|\\xf0\\x9f\\x9a\\xaf|\\xf0\\x9f\\x9a\\xb0|\\xf0\\x9f\\x9a\\xb1|\\xf0\\x9f\\x9a\\xb2|\\xf0\\x9f\\x9a\\xb3|\\xf0\\x9f\\x9a\\xb4|\\xf0\\x9f\\x9a\\xb5|\\xf0\\x9f\\x9a\\xb6|\\xf0\\x9f\\x9a\\xb7|\\xf0\\x9f\\x9a\\xb8|\\xf0\\x9f\\x9a\\xb9|\\xf0\\x9f\\x9a\\xba|\\xf0\\x9f\\x9a\\xbb|\\xf0\\x9f\\x9a\\xbc|\\xf0\\x9f\\x9a\\xbd|\\xf0\\x9f\\x9a\\xbe|\\xf0\\x9f\\x9a\\xbf|\\xf0\\x9f\\x9b\\x80|\\xf0\\x9f\\x9b\\x81|\\xf0\\x9f\\x9b\\x82|\\xf0\\x9f\\x9b\\x83|\\xf0\\x9f\\x9b\\x84|\\xf0\\x9f\\x9b\\x85|\\xf0\\x9f\\x9b\\x8b|\\xf0\\x9f\\x9b\\x8c|\\xf0\\x9f\\x9b\\x8d|\\xf0\\x9f\\x9b\\x8e|\\xf0\\x9f\\x9b\\x8f|\\xf0\\x9f\\x9b\\x90|\\xf0\\x9f\\x9b\\xa0|\\xf0\\x9f\\x9b\\xa1|\\xf0\\x9f\\x9b\\xa2|\\xf0\\x9f\\x9b\\xa3|\\xf0\\x9f\\x9b\\xa4|\\xf0\\x9f\\x9b\\xa5|\\xf0\\x9f\\x9b\\xa9|\\xf0\\x9f\\x9b\\xab|\\xf0\\x9f\\x9b\\xac|\\xf0\\x9f\\x9b\\xb0|\\xf0\\x9f\\x9b\\xb3|\\xf0\\x9f\\xa4\\x90|\\xf0\\x9f\\xa4\\x91|\\xf0\\x9f\\xa4\\x92|\\xf0\\x9f\\xa4\\x93|\\xf0\\x9f\\xa4\\x94|\\xf0\\x9f\\xa4\\x95|\\xf0\\x9f\\xa4\\x96|\\xf0\\x9f\\xa4\\x97|\\xf0\\x9f\\xa4\\x98|\\xf0\\x9f\\xa6\\x80|\\xf0\\x9f\\xa6\\x81|\\xf0\\x9f\\xa6\\x82|\\xf0\\x9f\\xa6\\x83|\\xf0\\x9f\\xa6\\x84|\\xf0\\x9f\\xa7\\x80|\\x23\\xe2\\x83\\xa3|\\x2a\\xe2\\x83\\xa3|\\x30\\xe2\\x83\\xa3|\\x31\\xe2\\x83\\xa3|\\x32\\xe2\\x83\\xa3|\\x33\\xe2\\x83\\xa3|\\x34\\xe2\\x83\\xa3|\\x35\\xe2\\x83\\xa3|\\x36\\xe2\\x83\\xa3|\\x37\\xe2\\x83\\xa3|\\x38\\xe2\\x83\\xa3|\\x39\\xe2\\x83\\xa3|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xa8|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xa9|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xab|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xae|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xb1|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xb4|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xb6|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xb8|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xb9|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xba|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xbc|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xbd|\\xf0\\x9f\\x87\\xa6\\xf0\\x9f\\x87\\xbf|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xa7|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xa9|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xab|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xad|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xae|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xaf|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xb1|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xb3|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xb4|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xb6|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xb8|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xb9|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xbb|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xbc|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xbe|\\xf0\\x9f\\x87\\xa7\\xf0\\x9f\\x87\\xbf|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xa8|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xa9|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xab|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xad|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xae|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xb0|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xb1|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xb3|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xb4|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xb5|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xba|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xbb|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xbc|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xbd|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xbe|\\xf0\\x9f\\x87\\xa8\\xf0\\x9f\\x87\\xbf|\\xf0\\x9f\\x87\\xa9\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xa9\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xa9\\xf0\\x9f\\x87\\xaf|\\xf0\\x9f\\x87\\xa9\\xf0\\x9f\\x87\\xb0|\\xf0\\x9f\\x87\\xa9\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xa9\\xf0\\x9f\\x87\\xb4|\\xf0\\x9f\\x87\\xa9\\xf0\\x9f\\x87\\xbf|\\xf0\\x9f\\x87\\xaa\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xaa\\xf0\\x9f\\x87\\xa8|\\xf0\\x9f\\x87\\xaa\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xaa\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xaa\\xf0\\x9f\\x87\\xad|\\xf0\\x9f\\x87\\xaa\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xaa\\xf0\\x9f\\x87\\xb8|\\xf0\\x9f\\x87\\xaa\\xf0\\x9f\\x87\\xb9|\\xf0\\x9f\\x87\\xaa\\xf0\\x9f\\x87\\xba|\\xf0\\x9f\\x87\\xab\\xf0\\x9f\\x87\\xae|\\xf0\\x9f\\x87\\xab\\xf0\\x9f\\x87\\xaf|\\xf0\\x9f\\x87\\xab\\xf0\\x9f\\x87\\xb0|\\xf0\\x9f\\x87\\xab\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xab\\xf0\\x9f\\x87\\xb4|\\xf0\\x9f\\x87\\xab\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xa7|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xa9|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xab|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xad|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xae|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xb1|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xb3|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xb5|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xb6|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xb8|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xb9|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xba|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xbc|\\xf0\\x9f\\x87\\xac\\xf0\\x9f\\x87\\xbe|\\xf0\\x9f\\x87\\xad\\xf0\\x9f\\x87\\xb0|\\xf0\\x9f\\x87\\xad\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xad\\xf0\\x9f\\x87\\xb3|\\xf0\\x9f\\x87\\xad\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xad\\xf0\\x9f\\x87\\xb9|\\xf0\\x9f\\x87\\xad\\xf0\\x9f\\x87\\xba|\\xf0\\x9f\\x87\\xae\\xf0\\x9f\\x87\\xa8|\\xf0\\x9f\\x87\\xae\\xf0\\x9f\\x87\\xa9|\\xf0\\x9f\\x87\\xae\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xae\\xf0\\x9f\\x87\\xb1|\\xf0\\x9f\\x87\\xae\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xae\\xf0\\x9f\\x87\\xb3|\\xf0\\x9f\\x87\\xae\\xf0\\x9f\\x87\\xb4|\\xf0\\x9f\\x87\\xae\\xf0\\x9f\\x87\\xb6|\\xf0\\x9f\\x87\\xae\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xae\\xf0\\x9f\\x87\\xb8|\\xf0\\x9f\\x87\\xae\\xf0\\x9f\\x87\\xb9|\\xf0\\x9f\\x87\\xaf\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xaf\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xaf\\xf0\\x9f\\x87\\xb4|\\xf0\\x9f\\x87\\xaf\\xf0\\x9f\\x87\\xb5|\\xf0\\x9f\\x87\\xb0\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xb0\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xb0\\xf0\\x9f\\x87\\xad|\\xf0\\x9f\\x87\\xb0\\xf0\\x9f\\x87\\xae|\\xf0\\x9f\\x87\\xb0\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xb0\\xf0\\x9f\\x87\\xb3|\\xf0\\x9f\\x87\\xb0\\xf0\\x9f\\x87\\xb5|\\xf0\\x9f\\x87\\xb0\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xb0\\xf0\\x9f\\x87\\xbc|\\xf0\\x9f\\x87\\xb0\\xf0\\x9f\\x87\\xbe|\\xf0\\x9f\\x87\\xb0\\xf0\\x9f\\x87\\xbf|\\xf0\\x9f\\x87\\xb1\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xb1\\xf0\\x9f\\x87\\xa7|\\xf0\\x9f\\x87\\xb1\\xf0\\x9f\\x87\\xa8|\\xf0\\x9f\\x87\\xb1\\xf0\\x9f\\x87\\xae|\\xf0\\x9f\\x87\\xb1\\xf0\\x9f\\x87\\xb0|\\xf0\\x9f\\x87\\xb1\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xb1\\xf0\\x9f\\x87\\xb8|\\xf0\\x9f\\x87\\xb1\\xf0\\x9f\\x87\\xb9|\\xf0\\x9f\\x87\\xb1\\xf0\\x9f\\x87\\xba|\\xf0\\x9f\\x87\\xb1\\xf0\\x9f\\x87\\xbb|\\xf0\\x9f\\x87\\xb1\\xf0\\x9f\\x87\\xbe|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xa8|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xa9|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xab|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xad|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xb0|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xb1|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xb3|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xb4|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xb5|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xb6|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xb8|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xb9|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xba|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xbb|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xbc|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xbd|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xbe|\\xf0\\x9f\\x87\\xb2\\xf0\\x9f\\x87\\xbf|\\xf0\\x9f\\x87\\xb3\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xb3\\xf0\\x9f\\x87\\xa8|\\xf0\\x9f\\x87\\xb3\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xb3\\xf0\\x9f\\x87\\xab|\\xf0\\x9f\\x87\\xb3\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xb3\\xf0\\x9f\\x87\\xae|\\xf0\\x9f\\x87\\xb3\\xf0\\x9f\\x87\\xb1|\\xf0\\x9f\\x87\\xb3\\xf0\\x9f\\x87\\xb4|\\xf0\\x9f\\x87\\xb3\\xf0\\x9f\\x87\\xb5|\\xf0\\x9f\\x87\\xb3\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xb3\\xf0\\x9f\\x87\\xba|\\xf0\\x9f\\x87\\xb3\\xf0\\x9f\\x87\\xbf|\\xf0\\x9f\\x87\\xb4\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xab|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xad|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xb0|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xb1|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xb3|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xb8|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xb9|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xbc|\\xf0\\x9f\\x87\\xb5\\xf0\\x9f\\x87\\xbe|\\xf0\\x9f\\x87\\xb6\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xb7\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xb7\\xf0\\x9f\\x87\\xb4|\\xf0\\x9f\\x87\\xb7\\xf0\\x9f\\x87\\xb8|\\xf0\\x9f\\x87\\xb7\\xf0\\x9f\\x87\\xba|\\xf0\\x9f\\x87\\xb7\\xf0\\x9f\\x87\\xbc|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xa7|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xa8|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xa9|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xad|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xae|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xaf|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xb0|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xb1|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xb3|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xb4|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xb8|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xb9|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xbb|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xbd|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xbe|\\xf0\\x9f\\x87\\xb8\\xf0\\x9f\\x87\\xbf|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xa8|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xa9|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xab|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xad|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xaf|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xb0|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xb1|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xb3|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xb4|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xb7|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xb9|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xbb|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xbc|\\xf0\\x9f\\x87\\xb9\\xf0\\x9f\\x87\\xbf|\\xf0\\x9f\\x87\\xba\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xba\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xba\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xba\\xf0\\x9f\\x87\\xb8|\\xf0\\x9f\\x87\\xba\\xf0\\x9f\\x87\\xbe|\\xf0\\x9f\\x87\\xba\\xf0\\x9f\\x87\\xbf|\\xf0\\x9f\\x87\\xbb\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xbb\\xf0\\x9f\\x87\\xa8|\\xf0\\x9f\\x87\\xbb\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xbb\\xf0\\x9f\\x87\\xac|\\xf0\\x9f\\x87\\xbb\\xf0\\x9f\\x87\\xae|\\xf0\\x9f\\x87\\xbb\\xf0\\x9f\\x87\\xb3|\\xf0\\x9f\\x87\\xbb\\xf0\\x9f\\x87\\xba|\\xf0\\x9f\\x87\\xbc\\xf0\\x9f\\x87\\xab|\\xf0\\x9f\\x87\\xbc\\xf0\\x9f\\x87\\xb8|\\xf0\\x9f\\x87\\xbd\\xf0\\x9f\\x87\\xb0|\\xf0\\x9f\\x87\\xbe\\xf0\\x9f\\x87\\xaa|\\xf0\\x9f\\x87\\xbe\\xf0\\x9f\\x87\\xb9|\\xf0\\x9f\\x87\\xbf\\xf0\\x9f\\x87\\xa6|\\xf0\\x9f\\x87\\xbf\\xf0\\x9f\\x87\\xb2|\\xf0\\x9f\\x87\\xbf\\xf0\\x9f\\x87\\xbc|\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa6|\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa6\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa6|\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa7|\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa7\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa6|\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa7\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa7|\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa6\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa6|\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa7|\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa7\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa6|\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa7\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa7|\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xe2\\x9d\\xa4\\xef\\xb8\\x8f\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa8|\\xf0\\x9f\\x91\\xa8\\xe2\\x80\\x8d\\xe2\\x9d\\xa4\\xef\\xb8\\x8f\\xe2\\x80\\x8d\\xf0\\x9f\\x92\\x8b\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa8|\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa6|\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa6\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa6|\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa7|\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa7\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa6|\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa7\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa7|\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xe2\\x9d\\xa4\\xef\\xb8\\x8f\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa9|\\xf0\\x9f\\x91\\xa9\\xe2\\x80\\x8d\\xe2\\x9d\\xa4\\xef\\xb8\\x8f\\xe2\\x80\\x8d\\xf0\\x9f\\x92\\x8b\\xe2\\x80\\x8d\\xf0\\x9f\\x91\\xa9)(\\xEF\\xB8\\x8E|\\xEF\\xB8\\x8F)?!',
);

$GLOBALS['emoji_maps']['html_to_unified'] = array_flip($GLOBALS['emoji_maps']['unified_to_html']);


#
# functions to convert incoming data into the unified format
#

	function emoji_docomo_to_unified($text) {
    return emoji_convert($text, 'docomo_to_unified');
}

function emoji_kddi_to_unified($text) {
    return emoji_convert($text, 'kddi_to_unified');
}

function emoji_softbank_to_unified($text) {
    return emoji_convert($text, 'softbank_to_unified');
}

function emoji_google_to_unified($text) {
    return emoji_convert($text, 'google_to_unified');
}

#
# functions to convert unified data into an outgoing format

#

	function emoji_unified_to_docomo($text) {
    return emoji_convert($text, 'unified_to_docomo');
}

function emoji_unified_to_kddi($text) {
    return emoji_convert($text, 'unified_to_kddi');
}

function emoji_unified_to_softbank($text) {
    return emoji_convert($text, 'unified_to_softbank');
}

function emoji_unified_to_google($text) {
    return emoji_convert($text, 'unified_to_google');
}

#
# HTML transformation

#

	function emoji_unified_to_html($text) {
    return preg_replace_callback($GLOBALS['emoji_maps']['unified_rx'], function($m) {
        if (isset($m[2]) && $m[2] == "\xEF\xB8\x8E")
            return $m[0];
        $cp = $GLOBALS['emoji_maps']['unified_to_html'][$m[1]];
        return "<span class=\"emoji-outer emoji-sizer\"><span class=\"emoji-inner emoji{$cp}\"></span></span>";
    }, $text);
}

function emoji_html_to_unified($text) {
    return preg_replace_callback("!<span class=\"emoji-outer emoji-sizer\"><span class=\"emoji-inner emoji([0-9a-f]+)\"></span></span>!", function($m) {
        if (isset($GLOBALS['emoji_maps']['html_to_unified'][$m[1]])) {
            return $GLOBALS['emoji_maps']['html_to_unified'][$m[1]];
        }
        return $m[0];
    }, $text);
}

function emoji_convert($text, $map) {

    return str_replace(array_keys($GLOBALS['emoji_maps'][$map]), $GLOBALS['emoji_maps'][$map], $text);
}

function emoji_get_name($unified_cp) {

    return $GLOBALS['emoji_maps']['names'][$unified_cp] ? $GLOBALS['emoji_maps']['names'][$unified_cp] : '?';
}

function emoji_contains_emoji($text) {

    $count = 0;
    str_replace($GLOBALS['emoji_maps']['prefixes'], '00', $text, $count);
    return $count > 0;
}

if (!function_exists('is_valid_url')) {

    function is_valid_url($url) {
        return (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://') ? $url : 'http://' . $url;
    }

}

if (!function_exists('getPresenceFromConfig')) {

    function getPresenceFromConfig($Item) {
        $Items = array('NOT_ATTENDING' => 'Not Attending', 'INVITED' => 'Invited', 'ARRIVED' => 'Arrived', 'MAY_BE' => 'May Be', 'ATTENDING' => 'Attending');
        if (!empty($Item)) {
            if (array_key_exists($Item, $Items)) {
                return $Items[$Item];
            }
        }
    }

}

function generateVersionLink($ReleaseVersion) {
    if (!file_exists(ROOT_PATH . '/' . $ReleaseVersion)) {
        array_map('unlink', glob(ROOT_PATH . '/' . strstr($ReleaseVersion, '.', true) . '.*'));
        symlink(ROOT_PATH, $ReleaseVersion);
        $CI = & get_instance();
        $CI->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
        $CI->cache->clean();
    }
}

if (!function_exists('get_valid_url_str')) {

    function get_valid_url_str($url) {
        $url = preg_replace('~[^\\pL0-9_]+~u', '-', $url);
        $url = trim($url, "-");
        $url = iconv("utf-8", "us-ascii//TRANSLIT", $url);
        $url = strtolower($url);
        $url = preg_replace('~[^-a-z0-9_]+~', '', $url);
        return $url;
    }

}



if (!function_exists('convert_to_numeric_arr')) {

    function convert_to_numeric_arr($arr = []) {
        $temp_arr = $arr;
        $arr = [];
        foreach($temp_arr as $item) {
            if(is_numeric($item)) {
                $arr[] = $item;
            }
        }
        return $arr;
    }

}

if (!function_exists('beliefmedia_ordinal')) {
    function beliefmedia_ordinal($cardinal) { 
      $test_c = abs($cardinal) % 10; 
      $extension = ((abs($cardinal) %100 < 21 && abs($cardinal) %100 > 4) ? 'th' : (($test_c < 4) ? ($test_c < 3) ? ($test_c < 2) ? ($test_c < 1) ? 'th' : 'st' : 'nd' : 'rd' : 'th')); 
     return $cardinal . $extension; 
    }
}

if (!function_exists('generate_password')) {
    function generate_password($password) { 
        $options = [
            'cost' => 12
        ];
        $hash = password_hash($password, PASSWORD_BCRYPT, $options);
        return $hash;
    }
}

/**
 * send msg91 ms
 * @param array $post_data
 * @return array
 */
if (!function_exists('send_msg91_sms')) {

    function send_msg91_sms($post_data = array()) {
        $url = MSG91_API_BASE_URL . "api/sendhttp.php";
        $post_array = array(
            "route" => MSG91_ROUTE_ID,
            "sender" => MSG91_SENDER_ID,
            "authkey" => MSG91_AUTH_KEY,
            "country" => DEFAULT_PHONE_CODE,
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
            log_message('error', 'MSG91 Error: '. $err);
            return array("response" => $err);
        } else {
            return $response;
        }
    }

}
