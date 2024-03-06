<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * This Class used as REST API for Forgot Password 
 * @category Controller
 * @author       Vinfotech Team
 */
class Recovery_password extends Common_API_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Function Name: forgot_password
     * @param Value
     * @param Type
     * @param SocialType
     * Description: Recover password
     */
    function forgot_password_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        if ($data) {
            $this->load->model('users/login_model');
            $return['Message'] = lang('forgot_password_message');
            $types = array('Username', 'Email', 'Mobile');
            $response_types = array('EmailWithResetUrl', 'EmailWithResetUrlAndCode', 'EmailWithResetCode', 'SMSWithResetCode');
            /* Define variables - ends */
            $type = isset($data['Type']) ? $data['Type'] : 'Mobile';
            if($type == 'Mobile') {
                $return['Message'] = lang('forgot_password_otp_message');
            }
            $validation = "trim|required|numeric|min_length[10]|max_length[10]";
            if ($type == 'Email') {
               $validation = "trim|required|valid_email"; 
            }
            $config = array(
                array(
                    'field' => 'Value',
                    'label' => 'email',
                    'rules' => $validation
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $value = $data['Value'];
                $response_type = isset($data['ResponseType']) ? $data['ResponseType'] : 'SMSWithResetCode';
                if (!in_array($response_type, $response_types)) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "response type");
                } elseif (!in_array($type, $types)) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "type");
                } else {                
                    $email_exist = $this->login_model->is_email_exist($value, $type);
                    if ($email_exist == 'notexist') {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = sprintf(lang('not_exist'), $type); 
                    }
                    /*elseif ($email_exist == 'inactive')
                    {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('forgot_pass_inactive_account');
                    }*/
                    elseif ($email_exist == 'deleted') {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('forgot_pass_delete_account');
                    } elseif ($email_exist == 'blocked') {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('forgot_pass_block_account');
                    } else {
                        $recovery_password = $this->login_model->forgot_password($type, $value, $response_type);
                        if (!$recovery_password) {
                            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $return['Message'] = lang('user_not_exists');
                        }
                    }                
                }
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: validate_forgot_password_token
     * @param UserGUID
     * Description: Check Recovery Link is valid or not
     */
    function validate_forgot_password_token_post() {
        $return = $this->return;
        $data = $this->post_data;
        if ($data) {
            $this->load->model('users/login_model');
            $config = array(
                array(
                    'field' => 'OTP',
                    'label' => 'OTP',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $otp = $data['OTP'];
                if (!$final_response = $this->login_model->check_forgot_password_token($otp)) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = "Invalid confirmation code";
                }
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: forgot_password_link
     * @param UserGUID
     * @param Password
     * Description: Generate and send one time password recovery link
     */
    function set_password_post() {
        /* Define variables - starts */
        $return = $this->return;
        $return['Message'] = lang('password_reset_success');
        $validation = TRUE;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;

        if ($data) {
            $this->load->model('users/login_model');
            $config = array(
                array(
                    'field' => 'OTP',
                    'label' => 'otp',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'Password',
                    'label' => 'Password',
                    'rules' => 'trim|required|min_length[6]|max_length[15]'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $otp = $data['OTP'] ? $data['OTP'] : '';
                $password = $data['Password'] ? trim($data['Password']) : '';
                $type = $data['Type'] ? $data['Type'] : 'Mobile';
                $user_reset_data = $this->login_model->get_password_reset_data($otp);
                
                if ($user_reset_data) {
                    $this->login_model->forgot_password_link($password, $user_reset_data, $otp, $type);
                    if(isset($data['LinkReferrerFlag']) && $data['LinkReferrerFlag'] == '1')
                    {
                        /* Define default variables */
                        
                        $email = get_detail_by_id($user_reset_data['UserID'],3,'Email');
                        $session_data           = array();
                        $session_data['UserSocialID']   = '';
                        $session_data['DeviceID']       = DEFAULT_DEVICE_ID;
                        $session_data['Latitude']       = '';
                        $session_data['Longitude']      = '';
                        $session_data['IPAddress']      = $_SERVER['REMOTE_ADDR'];                
                        $session_data['Email']          = $email;
                        $session_data['Username']       = $email;
                        $session_data['FirstLogin']     = '1';
                        $session_data['Password']       = '';
                        $session_data['Resolution']     = DEFAULT_RESOLUTION;
                        $session_data['Picture']        = '';
                        $session_data['Token']          = '';
                        $session_data['profileUrl']     = '';
                        $session_data['SourceID']       = 1;
                        $session_data['AutoLogin']      = true;                        
                        $session_data['DeviceTypeID']   = $this->login_model->get_device_type_id('Native');
                        $UserData               = $this->login_model->verify_login($session_data);     
                        
                    }
                } else {
                    /* Error - Email ID entered does not Exists. */
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('invalid_token');
                }
            }
        }
        else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return); /* Final Output */
    }
}
