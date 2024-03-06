<?php

class Leaderboard_model extends MY_Model {

	public function __construct() {
		parent::__construct();
	}

	public function get_leaderboard_type() {
		$this->db->select("category_id,name")
                ->from(LEADERBOARD_CATEGORY)
                ->where("status","1")
                ->order_by("display_order","ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
    * Function used for get category wise leaderboard name
    * @param array $post_data
    * @return string
    */
    public function get_category_leaderboard($post_data){
        $offset = 0;
        $limit = isset($post_data['limit']) ? $post_data['limit'] : "100";
       
        $this->db->select("(CASE 
                                WHEN LP.category_id = '".$this->fantasy_category."' AND LP.type=3 THEN 'Fantasy Leaderboard'
                                WHEN LP.category_id = '".$this->stock_category."' AND (LP.type=2 OR LP.type=3) THEN 'Stock Leaderboard'
                                WHEN LP.category_id = '".$this->stock_equity_category."' AND (LP.type=2 OR LP.type=3) THEN 'Stock Equity Leaderboard'
                                WHEN LP.category_id = '".$this->stock_predict_category."' AND (LP.type=2 OR LP.type=3) THEN 'Stock Predict Leaderboard'
                                WHEN LP.category_id = '".$this->live_stock_fantasy_category."' AND (LP.type=2 OR LP.type=3) THEN 'Live Stock Fantasy Leaderboard'
                                ELSE L.name
                            END) AS name
                        ");
        $this->db->select("LP.type,L.leaderboard_id, L.start_date,L.end_date,L.status,IF(IFNULL(LH.history_id,0) > 0,1,0) as is_joined",FALSE)
                ->from(LEADERBOARD_PRIZE." AS LP")
                ->join(LEADERBOARD." AS L", "L.prize_id = LP.prize_id", "INNER")
                ->join(LEADERBOARD_HISTORY." AS LH", "LH.leaderboard_id = L.leaderboard_id AND LH.user_id = '".$this->user_id."'", "LEFT")
                ->where("LP.category_id",$post_data['category_id'])
                ->where("LP.type",$post_data['type'])
                ->where("LP.status","1")
                ->where("L.status != ","1")
                ->order_by("LP.reference_id","ASC")
                ->order_by("LP.prize_id","DESC")
                ->order_by("L.prize_date","DESC")
                ->group_by("L.leaderboard_id")
                ->limit($limit,$offset);

        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
    * Function used for get leaderboard details
    * @param int $leaderboard_id
    * @return string
    */
    public function get_leaderboard_details($leaderboard_id){
        if(!$leaderboard_id){
            return false;
        }

        $this->db->select("L.leaderboard_id,L.name,L.status,L.start_date,L.end_date,IF(L.status=3,L.prize_detail,LP.prize_detail) as prize_detail,LP.category_id,LP.type",FALSE)
                ->from(LEADERBOARD_PRIZE." AS LP")
                ->join(LEADERBOARD." AS L", "L.prize_id = LP.prize_id", "INNER")
                ->where("L.status != ",1)
                ->where("L.leaderboard_id",$leaderboard_id);

        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
    * Function used for get leaderboard users list
    * @param array $post_data
    * @return string
    */
    public function get_leaderboard_list($post_data){
        $page_no = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit = isset($post_data['limit']) ? $post_data['limit'] : 20;
        $offset = get_pagination_offset($page_no, $limit);
        
        $this->db->select("LH.history_id,IFNULL(U.user_name,CONCAT(SUBSTRING(U.phone_no,1,4),'XXXX',SUBSTRING(U.phone_no,9,2))) as user_name,IFNULL(image,'') as image,LH.total_value,LH.rank_value,LH.is_winner,IF(LH.user_id='".$this->user_id."',1,0) as is_current,IFNULL(JSON_UNQUOTE(json_extract(LH.prize_data, '$[0].prize_type')),'') AS prize_type,IFNULL(JSON_UNQUOTE(json_extract(LH.prize_data, '$[0].amount')),'') AS amount",FALSE)
                ->from(LEADERBOARD_HISTORY." AS LH")
                ->join(USER." AS U", "U.user_id = LH.user_id", "INNER")
                ->where("LH.leaderboard_id",$post_data['leaderboard_id'])
                ->order_by("FIELD(LH.user_id,'".$this->user_id."') DESC")
                ->order_by("LH.rank_value","ASC")
                ->limit($limit,$offset);

        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
    * Function used for get leaderboard user details
    * @param array $post_data
    * @return string
    */
    public function get_user_leaderboard_detail($history_id){
        $this->db->select("LH.history_id,IFNULL(U.user_name,CONCAT(SUBSTRING(U.phone_no,1,4),'XXXX',SUBSTRING(U.phone_no,9,2))) as user_name,IFNULL(image,'') as image,LH.total_value,LH.rank_value,LH.is_winner,L.entity_no,L.start_date,L.end_date,LH.custom_data,LP.type",FALSE)
                ->from(LEADERBOARD_HISTORY." AS LH")
                ->join(LEADERBOARD." AS L", "L.leaderboard_id = LH.leaderboard_id", "INNER")
                ->join(LEADERBOARD_PRIZE." AS LP", "LP.prize_id = L.prize_id", "INNER")
                ->join(USER." AS U", "U.user_id = LH.user_id", "INNER")
                ->where("LH.history_id",$history_id);

        $result = $this->db->get()->row_array();
        return $result;
    }

    /**
    * Function used for get user match list
    * @param array $post_data
    * @return string
    */
    public function get_user_match_list($post_data){
        $this->fantasy_db = $this->load->database('fantasy_db', TRUE);
        //get match and collection
        $this->fantasy_db->select("CONCAT(IFNULL(T1.display_team_abbr,T1.team_abbr),' vs ',IFNULL(T2.display_team_abbr,T2.team_abbr)) as name,S.season_scheduled_date as date,L.league_display_name as league,LM.team_name as team,LMC.total_score as score,LMC.lineup_master_contest_id as lmc_id,LM.lineup_master_id as lm_id,L.sports_id", FALSE)
                    ->from(LINEUP_MASTER.' AS LM')
                    ->join(LINEUP_MASTER_CONTEST.' AS LMC', 'LMC.lineup_master_id = LM.lineup_master_id')
                    ->join(COLLECTION_SEASON.' AS CS', 'CS.collection_master_id = LM.collection_master_id')
                    ->join(SEASON.' S','S.season_id = CS.season_id','INNER')
                    ->join(TEAM.' T1','T1.team_id = S.home_id','INNER')
                    ->join(TEAM.' T2','T2.team_id = S.away_id','INNER')
                    ->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
                    ->where("S.status","2")
                    //->where("S.status_overview","4")
                    ->where("LMC.fee_refund","0")
                    ->where_in("LM.lineup_master_id",$post_data['custom_data'])
                    ->where("S.season_scheduled_date >=",$post_data['start_date'])
                    ->where("S.season_scheduled_date <=",$post_data['end_date']);
        if(isset($post_data['league_id']) && $post_data['league_id'] > 0){
            $this->fantasy_db->where('S.league_id', $post_data['league_id']);
        }
        $this->fantasy_db->group_by("S.season_game_uid");
        $result = $this->fantasy_db->get()->result_array();
        return $result;
    }

     public function get_user_match_list_predict($post_data){
        //get match and collection
         $this->db_stock    = $this->load->database('stock_db',TRUE);
        $this->db_stock->select("C.name, C.scheduled_date as date, LM.team_name as team, LMC.total_score as score,LMC.percent_change as accuracy_percent, LMC.lineup_master_contest_id as lmc_id", FALSE)
                    ->from(LINEUP_MASTER.' AS LM')
                    ->join(LINEUP_MASTER_CONTEST.' AS LMC', 'LMC.lineup_master_id = LM.lineup_master_id')
                    ->join('vi_collection'.' AS C', 'C.collection_id = LM.collection_id')
                    ->where("C.status", 1)
                    ->where("LMC.fee_refund",0)
                    ->where_in("LM.lineup_master_id",$post_data['custom_data'])
                    ->where("C.scheduled_date >=",$post_data['start_date'])
                    ->where("C.scheduled_date <=",$post_data['end_date']);
        
        $this->db_stock->group_by("C.collection_id");
        $result = $this->db_stock->get()->result_array();
        return $result; 
    }
}