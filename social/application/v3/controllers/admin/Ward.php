<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Ward extends MY_Controller {

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
     * Function for show Users count ward wise
     * Parameters : 
     * Return : Load View files
     */
    public function index() {
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/ward/user_count';
        $this->page_name = "ward";
        $this->show_date_filter = false;
        $this->load->view($this->layout, $data);
    }

    /**
     * Function for show engement ward wise
     * Parameters : 
     * Return : Load View files
     */
    public function engagement() {
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/ward/engagement';
        $this->page_name = "ward";
        $this->show_date_filter = false;
        $this->load->view($this->layout, $data);
    }

    /**
     * Function for show Users Listing page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function locality() {
        
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");
        
        /* View File */
        $data['content_view'] = 'admin/ward/list';
        $this->page_name = "ward";
        $this->show_date_filter = false;
        $this->load->view($this->layout, $data);
    }
    
}

//End of file users.php
