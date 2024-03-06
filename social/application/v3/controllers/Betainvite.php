<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Betainvite extends CI_Controller
{
    public $page_name = 'betainvite';
    
    public function __construct()
    {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->layout = $this->config->item("betainvite_error_layout");
        
        //For load admin configuration setting data from DB
        $this->load->model(array('admin/analytics_model'));
        $configData = $this->set_configuration_setting();
        $this->config->set_item('global_settings', $configData);
        
        //For allowed/blocked ips list
        $IpSetting = $this->set_allowed_ips_list();
        if(!in_array(getClientIP(), $IpSetting['UserIps']) && !in_array(GLOBAL_IP, $IpSetting['UserIps'])){
            redirect('blockedip');
        }
        
        //For set google analytics code for frontend
        $analyticData = $this->analytics_model->getAnalyticsProviders();
        $analyticArr = array();
        foreach($analyticData as $analytic){
            $analyticArr[] = $analytic['AnalyticsData'];
        }
        if(is_array($analyticArr)){
            $this->config->set_item('AnalyticsCode', $analyticArr);
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
        
    public function index(){
        $this->data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $this->data['content_view'] = 'betainvite/betainvite';
        $this->page_name = "betainvite";

        $this->load->view($this->layout, $this->data);       
    }

}//End of file betainvite.php