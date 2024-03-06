<?php

/**
 * Description of Discover
 *
 * @author nitins
 */
class Discover extends MY_Controller
{

    public $page_name = "";

    public function __construct()
    {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->show_date_filter = false; die();
        if ($this->session->userdata('AdminLoginSessionKey') == '')
        {
            redirect();
        }
    }

    public function index()
    {
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/discover/discover';
        $this->page_name = "discover";

        $this->load->view($this->layout, $data);
    }
}
