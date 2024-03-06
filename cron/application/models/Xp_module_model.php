<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
require_once 'Cron_model.php';
class Xp_module_model extends Cron_model {
    
    public $db_user ;
    public $db_fantasy ;
    
    public function __construct() 
    {
       	parent::__construct();
		$this->db_user		= $this->load->database('db_user', TRUE);
		$this->db_fantasy	= $this->load->database('db_fantasy', TRUE);
        
    }


    
    function get_signup_users($activity_id)
    {
        $current_date = format_date();
        $past_time = date(DATE_FORMAT,strtotime($current_date.' - '.$this->signup_interval.' hour'));
         $this->db_user->select('U.user_id')
        ->from(USER.' U')
        ->join(XP_USER_HISTORY.' XUH','U.user_id=XUH.user_id AND XUH.activity_id='.$activity_id,'LEFT');
        
        if(LOGIN_FLOW == 0)
        {
            $this->db_user->where('U.phone_verfied',1);
        }
        else{
            $this->db_user->where('U.email_verified',1);
        }

        $result = $this->db_user->where('U.user_name IS NOT NULL')
        ->where('XUH.history_id IS NULL')
        ->where('U.added_date>',$past_time)->get()->result_array();
        //echo $this->db_user->last_query();
        return $result;
    }

    function get_activity_by_id($activity_master_id,$single_record=0)
    {
        $this->db_user->select('XA.activity_id,XA.recurrent_count,XA.xp_point,XAM.activity_type')
        ->from(XP_ACTIVITY_MASTER.' XAM')
        ->join(XP_ACTIVITIES.' XA','XAM.activity_master_id=XA.activity_master_id')
        ->where('XAM.activity_master_id',$activity_master_id)
        ->where('XAM.status',1)
        ->where('XA.is_deleted',0);

        if($single_record)
        {
           $result = $this->db_user->get()->row_array();

        }
        else
        {
            $result = $this->db_user->get()->result_array();
            
        }
       
        return $result;
    }

    function get_xp_users($user_ids,$single_record=0)
    {
         $this->db_user->select('user_id,point,custom_data,level_id')
        ->from(XP_USERS)
        ->where_in('user_id',$user_ids);
        
        if($single_record)
        {
            $result = $this->db_user->get()->row_array();
        }
        else{

            $result = $this->db_user->get()->result_array();
        }
        return $result;

    }

    function get_xp_user_reward($user_ids,$single_record=0)
    {
        $this->db_user->select('XU.user_id,XU.point,XU.custom_data,XU.level_id,XLP.level_number')
        ->from(XP_USERS.' XU')
        ->join(XP_LEVEL_POINTS.' XLP','XU.level_id=XLP.level_pt_id')
        ->where_in('XU.user_id',$user_ids);

        if($single_record)
        {
            $result = $this->db_user->get()->row_array();
        }
        else{

            $result = $this->db_user->get()->result_array();
        }
        return $result;  
    }

    function get_level_list()
    {
        return $result = $this->db_user->select('level_pt_id,level_number,start_point,end_point',FALSE)
		->from(XP_LEVEL_POINTS)
		->where("end_point>",0)->get()->result_array();
    }
    private function get_next_level($points,$level)
    {
        $level_list = $this->get_level_list();
      
        $last_element = end($level_list);

        if($last_element['end_point'] < $points)
        {
            return $last_element;
        }
        foreach($level_list as $level_id => $value)
        {
            if($points >= $value['start_point'] && $points <= $value['end_point'])
            {
                //die('dfdf');
                return $value;
            } 
        }

        return array('level_pt_id' => $level,"level_number" => 1);

    }

    function get_all_level_list()
    {
        $level_cache_key = "xp_levels";
        $level_list = $this->get_cache_data($level_cache_key);
        if(!$level_list)
        {
          $this->load->model('Xp_module_model');
          $level_list = $this->get_level_list();
          if(!empty($level_list))
          {
              $level_list = array_column($level_list,NULL,'level_pt_id');
          }
          $this->set_cache_data($level_cache_key,$level_list,REDIS_2_DAYS);
        }

        return $level_list;
    }

     /**
    * steps - 1 credit points to xp_user and record not exists then insert and update level
    *         2 add history 
    *         3 is level changes then update level and add reward benifit with coins , notification for level up 
    *
   */
  public function credit_xp_points($data_arr)
  {
      $current_date = format_date();
      /**
       * 
       * {"activity_id":"1","point":"50","user_id":"1","level_id":1,"custom_data":"{\"level_ids\":[1]}"}
       * 
       * 
      */
      $xp_user = $this->get_xp_users([$data_arr['user_id']],1);
    
      //prepare xp_user data
      $xp_user_data = array();
      $xp_user_data['user_id'] = $data_arr['user_id'];
      $xp_user_data['point'] = $data_arr['point'];
      $total_points =$xp_user['point']+$data_arr['point'];
      $current_level = $this->get_next_level($total_points,$data_arr['level_id']);

      $xp_user_data['level_id'] = $current_level['level_pt_id']; 
      //echo "<pre>";
      //echo "USER_ID: ".$data_arr['user_id'];
      //echo "POINTS: ".$total_points;
      //echo "LEVELID: ".$xp_user_data['level_id'].'<br>';
      $custom_data = json_decode($xp_user['custom_data'],TRUE);
           
      $level_ids = $custom_data['level_ids'];
      if($xp_user_data['level_id']> 0)
      {
          $level_ids[] =(int)$xp_user_data['level_id'];
      }
      $level_ids = array_unique($level_ids);
      $custom_data['level_ids'] = $level_ids;
      $xp_user_data['custom_data'] = json_encode($custom_data);
      $xp_user_data['update_date'] = $current_date;
      $current_date = format_date();
      $is_insert = 1;
      if(empty($xp_user))
      {
          //prepare for insert
          $xp_user_data['added_date'] = $current_date; 
      }
      else
      {
            $is_insert = 0;
          //update point
      }

      //prepare history data
      $xp_user_history =array();
      $xp_user_history['point'] = $data_arr['point'];
      $xp_user_history['user_id'] = $data_arr['user_id'];
      $xp_user_history['activity_id'] = $data_arr['activity_id'];
      $xp_user_history['added_date'] = $current_date;

      //print_r($xp_user_data);
     // print_r($xp_user_history);
     

		$this->db_user->trans_start();
        if($is_insert){

            $this->db_user->insert(XP_USERS,$xp_user_data);
        }
        else
        {
            $this->update_user_xp_point($data_arr['user_id'],$xp_user_data);
        }

        //delete cache user_xp_
        $this->delete_cache_data('user_xp_'.$data_arr['user_id']);
        $this->delete_cache_data('user_xp_card_'.$data_arr['user_id']);
        $this->db_user->insert(XP_USER_HISTORY,$xp_user_history);

        //check if level updated then add reward histroy with pending status
        if(!isset($xp_user['level_id']) || ((int)$xp_user['level_id'] < (int)$xp_user_data['level_id']))
        {
             //level update notification
             if((int)$xp_user['level_id'] < (int)$xp_user_data['level_id'])
             {
                $notify_data = array();
                $notify_data['notification_type'] = 550; ////550 level_update notification 
                $notify_data['notification_destination'] = 1; //Web,Push,Email
                $notify_data["source_id"] = 0;
                $notify_data["user_id"] = $data_arr['user_id'];
                $notify_data["to"] = '';
                $notify_data["user_name"] = '';
                $notify_data["added_date"] = $current_date;
                $notify_data["modified_date"] = $current_date;
                $notify_data["subject"] = "Level Updated" ;

                $content = array(
                    'level_number' => $current_level['level_number']
                );

                $notify_data["content"] = json_encode($content);
                $this->load->model('notification/Notify_nosql_model');
                $this->Notify_nosql_model->send_notification($notify_data); 
            
             }
            
            //get reward and add history
            $user_reward_history = $this->get_user_reward_history($data_arr['user_id'],$xp_user_data['level_id']);

            if(isset($user_reward_history['reward_id']) && !empty($user_reward_history))
            {
                $new_history = array();
                $new_history['user_id'] =$user_reward_history['user_id'];
                $new_history['reward_id'] =$user_reward_history['reward_id'];
                $new_history['status'] = 0;
                $new_history['added_date'] = format_date();

                $new_history['coins'] = 0;
                if($user_reward_history['is_coin'] == 1)
                {
                    $new_history['coins'] =$user_reward_history['coin_amt'];
                }
                $this->db_user->insert(XP_REWARD_HISTORY,$new_history);
                $this->load->helper('queue');
                $reward_history_id = $this->db_user->insert_id();
                $user_reward_history['reward_history_id'] = $reward_history_id;
                $user_reward_history['level_number'] = $current_level['level_number'];

                add_data_in_queue($user_reward_history,'credit_xp_reward');
            }

        }

		$this->db_user->trans_complete();
		$this->db_user->trans_strict(FALSE);

		if ($this->db_user->trans_status() === FALSE)
		{
		    // generate an error... or use the log_message() function to log your error
			$this->db_user->trans_rollback();
			return false;
		}
		else
		{
			$this->db_user->trans_commit();
			
			//return ;
		}
     
  }

  function get_user_reward_history($user_id,$level_id)
  {
    $result =  $this->db_user->select("$user_id as user_id,XLR.is_coin,coin_amt,XLR.is_cashback,XLR.cashback_amt,XLR.cashback_type,XLR.cashback_amt_cap,XLR.is_contest_discount,XLR.discount_percent,XLR.discount_type,XLR.discount_amt_cap,XLR.reward_id")
    ->from(XP_USERS.' XU')
    ->join(XP_LEVEL_POINTS.' XLP','XU.level_id=XLP.level_pt_id')
    ->join(XP_LEVEL_REWARDS.' XLR','XLP.level_number=XLR.level_number and XLR.is_deleted=0','LEFT')
    ->join(XP_REWARD_HISTORY.' XRH','XRH.reward_id=XLR.reward_id AND XRH.user_id='.$user_id,'LEFT')
    ->where('XU.user_id',$user_id)
    ->where('XRH.reward_history_id IS NULL')
    ->where('XLP.level_pt_id',$level_id)->get()->row_array();

    //echo $this->db_user->last_query();
    return $result;
  }

  function update_user_xp_point($user_id,$data)
    {
        if(empty($data['point'])){
            return false;
        }
        $this->db_user->set('point', 'point + '.(int)$data['point'], FALSE);
        $this->db_user->set('level_id', $data['level_id']);
        $this->db_user->set('custom_data', $data['custom_data']);
        $this->db_user->where('user_id', $user_id);
        $this->db_user->update(XP_USERS);
        return $this->db_user->affected_rows();  
    }

    function check_user_pending_reward_history($reward_history_id)
    {
        return $result =  $this->db_user->select("status")
        ->from(XP_REWARD_HISTORY)
        ->where('status',0)->get()->row_array();
    }

    public function credit_xp_reward($data_arr)
    {
        $current_date = format_date();

        //check history status and process 
        $pending_record = $this->check_user_pending_reward_history($data_arr['reward_history_id']);

        if(empty($pending_record))
        {
            return true;
        }

        //add coins

        if($data_arr['coin_amt'] > 0)
        {
            $deposit_data = array(
                "user_id" => $data_arr['user_id'],
                "amount" => $data_arr['coin_amt'],
                "source" => 450, //Coins credited for level promotion
                "source_id" => 0,
                "plateform" => 1,
                "cash_type" => 2, // for coins 
                "link" => FRONT_APP_PATH . 'my-wallet',
                "custom_data"=> json_encode(array('level_number' => $data_arr['level_number']))
            );
    
            $this->deposit_fund($deposit_data);
        }
/**
 * {"user_id":"1","is_coin":"1","coin_amt":"50","is_cashback":"1","cashback_amt":"10","cashback_type":"0","cashback_amt_cap":"0","is_contest_discount":"0","discount_percent":"0","discount_type":"0","discount_amt_cap":"0","reward_id":"1","reward_history_id":2}
 * **/
        //update xp_user custom data for details
        //prepare custom data reward array
        $reward = array();
        $reward['reward_history_id'] =$data_arr['reward_history_id'];
        if($data_arr['is_cashback'] == 1)
        {
            $reward['cashback_amt'] =$data_arr['cashback_amt'];
            $reward['cashback_type'] =$data_arr['cashback_type'];
            $reward['cashback_amt_cap'] =$data_arr['cashback_amt_cap'];
        }

        if($data_arr['is_contest_discount'] == 1)
        {
            $reward['discount_percent'] =$data_arr['discount_percent'];
            $reward['discount_type'] =$data_arr['discount_type'];
            $reward['discount_amt_cap'] =$data_arr['discount_amt_cap'];
        }

        $xp_user= $this->get_xp_user_reward([$data_arr['user_id']],1);
        $custom_data = json_decode($xp_user['custom_data'],TRUE);
        $custom_data['reward'] = $reward;

        //udpate reward in xp_user
        $this->db_user->where('user_id', $data_arr['user_id']);
        $this->db_user->update(XP_USERS,array('custom_data' => json_encode($custom_data) ));

        //update user reward history
        $this->db_user->where('reward_history_id', $data_arr['reward_history_id']);
        $this->db_user->update(XP_REWARD_HISTORY,array('status' => 1 ));

        $user_detail = $this->db_user->select("user_id,email,user_name")->where("user_id", $data_arr['user_id'])->get(USER)->row_array();
       
    }

    function deposit_fund($input) {
        $this->finance_lang = $this->config->item('finance_lang');
        $order_id = $this->create_order($input);
        return $order_id;
    }

     /**
     * Used to create order
     * @param array $input
     * @return int
     */
    function create_order($input) {
        $today = format_date();
        $orderData = array();
        $orderData["user_id"] = $input['user_id'];
        $orderData["source"] = $input['source'];
        $orderData["source_id"] = $input['source_id'];
        $orderData["type"] = 0;
        $orderData["date_added"] = $today;
        $orderData["modified_date"] = $today;
        $orderData["status"] = 0;
        $orderData["real_amount"] = 0;
        $orderData["bonus_amount"] = 0;
        $orderData["winning_amount"] = 0;
        $orderData["points"] = 0;
        $orderData["reason"] = isset($input['reason']) ? $input['reason'] : '';

        if(!empty($input['custom_data']))
        {
            $orderData["custom_data"] = $input['custom_data'];
        }

        $amount = $input['amount'];

        switch ($input['cash_type']) {
            case 0:
                $orderData["real_amount"] = $amount; // Real Money
                break;
            case 1:
                $orderData["bonus_amount"] = $amount; // Bonus Money 
                break;
            case 2:
                $orderData["points"] = $amount; // Point Balance
                break;
            case 4:
                $orderData["winning_amount"] = $amount; // Winning Balance 
                break;
            default:
                return FALSE;
                break;
        }

        /* Add source for which status will be set to one */
        $status_one_srs = [450];

        if (in_array($orderData["source"], $status_one_srs)) {
            $orderData["status"] = 1;
        } else if ($orderData["source"] == 3) {
            $orderData["real_amount"] = 0;
            $orderData["bonus_amount"] = 0;
            $orderData["winning_amount"] = $amount;
            $orderData["status"] = 1;
        }

        //$this->db->trans_start();
        $orderData['order_unique_id'] = $this->_generate_order_key();
        $this->db_user->insert(ORDER, $orderData);
        $order_id = $this->db_user->insert_id();

        if (!$order_id) {
            return FALSE;
        }else{
            if($orderData["points"] > 0)
            {
                $this->load->helper('queue_helper');
                $coin_data = array(
                    'oprator' => 'add', 
                    'user_id' => $input['user_id'], 
                    'total_coins' => $orderData["points"], 
                    'bonus_date' => format_date("today", "Y-m-d")
                );
                // add_data_in_queue($coin_data, 'user_coins');
            }
        }

        /*$user_balance = $this->get_user_balance($orderData["user_id"]);
        $real_bal = $user_balance['real_amount'] + $orderData["real_amount"];
        $bonus_bal = $user_balance['bonus_amount'] + $orderData["bonus_amount"];
        $winning_bal = $user_balance['winning_amount'] + $orderData["winning_amount"];
        $point_bal = $user_balance['point_balance'] + $orderData["points"];   // update point balance
        */
        // Update User balance for order with completed status .
        if ($orderData["status"] == 1) {
            $this->update_user_balance($orderData["user_id"], $orderData, 'add');
        }
        //$this->db->trans_complete();
        return $order_id;
    }

     /**
     * Used for generate order unique id
     * @return string
     */
    function _generate_order_key() {
        $this->load->helper('security');
        $salt = do_hash(time() . mt_rand());
        $new_key = substr($salt, 0, 20);
        return $new_key;
    }

    /**
     * Used for check order unique id exist or not
     * @param string $key
     * @return int
     */
    private function _order_key_exists($key) {
        $this->db_user->select('order_id');
        $this->db_user->where('order_unique_id', $key);
        $this->db_user->limit(1);
        $query = $this->db_user->get(ORDER);
        $num = $query->num_rows();
        if ($num > 0) {
            return true;
        }
        return false;
    }

    function get_completed_cash_contest($user_ids =array(),$interval_cash_contest=1)
    {
        $current_date = format_date();
        $past_time = date(DATE_FORMAT,strtotime($current_date.' - '.$this->cash_contest_interval.' hour'));
        $this->db_fantasy->select('LM.user_id,COUNT(LMC.contest_id) as contest_count',FALSE)
        ->from(CONTEST.' C')
        ->join(LINEUP_MASTER_CONTEST.' LMC','LMC.contest_id=C.contest_id')
        ->join(LINEUP_MASTER.' LM','LMC.lineup_master_id=LM.lineup_master_id')
        ->where('C.status',3)
        ->where('C.entry_fee>',0)//cach contest
        ->where('C.currency_type',1);// real entry fee

        if($interval_cash_contest)
        {
            $this->db_fantasy->where('C.completed_date>',$past_time);
        }
        
        if(count($user_ids) > 0)
        {
            $this->db_fantasy->where_in('LM.user_id',$user_ids); 

            if($this->xp_point_start_date  != NULL && $this->xp_point_start_date !='')
            {
                $xp_point_start_date = date(DATE_ONLY_FORMAT,strtotime($this->xp_point_start_date));
                $this->db_fantasy->where('C.completed_date>',$xp_point_start_date.' 00:00:00');
            }
        }
        
        $result =  $this->db_fantasy->group_by('LM.user_id')
        ->get()->result_array();
        
       return $result;
    }

    function get_completed_free_contest($user_ids =array(),$interval_free_contest=1)
    {
        $current_date = format_date();
        $past_time = date(DATE_FORMAT,strtotime($current_date.' - '.$this->free_contest_interval.' hour'));
        $this->db_fantasy->select('LM.user_id,COUNT(LMC.contest_id) as contest_count',FALSE)
        ->from(CONTEST.' C')
        ->join(LINEUP_MASTER_CONTEST.' LMC','LMC.contest_id=C.contest_id')
        ->join(LINEUP_MASTER.' LM','LMC.lineup_master_id=LM.lineup_master_id')
        ->where('C.status',3)
        ->where('C.entry_fee',0);// real entry fee

        if($interval_free_contest)
        {
            $this->db_fantasy->where('C.completed_date>',$past_time);
        }
        
        if(count($user_ids) > 0)
        {
            $this->db_fantasy->where_in('LM.user_id',$user_ids); 

            if($this->xp_point_start_date  != NULL && $this->xp_point_start_date !='')
            {
                $xp_point_start_date = date(DATE_ONLY_FORMAT,strtotime($this->xp_point_start_date));
                $this->db_fantasy->where('C.completed_date>',$xp_point_start_date.' 00:00:00');
            }
        }
        
        $result =  $this->db_fantasy->group_by('LM.user_id')
        ->get()->result_array();
        return $result;
    }

    function get_completed_coin_contest($user_ids =array(),$interval_coin_contest=1)
    {
        $current_date = format_date();
        $past_time = date(DATE_FORMAT,strtotime($current_date.' - '.$this->coin_contest_interval.' hour'));
        $this->db_fantasy->select('LM.user_id,COUNT(LMC.contest_id) as contest_count',FALSE)
        ->from(CONTEST.' C')
        ->join(LINEUP_MASTER_CONTEST.' LMC','LMC.contest_id=C.contest_id')
        ->join(LINEUP_MASTER.' LM','LMC.lineup_master_id=LM.lineup_master_id')
        ->where('C.status',3)
        ->where('C.entry_fee>',0)//cach contest
        ->where('C.currency_type',2);// coin entry fee

        if($interval_coin_contest)
        {
            $this->db_fantasy->where('C.completed_date>',$past_time);
        }
        
        if(count($user_ids) > 0)
        {
            $this->db_fantasy->where_in('LM.user_id',$user_ids); 

            if($this->xp_point_start_date  != NULL && $this->xp_point_start_date !='')
            {
                $xp_point_start_date = date(DATE_ONLY_FORMAT,strtotime($this->xp_point_start_date));
                $this->db_fantasy->where('C.completed_date>',$xp_point_start_date.' 00:00:00');
            }
        }
        
        $result =  $this->db_fantasy->group_by('LM.user_id')
        ->get()->result_array();
        
    //     echo $this->db_fantasy->last_query();
    //    die('dfd');
       return $result;
    }

    function user_activity_xp_credit_count($user_ids,$activity_id)
    {
        $result =  $this->db_user->select("user_id,COUNT(activity_id) as xp_credit_count")
        ->from(XP_USER_HISTORY)
        ->where_in('user_id',$user_ids)
        ->where('activity_id',$activity_id)
        ->group_by('user_id')
        ->get()->result_array();
    
        return $result;
    }

    function get_user_who_send_invites($user_ids =array(),$invite_interval=1)
    {
        $current_date = format_date();
        $past_time = date(DATE_FORMAT,strtotime($current_date.' - '.$this->invite_interval.' hour'));
        $this->db_user->select('UAH.user_id,COUNT(UAH.friend_id) as count_value',FALSE)
        ->from(USER_AFFILIATE_HISTORY.' UAH')
        ->where('UAH.affiliate_type',1)// invite
        ->where('UAH.friend_id IS NOT NULL')
        ->where('UAH.user_id>',0);

        if($invite_interval)
        {
            $this->db_user->where('UAH.created_date>',$past_time);
        }
        
        if(count($user_ids) > 0)
        {
            $this->db_user->where_in('UAH.user_id',$user_ids); 

            if($this->xp_point_start_date  != NULL && $this->xp_point_start_date !='')
            {
                $xp_point_start_date = date(DATE_ONLY_FORMAT,strtotime($this->xp_point_start_date));
                $this->db_user->where('UAH.created_date>',$xp_point_start_date.' 00:00:00');
            }
        }
        
        $result =  $this->db_user->group_by('UAH.user_id')
        ->get()->result_array();

        //echo $this->db_user->last_query();die('dfdf');
        return $result;
    }

    public function get_first_deposit_user()
    {
        $current_date = format_date();
        $past_time = date(DATE_FORMAT,strtotime($current_date.' - '.$this->invite_interval.' hour'));
        $result = $this->db_user->select('user_id,1 as count_value',FALSE)
        ->from(ORDER)
        ->where('source',7)
        ->where('real_amount>',0)
        ->where('status',1)
        ->where('date_added>',$past_time)
        ->group_by('user_id')
        ->get()->result_array();
        return $result;
    }

    public function get_post_first_deposit_user($user_ids = array(),$deposit_interval=1)
    {
        $current_date = format_date();
        $past_time = date(DATE_FORMAT,strtotime($current_date.' - '.$this->post_1st_deposit_interval.' hour'));
       $this->db_user->select('user_id,COUNT(order_id) as count_value',FALSE)
        ->from(ORDER)
        ->where('source',7)
        ->where('real_amount>',0)
        ->where('status',1);

        if($deposit_interval)
        {
            $this->db_user->where('date_added>',$past_time);
        
        }
        
        if(count($user_ids) > 0)
        {
            $this->db_user->where_in('user_id',$user_ids); 

            if($this->xp_point_start_date  != NULL && $this->xp_point_start_date !='')
            {
                $xp_point_start_date = date(DATE_ONLY_FORMAT,strtotime($this->xp_point_start_date));
                $this->db_user->where('date_added>',$xp_point_start_date.' 00:00:00');
            }
        }
        
        $result = $this->db_user->group_by('user_id')
        ->get()->result_array();

        // echo "<pre>";
        // echo $this->db_user->last_query();die('dfd');
        return $result;
    }

    public function get_winning_zone_users($user_ids =array(),$interval_winning_zone=1)
    {
        $current_date = format_date();
        $past_time = date(DATE_FORMAT,strtotime($current_date.' - '.$this->winning_zone_interval.' hour'));
        $this->db_fantasy->select('LM.user_id,COUNT(LMC.contest_id) as count_value',FALSE)
        ->from(CONTEST.' C')
        ->join(LINEUP_MASTER_CONTEST.' LMC','LMC.contest_id=C.contest_id')
        ->join(LINEUP_MASTER.' LM','LMC.lineup_master_id=LM.lineup_master_id')
        ->where('C.status',3)
        ->where('LMC.is_winner',1);
        //->where('C.entry_fee>',0)//cach contest
        //->where('C.currency_type',2);// coin entry fee

        if($interval_winning_zone)
        {
            $this->db_fantasy->where('C.completed_date>',$past_time);
        }
        
        if(count($user_ids) > 0)
        {
            $this->db_fantasy->where_in('LM.user_id',$user_ids); 

            
            if($this->xp_point_start_date  != NULL && $this->xp_point_start_date !='')
            {
                $xp_point_start_date = date(DATE_ONLY_FORMAT,strtotime($this->xp_point_start_date));
                $this->db_fantasy->where('C.completed_date>',$xp_point_start_date.' 00:00:00');
            }
        }
        
        $result =  $this->db_fantasy->group_by('LM.user_id')
        ->get()->result_array();
        
       return $result;
    }

    function get_kyc_user($user_ids =array(),$interval_kyc=1)
    {
        $current_date = format_date();
        $past_time = date(DATE_FORMAT,strtotime($current_date.' - '.$this->kyc_interval.' hour'));
        $this->db_user->select('user_id,COUNT(user_id) as count_value',FALSE)
        ->from(USER)
        ->where('phone_verfied',1)
        ->where('email_verified',1);

        $allow_pan_flow = isset($this->app_config['allow_pan_flow'])?$this->app_config['allow_pan_flow']['key_value']:0;

        if($allow_pan_flow == 1)
        {
            $this->db_user->where('pan_verified',1);
        }

        $allow_bank_flow = isset($this->app_config['allow_bank_flow'])?$this->app_config['allow_bank_flow']['key_value']:0;
        if($allow_bank_flow == 1)
        {
            $this->db_user->where('is_bank_verified',1);
        }

        if($interval_kyc)
        {
            $this->db_user->where('kyc_date>',$past_time);
        }

        if(count($user_ids) > 0)
        {
            $this->db_user->where_in('user_id',$user_ids); 

            
            if($this->xp_point_start_date  != NULL && $this->xp_point_start_date !='')
            {
                $xp_point_start_date = date(DATE_ONLY_FORMAT,strtotime($this->xp_point_start_date));
                $this->db_user->where('kyc_date>',$xp_point_start_date.' 00:00:00');
            }
        }

        $result =  $this->db_user->group_by('user_id')
        ->get()->result_array();

        //echo $this->db_user->last_query();die('fdfd');
        
       return $result;
    }


    public function get_deposit_users()
    {
        $current_date = format_date();
        $past_time = date(DATE_FORMAT,strtotime($current_date.' - '.$this->deposit_cashback_interval.' hour'));
        $result = $this->db_user->select('O.user_id,O.real_amount,O.order_id',FALSE)
        ->from(ORDER.' O')
        ->join(XP_USERS.' XU','O.user_id=XU.user_id')
        ->where('O.source',7)
        ->where('O.real_amount>',0)
        ->where('O.status',1)
        ->where('XU.level_id>',0)
        ->where('O.date_added>',$past_time)
        ->get()->result_array();

        //echo $this->db_user->last_query();die('dfd');
        return $result;
    }

    function get_user_rewards($user_ids,$single_record=1)
    {
        $this->db_user->select('XU.user_id,XU.point,XU.custom_data,XU.level_id,XLP.level_number,XLR.is_coin,coin_amt,XLR.is_cashback,XLR.cashback_amt,XLR.cashback_type,XLR.cashback_amt_cap,XLR.is_contest_discount,XLR.discount_percent,XLR.discount_type,XLR.discount_amt_cap,XLR.reward_id')
        ->from(XP_USERS.' XU')
        ->join(XP_LEVEL_POINTS.' XLP','XU.level_id=XLP.level_pt_id')
        ->join(XP_LEVEL_REWARDS.' XLR','XLP.level_number=XLR.level_number')
        ->where_in('XU.user_id',$user_ids);

        if($single_record)
        {
            $result = $this->db_user->get()->row_array();
        }
        else{

            $result = $this->db_user->get()->result_array();
        }
        //echo $this->db_user->last_query();die('dfd');
        return $result;  
    }

      /**
     * Used for generate order unique id
     * @return string
     */
    public function _generate_order_unique_key() {
        $this->load->helper('security');
        $salt = do_hash(time() . mt_rand());
        $new_key = substr("o".$salt, 0, 10);
        return $new_key;
    }

    function get_processed_order_ids($reference_ids,$source)
    {

        $result = $this->db_user->select('order_id,user_id,reference_id')
        ->from(ORDER)
        ->where('source',$source)
        ->where_in('reference_id',$reference_ids)
        ->get()->result_array();

        if(!empty($result))
        {
            $result = array_column($result,NULL,'reference_id');
        }
        return $result;
    }

    /**
     * @uses credit cashback on deposit
     * @since June 2021
     * 
     * ***/
    function process_deposit_cashback()
    {
        $deposits =  $this->get_deposit_users();

        if(!empty($deposits))
        {
            $this->process_cashback($deposits,451,'cashback_type');
        }
    }

    private function get_percentage_amount($user_rewards,$one_deposit)
    {
        $cashback_percentage= $user_rewards[$one_deposit['user_id']]['cashback_amt'];
        $cap =  $user_rewards[$one_deposit['user_id']]['cashback_amt_cap'];
        $amount = $one_deposit['real_amount']*($cashback_percentage/100);
        if($amount > $cap)
        {
            $amount = $cap;
        }
        return $amount;  
    }
    
    function get_cash_contest_orders()
    {
        $current_date = format_date();
        $past_time = date(DATE_FORMAT,strtotime($current_date.' - '.$this->contest_joined_cashback_interval.' hour'));
        $completed_contests= $this->db_fantasy->select('C.contest_id',FALSE)
        ->from(CONTEST.' C')
        ->join(LINEUP_MASTER_CONTEST.' LMC','LMC.contest_id=C.contest_id')
        ->join(LINEUP_MASTER.' LM','LMC.lineup_master_id=LM.lineup_master_id')
        ->where('C.status',3)
        ->where('C.entry_fee>',0)//cach contest
        ->where('C.currency_type',1)
        ->group_by('C.contest_id')
        ->get()->result_array();// real entry fee

        if(!empty($completed_contests))
        {
            $contest_ids = array_column($completed_contests,'contest_id');

            $result = $this->db_user->select('O.user_id,O.real_amount,O.order_id',FALSE)
            ->from(ORDER.' O')
            ->join(XP_USERS.' XU','O.user_id=XU.user_id')
            ->where('O.source',1)
            ->where('O.real_amount>',0)
            ->where('O.status',1)
            ->where('XU.level_id>',0)
            ->where_in('O.reference_id',$contest_ids)
            ->get()->result_array();

            return $result;
        }

        return array();
    }

    function process_contest_joined_cashback()
    {
        $contest_orders = $this->get_cash_contest_orders();

         if(!empty($contest_orders))
         {
            $this->process_cashback($contest_orders,452,'discount_type');
 
         }
    }

    function process_cashback($order_list,$source,$cashback_key)
    {
        $user_ids = array_unique(array_column($order_list,'user_id'));
 
        //get reference ids
        $reference_ids =  array_column($order_list,'order_id');
        $orders = $this->get_processed_order_ids($reference_ids,$source);
        //get user level and reward details
       $user_rewards = $this->get_user_rewards($user_ids,0);
  
       if(!empty($user_rewards))
       {
           $user_rewards = array_column($user_rewards,NULL,'user_id');

           $user_txn_data = array();
           foreach($order_list as $one_deposit)
           {
               if(isset($orders[$one_deposit['order_id']]))
               {
                  continue;
               }

               if(!isset($user_rewards[$one_deposit['user_id']]))
               {
                   continue;
               }

               if($user_rewards[$one_deposit['user_id']]['is_cashback'] == '0' && $user_rewards[$one_deposit['user_id']]['is_contest_discount'] == '0' )
               {
                    continue;
               }

                 //user txn data
                $order_data = array();
                $order_data["order_unique_id"] = $this->_generate_order_unique_key();
                $order_data["user_id"]        = $one_deposit['user_id'];
                $order_data["source"]         = $source;
                $order_data["source_id"]      = $user_rewards[$one_deposit['user_id']]['reward_id'];
                $order_data["reference_id"]   = $one_deposit['order_id'];
                $order_data["season_type"]    = 1;
                $order_data["type"]           = 0;
                $order_data["status"]         = 0;
                $order_data["real_amount"]    = 0;
                $order_data["bonus_amount"]   = 0;
                $amount =$this->get_percentage_amount($user_rewards,$one_deposit);
                if(isset($user_rewards[$one_deposit['user_id']]) && $user_rewards[$one_deposit['user_id']][$cashback_key] == 1)//bonus
                {
                    $order_data["bonus_amount"] = $amount;
                }

                if(isset($user_rewards[$one_deposit['user_id']]) && $user_rewards[$one_deposit['user_id']][$cashback_key] == 0)//real
                {
                    $order_data["real_amount"] = $amount;
                }
               
                $order_data["winning_amount"] = 0;
                $order_data["points"] = 0;
                $order_data["custom_data"] = json_encode(array('level_number' => $user_rewards[$one_deposit['user_id']]['level_number']));
                $order_data["plateform"]      = PLATEFORM_FANTASY;
                $order_data["date_added"]     = format_date();
                $order_data["modified_date"]  = format_date();
                $user_txn_data[] = $order_data;
           }

           if(!empty($user_txn_data))
           {
               $this->db= $this->db_user;
                $this->db->trans_strict(TRUE);
                $this->db->trans_start();

                $user_txn_arr = array_chunk($user_txn_data, 999);
                foreach($user_txn_arr as $txn_data){
                    $this->insert_ignore_into_batch(ORDER, $txn_data);
                }

                $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points,SUM(real_amount) as real_amount FROM ".$this->db->dbprefix(ORDER)." WHERE source = $source AND type=0 AND status=0  GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
                SET U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.balance = (U.balance + OT.real_amount),O.status=1 
                WHERE O.source = $source AND O.type=0 AND O.status=0";
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
           
            } 
        }
    }
}