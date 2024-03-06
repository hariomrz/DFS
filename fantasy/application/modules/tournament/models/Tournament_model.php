<?php
class Tournament_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * used to get pin tournament
     * @param int $sports_id
     * @return array
    */
    public function get_pin_tournament($sports_id) {
        $current_date = format_date();

        $this->db->select("T.tournament_id,T.name,T.image,T.start_date,T.end_date,T.prize_detail,T.match_count,IFNULL(L.league_display_name,L.league_name) AS league,T.status,IFNULL(TS.contest_id,'0') as contest_id", FALSE);
        $this->db->from(TOURNAMENT." as T");
        $this->db->join(LEAGUE.' as L', 'L.league_id = T.league_id', "INNER");
        $this->db->join(TOURNAMENT_SEASON.' as TS', 'TS.tournament_id = T.tournament_id', "INNER");
        $this->db->where("T.sports_id", $sports_id);
        $this->db->where("T.status","0");
        $this->db->where("T.is_pin","1");
        $this->db->group_by("T.tournament_id");
        $this->db->order_by("T.tournament_id", "DESC");
        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
     * used to get tournament details
     * @param int $sports_id
     * @return array
    */
    public function get_user_tournament($sports_id) {
        $this->db->select("T.tournament_id,T.name,T.image,T.start_date,T.end_date,T.match_count,T.status,TH.total_score,TH.rank_value,TH.is_winner,IFNULL(TH.prize_data,'[]') as prize_data,IFNULL(L.league_display_name,L.league_name) AS league", FALSE);
        $this->db->from(TOURNAMENT_HISTORY." as TH");
        $this->db->join(TOURNAMENT.' as T', 'T.tournament_id = TH.tournament_id', "INNER");
        $this->db->join(LEAGUE.' as L', 'L.league_id = T.league_id', "INNER");
        $this->db->where("T.status !=","1");
        $this->db->where("T.sports_id", $sports_id);
        $this->db->where("TH.user_id", $this->user_id);
        $this->db->group_by("T.tournament_id");
        $this->db->order_by("T.start_date", "DESC");
        $this->db->order_by("TH.history_id", "DESC");
        $this->db->limit(1);
        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
     * used to get tournament list
     * @param int $sports_id
     * @return array
    */
    public function get_tournament_list($post_data) {
        $current_date = format_date();

        $this->db->select("T.tournament_id,T.name,T.image,T.start_date,T.end_date,T.prize_detail,T.match_count,T.no_of_fixture,T.is_top_team,T.status,IFNULL(L.league_display_name,L.league_name) AS league, 0 as joined_id,T.modified_date,L.is_featured,L.league_id", FALSE);
        $this->db->from(TOURNAMENT." as T");
        $this->db->join(LEAGUE.' as L', 'L.league_id = T.league_id', "INNER");
        $this->db->join(TOURNAMENT_SEASON.' as TS', 'TS.tournament_id = T.tournament_id', "INNER");

        if($this->user_id){
            $this->db->select("IFNULL(TH.total_score,'0.00') as total_score,IFNULL(TH.rank_value,'-') as rank_value,IFNULL(TH.history_id,0) as joined_id,IFNULL(TH.prize_data,'[]') as prize_data,IFNULL(TH.is_winner,0) as is_winner", FALSE);
            $this->db->join(TOURNAMENT_HISTORY.' as TH', 'TH.tournament_id = T.tournament_id AND TH.user_id='.$this->user_id, "LEFT");
        }
        if(!empty($post_data['status']) && $post_data['status'] == 2) {
          $this->db->where_in("T.status",["2","3"]);   
        }else if(isset($post_data['status']) && $post_data['status'] == "0") {
            $this->db->where("T.status","0");
        }else{
          $this->db->where("T.status !=","1");
        }
        if(isset($post_data['is_previous']) && !empty($post_data['is_previous'])){
            $end_date = date('Y-m-d',strtotime('-10 days',strtotime($current_date)));
            $this->db->where("T.end_date > ",$end_date);
        }

        $this->db->where("T.sports_id", $post_data['sports_id']);
        $this->db->group_by("T.tournament_id");
        $this->db->order_by("T.start_date", "DESC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get tournament details
     * @param int $sports_id
     * @return array
    */
    public function get_tournament_details($tournament_id) {
        $user_id = 0;
        if($this->user_id){
            $user_id = $this->user_id;
        }
        $this->db->select("T.tournament_id,T.name,T.image,T.start_date,T.end_date,T.prize_detail,IFNULL(T.banner_images,'[]') as banner_images,T.match_count,T.is_pin,T.no_of_fixture,T.is_top_team,T.status,IFNULL(L.league_display_name,L.league_name) AS league,IFNULL(TH.total_score,'0.00') as total_score,IFNULL(TH.rank_value,'-') as rank_value,IFNULL(TH.history_id,0) as joined_id,T.sports_id", FALSE);
        $this->db->from(TOURNAMENT." as T");
        $this->db->join(LEAGUE.' as L', 'L.league_id = T.league_id', "INNER");
        $this->db->join(TOURNAMENT_HISTORY.' as TH', 'TH.tournament_id = T.tournament_id AND TH.user_id='.$user_id, "LEFT");
        $this->db->where("T.tournament_id", $tournament_id);
        $this->db->where("T.status !=","1");
        $this->db->group_by("T.tournament_id");
        $this->db->order_by("T.start_date", "DESC");
        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
     * used to get joined tournament history details
     * @param int $history_id
     * @return array
    */
    public function get_user_history_details($history_id) {
        $this->db->select("TH.history_id,TH.user_id,TH.total_score,IF(TH.rank_value=0,'-',TH.rank_value) as rank_value,TH.is_winner,IFNULL(TH.prize_data,'[]') as prize_data,T.tournament_id,T.name,T.image,T.start_date,T.end_date,T.prize_detail,T.match_count,T.no_of_fixture,T.is_top_team,T.status,T.sports_id", FALSE);
        $this->db->from(TOURNAMENT_HISTORY." as TH");
        $this->db->join(TOURNAMENT.' as T', 'T.tournament_id = TH.tournament_id', "INNER");
        $this->db->where("TH.history_id", $history_id);
        $this->db->where("T.status !=","1");
        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
    * Function used for get tournament users
    * @param array $post_data
    * @return array
    */
    public function get_leaderboard($post_data)
    {
        $page_no = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit = isset($post_data['page_size']) ? $post_data['page_size'] : RECORD_LIMIT;
        $offset = get_pagination_offset($page_no, $limit);

        $this->db->select("TH.history_id,TH.user_id,TH.total_score,IF(TH.rank_value=0,'-',TH.rank_value) as rank_value,TH.is_winner,IFNULL(TH.prize_data,'[]') as prize_data,'' as name,'' as user_name,'' as image",FALSE)
            ->from(TOURNAMENT_HISTORY." AS TH")
            ->where("TH.tournament_id", $post_data['tournament_id'])
            ->order_by('TH.rank_value','ASC')
            ->group_by('TH.user_id');
        $result = $this->db->limit($limit,$offset)->get()->result_array();
        return $result;
    }

    /**
    * Function used for get user match list
    * @param array $post_data
    * @return string
    */
    public function get_user_match_list($custom_data,$user_id){
        $this->db->select("LM.lineup_master_id as lm_id,LM.collection_master_id as cm_id,LMC.lineup_master_contest_id as lmc_id,LMC.total_score as score,CM.collection_name as name,CM.season_scheduled_date,LM.team_name as team", FALSE)
                    ->from(LINEUP_MASTER.' AS LM')
                    ->join(LINEUP_MASTER_CONTEST.' AS LMC', 'LMC.lineup_master_id = LM.lineup_master_id')
                    ->join(COLLECTION_MASTER.' AS CM', 'CM.collection_master_id = LM.collection_master_id')
                    ->where_in("CONCAT(LM.collection_master_id,'_',LM.lineup_master_id)",$custom_data)
                    ->where("LM.user_id",$user_id);
        $this->db->group_by("CM.collection_master_id");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
    * Function used for get user match list
    * @param array $post_data
    * @return string
    */
    public function get_user_join_contest_data($contest_ids){
        if(empty($contest_ids)){
            return false;
        }
        $this->db->select("LM.collection_master_id as cm_id,LMC.contest_id,COUNT(DISTINCT LM.lineup_master_id) as total_teams", FALSE)
                    ->from(LINEUP_MASTER_CONTEST.' AS LMC')
                    ->join(LINEUP_MASTER.' AS LM', 'LM.lineup_master_id = LMC.lineup_master_id')
                    ->where("LMC.fee_refund","0")
                    ->where("LM.user_id",$this->user_id)
                    ->where_in("LMC.contest_id",$contest_ids);
        $this->db->group_by("LMC.contest_id");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
    * Function used for get user joined tournament data
    * @param int $tournament_id
    * @param int $user_id
    * @return array
    */
    public function get_user_joined_data($tournament_id,$user_id)
    {
        $this->db->select("THT.cm_id,SUM(THT.score) as total_score,COUNT(THT.lm_id) as total_teams,THT.contest_id,THT.is_included",false)
            ->from(TOURNAMENT_HISTORY_TEAMS." AS THT")
            ->where('THT.tournament_id',$tournament_id)
            ->where('THT.user_id',$user_id)
            ->group_by('THT.cm_id')
            ->order_by('total_score','DESC');
        $sql = $this->db->get();
        $result = $sql->result_array();
        return $result;
    }

    /**
     * Used for get season detail by season id
     * @param int $season_id
     * @return array
     */
    public function get_tournament_fixture_list($tournament_id,$sports_id)
    {
        if(empty($tournament_id)){
            return false;
        }

        $this->db->select("TS.cm_id,S.season_id,S.season_scheduled_date,S.status,S.status_overview,S.league_id,S.is_tour_game,TS.contest_id,IFNULL(C.contest_title,'') as contest_title,IFNULL(C.prize_distibution_detail,'[]') as contest_prize",false)
            ->from(TOURNAMENT_SEASON." AS TS")
            ->join(SEASON.' S','S.season_id = TS.season_id','INNER')
            ->join(CONTEST.' C','C.contest_id = TS.contest_id','LEFT')
            //->where("TS.contest_id !=","0")
            ->where("TS.tournament_id",$tournament_id)
            ->group_by("S.season_id")
            ->order_by("S.season_scheduled_date","ASC");

        if(in_array($sports_id,$this->tour_game_sports)){
            $this->db->select("IFNULL(S.tournament_name,'') as collection_name,IFNULL(S.tournament_name,'') as tournament_name,IFNULL(S.end_scheduled_date,S.season_scheduled_date) as end_scheduled_date,S.match_event,IFNULL(L.image,'') as league_image",false);
            $this->db->join(LEAGUE.' L','L.league_id = S.league_id','INNER');
        }else{
            $this->db->select("IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,CONCAT_WS(' vs ',IFNULL(T1.display_team_abbr,T1.team_abbr),IFNULL(T2.display_team_abbr,T2.team_abbr)) as collection_name",false)
            ->join(TEAM.' T1','T1.team_id = S.home_id','INNER')
            ->join(TEAM.' T2','T2.team_id = S.away_id','INNER');
        }

        $sql = $this->db->get();
        $result = $sql->result_array();
        return ($result) ? $result : array();
    }

     public function get_tournament_leaderboard_detail($sports_id,$is_previous=0) {
        $current_date = format_date();

        $this->db->select("T.tournament_id,T.name,T.sports_id,T.modified_date,T.status", FALSE);
        $this->db->from(TOURNAMENT." as T");
        $this->db->where("T.status !=","1");
        $this->db->where('T.sports_id',$sports_id);
        $this->db->where("T.start_date < ", $current_date);
        if(isset($is_previous) && !empty($is_previous)){
            $end_date = date('Y-m-d',strtotime('-10 days',strtotime($current_date)));
            $this->db->where("T.end_date > ",$end_date);
            $this->db->join(TOURNAMENT_HISTORY.' TH','TH.tournament_id = T.tournament_id','INNER');
            $this->db->group_by("T.tournament_id");
        }
        $this->db->order_by("T.start_date", 'DESC');
        $result = $this->db->get()->result_array();
        return $result;
    }

    public function get_sports_list_leaderboard() {
        $current_date = format_date();
        $this->db->select("DISTINCT T.sports_id", FALSE);
        $this->db->from(TOURNAMENT." as T");
        $this->db->join(TOURNAMENT_HISTORY.' TH','TH.tournament_id = T.tournament_id','INNER');
        $this->db->where("T.status <>",1);
        $end_date = date('Y-m-d',strtotime('-10 days',strtotime($current_date)));
        $this->db->where("T.start_date < ", $current_date);
        $this->db->where("T.end_date > ",$end_date);
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
    * Function used for get fixture tournament list
    * @param int $cm_id
    * @return array
    */
    public function get_fixture_tournament_list($cm_id)
    {
        $this->db->select("T.tournament_id,T.name,TS.contest_id,T.no_of_fixture,T.is_top_team",false)
            ->from(TOURNAMENT_SEASON." AS TS")
            ->join(TOURNAMENT.' T','T.tournament_id = TS.tournament_id','INNER')
            ->where('T.status',"0")
            ->where('TS.cm_id',$cm_id)
            ->where('TS.contest_id != ',"0")
            ->group_by('T.tournament_id');
        $sql = $this->db->get();
        $result = $sql->result_array();
        return $result;
    }


    /**
     * used to get tournament list
     * @param int $sports_id
     * @return array
    */
    public function get_featured_tournament_list($post_data) {
        $current_date = format_date();

        $this->db->select("T.tournament_id,T.name,T.image,T.start_date,T.end_date,T.prize_detail,T.match_count,T.no_of_fixture,T.is_top_team,T.status,IFNULL(L.league_display_name,L.league_name) AS league, 0 as joined_id,T.modified_date", FALSE);
        $this->db->from(TOURNAMENT." as T");
        $this->db->join(LEAGUE.' as L', 'L.league_id = T.league_id', "INNER");
        $this->db->join(TOURNAMENT_SEASON.' as TS', 'TS.tournament_id = T.tournament_id', "INNER");

        if($this->user_id){
            $this->db->select("IFNULL(TH.total_score,'0.00') as total_score,IFNULL(TH.rank_value,'-') as rank_value,IFNULL(TH.history_id,0) as joined_id", FALSE);
            $this->db->join(TOURNAMENT_HISTORY.' as TH', 'TH.tournament_id = T.tournament_id AND TH.user_id='.$this->user_id, "LEFT");
        }
        if(!empty($post_data['status']) && $post_data['status'] == 2) {
          $this->db->where_in("T.status",["2","3"]);   
        }else{
        //   $this->db->where("T.status !=","1");
         $this->db->where('T.status',0);
        } 
     
        $this->db->where("T.league_id", $post_data['league_id']);
        $this->db->group_by("T.tournament_id");
        $this->db->order_by("T.start_date", "DESC");
        $result = $this->db->get()->result_array();
        return $result;
    }



}
