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

    /**
     * getting affiliate detail to show in both affiliate and admin side
     */
    public function get_aff_details($campaign_id,$type=1)
	{
        $aff_data = array();
		if($type==2)
		{
			$affiliate_id = $this->get_single_row('affiliate_id,url,name,source,medium',CAMPAIGN,["campaign_id"=>$campaign_id]);	
            $aff_data['url']  = $affiliate_id['url'];
            $aff_data['source']  = $affiliate_id['source'];
            $aff_data['medium']  = $affiliate_id['medium'];
            $aff_data['campaign_name']  = $affiliate_id['name'];
			$affiliate_id = $affiliate_id['affiliate_id'];
            
            //getting affiliate detail and commission
            $signup 		= "JSON_EXTRACT(C.commission, '$.signup')";
            $deposit 		= "JSON_EXTRACT(C.commission, '$.deposit_per')";
            $deposit_cap 	= "JSON_EXTRACT(C.commission, '$.deposit_cap')";
            $game 			= "JSON_EXTRACT(C.commission, '$.game_per')";
            
            $select = "A.name,A.email,A.mobile,C.source,C.medium,C.name campaign_name,($signup*1) register_comm,($deposit*1) deposit_comm,($deposit_cap*1) deposit_cap,($game*1) game_comm";
		}else{
            $affiliate_id = $campaign_id;
            $select = "A.name,A.email,A.mobile";
        }
		
        // ,IF(CH.TYPE=2,SUM(CH.commission),0) deposit_comm,IF(CH.TYPE=1,SUM(CH.commission),0) register_comm ,IF(CH.TYPE=3,SUM(CH.commission),0) game_comm
        $aff = $this->db->select($select)
		->from(AFFILIATE.' A')
		->where(["A.affiliate_id"=>$affiliate_id]);
		if($type==2)
        {
            $this->db->where('C.campaign_id',$campaign_id)
            ->join(CAMPAIGN.' C','A.affiliate_id=C.affiliate_id','INNER');
        }
		$aff = $this->db->get()->row_array();	
		return $aff;
	}
}
/* End of file MY_Model.php */
/* Location: application/core/MY_Model.php */
