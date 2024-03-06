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
        $sql = $this->user_db->select("U.user_id,user_unique_id,date_created,first_name,last_name,email,user_name,status,U.bonus_balance,U.winning_balance,U.balance,U.referral_code,AL.role,AL.device_type,U.point_balance,AL.device_id,U.phone_no,IFNULL(U.bs_status,'') as bs_status")
                ->from(ACTIVE_LOGIN . ' AS AL')
                ->join(USER . ' AS U', 'U.user_id = AL.user_id')
                ->where("AL.key", $key)
                ->limit(1)
                ->get();
        $result = $sql->row_array();
        return ($result) ? $result : array();
    }

    /**
     * Used for get user detail by user id
     * @param int $user_id
     * @return array
     */
    public function get_user_detail_by_user_id($user_id,$select="")
    {
        if($select != ""){
            $this->user_db->select($select,FALSE);
        }
        $this->user_db->select("U.user_id,U.user_unique_id,TRIM(CONCAT(U.first_name,' ',U.last_name)) as name,U.image,U.user_name",FALSE)
                ->from(USER." AS U");
        if(is_array($user_id)){
            $this->user_db->where_in("U.user_id",$user_id);
            $result = $this->user_db->get()->result_array();
        }else{
            $this->user_db->where("U.user_id",$user_id);
            $result = $this->user_db->get()->row_array();
        }
        return ($result) ? $result:array();
    }

    /**
     * used for get contest participant users list
     * @param int $user_id
     * @return array
     */
    public function get_participant_user_details($user_ids,$allow_xp_point=0) {

        $this->user_db->select("U.user_id,IFNULL(U.user_name, U.first_name) AS name,IFNULL(U.image,'') AS image")
                ->from(USER.' U');
        if($allow_xp_point == 1)
        {
            $this->user_db->select("IFNULL(XLP.level_number,'') as level,IFNULL(B.badge_name,'') as badge_name,IFNULL(B.badge_icon,'') as badge_icon");
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
        if(!$user_id){
            return false;
        }

        $result = $this->user_db->select("user_id,balance,bonus_balance,winning_balance,point_balance,IFNULL(master_state_id,0) as master_state_id,total_winning,added_date")
                        ->from(USER)
                        ->where("user_id", $user_id)
                        ->limit(1)
                        ->get()->row_array();

        return $result;
    }

    /** 
     * used to get app config data 
     * @param string    $select
     * @return	array
     */
	public function get_app_config_data($select = '*') {
		$this->user_db->select($select);
		$this->user_db->from(APP_CONFIG);
		$query = $this->user_db->get();
        return $query->result_array();
    }

    /** 
     * used to update app config data 
     * @param array $update_data
     * @param string $key_name
     * @return  array
     */
    public function update_app_config_data($update_data,$key_name){

        $result = $this->user_db->update(APP_CONFIG,$update_data,array("key_name"=>$key_name));
        return true;
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
    * Function used for check admin session key
    * @param string $key
    * @return array
    */
    public function check_user_key_admin($key)
    {
        $sql = $this->user_db->select("*")
                        ->from(ACTIVE_LOGIN)
                        ->where("key",$key)
                        ->where("role",2)
                        ->get();
        $result = $sql->row_array();
        return ($result) ? $result : array();
    }

    /**
    * Function used for get admin access list
    * @param int $admin_id
    * @return array
    */
    public function get_admin_access_list($admin_id)
    {
        $sql = $this->user_db->select("AD.admin_id,AD.privilege,AD.status,AD.email,AD.role as admin_role,CONCAT_WS(' ', AD.firstname, AD.lastname) AS full_name,ARR.right_ids,AD.access_list,AD.firstname as first_name,AD.username", FALSE)
        ->from(ADMIN." AS AD")
        ->join(ADMIN_ROLES_RIGHTS . " AS ARR", "ARR.role_id = AD.role", "inner")
        ->where("AD.admin_id", $admin_id)
        ->where("AD.status", 1)
        ->get();
        $data = $sql->row_array();
        return $data;
    }

    /**
     * Used for save data in order table on contest join
     * @param array $post_data
     * @return array
     */
    public function deduct_entry_fee($post_data) {
        try
        {
            $current_date = format_date();

            //Start Transaction
            $this->user_db->trans_strict(TRUE);
            $this->user_db->trans_start();

            $order_data = array();
            $order_data['order_unique_id'] = generate_uid();
            $order_data["user_id"] = $post_data['user_id'];
            $order_data["source"] = $post_data['source'];
            $order_data["source_id"] = $post_data['source_id'];
            $order_data["reference_id"] = $post_data['reference_id'];
            $order_data["type"] = 1;
            $order_data["date_added"] = $current_date;
            $order_data["modified_date"] = $current_date;
            $order_data["plateform"] = 1;
            $order_data["season_type"] = 1;
            $order_data["status"] = 1;
            $order_data["real_amount"] = $post_data['real_amount'];
            $order_data["bonus_amount"] = $post_data['bonus_amount'];
            $order_data["winning_amount"] = $post_data['winning_amount'];
            $order_data["points"] = $post_data['points'];
            $order_data["reason"] = isset($post_data['reason']) ? $post_data['reason'] : '';
            $order_data["custom_data"] = isset($post_data['custom_data']) ? json_encode($post_data['custom_data']) : NULL;

            $this->user_db->insert(ORDER, $order_data);
            $order_id = $this->user_db->insert_id();
            if($order_id) {
                //deduct user balance after contest join
                $this->user_db->where('user_id', $order_data['user_id']);
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
                
                if($afftected_rows == 0){
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
                        return array("result" => "1", "message" => "Tournament joined successfully.","order_id"=>$order_id);
                    }
                }
            } else {
                throw new Exception("Something went wrong during tournament join.");
            }

        } catch (Exception $e)
        {
            $this->user_db->trans_rollback();
            return array("result" => "0", "message" => "Something went wrong during tournament join.");
        }
    }

    /**
     * Used for refund contest entry
     * @param array $contest
     * @return array
     */
    public function refund_contest_entry($contest)
    {
        if(empty($contest)){
            return false;
        }
        $contest_id = $contest['contest_id'];
        $check_refund = $this->user_db->select('count(order_id) as total')
                            ->from(ORDER)
                            ->where('reference_id', $contest_id)
                            ->where('status', 1)
                            ->where('source', 2)
                            ->get()
                            ->row_array();
        if(isset($check_refund['total']) && $check_refund['total'] > 0){
            return true;
        }
        //start refund process
        $refund_data = $this->user_db->select('O.order_id,O.real_amount,O.bonus_amount,O.winning_amount,O.points,O.source,O.source_id,O.user_id,O.reference_id,O.type',FALSE)
                            ->from(ORDER." AS O")
                            ->where('O.reference_id',$contest_id)
                            ->where('O.status',1)
                            ->where('O.source',1)
                            ->get()
                            ->result_array();

        //echo "<pre>";print_r($refund_data);die;
        $user_txn_data = array();
        $current_date = format_date();
        if(!empty($refund_data))
        {
            foreach ($refund_data as $key => $value)
            {
                //user txn data
                $order_data = array();
                $order_data["order_unique_id"] = generate_uid();
                $order_data["user_id"] = $value['user_id'];
                $order_data["source"] = 2;
                $order_data["source_id"] = $value['source_id'];
                $order_data["reference_id"] = $value['reference_id'];
                $order_data["type"] = 0;
                $order_data["status"] = 0;
                $order_data["season_type"] = 1;
                $order_data["plateform"] = 1;
                $order_data["real_amount"] = $value['real_amount'];
                $order_data["bonus_amount"] = $value['bonus_amount'];
                $order_data["winning_amount"] = $value['winning_amount'];
                $order_data["points"] = $value['points'];
                $order_data["reason"] = isset($contest['reason']) ? $contest['reason'] : '';
                $order_data["custom_data"] = isset($contest['custom_data']) ? json_encode($contest['custom_data']) : NULL;
                $order_data["date_added"] = $current_date;
                $order_data["modified_date"] = $current_date;
                $user_txn_data[] = $order_data;
            }
        }
        //echo "<pre>";print_r($user_txn_data);die;
        if(!empty($user_txn_data)){
            try
            {
                $this->db = $this->user_db;
                //Start Transaction
                $this->db->trans_strict(TRUE);
                $this->db->trans_start();
              
                $user_txn_arr = array_chunk($user_txn_data, 999);
                foreach($user_txn_arr as $txn_data){
                    $this->insert_ignore_into_batch(ORDER, $txn_data);
                }

                $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = 2 AND type=0 AND status=0 AND reference_id='".$contest_id."' GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
                    SET U.balance = (U.balance + OT.real_amount),U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
                    WHERE O.source = 2 AND O.type=0 AND O.status=0 AND O.reference_id='".$contest_id."' ";
                $this->db->query($bal_sql);
                //Trasaction End
                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE )
                {
                    $this->db->trans_rollback();
                    return false;
                }
                else
                {
                    $this->db->trans_commit();

                    //remove users balance cache
                    $ids = array_unique(array_column($user_txn_data,"user_id"));
                    $this->remove_user_balance_cache($ids);
                    return true;
                }
            } catch (Exception $e)
            {
                $this->db->trans_rollback();
                return false;
            }
        }
        return true;
    }

    /**
     * Used for refund contest entry
     * @param array $contest
     * @return array
     */
    public function winning_credit($user_txn_data,$source)
    {
        if(empty($user_txn_data) || empty($source)){
            return false;
        }

        $reference_ids = array_keys($user_txn_data);
        $check_winning = $this->user_db->select('reference_id')
                            ->from(ORDER)
                            ->where_in('reference_id', $reference_ids)
                            ->where('status', 1)
                            ->where('source', $source)
                            ->group_by("reference_id")
                            ->get()
                            ->row_array();
        if(!empty($check_winning)){
            $check_winning = array_fill_keys($check_winning,"0");
            $user_txn_data = array_diff_key($user_txn_data,$check_winning);
        }
        //echo "<pre>";print_r($user_txn_data);die;
        if(!empty($user_txn_data)){
            try
            {
                $this->db = $this->user_db;

                //Start Transaction
                $this->db->trans_strict(TRUE);
                $this->db->trans_start();

                $current_date = format_date();
                foreach($user_txn_data as $contest_txn){
                    $user_txn_arr = array_chunk($contest_txn, 999);
                    foreach($user_txn_arr as $txn_data){
                        $this->insert_ignore_into_batch(ORDER, $txn_data);
                       
                    }
                }
               

                $ctst_ids_arr = array_chunk($reference_ids, 300);
                foreach($ctst_ids_arr as $cnts_ids){
                    $reference_ids_str = implode(",", $cnts_ids);
                    $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = ".$source." AND type=0 AND status=0 AND reference_id IN (".$reference_ids_str.") GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
                    SET U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
                    WHERE O.source = ".$source." AND O.type=0 AND O.status=0 AND O.reference_id IN (".$reference_ids_str.") ";
                    $this->db->query($bal_sql);
                }
                //Trasaction End
                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE )
                {
                    $this->db->trans_rollback();
                    return false;
                }
                else
                {
                    $this->db->trans_commit();

                    //remove users balance cache
                    $ids = array_unique(array_column($user_txn_data,"user_id"));
                    $this->remove_user_balance_cache($ids);
                    return true;
                }
            } catch (Exception $e)
            {
                $this->db->trans_rollback();
                return false;
            }
        }
        return true;
    }

    /**
     * Used for save data in order table on contest join
     * @param array $post_data
     * @return array
     */
    public function credit_perfect_score($post_data) {
        try
        { 
            $current_date = format_date();

            //Start Transaction
            $this->user_db->trans_strict(TRUE);
            $this->user_db->trans_start();

            $order_data = array();
            $order_data['order_unique_id'] = generate_uid();
            $order_data["user_id"] = $post_data['user_id'];
            $order_data["source"] = $post_data['source'];
            $order_data["source_id"] = $post_data['source_id'];
            $order_data["reference_id"] = $post_data['reference_id'];
            $order_data["type"] = 0;
            $order_data["date_added"] = $current_date;
            $order_data["modified_date"] = $current_date;
            $order_data["plateform"] = 1;
            $order_data["season_type"] = 1;
            $order_data["status"] = 1;
            $order_data["real_amount"] = 0;
            $order_data["winning_amount"] = $post_data['real_amount'];
            $order_data["bonus_amount"] = $post_data['bonus_amount'];
            $order_data["points"] = round($post_data['points']);
            $order_data["reason"] = isset($post_data['reason']) ? $post_data['reason'] : '';
            $order_data["custom_data"] = isset($post_data['custom_data']) ? json_encode($post_data['custom_data']) : NULL;

            $this->user_db->insert(ORDER, $order_data);
            $order_id = $this->user_db->insert_id();

            if($order_id) {
                //deduct user balance after contest join
                $this->user_db->where('user_id', $order_data['user_id']);
                $this->user_db->set('winning_balance', 'winning_balance + '.$order_data["winning_amount"], FALSE);
                $this->user_db->set('bonus_balance', 'bonus_balance + '.$order_data["bonus_amount"], FALSE);
                $this->user_db->set('point_balance', 'point_balance + '.$order_data["points"], FALSE);
                $this->user_db->update(USER);
                $afftected_rows = $this->user_db->affected_rows();
               
                if($afftected_rows == 0){
                    throw new Exception("Something went wrong during perect score amount credit.");
                }else{
                    //Trasaction End
                    $this->user_db->trans_complete();
                    if ($this->user_db->trans_status() === FALSE )
                    {
                        throw new Exception("Something went wrong during perect score amount credit.");
                    }
                    else
                    {
                        $this->user_db->trans_commit();
                        //remove users balance cache
                        $ids = [$post_data['user_id']];
                        $this->remove_user_balance_cache($ids);
                        return array("result" => "1", "message" => "Perfect score amount credited successfully.","order_id"=>$order_id);
                    }
                }
            } else {
                throw new Exception("Something went wrong during amount credit.");
            }

        } catch (Exception $e)
        {  
            $this->user_db->trans_rollback();
            return array("result" => "0", "message" => "Something went wrong during amount credit.");
        }
    }

    /**
    * Check exclusion limit for playing game
    * @param user_id, contest_ids, max_limit,entry_fee
    * @return boolean
    */
    public function check_self_exclusion($self_exclusion_data) {

        $user_id = $self_exclusion_data['user_id'];
        $tournament_ids = $self_exclusion_data['tournament_ids'];
        $max_limit = $self_exclusion_data['max_limit'];
        $entry_fee = $self_exclusion_data['entry_fee'];

        $sql = $this->user_db->select('SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount')
        ->from(ORDER)
        ->where('user_id', $user_id)
        ->where_in('source', array(1,TOURNAMENT_JOIN_SOURCE,460))
        ->where('status', 1)
        /*->where_in('reference_id', $tournament_ids)*/
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
                        ->where('source',TOURNAMENT_CANCEL_SOURCE)
                        ->where_in('reference_id', $tournament_ids)
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
                        ->where('source', TOURNAMENT_WON_SOURCE)
                        ->where_in('reference_id', $tournament_ids)
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

    public function get_country_state_ids()
    {
       return $this->user_db->select('MC.master_country_id as country_id,MS.master_state_id as state_id,MS.pos_code as state_code,MC.pos_code as country_code')
                ->from(MASTER_COUNTRY. " MC")
                ->join(MASTER_STATE. " MS","MS.master_country_id=MC.master_country_id")
                ->get()->result_array();
    } 

      public function save_featured_league($post_data){   
    	$current_date = format_date();
    	$is_featured = isset($post_data['is_featured']) ? $post_data['is_featured'] : 0;
    	// $check_exist = $this->user_db->select("*",FEATURED_LEAGUE,array("league_uid"=>$post_data['league_uid'],"sports_id"=>$post_data['sports_id']));
    	$check_exist = $this->user_db->select("*")->from(FEATURED_LEAGUE)->where(array("league_uid"=>$post_data['league_uid'],"sports_id"=>$post_data['sports_id']))->get()->result_array();;
       
    	$result = 0;
    	if(!empty($check_exist)){
    		$data_arr = array();
    		$data_arr['pickem_id'] = 0;
    		if($is_featured == 1){
    			$data_arr['pickem_id'] = $post_data['league_id'];
    		}
    		$data_arr['modified_date'] = $current_date;
             $this->user_db->where(array("league_uid"=>$post_data['league_uid'],"sports_id"=>$post_data['sports_id']));
            $this->user_db->update(FEATURED_LEAGUE, $data_arr);
            return true;
    		// $result = $this->user_db->update(FEATURED_LEAGUE,$data_arr,array("league_uid"=>$post_data['league_uid'],"sports_id"=>$post_data['sports_id']));
    	}else if($is_featured == 1){
    		$data_arr = array();
    		$data_arr['sports_id'] = $post_data['sports_id'];
    		$data_arr['league_uid'] = $post_data['league_uid'];
    		$data_arr['name'] = $post_data['league_name'];
    		$data_arr['pickem_id'] = $post_data['league_id'];
    		$data_arr['added_date'] = $current_date;
    		$data_arr['modified_date'] = $current_date;
    		$this->user_db->insert(FEATURED_LEAGUE,$data_arr);
             return $this->user_db->insert_id();
    	}
    	return $result;
    }

 
}
