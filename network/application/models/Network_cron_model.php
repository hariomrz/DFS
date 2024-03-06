<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Network_cron_model extends MY_Model {
    
    public $db_user ;
    public $db_fantasy ;
    public $testingNode = FALSE;
    public $queue_name = NETWORK_COMMON_QUEUE;
    public $server_name;
    public function __construct() 
    {
      parent::__construct();
		  $this->db_user		= $this->load->database('user_db', TRUE);
		  $this->db_fantasy	= $this->load->database('fantasy_db', TRUE);
      $this->load->helper('queue');
      $this->server_name = get_server_host_name();
    }



   /**
     * Used for update contest status
     * @param int $sports_id
     * @return string print output
     */
   public function update_nw_contest_status()
   {
      //exit("update_nw_contest_status called.....");
       /*$where_condition = array(
        "status !=" => 1,
        "status !=" => 3,
        //"season_scheduled_date < "=> format_date()
       );
       $current_games = $this->get_nw_contest($where_condition);
       */
       $current_games = $this->get_nw_contest_for_update_status();
       //get_nw_contest_for_update_status
       //echo "<pre>conets season: ";print_r($current_games);die;
       if (!empty($current_games))
       {
          foreach ($current_games as $game_key => $game_value) 
          {
             if(empty($game_value['network_contest_id']))
             {
               continue;
             }


            $content        = array();
            $content['url'] = $this->server_name."/network/network_cron/update_nw_contest_status_by_id/".$game_value['network_contest_id'];
            add_data_in_queue($content, $this->queue_name);  
          }
       }
       exit("Cron url added for all open/pending contests.");
       
   } 

   /**
     * Used for get completed contest from master server side and add cron for each completed contests for distribute prizes to users
     * @param none
     * @return string print output
     */
   public function nw_contest_prize_distribution()
   {
       $where_condition = array(
          "status"               => 3,
          "is_prize_distributed" => 0,
          "season_scheduled_date < "=> format_date()
       );
       $completed_games = $this->get_nw_contest($where_condition);
       //echo "<pre>conets season: ";print_r($completed_games);die;
       if (!empty($completed_games))
       {
          foreach ($completed_games as $game_key => $game_value) 
          {
             if(empty($game_value['network_contest_id']))
             {
               continue;
             }


            $content        = array();
            $content['url'] = $this->server_name."/network/network_cron/nw_contest_prize_distribution_by_id/".$game_value['network_contest_id'];
            add_data_in_queue($content, $this->queue_name);  
          }
       }
       exit("Cron url added for all completed contests.");
       
   }

   /**
     * Used for get cancelled contests from master server side and add cron for each  contests for refund entry fee to users(participants)
     * @param none
     * @return string print output
     */
   public function nw_contest_cancellation()
   {
       $where_condition = array(
          "status"               => 1,
          "is_fee_refunded"      => 0,
          //"season_scheduled_date < "=> format_date()
       );
       $cancelled_games = $this->get_nw_contest($where_condition);
       //echo "<pre>conets season: ";print_r($cancelled_games);die;
       if (!empty($cancelled_games))
       {
          foreach ($cancelled_games as $game_key => $game_value) 
          {
             if(empty($game_value['network_contest_id']))
             {
               continue;
             }


            $content        = array();
            $content['url'] = $this->server_name."/network/network_cron/cancel_nw_contest_by_id/".$game_value['network_contest_id'];
            add_data_in_queue($content, $this->queue_name);  
          }
       }
       exit("Cron url added for all completed contests.");
       
   }

   /**
     * Used for get completed contest from master server side and add cron for each completed contests for send notifications to users(winners)
     * @param none
     * @return string print output
     */
   public function nw_contest_notification()
   {
       $where_condition = array(
          "status"               => 1,
          "is_fee_refunded"      => 0,
          //"season_scheduled_date < "=> format_date()
       );
       $cancelled_games = $this->get_nw_contest($where_condition);
       //echo "<pre>conets season: ";print_r($cancelled_games);die;
       if (!empty($cancelled_games))
       {
          foreach ($cancelled_games as $game_key => $game_value) 
          {
             if(empty($game_value['network_contest_id']))
             {
               continue;
             }


            $content        = array();
            $content['url'] = $this->server_name."/network/network_cron/cancel_nw_contest_by_id/".$game_value['network_contest_id'];
            add_data_in_queue($content, $this->queue_name);  
          }
       }
       exit("Cron url added for all completed contests.");
       
   }

   

  
   public function get_nw_contest($where_condition=array())
   {
      $this->db_fantasy->select("*")
                                ->from(NETWORK_CONTEST)
                                ->where('active', 1)
                                ->where('network_contest_id !=',"")
                                ->where('network_collection_master_id !=',"");
      if(!empty($where_condition))
      {
        $this->db_fantasy->where($where_condition);
      }  
                                
      $past_contests =  $this->db_fantasy->order_by("season_scheduled_date","ASC")
                                ->get()
                                ->result_array();
      //echo $this->db_fantasy->last_query();die;                          
    return $past_contests;                            
  }


  public function get_single_nw_contest($where_condition=array())
   {
      $this->db_fantasy->select("*")
                                ->from(NETWORK_CONTEST)
                                ->where('active', 1)
                                ->where('network_contest_id !=',"")
                                ->where('network_collection_master_id !=',"");
      if(!empty($where_condition))
      {
        $this->db_fantasy->where($where_condition);
      }  
                                
      $contest_row =  $this->db_fantasy->get()
                                ->row_array();
    return $contest_row;                            
  }

  public function get_order_detail_by_condition($condition=array())
  {
    if(empty($condition))
    {
      return array();
    }

    return $this->db_user->where($condition)
        ->get(ORDER,1)
        ->row_array();
  }

   /**
     * Used for generate order unique id
     * @return string
     */
    public function _generate_order_key() {
        $this->load->helper('security');
        do {
            $salt = do_hash(time() . mt_rand());
            $new_key = substr($salt, 0, 10);
        }

        // Already in the DB? Fail. Try again
        while (self::_order_key_exists($new_key));

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
                $input_data["reference_id"],
                $input_data["season_type"],
                $order_status,
                $input_data["custom_data"],
                $input_data["prize_image"]
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
                $this->load->model('user/User_nosql_model');
                $this->User_nosql_model->send_notification($tmp);
            }          
        }
        return $result;   
    }

    public function generate_order($amount, $user_id, $cash_type, $plateform, $source, $source_id,$reference_id='',$season_type,$status=0,$custom_data='',$prize_image)
    {
        
        $orderData                   = array();
        $orderData["user_id"]        = $user_id;
        $orderData["source"]         = $source;
        $orderData["source_id"]      = $source_id;
        $orderData["reference_id"]   = $reference_id;
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
        $orderData["custom_data"] = $custom_data;
        $orderData["prize_image"] = $prize_image;

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
            case NW_WON_GAME_SOURCE:
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
        $orderData['order_unique_id'] = $this->_generate_order_key();
        $this->db_user->insert(ORDER, $orderData);
        $order_id = $this->db_user->insert_id();
        
        if (!$order_id) 
        {            
            return false;
        }

        // Update User balance for order with completed status .
       if(!isset($orderData["prize_image"]))
       { 
          $orderData["status"] == 1 && $this->update_user_balance($orderData["user_id"], $orderData, "add");
       }   

        return $order_id;
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
            if(isset($balance_arr['source']) && $balance_arr['source'] == NW_WON_GAME_SOURCE && $oprator == 'add'){
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
        
        //delete user balance cache data
        $del_cache_key = "user_balance_".$user_id;
        $this->delete_cache_data($del_cache_key);
        return $this->db_user->affected_rows();  
    }


    public function create_refund_order($order_data=array())
    {
       if(empty($order_data))
       {
         return false;
       } 

      $this->db_user->insert(ORDER, $order_data);
      $order_id = $this->db_user->insert_id();
      return $order_id;
    }

     /**  Used to get user details and balance 
     * @param int $user_id
     * @return array
     */
    public function get_user_detail_with_balance($user_id)
    {
        $result =   $this->db_user->select("user_id,user_name,email,balance as real_amount, bonus_balance as bonus_amount, winning_balance as winning_amount,point_balance")
                                    ->where(array("user_id" => $user_id))
                                    ->limit(1)
                                    ->get(USER)
                                    ->row_array();
        return array(
                        "bonus_amount"   => $result["bonus_amount"]?$result["bonus_amount"]:0,
                        "real_amount"    => $result["real_amount"]?$result["real_amount"]:0,
                        "winning_amount" => $result["winning_amount"]?$result["winning_amount"]:0,
                        "point_balance"  => $result["point_balance"]?$result["point_balance"]:0,
                        "user_name"      => $result['user_name'],
                        "user_id"      => $result['user_id'],
                        "email"          => $result['email']
                    );
    }


      /**
     * Function to Update user balance
     *  Params: $user_id,$real_balance,$bonus_balance
     *  
     */
    function update_network_contest($update_data_arr,$where_arr)
    {
       
        $this->db_fantasy->where($where_arr);
        $this->db_fantasy->update(NETWORK_CONTEST,$update_data_arr);
        return TRUE;
          
    }

    public function get_nw_contest_for_update_status()
   {
      $this->db_fantasy->select("*")
                                ->from(NETWORK_CONTEST)
                                ->where('active', 1)
                                ->where('network_contest_id !=',"")
                                ->where('network_collection_master_id !=',"")
                                ->where_not_in("status",array(1,3));
                           
      $past_contests =  $this->db_fantasy->order_by("season_scheduled_date","ASC")
                                ->get()
                                ->result_array();
      //echo $this->db_fantasy->last_query();die;                          
    return $past_contests;                            
  }



  /*############### Un-Used Functions ##############################################*/

  
    
}
