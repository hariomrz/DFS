<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Model extends CI_Model {

    public $db_user;

    /**
     * Class constructor
     * load user db database.
     * @return	void
     */
    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->db_user		= $this->load->database('db_user', TRUE);
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
     * @param string    $select
     * @param string    $table
     * @param array/string $where
     * @return	array
     */
    function get_all_table_data($select = '*', $table, $where = "") {
        $this->db->select($select);
        $this->db->from($table);
        if ($where != "") {
            $this->db->where($where);
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

    function get_userdb_single_row($select = '*', $table, $where = "") {
        $this->db_user->select($select, FALSE);
        $this->db_user->from($table);
        if ($where != "") {
            $this->db_user->where($where);
        }
        $this->db_user->limit(1);
        $query = $this->db_user->get();
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
    public function replace_into_batch($table, $data) {
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
     * insert ignore into batch statement
     * @param    string    the table name
     * @param    array    data
     * @return   bool
     */
    public function insert_ignore_into_batch($table, $data) {
        $column_name = array();
        $update_fields = array();
        $append = array();

        foreach ($data as $i => $outer) {
            $coloumn_data = array();
            foreach ($outer as $FLEXey => $val) {
                if ($i == 0) {
                    $column_name[] = "`" . $FLEXey . "`";
                    $update_fields[] = "`" . $FLEXey . "`" . '=VALUES(`' . $FLEXey . '`)';
                }

                if (is_numeric($val)) {
                    $coloumn_data[] = $val;
                } else {
                    $coloumn_data[] = "'" . replace_quotes($val) . "'";
                }
            }

            $append[] = " ( " . implode(', ', $coloumn_data) . " ) ";
        }

        $sql = "INSERT IGNORE INTO " . $this->db->dbprefix($table) . " ( " . implode(", ", $column_name) . " ) VALUES " . implode(', ', $append);
        $this->db->query($sql);
        return true;
    }

    /**
     * Updates whole row [unlike update_field()]
     * @param array $data
     * @param int   $id
     */
    public function update($table = "", $data, $where = "") {
        if (!is_array($data)) {
            //log_message('error', 'Supposed to get an array!');
            return FALSE;
        } else if ($table == "") {
            //log_message('error', 'Got empty table name');
            return FALSE;
        } else if ($where == "") {
            return false;
        } else {
            $this->db->where($where);
            $this->db->update($table, $data);
            return true;
        }
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

    /**
     * [get_config_detail description]
     * Summary :-
     * @param  [type] $league_id [description]
     * @return [type]            [description]
     */
    public function get_cricket_config_detail($cricket_feed_providers = '') {
        $this->config->load('sports_config');
        $feed_config = $this->config->item("cricket_config");
        if ($cricket_feed_providers != '') {
            $feed_providers = $cricket_feed_providers;
        } else {
            $feed_providers = $this->config->item("cricket_feed_providers");
        }

        if (isset($feed_providers) && isset($feed_config[$feed_providers])) {
            return $feed = $feed_config[$feed_providers];
        } else {
            exit("Invalid League");
        }
    }

    public function get_kabaddi_config_detail($kabaddi_feed_providers = '') {
        $this->config->load('sports_config');
        $feed_config = $this->config->item("kabaddi_config");
        if ($kabaddi_feed_providers != '') {
            $feed_providers = $kabaddi_feed_providers;
        } else {
            $feed_providers = $this->config->item("kabaddi_feed_providers");
        }

        if (isset($feed_providers) && isset($feed_config[$feed_providers])) {
            return $feed = $feed_config[$feed_providers];
        } else {
            exit("Invalid League");
        }
    }

    public function get_soccer_config_detail($soccer_feed_providers = '') {
        $this->config->load('sports_config');
        $feed_config = $this->config->item("soccer_config");
        if ($soccer_feed_providers != '') {
            $feed_providers = $soccer_feed_providers;
        } else {
            $feed_providers = $this->config->item("soccer_feed_providers");
        }

        if (isset($feed_providers) && isset($feed_config[$feed_providers])) {
            return $feed = $feed_config[$feed_providers];
        } else {
            exit("Invalid League");
        }
    }

     public function get_basketball_config_detail($basketball_feed_providers = '') {
        $this->config->load('sports_config');
        $feed_config = $this->config->item("basketball_config");
        if ($basketball_feed_providers != '') {
            $feed_providers = $basketball_feed_providers;
        } else {
            $feed_providers = $this->config->item("basketball_feed_providers");
        }

        if (isset($feed_providers) && isset($feed_config[$feed_providers])) {
            return $feed = $feed_config[$feed_providers];
        } else {
            exit("Invalid League");
        }
    }

    public function get_nfl_config_detail($nfl_feed_providers = '') {
        $this->config->load('sports_config');
        $feed_config = $this->config->item("nfl_config");
        if ($nfl_feed_providers != '') {
            $feed_providers = $nfl_feed_providers;
        } else {
            $feed_providers = $this->config->item("nfl_feed_providers");
        }

        if (isset($feed_providers) && isset($feed_config[$feed_providers])) {
            return $feed = $feed_config[$feed_providers];
        } else {
            exit("Invalid League");
        }
    }

    public function get_baseball_config_detail($baseball_feed_providers = '') {
        $this->config->load('sports_config');
        $feed_config = $this->config->item("baseball_config");
        if ($baseball_feed_providers != '') {
            $feed_providers = $baseball_feed_providers;
        } else {
            $feed_providers = $this->config->item("baseball_feed_providers");
        }

        if (isset($feed_providers) && isset($feed_config[$feed_providers])) {
            return $feed = $feed_config[$feed_providers];
        } else {
            exit("Invalid League");
        }
    }

    /**
     * Used for delete file from s3 bucket
     * @return 
     */
    public function delete_s3_bucket_file($file_name) {

        $json_file_name = BUCKET_STATIC_DATA_PATH . BUCKET_DATA_PREFIX . $file_name;
        try{
            $data_arr = array();
            $data_arr['file_path'] = $json_file_name;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_deleted = $upload_lib->delete_file($data_arr);
            if($is_deleted){
                return true;
            }else {
                return false;
            }

        }catch(Exception $e){
            return false;
        }
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
        $cache_data = $this->cache->get($cache_key);
        return $cache_data;
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
    * Used for delete cache data by wildcard key / pattern
    * @param string $cache_key cache key
    * @return boolean
    */
    public function delete_wildcard_cache_data($cache_key) {
        if (!$cache_key || !CACHE_ENABLE) {
            return false;
        }

        $this->init_cache_driver();
        $delete_cache_key = CACHE_PREFIX . $cache_key;
        $this->cache->delete_wildcard($delete_cache_key);
        return true;
    }

    /**
     * Used for delete cache data by key
     * @param string $cache_key cache key
     * @return boolean
     */
    public function flush_cache_data() {
        if (!CACHE_ENABLE) {
            return false;
        }

        $this->init_cache_driver();
        $this->cache->clean();
        return true;
    }

    /**
     * @Summary: This function for use get scoring formula wich is store in master_scoring_category table    
     * @access: protected
     * @param: $league_id 
     * @return: resuly array
     */
    protected function get_scoring_rules($sports_id, $format = '') {

        $this->db->select('ms.score_points, ms.master_scoring_category_id, ms.format, ms.meta_key, msc.scoring_category_name')
                ->from(MASTER_SCORING_RULES . " AS ms")
                ->join(MASTER_SCORING_CATEGORY . " AS msc", "msc.master_scoring_category_id= ms.master_scoring_category_id", "left")
                ->where('msc.sports_id', $sports_id);
        if (!empty($format)) {
            $this->db->where('ms.format', $format);
        }
        $rs = $this->db->get();
        $raw_formula_data = $rs->result_array();
        //echo $this->db->last_query();die;
        $formula = array();
        foreach ($raw_formula_data as $val) {
            $formula[$val['scoring_category_name']][$val['meta_key']] = $val['score_points'];
        }
        return $formula;
    }

    /**
     * Used for set table auto increment id
     * @param string $table_name
     * @param string $primary_key_field
     * @return boolean
     */
    protected function set_auto_increment_key($table_name, $primary_key_field) {
        $rs = $this->db->select("MAX($primary_key_field) AS $primary_key_field ", FALSE)
                ->from($this->db->dbprefix($table_name))
                ->get();
        $new_id = $rs->row($primary_key_field) + 1;
        $this->db->query("ALTER TABLE " . $this->db->dbprefix($table_name) . " AUTO_INCREMENT " . $new_id . " ");
        return true;
    }
    
    /**
     * Used for get all team list
     * @param int $sports_id
     * @param int $year
     * @param string $team_abbrs
     * @return array
     */
    public function get_all_teams($sports_id, $year, $team_abbrs) {
        $sql = $this->db->select("team_id,sports_id,team_abbr,year,team_name")
                ->from(TEAM)
                ->where("sports_id", $sports_id)
                ->where("year", $year)
                ->where_in("team_abbr", $team_abbrs)
                ->get();
        return $sql->result_array();
    }

    /**
     * Used for get match details by id
     * @param int $season_game_uid
     * @param int $league_id
     * @return array
     */
    public function get_season_match_details($season_game_uid,$league_id)
    {
        $this->db->select("S.season_id,S.home_id,S.away_id, S.year, S.api_week, S.week, S.season_game_uid, S.league_id,S.type, S.feed_date_time, S.season_scheduled_date, S.format, L.sports_id,L.league_abbr,S.2nd_inning_team_id")
                ->from(SEASON . " AS S")
                ->join(LEAGUE . " AS L", "L.league_id = S.league_id", "left");

        if(!empty($season_game_uid))
        {
            $this->db->where("S.season_game_uid", $season_game_uid);
        }
        if(!empty($league_id))
        {
            $this->db->where("S.league_id", $league_id);
        }
        $this->db->where("L.active", '1');
        $this->db->group_by("S.season_game_uid");
        $sql = $this->db->get();
        //echo $this->db->last_query();die;
        $matches = $sql->row_array();

        return $matches;
    }

    /**
     * Used for update match delay time
     * @param array $match_info
     * @return boolean
     */
    public function update_match_delay_time($match_info){
        
        if(!empty($match_info) && isset($match_info['collection_master_id']))
        {
            //Update schedule date in collection season table
            $this->db->where('collection_master_id', $match_info['collection_master_id']);
            $this->db->update(COLLECTION_SEASON,array("season_scheduled_date"=>$match_info['season_scheduled_date']));

            //Update schedule date in collection table
            $this->db->where('collection_master_id', $match_info['collection_master_id']);
            $this->db->update(COLLECTION_MASTER,array("season_scheduled_date"=>$match_info['season_scheduled_date']));

            //Update schedule date in contest table
            $this->db->where('is_2nd_inning',0);
            $this->db->where('collection_master_id', $match_info['collection_master_id']);
            $this->db->update(CONTEST,array("season_scheduled_date"=>$match_info['season_scheduled_date']));
        }

        return true;
    }

    /**
     * Used for delete cache and s3 files
     * @param array $data_arr
     * @return boolean
     */
    public function remove_cache_bucket_data($data_arr){
        if(isset($data_arr['cache']) && !empty($data_arr['cache'])){
            foreach($data_arr['cache'] as $cache_key){
                $this->delete_cache_data($cache_key);
            }
        }

        if(isset($data_arr['bucket']) && !empty($data_arr['bucket'])){
            foreach($data_arr['bucket'] as $file_name){
                $this->delete_s3_bucket_file($file_name.".json");
            }
        }

        return true;
    }

    public function get_ncaa_config_detail($ncaa_feed_providers = '') 
    {
        $this->config->load('sports_config');
        $feed_config = $this->config->item("ncaa_config");
        if ($ncaa_feed_providers != '') 
        {
            $feed_providers = $ncaa_feed_providers;
        }else 
        {
            $feed_providers = $this->config->item("ncaa_feed_providers");
        }

        if (isset($feed_providers) && isset($feed_config[$feed_providers])) 
        {
            return $feed = $feed_config[$feed_providers];
        }else 
        {
            exit("Invalid League");
        }
    }

    function get_app_config_value($module_key)
	{
		return  $enabled =  isset($this->app_config[$module_key])?$this->app_config[$module_key]['key_value']:0; 
	}

    function get_level_list()
    {
        $level_cache_key = "xp_levels";
        $level_list = $this->get_cache_data($level_cache_key);
        if(!$level_list)
        {
          $level_list = $this->db_user->select('level_pt_id,level_number,start_point,end_point',FALSE)
            ->from(XP_LEVEL_POINTS)
            ->where("end_point>",0)->get()->result_array();
          if(!empty($level_list))
          {
              $level_list = array_column($level_list,NULL,'level_pt_id');
          }
          $this->set_cache_data($level_cache_key,$level_list,REDIS_2_DAYS);
        }

        return $level_list;
    }

    /**
     * update order status to platform
     * 
     * @param int $order_detail order DETAILS 
     * @param array $update_data update data
     * @return bool Status updated or not
     */
    function _common_update_payout_tx_status($order_detail, $update_data = array()) {
        // print_r($update_data);exit;
        // order_id,transaction_id,source,user_id,winning_amount,date_added,payment_gateway_id
        $order_id = $order_detail['order_id'];
        $this->_common_update_transaction($update_data, $order_detail['transaction_id']);
        
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
                $this->_common_update_user_balance($order_detail["user_id"], $order_detail, 'add');

                $user_cache_key = "user_balance_".$order_detail["user_id"];
                $this->delete_cache_data($user_cache_key);
            }
    
            $source_id = 0;

            $this->_common_update_order_status($order_id, $status_type, $source_id, 'by your recent withdraw');

            $user_data = $this->db_user->select('user_name, email')
                ->from(USER)
                ->where(["user_id" => $order_detail["user_id"]])
                ->get()->row_array();
                
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
            else if($order_detail['payment_gateway_id']==1)
            {
                $msg_content["payment_option"] = 'Payumoney'; 
            }
            // SOME CONFUSING BECAUSE STATUS IN DB IS DIFFER
            if($status_type == 1 || $status_type == 5) {
                $notify_data["notification_type"] = 25; // 25-ApproveWithdrawRequest
            }
            
            if($status_type == 2 || $status_type == 4) {
                $notify_data["notification_type"] = 26; // 26-RejectWtihdrawRequest
            }
            $device_ids  = $this->get_device_ids_by_user_id($order_detail['user_id']);

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
    
    /**
     * Used to update transaction data
     * @param array $data
     * @param int $transaction_id
     * @return int
     */
    function _common_update_transaction($data, $transaction_id) {

        $this->db_user->where('transaction_id', $transaction_id)->update(TRANSACTION, $data);
	}
	

	/**
     * Function to Update user balance
     *  Params: $user_id,$real_balance,$bonus_balance
     *  
     */
    function _common_update_user_balance($user_id, $balance_arr, $oprator='add')
    {
        if(empty($balance_arr)){
            return false;
        }
        if(isset($balance_arr['real_amount']) && $balance_arr['real_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('balance', 'balance - '.$balance_arr['real_amount'], FALSE);
            }else{
                $this->db_user->set('balance', 'balance + '.$balance_arr['real_amount'], FALSE);
            }
            if(isset($balance_arr['source']) && $balance_arr['source'] == "7" && $oprator == 'add'){
                $this->db->set('total_deposit', 'total_deposit + '.$balance_arr['real_amount'], FALSE);
            }
        }
        if(isset($balance_arr['bonus_amount']) && $balance_arr['bonus_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('bonus_balance', 'bonus_balance - '.$balance_arr['bonus_amount'], FALSE);
            }else{
                $this->db_user->set('bonus_balance', 'bonus_balance + '.$balance_arr['bonus_amount'], FALSE);
            }
        }
        if(isset($balance_arr['winning_amount']) && $balance_arr['winning_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('winning_balance', 'winning_balance - '.$balance_arr['winning_amount'], FALSE);
            }else{
                $this->db_user->set('winning_balance', 'winning_balance + '.$balance_arr['winning_amount'], FALSE);
            }
            if(isset($balance_arr['source']) && $balance_arr['source'] == "3" && $oprator == 'add'){
                $this->db_user->set('total_winning', 'total_winning + '.$balance_arr['winning_amount'], FALSE);
            }
        }
        if(isset($balance_arr['points']) && $balance_arr['points'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('point_balance', 'point_balance - '.$balance_arr['points'], FALSE);
            }else{
                $this->db_user->set('point_balance', 'point_balance + '.$balance_arr['points'], FALSE);
            }
        }
        //for tds deduction net winning update on withdrawal
        if(isset($balance_arr['source']) && $balance_arr['source'] == "8" && $oprator == "add" && isset($balance_arr['custom_data'])){
            $custom_data = json_decode($balance_arr['custom_data'],TRUE);
            if(isset($custom_data['net_winning']) && $custom_data['net_winning'] > 0){
                $this->db_user->set('net_winning', 'net_winning + '.$custom_data['net_winning'], FALSE);
            }       
        }
        $this->db_user->where('user_id', $user_id);
        $this->db_user->update(USER);
        return $this->db_user->affected_rows();  
	}
	
	/**  Used to update order status from pending to failed or complete
     * @param int $order_id
     * @param int $status
     * @param int $source_id
     * @param string $reason
     * @return int
     */
    function _common_update_order_status($order_id, $status, $source_id = 0, $reason = '') {
        $data = array(
            "status" => $status,
            "source_id" => $source_id,
            "modified_date" => format_date(),
            "reason" => $reason
        );
        $this->db_user->where('order_id', $order_id)
                ->update(ORDER, $data);
    }

    /**
	 * Used for push s3 data in queue
	 * @param string $file_name json file name
	 * @param array $data api file data
	 * @return 
	 */ 
    public function push_s3_data_in_queue($file_name,$data = array(),$action="save"){
    	if(BUCKET_STATIC_DATA_ALLOWED == "0" || $file_name == ""){
			return false;
		}
		$bucket_data = array("file_name"=>$file_name,"data"=>$data,"action"=>$action);

        $this->load->helper('queue_helper');
        add_data_in_queue($bucket_data, 'bucket');
    }
    
    public function get_cfl_config_detail($cfl_feed_providers = '') {
        $this->config->load('sports_config');
        $feed_config = $this->config->item("cfl_config");
        if ($cfl_feed_providers != '') {
            $feed_providers = $cfl_feed_providers;
        } else {
            $feed_providers = $this->config->item("cfl_feed_providers");
        }

        if (isset($feed_providers) && isset($feed_config[$feed_providers])) {
            return $feed = $feed_config[$feed_providers];
        } else {
            exit("Invalid League");
        }
    }


    public function get_ncaa_basketball_config_detail($ncaa_basketball_feed_providers = '') {
        $this->config->load('sports_config');
        $feed_config = $this->config->item("ncaa_basketball_config");
        if ($ncaa_basketball_feed_providers != '') {
            $feed_providers = $ncaa_basketball_feed_providers;
        } else {
            $feed_providers = $this->config->item("ncaa_basketball_feed_providers");
        }

        if (isset($feed_providers) && isset($feed_config[$feed_providers])) {
            return $feed = $feed_config[$feed_providers];
        } else {
            exit("Invalid League");
        }
    }

    /**
     * Used for generate order unique id
     * @return string
     */
    public function _generate_order_unique_key() {
        $this->load->helper('security');
        $salt = do_hash(time() . mt_rand());
        $new_key = substr("o".$salt, 0, 10);
        return $new_key;
    }

    function get_all_user_with_device_ids() {
		$result = $this->db_user->select('U.user_id, U.user_name, IF(AL.device_type=1,GROUP_CONCAT(AL.device_id),"") device_ids,IF(AL.device_type=2,GROUP_CONCAT(AL.device_id),"") ios_device_ids,AL.device_type',false)
		->from(USER.' U')
		->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id')
		->where('status',1)
        ->where('AL.device_id IS NOT NULL')
		->group_by('U.user_id')	
		->get()->result_array();

		return $result;
    }

    function get_all_user_with_android_device_ids() {
		$result = $this->db_user->select('U.user_id, U.user_name, GROUP_CONCAT(AL.device_id) as device_ids',false)
		->from(USER.' U')
		->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id')
		->where('status',1)
		->where('AL.device_type',1)
        ->where('AL.device_id IS NOT NULL')
		->group_by('U.user_id')	
		->get()->result_array();
		return $result;
    }
    function get_all_user_with_ios_device_ids() {
		$result = $this->db_user->select('U.user_id, U.user_name, GROUP_CONCAT(AL.device_id) as ios_device_ids',false)
		->from(USER.' U')
		->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id')
		->where('status',1)
		->where('AL.device_type',2)
        ->where('AL.device_id IS NOT NULL')
		->group_by('U.user_id')	
		->get()->result_array();
		return $result;
    }
    
   
    public function get_device_ids_by_user_id($user_id)
    {
        $result = $this->db_user->select('GROUP_CONCAT(CASE WHEN AL.device_type = 1 THEN device_id  else NULL END SEPARATOR " , ") device_ids,GROUP_CONCAT(case when AL.device_type = 2 then device_id  else NULL end SEPARATOR " , ") ios_device_ids',false)
		->from(ACTIVE_LOGIN.' AL')
        ->where('AL.device_id IS NOT NULL')
        ->where('AL.user_id',$user_id)
        ->get()->row_array();
        return $result;
    }

    public function delete_common_cache_for_contest($input_param=array())
    {
        //delete user balance data
        $user_balance_cache_key = 'user_balance_';
        $this->delete_wildcard_cache_data($user_balance_cache_key);

        //delete contest cache
        if(!empty($input_param['contest_id']))
        {
            $contest_cache_key = "contest_" . $input_param['contest_id'];
            $this->delete_cache_data($contest_cache_key);
        }
        else
        {
            $contest_cache_key = "contest_";
            $this->delete_wildcard_cache_data($contest_cache_key);
        }
        

        //delete joined data
        if(!empty($input_param['collection_master_id']))
        {
            $user_contest_cache_key = 'user_contest_'.$input_param["collection_master_id"]."_";
            $this->delete_wildcard_cache_data($user_contest_cache_key);
        }
        else
        {
            $user_contest_cache_key = 'user_contest_';
            $this->delete_wildcard_cache_data($user_contest_cache_key);
        }


        //collection contest
        if(!empty($input_param['collection_master_id']))
        {
            $collection_contest_cache_key = "collection_contest_" . $input_param["collection_master_id"];
            $this->delete_cache_data($collection_contest_cache_key);
        }
        else
        {
            $collection_contest_cache_key = "collection_contest_";
            $this->delete_wildcard_cache_data($collection_contest_cache_key);
        } 



        $a_reverse = isset($this->app_config['allow_reverse_contest'])?$this->app_config['allow_reverse_contest']['key_value']:0;
        if($a_reverse)
        {
            if(!empty($input_param['collection_master_id']))
            {

                $collection_contest_cache_key = "collection_contest_r_" . $input_param["collection_master_id"];
                $this->delete_cache_data($collection_contest_cache_key);
            }
            else
            {
                $collection_contest_cache_key = "collection_contest_r_" ;
                $this->delete_wildcard_cache_data($collection_contest_cache_key);
            }
        }

        if(!empty($input_param['collection_master_id']))
        {
            $collection_contest_cache_key = "collection_pin_contest_" . $input_param["collection_master_id"];
            $this->delete_cache_data($collection_contest_cache_key);
        }
        else
        {
            $collection_contest_cache_key = "collection_pin_contest_";
            $this->delete_wildcard_cache_data($collection_contest_cache_key);
        }    


        if($a_reverse)
        {
            if(!empty($input_param['collection_master_id']))
            {
                $collection_contest_cache_key = "collection_pin_contest_r_" . $input_param["collection_master_id"];
                $this->delete_cache_data($collection_contest_cache_key);
            }
            else
            {
                $collection_contest_cache_key = "collection_pin_contest_r_";
                $this->delete_wildcard_cache_data($collection_contest_cache_key);
            } 

        }


         //delete cache
        if(!empty($input_param['collection_master_id']))
        {
            $user_teams_cache_key = "user_teams_".$input_param['collection_master_id']."_";
            $this->delete_wildcard_cache_data($user_teams_cache_key);
        }
        else
        {
            $user_teams_cache_key = "user_teams_";
            $this->delete_wildcard_cache_data($user_teams_cache_key);
        }
        
        return true;
    }

   

    //Insert update function for sports data insertion 
    public function insert_or_update_on_exist($key_array,$data,$concat_key,$table,$update_key,$update_ignore = array(),$where = '')
    {
        //echo "<pre>";print_r($insert_data);die;
        $db_obj = $this->db;

        $update_data = array();
        $insert_data = array();
         //check if league already exist
        $db_obj->select("$update_key,$concat_key as data_key",FALSE)
            ->from($table);
        if(!empty($where)){
            $db_obj->where($where);
        }else{
            $db_obj->where_in("$concat_key", $key_array);
        }
        $sql = $db_obj->get();
        $existing_data = $sql->result_array();

        if(!empty($existing_data))
        {
            $existing_data = array_column($existing_data,NULL,'data_key');
            foreach($data as $key => $value)
            {
                if(isset($existing_data[$key]))
                {
                    $value[$update_key] = $existing_data[$key][$update_key];
                    //for ignore update manual info
                    if(!empty(($update_ignore)))
                    {
                        foreach ($update_ignore as $ingore_key => $ingore_value) 
                        {
                            unset($value[$ingore_value]);
                        }
                    } 
                    $update_data[] = $value; 
                }else
                {
                    $insert_data[] = $value;
                }
            }
        }else
        {
            $insert_data = $data;
        }
        //echo "<pre>";print_r($update_data);
        //echo "<pre>";print_r($insert_data);die;
        if(!empty($update_data))
        {
            $update_data = array_values($update_data);
            $db_obj->update_batch($table, $update_data, $update_key);
        }
        if(!empty($insert_data))
        {
            $insert_data = array_values($insert_data);
            //echo "<pre>";print_r($insert_data);die;
            $this->insert_ignore_into_batch($table, $insert_data);
       }

        return true;
    }

    public function remove_user_balance_cache($ids){
        $total = count($ids);
        if($total > 50){
            $this->delete_wildcard_cache_data('user_balance_');
        }else{
            foreach($ids as $id){
                $cache_key = 'user_balance_'.$id;
                $this->delete_cache_data($cache_key);
            }
        }
        return true;
    }

    public function get_motorsport_config_detail($motorsport_feed_providers = '') 
    {
        $this->config->load('sports_config');
        $feed_config = $this->config->item("motorsport_config");
        if ($motorsport_feed_providers != '') {
            $feed_providers = $motorsport_feed_providers;
        } else {
            $feed_providers = $this->config->item("motorsport_feed_providers");
        }

        if (isset($feed_providers) && isset($feed_config[$feed_providers])) {
            return $feed = $feed_config[$feed_providers];
        } else {
            exit("Invalid League");
        }
    }

    public function get_tennis_config_detail($tennis_feed_providers = '') 
    {
        $this->config->load('sports_config');
        $feed_config = $this->config->item("tennis_config");
        if ($tennis_feed_providers != '') {
            $feed_providers = $tennis_feed_providers;
        } else {
            $feed_providers = $this->config->item("tennis_feed_providers");
        }

        if (isset($feed_providers) && isset($feed_config[$feed_providers])) {
            return $feed = $feed_config[$feed_providers];
        } else {
            exit("Invalid League");
        }
    }

    public function get_team_id_with_team_uid($sports_id,$team_uid = '',$team_uid_array = array())
    {
        $this->db->select("team_id,team_uid");
        $this->db->from(TEAM);
        if($team_uid != "") 
        {
            $this->db->where("team_uid",$team_uid);
        }
        elseif(!empty($team_uid_array)) 
        {
            $this->db->where_in("team_uid",$team_uid_array);
        }
        $this->db->where("sports_id",$sports_id);
        $query = $this->db->get();
        $team_data = $query->result_array();
        //echo "<pre>";print_r($team_data);die;
        $team_ids = array();
        if(!empty($team_data))
        {
            $team_ids = array_column($team_data, "team_id","team_uid");
        }
        return $team_ids;
    }

    public function get_player_data_by_player_uid($season_id,$sports_id,$match_player_uid) 
    {
        $rs = $this->db->select("P.player_id, P.sports_id, P.player_uid,PT.is_published,PT.is_new,PT.feed_verified,PT.salary", FALSE)
                ->from(PLAYER . " AS P")
                 ->join(PLAYER_TEAM . " AS PT", "PT.player_id = P.player_id AND PT.season_id = '".$season_id."' ", 'LEFT')
                ->where("P.sports_id", $sports_id)
                ->where("P.sports_id", $sports_id)
                ->where_in("P.player_uid", $match_player_uid)
                ->get();
        $res = $rs->result_array();
        return $res;
    }

    public function get_player_data_by_season_id($sports_id,$season_id)
    {
        $rs = $this->db->select("P.player_id,P.player_uid", FALSE)
                ->from(PLAYER . " AS P")
                ->join(PLAYER_TEAM . " AS PT", "PT.player_id = P.player_id AND PT.season_id = '".$season_id."' ", 'LEFT')
                ->where("P.sports_id", $sports_id)
                ->get();
        $player_data = $rs->result_array();

        $player_ids = array();
        if(!empty($player_data))
        {
            $player_ids = array_column($player_data, "player_id","player_uid");
        }
        return $player_ids;
    }

    /**
     * function used for get season list for player point calculation
     * @param int $sports_id
     * @return array
     */
    public function get_season_match_for_calculate_points($sports_id = '') 
    {
        $current_date_time = format_date();
        $past_time = date("Y-m-d H:i:s", strtotime($current_date_time . " -".MATCH_SCORE_CLOSE_DAYS." days"));
        $close_date_time = date("Y-m-d H:i:s", strtotime($current_date_time . " -".CONTEST_CLOSE_INTERVAL." minutes"));
        $this->db->select("S.home_id,S.away_id, S.year,  S.season_game_uid,S.season_id, S.league_id, S.feed_date_time, S.season_scheduled_date, S.format, S.type, L.sports_id,S.2nd_inning_team_id")
                ->from(SEASON . " AS S")
                ->join(LEAGUE . " AS L", "L.league_id = S.league_id", "INNER")
                ->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.active = 1")
                ->where("L.active", '1')
                ->where("S.match_status", '0')
                ->where("S.season_scheduled_date <='".$current_date_time."'")
                ->where("(S.match_closure_date IS NULL OR S.match_closure_date > '".$close_date_time."')")
                ->where("S.season_scheduled_date >= ", $past_time);
         if (!empty($sports_id)) {
            $this->db->where("L.sports_id", $sports_id);
        }
        
       //$this->db->where("S.season_id", '10875');

        $this->db->group_by("S.season_id");
        $sql = $this->db->get();
        
        $matches = $sql->result_array();
        return $matches;
    }

    /**
     * function used for get season list for score update
     * @param int $sports_id
     * @return array
     */
    public function get_season_match_for_score($sports_id = '',$season_game_uid = '')
    {
        $current_date_time = format_date();
        $past_time = date("Y-m-d H:i:s", strtotime($current_date_time . " -".MATCH_SCORE_CLOSE_DAYS." days"));
        $this->db->select("S.home_id,S.away_id,S.year,S.season_id,S.season_game_uid, S.league_id,S.type, S.feed_date_time, S.season_scheduled_date, S.format, L.sports_id,L.league_abbr,T1.team_uid AS home_uid,T2.team_uid AS away_uid,S.week,S.2nd_inning_date,S.second_inning_update")
            ->from(SEASON." AS S")
            ->join(LEAGUE." AS L", "L.league_id = S.league_id", "INNER")
            ->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.active = 1")
            ->join(TEAM." AS T1", "T1.team_id = S.home_id", "INNER")
            ->join(TEAM." AS T2", "T2.team_id = S.away_id", "INNER")
            ->where("S.season_scheduled_date <='" . $current_date_time . "'")
            ->where("S.season_scheduled_date >= ", $past_time);

        if(!empty($sports_id))
        {
            $this->db->where("L.sports_id", $sports_id);
        }
        if(!empty($season_game_uid))
        {
            $this->db->where("S.season_game_uid", $season_game_uid);
        }
        $this->db->where("L.active", '1');
        $this->db->where("S.match_status", '0');
        $this->db->where_in("S.status", array(0,1,3));
        $this->db->group_by("S.season_id");
        $sql = $this->db->get();
        //echo $this->db->last_query();die;
        $result = $sql->result_array();
        return $result;
    }


    public function update_league_last_date($league_ids)
    {
        if(!empty($league_ids))
        {
           foreach ($league_ids as $key => $league_id) 
            {
                $sql  = " UPDATE ".$this->db->dbprefix(LEAGUE)."
                          SET
                            league_last_date = (SELECT DATE_ADD(season_scheduled_date, INTERVAL 10 DAY) FROM ".$this->db->dbprefix(SEASON)." WHERE league_id = ".$league_id." ORDER BY season_scheduled_date DESC LIMIT 1 )
                          WHERE 
                            league_id = ".$league_id."
                          AND
                            ( league_last_date <= (SELECT season_scheduled_date FROM ".$this->db->dbprefix(SEASON)." WHERE league_id = ".$league_id."   ORDER BY season_scheduled_date DESC LIMIT 1) OR league_last_date IS NULL )

                        ";
                $this->db->query($sql);
            }
        }
        return ;    
        
    }


}    

/* End of file MY_Model.php */
/* Location: application/core/MY_Model.php */