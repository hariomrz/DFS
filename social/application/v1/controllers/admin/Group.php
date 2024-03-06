<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Controller class for group
 * @package    team
 * @author     V-INFOTECH
 * @version    1.0
 */

class Group extends MY_Controller {

    public $page_name = 'team';
    public $dashboard = 'groups';

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model('admin/users_model');
        $this->show_date_filter = false;
        if ($this->session->userdata('AdminLoginSessionKey') == '') {
            redirect();
        }
        
        if($this->settings_model->isDisabled(1)){
           redirect();
           //exit();
        }
    }

    public function index($Type = 'User') {
        $this->data['title'] = 'Team List';
        $this->data['content_view'] = 'admin/team/list';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }

    public function settings($Type = 'User') {
        $this->data['title'] = 'Song Setting';
        $this->data['content_view'] = 'admin/song/settings';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }

    public function analytics($Type = 'User') {
        $this->data['title'] = 'Song Setting';
        $this->data['content_view'] = 'admin/song/analytics';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->load->view($this->layout, $this->data);
    }

    public function permission() {
        $this->data['title'] = 'Group Permission';
        $this->data['content_view'] = 'admin/group/permission';
        $this->data['global_settings'] = $this->config->item("global_settings");
        $this->page_name = "group_permission";
        $this->load->view($this->layout, $this->data);
    }

    public function downloadpagelist() {
        $file_url = base_url() . '/' . PATH_IMG_UPLOAD_FOLDER . "csv_file/PageList.xls";

        header('Content-Type: application/octet-stream');

        header("Content-Transfer-Encoding: Binary");

        header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\"");

        readfile($file_url);
    }

}

?>