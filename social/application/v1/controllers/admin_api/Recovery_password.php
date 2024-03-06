<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Example
 * This Class used for REST API
 * (All THE API CAN BE USED THROUGH POST METHODS)
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
 */
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require_once APPPATH . '/libraries/REST_Controller.php';

class Recovery_password extends REST_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(array('admin/login_model'));
        $this->post_data = $this->post();
    }

    function recoverypassword_post() {
        /* Define variables - starts */
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('reset_pass_link_sent');
        $Return['Data'] = array();
        $Return['ServiceName'] = 'admin_api/recoverypassword';
        $Validation = TRUE;
        $Types = array('Username', 'Email', 'PhoneNumber');
        /* Define variables - ends */
      
        /* Gather Inputs - starts */
        $Data = $this->post_data;
        
        if (isset($Data)) {
            if (isset($Data['Value']))
                $Value = $Data['Value'];
            else
                $Value = '';
            if (isset($Data['Type']))
                $Type = $Data['Type'];
            else
                $Type = '';
            if (isset($Data['SocialType']) && $Data['SocialType'] != '')
                $SocialType = $Data['SocialType'];
            else
                $SocialType = 'Web';
            $SourceID = $this->login_model->GetSourceID($SocialType);

            if ($this->form_validation->required($Type) == FALSE) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('type_required');
            } elseif ($this->form_validation->required($Value) == FALSE) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $Type . lang('is_required');
            } elseif (($Type == 'Email') && $this->form_validation->valid_email($Value) == FALSE) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('invalid_email');
            } elseif ($Type == 'Email' && $this->login_model->CheckEmailAdd($Value) == FALSE) {
                $Return['ResponseCode'] = 512;
                $Return['Message'] = lang('email_not_exist');
            } else {
                $recoverypassword = $this->login_model->recoveryPassword($Type,$Value);
                if (!$recoverypassword) {
                    $Return['ResponseCode'] = 512;
                    $Return['Message'] = lang('user_not_exists');
                } else {
                    if($Type!='Url'){
                        $Return['Message'] = lang('reset_pass_sent');
                        $Return['Data'] = $recoverypassword;
                    }
                }
            }
        } else {
            $Return['ResponseCode'] = 500;
            $Return['Message'] = lang('invalid_format');
        }
        $Outputs = $Return;
        $this->response($Outputs); /* Final Output */
    }

    function checkUserGUID_post(){
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['Data'] = array();
        $Return['ServiceName'] = 'admin_api/checkUserGUID';

        $Data = $this->post_data;

        $UserGUID = '';
        if(isset($Data['UserGUID'])){
            $UserGUID = $Data['UserGUID'];
        }

        if($UserGUID==''){
            $Return['ResponseCode'] = 511;
            $Return['Message'] = lang('user_guid_required');
        } else {
            if(!$this->login_model->checkRecoveryToken($UserGUID)){
                $Return['ResponseCode'] = 503;
                $Return['Message'] = lang('invalid_url');
            }
        }

        $this->response($Return);

    }

    function recoveryonetimepasswordlink_post()
    {
        /* Define variables - starts */
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('new_pass_sent');
        $Return['Data'] = array();
        $Return['ServiceName'] = 'admin_api/recoveryonetimepasswordlink';
        $Validation = TRUE;
        /* Define variables - ends */
        
        /* Gather Inputs - starts */
        $Data = $this->post_data;
        
        if (isset($Data)) {
            if (isset($Data['UserGUID']))
                $UserGUID = $Data['UserGUID'];
            else
                $UserGUID = '';
            if (isset($Data['Password']))
                $Password = $Data['Password'];
            else
                $Password = '';
            
            if ($this->form_validation->required($Password) == FALSE) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('pass_required');
            } elseif ($this->form_validation->required($UserGUID) == FALSE) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('user_guid_required');
            } else {
                
                /* Query to check userData exists in UserResetPaaswword Table - starts   */
                $UserResetdata = $this->login_model->getUserResetData($UserGUID);
                if ($UserResetdata) {
                    $Return['Data'] = $this->login_model->recoveryOneTimePasswordLink($Password,$UserResetdata,$UserGUID);
                }else{
                    /* Error - Email ID entered does not Exists. */
                    $Return['ResponseCode'] = 512;
                    $Return['Message'] = lang('user_not_exists');
                }
            }
        } else {
            $Return['ResponseCode'] = 500;
            $Return['Message'] = lang('invalid_format');
        }
            $Outputs = $Return;
            $this->response($Outputs); /* Final Output */
        
    }

}//End of file recovery_password.php