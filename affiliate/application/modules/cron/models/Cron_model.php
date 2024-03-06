<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron_model extends MY_Model {
  public function __construct() 
  {
    parent::__construct();
    $this->current_date = format_date('today');
  }

  public function get_commission($campaign_id,$type)
  {
    $campaign_data = $this->get_single_row('commission',CAMPAIGN,['campaign_id'=>$campaign_id]);
    $campaign_data = json_decode($campaign_data['commission'],true);
    // print_r($campaign_data);exit;
    switch($type)
    {
      case 1:
        return array('signup'=>$campaign_data['signup']);
      break;
      case 2:
        return array(
                  'deposit_per'=>$campaign_data['deposit_per'],
                  'deposit_cap'=>$campaign_data['deposit_cap'],
                );
      break;
      case 3:
        return array(
                  'game_per'=>$campaign_data['game_per'],
                  // 'game_cap'=>$campaign_data['game_cap'],
                );
      break;
      default:
      break;
    }
  }

  public function register_event($data=array()){
    if(!$data){return false;}

    //check visit recort and set user id in visit table else add new record
    $campaign_id = $this->get_single_row('campaign_id,expiry_date,status',CAMPAIGN,['campaign_code'=>$data['campaign_code']]);
    $camp_status = $campaign_id['status'];
    $exp_date = $campaign_id['expiry_date'];
    $campaign_id = $campaign_id['campaign_id'];
    $is_visited = $this->get_single_row('visit_id',VISIT,['visit_code'=>$data['visit_code']]);
    if($is_visited['visit_id'])
    {
      $this->db->update(VISIT,['user_id'=>$data['user_id'],"date_modified"   =>$this->current_date],['visit_code'=>$data['visit_code']]);
    }else{
      $visit_data = array(
        "visit_code"      =>$data['visit_code'],
        "campaign_id"     =>$campaign_id,
        "user_id"         =>$data['user_id'] ? $data['user_id'] : 0,
        "date_added"      =>$this->current_date,
        "date_modified"   =>$this->current_date,
      );

      

      $this->db->insert(VISIT,$visit_data);
    }

    $campaign_data = $this->get_commission($campaign_id,1);

    //register user
    $campaign_user_data = $data;
    unset($campaign_user_data['visit_code'],$campaign_user_data['campaign_code']);
    $campaign_user_data['campaign_id'] = $campaign_id;
    $campaign_user_data['date_created'] = $this->current_date;
    
    if($camp_status==1)
      {
        $campaign_user_data['is_expired'] = 0;
      }else{
        $campaign_user_data['is_expired'] = 1;
      }

    $is_user_exist = $this->get_single_row('user_id',CAMPAIGN_USERS,['user_id'=>$data['user_id']]);

    if(!$is_user_exist){
      $register_user = $this->db->insert(CAMPAIGN_USERS,$campaign_user_data);
      $isnert_id = $this->db->insert_id();
      $this->db->update(CAMPAIGN_USERS,[""=>format_date()],["campaign_user_id"=>$insert_id]);
    }else{
      $register_user = $this->db->update(CAMPAIGN_USERS,$campaign_user_data,['campaign_id'=>$campaign_id,'user_id'=>$data['user_id']]);
    }
    
    //update campaign history
    $campaign_his_data = array(
      "campaign_id"     =>$campaign_id,
      "user_id"         =>$data['user_id'],
      "type"            =>1,
      "amount"          =>$campaign_data['signup'],
      "commission"      =>$campaign_data['signup'],
      "entity_id"       =>$data['user_id'],
      "date_created"    =>$this->current_date,
      "date_modified"   =>$this->current_date,
    );

    if(format_date()> $exp_date)
    {
      $campaign_his_data['is_expired'] = 1;
    }

    if($camp_status==1)
    {
      $campaign_his_data['is_expired'] = 0;
    }else{
      $campaign_his_data['is_expired'] = 1;
    }

    $is_his_exist = $this->get_single_row('history_id',CAMPAIGN_HISTORY,['campaign_id'=>$campaign_id,'user_id'=>$data['user_id'],"type"=>"1"]);
    if(empty($is_his_exist)){
      $this->db->insert(CAMPAIGN_HISTORY,$campaign_his_data);
    }
    else{
      $this->db->update(CAMPAIGN_HISTORY,$campaign_his_data,['history_id'=>$is_his_exist['history_id']]);
    }

    return true;
  }

  public function deposit_event($data=array()){

      //check if data is null
      if(!$data){return false;}

      //check if user does not belong some other campaign 
      $campaign_id = $this->get_single_row('campaign_id,expiry_date,status',CAMPAIGN,['campaign_code'=>$data['campaign_code']]);
      $camp_status = $campaign_id['status'];
      $expiry_date = $campaign_id['expiry_date'];
      $campaign_id = $campaign_id['campaign_id'];
      $is_valid_campaign = $this->db->select('CH.campaign_id,CU.is_expired')
      ->from(CAMPAIGN_HISTORY.' CH')
      ->join(CAMPAIGN_USERS.' CU','CH.campaign_id = CU.campaign_id and CH.user_id = CU.user_id','INNER')
      ->where('CH.type',1)
      ->where('CH.user_id',$data['user_id'])
      ->get()->row_array();
      // $this->get_single_row('campaign_id,is_expired',CAMPAIGN_HISTORY,["user_id"=>$data['user_id'],"campaign_id"=>$campaign_id,"type"=>1]);
      if(!$is_valid_campaign){return FALSE;}
      $campaign_data = $this->get_commission($campaign_id,2); // 2 for deposit event
      
      
      //capping
      if($campaign_data['deposit_per']==0)
      {
        $commission=$campaign_data['deposit_cap'];
      }else{
        $commission = (($campaign_data['deposit_per']*$data['amount'])/100);
      }

      if($commission > 0 && $commission > $campaign_data['deposit_cap'])
      {
        $commission=$campaign_data['deposit_cap'];
      }

      $campaign_data['ref_id'] = $data['ref_id'];
      $campaign_data['name'] = $data['name'];

      //update campaign history
      $campaign_his_data = array(
        "campaign_id"     =>$campaign_id,
        "user_id"         =>$data['user_id'],
        "type"            =>2,
        "amount"          =>$data['amount'],
        "commission"      =>$commission,
        "entity_id"       =>$data['entity_id'],
        "date_created"    =>$this->current_date,
        "date_modified"   =>$this->current_date,
        "event_data"      =>json_encode($campaign_data),
        "is_expired"      =>$is_valid_campaign['is_expired'],
      );

      if(format_date()> $expiry_date)
      {
        $campaign_his_data['is_expired'] = 1;
      }

      if($camp_status==1 && $is_valid_campaign['is_expired']==0)
      {
        $campaign_his_data['is_expired'] = 0;
      }else{
        $campaign_his_data['is_expired'] = 1;
      }
      
      $is_his_exist = $this->get_single_row('history_id',CAMPAIGN_HISTORY,['entity_id'=>$data['entity_id'],"type"=>"2"]);
      if(!$is_his_exist){
        $this->db->insert(CAMPAIGN_HISTORY,$campaign_his_data);
      }
      else{
        $this->db->update(CAMPAIGN_HISTORY,$campaign_his_data,['history_id'=>$is_his_exist['history_id']]);
      }
      return true;
  }

  public function game_event($data=array()){
   
    //check if data is null
    if(!$data){return false;}

    //check if user does not belong some other campaign 
    $campaign_id = $this->get_single_row('campaign_id,expiry_date,status',CAMPAIGN,['campaign_code'=>$data['campaign_code']]);
    $camp_status = $campaign_id['status'];
    $expiry_date = $campaign_id['expiry_date'];
      $campaign_id = $campaign_id['campaign_id'];
    $is_valid_campaign = $this->db->select('CH.campaign_id,CU.is_expired')
    ->from(CAMPAIGN_HISTORY.' CH')
    ->join(CAMPAIGN_USERS.' CU','CH.campaign_id = CU.campaign_id and CH.user_id = CU.user_id','INNER')
    ->where('CH.type',1)
    ->where('CH.user_id',$data['user_id'])
    ->get()->row_array();
    //$this->get_single_row('campaign_id,is_expired',CAMPAIGN_HISTORY,["user_id"=>$data['user_id'],"campaign_id"=>$campaign_id,"type"=>1]);
    if(!$is_valid_campaign){return FALSE;}
    


    $campaign_data = $this->get_commission($campaign_id,3); // 2 for deposit event
    $commission = $campaign_data['game_per'];
    
    //capping
    // if($commission > 0 && $commission > $campaign_data['game_cap']){
    //   $commission=$campaign_data['game_cap'];
    // }

    $campaign_data['ref_id'] = $data['ref_id'];
    $campaign_data['name'] = $data['name'];
    $campaign_data['currency_type'] = $data['currency_type'];

    //update campaign history
    $campaign_his_data = array(
      "campaign_id"     =>$campaign_id,
      "user_id"         =>$data['user_id'],
      "type"            =>3,
      "amount"          =>$data['amount'],
      "commission"      =>$commission,
      "entity_id"       =>$data['entity_id'],
      "date_created"    =>$this->current_date,
      "date_modified"   =>$this->current_date,
      "event_data"      =>json_encode($campaign_data),
      "is_expired"      =>$is_valid_campaign['is_expired'],
    );

    if(format_date()> $expiry_date)
    {
      $campaign_his_data['is_expired'] = 1;
    }

    if($camp_status==1 && $is_valid_campaign['is_expired']==0)
    {
      $campaign_his_data['is_expired'] = 0;
    }else{
      $campaign_his_data['is_expired'] = 1;
    }

    // $is_his_exist = $this->get_single_row('history_id',CAMPAIGN_HISTORY,['entity_id'=>$data['entity_id'],"type"=>"3"]);
    $this->db->insert(CAMPAIGN_HISTORY,$campaign_his_data);
    
    // if(!$is_his_exist){}
    // else{$this->db->update(CAMPAIGN_HISTORY,$campaign_his_data,['history_id'=>$is_his_exist['history_id']]);}

    return true;    
  }

  public function add_visit($visit_data)
  {
      $exist = $this->db->select("visit_id")->from(VISIT)->where("visit_code",$visit_data['visit_code'])->get()->row_array();
      $campaign_id = $this->get_single_row('campaign_id,status',CAMPAIGN,['campaign_code'=>$visit_data['campaign_code']]);
      $visit_data['campaign_id'] = $campaign_id['campaign_id'];
      $visit_data['is_expired'] = ($campaign_id['status'] == 1) ? 0: 1;
      unset($visit_data['campaign_code']);
      
      if(empty($exist) && !empty($visit_data['campaign_id']))
      {
        $this->db->insert(VISIT,$visit_data);
        $this->db->insert_id();
      }
      return true;
  }

  public function update_expired_campaign()
  {
    $current_date = format_date();
    $this->db
    ->where("expiry_date <",$current_date)
    ->where("status !=",2)
    ->update(CAMPAIGN,["status"=>4]);

    $this->db
    ->where("expiry_date <",$current_date)
    ->where("status",2)
    ->update(CAMPAIGN,["is_unpublished"=>1]);
    // echo $this->db->last_query();exit;
    return true;
  }
}
