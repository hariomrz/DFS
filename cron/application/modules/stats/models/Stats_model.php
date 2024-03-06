<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Stats_model extends MY_Model {
  public function __construct() 
  {
    parent::__construct();
    $this->db = $this->load->database('db_fantasy', TRUE);
  }

  /**
   * function used for return all match list 
   * @param array $data
   * @return boolean
  */
  public function match_list($sports_id,$status=1) {
    $current_date = format_date();
    $last_date_time = date("Y-m-d H:i:s",strtotime('-5 day',strtotime($current_date)));

    $this->db->select("CM.collection_master_id,CM.collection_name,CM.season_scheduled_date,CM.status,CM.is_lineup_processed",FALSE)
          ->from(COLLECTION_MASTER." AS CM")
          ->join(LEAGUE." L", "L.league_id = CM.league_id", "INNER")
          ->where("CM.season_game_count","1")
          ->where("L.sports_id",$sports_id);

    if($status == '0')
    {  
        $this->db->where("CM.status","0");
        $this->db->where("CM.season_scheduled_date >", $current_date);
        $this->db->order_by("CM.season_scheduled_date","ASC");
    }else if($status == '2')
    {  
        $this->db->where("CM.status","1");
        $this->db->where("CM.season_scheduled_date <= ", $current_date);
        $this->db->where("CM.season_scheduled_date >= ", $last_date_time);
        $this->db->order_by("CM.season_scheduled_date","DESC");
    }else{
      //live
      $this->db->where("CM.status","0");
      $this->db->where("CM.season_scheduled_date <= ", $current_date);
      $this->db->where("CM.season_scheduled_date >= ", $last_date_time);
      $this->db->order_by("CM.season_scheduled_date","DESC");
    }
    $this->db->limit("20");
    $match_list = $this->db->get()->result_array();
    return $match_list;
  }

  public function get_match_detail($collection_master_id,$league_id) {
     $this->db->select("S.season_id,S.season_game_uid,S.season_scheduled_date,S.home_id,S.away_id,S.score_data,S.status,S.status_overview,S.match_status,S.playing_announce,S.playing_list,S.delay_minute,S.delay_message,S.delay_by_admin,S.match_closure_date,S.substitute_list,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,T1.team_uid as home_uid,T2.team_uid as away_uid",FALSE)
         ->from(COLLECTION_SEASON." AS CS")
         ->join(SEASON." S", "S.season_id = CS.season_id", "INNER")
         ->join(TEAM.' T1','T1.team_id=S.home_id','INNER')
          ->join(TEAM.' T2','T2.team_id=S.away_id','INNER')
         ->where("CS.collection_master_id",$collection_master_id)
         ->where("S.league_id",$league_id);
    $match_info = $this->db->get()->row_array();
    return $match_info;
  }
}
