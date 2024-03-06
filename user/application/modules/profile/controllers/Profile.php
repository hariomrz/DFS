<?php


class Profile extends Common_Api_Controller {    
    function __construct() {
        parent::__construct();        
    }

    /**
     * @method get_user_setting
     * @uses method to get user setting related to status of different coach marks
     * @param NA
     * @since Dec 2019
     * */
    private function get_user_setting()
    {
        $this->load->model("auth/Auth_nosql_model");
        $user_setting = $this->Auth_nosql_model->select_one_nosql('user_setting',array("user_id"=>$this->user_id));

        if(empty($user_setting))
        {
            //insert user setting
            $user_setting = array(
                'user_id' => $this->user_id,
                'earn_coin' => 0,
                'redeem' => 0,
                'refer_a_friend' => 0
            );
            $this->Auth_nosql_model->insert_nosql('user_setting',$user_setting);
        }
       
        
        return $user_setting;
    }

    /**
     * @method update_user_setting
     * @uses method to update user setting related to status of different coach marks
     * @param Array $_POST it contains earn_coins,redeem,refer_a_friend
     * @since Dec 2019
     * */
    function update_user_setting_post()
    {
        $post = $this->input->post();
        $this->form_validation->set_rules('earn_coin', 'Earn Coin', 'trim|required');
        $this->form_validation->set_rules('redeem', 'Redeem', 'trim|required');
        $this->form_validation->set_rules('refer_a_friend', 'Refer a Friend', 'trim|required');

        foreach($post as $key => $value)
        {
             if(!in_array($value,array(0,1)))
             {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line("enter_valid_user_setting");
                $this->api_response();
             }   

        }

        $user_setting = array(
            //'user_id' => $this->user_id,
            'earn_coin' => $post['earn_coin'],
            'redeem' => $post['redeem'],
            'refer_a_friend' => $post['refer_a_friend']
        );
        $this->Auth_nosql_model->update_nosql('user_setting',array("user_id"=>$this->user_id),$user_setting);

        $this->api_response();
    }
    /**
     * update_profile_data used to update user details
     * @param
     * @return json array
     */
    public function update_profile_data_post() {
        $post_data = $this->post();
        $this->form_validation->set_rules('step', 'signup step', 'trim|required');

        if (isset($post_data['step']) && $post_data['step'] == "username") {
            $this->form_validation->set_rules('user_name', 'username', 'trim|required|callback_check_username');
        }
        if (isset($post_data['step']) && $post_data['step'] == "email") {
            $this->form_validation->set_rules('email', 'email', 'trim|required|valid_email|callback_check_email');
        }
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $this->load->model("Profile_model");
        $user_info = $this->Profile_model->get_single_row("user_id,phone_no,user_name,email,IFNULL(image,'') as image", USER, array('user_id' => $this->user_id)); 
        if(!empty($user_info)){
            $this->email = $user_info['email'];
        }
        //update username and email
        $random_user_name = "";
        $user_data = array();
        $nosql_data = array();
        $today = format_date();
        $this->load->model("Profile_model");
        if ($this->email == "" && $post_data['step'] == "email" && isset($post_data["email"]) && !empty($post_data["email"])) {
            $user_data['email'] = $post_data["email"];
            $nosql_data['email'] = $post_data["email"];
 
            $random_user_name = $this->Profile_model->generate_user_name($user_data['email']);
            //for update user first_name if get from social media
            if (isset($post_data['first_name']) && $post_data['first_name'] != "") {
                $user_data['first_name'] = $post_data['first_name'];
            }
        } else if ($post_data['step'] == "username" && isset($post_data["user_name"]) && !empty($post_data["user_name"])) {
            $user_data['user_name'] = $post_data["user_name"];
            $nosql_data['user_name'] = $post_data["user_name"];
            //email
            $notify_data = array();
            $notify_data['notification_type'] = 130;
            $notify_data['notification_destination'] = 4;
            $notify_data["source_id"] = 1;
            $notify_data["user_id"] = $this->user_id;
            $notify_data["user_name"] = $post_data["user_name"];
            $notify_data["to"] = $this->email;
            $notify_data["added_date"] = $today;
            $notify_data["modified_date"] = $today;
            $notify_data["subject"] = $this->lang->line('subject_welcome_email');
            $notify_data["content"] = json_encode(array());

            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($notify_data);

            // add user data in feed DB
            $user_sync_data = array();
            $user_sync_data['data'] = array(
                "Action" => "username",
                "UserID" => $this->user_id,
                "FirstName" => $post_data['user_name']
            );
            $this->load->helper('queue_helper');
            add_data_in_queue($user_sync_data, 'user_sync');
        }

        if (!empty($user_data)) {
            $this->Profile_model->update_user($this->user_id, $user_data);
        }

        if(!empty($nosql_data)){
            $this->load->model("auth/Auth_nosql_model");
            $this->Auth_nosql_model->update_all_nosql(ACTIVE_LOGIN,array("user_id"=>$this->user_id),$nosql_data);
        }

        //apply referral code
        if ($this->email == "" && $post_data['step'] == "referral" && isset($post_data['referral_code']) && !empty($post_data['referral_code'])) {
            $user_detail = $this->Profile_model->get_single_row('user_id,user_name,phone_no,email', USER, array("referral_code" => $post_data['referral_code'],"user_id !="=>$this->user_id));

            if (empty($user_detail)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line("enter_valid_ref_code");
                $this->api_response();
            }

            $source_type = 5;
            if (!empty($post_data['source_type'])) {
                $source_type = $post_data['source_type'];
            }

            //$friend_detail = $this->Profile_model->get_single_row('user_id,user_name,phone_no,first_name,last_name,email', USER, array("user_id" => $this->user_id));
            $friend_detail = array(
                'email' => $this->email,
                'user_id' => $this->user_id,
                'user_name' => $this->user_name,
                'phone_no' => $this->phone_no
            );
            //[NRS - Signup bonus/coins/real cash to referral users]
            if (!empty($user_detail) && !empty($friend_detail)) {
                $this->add_bonus($friend_detail, $user_detail['user_id'], 1, $source_type);
                //$this->add_signup_bonus_for_referral($friend_detail, $user_detail['user_id'], $source_type);
            }
        }

        $message = $this->lang->line('signup_success');

        if ($post_data['step'] == "referral") {
            $message = $this->lang->line('referral_code_applied');
        } else if ($post_data['step'] == "username") {
            //$check_affililate_history = $this->Profile_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY, array("friend_id" => $this->user_id, "status" => 1, "affiliate_type" => 1, "user_id != " => "0"));
            $check_affililate_history = $this->Profile_model->check_already_refered(0, $this->user_id);
            if (empty($check_affililate_history)) { //[NRS - Signup bonus/coins/real cash to user w/o referral]
                if(isset($post_data['affcd']) && $post_data['affcd']!=''){
                    $affiliate_detail = $this->Profile_model->get_single_row('user_id', USER, array("referral_code" => $post_data['affcd'],"user_id !="=>$this->user_id,"is_affiliate"=>1));
                    if (empty($affiliate_detail)) {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['global_error'] = $this->lang->line("enter_valid_aff_code");
                        $this->api_response();
                    }
                    $friend_detail = array(
                        'email' => $this->email,
                        'user_id' => $this->user_id,
                        'user_name' => $this->user_name,
                        'phone_no' => $this->phone_no,
                        'is_affiliate' =>1,
                    );
                    $this->add_bonus($friend_detail, $affiliate_detail['user_id'], 6,3);
                }
                else{
                $user_detail = array(
                    'email' => $this->email,
                    'user_id' => $this->user_id,
                    'user_name' => $this->user_name,
                    'phone_no' => $this->phone_no
                );
                $this->add_bonus($user_detail, 0, 6);
                }
                //$this->add_signup_bonus_without_referral($user_detail);
            }

            if(isset($post_data['campaign_code']) && $post_data['campaign_code']!='' && $this->app_config['new_affiliate']['key_value']==1){
                
                $this->db->update(USER,["campaign_code"=>$post_data['campaign_code']],["user_id"=>$this->user_id]);
                  
                $user_data = array(
                    "campaign_code"=>$post_data['campaign_code'],
                    "visit_code"=>$post_data['visit_code'],
                    "user_id"=>$this->user_id,
                    "name"=>$post_data['user_name'],
                    "mobile"=>$this->phone_no,
                    "email"=>$this->email,
                 );
                 
                 $this->load->helper('queue_helper');
                 add_data_in_queue($user_data, 'af_camp_user');
            }

             //for tracking user and saving data
            if(isset($post_data['user_track_id']) && !empty($post_data['user_track_id']))
            {
                $this->load->helper('queue_helper');
                $content                    = array();
                $content['type']            = 'SIGNUP';
                $content['user_track_id']   = $post_data['user_track_id'];
                $content['user_id']         = $this->user_id;
                $content['current_date']    = format_date();
                add_data_in_queue($content,'track_user');
            }
            $message = $this->lang->line('username_update_success');
        } else if ($post_data['step'] == "email") {
            $message = $this->lang->line('email_update_success');
        }

        $is_referral_used = 0;
        $referral_code_used = '';

        if ($post_data['step'] == "username")
        {
            $with_referral = $this->Profile_model->get_single_row('user_id, ', USER_AFFILIATE_HISTORY, array('friend_id' => $this->user_id,'affiliate_type' => 1));
            if (!empty($with_referral))
            {
                $is_referral_used = 1;
                $code = $this->Profile_model->get_single_row('referral_code', USER, array("user_id" => $with_referral['user_id']));
                $referral_code_used = $code['referral_code'];
            }

            if (isset($post_data['is_signup_from_contest']) && $post_data['is_signup_from_contest'] == 1 && !empty($post_data['contest_unique_id']))
            {
                $contest_type = isset($post_data['contest_type']) ? $post_data['contest_type'] : "1";
                $new_user_signup_data                       = array();
                $new_user_signup_data['user_id']            = $this->user_id;
                $new_user_signup_data['contest_unique_id']  = $post_data['contest_unique_id'];
                $new_user_signup_data['contest_type']  = $contest_type;
                $new_user_signup_data['signup_date']        = date('Y-m-d',strtotime(format_date()));
                // echo "<pre>";print_r($new_user_signup_data);

                $this->load->model("auth/Auth_nosql_model");
                $new_user_data = $this->Auth_nosql_model->select_one_nosql('private_contest_new_users',array("user_id"=>$this->user_id, "contest_unique_id"=>$post_data['contest_unique_id'],"contest_type"=>$contest_type));
                if (empty($new_user_data))
                {
                    $this->Auth_nosql_model->insert_nosql('private_contest_new_users',$new_user_signup_data);
                }
            }
        }

        $this->api_response_arry['data'] = array("user_name" => $random_user_name, "email" => $this->email, "phone_no" => $this->phone_no, "user_unique_id" => $this->user_unique_id, "is_referral_used" => $is_referral_used, "referral_code_used" => $referral_code_used,"image"=>$user_info['image']); 
        $this->api_response_arry["message"] = $message;
        $this->api_response();
    }

    /**
     * change password from settings
     */
    public function change_password_post() {
        $this->form_validation->set_rules('old_password', $this->lang->line('old_password'), 'trim|required|min_length[8]|max_length[50]');

        $this->form_validation->set_rules('password', $this->lang->line('password'), 'trim|required|min_length[8]|max_length[50]');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_data = $this->post();

        $old_password = $post_data['old_password'];
        $password = $post_data['password'];

        $this->load->model("Profile_model");
        $user_info = $this->Profile_model->get_single_row('phone_no,user_name,email,password', USER, array('user_id' => $this->user_id));
        $old_password = md5($old_password);
        if (!empty($user_info)) {
            $new_hash = md5($password);
            if ($old_password == $user_info['password']) {
                if ($new_hash == $user_info['password']) {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['error'] = array('password' => $this->lang->line('select_other_than_previous_password'));
                    $this->api_response();
                } else {
                    $this->db->where('user_id', $this->user_id);
                    $update = array('password' => $new_hash);
                    $this->db->update(USER, $update);
                    $link = BASE_APP_PATH;
                    $tmp = array();
                    $tmp["notification_type"] = 41;
                    $tmp["source_id"] = 0;
                    $tmp["notification_destination"] = 4; //  Web, Push, Email
                    $tmp["user_id"] = $this->user_id;
                    $tmp["to"] = $this->email;
                    $tmp["user_name"] = $this->user_name;
                    $tmp["added_date"] = format_date();
                    $tmp["modified_date"] = format_date();
                    $tmp["subject"] = $this->lang->line('reset_password_done');
                    $input = array(
                        'link' => $link
                    );
                    $tmp["content"] = json_encode($input);

                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($tmp);

                    $this->api_response_arry["message"] = $this->lang->line('reset_password_done');
                    $this->api_response();
                }
            } else {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['error'] = array('old_password' => $this->lang->line('enter_valid_password'));
                $this->api_response();
            }
        } else {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('not_a_valid_user');
            $this->api_response();
        }
    }

    /**
     * Used update Device ID in NOSQL
     * @param
     * @return true/false
     */
    public function update_device_id_post() {
        $this->form_validation->set_rules('device_id', 'Device ID', 'trim|required');
        $this->form_validation->set_rules('device_type', 'Device Type', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post = $this->post();
        $this->load->model("auth/Auth_nosql_model");

        $last_row = $this->Auth_nosql_model->select_one_nosql(ACTIVE_LOGIN, array('device_type' => $post['device_type'], 'user_id' => $this->user_id), '_id', 'desc');

        if (!empty($last_row)) {
            $_id = $this->Auth_nosql_model->get_object_id($last_row['_id']->{'$id'});
            $this->Auth_nosql_model->update_nosql(ACTIVE_LOGIN, array("_id" => $_id), array('device_id' => $post['device_id']));
        } else {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('no_record_found');
            $this->api_response();
        }
        $this->api_response_arry['global_error'] = $this->lang->line('device_id_updated');
        $this->api_response();
    }

    /**
     * used for get profile data
     * @param
     * @return json array
     */
    public function profile_detail_post() {
        $this->load->model("Profile_model");
        $user_profile  = $this->Profile_model->get_profile_detail();

        $user_profile['user_bank_detail'] = $this->Profile_model->get_single_row('first_name,last_name,bank_name,ac_number,ifsc_code,micr_code,bank_document,upi_id', USER_BANK_DETAIL, array('user_id' => $this->user_id));

        if (empty($user_profile['user_bank_detail'])) {
            $user_profile['user_bank_detail'] = array();
        }

        $aadhar_detail = $this->Profile_model->get_single_row('aadhar_id,name,aadhar_number,front_image,back_image,verify_by', USER_AADHAR, array('user_id' => $this->user_id));
        $user_profile['aadhar_detail'] = !empty($aadhar_detail) ? $aadhar_detail : array();

        $with_referral = $this->Profile_model->get_single_row('user_id', USER_AFFILIATE_HISTORY, array('friend_id' => $this->user_id,'affiliate_type' => 1));
        $user_profile['with_referral'] = 0;
        if (!empty($with_referral)) {
            $user_profile['with_referral'] = 1;
        }

        $user_profile['phone_code'] = isset($user_profile['phone_code']) ? $user_profile['phone_code'] : DEFAULT_PHONE_CODE;
        $user_profile['is_password_set'] = TRUE;
        if ($user_profile['password'] == NULL) {
            $user_profile['is_password_set'] = FALSE;
        }
        $user_profile['is_profile_complete'] = 0;
        if($user_profile['phone_verfied'] == 1 && $user_profile['email_verified'] == 1 && $user_profile['pan_verified'] == 1 && $user_profile['is_bank_verified'] == 1){
            $user_profile['is_profile_complete'] = 1;
        }

        $user_profile['user_setting'] = $this->get_user_setting();

        $user_profile['subscription'] = array();
        if($this->app_config['allow_subscription']['key_value'])
        {
            $this->load->model('Profile_model');
            $subscription = $this->Profile_model->get_subscription_details();
            if($subscription)
            {
                $user_profile['subscription'] = $subscription;
            }
        }
        unset($user_profile['password']);
        $this->api_response_arry['data'] = $user_profile;
        $this->api_response();
    }

    public function get_playing_experience_post()
    {
        //get playing experience details and cache it
        $playing_experience = $this->get_user_series_count($this->user_id);
        $this->api_response_arry['data'] = $playing_experience;
        $this->api_response();
    }

    private function get_user_series_count($user_id)
	{
        $this->load->model("fantasy/Fantasy_model");
        $series_counts = array();
        $contest_counts = $this->Fantasy_model->get_contest_won($user_id);
        $user_league_count = $this->Fantasy_model->get_user_league_count($user_id);
        
        $user_match_count = $this->Fantasy_model->get_user_match_count($user_id);
        //$series_counts = array_merge($series_counts,$contest_counts);

        $series_counts['league_counts'] = 0;
        if(!empty($user_league_count['league_count']))
        {
            $series_counts['league_counts'] = $user_league_count['league_count'];
        }
        
        $series_counts['match_counts'] = 0;
        if(!empty($user_match_count['match_count']))
        {
            $series_counts['match_counts'] = $user_match_count['match_count'];
        }
        
        $series_counts['won_contest'] = 0;
        if(!empty($contest_counts['won_contest']))
        {
            $series_counts['won_contest'] = $contest_counts['won_contest'];
        }
        
        $series_counts['total_contest'] = 0;
        if(!empty($contest_counts['total_contest']))
        {
            $series_counts['total_contest'] = $contest_counts['total_contest'];
        }

        return $series_counts;
	}
    /**
     * do_upload used to upload profile image or pan card
     * @param
     * @return json array
     */
    public function do_upload_post() {
        $post_data = $this->post();

        $file_field_name = 'userfile';

        if (!isset($_FILES[$file_field_name])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('file_not_found'));
            $this->api_response();
        }

        $dir = PROFILE_IMAGE_UPLOAD_PATH;
        $temp_file = $_FILES[$file_field_name]['tmp_name'];
        $ext = pathinfo($_FILES[$file_field_name]['name'], PATHINFO_EXTENSION);
        $vals = @getimagesize($temp_file);
        
        if (!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > 4194304) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_image_size'));
            $this->api_response();
        }

        $file_name = time() . "." . $ext;
        $allowed_ext = array('jpg', 'jpeg', 'png');
        if (!in_array(strtolower($ext), $allowed_ext)) {
            $error_msg = sprintf($this->lang->line('invalid_image_ext'), implode(', ', $allowed_ext));
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $error_msg);
            $this->api_response();
        }

        /* --Start amazon server upload code-- */
        if (strtolower(IMAGE_SERVER) == 'remote') {
            $config['allowed_types'] = 'jpg|png|jpeg|gif';
            $config['max_size'] = '204800'; //204800
            $config['upload_path'] = ROOT_PATH . $dir;
            $config['file_name'] = $file_name;

            $this->load->library('upload', $config);
            if (!$this->upload->do_upload($file_field_name)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = strip_tags($this->upload->display_errors());
                $this->api_response();
            } else {
                $uploaded_data = $this->upload->data();
                $thumb_path = PROFILE_IMAGE_PATH . $uploaded_data['file_name'];
                $config = array();
                $config['image_library'] = 'gd2';
                $config['source_image'] = ROOT_PATH . PROFILE_IMAGE_UPLOAD_PATH . $uploaded_data['file_name'];
                $config['new_image'] = ROOT_PATH . PROFILE_IMAGE_THUMB_UPLOAD_PATH;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 200;

                $this->load->library('image_lib', $config);

                $this->image_lib->resize();
                $this->api_response_arry['data'] = array('image_path' => $thumb_path, 'file_name' => $uploaded_data['file_name']);

                $thumb_filePath = PROFILE_IMAGE_THUMB_UPLOAD_PATH . $file_name;
                $thumb_source_path = ROOT_PATH . PROFILE_IMAGE_THUMB_UPLOAD_PATH . $file_name;
                $filePath = $dir . $file_name;
                try{
                    $data_arr = array();
                    $data_arr['file_path'] = $filePath;
                    $data_arr['source_path'] = $temp_file;
                    $this->load->library('Uploadfile');
                    $upload_lib = new Uploadfile();
                    $is_uploaded = $upload_lib->upload_file($data_arr);
                    if($is_uploaded){
                        $data_arr = array();
                        $data_arr['file_path'] = $thumb_filePath;
                        $data_arr['source_path'] = $thumb_source_path;
                        $thumb_upload = $upload_lib->upload_file($data_arr);
                        if($thumb_upload){
                            unlink($thumb_source_path);
                            unlink(ROOT_PATH . $filePath);
                            $image_path = PROFILE_IMAGE_THUMB_PATH . $file_name;
                            $return_array = array('image_path' => $image_path, 'file_name' => $file_name);
                            $this->api_response_arry['data'] = $return_array;
                        }
                    }

                }catch(Exception $e){
                    //$result = 'Caught exception: '.  $e->getMessage(). "\n";
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                    $this->api_response(); 
                }
            }
        } else {
            $config['allowed_types'] = 'jpg|png|jpeg|gif';
            $config['max_size'] = '4096'; //204800
            $config['upload_path'] = $dir;
            $config['file_name'] = $file_name;

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload($file_field_name)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = strip_tags($this->upload->display_errors());
                $this->api_response();
            } else {
                $uploaded_data = $this->upload->data();
                $thumb_path = PROFILE_IMAGE_PATH . $uploaded_data['file_name'];
                $this->api_response_arry['data'] = array('image_path' => $thumb_path, 'file_name' => $uploaded_data['file_name']);
            }
        }

        if (isset($post_data['update_image_record']) && $post_data['update_image_record'] == 1 && $this->user_id) {
            $this->load->model('Profile_model');
            $data = $this->api_response_arry['data'];
            $image = $data['image_path'];
            $this->Profile_model->update(USER, array("image" => $file_name), array('user_id' => $this->user_id));

            $user_sync_data = array();
            $user_sync_data['data'] = array(
                "Action" => "Signup",
                "UserID" => $this->user_id,
                "ProfilePicture" => $file_name
            );
            $this->load->helper('queue_helper');
            add_data_in_queue($user_sync_data, 'user_sync');

        }

        $this->api_response();
    }

    /**
     * used for Update Profile
     * @param
     * @return json array
     */
    public function update_profile_post() {
        $this->load->model("Profile_model");

        $vconfig = array(
            array(
                'field' => 'first_name',
                'label' => 'first name',
                'rules' => 'required|trim|min_length[2]|max_length[50]'
            ),
            array(
                'field' => 'last_name',
                'label' => 'last name',
                'rules' => 'trim|min_length[2]|max_length[50]',
            ),
            array(
                'field' => 'phone_no',
                'label' => 'Phone no',
                'rules' => 'trim|min_length[10]|max_length[10]|callback_is_digits|callback_check_number'
            ),
            array(
                'field' => 'phone_code',
                'label' => 'phone code',
                'rules' => 'trim|required|max_length[4]'
            ),
            array(
                'field' => 'email',
                'label' => 'email',
                'rules' => 'required|valid_email|max_length[100]|callback_check_email'
            ),
            array(
                'field' => 'dob',
                'label' => 'date of birth',
                'rules' => 'required|callback_eighteen_years_old'
            ),
            array(
                'field' => 'image',
                'label' => 'image',
                'rules' => 'trim'
            ),
            array(
                'field' => 'gender',
                'label' => 'Gender',
                'rules' => 'trim|callback_is_valid_gender'
            ),
            array(
                'field' => 'master_country_id',
                'label' => 'country',
                'rules' => 'trim|required|callback_country_exist'
            ),
            array(
                'field' => 'master_state_id',
                'label' => 'state',
                'rules' => 'trim|callback_state_exist'
            ),
            array(
                'field' => 'city',
                'label' => 'city',
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'zip_code',
                'label' => 'pin code',
                'rules' => 'trim|required'
            )
        );
        $this->form_validation->set_rules($vconfig);
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_values = array();
        $post_data = $this->post();


        $user_data = $this->Profile_model->get_single_row('user_unique_id,email,phone_no,user_id,user_name,email_verified', USER, array("user_id" => $this->user_id));

        $current_date = format_date();
        if ($user_data['email'] != $post_data['email'] && $user_data['email_verified'] == 0) {
            $new_email_key = $user_data['user_unique_id'] . '_' . strtotime($current_date);

            $post_values['new_email_requested'] = $current_date;
            $post_values['new_email_key'] = $new_email_key;
            $post_values['email'] = $post_data['email'];

            $notify_data = array();
            $notify_data['notification_type'] = 28;
            $notify_data['notification_destination'] = 4;
            $notify_data["source_id"] = 1;
            $notify_data["user_id"] = $user_data['user_id'];
            $notify_data["to"] = $post_data['email'];
            $notify_data["added_date"] = $current_date;
            $notify_data["modified_date"] = $current_date;
            $notify_data["user_name"] = $user_data['user_name'];
            $notify_data["subject"] = 'Confirm Your Existence, Human!';
            $content = array();
            $verification_link = BASE_APP_PATH . "?email_verify_key=" . base64_encode($new_email_key);

            $content = array('link' => $verification_link);

            $notify_data["content"] = json_encode($content);
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($notify_data);
        }

        $post_values['first_name'] = $post_data['first_name'];
        $post_values['last_name'] = $post_data['last_name'];
        $post_values['dob'] = date('Y-m-d', strtotime($post_data['dob']));
        $post_values['gender'] = isset($post_data['gender']) ? $post_data['gender'] : NULL;
        $post_values['master_country_id'] = $post_data['master_country_id'];
        $post_values['master_state_id'] = isset($post_data['master_state_id']) ? $post_data['master_state_id'] : NULL;
        $post_values['last_ip'] = get_user_ip_address();
        $post_values['modified_date'] = $current_date;
        $post_values['address'] = isset($post_data['address']) ? $post_data['address'] : '';
        $post_values['city'] = $post_data['city'];
        $post_values['zip_code'] = $post_data['zip_code'];


        if (!empty($post_data['image'])) {
            $post_values['image'] = $post_data['image'];
        }

        if (!empty($post_data['is_notification']) && in_array($post_data['is_notification'], array('0', '1'))) {
            $post_values['is_notification'] = $post_data['is_notification'];
        }

        if (!empty($user_data['email']) && !empty($post_data['email'])) {
            $post_values['email'] = strtolower($post_data['email']);
        }

        if ($post_data['phone_no'] != $user_data['phone_no']) {
            $post_values['phone_verfied'] = 0;
            $post_values['phone_no'] = $post_data['phone_no'];
            $post_values['phone_code'] = $post_data['phone_code'];
        }

        $this->Profile_model->update(USER, $post_values, array('user_id' => $this->user_id));
        
        $this->api_response_arry['message'] = $this->lang->line('profile_added_successfully');
        $this->api_response();
    }

    /**
     * used for Update Profile
     * @param
     * @return json array
     */
    public function update_state_city_post() {
        $this->load->model("Profile_model");

        $vconfig = array(
            array(
                'field' => 'master_country_id',
                'label' => 'country',
                'rules' => 'trim|required|callback_country_exist'
            ),
            array(
                'field' => 'master_state_id',
                'label' => 'state',
                'rules' => 'trim|callback_state_exist'
            ),
            array(
                'field' => 'city',
                'label' => 'city',
                'rules' => 'trim|required'
            )
        );
        $this->form_validation->set_rules($vconfig);
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_data = $this->post();
        $current_date = format_date();

        $post_values = array();
        $post_values['master_country_id'] = $post_data['master_country_id'];
        $post_values['master_state_id'] = $post_data['master_state_id'];
        $post_values['city'] = $post_data['city'];
        $this->Profile_model->update(USER, $post_values, array('user_id' => $this->user_id));
        
        $this->api_response_arry['message'] = $this->lang->line('profile_added_successfully');
        $this->api_response();
    }

    /**
     * used for get country data
     * @param
     * @return json array
     */
    public function get_country_list_post() {
        $county_cache_key = 'master_county';
        $county_list = $this->get_cache_data($county_cache_key);
        if (!$county_list) {
            $this->load->model('Profile_model');
            $county_list = $this->Profile_model->get_country_list();
            $this->set_cache_data($county_cache_key, $county_list, REDIS_30_DAYS);
        }

        //for upload app data on s3 bucket
        $this->push_s3_data_in_queue("county_list", $county_list);

        $this->api_response_arry['data'] = $county_list;
        $this->api_response();
    }

    /**
     * used for get state List
     * @param
     * @return json array
     */
    public function get_state_list_post() {
        $this->form_validation->set_rules('master_country_id', 'country id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->post();
        $master_country_id = $post_data['master_country_id'];
        $state_cache_key = 'state_list_' . $master_country_id;
        $state_list = $this->get_cache_data($state_cache_key);

        if (!$state_list) {
            $this->load->model('Profile_model');
            $state_list = $this->Profile_model->get_state_list_by_country_id($master_country_id);
            $this->set_cache_data($state_cache_key, $state_list, REDIS_30_DAYS);
        }

        if (!empty($state_list)) {
            //for upload app data on s3 bucket
            $this->push_s3_data_in_queue("state_list_".$master_country_id, $state_list);

            $this->api_response_arry['data'] = $state_list;
        } else {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('invalid_country_code');
        }
        $this->api_response();
    }

    /**
     * update_bank_ac_detail to update bank detail
     * @param
     * @return json array
     */
    public function update_bank_ac_detail_post() {

        // if auto_kyc is enabled, use "verify_pan_account"
        // if (AUTO_KYC_ENABLE == 1)
        // {
        //     $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        //     $this->api_response_arry['error'] = array();
        //     $this->api_response_arry['global_error'] = $this->lang->line('service_disabled');
        //     $this->api_response();
        // }

        $this->load->model("Profile_model");
        $this->form_validation->set_rules('first_name', $this->lang->line('first_name'), 'trim|required|max_length[100]|required');
        $this->form_validation->set_rules('last_name', $this->lang->line('last_name'), 'trim|max_length[100]');
        $this->form_validation->set_rules('bank_name', $this->lang->line('bank_name'), 'trim|max_length[100]|required');
        $this->form_validation->set_rules('ac_number', $this->lang->line('ac_number'), 'trim|numeric|max_length[50]|required');
        $this->form_validation->set_rules('upi_id', $this->lang->line('upi_id'), 'trim|max_length[50]');
        
        if(INT_VERSION != 1) {
            $this->form_validation->set_rules('ifsc_code', $this->lang->line('ifsc_code'), 'trim|alpha_numeric|max_length[100]|required');
        }
        
        
        $this->form_validation->set_rules('bank_document', $this->lang->line('bank_document'), 'trim|required');
        // $this->form_validation->set_rules('upi_id', $this->lang->line('upi_id'), 'trim|required');

        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();
        $user_detail = $this->Profile_model->get_single_row('is_bank_verified', USER, array('user_id' => $this->user_id));

        if (isset($user_detail['is_bank_verified']) && $user_detail['is_bank_verified'] == 1) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('bank_detail_change_error');
            $this->api_response();
        }

        $user_bank_detail = $this->Profile_model->get_single_row('user_id', USER_BANK_DETAIL, array('user_id' => $this->user_id));

        $bank_data = array();
        $today = format_date();
        $bank_data['first_name'] = $post_data['first_name'];
        $bank_data['last_name'] = isset($post_data['last_name']) ? $post_data['last_name'] : "";
        $bank_data['bank_name'] = $post_data['bank_name'];
        $bank_data['ac_number'] = $post_data['ac_number'];
        $bank_data['ifsc_code'] = isset($post_data['ifsc_code']) ? $post_data['ifsc_code'] : NULL;
        $bank_data['bank_document'] = $post_data['bank_document'];
        $bank_data['upi_id'] = isset($post_data['upi_id'])?$post_data['upi_id']:"";
        $bank_data['modified_date'] = $today;

        $user_data = array();

        $message = $this->lang->line('bank_detail_added_success');
        if ($user_bank_detail) {
            $this->Profile_model->update(USER_BANK_DETAIL, $bank_data, array('user_id' => $this->user_id));
            $this->Profile_model->update(USER, array('is_bank_verified' => 0), array('user_id' => $this->user_id));
        
            $message = $this->lang->line('bank_detail_update_success');
        } else {
            $bank_data['added_date'] = $today;
            $bank_data['user_id'] = $this->user_id;
            $this->db->insert(USER_BANK_DETAIL, $bank_data);

         
        }

        $this->api_response_arry['message'] = $message;
        $this->api_response();
    }

    public function delete_bank_details_post()
    {
        $this->load->model("Profile_model");
        if(isset($this->app_config['auto_withdrawal']['key_value']) && $this->app_config['auto_withdrawal']['key_value']==1 && strtolower($this->app_config['auto_withdrawal']['custom_data']['payout'])=="cashfree")
        {
            $cf_bene_id = $this->Profile_model->get_single_row('beneficiary_id', USER_BANK_DETAIL, array('user_id'=> $this->user_id));
             
            if(!isset($cf_bene_id['beneficiary_id']) && $cf_bene_id['beneficiary_id']== null)
            {
                $bene_id = $this->user_unique_id.$this->user_id;
            }else{
                $bene_id = $cf_bene_id['beneficiary_id'];
            }
                $this->load->library('Cashfree_payout');
                $CF = new Cashfree_payout($this->app_config['auto_withdrawal']['custom_data']);
                $get_beneficiary = $CF->get_bene($bene_id);
                if($get_beneficiary['subCode']==200 && $get_beneficiary['subCode']!=404)
                {
                    $remove_beneficiary = $CF->remove_bene($bene_id);
                    if($remove_beneficiary['subCode']==200 && strtolower($remove_beneficiary['message'])=="beneficiary removed")
                    {
                        $this->api_response_arry['message'] =  $this->lang->line('bank_detail_deleted');
                    }else{
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] =  "can not delete user from cashfree try after some time.";
                        $this->api_response();
                    }
                }
        }
        
        $this->Profile_model->delete_row(USER_BANK_DETAIL,array('user_id'=> $this->user_id));
        $this->Profile_model->update(USER,array('is_bank_verified'=> 0),array('user_id' => $this->user_id));
        $this->api_response_arry['message'] = $this->lang->line('bank_detail_deleted');
        $this->api_response();

    }

    /**
     *  Used to upload pan card
     * @param
     * @return json array
     */
    public function do_upload_pan_post() {
        $file_field_name = 'panfile';
        if (!isset($_FILES[$file_field_name])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('file_not_found'));
            $this->api_response();
        }
        $dir = PAN_IMAGE_UPLOAD_PATH;
        $temp_file = $_FILES[$file_field_name]['tmp_name'];
        $ext = pathinfo($_FILES[$file_field_name]['name'], PATHINFO_EXTENSION);
               
        if (!in_array(strtolower($ext), array('jpg', 'jpeg', 'png', 'pdf'))) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_ext'));
            $this->api_response();
        }

        if (!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > '4194304') {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_image_size'));
            $this->api_response();
        }

        $file_name = time() . "." . $ext;
        /* --Start amazon server upload code-- */
        if (strtolower(IMAGE_SERVER) == 'remote') {

            $filePath = $dir . $file_name;
            try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $image_path = PAN_IMAGE_PATH . $file_name;
                    $return_array = array('image_path' => $image_path, 'file_name' => $file_name);
                    $this->api_response_arry['data'] = $return_array;
                }

            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response(); 
            }
        } else {
            $config['allowed_types'] = 'jpg|png|jpeg|pdf';
            $config['max_size'] = '4096'; //KB
            $config['upload_path'] = $dir;
            $config['file_name'] = $file_name;

            $this->load->library('upload', $config);
            if (!$this->upload->do_upload($file_field_name)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = strip_tags($this->upload->display_errors());
                $this->api_response();
            } else {
                $uploaded_data = $this->upload->data();
                $thumb_path = PAN_IMAGE_PATH . $uploaded_data['file_name'];
                $this->api_response_arry['data'] = array('image_path' => $thumb_path, 'file_name' => $uploaded_data['file_name']);
            }
        }
        if ($this->user_id && $this->input->post("is_save")) {
            $data = $this->api_response_arry['data'];
            $image = $data['image_path'];
            $this->load->model("Profile_model");
            $this->Profile_model->update(USER, array("pan_image" => $image, 'pan_verified' => 0), array('user_id' => $this->user_id));
        }
        $this->api_response();
    }

    /**
     *  Used to upload bank documents
     * @param
     * @return json array
     */
    public function do_upload_bank_document_post() {
        $file_field_name = 'bank_document';
        if (!isset($_FILES[$file_field_name])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('file_not_found'));
            $this->api_response();
        }

        $dir = BANK_DOCUMENT_IMAGE_UPLOAD_PATH;
        $temp_file = $_FILES[$file_field_name]['tmp_name'];
        $ext = pathinfo($_FILES[$file_field_name]['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($ext), array('jpg', 'jpeg', 'png', 'pdf'))) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_ext'));
            $this->api_response();
        }

        if (!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > '4194304') {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_image_size'));
            $this->api_response();
        }

        $file_name = time() . "." . $ext;

        /* --Start amazon server upload code-- */
        if (strtolower(IMAGE_SERVER) == 'remote') {
            $filePath = $dir . $file_name;
            try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $image_path = BANK_DOCUMENT_PATH . $file_name;
                    $return_array = array('image_path' => $image_path, 'file_name' => $file_name);
                    $this->api_response_arry['data'] = $return_array;
                }

            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response(); 
            }
        } else {
            $config['allowed_types'] = 'jpg|png|jpeg|pdf';
            $config['max_size'] = '4096'; //KB
            $config['upload_path'] = $dir;
            $config['file_name'] = $file_name;

            $this->load->library('upload', $config);
            if (!$this->upload->do_upload($file_field_name)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = strip_tags($this->upload->display_errors());
                $this->api_response();
            } else {
                $uploaded_data = $this->upload->data();
                $thumb_path = BANK_DOCUMENT_PATH . $uploaded_data['file_name'];
                $this->api_response_arry['data'] = array('image_path' => $thumb_path, 'file_name' => $uploaded_data['file_name']);
            }
        }

        if ($this->user_id && $this->input->post("is_save")) {
            $data = $this->api_response_arry['data'];
            $image = $data['image_path'];
            $this->load->model("Profile_model");
            $this->Profile_model->update(USER_BANK_DETAIL, array("bank_document" => $image), array('user_id' => $this->user_id));
        }

        $this->api_response();
    }

    public function eighteen_years_old() {
        if($this->app_config['allow_age_limit']['key_value'] == 0){
            return TRUE;
        }
        $post_data = $this->post();
        if (!$post_data['dob']) {
            return TRUE;
        }
        $format = "M d, Y";
        $d = DateTime::createFromFormat($format, $post_data['dob']);
        if (!$d || $d->format($format) !== $post_data['dob']) {
            $this->form_validation->set_message('eighteen_years_old', 'Invalid date');
            return FALSE;
        }
        // $then will first be a string-date
        $then = strtotime($post_data['dob']);
        //The age to be over, over +18
        $min = strtotime('+18 years', $then);

        if (time() < $min) {
            $this->form_validation->set_message('eighteen_years_old', $this->lang->line("eighteen_years_old"));
            return FALSE;
        }
        return TRUE;
    }

    public function validate_for_unique_pan() {
        $post_data = $this->post();

        $user_data = $this->Profile_model->get_single_row('user_id,pan_no,pan_verified', USER, array("pan_no" => $post_data['pan_no'], "pan_verified" => 1));

        if (!$user_data || ($user_data["pan_no"] == $post_data['pan_no'] && $user_data["user_id"] == $this->user_id)) {
            return TRUE;
        }
        $this->form_validation->set_message('validate_for_unique_pan', $this->lang->line("pan_already_exists"));
        return FALSE;
    }

    public function is_valid_gender() {
        $post_data = $this->post();
        if ($post_data['gender'] && !in_array($post_data['gender'], array('male', 'female'))) {
            $this->form_validation->set_message('is_valid_gender', "Incorrect gender.");
            return FALSE;
        }
        return TRUE;
    }

    public function country_exist() {
        $post_data = $this->post();
        $county_cache_key = 'county_by_id_' . $post_data['master_country_id'];
        $country = $this->get_cache_data($county_cache_key);

        if (empty($country)) {
            $country = $this->Profile_model->get_single_row('*', MASTER_COUNTRY, array("master_country_id" => $post_data['master_country_id']));
            $this->set_cache_data($county_cache_key, $country, REDIS_30_DAYS);
        }
        if (empty($country)) {
            $this->form_validation->set_message('country_exist', "Incorrect Master Country Id");
            return FALSE;
        }
        return TRUE;
    }

    public function state_exist() {
        $post_data = $this->post();
        $state_cache_key = 'state_by_id_' . $post_data['master_state_id'] . '_' . $post_data['master_country_id'];
        $state = $this->get_cache_data($state_cache_key);

        if (empty($state)) {
            $state = $this->Profile_model->get_single_row('*', MASTER_STATE, array("master_country_id" => $post_data['master_country_id'], "master_state_id" => $post_data['master_state_id']));
            $this->set_cache_data($state_cache_key, $state, REDIS_30_DAYS);
        }
        if (empty($state)) {
            $this->form_validation->set_message('state_exist', "Incorrect Master State Id for given Master Country Id ");
            return FALSE;
        }
        return TRUE;
    }

    public function check_username() {
        $post_data = $this->post();
        $this->load->model("Profile_model"); 
        $user_data = $this->Profile_model->get_single_row('user_name,user_id', USER, array("user_name" => $post_data['user_name']));

        if (!$user_data || ($user_data["user_name"] == $post_data['user_name'] && $user_data["user_id"] == $this->user_id)) {
            return TRUE;
        }
        $this->form_validation->set_message('check_username', $this->lang->line("user_name_already_exists"));
        return FALSE;
    }

    public function check_username_post() {
        $post_data = $this->post();
        $this->load->model("Profile_model");
        $user_data = $this->Profile_model->get_single_row('user_name,user_id', USER, array("user_name" => $post_data['user_name'],'user_id<>' => $this->user_id));

        if(!empty($user_data))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = $this->lang->line("user_name_already_exists");
            $this->api_response(); 
        }

        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['error'] = array();
        $this->api_response_arry['message'] = $this->lang->line("username_available");
        $this->api_response(); 
    }

    public function update_username_post()
    {
        $this->form_validation->set_rules('user_name', $this->lang->line('username'), 'trim|required|callback_check_username');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $user_data = array();
        $user_data['user_name'] = $this->input->post('user_name');    
        $this->load->model("Profile_model");    
        $this->Profile_model->update_user($this->user_id, $user_data);

        //update username in mongo
        if(isset($user_data['user_name']) && $user_data['user_name'] != ""){
            $nosql_data = array("user_name"=>$user_data["user_name"]);
            $this->load->model("auth/Auth_nosql_model");
            $this->Auth_nosql_model->update_all_nosql(ACTIVE_LOGIN,array("user_id"=>$this->user_id),$nosql_data);

            $this->Auth_nosql_model->update_all_nosql(ACTIVE_LOGIN,array("user_id"=>(string)$this->user_id),$nosql_data);
        }

        $user_cache_key = "user_profile_" . $this->user_id;
        $this->delete_cache_data($user_cache_key);

        //update username to social also.
        $user_sync_data = array();
        $user_sync_data['data'] = array(
            "Action" => "username",
            "UserID" => $this->user_id,
            "FirstName" => $user_data['user_name']
        );
        $this->load->helper('queue_helper');
        add_data_in_queue($user_sync_data, 'user_sync');


        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['error'] = array();
        $this->api_response_arry['message'] = $this->lang->line("username_update_success");
        $this->api_response(); 
    }

    public function new_number_send_otp_post()
    {
        $this->form_validation->set_rules('phone_code', $this->lang->line('phone_code'), 'trim|required');
        $this->form_validation->set_rules('phone_no', $this->lang->line('phone_no'), 'trim|required|callback_check_phone_no_unique_for_update');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();

        $single_country = !empty($this->app_config['single_country'])?$this->app_config['single_country']['key_value']:0;
        $phone_code = !empty($this->app_config['phone_code'])?$this->app_config['phone_code']['key_value']:DEFAULT_PHONE_CODE;
        if($single_country == 1 && $post_data['phone_code'] != $phone_code){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("disable_country_signup_error");
            $this->api_response();
        }
        
        //send otp
        $this->send_otp_change_number($post_data);
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['error'] = array();
        $this->api_response_arry['message'] = $this->lang->line("otp_send_success");
        
        $this->api_response();     
    }

    public function check_phone_no_unique_for_update() {
        $post_data = $this->post();
        $this->load->model("Profile_model"); 
        $user_data = $this->Profile_model->get_single_row('phone_code,phone_no,user_id', USER, array('phone_no' => $post_data['phone_no'],
        'phone_code' => $post_data['phone_code'],
        'user_id<>'=> $this->user_id));

        if (empty($user_data)) {
            return TRUE;
        }
        $this->form_validation->set_message('check_phone_no_unique_for_update', $this->lang->line("phone_no_already_exists"));
        return FALSE;
    }

    public function send_otp_change_number($data) {
        $is_systemuser = 0;
        if (isset($data['is_systemuser']) && $data['is_systemuser'] == 1) {
            $is_systemuser = 1;
        }
        $where = $data;
        $phone_code = ($data['phone_code']) ? $data['phone_code'] : DEFAULT_PHONE_CODE;

        $this->load->model("auth/Auth_nosql_model");
        $otp = $this->Auth_nosql_model->send_otp($where, 0, $is_systemuser);
        $data['otp'] = $otp;
        if ($is_systemuser == 0) {
            $sms_data = array();
            $sms_data['otp'] = $otp;
            $sms_data['mobile'] = $data['phone_no'];
            $sms_data['phone_code'] = $phone_code;
            $sms_data['message'] = str_replace('#OTP#',$otp,$this->lang->line('number_update_otp_message'));
            $this->load->helper('queue_helper');
            add_data_in_queue($sms_data, 'sms');
        }

        return $otp;
    }

    function new_number_verify_and_update_post()
    {
        $this->form_validation->set_rules('phone_code',$this->lang->line('phone_code'), 'trim|required');
        $this->form_validation->set_rules('phone_no', $this->lang->line('phone_no'), 'trim|required|callback_check_phone_no_unique_for_update');
        $this->form_validation->set_rules('otp', $this->lang->line('otp'), 'trim|required|callback_check_phone_no_unique_for_update');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $this->load->model("Profile_model"); 
        $result = $this->Profile_model->check_otp_to_update_mobile();

        if(!empty($result['status']) && $result['status'] ==1) {            
            //check verify bonus credited or not and update
            if(!empty(LOGIN_FLOW)) {
                $user_ref= $this->Profile_model->get_single_row('friend_id,user_id',USER_AFFILIATE_HISTORY,array('affiliate_type in (1,19,20,21)' => null,'friend_id' => $this->user_id));
                $user_detail = array(
                    'email' => $this->email,
                    'user_id' => $this->user_id,
                    'user_name' => $this->user_name,
                    'phone_no' => $this->input->post('phone_no')
                );
                if(!empty($user_ref['user_id'])) {
                     //bonus ,real,coin update
                    $this->add_bonus($user_detail, $user_ref['user_id'], 4);
                } else {
                    //bonus ,real,coin update
                    $this->add_bonus($user_detail, 0, 8);
                }
            }
            
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['message'] = $result['message']; 
            $this->api_response_arry['data'] = $result['data']; 
        }
        else
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['message'] = $result['message']; 
        }

        $this->api_response();    
    }
   
   
    public function new_email_otp_send_post()
    {
        $this->form_validation->set_rules('email', $this->lang->line('email'), 'trim|required|callback_check_email_unique_for_update');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
            
        }

        $post_data = $this->input->post();    
        $post_data['email'] = strtolower($post_data['email']);
        $this->send_otp_change_email($post_data);
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['error'] = array();
        $this->api_response_arry['message'] = $this->lang->line("email_otp_send_success");
        $this->api_response();     
    }

    public function send_otp_change_email($data) {
        $is_systemuser = 0;
        if (isset($data['is_systemuser']) && $data['is_systemuser'] == 1) {
            $is_systemuser = 1;
        }
        $where = $data;
      
        $this->load->model("auth/Auth_nosql_model");
        
        $otp = $this->Auth_nosql_model->send_otp($where, 0, $is_systemuser);
        $data['otp'] = $otp;
        if ($is_systemuser == 0) {
             $content        = array('otp' => $otp, 'email' => $data['email']);
             $notify_data    = array();
             $notify_data['queue_name'] = "email_otp";
             $notify_data['notification_type']           = 133;
             $notify_data['notification_destination']    = 4;
             $notify_data["source_id"]   = 1;
             $notify_data["user_id"]     = $this->user_id;
             $notify_data["user_name"]   = $this->user_name;
             $notify_data["to"]          = $data['email'];
             $notify_data["added_date"]  = format_date();
             $notify_data["modified_date"] = format_date();
             $notify_data["subject"] = $this->lang->line('signup_email_subject');
             $notify_data["content"] = json_encode($content);
             $this->load->model("notification/Notify_nosql_model");
             $this->Notify_nosql_model->send_notification($notify_data);
            
        }

        return $otp;
    }

    public function check_email_unique_for_update() {
        $post_data = $this->post();
        $this->load->model("Profile_model"); 
        $user_data = $this->Profile_model->get_single_row('email,user_id', USER, array('email' => $post_data['email'],
        'user_id<>'=> $this->user_id));
        if (empty($user_data)) {
            return TRUE;
        }
        $this->form_validation->set_message('check_email_unique_for_update', $this->lang->line("email_already_exists_message"));
        return FALSE;
    }

    function new_email_verify_and_update_post() {
        $this->form_validation->set_rules('email', $this->lang->line('email'), 'trim|required|callback_check_email_unique_for_update');
        $this->form_validation->set_rules('otp', 'otp', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $this->load->model("Profile_model"); 
        $result = $this->Profile_model->check_otp_to_update_email();

        if(!empty($result['status']) && $result['status'] ==1) {
            if(empty(LOGIN_FLOW)) {
                $user_ref= $this->Profile_model->get_single_row('friend_id,user_id',USER_AFFILIATE_HISTORY,array('affiliate_type in (1,19,20,21)' => null,'friend_id' => $this->user_id));
                $user_detail = array(
                    'email' => $this->email,
                    'user_id' => $this->user_id,
                    'user_name' => $this->user_name,
                    'phone_no' => $this->phone_no
                );
                if(!empty($user_ref['user_id'])) {
                     //bonus ,real,coin update
                    $this->add_bonus($user_detail, $user_ref['user_id'], 13);
                    //$this->add_email_verify_bonus_with_referral($user_detail,$user_ref['user_id']);
                } else {
                    //bonus ,real,coin update
                    $this->add_bonus($user_detail, 0, 7);
                    //$this->add_email_verify_bonus_without_referral($user_detail);
                }
            }
            
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['message'] = $result['message']; 
            $this->api_response_arry['data'] = $result['data']; 
        }
        else
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['message'] = $result['message']; 
        }

        $this->api_response();    
    }

    /**
     * used for Update Profile
     * @param
     * @return json array
     */
    public function update_basic_info_post() {
        $this->load->model("Profile_model");

        $vconfig = array(
            array(
                'field' => 'first_name',
                'label' => $this->lang->line('first_name'),
                'rules' => 'required|trim|min_length[2]|max_length[50]'
            ),
            array(
                'field' => 'last_name',
                'label' => $this->lang->line('last_name'),
                'rules' => 'trim|min_length[2]|max_length[50]',
            ),
            array(
                'field' => 'dob',
                'label' => $this->lang->line('dob'),
                'rules' => 'required|callback_eighteen_years_old'
            ),
            array(
                'field' => 'master_country_id',
                'label' => $this->lang->line('country'),
                'rules' => 'trim|required|callback_country_exist'
            ),
            array(
                'field' => 'master_state_id',
                'label' => $this->lang->line('state'),
                'rules' => 'trim|callback_state_exist'
            ),
        );
        $this->form_validation->set_rules($vconfig);
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_values = array();
        $post_data = $this->post();


        $user_data = $this->Profile_model->get_single_row('first_name,last_name,dob,user_unique_id,user_id,user_name,pan_verified', USER, array("user_id" => $this->user_id));

        //if pancard varified then first name ,last name and ,dob can not be update    
        if(!empty($user_data['pan_verified']) && $user_data['pan_verified'] == '1')
        {
            if($user_data['first_name'] !=trim($post_data['first_name'])
         )
            {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['error'] = array();
                $this->api_response_arry['global_error'] = $this->lang->line('err_fname_update_post_pan_verify'); 
                $this->api_response();   
            }

            // if( $user_data['last_name'] !=trim($post_data['last_name']))
            // {
            //     $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            //     $this->api_response_arry['error'] = array();
            //     $this->api_response_arry['global_error'] = $this->lang->line('err_lname_update_post_pan_verify'); 
            //     $this->api_response();   
            // }

            if(strtotime($user_data['dob']) != strtotime($post_data['dob']))
            {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['error'] = array();
                $this->api_response_arry['global_error'] = $this->lang->line('err_dob_update_post_pan_verify'); 
                $this->api_response();   
            }
        }


        $current_date = format_date();
       
        $post_values['first_name'] = trim($post_data['first_name']);
        $post_values['last_name'] = isset($post_data['last_name'])?$post_data['last_name']:'';
        $post_values['dob'] = date('Y-m-d', strtotime($post_data['dob']));
        $post_values['gender'] = isset($post_data['gender']) ? $post_data['gender'] : '';
        $post_values['master_country_id'] = $post_data['master_country_id'];
        $post_values['master_state_id'] = isset($post_data['master_state_id']) ? $post_data['master_state_id'] : NULL;
        $post_values['last_ip'] = get_user_ip_address();
        $post_values['modified_date'] = $current_date;
        $post_values['address'] = isset($post_data['address']) ? $post_data['address'] : '';
        $post_values['city'] = isset($post_data['city']) ? $post_data['city'] : '';
        $post_values['zip_code'] = isset($post_data['zip_code']) ? $post_data['zip_code'] : '';

        $this->Profile_model->update(USER, $post_values, array('user_id' => $this->user_id));
        
        // add user data in feed DB
        $user_sync_data = array();
        $user_sync_data['data'] = array(
            "Action" => "Signup",
            "UserID" => $this->user_id,
            "UserGUID" => $user_data['user_unique_id'],
            "Gender" => $post_values['gender'],
            "FirstName" => $post_values['first_name'],
            "LastName" => $post_values['last_name'],
            "DOB" => $post_values['dob']
        );
        $this->load->helper('queue_helper');
        add_data_in_queue($user_sync_data, 'user_sync');

        $this->api_response_arry['message'] = $this->lang->line('profile_added_successfully');
        $this->api_response();
    }

    public function update_pan_info_post()
    {
        // if auto_kyc is enabled, use "verify_pan_info"
        // if (AUTO_KYC_ENABLE == 1)
        // {
        //     $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        //     $this->api_response_arry['error'] = array();
        //     $this->api_response_arry['global_error'] = $this->lang->line('service_disabled');
        //     $this->api_response();
        // }

        $this->load->model("Profile_model");

        $vconfig = array(
            array(
                'field' => 'first_name',
                'label' => $this->lang->line('first_name'),
                'rules' => 'required|trim|min_length[2]|max_length[50]'
            ),
            array(
                'field' => 'last_name',
                'label' => $this->lang->line('last_name'),
                'rules' => 'trim|min_length[2]|max_length[50]',
            ),
            array(
                'field' => 'dob',
                'label' => $this->lang->line('dob'),
                'rules' => 'required|callback_eighteen_years_old'
            ),
            array(
                'field' => 'pan_no',
                'label' =>  $this->lang->line('pan_no'),
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'pan_image',
                'label' => $this->lang->line('pan_image'),
                'rules' => 'trim|required'
            )
        );
        $this->form_validation->set_rules($vconfig);
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_values = array();
        $post_data = $this->post();

        $check_exist = $this->Profile_model->get_single_row('user_id', USER, array("pan_no" => trim($post_data['pan_no']),"pan_verified"=>"1"));
        if(!empty($check_exist)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = $this->lang->line('duplicate_pan_no'); 
            $this->api_response();
        }

        $user_data = $this->Profile_model->get_single_row('first_name,last_name,dob,user_unique_id,user_id,user_name,pan_verified,pan_no', USER, array("user_id" => $this->user_id));
        //if pancard varified then first name ,last name and ,dob can not be update    
        if(!empty($user_data['pan_verified']) && $user_data['pan_verified'] == '1')
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = $this->lang->line('err_update_post_pan_verify'); 
            $this->api_response();   
        }

        $current_date = format_date();
       
        $post_values['first_name'] = $post_data['first_name'];
        $post_values['last_name'] = $post_data['last_name'];
        $post_values['pan_no'] = $post_data['pan_no'];
        $post_values['pan_verified'] = 0;
        $post_values['pan_image'] = $post_data['pan_image'];
        $post_values['dob'] = date('Y-m-d', strtotime($post_data['dob']));
      
        $post_values['last_ip'] = get_user_ip_address();
        $post_values['modified_date'] = $current_date;
        $this->Profile_model->update(USER, $post_values, array('user_id' => $this->user_id));
        
        // add user data in feed DB
        $user_sync_data = array();
        $user_sync_data['data'] = array(
            "Action" => "Signup",
            "UserID" => $this->user_id,
            "UserGUID" => $user_data['user_unique_id'],
            "FirstName" => $post_values['first_name'],
            "LastName" => $post_values['last_name'],
            "DOB" => $post_values['dob']
        );
        $this->load->helper('queue_helper');
        add_data_in_queue($user_sync_data, 'user_sync');

        $this->api_response_arry['message'] = $this->lang->line('pan_info_updated');
        $this->api_response();


    }


    /**
     * Used for check user phone number exist or not in db
     * @return boolean
     */
    public function check_number() {
        $this->load->model("auth/Auth_model");
        $post_data = $this->post();
        $user_data = $this->Auth_model->get_single_row('phone_no,user_id,phone_code', USER, array("phone_no" => $post_data['phone_no'], "phone_code" => $post_data['phone_code']));

        if (!$user_data || ($user_data["phone_no"] == $post_data['phone_no'] && $user_data["phone_code"] == $post_data['phone_code'] && $user_data["user_id"] == $this->user_id)) {
            return TRUE;
        }
        $this->form_validation->set_message('check_number', $this->lang->line("phone_no_already_exists"));
        return FALSE;
    }
    
    /**
     * Used to update referral code
     * @param
     * @return true/false
     */
    public function update_referral_code_post() {
        $this->form_validation->set_rules('referral_code', 'referral code', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post = $this->post();
        $this->load->model("Profile_model");
        $referral_code = $post['referral_code'];
        $can_edit = $this->Profile_model->is_referral_code_edit();        
        if($can_edit) {
            $this->load->model("auth/Auth_model"); 
            $is_exist = FALSE;
            if($this->referral_code != $referral_code) {
                $is_exist = $this->Auth_model->_referral_code_exists($referral_code);            
            }
            if(!$is_exist) {                
                $this->Profile_model->update_referral_code($referral_code);

                $nosql_data['referral_code'] = $referral_code;
                $this->load->model("auth/Auth_nosql_model");
                $this->Auth_nosql_model->update_nosql(ACTIVE_LOGIN,array("user_id"=>$this->user_id),$nosql_data);
                
                
                $user_detail = array(
                    'email' => $this->email,
                    'user_id' => $this->user_id,
                    'user_name' => $this->user_name,
                    'phone_no' => $this->phone_no
                );
                if($this->referral_code != $referral_code) {
                    //bonus ,real,coin update
                    $this->add_bonus($user_detail, 0, 18);
                }
                
            } else {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('referral_code_exist');                
            }
        }
        else {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('already_edited');                
        }
        $this->api_response();
    }

    //affiliate module methods 

    /**
     * method to request to become an affiliate 
     *@param user_id
     */
public function become_affiliate_post(){
    $this->load->model("Profile_model");
     $post = $this->post();   

    // $result = $this->Profile_model->become_affiliate($this->user_id);
    $result = $this->Profile_model->become_affiliate($post);
        if($result){
            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response_arry['status']          = TRUE;
            $this->api_response_arry['message']         = $this->lang->line('aff_req_success');
        }
        else{
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
        $this->api_response_arry['global_error']        = $this->lang->line('aff_req_error');
        }
        $this->api_response();
}

/**
     * method to summary of affiliate like % commission , total signup, total commission etc 
     *@param user_id
     *@return array
     */
public function get_affiliate_summary_post(){
    $this->load->model("Profile_model");
    $result = $this->Profile_model->get_affiliate_summary($this->user_id);
        if($result){
            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response_arry['status']          = TRUE;
            $this->api_response_arry['data']            = $result;
            $this->api_response_arry['message']         = $this->lang->line('aff_summary_success');
        }
        else{
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['data']            = array();
            $this->api_response_arry['global_error']    = $this->lang->line('aff_summary_error');
        }
        $this->api_response();
}

/**
     * method to transaction of affiliate signup and deposited by other users affiliated to this user. 
     *@param user_id
     *@return array
     */
public function get_affiliate_transactions_post(){
    $this->load->model("Profile_model");
    $result = $this->Profile_model->get_affiliate_transactions($this->user_id);

    if(!empty($result)){
        $transaction_messages = $this->get_transaction_msg();
        $user_ids = array_column($result['result'],'friend_id');
        $user_list = $this->Profile_model->get_users_by_ids($user_ids);
        if(!empty($user_list)){
            $user_list = array_column($user_list,NULL,'user_id');
        }
        
        foreach($result['result'] as $key=>&$value){

            if(isset($transaction_messages[$value["source"]]))
            {
                $value['friend_name'] = $value['friend_amount'] = $value['friend_order_id'] = '';
                $custom_data = json_decode($value['custom_data'],TRUE);
                if(isset($custom_data['amount'])){
                    $value['friend_amount'] = $custom_data['amount'];
                }
                if(isset($custom_data['order_id'])){
                    $value['friend_order_id'] = $custom_data['order_id'];
                }
                if(isset($user_list[$custom_data['user_id']])){
                    $value['friend_name'] = $user_list[$custom_data['user_id']]['user_name'];
                }
                switch($value['source']){
                    case 320:
                        $amount = CURRENCY_CODE." ".$value['signup_commission'];
                        $message = $transaction_messages[$value["source"]][$this->lang_abbr.'_message'];
                        $result['result'][$key]['trans_desc'] = str_replace("{{amount}}",$amount,$message);	
                    break;
                    case 321 :
                        $amount = (($value['deposit_comission']*100)/$value['friend_amount'])."% of ".CURRENCY_CODE." ".$value['friend_amount'];
                        $message = $transaction_messages[$value["source"]][$this->lang_abbr.'_message'];
                        $result['result'][$key]['trans_desc'] = str_replace("{{amount}}",$amount,$message);	
                        break;
                        case 556 :
                     
                        $result['result'][$key]['match'] =  $custom_data['match']; 	
                        $result['result'][$key]['league_name'] =  $custom_data['league_name']; 	
                        break; 
                    }
                    unset($value['custom_data']);
            }
        }

        $this->api_response_arry['data']            = $result;
        $this->api_response_arry['message']         = $this->lang->line('aff_tr_success');
    }
    else{
        $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['status']          = FALSE;
        $this->api_response_arry['data']            = array();
        $this->api_response_arry['global_error']    = $this->lang->line('aff_tr_error');
    }
    $this->api_response();
}

private function get_transaction_msg()
{
    $cache_key = "transaction_msg_list";
    $transaction_by_source= array();
    $transaction_by_source = $this->get_cache_data($cache_key);
    if(empty($transaction_by_source))
    {
        $this->load->model('auth/Auth_nosql_model');
        $transaction_msgs =  $this->Auth_nosql_model->select_nosql(COLL_TRANSACTION_MESSAGES);
        if(!empty($transaction_msgs))
        {
            $transaction_by_source = array_column($transaction_msgs,NULL,'source');
        }
        $this->set_cache_data($cache_key,$transaction_by_source, REDIS_30_DAYS);
    }
   
    return $transaction_by_source;
   
}

    /**
     * @method verify_pan_info
     * @uses method to verify user's PAN card details by 3rd party identity verification service provider
     * @param Array $_POST it contains first_name,last_name,pan_no
     * @since Aug 2020
     * */
    public function verify_pan_info_post($value='')
    {
        // if auto_kyc is disabled, use "update_pan_info"
        $this->load->model("Profile_model");

        $vconfig = array(
            array(
                'field' => 'first_name',
                'label' => $this->lang->line('first_name'),
                'rules' => 'required|trim|min_length[2]|max_length[50]'
            ),
            array(
                'field' => 'last_name',
                'label' => $this->lang->line('last_name'),
                'rules' => 'required|trim|min_length[2]|max_length[50]',
            ),
            array(
                'field' => 'pan_no',
                'label' =>  $this->lang->line('pan_no'),
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'dob',
                'label' => $this->lang->line('dob'),
                'rules' => 'required|callback_eighteen_years_old'
            )
        );
        $this->form_validation->set_rules($vconfig);
        if ($this->form_validation->run() == FALSE)
        {
            $this->send_validation_errors();
        }
        $post_data = $this->post();
        // echo "<pre>";print_r($post_data);die;

        $check_exist = $this->Profile_model->get_single_row('user_id', USER, array("pan_no" => trim($post_data['pan_no']),"pan_verified"=>"1"));
        if(!empty($check_exist))
        {
            $this->api_response_arry['data']['auto_pan_attempted'] = 0;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = $this->lang->line('duplicate_pan_no');
            $this->api_response();
        }

        $user_data = $this->Profile_model->get_single_row('first_name,last_name,dob,user_unique_id,user_id,user_name,pan_verified,pan_no,email,phone_no,auto_pan_attempted', USER, array("user_id" => $this->user_id));
        // if pancard varified then first name ,last name and ,dob can not be update
        if(!empty($user_data['pan_verified']) && $user_data['pan_verified'] == '1')
        {
            $this->api_response_arry['data']['auto_pan_attempted'] = 0;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = $this->lang->line('err_update_post_pan_verify');
            $this->api_response();
        }

        if(!empty($user_data['first_name']) && !empty($user_data['last_name']))
        {
            if (strtoupper($user_data['first_name']) !== strtoupper(trim($post_data['first_name'])) || strtoupper($user_data['last_name']) !== strtoupper(trim($post_data['last_name'])))
            {
                $this->api_response_arry['data']['auto_pan_attempted'] = 0;
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['error'] = array();
                $this->api_response_arry['global_error'] = $this->lang->line('profile_name_mismatch');
                $this->api_response();
            }
        }

        $reqData = array();
        $custom_data = array();
        $reqData['data'] = array("customer_pan_number" => $post_data['pan_no'], "consent" => "Y", "consent_text" => "I would like to verify my PAN number");
        $reqData['task_id'] = $this->user_id . '_' . $post_data['pan_no'];
        $reqData = json_encode($reqData);
        $custom_data['mode'] =  $this->app_config['auto_kyc']['custom_data']['mode'];
        $custom_data['kyc_id'] = $this->app_config['auto_kyc']['custom_data']['kyc_id'];
        $custom_data['kyc_key'] = $this->app_config['auto_kyc']['custom_data']['kyc_key'];
        $custom_data['type'] = 1;
        $validation_data = validate_kyc_info($reqData, $custom_data);
        // $validation_data = validate_kyc_info($reqData, $kyc_id, $kyc_key,$mode);
        if (!empty($validation_data))
        {
            if (isset($validation_data['response_code']) && $validation_data['response_code'] == "100")
            {
                if ($validation_data['result']['pan_status'] == "VALID")
                {
                    if (isset($validation_data['result']['user_full_name']))
                    {
                        $name_string = explode(" ", $validation_data['result']['user_full_name']);
                        if (isset($name_string) && $name_string != '')
                        {
                            $f_name = isset($name_string[0]) ? $name_string[0] : '';
                            $m_name = isset($name_string[1]) ? $name_string[1] : '';
                            $l_name = isset($name_string[2]) ? $name_string[2] : '';
                            if ($l_name == '')
                            {
                                $m_name = '';
                                $l_name = isset($name_string[1]) ? $name_string[1] : '';
                            }
                        }

                        if (strtoupper($f_name) == strtoupper($post_data['first_name']) && strtoupper($l_name) == strtoupper($post_data['last_name']))
                        {
                            $post_values = array();
                            $current_date                   = format_date();
                            $post_values['first_name']      = trim($post_data['first_name']);
                            $post_values['last_name']       = trim($post_data['last_name']);
                            $post_values['pan_no']          = $post_data['pan_no'];
                            $post_values['pan_verified']    = 1;
                            $post_values['last_ip']         = $this->input->ip_address();
                            $post_values['modified_date']   = $current_date;
                            $post_values['dob']             = date('Y-m-d', strtotime($post_data['dob']));
                            $post_values['auto_pan_attempted']             = $user_data['auto_pan_attempted']+1;

                            $this->Profile_model->update(USER, $post_values, array('user_id' => $this->user_id));
                            // $this->Profile_model->update(USER, array('auto_pan_attempted' => 1), array('user_id' => $this->user_id));

                            $check_affililate_history = $this->Profile_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$user_data['user_id'],"status" => 1,"affiliate_type in (1,19,20,21)" => null,"user_id != " => "0"));

                            if(empty($check_affililate_history))
                            {
                                //[NRS - PAN verification to user w/o referral]
                                $this->pan_verification_bonus_for_non_referral_users($user_data);
                            }
                            else
                            {
                                //[NRS - PAN verification to user with referral]
                                $this->pan_verification_bonus_for_referral_users($user_data);
                            }

                            //delete user profile infor from cache
                            $user_cache_key = "user_profile_" . $this->user_id;
                            $this->delete_cache_data($user_cache_key);

                            $user_balance_cache_key = 'user_balance_' . $this->user_id;
                            $this->delete_cache_data($user_balance_cache_key);
                            // $this->delete_user_sessions($user_data['user_id']);

                            $this->api_response_arry['data']['auto_pan_attempted'] =  $user_data['auto_pan_attempted']+1;
                            $this->api_response_arry['message'] = $this->lang->line('pan_info_updated');
                            $this->api_response();
                        }
                        else
                        {
                            //delete user profile infor from cache
                            $user_cache_key = "user_profile_" . $this->user_id;
                            $this->delete_cache_data($user_cache_key);

                            $this->Profile_model->update(USER, array('auto_pan_attempted' => $user_data['auto_pan_attempted']+1), array('user_id' => $this->user_id));

                            $this->api_response_arry['data']['auto_pan_attempted'] = $user_data['auto_pan_attempted']+1;
                            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                            $this->api_response_arry['error'] = array();
                            $this->api_response_arry['global_error'] = $this->lang->line('invalid_pan_details');
                            $this->api_response();
                        }
                    }
                    else
                    {
                         //delete user profile infor from cache
                            $user_cache_key = "user_profile_" . $this->user_id;
                            $this->delete_cache_data($user_cache_key);
                        $this->Profile_model->update(USER, array('auto_pan_attempted' => $user_data['auto_pan_attempted']+1), array('user_id' => $this->user_id));

                        $this->api_response_arry['data']['auto_pan_attempted'] = $user_data['auto_pan_attempted']+1;
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['error'] = array();
                        $this->api_response_arry['global_error'] = $this->lang->line('invalid_pan_details');
                        $this->api_response();
                    }
                }
                else
                {
                     //delete user profile infor from cache
                    $user_cache_key = "user_profile_" . $this->user_id;
                    $this->delete_cache_data($user_cache_key);
                    $this->Profile_model->update(USER, array('auto_pan_attempted' => $user_data['auto_pan_attempted']+1), array('user_id' => $this->user_id));

                    $this->api_response_arry['data']['auto_pan_attempted'] = $user_data['auto_pan_attempted']+1;
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['error'] = array();
                    $this->api_response_arry['global_error'] = isset($validation_data['response_msg'])?$validation_data['response_msg']:$this->lang->line('invalid_pan_no');
                    $this->api_response();
                }
            }
            else
            {
                 //delete user profile infor from cache
                $user_cache_key = "user_profile_" . $this->user_id;
                $this->delete_cache_data($user_cache_key);
                $this->Profile_model->update(USER, array('auto_pan_attempted' => $user_data['auto_pan_attempted']+1), array('user_id' => $this->user_id));

                $this->api_response_arry['data']['auto_pan_attempted'] = $user_data['auto_pan_attempted']+1;
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['error'] = array();
                $this->api_response_arry['global_error'] = isset($validation_data['response_msg'])?$validation_data['response_msg']:$this->lang->line('invalid_pan_no');
                $this->api_response();
            }
        }
        else
        {
            //delete user profile infor from cache
            $user_cache_key = "user_profile_" . $this->user_id;
            $this->delete_cache_data($user_cache_key);
  
            $this->Profile_model->update(USER, array('auto_pan_attempted' => $user_data['auto_pan_attempted']+1), array('user_id' => $this->user_id));

            $this->api_response_arry['data']['auto_pan_attempted'] = $user_data['auto_pan_attempted']+1;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = $this->lang->line('invalid_pan_no');
            $this->api_response();
        }
    }
    /**
     * method to add crypto withdrawal account detail
     * 
     */
    public function verify_crypto_wallet_post()
    {
        $this->load->model("Profile_model");
        // $this->form_validation->set_rules('full_name', $this->lang->line('full_name'), 'trim|required|max_length[100]|required');
        $this->form_validation->set_rules('bank_name', $this->lang->line('bank_name'), 'trim|max_length[100]|required');
        $this->form_validation->set_rules('upi_id', $this->lang->line('crypto'), 'trim|required|max_length[200]');

        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();
        if(isset($post_data['full_name']))
        {
        $name_string = explode(" ", $post_data['full_name']);
                        if (isset($name_string) && $name_string != '')
                        {
                            $post_data['first_name'] = isset($name_string[0]) ? $name_string[0] : '';
                            $post_data['middle_name'] = isset($name_string[1]) ? $name_string[1] : '';
                            $post_data['last_name'] = isset($name_string[2]) ? $name_string[2] : '';
                            if ($post_data['last_name'] == '')
                            {
                                $post_data['middle_name'] = '';
                                $post_data['last_name'] = isset($name_string[1]) ? $name_string[1] : '';
                            }else{
                                $post_data['last_name'] = $post_data['middle_name'].' '.$post_data['last_name'];
                            }
                        }
        }

        $user_bank_verified = $this->Profile_model->get_single_row('is_bank_verified', USER, array('user_id' => $this->user_id));

        if (isset($user_bank_verified['is_bank_verified']) && $user_bank_verified['is_bank_verified'] == 1)
        {
            $this->api_response_arry['data']['auto_bank_attempted'] = 0;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('crypto_wallet_change_error');
            $this->api_response();
        }

        $crypto_wallet_details = array("upi_id" => trim($post_data['upi_id']));
        $is_duplicate = $this->Profile_model->check_duplicate_crypto_wallet($crypto_wallet_details);
        
        $post_values = array();
        $current_date                       = format_date();
        $post_values['first_name']          = $post_data['first_name'] ? trim($post_data['first_name']): null;
        $post_values['last_name']           = $post_data['last_name'] ? trim($post_data['last_name']): null;
        $post_values['modified_date']       = $current_date;
        $post_values['added_date']          = $current_date;
        $post_values['bank_name']           = trim($post_data['bank_name']);
        $post_values['upi_id']              = trim($post_data['upi_id']);
        $post_values['user_id']             = $this->user_id;
        $post_values['type']                = 2;

        $crypto_id = $this->Profile_model->save_crypto_wallet($post_values,$is_duplicate ? 1:0);
        if($crypto_id)
        {
            unset(
                $post_values['added_date'],
                $post_values['bank_name'],
                $post_values['upi_id'],
                $post_values['user_id'],
                $post_values['type']
            );
            if($user_bank_verified['is_bank_verified']==2)
            {
                $post_values['is_bank_verified']    = 0;
            }
            $post_values['last_ip']             = $this->input->ip_address();
            $this->Profile_model->update(USER, $post_values, array('user_id' => $this->user_id));
        }

         //delete user profile infor from cache
         $user_cache_key = "user_profile_" . $this->user_id;
         $this->delete_cache_data($user_cache_key);

         $user_balance_cache_key = 'user_balance_' . $this->user_id;
         $this->delete_cache_data($user_balance_cache_key);
         $this->api_response_arry['message'] = $this->lang->line('crypto_wallet_added_success');
         $this->api_response();
    }

    /**
     * @method verify_bank_account
     * @uses method to verify user's BANK details by 3rd party identity verification service provider
     * @param Array $_POST it contains first_name,last_name,bank_name,ac_number,ifsc_code
     * @since Aug 2020
     * */
    public function verify_bank_account_post($value='')
    {
        // if auto_kyc is disabled, use "update_bank_ac_detail"
        // if (AUTO_KYC_ENABLE != 1)
        // {
        //     $this->api_response_arry['data']['auto_bank_attempted'] = 0;
        //     $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        //     $this->api_response_arry['error'] = array();
        //     $this->api_response_arry['global_error'] = $this->lang->line('service_disabled');
        //     $this->api_response();
        // }

        $this->load->model("Profile_model");
        $this->form_validation->set_rules('first_name', $this->lang->line('first_name'), 'trim|required|max_length[100]|required');
        $this->form_validation->set_rules('last_name', $this->lang->line('last_name'), 'trim|required|max_length[100]');
        $this->form_validation->set_rules('bank_name', $this->lang->line('bank_name'), 'trim|max_length[100]|required');
        $this->form_validation->set_rules('ac_number', $this->lang->line('ac_number'), 'trim|numeric|max_length[50]|required');
        $this->form_validation->set_rules('ifsc_code', $this->lang->line('ifsc_code'), 'trim|alpha_numeric|max_length[100]|required');
        $this->form_validation->set_rules('upi_id', $this->lang->line('upi_id'), 'trim|max_length[50]');

        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();
        $user_bank_verified = $this->Profile_model->get_single_row('is_bank_verified', USER, array('user_id' => $this->user_id));

        if (isset($user_bank_verified['is_bank_verified']) && $user_bank_verified['is_bank_verified'] == 1)
        {
            $this->api_response_arry['data']['auto_bank_attempted'] = 0;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('bank_detail_change_error');
            $this->api_response();
        }

        $user_data = $this->Profile_model->get_single_row('first_name,last_name,auto_bank_attempted', USER, array("user_id" => $this->user_id));

        if(!empty($user_data['first_name']) && !empty($user_data['last_name']))
        {
            if (strtoupper($user_data['first_name']) !== strtoupper(trim($post_data['first_name'])) || strtoupper($user_data['last_name']) !== strtoupper(trim($post_data['last_name'])))
            {
                $this->api_response_arry['data']['auto_bank_attempted'] = 0;
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['error'] = array();
                $this->api_response_arry['global_error'] = $this->lang->line('profile_name_mismatch');
                $this->api_response();
            }
        }

        $user_bank_details = array("ac_number" => trim($post_data['ac_number']), "ifsc_code" => trim($post_data['ifsc_code']));
        $is_duplicate = $this->Profile_model->check_duplicate_account($user_bank_details);
        if ($is_duplicate)
        {
            $this->api_response_arry['data']['auto_bank_attempted'] = 0;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = $this->lang->line('duplicate_bank_details');
            $this->api_response();
        }
        $check_bank_state = $this->check_bank_state($post_data['ifsc_code']);
        if($check_bank_state && $check_bank_state === "BANNED")
        {
             //delete user profile infor from cache
            $user_cache_key = "user_profile_" . $this->user_id;            
            $this->delete_cache_data($user_cache_key);
            $this->Profile_model->update(USER, array('auto_bank_attempted' => $user_data['auto_bank_attempted']+1), array('user_id' => $this->user_id));



            $this->api_response_arry['data']['auto_bank_attempted'] = $user_data['auto_bank_attempted']+1;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('fantasy_not_allowed_in_state');
            $this->api_response_arry['global_error'] = $this->lang->line('fantasy_not_allowed_in_state');
            $this->api_response();
        }
        if($check_bank_state && $check_bank_state === "INVALID")
        {
            $user_cache_key = "user_profile_" . $this->user_id;            
            $this->delete_cache_data($user_cache_key);
            $this->Profile_model->update(USER, array('auto_bank_attempted' => $user_data['auto_bank_attempted']+1), array('user_id' => $this->user_id));

            $this->api_response_arry['data']['auto_bank_attempted'] = $user_data['auto_bank_attempted']+1;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('invalid_ifsc_code');
            $this->api_response_arry['global_error'] = $this->lang->line('invalid_ifsc_code');
            $this->api_response();
        }
        if($check_bank_state && $check_bank_state === "FAILED")
        {
           $user_cache_key = "user_profile_" . $this->user_id;            
            $this->delete_cache_data($user_cache_key);

           $this->Profile_model->update(USER, array('auto_bank_attempted' => $user_data['auto_bank_attempted']+1), array('user_id' => $this->user_id));


            $this->api_response_arry['data']['auto_bank_attempted'] = $user_data['auto_bank_attempted']+1;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('service_currently_unavailable');
            $this->api_response_arry['global_error'] = $this->lang->line('service_currently_unavailable');
            $this->api_response();
        }

        $user_bank_detail = $this->Profile_model->get_single_row('user_id', USER_BANK_DETAIL, array('user_id' => $this->user_id));

        $reqData = array();
        $custom_data = array();
        $reqData['data'] = array("account_number" => $post_data['ac_number'], "ifsc" => $post_data['ifsc_code'], "consent" => "Y","consent_text"=>"I want to fetch account details.");
        $reqData['task_id'] = $this->user_id . '_' . $post_data['ac_number'];
        $reqData = json_encode($reqData);
        $custom_data['mode'] =  $this->app_config['auto_kyc']['custom_data']['mode'];
        $custom_data['kyc_id'] = $this->app_config['auto_kyc']['custom_data']['kyc_id'];
        $custom_data['kyc_key'] = $this->app_config['auto_kyc']['custom_data']['kyc_key'];
        $custom_data['type'] = 2;
        $validation_data = validate_kyc_info($reqData, $custom_data);
        // $validation_data = validate_bank_info($reqData, $kyc_id, $kyc_key,);
        if (!empty($validation_data))
        {
            if (isset($validation_data['success']) && $validation_data['success'] == true && $validation_data['response_code'] == 100)
            {
                if ($validation_data['result']['verification_status'] == "VERIFIED")
                {
                    $pattern_first_name = "/(?:^|[^a-zA-Z])" . preg_quote(strtoupper($post_data['first_name']), '/') . "(?:$|[^a-zA-Z])/i";
                    $pattern_last_name = "/(?:^|[^a-zA-Z])" . preg_quote(strtoupper($post_data['last_name']), '/') . "(?:$|[^a-zA-Z])/i";

                    // if((strpos(strtoupper($validation_data['result']['BeneName']), strtoupper($post_data['first_name']))) === false)
                    if(preg_match($pattern_first_name, strtoupper($validation_data['result']['beneficiary_name'])) === 1 && preg_match($pattern_last_name, strtoupper($validation_data['result']['beneficiary_name'])) === 1)
                    {
                        $post_values = array();
                        $current_date                   = format_date();
                        $post_values['first_name']      = trim($post_data['first_name']);
                        $post_values['last_name']       = trim($post_data['last_name']);
                        $post_values['last_ip']         = $this->input->ip_address();
                        $post_values['modified_date']   = $current_date;

                        $this->Profile_model->update(USER, $post_values, array('user_id' => $this->user_id));

                        // add user data in feed DB
                        $user_sync_data = array();
                        $user_sync_data['data'] = array(
                            "Action" => "Signup",
                            "UserID" => $this->user_id,
                            "FirstName" => $post_values['first_name'],
                            "LastName" => $post_values['last_name']
                        );
                        $this->load->helper('queue_helper');
                        add_data_in_queue($user_sync_data, 'user_sync');


                        $bank_data = array();
                        $current_date               = format_date();
                        $bank_data['first_name']    = $post_data['first_name'];
                        $bank_data['last_name']     = $post_data['last_name'];
                        $bank_data['bank_name']     = $post_data['bank_name'];
                        $bank_data['ac_number']     = $post_data['ac_number'];
                        $bank_data['ifsc_code']     = $post_data['ifsc_code'];
                        $bank_data['upi_id'] = isset($post_data['upi_id'])?$post_data['upi_id']:"";
                        // $bank_data['bank_document'] = $post_data['bank_document'];
                        $bank_data['modified_date'] = $current_date;
                        $message = $this->lang->line('bank_detail_added_success');
                        if ($user_bank_detail)
                        {
                            $this->Profile_model->update(USER_BANK_DETAIL, $bank_data, array('user_id' => $this->user_id));
                            $message = $this->lang->line('bank_detail_update_success');
                        }
                        else
                        {
                            $bank_data['added_date'] = $current_date;
                            $bank_data['user_id'] = $this->user_id;
                            $this->db->insert(USER_BANK_DETAIL, $bank_data);
                        }
                        $this->Profile_model->update(USER, array('is_bank_verified' => 1), array('user_id' => $this->user_id));
                        $this->Profile_model->update(USER, array('auto_bank_attempted' => $user_data['auto_bank_attempted']+1), array('user_id' => $this->user_id));

                        $user_detail = $this->Profile_model->get_single_row("user_id,user_name,email,phone_no,first_name,last_name",USER,array('user_id' => $this->user_id));

                        $check_affililate_history = $this->Profile_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$user_detail['user_id'],"status" => 1,"affiliate_type in (1,19,20,21)" => null,"user_id != " => "0"));

                        if(empty($check_affililate_history))
                        {
                            //[ bank verification to user w/o referral]
                            $this->bank_verification_bonus_for_non_referral_users($user_detail);
                        }
                        else
                        {
                            //[bank verification to user with referral]
                            $this->bank_verification_bonus_for_referral_users($user_detail);
                        }

                        //delete user profile infor from cache
                        $user_cache_key = "user_profile_" . $this->user_id;
                        $this->delete_cache_data($user_cache_key);

                        $user_balance_cache_key = 'user_balance_' . $this->user_id;
                        $this->delete_cache_data($user_balance_cache_key);
                        // $this->delete_user_sessions($user_detail['user_id']);

                        $this->api_response_arry['data']['auto_bank_attempted'] = $user_data['auto_bank_attempted']+1;
                        $this->api_response_arry['message'] = $message;
                        $this->api_response();
                    }
                    else
                    {
                         //delete user profile infor from cache
                        $user_cache_key = "user_profile_" . $this->user_id;
                        $this->delete_cache_data($user_cache_key);

                        $this->Profile_model->update(USER, array('auto_bank_attempted' => $user_data['auto_bank_attempted']+1), array('user_id' => $this->user_id));

                        $this->api_response_arry['data']['auto_bank_attempted'] = $user_data['auto_bank_attempted']+1;;
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['error'] = array();
                        $this->api_response_arry['global_error'] = $this->lang->line('invalid_bank_details');
                        $this->api_response();
                    }
                }
            }
            else
            {
                if (isset($validation_data['transaction_status']) && $validation_data['transaction_status'] == "0")
                {
                    $this->api_response_arry['data']['auto_bank_attempted'] = 0;
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->lang->line('service_currently_unavailable');
                    $this->api_response();
                }
                else if (isset($validation_data['transaction_status']) && $validation_data['transaction_status'] == "2")
                {
                    //delete user profile infor from cache
                    $user_cache_key = "user_profile_" . $this->user_id;
                    $this->delete_cache_data($user_cache_key);

                    $this->Profile_model->update(USER, array('auto_bank_attempted' => $user_data['auto_bank_attempted']+1), array('user_id' => $this->user_id));

                    $this->api_response_arry['data']['auto_bank_attempted'] = $user_data['auto_bank_attempted']+1;
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->lang->line('invalid_account_number');
                    $this->api_response_arry['global_error'] = $this->lang->line('invalid_bank_details');
                    $this->api_response();
                }
                else
                {
                    //delete user profile infor from cache
                    $user_cache_key = "user_profile_" . $this->user_id;
                    $this->delete_cache_data($user_cache_key);
                    $this->Profile_model->update(USER, array('auto_bank_attempted' => $user_data['auto_bank_attempted']+1), array('user_id' => $this->user_id));


                   
                    $this->api_response_arry['data']['auto_bank_attempted'] = $user_data['auto_bank_attempted']+1;
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $validation_data['message'];
                    $this->api_response_arry['global_error'] = $this->lang->line('invalid_bank_details');
                    $this->api_response();
                }
            }
        }
    }

    //[NRS - BANK verification bonus/coins/real cash to user w/o referral]
    private function bank_verification_bonus_for_non_referral_users($user_detail)
    {
        if(empty($user_detail))
        {
            return TRUE;
        }

        $user_id = $user_detail['user_id'];
        //check if affiliate master entry availalbe for bank verification bonus w/o referral
        $affililate_master_detail = $this->Profile_model->get_single_row('*', AFFILIATE_MASTER,array("affiliate_type"=>16));

        //if no details available then return true.
        if(empty($affililate_master_detail))
        {
            return TRUE;
        }

        //check if signup bonus already given to this user.
        $user_affililate_history = $this->Profile_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$user_id,"affiliate_type"=>16));

        if(!empty($user_affililate_history))
        {
            return TRUE;
        }

        $bouns_condition = array();
        $data_post = array();
        $data_post["friend_id"]         = $user_id;
        $data_post["friend_mobile"]     = (!empty($user_detail['phone_no'])) ? $user_detail['phone_no'] : NULL;
        $data_post["user_id"]           = 0;//FOR WITHOUT REFERRAL CASE
        $data_post["status"]            = 1;
        $data_post["source_type"]       = 0;
        //$data_post["amount_type"]     = 0;
        $data_post["affiliate_type"]    = 16;
        $data_post["is_referral"]       = 0;
    
        //for w/o referral case use only friend bonus/real/coin balance
        $data_post["friend_bonus_cash"] = $affililate_master_detail["user_bonus"];
        $data_post["friend_real_cash"]  = $affililate_master_detail["user_real"];
        $data_post["friend_coin"]       = $affililate_master_detail["user_coin"];
        $data_post["bouns_condition"]   = json_encode($bouns_condition);

        $this->load->model("affiliate/Affiliate_model");
        $affililate_history_id = $this->Affiliate_model->add_affiliate_activity($data_post);
        
        $this->load->model("finance/Finance_model");
        
        $custom_data = array();
		if($this->app_config['allow_crypto']['key_value']==1){
			$custom_data['b_to_c']='crypto wallet';
		}else{
			$custom_data['b_to_c']='bank';
		}
        //Entry on order table for bonus cash type
        if($affililate_master_detail["user_bonus"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $user_id,
                                "amount"    => $affililate_master_detail["user_bonus"],
                                "source"    => 132, //bank verification - bonus cash
                                "source_id" => $affililate_history_id,
                                "plateform" => 1,
                                "cash_type" => 1,// for bonus cash
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "custom_data"=> json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for real cash type
        if($affililate_master_detail["user_real"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $user_id,
                                "amount"    => $affililate_master_detail["user_real"],
                                "source"    => 133, //bank verification - real cash
                                "source_id" => $affililate_history_id,
                                "plateform" => 1,
                                "cash_type" => 0,//for real cash
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "custom_data"=> json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for coins type
        if($affililate_master_detail["user_coin"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $user_id,
                                "amount"    => $affililate_master_detail["user_coin"],
                                "source"    => 134, //bank verification - coins(points)
                                "source_id" => $affililate_history_id,
                                "plateform" => 1,
                                "cash_type" => 2,//for coins(point balance)
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "custom_data"=> json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        return TRUE;
    }

    private function bank_verification_bonus_for_referral_users($friend_detail)
    {
        /*  
            $user_detail = for user who sent referral,
            $friend_detail = for user who used referral code 
        */
        if(empty($friend_detail))
        {
            return TRUE;
        }   

        $friend_name = "Friend";
        if(!empty($friend_detail['first_name']) && !empty($friend_detail['last_name']))
        {
            $friend_name = $friend_detail['first_name'].' '.$friend_detail['last_name'];
        }   
        elseif(!empty($friend_detail['user_name']))
        {
            $friend_name = $friend_detail['user_name'];
        }

        $affililate_master_detail = $this->Profile_model->get_single_row('*', AFFILIATE_MASTER,array("affiliate_type"=>17));
        if(empty($affililate_master_detail))
        {
            return TRUE;
        }   
        
        //check user is referred user or not 
        $is_affiliate_user = $this->Profile_model->get_single_row('*', USER_AFFILIATE_HISTORY,array("friend_id"=>$friend_detail['user_id'],"status" =>1,"affiliate_type in (1,19,20,21)" => null));
        if(empty($is_affiliate_user))
        {
            return TRUE;
        }

        //check pan verification bonus already given to this user 
        $affililate_history = $this->Profile_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$friend_detail['user_id'],"affiliate_type"=>17));
        if(!empty($affililate_history))
        {
            return TRUE;
        }

        $bouns_condition = array();
        $data_post = array();
        $data_post["friend_id"]         = $friend_detail["user_id"];
        $data_post["friend_mobile"]     = (!empty($friend_detail['phone_no'])) ? $friend_detail['phone_no'] : NULL;
        $data_post["user_id"]           = $is_affiliate_user["user_id"];
        $data_post["status"]            = 1;
        $data_post["source_type"]       = $is_affiliate_user['source_type'];
        //$data_post["amount_type"]     = 0;
        $data_post["affiliate_type"]    = 17;
        $data_post["is_referral"]       = 1;
    
       //for user who used referral code
        $data_post["friend_bonus_cash"] = $affililate_master_detail["user_bonus"];
        $data_post["friend_real_cash"]  = $affililate_master_detail["user_real"];
        $data_post["friend_coin"]       = $affililate_master_detail["user_coin"];

        //for user who sent referral(refer code)
        $data_post["user_bonus_cash"]   = $affililate_master_detail["bonus_amount"];
        $data_post["user_real_cash"]    = $affililate_master_detail["real_amount"];
        $data_post["user_coin"]         = $affililate_master_detail["coin_amount"];
        $data_post["bouns_condition"]   = json_encode($bouns_condition);

        // $this->load->model('useraffiliate/Useraffiliate_model');
        // $affililate_history_id =$this->Useraffiliate_model->add_affiliate_activity($data_post);
        // $this->load->model('userfinance/Userfinance_model');

        $this->load->model("affiliate/Affiliate_model");
        $affililate_history_id = $this->Affiliate_model->add_affiliate_activity($data_post);

        $this->load->model("finance/Finance_model");

        $custom_data = array();
        if($this->app_config['allow_crypto']['key_value']==1){
            $custom_data['b_to_c']='crypto wallet';
        }else{
            $custom_data['b_to_c']='bank';
        }

        //Entry on order table for bonus cash type //referred by
        if($affililate_master_detail["bonus_amount"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $is_affiliate_user["user_id"], 
                                "amount"    => $affililate_master_detail["bonus_amount"], 
                                "source"    => 138,//bank verification referral - bonus cash
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 1,// for bonus cash 
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "friend_name"=>$friend_name,
                                "custom_data"=> json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for real cash type
        if($affililate_master_detail["real_amount"] > 0)
        {
            
            $deposit_data_friend = array(
                                "user_id"   => $is_affiliate_user["user_id"], 
                                "amount"    => $affililate_master_detail["real_amount"], 
                                "source"    => 139,//bank verification referral - real cash
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 0,//for real cash 
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "friend_name"=>$friend_name,
                                "custom_data"=> json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for coins type
        if($affililate_master_detail["coin_amount"] > 0)
        {
            
            $deposit_data_friend = array(
                                "user_id"   => $is_affiliate_user["user_id"], 
                                "amount"    => $affililate_master_detail["coin_amount"], 
                                "source"    => 140,//bank verification with referral - coins(points)
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 2,//for coins(point balance) 
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "friend_name"=>$friend_name,
                                "custom_data"=> json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        if(!empty($is_affiliate_user) && isset($is_affiliate_user["user_id"]))
        {
            /* DELETE CACHE OF USER WHOSE REFERRAL CODE IS BEING USED */
            //delete user profile infor from cache
            $user_cache_key = "user_profile_" . $is_affiliate_user["user_id"];
            $this->delete_cache_data($user_cache_key);

            $user_balance_cache_key = 'user_balance_' . $is_affiliate_user["user_id"];
            $this->delete_cache_data($user_balance_cache_key);
            // $this->delete_user_sessions($user_detail['user_id']);
        }

        // Generate transactions for user who used referral code
        //Entry on order table for bonus cash type
        if($affililate_master_detail["user_bonus"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $friend_detail["user_id"], 
                                "amount"    => $affililate_master_detail["user_bonus"], 
                                "source"    => 141,//bank verification with referred - bonus cash
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 1,// for bonus cash 
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "custom_data"=> json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for real cash type
        if($affililate_master_detail["user_real"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $friend_detail["user_id"], 
                                "amount"    => $affililate_master_detail["user_real"], 
                                "source"    => 142,//bank verification referred - real cash
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 0,//for real cash 
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "custom_data"=> json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for coins type
        if($affililate_master_detail["user_coin"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $friend_detail["user_id"], 
                                "amount"    => $affililate_master_detail["user_coin"], 
                                "source"    => 143,//bank verification  referred - coins(points)
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 2,//for coins(point balance) 
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "custom_data"=> json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        return TRUE;
    }

    //[NRS - PAN verification bonus/coins/real cash to user w/o referral]
    private function pan_verification_bonus_for_non_referral_users($user_detail)
    {
        if(empty($user_detail))
        {
            return TRUE;
        }

        $user_id = $user_detail['user_id'];

        //check if affiliate master entry availalbe for pan verification bonus w/o referral
        $affililate_master_detail = $this->Profile_model->get_single_row('*', AFFILIATE_MASTER,array("affiliate_type"=>9));
        //if no details available then return true.
        if(empty($affililate_master_detail))
        {
            return TRUE;
        }

        //check if signup bonus already given to this user.
        $user_affililate_history = $this->Profile_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$user_id,"affiliate_type"=>9));
        if(!empty($user_affililate_history))
        {
            return TRUE;
        }

        $bouns_condition = array();
        $data_post = array();
        $data_post["friend_id"]         = $user_id;
        $data_post["friend_mobile"]     = (!empty($user_detail['phone_no'])) ? $user_detail['phone_no'] : NULL;
        $data_post["user_id"]           = 0;//FOR WITHOUT REFERRAL CASE
        $data_post["status"]            = 1;
        $data_post["source_type"]       = 0;
        //$data_post["amount_type"]     = 0;
        $data_post["affiliate_type"]    = 9;
        $data_post["is_referral"]       = 0;
    
        //for w/o referral case use only friend bonus/real/coin balance
        $data_post["friend_bonus_cash"] = $affililate_master_detail["user_bonus"];
        $data_post["friend_real_cash"]  = $affililate_master_detail["user_real"];
        $data_post["friend_coin"]       = $affililate_master_detail["user_coin"];

        $data_post["bouns_condition"]   = json_encode($bouns_condition);

        $this->load->model("affiliate/Affiliate_model");
        $this->load->model("finance/Finance_model");
        $affililate_history_id = $this->Affiliate_model->add_affiliate_activity($data_post);

        $custom_data = array();
        if(INT_VERSION == 1) {
            $custom_data['p_to_id'] = "ID";
        }else{  
            $custom_data['p_to_id'] = "PAN";
        }
        //Entry on order table for bonus cash type
        if($affililate_master_detail["user_bonus"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $user_id,
                                "amount"    => $affililate_master_detail["user_bonus"],
                                "source"    => 59, //pan verification - bonus cash
                                "source_id" => $affililate_history_id,
                                "plateform" => 1,
                                "cash_type" => 1,// for bonus cash
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "custom_data" =>json_encode($custom_data),
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for real cash type
        if($affililate_master_detail["user_real"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $user_id,
                                "amount"    => $affililate_master_detail["user_real"],
                                "source"    => 60, //pan verification - real cash
                                "source_id" => $affililate_history_id,
                                "plateform" => 1,
                                "cash_type" => 0,//for real cash
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "custom_data" =>json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for coins type
        if($affililate_master_detail["user_coin"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $user_id, 
                                "amount"    => $affililate_master_detail["user_coin"], 
                                "source"    => 61, //pan verification - coins(points)
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 2,//for coins(point balance) 
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "custom_data" =>json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);               
        }

        return TRUE;
    }

    //[NRS - PAN verification bonus/coins/real cash to referral users]
    private function pan_verification_bonus_for_referral_users($friend_detail)
    {
        /*
            $user_detail = for user who sent referral,
            $friend_detail = for user who used referral code 
        */
        if(empty($friend_detail))
        {
            return TRUE;
        }

        $friend_name = "Friend";
        if(!empty($friend_detail['first_name']) && !empty($friend_detail['last_name']))
        {
            $friend_name = $friend_detail['first_name'].' '.$friend_detail['last_name'];
        }   
        elseif(!empty($friend_detail['user_name']))
        {
            $friend_name = $friend_detail['user_name'];
        }

        $affililate_master_detail = $this->Profile_model->get_single_row('*', AFFILIATE_MASTER,array("affiliate_type"=>5));

        if(empty($affililate_master_detail))
        {
            return TRUE;
        }
        
        //check user is referred user or not 
        $is_affiliate_user = $this->Profile_model->get_single_row('*', USER_AFFILIATE_HISTORY,array("friend_id"=>$friend_detail['user_id'],"status" =>1,"affiliate_type in (1,19,20,21)" => null));
        if(empty($is_affiliate_user))
        {
            return TRUE;
        }

        //check pan verification bonus already given to this user 
        $affililate_history = $this->Profile_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$friend_detail['user_id'],"affiliate_type"=>5));
        if(!empty($affililate_history))
        {
            return TRUE;
        }

        $bouns_condition = array();
        $data_post = array();
        $data_post["friend_id"]         = $friend_detail["user_id"];
        $data_post["friend_mobile"]     = (!empty($friend_detail['phone_no'])) ? $friend_detail['phone_no'] : NULL;
        $data_post["user_id"]           = $is_affiliate_user["user_id"];
        $data_post["status"]            = 1;
        $data_post["source_type"]       = $is_affiliate_user['source_type'];
        //$data_post["amount_type"]     = 0;
        $data_post["affiliate_type"]    = 5;
        $data_post["is_referral"]       = 1;

       //for user who used referral code
        $data_post["friend_bonus_cash"] = $affililate_master_detail["user_bonus"];
        $data_post["friend_real_cash"]  = $affililate_master_detail["user_real"];
        $data_post["friend_coin"]       = $affililate_master_detail["user_coin"];

        //for user who sent referral(refer code)
        $data_post["user_bonus_cash"]   = $affililate_master_detail["bonus_amount"];
        $data_post["user_real_cash"]    = $affililate_master_detail["real_amount"];
        $data_post["user_coin"]         = $affililate_master_detail["coin_amount"];
        $data_post["bouns_condition"]   = json_encode($bouns_condition);

        $this->load->model("affiliate/Affiliate_model");

        $data_post["created_date"] = date("Y-m-d H:i:s");
        $affililate_history_id = $this->Affiliate_model->add_affiliate_activity($data_post);

        $this->load->model("finance/Finance_model");
        // Generate transactions for user who sent referral(referral code)
        if(INT_VERSION == 1) {
            $custom_data['p_to_id'] = "ID";
        }else{  
            $custom_data['p_to_id'] = "PAN";
        }
        //Entry on order table for bonus cash type
        if($affililate_master_detail["bonus_amount"] > 0){
            //$post_target_url  = 'finance/deposit';
            $deposit_data_friend = array(
                                "user_id"   => $is_affiliate_user["user_id"],
                                "amount"    => $affililate_master_detail["bonus_amount"],
                                "source"    => 62,//pan verification with referral - bonus cash
                                "source_id" => $affililate_history_id,
                                "plateform" => 1,
                                "cash_type" => 1,// for bonus cash
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "friend_name"=>$friend_name,
                                "custom_data" =>json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
            
        }

        //Entry on order table for real cash type
        if($affililate_master_detail["real_amount"] > 0)
        {
            //$post_target_url  = 'finance/deposit';
            $deposit_data_friend = array(
                                "user_id"   => $is_affiliate_user["user_id"],
                                "amount"    => $affililate_master_detail["real_amount"],
                                "source"    => 63,//pan verification with referral - real cash
                                "source_id" => $affililate_history_id,
                                "plateform" => 1,
                                "cash_type" => 0,//for real cash
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "friend_name"=>$friend_name,
                                "custom_data" =>json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for coins type
        if($affililate_master_detail["coin_amount"] > 0)
        {
            //$post_target_url  = 'finance/deposit';
            $deposit_data_friend = array(
                                "user_id"   => $is_affiliate_user["user_id"],
                                "amount"    => $affililate_master_detail["coin_amount"],
                                "source"    => 64,//pan verification with referral - coins(points)
                                "source_id" => $affililate_history_id,
                                "plateform" => 1,
                                "cash_type" => 2,//for coins(point balance)
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "friend_name"=>$friend_name,
                                "custom_data" =>json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        if(!empty($is_affiliate_user) && isset($is_affiliate_user["user_id"]))
        {
            /* DELETE CACHE OF USER WHOSE REFERRAL CODE IS BEING USED */
            //delete user profile infor from cache
            $user_cache_key = "user_profile_" . $is_affiliate_user["user_id"];
            $this->delete_cache_data($user_cache_key);

            $user_balance_cache_key = 'user_balance_' . $is_affiliate_user["user_id"];
            $this->delete_cache_data($user_balance_cache_key);
            // $this->delete_user_sessions($user_detail['user_id']);
        }

        // Generate transactions for user who used referral code
        //Entry on order table for bonus cash type
        if($affililate_master_detail["user_bonus"] > 0)
        {
            $post_target_url    = 'finance/deposit';
            $deposit_data_friend = array(
                                "user_id"   => $friend_detail["user_id"],
                                "amount"    => $affililate_master_detail["user_bonus"],
                                "source"    => 65,//pan verification with referral - bonus cash
                                "source_id" => $affililate_history_id,
                                "plateform" => 1,
                                "cash_type" => 1,// for bonus cash
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "custom_data" =>json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for real cash type
        if($affililate_master_detail["user_real"] > 0)
        {   
            //$post_target_url  = 'finance/deposit';
            $deposit_data_friend = array(
                                "user_id"   => $friend_detail["user_id"],
                                "amount"    => $affililate_master_detail["user_real"],
                                "source"    => 66,//pan verification with referral - real cash
                                "source_id" => $affililate_history_id,
                                "plateform" => 1,
                                "cash_type" => 0,//for real cash
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "custom_data" =>json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        //Entry on order table for coins type
        if($affililate_master_detail["user_coin"] > 0)
        {
            //$post_target_url  = 'finance/deposit';
            $deposit_data_friend = array(
                                "user_id"   => $friend_detail["user_id"],
                                "amount"    => $affililate_master_detail["user_coin"],
                                "source"    => 67,//pan verification with referral - coins(points)
                                "source_id" => $affililate_history_id,
                                "plateform" => 1,
                                "cash_type" => 2,//for coins(point balance)
                                "link"      => FRONT_APP_PATH.'my-wallet',
                                "custom_data" =>json_encode($custom_data)
                            );
            $this->Finance_model->deposit_fund($deposit_data_friend);
        }

        return TRUE;
    }

    // [check bank's state]
    private function check_bank_state($ifsc_code)
    {
        $state_list = array("ANDHRA PRADESH", "ASSAM", "NAGALAND", "ORISSA", "SIKKIM", "TELANGANA", "TAMIL NADU");
        $reqData = array();
        $custom_data = array();
        $reqData['data'] = array("ifsc" => $ifsc_code, "consent" => "Y", "consent_text" => "I want to check this ifsc");
        $reqData['task_id'] = $this->user_id . '_' . $ifsc_code;
        $reqData = json_encode($reqData);
        $custom_data['mode'] =  $this->app_config['auto_kyc']['custom_data']['mode'];
        $custom_data['kyc_id'] = $this->app_config['auto_kyc']['custom_data']['kyc_id'];
        $custom_data['kyc_key'] = $this->app_config['auto_kyc']['custom_data']['kyc_key'];
        $custom_data['type'] = 3;
        $validation_data = validate_kyc_info($reqData, $custom_data);
        // $validation_data = validate_ifsc_info($reqData, $kyc_id, $kyc_key,$mode);
        if (!empty($validation_data))
        {
            if (isset($validation_data['success']) && $validation_data['success'] == "1")
            {
                if(in_array(strtoupper($validation_data['result']['state']), $state_list))
                {
                    return "BANNED";
                }
                else
                {
                    return "VALID";
                }
            }
            else
            {
                if (isset($validation_data['response_code']) && $validation_data['response_code'] != 100)
                {
                    return "INVALID";
                }
                else
                {
                    return "FAILED";
                }
            }
        }
        else
        {
            return "FAILED";
        }
    }

// public function get_first_rendom_avatar_post(){
//     $this->load->model("Profile_model");
    // $avatar = $this->Profile_model->get_first_rendom_avatar();

// }

/**
     * method to get all active avater at user end
     * @return list of avatar names
     */
public function get_avatars_post(){
    $this->load->model("Profile_model");
    $avatar = $this->Profile_model->get_avatars();
    $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
    $this->api_response_arry['error'] = array();
    $this->api_response_arry['message'] = array(); 
    $this->api_response_arry['data'] = $avatar;
    $this->api_response();
}

/**
     * method to update profile picture and delete cache 
     *@param image name
     * @return list of avatar names
     */

public function update_profile_picture_post(){
    $this->form_validation->set_rules('image', 'Image name', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $image = $this->input->post('image');
        $this->load->model("Profile_model");
        $update_image = $this->Profile_model->update_profile_picture($image);
        if($update_image){

            $user_sync_data = array();
            $user_sync_data['data'] = array(
                "Action" => "Signup",
                "UserID" => $this->user_id,
                "ProfilePicture" => $image
            );
            $this->load->helper('queue_helper');
            add_data_in_queue($user_sync_data, 'user_sync');

            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['error'] = array();
            // $this->api_response_arry['message'] = 'Profile Image updated successfully'; 
            $this->api_response_arry['message'] = $this->lang->line('image_upload_success'); 
            $this->api_response_arry['data'] = array();
            $this->api_response();   
        }
        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['error'] = array();
        // $this->api_response_arry['global_error'] = 'Some error in uploading image, try again later'; 
        $this->api_response_arry['global_error'] = $this->lang->line('image_upload_error'); 
        $this->api_response();
}

    /**
     * used for get app notification setting
     * @param
     * @return json array
     */
    public function update_app_notification_setting_post()
    {
        $this->load->model("Profile_model");
        $vconfig = array(
            array(
                'field' => 'app_notification_setting',
                'label' => 'Notification setting',
                'rules' => 'required'
            )
        );
        $this->form_validation->set_rules($vconfig);
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();

        $user_detail = $this->Profile_model->get_single_row('app_notification_setting, bank_rejected_reason',USER,array("user_id"=>$this->user_id));
        if ($user_detail && isset($user_detail['app_notification_setting']))
        {
            $update_data = array();

            $update_data['app_notification_setting'] = $post_data['app_notification_setting'];
            $this->Profile_model->update(USER, $update_data, array('user_id' => $this->user_id));

            $user_cache_key = "user_profile_" . $this->user_id;
            $this->delete_cache_data($user_cache_key);

            $this->api_response_arry['data']['app_notification_setting'] = $update_data['app_notification_setting'];
            $this->api_response_arry['message'] = $this->lang->line('notification_setting_updated');
            $this->api_response();
        }
    }

    /**
     * this function used for update state and declaration
     * @param
     * @return json array
     */
    public function update_declaration_post() {
        $this->load->model("Profile_model");
        $vconfig = array(
            array(
                'field' => 'master_country_id',
                'label' => 'country',
                'rules' => 'trim|required|callback_country_exist'
            ),
            array(
                'field' => 'master_state_id',
                'label' => 'state',
                'rules' => 'trim|callback_state_exist'
            )
        );
        $this->form_validation->set_rules($vconfig);
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_data = $this->post();
        $current_date = format_date();
        $post_values = array();
        $post_values['master_country_id'] = $post_data['master_country_id'];
        $post_values['master_state_id'] = $post_data['master_state_id'];
        $post_values['state_declaration'] = 1;
        $post_values['modified_date'] = $current_date;
        $this->Profile_model->update(USER, $post_values, array('user_id' => $this->user_id));
        $this->api_response_arry['message'] = $this->lang->line('state_declaration_successfully');
        $this->api_response();
    }

    /**
     * method to track user's active session time spent. 
     *@param platform, time_spent, if not web - version_code, os_platform, device_name(model)
     *@return true/false
     */
    public function track_active_session_post()
    {
        $post_data = $this->post();
        $this->load->model("auth/Auth_nosql_model");
        if (isset($post_data) && is_array($post_data) && count($post_data) > 0)
        {
            foreach ($post_data as $key => $value)
            {
                $single_session_data    = array();
                $mongo_date_start       = '';
                $mongo_date_end         = '';
                if(!empty($value['end_time']) && !empty($value['start_time'])){
                    $session_time       = strtotime($value['end_time']) - strtotime($value['start_time']); // in seconds
                    $mongo_date_start   = $this->Auth_nosql_model->normal_to_mongo_date($value['start_time']);
                    $mongo_date_end     = $this->Auth_nosql_model->normal_to_mongo_date($value['end_time']);

                    $session_data = array(
                        'user_id'               => $this->user_id,
                        'start_time'            => $mongo_date_start,
                        'end_time'              => $mongo_date_end,
                        'platform'              => $value['platform'],
                        'is_browser'            => $value['is_browser'],
                        'is_tablet'             => $value['is_tablet'],
                        'time_spent'            => $session_time,
                        'device_os'             => isset($value['os']) ? $value['os'] : '',
                        'device_os_version'     => isset($value['os_version']) ? $value['os_version'] : '',
                        'device_name'           => isset($value['device_name']) ? $value['device_name'] : '',
                        'app_version'           => isset($value['app_version']) ? $value['app_version'] : '',
                        'install_date'          => isset($value['install_date']) ? $value['install_date'] : '',
                        'timestamp'             => isset($value['timestamp']) ? $value['timestamp'] : '',
                    );
                    // echo "<pre>";print_r($session_data);
                    $this->Auth_nosql_model->insert_nosql(SESSION_TRACK, $session_data);
                }
            }
            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response_arry['status']          = TRUE;
            $this->api_response_arry['message']         = $this->lang->line('feedback_saved');
            $this->api_response_arry['data']            = TRUE;
        }
        else
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['message']         = $this->lang->line('input_invalid_format');
            $this->api_response_arry['data']            = array();
        }
        $this->api_response();
    }

    /**
     * used for get user public profile data
     * @param
     * @return json array
     */
    public function user_detail_post() {
        $vconfig = array(
            array(
                'field' => 'user_id',
                'label' => 'user id',
                'rules' => 'trim|required'
            )
        );
        $this->form_validation->set_rules($vconfig);
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();
        $this->load->model("Profile_model");
        $user_id = $post_data['user_id'];
        $user_profile  = $this->Profile_model->get_single_row('first_name,last_name,user_unique_id,user_name,IFNULL(image,"") as image', USER, array('user_id' => $user_id));

        //get playing experience details and cache it
        $playing_experience = $this->get_user_series_count($user_id);
        $user_profile['pe'] = $playing_experience;
        $this->api_response_arry['data'] = $user_profile;
        $this->api_response();
    }

    /**
     *  Used to upload pan card
     * @param
     * @return json array
     */
    public function do_upload_aadhar_post() {
        $file_field_name = 'userfile';
        if (!isset($_FILES[$file_field_name])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('file_not_found'));
            $this->api_response();
        }
        $dir = AADHAR_IMAGE_UPLOAD_PATH;
        $temp_file = $_FILES[$file_field_name]['tmp_name'];
        $ext = pathinfo($_FILES[$file_field_name]['name'], PATHINFO_EXTENSION);
            
        if (!in_array(strtolower($ext), array('jpg', 'jpeg', 'png', 'pdf'))) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_ext'));
            $this->api_response();
        }

        if (!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > '4194304') {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_image_size'));
            $this->api_response();
        }
        $file_name = time() . "." . $ext;
        /* --Start amazon server upload code-- */
        if (strtolower(IMAGE_SERVER) == 'remote') {

            $filePath = $dir . $file_name;
            try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $image_path = AADHAR_IMAGE_PATH . $file_name;
                    $return_array = array('image_path' => $image_path, 'file_name' => $file_name);
                    $this->api_response_arry['data'] = $return_array;
                }

            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response(); 
            }
        } else {
            $config['allowed_types'] = 'jpg|png|jpeg|pdf';
            $config['max_size'] = '4096'; //KB
            $config['upload_path'] = $dir;
            $config['file_name'] = $file_name;

            $this->load->library('upload', $config);
            if (!$this->upload->do_upload($file_field_name)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = strip_tags($this->upload->display_errors());
                $this->api_response();
            } else {
                $uploaded_data = $this->upload->data();
                $thumb_path = AADHAR_IMAGE_PATH . $uploaded_data['file_name'];
                $this->api_response_arry['data'] = array('image_path' => $thumb_path, 'file_name' => $uploaded_data['file_name']);
            }
        }

        $this->api_response();
    }

    public function save_aadhar_post()
    {
        $form_config = array(
            array(
                'field' => 'name',
                'label' => "name",
                'rules' => 'required|trim|min_length[2]|max_length[250]'
            ),
            array(
                'field' => 'aadhar_number',
                'label' =>  'Aadhaar number',
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'front_image',
                'label' => 'front image',
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'back_image',
                'label' => 'back image',
                'rules' => 'trim|required'
            )
        );
        $this->form_validation->set_rules($form_config);
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_values = array();
        $post_data = $this->post();
        $this->load->model("Profile_model");

        $check_exist = $this->Profile_model->get_single_row('user_id', USER_AADHAR, array("aadhar_number" => trim($post_data['aadhar_number']),'status'=>1));
        if(!empty($check_exist)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('duplicate_aadhar_no'); 
            $this->api_response();
        }

        $user_data = $this->Profile_model->get_single_row('user_id,aadhar_status', USER, array("user_id" => $this->user_id));
        //if pancard varified then first name ,last name and ,dob can not be update    
        if(!empty($user_data['aadhar_status']) && $user_data['aadhar_status'] == '1')
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('err_update_post_aadhar_verify'); 
            $this->api_response();   
        }

        $aadhar_detail = $this->Profile_model->get_single_row('user_id', USER_AADHAR, array('user_id' => $this->user_id));

        $current_date = format_date();
        $aadhar_data = array();
        $aadhar_data['name'] = $post_data['name'];
        $aadhar_data['aadhar_number'] = $post_data['aadhar_number'];
        $aadhar_data['front_image'] = $post_data['front_image'];
        $aadhar_data['back_image'] = $post_data['back_image'];
        $aadhar_data['status'] = 0;
        $aadhar_data['modified_date'] = $current_date;
        if($aadhar_detail) {
            $this->Profile_model->update(USER_AADHAR, $aadhar_data, array('user_id' => $this->user_id));
            $this->Profile_model->update(USER, array('aadhar_status' => 0), array('user_id' => $this->user_id));
        
            $message = $this->lang->line('aadhar_detail_update_success');
        } else {
            $aadhar_data['user_id'] = $this->user_id;
            $aadhar_data['added_date'] = $current_date;
            $this->db->insert(USER_AADHAR, $aadhar_data);
            
            $message = $this->lang->line('aadhar_detail_added_success');
        }

        //delete user profile infor from cache
        $user_cache_key = "user_profile_" . $this->user_id;
        $this->delete_cache_data($user_cache_key);

        //remove aadhar key
        $this->delete_cache_data('user_aadhar_'.$this->user_id);

        $this->api_response_arry['message'] = $message;
        $this->api_response();
    }

    /**
     * used for get user aadhar data
     * @param
     * @return json array
     */
    public function get_user_aadhar_post() {
        $cache_key = 'user_aadhar_'.$this->user_id;
        $aadhar_data = $this->get_cache_data($cache_key);
        if (!$aadhar_data) {
            $this->load->model('Profile_model');
            $user_info = $this->Profile_model->get_single_row('aadhar_id,status',USER_AADHAR, array('user_id' => $this->user_id));
            $aadhar_data = array("aadhar_status"=>"0","aadhar_id"=>"0");
            if(!empty($user_info)){
                $aadhar_data['aadhar_status'] = $user_info['status'];
                $aadhar_data['aadhar_id'] = $user_info['aadhar_id'];
            }
            $this->set_cache_data($cache_key, $aadhar_data, 300);
        }
        $this->api_response_arry['data'] = $aadhar_data;
        $this->api_response();
    }

    /**
    * This function used for the otp send to the aadhar mobile number   
    * parameter: aadhar number
    * return: json 
    */
    public function generate_aadhar_otp_post()
    {
        $is_auto_mode = $this->app_config['allow_aadhar']['custom_data']['is_auto_mode'] ? $this->app_config['allow_aadhar']['custom_data']['is_auto_mode'] : 0;
        if(!$is_auto_mode)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Auto aadhar verification is off, try manually"; 
            $this->api_response();
        }

        $form_config = array(
            array(
                'field' => 'aadhar_number',
                'label' => $this->lang->line('aadhar_number'),
                'rules' => 'trim|required'
            )
        );
        $this->form_validation->set_rules($form_config);
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_values = array();
        $post_data   = $this->post();
        
        $this->load->model("Profile_model");

        $check_exist = $this->Profile_model->get_single_row('user_id', USER_AADHAR, array("aadhar_number" => trim($post_data['aadhar_number']),'status'=>1));
        if(!empty($check_exist)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('duplicate_aadhar_no'); 
            $this->api_response();
        }
        
        $user_data = $this->Profile_model->get_single_row('user_id,aadhar_status', USER, array("user_id" => $this->user_id));
        //if pancard varified then first name ,last name and ,dob can not be update    
        if(!empty($user_data['aadhar_status']) && $user_data['aadhar_status'] == '1')
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('err_update_post_aadhar_verify'); 
            $this->api_response();   
        }

        // set pendding all aadhar verfiy status is rejected
        $this->Profile_model->update(USER_AADHAR,array('status' => 2),array('status' => 0,'aadhar_number'=>trim($post_data['aadhar_number'])));

        $reqData = array();
        $custom_data = array();
        $reqData['data'] = array(
            "customer_aadhaar_number" => trim($post_data['aadhar_number']),
            "consent" => "Y",
            "consent_text" => "I hear by declare my consent agreement for fetching my information via ZOOP API."
        );
        $reqData['task_id'] = generate_task_id();
        $reqData['mode'] = "sync";
        $reqData = json_encode($reqData);
        $custom_data['mode'] =  $this->app_config['auto_kyc']['custom_data']['mode'];
        $custom_data['kyc_id'] = $this->app_config['auto_kyc']['custom_data']['kyc_id'];
        $custom_data['kyc_key'] = $this->app_config['auto_kyc']['custom_data']['kyc_key'];
        $custom_data['type'] = 4;
        $response = validate_kyc_info($reqData, $custom_data);
        $response_code = isset($response['response_code'])?$response['response_code']:'';
        $success = isset($response['success'])?$response['success']:'';
        if(!empty($success) && $response_code == 100){
            $res= array(
                'request_id' => isset($response['request_id'])?$response['request_id']:'',
                'task_id'    => isset($response['task_id'])?$response['task_id']:'',
                'aadhar_number'=>$post_data['aadhar_number']
            );
            
            $request_id = isset($response['task_id'])?$response['task_id']:'';
            
            $this->api_response_arry['data']= $res;
            $this->api_response_arry["message"] = $this->lang->line('aadhar_otp_send_success');;
            $this->api_response();
        }else{
            $response_message = isset($response['response_message'])?$response['response_message']:'';

            $error_code_zoop=array(99,113,112,111,110);
            if(in_array($response_code, $error_code_zoop))
            {
                $response_message = isset($response['response_message'])?$response['response_message']:'';
                log_message('error',' == aadhaar error == '.$response_message);
                $error_ms = "Something went wrong while generate otp, please try again after some time.";
            }else{
                $error_ms = $this->lang->line('invalid_aadhar_number');
            }

            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] =  $error_ms;
            $this->api_response();
        }
    }

    /**
    * This function used for verfiy aadhar otp   
    * parameter: aadhar number,request_id,task_id
    * return: json 
    */
    public function verify_aadhar_otp_post()
    {
        $is_auto_mode = $this->app_config['allow_aadhar']['custom_data']['is_auto_mode'] ? $this->app_config['allow_aadhar']['custom_data']['is_auto_mode'] : 0;
        if(!$is_auto_mode)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Auto aadhar verification is off, try manually"; 
            $this->api_response();
        }
        
        $form_config = array(
            array(
                'field' => 'aadhar_number',
                'label' => $this->lang->line('aadhar_number'),
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'otp',
                'label' => $this->lang->line('otp'),
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'request_id',
                'label' => $this->lang->line('request_id'),
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'task_id',
                'label' => $this->lang->line('task_id'),
                'rules' => 'trim|required'
            )
        );
        $this->form_validation->set_rules($form_config);
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_values = array();
        $post_data   = $this->post();
        
        $this->load->model("Profile_model");

        // set pendding all aadhar verfiy status is rejected
        $this->Profile_model->update(USER_AADHAR,array('status' => 2),array('status' => 0,'aadhar_number'=>trim($post_data['aadhar_number'])));

        $check_exist = $this->Profile_model->get_single_row('user_id', USER_AADHAR, array("aadhar_number" => trim($post_data['aadhar_number']),'status'=>1));
        if(!empty($check_exist)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('duplicate_aadhar_no'); 
            $this->api_response();
        }

        $user_data = $this->Profile_model->get_single_row('user_id,aadhar_status', USER, array("user_id" => $this->user_id));
        //if pancard varified then first name ,last name and ,dob can not be update    
        if(!empty($user_data['aadhar_status']) && $user_data['aadhar_status'] == '1')
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('err_update_post_aadhar_verify'); 
            $this->api_response();   
        }

        $reqData = array();
        $custom_data = array();
        $reqData['data'] = array(
            "request_id"=> trim($post_data['request_id']),
            "otp"=> trim($post_data['otp']),
            "consent"  => "Y",
            "consent_text"=> "I hear by declare my consent agreement for fetching my information via ZOOP API."
        );
        $reqData['task_id'] = generate_task_id();
        $reqData['mode'] = "sync";
        $reqData = json_encode($reqData);
        $custom_data['mode'] =  $this->app_config['auto_kyc']['custom_data']['mode'];
        $custom_data['kyc_id'] = $this->app_config['auto_kyc']['custom_data']['kyc_id'];
        $custom_data['kyc_key'] = $this->app_config['auto_kyc']['custom_data']['kyc_key'];
        $custom_data['type'] = 5;
        $response = validate_kyc_info($reqData, $custom_data);
        $success = isset($response['success'])?$response['success']:'';
        $result  = isset($response['result'])?$response['result']:array();
        if(!empty($success) && !empty($result)){
            $user_full_name = isset($result['user_full_name'])?$result['user_full_name']:'';
            // date format change
            $user_dob = isset($result['user_dob'])?$result['user_dob']:'';
            $user_dob = str_replace('/', '-', $user_dob);
            $dob      = date("Y-m-d", strtotime($user_dob));  
            
            // check gender
            $user_gender = isset($result['user_gender'])?$result['user_gender']:'M';
            if($user_gender == 'M'){
                $gender = 'male';
            }elseif($user_gender == 'F'){
                $gender = 'female';
            }else{
                $gender = 'other';
            }
           
            $address_zip    = isset($result['address_zip'])?$result['address_zip']:'';
            $user_address   = isset($result['user_address'])?$result['user_address']:array();
            $city           = isset($user_address['dist'])?$user_address['dist']:'';
            $address        = isset($user_address['house'])?$user_address['house']:'';
            $street         = isset($user_address['street'])?$user_address['street']:'';
            $landmark       = isset($user_address['landmark'])?$user_address['landmark']:'';
            $state          = isset($user_address['state'])?$user_address['state']:'';
           

            $master_country_id = '101'; // for india
            // check state id
            $check_state = $this->Profile_model->get_single_row('master_state_id', MASTER_STATE, array("name" => trim($state),'master_country_id'=>$master_country_id));
            if(!empty($check_state)){
                $master_state_id = $check_state['master_state_id'];
            }else{

                log_message('error',' == Invalid state == aadhar_number '.$post_data['aadhar_number'].' == '.json_encode($user_address));
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('invalid_state'); 
                $this->api_response();
            }
            // set profile data given from aadhar data
            $profile_data=array(
                'gender'=>$gender,
                'dob'=>$dob,
                //'master_country_id'=>$master_country_id,
                'master_state_id'=>$master_state_id,
                'address'=>$address.' , '.$street.' , '.$landmark,
                'city'=>$city,
                'zip_code'=>$address_zip,
                'aadhar_status'=>1
            );
            
            $aadhar_detail = $this->Profile_model->get_single_row('user_id', USER_AADHAR, array('user_id' => $this->user_id));

            $current_date = format_date();
            $aadhar_data = array();
            $aadhar_data['name'] = $user_full_name;
            $aadhar_data['aadhar_number'] = $post_data['aadhar_number'];
            $aadhar_data['status'] = 1;
            $aadhar_data['verify_by'] = 2; // 2 auto
            $aadhar_data['front_image'] = '';
            $aadhar_data['back_image'] = '';
            $aadhar_data['modified_date'] = $current_date;

            if($aadhar_detail){
                $this->Profile_model->update(USER_AADHAR, $aadhar_data, array('user_id' => $this->user_id));
                
                $message = $this->lang->line('aadhar_verify_success');
            }else{
                $aadhar_data['user_id'] = $this->user_id;
                $aadhar_data['added_date'] = $current_date;
                $this->db->insert(USER_AADHAR, $aadhar_data);
                
                $message = $this->lang->line('aadhar_verify_success');
            }

            $this->Profile_model->update(USER,$profile_data, array('user_id' => $this->user_id));
            
            //delete user profile infor from cache
            $user_cache_key = "user_profile_" . $this->user_id;
            $this->delete_cache_data($user_cache_key);

            $this->api_response_arry['message'] = $message;
            $this->api_response();
        }else{
            $error_msg = isset($response['metadata']['reason_message'])?$response['metadata']['reason_message']:'Invalid format';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('invalid_aadhar_number_otp');  
            $this->api_response();
        }
    }
}
