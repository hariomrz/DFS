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
     * used to get app config data 
     * @param string    $select
     * @return  array
     */
    public function get_app_config_data($select = '*') {
        $this->user_db->select($select);
        $this->user_db->from(APP_CONFIG);
        $query = $this->user_db->get();
        return $query->result_array();
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
     * used for validate admin session key
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
        return ($result)?$result:array();
    }

    function get_admin_access_list($admin_id)
    {
        $sql = $this->user_db->select("AD.admin_id,AD.privilege,AD.status,AD.email,AD.role as admin_role,CONCAT_WS(' ', AD.firstname, AD.lastname) AS full_name,ARR.right_ids,AD.access_list,AD.firstname as first_name,AD.username", FALSE)
        ->from(ADMIN." AS AD")
        ->join(ADMIN_ROLES_RIGHTS." AS ARR", "ARR.role_id = AD.role", "inner")
        ->where("AD.admin_id", $admin_id)
        ->where("AD.status", 1)
        ->get();
        $data = $sql->row_array();
        return $data;
    }

    /**
     * used for validate user session key
     * @param string $key
     * @return array
     */
    public function check_user_key($key) {
        $sql = $this->user_db->select("U.user_id,user_unique_id,date_created,first_name,last_name,email,user_name,status,U.bonus_balance,U.winning_balance,U.balance,U.referral_code,AL.role,AL.device_type,U.point_balance,AL.device_id,U.language,U.phone_no,IFNULL(U.bs_status,'') as bs_status")
                ->from(ACTIVE_LOGIN . ' AS AL')
                ->join(USER . ' AS U', 'U.user_id = AL.user_id')
                ->where("AL.key", $key)
                ->limit(1)
                ->get();
        $result = $sql->row_array();
        return ($result) ? $result : array();
    }

    /**
     * used for get users detail by ids
     * @param array $user_ids
     * @return array
     */
    public function get_users_by_ids($user_ids)
    {
        if(empty($user_ids))
        {
            return array();
        }
        $this->user_db->select("user_id,email,phone_no,phone_code,user_name,IFNULL(image,'') as image",FALSE)
                ->from(USER)
                ->where_in('user_id', $user_ids);
        $result = $this->user_db->get()->result_array();
        return $result;
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
        $this->user_db->select("U.user_id,U.user_unique_id,IFNULL(TRIM(CONCAT(U.first_name,' ',U.last_name)),U.user_name) as name,IFNULL(U.image,'') as image,U.user_name",FALSE)
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
     * used for get user balance
     * @param int $user_id
     * @return array
     */
    public function get_user_balance($user_id) {
        if(!$user_id){
            return false;
        }

        $result = $this->user_db->select("user_id,balance,bonus_balance,winning_balance,point_balance,IFNULL(master_state_id,0) as master_state_id,total_winning,IFNULL(campaign_code,'') as campaign_code,added_date")
                    ->from(USER)
                    ->where("user_id", $user_id)
                    ->limit(1)
                    ->get()
                    ->row_array();

        return $result;
    }

    /**
     * Used for save data in order table on picks entry
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
            $order_data['order_unique_id'] = $this->_generate_order_key();
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
                $txn_applicable = 0;
                //deduct user balance after contest join
                $this->user_db->where('user_id', $order_data['user_id']);
                if($order_data["real_amount"] > 0){
                    $txn_applicable = 1;
                    $this->user_db->where('balance >= ', $order_data["real_amount"]);
                }
                if($order_data["bonus_amount"] > 0){
                    $txn_applicable = 1;
                    $this->user_db->where('bonus_balance >= ', $order_data["bonus_amount"]);
                }
                if($order_data["winning_amount"] > 0){
                    $txn_applicable = 1;
                    $this->user_db->where('winning_balance >= ', $order_data["winning_amount"]);
                }
                if($order_data["points"] > 0){
                    $txn_applicable = 1;
                    $this->user_db->where('point_balance >= ', $order_data["points"]);
                }
                $this->user_db->set('balance', 'balance - '.$order_data["real_amount"], FALSE);
                $this->user_db->set('bonus_balance', 'bonus_balance - '.$order_data["bonus_amount"], FALSE);
                $this->user_db->set('winning_balance', 'winning_balance - '.$order_data["winning_amount"], FALSE);
                $this->user_db->set('point_balance', 'point_balance - '.$order_data["points"], FALSE);
                $this->user_db->update(USER);
                $afftected_rows = $this->user_db->affected_rows();

                $user_balance_cache_key = 'user_balance_' . $order_data['user_id'];
				$this->delete_cache_data($user_balance_cache_key);

                if($txn_applicable == 1 && $afftected_rows == 0){
                    throw new Exception("Something went wrong during entry join.");
                }else{
                    //Trasaction End
                    $this->user_db->trans_complete();
                    if ($this->user_db->trans_status() === FALSE )
                    {
                        throw new Exception("Something went wrong during entry join.");
                    }
                    else
                    {
                        $this->user_db->trans_commit();
                        return array("result" => "1", "message" => $this->lang->line('team_success'),"order_id"=>$order_id);
                    }
                }
            } else {
                throw new Exception("Something went wrong during entry join.");
            }
        } catch (Exception $e)
        {
            $this->user_db->trans_rollback();
            return array("result" => "0", "message" => "Something went wrong during contest join.");
        }
    }

     /**
     * Used for save data in order table on picks entry
     * @param array $post_data
     * @return array
     */
    public function credit_user_amount($post_data) {
        try
        {
            $current_date = format_date();
            //Start Transaction
            $this->user_db->trans_strict(TRUE);
            $this->user_db->trans_start();
            $order_data = array();
            $order_data['order_unique_id'] = $this->_generate_order_key();
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
            $order_data["real_amount"] = $post_data['real_amount'];
            $order_data["bonus_amount"] = $post_data['bonus_amount'];
            $order_data["winning_amount"] = $post_data['winning_amount'];
            $order_data["points"] = $post_data['points'];
            $order_data["reason"] = isset($post_data['reason']) ? $post_data['reason'] : '';
            $order_data["custom_data"] = isset($post_data['custom_data']) ? json_encode($post_data['custom_data']) : NULL;

            $this->user_db->insert(ORDER, $order_data);
            $order_id = $this->user_db->insert_id();
            if($order_id) {
                $txn_applicable = 0;
                //Refund user balance after entry join
                $this->user_db->where('user_id', $order_data['user_id']);
                
                if($order_data["real_amount"] > 0 ){
                    $this->user_db->set('balance', 'balance + '.$order_data["real_amount"], FALSE);
                }
                 if($order_data["bonus_amount"] > 0 ){
                    $this->user_db->set('bonus_balance', 'bonus_balance + '.$order_data["bonus_amount"], FALSE);
                }
                if($order_data["winning_amount"] > 0 ){
                    $this->user_db->set('winning_balance', 'winning_balance + '.$order_data["winning_amount"], FALSE);
                }
                if($order_data["points"] > 0 ){
                    $this->user_db->set('point_balance', 'point_balance + '.$order_data["points"], FALSE);
                }
                $this->user_db->update(USER);
                $afftected_rows = $this->user_db->affected_rows();
                if($txn_applicable == 1 && $afftected_rows == 0){
                    throw new Exception("Something went wrong during entry join.");
                }else{
                    //Trasaction End
                    $this->user_db->trans_complete();
                    if ($this->user_db->trans_status() === FALSE )
                    {
                        throw new Exception("Something went wrong during entry join.");
                    }
                    else
                    {
                        $this->user_db->trans_commit();
                        return array("result" => "1", "message" => "Entry has been save successfully","order_id"=>$order_id);
                    }
                }
            } else {
                throw new Exception("Something went wrong during entry join.");
            }
        } catch (Exception $e)
        {
            $this->user_db->trans_rollback();
            return array("result" => "0", "message" => "Something went wrong during contest join.");
        }
    }

    public function get_state_detail($master_state_id){
        $sql = $this->user_db->select('*')
                        ->from(MASTER_STATE)
                        ->where('master_state_id', $master_state_id)
                        ->get();
        $result = $sql->row_array();
        return $result;
    }

    public function get_country_state_ids()
    {
        $this->user_db->select('MC.master_country_id as country_id,MS.master_state_id as state_id,MS.pos_code as state_code,MC.pos_code as country_code')
            ->from(MASTER_COUNTRY. " MC")
            ->join(MASTER_STATE. " MS","MS.master_country_id=MC.master_country_id");
        $result = $this->user_db->get()->result_array();
        return $result;
    }
}
