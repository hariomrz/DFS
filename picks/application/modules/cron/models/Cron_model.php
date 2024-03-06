<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron_model extends MY_Model {  
  public $db_user;
  public function __construct() 
  {
    parent::__construct();
    $this->db_user = $this->load->database('user_db', TRUE);
    $this->picks_db = $this->load->database('picks_db', TRUE);
  }

   /**
    * Auto recurring when maximum user joined the contest
    * Replica of same contest created again
    * @param contest_unique_id
    * @return boolean
    */
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
            $prize_details = json_decode($contest_data['base_prize_details']);
            $game_data['prize_pool'] = $prize_details->prize_pool;
            $game_data['prize_distibution_detail'] = $prize_details->prize_distibution_detail;
            $game_data['added_date'] = format_date();
            $game_data['modified_date'] = format_date();
            unset($game_data["contest_id"]);
            unset($game_data["is_cancel"]);
            $this->db->insert(CONTEST, $game_data);
        }
        return true;
    }

    public function get_seasons_score_update_data()
    {   
      $current_date_time = format_date();
        $close_date_time = date("Y-m-d H:i:s", strtotime($current_date_time . " -".CONTEST_CLOSE_INTERVAL." minutes"));
        return  $this->db->select('S.season_id,S.correct,S.wrong,S.tie_breaker_answer')
                        ->from(SEASON. " S")
                        ->join(LEAGUE. " L","S.league_id=L.league_id","left")
                        ->where('S.scheduled_date < ',$current_date_time)
                        ->where("(S.match_closure_date IS NULL OR S.match_closure_date > '".$close_date_time."')")
                        ->where('L.status',1)
                         /*->where('S.season_id',2)*/
                        ->get()->result_array();  
    }
    /**
    * Update scores in season picks
    * @param void
    * @return boolean
    */
    public function update_scores_in_picks_by_season()
    { 
        $seasons = $this->get_seasons_score_update_data();
       
        if(empty($seasons)) {
            return false;
        }
    
        foreach ($seasons as $key => $value) {
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
            
            $update  = "UPDATE ".$this->db->dbprefix(USER_TEAM_PICKS)." as UTP INNER JOIN 
            (SELECT P.pick_id,P.answer from ".$this->db->dbprefix(PICKS)." as P ) as GP ON UTP.pick_id=GP.pick_id 
            INNER JOIN ".$this->db->dbprefix(USER_TEAM)." UT ON UT.user_team_id=UTP.user_team_id
            SET score = 
             (CASE 

                WHEN  UTP.is_captain = 0 AND UTP.is_vc = 0 AND UTP.answer=GP.answer THEN  ".$value['correct']."
                WHEN  UTP.is_captain = 0 AND UTP.is_vc = 0 AND  UTP.answer!=GP.answer THEN -(".$value['wrong'].")
                WHEN  UTP.is_captain = 1 AND UTP.is_vc = 0 AND UTP.answer=GP.answer THEN (2 * ".$value['correct'].")
                WHEN  UTP.is_captain = 1 AND UTP.is_vc = 0 AND UTP.answer!=GP.answer THEN -(".$value['wrong'].")
                WHEN  UTP.is_captain = 0 AND UTP.is_vc = 1 AND UTP.answer=GP.answer THEN  ".$value['correct']."
                WHEN  UTP.is_captain = 0 AND UTP.is_vc = 1 AND UTP.answer!=GP.answer THEN 0
                WHEN  UTP.is_captain = 1 AND UTP.is_vc = 1 AND UTP.answer=GP.answer THEN (2 * ".$value['correct'].")
                WHEN  UTP.is_captain = 1 AND UTP.is_vc = 1 AND  UTP.answer!=GP.answer THEN 0
                ELSE 0 
                END
             ),
             status =
            (CASE 

                WHEN UTP.answer=GP.answer THEN 1
                WHEN UTP.answer!=GP.answer THEN 2
                ELSE 0 END 
            )  
            where UTP.pick_id=GP.pick_id and UT.season_id= ".$value['season_id']." and GP.answer>0 ";   
            $this->db->query($update);

            $sql = $this->db->select('UT.user_team_id,UT.user_id,UC.contest_id')
                            ->from(USER_TEAM . " AS UT")
                            ->join(USER_CONTEST . " AS UC", "UC.user_team_id = UT.user_team_id", 'INNER')
                            ->join(CONTEST . " AS C", "C.contest_id = UC.contest_id AND C.status != 1 ", 'INNER')
                            ->where('UT.season_id', $value['season_id'])
                            /*->group_by('UT.user_team_id')*/
                            ->get();
            $user_team_ids = $sql->result_array();
           //echo $this->db->last_query();die;
            //echo '<pre>';print_r($user_team_ids);die;
            if (!empty($user_team_ids)) {

                $ids = array_unique(array_column($user_team_ids, 'user_team_id'));
                $update_sql = " UPDATE  ".$this->db->dbprefix(USER_CONTEST)." AS UC 
                                    INNER JOIN   ".$this->db->dbprefix(USER_TEAM)." UT ON UT.user_team_id=UC.user_team_id
                                    INNER JOIN 
                                        ( SELECT SUM(L.score) AS scores,
                                        L.user_team_id 
                                            FROM 
                                                ".$this->db->dbprefix(USER_TEAM_PICKS)." AS L 
                                        WHERE 
                                            L.user_team_id IN (".implode(',', $ids).")
                                        GROUP BY 
                                            L.user_team_id
                                        ) AS UTP ON UTP.user_team_id = UC.user_team_id 
                                    SET 
                                        UC.total_score=UTP.scores
                                    WHERE UC.fee_refund=0
                                    ";
                //echo $update_sql;die;                    
                $this->db->query($update_sql);

                $contest_ids = array_values(array_unique(array_column($user_team_ids, 'contest_id')));
                //echo '<pre>';print_r($contest_ids);die;
                if(!empty($contest_ids))
                {
                    foreach ($contest_ids as $contest_id) {

                        $contest_info = 
                        $this->db->select("is_tie_breaker")
                                         ->from(CONTEST) 
                                         ->where("contest_id",$contest_id) 
                                         ->get()->row_array();
                                    $rank_str = "";
                                    if(isset($contest_info['is_tie_breaker']) && $contest_info['is_tie_breaker'] == 1){
                                      $rank_str = ",user_contest_id ";          
                                    }
                        $update_rank_sql = "UPDATE  ".$this->db->dbprefix(USER_CONTEST)." AS UC 
                                            INNER JOIN 
                                            (SELECT UC1.user_contest_id,RANK() OVER (ORDER BY `total_score` DESC,
                                           CASE WHEN ".$value['tie_breaker_answer'].">0 THEN  MIN(ABS(tie_breaker_answer - ".$value['tie_breaker_answer'].")) END ASC,user_contest_id ASC) user_rank
                                              FROM ".$this->db->dbprefix(USER_CONTEST)." AS UC1 INNER JOIN ".$this->db->dbprefix(USER_TEAM)." AS UT ON UC1.user_team_id=UT.user_team_id
                                              WHERE UC1.contest_id = ".$contest_id." group by user_contest_id) AS GP 
                                            ON GP.user_contest_id = UC.user_contest_id 
                                            SET 
                                            UC.game_rank = IFNULL(GP.user_rank,'0')
                                            WHERE UC.fee_refund=0 ";
                        //echo $update_rank_sql;die;
                        $this->db->query($update_rank_sql);
                    }
                }
                //clear cache
                $del_cache_key = 'picks_season_player_'.$value['season_id'];
                $this->delete_cache_data($del_cache_key);
                echo "Update lineup score for user_team_id:".implode(',', $ids);
            }else{
                 echo "No lineup score update";
            }

           $this->db->trans_complete();
           if ($this->db->trans_status() === FALSE ) {
              $this->db->trans_rollback();
           } else {
              $this->db->trans_commit();
           }

                       
        }
       
    }

    public function get_completed_seasons()
    {
        $current_date_time = format_date();    
        $past_time = date("Y-m-d H:i:s",strtotime($current_date_time." -7 days"));
        $this->db->select("S.season_id");
        $this->db->from(SEASON . " AS S");
        $this->db->join(LEAGUE . " AS L", "L.league_id = S.league_id", "left");
        $this->db->where("L.status", '1');
        $this->db->where("S.status", '2');
        $this->db->where("S.status_overview", '4'); // for result season
        
        $this->db->where("S.scheduled_date >= ",$past_time);
        $this->db->group_by("S.season_id");
        $sql = $this->db->get();
        //echo $this->db->last_query(); die;
        $matches = $sql->result_array();
        return $matches;
    }

    /**
    * Used for update contest status
    * @param int $sports_id
    * @return string print output
    */
    public function update_contest_status()
    {

     $current_game =  $this->get_completed_seasons();
     //echo "<pre>conets season: ";print_r($current_game);die;
     if(empty($current_game)) {
        return false;
     }
       
       //echo "<pre>conets season: ";print_r($current_game);die;
       if (!empty($current_game))
       {
           $all_season_id = array_column($current_game, 'season_id');
           $all_season_id_str = implode(',', array_map( function( $n ){ return '\''.$n.'\''; } ,  $all_season_id) );
           //0oecho '<pre>';print_r($all_season_id_str);die;
           $sql = $this->db->select('C.season_id, C.contest_id, C.contest_unique_id, C.contest_name, C.status')
                                   ->from(CONTEST." AS C")
                                   ->where("C.season_id IN ( $all_season_id_str )")
                                   ->where("C.season_id > ", 0)
                                   ->where("C.status", 0)
                                   ->where("C.total_user_joined >= ", 'C.minimum_size', FALSE)
                                   ->get();
           //echo $this->db->last_query(); die;
           $contest_data = $sql->result_array();
           //echo "<pre>";print_r($contest_data); die;
           if(!empty($contest_data))
           {
              $contest_ids = array_column($contest_data, 'contest_id');
              $score_check = $this->db->select("count(*) as total")
                    ->from(USER_CONTEST. " UC")
                    ->join(USER_TEAM_PICKS. " UTP","UTP.user_team_id=UC.user_team_id")
                    ->where_in("UC.contest_id",$contest_ids)
                    ->where("UC.total_score <> ","0")
                    ->where("UC.fee_refund","0")
                    ->where("UTP.status > ","0")
                    ->get()
                    ->row_array();
              if(!empty($score_check) && isset($score_check['total']) && $score_check['total'] > 0){
                $current_date = format_date();
                // Mark CONTEST Status Complete
                $this->db->where_in("contest_id", $contest_ids);
                $this->db->where("status", 0);
                $this->db->update(CONTEST, array("status" => 2,"modified_date" => $current_date ));
 
                 echo "Update status for contest having season_ids: ".$all_season_id_str." ";   
              }
            } 
           else
            {
               echo "No contest status update ";
            }
       }
       else
       {
           echo "No contest status update ";
       }
    }

   /**
   * Function used for distribute contest prize
   * @param 
   * @return boolean
   */
   public function prize_distribution($type)
   {        
        $this->db->select('C.season_id,C.currency_type,C.contest_id,C.is_tie_breaker,C.contest_unique_id,C.scheduled_date,C.entry_fee,C.size AS entries,C.prize_pool,C.prize_distibution_detail,C.minimum_size,C.site_rake,C.prize_type', FALSE)
            ->from(CONTEST . ' AS C')
            ->where('C.status', 2)
            ->where('C.scheduled_date < ', format_date())
            ->where('C.prize_type <> ', 4, FALSE)
            ->where('C.prize_distibution_detail != ', 'null' )
            ->group_by('C.contest_unique_id');

        $result = $this->db->get()->result_array();
        //echo "<pre>";print_r($result);die;
        if (!empty($result))
        {
          $this->load->helper('queue');
          $server_name = get_server_host_name();
          foreach ($result AS $prize)
          {
            $content = array();
           
          if(isset($prize['is_tie_breaker']) && $prize['is_tie_breaker'] == "1"){
            $content['url'] = $server_name."/picks/cron/contest_merchandise_distribution/".$prize['contest_id'];
          }else{
            $content['url'] =  $server_name."/picks/cron/contest_prize_distribution/".$prize['contest_id'];
          } 
            add_data_in_queue($content,'prize_distribution');
          }
        }
        return true;
  }

  /**
   * Function used for distribute contest prize
   * @param 
   * @return boolean
   */
  public function contest_prize_distribution($contest_id='')
  {
    if(!$contest_id){
      return false;
    }

    $prize_data = $this->db->select("C.season_id,C.contest_id,C.contest_unique_id,C.scheduled_date,C.entry_fee,C.size AS entries,C.size,C.prize_pool,C.prize_distibution_detail,C.minimum_size,C.site_rake,C.prize_type,C.currency_type,C.group_id,C.total_user_joined,C.guaranteed_prize,IF(C.user_id > 0,'1','0') as is_private,C.host_rake", FALSE)
                    ->from(CONTEST . ' AS C')
                    ->where('C.status', 2)
                    ->where('C.is_tie_breaker', 0)
                    ->where('C.scheduled_date < ', format_date())
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
        $match = $this->get_single_row('`match`',SEASON,['season_id'=>$prize_data['season_id']]);

        $prize = $prize_data;
        $contest_unique_id = $prize['contest_unique_id'];
        $contest_id = $prize['contest_id'];
        if(empty($prize['prize_distibution_detail']))
        {
          return;
        }

        $wining_amount = (array) json_decode($prize['prize_distibution_detail'], TRUE);
        $prize['per_user_prize'] = 1;
        $wining_amount = reset_contest_prize_data($prize);
        $wining_max = array_column($wining_amount, 'max');
        $winner_places = max($wining_max);
        if(empty($winner_places) || $winner_places == NULL || $winner_places == 0){
            return;
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
        //echo "<pre>";print_r($winning_amount_arr);
        //get winners
        $sql = "SELECT UT.user_id,UT.season_id,UC.total_score,UC.game_rank,UC.user_team_id,UC.user_contest_id,UC.contest_id 
                  FROM ".$this->db->dbprefix(USER_CONTEST)." AS UC 
                  INNER JOIN ".$this->db->dbprefix(USER_TEAM)."  AS UT ON UT.user_team_id = UC.user_team_id
                  WHERE UC.contest_id = ".$prize['contest_id']." AND UC.fee_refund=0 
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
        //echo "<pre>";print_r($winners);die;
        $user_lmc_data = array();
        $user_txn_data = array();
        if(!empty($winners))
        {
          foreach ($winners as $key => $value)
          {
            $won_data = $rank_prize_arr[$value['game_rank']];
            $is_winner = 0;
            $bonus_amount = $winning_amount = $points = 0;
            $prize_obj_tmp = $custom_data = array();
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

              $this->load->helper('queue_helper');
              $coin_data = array(
                  'oprator' => 'add', 
                  'user_id' => $value['user_id'], 
                  'total_coins' => $points, 
                  'bonus_date' => format_date("today", "Y-m-d")
              );
              add_data_in_queue($coin_data, 'user_coins');
            }

            
            
            $lmc_data = array();
            $lmc_data['user_team_id'] = $value['user_team_id'];
            $lmc_data['contest_id'] = $value['contest_id'];
            $lmc_data['is_winner'] = $is_winner;
            $lmc_data['prize_data'] = json_encode($prize_obj_tmp);
            $user_lmc_data[] = $lmc_data;


      

            // if(!empty($prize_obj_tmp)){
            //   foreach ($prize_obj_tmp as $c_key => $c_value) {
            //        $prize_obj_tmp[$c_key]['match'] = $match['match'];
            //   }
            // }
            $custom_data = array('match'=>$match['match']);

            //user txn data
            $order_data = array();
            $order_data["order_unique_id"] = $this->_generate_order_unique_key();
            $order_data["user_id"]        = $value['user_id'];
            $order_data["source"]         = CONTEST_WON_SOURCE;
            $order_data["source_id"]      = $value['user_contest_id'];
            $order_data["reference_id"]   = $value['contest_id'];
            $order_data["season_type"]    = 1;
            $order_data["type"]           = 0;
            $order_data["status"]         = 0;
            $order_data["real_amount"]    = 0;
            $order_data["bonus_amount"]   = $bonus_amount;
            $order_data["winning_amount"] = $winning_amount;
            $order_data["points"] = ceil($points);
            $order_data["custom_data"] =  json_encode($custom_data);
            $order_data["plateform"]      = PLATEFORM_FANTASY;
            $order_data["date_added"]     = format_date();
            $order_data["modified_date"]  = format_date();
            $user_txn_data[] = $order_data;
          }
        }
        //echo "<pre>";print_r($user_lmc_data);
        //echo "<pre>";print_r($user_txn_data);die;
        
        if(!empty($user_lmc_data)){
          try
          {
            $is_updated = 0;
            $this->db = $this->db;
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
              $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = 526 AND type=0 AND status=0 AND reference_id='".$contest_id."' GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
                SET U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
                WHERE O.source = 526 AND O.type=0 AND O.status=0 AND O.reference_id='".$contest_id."' ";
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

                // Game table update is_prize_distributed 1
                $this->picks_db->where('contest_id', $contest_id);
                $this->picks_db->update(CONTEST, array('status' => '3', 'modified_date' => format_date(),'completed_date' => format_date()));

                $this->load->helper('queue');
                $server_name = get_server_host_name();
                //for TDS
                $content = array();
                $content['url'] = $server_name."/picks/cron/deduct_tds_from_winning/".$contest_id;
                add_data_in_queue($content,'picks_tds');
                
                //for contest referral
               /* $content = array();
                $content['url'] = $server_name."/picks/cron/add_cash_contest_referral_bonus/".$contest_id;
                add_data_in_queue($content,'cron');

                //for every cash contest referral
                $content = array();
                $content['url'] = $server_name."/cron/cron/add_every_cash_contest_referral_benefits/".$contest_id;
                add_data_in_queue($content,'cron');*/

                //$this->deduct_tds_from_winning($contest_id);
                //$this->add_cash_contest_referral_bonus($contest_id);
                //$this->add_every_cash_contest_referral_benefits($contest_id);
                
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
   * Function used for distribute contest prize for merchandise case
   * @param:
   * @return:
   */
  public function contest_merchandise_distribution($contest_id)
  { 
    if(!$contest_id){
      return false;
    }
    $prize_data = $this->db->select("C.season_id,C.contest_id,C.contest_unique_id,C.scheduled_date,C.entry_fee,C.size AS entries,C.size,C.prize_pool,C.prize_distibution_detail,C.minimum_size,C.site_rake,C.prize_type,C.group_id,C.total_user_joined,C.guaranteed_prize,IF(C.user_id > 0,'1','0') as is_private,C.host_rake", FALSE)
                  ->from(CONTEST.' AS C')
                  ->where('C.status', 2)
                  ->where('C.is_tie_breaker', 1)
                  ->where('C.scheduled_date < ', format_date())
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
      $match = $this->get_single_row('`match`',SEASON,['season_id'=>$prize_data['season_id']]);

      $prize = $prize_data;
      $contest_unique_id = $prize['contest_unique_id'];
      $contest_id = $prize['contest_id'];
      if(empty($prize['prize_distibution_detail']))
      {
        return;
      }

      //$wining_amount = (array) json_decode($prize['prize_distibution_detail'], TRUE);
      $prize['per_user_prize'] = 1;
      $wining_amount = reset_contest_prize_data($prize);
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
      $sql = "SELECT UT.user_id,UT.season_id,UC.total_score,UC.game_rank,UC.user_team_id,UC.user_contest_id 
                FROM ".$this->db->dbprefix(USER_CONTEST)." AS UC 
                INNER JOIN ".$this->db->dbprefix(USER_TEAM)."  AS UT ON UT.user_team_id = UC.user_team_id
                WHERE UC.contest_id = ".$prize['contest_id']." AND UC.fee_refund=0 
                ORDER BY UC.game_rank ASC
                LIMIT ".$winner_places." ";
      $rs = $this->db->query($sql);
      $winners = $rs->result_array();

      //echo "<pre>";print_r($winning_amount_arr);die;
      $is_success = 0;
      $custom_data = array();
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

          // if(!empty($prize_obj_tmp)){
          //   foreach ($prize_obj_tmp as $c_key => $c_value) {
          //         $prize_obj_tmp[$c_key]['match'] = $match['match'];
          //   }
          // }
          $custom_data = array('match'=>$match['match']);

          $tmp_arr = array();
          $tmp_arr['user_id'] = $winner['user_id'];
          $tmp_arr['source'] = CONTEST_WON_SOURCE;
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
          $this->picks_db->where(array('user_contest_id' => $winner['user_contest_id']));
          $this->picks_db->update(USER_CONTEST, array('is_winner' => '1','prize_data' => json_encode($prize_obj_tmp)));
        }
      }

      if($is_success == 1){
        // Game table update is_prize_distributed 1
        $this->picks_db->where('contest_id', $contest_id);
        $this->picks_db->update(CONTEST, array('status' => '3', 'modified_date' => format_date(),'completed_date' => format_date()));

        $this->load->helper('queue');
        $server_name = get_server_host_name();
        //for TDS
        $content = array();
        $content['url'] = $server_name."/picks/cron/deduct_tds_from_winning/".$contest_id;
        add_data_in_queue($content,'picks_cron');
        
        //for contest referral
/*        $content = array();
        $content['url'] = $server_name."/cron/cron/add_cash_contest_referral_bonus/".$contest_id;
        add_data_in_queue($content,'cron');

        //for every cash contest referral
        $content = array();
        $content['url'] = $server_name."/cron/cron/add_every_cash_contest_referral_benefits/".$contest_id;
        add_data_in_queue($content,'cron');*/


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

          $orderData['order_unique_id'] = $this->_generate_order_key();
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
     * Used for generate order unique id
     * @return string
     */
    public function _generate_order_key() {
        $this->load->helper('security');
        $salt = do_hash(time() . mt_rand());
        $new_key = substr($salt, 0, 20);
        return $new_key;
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
          
          $this->db_user->select("O.source_id, O.user_id, O.real_amount, O.bonus_amount, SUM(O.winning_amount) AS total_real_amount")
                          ->from(ORDER . " O");
          $this->db_user->where("O.reference_id",$contest_id);
          $this->db_user->where("O.source",3);// 3: game won
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
                                          ->where("source", 11)
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
                                              'source' => 11, // TDS Deduction
                                              'source_id' => $contest_id,
                                              'reason' => CRON_TDS_NOTI
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
                if ($input_data["source"] == CONTEST_TDS_SOURCE && $user_balance["winning_amount"] < $amount) {
                    return false;
                }else if ($input_data["source"] != 8 && $input_data["source"] != 11 && $user_balance["real_amount"] < $amount) {
                    return false;
                }
                if ($input_data["source"] == CONTEST_TDS_SOURCE){
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
                    // Deduct Max 10% of entry fee from bonus amount.CONTEST_WON_SOURCE
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
            case 11:
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
        $orderData['order_unique_id'] = $this->_generate_order_key();
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
            if($input_data["source"] == CONTEST_TDS_SOURCE)
            {
                $tmp["notification_destination"] = 1;
                $tmp["notification_type"] = 130;
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
                $this->load->model('user/User_nosql_model');
                $this->User_nosql_model->send_notification($tmp);
            }
        }
       return array(
            'transaction_id' => $order_id,
            'order_id'       => $order_id,
            'bonus_balance'  => $bonus_bal,
            'balance'        => $real_bal);
    }

      /**
   * function used for send winning notification
   * @param void
   * @return boolean
   */
   public function match_prize_distribute_notification() 
   {
    $result = $this->db->select("UT.user_id,C.contest_id,C.season_id,C.contest_title,C.contest_name,C.status, C.contest_unique_id, C.sports_id,C.size, C.prize_pool, C.entry_fee, C.currency_type,UC.is_winner,UC.game_rank, UC.user_contest_id, UC.user_contest_id, C.prize_type,GROUP_CONCAT( DISTINCT UC.user_contest_id) as user_contest_ids,GROUP_CONCAT(DISTINCT UC.game_rank) as user_rank_list,GROUP_CONCAT( DISTINCT UT.team_name) as team_name,C.prize_distibution_detail,S.match,S.scheduled_date",false)
                  ->from(CONTEST . " C")
                  ->join(SEASON . " S", "S.season_id = C.season_id", "INNER")
                  ->join(USER_CONTEST . " UC", "UC.contest_id = C.contest_id AND UC.is_winner=1", "INNER")
                  ->join(USER_TEAM . " UT", "UT.user_team_id = UC.user_team_id", "INNER")
                  ->where("C.status", 3)
                  ->where("UC.is_winner", 1)
                  ->where("C.is_win_notify", 0)
                  ->group_by("S.season_id,C.contest_id,UT.user_id")
                  ->order_by("UT.user_id,C.contest_id,S.season_id")
                  ->get()
                  ->result_array();
    
    $match_data = array();
    $winning_contest = array();
    $notification_data = array();
    if (!empty($result)) 
    {   
      foreach ($result as $res) 
      {
        
        // print_r($winning_contest);die();
        $user_contest_ids = explode(',', $res['user_contest_ids']);
        $user_rank_list = explode(',', $res['user_rank_list']);
        $team_names = explode(',', $res['team_name']);
        $user_rank_list = array_unique($user_rank_list);

        $user_contest_ids = array_unique($user_contest_ids);
        $team_names = array_unique($team_names);
        $team_names = array_combine($user_contest_ids,$team_names);
        
        $team_ranks = array_combine($user_contest_ids,$user_rank_list);

        // echo "<pre>";print_r($team_ranks);die;
        $pre_query ="(SELECT user_id,GROUP_CONCAT(device_id ORDER BY keys_id DESC) as device_ids,GROUP_CONCAT(device_type ORDER BY keys_id DESC) as device_types ,keys_id,device_id,device_type FROM ".$this->db->dbprefix(ACTIVE_LOGIN)."  WHERE user_id =". $res['user_id']." AND device_id IS NOT NULL ORDER BY keys_id DESC)";

        $sql = $this->db_user->select("O.order_id,O.real_amount, O.bonus_amount, O.winning_amount,O.points, U.email,U.user_name,O.source_id,O.prize_image,O.custom_data,AL.device_id,AL.device_type,AL.device_ids,AL.device_types")
                ->from(ORDER . " O")
                ->join(USER . " U", "U.user_id = O.user_id", "INNER")
                ->join($pre_query.' AL','AL.user_id=U.user_id','LEFT')
                ->where("O.user_id", $res['user_id'])
                ->where("O.source", CONTEST_WON_SOURCE)
                ->where_in("O.source_id", $user_contest_ids)
                ->get();
        $order_info = $sql->result_array();
        // echo "<pre>";print_r($order_info);die;
        if (!empty($order_info)) 
        {
          $ord_custom_data = json_decode($order_info[0]['custom_data'],TRUE);
          if ($order_info[0]['bonus_amount'] > 0 || $order_info[0]['winning_amount'] > 0 || $order_info[0]['points'] > 0 || !empty($ord_custom_data))
          {
            // Game table update is_win_notify 1
            $this->db->where('contest_id', $res['contest_id']);
            $this->db->update(CONTEST, array('is_win_notify' => '1','modified_date' => format_date()));
            
            if(!isset($notification_data[$res['user_id']."_".$res['season_id']]) 
                || empty($notification_data[$res['user_id']."_".$res['season_id']]))
            {
              $user_temp_data = array();
              $user_temp_data['source_id'] = $res['season_id'];
              $user_temp_data['user_id'] = $res['user_id'];
              $user_temp_data['email'] = $order_info[0]['email'];
              $user_temp_data['deviceIDS'] = $order_info[0]['device_ids'];
              $user_temp_data['device_types'] = $order_info[0]['device_types'];
              $user_temp_data['user_name'] = $order_info[0]['user_name'];
              $user_temp_data['match'] = $res['match'];
              $user_temp_data['contest_data'] = array();
              $user_temp_data['scheduled_date'] = $res['scheduled_date'];
              //added for multigame module check in mail 
              $notification_data[$res['user_id']."_".$res['season_id']] = $user_temp_data;
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
              $contest_data['contest_name'] = !empty($res['contest_title']) ? $res['contest_title']:$res['contest_name'];
              $contest_data['match'] = $res['match'];
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
              $contest_data['sports_id'] = $res['sports_id'];              
              $notification_data[$res['user_id']."_".$res['season_id']]['contest_data'][] = $contest_data;
            }
          }
        }
      }

    }
    
    // echo "<pre>";print_r($notification_data);die;
    foreach($notification_data as $notification)
    {
        /* Send Notification */
        $notify_data = array();
        $notify_data['notification_type'] = CONTEST_WON_NOTIFY; //554-GameWon
        $notify_data['notification_destination'] = 5; //web, email
        $notify_data["source_id"] = $notification['source_id'];
        $notify_data["user_id"] = $notification['user_id'];
        $notify_data["to"] = $notification['email'];
        $notify_data["user_name"] = $notification['user_name'];
        $notify_data["sports_id"] = $notification['sports_id'];
        $notify_data["added_date"] = format_date();
        $notify_data["modified_date"] = format_date();
        $notify_data["subject"] = "Wohoo! You just WON!";
        $notify_data['device_details']=array();
        $unique_device_ids = explode(',',$notification['deviceIDS']);
        $u_device_types = explode(',',$notification['device_types']);                
        if(!empty($unique_device_ids[0]))
        {
          foreach($unique_device_ids as $d_key => $d_value) 
          {
            if(!empty($d_value) && !in_array($d_value,[1,2]))
            {
              $notify_data['device_details'][] =array(
                'device_id' => $d_value,
                'device_type' => $u_device_types[$d_key]) ;
            }
          }
        }
        $notify_data["device_ids"] = $notification['deviceIDS'];
        $notify_data['match'] = $notification['match'];
        $contest = array();
        $content['contest_data'] = $notification['contest_data'];
        $content['match'] = $notify_data['match'];
        $content['int_version'] = $this->app_config['int_version']['key_value'];
        $notify_data["content"] = json_encode($content);
        $this->load->model('user/User_nosql_model');
        $this->User_nosql_model->send_notification($notify_data); 
    }
    return;
  }

 
  /**
  * Function used for push contest for gst report
  * @param 
  * @return boolean
  */
  public function push_contest_for_gst_report()
  {
    $current_date = format_date();
    $contest_list = $this->db->select('C.contest_id,C.contest_name,C.entry_fee,C.prize_pool,C.site_rake,C.prize_type,S.match,S.season_id', FALSE)
                      ->from(CONTEST . ' AS C')
                      ->join(SEASON." AS S","S.season_id = C.season_id","INNER")
                      ->where('C.currency_type', 1)
                      ->where('C.entry_fee > ',0)
                      ->where('C.status', 3)
                      ->where('C.is_gst_report',0)
                      ->where('C.scheduled_date < ',$current_date)
                      ->order_by('C.contest_id','ASC')
                      ->get()
                      ->result_array();
    //echo "<pre>";print_r($contest_list);die;
    foreach($contest_list as $contest){
      $this->load->helper('queue');
      $server_name = get_server_host_name();
      $content = array();
      $content['url'] = $server_name."/picks/cron/generate_gst_report/".$contest['contest_id'];
      add_data_in_queue($content,'picks_gst');
      //$this->generate_gst_report($contest['contest_id']);
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
    $contest = $this->db->select('C.contest_id,C.contest_name,C.entry_fee,C.prize_pool,C.site_rake,C.prize_type,C.minimum_size as min_size,C.size as max_size,C.total_user_joined,S.match,S.season_id,S.scheduled_date', FALSE)
                      ->from(CONTEST . ' AS C')
                      ->join(SEASON." AS S","S.season_id = C.season_id","INNER")
                      ->where('C.currency_type', 1)
                      ->where('C.entry_fee > ',0)
                      ->where('C.status', 3)
                      ->where('C.is_gst_report',0)
                      ->where('C.scheduled_date < ',$current_date)
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
      $match_id = $contest['season_id'];
      $contest_id = $contest['contest_id'];
      $site_rake = $contest['site_rake'];
      $match_name = $contest['match'];
      $contest_name = $contest['contest_name'];
      $entry_fee = $contest['entry_fee'];
      $scheduled_date = $contest['scheduled_date'];
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
                          ->where('O.source', CONTEST_JOIN_SOURCE)
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

          $this->picks_db->where('contest_id', $contest_id);
          $this->picks_db->update(CONTEST, array('is_gst_report' => '1'));
        }
      }else{
        //update contest gst status as failed
        $this->picks_db->where('contest_id', $contest_id);
        $this->picks_db->update(CONTEST, array('is_gst_report' => '2'));
      }
    }
    return true;
  }

   /**
  * Function used for fetch list of invoices which have not been sent to users.
  * @param 
  * @return boolean
  */
  public function process_tax_invoices()
  {
      $invoice_data = $this->db_user->select('GR.*', FALSE)
                    ->from(GST_REPORT . ' AS GR')
                    ->where('GR.status', 1)
                    ->where('GR.module_type',1)
                    ->where('GR.is_invoice_sent',0)
                    ->get()
                    ->result_array();

      foreach($invoice_data as $invoice)
      {
          $this->load->helper('queue');
          $server_name = get_server_host_name();
          $tax_invoice = array();
          $tax_invoice['url'] = $server_name."/cron/cron/generate_tax_invoice/".$invoice['invoice_id'];
          add_data_in_queue($tax_invoice,'tax_invoice');
      }
  }

  /**
  * Used for generate tax invoice for a particular invoice and send to respective customer.
  * @param 
  * @return boolean
  */
  public function generate_tax_invoice($invoice_id)
  {
      if(!$invoice_id){
        return false;
      }
      $invoice_data = $this->db_user->select('GR.*, US.email, US.user_id, US.user_name, US.first_name, US.last_name, US.address, US.city, US.zip_code', FALSE)
                    ->from(GST_REPORT . ' AS GR')
                    ->join(USER." AS US","US.user_id = GR.user_id","inner")
                    ->where('GR.status', 1)
                    ->where('GR.module_type', 1)
                    ->where('GR.is_invoice_sent',0)
                    ->where('GR.invoice_id', $invoice_id)
                    ->get()
                    ->row_array();

      if (!empty($invoice_data))
      {
          /* Send Email */
          $notify_data = array();
          $notify_data['notification_type']         = 424; //GST invoice
          $notify_data['notification_destination']  = 4;
          $notify_data["source_id"]                 = "";
          $notify_data["user_id"]                   = $invoice_data['user_id'];
          $notify_data["to"]                        = $invoice_data['email'];
          $notify_data["user_name"]                 = $invoice_data['user_name'];
          $notify_data["added_date"]                = format_date();
          $notify_data["modified_date"]             = format_date();
          $notify_data["subject"]                   = "GST Invoice";

          $platform_fee     = number_format(($invoice_data['txn_amount'] * $invoice_data['site_rake'])/100);
          $taxable_value    = round((float)$platform_fee*100/118,2);
          $portal_state_id  = isset($this->app_config['allow_gst']['custom_data']['state_id']) ? $this->app_config['allow_gst']['custom_data']['state_id'] : 0;
          $cgst = $sgst = $igst = 0;

          if($invoice_data['state_id'] == $portal_state_id)
          {
            $cgst = round($taxable_value*9/100,2);
            $sgst = round($taxable_value*9/100,2);
          }
          else
          {
            $igst = round($taxable_value*18/100,2);
          }

          $content = array();
          $content['platform_fee']    = $platform_fee;
          $content['taxable_value']   = $taxable_value;
          $content['match'] = $invoice_data['match_name'];
          // $content['league_id']       = $invoice_data['league_id'];
          $content['contest_name']    = $invoice_data['contest_name'];
          $content["date"]            = format_date();
          $content['igst']            = $igst;
          $content['sgst']            = $sgst;
          $content['cgst']            = $cgst;
          $content['state']           = $invoice_data['state_name'];
          $content['entry_fee']       = $invoice_data['entry_fee'];
          $content['company_name']    = $this->app_config['allow_gst']['custom_data']['firm_name'];
          $content['company_address'] = $this->app_config['allow_gst']['custom_data']['firm_address'];
          $content['company_contact'] = $this->app_config['allow_gst']['custom_data']['contact_no'];
          $content['full_name']       = $invoice_data['first_name']." ".$invoice_data['last_name'];
          $content['email']           = $invoice_data['email'];
          $content['address']         = $invoice_data['address'];
          $content['city']            = $invoice_data['city'];
          $content['zip_code']        = $invoice_data['zip_code'];
          $notify_data["content"]     = json_encode($content);

          if($platform_fee > 0)
          {
            $this->load->model('user/User_nosql_model');
            $this->User_nosql_model->send_notification($notify_data);
          }

          $this->db_user->where('invoice_id',$invoice_id);
          $this->db_user->update(GST_REPORT, array('is_invoice_sent' => '1'));
      }
  }


  /**
     * Used for process season cancellation queue
     * @param 
     * @return boolean
     */

    function season_cancel_by_id($data) {
  
        $season_id = isset($data['season_id'])?$data['season_id']:0;  

        if(!$season_id){
            return false;
        }

        // for update season status
        if(isset($data['outside_season']) && $data['outside_season'] == 1){
            $where = array('season_id' => $season_id);
            $update_arr = array('status' => 5,'modified_date' => format_date());
            $this->db->update(SEASON, $update_arr, $where);
        }
               
        $this->db->select('contest_id, contest_unique_id, contest_name, entry_fee, season_id')
                ->from(CONTEST)
                ->where('status', 0)
                ->where("season_id",$season_id);
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            $contest_list = $query->result_array();
            if(!empty($contest_list)){    
                $this->load->helper('queue_helper');
                $data['action'] = 'cancel_game';   
                foreach($contest_list as $contest){                 
                    $data['contest_unique_id'] = $contest['contest_unique_id'];
                    add_data_in_queue($data, 'picks_game_cancel'); 
                }
            }
        }
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
        
        $this->db->select('C.contest_id, C.contest_unique_id, C.contest_title,C.contest_name, C.entry_fee, C.season_id, C.total_user_joined,MG.group_name,C.scheduled_date,S.match')
                ->from(CONTEST. ' C')
                ->join(SEASON." AS S", " S.season_id = C.season_id", 'INNER')
                ->join(MASTER_GROUP.' MG','C.group_id=MG.group_id')
                ->where('C.status', 0)
                ->where("C.contest_unique_id",$contest_unique_id);
        $this->db->limit(1);
        $contest_list = $this->db->get()->row_array();
        if (!empty($contest_list)) {

            //UPDATE contest status if participants 0
            if(isset($contest_list['total_user_joined']) && $contest_list['total_user_joined'] == "0"){              
              $where = array('status' => 0,'total_user_joined' => 0,'contest_id' => $contest_list['contest_id']);
              $update_arr = array('status' => 1,'is_win_notify' => 1,'modified_date' => format_date(),'cancel_reason' => $cancel_reason);
              $this->db->update(CONTEST, $update_arr, $where);

              if (CACHE_ENABLE) {
                $this->load->model('user/Nodb_model');
                $this->Nodb_model->flush_cache_data();
              }
            }else{
              $additional_data['contest_id'] = $contest_list['contest_id'];
              $additional_data['contest_name'] = $contest_list['contest_name'];
              $additional_data['cancel_reason'] = $cancel_reason;
              $additional_data['current_date'] = format_date();

              $additional_data['notification_type'] = CONTEST_CANCEL_NOTIFY;
              $additional_data['subject'] = ' has been Cancelled.';

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


    /**
     * Process game cancellation
     * @param array $contest_list
     * @param array $additional_data
     */
    function  process_game_cancellation($contest_list, $additional_data) {
      if (!empty($contest_list)) {

          //update game table for game cancel
          $contest_id = $contest_list['contest_id'];                   
          $refund_data = $this->db_user->select('O.order_id,O.real_amount,O.bonus_amount,O.winning_amount,O.points,O.source,O.source_id,O.user_id,O.reference_id,O.type')
                              ->from(ORDER . " AS O")
                              ->where('O.reference_id', $contest_id)
                              ->where('O.status', 1)
                              ->where('O.source', CONTEST_JOIN_SOURCE)
                              ->get()
                              ->result_array();
          //echo $this->db_user->last_query();die;
          // echo "<pre>";print_r($refund_data);die;
          $user_txn_data = array();
          if(!empty($refund_data)) {
            foreach ($refund_data as $key => $value) {
              //user txn data
              $order_data = array();
              $order_data["order_unique_id"] = $this->_generate_order_unique_key();
              $order_data["user_id"]        = $value['user_id'];
              $order_data["source"]         = CONTEST_CANCEL_SOURCE;//461- Game cancel
              $order_data["source_id"]      = $value['source_id'];
              $order_data["reference_id"]   = $value['reference_id'];
              $order_data["season_type"]    = 1;
              $order_data["type"]           = 0;
              $order_data["status"]         = 0;
              $order_data["real_amount"]    = $value['real_amount'];
              $order_data["bonus_amount"]   = $value['bonus_amount'];
              $order_data["winning_amount"] = $value['winning_amount'];
              $order_data["points"]         = $value['points'];
              // $order_data["plateform"]      = PLATEFORM_FANTASY;
              $order_data["date_added"]     = format_date();
              $order_data["modified_date"]  = format_date();
              $order_data["custom_data"]  = json_encode(array(
                'contest' => !empty($contest_list['contest_title']) ? $contest_list['contest_title']:$contest_list['contest_name'],
                'match'=>!empty($contest_list['match']) ? $contest_list['match']:'',
                'contest_type' => $contest_list['group_name'],
                'match_date' => date('d-M-Y H:i:s',strtotime($contest_list['scheduled_date'])) 
              ));
              $user_txn_data[] = $order_data;
            }
          }
          //echo "<pre>";print_r($user_txn_data);die;
          if(!empty($user_txn_data)){
            try
            {
              
              $this->picks_db->where("contest_id",$contest_id);
              $this->picks_db->update(USER_CONTEST, array('fee_refund' => 1));

              // 

              $this->db = $this->db_user;
              //Start Transaction
              $this->db->trans_strict(TRUE);
              $this->db->trans_start();
              
              $user_txn_arr = array_chunk($user_txn_data, 999);
              foreach($user_txn_arr as $txn_data){
                $this->insert_ignore_into_batch(ORDER, $txn_data);
              }

              $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = 525 AND type=0 AND status=0 AND reference_id='".$contest_id."' GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
              SET U.balance = (U.balance + OT.real_amount),U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
              WHERE O.source = 525 AND O.type=0 AND O.status=0 AND O.reference_id='".$contest_id."' ";

              $this->db->query($bal_sql);
              //Trasaction End
              $this->db->trans_complete();
              if ($this->db->trans_status() === FALSE ) { 
                $this->db->trans_rollback();
              } else {
                $this->db->trans_commit();
                // Game table update cancel status
                $this->picks_db->where('contest_id', $contest_id);
                $this->picks_db->update(CONTEST, array('status' => 1, 'modified_date' => $additional_data['current_date'], 'cancel_reason' => $additional_data['cancel_reason']));
              }

            } catch (Exception $e) {
                //echo 'Caught exception: '.  $e->getMessage(). "\n";die;
            }

          }

          $this->db = $this->picks_db;
          if (CACHE_ENABLE) {
            $this->load->model('user/Nodb_model');
            $this->Nodb_model->flush_cache_data();
          }
      }
    }


    /**
     * function used for send game cancel notification
     * @param void
     * @return boolean
     */
    public function match_cancel_notification($cancel_data = array()) {
      $notification_type = isset($cancel_data['notification_type']) ? $cancel_data['notification_type'] : CONTEST_CANCEL_NOTIFY;
      $cancel_reason = isset($cancel_data['cancel_reason']) ? $cancel_data['cancel_reason'] : "";
      $this->db->select("C.currency_type, S.scheduled_date, UT.user_id, C.contest_id, C.season_id, C.contest_name, C.contest_title,C.status, C.contest_unique_id, C.size, C.prize_pool, C.entry_fee, UC.user_contest_id, C.prize_type, count(DISTINCT UC.user_contest_id) as total_teams, C.cancel_reason,C.sports_id,S.match",false)
                    ->from(CONTEST . " C")
                    ->join(SEASON . " S", "S.season_id = C.season_id", "INNER")
                    ->join(USER_CONTEST . " UC", "UC.contest_id = C.contest_id", "INNER")
                    ->join(USER_TEAM . " UT", "UT.user_team_id = UC.user_team_id", "INNER")
                    ->where("C.status", 1)
                    ->where("UC.fee_refund", 1)
                    ->where("C.is_win_notify", 0)
                    ->group_by("S.season_id,C.contest_id,UT.user_id")
                    ->order_by("UT.user_id,C.contest_id,C.season_id");

      if(isset($cancel_data['contest_id']) && $cancel_data['contest_id'] != ""){
        $this->db->where("C.contest_id",$cancel_data['contest_id']);
      }
      $query = $this->db->get();
      $result = $query->result_array();     
      $notification_data = array();
      $this->db = $this->db_user;
      if (!empty($result)) {  
            
        foreach ($result as $res) {          
          if(!isset($notification_data[$res['user_id']."_".$res['season_id']]) || empty($notification_data[$res['user_id']."_".$res['season_id']])) {            
            $user_detail = $this->get_single_row('email, user_name', USER, array("user_id"=>$res["user_id"]));
            
            $user_temp_data = array();
            $user_temp_data['source_id'] = $res['season_id'];
            $user_temp_data['user_id'] = $res['user_id'];
            $user_temp_data['email'] = isset($user_detail['email']) ? $user_detail['email'] : "";
            $user_temp_data['user_name'] = isset($user_detail['user_name']) ? $user_detail['user_name'] : "";
           // $user_temp_data['match_data'] = $match_data[$res['season_game_uid']];
            $user_temp_data['match'] = $res['match'];


            $user_temp_data['scheduled_date'] = $res['scheduled_date'];

            // $user_temp_data['category_id'] = $res['category_id'];
            //$user_temp_data['season_game_count'] = $res['season_game_count'];
            $user_temp_data['contest_data'] = array();
            $notification_data[$res['user_id']."_".$res['season_id']] = $user_temp_data;
          }

          $contest_data = array();
          $contest_data['contest_id'] = $res['contest_id'];
          $contest_data['user_id'] = $res['user_id'];
          $contest_data['contest_unique_id'] = $res['contest_unique_id'];
          $contest_data['contest_name'] = $res['contest_name'];
          $contest_data['match'] = $res['match'];
          $contest_data['size'] = $res['size'];
          $contest_data['prize_pool'] = $res['prize_pool'];
          $contest_data['entry_fee'] = $res['entry_fee'];
          $contest_data['currency_type'] = $res['currency_type'];
          $contest_data['total_teams'] = $res['total_teams'];
          $contest_data['prize_type'] = $res['prize_type'];
          $contest_data['cancel_reason'] = $res['cancel_reason'];
          $contest_data['sports_id'] = $res['sports_id'];
          $contest_data['contest'] = !empty($res['contest_title']) ? $res['contest_title']:$res['contest_name'];
          
          $notification_data[$res['user_id']."_".$res['season_id']]['contest_data'][] = $contest_data;
          //$this->db = $this->picks_db;
          // Game table update is_win_notify 1 for cancel notification
          $this->picks_db->where('contest_id', $res['contest_id']);
          $this->picks_db->update(CONTEST, array('is_win_notify' => '1', 'modified_date' => format_date()));
        }

       
      }

      foreach($notification_data as $notification) {
        if(isset($notification['email']) && $notification['email'] != ""){
          /* Send Notification */
          $notify_data = array();
          $notify_data['notification_type'] = $notification_type;
          $notify_data['notification_destination'] = 5; //web, email
          $notify_data["source_id"] = $notification['source_id'];
          $notify_data["user_id"] = $notification['user_id'];
          $notify_data["to"] = $notification['email'];
          $notify_data["user_name"] = $notification['user_name'];
          $notify_data["added_date"] = format_date();
          $notify_data["modified_date"] = format_date();
          $notify_data["sports_id"] = $notification['sports_id'];
          $notify_data["subject"] = "Oops! Contests has been cancelled!";
          if($notification_type == CONTEST_CANCEL_NOTIFY) {
              $notify_data["subject"] = $cancel_data['contest_name'].$cancel_data["subject"];
              //$notify_data["subject"] = "Contests has been cancelled by technical team!";
          }

          $content = array();
          $content['notification_type'] = $notification_type;         
          $content['scheduled_date'] = $notification['scheduled_date'];         
          $content['match'] = $notification['match'];
          $content['contest'] = $notification['contest'];
     
          $content['contest_data'] = $notification['contest_data'];
          $content['int_version'] = $this->app_config['int_version']['key_value'];        
          if(!empty($notification['contest_data'])){
            $contest_names = array_column($notification['contest_data'], 'contest_name');
            $content['contest_name'] = implode(",", $contest_names);
          }
          $content['cancel_reason'] = $cancel_reason;
          if(isset($notification['contest_data']['0']['cancel_reason']) && $notification['contest_data']['0']['cancel_reason'] != ""){
            $content['cancel_reason'] = $notification['contest_data']['0']['cancel_reason'];
          }
          $notify_data["content"] = json_encode($content);
          $this->load->model('user/User_nosql_model');
          $this->User_nosql_model->send_notification($notify_data);
        }
      }
      return;
    }

    public function game_cancellation($is_recursive="0") {   
      set_time_limit(0);
      $current_date = format_date();          
      // When no user joined contest and open contest will cancel directly because no further action required for those contest
      if($is_recursive == "0"){
        $where = array('status' => 0,'total_user_joined' => 0,'scheduled_date <' => $current_date );
        $this->db->update(CONTEST, array('status' => 1), $where);
      }
      $contest_list = $this->db->select("C.contest_id,C.contest_title, C.contest_unique_id, C.contest_name, C.entry_fee, C.season_id, MG.group_name, C.currency_type,C.scheduled_date,S.match")
                                     ->from(CONTEST." AS C")
                                     ->join(SEASON." AS S", " S.season_id = C.season_id", 'INNER')
                                     ->join(MASTER_GROUP . " AS MG", " MG.group_id = C.group_id", 'INNER')
                                     ->where("C.status", 0)
                                     ->where("C.total_user_joined > ", 0)
                                     ->where("C.total_user_joined < ", 'C.minimum_size', FALSE)
                                     ->where("C.scheduled_date < ", $current_date)
                                     ->limit(1)
                                     ->get()
                                     ->row_array();  
                         
      if (!empty($contest_list)) {
          $subject = "Oops! Contest has been Cancelled";
          if(isset($contest_list['group_name']) && $contest_list['group_name'] != ""){
            $subject = "Oops! ".$contest_list['group_name']." has been Cancelled";
          }


          $additional_data['cancel_reason'] = 'due to insufficient participants';
          $additional_data['current_date'] = $current_date;
          $additional_data['notification_type'] = CONTEST_CANCEL_NOTIFY;
          $additional_data['subject'] = $subject;

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
   * Used for get master email template list
   * @param void
   * @return array
  */
  public function get_email_template_list(){
      $result = $this->db_user->select("template_name,template_path,notification_type,status,subject")
                ->from(EMAIL_TEMPLATE)
                ->where("status",1)
                ->get()
                ->result_array();
      return $result;
  }

 /**
  * Remove Unpublished Fixture when schedule time gone
  * @param void
  * @return boolean
  */
  public function remove_unpublished_fixture(){
      $current_date = format_date();

      $delete = "DELETE S1 FROM ".$this->db->dbprefix(SEASON)." S1 JOIN ".$this->db->dbprefix(SEASON)." S2 
      ON S1.season_id = S2.season_id AND S2.published=0 and  S2.status = 0 and S2.scheduled_date < '".$current_date."'";
      $this->db->query($delete);
      return true;
  }
 
}
