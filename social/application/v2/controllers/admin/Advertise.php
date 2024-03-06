<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All User related views rendering functions
 * @package    Users
 * @author     Ashwin kumar soni : 01-10-2014
 * @version    1.0
 */

class Advertise extends MY_Controller {

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        if ($this->session->userdata('AdminLoginSessionKey') == '')
            redirect('admin');
        $this->show_date_filter = false;
        $this->base_controller = get_class($this);
        //$this->load->model('admin/users_model');
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
        $data['content_view'] = 'admin/banner/banner_list';
        $this->page_name = "banner";
        $this->load->view($this->layout, $data);
    } 
    
    function banner() {
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings"); 
        /* View File */
        $data['content_view'] = 'admin/banner/banner_list';
        $this->page_name = "banner";
        $this->load->view($this->layout, $data);
    }
    public function add_banner($banner_id = '') {
        $path = base_url('assets/admin/js') . '/ckfinder';
       // editor($path);
        $data = array(); 
        $data['global_settings'] = $this->config->item("global_settings"); 
        /* View File */
        $data['content_view'] = 'admin/banner/banner_add_edit';
        $data['BlogID'] = $banner_id;
        $this->page_name = "banner";
        $this->load->view($this->layout, $data);
    } 
    
    public function default_image($banner_id = '') { 
        $data = array(); 
        $data['global_settings'] = $this->config->item("global_settings"); 
        /* View File */
        $data['content_view'] = 'admin/banner/default_banner';
        $data['BlogID'] = $banner_id;
        $this->page_name = "banner";
        $this->load->view($this->layout, $data);
    }
}

//End of file users.php
