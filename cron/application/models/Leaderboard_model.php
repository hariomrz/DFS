<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Leaderboard_model extends MY_Model{

	public $db_user;
	public $db_fantasy ;
	public function __construct(){
		parent::__construct();
		$this->db_user = $this->load->database('db_user',TRUE);
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
	}

	/**
     * Used for generate order unique id
     * @return string
     */
    public function _generate_order_unique_key() {
        $this->load->helper('security');
        $salt = do_hash(time() . mt_rand());
        $new_key = substr($salt, 0, 20);
        return $new_key;
    }

    /**
   	* Function used for count referral users and calculate rank
   	* @param int $category_id
   	* @return boolean
   	*/
	public function save_referral_leaderboard($category_id)
	{
		if(!$category_id){
			return false;
		}

		$current_date = format_date();
		$prize_list = $this->db_user->select('*')
							->from(LEADERBOARD_PRIZE)
							->where('category_id',$category_id)
							->where('status',1)
							->get()->result_array();
		//echo "<pre>";print_r($prize_list);die;
		foreach($prize_list as $prize){
			$name = "";
			$entity_no = "";
			$start_date = "";
			$end_date = "";
			if($prize['type'] == $this->type_weekly){
				list($start_date, $end_date) = x_week_range($current_date);
				$entity_no = date("W",strtotime($start_date));
				$name = "Week ".$entity_no;
			}else if($prize['type'] == $this->type_month){
				$start_date = date('Y-m-01',strtotime($current_date)).' 00:00:00';
				$end_date = date('Y-m-t',strtotime($current_date)).' 23:59:59';
				$entity_no = date("m",strtotime($start_date));
				$name = "Month ".$entity_no;
			}
			//echo "<br/>".$entity_no."===".$start_date."==".$end_date;die;
			if($entity_no != "" && $start_date != "" && $end_date != ""){
				$prize_date = date("Y-m-d",strtotime($start_date));
				$check_exist = $this->db_user->select('*')
									->from(LEADERBOARD)
									->where('prize_id',$prize['prize_id'])
									->where('prize_date',$prize_date)
									->where('entity_no',$entity_no)
									->get()->row_array();
				$status = 0;
				if(empty($check_exist)){
					$data_arr = array();
					$data_arr['prize_id'] = $prize['prize_id'];
					$data_arr['name'] = $name;
					$data_arr['prize_detail'] = $prize['prize_detail'];
					$data_arr['prize_date'] = $prize_date;
					$data_arr['entity_no'] = $entity_no;
					$data_arr['start_date'] = $start_date;
					$data_arr['end_date'] = $end_date;
					$this->db_user->insert(LEADERBOARD,$data_arr);
					$leaderboard_id = $this->db_user->insert_id();
				}else{
					$leaderboard_id = $check_exist['leaderboard_id'];
					$status = $check_exist['status'];
				}

				//save record in history table
				if($leaderboard_id && $status == 0){
					$sql = "SELECT '{$leaderboard_id}' as leaderboard_id,UAH.user_id,COUNT(UAH.user_affiliate_history_id)total_value,(RANK() OVER (ORDER BY COUNT(UAH.user_affiliate_history_id) DESC, MIN(created_date) ASC)) as rank_value 
						FROM ".$this->db_user->dbprefix(USER_AFFILIATE_HISTORY)." UAH 
						WHERE 
							UAH.affiliate_type in(1,19,20,21) 
							AND UAH.created_date >= '{$start_date}' 
							AND UAH.created_date <= '{$end_date}' 
						GROUP BY user_id 
						ORDER BY COUNT(user_affiliate_history_id) DESC";
					$query = $this->db_user->query($sql);
      				$user_list = $query->result_array();
						
					//$insert_sql = "INSERT INTO " . $this->db->dbprefix(LEADERBOARD_HISTORY) . " (leaderboard_id,user_id,total_value,rank_value) $sql ON DUPLICATE KEY UPDATE leaderboard_id=VALUES(leaderboard_id),user_id=VALUES(user_id)";
					//$this->db_user->query($insert_sql);
					$user_arr = array();
			      	foreach($user_list as $user){
			      		$tmp = array();
		      			$tmp['leaderboard_id'] = $user['leaderboard_id'];
		      			$tmp['user_id'] = $user['user_id'];
		      			$tmp['total_value'] = $user['total_value'];
		      			$tmp['rank_value'] = $user['rank_value'];
		      			$user_arr[$user['user_id']] = $tmp;
			      	}
			      	if(!empty($user_arr)){
						$this->db = $this->db_user;

						$user_arr = array_values($user_arr);
			      		$this->replace_into_batch(LEADERBOARD_HISTORY, $user_arr);

						//reset primary key
						$this->set_auto_increment_key(LEADERBOARD_HISTORY,'history_id');
					}
				}
			}
		}
		return true;
	}

	/**
   	* Function used for update referral leaderboard status
   	* @param int $category_id
   	* @return boolean
   	*/
	public function update_referral_leaderboard_status($category_id)
	{
		if(!$category_id){
			return false;
		}
		$current_date = format_date();
		$end_date = date('Y-m-d H:i:s', strtotime($current_date. ' -2 hours'));
		$result = $this->db_user->select('L.leaderboard_id,LP.prize_id,LP.allow_prize,LP.prize_detail', FALSE)
				->from(LEADERBOARD_PRIZE.' AS LP')
				->join(LEADERBOARD." AS L", "L.prize_id = LP.prize_id", "INNER")
				->where('LP.category_id',$category_id)
				->where('LP.status',1)
				->where('L.status < ', 2)
		        ->where("L.end_date < ",$end_date)
				->order_by('L.end_date','DESC')
				->get()->result_array();
		//echo "<pre>";print_r($result);die;
		foreach($result as $row){
			$update_arr = array('status' => 2);
			$row['prize_detail'] = json_decode($row['prize_detail'],TRUE);
			if($row['allow_prize'] == "0" || empty($row['prize_detail'])){
				$update_arr['status'] = 3;
			}
			$this->db_user->where('leaderboard_id',$row['leaderboard_id']);
			$this->db_user->where('prize_id',$row['prize_id']);
			$this->db_user->update(LEADERBOARD,$update_arr);
		}
		return true;
	}

	/**
   	* Function used for update fantasy points and calculate rank
   	* @param int $category_id
   	* @return boolean
   	*/
	public function save_fantasy_leaderboard($category_id)
	{
		if(!$category_id){
			return false;
		}

		$current_date = format_date();
		$prize_list = $this->db_user->select('*')
							->from(LEADERBOARD_PRIZE)
							->where('category_id',$category_id)
							->where('status',1)
							->get()->result_array();
		//echo "<pre>";print_r($prize_list);die;
		foreach($prize_list as $prize){
			$name = "";
			$entity_no = "";
			$start_date = "";
			$end_date = "";
			if($prize['type'] == $this->type_weekly){
				list($start_date, $end_date) = x_week_range($current_date);
				$entity_no = date("W",strtotime($start_date));
				$name = "Week ".$entity_no;
			}else if($prize['type'] == $this->type_month){
				$start_date = date('Y-m-01',strtotime($current_date)).' 00:00:00';
				$end_date = date('Y-m-t',strtotime($current_date)).' 23:59:59';
				$entity_no = date("m",strtotime($start_date));
				$name = "Month ".$entity_no;
			}else if($prize['type'] == $this->type_league){
				$league_info = $this->db_fantasy->select('*')
									->from(LEAGUE)
									->where('league_id',$prize['reference_id'])
									->get()->row_array();
				if(empty($league_info)){
					continue;
				}

				$start_date = $league_info['league_schedule_date'];
				$end_date = $league_info['league_last_date'];
				$entity_no = $league_info['league_id'];
				$name = $league_info['league_display_name'];
			}
			//echo "<br/>".$name."===".$entity_no."===".$start_date."==".$end_date;die;
			if($entity_no != "" && $start_date != "" && $end_date != ""){
				$prize_date = date("Y-m-d",strtotime($start_date));
				$check_exist = $this->db_user->select('*')
									->from(LEADERBOARD)
									->where('prize_id',$prize['prize_id'])
									->where('prize_date',$prize_date)
									->where('entity_no',$entity_no)
									->get()->row_array();
				$status = 0;
				if(empty($check_exist)){
					$data_arr = array();
					$data_arr['prize_id'] = $prize['prize_id'];
					$data_arr['name'] = $name;
					$data_arr['prize_detail'] = $prize['prize_detail'];
					$data_arr['prize_date'] = $prize_date;
					$data_arr['entity_no'] = $entity_no;
					$data_arr['start_date'] = $start_date;
					$data_arr['end_date'] = $end_date;
					$this->db_user->insert(LEADERBOARD,$data_arr);
					$leaderboard_id = $this->db_user->insert_id();
				}else{
					$leaderboard_id = $check_exist['leaderboard_id'];
					$status = $check_exist['status'];
					//update end date if extend league schedule
					if($prize['type'] == $this->type_league && $check_exist['end_date'] != $end_date){
						$this->db_user->where('leaderboard_id', $leaderboard_id);
				  		$this->db_user->update(LEADERBOARD, array('end_date' => $end_date));
					}
				}

				//echo "<br/>".$leaderboard_id."===".$status;die;
				//save record in history table
				if($leaderboard_id && $status == 0){
					//get match and collection
					$this->db_fantasy->select('S.season_game_uid,S.season_scheduled_date,S.league_id,CM.collection_master_id', FALSE)
					        	->from(SEASON.' AS S')
						        ->join(COLLECTION_SEASON.' CS','CS.season_id = S.season_id','INNER')
						        ->join(COLLECTION_MASTER.' AS CM', 'CM.collection_master_id = CS.collection_master_id AND CM.league_id=S.league_id')
						        ->join(CONTEST.' AS C', 'C.collection_master_id = CM.collection_master_id AND C.entry_fee > 0 AND C.user_id = 0 AND C.status = 3 AND C.is_reverse = 0')
						        ->where("CM.season_game_count","1")
						        ->where("CM.is_lineup_processed > ","0")
						        ->where("S.status","2")
						        ->where("S.status_overview","4")
						        ->where("S.season_scheduled_date >=",$start_date)
						        ->where("S.season_scheduled_date <=",$end_date);
			        if($prize['type'] == $this->type_league && $prize['reference_id'] > 0){
			        	$this->db_fantasy->where('S.league_id', $prize['reference_id']);
			        }
			        $this->db_fantasy->group_by("CM.collection_master_id");
			      	$match_list = $this->db_fantasy->get()->result_array();
			      	//echo "<pre>";print_r($match_list);die;
			      	if(!empty($match_list)){
				      	$collection_ids = array_column($match_list,'collection_master_id');
				      	//echo "<pre>";print_r($collection_ids);

				      	//collection users score
				      	$pre_sql = "SELECT LM.user_id,LM.collection_master_id,MAX(LMC.total_score) as total_score 
				      				FROM ".$this->db_fantasy->dbprefix(CONTEST)." AS C 
				      				INNER JOIN ".$this->db_fantasy->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC ON LMC.contest_id = C.contest_id 
				      				INNER JOIN ".$this->db_fantasy->dbprefix(LINEUP_MASTER)." AS LM ON LM.lineup_master_id = LMC.lineup_master_id 
				      				WHERE C.collection_master_id IN(".implode(',', $collection_ids).") AND C.status = '3' AND C.user_id = '0' AND C.entry_fee > '0' AND C.is_reverse = '0' 
				      				GROUP BY C.collection_master_id,LM.user_id";

	      				$sql = "SELECT LM.user_id,LM.lineup_master_id,MAX(LMC.total_score) as total_score,LM.date_added  
				      				FROM ".$this->db_fantasy->dbprefix(CONTEST)." AS C 
				      				INNER JOIN ".$this->db_fantasy->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC ON LMC.contest_id = C.contest_id 
				      				INNER JOIN ".$this->db_fantasy->dbprefix(LINEUP_MASTER)." AS LM ON LM.lineup_master_id = LMC.lineup_master_id 
				      				INNER JOIN (".$pre_sql.") AS TMP ON TMP.user_id = LM.user_id AND TMP.collection_master_id = LM.collection_master_id AND TMP.total_score = LMC.total_score 
				      				WHERE C.collection_master_id IN(".implode(',', $collection_ids).") AND C.status = '3' AND C.user_id = '0' AND C.entry_fee > '0' AND C.is_reverse = '0' 
				      				GROUP BY C.collection_master_id,LM.user_id";

	      				$final_sql = "SELECT user_id,GROUP_CONCAT(lineup_master_id) as team_ids,SUM(total_score) as total_value,RANK() OVER (ORDER BY SUM(total_score) DESC, MIN(date_added) ASC) rank_value FROM (".$sql.") as q GROUP BY user_id";
	      				$query = $this->db_fantasy->query($final_sql);
	      				$user_list = $query->result_array();
				      	//echo "<pre>";print_r($user_list);die;
	      				
				      	$user_arr = array();
				      	foreach($user_list as $user){
				      		$tmp = array();
			      			$tmp['leaderboard_id'] = $leaderboard_id;
			      			$tmp['user_id'] = $user['user_id'];
			      			$tmp['total_value'] = $user['total_value'];
			      			$tmp['rank_value'] = $user['rank_value'];
			      			$tmp['custom_data'] = json_encode(explode(",",$user['team_ids']));
			      			$user_arr[$user['user_id']] = $tmp;
				      	}
			      	}

			      	if(!empty($user_arr)){
						$this->db = $this->db_user;

			      		$user_arr = array_values($user_arr);
			      		$this->replace_into_batch(LEADERBOARD_HISTORY, $user_arr);

						//reset primary key
						$this->set_auto_increment_key(LEADERBOARD_HISTORY,'history_id');
			      	}
				}
			}
		}
		return true;
	}

	/**
   	* Function used for update fantasy leaderboard status
   	* @param int $category_id
   	* @return boolean
   	*/
	public function update_fantasy_leaderboard_status($category_id)
	{
		if(!$category_id){
			return false;
		}
		$current_date = format_date();
		$end_date = date('Y-m-d H:i:s', strtotime($current_date. ' -4 hours'));
		$result = $this->db_user->select('L.leaderboard_id,LP.prize_id,LP.type,LP.reference_id,LP.allow_prize,LP.prize_detail,L.start_date,L.end_date', FALSE)
				->from(LEADERBOARD_PRIZE.' AS LP')
				->join(LEADERBOARD." AS L", "L.prize_id = LP.prize_id", "INNER")
				->where('LP.category_id',$category_id)
				->where('LP.status',1)
				->where('((LP.type != 4 AND LP.is_complete = 0) OR (LP.type = 4 AND LP.is_complete = 1))',NULL)
				->where('L.status',0)
		        ->where("L.end_date < ",$end_date)
				->order_by('L.end_date','DESC')
				->get()->result_array();
		//echo "<pre>";print_r($result);die;
		foreach($result as $row){
			$start_date = $row['start_date'];
			$end_date = $row['end_date'];
			//get match and collection
			$this->db_fantasy->select('COUNT(DISTINCT S.season_game_uid) as total,GROUP_CONCAT(DISTINCT (CASE WHEN S.status=2 AND CM.status=1 THEN CM.collection_master_id ELSE 0 END)) as completed', FALSE)
			        	->from(SEASON.' AS S')
				        ->join(COLLECTION_SEASON.' CS','CS.season_id = S.season_id','INNER')
				        ->join(COLLECTION_MASTER.' AS CM', 'CM.collection_master_id = CS.collection_master_id AND CM.league_id=S.league_id')
				        ->join(CONTEST.' AS C', 'C.collection_master_id = CM.collection_master_id AND C.entry_fee > 0 AND C.user_id = 0 AND C.is_reverse = 0')
				        ->where("CM.season_game_count","1")
				        ->where("S.season_scheduled_date >=",$start_date)
				        ->where("S.season_scheduled_date <=",$end_date);
	        if($row['type'] == $this->type_league && $row['reference_id'] > 0){
	        	$this->db_fantasy->where('S.league_id', $row['reference_id']);
	        }
	        $match_list = $this->db_fantasy->get()->row_array();
	      	if(!empty($match_list) && $match_list['total'] == count(array_diff(explode(",", $match_list['completed']),array("0")))){
	      		$update_arr = array('status' => 2);
				$row['prize_detail'] = json_decode($row['prize_detail'],TRUE);
				if($row['allow_prize'] == "0" || empty($row['prize_detail'])){
					$update_arr['status'] = 3;
				}
				$this->db_user->where('leaderboard_id',$row['leaderboard_id']);
				$this->db_user->where('prize_id',$row['prize_id']);
				$this->db_user->update(LEADERBOARD,$update_arr);
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
    	$result = $this->db_user->select('L.leaderboard_id,LP.prize_id,LP.allow_prize,LP.prize_detail', FALSE)
                    ->from(LEADERBOARD_PRIZE.' AS LP')
					->join(LEADERBOARD." AS L", "L.prize_id = LP.prize_id", "INNER")
					->where('L.status', 2)
			        ->where("L.end_date < ",$current_date)
                    ->where('LP.prize_detail != ', 'null' )
					->order_by('L.leaderboard_id','DESC')
					->get()
					->result_array();
      	//echo "<pre>";print_r($result);die;
      	if (!empty($result))
      	{
        	$this->load->helper('queue');
        	$server_name = get_server_host_name();
        	foreach ($result AS $key => $prize)
        	{
          		$content = array();
        		$content['url'] = $server_name."/cron/leaderboard/prize_distribution/".$prize['leaderboard_id'];
          		add_data_in_queue($content,'leaderboard');
        	}
      	}
      	return true;
  	}

  	/**
   	* Function used for leaderboard prize distribution
   	* @param int $leaderboard_id
   	* @return boolean
   	*/
	public function prize_distribution($leaderboard_id){
		if(!$leaderboard_id){
			return false;
		}
		$current_date = format_date();
		$prize = $this->db_user->select('L.leaderboard_id,L.name,L.prize_date,L.status,L.entity_no,LP.prize_id,LP.category_id,LP.type,LP.allow_prize,LP.prize_detail', FALSE)
                ->from(LEADERBOARD_PRIZE.' AS LP')
				->join(LEADERBOARD." AS L", "L.prize_id = LP.prize_id", "INNER")
				->where('L.status', 2)
				->where('LP.status', 1)
				->where('LP.allow_prize', 1)
				->where("JSON_EXTRACT(LP.prize_detail, '$[0]') is not null")
				->where('L.leaderboard_id',$leaderboard_id)
				->get()
				->row_array();
	    //echo "=====<pre>";print_r($prize);die;
		if(!empty($prize)){
			if (empty($prize['prize_detail']))
			{
				return true;
			}

			$txn_source = 264;
			$reference_id = $prize['leaderboard_id'];
			if($prize['category_id'] == $this->fantasy_category){
				$txn_source = 265;
			} else if($prize['category_id'] == $this->stock_category){
				$txn_source = 464;
			} else if($prize['category_id'] == $this->stock_equity_category){
				$txn_source = 465;
			}
			  else if($prize['category_id'] == $this->stock_predict_category){
				$txn_source = 466;
			}
			$wining_amount = (array) json_decode($prize['prize_detail'], TRUE);
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
			                if(isset($win_amt['amount'])){
								$mname = $win_amt['amount'];
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
	      	//echo "<pre>";print_r($winning_amount_arr);die;
		    //get winners
			$winners = $this->db_user->select('LH.*', FALSE)
							->from(LEADERBOARD_HISTORY.' AS LH')
							->where('LH.leaderboard_id', $prize['leaderboard_id'])
							->order_by("LH.rank_value","ASC")
							->limit($winner_places)
							->get()
							->result_array();
			//echo "<pre>";print_r($winners);die;
			$user_ldb_data = array();
	        $user_txn_data = array();
	        if(!empty($winners))
	        {
	          	foreach ($winners as $key => $value)
	          	{
		            $is_winner = 0;
		            $bonus_amount = $winning_amount = $points = 0;
		            $prize_obj = $winning_amount_arr[$value['rank_value']];
	            	if($prize_obj['prize_type'] == 0){
	            		$is_winner = 1;
						$bonus_amount = $prize_obj['amount'];
					}else if($prize_obj['prize_type'] == 1){
	            		$is_winner = 1;
						$winning_amount = $prize_obj['amount'];
					}else if($prize_obj['prize_type'] == 2){
	            		$is_winner = 1;
						$prize_obj['amount'] = ceil($prize_obj['amount']);
						$points = $prize_obj['amount'];
					}else if($prize_obj['prize_type'] == 3){
						$is_winner = 1;
					}
		            $ldb_data = array();
		            $ldb_data['leaderboard_id'] = $value['leaderboard_id'];
		            $ldb_data['user_id'] = $value['user_id'];
		            $ldb_data['is_winner'] = $is_winner;
		            $ldb_data['prize_data'] = json_encode($prize_obj);
		            $user_ldb_data[] = $ldb_data;
					
					$prize_type='';
					if($prize['type']==1){
						$prize_type = 'daily';
					}elseif($prize['type']==2){
						$prize_type = 'weekly';
					}elseif($prize['type']==3){
						$prize_type = 'monthly';
					}elseif($prize['type']==4){
						$prize_type = $prize['name'];
					}
		            $prize['entity_no'] = $prize['entity_no'].'th';
					$custom_data = array("prize"=>$prize_obj,"type"=>$prize['type'],"entity_no"=>$prize['entity_no'],"entity_name"=>$prize['name']);
		            //user txn data
		            $order_data = array();
		            $order_data["order_unique_id"] = $this->_generate_order_unique_key();
		            $order_data["user_id"]        = $value['user_id'];
		            $order_data["source"]         = $txn_source;
		            $order_data["source_id"]     = $value['history_id'];
		            $order_data["reference_id"]   = $reference_id;
		            $order_data["season_type"]    = 1;
		            $order_data["type"]           = 0;
		            $order_data["status"]         = 0;
		            $order_data["real_amount"]    = 0;
		            $order_data["bonus_amount"]   = $bonus_amount;
		            $order_data["winning_amount"] = $winning_amount;
		            $order_data["points"] = $points;
		            $order_data["custom_data"] = json_encode($custom_data);
		            $order_data["plateform"]      = PLATEFORM_FANTASY;
		            $order_data["date_added"]     = format_date();
		            $order_data["modified_date"]  = format_date();
		            $user_txn_data[] = $order_data;
	          	}
	        }
	        if(!empty($user_ldb_data)){
	          	try
	          	{
	            	//Start Transaction
		            $this->db = $this->db_user;
		            $this->db->trans_strict(TRUE);
		            $this->db->trans_start();
		            $user_ldb_data = array_chunk($user_ldb_data, 999);
		            foreach($user_ldb_data as $lbd_data){
		              	$this->replace_into_batch(LEADERBOARD_HISTORY, $lbd_data);
		            }

		            $user_txn_arr = array_chunk($user_txn_data, 999);
	              	foreach($user_txn_arr as $txn_data){
		                $this->insert_ignore_into_batch(ORDER, $txn_data);

						if($order_data["points"] > 0) {
							$this->load->helper('queue_helper');
							$coin_data = array(
								'oprator' => 'add', 
								'user_id' => $value['user_id'], 
								'total_coins' => $order_data["points"], 
								'bonus_date' => format_date("today", "Y-m-d")
							);
							add_data_in_queue($coin_data, 'user_coins');    
						}
		            }

		            $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = ".$txn_source." AND type=0 AND status=0 AND reference_id='".$reference_id."' GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
		                SET U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
		                WHERE O.source = ".$txn_source." AND O.type=0 AND O.status=0 AND O.reference_id='".$reference_id."' ";
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
		              	// leaderboard table update status
		                $this->db->where('leaderboard_id', $leaderboard_id);
		                $this->db->update(LEADERBOARD, array('status' => '3','prize_detail' => $prize['prize_detail']));
		              	
		              	if(CACHE_ENABLE) {
		                  	//$this->load->model('Nodb_model');
		                  	//$this->Nodb_model->flush_cache_data();
							$user_balance_cache_key = 'user_balance_';
							$this->delete_wildcard_cache_data($user_balance_cache_key);
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

	/**
   	* Function used for sent prize notification
   	* @param void
   	* @return boolean
   	*/
  	public function prize_notification()
  	{
		$current_date = format_date();
    	$result = $this->db_user->select('L.leaderboard_id,L.name,L.entity_no,L.prize_detail,L.start_date,L.end_date,LP.prize_id,LP.category_id,LP.type', FALSE)
                    ->from(LEADERBOARD_PRIZE.' AS LP')
					->join(LEADERBOARD." AS L", "L.prize_id = LP.prize_id", "INNER")
					->where('L.status', 3)
					->where('L.is_win_notify', 0)
					->order_by('L.leaderboard_id','DESC')
					->get()
					->result_array();
      	//echo "<pre>";print_r($result);die;
      	if (!empty($result))
      	{
        	foreach ($result AS $key => $prize)
        	{
  				$is_success = 0;
    			$txn_source = 264;
    			$subject = "Referral Leaderboard Winnings";
				if($prize['category_id'] == $this->fantasy_category){
					$txn_source = 265;
					$subject = "Fantasy Points Leaderboard Winnings";
				} else if($prize['category_id'] == $this->stock_category){
					$txn_source = 569;
					$subject = "Stock Points Leaderboard Winnings";
				} else if($prize['category_id'] == $this->stock_equity_category){
					$txn_source = 570;
					$subject = "Stock Equity Points Leaderboard Winnings";
				}
				  else if($prize['category_id'] == $this->stock_predict_category){
					$txn_source = 571;
					$subject = "Stock Predict Points Leaderboard Winnings";
				}
          		//get winners
				$winners = $this->db_user->select('LH.*,U.user_name,U.email', FALSE)
								->from(LEADERBOARD_HISTORY.' AS LH')
								->join(USER." U", "U.user_id = LH.user_id", "INNER")
								->where('LH.leaderboard_id', $prize['leaderboard_id'])
								->where('LH.is_winner', '1')
								->order_by("LH.rank_value","ASC")
								->get()
								->result_array();
				//echo "<pre>";print_r($winners);die;
				foreach($winners as $row){
					$amount = 0;
					$prize_type = 1;
					$prize_data = json_decode($row['prize_data'],TRUE);
					if(!empty($prize_data)){
						if($prize_data['prize_type']==3)
						{
							$amount = $prize_data['name'];
						}else{
							$amount = $prize_data['amount'];
						}						
						$prize_type = $prize_data['prize_type'];
					}
					$notify_data = array();
	              	$notify_data['notification_type'] = $txn_source;//leaderboard winning
		            $notify_data['notification_destination'] = 1; //web
		            $notify_data["source_id"] = $row['history_id'];
		            $notify_data["user_id"] = $row['user_id'];
		            $notify_data["to"] = $row['email'];
		            $notify_data["user_name"] = !empty($row['user_name']) ? $row['user_name'] : $row['email'];
		            $notify_data["added_date"] = date("Y-m-d H:i:s");
		            $notify_data["modified_date"] = date("Y-m-d H:i:s");
		            $notify_data["subject"] = $subject;
		            $content = array(
					              	'leaderboard_id' => $prize['leaderboard_id'],
					              	'type' => $prize['type'],
					              	'entity_name' => $prize['name'],
					              	'rank_value' => $row['rank_value'],
					              	'entity_no' => $prize['entity_no'],
					              	'start_date' => $prize['start_date'],
					              	'end_date' => $prize['end_date'],
					              	'amount' => $amount,
					              	'prize_type' => $prize_type
				              	);
	              	$notify_data["content"] = json_encode($content);
	              	//echo "<pre>";print_r($notify_data);die;
		            $this->load->model('notification/Notify_nosql_model');
		            $this->Notify_nosql_model->send_notification($notify_data);

		            $is_success = 1;
				}

				if($is_success == 1){
				  	$this->db_user->where('leaderboard_id', $prize['leaderboard_id']);
				  	$this->db_user->update(LEADERBOARD, array('is_win_notify' => '1'));
			  	}
        	}
      	}
      	return true;
  	}
}
?>