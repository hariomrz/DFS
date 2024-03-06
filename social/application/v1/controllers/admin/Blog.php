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

class Blog extends MY_Controller {

	public $page_name = 'blog';
    public $dashboard = 'blog';

	public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/users_model');
            
        if ($this->session->userdata('AdminLoginSessionKey') == ''){
            redirect('admin');
        }
    }

    public function index($Type= 'User') {
        //Check logged in access right and allow/denied access
        /*if(!in_array(getRightsId('song_list'), getUserRightsData($this->DeviceType))){
            redirect('access_denied');
        }*/

        $this->data['title'] = 'Manage Blog';
        $this->data['content_view'] = 'admin/blog/list';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }

    public function create($Type= 'User') {        
        $this->data['title'] = 'Create Blog';
        $this->data['content_view'] = 'admin/blog/create';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }

    public function update($BlogGUID= '') {        
        $this->data['title']            = 'Update Blog';
        $this->data['content_view']     = 'admin/blog/create';
        $this->data['global_settings']  = $this->config->item("global_settings");
        $this->data['blog_guid']        = $BlogGUID;
        $this->load->view($this->layout, $this->data);
    }

    public function analytics($Type= 'User') {        
        $this->data['title'] = 'Song Setting';
        $this->data['content_view'] = 'admin/song/analytics';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }
}
?>
