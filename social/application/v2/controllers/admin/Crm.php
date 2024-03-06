<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All User related views rendering functions
 * @package    Users
 * @author     Ashwin kumar soni : 01-10-2014
 * @version    1.0
 */

class Crm extends MY_Controller {

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/users_model');
            
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect();
        }
    }

    /**
     * Function for show Users Listing page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function index() {
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/crm/users';
        $this->page_name = "crm_users";
        $this->show_date_filter = false;
        $this->load->view($this->layout, $data);
    }
    
}

//End of file users.php
