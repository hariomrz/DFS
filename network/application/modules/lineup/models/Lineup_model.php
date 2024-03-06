<?php 

class Lineup_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		
	}

	/**
	 * used to get fixture all players list
	 * @param array $post_data
	 * @return array
	*/
	public function get_all_rosters($post_data)
	{
		$collection_master_id = $post_data['collection_master_id'];
		$league_id = $post_data['league_id'];

		$this->db->select('P.player_id,P.player_uid,T.team_name,IFNULL(T.display_team_abbr,T.team_abbr) as team_abbreviation,S.season_game_uid,P.display_name as full_name,PT.position,ROUND(IFNULL(PT.salary,0),1) as salary,IFNULL(P.nick_name,"") as nick_name,IFNULL(P.display_name,"") as display_name,PT.player_team_id, TL.team_league_id,IF(S.home = T.team_abbr,S.away,S.home) as against_team,ROUND(SUM(IFNULL(GPS.score,0)),1) as fantasy_score,P.sports_id,TL.league_id,IFNULL(T.jersey,T.feed_jersey) as jersey,IFNULL(T.flag,T.feed_flag) as flag,PT.last_match_played,TL.team_uid', FALSE)
			->select('(CASE WHEN JSON_SEARCH(S.playing_list,"one",P.player_uid) IS NOT NULL THEN 1 ELSE 0 END) as is_playing,S.playing_announce',FALSE)
			->select('(CASE WHEN JSON_SEARCH(S.substitute_list,"one",P.player_uid) IS NOT NULL THEN 1 ELSE 0 END) as is_sub',FALSE)
			->from(COLLECTION_SEASON . " AS CS")
			->join(SEASON.' S','S.season_game_uid = CS.season_game_uid','INNER')
			->join(TEAM_LEAGUE.' TL','((S.home_uid = TL.team_uid AND S.league_id=TL.league_id) OR (S.away_uid = TL.team_uid AND S.league_id=TL.league_id))','INNER')
	        ->join(TEAM.' T', 'T.team_id = TL.team_id', 'INNER')
	        ->join(PLAYER_TEAM.' PT', 'PT.team_league_id = TL.team_league_id AND PT.season_game_uid = S.season_game_uid', 'INNER')	
	        ->join(PLAYER.' P', 'P.player_id = PT.player_id', 'INNER');
	   
        if($league_id != '')
        {
        	$this->db->join(GAME_PLAYER_SCORING.' GPS', 'GPS.player_uid = P.player_uid AND GPS.league_id = '.$league_id.' AND GPS.match_format = S.format', 'LEFT');
        }else
        {
        	$this->db->join(GAME_PLAYER_SCORING.' GPS', 'GPS.player_uid = P.player_uid AND GPS.match_format = S.format', 'LEFT');
        }

        $this->db->where("PT.is_deleted",0)
        		->where("CS.collection_master_id",$collection_master_id)
		        ->where("PT.player_status",1)
		        ->where("PT.is_published",1)
		        ->group_by('P.player_uid')
		        ->order_by('P.full_name','ASC');

        if(isset($league_id) && $league_id != ""){
        	$this->db->where("S.league_id",$league_id);
        	$this->db->where("TL.league_id",$league_id);
        }

		$sql = $this->db->get();
		$result	= $sql->result_array();
		return $result;
	}
}
/* End of file  */
/* Location: ./application/models/ */