<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All Support and Error logs related views rendering functions
 * @package    Support
 * @author     Girish Patidar : 04-03-2015
 * @version    1.0
 */

class Support extends MY_Controller {

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/support_model');
        
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect();
        }
    }

    /**
     * Function for show error log Listing page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function index() {
        
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('support_request_listing'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/support/errorloglist';
        $this->page_name = "support";

        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for view error log details in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function supportrequestview(){
        
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('support_request_listing_suppport_request_view'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/support/supportrequestview';
        $this->page_name = "support";

        $this->load->view($this->layout, $data);
    }

}
//End of file support.php