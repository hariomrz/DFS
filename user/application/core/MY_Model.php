<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class MY_Model extends CI_Model {

    /**
     * Class constructor
     * load user db database.
     * @return	void
     */
    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Class destructor
     * Closes the connection to user db if present
     * @return	void
     */
    function __destruct() {
        if (isset($this->db->conn_id)) {
            $this->db->close();
        }
    }
    /**
     * common function used to get all data from any table
     * @param string    $table
     * @param string    $select     
     * @param array/string $where
     * @return	array
     */
    function get_all_table_data($table, $select = '*', $where = "", $order_by = "") {
        $this->db->select($select);
        $this->db->from($table);
        if ($where != "") {
            $this->db->where($where);
        }
        if($order_by != "" && !empty($order_by)){
            foreach($order_by as $field=>$order){
                $this->db->order_by($field,$order);
            }
        }
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * common function used to get single record from any table
     * @param string    $select
     * @param string    $table
     * @param array/string $where
     * @return	array
     */
    function get_single_row($select = '*', $table, $where = "") {
        $this->db->select($select, FALSE);
        $this->db->from($table);
        if ($where != "") {
            $this->db->where($where);
        }
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * common function used to insert batch records into table
     * @param   array $data
     * @return	bool
     */
    function insert_batch($data) {
        $this->db->insert_batch($this->table_name, $data);
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Replace into Batch statement
     * Generates a replace into string from the supplied data
     * @param    string    the table name
     * @param    array    the update data
     * @return   string
     */
    function replace_into_batch($table, $data) {
        $column_name = array();
        $update_fields = array();
        $append = array();
        foreach ($data as $i => $outer) {
            $column_name = array_keys($outer);
            $coloumn_data = array();
            foreach ($outer as $key => $val) {
                if ($i == 0) {
                    $update_fields[] = "`" . $key . "`" . '=VALUES(`' . $key . '`)';
                }

                if (is_numeric($val)) {
                    $coloumn_data[] = $val;
                } else {
                    $coloumn_data[] = "'" . replace_quotes($val) . "'";
                }
            }
            $append[] = " ( " . implode(', ', $coloumn_data) . " ) ";
        }

        $sql = "INSERT INTO " . $this->db->dbprefix($table) . " ( " . implode(", ", $column_name) . " ) VALUES " . implode(', ', $append) . " ON DUPLICATE KEY UPDATE " . implode(', ', $update_fields);
        $this->db->query($sql);
    }

    /**
     * Updates whole row [unlike update_field()]
     * @param array $data
     * @param int   $id
     */
    public function update($table = "", $data, $where = "") {
        $return_flag = FALSE;
        if (!is_array($data)) {
            log_message('error', 'Supposed to get an array!');
        } else if ($table == "") {
            log_message('error', 'Got empty table name');
        } else if ($where == "") {
            log_message('error', 'Got empty where condition');
        } else {
            $this->db->where($where);
            $this->db->update($table, $data);
            $return_flag = TRUE;
        }
        return $return_flag;
    }

    /**
     * common function used to delete record from any table
     * @param string    $table
     * @param array/string $condition
     * @return	array
     */
    public function delete_row($table, $condition) {
        $this->db->where($condition);
        $this->db->delete($table);
    }

    public function get_nosql_single_record($table_name, $where_condition) {
        if (!$table_name || empty($where_condition)) {
            return false;
        }
        $this->load->library('mongo_db');
        return $this->mongo_db->where($where_condition)->find_one($table_name);
    }

    public function save_nosql_record($table_name, $data_arr) {
        if (!$table_name || empty($data_arr)) {
            return false;
        }
        $this->load->library('mongo_db');
        $this->mongo_db->insert($table_name, $data_arr);
        return true;
    }

    /**
     * for generate user_name from email
     * @param string $email
     * @return string
     */
    public function generate_user_name($email) {
        if ($this->input->post('no_username') == '1') {
            return NULL;
        }

        $user_name = explode('@', $email);
        $user_name = $user_name[0];
        $a = 0;
        do {
            if ($a !== 0) {
                $user_name = $user_name . $a;
            }
            $a++;
        }

        // Already in the DB? Fail. Try again
        while (self::_user_name_exists($user_name));

        return $user_name;
    }

    /**
     * to check user_name exist or not
     * @param string $user_name
     * @return boolean
     */
    public function _user_name_exists($user_name) {
        $this->db->select('user_id');
        $this->db->where('user_name', $user_name);
        $this->db->limit(1);
        $query = $this->db->get(USER);
        $num = $query->num_rows();
        if($num > 0){
            return true;
        }
        return false;
    }

    /**
     * Used for load cache driver
     * @return 
     */
    private function init_cache_driver() {
        $this->load->driver('cache', array('adapter' => CACHE_ADAPTER, 'backup' => 'file'));
    }

    /**
     * Used for get cache data by key
     * @param string $cache_key cache key
     * @return array
     */
    public function get_cache_data($cache_key) {
        if (!$cache_key || !CACHE_ENABLE) {
            return false;
        }

        $this->init_cache_driver();
        $cache_key = CACHE_PREFIX . $cache_key;
        return $this->cache->get($cache_key);
    }

    /**
     * Used for save cache data by key
     * @param string $cache_key cache key
     * @param array $data_arr cache data
     * @param int $expire_time cache expire time
     * @return boolean
     */
    public function set_cache_data($cache_key, $data_arr, $expire_time = 3600) {
        if (!$cache_key || !CACHE_ENABLE) {
            return false;
        }

        $this->init_cache_driver();
        $cache_key = CACHE_PREFIX . $cache_key;
        $this->cache->save($cache_key, $data_arr, $expire_time);
        return true;
    }

    /**
     * Used for delete cache data by key
     * @param string $cache_key cache key
     * @return boolean
     */
    public function delete_cache_data($cache_key) {
        if (!$cache_key || !CACHE_ENABLE) {
            return false;
        }

        $this->init_cache_driver();
        $delete_cache_key = CACHE_PREFIX . $cache_key;
        $this->cache->delete($delete_cache_key);
        return true;
    }

     /**
     * Used for get sports hub data
     * @param NA
     * @return Array
     */
    public function get_sports_hub($langs)
    {
        $arr_str = implode("_title,",$langs)."_title,".implode("_desc,",$langs)."_desc";


        $lang_t_str = array();
        $lang_d_str = array();
        foreach ($langs as $lang) {
            $lang_t_str[] = $lang."_title as ".$lang."_t";
            $lang_d_str[] = $lang."_desc as ".$lang."_d";
        }
        $sel_str = "sports_hub_id,game_key,image,is_featured,allowed_sports,game_type,display_order,".implode(",",$lang_t_str).",".implode(",",$lang_d_str);
        return $this->db->select($sel_str)
        ->from(SPORTS_HUB)
        ->where('status',1)
        // ->order_by('is_featured','DESC')
        ->order_by('display_order','ASC')
        ->get()->result_array();
    }

    public function get_affiliate_item_value($affiliate_type=1,$column)
    {

        $affiliate_master_key = 'affiliate_master';
        $result = $this->get_cache_data($affiliate_master_key);

        if(!$result)
        {
            $result = $this->db->select('affiliate_type,invest_money,bonus_amount,real_amount,user_bonus,coin_amount, user_real,user_coin')
            ->from(AFFILIATE_MASTER)
            ->get()->result_array();
            $this->set_cache_data($affiliate_master_key, $result, REDIS_30_DAYS);
        }

        $affiliate_map = array_column($result,$column,'affiliate_type');

        return $affiliate_map[$affiliate_type];
      
    }

    public function get_all_device_id($user_ids) {
        $this->db->select('device_id', false);
        $this->db->from(ACTIVE_LOGIN);
        $this->db->where_in('user_id', $user_ids);
        $this->db->where('device_id IS NOT NULL');
        $query = $this->db->get();
        $result = $query->result_array();
        return array_column($result, "device_id");
    }

    public function get_saperate_device_ids($user_ids)
    {
        $this->db->select("GROUP_CONCAT(case when AL.device_type = 1 then device_id  else NULL end SEPARATOR ' , ') as device_ids,
        GROUP_CONCAT(case when AL.device_type = 2 then device_id  else NULL end SEPARATOR ' , ') as ios_device_ids", false);
        $this->db->from(ACTIVE_LOGIN.' AL');
        $this->db->where_in('user_id', $user_ids);
        $this->db->where('device_id IS NOT NULL');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }


    /**
     * update order status to platform
     * 
     * @param int $order_detail order DETAILS 
     * @param array $update_data update data
     * @return bool Status updated or not
     */
    function _common_update_payout_tx_status($order_detail, $update_data = array()) {
        // order_id,transaction_id,source,user_id,winning_amount,date_added,payment_gateway_id
        $order_id = $order_detail['order_id'];
        $this->db->where('transaction_id', $order_detail['transaction_id'])->update(TRANSACTION, $update_data);
        
        $status_type = $update_data['transaction_status'];
        if(!empty($status_type)) {
            $reason = "";
            if ($status_type == 1 || $status_type == 5) {
                $status_type = 1;
            } else if ($status_type == 3) {
                $status_type = 0;
            } else if (($status_type == 4 || $status_type == 2) && $order_detail['source'] == 8) {
                $reason = $this->lang->line('withdraw_failed');
                $status_type = 2;
            }
    
            $source_id = 0;
            //update order details
            $order_data = array(
                "status" => $status_type,
                "source_id" => $source_id,
                "modified_date" => format_date(),
                "reason" => 'by your recent withdraw'
            );
            $this->db->where('order_id', $order_id)->update(ORDER, $order_data);

            $user_data = $this->get_single_row('user_name, email', USER, array("user_id" => $order_detail["user_id"]));
            $msg_content = array(
                "amount"    => $order_detail['winning_amount'],
                "reason"    => $reason, 
                "user_id"   => $order_detail['user_id'],
                "cash_type" => "0",
                "plateform" => "1",
                "source"    => "7",
                "source_id" => "0",
                "date_added"=> $order_detail['date_added']
            );
            if($order_detail['payment_gateway_id']==17)
            {
                $msg_content["payment_option"] = 'Cashfree'; 
            }
            else if($order_detail['payment_gateway_id']==3)
            {
                $msg_content["payment_option"] = 'Mpesa'; 
            }
            else if($order_detail['payment_gateway_id']==8)
            {
                $msg_content["payment_option"] = 'Razorpay'; 
            }
        
            // SOME CONFUSING BECAUSE STATUS IN DB IS DIFFER
            if($status_type == 1 || $status_type == 5) {
                $notify_data["notification_type"] = 25; // 25-ApproveWithdrawRequest
            }
            
            if($status_type == 2 || $status_type == 4) {
                $notify_data["notification_type"] = 26; // 26-RejectWtihdrawRequest
            }
            $device_ids  = $this->get_user_detail_by_id($order_detail['user_id']);

            $today = format_date();
            $notify_data["source_id"] = 0;
            $notify_data["notification_destination"] = 7; //  Web, Push, Email
            $notify_data["user_id"] =  $order_detail['user_id'];
            $notify_data["to"] = $user_data['email'];
            $notify_data["user_name"] = $user_data['user_name'];
            $notify_data["device_ids"] = isset($device_ids['device_ids']) ? $device_ids['device_ids'] : '' ;
            $notify_data["ios_device_ids"] = isset($device_ids['ios_device_ids']) ? $device_ids['ios_device_ids'] : '';
            $notify_data["added_date"] = $today;
            $notify_data["modified_date"] = $today;
            $notify_data["content"] = json_encode($msg_content);

            if($status_type == 1 || $status_type == 5) {
                $notify_data["subject"] = $this->lang->line("withdraw_email_approve_subject");                
            } else {
                $notify_data["subject"] = $this->lang->line("withdraw_email_reject_subject");
            }

            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($notify_data);  
            return true; 
        }
    }

    public function get_user_detail_by_id($user_id)
    {
        $result = $this->db->select('GROUP_CONCAT(CASE WHEN AL.device_type = 1 THEN device_id  else NULL END SEPARATOR " , ") device_ids,GROUP_CONCAT(case when AL.device_type = 2 then device_id  else NULL end SEPARATOR " , ") ios_device_ids',false)
        // ->from(USER.' U')
		->from(ACTIVE_LOGIN.' AL')
        ->where('AL.device_id IS NOT NULL')
        ->where('AL.user_id',$user_id)
        // ->group_by('U.user_id','AL.device_type')
        ->get()->row_array();
        return $result;
    }



}

/* End of file MY_Model.php */
/* Location: application/core/MY_Model.php */