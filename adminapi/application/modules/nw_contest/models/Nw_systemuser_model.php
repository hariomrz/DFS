<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Nw_systemuser_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database('user_db');
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
		//Do your magic here
	}

	/**
	 * Function used for generate user unique id
	 * @param 
	 * @return string
	 */
	public function _generate_key() 
	{
		$this->load->helper('security');
        $salt = do_hash(time() . mt_rand());
        $new_key = substr($salt, 0, 20);
        return $new_key;
	}

	/**
	 * Function used for check user unique id exist or not
	 * @param string $key
	 * @return boolean
	 */
	private function _key_exists($key)
	{
		$this->db->select('user_unique_id');
        $this->db->where('user_unique_id', $key);
        $this->db->limit(1);
        $query = $this->db->get(USER);
        $num = $query->num_rows();
        if($num > 0){
            return true;
        }
		return false;
		
	}

	/**
	 * Function used for generate user referral code
	 * @param 
	 * @return string
	 */
	public function _generate_referral_code() 
	{
		$this->load->helper('security');
		do {
			$salt = do_hash(time() . mt_rand());
			$new_key = substr($salt, 0, 5);
			$new_key = strtoupper($new_key);
		}
		// Already in the DB? Fail. Try again
		while (self::_referral_code_exists($new_key));
		return $new_key;
	}

	/**
	 * Function used for check user referral code exist or not
	 * @param string $key
	 * @return boolean
	 */
	private function _referral_code_exists($key)
	{
		$this->db->select('referral_code');
        $this->db->where('referral_code', $key);
        $this->db->limit(1);
        $query = $this->db->get(USER);
        $num = $query->num_rows();
        if($num > 0){
            return true;
        }
        return false;
	}	

	/**
	 * function to insert bulk system user via csv file
	 */
	public function import_system_users($data){
		if(!empty($data)){
			$this->db->insert_batch(USER, $data);
		}
		return $this->db->insert_id();
	}
	/**
	 * Function used for save user record in db
	 * @param array $post
	 * @return int
	 */
	public function registration($post)
	{
		$post['referral_code'] = self::_generate_referral_code();
		$this->db->insert(USER, $post);
		return $this->db->insert_id();
	}

	/**
     * used to check user can delete or not
     * @param int $user_id
     * @return array
     */
    public function check_for_delete($user_id){
    	$this->db_fantasy->select('COUNT(LM.lineup_master_id) as total')
			->from(LINEUP_MASTER . " AS LM")
			->where("LM.user_id",$user_id)
			->limit(1);
		$sql = $this->db_fantasy->get();
		$result = $sql->row_array();
		return $result;
    }

    /**
     * used for delete user from db
     * @param int $user_id
     * @return boolean
     */
    public function delete_user($user_id){
    	if(!$user_id){
    		return false;
    	}

    	$this->db->where("user_id",$user_id);
        $this->db->delete(USER);
        return true;
    }

    /**
     * used for update user details
     * @param int $user_id
     * @return boolean
     */
    public function update_user($data_arr,$user_id){
    	if(!$user_id){
    		return false;
    	}

    	$this->db->where("user_id",$user_id);
        $this->db->update(USER, $data_arr);
        return true;
    }

	/**
	 * Function used for get all system users
	 * @param array $post_data
	 * @return array
	 */
	public function get_all_system_users()
	{  	 
		$sort_field	= 'added_date';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;
		$post_data = $this->input->post();
		$total = 0;
		if($post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if($post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
		}

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('city','user_name','email','status','pan_verified')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		$offset	= $limit * $page;
		$sql = $this->db->select("U.user_id,U.user_unique_id,U.user_name,U.email,U.phone_no,U.balance,U.winning_balance,U.bonus_balance,U.image",FALSE)
			->from(USER.' AS U')
			->where("U.status","1")
			->where("U.is_systemuser","1")
			->order_by('U.'.$sort_field, $sort_order);
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name),IFNULL(U.pan_no,"")))', strtolower($post_data['keyword']) );
		}
		$this->db->group_by("U.user_id");
		if($post_data['current_page']==1) {
			$tempdb = clone $this->db;
			$temp_q = $tempdb->get();
			$total = $temp_q->num_rows();
		}

		$sql = $this->db->limit($limit,$offset)
						->get();
		$result	= $sql->result_array();
		$result=($result)?$result:array();
		return array('result'=>$result,'total'=>$total);
	}

	/**
	 * Function used for get system users list for contest
	 * @param array $post_data
	 * @return array
	 */
	public function get_system_users_list($post_data = array())
	{  	 
		$limit		= 1500;
		$page		= 0;
		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}
		$offset	= $limit * $page;
		$this->db->select("U.user_id,U.user_unique_id,U.user_name",FALSE)
			->from(USER.' AS U')
			->where("U.status","1")
			->where("U.is_systemuser","1")
			->where("U.user_name != ","")
			->order_by('U.user_name',"ASC");
		if(isset($post_data['user_ids']) && !empty($post_data['user_ids'])){
			$this->db->where_in("U.user_id",$post_data['user_ids']);
		}
		$sql = $this->db->limit($limit,$offset)->get();
		$result	= $sql->result_array();
		return $result;
	}

	/**
	 * Function used for get system users with team count for contest
	 * @param array $post_data
	 * @return array
	 */
	public function get_system_users_for_contest($post_data){
		if(!isset($post_data['contest_id'])){
			return false;
		}

		$this->db_fantasy->select("LM.user_id,count(LM.user_id) AS team_count")
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id=LMC.lineup_master_id", "INNER")
                ->where("LM.is_systemuser", 1)
                ->where("LMC.contest_id", $post_data['contest_id'])
                ->where("LMC.fee_refund", 0)
                ->group_by('LM.user_id')
                ->order_by("LM.user_id","ASC");

        $result = $this->db_fantasy->get()->result_array();
        return $result;
	}

	/**
	 * Function used for get joined system users count for fixture
	 * @param int $collection_master_id
	 * @return array
	 */
	public function get_fixture_system_users_count($collection_master_id){
		if(!isset($collection_master_id)){
			return false;
		}

		$this->db_fantasy->select("LM.user_id,count(LMC.lineup_master_contest_id) AS team_count")
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id=LMC.lineup_master_id", "INNER")
                ->where("LM.is_systemuser", 1)
                ->where("LM.collection_master_id", $collection_master_id)
                ->where("LMC.fee_refund", 0)
                ->group_by('LM.user_id')
                ->order_by("LM.user_id","ASC");

        $result = $this->db_fantasy->get()->result_array();
        return $result;
	}

	/**
	 * Function used for get joined system users count for contest
	 * @param int $contest_id
	 * @return array
	 */
	public function get_contest_system_users_count($contest_id){
		if(!isset($contest_id)){
			return false;
		}

		$this->db_fantasy->select("count(LMC.lineup_master_contest_id) AS team_count")
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id=LMC.lineup_master_id", "INNER")
                ->where("LM.is_systemuser", 1)
                ->where("LMC.fee_refund", 0)
                ->where("LMC.contest_id", $contest_id);

        $result = $this->db_fantasy->get()->row_array();
        return isset($result['team_count']) ? $result['team_count'] : 0;
	}

	/**
	 * Function used for get contest details by id
	 * @param array $post_data
	 * @return array
	 */
	public function get_contest_detail($post_data)
	{
		$this->db_fantasy->select('G.prize_distibution_detail,G.user_id,G.collection_master_id,G.sports_id,G.league_id,G.season_scheduled_date,G.contest_id, G.contest_unique_id, G.contest_name, G.league_id, DATE_FORMAT(G.season_scheduled_date,"'.MYSQL_DATE_TIME_FORMAT.'") as scheduled_date, G.season_week, G.size, G.entry_fee,G.currency_type,G.prize_pool,G.status,DATE_FORMAT(G.added_date,"'.MYSQL_DATE_TIME_FORMAT.'") as added_date,G.site_rake, G.total_user_joined,G.guaranteed_prize,G.is_pin_contest,G.is_uncapped,G.is_feature,G.prize_type,G.is_custom_prize_pool,G.multiple_lineup,G.minimum_size,G.master_contest_type_id,G.max_bonus_allowed,G.total_system_user,G.is_auto_recurring,CS.season_game_uid,S.playing_announce,S.playing_eleven_confirm')
			->from(CONTEST . " AS G")
			->join(COLLECTION_SEASON . " AS CS", 'CS.collection_master_id = G.collection_master_id', 'INNER')
			->join(SEASON . " AS S", 'S.season_game_uid = CS.season_game_uid AND G.league_id = S.league_id', 'INNER');
		if(isset($post_data['contest_id']) && $post_data['contest_id'] != ""){
			$this->db_fantasy->where('G.contest_id', $post_data['contest_id']);
		}
		if(isset($post_data['contest_unique_id']) && $post_data['contest_unique_id'] != ""){
			$this->db_fantasy->where('G.contest_unique_id', $post_data['contest_unique_id']);
		}
		$sql = $this->db_fantasy->get();
		$result = $sql->row_array();
		return $result;
	}

	/**
	 * Function used for get contest info by id
	 * @param array $post_data
	 * @return array
	 */
	public function get_contest_record($contest_id)
	{
		$this->db_fantasy->select('C.*')
			->from(CONTEST . " AS C")
			->where("C.contest_id",$contest_id);
		$sql = $this->db_fantasy->get();
		$result = $sql->row_array();
		return $result;
	}

	/**
	 * Function used for update contest data by id
	 * @param int $contest_id
	 * @param array $post_data
	 * @return array
	 */
	public function update_contest_record($contest_id,$data_arr)
	{
		$this->db_fantasy->where("contest_id",$contest_id);
        $this->db_fantasy->update(CONTEST, $data_arr);
		return true;
	}

	/**
     * used to join contest
     * @param array $post_data
     * @return array
     */
    public function join_game($team,$contest) {
    	$current_date = format_date();
    	$this->db_fantasy->insert(LINEUP_MASTER, $team);
        $lineup_master_id = $this->db_fantasy->insert_id();
        if($lineup_master_id)
        {
        	return $lineup_master_id;
	    }else 
	    {
	       return 0;
	    }
    }

    /**
     * Used for save data in oder table on contest join
     * @param array $data_arr
     * @return array
     */
    public function contest_deposit($data_arr) {
        $current_date = format_date();
        $points = 0;
        $amount = 0;
        if(isset($data_arr['currency_type']) && $data_arr['currency_type'] == "2"){
        	$points = $data_arr["amount"];
        }else{
        	$amount = $data_arr["amount"];
        }
        $order_data = array();
        $order_data["user_id"] = $data_arr['user_id'];
        $order_data["source"] = 0;
        $order_data["source_id"] = 0;
        $order_data["type"] = 0;
        $order_data["date_added"] = $current_date;
        $order_data["modified_date"] = $current_date;
        $order_data["plateform"] = 1;
        $order_data["season_type"] = 1;
        $order_data["status"] = 1;
        $order_data["real_amount"] = $amount;
        $order_data["bonus_amount"] = 0;
        $order_data["winning_amount"] = 0;
        $order_data["points"] = $points;
        $order_data["withdraw_method"] = 0;
        $order_data["reason"] = 'Admin deposit for contest join';
        $order_data['order_unique_id'] = $this->_generate_order_key();
        $this->db->insert(ORDER, $order_data);
        $order_id = $this->db->insert_id();
        if ($order_id) {
            //deduct user balance after contest join
            $this->db->where('user_id', $data_arr['user_id']);
            if(isset($data_arr['currency_type']) && $data_arr['currency_type'] == "2"){
            	$this->db->set('point_balance', 'point_balance + ' . $order_data["points"], FALSE);
        	}else{
            	$this->db->set('balance', 'balance + ' . $order_data["real_amount"], FALSE);
        	}
            $this->db->update(USER);

            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Used for save data in oder table on contest join
     * @param array $data_arr
     * @return array
     */
    public function contest_withdraw($data_arr) {
        $current_date = format_date();
        $points = 0;
        $amount = 0;
        if(isset($data_arr['currency_type']) && $data_arr['currency_type'] == "2"){
        	$points = $data_arr["amount"];
        }else{
        	$amount = $data_arr["amount"];
        }
        $order_data = array();
        $order_data["user_id"] = $data_arr['user_id'];
        $order_data["source"] = $data_arr['source'];
        $order_data["source_id"] = $data_arr['source_id'];
        $order_data["reference_id"] = isset($data_arr['reference_id']) ? $data_arr['reference_id'] : 0;
        $order_data["type"] = 1;
        $order_data["date_added"] = $current_date;
        $order_data["modified_date"] = $current_date;
        $order_data["plateform"] = 1;
        $order_data["season_type"] = 1;
        $order_data["status"] = 1;
        $order_data["real_amount"] = $amount;
        $order_data["bonus_amount"] = 0;
        $order_data["winning_amount"] = 0;
        $order_data["points"] = $points;
        $order_data["withdraw_method"] = 0;
        $order_data["reason"] = !empty($data_arr['reason']) ? $data_arr['reason'] : '';
        $order_data['order_unique_id'] = $this->_generate_order_key();
        $this->db->insert(ORDER, $order_data);
        $order_id = $this->db->insert_id();
        if ($order_id) {
            //deduct user balance after contest join
            $this->db->where('user_id', $data_arr['user_id']);
            if(isset($data_arr['currency_type']) && $data_arr['currency_type'] == "2"){
            	$this->db->set('point_balance', 'point_balance - ' . $order_data["points"], FALSE);
        	}else{
            	$this->db->set('balance', 'balance - ' . $order_data["real_amount"], FALSE);
        	}
            $this->db->update(USER);
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Used for generate order unique id
     * @return string
     */
    public function _generate_order_key() {
        $this->load->helper('security');
        do {
            $salt = do_hash(time() . mt_rand());
            $new_key = substr($salt, 0, 10);
        }

        // Already in the DB? Fail. Try again
        while (self::_order_key_exists($new_key));

        return $new_key;
    }

    /**
     * Used for check order unique id exist or not
     * @param string $key
     * @return int
     */
    private function _order_key_exists($key) {
        $this->db->select('order_id');
        $this->db->where('order_unique_id', $key);
        $this->db->limit(1);
        $query = $this->db->get(ORDER);
        $num = $query->num_rows();
        if ($num > 0) {
            return true;
        }
        return false;
    }

    /**
     * used to get contest prize details for update
     * @param int $join_count
     * @param array $contest
     * @return array
     */
    public function get_contest_prize_distribution_for_update($join_count, $contest) {
        if ((isset($contest['guaranteed_prize']) && $contest['guaranteed_prize'] == '2')) {
            return array();
        }

        $total_amount = $join_count * $contest['entry_fee'];
        $prize_pool_percent = 100;// - $contest['site_rake'];
        $prize_pool = truncate_number_only(($prize_pool_percent / 100) * $total_amount); //new prize pool
        $update_data = array();
        $update_data['prize_pool'] = $prize_pool;
        //check for auto prize pool	
        if ($contest["is_custom_prize_pool"] == '1') {
            $prize_pool_details = json_decode($contest['prize_distibution_detail'], TRUE);
            //update prize pool
            foreach ($prize_pool_details as $key => $value) {
                if(!isset($value['prize_type']) || $value['prize_type'] != 3){
                    $person_count = ($value['max'] - $value['min']) + 1;
                    $per_person = truncate_number_only((($prize_pool * $value['per']) / 100) / $person_count);
                    if(isset($value['prize_type']) && $value['prize_type'] == 2){
                        $per_person = ceil($per_person);
                    }
                    $prize_pool_details[$key]["amount"] = $per_person;
                    $prize_pool_details[$key]["min_value"] = number_format(($per_person * $person_count),"2",".","");
                }
            }
        }

        $update_data['prize_distibution_detail'] = json_encode($prize_pool_details);
        return $update_data;
    }

    public function generate_match_pl_team($data_arr){
		if(empty($data_arr)){
			return false;
		}

		$sports_id = $data_arr['sports_id'];
		$league_id = $data_arr['league_id'];
		$season_game_uid = $data_arr['season_game_uid'];
		$total_team_request = $data_arr['total_team_request'];

		$this->db_fantasy->select('master_lineup_position_id,position_name,number_of_players,max_player_per_position', FALSE)
	        ->from(MASTER_LINEUP_POSITION)
	        ->where("max_player_per_position > ","0")
	        ->where('sports_id', $sports_id);
	    $position = $this->db_fantasy->get()->result_array();
	    $formation = array();
	    foreach($position as $pos){
	        $formation['min_'.strtolower($pos['position_name'])] = $pos['number_of_players'];
	        $formation['max_'.strtolower($pos['position_name'])] = $pos['max_player_per_position'];
	    }

	    //get players
	    $this->db_fantasy->select('P.player_id,P.player_uid,PT.player_team_id,PT.team_league_id,PT.position,PT.salary,T.team_uid,(CASE WHEN JSON_SEARCH(S.playing_list,"one",P.player_uid) IS NOT NULL THEN 1 ELSE 0 END) as is_playing,(CASE WHEN JSON_SEARCH(S.substitute_list,"one",P.player_uid) IS NOT NULL THEN 1 ELSE 0 END) as is_sub,PT.last_match_played', FALSE)
	        ->from(SEASON.' AS S')
	        ->join(PLAYER_TEAM.' AS PT', 'PT.season_id = S.season_id')
	        ->join(PLAYER.' AS P', 'P.player_id = PT.player_id')
	        ->join(TEAM.' AS T', 'T.team_id = PT.team_id')
	        ->where("PT.player_status","1")
	        ->where("PT.is_published","1")
	        ->where('S.league_id', $league_id)
	        ->where('S.season_game_uid', $season_game_uid);
      	$player_list = $this->db_fantasy->get()->result_array();
      	if(!empty($player_list)){
	        //for remove non-playing players
	        $playing11 = array_unique(array_column($player_list, 'is_playing'));
	        $last_played_players = array_unique(array_column($player_list, 'last_match_played'));
	        if(in_array("1", $playing11)){
	          $player_list = array_filter($player_list, function($v) { return $v['is_playing'] == "1"; });
	          $player_list = array_values($player_list);
	        }else if(!empty($last_played_players) && in_array("1",$last_played_players)){
	        	$temp_list = array_filter($player_list, function($v) { return $v['last_match_played'] == "1"; });
	          	$temp_list = array_values($temp_list);
	          	if(!empty($temp_list) && count($temp_list) >= 20){
	          		$player_list = $temp_list;
	          	}
	        }

	        $team_data = array();
	        $team_data['sport_id'] = $sports_id;
	        $team_data['website_id'] = $this->bot_config['website_id'];
	        $team_data['token'] = $this->bot_config['token'];
	        $team_data['number_of_lineups'] = $total_team_request;
	        $team_data['formation'] = $formation;
	        $team_data['season_game_uid'] = $season_game_uid;
	        $team_data['fixture_players'] = $player_list;
	        $api_url = $this->bot_config['api']."/api/system-teams";
	        if($this->bot_config['version'] == "v2"){
	        	unset($team_data['formation']);
	        	unset($team_data['fixture_players']);
	        	$team_data['players'] = array_column($player_list,"player_uid");
	        	$api_url = $this->bot_config['api']."/api/generator/generate_bots";
	        }
			$post_data_json = json_encode($team_data);
	        $header = array("Content-Type:application/json", "Accept:application/json","token:".$this->bot_config['token']);
			//log_message('error','ADMIN***system-teams***request***'.$post_data_json.'**URL:'.$api_url.'#headers#'.json_encode($header));
			$start_date = format_date();
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
	        curl_close($ch);
			$result = json_decode($output, true);

			//pl log
			if (PL_LOG_TX) {
				$log_arr = array("start_date"=>$start_date,"end_date"=>format_date(),"request"=>$team_data,"team_count"=>"0","response"=>"");
				if(isset($result['lineups']) && !empty($result['lineups'])){
					$log_arr['team_count'] = count($result['lineups']);
				}else{
					$log_arr['response'] = json_encode($result);
				}
	            $test_data = json_encode($log_arr);
	            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "1"));
	        }
			
			//log_message('error','ADMIN***system-teams***response'.json_encode($result));
	        if(isset($result['lineups']) && !empty($result['lineups'])){
	        	return array("lineups"=>$result['lineups'],"player_list"=>$player_list,"position"=>$position);
	        }else{
	        	return false;
	        }
	    }else{
	    	return false;
	    }
	}


	public function get_system_user_league_list()
	{  	 
		$current_date = format_date();
		$previous_date = date("Y-m-d H:i:s",strtotime('-15 days',strtotime($current_date)));
		$post_data = $this->input->post();
		//echo "<pre>";print_r($post_data);die;
		if(isset($post_data['from_date']) && $post_data['from_date'] != '')
		{
			$previous_date = $post_data['from_date'];
		}
		if(isset($post_data['to_date']) && $post_data['to_date'] != '')
		{
			$current_date = $post_data['to_date'];
		}	
		$sql = $this->db_fantasy->select("L.league_id,
						L.league_display_name",FALSE)
				->from(CONTEST.' AS C')
		        ->join(COLLECTION_MASTER.' AS CM', 'CM.collection_master_id = C.collection_master_id')
		        ->join(LEAGUE.' AS L', 'L.league_id = CM.league_id')
		        ->join(LINEUP_MASTER.' AS LM', 'LM.collection_master_id = CM.collection_master_id AND LM.is_systemuser = 1 ')
		        ->join(LINEUP_MASTER_CONTEST.' AS LMC', 'LMC.lineup_master_id = LM.lineup_master_id')
		        ->where("C.status","3")
		        ->where("C.total_system_user >",0)
		        //->where("CM.season_scheduled_date >=",$previous_date)
		        //->where("CM.season_scheduled_date <=",$current_date)
		        ;
			
		    if($previous_date != $current_date)
			{
				$this->db_fantasy->where('DATE_FORMAT(CM.season_scheduled_date,"%Y-%m-%d") BETWEEN "'. date('Y-m-d', strtotime($previous_date)). '" and "'. date('Y-m-d', strtotime($current_date)).'"');
			}else
			{
				$this->db_fantasy->like("DATE_FORMAT(CM.season_scheduled_date,'%Y-%m-%d')",$previous_date);
			}
		    if(isset($post_data['sports_id']) && $post_data['sports_id'] != '')
			{
				$this->db_fantasy->where("C.sports_id",$post_data['sports_id']);
			}    
		//$this->db_fantasy->order_by("CM.season_scheduled_date","DESC");	
		$this->db_fantasy->group_by("CM.league_id");
		$sql = $this->db_fantasy->get();
		//echo $this->db_fantasy->last_query();die;
		$result	= $sql->result_array();
		$result=($result)?$result:array();
		return array('result'=>$result);
	}

	public function get_system_user_reports()
	{  	 
		$limit		= 50;
		$page		= 0;
		$post_data = $this->input->post();
		$total = 0;
		$current_date = format_date();
		$previous_date = date("Y-m-d H:i:s",strtotime('-15 days',strtotime($current_date)));
		if(isset($post_data['from_date']) && $post_data['from_date'] != '')
		{
			$previous_date = $post_data['from_date'];
		}
		if(isset($post_data['to_date']) && $post_data['to_date'] != '')
		{
			$current_date = $post_data['to_date'];
		}	
		
		if($post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if($post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
		}
		
		$offset	= $limit * $page;
		
		$sql = $this->db_fantasy->select("CM.collection_master_id,
			CM.season_scheduled_date, 
			CM.collection_name,
			round(SUM( C.total_user_joined)/(count(LMC.lineup_master_contest_id)/count(DISTINCT C.contest_id))) AS total_user_joined, 
			round(SUM( C.total_system_user)/(count(LMC.lineup_master_contest_id)/count(DISTINCT C.contest_id))) AS total_system_user,
			round(SUM(C.entry_fee*C.total_system_user)/(count(LMC.lineup_master_contest_id)/count(DISTINCT C.contest_id)),2) AS total_entry, 
			round((SUM((CASE 
					WHEN ( JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=1 AND `LMC`.`is_winner` = 1) 
					THEN  
						(JSON_UNQUOTE(json_extract(LMC.prize_data,  '$[0].amount' ))) ELSE 0 END))) / count(DISTINCT C.contest_id), 2 )
					AS winnings,
			round((round((SUM((CASE 
					WHEN ( JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=1 AND `LMC`.`is_winner` = 1) 
					THEN  
						(JSON_UNQUOTE(json_extract(LMC.prize_data,  '$[0].amount' ))) ELSE 0 END))) / count(DISTINCT C.contest_id), 2 ) - SUM(C.entry_fee*C.total_system_user)/(count(LMC.lineup_master_contest_id)/count(DISTINCT C.contest_id))),2) AS total_profit,
			round((
				round((round((SUM((CASE 
					WHEN ( JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=1 AND `LMC`.`is_winner` = 1) THEN  
					(JSON_UNQUOTE(json_extract(LMC.prize_data,  '$[0].amount' ))) ELSE 0 END))) / count(DISTINCT C.contest_id), 2 ) - SUM(C.entry_fee*C.total_system_user)/(count(LMC.lineup_master_contest_id)/count(DISTINCT C.contest_id))),2)	
			 	-	( ( SUM( CASE  WHEN C.site_rake > 0 THEN
				 		(C.entry_fee*C.site_rake*C.total_system_user)/100 
				  	  ELSE 0 
				 		END
				 	   ) 
					) /  
						round(SUM( C.total_system_user)/(count(LMC.lineup_master_contest_id)/count(DISTINCT C.contest_id)))
					 )
			),2) AS profit_after_site_rake",FALSE)
				->from(CONTEST.' AS C')
		        ->join(COLLECTION_MASTER.' AS CM', 'CM.collection_master_id = C.collection_master_id')
		        ->join(LINEUP_MASTER.' AS LM', 'LM.collection_master_id = CM.collection_master_id AND LM.is_systemuser = 1 ')
		        ->join(LINEUP_MASTER_CONTEST.' AS LMC', 'LMC.lineup_master_id = LM.lineup_master_id')
		        ->where("C.status","3")
		        ->where("C.total_system_user >",0)
		        //->where("LMC.is_winner","1")
		        //->where("C.collection_master_id",32)
		        ;

		        //->where("CM.season_scheduled_date >=",$previous_date)
		        //->where("CM.season_scheduled_date <=",$current_date);
				//->order_by();
		if($previous_date != $current_date)
		{
			$this->db_fantasy->where('DATE_FORMAT(CM.season_scheduled_date,"%Y-%m-%d") BETWEEN "'. date('Y-m-d', strtotime($previous_date)). '" and "'. date('Y-m-d', strtotime($current_date)).'"');
		}else
		{
			$this->db_fantasy->like("DATE_FORMAT(CM.season_scheduled_date,'%Y-%m-%d')",$previous_date);
		}		
		if(isset($post_data['league_id']) && $post_data['league_id'] != '')
		{
			$this->db_fantasy->where("C.league_id",$post_data['league_id']);
		}
		if(isset($post_data['sports_id']) && $post_data['sports_id'] != '')
		{
			$this->db_fantasy->where("C.sports_id",$post_data['sports_id']);
		}  
		$this->db_fantasy->order_by("CM.season_scheduled_date","DESC");
		$this->db_fantasy->group_by("CM.collection_master_id");
		
		$tempdb = clone $this->db_fantasy;
			$temp_q = $tempdb->get();
			$all_result_array = $temp_q->result_array();
		if($post_data['current_page']==1) {
			$total = $temp_q->num_rows();
		}

		$balance = floatval(round((array_sum(array_column($all_result_array,'profit_after_site_rake'))),2));	

		$sql = $this->db_fantasy->limit($limit,$offset)
						->get();
		//echo $this->db_fantasy->last_query();die;				
		$result	= $sql->result_array();
		$result=($result)?$result:array();
		return array('result'=>$result,'total'=>$total,'balance'=>$balance);
	}

	public function check_network_collection_master_id($post_data)
    {
        if(empty($post_data))
        {
            return array();
        }    

        $this->db_fantasy->select('league_id,network_collection_master_id,network_contest_id,collection_master_id')
                ->from(NETWORK_CONTEST);
        if(!empty($post_data['collection_master_id']))
        {
            $this->db_fantasy->where('network_collection_master_id',$post_data['collection_master_id']);
        }

        if(!empty($post_data['contest_id']))
        {
            $this->db_fantasy->where('network_contest_id',$post_data['contest_id']);
        }       

        $result = $this->db_fantasy->where('active', 1)
                           ->get()
                           ->row_array();
        //echo $this->db_fantasy->last_query();
        return $result;
    }

    public function save_network_lineup_master($insert_arr)
    {
        $this->db_fantasy->insert(NETWORK_LINEUP_MASTER,$insert_arr);
        $lm_id = $this->db_fantasy->insert_id();
        return $lm_id;
    } 

    public function get_system_users_list_not_join($already_join_users,$limit)
	{  	 
		$this->db->select("U.user_id,U.user_unique_id,U.user_name",FALSE)
			->from(USER.' AS U')
			->where("U.status","1")
			->where("U.is_systemuser","1")
			->where("U.user_name != ","")
			->order_by("RAND()");
		if(isset($already_join_users) && !empty($already_join_users)){
			$this->db->where_not_in("U.user_id",$already_join_users);
		}
		$sql = $this->db->limit($limit)->get();
		$result	= $sql->result_array();
		return $result;
	} 

}

/* End of file User_model.php */
/* Location: ./application/models/User_model.php */