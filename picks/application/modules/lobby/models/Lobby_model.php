<?php
class Lobby_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * used to get lobby fixture list
     * @param array $post_data
     * @return array
    */

    public function get_lobby_fixture_list($post_data) {
        $current_date = format_date();

		$this->db->select("S.season_id,S.delay_message,S.delay_minute,S.scheduled_date, (UNIX_TIMESTAMP(S.scheduled_date) * 1000 ) as game_starts_in,S.league_id,S.status_overview,S.status,S.is_pin_fixture,T1.team_abbr as home,T2.team_abbr as away,T1.flag as home_flag, T2.flag as away_flag,L.league_id,L.league_name,
		SUM(
		CASE WHEN C.prize_type=0 THEN C.max_prize_pool WHEN C.prize_type=1 THEN C.max_prize_pool ELSE 0 END) AS total_prize_pool,SP.name as sports_name,SP.sports_id", FALSE);
        $league_where = '';
        if(!empty($post_data['sports_id'])){
            $league_where = "AND L.sports_id=".$post_data['sports_id']."";
        }
		$this->db->from(SEASON . " as S");
		$this->db->join(TEAM. " T1","T1.team_id = S.home_id", "INNER");
        $this->db->join(TEAM. " T2","T2.team_id = S.away_id", "INNER");
		$this->db->join(CONTEST . ' as C', 'S.season_id = C.season_id AND C.status=0', "INNER");
        $this->db->join(SPORTS . ' as SP', 'SP.sports_id = C.sports_id', "INNER");
		$this->db->join(LEAGUE . ' as L', 'L.league_id = S.league_id '.$league_where.'', "INNER");
		$this->db->where("S.scheduled_date >", $current_date);
        if(!empty($post_data['sports_id'])){
		  $this->db->where("C.sports_id", $post_data['sports_id']);        
        }else{
             $this->db->where("S.is_pin_fixture", 1);        
        }
		$this->db->where('C.contest_access_type', '0');
		$this->db->where('S.status', '0');
		$this->db->where('S.published', 1);
		$this->db->group_by("S.season_id");
		$this->db->order_by("S.scheduled_date", "ASC");
		return  $this->db->get()->result_array();
		            
    }

    /**
     * used to get user joined data
     * @param array $post_data
     * @return array
    */
    public function get_user_joined_contest_data($season_id, $user_id) {
        if (!$user_id) {
            return false;
        }

        $this->db->select("UC.contest_id,IFNULL(COUNT(UC.user_team_id), 0) as lm_count", FALSE);
        $this->db->from(USER_TEAM . " as UT");
        $this->db->join(USER_CONTEST . ' as UC', 'UC.user_team_id = UT.user_team_id', "INNER");
        $this->db->where('UT.season_id', $season_id);
        $this->db->where('UT.user_id', $user_id);
        $this->db->group_by("UC.contest_id");
        return $this->db->get()->result_array();
    }

     /**
     * used to get fixture contest list for Without login users, without private contests
     * @param array $post_data
     * @param int $group_id
     * @return array
    */
    public function get_season_contests($post_data, $group_id = "") {
        $current_date = format_date();
        $season_id = $post_data['season_id'];
        if (!isset($post_data['sports_id'])) {
            $post_data['sports_id'] = 1;
        }
        $user_where = array(0);
        if($this->user_id != ""){
            $user_where[] = $this->user_id;
        }
     
        $this->db->select("C.contest_id,C.contest_unique_id,C.season_id,C.group_id,C.league_id,C.entry_fee,C.size,C.minimum_size,C.max_bonus_allowed,C.scheduled_date,C.total_user_joined,C.prize_pool,C.guaranteed_prize,C.multiple_lineup,C.contest_access_type,C.prize_distibution_detail,C.prize_type,C.is_pin_contest,C.is_tie_breaker,C.currency_type,IFNULL(C.contest_title,'') as contest_title,IFNULL(C.sponsor_logo,'') as sponsor_logo,IFNULL(C.sponsor_link,'') as sponsor_link", FALSE);
        $this->db->from(CONTEST . " as C");
        $this->db->join(SEASON . ' as S', 'S.season_id = C.season_id', "INNER");
        $this->db->where('C.status', 0);
        //$this->db->where('C.contest_access_type', 0);
        $this->db->where_in("C.user_id",$user_where);
        $this->db->where('C.size > C.total_user_joined',NULL);
        $this->db->where('C.season_id', $season_id);
        if (isset($group_id) && $group_id != "") {
            $this->db->where('C.group_id', $group_id);
        }

        if(isset($post_data['h2h_group_id']) && $post_data['h2h_group_id'] != ""){
            $this->db->where('C.group_id != ', $post_data['h2h_group_id']);
        }

        if (isset($post_data['pin_contest']) && $post_data['pin_contest'] == 1) {
            $this->db->where('C.is_pin_contest', 1);
        } else {
            $this->db->where('C.is_pin_contest', 0);
        }

        $this->db->where("C.scheduled_date > '".$current_date."'");
        $this->db->order_by("C.is_pin_contest", "DESC");

        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get lobby filter slider options
     * @param array $post_data
     * @return array
     */
    public function get_lobby_filter_slider_options($post_data) {
        $slider_option = array(
            "winning" => array("min" => 0),
            "entry_fee" => array("min" => 0),
            "entries" => array("min" => 0)
        );
       $this->db->select("MAX(IF(max_prize_pool > prize_pool,max_prize_pool,prize_pool)) as max_prize_pool,MAX(entry_fee) as max_entry_fee,MAX(entry_fee) as max_entry_fee,MAX(size) as max_size", FALSE)
                ->from(CONTEST)
                ->where("sports_id", $post_data['sports_id'])
                ->where("status", "0");

        $result =  $this->db->limit(1)
                ->get()
                ->row_array();

        $slider_option['winning']['max'] = (!empty($result['max_prize_pool'])) ? (int) $result['max_prize_pool'] : 10000;
        $slider_option['entry_fee']['max'] = (!empty($result['max_entry_fee'])) ? (int) $result['max_entry_fee'] : 10000;
        $slider_option['entries']['max'] = (!empty($result['max_size'])) ? (int) $result['max_size'] : 10000;
        return $slider_option;
    }

   /**
    * used to get lobby filter league list
    * @param array $post_data
    * @return array
    */
    public function get_lobby_filter_leagues($post_data) {
        $current_date = format_date();
        $this->db->select("C.league_id,L.display_name as league_name");
        $this->db->from(CONTEST . " AS C");
        $this->db->join(LEAGUE . " AS L", " L.league_id = C.league_id", "INNER");
        $this->db->where('C.sports_id', $post_data['sports_id']);
        $this->db->where('C.scheduled_date >= ', $current_date);

        $this->db->group_by("C.league_id");
        return $this->db->get()->result_array();
    }


    /**
     * used to get user joined team list
     * @param int $season_id
     * @return array
    */
    public function get_all_user_lineup_list($season_id) {
       
        $this->db->select("UT.user_team_id,UT.season_id,UT.team_name,
        count(DISTINCT UC.user_contest_id) as total_joined", FALSE);
        $this->db->from(USER_TEAM . ' UT');
        $this->db->join(USER_CONTEST . ' as UC', 'UC.user_team_id = UT.user_team_id', "LEFT");
        $this->db->where('UT.season_id', $season_id);
        $this->db->where('UT.user_id', $this->user_id);
        $this->db->group_by('UT.user_team_id');
        $this->db->order_by('UT.user_team_id', "ASC");
        return $this->db->get()->result_array();
    }

    /**
     * used to get match season details
     * @param int $season_id
     * @return array
    */
    public function get_fixture_season_details($season_id){

        $this->db->select("S.season_id,S.league_id,S.match,S.home_id,S.away_id,T1.team_abbr AS home,S.scheduled_date,T1.flag AS home_flag,T2.flag  AS away_flag,T2.team_abbr AS away,S.delay_minute,S.delay_message,S.status,S.status_overview,(UNIX_TIMESTAMP(S.scheduled_date) * 1000) as game_starts_in,L.league_name",FALSE);
        $this->db->from(SEASON." as S");
        $this->db->join(TEAM_LEAGUE.' TL1', 'TL1.team_id = S.home_id AND TL1.league_id = S.league_id','INNER');
        $this->db->join(TEAM.' as T1', 'T1.team_id = TL1.team_id',"INNER");
        $this->db->join(TEAM_LEAGUE.' TL2', 'TL2.team_id = S.away_id AND TL2.league_id = S.league_id',"LEFT");
        $this->db->join(TEAM.' as T2', 'T2.team_id = TL2.team_id',"LEFT");
        $this->db->join(LEAGUE.' as L', 'L.league_id = S.league_id',"INNER");
        $this->db->where('S.season_id', $season_id);
        $this->db->group_by("S.season_id");
        $this->db->order_by("S.scheduled_date","ASC");
        $result = $this->db->get()->row_array();
        return $result;
    }

}
