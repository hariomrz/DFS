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
       $_POST = $this->input->post();         
        
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

    public function get_all_group_list()
    {
        $sql = $this->db->select('group_id,group_name,description,icon')
                        ->from(MASTER_GROUP)
                        ->where('status','1')
                        ->order_by('sort_order','ASC');
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


    public function get_category_list() {

        $stock_type = 1;
        $post_data = $this->input->post();
        if(!empty($post_data['stock_type']) && $post_data['stock_type'] =='2')
        {
            $stock_type = 2;
        }
        $category_cache_key = "st_cat_".$stock_type;
        $category_list = $this->get_cache_data($category_cache_key);
        if(empty($category_list)) {
            $sql = $this->db->select('category_id,name')
                        ->from(CONTEST_CATEGORY);
                        
            $post_data = $this->input->post();
            if($stock_type==2)
            {
                $this->db->where('category_id',1);
            }

            $sql = $sql->get();
            $category_list = $sql->result_array();
            $this->set_cache_data($category_cache_key,$category_list,28800);
        }
        
        return $category_list;
    }

    public function get_market_list($market_id='',$single_record=0)
    {
        $sql = $this->db->select('market_id,name')
                        ->from(MARKET)
                        ->where('status',1);

        if($market_id)
        {
            $this->db->where('market_id',$market_id);
        }
             
        if($single_record)
        {
            $result = $this->db->get()->row_array();
        }
        else{
            $sql = $sql->get();
            $result = $sql->result_array();
        }
        return $result;
    }

    public function get_stock_type_config($stock_type=1)
    {
        $sql = $this->db->select('type,config_data,stock_limit')
                        ->from(STOCK_TYPE)
                        ->where('status',1);

                        $this->db->where('type',$stock_type);
        
                        $result = $this->db->get()->row_array();
        return $result;
    }

     /** 
     * common function used to get players list by player_uid
     * @param array $player_uid_array
     * @return array
     */
    public function get_stocks_detail_by_id($post_data)
    {
        if(empty($post_data))
        {
            return array(); 
        }
//(TRUNCATE((CASE WHEN C.status=1 THEN SH1.close_price ELSE S.last_price END), 2) - IFNULL(SH.close_price,0))
        $collection_id = isset($post_data['collection_id']) ? $post_data['collection_id'] : 0;
        $published_date = date('Y-m-d',strtotime($post_data['published_date']));
		$end_date = date('Y-m-d',strtotime($post_data['end_date']));
        $scheduled_date_time = $post_data['scheduled_date'];	
		$scheduled_date = date('Y-m-d',strtotime($scheduled_date_time));
        $current_date = format_date();
        $this->db->select('CS.stock_id,CS.stock_name,CS.lot_size,IFNULL(SH.close_price,0) as last_price ,TRUNCATE((CASE WHEN C.status=1 AND SH1.close_price IS NOT NULL THEN SH1.close_price ELSE S.last_price END), 2) as current_price,0 as is_wish,
        (
			TRUNCATE((CASE WHEN C.scheduled_date <= "'.$current_date.'" AND C.end_date >= "'.$current_date.'" THEN S.last_price - SH.close_price 
				 WHEN C.end_date < "'.$current_date.'" THEN SH1.close_price-SH.close_price 
				 ELSE S.last_price-S.open_price END), 2)
		) as price_diff,       
        IFNULL(S.logo,"") as logo', FALSE)
        ->from(COLLECTION_STOCK . " AS CS")
        ->join(COLLECTION.' C','C.collection_id = CS.collection_id','INNER')
        ->join(STOCK.' S', "S.stock_id = CS.stock_id", 'INNER')
        ->join(STOCK_HISTORY.' SH',"SH.stock_id=CS.stock_id AND SH.schedule_date='{$published_date}'",'LEFT')
        ->join(STOCK_HISTORY.' SH1',"SH1.stock_id=CS.stock_id AND SH1.schedule_date='{$end_date}'",'LEFT');        
        $this->db->where_in('CS.stock_id',$post_data['st_ids']);
        if(!empty($collection_id)) {
            $this->db->where("C.collection_id",$collection_id);
        }        
        $this->db->group_by('CS.stock_id');

        $sql = $this->db->get();    
        $result = $sql->result_array();
        
        //echo $this->db->last_query();die;
        return $result;
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


/**
     * Used for get sports past season match
     * @param int $sports_id
     * @return boolean
     */
    public function get_past_collections() {
        $current_date_time = format_date();
       
        $this->db->select("collection_id, scheduled_date,status");
        $this->db->from(COLLECTION);
        $this->db->where("status", '0');
        $this->db->where("is_lineup_processed",1);
        $this->db->where("DATE_ADD(end_date,INTERVAL 45 MINUTE) < ", $current_date_time);
        $sql = $this->db->get();
        //echo $this->db->last_query(); die;
        $matches = $sql->result_array();
        return $matches;
    }

     /**
     * @Summary: This function for use get all games start from current date and continues fatching from atleast spent 6 0r more than hour which are specify by league wise
     * @access: protected
     * @param:$league_id
     * @return: game array
     */
    public function get_collection_for_point_update($stock_type="") {
        $current_date_time = format_date();
        //$past_time = date("Y-m-d H:i:s", strtotime($current_date_time . " -".MATCH_SCORE_CLOSE_DAYS." days"));
        $this->db->select("collection_id,scheduled_date,published_date,end_date,stock_type");
        $this->db->from(COLLECTION );
        $this->db->where("status", '0');
        $this->db->where("DATE_FORMAT(scheduled_date ,'%Y-%m-%d %H:%i:%s') <='".$current_date_time."'");
        if(!empty($stock_type))
        {
            $this->db->where('stock_type',$stock_type);
        }
        else
        {
            $this->db->where_in('stock_type',[1,2]);
        }
        //$this->db->where("scheduled_date >= ", $past_time);
        $sql = $this->db->get();
        $result = $sql->result_array();

        //echo $this->db->last_query();die('***');
        return $result;
    }


    public function get_collection_for_point_manual_update($schedule_date="",$type="") {
        $current_date_time = format_date('today', 'Y-m-d');
        //$past_time = date("Y-m-d H:i:s", strtotime($current_date_time . " -".MATCH_SCORE_CLOSE_DAYS." days"));
        $this->db->select("collection_id, scheduled_date, published_date, end_date, status");
        $this->db->from(COLLECTION );
        $this->db->where("status",'0');
        if(!empty($type))
        {           
            $this->db->where('stock_type',$type);
        }
        else
        {
            $this->db->where_in('stock_type',[1,2]);
        } 

        $this->db->where("DATE_FORMAT(end_date ,'%Y-%m-%d') ='".$schedule_date."'");
        //$this->db->where("scheduled_date >= ", $past_time);
        $sql = $this->db->get();
        $result = $sql->result_array();
        // echo $this->db->last_query();die('***');
        return $result;
    }

    function get_stock_price_cap($key,$stock_type=2)
    {
        if($stock_type==2)
        {
            $a_equity = isset($this->app_config['allow_equity'])?$this->app_config['allow_equity']['key_value']:0;
            if($a_equity==1)
            {
                $custom_data = $this->app_config['allow_equity']['custom_data'];
                if(isset($custom_data[$key]))
                {
                    return $custom_data[$key]; 
                }
            }
        }
        return 1;
    }
    /**
     * Used to get holiday list
     */
    function get_holiday($market_id, $year) {
        $holiday_cache_key = "holiday_".$market_id."_".$year;
        $holiday_list = $this->get_cache_data($holiday_cache_key);
        if(empty($holiday_list)) {
          $this->db->select('holiday_date, year, description');
          $this->db->from(HOLIDAY);
          $this->db->where('market_id',$market_id);      
          $this->db->where('year',$year);  
          $this->db->order_by('holiday_id', 'ASC');
          $query = $this->db->get();
          if($query->num_rows() > 0) {
            $holiday_list = $query->result_array(); 
            $this->set_cache_data($holiday_cache_key, $holiday_list, REDIS_30_DAYS);
          }
        }
        return $holiday_list;
    }

    function check_holiday($collection_date, $market_id=1, $year='') {
        if(empty($year)) {
            $year = format_date('today', 'Y');  
        }
              
        $result = $this->get_holiday($market_id, $year);

        $holiday_list = array();
        if(!empty($result)) {
            $holiday_list = array_column($result, 'holiday_date');
        }  
        if(!empty($holiday_list)) {
            if(in_array($collection_date, $holiday_list)) {
                return FALSE; 
            }
        }
        return TRUE;        
    }

    function check_holiday_recursive($collection_date, $market_id=1, $add_sub='+1 day') {
        $flag = $this->check_holiday($collection_date, $market_id);
        if(!$flag) {
            $collection_date = date('Y-m-d', strtotime($collection_date.' '.$add_sub));
            $this->check_holiday_recursive($collection_date, $market_id);            
        }
        return $collection_date;   
    }


    function check_publish_date($publish_date, $market_id=1, $minus_one='-1 day') {  
        $publish_date = date('Y-m-d', strtotime($publish_date));    
        $flag = $this->check_holiday($publish_date, $market_id);     
        if(!$flag) {
            $publish_date = date('Y-m-d', strtotime($publish_date.' '.$minus_one));     
            $publish_date =  $this->check_publish_date($publish_date, $market_id); 
            $dayofweek = date('w', strtotime($publish_date));
            if(in_array($dayofweek,[0,6]))//0 =>sunday or 6=>saturday
            {            
                $publish_date = date('Y-m-d '.CONTEST_PUBLISH_TIME,strtotime($publish_date.' last friday'));
                $publish_date =  $this->check_publish_date($publish_date, $market_id);
            }
        }

         return $publish_date;   
    }



    /**
	 * used to get fixture all stocks list
	 * @param array $post_data
	 * @return array
	*/
	public function get_all_stocks($post_data)
	{
        /**
         * collection season stock name, lot size
         * stock history  stock history  last price(x) of publish date of collection
         * stocktable  => last price(y) as current price, 
         * x-y  = example +20.22
         * flag for wish list is_wish_list
         * 
         * market table=> give master configuration 
		 * 
		 * Current Date < Schdeule date then stock table close price - open price => Upcoming (Daily, Weekly, Monthly)
		 * Current Date >= Schdeule date and Current Date < end date then last price - open price => LIVE (Daily)
		 * Current Date >= Schdeule date and Current Date < end date then last price - open price (Schdeuled date) => LIVE (Weekly/Monthly)
		  * Current Date > end date then close price (end date) - open price (Schdeuled date) => Completed

		  * $published_date = date('Y-m-d',strtotime($post_data['published_date']));	
		 * (TRUNCATE((CASE WHEN C.status=1 AND SH1.close_price IS NOT NULL THEN SH1.close_price ELSE S.last_price END), 2) - IFNULL(SH.close_price,0)) as price_diff
         * ***/

		$collection_id = $post_data['collection_id'];
			
		$end_date = date('Y-m-d',strtotime($post_data['end_date']));

		$published_date_time = $post_data['published_date'];	
		$published_date = date('Y-m-d',strtotime($published_date_time));	
		$current_date = format_date();

		$this->db->select('CS.stock_id, CS.stock_name, CS.lot_size, IFNULL(SH.close_price,0) as last_price, IFNULL(I.industry_id,NULL) as industry_id,IFNULL(I.name,"") as industry_name,
		TRUNCATE((CASE WHEN C.status=1 THEN SH1.close_price ELSE S.last_price END), 2) as current_price, 
		0 as is_wish, 
		(
			TRUNCATE((CASE WHEN C.scheduled_date <= "'.$current_date.'" AND C.end_date >= "'.$current_date.'" THEN S.last_price - SH.close_price 			 
				 WHEN C.end_date < "'.$current_date.'" THEN SH1.close_price-SH.close_price 
				 ELSE S.last_price-S.previous_close END), 2)
		) as price_diff,
		(
			TRUNCATE((CASE WHEN C.scheduled_date <= "'.$current_date.'" AND C.end_date >= "'.$current_date.'"  THEN ABS(((S.last_price-SH.close_price)/SH.close_price)*100)  
				 WHEN C.end_date < "'.$current_date.'" THEN ABS(((SH1.close_price-SH.close_price)/SH.close_price)*100) 
				 ELSE ABS(((S.last_price-S.previous_close)/S.previous_close)*100) END), 2)
		) as percent_change,
		IFNULL(S.logo,"") as logo', FALSE)
			->from(COLLECTION_STOCK . " AS CS")
			->join(COLLECTION.' C','C.collection_id = CS.collection_id','INNER')
			->join(STOCK.' S', "S.stock_id = CS.stock_id", 'INNER')
			->join(STOCK_HISTORY.' SH',"SH.stock_id=CS.stock_id AND SH.schedule_date='{$published_date}'",'LEFT')
			->join(STOCK_HISTORY.' SH1',"SH1.stock_id=CS.stock_id AND SH1.schedule_date='{$end_date}'",'LEFT')
            ->join(INDUSTRY.' I','I.industry_id=S.industry_id', 'LEFT');
	           
        $this->db->where("CS.collection_id",$collection_id)
                //->where("S.status",1)
		        ->group_by('CS.stock_id')
		        ->order_by('CS.stock_name','ASC');

		$sql = $this->db->get();
		$result	= $sql->result_array();
        //echo $this->db->last_query();die('dsds');
		return $result;
	}


    /**
	 * used to get user created team count
	 * @param int $collection_master_id
	 * @return array
	*/
	public function get_all_old_manual_teams($collection_master_id)
	{	
		$result = $this->db->select('COUNT(lineup_master_id) as team_count')
				->from(LINEUP_MASTER)
				->where('collection_id',$collection_master_id)
				->where('user_id',$this->user_id)
				->get()
				->row_array();
		return $result;
	}

    	/**
	 * used for save user lineup data
	 * @param array $post_data
	 * @return int
	 */
	public function save_new_lineup($post_data)
	{
		$this->db->insert(LINEUP_MASTER, $post_data);
		return $this->db->insert_id();
	}

    /**
	 * used for get team details
	 * @param int $lineup_master_id
	 * @param int $collection_master_id
	 * @return array
	*/
	public function get_team_by_lineup_matser_id($lineup_master_id,$collection_id)
	{	
		$sql = $this->db->select('lineup_master_id,collection_id,user_id,team_name')
				->from(LINEUP_MASTER)
				->where('lineup_master_id',$lineup_master_id)
				->where('collection_id',$collection_id)
				->where('user_id',$this->user_id)
				->get();
		$result = $sql->row_array();
		return $result;
	}
    // $this->app_config['allow_social']['custom_data']['website_url']
    public function notify_to_social_server($url, $data) {
        $social_base_url = isset($this->app_config['allow_social']['custom_data']['website_url'])?$this->app_config['allow_social']['custom_data']['website_url']:'';
        if($social_base_url=='') log_message("error","\n\n\n social base url is not set please set this from config page. \n\n\n");
        $curlUrl =  $social_base_url . $url;
        $data_string = json_encode($data);
    
        try {

            $curl = curl_init();

              curl_setopt_array($curl, array(
              CURLOPT_URL => $curlUrl,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => $data_string,
              CURLOPT_HTTPHEADER => array(
                'APPVERSION: v3',
                'LoginSessionKey: '.$this->auth_key.'',
                'Content-Type: application/json'

              ),
            ));

            $response = curl_exec($curl);
            return json_decode($response,true);
           
            curl_close($ch);

        } catch (Exception $e) {
            // var_dump($e);
            // die('dfdf');
        }
    }

    function get_industry_list()
    {
        return  $result = $this->db->select("industry_id,display_name")
        ->from(INDUSTRY)
        ->where("status",1)
        ->get()->result_array();
    } 
    
     /**
     * get converted date acc to client time zone.
     * @return 
     */
	public function _get_client_dates($format = 'Y-m-d',$to_utc=2)
	{
		if(isset($_POST['from_date']) && $_POST['from_date'] != ""){
			$start_date_str = date('Y-m-d',strtotime($_POST['from_date'])).' 00:00:00';
			$temp_convert_start = get_timezone(strtotime($start_date_str),$format,TIMEZONE,1,$to_utc);
			$_POST['from_date'] = $temp_convert_start['date'];
		}else if(isset($_GET['from_date']) && $_GET['from_date'] != ""){
			$to_utc=1;
			$start_date_str = date('Y-m-d',strtotime($_GET['from_date'])).' 00:00:00';
			$temp_convert_start = get_timezone(strtotime($start_date_str),$format,TIMEZONE,1,$to_utc);
			$_POST['from_date'] = $_GET['from_date'] = $temp_convert_start['date'];
		}

		if(isset($_POST['to_date']) && $_POST['to_date'] != ""){
			$end_date_str = date('Y-m-d',strtotime($_POST['to_date'])).' 23:59:59';
			$temp_convert_end = get_timezone(strtotime($end_date_str),$format,TIMEZONE,1,$to_utc);
			$_POST['to_date'] = $temp_convert_end['date'];
		}else if(isset($_GET['to_date']) && $_GET['to_date'] != ""){
			$to_utc=1;
			$end_date_str = date('Y-m-d',strtotime($_GET['to_date'])).' 23:59:59';
			$temp_convert_end = get_timezone(strtotime($end_date_str),$format,TIMEZONE,1,$to_utc);
			$_POST['to_date'] = $_GET['to_date'] = $temp_convert_end['date'];
		}
		// echo "rest con";print_r($_POST);die;
		return;
	}
}
/* End of file MY_Model.php */
/* Location: application/core/MY_Model.php */
