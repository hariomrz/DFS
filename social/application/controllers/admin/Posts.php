<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of costumes
 *
 * @author nitins
 */
class Posts extends MY_Controller
{

    public $page_name = "posts";

    public function __construct()
    {
        parent::__construct();
        $this->base_controller = get_class($this);

        if ($this->session->userdata('AdminLoginSessionKey') == '')
        {
            redirect();
        }

        $this->lang->load('posts');
    }

    public function index()
    {
        $data['global_settings'] = $this->config->item("global_settings");
        $data['content_view'] = 'admin/posts/flag_list';
        $this->load->view($this->layout, $data);
    }

}
