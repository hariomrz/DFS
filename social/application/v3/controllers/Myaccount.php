<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All user registration and sign in process
 * Controller class for sign up and login  user of cnc
 * @package    signup
 
 * @version    1.0
 */

class Myaccount extends Common_Controller {

    public $page_name = 'myaccount';
    public $dashboard = '';

    /*
     * All user registration and sign in process
     * Controller class for sign up and login  user of cnc
     * @package    signup
     
     * @version    1.0
     */

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('UserGUID'))
        {
            redirect('/');
        }
        $this->data['ModuleID'] = 3;
        $this->data['ModuleEntityGUID'] = $this->session->userdata('UserGUID');
    }

    /*
     * All user registration and sign in process
     * Controller class for sign up and login  user of cnc
     * @package    signup
     
     * @version    1.0
     */

    public function index() {
        $this->privacy();
    }

    
    public function ProfileSetup(){
        $this->load->model('users/user_model');
        $user_location = $this->user_model->get_user_location($this->session->userdata('UserID'));

        /*if(!$user_location)
        {*/
            $this->load->library('geoip');
            $ip_address = $_SERVER['REMOTE_ADDR'];
            if(ENVIRONMENT!='production')
            {
                $ip_address = '103.21.54.66';
            }
            $record = $this->geoip->info($ip_address);

            $this->data['location'] = false;
            if(isset($record->city) && !empty($record->city) && isset($record->state_name) && !empty($record->state_name) && isset($record->country_name) && !empty($record->country_name) && isset($record->country_code) && !empty($record->country_code))
            {
                $this->data['location'] = true;
                $this->data['City'] = $record->city;
                $this->data['State'] = $record->state_name;
                $this->data['Country'] = $record->country_name;
                $this->data['CountryCode'] = $record->country_code;
                $this->data['Lat'] = isset($record->latitude) ? $record->latitude : '0';
                $this->data['Lng'] = isset($record->longitude) ? $record->longitude : '0' ;
            }
        /*}
        else
        {
            $this->data['location'] = true;
            $this->data['City'] = $user_location['City'];
            $this->data['State'] = $user_location['State'];
            $this->data['Country'] = $user_location['Country'];
            $this->data['CountryCode'] = $user_location['CountryCode'];
            $this->data['Lat'] = '0';
            $this->data['Lng'] = '0';
        }*/


        $this->data['title'] = 'Profile Setup';
        $this->page_name = 'myaccount';
        $this->data['pname'] = 'myaccount';
        $this->data['content_view'] = 'settings/profile-setup';            
        
        $this->load->view($this->layout, $this->data);
    }
    
    public function SignUpProfileSetup(){
        $this->data['title'] = 'Sign Up Step 2';
        $this->page_name = 'myaccount';
        $this->data['pname'] = 'myaccount';
        $this->data['content_view'] = 'settings/sign-up-step-2';
        $this->load->view($this->layout, $this->data);
    }

    public function privacy(){
        $this->data['title'] = 'Privacy Settings';
        $this->page_name = 'myaccount';
        $this->data['pname'] = 'myaccount';
        $this->data['sub'] = 'privacy';
        $this->data['content_view'] = 'settings/privacy_settings';            
        
        $this->load->view($this->layout, $this->data);
    }

    public function interest(){
        $this->data['title'] = 'Area of Interest';
        $this->page_name = 'myaccount';
        $this->data['pname'] = 'myaccount';
        $this->data['sub'] = 'interest';
        /*check module settings*/
        if($this->settings_model->isDisabled(31)){
            $this->data['content_view'] = 'module_disabled';
            $this->load->view($this->layout,$this->data);
            $this->output->_display();
            exit();
        }

        $this->data['content_view'] = 'settings/interest';
        $this->load->library('user_agent');
        $refferer_url = $this->agent->referrer();
        $current_user_url = get_entity_url($this->session->userdata('UserID'));

        $this->data['redirect_url'] = site_url('network/grow_your_network');
        if($refferer_url == $current_user_url)
        {
            $this->data['redirect_url'] = $current_user_url;
        }

        $this->load->view($this->layout, $this->data);
    }
    
    public function personalize(){
        $this->data['title'] = 'Personalize';
        $this->page_name = 'myaccount';
        $this->data['pname'] = 'myaccount';
        $this->data['sub'] = 'personalize';
        $this->data['content_view'] = 'settings/personalize';            
        
        $this->load->view($this->layout, $this->data);
    }

    public function resetPassword(){
        $this->data['title'] = 'Reset Password';
        $this->page_name = 'myaccount';
        $this->data['pname'] = 'myaccount';
        $this->data['sub'] = 'resetpassword';
        $this->data['content_view'] = 'settings/reset_password';            
        
        $this->load->view($this->layout, $this->data);
    }

    public function language(){
        $this->data['title'] = 'Language';
        $this->page_name = 'myaccount';
        $this->data['pname'] = 'myaccount';
        $this->data['sub'] = 'language';
        $this->data['content_view'] = 'settings/language';            
        
        $this->load->view($this->layout, $this->data);
    }

    public function video(){
        $this->data['title'] = 'Video Settings';
        $this->page_name = 'myaccount';
        $this->data['pname'] = 'myaccount';
        $this->data['sub'] = 'video';
        $this->data['content_view'] = 'settings/video';            
        
        $this->load->view($this->layout, $this->data);
    }

}
