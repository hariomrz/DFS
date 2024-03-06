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
        $current_date_time = format_date();
        $publish_date = format_date('today');

        $contest_start_time = new DateTime(format_date('today', 'Y-m-d '.CONTEST_START_TIME));
        $contest_publish_time = new DateTime(format_date('today', 'Y-m-d '.CONTEST_PUBLISH_TIME));
        $interval = $contest_publish_time->diff($contest_start_time);
        $hour = $interval->format('%h');
        if($hour > 0) {
            $publish_date = date('Y-m-d H:i:s',strtotime($publish_date.' + '.$hour.'hour'));
        }
        
        
        
        $this->db->select("CM.category_id, CM.collection_id, CM.name as collection_name, CM.published_date, CM.scheduled_date, CM.end_date, C.prize_type, count(C.contest_id) as total_contest", FALSE);
        $this->db->select("SUM(CASE WHEN C.prize_type=0 THEN C.prize_pool WHEN C.prize_type=1 THEN C.prize_pool ELSE 0 END) AS total_prize_pool", FALSE);
        $this->db->select('IFNULL(CM.custom_message,"") as custom_message', FALSE);        
        $this->db->from(COLLECTION . " as CM");
        $this->db->join(CONTEST_CATEGORY . ' as CC', 'CC.category_id = CM.category_id', "INNER");
        $this->db->join(CONTEST . ' as C', 'C.collection_id = CM.collection_id AND C.status=0', "INNER");
     
        //$this->db->where("DATE_FORMAT(CM.published_date,'%Y-%m-%d') <= '".$publish_date."' and CM.scheduled_date > '".$current_date_time."' ");

        $this->db->where("CM.published_date <= '".$publish_date."' and CM.scheduled_date > '".$current_date_time."' ");
        
        $stock_type = $this->input->post('stock_type');
        if(empty($stock_type))
        {
            $stock_type = 1;
        }

        $this->db->where('CM.stock_type', $stock_type);
        $this->db->where('CM.status', '0');
        $this->db->group_by("CM.collection_id");
        $this->db->order_by("CM.category_id", 'ASC');
        $result= $this->db->get()->result_array();

        //echo $this->db->last_query();die;
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
        $result = $this->db->select("MAX(IF(max_prize_pool > prize_pool,max_prize_pool,prize_pool)) as max_prize_pool,MAX(entry_fee) as max_entry_fee,MAX(entry_fee) as max_entry_fee,MAX(size) as max_size", FALSE)
                ->from(CONTEST)
                ->where("collection_id", $post_data['collection_id'])
                ->where("status", "0")
                ->limit(1)
                ->get()
                ->row_array();

        $slider_option['winning']['max'] = (!empty($result['max_prize_pool'])) ? (int) $result['max_prize_pool'] : 10000;
        $slider_option['entry_fee']['max'] = (!empty($result['max_entry_fee'])) ? (int) $result['max_entry_fee'] : 10000;
        $slider_option['entries']['max'] = (!empty($result['max_size'])) ? (int) $result['max_size'] : 10000;
        return $slider_option;
    }

    /**
     * used to get match collection details
     * @param int $collection_master_id
     * @return array
    */
    public function get_fixture_details($collection_id){

        $this->db->select("CM.collection_id,CM.name as collection_name,CM.scheduled_date,CM.end_date,CM.custom_message",FALSE);
        $this->db->from(COLLECTION." as CM");        
        $this->db->where('CM.collection_id', $collection_id, FALSE);
        $this->db->limit(1);
        $result = $this->db->get()->row_array();
        return $result;
    }

     /**
     * used to get user joined team list
     * @param int $collection_id
     * @return array
    */
    public function get_all_user_lineup_list($collection_id) {
        $this->db->select("LM.lineup_master_id,LM.collection_id,LM.team_name,
	   	JSON_UNQUOTE(json_extract(LM.team_data, '$.c_id')) AS c_id,
        JSON_UNQUOTE(json_extract(LM.team_data, '$.vc_id')) AS vc_id,   
        count(LMC.lineup_master_contest_id) as total_joined", FALSE);
        $this->db->from(LINEUP_MASTER . ' LM');
        $this->db->join(LINEUP_MASTER_CONTEST . ' as LMC', 'LMC.lineup_master_id = LM.lineup_master_id', "LEFT");
        $this->db->where('LM.collection_id', $collection_id);
        $this->db->where('LM.user_id', $this->user_id);
        $this->db->group_by('LM.lineup_master_id');
        $this->db->order_by('LM.lineup_master_id', "ASC");
        return $this->db->get()->result_array();
    }

     /**
     * used to get lobby banner list
     * @param array $post_data
     * @return array
    */
    public function get_user_joined_contest_data($post_data) {
        if (!$post_data) {
            return false;
        }

        $this->db->select("LMC.contest_id,IFNULL(COUNT(LM.lineup_master_id), 0) as lm_count", FALSE);
        $this->db->from(LINEUP_MASTER . " as LM");
        $this->db->join(LINEUP_MASTER_CONTEST . ' as LMC', 'LMC.lineup_master_id = LM.lineup_master_id', "INNER");
        $this->db->where('LM.collection_id', $post_data['collection_id']);
        $this->db->where('LM.user_id', $post_data['user_id']);
        $this->db->group_by("LMC.contest_id");
        return $this->db->get()->result_array();
    }

    /**
     * used to get fixture contest list for Without login users, without private contests
     * @param array $post_data
     * @param int $group_id
     * @return array
    */
    public function get_collection_contests($post_data, $group_id = "") {
        $current_date = format_date();
        $collection_id = $post_data['collection_id'];
        if (!isset($post_data['sports_id'])) {
            $post_data['sports_id'] = 7;
        }
        $user_where = array(0);
        if($this->user_id != ""){
            $user_where[] = $this->user_id;
        }
     
        $this->db->select("C.contest_id,C.contest_unique_id,C.collection_id,C.group_id,C.category_id,C.entry_fee,C.size,C.minimum_size,C.max_bonus_allowed,C.scheduled_date,C.total_user_joined,C.prize_pool,C.guaranteed_prize,C.multiple_lineup,C.contest_access_type,C.prize_distibution_detail,C.prize_type,C.is_pin_contest,C.is_tie_breaker,C.currency_type,IFNULL(C.contest_title,'') as contest_title,IFNULL(C.sponsor_logo,'') as sponsor_logo,IFNULL(C.sponsor_link,'') as sponsor_link,CM.end_date,CM.name as collection_name", FALSE);
        $this->db->from(CONTEST . " as C");
        $this->db->join(COLLECTION . ' as CM', 'CM.collection_id = C.collection_id', "INNER");
        $this->db->where('C.status', 0);
        $this->db->where_in("C.user_id",$user_where);
        $this->db->where('C.size > C.total_user_joined',NULL);
        $this->db->where('C.collection_id', $collection_id);
        if (isset($group_id) && $group_id != "") {
            $this->db->where('C.group_id', $group_id);
        }   

        if (isset($post_data['pin_contest']) && $post_data['pin_contest'] == 1) {
            $this->db->where('C.is_pin_contest', 1);
        } else {
            $this->db->where('C.is_pin_contest', 0);
        }

        $this->db->where("C.scheduled_date > DATE_ADD('{$current_date}', INTERVAL 0 MINUTE)");
        $this->db->order_by("C.is_pin_contest", "DESC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get fixture contest list for Without login users, without private contests
     * @param array $post_data
     * @param int $group_id
     * @return array
    */
    public function get_fixture_contest($post_data) {
        $current_date = format_date();
        $collection_id = $post_data['collection_id'];
        if (!isset($post_data['sports_id'])) {
            $post_data['sports_id'] = 7;
        }
        $user_where = array(0);
        if($this->user_id != ""){
            $user_where[] = $this->user_id;
        }
     
        $this->db->select("C.contest_id,C.contest_unique_id,C.collection_id,C.group_id,C.category_id,C.entry_fee,C.size,C.minimum_size,C.max_bonus_allowed,C.scheduled_date,C.total_user_joined,C.prize_pool,C.guaranteed_prize,C.multiple_lineup,C.contest_access_type,C.prize_distibution_detail,C.prize_type,C.is_pin_contest,C.is_tie_breaker,C.currency_type,IFNULL(C.contest_title,'') as contest_title,IFNULL(C.sponsor_logo,'') as sponsor_logo,IFNULL(C.sponsor_link,'') as sponsor_link,CM.end_date,CM.name as collection_name", FALSE);
        $this->db->from(CONTEST . " as C");
        $this->db->join(COLLECTION . ' as CM', 'CM.collection_id = C.collection_id', "INNER");
        $this->db->where('C.status', 0);
        $this->db->where_in("C.user_id",$user_where);
        $this->db->where('C.size > C.total_user_joined',NULL);
        $this->db->where('C.collection_id', $collection_id);
        if(isset($post_data['h2h_group_id']) && $post_data['h2h_group_id'] != ""){
            $this->db->where('C.group_id != ', $post_data['h2h_group_id']);
        }
        if(isset($post_data['rookie_group_id']) && $post_data['rookie_group_id'] != ""){
            $this->db->order_by("FIELD(C.group_id,".$post_data['rookie_group_id'].")");
        }
        
        $this->db->where("C.scheduled_date > DATE_ADD('{$current_date}', INTERVAL 0 MINUTE)");
        $this->db->order_by("C.is_pin_contest", "DESC");
        $result = $this->db->get()->result_array();
        return $result;
    }
}
