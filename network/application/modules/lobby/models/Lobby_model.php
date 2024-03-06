<?php

class Lobby_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * used to get fixture contest list
     * @param array $post_data
     * @param int $group_id
     * @return array
    */
    public function get_collection_network_contests($post_data) {
        $current_date = format_date();
        $collection_master_id = $post_data['collection_master_id'];
       

        $this->db->select("GROUP_CONCAT(NC.network_contest_id) AS contest_ids,NC.network_collection_master_id as collection_master_id", FALSE);
        $this->db->from(NETWORK_CONTEST . " as NC");
        $this->db->join(COLLECTION_MASTER .' as CM', 'CM.collection_master_id = NC.collection_master_id', "INNER");
        $this->db->where('NC.active', 1);
        $this->db->where('NC.collection_master_id', $collection_master_id);
        $this->db->where("CM.season_scheduled_date > DATE_ADD('{$current_date}', INTERVAL CM.deadline_time MINUTE)");
        $this->db->group_by("NC.collection_master_id");
        return $this->db->get()->row_array();
    }

    
}
