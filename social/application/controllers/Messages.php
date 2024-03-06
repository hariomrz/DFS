<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All user registration and sign in process
 * Controller class for sign up and login  user of cnc
 * @package    signup
 
 * @version    1.0
 */

class Messages extends Common_Controller {

    public $page_name = 'messages';
    public $dashboard = 'messages';

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
        if($this->settings_model->isDisabled(25)){
            $this->data['content_view'] = 'module_disabled';
            $this->load->view($this->layout,$this->data);
            $this->output->_display();
            exit();
        }
        $this->load->model(array('users/login_model'));
    }
    
    public function index($Type = 'thread',$GUID = '') {        
        if($Type == 'thread' && $GUID != 'thread') {
            $this->data['GUID'] = $GUID;
            $this->data['Type'] = $Type;
            $this->load->model('messages/messages_model');
            if($this->messages_model->thread_message_list($this->session->userdata('UserID'),array('ThreadGUID'=>$GUID), TRUE)==0){
                $this->data['GUID'] = '';
                $this->data['Type'] = '';
            }
        } else {
            $this->data['GUID'] = '';
            $this->data['Type'] = '';
        }
        $this->data['title'] = 'Messages';
        $this->data['content_view'] = 'messages/_messages';
        $this->load->view($this->layout, $this->data);
    }
}
