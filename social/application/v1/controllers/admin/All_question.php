<?php

/**
 * Description of Daily_digest
 *
 */
class All_question extends MY_Controller
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
        $data['content_view']       = 'admin/all_question/all_question';
        $this->page_name            = "all_question";

        $this->load->view($this->layout, $data);
    }

    public function create() {

        $data = array();
        $data['global_settings']    = $this->config->item("global_settings");

        /* View File */
        $data['content_view']       = 'admin/daily_digest/landing_page';
        $this->page_name            = "daily_digest";

        $this->load->view($this->layout, $data);
    }

}
