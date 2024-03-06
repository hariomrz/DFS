<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 * This Class used for Login API
 * (All THE API CAN BE USED THROUGH POST METHODS)
 * @package   CodeIgniter
 * @subpackage  Rest Server
 * @category  Controller
 * @author    Vinfotech Team
 */
class Login extends Common_API_Controller {

    function __construct() {
        parent::__construct();
    }

    function check_apk_ver_post() {
        $data = $this->post_data;
        $return = $this->return;

        $version = isset($data['current_ver']) ? $data['current_ver'] : '';
        $device_type = isset($data['device_type']) ? $data['device_type'] : '';

        $response['clean_cache'] = 0; //
        $response['image_name'] = 'version11.jpg'; // show this image in update popup
        $response['upgrade_required'] = 0;
        //$response['upgrade_optional'] = 0;
        $response['apk_url'] = 0;
        $response['device_type'] = $device_type;
        $response['upgrade_required_msg'] = "अपना टैलेंट इंदौर को दिखाने के लिए और टैलंटेड इंदौरवासियों को Follow करने के लिए ऐप अपडेट करें"; //अपना टैलेंट इंदौर को दिखाने के लिए और टैलंटेड इंदौरवासियों को Follow करने के लिए ऐप अपडेट करें
        //$response['upgrade_optional_msg'] = "दिनभर इंदौर न्यूज़ के लिए भोपू तुरंत अपडेट करे "; //इस नवरात्री गरबे में झूम \nनए भोपू से अब बढ़ी धूम

        if ($device_type == 1) {
            $app_version = ANDROID_VERSION;
        } elseif ($device_type == 2) {
            $app_version = IOS_VERSION;
        }

        $response['latest_version'] = $app_version;
        if (!empty($version) && $version < $app_version) {
            /* if (($device_type == 1 && $version < 9.1) || ($device_type == 2 && $version < 4.1)) {
                $response['upgrade_required'] = 3;                
            } else{
               // $response['upgrade_required'] = UPGRADE_REQUIRED;
            } 
            */     
            $response['upgrade_required'] = UPGRADE_REQUIRED;      
        }
        $return['Data'] = $response;
        $this->response($return);
        exit;
    }

    function master_data_post() {
        $return = $this->return;

        $data['burl'] = getenv('BUCKET_URL');
        $return['Data'] = $data;
        $this->response($return);
    }

}
