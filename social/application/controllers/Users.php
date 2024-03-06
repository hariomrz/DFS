<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All user registration and sign in process
 * Controller class for sign up and login  user of cnc
 * @package    signup
 
 * @version    1.0
 */

class Users extends Common_Controller {

    public $page_name = 'users';
    public $dashboard = 'users';

    /*
     * All user registration and sign in process
     * Controller class for sign up and login  user of cnc
     * @package    signup
     
     * @version    1.0
     */

    public function __construct() {
        parent::__construct();
        if ($this->session->userdata('LoginSessionKey') == '') {
            redirect('/');
        }
        $this->load->model(array('users/login_model'));
    }

    /*
     * All user registration and sign in process
     * Controller class for sign up and login  user of cnc
     * @package    signup
     
     * @version    1.0
     */

    public function index() {
        $this->data['title'] = 'Users';
        $this->data['Type'] = 'Users';
        $this->data['content_view'] = 'users/user_list';
        $this->load->view($this->layout, $this->data);
    }

    public function friends($UserGuID = '') {
        if($UserGuID==''){
            $UserID = $this->session->userdata('UserID');
        } else {
            $UserID = get_detail_by_guid($UserGuID, 3, "UserID", 1);
        }
        $this->data['title'] = 'Friends';
        $this->data['Type'] = 'Friends';
        $this->data['UID'] = $UserID;
        $this->data['UserID'] = $UserID;
        $this->data['content_view'] = 'users/friend_list';
        $this->load->view($this->layout, $this->data);
    }

    public function followers($UserGuID = '') {
        if($UserGuID==''){
            $UserID = $this->session->userdata('UserID');
        } else {
            $UserID = get_detail_by_guid($UserGuID, 3, "UserID", 1);
        }
        $this->data['title'] = 'Followers';
        $this->data['Type'] = 'Followers';
        $this->data['UID'] = $UserID;
        $this->data['UserID'] = $UserID;
        $this->data['content_view'] = 'users/followers_list';
        $this->load->view($this->layout, $this->data);
    }

    public function following($UserGuID = '') {
        if($UserGuID==''){
            $UserID = $this->session->userdata('UserID');
        } else {
            $UserID = get_detail_by_guid($UserGuID, 3, "UserID", 1);
        }
        $this->data['title'] = 'Following';
        $this->data['Type'] = 'Following';
        $this->data['UID'] = $UserID;
        $this->data['UserID'] = $UserID;
        $this->data['content_view'] = 'users/following_list';
        $this->load->view($this->layout, $this->data);
    }

    public function requests(){
        $this->data['title'] = 'Requests';
        $this->data['Type'] = 'Request';
        $this->data['content_view'] = 'users/pending_request';
        $this->load->view($this->layout, $this->data);
    }
}
