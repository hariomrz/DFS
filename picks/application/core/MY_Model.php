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
	function get_all_table_data($select = '*', $table, $where = "",$order_by = "") {
		$this->db->select($select);
		$this->db->from($table);
		if ($where != "") {
			$this->db->where($where);
		}
        if($order_by != "" && is_array($order_by)) {
            foreach($order_by as $key=>$type){
                $this->db->order_by($key,$type);
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
    function get_single_row($select = '*', $table, $where = "",$order_by = "") {
        $this->db->select($select, FALSE);
        $this->db->from($table);
        if ($where != "") {
            $this->db->where($where);
        }
        if($order_by != "" && is_array($order_by)) {
            foreach($order_by as $key=>$type){
                $this->db->order_by($key,$type);
            }
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
            $return_flag = $this->db->affected_rows();
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

    public function get_all_group_list($data=array())
    {
        $sql = $this->db->select('group_id,group_name,description,icon')
                        ->from(MASTER_GROUP)
                        ->where('status','1')
                        ->order_by('sort_order','ASC');
        if(isset($data['type']) && $data['type'] == "admin"){
            $sql->where_in('is_private','0');
        }
        $sql = $sql->get();
        $result = $sql->result_array();
        return $result;
    }

    /**
     * Used for generate order unique id
     * @return string
     */
    public function _generate_order_unique_key() {
        $this->load->helper('security');
        $salt = do_hash(time().mt_rand());
        $new_key = substr("o".$salt, 0, 10);
        return $new_key;
    }

    public function insert_or_update_on_exist($key_array,$data,$concat_key,$table,$update_key,$update_ignore = array())
    {
        //echo "<pre>";print_r($insert_data);die;
        $db_obj = $this->db;

        $update_data = array();
        $insert_data = array();
        //check if league already exist
        $db_obj->select("$update_key,$concat_key as data_key",FALSE)
            ->from($table)
            ->where_in("$concat_key", $key_array);
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
    * used to get lobby match details by ids
    * @param array $post_data
    * @return array
    */
    public function get_lobby_season_detail($post_data) {

        $current_date_time = format_date();
        $this->db->select("S.season_id,S.home_id,S.away_id,T1.team_abbr AS home,T2.team_abbr AS away,S.season_game_uid,S.scheduled_date,L.league_name,T1.flag AS home_flag,IFNULL(S.score_data,'') as score_data,S.delay_minute,S.delay_message,L.league_id,S.status,S.status_overview,S.is_pin_fixture", FALSE);

        $this->db->select("T2.flag AS away_flag,T2.team_abbr AS away", false);
        
        $this->db->from(SEASON . " AS S");
        $this->db->join(LEAGUE . " AS L", "L.league_id = S.league_id", "INNER");
        $this->db->join(TEAM_LEAGUE . ' TL1', 'TL1.team_id = S.home_id AND TL1.league_id = S.league_id', 'INNER');
        $this->db->join(TEAM . ' as T1', 'T1.team_id = TL1.team_id', "INNER");
        $this->db->join(TEAM_LEAGUE . ' TL2', 'TL2.team_id = S.away_id AND TL2.league_id = S.league_id', "INNER");
        $this->db->join(TEAM . ' as T2', 'T2.team_id = TL2.team_id', "INNER");


        if(isset($post_data['league_id']) && !empty($post_data['league_id']) )
        {
            $this->db->where("L.league_id", $post_data['league_id']);
        }

        if(isset($post_data['sports_id']) && !empty($post_data['sports_id']) )
        {
            $this->db->where("L.sports_id", $post_data['sports_id']);
        }

        if(isset($post_data['league_ids']) && !empty($post_data['league_ids']) )
        {
            $this->db->where_in("L.league_id", $post_data['league_ids']);
        }

        $this->db->where("L.status", 1);
        
        $this->db->where("S.season_id", $post_data['season_id']);
        

        $this->db->group_by("S.season_id");
        $this->db->order_by("S.scheduled_date", "ASC");
        $sql = $this->db->get();
        return $sql->row_array();
    }
}
/* End of file MY_Model.php */
/* Location: application/core/MY_Model.php */
