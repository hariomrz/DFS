<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
* Controller class to create and view polls
* @package    poll
* @author     V-Infotech
* @version    1.0
*/
class Poll extends Common_Controller {

    public $page_name = 'poll';
    public $dashboard = '';

    public function __construct() 
    {
        parent::__construct();
        $this->data['pname'] = 'polls';
        if($this->session->userdata('LoginSessionKey') == '') 
        {
            redirect('/');
        }
        if($this->settings_model->isDisabled(30)){
            $this->data['content_view'] = 'module_disabled';
            $this->load->view($this->layout,$this->data);
            $this->output->_display();
            exit();
        }
    }

    /*
    * @package    Poll
    * @author     V-Infotech
    * @version    1.0
    */
    public function index() 
    {
        $this->data['title'] = 'Polls';
        $this->data['content_view'] = 'poll/wall';
        $this->data['auth'] = array('LoginSessionKey' => $this->session->userdata('LoginSessionKey'), 'UserGUID' => $this->session->userdata('UserGUID'));
        $this->data['ModuleEntityGUID'] = $this->session->userdata('UserGUID');
        $this->data['IsFriend'] = '0';
        $this->dashboard = 'profile';
        $this->data['ActivityGuID'] = "";
        $this->data['UserID'] = $this->session->userdata('UserID');
        $this->data['WallType'] = '';
        $this->data['EntityID'] = $this->session->userdata('UserID');
        $this->data['profile_url'] = get_entity_url($this->data['EntityID']);
        $this->data['ModuleID']         = '3';
        $this->data['IsPoll'] = '1';
        $this->load->view($this->layout, $this->data);    
    }

}
