<?php
require_once APPPATH . 'modules/auth/controllers/Auth.php';

class Emailauth extends Auth {


    function __construct() {
        parent::__construct();
    }

    /**
     * used for save otp in db
     * @param
     * @return json array
     */
    public function send_otp($data) {
        $is_systemuser = 0;
        if (isset($data['is_systemuser']) && $data['is_systemuser'] == 1) {
            $is_systemuser = 1;
        }
        $where = $data['email'];
        $this->load->model(array('auth/Auth_nosql_model','notification/Notify_nosql_model'));
        $otp = $this->Auth_nosql_model->send_otp($data,TRUE,$is_systemuser);
        $data['otp'] = $otp;
        if ($is_systemuser == 0) {
            $content        = array('otp' => $otp, 'email' => $data['email']);
            $notify_data    = array();
            $notify_data['queue_name'] = "email_otp";
            $notify_data['notification_type']           = 133;
            $notify_data['notification_destination']    = 4;
            $notify_data["source_id"]   = 1;
            $notify_data["user_id"]     = $data['user_id'];
            $notify_data["user_name"]   = $data['user_name'];
            $notify_data["to"]          = $data["email"];
            $notify_data["added_date"]  = format_date();
            $notify_data["modified_date"] = format_date();
            $notify_data["subject"] = $this->lang->line('signup_email_subject');
            $notify_data["content"] = json_encode($content);
            $this->Notify_nosql_model->send_notification($notify_data);
        }
        return $otp;
    }

    /**
     * Used for validate google captcha
     * @param array $post_data
     * @return json array
     */
    public function validate_google_captcha($post_data){
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $user_ip = get_user_ip_address();
        $data = array(
                    'secret' => $this->app_config['allow_google_captcha']['custom_data']['google_captcha_secret'],
                    'response' => $post_data['token'],
                    'remoteip' => $user_ip
                );

        $option = array(
                        'http' => array(
                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                            'method' => 'POST',
                            'content' => http_build_query($data)
                        )
                    );
                 
        $context = stream_context_create($option);
        $response = file_get_contents($url,false,$context);
        $result = json_decode($response,true);
        return $result;
    }

    /**
     * used for user login with email
     * @param
     * @return json array
     */
    public function login_post() {
        $post_data = $this->input->post();
        if (isset($post_data)) {
            $validation_rule    =   array(                                
                                        array(
                                                'field' => 'email',
                                                'label' => $this->lang->line("email"),
                                                'rules' => 'trim|required|valid_email'
                                        ),
                                        array(
                                                'field' => 'device_type',
                                                'label' => $this->lang->line("device_type"),
                                                'rules' => 'trim|callback_check_device_type'
                                        ),
                                        array(
                                            'field' => 'facebook_id',
                                            'label' => $this->lang->line("facebook_id"),
                                            'rules' => 'callback_social_required'
                                        ),
                                        array(
                                            'field' => 'google_id',
                                            'label' => $this->lang->line("google_id"),
                                            'rules' => 'callback_social_required'
                                        )
                                    );
            if($this->app_config['allow_google_captcha']['key_value']){
            $validation_rule[] = array(
                                        'field' => 'token',
                                        'label' => "token",
                                        'rules' => 'trim|required'
                                    );
            }
            $this->form_validation->set_rules($validation_rule); 
            if($this->form_validation->run() == FALSE)  { //validate post parameter
                $this->send_validation_errors(); 
            }
            
            if($this->app_config['allow_google_captcha']['key_value']){
                $captcha = $this->validate_google_captcha($post_data);
                if(!isset($captcha['success']) || $captcha['success'] != true){
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error'] = "Sorry, your request blocked due to suspicious activity.";
                    $this->api_response();
                }
            }

            //for check user account blocked status
            $this->check_user_account_blocked_status();

            $this->load->model("emailauth/Emailauth_model");
            $current_date   = format_date();
            $device_type    = $post_data['device_type'];
            $user_profile   = $this->Emailauth_model->get_single_row("user_id,user_unique_id,referral_code,user_name,email,password,phone_no,phone_verfied,email_verified,IFNULL(facebook_id,'') as facebook_id,IFNULL(google_id,'') as google_id,status,is_systemuser", USER, array('email' => $post_data['email']));
            
            $is_user_exist       = 0; //0=Not exist,1=User already exist.
            $is_profile_complete = 0; // 0 = Not complete,1=complete
            $next_step = "otp";
            $user_data = array();
            //If user already exist than return user id with profile related information.
            if (!empty($user_profile)) {
                //if admin inactivate account then show error
                if (isset($user_profile['status']) && $user_profile['status'] == 0) {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error'] = $this->lang->line("your_account_deactivated");
                    $this->api_response();
                }

                //for check given mobile attached with social account or not
                $validate_social = $this->Emailauth_model->validate_user_social_id($post_data,$user_profile);
                 if (!$validate_social) {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->lang->line("email_already_attached_to_other");
                    $this->api_response();
                    
                }
                $is_user_exist = 1;
                if($user_profile['email_verified'] == 1 && !empty($user_profile['user_name']) && !empty($user_profile['email']))
                {
                    $is_profile_complete = 1;
                }
                $tmp_username                = $user_profile['user_name'];
                $user_data['user_id']        = $user_profile['user_id'];
                $user_data['user_unique_id'] = $user_profile['user_unique_id'];
                $user_data['email']          = strtolower($user_profile['email']);
                $user_data['user_name']      = $user_profile['user_name'];
                $user_data['is_systemuser']  = $user_profile['is_systemuser'];
                if (is_null($user_data['user_name']) || $user_data['user_name'] == "") {
                    $email_arr = explode("@", $user_data['email']);
                    $user_data['user_name'] = isset($email_arr[0]) ? $email_arr[0] : "";
                }
                if ((isset($user_profile['email_verified']) && $user_profile['email_verified'] == 0) || empty($user_profile['password'])) {
                    $tmp_user = array("user_id"=>$user_profile['user_id'],"user_unique_id"=>$user_profile['user_unique_id'],"phone_no"=>$user_profile['phone_no'],"user_name"=>$user_profile['user_name'],"email"=>$user_profile['email'],"facebook_id"=>$user_profile['facebook_id'],"google_id"=>$user_profile['google_id'],"phone_verfied"=>$user_profile['phone_verfied'],"email_verified"=>$user_profile['email_verified'],"status"=>$user_profile['status'],"referral_code"=>$user_profile['referral_code']);
                    $user_data['user_detail'] = $tmp_user;
                    $this->send_otp($user_data);
                    unset($user_data['user_detail']);
                }elseif(!empty($post_data['google_id']) || !empty($post_data['facebook_id'])) {
                    $this->send_otp($user_data);
                    $next_step = "otp";
                }else if(!empty($user_profile['password'])){
                    $next_step = "password";
                }

            } else { //if new user than create user_id and return.
                $tracking_source = array(
                    "cm" => isset($post_data['campaign']) ? $post_data['campaign'] : '',
                    "md" => isset($post_data['medium']) ? $post_data['medium'] : '',
                    "sr" => isset($post_data['source']) ? $post_data['source'] : '',
                    "tm" => isset($post_data['term']) ? $post_data['term'] : '',
                    "cn" => isset($post_data['content']) ? $post_data['content'] : '',
                );
                $data = array();
                $user_unique_id         = $this->Emailauth_model->_generate_key();
                $referral_code          = $this->Emailauth_model->_generate_referral_code();
                $data['user_unique_id'] = $user_unique_id;
                $data['referral_code']  = $referral_code;
                $data['last_login']     = $current_date;
                $data['added_date']     = $current_date;
                $data['modified_date']  = $current_date;
                $data['last_ip']        = get_user_ip_address();
                $data['status']         = 2;
                $data["email"]          = strtolower($post_data['email']);
                $data['phone_verfied']  = 0; //default not verified
                $data['device_type']    = $device_type;
                $data["is_systemuser"] = isset($post_data['is_systemuser']) ? $post_data['is_systemuser'] : 0;
                $data['tracking']       = $tracking_source ? json_encode($tracking_source):null;
                $user_id = $this->Emailauth_model->registration($data);
                if ($user_id) {
                    $user_data['user_id']       = $user_id;
                    $user_data['user_unique_id']= $user_unique_id;
                    $user_data['email']         = $post_data['email'];
                    $email_arr                  = explode("@", $post_data['email']);
                    $tmp_username               = isset($email_arr[0]) ? $email_arr[0] : "";
                    $user_data['user_name']     = $tmp_username;
                    $user_data['is_systemuser'] = $data["is_systemuser"];
                    $tmp_user = array("user_id"=>(string)$user_id,"user_unique_id"=>$user_unique_id,"phone_no"=>"","user_name"=>$user_data['user_name'],"email"=>$user_data['email'],"facebook_id"=>"","google_id"=>"","phone_verfied"=>0,"status"=>$data['status'],"referral_code"=>$referral_code);
                    $user_data['user_detail'] = $tmp_user;
                  
                    $this->send_otp($user_data);
                    unset($user_data['user_name']);
                    unset($user_data['user_detail']);
                }
            }

            // add user data in feed DB
            if($user_data['user_id']) {
                $user_sync_data = array();
                $user_sync_data['data'] = array(
                    "Action" => "Signup",
                    "UserID" => $user_data['user_id'],
                    "UserGUID" => $user_data['user_unique_id'],
                    "Email" => $user_data['email'],
                    "FirstName" => $tmp_username,
                    "LastName" => '',
                    "IPAddress" => get_user_ip_address(),
                    "DeviceTypeID" => $device_type
                );
                $this->load->helper('queue_helper');
                add_data_in_queue($user_sync_data, 'user_sync');
            }

            unset($user_data['user_id']);
            $user_data['is_user_exist']          = $is_user_exist;
            $user_data['next_step']          = $next_step;
            $user_data['is_profile_complete']    = $is_profile_complete;
            $this->api_response_arry['data']    = $user_data;
            $this->api_response();
        } else {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("input_invalid_format");
            $this->api_response();
        }
    }

    /**
     * used for validate otp
     * @param
     * @return json array
     */
    public function validate_otp_post() {
        
        $this->form_validation->set_rules('otp', $this->lang->line("confirmation_code"), 'trim|required|max_length[6]');
        $this->form_validation->set_rules('email', $this->lang->line("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('facebook_id', $this->lang->line("facebook_id"), 'callback_social_required|callback_facebook_valid');
        $this->form_validation->set_rules('google_id', $this->lang->line("google_id"), 'callback_social_required|callback_google_valid');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $this->load->model("emailauth/Emailauth_model");
        $post_data['email'] = strtolower($post_data['email']);
        $result = $this->Emailauth_model->check_email_otp($post_data);
        if(empty($result) || empty($result['status'])) {
            $this->api_response_arry["message"] = $result['message'];
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $user_data = $result['data'];
        $device_type = (!empty($post_data['device_type'])) ? $post_data['device_type'] : "3";
        $device_id   = (!empty($post_data['device_id'])) ? $post_data['device_id']: "";
        $is_profile_complete = 0; // 0 = Not complete,1=complete
        $next_step = "create-account";
        //If user already exist than return user id with profile related information.
        if (!empty($user_data['email']) && $user_data['email_verified'] == 1 && !empty($user_data['password']) && !empty($user_data['user_name'])) {
            $is_profile_complete = 1;

            if(!empty($post_data['facebook_id']) || !empty($post_data['google_id'])){
                if(!empty($post_data['facebook_id'])){
                    $social_data = ['facebook_id'=>$post_data['facebook_id']];
                }elseif(!empty($post_data['google_id'])){
                    $social_data = ['google_id'=>$post_data['google_id']];
                }
                $this->Emailauth_model->update_social_data($user_data['email'], $social_data);
            }
             $next_step = "login_success";
        }

        //save default username
        if(!isset($user_data['user_name']) || $user_data['user_name'] == ""){
            $user_data['user_name'] = $this->Emailauth_model->generate_user_name($user_data['email']);
        }
        
        $api_key = $this->Emailauth_model->generate_active_login_key($user_data, $device_type, $device_id);
        // add user data in feed DB
        $user_sync_data = array();
        $user_sync_data['data'] = array(
            "Action" => "Login",
            "UserID" => $user_data['user_id'],
            "LoginSessionKey" => $api_key,
            "IPAddress" => get_user_ip_address(),
            "DeviceToken" => $device_id,            
            "DeviceTypeID" => $device_type
        );
        $this->load->helper('queue_helper');
        add_data_in_queue($user_sync_data, 'user_sync');

        unset($user_data['user_id']);
        unset($user_data['password']);
        unset($user_data['otp_code']);
        unset($user_data['created_date']);
        unset($user_data['status']);
        unset($user_data['email_verified']);
        $response = array();
        $response[AUTH_KEY]                 = $api_key;
        $response['user_profile']           = $user_data;
        $response['is_profile_complete']    = $is_profile_complete;
        $response['next_step']              = $next_step;
        $this->api_response_arry['data']    = $response;
        $this->api_response_arry["message"] = $this->lang->line('email_verified_success');
        $this->api_response();
    }

    public function validate_login_post() {
        
        $this->form_validation->set_rules('email', $this->lang->line("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('password', $this->lang->line("password"), 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        //for check user account blocked status
        $this->check_user_account_blocked_status();
        
        $post_data = $this->input->post();
        $this->load->model("emailauth/Emailauth_model");
        $user_data = $this->Emailauth_model->get_single_row("user_id,user_unique_id,referral_code,email,password,IFNULL(user_name,'') AS user_name,IFNULL(phone_no,'') AS phone_no,phone_verfied,bs_status", USER, array('email' => $post_data['email'],'password' => md5($post_data['password'])));
        if (empty($user_data)) {
            //update wrong attempt count
            $this->Emailauth_model->update_user_attempt_count($post_data);

            $this->api_response_arry["message"] = $this->lang->line('invalid_email_or_password');
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }else{
            //update wrong attempt count
            $this->Emailauth_model->reset_user_attempt_count($user_data);
        }
        $device_type    = (!empty($post_data['device_type'])) ? $post_data['device_type'] : "3";
        $device_id      = (!empty($post_data['device_id'])) ? $post_data['device_id']: "";
        $is_profile_complete = 0; // 0 = Not complete,1=complete
         $next_step = "create-account";
        if (!empty($user_data['user_name'])) {
            $is_profile_complete = 1;
             $next_step = "login_success";
        }

        //check profile incomplete and referral code used or not and set next step
        $api_key        = $this->Emailauth_model->generate_active_login_key($user_data, $device_type, $device_id);
        unset($user_data['password']);
        $response = array();
        $response[AUTH_KEY] = $api_key;
        $response['user_profile'] = $user_data;
        $response['is_profile_complete'] = $is_profile_complete;
        $this->api_response_arry['data'] = $response;
        $this->api_response_arry["message"] = $this->lang->line('login_success');
        $this->api_response();
    }

    public function forgot_password_post() {
        $validation_rule    =   array(                                
                                        array(
                                            'field' => 'email',
                                            'label' => $this->lang->line("email"),
                                            'rules' => 'required|trim|min_length[5]|max_length[50]|valid_email'
                                        )
                                    );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("emailauth/Emailauth_model");
        $userdata = $this->Emailauth_model->get_single_row("user_id,user_unique_id,email,user_name,status", USER, array('email' => $post_data['email']));

        if (empty($userdata)) {
            $this->api_response_arry["error"] = array('email' => $this->lang->line('email_not_exist'));
            $this->api_response_arry["message"] = $this->lang->line('email_not_exist');
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        if (in_array($userdata['status'], array('0', '3'))) {
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['error'] = array('email' => $this->lang->line("your_account_deactivated"));
            $this->api_response();
        }

        $current_date = format_date();
        $time = strtotime($current_date);
        $new_password_key = $userdata['user_unique_id'].'_'.$time;
        $update = array(
            'new_password_key' => $new_password_key,
            'new_password_requested' => $current_date
        );
        $this->Emailauth_model->update(USER, $update, array("user_id" => $userdata['user_id']));
        $userdata['new_password_key'] = $new_password_key;
        $link = BASE_APP_PATH."forgot-password?key=".base64_encode($userdata['new_password_key']);
        $notify_data = array();
        $notify_data['notification_type']       = 15;
        $notify_data['notification_destination']= 4;
        $notify_data["source_id"]               = 1;
        $notify_data["user_id"]                 = $userdata['user_id'];
        $notify_data["user_name"]               = $userdata["user_name"];
        $notify_data["to"]                      = $userdata["email"];
        $notify_data["added_date"]              = $current_date;
        $notify_data["modified_date"]           = $current_date;
        $notify_data["subject"]                 = $this->lang->line('forgot_password_email_subject');
        $notify_data["content"]                 = json_encode(array('link' => $link));
       
        $this->load->model('notification/Notify_nosql_model');
        $this->Notify_nosql_model->send_notification($notify_data);

        $this->api_response_arry['message'] = sprintf($this->lang->line('forgot_pass_mail_sent'), $post_data['email']);
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }

    public function forgot_password_validate_code_post() {
        $this->form_validation->set_rules('key', $this->lang->line("forgot_password_key"), 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $key = base64_decode($post_data['key']);
        $this->load->model("emailauth/Emailauth_model");
        $data = $this->Emailauth_model->get_new_password_key($key);
        if (!$data) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array('key' => $this->lang->line('invalid_password_link'));
            $this->api_response();
        }

        $time           = strtotime($data['new_password_requested'] . " +24 hours");
        $current_time   = strtotime(format_date());
        if ($time < $current_time) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array('key' => $this->lang->line('invalid_link'));
            $this->api_response();
        }

        $this->api_response_arry['message'] = $this->lang->line('valid_code');
        $this->api_response();
    }

    public function reset_password_post() {
        $this->form_validation->set_rules('key', $this->lang->line("forgot_password_key"), 'trim|required');
        $this->form_validation->set_rules('password', $this->lang->line("password"), 'required|min_length[8]|max_length[50]');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data      = $this->input->post();
        $key            = base64_decode($post_data['key']);
        $current_date   = format_date();
        $this->load->model("emailauth/Emailauth_model");
        $data = $this->Emailauth_model->get_new_password_key($key);
        if (!$data) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array('key' => $this->lang->line('invalid_password_link'));
            $this->api_response();
        }

        $user_unique_id = $data['user_unique_id'];
        $time           = strtotime($data['new_password_requested'] . " +24 hours");
        $current_time   = strtotime($current_date);
        if ($time < $current_time) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = array('key' => $this->lang->line('invalid_link'));
            $this->api_response();
        }

        $password = md5($post_data['password']);
        $user_data = array('new_password_requested' => NULL, 'new_password_key' => NULL, 'password' => $password, 'modified_date' => $current_date);
        if ($data['status'] == '2') { //If email not verified then verify it 
            $user_data['status'] = '1';
            $user_data['new_email_key'] = NULL;
            $user_data['new_email_requested'] = NULL;
        }

        $this->Emailauth_model->update(USER, $user_data, array('user_id' => $data['user_id']));
        $this->api_response_arry['message'] = $this->lang->line('password_reset_success');
        $this->api_response();
    }

    public function resend_otp_post() {
        $this->form_validation->set_rules('email', $this->lang->line("email"), 'trim|required|valid_email');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_data  = $this->input->post();
        $email      = $post_data['email'];
        $this->load->model("emailauth/Emailauth_model");
        $userdata = $this->Emailauth_model->get_single_row("user_id,user_unique_id,referral_code,phone_no,phone_code,phone_verfied,IFNULL(facebook_id,'') as facebook_id,IFNULL(google_id,'') as google_id,IFNULL(user_name,'') as user_name,IFNULL(email,'') as email,status,is_systemuser", USER, array('email' => $email));
        if(!$userdata && LOGIN_FLOW != "2") {
            $error = array('phone_no'=>$this->lang->line('no_account_found'));
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error']           = $error;
            $this->api_response();
        }

        $this->load->model("auth/Auth_nosql_model");
        //check for phone number existed
        $record = $this->Auth_nosql_model->select_one_nosql(MANAGE_OTP, array("phone_no" => $email));
        if(!empty($record)) {
            $created_date   = $record['updated_at'];
            $now            = strtotime(format_date());
            $time           = strtotime($created_date.' +30 second');
            if($time > $now) {
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']    = $this->lang->line('otp_multiple_request');
                $this->api_response();
            }
        }

        if(!empty($userdata)){
            $email_arr              = explode("@", $userdata['email']);
            $tmp_username           = isset($email_arr[0]) ? $email_arr[0] : "";
            $userdata['user_name']  = $tmp_username;
            $tmp_user = array("user_id"=>$userdata['user_id'],"user_unique_id"=>$userdata['user_unique_id'],"phone_no"=>$userdata['phone_no'],"user_name"=>$userdata['user_name'],"email"=>$userdata['email'],"facebook_id"=>$userdata['facebook_id'],"google_id"=>$userdata['google_id'],"phone_verfied"=>$userdata['phone_verfied'],"status"=>$userdata['status'],"referral_code"=>$userdata['referral_code']);
            $userdata['user_detail'] = $tmp_user;
            $message = $this->lang->line("email_otp_send_success");
            $this->send_otp($userdata);
        }

        $this->api_response_arry['data'] = array();
        $this->api_response_arry['message'] = $this->lang->line("resend_otp_send_success");
        $this->api_response();
    }

    public function social_login_post() {
        $validation_rule    =   array(                                
                                    array(
                                        'field' => 'device_type',
                                        'label' => $this->lang->line("device_type"),
                                        'rules' => 'trim|callback_check_device_type'
                                    ), 
                                    array(
                                        'field' => 'device_id',
                                        'label' => $this->lang->line("device_id"),
                                        'rules' => 'trim|callback_check_device_id'
                                    ),
                                    array(
                                        'field' => 'facebook_id',
                                        'label' => $this->lang->line("facebook_id"),
                                        'rules' => 'callback_social_required|callback_facebook_valid'
                                    ),
                                    array(
                                        'field' => 'google_id',
                                        'label' => $this->lang->line("google_id"),
                                        'rules' => 'callback_social_required|callback_google_valid'
                                    )
                                );
        $this->form_validation->set_rules($validation_rule); 
        if($this->form_validation->run() == FALSE)  { //validate post parameter
            $this->send_validation_errors(); 
        }

        $post_data = $this->input->post();
        $social_data = array();        
        if (isset($post_data['facebook_id']) && $post_data['facebook_id'] != "") {
            $social_data = array("facebook_id" => $post_data['facebook_id']);
        } else if (isset($post_data['google_id']) && $post_data['google_id'] != "") {
            $social_data = array("google_id" => $post_data["google_id"]);
        }

        if (empty($social_data)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('social_required');
            $this->api_response();
        }
        
        $current_date = format_date();
        $device_type = $post_data['device_type'] ? $post_data['device_type'] : 3;
        $device_id = $post_data['device_id'] ? $post_data['device_id'] : '';
        $this->load->model("emailauth/Emailauth_model");
        $user_profile = $this->Emailauth_model->get_single_row("user_id,user_unique_id,referral_code,facebook_id,google_id,IFNULL(user_name,'') AS user_name,email,phone_no,password,email_verified", USER, $social_data);

        $is_user_exist          = 0; //0=Not exist,1=User already exist.
        $is_profile_complete    = 0; // 0 = Not complete,1=complete
        $next_step              = 'email';
        $api_key                = '';
        $user_data              = array();

        $update_email = 0;
        if (empty($user_profile) && !empty($post_data['email'])) {
            $update_email = 1;
            $user_profile = $this->Emailauth_model->get_single_row("user_id,user_unique_id,phone_no,facebook_id,google_id,IFNULL(user_name,'') AS user_name,email,password,bs_status,email_verified", USER, array('email' => $post_data['email']));
        }
        //If user already exist than return user id with profile related information.
        if (!empty($user_profile)) {
            $next_step = "login_success";
            $is_user_exist = 1;
            if ($user_profile['email_verified'] == 1 && !empty($user_profile['user_name']) && !empty($user_profile['email'])) {
                $is_profile_complete = 1;
            }
            
            $user_data['user_unique_id']    = $user_profile['user_unique_id'];
            $user_data['user_name']         = $user_profile['user_name'];
            $user_data['email']             = $user_profile['email'];

            if ($update_email) {
                $this->Emailauth_model->update_user_data($user_profile['user_id'], $social_data, array('status' =>1));
            }

            //check profile incomplete and referral code used or not and set next step
            if($is_profile_complete == 0) {
               $next_step = "create-account";
            }

            //save default username
            if(!isset($user_profile['user_name']) || $user_profile['user_name'] == ""){
                $user_data['user_name'] = $user_profile['user_name'] = $this->Emailauth_model->generate_user_name($user_profile['email']);
            }
            $api_key = $this->Emailauth_model->generate_active_login_key($user_profile, $device_type, $device_id);

        } else if (isset($post_data['email']) && $post_data['email'] != "") {

            $tracking_source = array(
                "cm" => isset($post_data['campaign']) ? $post_data['campaign'] : '',
                "md" => isset($post_data['medium']) ? $post_data['medium'] : '',
                "sr" => isset($post_data['source']) ? $post_data['source'] : '',
                "tm" => isset($post_data['term']) ? $post_data['term'] : '',
                "cn" => isset($post_data['content']) ? $post_data['content'] : '',
            );

            $data = $social_data;
            $user_unique_id = $this->Emailauth_model->_generate_key();
            $referral_code = $this->Emailauth_model->_generate_referral_code();
            $data['user_unique_id'] = $user_unique_id;
            $data['referral_code'] = $referral_code;
            $data['last_login']     = $current_date;
            $data['added_date']     = $current_date;
            $data['modified_date']  = $current_date;
            $data['last_ip']        = get_user_ip_address();
            $data['status']         = '1';
            $data["email"]          = $post_data['email'];
            $data['email_verified'] = 1; //default not verified
            $data['phone_verfied']  = 0; //default not verified
            $data['device_type']    = $device_type;
            $data['tracking'] = $tracking_source ? json_encode($tracking_source):null; 
            $user_id = $this->Emailauth_model->registration($data);
            if ($user_id) {
                $next_step              = "create-account";
                $user_data['user_unique_id'] = $user_unique_id;
                $user_data['email']     = $post_data['email'];
                
                $login_data = array();
                $login_data['user_id'] = $user_id;
                $login_data['user_unique_id'] = $user_unique_id;
                $login_data['referral_code'] = $referral_code;
                $login_data['email'] = $data['email'];
                $login_data['phone_no'] = "";
                $api_key = $this->Emailauth_model->generate_active_login_key($login_data, $device_type, $device_id);
            }
        }

        $response = array();
        $response['user_profile']   = $user_data;
        $response[AUTH_KEY]         = $api_key;
        $response['is_user_exist']  = $is_user_exist;
        $response['is_profile_complete'] = $is_profile_complete;
        $response['next_step']      = $next_step;
        $this->api_response_arry['data']    = $response;
        $this->api_response();
    }

    public function check_username() {
        $post_data = $this->post();
        $this->load->model("emailauth/Emailauth_model"); 
        $user_data = $this->Emailauth_model->get_single_row('user_name,user_id', USER, array("user_name" => $post_data['user_name']));

        if (!$user_data || ($user_data["user_name"] == $post_data['user_name'] && $user_data["user_id"] == $this->user_id)) {
            return TRUE;
        }
        $this->form_validation->set_message('check_username', $this->lang->line("user_name_already_exists"));
        return FALSE;
    }

    /**
     * used for user login with email
     * @param
     * @return json array
     */
    public function signup_post() {
        $post_data  = $this->input->post();
        $validation_rule = array(                                
                                    array(
                                            'field' => 'email',
                                            'label' => $this->lang->line("email"),
                                            'rules' => 'trim|required|valid_email'
                                    ),
                                    array(
                                            'field' => 'user_name',
                                            'label' => $this->lang->line("username"),
                                            'rules' => 'trim|required|callback_check_username'
                                    ),
                                    array(
                                            'field' => 'password',
                                            'label' => 'password',
                                            'rules' => 'trim|required'
                                    ),
                                    array(
                                            'field' => 'device_type',
                                            'label' => $this->lang->line("device_type"),
                                            'rules' => 'trim|callback_check_device_type'
                                    )
                                );
        if($this->app_config['allow_google_captcha']['key_value']){
        $validation_rule[] = array(
                                    'field' => 'token',
                                    'label' => "token",
                                    'rules' => 'trim|required'
                                );
        }
        $this->form_validation->set_rules($validation_rule); 
        if($this->form_validation->run() == FALSE)  { //validate post parameter
            $this->send_validation_errors(); 
        }
        
        if($this->app_config['allow_google_captcha']['key_value']){
            $captcha = $this->validate_google_captcha($post_data);
            if(!isset($captcha['success']) || $captcha['success'] != true){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = "Sorry, your request blocked due to suspicious activity.";
                $this->api_response();
            }
        }

        $this->load->model("emailauth/Emailauth_model");
        //validate referral code
        if(isset($post_data['referral_code']) && $post_data['referral_code'] != ""){
            $refer_user_detail = $this->Emailauth_model->get_single_row('user_id,user_name,phone_no,email', USER, array("referral_code" => $post_data['referral_code']));
            if (empty($refer_user_detail)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line("enter_valid_ref_code");
                $this->api_response();
            }
        }

        $current_date   = format_date();
        $device_type    = $post_data['device_type'];
        $user_profile   = $this->Emailauth_model->get_single_row("user_id,user_unique_id,referral_code,user_name,email,password,phone_no,phone_verfied,email_verified,IFNULL(facebook_id,'') as facebook_id,IFNULL(google_id,'') as google_id,status,is_systemuser",USER,array('email' => $post_data['email']));
        if (!empty($user_profile)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("email_already_exists_message");
            $this->api_response();
        }

        $user_data = array();
        $user_data['user_id'] = 0;
        $user_data['email'] = $post_data['email'];
        $user_data['user_name'] = $post_data['user_name'];
        $user_data['is_systemuser'] = isset($data["is_systemuser"]) ? $data["is_systemuser"] : 0;
        $this->send_otp($user_data);
        //echo "<pre>";print_r($user_data);die;
        unset($user_data['user_id']);
        $user_data['is_user_exist'] = 0;
        $user_data['next_step'] = "otp";
        unset($user_data['is_systemuser']);
        $this->api_response_arry['data'] = $user_data;
        $this->api_response();
    }

    /**
     * used for validate otp
     * @param
     * @return json array
     */
    public function signup_validate_post() {
        $validation_rule = array(                                
                                    array(
                                            'field' => 'otp',
                                            'label' => $this->lang->line("confirmation_code"),
                                            'rules' => 'trim|required|max_length[4]'
                                    ),
                                    array(
                                            'field' => 'email',
                                            'label' => $this->lang->line("email"),
                                            'rules' => 'trim|required|valid_email'
                                    ),
                                    array(
                                            'field' => 'user_name',
                                            'label' => $this->lang->line("username"),
                                            'rules' => 'trim|required|callback_check_username'
                                    ),
                                    array(
                                            'field' => 'password',
                                            'label' => 'password',
                                            'rules' => 'trim|required'
                                    ),
                                    array(
                                            'field' => 'device_type',
                                            'label' => $this->lang->line("device_type"),
                                            'rules' => 'trim|callback_check_device_type'
                                    )
                                );
        if($this->app_config['allow_google_captcha']['key_value']){
        $validation_rule[] = array(
                                    'field' => 'token',
                                    'label' => "token",
                                    'rules' => 'trim|required'
                                );
        }
        $this->form_validation->set_rules($validation_rule); 
        if($this->form_validation->run() == FALSE)  { //validate post parameter
            $this->send_validation_errors(); 
        }
        
        $post_data  = $this->input->post();
        if($this->app_config['allow_google_captcha']['key_value']){
            $captcha = $this->validate_google_captcha($post_data);
            if(!isset($captcha['success']) || $captcha['success'] != true){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = "Sorry, your request blocked due to suspicious activity.";
                $this->api_response();
            }
        }

        $this->load->model("emailauth/Emailauth_model");
        $refer_user_detail = array();
        if(isset($post_data['referral_code']) && $post_data['referral_code'] != ""){
            $refer_user_detail = $this->Emailauth_model->get_single_row('user_id,user_name,phone_no,email', USER, array("referral_code" => $post_data['referral_code']));
            if (empty($refer_user_detail)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line("enter_valid_ref_code");
                $this->api_response();
            }
        }

        $user_profile = $this->Emailauth_model->get_single_row("user_id,user_unique_id,referral_code,user_name,email,password,phone_no,phone_verfied,email_verified,IFNULL(facebook_id,'') as facebook_id,IFNULL(google_id,'') as google_id,status,is_systemuser",USER,array('email' => $post_data['email']));
        if (!empty($user_profile)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("email_already_exists_message");
            $this->api_response();
        }

        $current_date = format_date();
        $post_data['email'] = strtolower($post_data['email']);
        $condition = array("phone_no" => $post_data['email'], "otp_code" => $post_data['otp']);
        $this->load->model("auth/Auth_nosql_model");
        $otp_record = $this->Auth_nosql_model->select_one_nosql(MANAGE_OTP, $condition);
        if(empty($otp_record)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('phone_verified_failed');
            $this->api_response();
        }
        $device_type = (!empty($post_data['device_type'])) ? $post_data['device_type'] : "3";
        $device_id   = (!empty($post_data['device_id'])) ? $post_data['device_id']: "";
        $tracking_source = array(
            "cm" => isset($post_data['campaign']) ? $post_data['campaign'] : '',
            "md" => isset($post_data['medium']) ? $post_data['medium'] : '',
            "sr" => isset($post_data['source']) ? $post_data['source'] : '',
            "tm" => isset($post_data['term']) ? $post_data['term'] : '',
            "cn" => isset($post_data['content']) ? $post_data['content'] : '',
        );
        $data = array();
        $user_unique_id         = $this->Emailauth_model->_generate_key();
        $referral_code          = $this->Emailauth_model->_generate_referral_code();
        $data['user_unique_id'] = $user_unique_id;
        $data['referral_code']  = $referral_code;
        $data['last_login']     = $current_date;
        $data['added_date']     = $current_date;
        $data['modified_date']  = $current_date;
        $data['last_ip']        = get_user_ip_address();
        $data['status']         = 1;
        $data["email"]          = strtolower($post_data['email']);
        $data["user_name"]      = $post_data['user_name'];
        $data["password"]       = md5($post_data['password']);
        $data['email_verified'] = 1;
        $data['phone_verfied']  = 0; //default not verified
        $data['device_type']    = $device_type;
        $data["is_systemuser"] = isset($post_data['is_systemuser']) ? $post_data['is_systemuser'] : 0;
        $data['tracking']       = $tracking_source ? json_encode($tracking_source):null;
        $user_id = $this->Emailauth_model->registration($data);
        if ($user_id) {
            $user_data['user_id']       = $user_id;
            $user_data['user_unique_id']= $user_unique_id;
            $user_data['email']         = $post_data['email'];
            $user_data['user_name']     = $post_data['user_name'];
            $user_data['referral_code'] = $referral_code;
            $user_data['is_systemuser'] = $data["is_systemuser"];
            $user_data['phone_no'] = "";
            $user_data['bs_status'] = NULL;
            $tmp_user = array("user_id"=>(string)$user_id,"user_unique_id"=>$user_unique_id,"phone_no"=>"","user_name"=>$user_data['user_name'],"email"=>$user_data['email'],"facebook_id"=>"","google_id"=>"","email_verified"=>1,"phone_verfied"=>0,"status"=>$data['status'],"referral_code"=>$referral_code);
            $user_data['user_detail'] = $tmp_user;
            $api_key = $this->Emailauth_model->generate_active_login_key($user_data, $device_type, $device_id);

            
            //[NRS - Signup bonus/coins/real cash to referral users]
            if (!empty($refer_user_detail)) {
                $source_type = 5;
                if(!empty($post_data['source_type'])) {
                    $source_type = $post_data['source_type'];
                }
                $friend_detail = array(
                    'email' => $post_data['email'],
                    'user_id' => $user_id,
                    'user_name' => $post_data['user_name'],
                    'phone_no' => $post_data['phone_no']
                );
                $this->add_bonus($friend_detail, $refer_user_detail['user_id'], 1, $source_type);  
                $user_cache_key = "user_balance_".$refer_user_detail['user_id'];
                $this->delete_cache_data($user_cache_key);                    
            }

            // add user data in feed DB
            if($user_data['user_id']) {
                $user_sync_data = array();
                $user_sync_data['data'] = array(
                    "Action" => "Signup",
                    "UserID" => $user_data['user_id'],
                    "UserGUID" => $user_data['user_unique_id'],
                    "Email" => $user_data['email'],
                    "FirstName" => $user_data['user_name'],
                    "LastName" => '',
                    "IPAddress" => get_user_ip_address(),
                    "DeviceToken" => $device_id,
                    "DeviceTypeID" => $device_type,
                    "LoginSessionKey" => $api_key
                );
                $this->load->helper('queue_helper');
                add_data_in_queue($user_sync_data, 'user_sync');
            }

            unset($user_data['is_systemuser']);
            unset($user_data['bs_status']);
            $response = array();
            $response[AUTH_KEY]                 = $api_key;
            $response['user_profile']           = $user_data;
            $response['is_profile_complete']    = 1;
            $response['next_step']              = "login_success";
            $this->api_response_arry['data']    = $response;
            $this->api_response_arry["message"] = $this->lang->line('signup_success');
            $this->api_response();
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = "Something went wrong while signup. please try again.";
            $this->api_response();
        }
    }

    /**
     * used for user login with email
     * @param
     * @return json array
     */
    public function email_login_post() {
        $validation_rule    =   array(                                
                                    array(
                                            'field' => 'email',
                                            'label' => $this->lang->line("email"),
                                            'rules' => 'trim|required|valid_email'
                                    ),
                                    array(
                                            'field' => 'password',
                                            'label' => $this->lang->line("password"),
                                            'rules' => 'trim|required'
                                    ),
                                    array(
                                            'field' => 'device_type',
                                            'label' => $this->lang->line("device_type"),
                                            'rules' => 'trim|callback_check_device_type'
                                    )
                                );
        if($this->app_config['allow_google_captcha']['key_value']){
            $validation_rule[] = array(
                                    'field' => 'token',
                                    'label' => "token",
                                    'rules' => 'trim|required'
                                );
        }
        $this->form_validation->set_rules($validation_rule); 
        if($this->form_validation->run() == FALSE)  { //validate post parameter
            $this->send_validation_errors(); 
        }
        $post_data = $this->input->post();
        if($this->app_config['allow_google_captcha']['key_value']){
            $captcha = $this->validate_google_captcha($post_data);
            if(!isset($captcha['success']) || $captcha['success'] != true){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = "Sorry, your request blocked due to suspicious activity.";
                $this->api_response();
            }
        }

        //for check user account blocked status
        $this->check_user_account_blocked_status();

        $current_date   = format_date();
        $device_type    = (!empty($post_data['device_type'])) ? $post_data['device_type'] : "3";
        $device_id      = (!empty($post_data['device_id'])) ? $post_data['device_id']: "";
        $this->load->model("emailauth/Emailauth_model");
        $user_profile = $this->Emailauth_model->get_single_row("user_id,user_unique_id,referral_code,user_name,email,password,IFNULL(phone_no,'') as phone_no,phone_verfied,email_verified,IFNULL(facebook_id,'') as facebook_id,IFNULL(google_id,'') as google_id,status,bs_status,is_systemuser", USER, array('email' => $post_data['email']));
        if (empty($user_profile)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("email_not_exist");
            $this->api_response();
        }else if($user_profile['password'] != md5($post_data['password'])){
            //update wrong attempt count
            $this->Emailauth_model->update_user_attempt_count($post_data);

            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("invalid_email_or_password");
            $this->api_response();
        }else if (isset($user_profile['status']) && $user_profile['status'] != 1) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("your_account_deactivated");
            $this->api_response();
        }else{
            //update wrong attempt count
            $this->Emailauth_model->reset_user_attempt_count($user_profile);

            $api_key = $this->Emailauth_model->generate_active_login_key($user_profile, $device_type, $device_id);
            unset($user_profile['password']);
            unset($user_profile['bs_status']);
            unset($user_profile['is_systemuser']);
            $response = array();
            $response[AUTH_KEY] = $api_key;
            $response['user_profile'] = $user_profile;
            $response['is_profile_complete'] = 1;
            $response['next_step'] = "login_success";
            $this->api_response_arry['data'] = $response;
            $this->api_response_arry["message"] = $this->lang->line('login_success');
            $this->api_response();
        }
    }
}