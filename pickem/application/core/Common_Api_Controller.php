<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

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
    public $api_response_arry = array(
        "response_code" => rest_controller::HTTP_OK,
        "service_name" => "",
        "message" => "",
        "global_error" => "",
        "error" => array(),
        "data" => array()
    );
    public $auth_key;
    public $auth_key_role;
    public $global_sports = array();

    public function __construct() {
        parent::__construct();
        $_POST = $this->post();

        //set service name
        $method = ($this->router->method == 'index') ? NULL : '/' . $this->router->method;
        $this->api_response_arry['service_name'] = $this->router->class . $method;

        $this->_check_cors();

        //security xss clean
        $_POST = $this->security->xss_clean($_POST, TRUE);
        if (isset($_POST['page_no'])) {
            $_POST['page_no'] = (int) ($_POST['page_no']) ? $_POST['page_no'] : 1;
        }
        if (isset($_POST['page_size'])) {
            $_POST['page_size'] = (int) ($_POST['page_size']) ? $_POST['page_size'] : RECORD_LIMIT;
        }
       
        $this->custom_auth_override = $this->_custom_auth_override_check();
        $this->auth_key = $this->input->get_request_header(AUTH_KEY);
        //this condition for uc browser lowecase issue
        if (!$this->auth_key) {
            $this->auth_key = $this->input->get_request_header(strtolower(AUTH_KEY));
        }
        $this->headers[AUTH_KEY] = $this->auth_key;

        if (!empty($this->input->get_request_header(AUTH_KEY_ROLE))) {
            $this->auth_key_role = $this->input->get_request_header(AUTH_KEY_ROLE);
            $this->headers[AUTH_KEY_ROLE] = $this->auth_key_role;
        }

        $this->get_app_config_data();

        //Define MAX contest bonus
        $max_contest_bonus = isset($this->app_config['max_contest_bonus']) ? $this->app_config['max_contest_bonus']['key_value'] : 0;
        define('MAX_CONTEST_BONUS', $max_contest_bonus);

        //module enable check
        $allow_pickem_tournament =  isset($this->app_config['allow_pickem_tournament']) ? $this->app_config['allow_pickem_tournament']['key_value']:0;
        if($allow_pickem_tournament == 0)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Pickem module not enabled";
            $this->api_response_arry['global_error'] = "Module Disable";
            $this->api_response();
        }

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
        header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization," . AUTH_KEY.",role,Version,Apiversion,User-Token,Device,RequestTime,Cookie,_ga_token,X-RefID,Ult,loc_check");
        // If the request HTTP method is 'OPTIONS', kill the response and send it to the client
        if ($this->input->method() === 'options') {
            exit;
        }
    }

    private function validate_api_request(){
        $api_check = isset($this->app_config['api_check'])?$this->app_config['api_check']['key_value']:0;
        if($api_check == "1"){
            $app_data = isset($this->app_config['api_check']['custom_data']) ? $this->app_config['api_check']['custom_data'] : array();
            if(empty($app_data)){
                return true;
            }
            
            $is_cron_data = isset($_POST['is_cron_data']) ? $_POST['is_cron_data'] : 0;
            $class_name = $this->router->class;
            $method_name = $this->router->method;
            $exclude_list = get_exclude_class_methods();
            if($is_cron_data == "1" && (in_array($class_name,$exclude_list['class']) || in_array($class_name.'/'.$method_name,$exclude_list['method']))){
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
            //$cookie_token = isset($_COOKIE['_ga_token']) ? $_COOKIE['_ga_token'] : "";
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

    protected function validate_banned_state($entry_fee = ''){
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
            if(in_array($class_name,$exclude_list['class']) || in_array($class_name.'/'.$method_name,$exclude_list['method'])){
                return true;
            }
            //check location only when header parameter is 1
            $loc_check = $this->input->get_request_header('loc_check');
            if(empty($loc_check) && $method_name != 'join_game'){
                return true;
            }

            $user_location = $this->input->get_request_header('Ult');
            $loc_query = get_user_ip_address();
            if($user_location){
                $loc_query = base64_decode($user_location);
            }

            $api_key = isset($app_data['api_key']) ? $app_data['api_key'] : "";
            $country_code = isset($app_data['country_code_allowed']) ? $app_data['country_code_allowed'] : "";
            $state_cache_key = 'banned_state';
            $state_code = $this->get_cache_data($state_cache_key);
            if (!$state_code) {
                $this->load->model("user/User_model");
                $state_code = $this->User_model->get_banned_state_list();
                $this->set_cache_data($state_cache_key, $state_code, REDIS_30_DAYS);
            }

            $api_arr = array("key"=>$api_key,"query"=>$loc_query);
            //$api_arr = array("key"=>"89b9edaa50807c4ebd630273edfd568d","query"=>"22.719568,75.857727");  //done done
            $result  = validate_location_api($api_arr);

            if(!empty($result['data']) && isset($result['data']['0']['region_code']) && isset($result['data']['0']['country_code'])){
                $user_country = $result['data']['0']['country_code'];
                $user_state = $result['data']['0']['region_code'];

                $state_country_id_cache_key = 'state_country_id';
                $ct_st_ids = $this->get_cache_data($state_country_id_cache_key);
                if(!$ct_st_ids){
                    $this->load->model("user/User_model");
                    $ct_st_ids = $this->User_model->get_country_state_ids();
                    $this->set_cache_data($state_country_id_cache_key, $ct_st_ids, REDIS_30_DAYS);
                }
                $country_id  =  $ct_st_ids[array_search($user_country, array_column($ct_st_ids, 'country_code'))]['country_id'];
                $state_id    =  $ct_st_ids[array_search($user_state, array_column($ct_st_ids, 'state_code'))]['state_id'];

                $country_code = explode(',', $country_code);
                $state_code = array_column($state_code, 'pos_code');
                $this->api_response_arry['header']['Access-Control-Expose-Headers'] = 'banned_cs';
                $this->api_response_arry['header']['banned_cs'] = $country_id.'_'.$state_id;
               if($app_data['site_access'] == 1  && $method_name == 'join_game' && $entry_fee > 0 && (!in_array($country_id ,$country_code) ||  in_array($user_state, $state_code))) {
                   $this->api_response_arry['response_code'] = 403;
                   $this->api_response();
                }elseif($app_data['site_access'] == 0 && (!in_array($country_id ,$country_code) ||  in_array($user_state, $state_code) )){
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
        if($allow_spanish == 1){
            $language_list['es'] = 'spanish';
            $app_language_list['es'] = 'española';
        }

        $this->config->set_item('language_list',$language_list);

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
     * return service api response data
     * @return json array
     */
    public function api_response() {
        $output = array();
        $output['service_name'] = $this->api_response_arry['service_name'];
        $output['message'] = $this->api_response_arry['message'];
        $output['global_error'] = $this->api_response_arry['global_error'];
        $output['error'] = $this->api_response_arry['error'];
        $output['data'] = $this->api_response_arry['data'];
        $output['response_code'] = $this->api_response_arry['response_code'];

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
     * Retrieve the validation errors array and send as response.
     * @return none
     */
    public function send_validation_errors($return_only = FALSE) {
        $errors = $this->form_validation->error_array();
        $message = $errors[array_keys($errors)[0]];
        if (empty($errors)) {
            $message = $this->lang->line('all_required');
        }
      
        $return['response_code'] = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;     
        $return['service_name'] = '';
        $return['message'] =$message ;
         $return['error'] = $errors;
        $return['global_error'] = '';
        $return['data'] = '';

        if (!$this->input->post()) {
            $return['global_error'] = $this->lang->line('global_error');
        }

        if ($return_only === TRUE)
            return $return;

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

    /**
     * Prepares for basic authentication
     *
     * @access protected
     * @return void
     */
    protected function _custom_prepare_basic_auth($auth_key = FALSE) {
        if (!$this->auth_key && $auth_key){
            $this->auth_key = $auth_key;
        }

        $key = $this->auth_key;
        $role = 1;
        if ($this->auth_key_role) {
            $role = $this->auth_key_role;
        }
        $this->load->model("user/User_nosql_model");
        if ($role == 2) {
            //admin
            $key_detail = $this->User_nosql_model->select_one_nosql(ADMIN_ACTIVE_LOGIN, array(AUTH_KEY => $key));
            if (empty($key_detail)) {
                $this->load->model("user/User_model");
                $key_detail = $this->User_model->check_user_key_admin($key);
                //get access list
                $data = $this->User_model->get_admin_access_list($key_detail['user_id']);
                $key_detail[AUTH_KEY] = $key;
                if(!empty($data)){
                    $key_detail = array_merge($key_detail, $data);
                }
                $this->User_nosql_model->insert_nosql(ADMIN_ACTIVE_LOGIN, $key_detail);
            }
        } else {
            //user
            $key_detail = $this->User_nosql_model->select_one_nosql(ACTIVE_LOGIN, array(AUTH_KEY => $key));
            if (empty($key_detail)) {
                $this->load->model("user/User_model");
                $key_detail = $this->User_model->check_user_key($key);
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
                    $nosql_data[AUTH_KEY] = $key;
                    $key_detail[AUTH_KEY] = $key;
                    $this->User_nosql_model->insert_nosql(ACTIVE_LOGIN, $nosql_data);
                }
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
            }

            if (isset($key_detail['role']) && $key_detail['role'] == 2) {
                $this->admin_id = $key_detail['admin_id'];
                $this->admin_role = $key_detail['admin_role'];
                $this->access_list = $key_detail['access_list'];
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
     * Used for handle errors
     * @param array $response
     * @return json array
     */
    public function handle_error($response) {
        if (!empty($response['response_code']) && $response['response_code'] !== 200) {
            $this->api_response_arry = $response;
            $this->api_response();
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
        $cache_data = $this->cache->get($cache_key);
        if (is_array($cache_data)) {
            return $cache_data;
        } else {
            return array();
        }
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

    public function delete_cache_and_bucket_cache($data_arr=array())
    {
        $bucket_data = isset($this->app_config['bucket_data']['key_value']) ? $this->app_config['bucket_data']['key_value'] : '0';
        if($bucket_data && !empty($data_arr)){
            $sports_ids = !empty($data_arr['sports_ids']) ? $data_arr['sports_ids'] : array(CRICKET_SPORTS_ID);
            $file_name = $data_arr['file_name'];
            if(isset($data_arr['lang_file']) && $data_arr['lang_file'] == 1){
                $languages = $this->config->item('language_list');
                foreach($languages as $lang_abbr => $lang_value)
                {
                    //for delete s3 bucket file
                    foreach($sports_ids as $sports_id){
                        $this->push_s3_data_in_queue($file_name.$sports_id.'_'.$lang_abbr,array(),"delete");
                        if(!isset($data_arr['ignore_cache']) || $data_arr['ignore_cache'] != "1"){
                            $this->delete_cache_data($file_name.$sports_id.'_'.$lang_abbr);
                        }
                    }
                }
            }else{
                //for delete s3 bucket file
                foreach($sports_ids as $sports_id){
                    $this->push_s3_data_in_queue($file_name.$sports_id,array(),"delete");
                    if(!isset($data_arr['ignore_cache']) || $data_arr['ignore_cache'] != "1"){
                        $this->delete_cache_data($file_name.$sports_id);
                    }
                }
            }
            
        }
    }

    /**
     * Used for push s3 data in queue
     * @param string $file_name json file name
     * @param array $data api file data
     * @return 
     */
    public function push_s3_data_in_queue($file_name, $data = array(), $action = "save") {
        $bucket_data = isset($this->app_config['bucket_data_prefix']['key_value']) ? $this->app_config['bucket_data_prefix']['key_value'] : '0';
        if ($bucket_data == "0" || $file_name == "") {
            return false;
        }
        $bucket_data = array("file_name" => $file_name, "data" => $data, "action" => $action);
        $this->load->helper('queue_helper');
        add_data_in_queue($bucket_data, 'bucket');
    }

    function get_app_config_data()
    {
        //check if affiliate master entry availalbe for email verify bonus w/o referral
        $app_config_cache_key = 'app_config';
        $data = $this->get_cache_data($app_config_cache_key);
        if (!$data) {
            $this->load->model("user/User_model");
            $result = $this->User_model->get_app_config_data();
    
            foreach($result as &$row) {
                if(!empty($row['custom_data'])) {
                    $row['custom_data'] = json_decode($row['custom_data'],TRUE);
                }
            }

            $data = array_column($result,NULL,'key_name');
            $this->set_cache_data($app_config_cache_key, $data, REDIS_30_DAYS);
        }
       
        $this->app_config = $data;
    }

    /**
     * Used for get banned state list with limit
     * @param int $limit
     * @return json array
     */
    public function get_banned_state($limit=''){
        //banned_state
        $state_cache_key = 'banned_state';
        $state_list = $this->get_cache_data($state_cache_key);
        if (!$state_list) {
            $this->load->model("user/User_model");
            $state_list = $this->User_model->get_banned_state_list();
            $this->set_cache_data($state_cache_key, $state_list, REDIS_30_DAYS);
        }
        if(isset($limit) && $limit > 0){
            $state_list = array_slice($state_list, 0, $limit, true);
        }
        return $state_list;
    }

    /**
     * Admin access roles check
     * @param admin_id
     * @param module_name
     * @return array
    */
    public function admin_roles_manage($admin_id,$module_name){
        $admin_access_list = isset($this->access_list) ? $this->access_list : array();
        if($admin_access_list=="null" || $admin_access_list == "" || empty($admin_access_list)){
            return true;
        }else{
            $ignore_list = array("do_upload","remove_media","change_password","get_setting","get_front_bg_image","get_affiliate_master_data","get_pending_counts","get_conntest_filter","get_sport_leagues","get_system_user_league_list","get_system_user_reports","update_affiliate_master_data","get_game_stats","get_game_lineup_detail","get_all_transaction","get_all_upcoming_collections","get_cd_balance","get_segementation_template_list","get_filter_result_test","get_contest_detail","get_season_details","get_lineup_detail","front_bg_upload","get_system_users_for_contest","get_contest_joined_system_users","reset_front_bg_image","join_system_users","coin_distributed_graph","user_coin_redeem_graph","get_reward_list","get_reward_history","get_reward_list_by_status","approve_reward_request","add_reward","export_reward_list_by_status","update_reward_status","do_upload_reward_image","update_finance_data");
            $admin_access_json = json_decode($admin_access_list);
            if(!in_array($module_name,$admin_access_json)){
                if(in_array($this->router->method,$ignore_list)){
                    //echo "this is ignore";exit;
                }else{
                    $this->api_response_arry['service_name']  = 'admin_roles_manage';
                    $this->api_response_arry['message']       = "You have not access for the ".ucwords($module_name)." module please contact admin.";
                    $this->api_response_arry['global_error']  = 'Module access';
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response();
                }
            }
        }
    }

    /**
     * Used for get active sports list
     * @param void
     * @return array
    */
    public function get_sports_list(){
        $cache_key = 'sports_list';
        $sports_list = $this->get_cache_data($cache_key);
        if(!$sports_list){
            $this->load->model('admin/League_model');
            $sports_list = $this->League_model->get_sport_list();
            $this->set_cache_data($cache_key, $sports_list, REDIS_30_DAYS);
        }
        return $sports_list;
    }

    /**
     * get converted date acc to client time zone.
     * @return 
     */
	public function _get_client_dates($format = 'Y-m-d',$to_utc=2)
	{
		if(isset($_POST['from_date']) && $_POST['from_date'] != ""){
			$start_date_str = date('Y-m-d',strtotime($_POST['from_date'])).' 00:00:00';
			$temp_convert_start = get_timezone(strtotime($start_date_str),$format,$this->app_config['timezone'],1,$to_utc);
			$_POST['from_date'] = $temp_convert_start['date'];
		}else if(isset($_GET['from_date']) && $_GET['from_date'] != ""){
			$to_utc=1;
			$start_date_str = date('Y-m-d',strtotime($_GET['from_date'])).' 00:00:00';
			$temp_convert_start = get_timezone(strtotime($start_date_str),$format,$this->app_config['timezone'],1,$to_utc);
			$_POST['from_date'] = $_GET['from_date'] = $temp_convert_start['date'];
		}

		if(isset($_POST['to_date']) && $_POST['to_date'] != ""){
			$end_date_str = date('Y-m-d',strtotime($_POST['to_date'])).' 23:59:59';
			$temp_convert_end = get_timezone(strtotime($end_date_str),$format,$this->app_config['timezone'],1,$to_utc);
			$_POST['to_date'] = $temp_convert_end['date'];
		}else if(isset($_GET['to_date']) && $_GET['to_date'] != ""){
			$to_utc=1;
			$end_date_str = date('Y-m-d',strtotime($_GET['to_date'])).' 23:59:59';
			$temp_convert_end = get_timezone(strtotime($end_date_str),$format,$this->app_config['timezone'],1,$to_utc);
			$_POST['to_date'] = $_GET['to_date'] = $temp_convert_end['date'];
		}
		// echo "rest con";print_r($_POST);die;
		return;
	}
}

/* End of file MYREST_Controller.php */
/* Location: ./application/controllers/MYREST_Controller.php */