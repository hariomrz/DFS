<?php

/**
 * Used for return fanbtasy db records
 * @package     Fantasy
 * @category    Fantasy
 */
class Fantasy_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->fantasy_db = $this->load->database('fantasy_db', TRUE);
    }

    function __destruct() {
        if (isset($this->fantasy_db->conn_id)) {
            $this->fantasy_db->close();
        }
    }

    /**
     * for get sports list
     * @param
     * @return array
     */
    public function get_sports_list($langs) {
        $lang_str = array();
        foreach ($langs as $lang) {
            $lang_str[] = "MSF.".$lang."_display_name as ".$lang;
        }
        $sel_str = "MS.team_player_count,MS.sports_id,MS.sports_name,".implode(",",$lang_str);
        return $this->fantasy_db->select($sel_str)
                        ->from(MASTER_SPORTS . " MS")
                        ->join(MASTER_SPORTS_FORMAT . " MSF", "MSF.sports_id=MS.sports_id")
                        ->where('MS.active', '1')
                        ->where('MSF.status', '1')
                        ->order_by("MS.order", "ASC")
                        ->get()
                        ->result_array();
    }

    function get_contest_won($user_id) {
        $this->fantasy_db->select("SUM(IF(LMC.is_winner=1,1,0)) as won_contest,COUNT(LMC.lineup_master_contest_id) as total_contest")
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(LINEUP_MASTER . " LM", "LMC.lineup_master_id = LM.lineup_master_id", "INNER")
                ->join(CONTEST . " C", "C.contest_id = LMC.contest_id", "INNER");
                
        $this->fantasy_db->where_in("C.status", array("2","3"));        
        $this->fantasy_db->where("LM.user_id", $user_id);
        $this->fantasy_db->limit(1);
        $won_result = $this->fantasy_db->get()->row_array();
       
        return $won_result;
    }

    function get_user_match_count($user_id) {

        $this->fantasy_db->select("COUNT(DISTINCT CM.collection_master_id) as match_count")
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(LINEUP_MASTER . ' LM', 'LM.lineup_master_id=LMC.lineup_master_id')
                //->join(CONTEST . " C", "LMC.contest_id = C.contest_id", "INNER")
                ->join(COLLECTION_MASTER . " CM", "CM.collection_master_id = LM.collection_master_id", "INNER")
                ->join(CONTEST.' AS C',"CM.collection_master_id = C.collection_master_id", "INNER");
        $this->fantasy_db->where("LM.user_id", $user_id);
        $this->fantasy_db->where("CM.status", 1);
        $this->fantasy_db->where("C.status", 3);
        $this->fantasy_db->where("LMC.fee_refund", 0);
        $this->fantasy_db->limit(1);
        $match_count = $this->fantasy_db->get()->row_array();
        // echo $this->fantasy_db->last_query();die;
        return $match_count;
    }

    function get_user_league_count($user_id) {
        $this->fantasy_db->select("COUNT(DISTINCT CM.league_id) as league_count")
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(LINEUP_MASTER . ' LM', 'LM.lineup_master_id=LMC.lineup_master_id')
                //->join(CONTEST . " C", "LMC.contest_id = C.contest_id", "INNER")
                ->join(COLLECTION_MASTER . " CM", "CM.collection_master_id = LM.collection_master_id", "INNER");
        $this->fantasy_db->where("LM.user_id", $user_id);
        $this->fantasy_db->where("CM.status",1);
        $this->fantasy_db->limit(1);
        $match_count = $this->fantasy_db->get()->row_array();
        return $match_count;
    }

    public function get_total_contest_won() {
        $this->fantasy_db->select("count(LM.lineup_master_id) as won_count")
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
        ;
        $this->fantasy_db->where("LM.user_id", $this->user_id);
        $this->fantasy_db->where("LM.is_winner", 1);
        $this->fantasy_db->limit(1);
        return $this->fantasy_db->get()->row_array();
    }

    function get_collection_by_lineup_master_contest_id($lineup_master_contest_id) {
        $this->fantasy_db->select("C.contest_name,C.contest_unique_id,CL.collection_name,C.contest_id,LMC.lineup_master_contest_id")
                ->from(LINEUP_MASTER_CONTEST . " LMC")
                ->join(CONTEST . " C", "LMC.contest_id = C.contest_id", "INNER")
                ->join(COLLECTION_MASTER . " CL", "CL.collection_master_id = C.collection_master_id", "INNER");

        if (is_array($lineup_master_contest_id)) {
            $this->fantasy_db->where_in("LMC.lineup_master_contest_id", $lineup_master_contest_id);
            return $this->fantasy_db->get()->result_array();
        } else {
            $this->fantasy_db->where("LMC.lineup_master_contest_id", $lineup_master_contest_id);
            $this->fantasy_db->limit(1);
            return $this->fantasy_db->get()->row_array();
        }
    }

    /**
     * check status of contest for scratch and wing claim
     */
    public function get_contest_status($contest_id)
    {
        $result = $this->fantasy_db->select('status')
        ->from(CONTEST)
        ->where('contest_id',$contest_id)
        ->where('status',0)
        ->where('is_scratchwin',1)
        ->get()->row_array();
        return ($result)? 1:0;
    }
    
    public function get_banner_collection($cm_ids){
        $this->fantasy_db->select("S.season_id,CS.collection_master_id,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away", FALSE)
                ->from(COLLECTION_SEASON . " AS CS")
                ->join(SEASON." as S", "S.season_id = CS.season_id", "INNER")
                ->join(TEAM.' T1','T1.team_id = S.home_id','INNER')
                ->join(TEAM.' T2','T2.team_id = S.away_id','INNER')
                ->where_in("CS.collection_master_id", $cm_ids)
                ->group_by("S.season_id");
        return $this->fantasy_db->get()->result_array();
    }

}
