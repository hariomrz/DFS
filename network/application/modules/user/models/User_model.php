<?php

/**
 * Used for return user db records
 * @package     User
 * @category    User
 */
class User_model extends CI_Model {

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

   

    /**  Used to get user balance 
     * @param int $user_id
     * @return array
     */
    function get_user_balance($user_id) {

        if (!$user_id)
            return false;

        $this->user_db->select("user_id,balance as real_amount, bonus_balance as bonus_amount, winning_balance as winning_amount,point_balance,phone_verfied,email_verified,pan_verified,is_bank_verified,pan_image");
        $this->user_db->from(USER);
        $this->user_db->where(array("user_id" => $user_id));
        $this->user_db->limit(1);
        $query = $this->user_db->get();
        $result = $query->row_array();
        return array(
            "bonus_amount" => ($result["bonus_amount"] && $result["bonus_amount"] > 0) ? $result["bonus_amount"] : 0,
            "real_amount" => ($result["real_amount"] && $result["real_amount"] > 0) ? $result["real_amount"] : 0,
            "winning_amount" => ($result["winning_amount"] && $result["winning_amount"] > 0) ? $result["winning_amount"] : 0,
            "point_balance" => ($result["point_balance"] && $result["point_balance"] > 0) ? $result["point_balance"] : 0,
            'phone_verfied' => $result["phone_verfied"],
            'email_verified' => $result["email_verified"],
            'pan_verified' => $result["pan_verified"],
            'is_bank_verified' => $result["is_bank_verified"],
            'pan_image' => $result["pan_image"]
        );
    }

    /**
     * Used for generate order unique id
     * @return string
     */
    public function _generate_order_key() {
        $this->load->helper('security');
        do {
            $salt = do_hash(time() . mt_rand());
            $new_key = substr($salt, 0, 10);
        }

        // Already in the DB? Fail. Try again
        while (self::_order_key_exists($new_key));

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
        $order_data["reference_id"] = $data_arr['reference_id'];
        $order_data["source_id"] = $data_arr['source_id'];
        $order_data["reference_id"] = $data_arr['reference_id'];
        $order_data["type"] = 1;
        $order_data["date_added"] = $current_date;
        $order_data["modified_date"] = $current_date;
        $order_data["plateform"] = 1;
        $order_data["season_type"] = 1;
        $order_data["status"] = 0;
        $order_data["real_amount"] = 0;
        $order_data["bonus_amount"] = 0;
        $order_data["winning_amount"] = 0;
        $order_data["points"] = 0;
        $order_data["withdraw_method"] = 0;
        $order_data["reason"] = !empty($data_arr['reason']) ? $data_arr['reason'] : '';

        $user_balance = $this->get_user_balance($data_arr["user_id"]);
        //bonus_amount,real_amount,winning_amount,point_balance
        if ($data_arr['cash_type'] != 3 && ($user_balance["real_amount"] + $user_balance["winning_amount"] < $amount)) {
            return array("result" => "0", "message" => "Insufficient amount in winnings!");
        }

        $remain_amt = $amount - $order_data["bonus_amount"];
        // Deduct reamining amount from real amount.
        $order_data["real_amount"] = $remain_amt;
        if ($remain_amt > $user_balance["real_amount"]) {
            $order_data["real_amount"] = $user_balance["real_amount"];
        }
        $remain_amt = $remain_amt - $order_data["real_amount"];
        if ($remain_amt > 0 && $remain_amt > $user_balance["winning_amount"]) {
            return array("result" => "0", "message" => "Insufficient amount in winnings!");
        }

        // Deduct Remaining amount from winning amount
        $order_data["winning_amount"] = number_format($remain_amt,"2",".","");
        $order_data["real_amount"] = number_format($order_data["real_amount"],2,".","");
        $order_data["bonus_amount"] = number_format($order_data["bonus_amount"],2,".","");
        //calculate profit
        if(isset($data_arr['source']) && $data_arr['source'] == NW_JOIN_GAME_SOURCE){
            $site_rake = DEFAULT_SITE_RAKE;
            if (isset($data_arr['site_rake']) && $data_arr['site_rake'] != "") {
                $site_rake = $data_arr['site_rake'];
            }
            $total_real_entry = $order_data["real_amount"] + $order_data["winning_amount"];
            $profit = number_format(($total_real_entry * $site_rake)/100,2,'.','');
            $order_custom_data = (!empty($data_arr['custom_data'])) ? array_merge($data_arr['custom_data'],array('profit'=>$profit)) : array('profit'=>$profit);
            $order_data['custom_data'] = json_encode($order_custom_data);
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

            $order_data['order_id'] = $order_id;


            return array("result" => "1", "message" => "Contest joined successfully.","order_data"=>$order_data);
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
            $result = array('real_amount' => $refer_data['real_amount'], 'bonus_amount' => $refer_data['bonus_amount'], 'coin_amount' => $refer_data['coin_amount']);
            $max_value = array_keys($result, max($result));
            $max_value_key = $max_value[0];
            $lobby_data['2']['amount'] = $refer_data[$max_value_key];
            if($max_value_key == 'bonus_amount') {
                $lobby_data['2']['currency_type'] = "Bonus";
            } else if($max_value_key == 'coin_amount') {
                $lobby_data['2']['currency_type'] = "Coin";
            }
            /* if (isset($refer_data['real_amount']) && $refer_data['real_amount'] > 0) {
                $lobby_data['2']['amount'] = $refer_data['real_amount'];
            } else if (isset($refer_data['bonus_amount']) && $refer_data['bonus_amount'] > 0) {
                $lobby_data['2']['amount'] = $refer_data['bonus_amount'];
                $lobby_data['2']['currency_type'] = "Bonus";
            } else if (isset($refer_data['coin_amount']) && $refer_data['coin_amount'] > 0) {
                $lobby_data['2']['amount'] = $refer_data['coin_amount'];
                $lobby_data['2']['currency_type'] = "Coin";
            }
             * 
             */
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

    public function get_users_by_ids($user_ids)
	{

		if(empty($user_ids))
		{
			return array();
		}
		$sql = $this->user_db->select('user_id,email,phone_no,phone_code,user_name,image')
						->from(USER)
						->where_in('user_id', $user_ids)
						->get();
		$rs = $sql->result_array();

		//$rs = array_column($rs, "email","user_id");
		
		return $rs;
	}

    /**  Used to update order status from pending to failed or complete
     * @param int $order_id
     * @param int $status
     * @param int $source_id
     * @param string $reason
     * @return int
     */
    function update_network_order_status($order_id, $order_update_data=array()) {
        
        if(empty($order_update_data) || empty($order_id))
        {
            return false;
        }    

        $this->user_db->where('order_id', $order_id)
                ->update(ORDER, $order_update_data);
        return $this->user_db->affected_rows();
    }


    function refund_user_join_network_game($user_id,$order_data)
    {
        if(empty($user_id) || empty($order_data))
        {
            return false;
        }    

        $this->user_db->where('user_id', $user_id);
        if(isset($order_data["real_amount"]))
        {
            $this->user_db->set('balance', 'balance + ' . $order_data["real_amount"], FALSE);
        }

        if(isset($order_data["bonus_amount"]))
        {

            $this->user_db->set('bonus_balance', 'bonus_balance + ' . $order_data["bonus_amount"], FALSE);
        }   

        if(isset($order_data["winning_amount"]))
        {
            $this->user_db->set('winning_balance', 'winning_balance + ' . $order_data["winning_amount"], FALSE);
        } 
       
        if(isset($order_data["points"]))
        {
            $this->user_db->set('point_balance', 'point_balance - ' . $order_data["points"], FALSE);
        }    

        $this->user_db->update(USER);
    }


    public function get_all_config()
	{
		$sql = $this->user_db->select('*')
						->from(APP_CONFIG)
						->get();
		$rs = $sql->result_array();

		//$rs = array_column($rs, "email","user_id");
		
		return $rs;
	}

    /** 
     * used to get app config data 
     * @param string    $select
     * @return  array
     */
    function get_app_config_data($select = '*') {
        $this->user_db->select($select);
        $this->user_db->from(APP_CONFIG);
        $query = $this->user_db->get();
        return $query->result_array();
    }

     public function check_self_exclusion($self_exclusion_data)
     {
        
        $user_id = $self_exclusion_data['user_id'];
        $contest_ids = $self_exclusion_data['contest_ids'];
        $max_limit = $self_exclusion_data['max_limit'];
        $entry_fee = $self_exclusion_data['entry_fee'];
        //log_message('error', "check_self_exclusion user_id => ".$user_id." max_limit => ".$max_limit." entry_fee => ".$entry_fee);
        //log_message('error', "contest_ids => ".json_encode($contest_ids));
        $sql = $this->user_db->select('SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount')
        ->from(ORDER)
        ->where('user_id', $user_id)
        ->where_in('source', array(1,240))
        ->where('status', 1)
        ->where_in('reference_id', $contest_ids)
        ->get();
        $result = $sql->row_array(); 
        if(!empty($result)) {
            $this->user_db->select('max_limit');
            $this->user_db->from(USER_SELF_EXCLUSION);
            $this->user_db->where('user_id', $user_id);
            $this->user_db->limit(1);
            $query = $this->user_db->get();            
            if($query->num_rows() > 0) {
                $row = $query->row_array(); 
                if(!empty($row['max_limit'])) {
                    $max_limit = $row['max_limit'];
                }                  
            } 

            $total_invested_amount = 0;
            if(!empty($result['real_amount'])) {
                $total_invested_amount = $result['real_amount'];
            }
            if(!empty($result['winning_amount'])) {
                $total_invested_amount = $total_invested_amount + $result['winning_amount'];
            }
            
            //log_message('error', "total_invested_amount => ".$total_invested_amount." max_limit => ".$max_limit);
            if($total_invested_amount > 0 ) {
                #user contest refund entry fee
                #SELECT SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount FROM `vi_order` where source=2 and user_id=3062 and reference_id in(2617,2618,2619,2620,2621,2633)
                $sql = $this->user_db->select('SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount')
                        ->from(ORDER)
                        ->where('user_id', $user_id)
                        ->where('status', 1)
                        ->where_in('source', array(2,242))
                        ->where_in('reference_id', $contest_ids)
                        ->get();
                $result = $sql->row_array();  
                if(!empty($result)) {
                    $total_refund_amount = 0;
                    if(!empty($result['real_amount'])) {
                        $total_refund_amount = $result['real_amount'];
                    }
                    if(!empty($result['winning_amount'])) {
                        $total_refund_amount = $total_refund_amount + $result['winning_amount'];
                    }
                    $total_invested_amount = $total_invested_amount - $total_refund_amount;
                }

                #user contest winning amount
                #SELECT SUM(winning_amount) as winning_amount FROM `vi_order` where source=3 and user_id=3062 and reference_id in(2617,2618,2619,2620,2621,2633)
                $sql = $this->user_db->select('SUM(winning_amount) as winning_amount')
                        ->from(ORDER)
                        ->where('user_id', $user_id)
                        ->where('status', 1)
                        ->where_in('source', array(3,241))
                        ->where_in('reference_id', $contest_ids)
                        ->get();
                $result = $sql->row_array();  
                if(!empty($result)) {
                    $total_winning_amount = $result['winning_amount'];
                    if(!empty($total_winning_amount)) {
                        $total_invested_amount = $total_invested_amount - $total_winning_amount;
                    }
                    
                }
            }

            //log_message('error', "final_invested_amount => ".$total_invested_amount." max_limit => ".$max_limit);
            if($total_invested_amount > 0  && ($total_invested_amount + $entry_fee) > $max_limit) {
                return 0;
            } else if($total_invested_amount > 0  && ($total_invested_amount + $entry_fee) <= $max_limit) {
                return 1;
            } else if($total_invested_amount <= 0  && ($total_invested_amount + $entry_fee) <= $max_limit) {                
                return 1;
            } else {
                return 0;
            }
            //$total_invested_amount > 0 //loss
        }
        return 1;
    }
    
}
