<?php 
class Common_model extends MY_Model {
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Used for get player details
	 * @param int $player_team_id
	 * @return array
	 */
	public function get_roster_detail($player_team_id)
	{
		$this->db->select('P.player_id,P.player_uid,P.sports_id,P.full_name,IFNULL(P.display_name,P.full_name) as display_name,PT.position,ROUND(IFNULL(PT.salary,0),1) as salary,PT.player_team_id,PT.team_id,PT.last_match_played as lmp,IFNULL(T.display_team_abbr,T.team_abbr) as team_abbr,IFNULL(IFNULL(P.image,T.jersey),T.feed_jersey) as jersey,PT.rank_number,S.league_id,S.is_tour_game', FALSE)
            ->from(PLAYER_TEAM." AS PT")
            ->join(PLAYER.' P','P.player_id = PT.player_id','INNER')
            ->join(TEAM.' T', 'T.team_id = PT.team_id', 'INNER')
            ->join(SEASON.' S', 'S.season_id = PT.season_id', 'INNER')
            ->where("PT.is_deleted",0)
            ->where("PT.player_team_id", $player_team_id);

		$query = $this->db->get();
		$result	= $query->row_array();
		return $result;
	}

	/**
	 * Used for get player played matches history
	 * @param array $post_data
	 * @return array
	 */
	public function get_roster_match_history($post_data)
	{
        $this->db->select('PT.player_team_id,GPS.score,ROUND(IFNULL(PT.salary,0),1) as salary,S.season_id,S.league_id,S.format,S.scheduled_date,S.is_tour_game,S.home_id,S.away_id,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away', FALSE)
            ->from(GAME_PLAYER_SCORING." AS GPS")
            ->join(PLAYER_TEAM.' PT','PT.player_id = GPS.player_id AND PT.season_id=GPS.season_id','INNER')
            ->join(SEASON.' S', 'S.season_id = GPS.season_id', 'INNER')
        	->join(TEAM.' T1', 'T1.team_id = S.home_id', 'INNER')	
            ->join(TEAM.' T2', 'T2.team_id = S.away_id', 'INNER') 
            ->where("GPS.league_id", $post_data['league_id'])
            ->where("GPS.player_id", $post_data['player_id'])
            ->where("S.status", "2")
            ->group_by('S.season_id')
            ->order_by('S.scheduled_date DESC')
            ->limit($post_data['limit']);

        $query = $this->db->get();
       	$result	= $query->result_array();
        return $result;
    }

    /**
     * Used for get player played tour game history
     * @param array $post_data
     * @return array
     */
    public function get_roster_tour_match_history($post_data)
    {
        $this->db->select("PT.player_team_id,GPS.score,ROUND(IFNULL(PT.salary,0),1) as salary,S.season_id,S.league_id,S.scheduled_date,S.is_tour_game,S.tournament_name,IFNULL(S.track_name,'') as track_name,GPS.score AS match_points", FALSE)
            ->from(GAME_PLAYER_SCORING." AS GPS")
            ->join(PLAYER_TEAM.' PT','PT.player_id = GPS.player_id AND PT.season_id=GPS.season_id','INNER')
            ->join(SEASON.' S', 'S.season_id = GPS.season_id', 'INNER')
            ->where("GPS.league_id", $post_data['league_id'])
            ->where("GPS.player_id", $post_data['player_id'])
            ->where("S.status", "2")
            ->group_by('S.season_id')
            ->order_by('S.scheduled_date DESC')
            ->limit($post_data['limit']);

        if($post_data['sports_id'] == MOTORSPORT_SPORTS_ID){
            if(isset($post_data['position']) && $post_data['position'] == "CR"){
                $this->db->select("MIN(NULLIF(GSM.f_position,0)) as f_position,MIN(NULLIF(GSM.q3_position,0)) as q3_position,MAX(GSM.f_total_laps) as f_total_laps,MAX(GSM.f_laps) as f_laps", FALSE);
                $this->db->join(GAME_STATISTICS_MOTORSPORT.' GSM', 'GSM.season_id = GPS.season_id', 'INNER');
                $this->db->where("GSM.team_id",$post_data['team_id']);
            }else{
                $this->db->select("GSM.f_position,GSM.q3_position,GSM.f_total_laps,GSM.f_laps", FALSE);
                $this->db->join(GAME_STATISTICS_MOTORSPORT.' GSM', 'GSM.season_id = GPS.season_id AND GSM.player_id=GPS.player_id', 'INNER');
            }
        }else{
            $this->db->select("GST.service_aces,GST.service_df,GST.total_score,GST.winner,SM.score,IFNULL(P1.display_name,P1.full_name) as home,IFNULL(P2.display_name,P2.full_name) as away,SM.home_id,SM.away_id", FALSE);
            $this->db->join(GAME_STATISTICS_TENNIS.' GST', 'GST.season_id = GPS.season_id AND GST.player_id=GPS.player_id', 'INNER');
            $this->db->join(SEASON_MATCH.' SM', 'SM.season_match_id = GST.season_match_id', 'INNER');
            $this->db->join(PLAYER.' P1', 'P1.player_id = SM.home_id', 'INNER');
            $this->db->join(PLAYER.' P2', 'P2.player_id = SM.away_id', 'INNER');
        }
        
            
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    /**
	 * Used for get player fantasy points breakdown
	 * @param array $post_data
	 * @return array
	 */
	public function get_player_breakdown($player_team_id)
	{
        $this->db->select('P.player_id,S.season_id,S.league_id,S.format,S.scheduled_date,P.full_name,IFNULL(P.display_name,P.full_name) as display_name,PT.position,ROUND(IFNULL(PT.salary,0),1) as salary,PT.player_team_id,IFNULL(L.league_display_name,L.league_name) as league_name,L.sports_id,PT.rank_number,PT.team_id,P.player_uid,IFNULL(GPS.score,0) as score,GPS.break_down,S.home_id,S.away_id,S.is_tour_game,L.no_of_sets', FALSE)
            ->from(PLAYER_TEAM." AS PT")
            ->join(PLAYER.' P','P.player_id = PT.player_id','INNER')
            ->join(SEASON.' S', 'S.season_id = PT.season_id', 'INNER')
            ->join(GAME_PLAYER_SCORING.' GPS', 'GPS.season_id = S.season_id AND P.player_id=GPS.player_id', 'LEFT')
            ->join(LEAGUE.' L', 'L.league_id = S.league_id', 'INNER')
            ->where("PT.is_deleted",0)                        
            ->where("PT.player_team_id", $player_team_id);

		$query = $this->db->get();
        //echo $this->db->last_query();die;
		$result	= $query->row_array();
        return $result;
    }

    /**
     * used to get scoring rules list
     * @param array $post_data
     * @return array
     */
    public function get_fantasy_points_category($post_data) {         
        $this->db->select('MSR.master_scoring_id,MSR.score_position, score_points,MSC.scoring_category_name as category_name,ROUND((CASE WHEN MSC.sports_id != "' . PBL_SPORTS_ID . '" AND meta_key="CAPTAIN" THEN ' . CAPTAIN_POINT . ' WHEN meta_key="VICE_CAPTAIN" THEN ' . VICE_CAPTAIN_POINT . ' ELSE score_points END),2) as score_points,MSR.meta_key', FALSE)
                ->from(MASTER_SCORING_RULES . ' AS MSR')
                ->join(MASTER_SCORING_CATEGORY . ' AS MSC', 'MSC.master_scoring_category_id = MSR.master_scoring_category_id');

        if (!empty($post_data['format'])) {
            $this->db->where('MSR.format', $post_data['format']);
        }
        if (!empty($post_data['sports_id'])) {
            $this->db->where('MSC.sports_id', $post_data['sports_id']);
        }
        if (!empty($post_data['sports_id']) && $post_data['sports_id'] == TENNIS_SPORTS_ID) {
            $this->db->where('MSC.scoring_category_name', 'Best_of_'.$post_data['no_of_sets'].'_sets');
        }
        $this->db->order_by("MSR.master_scoring_id","ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get scoring rules list
     * @param array $post_data
     * @return array
     */
    public function get_player_motor_sports_stats($post_data) {
        $this->db->select('GSM.q3_position,GSM.f_time,GSM.f_laps,GSM.f_fastest_lap_time,GSM.f_grid,GSM.f_pitstop_count,IFNULL(T.display_team_name,T.team_name) as team_name,IFNULL(T.display_team_abbr,T.team_abbr) as team_abbr', FALSE);
        $this->db->from(GAME_STATISTICS_MOTORSPORT.' AS GSM');
        $this->db->join(TEAM.' AS T', 'T.team_id = GSM.team_id');
        $this->db->where('GSM.season_id', $post_data['season_id']);
        $this->db->where('GSM.player_id', $post_data['player_id']);
        $result = $this->db->get()->row_array();
        return $result;
    }
}

/* End of file  */
/* Location: ./application/models/ */