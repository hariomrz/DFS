<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Controller class for group
 * @package    team
 * @author     V-INFOTECH
 * @version    1.0
 */

class Rules extends MY_Controller {

	public $page_name = 'rules';
    public $dashboard = 'rules';

	public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model(array('admin/users_model','admin/rules_model'));
            
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect();
        }
    }

    public function index()
    {
        $this->data['title'] = 'Rules';
        $this->data['content_view'] = 'admin/rules/list';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }
}

?>