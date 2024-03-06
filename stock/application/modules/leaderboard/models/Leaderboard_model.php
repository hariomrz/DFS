<?php

class Leaderboard_model extends MY_Model {

    public $db_user;
	public function __construct() {
		parent::__construct();
        $this->db_user = $this->load->database('user_db',TRUE);
	}


    /**
    * Function used for get leaderboard user details
    * @param array $post_data
    * @return string
    */
    public function get_user_leaderboard_detail($history_id){
        
        $this->db_user->select("LH.history_id, IFNULL(U.user_name, CONCAT(SUBSTRING(U.phone_no,1,4),'XXXX',SUBSTRING(U.phone_no,9,2))) as user_name,IFNULL(image,'') as image, LH.total_value,LH.rank_value, LH.is_winner, L.entity_no, L.start_date, L.end_date, LH.custom_data,LP.type",FALSE)
                ->from(LEADERBOARD_HISTORY." AS LH")
                ->join(LEADERBOARD." AS L", "L.leaderboard_id = LH.leaderboard_id", "INNER")
                ->join(LEADERBOARD_PRIZE." AS LP", "LP.prize_id = L.prize_id", "INNER")
                ->join(USER." AS U", "U.user_id = LH.user_id", "INNER")
                ->where("LH.history_id",$history_id);

        $result = $this->db_user->get()->row_array();
        return $result;
    }

    /**
    * Function used for get user match list
    * @param array $post_data
    * @return string
    */
    public function get_user_match_list($post_data){
        //get match and collection
        $this->db->select("C.name, C.scheduled_date as date,C.end_date, LM.team_name as team, LMC.total_score as score,LMC.percent_change as accuracy_percent, LMC.lineup_master_contest_id as lmc_id,LM.collection_id,LMC.contest_id", FALSE)
                    ->from(LINEUP_MASTER.' AS LM')
                    ->join(LINEUP_MASTER_CONTEST.' AS LMC', 'LMC.lineup_master_id = LM.lineup_master_id')
                    ->join(COLLECTION.' AS C', 'C.collection_id = LM.collection_id')
                    ->where("C.status", 1)
                    ->where("LMC.fee_refund",0)
                    ->where_in("LM.lineup_master_id",$post_data['custom_data'])
                    ->where("C.scheduled_date >=",$post_data['start_date'])
                    ->where("C.scheduled_date <=",$post_data['end_date']);
        
        $this->db->group_by("C.collection_id");
        $result = $this->db->get()->result_array();
        return $result;
    }
}