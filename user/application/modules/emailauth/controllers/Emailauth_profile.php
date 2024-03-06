<?php

require_once APPPATH . 'modules/profile/controllers/Profile.php';

class Emailauth_profile extends Profile {

    function __construct() {
        parent::__construct();
    }

    /**
     * update_profile_data used to update user details
     * @param
     * @return json array
     */
    public function update_profile_data_post() {
        $this->form_validation->set_rules('user_name', 'username', 'trim|required|callback_check_username');
        $this->form_validation->set_rules('password', 'password', 'trim|required');
        $this->form_validation->set_rules('phone_no', 'Phone No', 'trim|callback_is_digits|callback_check_phone_no');
        $this->form_validation->set_rules('phone_code', 'Phone code', 'trim|max_length[4]');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $this->load->model("auth/Auth_model");
        //update username and email
        $post_data = $this->post();
        $user_data = array();
        $nosql_data = array();
        $today = format_date();    

        $user_data['user_name'] = $post_data["user_name"];
        $user_data['password'] =  md5($post_data["password"]);
        if ( isset($post_data["phone_no"]) && !empty($post_data["phone_no"])) {
            $single_country = !empty($this->app_config['single_country'])?$this->app_config['single_country']['key_value']:0;
            $phone_code = !empty($this->app_config['phone_code'])?$this->app_config['phone_code']['key_value']:DEFAULT_PHONE_CODE;
            if($single_country == 1 && $post_data['phone_code'] != $phone_code){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line("disable_country_signup_error");
                $this->api_response();
            }
         $user_data['phone_no'] = $post_data["phone_no"];
         $user_data['phone_code'] = $post_data["phone_code"];
        }    
        //echo '<pre>';print_r($user_data);die;
        $nosql_data['user_name'] = $post_data["user_name"];
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

        if (!empty($user_data)) {
            $this->Auth_model->update_user_data($this->user_id, array(), $user_data);
        }

        if(!empty($nosql_data)){
            $this->load->model("auth/Auth_nosql_model");
            $this->Auth_nosql_model->update_all_nosql(ACTIVE_LOGIN,array("user_id"=>$this->user_id),$nosql_data);
        }

        $this->user_name =!empty($this->user_name)?$this->user_name:$post_data['user_name'];
        $this->phone_no =!empty($this->phone_no)?$this->phone_no:$post_data['phone_no'];
        //apply referral code
        if (!empty($post_data['referral_code'])) { 
            $refer_user_detail = $this->Auth_model->get_single_row('user_id,user_name,phone_no,email', USER, array("referral_code" => $post_data['referral_code']));

            if (empty($refer_user_detail)) {
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
            if (!empty($refer_user_detail) && !empty($friend_detail)) {
                $this->add_bonus($friend_detail, $refer_user_detail['user_id'], 1, $source_type);  
                $user_cache_key = "user_balance_" . $refer_user_detail['user_id'];
                $this->delete_cache_data($user_cache_key);                    
            }
        }
        
        $this->load->model("profile/Profile_model");
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
            //echo '<pre>';print_r($user_detail);die;
            $this->add_bonus($user_detail, 0, 6);

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
            }
            //$this->add_signup_bonus_without_referral($user_detail);
        }

        $this->api_response_arry['data'] = array("user_name" => $post_data['user_name']);
        $this->api_response_arry["message"] = $this->lang->line('signup_success');;
        $this->api_response();
    }

    /**
     * Used for check user phone number exist or not in db
     * @return boolean
     */
    public function check_phone_no() {
        $this->load->model("Emailauth_model");
        $post_data = $this->post();

        if(empty($post_data['phone_no']) && empty($post_data['phone_code'])){
            return true;
        }
        elseif(!empty($post_data['phone_no']) && empty($post_data['phone_code']))
        { 
           $this->form_validation->set_message('check_phone_no', 'Phone code is required when you sent phone no');
            return FALSE;
        }elseif(empty($post_data['phone_no']) && !empty($post_data['phone_code'])){
           $this->form_validation->set_message('check_phone_no', 'Phone no is required when you sent phone code.');
            return FALSE;
        }
        $user_data = $this->Emailauth_model->get_single_row('phone_no,user_id,phone_code', USER, array("phone_no" => $post_data['phone_no'], "phone_code" => $post_data['phone_code']));

        if (!$user_data || ($user_data["phone_no"] == $post_data['phone_no'] && $user_data["phone_code"] == $post_data['phone_code'] && $user_data["user_id"] == $this->user_id)) {
            return TRUE;
        }
        $this->form_validation->set_message('check_phone_no', $this->lang->line("phone_no_already_exists"));
        return FALSE;


    }

    public function check_username() {
        $post_data = $this->post();
        $this->load->model("Emailauth_model"); 
        $user_data = $this->Emailauth_model->get_single_row('user_name,user_id', USER, array("user_name" => $post_data['user_name']));

        if (!$user_data || ($user_data["user_name"] == $post_data['user_name'] && $user_data["user_id"] == $this->user_id)) {
            return TRUE;
        }
        $this->form_validation->set_message('check_username', $this->lang->line("user_name_already_exists"));
        return FALSE;
    }

     public function is_digits($phone_no) {
        if(empty($phone_no)){return true;}

        if (preg_match("/^[0-9]+$/", $phone_no)) {
            return TRUE;
        }
        $this->form_validation->set_message('is_digits', 'Please enter valid phone no');
        return FALSE;
    }

}
