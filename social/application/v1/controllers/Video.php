<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All user registration and sign in process
 * Controller class for sign up and login  user of cnc
 * @package    signup
 
 * @version    1.0
 */

class Video extends Common_Controller {

	public $page_name = 'userprofile';
    public $dashboard = 'userprofile';

	public function __construct() {
        parent::__construct();
        if ($this->session->userdata('LoginSessionKey') == '') {
            redirect('/');
        }
        $this->load->model(array('users/login_model'));
    }

    public function index($UserGUID = '',$Type = 'User') {        
        $this->data['title'] = 'Video';
        $this->data['content_view'] = 'video/video';
        $this->load->view($this->layout, $this->data);
    }
}
?>