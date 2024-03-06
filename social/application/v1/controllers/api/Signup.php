<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * This Class used as REST API for Signup 
* @category     Controller
* @author       Vinfotech Team
*/

class Signup extends Common_API_Controller 
{
    function __construct() 
    {
        parent::__construct();
        $this->check_module_status(7);
        $this->load->model(array('users/signup_model'));
    }

    /**
     * [This API is for email address availability for the new registration.]
     * @return [json] [success or failure]
     */
    function checkuserexist_post() {
        $data = $this->post_data;
        if (isset($data)) {
            if ($this->form_validation->required($data['Email']) == FALSE) {
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = lang('email_required');
            } elseif ($this->login_model->is_email_exist($data['Email'], 'Email') == 'exist' || $this->login_model->is_email_exist($data['Email'], 'Email') == 'inactive') {
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = lang('email_exists');
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
            $device_type_id = '';
        }
        $this->response($this->return); /* Final Output */
    }
    
    
        /**
     * [This API is for email address availability for the new registration.]
     * @return [json] [success or failure]
     */
    function check_mobile_exist_post() {
        $data = $this->post_data;
        if (isset($data)) {
            if ($this->form_validation->required($data['Mobile']) == FALSE) {
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = "Please enter mobile number";
            } else {
                $mobile    = $data['Mobile'];
                $flag = $this->login_model->is_email_exist($mobile);
                if ($flag == 'exist' || $flag == 'inactive' || $flag == 'blocked') {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = lang('mobile_registered');
                } else {
                    $this->login_model->send_otp($mobile);
                }
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
            $device_type_id = '';
        }
        $this->response($this->return); /* Final Output */
    }

    /**
     * [index_post Register new acccount]
     * @return [json] [success or failure]
     */
    function index_post() {
    	/* Define variables - starts */ 
        $is_sign_up = 0;    
        /* Define variables - ends */
        $data = $this->post_data;
        if (isset($data))  {
            /* Check Social Type and get the Source ID  */
            $social_type = (isset($data['SocialType']) && !empty($data['SocialType'])) ? $data['SocialType'] : DEFAULT_SOCIAL_TYPE;
            $source_id = $this->login_model->get_source_id($social_type);
            $this->SourceID = $source_id;
            /* Check Device Type and get the Device ID  */
            $device_type = isset($data['DeviceType']) ? $data['DeviceType'] : DEFAULT_DEVICE_TYPE;

            $device_type_id = $this->login_model->get_device_type_id($device_type);
            $this->DeviceTypeID = $device_type_id;

            $validation_rule = $this->form_validation->_config_rules['api/native_signup'];
            $is_device = isset($data['IsDevice']) ? $data['IsDevice'] : "0";
            $this->IsApp = $is_device;
            $data['IsApp'] = $is_device;
            if ($device_type != "Native" && $is_device == "1") {
                $validation_rule[] = array(
                    'field' => 'DeviceID',
                    'label' => 'device ID',
                    'rules' => 'trim|required'
                );
            }
            $data['FullName'] = isset($data['FullName']) ? ucfirst(strtolower($data['FullName'])) : '';
            if(!empty($data['FullName'])){
                $data['FirstName'] = strtok($data['FullName'], ' ');
                $data['LastName'] = strstr($data['FullName'], ' ');
            }else{
                $data['FirstName'] = isset($data['FirstName']) ? ucfirst(strtolower($data['FirstName'])) : '';
                $data['LastName'] = isset($data['LastName']) ? ucfirst(strtolower($data['LastName'])) : '';
            }

            /* Validation - starts */
            $user_id = 0;
            $this->form_validation->set_rules($validation_rule); 
            /* Validation - starts */
            if ($this->form_validation->run() == FALSE && $source_id == 1)  { // for web
                $error = $this->form_validation->rest_first_error_string();         
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error; //Shows all error messages as a string
            } else {
            	$account_return = $this->return;
                $account_return['ResponseCode'] = 504;
                /* Define variables and Gather Inputs */
                $data['Email'] = isset($data['Email']) ? strtolower($data['Email']) : '';
                $data['Username'] = isset($data['Username']) ? strtolower($data['Username']) : '';
                $data['Password'] = isset($data['Password']) ? $data['Password'] : '';
                $data['Role'] = isset($data['Role']) ? $data['Role'] : DEFAULT_ROLE;
                $data['UserSocialID'] = isset($data['UserSocialID']) ? $data['UserSocialID'] : '';
                $data['Latitude'] = isset($data['Latitude']) ? $data['Latitude'] : '';
                $data['Longitude'] = isset($data['Longitude']) ? $data['Longitude'] : '';
                $data['IPAddress'] = isset($data['IPAddress']) ? $data['IPAddress'] : getRealIpAddr();
                $data['Resolution'] = isset($data['Resolution']) ? $data['Resolution'] : DEFAULT_RESOLUTION;
                $data['Picture'] = isset($data['Picture']) ? $data['Picture'] : '';
                $data['Token'] = isset($data['Token']) ? $data['Token'] : '';
                $data['UserTypeID'] = isset($data['UserTypeID']) ? $data['UserTypeID'] : '';
                $data['DeviceID'] = isset($data['DeviceID']) ? $data['DeviceID'] : DEFAULT_DEVICE_ID;
                $data['DeviceToken'] = isset($data['DeviceToken']) ? $data['DeviceToken'] : '';
                $data['BetaInviteGuId'] = isset($data['BetaInviteGuId']) ? $data['BetaInviteGuId'] : '';
                $data['Gender']  = isset($data['Gender']) ? $data['Gender'] : 0;
                $data['HouseNumber']  = isset($data['HouseNumber']) ? $data['HouseNumber'] : '';

                $data['SourceID']       = $source_id;
                $data['SocialType']     = $social_type;
                $data['DeviceType'] 	= $device_type;
                $data['DeviceTypeID']	= $device_type_id;
                $data['ConfirmPassword'] = isset($data['ConfirmPassword']) ? $data['ConfirmPassword'] : '' ;
                if ($source_id != 1) { //to check login
                    $data['Username'] 	= $data['UserSocialID'];
                }
                $dob = isset($data['DOB']) ? $data['DOB'] : '';
                 if (!empty($dob))
                {
                    $dob2 = explode('/', $dob);
                    $dob = $dob2[2] . '-' . $dob2[0] . '-' . $dob2[1];
                }
                if (empty($dob))
                {
                    $dob = '0000-00-00';
                }
                $data['DOB']	= $dob;
                // Reset form validation object and rules.
                $this->form_validation = $this->form_validation->_reset_validation();

                $validation_rule         =    $this->form_validation->_config_rules['api/socail_signup'];
                if ($data['DeviceType'] != "Native" && $is_device == "1") {
                    $validation_rule[] = array(
                        'field' => 'DeviceID',
                        'label' => 'device ID',
                        'rules' => 'trim|required'
                    );
                }
                /* Validation - starts */
                $this->form_validation->set_rules($validation_rule);

                if($this->form_validation->run() == FALSE && $source_id != 1) {
                    $error = $this->form_validation->rest_first_error_string();                  		
                    $account_return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    // In case of twitter return response code 504 
                    if($source_id == 3) {
                        $account_return['ResponseCode'] = 504;
                    } 
                    $account_return['Message'] = $error; //Shows all error messages as a string
            	} else {
            		if ($source_id != 1) { //social
                    	$account_return = $this->login_model->verify_login($data,1);
                        
                     
                         if($account_return['ResponseCode'] == 504) {
                            if ($this->form_validation->required($data['Email']) == FALSE) {
                                $account_return['ResponseCode'] = 504;
                                $account_return['Message'] = lang('email_required');
                            } else {
                                if($this->login_model->is_email_exist($data['Email']) == 'exist' || $this->login_model->is_email_exist($data['Email']) == 'inactive') {
                                    $account_return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                                    $account_return['Message'] = lang('email_exists');
                                } else {    
                                    $account_return = $this->signup_model->createAccount($data);  
                                    $is_sign_up = 1;
                                }
                            }
                        }
                    } else {	                	
                	$account_return = $this->signup_model->createAccount($data);
                        
                    	$is_sign_up = 1;
                    }            
            	}
            	if ($account_return['ResponseCode'] == '201' && $is_device == "1") {   
                    if ($source_id == 1){
                        $data['Username']=$data['Email'];
                    }
                    $data['PhoneNumber'] = $data['Mobile'];
                    $account_return = $this->login_model->verify_login($data);
                }
                if(!empty($account_return['UserGUID'])) {
                    $this->return['UserGUID'] = $account_return['UserGUID'];    
                }        
                $this->return['ResponseCode'] = $account_return['ResponseCode'];
                $this->return['Message'] = $account_return['Message'];
                $this->return['Data'] = $account_return['Data'];	 
                $user_id = isset($account_return['Data']['UserID']) ? $account_return['Data']['UserID'] : 0 ; 
            }
            $session_id = isset($data['SessionID']) ? $data['SessionID'] : session_id() ; 
            
            $this->signup_model->update_analytics($source_id, $device_type_id, $is_sign_up, $this->return['ClientError'],$session_id, $user_id);
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
            $device_type_id = '';
        }
        $this->response($this->return); /* Final Output */
    }

    /**
     * Get number of counts of sent mail for registration
     */
    function get_sent_email_count_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_guid = isset($data['UserGUID']) ? $data['UserGUID'] : 0 ;
        $return['Data'] = $this->signup_model->get_sent_email_count($user_guid);
        $this->response($return);
    }

    /**
     * check unique value
     * @access public
     * @param null
     */
    function is_unique_value($str, $fields) {
        $str = strtolower($str);
        list($table, $field, $select_field1, $entitiy) = explode('.', $fields);

        $this->db->select($select_field1);
        $this->db->where(array($field => EscapeString($str)));
        if ($entitiy == 'Email' || $entitiy == 'PhoneNumber') {
            $this->db->where('StatusID!=3', NULL, FALSE);
        } else if ($entitiy == 'Username') {
            $this->db->join(USERS, USERS . '.UserID=' . PROFILEURL . '.EntityID', 'left');
            $this->db->where(USERS . '.StatusID!=3', NULL, FALSE);
        }
        /* if(!empty($select_field1) && $select_field1 == 'user_id') {
          $id = $this->user_id;
          if(!empty($id) ) {
          $this->db->where($select_field1.' !=', $id);
          $this->db->where('status = ', 1);
          }
          }
          if(!empty($field1) && $field1 == 'status') {
          $this->db->where($field1.' = ', 1);
          } */
        $query = $this->db->get($table);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            if ($field == "Email") {
                $this->return['ClientError'] = 2;
                if ($result['StatusID'] == 3) {
                    $this->form_validation->set_message('is_unique_value', lang('email_registered'));
                } else {
                    $this->form_validation->set_message('is_unique_value', lang('email_exists'));
                }
            } else if ($field == "PhoneNumber") {
                $this->return['ClientError'] = 2;
                if ($result['StatusID'] == 3) {
                    $this->form_validation->set_message('is_unique_value', lang('mobile_registered'));
                } else {
                    $this->form_validation->set_message('is_unique_value', lang('mobile_exists'));
                }
            } else {
                $this->form_validation->set_message('is_unique_value', lang('username_already_exists'));
                $this->return['ClientError'] = 3;
            }
            return FALSE;
        } else {
            if ($table == "ProfileUrl") {
                $controllers = array();
                $route = $this->router->routes;
                if($handle = opendir(APPPATH.'/controllers')) {
                    while(false !== ($controller = readdir($handle))) {
                        if($controller != '.' && $controller != '..' && strstr($controller, '.') == '.php') {
                            $controllers[] = strstr($controller, '.', true);
                        }
                    }
                    closedir($handle);
                }
                $reserved_routes = array_merge($controllers, array_keys($route));
                $reserved_routes[] = 'post';
                $reserved_routes[] = 'article';
                if (in_array(EscapeString($str), array_map('strtolower',$reserved_routes))) {
                    $this->form_validation->set_message('is_unique_value', lang('username_already_exists'));
                    $this->return['ClientError'] = 3;
                    return FALSE;
                } else {
                    return TRUE;
                }
            } else {
                return TRUE;
            } 
        }   
    }

    /**
     * Function Name: analytics
     * Description: Add Analytics
     */
    public function add_analytics_post() 
    {
        $data = $this->post_data;        
        $return = $this->signup_model->add_analytics($data);
        $this->response($return); /* Final Output */
    }

    /**
     * [set_session Set Session for Current User]
     * @param [array] $output [User data]
     */
    public function set_session($output) 
    {
        $this->session->set_userdata('LoginSessionKey', $output['Data']['LoginSessionKey']);
        $this->session->set_userdata('UserID', get_detail_by_guid($output['Data']['UserGUID'], 3));
        $this->session->set_userdata('UserGUID', $output['Data']['UserGUID']);
        $this->session->set_userdata('FirstName', $output['Data']['FirstName']);
        $this->session->set_userdata('LastName', $output['Data']['LastName']);
        $this->session->set_userdata('Email', $output['Data']['Email']);
        $this->session->set_userdata('LoginKeyword', '');
        $this->session->set_userdata('ProfilePicture', $output['Data']['ProfilePicture']);
        if ($output['Data']['FirstName'] != '') 
        {
            $display_name = $output['Data']['FirstName'];
            if ($output['Data']['LastName'] != '') 
            {
                $display_name.=" " . $output['Data']['LastName'];
            }
        } 
        else 
        {
            $display_name = $output['Data']['Email'];
        }
        $this->session->set_userdata('DisplayName', $display_name);
    }

   /**
    * [resend_activation_link_post used to re-send account activation link]
    * @return [json] [success/failure]
    */
    function resend_activation_link_post()
    {
        $data = $this->post_data;

        $validation_rule = $this->form_validation->_config_rules['api/signup/ResendActivationLink'];
        $this->form_validation->set_rules($validation_rule);

        /* Check Device Type and get the Device ID  */
        $device_type = isset($data['DeviceType']) ? $data['DeviceType'] : DEFAULT_DEVICE_TYPE;

        $device_type_id = $this->login_model->get_device_type_id($device_type);
        $this->DeviceTypeID = $device_type_id;

        /* Validation - starts */
        if ($this->form_validation->run() == FALSE)
        { // for web
            $error = $this->form_validation->rest_first_error_string();
            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->return['Message'] = $error; //Shows all error messages as a string
        }
        else
        {
            $this->load->model('users/signup_model');
            $sent_email_count = $this->signup_model->get_sent_email_count($data['UserGUID']);
            if ($sent_email_count < 5)
            {
                $this->signup_model->send_activation_link($data['UserGUID']);
                $this->return['ResponseCode'] = self::HTTP_OK;
                $this->return['Message'] = lang('activation_email_success');
            }
            else
            {
                $this->return['ResponseCode'] = 509;
                $this->return['Message'] = lang('already_sent_5_email');
            }
        }
        $this->response($this->return); /* Final Output */
    }

    /**
     * [verify email address bt OTP]
     * @return [json] [success/failure]
     */
    function email_activation_post()
    {
        $data = $this->post_data;

        $validation_rule = $this->form_validation->_config_rules['api/signup/email_activation'];
        /* Validation - starts */
        $this->form_validation->set_rules($validation_rule);

        /* Validation - starts */
        if ($this->form_validation->run() == FALSE)
        { // for web
            $error = $this->form_validation->rest_first_error_string();
            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->return['Message'] = $error; //Shows all error messages as a string
        }
        else
        {
            $this->load->model('users/login_model');
            $NumRows = $this->login_model->confirm_email($data['ActivationCode']);
            if ($NumRows['msg'] != 1)
            {
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = lang('msg' . $NumRows['msg']);
            }
            else
            {

                /* Define default variables */
                $Data['UserSocialID'] = '';
                $Data['DeviceID'] = DEFAULT_DEVICE_ID;
                $Data['DeviceToken'] = DEFAULT_DEVICE_ID;
                $Data['Latitude'] = '';
                $Data['Longitude'] = '';
                $Data['IPAddress'] = $this->input->ip_address();
                $Data['Email'] = $NumRows['email'];
                $Data['Username'] = $NumRows['email'];
                $Data['Password'] = '';
                $Data['Resolution'] = DEFAULT_RESOLUTION;
                $Data['Picture'] = '';
                $Data['Token'] = '';
                $Data['profileUrl'] = '';
                $Data['SourceID'] = 1;
                $Data['AutoLogin'] = true;
                $Data['DeviceTypeID'] = $this->login_model->get_device_type_id('IPhone');
                $login_data = $this->login_model->verify_login($Data);
                $this->return['Data'] = $login_data['Data'];
            }
        }
        $this->response($this->return); /* Final Output */
    }


}
