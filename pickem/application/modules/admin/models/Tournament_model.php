<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tournament_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Used for get league list by sports id
	 * @param int $sports_id
	 * @return array
	 */
	public function get_sport_league_list($post_data)
	{
		$current_date = format_date();
		$this->db->select('L.league_id,L.league_uid,L.league_abbr,IFNULL(display_name,league_name) AS league_name',FALSE)
		 	->from(LEAGUE." AS L")
		 	->join(SEASON.' S','S.league_id = L.league_id','INNER')
			->where('L.status','1')
			->where('L.sports_id',$post_data['sports_id'])
			->where('S.scheduled_date > ',$current_date)
			->where('S.status','0')
			->group_by('L.league_id');

        $sql = $this->db->get();
		$result = $sql->result_array();
		return $result;
	}

	/**
	 * Used for get league fixture list
	 * @param array $post_data
	 * @return array
	 */
	public function get_fixture_list($league_id)
	{
		$current_date = format_date();
		$this->db->select("S.season_id,S.scheduled_date,IFNULL(T1.display_abbr,T1.team_abbr) AS home,IFNULL(T2.display_abbr,T2.team_abbr) AS away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag",false)
			->from(SEASON." AS S")
			->join(TEAM.' T1','T1.team_id = S.home_id','INNER')
			->join(TEAM.' T2','T2.team_id = S.away_id','INNER')
			->where("S.league_id",$league_id)
			->where('S.scheduled_date > ',$current_date)
			->where('S.status','0')
			->group_by("S.season_id")
			->order_by("S.scheduled_date","ASC");

		$result	= $this->db->get()->result_array();
		return $result;
	}

	/**
    * Function used for save tournament data
    * @param array $post_data
    * @return array
    */
	public function save_tournament($post_data)
	{
		try{
			//Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();

            $current_date = format_date();
            $season_ids = $post_data['season_ids'];
            $data_arr = array();
            $data_arr["sports_id"] = $post_data['sports_id'];
            $data_arr["league_id"] = $post_data['league_id'];
            $data_arr["name"] = $post_data['name'];
            $data_arr["image"] = isset($post_data['image']) ? $post_data['image'] : "";
            $data_arr["start_date"] = $post_data['start_date'];
            $data_arr["end_date"] = $post_data['end_date'];
            $data_arr["currency_type"] = $post_data['currency_type'];
            $data_arr["entry_fee"] = !empty($post_data['entry_fee'])?$post_data['entry_fee']:0;
            $data_arr["max_bonus"] = !empty($post_data['max_bonus'])?floor($post_data['max_bonus']):0;
            $data_arr["prize_detail"] = json_encode($post_data['prize_detail']);
            if(isset($post_data['perfect_score']) && !empty($post_data['perfect_score'])){
            	$data_arr["perfect_score"] = json_encode($post_data['perfect_score']);
            }
            if(isset($post_data['banner_images']) && !empty($post_data['banner_images'])){
            	$data_arr["banner_images"] = json_encode($post_data['banner_images']);
            }
            if(isset($post_data['tie_breaker_question']) && !empty($post_data['tie_breaker_question'])){
            	$data_arr["tie_breaker_question"] = json_encode($post_data['tie_breaker_question']);
            }
            $data_arr["match_count"] = count($season_ids);
			$data_arr["is_score_predict"] = isset($post_data['is_score_predict'])?($post_data['is_score_predict']):0;
			$data_arr["auto_match_publish"] = isset($post_data['auto_match_publish']) ? ($post_data['auto_match_publish']) : 0;
			$data_arr["is_tie_breaker"] = '1';
            $data_arr["added_date"] = $current_date;
            $data_arr["modified_date"] = $current_date;
			$this->db->insert(TOURNAMENT,$data_arr);
			$tournament_id = $this->db->insert_id();
			if($tournament_id){
				foreach($season_ids as $season_id=>$scheduled_date){
					$season_data = array();
					$season_data['tournament_id'] = $tournament_id;
					$season_data['season_id'] = $season_id;
					$season_data['scheduled_date'] = $scheduled_date;
					$season_data['added_date'] = $current_date;
					$this->db->insert(TOURNAMENT_SEASON,$season_data);
				}

        		//Trasaction End
	            $this->db->trans_complete();
	            if ($this->db->trans_status() === FALSE )
	            {
	                $this->db->trans_rollback();
					return false;
	            }
	            else
	            {
	                $this->db->trans_commit();
	                return $tournament_id;
	            }
			}else{
				return false;
			}
		}
		catch(Exception $e){
			$this->db->trans_rollback();
	        return false;
      	}
		return false;
	}

	/**
	 * Used for get tournament list
	 * @param array $post_data
	 * @return array
	 */
	public function get_tournament_list($post_data)
	{
		$current_date = format_date();
		$sort_field	= 'start_date';
		$sort_order	= 'ASC';
		$pagination = get_pagination_data($post_data);
		$status = isset($post_data['status']) ? $post_data['status'] : "upcoming";
		$where = array("T.start_date > " => $current_date);
		if($status == "live"){
			$sort_field	= 'start_date';
			$sort_order	= 'DESC';
			$where = array("T.start_date <= " => $current_date,"T.status"=>"0");
		}else if($status == "completed"){
			$sort_field	= 'end_date';
			$sort_order	= 'DESC';
			$where = array("T.start_date <= " => $current_date,"T.status >= "=>"2");
		}

		$this->db->select("T.tournament_id,T.is_pin,T.league_id,T.name,IFNULL(T.image,'') as image,T.start_date,T.end_date,T.currency_type,T.entry_fee,T.max_bonus,T.match_count,T.prize_detail,T.status,IFNULL(L.display_name,L.league_name) AS league_name,count(UT.user_tournament_id) as user_count,T.is_score_predict,T.auto_match_publish",false)
			->from(TOURNAMENT." AS T")
			->join(LEAGUE.' L','L.league_id = T.league_id','INNER')
			->join(USER_TOURNAMENT . ' as UT', 'UT.tournament_id = T.tournament_id', "LEFT")
			->where("L.sports_id",$post_data['sports_id'])
			->where($where)
			->group_by("T.tournament_id")
			->order_by($sort_field, $sort_order);

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->where("(T.name LIKE '%".$post_data['keyword']."%')");
		}

		if(isset($post_data['start_date']) && $post_data['start_date'] != "")
		{
			$this->db->where("T.start_date >= ",$post_data['start_date']);
		}

		if(isset($post_data['end_date']) && $post_data['end_date'] != "")
		{
			$this->db->where("T.start_date <= ",$post_data['end_date']);
		}

		if(isset($post_data['league_id']) && $post_data['league_id'] != "")
		{
			$this->db->where("T.league_id",$post_data['league_id']);
		}

		$tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();

		$sql = $tempdb->limit($pagination['limit'],$pagination['offset'])->get();
		$result	= $sql->result_array();
		// echo $this->db->last_query(); die;
		$result = isset($result) ? $result : array();
		return array('result' => $result,'total' => $total);
	}

	/**
	 * Used for get league published fixture
	 * @param array $post_data
	 * @return array
	 */
	public function get_tournament_fixtures($tournament_id)
	{
		$current_date = format_date();
		$this->db->select("S.season_id,S.league_id,S.scheduled_date,IFNULL(T1.display_abbr,T1.team_abbr) AS home,IFNULL(T2.display_abbr,T2.team_abbr) AS away,IF(IFNULL(TS.tournament_season_id,0) > 0,1,0) as is_added",false)
			->from(TOURNAMENT." AS T")
			->join(SEASON." AS S","S.league_id=T.league_id","INNER")
			->join(TEAM.' T1','T1.team_id = S.home_id','INNER')
			->join(TEAM.' T2','T2.team_id = S.away_id','INNER')
			->join(TOURNAMENT_SEASON.' TS','TS.tournament_id = T.tournament_id AND TS.season_id=S.season_id','LEFT')
			->where("T.tournament_id",$tournament_id)
			->group_by("S.season_id")
			->having("(is_added=1 OR S.scheduled_date > '".$current_date."')")
			->order_by("S.scheduled_date","ASC");

		$result	= $this->db->get()->result_array();
		return $result;
	}

	/**
    * Function used for save tournament fixture data
    * @param array $post_data
    * @return array
    */
	public function save_tournament_fixture($post_data)
	{
		try{
			$tournament_id = $post_data['tournament_id'];
			$match_list = $this->get_all_table_data("season_id,scheduled_date",TOURNAMENT_SEASON,array("tournament_id"=>$tournament_id),array("scheduled_date"=>"ASC"));
			$match_list = array_column($match_list,"scheduled_date","season_id");
			$match_list = array_diff_key($post_data['season_ids'],$match_list);
			
			//Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();

            $current_date = format_date();
            foreach($match_list as $season_id=>$scheduled_date){
				$season_data = array();
				$season_data['tournament_id'] = $tournament_id;
				$season_data['season_id'] = $season_id;
				$season_data['scheduled_date'] = $scheduled_date;
				$season_data['added_date'] = $current_date;
				$this->db->insert(TOURNAMENT_SEASON,$season_data);
			}

			$record_info = $this->get_single_row("MIN(scheduled_date) as start_date,MAX(scheduled_date) as end_date",TOURNAMENT_SEASON,array("tournament_id"=>$tournament_id));

			if(!empty($record_info)){
				$season_count = count($post_data['season_ids']);
				$this->db->set('start_date',$record_info['start_date']);
				$this->db->set('end_date',$record_info['end_date']);
				$this->db->set('match_count','match_count + '.$season_count,FALSE);
				$this->db->where('tournament_id',$tournament_id);
				$this->db->update(TOURNAMENT);
				
			}

			//Trasaction End
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE )
            {
                $this->db->trans_rollback();
				return false;
            }
            else
            {
                $this->db->trans_commit();
                return $tournament_id;
            }
		}
		catch(Exception $e){
			$this->db->trans_rollback();
	        return false;
      	}
		return false;
	}

	/**
    * Function used for get tournament details
    * @param int $tournament_id
    * @return array
    */
	public function get_tournament_detail($tournament_id)
	{
		$this->db->select("T.tournament_id,T.league_id,T.name,IFNULL(T.image,'') as image,T.start_date,T.is_pin,T.end_date,T.currency_type,T.entry_fee,T.max_bonus,T.match_count,T.is_tie_breaker,T.prize_detail,T.perfect_score,T.banner_images,T.tie_breaker_question,T.status,T.added_date,IFNULL(L.display_name,L.league_name) AS league_name,GROUP_CONCAT(DISTINCT TS.season_id) as season_ids,T.tie_breaker_answer,T.cancel_reason,count(UT.user_tournament_id) as user_count,is_score_predict,T.auto_match_publish",false)
			->from(TOURNAMENT." AS T")
			->join(LEAGUE.' L','L.league_id = T.league_id','INNER')
			->join(TOURNAMENT_SEASON." AS TS", 'TS.tournament_id = T.tournament_id','INNER')
			->join(USER_TOURNAMENT." AS UT", 'UT.tournament_id = T.tournament_id','LEFT')
			->where('T.tournament_id',$tournament_id)
			->group_by('T.tournament_id');
		$sql = $this->db->get();
		$result	= $sql->row_array();
		return $result;
	}

	/**
     * Function used for get joined participant list
     * @param tournament_id
     * @return array
    */    
	public function get_join_partcipants_list($post_data)
	{	
		$pagination = get_pagination_data($post_data);
		$sql = $this->db->select("T.user_tournament_id, T.tournament_id,T.user_id,T.user_name,T.total_score,T.game_rank,T.is_winner,T.amount,T.bonus,T.coin,T.merchandise,T.fee_refund,T.added_date,TO.name,TO.prize_detail,TO.image,TO.start_date,TO.end_date,TO.max_bonus,count(DISTINCT user_team_id) as joined_fixture_count", FALSE)
					    ->from(USER_TOURNAMENT. " T")
					    ->join(USER_TEAM. " UT","T.user_tournament_id=UT.user_tournament_id","LEFT")
					    ->join(TOURNAMENT. " TO","TO.tournament_id=T.tournament_id")
						->where("T.tournament_id",$post_data['tournament_id'])
						->group_by("T.user_tournament_id")
						->order_by('T.game_rank','ASC');
		$tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();

		$sql = $tempdb->limit($pagination['limit'],$pagination['offset'])->get();
		$result	= $sql->result_array(); 				
        return array('result' => $result,'total' => $total);
	}

	/**
     * Function used for get participant details
     * @param tournament_id
     * @return array
    */    
	public function get_partcipants_detail($user_tournament_id)
	{
		$sql = $this->db->select("UT.season_id, UT.score,UT.team_id,UT.is_correct,T.team_abbr as home,T1.team_abbr as away,S.home_id,S.away_id,UT.away_predict,UT.home_predict,S.score_data,S.scheduled_date", FALSE)
					    ->from(USER_TEAM." UT")
					    ->join(SEASON. " S","S.season_id=UT.season_id")
					    ->join(TEAM. " T","T.team_id=S.home_id")
					    ->join(TEAM. " T1","T1.team_id=S.away_id")
						->where("UT.user_tournament_id",$user_tournament_id)
						->order_by('UT.user_team_id','ASC'); 				
		$result = $sql->get()->result_array();  
        return $result;
	}

   /**
    * Function used to mark tournament complete
    * @param tournament_id
    * @return array
    */
    function get_tournament_fixture_status($tournament_id)
    {
        return  $this->db->select("SUM(CASE WHEN S.status = 2 THEN 2 WHEN S.status = 4 THEN 2 ELSE 0 END) as status,T.status as tournament_status,count(S.season_id) as cnt,T.end_date as match_closure_date,T.tie_breaker_question,T.tie_breaker_answer,T.perfect_score,T.name")
                    ->from(TOURNAMENT. " T")
                    ->join(TOURNAMENT_SEASON. " TS","TS.tournament_id=T.tournament_id")
                    ->join(SEASON. " S","TS.season_id=S.season_id")
                    ->where("T.tournament_id",$tournament_id)
                    ->group_by("TS.tournament_id")
                    ->having("status", "cnt * 2",FALSE)
                    ->get()->row_array();
        //echo $this->db->last_query();die;
    }

   /**
    * Function to get user to credit perfect score
    * @param tournament_id
    * @return array
    */
    function get_user_for_perfect_score($tournament_id)
    {
    	$sql = "SELECT 	`UTO`.`user_tournament_id`,`UTO`.`user_id`, SUM(CASE WHEN UT.is_correct=1 THEN UT.is_correct ELSE 0  END) as `correct_cnt`,UTO.tournament_id,GT.season_cnt
			    FROM ".$this->db->dbprefix(USER_TOURNAMENT)." `UTO`
			    JOIN  ".$this->db->dbprefix(USER_TEAM)." `UT` ON `UT`.`user_tournament_id`=`UTO`.`user_tournament_id` INNER JOIN
				(SELECT count(season_id) as season_cnt,tournament_id from  ".$this->db->dbprefix(TOURNAMENT_SEASON)." where tournament_id=".$tournament_id."
				) as GT
				ON GT.tournament_id = UTO.tournament_id
				GROUP by UTO.user_id
				HAVING correct_cnt = GT.season_cnt";
		$result = $this->db->query($sql);
		return $result->result_array();
    }

	/**
    * Function used for get fixture details
    * @param int $tournament_id
    * @return array
    */
	public function get_fixture_detail($tournament_id,$season_id)
	{
		$this->db->select("T.tournament_id,T.match_count,T.status,count(UT.user_tournament_id) as user_count,GROUP_CONCAT(DISTINCT UTE.user_team_id) as user_team_id",false)
			->from(TOURNAMENT." AS T")
			->join(TOURNAMENT_SEASON." AS TS", 'TS.tournament_id = T.tournament_id','INNER')
			->join(USER_TOURNAMENT." AS UT", 'UT.tournament_id = T.tournament_id','LEFT')
			->join(USER_TEAM." AS UTE", 'UTE.user_tournament_id = UT.user_tournament_id AND UTE.season_id = '.$season_id,'LEFT')
			->where('T.tournament_id',$tournament_id)
			->where('TS.season_id',$season_id)
			->where('T.status',0)
			->group_by('T.tournament_id');
		$sql = $this->db->get();
		$result	= $sql->row_array();
		return $result;
	}

	/**
    * Function used delete user anwsers
    * @param int $season_id,$user_team_ids
    * @return array
    */
	public function delete_user_pickem_answer($season_id,$user_team_ids)
	{
		$this->db->where('season_id',$season_id);
		$this->db->where_in('user_team_id',$user_team_ids);
        $this->db->delete(USER_TEAM);
        $result = $this->db->affected_rows();
        return $result;
	}
}