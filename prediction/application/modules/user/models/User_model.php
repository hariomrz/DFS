<?php

/**
 * Used for return user db records
 * @package     User
 * @category    User
 */
class User_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        $this->user_db = $this->load->database('user_db', TRUE);
    }

    function __destruct() {
        $this->user_db->close();
    }

    /**
     * used for validate user session key
     * @param string $key
     * @return array
     */
    public function check_user_key($key) {
        $sql = $this->user_db->select("U.user_id,user_unique_id,date_created,first_name,last_name,email,user_name,status,U.bonus_balance,U.winning_balance,U.balance,U.referral_code,AL.role,AL.device_type,U.point_balance,AL.device_id,U.language,U.phone_no")
                ->from(ACTIVE_LOGIN . ' AS AL')
                ->join(USER . ' AS U', 'U.user_id = AL.user_id')
                ->where("AL.key", $key)
                ->limit(1)
                ->get();
        $result = $sql->row_array();
        return ($result) ? $result : array();
    }

    /**
     * used for get user balance
     * @param int $user_id
     * @return array
     */
    public function get_user_balance($user_id) {
        if (!$user_id)
            return false;

        $user_balance_cache_key = 'user_balance_' . $user_id;
        $user_balance = $this->get_cache_data($user_balance_cache_key);
    
        if(empty($user_balance))
        {
            $result = $this->user_db->select("user_id,balance,bonus_balance,winning_balance,point_balance")
            ->from(USER)
            ->where("user_id", $user_id)
            ->limit(1)
            ->get()->row_array();

            $user_balance = array(
                "bonus_amount" => $result["bonus_balance"] ? $result["bonus_balance"] : 0,
                "real_amount" => $result["balance"] ? $result["balance"] : 0,
                "winning_amount" => $result["winning_balance"] ? $result["winning_balance"] : 0,
                "point_balance" => $result["point_balance"] ? $result["point_balance"] : 0
            );

            $this->set_cache_data($user_balance_cache_key, $user_balance, REDIS_2_HOUR);
        }

        return $user_balance;
    }

    /**
     * Used for generate order unique id
     * @return string
     */
    public function _generate_order_key() {
        $this->load->helper('security');
        $salt = do_hash(time() . mt_rand());
        $new_key = substr("o".$salt, 0, 20);
        return $new_key;
    }

    /**
     * Used for check order unique id exist or not
     * @param string $key
     * @return int
     */
    private function _order_key_exists($key) {
        $this->user_db->select('order_id');
        $this->user_db->where('order_unique_id', $key);
        $this->user_db->limit(1);
        $query = $this->user_db->get(ORDER);
        $num = $query->num_rows();
        if ($num > 0) {
            return true;
        }
        return false;
    }

    /**
     * Used for save data in oder table on contest join
     * @param array $data_arr
     * @return array
     */
    public function withdraw($data_arr) {
        $current_date = format_date();
        $amount = $data_arr["amount"];
        $order_data = array();
        $order_data["user_id"] = $data_arr['user_id'];
        $order_data["source"] = $data_arr['source'];
        $order_data["source_id"] = $data_arr['source_id'];
        $order_data["type"] = 1;
        $order_data["date_added"] = $current_date;
        $order_data["modified_date"] = $current_date;
        $order_data["plateform"] = 1;
        $order_data["season_type"] = 1;
        $order_data["status"] = 1;
        $order_data["real_amount"] = 0;
        $order_data["bonus_amount"] = 0;
        $order_data["winning_amount"] = 0;
        $order_data["points"] = 0;
        $order_data["withdraw_method"] = 0;
        $order_data["reason"] = !empty($data_arr['reason']) ? $data_arr['reason'] : '';

        $user_balance = $this->get_user_balance($data_arr["user_id"]);
        //bonus_amount,real_amount,winning_amount,point_balance
        if ($data_arr['cash_type'] != 3 && ($user_balance["real_amount"] + $user_balance["bonus_amount"] + $user_balance["winning_amount"] < $amount)) {
            return array("result" => "0", "message" => "Insufficient amount in winnings!");
        }

        if ($data_arr['cash_type'] == 3 && $user_balance["point_balance"] < $amount) {
            return array("result" => "0", "message" => "Insufficient Coins");
        }

        // Use both cash (bouns+real) only for join game. 
        $max_bonus_allowed = MAX_BONUS_PERCEN_USE;
        if (isset($data_arr['max_bonus_allowed']) && $data_arr['max_bonus_allowed'] != "") {
            $max_bonus_allowed = $data_arr['max_bonus_allowed'];
        }
        $max_bouns = ($amount * $max_bonus_allowed) / 100;
        // Deduct Max 10% of entry fee from bonus amount.
        $order_data["bonus_amount"] = $max_bouns;
        if ($max_bouns > $user_balance["bonus_amount"]) {
            $order_data["bonus_amount"] = $user_balance["bonus_amount"];
        }
        
        if($order_data["source"] == 1 && MAX_CONTEST_BONUS > 0 && $order_data["bonus_amount"] > MAX_CONTEST_BONUS) { // Game join max bonus cap check
            $order_data["bonus_amount"] = MAX_CONTEST_BONUS;
        }
            
        $remain_amt = $amount - $order_data["bonus_amount"];
        // Deduct reamining amount from real amount.
        $order_data["real_amount"] = $remain_amt;
        if ($remain_amt > $user_balance["real_amount"]) {
            $order_data["real_amount"] = $user_balance["real_amount"];
        }
        $remain_amt = $remain_amt - $order_data["real_amount"];
        if ($remain_amt > $user_balance["winning_amount"]) {
            return array("result" => "0", "message" => "Insufficient amount in winnings!");
        }

        // Deduct Remaining amount from winning amount
        $order_data["winning_amount"] = $remain_amt;

        //calculate profit
        if(isset($data_arr['source']) && $data_arr['source'] == 1){
            $site_rake = DEFAULT_SITE_RAKE;
            if (isset($data_arr['site_rake']) && $data_arr['site_rake'] != "") {
                $site_rake = $data_arr['site_rake'];
            }
            $total_real_entry = $order_data["real_amount"] + $order_data["winning_amount"];
            $profit = number_format(($total_real_entry * $site_rake)/100,2,'.','');
            $order_data['custom_data'] = json_encode(array('profit'=>$profit));
        }

        $order_data['order_unique_id'] = $this->_generate_order_key();
        $this->user_db->insert(ORDER, $order_data);
        $order_id = $this->user_db->insert_id();
        if ($order_id) {
            //deduct user balance after contest join
            $this->user_db->where('user_id', $data_arr['user_id']);
            $this->user_db->set('balance', 'balance - ' . $order_data["real_amount"], FALSE);
            $this->user_db->set('bonus_balance', 'bonus_balance - ' . $order_data["bonus_amount"], FALSE);
            $this->user_db->set('winning_balance', 'winning_balance - ' . $order_data["winning_amount"], FALSE);
            $this->user_db->set('point_balance', 'point_balance - ' . $order_data["points"], FALSE);
            $this->user_db->update(USER);

            return array("result" => "1", "message" => "Contest joined successfully.");
        } else {
            return array("result" => "0", "message" => "Something went wrong during contest join.");
        }
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

        $this->user_db->select("affiliate_master_id,real_amount,bonus_amount,coin_amount,user_real,user_bonus,user_coin");
        $this->user_db->from(AFFILIATE_MASTER);
        $this->user_db->where_in("affiliate_master_id", array(LOBBY_REFER_BANNER_AFF_ID, LOBBY_DEPOSIT_BANNER_AFF_ID));
        $result = $this->user_db->get()->result_array();

        $result = array_column($result, NULL, "affiliate_master_id");
        $refer_data = isset($result[LOBBY_REFER_BANNER_AFF_ID]) ? $result[LOBBY_REFER_BANNER_AFF_ID] : array();
        if (!empty($refer_data)) {
            if (isset($refer_data['real_amount']) && $refer_data['real_amount'] > 0) {
                $lobby_data['2']['amount'] = $refer_data['real_amount'];
            } else if (isset($refer_data['bonus_amount']) && $refer_data['bonus_amount'] > 0) {
                $lobby_data['2']['amount'] = $refer_data['bonus_amount'];
                $lobby_data['2']['currency_type'] = "Bonus";
            } else if (isset($refer_data['coin_amount']) && $refer_data['coin_amount'] > 0) {
                $lobby_data['2']['amount'] = $refer_data['coin_amount'];
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
     * used for get contest participant users list
     * @param int $user_id
     * @return array
     */
    public function get_participant_user_details($user_ids) {
        $result = $this->user_db->select("user_id,IFNULL(user_name, 'first_name') AS name,IFNULL(image,'') AS image")
                ->from(USER)
                ->where_in("user_id", $user_ids)
                ->order_by('user_name', 'ASC')
                ->get()
                ->result_array();

        return $result;
    }

    /**
     * Used to check promo code
     * @param array $input
     * @return array
     */
    public function check_promo_code_details($promo_code) {
        $current_date = format_date("today", "Y-m-d H:i:s");
        $sql = $this->user_db->select("PC.*,count(PCE.promo_code_earning_id) as total_used", FALSE)
                ->from(PROMO_CODE . " AS PC")
                ->join(PROMO_CODE_EARNING . " AS PCE", "PCE.promo_code_id = PC.promo_code_id AND PCE.is_processed='1' AND PCE.user_id='" . $this->user_id . "'", "LEFT")
                ->where("status", "1")
                ->where("promo_code", $promo_code)
                ->where("DATE_FORMAT(start_date,'%Y-%m-%d %H:%i:%s') <= ", date('Y-m-d H:i:s', strtotime($current_date)))
                ->where("DATE_FORMAT(expiry_date,'%Y-%m-%d %H:%i:%s') >= ", date('Y-m-d H:i:s', strtotime($current_date)))
                ->group_by("PC.promo_code_id")
                ->get();
        return $sql->row_array();
    }

    /**
     * Used to check and get user promo code earn info
     * @param array $input
     * @return array
     */
    public function get_user_promo_code_earn_info($where_condition) {
        $sql = $this->user_db->select("*", FALSE)
                ->from(PROMO_CODE_EARNING)
                ->where($where_condition)
                ->get();
        return $sql->row_array();
    }

    /**
     * Used for save promo code earning
     * @param array $data_arr
     * @param int $promo_code_earning_id
     * @return array
     */
    public function save_promo_code_earning_details($data_arr) {

        $this->user_db->insert(PROMO_CODE_EARNING, $data_arr);
        return $this->user_db->insert_id();
    }

    /**
     * Used for update promo code earning
     * @param array $data_arr
     * @param int $promo_code_earning_id
     * @return array
     */
    public function update_promo_code_earning_details($data_arr, $promo_code_earning_id) {
        $this->user_db->where('promo_code_earning_id', $promo_code_earning_id)->update(PROMO_CODE_EARNING, $data_arr);
        return $this->db->affected_rows();
    }

     /**
     * Used for save data in oder table on withdraw request
     * @param array $data_arr
     * @return array
     */
    function withdraw_coins($data_arr) {
        $current_date = format_date();
        $amount = $data_arr["amount"];
        $order_data = array();
        $order_data["user_id"] = $data_arr['user_id'];
        $order_data["source"] = $data_arr['source'];
        $order_data["source_id"] = $data_arr['source_id'];
        $order_data["type"] = 1;
        $order_data["date_added"] = $current_date;
        $order_data["modified_date"] = $current_date;
        $order_data["plateform"] = 1;
        $order_data["season_type"] = 1;
        $order_data["status"] = $data_arr['status'];
        $order_data["real_amount"] = 0;
        $order_data["bonus_amount"] = 0;
        $order_data["winning_amount"] = 0;
        $order_data["points"] = $amount;
        $order_data["withdraw_method"] = 1;
        $order_data["reason"] = !empty($data_arr['reason']) ? $data_arr['reason'] : '';
        if (!empty($data_arr['email'])) {
            $order_data["email"] = $data_arr['email'];
        }
        $user_balance = $this->get_user_balance($data_arr["user_id"]);

        if(isset($data_arr['entry_type']))
        {
            $custom_data = array('entry_type' => $data_arr['entry_type']);
            $order_data["custom_data"] = json_encode($custom_data);
        }

        //If requested amount is greater then total amount.
        if (($user_balance["point_balance"] < $amount)) {
            throw new Exception($this->lang->line("prediction_insufficent_coins_err"));
        }

        $this->db = $this->user_db;
        $this->db->trans_start();
        $order_data['order_unique_id'] = $this->_generate_order_key();
        $this->db->insert(ORDER, $order_data);
        $order_id = $this->db->insert_id();
        if ($order_id) {
            
            $point_bal = $user_balance['point_balance'] - $order_data["points"];
            // update user balance!
            $this->update_user_balance($order_data["user_id"], $order_data, 'withdraw');
            
            $withdraw_method_arr = array('0' => 'NA', '1' => 'Bank', '2' => 'PayTm', '3' => 'Paypal', '4' => 'Admin');
            $withdraw_method = ($withdraw_method_arr[$order_data["withdraw_method"]]) ? $withdraw_method_arr[$order_data["withdraw_method"]] : '';
           
        } else {
            $this->db->trans_rollback();
            throw new Exception('Something went wrong during contest join.');
        }
        $this->db->trans_complete();
        $this->db->trans_strict(FALSE);
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();

            $user_balance['point_balance'] = $point_bal;
            $user_balance['order_id'] = $order_id;
            return $user_balance;
        }
    }

 /**  Used to update user balance 
     * @param int $user_id
     * @param float $real_bal
     * @param float $bonus_bal
     * @param float $winning_bal
     * @param float $point_bal  
     * @return int
     */
    function update_user_balance($user_id, $balance_arr,$oprator='add') {
        if(empty($balance_arr)){
            return false;
        }
        if(isset($balance_arr['real_amount']) && $balance_arr['real_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('balance', 'balance - '.$balance_arr['real_amount'], FALSE);
            }else{
                $this->db->set('balance', 'balance + '.$balance_arr['real_amount'], FALSE);
            }
            if(isset($balance_arr['source']) && $balance_arr['source'] == "7" && $oprator == 'add'){
                $this->db->set('total_deposit', 'total_deposit + '.$balance_arr['real_amount'], FALSE);
            }
        }
        if(isset($balance_arr['bonus_amount']) && $balance_arr['bonus_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('bonus_balance', 'bonus_balance - '.$balance_arr['bonus_amount'], FALSE);
            }else{
                $this->db->set('bonus_balance', 'bonus_balance + '.$balance_arr['bonus_amount'], FALSE);
            }
            $this->load->helper('queue_helper');
            $bonus_data = array('oprator' => $oprator, 'user_id' => $user_id, 'total_bonus' => $balance_arr['bonus_amount'], 'bonus_date' => format_date("today", "Y-m-d"));
            add_data_in_queue($bonus_data, 'user_bonus');
        }
        if(isset($balance_arr['winning_amount']) && $balance_arr['winning_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('winning_balance', 'winning_balance - '.$balance_arr['winning_amount'], FALSE);
            }else{
                $this->db->set('winning_balance', 'winning_balance + '.$balance_arr['winning_amount'], FALSE);
            }
        }
        if(isset($balance_arr['points']) && $balance_arr['points'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('point_balance', 'point_balance - '.$balance_arr['points'], FALSE);
            }else{
                $this->db->set('point_balance', 'point_balance + '.$balance_arr['points'], FALSE);
            }
            $this->load->helper('queue_helper');
            $coin_data = array('oprator' => $oprator, 'user_id' => $user_id, 'total_coins' => $balance_arr['points'], 'bonus_date' => format_date("today", "Y-m-d"));
            add_data_in_queue($coin_data, 'user_coins');
        }
        $this->db->where('user_id', $user_id);
        $this->db->update(USER);
        return $this->db->affected_rows();
    }

    public function get_users_by_ids($user_ids)
	{

		if(empty($user_ids))
		{
			return array();
		}
		$sql = $this->user_db->select('user_id,email,phone_no,phone_code,user_name,user_unique_id')
						->from(USER)
						->where_in('user_id', $user_ids)
						->get();
		$rs = $sql->result_array();

		//$rs = array_column($rs, "email","user_id");
		
		return $rs;
    }
    
     /**
     * Used for get sports hub data
     * @param NA
     * @return Array
     */
    public function get_sports_hub()
    {
        return $this->user_db->select("*")
        ->from(SPORTS_HUB)
        //->where('status',1)
        ->order_by('display_order','ASC')
        ->get()->result_array();
    }

    function get_all_table_data($table, $select = '*', $where = "") {
        $this->user_db->select($select);
        $this->user_db->from($table);
        if ($where != "") {
            $this->user_db->where($where);
        }
        $query = $this->user_db->get();
        return $query->result_array();
    }

    /**
     * Updates whole row [unlike update_field()]
     * @param array $data
     * @param int   $id
     */
    public function update_user_db($table = "", $data, $where = "") {
        if (!is_array($data)) {
            log_message('error', 'Supposed to get an array!');
            return FALSE;
        } else if ($table == "") {
            log_message('error', 'Got empty table name');
            return FALSE;
        } else if ($where == "") {
            return false;
        } else {
            $this->user_db->where($where);
            $this->user_db->update($table, $data);
            return true;
        }
    }

    /**
    * Function used for get banned state list
    * @param void
    * @return array
    */
    public function get_banned_state_list()
    {
        $this->user_db->select("BS.master_state_id as state_id,MS.name,MS.pos_code", FALSE);
        $this->user_db->from(BANNED_STATE." AS BS");
        $this->user_db->join(MASTER_STATE." AS MS","MS.master_state_id = BS.master_state_id","INNER");
        $this->user_db->order_by('BS.id','ASC');
        $result = $this->user_db->get()->result_array();
        return $result;

    }

    public function get_country_state_ids() {
       return $this->user_db->select('MC.master_country_id as country_id,MS.master_state_id as state_id,MS.pos_code as state_code,MC.pos_code as country_code')
                ->from(MASTER_COUNTRY. " MC")
                ->join(MASTER_STATE. " MS","MS.master_country_id=MC.master_country_id")
                ->get()->result_array();
    } 

}
