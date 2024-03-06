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
        $this->show_date_filter = false; 
        if ($this->session->userdata('AdminLoginSessionKey') == '')
        {
            redirect('admin');
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

    public function trending_tags()
    {
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/discover/trending_tags';
        $this->page_name = "trending_tags";

        $this->load->view($this->layout, $data);
    }

    public function top_followed_tags() {
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/discover/top_followed';
        $this->page_name = "top_followed";

        $this->load->view($this->layout, $data);
    }

    public function mute_tags() {
        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/discover/muted_tags';
        $this->page_name = "mute_tags";

        $this->load->view($this->layout, $data);
    }
}
