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
    
    /*
     * to get merchandise list
     * @param void
     * @return array
     */
    public function get_merchandise_list($ids_arr = array())
    {
        $this->db->select('merchandise_id,name,image_name,price')
                ->from(MERCHANDISE)
                ->order_by("merchandise_id","ASC");

        if(isset($ids_arr) && !empty($ids_arr)){
            $this->db->where_in("merchandise_id",$ids_arr);
        }
        $result = $this->db->get()->result_array();
        return $result;
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

    public function get_collection_details($collection_id)
    {
        $sql = $this->db->select('category_id,name,published_date,scheduled_date,end_date')
                        ->from(COLLECTION)
                        ->where('collection_id',$collection_id);
                        
        $sql = $sql->get();
        $result = $sql->row_array();
        return $result;
    }

    public function get_cricket_config_detail($cricket_feed_providers = '') 
    {
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

    /**
     * used to get match details by ids
     * @param array $post_data
     * @return array
    */
    public function get_season_detail_by_ids($post_data) {

        $this->db->select("S.league_id, S.season_game_uid,S.format,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,S.season_scheduled_date,S.season_scheduled_date,IFNULL(T1.feed_flag,T1.flag) AS home_flag,IFNULL(T2.feed_flag,T2.flag) AS away_flag,IFNULL(L.league_display_name,L.league_name) AS league_abbr,S.delay_minute as dm,S.delay_message as dmsg,S.custom_message as cmsg,S.scoring_alert as sa,S.is_pin_fixture as pin,S.status as match_status,S.is_live_score,S.score_data", FALSE);
        $this->db->from(SEASON." AS S");
        $this->db->join(LEAGUE." AS L", "L.league_id = S.league_id", "INNER");
        $this->db->join(TEAM.' as T1', 'T1.team_uid = S.home_uid', "INNER");
        $this->db->join(TEAM.' as T2', 'T2.team_uid = S.away_uid', "INNER");

        if(isset($post_data['sports_id']) && !empty($post_data['sports_id']) )
        {
            $this->db->where("L.sports_id", $post_data['sports_id']);
        }
        if(isset($post_data['game_keys']) && is_array($post_data['game_keys']) && !empty($post_data['game_keys'])) {
            $this->db->where_in("CONCAT_WS('_',S.league_id,S.season_game_uid)", $post_data['game_keys']);
        }
        $this->db->group_by("S.season_game_uid");
        $this->db->order_by("S.season_scheduled_date", "ASC");
        $sql = $this->db->get();
        return $sql->result_array();
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

    public function get_master_odds(){
        $master_odds = $this->db->select('*')
                ->from(MASTER_ODDS)
                ->get()->result_array();
        return $master_odds;
    }

    public function update_total_points_rank($collection_id){
        //total scores update
        $sql = $this->db->select('UT.user_team_id,UT.user_id')
                        ->from(USER_TEAM." AS UT")
                        ->where('UT.collection_id', $collection_id)
                        ->group_by('UT.user_team_id')
                        ->get();
        $user_team_ids = $sql->result_array();
        if(!empty($user_team_ids)){
          $ids = array_column($user_team_ids, 'user_team_id');
          $update_sql = " UPDATE  ".$this->db->dbprefix(USER_TEAM)." AS UT 
                        INNER JOIN ".$this->db->dbprefix(USER_CONTEST)." AS UC ON UC.user_team_id=UT.user_team_id AND UC.fee_refund=0 
                        INNER JOIN 
                        ( SELECT SUM(UP.points) AS scores,SUM(UP.answer_time) AS total_time,COUNT(UP.predict_id) as total_answer,UP.user_team_id FROM ".$this->db->dbprefix(USER_PREDICTION)." AS UP
                            WHERE 
                                UP.user_team_id IN (".implode(',', $ids).")
                            GROUP BY 
                                UP.user_team_id
                            ) AS L_PQ ON L_PQ.user_team_id = UT.user_team_id 
                        SET 
                            UT.total_score = IFNULL(L_PQ.scores,'0.00'), 
                            UC.total_score = IFNULL(L_PQ.scores,'0.00'), 
                            UC.total_time = IFNULL(L_PQ.total_time,'0.00'),
                            UC.total_answer = IFNULL(L_PQ.total_answer,'0') 
                        WHERE UT.collection_id=".$collection_id."
                        ";
          $this->db->query($update_sql);
        }

        //update contest wise rank
        $sql = $this->db->select('C.contest_id,C.is_tie_breaker')
                        ->from(CONTEST." AS C")
                        ->join(USER_CONTEST." AS UC", "UC.contest_id = C.contest_id", "INNER")
                        ->where('C.collection_id', $collection_id)
                        ->where('C.total_user_joined >= C.minimum_size',NULL)
                        ->where('C.status !=',"1")
                        ->group_by('C.contest_id')
                        ->get();
        $contest_data = $sql->result_array();
        //print_r($contest_data);die;
        foreach($contest_data as $contest){
          $rank_str = "";
          if(isset($contest['is_tie_breaker']) && $contest['is_tie_breaker'] == 1){
            $rank_str = ",user_contest_id ASC";
          }
          //update rank during score
          $update_rank_sql = "UPDATE ".$this->db->dbprefix(USER_CONTEST)." AS UC 
                              INNER JOIN 
                                  (SELECT UC1.user_contest_id,RANK() OVER (ORDER BY `total_score` DESC, total_answer DESC, total_time ASC ".$rank_str.") user_rank 
                                   FROM ".$this->db->dbprefix(USER_CONTEST)." AS UC1 
                                   WHERE UC1.contest_id = ".$contest['contest_id'].") AS L_PQ 
                              ON L_PQ.user_contest_id = UC.user_contest_id 
                              SET 
                                  UC.game_rank = IFNULL(L_PQ.user_rank,'1')
                              WHERE UC.fee_refund=0 AND UC.contest_id='".$contest['contest_id']."'";
          $this->db->query($update_rank_sql);
        }
        return true;
    }

}
/* End of file MY_Model.php */
/* Location: application/core/MY_Model.php */
