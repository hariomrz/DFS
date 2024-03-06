<?php

class Stats_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * used to get match collection details
     * @param int $collection_master_id
     * @return array
    */
    public function get_collection_details($cm_id,$sports_id){

        $this->db->select("CM.collection_master_id,CM.league_id,CM.collection_name,CM.season_scheduled_date,CM.is_tour_game,S.season_id,S.season_game_uid,S.home_id,S.away_id,S.format,S.playing_announce,S.status,S.status_overview,S.score_data,'[]' as fall_of_wickets,IFNULL(S.team_batting_order,'[]') as team_batting_order",FALSE);
        $this->db->from(COLLECTION_MASTER." as CM");
        $this->db->join(COLLECTION_SEASON.' as CS', 'CM.collection_master_id = CS.collection_master_id',"INNER");
        $this->db->join(SEASON.' as S', 'S.season_id = CS.season_id',"INNER");
        $this->db->where('CM.collection_master_id', $cm_id);
        $this->db->group_by("S.season_id");
        if(in_array($sports_id,$this->tour_game_sports)){
            $this->db->select("IFNULL(S.tournament_name,'') as tournament_name,S.match_event,IFNULL(S.end_scheduled_date,S.season_scheduled_date) as end_scheduled_date",FALSE);
        }else{
            $this->db->select("IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,S.result_info,IFNULL(S.fall_of_wickets,'[]') as fall_of_wickets,T1.team_uid as home_uid,T2.team_uid as away_uid,IFNULL(CF.fall_of_wickets,'[]') as fall_of_wickets",FALSE);
            $this->db->join(TEAM.' as T1', 'T1.team_id = S.home_id',"INNER");
            $this->db->join(TEAM.' as T2', 'T2.team_id = S.away_id',"INNER");
            $this->db->join(CRICKET_FOW.' CF', 'CF.season_id = S.season_id','LEFT');
        }
        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
     * used to get season details
     * @param int $season_id
     * @return array
    */
    public function get_season_details($season_id,$sports_id){

        $this->db->select("S.season_id,S.season_game_uid,S.home_id,S.away_id,S.format,S.season_scheduled_date,S.is_tour_game,S.playing_announce,S.status,S.status_overview,S.score_data,'[]' as fall_of_wickets",FALSE);
        $this->db->from(SEASON." as S");
        $this->db->where('S.season_id', $season_id);
        $this->db->group_by("S.season_id");
        if($sports_id == MOTORSPORT_SPORTS_ID){
            $this->db->select("IFNULL(S.tournament_name,'') as tournament_name,S.match_event,IFNULL(S.end_scheduled_date,S.season_scheduled_date) as end_scheduled_date",FALSE);
        }else{
            $this->db->select("IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,S.result_info,IFNULL(S.fall_of_wickets,'[]') as fall_of_wickets,T1.team_uid as home_uid,T2.team_uid as away_uid",FALSE);
            $this->db->join(TEAM.' as T1', 'T1.team_id = S.home_id',"INNER");
            $this->db->join(TEAM.' as T2', 'T2.team_id = S.away_id',"INNER");
            $this->db->join(CRICKET_FOW.' CF', 'CF.season_id = S.season_id','LEFT');
        }
        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
     * used to get fixture all players list
     * @param array $post_data
     * @return array
    */
    public function get_match_rosters($season_id)
    {
        $this->db->select('P.player_id,P.player_uid,P.full_name,P.display_name,PT.player_team_id,PT.player_team_id,PT.position,PT.salary,T.team_name,IFNULL(T.display_team_abbr,T.team_abbr) as team_abbr,IFNULL(T.jersey,T.feed_jersey) as jersey,T.team_uid,IFNULL(GPS.score,0) as fantasy_score,0 as user_selected,0 as selected_by', FALSE)
            ->from(SEASON." AS S")
            ->join(PLAYER_TEAM.' PT', 'PT.season_id = S.season_id', 'INNER')
            ->join(TEAM.' T', 'T.team_id = PT.team_id', 'INNER')
            ->join(PLAYER.' P', 'P.player_id = PT.player_id', 'INNER')
            ->join(GAME_PLAYER_SCORING.' GPS', 'GPS.player_id = P.player_id AND GPS.league_id = S.league_id AND S.season_id=GPS.season_id', 'LEFT')
            ->where("S.season_id",$season_id)
            ->where("PT.is_deleted",0)
            ->where("PT.player_status",1)
            ->where("PT.is_published",1)
            ->group_by('P.player_uid')
            ->order_by('P.full_name','ASC');

        $sql = $this->db->get();
        $result = $sql->result_array();
        return $result;
    }

    /**
     * used to get fixture scorecard data
     * @param int $season_id
     * @return array
    */
    public function get_fixture_scorecard($season_id)
    {
        $this->db->select("player_id,GST.team_id,batting_runs,batting_balls_faced,batting_fours,batting_sixes,bowling_overs,bowling_runs_given,bowling_maiden_overs,bowling_wickets,out_string,batting_order,bowling_order,FORMAT(IFNULL(batting_strike_rate,0),2) AS batting_strike_rate,FORMAT(IFNULL(bowling_economy_rate,0),2) AS bowling_economy_rate,playing_11,innings,T.team_uid", FALSE)
            ->from(GAME_STATISTICS_CRICKET." AS GST")
            ->join(TEAM.' T', 'T.team_id = GST.team_id', 'INNER')
            ->where("GST.season_id",$season_id)
            ->order_by("GST.batting_order",'ASC')
            ->order_by("GST.bowling_order",'ASC');
        $sql = $this->db->get();
        $result = $sql->result_array();
        $result = ($result) ? $result : array();
        return $result;
    }

    /**
     * used to get match players selected count
     * @param array $post_data
     * @return array
    */
    public function get_players_selection_count($cm_id)
    {
        $sql = "SELECT pl.id,count(pl.id) as total FROM ".$this->db->dbprefix(LINEUP_MASTER).", JSON_TABLE(team_data, '$.pl[*]' COLUMNS ( id int(11) path '$') ) pl WHERE collection_master_id='".$cm_id."' GROUP BY pl.id";
        $query = $this->db->query($sql);
        return ($query->num_rows() > 0) ? $query->result_array() : array();
    }

    public function get_user_team_list($cm_id)
    {
        $this->db->select('LM.lineup_master_id,LM.team_data', FALSE)
                        ->from(LINEUP_MASTER.' LM')
                        ->join(LINEUP_MASTER_CONTEST.' LMC', 'LM.lineup_master_id = LMC.lineup_master_id', 'INNER');
       $this->db->where("LM.collection_master_id",$cm_id);
       $this->db->where("LM.user_id",$this->user_id);
       $this->db->where("LMC.fee_refund",0);
       $this->db->group_by("LM.lineup_master_id");
       $sql = $this->db->get();
       $result = $sql->result_array();
       return $result;
    }
}