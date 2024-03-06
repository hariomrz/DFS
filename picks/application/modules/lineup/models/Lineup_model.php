<?php 

class Lineup_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		
	}


   /**
    * used to get collection teams list
	* @param int $collection_master_id
	* @return array
	*/
	public function get_season_teams($season_id,$league_id)
	{
		$this->db->select("DISTINCT T.team_id,TL.team_league_id,T.team_abbr,T.team_abbr,T.team_name,T.jersey,T.flag,TL.team_id",FALSE)
			->from(SEASON." AS S")
			->join(TEAM_LEAGUE." AS TL","((S.home_id = TL.team_id AND S.league_id=TL.league_id) OR (S.away_id = TL.team_id AND S.league_id=TL.league_id))","INNER")
			->join(TEAM." AS T","T.team_id = TL.team_id","INNER")
			->where("S.season_id",$season_id)
			->where("S.league_id",$league_id)
			->where("TL.league_id",$league_id)
			->group_by("TL.team_id")
			->order_by("team_name", "DESC");

		$sql = $this->db->get();
		$result = $sql->result_array();
		return $result;
	}

	/**
	 * used to get fixture all pick list
	 * @param array $post_data
	 * @return array
	*/

	public function get_all_rosters($post_data)
	{
		$season_id = $post_data['season_id'];
		
		$result = $this->db->select("P.pick_id,P.name as questions,P.option_1,P.option_2,P.option_3,P.option_4,P.details,P.option_images,P.option_stats,P.explaination,IFNULL(P.stats_text,'') as stats_text")
			->from(PICKS. " P")
			->join(SEASON." S","P.season_id=S.season_id")
			->where('P.season_id',$season_id)
			->where('S.status',0)
			->order_by('P.pick_id','ASC')
			->get()->result_array();
		//echo $this->db->last_query();die;
		return $result;
	}

	/**
	 * used for get team details
	 * @param int $lineup_master_id
	 * @param int $season_id
	 * @return array
	*/
	public function get_team_by_user_team_id($user_team_id,$season_id)
	{	
		$sql = $this->db->select('user_team_id,season_id,user_id,team_name')
				->from(USER_TEAM)
				->where('user_team_id',$user_team_id)
				->where('season_id',$season_id)
				->where('user_id',$this->user_id)
				->get();
		$result = $sql->row_array();
		return $result;
	}

	public function save_user_team($insert_data=array(),$user_team_id='')
	{	
		if(!empty($user_team_id))
		{
			$this->db->where('user_team_id',$user_team_id);
			$this->db->update(USER_TEAM,$insert_data);

		}else{
			
			$this->db->insert(USER_TEAM,$insert_data);
			return $this->db->insert_id();
		}

	}


	public function get_lineup_data($post_data)
	{
		$lineup = $this->db->select("
					UTP.score,UTP.is_captain,UTP.is_vc,UTP.pick_id,UTP.answer as user_answer,UT.team_name,UTP.score,P.name as questions,P.option_1,P.option_2,P.option_3,P.option_4,P.details,P.answer as correct_answer,UT.tie_breaker_answer,P.explaination,P.explaination_image,P.option_images,P.option_stats")
						->from(USER_TEAM_PICKS. " UTP")
						->join(USER_TEAM. " UT","UT.user_team_id=UTP.user_team_id")
						->join(PICKS. " P","P.season_id=UT.season_id and UTP.pick_id=P.pick_id")
						->where('UT.user_team_id',$post_data['user_team_id'])
						->where('UT.season_id',$post_data['season_id'])
						->get()->result_array();
		$user_contest_data= [];			
		if(!empty($lineup))
		{
			$user_contest_data = $this->db->select('UC.total_score,UC.game_rank')
										->from(USER_CONTEST. " UC")
										->where('UC.user_team_id',$post_data['user_team_id'])
										->get()->row_array();
			
		}
		return ['lineup'=>$lineup,'user_contest_data'=>$user_contest_data];
	}

	public function delete_picks($picks,$user_team_id)
	{
		$this->db->where('user_team_id',$user_team_id);
		$this->db->where_not_in('pick_id',$picks);
		$this->db->delete(USER_TEAM_PICKS);
		
	}

}