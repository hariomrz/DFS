<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
* All user registration and sign in process
* Controller class for sign up and login  user of cnc
* @package    signup

* @version    1.0
*/
class Network extends Common_Controller
{
    public $page_name= 'network';

    /*
    * All user registration and sign in process
    * Controller class for sign up and login  user of cnc
    * @package    signup
    * @author      
    * @version    1.0
    */
    public function __construct()
    {
        parent::__construct();
            if($this->session->userdata('LoginSessionKey')==''){
            redirect('/');
        }
        
    }

    /*
    * All user registration and sign in process
    * Controller class for sign up and login  user of cnc
    * @package    signup
    
    * @version    1.0
    */
    public function index()
    {
        
        $this->data['title'] = 'Network';
        $this->page_name = 'network';
        $this->dashboard = '';
        $this->data['content_view'] = 'networks/build_network_main';
        $this->load->view($this->layout, $this->data);
    }

    public function grow_your_network()
    {
        $this->data['title'] = 'Grow Your Network';
        $this->page_name = 'network';
        $this->dashboard = '';
        $this->data['content_view'] = 'networks/grow_your_network';
        $this->load->view($this->layout, $this->data);
    }

    public function interest()
    {
        $this->data['title'] = 'Choose Interest';
        $this->page_name = 'network';
        $this->dashboard = '';
        $this->data['content_view'] = 'networks/interest';
        $this->load->view($this->layout, $this->data);
    }

    public function build()
    {
        $this->data['title'] = 'Network';
        $this->page_name = 'network';
        $this->dashboard = '';
        $this->data['content_view'] = 'networks/build_network_main';
        $this->load->view($this->layout, $this->data);
    }

    /*public function test_notifications(){
        $this->load->model('notification_model');

        $type               = 'GroupPostLike';
        $current_user_id    = '78';
        $receivers_id       = array(76);
        $referenced_id      = 0;
        $is_admin_entity_id = 0;
        $notification_param = array();

        $data = $this->notification_model->generateNotification($type, $current_user_id, $receivers_id ,$referenced_id , $is_admin_entity_id, $notification_param );

        echo "<pre>";
        print_r($data);
        exit;
    }*/

    

}