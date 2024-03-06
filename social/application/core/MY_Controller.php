<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');


/*if($_SERVER['HTTP_HOST'])
{
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
}*/
require_once APPPATH . '/libraries/REST_Controller.php';
class MY_Controller extends CI_Controller {

    public $layout = "<br><br>Please don't forget to set a layout for this page. <br>Layout file must be kept in views/layout folder ";
    public $data = array("content_view" => "<br><br>Please select a content view for this page");
    public $base_controller = "";
    public $title = "";
    public $page_js = array();
    public $DeviceType;
    public $post_data;
    public $show_date_filter = true;
    
    function __construct(){
        parent::__construct();

        $this->load->driver('cache', array('adapter' => CACHE_ADAPTER, 'backup' => 'file'));
        
        $this->load->model(array('admin/adminconfig_model'));
                
        //For load admin configuration setting data from DB        
        $configData = $this->adminconfig_model->setConfigurationSetting();
        $this->config->set_item('global_settings', $configData);
        
        $languageArr = getLanguageList();
        
        if(get_cookie('site_language') == "" && isset($languageArr[$configData['culture_info']]) && $languageArr[$configData['culture_info']] != ""){
            $this->config->set_item('language', str_replace(" ","_",strtolower($languageArr[$configData['culture_info']])));
        }
        
        //For load language and set selected language variables
        $language = str_replace(" ","_",strtolower(get_cookie('site_language')));
        if($language != $this->config->item('language')) {
            if($language !=""){
                $this->config->set_item('language', $language);
            }
            $loaded = $this->lang->is_loaded;
            $this->lang->is_loaded = array();
            foreach($loaded as $key=>$val) {
                $this->load->language(str_replace("_lang.php","",$key), $language);
            }
        }//End language code
                
        //For set smtp setting details
        $smtpData = $this->adminconfig_model->setSmtpSettingDetails();
        $this->config->set_item('smtp_settings', $smtpData);
                
        //For allowed/blocked ips list
        $IpSetting = $this->adminconfig_model->setAllowedIpsList();
        if(!in_array(getClientIP(), $IpSetting['AdminIps']) && !in_array(GLOBAL_IP, $IpSetting['AdminIps'])){
            redirect('blockedip');
        }
        
        $this->base_controller = get_class($this);
        $this->layout = $this->config->item("admin_layout");
        $this->data['title'] = $this->config->item('title');
        
        //For set Device Type For rights permission
        if(isset($this->post_data['DeviceType'])) $this->DeviceType = $this->post_data['DeviceType']; else $this->DeviceType = '1';        
        
    }

    function __destruct(){
    }    

}

include(APPPATH.'core/Common_API_Controller.php');
include(APPPATH.'core/Common_Controller.php');
include(APPPATH.'core/Admin_API_Controller.php');

/* End of file MY_Controller.php */
/* Location: application/core/MY_Controller.php */


