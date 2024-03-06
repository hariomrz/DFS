<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron_model extends MY_Model {  
  public $db_user;
  public function __construct() 
  {
    parent::__construct();
    $this->db_user = $this->load->database('user_db', TRUE);
    $this->livefantasy_db = $this->load->database('livefantasy_db', TRUE);
  }

  public function get_email_template_list(){

      $result = $this->db_user->select("template_name,template_path,notification_type,status,subject")
                  ->from(EMAIL_TEMPLATE)
                  ->where("status",1)
                  ->get()
                  ->result_array();

      return $result;
  }


  // For daily and custom date
  public function game_cancellation($is_recursive="0") {   
    set_time_limit(0);
    $current_date = format_date();
    $this->db = $this->livefantasy_db;
    // When no user joined contest and open contest will cancel directly because no further action required for those contest
    if($is_recursive == "0"){
      $collection = $this->db->select("GROUP_CONCAT(DISTINCT C.collection_id) as collection_ids")
         ->from(CONTEST." AS C")
         ->join(COLLECTION." AS CM", " CM.collection_id = C.collection_id", 'INNER')
         ->where("CM.status > ",0)
         ->where("C.status", 0)
         ->where("C.total_user_joined", 0)
         ->where("CM.season_scheduled_date < ", $current_date)
         ->get()
         ->row_array();
      if(!empty($collection) && $collection['collection_ids'] != ""){
        $where = array('status' => 0,'total_user_joined' => 0,'season_scheduled_date <' => $current_date ,'collection_id IN('.$collection['collection_ids'].')'=>NULL);
        $this->db->update(CONTEST, array('status' => 1), $where);
      }
    }
    $contest_list = $this->db->select("C.contest_id,C.contest_unique_id,C.contest_name,C.entry_fee,C.sports_id,C.collection_id,MG.group_name,C.currency_type")
         ->from(CONTEST." AS C")
         ->join(MASTER_GROUP . " AS MG", " MG.group_id = C.group_id", 'INNER')
        ->join(COLLECTION." AS CM", " CM.collection_id = C.collection_id", 'INNER')
        ->where("CM.status > ",0)
         ->where("C.status", 0)
         ->where("C.total_user_joined > ", 0)
         ->where("C.total_user_joined < ", 'minimum_size', FALSE)
         ->where("CM.season_scheduled_date < ", $current_date)
         ->limit(1)
         ->get()
         ->row_array();
    // echo $this->db->last_query(); 
    // echo "<pre>";print_r($contest_list);die;                                                     
    if (!empty($contest_list)) {
      $subject = "Contest Canceled - Insufficient Participation";
      $additional_data['contest_id'] = $contest_list['contest_id'];
      $additional_data['contest_name'] = $contest_list['contest_name'];
      $additional_data['cancel_reason'] = '';
      $additional_data['current_date'] = $current_date;
      $additional_data['notification_type'] = 621;
      $additional_data['subject'] = $subject;
      $additional_data['cancel_reason_type'] = 'due to insufficient participation';

      //refund for paid contest
      if($contest_list['entry_fee'] > 0){
        $this->process_game_cancellation($contest_list, $additional_data);
      }else{
        //update for free contest
        $this->db->where("contest_id",$contest_list['contest_id']);
        $this->db->update(USER_CONTEST, array('fee_refund' => 1));
        
        // Game table update cancel status
        $this->db->where('contest_id', $contest_list['contest_id']);
        $this->db->update(CONTEST, array('status' => 1, 'entry_fee' => 0, 'modified_date' => $additional_data['current_date'], 'cancel_reason' => $additional_data['cancel_reason']));
      }
      $this->match_cancel_notification($additional_data);

      $this->game_cancellation("1");
    }
    return;
  }

  /**
   * Cancel game 
   * @param array $data
   * @return boolean
  */
  function game_cancellation_by_id($data) {   
    set_time_limit(0);
    $contest_unique_id  = isset($data['contest_unique_id'])?$data['contest_unique_id']:0;
    $cancel_reason      = isset($data['cancel_reason'])?$data['cancel_reason']:'';
    $cancel_reason      = strip_tags($cancel_reason);
    if(!$contest_unique_id){
        return false;
    }
    $this->db = $this->livefantasy_db;
    $this->db->select('contest_id,contest_unique_id,contest_name,entry_fee,sports_id, collection_id,contest_template_id,total_user_joined')
        ->from(CONTEST)
        ->where('status', 0)
        ->where("contest_unique_id",$contest_unique_id);
    $this->db->limit(1);
    $contest_list = $this->db->get()->row_array();
    if(!empty($contest_list)) {
      //UPDATE contest status if participants 0
      if(isset($contest_list['total_user_joined']) && $contest_list['total_user_joined'] == "0") {

        $sports_id = $contest_list['sports_id'];
        $where = array('status' => 0,'total_user_joined' => 0,'contest_id' => $contest_list['contest_id']);
        $update_arr = array('status' => 1,'is_win_notify' => 1,'modified_date' => format_date(),'cancel_reason' => $cancel_reason);
        $this->db->update(CONTEST, $update_arr, $where);

        if (CACHE_ENABLE) {
          $this->flush_cache_data();
        }
      }else{
        $additional_data['contest_id'] = $contest_list['contest_id'];
        $additional_data['contest_name'] = $contest_list['contest_name'];
        $additional_data['cancel_reason'] = $cancel_reason;
        $additional_data['current_date'] = format_date();
        $additional_data['notification_type'] = 621;
        $additional_data['subject'] = 'Contest Canceled by Admin';
        $additional_data['cancel_reason_type'] = 'by admin';
        if($contest_list['entry_fee'] > 0){
          $this->process_game_cancellation($contest_list, $additional_data);
        }else{
          //update for free contest
          $this->db->where("contest_id",$contest_list['contest_id']);
          $this->db->update(USER_CONTEST, array('fee_refund' => 1));
          
          // Game table update cancel status
          $this->db->where('contest_id', $contest_list['contest_id']);
          $this->db->update(CONTEST, array('status' => 1, 'entry_fee' => 0, 'modified_date' => $additional_data['current_date'], 'cancel_reason' => $additional_data['cancel_reason']));
        }
        $this->match_cancel_notification($additional_data);
      }
    }
    return true;
  }

  function collection_cancel_by_id($data) {
    $collection_id = isset($data['collection_id'])?$data['collection_id']:0;        
    if(!$collection_id){
        return false;
    }
      
    $this->db->select('contest_id, contest_unique_id, contest_name, entry_fee, sports_id, collection_id')
            ->from(CONTEST)
            ->where('status', 0)
            ->where("collection_id",$collection_id);
    $query = $this->db->get();
    if($query->num_rows() > 0) {
        $contest_list = $query->result_array();
        if(!empty($contest_list)){    
            $this->load->helper('queue_helper');
            $data['action'] = 'cancel_game';   
            foreach($contest_list as $contest){
                $data['contest_unique_id'] = $contest['contest_unique_id'];
                // echo "\ncancel_game data push for -".$contest['contest_unique_id'];
                add_data_in_queue($data, 'lf_game_cancel');
            }
        }
    }

    $this->db->where('collection_id',$collection_id);
    $this->db->where('status !=',"2");
    $this->db->update(COLLECTION,array('status'=>3));
    return true;
  }

  /**
   * Process game cancellation
   * @param array $contest_list
   * @param array $additional_data
   */
  function process_game_cancellation($contest_list, $additional_data) {
    if(!empty($contest_list)) {
      //update game table for game cancel
      $contest_id = $contest_list['contest_id'];
      $sports_id  = $contest_list['sports_id'];
      $collection_id = $contest_list['collection_id'];

      $refund_data = $this->db_user->select('O.order_id,O.real_amount,O.bonus_amount,O.winning_amount,O.points,O.source,O.source_id,O.user_id,O.reference_id,O.type,O.custom_data')
          ->from(ORDER . " AS O")
          ->where('O.reference_id', $contest_id)
          ->where('O.status', 1)
          ->where('O.source', 500)
          ->get()
          ->result_array();

      // echo "<pre>";print_r($refund_data);die;
      $user_txn_data = array();
      if(!empty($refund_data))
      {
        foreach ($refund_data as $key => $value)
        {
          //user txn data
          $order_data = array();
          $order_data["order_unique_id"] = $this->_generate_order_unique_key();
          $order_data["user_id"]        = $value['user_id'];
          $order_data["source"]         = 501;
          $order_data["source_id"]      = $value['source_id'];
          $order_data["reference_id"]   = $value['reference_id'];
          $order_data["season_type"]    = 1;
          $order_data["type"]           = 0;
          $order_data["status"]         = 0;
          $order_data["real_amount"]    = $value['real_amount'];
          $order_data["bonus_amount"]   = $value['bonus_amount'];
          $order_data["winning_amount"] = $value['winning_amount'];
          $order_data["points"] = $value['points'];
          $order_data["custom_data"] = $value['custom_data'];
          $order_data["plateform"]      = PLATEFORM_FANTASY;
          $order_data["date_added"]     = format_date();
          $order_data["modified_date"]  = format_date();
          $user_txn_data[] = $order_data;
        }
      }
      // echo "<pre>";print_r($user_txn_data);die;
      if(!empty($user_txn_data)){
        try
        {
          $this->db->where("contest_id",$contest_id);
          $this->db->update(USER_CONTEST, array('fee_refund' => 1));

          //CHECK contest status
          $contest_info = $this->db->select('C.contest_id,C.status')
              ->from(CONTEST . " AS C")
              ->where('C.contest_id', $contest_id)
              ->get()
              ->row_array();
          if(!empty($contest_info) && $contest_info['status'] == "0"){
            // Game table update cancel status
            $this->db->where('contest_id', $contest_id);
            $this->db->update(CONTEST, array('status' => 1, 'modified_date' => $additional_data['current_date'], 'cancel_reason' => $additional_data['cancel_reason']));
            
            $this->db = $this->db_user;
            //Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
            
            $user_txn_arr = array_chunk($user_txn_data, 999);
            foreach($user_txn_arr as $txn_data){
              $this->insert_ignore_into_batch(ORDER, $txn_data);
              if($order_data["points"] > 0) {
                $this->load->helper('queue_helper');
                $coin_data = array(
                  'oprator' => 'add', 
                  'user_id' => $value['user_id'], 
                  'total_coins' => $order_data["points"], 
                  'bonus_date' => format_date("today", "Y-m-d")
                );
                add_data_in_queue($coin_data, 'user_coins');    
              }
            }

            $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = 501 AND type=0 AND status=0 AND reference_id='".$contest_id."' GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
            SET U.balance = (U.balance + OT.real_amount),U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
            WHERE O.source = 501 AND O.type=0 AND O.status=0 AND O.reference_id='".$contest_id."' ";

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
              
              //flush redis cache data
              $this->flush_cache_data();
            }
          }
        } catch (Exception $e)
        {
            //echo 'Caught exception: '.  $e->getMessage(). "\n";die;
        }
      }
    }
    return true;
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
    $this->livefantasy_db->select("C.currency_type,LM.user_id,C.contest_id,C.collection_id,C.contest_name,C.status, C.contest_unique_id, C.size, C.prize_pool, C.entry_fee, LM.user_team_id,C.prize_type,C.league_id,CS.season_game_uid,CM.collection_name,C.sports_id,count(DISTINCT LMC.contest_id) as total_teams,C.cancel_reason,CM.inn_over,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs",false)
        ->from(CONTEST . " C")
        ->join(COLLECTION . " CM", "CM.collection_id = C.collection_id", "INNER")
        ->join(SEASON . " CS", "CM.season_game_uid = CS.season_game_uid", "INNER")
        ->join(USER_CONTEST . " LMC", "LMC.contest_id = C.contest_id", "INNER")
        ->join(USER_TEAM . " LM", "LM.user_team_id = LMC.user_team_id", "INNER")
        ->where("C.status", 1)
        ->where("LMC.fee_refund", 1)
        ->where("C.is_win_notify",0)
        ->group_by("CM.collection_id,C.contest_id,LM.user_id")
        ->order_by("LM.user_id,C.contest_id,C.collection_id");

    if(isset($cancel_data['contest_id']) && $cancel_data['contest_id'] != ""){
      $this->livefantasy_db->where("C.contest_id",$cancel_data['contest_id']);
    }
    $query = $this->livefantasy_db->get();
    $result = $query->result_array();
    $match_data = array();
    $notification_data = array();
    if (!empty($result)) 
    {   
      foreach ($result as $res) 
      {
        if(!isset($match_data[$res['season_game_uid']]) || empty($match_data[$res['season_game_uid']]))
        {
          $season_data = $this->livefantasy_db->select("S.season_game_uid,S.home_uid,S.away_uid,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,S.season_scheduled_date,L.league_abbr,L.league_name,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag",FALSE)
                  ->from(SEASON . " AS S")
                  ->join(LEAGUE . " AS L", "L.league_id = S.league_id", "INNER")
                  ->join(TEAM.' T1','T1.team_uid=S.home_uid','INNER')
                  ->join(TEAM.' T2','T2.team_uid=S.away_uid','INNER')
                  ->where("S.season_game_uid",$res['season_game_uid'])
                  ->where("S.league_id",$res['league_id'])
                  ->get()->row_array();
          $season_data['home_flag'] = get_image(0,$season_data['home_flag']);
          $season_data['away_flag'] = get_image(0,$season_data['away_flag']);
          $match_data[$res['season_game_uid']] = $season_data;
        }

        if(!isset($notification_data[$res['user_id']."_".$res['collection_id']]) || empty($notification_data[$res['user_id']."_".$res['collection_id']]))
        {
          $this->db = $this->db_user;
          $user_detail = $this->get_single_row('email, user_name', USER, array("user_id"=>$res["user_id"]));
          $user_temp_data = array();
          $user_temp_data['source_id'] = $res['collection_id'];
          $user_temp_data['user_id'] = $res['user_id'];
          $user_temp_data['email'] = isset($user_detail['email']) ? $user_detail['email'] : "";
          $user_temp_data['user_name'] = isset($user_detail['user_name']) ? $user_detail['user_name'] : "";
          $user_temp_data['inning'] = isset($res['inning']) ? $res['inning'] : "";
          $user_temp_data['overs'] = isset($res['overs']) ? $res['overs'] : "";          
          $user_temp_data['cancel_reason_type'] = isset($cancel_data['cancel_reason_type']) ? $cancel_data['cancel_reason_type'] : "";
          $user_temp_data['match_data'] = $match_data[$res['season_game_uid']];
          $user_temp_data['collection_name'] = $res['collection_name'];
          // $user_temp_data['season_game_count'] = $res['season_game_count'];
          $user_temp_data['contest_data'] = array();
          $notification_data[$res['user_id']."_".$res['collection_id']] = $user_temp_data;
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
        $notification_data[$res['user_id']."_".$res['collection_id']]['contest_data'][] = $contest_data;

        // Game table update is_win_notify 1 for cancel notification
        $this->livefantasy_db->where('contest_id', $res['contest_id']);
        $this->livefantasy_db->update(CONTEST, array('is_win_notify' => '1', 'modified_date' => format_date()));
      }

    }
    // echo "<pre>";print_r($notification_data);die;
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
        if(isset($cancel_data['subject']) && $cancel_data['subject']!=""){
            $notify_data["subject"] = $cancel_data['subject'];
        }        
        if($notification_type == 125) {
            //$notify_data["subject"] = $cancel_data['contest_name'].$cancel_data["subject"];
            $notify_data["subject"] = "Contests has been cancelled by technical team!";
        }

        $content = array();
        $content['notification_type'] = $notification_type;
        $content['match_data'] = $notification['match_data'];
        $content['collection_name'] = $notification['collection_name'];
        // $content['season_game_count'] = $notification['season_game_count'];
        $content['contest_data'] = $notification['contest_data'];
        $content['int_version'] = $this->app_config['int_version']['key_value'];
        $content['contest_name'] = $notification['collection_name'];
        $content['inning'] = $notification['inning'];
        $content['cancel_reason_type'] = $notification['cancel_reason_type'];
        $content['overs'] = $notification['overs'];
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
        // print_r($notify_data);die();
        $this->Notify_nosql_model->send_notification($notify_data);
      }
    }
    return;
  }

  /**
   * @Summary: This function for use for update point in user team and user contest table with rank 
   * database.
   * @access: public
   * @param:$league_id
   * @return:
   */
  public function update_scores_by_collection($sports_id)
  {
    $current_date = format_date();
    $this->db->select("C.collection_id,C.league_id,C.season_game_uid,C.inn_over,C.collection_name,C.season_scheduled_date,L.sports_id");
    $this->db->from(COLLECTION." AS C");
    $this->db->join(LEAGUE." AS L", "L.league_id = C.league_id", "left");
    $this->db->where("L.active", '1');
    $this->db->where("C.status", '1');
    $this->db->where("DATE_FORMAT(C.season_scheduled_date ,'%Y-%m-%d %H:%i:%s') <='".$current_date."'");
    $this->db->where("L.sports_id", $sports_id);
    $this->db->group_by("C.collection_id");
    $sql = $this->db->get();
    $result = $sql->result_array();
    //echo "<pre>";print_r($result);die;
    if(!empty($result))
    {
      foreach($result as $row){
        $collection_id = $row['collection_id'];
        $market_odds = $this->get_all_table_data("*",MARKET_ODDS,array("league_id"=>$row['league_id'],"season_game_uid"=>$row['season_game_uid'],"inn_over"=>$row['inn_over']));
        //echo "<pre>";print_r($market_odds);die;
        $closed_market = array();
        foreach($market_odds as $market){
          if($market['market_status'] == "cls"){
            $closed_market[] = $market['market_id'];
            $score = $market['score'];
            $odds_id = $market['result'];
            $market_odds_arr = json_decode($market['market_odds'],TRUE);
            $points = isset($market_odds_arr[$market['result']]) ? $market_odds_arr[$market['result']] : 0;
            $half_points = number_format(($points / 2),2,".","");
            $sql = "UPDATE ".$this->db->dbprefix(USER_PREDICTION)." AS UP 
                SET UP.score = ".$score.",
                UP.is_correct = (CASE WHEN (UP.odds_id='".$odds_id."' OR UP.second_odds_id='".$odds_id."') THEN 1 WHEN (UP.odds_id!='".$odds_id."' AND UP.second_odds_id!='".$odds_id."') THEN 2 ELSE 0 END),
                UP.points = (CASE WHEN (UP.odds_id='".$odds_id."' AND UP.second_odds_id=0) THEN ".$points." WHEN (UP.odds_id='".$odds_id."' OR UP.second_odds_id='".$odds_id."') THEN ".$half_points." ELSE 0 END)
                WHERE UP.market_id='".$market['market_id']."'
                ";
            $this->db->query($sql);
          }
        }

        //points and rank
        $this->update_total_points_rank($collection_id);
        
        //notify node for rank
        $this->update_match_rank($collection_id);

      }
    }
  }

  /**
   * Used to notify node for game rank update
   */
  function update_match_rank($collection_id) {
    $this->load->library('Node');    
    $data = array('collection_id' => $collection_id);
    //log_message('error', 'Notify updateMatchRank - ' . json_encode($data));
    $node = new node(array("route" => 'updateMatchRankLF', "postData" => array("data" => $data)));
  }

  /**
   * Used for update contest status
   * @param int $sports_id
   * @return string print output
   */
  public function update_contest_status($sports_id)
  {
    $current_date = format_date();
    $this->db->select("C.collection_id,C.league_id,C.season_game_uid,C.inn_over,C.collection_name,C.season_scheduled_date,L.sports_id");
    $this->db->from(COLLECTION." AS C");
    $this->db->join(CONTEST." AS CT", "CT.collection_id = C.collection_id", "INNER");
    $this->db->join(LEAGUE." AS L", "L.league_id = C.league_id", "left");
    $this->db->where("L.active", '1');
    $this->db->where("C.status", '2');
    $this->db->where("CT.status", "0");
    $this->db->where("DATE_FORMAT(C.season_scheduled_date ,'%Y-%m-%d %H:%i:%s') <='".$current_date."'");
    $this->db->where("L.sports_id", $sports_id);
    $this->db->group_by("C.collection_id");
    $this->db->order_by("C.collection_id","DESC");
    $sql = $this->db->get();
    $result = $sql->result_array();
    //echo "<pre>";print_r($result);die;
    if(!empty($result))
    {
      $collection_ids = array_column($result, 'collection_id');
      $sql = $this->db->select('C.collection_id,C.contest_id,C.contest_unique_id,C.contest_name,C.status')
                   ->from(CONTEST." AS C")
                   ->where("C.status", 0)
                   ->where("C.total_user_joined >= ", 'minimum_size', FALSE)
                   ->where("C.sports_id", $sports_id)
                   ->where_in("C.collection_id",$collection_ids)
                   ->get();
      $contest_data = $sql->result_array();
      //echo "<pre>";print_r($contest_data); die;
      if(!empty($contest_data))
      {
        $contest_ids = array_column($contest_data, 'contest_id');
        $score_check = $this->db->select("count(*) as total")
                    ->from(USER_CONTEST)
                    ->where_in("contest_id",$contest_ids)
                    ->where("total_score > ","0")
                    ->where("fee_refund","0")
                    ->get()
                    ->row_array();
        if(!empty($score_check) && isset($score_check['total']) && $score_check['total'] > 0){
          // Mark CONTEST Status Complete
          $this->db->where_in("contest_id", $contest_ids);
          $this->db->where("status", 0);
          $this->db->update(CONTEST, array("status" => 2,"modified_date" => format_date() ));
          
          echo "Update status for contest having collection_ids: ".implode(',', $collection_ids)." ";   
        }
      }else{
        echo "No contest for status update ";
      }
    }
    else
    {
      echo "No contest status update ";
    }
    return true;
  }

  /**
   * Function used for distribute contest prize
   * @param 
   * @return boolean
   */
  public function prize_distribution($type)
  {        
    $this->db->select('C.collection_id,C.group_id', FALSE)
        ->from(CONTEST . ' AS C')
        ->where('C.is_tie_breaker','0')
        ->where('C.status', 2)
        ->where('C.season_scheduled_date < ', format_date())
        ->where('C.prize_type <> ', 4, FALSE)
        ->where('C.prize_distibution_detail != ', 'null' )
        ->group_by('C.collection_id')
        ->order_by('C.season_scheduled_date','DESC')
        ->order_by("FIELD(C.group_id,'1','2') ASC");
    if($type == "1"){
      $this->db->group_by('C.group_id');
    }
    $result = $this->db->get()->result_array();
    //echo "<pre>";print_r($result);die;
    if (!empty($result))
    {
      $this->load->helper('queue');
      $server_name = get_server_host_name();
      foreach ($result AS $prize)
      {
        $group_str = "";
        if($type == "1"){
          $group_str = "/".$prize['group_id'];
        }
        $content = array();
        $content['url'] = $server_name."/livefantasy/cron/match_prize_distribution/".$prize['collection_id'].$group_str;
        add_data_in_queue($content,'lf_prize_distribution');
      }
    }
    return true;
  }

  /**
   * Function used for distribute match contest prize
   * @param 
   * @return boolean
   */
  public function match_prize_distribution($collection_id,$group_id='')
  {
    if(!$collection_id){
      return false;
    }

    $this->db->select('C.contest_id,C.contest_unique_id,C.season_scheduled_date,C.entry_fee,C.size AS entries,C.prize_pool,C.prize_distibution_detail,C.minimum_size,C.site_rake,C.prize_type,C.group_id,CM.collection_name,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,C.contest_access_type,C.currency_type', FALSE)
        ->from(CONTEST . ' AS C')
        ->join(COLLECTION." AS CM", "CM.collection_id = C.collection_id", "INNER")
        ->where('C.status', 2)
        ->where('C.is_tie_breaker', 0)
        ->where('C.season_scheduled_date < ', format_date())
        ->where('C.prize_type <> ', 4, FALSE)
        ->where('C.prize_distibution_detail != ', 'null' )
        ->where('C.collection_id', $collection_id)
        ->group_by('C.contest_id')
        ->order_by("FIELD(C.group_id,'1','2') ASC")
        ->order_by('C.total_user_joined','DESC');
    if(isset($group_id) && $group_id != ""){
      $this->db->where('C.group_id',$group_id);
    }
    $result = $this->db->get()->result_array();
    //echo "<pre>";print_r($result);die;
    if (!empty($result))
    {
      $contest_ids = array();
      $user_lmc_data = array();
      $user_txn_data = array();
      $private_contest = array();
      foreach($result as $prize){
        $contest_unique_id = $prize['contest_unique_id'];
        $contest_id = $prize['contest_id'];
        $collection_name = $prize['collection_name'];
        $over = $prize['overs'];
        if(empty($prize['prize_distibution_detail']))
        {
          return;
        }
        $contest_ids[] = $contest_id;
        $wining_amount = (array) json_decode($prize['prize_distibution_detail'], TRUE);
        $wining_max = array_column($wining_amount, 'max');
        $winner_places = max($wining_max);
        if(empty($winner_places) || $winner_places == NULL || $winner_places == 0){
            return;
        }

        if (isset($prize['contest_access_type']) && $prize['contest_access_type'] == 1 && $prize['currency_type'] == 1)
        {
          $private_contest[] = $prize['contest_id'];
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
                if($mname == "" && isset($win_amt['min_value'])){
                  $mname = $win_amt['min_value'];
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
        $sql = "SELECT UT.user_id,UT.collection_id,UC.total_score,UC.game_rank,UC.user_team_id,UC.user_contest_id,UC.contest_id 
                  FROM ".$this->db->dbprefix(USER_CONTEST)." AS UC 
                  INNER JOIN ".$this->db->dbprefix(USER_TEAM)."  AS UT ON UT.user_team_id = UC.user_team_id
                  WHERE UC.contest_id = ".$contest_id." AND UC.fee_refund=0 
                  AND game_rank <= ".$winner_places."
                  ORDER BY UC.game_rank ASC";
        $rs = $this->db->query($sql);
        $winners = $rs->result_array();
        $tie_up_postion = array_count_values(array_column($winners,"game_rank","user_contest_id"));  
        $rank_prize_arr = array();
        foreach($tie_up_postion as $rank => $win_count){
          $rank_prize_arr[$rank] = array("amount"=>"0","bonus"=>"0","coins"=>"0","winner"=>$win_count);
          if($win_count > 1){
            $rk_to = $rank+$win_count;
            for($i=$rank;$i < $rk_to;$i++){
              $prize_arr = $winning_amount_arr[$i];
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
            }else{
              $rank_prize_arr[$rank]['amount'] = $prize_arr['amount'];
            }
          }
        }

        if(!empty($winners))
        {
          foreach ($winners as $key => $value)
          {
            $won_data = $rank_prize_arr[$value['game_rank']];
            $is_winner = 0;
            $bonus_amount = $winning_amount = $points = 0;
            $prize_obj_tmp = array();
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
            
            $lmc_data = array();
            $lmc_data['user_team_id'] = $value['user_team_id'];
            $lmc_data['contest_id'] = $value['contest_id'];
            $lmc_data['is_winner'] = $is_winner;
            $lmc_data['prize_data'] = json_encode($prize_obj_tmp);
            $user_lmc_data[] = $lmc_data;

            $custom_data = array();
            $custom_data['prize'] = $prize_obj_tmp;
            $custom_data['match'] = $collection_name;
            $custom_data['over'] = $over;

            //user txn data
            $order_data = array();
            $order_data["order_unique_id"] = $this->_generate_order_unique_key();
            $order_data["user_id"]        = $value['user_id'];
            $order_data["source"]         = 502;
            $order_data["source_id"]      = $value['user_contest_id'];
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
      // echo "<pre>";print_r($contest_ids);
      // echo "<pre>";print_r($user_lmc_data);
      // echo "<pre>";print_r($user_txn_data);die;
      if(!empty($user_lmc_data)){
        try
        {
          $is_updated = 0;
          //Start Transaction
          $this->db->trans_strict(TRUE);
          $this->db->trans_start();
          
          $user_lmc_arr = array_chunk($user_lmc_data, 999);
          foreach($user_lmc_arr as $lmc_data){
            $this->replace_into_batch(USER_CONTEST, $lmc_data);
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

            //update vi_user as u inner join `vi_order` as o on o.user_id=u.user_id set u.winning_balance=(u.winning_balance + o.winning_amount),u.bonus_balance=(u.bonus_balance + o.bonus_amount),u.point_balance=(u.point_balance + o.points),o.status=1 WHERE o.`source` = 3 AND o.`type` = 0 AND o.`status` = 0 and o.reference_id=195549
            $contest_ids = array_unique($contest_ids);
            $ctst_ids_arr = array_chunk($contest_ids, 300);
            foreach($ctst_ids_arr as $cnts_ids){
              $contest_ids_str = implode(",", $cnts_ids);
              $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = 502 AND type=0 AND status=0 AND reference_id IN (".$contest_ids_str.") GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
                SET U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
                WHERE O.source = 502 AND O.type=0 AND O.status=0 AND O.reference_id IN (".$contest_ids_str.") ";
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

              $this->db = $this->livefantasy_db;
              // Game table update is_prize_distributed 1
              $this->db->where('status',"2");
              $this->db->where('collection_id',$collection_id);
              $this->db->where_in('contest_id', $contest_ids);
              $this->db->update(CONTEST, array('status' => '3', 'modified_date' => format_date(),'completed_date' => format_date()));

              //flush redis cache data
              $this->flush_cache_data();

              // Deduct TDS If winning is greated than prize limit 
              $this->load->helper('queue');
              $server_name = get_server_host_name();
              foreach ($contest_ids AS $contest_id)
              {
                //for TDS
                $content = array();
                $content['url'] = $server_name."/livefantasy/cron/deduct_tds_from_winning/".$contest_id;
                add_data_in_queue($content,'tds');
                
                //for contest referral
                $content = array();
                $content['url'] = $server_name."/livefantasy/cron/add_cash_contest_referral_bonus/".$contest_id;
                add_data_in_queue($content,'referral');

                //for every cash contest referral
                $content = array();
                $content['url'] = $server_name."/livefantasy/cron/add_every_cash_contest_referral_benefits/".$contest_id;
                add_data_in_queue($content,'referral');
              }

              //private contest rake
              foreach($private_contest as $contest_id){
                $data = array('contest_id' => $contest_id);
                add_data_in_queue($data, 'lf_host_rake');
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
   * Function used for merchandise distribute contest prize
   * @param 
   * @return boolean
   */
  public function merchandise_prize_distribution()
  {
    $result = $this->db->select('C.currency_type,C.contest_id,C.is_tie_breaker,C.contest_unique_id,C.season_scheduled_date,C.entry_fee,C.size AS entries,C.prize_pool,C.prize_distibution_detail,C.minimum_size,C.site_rake,C.prize_type', FALSE)
                  ->from(CONTEST . ' AS C')
                  ->where('C.is_tie_breaker','1')
                  ->where('C.status', 2)
                  ->where('C.season_scheduled_date < ', format_date())
                  ->where('C.prize_type <> ', 4, FALSE)
                  ->where('C.prize_distibution_detail != ', 'null' )
                  ->group_by('C.contest_id')
                  ->order_by("FIELD(C.group_id,'1','2') ASC")
                  ->order_by('C.total_user_joined','DESC')
                  ->get()
                  ->result_array();
    //echo "<pre>";print_r($result);die;
    if (!empty($result))
    {
      $this->load->helper('queue');
      $server_name = get_server_host_name();
      foreach ($result AS $prize)
      {
        $content = array();
        $content['url'] = $server_name."/livefantasy/cron/contest_merchandise_distribution/".$prize['contest_id'];
        add_data_in_queue($content,'lf_prize_distribution');
      }
    }
    return true;
  }

  /**
  * Function used for winning deposit
  * @param array $input_data
  */
  public function winning_deposit($input_data) 
  {
    try
    {   
        $this->db = $this->db_user;
        $order_info = $this->get_single_row('order_id', ORDER, array('user_id' => $input_data['user_id'], 'source' => $input_data['source'], 'source_id' => $input_data['source_id'], "season_type" => $input_data['season_type']));
        // If prize is not alloted to user for the selected lineup contest 
        if(empty($order_info))
        {
          $orderData                   = array();
          $orderData["user_id"]        = $input_data['user_id'];
          $orderData["source"]         = $input_data['source'];
          $orderData["source_id"]      = $input_data['source_id'];
          $orderData["reference_id"] = isset($input_data['reference_id']) ? $input_data['reference_id'] : 0;
          $orderData["season_type"]    = $input_data['season_type'];
          $orderData["type"]           = 0;
          $orderData["status"]         = $input_data['status'];
          $orderData["real_amount"]    = $input_data['real_amount'];
          $orderData["bonus_amount"]   = $input_data['bonus_amount'];
          $orderData["winning_amount"] = $input_data['winning_amount'];
          $orderData["points"] = $input_data['points'];
          $orderData["custom_data"] = isset($input_data['custom_data']) ? $input_data['custom_data'] : array();
          $orderData["plateform"]      = $input_data['plateform'];
          $orderData["date_added"]     = format_date();
          $orderData["modified_date"]  = format_date();

          $orderData['order_unique_id'] = $this->_generate_order_unique_key();
          $this->db_user->insert(ORDER, $orderData);
          $order_id = $this->db_user->insert_id();
          if (!$order_id) 
          {            
              return false;
          }
          // Update User balance for order with completed status .
          if(($input_data['real_amount'] > 0 || $input_data['bonus_amount'] > 0 || $input_data['winning_amount'] > 0 || $input_data['points'] > 0) && $orderData["status"] == 1){
            $this->update_user_balance($orderData["user_id"], $orderData, "add");

            if($input_data['points'] > 0) {
              $this->load->helper('queue_helper');
              $coin_data = array(
                  'oprator' => 'add', 
                  'user_id' => $input_data['user_id'], 
                  'total_coins' => $input_data['points'], 
                  'bonus_date' => format_date("today", "Y-m-d")
              );
              add_data_in_queue($coin_data, 'user_coins');    
            }
          }
          return $order_id;
        }else{
          return true;
        }
    } catch (Exception $e)
    {
      return false;
    } 
  }

  /**
   * Function used for distribute contest prize for merchandise case
   * @param:
   * @return:
   */
  public function contest_merchandise_distribution($contest_id)
  {
    if(!$contest_id){
      return false;
    }
    $prize_data = $this->db->select('C.contest_id,C.contest_unique_id,C.season_scheduled_date,C.entry_fee,C.size AS entries,C.prize_pool,C.prize_distibution_detail,C.minimum_size,C.site_rake,C.prize_type,C.group_id,CM.collection_name,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs', FALSE)
                  ->from(CONTEST.' AS C')
                  ->join(COLLECTION." AS CM", "CM.collection_id = C.collection_id", "INNER")
                  ->where('C.status', 2)
                  ->where('C.is_tie_breaker', 1)
                  ->where('C.season_scheduled_date < ', format_date())
                  ->where('C.prize_type <> ', 4, FALSE)
                  ->where('C.prize_distibution_detail != ', 'null' )
                  ->where('C.contest_id', $contest_id)
                  ->group_by('C.contest_unique_id')
                  ->limit(1)
                  ->get()
                  ->row_array();
    //echo "<pre>";print_r($prize_data);die;
    if (!empty($prize_data))
    {
      $prize = $prize_data;
      $contest_unique_id = $prize['contest_unique_id'];
      $contest_id = $prize['contest_id'];
      $collection_name = $prize['collection_name'];
      $over = $prize['overs'];
      if(empty($prize['prize_distibution_detail']))
      {
        return;
      }

      $wining_amount = (array) json_decode($prize['prize_distibution_detail'], TRUE);
      $wining_max = array_column($wining_amount, 'max');
      $winner_places = max($wining_max);
      if(empty($winner_places) || $winner_places == NULL || $winner_places == 0){
          return;
      }

      $merchandise = array();
      $merchandise_ids = array();
      foreach($wining_amount as $amt_arr){
        if(isset($amt_arr['prize_type']) && $amt_arr['prize_type'] == 3)// for merchandise
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
          for($i=$win_amt['min']; $i<=$win_amt['max']; $i++)
          {
            if($win_amt['prize_type']==3)
            {
              $image = "";
              $mname = "";
              $mprice= 0;
              if(isset($merchandise[$win_amt['amount']])){
                $image = $merchandise[$win_amt['amount']]['image_name'];
                $mname = $merchandise[$win_amt['amount']]['name'];
                $mprice = $merchandise[$win_amt['amount']]['price'];
              }
              if($mname == "" && isset($win_amt['min_value'])){
                $mname = $win_amt['min_value'];
              }
              $winning_amount_arr[$i-1] = array("prize_type"=>$win_amt['prize_type'],
                "name"=>$mname,
                "image"=>$image,
                "price" => $mprice
              );
            }
            else
            {   
              $winning_amount_arr[$i-1] = array("prize_type"=>$win_amt['prize_type'],"amount"=>$win_amt['amount']);
            }
          }
        }
      }

      //get winners
      $sql = "SELECT UT.user_id,UT.collection_id,UC.total_score,UC.game_rank,UC.user_team_id,UC.user_contest_id 
                FROM ".$this->db->dbprefix(USER_CONTEST)." AS UC 
                INNER JOIN ".$this->db->dbprefix(USER_TEAM)."  AS UT ON UT.user_team_id = UC.user_team_id
                WHERE UC.contest_id = ".$prize['contest_id']." AND UC.fee_refund=0 
                ORDER BY UC.game_rank ASC
                LIMIT ".$winner_places." ";
      $rs = $this->db->query($sql);
      $winners = $rs->result_array();
      //echo "<pre>";print_r($winning_amount_arr);die;
      $is_success = 0;
      foreach($winners as $key=>$winner){
        if(isset($winning_amount_arr[$key])){
          $prize_obj = $winning_amount_arr[$key];
          $real_amount = $bonus_amount = $winning_amount = $points = 0;
          if($prize_obj['prize_type'] == 0){
            $bonus_amount = $prize_obj['amount'];
          }else if($prize_obj['prize_type'] == 1){
            $winning_amount = $prize_obj['amount'];
          }else if($prize_obj['prize_type'] == 2){
            $prize_obj['amount'] = ceil($prize_obj['amount']);
            $points = $prize_obj['amount'];
          }
          $prize_obj_tmp = array();
          $prize_obj_tmp[] = $prize_obj;

          $custom_data = array();
          $custom_data['prize'] = $prize_obj_tmp;
          $custom_data['match'] = $collection_name;
          $custom_data['over'] = $over;
          $tmp_arr = array();
          $tmp_arr['user_id'] = $winner['user_id'];
          $tmp_arr['source'] = 502;
          $tmp_arr['source_id'] = $winner['user_contest_id'];
          $tmp_arr["reference_id"] = $contest_id;
          $tmp_arr['season_type'] = 1;
          $tmp_arr['status'] = 1;
          $tmp_arr['real_amount'] = $real_amount;
          $tmp_arr['bonus_amount'] = $bonus_amount;
          $tmp_arr['winning_amount'] = $winning_amount;
          $tmp_arr['points'] = ceil($points);
          $tmp_arr['custom_data'] = json_encode($custom_data);
          $tmp_arr['plateform'] = PLATEFORM_FANTASY;
          $result = $this->winning_deposit($tmp_arr);
          if($result){
            $is_success = 1;
          }

          //lineup master update is_winner 1
          $this->livefantasy_db->where(array('user_contest_id' => $winner['user_contest_id']));
          $this->livefantasy_db->update(USER_CONTEST, array('is_winner' => '1','prize_data' => json_encode($prize_obj_tmp)));
        }
      }

      if($is_success == 1){
        // Game table update is_prize_distributed 1
        $this->livefantasy_db->where('contest_id', $contest_id);
        $this->livefantasy_db->update(CONTEST, array('status' => '3', 'modified_date' => format_date(),'completed_date' => format_date()));

        $this->load->helper('queue');
        $server_name = get_server_host_name();
        //for TDS
        $content = array();
        $content['url'] = $server_name."/livefantasy/cron/deduct_tds_from_winning/".$contest_id;
        add_data_in_queue($content,'tds');
        
        //for contest referral
        $content = array();
        $content['url'] = $server_name."/livefantasy/cron/add_cash_contest_referral_bonus/".$contest_id;
        add_data_in_queue($content,'referral');

        //for every cash contest referral
        $content = array();
        $content['url'] = $server_name."/livefantasy/cron/add_every_cash_contest_referral_benefits/".$contest_id;
        add_data_in_queue($content,'referral');

        if (CACHE_ENABLE) {
          $this->flush_cache_data();
        }
      }

    }
    return true;
  }

  public function deduct_tds_from_winning($contest_id)
  {
    $allow_tds = isset($this->app_config['allow_tds'])?$this->app_config['allow_tds']['key_value']:0;
    if($allow_tds != "1")
    {
      return TRUE;
    }
    $tds_percent = isset($this->app_config['allow_tds']['custom_data']['percent']) ? $this->app_config['allow_tds']['custom_data']['percent'] : 0;
    $tds_amount = isset($this->app_config['allow_tds']['custom_data']['amount']) ? $this->app_config['allow_tds']['custom_data']['amount'] : 0;
    if(!empty($contest_id))
    {
      $contest = $this->db->select('C.contest_id,C.contest_name,CM.collection_name,CM.inn_over,CM.collection_id,CM.collection_id as match_id,CM.season_scheduled_date', FALSE)
                      ->from(CONTEST.' AS C')
                      ->join(COLLECTION." AS CM","CM.collection_id = C.collection_id","INNER")
                      ->where('C.contest_id', $contest_id)
                      ->limit(1)
                      ->get()
                      ->row_array();

      $contest_id = $contest['contest_id'];
      $match_id = $contest['match_id'];
      $contest_name = $contest['contest_name'];
      $inn_over_arr = explode("_",$contest['inn_over']);
      $match_name = $contest['collection_name']." Inning ".$inn_over_arr['0']." Over ".$inn_over_arr['1'];
      $scheduled_date = $contest['season_scheduled_date'];
      
      $custom_data = array("contest_id"=>$contest_id,"match_id"=>$match_id,"contest_name"=>$contest_name,"match_name"=>$match_name,"scheduled_date"=>$scheduled_date);
      $this->db_user->select("O.source_id, O.user_id, O.real_amount, O.bonus_amount, SUM(O.winning_amount) AS total_real_amount");
      $this->db_user->from(ORDER . " O");
      $this->db_user->where("O.reference_id",$contest_id);
      $this->db_user->where("O.source",502);// 502: game won
      $this->db_user->where("O.status",1);// 1: completed
      $this->db_user->where("O.winning_amount > ", 0); // 1: completed
      $this->db_user->group_by("O.user_id");
      $this->db_user->having("total_real_amount >= ",$tds_amount);
      $this->db_user->order_by("total_real_amount","DESC");
      $sql = $this->db_user->get();
      //echo $this->db_user->last_query();
      $winning_data = $sql->result_array();
      if(!empty($winning_data))
      {
          foreach($winning_data as $winning)
          {
              if($winning['total_real_amount'] > $tds_amount)
              {
                  $tds_amount = ($winning['total_real_amount'] * $tds_percent) / 100;
                  // Save txn for TDS amount on prize amount
                  if($tds_amount > 0)
                  {
                      $check_order = $this->db_user->select("order_id")
                                      ->from(ORDER)
                                      ->where("user_id", $winning["user_id"])
                                      ->where("source_id", $contest_id)
                                      ->where("source", 504)
                                      ->get()->row_array();
                      if(!empty($check_order) && $check_order['order_id'] != ""){
                          return true;
                      }else{
                          //$post_target_url   = 'finance/withdraw';
                          $tds_amount = number_format($tds_amount, 2, '.', '');
                          $post_params = array(
                                          'user_id' => $winning['user_id'],
                                          'amount' => $tds_amount,
                                          'cash_type' => 0,   // 0: real amount
                                          'contest_id' => $contest_id,
                                          'plateform' => PLATEFORM_FANTASY,
                                          'source' => 504, // TDS Deduction
                                          'source_id' => $contest_id,
                                          'custom_data' => $custom_data,
                                          'reason' => "Live Fantasy TDS Deduction"
                                      );
                          $this->withdraw($post_params);
                      }
                  }
              }
          }
      }
    }
    return TRUE;
  }

  /**  Used to get user balance 
   * @param int $user_id
   * @return array
   */
  public function get_user_balance($user_id)
  {
      $result =   $this->db_user->select("user_id,balance as real_amount, bonus_balance as bonus_amount, winning_balance as winning_amount,point_balance")
                                  ->where(array("user_id" => $user_id))
                                  ->limit(1)
                                  ->get(USER)
                                  ->row_array();
      return array(
                      "bonus_amount"   => $result["bonus_amount"]?$result["bonus_amount"]:0,
                      "real_amount"    => $result["real_amount"]?$result["real_amount"]:0,
                      "winning_amount" => $result["winning_amount"]?$result["winning_amount"]:0,
                      "point_balance"  => $result["point_balance"]?$result["point_balance"]:0
                  );
  }

  public function withdraw($input_data) 
  { 
      $this->db = $this->db_user;
      $amount                       = $input_data["amount"];
      $orderData                    = array();
      $orderData["user_id"]         = $input_data["user_id"];
      $orderData["source"]          = $input_data["source"];
      $orderData["source_id"]       = $input_data["source_id"];
      $orderData["type"]            = 1;
      $orderData["date_added"]      = format_date();
      $orderData["modified_date"]   = format_date();
      $orderData["plateform"]       = $input_data["plateform"];
      $orderData["status"]          = 0;
      $orderData["real_amount"]     = 0;
      $orderData["bonus_amount"]    = 0;
      $orderData["winning_amount"]  = 0;
      $orderData["withdraw_method"] = isset($input_data["withdraw_method"]) ? $input_data["withdraw_method"]:0;
      $orderData["reason"] = !empty($input_data["reason"]) ? $input_data["reason"] : '';
      if(isset($input_data['custom_data']) && !empty($input_data['custom_data'])){
        $orderData["custom_data"] = json_encode($input_data['custom_data']);
      }

      if(!empty($input_data['email']))
      {
          $orderData["email"] = $input_data['email']; 
      }

      $user_balance = $this->get_user_balance($orderData["user_id"]);
      // If requested amount is greater then total amount.
      if ($user_balance["real_amount"] + $user_balance["bonus_amount"] +  $user_balance["winning_amount"] < $amount) 
      {
          return false;
      }

      switch ($input_data["cash_type"]) 
      {
          case 0:
              if ($input_data["source"] == 504 && $user_balance["winning_amount"] < $amount) {
                  return false;
              }else if ($input_data["source"] != 8 && $input_data["source"] != 504 && $user_balance["real_amount"] < $amount) {
                  return false;
              }
              if ($input_data["source"] == 504){
                  $orderData["winning_amount"] = $amount;
              }else{
                  $orderData["real_amount"] = $amount;
              }
              break;
          case 1:
              if ($input_data["source"] != 8 && $user_balance["bonus_amount"] < $amount) 
              {
                  return false;
              }
              $orderData["bonus_amount"] = $amount;
              break;
          case 2:
              // Use both cash (bouns+real) only for join game. 
              $max_bouns = ($amount * MAX_BONUS_PERCEN_USE)/100;

              if ($orderData["source"] == 1) 
              {
                  // Deduct Max 10% of entry fee from bonus amount.
                  $orderData["bonus_amount"] = $max_bouns;
                  if($max_bouns>$user_balance["bonus_amount"])
                  {
                      $orderData["bonus_amount"] = $user_balance["bonus_amount"];
                  }
                  $remain_amt = $amount-$orderData["bonus_amount"];
                  // Deduct reamining amount from real amount.
                  $orderData["real_amount"] = $remain_amt;

                  if ($remain_amt > $user_balance["real_amount"]) 
                  {
                       $orderData["real_amount"] = $user_balance["real_amount"];
                  }
                  
                  $remain_amt =  $remain_amt - $orderData["real_amount"];
                  if($remain_amt > $user_balance["winning_amount"])
                  {
                      return false;                       
                  }
                  // Deduct Remaining amount from winning amount
                  $orderData["winning_amount"] =  $remain_amt;
              }
              break;
      }

      switch ($input_data["source"]) 
      {
          // admin
          case 0:
          // JoinGame
          case 1:
          // TDS on winning
          case 504:
          // BonusExpired
          case 5:
              $orderData["status"] = 1;
              break;
          // 8-Withdraw [ User can withdraw cash only from winning amount. ]  
         case 8:
              $orderData["real_amount"] = 0;
              $orderData["bonus_amount"] = 0;
              $orderData["winning_amount"] = $amount;                
              if($amount > $user_balance["winning_amount"])
              {
                  return false;
              }
              break;
      }
      $orderData['order_unique_id'] = $this->_generate_order_unique_key();
      $this->db_user->insert(ORDER, $orderData);
      $order_id = $this->db_user->insert_id();
      if (!$order_id) {
          return false;
      }

      $real_bal    = $user_balance['real_amount'] - $orderData["real_amount"];
      $bonus_bal   = $user_balance['bonus_amount'] - $orderData["bonus_amount"];
      $winning_bal = $user_balance['winning_amount'] - $orderData["winning_amount"];
      $this->update_user_balance($orderData["user_id"], $orderData, "withdraw");
      // Add notification
      $tmp = array();
      $user_detail = $this->get_single_row('email, user_name', USER, array("user_id"=>$input_data["user_id"]));

      if($input_data["source"] !=1 && $input_data["source"] !=21)
      {   
          $subject = "Amount withdrawal";
          $input_data["reason"] = CRON_WITHDRAWL_NOTI1;
          $tmp["notification_destination"] = 7; //  Web, Push, Email
          $tmp["notification_type"] = 7; // 7-Withdraw
          if($input_data["source"] == 504)
          {
              $tmp["notification_destination"] = 1;
              $tmp["notification_type"] = 619;
              $subject = "TDS Deducted";
          }

          $tmp["source_id"]                = $input_data["source_id"];
          $tmp["user_id"]                  = $input_data["user_id"];
          $tmp["to"]                       = $user_detail['email'];
          $tmp["user_name"]                = $user_detail['user_name'];
          $tmp["added_date"]               = date("Y-m-d H:i:s");
          $tmp["modified_date"]            = date("Y-m-d H:i:s");
          $tmp["content"]                  = json_encode($input_data);
          $tmp["subject"]                  = $subject;
          $source = $input_data["source"];
          if($source != 7 && empty($input_data['ignore_deposit_noty']) )
          {
              $this->load->model('notification/Notify_nosql_model');
              $this->Notify_nosql_model->send_notification($tmp);
          }
      }
     return array(
          'transaction_id' => $order_id,
          'order_id'       => $order_id,
          'bonus_balance'  => $bonus_bal,
          'balance'        => $real_bal);
  }

  //Get user cash count for referral bonus
  private function get_user_cash_contest_count($user_id)
  {
      $total_cash_contest = 0;
      $this->livefantasy_db->select("count(UC.user_contest_id ) as total_cash_contest",FALSE)
          ->from(CONTEST." C")
          ->join(USER_CONTEST." UC","UC.contest_id = C.contest_id","INNER")
          ->join(USER_TEAM." UT","UT.user_team_id= UC.user_team_id","INNER")
          ->where("UT.user_id>",0)
          ->where("C.entry_fee>",0)
          ->where("C.currency_type != ","2")
          ->where("UT.user_id", $user_id);
      $record = $this->livefantasy_db->get()->row_array();
      if(!empty($record['total_cash_contest']))
      {
          $total_cash_contest = $record['total_cash_contest'];
      }
      return $total_cash_contest;
  }

  /**
   * Function to Update user balance
   *  Params: $user_id,$real_balance,$bonus_balance
   *  
   */
  function update_user_balance($user_id,$balance_arr,$oprator='add')
  {
      if(empty($balance_arr)){
          return false;
      }
      if(isset($balance_arr['real_amount']) && $balance_arr['real_amount'] > 0 ){
          if($oprator=='withdraw'){
              $this->db_user->set('balance', 'balance - '.$balance_arr['real_amount'], FALSE);
          }else{
              $this->db_user->set('balance', 'balance + '.$balance_arr['real_amount'], FALSE);
          }
          if(isset($balance_arr['source']) && $balance_arr['source'] == "7" && $oprator == 'add'){
              $this->db->set('total_deposit', 'total_deposit + '.$balance_arr['real_amount'], FALSE);
          }
      }
      if(isset($balance_arr['bonus_amount']) && $balance_arr['bonus_amount'] > 0 ){
          if($oprator=='withdraw'){
              $this->db_user->set('bonus_balance', 'bonus_balance - '.$balance_arr['bonus_amount'], FALSE);
          }else{
              $this->db_user->set('bonus_balance', 'bonus_balance + '.$balance_arr['bonus_amount'], FALSE);
          }

          $this->load->helper('queue_helper');
          $bonus_data = array('oprator' => $oprator, 'user_id' => $user_id, 'total_bonus' => $balance_arr['bonus_amount'], 'bonus_date' => format_date("today", "Y-m-d"));
          add_data_in_queue($bonus_data, 'user_bonus');
      }
      if(isset($balance_arr['winning_amount']) && $balance_arr['winning_amount'] > 0 ){
          if($oprator=='withdraw'){
              $this->db_user->set('winning_balance', 'winning_balance - '.$balance_arr['winning_amount'], FALSE);
          }else{
              $this->db_user->set('winning_balance', 'winning_balance + '.$balance_arr['winning_amount'], FALSE);
          }
          if(isset($balance_arr['source']) && $balance_arr['source'] == "3" && $oprator == 'add'){
              $this->db_user->set('total_winning', 'total_winning + '.$balance_arr['winning_amount'], FALSE);
          }
      }
      if(isset($balance_arr['points']) && $balance_arr['points'] > 0 ){
          if($oprator=='withdraw'){
              $this->db_user->set('point_balance', 'point_balance - '.$balance_arr['points'], FALSE);
          }else{
              $this->db_user->set('point_balance', 'point_balance + '.$balance_arr['points'], FALSE);
          }
          $this->load->helper('queue_helper');
          $coin_data = array('oprator' => $oprator, 'user_id' => $user_id, 'total_coins' => $balance_arr['points'], 'bonus_date' => format_date("today", "Y-m-d"));
          add_data_in_queue($coin_data, 'user_coins');
      }
      $this->db_user->where('user_id', $user_id);
      $this->db_user->update(USER);
      
      return $this->db_user->affected_rows();  
  }

  public function deposit($input_data) 
  {
      $order_status = (!empty($input_data['status'])) ? $input_data['status'] : 0; 
      $result = $this->generate_order(
              $input_data["amount"], 
              $input_data["user_id"], 
              $input_data["cash_type"], 
              $input_data["plateform"], 
              $input_data["source"], 
              $input_data["source_id"],
              $input_data["season_type"],
              $order_status
              );
      if($result)
      {

          // Add notification
          $tmp = array(); 
          $this->db = $this->db_user;
          $user_detail = $this->get_single_row('email, user_name', USER, array("user_id"=>$input_data["user_id"]));

          if($input_data["cash_type"]==3)
          {
               $tmp["notification_type"]        = 27; // 27-Deposit Coins
          }
          else
          {
               $tmp["notification_type"]        = 6; // 6-Deposit
          }
          
          $tmp["source_id"]                = $input_data["source_id"];
          $tmp["notification_destination"] = 7; //  Web, Push, Email
          $tmp["user_id"]                  = $input_data["user_id"];
          $tmp["to"]                       = $user_detail['email'];
          $tmp["user_name"]                = $user_detail['user_name'];
          $tmp["added_date"]               = date("Y-m-d H:i:s");
          $tmp["modified_date"]            = date("Y-m-d H:i:s");
          $tmp["content"]                  = json_encode($input_data);
          $tmp["subject"]                  = "Amount deposited.";

          $source = $input_data["source"];
          if($source != 7 && empty($input_data['ignore_deposit_noty']) )
          {
              $this->load->model('notification/Notify_nosql_model');
              $this->Notify_nosql_model->send_notification($tmp);
          }          
      }
      return $result;   
  }

  public function generate_order($amount, $user_id, $cash_type, $plateform, $source, $source_id,$season_type,$status=0, $custom_data='')
  {
      
      $orderData                   = array();
      $orderData["user_id"]        = $user_id;
      $orderData["source"]         = $source;
      $orderData["source_id"]      = $source_id;
      $orderData["season_type"]    = $season_type;
      $orderData["type"]           = 0;
      $orderData["date_added"]     = format_date();
      $orderData["modified_date"]  = format_date();
      $orderData["plateform"]      = $plateform;
      $orderData["status"]         = $status;
      $orderData["real_amount"]    = 0;
      $orderData["bonus_amount"]   = 0;
      $orderData["winning_amount"] = 0;
      $orderData["points"] = 0;

      if(!empty($custom_data)) {
          $orderData["custom_data"] = $custom_data;
      } 

      switch ($cash_type) {
          // Real Money
          case 0:
              $orderData["real_amount"] = $amount;
              break;
          // Bonus Money 
          case 1:
              $orderData["bonus_amount"] = $amount;
              break;
         // Point Balance     
          case 3:
          case 2:
              $orderData["points"] = $amount;
              break;
          default:
              return true;

              break;
      }

      switch ($source) {
          case 0:
          case 2:
          case 4:
          case 9:
          case 41:
          case 202:
          case 221:
          case 181:
          case 251:
          case 6:
          case 30:
          case 31:
          case 32:
          case 437:
              $orderData["status"] = 1;
              break;
          case 3:
              switch ($cash_type) {
              // Real Money
              case 0:
                  $orderData["winning_amount"] = $amount;
                  break;
              // Bonus Money 
              case 1:
                  $orderData["bonus_amount"] = $amount;
                  break;
             // Point Balance     
              default:
                  break;
              }

              $orderData["real_amount"] = 0;
              $orderData["status"] = 1;
              break;     
      }
      $orderData['order_unique_id'] = $this->_generate_order_unique_key();
      $this->db_user->insert(ORDER, $orderData);
      $order_id = $this->db_user->insert_id();
      
      if (!$order_id) 
      {            
          return false;
      }

      // Update User balance for order with completed status .
      $orderData["status"] == 1 && $this->update_user_balance($orderData["user_id"], $orderData, "add");

      return $order_id;
  }

  public function add_cash_contest_referral_bonus($contest_id)
  {
    //get contest basic details
    $query = $this->db->select('entry_fee,contest_id,contest_unique_id,max_bonus_allowed')
                            ->where("contest_id",$contest_id)
                            ->where("user_id","0")
                            ->where("max_bonus_allowed < ","100")
                            ->get(CONTEST);
    $data = $query->row_array();
    if(!$data)
    {
        return TRUE;
    }
    $entry_fee = $data['entry_fee'];
    $contest_id = $data['contest_id'];
    //if contest not a cash leageue then return.    
    if($entry_fee<=0)
    {
        return TRUE;
    }

    //get total joined users for this contest.
    $sql = "SELECT `user_id` FROM ".$this->db->dbprefix(USER_CONTEST)." AS `UC` INNER JOIN ".$this->db->dbprefix(USER_TEAM)." AS `UT` ON `UT`.`user_team_id` = `UC`.`user_team_id` WHERE `contest_id` = '$contest_id'";
    $query = $this->db->query($sql);
    $users = $query->result_array();
    if(empty($users))
    {
        return TRUE;
    }
    $user_id_arr = array_column($users, 'user_id');
    //get all valid affiliate users and their details.
    $user_tbl = $this->db_user->dbprefix(USER);
    $query = $this->db_user->select('U1.email AS user_email,CONCAT(U1.first_name," ",U1.last_name) as name,U1.user_name as username1,CONCAT(U2.first_name," ",U2.last_name) as friendname, U2.user_name as username2, U2.email AS friend_email, UAH.user_id, UAH.friend_id, UAH.user_affiliate_history_id,UAH.source_type,U1.phone_no user_phone,U2.phone_no as friend_phone', False)
                            ->from(USER_AFFILIATE_HISTORY.' AS UAH')
                            ->join($user_tbl.' AS U1', 'U1.user_id = UAH.user_id', 'INNER', FALSE)
                            ->join($user_tbl.' AS U2', 'U2.user_id = UAH.friend_id', 'INNER', FALSE)
                            ->where_in('UAH.friend_id', $user_id_arr)
                            ->where('UAH.status', '1')
                            ->where('UAH.affiliate_type', 1)
                            ->get();
    $refferal_data = $query->result_array();
    if(empty($refferal_data))
    {
        return TRUE;
    }    

   // echo '<pre>';print_r($refferal_data);die;
    $order_source_array = array(
        //1st cash contest order sources for both referred and referral users
        '10' => array(68,69,70,71,72,73),
        //5th cash contest order sources for both referred and referral users
        '11' => array(74,75,76,77,78,79),
        //10th cash contest order sources for both referred and referral users
        '12' => array(80,81,82,83,84,85)
    );
    $current_date = format_date();
    // echo "order source : ".$order_source_array[10][0];die;
    //process each valid referral users for cash contest referrals.
    foreach ($refferal_data as $key => $value)
    {
        $user_id          = $value['user_id'];
        $friend_id        = $value['friend_id'];
       // $user_bonus_cash  = $value['user_bonus_cash'];
        $refferal_id      = $value['user_affiliate_history_id'];
        $source_type      = $value['source_type'];
        $friend_phone     = $value['friend_phone'];
        $friend_email     = (!empty($value['friend_email']))? $value['friend_email'] : "";

        //get users total played cash contests.
        $cash_contest_count = $this->get_user_cash_contest_count($friend_id);
        if(empty($cash_contest_count))
        {
            continue;
        } 

        $affiliate_type_arr = array();

        if($cash_contest_count >= 1)
        {
            $affiliate_type_arr[] = 10;                
        } 

        if($cash_contest_count >= 5)
        {
            $affiliate_type_arr[] = 11;
        }

        if($cash_contest_count >= 10)
        {
            $affiliate_type_arr[] = 12;
        }    

        //echo '<pre>';print_r($affiliate_type_arr);die;
        if(empty($affiliate_type_arr))
        {
            continue;
        }
        //process each active affiliate/referral types for this user.
        foreach ($affiliate_type_arr as $affiliate_type)
        {
            //check this type of referral/affilate available or not in system.
            $this->db = $this->db_user;
            $affililate_master_detail = $this->get_single_row('*',AFFILIATE_MASTER,array("affiliate_type"=>$affiliate_type));

            //echo '<pre>';print_r($affililate_master_detail);die("called...1");
            if(empty($affililate_master_detail))
            {
                continue;
            }   
            
            //check if bonus already given for this referral/affiliate type
            $affililate_history = $this->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$friend_id,"status"=>1,"user_id" =>$user_id,"affiliate_type"=>$affiliate_type));
            if(!empty($affililate_history))
            {
                continue;
            }    
                
            // echo '<pre>';print_r($affililate_history);die("called...2");    
            //create a new entry in affiliate history for this referral type
            $affiliate_user_data                    = array();
            $affiliate_user_data["user_id"]         = $user_id;
            $affiliate_user_data["source_type"]     = $source_type;
            $affiliate_user_data["affiliate_type"]  = $affiliate_type;
            $affiliate_user_data["status"]          = 1;
            $affiliate_user_data["is_referral"]     = 1;
            $affiliate_user_data["created_date"]    = $current_date;
            $affiliate_user_data["friend_id"]       = $friend_id;
            $affiliate_user_data['friend_bonus_cash'] = (!empty($affililate_master_detail["user_bonus"])) ? $affililate_master_detail["user_bonus"] : 0;
            $affiliate_user_data['friend_real_cash'] = (!empty($affililate_master_detail["user_real"])) ? $affililate_master_detail["user_real"] : 0;
            $affiliate_user_data['user_bonus_cash'] = (!empty($affililate_master_detail["bonus_amount"])) ? $affililate_master_detail["bonus_amount"] : 0;
            $affiliate_user_data['user_real_cash'] = (!empty($affililate_master_detail["real_amount"])) ? $affililate_master_detail["real_amount"] : 0;
            $affiliate_user_data["bouns_condition"] = json_encode(array());
            $affiliate_user_data["friend_mobile"]   = $friend_phone;
            $affiliate_user_data["friend_email"]    = $friend_email;

            $this->db_user->insert(USER_AFFILIATE_HISTORY, $affiliate_user_data);
            $affililate_history_id = $this->db_user->insert_id();
            if(empty($affililate_history_id))
            {
                continue;
            }

            /*############ Generate transactions for user who sent referral(referral code) #########*/  
            //Entry on order table for bonus cash type
            if($affililate_master_detail["bonus_amount"] > 0)
            {
                $deposit_data_friend = array(
                                    "user_id"   => $user_id, 
                                    "amount"    => $affililate_master_detail["bonus_amount"], 
                                    "source"    => $order_source_array[$affiliate_type][0],
                                    "source_id" => $affililate_history_id, 
                                    "plateform" => 1, 
                                    "cash_type" => 1,//for bonus cash 
                                    "season_type"=>1,
                                    "status"    =>1,
                                    "ignore_deposit_noty"=>1
                                );
                $return_data = $this->deposit($deposit_data_friend);

                if($return_data)
                {
                    /*Add Notification*/
                    $tmp = array();
                    $input = array(
                        'friend_name' => ($value['friendname']) ? $value['friendname'] : $value['friend_email'], 
                        'username'    => $value['username1'],
                        'amount'      => $affililate_master_detail["bonus_amount"],
                        'friend_email'=> $value['friend_email']
                    );
                    $tmp["notification_type"]        = $order_source_array[$affiliate_type][0];
                    $tmp["source_id"]                = $affililate_history_id;
                    $tmp["notification_destination"] = 3; //web, push, email
                    $tmp["user_id"]                  = $user_id;
                    $tmp["to"]                       = $value['user_email'];
                    $tmp["user_name"]                = $value['username1'];
                    $tmp["added_date"]               = $current_date;
                    $tmp["modified_date"]            = $current_date;
                    $tmp["content"]                  = json_encode($input);
                   // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                    //notification to user
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($tmp); 
                    /* END Notification */            
                }
            }

            //Entry on order table for real cash type
            if($affililate_master_detail["real_amount"] > 0)
            {
               $deposit_data_friend = array(
                                    "user_id"   => $user_id, 
                                    "amount"    => $affililate_master_detail["real_amount"], 
                                    "source"    => $order_source_array[$affiliate_type][1],
                                    "source_id" => $affililate_history_id, 
                                    "plateform" => 1, 
                                    "cash_type" => 0,//for real cash 
                                    "season_type"=> 1,
                                    "status"    =>1,
                                    "ignore_deposit_noty"=>1
                                );
                $return_data = $this->deposit($deposit_data_friend);

                if($return_data)
                {
                    /*Add Notification*/
                    $tmp = array();
                    $input = array(
                        'friend_name' => ($value['friendname']) ? $value['friendname'] : $value['friend_email'], 
                        'username'    => $value['username1'],
                        'amount'      => $affililate_master_detail["real_amount"],
                        'friend_email'=> $value['friend_email']
                    );
                    $tmp["notification_type"]        = $order_source_array[$affiliate_type][1];
                    $tmp["source_id"]                = $affililate_history_id;
                    $tmp["notification_destination"] = 3; //web, push, email
                    $tmp["user_id"]                  = $user_id;
                    $tmp["to"]                       = $value['user_email'];
                    $tmp["user_name"]                = $value['username1'];
                    $tmp["added_date"]               = $current_date;
                    $tmp["modified_date"]            = $current_date;
                    $tmp["content"]                  = json_encode($input);
                   // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                    //notification to user
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($tmp); 
                    /* END Notification */            
                }
            }
               
            /*## Generate transactions for user who used referral code ###*/    
            //Entry on order table for bonus cash type
            if($affililate_master_detail["user_bonus"] > 0)
            {
               
                $deposit_data_friend = array(
                                    "user_id"   => $friend_id, 
                                    "amount"    => $affililate_master_detail["user_bonus"], 
                                    "source"    => $order_source_array[$affiliate_type][3],
                                    "source_id" => $affililate_history_id, 
                                    "plateform" => 1, 
                                    "cash_type" => 1,//for bonus cash 
                                    "season_type"=> 1,
                                    "status"    =>1,
                                    "ignore_deposit_noty"=>1
                                );
                $return_data = $this->deposit($deposit_data_friend);

                if($return_data)
                {
                    /*Add Notification*/
                    $tmp = array();
                    $input = array(
                        'friend_name' => ($value['friendname'])?$value['friendname']:$value['friend_email'], 
                        'username'    => $value['username1'],
                        'amount'      => $affililate_master_detail["user_bonus"],
                        'friend_email'=> $value['friend_email']
                    );
                    $tmp["notification_type"]        = $order_source_array[$affiliate_type][3];
                    $tmp["source_id"]                = $affililate_history_id;
                    $tmp["notification_destination"] = 3; //web,push,email
                    $tmp["user_id"]                  = $friend_id;
                    $tmp["to"]                       = $value['friend_email'];
                    $tmp["user_name"]                = $value['username2'];
                    $tmp["added_date"]               = $current_date;
                    $tmp["modified_date"]            = $current_date;
                    $tmp["content"]                  = json_encode($input);
                   // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                    //notification to user
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($tmp); 
                    /* END Notification */            
                }
            }

            //Entry on order table for real cash type
            if($affililate_master_detail["user_real"] > 0)
            {
                $deposit_data_friend = array(
                                    "user_id"   => $friend_id, 
                                    "amount"    => $affililate_master_detail["user_real"], 
                                    "source"    => $order_source_array[$affiliate_type][4],
                                    "source_id" => $affililate_history_id, 
                                    "plateform" => 1, 
                                    "cash_type" => 0,//for real cash 
                                    "season_type"=> 1,
                                    "status"    =>1,
                                    "ignore_deposit_noty"=>1
                                );
                $return_data = $this->deposit($deposit_data_friend);

                if($return_data)
                {
                    /*Add Notification*/
                    $tmp = array();
                    $input = array(
                        'friend_name' => ($value['friendname'])?$value['friendname']:$value['friend_email'], 
                        'username'    => $value['username1'],
                        'amount'      => $affililate_master_detail["user_real"],
                        'friend_email'=> $value['friend_email']
                    );
                    $tmp["notification_type"]        = $order_source_array[$affiliate_type][4];
                    $tmp["source_id"]                = $affililate_history_id;
                    $tmp["notification_destination"] = 3; //web,push,email
                    $tmp["user_id"]                  = $friend_id;
                    $tmp["to"]                       = $value['friend_email'];
                    $tmp["user_name"]                = $value['username2'];
                    $tmp["added_date"]               = $current_date;
                    $tmp["modified_date"]            = $current_date;
                    $tmp["content"]                  = json_encode($input);
                   // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                    //notification to user
                    $this->load->model('notification/Notify_nosql_model');
                    $this->Notify_nosql_model->send_notification($tmp); 
                    /* END Notification */            
                }
            }
        }
    }

    return TRUE;
  }

  private function get_user_cash_contest_amount($input_array=array())
  {
     if(empty($input_array['user_contest_ids']) || empty($input_array['user_id']))
     {
        return 0;
     } 

    $user_contest_ids = $input_array['user_contest_ids'];
    $this->db_user->select("O.source_id, O.user_id,SUM(O.real_amount+O.winning_amount) AS total_amount")
                    ->from(ORDER . " O");
    $user_contest_ids_chunk = array_chunk($user_contest_ids,500);
    if(!empty($user_contest_ids_chunk))
    {
      $this->db_user->group_start();
      foreach ($user_contest_ids_chunk as $chunk_key => $chunk_arr) 
      {
          if($chunk_key == 0)
          {
            $this->db_user->where_in('O.source_id',$chunk_arr);
          }
          else
          {
            $this->db_user->or_where_in('O.source_id',$chunk_arr);
          }  
      }
      $this->db_user->group_end();
    }  

    $this->db_user->where("O.source", 1);  // 1: game join
    $this->db_user->where("O.status", 1);  // 1: completed
    $this->db_user->where("O.user_id", $input_array['user_id']); 
    $this->db_user->group_by("O.user_id");
    $sql = $this->db_user->get();
    //echo $this->db_user->last_query();die;
    $order_rs = $sql->row_array();
    return (!empty($order_rs['total_amount'])) ? $order_rs['total_amount'] : 0; 

  }

  private function calculate_referral_amount($rf_data=array())
  {
      if($rf_data['amount_type'] != 2)
      {
          return $rf_data;
      }  

      $user_invested_amount = (!empty($rf_data['user_invested_amount'])) ? $rf_data['user_invested_amount'] : 0;

       $max_earning_amount = $rf_data['max_earning_amount'];

       $bonus_amount = (!empty($rf_data['bonus_amount'])) ? number_format(($user_invested_amount*$rf_data['bonus_amount'])/100,2) : 0;
       $rf_data['bonus_amount'] =  (!empty($max_earning_amount) && $bonus_amount > $max_earning_amount) ? $max_earning_amount : $bonus_amount;

       $real_amount = (!empty($rf_data['real_amount'])) ? number_format(($user_invested_amount*$rf_data['real_amount'])/100,2) : 0;
       $rf_data['real_amount'] =  (!empty($max_earning_amount) && $real_amount > $max_earning_amount) ? $max_earning_amount : $real_amount;

      $coin_amount = (!empty($rf_data['coin_amount'])) ? number_format(($user_invested_amount*$rf_data['coin_amount'])/100,2) : 0; 
      $rf_data['coin_amount'] =  (!empty($max_earning_amount) && $coin_amount > $max_earning_amount) ? $max_earning_amount : $coin_amount;


      $user_bonus = (!empty($rf_data['user_bonus'])) ? number_format(($user_invested_amount*$rf_data['user_bonus'])/100,2) : 0;
      $rf_data['user_bonus'] =  (!empty($max_earning_amount) && $user_bonus > $max_earning_amount) ? $max_earning_amount : $user_bonus;


      $user_real = (!empty($rf_data['user_real'])) ? number_format(($user_invested_amount*$rf_data['user_real'])/100,2) : 0;
      $rf_data['user_real'] =  (!empty($max_earning_amount) && $user_real > $max_earning_amount) ? $max_earning_amount : $user_real;

      $user_coin = (!empty($rf_data['user_coin'])) ? number_format(($user_invested_amount*$rf_data['user_coin'])/100,2) : 0;
      $rf_data['user_coin'] =  (!empty($max_earning_amount) && $user_coin > $max_earning_amount) ? $max_earning_amount : $user_coin;


      return $rf_data;
  }

  /**
   * Internal function used for add referral benefits to users when user's friend invested on any cash contest.
   * @param string $contest_id
   * @return boolean
   */
  public function add_every_cash_contest_referral_benefits($contest_id)
  {
    //get contest basic details
    $query = $this->db->select('entry_fee,contest_id,contest_unique_id,max_bonus_allowed')
                            ->where("contest_id",$contest_id)
                            ->where("user_id","0")
                            ->where("max_bonus_allowed < ","100")
                            ->get(CONTEST);
    $data = $query->row_array();
    //echo "<pre>";print_r($data);die;
    if(!$data)
    {
        return TRUE;
    }
    $entry_fee = $data['entry_fee'];
    $contest_id = $data['contest_id'];
    //if contest not a cash leageue then return.    
    if($entry_fee<=0)
    {
        return TRUE;
    }

    //get affiliate master data
    $this->db = $this->db_user;
    $affililate_master_detail = $this->get_single_row('*',AFFILIATE_MASTER,array("affiliate_type"=>22,"status"=>1));
    if(empty($affililate_master_detail))
    {
      return TRUE;
    }  
    $affiliate_type = $affililate_master_detail['affiliate_type'];

    //get total joined users for this contest.
    $sql = "SELECT `user_id`,`UT`.`user_team_id`,`UC`.`user_contest_id` FROM ".$this->livefantasy_db->dbprefix(USER_CONTEST)." AS `UC` INNER JOIN ".$this->livefantasy_db->dbprefix(USER_TEAM)." AS `UT` ON `UT`.`user_team_id` = `UC`.`user_team_id` WHERE `contest_id` = '$contest_id'";
    $query = $this->livefantasy_db->query($sql);
    $users = $query->result_array();
    if(empty($users))
    {
        return TRUE;
    }

    $user_id_arr = array_column($users, 'user_id');
    $user_id_chunks = array_chunk($user_id_arr,500);
    //echo "<pre>";print_r($user_id_info);die;
    //get all valid affiliate users and their details.
    $user_tbl = $this->db_user->dbprefix(USER);
    $query = $this->db_user->select('U1.email AS user_email,CONCAT(U1.first_name," ",U1.last_name) as name,U1.user_name as username1,CONCAT(U2.first_name," ",U2.last_name) as friendname, U2.user_name as username2, U2.email AS friend_email, UAH.user_id, UAH.friend_id, UAH.user_affiliate_history_id,UAH.source_type,U1.phone_no user_phone,U2.phone_no as friend_phone', False)
                            ->from(USER_AFFILIATE_HISTORY.' AS UAH')
                            ->join($user_tbl.' AS U1', 'U1.user_id = UAH.user_id', 'INNER', FALSE)
                            ->join($user_tbl.' AS U2', 'U2.user_id = UAH.friend_id', 'INNER', FALSE)
                            ->where('UAH.status', '1')
                            ->where_in('UAH.affiliate_type',array(1,19,20,21));
    if(!empty($user_id_chunks))
    {
      $this->db_user->group_start();
      foreach ($user_id_chunks as $chunk_key => $chunk_arr) 
      {
          if($chunk_key == 0)
          {
            $this->db_user->where_in('UAH.friend_id',$chunk_arr);
          }
          else
          {
            $this->db_user->or_where_in('UAH.friend_id',$chunk_arr);
          }  
      }
      $this->db_user->group_end();
    }
    $query = $this->db_user->get();
    $refferal_data = $query->result_array();
    if(empty($refferal_data))
    {
      return TRUE;
    }    

    $user_id_info = array_column($users,NULL,'user_id');
    $current_date = format_date();
    // echo '<pre>';print_r($refferal_data);die;
    $order_source_array = array(
        //every cash contest order sources for both referred and referral users
        '22' => array(270,271,272,273,274,275)
    );

    //process each valid referral users for cash contest referrals.
    foreach ($refferal_data as $key => $value)
    {
        $user_id          = $value['user_id'];
        $friend_id        = $value['friend_id'];
        // $user_bonus_cash  = $value['user_bonus_cash'];
        $refferal_id      = $value['user_affiliate_history_id'];
        $source_type      = $value['source_type'];
        $friend_phone     = $value['friend_phone'];
        $friend_email     = (!empty($value['friend_email']))? $value['friend_email'] : "";

        //get users total played cash contests.
        $input_data = array(
          "user_contest_ids" => array($user_id_info[$friend_id]['user_contest_id']),
          "user_id" => $friend_id
        );
        $cash_contest_amount = $this->get_user_cash_contest_amount($input_data);
        if(empty($cash_contest_amount))
        {
            continue;
        } 

        //check if bonus already given for this referral/affiliate type
        $this->db = $this->db_user;
        $affililate_history = $this->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$friend_id,"status"=>1,"user_id" =>$user_id,"affiliate_type"=>$affiliate_type,"contest_id"=>$contest_id));
        if(!empty($affililate_history))
        {
          echo "Already exists for user : $user_id <br>";
          continue;
        }    
                    
        // echo '<pre>';print_r($affililate_history);die("called...2");    
        //create a new entry in affiliate history for this referral type
        $rf_input_data = $affililate_master_detail;
        $rf_input_data['user_invested_amount'] = $cash_contest_amount;
        $calculated_referral_amount = $this->calculate_referral_amount($rf_input_data);
        //echo "<pre>";print_r($calculated_referral_amount);die;

        $affiliate_user_data                    = array();
        $affiliate_user_data["user_id"]         = $user_id;
        $affiliate_user_data["source_type"]     = $source_type;
        $affiliate_user_data["affiliate_type"]  = $affiliate_type;
        $affiliate_user_data["status"]          = 1;
        $affiliate_user_data["is_referral"]     = 1;
        $affiliate_user_data["created_date"]    = $current_date;
        $affiliate_user_data["friend_id"]       = $friend_id;
        $affiliate_user_data['friend_real_cash'] = (!empty($calculated_referral_amount["user_real"])) ? $calculated_referral_amount["user_real"] : 0;
        $affiliate_user_data['friend_bonus_cash'] = (!empty($calculated_referral_amount["user_bonus"])) ? $calculated_referral_amount["user_bonus"] : 0;
        $affiliate_user_data['friend_coin'] = (!empty($calculated_referral_amount["user_coin"])) ? $calculated_referral_amount["user_coin"] : 0;
        $affiliate_user_data['user_real_cash'] = (!empty($calculated_referral_amount["real_amount"])) ? $calculated_referral_amount["real_amount"] : 0;
        $affiliate_user_data['user_bonus_cash'] = (!empty($calculated_referral_amount["bonus_amount"])) ? $calculated_referral_amount["bonus_amount"] : 0;
        $affiliate_user_data['user_coin'] = (!empty($calculated_referral_amount["coin_amount"])) ? $calculated_referral_amount["coin_amount"] : 0;
        $affiliate_user_data["bouns_condition"] = json_encode(array());
        $affiliate_user_data["friend_mobile"]   = $friend_phone;
        $affiliate_user_data["friend_email"]    = $friend_email;
        $affiliate_user_data["contest_id"]      = $contest_id;

        $this->db_user->insert(USER_AFFILIATE_HISTORY, $affiliate_user_data);
        $affililate_history_id = $this->db_user->insert_id();
        //echo $affililate_history_id;die;
        if(empty($affililate_history_id))
        {
            continue;
        }

        /*###### Generate transactions for user who sent referral(referral code) #####*/  
        //Entry on order table for real cash type
        if($calculated_referral_amount["real_amount"] > 0)
        {
           $deposit_data_friend = array(
                                "user_id"   => $user_id, 
                                "amount"    => $calculated_referral_amount["real_amount"], 
                                "source"    => $order_source_array[$affiliate_type][0],
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 0,//for real cash 
                                "season_type"=> 1,
                                "status"    =>1,
                                "ignore_deposit_noty"=>1
                            );
            $return_data = $this->deposit($deposit_data_friend);

            if($return_data)
            {
                /*Add Notification*/
                $tmp = array();
                $input = array(
                    'friend_name' => ($value['friendname']) ? $value['friendname'] : $value['username2'], 
                    'username'    => $value['username1'],
                    'amount'      => $calculated_referral_amount["real_amount"],
                    'friend_email'=> $value['friend_email']
                );
                $tmp["notification_type"]        = $order_source_array[$affiliate_type][0];
                $tmp["source_id"]                = $affililate_history_id;
                $tmp["notification_destination"] = 3; //web, push, email
                $tmp["user_id"]                  = $user_id;
                $tmp["to"]                       = $value['user_email'];
                $tmp["user_name"]                = $value['username1'];
                $tmp["added_date"]               = $current_date;
                $tmp["modified_date"]            = $current_date;
                $tmp["content"]                  = json_encode($input);
               // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                //notification to user
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp); 
                /* END Notification */            
            }
        }

        //Entry on order table for bonus cash type
        if($calculated_referral_amount["bonus_amount"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $user_id, 
                                "amount"    => $calculated_referral_amount["bonus_amount"], 
                                "source"    => $order_source_array[$affiliate_type][1],
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 1,//for bonus cash 
                                "season_type"=>1,
                                "status"    =>1,
                                "ignore_deposit_noty"=>1
                            );
            $return_data = $this->deposit($deposit_data_friend);

            if($return_data)
            {
                /*Add Notification*/
                $tmp = array();
                $input = array(
                    'friend_name' => ($value['friendname']) ? $value['friendname'] : $value['username2'], 
                    'username'    => $value['username1'],
                    'amount'      => $calculated_referral_amount["bonus_amount"],
                    'friend_email'=> $value['friend_email']
                );
                $tmp["notification_type"]        = $order_source_array[$affiliate_type][1];
                $tmp["source_id"]                = $affililate_history_id;
                $tmp["notification_destination"] = 3; //web, push
                $tmp["user_id"]                  = $user_id;
                $tmp["to"]                       = $value['user_email'];
                $tmp["user_name"]                = $value['username1'];
                $tmp["added_date"]               = $current_date;
                $tmp["modified_date"]            = $current_date;
                $tmp["content"]                  = json_encode($input);
               // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                //notification to user
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp); 
                /* END Notification */            
            }
        }

        //Entry on order table for coin type
        if($calculated_referral_amount["coin_amount"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $user_id, 
                                "amount"    => $calculated_referral_amount["coin_amount"], 
                                "source"    => $order_source_array[$affiliate_type][2],
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 3,//for coins 
                                "season_type"=>1,
                                "status"    =>1,
                                "ignore_deposit_noty"=>1
                            );
            $return_data = $this->deposit($deposit_data_friend);

            if($return_data)
            {
                /*Add Notification*/
                $tmp = array();
                $input = array(
                    'friend_name' => ($value['friendname']) ? $value['friendname'] : $value['username2'], 
                    'username'    => $value['username1'],
                    'amount'      => $calculated_referral_amount["coin_amount"],
                    'friend_email'=> $value['friend_email']
                );
                $tmp["notification_type"]        = $order_source_array[$affiliate_type][2];
                $tmp["source_id"]                = $affililate_history_id;
                $tmp["notification_destination"] = 3; //web, push
                $tmp["user_id"]                  = $user_id;
                $tmp["to"]                       = $value['user_email'];
                $tmp["user_name"]                = $value['username1'];
                $tmp["added_date"]               = $current_date;
                $tmp["modified_date"]            = $current_date;
                $tmp["content"]                  = json_encode($input);
               // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                //notification to user
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp); 
                /* END Notification */            
            }
        }

        /*############# Generate transactions for user who used referral code #########*/    
        //Entry on order table for real cash type
        if($calculated_referral_amount["user_real"] > 0)
        {
            $deposit_data_friend = array(
                                "user_id"   => $friend_id, 
                                "amount"    => $calculated_referral_amount["user_real"], 
                                "source"    => $order_source_array[$affiliate_type][3],
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 0,//for real cash 
                                "season_type"=> 1,
                                "status"    =>1,
                                "ignore_deposit_noty"=>1
                            );
            $return_data = $this->deposit($deposit_data_friend);

            if($return_data)
            {
                /*Add Notification*/
                $tmp = array();
                $input = array(
                    'friend_name' => ($value['friendname'])?$value['friendname']:$value['username2'], 
                    'username'    => $value['username1'],
                    'amount'      => $calculated_referral_amount["user_real"],
                    'friend_email'=> $value['friend_email']
                );
                $tmp["notification_type"]        = $order_source_array[$affiliate_type][3];
                $tmp["source_id"]                = $affililate_history_id;
                $tmp["notification_destination"] = 3; //web,push,email
                $tmp["user_id"]                  = $friend_id;
                $tmp["to"]                       = $value['friend_email'];
                $tmp["user_name"]                = $value['username2'];
                $tmp["added_date"]               = $current_date;
                $tmp["modified_date"]            = $current_date;
                $tmp["content"]                  = json_encode($input);
               // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                //notification to user
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp); 
                /* END Notification */            
            }
        }  

        //Entry on order table for bonus cash type
        if($calculated_referral_amount["user_bonus"] > 0)
        {
           
            $deposit_data_friend = array(
                                "user_id"   => $friend_id, 
                                "amount"    => $calculated_referral_amount["user_bonus"], 
                                "source"    => $order_source_array[$affiliate_type][4],
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 1,//for bonus cash 
                                "season_type"=> 1,
                                "status"    =>1,
                                "ignore_deposit_noty"=>1
                            );
            $return_data = $this->deposit($deposit_data_friend);

            if($return_data)
            {
                /*Add Notification*/
                $tmp = array();
                $input = array(
                    'friend_name' => ($value['friendname'])?$value['friendname']:$value['username2'], 
                    'username'    => $value['username1'],
                    'amount'      => $calculated_referral_amount["user_bonus"],
                    'friend_email'=> $value['friend_email']
                );
                $tmp["notification_type"]        = $order_source_array[$affiliate_type][4];
                $tmp["source_id"]                = $affililate_history_id;
                $tmp["notification_destination"] = 3; //web,push,email
                $tmp["user_id"]                  = $friend_id;
                $tmp["to"]                       = $value['friend_email'];
                $tmp["user_name"]                = $value['username2'];
                $tmp["added_date"]               = $current_date;
                $tmp["modified_date"]            = $current_date;
                $tmp["content"]                  = json_encode($input);
               // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                //notification to user
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp); 
                /* END Notification */            
            }
        }

        //Entry on order table for coins type
        if($calculated_referral_amount["user_coin"] > 0)
        {
           
            $deposit_data_friend = array(
                                "user_id"   => $friend_id, 
                                "amount"    => $calculated_referral_amount["user_coin"], 
                                "source"    => $order_source_array[$affiliate_type][5],
                                "source_id" => $affililate_history_id, 
                                "plateform" => 1, 
                                "cash_type" => 3,//for coins 
                                "season_type"=> 1,
                                "status"    =>1,
                                "ignore_deposit_noty"=>1
                            );
            $return_data = $this->deposit($deposit_data_friend);

            if($return_data)
            {
                /*Add Notification*/
                $tmp = array();
                $input = array(
                    'friend_name' => ($value['friendname'])?$value['friendname']:$value['username2'], 
                    'username'    => $value['username1'],
                    'amount'      => $calculated_referral_amount["user_coin"],
                    'friend_email'=> $value['friend_email']
                );
                $tmp["notification_type"]        = $order_source_array[$affiliate_type][5];
                $tmp["source_id"]                = $affililate_history_id;
                $tmp["notification_destination"] = 3; //web,push
                $tmp["user_id"]                  = $friend_id;
                $tmp["to"]                       = $value['friend_email'];
                $tmp["user_name"]                = $value['username2'];
                $tmp["added_date"]               = $current_date;
                $tmp["modified_date"]            = $current_date;
                $tmp["content"]                  = json_encode($input);
               // $tmp["subject"]                  = $this->username.", you have earned Cash Bonus!";
                //notification to user
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($tmp); 
                /* END Notification */            
            }
        }  
    }

    return TRUE;
  }

  public function auto_recurring_contest($contest_unique_id)
  {
      if(!$contest_unique_id){
          return false;
      }

      $contest_data = $this->db->select("*")
                  ->from(CONTEST)
                  ->where("contest_unique_id",$contest_unique_id)
                  ->get()
                  ->row_array();
      if(!empty($contest_data)){
          $game_data = $contest_data;
          $game_data['contest_unique_id'] = random_string('alnum', 9);
          $game_data['total_user_joined'] = 0;
          $game_data['total_system_user'] = 0;
          $prize_details = json_decode($contest_data['base_prize_details']);
          $game_data['prize_pool'] = $prize_details->prize_pool;
          $game_data['prize_distibution_detail'] = $prize_details->prize_distibution_detail;
          $game_data['added_date'] = format_date();
          $game_data['modified_date'] = format_date();
          unset($game_data["contest_id"]);
          $this->db->insert(CONTEST, $game_data);
      }
      return true;
  }

  public function match_prize_distribute_notification()
  {
    $this->livefantasy_db->select("LM.user_team_id,LM.user_id,C.contest_id,C.collection_id,C.contest_name,C.status, C.contest_unique_id, C.size, C.prize_pool, C.entry_fee, C.prize_type,C.league_id,CS.season_game_uid,CM.collection_name,C.sports_id,C.currency_type, count(DISTINCT LMC.contest_id) as total_teams,C.cancel_reason,CM.inn_over,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,LMC.is_winner,LMC.game_rank,GROUP_CONCAT(LMC.user_contest_id) as user_contest_ids,GROUP_CONCAT(LMC.game_rank) as user_rank_list,GROUP_CONCAT(LM.team_name) as team_name,LMC.prize_data,C.prize_distibution_detail",false)
        ->from(CONTEST . " C")
        ->join(COLLECTION . " CM", "CM.collection_id = C.collection_id", "INNER")
        ->join(SEASON . " CS", "CM.season_game_uid = CS.season_game_uid", "INNER")
        ->join(USER_CONTEST . " LMC", "LMC.contest_id = C.contest_id", "INNER")
        ->join(USER_TEAM . " LM", "LM.user_team_id = LMC.user_team_id", "INNER")
        ->where("C.status", 3)
        ->where("LMC.is_winner",1)
        // ->where("C.added_date >","2022-03-14")
        ->where("C.is_win_notify",0)
        ->group_by("LMC.user_team_id,C.contest_id")
        ->order_by("LM.user_id,C.contest_id,C.collection_id");
        // $this->livefantasy_db->limit(30);
    $query = $this->livefantasy_db->get();
    $result = $query->result_array();   
    // print_r($this->livefantasy_db->last_query()); 
    // echo "<pre>";print_r($result);die;
    $match_data = array();
    $notification_data = array();
    if (!empty($result)) 
    {   
      $push_data = $this->db_user->select('notification_type,message')
      ->from(NOTIFICATION_DESCRIPTION." ND")
      ->where('notification_type',622)
      ->get()->result_array();
      $push_message = array();
      foreach($push_data as $p_data)
      {
        $push_message[$p_data['notification_type']] = $p_data['message'];
      }  
        
      $user_ids = array_unique(array_column($result,'user_id'));
      // Get user device details
      /*$device_detail = $this->db_user->select("user_id,device_id,device_type")
      ->from(ACTIVE_LOGIN)
      ->where_in('user_id',$user_ids)
      ->where('device_id is NOT NULL', NULL, FALSE)
      ->group_by('user_id')
      ->order_by('keys_id','DESC')
      ->get()->result_array();

      if(!empty($device_detail)){
        $device_detail =  array_column($device_detail,NULL,'user_id');
      }*/
      foreach ($result as $res) 
      {
        if(!isset($match_data[$res['season_game_uid']]) || empty($match_data[$res['season_game_uid']]))
        {         
          $season_data = $this->livefantasy_db->select("S.season_game_uid,S.home_uid,S.away_uid,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,S.season_scheduled_date,L.league_abbr,L.league_name,IFNULL(T1.feed_flag,T1.flag) AS home_flag,IFNULL(T2.feed_flag,T2.flag) AS away_flag",FALSE)
              ->from(SEASON . " AS S")
              ->join(LEAGUE . " AS L", "L.league_id = S.league_id", "INNER")
              ->join(TEAM.' T1','T1.team_uid=S.home_uid','INNER')
              ->join(TEAM.' T2','T2.team_uid=S.away_uid','INNER')
              ->where("S.season_game_uid",$res['season_game_uid'])
              ->where("S.league_id",$res['league_id'])
              ->get()->row_array();
         
          $season_data['home_flag'] = get_image(0,$season_data['home_flag']);
          $season_data['away_flag'] = get_image(0,$season_data['away_flag']);
          $match_data[$res['season_game_uid']] = $season_data;
        }
        $user_contest_ids = explode(',', $res['user_contest_ids']);
        $user_rank_list = explode(',', $res['user_rank_list']);
        $team_names = explode(',', $res['team_name']);
        $team_names = array_combine($user_contest_ids,$team_names);
        $team_ranks = array_combine($user_contest_ids,$user_rank_list);
        // echo "<pre>";print_r($team_ranks);die;
        $sql = $this->db_user->select("O.user_id,O.source_id,O.source,O.order_id,O.real_amount, O.bonus_amount, O.winning_amount, O.points, U.email,U.user_name,O.source_id,O.prize_image,O.custom_data")
          ->from(ORDER . " O")
          ->join(USER . " U", "U.user_id = O.user_id", "INNER")
          ->where("O.user_id", $res['user_id'])
          ->where_in("O.source_id", $user_contest_ids)
          ->where("O.source",502)
          ->get();
        $order_info = $sql->result_array();
        // print_r($this->db_user->last_query());die();
        // print_r($order_info);die();
        
        if (!empty($order_info)) 
        {
          // Game table update is_win_notify 1
          $this->livefantasy_db->where('contest_id', $res['contest_id']);
          $this->livefantasy_db->update(CONTEST, array('is_win_notify' => '1', 'modified_date' => format_date()));
          if(!isset($notification_data[$res['user_id']."_".$res['collection_id']]) || empty($notification_data[$res['user_id']."_".$res['collection_id']]))
          {
            $user_temp_data = array();
            $user_temp_data['source_id'] = $res['collection_id'];
            $user_temp_data['user_id'] = $res['user_id'];
            $user_temp_data['email'] = $order_info[0]['email'];
            $user_temp_data['user_name'] = $order_info[0]['user_name'];
            $user_temp_data['match_data'] = $match_data[$res['season_game_uid']];
            $user_temp_data['collection_name'] = $res['collection_name'];
            $user_temp_data['inning'] = isset($res['inning']) ? $res['inning'] : "";
            $user_temp_data['over'] = isset($res['overs']) ? $res['overs'] : "";
            $user_temp_data['contest_data'] = array();
            $notification_data[$res['user_id']."_".$res['collection_id']] = $user_temp_data;
          }
          
          foreach ($order_info as $key => $value) 
          {           
            
            $ord_custom_data = json_decode($order_info[0]['custom_data'],TRUE);
            
            if ($order_info[0]['bonus_amount'] > 0  || $order_info[0]['winning_amount'] > 0 || $order_info[0]['points'] > 0 || !empty($ord_custom_data))
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
              $contest_data['inning'] = isset($res['inning']) ? $res['inning'] : "";
              $contest_data['over'] = isset($res['overs']) ? $res['overs'] : "";
              $contest_data['user_rank'] = isset($team_ranks[$value['source_id']]) ? $team_ranks[$value['source_id']] : $res['game_rank'];
              $contest_data['size'] = $res['size'];
              $contest_data['prize_pool'] = $res['prize_pool'];
              $contest_data['entry_fee'] = $res['entry_fee'];
              $contest_data['currency_type'] = $res['currency_type'];
              $contest_data['team_name'] = $team_names[$value['source_id']];
              $contest_data['prize_type'] = $res['prize_type'];
              $contest_data['amount'] = $amount;
              $contest_data['custom_data'] = json_decode($value['custom_data'],TRUE);
              $contest_data['total_winner'] = $total_winner;
              $notification_data[$res['user_id']."_".$res['collection_id']]['contest_data'][] = $contest_data;
              $notification_data[$res['user_id']."_".$res['collection_id']]['notification_type'] = 622;
              $notification_data[$res['user_id']."_".$res['collection_id']]['subject'] = "Wohoo! You just WON!";
              // $notification_data[$res['user_id']."_".$res['collection_id']]['device_ids'] = $value['device_ids'];
            }            
          }
        }
      }
    
      //echo "<pre>";print_r($notification_data);die;
      foreach($notification_data as $notification)
      {
          /* Send Notification */
          $notify_data = array();
          $notify_data['notification_type'] = $notification['notification_type']; //3-GameWon
          $notify_data['notification_destination'] = 7; //web, push, email
          $notify_data["source_id"] = $notification['source_id'];
          $notify_data["user_id"] = $notification['user_id'];
          $notify_data["to"] = $notification['email'];
          $notify_data["user_name"] = $notification['user_name'];
          $notify_data["added_date"] = format_date();
          $notify_data["modified_date"] = format_date();
          $notify_data["subject"] = $notification['subject'];
          // $notify_data["device_ids"] = $notification['device_ids'];

          $content = array();
          $content['match_data'] = $notification['match_data'];
          $content['collection_name'] = $notification['collection_name'];
          $content['inning'] = $notification['inning'];
          $content['over'] = $notification['over'];
          $content['contest_data'] = $notification['contest_data'];
          // $content['completed_date'] = $notification['completed_date'];
          $content["message"] = $push_message[$notification['notification_type']];
          $content['int_version'] = $this->app_config['int_version']['key_value'];
          $notify_data["content"] = json_encode($content);
          // print_r($notify_data);die();
          $this->load->model('notification/Notify_nosql_model');
          $this->Notify_nosql_model->send_notification($notify_data); 
      }
    }else{
      echo "No Pending Prize distribute notification.\n";
    }
    return true;
  }

  /**
   * [process_contest_host_rake description]
   * @Summary :- This function will reset the number of winners if joined count is less than selected no. of winners.
   * @return  [type]
   */
  public function process_contest_host_rake($contest_id)
  {
      set_time_limit(0);
      $current_date = format_date();

      $contest_details = $this->livefantasy_db->select("C.contest_id, C.entry_fee, C.total_user_joined, C.minimum_size, C.prize_pool, C.host_rake, C.user_id as contest_host, C.contest_access_type, C.host_rake_awarded")
            ->from(CONTEST." AS C")
            ->where("C.status", 3)
            ->where("contest_id",$contest_id)
            ->where("C.contest_access_type", 1)
            ->where("C.host_rake_awarded", 0)
            ->where("total_user_joined >= ", 'minimum_size', FALSE)
            ->where("season_scheduled_date < ", $current_date)
            ->get()->row_array();

      if(!empty($contest_details))
      {
        $host_rake_per_entry = ($contest_details['entry_fee']*$contest_details['host_rake'])/100;
        $total_host_rake = $host_rake_per_entry * $contest_details['total_user_joined'];
        $user_id = $contest_details['contest_host'];
        //user txn data
        $order_data = array();
        $order_data["order_unique_id"] = $this->_generate_order_unique_key();
        $order_data["user_id"]        = $user_id;
        $order_data["source"]         = 503;
        $order_data["source_id"]      = $contest_details['contest_id'];
        $order_data["reference_id"]   = $contest_details['contest_id'];
        $order_data["season_type"]    = 1;
        $order_data["type"]           = 0;
        $order_data["status"]         = 0;
        $order_data["real_amount"]    = $total_host_rake;
        $order_data["bonus_amount"]   = 0;
        $order_data["winning_amount"] = 0;
        $order_data["points"]         = 0;
        $order_data["custom_data"]    = NULL;
        $order_data["plateform"]      = PLATEFORM_FANTASY;
        $order_data["date_added"]     = format_date();
        $order_data["modified_date"]  = format_date();

        $this->db = $this->db_user;
        $this->db->insert(ORDER, $order_data);

        $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id 
          SET U.balance = (U.balance + O.real_amount),O.status=1 
          WHERE O.source = 503 AND O.type=0 AND O.status=0 AND O.reference_id='".$contest_details['contest_id']."' ";
        $this->db->query($bal_sql);
        if ($this->db->affected_rows() > 0)
        {
            $this->livefantasy_db->where("contest_id", $contest_details['contest_id']);
            $this->livefantasy_db->update(CONTEST, array("host_rake_awarded" => 1, "modified_date" => format_date() ));

            $this->delete_cache_data('user_balance_'.$user_id);
        }
      }
  }

  /**
  * Function used for push contest for gst report
  * @param 
  * @return boolean
  */
  public function push_contest_for_gst_report()
  {
    $current_date = format_date();
    $contest_list = $this->db->select('C.contest_id,C.contest_name', FALSE)
                      ->from(CONTEST.' AS C')
                      ->join(COLLECTION." AS CM","CM.collection_id = C.collection_id","INNER")
                      ->where('C.currency_type', 1)
                      ->where('C.entry_fee > ',0)
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
      $content['url'] = $server_name."/livefantasy/cron/generate_gst_report/".$contest['contest_id'];
      add_data_in_queue($content,'lf_gst');
    }
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
    $contest = $this->db->select('C.contest_id,C.contest_name,C.entry_fee,C.prize_pool,C.site_rake,C.prize_type,C.minimum_size as min_size,C.size as max_size,C.total_user_joined,CM.collection_name,CM.inn_over,CM.collection_id,CM.season_scheduled_date', FALSE)
                      ->from(CONTEST.' AS C')
                      ->join(COLLECTION." AS CM","CM.collection_id = C.collection_id","INNER")
                      ->where('C.currency_type', 1)
                      ->where('C.entry_fee > ',0)
                      ->where('C.status', 3)
                      ->where('C.is_gst_report',0)
                      ->where('C.season_scheduled_date < ',$current_date)
                      ->where('C.contest_id', $contest_id)
                      ->limit(1)
                      ->get()
                      ->row_array();
    //echo "<pre>";print_r($contest);die;
    if(!empty($contest))
    {
      $portal_state_id = isset($this->app_config['allow_gst']['custom_data']['state_id']) ? $this->app_config['allow_gst']['custom_data']['state_id'] : 0;
      $cgst_value = 9;//value in percentage
      $sgst_value = 9;
      $igst_value = 18;
      $inn_over_arr = explode("_",$contest['inn_over']);
      $match_id = $contest['collection_id'];
      $contest_id = $contest['contest_id'];
      $site_rake = $contest['site_rake'];
      $match_name = $contest['collection_name']." Inning ".$inn_over_arr['0']." Over ".$inn_over_arr['1'];
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
                          ->where('O.source', 500)
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
          $tmp_arr['module_type'] = "2";
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
          $tmp_arr['date_added'] = $current_date;
          $tmp_arr['min_size'] = $min_size;
          $tmp_arr['max_size'] = $max_size;
          $tmp_arr['total_user_joined'] = $total_user_joined;
          $tmp_arr['prize_pool'] = $prize_pool;
          $tmp_arr['is_invoice_sent'] = 1;
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

          $this->livefantasy_db->where('contest_id', $contest_id);
          $this->livefantasy_db->update(CONTEST, array('is_gst_report' => '1'));
        }
      }else{
        //update contest gst status as failed
        $this->livefantasy_db->where('contest_id', $contest_id);
        $this->livefantasy_db->update(CONTEST, array('is_gst_report' => '2'));
      }
    }
    return true;
  }
}
