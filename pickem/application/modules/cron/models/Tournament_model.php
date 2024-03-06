<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tournament_model extends MY_Model{
	public $db_user;
	public function __construct() 
    {
       	parent::__construct();
       	$this->db_user = $this->load->database('user_db', TRUE);
       	$this->db_pickem = $this->load->database('db_pickem', TRUE);
    }

   /**
   	* Function used for automatic add future games
   	* @param void
   	* @return boolean
   	*/
   	public function auto_match_addition(){
   		$current_date = format_date();
   		$this->db->select('T.tournament_id,T.league_id,T.start_date,T.end_date,T.match_count,GROUP_CONCAT(S.season_id) as season_ids,IFNULL(TS.tournament_season_id,0) as ts_id',FALSE);
		$this->db->from(TOURNAMENT." AS T");
		$this->db->join(SEASON. " S","S.league_id=T.league_id AND S.status < 2 AND S.scheduled_date > '".$current_date."'");
		$this->db->join(TOURNAMENT_SEASON. " TS","TS.tournament_id=T.tournament_id AND TS.season_id=S.season_id","LEFT");
		$this->db->where('T.status',0);
		$this->db->where('T.auto_match_publish',1);
		//$this->db->where('T.start_date <= ',$current_date);
		$this->db->where('T.end_date >= ',$current_date);
		$this->db->where('TS.tournament_season_id IS NULL');
		$this->db->group_by('T.tournament_id');
		$this->db->limit('10');
		$result = $this->db->get()->result_array();
    	//echo '<pre>';print_r($result);die;
    	foreach($result as $row){
    		$tournament_id = $row['tournament_id'];
    		$season_ids = $row['season_ids'];
    		$end_date = $row['end_date'];
    		if(!empty($season_ids)){
    			$match_list = $this->get_all_table_data("season_id,scheduled_date",SEASON,array("league_id"=>$row["league_id"],"season_id IN(".$season_ids.")"=>NULL));
    			//echo "<pre>";print_r($match_list);die;
    			if(!empty($match_list)){
    				//Start Transaction
		            $this->db->trans_strict(TRUE);
		            $this->db->trans_start();

		            $current_date = format_date();
		            foreach($match_list as $match){
						$season_data = array();
						$season_data['tournament_id'] = $tournament_id;
						$season_data['season_id'] = $match['season_id'];
						$season_data['scheduled_date'] = $match['scheduled_date'];
						$season_data['added_date'] = $current_date;
						$this->db->insert(TOURNAMENT_SEASON,$season_data);
					}

					$record_info = $this->get_single_row("COUNT(DISTINCT season_id) as total,MIN(scheduled_date) as start_date,MAX(scheduled_date) as end_date",TOURNAMENT_SEASON,array("tournament_id"=>$tournament_id));
					if(!empty($record_info)){
						$this->db->set('start_date',$record_info['start_date']);
						if(strtotime($record_info['end_date']) > strtotime($end_date)){
							$this->db->set('end_date',$record_info['end_date']);
						}
						$this->db->set('match_count',$record_info['total']);
						$this->db->where('tournament_id',$tournament_id);
						$this->db->update(TOURNAMENT);
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
		            }
    			}
    		}
    	}
    	return true;
   	}

   /**
   	* Function used for Cancel tournament and feed refund
   	* @param void
   	* @return boolean
   	*/
   	public function cancel_tournament($tournament_data)
   	{	
   		if(empty($tournament_data)){
   			return false;
   		}

   		$tournament_id = $tournament_data['tournament_id'];
   		$cancel_reason = $tournament_data['cancel_reason'];
		$refund_data = $this->db_user->select('O.order_id,O.real_amount,O.bonus_amount,O.winning_amount,O.points,O.source,O.source_id,O.user_id,O.reference_id,O.type')
                      ->from(ORDER . " AS O")
                      ->where('O.reference_id', $tournament_id)
                      ->where('O.status', 1)
                      ->where('O.source', TOURNAMENT_JOIN_SOURCE)
                      ->get()
                      ->result_array();
	          $user_txn_data = array();
	          if(!empty($refund_data)) {
	            foreach ($refund_data as $key => $value) {

	              $order_data = array();
	              $order_data["order_unique_id"] = generate_uid();
	              $order_data["user_id"]        = $value['user_id'];
	              $order_data["source"]         = TOURNAMENT_CANCEL_SOURCE;//530- Tournament cancel
	              $order_data["source_id"]      = $value['source_id'];
	              $order_data["reference_id"]   = $value['reference_id'];
	              $order_data["type"]           = 0;
	              $order_data["status"]         = 0;
	              $order_data["real_amount"]    = $value['real_amount'];
	              $order_data["bonus_amount"]   = $value['bonus_amount'];
	              $order_data["winning_amount"] = $value['winning_amount'];
	              $order_data["points"]         = $value['points'];
	              $order_data["date_added"]     = format_date();
	              $order_data["modified_date"]  = format_date();
	              $order_data["custom_data"]  = json_encode(array('name'=>$tournament_data['name'],'cancel_reason'=>$tournament_data['cancel_reason']));
	              $user_txn_data[] = $order_data;
            	}
	          }else{
				$this->db->where('tournament_id', $tournament_id);
				$this->db->update(TOURNAMENT, array('status' => 1, 'modified_date' => format_date()));
	          }

	        if(!empty($user_txn_data)){
	         try
	            {
		              $this->db_pickem->where("tournament_id",$tournament_id);
		              $this->db_pickem->update(USER_TOURNAMENT, array('fee_refund' => 1));

		              $this->db = $this->db_user;

		              //Start Transaction
		              $this->db->trans_strict(TRUE);
		              $this->db->trans_start();
		              $user_txn_arr = array_chunk($user_txn_data, 999);
		              foreach($user_txn_arr as $txn_data){
		                $this->insert_ignore_into_batch(ORDER, $txn_data);
		              }

		              $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = 529 AND type=1 AND status=1 AND reference_id='".$tournament_id."' GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
		              SET U.balance = (U.balance + OT.real_amount),U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
		              WHERE O.source = 530 AND O.type=0 AND O.status=0 AND O.reference_id='".$tournament_id."' ";

		              $this->db->query($bal_sql);
		              //Trasaction End
		              $this->db->trans_complete();
		              if ($this->db->trans_status() === FALSE ) { 
		                $this->db->trans_rollback();
		              } else {
		                $this->db->trans_commit();
		                //Update order status 1
		                   /*$this->db->where('reference_id', $tournament_id);
		                   $this->db->where('source', TOURNAMENT_CANCEL_SOURCE);
			               $this->db->update(ORDER, array('status' => 1, 'modified_date' => format_date(),'cancel_reason'=>$cancel_reason ));*/

		                // Game table update cancel status
			               $this->db_pickem->where('tournament_id', $tournament_id);
			               $this->db_pickem->update(TOURNAMENT, array('status' => 1, 'modified_date' => format_date(),'cancel_reason'=>$cancel_reason ));

		                //Send tournament cancel notification to joined user
		                $notify_data = array();
				   		foreach ($user_txn_data as $key => $value) {
				   			$user_detail = $this->get_single_row('email, user_name', USER, array("user_id"=>$value["user_id"]));
							$notify_data['notification_type'] = TOURNAMENT_CANCEL_NOTIFY;
							$notify_data['notification_destination'] = 7;
							$notify_data['user_id'] 		= $value['user_id'];
							$notify_data['to'] 				= $user_detail['email'];
							$notify_data['user_name']  		= $user_detail['user_name'];
							$notify_data['source_id']  		= $value['source_id'];
							$notify_data["added_date"] 		= format_date();
							$notify_data["modified_date"] 	= format_date();
							$notify_data["subject"]    		= "Oops! Tournament has been cancelled!";                 
							$notify_data['content']     	= json_encode(array('name'=>$tournament_data['name'],'cancel_reason'=>$tournament_data['cancel_reason'],'start_date'=>$tournament_data['start_date'],'end_date'=>$tournament_data['end_date'],'entry_fee'=>$tournament_data['entry_fee'],'currency_type'=>$tournament_data['currency_type']));

							$this->load->model('user/User_nosql_model');
				          	$this->User_nosql_model->send_notification($notify_data);

							$user_balance_cache_key = 'user_balance_' . $value['user_id'];
							$user_balance = $this->delete_cache_data($user_balance_cache_key);
				   		}
				   		
		              }



		        } catch (Exception $e) {
		            echo 'Caught exception: '.  $e->getMessage(). "\n";die;
		        }

		    } 
         return true;
   	}

   	public function update_tournament_score($data=array())
    { 	
    	//$data['tournament_id'] = 184;
	    //Start Transaction
	    $this->db->trans_strict(TRUE);
	    $this->db->trans_start();
	    if(!empty($data['season_id']) || !empty($data['tournament_id']) ){
	     	$this->db->select('T.tie_breaker_answer,T.tournament_id,UT.user_tournament_id');
			$this->db->from(TOURNAMENT. " T");
			$this->db->join(TOURNAMENT_SEASON. " TS","TS.tournament_id=T.tournament_id");
			$this->db->join(USER_TOURNAMENT. " UT","UT.tournament_id=T.tournament_id");
			if(!empty($data['season_id'])){
				$this->db->where_in('TS.season_id',$data['season_id']);
				$this->db->where('T.status !=',2);
				$this->db->group_by('UT.user_tournament_id');
			
			}elseif(!empty($data['tournament_id'])){
				$this->db->where('T.tournament_id',$data['tournament_id']);
			}
			$result = $this->db->get()->result_array();
			
	      //echo '<pre>';print_r($result);die;

	      if(empty($result)){
	        return true;
	      }
	      if(!empty($data['season_id'])){
	      $user_tournament_ids = implode(',',array_unique(array_column($result,'user_tournament_id')));  
	      //update total_Score
	      $score_sql = "UPDATE ".$this->db->dbprefix(USER_TOURNAMENT)." AS UTO 
	                  INNER JOIN (
	                    SELECT 
	                      SUM(UT.score) as score, 
	                      UT.user_tournament_id 
	                    from 
	                      ".$this->db->dbprefix(USER_TEAM)." AS UT 
	                      where UT.user_tournament_id IN(".$user_tournament_ids.")
	                     group by UT.user_tournament_id
	                  ) as GT ON UTO.user_tournament_id=GT.user_tournament_id 
	                SET 
	                  total_score = GT.score 
	                where UTO.fee_refund = 0";
	      //echo $score_sql;die;
	      $this->db->query($score_sql);

	      }

	      $tournament = array_column($result, 'tie_breaker_answer','tournament_id');
	      //echo '<pre>';print_r($tournament);die;
	      foreach ($tournament as $tournament_id => $answer) {
	        $rank_sql = "UPDATE ".$this->db->dbprefix(USER_TOURNAMENT)." as UT 
	                INNER JOIN (
	                  SELECT 
	                    user_tournament_id, 
	                    RANK() OVER (PARTITION BY tournament_id
	                      ORDER BY 
	                        SUM(total_score) DESC,CASE WHEN $answer>0 THEN  MIN(ABS(tie_breaker_answer - $answer)) END ASC,user_tournament_id ASC
	                       
	                    ) rank_value 
	                  FROM 
	                    ".$this->db->dbprefix(USER_TOURNAMENT)." where tournament_id = ".$tournament_id."
	                  GROUP BY 
	                    tournament_id,user_id
	                ) as GT ON UT.user_tournament_id = GT.user_tournament_id 
	              SET 
	                game_rank = GT.rank_value 
	              ";
	          //echo $rank_sql;die;
	        $query = $this->db->query($rank_sql);
	        $this->db->where('tournament_id',$tournament_id);
			$this->db->update(TOURNAMENT,array("modified_date"=>format_date()));
	      }
	    }else{
	        $this->db->select('S.status,T.tournament_id'); //T.tie_breaker_answer as admin_answer,UTO.tie_breaker_answer as user_answer,UTO.user_tournament_id
	        $this->db->from(USER_TOURNAMENT. " UTO");
	        $this->db->join(TOURNAMENT. " T","T.tournament_id=UTO.tournament_id");
	        $this->db->join(TOURNAMENT_SEASON. " TS","TS.tournament_id=T.tournament_id");
	        $this->db->join(SEASON. " S","S.season_id=TS.season_id");
	        $this->db->where('T.start_date <',format_date());
	        $this->db->where('T.status',0);
	        $this->db->where('S.status',0);
	        $this->db->group_by('T.tournament_id');
	        $sql = $this->db->get()->result_array();
	        
	        if(!empty($sql))
	          {
	            $tournament_ids = implode(',',array_column($sql, 'tournament_id'));
	            //echo '<pre>';print_r($tournament_ids);die;
	            $rank_sql = "UPDATE ".$this->db->dbprefix(USER_TOURNAMENT)." as UT 
	                    INNER JOIN (
	                      SELECT 
	                        user_tournament_id, 
	                        RANK() OVER (PARTITION BY tournament_id
	                          ORDER BY 
	                            SUM(total_score) DESC,MIN(added_date) ASC
	                           
	                        ) rank_value 
	                      FROM 
	                        ".$this->db->dbprefix(USER_TOURNAMENT)." where tournament_id IN (".$tournament_ids.")
	                      GROUP BY 
	                        tournament_id,user_id
	                    ) as GT ON UT.user_tournament_id = GT.user_tournament_id 
	                  SET 
	                    game_rank = GT.rank_value 
	                  ";
	             // echo $rank_sql;die;
	            $query = $this->db->query($rank_sql);
	            //echo '<pre>';print_r($tournament_ids);die;
	          }
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
	    }
	  

    return true;
  }
	
	/**
   	* Function used for push prize distribution request into queue
   	* @param void
   	* @return boolean
   	*/
  	public function process_prize_distribution()
  	{
  		$current_date = format_date();
		$this->db->select("T.tournament_id,T.prize_detail,T.status")
                    ->from(TOURNAMENT." AS T")
                    ->where("T.status",2)
                    ->where("T.end_date < ",$current_date)
                    ->order_by('T.tournament_id','DESC');
       	$sql = $this->db->get();
       	$result = $sql->result_array();
		
      	if(!empty($result))
      	{
        	$this->load->helper('queue');
        	$server_name = get_server_host_name();
        	foreach($result AS $key => $prize)
        	{
          		$content = array();
        		$content['url'] = $server_name."/pickem/cron/tournament/prize_distribution/".$prize['tournament_id'];
          		add_data_in_queue($content,'pickem_tournament');
        	}
      	}
      	return true;
  	}

  	/**
   	* Function used for tournament prize distribution
   	* @param int $tournament_id
   	* @return boolean
   	*/
	public function prize_distribution($tournament_id){
		if(!$tournament_id){
			return false;
		}
		$current_date = format_date();
		$this->db->select("T.tournament_id,T.sports_id,T.league_id,T.name,T.start_date,T.end_date,T.prize_detail,T.status")
                    ->from(TOURNAMENT." AS T")
                    ->where("T.status",2)
                    ->where("JSON_EXTRACT(T.prize_detail, '$[0]') is not null")
					->where('T.tournament_id',$tournament_id);
       	$sql = $this->db->get();
       	$result = $sql->row_array();
	    //echo "=====<pre>";print_r($result);die;
		if(!empty($result)){
			$tournament_id = $result['tournament_id'];
			$tournament_name = $result['name'];
			$wining_amount = json_decode($result['prize_detail'], TRUE);
	        $wining_max = array_column($wining_amount, 'max');
	        $winner_places = max($wining_max);
	        if(empty($winner_places) || $winner_places == NULL || $winner_places == 0){
	        	$this->db->where('tournament_id', $tournament_id);
                $this->db->update(TOURNAMENT, array('status' => '3',"modified_date"=>$current_date));
	            return true;
	        }
	       // echo "=====<pre>";print_r($wining_amount);die;
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
			                $mname = "";
			                if(isset($win_amt['amount'])){
								$mname = $win_amt['amount'];
							}
			                $winning_amount_arr[$i] = array("prize_type"=>$win_amt['prize_type'],"name"=>$mname);
		              	}
		              	else
		              	{   
		                	$winning_amount_arr[$i] = array("prize_type"=>$win_amt['prize_type'],"amount"=>$win_amt['amount']);
		              	}
		            }
	          	}
	        }
	      	//echo "<pre>";print_r($winning_amount_arr);die;
		    //get winners
			$winners = $this->db->select('UT.*', FALSE)
							->from(USER_TOURNAMENT.' AS UT')
							->where('UT.tournament_id', $tournament_id)
							->order_by("UT.game_rank","ASC")
							->limit($winner_places)
							->get()
							->result_array();
			//echo "<pre>";print_r($winners);die;
			$user_game_data = array();
	        $user_txn_data = array();
	        if(!empty($winners))
	        {
	          	foreach ($winners as $key => $value)
	          	{
		            $is_winner = 0;
		            $ldb_data = array();
		            $ldb_data['bonus'] = $ldb_data['amount'] = $ldb_data['coin'] = 0;
		            $ldb_data['merchandise'] = NULL;
		            $prize_obj = $winning_amount_arr[$value['game_rank']];
		            $custom_data = array("name"=>$tournament_name);
	            	if($prize_obj['prize_type'] == 0){
	            		$is_winner = 1;
						$ldb_data['bonus'] = $prize_obj['amount'];
						$custom_data['prize_type'] = 0;
					}else if($prize_obj['prize_type'] == 1){
	            		$is_winner = 1;
						$ldb_data['amount'] = $prize_obj['amount'];
						$custom_data['prize_type'] = 1;
					}else if($prize_obj['prize_type'] == 2){
	            		$is_winner = 1;
						$prize_obj['amount'] = ceil($prize_obj['amount']);
						$ldb_data['coin']  = $prize_obj['amount'];
						$custom_data['prize_type'] = 2;
					}else if($prize_obj['prize_type'] == 3){
						$is_winner = 1;
						$ldb_data['merchandise'] = $prize_obj['name'];
						$custom_data['prize_type'] = 3;
						$custom_data['merchandise']= $prize_obj['name'];
					}
		           
		            $ldb_data['tournament_id'] = $value['tournament_id'];
		            $ldb_data['user_id'] = $value['user_id'];
		            $ldb_data['is_winner'] = $is_winner;
		            //$ldb_data['prize_data'] = json_encode($prize_obj);
		            $user_game_data[] = $ldb_data;
		            //echo "<pre>";print_r($user_game_data);die;
		            //user txn data
                    $order_data = array();
                    $order_data["order_unique_id"] = generate_uid();
                    $order_data["user_id"] = $value['user_id'];
                    $order_data["source"] = TOURNAMENT_WON_SOURCE;
                    $order_data["source_id"] = $value['user_tournament_id'];
                    $order_data["reference_id"] = $tournament_id;
                    $order_data["season_type"] = 1;
                    $order_data["type"] = 0;
                    $order_data["status"] = 0;
                    $order_data["real_amount"] = 0;
                    $order_data["bonus_amount"] = $ldb_data['bonus'];
                    $order_data["winning_amount"] = $ldb_data['amount'];
                    $order_data["points"] = ceil($ldb_data['coin']);
                    $order_data["custom_data"] = json_encode($custom_data);
                    $order_data["date_added"] = format_date();
                    $order_data["modified_date"] = format_date();
                    $user_txn_data[$tournament_id][] = $order_data;
	          	}
	        }
	       	//echo "<pre>";print_r($user_txn_data);die;
	        if(!empty($user_txn_data)){
	        	$this->load->model("user/User_model");
                $result = $this->User_model->winning_credit($user_txn_data,TOURNAMENT_WON_SOURCE);
                if($result){
                    try
                    {
                       
                        //Start Transaction
                        $this->db->trans_strict(TRUE);
                        $this->db->trans_start();
                  
                        $tournament_ids = array_unique(array_column($user_game_data,"tournament_id"));
                        $user_game_arr = array_chunk($user_game_data, 999);
                        foreach($user_game_arr as $game_data){
                            $this->replace_into_batch(USER_TOURNAMENT, $game_data);
                        }

                        // Game table update is_prize_distributed 1
                        $this->db->where('status',"2");
                        $this->db->where('tournament_id',$tournament_id);
                        $this->db->update(TOURNAMENT, array('status' => '3', 'modified_date' => format_date()));

						$this->load->helper('queue');
						$server_name = get_server_host_name();
						//for TDS
						$content = array();
						$content['url'] = $server_name."/pickem/cron/tournament/deduct_tds_from_winning/".$tournament_id;
						add_data_in_queue($content,'pickem_tds');

						$content = array();
						$content['url'] = $server_name."/pickem/cron/tournament/perfect_score_distribution/".$tournament_id;
						add_data_in_queue($content,'pickem_tournament');


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
                    } catch (Exception $e)
                    {
                        log_message("Error","Tournament Winning - ".$tournament_id." : ".$e->getMessage());
                    }
                }
	        }
		}
		return true;
	}

	/**
    * Perfect score distribution
    */
	public function perfect_score_distribution($tournament_id)
	{
		$sql = "SELECT  `UTO`.`user_tournament_id`,`UTO`.`user_id`, SUM(CASE WHEN UT.is_correct=1 THEN UT.is_correct ELSE 0  END) as `correct_cnt`,UTO.tournament_id,GT.season_cnt,T.perfect_score,T.name
			FROM ".$this->db->dbprefix(USER_TOURNAMENT)." `UTO`
			JOIN  ".$this->db->dbprefix(USER_TEAM)." `UT` ON `UT`.`user_tournament_id`=`UTO`.`user_tournament_id` 
			JOIN ".$this->db->dbprefix(TOURNAMENT)." `T` ON `T`.`tournament_id`=`UTO`.`tournament_id` INNER JOIN
			(SELECT count(season_id) as season_cnt,tournament_id from  ".$this->db->dbprefix(TOURNAMENT_SEASON)." where tournament_id=".$tournament_id."
			) as GT
			ON GT.tournament_id = UTO.tournament_id
			where T.perfect_score IS NOT NULL
			GROUP by UTO.user_id
			HAVING 
			(CASE WHEN GT.`season_cnt`= `correct_cnt`
					THEN 1
				 WHEN JSON_SEARCH(T.perfect_score,'all', `correct_cnt`, NULL, '$[*].correct')>0 
					THEN 1
					ELSE 0 END
			)>0";
		$result = $this->db->query($sql)->result_array();
		
		//echo '<pre>';print_r($result);die;
		if(!empty($result)){
			$key = array_key_first($result);
			$ps_prize = json_decode($result[0]['perfect_score'],1);
			
			$tournament_name = $result[0]['name'];
			$ps_credit = array();
			$count_user =  array_count_values(array_column($result, 'correct_cnt'));
			
			$perfect_user_arry = array();
			foreach ($ps_prize as $prize_key => $prize_value) {

				$ps_credit = [];
				$ps_credit['real_amount']  = $ps_credit['bonus_amount']  = $ps_credit['points']  = 0;
				foreach ($result as $key => $value){
					
					if(in_array($value['user_id'], $perfect_user_arry)){
						//$count_user[$value['correct_cnt']]=$count_user[$value['correct_cnt']]-1;
						continue;
					}
					//print_R($count_user); die;
					

					if($prize_key == 0 && $value['season_cnt'] == $value['correct_cnt'] ){
						$prize_amount  = number_format($prize_value['amount'] / $count_user[$value['correct_cnt']] ,2, '.', '');
					}elseif (!empty($prize_value['correct']) && $prize_value['correct'] == $value['correct_cnt']) {
						$prize_amount  = number_format($prize_value['amount']/ $count_user[$value['correct_cnt']],2, '.', '');
					}else{
						continue;
					}  
					$ps_credit['source']       = 533;
					$ps_credit['user_id']      = $value['user_id'];
					$ps_credit['source_id']    =  $value['user_tournament_id'];
					$ps_credit['reference_id'] =  $value['tournament_id'];
					if($prize_value['prize_type'] == 1){
						$ps_credit['real_amount']  = $prize_amount; //real amount
					}elseif($prize_value['prize_type'] == 0){
						$ps_credit['bonus_amount']  = $prize_amount; //bonus
					}elseif($prize_value['prize_type'] == 2){
						$ps_credit['points']  = $prize_amount; //coins
					}
					$ps_credit['reason']       = 'Perfect score credit';
					$ps_credit['custom_data']  = array('name'=>$tournament_name,'amount'=>$prize_amount,'prize_type'=>$prize_value['prize_type']);
					$perfect_user_arry[]=$value['user_id'];
					
					$this->load->model('user/User_model');
					
					// user tournament
					$this->db->where('user_tournament_id', $value['user_tournament_id']);
                	$this->db->update(USER_TOURNAMENT, array('perfect_score_data'=>json_encode(array('name'=>$tournament_name,'amount'=>$prize_amount,'prize_type'=>$prize_value['prize_type']))));
					
					$this->User_model->credit_perfect_score($ps_credit);

				} 
			}
		}
		return true;
	}

	/**
   	* Function used for sent prize winning notification
   	* @param void
   	* @return boolean
   	*/
  	public function prize_notification()
  	{
		$result = $this->db->select('T.tournament_id,T.prize_detail,T.name,T.entry_fee,T.start_date,T.end_date,T.currency_type,IFNULL(L.display_name,L.league_name) AS league', FALSE)
                    ->from(TOURNAMENT.' AS T')
					->join(LEAGUE." AS L", "L.league_id = T.league_id", "INNER")
					->where('T.status', 3)
					->where('T.is_notify', 0)
					->order_by('T.tournament_id','DESC')
					->get()
					->result_array();


      	if(!empty($result))
      	{
        	foreach ($result as $tournament)
        	{	
        		$total_winner = 1;
        		$prize_detail = json_decode($tournament['prize_detail'],1);
	              if(isset( $prize_detail[count($prize_detail) - 1]['max'])){
	                  $total_winner = $prize_detail[count($prize_detail) - 1]['max'];
	              }
	              
  				//get winners
				$winners = $this->db->select('UT.*', FALSE)
								->from(USER_TOURNAMENT.' AS UT')
								->where('UT.tournament_id', $tournament['tournament_id'])
								->where('UT.is_winner', '1')
								->order_by("UT.game_rank","ASC")
								->get()
								->result_array();
				//echo "<pre>";print_r($winners);die;
				if(!empty($winners)){
					$user_ids = array_unique(array_column($winners,"user_id"));
					$this->load->model('user/User_model');
		            $user_data = $this->User_model->get_user_detail_by_user_id($user_ids,"email");
		            $user_data = array_column($user_data,NULL,"user_id");
					//echo "<pre>";print_r($user_data);die;
					foreach($winners as $row){
	        
						$notify_data = array();
						$notify_data['notification_type'] = TOURNAMENT_WON_NOTIFY; //3-GameWon
						$notify_data['notification_destination'] = 7; //web, push, email
						$notify_data["source_id"] = $row['user_tournament_id'];
						$notify_data["user_id"] = $row['user_id'];
						$notify_data["to"] = $user_data[$row['user_id']]['email'];
						$notify_data["user_name"] = $user_data[$row['user_id']]['user_name'];
						$notify_data["added_date"] = format_date();
						$notify_data["modified_date"] = format_date();
						$notify_data["subject"] = "Wohoo! You just WON!";
						$notify_data['content'] = json_encode(array('source_id'=>$notify_data["source_id"],'notification_type'=>TOURNAMENT_WON_NOTIFY,'tournament_id'=>$tournament['tournament_id'],'name'=>$tournament['name'],'prize'=>$prize_detail,'league_name'=>$tournament['league'],'start_date'=>$tournament['start_date'],'end_date'=>$tournament['end_date'],'game_rank'=>$row['game_rank'],'total_winner'=>$total_winner,'entry_fee'=>$tournament['entry_fee']));

	                    $this->load->model('user/User_nosql_model');
	                    $this->User_nosql_model->send_notification($notify_data);

		            }
				}
				
                $this->db->where('tournament_id',$tournament['tournament_id']);
                $this->db->where('status',"3");
                $this->db->where('is_notify',"0");
                $this->db->update(TOURNAMENT, array('is_notify'=>'1','modified_date'=>format_date()));


        	}
      	}
      	return true;
  	}


	/** 
	 *  Deduct TDS after winning
	 *  @param tournament_id
	 */
  	public function deduct_tds_from_winning($tournament_id)
    {
        $allow_tds = isset($this->app_config['allow_tds'])?$this->app_config['allow_tds']['key_value']:0;
        if($allow_tds != "1")
        {
          return TRUE;
        }
        $tds_percent = isset($this->app_config['allow_tds']['custom_data']['percent']) ? $this->app_config['allow_tds']['custom_data']['percent'] : 0;
        $tds_amount = isset($this->app_config['allow_tds']['custom_data']['amount']) ? $this->app_config['allow_tds']['custom_data']['amount'] : 0;
        if(!empty($tournament_id))
        {
          
          $this->db_user->select("O.source_id, O.user_id, O.real_amount, O.bonus_amount, SUM(O.winning_amount) AS total_real_amount")
                          ->from(ORDER . " O");
          $this->db_user->where("O.reference_id",$tournament_id);
          $this->db_user->where("O.source",TOURNAMENT_WON_SOURCE);// 3: game won
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
                                          ->where("source_id", $tournament_id)
                                          ->where("source", TOURNAMENT_TDS_SOURCE)
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
                                              'tournament_id' => $tournament_id,
                                              'plateform' => 'Fanatsy',
                                              'source' => TOURNAMENT_TDS_SOURCE, // TDS Deduction
                                              'source_id' => $tournament_id,
                                              'reason' =>  'For deduction of tds'
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
                if ($input_data["source"] == TOURNAMENT_TDS_SOURCE && $user_balance["winning_amount"] < $amount) {
                    return false;
                }else if ($input_data["source"] != 8 && $input_data["source"] != 11 && $user_balance["real_amount"] < $amount) {
                    return false;
                }
                if ($input_data["source"] == TOURNAMENT_TDS_SOURCE){
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
        $orderData['order_unique_id'] = generate_uid();
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
            $input_data["reason"] = 'On withdrawal';
            $tmp["notification_destination"] = 7; //  Web, Push, Email
            $tmp["notification_type"] = TOURNAMENT_TDS_NOTIFY; // 7-Withdraw
            if($input_data["source"] == TOURNAMENT_TDS_SOURCE)
            {
                $tmp["notification_destination"] = 1;
                $tmp["notification_type"] = TOURNAMENT_TDS_NOTIFY;
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
     * Contest reschedule when delay in any fixture
     * @param void
     */
    public function tournament_rescheduled()
    {
        $this->db->select("T.league_id,T.tournament_id,TS.season_id,T.start_date,L.sports_id")
                    ->from(TOURNAMENT. " T")
                    ->join(TOURNAMENT_SEASON. " TS","TS.tournament_id = T.tournament_id AND T.start_date = TS.scheduled_date ")
                    ->join(LEAGUE. " L","L.league_id = T.league_id ")
                    ->where("T.start_date >", format_date());
         $query = $this->db->get();
         $tournament_details = $query->result_array();
         //echo "<pre>";print_r($tournament_details);die;
         if(isset($tournament_details) && !empty($tournament_details))
         {
             $game_array = array();
             foreach ($tournament_details as $key => $value) 
             {
                $game_array[$value['league_id'].'|'.$value['season_id'].'|'.$value['tournament_id'].'|'.$value['sports_id']] = $value['start_date'];
             }
            //echo "<pre>";print_r($game_array);die;
            foreach ($game_array as $key => $value) 
            {
                $league_game_id = explode("|", $key);
                $league_id              = $league_game_id[0];
                $season_id        = $league_game_id[1];
                $tournament_id   = $league_game_id[2];
                $sports_id              = $league_game_id[3];
                $scheduled_date  = $value;
                
                $this->db->select("S.scheduled_date")
                        ->from(SEASON. " S")
                        ->where("S.league_id", $league_id)
                        ->where("S.season_id", $season_id)
                        ->group_start() //this will start grouping
                        ->where("S.scheduled_date >", $scheduled_date)
                        ->or_where("S.scheduled_date <", $scheduled_date)
                        ->group_end(); //this will end grouping                        
                $query = $this->db->get();
               // echo $this->db->last_query();//die;
                $new_scheduled_date = $query->row('scheduled_date');
                //echo "<pre>";print_r($new_scheduled_date);
                //echo '<pre>';print_r($season_id);die;
                if(isset($new_scheduled_date) && $new_scheduled_date != '')
                {
                    //Update collection season table
                    $this->db->set("scheduled_date",$new_scheduled_date);
                    $this->db->where("season_id",$season_id);
                    $this->db->where("tournament_id",$tournament_id);
                    $this->db->update(TOURNAMENT_SEASON);    
                    //echo $this->db->last_query();
                    //Update collection master table
                    $this->db->set("start_date",$new_scheduled_date);
                    $this->db->where("league_id",$league_id);
                    $this->db->where("tournament_id",$tournament_id);
                    $this->db->update(TOURNAMENT); 

                    //delete cache
                    if (CACHE_ENABLE) {
                      $this->load->model('user/Nodb_model');
                      $this->Nodb_model->flush_cache_data();
                    }
                    
                }    
            }
        }    
    }
	
	/**
	* Function used for the score update in the user team table according to feed data 
	* @params: session_id
	*/
	public function update_user_team_score($season_id){
		$this->db->select("S.season_id,T.tournament_id,T.is_score_predict,S.winning_team_id,S.score_data->>'$.away_score' as away_score,S.score_data->>'$.home_score' as home_score,UT.user_tournament_id,T.sports_id")
		->from(SEASON. " S")
		->join(TOURNAMENT_SEASON." AS TS", 'TS.season_id = S.season_id','INNER')
		->join(TOURNAMENT." AS T", 'T.tournament_id = TS.tournament_id','INNER')
		->join(USER_TOURNAMENT." AS UT", 'UT.tournament_id = T.tournament_id','INNER')
		->where("S.season_id",$season_id)
		->where("S.status",2);
		$query = $this->db->get();
        $season_details = $query->result_array();
		
		$pickem_config = !empty($this->app_config['allow_pickem_tournament'])?$this->app_config['allow_pickem_tournament']['custom_data']:array();
		$correct 	= isset($pickem_config['correct'])?$pickem_config['correct']:0;
		$wrong 		= isset($pickem_config['wrong'])?$pickem_config['wrong']:0;
		$win_goal 	= isset($pickem_config['winning_and_goal'])?$pickem_config['winning_and_goal']:0;
		$win_goal_diff = isset($pickem_config['winning_and_goal_difference'])?$pickem_config['winning_and_goal_difference']:0;
		$win_only = isset($pickem_config['winning_only'])?$pickem_config['winning_only']:0;
		
		if(!empty($season_details)){
			foreach($season_details as $value){

				$winning_team_id=$value['winning_team_id'];
				$user_tournament_id=$value['user_tournament_id'];
				if(!empty($value['is_score_predict']) && ($value['sports_id'] == 5)){
					
					if(is_numeric($value['away_score']) && is_numeric($value['home_score'])){
						
						$away_home=$value['away_score']-$value['home_score'];
						$home_away=$value['home_score']-$value['away_score'];
						
						$score = "CASE 
						WHEN UT.team_id=".$winning_team_id." and UT.away_predict=".$value['away_score']." and UT.home_predict=".$value['home_score']." THEN ".$win_goal." 
						WHEN UT.team_id=".$winning_team_id." and ((UT.away_predict-UT.home_predict=".$away_home.") or (UT.home_predict-UT.away_predict=".$home_away.")) THEN ".$win_goal_diff."
						WHEN UT.team_id=".$winning_team_id." THEN ".$win_only."
						ELSE 0 END";
					}else{
						$score = "CASE 
						WHEN UT.team_id=".$winning_team_id." THEN ".$win_only."
						ELSE 0 END";
					}
					
					$update_score = "UPDATE  ".$this->db->dbprefix(USER_TEAM)." as UT SET is_correct=CASE WHEN UT.team_id =".$winning_team_id." THEN 1 ELSE 2 END,score = ".$score." where user_tournament_id = ".$user_tournament_id." and UT.season_id =".$season_id."";

					// $update_score = "UPDATE  ".$this->db->dbprefix(USER_TEAM)." as UT SET is_correct=CASE WHEN UT.team_id =".$winning_team_id." THEN 1 ELSE 2 END,score = CASE 
					// WHEN UT.team_id=".$winning_team_id." and UT.away_predict=".$value['away_score']." and UT.home_predict=".$value['home_score']." THEN ".$win_goal." 
					// WHEN UT.team_id=".$winning_team_id." and ((UT.away_predict-UT.home_predict=".$away_home.") or (UT.home_predict-UT.away_predict=".$home_away.")) THEN ".$win_goal_diff."
					// WHEN UT.team_id=".$winning_team_id." THEN ".$win_only."
					// ELSE 0 END where user_tournament_id = ".$user_tournament_id." and UT.season_id =".$season_id."";
					
				}else{
					$update_score = "UPDATE  ".$this->db->dbprefix(USER_TEAM)." as UT SET score = CASE WHEN UT.team_id=".$winning_team_id." THEN ".$correct." ELSE -(".$wrong.")  END,is_correct=CASE WHEN UT.team_id =".$winning_team_id." THEN 1 ELSE 2 END where user_tournament_id = ".$user_tournament_id." and UT.season_id =".$season_id.""; //and UT.is_correct=0
				}
				$this->db->query($update_score);
			}

			$this->load->helper('queue');
			$season_ids=array($season_id);	
			$content = ['season_id'=>$season_ids];
			add_data_in_queue($content, 'pickem_score');
			
		}
	}


	/**
	 * Used for auto cancel tournament
	 * @return boolean
	 */
	public function auto_cancel_tournament()
	{
		$format= array(
		'1'=>9,  // one-day
		'2'=>120, //test
		'3'=>5, // t20
		'4'=>3, //t10
		);
		
		$current_date = format_date();
		$this->db->select("T.tournament_id,T.sports_id,T.end_date,TS.season_id,S.format",false)
		->from(TOURNAMENT." AS T ")
		->join(TOURNAMENT_SEASON." TS","TS.tournament_id = T.tournament_id","INNER")
		->join(SEASON." S","S.season_id = TS.season_id","INNER")
		->join(USER_TOURNAMENT." as UT","UT.tournament_id = T.tournament_id","LEFT")
		->where("T.status","0")
		->where("T.end_date < ",$current_date)
		->where("T.start_date < ",$current_date)
		->where("UT.tournament_id",null)
		->where("TS.scheduled_date = (SELECT MAX(TS1.scheduled_date) as scheduled_date FROM ".$this->db->dbprefix(TOURNAMENT_SEASON)." as TS1 where TS1.tournament_id = T.tournament_id)")
		->group_by("T.tournament_id");
		$sql = $this->db->get();
		$tournament_list = $sql->result_array();
		
		if(!empty($tournament_list)){
			$update_t_ids = array();
			foreach($tournament_list as $trnmt){
				
				$hours = 8;
				if($trnmt['sports_id'] == 7){ // cricket
					$hours = isset($format[$trnmt['format']])?$format[$trnmt['format']]:120;
				}else{ // other sports
					$hours = 8;
				}

				$s_scheduled_end = date("Y-m-d H:i:s", strtotime($trnmt['end_date'] . " +".$hours." hours"));
				$current_date = format_date();
				
				if($s_scheduled_end < $current_date){
					$update_t_ids[]=$trnmt['tournament_id'];
				}
			}
			
			if(!empty($update_t_ids)){
				$this->db->where_in("tournament_id",$update_t_ids);
				$this->db->update(TOURNAMENT,array('cancel_reason'=>'Insufficient participation','status'=>1,'modified_date' => $current_date));
				
				// cache delete
				$sports_list_cache_key = "pickem_tournament_sports_list";
				$this->delete_cache_data($sports_list_cache_key);
			}
		}  
		return true;
	}
}
?>