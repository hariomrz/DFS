<?php 
class Lineup_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
     * used to get user team list with joined count
     * @param int $cm_id
     * @return array
    */
    public function get_user_lineup_list($cm_id) {
        $bench_player = $this->get_app_config_value('bench_player');

        $this->db->select("LM.lineup_master_id,LM.user_name,LM.team_name,LM.team_data,count(DISTINCT LMC.lineup_master_contest_id) as total_joined,LM.is_pl_team,LM.is_2nd_inning,LM.booster_id,0 as bench_applied", FALSE);
        $this->db->from(LINEUP_MASTER.' LM');
        $this->db->join(LINEUP_MASTER_CONTEST.' as LMC', 'LMC.lineup_master_id = LM.lineup_master_id', "LEFT");
        $this->db->where('LM.collection_master_id', $cm_id);
        $this->db->where('LM.user_id', $this->user_id);
        $this->db->group_by('LM.lineup_master_id');
        $this->db->order_by('LM.lineup_master_id', "ASC");

        //for bench
        if($bench_player == "1"){
            $this->db->select("IF(COUNT(BP.bench_player_id) > 0,1,0) as bench_applied",FALSE);
            $this->db->join(BENCH_PLAYER.' as BP', 'BP.lineup_master_id = LM.lineup_master_id', "LEFT");
        }
        $result = $this->db->get()->result_array();
        return $result;
    }

	/**
	 * used to get fixture all players list
	 * @param int $cm_id
	 * @return array
	*/
	public function get_fixture_rosters($cm_id)
	{
		$this->db->select("P.player_id,P.player_uid,P.full_name,P.display_name,PT.player_team_id,PT.team_id,PT.position,ROUND(IFNULL(PT.salary,0),1) as salary,PT.last_match_played as lmp,T.team_uid,IFNULL(T.display_team_name,T.team_name) AS team_name,IFNULL(T.display_team_abbr,T.team_abbr) AS team_abbr,IFNULL(IFNULL(P.image,T.jersey),T.feed_jersey) as jersey,ROUND(SUM(IFNULL(GPS.score,0)),1) as fantasy_score,S.playing_announce,(CASE WHEN JSON_SEARCH(S.playing_list,'one',P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_playing,(CASE WHEN JSON_SEARCH(S.substitute_list,'one',P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_sub", FALSE)
			->from(COLLECTION_SEASON . " AS CS")
	        ->join(SEASON.' AS S', 'S.season_id = CS.season_id', 'INNER')
	        ->join(PLAYER_TEAM.' AS PT', 'PT.season_id = S.season_id', 'INNER')
	        ->join(PLAYER.' AS P', 'P.player_id = PT.player_id', 'INNER')
			->join(TEAM.' AS T', 'T.team_id = PT.team_id', 'INNER')
			->join(GAME_PLAYER_SCORING.' GPS', 'GPS.player_id = P.player_id AND GPS.league_id=S.league_id', 'LEFT')
			->where("CS.collection_master_id",$cm_id)
			->where("PT.is_deleted",0)
			->where("PT.player_status",1)
	        ->where("PT.is_published",1)
	        ->group_by('P.player_id')
	        ->order_by('P.display_name','ASC');

		$sql = $this->db->get();
		$result	= $sql->result_array();
		return $result;
	}

	/**
	 * used to get fixture(collection) all matches teams list
	 * @param int $cm_id
	 * @return array
	*/
	public function get_fixture_teams($cm_id)
	{
		$this->db->select("T.team_id,IFNULL(T.display_team_name,T.team_name) AS team_name,IFNULL(T.display_team_abbr,T.team_abbr) AS team_abbr,IFNULL(T.jersey,T.feed_jersey) AS jersey,IFNULL(T.flag,T.feed_flag) as flag",FALSE)
			->from(COLLECTION_SEASON.' AS CS')
			->join(SEASON." AS S","S.season_id = CS.season_id","INNER")
			->join(TEAM." AS T","(T.team_id = S.home_id OR T.team_id = S.away_id)","INNER")
			->where("CS.collection_master_id",$cm_id)
			->group_by("T.team_id")
			->order_by("team_name", "ASC");

		$sql = $this->db->get();
		$result = $sql->result_array();
		return $result;
	}

	/**
	 * used to get tour game sport teams list
	 * @param int $sports_id
	 * @return array
	*/
	public function get_tour_sport_teams($sports_id,$season_ids=array())
	{
		$this->db->select("T.team_id,IFNULL(T.display_team_name,T.team_name) AS team_name,IFNULL(T.display_team_abbr,T.team_abbr) AS team_abbr,IFNULL(T.jersey,T.feed_jersey) AS jersey,IFNULL(T.flag,T.feed_flag) as flag",FALSE)
			->from(TEAM.' AS T')
			->where("T.sports_id",$sports_id)
			->group_by("T.team_id")
			->order_by("team_name", "DESC");
		if(!empty($season_ids)){
			$this->db->join(PLAYER_TEAM." AS PT","PT.team_id=T.team_id");
			$this->db->where_in("PT.season_id",$season_ids);
		}
		$sql = $this->db->get();
		$result = $sql->result_array();
		return $result;
	}

	/**
	 * Used to get team details
	 * @param int $lm_id
	 * @return array
	*/
	public function get_team_detail($lm_id,$lmc_id="")
	{
		$this->db->select("LM.lineup_master_id,LM.user_id,LM.user_name,LM.team_name,LM.team_data,LM.is_pl_team,CM.collection_master_id,CM.season_scheduled_date,CM.is_lineup_processed,L.sports_id,LM.booster_id,CM.status as cm_status",FALSE)
			->from(LINEUP_MASTER.' AS LM')
			->join(COLLECTION_MASTER." AS CM","CM.collection_master_id = LM.collection_master_id","INNER")
			->join(LEAGUE." AS L","L.league_id = CM.league_id","INNER")
			->where("LM.lineup_master_id",$lm_id)
			->group_by("LM.lineup_master_id");
		if(isset($lmc_id) && $lmc_id != ""){
			$this->db->select("LMC.game_rank,LMC.total_score,LMC.is_winner,LMC.amount,LMC.bonus,LMC.coin,IFNULL(LMC.merchandise,'') as merchandise,LMC.booster_points");
			$this->db->join(LINEUP_MASTER_CONTEST." AS LMC","LMC.lineup_master_id = LM.lineup_master_id","INNER");
			$this->db->where("LMC.lineup_master_contest_id",$lmc_id);
		}
		$sql = $this->db->get();
		$result = $sql->row_array();
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
        if($is_lineup_processed == "1" && $this->db->table_exists($lineup_table)) {
            $sql = $this->db->select("L.player_team_id,ROUND(IFNULL(L.score,0),1) AS score,L.captain", FALSE)
                    ->from($lineup_table." L")
                    ->where('L.lineup_master_id', $lm_id)
                    ->get();
            $result = $sql->result_array();
            if(!empty($result)){
            	$c_vc = array_column($result,"player_team_id","captain");
            	$team_info['team_data']['c_id'] = isset($c_vc['1']) ? $c_vc['1'] : 0;
            	$team_info['team_data']['vc_id'] = isset($c_vc['2']) ? $c_vc['2'] : 0;
            	$team_info['team_data']['pl'] = array_column($result,"score","player_team_id");
            }
        }else if(in_array($is_lineup_processed,array("2","3"))){
        	$team_data = $this->get_single_row("*",COMPLETED_TEAM,array("lineup_master_id"=>$lm_id));
        	if(!empty($team_data)){
        		$team_info['team_data'] = json_decode($team_data['team_data'],TRUE);
        	}
        }
        return $team_info;
    }
}
/* End of file  */
/* Location: ./application/models/ */
