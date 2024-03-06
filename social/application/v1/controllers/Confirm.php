<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All user registration and sign in process
 * Controller class for sign up and login  user of cnc
 * @package    signup
 
 * @version    1.0
 */

class Confirm extends Common_Controller {

	public function __construct(){
		parent::__construct();
	}

	public function email($UserGuID)
    {
        $this->load->model('users/login_model');
		
        $NumRows = $this->login_model->confirm_email($UserGuID);
		
        if($NumRows['msg'] != 1) 
        {
        	$this->session->set_flashdata('errMsg', lang('msg'.$NumRows['msg']));
            $this->session->set_userdata('UserStatusID',2);
        } 
        else 
        {
        	// $this->session->set_flashdata('msg', lang('msg'.$NumRows['msg']));
        	// $this->session->set_flashdata('email', $NumRows['email']);

            /* Define default variables */
            $Data['UserSocialID']   = '';
            $Data['DeviceID']       = DEFAULT_DEVICE_ID;
            $Data['Latitude']       = '';
            $Data['Longitude']      = '';
            $Data['IPAddress']      = $_SERVER['REMOTE_ADDR'];                
            $Data['Email']          = $NumRows['email'];
            $Data['Username']       = $NumRows['email'];
            $Data['Password']       = '';
            $Data['Resolution']     = DEFAULT_RESOLUTION;
            $Data['Picture']        = '';
            $Data['Token']          = '';
            $Data['DeviceToken']    = '';
            $Data['profileUrl']     = '';
            $Data['SourceID']       = 1;
            $Data['AutoLogin']      = true;
            
            $Data['DeviceTypeID']   = $this->login_model->get_device_type_id('Native');
            
            $UserData               = $this->login_model->verify_login($Data);
            
            if($UserData['ResponseCode'] == 200) 
            {            
                $this->SetSession($UserData['Data']);
            }
            else
            {
                $this->session->set_flashdata('msg',$UserData['Message']);    
            } 
        }
		redirect(site_url('signin'));
	}

    /**
     * [SetSession used to set user session]
     * @param [type] $output [user details]
     */
    public function SetSession($output) {

        $this->session->set_userdata('LoginSessionKey', $output['LoginSessionKey']);
        $this->session->set_userdata('UserID', $output['UserID']);
        $this->session->set_userdata('UserGUID', $output['UserGUID']);
        $this->session->set_userdata('FirstName', $output['FirstName']);
        $this->session->set_userdata('LastName', $output['LastName']);
        $this->session->set_userdata('Email', $output['Email']);
        //$this->session->set_userdata('LoginKeyword', $output->Data->LoginKeyword);
        $this->session->set_userdata('ProfilePicture', $output['ProfilePicture']);
        $this->session->set_userdata('TimeZoneOffset',$output['TimeZoneOffset']);
        $this->session->set_userdata('UserStatusID',$output['StatusID']);
        //$this->session->set_userdata('RoleGuID', $output->Data->RoleGuID);
        if ($output['FirstName'] != '') {
            $DisplayName = $output['FirstName'];
            if ($output['LastName'] != '') {
                $DisplayName.=" " . $output['LastName'];
            }
        } else {
            $DisplayName = $output['Email'];
        }
        $this->session->set_userdata('DisplayName', $DisplayName);
        $this->load->model('users/user_model');
        $is_super_admin=$this->user_model->is_super_admin($output['UserID']);
        if($is_super_admin)
        {
            $this->session->set_userdata('isSuperAdmin',TRUE); 
        }
    }
}

?>