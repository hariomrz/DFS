<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once 'Cron_model.php';
class Communication_dashboard_model extends Cron_model {
    
    public function __construct() 
    {
       	parent::__construct();
       
    }

    public function update_cd_balance($post)
    {
        $is_update = false;
        if(!empty($post['email_count']))
        {
            $is_update = true;
            $this->db_user->set('email_balance', 'email_balance-'.$post['email_count'], FALSE);
        }

        if(!empty($post['sms_count']))
        {
            $is_update = true;
            $this->db_user->set('sms_balance', 'sms_balance-'.$post['sms_count'], FALSE);
        }

        if(!empty($post['notification_count']))
        {
            $is_update = true;
            $this->db_user->set('notification_balance', 'notification_balance-'.$post['notification_count'], FALSE);
        }

        if($is_update)
        {
            $this->db_user->update(CD_BALANCE);
        }

        return true;
    }

    function get_short_url($long_url)
    {

        $this->load->library('bitly');
        $params = array();
        $params['access_token'] = BITLY_ACCESS_TOKEN;
        $params['longUrl'] = $long_url;
        $params['domain'] = 'bit.ly';
        $results = $this->bitly->bitly_get('shorten', $params);

        if(isset($results['data']) && isset($results['data']['url']))
        {
         return $results['data']['url'];
        }
        else
        {
            return $long_url;
        }
    }

    function get_sms_history_record($sms_info)
    {
        $this->db = $this->db_user;
        $history = $this->get_single_row('sms_count,sms_status',CD_BALANCE_DEDUCT_HISTORY,
            array('recent_communication_id' => $sms_info['recent_communication_id'],
                  'mobile' => $sms_info['number'][0],
                  'sms_status' => 0));

        if(!empty($history))
        {
            return $history['sms_count'];
        }
        else
        {
            return 0;
        }

    }

    function update_sms_history_status($value,$status=1)
    {
        $this->db_user->where('recent_communication_id', $value['recent_communication_id']);
        $this->db_user->where('mobile', $value['number'][0]);
        $this->db_user->set('sms_status', $status);
        $this->db_user->update(CD_BALANCE_DEDUCT_HISTORY);
    }

    function refund_sms_balance($sms_count)
    {
        $this->db_user->set('sms_balance', 'sms_balance+'.$sms_count, FALSE);
        $this->db_user->update(CD_BALANCE);
        return true;
    }

    function get_notification_description($notification_type)
    {
        $message = $this->db_user->select('message')
        ->from(NOTIFICATION_DESCRIPTION)
        ->where('notification_type',$notification_type)
        ->get()
        ->row_array();

        return $message;
    }

     //------------

     public function get_user_base_list(){
         $lists = $this->db_user->where('status',1)
        ->get(CD_USER_BASED_LIST)->result_array();
        return $lists;

     }

     private function get_referral_user($lists){
        if($lists['referral']['status']==1){
                // $list = json_decode($list['referral'],true)[0];
        $result = $this->db_user->select('UAH.user_id')
        ->from(USER_AFFILIATE_HISTORY.' AS UAH')
        ->join(USER.' AS U','U.user_id = UAH.user_id and U.is_systemuser=0','INNER')
        ->where_in('UAH.affiliate_type',[1,19,20,21])
        ->where('UAH.status',1)
        ->having('count(UAH.user_id) >=',$lists['referral']['min_value'])
        ->having('count(UAH.user_id) <=',$lists['referral']['max_value']);
        $resutl = $this->db_user->group_by('UAH.user_id');
                // ->get()->num_rows();
        $result = $this->db_user->get()->result_array();
        // echo $this->db_user->last_query();exit;
        $result = array_column($result, 'user_id');
        }
        else{
            $result = array();
        }
        return $result;
    }


    private function get_user_data($lists){
            // $this->db = $this->db_user;

        if($lists['age_group']['status']==1 || $lists['profile_status']['status']==1 || $lists['location']['status']==1 || $lists['gender']['status']==1){
            $current_date = format_date('today','Y-m-d');
            $current_year = format_date('today','Y');
            $result= $this->db_user->select('U.user_id')
            ->from(USER.' U')
            ->where('U.status',1)
            ->where('U.is_systemuser',0);
                //age group condition
            if(isset($lists['age_group']) && $lists['age_group']['status']==1){
                $age_cond = $current_year." -
                YEAR(dob) -
                IF(STR_TO_DATE(CONCAT(YEAR(".$current_date."), '-', MONTH(dob), '-', DAY(dob)) ,'%Y-%c-%e') > ".$current_date.", 1, 0)";
                $this->db_user->where($age_cond.'>=',$lists['age_group']['min_value']);
                $this->db_user->where($age_cond.'<=',$lists['age_group']['max_value']);
            }
            //profile status
            if(isset($lists['profile_status']) && $lists['profile_status']['status']==1){

                if($lists['profile_status']['verified']==1 && $lists['profile_status']['not_verified']==0){
                    $varification_criteria = array("pan_verified"=>1,"phone_verfied"=>1,"email_verified"=>1,"is_bank_verified"=>1);
                    $this->db_user->where($varification_criteria);
                }
                elseif ($lists['profile_status']['not_verified']==1 && $lists['profile_status']['verified']==0) {
                    $this->db_user->where("(pan_verified = '0' OR phone_verfied = '0' OR email_verified = '0' OR is_bank_verified = '0')",NULL,FALSE);
                }
                    // }
            }
                //location filter
            if(isset($lists['location']) && $lists['location']['status']==1){
                $location_arr = array_column($lists['location']['location'],'name');
                        // print_r($location_arr);exit;
                $this->db_user->where_in('city',$location_arr);
                    // }
            }
                //gender filter
            if(isset($lists['gender']) &&  $lists['gender']['status']==1){
                $gender_arr = array_column($lists['gender']['gender'], 'name');
                $this->db_user->where_in('gender',$gender_arr);
            }
            $result = $this->db_user->get()->result_array();
            $result = array_column($result, 'user_id');
        }
        else{
            $result = array();
        }
        return $result;
    }

    private function get_money_won($lists){
            // foreach($lists as $key=>$value){ $lists['money_deposit']['status']==1 && $lists['money_won']['status']==1
                // print_r($value);exit;
        if(isset($lists['money_won']) && $lists['money_won']['status']==1){
            $result = $this->db_user->select('O.user_id')
            ->from(ORDER.' AS O')
            ->join(USER.' AS U','O.user_id=U.user_id and U.is_systemuser=0','INNER')
            ->where('U.status',1)
            ->where('O.status',1)
            ->where('O.source',3);
                    // $list = json_decode($value['money_won'],TRUE)[0];
            $this->db_user->having('sum(O.winning_amount) >=',$lists['money_won']['min_value'])
            ->having('sum(O.winning_amount) <=',$lists['money_won']['max_value']);
            $this->db_user->group_by('O.user_id')
            ->order_by("O.user_id","ASC");
            $result = $this->db_user->get()->result_array();
            $result = array_column($result, 'user_id');
        }
        else{
            $result = array();
        }
            // }
                // echo $this->db_user->last_query();exit;
        return $result;
    }
    private function get_money_deposit($lists){
            // foreach($lists as $key=>$value){
            //    $money_deposit_data[$key]['user_base_list_id']= $value['user_base_list_id'];

                // //MONEY DEPOSIT fileter
        // print_r($lists['money_deposited']['status']);exit;
        if(isset($lists['money_deposit']) && $lists['money_deposit']['status']==1){
            $result = $this->db_user->select('O.user_id')
            ->from(ORDER.' AS O')
            ->join(USER.' AS U','O.user_id=U.user_id and U.is_systemuser=0','INNER')
            ->where('U.status',1)
            ->where('O.status',1)
            ->where('O.source',7);
                // $list = json_decode($value['money_deposit'],TRUE)[0];
            $this->db_user->having('sum(O.real_amount) >=',$lists['money_deposit']['min_value'])
            ->having('sum(O.real_amount) <=',$lists['money_deposit']['max_value']);
            $this->db_user->group_by('O.user_id');
            $result = $this->db_user->get()->result_array();
            // echo $this->db_user->last_query();exit;
            $result = array_column($result, 'user_id');
        }
        else{
            $result = array();
        }
            // echo $this->db_user->last_query();exit;
            // }
        $money_won_data = $this->get_money_won($lists);
            // print_r($money_won_data);exit;
        $common_user_id= array();
            // foreach($money_won_data as $key=>$value){
        if(!empty($money_won_data) && !empty($result)){
            $common_user_id = array_intersect($money_won_data, $result);
        }
        elseif(!empty($result) && empty($money_won_data)){
            if($lists['money_won']['status']==0){
            $common_user_id =$result;
            }
            else{
            $common_user_id = array_intersect($money_won_data, $result);
            }
        }
        elseif(empty($result) && !empty($money_won_data)){
            if($lists['money_deposit']['status']==0){
            $common_user_id =$money_won_data;
            }
            else{
            $common_user_id = array_intersect($money_won_data, $result);
            }
        }
            // }
        return $common_user_id;

    }
    private function get_point_redeem($lists){
                // print_r($value);exit;
                // $coin_redeem_data[$key]['user_base_list_id']= $value['user_base_list_id'];
        if(isset($lists['coin_redeem']) && $lists['coin_redeem']['status']==1){
            $result= $this->db_user->select('O.user_id')
            ->from(ORDER.' AS O')
            ->join(USER.' AS U','O.user_id=U.user_id and U.is_systemuser=0','INNER')
            ->where('U.status',1)
            ->where('O.status',1)
            ->where('O.source_id',146);
                    // $list = json_decode($list['coin_redeem'],TRUE)[0];
            $this->db_user->having('sum(O.winning_amount) >=',$lists['coin_redeem']['min_value'])
            ->having('sum(O.winning_amount) <=',$lists['coin_redeem']['max_value']);
            $this->db_user->group_by('O.user_id')
            ->order_by("O.user_id","ASC");
            $result = $this->db_user->get()->result_array();
            // echo $this->db_user->last_query();exit;
            $result = array_column($result, 'user_id');
        }
        else{
            $result = array();
        }
            // echo $this->db_user->last_query();exit;

        return $result;
    }

    private function get_point_earn($lists){
        if(isset($lists['coin_earn']) &&  $lists['coin_earn']['status']==1){
            $result = $this->db_user->select('O.user_id')
            ->from(ORDER.' AS O')
            ->join(USER.' AS U','O.user_id=U.user_id and U.is_systemuser=0','INNER')
            ->where('U.status',1)
            ->where('O.status',1);
                    // $list = json_decode($vlaue['coin_earn'],TRUE)[0];
            $this->db_user->having('sum(O.points) >=',$lists['coin_earn']['min_value'])
            ->having('sum(O.points) <=',$lists['coin_earn']['max_value']);
            $this->db_user->group_by('user_id');
            $result = $this->db_user->get()->result_array();
            $result= array_column($result, 'user_id');
        }
        else{
            $result = array();
        }
            // echo $this->db_user->last_query();exit;
            // }
        $redeem_data = $this->get_point_redeem($lists);
        $common_user_id= array();
            // foreach($redeem_data as $key=>$value){
        if(!empty($redeem_data) && !empty($result)){
            $common_user_id = array_intersect($redeem_data, $result);
        }
        elseif(!empty($result) && empty($redeem_data)){
            if($lists['coin_redeem']['status']==0){
            $common_user_id =$result;
            }
            else{
                $common_user_id = array_intersect($redeem_data, $result);
            }
        }
        elseif(empty($result) && !empty($redeem_data)){
                    // $common_user_id[$key]['user_base_list_id'] = $value['user_base_list_id'];
            if($lists['coin_earn']['status']==0){
            $common_user_id =$redeem_data;
            }
            else{
                $common_user_id = array_intersect($redeem_data, $result);
            }
        }
            // }
        return $common_user_id;
    }
    
        //for admin created contest join lost
    private function get_acc_lost($lists){
        // $this->db = $this->db_fantasy;
                    // print_r($value['admin_created_contest_lost']); exit;
                // $ac_contest_lost_data[$key]['user_base_list_id']= $value['user_base_list_id'];
        if($lists['sport_id']['status']==1 || $lists['admin_created_contest_lost']['status']==1){
            $result =$this->db_fantasy->select("LM.user_id")
            ->from(LINEUP_MASTER_CONTEST . " LMC")
            ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id=LMC.lineup_master_id", "INNER")
            ->join(CONTEST.' C','C.contest_id=LMC.contest_id');

                 //admin created contest join
            if(isset($lists['sport_id']) && $lists['sport_id']['status']==1){
                    // $list = json_decode($value['sport_id'],TRUE)[0];
                $result = array_column($lists['sport_id']['sport_preference'],'id');
                $this->db_fantasy->where_in('C.sports_id',$sports_id);    
            }
             //admin created contest won
            if(isset($lists['admin_created_contest_lost']) &&  $lists['admin_created_contest_lost']['status']==1){
                    // $list = json_decode($value['admin_created_contest_lost'],TRUE)[0];
                $this->db_fantasy->where('C.contest_access_type',0)
                ->where('LMC.is_winner',0)
                ->having('count(LMC.is_winner) >=',$lists['admin_created_contest_lost']['min_value'])
                ->having('count(LMC.is_winner) <=',$lists['admin_created_contest_lost']['max_value']);
            }

            $this->db_fantasy->group_by('LM.user_id')
            ->order_by("LM.user_id","ASC");
            $result = $this->db_fantasy->get()->result_array();
                // $ac_contest_lost_data[$key]['user_id'] = $result;
            $result = array_column($result, 'user_id');
        }else{
            $result = array();
        }
        return $result;
    }


        //for admin created contest join and won
    private function get_acc_join_won($lists){
        if($lists['sport_id']['status']==1 || $lists['admin_created_contest_join']['status']==1 || $lists['admin_created_contest_won']['status']==1){
            $result = $this->db_fantasy->select("LM.user_id")
            ->from(LINEUP_MASTER_CONTEST . " LMC")
            ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id=LMC.lineup_master_id", "INNER")
            ->join(CONTEST.' C','C.contest_id=LMC.contest_id');


            if(isset($lists['sport_id']) && $lists['sport_id']['status']==1){
                    // $list = json_decode($value['sport_id'],TRUE)[0];
                $sports_id = array_column($lists['sport_id']['sport_preference'],'id');
                $this->db_fantasy->where_in('C.sports_id',$sports_id);    
            }

            if($lists['admin_created_contest_join']['status']==1 || $lists['admin_created_contest_won']['status']==1){
                $this->db_fantasy->where('C.contest_access_type',0);
            }
                //admin created contest join
            if(isset($lists['admin_created_contest_join']) && $lists['admin_created_contest_join']['status']==1){
                $this->db_fantasy->having('count(LM.user_id) >=',$lists['admin_created_contest_join']['min_value'])
                ->having('count(LM.user_id) <=',$lists['admin_created_contest_join']['max_value']);
            }

                //admin created contest won
                // print_r($lists['admin_created_contest_won']['min_value']);exit;
            if(isset($lists['admin_created_contest_won']) && $lists['admin_created_contest_won']['status']==1){
                    // $list = json_decode($lists['admin_created_contest_won'],TRUE)[0];
                $this->db_fantasy->where('LMC.is_winner',1)
                ->having('count(LMC.is_winner) >=',$lists['admin_created_contest_won']['min_value'])
                ->having('count(LMC.is_winner) <=',$lists['admin_created_contest_won']['max_value']);
            }

            $this->db_fantasy->group_by('LM.user_id')
            ->order_by("LM.user_id","ASC");
            $result = $this->db_fantasy->get()->result_array();
                // echo $this->db_fantasy->last_query();exit;
            $result = array_column($result, 'user_id');
                // echo $this->db_fantasy->last_query();exit;
        }
        else{
            $result = array();
        }


        $contest_lost = $this->get_acc_lost($lists);
        $common_user_id= array();

            // foreach($contest_lost as $key=>$value){
        if(!empty($contest_lost) && !empty($result)){
            $common_user_id = array_intersect($contest_lost, $result);
        }
        elseif(!empty($result) && empty($contest_lost)){
            if($lists['admin_created_contest_lost']['status']==0){
            $common_user_id =$result;
            }
            else{
                $common_user_id = array_intersect($contest_lost, $result);
            }
        }
        elseif(empty($result) && !empty($contest_lost)){
                    // $common_user_id[$key]['user_base_list_id'] = $value['user_base_list_id'];
            if($lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0){
            $common_user_id =$contest_lost;
            }
            else{
                $common_user_id = array_intersect($contest_lost, $result);
            }
        }
            // }
                // print_r($common_user_id);exit;
        return $common_user_id;

    }

    //for admin PRIVATE contest join lost
    private function get_private_contest_lost($lists){

        if($lists['sport_id']['status']==1 || $lists['private_contest_lost']['status']==1){
            // $this->db = $this->db_fantasy;
            $result = $this->db_fantasy->select("LM.user_id")
            ->from(LINEUP_MASTER_CONTEST . " LMC")
            ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id=LMC.lineup_master_id", "INNER")
            ->join(CONTEST.' C','C.contest_id=LMC.contest_id');


                 //admin created contest join
            if(isset($lists['sport_id']) && $lists['sport_id']['status']==1){
                    // $list = json_decode($value['sport_id'],TRUE)[0];
                $sports_id = array_column($lists['sport_id']['sport_preference'],'id');
                $this->db_fantasy->where_in('C.sports_id',$sports_id);    
            }
             //admin created contest won
            if(isset($lists['private_contest_lost']) && $lists['private_contest_lost']['status']==1){
                    // $list = json_decode($value['private_contest_lost'],TRUE)[0];
                $this->db_fantasy->where('LMC.is_winner',0)
                ->where('C.contest_access_type',1)
                ->having('count(LMC.is_winner) >=',$lists['private_contest_lost']['min_value'])
                ->having('count(LMC.is_winner) <=',$lists['private_contest_lost']['max_value']);
            }

            $this->db_fantasy->group_by('LM.user_id')
            ->order_by("LM.user_id","ASC");
            $result = $this->db_fantasy->get()->result_array();
            $result = array_column($result, 'user_id');
                // echo $this->db_fantasy->last_query();exit;
        }
        else{
            $result = array();
        }

        // print_r($private_contest_lost_data);exit;
        return $result;
    }

    //for admin private_contest join and won
    private function get_private_contest_join_won($lists){
                    // print_r($value);exit;
                // $private_contest_data[$key]['user_base_list_id']= $value['user_base_list_id'];
        if($lists['sport_id']['status']==1 || $lists['private_contest_join']['status']==1 || $lists['private_contest_won']['status']==1){
            $result = $this->db_fantasy->select("LM.user_id")
            ->from(LINEUP_MASTER_CONTEST . " LMC")
            ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id=LMC.lineup_master_id", "INNER")
            ->join(CONTEST.' C','C.contest_id=LMC.contest_id',"INNER");

            if(isset($lists['sport_id']) && $lists['sport_id']['status']==1){
                    // $list = json_decode($value['sport_id'],TRUE)[0];
                $sports_id = array_column($lists['sport_id']['sport_preference'],'id');
                $this->db_fantasy->where_in('C.sports_id',$sports_id);    
            }
            if($lists['private_contest_join']['status']==1 || $lists['private_contest_won']['status']==1){
                $this->db_fantasy->where('C.contest_access_type',1);
            }
                //admin created contest join
            if(isset($lists['private_contest_join']) && $lists['private_contest_join']['status']==1){
                $this->db_fantasy->having('count(LM.user_id) >=',$lists['private_contest_join']['min_value'])
                ->having('count(LM.user_id) <=',$lists['private_contest_join']['max_value']);
            }

                //admin created contest won
            if(isset($lists['private_contest_won']) &&  $lists['private_contest_won']['status']==1){
                $this->db_fantasy->where('LMC.is_winner',1)
                ->having('count(LMC.is_winner) >=',$lists['private_contest_won']['min_value'])
                ->having('count(LMC.is_winner) <=',$lists['private_contest_won']['max_value']);
            }

            $this->db_fantasy->group_by('LM.user_id')
            ->order_by("LM.user_id","ASC");
            $result = $this->db_fantasy->get()->result_array();
            $result = array_column($result, 'user_id');
                // echo $this->db_fantasy->last_query();exit;
        }
        else{
            $result = array();
        }
        $private_contest_lost = $this->get_private_contest_lost($lists);
        $common_user_id= array();
        if(!empty($private_contest_lost) && !empty($result)){
                    // $common_user_id[$key]['user_base_list_id'] = $value['user_base_list_id'];
            $common_user_id = array_intersect($private_contest_lost, $result);
        }
        elseif(!empty($result) && empty($private_contest_lost)){
                    // $common_user_id[$key]['user_base_list_id'] = $value['user_base_list_id'];
            if($lists['private_contest_lost']['status']==0){
            $common_user_id =$result;
            }
            else{
            $common_user_id = array_intersect($private_contest_lost, $result);
            }
        }
        elseif(empty($result) && !empty($private_contest_lost)){
                    // $common_user_id[$key]['user_base_list_id'] = $value['user_base_list_id'];
            if($lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0){
            $common_user_id =$private_contest_lost;
            }
            else{
                $common_user_id = array_intersect($private_contest_lost, $result);
            }
        }
                // print_r($common_user_id);exit;
        return $common_user_id;
    }

    public function get_user_base_count($lists){
        $referral_user_id = $this->get_referral_user($lists);
        $user_data = $this->get_user_data($lists);
        $money_deposit_user_id = $this->get_money_deposit($lists);
        $coin_earn_user_id = $this->get_point_earn($lists);
        $ac_contest_data = $this->get_acc_join_won($lists);//checked with user : 21,31 on 192.168.0.202
        $private_contest_data = $this->get_private_contest_join_won($lists);// checked on user 14
        // print_r($user_data);exit;

                // print_r($ac_contest_data);exit;
                $coin_contest=array();
                // foreach($coin_earn_user_id as $key=>$value){
                if(!empty($coin_earn_user_id) && !empty($ac_contest_data) && !empty($private_contest_data)){
                    $coin_contest = array_intersect($coin_earn_user_id, $ac_contest_data,$private_contest_data);
                }
                elseif(empty($coin_earn_user_id) && !empty($ac_contest_data) && !empty($private_contest_data)){
                    if($lists['coin_earn']['status']==0){
                    $coin_contest = array_intersect($ac_contest_data,$private_contest_data);
                    }else{
                    $coin_contest = array_intersect($ac_contest_data,$private_contest_data,$coin_earn_user_id);
                    }
                }
                elseif(!empty($coin_earn_user_id) && empty($ac_contest_data) && !empty($private_contest_data)){
                    if($lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0 && $lists['admin_created_contest_lost']['status']==0){
                    $coin_contest = array_intersect($coin_earn_user_id,$private_contest_data);
                    }
                    else{
                    $coin_contest = array_intersect($ac_contest_data,$private_contest_data,$coin_earn_user_id);
                    }
                }
                elseif(!empty($coin_earn_user_id) && !empty($ac_contest_data) && empty($private_contest_data)){
                    if($lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0 && $lists['private_contest_lost']['status']==0){
                    $coin_contest = array_intersect($coin_earn_user_id,$ac_contest_data);
                    }
                    else{
                    $coin_contest = array_intersect($ac_contest_data,$private_contest_data,$coin_earn_user_id);
                    }
                }
                elseif(empty($coin_earn_user_id) && empty($ac_contest_data) && !empty($private_contest_data)){
                    if($lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0 && $lists['admin_created_contest_lost']['status']==0 && $lists['coin_earn']['status']==0){
                    $coin_contest = $private_contest_data;
                    }
                    elseif($lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0 && $lists['admin_created_contest_lost']['status']==0 && $lists['coin_earn']['status']==1){
                        $coin_contest = array_intersect($private_contest_data,$coin_earn_user_id);
                    }
                    elseif(($lists['admin_created_contest_join']['status']==1 || $lists['admin_created_contest_won']['status']==1 || $lists['admin_created_contest_lost']['status']==1) && $lists['coin_earn']['status']==0){
                        $coin_contest = array_intersect($ac_contest_data,$private_contest_data);
                    }
                    else{
                    $coin_contest = array_intersect($ac_contest_data,$private_contest_data,$coin_earn_user_id);
                    }
                }
                elseif(!empty($coin_earn_user_id) && empty($ac_contest_data) && empty($private_contest_data)){
                    if($lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0 && $lists['admin_created_contest_lost']['status']==0 && $lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0 && $lists['private_contest_lost']['status']==0){
                    $coin_contest = $coin_earn_user_id;
                    }
                    elseif($lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0 && $lists['admin_created_contest_lost']['status']==0 && ($lists['private_contest_join']['status']==1 || $lists['private_contest_won']['status']==1 || $lists['private_contest_lost']['status']==1)){
                        $coin_contest = array_intersect($private_contest_data,$coin_earn_user_id);
                    }
                    elseif(($lists['admin_created_contest_join']['status']==1 || $lists['admin_created_contest_won']['status']==1 || $lists['admin_created_contest_lost']['status']==1) && ($lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0 && $lists['private_contest_lost']['status']==0)){
                        $coin_contest = array_intersect($ac_contest_data,$coin_earn_user_id);
                    }
                    else{
                    $coin_contest = array_intersect($ac_contest_data,$private_contest_data,$coin_earn_user_id);
                    }
                }
                elseif(empty($coin_earn_user_id) && !empty($ac_contest_data) && empty($private_contest_data)){
                    if($lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0 && $lists['private_contest_lost']['status']==0 && $lists['coin_earn']['status']==0){
                    $coin_contest = $ac_contest_data;
                    }
                    elseif($lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0 && $lists['private_contest_lost']['status']==0 && $lists['coin_earn']['status']==1){
                        $coin_contest = array_intersect($ac_contest_data,$coin_earn_user_id);
                    }
                    elseif(($lists['private_contest_join']['status']==1 || $lists['private_contest_won']['status']==1 || $lists['private_contest_lost']['status']==1) && $lists['coin_earn']['status']==0){
                        $coin_contest = array_intersect($ac_contest_data,$private_contest_data);
                    }
                    else{
                    $coin_contest = array_intersect($ac_contest_data,$private_contest_data,$coin_earn_user_id);
                    }
                    $coin_contest = $ac_contest_data;
                }
                // }
                // print_r($coin_contest);exit;  //tested with user id 21

                $user_referral_money=array();
                // foreach($user_data as $key=>$value){
                if(!empty($user_data) && !empty($referral_user_id) && !empty($money_deposit_user_id)){
                    $user_referral_money = array_intersect($user_data, $referral_user_id,$money_deposit_user_id);
                }
                elseif(empty($user_data) && !empty($referral_user_id) && !empty($money_deposit_user_id)){
                    if($lists['age_group']['status']==0 && $lists['profile_status']['status']==0 && $lists['location']['status']==0 && $lists['gender']['status']==0){
                        $user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id);
                        }
                        else{
                        $user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id,$user_data);    
                        }
                }
                elseif(!empty($user_data) && empty($referral_user_id) && !empty($money_deposit_user_id)){
                    if($lists['referral']['status']==0){
                    $user_referral_money = array_intersect($user_data,$money_deposit_user_id);
                    }
                    else{
                        $user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id,$user_data);    
                    }
                }
                elseif(!empty($user_data) && !empty($referral_user_id) && empty($money_deposit_user_id)){
                    if($lists['money_deposit']['status']==0 && $lists['money_won']['status']==0){
                    $user_referral_money = array_intersect($user_data,$referral_user_id);
                    }
                    else{
                    $user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id,$user_data);    
                    }
                }
                elseif(empty($user_data) && empty($referral_user_id) && !empty($money_deposit_user_id)){
                    if($lists['age_group']['status']==0 && $lists['profile_status']['status']==0 && $lists['location']['status']==0 && $lists['gender']['status']==0 && $lists['referral']['status']==0){
                        $user_referral_money = $money_deposit_user_id;
                        }
                        elseif($lists['age_group']['status']==0 && $lists['profile_status']['status']==0 && $lists['location']['status']==0 && $lists['gender']['status']==0 && $lists['referral']['status']==1){
                            $user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id);       
                        }
                        elseif(($lists['age_group']['status']==1 || $lists['profile_status']['status']==1 || $lists['location']['status']==1 || $lists['gender']['status']==1) && $lists['referral']['status']==0){
                            $user_referral_money = array_intersect($user_data,$money_deposit_user_id);      
                        }
                        else{
                        $user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id,$user_data);    
                        }
                }
                elseif(!empty($user_data) && empty($referral_user_id) && empty($money_deposit_user_id)){
                    if($lists['money_deposit']['status']==0 && $lists['money_won']['status']==0 && $lists['referral']['status']==0){
                    $user_referral_money = $user_data;
                    }
                    elseif($lists['money_deposit']['status']==0 && $lists['money_won']['status']==0 && $lists['referral']['status']==1){
                            $user_referral_money = array_intersect($referral_user_id,$user_data);       
                        }
                        elseif(($lists['money_deposit']['status']==1 || $lists['money_won']['status']==1) && $lists['referral']['status']==0){
                            $user_referral_money = array_intersect($user_data,$money_deposit_user_id);      
                        }
                    else{
                    $user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id,$user_data);    
                    }
                }
                elseif(empty($user_data) && !empty($referral_user_id) && empty($money_deposit_user_id)){
                    if($lists['money_deposit']['status']==0 && $lists['money_won']['status']==0 && $lists['age_group']['status']==0 && $lists['profile_status']['status']==0 && $lists['location']['status']==0 && $lists['gender']['status']==0){
                    $user_referral_money = $referral_user_id;
                    }
                    elseif(($lists['money_deposit']['status']==0 && $lists['money_won']['status']==0) && ($lists['age_group']['status']==1 || $lists['profile_status']['status']==1 || $lists['location']['status']==1 || $lists['gender']['status']==1)){
                            $user_referral_money = array_intersect($referral_user_id,$user_data);       
                        }
                        elseif(($lists['money_deposit']['status']==1 || $lists['money_won']['status']==1) && ($lists['age_group']['status']==0 && $lists['profile_status']['status']==0 && $lists['location']['status']==0 && $lists['gender']['status']==0)){
                            $user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id);       
                        }
                    else{
                    $user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id,$user_data);    
                    }
                }
                // }
                // print_r($user_referral_money);exit; //tested with user id 39

                $final_user_list = array();
                if(!empty($coin_contest) && !empty($user_referral_money)){
                    $final_user_list = array_intersect($user_referral_money, $coin_contest);
                }
                elseif(!empty($user_referral_money) && empty($coin_contest)){
                    if($lists['coin_earn']['status']==0 && $lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0 && $lists['admin_created_contest_lost']['status']==0 && $lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0 && $lists['private_contest_lost']['status']==0){
                    $final_user_list =$user_referral_money;
                    }
                    else{
                        $final_user_list = array_intersect($user_referral_money, $coin_contest);
                    }
                }
                elseif(empty($user_referral_money) && !empty($coin_contest)){
                        if($lists['age_group']['status']==0 && $lists['profile_status']['status']==0 && $lists['location']['status']==0 && $lists['gender']['status']==0 && $lists['referral']['status']==0 && $lists['money_deposit']['status']==0 && $lists['money_won']['status']==0){
                            $final_user_list =$coin_contest;
                        }else{
                            $final_user_list = array_intersect($user_referral_money, $coin_contest);
                        }
                }
                    $user_collection=array();
                if(!empty($final_user_list)){
                    $user_collection['count'] = count($final_user_list);
                    $user_collection['user_ids'] = implode(',',$final_user_list);
                    $user_collection['user_base_list_id'] = $lists['user_base_list_id'];
                    return $user_collection;
                }
                else{
                    $user_collection['user_ids'] = array();
                    $user_collection['count'] = count($final_user_list);
                    $user_collection['user_base_list_id'] = $lists['user_base_list_id'];
                    return $user_collection;
                }

            }

            public function update_user_base_list($list_data){
                if(isset($list_data) && !empty($list_data)){
                    $update = $this->db_user->where('user_base_list_id',$list_data['user_base_list_id'])
                    ->where('status',1)
                    ->update(CD_USER_BASED_LIST,$list_data);
                    if($update)
                        return true;
                }
            }

            public function filter_system_users($user_id){
                $user_collection=array();
                if(!empty($user_id['user_ids'])){
                $result = $this->db_user->select('user_id')
                ->from(USER.' AS U')
                ->where('U.is_systemuser',0)
                ->where('U.status',1)
                ->where_in('U.user_id',$user_id['user_ids'])
                ->get()->result_array();
                $result = array_column($result,'user_id');
                $user_collection['count'] = count($result);
                $user_collection['user_ids'] = implode(',',$result);
                $user_collection['user_base_list_id'] = $user_id['user_base_list_id'];
                }
                else{
                $result = array();
                $user_collection['count'] = count($result);
                $user_collection['user_ids'] = $result;
                $user_collection['user_base_list_id'] = $user_id['user_base_list_id'];
                }
                return $user_collection;
            }

            function delete_deduct_balance_history($date)
            {
               
                $this->db_user->where('added_date<',$date);
                $this->db_user->delete(CD_BALANCE_DEDUCT_HISTORY);
                return true;
            }

            //***********************************************NOTIFY BY SELECTION DEPENDENCY METHODS******************************************** */

            public function get_users_device_by_ids($user_ids)
        {
            if(empty($user_ids))
            {
                return array();
            }
            $post = $this->input->post();

            $pre_query ="(SELECT user_id,GROUP_CONCAT(device_id) as device_ids,GROUP_CONCAT(device_type) as device_types ,keys_id,device_id,device_type FROM ".$this->db_user->dbprefix(ACTIVE_LOGIN)."  WHERE device_id IS NOT NULL GROUP BY user_id ORDER BY keys_id DESC)";

            $this->db_user->select('U.user_id,U.email,U.phone_no,U.phone_code,U.user_name,AL.device_id,AL.device_type,AL.device_ids,AL.device_types')
                            ->from(USER.' U')
                            ->join($pre_query.' AL','AL.user_id=U.user_id','LEFT');

            if(empty($post['all_user']) || (isset($post['all_user']) && $post['all_user'] == '0'))
            {
                $this->db_user->where_in('U.user_id', $user_ids);
            }
            $sql = $this->db_user->where('U.is_systemuser',0)
                            ->group_by('U.user_id')
                            ->get();

            if(!empty($post['is_debug']) && $post['is_debug'] ==1)
            {
                echo $this->db_user->last_query();die('df');
            }
            $rs = $sql->result_array();
            
            return $rs;
        }
}