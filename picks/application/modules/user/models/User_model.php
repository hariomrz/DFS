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
                ->where("AL.role",1)
                ->limit(1)
                ->get();
        $result = $sql->row_array();
        //echo $this->user_db->last_query();
        return ($result) ? $result : array();
    }

    /**
     * used for get users list by ids
     * @param array $user_ids
     * @return array
     */
    public function get_users_by_ids($user_ids)
    {
        if(empty($user_ids))
        {
            return array();
        }
        $sql = $this->user_db->select("user_id,user_unique_id,email,phone_no,phone_code,user_name,IFNULL(image,'') as image",FALSE)
                ->from(USER)
                ->where_in('user_id', $user_ids)
                ->get();
        $result = $sql->result_array();
        return $result;
    }

    /**
     * used for get contest participant users list
     * @param int $user_id
     * @return array
     */
    public function get_participant_user_details($user_ids,$allow_xp_point=0) {

        $select = "U.user_id,IFNULL(U.user_name, 'U.first_name') AS name,IFNULL(U.image,'') AS image";
         $this->user_db->select($select)
                ->from(USER.' U');
        if($allow_xp_point == 1)
        {
            $this->user_db->select('XU.level_id,XLP.level_number,B.badge_id, B.badge_name,B.badge_icon');
            $this->user_db->join(XP_USERS.' XU','XU.user_id=U.user_id','LEFT');
            $this->user_db->join(XP_LEVEL_POINTS.' XLP','XLP.level_pt_id=XU.level_id','LEFT');
            $this->user_db->join(XP_LEVEL_REWARDS.' R','R.level_number=XLP.level_number','LEFT');
            $this->user_db->join(XP_BADGE_MASTER.' B','B.badge_id=R.badge_id','LEFT');
        }

        $result = $this->user_db->where_in("U.user_id", $user_ids)
                ->order_by('U.user_name', 'ASC')
                ->get()
                ->result_array();

        return $result;
    }

    /**
     * used for get user balance
     * @param int $user_id
     * @return array
     */
    public function get_user_balance($user_id) {
        if (!$user_id)
            return false;

        $result = $this->user_db->select("user_id,balance,bonus_balance,winning_balance,point_balance,IFNULL(master_state_id,0) as master_state_id,total_winning,added_date")
                        ->from(USER)
                        ->where("user_id", $user_id)
                        ->limit(1)
                        ->get()->row_array();

        return array(
            "bonus_amount" => ($result["bonus_balance"] && $result["bonus_balance"] > 0) ? $result["bonus_balance"] : 0,
            "real_amount" => ($result["balance"] && $result["balance"] > 0) ? $result["balance"] : 0,
            "winning_amount" => ($result["winning_balance"] && $result["winning_balance"] > 0) ? $result["winning_balance"] : 0,
            "point_balance" => ($result["point_balance"] && $result["point_balance"] > 0) ? $result["point_balance"] : 0,
            "master_state_id" => $result['master_state_id'],
            "total_winning" => $result['total_winning'],
            "added_date" => $result['added_date']

        );
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

    /**
     * Used for generate order unique id
     * @return string
     */
    public function _generate_order_key() {
        $this->load->helper('security');
        $salt = do_hash(time() . mt_rand());
        $new_key = substr("o".$salt, 0, 10);
        return $new_key;
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
        $order_data["reference_id"] = isset($data_arr['reference_id']) ? $data_arr['reference_id'] : 0;
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
        $custom_data = array();
        if(isset($data_arr['custom_data']))
        {
            $custom_data = $data_arr['custom_data'];
        }
        $user_balance = $this->get_user_balance($data_arr["user_id"]);
        //bonus_amount,real_amount,winning_amount,point_balance
        if ($data_arr['cash_type'] != 3 && ($user_balance["real_amount"] + $user_balance["bonus_amount"] + $user_balance["winning_amount"] < $amount)) {
            return array("result" => "0", "message" => "Insufficient amount in winnings!");
        }

        if ($data_arr['cash_type'] == 3 && $user_balance["point_balance"] < $amount) {
            return array("result" => "0", "message" => "Insufficient Coins");
        }
        if($data_arr['cash_type'] == 3){
            $order_data["points"] = $amount;
        }else{
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
            
            if($order_data["source"] == CONTEST_JOIN_SOURCE && MAX_CONTEST_BONUS > 0 && $order_data["bonus_amount"] > MAX_CONTEST_BONUS) { // Game join max bonus cap check
                $order_data["bonus_amount"] = MAX_CONTEST_BONUS;
            }
                
            $remain_amt = number_format(($amount - $order_data["bonus_amount"]),"2",".","");
            // Deduct reamining amount from real amount.
            $order_data["real_amount"] = $remain_amt;
            if ($remain_amt > $user_balance["real_amount"]) {
                $order_data["real_amount"] = $user_balance["real_amount"];
            }
            $remain_amt = number_format(($remain_amt - $order_data["real_amount"]),"2",".","");
            if ($remain_amt > 0 && $remain_amt > $user_balance["winning_amount"]) {
                return array("result" => "0", "message" => "Insufficient amount in winnings!");
            }

            //Deduct Remaining amount from winning amount
            $order_data["winning_amount"] = number_format($remain_amt,"2",".","");
            $order_data["real_amount"] = number_format($order_data["real_amount"],2,".","");
            $order_data["bonus_amount"] = number_format($order_data["bonus_amount"],2,".","");

            //calculate profit
            if(isset($data_arr['source']) && $data_arr['source'] == CONTEST_JOIN_SOURCE){
                $site_rake = DEFAULT_SITE_RAKE;
                if (isset($data_arr['site_rake']) && $data_arr['site_rake'] != "") {
                    $site_rake = $data_arr['site_rake'];
                }
                $total_real_entry = $order_data["real_amount"] + $order_data["winning_amount"];
                $profit = number_format(($total_real_entry * $site_rake)/100,2,'.','');
                $custom_data['profit'] = $profit;
            }
        }

        $order_data['custom_data'] = json_encode($custom_data);
        $order_data['order_unique_id'] = $this->_generate_order_key();
        try
        {
            //Start Transaction
            $this->user_db->trans_strict(TRUE);
            $this->user_db->trans_start();

            $this->user_db->insert(ORDER, $order_data);
            $order_id = $this->user_db->insert_id();
            if ($order_id) {
                //deduct user balance after contest join
                $this->user_db->where('user_id', $data_arr['user_id']);
                if($order_data["real_amount"] > 0){
                    $this->user_db->where('balance >= ', $order_data["real_amount"]);
                }
                if($order_data["bonus_amount"] > 0){
                    $this->user_db->where('bonus_balance >= ', $order_data["bonus_amount"]);
                }
                if($order_data["winning_amount"] > 0){
                    $this->user_db->where('winning_balance >= ', $order_data["winning_amount"]);
                }
                if($order_data["points"] > 0){
                    $this->user_db->where('point_balance >= ', $order_data["points"]);
                }
                $this->user_db->set('balance', 'balance - '.$order_data["real_amount"], FALSE);
                $this->user_db->set('bonus_balance', 'bonus_balance - '.$order_data["bonus_amount"], FALSE);
                $this->user_db->set('winning_balance', 'winning_balance - '.$order_data["winning_amount"], FALSE);
                $this->user_db->set('point_balance', 'point_balance - '.$order_data["points"], FALSE);
                $this->user_db->update(USER);
                $afftected_rows = $this->user_db->affected_rows();
                if($afftected_rows == 0 && $data_arr['is_promo_code'] ==0){
                    throw new Exception("Something went wrong during contest join.");
                }else{
                    //Trasaction End
                    $this->user_db->trans_complete();
                    if ($this->user_db->trans_status() === FALSE )
                    {
                      throw new Exception("Something went wrong during contest join.");
                    }
                    else
                    {
                        $this->user_db->trans_commit();
                        if($order_data["bonus_amount"] > 0) {
                            $this->load->helper('queue_helper');
                            $bonus_data = array('oprator' => 'withdraw', 'user_id' => $order_data["user_id"], 'total_bonus' => $order_data["bonus_amount"], 'bonus_date' => format_date("today", "Y-m-d"));
                            add_data_in_queue($bonus_data, 'user_bonus');
                        }
                        if($order_data["points"])
                        {
                            $this->load->helper('queue_helper');
                            $coins_data = array('oprator' => 'withdraw', 'user_id' => $order_data["user_id"], 'total_coins' => $order_data["points"], 'bonus_date' => format_date("today", "Y-m-d"));
                            add_data_in_queue($coins_data, 'user_coins');
                        }
                        return array("result" => "1", "message" => "Contest joined successfully.","order_id"=>$order_id);
                    }
                }
            } else {
                throw new Exception("Something went wrong during contest join.");
            }
        } catch (Exception $e)
        {
            $this->user_db->trans_rollback();
            return array("result" => "0", "message" => "Something went wrong during contest join.");
        }
    }

    function get_admin_access_list($admin_id)
    {
        $sql = $this->user_db->select("AD.admin_id,AD.privilege,AD.status,AD.email,AD.role as admin_role,CONCAT_WS(' ', AD.firstname, AD.lastname) AS full_name,ARR.right_ids,AD.access_list,AD.firstname as first_name,AD.username", FALSE)
        ->from(ADMIN . " AS AD")
        ->join(ADMIN_ROLES_RIGHTS . " AS ARR", "ARR.role_id = AD.role", "inner")
        ->where("AD.admin_id", $admin_id)
        ->where("AD.status", 1)
        ->get();
        $data = $sql->row_array();
        return $data;
    }

    /**
	 * [check_user_key description]
	 * @MethodName check_user_key
	 * @Summary This function used for check user key exist
	 * @param      [varchar]  [Login key]
	 * @return     [array]
	 */
	public function check_user_key_admin($key)
	{
		$sql = $this->user_db->select("*")
						->from(ACTIVE_LOGIN)
						->where("key",$key)
						->where("role",2)
						->get();
		$result = $sql->row_array();
		return ($result)?$result:array();
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


    /**
     * [get_user_detail_by_user_id description]
     * @MethodName get_user_detail_by_user_id
     * @Summary This function used for get user Detail
     * @param      [int]  [User Id]
     * @return     [array]
     */
    public function get_user_detail_by_user_id($user_id)
    {
        $result = $this->user_db->select("U.master_state_id,U.master_country_id,U.facebook_id,U.address,U.user_unique_id,U.user_id,U.first_name,U.last_name,U.image,MC.country_name,U.balance,U.email,DATE_FORMAT(U.dob,'%d-%b-%Y') as dob,U.city,U.language,U.status,U.added_date,U.user_name,MS.name as state_name,IFNULL(U.zip_code,'--') As zip_code,IFNULL(U.phone_no,'--') AS phone_no,IFNULL(U.gender,'--') As gender",FALSE)
                ->from(USER." AS U")
                ->join(MASTER_COUNTRY." AS MC","MC.master_country_id = U.master_country_id","left")
                ->join(MASTER_STATE." AS MS","MS.master_state_id = U.master_state_id","left")
                ->where("U.user_id",$user_id)
                ->get()->row_array();
        return ($result) ? $result : array();
    }

    /**
     * Used for update app setting based on key
     * @param array data
     * @param array where
     * @return boolean
     */
    public function update_app_setting($data,$where)
    {
        $this->user_db->where($where);
        $this->user_db->update(APP_CONFIG, $data);
        return true;
    }


   /**
    * Check exclusion limit for playing game
    * @param user_id, contest_ids, max_limit,entry_fee
    * @return boolean
    */
    public function check_self_exclusion($self_exclusion_data) {

        $user_id = $self_exclusion_data['user_id'];
        $contest_ids = $self_exclusion_data['contest_ids'];
        $max_limit = $self_exclusion_data['max_limit'];
        $entry_fee = $self_exclusion_data['entry_fee'];
        //log_message('error', "check_self_exclusion user_id => ".$user_id." max_limit => ".$max_limit." entry_fee => ".$entry_fee);
        //log_message('error', "contest_ids => ".json_encode($contest_ids));
        
        
        $sql = $this->user_db->select('SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount')
        ->from(ORDER)
        ->where('user_id', $user_id)
        ->where_in('source', array(1,527))
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
                        ->where_in('source', array(2,527))
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
                        ->where_in('source', array(3,527))
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

    public function get_country_state_ids() {
       return $this->user_db->select('MC.master_country_id as country_id,MS.master_state_id as state_id,MS.pos_code as state_code,MC.pos_code as country_code')
                ->from(MASTER_COUNTRY. " MC")
                ->join(MASTER_STATE. " MS","MS.master_country_id=MC.master_country_id")
                ->get()->result_array();
    } 
}
