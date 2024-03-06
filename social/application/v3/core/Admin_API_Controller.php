<?php
//require_once APPPATH . '/libraries/REST_Controller.php';
class Admin_API_Controller extends REST_Controller {

    public $post_data;
    public $return = array();
    public $DeviceType;
    public $DeviceTypeID;
    public $IsApp=0;
    public $AppVersion='';
    public $LoggedInName;
    public $is_profile_setup=0;
    public function __construct() {
        parent::__construct();
        $this->_check_cors();
        //Load Memcached
        $this->load->driver('cache', array('adapter' => CACHE_ADAPTER, 'backup' => 'file'));
        
        $this->configData = $configData = $this->set_configuration_setting();
        $this->config->set_item('global_settings', $configData);
        
        //For set smtp setting details
        $smtpData = $this->set_smtp_setting_details();        
        $this->config->set_item('smtp_settings', $smtpData);
        
        //For allowed/blocked ips list
        $IpSetting = $this->set_allowed_ips_list();        
        if(!in_array(getClientIP(), $IpSetting['AdminIps']) && !in_array(GLOBAL_IP, $IpSetting['AdminIps'])){
            $this->return = array(
                'ResponseCode' => 517,
                'Message' => lang('ip_blocked'),
                'Data' => array()
            );
            $this->response($this->return);
        }
        
        /* Code by Ashvin */
        /* Gather Inputs - starts */
        $this->post_data = $this->post();
        if(empty($this->post_data)) //&& !empty($this->get())
        {
            $this->post_data = $this->get();
        }
        /* Gather Inputs - ENDS */
        
        
         /* Getting header information and set login session key in post data*/
        $headers = $this->input->request_headers();

        $auth_key = ucfirst(strtolower('AdminLoginSessionKey'));
        $headers[$auth_key] = $this->input->get_request_header("AdminLoginSessionKey");
        //$auth_key = 'AdminLoginSessionKey';
        if(!empty($headers[$auth_key]) && $headers[$auth_key]!="[object HTMLInputElement]")
        {
            $admin_login_session_key = $headers[$auth_key];    
        } 
        else if(!empty($this->post_data['AdminLoginSessionKey']))
        {
            $admin_login_session_key = $this->post_data['AdminLoginSessionKey'];
        } 
        else 
        {
            $admin_login_session_key = '';
        }
        $this->post_data['AdminLoginSessionKey'] = $admin_login_session_key;
        
        //echo "<pre>";print_r($headers);die;

        //For set Device Type For rights permission
        if(isset($this->post_data['DeviceType'])) $this->DeviceType = $this->post_data['DeviceType']; else $this->DeviceType = '1';
        $this->post_data['DeviceType'] = $this->DeviceType; 
                
        //Language code for rest services
       /* if (is_array($this->response->lang)) {
            $language = $this->response->lang[0];
        } else {
            $language = $this->response->lang;
        }
        if($language != $this->config->item('language')) {
            $this->config->set_item('language', $language);
            $loaded = $this->lang->is_loaded;
            $this->lang->is_loaded = array();
            foreach($loaded as $key=>$val) {
                $this->load->language(str_replace("_lang.php","",$key), $language);
            }
        }//End language code
        */
        $method = ($this->router->method == 'index') ? NULL : '/' . $this->router->method;

        /* Define return variables - starts */
        $this->return = array(
            'ResponseCode' => 200,
            'Message' => 'Success',
            'Data' => array(),
            'ClientError' => 0,
            'ServiceName' => $this->router->class . $method
        );
        
        $api_method = $this->router->method;
        $api_class = $this->router->class;
        if(!in_array($api_class, array('announcement', 'quiz', 'advertise')))
        {
            // To filter input for xss attack
            $this->post_data = $this->security->xss_clean($this->post_data);
        }

        //Set REST API Validation Configuration
        $this->form_validation->set_rest_validation(TRUE, $this->post_data);
    }    
    
    protected function _check_cors() {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Loginsessionkey, Adminloginsessionkey, Appversion");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        //log_message('error', $this->input->method());
        // If the request HTTP method is 'OPTIONS', kill the response and send it to the client
        if ($this->input->method() === 'options') {
            exit;
        }
    }

    function check_module_status($moduleID){
        if ($this->settings_model->isDisabled($moduleID)) {
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
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
?>
