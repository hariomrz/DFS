<?php

/**
 * Description of Banner
 */
class Banner extends MY_Controller
{

    public $page_name = "";

    public function __construct()
    {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->show_date_filter = false;
        if ($this->session->userdata('AdminLoginSessionKey') == '')
        {
            redirect();
        }
    }

    public function index() {      

        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/banner/list';
        $this->page_name = "banner";

        $this->load->view($this->layout, $data);
    }
    
    public function add() {
        $path = base_url('assets/admin/js') . '/ckfinder';
       // editor($path);
        $data = array(); 
        $data['global_settings'] = $this->config->item("global_settings"); 
        /* View File */
        $data['content_view'] = 'admin/banner/add';
        $this->page_name = "banner";
        $this->load->view($this->layout, $data);
    }     
}
