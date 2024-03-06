<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All user registration and sign in process
 * Controller class for sign up and login  user of cnc
 * @package    signup
 
 * @version    1.0
 */

class Wall extends Common_Controller {

    public $page_name = 'userprofile';
    public $dashboard = '';

    /*
     * All user registration and sign in process
     * Controller class for sign up and login  user of cnc
     * @package    signup
     * @author      
     * @version    1.0
     */

    public function __construct() {
        parent::__construct();
        //print_r($this->session->all_userdata());die;
    }

    /*
     * All user registration and sign in process
     * Controller class for sign up and login  user of cnc
     * @package    signup
     
     * @version    1.0
     */

    public function index() {

        if ($this->session->userdata('LoginSessionKey') == '') {
            redirect('/');
        }

        $loginCount = $this->login_model->get_login_count($this->session->userdata('UserID'));
        if ($loginCount > 1) {
            redirect($this->data['profile_url']);   
            die;
        }
        /* $header=array('title'=>'Vcommonsocial - Wall');
          $this->page_name='myaccount';
          $this->load->view('include/header',$header);
          if($this->session->userdata('RoleID')==2){
          $this->load->view('dashboard-teacher');
          }else if($this->session->userdata('RoleID')==3){
          $this->load->view('dashboard-student');
          }
          $this->load->view('include/footer'); */

        $this->data['title'] = 'Wall';
        $this->page_name = 'myaccount';
        $this->data['content_view'] = 'dashboard-student';
        $this->load->view($this->layout, $this->data);
    }

    public function activity($activity_guid)
    {
        $this->load->model('activity/activity_model');

        $this->data['title'] = 'Activity';
        $this->data['type'] = 'myaccount';
        $this->data['content_view'] = 'wall/public';
        $this->data['ActivityGUID'] = $activity_guid;
        $activity_details = $this->activity_model->get_activity_share_details($activity_guid);
        $this->data['OGImage'] = $activity_details['OGImage'];
        $this->data['OGDesc'] = $activity_details['OGDesc'];
        $this->data['OGHeight'] = 500;
        $this->data['OGWidth'] = 750;
        $this->load->view($this->layout, $this->data);
    }
}
