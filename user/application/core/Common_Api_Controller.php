<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
/**
 * Base class for all rest api
 * @package Core
 * @category Rest
 */
class Common_Api_Controller extends REST_Controller {

    public $data = array();
    public $headers = array();
    public $email = FALSE;
    public $user_id = FALSE;
    public $language = FALSE;
    public $lang_abbr = FALSE;
    public $user_unique_id = FALSE;
    public $referral_code = FALSE;
    public $bs_status = "";
    public $referral_bonus_source = array();
    public $api_response_arry = array(
        "response_code" => rest_controller::HTTP_OK,
        "service_name" => "",
        "message" => "",
        "global_error" => "",
        "error" => array(),
        "data" => array()
    );
    public $send_email_otp = REQUIRE_OTP;
    public $auth_key;
    public $auth_key_role;

    public function __construct() {
        parent::__construct();
        $_POST = $this->post();
        //set service name
        $method = ($this->router->method == 'index') ? NULL : '/' . $this->router->method;
        $this->api_response_arry['service_name'] = $this->router->class . $method;

        $this->_check_cors();
        
        
        $this->referral_bonus_source = array(
            'ub_1' => 56,
            'ur_1' => 57,
            'uc_1' => 58,
            'ba_1' => 53,
            'ra_1' => 54,
            'ca_1' => 55,            
            'ub_6' => 50,
            'ur_6' => 51,
            'uc_6' => 52,
            'ra_6' => 320,            
            'ub_7' => 86,
            'ur_7' => 87,
            'uc_7' => 88,            
            'ba_7' => 89,
            'ra_7' => 90,
            'ca_7' => 91,            
            'ub_13' => 86,
            'ur_13' => 87,
            'uc_13' => 88,
            'ba_13' => 89,
            'ra_13' => 90,
            'ca_13' => 91,
            'ub_14' => 105,
            'ur_14' => 106,
            'uc_14' => 107,
            'ba_14' => 98,
            'ra_14' => 99,
            'ca_14' => 100,
            'ub_15' => 95,
            'ur_15' => 96,
            'uc_15' => 97,
            'ub_18' => 153,
            'ur_18' => 154,
            'uc_18' => 155,
            'ub_19' => 56,
            'ur_19' => 57,
            'uc_19' => 58,
            'ba_19' => 156,
            'ra_19' => 157,
            'ca_19' => 158,
            'ub_20' => 56,
            'ur_20' => 57,
            'uc_20' => 58,
            'ba_20' => 159,
            'ra_20' => 160,
            'ca_20' => 161,
            'ub_21' => 56,
            'ur_21' => 57,
            'uc_21' => 58,
            'ba_21' => 162,
            'ra_21' => 163,
            'ca_21' => 164,
            'ub_8' => 165,
            'ur_8' => 166,
            'uc_8' => 167,
            'ub_4' => 171,
            'ur_4' => 172,
            'uc_4' => 173,
            'ba_4' => 168,
            'ra_4' => 169,
            'ca_4' => 170,
        );

        //securiy xss clean
        $_POST = $this->security->xss_clean($_POST, TRUE);
        if (isset($_POST['page_no'])) {
            $_POST['page_no'] = (int)($_POST['page_no']) ? $_POST['page_no'] : 1;
        }
        if (isset($_POST['page_size'])) {
            $_POST['page_size'] = (int)($_POST['page_size']) ? $_POST['page_size'] : RECORD_LIMIT;
        }

        $this->custom_auth_override = $this->_custom_auth_override_check();
        $this->auth_key = $this->input->get_request_header(AUTH_KEY);
        //this condition for uc browser lowecase issue
        if (!$this->auth_key) {
            $this->auth_key = $this->input->get_request_header(strtolower(AUTH_KEY));
        }
        //this condition for old session key
        if (!$this->auth_key) {
            $this->auth_key = $this->input->get_request_header(PREVIOUS_AUTH_KEY);
        }
        if(!$this->auth_key && $method == "/get_tds_report"){
            $this->auth_key = $this->input->get(AUTH_KEY);
        }
        if(!$this->auth_key && $method == "/gst_invoice_download"){
            $this->auth_key = $this->input->get(AUTH_KEY);
        }
        if(!$this->auth_key && $method == "/get_gst_report"){
            $this->auth_key = $this->input->get(AUTH_KEY);
        }
        $this->headers[AUTH_KEY] = $this->auth_key;

        if (!empty($this->input->get_request_header(AUTH_KEY_ROLE))) {
            $this->auth_key_role = $this->input->get_request_header(AUTH_KEY_ROLE);
            $this->headers[AUTH_KEY_ROLE] = $this->auth_key_role;
        }

        $this->get_app_config_data();   

        //validate api request
        $this->validate_api_request();

        $this->set_lang();
        //Do your magic here
        if ($this->custom_auth_override === FALSE || $this->auth_key) {
            $this->_custom_prepare_basic_auth();
            
        }

        //validate banned state request
        $this->validate_banned_state();
    }

    /**
     * check cors data
     * @return boolean
     */
    protected function _check_cors() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization," . AUTH_KEY.",Version,Apiversion,User-Token,Device,RequestTime,Cookie,_ga_token,X-RefID,Ult,loc_check");
        // If the request HTTP method is 'OPTIONS', kill the response and send it to the client
        if ($this->input->method() === 'options') {
            exit;
        }

        $this->validate_cors_request();
    }

    /**
     * this function used for validate and block cors request
     * @return boolean
     */
    public function validate_cors_request(){
        if(ALLOW_CORS != "1"){
            $domain_name = WEBSITE_DOMAIN;
            $http_prtcl = HTTP_PROTOCOL;
            $http_prtcl2 = "https";
            if($http_prtcl == "https"){
                $http_prtcl2 = "http";
            }
            $origin_arr = array();
            $origin_arr[] = $http_prtcl."://".$domain_name;
            $origin_arr[] = $http_prtcl2."://".$domain_name;
            if (strpos($domain_name, 'www') !== false) {
                $domain_name = str_replace("www.","",$domain_name);
                $origin_arr[] = $http_prtcl."://".$domain_name;
                $origin_arr[] = $http_prtcl2."://".$domain_name;
            }else{
                $origin_arr[] = $http_prtcl."://www.".$domain_name;
                $origin_arr[] = $http_prtcl2."://www.".$domain_name;
            }
            if(!in_array($_SERVER['HTTP_ORIGIN'], $origin_arr)){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = "Sorry, your are not allowed for this activity.";
                $this->api_response();
            }
        }
        return true;
    }

    private function validate_api_request(){
        $api_check = isset($this->app_config['api_check'])?$this->app_config['api_check']['key_value']:0;
        if($api_check == "1"){
            $app_data = isset($this->app_config['api_check']['custom_data']) ? $this->app_config['api_check']['custom_data'] : array();
            if(empty($app_data)){
                return true;
            }
            
            $class_name = $this->router->class;
            $method_name = $this->router->method;
            $exclude_list = get_exclude_class_methods();
            if(in_array($class_name,$exclude_list['class']) || in_array($class_name.'/'.$method_name,$exclude_list['method'])){
                return true;
            }

            $current_time = strtotime(format_date());
            $key = $app_data['key'];
            $format = $app_data['format'];
            $valid_time = isset($app_data['valid_time']) ? $app_data['valid_time'] : 5;
            $origin = isset($app_data['origin']) ? $app_data['origin'] : "";
            $format_arr = explode("_",$format);
            $user_token = $this->input->get_request_header('User-Token');
            $content = $this->input->get_request_header('Content-Length');
            $cookie_token = $this->input->get_request_header('_ga_token');
            $timestamp = $this->input->get_request_header('RequestTime');
            $UserRefID = $this->input->get_request_header('X-RefID');
            $timestamp = strtotime($timestamp);
            $data_type = ($content % 2);
            if($data_type == "1"){
                $user_token = $user_token.$cookie_token;
            }else{
                $user_token = $cookie_token.$user_token;
            }

            $reqArr = array();
            $reqArr['key'] = md5($key);
            $reqArr['refid'] = $UserRefID;
            $reqArr['content'] = $content;
            $reqArr['language'] = $this->input->get_request_header('Accept-Language');
            $reqArr['origin'] = $this->input->get_request_header('Origin');
            $reqArr['timestamp'] = $timestamp;
            $token_fields = array();
            foreach($format_arr as $field_key){
                $token_fields[] = isset($reqArr[strtolower($field_key)]) ? $reqArr[strtolower($field_key)] : "";
            }
            $data_token = md5(implode("_",$token_fields));
            if($data_token != $user_token){
                $this->api_response_arry['response_code'] = 403;
                $this->api_response_arry['message'] = "Sorry!! Invalid request.";
                $this->api_response();
            }

            $time_diff = $current_time - $timestamp;
            if($time_diff < 0 || $time_diff > $valid_time){
                $this->api_response_arry['response_code'] = 403;
                $this->api_response_arry['message'] = "Sorry!! Request timeout.";
                $this->api_response();
            }

            if($origin != "" && $origin != "*"){
                $reqArr['origin'] = trim($reqArr['origin'],"/");
                $origin_arr = array("http://".$origin,"https://".$origin,"http://www.".$origin,"https://www.".$origin);
                if(!in_array($reqArr['origin'],$origin_arr)){
                    $this->api_response_arry['response_code'] = 403;
                    $this->api_response_arry['message'] = "Sorry!! Invalid request type.";
                    $this->api_response();
                }
            }
        }
        return true;
    }

    private function validate_banned_state(){
        $allow_bs = isset($this->app_config['allow_bs'])?$this->app_config['allow_bs']['key_value']:0;
        if($allow_bs == "1" && $this->user_id){
            $app_data = isset($this->app_config['allow_bs']['custom_data']) ? $this->app_config['allow_bs']['custom_data'] : array();

            if(empty($app_data)){
                return true;
            }

            //print_r($app_data);
            $class_name = $this->router->class;
            $method_name = $this->router->method;
            $exclude_list = get_exclude_class_methods();
            $exclude_list['class'][]  = 'auth';
            $exclude_list['class'][]  = 'emailauth';
            $exclude_list['method'][] = 'profile/update_profile_data';
            $exclude_list['method'][] = 'emailauth/update_profile_data';
            if(in_array($class_name,$exclude_list['class']) || in_array($class_name.'/'.$method_name,$exclude_list['method'])){
                return true;
            }
            //check location only when header parameter is 1
            $loc_check = $this->input->get_request_header('loc_check');
            if(empty($loc_check) && !in_array($method_name, ['deposit','withdraw'])){
                return true;
            }

            $user_location = $this->input->get_request_header('Ult');
            $loc_query = get_user_ip_address();
            if($user_location){
                $loc_query = base64_decode($user_location);
            }
            //$loc_query = '103.15.66.178';

            $api_key = isset($app_data['api_key']) ? $app_data['api_key'] : "";
            $country_code = isset($app_data['country_code_allowed']) ? $app_data['country_code_allowed'] : "";
            $state_cache_key = 'banned_state';
            $state_code = $this->get_cache_data($state_cache_key);
            if (!$state_code) {
                $state_code = $this->get_banned_state();
                $this->set_cache_data($state_cache_key, $state_code, REDIS_30_DAYS);
            }

            $api_arr = array("key"=>$api_key,"query"=>$loc_query);
            $result  = validate_location_api($api_arr);
             if(!empty($result['data']) && isset($result['data']['0']['region_code']) && isset($result['data']['0']['country_code'])){
                $user_country = $result['data']['0']['country_code'];
                $user_state = $result['data']['0']['region_code'];

                $state_country_id_cache_key = 'state_country_id';
                $ct_st_ids = $this->get_cache_data($state_country_id_cache_key);
                if(!$ct_st_ids){
                    $this->load->model("auth/Auth_model");
                    $ct_st_ids = $this->Auth_model->get_country_state_ids();
                    $this->set_cache_data($state_country_id_cache_key, $ct_st_ids, REDIS_30_DAYS);
                }
                $country_id  =  $ct_st_ids[array_search($user_country, array_column($ct_st_ids, 'country_code'))]['country_id'];
                $state_id    =  $ct_st_ids[array_search($user_state, array_column($ct_st_ids, 'state_code'))]['state_id'];
                $country_code = explode(',', $country_code);
                $state_code = array_column($state_code, 'pos_code');

                $this->api_response_arry['header']['Access-Control-Expose-Headers'] = 'banned_cs';
                $this->api_response_arry['header']['banned_cs'] = $country_id.'_'.$state_id;
                if($app_data['site_access'] == 1  &&  !in_array($method_name, ['deposit','withdraw'])){
                    //Do nothing
                }elseif(!in_array($country_id ,$country_code) ||  in_array($user_state, $state_code) ){
                    $this->api_response_arry['response_code'] = 403;
                    $this->api_response();
                }
            }
            
        }

        return true;
    }

    /**
     * set application language based of user request
     * @return boolean
     */
    public function set_lang($lang = FALSE) {
        $language_list = $this->config->item('language_list');
        if (!$lang) {
            $header_language = $this->input->get_request_header('Accept-Language');

            if ($header_language && isset($language_list[$header_language])) {
                $lang = $language_list[$header_language];
            } else {
                $lang = $this->config->item('language');
            }
        } else {
            if ($lang && isset($language_list[$lang])) {
                $lang = $language_list[$lang];
            } else if ($lang && in_array($lang, $language_list)) {
                $lang = trim($lang);
            } else {
                $lang = $this->config->item('language');
            }
        }

        $this->language = $lang;
        $this->lang_abbr = array_search($lang, $language_list);
        $this->config->set_item('language', $this->language);
        $this->lang->load('general', $this->language);
        $this->lang->load('form_validation', $this->language, TRUE);
        return TRUE;
    }

    /**
     * Retrieve the validation errors array and send as response.
     * @return none
     */
    public function send_validation_errors($return_only = FALSE) {
        $errors = $this->form_validation->error_array();
        $return['response_code'] = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
        $return['error'] = $errors;
        $return['service_name'] = '';
        $return['message'] = '';
        $return['global_error'] = '';
        $return['data'] = '';

        if (!$this->input->post()) {
            $return['global_error'] = $this->lang->line('input_invalid_format');
        }

        if ($return_only === TRUE) {
            return $return;
        }

        $this->response($return, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Check if there is a specific auth type set for the current class/method/HTTP-method being called
     *
     * @access protected
     * @return bool
     */
    protected function _custom_auth_override_check() {
        // Assign the class/method auth type override array from the config
        $auth_override_class_method = $this->config->item('auth_override_class_method');

        // Check to see if the override array is even populated
        if (!empty($auth_override_class_method)) {
            // check for wildcard flag for rules for classes
            if (!empty($auth_override_class_method[$this->router->class]['*'])) { // Check for class overrides
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method[$this->router->class]['*'] === 'none') {
                    return TRUE;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method[$this->router->class]['*'] === 'custom') {
                    $this->_custom_prepare_basic_auth();

                    return TRUE;
                }
            }

            // Check to see if there's an override value set for the current class/method being called
            if (!empty($auth_override_class_method[$this->router->class][$this->router->method])) {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'none') {
                    return TRUE;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'custom') {
                    $this->_custom_prepare_basic_auth();

                    return TRUE;
                }
            }
        }

        // Assign the class/method/HTTP-method auth type override array from the config
        $auth_override_class_method_http = $this->config->item('auth_override_class_method_http');

        // Check to see if the override array is even populated
        if (!empty($auth_override_class_method_http)) {
            // check for wildcard flag for rules for classes
            if (!empty($auth_override_class_method_http[$this->router->class]['*'][$this->request->method])) {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'none') {
                    return TRUE;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'custom') {
                    $this->_custom_prepare_basic_auth();

                    return TRUE;
                }
            }

            // Check to see if there's an override value set for the current class/method/HTTP-method being called
            if (!empty($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method])) {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'none') {
                    return TRUE;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'custom') {
                    $this->_custom_prepare_basic_auth();

                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    function get_app_config_data()
    {
        //check if affiliate master entry availalbe for email verify bonus w/o referral
        $app_config_cache_key = 'app_config';
        $data = $this->get_cache_data($app_config_cache_key);
        if (!$data) {
            $this->load->model("auth/Auth_model");
            $result = $this->Auth_model->get_all_table_data(APP_CONFIG,"*");
    
            foreach($result as &$row)
            {
                if(!empty($row['custom_data']))
                {
                    $row['custom_data'] = json_decode($row['custom_data'],TRUE);
                }
            }

            $data = array_column($result,NULL,'key_name');
    
            $this->set_cache_data($app_config_cache_key, $data, REDIS_30_DAYS);
        }
       
        $this->app_config = $data;
        $this->define_app_constant();
        
    }

    /**
     * @method define_app_constant
     * @uses this method defines constant from app config variable
     * @since Jan 2021
     * @param NA
    */
    function define_app_constant()
    {
        $site_title = isset($this->app_config['site_title'])?$this->app_config['site_title']['key_value']:'Fantasy Sports';
        define('SITE_TITLE', $site_title);

        $coins_balance_claim = isset($this->app_config['coins_balance_claim'])?$this->app_config['coins_balance_claim']['key_value']:'';
        define('COINS_BALANCE_CLAIM', $coins_balance_claim);

        $fb_link = isset($this->app_config['fb_link'])?$this->app_config['fb_link']['key_value']:'';
        define('FB_LINK', $fb_link);

        $twitter_link = isset($this->app_config['twitter_link'])?$this->app_config['twitter_link']['key_value']:'';
        define('TWITTER_LINK', $twitter_link);

        $instagram_link = isset($this->app_config['instagram_link'])?$this->app_config['instagram_link']['key_value']:'';
        define('INSTAGRAM_LINK', $instagram_link);

        $report_admin_email = isset($this->app_config['report_admin_email'])?$this->app_config['report_admin_email']['key_value']:'';
        define('REPORT_ADMIN_EMAIL', $report_admin_email);

        $fcm_key = isset($this->app_config['fcm_key'])?$this->app_config['fcm_key']['key_value']:'';
        define('FCM_KEY', $fcm_key);
        
        //all languages
        $allow_english = isset($this->app_config['allow_english'])?$this->app_config['allow_english']['key_value']:0;
        $allow_hindi = isset($this->app_config['allow_hindi'])?$this->app_config['allow_hindi']['key_value']:0;
        $allow_gujrati = isset($this->app_config['allow_gujrati'])?$this->app_config['allow_gujrati']['key_value']:0;
        $allow_french = isset($this->app_config['allow_french'])?$this->app_config['allow_french']['key_value']:0;
        $allow_bengali = isset($this->app_config['allow_bengali'])?$this->app_config['allow_bengali']['key_value']:0;
        $allow_punjabi = isset($this->app_config['allow_punjabi'])?$this->app_config['allow_punjabi']['key_value']:0;
        $allow_tamil = isset($this->app_config['allow_tamil'])?$this->app_config['allow_tamil']['key_value']:0;
        $allow_thai = isset($this->app_config['allow_thai'])?$this->app_config['allow_thai']['key_value']:0;
        $allow_russian = isset($this->app_config['allow_russian'])?$this->app_config['allow_russian']['key_value']:0;
        $allow_indonesian = isset($this->app_config['allow_indonesian'])?$this->app_config['allow_indonesian']['key_value']:0;
		$allow_tagalog = isset($this->app_config['allow_tagalog'])?$this->app_config['allow_tagalog']['key_value']:0;
		$allow_chinese = isset($this->app_config['allow_chinese'])?$this->app_config['allow_chinese']['key_value']:0;
		$allow_kannada = isset($this->app_config['allow_kannada'])?$this->app_config['allow_kannada']['key_value']:0;
		$allow_russian = isset($this->app_config['allow_russian'])?$this->app_config['allow_russian']['key_value']:0;
		$allow_indonesian = isset($this->app_config['allow_indonesian'])?$this->app_config['allow_indonesian']['key_value']:0;
		$allow_tagalog = isset($this->app_config['allow_tagalog'])?$this->app_config['allow_tagalog']['key_value']:0;
		$allow_chinese = isset($this->app_config['allow_chinese'])?$this->app_config['allow_chinese']['key_value']:0;
        $allow_spanish = isset($this->app_config['allow_spanish'])?$this->app_config['allow_spanish']['key_value']:0;
      
        $language_list = array();
        $app_language_list = array();
        if($allow_english == 1){
            $language_list['en'] = 'english';
            $app_language_list['en'] = 'English';
        }
        if($allow_hindi == 1){
            $language_list['hi'] = 'hindi';
            $app_language_list['hi'] = 'हिंदी';
        }
        if($allow_gujrati == 1){
            $language_list['guj'] = 'gujrati';
            $app_language_list['guj'] = 'ગુજ્રાતી';
        }
        if($allow_french == 1){
            $language_list['fr'] = 'french';
            $app_language_list['fr'] = 'Français';
        }
        if($allow_bengali == 1){
            $language_list['ben'] = 'bengali';
            $app_language_list['ben'] = 'বাংলা';
        }
        if($allow_punjabi == 1){
            $language_list['pun'] = 'punjabi';
            $app_language_list['pun'] = 'ਪੰਜਾਬੀ';
        }
        if($allow_tamil == 1){
            $language_list['tam'] = 'tamil';
            $app_language_list['tam'] = 'தமிழ்';
        }
        if($allow_thai == 1){
            $language_list['th'] = 'thai';
            $app_language_list['th'] = 'ไทย';
        }
        if($allow_russian == 1){
            $language_list['ru'] = 'russian';
            $app_language_list['ru'] = 'Rusia';
        }
        if($allow_indonesian == 1){
            $language_list['id'] = 'indonesian';
            $app_language_list['id'] = 'Indonesia';
		}
		if($allow_tagalog == 1){
            $language_list['tl'] = 'tagalog';
            $app_language_list['tl'] = 'tagalog';
		}
		if($allow_chinese == 1){
            $language_list['zh'] = 'chinese';
            $app_language_list['zh'] = '中国人';
		}
        if($allow_kannada == 1){
            $language_list['kn'] = 'kannada';
            $app_language_list['kn'] = 'ಕನ್ನಡ';
        }
        if($allow_russian == 1){
            $language_list['ru'] = 'russian';
            $app_language_list['ru'] = 'Rusia';
        }
        if($allow_indonesian == 1){
            $language_list['id'] = 'indonesian';
            $app_language_list['id'] = 'Indonesia';
		}
		if($allow_tagalog == 1){
            $language_list['tl'] = 'tagalog';
            $app_language_list['tl'] = 'tagalog';
		}
		if($allow_chinese == 1){
            $language_list['zh'] = 'chinese';
            $app_language_list['zh'] = '中国人';
		}
        if($allow_spanish == 1){
            $language_list['es'] = 'spanish';
            $app_language_list['es'] = 'española';
        }


        define('LANGUAGE_LIST',serialize($language_list));
        define('APP_LANGUAGE_LIST',serialize($app_language_list));

        $this->config->set_item('language_list',$language_list);

       
        $config_app_language_list= array();

        foreach($app_language_list as $key => $value)
        {
            $config_app_language_list[] = array("value"=>$key,"label"=>$value);
        }
        $this->config->set_item('app_language_list',$config_app_language_list);

        $android_app = isset($this->app_config['android_app']['key_value'])?$this->app_config['android_app']['custom_data']:0;
        if(!empty($android_app))
        {
            if(!isset($android_app['android_app_page'])){
                $android_app['android_app_page'] = $android_app['android_app_link'];
            }
            define('ANDROID_APP_LINK', $android_app['android_app_link']);
            define('ANDROID_MIN_VER', $android_app['android_min_ver']);
            define('ANDROID_CURRENT_VER', $android_app['android_current_ver']);
            define('ANDROID_APP_PAGE', $android_app['android_app_page']);
        }

        $ios_app = isset($this->app_config['ios_app']['key_value'])?$this->app_config['ios_app']['custom_data']:0;
        if(!empty($ios_app))
        {
            define('IOS_APP_LINK', $ios_app['ios_app_link']);
            define('IOS_MIN_VER', $ios_app['ios_min_ver']);
            define('IOS_CURRENT_VER', $ios_app['ios_current_ver']);
        }

        $default_sports_id = isset($this->app_config['default_sports_id'])?$this->app_config['default_sports_id']['key_value']:7;
        define('DEFAULT_SPORTS_ID', $default_sports_id);

        $allow_bank_transfer = isset($this->app_config['allow_bank_transfer'])?$this->app_config['allow_bank_transfer']['key_value']:0;
        define('ALLOW_BANK_TRANSFER', $allow_bank_transfer);

        $allow_mpesa_withdraw = isset($this->app_config['allow_mpesa_withdraw'])?$this->app_config['allow_mpesa_withdraw']['key_value']:0;
        define('ALLOW_MPESA_WITHDRAW', $allow_mpesa_withdraw);

        $allow_private_contest = isset($this->app_config['allow_private_contest'])?$this->app_config['allow_private_contest']['key_value']:0;
        define('ALLOW_PRIVATE_CONTEST', $allow_private_contest);

        $bucket_static_data_allowed = isset($this->app_config['bucket_static_data_allowed'])?$this->app_config['bucket_static_data_allowed']['key_value']:0;
        define('BUCKET_STATIC_DATA_ALLOWED', $bucket_static_data_allowed);

        $bucket_data_prefix = isset($this->app_config['bucket_data_prefix'])?$this->app_config['bucket_data_prefix']['key_value']:0;
        define('BUCKET_DATA_PREFIX', $bucket_data_prefix);

        $int_version = isset($this->app_config['int_version'])?$this->app_config['int_version']['key_value']:0;
        define('INT_VERSION', $int_version);

        $coin_only = isset($this->app_config['coin_only'])?$this->app_config['coin_only']['key_value']:0;
		define('COIN_ONLY', $coin_only);

        $max_contest_bonus = isset($this->app_config['max_contest_bonus'])?$this->app_config['max_contest_bonus']['key_value']:0;
        define('MAX_CONTEST_BONUS', $max_contest_bonus);

        $currency_code = isset($this->app_config['currency_code'])?$this->app_config['currency_code']['key_value']:0;
        define('CURRENCY_CODE', $currency_code);
        define('CURRENCY_CODE_HTML', CURRENCY_CODE);
    }

    /**
     * Prepares for basic authentication
     *
     * @access protected
     * @return void
     */
    protected function _custom_prepare_basic_auth($auth_key = FALSE) {
        if (!$this->auth_key && $auth_key) {
            $this->auth_key = $auth_key;
        }

        $key = $this->auth_key;

        $this->load->model("auth/Auth_nosql_model");
        $key_detail = $this->Auth_nosql_model->select_one_nosql(ACTIVE_LOGIN, array(AUTH_KEY => $key));

        if (empty($key_detail)) {
            $this->load->model("auth/Auth_model");
            $key_detail = $this->Auth_model->check_user_key($key);
            if (!empty($key_detail)) {
                $nosql_data = array();
                $nosql_data['role'] = $key_detail['role'];
                $nosql_data['user_id'] = $key_detail['user_id'];
                $nosql_data['device_type'] = $key_detail['device_type'];
                $nosql_data['user_unique_id'] = $key_detail['user_unique_id'];
                $nosql_data['email'] = $key_detail['email'];
                $nosql_data['user_name'] = $key_detail['user_name'];
                $nosql_data['referral_code'] = $key_detail['referral_code'];
                $nosql_data['phone_no'] = $key_detail['phone_no'];
                $nosql_data['bs_status'] = $key_detail['bs_status'];
                $nosql_data[AUTH_KEY] = $key;
                $key_detail[AUTH_KEY] = $key;
                $this->Auth_nosql_model->insert_nosql(ACTIVE_LOGIN, $nosql_data);
            }
        }

        if (!empty($key_detail)) {
            if (isset($key_detail['role']) && $key_detail['role'] == 1) {
                $this->email = $key_detail['email'];
                $this->user_id = $key_detail['user_id'];
                $this->user_name = $key_detail['user_name'];
                $this->user_unique_id = $key_detail['user_unique_id'];
                $this->referral_code = $key_detail['referral_code'];
                $this->phone_no = $key_detail['phone_no'];
                $this->bs_status = isset($key_detail['bs_status']) ? $key_detail['bs_status'] : "";
                if (!$this->language) {
                    $this->language = $key_detail['language'];
                    $this->set_lang($this->language);
                }
            }
            return TRUE;
        } else {
            if ($this->custom_auth_override) {
                return TRUE;
            }
            $this->response([
                $this->config->item('rest_status_field_name') => FALSE,
                "response_code" => self::HTTP_UNAUTHORIZED,
                "global_error" => $this->lang->line('text_rest_unauthorized'),
                $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_unauthorized')
                    ], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Check Device Type
     * Check if the input value exist or not
     * @return boolean
     */
    public function check_device_type($device_type) {
        if (in_array($device_type, array(1, 2, 3))) {
            return TRUE;
        }
        $this->form_validation->set_message('check_device_type', $this->lang->line("invalid_device_type"));
        return FALSE;
    }

    /**
     * Used for validate user device id
     * @return boolean
     */
    public function check_device_id() {
        if (in_array($this->input->post('device_type'), array(3)) || $this->input->post('device_id')) { //device id rule not applied in web
            return TRUE;
        }
        //$this->form_validation->set_message('check_device_id', $this->lang->line("device_id_required"));
        return TRUE;
    }

    /**
     * Used for password filed required or not
     * @return boolean
     */
    public function password_required() {
        if ($this->input->post('password')) {
            return TRUE;
        } else if ($this->input->post('facebook_id') || $this->input->post('google_id')) {
            return TRUE;
        }

        $this->form_validation->set_message('password_required', $this->lang->line("password_required"));
        return FALSE;
    }

    /**
     * Used for validate input is number or not
     * @return boolean
     */
    public function is_digits($phone_no) {
        if (preg_match("/^[0-9]+$/", $phone_no)) {
            return TRUE;
        }

        $this->form_validation->set_message('is_digits', $this->lang->line("phone_no_required"));
        return FALSE;
    }

    /**
     * Used for compare two field data
     * @param string $second_field
     * @param string $first_field
     * @return boolean
     */
    function check_equal_greater($second_field, $first_field) {
        if ($second_field >= $first_field) {
            return TRUE;
        } else {
            $this->form_validation->set_message('check_equal_greater', $this->lang->line("should_be_greater_than_or_equal") . " " . $first_field . ".");
            return FALSE;
        }
    }

    /**
     * Used for compare two field data
     * @param string $second_field
     * @param string $first_field
     * @return boolean
     */
    function validate_deposit_amount($amount) {
        $min_deposit = isset($this->app_config['min_deposit']['key_value']) ? $this->app_config['min_deposit']['key_value'] : 5;
        $max_deposit = isset($this->app_config['max_deposit']['key_value']) ? $this->app_config['max_deposit']['key_value'] : 10000;
        if ($amount >= $min_deposit && $amount <= $max_deposit) {
            return TRUE;
        } else {
            $msg = "Deposit amount should be between ".$min_deposit." to ".$max_deposit;
            $this->form_validation->set_message('validate_deposit_amount', $msg);
            return FALSE;
        }
    }

    public function check_email()
	{
		$this->load->model('auth/Auth_model');
		$user_data = $this->Auth_model->get_single_row('email,user_id', USER, array("email"=>$this->input->post('email')));

		if(!$user_data || ($user_data["email"]==$this->input->post('email')&&$user_data["user_id"]==$this->user_id))
		{
			return TRUE;
		}

		$this->form_validation->set_message('check_email', $this->lang->line("email_already_exists_message"));
		return FALSE;
	}


    /**
     * Used to validate decimal number or not
     * @param string $str
     * @return boolean
     */
    public function decimal_numeric($str) {
        if (!is_numeric($str) && $str > 0) { //Use your logic to check here
            $this->form_validation->set_message('decimal_numeric', $this->lang->line('numeric_only'));
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Used to validate number greater then 0 or not
     * @param string $str
     * @return boolean
     */
    public function greater_than_zero($str) {
        if ($str == '0') { //Use your logic to check here
            $this->form_validation->set_message('greater_than_zero', $this->lang->line('greater_than_zero'));
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * return service api response data
     * @return json array
     */
    public function api_response() {
        $output = array();
        $output['service_name'] = $this->api_response_arry['service_name'];
        $output['message']      = $this->api_response_arry['message'];
        $output['global_error'] = $this->api_response_arry['global_error'];
        $output['error']        = $this->api_response_arry['error'];
        $output['data']         = $this->api_response_arry['data'];
        $output['response_code'] = $this->api_response_arry['response_code'];
        if(!empty($this->api_response_arry['header']) && is_array($this->api_response_arry['header'])){
            foreach($this->api_response_arry['header'] as $hkey=>$hval){
                header($hkey.': '.$hval, TRUE);
            }
        }

        //for query log hook added in log folder
        $hook = & load_class('Hooks', 'core');
        $hook->call_hook('post_controller');

        if (method_exists($this, 'response')) {
            $this->response($output, $this->api_response_arry['response_code']);
        } else {
            http_response_code($this->api_response_arry['response_code']);
        }
    }

    /**
     * Used for load cache driver
     * @return 
     */
    private function init_cache_driver() {
        $this->load->driver('cache', array('adapter' => CACHE_ADAPTER, 'backup' => 'file'));
    }

    /**
     * Used for get cache data by key
     * @param string $cache_key cache key
     * @return array
     */
    public function get_cache_data($cache_key) {
        if (!$cache_key || !CACHE_ENABLE) {
            return false;
        }

        $this->init_cache_driver();
        $cache_key = CACHE_PREFIX . $cache_key;
        return $this->cache->get($cache_key);
    }

    /**
     * Used for save cache data by key
     * @param string $cache_key cache key
     * @param array $data_arr cache data
     * @param int $expire_time cache expire time
     * @return boolean
     */
    public function set_cache_data($cache_key, $data_arr, $expire_time = 3600) {
        if (!$cache_key || !CACHE_ENABLE) {
            return false;
        }

        $this->init_cache_driver();
        $cache_key = CACHE_PREFIX . $cache_key;
        $this->cache->save($cache_key, $data_arr, $expire_time);
        return true;
    }

    /**
     * Used for delete cache data by key
     * @param string $cache_key cache key
     * @return boolean
     */
    public function delete_cache_data($cache_key) {
        if (!$cache_key || !CACHE_ENABLE) {
            return false;
        }

        $this->init_cache_driver();
        $delete_cache_key = CACHE_PREFIX . $cache_key;
        $this->cache->delete($delete_cache_key);
        return true;
    }

    
	/**
	* Used for delete cache data by wildcard key / pattern
	* @param string $cache_key cache key
	* @return boolean
	*/
	public function delete_wildcard_cache_data($cache_key) {
		if (!$cache_key || !CACHE_ENABLE) {
			return false;
		}

		$this->init_cache_driver();
		$delete_cache_key = CACHE_PREFIX . $cache_key;
		$this->cache->delete_wildcard($delete_cache_key);
		return true;
	}



    /**
     * Used for push s3 data in queue
     * @param string $file_name json file name
     * @param array $data api file data
     * @return 
     */
    public function push_s3_data_in_queue($file_name, $data = array()) {
        if (BUCKET_STATIC_DATA_ALLOWED == "0" || $file_name == "") {
            return false;
        }
        $bucket_data = array("file_name" => $file_name, "data" => $data);
        $this->load->helper('queue_helper');
        add_data_in_queue($bucket_data, 'bucket');
    }

    /**
     * Validate Promo Code
     * @param
     * @return json array
     */
    function validate_promo($post_data) {
        
        $code_detail = $this->Finance_model->check_promo_code_details($post_data);

        if (empty($code_detail)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->finance_lang["invalid_promo_code"];
            $this->api_response();
        } elseif ($code_detail['type'] == DEPOSIT_RANGE_TYPE && ($post_data['amount'] < $code_detail['min_amount'] || $post_data['amount'] > $code_detail['max_amount'])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->finance_lang["promo_code_amount_range_invalid"];
            $this->api_response();
        } else if ($code_detail['type'] == FIRST_DEPOSIT_TYPE && $code_detail['total_used'] > 0) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->finance_lang["first_deposit_already_used"];
            $this->api_response();
        } else if ($code_detail['total_used'] >= $code_detail['per_user_allowed']) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->finance_lang["allowed_limit_exceed"];
            $this->api_response();
        } else {
            if ($code_detail['type'] == FIRST_DEPOSIT_TYPE) {
                $order_info = $this->Finance_model->get_single_row('count(order_id) as total', ORDER, array("source" => "7", "user_id" => $this->user_id, "source_id != " => "0"));

                if (!empty($order_info) && $order_info['total'] > 0) {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->finance_lang["first_deposit_already_used"];
                    $this->api_response();
                }
            }
            if ($code_detail['value_type'] == "1") {
                $bonus_amount = ($post_data['amount'] * $code_detail['discount']) / 100;
                if ($bonus_amount > $code_detail['benefit_cap']) {
                    $bonus_amount = $code_detail['benefit_cap'];
                }
            } else {
                $bonus_amount = $code_detail['discount'];
            }
        }

        $code_detail['amount'] = $bonus_amount;

        return $code_detail;
    }

    function validate_deal($post_data)
    {
        if(empty($this->app_config['allow_deal']['key_value']))
        {
            return array();
        }
        if($post_data!=NULL)
        {
            $deal_detail = $this->Finance_model->check_deal_details($post_data);
        }
        if (empty($deal_detail)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->finance_lang["invalid_deal"];
            $this->api_response();
        }elseif(!empty($deal_detail)){
            $deal_amount            = number_format($deal_detail['amount'], 2, '.', '');
            $post_data['amount']    = number_format($post_data['amount'], 2, '.', '');
            if($post_data['amount'] != $deal_amount){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->finance_lang["invalid_deal_amount"];
                $this->api_response();
            }
        }


        return $deal_detail;
    }

    
    /**
     * Update Order Status
     * 
     * @param int $order_id Order ID
     * @param int $status_type Status type/code
     * @param int $source_id Source ID
     * @param string $reason Reason
     * @param int $pg Payment Gateway
     * @return array Error and service name
     */
    public function update_order_status($order_id, $status_type, $source_id, $reason = "", $pg,$txnid=0,$response_data=array()) {
        $orderData = $this->Finance_model->get_pending_order_detail($order_id);
        
        if (!$orderData){
            
            /*$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->finance_lang["first_deposit_already_used"];
            $this->api_response();*/
            // paypal payment status is success or not
            if($pg == 6){ 
                $transaction_details = $this->Finance_model->get_single_row('surl,furl,transaction_status', TRANSACTION, array("transaction_id" => $txn_id));
                if(!empty($transaction_details) && $transaction_details['transaction_status'] == 1){
                    //return true;
                }
            }else{
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->finance_lang["first_deposit_already_used"];
                $this->api_response();
            }
        }
        
        $user_balance = $this->Finance_model->get_user_balance($orderData["user_id"]);
        
        if ($orderData['source'] == 7 && $status_type = 1) {
            if ($pg == 2) {  // Paytm
                $paym_data = $this->post();
                if($this->app_config['allow_paytm']['custom_data']['pg_mode']=='TEST')
                {
                    $PAYTM_ORDER_STATUS_API       = PAYTM_ORDER_STATUS_API_TEST;
                }else{
                    $PAYTM_ORDER_STATUS_API       = PAYTM_ORDER_STATUS_API_PRO;
                }
                $paytm_status_response = get_paytm_transaction_status($paym_data['MID'], $paym_data['ORDERID'], $paym_data['CHECKSUMHASH'],$PAYTM_ORDER_STATUS_API);

                if (empty($paytm_status_response['STATUS']) || (!empty($paytm_status_response['STATUS']) && $paytm_status_response['STATUS'] !== "TXN_SUCCESS")) {
                    if (!empty($paytm_status_response['STATUS'])) {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $paytm_status_response['STATUS'];
                        $this->api_response();
                    } else {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $this->finance_lang["payment_status_update_error"];
                        $this->api_response();
                    }
                }
            } elseif ($pg == 1) {  // Payumoney
                $config = array(
                    "VERSION"  => $this->app_config['allow_payumoney']['custom_data']['version'],
                    "MERCHANT_KEY"=>$this->app_config['allow_payumoney']['custom_data']['merchant_key'],
                    "TXN_VALIDATE_BASE_URL"=>($this->app_config['allow_payumoney']['custom_data']['pg_mode']=='TEST') ? PAYU_TXN_VALIDATE_BASE_URL_TEST : PAYU_TXN_VALIDATE_BASE_URL_PRO,
                    "AUTH_HEADER"=>$this->app_config['allow_payumoney']['custom_data']['auth_header']
                );
                if($this->VERSION!='NEW'){
                    $payu_status_response = payu_validate_transaction($source_id,$config);
                    if (empty($payu_status_response) || (!empty($payu_status_response['status']) && $payu_status_response['status'] !== 'SUCCESS')) {
                        if (!empty($payu_status_response['status'])) {
                            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                            $this->api_response_arry['message'] = $payu_status_response['status'];
                            $this->api_response();
                        } else {
                            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                            $this->api_response_arry['message'] = $this->finance_lang["payment_status_update_error"];
                            $this->api_response();
                        }
                    }
                }
            }
            elseif($pg==5){ // Ipay 
                $getdata = $this->get();
                $config = array(
                    "IPAY_MERCHANT_KEY"=>$this->app_config['allow_ipay']['custom_data']['merchant_key'],
                    "SUCCESS_STATUS"=>"aei7p7yrx4ae34",
                    "FAIL_STATUS"=>"fe2707etr5s4wq",
                    "ALREADY_STATUS"=>"cr5i3pgy9867e1",
                );
                $ipay_status_response = get_ipay_transaction_status($source_id,$getdata,$config);
                //print_r($ipay_status_response);exit;
                
                if(empty($ipay_status_response) || (!empty($ipay_status_response) && $ipay_status_response['status']!=='SUCCESS'))
                {
                    if(!empty($ipay_status_response['status'])){
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $ipay_status_response['status'];
                        $this->api_response();
                    } else {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $this->finance_lang["payment_status_update_error"];
                        $this->api_response();
                    }
                }
            }
            elseif($pg==6){  //paypal
                $success_ack = 'ACK=Success';
                $paypal_status_response= '';
                $PAYPAL_METHOD = $this->app_config['allow_paypal']['custom_data']['method'];

                if(strtolower($PAYPAL_METHOD)=='signature' || strtolower($PAYPAL_METHOD)=='secret'){
                    $paypal_status_response = $response_data;
                }
                $success_ack_check = false;
                if(strpos($paypal_status_response, $success_ack) !== false){
                    $success_ack_check=true;
                }

                if(empty($paypal_status_response) || $success_ack_check == false)
                { 
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $this->finance_lang["payment_status_update_error"];
                        $this->api_response();
                }

            }
            elseif($pg==7){ // Paystack 
                $getdata = $this->get();
                $config = array(
                    "secret"=>$this->app_config['allow_paystack']['custom_data']['secret'],
                  );
                $paystack_status_response = get_paystack_transaction_status($getdata['trxref'],$config);
                //print_r($ipay_status_response);exit;
                
                if(empty($paystack_status_response) || (!empty($paystack_status_response) && $paystack_status_response['status']!=='SUCCESS'))
                {
                    if(!empty($paystack_status_response['status'])){
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $paystack_status_response['status'];
                        $this->api_response();
                    } else {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $this->finance_lang["payment_status_update_error"];
                        $this->api_response();
                    }
                }
            }
            elseif($pg==10)
            {
                // print_r($this->app_config['allow_stripe']['custom_data']['s_key']);exit;
                $amount = $this->Finance_model->get_single_row('real_amount', ORDER, array("order_id" => $order_id));
                $code_earning = $this->Finance_model->get_single_row('pg_order_id', TRANSACTION, array("transaction_id" => $source_id));
                $data = array(
                    'amount'=>$amount['real_amount']*100,
                    'charge_id'=>$code_earning['pg_order_id'],
                    'key'=>$this->app_config['allow_stripe']['custom_data']['s_key'],
                    "user_id"=>$this->user_id,
                );
                $stripe_response = get_stripe_txn_status($data);
                if(empty($stripe_response) || (!empty($stripe_response) && $stripe_response['status']!=='SUCCESS')){
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->finance_lang["payment_status_update_error"];
                    $this->api_response_arry['data'] = $stripe_response['status'];
                    $this->api_response();
                }
            }
            elseif($pg==15){ // Crypto
                $cusData = json_decode($orderData['custom_data'],true);
                $cryptoTranId = $cusData['tran_id'];
                $clientTranId = $cusData['client_tran_id'];
                $client_endpoint = $this->app_config['allow_crypto']['custom_data']['crypto_endpoint'];

                $crypto_status_response = get_crypto_transaction_status($clientTranId,$cryptoTranId,$client_endpoint);
                
                if(empty($crypto_status_response) || $crypto_status_response!=='SUCCESS')
                {
                    if(!empty($crypto_status_response)){
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $crypto_status_response;
                        $this->api_response();
                    } else {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $this->finance_lang["payment_status_update_error"];
                        $this->api_response();
                    }
                }
            }
            elseif ($pg == 17) { //cashfree
                $data = array();
                $data['txnid']      = $this->app_config['order_prefix']['key_value'].$source_id;
                $data['mode']       = $this->app_config['allow_cashfree']['custom_data']['mode'];
                $data['app_id']     = $this->app_config['allow_cashfree']['custom_data']['app_id'];
                $data['secret_key'] = $this->app_config['allow_cashfree']['custom_data']['secret_key'];
                $data['app_version'] = $this->app_config['allow_cashfree']['custom_data']['app_version'];
                $cashfree_status_response = cashfree_validate_transaction($data);
                if(empty($cashfree_status_response) || (!empty($cashfree_status_response['payment_status']) && $cashfree_status_response['payment_status'] !== 'SUCCESS'))
                {
                    if(!empty($cashfree_status_response['payment_status']))
                    {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $payu_status_response['status'];
                        $this->api_response();
                    }
                    else
                    {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $this->finance_lang["payment_status_update_error"];
                        $this->api_response();
                    }
                }
                
                if($orderData['status']==0)
                {
                $txn_data['gate_way_name'] = "Cashfree_upgraded";
                $txn_data['txn_amount'] = $cashfree_status_response['order_amount'] ? $cashfree_status_response['order_amount']:'';
                $txn_data['bank_txn_id'] = isset($cashfree_status_response['bank_reference']) ? $cashfree_status_response['bank_reference'] : NULL;
                $txn_data['txn_date'] = isset($cashfree_status_response['payment_time']) ? $cashfree_status_response['payment_time'] : NULL;
                $txn_data['transaction_message'] = isset($cashfree_status_response['payment_message']) ? $cashfree_status_response['payment_message'] : '';
                $txn_data['currency'] = isset($cashfree_status_response['payment_currency']) ? $cashfree_status_response['payment_currency'] : '';
                $txn_data['mid'] = isset($cashfree_status_response['cf_payment_id']) ? $cashfree_status_response['cf_payment_id'] : "";
                $txn_data['pg_order_id'] = isset($cashfree_status_response['order_id']) ? $cashfree_status_response['order_id'] : "";
                $txn_data['txn_id'] = isset($cashfree_status_response['referenceId']) ? $cashfree_status_response['referenceId'] : "";
                $txn_data['transaction_status'] = $status_type;

                $order_data = array(
                    "order_id"=>$order_id,
                    "status"=>$status_type,
                    "source_id"=>$source_id,
                    "reason"=>$reason,
                );
                

                $is_updated_row = $this->Finance_model->update_order_transaction($source_id,$txn_data,$order_data);
                // $res = $this->Finance_model->update_transaction($data, $transaction_id);
                
                }
            }
            elseif($pg==16){ // Cashierpay

                $mode = $this->app_config['allow_cashierpay']['custom_data']['mode'];
                $payId = $this->app_config['allow_cashierpay']['custom_data']['payId'];
                $secretKey = $this->app_config['allow_cashierpay']['custom_data']['secretKey'];
                $currency = $this->app_config['allow_cashierpay']['custom_data']['currency'];
                $order_prefix = isset($this->app_config['order_prefix']) ? $this->app_config['order_prefix']['key_value'] : '';
                if($mode == 'PROD')
                {
                    $url = "https://enquiry.cashierpay.online/".CASHIERPAY_STATUS_URL;
                }else{
                    $url = "https://enquiry.cashierpay.online/".CASHIERPAY_STATUS_URL;
                }
                
                $request_data=array(
                    "ORDER_ID"          => $order_prefix.$orderData['source_id'],
                    "TXNTYPE"           => 'STATUS',
                    "AMOUNT"            => $orderData['real_amount']*100,
                    "CURRENCY_CODE"     => $currency,
                    "PAY_ID"            => $payId,
                );
                $request_data['HASH'] = get_cashierpay_hash($request_data,$secretKey);
                
                $cashierpay_status_response = get_cashierpay_txn_status($request_data,$url);
                // echo "<pre>";print_r($cashierpay_status_response);exit;
                if(empty($cashierpay_status_response) || strtoupper($cashierpay_status_response['STATUS'])!=='CAPTURED' || $cashierpay_status_response['RESPONSE_CODE']!=000)
                {
                    if(!empty($cashierpay_status_response)){
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $cashierpay_status_response;
                        $this->api_response();
                    } else {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $this->finance_lang["payment_status_update_error"];
                        $this->api_response();
                    }
                }
            }else if($pg==18)
            {
                $salt = $this->app_config['allow_paylogic']['custom_data']['salt'];
                $app_id = $this->app_config['allow_paylogic']['custom_data']['app_id'];

                $pg_order_id = $this->Finance_model->get_single_row('pg_order_id', TRANSACTION, array("transaction_id" => $orderData['source_id']));
                
                $request_data=array(
                    "txn_id"          => $pg_order_id['pg_order_id'],
                );

                $txn_data = get_paylogic_txn_status($request_data,$app_id);
                $txn_data = $this->decrypt($txn_data,$salt);
                
                if((isset($txn_data['trans_status']) && strtolower($txn_data['trans_status'])!='ok'))
                {
                    if(!empty($paylogic_response)){
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $paylogic_response;
                        $this->api_response();
                    } else {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $this->finance_lang["payment_status_update_error"];
                        $this->api_response();
                    }
                }
            }else if($pg==27)
            {
                $config = [
                    "app_id" =>$this->app_config['allow_directpay']['custom_data']['app_id'],
                    "secret" =>$this->app_config['allow_directpay']['custom_data']['secret_key'],
                    "mode" =>$this->app_config['allow_directpay']['custom_data']['mode'],
                ];
                $pg_order_id = $this->Finance_model->get_single_row('pg_order_id', TRANSACTION, array("transaction_id" => $orderData['source_id']));
                $req_data = array(
                    "merchant_id"=>$config['app_id'],
                    "order_id"=>$pg_order_id['pg_order_id'],
                    );
                $encode_payload = base64_encode(json_encode($req_data));
                $signature = hash_hmac('sha256', $encode_payload, $config['secret']);

                $txn_data = get_directpay_txn_status($encode_payload,$signature,$config['mode']);
                if($txn_data['status']==200 && isset($txn_data['data']['transaction']['status']))
                {
                    $txn_data = $txn_data['data']['transaction'];
                }

                
                if((isset($txn_data['status']) && strtolower($txn_data['status'])!='success'))
                {
                    if(!empty($paylogic_response)){
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $paylogic_response;
                        $this->api_response();
                    } else {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $this->finance_lang["payment_status_update_error"];
                        $this->api_response();
                    }
                }
            }
            

        }

        $source_id = $source_id ? $source_id : 0;
        if ($pg != 17) {
            /**
             *due to double benefit in upi payment , we uppdated order and transation at same time in cashfree with same method in model
             *so skipped here 
             */
            $this->Finance_model->update_order_status($order_id, $status_type, $source_id, $reason);
        }

        // For Creadit payment.
        if ($orderData["type"] == 0 && $status_type == 1) {
            
            if ($pg == 17) {
                if($is_updated_row == 1)
                {
                    $this->Finance_model->update_user_balance($orderData["user_id"], $orderData,'add',$order_id);
                }else{
                    $status_type=0; // 0 is set to false notification condition.
                }
            }else{
                $this->Finance_model->update_user_balance($orderData["user_id"], $orderData,'add',$order_id);
                $user_cache_key = "user_balance_" . $orderData["user_id"];
                $this->delete_cache_data($user_cache_key);
            }

            //for promo code balance redeem on transaction success
            $promo_code_data = $this->Finance_model->get_order_promo_code_details($order_id, $orderData['user_id']);
            if (!empty($promo_code_data) && $promo_code_data['is_processed'] == 0) {
                $code_earning = $this->Finance_model->get_single_row('COUNT(promo_code_earning_id) as total', PROMO_CODE_EARNING, array("promo_code_id" => $promo_code_data["promo_code_id"], "is_processed" => "1", "user_id" => $promo_code_data["user_id"]));

                if (isset($code_earning['total']) && $code_earning['total'] >= $promo_code_data['per_user_allowed']) {
                    //marke as failed
                    $code_arr = array("is_processed" => "2");
                    $this->Finance_model->update_promo_code_earning_details($code_arr, $promo_code_data["promo_code_earning_id"]);
                } else {
                    $code_arr = array("is_processed" => "1");
                    $code_result = $this->Finance_model->update_promo_code_earning_details($code_arr, $promo_code_data["promo_code_earning_id"]);
                    if ($code_result) {
                        //check promo code cash or bonus type
                        $promo_code_cash_type = 1; //bonus
                        $cash_type = 'Bonus';
                        if ($promo_code_data['cash_type'] == 1) {
                            $promo_code_cash_type = 0; //real cash
                            $cash_type = 'Cash';
                        }
                        $bonus_source = 6;
                        if ($promo_code_data['type'] == 0) {
                            $bonus_source = 30;
                        } else if ($promo_code_data['type'] == 1) {
                            $bonus_source = 31;
                        } else if ($promo_code_data['type'] == 2) {
                            $bonus_source = 32;
                        }
                        $custom_data = array('promo_code' => $promo_code_data['promo_code'],'cash_type'=>$cash_type);
                        $ord_data = array(
                            "user_id" => $promo_code_data["user_id"],
                            "amount" => $promo_code_data['amount_received'],
                            "source" => $bonus_source,
                            "source_id" => $promo_code_data['promo_code_earning_id'],
                            "cash_type" => $promo_code_cash_type,
                            "custom_data" => json_encode($custom_data));

                        $this->Finance_model->create_order($ord_data);
                    }
                }
            }

            //deal redeem
            $this->deal_redeem_on_update_status($order_id, $orderData);
        }
        // Credit / if withdraw reqest cancel.
        if ($orderData["type"] == 1 && $status_type == 2 && $orderData['source'] == 8) {
            $this->Finance_model->update_user_balance($orderData["user_id"], $orderData, 'add');
        }
        
        
        
        if ($orderData['source'] == 7 && $status_type == 1) {
            //for update user lifetime deposit
            // if ($orderData['type'] == 0 && $orderData['source_id'] > 0) {
            //     $this->Finance_model->update_user_total_deposit($orderData["user_id"], $orderData["real_amount"]);
            // }

            $tmp = array();
            $user_detail = $this->Finance_model->get_single_row('user_id, user_name, phone_no, email, campaign_code', USER, array("user_id" => $orderData["user_id"]));
            $notify_data["amount"] = $orderData["real_amount"];
            $notify_data["reason"] = $reason;
            $tmp["notification_type"] = 6; // 6-Deposit
            $tmp["source_id"] = $source_id;
            $tmp["notification_destination"] = 7; //  Web, Push, Email
            $tmp["user_id"] = $orderData["user_id"];
            $tmp["to"] = $user_detail['email'];
            $tmp["user_name"] = $user_detail['user_name'];
            $tmp["added_date"] = format_date();
            $tmp["modified_date"] = format_date();
            $tmp["content"] = json_encode($notify_data);
            $tmp["subject"] = $this->lang->line('deposit_success_subject');

            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($tmp);
            if(isset($user_detail['campaign_code']) && $user_detail['campaign_code']!='' && $this->app_config['new_affiliate']['key_value']==1)
            {
                // "visit_code"=>$post_data['visit_code'],
                $user_data = array(
                    "campaign_code"=>$user_detail['campaign_code'],
                    "user_id"=>$user_detail['user_id'],
                    "name"=>'deposit',
                    "ref_id"=>$order_id,
                    "entity_id"=>$orderData['source_id'],
                    "amount"=>$orderData["real_amount"],
                 );
                 
                 $this->load->helper('queue_helper');
                 add_data_in_queue($user_data, 'af_deposit_user');
            }
            
            $user_affiliate = $this->Finance_model->get_single_row('*', USER_AFFILIATE_HISTORY, array("friend_id" => $orderData['user_id'], "status" => 1, 'affiliate_type in (1,19,20,21)' => null));
            if (!empty($user_affiliate)) {//referral user case
                if (!empty($user_detail) && !empty($user_affiliate['user_id'])) {
                    $this->add_bonus($user_detail, $user_affiliate['user_id'], 14, 5, $orderData["real_amount"]);
                }
            }
            $affiliate_member = $this->Finance_model->get_single_row('*', USER_AFFILIATE_HISTORY, array("friend_id" => $orderData['user_id'], "status" => 1, 'affiliate_type' => 6,'is_affiliate'=>1));
            if(!empty($affiliate_member['user_id']) && $affiliate_member['user_id']!=0 && !empty($user_detail)){
                $affiliate_commission = $this->Finance_model->get_single_row('commission_type,deposit_commission,user_name',USER,array('user_id'=>$affiliate_member['user_id'],'is_affiliate'=>1,'status'=>1));
                if(!empty($affiliate_commission)){
                    $friend_username = $this->Finance_model->get_single_row('user_name',USER,array('user_id'=>$orderData['user_id']));
                    $orderData["winning_amount"]=$orderData["real_amount"]*$affiliate_commission['deposit_commission']*.01;
                    $deposit_data_friend = array(
                        "user_id" 	=> $affiliate_member['user_id'],
                        "amount"  	=> round($orderData["winning_amount"],2),
                        "source" 	=> 321,// for commission of affiliate against amount deposit
                        "source_id" => $order_id, 
                        "plateform" => 1, 
                        "cash_type" => $affiliate_commission['commission_type'],//either 0 as real or 4 for winning amount
                        "reason" => "Commission for user deposit through affiliate program.", 
                        "link" 	=> FRONT_APP_PATH.'my-wallet',
                    );
                    $custom_data = array(
                        'user_id'=>$orderData['user_id'],
                        'user_name'=>$friend_username['user_name'],
                        'amount' => $orderData["real_amount"],
                    );
                    $deposit_data_friend['custom_data'] = json_encode($custom_data);
                    $this->load->model("finance/Finance_model");
                    $this->Finance_model->deposit_fund($deposit_data_friend);
                }
            }
           
            $this->add_gst_cashback($orderData['order_id']);
        }

        if ($orderData['source'] == 325 && $status_type == 1) {
            $tmp = array();
            $user_detail = $this->Finance_model->get_single_row('user_id, user_name, phone_no, email', USER, array("user_id" => $orderData["user_id"]));
            $notify_data["amount"] = $orderData["points"];
            $notify_data["reason"] = $reason;
            $tmp["notification_type"] = 425; // 425-Inapp purchase coins issued
            $tmp["source_id"] = $source_id;
            $tmp["notification_destination"] = 1; //  Web
            $tmp["user_id"] = $orderData["user_id"];
            $tmp["to"] = $user_detail['email'];
            $tmp["user_name"] = $user_detail['user_name'];
            $tmp["added_date"] = format_date();
            $tmp["modified_date"] = format_date();
            $tmp["content"] = json_encode($notify_data);
            $tmp["subject"] = $this->lang->line('deposit_coin_subject');

            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($tmp);
        }

        if ($orderData['source'] == 437 && $status_type == 1) {
            $tmp = array();
            $user_detail = $this->Finance_model->get_single_row('user_id, user_name, phone_no, email', USER, array("user_id" => $orderData["user_id"]));
            $notify_data["amount"] = $orderData["points"];
            $notify_data["reason"] = $reason;
            $tmp["notification_type"] = 437; // 437-Inapp purchase coins subscription
            $tmp["source_id"] = $source_id;
            $tmp["notification_destination"] = 1; //  Web
            $tmp["user_id"] = $orderData["user_id"];
            $tmp["to"] = $user_detail['email'];
            $tmp["user_name"] = $user_detail['user_name'];
            $tmp["added_date"] = format_date();
            $tmp["modified_date"] = format_date();
            $tmp["content"] = json_encode($notify_data);
            $tmp["subject"] = "coin package subscription";

            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($tmp);
        }

        
        //delete user balance cache data
        $user_cache_key = "user_balance_" . $orderData["user_id"];
        $this->delete_cache_data($user_cache_key);        
        return true;
    }

    function deal_redeem_on_update_status($order_id,$orderData)
    {
        $deal_data = $this->Finance_model->get_order_deal_details($order_id, $orderData['user_id']);
        if (!empty($deal_data) && $deal_data['is_processed'] == 0) {
            $deal_earning = $this->Finance_model->get_single_row('COUNT(deal_earning_id) as total', DEALS_EARNING, array("deal_id" => $deal_data["deal_id"], "is_processed" => "1", "user_id" => $deal_data["user_id"]));

            $deal_arr = array("is_processed" => "1");
            $deal_result = $this->Finance_model->update_deal_earning_details($deal_arr, $deal_data["deal_earning_id"]);
            if ($deal_result) {

                $custom_data = json_encode(array('deal'=>$deal_data['amount']));
                //check deal cash or bonus type or coins
                if(!empty($deal_data['bonus']) && $deal_data['bonus'] > 0)
                {
                    $ord_data = array(
                        "user_id" => $deal_data["user_id"],
                        "amount" => $deal_data['bonus'],
                        "source" => 135,
                        "source_id" => $deal_data['deal_earning_id'],
                        "cash_type" => 1,
                        "custom_data"=> $custom_data
                    );//1 => bonus
    
                    $this->Finance_model->create_order($ord_data);
                }

                if(!empty($deal_data['cash']) && $deal_data['cash'] > 0)
                {
                    $ord_data = array(
                        "user_id" => $deal_data["user_id"],
                        "amount" => $deal_data['cash'],
                        "source" => 136,
                        "source_id" => $deal_data['deal_earning_id'],
                        "cash_type" => 0,
                        "custom_data"=> $custom_data
                    );//0 => real
    
                    $this->Finance_model->create_order($ord_data);
                }

                if(!empty($deal_data['coin']) && $deal_data['coin'] > 0)
                {
                    $ord_data = array(
                        "user_id" => $deal_data["user_id"],
                        "amount" => $deal_data['coin'],
                        "source" => 137,
                        "source_id" => $deal_data['deal_earning_id'],
                        "cash_type" => 2,
                        "custom_data"=> $custom_data
                    );//2 => coins
    
                    $this->Finance_model->create_order($ord_data);
                }

            }
        }
    }

    public function check_user_account_blocked_status()
    {
        if(WRONG_OTP_LIMIT <= 0){
            return true;
        }
        
        $post_data = $this->input->post();
        if((LOGIN_FLOW == "1" && !isset($post_data['email'])) || (LOGIN_FLOW == "2" && !isset($post_data['email'])) || (LOGIN_FLOW == "0" && !isset($post_data['phone_no']))){
            return false;
        }

        $this->load->model("auth/Auth_model");
        if(LOGIN_FLOW == "1" || LOGIN_FLOW == "2"){
            $email = $this->input->post('email');
            $where_arr = array('email' => $email);
        }else{
            $phone_no = $this->input->post('phone_no');
            $where_arr = array('phone_no' => $phone_no);
        }
        $check_status = $this->Auth_model->check_user_account_status($where_arr);
        if(!empty($check_status) && $check_status['is_blocked'] == 1){
            $this->api_response_arry["message"] = $this->lang->line('account_blocked_by_wrong_otp');
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        return true;
    }

    function rabbit_mq_push($data, $queue_name, $exchange_name = '') {

		$connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
		$channel = $connection->channel();
		$push_data = json_encode($data);
		$message = new AMQPMessage($push_data, array('delivery_mode' => 1, 'content_type' => 'application/json')); # make message persistent as 2
		$channel->basic_publish($message, $exchange_name, $queue_name);
		$channel->close();
		$connection->close();
		return true;
	}
    /**
     * Used to add user/referral bonus
     * @param type $user_detail
     * @param type $ref_id
     * @param type $affiliate_type
     * @param type $source_type
     * @return boolean
     */
    function add_bonus($user_detail, $ref_id=0, $affiliate_type=7, $source_type=0, $deposit_amount=0) {        
        $user_id = $user_detail['user_id'];
        if(empty($user_id)) {
            return TRUE;
        }

        $this->load->model(array('profile/Profile_model','affiliate/Affiliate_model')); 
        if($affiliate_type == 1 && !empty($ref_id)) {
            $total_joined = $this->Affiliate_model->get_user_referral_count($ref_id);
            if($total_joined == 4) {
                $affiliate_type = 19;
            }
            if($total_joined == 9) {
                $affiliate_type = 20;
            }
            if($total_joined == 14) {
                $affiliate_type = 21;
            }
        }
        //check if affiliate master entry availalbe for email verify bonus w/o referral
        $affiliate_cache_key = 'aff_master_type_' . $affiliate_type;
        $affililate_master_detail = $this->get_cache_data($affiliate_cache_key);
        if (!$affililate_master_detail) {
            $affililate_master_detail = $this->Profile_model->get_single_row('*', AFFILIATE_MASTER, array("affiliate_type" => $affiliate_type));
            $this->set_cache_data($affiliate_cache_key, $affililate_master_detail, REDIS_30_DAYS);
        }
        
        //if no details available then return true without further processing.
        if (empty($affililate_master_detail)) {
            return TRUE;
        }
         
        $is_affiliate= 0;
        $is_referral = 0;
        $friend_name = '';
        //check if signup bonus already given to this user.
        $affiliate_type_array = array(1,4,14,13,19,20,21);
        if(in_array($affiliate_type, $affiliate_type_array)) {
            $is_referral = 1;
            $friend_name = "Friend";
            if (!empty($user_detail['user_name'])) {
                $friend_name = $user_detail['user_name'];
            }
            if($affiliate_type == 14) {
                $amount_type = $affililate_master_detail['amount_type'];
                $real_amount = $affililate_master_detail['real_amount'];
                $max_earning_amount = $affililate_master_detail['max_earning_amount'];
                //log_message('error', ' user_id ='.$user_id.' ref_id ='.$ref_id);
                //log_message('error', 'amount_type ='.$amount_type.' real_amount ='.$real_amount.' max_earning_amount ='.$max_earning_amount);
                if($amount_type == 2) {
                    $real_amount = round((($deposit_amount * $real_amount)/100), 2);
                }
                
                if(!empty($max_earning_amount)) { // bonus max cap calculation
                    $deposit_bonus = $this->Affiliate_model->get_friend_deposit_bonus_by_user($user_id, $ref_id);                
                    $total_cash_earned = isset($deposit_bonus['total_cash_earned'])?$deposit_bonus['total_cash_earned']:0;
                   // log_message('error', 'deposit_amount ='.$deposit_amount.' real_amount ='.$real_amount.' total_cash_earned ='.$total_cash_earned);
                    if($max_earning_amount > $total_cash_earned) {
                        $remaing_amount = $max_earning_amount - $total_cash_earned;                       
                        if($real_amount > $remaing_amount) {
                            $real_amount = $remaing_amount;
                        }
                       // log_message('error', 'remaing_amount ='.$remaing_amount.' real_amount ='.$real_amount);
                    } else {
                        return TRUE;
                    }
                }
                $affililate_master_detail['real_amount'] = $real_amount;
                $user_affililate_history = array();
            } else {
                if(in_array($affiliate_type, array(1,19,20,21))) {
                    $user_affililate_history = $this->Profile_model->check_already_refered(0, $user_id);
                } else {
                    $user_affililate_history = $this->Profile_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY, array("friend_id" => $user_id, "status" => 1, "user_id" => $ref_id, "affiliate_type" => $affiliate_type));
                }
            }            
        } else {
            $user_affililate_history = $this->Profile_model->get_single_row('user_affiliate_history_id, user_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$user_id,"affiliate_type"=>$affiliate_type));
        }
        if(!empty($user_affililate_history)) {
            return TRUE;
        }

        if($affiliate_type == 6 && !empty($ref_id)) {
            if($this->referral_bonus_source['ra_'.$affiliate_type]==320){
                $is_affiliate=1;
                $affiliate_summary = $this->Profile_model->get_single_row('user_id,referral_code,signup_commission,commission_type', USER,array("user_id"=>$ref_id));
                $affililate_master_detail["real_amount"]=$affiliate_summary['signup_commission'];
            }
        }
        
        $bouns_condition = array();
        $data_post = array();
        $data_post["friend_id"] 	= $user_id;
        $data_post["friend_mobile"] 	= (!empty($user_detail['phone_no'])) ? $user_detail['phone_no'] : NULL;
        $data_post["user_id"] 		= $ref_id;//FOR WITHOUT REFERRAL CASE
        $data_post["status"]	 	= 1;
        $data_post["source_type"]	= $source_type;
        //$data_post["amount_type"]	= 0;
        $data_post["affiliate_type"]	= $affiliate_type;
        $data_post["is_referral"]	= $is_referral;
        $data_post["is_affiliate"] = $is_affiliate;
        
        //for user who used referral code
        $data_post["friend_bonus_cash"]	= $affililate_master_detail["user_bonus"];
        $data_post["friend_real_cash"]	= $affililate_master_detail["user_real"];
        $data_post["friend_coin"]	= $affililate_master_detail["user_coin"];
        if(!empty($ref_id)) { //for user who sent referral(refer code)
            $data_post["user_bonus_cash"]	= $affililate_master_detail["bonus_amount"];
            $data_post["user_real_cash"]	= $affililate_master_detail["real_amount"];
            $data_post["user_coin"]         = $affililate_master_detail["coin_amount"];
        }

	$data_post["bouns_condition"]	= json_encode($bouns_condition);

        
        $affililate_history_id = $this->Affiliate_model->add_affiliate_activity($data_post);
        
        $this->load->model("finance/Finance_model");
        //Entry on order table for bonus cash type
        if ($affililate_master_detail["user_bonus"] > 0) {

            $deposit_data_friend = array(
                "user_id" => $user_id,
                "amount" => $affililate_master_detail["user_bonus"],
                "source" => $this->referral_bonus_source['ub_'.$affiliate_type],//86, //New signup with referral - bonus cash
                "source_id" => $affililate_history_id,
                "plateform" => 1,
                "cash_type" => 1, //for bonus cash 
                "link" => FRONT_APP_PATH . 'my-wallet',
            );

            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for real cash type
        if($affililate_master_detail["user_real"] > 0) {
            $deposit_data_friend = array(
                "user_id"   => $user_id, 
                "amount"    => $affililate_master_detail["user_real"], 
                "source"    => $this->referral_bonus_source['ur_'.$affiliate_type],//87, //New email verify - real cash
                "source_id" => $affililate_history_id, 
                "plateform" => 1, 
                "cash_type" => 0,//for real cash 
                "link" 	=> FRONT_APP_PATH.'my-wallet'
            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }
        
        //Entry on order table for coins type
        if($affililate_master_detail["user_coin"] > 0) {
            $deposit_data_friend = array(
                "user_id"   => $user_id, 
                "amount"    => $affililate_master_detail["user_coin"], 
                "source"    => $this->referral_bonus_source['uc_'.$affiliate_type],//88, //New email verify - coins(points)
                "source_id" => $affililate_history_id, 
                "plateform" => 1, 
                "cash_type" => 2,//for coins(point balance) 
                "link"      => FRONT_APP_PATH.'my-wallet'
            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }
        
        if(!empty($ref_id)) {
            //add balance to user who referred login user
            if ($affililate_master_detail["bonus_amount"] > 0) {

                $deposit_data_friend = array(
                    "user_id" => $ref_id,
                    "amount" => $affililate_master_detail["bonus_amount"],
                    "source" => $this->referral_bonus_source['ba_'.$affiliate_type],//89, //New signup with referral - bonus cash
                    "source_id" => $affililate_history_id,
                    "plateform" => 1,
                    "cash_type" => 1, //for bonus cash 
                    "link" => FRONT_APP_PATH . 'my-wallet',
                    "friend_name" => $friend_name
                );

                $this->Finance_model->deposit_fund($deposit_data_friend);
            }

            //Entry on order table for real cash type
            if($affililate_master_detail["real_amount"] > 0) {
                $deposit_data_friend = array(
                    "user_id" 	=> $ref_id, 
                    "amount"  	=> $affililate_master_detail["real_amount"], 
                    "source" 	=> $this->referral_bonus_source['ra_'.$affiliate_type],//90, //New email verify - real cash
                    "source_id" => $affililate_history_id, 
                    "plateform" => 1, 
                    "cash_type" => 0,//for real cash 
                    "link" 	=> FRONT_APP_PATH.'my-wallet',
                    "friend_name" => $friend_name,
                    "reason"=>"Commission for user signup through affiliate program."

                );
                if($this->referral_bonus_source['ra_'.$affiliate_type]==320){
                    $deposit_data_friend['cash_type'] = $affiliate_summary['commission_type'];
                    $custom_data = array(
                        "user_id"=>$user_id,
                        "user_name"=>$user_detail['user_name'],
                        //"amount"=>$affiliate_summary['signup_commission'],
                    );
                    $deposit_data_friend['custom_data'] = json_encode($custom_data);
                }
                $this->Finance_model->deposit_fund($deposit_data_friend);
            }

            //Entry on order table for coins type
            if($affililate_master_detail["coin_amount"] > 0) {
                $deposit_data_friend = array(
                    "user_id" 	=> $ref_id, 
                    "amount"  	=> $affililate_master_detail["coin_amount"], 
                    "source" 	=> $this->referral_bonus_source['ca_'.$affiliate_type],//91, //New email verify - coins(points)
                    "source_id" => $affililate_history_id, 
                    "plateform" => 1, 
                    "cash_type" => 2,//for coins(point balance) 
                    "link"      => FRONT_APP_PATH.'my-wallet',
                    "friend_name" => $friend_name
                );
                $this->Finance_model->deposit_fund($deposit_data_friend);
            }

            
        }

        $user_cache_key = "user_balance_" . $user_id;
            $this->delete_cache_data($user_cache_key);  
            
        if(!empty($ref_id))
        {
            $user_cache_key = "user_balance_" . $ref_id;
            $this->delete_cache_data($user_cache_key);  
        }
        
        return TRUE;	
    }

    public function validate_user_site_access($auth_key=""){
        if(SITE_ACCESS_INTERVAL == "0"){
            return true;
        }

        $session_id = $auth_key;
        $ip_address = get_user_ip_address();
        if(!$session_id || $session_id == ""){
            $session_id = $ip_address;
        }
        $current_time = strtotime(format_date());
        $mem_usage = get_server_memory_usage();
        $cpu_load = get_server_cpu_usage();
        $disk_usage = get_server_disk_usage();
        $last_access_time = get_user_access_time($session_id);
        if($last_access_time == 0 && $session_id != $ip_address){
            $last_access_time = get_user_access_time($ip_address);
        }
        $minutes = floor(($current_time - $last_access_time) / 60);
        $access_type = 1;
        if ($mem_usage > MEMORY_USAGE_LIMIT || $cpu_load > CPU_LOAD_LIMIT || $disk_usage > DISK_USAGE_LIMIT) {
            if($last_access_time == 0 || $minutes > SITE_ACCESS_INTERVAL){
                $access_type = 0;
            }else{
                save_user_session_key($session_id);
            }
        }else{
            save_user_session_key($session_id);
        }

        if($access_type == 0){
            $this->api_response_arry['response_code'] = 400;
            $this->api_response_arry['data'] = "0.5";
            $this->api_response_arry['message'] = "Sorry!! You are in queue, Please try again in some time...";
            $this->api_response();
        }
        return true;
    }

    public function get_leaderboard_type_list(){
        //leaderboard
        $leaderboard_key = 'leaderboard_list';
        $leaderboard = $this->get_cache_data($leaderboard_key);
        if (!$leaderboard) {
            $this->load->model("leaderboard/Leaderboard_model");
            $leaderboard = $this->Leaderboard_model->get_leaderboard_type();
            $this->set_cache_data($leaderboard_key, $leaderboard, REDIS_30_DAYS);
        }
        return $leaderboard;
    }

    /**
     * Used for get banned state list with limit
     * @param int $limit
     * @return json array
     */
    public function get_banned_state($limit=''){
        //leaderboard
        $state_cache_key = 'banned_state';
        $state_list = $this->get_cache_data($state_cache_key);
        if (!$state_list) {
            $this->load->model("auth/Auth_model");
            $state_list = $this->Auth_model->get_banned_state_list();
            $this->set_cache_data($state_cache_key, $state_list, REDIS_30_DAYS);
        }
        if(isset($limit) && $limit > 0){
            $state_list = array_slice($state_list, 0, $limit, true);
        }
        return $state_list;
    }

    /**
     * Used for gst number validate
     * @param string gst_number
     * @return boolean
     */
    public function validate_gst_number($gst_number) {
        if(isset($gst_number) && !empty($gst_number)){
            if(strlen($gst_number) != 15){
                $msg = $this->lang->line('validate_gst_number');
                $this->form_validation->set_message('gst_number', $msg);
                return FALSE;
            }
        }else{
            return TRUE;
        }    
    }

    /**
     * Used for gst cashback
     * @param array
     * @return boolean
     */
    public function add_gst_cashback($order_id){
        
        if(empty($order_id)){
            return false;
        }
        // gst cashback bonus
        if(isset($this->app_config['allow_gst']) && $this->app_config['allow_gst']['key_value'] == "1"){
            $gst_bonus = isset($this->app_config['allow_gst']['custom_data']['gst_bonus'])?$this->app_config['allow_gst']['custom_data']['gst_bonus']:0;
            
            if($gst_bonus > 0){
                
                $this->load->model("finance/Finance_model");
                $orderData = $this->Finance_model->get_single_row('order_id,user_id,real_amount,tds,custom_data', ORDER, array("order_id"=>$order_id,'status'=>1));
                if(!empty($orderData) && isset($orderData['tds']) && $orderData['tds'] > 0){
                    
                    $custom_data = isset($orderData['custom_data'])?json_decode($orderData['custom_data'],true):array();
                    $gst_rate = isset($custom_data['gst_rate'])?$custom_data['gst_rate']:0;

                    $deposit_gst_bonus = array(
                        "user_id" => $orderData['user_id'],
                        "amount" => number_format((($gst_bonus/100)*$orderData['real_amount']), 2, '.', ''),
                        "source" => 550, //gst cashback
                        "source_id" => $orderData['order_id'],
                        "plateform" => 1,
                        "cash_type" => 5, //for gst bonus 
                        "custom_data"=> json_encode(array('amount'=>$orderData['real_amount'],'gst_rate'=>$gst_rate,'gst_bonus'=>$gst_bonus))
                    );
                    $this->Finance_model->deposit_fund($deposit_gst_bonus);
                }
            }
        }
    } 


}
