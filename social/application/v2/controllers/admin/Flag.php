<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Description of Flag
 *
 * @author nitins
 */
class Flag extends MY_Controller
{

    public $page_name = 'flag';
    public $dashboard = 'flag';

    public function __construct()
    {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/users_model');
        $this->show_date_filter = false;
        if ($this->session->userdata('AdminLoginSessionKey') == '')
        {
            redirect();
        }
    }

    public function index()
    {
        $this->data['title'] = 'Flags';
    }

    public function users()
    {
        $this->data['title'] = 'User Flag';
        $this->data['content_view'] = 'admin/flags/users';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }

    public function pages()
    {
        if($this->settings_model->isDisabled(18)){
           redirect();
           //exit();
        }
        
        $this->data['title'] = 'Page Flag';
        $this->data['content_view'] = 'admin/flags/pages';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }

}
