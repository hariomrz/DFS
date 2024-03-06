<?php
//require_once APPPATH . '/libraries/REST_Controller.php';
class Common_API_Controller extends REST_Controller {

    public $post_data;
    public $return              = array();
    public $allowed_languages   = array();
    
    public $UserID=0;   
    public $LoggedInGUID;
    public $LoggedInName;
    public $LoggedInProfilePicture;
    public $DeviceTypeID;
    public $IsApp=0;
    public $SourceID;
    public $RoleID;
    public $LocalityID=0;
    public $UserTypeID;
    public $can_create_poll=0;
    public $is_profile_setup=0;

    public $public_methods = array();

    protected $module_id;

    public $InputID;
    public $AppVersion='';

    public function __construct($bypass = false) {
        parent::__construct();
        if($bypass) {
            $this->bypassSetData();
            return;
        }        
        $this->_check_cors();
        $this->set_lang();
        //Load Memcached
        if(CACHE_ENABLE) {
            $this->load->driver('cache', array('adapter' => CACHE_ADAPTER, 'backup' => 'file'));
        } 
        $this->configData = $configData = $this->set_configuration_setting();
        $this->config->set_item('global_settings', $configData);
        
        //For set smtp setting details
        $smtpData = $this->set_smtp_setting_details();        
        $this->config->set_item('smtp_settings', $smtpData);
        
        //For redirect to maintenance page when site down for maintenance
        if($configData['site_down_for_maintenance'] == 1){
            $this->return = array(
                'ResponseCode' => 503,
                'Message' => lang('site_down_for_maintenance'),
                'Data' => array()
            );
            $this->response($this->return);
        }
        
        //For allowed/blocked ips list
        $IpSetting = $this->set_allowed_ips_list();        
        if(!in_array(getClientIP(), $IpSetting['UserIps']) && !in_array(GLOBAL_IP, $IpSetting['UserIps'])){
            $this->return = array(
                'ResponseCode' => 517,
                'Message' => lang('ip_blocked'),
                'Data' => array()
            );
            $this->response($this->return);
        }

        /* Gather Inputs - starts */
        $this->post_data = $this->post();
        if(empty($this->post_data)) //&& !empty($this->get())
        {
            $this->post_data = $this->get();
        }
        
        /* if(isset($this->post_data['PageNo'])) {
            $this->post_data['PageNo'] = is_integer($this->post_data['PageNo']) ? $this->post_data['PageNo'] : PAGE_NO;
        }
        if(isset($this->post_data['PageSize'])) {
            $this->post_data['PageSize'] = is_integer($this->post_data['PageSize']) ? $this->post_data['PageSize'] : PAGE_SIZE;
        }
         * 
         */
        /* Gather Inputs - ENDS */
        
        $api_method = $this->router->method;

        if($api_method!='saveFileFromUrl' && $api_method!='updatePictureWithoutId' && $api_method!='updateProfilePicture')
        {
            // To filter input for xss attack
            $this->post_data = $this->security->xss_clean($this->post_data,TRUE);
        }
        //var_dump($this->post_data);        
        $this->moduleSettings = get_module_settings();
        
        /* Getting header information and set login session key in post data*/
        $headers = $this->input->request_headers();
        //$auth_key = ucfirst(strtolower(AUTH_KEY));
        $auth_key = AUTH_KEY;
        // Check if sent from normal user
        $login_session_key = $this->get_login_session_key($auth_key, $headers);
        
        // Check if sent by admin session key
        if(!$login_session_key) {
            $login_session_key = $this->get_login_session_key('AdminLoginSessionKey', $headers);
        }
        $this->post_data[$auth_key] = $login_session_key;
        
        $method = ($this->router->method == 'index') ? NULL : '/' . $this->router->method;
        /* Define return variables - starts */
        $this->return = array('ResponseCode' => self::HTTP_OK,
            'Message'      => lang('success'),
            'Data'         => array(),
            'ServiceName'  => $this->router->class . $method
        );

        //Set REST API Validation Configuration
        $this->form_validation->set_rest_validation(TRUE, $this->post_data);


        $this->custom_auth_override = $this->_custom_auth_override_check();
        //Do your magic here
        if ($this->custom_auth_override === FALSE)
        {
            $this->_custom_prepare_basic_auth();
        }
        
        $is_device = isset($headers['IsDevice']) ? $headers['IsDevice'] : '';
        if(empty($is_device)) {
            $is_device = isset($headers['isdevice']) ? $headers['isdevice'] : '';
        }
        if(!empty($is_device)) {
            $this->IsApp = $is_device;
        }        
        
        if($this->IsApp == 1 && ENVIRONMENT != 'production') {
            @$this->db->insert('jsondata', array('URL' => current_url(), 'DataJ' => json_encode(array_merge(array("API" => $this->uri->segment(2), "IsApp" => $this->IsApp), $this->post_data))));
        }
        
    } 

    protected function _check_cors() {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Loginsessionkey, appversion");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");                
        // If the request HTTP method is 'OPTIONS', kill the response and send it to the client
        if ($this->input->method() === 'options') {
            exit;
        }
    }
    public function set_lang($lang=FALSE) {
        $language_list = $this->config->item('language_list');
        if(!$lang) {
            //Language code for rest services
            if (is_array($this->response->lang)) {
                $header_language = strtolower($this->response->lang[0]);
            } else {
                $header_language = strtolower($this->response->lang);
            }           

            if ($header_language && isset($language_list[$header_language])) {
                $lang = $language_list[$header_language];
            } else {
                $lang = $this->config->item('language');
            }
        } else {
            if($lang && isset($language_list[$lang])) {
                $lang = $language_list[$lang];
            } else if ($lang && in_array($lang, $language_list)) {
                $lang = trim($lang);
            } else {
                $lang = $this->config->item('language');
            }
        }
        
        if($lang != $this->config->item('language')) {
            $this->config->set_item('language', $lang);
            $loaded = $this->lang->is_loaded;
            $this->lang->is_loaded = array();
            foreach($loaded as $key=>$val) {
                $this->load->language(str_replace("_lang.php","",$key), $lang);
            }
        }//End language code
        return TRUE;
    }
    /*
    | @Function - Use to Validate DeviceType
    | 
    */
    function validate_DeviceType($DeviceType){
        $this->db->select("Name"); /* select Device Types from database*/
        $query = $this->db->get(DEVICETYPES);
        if($query->num_rows()>0){
            $Data=$query->result_array();
        }
        foreach ($Data as $Value) {
           $result[] = $Value['Name'];
        }
        
        $this->form_validation->set_message('validate_DeviceType',lang('valid_deviceType'));
        /*Check DeviceType is valid or not*/
        if(!in_array($DeviceType,$result)){           
            return FALSE;
        }
        return TRUE;
    }

    // Callback function to validate password 
    function validate_password($password)
    {
        $regex = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{6,}$/";
        if (preg_match($regex, $password)) {
           return TRUE;
        }
        else
        {
            $this->form_validation->set_message('validate_password', lang('password_validation_msg'));
            return FALSE;
        }
    }

    /**
     * Check if there is a specific auth type set for the current class/method/HTTP-method being called
     *
     * @access protected
     * @return bool
     */
    protected function _custom_auth_override_check()
    {
        // Assign the class/method auth type override array from the config
        $auth_override_class_method = $this->config->item('auth_override_class_method');

        // Check to see if the override array is even populated
        if (!empty($auth_override_class_method))
        {
            // check for wildcard flag for rules for classes
            if (!empty($auth_override_class_method[$this->router->class]['*'])) // Check for class overrides
            {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method[$this->router->class]['*'] === 'none')
                {
                    return TRUE;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method[$this->router->class]['*'] === 'custom')
                {
                    $this->_custom_prepare_basic_auth();

                    return TRUE;
                }
            }

            // Check to see if there's an override value set for the current class/method being called
            if (!empty($auth_override_class_method[$this->router->class][$this->router->method]))
            {
                // None auth override found, prepare nothing but send back a TRUE override flag
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'none')
                {
                    return TRUE;
                }

                // Basic auth override found, prepare basic
                if ($auth_override_class_method[$this->router->class][$this->router->method] === 'custom')
                {
                    if(!empty($this->post_data[AUTH_KEY])) 
                    {
                        $this->_custom_prepare_basic_auth();
                    }
                    else
                    {
                      return TRUE;  
                    }
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
    protected function _custom_prepare_basic_auth() {        
        $this->load->library('mongo_db');
        $key = $this->post_data[AUTH_KEY];
        $key_detail = array();
        try {
            $key_detail = $this->mongo_db->where(array( DFS_AUTH_KEY => $key))->find_one('active_login');
            if(!empty($key_detail)){
                $key_detail = $key_detail[0];
            }
        } catch (Exception $e) {
            log_message("error", "Unable to connect to MongoDB: {$e->getMessage()}");
        }
        $logged_user_data = array('ResponseCode' => self::HTTP_UNAUTHORIZED, 'Message' => lang("invalid_key"));
        
        if(!empty($key_detail)) {
            $this->DeviceTypeID             = $key_detail['device_type'];
            if(in_array($this->DeviceTypeID, array(1,2))) {
                $this->IsApp                    = 1;
            }
            $this->UserID                   = $key_detail['user_id'];
            $this->LoggedInGUID             = $key_detail['user_unique_id'];
            $this->LoggedInName             = $key_detail['user_name'];
            //$this->LoggedInProfilePicture   = $key_detail['LoggedInProfilePicture'];
            $this->SourceID                 = 1;
            $this->UserTypeID                = 3;
            $this->RoleID                   = isset($key_detail['RoleID']) ? $key_detail['RoleID'] : '';
            $this->load->model(array('users/login_model'));
            $this->RightIDs                 = $this->login_model->get_user_rights($this->UserID);
	        if(empty($this->LocalityID)) {
                $this->LocalityID               = 0;
            } 
            $this->can_create_poll          = 1;
            $this->is_profile_setup          = 1;
            
	       
            if($this->settings_model->isDisabled(30)){ //check for poll module
                $this->can_create_poll          = 0;
            }      
                
        } else {
            $this->response($logged_user_data);
        }
    }  
    
    function check_module_status($moduleID){
        if ($this->settings_model->isDisabled($moduleID)) {
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
    }

    function check_user_status($userID){
        $status_id = get_detail_by_id($userID,3,'StatusID');
        if ($status_id != 2) {
            $this->return['Message'] = lang('unauthorized_user');
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
    }
    
    function get_login_session_key($auth_key, $headers) {
        
        if(isset($headers['LocalityID']) && is_numeric($headers['LocalityID'])){
            $this->LocalityID = $headers['LocalityID'];
        } else if(isset($headers['localityid']) && is_numeric($headers['localityid'])) {
            $this->LocalityID = $headers['localityid'];    
        }
        
        if(isset($headers['appversion'])){
            $this->AppVersion = $headers['appversion'];
        } else if(isset($headers['APPVERSION'])) {
            $this->AppVersion = $headers['APPVERSION'];    
        }
        
        if(!empty($headers[$auth_key])) {
            $login_session_key = $headers[$auth_key];    
        } else if(!empty($this->post_data[$auth_key])) {
            $login_session_key = $this->post_data[$auth_key];
        } else if(!empty($headers[strtolower ($auth_key)])) {
            $login_session_key = $headers[strtolower ($auth_key)];    
        } else {
            foreach ($headers as $headerKey => $headerVal) {
                if(strtolower($headerKey) == strtolower($auth_key)) {
                    return $headerVal;
                }
            }            
            $login_session_key = '';
        }
        if(isset($headers['IsDevice']) && is_numeric($headers['IsDevice'])){
            $this->IsApp = $headers['IsDevice'];
        }
        
        return $login_session_key;
    }
    
    function bypassSetData() {
        
        //Load Memcached
        if(CACHE_ENABLE)
        {
            $this->load->driver('cache', array('adapter' => CACHE_ADAPTER, 'backup' => 'file'));
        }     
        //For load admin configuration setting data from DB
        $this->configData = $configData = $this->set_configuration_setting();
        $this->config->set_item('global_settings', $configData);
        
        //For set smtp setting details
        $smtpData = $this->set_smtp_setting_details();        
        $this->config->set_item('smtp_settings', $smtpData);
        
        
        /* Gather Inputs - starts */
        $this->post_data = $this->post();
        if(empty($this->post_data)) //&& !empty($this->get())
        {
            $this->post_data = $this->get();
        } 

        $headers = $this->input->request_headers();
        $auth_key = AUTH_KEY;
        $login_session_key = $this->get_login_session_key($auth_key, $headers);
        
        // Check if sent by admin session key
        if(!$login_session_key) {
            $login_session_key = $this->get_login_session_key('AdminLoginSessionKey', $headers);
        }
        $this->post_data[$auth_key] = $login_session_key;

        //Set REST API Validation Configuration
        $this->form_validation->set_rest_validation(TRUE, $this->post_data);
        
        
        $method = ($this->router->method == 'index') ? NULL : '/' . $this->router->method;

        /* Define return variables - starts */
        $this->return = array('ResponseCode' => self::HTTP_OK,
            'Message'      => lang('success'),
            'Data'         => array(),
            'ServiceName'  => $this->router->class . $method
        );
        
        $headers = $this->input->request_headers();
        if(isset($headers['appversion'])){
            $this->AppVersion = $headers['appversion'];
        } else if(isset($headers['APPVERSION'])) {
            $this->AppVersion = $headers['APPVERSION'];    
        }
    }
    
    function set_configuration_setting() {
        $this->load->driver('cache');
        $global_settings = $this->cache->file->get(GLOBALSETTINGS);
        if(!($global_settings)) { 
            $this->load->model(array('admin/adminconfig_model'));
            $global_settings = $this->adminconfig_model->setConfigurationSetting();
        }
        return $global_settings;  
    }
    
    function set_smtp_setting_details() {
        $this->load->driver('cache');
        $smtp_settings = $this->cache->file->get('SmtpSettings');
        if(!($smtp_settings)) { 
            $this->load->model(array('admin/adminconfig_model'));
            $smtp_settings = $this->adminconfig_model->setSmtpSettingDetails();
        }
        return $smtp_settings;  
    }
    
    function set_allowed_ips_list() {
        $this->load->driver('cache');
        $ip_settings = $this->cache->file->get('IpSettings');
        if(!($ip_settings)) { 
            $this->load->model(array('admin/adminconfig_model'));
            $ip_settings = $this->adminconfig_model->setAllowedIpsList();
        }
        return $ip_settings;  
    }
}
