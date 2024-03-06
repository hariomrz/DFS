<?php

/**User_orientation
 *
 */
class User_orientation extends MY_Controller
{

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->show_date_filter = false;
        if ($this->session->userdata('AdminLoginSessionKey') == '') {
            redirect('admin');
        }
    }

    public function index() {

        $data = array();
        $data['global_settings']    = $this->config->item("global_settings");

        /* View File */
        $data['content_view']       = 'admin/orientation/landing_page';
        $this->page_name            = "orientation";

        $this->load->view($this->layout, $data);
    }   

}