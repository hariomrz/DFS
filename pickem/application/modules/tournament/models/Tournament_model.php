<?php
class Tournament_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * used to get tournament list
     * @param int $sports_id
     * @return array
    */
    public function get_tournament_list($sports_id=0) {
        $current_date = format_date();

        $cond = '';
        if($this->user_id){
            $cond = "AND UT.user_id= $this->user_id";
        }

        $this->db->select("count(UT.user_tournament_id) as is_joined,T.tournament_id,T.sports_id,T.image,T.name,T.start_date,T.max_bonus,T.end_date,T.prize_detail,T.entry_fee,T.is_pin,IFNULL(L.display_name,L.league_name) AS league,T.currency_type,T.perfect_score,UT.user_id,T.is_score_predict,T.modified_date,T.status,L.is_featured,L.league_id,IFNULL(UT.game_rank,'0') as game_rank", FALSE);
        $this->db->from(TOURNAMENT." as T");
        $this->db->join(LEAGUE . ' as L', 'L.league_id = T.league_id', "INNER");
        $this->db->join(USER_TOURNAMENT . ' as UT', 'UT.tournament_id = T.tournament_id '.$cond.' ', "LEFT");  

        if(!empty($sports_id)){
            $this->db->where("T.sports_id", $sports_id);
        }else{
            $this->db->where("T.is_pin", 1);
        }
        $this->db->where('T.status !=',1);
        $this->db->where("T.end_date > ", $current_date);
        $this->db->group_by("T.tournament_id");
        $this->db->order_by("T.is_pin", "DESC");
        $this->db->order_by("T.start_date", "ASC");
        $result = $this->db->get()->result_array();
        //echo $this->db->last_query();die;
        return $result;
    }


      /**
     * used to get tournament list
     * @param int $sports_id
     * @return array
    */
    public function get_featured_tournament_list($league_id) {
        $current_date = format_date();

        $cond = '';
        // if($this->user_id){
        //     $cond = "AND UT.user_id= $this->user_id";
        // }

        $this->db->select("count(UT.user_tournament_id) as is_joined,T.tournament_id,T.sports_id,T.image,T.name,T.start_date,T.max_bonus,T.end_date,T.prize_detail,T.entry_fee,T.is_pin,IFNULL(L.display_name,L.league_name) AS league,T.currency_type,T.perfect_score,UT.user_id,T.is_score_predict,T.modified_date,T.status,IFNULL(UT.game_rank,'-') as game_rank", FALSE);
        $this->db->from(TOURNAMENT." as T");
        $this->db->join(LEAGUE . ' as L', 'L.league_id = T.league_id', "INNER");
        $this->db->join(USER_TOURNAMENT . ' as UT', 'UT.tournament_id = T.tournament_id '.$cond.' ', "LEFT");  

        if(!empty($league_id)){
            $this->db->where("T.league_id", $league_id);
        }else{
            $this->db->where("T.is_pin", 1);
        }
        // $this->db->where('T.status !=',1);
        $this->db->where('T.status',0);
        // $this->db->where("T.start_date > ", $current_date);
        $this->db->group_by("T.tournament_id");
        $this->db->order_by("T.is_pin", "DESC");
        $this->db->order_by("T.start_date", "DESC");
        $result = $this->db->get()->result_array();
        //echo $this->db->last_query();die;
        return $result;
    }

    

    /**
     * used to get tournament details
     * @param int $sports_id
     * @return array
    */
    public function get_tournament_details($tournament_id) {
        $this->db->select("T.tournament_id,T.sports_id,T.tie_breaker_question,T.name,T.image,T.is_pin,T.start_date,T.end_date,T.currency_type,T.entry_fee,T.max_bonus,T.prize_detail,T.banner_images,T.match_count,T.status,IFNULL(L.display_name,L.league_name) AS league,GROUP_CONCAT(DISTINCT TS.season_id) as season_ids,IFNULL(UTO.user_tournament_id,0) as user_tournament_id,IFNULL(UTO.game_rank,0) as game_rank,T.tie_breaker_answer,UTO.tie_breaker_answer as tie_breaker_user,T.perfect_score,T.is_score_predict,UTO.perfect_score_data as user_perfect_score_data", FALSE);
        $this->db->from(TOURNAMENT." as T");
        $this->db->join(LEAGUE.' as L', 'L.league_id = T.league_id', "INNER");
        $this->db->join(TOURNAMENT_SEASON. ' as TS', 'TS.tournament_id = T.tournament_id', "INNER");
        $this->db->join(USER_TOURNAMENT. ' as UTO', 'UTO.tournament_id = T.tournament_id AND UTO.user_id='.$this->user_id.'', "LEFT");
        $this->db->where("T.tournament_id", $tournament_id);
        //$this->db->where('UTO.user_id',$this->user_id);
        $this->db->group_by("T.tournament_id");
        $this->db->order_by("T.start_date", "DESC");
        $result = $this->db->get()->row_array();
        return $result;
    }
/*
    public function get_my_tournament($post_data) {
        $current_date = format_date();
        $sort_field = 'start_date';
        $sort_order = 'ASC'; 
        $user_id = $this->user_id;
        $this->db->select("T.tournament_id,T.name,T.league_id,T.start_date,T.end_date,T.match_count,
                        ,L.league_name,T.image,UT.game_rank", FALSE);
               $this->db->from(TOURNAMENT . " AS T");
               $this->db->join(LEAGUE.' L','L.league_id=T.league_id');
               $this->db->join(USER_TOURNAMENT . " AS UT", "UT.tournament_id=T.tournament_id AND UT.user_id=" . $user_id, "INNER");
    
        if (!empty($post_data['sports_id'])) {
            $this->db->where('T.sports_id', $post_data['sports_id']);
        }
    
        if (!empty($post_data['status']) && $post_data['status'] == 1) {//live
            $sort_order = 'DESC';
            $sort_field = 'T.start_date';
            $this->db->where('T.start_date<', $current_date)
                    ->where('T.status', 0);
        }

        if (isset($post_data['status']) && $post_data['status'] == 0) {//Upcoming
            $sort_order = 'ASC';
            $sort_field = 'T.start_date';
            $this->db->where('T.start_date>', $current_date)
                    ->where('T.status', 0);
        }

        if (!empty($post_data['status']) && $post_data['status'] == 2) {//completed
            $sort_order = 'DESC';
            $sort_field = 'T.start_date';
            $this->db->where('T.start_date<', $current_date)
                    ->where_in('T.status', array(2,3));
        }

        $this->db->group_by('T.tournament_id');
        if (!empty($sort_field) && !empty($sort_order)) {
            $this->db->order_by($sort_field, $sort_order);
        }

        $sql = $this->db->get();
        //echo $this->db->last_query();die();
        return $sql->result_array();
    }*/


    /**
     * used to get_my_tournament
     * @param int $sports_id
     * @return array
    */

    public function get_my_tournament($post_data)
    {

        $current_date = format_date();
        $cond = '';
        $this->db->select("T.tournament_id,T.image,T.name,T.start_date,T.end_date,T.match_count,T.status,T.prize_detail,IFNULL(L.display_name,L.league_name) AS league,T.currency_type,T.perfect_score,T.is_score_predict,T.modified_date", FALSE);
        $this->db->from(TOURNAMENT." as T");
        $this->db->join(LEAGUE . ' as L', 'L.league_id = T.league_id', "INNER");

         $this->db->select("UT.user_id,IFNULL(UT.total_score,'0.00') as total_score,IFNULL(UT.game_rank,'-') as game_rank,IFNULL(UT.user_tournament_id,0) as user_tournament_id,IFNULL(UT.amount,'0') as amount,IFNULL(UT.bonus,'0') as bonus,IFNULL(UT.coin,'0') as coin,IFNULL(UT.merchandise,'') as merchandise,IFNULL(UT.is_winner,0) as is_winner", FALSE);
        $cond = "AND UT.user_id= $this->user_id";
        $this->db->join(USER_TOURNAMENT . ' as UT', 'UT.tournament_id = T.tournament_id '.$cond.' ', "INNER");  
        

        
        $this->db->where("T.sports_id", $post_data['sports_id']);
        if(!empty($post_data['status']) && $post_data['status'] == 2) {
          $this->db->where_in("T.status",["2","3"]);   
        }else{
          $this->db->where("T.status !=","1");
        }
       if(isset($post_data['is_previous']) && !empty($post_data['is_previous'])){
            $end_date = date('Y-m-d',strtotime('-10 days',strtotime($current_date)));
            $this->db->where("T.end_date > ",$end_date);
        }
        //$this->db->group_by("T.tournament_id");
        $this->db->order_by("T.start_date", "DESC");
        $result = $this->db->get()->result_array();
        //echo $this->db->last_query();die;
        return $result;
    

    }

    /**
     * used to join contest
     * @param array $post_data
     * @return array
     */
    public function join_tournament($tournament) {

        $this->db->trans_begin();
        $user_tournament = array();
        $user_tournament['tournament_id'] = $tournament["tournament_id"];
        $user_tournament['user_id'] = $this->user_id;
        $user_tournament['user_name'] = $this->user_name;
        $user_tournament['total_score'] = 0;
        $user_tournament['added_date'] = format_date();
        $this->db->insert(USER_TOURNAMENT, $user_tournament);
        $user_tournament_id = $this->db->insert_id();

        if ($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $result = array();
        }
        else{
            $this->db->trans_commit();
            $result= array("user_tournament_id" => $user_tournament_id);
        }  

        return $result;
     
    }

    /**
     * used to join contest
     * @param array $post_data
     * @return array
     */
    public function remove_joined_tournament($user_tournament_id) {
        $this->db->where("user_tournament_id",$user_tournament_id);
        $this->db->delete(USER_TOURNAMENT);
        return $this->db->affected_rows();
       
    }

    /**
     * used to get tournament details
     * @param int $sports_id
     * @return array
    */
    public function get_user_tournament($sports_id=0) {

        $this->db->select("T.tournament_id,T.sports_id,T.image,T.name,T.start_date,T.match_count,T.status,UT.user_tournament_id,UT.game_rank,IFNULL(L.display_name,L.league_name) AS league,count(DISTINCT UTM.season_id) as predict_count", FALSE);
        $this->db->from(USER_TOURNAMENT." as UT");
        $this->db->join(USER_TEAM." as UTM","UTM.user_tournament_id=UT.user_tournament_id","LEFT");
        $this->db->join(TOURNAMENT.' as T', 'T.tournament_id = UT.tournament_id', "INNER");
        $this->db->join(LEAGUE.' as L', 'L.league_id = T.league_id', "INNER");
        $this->db->where("UT.user_id", $this->user_id);
        $this->db->where('T.status !=',1);
         if(!empty($sports_id)){
            $this->db->where('T.sports_id',$sports_id);
         }else{
            $this->db->where("T.is_pin", 1);
         }

        $this->db->group_by("T.tournament_id");
        $this->db->order_by("T.start_date", "DESC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used for get user total invested amount and and check it with self exlusion limit
     * @param int $user_id
     * @return boolean
     */
    public function user_join_tournament_ids($user_id) {

        $current_date = format_date("today", "Y-m");
        $this->db->select("GROUP_CONCAT(DISTINCT T.tournament_id) as tournament_ids")
          
                ->from(USER_TOURNAMENT . " UT")
                ->join(TOURNAMENT . " T", "T.tournament_id = UT.tournament_id", "INNER")
                ->where("UT.user_id", $user_id);
        $this->db->where("DATE_FORMAT(T.start_date,'%Y-%m')", $current_date);
        $result = $this->db->get()->row_array();
        $tournament_ids_arr = array();
        if(!empty($result)) {
            $tournament_ids = $result['tournament_ids'];
            $tournament_ids_arr = explode(',', $tournament_ids);
        }
        return $tournament_ids_arr;
    }



    /**
    * Function used for get user prediction details
    * @param tournament_id
    * @return array
    */
    function get_prediction_detail($tournament_id)
    {
        return $this->db->select("UT.season_id,UT.user_team_id,UT.team_id,UT.score,UT.away_predict,UT.home_predict",FALSE)
        ->from(USER_TOURNAMENT . ' UTO')
        ->join(USER_TEAM . ' UT', "UTO.user_tournament_id = UT.user_tournament_id")
        ->where('UTO.tournament_id',$tournament_id)
        ->where('UTO.user_id',$this->user_id)
        ->get()->result_array();   
    }


    /**
    * Function used for get tournament users
    * @param array $post_data
    * @return array
    */
    public function get_leaderboard($post_data)
    {
        $post_data['page'] = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $post_data['limit'] = isset($post_data['page_size']) ? $post_data['page_size'] : RECORD_LIMIT;
        $pagination = get_pagination_data($post_data);
        $this->db->select("UT.user_tournament_id,UT.user_id,UT.total_score,IF(UT.game_rank=0,'-',UT.game_rank) as game_rank,UT.is_winner,UT.amount,UT.bonus,UT.coin,UT.user_name,UT.merchandise,UT.perfect_score_data",FALSE)
            ->from(USER_TOURNAMENT." AS UT")
            ->where("UT.tournament_id", $post_data['tournament_id'])
            /*->order_by("FIELD(UT.user_id,'".$this->user_id."') DESC")*/
            ->order_by('UT.game_rank','ASC')
            ->group_by('UT.user_id');
        $result = $this->db->limit($pagination['limit'],$pagination['offset'])->get()->result_array();
        return $result;
    }

     /**
    * Function used for get user match list
    * @param array $post_data
    * @return string
    */
    public function get_user_tournament_detail($user_tournament_id){
        $this->db->select("UT.user_team_id,UT.season_id,UT.team_id,UT.score,UT.is_correct,UTO.total_score,UTO.game_rank,UTO.user_name,S.home_id,S.away_id,IFNULL(T1.display_name,T1.team_abbr) AS home,IFNULL(T2.display_name,T2.team_abbr) AS away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,", FALSE)
                    ->from(USER_TEAM.' AS UT')
                    ->join(USER_TOURNAMENT.' AS UTO', 'UTO.user_tournament_id = UT.user_tournament_id')
                    ->join(SEASON.' AS S', 'S.season_id = UT.season_id')
                    ->join(TEAM.' AS T1', 'T1.team_id = S.home_id')
                    ->join(TEAM.' AS T2', 'T2.team_id = S.away_id')
                    ->where("UTO.user_id",$this->user_id)
                    ->where("UTO.user_tournament_id",$user_tournament_id);
        
        $result = $this->db->get()->result_array();
        return $result;
    }

   /**
    * Function used for get footer leaderboard details
    * @param void
    * @return  array
    */
    public function get_tournament_leaderboard_detail($sports_id,$is_previous=0)
    {
        $current_date = format_date();
        $this->db->select("T.tournament_id,T.name,T.modified_date,T.status,T.is_score_predict", FALSE);
        $this->db->from(TOURNAMENT." as T");
        $this->db->where('T.status !=',1);
        $this->db->where('T.sports_id',$sports_id);
        $this->db->where("T.start_date < ", $current_date);
        if(isset($is_previous) && !empty($is_previous)){
            $end_date = date('Y-m-d',strtotime('-10 days',strtotime($current_date)));
            $this->db->where("T.end_date > ",$end_date);
            $this->db->join(USER_TOURNAMENT . ' as UT', 'UT.tournament_id = T.tournament_id', "INNER"); 
            $this->db->group_by("T.tournament_id");
        }
        $this->db->order_by("T.start_date", "DESC");
        $result = $this->db->get()->result_array();
        //echo $this->db->last_query();die;
        return $result;
    }

    public function get_sports_list_leaderboard() {
        $current_date = format_date();
        $this->db->select("DISTINCT T.sports_id", FALSE);
        $this->db->from(TOURNAMENT." as T");
        $this->db->join(USER_TOURNAMENT.' UT','UT.tournament_id = T.tournament_id','INNER');
        $end_date = date('Y-m-d',strtotime('-10 days',strtotime($current_date)));
        $this->db->where("T.start_date < ", $current_date);
        $this->db->where("T.end_date > ",$end_date);
        $this->db->where("T.status <>",1);
        $result = $this->db->get()->result_array();
        return $result;
    }

    public function get_fixture_users($post_data){
        $current_date = format_date();
        $this->db->select("UT.user_team_id,UT.team_id,UT.score,IFNULL(UT.away_predict,'') as away_predict,IFNULL(UT.home_predict,'') as home_predict,UT.is_correct,T.user_id,T.user_name,'' as image", FALSE);
        $this->db->from(USER_TEAM." as UT");
        $this->db->join(USER_TOURNAMENT.' T','T.user_tournament_id = UT.user_tournament_id','INNER');
        $this->db->where("T.tournament_id", $post_data['tournament_id']);
        $this->db->where("UT.season_id",$post_data['season_id']);
        $this->db->order_by("UT.user_team_id","ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get joined tournament details
     * @param int $user_tournament_id
     * @return array
    */
    public function get_user_tournament_details($user_tournament_id) {
        $this->db->select("UT.user_tournament_id,UT.tournament_id,UT.user_id,UT.total_score,UT.is_winner,UT.tie_breaker_answer,T.name as tournament_name,T.start_date,T.end_date,T.status,T.currency_type,T.entry_fee,T.prize_detail", FALSE);
        $this->db->from(USER_TOURNAMENT." as UT");
        $this->db->join(TOURNAMENT.' as T', 'T.tournament_id = UT.tournament_id', "INNER");
        $this->db->where("UT.user_tournament_id", $user_tournament_id);
        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
     * used to get joined tournament history details
     * @param int $user_tournament_id
     * @return array
    */
    public function get_user_history_details($user_tournament_id) {
        $this->db->select("UT.user_team_id,UT.season_id,UT.team_id,UT.score,UT.home_predict,UT.away_predict,UT.is_correct,S.home_id,S.away_id,IFNULL(T1.display_name,T1.team_abbr) AS home,IFNULL(T2.display_name,T2.team_abbr) AS away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,S.scheduled_date,S.winning_team_id,S.score_data,S.status", FALSE);
        $this->db->from(USER_TEAM." as UT");
        $this->db->join(SEASON.' as S', 'S.season_id = UT.season_id', "INNER");
        $this->db->join(TEAM.' AS T1', 'T1.team_id = S.home_id');
        $this->db->join(TEAM.' AS T2', 'T2.team_id = S.away_id');
        $this->db->where("UT.user_tournament_id", $user_tournament_id);
        $this->db->where("UT.is_correct > ","0");
        $this->db->order_by("S.scheduled_date","ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }
}
