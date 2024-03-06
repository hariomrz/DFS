<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 *
 * This Class used as REST API for change password
 *
 * @package		CodeIgniter
 * @category    Controller
 * @author      Vinfotech Team
 */
class Change_password extends Common_API_Controller 
{

    function __construct() 
    {
        parent::__construct();
        $this->check_module_status(5);
    }

    /**
      * Function Name: index
      * @param Password, PasswordNew
      * @return success / failure message and response code
      * Description: Change Password
      */
    function index_post() 
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data; 

        if ($data) {
            $config = array(
                array(
                    'field' => 'Password',
                    'label' => 'password',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'PasswordNew',
                    'label' => 'new password',
                    'rules' => 'trim|required|min_length[6]|max_length[15]'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();         
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $password       = isset($data['Password']) ? $data['Password'] : '' ;
                $new_password    = isset($data['PasswordNew']) ? $data['PasswordNew'] : '' ;
                
                $user_data = $this->login_model->check_password($this->UserID, $password);
                if ($user_data) {
                    $this->login_model->update_password($user_data, $new_password, FALSE);
                    $return['Message'] = lang('password_changed');
                                        
                    // Community synch user
                   /* $this->load->model(array(
                        'community_server/community_users_model', 
                        'users/community_model'
                    ));
                    $email_id = $this->community_model->get_email_by_user_id($this->UserID);
                    $data = array_merge($data, array('UserEmailID' => $email_id, 'UserID' => $this->UserID));
                    $this->community_users_model->change_password($data);
                    * 
                    */
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('old_pass_not_match');
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
	
    /**
      * Function Name: set
      * @param PasswordNew
      * @return success / failure message and response code
      * Description: Set Password For First Time (In case of social login)
      */
	function set_post() 
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;
		$user_id = $this->UserID;

        if (isset($data)) 
        {
            if ($this->form_validation->run('api/changepassword/set') == FALSE) 
            {
                $error = $this->form_validation->rest_first_error_string();         
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } 
            else 
            {
                $new_password = isset($data['PasswordNew']) ? $data['PasswordNew'] : '' ;
                $this->login_model->set_password($user_id, $new_password);
                $return['Message'] = lang('password_set');
            }

        } else {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return); /* Final Output */
    }
}