<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All User related views rendering functions
 * @package    Emailsettings
 * @author     Girish Patidar : 23-01-2015
 * @version    1.0
 */

class Emailsetting extends MY_Controller {

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/emailsetting_model');
        
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect();
        }
    }

    /**
     * Function for show email smtp setting Listing page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function index() {

        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('smtp_settings'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/emailsetting/smtplist';
        $this->page_name = "emailsetting";

        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for add smtp settings from admin
     * Parameters : 
     * Return : Load View files
     */
    public function email_setting_authentication() {
        
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('smtp_settings_save_add_event'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        $data['emailSettingId'] = '';
        
        if(isset($_GET['id']) && is_numeric($_GET['id'])){
            $setting_id = $_GET['id'];
            $data['emailSettingId'] = $setting_id;
        }
        
        /* View File */
        $data['content_view'] = 'admin/emailsetting/add_smtp_setting';
        $this->page_name = "smtpsetting";

        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for show email type Listing page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function smtpemails() {

        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('smtp_emails'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/emailsetting/smtpemails';
        $this->page_name = "emailsetting";

        $this->load->view($this->layout, $data);
    }

}

//End of file emailsetting.php