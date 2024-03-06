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
    public function get_sports_list() {
        return $this->fantasy_db->select('MS.sports_id,MSF.en_display_name,MSF.hi_display_name,MSF.guj_display_name,MS.sports_name,MS.team_player_count,MS.max_player_per_team')
                        ->from(MASTER_SPORTS . " MS")
                        ->join(MASTER_SPORTS_FORMAT . " MSF", "MSF.sports_id=MS.sports_id")
                        ->where('MS.active', '1')
                        ->where('MSF.status', '1')
                        ->order_by("MS.order", "ASC")
                        ->get()
                        ->result_array();
    }

    /**
     * for get banner detail by id
     * @param int $banner_type_id
     * @return array
     */
    public function get_banner_detail_by_id($banner_type_id) {
        return $this->fantasy_db->select('banner_id,banner_type_id,name,target_url,image,collection_master_id,status')
                        ->from(BANNER_MANAGEMENT)
                        ->where('is_deleted', '0')
                        ->where('banner_type_id', $banner_type_id)
                        ->limit(1)
                        ->get()
                        ->row_array();
    }

    /**
     * used to get lobby banner list
     * @param array $post_data
     * @return array
     */
    public function get_lobby_banner_list() {

        $this->fantasy_db->select("BM.banner_type_id,BM.name,BM.target_url,BM.image", FALSE)
                ->from(BANNER_MANAGEMENT . " AS BM")
                ->join(BANNER_TYPE . " as BT", "BT.banner_type_id = BM.banner_type_id", "INNER")
                ->where("BM.is_deleted", "0")
                ->where("BM.status", "1")
                ->where("BM.banner_type_id", 4)
                ->group_by("BM.banner_id");

        $this->fantasy_db->order_by("BM.banner_type_id");
        $this->fantasy_db->order_by("BM.banner_id", "ASC");
        return $this->fantasy_db->get()->result_array();
    }

}
