<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Contest_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
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
            $this->db_fantasy->where($where);
            $this->db_fantasy->update($table, $data);
            $return_flag = TRUE;
        }
        return $return_flag;
    }

	/**
	 * Function used for get fixture contest
	 * @param array $post_data
	 * @return array
	 */
	public function get_fixture_contest($post_data)
	{ 
		$sort_field = 'C.group_id';
		$sort_order = 'ASC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','minimum_size','size','entry_fee','prize_pool','is_auto_recurring','guaranteed_prize','max_bonus_allowed','added_date')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$this->db_fantasy->select("C.contest_id,C.status,C.contest_unique_id,C.contest_template_id,C.collection_master_id,C.league_id,C.group_id,C.season_scheduled_date,C.minimum_size,C.size,C.contest_name,C.currency_type,C.entry_fee,C.prize_pool,C.total_user_joined,C.prize_distibution_detail,C.multiple_lineup,C.prize_type, C.guaranteed_prize,C.is_auto_recurring,C.is_pin_contest,MG.group_name,C.total_system_user,IFNULL(C.sponsor_name,'') as sponsor_name,IFNULL(C.sponsor_logo,'') as sponsor_logo,IFNULL(C.sponsor_link,'') as sponsor_link,IFNULL(C.video_link,'') as video_link,IFNULL(C.contest_title,'') as contest_title,C.is_reverse,C.is_scratchwin,C.max_bonus_allowed,C.is_2nd_inning", FALSE)
			->from(CONTEST." AS C")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = C.group_id','INNER')
			->where('C.collection_master_id',$post_data['collection_master_id'])
			->group_by('C.contest_id');

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db_fantasy->like('C.contest_name', $post_data['keyword']);
		}
		$this->db_fantasy->order_by('C.group_id','DESC');
		$sql = $this->db_fantasy->order_by($sort_field, $sort_order)
						->get();
		$result	= $sql->result_array();
		return $result;
	}

	/**
	 * Function used for delete contest
	 * @param array $post_data
	 * @return array
	 */
	public function delete_contest($post_data)
	{
		$this->db_fantasy->where("contest_id",$post_data['contest_id']);
		$this->db_fantasy->where("total_user_joined","0");
		$this->db_fantasy->delete(CONTEST);
		$is_deleted = $this->db_fantasy->affected_rows();
		return $is_deleted;
	}

	/**
	 * Function used for mark pin contest
	 * @param array $post_data
	 * @return array
	 */
	public function mark_pin_contest($post_data)
	{
		if(empty($post_data)){
			return false;
		}

		$is_pin_contest = 1;
		if(isset($post_data['is_pin_contest'])){
			$is_pin_contest = $post_data['is_pin_contest'];
		}

		//update status in database
		$this->db_fantasy->where('contest_id',$post_data['contest_id']);
		$this->db_fantasy->set('is_pin_contest', $is_pin_contest);
		$this->db_fantasy->update(CONTEST);
		return true;
	}

	public function change_scratch_win_status($post_data){

		$this->db_fantasy->update(CONTEST,['is_scratchwin'=>$post_data['status']],['contest_id'=>$post_data['contest_id']]);
		return $this->db_fantasy->affected_rows();
	}

	public function save_contest($contest_data)
	{
		$this->db_fantasy->trans_start();
		
		$contest_data['added_date'] = format_date();
		$contest_data['modified_date'] = format_date();
		$this->db_fantasy->insert(CONTEST,$contest_data);
		$contest_id = $this->db_fantasy->insert_id();

		$this->db_fantasy->trans_complete();
		$this->db_fantasy->trans_strict(FALSE);
		if ($this->db_fantasy->trans_status() === FALSE)
		{
		    $this->db_fantasy->trans_rollback();
			return false;
		}
		else
		{
			$this->db_fantasy->trans_commit();
			return $contest_id;
		}
	}

	public function get_contest_detail($game_unique_id)
	{
		$this->db_fantasy->select('G.contest_id,G.contest_unique_id,G.sports_id,G.collection_master_id,G.contest_name,IFNULL(G.contest_title,"") as contest_title,G.max_bonus_allowed,G.prize_distibution_detail,G.user_id,G.season_scheduled_date,G.league_id,G.size,G.entry_fee,G.currency_type,G.prize_pool,G.status,G.added_date,G.site_rake,G.total_user_joined,G.guaranteed_prize,G.guaranteed_prize as prize_pool_type,G.is_pin_contest,G.is_feature,G.prize_type,G.is_custom_prize_pool,G.multiple_lineup,IFNULL(G.completed_date,"") as completed_date,G.is_scratchwin,G.is_tie_breaker,G.is_pdf_generated,CM.collection_name,CM.is_tour_game,G.is_2nd_inning,GROUP_CONCAT(DISTINCT CS.season_id) as season_ids')
			->from(CONTEST." AS G")
			->join(COLLECTION_MASTER." AS CM","CM.collection_master_id = G.collection_master_id","INNER")
			->join(COLLECTION_SEASON." AS CS","CS.collection_master_id = CM.collection_master_id","INNER")
			->where('G.contest_unique_id', $game_unique_id);
		$result = $this->db_fantasy->get()->row_array();
		return $result;
	}

	public function get_contest_users($post_data)
	{
		$current_date = format_date();
		$limit = RECORD_LIMIT;
		$page = 1;
		$sort_field = "LMC.game_rank";
		$sort_order = "ASC";
		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page'];
		}
		$offset	= $limit * ($page-1);
		$this->db_fantasy->select("LM.lineup_master_id,LM.user_id,LM.collection_master_id,LMC.lineup_master_contest_id,LM.user_name,LM.team_name,LMC.total_score,LMC.game_rank,LMC.is_winner,LMC.amount,LMC.bonus,LMC.coin,IFNULL(LMC.merchandise,'') as merchandise,LM.is_systemuser,LM.booster_id,LMC.booster_points,LM.is_pl_team",FALSE)
				->from(LINEUP_MASTER_CONTEST." AS LMC")
				->join(LINEUP_MASTER.' LM','LMC.lineup_master_id = LM.lineup_master_id','INNER')
				->where("LMC.contest_id", $post_data['contest_id']);
				
		if(isset($post_data['user_id']) && $post_data['user_id'] != ""){
			$this->db_fantasy->where("LM.user_id", $post_data['user_id']);
		}

		if(isset($post_data['is_systemuser']) && in_array($post_data['is_systemuser'],array("0","1"))){
			$this->db_fantasy->where("LM.is_systemuser", $post_data['is_systemuser']);
		}
		
		if(isset($post_data['user_id']) && $post_data['user_id'] != ""){
			$sql = $this->db_fantasy->order_by($sort_field,$sort_order)->get();
			$result	= $sql->result_array();
			$total = count($result);
		}else{
			$tempdb = clone $this->db_fantasy;
			// $total = 0;
			// if($page == 1){
				$query = $this->db_fantasy->get();
				$total = $query->num_rows();
			// }

			$sql = $tempdb->limit($limit, $offset)
						->order_by($sort_field,$sort_order)
						->get();
			$result	= $sql->result_array();
		}
		return array('result'=>$result, 'total'=>$total);
	}

	/**
    * Function used for get user team details
    * @param int $lineup_master_contest_id
    * @return array
    */
	public function get_user_contest_team_detail($lineup_master_contest_id)
	{
		$this->db_fantasy->select("LMC.lineup_master_contest_id,LMC.lineup_master_id,LMC.total_score,LMC.game_rank,LMC.is_winner,LMC.amount,LMC.bonus,LMC.coin,IFNULL(LMC.merchandise,'') as merchandise,LM.collection_master_id,LM.user_id,LM.user_name,LM.team_name,LM.team_data,LM.is_systemuser,LM.is_pl_team,CM.is_lineup_processed,LM.booster_id",FALSE)
				->from(LINEUP_MASTER_CONTEST." LMC")
				->join(LINEUP_MASTER." LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
				->join(COLLECTION_MASTER." CM","CM.collection_master_id = LM.collection_master_id","INNER")
				->where("LMC.lineup_master_contest_id",$lineup_master_contest_id)
				->limit(1);
		$result = $this->db_fantasy->get()->row_array();
		return $result;
	}

	/**
     * used for get user team players list
     * @param int $lineup_master_contest_id
     * @param array $post_data
     * @return array
     */
    public function get_user_team_with_score($team_info) {
        if (empty($team_info)) {
            return false;
        }
        $team_info['team_data']['pl'] = array_fill_keys($team_info['team_data']['pl'],0);
        $is_lineup_processed = $team_info['is_lineup_processed'];
        $cm_id = $team_info['collection_master_id'];
        $lm_id = $team_info['lineup_master_id'];

        $lineup_table = LINEUP."_".$cm_id;
        if($is_lineup_processed == "1" && $this->db_fantasy->table_exists($lineup_table)) {
            $sql = $this->db_fantasy->select("L.player_team_id,ROUND(IFNULL(L.score,0),1) AS score,L.captain", FALSE)
                    ->from($lineup_table." L")
                    ->where('L.lineup_master_id', $lm_id)
                    ->get();
            $result = $sql->result_array();
            if(!empty($result)){
            	$c_vc = array_column($result,"player_team_id","captain");
            	$team_info['team_data']['c_id'] = $c_vc['1'];
            	$team_info['team_data']['vc_id'] = isset($c_vc['2']) ? $c_vc['2'] : 0;
            	$team_info['team_data']['pl'] = array_column($result,"score","player_team_id");
            }
        }else if(in_array($is_lineup_processed,array("2","3"))){
        	$sql = $this->db_fantasy->select("*", FALSE)
                    ->from(COMPLETED_TEAM)
                    ->where('lineup_master_id', $lm_id)
                    ->get();
            $team_data = $sql->row_array();
        	if(!empty($team_data)){
        		$team_info['team_data'] = json_decode($team_data['team_data'],TRUE);
        	}
        }
        return $team_info;
    }

    /**
	 * used to get fixture all players list
	 * @param int $cm_id
	 * @return array
	*/
	public function get_fixture_rosters($cm_id,$lineup_data=0)
	{
		$this->db_fantasy->select("PT.player_team_id,PT.position,PT.last_match_played as lmp,IFNULL(PT.salary,0) as salary,PT.feed_verified,PT.is_published,PT.is_new,P.player_id,P.player_uid,P.full_name,P.display_name,IFNULL(P.image,'') as player_image,T.team_id,IFNULL(T.display_team_name,T.team_name) AS team_name,IFNULL(T.display_team_abbr,T.team_abbr) AS team_abbr,IFNULL(T.jersey,T.feed_jersey) AS jersey,P.sports_id", FALSE)
			->from(COLLECTION_SEASON . " AS CS")
	        ->join(PLAYER_TEAM.' AS PT', 'PT.season_id = CS.season_id', 'INNER')
	        ->join(PLAYER.' AS P', 'P.player_id = PT.player_id', 'INNER')
			->join(TEAM.' AS T', 'T.team_id = PT.team_id', 'INNER')
			->where("CS.collection_master_id",$cm_id)
			->where("PT.is_deleted",0)
			->where("PT.player_status",1)
	        ->group_by('P.player_id')
	        ->order_by('P.display_name','ASC');

        if($lineup_data == 1){
        	$this->db_fantasy->select('(CASE WHEN JSON_SEARCH(S.playing_list,"one",P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_playing,S.playing_announce',FALSE);
        	$this->db_fantasy->select('(CASE WHEN JSON_SEARCH(S.substitute_list,"one",P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_sub',FALSE);
        	$this->db_fantasy->join(SEASON.' S','S.season_id = PT.season_id','INNER');
        }

		$sql = $this->db_fantasy->get();
		$result	= $sql->result_array();
		return $result;
	}

	/**
	 * Used for get league list by sports id
	 * @param int $sports_id
	 * @return array
	 */
	public function get_fixture_league_list($sports_id)
	{
		$current_date = format_date();
		$this->db_fantasy->select('L.league_id,L.league_uid,L.league_abbr,IFNULL(league_display_name,league_name) AS league_name',FALSE)
		 	->from(LEAGUE." AS L")
		 	->join(COLLECTION_MASTER.' AS CM','CM.league_id = L.league_id','INNER')
			->where('L.active','1')
			->where('CM.season_game_count','1')
			->where('L.sports_id',$sports_id)
			->group_by('L.league_id')
			->order_by('L.league_schedule_date','DESC');
        $sql = $this->db_fantasy->get();
		$result = $sql->result_array();
		return $result;
	}

	/**
	* Function used for get league fixture list for contest dashboard
	* @param int $league_id
	* @return array
	*/
	public function get_league_fixture_list($league_id)
	{
		$this->db_fantasy->select('CM.collection_master_id,CM.collection_name,CM.season_scheduled_date')
			->from(COLLECTION_MASTER." AS CM")
			->where('CM.season_game_count',1)
			->where('CM.league_id', $league_id);
		$result = $this->db_fantasy->get()->result_array();
		return $result;
	}

	/**
	 * Function used for get contest list on contest dashboard page
	 * @param array $post_data
	 * @return [type] [description]
	 */
	public function get_contest_list($post_data)
	{
		$current_date = format_date();
		$limit = RECORD_LIMIT;
		$page = 1;
		$status = isset($post_data['status']) ? $post_data['status'] : "";
		$sort_field = 'C.contest_id';
		$sort_order = 'DESC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','entry_fee','minimum_size','size','total_user_joined','prize_pool','max_bonus_allowed','spot_left','system_teams','real_teams','current_earning','potential_earning')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		if(!empty($post_data['pageSize']) && $post_data['pageSize'])
		{
			$limit = $post_data['pageSize'];
		}

		if(!empty($post_data['currentPage']) && $post_data['currentPage'])
		{
			$page = $post_data['currentPage'];
		}
		$offset	= $limit * ($page-1);

		$this->db_fantasy->select('C.contest_id,C.contest_unique_id,C.collection_master_id,C.league_id,C.group_id,C.season_scheduled_date,C.minimum_size,C.size,C.contest_name,C.currency_type,C.entry_fee,C.prize_pool,C.total_user_joined,C.multiple_lineup,C.prize_type,C.guaranteed_prize,C.is_auto_recurring,C.is_pin_contest,CS.season_id,C.max_bonus_allowed,(C.size-C.total_user_joined) as spot_left,SUM(IF(LM.is_systemuser=1,1,0)) as system_teams,SUM(IF(LM.is_systemuser=0,1,0)) as real_teams,(C.entry_fee*(SUM(IF(LM.is_systemuser=0,1,0)))) as current_earning,(C.size-C.total_user_joined)*C.entry_fee as potential_earning,IFNULL(C.contest_title,"") as contest_title,C.is_2nd_inning,C.status,count(DISTINCT TS.tournament_id) as tournament_count', FALSE)
			->from(CONTEST." AS C")
			->join(COLLECTION_MASTER." AS CM", 'CM.collection_master_id = C.collection_master_id','INNER')
			->join(COLLECTION_SEASON." AS CS", 'CS.collection_master_id = CM.collection_master_id','INNER')
			->join(LINEUP_MASTER_CONTEST." AS LMC", 'LMC.contest_id = C.contest_id','LEFT')
			->join(LINEUP_MASTER." AS LM", 'LM.lineup_master_id = LMC.lineup_master_id','LEFT')
			->join(TOURNAMENT_SEASON." AS TS", 'TS.contest_id = C.contest_id','LEFT')
			->where('C.sports_id',$post_data['sports_id'])
			->where('CM.season_game_count',1)
			->group_by('C.contest_id');

		if(isset($post_data['league_id']) && $post_data['league_id'] != "")
		{
			$this->db_fantasy->where('C.league_id', $post_data['league_id']);
		}

		if(isset($post_data['collection_master_id']) && $post_data['collection_master_id'] != "")
		{
			$this->db_fantasy->where('C.collection_master_id', $post_data['collection_master_id']);
		}
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db_fantasy->like('C.contest_name', $post_data['keyword']);
		}

		if(isset($post_data['group_id']) && $post_data['group_id'] != "")
		{
			$this->db_fantasy->where('C.group_id', $post_data['group_id']);
		}

		switch ($status)
		{
			case 'current_game':
				$this->db_fantasy->where('C.status','0');
				$this->db_fantasy->where("C.season_scheduled_date <= ",$current_date);
				break;
			case 'completed_game':
				$this->db_fantasy->where('C.status >','1');
				break;
			case 'cancelled_game':
				$this->db_fantasy->where('C.status','1');
				break;
			case 'upcoming_game':
				$this->db_fantasy->where('C.status','0');
				$this->db_fantasy->where("C.season_scheduled_date > ",$current_date);
				break;
			default:
				break;
		}
		$total = 0;
		if($page == 1){
			$tempdb = clone $this->db_fantasy;
	        $temp_q = $tempdb->get();
			$total = $temp_q->num_rows();
		}

		$this->db_fantasy->order_by($sort_field, $sort_order);
		$result = $this->db_fantasy->limit($limit,$offset)->get()->result_array();
		//echo $this->db_fantasy->last_query();die('dfd');
		return array('result' => $result, 'total' => $total);
	}

	public function get_contest_by_lineup_master_contest_ids($lineup_master_contest_id)
	{
		$result =  $this->db_fantasy->from(LINEUP_MASTER_CONTEST." LMC")
						->join(CONTEST." C","LMC.contest_id = C.contest_id","INNER")
						->where_in("LMC.lineup_master_contest_id",$lineup_master_contest_id)
						->get()
						->result_array();
                return $result;
	}

	public function get_contest_by_id($contest_id)
	{
		$result =  $this->db_fantasy->from(CONTEST." C")
						->where_in("C.contest_id",$contest_id)
						->get()
						->result_array();
                return $result;
	}

	public function export_contest_winner_data()
	{	
		$post_data = $this->input->get();
		$contest_id = $post_data['contest_id'];
		$return = array(
						'contest_name'=> '',
						'winner_name'=> '',
						'rank'=> 0,
						'entry_fee'=> 0,
						'total_entries'=> 0,
						'winning_amount'=> 0,
						"winning_bonus" => 0,
						"winning_coins" => 0,
						"winning_merchandise" => ''
					);

		$result = $this->db_fantasy->select("C.contest_name,LM.user_name as winner_name,IF(LM.is_systemuser=1,'YES','') as Bot,LMC.game_rank as rank_value,C.entry_fee,LMC.total_score,C.total_user_joined as total_entries,LMC.amount as winning_amount,LMC.bonus as winning_bonus,LMC.coin as winning_coins,IFNULL(LMC.merchandise,'') as winning_merchandise",FALSE)
				->from(CONTEST . " AS C")
				->join(LINEUP_MASTER_CONTEST . " AS LMC", "LMC.contest_id = C.contest_id", "INNER")
				->join(LINEUP_MASTER.' LM', 'LM.lineup_master_id = LMC.lineup_master_id','LEFT')
				->where('C.contest_id',$contest_id)
				->order_by('rank_value','ASC')
				->get()->result_array();		
				
				
		if(empty($result))
		{
			return $return;
		}

		return $result;
	}

	/**
     * Used for get collection all completed contest list for revert prize
     * @param int $collection_master_id
     * @return string
     */
 	public function get_collection_revert_prize_contest($collection_master_id)
	{
		$this->db_fantasy->select('C.contest_id,C.status,C.contest_unique_id,', FALSE)
			->from(CONTEST." AS C")
			->where('C.status','3')
			->where('C.collection_master_id',$collection_master_id);
		$sql = $this->db_fantasy->get();
		$result	= $sql->result_array();
		return $result;
	}

	/**
	*@method get_collections_by_filter
	*@uses function to get collection list by league uid with pagination
	****/
	public function get_collections_by_filter($post_data)
	{
		
	    $this->db_fantasy->select("CM.collection_name,CM.collection_master_id,C.season_scheduled_date AS season_schedule_date",FALSE)
			->from(COLLECTION_MASTER.' AS CM')
			->join(CONTEST." AS C","C.collection_master_id = CM.collection_master_id","INNER");
		if($this->input->post("league_id"))
		{
			$this->db_fantasy->where("CM.league_id",$this->input->post("league_id"));
		}

		if($this->input->post("sports_id"))
		{
			$this->db_fantasy->where("C.sports_id",$this->input->post("sports_id"));
		}

		if(!empty($post_data['fetch_only_completed']))
		{
			switch ($post_data['fetch_only_completed'])
			{
				case 'current_game':
					$this->db_fantasy->where('C.status','0');
					break;
				case 'completed_game':
					$this->db_fantasy->where('C.status >','1');
					break;
				case 'cancelled_game':
					$this->db_fantasy->where('C.status','1');
					break;
				case 'upcoming_game':
				default:
					$this->db_fantasy->where("C.season_scheduled_date>",format_date());
					break;
			}	
		}

		if(!empty($post_data['fetch_completed_collection']))
		{
			switch ($post_data['fetch_completed_collection'])
			{
				case 1:
					$this->db_fantasy->where('CM.status','1');
					break;
				case 0:
					$this->db_fantasy->where('CM.status','0');
					break;
			}	
		}			

		$collection_data = $this->db_fantasy->order_by('C.season_scheduled_date','ASC')
								->group_by("CM.collection_master_id")
								->get()
								->result_array();
		return $collection_data;
	}

	/**
	 * Function used for get collection contest list
	 * @param array $post_data
	 * @return [type] [description]
	 */
	public function get_collection_contest_list($post_data)
	{
		$current_time = format_date();
		$limit = 200;
		$page = 0;
		$sort_field = 'C.contest_id';
		$sort_order = 'DESC';
		$offset	= $limit * $page;
		$this->db_fantasy->select('C.status,C.contest_id,C.contest_unique_id,C.contest_template_id,C.collection_master_id,C.league_id,C.group_id,C.season_scheduled_date,C.minimum_size,C.size,C.contest_name
		', FALSE)
			->from(CONTEST." AS C")
			->join(COLLECTION_MASTER." AS CM", 'CM.collection_master_id = C.collection_master_id','INNER')
			->join(COLLECTION_SEASON." AS CS", 'CS.collection_master_id = CM.collection_master_id','INNER')
			->where('C.sports_id',$post_data['sports_id'])
			->where('CM.season_game_count',1)
			->group_by('C.contest_id');

		if(isset($post_data['collection_master_id']) && $post_data['collection_master_id'] != "")
		{
			$this->db_fantasy->where('C.collection_master_id', $post_data['collection_master_id']);
		}

		if(isset($post_data['league_id']) && $post_data['league_id'] != "")
		{
			$this->db_fantasy->where('C.league_id', $post_data['league_id']);
		}

		if(isset($post_data['group_id']) && $post_data['group_id'] != "")
		{
			$this->db_fantasy->where('C.group_id', $post_data['group_id']);
		}

		$tempdb = clone $this->db_fantasy;
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows();
		$this->db_fantasy->order_by($sort_field, $sort_order);
		$result = $this->db_fantasy->limit($limit,$offset)->get()->result_array();
		return array('result' => $result, 'total' => $total);
	}

	/**
     * Used for get contest participants list
     * @param array $post_data
     * @return array
     */
	public function get_contest_participant_report($post_data)
	{
		$current_time = format_date();
		$limit = 2;
		$page = 0;
		$sort_field = 'C.contest_id';
		$sort_order = 'DESC';
		$this->db_fantasy->select('COUNT(DISTINCT LM.user_id) as users,
		SUM(IF(LM.is_systemuser=1,1,0)) as system_teams,SUM(IF(LM.is_systemuser=0,1,0)) as real_teams
		', FALSE)
			->from(CONTEST." AS C")
			->join(LINEUP_MASTER_CONTEST." AS LMC", 'LMC.contest_id = C.contest_id','LEFT')
			->join(LINEUP_MASTER." AS LM", 'LM.lineup_master_id = LMC.lineup_master_id','LEFT')
			->where('C.sports_id',$post_data['sports_id'])
			->group_by('C.contest_id');

		if(isset($post_data['contest_id']) && $post_data['contest_id'] != "")
		{
			$this->db_fantasy->where('C.contest_id', $post_data['contest_id']);
		}

		if(isset($post_data['league_id']) && $post_data['league_id'] != "")
		{
			$this->db_fantasy->where('C.league_id', $post_data['league_id']);
		}

		if(isset($post_data['group_id']) && $post_data['group_id'] != "")
		{
			$this->db_fantasy->where('C.group_id', $post_data['group_id']);
		}

		$result = $this->db_fantasy->get()->row_array();
		return $result;
	}

	public function get_all_users_contest_revenue($user_ids = array())
	{
        $this->db_fantasy->select("LM.user_id, LMC.contest_id, C.site_rake, C.entry_fee",FALSE)
			->from(CONTEST." C")
            ->join(LINEUP_MASTER_CONTEST." LMC","LMC.contest_id = C.contest_id","INNER")
			->join(LINEUP_MASTER." LM","LMC.lineup_master_id= LM.lineup_master_id","INNER")
			->where("LM.user_id>",0)
			->where_in("C.status",array(2,3))
			->where_in("C.prize_type",array(0,1));
        if(!empty($user_ids))
        {
            $this->db_fantasy->where_in("LM.user_id", $user_ids);
        }
		$record = $this->db_fantasy->get()->result_array();
		return $record;
	}

	public function get_users_win_loss($user_ids = array())
	{
    	$this->db_fantasy->select("LM.user_id, count(LMC.lineup_master_contest_id ) as matches_played, sum(LMC.is_winner) as matches_won, (count(LMC.lineup_master_contest_id )-sum(LMC.is_winner)) as matches_lost ",FALSE)
			->from(CONTEST." C")
            ->join(LINEUP_MASTER_CONTEST." LMC","LMC.contest_id = C.contest_id","INNER")
			->join(LINEUP_MASTER." LM","LMC.lineup_master_id= LM.lineup_master_id","INNER")
			->where("LM.user_id>",0)
            ->where_in("C.status",array(2,3));
        if(!empty($user_ids))
        {
            $this->db_fantasy->where_in("LM.user_id", $user_ids);
        }
		$this->db_fantasy->group_by("LM.user_id");
		$record = $this->db_fantasy->get()->result_array();
		return $record;					
	}

	public function get_lobby_fixture_list($post_data) {
        $current_date = format_date();
        $this->db_fantasy->select("CM.collection_master_id,CM.season_scheduled_date,CM.deadline_time,CM.league_id,CM.collection_name,CS.season_id,SUM(C.total_user_joined) as total_players,SUM(CASE WHEN C.prize_type=0 THEN C.prize_pool WHEN C.prize_type=1 THEN C.prize_pool ELSE 0 END) AS total_prize_pool", FALSE);
        $this->db_fantasy->from(COLLECTION_MASTER." as CM");
        $this->db_fantasy->join(COLLECTION_SEASON.' as CS', 'CM.collection_master_id = CS.collection_master_id', "INNER");
        $this->db_fantasy->join(CONTEST.' as C', 'C.collection_master_id = CM.collection_master_id', "INNER");
        $this->db_fantasy->where("CM.season_scheduled_date > DATE_ADD('{$current_date}', INTERVAL CM.deadline_time MINUTE)");
        $this->db_fantasy->where("C.sports_id", $post_data['sports_id']);
        $this->db_fantasy->where('CM.status', '0');
        $this->db_fantasy->where('CM.season_game_count', '1');
        $this->db_fantasy->where('C.status', '0');
        $this->db_fantasy->where('C.contest_access_type', '0');
        $this->db_fantasy->group_by("CM.collection_master_id");
		$this->db_fantasy->order_by("CM.season_scheduled_date", "ASC");
        $result = $this->db_fantasy->get()->result_array();
        return $result;
	}

	public function get_live_upcoming_collection($sports_id=7)
	{
		$past_time = date("Y-m-d H:i:s", strtotime($current_date_time . " -7 days"));
		$result = $this->db_fantasy->select('CM.collection_name,CS.season_id,CM.season_scheduled_date')
		->from(COLLECTION_MASTER.' CM')
		->join(COLLECTION_SEASON.' CS','CM.collection_master_id=CS.collection_master_id')
		->join(LEAGUE.' L','CM.league_id=L.league_id')
		->where('L.sports_id', $sports_id)
		->where('CM.status',0)
		->where('CM.season_game_count',1)
		->where("CM.season_scheduled_date >= ", $past_time)
		->order_by('CM.season_scheduled_date','DESC')
		->get()
		->result_array();
		return $result;
	}

	public function get_all_season_schedule($count_only=FALSE)
	{
		$sort_field	= 'season_scheduled_date';
		$sort_order	= 'DESC';
		$limit		= 100;
		$page		= 0;
		$post_data = $this->input->post();
		$league_id	= isset($post_data['league_id']) ? $post_data['league_id'] : "";
		$sports_id	= isset($post_data['sports_id']) ? $post_data['sports_id'] : "";
		$week		= isset($post_data['week']) ? $post_data['week'] : "";
		$fromdate	= isset($post_data['fromdate']) ? $post_data['fromdate'] : "";
		$todate		= isset($post_data['todate']) ? $post_data['todate'] : "";
		$status		= isset($post_data['status']) ? $post_data['status'] : "";

		$this->db_fantasy->select("S.season_id,S.league_id,S.season_game_uid,S.subtitle,S.format,S.type,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away, S.status, S.week, S.season_scheduled_date,S.is_published,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,(CASE WHEN format = 1 THEN 'ONE-DAY' WHEN format = 2 THEN 'TEST' WHEN format = 3 THEN 'T20' END) AS format_str,IFNULL(L.league_display_name,L.league_name) AS league_abbr,S.is_salary_changed,S.is_published",false)
			->from(SEASON." AS S")
			->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
			->join(TEAM.' T1','T1.team_id=S.home_id','INNER')
			->join(TEAM.' T2','T2.team_id=S.away_id','INNER');
			
		if($sports_id != "")
		{
			$this->db_fantasy->where("L.sports_id", $sports_id);
		}
		if($league_id != "")
		{
			$this->db_fantasy->where("S.league_id", $league_id);
		}

		if($status != "")
		{
			if($status == 'not_complete'){
				$this->db_fantasy->where("status !=", 2);
			}
			else{
				$this->db_fantasy->where("status", $status);
			}
		}

		$sql =   $this->db_fantasy->group_by('S.season_id')
						->order_by($sort_field, $sort_order)
						->get();
		$result	= $sql->result_array();
		$result = ($result) ? $result : array();
		return $result;
	}

	public function get_fixture_users($season_id)
	{
		$users = $this->db_fantasy->select('LM.user_id')
					->from(LINEUP_MASTER.' LM')
					->join(COLLECTION_MASTER. ' CM','CM.collection_master_id=LM.collection_master_id')
					->join(COLLECTION_SEASON. ' CS','CM.collection_master_id=CS.collection_master_id')
					->where('CS.season_id',$season_id)
					->group_by('LM.user_id')
					->get()->result_array();

		return $users;
	}

	/**
	 * [contest_list description]
	 * @MethodName contest_list
	 * @Summary This function used for get all contest List
	 * @return     [array]
	 */
	public function get_completed_contest_report($post_params)
	{ 
		$sort_field = 'G.season_scheduled_date';
		$sort_order = 'DESC';
		$limit      = 50;
		$page       = 0;
		
		$post_data = $post_params;
		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','collection_name','size','entry_fee','season_scheduled_date','prize_pool','guaranteed_prize','total_user_joined','minimum_size','site_rake')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		$classic = $this->lang->line('classic');
		$second_inning = $this->lang->line('2nd_inning');
		$this->db_fantasy->select("G.group_id, G.group_id,CM.collection_name,G.contest_id,G.contest_unique_id, G.contest_name, G.entry_fee, G.prize_pool,G.site_rake, G.total_user_joined, G.size,G.minimum_size, (CASE 
		WHEN G.guaranteed_prize=0 THEN 'No Guarantee'
		 WHEN G.guaranteed_prize=1 THEN 'Guaranteed prize custom'
		 WHEN G.guaranteed_prize=2 THEN 'Guaranteed'
		 END
		 ) AS guaranteed_prize,G.season_scheduled_date AS season_scheduled_date,SUM(IF(LM.is_systemuser=1,1,0)) as system_teams,SUM(IF(LM.is_systemuser=0,1, 0)) as real_teams,G.currency_type,G.max_bonus_allowed,G.entry_fee*G.total_user_joined as total_entry_fee,G.entry_fee*G.total_system_user AS botuser_total_real_entry_fee,
		 (CASE WHEN G.is_2nd_inning=1 THEN '{$second_inning}' WHEN G.is_2nd_inning = 0 THEN '{$classic}' END) AS feature_type,MG.group_name",false)
		->from(CONTEST." AS G")
		->join(MASTER_GROUP." AS MG","MG.group_id = G.group_id","INNER")
		->join(COLLECTION_MASTER." AS CM","CM.collection_master_id = G.collection_master_id","LEFT")
		->join(LINEUP_MASTER_CONTEST." AS LMC", 'LMC.contest_id = G.contest_id','LEFT')
		->join(LINEUP_MASTER." AS LM", 'LM.lineup_master_id = LMC.lineup_master_id','LEFT')
		->where('G.status','3');
		$game_type = isset($post_data['game_type']) ? $post_data['game_type'] : "";

		if(isset($post_data['sports_id']) && $post_data['sports_id'] != '')
		{
			$this->db_fantasy->where('G.sports_id',$post_data['sports_id']);
		}
		if(isset($post_data['league_id']) && $post_data['league_id'] != '')
		{
			$this->db_fantasy->where('G.league_id',$post_data['league_id']);
		}
		if(isset($post_data['contest_name']))
		{
			$this->db_fantasy->like('G.contest_name',$post_data['contest_name']);
		}
		if(isset($post_data['group_id']))
		{
		$this->db_fantasy->like('G.group_id',$post_data['group_id']);
		}
		if(isset($post_data['collection_master_id']) && $post_data['collection_master_id']!="")
		{
			$this->db_fantasy->where('G.collection_master_id',$post_data['collection_master_id']);
		}
	
		if($post_data['feature_type']== 2)
		{
			$this->db_fantasy->where('G.is_2nd_inning',1);
		}

		if(isset($post_data['feature_type']) && $post_data['feature_type']=="0")
		{
			$this->db_fantasy->where('G.is_2nd_inning',0);
		}
		$this->db_fantasy->group_by('G.contest_unique_id');
	    if(!empty($post_data['from_date'])&&!empty($post_data['to_date'])){
			$this->db_fantasy->where("DATE_FORMAT(G.season_scheduled_date,'%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(G.season_scheduled_date,'%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."'");
		}
		$tempdb = clone $this->db_fantasy;
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows();
		if(!empty($sort_field) && !empty($sort_order))
		{
			$this->db_fantasy->order_by($sort_field, $sort_order);
		}

		if(!empty($limit) && !$post_data["csv"])
		{
			$this->db_fantasy->limit($limit, $offset);
		}
		$sql = $this->db_fantasy->get();
		$result	= $sql->result_array();
		return array('result'=>$result, 'total'=>$total);
	}

	public function get_team_create_rank(){
		$sort_field = 'total_team';
		$sort_order = 'DESC';
		$limit = 50;
		$page = 0;
		$current_date = format_date(); 
		$from_date = date('Y-m-d',strtotime($current_date)).' 00:00:00';
		$to_date = date('Y-m-d',strtotime($current_date. ' + 7 days')).' 23:59:59';
		$post_data = $this->input->post();

		if(!empty($post_data['from_date']) && !empty($post_data['to_date'])){
			
			$from_date = $post_data['from_date'];
			$to_date = $post_data['to_date'];
		}

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('user_name','rank_value')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		
		$sum_arr = 'total_team';
		$result = $this->db_fantasy->select('user_id,user_name,count(lineup_master_id) as total_team,(RANK() OVER (ORDER BY count(lineup_master_id) DESC)) as rank_value',false)
		->from(LINEUP_MASTER.' LM')
		->where('LM.is_systemuser',0)
		->group_by('LM.user_id')
		// ->where("LM.date_added between '{$from_date}' and '{$to_date}'",null,false)
		->where("DATE_FORMAT(LM.date_added, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(LM.date_added, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ")
		->order_by($sort_field, $sort_order);

		if(isset($post_data['csv']) && $post_data['csv']==TRUE){
			$result = $this->db_fantasy->get()->result_array();
		}
		else{
			$result = $this->db_fantasy->limit($limit,$offset)->get();
			$result = $result->result_array();
		}

		$grand_total = array_sum(array_column($result,$sum_arr));
		return array('result'=>$result,$sum_arr=>$grand_total);
 	}

 	public function get_contest_rank(){
		$sort_field = 'rank_value';
		$sort_order = 'ASC';
		$limit = 50;
		$page = 0;
		$current_date = format_date(); 
		$from_date = date('Y-m-d',strtotime($current_date)).' 00:00:00';
		$to_date = date('Y-m-d',strtotime($current_date. ' + 7 days')).' 23:59:59';
		$post_data = $this->input->post();

		if(!empty($post_data['from_date']) && !empty($post_data['to_date'])){
			
			$from_date = $post_data['from_date'];
			$to_date = $post_data['to_date'];
		}

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('user_name','rank_value')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;

		$sum_arr = 'winning';
		$result = $this->db_fantasy->select('LM.user_id,LM.user_name,count(LM.lineup_master_id) as winning,RANK() OVER (ORDER BY count(LM.lineup_master_id) DESC) as rank_value', false)
		->from(LINEUP_MASTER_CONTEST . ' LMC')
		->join(LINEUP_MASTER . ' LM', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER')
		->join(CONTEST . ' C', 'C.contest_id = LMC.contest_id', 'INNER')
		// ->where("LMC.created_date between '{$from_date}' and '{$to_date}'",null,false)
		->where("DATE_FORMAT(LMC.created_date, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(LMC.created_date, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ")
		->where_in('C.status',[2,3])
		->where('LM.is_systemuser',0)
		->group_by('LM.user_id')
		->order_by($sort_field, $sort_order);

		if(isset($post_data['csv']) && $post_data['csv']==TRUE){
			$result = $this->db_fantasy->get()->result_array();
		}
		else{
			$result = $this->db_fantasy->limit($limit,$offset)->get();
			$result = $result->result_array();
		}
		$grand_total = array_sum(array_column($result,$sum_arr));

		$fee_site_rake = $this->db_fantasy->select('sum(C.entry_fee) as fee,ROUND(sum(C.site_rake),2) as site_rake', false)
				->from(LINEUP_MASTER_CONTEST . ' LMC')
				->join(LINEUP_MASTER . ' LM', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER')
				->join(CONTEST . ' C', 'C.contest_id = LMC.contest_id', 'INNER')
				// ->where("LMC.created_date between '{$from_date}' and '{$to_date}'",null,false)
				->where("DATE_FORMAT(LMC.created_date, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(LMC.created_date, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ")
				->where_in('C.status',[2,3])
				->where('LM.is_systemuser',0)
				->get()->row_array();

				$total_contest = $this->db_fantasy->select('LMC.contest_id', false)
				->distinct()
				->from(LINEUP_MASTER_CONTEST . ' LMC')
				->join(LINEUP_MASTER . ' LM', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER')
				->join(CONTEST . ' C', 'C.contest_id = LMC.contest_id', 'INNER')
				// ->where("LMC.created_date between '{$from_date}' and '{$to_date}'",null,false)
				->where("DATE_FORMAT(LMC.created_date, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(LMC.created_date, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ")
				->where_in('C.status',[2,3])
				->where('LM.is_systemuser',0)
				->get()->result_array();

		//echo $this->db_fantasy->last_query();exit;
		$result = array('result'=>$result,$sum_arr=>$grand_total,"fee"=>(int)$fee_site_rake['fee'],"site_rake"=>(float)$fee_site_rake['site_rake'],"total_contest"=>count($total_contest));
		return $result;
	}

	/**
     * used to get team wise bench player counts
     * @param array $lineup_master_ids
     * @return array
    */
    public function get_team_bench_players($lineup_master_id) {
        $this->db_fantasy->select("priority,player_id,out_player_id,status,IFNULL(reason,'') as reason", FALSE);
        $this->db_fantasy->from(BENCH_PLAYER);
        $this->db_fantasy->where('lineup_master_id', $lineup_master_id);
        $this->db_fantasy->order_by("priority","ASC");
        return $this->db_fantasy->get()->result_array();
    }

    /**
     * used to get fixture bench stats
     * @param int $collection_master_id
     * @return array
    */
    public function get_fixture_bench_stats($cm_id) {
        $this->db_fantasy->select("COUNT(DISTINCT LM.lineup_master_id) as total_teams,IFNULL(COUNT(DISTINCT BP.lineup_master_id),0) as bench_applied,IFNULL(GROUP_CONCAT(DISTINCT CASE WHEN BP.status=1 THEN BP.lineup_master_id ELSE 0 END),0) as bench_used", FALSE);
        $this->db_fantasy->from(LINEUP_MASTER." as LM");
        $this->db_fantasy->join(BENCH_PLAYER.' as BP', 'BP.lineup_master_id = LM.lineup_master_id', 'LEFT');
        $this->db_fantasy->where('LM.collection_master_id', $cm_id);
        $result = $this->db_fantasy->get()->row_array();
        return $result;
    }


	function get_match_detail_by_suid($season_game_uid)
	{
		
			$this->db_fantasy->select('S.season_id,S.season_scheduled_date, S.season_game_uid,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away, S.league_id,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,S.status',FALSE)
			->from(SEASON . " AS S")
			->join(TEAM.' as T1', 'T1.team_id = S.home_id',"INNER")
			->join(TEAM.' as T2', 'T2.team_id = S.away_id',"INNER")
			->where('S.season_game_uid',$season_game_uid);
			$results = $this->db_fantasy->get()->row_array();
		
		if(!empty($results)){
			$results['home_flag'] = get_image(0,$results['home_flag']);							
			$results['away_flag'] = get_image(0,$results['away_flag']);							
		}	
		
		return $results;
	}

	public function get_collection_master_by_suid($season_id)
	{
		$result = $this->db_fantasy->select('CM.collection_master_id,CM.collection_name,CM.delay_minute,CM.season_scheduled_date')
		->from(COLLECTION_MASTER.' CM')
		->join(COLLECTION_SEASON.' CS','CM.collection_master_id=CS.collection_master_id')
		->where('CS.season_id',$season_id)
		->get()
		->row_array();

		return $result;
	}

	public function get_user_game_stats($post_data)
	{
		$user_id = $post_data['user_id'];
		if(isset($post_data['from_date'])){
            $startDate = date("Y-m-d", strtotime($post_data['from_date']));
		}
        else{
            $startDate = date('Y-m-d', strtotime('-10 day'));
        }
        if(isset($post_data['to_date'])){
            $endDate = date("Y-m-d", strtotime($post_data['to_date']));
        }
        else{
			$endDate = date("Y-m-d");
        }

        $this->db_fantasy->select("count(lmc.lineup_master_contest_id) as contest_joined,SUM(CASE WHEN lmc.is_winner = 1 THEN 1 ELSE 0 END) as contest_won")
				->from(LINEUP_MASTER .' lm')
				->join(LINEUP_MASTER_CONTEST .' lmc','lmc.lineup_master_id=lm.lineup_master_id','INNER')
				->where('lm.user_id',$user_id)
				->where("lm.date_added BETWEEN '{$startDate}' AND '{$endDate}'");
		$query = $this->db_fantasy->get();  
		$result = $query->row_array();

		$this->db_fantasy->select("lm.date_added,SUM(CASE WHEN c.entry_fee > 0 THEN 1 ELSE 0 END) AS paid,SUM(CASE WHEN c.entry_fee <= 0 THEN 1 ELSE 0 END) AS free")
				->from(LINEUP_MASTER.' lm')
				->join(LINEUP_MASTER_CONTEST.' lmc','lmc.lineup_master_id=lm.lineup_master_id')
				->join(CONTEST.' c','c.contest_id=lmc.contest_id')
				->where('lm.user_id',$user_id)
				->where("lm.date_added BETWEEN '{$startDate}' AND '{$endDate}'")
				->group_by('lm.date_added');
		$free_paid_sql = $this->db_fantasy->get();
		$free_paid = $free_paid_sql->result_array();

		$this->db_fantasy->select("ms.sports_name,c.sports_id, count(c.sports_id) as sport_count")
				->from(LINEUP_MASTER.' lm')
				->join(LINEUP_MASTER_CONTEST.' lmc','lmc.lineup_master_id=lm.lineup_master_id')
				->join(CONTEST.' c','c.contest_id=lmc.contest_id')
				->join(MASTER_SPORTS.' ms','ms.sports_id=c.sports_id')
				->where('lm.user_id',$user_id)
				->where("lm.date_added BETWEEN '{$startDate}' AND '{$endDate}'")
				->group_by('c.sports_id');
		$sports_sql = $this->db_fantasy->get();
		$sports = $sports_sql->result_array();

		$return_arr = array();
		$return_arr['contest_joined'] = isset($result['contest_joined']) ? $result['contest_joined'] : 0;
		$return_arr['contest_won'] = isset($result['contest_won']) ? $result['contest_won'] : 0;
		$return_arr['free_paid'] = !empty($free_paid) ? $free_paid : array();
		$return_arr['sport_pref'] = !empty($sports) ? $sports : array();
		return $return_arr;
	}


	/**
	*@method get_all_upcoming_collections
	*@uses function to get collection list by league uid with pagination
	****/
	public function get_all_upcoming_collections()
	{
		$current_date = format_date();
		$post_data = $this->input->post();
	    $this->db_fantasy->select("CM.collection_name,CM.collection_master_id,C.season_scheduled_date as scheduled_date",FALSE)
								->from(COLLECTION_MASTER.' AS CM')
								->join(CONTEST." AS C","C.collection_master_id = CM.collection_master_id","INNER");
		
		if($this->input->post("league_id"))
		{
			$this->db_fantasy->where("CM.league_id",$this->input->post("league_id"));
		}

		if($this->input->post("sports_id"))
		{
			$this->db_fantasy->where("C.sports_id",$this->input->post("sports_id"));
		}

		$this->db_fantasy->where("C.season_scheduled_date > ",$current_date);

		$collection_data = $this->db_fantasy->order_by('C.season_scheduled_date','ASC')
								->group_by("CM.collection_master_id")
								->get()->result_array();
		// die($this->db->last_query());
		return $collection_data;
	}


	/**
	 * [description]
	 * @MethodName get_sport_leagues
	 * @Summary This function used for get all league for selected sport
	 * @param      na
	 * @return     [array]
	 */
	public function get_sport_all_recent_leagues()
	{
		$sort_field	= 'league_abbr';
		$sort_order	= 'ASC';
               
		$post_data = $this->input->post();

		$Current_DateTime = format_date();

		$this->db_fantasy->select('L.league_uid,L.league_id, L.league_name, L.league_abbr,L.active AS status,league_schedule_date,MS.max_player_per_team,show_global_leaderboard,L.is_promote,L.image,L.sports_id')
						->from(LEAGUE." AS L")
						->join(MASTER_SPORTS . " AS MS", "MS.sports_id = L.sports_id", 'INNER')
						->join(SEASON . " AS S", "L.league_id = S.league_id", 'left');
						
						if(!isset($post_data['admin_list_filter']))
						{
							$this->db_fantasy->where('S.season_scheduled_date >',$Current_DateTime);//will be uncomment in case or real data;
						}

						$this->db_fantasy->where('MS.active', '1');
						$this->db_fantasy->where('L.active', '1');
						$this->db_fantasy->where('L.archive_status', '0');
        if(!empty($post_data['sports_id']) || isset($post_data['sports_id']))
        {
            $this->db_fantasy->where('L.sports_id', $post_data['sports_id']);
        }

        if(!empty($post_data['league_ids']) || isset($post_data['league_ids']))
        {
            $this->db_fantasy->where_in('L.league_id', $post_data['league_ids']);
        }

        if(!empty($post_data['not_league']) || isset($post_data['not_league']))
        {
            $this->db_fantasy->where_not_in('L.league_id', $post_data['not_league']);
        }

        if(isset($post_data['active']))
        {
            $this->db_fantasy->where('L.active', $post_data['active']);
        }
						
		$sql = $this->db_fantasy->order_by($sort_field, $sort_order);
		$sql = $this->db_fantasy->group_by("L.league_id");

		$tempdb = clone $this->db_fantasy;
		$query  = $this->db_fantasy->get();
		$total  = $query->num_rows();

        if(isset($limit) && isset($page))
        {
            $offset	= $limit * $page;
            
            $tempdb->limit($limit,$offset);
        }
        $sql = $tempdb->get();
		$result = $sql->result_array();
		return array('result'=>$result,'total'=>$total);
	}

	public function contest_promotion_detail($contest_id)
	{
		$this->db_fantasy->select('G.contest_id,G.contest_unique_id,G.sports_id,G.collection_master_id,G.contest_name,IFNULL(G.contest_title,"") as contest_title,G.max_bonus_allowed,G.prize_distibution_detail,G.user_id,G.season_scheduled_date,G.league_id,G.size,G.entry_fee,G.currency_type,G.prize_pool,G.status,G.added_date,G.site_rake,G.total_user_joined,G.guaranteed_prize,G.guaranteed_prize as prize_pool_type,G.is_pin_contest,G.is_feature,G.prize_type,G.is_custom_prize_pool,G.multiple_lineup,IFNULL(G.completed_date,"") as completed_date,G.is_scratchwin,G.is_tie_breaker,G.is_pdf_generated,G.is_2nd_inning,MS.sports_name,CM.collection_name')
			->from(CONTEST." AS G")
			->join(COLLECTION_MASTER." AS CM","CM.collection_master_id = G.collection_master_id","INNER")
			->join(MASTER_SPORTS." AS MS","MS.sports_id = G.sports_id","INNER")
			->where('G.contest_id', $contest_id);
		$result = $this->db_fantasy->get()->row_array();
		return $result;
	}

	public function export_contest_teams($contest_id){
      	if(!$contest_id){
        	return false;
      	}
      	$contest = $this->db_fantasy->select("C.contest_id,C.sports_id,C.collection_master_id,CM.collection_name,C.contest_name,C.total_user_joined,C.season_scheduled_date,IFNULL(CM.setting,'[]') as setting,CM.is_lineup_processed", FALSE)
				->from(CONTEST . ' as C')
				->join(COLLECTION_MASTER . " as CM", "C.collection_master_id = CM.collection_master_id", "INNER")
				->where("C.contest_id",$contest_id)
				->get()
				->row_array();
        //echo "======<pre>";print_r($contest);die; 
      	if(empty($contest)){
      		return false;
      	}
      
      	$cm_id = $contest['collection_master_id'];
      	$players_list = $this->get_fixture_rosters($cm_id);
        $player_list_array = array();
        if(!empty($players_list)){
            $player_list_array = array_column($players_list, NULL, 'player_team_id');
        }
      	//echo "<pre>";print_r($player_list_array);die;
      	$this->db_fantasy->select("LM.team_name,LM.user_name,LM.user_id,LM.lineup_master_id,LMC.lineup_master_contest_id,LMC.contest_id,LM.team_data")
            ->from(LINEUP_MASTER_CONTEST . " LMC")
            ->join(LINEUP_MASTER . " LM", "LMC.lineup_master_id = LM.lineup_master_id", "INNER")
            ->where("LMC.fee_refund", "0")
            ->where("LMC.contest_id", $contest_id)
            ->order_by("LMC.game_rank", "ASC", FALSE)
            ->order_by("LMC.total_score", "DESC", FALSE);
      	$user_teams = $this->db_fantasy->get()->result_array();

      	$setting = json_decode($contest['setting'],TRUE);
      	$c_point = CAPTAIN_POINT;
      	$vc_point = VICE_CAPTAIN_POINT;
      	if(!empty($setting) && isset($setting['c'])){
        	$c_point = $setting['c'];
      	}
      	if(!empty($setting) && isset($setting['vc'])){
        	$vc_point = $setting['vc'];
      	}
      	if(in_array($contest['sports_id'],[MOTORSPORT_SPORTS_ID,TENNIS_SPORTS_ID])){
        	$vc_point = 0; 
      	}
      	$final_data = array();
      	$column = 0;
      	foreach($user_teams as $team){
      		$team_data = json_decode($team['team_data'],TRUE);
      		$team_players = $team_data['pl'];
      		$c_id = $team_data['c_id'];
      		$vc_id = $team_data['vc_id'];

      		$temp_array = array();
			$temp_array[0] = $team["user_name"];
			$temp_array[1] = $team["team_name"];
			$temp_array[2] = "";
			$temp_array[3] = "";
			$pl_start_key = 2;
			if($c_point > 0){
				$pl_start_key+= 1;
			}
			if($vc_point > 0){
				$pl_start_key+= 1;
			}
			$column = 0;
			foreach($team_players as $key=>$player_id)
			{
				$pl_obj = $player_list_array[$player_id];
				if($c_point > 0 && $player_id==$c_id)
				{
					$temp_array[2] = ucfirst($pl_obj["full_name"]);
				}
				elseif($vc_point > 0 && $player_id==$vc_id)
				{
					$temp_array[3] = ucfirst($pl_obj["full_name"]);
				}else{
					$temp_array[($key+$pl_start_key)] = ucfirst($pl_obj["full_name"]);
				}
				$column++;
			}
			$temp_array = array_filter($temp_array);
			$final_data[] = $temp_array;
      	}
      	//echo "<pre>";print_r($final_data);die;
      	$contest['c_vc'] = array("c_point"=>$c_point,"vc_point"=>$vc_point);
      	$contest['teams'] = $final_data;
      	return $contest;
  	}


}
