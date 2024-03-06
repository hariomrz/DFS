<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Systemuser_model extends MY_Model {
	public $multibot_join_msg = "Something went wrong during contest join. Please contact technical team.";
	public $multibot_join_status = false;
	public function __construct()
	{
		parent::__construct();
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
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
			$this->db->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name)))', strtolower($post_data['keyword']) );
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

    public function get_system_user_fixture_wise_stats($cm_id)
	{
		$result = $this->db_fantasy->select("COUNT(DISTINCT user_id) as total_system_users, COUNT(lineup_master_id) as total_teams")
					->from(LINEUP_MASTER)
					->where('is_systemuser',1)
					->where('collection_master_id',$cm_id)
					->get()
					->row_array();
		return $result;
	}

	public function get_fixture_system_user_list($post_data)
	{
		$limit = 10;
		$page = 0;
		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page'] - 1;
		}
		
		$offset	= $limit * $page;
		$this->db_fantasy->select("user_id,user_name,count(lineup_master_id) as total_teams")
				->from(LINEUP_MASTER)
				->where('is_systemuser',1)
				->where('collection_master_id',$post_data['collection_master_id'])
				->group_by("user_id")
				->order_by("total_teams","DESC");

		$total = 0;
		if($page <= 1)
		{	
			$tempdb = clone $this->db_fantasy;
			$query = $tempdb->get();
			$total = $query->num_rows();
		}
		
		$this->db_fantasy->limit($limit,$offset);
		$sql = $this->db_fantasy->get();
		$result	= $sql->result_array();
		return array("total"=>$total,"result"=>$result);
	}

	public function update_bot_history($update_arr=array(),$where=array())
	{
		if(empty($where) || empty($update_arr))
		{
			return TRUE;
		}

		$this->db_fantasy->where($where);
		$this->db_fantasy->update(BOT_REQ_HISTORY, $update_arr);
		return true;
	}

	public function get_collection_details($cm_id)
	{
		$this->db_fantasy->select("CM.collection_master_id,CM.collection_name,CM.season_scheduled_date,S.season_game_uid,S.playing_announce,S.playing_eleven_confirm,MS.max_player_per_team,L.league_id,L.sports_id", FALSE);
        $this->db_fantasy->from(COLLECTION_MASTER." as CM");
        $this->db_fantasy->join(COLLECTION_SEASON.' as CS', 'CM.collection_master_id = CS.collection_master_id', "INNER");
        $this->db_fantasy->join(SEASON.' as S', 'S.season_id = CS.season_id', "INNER");
        $this->db_fantasy->join(LEAGUE.' as L', 'L.league_id = CM.league_id', "INNER");
        $this->db_fantasy->join(MASTER_SPORTS.' as MS', 'MS.sports_id = L.sports_id', "INNER");
        $this->db_fantasy->where("CM.collection_master_id", $cm_id);
        $this->db_fantasy->group_by("CM.collection_master_id");
        $result = $this->db_fantasy->get()->row_array();
		return $result;
	}

	public function get_already_used_system_users($cm_id)
	{
		$result = $this->db_fantasy->select("count(lineup_master_id) as team_count,user_id,user_name")
					->from(LINEUP_MASTER)
					->where('is_systemuser',1)
					->where('collection_master_id',$cm_id)
					->group_by("user_id")
					->get()
					->result_array();
		return (!empty($result)) ? $result : array();
	}

	public function get_remaining_system_users($input_param=array())
	{
		$this->db->select("U.user_id,U.user_unique_id,U.user_name",FALSE)
			->from(USER.' AS U')
			->where("U.status","1")
			->where("U.is_systemuser","1");
		if(!empty($input_param['already_used_user']))
		{
			$this->db->where_not_in("U.user_id",$input_param['already_used_user']);
		}
		if(!empty($input_param['requested_users']))
		{
			$this->db->limit($input_param['requested_users']);
		}
		$this->db->order_by('U.user_id','RANDOM');
		$result = $this->db->get()->result_array();
		return $result;	
	}

	public function generate_match_pl_team($data_arr)
	{
		if(empty($data_arr)){
			return false;
		}
		$sports_id = $data_arr['sports_id'];
		$league_id = $data_arr['league_id'];
		$season_game_uid = $data_arr['season_game_uid'];
		$total_team_request = $data_arr['total_team_request'];
		$max_player_per_team = $data_arr['max_player_per_team'];
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
	    $this->db_fantasy->select('P.player_id,P.player_uid,PT.player_team_id,PT.team_id,PT.team_id as team,PT.position,PT.salary,(CASE WHEN JSON_SEARCH(S.playing_list,"one",P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_playing,PT.last_match_played,S.substitute_list', FALSE)
	        ->from(SEASON.' AS S')
	        ->join(PLAYER_TEAM.' AS PT', 'PT.season_id = S.season_id')
	        ->join(PLAYER.' AS P', 'P.player_id = PT.player_id')
	        ->where("PT.player_status","1")
	        ->where("PT.is_published","1")
	        ->where('S.league_id', $league_id)
	        ->where('S.season_game_uid', $season_game_uid);
      	$player_list = $this->db_fantasy->get()->result_array();
      	// echo "<pre>";print_r(($player_list));  die; 
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

			//In system genarate start_date
			$roster_list = $player_list;
	       	$team_count = $total_team_request;
	        $pl_list = array();
		    foreach($roster_list as $row){
		        $pl_list[$row['position']][] = $row;
		    }

		    $this->load->helper('systemuser_helper');
		    $start_date = format_date();
		    $skipped = array();
		    $team_list = array();
		    $team_pl_list = array();
		    $repeat_limit = $team_count * 2;
		    $j = 0;
		    for($i=0;$i<$team_count;$i++){
		    	if($j > $repeat_limit){
		    		$i = $team_count;
		    	}
		    	$team_arr = systemuser_make_team($pl_list,$max_player_per_team,$formation,$sports_id);
		    	if(!empty($team_arr) && count($team_arr) == 11){
	                $team_plr = array_column($team_arr,"player_team_id");
	                $c_vc = array_rand($team_plr,2);
	                $c_id = $team_plr[$c_vc['0']];
	                $vc_id = $team_plr[$c_vc['1']];

	                $team_pls = $team_plr;
	                asort($team_pls);
	                $team_pls[] = $c_id."_1";
	                $team_pls[] = $vc_id."_2";
	                $team_pl_str = implode("_",$team_pls);
	                if(!in_array($team_pl_str,$team_pl_list)){
	                	$tmp_team = array("pl"=>$team_plr,"c_id"=>$c_id,"vc_id"=>$vc_id);
	                	$team_list[$i] = $tmp_team;
	                	$team_pl_list[] = $team_pl_str;
	                }else{
	            		$i--;
	                }
	            }else{
	            	$i--;
	            }
	            $j++;
		    }

		  	$result['lineups'] = $team_list;  
			//echo "<pre>";print_r(($result));  die; 
			//End In system genarate lineup
	        if(isset($result['lineups']) && !empty($result['lineups'])){
	        	return array("lineups"=>$result['lineups'],"player_list"=>$player_list,"position"=>$position);
	        }else{
	        	return false;
	        }
	    }else{
	    	return false;
	    }
	}

	public function add_system_user_teams($team_data=array())
	{
		if(empty($team_data))
		{
			return FALSE;
		}

		$this->db_fantasy->insert_batch(LINEUP_MASTER,$team_data);
		$total_affected_rows = $this->db_fantasy->affected_rows();
		$first_insert_id = $this->db_fantasy->insert_id();
		return array("first_id"=>$first_insert_id,"effected_rows"=>$total_affected_rows);
	}

	/**
	 * Function used for get contest details by id
	 * @param array $post_data
	 * @return array
	 */
	public function get_contest_detail($post_data)
	{
		$this->db_fantasy->select('G.contest_id,G.contest_unique_id,G.sports_id,G.league_id,G.collection_master_id,G.contest_name,IFNULL(G.contest_title,"") as contest_title,G.max_bonus_allowed,G.prize_distibution_detail,G.user_id,G.season_scheduled_date,G.size,G.entry_fee,G.currency_type,G.prize_pool,G.status,G.site_rake,G.total_user_joined,G.guaranteed_prize,G.is_pin_contest,G.is_feature,G.prize_type,G.is_custom_prize_pool,G.multiple_lineup,G.minimum_size,G.total_system_user,G.is_auto_recurring')
			->from(CONTEST." AS G");
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
	 * Function used for get system users with team count for contest
	 * @param array $post_data
	 * @return array
	 */
	public function get_system_user_valid_teams($post_data){
		if(!isset($post_data['contest_id'])){
			return false;
		}

		$this->db_fantasy->select("LM.lineup_master_id,LM.collection_master_id,LM.league_id,LM.user_id,LM.is_systemuser,LM.user_name,LM.team_name,IFNULL(LMC.lineup_master_contest_id,0) as is_joined",FALSE)
                ->from(LINEUP_MASTER . " LM")
                ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.lineup_master_id=LM.lineup_master_id AND LMC.contest_id='".$post_data['contest_id']."' AND LMC.fee_refund=0", "LEFT")
                ->where("LM.is_systemuser", 1)
                ->where("LM.collection_master_id",$post_data["collection_master_id"])
                ->where("IFNULL(LMC.lineup_master_contest_id,0) = 0")
                ->order_by("LM.user_id","RANDOM");
        $result = $this->db_fantasy->get()->result_array();
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

		$this->db_fantasy->select("LM.user_id,count(LM.user_id) AS team_count,LM.user_name")
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

	public function get_lmc_data($lmc_data,$contest_id)
	{
		if(empty($lmc_data) || empty($contest_id))
		{
			return array();
		}
		$this->db_fantasy->select("lineup_master_contest_id,lineup_master_id");
		$this->db_fantasy->where("contest_id",$contest_id);
		$lm_ids = array_column($lmc_data,"lineup_master_id");
		$lm_id_chunks = array_chunk($lm_ids,200);
		if(!empty($lm_id_chunks))
		{
			$this->db_fantasy->group_start();
			foreach ($lm_id_chunks as $chunk_key => $chunk_arr) 
			{
				if($chunk_key == 0)
				{
					$this->db_fantasy->where_in('lineup_master_id',$chunk_arr);
				}
				else
				{
					$this->db_fantasy->or_where_in('lineup_master_id',$chunk_arr);
				}  
			}
			$this->db_fantasy->group_end();
		}  
		
        $rs = $this->db_fantasy->get(LINEUP_MASTER_CONTEST)->result_array();
		return $rs;
	}

	public function delete_lmc_data($lmc_data,$contest_id)
	{
		if(empty($lmc_data) || empty($contest_id))
		{
			return false;
		}
		$this->db_fantasy->where("contest_id",$contest_id);
		$lm_ids = array_column($lmc_data,"lineup_master_id");
		$lm_id_chunks = array_chunk($lm_ids,200);
		if(!empty($lm_id_chunks))
		{
			$this->db_fantasy->group_start();
			foreach ($lm_id_chunks as $chunk_key => $chunk_arr) 
			{
				if($chunk_key == 0)
				{
					$this->db_fantasy->where_in('lineup_master_id',$chunk_arr);
				}
				else
				{
					$this->db_fantasy->or_where_in('lineup_master_id',$chunk_arr);
				}  
			}
			$this->db_fantasy->group_end();
		}  
		
        $this->db_fantasy->delete(LINEUP_MASTER_CONTEST);
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
     * used to join contest
     * @param array $post_data
     * @return array
     */
    public function join_game_with_multiple_system_users($team=array(),$contest=array()) 
	{
		if(empty($team) || empty($contest))
		{
			return array("join_status"=>$this->multibot_join_status,"msg"=>$this->multibot_join_msg);
		}
		try {	
			$current_date = format_date();
			//prepare batch data for lmc.
			$lmc_data = array();
			$lmc_error = 0;
			$contest_id = $contest["contest_id"];
			$user_team_count = array();
			$multiple_lineup = (!empty($contest["multiple_lineup"])) ? $contest["multiple_lineup"] : 1;
			$added_teams = 0;
			$sys_user_ids = array();
			
			//get already join system users with joined team count
			$al_joined_arr = array("contest_id"=>$contest_id);
			$already_joined_users = $this->get_system_users_for_contest($al_joined_arr); 
			if(!empty($already_joined_users))
			{
				$user_team_count = array_column($already_joined_users,"team_count","user_id");
			}

			foreach($team as $tkey=>$tvalue)
			{	
				if(empty($tvalue['user_id']) || empty($tvalue['lineup_master_id']))
				{
					$lmc_error = 1;
					continue;
				}

				//if added teams already reached then skip all remaining sytem user teams
				if(count($lmc_data) == $contest['total_team_request'])
				{
					break;
				}

				//if single user teams data already reached allowed multiple lineup limit then don't include that user.
				if(!empty($user_team_count[$tvalue['user_id']]))
				{
					if($user_team_count[$tvalue['user_id']] >= $multiple_lineup)
					{
						continue;
					}
					else
					{
						$user_team_count[$tvalue['user_id']] = $user_team_count[$tvalue['user_id']]+1;
					}
				}
				else
				{
					$user_team_count[$tvalue['user_id']] = 1;
				}


				$sys_user_ids[$tvalue['lineup_master_id']] = $tvalue['user_id'];
				$lmc_data[] = array(
					"lineup_master_id"	=> $tvalue['lineup_master_id'],
					"contest_id"		=> $contest_id,
					"created_date"		=> $current_date,
				);
			}

			//echo "LE : $lmc_error <pre>";print_r($lmc_data);die; 
			//log_message("error","Prepared teams : ".count($lmc_data)." | user_team_count : ".json_encode($user_team_count));
			if(count($lmc_data) < $contest['total_team_request'])
			{
				throw new Exception("No of teams requested is not available..");
			}

			if($lmc_error==1 || empty($lmc_data))
			{
				throw new Exception($this->multibot_join_msg);
			}

			//echo $lmc_error;print_r($lmc_data);die;
			$this->db_fantasy->insert_batch(LINEUP_MASTER_CONTEST,$lmc_data);
			$lmc_insert_count = $this->db_fantasy->affected_rows();
			if($lmc_insert_count <= 0)
			{
				$this->delete_lmc_data($lmc_data,$contest_id);
				throw new Exception($this->multibot_join_msg);
			}

			$total_teams = count($lmc_data);
			//if contest is paid only then proceed with transaction flow otherwise just update contest record. 
			if($contest['entry_fee'] > 0)
			{
				//prepare data for transactions/order.
				$valid_lmcs = $this->get_lmc_data($lmc_data,$contest_id);
				if(empty($valid_lmcs))
				{
					throw new Exception($this->multibot_join_msg);
				}
				
				$lm_user_data = array_column($valid_lmcs,'lineup_master_contest_id','lineup_master_id');
				
				//echo "<pre>";print_r($lm_user_data);print_r($team);print_r($valid_lmcs);die;
				$order_data = array();
				$order_error = 0;
				foreach($lmc_data as $lmkey=>$lm)
				{
					if(empty($sys_user_ids[$lm['lineup_master_id']]))
					{
						$order_error = 1;
						continue;
					}

					$sys_userid = $sys_user_ids[$lm['lineup_master_id']];
					$common_arr = array(
						"user_id"		=> $sys_userid,
						"date_added"	=> $current_date,
						"modified_date"	=> $current_date
					);

					$lmc_id = $lm_user_data[$lm['lineup_master_id']];
					$points = 0;
					$amount = 0;
					if(isset($contest['currency_type']) && $contest['currency_type'] == "2"){
						$points = $contest["entry_fee"];
					}else{
						$amount = $contest["entry_fee"];
					}

					$withdraw_data = array();
					$withdraw_data["order_unique_id"] = $this->_generate_order_unique_key();
					$withdraw_data["source"] = 1;
					$withdraw_data["source_id"] = $lmc_id;
					$withdraw_data["reference_id"] = $contest_id;
					$withdraw_data["type"] = 1;
					$withdraw_data["real_amount"] = $amount;
					$withdraw_data["points"] = $points;
					$withdraw_data["reason"] = '';
					$withdraw_data["status"] = 1;
					$withdraw_data['custom_data'] = json_encode(array("contest_name"=>$contest['contest_name']));
					$withdraw_data = array_merge($withdraw_data,$common_arr);
					$order_data[] = $withdraw_data;

				}

				if($order_error == 1 || empty($order_data))
				{
					$this->delete_lmc_data($lmc_data,$contest_id);
					throw new Exception($this->multibot_join_msg);
				}
				//echo "<pre>";print_r($order_data);die;

				//insert orders/transactions combined for both deposit and withdrawal for system user
				$this->db->insert_batch(ORDER, $order_data);
				$total_order_inserted = $this->db->affected_rows();
				if($total_order_inserted <= 0)
				{
					$this->delete_lmc_data($lmc_data,$contest_id);
					throw new Exception($this->multibot_join_msg);
				}

				//now update contest records with latest user joined count, system user count and prize details
				$contest_info = $this->get_contest_record($contest["contest_id"]);
				$joined_count = $contest_info["total_user_joined"] + $total_teams;

				//increment contest joined count
				$this->db_fantasy->where('contest_id', $contest['contest_id']);
				$this->db_fantasy->where("total_user_joined+".$total_teams." <= size",NULL,FALSE);
				$this->db_fantasy->set('total_user_joined', 'total_user_joined+'.$total_teams, FALSE);
				$this->db_fantasy->set('total_system_user', 'total_system_user+'.$total_teams, FALSE);

				//for update prize distribution details
				if (!empty($joined_count) && $joined_count > $contest['minimum_size'] && $joined_count <= $contest['size'] && $contest['entry_fee'] > 0) {
					$prize_pool = $this->get_contest_prize_distribution_for_update($joined_count, $contest);
					if (!empty($prize_pool) && isset($prize_pool['prize_pool'])) {
						$this->db_fantasy->set('prize_pool', $prize_pool['prize_pool']);
					}
					if (!empty($prize_pool) && isset($prize_pool['prize_distibution_detail'])) {
						$this->db_fantasy->set('prize_distibution_detail', $prize_pool['prize_distibution_detail']);
					}
				}
				if($joined_count > $contest['minimum_size']){
					$contest['total_user_joined'] = $joined_count;
					$current_prize = reset_contest_prize_data($contest);
					if(!empty($current_prize)){
						$this->db_fantasy->set('current_prize', json_encode($current_prize));
					}
				}
				$this->db_fantasy->update(CONTEST);
				$is_contest_updated = $this->db_fantasy->affected_rows();
				//if contest is updated then return with true otherwise remove contest join entries and contest withdraw entries for this request.
				if($is_contest_updated)
				{
					
					return array("join_status"=>true,"msg"=>"Game joined successfully with requested teams.");	
				}
				else
				{
					$this->delete_lmc_data($lmc_data,$contest_id);
					$lmc_ids_data = array_column($valid_lmcs,'lineup_master_contest_id');
					$this->delete_order_data_for_multi_join($lmc_ids_data,$contest_id);
					throw new Exception($this->multibot_join_msg);
				}
			}
			else
			{
				//increment contest joined count for free contest
				$this->db_fantasy->where('contest_id', $contest['contest_id']);
				$this->db_fantasy->where("total_user_joined+".$total_teams." <= size",NULL,FALSE);
				$this->db_fantasy->set('total_user_joined', 'total_user_joined+'.$total_teams, FALSE);
				$this->db_fantasy->set('total_system_user', 'total_system_user+'.$total_teams, FALSE);
				$this->db_fantasy->update(CONTEST);
				$is_contest_updated =$this->db_fantasy->affected_rows();
				if($is_contest_updated){
					return array("join_status"=>true,"msg"=>"Game joined successfully with requested teams.");
				}else{
					$this->delete_lmc_data($lmc_data,$contest_id);
					throw new Exception($this->multibot_join_msg);
				}
			}

			return array("join_status"=>$this->multibot_join_status,"msg"=>$this->multibot_join_msg);

		}catch(Exception $e){
			return array("join_status"=>$this->multibot_join_status,"msg"=>$e->getMessage());
		}	
    }

    public function delete_order_data_for_multi_join($lmc_ids_data,$contest_id)	
	{
		if(empty($lmc_ids_data) || empty($contest_id))
		{
			return false;
		}
		$this->db->where("source",1);
		$this->db->where("reference_id",$contest_id);
		$lmc_id_chunks = array_chunk($lmc_ids_data,200);
		if(!empty($lmc_id_chunks))
		{
			$this->db->group_start();
			foreach ($lmc_id_chunks as $chunk_key => $chunk_arr) 
			{
				if($chunk_key == 0)
				{
					$this->db->where_in('source_id',$chunk_arr);
				}
				else
				{
					$this->db->or_where_in('source_id',$chunk_arr);
				}  
			}
			$this->db->group_end();
		}  
		$this->db->limit(count($lmc_ids_data));
        $this->db->delete(ORDER);
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


    public function get_system_user_reports($post_data = array())
	{  	 
		$limit = RECORD_LIMIT;
		$page = 0;
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
		
		$this->db_fantasy->select("C.contest_id,CM.collection_master_id,CM.season_scheduled_date,CM.collection_name,
			round(SUM(C.total_user_joined)/(count(LMC.lineup_master_contest_id)/count(DISTINCT C.contest_id))) AS total_user_joined,round(SUM( C.total_system_user)/(count(LMC.lineup_master_contest_id)/count(DISTINCT C.contest_id))) AS total_system_user",FALSE)
			->from(CONTEST.' AS C')
	        ->join(COLLECTION_MASTER.' AS CM', 'CM.collection_master_id = C.collection_master_id')
	        ->join(LINEUP_MASTER.' AS LM', 'LM.collection_master_id = CM.collection_master_id AND LM.is_systemuser = 1 ')
	        ->join(LINEUP_MASTER_CONTEST.' AS LMC', 'LMC.lineup_master_id = LM.lineup_master_id')
	        ->where("C.status","3")
	        ->where("C.total_system_user >",0);

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
		
		if(isset($post_data['csv']) && $post_data['csv']=='false')
		{
			$tempdb = clone $this->db_fantasy;
			$temp_q = $tempdb->get();
			$all_result_array = $temp_q->result_array();
			$total = 0;
			if($post_data['current_page']==1) {
				$total = $temp_q->num_rows();
			}

			$sql = $this->db_fantasy->limit($limit,$offset);
			$sql = $this->db_fantasy->get();			
			$result	= $sql->result_array();
			$result=($result)?$result:array();
			return array('result'=>$result,'total'=>$total);
		}else{
			$result	= $this->db_fantasy->get()->result_array();
			$result=($result)?$result:array();
			return array('result'=>$result);
		}
	}

	public function get_contest_ids($cmid){
		$result = $this->db_fantasy->select('contest_id')
					->from(CONTEST)
					->where('collection_master_id',$cmid)
					->where('total_system_user >',0)
					->get()->result_array();
		return array_column($result,'contest_id');
	}

	public function get_system_user_league_list($post_data)
	{  	 
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
		$sql = $this->db_fantasy->select("L.league_id,L.league_display_name",FALSE)
				->from(CONTEST.' AS C')
		        ->join(LEAGUE.' AS L', 'L.league_id = C.league_id')
		        ->where("C.status","3")
		        ->where("C.total_system_user >",0);
			
		    if($previous_date != $current_date)
			{
				$this->db_fantasy->where('DATE_FORMAT(C.season_scheduled_date,"%Y-%m-%d") BETWEEN "'. date('Y-m-d', strtotime($previous_date)). '" and "'. date('Y-m-d', strtotime($current_date)).'"');
			}else
			{
				$this->db_fantasy->like("DATE_FORMAT(C.season_scheduled_date,'%Y-%m-%d')",$previous_date);
			}
		    if(isset($post_data['sports_id']) && $post_data['sports_id'] != '')
			{
				$this->db_fantasy->where("C.sports_id",$post_data['sports_id']);
			}    
		$this->db_fantasy->group_by("L.league_id");
		$sql = $this->db_fantasy->get();
		$result	= $sql->result_array();
		$result = ($result) ? $result : array();
		return $result;
	}

	public function get_bot_prize($contest_id_arr){
		$result = $this->db_fantasy->select("LMC.contest_id,round((SUM((CASE WHEN (LMC.is_winner = 1 AND LM.is_systemuser = 0) THEN LMC.amount ELSE 0 END))),2) AS realuser_winnings,round((SUM((CASE WHEN (LMC.is_winner = 1 AND LM.is_systemuser = 1) THEN LMC.amount ELSE 0 END))) , 2 ) AS systemuser_winnings",FALSE)
					->from(LINEUP_MASTER_CONTEST." AS LMC")
					->join(LINEUP_MASTER.' LM','LMC.lineup_master_id = LM.lineup_master_id','LEFT')
					->where_in("LMC.contest_id", $contest_id_arr)
					->group_by('LMC.contest_id')
					->get()
					->result_array();

		$bot_prize_data = array();
		foreach($result as $res){
			$bot_prize_data[$res['contest_id']]= $res;
		}
		return $bot_prize_data;
	}

	/**
	 * Function used for get system users with team count for contest
	 * @param array $post_data
	 * @return array
	 */
	public function get_available_system_users_for_contest($post_data,$team_required=0){
		if(!isset($post_data['contest_id'])){
			return false;
		}

		$team_select_str = (!empty($team_required)) ? ",group_concat(LM.lineup_master_id) as all_team_ids,group_concat(LMC.lineup_master_id) as used_team_ids":"";
		
		$this->db_fantasy->select("(count(LM.lineup_master_id) - IFNULL(count(LMC.lineup_master_contest_id),0)) as available_team_count,LM.user_id,IFNULL(count(LMC.lineup_master_contest_id),0) as already_joined_teams,LM.user_name $team_select_str",FALSE)
                ->from(LINEUP_MASTER . " LM")
                ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.lineup_master_id=LM.lineup_master_id AND LMC.contest_id='".$post_data['contest_id']."' AND LMC.fee_refund=0", "LEFT")
                ->group_by("LM.user_id")
				->having("available_team_count > ",0)
				->where("LM.is_systemuser",1)
                ->where("LM.collection_master_id",$post_data["collection_master_id"])
				->order_by("available_team_count","ASC")
				->order_by("LM.lineup_master_id","ASC");

        $result = $this->db_fantasy->get()->result_array();
		//echo $this->db_fantasy->last_query();die;
        return $result;
	}

	public function insert_bot_history($insert_arr=array())
	{
		if(empty($insert_arr))
		{
			return TRUE;
		}

		$this->db_fantasy->insert(BOT_REQ_HISTORY, $insert_arr);
		return $this->db_fantasy->insert_id();
	}

	public function get_bot_history($select="*",$where=array())
	{
		if(empty($where))
		{
			return TRUE;
		}

		$this->db_fantasy->select($select);
		$this->db_fantasy->from(BOT_REQ_HISTORY);
		$this->db_fantasy->where( $where );
		$query = $this->db_fantasy->get();
		return $query->row_array();
	}
}

/* End of file User_model.php */
/* Location: ./application/models/User_model.php */
