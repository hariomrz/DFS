<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron_model extends MY_Model {
    
    public $db_user;
    public $market_id = 1;
    public $market = array('1' => 'NSE');
    public function __construct() {
      parent::__construct();
      $this->db_user	= $this->load->database('user_db', TRUE);
      $this->db_stock	= $this->load->database('stock_db',TRUE);
      $this->move_completed_lineup_stock = array();
      $this->move_completed_lineup_stock[1] = "normal_stock_format";
      $this->move_completed_lineup_stock[2] = "equity_stock_format";
    }

    /**
     * Used to update stock price data
     */
    public function update_stock_price($data=array()) {
      $stock_id = isset($data['stock_id']) ? $data['stock_id'] : 0;
      $this->db->select('s.stock_id, s.trading_symbol, s.exchange_token');
      $this->db->from(STOCK.' s');
		  //$this->db->where('s.status', 1);
      if(!empty($stock_id)) {
        $this->db->where('s.stock_id', $stock_id, FALSE);
      }
      $this->db->order_by('s.trading_symbol', 'ASC');
      $query = $this->db->get();
      if($query->num_rows()) {
        $results = $query->result_array();
        $instruments = array();
        $stocks = array();
        foreach($results as $result) {
          $key = $this->market[$this->market_id].":".$result['trading_symbol'];
          $instruments[] = $key;
          $stocks[$key] = $result['stock_id'];
        }
        if(!empty($instruments)) {
          $results = $this->quote_ohlc($instruments);
          $results = json_decode(json_encode($results), true);//Convert object to array
          $current_date_time = format_date();
          if(!empty($results)) {
            foreach($results as $key => $result) {
              $stock_id = $stocks[$key];
              $update_data = array();
              $update_data['last_price'] = $result['last_price'];
              $ohlc = $result['ohlc'];
              $update_data['open_price'] = $ohlc['open'];
              $update_data['high_price'] = $ohlc['high'];
              $update_data['low_price'] = $ohlc['low'];
              $update_data['close_price'] = $ohlc['close'];
              $update_data['modified_date'] = $current_date_time;

              $this->db->update(STOCK,$update_data,array('stock_id'=>$stock_id));
              //print_r($update_data);die;
              //echo "stock_id = ".$stock_id." last_price = ".$update_data['last_price']." open_price = ".$update_data['open_price']." high_price = ".$update_data['high_price']." low_price = ".$update_data['low_price']." close_price = ".$update_data['close_price'];
            }
          }
        }          
      }
    }

    // For daily and custom date
    public function game_cancellation($is_recursive="0") {   
      set_time_limit(0);
      $current_date = format_date();          
      // When no user joined contest and open contest will cancel directly because no further action required for those contest
      if($is_recursive == "0"){
        $where = array('status' => 0,'total_user_joined' => 0,'scheduled_date <' => $current_date );
        $this->db->update(CONTEST, array('status' => 1), $where);
      }
      $contest_list = $this->db->select("C.contest_id, C.contest_unique_id, C.contest_name, C.entry_fee, C.collection_id, MG.group_name, C.currency_type")
                                     ->from(CONTEST." AS C")
                                     ->join(MASTER_GROUP . " AS MG", " MG.group_id = C.group_id", 'INNER')
                                     ->where("C.status", 0)
                                     ->where("C.total_user_joined > ", 0)
                                     ->where("C.total_user_joined < ", 'C.minimum_size', FALSE)
                                     ->where("C.scheduled_date < ", $current_date)
                                     ->limit(1)
                                     ->get()
                                     ->row_array();  
      //echo "<pre>";print_r($contest_list);die;                                                     
      if (!empty($contest_list)) {
          $subject = "Oops! Contest has been Cancelled";
          if(isset($contest_list['group_name']) && $contest_list['group_name'] != ""){
            $subject = "Oops! ".$contest_list['group_name']." has been Cancelled";
          }


          $additional_data['cancel_reason'] = '';
          $additional_data['current_date'] = $current_date;
          $additional_data['notification_type'] = 551;
          $additional_data['subject'] = $subject;

          //refund for paid contest
          if($contest_list['entry_fee'] > 0){
            $this->process_game_cancellation($contest_list, $additional_data);
          }else{
            //update for free contest
            $this->db->where("contest_id",$contest_list['contest_id']);
            $this->db->update(LINEUP_MASTER_CONTEST, array('fee_refund' => 1));
            
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
                              ->where('O.source', 460)
                              ->get()
                              ->result_array();

          //echo "<pre>";print_r($refund_data);die;
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
              $order_data["plateform"]      = PLATEFORM_FANTASY;
              $order_data["date_added"]     = format_date();
              $order_data["modified_date"]  = format_date();
              $order_data["custom_data"]  = json_encode(array(
                'contest_name' => $contest_list['contest_name'],
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
              $this->db->where("contest_id",$contest_id);
              $this->db->update(LINEUP_MASTER_CONTEST, array('fee_refund' => 1));

              $this->db_stock = $this->db;

              $this->db = $this->db_user;
              //Start Transaction
              $this->db->trans_strict(TRUE);
              $this->db->trans_start();
              
              $user_txn_arr = array_chunk($user_txn_data, 999);
              foreach($user_txn_arr as $txn_data){
                $this->insert_ignore_into_batch(ORDER, $txn_data);
              }

              $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = 461 AND type=0 AND status=0 AND reference_id='".$contest_id."' GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
              SET U.balance = (U.balance + OT.real_amount),U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
              WHERE O.source = 461 AND O.type=0 AND O.status=0 AND O.reference_id='".$contest_id."' ";

              $this->db->query($bal_sql);
              //Trasaction End
              $this->db->trans_complete();
              if ($this->db->trans_status() === FALSE ) { 
                $this->db->trans_rollback();
              } else {
                $this->db->trans_commit();
                // Game table update cancel status
                $this->db_stock->where('contest_id', $contest_id);
                $this->db_stock->update(CONTEST, array('status' => 1, 'modified_date' => $additional_data['current_date'], 'cancel_reason' => $additional_data['cancel_reason']));
              }

            } catch (Exception $e) {
                //echo 'Caught exception: '.  $e->getMessage(). "\n";die;
            }
            $this->db = $this->db_stock;
          }

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
      $notification_type = isset($cancel_data['notification_type']) ? $cancel_data['notification_type'] : 551;
      $cancel_reason = isset($cancel_data['cancel_reason']) ? $cancel_data['cancel_reason'] : "";
      $this->db->select("C.currency_type, CM.category_id, CM.scheduled_date, LM.user_id, C.contest_id, C.collection_id, C.contest_name, C.status, C.contest_unique_id, C.size, C.prize_pool, C.entry_fee, LM.lineup_master_id, C.prize_type, CM.name, count(DISTINCT LMC.lineup_master_contest_id) as total_teams, C.cancel_reason,CM.end_date,CM.stock_type",false)
                    ->from(CONTEST . " C")
                    ->join(COLLECTION . " CM", "CM.collection_id = C.collection_id", "INNER")
                    ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.contest_id = C.contest_id", "INNER")
                    ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
                    ->where("C.status", 1)
                    ->where("LMC.fee_refund", 1)
                    ->where("C.is_win_notify", 0)
                    ->group_by("CM.collection_id,C.contest_id,LM.user_id")
                    ->order_by("LM.user_id,C.contest_id,C.collection_id");

      if(isset($cancel_data['contest_id']) && $cancel_data['contest_id'] != ""){
        $this->db->where("C.contest_id",$cancel_data['contest_id']);
      }
      $query = $this->db->get();
      $result = $query->result_array();
      //echo "<pre>";print_r($result);die;
      //$match_data = array();
      $notification_data = array();
      if (!empty($result)) {   
        $category_list = $this->get_category_list();
			  $category_list = array_column($category_list, 'name', 'category_id');

        $this->db_stock = $this->db;
        $this->db = $this->db_user;        
        foreach ($result as $res) {          
          if(!isset($notification_data[$res['user_id']."_".$res['collection_id']]) || empty($notification_data[$res['user_id']."_".$res['collection_id']])) {            
            $user_detail = $this->get_single_row('email, user_name', USER, array("user_id"=>$res["user_id"]));
            
            $user_temp_data = array();
            $user_temp_data['source_id'] = $res['collection_id'];
            $user_temp_data['user_id'] = $res['user_id'];
            $user_temp_data['email'] = isset($user_detail['email']) ? $user_detail['email'] : "";
            $user_temp_data['user_name'] = isset($user_detail['user_name']) ? $user_detail['user_name'] : "";
           // $user_temp_data['match_data'] = $match_data[$res['season_game_uid']];
            $user_temp_data['collection_name'] = $res['name'];

            $user_temp_data['category_name'] = $category_list[$res['category_id']];

            $user_temp_data['scheduled_date'] = $res['scheduled_date'];

            $user_temp_data['end_date'] = $res['end_date'];
            $user_temp_data['category_id'] = $res['category_id'];
            $user_temp_data['stock_type'] = $res['stock_type'];
            //$user_temp_data['season_game_count'] = $res['season_game_count'];
            $user_temp_data['contest_data'] = array();
            $notification_data[$res['user_id']."_".$res['collection_id']] = $user_temp_data;
          }

          $contest_data = array();
          $contest_data['contest_id'] = $res['contest_id'];
          $contest_data['user_id'] = $res['user_id'];
          $contest_data['contest_unique_id'] = $res['contest_unique_id'];
          $contest_data['contest_name'] = $res['contest_name'];
          $contest_data['collection_name'] = $res['name'];
          $contest_data['size'] = $res['size'];
          $contest_data['prize_pool'] = $res['prize_pool'];
          $contest_data['entry_fee'] = $res['entry_fee'];
          $contest_data['currency_type'] = $res['currency_type'];
          $contest_data['total_teams'] = $res['total_teams'];
          $contest_data['prize_type'] = $res['prize_type'];
          $contest_data['cancel_reason'] = $res['cancel_reason'];
          
          $notification_data[$res['user_id']."_".$res['collection_id']]['contest_data'][] = $contest_data;

          // Game table update is_win_notify 1 for cancel notification
          $this->db_stock->where('contest_id', $res['contest_id']);
          $this->db_stock->update(CONTEST, array('is_win_notify' => '1', 'modified_date' => format_date()));
        }

        $this->db = $this->db_stock;
      }
      //echo "<pre>";print_r($notification_data);die;
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
          $notify_data["subject"] = "Oops! Contests has been cancelled!";
          if($notification_type == 552) {
              //$notify_data["subject"] = $cancel_data['contest_name'].$cancel_data["subject"];
              $notify_data["subject"] = "Contests has been cancelled by technical team!";
          }

          $content = array();
          $content['notification_type'] = $notification_type;
         // $content['match_data'] = $notification['match_data'];
          $content['scheduled_date'] = $notification['scheduled_date'];
          $content['category_name'] = $notification['category_name'];
          $content['collection_name'] = $notification['collection_name'];
          $content['stock_type'] = $notification['stock_type'];
          if($notification['stock_type'] == 3 || $notification['stock_type'] == 4){
            $content['collection_name'] = $this->get_rendered_collection_name($notification);
          }
         // $content['season_game_count'] = $notification['season_game_count'];
          $content['contest_data'] = $notification['contest_data'];
          $content['int_version'] = $this->app_config['int_version']['key_value'];
          //$content['contest_name'] = $notification['name'];
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
        
        $this->db->select('C.contest_id, C.contest_unique_id, C.contest_name, C.entry_fee, C.collection_id, C.total_user_joined,MG.group_name,C.scheduled_date')
                ->from(CONTEST. ' C')
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

              $additional_data['notification_type'] = 552;
              $additional_data['subject'] = ' has been Cancelled.';

              if($contest_list['entry_fee'] > 0){
                $this->process_game_cancellation($contest_list, $additional_data);
              }else{
                //update for free contest
                $this->db->where("contest_id",$contest_list['contest_id']);
                $this->db->update(LINEUP_MASTER_CONTEST, array('fee_refund' => 1));
                
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
        
        $this->db->select('contest_id, contest_unique_id, contest_name, entry_fee, collection_id')
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
                    add_data_in_queue($data, 'stock_game_cancel');
                }
            }
        }
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

    /**
     * Used to get email template information
     */
    public function get_email_template_list(){
        $result = $this->db_user->select("template_name,template_path,notification_type,status,subject")
                    ->from(EMAIL_TEMPLATE)
                    ->where("status",1)
                    ->get()
                    ->result_array();
        return $result;
    }


    private function get_collection_to_update($collection_list)
    {
      $all_collection_id = array_column($collection_list, 'collection_id');
      $all_collection_id_str = implode(',', array_map( function( $n ){ return '\''.$n.'\''; } ,  $all_collection_id) );

      $sql = $this->db->select('SUM(IF(SH.history_id IS NULL,1,0)) as collection_null_stocks,C.collection_id, C.scheduled_date,C.status')
      ->from(COLLECTION." AS C")
      ->join(COLLECTION_STOCK." CS","C.collection_id=CS.collection_id")
      ->join(STOCK_HISTORY." SH","SH.stock_id=CS.stock_id AND SH.schedule_date=DATE_FORMAT(C.end_date,'%Y-%m-%d')",'LEFT')
       ->where("C.collection_id IN ( $all_collection_id_str )")
      ->where("C.collection_id > ", 0)
      ->where("C.status", 0)
      ->where_in("C.stock_type",array("1","2"))
      ->group_by('C.collection_id')
      ->get();
      //echo $this->db->last_query(); die;
      $collection_data = $sql->result_array();

      
      $collection_to_close = array_filter(
        $collection_data,
        function($value)  {
            return $value['collection_null_stocks'] == 0;
        }
      );

      return $collection_to_close;
    }
    /**
     * Used for update contest status
     * @param int $sports_id
     * @return string print output
     */
   public function update_contest_status($current_game=array())
   {
      if(empty($current_game)) {
        $current_game = $this->get_past_collections();
      }

      if(empty($current_game)) {
        return false;
      }
       
       $current_game = $this->get_collection_to_update($current_game);
       //echo "<pre>conets season: ";print_r($current_game);die;
       if (!empty($current_game))
       {
           $all_collection_id = array_column($current_game, 'collection_id');
           $all_collection_id_str = implode(',', array_map( function( $n ){ return '\''.$n.'\''; } ,  $all_collection_id) );
           $sql = $this->db->select('C.collection_id, G.contest_id, G.contest_unique_id, G.contest_name, G.status')
                                   ->from(CONTEST." AS G")
                                   ->join(COLLECTION." C","G.collection_id=C.collection_id")
                                    ->where("C.collection_id IN ( $all_collection_id_str )")
                                   ->where("G.collection_id > ", 0)
                                   ->where("G.status", 0)
                                   ->where("C.status", 0)
                                   ->where("G.total_user_joined >= ", 'G.minimum_size', FALSE)
                                   ->get();
           //echo $this->db->last_query(); die;
           $contest_data = $sql->result_array();
           //echo "<pre>";print_r($contest_data); die;
           if(!empty($contest_data))
           {
              $contest_ids = array_column($contest_data, 'contest_id');
              $score_check = $this->db->select("count(*) as total")
                    ->from(LINEUP_MASTER_CONTEST)
                    ->where_in("contest_id",$contest_ids)
                    ->where("total_score <> ","0")
                    ->where("fee_refund","0")
                    ->get()
                    ->row_array();
              if(!empty($score_check) && isset($score_check['total']) && $score_check['total'] > 0){
                $current_date = format_date();
                // Mark CONTEST Status Complete
                $this->db->where_in("contest_id", $contest_ids);
                $this->db->where("status", 0);
                $this->db->update(CONTEST, array("status" => 2,"modified_date" => $current_date ));

                // Mark COLLECTION Status Complete
                $collection_ids = array_column($contest_data, 'collection_id');
                $this->db->where_in("collection_id", $collection_ids);
                $this->db->update(COLLECTION, array("status" => 1, "modified_date" => $current_date));
                
                $schedule_date = format_date('today', 'Y-m-d');
                $this->db->where('schedule_date', $schedule_date);
                $this->db->where('status', 0);
                $this->db->update(STOCK_HISTORY, array('status'=>2, 'added_date' => $current_date));

                $this->load->helper('queue');
                //update score for above collection list
                foreach($collection_ids as $collection_id) {
                  $content = array();
                  $content['action'] = 'calculate_score';
                  $content['collection_id'] = $collection_id;
                  add_data_in_queue($content,'stock_calculate_score');
                }

                 //delete contest cache
                 foreach($contest_ids as $contest_id) {
                    $del_cache_key = 'st_contest_'.$contest_id;
                    $this->delete_cache_data($del_cache_key);
                 }

                 //delete lobby filters data
                 $del_cache_key = 'st_lobby_filters';
                 $this->delete_cache_data($del_cache_key);
 
                 echo "Update status for contest having collection_ids: ".implode(',', $collection_ids)." ";   
              }
           } 
           else
           {
             //if all contest of any collection cancelled then collection status update
              $this->update_contest_collection_status($all_collection_id_str);
               echo "No contest status update ";
           }
       }
       else
       {
           echo "No contest status update ";
       }
   } 

   function update_contest_collection_status($all_collection_id_str='')
   {
   
      if(empty($all_collection_id_str))
      {
        return false;
      }

        $sql = $this->db->select('G.collection_id, G.contest_id, G.contest_unique_id, G.contest_name, G.status')
        ->from(CONTEST." AS G")
        ->join(COLLECTION." C","G.collection_id=C.collection_id")
        ->where("C.collection_id IN ( $all_collection_id_str )")
        ->where("C.collection_id > ", 0)
        ->where("G.status", 1)
        ->get();
          //echo $this->db->last_query();die();
          $collection_data = $sql->result_array();
          if(!empty($collection_data))
          {
              $collection_id = array();
              $collection_id = array_column($collection_data,'collection_id');
              $this->db->where_in("collection_id", $collection_id);
              $this->db->update(COLLECTION, array("status" => 1, "modified_date" => format_date()));
          }
   }

   private function validate_stock_date($stock_type=1,$current_date)
   {
      
    if($stock_type==1)
    {
      $end_date = date('Y-m-d '.CONTEST_END_TIME,strtotime($current_date));
      $start_date = date('Y-m-d '.CONTEST_START_TIME,strtotime($current_date));
    }
    else{
      $end_date = date('Y-m-d '.CONTEST_END_TIME_EQUITY,strtotime($current_date));
      $start_date = date('Y-m-d '.CONTEST_START_TIME_EQUITY,strtotime($current_date));
    }
     
    $end_date = date('Y-m-d H:i:s',strtotime($end_date.' +45 minutes'));
      $current_time = strtotime($current_date);  // 18-09-2021 03:45
      $end_time = strtotime($end_date); // 18-09-2021 10:50
      $start_time = strtotime($start_date); // 18-09-2021 03:45
      if($current_time > $end_time || $current_time < $start_time) {
        echo "No lineup score update because market closed.";
        return true;
        //exit();
      }
      return false;
   }
   /**
     * @Summary: This function for use for update fantasy plaer points to lineup and lineup master table after caclulation of fantasy points 
     * database.
     * @access: public
     * @param:$league_id
     * @return:
     */
    public function update_scores_in_lineup_by_collection($collection_id="") {

        $current_date = format_date();
        if(empty($collection_id)) {
          $current_game = $this->get_collection_for_point_update();
        } else {
          $one_collection =$this->get_single_row('collection_id,scheduled_date,published_date,end_date,stock_type',COLLECTION,array('collection_id' => $collection_id));
          $current_game[] = $one_collection;
        }
        // echo "<pre>";print_r($current_game);die('ddfd');
        if(!empty($current_game)) {
            $captain_point = CAPTAIN_POINT;
           
            if($captain_point <= 1) {
               $captain_point = "1";
            }

            $all_collection_id = array_column($current_game, 'collection_id');
            $collection_publish_date_map = array_column($current_game,NULL,'collection_id');
            //$all_collection_id_str = implode(',', array_map( function( $n ){ return '\''.$n.'\''; } ,  $all_collection_id) );
          
            //echo "<pre>";print_r($all_collection_id);die;
            if(!empty($all_collection_id)) {
              //Start Transaction
              $this->db->trans_strict(TRUE);
              $this->db->trans_start();
              $collection_ids_to_update_score = array();
            
              /**
               * Stock- Reliance | Shares- 500 | B/S- Sell | Closing POint- 1100 |  Result Rate- 1080 | * Point Calucaltion- difference of closing rate and Result rate * Shares= 1100-1080=20*500= 10,000 points. Refer attached screenshot for reference.

                L lot_size take from collection_stock
                CR closing rate : collection ki publish date ka stock histroy se close_price
                RR Result rate: colletion end_date ka stock histroy se close_price

                score (P) = (CR-RR)*L
                
                score (P) = (RR-CR)*L

                Lot size* Difference = Fantasy Points of Stocks
                1. if difference is negative for BUY option, then Fantasy points is in Negative value
                2. if difference is positive for BUY option, then Fantasy points is in Positive value.
                3. if difference is negative for SELL option, then Fantasy points is in Positive value.
                4. if difference is positive for SELL option, then Fantasy points is in Negative value
                * 
                *  (TRUNCATE((CASE WHEN SH2.close_price IS NOT NULL THEN SH2.close_price ELSE S.last_price END), 2)-SH1.close_price)*CS.lot_size as score
                * ****/
              foreach ($collection_publish_date_map as $collection_id => $collection_data ) {

                if($this->validate_stock_date($collection_data['stock_type'],$current_date))
                {
                  continue;
                }
                //echo "<pre>";print_r($season_str);die;
                //Update player fantasy score in particular lineup table based on collection id
                $sql = $this->get_stock_type_point_query($collection_data);       
                
                $this->db->query($sql);
                $collection_ids_to_update_score[] = $collection_id;
                $sql = $this->db->select('LM.lineup_master_id,LM.user_id')
                                        ->from(LINEUP_MASTER . " AS LM")
                                        ->join(LINEUP_MASTER_CONTEST . " AS LMC", "LMC.lineup_master_id = LM.lineup_master_id", 'INNER')
                                        ->join(CONTEST . " AS C", "C.contest_id = LMC.contest_id AND C.status != 1 ", 'INNER')
                                        ->where('LM.collection_id', $collection_id)
                                        ->group_by('LM.lineup_master_id')
                                        ->get();
                $lineup_master_ids = $sql->result_array();
                //echo "<pre>";print_r($lineup_master_ids);die;

               
                $salary_cap =  $this->get_stock_price_cap('salary_cap',2);

                /**
                 * CASE 1
                 * salary cap 500000
                 * score + remaining cap  = 550000
                 * result = 10%
                 * 
                 * (total-salary_cap/salary_cap)*100
                 * 
                 * LMC.total_score =IFNULL( L_PQ.scores,'0.00')+IFNULL(LM.remaining_cap,'0.00'),  //+L_PQ.alpha_beta commented from total_score update line no 717
                 * LMC.total_score = IFNULL( L_PQ.scores,'0.00')+IFNULL(LM.remaining_cap,'0.00') commented  logic was changed discuss with gaurav sir and client from line 717
                 * **/
                if (!empty($lineup_master_ids)) {
                    $ids = array_column($lineup_master_ids, 'lineup_master_id');
                    $update_sql = " UPDATE  
                                        ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC 
                                    INNER JOIN   ".$this->db->dbprefix(LINEUP_MASTER)." LM ON LMC.lineup_master_id=LM.lineup_master_id
                                    INNER JOIN 
                                        ( SELECT SUM(L.score) AS scores,
                                        SUM(L.gain_loss) as  gain_loss,
                                        SUM(CASE WHEN L.captain=1 OR L.captain=2 THEN L.gain_loss ELSE 0 END) AS alpha_beta,
                                        L.lineup_master_id 
                                            FROM 
                                                ".$this->db->dbprefix(LINEUP)." AS L 
                                        WHERE 
                                            L.lineup_master_id IN (".implode(',', $ids).")
                                        GROUP BY 
                                            L.lineup_master_id
                                        ) AS L_PQ ON L_PQ.lineup_master_id = LMC.lineup_master_id 
                                    SET "; 
                                    
                                    if($collection_data['stock_type']==1){
                                      $update_score_sql = " LMC.last_score=LMC.total_score,
                                      LMC.total_score = IFNULL( L_PQ.scores,'0.00'),
                                      LMC.percent_change=((IFNULL( L_PQ.gain_loss,'0.00'))/$salary_cap)*100
                                      WHERE LMC.fee_refund=0";
                                    }else{
                                      $update_score_sql = " LMC.last_score=LMC.total_score,
                                        LMC.total_score = $salary_cap + IFNULL(L_PQ.gain_loss,'0.00'),
                                        LMC.percent_change=((IFNULL( L_PQ.gain_loss,'0.00'))/$salary_cap)*100
                                        WHERE LMC.fee_refund=0 ";
                                    }
                    $update_sql  = $update_sql.$update_score_sql;
                    $this->db->query($update_sql);

                    $lineup_master_ids_chunk = array_chunk($ids,3000);
                    $this->db->select('LMC.lineup_master_contest_id,LMC.contest_id');
                    $this->db->from(LINEUP_MASTER_CONTEST . " AS LMC");
                    foreach($lineup_master_ids_chunk as $lineup_master_ids) {
                      $this->db->or_where_in('LMC.lineup_master_id', $lineup_master_ids);
                    }    
                    $sql = $this->db->get();
                    //   echo $this->db->last_query();                    
                    $lmc_ids = $sql->result_array();
                    //echo "<pre>";print_r($lmc_ids);die;
                    $contest_ids = array_unique(array_column($lmc_ids, 'contest_id'));
                    $lmc_ids = array_unique(array_column($lmc_ids, 'lineup_master_contest_id'));
                    $this->load->helper('queue');
                    foreach($contest_ids as $contest_id) {
                        $contest_info = $this->db->select("is_tie_breaker")
                              ->from(CONTEST) 
                              ->where("contest_id",$contest_id) 
                              ->get()->row_array();
                        $rank_str = "";
                        if(isset($contest_info['is_tie_breaker']) && $contest_info['is_tie_breaker'] == 1){
                          $rank_str = ",lineup_master_contest_id ASC";
                        }
                        //update rank during score
                        $update_rank_sql = "UPDATE 
                                                ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC 
                                            INNER JOIN 
                                                (SELECT LMC1.lineup_master_contest_id,RANK() OVER (ORDER BY `total_score` DESC ".$rank_str.") user_rank 
                                                  FROM ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC1 
                                                  WHERE LMC1.contest_id = ".$contest_id.") AS L_PQ 
                                            ON L_PQ.lineup_master_contest_id = LMC.lineup_master_contest_id 
                                            SET 
                                                LMC.game_rank = IFNULL(L_PQ.user_rank,'0')
                                            WHERE LMC.fee_refund=0 ";
                        $this->db->query($update_rank_sql);
                    }

                    $score_updated_date = $current_date;
                    $this->db->set('score_updated_date',$score_updated_date);
                    $this->db->where('collection_id', $collection_id);        
                    $this->db->update(COLLECTION);


                    $lmc_data = array('action' => 'collection_info', 'collection_id' => $collection_id, 'lmc_ids' => $lmc_ids, 'score_updated_date' => $score_updated_date);
                                        
                    //$this->notify_node_collection_info($lmc_data);
                    add_data_in_queue($lmc_data,'stock_notify_node');

                    //Trasaction End
                    $this->db->trans_complete();
                    if ($this->db->trans_status() === FALSE ) {
                      $this->db->trans_rollback();
                    } else {
                      $this->db->trans_commit();
                    }

                    //clear cache
                    $del_cache_key = 'st_collection_player_'.$collection_id;
                    $this->delete_cache_data($del_cache_key);

                    echo "Update lineup score for lineup_master_id:".implode(',', $ids);
                } else {
                    echo "No lineup score update";
                }
              }                                           
            }

            //Update score_updated_info in season table
            //echo "<pre>";print_r($current_game);die; 
            
        }
    }

    private function get_stock_type_point_query($collection_data)
    {

      $captain_point = CAPTAIN_POINT;
           
      if($captain_point <= 1) {
         $captain_point =1;
      }

      $vice_captain_point = VICE_CAPTAIN_POINT;
           
      if($vice_captain_point <= 1) {
         $vice_captain_point = 1;
      }

      $published_date = date('Y-m-d',strtotime($collection_data['published_date']));
      $scheduled_date = date('Y-m-d',strtotime($collection_data['scheduled_date']));
      $end_date =  date('Y-m-d',strtotime($collection_data['end_date']));
      $collection_id = $collection_data['collection_id'];
      $current_date =format_date();

      if($collection_data['stock_type'] == 1)
      {

         /**
               * Stock- Reliance | Shares- 500 | B/S- Sell | Closing POint- 1100 |  Result Rate- 1080 | * Point Calucaltion- difference of closing rate and Result rate * Shares= 1100-1080=20*500= 10,000 points. Refer attached screenshot for reference.

                L lot_size take from collection_stock
                CR closing rate : collection ki publish date ka stock histroy se close_price
                RR Result rate: colletion end_date ka stock histroy se close_price

                score (P) = (CR-RR)*L
                
                score (P) = (RR-CR)*L

                Lot size* Difference = Fantasy Points of Stocks
                1. if difference is negative for BUY option, then Fantasy points is in Negative value
                2. if difference is positive for BUY option, then Fantasy points is in Positive value.
                3. if difference is negative for SELL option, then Fantasy points is in Positive value.
                4. if difference is positive for SELL option, then Fantasy points is in Negative value
                * 
                *  (TRUNCATE((CASE WHEN SH2.close_price IS NOT NULL THEN SH2.close_price ELSE S.last_price END), 2)-SH1.close_price)*CS.lot_size as score
                * ****/
        $sql = "UPDATE ".$this->db->dbprefix(LINEUP)."  AS LU
        INNER JOIN (
                    SELECT CS.lot_size, CS.stock_id,SH1.close_price as closing_rate,TRUNCATE((CASE WHEN SH2.close_price IS NOT NULL THEN SH2.close_price ELSE S.last_price END), 2) as result_rate,                                    
                    (
                      TRUNCATE((CASE WHEN C.scheduled_date <= '".$current_date."' AND C.end_date >= '".$current_date."' THEN ((S.last_price-SH1.close_price)/SH1.close_price)*100 
                         WHEN C.end_date < '".$current_date."' THEN ((SH2.close_price-SH1.close_price)/SH1.close_price)*100 
                         ELSE ((S.last_price-S.open_price)/S.open_price)*100 END),2)*100
                    ) as score                           
  
                    FROM ".$this->db->dbprefix(COLLECTION_STOCK )." AS CS
                    INNER JOIN ".$this->db->dbprefix(COLLECTION)." C ON CS.collection_id=C.collection_id
                    INNER JOIN ".$this->db->dbprefix(STOCK)." S ON S.stock_id=CS.stock_id
                    INNER JOIN ".$this->db->dbprefix(STOCK_HISTORY)." SH1
                    ON CS.stock_id=SH1.stock_id AND SH1.schedule_date ='".$published_date."' 
                    LEFT JOIN  ".$this->db->dbprefix(STOCK_HISTORY)." SH2
                    ON CS.stock_id=SH2.stock_id AND SH2.schedule_date ='".$end_date."'
                    WHERE 
                    CS.collection_id=$collection_id
                    GROUP BY CS.stock_id
                    ) AS GP ON GP.stock_id = LU.stock_id
        INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER)." AS LM  ON  LU.lineup_master_id=LM.lineup_master_id
        SET LU.score = (
                        CASE WHEN LU.captain = '1' AND GP.score>=0 AND LU.type=1 THEN IFNULL(GP.score,'0.00')*".$captain_point."
                          WHEN LU.captain = '1' AND GP.score>=0 AND LU.type=2 THEN IFNULL(-1*ABS(GP.score),'0.00')*".$captain_point."
                          WHEN LU.captain = '1' AND GP.score<0 AND LU.type=1 THEN IFNULL(-1*ABS(GP.score),'0.00')*".$captain_point."
                          WHEN LU.captain = '1' AND GP.score<0 AND LU.type=2 THEN IFNULL(ABS(GP.score),'0.00')*".$captain_point."
                          WHEN LU.captain = '2' AND GP.score>=0 AND LU.type=1 THEN IFNULL(GP.score,'0.00')*".$vice_captain_point."
                          WHEN LU.captain = '2' AND GP.score>=0 AND LU.type=2 THEN IFNULL(-1*ABS(GP.score),'0.00')*".$vice_captain_point."
                          WHEN LU.captain = '2' AND GP.score<0 AND LU.type=1 THEN IFNULL(-1*ABS(GP.score),'0.00')*".$vice_captain_point."
                          WHEN LU.captain = '2' AND GP.score<0 AND LU.type=2 THEN IFNULL(ABS(GP.score),'0.00')*".$vice_captain_point."
                          WHEN LU.captain = '0' AND GP.score>=0 AND LU.type=1 THEN IFNULL(GP.score,'0.00')
                          WHEN LU.captain = '0' AND GP.score>=0 AND LU.type=2 THEN IFNULL(-1*ABS(GP.score),'0.00')
                          WHEN LU.captain = '0' AND GP.score<0 AND LU.type=1 THEN IFNULL(-1*ABS(GP.score),'0.00')
                          WHEN LU.captain = '0' AND GP.score<0 AND LU.type=2 THEN IFNULL(ABS(GP.score),'0.00')
  
                            ELSE IFNULL(GP.score,'0.00') END
                        )
        WHERE LM.collection_id=$collection_id
        ";  
      }
      else{
        $sql = "UPDATE ".$this->db->dbprefix(LINEUP)."  AS LU
      INNER JOIN (
                  SELECT CS.lot_size, CS.stock_id,SH1.close_price as closing_rate,TRUNCATE((CASE WHEN SH2.close_price IS NOT NULL THEN SH2.close_price ELSE S.last_price END), 2) as result_rate,                                    
                  (
                    (CASE 
                        WHEN C.scheduled_date <= '".$current_date."' AND C.end_date >= '".$current_date."' THEN S.last_price
                       WHEN C.end_date < '".$current_date."' THEN SH2.close_price
                       ELSE S.last_price END)
                  ) as score ,
                  (
                    TRUNCATE((CASE WHEN C.scheduled_date <= '".$current_date."' AND C.end_date >= '".$current_date."' THEN (S.last_price-SH1.close_price) 
                       WHEN C.end_date < '".$current_date."' THEN (SH2.close_price-SH1.close_price) 
                       ELSE (S.last_price-S.open_price) END),2)
                  ) as score_diff                                         

                  FROM ".$this->db->dbprefix(COLLECTION_STOCK )." AS CS
                  INNER JOIN ".$this->db->dbprefix(COLLECTION)." C ON CS.collection_id=C.collection_id
                  INNER JOIN ".$this->db->dbprefix(STOCK)." S ON S.stock_id=CS.stock_id
                  INNER JOIN ".$this->db->dbprefix(STOCK_HISTORY)." SH1
                  ON CS.stock_id=SH1.stock_id AND SH1.schedule_date ='".$published_date."' 
                  LEFT JOIN  ".$this->db->dbprefix(STOCK_HISTORY)." SH2
                  ON CS.stock_id=SH2.stock_id AND SH2.schedule_date ='".$end_date."'
                  WHERE 
                  CS.collection_id=$collection_id
                  GROUP BY CS.stock_id
                  ) AS GP ON GP.stock_id = LU.stock_id
      INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER)." AS LM  ON  LU.lineup_master_id=LM.lineup_master_id
      SET LU.score = IFNULL(GP.score,'0.00')*LU.user_lot_size,
      LU.gain_loss=(
        CASE 
         WHEN LU.captain = '1' AND GP.score_diff>=0 AND LU.type=1 THEN IFNULL(GP.score_diff,'0.00')*LU.user_lot_size*".$captain_point."
         WHEN LU.captain = '1' AND GP.score_diff>=0 AND LU.type=2 THEN IFNULL(-1*ABS(GP.score_diff),'0.00')*LU.user_lot_size*".$captain_point."
         WHEN LU.captain = '1' AND GP.score_diff<0 AND LU.type=1 THEN IFNULL(-1*ABS(GP.score_diff),'0.00')*LU.user_lot_size*".$captain_point."
         WHEN LU.captain = '1' AND GP.score_diff<0 AND LU.type=2 THEN IFNULL(ABS(GP.score_diff),'0.00')*LU.user_lot_size*".$captain_point."
         WHEN LU.captain = '2' AND GP.score_diff>=0 AND LU.type=1 THEN IFNULL(GP.score_diff,'0.00')*LU.user_lot_size*".$vice_captain_point."
         WHEN LU.captain = '2' AND GP.score_diff>=0 AND LU.type=2  THEN IFNULL(-1*ABS(GP.score_diff),'0.00')*LU.user_lot_size*".$vice_captain_point."
         WHEN LU.captain = '2' AND GP.score_diff<0 AND LU.type=1  THEN IFNULL(-1*ABS(GP.score_diff),'0.00')*LU.user_lot_size*".$vice_captain_point."
         WHEN LU.captain = '2' AND GP.score_diff<0 AND LU.type=2  THEN IFNULL(ABS(GP.score_diff),'0.00')*LU.user_lot_size*".$vice_captain_point."
         WHEN LU.captain = '0' AND GP.score_diff>=0 AND LU.type=1 THEN IFNULL(GP.score_diff,'0.00')*LU.user_lot_size
         WHEN LU.captain = '0' AND GP.score_diff>=0 AND LU.type=2  THEN IFNULL(-1*ABS(GP.score_diff),'0.00')*LU.user_lot_size
         WHEN LU.captain = '0' AND GP.score_diff<0 AND LU.type=1  THEN IFNULL(-1*ABS(GP.score_diff),'0.00')*LU.user_lot_size
         WHEN LU.captain = '0' AND GP.score_diff<0 AND LU.type=2  THEN IFNULL(ABS(GP.score_diff),'0.00')*LU.user_lot_size
         ELSE IFNULL(GP.score_diff,'0.00')*LU.user_lot_size END
      )
      WHERE LM.collection_id=$collection_id
      ";
      
      /**
       *  LU.gain_loss=(
        CASE WHEN LU.captain = '1' THEN IFNULL(GP.score_diff,'0.00')*LU.user_lot_size*".$captain_point."
          WHEN LU.captain = '2'  THEN IFNULL(GP.score_diff,'0.00')*LU.user_lot_size*".$vice_captain_point."
          ELSE IFNULL(GP.score_diff,'0.00')*LU.user_lot_size END
      )
       * **/

      /**
      * Commented from line 907 as it was calculation cap vice captain point due to which game rank calculate wrong
      * (
                      CASE WHEN LU.captain = '1' THEN IFNULL(GP.score,'0.00')*LU.user_lot_size*".$captain_point."
                        WHEN LU.captain = '2'  THEN IFNULL(GP.score,'0.00')*LU.user_lot_size*".$vice_captain_point."
                        ELSE IFNULL(GP.score,'0.00')*LU.user_lot_size END
      )*/

      }

      return $sql;
    }
      
    function notify_node_collection_info($data) {
        $lmc_ids = $data['lmc_ids'];
        if(empty($lmc_ids)) {
          return true;
        }
        $collection_id = $data['collection_id'];
        $score_updated_date = $data['score_updated_date'];
        $this->db->select('LMC.lineup_master_contest_id, LMC.contest_id, LMC.total_score, LM.lineup_master_id, LM.user_id, LMC.game_rank, LMC.percent_change', false);
        $this->db->from(LINEUP_MASTER_CONTEST . ' LMC');
        $this->db->join(LINEUP_MASTER . ' LM', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER');
        $this->db->where_in("LMC.lineup_master_contest_id", $lmc_ids);
        $this->db->where("LM.collection_id", $collection_id);
        $contest_list = $this->db->get()->result_array();

        $collection_info = array();
        foreach ($contest_list as $contest) {

            $user_id = $contest['user_id'];
            $key = $user_id.'_'.$collection_id;
            if (!array_key_exists($key, $collection_info)) {
               $collection_info[$key] = array(
                'score_updated_date' => $score_updated_date,  
                'collection_id' => $collection_id,
                'contests' => array()  
               );
            }

            if (!array_key_exists($contest['contest_id'], $collection_info[$key]['contests'])) {                
              $collection_info[$key]['contests'][$contest['contest_id']] = array(
                  "contest_id" => $contest["contest_id"]                    
              );
            }
            $collection_info[$key]['contests'][$contest['contest_id']]["teams"][$contest['lineup_master_contest_id']] = array(
                "lineup_master_id" => $contest['lineup_master_id'],
                "lineup_master_contest_id" => $contest["lineup_master_contest_id"],
                "total_score" => $contest["total_score"] ? $contest["total_score"] : 0,
                "game_rank" => $contest["game_rank"] ? $contest["game_rank"] : 0,
                "percent_change" => $contest["percent_change"] ? $contest["percent_change"] : 0
            );
        }

        foreach($collection_info as $key => $fixture_contest) {
          $contests = $fixture_contest['contests'];
          array_multisort($contests, SORT_ASC);
          foreach($contests as &$contest) {
            ksort($contest['teams']);
            $contest['teams']  = array_values($contest['teams']);
          }
          $collection_info[$key]['contests'] = $contests;
        }

        if(!empty($collection_info)) {
          $this->load->library('Node');            
          $node = new node(array("route" => 'updateCollectionInfo', "postData" => array("collection" => $collection_info)));
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
    $prize_data = $this->db->select('C.currency_type,C.contest_id,C.is_tie_breaker,C.contest_unique_id,C.scheduled_date,C.entry_fee,C.size AS entries,C.prize_pool,C.prize_distibution_detail,C.minimum_size,C.site_rake,C.prize_type', FALSE)
                    ->from(CONTEST . ' AS C')
                    ->where('C.status', 2)
                    ->where('C.scheduled_date < ', format_date())
                    ->where('C.prize_type <> ', 4, FALSE)
                    ->where('C.prize_distibution_detail != ', 'null' )
                    ->group_by('C.contest_unique_id')
                    ->get()
                    ->result_array();
      //echo "<pre>";print_r($prize_data);die;
      if (!empty($prize_data))
      {
        $this->load->helper('queue');
        $server_name = get_server_host_name();
        foreach ($prize_data AS $key => $prize)
        {
          $content = array();
          if(isset($prize['is_tie_breaker']) && $prize['is_tie_breaker'] == "1"){
            $content['url'] = $server_name."/stock/cron/cron/contest_merchandise_distribution/".$prize['contest_id'];
          }else{
            $content['url'] = $server_name."/stock/cron/cron/contest_prize_distribution/".$prize['contest_id'];
          }
          add_data_in_queue($content,'stock_prize_distribution');
        }
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
    $prize_data = $this->db->select('C.contest_id,C.contest_unique_id,C.scheduled_date,C.entry_fee,C.size AS entries,C.prize_pool,C.prize_distibution_detail,C.minimum_size,C.site_rake,C.prize_type,MG.group_name,C.contest_name', FALSE)
                  ->from(CONTEST.' AS C')
                  ->join(MASTER_GROUP.' MG','C.group_id=MG.group_id')
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
      $prize = $prize_data;
      $contest_unique_id = $prize['contest_unique_id'];
      $contest_id = $prize['contest_id'];
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
      $sql = "SELECT LM.user_id,LM.collection_id,LMC.total_score,LMC.game_rank,LMC.lineup_master_id,LMC.lineup_master_contest_id 
                FROM ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC 
                INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER)."  AS LM ON LM.lineup_master_id = LMC.lineup_master_id
                WHERE LMC.contest_id = ".$prize['contest_id']." AND LMC.fee_refund=0 
                ORDER BY LMC.game_rank ASC
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
          $tmp_arr = array();
          $tmp_arr['user_id'] = $winner['user_id'];
          $tmp_arr['source'] = CONTEST_WON_SOURCE;
          $tmp_arr['source_id'] = $winner['lineup_master_contest_id'];
          $tmp_arr["reference_id"] = $contest_id;
          $tmp_arr['season_type'] = 1;
          $tmp_arr['status'] = 1;
          $tmp_arr['real_amount'] = $real_amount;
          $tmp_arr['bonus_amount'] = $bonus_amount;
          $tmp_arr['winning_amount'] = $winning_amount;
          $tmp_arr['points'] = ceil($points);
          $custom_data = array();
          $custom_data['contest_name'] = $prize_data['contest_name'];
          $custom_data['contest_type'] = $prize_data['group_name'];
          $custom_data['match_date'] = date('d-M-Y H:i:s',strtotime($prize_data['scheduled_date']));
          $custom_data['prize_data'] = $prize_obj_tmp;
          $tmp_arr['custom_data'] = json_encode($custom_data);
          //$tmp_arr['custom_data'] = json_encode($prize_obj_tmp);
          $tmp_arr['plateform'] = PLATEFORM_FANTASY;
          $result = $this->winning_deposit($tmp_arr);
          if($result){
            $is_success = 1;
          }

          //lineup master update is_winner 1
          $this->db_stock->where(array('lineup_master_contest_id' => $winner['lineup_master_contest_id']));
          $this->db_stock->update(LINEUP_MASTER_CONTEST, array('is_winner' => '1','prize_data' => json_encode($prize_obj_tmp)));
        }
      }

      if($is_success == 1){
        // Game table update is_prize_distributed 1
        $this->db_stock->where('contest_id', $contest_id);
        $this->db_stock->update(CONTEST, array('status' => '3', 'modified_date' => format_date(),'completed_date' => format_date()));

        // Deduct TDS If winning is greated than prize limit 
        if(IS_TDS_APPLICABLE)
        {
            $this->deduct_tds_from_winning($contest_id);
        }

        $content = array();
        $content['url'] = $server_name."/stock/cron/cron/update_scontest_report";
        add_data_in_queue($content,'report_cron');
        return true;

        //$this->add_cash_contest_referral_bonus($contest_id);
        //$this->add_every_cash_contest_referral_benefits($contest_id);
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
   * Function used for distribute contest prize
   * @param 
   * @return boolean
   */
  public function contest_prize_distribution($contest_id)
  {
    if(!$contest_id){
      return false;
    }
    ini_set('memory_limit', '-1');
    $prize_data = $this->db->select('C.contest_id,C.contest_unique_id,C.scheduled_date,C.entry_fee,C.contest_access_type,C.size AS entries,C.prize_pool,C.prize_distibution_detail,C.minimum_size,C.site_rake,C.prize_type,C.currency_type,C.contest_name,MG.group_name', FALSE)
                    ->from(CONTEST . ' AS C')
                    ->join(MASTER_GROUP.' MG','C.group_id=MG.group_id')
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
        $prize = $prize_data;
        $contest_unique_id = $prize['contest_unique_id'];
        $contest_id = $prize['contest_id'];
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
        $sql = "SELECT LM.user_id,LM.collection_id,LMC.total_score,LMC.game_rank,LMC.lineup_master_id,LMC.lineup_master_contest_id,LMC.contest_id 
                  FROM ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC 
                  INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER)."  AS LM ON LM.lineup_master_id = LMC.lineup_master_id
                  WHERE LMC.contest_id = ".$prize['contest_id']." AND LMC.fee_refund=0 
                  AND game_rank <= ".$winner_places."
                  ORDER BY LMC.game_rank ASC";
        $rs = $this->db->query($sql);
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
        //echo "<pre>";print_r($winners);
        $user_lmc_data = array();
        $user_txn_data = array();
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

            if(!empty($is_winner)){
              //user txn data
              $order_data = array();
              $order_data["order_unique_id"] = $this->_generate_order_unique_key();
              $order_data["user_id"]        = $value['user_id'];
              $order_data["source"]         = CONTEST_WON_SOURCE;
              $order_data["source_id"]      = $value['lineup_master_contest_id'];
              $order_data["reference_id"]   = $value['contest_id'];
              $order_data["season_type"]    = 1;
              $order_data["type"]           = 0;
              $order_data["status"]         = 0;
              $order_data["real_amount"]    = 0;
              $order_data["bonus_amount"]   = $bonus_amount;
              $order_data["winning_amount"] = $winning_amount;
              $order_data["points"] = ceil($points);

              $custom_data = array();
              $custom_data['contest_name'] = $prize_data['contest_name'];
              $custom_data['contest_type'] = $prize_data['group_name'];
              $custom_data['match_date'] = date('d-M-Y H:i:s',strtotime($prize_data['scheduled_date']));
              $custom_data['prize_data'] = $prize_obj_tmp;
              $order_data["custom_data"]  = json_encode($custom_data);
              // $order_data["custom_data"] = json_encode($prize_obj_tmp);
              $order_data["plateform"]      = PLATEFORM_FANTASY;
              $order_data["date_added"]     = format_date();
              $order_data["modified_date"]  = format_date();
              $user_txn_data[] = $order_data;
            }
          }
        }
        //echo "<pre>";print_r($user_lmc_data);
        //echo "<pre>";print_r($user_txn_data);die;
        
        if(!empty($user_lmc_data)){
          try
          {
            $is_updated = 0;
          
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
              $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = ".CONTEST_WON_SOURCE." AND type=0 AND status=0 AND reference_id='".$contest_id."' GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
                SET U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
                WHERE O.source = ".CONTEST_WON_SOURCE." AND O.type=0 AND O.status=0 AND O.reference_id='".$contest_id."' ";
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
                $this->db_stock->where('contest_id', $contest_id);
                $this->db_stock->update(CONTEST, array('status' => '3', 'modified_date' => format_date(),'completed_date' => format_date()));

                // Deduct TDS If winning is greated than prize limit 
                if(IS_TDS_APPLICABLE)
                {
                    $this->deduct_tds_from_winning($contest_id);
                }

                //$this->add_cash_contest_referral_bonus($contest_id);
                //$this->add_every_cash_contest_referral_benefits($contest_id);
                //add in queue here for host rake
                if (isset($prize_data['contest_access_type']) && $prize_data['contest_access_type'] == 1 && $prize_data['currency_type'] == 1)
                {
                  $this->load->helper('queue_helper');
                  // $data['action'] = 'process_host_rake';
                  $data['contest_id'] = $contest_id;
                  add_data_in_queue($data, 'stock_host_rake');
                }

                $content = array();
                $content['url'] = $server_name."/stock/cron/cron/update_scontest_report";
                add_data_in_queue($content,'report_cron');
                return true;
                
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

  public function deduct_tds_from_winning($contest_id)
    {
        if(!empty($contest_id))
        {
          
          $this->db_user->select("O.source_id, O.user_id, O.real_amount, O.bonus_amount, SUM(O.winning_amount) AS total_real_amount")
                          ->from(ORDER . " O");
          $this->db_user->where("O.reference_id",$contest_id);
          $this->db_user->where("O.source",CONTEST_WON_SOURCE);// 462: game won
          $this->db_user->where("O.status",1);// 1: completed
          $this->db_user->where("O.winning_amount > ", 0); // 1: completed
          $this->db_user->group_by("O.user_id");
          $this->db_user->having("total_real_amount >= ",TDS_APPLICABLE_ON);
          $this->db_user->order_by("total_real_amount","DESC");
          $sql = $this->db_user->get();
          //echo $this->db_user->last_query();
          $winning_data = $sql->result_array();
          if(!empty($winning_data))
          {
              foreach($winning_data as $winning)
              {
                  if($winning['total_real_amount'] > TDS_APPLICABLE_ON)
                  {
                      $tds_amount = ($winning['total_real_amount'] * TDS_PERCENT) / 100;
                      // Save txn for TDS amount on prize amount
                      if($tds_amount > 0)
                      {
                          $check_order = $this->db_user->select("order_id")
                                          ->from(ORDER)
                                          ->where("user_id", $winning["user_id"])
                                          ->where("source_id", $contest_id)
                                          ->where("source", CONTEST_TDS_SOURCE)
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
                                              'source' => CONTEST_TDS_SOURCE, // TDS Deduction
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
                }else if ($input_data["source"] != 8 && $input_data["source"] != CONTEST_TDS_SOURCE && $user_balance["real_amount"] < $amount) {
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

                if ($orderData["source"] == CONTEST_JOIN_SOURCE) 
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
            case CONTEST_JOIN_SOURCE:
            // TDS on winning
            case CONTEST_TDS_SOURCE:
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

        if($input_data["source"] !=CONTEST_JOIN_SOURCE && $input_data["source"] !=21)
        {   
            $subject = "Amount withdrawal";
            $input_data["reason"] = CRON_WITHDRAWL_NOTI1;
            $tmp["notification_destination"] = 7; //  Web, Push, Email
            $tmp["notification_type"] = 7; // 7-Withdraw
            if($input_data["source"] == CONTEST_TDS_SOURCE)
            {
                $tmp["notification_destination"] = 5;
                $tmp["notification_type"] = 555;
                $subject = "TDS Deducted";
            }

            $tmp["source_id"]                = $input_data["source_id"];
            $tmp["user_id"]                  = $input_data["user_id"];
            $tmp["to"]                       = $user_detail['email'];
            $tmp["user_name"]                = $user_detail['user_name'];
            $tmp["added_date"]               = format_date();
            $tmp["modified_date"]            = format_date();
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
        }
        $this->db_user->where('user_id', $user_id);
        $this->db_user->update(USER);
        
        return $this->db_user->affected_rows();  
    }

     /**
   * function used for send winning notification
   * @param void
   * @return boolean
   */
  public function collection_prize_distribute_notification() 
  {
    $result = $this->db->select("LM.user_id,C.contest_id,C.collection_id,C.contest_name,C.status, C.contest_unique_id, C.size, C.prize_pool, C.entry_fee, C.currency_type,LMC.is_winner,LMC.game_rank, LM.lineup_master_id, LMC.lineup_master_contest_id, C.prize_type,GROUP_CONCAT( DISTINCT LMC.lineup_master_contest_id) as lineup_master_contest_ids,GROUP_CONCAT(DISTINCT LMC.game_rank) as user_rank_list,GROUP_CONCAT( DISTINCT LM.team_name) as team_name,C.category_id,CS.stock_id,C.prize_distibution_detail,CM.name as collection_name,CM.scheduled_date,CM.end_date,CM.stock_type",false)
                  ->from(CONTEST . " C")
                  ->join(COLLECTION . " CM", "CM.collection_id = C.collection_id", "INNER")
                  ->join(COLLECTION_STOCK . " CS", "CM.collection_id = CS.collection_id", "INNER")
                  ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.contest_id = C.contest_id AND LMC.is_winner=1", "INNER")
                  ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
                  ->where("C.status", 3)
                  ->where("LMC.is_winner", 1)
                  ->where("C.is_win_notify", 0)
                  ->group_by("CM.collection_id,C.contest_id,LM.user_id")
                  ->order_by("LM.user_id,C.contest_id,C.collection_id")
                  ->get()
                  ->result_array();
    $match_data = array();
    $winning_contest = array();
    $notification_data = array();
    if (!empty($result)) 
    {   
      foreach ($result as $res) 
      {
        //for stock predict prize notification
        if($res['stock_type']==3 && !in_array($res['contest_id'],$winning_contest) ){
          $winning_contest[] = $res['contest_id'];
        }
        // print_r($winning_contest);die();
        $lineup_master_contest_ids = explode(',', $res['lineup_master_contest_ids']);
        $user_rank_list = explode(',', $res['user_rank_list']);
        $team_names = explode(',', $res['team_name']);
        $user_rank_list = array_unique($user_rank_list);

        $lineup_master_contest_ids = array_unique($lineup_master_contest_ids);
        $team_names = array_unique($team_names);
        $team_names = array_combine($lineup_master_contest_ids,$team_names);
        
        $team_ranks = array_combine($lineup_master_contest_ids,$user_rank_list);

        // echo "<pre>";print_r($team_ranks);die;
        $pre_query ="(SELECT user_id,GROUP_CONCAT(device_id ORDER BY keys_id DESC) as device_ids,GROUP_CONCAT(device_type ORDER BY keys_id DESC) as device_types ,keys_id,device_id,device_type FROM ".$this->db->dbprefix(ACTIVE_LOGIN)."  WHERE user_id =". $res['user_id']." AND device_id IS NOT NULL ORDER BY keys_id DESC)";

        $sql = $this->db_user->select("O.order_id,O.real_amount, O.bonus_amount, O.winning_amount,O.points, U.email,U.user_name,O.source_id,O.prize_image,O.custom_data,AL.device_id,AL.device_type,AL.device_ids,AL.device_types")
                ->from(ORDER . " O")
                ->join(USER . " U", "U.user_id = O.user_id", "INNER")
                ->join($pre_query.' AL','AL.user_id=U.user_id','LEFT')
                ->where("O.user_id", $res['user_id'])
                ->where("O.source", CONTEST_WON_SOURCE)
                ->where_in("O.source_id", $lineup_master_contest_ids)
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
            
            if(!isset($notification_data[$res['user_id']."_".$res['collection_id']]) 
                || empty($notification_data[$res['user_id']."_".$res['collection_id']]))
            {
              $user_temp_data = array();
              $user_temp_data['source_id'] = $res['collection_id'];
              $user_temp_data['user_id'] = $res['user_id'];
              $user_temp_data['email'] = $order_info[0]['email'];
              $user_temp_data['deviceIDS'] = $order_info[0]['device_ids'];
              $user_temp_data['device_types'] = $order_info[0]['device_types'];
              $user_temp_data['user_name'] = $order_info[0]['user_name'];
              $user_temp_data['collection_name'] = $res['collection_name'];
              $user_temp_data['contest_data'] = array();
              $user_temp_data['scheduled_date'] = $res['scheduled_date'];
              $user_temp_data['end_date'] = $res['end_date'];
              $user_temp_data['category_id'] = $res['category_id'];
              $user_temp_data['stock_type'] = $res['stock_type'];
              //added for multigame module check in mail 
              $notification_data[$res['user_id']."_".$res['collection_id']] = $user_temp_data;
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
              $contest_data['amount'] = $amount;
              $contest_data['custom_data'] = json_decode($value['custom_data'],TRUE);
              $contest_data['total_winner'] = $total_winner;              
              $notification_data[$res['user_id']."_".$res['collection_id']]['contest_data'][] = $contest_data;
            }
          }
        }
      }
      if(!empty($winning_contest)){
        //send notification to all joined users of winning contest of stock predict
        $this->notify_prize_distribute_to_alluser($winning_contest);
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
        $notify_data["stock_type"] = $notification['stock_type'];
        $notify_data['collection_name'] = $this->get_rendered_collection_name($notification);;
        $contest = array();
        $content['contest_data'] = $notification['contest_data'];
        $content['collection_name'] = $notify_data['collection_name'];
        $content['int_version'] = $this->app_config['int_version']['key_value'];
        $content['stock_type'] = $notification['stock_type'];
        $notify_data["content"] = json_encode($content);
        $this->load->model('notification/Notify_nosql_model');
        $this->Notify_nosql_model->send_notification($notify_data); 
    }
    return;
  }

  /**
   * function used for send winning notification to all users that have joined 
   * @param void
   * @return boolean
   */
  public function notify_prize_distribute_to_alluser($contest_ids)
  {
    $contest_joined_user = $this->db->select("LM.user_id,C.contest_name,C.contest_id",false)
        ->from(CONTEST . " C")
        ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.contest_id = C.contest_id", "INNER")
        ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
        ->where("C.status", 3)
        // ->where("C.is_win_notify", 0)
        ->where_in("C.contest_id",$contest_ids)
        ->group_by("C.contest_id,LM.user_id")
        ->order_by("LM.user_id,C.contest_id")
        ->get()->result_array();

    $match_data = array();
    // print_r($contest_joined_user);die();
    $notification_data = array();
    if (!empty($contest_joined_user)) 
    {
      $notification_type = 624; //prize distribution
      //Get notification message and subject
      $notify_desc = $this->get_notification_row($notification_type);
      $chunks = array_chunk($contest_joined_user, 200);
      $this->load->helper('queue_helper');
      foreach($chunks as $key=>$chunk)
      {
        $user_ids = array_unique(array_column($chunk, 'user_id'));
        $device_detail = $this->db_user->select("user_id,GROUP_CONCAT(device_id ORDER BY keys_id DESC) as device_ids,GROUP_CONCAT(device_type ORDER BY keys_id DESC) as device_types")
          ->from(ACTIVE_LOGIN)
          ->where_in('user_id',$user_ids)
          ->where('device_id is NOT NULL', NULL, FALSE)
          ->group_by('user_id')
          ->order_by('keys_id','DESC')
          ->get()->result_array();

        if(!empty($device_detail)){
          $device_detail =  array_column($device_detail,NULL,'user_id');

          $new_notification_data = array();
          foreach ($chunk as $ck => $users) {
            // print_r($users);die();
            $user_id = $users['user_id'];
            if(isset($device_detail[$user_id])) {
              $notifi_data = array();
              $device_ids  = explode(',',$device_detail[$user_id]['device_ids']);
              //get only first 2 ids
              $unique_device_ids = array_slice($device_ids, 0,1);
              $device_types = explode(',',$device_detail[$user_id]['device_types']);
              //get only first 2 device type
              $u_device_types = array_slice($device_types, 0,1);              
              $notifi_data['device_details'] = array();
              foreach($unique_device_ids as $d_key => $d_value) 
              {
                if(!empty($d_value) && !in_array($d_value,[1,2]))
                {
                  $notifi_data['device_details'][] =array(
                    'device_id' => $d_value,
                    'device_type' => $u_device_types[$d_key]) ;
                }
              }
                          
              $notifi_data["notification_type"] = $users["notification_type"] = $notification_type;
              $notifi_data["stock_type"] = $users["stock_type"] = 3; //stock predict
              $notify_desc['en_subject'] = str_replace('{{contest_name}}',$users['contest_name'],$notify_desc['en_subject']);
              $notifi_data["notification_data"] = array(
                  "en_subject" => $notify_desc['en_subject'],
                  "message"    => $notify_desc['message']."🥇",
                ); 

              $notifi_data["content"] = array(
                    "template_data" =>$users
                  );
              // print_r($notifi_data);die();
              $new_notification_data[] =  $notifi_data;
            }else{
              //echo 'devices is not found';
            }
          }
          // print_r($new_notification_data);die();
          $data = ["push_notification_data" => $new_notification_data];            
          add_data_in_queue($data, 'stock_auto_push');
        }
      }
    }else{
      echo "No user to notify.\n ";
    }
    return;
  }
  
  private function get_rendered_collection_name($data)
  {
  
      $collection_name = ucfirst($data['collection_name']);
      $date  = date('d-M-Y',strtotime($data['scheduled_date']));
      $end_date  = date('d-M-Y',strtotime($data['end_date']));
        if($data['category_id'] =='1')
        {
          $collection_name.='-'.$date;
        }
        elseif($data['category_id'] =='2')
        {
          $collection_name.='-('.$date.'-'.$end_date.')';
        }
        else{
          $month = date('M',strtotime($data['scheduled_date']));
          $collection_name.='-('.$month.')';
        }

       if($data['stock_type'] == 3){
          
          $date      = date('d-M h:i A',strtotime('+330 minutes',strtotime($data['scheduled_date'])) );
          $end_date  = date('h:i A',strtotime('+330 minutes',strtotime($data['end_date'])));
          $collection_name = $date.' - '.$end_date;
         
       }

       if($data['stock_type'] == 4){
            
            $date      = date('d-M h:i A',strtotime('+330 minutes',strtotime($data['scheduled_date'])) );
            $end_date  = date('d-M h:i A',strtotime('+330 minutes',strtotime($data['end_date'])));
            $collection_name = $date.' - '.$end_date;
           
         }
  
    return $collection_name;
  }

  public function move_completed_collection_team()
  {
    $current_date = format_date();
    $this->db->select("CM.collection_id,CM.status,CM.is_lineup_processed", FALSE);
    $this->db->from(COLLECTION.' CM');
    $this->db->where('CM.is_lineup_processed',"1");
    $this->db->where('CM.status',"1");
    $this->db->where('CM.stock_type',"1");
    $this->db->where('CM.scheduled_date < ',$current_date);
    $this->db->order_by('CM.collection_id', "ASC");
    $this->db->limit(5);
    $collection_list = $this->db->get()->result_array();
    //echo "<pre>";print_r($collection_list);die;
    foreach($collection_list as $collection){
     
        $this->db->select("LM.lineup_master_id,LM.user_id,L.stock_id,L.score,L.captain,L.type", FALSE);
        $this->db->from(LINEUP_MASTER.' LM');
        $this->db->join(LINEUP.' as L', 'LM.lineup_master_id = L.lineup_master_id', "INNER");
        $this->db->where('LM.collection_id',$collection['collection_id']);
        $this->db->order_by('LM.lineup_master_id',"ASC");
        $this->db->order_by('L.lineup_id',"ASC");
        $team_list = $this->db->get()->result_array();
        //echo "<pre>";print_r($team_list);die;
        $team_data = array();
        foreach($team_list as $row){
          if(isset($team_data[$row['lineup_master_id']])){
            $tm_arr = $team_data[$row['lineup_master_id']];
            $tm_arr['team_data'] = json_decode($tm_arr['team_data'],TRUE);
          }else{
            $tm_arr = array();
            $tm_arr['collection_id'] = $collection['collection_id'];
            $tm_arr['lineup_master_id'] = $row['lineup_master_id'];
            $tm_arr['user_id'] = $row['user_id'];
            $tm_arr['team_data'] = array("c_id"=>"","b"=>array(),"s" => array());
            $tm_arr['added_date'] = $current_date;
          }
          if($row['captain'] == "1"){
            $tm_arr['team_data']['c_id'] = $row['stock_id'];
          }

          if($row['captain'] == "2"){
            $tm_arr['team_data']['vc_id'] = $row['stock_id'];
          }

          if($row['type'] =='1')
          {
            $tm_arr['team_data']['b'][$row['stock_id']]  = $row['score'];
          }
          else
          {
            $tm_arr['team_data']['s'][$row['stock_id']] = $row['score'];
          }
          $tm_arr['team_data'] = json_encode($tm_arr['team_data']);
          $team_data[$row['lineup_master_id']] = $tm_arr;
        }

        if(!empty($team_data)){
          $team_data = array_values($team_data);
          $this->replace_into_batch(COMPLETED_TEAM,$team_data);

          $this->set_auto_increment_key(COMPLETED_TEAM,'team_id');
        }

        //update status
        $this->db->where('collection_id',$collection['collection_id']);
        $this->db->where('status',"1");
        $this->db->update(COLLECTION, array('is_lineup_processed'=>"2"));
        //echo "<pre>";print_r($team_data);die;
      
    }
  }

  public function revert_completed_team($collection_id)
  {
    $this->db->select("LM.lineup_master_id,LM.user_id,L.stock_id,L.score,L.captain", FALSE);
    $this->db->from(LINEUP_MASTER.' LM');
    $this->db->join(COMPLETED_TEAM.' as CT', 'CT.lineup_master_id = L.lineup_master_id', "INNER");
    $this->db->where('LM.collection_id',$collection_id);
    $this->db->order_by('LM.lineup_master_id',"ASC");
    $this->db->order_by('L.lineup_id',"ASC");
    $team_list = $this->db->get()->result_array();

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
          //$game_data['total_system_user'] = 0;
          $prize_details = json_decode($contest_data['base_prize_details']);
          $game_data['prize_pool'] = $prize_details->prize_pool;
          $game_data['prize_distibution_detail'] = $prize_details->prize_distibution_detail;
         // $game_data['salary_cap'] = SALARY_CAP;
          $game_data['added_date'] = format_date();
          $game_data['modified_date'] = format_date();
          unset($game_data["contest_id"]);
          unset($game_data["is_cancel"]);
          $this->db->insert(CONTEST, $game_data);
      }
      return true;
  }

  public function generate_live_contest_pdf($contest_id){
    if(!$contest_id){
      return false;
    }
    $contest_info = $this->db->select('C.contest_id,C.contest_unique_id ,C.collection_id,CM.name as collection_name,C.contest_name,C.prize_pool,C.prize_distibution_detail,C.entry_fee,C.total_user_joined,C.scheduled_date,CM.published_date,CM.end_date,CM.stock_type', FALSE)
        ->from(CONTEST . ' as C')
        ->join(COLLECTION . " as CM", "C.collection_id = CM.collection_id", "INNER")
        ->where("C.contest_id",$contest_id)
        ->where("C.is_pdf_generated","1")
        ->get()
        ->row_array();
    if(empty($contest_info)){
        return false;
    }
    
    $limit = 100;
    $collection_id = $contest_info['collection_id'];
   
    $collection_stock_cache_key = "st_collection_stocks_".$collection_id;
    $stocks_list = $this->get_cache_data($collection_stock_cache_key);
    if(!$stocks_list)
    {
      $post_data['collection_id'] = $collection_id;
      $post_data['published_date'] = $contest_info['published_date'];
      $post_data['scheduled_date'] = $contest_info['scheduled_date'];
      $post_data['end_date'] = $contest_info['end_date'];
      $stocks_list = $this->get_all_stocks($post_data);
      $this->set_cache_data($collection_stock_cache_key,$stocks_list,REDIS_1_MINUTE);
    }
    $stock_list_array = array_column($stocks_list,NULL,'stock_id');
    //echo "<pre>";print_r($player_list_array);die;

    $this->db->select("LM.team_name,LM.user_name,LM.user_id,LM.lineup_master_id,LMC.lineup_master_contest_id,LMC.contest_id")
          ->from(LINEUP_MASTER_CONTEST . " LMC")
          ->join(LINEUP_MASTER . " LM", "LMC.lineup_master_id = LM.lineup_master_id", "INNER")
          ->where("LMC.fee_refund", "0")
          ->where("LMC.contest_id", $contest_info["contest_id"])
          ->order_by("LMC.game_rank", "ASC", FALSE)
          ->order_by("LMC.total_score", "DESC", FALSE);
    $user_rank = $this->db->get()->result_array();
    $user_team_ids = array_column($user_rank, "lineup_master_id");

    $this->db->select("L.lineup_master_id,L.stock_id,L.type,L.captain as player_role")
          ->from(LINEUP . " AS L")
          ->join(LINEUP_MASTER_CONTEST . ' LMC', 'LMC.lineup_master_id = L.lineup_master_id', 'INNER')
          ->where("LMC.fee_refund", "0")
          ->where("LMC.contest_id", $contest_id);
    if(isset($user_team_ids) && !empty($user_team_ids)){
        $this->db->where_in("L.lineup_master_id",$user_team_ids);
    }
    $user_teams = $this->db->get()->result_array();
    $user_team_data = array();
    foreach($user_teams as $stock){
      $stock_name = "";
      if(isset($stock_list_array[$stock['stock_id']])){
        $stock_name = $stock_list_array[$stock['stock_id']]['stock_name'];
      }
      $stock['stock_name'] = $stock_name;
      $user_team_data[$stock['lineup_master_id']][] = $stock;
    }

    $final_data = array();
    $column = 0;
    foreach($user_rank as $team)
    {
      if(!empty($user_team_data[$team['lineup_master_id']])){
        $team_stocks = $user_team_data[$team['lineup_master_id']];
        $temp_array = array();
        $temp_array[0] = $team["user_name"];
        $temp_array[1] = $team["team_name"];
        $temp_array[2] = "";
        $temp_array[3] = "";
        $pl_start_key = 2;
        if(CAPTAIN_POINT > 0){
          $pl_start_key+= 1;
        }

        if(VICE_CAPTAIN_POINT > 0){
          $pl_start_key+= 1;
        }
       
        $column = 0;
        foreach($team_stocks as $key => $stock)
        {
          if(CAPTAIN_POINT > 0 && $stock["player_role"]==1)
          {
            $temp_array[2] = $this->get_with_buy_sell($stock["stock_name"],$stock["type"]); //ucfirst($stock["stock_name"]);
          }
          else if(VICE_CAPTAIN_POINT > 0 && $stock["player_role"]==2)
          {
            $temp_array[3] = $this->get_with_buy_sell($stock["stock_name"],$stock["type"]); //ucfirst($stock["stock_name"]);
          }
          elseif($stock["type"] == 1 || $stock["type"] ==2){ 
            $temp_array[($key+$pl_start_key)] = $this->get_with_buy_sell($stock["stock_name"],$stock["type"]);//ucfirst($stock["stock_name"]);
          }
          else{
               $temp_array[($key+$pl_start_key)] = ucfirst($stock["stock_name"]); //For Stock type 3 stock predict
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
    $pdf_data = array();
    $pdf_data['contest_info'] = $contest_info;
    $pdf_data['team_list'] = $final_data;

    $config_data =  $this->get_stock_type_config($contest_info['stock_type']);
    $tc = 10;
    
    if($contest_info['stock_type'] == 3 ){
       $config_data = json_decode($config_data['config_data'],TRUE);
    }else{

      $config_data = json_decode($config_data,TRUE);
    }

    if(isset($config_data['min']))
    {
      $tc = $config_data['min'];
    }
    else
    {
      $tc = $config_data['tc'];
    }
    $pdf_data['tc'] = $tc;
    generate_pdf($contest_pdf_name,$pdf_data);

    
    $filePath = "stock_lineup/".$contest_id.".pdf";
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
    $this->db->where('contest_id', $contest_id);
    $this->db->update(CONTEST, $contest_update);
    return true;
  }

  private function normal_stock_format($row)
  {
     return $row['score'];
  }

  private function equity_stock_format($row)
  {
     return array('score' => $row['score'],"user_lot_size" => $row['user_lot_size']);
  }


  public function move_completed_collection_team_equity()
  {
    $current_date = format_date();
    $this->db->select("CM.collection_id,CM.status,CM.is_lineup_processed,CM.stock_type", FALSE);
    $this->db->from(COLLECTION.' CM');
    $this->db->where('CM.is_lineup_processed',"1");
    $this->db->where('CM.status',"1");
    $this->db->where('CM.stock_type',"2");
    $this->db->where('CM.scheduled_date < ',$current_date);
    $this->db->order_by('CM.collection_id', "ASC");
    $this->db->limit(5);
    $collection_list = $this->db->get()->result_array();
    //echo "<pre>";print_r($collection_list);die;
    foreach($collection_list as $collection){
     
        $this->db->select("LM.lineup_master_id,LM.user_id,L.stock_id,L.score,L.captain,L.type,L.user_lot_size", FALSE);
        $this->db->from(LINEUP_MASTER.' LM');
        $this->db->join(LINEUP.' as L', 'LM.lineup_master_id = L.lineup_master_id', "INNER");
        $this->db->where('LM.collection_id',$collection['collection_id']);
        $this->db->order_by('LM.lineup_master_id',"ASC");
        $this->db->order_by('L.lineup_id',"ASC");
        $team_list = $this->db->get()->result_array();
        //echo "<pre>";print_r($team_list);die;
        $team_data = array();
        foreach($team_list as $row){
          if(isset($team_data[$row['lineup_master_id']])){
            $tm_arr = $team_data[$row['lineup_master_id']];
            $tm_arr['team_data'] = json_decode($tm_arr['team_data'],TRUE);
          }else{
            $tm_arr = array();
            $tm_arr['collection_id'] = $collection['collection_id'];
            $tm_arr['lineup_master_id'] = $row['lineup_master_id'];
            $tm_arr['user_id'] = $row['user_id'];
            $tm_arr['team_data'] = array("c_id"=>"","b"=>array(),"s" => array());
            $tm_arr['added_date'] = $current_date;
          }
          if($row['captain'] == "1"){
            $tm_arr['team_data']['c_id'] = $row['stock_id'];
          }

          if($row['captain'] == "2"){
            $tm_arr['team_data']['vc_id'] = $row['stock_id'];
          }

          //get stock_type function
          $func = $this->move_completed_lineup_stock[$collection['stock_type']];

          if($row['type'] =='1')
          {
            $tm_arr['team_data']['b'][$row['stock_id']]  = $this->$func($row);
          }
          else
          {
            $tm_arr['team_data']['s'][$row['stock_id']] = $this->$func($row);
          }
          $tm_arr['team_data'] = json_encode($tm_arr['team_data']);
          $team_data[$row['lineup_master_id']] = $tm_arr;
        }

        if(!empty($team_data)){
          $team_data = array_values($team_data);
          $this->replace_into_batch(COMPLETED_TEAM,$team_data);

          $this->set_auto_increment_key(COMPLETED_TEAM,'team_id');
        }

        //update status
        $this->db->where('collection_id',$collection['collection_id']);
        $this->db->where('status',"1");
        $this->db->update(COLLECTION, array('is_lineup_processed'=>"2"));
        //echo "<pre>";print_r($team_data);die;
      
    }
  }

function unit_testing()
{
    $this->load->library('unit_test');
    $func = $this->move_completed_lineup_stock[2];
    $test = $this->$func(array('score' => 10,"user_lot_size" => 5));
    $test_name = 'check hash map';
    echo $this->unit->run($test, array('score' => 10,"user_lot_size" => 5), $test_name);
}

  private function get_with_buy_sell($name,$type)
  {
    if($type=='1')
    {
      $name = ucfirst($name).'(B)';
    }
    else
    {
      $name = ucfirst($name).'(S)';
    }
    return $name;
  }

  public function get_all_stocks($post_data)
	{
        /**
         * collection season stock name, lot size
         * stock history  stock history  last price(x) of publish date of collection
         * stocktable  => last price(y) as current price, 
         * x-y  = example +20.22
         * flag for wish list is_wish_list
         * 
         * market table=> give master configuration 
         * ***/

		$collection_id = $post_data['collection_id'];
		
		$published_date=date('Y-m-d',strtotime($post_data['published_date'])) ;
		$end_date=date('Y-m-d',strtotime($post_data['end_date'])) ;
    $scheduled_date=date('Y-m-d',strtotime($post_data['scheduled_date'])) ;
		$this->db->select('CS.stock_id,CS.stock_name,CS.lot_size,IFNULL(SH1.close_price,0) as last_price ,S.last_price as current_price,0 as is_wish,(IFNULL(S.last_price - SH1.close_price,0)) as price_diff,IFNULL(S.logo,"") as logo,S.display_name,IFNULL(SH1.close_price,0) as closing_rate,IFNULL(SH2.close_price,0) as result_rate,IFNULL(SH1.close_price,0) as joining_rate', FALSE)
			->from(COLLECTION_STOCK . " AS CS")
			->join(COLLECTION.' C','C.collection_id = CS.collection_id','INNER')
	        ->join(STOCK.' S', "S.stock_id = CS.stock_id", 'LEFT')
			->join(STOCK_HISTORY.' SH1',"SH1.stock_id=CS.stock_id AND SH1.schedule_date='{$published_date}'",'LEFT')
			->join(STOCK_HISTORY.' SH2',"SH2.stock_id=CS.stock_id AND SH2.schedule_date='{$end_date}'",'LEFT');
	   
            $date ='';//last working day before of publish date in collection
        $this->db->where("CS.collection_id",$collection_id)
                ->where("S.status",1)
		        ->group_by('CS.stock_id')
		        ->order_by('CS.stock_name','ASC');

		$sql = $this->db->get();
		$result	= $sql->result_array();
        //echo $this->db->last_query();die('dsds');
		return $result;
	}


    /**
     * Used to update stock historical data day wise
     */
    public function stock_historical_data_day_wise($data=array()) {
      $stock_id = isset($data['stock_id']) ? $data['stock_id'] : 0;
      $interval = isset($data['interval']) ? $data['interval'] : '1day';
      $start_date = isset($data['from_date']) ? $data['from_date'] : '';
      $end_date = isset($data['to_date']) ? $data['to_date'] : '';
      $type = isset($data['type']) ? $data['type'] : 1;
            
      if(empty($start_date)) {
        $start_date = date('Y-m-d 09:14:00',strtotime(format_date().' 1 year ago'));
      }  
      if(empty($end_date)) {
        $end_date = format_date('today', 'Y-m-d 15:40:00');
      }

      if(empty($interval)) {
        $interval = '1year';
      }  

      $stock_list = $this->get_stocks_with_close_price_status();
      if(!empty($stock_list)) {
        if(!empty($stock_id)) {
          $stock = array_column($stock_list, NULL, 'stock_id');
          $stock_list = array();
          $stock_list[] = $stock[$stock_id];          
        } 

        $stock_list = array_chunk($stock_list, 25); //Limited to 120 symbols per request.
        try {
          $this->load->library('Twelvedata');
          $twelve_data = new Twelvedata();
          foreach($stock_list as $results) {         
            $stocks = array_column($results, NULL, 'trading_symbol');   
              
            $results = array_column($results, 'trading_symbol');
            $current_date = format_date();
            if(!empty($results)) {
              $request_param['symbol'] = implode(',',$results);
              $request_param['interval'] = $interval;
              $request_param['exchange'] = 'NSE';
              $request_param['start_date'] = $start_date;
              $request_param['end_date'] = $end_date;
              
              $history_data = $twelve_data->query('/time_series', $request_param);
              /* $history_data = array('INFY' => array(
                                            'meta' => array('symbol' => 'INFY', 'interval' => '1day'),
                                            'values' => array(0 => array('datetime' => '2021-08-10', 'open' => 1668.00000, 'high' => 1680.00000, 'low' => 1661.05005, 'close' => 1677.25000, 'volume' => 6844760)),
                                            'status' => 'ok'
                                              ),      
                                      'TCS' => array(
                                                'meta' => array('symbol' => 'TCS', 'interval' => '1day'),
                                                'values' => array(0 => array('datetime' => '2021-08-10', 'open' => 1668.00000, 'high' => 1680.00000, 'low' => 1661.05005, 'close' => 1677.25000, 'volume' => 6844760)),
                                                'status' => 'ok'
                                          ),
                                        );
                                        */
              //print_r($history_data);die;
              $historical_data = array();
              foreach($history_data as $key => $history) {
                $status = isset($history['status']) ? $history['status'] : '';
                if($status == 'ok') {
                  $stock_data = $stocks[$key];
                  $stock_id = $stock_data['stock_id'];
                  $stock_status = $stock_data['status'];
                  $close_price = $stock_data['close_price'];
                  
                  $values = isset($history['values']) ? $history['values'] : array();
                  foreach($values as $k => $value) {
                      //print_r($history);die;
                    $update_data = array();
                    $history_date = $value['datetime'];  
                    if($type == 1) {  
                      $history_date = date('Y-m-d',strtotime($history_date)); //// need to calculate it based on return date from API        
                      
                      $update_data['open_price']  = $value['open'];
                      $update_data['high_price']  = $value['high'];
                      $update_data['low_price']   = $value['low'];
                      $update_data['close_price'] = $close_price;
                      if(empty($stock_status)) {
                        $update_data['close_price'] = $value['close'];
                      }                      
                      $update_data['added_date']  = $current_date;
                      $update_data['schedule_date'] = $history_date; 
                      $update_data['volume'] = $value['volume'];
                      $update_data['stock_id'] = $stock_id;
                      $historical_data[] = $update_data;
                    } else if($type == 2) {
                      $history_date = date('Y-m-d H:i:s',strtotime($history_date)); //// need to calculate it based on return date from API        
                      
                      $update_data['close_price'] = $value['close'];
                      $update_data['added_date']  = $current_date;
                      $update_data['schedule_date'] = $history_date; 
                      $update_data['stock_id'] = $stock_id;
                      $historical_data[] = $update_data;
                    }
                  }
                } else {
                  log_message('error', 'Time series Day wise Data error for ' . $key);
                }         
              } 
              if($historical_data) {
                if($type == 1) {  
                  $this->replace_into_batch(STOCK_HISTORY,$historical_data);
                } else if($type == 2) {
                  $this->replace_into_batch(STOCK_HISTORY_DETAILS,$historical_data);
                }
              }             
            }       
          }
        } catch(Exception $e){
          $response = $e->getMessage();
          log_message('error', 'Time series Day wise data error: '.$response);
        }
      }
    }


    /**
     * Used to update stock historical data minute wise
     */
    public function stock_historical_data_minute_wise($data=array()) {
      $stock_id = isset($data['stock_id']) ? $data['stock_id'] : 0;
      $interval = isset($data['interval']) ? $data['interval'] : '5min';
            
      $stock_list = $this->get_nifty_fifty_stocks();
      if(!empty($stock_list)) {
        if(!empty($stock_id)) {
          $stock = array_column($stock_list, NULL, 'stock_id');
          $stock_list = array();
          $stock_list[] = $stock[$stock_id];          
        } 

        $stock_list = array_chunk($stock_list, 25); //Limited to 120 symbols per request.       
        try {
          $this->load->library('Twelvedata');
          $twelve_data = new Twelvedata();
          //sleep(5); //to avoid api hit on same time due to server dealy
          foreach($stock_list as $results) {         
            $stocks = array_column($results, 'stock_id', 'trading_symbol');        
            $results = array_column($results, 'trading_symbol');
            $current_date = format_date();
            if(!empty($results)) {
              $request_param['symbol'] = implode(',',$results);
              $request_param['interval'] = $interval;
              $request_param['exchange'] = 'NSE';
              
              $history_data = $twelve_data->query('/quote', $request_param);
              //print_r($history_data);die;
              $historical_data = array();
              foreach($history_data as $key => $value) {              
                  $stock_id = $stocks[$key];
                  //print_r($history);die;
                  $history_data = array();
                  $history_date = $value['datetime'];    
                  $history_date = date('Y-m-d H:i:s',strtotime($history_date)); //// need to calculate it based on return date from API        
                                    
                  $history_data['close_price'] = $value['close'];
                  $history_data['added_date'] = $current_date;
                  $history_data['schedule_date'] = $history_date; 
                  $history_data['stock_id'] = $stock_id;
                  $historical_data[] = $history_data; 
              }
              if($historical_data) {
                $this->replace_into_batch(STOCK_HISTORY_DETAILS,$historical_data);
              }            
            }        
          }
        } catch(Exception $e){
          $response = $e->getMessage();
          log_message('error', 'Quote minute wise data error: '.$response);
        } 
      }
    }

    /**
     * Used to update stock latest quote
     */
    public function update_stock_latest_quote($data=array()) {
      $stock_id = isset($data['stock_id']) ? $data['stock_id'] : '';

      $stock_list = $this->get_nifty_fifty_stocks();
      if(!empty($stock_list)) {
        if(!empty($stock_id)) {
          $stock = array_column($stock_list, NULL, 'stock_id');
          $stock_list = array();
          $stock_list[] = $stock[$stock_id];          
        } 

        $stock_list = array_chunk($stock_list, 25); //Limited to 120 symbols per request.
        try {
          //log_message('error', 'Stock Historical Data latest quote Executed at: '.format_date().' Count => '.count($stock_list));
          $this->load->library('Twelvedata');
          $twelve_data = new Twelvedata();
          foreach($stock_list as $results) {
            $stocks = array_column($results, 'stock_id', 'trading_symbol');        
            $results = array_column($results, 'trading_symbol');
            $current_date = format_date();
            if(!empty($results)) {
              $request_param['symbol'] = implode(',',$results);
              $request_param['exchange'] = 'NSE';              
              $history_data = $twelve_data->query('/quote', $request_param);
                
              //print_r($history_data);die;
              $historical_data = array();
              foreach($history_data as $key => $value) {              
                  $stock_id = $stocks[$key];
                      //print_r($history);die;
                  $update_data = array();
                  $history_data = array();
                  $history_date = $value['datetime'];    
                  $history_date = date('Y-m-d H:i:s',strtotime($history_date)); //// need to calculate it based on return date from API        
                  
                  $update_data['open_price']  = $value['open'];
                  $update_data['high_price']  = $value['high'];
                  $update_data['low_price']   = $value['low'];
                  $update_data['last_price']  = $value['close'];
                  $update_data['close_price'] = $value['previous_close'];
                  $update_data['modified_date'] = $current_date;
                  $update_data['stock_id'] = $stock_id;
                  $historical_data[] = $update_data; 
                  //$this->db->update(STOCK,$update_data,array('stock_id'=>$stock_id));
              }
              if($historical_data) {
                $this->replace_into_batch(STOCK,$historical_data);
              }              
            } 
          }
        } catch(Exception $e){
          $response = $e->getMessage();
          log_message('error', 'Quote latest data error: '.$response);
        } 
      }      
    }

    /**
     * Used to get all stock list from NSE
     */
    public function stock_list() {
      $instruments = array();
      try {
        $this->load->library('Twelvedata');
        $twelve_data = new Twelvedata();

        $request_param['type'] = 'EQUITY';
        $request_param['exchange'] = 'NSE';
        $instruments = $twelve_data->query('/stocks', $request_param);
      } catch(Exception $e){
        $response = $e->getMessage();
        log_message('error', 'Stock List ERROR: '.$response);
      } 

      $final_instruments = array();
      if(count($instruments) > 0) {
        $status = isset($instruments['status']) ? $instruments['status'] : '';
        if($status == 'ok') {
          $instruments = isset($instruments['data']) ? $instruments['data'] : array();
          $current_date = format_date();
          foreach($instruments as $key => $value) {    
            $symbol = trim($value['symbol']);
            if(!empty($symbol)) {     
              $final_instruments[]= array(
                  'market_id' => $this->market_id,
                  'name' => trim($value['name']),
                  'trading_symbol' => trim($value['symbol']),
                  'added_date' => $current_date
              );
            }
          }
        }
        if($final_instruments) {
          $this->replace_into_batch(MASTER_STOCK,$final_instruments);
        }
      }
    }


    function get_nifty_fifty_stocks() {
      $nfstock_cache_key = "st_nfstock";
      $stock_list = $this->get_cache_data($nfstock_cache_key);
      if(empty($stock_list)) {
        $this->db->select('s.stock_id, s.trading_symbol');
        $this->db->from(STOCK.' s');
        $this->db->order_by('s.stock_id', 'ASC');
        $query = $this->db->get();
        if($query->num_rows() > 0) {
          $stock_list = $query->result_array(); 
          $this->set_cache_data($nfstock_cache_key, $stock_list, REDIS_24_HOUR);
        }
      }
      return $stock_list;
    }

    function process_remaining_cap($collection_id)
    {

      $one_collection =$this->get_single_row('collection_id,scheduled_date,published_date,end_date,stock_type',COLLECTION,array('collection_id' => $collection_id));

      $scheduled_date = date('Y-m-d',strtotime($one_collection['scheduled_date']));
      $published_date = date('Y-m-d',strtotime($one_collection['published_date']));
      $end_date =  date('Y-m-d',strtotime($one_collection['end_date']));

      $salary_cap =  $this->get_stock_price_cap('salary_cap',2);

      $current_date = format_date();

      $sql="UPDATE ".$this->db->dbprefix(LINEUP_MASTER)."  AS LM 
        INNER JOIN
         (SELECT LU.lineup_master_id,LU.stock_id, $salary_cap-SUM(IFNULL(GP.score,0)*IFNULL(LU.user_lot_size,0)) as remaining_cap  from vi_lineup LU
         INNER JOIN 
         (SELECT CS.stock_id,                                    
                    (
                      (CASE
                          WHEN C.scheduled_date <= '".$current_date."' AND C.end_date >= '".$current_date."' THEN SH1.close_price
                         ELSE S.last_price END)
                    ) as score                              
  
                    FROM ".$this->db->dbprefix(COLLECTION_STOCK )." AS CS
                    INNER JOIN ".$this->db->dbprefix(COLLECTION)." C ON CS.collection_id=C.collection_id
                    INNER JOIN ".$this->db->dbprefix(STOCK)." S ON S.stock_id=CS.stock_id
                    INNER JOIN ".$this->db->dbprefix(STOCK_HISTORY)." SH1
                    ON CS.stock_id=SH1.stock_id AND SH1.schedule_date ='".$published_date."' 
                    WHERE 
                    C.collection_id=$collection_id
                    GROUP BY CS.stock_id)
                    AS GP ON GP.stock_id = LU.stock_id
                    group by LU.lineup_master_id) as RC
        SET LM.remaining_cap = RC.remaining_cap
        WHERE LM.collection_id=$collection_id AND LM.lineup_master_id=RC.lineup_master_id";

        $this->db->query($sql);

        return true;
    }

    function get_stocks_with_close_price_status() {      
        $schedule_date = format_date('today', 'Y-m-d');
        $this->db->select('s.stock_id, s.trading_symbol, IFNULL(sh.status,0) as status, IFNULL(sh.close_price,0) as close_price');
        $this->db->from(STOCK.' s');
        $this->db->join(STOCK_HISTORY.' sh','sh.stock_id=s.stock_id AND sh.schedule_date = "'.$schedule_date.'"', 'LEFT');
        $this->db->order_by('s.stock_id', 'ASC');
        $query = $this->db->get();
        $stock_list = $query->result_array(); 
        return $stock_list;
    }

    public function get_notification_row($notification_type)
    {
      $result = $this->db_user->select('en_subject,message')->from(NOTIFICATION_DESCRIPTION)
      ->where('notification_type',$notification_type)->get()->row_array();
      return $result;
    }

    public function send_stock_push($data)
    {
      $for_all_users = array(560,561,562,563,564,565,566,567,568,623);// those notification types , in which we will send to all users.

      // selecting only those users who joined contest for 15 min before push else we will select all uses
      if(in_array($data['notification_type'],$for_all_users))
      {
        $user_detail = $this->db_user->select("U.user_id, U.user_name")
      ->from(USER .' U')
      ->where("U.status",'1')
      ->where("U.is_systemuser",0)
      ->get()->result_array();
      }
      else
      {
        //here I will apply the user select condition in any special condition .
      }

      if(!empty($user_detail))
      {
        $user_ids = array_unique(array_column($user_detail,'user_id'));
        $user_names = array_unique(array_column($user_detail,'user_name','user_id'));
      }
      

      $chunks = array_chunk($user_ids, 100);
      foreach($chunks as $key=>$chunk)
      {
        $new_notification_data = array();
        $pre_query ="(SELECT user_id,GROUP_CONCAT(device_id ORDER BY keys_id DESC) as device_ids,GROUP_CONCAT(device_type ORDER BY keys_id DESC) as device_types ,keys_id,device_id,device_type FROM ".$this->db->dbprefix(ACTIVE_LOGIN)."  WHERE device_id IS NOT NULL GROUP BY user_id ORDER BY keys_id DESC)";
        $device_detail = $this->db_user->select('U.user_id,U.email,U.phone_no,U.phone_code,U.user_name,AL.device_id,AL.device_type,AL.device_ids,AL.device_types')
          ->from(USER.' U')
          ->join($pre_query.' AL','AL.user_id=U.user_id','LEFT')
          ->where_in('U.user_id', $chunk)
          ->where('U.is_systemuser',0)
          ->group_by('U.user_id')
          ->get()->result_array();
        $new_device_detail = array();
        foreach($device_detail as $key_device => $device)
        {
          $new_device_detail[$device['user_id']] = $device;
        }
        $notification_data=array();
        $notification_data["notification_type"]   = $data['notification_type'];
        $notification_data["stock_type"]          = $data['stock_type'];
        $notification_data["content"]             = array(
                "custom_notification_subject"   =>$data['notification_data']['en_subject'],
                "custom_notification_text"      =>$data['notification_data']['message'],
                
        );
        if($data['notification_type']==567){
          $notification_data["content"]["template_data"]                 = [
            "category_id"       =>$data['category_id'],
            "collection_id"     =>$data['collection_id'],
            "notification_type" =>$data['notification_type'],
            "stock_type"        =>$data['stock_type'],
          ];
        }
        
        foreach($chunk as $sub_key => $user)
        {
          $user_id = $user;
          $notification_data['user_id'] = $user_id;
          
          if(isset($new_device_detail[$user_id])) {
            $device_ids  = explode(',',$new_device_detail[$user_id]['device_ids']);
            //get only first 2 ids
            $unique_device_ids = array_slice($device_ids, 0,2);                
            $device_types = explode(',',$new_device_detail[$user_id]['device_types']);
            //get only first 2 device type
            $u_device_types = array_slice($device_types, 0,2);

            $notification_data['device_details']=array();
            // print_r($u_device_types);exit;
            if(!empty($unique_device_ids[0]))
            {
              foreach($unique_device_ids as $d_key => $d_value) 
              {
                if(!empty($d_value) && !in_array($d_value,[1,2]))
                {
                  $notification_data['device_details'][] =array(
                    'device_id' => $d_value,
                    'device_type' => $u_device_types[$d_key]) ;
                }
              }
              $notification_data["content"]['custom_notification_text'] = str_replace("{{username}}",$user_names[$user],$data['notification_data']['message']);
              $new_notification_data[] =  $notification_data;
            }
          } 
        }
        // print_r($new_notification_data); die();
        $push_data = ["push_notification_data" => $new_notification_data];
        $this->load->helper('queue_helper');
        add_data_in_queue($push_data, 'stock_auto_push');
      }
    }

    public function send_morning_push()
    {
      $stock_fantasy = $this->app_config['allow_stock_fantasy']['key_value'] ? $this->app_config['allow_stock_fantasy']['key_value'] : 0;
      $equity = $this->app_config['allow_equity']['key_value'] ? $this->app_config['allow_equity']['key_value'] : 0;
      $stock_predict = $this->app_config['allow_stock_predict']['key_value'] ? $this->app_config['allow_stock_predict']['key_value'] : 0;
      $allow_live_stock_fantasy = $this->app_config['allow_live_stock_fantasy']['key_value'] ? $this->app_config['allow_live_stock_fantasy']['key_value'] : 0;
      $stock_type = array();
      
      if($allow_live_stock_fantasy==1)
      {
        $active_stock_type = 4;
        array_push($stock_type,'4');
      }

      if($stock_predict==1)
      {
        $active_stock_type = 3;
        array_push($stock_type,'3');
      }
      
      if($equity==1)
      {
        $active_stock_type = 2;
        array_push($stock_type,'2');
      }
     
      if($stock_fantasy==1)
      {
        $active_stock_type = 1;
        array_push($stock_type,'1');
      }

     
      $current_date = format_date('today', 'Y-m-d');
      $holiday = $this->db->select('*')->from(HOLIDAY)->where("holiday_date",$current_date)->get()->row_array();
      if(!empty($holiday))
      {
        $current_content = array(
                                  "notification_type"             => 560,
                                  "ocation"                       =>$holiday['description'],
                                  "stock_type"                    =>$active_stock_type,
                                );
        $this->load->helper('queue_helper');
        add_data_in_queue($current_content,'stock_push');
      }else
      {
        // 15 min pahle wala notification yha aaega. yaa fixure publish wala aaega.
        $current_date = format_date('today', 'Y-m-d');
        $cur_date_time = format_date('today', 'Y-m-d H:i:s'); //'2021-09-23 03:30:00';

        //fixture publish notification
        $collections = $this->db->select("collection_id,name,(CASE WHEN category_id=1 THEN 'Daily' WHEN category_id=2 THEN 'Weekly' WHEN category_id=3 THEN 'Monthly' END) AS category,stock_type")->
        from(COLLECTION)->
        where('published_date <',$cur_date_time)->
        where('scheduled_date >',$cur_date_time)->
        where('is_notified',1)
        ->where_in('stock_type',$stock_type)
        ->get()->result_array();
        foreach($collections as $key=>$collection)
        {
        $current_content = array(
          "notification_type"             => 568,
          "category"                      =>$collection['category'],
          "collection_name"               => $collection['name'],
          "stock_type"                    =>$collection['stock_type'],
        );
        $this->load->helper('queue_helper');
        add_data_in_queue($current_content,'stock_push');
        }
        $collection_ids = array_unique(array_column($collections,'collection_id'));
        $this->db->where_in('collection_id',$collection_ids)
        ->where('is_notified',1)
        ->where_in('stock_type',$stock_type)
        ->update(COLLECTION,['is_notified'=>2]);
      }
      return true;
    }

     /**
       *  here we will send top gainer
       *  top loosers notifications of the day
       * fixture published
       * contest added
        */
    public function send_evening_push($data = array())
    {
      $stock_fantasy = $this->app_config['allow_stock_fantasy']['key_value'] ? $this->app_config['allow_stock_fantasy']['key_value'] : 0;
      $equity = $this->app_config['allow_equity']['key_value'] ? $this->app_config['allow_equity']['key_value'] : 0;
      $stock_predict = $this->app_config['allow_stock_predict']['key_value'] ? $this->app_config['allow_stock_predict']['key_value'] : 0;
      $allow_live_stock_fantasy = $this->app_config['allow_live_stock_fantasy']['key_value'] ? $this->app_config['allow_live_stock_fantasy']['key_value'] : 0;
      $stock_type = array();
      
      if($allow_live_stock_fantasy==1)
      {
        $active_stock_type = 4;
        array_push($stock_type,'4');
      }

      if($stock_predict==1)
      {
        $active_stock_type = 3;
        array_push($stock_type,'3');
      }
      
      if($equity==1)
      {
        $active_stock_type = 2;
        array_push($stock_type,'2');
      }
     
      if($stock_fantasy==1)
      {
        $active_stock_type = 1;
        array_push($stock_type,'1');
      }

        $this->load->helper('queue_helper');
        $current_date = format_date('today', 'Y-m-d');
        $cur_date_time = format_date('today', 'Y-m-d H:i:s'); //'2021-09-23 03:30:00';

        //fixture publish notification
        $fixtures = $this->db->select("collection_id,name,(CASE WHEN category_id=1 THEN 'Daily' WHEN category_id=2 THEN 'Weekly' WHEN category_id=3 THEN 'Monthly' END) AS category,stock_type")->
        from(COLLECTION)->
        where('published_date <',$cur_date_time)->
        where('scheduled_date >',$cur_date_time)->
        where('is_notified',0)
        ->where_in('stock_type',$stock_type)
        ->get()->result_array();
          if(isset($fixtures[0]))
          {
            $collection_ids = array_unique(array_column($fixtures,'collection_id'));
            foreach($fixtures as $key=>$fixture)
            {
              $fixture_content = array(
                "notification_type"             => 566,
                "category"                      =>$fixture['name'],
                "collection_name"               => $fixture['category'],
                "stock_type"                    =>$fixture['stock_type'],
                );
                // print_r($fixture_content);exit;
                add_data_in_queue($fixture_content,'stock_push');
            }
            $this->db->where_in('collection_id',$collection_ids)->update(COLLECTION,['is_notified'=>1]);
            // top 5 gainers

            
            
            $this->db->select("IFNULL(s.display_name,s.name) as display_name, (s.last_price - s.open_price) as price_diff,TRUNCATE(((s.last_price-IFNULL(s.open_price, 0))/IFNULL(s.open_price, 0))*100, 2) as percent_change",FALSE);
            $this->db->from(STOCK.' s');
            $this->db->where('s.status', 1);
            $this->db->limit('5');
            
            $new_sql = clone $this->db;
            $this->db->having('price_diff > ', 0);
            $this->db->order_by('percent_change', 'DESC');
            $results = $this->db->get()->result_array();
            if(isset($results[0]['display_name']))
            {
            $top_five_gainer = implode(', ',array_column($results,'display_name'));
            }
            // top gainer
            $gainer_content = array(
            "notification_type"             => 561,
            "gainer"                        =>$top_five_gainer,
            "stock_type"                    =>$active_stock_type,
            );
            add_data_in_queue($gainer_content,'stock_push');

            // top loosers
            $loosers_result = $new_sql->having('price_diff < ', 0)->order_by('percent_change', 'ASC')->get()->result_array();
            if(isset($loosers_result[0]['display_name']))
            {
            $top_five_loosers = implode(', ',array_column($loosers_result,'display_name'));
            }
            $looser_content = array(
              "notification_type"             => 562,
              "loosers"                    =>$top_five_loosers,
              "stock_type"                    =>$active_stock_type,
            );
            add_data_in_queue($looser_content,'stock_push');
            return true;
        }
    }

    public function send_candel_Start_notification($data)
    {
      $stock_predict = $this->app_config['allow_stock_predict']['key_value'] ? $this->app_config['allow_stock_predict']['key_value'] : 0;
      $cur_date_time = format_date('today', 'Y-m-d H:i'); //'2021-09-23 03:30:00';
      // $cur_date_time = "2022-03-10 06:00:00";

      $endTime = strtotime("+15 minutes", strtotime($cur_date_time));

      $upcomming_time = format_date($endTime, 'Y-m-d H:i');

      $upcomming_candels = $this->db->select("lineup_master_contest_id,LM.user_id,C.contest_id,C.collection_id,C.contest_name,C.status, C.contest_unique_id,CM.name as collection_name,CM.stock_type",false)
          ->from(CONTEST . " C")
          ->join(COLLECTION . " CM", "CM.collection_id = C.collection_id", "INNER")
          ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.contest_id = C.contest_id", "INNER")
          ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
          ->where('CM.scheduled_date >',$cur_date_time)
        ->where('CM.scheduled_date <=',$upcomming_time)
        ->where(array('CM.is_notified'=>1,'CM.stock_type'=>3,'C.status'=>0))
        ->group_by('C.contest_id,LM.user_id')
        ->get()
        ->result_array();
        // print_r($upcomming_candels);die($this->db->last_query());

      if(!empty($upcomming_candels)){
        $collection_ids = array_unique(array_column($upcomming_candels,'collection_id'));

        $user_ids = array_unique(array_column($upcomming_candels, 'user_id'));
        $device_detail = $this->db_user->select("user_id,GROUP_CONCAT(device_id ORDER BY keys_id DESC) as device_ids,GROUP_CONCAT(device_type ORDER BY keys_id DESC) as device_types")
          ->from(ACTIVE_LOGIN)
          ->where_in('user_id',$user_ids)
          ->where('device_id is NOT NULL', NULL, FALSE)
          ->group_by('user_id')
          ->order_by('keys_id','DESC')
          ->get()->result_array();
        
        if(!empty($device_detail)){
          $device_detail =  array_column($device_detail,NULL,'user_id');
          //Get notification message and subject
          $notify_desc = $this->get_notification_row(625);
          $chunks = array_chunk($upcomming_candels, 200);
          $this->load->helper('queue_helper');
          foreach($chunks as $key=>$chunk)
          {
            $new_notification_data = array();
            foreach ($chunk as $ck => $candel) {
              // print_r($candel);die();
              $user_id = $candel['user_id'];

              if(isset($device_detail[$user_id])) {
                $notifi_data=array();

                $device_ids  = explode(',',$device_detail[$user_id]['device_ids']);
                //get only first 2 ids
                // $unique_device_ids = array_slice($device_ids, 0,1);                
                $device_types = explode(',',$device_detail[$user_id]['device_types']);
                //get only first 2 device type
                // $u_device_types = array_slice($device_types, 0,1);
                
                $notifi_data['device_details'] = array();

                foreach($device_ids as $d_key => $d_value) 
                  {
                    if(!empty($d_value) && !in_array($d_value,[1,2]))
                    {
                      $notifi_data['device_details'][] =array(
                        'device_id' => $d_value,
                        'device_type' => $device_types[$d_key]) ;
                    }
                  }
                }              
                $notifi_data["notification_type"] = $candel['notification_type'] = 625;
                $notifi_data["stock_type"] = $candel['stock_type'];

                $message = str_replace("{{contest_name}}",$candel['contest_name'],$notify_desc['message'])."🤞";
                $notifi_data["notification_data"] = array(
                    "en_subject"   =>$notify_desc['en_subject'],
                    "message"      =>$message
                  ); 
                $notifi_data["content"] = array(
                      "template_data" =>$candel
                    );
              
                $new_notification_data[] =  $notifi_data;
              }
            }
            // print_r($new_notification_data);die();
            $data = ["push_notification_data" => $new_notification_data];            
            add_data_in_queue($data, 'stock_auto_push');
          }
          $this->db->where_in('collection_id',$collection_ids)
          ->where(array('is_notified'=>1,'stock_type'=>3))
          ->update(COLLECTION,['is_notified'=>2]);
      }else{
        echo "No candel will start within 15 minutes.\n ";
      }
    }

    public function notify_accuracy_percentage($data)
    {
      $stock_predict = $this->app_config['allow_stock_predict']['key_value'] ? $this->app_config['allow_stock_predict']['key_value'] : 0;
      $cur_date_time = format_date('today', 'Y-m-d h:i:s'); //'2021-09-23 03:30:00';
      // $cur_date_time = "2022-03-10 06:00:00";
      $startTime = strtotime("-1 month", strtotime($cur_date_time));

      $start_date = format_date($startTime, 'Y-m-d');

      $today_user_contest = $this->db->select("LM.user_id,C.contest_id,C.collection_id, C.contest_unique_id, CM.stock_type,FORMAT(AVG(LMC.percent_change),2) as average_accuracy",false)
          ->from(CONTEST . " C")
          ->join(COLLECTION . " CM", "CM.collection_id = C.collection_id", "INNER")
          ->join(LINEUP_MASTER_CONTEST . " LMC", "LMC.contest_id = C.contest_id", "INNER")
          ->join(LINEUP_MASTER . " LM", "LM.lineup_master_id = LMC.lineup_master_id", "INNER")
          ->where('CM.scheduled_date >',$start_date)
          ->where('CM.scheduled_date <=',$cur_date_time)
          ->where_in('C.status',array(2,3))
          ->where('CM.stock_type',3)
          ->group_by('LM.user_id')
          ->get()->result_array();

      // print_r($today_user_contest);die($this->db->last_query());

      if(!empty($today_user_contest)){
        $collection_ids = array_unique(array_column($today_user_contest,'collection_id'));

        $user_ids = array_unique(array_column($today_user_contest, 'user_id'));

        $device_detail = $this->db_user->select("user_id,GROUP_CONCAT(device_id ORDER BY keys_id DESC) as device_ids,GROUP_CONCAT(device_type ORDER BY keys_id DESC) as device_types")
          ->from(ACTIVE_LOGIN)
          ->where_in('user_id',$user_ids)
          ->where('device_id is NOT NULL', NULL, FALSE)
          ->where('date_created >',$start_date)
          ->group_by('user_id')
          ->order_by('keys_id','DESC')
          ->get()->result_array();
        if(!empty($device_detail)){
          $device_detail =  array_column($device_detail,NULL,'user_id');
          //Get notification message and subject
          $notify_desc = $this->get_notification_row(626);
          $chunks = array_chunk($today_user_contest, 200);
          $this->load->helper('queue_helper');
          foreach($chunks as $key=>$chunk)
          {
            $new_notification_data = array();
            foreach ($chunk as $ck => $users) {
              // print_r($users);die();
              $user_id = $users['user_id'];
              if(isset($device_detail[$user_id])) {
                $notifi_data = array();
                $device_ids  = explode(',',$device_detail[$user_id]['device_ids']);
                //get only first 2 ids
                $unique_device_ids = array_slice($device_ids, 0,2);
                
                $device_types = explode(',',$device_detail[$user_id]['device_types']);

                //get only first 2 device type
                $u_device_types = array_slice($device_types, 0,2);
                
                $notifi_data['device_details'] = array();
                foreach($unique_device_ids as $d_key => $d_value) 
                {
                  if(!empty($d_value) && !in_array($d_value,[1,2]))
                  {
                    $notifi_data['device_details'][] =array(
                      'device_id' => $d_value,
                      'device_type' => $u_device_types[$d_key]) ;
                  }
                }
                            
                $notifi_data["notification_type"] = $users["notification_type"] = 626;
                $notifi_data["stock_type"] = $users['stock_type'];

                $message = str_replace("{{average_accuracy}}",$users['average_accuracy'],$notify_desc['message'])."";
                if($users['average_accuracy']<0){
                  $message = $message." get into the race for betterment.🤞";
                }else{
                  $message = $message." become the best.🤞";
                }
                $notifi_data["notification_data"] = array(
                    "en_subject"   =>$notify_desc['en_subject'],
                    "message"      =>$message
                  ); 
                $notifi_data["content"] = array(
                      "template_data" =>$users
                    );
                // print_r($notifi_data);die();
                $new_notification_data[] =  $notifi_data;
              }else{
                //echo 'devices is not found';
              }
            }
            // print_r($new_notification_data);die();
            $data = ["push_notification_data" => $new_notification_data];            
            add_data_in_queue($data, 'stock_auto_push');
          }
        }else{
          echo "No candel will start within 15 minutes.\n ";
        }
      }
    }


    /**
	 * Used to get contest report
	 * @return     [array]
	 */
	public function get_completed_contest_report() { 
    $post_data = $_REQUEST; 
        $end_date = format_date();
        $start_date = date("Y-m-d H:i:s", strtotime($end_date." -20 hours"));		
        if(isset($post_data['from_date']) && $post_data['from_date'] != ""){
        	$start_date = date("Y-m-d H:i:s",strtotime($post_data['from_date']));
        }
        if(isset($post_data['to_date']) && $post_data['to_date'] != ""){
        	$end_date = date("Y-m-d H:i:s",strtotime($post_data['to_date']));
        }
	
		$this->db->select("CM.collection_id,C.group_id, C.category_id, C.contest_id, C.contest_unique_id, C.contest_name, C.entry_fee, C.prize_pool, C.site_rake, C.total_user_joined, C.size, C.minimum_size, C.currency_type, C.max_bonus_allowed");
		$this->db->select("C.entry_fee*C.total_user_joined as total_entry_fee,",false);
		$this->db->select("C.guaranteed_prize,CM.stock_type ,ST.name as stock_name");
		$this->db->select("C.scheduled_date AS scheduled_date",false);
		$this->db->from(CONTEST." AS C");
		$this->db->join(COLLECTION." AS CM","CM.collection_id = C.collection_id","INNER");
    $this->db->join(STOCK_TYPE." AS ST","ST.stock_type_id = CM.stock_type","LEFT");
		$this->db->where('C.status',3);
    $this->db->where('C.report_generated',0);
    $this->db->where("C.completed_date >= ", $start_date);
		$this->db->where("C.completed_date <= ", $end_date);
	

	  //   if(!empty($post_data['from_date']) && !empty($post_data['to_date'])) {
		// 	$this->db->where("DATE_FORMAT(C.scheduled_date,'%Y-%m-%d') >= '".format_date($post_data['from_date'],'Y-m-d')."' and DATE_FORMAT(C.scheduled_date,'%Y-%m-%d') <= '".format_date($post_data['to_date'],'Y-m-d')."'");
		// }
			
		// if(in_array($post_data['stock_type'], [1,2,3,4]))
		// {
		// 	$this->db->where('CM.stock_type',$post_data['stock_type']);
		// }	

		$tempdb = clone $this->db;
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows(); 

		if(!empty($sort_field) && !empty($sort_order)) {
			$this->db->order_by($sort_field, $sort_order);
		}

		if(!empty($limit) && !$post_data["csv"]) {
			$this->db->limit($limit, $offset);
		}
		$query = $this->db->get();
		// echo $this->db->last_query();die;
		$result	= array();
		if($query->num_rows()) {
			$category_list = $this->get_category_list();
			$category_list = array_column($category_list, 'name', 'category_id');
			$result	= $query->result_array();
			foreach($result as $key=>$contest) {
				$group = $this->db->select('group_name')->from(MASTER_GROUP)->where('group_id',$contest['group_id'])->get()->row_array();
				$result[$key]['group_name'] = $group['group_name'];
				$result[$key]['category_name'] = $category_list[$contest['category_id']];
			}
		}
    
		return array('result'=>$result, 'total'=>$total);
	}

  public function get_match_entry_fee_details($post_data){
		$result = $this->db_user->select("SUM(IF(O.source=1,(O.real_amount+O.winning_amount),0)) as entry_real,SUM(IF(O.source=1,O.bonus_amount,0)) as entry_bonus,SUM(IF(O.source=1,O.points,0)) as entry_coins,ROUND(SUM(IF(O.source=3,O.winning_amount,0)),2) as real_win,SUM(IF(O.source=3,O.bonus_amount,0)) as bonus_win,SUM(IF(O.source=3,O.points,0)) as coins_win,U.is_systemuser")
					->from(ORDER.' AS O')
					->join(USER.' AS U','O.user_id=U.user_id','left')
					->where_in('reference_id',explode(',',$post_data['contest_ids']))
					->where("O.status",1)
					->where_in("O.source",array(1,3))
					->group_by('U.is_systemuser')
					->order_by('U.is_systemuser','ASC')
					->get()->result_array();

		$entry_data = array("entry_real"=>0,"entry_bonus"=>0,"entry_coins"=>0,"prize_pool_real"=>0,"prize_pool_bonus"=>0,"prize_pool_coins"=>0,"bots_entry"=>0,"bots_winning"=>0,"promo_discount"=>0);
		if(!empty($result)){
			foreach($result as $rkey=>$rvalue){
				//for real user
				if(isset($rvalue['is_systemuser']) && $rvalue['is_systemuser'] == 0){

					$entry_data['entry_real'] = (!empty($rvalue['entry_real']))? $rvalue['entry_real']:0;
					$entry_data['entry_bonus'] = (!empty($rvalue['entry_bonus']))? $rvalue['entry_bonus']:0;
					$entry_data['entry_coins'] = (!empty($rvalue['entry_coins']))? $rvalue['entry_coins']:0;
					$entry_data['prize_pool_real'] = (!empty($rvalue['real_win']))? $rvalue['real_win']:0;
					$entry_data['prize_pool_bonus'] = (!empty($rvalue['bonus_win']))? $rvalue['bonus_win']:0;
					$entry_data['prize_pool_coins'] = (!empty($rvalue['coins_win']))? $rvalue['coins_win']:0;
				}

				//for system user

				if(isset($rvalue['is_systemuser']) && $rvalue['is_systemuser'] == 1){

					$entry_data['bots_entry'] = (!empty($rvalue['entry_real']))? $rvalue['entry_real']:0;

					$entry_data['bots_winning'] = (!empty($rvalue['real_win']))? $rvalue['real_win']:0;
				 }
			} 
			//promo_code
			$promocode = $this->db_user->select("sum(amount_received) as promo_discount")
							->from(PROMO_CODE_EARNING)
							->where_in('contest_unique_id',explode(',',$post_data['contest_unique_ids']))
							->where('is_processed','1')
							->get()->row_array();
			if(!empty($promocode)){
				$entry_data['promo_discount'] = (!empty($promocode)) ? $promocode['promo_discount']:0;
			}
		}
		return $entry_data;
	}


   /**
     * Replace into Batch statement
     * Generates a replace into string from the supplied data
     * @param    string    the table name
     * @param    array    the update data
     * @return   string
     */
    function replace_into_batch_report($table, $data) {

      // echo "here";die;
      $column_name = array();
      $update_fields = array();
      $append = array();
      foreach ($data as $i => $outer) {
          $column_name = array_keys($outer);
          $coloumn_data = array();
          foreach ($outer as $key => $val) {
              if ($i == 0) {
                  $update_fields[] = "`" . $key . "`" . '=VALUES(`' . $key . '`)';
              }

              if (is_numeric($val)) {
                  $coloumn_data[] = $val;
              } else {
                  $coloumn_data[] = "'" . replace_quotes($val) . "'";
              }
          }
          $append[] = " ( " . implode(', ', $coloumn_data) . " ) ";
      }

      $sql = "INSERT INTO " . $this->db_user->dbprefix($table) . " ( " . implode(", ", $column_name) . " ) VALUES " . implode(', ', $append) . " ON DUPLICATE KEY UPDATE " . implode(', ', $update_fields);
      $this->db_user->query($sql);
  }



}