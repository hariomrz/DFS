<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Finance_erp_model extends MY_Model{

	function __construct()
	{
	  parent::__construct();
  }

  public function get_expenses_income($post_data){
    $this->db->select('FC.type,SUM(T.amount) as total',FALSE)
          ->from(FINANCE_CATEGORY." AS FC")
          ->join(FINANCE_DASHBOARD. " AS T","FC.category_id = T.category_id","LEFT");

    if(isset($post_data['from_date']) && $post_data['from_date'] != ""){
      $this->db->where('T.record_date >= ',$post_data['from_date']);
    }
    if(isset($post_data['to_date']) && $post_data['to_date'] != ""){
      $this->db->where('T.record_date <= ',$post_data['to_date']);
    }

    $this->db->group_by('FC.type');
    $sql = $this->db->get();
    $result = $sql->result_array();
    return $result;
  }

  public function get_expenses_income_record($post_data,$type=0){
    $sql_where = "";
    if(isset($post_data['from_date']) && $post_data['from_date'] != ""){
      $sql_where.= " AND T.record_date >='".$post_data['from_date']."'";
    }
    if(isset($post_data['to_date']) && $post_data['to_date'] != ""){
      $sql_where.= " AND T.record_date <='".$post_data['to_date']."'";
    }
    $this->db->select('FC.category_id,FC.name,FC.is_custom,IFNULL(SUM(T.amount),0) as total',FALSE)
          ->from(FINANCE_CATEGORY." AS FC")
          ->join(FINANCE_DASHBOARD. " AS T","FC.category_id = T.category_id ".$sql_where,"LEFT");
    $this->db->where('FC.type',$type);
    $this->db->group_by('FC.category_id');
    $this->db->order_by('FC.category_id','ASC');
    $this->db->order_by('FC.is_custom','ASC');
    $sql = $this->db->get();
    $result = $sql->result_array();
    return $result;
  }

  public function get_category_list($post_data){
    $this->db->select('FC.category_id,FC.name,FC.type,FC.is_custom',FALSE)
          ->from(FINANCE_CATEGORY." AS FC");

    if(isset($post_data['type']) && $post_data['type'] != ""){
      $this->db->where('FC.type',$post_data['type']);
    }
    if(isset($post_data['is_custom']) && $post_data['is_custom'] != ""){
      $this->db->where('FC.is_custom',$post_data['is_custom']);
    }
    if(isset($post_data['order_by']) && $post_data['order_by'] != ""){
      $this->db->order_by('FC.category_id',$post_data['order_by']);
    }else{
      $this->db->order_by('FC.category_id','ASC');
    }
    $sql = $this->db->get();
    $result = $sql->result_array();
    return $result;
  }

  public function save_category($data)
  {
    $this->db->insert(FINANCE_CATEGORY,$data);
    return $this->db->insert_id();
  }

  public function update_category($data,$category_id)
  {
    $this->db->where('category_id', $category_id)
        ->update(FINANCE_CATEGORY, $data); 
    return true;
  }

  public function get_transaction_list($post_data){ 
    $limit = 10;
    $page = 0;
    $order_field = "record_date";
    $order_by = "DESC";
    if(isset($post_data['items_perpage'])) {
      $limit = $post_data['items_perpage'];
    }
    if(isset($post_data['current_page'])) {
      $page = $post_data['current_page']-1;
    }
    if(isset($post_data['order_field']) && $post_data['order_field'] != "") {
      $order_field = $post_data['order_field'];
    }
    if(isset($post_data['order_by']) && $post_data['order_by'] != "") {
      $order_by = $post_data['order_by'];
    }
    $offset = $limit * $page;
  
    $tz_diff = get_tz_diff($this->app_config['timezone']);

    $this->db->select("T.finance_id,T.amount,T.description,CONVERT_TZ(T.record_date, '+00:00', '".$tz_diff."') AS record_date,FC.name as category_name,FC.type,T.category_id",FALSE)
          ->from(FINANCE_DASHBOARD." AS T")
          ->join(FINANCE_CATEGORY. " AS FC","FC.category_id = T.category_id")
          ->where("FC.is_custom","1");

    if(isset($post_data['type']) && $post_data['type'] != ""){
      $this->db->where('FC.type',$post_data['type']);
    }
    if(isset($post_data['category_id']) && $post_data['category_id'] != ""){
      $this->db->where('T.category_id',$post_data['category_id']);
    }
    if(isset($post_data['from_date']) && $post_data['from_date'] != ""){
      $this->db->where('T.record_date >= ',$post_data['from_date']);
    }
    if(isset($post_data['to_date']) && $post_data['to_date'] != ""){
      $this->db->where('T.record_date <= ',$post_data['to_date']);
    }
    if(isset($order_field) && isset($order_by)){
      $this->db->order_by($order_field,$order_by);
    }else{
      $this->db->order_by('T.record_date','DESC');
    }

    $tempdb = clone $this->db;
    //count
    $query = $this->db->get();
    $total = $query->num_rows();

    if(!isset($post_data['csv']) || $post_data['csv'] != 1){
      $tempdb->limit($limit,$offset);
    }
    $sql = $tempdb->get();
    $result = $sql->result_array();
    return array('result'=>$result,'total'=>$total);
  }

  public function save_transaction($data)
  {
    $this->db->insert(FINANCE_DASHBOARD,$data);
    return $this->db->insert_id();
  }

  public function update_transaction($data,$finance_id)
  {
    $this->db->where('finance_id', $finance_id)
        ->update(FINANCE_DASHBOARD, $data); 
    return true;
  }

  public function delete_transaction($finance_id) {
    $this->db->where("finance_id",$finance_id);
    $this->db->delete(FINANCE_DASHBOARD);
    return true;
  }

  public function update_finance_data($post_data=array()) {
    $cat_arr = array();
    $cat_arr['withdrawal'] = array('id'=>"1","name"=>"Amount Disbursed");
    $cat_arr['admin_real'] = array('id'=>"2","name"=>"Real Cash deposited by Admin");
    $cat_arr['admin_winning'] = array('id'=>"3","name"=>"Winning Deposited by Admin");
    $cat_arr['referral_distributed'] = array('id'=>"4","name"=>"Real cash Referral Distributed");
    $cat_arr['coin_redeemed'] = array('id'=>"5","name"=>"Coin Redeemed for Real Cash");
    $cat_arr['bot_join_fee'] = array('id'=>"6","name"=>"Bots Joining Paid");
    $cat_arr['promo_code_amount'] = array('id'=>"7","name"=>"By Promo Code discounted");
    $cat_arr['deal_deposit'] = array('id'=>"8","name"=>"By Deals Deposit");
    $cat_arr['bot_winning'] = array('id'=>"10","name"=>"Bots Winning");
    $cat_arr['in_app_purchase'] = array('id'=>"11","name"=>"In App Purchase");

    $added_date = format_date();
    $from_date = $to_date = date('Y-m-d', strtotime($added_date.' -1 day'));
    if(isset($post_data['from_date']) && $post_data['from_date'] != ""){
      $from_date = $post_data['from_date'];
    }
    if(isset($post_data['to_date']) && $post_data['to_date'] != ""){
      $to_date = $post_data['to_date'];
    }
    //echo $from_date."====".$to_date;die;
    $referral_source = array("51","54","57","60","63","66","69","72","75","78","81","84","87","90","93","96","99","106","133","139","142","154","157","160","163","166","169","172","270","273","276","279");
    $promo_code = array("6","30","31","32","121","122","124");
    $source = array("0","1","8","136","146","283");
    $source = array_merge($source,$referral_source,$promo_code);
    $this->db->select("DATE_FORMAT(O.date_added,'%Y-%m-%d') as record_date,IFNULL(SUM(CASE WHEN O.source=0 THEN O.real_amount ELSE 0 END),0) as admin_real,IFNULL(SUM(CASE WHEN O.source=0 THEN O.winning_amount ELSE 0 END),0) as admin_winning,IFNULL(SUM(CASE WHEN O.source=8 THEN O.winning_amount ELSE 0 END),0) as withdrawal,IFNULL(SUM(CASE WHEN O.source=1 AND U.is_systemuser=1 THEN O.real_amount ELSE 0 END),0) as bot_join_fee,IFNULL(SUM(CASE WHEN O.source=136 THEN O.real_amount ELSE 0 END),0) as deal_deposit,IFNULL(SUM(CASE WHEN O.source=146 THEN O.real_amount ELSE 0 END),0) as coin_redeemed,IFNULL(SUM(CASE WHEN O.source IN(".implode(',',$referral_source).") THEN O.real_amount ELSE 0 END),0) as referral_distributed,IFNULL(SUM(CASE WHEN O.source IN(".implode(',',$promo_code).") THEN O.real_amount ELSE 0 END),0) as promo_code_amount,IFNULL(SUM(CASE WHEN O.source=283 THEN O.real_amount ELSE 0 END),0) as in_app_purchase,IFNULL(SUM(CASE WHEN O.source=3 AND U.is_systemuser=1 THEN O.winning_amount ELSE 0 END),0) as bot_winning",FALSE);
    $this->db->from(ORDER." AS O");
    $this->db->join(USER." AS U","U.user_id = O.user_id");
    $this->db->where("O.status","1");
    $this->db->where("DATE_FORMAT(O.date_added,'%Y-%m-%d') >= ",$from_date);
    $this->db->where("DATE_FORMAT(O.date_added,'%Y-%m-%d') <= ",$to_date);
    $this->db->where_in("O.source",$source);
    $this->db->group_by("DATE_FORMAT(O.date_added,'%Y-%m-%d')");
    //$this->db->limit(10);
    $sql = $this->db->get();
    $result = $sql->result_array();
    //echo $this->db->last_query();die;

    //contest promo code discount
    $this->db->select("DATE_FORMAT(PCE.added_date,'%Y-%m-%d') as record_date,IFNULL(SUM(PCE.amount_received),0) as total",FALSE);
    $this->db->from(PROMO_CODE_EARNING." AS PCE");
    $this->db->join(PROMO_CODE." AS PC","PC.promo_code_id = PCE.promo_code_id");
    $this->db->where("PC.type","3");
    $this->db->where("PCE.is_processed","1");
    $this->db->where("DATE_FORMAT(PCE.added_date,'%Y-%m-%d') >= ",$from_date);
    $this->db->where("DATE_FORMAT(PCE.added_date,'%Y-%m-%d') <= ",$to_date);
    $this->db->group_by("DATE_FORMAT(PCE.added_date,'%Y-%m-%d')");
    $sql = $this->db->get();
    $contest_discount = $sql->result_array();
    $contest_discount = array_column($contest_discount,"total","record_date");
    //echo "<pre>";print_r($result);die;
    $finance_data = array();
    foreach($result as $row){
      foreach($cat_arr as $cat_key=>$cat){
        if($cat_key == "promo_code_amount" && isset($contest_discount[$row['record_date']])){
          $row[$cat_key] = $row[$cat_key] + $contest_discount[$row['record_date']];
        }
        if(isset($row[$cat_key]) && $row[$cat_key] > 0){
          $category_id = $cat['id'];
          $record_date = $row['record_date'];
          $check_exist = $this->get_single_row('finance_id',FINANCE_DASHBOARD,array("category_id"=>$category_id,"record_date"=>$record_date));
          if(!empty($check_exist)){
            $data = array("amount"=>$row[$cat_key],"modified_date"=>$added_date);
            $this->db->where('finance_id', $check_exist['finance_id'])
                ->update(FINANCE_DASHBOARD, $data);
          }else{
            $data = array("category_id"=>$category_id,"amount"=>$row[$cat_key],"description"=>$cat['name'],"record_date"=>$record_date,"added_date"=>$added_date,"modified_date"=>$added_date);
            $this->db->insert(FINANCE_DASHBOARD,$data);
          }
        }
      }
    }

    $this->db->select("DATE_FORMAT(O.date_added,'%Y-%m-%d') as record_date,GROUP_CONCAT(DISTINCT O.reference_id) as reference_ids",FALSE);
    $this->db->from(ORDER." AS O");
    $this->db->where("O.status","1");
    $this->db->where("DATE_FORMAT(O.date_added,'%Y-%m-%d') >= ",$from_date);
    $this->db->where("DATE_FORMAT(O.date_added,'%Y-%m-%d') <= ",$to_date);
    $this->db->where("O.source","3");
    $this->db->where("O.winning_amount > ","0");
    $this->db->group_by("DATE_FORMAT(O.date_added,'%Y-%m-%d')");
    //$this->db->limit(10);
    $sql = $this->db->get();
    $result = $sql->result_array();
    //echo "<pre>";print_r($result);die;
    if(!empty($result)){
      $contest_ids = array_column($result,'reference_ids');
      $contest_ids = implode(",",$contest_ids);
      $contest_ids = explode(",",$contest_ids);
      $this->db_fantasy->select("GROUP_CONCAT(DISTINCT (CASE WHEN user_id=0 THEN C.contest_id ELSE 0 END)) as public_contest,GROUP_CONCAT(DISTINCT (CASE WHEN user_id!=0 THEN C.contest_id ELSE 0 END)) as private_contest",FALSE);
      $this->db_fantasy->from(CONTEST." AS C");
      $this->db_fantasy->where_in("C.contest_id",$contest_ids);
      $sql = $this->db_fantasy->get();
      $contest = $sql->row_array();
      $public_contest = $private_contest = array();
      if(!empty($contest)){
        if(isset($contest['public_contest']) && $contest['public_contest'] != ""){
          $public_contest = explode(",",$contest['public_contest']);
        }
        if(isset($contest['private_contest']) && $contest['private_contest'] != ""){
          $private_contest = explode(",",$contest['private_contest']);
        }
      }
      //echo "<pre>";print_r($result);
      //echo "<pre>";print_r($private_contest);die;
      foreach($result as $row){
        $record_date = $row['record_date'];
        $ref_ids = explode(",",$row['reference_ids']);
        $public_ids = array_intersect($public_contest,$ref_ids);
        $private_ids = array_intersect($private_contest,$ref_ids);
        if(!empty($public_ids)){
          $this->db->select("IFNULL(SUM(CASE WHEN O.source=3 THEN O.winning_amount ELSE 0 END),0) as winning,IFNULL(SUM(CASE WHEN O.source=1 THEN O.winning_amount ELSE 0 END),0) as winning_entry,IFNULL(SUM(CASE WHEN O.source=1 THEN O.real_amount ELSE 0 END),0) as real_entry",FALSE);
          $this->db->from(ORDER." AS O");
          $this->db->where("O.status","1");
          $this->db->where_in("O.source",array("1","3"));
          $this->db->where_in("O.reference_id",$public_ids);
          $sql = $this->db->get();
          $public_amount = $sql->row_array();
          if(!empty($public_amount)){
            $rake = $public_amount['winning_entry'] + $public_amount['real_entry'] - $public_amount['winning'];
            $check_exist = $this->get_single_row('finance_id',FINANCE_DASHBOARD,array("category_id"=>'9',"record_date"=>$record_date));
            if(!empty($check_exist)){
              $data = array("amount"=>$rake,"modified_date"=>$added_date);
              $this->db->where('finance_id', $check_exist['finance_id'])
                  ->update(FINANCE_DASHBOARD, $data);
            }else{
              $data = array("category_id"=>'9',"amount"=>$rake,"description"=>"Platform Fee (Site Rake)","record_date"=>$record_date,"added_date"=>$added_date,"modified_date"=>$added_date);
              $this->db->insert(FINANCE_DASHBOARD,$data);
            }
          }
        }
        if(!empty($private_ids)){
          $this->db->select("IFNULL(SUM(CASE WHEN O.source=3 THEN O.winning_amount ELSE 0 END),0) as winning,IFNULL(SUM(CASE WHEN O.source=1 THEN O.winning_amount ELSE 0 END),0) as winning_entry,IFNULL(SUM(CASE WHEN O.source=1 THEN O.real_amount ELSE 0 END),0) as real_entry",FALSE);
          $this->db->from(ORDER." AS O");
          $this->db->where("O.status","1");
          $this->db->where_in("O.source",array("1","3"));
          $this->db->where_in("O.reference_id",$private_ids);
          $sql = $this->db->get();
          $private_amount = $sql->row_array();
          if(!empty($private_amount)){
            $rake = $private_amount['winning_entry'] + $private_amount['real_entry'] - $private_amount['winning'];
            $check_exist = $this->get_single_row('finance_id',FINANCE_DASHBOARD,array("category_id"=>'12',"record_date"=>$record_date));
            if(!empty($check_exist)){
              $data = array("amount"=>$rake,"modified_date"=>$added_date);
              $this->db->where('finance_id', $check_exist['finance_id'])
                  ->update(FINANCE_DASHBOARD, $data);
            }else{
              $data = array("category_id"=>'12',"amount"=>$rake,"description"=>"Private Contest (Site Rake)","record_date"=>$record_date,"added_date"=>$added_date,"modified_date"=>$added_date);
              $this->db->insert(FINANCE_DASHBOARD,$data);
            }
          }
        }
      }
    }
    return true;
  }
}