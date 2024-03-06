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
	function get_all_table_data ($select = '*', $table, $where = "") {
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
     * for get sports list
     * @param
     * @return array
     */
    public function get_sports_list()
    { $this->fantasy_db = $this->load->database('fantasy_db', TRUE);
        $result = $this->fantasy_db->select('MS.sports_id,MSF.en_display_name,MSF.hi_display_name,MSF.guj_display_name,MS.sports_name,MS.team_player_count,MS.max_player_per_team')
                ->from(MASTER_SPORTS." MS")
                ->join(MASTER_SPORTS_FORMAT." MSF","MSF.sports_id=MS.sports_id")
                ->where('MS.active', '1')
                ->where('MSF.status', '1')
                ->order_by("MS.order","ASC")
                ->get()
                ->result_array();
        return $result;
    }
}
/* End of file MY_Model.php */
/* Location: application/core/MY_Model.php */