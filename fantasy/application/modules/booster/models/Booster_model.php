<?php
class Booster_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * used to get collection booster list
     * @param array $collection_ids
     * @return array
    */
    public function get_lobby_collection_booster($collection_ids) {
        $this->db->select("BC.collection_master_id,GROUP_CONCAT(IFNULL(B.display_name,B.name) SEPARATOR ', ') as name", FALSE)
                ->from(BOOSTER_COLLECTION." AS BC")
                ->join(BOOSTER." as B", "B.booster_id = BC.booster_id", "INNER")
                ->where_in("BC.collection_master_id",$collection_ids)
                ->group_by("BC.collection_master_id")
                ->order_by("B.booster_id","ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get booster list
     * @param int $sports_id
     * @return array
    */
    public function get_booster_list($sports_id) {
        $this->db->select("B.booster_id,IFNULL(B.display_name,B.name) as name,IFNULL(B.image_name,'') as image_name,B.points,IFNULL(MLP.position_display_name,'All') as position", FALSE)
                ->from(BOOSTER." AS B")
                ->join(MASTER_LINEUP_POSITION." as MLP", "MLP.master_lineup_position_id = B.position_id", "LEFT")
                ->where("B.sports_id",$sports_id)
                ->where("B.status", "1")
                ->order_by("B.booster_id","ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * used to get collection booster list
     * @param int $collection_master_id
     * @return array
    */
    public function get_collection_booster($collection_master_id) {
        $this->db->select("B.booster_id,IFNULL(B.display_name,B.name) as name,IFNULL(B.image_name,'') as image_name,BC.points,IFNULL(MLP.position_name,'All') as position", FALSE)
                ->from(BOOSTER_COLLECTION." AS BC")
                ->join(BOOSTER." as B", "B.booster_id = BC.booster_id", "INNER")
                ->join(MASTER_LINEUP_POSITION." as MLP", "MLP.master_lineup_position_id = BC.position_id", "LEFT")
                ->where("BC.collection_master_id",$collection_master_id)
                ->order_by("B.booster_id","ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * Used to get booster details
     * @param int $booster_id
     * @return array
    */
    public function get_match_booster_detail($cm_id,$booster_ids) {
        $this->db->select("B.booster_id,IFNULL(B.display_name,B.name) as name,IFNULL(B.image_name,'') as image_name,BC.points,BC.position_id", FALSE)
                ->from(BOOSTER_COLLECTION." AS BC")
                ->join(BOOSTER." as B", "B.booster_id = BC.booster_id", "INNER")
                ->where("BC.collection_master_id",$cm_id)
                ->where_in("B.booster_id",$booster_ids);
        $result = $this->db->get()->result_array();
        return $result;
    }    
}
