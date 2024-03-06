<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Dfs_model extends MY_Model {
  public $db_user ;
  public $db_fantasy ;
  public function __construct() 
  {
   	parent::__construct();
    $this->db_user = $this->load->database('db_user', TRUE);
    $this->db_fantasy	= $this->load->database('db_fantasy', TRUE);
  }

  /*
   * used for get master position list
   * @param int $sports_id
   * @return array
  */
  public function get_master_position($sports_id)
  {
      $sql = $this->db_fantasy->select('master_lineup_position_id,position_name as position, position_name, position_display_name,number_of_players,max_player_per_position,position_order')
          ->from(MASTER_LINEUP_POSITION)
          ->where('sports_id',$sports_id) 
          ->order_by('position_order','ASC');
      if(!in_array($sports_id, array(1))){
          $sql->where('position_name = allowed_position'); // to avoid FLEX position
      }
      $sql = $sql->get();
      $result = $sql->result_array();
      return ($result) ? $result : array();
  }

  /**
   * @Summary: This function for send notification to user in case of game delay and cancel.     
   * @return:
   */
  public function game_abandoned() {
    $current_date = format_date();
    // Status: 0-Not Started, 1-Live, 2-Completed, 3-Postponed
    // Status Overview: 0-Scheduled, 1-Rain Delay/Suspended, 2-Abandoned, 3-Canceled, 4-Result
    $season_list = $this->db_fantasy->select("S.season_id,S.season_game_uid,S.league_id,S.season_scheduled_date,S.status,S.status_overview,S.notification_sent,CM.collection_master_id,CM.status as cm_status")
           ->from(SEASON." AS S")
           ->join(COLLECTION_SEASON." AS CS", "CS.season_id = S.season_id", 'INNER')
           ->join(COLLECTION_MASTER." AS CM", "CM.collection_master_id = CS.collection_master_id", 'INNER')
           ->where("CM.status",0)
           ->where_in("S.status",[0,1,2])
           ->where_in("S.status_overview",[2,3])
           ->where_in("S.notification_sent",[0,2])
           ->where("S.season_scheduled_date < ", $current_date)
           ->group_by("CM.collection_master_id")
           ->order_by("S.season_scheduled_date","DESC")
           ->get()
           ->result_array();
    //echo "<pre>";print_r($season_list);die;
    $cancelled_contest_ids = array();
    if(!empty($season_list)){
        foreach($season_list as $season) 
        {
          // In Case of Game Abandoned and Canceled
          if(in_array($season['status_overview'],[2,3])){
            $cm_id = $season['collection_master_id'];
            $cancel_season = $this->db_fantasy->select("COUNT(DISTINCT CS.season_id) as total,COUNT(S.season_id) as total_cancel")
                                    ->from(COLLECTION_SEASON." CS")
                                    ->join(SEASON." AS S", "S.season_id = CS.season_id AND S.status_overview IN(2,3)", 'LEFT')
                                    ->where("CS.collection_master_id",$cm_id)
                                    ->get()
                                    ->row_array();
            //echo "<pre>====";print_r($cancel_season);die;
            if(!empty($cancel_season) && $cancel_season['total'] == $cancel_season['total_cancel']){
              $contest_list = $this->db_fantasy->select("C.contest_id,C.contest_unique_id,C.collection_master_id,C.sports_id,C.contest_name,C.size,C.entry_fee,C.prize_pool,C.prize_type,C.currency_type")
                              ->from(CONTEST." C")
                              ->where("C.collection_master_id", $cm_id)
                              ->where("C.status", 0)
                              ->get()
                              ->result_array();
              $additional_data = array();
              $additional_data['cancel_reason'] = 'Match cancellation on field';
              $additional_data['current_date'] = $current_date;
              $additional_data['notification_type'] = 23;
              $additional_data['subject'] = "Oops! Contest has been Cancelled";
              foreach($contest_list as $contest){
                $cancelled_contest_ids[] = $contest['contest_id'];
                if($contest['entry_fee'] > 0){
                  $this->process_game_cancellation($contest, $additional_data);
                }else{
                  //update for free contest
                  $this->db_fantasy->where("contest_id",$contest['contest_id']);
                  $this->db_fantasy->update(LINEUP_MASTER_CONTEST, array('fee_refund' => 1));
                  
                  // Game table update cancel status
                  $this->db_fantasy->where('contest_id', $contest['contest_id']);
                  $this->db_fantasy->update(CONTEST, array('status' => 1, 'entry_fee' => 0, 'modified_date' => $current_date, 'cancel_reason' => $additional_data['cancel_reason']));
                }
              }
              //Update Fixture notification status.
              $this->db_fantasy->where('season_id', $season['season_id']);
              $this->db_fantasy->where('league_id', $season['league_id']);
              $this->db_fantasy->update(SEASON, array("notification_sent" => 3));

              //update collection status
              $this->db_fantasy->where('collection_master_id', $cm_id);
              $this->db_fantasy->update(COLLECTION_MASTER, array('status' => 1, 'modified_date' => $current_date));

              //match cancel notification
              $cancel_data = array("notification_type"=>$additional_data['notification_type'],"cancel_reason"=>$additional_data['cancel_reason'],"collection_master_id"=>$cm_id);
              $this->match_cancel_notification($cancel_data);
            }

            //if not match created only send notification update
            $this->db_fantasy->where('season_id', $season['season_id']);
            $this->db_fantasy->where('league_id', $season['league_id']);
            $this->db_fantasy->update(SEASON, array("notification_sent" => 3));
          }
        }
    }
    if(!empty($cancelled_contest_ids))
    {
        echo 'Contest(s) ('.implode(', ', $cancelled_contest_ids).') has been cancelled due to fixture cancellation on field.';
    }
    else{
        echo 'No contest found to cancel.';
    }
  }

  public function contest_rescheduled()
  {
    $this->db_fantasy->select("CM.collection_master_id,CM.league_id,CM.season_scheduled_date,IFNULL(CM.2nd_inning_date,'') as 2nd_inning_date,CS.season_id,S.season_scheduled_date as match_date,CS.season_id,L.sports_id,S.format")
        ->from(COLLECTION_MASTER. " CM")
        ->join(COLLECTION_SEASON. " CS","CS.collection_master_id = CM.collection_master_id AND CM.season_scheduled_date = CS.season_scheduled_date")
        ->join(LEAGUE." as L","L.league_id = CM.league_id")
        ->join(SEASON." as S","S.season_id = CS.season_id")
        ->where("S.season_scheduled_date != CS.season_scheduled_date")
        ->where("CM.season_scheduled_date > ", format_date());
    $query = $this->db_fantasy->get();
    $collection_list = $query->result_array();
    //echo "<pre>";print_r($collection_list);die;
    if(!empty($collection_list))
    {
      $allow_2nd_inning = isset($this->app_config['allow_2nd_inning']) ? $this->app_config['allow_2nd_inning']['key_value'] : 0;
      foreach($collection_list as $row){
        $sports_id = $row['sports_id'];
        $cm_id = $row['collection_master_id'];
        $season_id = $row['season_id'];
        $season_scheduled_date = $row['season_scheduled_date'];
        $second_inning_date = $row['2nd_inning_date'];
        $match_date = $row['match_date'];
        if($season_scheduled_date != $match_date){
          if($allow_2nd_inning == 1 && in_array($row['format'],array(1,3))){
              $second_inning_interval = second_inning_game_interval($row['format']);
              $second_inning_date = date("Y-m-d H:i:s",strtotime($match_date.' +'.$second_inning_interval.' minutes'));
          }
          //Update collection master table
          $this->db_fantasy->set("season_scheduled_date",$match_date);
          $this->db_fantasy->set("2nd_inning_date",$second_inning_date);
          $this->db_fantasy->where("status",0);
          $this->db_fantasy->where("collection_master_id",$cm_id);
          $this->db_fantasy->update(COLLECTION_MASTER); 

          //Update collection season table
          $this->db_fantasy->set("season_scheduled_date",$match_date);
          $this->db_fantasy->where("season_id",$season_id);
          $this->db_fantasy->where("collection_master_id",$cm_id);
          $this->db_fantasy->update(COLLECTION_SEASON);

          //Update Contest table
          $this->db_fantasy->set("season_scheduled_date",$match_date);
          $this->db_fantasy->where("status",0);
          $this->db_fantasy->where("is_2nd_inning",0);
          $this->db_fantasy->where("collection_master_id",$cm_id);
          $this->db_fantasy->update(CONTEST);

          if($allow_2nd_inning == 1){
            //Update Contest table
            $this->db_fantasy->set("season_scheduled_date",$second_inning_date);
            $this->db_fantasy->where("status",0);
            $this->db_fantasy->where("is_2nd_inning",1);
            $this->db_fantasy->where("collection_master_id",$cm_id);
            $this->db_fantasy->update(CONTEST);
          }

          $this->delete_cache_data('fixture_'.$cm_id);
          $this->delete_s3_bucket_file("lobby_fixture_list_".$sports_id.".json");
        }
      }
    }    
  }

  /*
   * used for game cancellation
   * @param int $is_recursive
   * @return array
  */
  public function game_cancellation($is_recursive="0") {   
    set_time_limit(0);
    $current_date = format_date();          
    // When no user joined contest and open contest will cancel directly because no further action required for those contest
    if($is_recursive == "0"){
      $where = array('status' => 0,'total_user_joined' => 0,'season_scheduled_date <' => $current_date );
      $this->db_fantasy->update(CONTEST, array('status' => 1), $where);
    }

    $contest = $this->db_fantasy->select("C.contest_id,C.contest_unique_id,C.contest_name,C.entry_fee,C.sports_id,C.collection_master_id,MG.group_name,C.currency_type")
               ->from(CONTEST." AS C")
               ->join(MASTER_GROUP . " AS MG", " MG.group_id = C.group_id", 'INNER')
               ->where("C.status", 0)
               ->where("C.total_user_joined > ", 0)
               ->where("total_user_joined < ", 'minimum_size', FALSE)
               ->where("season_scheduled_date < ", $current_date)
               ->limit(1)
               ->get()
               ->row_array();
    //echo "<pre>";print_r($contest);die;
    if(!empty($contest)) {
      $subject = "Oops! Contest has been Cancelled";
      if(isset($contest['group_name']) && $contest['group_name'] != ""){
        $subject = "Oops! ".$contest['group_name']." has been Cancelled";
      }
      $additional_data['cancel_reason'] = '';
      $additional_data['current_date'] = $current_date;
      $additional_data['notification_type'] = 2;
      $additional_data['subject'] = $subject;

      //refund for paid contest
      if($contest['entry_fee'] > 0){
        $this->process_game_cancellation($contest, $additional_data);
      }else{
        //update for free contest
        $this->db_fantasy->where("contest_id",$contest['contest_id']);
        $this->db_fantasy->update(LINEUP_MASTER_CONTEST, array('fee_refund' => 1));
        
        // Game table update cancel status
        $this->db_fantasy->where('contest_id', $contest['contest_id']);
        $this->db_fantasy->update(CONTEST, array('status' => 1, 'entry_fee' => 0, 'modified_date' => $additional_data['current_date'], 'cancel_reason' => $additional_data['cancel_reason']));
      }
      $this->game_cancellation("1");
    }
    return;
  }

  /**
   * Process game cancellation
   * @param array $contest_list
   * @param array $additional_data
   */
  private function process_game_cancellation($contest, $additional_data) {
    if(!empty($contest)) {
      //update game table for game cancel
      $contest_id = $contest['contest_id'];
      $sports_id = $contest['sports_id'];
      $collection_master_id = $contest['collection_master_id'];
      $refund_data = $this->db_user->select('O.order_id,O.real_amount,O.bonus_amount,O.winning_amount,O.points,O.source,O.source_id,O.user_id,O.reference_id,O.type,O.custom_data,O.cb_amount')
                          ->from(ORDER . " AS O")
                          ->where('O.reference_id', $contest_id)
                          ->where('O.status', 1)
                          ->where('O.source', 1)
                          ->get()
                          ->result_array();
      //echo "<pre>";print_r($refund_data);die;
      $user_txn_data = array();
      if(!empty($refund_data))
      {
        foreach ($refund_data as $key => $value)
        {
          //user txn data
          $order_data = array();
          $order_data["order_unique_id"] = $this->_generate_order_unique_key();
          $order_data["user_id"]        = $value['user_id'];
          $order_data["source"]         = 2;
          $order_data["source_id"]      = $value['source_id'];
          $order_data["reference_id"]   = $value['reference_id'];
          $order_data["season_type"]    = 1;
          $order_data["type"]           = 0;
          $order_data["status"]         = 0;
          $order_data["real_amount"]    = $value['real_amount'];
          $order_data["bonus_amount"]   = $value['bonus_amount'];
          $order_data["winning_amount"] = $value['winning_amount'];
          $order_data["cb_amount"]      = $value['cb_amount'];
          $order_data["points"] = $value['points'];
          $order_data["custom_data"] = $value['custom_data'];
          $order_data["plateform"]      = PLATEFORM_FANTASY;
          $order_data["date_added"]     = format_date();
          $order_data["modified_date"]  = format_date();
          $user_txn_data[] = $order_data;
        }
      }
      //echo "<pre>";print_r($user_txn_data);die;
      if(!empty($user_txn_data)){
        try
        {
          $this->db_fantasy->where("contest_id",$contest_id);
          $this->db_fantasy->update(LINEUP_MASTER_CONTEST, array('fee_refund' => 1));

          //CHECK contest status
          $contest_info = $this->db_fantasy->select('C.contest_id,C.status')
                          ->from(CONTEST . " AS C")
                          ->where('C.contest_id', $contest_id)
                          ->get()
                          ->row_array();
          if(!empty($contest_info) && $contest_info['status'] == "0"){
            // Game table update cancel status
            $this->db_fantasy->where('contest_id', $contest_id);
            $this->db_fantasy->update(CONTEST, array('status' => 1, 'modified_date' => $additional_data['current_date'], 'cancel_reason' => $additional_data['cancel_reason']));
            
            $this->db = $this->db_user;
            //Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
            
            $user_txn_arr = array_chunk($user_txn_data, 999);
            foreach($user_txn_arr as $txn_data){
              $this->insert_ignore_into_batch(ORDER, $txn_data);
            }

            $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points,SUM(cb_amount) as cb_amount FROM ".$this->db->dbprefix(ORDER)." WHERE source = 2 AND type=0 AND status=0 AND reference_id='".$contest_id."' GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
            SET U.balance = (U.balance + OT.real_amount),U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),U.cb_balance = (U.cb_balance + OT.cb_amount),O.status=1 
            WHERE O.source = 2 AND O.type=0 AND O.status=0 AND O.reference_id='".$contest_id."' ";

            $this->db->query($bal_sql);
            //Trasaction End
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE )
            { 
              $this->db->trans_rollback();
            }
            else
            {
              $this->db->trans_commit();

              //delete cache data
              $this->delete_wildcard_cache_data('user_balance_');
              $this->delete_cache_data('contest_'.$contest_id);
              $this->delete_cache_data('lobby_filters_'.$sports_id);
            }
          }
        } catch (Exception $e)
        {
          $this->db->trans_rollback();
        }
      }else{
        // Game table update cancel status
        if(empty($refund_data)){
          $this->db_fantasy->where('contest_id', $contest_id);
          $this->db_fantasy->update(CONTEST, array('status' => 1, 'modified_date' => $additional_data['current_date'], 'cancel_reason' => $additional_data['cancel_reason']));
        }
      }
    }
  }

  /**
   * function used for send game cancel notification
   * @param void
   * @return boolean
   */
  public function match_cancel_notification($cancel_data = array()) 
  {
    $notification_type = isset($cancel_data['notification_type']) ? $cancel_data['notification_type'] : "2";
    $cancel_reason = isset($cancel_data['cancel_reason']) ? $cancel_data['cancel_reason'] : "";
    $this->db_fantasy->select("C.currency_type,LM.user_id,C.contest_id,C.collection_master_id,C.contest_name,C.status, C.contest_unique_id, C.size, C.prize_pool, C.entry_fee, LM.lineup_master_id,C.prize_type,C.league_id,CS.season_id,CM.collection_name,CM.season_game_count,C.sports_id,count(DISTINCT LMC.lineup_master_contest_id) as total_teams,C.cancel_reason",false)
          ->from(CONTEST . " C")
          ->join(COLLECTION_MASTER . " CM", "CM.collection_master_id = C.collection_master_id", "INNER")
          ->join(COLLECTION_SEASON . " CS", "CM.collection_master_id = CS.collection_master_id", "INNER")
          ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.contest_id = C.contest_id", "INNER")
          ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
          ->where("C.status", 1)
          ->where("LMC.fee_refund", 1)
          ->where("C.is_win_notify", 0)
          ->group_by("CM.collection_master_id,C.contest_id,LM.user_id")
          ->order_by("LM.user_id,C.contest_id,C.collection_master_id");

    if(isset($cancel_data['contest_id']) && $cancel_data['contest_id'] != ""){
      $this->db_fantasy->where("C.contest_id",$cancel_data['contest_id']);
    }
    if(isset($cancel_data['collection_master_id']) && $cancel_data['collection_master_id'] != ""){
      $this->db_fantasy->where("C.collection_master_id",$cancel_data['collection_master_id']);
    }
    $query = $this->db_fantasy->get();
    $result = $query->result_array();
    //echo "<pre>";print_r($result);die;
    $match_data = array();
    $notification_data = array();
    if (!empty($result)) 
    {   
      foreach ($result as $res) 
      {
        if(!isset($match_data[$res['season_id']]) || empty($match_data[$res['season_id']]))
        {
          if($res['sports_id'] == MOTORSPORT_SPORTS_ID){
            $season_data = $this->db_fantasy->select("S.season_id,S.season_game_uid,S.season_scheduled_date,L.league_abbr,L.league_name,S.tournament_name, 1 as is_tour_game",FALSE)
                    ->from(SEASON . " AS S")
                    ->join(LEAGUE . " AS L", "L.league_id = S.league_id", "INNER")
                    ->where("S.season_id",$res['season_id'])
                    ->where("S.league_id",$res['league_id'])
                    ->get()
                    ->row_array();
          }else{
            $season_data = $this->db_fantasy->select("S.season_id,S.season_game_uid,S.home_id,S.away_id,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,S.season_scheduled_date,L.league_abbr,L.league_name,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag",FALSE)
                  ->from(SEASON . " AS S")
                  ->join(LEAGUE . " AS L", "L.league_id = S.league_id", "INNER")
                  ->join(TEAM.' T1','T1.team_id=S.home_id','INNER')
                  ->join(TEAM.' T2','T2.team_id=S.away_id','INNER')
                  ->where("S.season_id",$res['season_id'])
                  ->where("S.league_id",$res['league_id'])
                  ->get()->row_array();

            $season_data['home_flag'] = get_image(0,$season_data['home_flag']);
            $season_data['away_flag'] = get_image(0,$season_data['away_flag']);
            $season_data['is_tour_game'] = 0;
          }
          $match_data[$res['season_id']] = $season_data;
        }

        if(!isset($notification_data[$res['user_id']."_".$res['collection_master_id']]) || empty($notification_data[$res['user_id']."_".$res['collection_master_id']]))
        {
          $this->db = $this->db_user;
          $user_detail = $this->get_single_row('email, user_name', USER, array("user_id"=>$res["user_id"]));
          $user_temp_data = array();
          $user_temp_data['source_id'] = $res['collection_master_id'];
          $user_temp_data['user_id'] = $res['user_id'];
          $user_temp_data['email'] = isset($user_detail['email']) ? $user_detail['email'] : "";
          $user_temp_data['user_name'] = isset($user_detail['user_name']) ? $user_detail['user_name'] : "";
          $user_temp_data['match_data'] = $match_data[$res['season_id']];
          $user_temp_data['collection_name'] = $res['collection_name'];
          $user_temp_data['season_game_count'] = $res['season_game_count'];
          $user_temp_data['contest_data'] = array();
          $notification_data[$res['user_id']."_".$res['collection_master_id']] = $user_temp_data;
        }

        $contest_data = array();
        $contest_data['contest_id'] = $res['contest_id'];
        $contest_data['user_id'] = $res['user_id'];
        $contest_data['contest_unique_id'] = $res['contest_unique_id'];
        $contest_data['contest_name'] = $res['contest_name'];
        $contest_data['collection_name'] = $res['collection_name'];
        $contest_data['size'] = $res['size'];
        $contest_data['prize_pool'] = $res['prize_pool'];
        $contest_data['entry_fee'] = $res['entry_fee'];
        $contest_data['currency_type'] = $res['currency_type'];
        $contest_data['total_teams'] = $res['total_teams'];
        $contest_data['prize_type'] = $res['prize_type'];
        $contest_data['cancel_reason'] = $res['cancel_reason'];
        $notification_data[$res['user_id']."_".$res['collection_master_id']]['contest_data'][] = $contest_data;

        // Game table update is_win_notify 1 for cancel notification
        $this->db_fantasy->where('contest_id', $res['contest_id']);
        $this->db_fantasy->update(CONTEST, array('is_win_notify' => '1', 'modified_date' => format_date()));
      }
    }
    //echo "<pre>";print_r($notification_data);die;
    foreach($notification_data as $notification)
    {
      if(isset($notification['email']) && $notification['email'] != ""){
        /* Send Notification */
        $notify_data = array();
        $notify_data['notification_type'] = $notification_type;
        $notify_data['notification_destination'] = 7; //web, push, email
        $notify_data["source_id"] = $notification['source_id'];
        $notify_data["user_id"] = $notification['user_id'];
        $notify_data["to"] = $notification['email'];
        $notify_data["user_name"] = $notification['user_name'];
        $notify_data["added_date"] = format_date();
        $notify_data["modified_date"] = format_date();
        $notify_data["subject"] = "Oops! Contests has been cancelled!";
        if($notification_type == 125) {
          $notify_data["subject"] = "Contests has been cancelled by technical team!";
        }

        $content = array();
        $content['notification_type'] = $notification_type;
        $content['match_data'] = $notification['match_data'];
        $content['collection_name'] = $notification['collection_name'];
        $content['season_game_count'] = $notification['season_game_count'];
        $content['contest_data'] = $notification['contest_data'];
        $content['int_version'] = $this->app_config['int_version']['key_value'];
        $content['contest_name'] = $notification['collection_name'];
        if(!empty($notification['contest_data'])){
          $contest_names = array_column($notification['contest_data'], 'contest_name');
          $content['contest_name'] = implode(",", $contest_names);
        }
        $content['cancel_reason'] = $cancel_reason;
        if(isset($notification['contest_data']['0']['cancel_reason']) && $notification['contest_data']['0']['cancel_reason'] != ""){
          $content['cancel_reason'] = $notification['contest_data']['0']['cancel_reason'];
        }
        $notify_data["content"] = json_encode($content);
        $this->load->model('notification/Notify_nosql_model');
        $this->Notify_nosql_model->send_notification($notify_data);
      }
    }
    return;
  }

  /**
   * Game cancel refund by contest
   * @param array $data
   * @return boolean
   */
  public function game_cancellation_by_id($data) {   
    set_time_limit(0);
    $contest_unique_id  = isset($data['contest_unique_id'])?$data['contest_unique_id']:0;
    $cancel_reason      = isset($data['cancel_reason'])?$data['cancel_reason']:'';
    $cancel_reason      = strip_tags($cancel_reason);
    if(!$contest_unique_id){
        return false;
    }
    
    $this->db_fantasy->select('contest_id,contest_unique_id,contest_name,entry_fee,sports_id, collection_master_id,total_user_joined')
            ->from(CONTEST)
            ->where('status', 0)
            ->where("contest_unique_id",$contest_unique_id);
    $this->db_fantasy->limit(1);
    $contest_list = $this->db_fantasy->get()->row_array();
    if (!empty($contest_list)) {
      //UPDATE contest status if participants 0
      if(isset($contest_list['total_user_joined']) && $contest_list['total_user_joined'] == "0"){
        $sports_id = $contest_list['sports_id'];
        $where = array('status' => 0,'total_user_joined' => 0,'contest_id' => $contest_list['contest_id']);
        $update_arr = array('status' => 1,'is_win_notify' => 1,'modified_date' => format_date(),'cancel_reason' => $cancel_reason);
        $this->db_fantasy->update(CONTEST, $update_arr, $where);

        if (CACHE_ENABLE) {
          $this->load->model('Nodb_model');
          $this->Nodb_model->flush_cache_data();
        }
      }else{
        $additional_data['contest_id'] = $contest_list['contest_id'];
        $additional_data['contest_name'] = $contest_list['contest_name'];
        $additional_data['cancel_reason'] = $cancel_reason;
        $additional_data['current_date'] = format_date();
        $additional_data['notification_type'] = 125;
        $additional_data['subject'] = ' has been Cancelled.';
        if($contest_list['entry_fee'] > 0){
          $this->process_game_cancellation($contest_list, $additional_data);
        }else{
          //update for free contest
          $this->db_fantasy->where("contest_id",$contest_list['contest_id']);
          $this->db_fantasy->update(LINEUP_MASTER_CONTEST, array('fee_refund' => 1));
          
          // Game table update cancel status
          $this->db_fantasy->where('contest_id', $contest_list['contest_id']);
          $this->db_fantasy->update(CONTEST, array('status' => 1, 'entry_fee' => 0, 'modified_date' => $additional_data['current_date'], 'cancel_reason' => $additional_data['cancel_reason']));
        }
        $this->match_cancel_notification($additional_data);
      }
    }
    return true;
  }

  /**
   * Game cancel refund by fixture
   * @param array $data
   * @return boolean
   */
  public function collection_cancel_by_id($data) {
      $collection_master_id = isset($data['collection_master_id'])?$data['collection_master_id']:0;        
      if(!$collection_master_id){
          return false;
      }
      
      $this->db_fantasy->select('contest_id, contest_unique_id, contest_name, entry_fee, sports_id, collection_master_id')
              ->from(CONTEST)
              ->where('status', 0)
              ->where("collection_master_id",$collection_master_id);
      $query = $this->db_fantasy->get();
      if($query->num_rows() > 0) {
        $contest_list = $query->result_array();
        if(!empty($contest_list)){    
          $this->load->helper('queue_helper');
          $data['action'] = 'cancel_game';   
          foreach($contest_list as $contest){
            $data['contest_unique_id'] = $contest['contest_unique_id'];
            add_data_in_queue($data, 'game_cancel');
          }
        }
      }
  }

  /**
  * Used for bench player process
  * @param 
  * @return boolean
  */
  public function process_bench(){
    $current_date = format_date();
    $total_credit = 100;
    $sql = $this->db_fantasy->select("CM.collection_master_id,CM.league_id,CM.season_scheduled_date,CM.deadline_time,L.sports_id,MS.max_player_per_team,IFNULL(CM.setting,'[]') as setting", FALSE)
          ->from(COLLECTION_MASTER . ' as CM')
          ->join(LEAGUE.' as L', 'L.league_id = CM.league_id', "INNER")
          ->join(MASTER_SPORTS.' as MS', 'MS.sports_id = L.sports_id', "INNER")
          ->where("CM.status","0")
          ->where("CM.is_lineup_processed","0")
          ->where("CM.bench_processed","0")
          ->having("CM.season_scheduled_date <= DATE_ADD('{$current_date}', INTERVAL CM.deadline_time MINUTE)");
  
    $result = $sql->get()->result_array();
    //echo "<pre>";print_r($result);die;
    foreach($result as $row){
      $setting = json_decode($row['setting'],TRUE);
      $sports_id = $row['sports_id'];
      $max_player_per_team = $row['max_player_per_team'];
      if(!empty($setting) && isset($setting['max_player_per_team'])){
        $max_player_per_team = $setting['max_player_per_team'];
      }
      $collection_master_id = $row['collection_master_id'];
      $sql = $this->db_fantasy->select('LM.*,GROUP_CONCAT(CONCAT(BP.priority,"_",BP.player_id)) as bench_players', FALSE)
          ->from(LINEUP_MASTER.' as LM')
          ->join(BENCH_PLAYER.' as BP', 'BP.lineup_master_id = LM.lineup_master_id', "INNER")
          ->where("LM.collection_master_id",$collection_master_id)
          ->group_by("LM.lineup_master_id")
          ->order_by("LM.lineup_master_id","ASC");
      $result = $sql->get()->result_array();
      //echo "<pre>";print_r($result);die;
      //update collection status if bench not applied for any teams
      if(empty($result)){
        $this->db_fantasy->where('status', "0");
        $this->db_fantasy->where('is_lineup_processed', "0");
        $this->db_fantasy->where('collection_master_id', $collection_master_id);
        $this->db_fantasy->update(COLLECTION_MASTER, array('bench_processed' => '2'));
      }else{

        //master position set in cache
        $position_cache_key = "position_".$sports_id;
        $master_positions = $this->get_cache_data($position_cache_key);
        if(!$master_positions)
        {
          $master_positions = $this->get_master_position($sports_id);
          //set master position in cache for 30 days
          $this->set_cache_data($position_cache_key,$master_positions,REDIS_30_DAYS);
        }
        $position_arr = array();
        foreach($master_positions as $pos){
          $min = $pos['number_of_players'];
          $max = $pos['max_player_per_position'];
          $pos_key = strtolower($pos['position']);
          if(!empty($setting) && isset($setting['pos'][$pos_key."_min"])){
            $min = $setting['pos'][$pos_key."_min"];
          }
          if(!empty($setting) && isset($setting['pos'][$pos_key."_max"])){
            $max = $setting['pos'][$pos_key."_max"];
          }
          $position_arr[$pos['position']] = array("position"=>$pos['position'],"min"=>$min,"max"=>$max);
        }

        //collection players
        $post_data['sports_id'] = $row['sports_id'];
        $post_data['league_id'] = $row['league_id'];
        $post_data['collection_master_id'] = $collection_master_id;
        $players_list = $this->get_match_rosters_list($post_data);
        $player_list_array = array_column($players_list,NULL,'player_team_id');
        $bench_processed = 0;
        //echo "<pre>";print_r($result);die;
        foreach($result as $team){
          if(!isset($team['bench_players']) || $team['bench_players'] == ""){
            $bench_processed = 1;
            continue;
          }
          $lineup_master_id = $team['lineup_master_id'];
          $team_data = json_decode($team['team_data'],TRUE);
          $bench_players = explode(",",$team['bench_players']);
          $bench_arr = array();
          foreach($bench_players as $pl_str){
            $pl_str = explode("_",$pl_str);
            $bench_arr[$pl_str['0']] = array("priority"=>$pl_str['0'],"player_id"=>$pl_str['1'],"out_player_id"=>"0","status"=>"0","reason"=>"");
          }
          //echo "<pre>";print_r($bench_arr);die;
          $team_non_playing = 0;
          $bench_playing = 0;
          $team_pls = array();
          $team_pos_pls = array();
          $team_wise_pl = array();
          $pos_pxi = array_fill_keys(array_keys($position_arr),array());
          foreach($team_data['pl'] as $player_team_id){
            $player_info = isset($player_list_array[$player_team_id]) ? $player_list_array[$player_team_id] : array();
            if(!empty($player_info)){
              $tmp_arr = array();
              $tmp_arr['player_uid'] = $player_info['player_uid'];
              $tmp_arr['player_team_id'] = $player_info['player_team_id'];
              $tmp_arr['team_id'] = $player_info['team_id'];
              $tmp_arr['position'] = $player_info['position'];
              $tmp_arr['salary'] = $player_info['salary'];
              $tmp_arr['player_team_id'] = $player_info['player_team_id'];
              $tmp_arr['player_team_id'] = $player_info['player_team_id'];
              $tmp_arr['is_playing'] = $player_info['is_playing'];
              $team_pls[$player_team_id] = $tmp_arr;
              if(!isset($team_pos_pls[$tmp_arr['position']])){
                $team_pos_pls[$tmp_arr['position']] = array();
              }
              $team_pos_pls[$tmp_arr['position']][] = $tmp_arr;
              if($player_info['is_playing'] == "1"){
                $team_wise_pl[$tmp_arr['team_id']][] = $tmp_arr['player_team_id'];
                $pos_pxi[$tmp_arr['position']][] = $tmp_arr;
              }
              if($player_info['is_playing'] == "0"){
                $team_non_playing = 1;
              }
            }
          }
          foreach($bench_arr as &$bench){
            $player_info = isset($player_list_array[$bench['player_id']]) ? $player_list_array[$bench['player_id']] : array();
            if(!empty($player_info)){
              $bench['player_uid'] = $player_info['player_uid'];
              $bench['player_team_id'] = $player_info['player_team_id'];
              $bench['team_id'] = $player_info['team_id'];
              $bench['position'] = $player_info['position'];
              $bench['salary'] = $player_info['salary'];
              $bench['player_team_id'] = $player_info['player_team_id'];
              $bench['player_team_id'] = $player_info['player_team_id'];
              $bench['is_playing'] = $player_info['is_playing'];
              if($player_info['is_playing'] == "1"){
                $bench_playing = 1;
              }
            }
          }
          $team_credit = array_sum(array_column($team_pls,"salary"));
          $team_pos_pl = array_column($team_pls,"position","player_team_id");
          $team_pos_pl = array_count_values($team_pos_pl);
          //print_r($team_wise_pl);die;
          foreach($bench_arr as &$bench){
            $bench_out_ids = array_column($bench_arr,"out_player_id");
            if($team_non_playing == "0" && $bench['is_playing'] == "1"){
              $bench['status'] = "2";
              $bench['reason'] = "All team players are playing";
            }else if($bench['is_playing'] == "0"){
              $bench['status'] = "2";
              $bench['reason'] = "Not Playing";
            }else if($team_pos_pl[$bench['position']] >= $position_arr[$bench['position']]['max']){
              $bench['status'] = "2";
              $bench['reason'] = "Position Violation";
            }else if(count(array_unique($team_wise_pl[$bench['team_id']])) >= $max_player_per_team){
              $bench['status'] = "2";
              $bench['reason'] = "Team Limit Violation";
            }else{
              foreach($team_pos_pls[$bench['position']] as $player){
                $final_sal = $team_credit + $player['salary'] - $bench['salary'];
                if($bench['status'] == "0" && $player['position'] == $bench['position'] && $player['is_playing'] == "0" && $final_sal <= $total_credit && !in_array($player['player_team_id'],$bench_out_ids)){
                  $bench['status'] = "1";
                  $bench['out_player_id'] = $player['player_team_id'];

                  unset($team_pls[$player['player_team_id']]);
                  $player_info = $player_list_array[$bench['player_id']];
                  $tmp_arr = array();
                  $tmp_arr['player_uid'] = $player_info['player_uid'];
                  $tmp_arr['player_team_id'] = $player_info['player_team_id'];
                  $tmp_arr['team_id'] = $player_info['team_id'];
                  $tmp_arr['position'] = $player_info['position'];
                  $tmp_arr['salary'] = $player_info['salary'];
                  $tmp_arr['player_team_id'] = $player_info['player_team_id'];
                  $tmp_arr['player_team_id'] = $player_info['player_team_id'];
                  $tmp_arr['is_playing'] = $player_info['is_playing'];
                  $team_pls[$bench['player_id']] = $tmp_arr;

                  //captain and vice-captain update
                  if($team_data['c_id'] == $player['player_team_id']){
                    $team_data['c_id'] = $tmp_arr['player_team_id'];
                  }else if($team_data['vc_id'] == $player['player_team_id']){
                    $team_data['vc_id'] = $tmp_arr['player_team_id'];
                  }

                  $team_credit = $final_sal;
                  $team_wise_pl[$tmp_arr['team_id']][] = $tmp_arr['player_team_id'];
                }
              }

              //check for other positions
              if(empty($setting) && $bench['status'] == "0"){
                foreach($team_pls as $player){
                  $final_sal = $team_credit + $player['salary'] - $bench['salary'];
                  if($bench['status'] == "0" && $player['position'] != $bench['position'] && $player['is_playing'] == "0" && $final_sal <= $total_credit && !in_array($player['player_team_id'],$bench_out_ids)){
                    $bench['status'] = "1";
                    $bench['out_player_id'] = $player['player_team_id'];

                    unset($team_pls[$player['player_team_id']]);
                    $player_info = $player_list_array[$bench['player_id']];
                    $tmp_arr = array();
                    $tmp_arr['player_uid'] = $player_info['player_uid'];
                    $tmp_arr['player_team_id'] = $player_info['player_team_id'];
                    $tmp_arr['team_id'] = $player_info['team_id'];
                    $tmp_arr['position'] = $player_info['position'];
                    $tmp_arr['salary'] = $player_info['salary'];
                    $tmp_arr['player_team_id'] = $player_info['player_team_id'];
                    $tmp_arr['player_team_id'] = $player_info['player_team_id'];
                    $tmp_arr['is_playing'] = $player_info['is_playing'];
                    $team_pls[$bench['player_id']] = $tmp_arr;

                    //captain and vice-captain update
                    if($team_data['c_id'] == $player['player_team_id']){
                      $team_data['c_id'] = $tmp_arr['player_team_id'];
                    }else if($team_data['vc_id'] == $player['player_team_id']){
                      $team_data['vc_id'] = $tmp_arr['player_team_id'];
                    }

                    $team_credit = $final_sal;
                    $team_wise_pl[$tmp_arr['team_id']][] = $tmp_arr['player_team_id'];
                  }
                }
              }

              if($bench['status'] == "0"){
                $bench['status'] = "2";
                $bench['reason'] = "Salary Exceeded";
              }
            }
          }
          //echo "<pre>";print_r($bench_arr);die;
          //update team
          $team_data['pl'] = array_column($team_pls,"player_team_id");
          $this->db_fantasy->where('collection_master_id', $collection_master_id);
          $this->db_fantasy->where('lineup_master_id', $lineup_master_id);
          $this->db_fantasy->update(LINEUP_MASTER, array('team_data' => json_encode($team_data)));

          foreach($bench_arr as $row){
            $data_arr = array();
            $data_arr['out_player_id'] = $row['out_player_id'];
            $data_arr['status'] = $row['status'];
            $data_arr['reason'] = $row['reason'];
            $data_arr['date_modified'] = format_date();
            $this->db_fantasy->where('player_id', $row['player_id']);
            $this->db_fantasy->where('lineup_master_id', $lineup_master_id);
            $this->db_fantasy->update(BENCH_PLAYER, $data_arr);
          }

          $bench_processed = 1;
        }

        //update collection status
        if($bench_processed == 1){
          $this->db_fantasy->where('status', "0");
          $this->db_fantasy->where('is_lineup_processed', "0");
          $this->db_fantasy->where('collection_master_id', $collection_master_id);
          $this->db_fantasy->update(COLLECTION_MASTER, array('bench_processed' => '1'));
        }
      }
    }

    return true;
  }

  /**
   * Used for push collection in queue for lineup move
   * @param void
   * @return boolean
   */
  public function push_lineup_move()
  {
    $current_date = format_date();
    $this->db_fantasy->select("CM.collection_master_id,CM.status,CM.is_lineup_processed,CM.season_scheduled_date,COUNT(LM.lineup_master_id) as teams",FALSE);
    $this->db_fantasy->from(COLLECTION_MASTER." AS CM");
    $this->db_fantasy->join(LINEUP_MASTER." AS LM", "LM.collection_master_id = CM.collection_master_id", "LEFT");
    $this->db_fantasy->where("CM.is_lineup_processed", '0');
    $this->db_fantasy->where("CM.status", '0');
    $this->db_fantasy->where("CM.season_scheduled_date < ", $current_date);
    $this->db_fantasy->group_by("CM.collection_master_id");
    $this->db_fantasy->order_by("CM.season_scheduled_date","DESC");
    $sql = $this->db_fantasy->get();
    $result = $sql->result_array();
    //echo "<pre>";print_r($result);die;
    if(!empty($result))
    {
      $this->load->helper('queue');
      $server_name = get_server_host_name();
      foreach($result as $row){
        //if there is no teams then mark processed
        if($row['teams'] == 0){
          $this->db_fantasy->update(COLLECTION_MASTER, array('is_lineup_processed' => 1, 'modified_date' => $current_date),array("collection_master_id"=>$row['collection_master_id'],"is_lineup_processed"=>"0"));
        }else{
          $collection_master_id = $row['collection_master_id'];
          $content = array();
          $content['url'] = $server_name."/cron/dfs/lineup_move/".$collection_master_id;
          add_data_in_queue($content,'dfs_lineup');
        }
      }
    }
    return true;
  }

  /**
   * Used for create lineup table
   * @param int $cm_id
   * @return string
   */
  private function create_lineup_table($cm_id){
      $table_name = LINEUP."_".$cm_id;
      if($this->db_fantasy->table_exists($table_name)){
        return $table_name;
      }else{
        $this->load->dbforge();
        $this->dbforge->add_field(array(
            'lineup_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'lineup_master_id' => array(
                'type' => 'INT',
                'constraint' => '11',
                'null' => FALSE
            ),
            'master_lineup_position_id' => array(
                'type' => 'INT',
                'constraint' => '11',
                'null' => FALSE
            ),
            'player_unique_id' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => FALSE
            ),
            'player_team_id' => array(
                'type' => 'INT',
                'constraint' => '11',
                'null' => FALSE
            ),
            'team_id' => array(
                'type' => 'INT',
                'constraint' => '11',
                'null' => FALSE
            ),
            'player_salary' => array(
                'type' => 'FLOAT',
                'null' => FALSE,
                'default' => 0
            ),
            'score' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ),
            'booster_points' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ),
            'captain' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => "1=>Captain, 2=>Vice Captain"
            ),
            'is_substitute' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => "1-for substituted (out player)"
            ),
            'in_minutes' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => "When player substitute enter in minutes"
            ),
            'out_minutes' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => "When player substitute enter out minutes"
            ),
            'status' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => null,
                'comment' => "1=>IN ,2 => OUT"
            ),
            'substituted_by' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => TRUE,
                'comment' => "Player unique id of new player"
            ),
            'added_date' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            )
        ));
        
        $this->dbforge->add_key('lineup_id', TRUE);
        $this->dbforge->create_table($table_name);

        $this->db_fantasy->query('ALTER TABLE '.$this->db_fantasy->dbprefix($table_name).' ADD CONSTRAINT unique_key UNIQUE (lineup_master_id,player_unique_id)');
        return $table_name;
      }
  }

  /**
   * Used for collection lineup move
   * @param int $collection_master_id
   * @return boolean
   */
  public function lineup_move($collection_master_id)
  {
    $current_date = format_date();
    $this->db_fantasy->select("CM.collection_master_id,CM.league_id,CM.status,CM.is_lineup_processed,CM.season_scheduled_date,L.sports_id");
    $this->db_fantasy->from(COLLECTION_MASTER." AS CM");
    $this->db_fantasy->join(LEAGUE." as L", "L.league_id = CM.league_id", "INNER");
    $this->db_fantasy->where("CM.is_lineup_processed", '0');
    $this->db_fantasy->where("CM.status", '0');
    $this->db_fantasy->where("CM.collection_master_id", $collection_master_id);
    $this->db_fantasy->where("CM.season_scheduled_date < ", $current_date);
    $this->db_fantasy->order_by("CM.season_scheduled_date","DESC");
    $sql = $this->db_fantasy->get();
    $result = $sql->row_array();
    //echo "<pre>";print_r($result);die;
    if(!empty($result))
    {
      $sports_id = $result['sports_id'];
      $league_id = $result['league_id'];
      $cm_id = $result['collection_master_id'];
      $team_list = $this->get_all_table_data("lineup_master_id,team_data",LINEUP_MASTER,array("collection_master_id"=>$cm_id),array("lineup_master_id"=>"ASC"));
      if(empty($team_list)){
        $this->db_fantasy->update(COLLECTION_MASTER, array('is_lineup_processed' => 1, 'modified_date' => $current_date),array("collection_master_id"=>$cm_id,"is_lineup_processed"=>"0"));
      }

      //master position
      $position_cache_key = "position_".$sports_id;
      $position_list = $this->get_cache_data($position_cache_key);
      if(!$position_list)
      {
        $position_list = $this->get_master_position($sports_id);
        $this->set_cache_data($position_cache_key,$position_list,REDIS_30_DAYS);
      }
      $position_list = array_column($position_list,"master_lineup_position_id","position");
      //echo "<pre>";print_r($position_list);
      //fixture roster
      $this->db_fantasy->select('P.player_id,P.player_uid,S.season_id,PT.position,ROUND(IFNULL(PT.salary,0),1) as salary,PT.player_team_id,PT.team_id,S.league_id,PT.last_match_played,PT.position', FALSE)
        ->select('(CASE WHEN JSON_SEARCH(S.playing_list,"one",P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_playing,S.playing_announce',FALSE)
        ->from(COLLECTION_SEASON . " AS CS")
        ->join(SEASON.' AS S','S.season_id = CS.season_id','INNER')
        ->join(PLAYER_TEAM.' PT', 'PT.season_id = S.season_id', 'INNER')
        ->join(PLAYER.' P', 'P.player_id = PT.player_id', 'INNER')
        ->where("PT.is_deleted",0)
        ->where("PT.player_status",1)
        ->where("PT.is_published",1)
        ->where("CS.collection_master_id",$collection_master_id)
        ->where("S.league_id",$league_id)
        ->group_by('P.player_uid')
        ->order_by('P.full_name','ASC');
      $sql = $this->db_fantasy->get();
      $roster_list = $sql->result_array();
      $roster_list = array_column($roster_list,NULL,"player_team_id");
      //echo "<pre>";print_r($roster_list);die;
      $lineup_arr = array();
      foreach($team_list as $team){
        $team_data = json_decode($team['team_data'],TRUE);
        foreach($team_data['pl'] as $player_team_id){
          $pl_info = $roster_list[$player_team_id];
          $captain = 0;
          if($player_team_id == $team_data['c_id']){
              $captain = 1;
          }else if($player_team_id == $team_data['vc_id']){
              $captain = 2;
          }
          $tmp_arr = array();
          $tmp_arr['lineup_master_id'] = $team['lineup_master_id'];
          $tmp_arr['master_lineup_position_id'] = $position_list[$pl_info['position']];
          $tmp_arr['player_unique_id'] = $pl_info['player_uid'];
          $tmp_arr['player_team_id'] = $player_team_id;
          $tmp_arr['team_id'] = $pl_info['team_id'];
          $tmp_arr['player_salary'] = $pl_info['salary'];
          $tmp_arr['captain'] = $captain;
          $tmp_arr['added_date'] = $current_date;
          $lineup_arr[$team['lineup_master_id']."_".$player_team_id] = $tmp_arr;
        }
      }
      //echo "<pre>";print_r($lineup_arr);die;
      if(!empty($lineup_arr)){
        try
        {
          //check and create dynamic table
          $lineup_table = $this->create_lineup_table($cm_id);

          //Start Transaction
          $this->db_fantasy->trans_strict(TRUE);
          $this->db_fantasy->trans_start();

          $data_keys = array_keys($lineup_arr);
          $concat_key = 'CONCAT(lineup_master_id,"_",player_team_id)';
          $this->insert_or_update_on_exist($data_keys,$lineup_arr,$concat_key,$lineup_table,'lineup_id');

          $update_arr = array();
          $update_arr['is_lineup_processed'] = 1;
          $update_arr['modified_date'] = format_date();
          $this->db_fantasy->where('collection_master_id',$cm_id);
          $this->db_fantasy->update(COLLECTION_MASTER,$update_arr);

          //Trasaction End
          $this->db_fantasy->trans_complete();
          if($this->db_fantasy->trans_status() === FALSE )
          {
            $this->db_fantasy->trans_rollback();
          }
          else
          {
            $this->db_fantasy->trans_commit();
          }
        }catch (Exception $e)
        {
          $this->db_fantasy->trans_rollback();
          log_message("Error","Lineup Move - ".$cm_id." : ".$e->getMessage());
        }
      }
    }
    return true;
  }

  /**
   * Used for move collection completed teams
   * @param void
   * @return boolean
   */
  public function move_completed_collection_team()
  {
    $current_date = format_date();
    $this->db_fantasy->select("CM.collection_master_id,CM.league_id,CM.status,CM.is_lineup_processed", FALSE);
    $this->db_fantasy->from(COLLECTION_MASTER.' CM');
    $this->db_fantasy->where('CM.is_lineup_processed',"1");
    $this->db_fantasy->where('CM.status',"1");
    $this->db_fantasy->where('CM.season_scheduled_date < ',$current_date);
    $this->db_fantasy->order_by('CM.collection_master_id', "ASC");
    $this->db_fantasy->limit(50);
    $match_list = $this->db_fantasy->get()->result_array();
    //echo "<pre>";print_r($match_list);die;
    if(!empty($match_list)){
      ini_set('memory_limit', '-1');
      $this->db = $this->db_fantasy;
      foreach($match_list as $match){
        $lineup_table = LINEUP."_".$match['collection_master_id'];
        if($this->db_fantasy->table_exists($lineup_table)){
          $this->db_fantasy->select("LM.lineup_master_id,LM.user_id,L.player_team_id,L.score,L.captain", FALSE);
          $this->db_fantasy->from(LINEUP_MASTER.' LM');
          $this->db_fantasy->join($lineup_table.' as L', 'LM.lineup_master_id = L.lineup_master_id', "INNER");
          $this->db_fantasy->where('LM.collection_master_id',$match['collection_master_id']);
          $this->db_fantasy->order_by('LM.lineup_master_id',"ASC");
          $this->db_fantasy->order_by('L.lineup_id',"ASC");
          $team_list = $this->db_fantasy->get()->result_array();
          $team_data = array();
          foreach($team_list as $row){
            if(isset($team_data[$row['lineup_master_id']])){
              $tm_arr = $team_data[$row['lineup_master_id']];
              $tm_arr['team_data'] = json_decode($tm_arr['team_data'],TRUE);
            }else{
              $tm_arr = array();
              $tm_arr['collection_master_id'] = $match['collection_master_id'];
              $tm_arr['lineup_master_id'] = $row['lineup_master_id'];
              $tm_arr['user_id'] = $row['user_id'];
              $tm_arr['team_data'] = array("c_id"=>"","vc_id"=>"","pl"=>array());
              $tm_arr['added_date'] = $current_date;
            }
            if($row['captain'] == "1"){
              $tm_arr['team_data']['c_id'] = $row['player_team_id'];
            }else if($row['captain'] == "2"){
              $tm_arr['team_data']['vc_id'] = $row['player_team_id'];
            }
            $tm_arr['team_data']['pl'][$row['player_team_id']] = $row['score'];
            $tm_arr['team_data'] = json_encode($tm_arr['team_data']);
            $team_data[$row['lineup_master_id']] = $tm_arr;
          }

          if(!empty($team_data)){
            $team_data = array_values($team_data);
            $this->replace_into_batch(COMPLETED_TEAM,$team_data);

            $this->set_auto_increment_key(COMPLETED_TEAM,'team_id');
          }

          //update status
          $this->db_fantasy->where('collection_master_id',$match['collection_master_id']);
          $this->db_fantasy->where('status',"1");
          $this->db_fantasy->update(COLLECTION_MASTER, array('is_lineup_processed'=>"2"));
          //echo "<pre>";print_r($team_data);die;
        }else{
          $this->db_fantasy->where('collection_master_id',$match['collection_master_id']);
          $this->db_fantasy->where('status',"1");
          $this->db_fantasy->where('is_lineup_processed',"1");
          $this->db_fantasy->update(COLLECTION_MASTER, array('is_lineup_processed'=>"2"));
        }
      }
    }
  }

  /**
   * Used for delete completed collection lineup table
   * @param void
   * @return boolean
   */
  public function archive_collection_team_table()
  {
    ini_set('memory_limit', '-1');
    $current_date = format_date();
    $past_time = date("Y-m-d H:i:s", strtotime($current_date . " -7"." days"));
    $this->db_fantasy->select("CM.collection_master_id,CM.league_id,CM.status,CM.is_lineup_processed", FALSE);
    $this->db_fantasy->from(COLLECTION_MASTER.' CM');
    $this->db_fantasy->where('CM.is_lineup_processed',"2");
    $this->db_fantasy->where('CM.status',"1");
    $this->db_fantasy->where('CM.season_scheduled_date < ',$past_time);
    $this->db_fantasy->order_by('CM.collection_master_id', "ASC");
    $this->db_fantasy->limit(10);
    $match_list = $this->db_fantasy->get()->result_array();
    //echo "<pre>";print_r($match_list);die;
    foreach($match_list as $match){
      $cm_id = $match['collection_master_id'];
      $lineup_table = LINEUP."_".$cm_id;
      if($this->db_fantasy->table_exists($lineup_table)){
        $bs_select = "L.booster_points";
        if(!$this->db_fantasy->field_exists('booster_points', $lineup_table)){
          $bs_select = "0 as booster_points";
        }
        $this->db_fantasy->select("L.lineup_id,LM.lineup_master_id,LM.collection_master_id as cm_id,L.master_lineup_position_id,L.player_unique_id,L.player_team_id,IFNULL(PT.team_id,0) as team_id,L.player_salary,L.score,L.captain,L.is_substitute,L.in_minutes,L.out_minutes,L.status,L.substituted_by,L.added_date,".$bs_select, FALSE);
        $this->db_fantasy->from(LINEUP_MASTER.' LM');
        $this->db_fantasy->join($lineup_table.' as L', 'LM.lineup_master_id = L.lineup_master_id', "INNER");
        $this->db_fantasy->join(PLAYER_TEAM.' as PT', 'PT.player_team_id = L.player_team_id', "LEFT");
        $this->db_fantasy->where('LM.collection_master_id',$cm_id);
        $this->db_fantasy->order_by('LM.lineup_master_id',"ASC");
        $this->db_fantasy->order_by('L.lineup_id',"ASC");
        $team_list = $this->db_fantasy->get()->result_array();
        //echo "<pre>";print_r($team_list);die;

        //Start Transaction
        $this->db_fantasy->trans_strict(TRUE);
        $this->db_fantasy->trans_start();

        if(!empty($team_list)){
          $team_arr = array_chunk($team_list, 5000);
          foreach($team_arr as $team_data){
            $this->insert_ignore_into_batch(LINEUP, $team_data);
          }
        }

        //drop table
        $this->db_fantasy->query("DROP TABLE ".$this->db_fantasy->dbprefix($lineup_table));

        //update status
        $this->db_fantasy->where('collection_master_id',$cm_id);
        $this->db_fantasy->where('status',"1");
        $this->db_fantasy->where('is_lineup_processed',"2");
        $this->db_fantasy->update(COLLECTION_MASTER, array('is_lineup_processed'=>"3"));

        //Trasaction End
        $this->db_fantasy->trans_complete();
        if($this->db_fantasy->trans_status() === FALSE )
        {
          $this->db_fantasy->trans_rollback();
        }
        else
        {
          $this->db_fantasy->trans_commit();
        }
        //echo "<pre>";print_r($team_data);die;
      }else{
        //update status
        $this->db_fantasy->where('collection_master_id',$cm_id);
        $this->db_fantasy->where('status',"1");
        $this->db_fantasy->where('is_lineup_processed',"2");
        $this->db_fantasy->update(COLLECTION_MASTER, array('is_lineup_processed'=>"3"));
      }
    }
  }

  /**
   * Used for push live contest for team pdf
   * @param void
   * @return boolean
   */
  public function push_live_collection_for_pdf(){
    $current_date = format_date();
    $sql = $this->db_fantasy->select('CM.collection_master_id,CM.season_scheduled_date,C.contest_id,C.contest_unique_id', FALSE)
          ->from(COLLECTION_MASTER . ' as CM')
          ->join(CONTEST." as C", "C.collection_master_id = CM.collection_master_id", "INNER")
          ->where("CM.status","0")
          ->where("CM.is_lineup_processed","1")
          ->where("C.is_pdf_generated","0")
          ->where("C.total_user_joined >= C.minimum_size")
          ->having("CM.season_scheduled_date <= ",$current_date);
    $result_record = $sql->get()->result_array();
    if(!empty($result_record)){
      foreach($result_record as $contest){
        $contest_update = array();
        $contest_update['is_pdf_generated'] = 1;
        $contest_update['modified_date'] = format_date();
        $this->db_fantasy->where('contest_id', $contest['contest_id']);
        $this->db_fantasy->update(CONTEST, $contest_update);

        $this->load->helper('queue');
        $server_name = get_server_host_name();
        $content = array();
        $content['url'] = $server_name."/cron/dfs/generate_contest_pdf/".$contest['contest_id'];
        add_data_in_queue($content,'contestpdf');
      }
    }
    return true;
  }

  /**
   * Used for generate contest team pdf
   * @param int $contest_id
   * @return boolean
   */
  public function generate_contest_pdf($contest_id){
      if(!$contest_id){
        return false;
      }
      $contest_info = $this->db_fantasy->select("C.contest_id,C.sports_id,C.contest_unique_id,C.league_id,C.collection_master_id,CM.collection_name,C.contest_name,C.prize_pool,C.prize_distibution_detail,C.entry_fee,C.total_user_joined,C.season_scheduled_date,IFNULL(CM.setting,'[]') as setting", FALSE)
          ->from(CONTEST . ' as C')
          ->join(COLLECTION_MASTER . " as CM", "C.collection_master_id = CM.collection_master_id", "INNER")
          ->where("C.contest_id",$contest_id)
          ->where("C.is_pdf_generated","1")
          ->get()
          ->row_array();
         
      if(empty($contest_info)){
          return false;
      }
      
      $limit = 100;
      $collection_master_id = $contest_info['collection_master_id'];
      $lineup_table = LINEUP."_".$collection_master_id;
      if (!$this->db_fantasy->table_exists($lineup_table)) {
          return false;
      }
      $post_data['sports_id'] = $contest_info['sports_id'];
      $post_data['league_id'] = $contest_info['league_id'];
      $post_data['collection_master_id'] = $collection_master_id;
      $players_list = $this->get_match_rosters_list($post_data);
      $player_list_array = array_column($players_list,NULL,'player_uid');
      //echo "<pre>";print_r($player_list_array);die;

      $this->db_fantasy->select("LM.team_name,LM.user_name,LM.user_id,LM.lineup_master_id,LM.league_id,LMC.lineup_master_contest_id,LMC.contest_id")
            ->from(LINEUP_MASTER_CONTEST . " LMC")
            ->join(LINEUP_MASTER . " LM", "LMC.lineup_master_id = LM.lineup_master_id", "INNER")
            ->where("LMC.fee_refund", "0")
            ->where("LMC.contest_id", $contest_info["contest_id"])
            ->order_by("LMC.game_rank", "ASC", FALSE)
            ->order_by("LMC.total_score", "DESC", FALSE);
      $user_rank = $this->db_fantasy->get()->result_array();
      $user_team_ids = array_column($user_rank, "lineup_master_id");

      $this->db_fantasy->select("L.lineup_master_id,L.master_lineup_position_id,L.player_unique_id,L.player_team_id,L.captain as player_role")
            ->from($lineup_table . " AS L")
            ->join(LINEUP_MASTER_CONTEST . ' LMC', 'LMC.lineup_master_id = L.lineup_master_id', 'INNER')
            ->where("LMC.fee_refund", "0")
            ->where("LMC.contest_id", $contest_id);
      if(isset($user_team_ids) && !empty($user_team_ids)){
          $this->db_fantasy->where_in("L.lineup_master_id",$user_team_ids);
      }
      $user_teams = $this->db_fantasy->get()->result_array();
      $user_team_data = array();
      foreach($user_teams as $player){
        $player_name = "";
        if(isset($player_list_array[$player['player_unique_id']])){
          $player_name = $player_list_array[$player['player_unique_id']]['full_name'];
        }
        $player['full_name'] = $player_name;
        $user_team_data[$player['lineup_master_id']][] = $player;
      }

      $setting = json_decode($contest_info['setting'],TRUE);
      $c_point = CAPTAIN_POINT;
      $vc_point = VICE_CAPTAIN_POINT;
      if(!empty($setting) && isset($setting['c'])){
        $c_point = $setting['c'];
      }
      if(!empty($setting) && isset($setting['vc'])){
        $vc_point = $setting['vc'];
      }
      if(in_array($contest_info['sports_id'],[MOTORSPORT_SPORTS_ID,TENNIS_SPORTS_ID])){
        $vc_point = 0; 
      }
      $final_data = array();
      $column = 0;
      foreach($user_rank as $team)
      {
        if(!empty($user_team_data[$team['lineup_master_id']])){
          $team_players = $user_team_data[$team['lineup_master_id']];
          $temp_array = array();
          $temp_array[0] = $team["user_name"];
          $temp_array[1] = $team["team_name"];
          $temp_array[2] = "";
          $temp_array[3] = "";
          $pl_start_key = 2;
          if($c_point > 0){
            $pl_start_key+= 1;
          }
          if($vc_point > 0){
            $pl_start_key+= 1;
          }
          $column = 0;
          foreach($team_players as $key => $player)
          {
            if($c_point > 0 && $player["player_role"]==1)
            {
              $temp_array[2] = ucfirst($player["full_name"]);
            }
            elseif($vc_point > 0 && $player["player_role"]==2)
            {
              $temp_array[3] = ucfirst($player["full_name"]);
            }else{
              $temp_array[($key+$pl_start_key)] = ucfirst($player["full_name"]);
            }
            $column++;
          }
          $temp_array = array_filter($temp_array);
          $final_data[] = $temp_array;
        }
      }
      //echo "<pre>";print_r($final_data);die;
      ini_set('memory_limit', '-1');
      $this->load->helper('fpdf_helper');
      $contest_pdf_name = "/tmp/".$contest_id.".pdf";
      $contest_info['logo'] = IMAGE_PATH."assets/img/logo.png";
      $contest_info['int_version'] = isset($this->app_config['int_version']['key_value'])?$this->app_config['int_version']['key_value']:0;
      $pdf_data = array();
      $pdf_data['contest_info'] = $contest_info;
      $pdf_data['team_list'] = $final_data;
      $pdf_data['c_vc'] = array("c_point"=>$c_point,"vc_point"=>$vc_point);
     
      generate_pdf($contest_pdf_name,$pdf_data);

      $filePath = "lineup/".$contest_id.".pdf";
      try{
          $data_arr = array();
          $data_arr['file_path'] = $filePath;
          $data_arr['source_path'] = $contest_pdf_name;
          $this->load->library('Uploadfile');
          $upload_lib = new Uploadfile();
          $is_uploaded = $upload_lib->upload_file($data_arr);
          if($is_uploaded){
              /* Delete file from tmp directory*/
              @unlink($contest_pdf_name);
          }
      }catch(Exception $e){
          //echo 'Caught exception: '.  $e->getMessage(). "\n";
        return false;
      }

      $contest_update = array();
      $contest_update['is_pdf_generated'] = 2;
      $contest_update['modified_date'] = format_date();
      $this->db_fantasy->where('contest_id', $contest_id);
      $this->db_fantasy->update(CONTEST, $contest_update);
      return true;
  }

  /**
   * used to get fixture all players list
   * @param array $post_data
   * @return array
  */
  public function get_match_rosters_list($post_data)
  {
    $collection_master_id = $post_data['collection_master_id'];
    $league_id = $post_data['league_id'];
    $sports_id = !empty($post_data['sports_id']) ? $post_data['sports_id'] : '';
    $this->db_fantasy->select('P.player_id,P.player_uid,IFNULL(T.display_team_abbr,T.team_abbr) as team_name,IFNULL(T.display_team_abbr,T.team_abbr) as team_abbreviation,S.season_id,S.season_game_uid,P.display_name as full_name,P.first_name,P.last_name,PT.position,IFNULL(PT.salary,0) as salary,IFNULL(P.display_name,"") as display_name,PT.player_team_id, PT.team_id,T.team_uid,P.sports_id,S.league_id,IFNULL(T.jersey,T.feed_jersey) as jersey,PT.last_match_played as lmp', FALSE)
      ->select('(CASE WHEN JSON_SEARCH(S.playing_list,"one",P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_playing,S.playing_announce',FALSE)
      ->select('(CASE WHEN JSON_SEARCH(S.substitute_list,"one",P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_sub',FALSE)
      ->from(COLLECTION_SEASON . " AS CS")
      ->join(SEASON.' S','S.season_id = CS.season_id','INNER')
      ->join(PLAYER_TEAM.' PT','PT.season_id=S.season_id','INNER')
      ->join(PLAYER.' P', 'P.player_id = PT.player_id', 'INNER')
      ->join(TEAM.' T', 'T.team_id = PT.team_id', 'INNER')
      ->where("PT.is_deleted",0)
      ->where("CS.collection_master_id",$collection_master_id)
      ->where("PT.player_status",1)
      ->where("PT.is_published",1)
      ->group_by('P.player_uid')
      ->order_by('P.full_name','ASC');

    if(isset($league_id) && $league_id != ""){
      $this->db->where("S.league_id",$league_id);
      $this->db->where("TL.league_id",$league_id);
    }

    $sql = $this->db_fantasy->get();
    $result = $sql->result_array();
    return $result;
  }

  /**
   * Used for update score in lineup table
   * @param void
   * @return boolean
   */
  public function update_lineup_score($sports_id='')
  {
      $current_date = format_date();
      $past_time = date("Y-m-d H:i:s", strtotime($current_date . " -".MATCH_SCORE_CLOSE_DAYS." days"));
      $close_date_time = date("Y-m-d H:i:s", strtotime($current_date." -".CONTEST_CLOSE_INTERVAL." minutes"));
      $this->db_fantasy->select("CM.collection_master_id,CM.season_scheduled_date,CM.status,S.season_id,S.status,IFNULL(CM.2nd_inning_date,'') as 2nd_inning_date");
      $this->db_fantasy->from(COLLECTION_MASTER." AS CM");
      $this->db_fantasy->join(COLLECTION_SEASON." AS CS", "CS.collection_master_id = CM.collection_master_id", "INNER");
      $this->db_fantasy->join(SEASON." AS S", "S.season_id = CS.season_id AND S.league_id=CM.league_id", "INNER");
      $this->db_fantasy->where("CM.is_lineup_processed", '1');
      $this->db_fantasy->where("CM.status", '0');
      $this->db_fantasy->where("S.match_status !=", '2');
      $this->db_fantasy->where("CM.season_scheduled_date < ", $current_date);
      $this->db_fantasy->where("CM.season_scheduled_date >= ", $past_time);
      $this->db_fantasy->where("(S.match_closure_date IS NULL OR S.match_closure_date > '".$close_date_time."')");
      $this->db_fantasy->group_by("CM.collection_master_id");
      $this->db_fantasy->order_by("CM.season_scheduled_date","DESC");

      if($sports_id != ""){
        $this->db_fantasy->join(LEAGUE." AS L", "L.league_id = CM.league_id", "INNER");
        $this->db_fantasy->where("L.sports_id",$sports_id);
      }

      $sql = $this->db_fantasy->get();
      $result = $sql->result_array();
      //echo "<pre>";print_r($result);die;
      if(!empty($result))
      {
          $this->load->helper('queue');
          $server_name = get_server_host_name();
          foreach($result as $row){
              $cm_id = $row['collection_master_id'];
              $content = array();
              $content['url'] = $server_name."/cron/dfs/update_collection_score/".$cm_id;
              add_data_in_queue($content,'point_update_cron');
          }
      }
      return true;
  }

  private function get_booster_point_sql($sports_id,$cm_id){
    $allow_booster = isset($this->app_config['allow_booster']) ? $this->app_config['allow_booster']['key_value'] : 0;
    if(!in_array($sports_id, array(CRICKET_SPORTS_ID, NFL_SPORTS_ID, BASKETBALL_SPORTS_ID, SOCCER_SPORTS_ID, BASEBALL_SPORTS_ID))) {
      $allow_booster = 0;
    }
    $booster_sel = "";
    $booster_up = "";
    $booster_join = "";
    if(isset($allow_booster) && $allow_booster == "1") {
      $check_booster = $this->db_fantasy->select("COUNT(id) as total")
                             ->from(BOOSTER_COLLECTION) 
                             ->where("collection_master_id",$cm_id) 
                             ->get()->row_array();
      if(!empty($check_booster) && isset($check_booster['total']) && $check_booster['total'] > 0){                     
        $booster_join = "LEFT JOIN ".$this->db_fantasy->dbprefix(BOOSTER_COLLECTION)." AS BC ON BC.booster_id=LM.booster_id AND BC.collection_master_id=LM.collection_master_id";
        if($sports_id == CRICKET_SPORTS_ID) {
          $booster_sel = ",JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.SIX')) as sixes,JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.FOUR')) as fours,JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.WICKET')) as wickets,JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.RUN_OUT')) as run_outs";
          $booster_up = ",LU.booster_points = (
                          CASE WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=1 THEN (IFNULL(GP.fours,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=2 THEN (IFNULL(GP.sixes,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=3 THEN (IFNULL(GP.wickets,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=4 THEN (IFNULL(GP.run_outs,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=1 THEN (IFNULL(GP.fours,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=2 THEN (IFNULL(GP.sixes,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=3 THEN (IFNULL(GP.wickets,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=4 THEN (IFNULL(GP.run_outs,'0.00') * (BC.points - 1)) 
                          ELSE '0.00' END)";
        }else if($sports_id == NFL_SPORTS_ID) {
          $booster_sel = ",JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.GLADIATOR')) as gladiator, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.IRON_WALL')) as iron_wall,JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.HOT_POTATO')) as hot_potato, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.NEW_KICKS')) as new_kicks, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.RUNNING_BOMB')) as running_bomb, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.NO_FUMBLE')) as no_fumble, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.CAPTAIN')) as captain, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.IM_OPEN')) as im_open";
          $booster_up = ",LU.booster_points = (
                          CASE WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=5 THEN (IFNULL(GP.gladiator,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=6 THEN (IFNULL(GP.iron_wall,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=7 THEN (IFNULL(GP.hot_potato,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=8 THEN (IFNULL(GP.new_kicks,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=9 THEN (IFNULL(GP.running_bomb,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=10 THEN (IFNULL(GP.no_fumble,'0.00') * (BC.points - 1))
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=11 THEN (IFNULL(GP.captain,'0.00') * (BC.points - 1))
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=12 THEN (IFNULL(GP.im_open,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=5 THEN (IFNULL(GP.gladiator,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=6 THEN (IFNULL(GP.iron_wall,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=7 THEN (IFNULL(GP.hot_potato,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=8 THEN (IFNULL(GP.new_kicks,'0.00') * (BC.points - 1))
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=9 THEN (IFNULL(GP.running_bomb,'0.00') * (BC.points - 1))
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=10 THEN (IFNULL(GP.no_fumble,'0.00') * (BC.points - 1))
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=11 THEN (IFNULL(GP.captain,'0.00') * (BC.points - 1))
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=12 THEN (IFNULL(GP.im_open,'0.00') * (BC.points - 1)) 
                          ELSE '0.00' END)";
        }else if($sports_id == BASKETBALL_SPORTS_ID) {
          $booster_sel = ",JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.DICKEYS_MIDDLE_RING')) as dickeys_middle_ring, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.MISS_BUTLERS_TURNOVER')) as miss_butlers_turnover, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.GRAND_THEFT_BALLER')) as grand_theft_baller, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.BLOCK_PARTY')) as block_party, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.CAPTAIN')) as captain";
          $booster_up = ",LU.booster_points = (
                          CASE WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=13 THEN (IFNULL(GP.dickeys_middle_ring,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=14 THEN (IFNULL(GP.miss_butlers_turnover,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=15 THEN (IFNULL(GP.grand_theft_baller,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=16 THEN (IFNULL(GP.block_party,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=17 THEN (IFNULL(GP.captain,'0.00') * (BC.points - 1))
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=13 THEN (IFNULL(GP.dickeys_middle_ring,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=14 THEN (IFNULL(GP.miss_butlers_turnover,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=15 THEN (IFNULL(GP.grand_theft_baller,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=16 THEN (IFNULL(GP.block_party,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=17 THEN (IFNULL(GP.captain,'0.00') * (BC.points - 1))
                          ELSE '0.00' END)";
        }else if($sports_id == SOCCER_SPORTS_ID) {
          $booster_sel = ",JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.OWN_GOAL')) as own_goal, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.GOAL')) as goal, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.SHARP_SHOOTER')) as sharp_shooter, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.SAVER')) as saver, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.NO_RED_ZONE')) as no_red_zone";
          $booster_up = ",LU.booster_points = (
                          CASE WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=18 THEN (IFNULL(GP.own_goal,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=19 THEN (IFNULL(GP.goal,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=20 THEN (IFNULL(GP.sharp_shooter,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=21 THEN (IFNULL(GP.saver,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=22 THEN (IFNULL(GP.no_red_zone,'0.00') * (BC.points - 1))
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=18 THEN (IFNULL(GP.own_goal,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=19 THEN (IFNULL(GP.goal,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=20 THEN (IFNULL(GP.sharp_shooter,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=21 THEN (IFNULL(GP.saver,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=22 THEN (IFNULL(GP.no_red_zone,'0.00') * (BC.points - 1))
                          ELSE '0.00' END)";
        }else if($sports_id == BASEBALL_SPORTS_ID) {
          $booster_sel = ",JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.J_WALKER')) as j_walker, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.No_SHORTCUTS')) as no_shortcuts, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.FULLY_LOADED')) as fully_loaded, JSON_UNQUOTE(json_extract(GST.booster_break_down, '$.WALK_RUN')) as walk_run";
          $booster_up = ",LU.booster_points = (
                          CASE WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=23 THEN (IFNULL(GP.j_walker,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=24 THEN (IFNULL(GP.no_shortcuts,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=25 THEN (IFNULL(GP.fully_loaded,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND LU.master_lineup_position_id=BC.position_id AND BC.booster_id=26 THEN (IFNULL(GP.walk_run,'0.00') * (BC.points - 1))
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=23 THEN (IFNULL(GP.j_walker,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=24 THEN (IFNULL(GP.no_shortcuts,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=25 THEN (IFNULL(GP.fully_loaded,'0.00') * (BC.points - 1)) 
                          WHEN LM.booster_id > 0 AND BC.position_id=0 AND BC.booster_id=26 THEN (IFNULL(GP.walk_run,'0.00') * (BC.points - 1))
                          ELSE '0.00' END)";
        }
      }
    }
    return array("booster_sel"=>$booster_sel,"booster_up"=>$booster_up,"booster_join"=>$booster_join);
  }

  /**
   * Used for update fixture score in user teams
   * @param int $collection_master_id
   * @return boolean
   */
  public function update_collection_score($collection_master_id)
  {
      if(!$collection_master_id){
          return false;
      }

      $current_date = format_date();
      $this->db_fantasy->select("CM.collection_master_id,CM.league_id,CM.status,GROUP_CONCAT(DISTINCT S.season_id) as season_ids,IFNULL(CM.2nd_inning_date,'') as 2nd_inning_date,L.sports_id");
      $this->db_fantasy->from(COLLECTION_MASTER." AS CM");
      $this->db_fantasy->join(COLLECTION_SEASON." AS CS", "CS.collection_master_id = CM.collection_master_id", "INNER");
      $this->db_fantasy->join(SEASON." AS S", "S.season_id = CS.season_id", "INNER");
      $this->db_fantasy->join(LEAGUE." AS L", "L.league_id = S.league_id", "INNER");
      $this->db_fantasy->where("CM.collection_master_id", $collection_master_id);
      $this->db_fantasy->where("CM.is_lineup_processed", '1');
      $this->db_fantasy->where("CM.status", '0');
      $this->db_fantasy->where("CM.season_scheduled_date < ", $current_date);
      $this->db_fantasy->group_by("CM.collection_master_id");
      $sql = $this->db_fantasy->get();
      $result = $sql->row_array();
      //echo "<pre>";print_r($result);die;
      if(!empty($result))
      {
          $captain_point = CAPTAIN_POINT;
          $vice_captain_point = VICE_CAPTAIN_POINT;
          if($captain_point <= 1)
          {
             $captain_point = 1;
          }
          if($vice_captain_point <= 1)
          {
             $vice_captain_point = 1;
          }
          $collection_master_id = $result['collection_master_id'];
          $league_id = $result['league_id'];
          $sports_id = $result['sports_id'];
          $season_ids = $result['season_ids'];
          $second_inning_date = $result['2nd_inning_date'];
          $allow_2nd_inning = isset($this->app_config['allow_2nd_inning'])?$this->app_config['allow_2nd_inning']['key_value']:0;
          $lineup_table = LINEUP."_".$collection_master_id;
          if($this->db_fantasy->table_exists($lineup_table))
          {
              $contest_list = $this->db_fantasy->select("contest_id,is_tie_breaker")
                                   ->from(CONTEST) 
                                   ->where("collection_master_id",$collection_master_id)
                                   ->where("status","0")
                                   ->where("total_user_joined >","0")
                                   ->where('total_user_joined >= minimum_size')
                                   ->where('season_scheduled_date <= ',$current_date)
                                   ->get()
                                   ->result_array();
              if(empty($contest_list)){
                  return true;
              }

              /*if(!$this->db_fantasy->field_exists('booster_points', $lineup_table)){
                $this->db_fantasy->query("ALTER TABLE ".$this->db_fantasy->dbprefix($lineup_table)." ADD `booster_points` FLOAT NOT NULL DEFAULT '0' AFTER `score`;");
              }*/

              //Start Transaction
              $this->db_fantasy->trans_strict(TRUE);
              $this->db_fantasy->trans_start();

              //check and get booster related data
              $booster = $this->get_booster_point_sql($sports_id,$collection_master_id);
              $booster_sel = isset($booster['booster_sel']) ? $booster['booster_sel'] : "";
              $booster_join = isset($booster['booster_join']) ? $booster['booster_join'] : "";
              $booster_up = isset($booster['booster_up']) ? $booster['booster_up'] : "";

              //Update player fantasy score in particular lineup table based on collection master id
              $sql = "UPDATE ".$this->db_fantasy->dbprefix($lineup_table)."  AS LU 
                      INNER JOIN ".$this->db->dbprefix(PLAYER_TEAM)." AS PT ON PT.player_team_id=LU.player_team_id AND PT.season_id IN(".$season_ids.")
                      INNER JOIN (
                        SELECT SUM(IFNULL(GST.score,0)) AS score,GST.player_id $booster_sel 
                        FROM ".$this->db_fantasy->dbprefix(GAME_PLAYER_SCORING)." AS GST 
                        WHERE GST.season_id IN(".$season_ids.")
                        GROUP BY GST.player_id
                      ) AS GP ON GP.player_id = PT.player_id 
                      INNER JOIN ".$this->db_fantasy->dbprefix(LINEUP_MASTER)." AS LM ON  LU.lineup_master_id=LM.lineup_master_id 
                      $booster_join
                      SET LU.score = (
                                      CASE WHEN LU.captain = '1' THEN IFNULL(GP.score,'0.00')*".$captain_point." 
                                      WHEN LU.captain = '2' THEN IFNULL(GP.score,'0.00')*".$vice_captain_point." 
                                      ELSE IFNULL(GP.score,'0.00') END
                                    ) $booster_up
                      WHERE LM.is_2nd_inning=0 AND LM.collection_master_id = '".$collection_master_id."'";
              $this->db_fantasy->query($sql);


              if($allow_2nd_inning == 1 && $second_inning_date != "" && $second_inning_date < $current_date){
                $sql = "UPDATE ".$this->db_fantasy->dbprefix($lineup_table)."  AS LU 
                      INNER JOIN ".$this->db->dbprefix(PLAYER_TEAM)." AS PT ON PT.player_team_id=LU.player_team_id AND PT.season_id IN(".$season_ids.")
                      INNER JOIN (
                        SELECT SUM(IFNULL(GST.2nd_inning_score,0)) AS score,GST.player_id 
                        FROM ".$this->db_fantasy->dbprefix(GAME_PLAYER_SCORING)." AS GST 
                        WHERE GST.season_id IN(".$season_ids.")
                        GROUP BY GST.player_id
                      ) AS GP ON GP.player_id = PT.player_id 
                      INNER JOIN ".$this->db_fantasy->dbprefix(LINEUP_MASTER)." AS LM ON  LU.lineup_master_id=LM.lineup_master_id
                      SET LU.score = (
                                      CASE WHEN LU.captain = '1' THEN IFNULL(GP.score,'0.00')*".$captain_point." 
                                      WHEN LU.captain = '2' THEN IFNULL(GP.score,'0.00')*".$vice_captain_point." 
                                      ELSE IFNULL(GP.score,'0.00') END
                                    )
                      WHERE LM.is_2nd_inning=1 AND LM.collection_master_id = '".$collection_master_id."'";
                $this->db_fantasy->query($sql);
              }

              //update total score in lmc table
              $update_sql = " UPDATE ".$this->db_fantasy->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC 
                INNER JOIN (SELECT SUM(score) AS scores,SUM(booster_points) as booster_points,L.lineup_master_id FROM ".$this->db_fantasy->dbprefix($lineup_table)." AS L GROUP BY L.lineup_master_id) AS L_PQ ON L_PQ.lineup_master_id = LMC.lineup_master_id 
                SET LMC.total_score = IFNULL(L_PQ.scores,'0.00') + IFNULL(L_PQ.booster_points,'0.00'),LMC.booster_points = IFNULL(L_PQ.booster_points,'0.00')
                WHERE LMC.fee_refund=0";
              $this->db_fantasy->query($update_sql);

              //update contest wise rank
              foreach($contest_list as $contest){
                  $rank_str = "ORDER BY total_score DESC";
                  if(isset($contest['is_tie_breaker']) && $contest['is_tie_breaker'] == 1){
                      $rank_str.= ",lineup_master_contest_id ASC";
                  }

                  $rank_sql = "UPDATE ".$this->db_fantasy->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC 
                    INNER JOIN (SELECT LMC1.lineup_master_contest_id,RANK() OVER (".$rank_str.") user_rank FROM ".$this->db_fantasy->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC1 
                    WHERE LMC1.contest_id = ".$contest['contest_id'].") AS L_PQ ON L_PQ.lineup_master_contest_id = LMC.lineup_master_contest_id
                    SET LMC.game_rank = IFNULL(L_PQ.user_rank,'0') 
                    WHERE LMC.fee_refund=0";
                  $this->db_fantasy->query($rank_sql);
              }

              //Trasaction End
              $this->db_fantasy->trans_complete();
              if ($this->db_fantasy->trans_status() === FALSE )
              {
                  $this->db_fantasy->trans_rollback();
              }
              else
              {
                  $this->db_fantasy->trans_commit();
              }
          }
      }

      return true;
  }

  /**
   * used to get fixture all players list
   * @param array $post_data
   * @return array
  */
  public function get_playing_players($league_id,$season_game_uids)
  {
    $this->db_fantasy->select('GROUP_CONCAT(DISTINCT player_uid) as playing_players,MAX(score) as top_score')
      ->from(GAME_PLAYER_SCORING)
      ->where("league_id",$league_id)
      ->where_in("season_game_uid",$season_game_uids);
    $sql = $this->db_fantasy->get();
    $result = $sql->row_array();
    return $result;
  }

  /**
   * Used for update contest status
   * @param void
   * @return string print output
   */
  public function update_contest_status($sports_id="")
  {
      $current_date = format_date();
      $close_date_time = date("Y-m-d H:i:s", strtotime($current_date." -".CONTEST_CLOSE_INTERVAL." minutes"));
      $this->db_fantasy->select("CM.collection_master_id,CM.status,CM.season_game_count,SUM(CASE WHEN S.status=2 AND S.status_overview=4 THEN 1 ELSE 0 END) as completed,SUM(CASE WHEN S.status=2 AND S.status_overview=3 THEN 1 ELSE 0 END) as cancelled");
      $this->db_fantasy->from(COLLECTION_MASTER." AS CM");
      $this->db_fantasy->join(COLLECTION_SEASON." AS CS", "CS.collection_master_id = CM.collection_master_id", "INNER");
      $this->db_fantasy->join(SEASON." AS S", "S.season_id = CS.season_id AND S.league_id=CM.league_id", "INNER");
      //$this->db_fantasy->where("CM.season_game_count > ", '1');
      $this->db_fantasy->where("CM.is_lineup_processed", '1');
      $this->db_fantasy->where("CM.status", '0');
      $this->db_fantasy->where("S.status", '2');
      $this->db_fantasy->where("S.status_overview", '4');
      $this->db_fantasy->where("CM.season_scheduled_date < ", $current_date);
      $this->db_fantasy->where("((S.match_closure_date IS NOT NULL AND S.match_closure_date <= '".$close_date_time."') OR S.match_status = '2')");
      $this->db_fantasy->group_by("CM.collection_master_id");
      $this->db_fantasy->having("CM.season_game_count = (completed + cancelled)");
      $this->db_fantasy->order_by("CM.season_scheduled_date","DESC");
      $this->db_fantasy->limit(100);

      if($sports_id != ""){
        $this->db_fantasy->join(LEAGUE." AS L", "L.league_id = CM.league_id", "INNER");
        $this->db_fantasy->where("L.sports_id",$sports_id);
      }

      $sql = $this->db_fantasy->get();
      $result = $sql->result_array();
      //echo "<pre>";print_r($result);die;
      if(!empty($result))
      {
          $cm_ids = array_column($result,"collection_master_id");
          $this->db_fantasy->select("GROUP_CONCAT(DISTINCT(C.contest_id)) as contest_ids",FALSE)
                  ->from(CONTEST." AS C")
                  ->join(LINEUP_MASTER_CONTEST." AS LMC","LMC.contest_id=C.contest_id","INNER")
                  ->where_in("C.collection_master_id",$cm_ids)
                  ->where("C.status","0")
                  ->where("C.total_user_joined >","0")
                  ->where("LMC.total_score > ",0)
                  ->where("LMC.fee_refund",0);
          $sql = $this->db_fantasy->get();
          $contest = $sql->row_array();
          //echo "======<pre>";print_r($contest);die;
          if(!empty($contest) && $contest['contest_ids'] != ""){
              $c_ids = explode(",",$contest['contest_ids']);
              $c_ids_chunks = array_chunk($c_ids,500);
              $this->db_fantasy->where("status", 0);
              $this->db_fantasy->group_start();
              foreach($c_ids_chunks as $key => $vchunk_arr) 
              {
                if($key == 0)
                {
                  $this->db_fantasy->where_in('contest_id',$vchunk_arr);
                }
                else
                {
                  $this->db_fantasy->or_where_in('contest_id',$vchunk_arr);
                }  
              }
              $this->db_fantasy->group_end();
              $this->db_fantasy->update(CONTEST, array("status" => 2,"modified_date" => format_date()));
          }
          
          $this->db_fantasy->select("C.collection_master_id",FALSE);
          $this->db_fantasy->from(CONTEST." AS C");
          $this->db_fantasy->where("C.status","0");
          $this->db_fantasy->where_in("C.collection_master_id", $cm_ids);
          $this->db_fantasy->group_by("C.collection_master_id");
          $sql = $this->db_fantasy->get();
          $cm_contest = $sql->result_array();
          if(!empty($cm_contest)){
              $cm_contest = array_column($cm_contest,"collection_master_id");
              $cm_ids = array_diff($cm_ids,$cm_contest);
          }
          //echo "<pre>";print_r($cm_ids);die;
          if(!empty($cm_ids)){
              $this->db_fantasy->where_in("collection_master_id", $cm_ids);
              $this->db_fantasy->update(COLLECTION_MASTER, array("status" => 1, "modified_date" => format_date()));

              //delete lobby filters data
              $this->delete_wildcard_cache_data('lobby_filters_');
          }
     }
     return true;
  }

  /**
   * Function used for distribute contest prize
   * @param 
   * @return boolean
   */
  public function prize_distribution()
  {        
    $this->db_fantasy->select('C.collection_master_id,C.group_id', FALSE)
        ->from(CONTEST.' AS C')
        ->where('C.status', 2)
        ->where('C.season_scheduled_date < ', format_date())
        ->where('C.prize_type <> ', 4, FALSE)
        ->where('C.prize_distibution_detail != ', 'null' )
        ->group_by('C.collection_master_id')
        ->order_by('C.season_scheduled_date','DESC')
        ->order_by("FIELD(C.group_id,'1','2') ASC");
    $result = $this->db_fantasy->get()->result_array();
    //echo "<pre>";print_r($result);die;
    if(!empty($result))
    {
      $this->load->helper('queue');
      $server_name = get_server_host_name();
      foreach ($result AS $prize)
      {
        $content = array();
        $content['url'] = $server_name."/cron/dfs/match_prize_distribution/".$prize['collection_master_id'];
        add_data_in_queue($content,'prize_distribution');
      }
    }
    return true;
  }

  /**
   * to get merchandise list
   * @param void
   * @return array
   */
  public function get_merchandise_list($ids_arr = array())
  {
      $this->db_fantasy->select('merchandise_id,name,image_name,price')
              ->from(MERCHANDISE)
              ->order_by("merchandise_id","ASC");

      if(isset($ids_arr) && !empty($ids_arr)){
          $this->db_fantasy->where_in("merchandise_id",$ids_arr);
      }
      $result = $this->db_fantasy->get()->result_array();
      return $result;
  }

  /**
   * Function used for distribute match contest prize
   * @param int $collection_master_id
   * @return boolean
   */
  public function match_prize_distribution($collection_master_id)
  {
    if(!$collection_master_id){
      return false;
    }

    $this->db_fantasy->select("C.contest_id,C.contest_unique_id,IFNULL(NULLIF(C.contest_title, ''),C.contest_name) as contest_name,C.season_scheduled_date,C.master_duration_id AS league_contest_type_id,C.entry_fee,C.master_contest_type_id,C.size AS entries,C.size,C.prize_pool,C.prize_distibution_detail,C.is_uncapped,C.minimum_size,C.site_rake,C.host_rake,C.prize_type,C.total_user_joined,C.guaranteed_prize,IF(C.user_id > 0,'1','0') as is_private,C.group_id", FALSE)
        ->from(CONTEST . ' AS C')
        ->where('C.status', 2)
        ->where('C.season_scheduled_date < ', format_date())
        ->where('C.prize_distibution_detail IS NOT NULL')
        ->where('C.collection_master_id', $collection_master_id)
        ->where('C.total_user_joined >= C.minimum_size')
        ->group_by('C.contest_id')
        ->order_by("FIELD(C.group_id,'1','2') ASC")
        ->order_by('C.total_user_joined','DESC');
    $result = $this->db_fantasy->get()->result_array();
    //echo "<pre>";print_r($result);die;
    if (!empty($result))
    {
      $h2h_challenge = isset($this->app_config['h2h_challenge'])?$this->app_config['h2h_challenge']['key_value']:0;
      $h2h_group_id = isset($this->app_config['h2h_challenge']['custom_data']['group_id']) ? $this->app_config['h2h_challenge']['custom_data']['group_id'] : 0;
      $contest_ids = array();
      $private_contest = array();
      $h2h_contest_ids = array();
      $user_lmc_data = array();
      $user_txn_data = array();
      foreach($result as $prize){
        $contest_unique_id = $prize['contest_unique_id'];
        $contest_id = $prize['contest_id'];
        $contest_name = $prize['contest_name'];
        if(empty($prize['prize_distibution_detail']))
        {
          return;
        }
        $contest_ids[] = $contest_id;
        if($prize['is_private'] == 1){
          $private_contest[] = $contest_id;
        }
        //h2h contest ids
        if($h2h_challenge == 1 && $h2h_group_id == $prize['group_id']){
          $h2h_contest_ids[] = $contest_id;
        }
        $prize['per_user_prize'] = 1;
        $wining_amount = reset_contest_prize_data($prize);
        //echo "<pre>";print_r($wining_amount);die;
        $wining_max = array_column($wining_amount, 'max');
        $winner_places = max($wining_max);
        if(empty($winner_places) || $winner_places == NULL || $winner_places == 0){
            return;
        }

        $merchandise = $merchandise_ids = array();
        foreach($wining_amount as $amt_arr){
          if(isset($amt_arr['prize_type']) && $amt_arr['prize_type'] == 3)
          {
            $merchandise_ids[] = $amt_arr['amount'];
          }
        }
        if(!empty($merchandise_ids)){
          $merchandise = $this->get_merchandise_list($merchandise_ids);
          $merchandise = array_column($merchandise,NULL,"merchandise_id");
        }

        $winning_amount_arr = array();
        if(!empty($wining_amount)) 
        {
          foreach($wining_amount as $win_amt) 
          {
            if(!isset($win_amt['prize_type'])){
              $win_amt['prize_type'] = 1;
            }
            for($i=$win_amt['min']; $i<=$win_amt['max']; $i++)
            {
              if(isset($win_amt['prize_type']) && $win_amt['prize_type']==3)
              {
                $image = "";
                $mname = "";
                if(isset($merchandise[$win_amt['amount']])){
                  $image = $merchandise[$win_amt['amount']]['image_name'];
                  $mname = $merchandise[$win_amt['amount']]['name'];
                }
                $winning_amount_arr[$i] = array("prize_type"=>$win_amt['prize_type'],"name"=>$mname,"image"=>$image);
              }
              else
              {   
                $winning_amount_arr[$i] = array("prize_type"=>$win_amt['prize_type'],"amount"=>$win_amt['amount']);
              }
            }
          }
        }
        //echo "<pre>";print_r($winning_amount_arr);die;

        //get winners
        $this->db_fantasy->select("LM.user_id,LM.collection_master_id,LMC.lineup_master_contest_id,LMC.lineup_master_id,LMC.contest_id,LMC.total_score,LMC.game_rank");
        $this->db_fantasy->from(LINEUP_MASTER_CONTEST." AS LMC");
        $this->db_fantasy->join(LINEUP_MASTER." AS LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER");
        $this->db_fantasy->where("LMC.contest_id",$contest_id);
        $this->db_fantasy->where("LMC.fee_refund",'0');
        $this->db_fantasy->where("LMC.game_rank <= ",$winner_places);
        $this->db_fantasy->order_by("LMC.game_rank","ASC");
        $sql = $this->db_fantasy->get();
        $winners = $sql->result_array();
        $tie_up_postion = array_count_values(array_column($winners,"game_rank","lineup_master_contest_id"));
        $rank_prize_arr = array();
        foreach($tie_up_postion as $rank => $win_count){
          $rank_prize_arr[$rank] = array("amount"=>"0","bonus"=>"0","coins"=>"0","merchandise"=>"","winner"=>$win_count);
          if($win_count > 1){
            $rk_to = $rank+$win_count;
            for($i=$rank;$i < $rk_to;$i++){
              $prize_arr = isset($winning_amount_arr[$i]) ? $winning_amount_arr[$i] : array("prize_type"=>"1","amount"=>"0");
              if(isset($prize_arr['prize_type']) && $prize_arr['prize_type'] == "0"){
                $rank_prize_arr[$rank]['bonus'] = $rank_prize_arr[$rank]['bonus'] + $prize_arr['amount'];
              }else if(isset($prize_arr['prize_type']) && $prize_arr['prize_type'] == "2"){
                $rank_prize_arr[$rank]['coins'] = $rank_prize_arr[$rank]['coins'] + $prize_arr['amount'];
              }else{
                $rank_prize_arr[$rank]['amount'] = $rank_prize_arr[$rank]['amount'] + $prize_arr['amount'];
              }
            }
          }else{
            $prize_arr = $winning_amount_arr[$rank];
            if(isset($prize_arr['prize_type']) && $prize_arr['prize_type'] == "0"){
              $rank_prize_arr[$rank]['bonus'] = $prize_arr['amount'];
            }else if(isset($prize_arr['prize_type']) && $prize_arr['prize_type'] == "2"){
              $rank_prize_arr[$rank]['coins'] = $prize_arr['amount'];
            }else if(isset($prize_arr['prize_type']) && $prize_arr['prize_type'] == "3"){
                $rank_prize_arr[$rank]['merchandise'] = $prize_arr['name'];
                $rank_prize_arr[$rank]['image'] = isset($prize_arr['image']) ? $prize_arr['image'] : "";
            }else{
              $rank_prize_arr[$rank]['amount'] = $prize_arr['amount'];
            }
          }
        }
        //echo "<pre>";print_r($rank_prize_arr);die;
        if(!empty($winners))
        {
          foreach ($winners as $key => $value)
          {
            $won_data = $rank_prize_arr[$value['game_rank']];
            $is_winner = 0;
            $bonus_amount = $winning_amount = $points = 0;
            $merchandise = "";
            $prize_obj_tmp = array();
            $custom_data = array("contest_name"=>$contest_name);
            if(isset($won_data['amount']) && $won_data['amount'] > 0){
              $is_winner = 1;
              $winning_amount = number_format(($won_data['amount'] / $won_data['winner']),2,".","");
              $prize_obj_tmp[] = array("prize_type"=>"1","amount"=>$winning_amount);
            }
            if(isset($won_data['bonus']) && $won_data['bonus'] > 0){
              $is_winner = 1;
              $bonus_amount = number_format(($won_data['bonus'] / $won_data['winner']),2,".","");
              $prize_obj_tmp[] = array("prize_type"=>"0","amount"=>$bonus_amount);
            }
            if(isset($won_data['coins']) && $won_data['coins'] > 0){
              $is_winner = 1;
              $points = number_format(($won_data['coins'] / $won_data['winner']),2,".","");
              $prize_obj_tmp[] = array("prize_type"=>"2","amount"=>ceil($points));
            }
            if(isset($won_data['merchandise']) && $won_data['merchandise'] != ""){
              $is_winner = 1;
              $merchandise = $won_data['merchandise'];
              $image = isset($won_data['image']) ? $won_data['image'] : "";
              $custom_data["merchandise"] = $merchandise;
              $prize_obj_tmp[] = array("prize_type"=>"3","name"=>$merchandise,"image"=>$image);
            }
            
            $lmc_data = array();
            $lmc_data['lineup_master_id'] = $value['lineup_master_id'];
            $lmc_data['contest_id'] = $value['contest_id'];
            $lmc_data['is_winner'] = $is_winner;
            $lmc_data['amount'] = $winning_amount;
            $lmc_data['bonus'] = $bonus_amount;
            $lmc_data['coin'] = ceil($points);
            $lmc_data['merchandise'] = $merchandise;
            $lmc_data['prize_data'] = json_encode($prize_obj_tmp);
            $user_lmc_data[] = $lmc_data;

            //user txn data
            if($is_winner == 1){
              $order_data = array();
              $order_data["order_unique_id"] = $this->_generate_order_unique_key();
              $order_data["user_id"]        = $value['user_id'];
              $order_data["source"]         = 3;
              $order_data["source_id"]      = $value['lineup_master_contest_id'];
              $order_data["reference_id"]   = $value['contest_id'];
              $order_data["season_type"]    = 1;
              $order_data["type"]           = 0;
              $order_data["status"]         = 0;
              $order_data["real_amount"]    = 0;
              $order_data["bonus_amount"]   = $bonus_amount;
              $order_data["winning_amount"] = $winning_amount;
              $order_data["points"] = ceil($points);
              $order_data["custom_data"] = json_encode($custom_data);
              $order_data["plateform"]      = PLATEFORM_FANTASY;
              $order_data["date_added"]     = format_date();
              $order_data["modified_date"]  = format_date();
              $user_txn_data[] = $order_data;
            }
          }
        }
      }
      //echo "<pre>";print_r($contest_ids);
      //echo "<pre>";print_r($user_lmc_data);
      //echo "<pre>";print_r($user_txn_data);die;
      if(!empty($user_lmc_data)){
        try
        {
          $is_updated = 0;
          $this->db = $this->db_fantasy;
          //Start Transaction
          $this->db->trans_strict(TRUE);
          $this->db->trans_start();
          
          $user_lmc_arr = array_chunk($user_lmc_data, 999);
          foreach($user_lmc_arr as $lmc_data){
            $this->replace_into_batch(LINEUP_MASTER_CONTEST, $lmc_data);
          }

          //Trasaction End
          $this->db->trans_complete();
          if ($this->db->trans_status() === FALSE )
          {
            $this->db->trans_rollback();
          }
          else
          {
            $is_updated = 1;
            $this->db->trans_commit();
          }

          if($is_updated == 1){
            $this->db = $this->db_user;
            //Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
            
            $user_txn_arr = array_chunk($user_txn_data, 999);
            foreach($user_txn_arr as $txn_data){
              $this->insert_ignore_into_batch(ORDER, $txn_data);
            }

            $contest_ids = array_unique($contest_ids);
            $ctst_ids_arr = array_chunk($contest_ids, 300);
            foreach($ctst_ids_arr as $cnts_ids){
              $contest_ids_str = implode(",", $cnts_ids);
              $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = 3 AND type=0 AND status=0 AND reference_id IN (".$contest_ids_str.") GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
                SET U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
                WHERE O.source = 3 AND O.type=0 AND O.status=0 AND O.reference_id IN (".$contest_ids_str.") ";
              $this->db->query($bal_sql);
            }

            //Trasaction End
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE )
            {
              $this->db->trans_rollback();
            }
            else
            {
              $this->db->trans_commit();

              // Game table update is_prize_distributed 1
              $this->db_fantasy->where('status',"2");
              $this->db_fantasy->where('collection_master_id',$collection_master_id);
              $this->db_fantasy->where_in('contest_id', $contest_ids);
              $this->db_fantasy->update(CONTEST, array('status' => '3', 'modified_date' => format_date(),'completed_date' => format_date()));
 
              $this->load->helper('queue');
              $server_name = get_server_host_name();

              //for contest tds
              $content = array();
              $content['url'] = $server_name."/cron/dfs/tds_process/".$collection_master_id;
              add_data_in_queue($content,'dfs_tds');

              //affiliate user comission
              $af_cb = isset($this->app_config['affiliate_module']['custom_data']['site_commission']) ? $this->app_config['affiliate_module']['custom_data']['site_commission'] : 0;
              if($af_cb == 1){
                $content = array();
                $content['url'] = $server_name."/cron/dfs/process_affiliate_cb/".$collection_master_id;
                add_data_in_queue($content,'dfs_tds');
              }

              $content = array();
              $content['url'] = $server_name."/adminapi/report/cron/user_match_report/".$collection_master_id;
              add_data_in_queue($content,'report_cron');

              $content = array();
              $content['url'] = $server_name."/adminapi/report/cron/update_contest_report/".$collection_master_id;
              add_data_in_queue($content,'report_cron');

              foreach($contest_ids AS $contest_id)
              { 
                //for contest referral
                $content = array();
                $content['url'] = $server_name."/cron/cron/add_cash_contest_referral_bonus/".$contest_id;
                add_data_in_queue($content,'referral');

                //for every cash contest referral
                $content = array();
                $content['url'] = $server_name."/cron/cron/add_every_cash_contest_referral_benefits/".$contest_id;
                add_data_in_queue($content,'referral');
              }

              //add in queue here for host rake
              foreach($private_contest as $pcontest_id) {
                $pdata = array("contest_id"=>$pcontest_id);
                add_data_in_queue($pdata, 'host_rake');
              }

              //for update h2h users level
              if($h2h_challenge == 1 && !empty($h2h_contest_ids))
              {
                $content = array();
                $content['url'] = $server_name."/cron/cron/update_h2h_user_level/match_".$collection_master_id;
                add_data_in_queue($content,'h2h');
              }

              if (CACHE_ENABLE) {
                $this->load->model('Nodb_model');
                $this->Nodb_model->flush_cache_data();
              }
            }
          }
        } catch (Exception $e)
        {
            //echo 'Caught exception: '.  $e->getMessage(). "\n";
        }
      }
    }
    return true;
  }

  /**
   * Function used for process financial year tds
   * @param int $fy
   * @return boolean
   */
  public function fy_tds_process($fy)
  {
    if(!$fy){
      return false;
    }
    //echo "<pre>";print_r($tds);
    $current_date = format_date();
    $start_date = $fy."-04-01 00:00:00";
    $end_date = ($fy + 1)."-03-31 23:59:59";
    $this->db_fantasy->select("CM.collection_master_id", FALSE)
        ->from(CONTEST.' AS C')
        ->join(COLLECTION_MASTER." AS CM", "CM.collection_master_id = C.collection_master_id", "INNER")
        ->where('C.status',3)
        ->where('CM.status',1)
        ->where('CM.is_lineup_processed > ',0)
        ->where('CM.season_scheduled_date >= ', $start_date)
        ->where('CM.season_scheduled_date <= ', $end_date)
        ->group_by('CM.collection_master_id');
    $result = $this->db_fantasy->get()->result_array();
    //echo "<pre>";print_r($result);die;
    if(!empty($result)){
      $this->load->helper('queue');
      $server_name = get_server_host_name();

      foreach($result as $row){
        //for contest tds
        $content = array();
        $content['url'] = $server_name."/cron/dfs/tds_process/".$row['collection_master_id'];
        add_data_in_queue($content,'dfs_tds');
      }
    }
    
    return true;
  }

  /**
   * Function used for process match tds
   * @param int $collection_master_id
   * @return boolean
   */
  public function tds_process($collection_master_id)
  {
    if(!$collection_master_id){
      return false;
    }
    $tds = $this->app_config['allow_tds']['key_value'] && isset($this->app_config['allow_tds']['custom_data']) ? $this->app_config['allow_tds']['custom_data'] : array();
    if(empty($tds)){
      return false;
    }
    //echo "<pre>";print_r($tds);
    $current_date = format_date();
    $this->db_fantasy->select("CM.collection_master_id as entity_id,CM.collection_name as entity_name,CM.season_scheduled_date as scheduled_date,C.sports_id,GROUP_CONCAT(DISTINCT contest_id) as ids", FALSE)
        ->from(CONTEST.' AS C')
        ->join(COLLECTION_MASTER." AS CM", "CM.collection_master_id = C.collection_master_id", "INNER")
        ->where('C.status',3)
        ->where('C.collection_master_id', $collection_master_id)
        ->where('CM.season_scheduled_date < ', $current_date);
    $result = $this->db_fantasy->get()->row_array();
    //echo "<pre>";print_r($result);
    if(!empty($result)){
      $module_type = 1;
      $sports_id = $result['sports_id'];
      $entity_id = $result['entity_id'];
      $entity_name = $result['entity_name'];
      $scheduled_date = $result['scheduled_date'];
      $c_ids = explode(",",$result['ids']);
      $c_ids_chunks = array_chunk($c_ids,500);
      $this->db_user->select("O.user_id,IFNULL(SUM(CASE WHEN O.source=1 THEN (real_amount + winning_amount) ELSE 0 END),0) as total_entry,IFNULL(SUM(CASE WHEN O.source=3 THEN winning_amount ELSE 0 END),0) as total_winning");
      $this->db_user->from(ORDER." AS O");
      $this->db_user->where("status", 1);
      $this->db_user->where_in("source",[1,3]);
      $this->db_user->group_start();
      foreach($c_ids_chunks as $key => $vchunk_arr) 
      {
        if($key == 0)
        {
          $this->db_user->where_in('reference_id',$vchunk_arr);
        }
        else
        {
          $this->db_user->or_where_in('reference_id',$vchunk_arr);
        }  
      }
      $this->db_user->group_end();
      $this->db_user->group_by("O.user_id");
      $this->db_user->order_by("O.user_id","ASC");
      $sql = $this->db_user->get();
      $user_list = $sql->result_array();
      //echo "<pre>";print_r($user_list);
      $tds_report = array();
      $tds_txn = array();
      foreach($user_list as $row){
        $row['net_winning'] = $row['total_winning'] - $row['total_entry'];
        $tmp_arr = array();
        $tmp_arr['module_type'] = $module_type;
        $tmp_arr['user_id'] = $row['user_id'];
        $tmp_arr['sports_id'] = $sports_id;
        $tmp_arr['entity_id'] = $entity_id;
        $tmp_arr['entity_name'] = $entity_name;
        $tmp_arr['scheduled_date'] = $scheduled_date;
        $tmp_arr['total_entry'] = $row['total_entry'];
        $tmp_arr['total_winning'] = $row['total_winning'];
        $tmp_arr['net_winning'] = $row['net_winning'];
        $tmp_arr['status'] = 0;
        $tmp_arr['date_added'] = $current_date;
        $tds_report[] = $tmp_arr;

        if(isset($tds['indian']) && $tds['indian'] == 0 && $row['net_winning'] >= $tds['amount']){
          $tds_amount = number_format((($row['net_winning'] * $tds['percent']) / 100),2,'.','');
          if($tds_amount > 0){
            $tmp_tds = array();
            $tmp_tds['user_id'] = $row['user_id'];
            $tmp_tds['amount'] = $tds_amount;
            $tmp_tds['tds'] = $tds_amount;
            $tmp_tds['source'] = 130;
            $tmp_tds['source_id'] = $entity_id;
            $tmp_tds['custom_data'] = array("name"=>$entity_name,"tds_rate"=>$tds['percent'],"net_winning"=>$row['net_winning']);
            $tds_txn[] = $tmp_tds;
          }
        }
      }
      //echo "<pre>";print_r($tds_report);die;
      if(!empty($tds_report)){
        try{
          $this->db = $this->db_user;
          //Start Transaction
          $this->db->trans_strict(TRUE);
          $this->db->trans_start();
          
          $tds_report_arr = array_chunk($tds_report, 999);
          foreach($tds_report_arr as $tds_txn_data){
            $this->insert_ignore_into_batch(USER_TDS_REPORT, $tds_txn_data);
          }

          $netwin_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(USER_TDS_REPORT)." AS UT ON UT.user_id=U.user_id 
              SET U.net_winning = (U.net_winning + UT.net_winning),UT.status=1 
              WHERE UT.status=0 AND UT.module_type = ".$module_type." AND UT.entity_id=".$entity_id;
          $this->db->query($netwin_sql);

          if(!empty($tds_txn)){
            foreach($tds_txn as $tds_row){
              $check_exist = $this->get_single_row('order_id',ORDER, array("source"=>$tds_row['source'],"user_id"=>$tds_row['user_id'],"source_id"=>$tds_row['source_id']));
              if(empty($check_exist)){
                $tds_row["order_unique_id"] = $this->_generate_order_unique_key();
                $tds_row["type"] = 1;
                $tds_row["status"] = 0;
                $tds_row["real_amount"] = 0;
                $tds_row["bonus_amount"] = 0;
                $tds_row["winning_amount"] = $tds_row['amount'];
                $tds_row["points"] = 0;
                $tds_row["custom_data"] = json_encode($tds_row['custom_data']);
                $tds_row["date_added"] = $current_date;
                $tds_row["modified_date"] = $current_date;
                unset($tds_row['amount']);
                $this->db->insert(ORDER,$tds_row);
                $order_id = $this->db->insert_id();
                if($order_id){
                  $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id 
                  SET U.winning_balance = (U.winning_balance - O.winning_amount),O.status=1 
                  WHERE O.source = ".$tds_row['source']." AND O.status=0 AND O.order_id=".$order_id;
                  $this->db->query($bal_sql);
                }
              }
            }
          }

          //Trasaction End
          $this->db->trans_complete();
          if ($this->db->trans_status() === FALSE )
          {
            $this->db->trans_rollback();
          }
          else
          {
            $this->db->trans_commit();
          }
        }
        catch(Exception $e){
          $this->db->trans_rollback();
        }
      }
    }
    
    return true;
  }

  /**
   * Function used for process financial year net winning settlement
   * @param int $fy
   * @return boolean
   */
  public function fy_settlement()
  {
    die("Disabled");
    $tds = $this->app_config['allow_tds']['key_value'] && isset($this->app_config['allow_tds']['custom_data']) ? $this->app_config['allow_tds']['custom_data'] : array();
    if(empty($tds)){
      return false;
    }
    if(isset($tds['indian']) && $tds['indian'] == 0){
      return false;
    }

    //echo "<pre>";print_r($tds);
    $fy_source = 535;
    $current_date = format_date();
    $current_year = date("Y",strtotime($current_date));
    $current_month = date("m",strtotime($current_date));
    if($current_month < 4) {
        $current_year--;
    }
    $fy = ($current_year - 1)."-".substr($current_year,-2);
    $custom_data = array("fy"=>$fy,"tds_rate"=>$tds['percent']);
    $this->db_user->select("U.user_id,U.winning_balance,U.net_winning", FALSE)
        ->from(USER.' AS U')
        ->where('U.net_winning != ',0);
    $result = $this->db_user->get()->result_array();
    //echo "<pre>";print_r($result);die;
    $tds_txn = array();
    foreach($result as $row){
      $tds_val = 0;
      if($row['net_winning'] > 0){
        $tds_val = number_format((($row['net_winning'] * $tds['percent']) / 100),2,'.','');
      }
      $tds_row = array();
      $tds_row['custom_data'] = $custom_data;
      $tds_row['custom_data']['net_winning'] = $row['net_winning'];
      $tds_row["order_unique_id"] = $this->_generate_order_unique_key();
      $tds_row["real_amount"] = 0;
      $tds_row["bonus_amount"] = 0;
      $tds_row["winning_amount"] = $row['net_winning'];
      $tds_row["points"] = 0;
      $tds_row["tds"] = $tds_val;
      $tds_row["source"] = $fy_source;
      $tds_row["source_id"] = 0;
      $tds_row["user_id"] = $row['user_id'];
      $tds_row["type"] = 1;
      $tds_row["status"] = 0;
      $tds_row["custom_data"] = json_encode($tds_row['custom_data']);
      $tds_row["date_added"] = $current_date;
      $tds_row["modified_date"] = $current_date;

      $tds_txn[] = $tds_row;
    }

    //echo "<pre>";print_r($tds_txn);die;
    if(!empty($tds_txn)){
      try{
        $this->db = $this->db_user;
        //Start Transaction
        $this->db->trans_strict(TRUE);
        $this->db->trans_start();
        
        $tds_txn_arr = array_chunk($tds_txn, 999);
        foreach($tds_txn_arr as $tds_txn_data){
          $this->insert_ignore_into_batch(ORDER, $tds_txn_data);
        }

        $netwin_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id 
            SET U.winning_balance=(U.winning_balance - O.tds),U.net_winning = 0,O.status=1 
            WHERE O.status=0 AND O.source = ".$fy_source;
        $this->db->query($netwin_sql);

        //Trasaction End
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE )
        {
          $this->db->trans_rollback();
        }
        else
        {
          $this->db->trans_commit();
        }
      }
      catch(Exception $e){
        $this->db->trans_rollback();
      }
    }
    return true;
  }

  /**
   * function used for send winning notification
   * @param void
   * @return boolean
   */
  public function match_prize_distribute_notification() 
  {
    $result = $this->db_fantasy->select("C.currency_type,LM.user_id,C.contest_id,C.collection_master_id,C.contest_name,C.status, C.contest_unique_id, C.size, C.prize_pool, C.entry_fee,LMC.is_winner,LMC.game_rank, LM.lineup_master_id, LMC.lineup_master_contest_id, C.prize_type,GROUP_CONCAT(LMC.lineup_master_contest_id) as lineup_master_contest_ids,GROUP_CONCAT(LMC.game_rank) as user_rank_list,GROUP_CONCAT(LM.team_name) as team_name,C.league_id,CS.season_id,C.prize_distibution_detail,CM.collection_name,CM.season_game_count,C.sports_id",false)
                  ->from(CONTEST . " C")
                  ->join(COLLECTION_MASTER . " CM", "CM.collection_master_id = C.collection_master_id", "INNER")
                  ->join(COLLECTION_SEASON . " CS", "CM.collection_master_id = CS.collection_master_id", "INNER")
                  ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.contest_id = C.contest_id", "INNER")
                  ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
                  ->where("C.status", 3)
                  ->where("LMC.is_winner", 1)
                  ->where("C.is_win_notify", 0)
                  ->group_by("CM.collection_master_id,C.contest_id,LM.user_id")
                  ->order_by("LM.user_id,C.contest_id,C.collection_master_id")
                  ->get()
                  ->result_array();
    //echo "<pre>";print_r($result);die;
    $match_data = array();
    $notification_data = array();
    if (!empty($result)) 
    {   
      foreach ($result as $res) 
      {
        if(!isset($match_data[$res['season_id']]) || empty($match_data[$res['season_id']]))
        {
          if($res['sports_id'] == MOTORSPORT_SPORTS_ID){
            $season_data = $this->db_fantasy->select("S.season_id,S.season_game_uid,S.season_scheduled_date,L.league_abbr,L.league_name,S.tournament_name, 1 as is_tour_game",FALSE)
                    ->from(SEASON . " AS S")
                    ->join(LEAGUE . " AS L", "L.league_id = S.league_id", "INNER")
                    ->where("S.season_id",$res['season_id'])
                    ->where("S.league_id",$res['league_id'])
                    ->get()
                    ->row_array();
          }else{
            $season_data = $this->db_fantasy->select("S.season_id,S.season_game_uid,T1.team_uid as home_uid,T2.team_uid as away_uid,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,S.season_scheduled_date,L.league_abbr,L.league_name,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag",FALSE)
                    ->from(SEASON . " AS S")
                    ->join(LEAGUE . " AS L", "L.league_id = S.league_id", "INNER")
                    ->join(TEAM.' T1','T1.team_id=S.home_id','INNER')
                    ->join(TEAM.' T2','T2.team_id=S.away_id','INNER')
                    ->where("S.season_id",$res['season_id'])
                    ->where("S.league_id",$res['league_id'])
                    ->get()->row_array();

            $season_data['home_flag'] = get_image(0,$season_data['home_flag']);
            $season_data['away_flag'] = get_image(0,$season_data['away_flag']);
            $season_data['is_tour_game'] = 0;
          }
          $match_data[$res['season_id']] = $season_data;
        }

        $lineup_master_contest_ids = explode(',', $res['lineup_master_contest_ids']);
        $user_rank_list = explode(',', $res['user_rank_list']);
        $team_names = explode(',', $res['team_name']);
        $team_names = array_combine($lineup_master_contest_ids,$team_names);
        $team_ranks = array_combine($lineup_master_contest_ids,$user_rank_list);
        //echo "<pre>";print_r($team_ranks);die;
        $sql = $this->db_user->select("O.order_id,O.real_amount, O.bonus_amount, O.winning_amount,O.points, U.email,U.user_name,O.source_id,O.prize_image,O.custom_data")
                ->from(ORDER . " O")
                ->join(USER . " U", "U.user_id = O.user_id", "INNER")
                ->where("O.user_id", $res['user_id'])
                ->where("O.source", PRIZE_WON_DESCRIPTION)
                ->where_in("O.source_id", $lineup_master_contest_ids)
                ->get();
        $order_info = $sql->result_array();
        //echo "<pre>";print_r($order_info);die;
        if (!empty($order_info)) 
        {
          $ord_custom_data = json_decode($order_info[0]['custom_data'],TRUE);
          if ($order_info[0]['bonus_amount'] > 0 || $order_info[0]['winning_amount'] > 0 || $order_info[0]['points'] > 0 || !empty($ord_custom_data))
          {
            // Game table update is_win_notify 1
            $this->db_fantasy->where('contest_id', $res['contest_id']);
            $this->db_fantasy->update(CONTEST, array('is_win_notify' => '1', 'modified_date' => format_date()));
            if(!isset($notification_data[$res['user_id']."_".$res['collection_master_id']]) 
                || empty($notification_data[$res['user_id']."_".$res['collection_master_id']]))
            {
              $user_temp_data = array();
              $user_temp_data['source_id'] = $res['collection_master_id'];
              $user_temp_data['user_id'] = $res['user_id'];
              $user_temp_data['email'] = $order_info[0]['email'];
              $user_temp_data['user_name'] = $order_info[0]['user_name'];
              $user_temp_data['match_data'] = $match_data[$res['season_id']];
              $user_temp_data['collection_name'] = $res['collection_name'];
              $user_temp_data['contest_data'] = array();
              //added for multigame module check in mail 
              $user_temp_data['season_game_count'] = $res['season_game_count'];
              $notification_data[$res['user_id']."_".$res['collection_master_id']] = $user_temp_data;
            }

            foreach ($order_info as $key => $value) 
            {
              $amount = 0;
              switch($res['prize_type'])
              {
                  case 0 :
                      $amount = $value['bonus_amount'];
                      break;
                  case 1 :
                      $amount = $value['winning_amount'];
                      break;
              }
              $prize_distibution_detail = json_decode($res['prize_distibution_detail'],true);
              $total_winner = 1;
              if(isset($prize_distibution_detail[count($prize_distibution_detail) - 1]['max'])){
                  $total_winner = $prize_distibution_detail[count($prize_distibution_detail) - 1]['max'];
              }
              $contest_data = array();
              $contest_data['contest_id'] = $res['contest_id'];
              $contest_data['user_id'] = $res['user_id'];
              $contest_data['contest_unique_id'] = $res['contest_unique_id'];
              $contest_data['contest_name'] = $res['contest_name'];
              $contest_data['collection_name'] = $res['collection_name'];
              $contest_data['user_rank'] = isset($team_ranks[$value['source_id']]) ? $team_ranks[$value['source_id']] : $res['game_rank'];
              $contest_data['size'] = $res['size'];
              $contest_data['prize_pool'] = $res['prize_pool'];
              $contest_data['entry_fee'] = $res['entry_fee'];
              $contest_data['currency_type'] = $res['currency_type'];
              $contest_data['team_name'] = $team_names[$value['source_id']];
              $contest_data['prize_type'] = $res['prize_type'];
              $contest_data['amount'] = $value['winning_amount'];
              $contest_data['bonus'] = $value['bonus_amount'];
              $contest_data['points'] = $value['points'];
              $contest_data['custom_data'] = json_decode($value['custom_data'],TRUE);
              $contest_data['total_winner'] = $total_winner;
              $notification_data[$res['user_id']."_".$res['collection_master_id']]['contest_data'][] = $contest_data;
            }
          }
        }
      }
    }
    //echo "<pre>";print_r($notification_data);die;
    foreach($notification_data as $notification)
    {
        /* Send Notification */
        $notify_data = array();
        $notify_data['notification_type'] = 3; //3-GameWon
        $notify_data['notification_destination'] = 7; //web, push, email
        $notify_data["source_id"] = $notification['source_id'];
        $notify_data["user_id"] = $notification['user_id'];
        $notify_data["to"] = $notification['email'];
        $notify_data["user_name"] = $notification['user_name'];
        $notify_data["added_date"] = format_date();
        $notify_data["modified_date"] = format_date();
        $notify_data["subject"] = "Wohoo! You just WON!";

        $content = array();
        $content['match_data'] = $notification['match_data'];
        $content['collection_name'] = $notification['collection_name'];
        $content['season_game_count'] = $notification['season_game_count'];
        $content['contest_data'] = $notification['contest_data'];
        $content['int_version'] = $this->app_config['int_version']['key_value'];
        $notify_data["content"] = json_encode($content);
        $this->load->model('notification/Notify_nosql_model');
        $this->Notify_nosql_model->send_notification($notify_data); 
    }
    return;
  }

  /**
  * Used for revert contest prize amount from user wallet
  * @param int $contest_id
  * @return boolean
  */
  public function revert_contest_prize($contest_id){
    if(!$contest_id){
      return false;
    }

    $winner_data = $this->db_fantasy->select('C.contest_id,C.season_scheduled_date,C.entry_fee,C.status,C.minimum_size,C.size,C.prize_pool,LMC.lineup_master_contest_id,LMC.prize_data,LMC.is_winner', FALSE)
                ->from(CONTEST . ' AS C')
                ->join(LINEUP_MASTER_CONTEST . ' AS LMC', 'ON LMC.contest_id = C.contest_id','INNER')
                ->where('C.status', 3)
                ->where('C.contest_id', $contest_id)
                ->where('LMC.is_winner', 1)
                ->order_by("LMC.lineup_master_contest_id","ASC")
                ->get()
                ->result_array();
    if(!empty($winner_data)){
      $contest_complete = 0;
      foreach($winner_data as $winner){
        $is_complete = 0;
        //Start Transaction
        $this->db_user->trans_strict(TRUE);
        $this->db_user->trans_start();

        $order_info = $this->db_user->select("O.*")
                      ->from(ORDER . " O")
                      ->where("O.source", 3)
                      ->where("O.source_id", $winner['lineup_master_contest_id'])
                      ->get()
                      ->row_array();
        if(!empty($order_info)){
          if(isset($order_info['winning_amount']) && $order_info['winning_amount'] > 0){
            $this->db_user->set('winning_balance', 'winning_balance - '.$order_info['winning_amount'], FALSE);
          }
          if(isset($order_info['bonus_amount']) && $order_info['bonus_amount'] > 0){
            $this->db_user->set('bonus_balance', 'bonus_balance - '.$order_info['bonus_amount'], FALSE);
          }
          if(isset($order_info['points']) && $order_info['points'] > 0){
            $this->db_user->set('point_balance', 'point_balance - '.$order_info['points'], FALSE);
          }
          $this->db_user->where('user_id', $order_info['user_id']);
          $this->db_user->update(USER);
          $rs = $this->db_user->affected_rows();
          if($rs){
            $custom_data = json_decode($order_info['custom_data'],TRUE);
            //user txn data
            $order_data = array();
            $order_data["order_unique_id"] = $this->_generate_order_unique_key();
            $order_data["user_id"]        = $order_info['user_id'];
            $order_data["source"]         = 20;
            $order_data["source_id"]      = $order_info['source_id'];
            $order_data["reference_id"]   = $order_info['reference_id'];
            $order_data["season_type"]    = 1;
            $order_data["type"]           = 1;
            $order_data["status"]         = 1;
            $order_data["real_amount"]    = $order_info['real_amount'];
            $order_data["bonus_amount"]   = $order_info['bonus_amount'];
            $order_data["winning_amount"] = $order_info['winning_amount'];
            $order_data["points"] = $order_info['points'];
            $order_data["custom_data"] = json_encode($custom_data);
            $order_data["plateform"]      = PLATEFORM_FANTASY;
            $order_data["date_added"]     = format_date();
            $order_data["modified_date"]  = format_date();
            $this->db_user->insert(ORDER, $order_data);
          }
        }

        //Trasaction End
        $this->db_user->trans_complete();
        if ($this->db_user->trans_status() === FALSE )
        {
          $this->db_user->trans_rollback();
        }
        else
        {
          $this->db_user->trans_commit();
          $is_complete = 1;
        }

        if($is_complete == 1){
          //update winner entry
          $this->db_fantasy->set('is_winner', '0');
          $this->db_fantasy->set('prize_data', null);
          $this->db_fantasy->where("lineup_master_contest_id",$winner['lineup_master_contest_id']);
          $this->db_fantasy->update(LINEUP_MASTER_CONTEST);

          $contest_complete = 1;
        }

      }

      if($contest_complete == 1){
        //update old winning source status
        $this->db_user->set('source', '21');
        $this->db_user->where("source","3");
        $this->db_user->where("type","0");
        $this->db_user->where("reference_id",$contest_id);
        $this->db_user->update(ORDER);

        //update contest status
        $this->db_fantasy->set('status', '0');
        $this->db_fantasy->where("contest_id",$contest_id);
        $this->db_fantasy->update(CONTEST);

        //flush cache
        $this->load->model('Nodb_model');
        $this->Nodb_model->flush_cache_data();
      }
    }
    return true;
  }

  /**
  * Function used for push contest for gst report
  * @param 
  * @return boolean
  */
  public function push_contest_for_gst_report()
  {
    $current_date = format_date();
    $contest_list = $this->db_fantasy->select('C.contest_id,C.contest_name,C.entry_fee,C.prize_pool,C.site_rake,C.prize_type,CM.collection_name,CM.collection_master_id', FALSE)
                      ->from(CONTEST . ' AS C')
                      ->join(COLLECTION_MASTER." AS CM","CM.collection_master_id = C.collection_master_id","INNER")
                      ->where('C.currency_type', 1)
                      ->where('C.entry_fee > ',0)
                      ->where('C.site_rake > ',0)
                      ->where('C.status', 3)
                      ->where('C.is_gst_report',0)
                      ->where('C.season_scheduled_date < ',$current_date)
                      ->order_by('C.contest_id','ASC')
                      ->get()
                      ->result_array();
    //echo "<pre>";print_r($contest_list);die;
    foreach($contest_list as $contest){
      $this->load->helper('queue');
      $server_name = get_server_host_name();
      $content = array();
      $content['url'] = $server_name."/cron/dfs/generate_gst_report/".$contest['contest_id'];
      add_data_in_queue($content,'gst');
    }
    return true;
  }

  /**
  * Function used for generate gst report
  * @param int $contest_id
  * @return boolean
  */
  public function generate_gst_report($contest_id)
  {
    if(!$contest_id){
      return false;
    }

    $current_date = format_date();
    $contest = $this->db_fantasy->select('C.contest_id,C.contest_name,C.entry_fee,C.prize_pool,C.site_rake,C.prize_type,C.minimum_size as min_size,C.size as max_size,C.total_user_joined,CM.collection_name,CM.collection_master_id,CM.season_scheduled_date', FALSE)
                      ->from(CONTEST . ' AS C')
                      ->join(COLLECTION_MASTER." AS CM","CM.collection_master_id = C.collection_master_id","INNER")
                      ->where('C.currency_type', 1)
                      ->where('C.entry_fee > ',0)
                      ->where('C.site_rake > ',0)
                      ->where('C.status', 3)
                      ->where('C.is_gst_report',0)
                      ->where('C.season_scheduled_date < ',$current_date)
                      ->where('C.contest_id', $contest_id)
                      ->limit(1)
                      ->get()
                      ->row_array();
    //echo "<pre>";print_r($contest);
    if(!empty($contest))
    {
      $portal_state_id = isset($this->app_config['allow_gst']['custom_data']['state_id']) ? $this->app_config['allow_gst']['custom_data']['state_id'] : 0;
      $cgst_value = 9;//value in percentage
      $sgst_value = 9;
      $igst_value = 18;
      $match_id = $contest['collection_master_id'];
      $contest_id = $contest['contest_id'];
      $site_rake = $contest['site_rake'];
      $match_name = $contest['collection_name'];
      $contest_name = $contest['contest_name'];
      $entry_fee = $contest['entry_fee'];
      $scheduled_date = $contest['season_scheduled_date'];
      $min_size = $contest['min_size'];
      $max_size = $contest['max_size'];
      $total_user_joined = $contest['total_user_joined'];
      $prize_pool = $contest['prize_pool'];
      $this->db = $this->db_user;
      $users_list = $this->db_user->select("O.order_id,O.real_amount,O.winning_amount,O.source_id,O.reference_id,O.type,O.date_added,U.user_id,U.user_name,U.pan_no,IFNULL(U.master_state_id,'0') as state_id,IFNULL(MS.name,'') as state_name",FALSE)
                          ->from(ORDER . " AS O")
                          ->join(USER." AS U","U.user_id = O.user_id","INNER")
                          ->join(MASTER_STATE." AS MS","MS.master_state_id = U.master_state_id","LEFT")
                          ->where('O.reference_id', $contest_id)
                          ->where('O.type', 1)
                          ->where('O.status', 1)
                          ->where('O.source', 1)
                          ->get()
                          ->result_array();
      //echo "<pre>";print_r($users_list);die;
      $gst_data = array();
      foreach($users_list as $row){
        $cgst = $sgst = $igst = 0;
        $txn_amount = number_format(($row['real_amount'] + $row['winning_amount']),"2",".","");
        if($txn_amount > 0){
          //taxable amount
          $total_rake_amount = number_format((($txn_amount/100)*$site_rake),2,".","");
          $rake_amount = number_format((($total_rake_amount*100)/118),2,".","");
          if($row['state_id'] == $portal_state_id){
            $cgst = number_format((($rake_amount/100)*$cgst_value),2,".","");
            $sgst = number_format((($rake_amount/100)*$sgst_value),2,".","");
          }else{
            $igst = number_format((($rake_amount/100)*$igst_value),2,".","");
          }
          //echo $cgst."===".$sgst."===".$igst;die;
          $tmp_arr = array();
          $tmp_arr['user_id'] = $row['user_id'];
          $tmp_arr['state_id'] = $row['state_id'];
          $tmp_arr['order_id'] = $row['order_id'];
          $tmp_arr['match_id'] = $match_id;
          $tmp_arr['contest_id'] = $contest_id;
          $tmp_arr['lmc_id'] = $row['source_id'];
          $tmp_arr['user_name'] = $row['user_name'];
          $tmp_arr['pan_no'] = $row['pan_no'];
          $tmp_arr['state_name'] = $row['state_name'];
          $tmp_arr['match_name'] = $match_name;
          $tmp_arr['contest_name'] = $contest_name;
          $tmp_arr['scheduled_date'] = $scheduled_date;
          $tmp_arr['txn_date'] = $row['date_added'];
          $tmp_arr['txn_amount'] = $txn_amount;
          $tmp_arr['site_rake'] = $site_rake;
          $tmp_arr['entry_fee'] = $entry_fee;
          $tmp_arr['rake_amount'] = $rake_amount;//taxable amount
          $tmp_arr['cgst'] = $cgst;
          $tmp_arr['sgst'] = $sgst;
          $tmp_arr['igst'] = $igst;
          $tmp_arr['status'] = 1;
          $tmp_arr['is_invoice_sent'] = 1;
          $tmp_arr['date_added'] = $current_date;
          $tmp_arr['min_size'] = $min_size;
          $tmp_arr['max_size'] = $max_size;
          $tmp_arr['total_user_joined'] = $total_user_joined;
          $tmp_arr['prize_pool'] = $prize_pool;
          $gst_data[] = $tmp_arr;
        }
      }
      //echo "<pre>";print_r($gst_data);die;
      if(!empty($gst_data)){
        //Start Transaction
        $this->db->trans_strict(TRUE);
        $this->db->trans_start();
        
        $gst_data_arr = array_chunk($gst_data, 999);
        foreach($gst_data_arr as $chunk_data){
          $this->replace_into_batch(GST_REPORT, $chunk_data);
        }

        //Trasaction End
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE )
        {
          $this->db->trans_rollback();
        }
        else
        {
          $this->db->trans_commit();
          //For primary key setting
          $this->set_auto_increment_key(GST_REPORT,'invoice_id');

          $this->db_fantasy->where('contest_id', $contest_id);
          $this->db_fantasy->update(CONTEST, array('is_gst_report' => '1'));
        }
      }else{
        //update contest gst status as failed
        $this->db_fantasy->where('contest_id', $contest_id);
        $this->db_fantasy->update(CONTEST, array('is_gst_report' => '2'));
      }
    }
    return true;
  }

  public function validate_match_team_data($post_data){
    if(empty($post_data)){
      return false;
    }

    $collection_master_id = $post_data['collection_id'];
    $total_team = $total_player = 0;
    $collection_info = $this->db_fantasy->select("*")
                  ->from(COLLECTION_MASTER)
                  ->where("collection_master_id",$collection_master_id)
                  ->get()
                  ->row_array();
    if(empty($collection_info)){
      echo "Collection data not found.";die;
    }

    $league_info = $this->db_fantasy->select("S.team_player_count,S.sports_id,L.league_id")
                  ->from(LEAGUE." AS L")
                  ->join(MASTER_SPORTS. ' S','S.sports_id=L.sports_id')
                  ->where("L.league_id",$collection_info['league_id'])
                  ->get()
                  ->row_array();
    $total_team_player = 11;
    if(isset($league_info['team_player_count'])){
      $total_team_player = $league_info['team_player_count'];
    }

    $team_info = $this->db_fantasy->select("count(lineup_master_id) as total")
                  ->from(LINEUP_MASTER)
                  ->where("collection_master_id",$collection_master_id)
                  ->get()
                  ->row_array();
    if(!empty($team_info)){
      $total_team = isset($team_info['total']) ? $team_info['total'] : 0;
    }

    $lineup_table = LINEUP."_".$collection_master_id;
    if($this->db_fantasy->table_exists($lineup_table)) {
      $player_info = $this->db_fantasy->select("count(lineup_id) as total")
                  ->from($lineup_table)
                  ->get()
                  ->row_array();
      if(!empty($player_info)){
        $total_player = isset($player_info['total']) ? $player_info['total'] : 0;
      }
    }

    $total_pl = $total_team_player * $total_team;
    if($collection_info['is_lineup_processed'] == 0 && $total_pl == $total_player){
      $this->db_fantasy->where("collection_master_id",$collection_master_id);
      $this->db_fantasy->update(COLLECTION_MASTER,array("is_lineup_processed"=>"1"));
    }

    $cl_info = array();
    $cl_info['teams'] = $total_team;
    $cl_info['players'] = $total_player;
    $cl_info['expected_player'] = $total_pl;
    echo "<pre>";print_r($cl_info);die;
  }

  /**
   * Used for auto publish fixture
   * @param int $sports_id
   * @return boolean
   */
  public function auto_publish_fixture($sports_id)
  {
      if(!$sports_id){
          return false;
      }
      $current_date = format_date();
      $future_date = date("Y-m-d H:i:s", strtotime($current_date . " +3 days"));
      $this->db_fantasy->select("S.season_id,S.league_id,S.format,S.season_scheduled_date,S.2nd_inning_date,S.home_id,S.away_id,S.status,S.is_published,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IFNULL(CM.collection_master_id,0) as collection_master_id,COUNT(DISTINCT player_team_id) as total_pl,L.league_name",false)
          ->from(SEASON." AS S")
          ->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
          ->join(PLAYER_TEAM.' PT','PT.season_id = S.season_id AND PT.feed_verified=1','INNER')
          ->join(TEAM.' T1','T1.team_id = S.home_id','INNER')
          ->join(TEAM.' T2','T2.team_id = S.away_id','INNER')
          ->join(COLLECTION_SEASON.' CS','CS.season_id = S.season_id',"LEFT")
          ->join(COLLECTION_MASTER.' CM','CM.collection_master_id = CS.collection_master_id AND CM.season_game_count=1',"LEFT")
          ->where("L.sports_id",$sports_id)
          ->where("L.auto_published","1")
          ->where("S.is_published","0")
          ->where("S.season_scheduled_date > ",$current_date)
          ->where("S.season_scheduled_date < ",$future_date)
          ->group_by("S.season_id")
          ->having("collection_master_id","0")
          ->having("total_pl >= ","15")
          ->limit(5)
          ->order_by("S.season_scheduled_date","ASC");

      $sql = $this->db_fantasy->get();
      $match_list = $sql->result_array();
      //echo "<pre>";print_r($match_list);die;
      if(!empty($match_list)){
          $this->db_fantasy->select('CT.*,IFNULL(CT.template_title,CT.template_name) as template_title,IFNULL(CT.sponsor_name,"") as sponsor_name,IFNULL(CT.sponsor_logo,"") as sponsor_logo,IFNULL(CT.sponsor_link,"") as sponsor_link', FALSE)
              ->from(CONTEST_TEMPLATE." AS CT")
              ->where('CT.auto_published',"1")
              ->where('CT.sports_id',$sports_id)
              ->group_by('CT.contest_template_id');
          $template_list = $this->db_fantasy->get()->result_array();
          //echo "<pre>";print_r($template_list);die;

          if(empty($template_list)){
              return false;
          }
          $result = 0;
          foreach($match_list as $season){
              $season_id = $season['season_id'];
              $league_id = $season['league_id'];
              $season_scheduled_date = $season['season_scheduled_date'];

              //check and create collection
              $this->db_fantasy->select("CM.collection_master_id", FALSE);
              $this->db_fantasy->from(COLLECTION_SEASON." AS CS");
              $this->db_fantasy->join(COLLECTION_MASTER." AS CM","CM.collection_master_id = CS.collection_master_id","INNER");
              $this->db_fantasy->where('CS.season_id',$season_id);
              $this->db_fantasy->where("CM.season_game_count","1");
              $query = $this->db_fantasy->get(); 
              $collection_info = $query->row_array();
              if(empty($collection_info)){
                  try{
                      //Start Transaction
                      $this->db_fantasy->trans_strict(TRUE);
                      $this->db_fantasy->trans_start();

                      //for second inning date
                      $secong_inning_date = "";
                      $allow_2nd_inning = isset($this->app_config['allow_2nd_inning']) ? $this->app_config['allow_2nd_inning']['key_value'] : 0;
                      if($allow_2nd_inning == 1 && in_array($season['format'],array(1,3))){
                          if($season['2nd_inning_date'] != ""){
                              $secong_inning_date = $season['2nd_inning_date'];
                          }else{
                              $second_inning_interval = second_inning_game_interval($season['format']);
                              $secong_inning_date = date("Y-m-d H:i:s",strtotime($season['season_scheduled_date'].' +'.$second_inning_interval.' minutes'));
                          }
                      }

                      $collection = array();
                      $collection["league_id"] = $season['league_id'];
                      $collection["collection_name"] = $season['home'].' vs '.$season['away'];
                      $collection["season_scheduled_date"] = $season['season_scheduled_date'];
                      if($secong_inning_date != ""){
                        $collection["2nd_inning_date"] = $secong_inning_date;
                      }
                      $collection["deadline_time"] = '0';
                      $collection["added_date"] = $current_date;
                      $collection["modified_date"] = $current_date;
                      $this->db_fantasy->insert(COLLECTION_MASTER,$collection);
                      $collection_master_id = $this->db_fantasy->insert_id();
                      if($collection_master_id){
                          $collection_season_data = array();
                          $collection_season_data['collection_master_id'] = $collection_master_id;
                          $collection_season_data['season_id'] = $season_id;
                          $collection_season_data['season_scheduled_date'] = $season['season_scheduled_date'];
                          $collection_season_data['added_date'] = $current_date;
                          $collection_season_data['modified_date'] = $current_date;
                          $this->db_fantasy->insert(COLLECTION_SEASON,$collection_season_data);

                          //update for free contest
                          $update_data = array("is_published"=>1,"is_salary_changed"=>1,'modified_date'=>$current_date);
                          $this->db_fantasy->where("season_id",$season_id);
                          $this->db_fantasy->update(SEASON, $update_data);

                          //update season player
                          $pl_update_data = array("is_published"=>1);
                          $this->db_fantasy->where("season_id",$season_id);
                          $this->db_fantasy->where("feed_verified","1");
                          $this->db_fantasy->update(PLAYER_TEAM, $pl_update_data);

                          //update dfs tournament fixture cm id
                          $this->db_fantasy->where("season_id",$season_id);
                          $this->db_fantasy->where("cm_id","0");
                          $this->db_fantasy->update(TOURNAMENT_SEASON,array("cm_id"=>$collection_master_id));

                          //Trasaction End
                          $this->db_fantasy->trans_complete();
                          if ($this->db_fantasy->trans_status() === FALSE )
                          {
                              $this->db_fantasy->trans_rollback();
                              continue;
                          }
                          else
                          {
                              $this->db_fantasy->trans_commit();
                          }
                      }else{
                          continue;
                      }
                  }
                  catch(Exception $e){
                      $this->db_fantasy->trans_rollback();
                      continue;
                  }
              }else{
                  $collection_master_id = $collection_info['collection_master_id'];
              }

              //create template contest
              if($collection_master_id){
                  foreach($template_list as $game_data) {
                      $game_data['contest_unique_id'] = random_string('alnum', 9);
                      $game_data['league_id'] = $league_id;
                      $game_data['contest_name'] = $game_data['template_name'];
                      $game_data['contest_title'] = isset($game_data['template_title']) ? $game_data['template_title'] : "";
                      $game_data['collection_master_id'] = $collection_master_id;
                      $game_data['season_scheduled_date'] = $season_scheduled_date;
                      $game_data['status'] = 0;
                      $game_data['added_date'] = $current_date;
                      $payout_data = json_decode($game_data['prize_distibution_detail'],TRUE);
                      $max_prize_pool = 0;
                      //change values for percentage case
                      if(isset($game_data['max_prize_pool']) && $game_data['max_prize_pool'] > 0){
                          $max_prize_pool = $game_data['max_prize_pool'];
                      }else{
                          foreach($payout_data as $key=>$prize){
                              $amount = $prize['amount'];
                              if(isset($prize['prize_type']) && $prize['prize_type'] == 1){
                                  if(isset($prize['max_value'])){
                                      $mx_amount = $prize['max_value'];
                                  }else{
                                      $mx_amount = $amount;
                                  }
                                  $max_prize_pool = $max_prize_pool + $mx_amount;
                              }
                          }
                      }
                      $game_data['max_prize_pool'] = $max_prize_pool;
                      if ($game_data['is_auto_recurring'] == 1) {
                          $game_data['base_prize_details'] = json_encode(array("prize_pool" => $game_data['prize_pool'], "prize_distibution_detail" => $game_data['prize_distibution_detail']));
                      }

                      $tmp_game_data = $game_data;
                      $tmp_game_data['total_user_joined'] = $tmp_game_data['minimum_size'];
                      $current_prize = reset_contest_prize_data($tmp_game_data);
                      $game_data['current_prize'] = json_encode($current_prize);

                      unset($game_data['template_name']);
                      unset($game_data['template_title']);
                      unset($game_data['template_description']);
                      unset($game_data['auto_published']);
                      $this->db_fantasy->insert(CONTEST,$game_data);
                      $result = $this->db_fantasy->insert_id();
                  }
              }
          }

          if($result){
              return true;
          }else{
              return false;
          }
      }
  }

  public function process_affiliate_cb($cm_id){
    if(!$cm_id){
      return false;
    }

    $check_exist = $this->db_user->select('count(user_id) as total')
                          ->from(AFFILIATE_REPORT." AS AR")
                          ->where('AR.entity_id', $cm_id)
                          ->get()
                          ->row_array();
    //echo "<pre>";print_r($check_exist);die;
    if(!empty($check_exist) && $check_exist['total'] > 0){
      return false;
    }
    $this->db_fantasy->select("1 as module_type,C.sports_id,CM.league_id,CM.collection_master_id as entity_id,IFNULL(L.league_display_name,L.league_name) AS league_name,CM.collection_name as entity_name,CM.season_scheduled_date as schedule_date,SUM(C.total_user_joined-C.total_system_user) AS total_user,round(SUM(C.entry_fee * (C.total_user_joined-C.total_system_user))) as entry_fee,round(SUM(C.entry_fee * (C.total_user_joined-C.total_system_user) * C.site_rake / 100),2) AS rake_amount", FALSE)
          ->from(COLLECTION_MASTER.' AS CM')
          ->join(CONTEST.' AS C', 'C.collection_master_id = CM.collection_master_id', 'INNER')
          ->join(LEAGUE.' AS L', 'L.league_id = CM.league_id', 'INNER')
          ->where('C.site_rake > ',0)
          ->where("C.guaranteed_prize != ",2)
          ->where("C.status",3)
          ->where("C.currency_type",1)
          ->where("C.collection_master_id", $cm_id)
          ->group_by("C.collection_master_id");
    $result = $this->db_fantasy->get()->row_array();
    if(!empty($result)){
      $user_list = $this->db_user->select('user_id,user_name,site_rake_commission,commission_type')
                          ->from(USER." AS U")
                          ->where('U.is_affiliate',"1")
                          ->where('U.site_rake_status',"1")
                          ->where('U.site_rake_commission > ',"0")
                          ->get()
                          ->result_array();
      //echo "<pre>";print_r($user_list);
      $user_rpt_data = array();
      $user_txn_data = array();
      foreach($user_list as $row){
        $tmp_data = $result;
        $tmp_data['user_id'] = $row['user_id'];
        $tmp_data['user_rake'] = $row['site_rake_commission'];
        $user_amount = number_format(($tmp_data['rake_amount'] * $tmp_data['user_rake'])/100,"2",".","");
        $tmp_data['user_amount'] = $user_amount;
        $tmp_data['modified_date'] = format_date();
        $user_rpt_data[] = $tmp_data;

        $real_amount = $user_amount;
        $winning_amount = 0;
        if($row['commission_type'] == "4"){
          $real_amount = 0;
          $winning_amount = $user_amount;
        }

        $custom_data = array("league_name"=>$tmp_data['league_name'],"match"=>$tmp_data['entity_name'],"rake_amount"=>$tmp_data['rake_amount'],"user_rake_per"=>$tmp_data['user_rake']);
        //user txn data
        $order_data = array();
        $order_data["order_unique_id"] = $this->_generate_order_unique_key();
        $order_data["user_id"]        = $row['user_id'];
        $order_data["source"]         = 556;
        $order_data["source_id"]      = $cm_id;
        $order_data["reference_id"]   = 0;
        $order_data["season_type"]    = 1;
        $order_data["type"]           = 0;
        $order_data["status"]         = 0;
        $order_data["real_amount"]    = $real_amount;
        $order_data["bonus_amount"]   = 0;
        $order_data["winning_amount"] = $winning_amount;
        $order_data["points"] = 0;
        $order_data["custom_data"] = json_encode($custom_data);
        $order_data["plateform"]      = PLATEFORM_FANTASY;
        $order_data["date_added"]     = format_date();
        $order_data["modified_date"]  = format_date();
        $user_txn_data[] = $order_data;
      }

      //echo "<pre>";print_r($user_rpt_data);
      //echo "<pre>";print_r($user_txn_data);die;
      if(!empty($user_rpt_data)){
        try
        {
          $this->db = $this->db_user;
          //Start Transaction
          $this->db->trans_strict(TRUE);
          $this->db->trans_start();
          
          //save affiliate report
          $this->insert_ignore_into_batch(AFFILIATE_REPORT, $user_rpt_data);

          //save user transaction data
          $this->insert_ignore_into_batch(ORDER, $user_txn_data);

          $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount FROM ".$this->db->dbprefix(ORDER)." WHERE source = 556 AND type=0 AND status=0 AND source_id =".$cm_id." GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
            SET U.balance = (U.balance + OT.real_amount),U.winning_balance = (U.winning_balance + OT.winning_amount),O.status=1 
              WHERE O.source = 556 AND O.type=0 AND O.status=0 AND O.source_id = ".$cm_id." ";
          $this->db->query($bal_sql);

          //Trasaction End
          $this->db->trans_complete();
          if ($this->db->trans_status() === FALSE )
          {
            $this->db->trans_rollback();
          }
          else
          {
            $this->db->trans_commit();
          }
        } catch (Exception $e)
        {
          //echo 'Caught exception: '.  $e->getMessage(). "\n";
        }
      }
    }
    return true;
  }

}
