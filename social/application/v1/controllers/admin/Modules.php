<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Controller class for group
 * @package    team
 * @author     V-INFOTECH
 * @version    1.0
 */

class Modules extends MY_Controller {

	public $page_name = 'modules';
    public $dashboard = 'modules';

	public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);

        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect();
        }
        $rights = getUserRightsData($this->DeviceType);
        if(!in_array(getRightsId('module_settings'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }
    }

    public function index()
    {
        $this->show_date_filter = false;
        $this->data['title'] = 'Install Modules';
        $this->data['content_view'] = 'admin/modules/install';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }
}

?>
