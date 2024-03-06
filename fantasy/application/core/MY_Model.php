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

    function get_app_config_value($module_key)
    {
        $enabled =  isset($this->app_config[$module_key])?$this->app_config[$module_key]['key_value']:0;
        return  $enabled;
    }

    /**
     * for get sports list
     * @param
     * @return array
     */
    public function get_sports_list()
    {
        $result = $this->db->select('MS.sports_id,MSF.en_display_name,MSF.hi_display_name,MSF.guj_display_name,MSF.fr_display_name,MSF.ben_display_name,MSF.pun_display_name,MS.sports_name,MS.team_player_count,MS.max_player_per_team')
                ->from(MASTER_SPORTS." MS")
                ->join(MASTER_SPORTS_FORMAT." MSF","MSF.sports_id=MS.sports_id")
                ->where('MS.active', '1')
                ->where('MSF.status', '1')
                ->order_by("MS.order","ASC")
                ->get()
                ->result_array();
        return $result;
    }

    /*
     * used for get master position list
     * @param int $sports_id
     * @return array
    */
    public function get_master_position($sports_id)
    {
        $sql = $this->db->select('master_lineup_position_id,position_name as position, position_name, position_display_name,number_of_players,max_player_per_position,position_order')
            ->from(MASTER_LINEUP_POSITION)
            ->where('sports_id',$sports_id) 
            ->order_by('position_order','ASC');
        if(!in_array($sports_id, array(1))){
            $sql->where('position_name = allowed_position'); // to avoid FLEX position
        }
        $sql = $sql->get();
        $result = $sql->result_array();
        return ($result) ? $result : array();
    }

    /**
     * to get merchandise list
     * @param void
     * @return array
     */
    public function get_merchandise_list($ids_arr = array())
    {
        $this->db->select('merchandise_id,name,image_name')
                ->from(MERCHANDISE)
                ->order_by("merchandise_id","ASC");

        if(isset($ids_arr) && !empty($ids_arr)){
            $this->db->where_in("merchandise_id",$ids_arr);
        }
        $result = $this->db->get()->result_array();
        return $result;
    }

    public function get_all_group_list()
    {
        $this->db->select('group_id,group_name,description,icon')
                        ->from(MASTER_GROUP)
                        ->where('status','1')
                        ->order_by('sort_order','ASC');
        $sql = $this->db->get();
        $result = $sql->result_array();
        return $result;
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
        $this->db->select("S.season_id,S.season_game_uid,S.league_id,S.format,S.season_scheduled_date,S.playing_announce,S.delay_minute,IFNULL(S.delay_message,'') as delay_message,IFNULL(S.custom_message,'') as custom_message,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IFNULL(T1.display_team_name,T1.team_name) AS home_name,IFNULL(T2.display_team_name,T2.team_name) AS away_name,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag",false)
            ->from(SEASON." AS S")
            ->join(TEAM.' T1','T1.team_id = S.home_id','INNER')
            ->join(TEAM.' T2','T2.team_id = S.away_id','INNER')
            ->where_in("S.season_id",$season_ids)
            ->group_by("S.season_id")
            ->order_by("S.season_scheduled_date","ASC");

        $sql = $this->db->get();
        $result = $sql->result_array();
        return ($result) ? $result : array();
    }

    /**
     * Used for get tour game season detail by season id
     * @param int $season_id
     * @return array
     */
    public function get_tour_season_detail($season_ids)
    {
        if(empty($season_ids)){
            return false;
        }
        $this->db->select("S.season_id,S.league_id,S.scheduled_date,S.delay_minute,IFNULL(S.delay_message,'') as delay_message,IFNULL(S.custom_message,'') as custom_message,S.tournament_name,S.match_event,IFNULL(S.end_scheduled_date,S.season_scheduled_date) as end_scheduled_date",false)
            ->from(SEASON." AS S")
            ->where_in("S.season_id",$season_ids)
            ->group_by("S.season_id")
            ->order_by("S.scheduled_date","ASC");
        $sql = $this->db->get();
        $result = $sql->result_array();
        return ($result) ? $result : array();
    }

    /**
     * used to get fixture detail
     * @param int $collection_master_id
     * @return array
    */
    public function get_fixture_detail($cm_id) {
        if(!$cm_id){
            return false;
        }

        $this->db->select("CM.collection_master_id,CM.league_id,CM.collection_name,CM.season_scheduled_date,IFNULL(CM.2nd_inning_date,'') as 2nd_inning_date,CM.status,CM.season_game_count,CM.is_pin,CM.is_gc,CM.is_tour_game,CM.is_h2h,GROUP_CONCAT(DISTINCT CS.season_id) as season_ids,IFNULL(L.league_display_name,L.league_name) AS league_name,IFNULL(L.image,'') as league_image,L.is_featured,L.ldb,L.sports_id,IFNULL(CM.setting,'[]') as setting,IF(ISNULL(CM.setting),0,1) as is_dm", FALSE);
        $this->db->from(COLLECTION_MASTER." as CM");
        $this->db->join(COLLECTION_SEASON.' as CS', 'CM.collection_master_id = CS.collection_master_id', "INNER");
        $this->db->join(LEAGUE.' as L', 'L.league_id = CM.league_id', "INNER");
        $this->db->where("CM.collection_master_id", $cm_id);
        $this->db->group_by("CM.collection_master_id");
        $result = $this->db->get()->row_array();
        return $result;
    }
}
/* End of file MY_Model.php */
/* Location: application/core/MY_Model.php */