<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All User related views rendering functions
 * @package    Roles
 * @author     Girish Patidar : 18-02-2015
 * @version    1.0
 */

class Roles extends MY_Controller {

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/roles_model');
        
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect();
        }
    }

    /**
     * Function for show roles Listing page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function index() {
        
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('list_roles'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/roles/roles';
        $this->page_name = "roles";

        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for show role permissions page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function managepermission() {
       
        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('list_permissions'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/roles/managepermission';
        $this->page_name = "rolepermission";

        $this->load->view($this->layout, $data);
    }
    
    /**
     * Function for show users in roles & permission section page in admin
     * Parameters : 
     * Return : Load View files
     */
    public function manageuser() {

        //Check logged in access right and allow/denied access
        if(!in_array(getRightsId('list_role_users'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/roles/manageuser';
        $this->page_name = "manageroleuser";

        $this->load->view($this->layout, $data);
    }

}

//End of file roles.php