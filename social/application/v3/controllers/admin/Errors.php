<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Errors extends CI_Controller
{
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function blockedip(){
        $this->load->view('admin/error/blockedip');
                
        //For allowed/blocked ips list
        $IpSetting = $this->set_allowed_ips_list();
        if(!in_array(getClientIP(), $IpSetting['UserIps']) && !in_array(GLOBAL_IP, $IpSetting['UserIps'])){
            redirect('usersite/sitemap');
        }
    }
    
    public function site_maintenance(){
        $this->load->model(array('admin/adminconfig_model'));
        
        //For allowed/blocked ips list
        $IpSetting = $this->set_allowed_ips_list();
        if(!in_array(getClientIP(), $IpSetting['UserIps']) && !in_array(GLOBAL_IP, $IpSetting['UserIps'])){
            redirect('blockedip');
        }
        
        
        $this->load->view('admin/error/site_down_for_maintenance');
    }
    
    public function access_denied(){
        $this->load->view('admin/error/access_denied');
    }
    
    public function resetipsetting(){
        $this->load->model(array('admin/ipsetting_model'));
        
        $dataArr = array();
        $dataArr['StatusID'] = 2;
        $IpSetting = $this->ipsetting_model->updateAllowedIpAddress($dataArr,1);
        
        //For delete exisitng ip setting cache data
        deleteCacheData('IpSettings');
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
    
}//End of file error.php