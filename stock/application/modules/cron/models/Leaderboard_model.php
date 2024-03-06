<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Leaderboard_model extends MY_Model{

	public $db_user;
	public $type_daily = 1;
 	public $type_weekly = 2;
 	public $type_month = 3;
	public function __construct(){
		parent::__construct();
		$this->db_user = $this->load->database('user_db',TRUE);
		$this->db_stock	= $this->load->database('stock_db',TRUE);
	}
    
	/**
   	* Function used for update stock points and calculate rank
   	* @return boolean
   	*/
	public function save_stock_leaderboard() {
		$current_date = format_date();
		$prize_list = $this->db_user->select('*')
							->from(LEADERBOARD_PRIZE)
							->where_in('category_id',array(STOCK_LEADERBOARD_ID, STOCK_EQUITY_LEADERBOARD_ID,STOCK_PREDICT_LEADERBOARD_ID,LIVE_STOCK_FANTASY_LEADERBOARD_ID))
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
				}

				$stock_type = 1;
				if($prize['category_id'] == STOCK_EQUITY_LEADERBOARD_ID) {
					$stock_type = 2;
				}
				if($prize['category_id'] == STOCK_PREDICT_LEADERBOARD_ID) {
					$stock_type = 3;
				}
				if($prize['category_id'] == LIVE_STOCK_FANTASY_LEADERBOARD_ID) {
					$stock_type = 4;
				}
				//echo "<br/>".$leaderboard_id."===".$status;die;
				//save record in history table
				if($leaderboard_id && $status == 0){
					//get match and collection
					$this->db->select('CM.collection_id', FALSE)
					        	->from(COLLECTION.' AS CM')
						        ->join(CONTEST.' AS C', 'C.collection_id = CM.collection_id AND C.entry_fee > 0 AND C.user_id = 0 AND C.status = 3')						      
						        ->where("CM.stock_type",$stock_type)
								->where("CM.is_lineup_processed > ",0)
						        ->where("CM.status",1)
						        ->where("CM.scheduled_date >=",$start_date)
						        ->where("CM.scheduled_date <=",$end_date);			        
			        $this->db->group_by("CM.collection_id");

			      	$match_list = $this->db->get()->result_array();
			      	//echo "<pre>";print_r($match_list);die;
			      	if(!empty($match_list)){
				      	$collection_ids = array_column($match_list,'collection_id');
				      	//echo "<pre>";print_r($collection_ids);

				      	//collection users score
				      	$pre_sql = "SELECT LM.user_id,LM.collection_id,MAX(LMC.total_score) as total_score 
				      				FROM ".$this->db->dbprefix(CONTEST)." AS C 
				      				INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC ON LMC.contest_id = C.contest_id 
				      				INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER)." AS LM ON LM.lineup_master_id = LMC.lineup_master_id 
				      				WHERE C.collection_id IN(".implode(',', $collection_ids).") AND C.status = 3 AND C.user_id = 0 AND C.entry_fee > 0 
				      				GROUP BY C.collection_id,LM.user_id";

	      				$sql = "SELECT LM.user_id,LM.lineup_master_id,MAX(LMC.total_score) as total_score,LM.added_date  
				      				FROM ".$this->db->dbprefix(CONTEST)." AS C 
				      				INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC ON LMC.contest_id = C.contest_id 
				      				INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER)." AS LM ON LM.lineup_master_id = LMC.lineup_master_id 
				      				INNER JOIN (".$pre_sql.") AS TMP ON TMP.user_id = LM.user_id AND TMP.collection_id = LM.collection_id AND TMP.total_score = LMC.total_score 
				      				WHERE C.collection_id IN(".implode(',', $collection_ids).") AND C.status = 3 AND C.user_id = 0 AND C.entry_fee > 0  
				      				GROUP BY C.collection_id,LM.user_id";

				      	if($stock_type ==3){ /*For Stock Predict Calculate percent_change as total_score in leadeboard*/
				      		$pre_sql = "SELECT LM.user_id,LM.collection_id,MAX(LMC.percent_change) as total_score 
				      				FROM ".$this->db->dbprefix(CONTEST)." AS C 
				      				INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC ON LMC.contest_id = C.contest_id 
				      				INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER)." AS LM ON LM.lineup_master_id = LMC.lineup_master_id 
				      				WHERE C.collection_id IN(".implode(',', $collection_ids).") AND C.status = 3 AND C.user_id = 0 AND C.entry_fee > 0 
				      				GROUP BY C.collection_id,LM.user_id";

	      				    $sql = "SELECT LM.user_id,LM.lineup_master_id,MAX(LMC.percent_change) as total_score,LM.added_date  
				      				FROM ".$this->db->dbprefix(CONTEST)." AS C 
				      				INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC ON LMC.contest_id = C.contest_id 
				      				INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER)." AS LM ON LM.lineup_master_id = LMC.lineup_master_id 
				      				INNER JOIN (".$pre_sql.") AS TMP ON TMP.user_id = LM.user_id AND TMP.collection_id = LM.collection_id AND TMP.total_score = LMC.percent_change 
				      				WHERE C.collection_id IN(".implode(',', $collection_ids).") AND C.status = 3 AND C.user_id = 0 AND C.entry_fee > 0  
				      				GROUP BY C.collection_id,LM.user_id";
				      				
				      	}			

	      				$final_sql = "SELECT user_id, GROUP_CONCAT(lineup_master_id) as team_ids, SUM(total_score) as total_value, RANK() OVER (ORDER BY SUM(total_score) DESC, MIN(added_date) ASC) rank_value FROM (".$sql.") as q GROUP BY user_id";

	      				if($stock_type ==3)
	      				{
	      					$predict_pre_sql = "SELECT count(user_id) as usercount,user_id, added_date,GROUP_CONCAT(lineup_master_id) as team_ids, SUM(total_score) as total_value from (".$sql.") as q GROUP BY user_id";

	      					$final_sql = "SELECT user_id, team_ids,(total_value / usercount) as total_value , RANK() OVER (ORDER BY (SUM(total_value)/ usercount ) DESC, MIN(added_date) ASC) rank_value FROM (".$predict_pre_sql.")as q GROUP BY user_id ";
	      				}

	      				$query = $this->db->query($final_sql);
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
						$this->db = $this->db_stock;
			      	}
				}
			}
		}
		return true;
	}

	/**
   	* Function used for update fantasy leaderboard status
   	* @return boolean
   	*/
	public function update_stock_leaderboard_status()
	{
		$current_date = format_date();
		$end_date = date('Y-m-d H:i:s', strtotime($current_date. ' -4 hours'));
		$result = $this->db_user->select('L.leaderboard_id, LP.category_id, LP.prize_id, LP.type, LP.reference_id, LP.allow_prize, LP.prize_detail, L.start_date, L.end_date', FALSE)
				->from(LEADERBOARD_PRIZE.' AS LP')
				->join(LEADERBOARD." AS L", "L.prize_id = LP.prize_id", "INNER")
				->where_in('LP.category_id',array(STOCK_LEADERBOARD_ID, STOCK_EQUITY_LEADERBOARD_ID,STOCK_PREDICT_LEADERBOARD_ID,LIVE_STOCK_FANTASY_LEADERBOARD_ID))
				->where('LP.status',1)
				->where('((LP.type != 4 AND LP.is_complete = 0) OR (LP.type = 4 AND LP.is_complete = 1))',NULL)
				->where('L.status < ', 2)
		        ->where("L.end_date < ",$end_date)
				->order_by('L.end_date','DESC')
				->get()->result_array();
		//echo "<pre>";print_r($result);die;
		foreach($result as $row){
			$start_date = $row['start_date'];
			$end_date = $row['end_date'];
			$stock_type = 1;
			if($row['category_id'] == STOCK_EQUITY_LEADERBOARD_ID) {
				$stock_type = 2;
			}
			if($row['category_id'] == STOCK_PREDICT_LEADERBOARD_ID) {
				$stock_type = 3;
			}
			if($row['category_id'] == LIVE_STOCK_FANTASY_LEADERBOARD_ID) {
				$stock_type = 4;
			}
			//get match and collection
			$this->db->select('COUNT(DISTINCT CM.collection_id) as total,GROUP_CONCAT(DISTINCT (CASE WHEN CM.status=1 THEN CM.collection_id ELSE 0 END)) as completed', FALSE)
			        	->from(COLLECTION.' AS CM')
				        ->join(CONTEST.' AS C', 'C.collection_id = CM.collection_id AND C.entry_fee > 0 AND C.user_id = 0')
				        ->where("CM.stock_type",$stock_type)
						->where("CM.scheduled_date >=",$start_date)
				        ->where("CM.end_date <=",$end_date);
	        
	        $match_list = $this->db->get()->row_array();
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
}
?>