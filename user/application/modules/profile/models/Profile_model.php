<?php

class Profile_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * for update user data
     * @param int $user_id
     * @param array $data
     * @return boolean
     */
    public function update_user($user_id, $data) {
        return $this->db->where('user_id', $user_id)->update(USER, $data);
    }

    /**
     * @method get_all_country
     * @uses to get all country list
     * ** */
    public function get_country_list() {
        $result = $this->db->select("master_country_id,country_name,abbr,phonecode")
                ->order_by('country_name,abbr', 'ASC')
                ->get(MASTER_COUNTRY)
                ->result_array();
        return $result ? $result : array();
    }

    public function get_state_list_by_country_id($master_country_id) {
        $result = $this->db->select("master_state_id,name as state_name")
                ->where('master_country_id', $master_country_id)
                ->order_by('name', 'ASC')
                ->get(MASTER_STATE)
                ->result_array();
        return $result ? $result : array();
    }

    /**
     * for validate otp
     * @return array
     */
    public function check_otp_to_update_mobile() {
        $post_data = $this->input->post();
        $otp_code = $this->input->post('otp');
        $phone_no = $this->input->post('phone_no');

        $condition = array('phone_no' => $phone_no, 'otp_code' => $otp_code);
        $this->load->model("auth/Auth_nosql_model");
        $otp_record = $this->Auth_nosql_model->select_one_nosql(MANAGE_OTP, $condition);

        if (!empty($otp_record)) {
            if (isset($otp_record['user_detail']) && !empty($otp_record['user_detail'])) {
                $user_record = (array) $otp_record['user_detail'];
            } else {
                $user_record = $this->db->select("user_id,phone_no,status,IFNULL(user_name,'') AS user_name,IFNULL(email,'') AS email,IFNULL(facebook_id,'') as facebook_id,IFNULL(google_id,'') as google_id,phone_verfied")
                        ->from(USER)
                        ->where("phone_no", $phone_no)
                        ->limit(1)
                        ->get()
                        ->row_array();
            }
        } else {
            return array('status' => 0, 'message' => $this->lang->line('phone_verified_failed'));
        }

        $created_date = $otp_record['updated_at'];
        $now = strtotime($created_date);
        $time = strtotime($created_date . ' +15 minutes');
        if ($otp_code == $otp_record['otp_code'] && $time > $now) {

            $user_id = $user_record['user_id'];
            //$phone_no = $user_record['phone_no'];
            //valid code
            $updateData = array();
            $updateData["blocked_date"] = NULL;
            $updateData["otp_attempt_count"] = 0;
            $updateData["phone_verfied"] = 1;
            $updateData["kyc_date"] = format_date();
            $updateData["phone_no"] = $phone_no;
            if ($user_record['status'] == 2) {
                $updateData["status"] = 1;
            }

            $updateData['last_login'] = $created_date;
            $updateData['last_ip'] = get_user_ip_address();
            $this->db->where('user_id', $this->user_id);
            $this->db->update(USER, $updateData);

            // add user data in feed DB
            $user_sync_data = array();
            $user_sync_data['data'] = array(
                "Action" => "Signup",
                "UserID" => $this->user_id,
                "PhoneNumber" => $phone_no
            );
            $this->load->helper('queue_helper');
            add_data_in_queue($user_sync_data, 'user_sync');

            //delete otp record
            $this->Auth_nosql_model->delete_nosql(MANAGE_OTP, array("phone_no" => $phone_no));

            $user_record['phone_verfied'] = $updateData["phone_verfied"];
            return array('status' => 1, 'data' => $user_record, 'message' => $this->lang->line('phone_verified_success'));
        } else {
            return array('status' => 0, 'message' => $this->lang->line('phone_verified_failed'));
        }
    }

    /**
     * for validate otp
     * @return array
     */
    public function check_otp_to_update_email() {
        $post_data = $this->input->post();
        $otp_code = $this->input->post('otp');
        $email = $this->input->post('email');
        $email = strtolower($email);
        $condition = array('phone_no' => $email, 'otp_code' => $otp_code);
        $this->load->model("auth/Auth_nosql_model");
        $otp_record = $this->Auth_nosql_model->select_one_nosql(MANAGE_OTP, $condition);


        if (!empty($otp_record)) {
            if (isset($otp_record['user_detail']) && !empty($otp_record['user_detail'])) {
                $user_record = (array) $otp_record['user_detail'];
            } else {
                $user_record = $this->db->select("user_id,email,status,IFNULL(user_name,'') AS user_name,IFNULL(email,'') AS email,IFNULL(facebook_id,'') as facebook_id,IFNULL(google_id,'') as google_id,phone_verfied")
                        ->from(USER)
                        ->where("LOWER(email)", $email)
                        ->limit(1)
                        ->get()
                        ->row_array();
            }
        } else {
            return array('status' => 0, 'message' => $this->lang->line('email_verified_failed'));
        }

        $created_date = $otp_record['updated_at'];
        $now = strtotime($created_date);
        $time = strtotime($created_date . ' +15 minutes');
        if ($otp_code == $otp_record['otp_code'] && $time > $now) {

            $user_id = $user_record['user_id'];
            //valid code
            $updateData = array();
            $updateData["blocked_date"] = NULL;
            $updateData["otp_attempt_count"] = 0;
            $updateData["email_verified"] = 1;
            $updateData["kyc_date"] = format_date();
            $updateData["email"] = $email;
            if ($user_record['status'] == 2) {
                $updateData["status"] = 1;
            }

            $updateData['last_login'] = $created_date;
            $updateData['last_ip'] = get_user_ip_address();
            $this->db->where('user_id', $this->user_id);
            $this->db->update(USER, $updateData);

            // add user data in feed DB
            $user_sync_data = array();
            $user_sync_data['data'] = array(
                "Action" => "Signup",
                "UserID" => $this->user_id,
                "Email" => $email
            );
            $this->load->helper('queue_helper');
            add_data_in_queue($user_sync_data, 'user_sync');

            //update email for all session
            $nosql_data = array("email"=>$email);
            $this->Auth_nosql_model->update_all_nosql(ACTIVE_LOGIN,array("user_id"=>$this->user_id),$nosql_data);

            //delete otp record
            $this->Auth_nosql_model->delete_nosql(MANAGE_OTP, array("email" => $email));

            $user_record['email_verified'] = $updateData["email_verified"];
            return array('status' => 1, 'data' => $user_record, 'message' => $this->lang->line('email_verified_success'));
        } else {
            return array('status' => 0, 'message' => $this->lang->line('email_verified_failed'));
        }
    }

    function get_profile_detail() {
        $result = $this->db->select('U.user_id, U.user_unique_id, U.first_name, U.last_name, U.referral_code, U.is_referral_code_edited as is_rc_edit, U.user_name, U.dob, U.gender, U.email, IFNULL(U.image,"") as image, U.address, U.master_country_id, U.master_state_id, U.city, U.balance, U.phone_no,IFNULL(U.phone_verfied,0) as phone_verfied,U.pan_no,U.pan_image,IFNULL(U.pan_verified,0) as pan_verified,IFNULL(U.auto_pan_attempted,0) as auto_pan_attempted,U.status,U.is_notification, U.password, U.zip_code,U.added_date as member_since,U.new_temp_email,U.facebook_id,U.google_id,IFNULL(U.email_verified,0) as email_verified,U.phone_code,IFNULL(U.is_bank_verified,0) as is_bank_verified,IFNULL(U.auto_bank_attempted,0) as auto_bank_attempted,IFNULL(MC.country_name,"") as country_name,IFNULL(MS.name,"") as state_name,is_affiliate,signup_commission,deposit_commission,aadhar_status,U.wdl_status,IFNULL(U.gst_number,"") as gst_number')
                        ->from(USER . ' U')
                        ->join(MASTER_COUNTRY . ' MC', 'MC.master_country_id=U.master_country_id', 'LEFT')
                        ->join(MASTER_STATE . ' MS', 'MS.master_state_id=U.master_state_id', 'LEFT')
                        ->where('U.user_id', $this->user_id)
                        ->get()->row_array();

        return $result;
    }
    
    /** 
     * used to check user can edit referral code or not
     * @return boolean
     */
    function is_referral_code_edit() {
        $this->db->select('user_id');
        $this->db->from(USER);
        $this->db->where('user_id', $this->user_id);
        $this->db->where('is_referral_code_edited',0);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return TRUE;
        }
        return FALSE;
    }
    
    /** 
     * used to update referral code
     * @param string $referral_code
     * @return boolean
     */
    function update_referral_code($referral_code) {
        $data = array('referral_code' => $referral_code, 'is_referral_code_edited' => 1);               
        $this->db->where('user_id', $this->user_id);
        $this->db->update(USER, $data);                
        return true;
    }
    
    function check_already_refered($user_id, $friend_id) {
        $this->db->select('user_affiliate_history_id');
        $this->db->from(USER_AFFILIATE_HISTORY);
        $this->db->where('friend_id', $friend_id);
        if($user_id == 0) {
            $this->db->where('user_id !=', $user_id);
        } else {
            $this->db->where('user_id', $user_id);
        }
        $this->db->where('status', 1);
        $this->db->where_in('affiliate_type', array(1,19,20,21));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return TRUE;
        }
        return FALSE;
    } 

    /**
     * method to get all active avater at user end
     * @return list of avatar names
     */

    public function get_avatars(){
        $avatar = $this->db->select('id,name')
        ->from(AVATARS)
        ->where('is_default',0)
        ->where('status',1)
        ->get()->result_array();
        return $avatar;
    }
/**
     * update profile pucture when user select an avatar image
     * @return     true/false
     */
    public function update_profile_picture($image){
        $this->db->where('user_id', $this->user_id);
        $update = $this->db->update(USER, ['image'=>$image]); 
        return $update;
    }

/**
     * method to update user profile image.
     * @return     [array]
     */
    public function update_user_image($image){
        $update = $this->db->update(USER,['image'=>$image],['user_id'=>$this->user_id]);
        if($update){
            return true;
        }
        return false;
    }
    
    //affiliate module methods 

     /**
     * method to request to become an affiliate 
     *@param user_id
     */ 
    public function become_affiliate($data){
      
        $post_data['modified_date']     =format_date('today');
        $post_data['aff_request_date']  =format_date('today');
        $post_data['is_affiliate']      = 2;
        $post_data['user_affiliated_website']      =$data['web_url'];
        $post_data['expected_affiliated_user']      = $data['refer'];
        $where = ['user_id'=>  $data['user_id'],"is_systemuser"=>0];     
        $result = $this->db->update(USER,$post_data,$where);
        return $result;
    }

    /**
     * method to summary of affiliate like % commission , total signup, total commission etc 
     *@param user_id
     *@return array
     */
    public function get_affiliate_summary($user_id){
        $affiliate_summary = array();
        $affiliate_summary['is_affiliate'] = 0;
        $affiliate_summary['signup_commission'] = 0;
        $affiliate_summary['deposit_commission'] = 0;
        $affiliate_summary['total_signup'] = 0;
        $affiliate_summary['commission_amount'] = 0;
        $affiliate_summary['deposit_amount'] = 0;
        $affiliate_summary['commission_type'] = 'winning_amount';
        $affiliate_summary['site_rake_commission_total'] = 0;
        $affiliate_summary['site_rake_commission'] = 0;

        $user_info = $this->get_single_row('is_affiliate,signup_commission,deposit_commission,commission_type,referral_code,site_rake_commission',USER, array("user_id"=>$this->user_id,"is_systemuser"=>0));
        if(!empty($user_info)){
            if($user_info['commission_type']==0)
            {
                $affiliate_summary['commission_type'] =  'real_amount';
            }else{
                $affiliate_summary['commission_type'] =  'winning_amount';
            }

            $commission = $this->db->select("SUM(real_amount+winning_amount) as total")
                                ->from(ORDER)
                                ->where("type",0)
                                ->where("user_id",$this->user_id)
                                ->where_in("source",[320,321])
                                ->group_start()
                                ->where("winning_amount >",0)
                                ->or_where("real_amount >",0)
                                ->group_end()
                                ->get()
                                ->row_array();
            $affiliate_summary['commission_amount'] = $commission['total'];


            $affiliate_users = $this->get_all_table_data(USER_AFFILIATE_HISTORY, 'friend_id', array('user_id' => $this->user_id,"affiliate_type"=>6,"is_affiliate"=>1));
            $affiliated_user_ids = array_column($affiliate_users,'friend_id');
            $affiliate_summary['total_signup'] = count($affiliated_user_ids);
            if(!empty($affiliated_user_ids)){
                $deposit = $this->db->select('sum(real_amount) as deposit')
                                ->from(ORDER.' AS O')
                                ->where_in("user_id",$affiliated_user_ids)
                                ->where("source",7)
                                ->where("status",1)
                                ->get()->row_array();
                if(!empty($deposit)){
                    $affiliate_summary['deposit_amount'] = $deposit['deposit'];
                }
            }

            $commission = $this->db->select("SUM(real_amount+winning_amount) as total_com")
                                ->from(ORDER)
                                ->where("type",0)
                                ->where("user_id",$this->user_id)
                                ->where_in("source",[556])
                                ->group_start()
                                ->where("winning_amount >",0)
                                ->or_where("real_amount >",0)
                                ->group_end()
                                ->get()
                                ->row_array();
            $affiliate_summary['site_rake_commission_total'] = $commission['total_com'];

            $affiliate_summary['is_affiliate'] = $user_info['is_affiliate'];
            $affiliate_summary['signup_commission'] = $user_info['signup_commission'];
            $affiliate_summary['deposit_commission'] = $user_info['deposit_commission'];
            $affiliate_summary['site_rake_commission'] = $user_info['site_rake_commission'];
            $affiliate_summary['referral_code'] = $user_info['referral_code'];
        }
        return $affiliate_summary;
    }

    /**
     * method to transaction of affiliate signup and deposited by other users affiliated to this user. 
     *@param user_id
     *@return array
     */
    public function get_affiliate_transactions($user_id){
        $sort_field	= 'date_added';
        $sort_order	= 'DESC';
        $limit		= 50;
        $page		= 0;
        $post_data = $this->input->post();
        if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('user_name','date_added','friend_id','friend_name','friend_amount','friend_order_id','deposit_comission','signup_commission')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

        $offset	= $limit * $page;
        $friend_id = "JSON_UNQUOTE(JSON_EXTRACT(O.custom_data, '$.user_id'))";
        $result = $this->db->select("IFNULL($friend_id,'0') as friend_id,O.custom_data,if(source =320,(O.winning_amount+O.real_amount),0) as signup_commission, if(source=321,(O.winning_amount+O.real_amount),0) as deposit_comission,O.date_added,O.reason,O.source,if(source=556,(O.winning_amount+O.real_amount),0) as site_rake_comission,O.custom_data")
                ->from(ORDER.' AS O')
                ->where('O.type','0')
                ->where('O.user_id',$this->user_id)
                ->where_in('source',[320,321,556])
                ->group_start()
                ->where("winning_amount >",0)
                ->or_where("real_amount >",0)
                ->group_end()
                ->order_by($sort_field, $sort_order);

        if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
         {
             $this->db->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
         }

        $tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
        $total = $temp_q->num_rows();
        
        $result = $this->db->limit($limit,$offset)->get();
        $result	= $result->result_array();
        return array('result'=>$result,'total'=>$total);
    }

    /** 
     * used for check duplicate account
     * @param int ac_number, ifsc_code
     * @return array
     */
    public function check_duplicate_account($user_bank_details)
    {
        $this->db->select('UBD.user_id, U.is_bank_verified')
                ->from(USER_BANK_DETAIL ." AS UBD")
                ->join(USER ." AS U", "UBD.user_id=U.user_id", "INNER")
                ->where('UBD.ac_number',$user_bank_details["ac_number"])
                ->where('UBD.ifsc_code',$user_bank_details["ifsc_code"])
                ->where('U.is_bank_verified',1);

        $record = $this->db->get()->result_array();
        // echo "<pre>";print_r($record);
        // die;

        if (!empty($record))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    public function get_subscription_details()
    {
        $result = $this->db->select("US.subscription_id,US.user_id,US.type,US.start_date,US.expiry_date,S.name,S.amount,S.coins,
        (CASE 
        WHEN US.type=1 THEN S.android_id
        WHEN US.type=2 THEN S.ios_id END) as product_id,S.duration")
        ->from(USER_SUBSCRIPTION.' US')
        ->join(SUBSCRIPTION.' S','S.subscription_id = US.subscription_id','INNER')
        ->where('US.user_id',$this->user_id)
        ->where('US.status','1')
        ->get()->row_array();
        if(!empty($result)) $result['package_name'] = $this->app_config['allow_subscription']['custom_data']['package_name'];
        return $result;
    }
    
    public function check_duplicate_crypto_wallet($crypto_wallet_details)
    {
        $this->db->select('UBD.user_id, U.is_bank_verified')
                ->from(USER_BANK_DETAIL ." AS UBD")
                ->join(USER ." AS U", "UBD.user_id=U.user_id", "INNER")
                ->where('UBD.user_id',$this->user_id);
        $record = $this->db->get()->result_array();
// echo $this->db->last_query();exit;        
        if (!empty($record)) return TRUE;
        return FALSE;
    }

    public function save_crypto_wallet($post_values,$is_duplicate)
    {
        if($is_duplicate)
        {
            unset($post_values['added_date']);
            $this->db->update(USER_BANK_DETAIL, $post_values,['user_id'=>$this->user_id]);
            if($this->db->affected_rows()) return true;
            return false;
        }else{
            $this->db->insert(USER_BANK_DETAIL, $post_values);
            return $this->db->insert_id();
        }
        
    }

    public function get_users_by_ids($user_ids)
    {
        if(empty($user_ids))
        {
            return array();
        }
        $sql = $this->db->select('user_id,email,phone_no,phone_code,user_name')
                        ->from(USER)
                        ->where_in('user_id', $user_ids)
                        ->get();
        $result = $sql->result_array();
        return $result;
    }

}
