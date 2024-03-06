<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
     * Used for get completed match list
     * @param void
     * @return array
     */
	public function get_completed_matches($post_data) {
        $this->db_fantasy = $this->load->database('db_fantasy', TRUE);
        $end_date = format_date();
        $start_date = date("Y-m-d H:i:s", strtotime($end_date." -20 hours"));
        if(isset($post_data['start_date']) && $post_data['start_date'] != ""){
        	$start_date = date("Y-m-d H:i:s",strtotime($post_data['start_date']));
        }
        if(isset($post_data['end_date']) && $post_data['end_date'] != ""){
        	$end_date = date("Y-m-d H:i:s",strtotime($post_data['end_date']));
        }

		$this->db_fantasy->select("C.collection_master_id", false)
			->from(CONTEST.' C')
			->where("C.status",3)
			->where("C.completed_date >= ", $start_date)
			->where("C.completed_date <= ", $end_date)
			->group_by("C.collection_master_id");
		$match_list = $this->db_fantasy->get()->result_array();
		$result = array();
		if(!empty($match_list)){
			$collection_ids = array_column($match_list,'collection_master_id');
			$this->db_fantasy->select("C.sports_id,CM.league_id,CM.collection_master_id,CM.collection_name as match_name,CM.season_scheduled_date as schedule_date,SUM(C.total_user_joined) AS total_user_joined,SUM(C.total_system_user) AS total_system_user,round(sum(C.prize_pool * C.site_rake / 100),2) AS total_rake, round(sum(C.prize_pool),2) as total_pool,round(sum(CASE WHEN C.contest_access_type=1 THEN (C.prize_pool * C.site_rake / 100) ELSE 0 END),2) as private_site_rake,GROUP_CONCAT(C.contest_unique_id) AS contest_unique_ids,GROUP_CONCAT(C.contest_id) AS contest_ids", FALSE)
				->from(COLLECTION_MASTER.' AS CM')
				->join(CONTEST.' AS C', 'C.collection_master_id = CM.collection_master_id', 'INNER')
				->where('CM.season_game_count',1)
				->where("C.status",3)
				->where_in("C.collection_master_id", $collection_ids)
				->group_by("C.collection_master_id");
			$result = $this->db_fantasy->get()->result_array();
		}
        return $result;
	}

	public function get_match_entry_fee_details($post_data){
		$result = $this->db->select("SUM(IF(O.source=1,(O.real_amount+O.cb_amount),0)) as entry_real,SUM(IF(O.source=1,O.bonus_amount,0)) as entry_bonus,SUM(IF(O.source=1,O.points,0)) as entry_coins,ROUND(SUM(IF(O.source=3,O.winning_amount,0)),2) as real_win,SUM(IF(O.source=3,O.bonus_amount,0)) as bonus_win,SUM(IF(O.source=3,O.points,0)) as coins_win,U.is_systemuser")
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
			$promocode = $this->db->select("sum(amount_received) as promo_discount")
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



	public function get_match_entry_fees_details($post_data){
		$result = $this->db->select("SUM(IF(O.source=1,(O.real_amount+O.cb_amount),0)) as entry_real,SUM(IF(O.source=1,O.bonus_amount,0)) as entry_bonus,SUM(IF(O.source=1,O.points,0)) as entry_coins,ROUND(SUM(IF(O.source=3,O.winning_amount,0)),2) as real_win,SUM(IF(O.source=3,O.bonus_amount,0)) as bonus_win,SUM(IF(O.source=3,O.points,0)) as coins_win,U.is_systemuser")
					->from(ORDER.' AS O')
					->join(USER.' AS U','O.user_id=U.user_id','left')
					->where_in('reference_id',explode(',',$post_data['contest_ids']))
					// ->where("O.status",1)
					->where_in("O.source",array(1))
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
			$promocode = $this->db->select("sum(amount_received) as promo_discount")
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
	 * [contest_list description]
	 * @MethodName contest_list
	 * @Summary This function used for get all contest List
	 * @return     [array]
	 */
	public function get_completed_contest_report($from_date,$to_date,$collection_master_id)
	{ 
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
        $end_date = format_date();
        $start_date = date("Y-m-d H:i:s", strtotime($end_date." -20 hours"));
		// print_r($post_data);die;
        if(isset($from_date) && $from_date != ""){
        	$start_date = date("Y-m-d H:i:s",strtotime($from_date));
        }
        if(isset($to_date) && $to_date != ""){
        	$end_date = date("Y-m-d H:i:s",strtotime($to_date));
        }		
		//SUM(IF(LM.is_systemuser=1,1,0)) as system_teams,SUM(IF(LM.is_systemuser=0,1, 0)) as real_teams,(SUM(IF(LM.is_systemuser=1,1,0))*G.entry_fee) as botuser_total_real_entry_fee need to add ,(G.total_user_joined - G.total_system_user) AS real_teams,G.total_system_user AS system_teams
		$classic = $this->lang->line('classic');
		$reverse = $this->lang->line('reverse');
		$second_inning = $this->lang->line('2nd_inning');
		$this->db_fantasy->select("CM.collection_master_id,CM.league_id,G.sports_id,G.group_id, G.group_id,CM.collection_name,SUM(G.total_system_user) AS total_system_user,G.contest_id,G.contest_unique_id, G.contest_name, G.entry_fee, G.prize_pool,G.site_rake, G.total_user_joined, G.size,G.minimum_size, 
		G.guaranteed_prize,G.season_scheduled_date,SUM(IF(LM.is_systemuser=1,1,0)) as system_teams,SUM(IF(LM.is_systemuser=0,1, 0)) as real_teams,G.currency_type,G.max_bonus_allowed,G.entry_fee*G.total_user_joined as total_entry_fee,G.entry_fee*G.total_system_user AS bot_entry_fee,
		 (CASE WHEN G.is_2nd_inning=1 THEN '{$second_inning}'
		 WHEN G.is_reverse =1 THEN '{$reverse}'
		 WHEN G.is_reverse=0 AND G.is_2nd_inning =0 THEN '{$classic}' 		

		END) AS feature_type,MG.group_name,round(sum(G.prize_pool),2) as total_pool,round(sum(CASE WHEN G.contest_access_type=1 THEN (G.prize_pool * G.site_rake / 100) ELSE 0 END),2) as private_site_rake,round(sum(G.prize_pool * G.site_rake / 100),2) AS total_rake
		,round(sum(G.prize_pool),2) as total_pool,GROUP_CONCAT(G.contest_unique_id) AS contest_unique_ids,GROUP_CONCAT(G.contest_id) AS contest_ids,G.is_reverse,G.is_2nd_inning",false)
		->from(CONTEST." AS G")
		->join(MASTER_GROUP." AS MG","MG.group_id = G.group_id","INNER")
		->join(COLLECTION_MASTER." AS CM","CM.collection_master_id = G.collection_master_id","LEFT")
		->join(LINEUP_MASTER_CONTEST." AS LMC", 'LMC.contest_id = G.contest_id','LEFT')
		->join(LINEUP_MASTER." AS LM", 'LM.lineup_master_id = LMC.lineup_master_id','LEFT')
		->where('G.status','3')
		// ->where('LM.is_systemuser','1')
		->where('G.report_generated','0');
		if(isset($collection_master_id) && $collection_master_id !='')
		{
			$this->db_fantasy->where('G.collection_master_id', $collection_master_id);
		}
		$this->db_fantasy->where("G.completed_date >= ", $start_date)
		->where("G.completed_date <= ", $end_date);
		$this->db_fantasy->group_by('G.contest_unique_id');
		$result = $this->db_fantasy->get()->result_array();
		// print_r($result);die;
		return $result;
	}


	/**
   * Function used for process match tds
   * @param int $collection_master_id
   * @return boolean
   */
	public function user_match_report($collection_master_id)
	{
		if (!$collection_master_id) {
			return false;
		}
		$current_date = format_date();
		$this->db_fantasy->select("C.contest_id,C.collection_master_id,C.season_scheduled_date,C.site_rake", FALSE)
			->from(CONTEST.' AS C')
			->where('C.status',3)
			->where('C.collection_master_id', $collection_master_id)
			->where('C.season_scheduled_date < ', $current_date);
		$result = $this->db_fantasy->get()->result_array();
		// echo $this->db_fantasy->last_query();die;
		//echo "<pre>";print_r($result);die;
		if(!empty($result)){
			$module_type = 1;
			$season_scheduled_date = $result['0']['season_scheduled_date'];
			$contest_ids = array_unique(array_column($result,"contest_id"));
			$c_ids_chunks = array_chunk($contest_ids,999);
			$this->db_fantasy->select("LM.user_id,count(LMC.lineup_master_contest_id) as match_played,sum(LMC.is_winner) as match_won,(count(LMC.lineup_master_contest_id) - sum(LMC.is_winner)) as match_lost,0 as total_entry_fee,0 as total_bonus_used,0 as coin_entry,0 as total_win_amt,0 as coin_winning,0 as bonus_win,0 as revenue,LM.collection_master_id as entity_id,'".$season_scheduled_date."' as schedule_date,'".$current_date."' as created_date,1 as module_type", FALSE)
			->from(LINEUP_MASTER_CONTEST.' AS LMC')
			->join(LINEUP_MASTER." LM", "LMC.lineup_master_id= LM.lineup_master_id", "INNER");
			foreach($c_ids_chunks as $key => $vchunk_arr) 
			{
				if($key == 0)
				{
					$this->db_fantasy->where_in('LMC.contest_id',$vchunk_arr);
				}
				else
				{
					$this->db_fantasy->or_where_in('LMC.contest_id',$vchunk_arr);
				}  
			}
			$this->db_fantasy->group_by("LM.user_id");
			$users_games = $this->db_fantasy->get()->result_array();
			$users_games = array_column($users_games,NULL,"user_id");
			// echo "<pre>";print_r($users_games);die;

			//get users txn paid datarevenue
			$this->db_user = $this->load->database('db_user', TRUE);
			$this->db_user->select("O.user_id,O.reference_id,
					(SUM(IF(source = 1, O.real_amount + O.winning_amount, 0)) - SUM(IF(source = 2, O.real_amount + O.winning_amount, 0))) AS total_entry_fee,
					(SUM(IF(source = 1, O.bonus_amount, 0)) - SUM(IF(source = 2, O.bonus_amount, 0))) AS total_bonus_used,
					(SUM(IF(source = 1, O.points, 0)) - SUM(IF(source = 2, O.points, 0))) AS coin_entry,
					IFNULL(SUM(CASE WHEN O.source = 3 THEN O.winning_amount ELSE 0 END), 0) AS total_win_amt,
					IFNULL(SUM(CASE WHEN O.source = 3 THEN O.points ELSE 0 END), 0) AS coin_winning,
					IFNULL(SUM(CASE WHEN O.source = 3 THEN O.bonus_amount ELSE 0 END), 0) AS bonus_win",FALSE);
			$this->db_user->from(ORDER." AS O");
			$this->db_user->where("status", 1);
			foreach($c_ids_chunks as $key => $vchunk_arr) 
			{
				if($key == 0)
				{
					$this->db_user->where_in("O.reference_id", $vchunk_arr);
				}
				else
				{
					$this->db_user->or_where_in("O.reference_id", $vchunk_arr);
				}  
			}
			$this->db_user->where_in("O.source", [1,2,3]);
			$this->db_user->group_by("O.user_id");
			$this->db_user->group_by("O.reference_id");
			$sql = $this->db_user->get();
			$user_list = $sql->result_array();

			$contest_rake = array_column($result,"site_rake","contest_id");
			if(!empty($user_list)){
				foreach($user_list as $user){
					$user_id = $user['user_id'];
					$total_entry_fee = $user['total_entry_fee'];
					$site_rake = isset($contest_rake[$user['reference_id']]) ? $contest_rake[$user['reference_id']] : 0;
					$revenue = number_format((($total_entry_fee * $site_rake) / 100),2,'.','');

					$users_games[$user_id]['module_type'] = $module_type;
					$users_games[$user_id]['entity_id'] = $collection_master_id;
					$users_games[$user_id]['schedule_date'] = $season_scheduled_date;
					$users_games[$user_id]['created_date'] = $current_date;
					$users_games[$user_id]['revenue'] = $users_games[$user_id]['revenue'] + $revenue;
					$users_games[$user_id]['total_entry_fee'] = $users_games[$user_id]['total_entry_fee'] + $total_entry_fee;
					$users_games[$user_id]['total_bonus_used'] = $users_games[$user_id]['total_bonus_used'] + $user['total_bonus_used'];
					$users_games[$user_id]['coin_entry'] = $users_games[$user_id]['coin_entry'] + $user['coin_entry'];
					$users_games[$user_id]['total_win_amt'] = $users_games[$user_id]['total_win_amt'] + $user['total_win_amt'];
					$users_games[$user_id]['coin_winning'] = $users_games[$user_id]['coin_winning'] + $user['coin_winning'];
					$users_games[$user_id]['bonus_win'] = $users_games[$user_id]['bonus_win'] + $user['bonus_win'];
				}
			}
			
			//echo "<pre>";print_r($users_games);die;
			if(!empty($users_games)) {
				try {
					$this->db = $this->db_user;
					//Start Transaction
					$this->db->trans_strict(TRUE);
					$this->db->trans_start();

					$update_data_chunks = array_chunk($users_games, 999);
					// print_r($update_data_chunks);die;
					foreach ($update_data_chunks as $update_key => $update_chunk) {
						$this->insert_ignore_into_batch(USER_MATCH_REPORT, $update_chunk);
					}
					//Trasaction End
					$this->db->trans_complete();
					if ($this->db->trans_status() === FALSE) {
						$this->db->trans_rollback();
					} else {
						$this->db->trans_commit();
					}
				} catch (Exception $e) {
					$this->db->trans_rollback();
				}
			}
		}
		return true;
	}


	 public function push_match_report()
      {
               $current_date = format_date();
              $this->db_fantasy->select("C.collection_master_id", FALSE)
                      ->from(CONTEST.' AS C')
                       ->where('C.status',3)
                       ->where('C.season_scheduled_date < ', $current_date)
                       ->group_by("C.collection_master_id");
               $result = $this->db_fantasy->get()->result_array();
               $this->load->helper('queue_helper');
        $server_name = get_server_host_name();
               foreach($result as $row){
            $content = array();
            $content['url'] = $server_name."/adminapi/report/cron/user_match_report/".$row['collection_master_id'];
            add_data_in_queue($content,'cron');
        }
       }


}