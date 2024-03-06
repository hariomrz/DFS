<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MY_Model extends CI_Model {

	public function __construct()
	{
		// Call the Model constructor
		parent::__construct();	
		$this->load->database();
		$this->db_fantasy		= $this->load->database('db_fantasy', TRUE);	
	}
	function __destruct()
	{
		if (isset($this->db->conn_id)) {
            $this->db->close();
        }
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
     * Used for load cache driver
     * @return 
     */
    private function init_cache_driver() {
        $this->load->driver('cache', array('adapter' => CACHE_ADAPTER, 'backup' => 'file'));
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
    	$this->push_data_in_queue($bucket_data, 'bucket');
    }

    /**
	 * Used for push data in queue
	 * @param string $file_name json file name
	 * @param array $data api file data
	 * @return 
	 */
    public function push_data_in_queue($data,$queue_name){
    	if(empty($queue_name)){
    		return true;
    	}

    	$this->load->helper('queue_helper');
    	rabbit_mq_push($data, $queue_name);
	}
	
	public function get_submodule_setting()
	{
		return  $this->db->select('module_setting_id,
		submodule_key,
		name,
		description,
		daily_coins_data,
		status')->from(SUBMODULE_SETTING)->where('status',1)->get()->result_array();
	}

	public function insert_batch( $data )
	{
		$this->db->insert_batch( $this->table_name , $data );         
		if ($this->db->affected_rows() > 0 )
		{
			return TRUE;
		}
		
		return FALSE;       
	}

	public function get_all_table_data($column,$table,$league_id=array())
	{
		$this->db->select($column)
						->from($table);

						if(!empty($league_id))
						{
							$this->db->where($league_id);	
						}
						//->order_by("team_name","ASC")
		$sql = $this->db->get();
		return $sql->result_array();
	}


	public function get_users_by_ids($user_ids)
	{

		if(empty($user_ids))
		{
			return array();
		}
		$sql = $this->db->select('user_id,user_unique_id,email,phone_no,phone_code,user_name,user_unique_id,IFNULL(first_name,user_name) as first_name,IFNULL(last_name,"") as last_name,IFNULL(image,"") as image',FALSE)
						->from(USER)
						->where_in('user_id', $user_ids)
						->get();
		$rs = $sql->result_array();

		//$rs = array_column($rs, "email","user_id");
		
		return $rs;
	}

	public function get_users_device_by_ids($user_ids)
	{
		if(empty($user_ids))
		{
			return array();
		}
		$post = $this->input->post();

		$pre_query ="(SELECT user_id,GROUP_CONCAT(device_id) as device_ids,GROUP_CONCAT(device_type) as device_types ,keys_id,device_id,device_type FROM ".$this->db->dbprefix(ACTIVE_LOGIN)."  WHERE device_id IS NOT NULL GROUP BY user_id ORDER BY keys_id DESC)";

		$this->db->select('U.user_id,U.email,U.phone_no,U.phone_code,U.user_name,AL.device_id,AL.device_type,AL.device_ids,AL.device_types')
						->from(USER.' U')
						->join($pre_query.' AL','AL.user_id=U.user_id','LEFT');

		if(empty($post['all_user']) || (isset($post['all_user']) && $post['all_user'] == '0'))
		{
			$this->db->where_in('U.user_id', $user_ids);
		}
		$sql = $this->db->where('U.is_systemuser',0)
						->group_by('U.user_id')
						->get();

		if(!empty($post['is_debug']) && $post['is_debug'] ==1)
		{
			echo $this->db->last_query();die('df');
		}
		$rs = $sql->result_array();
		
		return $rs;
	}


	public function get_users_by_ids_first_deposit($user_ids)
	{

		if(empty($user_ids))
		{
			return array();
		}
		$sql = $this->db->select('U.user_id,U.email,U.phone_no,U.phone_code,U.user_name,U.user_name')
						->from(USER.' U')
						->join(ORDER.' O',"O.user_id=U.user_id AND O.status =1 AND O.source=7 ","LEFT")
						->where_in('U.user_id', $user_ids)
						->where('O.order_id IS NULL')
						->get();
		$rs = $sql->result_array();

	
		//$rs = array_column($rs, "email","user_id");
		
		return $rs;
	}


	public function get_all_data($column,$table,$where = array())
	{
		$sql = $this->db->select($column)
						->from($table)
						->where($where)
						->get();
		return $sql->result_array();
	}

	function get_single_row($select = '*', $table, $where = "") {
		$this->db->select($select);
		$this->db->from($table);
		if ($where != "") {
			$this->db->where( $where );
		}
		$query = $this->db->get();
		return $query->row_array();
	}
/**********************USE IN SPORTS MODEL************************************************/

	/**
	 * @Summary: This function for use check attribute existing on array or not
	 * @access: protected
	 * @param:$key, $arry
	 * @return: return exist value or assing value
	 */
	protected function check_value_exist($key, $arry)
	{
		if(array_key_exists($key,$arry))
		{
			return floatval($arry[$key]);
		}else
		{
			return "0.00";
		}
	}
	
	/**
	 * @Summary: This function for use check URL hit by Webbrowser or schedular by cron(wget)
	 * @access: protected
	 * @param:
	 * @return: true or false
	 */
	protected function check_url_hit()
	{
		return TRUE;
		//Check url hit by server or manual
		if ( ENVIRONMENT == 'production'  )
		{
			$http_user_agent = substr($_SERVER['HTTP_USER_AGENT'],0,4);
			if(strtolower($http_user_agent) != 'wget')
			{
				redirect('');
			}
		}	
		return TRUE;
	}
	

	/**
	 * @Summary: This function for use for update node client server to update score run time    
	 * @access: protected
	 * @param: $teams
	 * @return: 
	 */
	protected function update_node_client( $teams )
	{
		//send_email("vinod@vinfotech.com","Test update_node_client cron","Yes (fantasycentrepro LIVE), update_node_client cron working fine");
		debug($teams);//die;
		$FinalScoreResult = array();
		$games            = array();
		foreach ($teams as $key => $team)
		{			
			$sql = $this->db->select("G.game_unique_id")
							->from(GAME." AS G")
							->where("FIND_IN_SET( '".$team['season_game_unique_id']."', G.selected_matches)")
							->where("is_cancel",'0')
							->where("prize_distributed",'0')
							->get();

			$games = $sql->result_array();
			
			if($games&&is_array($games))
			{
				foreach ($games as $games_key => $games_value)
				{
					$game_unique_id = $games_value['game_unique_id'];					
					$sql = $this->db->select("LM.lineup_master_id,LM.collection_master_id")
									->from(LINEUP_MASTER." AS LM")
									->where("LM.game_unique_id","$game_unique_id")
									->get();
					$result = $sql->result_array();
					if( $result && isset( $result[0]['lineup_master_id'] ) )
					{
						if( !isset( $FinalScoreResult[$game_unique_id] ) )
						{
							$FinalScoreResult[$game_unique_id] = array();
						}
						foreach( $result as $k => $val )
						{
							$games[$game_unique_id] = $game_unique_id;
							$collection_master_id = $val['collection_master_id'];
							if( !isset( $FinalScoreResult[$game_unique_id][$val['lineup_master_id']] ) )
							{
								$FinalScoreResult[$game_unique_id][$val['lineup_master_id']] = array();
							}
							// $FinalScoreResult[$val['game_unique_id']][$val['lineup_master_id']][$val['player_unique_id']] = $val['score'];
							$lineup_table = LINEUP."_".$collection_master_id;
	                        if($this->db->table_exists($lineup_table))
	                        {
								$sql = "SELECT 
											`L`.`player_unique_id`,`L`.`score` 
										FROM 
											`".$this->db->dbprefix($lineup_table)."` AS `L` 
										WHERE 
											`lineup_master_id` = ".$val['lineup_master_id'];
								$total_score = $this->run_query($sql);
								$FinalScoreResult[$game_unique_id][$val['lineup_master_id']] = $total_score;
							}
						}
					}
					$sqls = "SELECT
								CASE 
									WHEN (`U`.`user_name` IS NULL OR `U`.`user_name` = '' )
									THEN 
									CONCAT(`U`.`first_name`, ' ', `U`.`last_name`) 
									ELSE 
									`U`.`user_name` 
									END AS `name`,`U`.`image`,`U`.`user_id`,`LM`.`lineup_master_id`,`U`.`email`,`LM`.`total_score` 
							FROM 
								`".$this->db->dbprefix(USER)."` AS `U`
							INNER JOIN 
								`".$this->db->dbprefix(LINEUP_MASTER)."` AS `LM` ON `LM` . `user_id` = `U` . `user_id` 
							WHERE 
								`LM`.`game_unique_id` = '" . $game_unique_id . "' 
							ORDER BY 
								`LM`.`total_score` DESC
							";
					$res = $this->run_query($sqls);
					$FinalScoreResult[$game_unique_id]['user_detail'] = $res;

					if( $FinalScoreResult )
					{
						$data_string = json_encode($FinalScoreResult);

						$curlUrl = NODE_ADDR."/recieveScore";
						debug($curlUrl);
						debug($FinalScoreResult);
						$curl = curl_init();
						curl_setopt_array($curl, array(
							CURLOPT_POST           => 1,
							CURLOPT_POSTFIELDS     => $data_string,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_URL            => $curlUrl,
							CURLOPT_SSL_VERIFYPEER => false
						));
						$result = curl_exec($curl);
						curl_close($curl);
						echo $result;
						$FinalScoreResult = array();
					}
				}
			}
		}
	exit();
	}

	function isTimeValid($time)
	{
		return is_object(DateTime::createFromFormat('H:i a', $time));
	}

	
	/**
	 * [get_all_country description]
	 * @MethodName get_all_country
	 * @Summary This function used to get all master country
	 * @return     [type]
	 */
	public function get_all_country()
	{
		$sql = $this->db->select("*")
						->from(MASTER_COUNTRY)
						->order_by("country_name","ASC")
						->get();
		return $sql->result_array();
	}

	/**
	 * [get_all_state description]
	 * @MethodName get_all_state
	 * @Summary This function used to get all master state
	 * @return     [type]
	 */
	public function get_all_state()
	{
		$sql = $this->db->select("*")
						->from(MASTER_STATE)
						->order_by("name","ASC")
						->get();
		return $sql->result_array();
	}

	/**
	 * [get_all_state_by_country description]
	 * @MethodName get_all_state_by_country
	 * @Summary This function used to get all master state by country id
	 * @return     [type]
	 */
	public function get_all_state_by_country($country)
	{
		$sql = $this->db->select("*")
						->from(MASTER_STATE)
						->where('master_country_id', $country)
						->order_by("name","ASC")
						->get();
		return $sql->result_array();
	}

	

	 /**
	 * [get_all_position description]
	 * @MethodName get_all_position
	 * @Summary This function is used to get all position by sports id in database
	 * @param      [int]  [sports_id]
	 * @return     [array]
	 */
	public function get_all_position($sports_id,$select='')
	{
		$select_str = 'master_lineup_position_id,position_name as position, position_name, position_display_name,number_of_players,max_player_per_position,position_order';
		if(isset($select) && $select != ""){
			$select_str = $select;
		}
		$result = array();
		$sql = $this->db_fantasy->select($select_str)
						->from(MASTER_LINEUP_POSITION)
						->where('position_name = allowed_position') // to avoid FLEX position
						->where('sports_id',$sports_id) 
						->order_by('position_order','ASC')
						->get();
		$result = $sql->result_array();
		return ($result) ? $result : array();
	}

	/**
     * [replace_into_batch description]
     * Summary :-
     * @param  [type] $table [description]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function replace_into_batch($table, $data)
	{
		$column_name	= array();
		$update_fields	= array();
		$append			= array();
		foreach($data as $i=>$outer)
		{
			$column_name = array_keys($outer);
			$coloumn_data = array();
			foreach ($outer as $key => $val) 
			{
				if($i == 0)
				{
					// $column_name[]   = "`" . $key . "`";
					$update_fields[] = "`" . $key . "`" .'=VALUES(`'.$key.'`)';
				}

				if (is_numeric($val)) 
				{
					$coloumn_data[] = $val;
				} 
				else 
				{
					$coloumn_data[] = "'" . replace_quotes($val) . "'";
				}
			}
			$append[] = " ( ".implode(', ', $coloumn_data). " ) ";
		}
		$sql = "INSERT INTO " . $this->db->dbprefix($table) . " ( " . implode(", ", $column_name) . " ) VALUES " . implode(', ', $append) . " ON DUPLICATE KEY UPDATE " .implode(', ', $update_fields);
		$this->db->query($sql);

	}

	/**
     * to get merchandise list
     * @param void
     * @return array
     */
    public function get_prize_merchandise_list($ids_arr = array())
    {
        $this->db_fantasy->select('merchandise_id,name,image_name')
                ->from(MERCHANDISE)
                ->order_by("merchandise_id","ASC");

        if(isset($ids_arr) && !empty($ids_arr)){
            $this->db_fantasy->where_in("merchandise_id",$ids_arr);
        }
        $result = $this->db_fantasy->get()->result_array();
        return $result;
    }  

	/**
	* function used for get sports position data
	* @param int $sports_id
	* @return array
	*/
	public function get_position_by_sports_id($sports_id)
	{
		$sql = $this->db_fantasy->select("*")
				->from(MASTER_LINEUP_POSITION)
				->where('sports_id', $sports_id)
				->where('max_player_per_position > ', "0")
				->order_by("position_order","ASC")
				->get();
		$result = $sql->result_array();
		return $result;
	}

	 /**
     * Used for get sports hub data
     * @param NA
     * @return Array
     */
    public function get_sports_hub($lang='en')
    {
        return $this->db->select("sports_hub_id,game_key,".$lang."_title,image,is_featured,display_order
		")
        ->from(SPORTS_HUB)
        ->where('status',1)
        // ->order_by('is_featured','DESC')
        ->order_by('display_order','ASC')
        ->get()->result_array();
    }

	function get_app_config_value($module_key)
	{
		return  $enabled =  isset($this->app_config[$module_key])?$this->app_config[$module_key]['key_value']:0; 
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
     * used to get match collection details by match and sports
     * @param array $post_data
     * @return array
    */
    public function get_sports_fixture_collection($post_data){

        $this->db_fantasy->select("CM.collection_master_id,CM.league_id,CM.collection_name,CM.season_scheduled_date,CM.status,CM.is_lineup_processed,S.season_game_uid,CM.is_gc",FALSE);
        $this->db_fantasy->from(SEASON." as S");
        $this->db_fantasy->join(LEAGUE.' as L', 'L.league_id = S.league_id',"INNER");
        $this->db_fantasy->join(COLLECTION_SEASON.' as CS', 'CS.season_id = S.season_id',"INNER");
        $this->db_fantasy->join(COLLECTION_MASTER.' as CM', 'CM.collection_master_id = CS.collection_master_id AND CM.league_id=S.league_id',"INNER");
        $this->db_fantasy->where('CM.season_game_count',1);
        $this->db_fantasy->where('S.season_game_uid', $post_data['season_game_uid']);
        $this->db_fantasy->where('L.sports_id', $post_data['sports_id']);
        $result = $this->db_fantasy->get()->row_array();
        return $result;
    }

    /**
     * used for update game center flag for match
     * @param int $collection_master_id
     * @param boolean
     */
    public function update_match_gc_status($collection_master_id) {
        $this->db_fantasy->where("collection_master_id",$collection_master_id);
        $this->db_fantasy->update(COLLECTION_MASTER, array("is_gc"=>"1"));
        return TRUE;
    }

    //ALLOW_NETWORK_FANTASY -> case 3

	function http_post_request($url, $params = array(), $api_type = 1, $debug = false)
    {
        switch ($api_type)
        {
            case 1 :
                $api_url = FANTASY_API_URL . $url;
                break;
            case 2 :
                $api_url = USER_API_URL . $url;
                break;
            case 3 :
            	$api_url = $url; 
            break;    
        }

        $post_data_json = json_encode($params);
        $header = array("Content-Type:application/json", "Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (ENVIRONMENT !== 'production'){
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }

        $output = curl_exec($ch);
        if ($debug)
        {
            echo '<pre>';
            echo $output;
            exit();
        }
        curl_close($ch);
        return json_decode($output, true);
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

	function get_user_balance($user_id)
    {
        $result =	$this->db->select("user_id,balance as real_amount, bonus_balance as bonus_amount, winning_balance as winning_amount,point_balance")
            ->where(array("user_id" => $user_id))
            ->get(USER)
            ->row_array();
        return array(
                        "bonus_amount" => $result["bonus_amount"]?$result["bonus_amount"]:0,
                        "real_amount" => $result["real_amount"]?$result["real_amount"]:0,
                        "winning_amount" => $result["winning_amount"]?$result["winning_amount"]:0,
                        "point_balance" => $result["point_balance"]?$result["point_balance"]:0
                    );
    }

	/**  Used to update user balance 
     * @param int $user_id
     * @param float $real_bal
     * @param float $bonus_bal
     * @param float $winning_bal
     * @param float $point_bal  
     * @return int
     */
    function credit_user_balance($user_id, $balance_arr,$oprator='add',$order_id='') {

        if(empty($balance_arr)){
            return false;
        }
        if(isset($balance_arr['real_amount']) && $balance_arr['real_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('balance', 'balance - '.$balance_arr['real_amount'], FALSE);
            }else{
                $this->db->set('balance', 'balance + '.$balance_arr['real_amount'], FALSE);
            }
            if(isset($balance_arr['source']) && $balance_arr['source'] == "7" && $oprator == 'add'){
                $this->db->set('total_deposit', 'total_deposit + '.$balance_arr['real_amount'], FALSE);
            }
        }
        if(isset($balance_arr['bonus_amount']) && $balance_arr['bonus_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('bonus_balance', 'bonus_balance - '.$balance_arr['bonus_amount'], FALSE);
            }else{
                $this->db->set('bonus_balance', 'bonus_balance + '.$balance_arr['bonus_amount'], FALSE);
            }
            $this->load->helper('queue_helper');
            $bonus_data = array('oprator' => $oprator, 'user_id' => $user_id, 'total_bonus' => $balance_arr['bonus_amount'], 'bonus_date' => format_date("today", "Y-m-d"));
            add_data_in_queue($bonus_data, 'user_bonus');
        }
        if(isset($balance_arr['winning_amount']) && $balance_arr['winning_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('winning_balance', 'winning_balance - '.$balance_arr['winning_amount'], FALSE);
            }else{
                $this->db->set('winning_balance', 'winning_balance + '.$balance_arr['winning_amount'], FALSE);
            }
        }
        if(isset($balance_arr['points']) && $balance_arr['points'] > 0 ){
            if($oprator=='withdraw'){
                $this->db->set('point_balance', 'point_balance - '.$balance_arr['points'], FALSE);
            }else{
                $this->db->set('point_balance', 'point_balance + '.$balance_arr['points'], FALSE);
            }
        }
        //for tds deduction net winning update on withdrawal
        if(isset($balance_arr['source']) && $balance_arr['source'] == "8" && $oprator == "withdraw" && isset($balance_arr['tds']) && $balance_arr['tds'] > 0){
            $custom_data = json_decode($balance_arr['custom_data'],TRUE);
            if(isset($custom_data['net_winning']) && $custom_data['net_winning'] > 0){
                $this->db->set('net_winning', 'net_winning - '.$custom_data['net_winning'], FALSE);
            }       
        }
        $this->db->where('user_id', $user_id);
        $this->db->update(USER);
        return $this->db->affected_rows();
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
		// echo $this->db->last_query();die;
        return true;
    }

    public function save_featured_league($post_data){
    	$current_date = format_date();
    	$is_featured = isset($post_data['is_featured']) ? $post_data['is_featured'] : 0;
    	$check_exist = $this->get_single_row("*",FEATURED_LEAGUE,array("league_uid"=>$post_data['league_uid'],"sports_id"=>$post_data['sports_id']));
    	$result = 0;
    	if(!empty($check_exist)){
    		$data_arr = array();
    		$data_arr['dfs_id'] = 0;
    		if($is_featured == 1){
    			$data_arr['dfs_id'] = $post_data['league_id'];
    		}
    		$data_arr['modified_date'] = $current_date;
    		$result = $this->update(FEATURED_LEAGUE,$data_arr,array("league_uid"=>$post_data['league_uid'],"sports_id"=>$post_data['sports_id']));
    	}else if($is_featured == 1){
    		$data_arr = array();
    		$data_arr['sports_id'] = $post_data['sports_id'];
    		$data_arr['league_uid'] = $post_data['league_uid'];
    		$data_arr['name'] = $post_data['league_name'];
    		$data_arr['dfs_id'] = $post_data['league_id'];
    		$data_arr['added_date'] = $current_date;
    		$data_arr['modified_date'] = $current_date;
    		$result = $this->save_record(FEATURED_LEAGUE,$data_arr);
    	}
    	return $result;
    }

}
//End of file