<?php 
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class MY_Model extends CI_Model {

	/**
    * Class constructor
    * load fantasy db database.
    * @return	void
    */
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
		$this->load->database();
	}

    /**
    * Class destructor
    * Closes the connection to user db if present
    * @return	void
    */
    function __destruct() {
        if(isset($this->db->conn_id)) {
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
	function get_all_table_data($select = '*', $table, $where = "", $order_by = "") {
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
     * common function used to delete record from any table
     * @param string    $table
     * @param array/string $condition
     * @return  array
     */
    public function save_record($table, $data) {
        if(empty($data)){
            return false;
        }

        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

	/**
     * Updates whole row [unlike update_field()]
     * @param array $data
     * @param int   $id
     */
    public function update($table = "", $data, $where = "") {
        if (!is_array($data)) {
            log_message('error', 'Supposed to get an array!');
            return FALSE;
        } else if ($table == "") {
            log_message('error', 'Got empty table name');
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
        $result = $this->db->affected_rows();
        return $result;
    }

    //Insert update function for sports data inswertion 
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

    /**
     * Used for load cache driver
     * @return 
     */
    private function init_cache_driver(){
        $this->load->driver('cache', array('adapter' => CACHE_ADAPTER, 'backup' => 'file'));
    }

    /**
     * Used for get cache data by key
     * @param string $cache_key cache key
     * @return array
     */
    public function get_cache_data($cache_key){
        if(!$cache_key || !CACHE_ENABLE){
            return false;
        }

        $this->init_cache_driver();
        $cache_key = CACHE_PREFIX.$cache_key;
        $cache_data = $this->cache->get($cache_key);
        if(is_array($cache_data)){
            return $cache_data;
        }else{
            return array();
        }
    }

    /**
     * Used for save cache data by key
     * @param string $cache_key cache key
     * @param array $data_arr cache data
     * @param int $expire_time cache expire time
     * @return boolean
     */
    public function set_cache_data($cache_key,$data_arr,$expire_time=3600){
        if(!$cache_key || !CACHE_ENABLE){
            return false;
        }

        $this->init_cache_driver();
        $cache_key = CACHE_PREFIX.$cache_key;
        $this->cache->save($cache_key, $data_arr, $expire_time);
        return true;
    }

    /**
     * Used for delete cache data by key
     * @param string $cache_key cache key
     * @return boolean
     */
    public function delete_cache_data($cache_key){
        if(!$cache_key || !CACHE_ENABLE){
            return false;
        }

        $this->init_cache_driver();
        $delete_cache_key = CACHE_PREFIX.$cache_key;
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

    /**
     * Used for get season detail by season id
     * @param int $season_id
     * @return array
     */
    public function get_fixture_season_detail($season_ids,$select="")
    {
        if(empty($season_ids)){
            return false;
        }

        if($select != ""){
            $this->db->select($select,false);    
        }
        $this->db->select("S.season_id,S.format,S.scheduled_date,S.delay_minute,IFNULL(S.delay_message,'') as delay_message,S.winning_team_id,S.status,IFNULL(S.custom_message,'') as custom_message,IFNULL(T1.display_abbr,T1.team_abbr) AS home,IFNULL(T2.display_abbr,T2.team_abbr) AS away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,S.home_id,S.away_id,S.score_data,IFNULL(T1.display_name,T1.team_name) AS home_name,IFNULL(T2.display_name,T2.team_name) AS away_name",false)
            ->from(SEASON." AS S")
            ->join(TEAM.' T1','T1.team_id = S.home_id','INNER')
            ->join(TEAM.' T2','T2.team_id = S.away_id','INNER')
            ->where_in("S.season_id",$season_ids)
            ->group_by("S.season_id")
            ->order_by("S.scheduled_date","ASC");

        $sql = $this->db->get();
        $result = $sql->result_array();
        return ($result) ? $result : array();
    }

    /**
     * Used for get season detail by season id
     * @param int $tournament_id
     * @return array
     */
    public function get_team_prediction_percent($tournament_id,$season_ids)
    {
       return $this->db->select("UT.season_id,count(UT.user_team_id) as total_season_count,
            sum(case when S.home_id = UT.team_id then 1 else 0 end) as home_count, 
            sum(case when S.away_id = UT.team_id then 1 else 0 end) as away_count, 
            sum(case when UT.team_id = 0 then 1 else 0 end) as draw_count ")
            ->from(USER_TOURNAMENT. " UTO")
            ->join(USER_TEAM." UT","UT.user_tournament_id=UTO.user_tournament_id")
            ->join(SEASON." S","S.season_id=UT.season_id")
            ->where('UTO.tournament_id',$tournament_id)
            ->where_in('UT.season_id',$season_ids)
            ->group_by('UT.season_id')
            ->get()->result_array();
    }

}
/* End of file MY_Model.php */
/* Location: application/core/MY_Model.php */