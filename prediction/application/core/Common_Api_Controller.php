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
        //this condition for old session key
        if (!$this->auth_key) {
            $this->auth_key = $this->input->get_request_header(PREVIOUS_AUTH_KEY);
        }
        $this->headers[AUTH_KEY] = $this->auth_key;

        if (!empty($this->input->get_request_header(AUTH_KEY_ROLE))) {
            $this->auth_key_role = $this->input->get_request_header(AUTH_KEY_ROLE);
            $this->headers[AUTH_KEY_ROLE] = $this->auth_key_role;
        }
        
        $this->get_app_config_data();
        $this->set_lang();
        //Do your magic here
        if ($this->custom_auth_override === FALSE || $this->auth_key) {
            $this->_custom_prepare_basic_auth();
        }
        $this->global_sports = $this->get_sports_list();

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
        if (!$this->auth_key && $auth_key)
            $this->auth_key = $auth_key;

        $key = $this->auth_key;
        $role = 1;
        if ($this->auth_key_role) {
            $role = $this->auth_key_role;
        }
        $this->load->model("user/User_nosql_model");
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

        if (!empty($key_detail)) {
            if (isset($key_detail['role']) && $key_detail['role'] == 1) {
                $this->email = $key_detail['email'];
                $this->user_id = $key_detail['user_id'];
                $this->user_name = $key_detail['user_name'];
                $this->user_unique_id = $key_detail['user_unique_id'];
                $this->referral_code = $key_detail['referral_code'];
                $this->phone_no = $key_detail['phone_no'];
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
     * Used for check collection status
     * @param array $post_data
     * @return boolean
     */
    public function check_collection_status() {
        if (in_array($this->input->post('status'), array(0, 1, 2))) {
            return TRUE;
        }
        $this->form_validation->set_message('check_collection_status', $this->lang->line("invalid_status"));
        return FALSE;
    }

    /**
     * Used for validate leaderboard type
     * @param array $post_data
     * @return boolean
     */
    public function valid_leaderboard_type() {
        $valid_leaderboard_type = array('1', '2', '3', '4', '5', '6');
        $input_leaderboard_type = $this->input->post('leaderboard_type');
        if (!empty($input_leaderboard_type) && in_array($input_leaderboard_type, $valid_leaderboard_type)) {
            return TRUE;
        }
        $this->form_validation->set_message('valid_leaderboard_type', $this->lang->line("valid_leaderboard_type"));
        return FALSE;
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
     * Used for push s3 data in queue
     * @param string $file_name json file name
     * @param array $data api file data
     * @return 
     */
    public function push_s3_data_in_queue($file_name, $data = array(), $action = "save") {
        if (BUCKET_STATIC_DATA_ALLOWED == "0" || $file_name == "") {
            return false;
        }
        $bucket_data = array("file_name" => $file_name, "data" => $data, "action" => $action);
        $this->push_data_in_queue($bucket_data, 'bucket');
    }

    /**
     * Used for push data in queue
     * @param string $file_name json file name
     * @param array $data api file data
     * @return 
     */
    public function push_data_in_queue($data, $queue_name) {
        if (empty($queue_name)) {
            return true;
        }

        $this->load->helper('queue_helper');
        rabbit_mq_push($data, $queue_name);
    }

    /**
     * Used for get user balance
     * @param void
     * @return array
     */
    public function get_user_balance() {
        $user_bal_cache_key = "user_balance_" . $this->user_id;
        $user_balance = $this->get_cache_data($user_bal_cache_key);
        if (!$user_balance) {
            $this->load->model("user/User_model");
            $user_balance = $this->User_model->get_user_balance($this->user_id);
            $this->set_cache_data($user_bal_cache_key, $user_balance, 3600);
        }
        return $user_balance;
    }

    /**
     * Used for get sports list
     * @param void
     * @return array
     */
    private function get_sports_list() {
        $active_sports_cache = 'active_sports';
        $sports_list = $this->get_cache_data($active_sports_cache);
        if (!$sports_list) {
            $this->load->model('fantasy/Fantasy_model');
            $sports_list = $this->Fantasy_model->get_sports_list();
            //set master position in cache for 30 days
            $this->set_cache_data($active_sports_cache, $sports_list, REDIS_30_DAYS);
        }

        return $sports_list;
    }

    function get_app_config_data()
    {
        //check if affiliate master entry availalbe for email verify bonus w/o referral
        $app_config_cache_key = 'app_config';
        $data = $this->get_cache_data($app_config_cache_key);
        if (!$data) {
            $this->load->model("user/User_model");
            $result = $this->User_model->get_all_table_data(APP_CONFIG,"*");
    
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

     protected function validate_banned_state(){
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
            if(empty($loc_check)){
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
            //$api_arr = array("key"=>"89b9edaa50807c4ebd630273edfd568d","query"=>"15.828250,80.357132");  //done done
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
             if(!in_array($country_id ,$country_code) ||  in_array($user_state, $state_code)) {
                    $this->api_response_arry['response_code'] = 403;
                    $this->api_response();
                }
            }
            
        }

        return true;
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
        if($allow_kannada == 1){
            $language_list['es'] = 'Spanish';
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
            define('ANDROID_APP_LINK', $android_app['android_app_link']);
            define('ANDROID_MIN_VER', $android_app['android_min_ver']);
            define('ANDROID_CURRENT_VER', $android_app['android_current_ver']);
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

        $allow_bank_transfer = isset($this->app_config['allow_bank_transfer'])?$this->app_config['allow_bank_transfer']['key_value']:7;
        define('ALLOW_BANK_TRANSFER', $allow_bank_transfer);

        $allow_mpesa_withdraw = isset($this->app_config['allow_mpesa_withdraw'])?$this->app_config['allow_mpesa_withdraw']['key_value']:7;
        define('ALLOW_MPESA_WITHDRAW', $allow_mpesa_withdraw);

        $allow_private_contest = isset($this->app_config['allow_private_contest'])?$this->app_config['allow_private_contest']['key_value']:7;
        define('ALLOW_PRIVATE_CONTEST', $allow_private_contest);

        $bucket_static_data_allowed = isset($this->app_config['bucket_static_data_allowed'])?$this->app_config['bucket_static_data_allowed']['key_value']:7;
        define('BUCKET_STATIC_DATA_ALLOWED', $bucket_static_data_allowed);

        $bucket_data_prefix = isset($this->app_config['bucket_data_prefix'])?$this->app_config['bucket_data_prefix']['key_value']:7;
        define('BUCKET_DATA_PREFIX', $bucket_data_prefix);

        $int_version = isset($this->app_config['int_version'])?$this->app_config['int_version']['key_value']:7;
        define('INT_VERSION', $int_version);
        
        $max_contest_bonus = isset($this->app_config['max_contest_bonus'])?$this->app_config['max_contest_bonus']['key_value']:7;
        define('MAX_CONTEST_BONUS', $max_contest_bonus);

        $currency_code = isset($this->app_config['currency_code'])?$this->app_config['currency_code']['key_value']:7;
        define('CURRENCY_CODE', $currency_code);
        define('CURRENCY_CODE_HTML', CURRENCY_CODE);
    }

    

}

/* End of file MYREST_Controller.php */
/* Location: ./application/controllers/MYREST_Controller.php */