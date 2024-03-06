<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Project_info_model extends MY_Model {
    
    public $db_user ;
    public $db_fantasy ;
    public $testingNode = FALSE;

    public function __construct() 
    {
       	parent::__construct();
		    $this->db_user		= $this->load->database('db_user', TRUE);
		    $this->db_fantasy	= $this->load->database('db_fantasy', TRUE);
    }


  public function get_project_deposit($limit)
  {   
      if($limit == '') {$limit = 2;}
      $this->db_user->select('SUM(real_amount) AS amount,
                               DATE_FORMAT(date_added, "%Y-%m-01") AS month_date')
                    ->from(ORDER)
                    ->where('source', 7)
                    ->where('status', 1)
                    ->group_by('month_date')
                    ->order_by('month_date', 'DESC')
                    ->limit($limit);
      $sql = $this->db_user->get();
      //echo $this->db_user->last_query();die;
      $amount_info = $sql->result_array();
      return $amount_info;
  }


  public function get_system_user_reports($post_data)
  {    
      $limit    = 50;
      $page   = 0;
      //$post_data = $this->input->post();
      $total = 0;
      $current_date = format_date();
      $previous_date = date("Y-m-d H:i:s",strtotime('-10 days',strtotime($current_date)));
      if(isset($post_data['from_date']) && $post_data['from_date'] != '')
      {
        $previous_date = $post_data['from_date'];
      }
      if(isset($post_data['to_date']) && $post_data['to_date'] != '')
      {
        $current_date = $post_data['to_date'];
      } 
      
      if(isset($post_data['items_perpage']) && $post_data['items_perpage'])
      {
        $limit = $post_data['items_perpage'];
      }

      if(isset($post_data['current_page']) && $post_data['current_page'])
      {
        $page = $post_data['current_page']-1;
      }
      
      $offset = $limit * $page;
      
      $sql = $this->db_fantasy->select("C.contest_id,CM.collection_master_id,
        CM.season_scheduled_date, 
        CM.collection_name,
        round(SUM( C.total_user_joined)/(count(LMC.lineup_master_contest_id)/count(DISTINCT C.contest_id))) AS total_user_joined, 
        round(SUM( C.total_system_user)/(count(LMC.lineup_master_contest_id)/count(DISTINCT C.contest_id))) AS total_system_user",FALSE)
          ->from(CONTEST.' AS C')
              ->join(COLLECTION_MASTER.' AS CM', 'CM.collection_master_id = C.collection_master_id')
              ->join(LINEUP_MASTER.' AS LM', 'LM.collection_master_id = CM.collection_master_id AND LM.is_systemuser = 1 ')
              ->join(LINEUP_MASTER_CONTEST.' AS LMC', 'LMC.lineup_master_id = LM.lineup_master_id')
              ->where("C.status","3")
              ->where("C.total_system_user >",0);
      

      if($previous_date != $current_date)
      {
        $this->db_fantasy->where('DATE_FORMAT(CM.season_scheduled_date,"%Y-%m-%d") BETWEEN "'. date('Y-m-d', strtotime($previous_date)). '" and "'. date('Y-m-d', strtotime($current_date)).'"');
      }else
      {
        $this->db_fantasy->like("DATE_FORMAT(CM.season_scheduled_date,'%Y-%m-%d')",$previous_date);
      }   
      if(isset($post_data['league_id']) && $post_data['league_id'] != '')
      {
        $this->db_fantasy->where("C.league_id",$post_data['league_id']);
      }
      if(isset($post_data['sports_id']) && $post_data['sports_id'] != '')
      {
        $this->db_fantasy->where("C.sports_id",$post_data['sports_id']);
      }  
      $this->db_fantasy->order_by("CM.season_scheduled_date","DESC");
      $this->db_fantasy->group_by("CM.collection_master_id");
      
      $tempdb = clone $this->db_fantasy;
        $temp_q = $tempdb->get();
        $all_result_array = $temp_q->result_array();
      if(isset($post_data['current_page']) && $post_data['current_page']==1) {
        $total = $temp_q->num_rows();
      }

      $sql = $this->db_fantasy->limit($limit,$offset)
              ->get();
      //echo $this->db_fantasy->last_query();die;       
      $result = $sql->result_array();
      $result=($result)?$result:array();
      return array('result'=>$result,'total'=>$total);
  }

  public function get_contest_ids($cmid)
  {
      $result = $this->db_fantasy->select('contest_id')
      ->from(CONTEST)
      ->where('collection_master_id',$cmid)
      ->get()->result_array();
      return array_column($result,'contest_id');
  }

  public function get_contest_details($contest_id_arr)
  {
    $result = $this->db_user->select('O.reference_id,sum(O.real_amount+O.winning_amount) as real_amount,sum(O.bonus_amount) as bonus_amount')
    ->from(ORDER.' AS O')
    ->join(USER.' AS U','U.user_id = O.user_id','left')
    ->where_in('reference_id',$contest_id_arr)
    ->where('source',1)
    ->where('U.is_systemuser',0)
    ->group_by('reference_id')
    ->get()->result_array();
    // echo $this->db->last_query();exit;
    return $result;
  }

  public function get_bot_prize($contest_id_arr)
  {
      $result = $this->db_fantasy->select("LMC.contest_id,round((SUM((
      CASE WHEN ( 
      JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=1 AND `LMC`.`is_winner` = 1 AND LM.is_systemuser = 0) 
      THEN (JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].amount' ))) 
      ELSE 0 END))) , 2 ) AS realuser_winnings,
      round((SUM((
      CASE WHEN ( 
      JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].prize_type'))=1 AND `LMC`.`is_winner` = 1 AND LM.is_systemuser = 1) 
      THEN (JSON_UNQUOTE(json_extract(LMC.prize_data, '$[0].amount' ))) 
      ELSE 0 END))) , 2 ) AS systemuser_winnings
      ",FALSE)
      ->from(LINEUP_MASTER_CONTEST." AS LMC")
      ->join(LINEUP_MASTER.' LM','LMC.lineup_master_id = LM.lineup_master_id','LEFT')
      ->where_in("LMC.contest_id", $contest_id_arr)
      ->group_by('LMC.contest_id')
      ->get()->result_array();
      $bot_prize_data= array();
      foreach($result as $res){
        $bot_prize_data[$res['contest_id']]= $res;
      }
      return $bot_prize_data;
    
   }

   public function get_balance_info(){
    $this->db = $this->db_user;
    $result = $this->get_single_row('IFNULL(SUM(balance),0) as deposit,IFNULL(SUM(winning_balance),0) as winning', USER, array("status"=>"1"));
    return $result;
   }



}
