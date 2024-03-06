<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once 'Cron_model.php';
class Prediction_model extends Cron_model {
    
    public $db_user ;
    public $db_fantasy ;
    public $testingNode = FALSE;

    public function __construct() 
    {
       	parent::__construct();
		$this->db_user		= $this->load->database('db_user', TRUE);
		$this->db_prediction	= $this->load->database('db_prediction', TRUE);
    }

    function get_order_by_source($user_id,$source,$user_prediction_id)
    {
        $condition = array(
                                "source"    =>$source,
                                "source_id" => $user_prediction_id,
                                "user_id" => $user_id
                                                           );
        return $this->db_user->where($condition)
            ->get(ORDER,1)
            ->row_array();
    }

    function refund_coins($input_data) 
    {
        // check for already refunded.
        $refundDetail = $this->get_order_by_source($input_data["user_id"], 174, $input_data["user_prediction_id"]);
        if ($refundDetail) {
            return false;
        }

        //get order details for source = 40-make prediction
        $orderDetail = $this->get_order_by_source($input_data["user_id"], 40,$input_data["user_prediction_id"]);
        if (!$orderDetail) {
            return false;
        }

        $user_balance = $this->get_user_balance($orderDetail["user_id"]);
         
        $orderData["user_id"]        = $orderDetail["user_id"];
        /* For Cancel game source    = 2 */
        $orderData["source"]         = 174;
        $orderData["source_id"]      = $input_data["user_prediction_id"];
        $orderData["season_type"]    = 1;
        /* type                      = 0 For creadit amount */
        $orderData["type"]           = 0;
        $orderData["date_added"]     = format_date();
        $orderData["modified_date"]  = format_date();
        $orderData["plateform"]      = $orderDetail["plateform"];
        $orderData["status"]         = 1;
        $orderData["real_amount"]    = $orderDetail["real_amount"];
        $orderData["bonus_amount"]   = $orderDetail["bonus_amount"];
        $orderData["winning_amount"] = $orderDetail["winning_amount"];
        $orderData["points"]         = $orderDetail["points"];
        $orderData['order_unique_id'] = $this->_generate_order_key();
        
        $this->db_user->insert(ORDER, $orderData);
        $order_id = $this->db_user->insert_id();
        if (!$order_id) {
            return false;
        }

        $real_bal    = $user_balance['real_amount'] + $orderData["real_amount"];
        $bonus_bal   = $user_balance['bonus_amount'] + $orderData["bonus_amount"];
        $winning_bal = $user_balance['winning_amount'] + $orderData["winning_amount"];
        $point_balance = $user_balance['point_balance'] + $orderData["points"];

        
        $this->update_user_balance($orderData["user_id"], $orderData, "add");

        //update refund flag
        $this->db_prediction->where('user_prediction_id', $input_data["user_prediction_id"]);
        $this->db_prediction->update(USER_PREDICTION,array('is_refund' => 1));

        // Refund cash notification
        $tmp = array(); 
        $this->db = $this->db_user;
        $user_detail = $this->get_single_row('email, user_name', USER, array("user_id"=>$orderDetail["user_id"]));
  
        $tmp["notification_type"]        = 174; // 174 coin refund
       
        $input_data['amount']            = $orderData["points"];
        $tmp["source_id"]                = $input_data["user_prediction_id"];
        
        $tmp["notification_destination"] = 7; //  Web, Push, Email
        
        $tmp["user_id"]                  = $orderDetail["user_id"];
        $tmp["to"]                       = $user_detail['email'];
        $tmp["user_name"]                = $user_detail['user_name'];
        $tmp["added_date"]               = date("Y-m-d H:i:s");
        $tmp["modified_date"]            = date("Y-m-d H:i:s");
        $tmp["content"]                  = json_encode($input_data);
        $tmp["subject"]                  = "Coins deposited.";

        $this->load->model('notification/Notify_nosql_model');
        $this->Notify_nosql_model->send_notification($tmp);
       

        return true;
    }

    
    function get_prediction_for_refund($post)
    {
       $user_list = $this->db_prediction->select("PM.prediction_master_id,UP.bet_coins,UP.user_id,PO.prediction_option_id,UP.user_prediction_id",FALSE)
       ->from(PREDICTION_MASTER.' PM')
       ->join(PREDICTION_OPTION.' PO',"PO.prediction_master_id=PM.prediction_master_id")
       ->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
       ->where("PO.prediction_master_id",$post['prediction_master_id'])
       ->where("UP.is_refund",0)
       ->group_by('UP.user_id')->get()->result_array();


       $this->load->helper('queue');
        foreach($user_list as $prediction_user)
        {
            rabbit_mq_push($prediction_user, 'prediction_refund');
        }
    }

    public function process_prediction_winning($post)
    {
        //$param = json_decode('{"prediction_master_id":"12","status":1,"added_on_queue":"2018-12-17 06:07:49"}', TRUE);
        
        // Get prediction Detail
        $prediction = $this->db_prediction->select("P.prediction_master_id, P.desc, P.total_user_joined, P.site_rake, P.total_pool, PO.prediction_option_id, PO.option, PO.is_correct,P.season_game_uid")
                                ->from(PREDICTION_MASTER.' AS P')
                                ->join(PREDICTION_OPTION .' AS PO', 'PO.prediction_master_id = P.prediction_master_id', 'INNER')
                                ->where('P.status', 1) 
                                ->where('PO.is_correct', 1) 
                                ->where('P.prediction_master_id', $post['prediction_master_id']) 
                                ->get()->row_array();
        //echo $this->db_fantasy->last_query();

        if (!empty($prediction))
        {
            // update predition status to prize distributed (2: prize distributed)
            $this->db_prediction->where('prediction_master_id', $prediction['prediction_master_id']);
            $this->db_prediction->update(PREDICTION_MASTER, array('status' => '2', 'updated_date' => format_date()));
        
            $winner_users = $this->db_prediction->select("UP.user_prediction_id, UP.user_id, UP.bet_coins")
                                ->from(USER_PREDICTION.' AS UP')
                                ->where('UP.prediction_option_id', $prediction['prediction_option_id']) 
                                ->get()->result_array();
            print_r($winner_users);            
            if(!empty($winner_users)){
                
                $siterake_amount = ($prediction['total_pool'] * $prediction['site_rake']) / 100;
                $prize_amount = $prediction['total_pool'] - $siterake_amount;
            
                $bet_amounts = array_column($winner_users, 'bet_coins');
                $total_bet_amt = array_sum($bet_amounts);
                $winner_weger = $prize_amount / $total_bet_amt;
                
                foreach($winner_users as $winner){
                    //echo "\nuser: ".$winner['user_id'];
                    
                    $winner_amount = $winner_weger * $winner['bet_coins'];
                    //echo '$winner_amount: '.$winner_amount;
                    
                    $this->transfer_prize_money_to_user($winner['user_prediction_id'], $winner['user_id'], $winner_amount, $prediction['prediction_master_id'], 2); //2: coin
                    
                    // Send Notification to winner
                    $this->prediction_won_notification($prediction, $winner['user_prediction_id'], $winner['user_id'], $winner['bet_coins'],$post['prediction_data']);
                }
            }
            
            //notigy lossers for on game center
            $this->get_loosers_notify($prediction['prediction_master_id'],$prediction['prediction_option_id']);
        }
        echo 'Prize distributed.';
        return true;
    }

    function get_loosers_notify($prediction_master_id,$option_id)
    {
        $loser_users = $this->db_prediction->select("UP.user_id,PM.status,0 as is_correct,PM.prediction_master_id,UP.user_id")
                                ->from(USER_PREDICTION.' AS UP')
                                ->join(PREDICTION_OPTION.' PO',"UP.prediction_option_id=PO.prediction_option_id")
                                ->join(PREDICTION_MASTER.' PM',"PO.prediction_master_id=PM.prediction_master_id")
                                ->where('UP.prediction_option_id<>', $option_id) 
                                ->where('PM.prediction_master_id', $prediction_master_id) 
                                ->get()->result_array();


        foreach($loser_users as $item)
        {
            $content = array(
                'user_predicted' => $item,
                'prediction_master_id' => $item['prediction_master_id'],
                'user_id' => $item['user_id'],
            );

            $notify_data["content"] = json_encode($content);

            $node_url = "lossPrediction";
            $this->notify_prediction_to_client($node_url,$content);

        }
                            
    }

    /**
     * @Summary: This function for transfer prize money to user.
     * @access: public
     * @param:
     * @return:
     */
    private function transfer_prize_money_to_user($user_prediction_id, $user_id, $win_amount, $prediction_master_id, $prize_type=2)
    {   
        //echo "<br> Prize: $lineup_master_contest_id, $user_id, $win_amount, $prediction_master_id, $prize_type";
        // Get user if from lineup_master_contest_id
        
        if($prize_type==3)
        {
            $prize_image = $win_amount;
            $win_amount = 0;
        }
        else
        {
            if($prize_type==2){
                 $cash_type = COINS;
            }
            else if($prize_type ==1){
                $cash_type = CASH_REAL;
            }
            else{
                $cash_type = CASH_BONUS;
            }
            $win_amount = round($win_amount);
        }

        try
        {   
            $this->db = $this->db_user;

            $order_info = $this->get_single_row('order_id', ORDER, array('user_id' => $user_id, 'source' => PREDICTION_WON_SOURCE, 'source_id' => $user_prediction_id, "season_type" => 1));
            // If prize is not alloted to user for the selected lineup contest 
            if(empty($order_info))
            {
                // Skip winn amount txn for 0 amount
                if($win_amount > 0)
                {
                    $deposit_params = array(
                                    'user_id' => $user_id,
                                    'amount' => $win_amount,
                                    'cash_type' => $cash_type,
                                    'plateform' => PLATEFORM_FANTASY,
                                    'source' => PREDICTION_WON_SOURCE,
                                    'source_id' => $user_prediction_id,
                                    'reason' => CRON_PRIZE_MONEY_NOTI,
                                    'season_type' => 1,
                                    'ignore_deposit_noty' => 1
                                );
                    //echo '<br>deposit_param: '; print_r($deposit_params);
                    
                    $this->deposit($deposit_params);
                }

                //Update winning amount 
                $this->db_prediction->where(array('user_prediction_id' => $user_prediction_id));
                $this->db_prediction->update(USER_PREDICTION, array('win_coins' => $win_amount));
               
            }
        } catch (Exception $e)
        {
            //echo 'Caught exception: '.  $e->getMessage(). "\n";
        }
    }

    private function prediction_won_notification($prediction_data, $user_prediction_id, $user_id, $user_bet_coins, $prediction_details)
    {
                $sql = $this->db_user->select("O.order_id, O.real_amount, O.bonus_amount, O.winning_amount, O.points,U.first_name, U.email, U.user_name,O.source_id,O.prize_image")
                        ->from(ORDER . " O")
                        ->join(USER . " U", "U.user_id = O.user_id", "INNER")
                        ->where("O.user_id", $user_id)
                        ->where("O.source", PREDICTION_WON_SOURCE)
                        ->where("O.source_id", $user_prediction_id)
                        ->get();
                //echo $this->db_user->last_query(); 
                $order_info = $sql->row_array();
                //echo '<br>$order_info:';print_r($order_info);
                if (!empty($order_info)) 
                {
                    if ($order_info['bonus_amount'] > 0 || $order_info['winning_amount'] > 0 || $order_info['points'] > 0 )
                    {
                        /* Send Notification */
                        $notify_data = array();
                        $notify_data['notification_type'] =183; //51-Prediction won
                        $notify_data['notification_destination'] = 7; //web, push, email
                        $notify_data["source_id"] = $user_prediction_id;
                        $notify_data["user_id"] = $user_id;
                        $notify_data["to"] = $order_info['email'];
                        $notify_data["user_name"] = !empty($order_info['user_name']) ? $order_info['user_name'] : $order_info['email'];
                        $notify_data["added_date"] = date("Y-m-d H:i:s");
                        $notify_data["modified_date"] = date("Y-m-d H:i:s");
                        $notify_data["subject"] = "Congratulations! You're a WINNER!";

                        //get user predicted details
                        $user_predicted = $this->get_user_predicted($user_id,$prediction_data['season_game_uid']);

                            $content = array(
                                'amount'            => $order_info['points'],
                                'prediction_master_id'        => $prediction_data['prediction_master_id'],
                                'desc'      => $prediction_data['desc'],
                                'prediction_option_id' => $prediction_data['prediction_option_id'],
                                'option' => $prediction_data['option'],
                                'user_id'           => $user_id,
                                'order_id'          => $order_info['order_id'],
                                'user_prediction_id'  => $user_prediction_id,
                                'bet_amount'         => $user_bet_coins,
                                'total_pool'  => $prediction_data['total_pool'],
                                'prediction_data' => $prediction_details,
                                'home' => $prediction_details['home'],
                                'away' => $prediction_details['away'],
                                'user_predicted' => $user_predicted
                            );

                        $notify_data["content"] = json_encode($content);

                        $node_url = "wonPrediction";
                        $this->notify_prediction_to_client($node_url,$content);
    

                        //echo '<br>$notify_data: ';print_r($notify_data);
                        //$this->Cron_model->add_notification($notify_data);
                        $this->load->model('notification/Notify_nosql_model');
                        $this->Notify_nosql_model->send_notification($notify_data);

                    }
                }
    }

    function get_user_predicted($user_id,$season_game_uid)
    {
        
        /**SELECT PM.prediction_master_id,PO.is_correct,PM.status
                FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
                INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id = PM.prediction_master_id
                INNER JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id  
                WHERE PM.season_game_uid='`+req.body.season_game_uid+`' AND UP.user_id=${req.body.currect_user_id}`**/

                $result = $this->db_prediction->select('PM.prediction_master_id,PO.is_correct,PM.status')
                ->from(PREDICTION_MASTER.' PM')
                ->join(PREDICTION_OPTION.' PO','PO.prediction_master_id = PM.prediction_master_id')
                ->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
                ->where('PM.season_game_uid',$season_game_uid)
                ->where('UP.user_id',$user_id)
                ->get()->result_array() ;
                return $result;

    }

    public function notify_prediction_to_client($url,$data)
	{
		 $curlUrl = LINEUP_NODE_ADDR.$url;

		 $data_string = json_encode($data);

		 try{

		 	$header = array("Content-Type:application/json",
		 	 "Accept:application/json",
		 	  "User-Agent:Mozilla/5.0 (Windows NT 6.3; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0"
		 	);

 			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL,$curlUrl);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$server_output = curl_exec($ch);

		 	// Check the return value of curl_exec(), too
		    if ($server_output === false) {
		        throw new Exception(curl_error($ch), curl_errno($ch));
		    }
			curl_close ($ch);

		 }
		 catch(Exception $e){
		 	// var_dump($e);
		 	// die('dfdf');
		 }

            return true;
	}


    function get_all_user_data()
	{
		$result = $this->db_user->select('U.user_id,U.user_name,IF(AL.device_type=1,GROUP_CONCAT(AL.device_id),"") device_ids,IF(AL.device_type=2,GROUP_CONCAT(AL.device_id),"") ios_device_ids,AL.device_type,U.email',false)
		->from(USER.' U')
		->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id')
		->where('status',1)
		->group_by('U.user_id')	
		->get()->result_array();

		return $result;
    }
    
    function check_match_live($season_game_uid)
	{
		$current_date = format_date();
		return $this->db_fantasy->select('S.season_id,S.season_game_uid,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away')
        ->from(SEASON." as S")
        ->join(TEAM.' T1','T1.team_id=S.home_id','INNER')
        ->join(TEAM.' T2','T2.team_id=S.away_id','INNER')
		->where('S.season_game_uid',$season_game_uid)
		->where('S.season_scheduled_date<',$current_date)
		->where('S.status',0)
		->get()->row_array();	
	}

    function notify_user_on_new_prediction($post_data)
    {
        $user_details = $this->get_all_user_data();

            $current_date = format_date();
            //check for live match, and if live send notification and push notification
            $live_match = $this->Prediction_model->check_match_live($post_data['season_game_uid']);

            $input = array();
            $input['question'] = $post_data['question'];

            $this->load->model('notification/Notify_nosql_model');
            if(!empty($live_match))
            { 
                 //get notification text for push notification
                 $noti_row =  $this->Notify_nosql_model->select_one_nosql('notification_description',array('notification_type' => '175'));
                 $notification_text = str_replace('{{question}}',$post_data['question'],$noti_row['en_message']);
               foreach($user_details as $user)
               {
                    if(empty($user['device_ids']) && empty($user["ios_device_ids"])) {
                        continue;
                    } 
    
                    $notify_data = array();
                    $notify_data["device_ids"]          = isset($user['device_ids']) ? $user['device_ids'] : '';            
                    $notify_data['ios_device_ids']      = isset($user["ios_device_ids"]) ? $user["ios_device_ids"] : '';
                    $notify_data["notification_type"] = 175; //175, 
                    $notify_data["source_id"] = 0;
                    $notify_data["notification_destination"] = 1; //Web,Push,Email
                    $notify_data["user_id"] = $user['user_id'];
                    $notify_data["to"] = $user['email'];
                    $notify_data["user_name"] = $user['user_name'];
                    $notify_data["added_date"] = $current_date;
                    $notify_data["modified_date"] = $current_date;
                    $notify_data["content"] = json_encode($input);
                    $notify_data["subject"] = '';
                    $notify_data["custom_notification_text"] = $notification_text;
                    $this->Notify_nosql_model->send_notification($notify_data);
               }
            }
            else
            {
                 //check for first prediction and send notification and push notification
                 $first_count = $this->get_match_prediction_count($post_data['season_game_uid']);
                 if( $first_count == 1)
                 {
                     //get notification text for push notification
                    $noti_row =  $this->Notify_nosql_model->select_one_nosql('notification_description',array('notification_type' => '176'));

                     //get season game match
                    $sports_id = isset($post_data['sports_id']) ? $post_data['sports_id'] : "";
                    $match_detail = $this->get_match_details($post_data['season_game_uid'],$sports_id);
                    $input['match'] = $match_detail['home'].' vs '.$match_detail['away'];

                    $notification_text = str_replace('{{match}}',$input['match'],$noti_row['en_message']);
                    foreach($user_details as $user)
                    {
                        if(empty($user['device_ids']) && empty($user["ios_device_ids"])) {
                            continue;
                        } 
        
                        $notify_data = array();
                        $notify_data["device_ids"]          = isset($user['device_ids']) ? $user['device_ids'] : '';            
                        $notify_data['ios_device_ids']      = isset($user["ios_device_ids"]) ? $user["ios_device_ids"] : '';
                        $notify_data["notification_type"] = 176; //176, 
                        $notify_data["source_id"] = 0;
                        $notify_data["notification_destination"] = 1; //Web,Push,Email
                        $notify_data["user_id"] = $user['user_id'];
                        $notify_data["to"] = $user['email'];
                        $notify_data["user_name"] = $user['user_name'];
                        $notify_data["added_date"] = $current_date;
                        $notify_data["modified_date"] = $current_date;
                        $notify_data["content"] = json_encode($input);
                        $notify_data["subject"] = '';
                        $notify_data["custom_notification_text"] = $notification_text;
                        $this->Notify_nosql_model->send_notification($notify_data);
                    }

                 }
            }
    }

    function get_match_prediction_count($season_game_uid)
	{
		$result = $this->db_prediction->select('COUNT(prediction_master_id) as count')
		->from(PREDICTION_MASTER)
		->where('season_game_uid',$season_game_uid)->get()->row_array();

		if(isset($result['count']))
		{
			return $result['count'];
		}

		return 0;
		
	
    }
    
    function get_match_details($season_game_uid,$sports_id="")
	{
		$this->db_fantasy->select('S.season_id,S.season_game_uid,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away')
		->from(SEASON." as S")
        ->join(TEAM.' T1','T1.team_id=S.home_id','INNER')
        ->join(TEAM.' T2','T2.team_id=S.away_id','INNER')
		->where('S.season_game_uid',$season_game_uid);
        if(isset($sports_id) && $sports_id != ""){
            $this->db_fantasy->join(LEAGUE." as L","L.league_id=S.league_id");
            $this->db_fantasy->where("L.sports_id",$sports_id);
        }
		$result = $this->db_fantasy->get()->row_array();	
        return $result;
	}

    function notify_sport_predictor_live()
    {
        $current_date_time = format_date('today');
        $past_time = $process_date_time = date('Y-m-d H:i:s',strtotime('-5 minutes',strtotime($current_date_time)));
        $live_matches =  $this->db_fantasy->select("season_game_uid,CONCAT(IFNULL(home,'-'),' vs ',IFNULL(away,'-')) AS match_name")
		->from(SEASON)
		->where('season_scheduled_date<',$current_date_time)
		->where('season_scheduled_date>',$past_time)
		->where('status',1)
		->get()->result_array();
        // echo $this->db_fantasy->last_query();exit;
        // print_r($live_matches);exit;
        if(!empty($live_matches))
        {
            foreach($live_matches as $key=>$match)
            {
                // print_r($match);exit;
                // $live_matches = array_unique(array_column($live_matches,'season_game_uid')); //[51938,51925]; //
                $prediction_query = $this->db_prediction->select('user_id')
                ->from(USER_PREDICTION.' UP')
                ->join(PREDICTION_OPTION.' PO','UP.prediction_option_id = PO.prediction_option_id','INNER')
                ->join(PREDICTION_MASTER.' PM','PM.prediction_master_id = PO.prediction_master_id','INNER')
                ->where('PM.season_game_uid',$match['season_game_uid'])
                ->distinct('user_id')->get()->result_array();
                // echo $this->db_prediction->last_query();exit;
                
                $prediction_users = array_column($prediction_query,'user_id');
                if(!empty($prediction_users))
                {
                    $user_detail = $this->db_user->select("U.user_id, IFNULL(U.user_name,'') AS user_name,U.point_balance,IF(AL.device_type=1,GROUP_CONCAT(AL.device_id),'') device_ids,IF(AL.device_type=2,GROUP_CONCAT(AL.device_id),'') ios_device_ids,AL.device_type",false)
                    ->from(USER .' U')
                    ->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id AND AL.device_id IS NOT NULL')
                    ->where("U.is_systemuser",0)
                    ->where("U.status",1)
                    ->where_in("U.user_id",$prediction_users)
                    ->group_by('U.user_id')
                    ->get()->result_array();

                    $notify_data = array();            
                    $notify_data["source_id"] = 0;
                    $notify_data["notification_destination"] = 2; //Push
                    $notify_data["added_date"] = $current_date_time;
                    $notify_data["modified_date"] = $current_date_time;
                    $notify_data["notification_type"] = 591;     
                    $notify_data['custom_notification_subject'] = 'Your game is live It\'s here 😍';
                    $notify_data['custom_notification_text'] = 'Visit '.$match['match_name'].' Game Center and score along with your team 💵';
            
                    foreach($user_detail as $user) {
                        if(empty($user['device_ids']) && empty($user["ios_device_ids"])) {
                            continue;
                        } 
            
                        $notify_data["device_ids"]          = isset($user['device_ids']) ? $user['device_ids'] : '';            
                        $notify_data['ios_device_ids']      = isset($user["ios_device_ids"]) ? $user["ios_device_ids"] : '';
                        $username = $user['user_name'] ? $user['user_name'] : "";
                        $notify_data["user_id"]     = $user['user_id'];
                        $notify_data["content"] 	= json_encode(array('user_id' => $user['user_id'], 'device_ids' => $user['device_ids']));
                        $this->load->model('notification/Notify_nosql_model','nnm');
                        $this->nnm->send_notification($notify_data);
                    }   
                }
            }
        }
        return true;

    }



}