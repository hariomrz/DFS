<?php

/**
 * Name : Album 
 * Description : admin will create, list and edit album,in this album users can upload photos and  *  like,comments
 */
class Album extends MY_Controller
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
        $data['content_view']       = 'admin/album/list';
        $this->page_name            = "album";

        $this->load->view($this->layout, $data);
    }
    public function album_detail() {

        $data = array();
        $data['global_settings']    = $this->config->item("global_settings");

        /* View File */
        $data['content_view']       = 'admin/album/album_detail';
        $this->page_name            = "album_detail";

        $this->load->view($this->layout, $data);
    }
    public function album_list() {

        $data = array();
        $data['global_settings']    = $this->config->item("global_settings");

        /* View File */
        $data['content_view']       = 'admin/album/album_list';
        $this->page_name            = "album_list";

        $this->load->view($this->layout, $data);
    }

    public function create() {

        $data = array();
        $data['global_settings']    = $this->config->item("global_settings");

        /* View File */
        $data['content_view']       = 'admin/album/create';
        $this->page_name            = "create_album";

        $this->load->view($this->layout, $data);
    }

}
