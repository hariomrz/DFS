<?php
require_once ROOT_PATH . 'user/application/modules/auth/models/Auth_model.php';

class Emailauth_model extends Auth_model {

    public function __construct() {
        parent::__construct();
    }

    public function check_email_otp($post_data) {
        $otp_code = $post_data['otp'];
        $email = $post_data['email'];
        
        $condition = array("phone_no" => $email, "otp_code" => $otp_code);
        $this->load->model("auth/Auth_nosql_model");
        $otp_record = $this->Auth_nosql_model->select_one_nosql(MANAGE_OTP, $condition);
        if (!empty($otp_record)) {
            if(isset($otp_record['user_detail']) && !empty($otp_record['user_detail'])){
                $user_record = (array)$otp_record['user_detail'];
            }else{
                $user_record = $this->db->select("user_id,user_unique_id,referral_code,phone_no,email,status,IFNULL(user_name,'') AS user_name,IFNULL(facebook_id,'') as facebook_id,IFNULL(google_id,'') as google_id,password,bs_status")
                    ->from(USER)
                    ->where("email", $email)
                    ->limit(1)
                    ->get()
                    ->row_array();
            }
        } else {
            return array("status" => 0, "message" => $this->lang->line('email_verified_failed'));
        }

        $created_date = $otp_record['updated_at'];
        $now = strtotime($created_date);
        $time = strtotime($created_date . ' +15 minutes');
        if ($otp_code == $otp_record['otp_code'] && $time > $now) {
            if ((isset($post_data["facebook_id"]) && !empty($post_data["facebook_id"])) || (isset($post_data["google_id"]) && !empty($post_data["google_id"]))) {
                $result = $this->validate_user_social_id($post_data, $user_record);
                if (!$result) {
                    return array("status" => 0, "message" => $this->lang->line('email_already_attached_to_other'));
                }
            }

            $user_id = $user_record['user_id'];
            $email = $user_record['email'];
            //valid code
            $updateData = array();
            $updateData["blocked_date"] = NULL;
            $updateData["new_email_requested"] = NULL;
            $updateData["new_email_key"] = NULL;
            $updateData["otp_attempt_count"] = 0;
            $updateData["email_verified"] = 1;
            $updateData["kyc_date"] = format_date();
            if ($user_record['status'] == 2) {
                $updateData["status"] = 1;
            }

            if (isset($post_data["facebook_id"]) && !empty($post_data["facebook_id"])) {
                $updateData['facebook_id'] = $post_data["facebook_id"];
            } else if (isset($post_data["google_id"]) && !empty($post_data["google_id"])) {
                $updateData['google_id'] = $post_data["google_id"];
            }
            $updateData['last_login'] = $created_date;
            $updateData['last_ip'] = get_user_ip_address();
            $this->db->where('user_id', $user_id);
            $this->db->update(USER, $updateData);

            //delete otp record
            $this->Auth_nosql_model->delete_nosql(MANAGE_OTP, array("phone_no" => $email));

            $user_record['email_verified'] = 1;
            return array("status" => 1, "data" => $user_record);
        } else {
            return array("status" => 0, "message" => $this->lang->line('phone_verified_failed'));
        }
    }

    public function update_user_attempt_count($post_data){

        if(WRONG_OTP_LIMIT > 0){
            $user_info = $this->get_single_row("user_id,blocked_date,otp_attempt_count",USER,array('email' => $post_data['email']));
            if(!empty($user_info)){
                $user_info['otp_attempt_count'] = $user_info['otp_attempt_count'] + 1;
                $this->db->set('otp_attempt_count', 'otp_attempt_count+1', FALSE);
                if($user_info['otp_attempt_count'] >= WRONG_OTP_LIMIT){
                    $this->db->set('blocked_date', format_date());
                }
                $this->db->where('user_id', $user_info['user_id']);
                $this->db->update(USER);
            }
        }

        return true;
    }

    public function reset_user_attempt_count($user_info){

        if(WRONG_OTP_LIMIT > 0 && !empty($user_info)){
            $this->db->set('otp_attempt_count', '0');
            $this->db->set('blocked_date', NULL);
            $this->db->where('user_id', $user_info['user_id']);
            $this->db->update(USER);
        }

        return true;
    }

    public function update_social_data($email, $social_data){
        if(!empty($email))
            $this->db->update(USER,$social_data,['email'=>$email]);
        return true;
    }
}