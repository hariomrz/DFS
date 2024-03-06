<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All User related views rendering functions
 * @package    Users
 * @version    1.0
 */

class Crm extends MY_Controller {

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/users_model');
            
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect('admin');
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

    /**
     * Function for show Users who are following the most users
     * Parameters : 
     * Return : Load View files
     */
    public function top_following() {
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/follow/top_following';
        $this->page_name = "top_following";
        $this->show_date_filter = false;
        $this->load->view($this->layout, $data);
    }

    /**
     * Function for show people who are being followed the most
     * Parameters : 
     * Return : Load View files
     */
    public function top_followed() {
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/follow/top_followed';
        $this->page_name = "top_follow";
        $this->show_date_filter = false;
        $this->load->view($this->layout, $data);
    }
    
}

//End of file users.php
