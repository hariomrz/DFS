<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Booster_model extends MY_Model{

  	function __construct()
  	{
	    parent::__construct();
		  $this->db_fantasy = $this->load->database('db_fantasy', TRUE);
	  }

    /**
    * Function used for get boosters list by sports
    * @param array $post_data
    * @return result array
    */
    public function get_booster_list($sports_id)
    {
      $this->db_fantasy->select('B.booster_id,B.position_id,B.name,B.display_name,B.image_name,B.points,B.status', FALSE)
            ->from(BOOSTER.' as B')
            ->where("B.sports_id",$sports_id)
            ->order_by("B.booster_id","ASC");

      $result = $this->db_fantasy->get()->result_array();
      return $result;
    }

    /**
    * Function used for update booster details
    * @param int $booster_id
    * @param array $data
    * @return result array
    */
    public function update_booster_by_id($booster_id,$data)
    {
      $this->db_fantasy->where('booster_id', $booster_id)
          ->update(BOOSTER, $data); 
      return $this->db_fantasy->affected_rows();
    }

    /**
     * used to get fixture booster list
     * @param array $collection_ids
     * @return array
    */
    public function get_fixture_apply_booster($cm_id,$sports_id) {
        $this->db_fantasy->select("B.booster_id,B.name,B.display_name,IFNULL(B.image_name,'') as image_name,IFNULL(BC.points,B.points) as points,IF(IFNULL(BC.id,'0') > 0, IFNULL(MLP1.position_name,'All'),IFNULL(MLP.position_name,'All')) as position,IF(IFNULL(BC.id,'0') > 0,1,0) as is_applied", FALSE)
                ->from(BOOSTER." AS B")
                ->join(MASTER_LINEUP_POSITION." as MLP", "MLP.master_lineup_position_id = B.position_id", "LEFT")
                ->join(BOOSTER_COLLECTION." as BC", "BC.booster_id = B.booster_id AND BC.collection_master_id='".$cm_id."'", "LEFT")
                ->join(MASTER_LINEUP_POSITION." as MLP1", "MLP1.master_lineup_position_id = BC.position_id", "LEFT")
                ->where("(B.status = 1 OR BC.id IS NOT NULL)")
                ->where('B.sports_id', $sports_id, FALSE)
                ->order_by("is_applied","DESC")
                ->order_by("B.booster_id","ASC");
        $result = $this->db_fantasy->get()->result_array();
        return $result;
    }

    /**
    * Function used for get boosters list by sports
    * @param array $post_data
    * @return result array
    */
    public function check_fixture_booster_exist($where)
    {
      if(empty($where)){
        return false;
      }

      $this->db_fantasy->select('*', FALSE)
            ->from(BOOSTER_COLLECTION)
            ->where($where);
      $result = $this->db_fantasy->get()->row_array();
      return $result;
    }

    /**
    * Function used for save match booster
    * @param array $data
    * @return result array
    */
    public function save_fixture_booster($data)
    {
      $this->db_fantasy->insert(BOOSTER_COLLECTION,$data);
      return $this->db_fantasy->insert_id();
    }

    /**
     * used to get booster details
     * @param int $booster_id
     * @return array
    */
    public function get_booster_detail($booster_id) {
        $this->db_fantasy->select("B.booster_id,B.position_id,IFNULL(B.display_name,B.name) as name,IFNULL(B.image_name,'') as image_name,B.points,IFNULL(MLP.position_name,'All') as position", FALSE)
                ->from(BOOSTER." AS B")
                ->join(MASTER_LINEUP_POSITION." as MLP", "MLP.master_lineup_position_id = B.position_id", "LEFT")
                ->where("B.booster_id",$booster_id);
        $result = $this->db_fantasy->get()->row_array();
        return $result;
    }

    /**
     * used to get booster details
     * @param int $booster_id
     * @return array
    */
    public function get_match_booster_detail($booster_id,$collection_master_id) {
        $this->db_fantasy->select("B.booster_id,B.position_id,IFNULL(B.display_name,B.name) as name,IFNULL(B.image_name,'') as image_name,BC.points,IFNULL(MLP.position_name,'All') as position", FALSE)
                ->from(BOOSTER_COLLECTION." AS BC")
                ->join(BOOSTER." as B", "B.booster_id = BC.booster_id", "INNER")
                ->join(MASTER_LINEUP_POSITION." as MLP", "MLP.master_lineup_position_id = BC.position_id", "LEFT")
                ->where("BC.booster_id",$booster_id)
                ->where("BC.collection_master_id",$collection_master_id);
        $result = $this->db_fantasy->get()->row_array();
        return $result;
    }
}
