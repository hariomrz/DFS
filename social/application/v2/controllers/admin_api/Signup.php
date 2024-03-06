<?php defined('BASEPATH') OR exit('No direct script access allowed');
/*
* All Sginup related process like : login, setusersession
* @package    Signup
* @subpackage Rest Server
* @category   Controller
* @author     Ashwin kumar soni(09-11-2014)
* @version    1.0
*/

/* This can be removed if you use __autoload() in config.php OR use Modular Extensions */
require_once APPPATH . '/libraries/REST_Controller.php';

class Signup extends REST_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(array('admin/login_model', 'users/signup_model'));
        
        //$this->post_data = $this->post();
        if ($this->input->post()) {
            $JSONInput = $this->post();
        } else {
            $Handle = fopen('php://input', 'r');
            $JSONInput = fgets($Handle);
        }
        $jsondata = $JSONInput;
        $this->post_data = json_decode($JSONInput,true);
    }

    /**
    * Function for signup
    * Parameters : post_data,
    * Return : Status : success/error
    */
    function index_post() {
        /* Define variables - starts */
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['Data'] = array();
        $Return['Service Name'] = 'admin_api/signup';
        /* Define variables - ends */
        
        /* Gather Inputs - starts */
        $Data = $this->post_data;

        if (isset($Data)) {

            /* Check provided JSON format is valid */
            if (isset($Data['FirstName']))
                $FirstName = ucfirst(strtolower($Data['FirstName']));
            else
                $FirstName = '';
            if (isset($Data['LastName']))
                $LastName = ucfirst(strtolower($Data['LastName']));
            else
                $LastName = '';
            if (isset($Data['Email']))
                $Email = strtolower($Data['Email']);
            else
                $Email = '';
            if (isset($Data['UserName']))
                $UserName = strtolower($Data['UserName']);
            else
                $UserName = '';
            if (isset($Data['Password']))
                $Password = $Data['Password'];
            else
                $Password = '';
            if (isset($Data['DeviceID']) && $Data['DeviceID'] != '')
                $DeviceID = $Data['DeviceID'];
            else
                $DeviceID = '1';
            //if (isset($Data['Role']) && $Data['Role'] != '' && in_array($Data['Role'], $RoleArray))
            if (isset($Data['Role']) && $Data['Role'] != '')
                $Role = $Data['Role'];
            else
                $Role = '3'; //Normal User by default
            if (isset($Data['DeviceType']))
                $DeviceType = $Data['DeviceType'];
            else
                $DeviceType = 'Native';
            $DeviceTypeID = $Data['DeviceTypeID'] = $this->login_model->GetDeviceTypeID($DeviceType);

            if (isset($Data['SocialType']) && $Data['SocialType'] != '')
                $SocialType = $Data['SocialType'];
            else
                $SocialType = 'Web';
            $SourceID = $this->login_model->GetSourceID($SocialType);

            if (isset($Data['Resolution']))
                $Resolution = $Data['Resolution'];
            else
                $Resolution = 'Low';
            $ResolutionID = $this->login_model->GetResolutionID($Resolution);

            if (isset($Data['IPAddress']))
                $IPAddress = $Data['IPAddress'];
            else
                $IPAddress = '';
            if (isset($Data['Latitude']))
                $Latitude = $Data['Latitude'];
            else
                $Latitude = '';
            if (isset($Data['Longitude']))
                $Longitude = $Data['Longitude'];
            else
                $Longitude = '';
            if (isset($Data['EmailNotification']))
                $EmailNotification = $Data['EmailNotification'];
            else
                $EmailNotification = 0;
            if (isset($Data['PhoneNumber']))
                $PhoneNumber = $Data['PhoneNumber'];
            else
                $PhoneNumber = '';
            if (isset($Data['UserTypeID']))
                $UserTypeID = $Data['UserTypeID'];
            else
                $UserTypeID = '';
            if (isset($Data['UserSocialID']))
                $UserSocialID = $Data['UserSocialID'];
            else
                $UserSocialID = '';
            
            if(isset($Data['Token']))
                $Token = $Data['Token'];
            else
                $Token = '';

            /* Gather Inputs - ends */

            $Data['UserSocialID'] = $UserSocialID;
            $Data['UserTypeID'] = $UserTypeID;
            $Data['PhoneNumber'] = $PhoneNumber;
            $Data['EmailNotification'] = $EmailNotification;
            $Data['IPAddress'] = $IPAddress;
            $Data['Latitude'] = $Latitude;
            $Data['Longitude'] = $Longitude;
            $Data['SocialType'] = $SocialType;
            $Data['Resolution'] = $Resolution;
            $Data['DeviceID'] = $DeviceID;
            $Data['DeviceType'] = $DeviceType;
            $Data['DeviceTypeID'] = $DeviceTypeID;
            $Data['Role'] = $Role;
            $Data['SourceID'] = $SourceID;
            $Data['ResolutionID'] = $ResolutionID;
            $Data['Token'] = $Token;
            $IsSignUp = 0;
            $ClientError = 0;
            if ($SocialType != 'Web')
                $Data['UserName'] = $UserName = $UserSocialID;

            /* Validation - starts */
            if ($this->form_validation->required($FirstName) == FALSE && $SourceID == 1) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('first_name_required');
            } elseif ($this->form_validation->required($LastName) == FALSE && $SourceID == 1) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('last_name_required');
            } elseif ($this->form_validation->required($UserName) == FALSE && $SourceID == 1) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('username_required');
            } elseif ($this->form_validation->required($Email) == FALSE) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('email_required');
            } elseif ($this->form_validation->valid_email($Email) == FALSE) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('invalid_email');
            } elseif ($this->form_validation->required($Password) == FALSE && $SourceID == 1) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('pass_required');
            } elseif ($this->form_validation->required($DeviceType) == FALSE) {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('device_required');
            }/* Validation - ends */ else {

                $accountReturn = $Return;
                $accountReturn['ResponseCode'] = 504;

                if ($SourceID != 1) //social
                    $accountReturn = $this->login_model->verifyLogin($Data);



                if ($accountReturn['ResponseCode'] == 504) {
                //login does not exist, create account and apply other basic validations
                   if ($this->_CheckUserNameExist($UserName) == FALSE) {
                        $accountReturn['ResponseCode'] = 512;
                        $accountReturn['Message'] = lang('username_already_exists');
                        $ClientError = 3;
                    } elseif ($this->login_model->_CheckEmailExist($Email) == 'deleted') {
                        $accountReturn['ResponseCode'] = 512;
                        $accountReturn['Message'] = lang('email_registered');
                        $ClientError = 2;
                    } elseif ($this->login_model->_CheckEmailExist($Email) == 'exist') {
                        $accountReturn['ResponseCode'] = 512;
                        $accountReturn['Message'] = lang('email_exists');
                        $ClientError = 2;
                    }  elseif ($this->form_validation->required($Email) == FALSE) {
                        $accountReturn['ResponseCode'] = 511;
                        $accountReturn['Message'] = lang('email_required');
                    } elseif ($this->form_validation->valid_email($Email) == FALSE) {
                        $accountReturn['ResponseCode'] = 511;
                        $accountReturn['Message'] = lang('invalid_email');
                    } else {
                        $accountReturn = $this->signup_model->createAccount($Data);
                        //Temporary code for testing *****
                        $IsSignUp = 1;

                        /* Registration insert - ends */
                    }                
                }

                if ($accountReturn['ResponseCode'] == '200')
                    $this->SetSession($accountReturn);

                    

                $Return['ResponseCode'] = $accountReturn['ResponseCode'];
                $Return['Message'] = $accountReturn['Message'];
                $Return['Data'] = $accountReturn['Data'];
            }
        } else {
            $Return['ResponseCode'] = 500;
            $Return['Message'] = lang('input_invalid_format');
        }
        $this->signup_model->update_analytics($SourceID, $DeviceTypeID, $IsSignUp, $ClientError);
        $this->response($Return); /* Final Output */
    }

    public function signupAnalytics_post() {
        $this->signup_model->add_analytics();
    }

    /**
    * Function for setsession for a logged in user
    * Parameters : $output
    * Return : Setsession
    */
    public function SetSession($output) {
        $this->session->set_userdata('AdminLoginSessionKey', $output['Data']['AdminLoginSessionKey']);
        $this->session->set_userdata('UserID', $output['Data']['UserID']);
        $this->session->set_userdata('UserGUID', $output['Data']['UserGUID']);
        $this->session->set_userdata('FirstName', $output['Data']['FirstName']);
        $this->session->set_userdata('LastName', $output['Data']['LastName']);
        $this->session->set_userdata('Email', $output['Data']['Email']);
        $this->session->set_userdata('LoginKeyword', '');
        $this->session->set_userdata('ProfilePicture', $output['Data']['ProfilePicture']);
        //$this->session->set_userdata('RoleID', $output['Data']['RoleID']);
        if ($output['Data']['FirstName'] != '') {
            $DisplayName = $output['Data']['FirstName'];
            if ($output['Data']['LastName'] != '') {
                $DisplayName.=" " . $output['Data']['LastName'];
            }
        } else {
            $DisplayName = $output['Data']['Email'];
        }
        $this->session->set_userdata('DisplayName', $DisplayName);
    }

    
    /**
    * Function for CheckUserNameExist : Callback Function
    * Parameters : $UserName
    * Return : Status : true/false
    */
    function _CheckUserNameExist($UserName) {
        $this->db->select('UserID');
        $this->db->where('LoginKeyword', EscapeString($UserName));
        $Query = $this->db->get(USERLOGINS);
        
        if ($Query->num_rows() == 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    

}//End of file signup.php