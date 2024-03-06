<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tournament_model extends MY_Model{

	public function __construct() 
    {
       	parent::__construct();
       	$this->db = $this->load->database('db_fantasy', TRUE);
    }

	/**
   	* Function used for update fantasy points and calculate rank
   	* @param void
   	* @return boolean
   	*/
	public function update_tournament_score()
	{
		$current_date = format_date();
		$this->db->select("T.tournament_id,T.sports_id,T.league_id,T.name,T.start_date,T.end_date,T.status,T.no_of_fixture,T.is_top_team,GROUP_CONCAT(DISTINCT TS.cm_id) as cm_ids,GROUP_CONCAT(DISTINCT TS.contest_id) as contest_ids")
                    ->from(TOURNAMENT." AS T")
                    ->join(TOURNAMENT_SEASON." AS TS", "TS.tournament_id = T.tournament_id", "INNER")
                    ->where("T.status","0")
                    ->where("T.start_date < ",$current_date)
                    ->group_by("T.tournament_id");
       	$sql = $this->db->get();
       	$result = $sql->result_array();
       	//echo "<pre>";print_r($result);die;
       	if(!empty($result)){
       		foreach($result as $row){
       			$tournament_id = $row['tournament_id'];
       			$no_of_fixture = $row['no_of_fixture'];
       			$is_top_team = $row['is_top_team'];
       			$cm_ids = explode(",",$row['cm_ids']);
       			$contest_ids = explode(",",$row['contest_ids']);
       			$contest_ids = array_diff($contest_ids,[0]);
       			//echo "<pre>";print_r($contest_ids);die;
       			if(!empty($contest_ids)){
	   				$this->db->select('C.collection_master_id as cm_id,C.contest_id,LMC.lineup_master_contest_id as lmc_id,LMC.lineup_master_id as lm_id,LM.team_name,LMC.total_score,LMC.game_rank,LM.user_id', FALSE)
			        	->from(CONTEST.' AS C')
				        ->join(LINEUP_MASTER_CONTEST.' AS LMC', 'LMC.contest_id = C.contest_id')
				        ->join(LINEUP_MASTER.' AS LM', 'LM.lineup_master_id = LMC.lineup_master_id')
				        ->where("LMC.fee_refund","0")
				        ->where("C.status","3")
				        ->where("C.user_id","0")
				        ->where_in("C.contest_id",$contest_ids)
		        		->order_by("LMC.contest_id","ASC")
		        		->order_by("LMC.game_rank","ASC");
	        		if($is_top_team == 1){
	       				$pre_sql = "(SELECT LMC.contest_id,LM.user_id,MAX(LMC.total_score) as total_score FROM ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER)." AS LM ON LM.lineup_master_id = LMC.lineup_master_id WHERE LMC.contest_id IN(".implode(',', $contest_ids).") AND LMC.fee_refund = '0' GROUP BY LMC.contest_id,LM.user_id)";
	       				$this->db->join($pre_sql." as TMP","TMP.user_id = LM.user_id AND TMP.contest_id = LMC.contest_id AND TMP.total_score = LMC.total_score");
	       				$this->db->group_by("LMC.contest_id");
	       				$this->db->group_by("LM.user_id");
	       				$this->db->group_by("LM.collection_master_id");
	       			}else{
	       				$this->db->group_by("LMC.lineup_master_contest_id");
	       			}
		      		$match_list = $this->db->get()->result_array();
		      		if(!empty($match_list)){
		      			//Start Transaction
			            $this->db->trans_strict(TRUE);
			            $this->db->trans_start();

		      			$user_arr = array();
		      			$user_teams = array();
				      	foreach($match_list as $user){
				      		$user_key = $tournament_id."_".$user['user_id'];
				      		if(!isset($user_arr[$user_key])){
					      		$tmp = array();
				      			$tmp['tournament_id'] = $tournament_id;
				      			$tmp['user_id'] = $user['user_id'];
				      			$tmp['total_score'] = $user['total_score'];
				      			$user_arr[$user_key] = $tmp;
				      		}else{
				      			$user_arr[$user_key]['total_score']+= $user['total_score'];
				      		}

				      		$team = array();
			      			$team['tournament_id'] = $tournament_id;
			      			$team['user_id'] = $user['user_id'];
			      			$team['cm_id'] = $user['cm_id'];
			      			$team['contest_id'] = $user['contest_id'];
			      			$team['lmc_id'] = $user['lmc_id'];
			      			$team['lm_id'] = $user['lm_id'];
			      			$team['team_name'] = $user['team_name'];
			      			$team['score'] = $user['total_score'];
			      			$team['game_rank'] = $user['game_rank'];
			      			$team['is_included'] = 1;
			      			if($no_of_fixture > 0){
			      				$team['is_included'] = 0;
			      			}
			      			$team['created_date'] = format_date();
			      			$user_teams[] = $team;

				      	}
				      	if(!empty($user_arr)){
							$data_keys = array_keys($user_arr);
							$concat_key = 'CONCAT(tournament_id,"_",user_id)';
	                		$this->insert_or_update_on_exist($data_keys,$user_arr,$concat_key,TOURNAMENT_HISTORY,'history_id');

	                		$data_keys = array_keys($user_teams);
							$concat_key = 'CONCAT(tournament_id,"_",user_id,"_",cm_id,"_",contest_id,"_",lm_id)';
	                		$this->insert_or_update_on_exist($data_keys,$user_teams,$concat_key,TOURNAMENT_HISTORY_TEAMS,'id');

	                		//update total score in lmc table
	                		if($no_of_fixture > 0){
	                			$this->db->where('tournament_id',$tournament_id);
								$this->db->update(TOURNAMENT_HISTORY_TEAMS,array("is_included"=>"0"));

	                			$update_sql = " UPDATE ".$this->db->dbprefix(TOURNAMENT_HISTORY_TEAMS)." AS THT 
				              	INNER JOIN (
				              		SELECT tournament_id,user_id,cm_id FROM (
					              		SELECT tournament_id,user_id,cm_id,
										  ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY SUM(score) DESC) as rn
										  FROM ".$this->db->dbprefix(TOURNAMENT_HISTORY_TEAMS)." 
										  WHERE tournament_id=".$tournament_id."
										  GROUP BY tournament_id,user_id,cm_id
										) tmp
									WHERE rn <= ".$no_of_fixture." 
									GROUP BY tournament_id,user_id,cm_id
				              	) AS L_PQ ON L_PQ.tournament_id = THT.tournament_id AND L_PQ.user_id = THT.user_id AND L_PQ.cm_id=THT.cm_id
				                SET THT.is_included = 1
				                WHERE THT.tournament_id=".$tournament_id;
				              	$this->db->query($update_sql);
	                		}

			              	//update total fantasy points
			              	$update_sql = " UPDATE ".$this->db->dbprefix(TOURNAMENT_HISTORY)." AS TH 
			              	LEFT JOIN (SELECT THT.tournament_id,THT.user_id,SUM(THT.score) AS scores FROM ".$this->db->dbprefix(TOURNAMENT_HISTORY_TEAMS)." AS THT WHERE THT.is_included=1 AND THT.tournament_id=".$tournament_id." GROUP BY THT.tournament_id,THT.user_id) AS L_PQ ON L_PQ.tournament_id = TH.tournament_id AND L_PQ.user_id = TH.user_id 
				                SET TH.total_score = IFNULL(L_PQ.scores,'0.00')
				                WHERE TH.tournament_id=".$tournament_id;
			              	$this->db->query($update_sql);

					        $rank_sql = "UPDATE ".$this->db->dbprefix(TOURNAMENT_HISTORY)." AS TH INNER JOIN (SELECT TH1.history_id,RANK() OVER (ORDER BY TH1.total_score DESC) game_rank FROM ".$this->db->dbprefix(TOURNAMENT_HISTORY)." AS TH1 WHERE TH1.tournament_id = ".$tournament_id.") AS L_PQ ON L_PQ.history_id = TH.history_id 
					        SET TH.rank_value = IFNULL(L_PQ.game_rank,'0') WHERE TH.tournament_id=".$tournament_id;
		                  	$this->db->query($rank_sql);

							$this->db->where('tournament_id',$tournament_id);
							$this->db->update(TOURNAMENT,array("modified_date"=>format_date()));
				      	}

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
		return true;
	}

	/**
   	* Function used for update tournament status
   	* @param void
   	* @return boolean
   	*/
	public function update_tournament_status()
	{
		$current_date = format_date();
		$end_date = date('Y-m-d H:i:s', strtotime($current_date. ' -2 hours'));
		$this->db->select("T.tournament_id,T.sports_id,T.league_id,T.name,T.start_date,T.end_date,T.prize_detail,T.status,GROUP_CONCAT(DISTINCT TS.season_id) as season_ids")
                    ->from(TOURNAMENT." AS T")
                    ->join(TOURNAMENT_SEASON." AS TS", "TS.tournament_id = T.tournament_id", "INNER")
                    ->where("T.status","0")
                    ->where("T.end_date < ",$end_date)
                    ->group_by("T.tournament_id");
       	$sql = $this->db->get();
       	$result = $sql->result_array();
		//echo "<pre>";print_r($result);die;
		foreach($result as $row){
			$season_ids = explode(",",$row['season_ids']);
			$tournament_id = $row['tournament_id'];
			$start_date = $row['start_date'];
			$end_date = $row['end_date'];
			//get match and collection
			$this->db->select('CM.collection_master_id,S.season_id', FALSE)
		        	->from(SEASON.' AS S')
			        ->join(COLLECTION_SEASON.' AS CS', 'CS.season_id = S.season_id')
			        ->join(COLLECTION_MASTER.' AS CM', 'CM.collection_master_id = CS.collection_master_id AND CM.league_id=S.league_id')
			        ->join(CONTEST.' AS C', 'C.collection_master_id = CM.collection_master_id AND C.user_id = 0 AND C.status != 0')
			        ->where("CM.season_game_count","1")
			        ->where("CM.is_lineup_processed > ","0")
			        ->where("CM.status","1")
			        ->where("S.status","2")
			        ->where_in("S.season_id",$season_ids)
	        		->group_by("CM.collection_master_id");
	      	$match_list = $this->db->get()->result_array();
	      	//echo "<pre>";print_r($match_list);die;
	      	if(count($season_ids) == count($match_list)){
	      		$status = 2;
	      		$cancel_reason = '';
				$row['prize_detail'] = json_decode($row['prize_detail'],TRUE);
				if(empty($row['prize_detail'])){
					$status = 3;
				}

				// check if join 
				$this->db->select("TH.tournament_id")
					->from(TOURNAMENT_HISTORY." AS TH")
					->where('TH.tournament_id',$tournament_id);
				$t_h_sql = $this->db->get()->result_array();
				if(empty($t_h_sql)){
					$status = 1;
					$cancel_reason = 'Insufficient participation';
				}

				$this->db->where('tournament_id',$tournament_id);
				$this->db->where('status',"0");
				$this->db->update(TOURNAMENT,array("status"=>$status,'cancel_reason'=>$cancel_reason,"modified_date"=>format_date()));
	      	}
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
		$this->db->select("T.tournament_id,T.prize_detail,T.status,COUNT(TH.history_id) as total_joined")
                    ->from(TOURNAMENT." AS T")
                    ->join(TOURNAMENT_HISTORY.' AS TH', 'TH.tournament_id = T.tournament_id','LEFT')
                    ->where("T.status",2)
                    ->where("T.end_date < ",$current_date)
                    ->group_by('T.tournament_id')
                    ->order_by('T.tournament_id','DESC');
       	$sql = $this->db->get();
       	$result = $sql->result_array();
		//echo "<pre>";print_r($result);die;
      	if(!empty($result))
      	{
        	$this->load->helper('queue');
        	$server_name = get_server_host_name();
        	foreach($result AS $key => $prize)
        	{
        		if($prize['total_joined'] == 0){
        			$this->db->where('status',"2");
                    $this->db->where('tournament_id',$prize['tournament_id']);
                    $this->db->update(TOURNAMENT, array('status'=>'3','is_notify'=>1,'modified_date'=>format_date()));
        		}else{
	          		$content = array();
	        		$content['url'] = $server_name."/cron/tournament/prize_distribution/".$prize['tournament_id'];
	          		add_data_in_queue($content,'dfs_tournament');
        		}
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
	        //echo "=====<pre>";print_r($wining_amount);die;
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
	      	//echo "<pre>";print_r($winning_amount_arr);
		    //get winners
			$winners = $this->db->select('TH.*', FALSE)
							->from(TOURNAMENT_HISTORY.' AS TH')
							->where('TH.tournament_id', $tournament_id)
							->order_by("TH.rank_value","ASC")
							->limit($winner_places)
							->get()
							->result_array();
			//echo "<pre>";print_r($winners);die;
			$user_game_data = array();
	        $user_txn_data = array();
	        $source = 372;
	        if(!empty($winners))
	        {
	          	foreach ($winners as $key => $value)
	          	{
		            $is_winner = 0;
		            $bonus_amount = $winning_amount = $coin = 0;
		            $prize_obj = $winning_amount_arr[$value['rank_value']];
		            $custom_data = array("tournament"=>$tournament_name);
	            	if($prize_obj['prize_type'] == 0){
	            		$is_winner = 1;
						$bonus_amount = $prize_obj['amount'];
					}else if($prize_obj['prize_type'] == 1){
	            		$is_winner = 1;
						$winning_amount = $prize_obj['amount'];
					}else if($prize_obj['prize_type'] == 2){
	            		$is_winner = 1;
						$prize_obj['amount'] = ceil($prize_obj['amount']);
						$coin = $prize_obj['amount'];
					}else if($prize_obj['prize_type'] == 3){
						$is_winner = 1;
						$custom_data['merchandise'] = $prize_obj['name'];
					}
		            $ldb_data = array();
		            $ldb_data['tournament_id'] = $value['tournament_id'];
		            $ldb_data['user_id'] = $value['user_id'];
		            $ldb_data['is_winner'] = $is_winner;
		            $ldb_data['prize_data'] = json_encode($prize_obj);
		            $user_game_data[] = $ldb_data;

		            //user txn data
                    $order_data = array();
                    $order_data["order_unique_id"] = $this->_generate_order_unique_key();
                    $order_data["user_id"] = $value['user_id'];
                    $order_data["source"] = $source;
                    $order_data["source_id"] = $value['history_id'];
                    $order_data["reference_id"] = $tournament_id;
                    $order_data["season_type"] = 1;
                    $order_data["type"] = 0;
                    $order_data["status"] = 0;
                    $order_data["real_amount"] = 0;
                    $order_data["bonus_amount"] = $bonus_amount;
                    $order_data["winning_amount"] = $winning_amount;
                    $order_data["points"] = ceil($coin);
                    $order_data["custom_data"] = json_encode($custom_data);
                    $order_data["date_added"] = format_date();
                    $order_data["modified_date"] = format_date();
                    $user_txn_data[$tournament_id][] = $order_data;
	          	}
	        }
	        //echo "<pre>";print_r($user_txn_data);die;
	        if(!empty($user_txn_data)){
                $result = $this->winning_credit($user_txn_data,$source);
                if($result){
                    try
                    {
                        $this->db = $this->load->database('db_fantasy', TRUE);
                        //Start Transaction
                        $this->db->trans_strict(TRUE);
                        $this->db->trans_start();
                  
                        $tournament_ids = array_unique(array_column($user_game_data,"tournament_id"));
                        $user_game_arr = array_chunk($user_game_data, 999);
                        foreach($user_game_arr as $game_data){
                            $this->replace_into_batch(TOURNAMENT_HISTORY, $game_data);
                        }

                        // Game table update is_prize_distributed 1
                        $this->db->where('status',"2");
                        $this->db->where('tournament_id',$tournament_id);
                        $this->db->update(TOURNAMENT, array('status' => '3', 'modified_date' => format_date()));

                        //Trasaction End
                        $this->db->trans_complete();
                        if ($this->db->trans_status() === FALSE )
                        {
                            $this->db->trans_rollback();
                        }
                        else
                        {
                            $this->db->trans_commit();

                            $this->load->helper('queue');
			              	$server_name = get_server_host_name();
			              	$content = array();
				            $content['url'] = $server_name."/cron/tournament/tds_process/".$tournament_id;
				            add_data_in_queue($content,'dfs_tds');
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
   	* Function used for sent prize winning notification
   	* @param void
   	* @return boolean
   	*/
  	public function prize_notification()
  	{
		$result = $this->db->select('T.tournament_id,T.name,T.start_date,T.end_date', FALSE)
                    ->from(TOURNAMENT.' AS T')
					->where('T.status', 3)
					->where('T.is_notify', 0)
					->order_by('T.tournament_id','DESC')
					->get()
					->result_array();
      	//echo "<pre>";print_r($result);die;
      	if(!empty($result))
      	{
        	foreach ($result as $tournament)
        	{
				$notify_data = array();
  				//get winners
				$winners = $this->db->select('TH.*', FALSE)
								->from(TOURNAMENT_HISTORY.' AS TH')
								->where('TH.tournament_id', $tournament['tournament_id'])
								->where('TH.is_winner', '1')
								->order_by("TH.rank_value","ASC")
								->get()
								->result_array();
				//echo "<pre>";print_r($winners);
				if(!empty($winners)){
					foreach($winners as $row){
						$content = array("tournament_id"=>$tournament['tournament_id'],"name"=>$tournament['name']);
	                    $temp_data = array();
	                    $temp_data['to'] = "";
	                    $temp_data['notification_type'] = "472";
	                    $temp_data['notification_destination'] = "1";//inapp
	                    $temp_data['user_id'] = $row['user_id'];
	                    $temp_data['source_id'] = $row['history_id'];
	                    $temp_data['content'] = json_encode($content);
	                    $temp_data['added_date'] = format_date();
	                    $temp_data['modified_date'] = format_date();
	                    $notify_data[$row['tournament_id']."_".$row['user_id']] = $temp_data;

		            }
				}
				//echo "<pre>";print_r($notify_data);die;
                // tournament table update is_notify 1 for winning
                $this->db->where('tournament_id',$tournament['tournament_id']);
                $this->db->where('status',"3");
                $this->db->where('is_notify',"0");
                $this->db->update(TOURNAMENT, array('is_notify'=>'1','modified_date'=>format_date()));

                //echo "<pre>";print_r($notify_data);die;
		        if(!empty($notify_data)){
		        	$this->load->model('notification/Notify_nosql_model');
		            foreach($notify_data as $notify){
		        		$this->Notify_nosql_model->send_notification($notify);
		            }
		        }
        	}
      	}
      	return true;
  	}

  	/**
     * Used for refund contest entry
     * @param array $contest
     * @return array
     */
    public function winning_credit($user_txn_data,$source)
    {
        if(empty($user_txn_data) || empty($source)){
            return false;
        }
        $this->db = $this->load->database('db_user', TRUE);
        $reference_ids = array_keys($user_txn_data);
        $check_winning = $this->db->select('reference_id')
                            ->from(ORDER)
                            ->where_in('reference_id', $reference_ids)
                            ->where('status', 1)
                            ->where('source', $source)
                            ->group_by("reference_id")
                            ->get()
                            ->row_array();
        if(!empty($check_winning)){
            $check_winning = array_fill_keys($check_winning,"0");
            $user_txn_data = array_diff_key($user_txn_data,$check_winning);
        }
        //echo "<pre>";print_r($user_txn_data);die;
        if(!empty($user_txn_data)){
            try
            {
                //Start Transaction
                $this->db->trans_strict(TRUE);
                $this->db->trans_start();

                $current_date = format_date();
                foreach($user_txn_data as $contest_txn){
                    $user_txn_arr = array_chunk($contest_txn, 999);
                    foreach($user_txn_arr as $txn_data){
                        $this->insert_ignore_into_batch(ORDER, $txn_data);
                    }
                }

                $ctst_ids_arr = array_chunk($reference_ids, 300);
                foreach($ctst_ids_arr as $cnts_ids){
                    $reference_ids_str = implode(",", $cnts_ids);
                    $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = ".$source." AND type=0 AND status=0 AND reference_id IN (".$reference_ids_str.") GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
                    SET U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
                    WHERE O.source = ".$source." AND O.type=0 AND O.status=0 AND O.reference_id IN (".$reference_ids_str.") ";
                    $this->db->query($bal_sql);
                }
                //Trasaction End
                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE )
                {
                    $this->db->trans_rollback();
                    return false;
                }
                else
                {
                    $this->db->trans_commit();

                    //remove users balance cache
                    $ids = array_unique(array_column($user_txn_data,"user_id"));
                    $this->remove_user_balance_cache($ids);
                    return true;
                }
            } catch (Exception $e)
            {
                $this->db->trans_rollback();
                return false;
            }
        }
        return true;
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
    $this->db->select("T.tournament_id,T.sports_id, TS.season_id,S.format,T.end_date,TS.season_scheduled_date",false)
    ->from(TOURNAMENT." AS T ")
    ->join(TOURNAMENT_SEASON." TS","TS.tournament_id = T.tournament_id","INNER")
    ->join(SEASON." S","S.season_id = TS.season_id","INNER")
    ->join(TOURNAMENT_HISTORY." as TH","TH.tournament_id = T.tournament_id","LEFT")
    ->where("TS.season_scheduled_date = (SELECT MAX(TS1.season_scheduled_date) as season_scheduled_date FROM ".$this->db->dbprefix(TOURNAMENT_SEASON)." as TS1 where TS1.tournament_id = T.tournament_id)")
    ->where("T.status","0")
    ->where("T.end_date < ",$current_date)
    ->where("T.start_date < ",$current_date)
    ->where("TH.tournament_id",null)
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
      
        // cache data delete
        $tmnt_sports_list_cache_key = "dfs_tournament_sports_list";
        $this->delete_cache_data($tmnt_sports_list_cache_key);
        
        foreach($update_t_ids as $torunmt){
          $cache_key = "tournament_".$torunmt;
          $this->delete_cache_data($cache_key);
        }
      }
    }  
    return true;
  }

    /**
   	* Function used for queue process for the torunament id according
   	* @param void
   	* @return boolean
   	*/
  	public function tournament_history_teams_queue()
	{
		$this->db->select("TH.tournament_id")
			->from(TOURNAMENT_HISTORY." AS TH")
			->where('TH.custom_data !=',Null)
			->group_by("TH.tournament_id");
       	$sql = $this->db->get();
		$result = $sql->result_array();	
		if(!empty($result)){
			
			$this->load->helper('queue');
        	$server_name = get_server_host_name();

			foreach($result as $row){
				$content = array();
				$content['url'] = $server_name."/cron/tournament/update_tournament_history_teams/".$row['tournament_id'];
				add_data_in_queue($content,'dfs_tournament');
			}
		}

	}
  	/**
   	* Function used for update tournament data in history teams
   	* @param void
   	* @return boolean
   	*/
	public function update_tournament_history_teams($trnt_id)
	{
		$this->db->select("TH.history_id,TH.tournament_id,TH.user_id,TH.custom_data")
                    ->from(TOURNAMENT_HISTORY." AS TH")
                    ->where('TH.tournament_id',$trnt_id)
					->where('TH.custom_data !=',Null);
       	$sql = $this->db->get();
       	$result = $sql->result_array();
       	$team = array();
		$user_teams = array();
		if(!empty($result)){
			foreach($result as $row){
				$custom_data = json_decode($row['custom_data']);
				$match_data = $this->get_user_match_list($custom_data);
				
				foreach($match_data as $match_row){

					$team['tournament_id'] = $row['tournament_id'];
					$team['user_id'] 	= $row['user_id'];
					$team['cm_id'] 		= $match_row['cm_id'];
					$team['contest_id'] = $match_row['contest_id'];
					$team['lmc_id'] 	= $match_row['lmc_id'];
					$team['lm_id'] 		= $match_row['lm_id'];
					$team['team_name'] 	= $match_row['team'];
					$team['score'] 		= $match_row['score'];
					$team['game_rank'] 	= $match_row['game_rank'];
					$team['is_included'] = 1;
					$team['created_date'] = format_date();
					$user_teams[] = $team;
				}
			}
		}

		if(!empty($user_teams)){
			$data_keys = array_keys($user_teams);
			$concat_key = 'CONCAT(tournament_id,"_",user_id,"_",cm_id,"_",contest_id,"_",lm_id)';
			$this->insert_or_update_on_exist($data_keys,$user_teams,$concat_key,TOURNAMENT_HISTORY_TEAMS,'id');
		}
		

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
    * Function used for get user match list
    * @param array $post_data
    * @return string
    */
    private function get_user_match_list($custom_data){
        $this->db->select("LM.lineup_master_id as lm_id,LM.collection_master_id as cm_id,LMC.lineup_master_contest_id as lmc_id,LMC.total_score as score,CM.collection_name as name,CM.season_scheduled_date,LM.team_name as team,LMC.game_rank,LMC.contest_id", FALSE)
                    ->from(LINEUP_MASTER.' AS LM')
                    ->join(LINEUP_MASTER_CONTEST.' AS LMC', 'LMC.lineup_master_id = LM.lineup_master_id')
                    ->join(COLLECTION_MASTER.' AS CM', 'CM.collection_master_id = LM.collection_master_id')
                    ->where_in("CONCAT(LM.collection_master_id,'_',LM.lineup_master_id)",$custom_data);
                    
        $this->db->group_by("CM.collection_master_id");
        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
	* Function used for process match tds
	* @param void
	* @return boolean
	*/
	public function push_tournament_tds()
	{
		$tds = $this->app_config['allow_tds']['key_value'] && isset($this->app_config['allow_tds']['custom_data']) ? $this->app_config['allow_tds']['custom_data'] : array();
		if(empty($tds)){
	  		return false;
		}
		$fy_tds_date = "2023-04-01 00:00:00";
		//echo "<pre>";print_r($tds);
		$current_date = format_date();
		$this->db->select("T.tournament_id,T.sports_id,T.name,T.start_date,T.status", FALSE)
		    ->from(TOURNAMENT.' AS T')
		    ->join(TOURNAMENT_HISTORY." AS TH", "TH.tournament_id = T.tournament_id", "INNER")
		    ->where('T.status',3)
		    ->where('T.start_date >= ', $fy_tds_date)
		    ->where('T.end_date < ', $current_date)
		    ->group_by("T.tournament_id");
		$result = $this->db->get()->result_array();
		//echo "<pre>======";print_r($result);die;
		if(!empty($result)){
			$this->load->helper('queue');
	      	$server_name = get_server_host_name();
			foreach($result as $row){
	          	$content = array();
	            $content['url'] = $server_name."/cron/tournament/tds_process/".$row['tournament_id'];
	            add_data_in_queue($content,'dfs_tds');
			}
		}
		return true;
	}

	/**
	* Function used for process tournament tds
	* @param int $tournament_id
	* @return boolean
	*/
	public function tds_process($tournament_id)
	{
		if(!$tournament_id){
		  return false;
		}
		$tds = $this->app_config['allow_tds']['key_value'] && isset($this->app_config['allow_tds']['custom_data']) ? $this->app_config['allow_tds']['custom_data'] : array();
		if(empty($tds)){
	  		return false;
		}
		$fy_tds_date = "2023-04-01 00:00:00";
		//echo "<pre>";print_r($tds);
		$current_date = format_date();
		$this->db->select("T.tournament_id,T.sports_id,T.name,T.start_date,T.status", FALSE)
		    ->from(TOURNAMENT.' AS T')
		    ->join(TOURNAMENT_HISTORY." AS TH", "TH.tournament_id = T.tournament_id", "INNER")
		    ->where('T.status',3)
		    ->where('T.tournament_id', $tournament_id)
		    ->where('T.start_date >= ', $fy_tds_date)
		    ->where('T.end_date < ', $current_date)
		    ->group_by("T.tournament_id");
		$result = $this->db->get()->row_array();
		//echo "<pre>======";print_r($result);die;
		if(!empty($result)){
			$module_type = 2;
			$sports_id = $result['sports_id'];
			$entity_id = $result['tournament_id'];
			$entity_name = $result['name'];
			$scheduled_date = $result['start_date'];
			$this->db_user->select("O.user_id,0 as total_entry,IFNULL(SUM(O.winning_amount),0) as total_winning");
			$this->db_user->from(ORDER." AS O");
			$this->db_user->where("status", 1);
			$this->db_user->where("source",372);
			$this->db_user->where("winning_amount >",0);
			$this->db_user->where("reference_id",$entity_id);
			$this->db_user->group_by("O.user_id");
			$this->db_user->order_by("O.user_id","ASC");
			$sql = $this->db_user->get();
			$user_list = $sql->result_array();
			//echo "<pre>";print_r($user_list);die;
			$tds_report = array();
			$tds_txn = array();
		  	foreach($user_list as $row){
			    $row['net_winning'] = $row['total_winning'] - $row['total_entry'];
			    $tmp_arr = array();
			    $tmp_arr['module_type'] = $module_type;
			    $tmp_arr['user_id'] = $row['user_id'];
			    $tmp_arr['sports_id'] = $sports_id;
			    $tmp_arr['entity_id'] = $entity_id;
			    $tmp_arr['entity_name'] = $entity_name;
			    $tmp_arr['scheduled_date'] = $scheduled_date;
			    $tmp_arr['total_entry'] = $row['total_entry'];
			    $tmp_arr['total_winning'] = $row['total_winning'];
			    $tmp_arr['net_winning'] = $row['net_winning'];
			    $tmp_arr['status'] = 0;
			    $tmp_arr['date_added'] = $current_date;
			    $tds_report[] = $tmp_arr;
		    	if(isset($tds['indian']) && $tds['indian'] == 0 && $row['net_winning'] >= $tds['amount']){
		      		$tds_amount = number_format((($row['net_winning'] * $tds['percent']) / 100),2,'.','');
					if($tds_amount > 0){
						$tmp_tds = array();
						$tmp_tds['user_id'] = $row['user_id'];
						$tmp_tds['amount'] = $tds_amount;
						$tmp_tds['tds'] = $tds_amount;
						$tmp_tds['source'] = 130;
						$tmp_tds['source_id'] = $entity_id;
						$tmp_tds['custom_data'] = array("name"=>$entity_name,"tds_rate"=>$tds['percent'],"net_winning"=>$row['net_winning'],"type"=>"DFS Tournament");
						$tds_txn[] = $tmp_tds;
					}
		    	}
		  	}
		  	//echo "<pre>";print_r($tds_report);die;
	  		if(!empty($tds_report)){
		    	try{
		      		$this->db = $this->db_user;
					//Start Transaction
					$this->db->trans_strict(TRUE);
					$this->db->trans_start();
		      
					$tds_report_arr = array_chunk($tds_report, 999);
					foreach($tds_report_arr as $tds_txn_data){
						$this->insert_ignore_into_batch(USER_TDS_REPORT, $tds_txn_data);
					}

					$netwin_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(USER_TDS_REPORT)." AS UT ON UT.user_id=U.user_id 
					  SET U.net_winning = (U.net_winning + UT.net_winning),UT.status=1 
					  WHERE UT.status=0 AND UT.module_type = ".$module_type." AND UT.entity_id=".$entity_id;
					$this->db->query($netwin_sql);

		      		if(!empty($tds_txn)){
						foreach($tds_txn as $tds_row){
							$check_exist = $this->get_single_row('order_id',ORDER, array("source"=>$tds_row['source'],"user_id"=>$tds_row['user_id'],"source_id"=>$tds_row['source_id']));
							if(empty($check_exist)){
								$tds_row["order_unique_id"] = $this->_generate_order_unique_key();
								$tds_row["type"] = 1;
								$tds_row["status"] = 0;
								$tds_row["real_amount"] = 0;
								$tds_row["bonus_amount"] = 0;
								$tds_row["winning_amount"] = $tds_row['amount'];
								$tds_row["points"] = 0;
								$tds_row["custom_data"] = json_encode($tds_row['custom_data']);
								$tds_row["date_added"] = $current_date;
								$tds_row["modified_date"] = $current_date;
								unset($tds_row['amount']);
								$this->db->insert(ORDER,$tds_row);
								$order_id = $this->db->insert_id();
								if($order_id){
							  		$bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id 
							  			SET U.winning_balance = (U.winning_balance - O.winning_amount),O.status=1 
							  			WHERE O.source = ".$tds_row['source']." AND O.status=0 AND O.order_id=".$order_id;
							  		$this->db->query($bal_sql);
								}
							}
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
			    }
			    catch(Exception $e){
		      		$this->db->trans_rollback();
			    }
	  		}
		}
		return true;
	}
}
?>