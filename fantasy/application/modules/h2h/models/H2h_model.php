<?php
class H2h_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * used to get match wise h2h challenge template list
     * @param array $post_data
     * @return array
    */
    public function get_h2h_template($post_data) {
        $current_date = format_date();
        $collection_master_id = $post_data['collection_master_id'];
        $h2h_group_id = $post_data['h2h_group_id'];
     
        $this->db->select("TP.contest_template_id,CT.collection_master_id,TP.group_id,TP.entry_fee,TP.size,TP.minimum_size,TP.max_bonus_allowed,TP.prize_pool,TP.guaranteed_prize,TP.multiple_lineup,TP.prize_distibution_detail,TP.prize_type,TP.is_tie_breaker,TP.currency_type,IFNULL(TP.template_title,'') as contest_title,IFNULL(TP.sponsor_logo,'') as sponsor_logo,IFNULL(TP.sponsor_link,'') as sponsor_link,TP.is_scratchwin,TP.site_rake", FALSE);
        $this->db->from(COLLECTION_TEMPLATE." as CT");
        $this->db->join(CONTEST_TEMPLATE.' as TP', 'TP.contest_template_id = CT.contest_template_id', "INNER");
        $this->db->where('CT.collection_master_id', $collection_master_id);
        $this->db->where('TP.status',"1");
        $this->db->where('TP.group_id',$h2h_group_id);
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get collection master details by id
     * @param int $collection_master_id
     * @return array
    */
    public function get_h2h_team_detail($lineup_master_id)
    {
        $this->db->select('LM.lineup_master_id,LM.user_id,LM.user_name,CM.collection_master_id,CM.league_id,CM.collection_name,CM.season_scheduled_date',FALSE)
            ->from(LINEUP_MASTER.' LM')
            ->join(COLLECTION_MASTER.' CM','CM.collection_master_id = LM.collection_master_id')
            ->where('LM.lineup_master_id',$lineup_master_id);
        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
     * used to get user h2h contest joined count
     * @param array $post_data
     * @return array
     */
    public function get_user_h2h_contest_join_count($collection_master_id) {
        $result = $this->db->select("IFNULL(COUNT(LMC.lineup_master_contest_id), 0) as user_joined_count", FALSE)
                ->from(CONTEST . " C")
                ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.contest_id = C.contest_id AND LMC.fee_refund=0", "INNER")
                ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
                ->where('C.group_id',$this->h2h_group_id)
                ->where("C.collection_master_id", $collection_master_id)
                ->where("LM.user_id", $this->user_id)
                ->get()
                ->row_array();
        return $result;
    }

    /**
     * used to get opponent game data
     * @param array $post_data
     * @return array
    */
    public function check_opponent_game($post_data)
    {
        $this->db->select('C.contest_id,C.contest_unique_id,C.group_id,C.contest_name,C.collection_master_id,C.season_scheduled_date,C.minimum_size,C.size,C.total_user_joined,C.entry_fee,C.currency_type,C.prize_pool,C.prize_type,C.prize_distibution_detail,C.site_rake,C.max_bonus_allowed,C.is_auto_recurring,C.is_pin_contest,C.is_reverse,C.is_2nd_inning,C.multiple_lineup,C.status,LM.user_id,IFNULL(HU.total_win,0) as total_win,0 as deadline_time',FALSE)
            ->from(CONTEST.' C')
            ->join(LINEUP_MASTER_CONTEST.' LMC','LMC.contest_id = C.contest_id','LEFT')
            ->join(LINEUP_MASTER.' LM','LM.lineup_master_id = LMC.lineup_master_id','LEFT')
            ->join(H2H_USERS.' HU','HU.user_id = LM.user_id','LEFT')
            ->where('C.group_id',$this->h2h_group_id)
            ->where('C.contest_template_id',$post_data['contest_id'])
            ->where('C.collection_master_id',$post_data['collection_master_id'])
            ->where('C.status',0)
            ->where('C.total_user_joined != C.size')
            ->group_by("C.contest_id")
            ->having("total_win >= ",$post_data['min'])
            ->having("total_win <= ",$post_data['max'])
            ->having('!FIND_IN_SET('.$this->user_id.',GROUP_CONCAT(LM.user_id))');
        $result = $this->db->get()->row_array();
        //echo $this->db->last_query();die;
        return $result;
    }

    /**
     * used to get h2h contest joined users
     * @param array $post_data
     * @return array
     */
    public function get_h2h_contest_users($contest_id) {
        $result = $this->db->select("LM.user_id,LM.user_name,LM.team_name", FALSE)
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
                ->where('LMC.contest_id',$contest_id)
                ->where("LMC.fee_refund", "0")
                ->get()
                ->result_array();
        return $result;
    }

    /**
     * used for get user h2h joined contest list by collection id
     * @param int $collection_master_id
     * @return array
     */
    public function get_user_h2h_contest($collection_master_id) {
        $this->db->select("C.contest_id,C.contest_unique_id,C.group_id,IFNULL(C.contest_title,'') as contest_title,IFNULL(NULLIF(C.contest_title, ''),C.contest_name) as contest_name,C.collection_master_id,C.season_scheduled_date,C.minimum_size,C.size,C.total_user_joined,C.entry_fee,C.currency_type,C.prize_pool,C.prize_type,C.prize_distibution_detail,C.site_rake,LM.user_id,LM.team_name,IFNULL(LM1.user_id,0) as opp_user_id,IFNULL(LM1.team_name,'') as opp_team_name,IFNULL(HU.total,0) as total,IFNULL(HU.total_win,0) as total_win", false)
                ->from(CONTEST.' C')
                ->join(LINEUP_MASTER_CONTEST.' LMC', 'LMC.contest_id = C.contest_id', 'INNER')
                ->join(LINEUP_MASTER.' LM', 'LM.lineup_master_id = LMC.lineup_master_id', 'INNER')
                ->join(LINEUP_MASTER_CONTEST.' LMC1', 'LMC1.contest_id = C.contest_id AND LMC1.lineup_master_contest_id != LMC.lineup_master_contest_id', 'LEFT')
                ->join(LINEUP_MASTER.' LM1', 'LM1.lineup_master_id = LMC1.lineup_master_id AND LM1.user_id != '.$this->user_id, 'LEFT')
                ->join(H2H_USERS.' HU','HU.user_id = LM1.user_id','LEFT')
                ->where("C.group_id",$this->h2h_group_id)
                ->where("LM.user_id",$this->user_id)
                ->where("C.collection_master_id",$collection_master_id)
                ->group_by("C.contest_id");

        $result = $this->db->get()->result_array();
        return $result;
    }    
}