<?php
class Lobby_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * used to get lobby fixture list
     * @param int $sports_id
     * @return array
    */
    public function get_lobby_fixture_list($sports_id) {
        $current_date = format_date();
        $this->db->select("CM.collection_master_id,CM.league_id,CM.collection_name,CM.season_scheduled_date,IFNULL(CM.2nd_inning_date,'') as 2nd_inning_date,CM.status,CM.season_game_count,CM.is_pin,CM.is_gc,CM.is_tour_game,CM.is_h2h,GROUP_CONCAT(DISTINCT CS.season_id) as season_ids,IFNULL(L.league_display_name,L.league_name) AS league_name,IFNULL(L.image,'') as league_image,L.is_featured,L.ldb,ROUND(SUM(CASE WHEN C.prize_type=0 THEN C.max_prize_pool WHEN C.prize_type=1 THEN C.max_prize_pool ELSE 0 END) / COUNT(DISTINCT CS.season_id)) AS total_prize_pool,SUM(CASE WHEN C.is_2nd_inning = 0 THEN 1 ELSE 0 END) as dfs_total,SUM(CASE WHEN C.is_2nd_inning = 1 THEN 1 ELSE 0 END) as 2nd_total,IF(ISNULL(CM.setting),0,1) as is_dm", FALSE);
        $this->db->from(COLLECTION_MASTER." as CM");
        $this->db->join(COLLECTION_SEASON.' as CS', 'CM.collection_master_id = CS.collection_master_id', "INNER");
        $this->db->join(CONTEST . ' as C', 'C.collection_master_id = CM.collection_master_id AND C.status=0', "INNER");
        $this->db->join(LEAGUE . ' as L', 'L.league_id = CM.league_id', "INNER");
        $this->db->where("((CM.season_scheduled_date > '".$current_date."' AND C.is_2nd_inning=0) OR (CM.2nd_inning_date > '".$current_date."' AND C.is_2nd_inning=1))");
        $this->db->where("C.sports_id",$sports_id);
        $this->db->where('CM.status','0');
        $this->db->group_by("CM.collection_master_id");
        $this->db->order_by("CM.is_pin","DESC");
        $this->db->order_by("CM.season_scheduled_date", "ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get fixture contest list for Without login users, without private contests
     * @param array $post_data
     * @return array
    */
    public function get_fixture_contest($post_data) {
        $current_date = format_date();
        $is_2nd_inning = isset($post_data['is_2nd_inning']) ? $post_data['is_2nd_inning'] : 0;
        $user_where = array(0);
        if($this->user_id != ""){
            $user_where[] = $this->user_id;
        }

        $this->db->select("C.contest_id,C.contest_unique_id,C.collection_master_id,C.group_id,C.season_scheduled_date,C.entry_fee,C.site_rake,C.size,C.minimum_size,C.max_bonus_allowed,C.total_user_joined,C.prize_pool,C.guaranteed_prize,C.multiple_lineup,C.prize_type,C.is_pin_contest,C.is_tie_breaker,C.currency_type,IFNULL(C.contest_title,'') as contest_title,IFNULL(NULLIF(C.contest_title, ''),C.contest_name) as contest_name,IFNULL(C.sponsor_logo,'') as sponsor_logo,IFNULL(C.sponsor_link,'') as sponsor_link,C.prize_distibution_detail,C.is_scratchwin,C.is_2nd_inning", FALSE);
        $this->db->from(CONTEST." as C");
        $this->db->where('C.status', 0);
        $this->db->where('C.is_2nd_inning',$is_2nd_inning);
        $this->db->where('C.collection_master_id', $post_data['collection_master_id']);
        $this->db->where_in("C.user_id",$user_where);
        $this->db->where('C.size > C.total_user_joined',NULL);
        $this->db->where("C.season_scheduled_date >",$current_date);
        if(isset($post_data['h2h_group_id']) && $post_data['h2h_group_id'] != ""){
            $this->db->where('C.group_id != ', $post_data['h2h_group_id']);
        }
        if(isset($post_data['rookie_group_id']) && $post_data['rookie_group_id'] != ""){
            $this->db->order_by("FIELD(C.group_id,".$post_data['rookie_group_id'].")");
        }
        $this->db->order_by("C.is_pin_contest", "DESC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get lobby banner list
     * @param array $post_data
     * @return array
    */
    public function get_user_joined_contest_data($post_data) {
        if (empty($post_data)) {
            return false;
        }

        $this->db->select("LMC.contest_id,IFNULL(COUNT(LM.lineup_master_id), 0) as lm_count", FALSE);
        $this->db->from(LINEUP_MASTER." as LM");
        $this->db->join(LINEUP_MASTER_CONTEST . ' as LMC', 'LMC.lineup_master_id = LM.lineup_master_id', "INNER");
        $this->db->where('LM.collection_master_id', $post_data['collection_master_id']);
        $this->db->where('LM.user_id', $post_data['user_id']);
        $this->db->group_by("LMC.contest_id");
        if(isset($post_data['is_2nd_inning']) && in_array($post_data['is_2nd_inning'],[0,1])){
            $this->db->where('LM.is_2nd_inning',$post_data['is_2nd_inning']);
        }
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get lobby banner list
     * @param array $post_data
     * @return array
    */
    public function get_season_tournament($season_ids) {
        if (empty($season_ids)) {
            return false;
        }

        $this->db->select("TS.season_id,T.name as tournament_name,count(TS.season_id) as tournament_count,T.tournament_id", FALSE);
        $this->db->from(TOURNAMENT_SEASON." as TS");
        $this->db->join(TOURNAMENT.' as T', 'T.tournament_id = TS.tournament_id', "INNER");
        $this->db->where('T.status',"0");
        $this->db->where('TS.contest_id !=',"0");
        $this->db->where_in('TS.season_id', $season_ids);
        $this->db->group_by("TS.season_id");
        $result = $this->db->get()->result_array();
        return $result;
    }
}
