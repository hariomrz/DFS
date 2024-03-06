<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * 
 * Controller class 
 * @package    blog
 * @author     V-INFOTECH
 * @version    1.0
 */

class Announcement extends MY_Controller {
    public $page_name = 'announcement';
    public $dashboard = 'announcement';

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/users_model');

        if ($this->session->userdata('AdminLoginSessionKey') == '')
        {
            redirect('admin');
        }
    }

    public function index() {
        $this->data['title'] = 'Manage Blog';
        $this->data['content_view'] = 'admin/announcement/list';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }

}

?>
