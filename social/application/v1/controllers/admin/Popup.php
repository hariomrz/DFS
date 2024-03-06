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

class Popup extends MY_Controller
{

    public $page_name = 'popup';
    public $dashboard = 'popup';

    public function __construct()
    {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/users_model');

        if ($this->session->userdata('AdminLoginSessionKey') == '')
        {
            redirect();
        }
    }

    public function index()
    {
        //Check logged in access right and allow/denied access
        /* if(!in_array(getRightsId('song_list'), getUserRightsData($this->DeviceType))){
          redirect('access_denied');
          } */
        $this->data['title'] = 'Manage Popups';
        $this->data['content_view'] = 'admin/popup/list';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }

    public function create()
    {
        $this->data['title'] = 'Create Popup';
        $this->data['content_view'] = 'admin/popup/create';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);   
    }

}

?>