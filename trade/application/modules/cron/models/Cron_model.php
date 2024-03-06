<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Cron_model extends MY_Model {
    public function __construct() 
    {
       	parent::__construct();
    }

    function __destruct() {
        $this->db->close();
    }

    /**
     * Function used for auto publish fixture with default templates
     * @param int $sports_id
     * @return string
     */
    public function auto_publish_fixture($sports_id){
		if(isset($this->app_config['opinion_trade']) && $this->app_config['opinion_trade']['key_value'] == "0") {
            return true; 
        }
		$op_custom_data = $this->app_config['opinion_trade']['custom_data'];
        $currency_type = isset($op_custom_data['currency'])?$op_custom_data['currency']:'realcash';
		$currency = 1;
		if($currency_type == 'realcash'){
			$currency = 1;
		}elseif($currency_type == 'coins'){
			$currency = 2;
		}


    	$current_date = format_date();
    	$future_date = date("Y-m-d H:i:s", strtotime($current_date . " +3 days"));
 		$this->db->select("S.season_id,S.scheduled_date,S.is_published,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.display_abbr,T2.team_abbr) as away,IFNULL(T1.display_name,T1.team_name) as home_name,IFNULL(T2.display_name,T2.team_name) as away_name,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,IFNULL(L.display_name,L.league_name) as league_name,S.status",FALSE)
			->from(SEASON." AS S")
			->join(LEAGUE." L", "L.league_id = S.league_id", "INNER")
			->join(TEAM." T1", "T1.team_id = S.home_id AND T1.sports_id=L.sports_id", "INNER")
			->join(TEAM." T2", "T2.team_id = S.away_id AND T2.sports_id=L.sports_id", "INNER")
            ->where("L.sports_id",$sports_id)
			->where("L.auto_published","1")
			->where("S.is_published","0")
			->where("S.scheduled_date > ",$current_date)
            ->where("S.scheduled_date < ",$future_date)
            ->order_by("S.scheduled_date","ASC")
			->group_by("S.season_id")
			->limit("10");
		$match_list = $this->db->get()->result_array();
		//echo "<pre>";print_r($match_list);die;
		if(!empty($match_list)){
			$template_list = $this->get_all_table_data("template_id,name",MASTER_TEMPLATE,array("sports_id"=>$sports_id,'status'=>1),array("template_id"=>"ASC"));
			$question_arr = array();
			foreach($match_list as $row){
				$season_id = $row['season_id'];
				$home_name = $row['home_name'];
				$away_name = $row['away_name'];
				$match_name = $home_name." vs ".$away_name;
				$scheduled_date = $row['scheduled_date'];
				foreach($template_list as $template){
					$question = str_replace('{home_name}',$home_name,$template['name']);
					$question = str_replace('{away_name}',$away_name,$question);
					$question = str_replace('{match_name}',$match_name,$question);
					$tmp_arr = array();
					$tmp_arr['season_id'] = $season_id;
					$tmp_arr['template_id'] = $template['template_id'];
					$tmp_arr['question'] = $question;
					$tmp_arr['scheduled_date'] = $scheduled_date;
					$tmp_arr['option1'] = "Yes";
					$tmp_arr['option2'] = "No";
					$tmp_arr['option1_val'] = "5.0";
					$tmp_arr['option2_val'] = "5.0";
					$tmp_arr['currency_type'] = $currency;
					$tmp_arr['added_date'] = $current_date;
					$tmp_arr['modified_date'] = $current_date;
					$question_arr[] = $tmp_arr;
				}
			}
			//echo "<pre>";print_r($question_arr);die;
			if(!empty($question_arr)){
				try{
					//Start Transaction
					$this->db->trans_strict(TRUE);
					$this->db->trans_start();
					
					//save question
					$this->db->insert_batch(SEASON_QUESTION, $question_arr);

					//Update Fixture publish status
					$season_ids = array_unique(array_column($question_arr,"season_id"));
	              	$this->db->where_in('season_id', $season_ids);
	              	$this->db->where('is_published',"0");
	              	$this->db->update(SEASON, array("is_published"=>1));

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

    /**
     * Function used for push question for cancellation
     * @param void
     * @return string
     */
    public function game_cancellation() {
		
    	set_time_limit(0);
		$current_date = format_date();
		
		$this->db->select("SQ.question_id,SQ.scheduled_date",FALSE)
			->from(USER_TEAM." AS UT")
			->join(SEASON_QUESTION." SQ", "SQ.question_id = UT.question_id", "INNER")
            ->where("SQ.scheduled_date <= ",$current_date)
            ->where("UT.matchup_id","0")
            ->where("UT.status","0")
            ->group_by("UT.question_id")
            ->order_by("UT.question_id","ASC");
		$record_list = $this->db->get()->result_array();
		//echo "<pre>";print_r($record_list);die;
		if(!empty($record_list)){
			$this->load->helper('queue');
      		$server_name = get_server_host_name();
      		foreach($record_list as $row){
				$question_id = $row['question_id'];
				$content = array();
				$content['url'] = $server_name."/trade/cron/cancel_question/".$question_id;
				add_data_in_queue($content,'cron');
	      	}
		}else{
			
			$this->db->select("SQ.question_id",FALSE)
			->from(SEASON_QUESTION." AS SQ")
			->join(USER_TEAM." UT", "UT.question_id = SQ.question_id", "LEFT")			
			->where("SQ.status","0")
			->where("SQ.scheduled_date <= ",$current_date)
			->where("UT.question_id",NULL);
			$questions = $this->db->get()->result_array();
		
			if(!empty($questions)){

				$this->load->helper('queue');
      		    $server_name = get_server_host_name();
				foreach($questions as $quest){				
					$content['url'] = $server_name."/trade/cron/cancel_question/".$quest['question_id'];
					add_data_in_queue($content,'cron');
				}
			}
		}

	}

    /**
     * Function used for cancel question and refund entry
     * @param int $question_id
     * @param int $type
     * @return string
     */
    public function cancel_question($question_id,$type=0) {
		
    	if(!$question_id){
    		return false;
    	}
		
		$current_date = format_date();
 		$this->db->select("GROUP_CONCAT(DISTINCT UT.user_team_id) as user_team_ids,UT.user_id,UT.answer,SUM(UT.entry_real) as total_entry,SUM(UT.entry_winning) as total_winning,SUM(UT.entry_coins) as total_coins,SUM(UT.entry_bonus) as total_bonus,UT.matchup_id,SQ.question_id,SQ.currency_type,SQ.season_id",FALSE)
			->from(USER_TEAM." AS UT")
			->join(SEASON_QUESTION." SQ", "SQ.question_id = UT.question_id", "INNER")            
            ->where("UT.question_id",$question_id)
            ->where("UT.status","0")
			->group_by("UT.user_id")
            ->order_by("UT.question_id","ASC");
        if($type == 0){
			$this->db->where("SQ.scheduled_date <= ",$current_date);
        	$this->db->where("UT.matchup_id","0");
        }
		$team_list = $this->db->get()->result_array();
		//echo "<pre>";print_r($team_list);die;
		if(!empty($team_list)){
			
			$user_ids_arr = array();
			$user_txn_data = array();
			$t_user_team_ids = array();
			foreach($team_list as $value){
				$user_ids[] = $value['user_id'];

				$user_team_ids = explode(',',$value['user_team_ids']);
				$t_user_team_ids = array_merge($t_user_team_ids,$user_team_ids);
				$quantity = count($user_team_ids);

				if(!empty($user_team_ids)){
					
					$real_amount = $bonus_amount = $winning_amount = $points = 0;
				
					if($value['currency_type'] == 1){
						$real_amount = $value['total_entry'];
						$winning_amount = $value['total_winning'];
					}elseif($value['currency_type'] == 2){
						$points = $value['total_coins'];
					}elseif($value['currency_type'] == 0){
						$bonus_amount = $value['total_bonus'];
					}

					$match_data = $this->get_fixture_details($value['season_id']);
					$match_name = isset($match_data)?$match_data['home'].' vs '.$match_data['away']:'';
					
					//user txn data
					$order_data = array();
					$order_data["order_unique_id"] = $this->_generate_order_key();
					$order_data["user_id"]        = $value['user_id'];
					$order_data["source"]         = 543;
					$order_data["source_id"]      = 0;
					$order_data["reference_id"]   = $value['question_id'];
					$order_data["season_type"]    = 1;
					$order_data["type"]           = 0;
					$order_data["status"]         = 0;
					$order_data["real_amount"]    = $real_amount;
					$order_data["bonus_amount"]   = $bonus_amount;
					$order_data["winning_amount"] = $winning_amount;
					$order_data["points"] 		  = $points;
					$order_data["custom_data"]    = json_encode(array('quantity'=>$quantity,'match_name'=>$match_name));
					$order_data["date_added"]     = format_date();
					$order_data["modified_date"]  = format_date();
					$user_txn_data[] = $order_data;

					// cache remove
					$user_teams_cache_key = 'user_teams_'.$value['question_id'].'_'.$value['user_id'];
					$this->delete_cache_data($user_teams_cache_key);
				}
			}

			try
			{
				$this->db->select("SUM(CASE WHEN UT.matchup_id = 0 THEN 1 ELSE 0 END) as unmatch_count,count(UT.user_team_id) as total,SQ.scheduled_date",FALSE)
				->from(USER_TEAM." AS UT")
				->join(SEASON_QUESTION." SQ", "SQ.question_id = UT.question_id", "INNER")            
				->where("UT.question_id",$question_id)
				->where("UT.status","0")
				->order_by("UT.question_id","ASC");
				$question_data = $this->db->get()->row_array();
				
				//update status
				$this->db->where_in('user_team_id',$t_user_team_ids);
				$this->db->update(USER_TEAM,array("status"=>"1","modified_date"=>$current_date));


				if(($type == 1) || ($question_data['unmatch_count'] == $question_data['total'] && $question_data['scheduled_date'] <= $current_date)){
					//update status
					$this->db->where('question_id',$question_id);
					$this->db->update(SEASON_QUESTION,array("status"=>"1","modified_date"=>$current_date));
				}

				if(!empty($user_txn_data) && !empty($t_user_team_ids)){
					$this->db_user = $this->load->database('user_db', TRUE);
					$this->db = $this->db_user;
					//Start Transaction
					$this->db->trans_strict(TRUE);
					$this->db->trans_start();
					
					$user_txn_arr = array_chunk($user_txn_data, 999);
					foreach($user_txn_arr as $txn_data){
						$this->insert_ignore_into_batch(ORDER, $txn_data);
					}

					$bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = 543 AND type=0 AND status=0 AND reference_id = '".$question_id."' GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
					SET U.balance = (U.balance + OT.real_amount),U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
					WHERE O.source = 543 AND O.type=0 AND O.status=0 AND O.reference_id = '".$question_id."' ";
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
						
						//remove users balance cache
						$ids = array_unique(array_column($user_txn_data,"user_id"));
						$this->remove_user_balance_cache($ids);

						
						$user_ids = implode(',', $user_ids_arr);
						// node broadcast
						$this->load->helper('queue');
						$server_name = get_server_host_name();
						$content = array('action_type'=>'trade_update','question_id'=>$question_id,'user_id'=>$user_ids);
						add_data_in_queue($content,'node_emitter');
					}
				}
			} catch (Exception $e)
			{
				$this->db->trans_rollback();
			}
		}else{

			$this->db->select("SQ.question_id",FALSE)
			->from(SEASON_QUESTION." AS SQ")
			->where("SQ.question_id",$question_id)
            ->where("SQ.status","0")
            ->where("SQ.scheduled_date <= ",$current_date);
			$question = $this->db->get()->row_array();
			if(!empty($question)){
				$this->db->where('question_id',$question_id);
				$this->db->update(SEASON_QUESTION,array("status"=>"1","modified_date"=>$current_date));
			}
		}
		
		return true;
	}

	public function update_question_status($season_id=''){
		$current_date = format_date();
 		$this->db->select("SQ.question_id,SQ.template_id,SQ.question,SQ.scheduled_date,S.home_id,S.away_id,S.score_stats,COUNT(UT.user_team_id) as total_users",FALSE)
			->from(SEASON_QUESTION." AS SQ")
			->join(SEASON." AS S", "S.season_id = SQ.season_id", "INNER")
			->join(USER_TEAM." AS UT", "UT.question_id = SQ.question_id AND UT.status=0", "LEFT")
			->where("SQ.scheduled_date <= ",$current_date)
            ->where("SQ.status","0")
            ->where("SQ.template_id > ","0")
            ->where("S.status","2")
            ->order_by("SQ.scheduled_date","DESC")
            ->group_by("SQ.question_id");
        if($season_id != ""){
        	$this->db->where("SQ.season_id",$season_id);
        }
		$record_list = $this->db->get()->result_array();
		//echo "<pre>";print_r($record_list);die;
		$question_data = array();
		$tie_question = array();
		foreach($record_list as $row){
			$question_id = $row['question_id'];
			$template_id = $row['template_id'];
			$home_id = $row['home_id'];
			$away_id = $row['away_id'];
			$score_stats = json_decode($row['score_stats'],TRUE);
			if($row['total_users'] > 0 && empty($score_stats)){
				continue;
			}
			
			$tmp_arr = array("question_id"=>$question_id,"answer"=>"0","modified_date"=>$current_date,"status"=>"2"); 
			if($template_id == 1 && isset($score_stats['win_team'])){
				$tmp_arr['answer'] = 2;
				if($home_id == $score_stats['win_team']){
					$tmp_arr['answer'] = 1;
				}
			}else if($template_id == 2 && isset($score_stats['win_team']) && isset($score_stats['first_bat'])){
				$tmp_arr['answer'] = 2;
				if($score_stats['win_team'] == $score_stats['first_bat']){
					$tmp_arr['answer'] = 1;
				}
			}else if($template_id == 3 && !empty($score_stats['sixes'])){
				$tmp_arr['answer'] = 2;
				if($score_stats['sixes'][$home_id] > $score_stats['sixes'][$away_id]){
					$tmp_arr['answer'] = 1;
				}
			}else if($template_id == 4 && !empty($score_stats['fours'])){
				$tmp_arr['answer'] = 2;
				if($score_stats['fours'][$home_id] > $score_stats['fours'][$away_id]){
					$tmp_arr['answer'] = 1;
				}
			}else if($template_id == 5 && isset($score_stats['win_team'])){
				$tmp_arr['answer'] = 2;
				if($home_id == $score_stats['win_team']){
					$tmp_arr['answer'] = 1;
				}
			}else if($template_id == 6 && !empty($score_stats['goals'])){
				$tmp_arr['answer'] = 2;
				$goal_diff = $score_stats['goals'][$home_id] - $score_stats['goals'][$away_id];
				if($home_id == $score_stats['win_team'] && $goal_diff >= 2){
					$tmp_arr['answer'] = 1;
				}
			}else if($template_id == 7 && !empty($score_stats['goal_minute'])){
				$tmp_arr['answer'] = 2;
				if($score_stats['goal_minute'] > 0 && $score_stats['goal_minute'] <= 30){
					$tmp_arr['answer'] = 1;
				}
			}else if($template_id == 8 && !empty($score_stats['goals'])){
				$tmp_arr['answer'] = 2;
				if($score_stats['goals'][$home_id] > $score_stats['goals'][$away_id]){
					$tmp_arr['answer'] = 1;
				}
			}else if($row['total_users'] == 0){
				$tmp_arr['status'] = 1;
			}else if($row['total_users'] > 0){
				if($row['template_id'] == 3 && !empty($score_stats['sixes']) && $score_stats['sixes'][$home_id] == $score_stats['sixes'][$away_id]){
					$tie_question[] = $question_id;
				}else if($row['template_id'] == 4 && !empty($score_stats['fours']) && $score_stats['fours'][$home_id] == $score_stats['fours'][$away_id]){
					$tie_question[] = $question_id;
				}else if($row['template_id'] == 5 && !empty($score_stats['goals']) && $score_stats['goals'][$home_id] == $score_stats['goals'][$away_id]){
					$tie_question[] = $question_id;
				}
			}
			if($row['total_users'] <= 0 && $tmp_arr['status'] == 2){
				$tmp_arr['status'] = 3;
			}
			if($tmp_arr['answer'] != 0 || $tmp_arr['status'] == 1){
				$question_data[] = $tmp_arr;
			}
		}

		//echo "<pre>";print_r($question_data);die;
		if(!empty($question_data)){
			try
    		{
    			//Start Transaction
	            $this->db->trans_strict(TRUE);
	            $this->db->trans_start();
	            $question_ids = array_unique(array_column($question_data,"question_id"));
	            //echo "<pre>";print_r($question_ids);die;
	            $question_arr = array_chunk($question_data, 999);
	            foreach($question_arr as $qst_list){
	            	$this->db->update_batch(SEASON_QUESTION,$qst_list,'question_id');
	            }

	            //update user entry status
	            if(!empty($question_ids)){
	            	$st_sql = "UPDATE ".$this->db->dbprefix(USER_TEAM)." AS UT INNER JOIN ".$this->db->dbprefix(SEASON_QUESTION)." AS SQ ON SQ.question_id=UT.question_id AND SQ.status=2  
			            SET UT.status = (CASE WHEN SQ.answer=UT.answer THEN 2 ELSE 3 END) 
			            WHERE UT.status = 0 AND UT.matchup_id > 0 AND UT.question_id IN(".implode(",",$question_ids).") ";
		            $this->db->query($st_sql);
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

			} catch (Exception $e)
	        {
          		$this->db->trans_rollback();
	        }
		}

		//forcefully cancel question for tie case
		if(!empty($tie_question)){
			$this->load->helper('queue');
      		$server_name = get_server_host_name();
      		foreach($tie_question as $qrow){
				$content = array();
				$content['url'] = $server_name."/trade/cron/cancel_question/".$qrow['question_id']."/1";
				add_data_in_queue($content,'cron');
	      	}
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
		$this->db->select('SQ.season_id,SQ.status', FALSE)
		    ->from(SEASON_QUESTION.' AS SQ')
		    ->where('SQ.status', 2)
		    ->where('SQ.scheduled_date < ', format_date())
		    ->group_by('SQ.season_id')
		    ->order_by('SQ.scheduled_date','DESC');
		$result = $this->db->get()->result_array();
		//echo "<pre>";print_r($result);die;
		if(!empty($result))
		{
			$this->load->helper('queue');
			$server_name = get_server_host_name();
			foreach($result as $prize)
			{
				$content = array();
				$content['url'] = $server_name."/trade/cron/match_prize_distribution/".$prize['season_id'];
				add_data_in_queue($content,'prize');
			}
		}
		return true;
	}

	/**
	* Function used for distribute match prize
	* @param int $season_id
	* @return boolean
	*/
	public function match_prize_distribution($season_id)
	{
		if(!$season_id){
			return false;
		}

		$this->db->select("SQ.question_id,SQ.season_id,SQ.question,SQ.cap,SQ.site_rake,SQ.currency_type,UT.user_team_id,UT.user_id,UT.entry_fee,UT.status",FALSE)
			->from(SEASON_QUESTION." AS SQ")
			->join(USER_TEAM." AS UT", "UT.question_id = SQ.question_id")
			->where("SQ.season_id",$season_id)
            ->where("SQ.status","2")
            ->where("UT.status","2");
		$record_list = $this->db->get()->result_array();
		//echo "<pre>";print_r($record_list);die;
    	if(!empty($record_list))
    	{
    		$this->db->select("S.season_id,S.scheduled_date,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.display_abbr,T2.team_abbr) as away",FALSE)
				->from(SEASON." AS S")
				->join(TEAM." T1", "T1.team_id = S.home_id", "INNER")
				->join(TEAM." T2", "T2.team_id = S.away_id", "INNER")
				->where("S.season_id",$season_id);
			$season_info = $this->db->get()->row_array();
			
			$user_lmc_data = array();
			$user_txn_data = array();
			$question_ids = array();
			foreach($record_list as $row){
		        $question_id = $row['question_id'];
		        $user_team_id = $row['user_team_id'];
		        $match_name = $season_info['home']." vs ".$season_info['away'];
		        $winning = $row['cap'] - ($row['cap'] * $row['site_rake'] / 100);
		        $points = $winning_amount = 0;
		        if($row['currency_type'] == 1){
		        	$winning_amount = $winning;
		        }else if($row['currency_type'] == 2){
		        	$points = $winning;
		        }

		        $lmc_data = array();
	            $lmc_data['user_team_id'] = $user_team_id;
	            $lmc_data['winning'] = $winning;
	            $lmc_data['modified_date'] = format_date();
	            $user_lmc_data[] = $lmc_data;

	            $custom_data = array("match_name"=>$match_name);
	            $order_data = array();
				$order_data["order_unique_id"] = $this->_generate_order_key();
				$order_data["user_id"]        = $row['user_id'];
				$order_data["source"]         = 544;
				$order_data["source_id"]      = $user_team_id;
				$order_data["reference_id"]   = $question_id;
				$order_data["season_type"]    = 1;
				$order_data["type"]           = 0;
				$order_data["status"]         = 0;
				$order_data["real_amount"]    = 0;
				$order_data["bonus_amount"]   = 0;
				$order_data["winning_amount"] = $winning_amount;
				$order_data["points"] = ceil($points);
				$order_data["custom_data"] = json_encode($custom_data);
				$order_data["date_added"]     = format_date();
				$order_data["modified_date"]  = format_date();
				$user_txn_data[] = $order_data;

				$question_ids[] = $question_id;
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
		            	$this->db->update_batch(USER_TEAM,$lmc_data,'user_team_id');
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
	          			$this->db = $this->load->database('user_db', TRUE);
			            //Start Transaction
			            $this->db->trans_strict(TRUE);
			            $this->db->trans_start();
		            
			            $user_txn_arr = array_chunk($user_txn_data, 999);
			            foreach($user_txn_arr as $txn_data){
		              		$this->insert_ignore_into_batch(ORDER, $txn_data);
			            }

		            	$question_ids_str = implode(",", array_unique($question_ids));
	              		$bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = 544 AND type=0 AND status=0 AND reference_id IN (".$question_ids_str.") GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
	              			SET U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
	              			WHERE O.source = 544 AND O.type=0 AND O.status=0 AND O.reference_id IN (".$question_ids_str.") ";
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

	              			//update question status
		              		$this->db_trade = $this->load->database('trade_db', TRUE);
							$this->db_trade->where('status',"2");
							$this->db_trade->where('season_id',$season_id);
							$this->db_trade->where_in('question_id', $question_ids);
							$this->db_trade->update(SEASON_QUESTION, array('status' => '3', 'modified_date' => format_date()));

							//remove users balance cache
		                    $ids = array_unique(array_column($user_txn_data,"user_id"));
		                    $this->remove_user_balance_cache($ids);
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
     * Prize notification
     * @param void
     * @return boolean
     */
    public function Notification($type)
    {
		$current_date = format_date();
        $this->db->select('UT.user_team_id,UT.user_id,UT.answer,UT.status,UT.matchup_id,GROUP_CONCAT(DISTINCT UT.question_id) as question_id,SQ.season_id,SQ.scheduled_date')
        ->from(USER_TEAM. " UT")
		->join(SEASON_QUESTION." SQ","SQ.question_id=UT.question_id");
		// type =1(win), type = 0(Cancel)
		if($type == 1){
			$this->db->where('UT.status',2); //Correct
		}else{
			$this->db->where('UT.status',1); //Cancelled
		}
		//$this->db->where('SQ.scheduled_date < ', format_date());
		$result = $this->db->where('UT.is_win_notify',0)
		->group_by("UT.user_id,SQ.season_id")
		->get()->result_array();
		//'<pre>'; print_R($result); die;
		if(!empty($result)){

			$season_ids = array_values(array_unique(array_column($result, 'season_id')));
			$user_ids = array_values(array_unique(array_column($result, 'user_id')));

			$match_arr = array();
			$season_data = $this->db->select('S.season_id,CONCAT(IFNULL(T1.display_abbr, T1.team_abbr), " vs ", IFNULL(T2.display_abbr, T2.team_abbr)) as home_vs_away')
			->from(SEASON." S")
			->join(LEAGUE." L", "L.league_id = S.league_id", "INNER")
			->join(TEAM." T1", "T1.team_id = S.home_id AND T1.sports_id=L.sports_id", "INNER")
			->join(TEAM." T2", "T2.team_id = S.away_id AND T2.sports_id=L.sports_id", "INNER")
			->where_in('S.season_id',$season_ids)
			->group_by("S.season_id")
			->get()->result_array();
			
			$match_arr = array_flip(array_column($season_data, 'season_id',"home_vs_away"));
			
			$this->db_user = $this->load->database('user_db', TRUE);
			$user_info = $this->db_user->select("U.user_id,U.email,U.user_name,U.user_name")
			->from(USER . " U")
			->where_in("U.user_id", $user_ids)
			->get()->result_array();
			
			$user_name_arr = array_flip(array_column($user_info, 'user_id',"user_name"));
			$user_email_arr = array_flip(array_column($user_info, 'user_id',"email"));
			
            foreach ($result as  $res){
				$season_id = $res['season_id'];
				$user_id = $res['user_id'];

				$match_name = isset($match_arr[$season_id])?$match_arr[$season_id]:'';
				$user_name = isset($user_name_arr[$user_id])?$user_name_arr[$user_id]:'';
				$user_email = isset($user_email_arr[$user_id])?$user_email_arr[$user_id]:'';
				$opinion = ($res['answer']==1)?"Yes":"No";
				if($res['scheduled_date'] < format_date()){
					$refund_type = ($res['matchup_id']==0)?"unmatched":"cancel";
				}else{
					$refund_type = "cancel";
				}
				
				//Send Notification
				$input = array('match_name'=>$match_name,'opinion'=>$opinion,'refund_type'=>$refund_type);
				$notify_data = array();
				$notify_data["notification_type"] = ($type)?TRADE_WON_NOTIFY:TRADE_REFUND_NOTIFY; //JoinGame, 
				$notify_data["source_id"] = $res['user_team_id'];
				$notify_data["notification_destination"] = 7; //Web,Push
				$notify_data["user_id"] = $user_id;
				$notify_data["to"] = $user_email;
				$notify_data["user_name"] = $user_name;
				$notify_data["added_date"] = $current_date;
				$notify_data["modified_date"] = $current_date;
				$notify_data["content"] = json_encode($input);
				$notify_data["subject"] = '';
				$this->load->model('user/User_nosql_model');
				$this->User_nosql_model->send_notification($notify_data); 

				// update notify status
				$this->db->where('user_id', $res['user_id']);
				if($type == 1){
					$this->db->where('status',2); //Correct
				}else{
					$this->db->where('status',1); //Cancelled
				}
				
				$q_ids = explode(',',$res['question_id']);
				$this->db->where_in('question_id',$q_ids);
				$this->db->update(USER_TEAM, array('is_win_notify' => '1','modified_date' => format_date()));
            }
        }
    }

	public function update_question_price($question_id){

		$this->db->select("SQ.question_id,SQ.option1_val as opt1,SQ.option2_val as opt2,COUNT(UT.user_team_id) as total,SUM(CASE WHEN UT.answer=1 THEN 1 ELSE 0 END) as opt1_bid,SUM(CASE WHEN UT.answer=2 THEN 1 ELSE 0 END) as opt2_bid",FALSE)
			->from(SEASON_QUESTION." AS SQ")
			->join(USER_TEAM." AS UT", "UT.question_id = SQ.question_id", "LEFT")
            ->where("SQ.question_id",$question_id)
			->where("UT.status !=",1)
			->group_by("SQ.question_id");
		$result = $this->db->get()->row_array();
		
		if($result){
			$price = calculate_question_price($result);
		}else{
			$price = array('opt1'=>'5.0','opt2'=>'5.0');
		}
		//echo "<pre>";print_r($price);die;
		try {
			//Start Transaction
			$this->db->trans_strict(TRUE);
			$this->db->trans_start();

			$this->db->where('question_id',$question_id);
			$this->db->update(SEASON_QUESTION,array("option1_val"=>$price['opt1'],"option2_val"=>$price['opt2']));

			$this->db->trans_complete();
			if ($this->db->trans_status() === FALSE )
			{
				$this->db->trans_rollback();
			}
			else
			{
				$this->db->trans_commit();
			}

		}catch(Exception $e){
			$this->db->trans_rollback();
		}
		
		return true;
	}

	

	/**
     * trade matchup
     * @param void
     * @return boolean
     */
    public function trade_matchup()
    {
		$current_date = format_date();
        $result = $this->db->select('UT.user_id,UT.question_id,UT.answer,UT.entry_fee')
        ->from(USER_TEAM. " UT")
		->join(SEASON_QUESTION." SQ","SQ.question_id=UT.question_id")
		->where('UT.status',0)
		->where('UT.matchup_id',0)
		->where('SQ.scheduled_date > ', format_date())
		->group_by("SQ.question_id,UT.user_id")
		->get()->result_array();
		//echo '<pre>'; print_r($result);die;
		if(!empty($result)){
			$this->load->helper('queue');
			$server_name = get_server_host_name();
			
			foreach($result as $value){
				// process for matchup
				$content = array('question_id'=>$value['question_id'],'user_id'=>$value['user_id'],'type'=>$value['answer'],'entry_fee'=>$value['entry_fee']);
				add_data_in_queue($content,'matchup');
			}
		}
	}

	/**
     * anwser matchup 
     * @param void
     * @return boolean
     */
    public function anwser_matchup($data)
    {
		$current_date = format_date();
        $result = $this->db->select('UT.user_team_id,UT.user_id,UT.answer,SQ.cap,UT.answer,UT.entry_fee,UT.question_id')
        ->from(USER_TEAM. " UT")
		->join(SEASON_QUESTION." SQ","SQ.question_id=UT.question_id")
		->where('UT.status',0)
		->where('UT.matchup_id',0)
		->where('UT.question_id',$data['question_id'])
		->where('UT.user_id',$data['user_id'])
		->where('UT.answer',$data['type'])
		->where('UT.entry_fee',$data['entry_fee'])
		->where('SQ.scheduled_date > ', format_date())
		->get()->result_array();
		//echo '<pre>'; print_R($result);die;
		if(!empty($result)){
			$matchup_user_ids = array();
			$updateData = array();
			
			$cap = $result[0]["cap"];
			$entry_fee = $result[0]["entry_fee"];
			$answer = $result[0]["answer"];
			$user_id = $result[0]['user_id'];
			$question_id = $result[0]['question_id'];

			$opp_price_val = number_format(($cap-$entry_fee),1,".","");
			$opp_answer = 1;
			if($answer == 1){
				$opp_answer = 2;
			}
			
			$check_matchup = $this->get_all_table_data("user_team_id,user_id,entry_fee,answer",USER_TEAM,array("matchup_id"=>0,"question_id"=>$question_id,"user_id != "=>$user_id,"entry_fee"=>$opp_price_val,"answer"=>$opp_answer,'status'=>0));


			if(!empty($check_matchup)){
				foreach($result as $key=>$value){
					if(!isset($check_matchup[$key]) && empty($check_matchup[$key])){
						break;
					}
	
					$updateData[] = array(
						'user_team_id' => $value['user_team_id'],
						'matchup_id' => $check_matchup[$key]['user_team_id']
					);
		
					$updateData[] = array(
						'user_team_id' => $check_matchup[$key]['user_team_id'],
						'matchup_id' => $value['user_team_id']
					);
				}
			}
			
			// Update the database outside the loop
			if (!empty($updateData)){
				
				$updateData_arr = array_chunk($updateData, 999);
				foreach($updateData_arr as $updateData_team){
					$this->db->where('matchup_id',0);
					$this->db->where('status',0);
					$this->db->update_batch(USER_TEAM, $updateData_team,'user_team_id');
				}
			}

			
			$this->load->helper('queue');
			$server_name = get_server_host_name();

			if(!empty($matchup_user_ids)){
				foreach($matchup_user_ids as $key=>$value){
					// node emitter
					$content = array('action_type'=>'matchup','question_id'=>$data['question_id'],'user_id'=>$key,'matchup_count'=>$value);
					add_data_in_queue($content,'node_emitter');
				}
			}
        }

		return true;
    }

	/**
	* This function used for the get match list according to sport id
	* @param sports_id
	* @return json array
	*/
    public function get_fixture_details($season_id) {
		$current_date = format_date();
 		$this->db->select("S.season_id,S.scheduled_date,S.is_published,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.display_abbr,T2.team_abbr) as away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,IFNULL(L.display_name,L.league_name) as league_name,S.status,L.sports_id",FALSE)
			->from(SEASON." AS S")
			->join(LEAGUE." L", "L.league_id = S.league_id", "INNER")
			->join(TEAM." T1", "T1.team_id = S.home_id AND T1.sports_id=L.sports_id", "INNER")
			->join(TEAM." T2", "T2.team_id = S.away_id AND T2.sports_id=L.sports_id", "INNER")
			->where("S.is_published","1")
            ->where("S.season_id",$season_id)
            ->order_by("S.scheduled_date","ASC")
			->group_by("S.season_id");

		$match_list = $this->db->get()->row_array();
		return $match_list;
	}


	public function anwser_cancel($data){
		if(empty($data['question_id']) || empty($data['user_id'])){
			return false;
		}


		$current_date = format_date();
		$question_id = $data['question_id'];
		$user_id = $data['user_id'];
		$type = $data['type'];
		$cancel_trade_count = 0;
		$this->db->select("GROUP_CONCAT(DISTINCT UT.user_team_id) as user_team_ids,UT.user_id,UT.answer,SUM(UT.entry_real) as total_entry,SUM(UT.entry_winning) as total_winning,SUM(UT.entry_coins) as total_coins,SUM(UT.entry_bonus) as total_bonus,UT.matchup_id,SQ.question_id,SQ.currency_type,SQ.season_id",FALSE)
		->from(USER_TEAM." AS UT")
		->join(SEASON_QUESTION." SQ", "SQ.question_id = UT.question_id", "INNER")            
		->where("UT.question_id",$question_id)
		->where("UT.user_id",$user_id)
		->where("UT.matchup_id",0)
		->where("UT.answer",$type)
		->where("UT.status",0)
		->where("SQ.scheduled_date >",$current_date)
		->group_by("SQ.question_id")
		->order_by("UT.question_id","ASC");
		$team_list = $this->db->get()->row_array();
		//print_R($team_list);die;
		if(!empty($team_list)){

			$user_team_ids = explode(',',$team_list['user_team_ids']);
			$quantity = count($user_team_ids);

			$real_amount = $bonus_amount = $winning_amount = $points = 0;
			
			if($team_list['currency_type'] == 1){
				$real_amount = $team_list['total_entry'];
				$winning_amount = $team_list['total_winning'];
			}elseif($team_list['currency_type'] == 2){
				$points = $team_list['total_coins'];
			}elseif($this->currency_type == 0){
				$bonus_amount = $team_list['total_bonus'];
			}

			$match_data = $this->get_fixture_details($team_list['season_id']);
			$match_name = isset($match_data)?$match_data['home'].' vs '.$match_data['away']:'';
			
			//user txn data
			$user_txn_data = array();
			$order_data = array();
			$order_data["order_unique_id"] = $this->_generate_order_key();
			$order_data["user_id"]        = $team_list['user_id'];
			$order_data["source"]         = 543;
			$order_data["source_id"]      = 0;
			$order_data["reference_id"]   = $team_list['question_id'];
			$order_data["season_type"]    = 1;
			$order_data["type"]           = 0;
			$order_data["status"]         = 0;
			$order_data["real_amount"]    = $real_amount;
			$order_data["bonus_amount"]   = $bonus_amount;
			$order_data["winning_amount"] = $winning_amount;
			$order_data["points"] 		  = $points;
			$order_data["custom_data"] 	  = json_encode(array('quantity'=>$quantity,'match_name'=>$match_name));
			$order_data["date_added"]     = format_date();
			$order_data["modified_date"]  = format_date();
			$user_txn_data[] = $order_data;

			// cache remove
			$user_teams_cache_key = 'user_teams_'.$team_list['question_id'].'_'.$team_list['user_id'];
			$this->delete_cache_data($user_teams_cache_key);

			$this->db_user = $this->load->database('user_db', TRUE);
			
			if(!empty($user_team_ids)){
				try
        		{
        			//update status
					$user_team_arr = array_chunk($user_team_ids, 999);
			        foreach($user_team_arr as $user_team){
						$this->db->where_in('user_team_id',$user_team);
						$this->db->where('matchup_id',0);
						$this->db->where('status',0);
						$this->db->update(USER_TEAM,array("status"=>"1","modified_date"=>$current_date));
					}
					
					$this->update_question_price($question_id);
					
	            	if(!empty($user_txn_data)){
		            	$this->db = $this->db_user;
			            //Start Transaction
			            $this->db->trans_strict(TRUE);
			            $this->db->trans_start();
			            
			            $user_txn_arr = array_chunk($user_txn_data, 999);
			            foreach($user_txn_arr as $txn_data){
		              		$this->insert_ignore_into_batch(ORDER, $txn_data);
			            }

			            $bal_sql = "UPDATE ".$this->db->dbprefix(USER)." AS U INNER JOIN ".$this->db->dbprefix(ORDER)." AS O ON O.user_id=U.user_id INNER JOIN (SELECT user_id,source,type,status,reference_id,SUM(real_amount) as real_amount,SUM(winning_amount) as winning_amount,SUM(bonus_amount) as bonus_amount,SUM(points) as points FROM ".$this->db->dbprefix(ORDER)." WHERE source = 543 AND type=0 AND status=0 AND reference_id = '".$question_id."' GROUP BY user_id) AS OT ON OT.user_id=U.user_id 
			            SET U.balance = (U.balance + OT.real_amount),U.winning_balance = (U.winning_balance + OT.winning_amount),U.bonus_balance = (U.bonus_balance + OT.bonus_amount),U.point_balance = (U.point_balance + OT.points),O.status=1 
			            WHERE O.source = 543 AND O.type=0 AND O.status=0 AND O.reference_id = '".$question_id."' ";
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
							
							$cancel_trade_count = count($user_team_ids);
							//remove users balance cache
		                    $ids = array_unique(array_column($user_txn_data,"user_id"));
		                    $this->remove_user_balance_cache($ids);

							$this->load->helper('queue');
      						$server_name = get_server_host_name();
							$content = array('action_type'=>'cancel','question_id'=>$question_id,'user_id'=>$user_id,'cancel_count'=>$cancel_trade_count,'type'=>$type);
							add_data_in_queue($content,'node_emitter');

						}
		          	}
            	} catch (Exception $e)
		        {
	          		$this->db->trans_rollback();
		        }
			}
		}
		return $cancel_trade_count;
	}


	public function add_auto_question_exchange($post_data)
	{
		if(isset($this->app_config['opinion_trade']) && $this->app_config['opinion_trade']['key_value'] == "0") {
            return true; 
        }
		$op_custom_data = $this->app_config['opinion_trade']['custom_data'];
        $currency_type = isset($op_custom_data['currency'])?$op_custom_data['currency']:'realcash';
		$currency = 1;
		if($currency_type == 'realcash'){
			$currency = 1;
		}elseif($currency_type == 'coins'){
			$currency = 2;
		}

		$question_arr = array();
		foreach ($post_data as $key => $value) 
		{
			
			if($value['question_type'] != "question")
			{
				continue;
			}

			if(!in_array($value['type'], array(6,8,9,11)))
			{
				continue;
			}

			//check question alreadt exist
			$reference_master = $this->get_single_row("reference_id",SEASON_QUESTION,array("reference_id"=>$value['question_id']));
			//echo "=====  <pre>";print_r($reference_master);die;
    		if(!empty($reference_master))
    		{
    			$this->db->where('reference_id', $reference_master['reference_id']);
              	$this->db->where('status',"0");
              	$this->db->set('scheduled_date',$value['end_date']);
              	$this->db->update(SEASON_QUESTION);
    			//continue;
    		}	
    		
    		if(empty($reference_master))
    		{
    			
				$current_date = format_date();	
				$this->db->select("S.season_id,S.scheduled_date,S.is_published,S.status,S.is_published",FALSE)
				->from(SEASON." AS S")
				->join(LEAGUE." L", "L.league_id = S.league_id", "INNER")
				->where("L.sports_id",$value['sports_id'])
	            ->where("L.league_uid",$value['league_id'])
	            ->where("S.season_game_uid",$value['match_id'])
	            ->where_in("S.status",array(0,1,3));
				$row = $this->db->get()->row_array();
				//echo "<pre>";print_r($row);die;
				
				if(isset($row['season_id']) && $row['season_id'] > 0)
				{
					$season_id = $row['season_id'];
					$scheduled_date = $value['end_date'];
					$question = $value['title'];
					$options = json_decode($value['options'],TRUE);
					
					$tmp_arr = array();
					$tmp_arr['season_id'] = $season_id;
					$tmp_arr['question'] = $question;
					$tmp_arr['scheduled_date'] = $scheduled_date;
					$tmp_arr['option1'] = $options[1];
					$tmp_arr['option2'] = $options[2];
					$tmp_arr['option1_val'] = "5.0";
					$tmp_arr['option2_val'] = "5.0";
					$tmp_arr['currency_type'] = $currency;
					$tmp_arr['added_date'] = $current_date;
					$tmp_arr['modified_date'] = $current_date;
					$tmp_arr['reference_id'] = $value['question_id'];
					$question_arr[] = $tmp_arr;
				}					
			}	
		}

		//echo "<pre>";print_r($question_arr);die;
		if(!empty($question_arr)){
			try{
				//Start Transaction
				$this->db->trans_strict(TRUE);
				$this->db->trans_start();
				
				//save question
				$this->db->insert_batch(SEASON_QUESTION, $question_arr);

				//Update Fixture publish status
				$season_ids = array_unique(array_column($question_arr,"season_id"));
              	$this->db->where_in('season_id', $season_ids);
              	$this->db->where('is_published',"0");
              	$this->db->update(SEASON, array("is_published"=>1));

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
		
		return true;
		//exit();
	}

	public function update_answer_exchange($post_data)
	{
		if(isset($this->app_config['opinion_trade']) && $this->app_config['opinion_trade']['key_value'] == "0") {
            return true; 
        }
		
		foreach ($post_data as $key => $value) 
		{
			if($value['question_type'] != "answer")
			{
				continue;
			}

			if(!in_array($value['type'], array(6,8,9,11)))
			{
				continue;
			}

			$reference_id = $value['question_id'];
			$answer = $value['answer'];
			//1-Closed,2-Cancel  from feed 
			$status = '2';
			if($value['status'] == '2')
			{
				$status = '1';
			}
			
			$modified_date = format_date();
			//update question if its open
			$this->db->where('reference_id',$reference_id);
			$this->db->where('status',0);
			$this->db->set('answer',$answer);
			$this->db->set('status',$status);
			$this->db->set('modified_date',$modified_date);
          	$this->db->update(SEASON_QUESTION);
          	//echo "<br>";echo $this->db->last_query();die;
          	//update user team 

          	$st_sql = "UPDATE ".$this->db->dbprefix(USER_TEAM)." AS UT 
          				INNER JOIN ".$this->db->dbprefix(SEASON_QUESTION)." AS SQ ON SQ.question_id=UT.question_id AND SQ.status=2  
			            SET UT.status = (CASE WHEN SQ.answer=UT.answer THEN 2 ELSE 3 END) 
			            WHERE 
			            UT.status = 0 AND UT.matchup_id > 0 AND SQ.reference_id = ".$reference_id." ";
		    $this->db->query($st_sql);
		}
	
		return true;
		//exit();
	}

}
