<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Report_model extends MY_Model{

  	function __construct()
  	{
	  	parent::__construct();
	}

	/**
	 * Used for get season list
	 * @param array $post_data
	 * @return array
	 */
	public function get_opinion_report($post_data)
	{

		// echo "<pre>";
		// print_r($post_data);die;
		$current_date = format_date();

		// echo "<pre>";
		// print_r($current_date);die;
		$sort_field	= 'S.scheduled_date';
		$sort_order	= 'DESC';
		$pagination = get_pagination_data($post_data);	
		$sports_id = $post_data['sports_id'];
		if($this->input->post('csv'))
		{
			$tz_diff = get_tz_diff($this->app_config['timezone']);
			$this->db->select("CONCAT(T1.display_abbr, ' vs ' ,T2.display_abbr ) AS match_name,CONVERT_TZ(S.scheduled_date, '+00:00', '".$tz_diff."') AS scheduled_date, count(SQ.question_id) as opinion_entered,count(distinct(UT.user_id)) as unique_user_joined,SUM(CASE WHEN UT.matchup_id > 0 THEN 1 ELSE 0 END) as matched,SUM(CASE WHEN UT.matchup_id = 0 THEN 1 ELSE 0 END) as unmatched,sum(UT.entry_fee) as total_entry_fee, SQ.site_rake, sum(UT.winning) as distribution,(CASE 
				WHEN S.status =0 && S.scheduled_date > '" .$current_date ."' THEN 'Not Started' 
				WHEN S.status = 0 && S.scheduled_date < '" .$current_date ."' THEN 'Live' 
				WHEN S.status = 2 THEN 'Completed' 
				WHEN S.status = 3 THEN 'Postponed' 
				WHEN S.status = 4 THEN 'Suspended' 
				WHEN S.status = 5 THEN 'Canceled' 			
				ELSE 'other' END) AS status, (UT.entry_fee*SQ.site_rake/100) as profit_loss ",false);  //CONVERT_TZ(S.scheduled_date, '+00:00', '".$tz_diff."') AS scheduled_date
		}
		else
		{
			$this->db->select("SQ.question_id,T1.display_abbr as home_name,T2.display_abbr as away_name,S.title,S.league_id,S.season_id, count(SQ.question_id) as opinion_entered,count(distinct(UT.user_id)) as unique_user_joined,sum(UT.entry_fee) as total_entry_fee, SUM(CASE WHEN UT.matchup_id > 0 THEN 1 ELSE 0 END) as matched, SUM(CASE WHEN UT.matchup_id = 0 THEN 1 ELSE 0 END) as unmatched, sum(UT.winning) as distribution, S.scheduled_date,SQ.site_rake,S.status",false);
		}
			$this->db->from(SEASON." AS S")
			->join(SEASON_QUESTION.' SQ','SQ.season_id = S.season_id','INNER')						
			->join(USER_TEAM.' UT','UT.question_id = SQ.question_id','INNER')						
			->join(LEAGUE.' L','L.league_id = S.league_id','INNER')	
			->join(TEAM.' T1','T1.team_id=S.home_id','left')		
			->join(TEAM.' T2','T2.team_id=S.away_id','left')					
			->where("L.sports_id",$post_data['sports_id'])		
			->group_by("S.season_id")
			// ->group_by("UT.user_id")
			->order_by($sort_field, $sort_order);	

			

		if(isset($post_data['league_id']) && $post_data['league_id'] != "")
		{
			$this->db->where("S.league_id",$post_data['league_id']);
		}

		if(isset($post_data['season_id']) && $post_data['season_id'] != "")
		{
			$this->db->where("S.season_id",$post_data['season_id']);
		}

		if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db->where("DATE_FORMAT(S.scheduled_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(S.scheduled_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
		}


		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('S.title', $post_data['keyword']);
			
		}

		// $tempdb_fantasy = clone $this->db;
		// $query = $this->db->get();
		// $total = $query->num_rows();

		// $sql = $tempdb_fantasy->limit($pagination['limit'],$pagination['offset'])->get();
		// $result	= $sql->result_array();
	
		// $result = isset($result) ? $result : array();
		// return array('result' => $result,'total' => $total);

		$tempdb = clone $this->db;
		 $total = 0;
		
		
		if(!$this->input->post('csv'))
		{
			$query = $this->db->get();
			$total = $query->num_rows();
			$tempdb->limit($pagination['limit'],$pagination['offset']);
		}

		$sql = $tempdb->get();
		$result	= $sql->result_array();
		// echo $tempdb->last_query();exit;	
		return array('result'=>$result,'total'=>$total);
	}

	public function get_season_by_game_id($season_game_uid,$league_id)
	{
		$sql = $this->db->select("S.*,IFNULL(T1.team_abbr,T1.team_abbr) AS home,IFNULL(T2.team_abbr,T2.team_abbr) AS away,L.sports_id,L.league_abbr,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag", FALSE)
				->from(SEASON . " AS S")
				->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
				->join(TEAM.' T1','T1.team_id = S.home_id AND L.sports_id=T1.sports_id','LEFT')
				->join(TEAM.' T2','T2.team_id = S.away_id AND L.sports_id=T2.sports_id','LEFT')
				->where("S.season_game_uid", $season_game_uid)
				->where("S.league_id", $league_id)
				->get();
		$result = $sql->row_array();
		return $result;
	}

	public function update_match_delay_data($post_data){
		$league_id = $post_data['league_id'];
		$season_game_uid = $post_data['season_game_uid'];
		$delay_minute = $post_data['new_delay_minute'] - $post_data['delay_minute'];
		$season_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$delay_minute.' minutes', strtotime($post_data['scheduled_date'])));
        $upd_data = array('scheduled_date'=>$season_scheduled_date,'delay_minute' => $post_data['new_delay_minute'],'delay_message' => $post_data['delay_message'],"delay_by_admin"=>1);
        $this->db->where('season_game_uid', $season_game_uid);
        $this->db->where('league_id', $league_id);
        $this->db->update(SEASON, $upd_data);

    
		return true;
	}


	public function update_match_custom_message($post_data){
		$custom_message = $post_data['custom_message'];
		if(isset($post_data['is_remove']) && $post_data['is_remove'] == 1){
			$custom_message = "";
		}
		$upd_data = array('custom_message' => $custom_message,"notify_by_admin"=>1);
        $this->db->where('season_game_uid', $post_data['season_game_uid']);
        $this->db->where('league_id', $post_data['league_id']);
        $this->db->update(SEASON, $upd_data);
		return true;
	}





}
