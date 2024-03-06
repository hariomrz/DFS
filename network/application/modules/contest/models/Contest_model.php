<?php

class Contest_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    

    /**
     * used for get user joined fixture list
     * @param array $post_data
     * @return array
     */
    public function check_collection_exist($post_data) 
    {
       $current_date = format_date();
        $this->db->select("CM.collection_master_id,CM.season_scheduled_date,CM.league_id,CM.season_game_count,CM.collection_name,CS.season_game_uid", false)
                ->from(COLLECTION_MASTER . ' CM')
                ->join(COLLECTION_SEASON . ' CS', 'CM.collection_master_id = CS.collection_master_id', 'INNER')
                ->where('CM.season_game_count',1)
                ->where('CM.league_id',$post_data['league_id'])
                ->where('CS.season_game_uid',$post_data['season_game_uid'])
                ->where('CM.status',0)
                ->where('CS.season_scheduled_date',$post_data['season_scheduled_date'])
                ->where("CM.season_scheduled_date >= DATE_ADD('{$current_date}', INTERVAL CM.deadline_time MINUTE)")
                ->where('CM.season_game_count',1);

        $result = $this->db->get()->row_array();
        //echo $this->db->last_query();die;
        return $result;
    }


    public function check_league_exist($post_data)
    {
        $league_detail = $this->db->select("league_id,league_uid")
                ->from(LEAGUE)
                ->where('league_uid', $post_data['league_uid'])
                ->where('sports_id', $post_data['sports_id'])
                ->get()->row_array();
        return $league_detail;
    }


    public function save_network_collection($collection_arr,$contest_arr)
    {
        
        $this->db->insert(COLLECTION_MASTER,$collection_arr);
        $collection_master_id = $this->db->insert_id();

        if($collection_master_id)
        {
            $collection_season_data = array();
            $collection_season_data['collection_master_id'] = $collection_master_id;
            $collection_season_data['season_game_uid'] = $contest_arr['season_game_uid'];
            $collection_season_data['season_scheduled_date'] = $contest_arr['season_scheduled_date'];
            $collection_season_data['added_date'] =format_date();
            $collection_season_data['modified_date'] = format_date();
            $this->db->insert(COLLECTION_SEASON,$collection_season_data);
        }

        return $collection_master_id;
    }

    public function save_network_contest($insert_arr)
    {
        $this->db->insert(NETWORK_CONTEST,$insert_arr);
        $contest_id = $this->db->insert_id();
        return $contest_id;
    }    

     /**
     * used to get contest details
     * @param array $post_data
     * @return array
     */
    public function get_network_contest_detail($post_data) {
        $sql = $this->db->select("NC.*", FALSE)
                ->from(NETWORK_CONTEST . " AS NC")
                ->join(COLLECTION_MASTER . " AS CM", 'CM.collection_master_id = NC.collection_master_id', 'INNER');

        
        if (isset($post_data['contest_id']) && $post_data['contest_id'] != "") {
            $this->db->where('NC.network_contest_id', $post_data['contest_id']);
        }
        $result = $sql->get()->row_array();
        return $result;
    }

     public function get_data_for_update()
    {
         $rs = $this->db->query("SELECT a.id,a.network_collection_master_id,a.collection_master_id,b.league_id,c.league_display_name,c.sports_id FROM `vi_network_contest` a INNER JOIN vi_collection_master as b on a.collection_master_id= b.collection_master_id INNER JOIN vi_league as c on c.league_id=b.league_id WHERE a.collection_master_id is not null ");
         return $rs->result_array(); 
    }

    /**
    *@method update_network_contest_details
    *@uses this function used for publish/update network contest
    **/
    public function update_network_contest_details($update_data,$where_data)
    {
        if(empty($update_data) || empty($where_data)){
            return false;
        }
        $this->db->where($where_data);
        $this->db->update(NETWORK_CONTEST,$update_data);

        return true;
    }


    public function save_network_lineup_master($insert_arr)
    {
        $this->db->insert(NETWORK_LINEUP_MASTER,$insert_arr);
        $lm_id = $this->db->insert_id();
        return $lm_id;
    }

     /**
     * used for get user total invested amount and and check it with self exlusion limit
     * @param int $user_id
     * @return boolean
     */
    public function user_join_contest_ids($user_id) 
    {
        $current_date = format_date("today", "Y-m");
        $this->db->select("GROUP_CONCAT(DISTINCT C.contest_id) as contest_ids")
                ->from(LINEUP_MASTER . " LM")
                ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.lineup_master_id = LM.lineup_master_id", "INNER")
                ->join(CONTEST . " C", "C.contest_id = LMC.contest_id", "INNER")
                ->where("LM.user_id", $user_id);
        $this->db->where("DATE_FORMAT(C.season_scheduled_date,'%Y-%m')", $current_date);
        $result = $this->db->get()->row_array();
        $contest_ids_arr = array();
        if(!empty($result)) {
            $contest_ids = $result['contest_ids'];
            $contest_ids_arr = explode(',', $contest_ids);
        }
        return $contest_ids_arr;
    }


    public function get_network_lineup_master_ids($lineup_master_ids)
    {
        $this->db->select('GROUP_CONCAT(network_lineup_master_id) AS network_lineup_master_id')
                ->from(NETWORK_LINEUP_MASTER);
        $this->db->where_in('lineup_master_id',$lineup_master_ids);
        $result = $this->db->get()
                   ->row_array();
        return $result;
    }

    public function get_contest_auto_publish($sports_id) 
    {
       $current_date = format_date();
        $this->db->select("NC.id,NC.network_contest_id,NC.sports_id,NC.league_id", false)
                ->from(NETWORK_CONTEST . ' NC')
                ->where('NC.sports_id',$sports_id)
                ->where('NC.active',0)
                ->where('NC.season_scheduled_date > ',$current_date)
                ->order_by('NC.id','ASC');
        $result = $this->db->get()->result_array();
        //echo $this->db->last_query();die;
        return $result;
    }

    /**
     * used for save contest invite code
     * @param array $invites
     * @return boolean
     */
    public function save_invites($invites) 
    {
        $this->db->insert_batch(INVITE, $invites);
        return TRUE;
    }

    /**
     * used for generate contest promo code
     * @return string
     */
    public function _generate_contest_code() 
    {
        $this->load->helper('security');

        do {
            $salt = do_hash(time() . mt_rand());
            $new_key = substr($salt, 0, 6);
            $new_key = strtoupper($new_key);
        }
        // Already in the DB? Fail. Try again
        while (self::_contest_code_exists($new_key));
        return $new_key;
    }

    /**
     * used for check contest code exist or not
     * @param string $key
     * @return boolean
     */
    private function _contest_code_exists($key) 
    {
        $this->db->select('invite_id');
        $this->db->where('code', $key);
        $this->db->limit(1);
        $query = $this->db->get(INVITE);
        $num = $query->num_rows();
        if ($num > 0) {
            return true;
        }
        return false;
    }


}
