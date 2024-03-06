<?php
class Auth_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * for generate user unique key
     * @return string
     */
    public function _generate_key() {
        $this->load->helper('security');
        do {
            $salt = do_hash(time() . mt_rand());
            $new_key = substr($salt, 0, 10);
        }

        // Already in the DB? Fail. Try again
        while (self::_key_exists($new_key));

        return $new_key;
    }

    /**
     * to check key exist or not
     * @param string $key
     * @return boolean
     */
    private function _key_exists($key) {
        $this->db->select('user_id');
        $this->db->where('user_unique_id', $key);
        $this->db->limit(1);
        $query = $this->db->get(USER);
        $num = $query->num_rows();
        if($num > 0){
            return true;
        }
        return false;
    }


    /**
     * used for user registration
     * @param array $post
     * @return int
     */
    public function registration($post) {
        
        $this->db->insert(USER, $post);
        return $this->db->insert_id();
    }

    /**
     * for generate user referral code
     * @return string
     */
    public function _generate_referral_code() {
        $this->load->helper('security');

        do {
            $salt = do_hash(time() . mt_rand());
            $new_key = substr($salt, 0, 6);
            $new_key = strtoupper($new_key);
        }

        // Already in the DB? Fail. Try again
        while (self::_referral_code_exists($new_key));

        return $new_key;
    }

    /**
     * to check referral code exist or not
     * @param string $key
     * @return boolean
     */
     function _referral_code_exists($key) {
        $this->db->select('user_id');
        $this->db->where('referral_code', $key);
        $this->db->limit(1);
        $query = $this->db->get(USER);
        $num = $query->num_rows();
        if($num > 0){
            return true;
        }
        return false;
    }

    /**
     * used for validate user session key
     * @param string $key
     * @return array
     */
    public function check_user_key($key) {
        $sql = $this->db->select("U.user_id,user_unique_id,date_created,first_name,last_name,email,user_name,status,U.bonus_balance,U.winning_balance,U.balance,U.referral_code,AL.role,AL.device_type,U.point_balance,AL.device_id,U.language,U.phone_no,IFNULL(U.bs_status,'') as bs_status")
                ->from(ACTIVE_LOGIN . ' AS AL')
                ->join(USER . ' AS U', 'U.user_id = AL.user_id')
                ->where("key", $key)
                ->limit(1)
                ->get();
        $result = $sql->row_array();
        return ($result) ? $result : array();
    }

    /**
     * for validate otp
     * @return array
     */
    public function check_otp() {
        $post_data = $this->input->post();
        $otp_code = $this->input->post('otp');
        $phone_no = $this->input->post('phone_no');
        $email = $this->input->post('email');
        $install_date = $this->input->post('install_date');
        $device_type = $this->input->post('device_type');

        $condition = array('phone_no' => $phone_no, 'otp_code' => $otp_code);
        $this->load->model("auth/Auth_nosql_model");
        $otp_record = $this->Auth_nosql_model->select_one_nosql(MANAGE_OTP, $condition);

        if (!empty($otp_record)) {
            if(isset($otp_record['user_detail']) && !empty($otp_record['user_detail'])){
                $user_record = (array)$otp_record['user_detail'];
            }else{
                $user_record = $this->db->select("user_id,user_unique_id,referral_code,phone_no,status,IFNULL(user_name,'') AS user_name,IFNULL(email,'') AS email,IFNULL(facebook_id,'') as facebook_id,IFNULL(google_id,'') as google_id,phone_verfied,bs_status,IFNULL(image,'') as image,IFNULL(master_state_id,'') as master_state_id",FALSE)
                    ->from(USER)
                    ->where("phone_no", $phone_no)
                    ->limit(1)
                    ->get()
                    ->row_array();
            }
        } else {
            if(WRONG_OTP_LIMIT > 0){
                $user_info = $this->get_single_row("user_id,blocked_date,otp_attempt_count",USER,array('phone_no' => $phone_no));
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
            return array('status' => 0, 'message' => $this->lang->line('phone_verified_failed'));
        }

        $created_date = $otp_record['updated_at'];
        $now = strtotime($created_date);
        $time = strtotime($created_date . ' +15 minutes');
        if ($otp_code == $otp_record['otp_code'] && $time > $now) {
            if (!empty($post_data["facebook_id"]) || !empty($post_data["google_id"])) {
                $result = $this->validate_user_social_id($post_data, $user_record);
                if (!$result) {
                    return array('status' => 0, 'message' => $this->lang->line('mobile_already_attached_to_other'));
                }
            }

            $user_id = $user_record['user_id'];
            $phone_no = $user_record['phone_no'];
            //valid code
            $updateData = array();
            $updateData["blocked_date"] = NULL;
            $updateData["otp_attempt_count"] = 0;
            $updateData["phone_verfied"] = 1;
            $updateData["kyc_date"] = format_date();
            if ($user_record['status'] == 2) {
                $updateData["status"] = 1;
            }
            if ($email) {
                $updateData = array('status' => '1', 'new_email_requested' => NULL, 'new_email_key' => NULL);
            }

            if (!empty($post_data["facebook_id"])) {
                $updateData['facebook_id'] = $post_data["facebook_id"];
            } else if (!empty($post_data["google_id"])) {
                $updateData['google_id'] = $post_data["google_id"];
            }
            $updateData['last_login'] = $created_date;
            $updateData['last_ip'] = get_user_ip_address();

            $install_dates = $this->get_single_row("IFNULL(android_install_date,'') as android_install_date,IFNULL(ios_install_date,'') as ios_install_date",USER,['user_id'=>$user_id]);
            if(empty($install_dates['android_install_date']) && $device_type==1){
            $updateData['android_install_date'] = $post_data['install_date'];
            }
            if(empty($install_dates['ios_install_date']) && $device_type==2){
            $updateData['ios_install_date'] = $post_data['install_date'];
            }
            
            $this->db->where('user_id', $user_id);
            $this->db->update(USER, $updateData);

            //delete otp record
            $this->Auth_nosql_model->delete_nosql(MANAGE_OTP, array("phone_no" => $phone_no));

            $user_record['phone_verfied'] = $updateData["phone_verfied"];
            return array('status' => 1, 'data' => $user_record);
        } else {
            return array('status' => 0, 'message' => $this->lang->line('phone_verified_failed'));
        }
    }

    /**
     * for validate user social ids
     * @param array $data
     * @param array $user_data
     * @return boolean
     */
    public function validate_user_social_id($data, $user_data) {

        $valid = TRUE;
        if (!empty($user_data['facebook_id']) && !empty($data['facebook_id']) && $data['facebook_id'] != $user_data['facebook_id']) {
            $valid = FALSE;
        } else if (!empty($user_data['google_id']) && !empty($data['google_id']) && $data['google_id'] != $user_data['google_id']) {
            $valid = FALSE;
        }

        return $valid;
    }

    /**
     * [generate_active_login_key description]
     * @MethodName generate_active_login_key
     * @Summary This genrate new key and insert in database
     * @param      [int]  [User Id]
     * @return     [key]
     */
    public function generate_active_login_key($user_data, $device_type = "1", $device_id = "0") {
        $key = random_string('unique');
        $user_id = $user_data['user_id'];
        $insert_data = array(
            'key' => $key,
            'role' => 1,
            'user_id' => $user_id,
            'device_type' => $device_type,
            'date_created' => format_date()
        );

        if (!empty($device_id)) {

            $this->db->where('device_id', $device_id)->delete(ACTIVE_LOGIN);
            $insert_data['device_id'] = $device_id;
        }

        $this->db->insert(ACTIVE_LOGIN, $insert_data);

        unset($insert_data['key']);
        $this->load->model("auth/Auth_nosql_model");
        $insert_data[AUTH_KEY] = $key;
        $insert_data["user_unique_id"] = $user_data['user_unique_id'];
        $insert_data["email"] = $user_data['email'];
        $insert_data["user_name"] = $user_data['user_name'];
        $insert_data["referral_code"] = $user_data['referral_code'];
        $insert_data["phone_no"] = $user_data['phone_no'];
        $insert_data["bs_status"] = $user_data['bs_status'];
        $this->Auth_nosql_model->insert_nosql(ACTIVE_LOGIN, $insert_data);
        return $key;
    }

    /**
     * for update user data
     * @param int $user_id
     * @param array $data
     * @param array $social_data
     * @return boolean
     */
    public function update_user_data($user_id, $social_data, $data = array()) {
        $data['last_login'] = format_date();
        $data['last_ip'] = get_user_ip_address();
        if (!empty($social_data["facebook_id"])) {
            $data['facebook_id'] = $social_data["facebook_id"];
        } elseif (!empty($social_data["google_id"])) {
            $data['google_id'] = $social_data["google_id"];
        }
        return $this->db->where('user_id', $user_id)->update(USER, $data);
    }

    /**
     * [get_user_details description]
     * @MethodName get_user_details 
     * @Summary This function used for retrive user details
     * @param      [varchar]  [Login key]
     * @return     [array]
     */
    public function get_user_details($data) {
        $sql = $this->db->select("U.first_name,U.last_name,U.user_id,U.user_unique_id,U.email,U.user_name,U.dob,U.password,U.phone_no,U.phone_code,U.master_country_id,IFNULL(U.image,'') AS image,
									U.facebook_id,U.google_id,U.balance,U.bonus_balance,U.referral_code,U.language,U.status,U.pan_verified,U.pan_no,U.phone_verfied,
									U.status_reason,U.new_password_key,U.new_password_requested,U.last_login,U.last_ip,U.added_date,U.modified_date,U.point_balance,MC.phonecode")
                ->from(USER . ' AS U')
                ->join(MASTER_COUNTRY . ' AS MC', "U.master_country_id = MC.master_country_id", "LEFT")
                ->where($data)
                ->limit(1)
                ->get();
        $result = $sql->row_array();
        return ($result) ? $result : array();
    }

    public function remove_active_login($key) {
        $this->load->model("auth/Auth_nosql_model");
        $this->Auth_nosql_model->delete_nosql(ACTIVE_LOGIN, array(AUTH_KEY => $key));
        $this->db->where(config_item('rest_key_column'), $key)->delete(config_item('rest_keys_table'));
    }

    /*     * ************** OLD Apis ************** */

    public function add_user_analytics($post) {
        $this->db->insert(ANALYTICS_USER_LOGIN, $post);
        return $this->db->insert_id();
    }

    /**
     * [check_user_key description]
     * @MethodName check_user_key_for_admin
     * @Summary This function used for check user key exist
     * @param      [varchar]  [Login key]
     * @return     [array]
     */
    public function check_user_key_for_admin($key = FALSE) {
        $sql = $this->db->select("A.admin_id,AL.role, AL.date_created")
                ->from(ACTIVE_LOGIN . ' AS AL')
                ->join(ADMIN . ' AS A', 'A.admin_id = AL.user_id')
                ->where("key", $key)
                ->limit(1)
                ->get();
        $result = $sql->row_array();
        return ($result) ? $result : array();
    }

    public function get_new_email_key($new_email_key) {
        $sql = $this->db->select("user_id,user_unique_id,new_email_requested,status,email_verified,first_name,last_name,user_name,email,phone_no")                
                ->from(USER)
                ->where("new_email_key", $new_email_key)
                ->limit(1)
                ->get();
        $result = $sql->row_array();
        return ($result) ? $result : array();
    }
   
    public function get_new_password_key($new_password_key) {
        $sql = $this->db->select("user_id,user_unique_id,new_password_requested,status,email,user_name")
                ->from(USER)
                ->where("new_password_key", $new_password_key)
                ->limit(1)
                ->get();
        $result = $sql->row_array();
        return ($result) ? $result : array();
    }

    public function check_user_account_status($where_arr = array()){
        if(empty($where_arr)){
            return false;
        }
        $return = array();
        $current_date = format_date();
        $current_date_time = strtotime($current_date." -24 hours");
        $user_info = $this->get_single_row("user_id,blocked_date,otp_attempt_count",USER,$where_arr);
        if(!empty($user_info) && strtotime($user_info['blocked_date']) > $current_date_time){
            $return = array("user_id"=>$user_info['user_id'],"blocked_date"=>$user_info['blocked_date'],"is_blocked"=>"1");
        }
        return $return;
    }

    /**
     * used to insert track record
     * @param array $post
     * @return int
     */
    public function insert_track_record($data)
    {
        $this->db->insert(USER_TRACK, $data);
        return $this->db->insert_id();
    }

    /**
     * method to get a random avatar image fro default 10
     * @return     [array]
     */
    public function get_first_rendom_avatar(){
        $avatar = $this->db->select('name')
        ->from(AVATARS)
        ->where('is_default',1)
        ->where('status',1)
        ->order_by('rand()')->limit(1)->get()->row_array();
        return $avatar;
    }

    /**
    * Function used for get banned state list
    * @param void
    * @return array
    */
    public function get_banned_state_list()
    {
        $this->db->select("BS.master_state_id as state_id,MS.name,MS.pos_code", FALSE);
        $this->db->from(BANNED_STATE." AS BS");
        $this->db->join(MASTER_STATE." AS MS","MS.master_state_id = BS.master_state_id","INNER");
        $this->db->order_by('BS.id','ASC');
        $result = $this->db->get()->result_array();
        return $result;
    }
    
    public function add_short_url_data($data)
    {
        $this->db->insert(SHORTENED_URLS,$data);
        $insertId = $this->db->insert_id();
        if($insertId)
        {
            $result = $this->db->select('short_id')->from(SHORTENED_URLS)->where('id',$insertId)->get()->row_array();
            return $result['short_id'];
        }
        return array();
    }

    /**
     * used to get lobby banner list
     * @param array $post_data
     * @return array
    */
    public function get_lobby_banner_list($post_data) {
        $column_key = $this->lang_abbr."_name";
        $current_date = format_date();
        $close_date = $current_date;
        $sports_where = array(0,$post_data['sports_id']);
        $this->db->select("BM.banner_type_id,IF(BM.".$column_key." IS NULL or BM.".$column_key." = '', BM.name, BM.".$column_key.") as name,BM.target_url,BM.image,(CASE WHEN BM.banner_type_id=1 THEN BM.scheduled_date ELSE '".$close_date."' END) as schedule_date,BM.collection_master_id,BM.game_type_id", FALSE)
                ->from(BANNER_MANAGEMENT . " AS BM")
                ->join(BANNER_TYPE . " as BT", "BT.banner_type_id = BM.banner_type_id AND BM.banner_type_id NOT IN(5,7,8)", "INNER")
                ->where("BM.is_deleted", "0")
                ->where("BM.status", "1")
                ->group_by("BM.banner_id")
                ->having("schedule_date >=",$current_date);

        $this->db->order_by("BM.banner_type_id != " . LOBBY_WHYUS_BANNER_TYPE_ID, NULL, FALSE);
        $this->db->order_by("BM.banner_type_id");
        $this->db->order_by("BM.banner_id", "ASC");
        return $this->db->get()->result_array();
    }

    /**
     * used for get lobby banner referral amount
     * @param 
     * @return array
     */
    public function get_lobby_banner_referral_data() {
        $lobby_data = array();
        $lobby_data['2'] = array("amount" => 0, "currency_type" => $this->app_config['currency_abbr']['key_value']);
        $lobby_data['3'] = array("amount" => 0, "currency_type" => $this->app_config['currency_abbr']['key_value']);

        $this->db->select("affiliate_master_id,real_amount,bonus_amount,coin_amount,user_real,user_bonus,user_coin");
        $this->db->from(AFFILIATE_MASTER);
        $this->db->where_in("affiliate_master_id", array(LOBBY_REFER_BANNER_AFF_ID, LOBBY_DEPOSIT_BANNER_AFF_ID));
        $result = $this->db->get()->result_array();

        $result = array_column($result, NULL, "affiliate_master_id");
        $refer_data = isset($result[LOBBY_REFER_BANNER_AFF_ID]) ? $result[LOBBY_REFER_BANNER_AFF_ID] : array();
        if (!empty($refer_data)) {
            $result = array('real_amount' => $refer_data['real_amount'], 'bonus_amount' => $refer_data['bonus_amount'], 'coin_amount' => $refer_data['coin_amount']);
            $max_value = array_keys($result, max($result));
            $max_value_key = $max_value[0];
            $lobby_data['2']['amount'] = $refer_data[$max_value_key];
            if($max_value_key == 'bonus_amount') {
                $lobby_data['2']['currency_type'] = "Bonus";
            } else if($max_value_key == 'coin_amount') {
                $lobby_data['2']['currency_type'] = "Coin";
            }
        }

        $deposit_data = isset($result[LOBBY_DEPOSIT_BANNER_AFF_ID]) ? $result[LOBBY_DEPOSIT_BANNER_AFF_ID] : array();
        if (!empty($deposit_data)) {
            if (isset($deposit_data['user_real']) && $deposit_data['user_real'] > 0) {
                $lobby_data['3']['amount'] = $deposit_data['user_real'];
            } else if (isset($deposit_data['user_bonus']) && $deposit_data['user_bonus'] > 0) {
                $lobby_data['3']['amount'] = $deposit_data['user_bonus'];
                $lobby_data['3']['currency_type'] = "Bonus";
            } else if (isset($deposit_data['user_coin']) && $deposit_data['user_coin'] > 0) {
                $lobby_data['3']['amount'] = $deposit_data['user_coin'];
                $lobby_data['3']['currency_type'] = "Coin";
            }
        }

        return $lobby_data;
    }

    /**
     * used to get lobby banner list
     * @param array $post_data
     * @return array
    */
    public function get_hub_banner_list() {
        $this->db->select("BM.banner_type_id,BM.target_url,BM.image,BM.name", FALSE)
                ->from(BANNER_MANAGEMENT . " AS BM")
                ->join(BANNER_TYPE . " as BT", "BT.banner_type_id = BM.banner_type_id AND BM.banner_type_id IN(7,8)", "INNER")
                ->where("BM.is_deleted", "0")
                ->where("BM.status", "1")
                ->group_by("BM.banner_id");

        $this->db->order_by("BM.banner_type_id");
        $this->db->order_by("BM.banner_id", "ASC");
        return $this->db->get()->result_array();
    }

    public function get_country_state_ids()
    {
       return $this->db->select('MC.master_country_id as country_id,MS.master_state_id as state_id,MS.pos_code as state_code,MC.pos_code as country_code')
                ->from(MASTER_COUNTRY. " MC")
                ->join(MASTER_STATE. " MS","MS.master_country_id=MC.master_country_id")
                ->get()->result_array();

    }   

      /**
    * Function used for get banned state list
    * @param void
    * @return array
    */
    public function get_featured_league_list()
    {
        $this->db->select("FL.sports_id,FL.name,FL.dfs_id,FL.pickem_id", FALSE);
        $this->db->from(FEATURED_LEAGUE." AS FL"); 
        $this->db->where('FL.dfs_id > 0 or FL.pickem_id > 0')  
        ->group_by("FL.sports_id")
        ->group_by("FL.league_uid");
        $result = $this->db->get()->result_array();
        return $result;
    }

    
}