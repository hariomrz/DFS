<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Prize_model extends MY_Model {
  public $db_user ;
  public $db_fantasy ;
  public function __construct() 
  {
   	parent::__construct();
    $this->db_user		= $this->load->database('db_user', TRUE);
    $this->db_fantasy	= $this->load->database('db_fantasy', TRUE);
  }

  /**
   * Function used for distribute contest prize
   * @param 
   * @return boolean
   */
  public function prize_distribution($type)
  {        
    $this->db_fantasy->select('C.collection_master_id,C.group_id', FALSE)
        ->from(CONTEST . ' AS C')
        ->where('C.is_tie_breaker','0')
        ->where('C.status', 2)
        ->where('C.season_scheduled_date < ', format_date())
        ->where('C.prize_type <> ', 4, FALSE)
        ->where('C.prize_distibution_detail != ', 'null' )
        ->group_by('C.collection_master_id')
        ->order_by('C.season_scheduled_date','DESC')
        ->order_by("FIELD(C.group_id,'1','2') ASC");
    if($type == "1"){
      $this->db_fantasy->group_by('C.group_id');
    }
    $result = $this->db_fantasy->get()->result_array();
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
        $content['url'] = $server_name."/cron/prize/match_prize_distribution/".$prize['collection_master_id'].$group_str;
        add_data_in_queue($content,'prize_distribution');
      }
    }
    return true;
  }

  /**
   * Function used for distribute match contest prize
   * @param 
   * @return boolean
   */
  public function match_prize_distribution($collection_master_id,$group_id='')
  {
    if(!$collection_master_id){
      return false;
    }

    $this->db_fantasy->select("C.contest_id,C.contest_unique_id,C.season_scheduled_date,C.master_duration_id AS league_contest_type_id,C.entry_fee,C.master_contest_type_id,C.size AS entries,C.size,C.prize_pool,C.prize_distibution_detail,C.is_uncapped,C.minimum_size,C.site_rake,C.prize_type,C.group_id,C.contest_access_type,C.currency_type,C.total_user_joined,C.guaranteed_prize,IF(C.user_id > 0,'1','0') as is_private,C.host_rake,C.contest_name", FALSE)
        ->from(CONTEST . ' AS C')
        ->where('C.status', 2)
        ->where('C.is_tie_breaker', 0)
        ->where('C.season_scheduled_date < ', format_date())
        ->where('C.prize_type <> ', 4, FALSE)
        ->where('C.prize_distibution_detail != ', 'null' )
        ->where('C.collection_master_id', $collection_master_id)
        ->group_by('C.contest_id')
        ->order_by("FIELD(C.group_id,'1','2') ASC")
        ->order_by('C.total_user_joined','DESC');
    if(isset($group_id) && $group_id != ""){
      $this->db_fantasy->where('C.group_id',$group_id);
    }
    $result = $this->db_fantasy->get()->result_array();
    //echo "<pre>";print_r($result);
    if (!empty($result))
    {
      $h2h_challenge = isset($this->app_config['h2h_challenge'])?$this->app_config['h2h_challenge']['key_value']:0;
      $h2h_group_id = isset($this->app_config['h2h_challenge']['custom_data']['group_id']) ? $this->app_config['h2h_challenge']['custom_data']['group_id'] : 0;
      $h2h_contest_ids = array();
      $contest_ids = array();
      $user_lmc_data = array();
      $user_txn_data = array();
      $private_contest = array();
      foreach($result as $prize){
        $contest_unique_id = $prize['contest_unique_id'];
        $contest_id = $prize['contest_id'];
        $contest_name = isset($prize['contest_name'])?$prize['contest_name']:'';
        
        if(empty($prize['prize_distibution_detail']))
        {
          return;
        }
        //h2h contest ids
        if($h2h_challenge == 1 && $h2h_group_id == $prize['group_id']){
          $h2h_contest_ids[] = $contest_id;
        }
        $contest_ids[] = $contest_id;

        //$wining_amount = (array) json_decode($prize['prize_distibution_detail'], TRUE);
        $prize['per_user_prize'] = 1;
        $wining_amount = reset_contest_prize_data($prize);
        //echo "<pre>";print_r($wining_amount);die;
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
        //echo "<pre>";print_r($winning_amount_arr);

        //get winners
        $sql = "SELECT LM.user_id,LM.collection_master_id,LMC.total_score,LMC.game_rank,LMC.lineup_master_id,LMC.lineup_master_contest_id,LMC.contest_id 
                  FROM ".$this->db_fantasy->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC 
                  INNER JOIN ".$this->db_fantasy->dbprefix(LINEUP_MASTER)."  AS LM ON LM.lineup_master_id = LMC.lineup_master_id
                  WHERE LMC.contest_id = ".$prize['contest_id']." AND LMC.fee_refund=0 
                  AND game_rank <= ".$winner_places."
                  ORDER BY LMC.game_rank ASC";
        $rs = $this->db_fantasy->query($sql);
        $winners = $rs->result_array();
        $tie_up_postion = array_count_values(array_column($winners,"game_rank","lineup_master_contest_id"));  
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
            $lmc_data['lineup_master_id'] = $value['lineup_master_id'];
            $lmc_data['contest_id'] = $value['contest_id'];
            $lmc_data['is_winner'] = $is_winner;
            $lmc_data['prize_data'] = json_encode($prize_obj_tmp);
            $user_lmc_data[] = $lmc_data;

            $custom_data = array();
            $custom_data['contest_name'] = $contest_name;
            //user txn data
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

            //update vi_user as u inner join `vi_order` as o on o.user_id=u.user_id set u.winning_balance=(u.winning_balance + o.winning_amount),u.bonus_balance=(u.bonus_balance + o.bonus_amount),u.point_balance=(u.point_balance + o.points),o.status=1 WHERE o.`source` = 3 AND o.`type` = 0 AND o.`status` = 0 and o.reference_id=195549
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

              // Deduct TDS If winning is greated than prize limit 
              $this->load->helper('queue');
              $server_name = get_server_host_name();
              foreach ($contest_ids AS $contest_id)
              {
                //for TDS
                $content = array();
                $content['url'] = $server_name."/cron/cron/deduct_tds_from_winning/".$contest_id;
                add_data_in_queue($content,'tds');
                
                //for contest referral
                $content = array();
                $content['url'] = $server_name."/cron/cron/add_cash_contest_referral_bonus/".$contest_id;
                add_data_in_queue($content,'referral');

                //for every cash contest referral
                $content = array();
                $content['url'] = $server_name."/cron/cron/add_every_cash_contest_referral_benefits/".$contest_id;
                add_data_in_queue($content,'referral');
              }

              //private contest rake
              foreach($private_contest as $contest_id){
                $data = array('contest_id' => $contest_id);
                add_data_in_queue($data, 'host_rake');
              }

              //for update h2h users level
              if($h2h_challenge == 1 && !empty($h2h_contest_ids))
              {
                $content = array();
                $content['url'] = $server_name."/cron/cron/update_h2h_user_level/match_".$collection_master_id;
                add_data_in_queue($content,'h2h');
              }

              if (CACHE_ENABLE) {
                //$this->load->model('Nodb_model');
                //$this->Nodb_model->flush_cache_data();
                $delete_cache_arr = array("contest_id"=>$contest_id,"collection_master_id"=>$collection_master_id);
                $this->delete_common_cache_for_contest($delete_cache_arr);
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
    $result = $this->db_fantasy->select('C.currency_type,C.contest_id,C.is_tie_breaker,C.contest_unique_id,C.season_scheduled_date,C.master_duration_id AS league_contest_type_id,C.entry_fee,C.master_contest_type_id,C.size AS entries,C.prize_pool,C.prize_distibution_detail,C.is_uncapped,C.minimum_size,C.site_rake,C.prize_type', FALSE)
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
        $content['url'] = $server_name."/cron/cron/contest_merchandise_distribution/".$prize['contest_id'];
        add_data_in_queue($content,'prize_distribution');
      }
    }
    return true;
  }
}
