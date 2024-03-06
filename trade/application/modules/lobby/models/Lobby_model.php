<?php
class Lobby_model extends MY_Model {
    public function __construct() {
        parent::__construct();
		$this->user_db = $this->load->database('user_db', TRUE);
    }

    function __destruct() {
        $this->db->close();
        $this->user_db->close();
    }

    /**
	* This function used for the get match list according to sport id
	* @param sports_id
	* @return json array
	*/
    public function get_match_list($sports_id) {
		$current_date = format_date();
 		$this->db->select("S.season_id,S.scheduled_date,S.is_published,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.display_abbr,T2.team_abbr) as away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,T1.display_name as home_display_name,T2.display_name as away_display_name,IFNULL(L.display_name,L.league_name) as league_name,S.status,L.sports_id,S.is_pin_season",FALSE)
			->from(SEASON." AS S")
			->join(LEAGUE." L", "L.league_id = S.league_id", "INNER")
			->join(SEASON_QUESTION." SQ", "SQ.season_id = S.season_id", "INNER")
			->join(TEAM." T1", "T1.team_id = S.home_id AND T1.sports_id=L.sports_id", "INNER")
			->join(TEAM." T2", "T2.team_id = S.away_id AND T2.sports_id=L.sports_id", "INNER")
			->where("S.is_published","1")
			->where_in("S.status",array(0,1))
			->where("SQ.status","0")
            ->where("SQ.scheduled_date > ",$current_date)
            ->order_by("S.is_pin_season","DESC")
            ->order_by("S.scheduled_date","ASC")
			->group_by("S.season_id");

			if(isset($sports_id) && $sports_id != 0){
				$this->db->where("L.sports_id",$sports_id);
			}

		$match_list = $this->db->get()->result_array();
		return $match_list;
	}
	
	/**
	* This function used for the get question list according to sport id and season id
	* @param sports_id
	* @return json array
	*/
	public function get_sports_question($post_data) {
    	if(empty($post_data)){
    		return array();
    	}

		$current_date = format_date();
    	$page = get_pagination_data($post_data);
 		$this->db->select("SQ.question_id,SQ.season_id,SQ.question,SQ.scheduled_date,SQ.option1,SQ.option2,SQ.option1_val,SQ.option2_val,L.sports_id,SQ.added_date,SQ.currency_type",FALSE)
			->from(SEASON_QUESTION." AS SQ")
			->join(SEASON." S", "S.season_id = SQ.season_id", "INNER")
			->join(LEAGUE." L", "L.league_id = S.league_id", "INNER")
			->where("SQ.status","0")
            ->where("SQ.scheduled_date > ",$current_date)
			->where("S.is_published","1");
            
			
		if(isset($post_data['season_id']) && $post_data['season_id'] != ""){
			$this->db->where("SQ.season_id",$post_data['season_id']);
		}

		if(isset($post_data['sports_id']) && $post_data['sports_id'] != ""){
			$this->db->where("L.sports_id",$post_data['sports_id']);
		}

		$tempdb = clone $this->db; //to get rows for pagination
		$tempdb = $tempdb->select("count(SQ.question_id) as total");
		$temp_q = $tempdb->get()->row_array();
		$total = isset($temp_q['total']) ? $temp_q['total'] : 0; 
		
		// add limit
		$this->db->group_by("SQ.question_id")
			->order_by("SQ.scheduled_date","ASC")
            ->order_by("SQ.question_id","ASC")
			->limit($page['limit'],$page['offset']);
		$result = $this->db->get()->result_array();
		return array('question'=>$result,'total'=>$total);
	}

	/**
	* This function used for the get trade count
	* @param sports_id
	* @return json array
	*/
	public function get_trade_count($post_data){
    	if(empty($post_data)){
    		return array();
    	}
		$current_date = format_date();
 		$this->db->select(" SUM(CASE WHEN answer = 1 THEN UT.entry_fee ELSE 0 END) as total_yes,
		 SUM(CASE WHEN answer = 1 THEN 1 ELSE 0 END) as quntity_yes,
		 SUM(CASE WHEN answer = 1 AND matchup_id = 0 THEN UT.entry_fee ELSE 0 END) as total_yes_unmatched,
		 SUM(CASE WHEN answer = 1 AND matchup_id = 0 THEN 1 ELSE 0 END) as quntity_yes_unmatched,
		 SUM(CASE WHEN answer = 2 THEN UT.entry_fee ELSE 0 END) as total_no,
		 SUM(CASE WHEN answer = 2 THEN 1 ELSE 0 END) as quntity_no,
		 SUM(CASE WHEN answer = 2 AND matchup_id = 0 THEN UT.entry_fee ELSE 0 END) as total_no_unmatched,
		 SUM(CASE WHEN answer = 2 AND matchup_id = 0 THEN 1 ELSE 0 END) as quntity_no_unmatched,
		 MAX(CASE WHEN matchup_id = 0 AND answer = 1 THEN 1 ELSE 0 END) as cancel_btn_yes,
		 MAX(CASE WHEN matchup_id = 0 AND answer = 2 THEN 1 ELSE 0 END) as cancel_btn_no",FALSE)
			->from(USER_TEAM." AS UT")
			->where("UT.status !=",1)
			->where("UT.user_id",$post_data['user_id'])
			->where("UT.question_id",$post_data['question_id'])
            ->order_by("UT.added_date","DESC")
            ->group_by("UT.user_id");
			
		$record_list = $this->db->get()->row_array();
		return $record_list;
	}

	/**
	* This function used for the get user join team details
	* @param sports_id
	* @return json array
	*/
	public function save_team($post_data){
		try {
            //Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
            $user_team_id = 0;
			$this->load->model("user/User_model");
			
			$uteam = array();
			$t_entry = array("real"=>"0","winning"=>"0","bonus"=>"0","coin"=>"0");
			$balance = $this->User_model->get_user_balance($this->user_id);
			
			for($i=1;$i<=$post_data['quantity'];$i++){
				$contest_entry = array("real"=>"0","winning"=>"0","bonus"=>"0","coin"=>"0");
				if($post_data['entry_fee'] > 0){
					
					
					$t_entry_fee = $post_data["entry_fee"]*$post_data['quantity'];
					if($this->currency_type == 1){
						$amount = $post_data["entry_fee"];
						if($amount > $balance['balance']){
							$real = $balance['balance'];
							$amount = $amount - $real;
						}else{
							$real = $amount;
							$amount = $amount - $real;
						}
						$winning = $amount;

						$contest_entry['real'] = $real;
						$contest_entry['winning'] = $winning;

						$balance['balance'] = $balance['balance'] - $real;
						$balance['winning_balance'] = $balance['winning_balance'] - $winning;


					}elseif($this->currency_type == 2){
						$contest_entry['coin'] = $post_data["entry_fee"];
						$balance['point_balance'] = $balance['point_balance'] - $post_data["entry_fee"];
					}elseif($this->currency_type == 0){
						$contest_entry['bonus'] = $post_data["entry_fee"];
						$balance['bonus_balance'] = $balance['bonus_balance'] - $post_data["entry_fee"];
					}
				}

				// total amount for order table
				$t_entry['real'] = $t_entry['real'] + $contest_entry['real'];
				$t_entry['bonus'] = $t_entry['bonus'] + $contest_entry['bonus'];
				$t_entry['winning'] = $t_entry['winning'] + $contest_entry['winning'];
				$t_entry['coin'] = $t_entry['coin'] + $contest_entry['coin'];

				$temp = array();
				$temp['user_id'] 		= $post_data["user_id"];
	            $temp['sports_id'] 		= $post_data["sports_id"];
	            $temp['question_id'] 	= $post_data["question_id"];
	            $temp['answer'] 		= $post_data["option_id"];
	            $temp['entry_fee'] 		= $post_data["entry_fee"];
	            $temp['trade_price'] 	= $post_data["entry_fee"];
	            $temp['entry_real'] 	= $contest_entry['real'];
	            $temp['entry_winning'] 	= $contest_entry['winning'];
	            $temp['entry_coins'] 	= $contest_entry['coin'];
	            $temp['entry_bonus'] 	= $contest_entry['bonus'];
	            $temp['added_date'] 	= format_date();
	            $temp['modified_date'] = format_date();
				$uteam[] = $temp;
	        }

			if(!empty($uteam)){
				$this->db->insert_batch(USER_TEAM,$uteam);
	        	$user_team_id = $this->db->insert_id();
				$user_team_id = 1;
				$txn_status = 1;
				if(!empty($user_team_id)){
                    $txn_id = 0;
                    //entry fee deduct from user wallet
                    if($post_data['entry_fee'] > 0){
						
						// create data
                        $order_arr = array();
                        $order_arr['user_id'] = $this->user_id;
						$order_arr['source'] =  '542'; // joining
                        $order_arr['source_id'] = 0;
                        $order_arr['reference_id'] = $post_data["question_id"];
                        $order_arr['real_amount'] = $t_entry['real'];
                        $order_arr['bonus_amount'] = $t_entry['bonus'];
                        $order_arr['winning_amount'] = $t_entry['winning'];
                        $order_arr['points'] = $t_entry['coin'];
                        $order_arr['custom_data'] = array("match_name"=>$post_data['match_name'],'quantity'=>$post_data['quantity']);
                        $this->load->model("user/User_model");
                        
						$join_result = $this->User_model->deduct_entry_fee($order_arr);
                        if(!isset($join_result['result']) || $join_result['result'] != "1"){
                            $txn_status = 0;
                        }else{
                            $txn_id = isset($join_result['order_id']) ? $join_result['order_id'] : 0;
                        }
                    }
				}
			}
			
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE || $txn_status == 0)
            {
                $this->db->trans_rollback();
                return array();
            }else{
                $this->db->trans_commit();

                //update price in question 
                $this->update_question_price($post_data["question_id"]);
                return array("question_id" => $post_data["question_id"]);
            }
        }catch(Exception $e){
            $this->db->trans_rollback();
            return array();
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
		//echo "<pre>";print_r($result);die;
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
	* This function used for the get fixtrue question list
	* @param sports_id
	* @return json array
	*/
	public function get_fixture_question_list($season_id) {
    	if(!$season_id){
    		return array();
    	}
		$current_date = format_date();
 		$this->db->select("SQ.question_id,SQ.question,SQ.scheduled_date,SQ.option1,SQ.option2,SQ.option1_val,SQ.option2_val,SQ.added_date,SQ.currency_type",FALSE)
			->from(SEASON_QUESTION." AS SQ")
			->join(SEASON." S", "S.season_id = SQ.season_id", "INNER")
			->where("SQ.status","0")
			->where("S.status","0")
            //->where("SQ.scheduled_date > ",$current_date)
			->where("SQ.season_id",$season_id)
			->where("S.is_published","1")
            ->order_by("SQ.scheduled_date","ASC")
            ->order_by("SQ.question_id","ASC")
			->group_by("SQ.question_id");
		
		$result = $this->db->get()->result_array();
		return $result;
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

	/**
	* This function used for the get match list according to sport id
	* @param sports_id
	* @return json array
	*/
    public function get_fixture_list($season_ids) {
		$current_date = format_date();
 		$this->db->select("S.season_id,S.scheduled_date,S.is_published,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.display_abbr,T2.team_abbr) as away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,IFNULL(L.display_name,L.league_name) as league_name,S.status,L.sports_id",FALSE)
			->from(SEASON." AS S")
			->join(LEAGUE." L", "L.league_id = S.league_id", "INNER")
			->join(TEAM." T1", "T1.team_id = S.home_id AND T1.sports_id=L.sports_id", "INNER")
			->join(TEAM." T2", "T2.team_id = S.away_id AND T2.sports_id=L.sports_id", "INNER")
			->where("S.is_published","1")
            ->where_in("S.season_id",$season_ids)
            ->order_by("S.scheduled_date","ASC")
			->group_by("S.season_id");

		$match_list = $this->db->get()->result_array();
		return $match_list;
	}
	/**
	* This function used for the get question details
	* @param season_id
	* @return json array
	*/
	public function get_question_detail($question_id){
    	if(!$question_id){
    		return array();
    	}
 		$this->db->select("S.season_id,SQ.question_id,SQ.question,SQ.scheduled_date,SQ.option1,SQ.option2,SQ.option1_val,SQ.option2_val,SQ.added_date,SQ.currency_type,SQ.cap,SQ.status",FALSE)
			->from(SEASON_QUESTION." AS SQ")
			->join(SEASON." S", "S.season_id = SQ.season_id", "INNER")
			->where("SQ.question_id",$question_id)
			->where("S.is_published","1");
		
		$result = $this->db->get()->row_array();
		return $result;
	}

	
	/**
	* This function used for the get unmtached answer
	* @param question_id
	* @return json array
	*/
	public function get_question_unmatched_anwser($question_id) {
    	if(empty($question_id)){
    		return array();
    	}
		$current_date = format_date();
 		$this->db->select("UT.answer,UT.entry_fee,COUNT(UT.user_team_id) as total",FALSE)
			->from(USER_TEAM." AS UT")
			->join(SEASON_QUESTION." SQ", "SQ.question_id = UT.question_id", "INNER")
			->where("UT.user_id != ",$this->user_id)
			->where("UT.question_id",$question_id)
			->where("UT.matchup_id",0)
			->where("UT.status",0)
            ->group_by("UT.answer,UT.entry_fee")
			->order_by("UT.answer","ASC")
            ->order_by("UT.entry_fee","ASC");
			
		$record_list = $this->db->get()->result_array();
		return $record_list;
	}

	/**
	* This function used for the get my answer
	* @param question_id
	* @return json array
	*/
	public function get_my_answer($post_data) {
    	if(empty($post_data)){
    		return array();
    	}
		$current_date = format_date();
		$page = get_pagination_data($post_data);

 		$this->db->select("SQ.season_id,SQ.question_id,SUM(UT.entry_fee) as entry_fee,SUM(SQ.cap) as return_val,SQ.question,UT.sports_id,SQ.currency_type,SQ.scheduled_date,
		 SUM(CASE WHEN matchup_id > 0 AND UT.user_id = '".$post_data['user_id']."' THEN 1 ELSE 0 END) as matched_trade,
		 SUM(CASE WHEN matchup_id = 0 AND UT.user_id = '".$post_data['user_id']."' THEN 1 ELSE 0 END) as unmatched_trade,
		 SUM(CASE WHEN UT.user_id = '".$post_data['user_id']."' THEN 1 ELSE 0 END) as total_trade",FALSE)
		->from(USER_TEAM." AS UT")
		->join(SEASON_QUESTION." SQ", "SQ.question_id = UT.question_id", "INNER")
		->where("UT.user_id",$this->user_id)
		->where("UT.status",0)
		->where("SQ.status",0);
		if(isset($post_data['sports_id']) && !empty($post_data['sports_id'])){
			$this->db->where("UT.sports_id",$post_data['sports_id']);
		}
		if(isset($post_data['season_id']) && !empty($post_data['season_id'])){
			$this->db->where("SQ.season_id",$post_data['season_id']);
		}

		// current trade
		$current_trade = array(); 
		if(isset($page['offset']) && $page['offset']==0){

			// current trade investment and return
			$current_t = clone $this->db; //to get rows for pagination
			$current_t->select("
			max(CASE WHEN SQ.currency_type = 1 THEN 1 ELSE 0 END) as currency_real,
		 	max(CASE WHEN SQ.currency_type = 2 THEN 1 ELSE 0 END) as currency_coin,
			SUM(CASE WHEN SQ.currency_type = 1 THEN UT.entry_fee ELSE 0 END) as real_invest,
			SUM(CASE WHEN SQ.currency_type = 2 THEN UT.entry_fee ELSE 0 END) as coin_invest,
			SUM(CASE WHEN SQ.currency_type = 1 THEN SQ.cap ELSE 0 END) as real_return,
			SUM(CASE WHEN SQ.currency_type = 2 THEN SQ.cap ELSE 0 END) as coin_return");
			$current_r = $current_t->get()->row_array();

			$current_trade['currency_real'] = isset($current_r['currency_real']) ? $current_r['currency_real'] : 1;	
			$current_trade['currency_coin'] = isset($current_r['currency_coin']) ? $current_r['currency_coin'] : 0;	
			$current_trade['real_invest'] = isset($current_r['real_invest']) ? $current_r['real_invest'] : 0; 
			$current_trade['coin_invest'] = isset($current_r['coin_invest']) ? $current_r['coin_invest'] : 0; 
			$current_trade['real_return'] = isset($current_r['real_return']) ? $current_r['real_return'] : 0; 
			$current_trade['coin_return'] = isset($current_r['coin_return']) ? $current_r['coin_return'] : 0; 
		}
		
		$this->db->group_by("SQ.question_id");
		$this->db->order_by("UT.added_date","DESC");		
		// total
		$tempdb = clone $this->db;
		$query  = $this->db->get();
		$total  = $query->num_rows(); 

		// add limit
		$tempdb->limit($page['limit'],$page['offset']);
		$result = $tempdb->get()->result_array();
		
		$return = array();
		if($current_trade){
			$return['current_trade'] = $current_trade;
		}
		$return['total'] = $total;
		$return['answer'] = $result;
		
		return $return;
	}

	/**
	* This function used for the completed answer
	* @param
	* @return json array
	*/
	public function get_completed_list($post_data) {
    	if(empty($post_data)){
    		return array();
    	}
		$return = array();
		$current_date = format_date();
		$today_date = date('Y-m-d',strtotime($current_date));
 		$this->db->select("S.season_id,S.scheduled_date,SUM(UT.entry_fee) as entry_fee ,SUM(UT.winning) as winning,SQ.currency_type,S.scheduled_date,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.display_abbr,T2.team_abbr) as away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag",FALSE)
		->from(SEASON." S")
		->join(SEASON_QUESTION." SQ", "SQ.season_id = S.season_id", "INNER")
		->join(USER_TEAM." UT", "UT.question_id = SQ.question_id", "INNER")
		->join(LEAGUE." L", "L.league_id = S.league_id", "INNER")
		->join(TEAM." T1", "T1.team_id = S.home_id AND T1.sports_id=L.sports_id", "INNER")
		->join(TEAM." T2", "T2.team_id = S.away_id AND T2.sports_id=L.sports_id", "INNER")
		->where("UT.user_id",$this->user_id)
		->where_in("UT.status",array(2,3));
		if(isset($post_data['sports_id']) && !empty($post_data['sports_id'])){
			$this->db->where("UT.sports_id",$post_data['sports_id']);
		}
		$this->db->group_by("S.season_id");

		// total
		$tempdb = clone $this->db;
		$query  = $this->db->get();
		$total  = $query->num_rows();

		// pagination
		$page = get_pagination_data($post_data);
		$tempdb->limit($page['limit'],$page['offset']);
		$tempdb->order_by("UT.added_date","DESC");
		$result = $tempdb->get()->result_array();
		
		$return['total'] = $total;
		$return['completed'] = $result;
		return $return;
	}


	public function get_question_users($question_id) {
    	if(!$question_id){
    		return array();
    	}
    	$this->db->select("UT.user_team_id,UT.user_id,UT.answer,UT.entry_fee,UT.matchup_id,UT.added_date",FALSE)
			->from(USER_TEAM." AS UT")
			->where("UT.status !=",1)
			->where("UT.question_id",$question_id)
            ->order_by("UT.added_date","DESC")
			->group_by("UT.user_team_id");

		$record_list = $this->db->get()->result_array();
		return $record_list;
	}

	/**
     * used for get question participant users list
     * @param int $user_id
     * @return array
     */
    public function get_participant_user_details($user_ids)
    {
        $this->user_db->select("U.user_id,IFNULL(U.user_name, 'U.first_name') AS name,IFNULL(U.image,'') AS image",FALSE)
            ->from(USER.' U');
        $result = $this->user_db->where_in("U.user_id", $user_ids)
                ->order_by('U.user_name', 'ASC')
                ->get()
                ->result_array();
        return $result;
    }

	/**
	* This function used for the get question to question id
	* @param sports_id
	* @return json array
	*/
    public function get_question_list($question_ids){
		$current_date = format_date();
 		$this->db->select("SQ.question_id,SQ.question",FALSE)
			->from(SEASON_QUESTION." AS SQ")
			->where_in("SQ.question_id",$question_ids)
            ->order_by("SQ.question_id","ASC")
			->group_by("SQ.question_id");

		$match_list = $this->db->get()->result_array();
		return $match_list;
	}

	/**
	* This function used for the cancel awnser
	* @param post data
	* @return json array
	*/
    public function cancel_awnser($post_data){
		$current_date = format_date();
		$question_id = $post_data['question_id'];
		$cancel_trade_count = 0;
		$this->db->select("GROUP_CONCAT(DISTINCT UT.user_team_id) as user_team_ids,UT.user_id,UT.answer,SUM(UT.entry_real) as total_entry,SUM(UT.entry_winning) as total_winning,SUM(UT.entry_coins) as total_coins,SUM(UT.entry_bonus) as total_bonus,UT.matchup_id,SQ.question_id,SQ.currency_type,SQ.season_id",FALSE)
		->from(USER_TEAM." AS UT")
		->join(SEASON_QUESTION." SQ", "SQ.question_id = UT.question_id", "INNER")            
		->where("UT.question_id",$question_id)
		->where("UT.user_id",$this->user_id)
		->where("UT.matchup_id",0)
		->where("UT.answer",$post_data['type'])
		->where("UT.status",0)
		->where("SQ.scheduled_date >",$current_date)
		->group_by("SQ.question_id")
		->order_by("UT.question_id","ASC");
		$team_list = $this->db->get()->row_array();
		//print_R($team_list);die;
		if(!empty($team_list)){


			$user_team_ids = explode(',',$team_list['user_team_ids']);
			//$quantity = count($user_team_ids);
			$cancel_trade_count = count($user_team_ids);

			/*$real_amount = $bonus_amount = $winning_amount = $points = 0;
			
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

			$this->db_user = $this->load->database('user_db', TRUE);
			
			if(!empty($user_team_ids)){
				try
        		{
        			//update status
					$this->db->where_in('user_team_id',$user_team_ids);
					$this->db->where('matchup_id',0);
					$this->db->where('status',0);
	            	$this->db->update(USER_TEAM,array("status"=>"1","modified_date"=>$current_date));
					$last_query = $this->db->last_query();
					log_message("error",format_date()." cancel anwser = ".$last_query);


					$this->update_question_price($post_data["question_id"]);
					
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
						}
		          	}
            	} catch (Exception $e)
		        {
	          		$this->db->trans_rollback();
		        }
			}*/
		}
		return $cancel_trade_count;

	}
	
	/**
	* This function used for the get trade count question wise
	* @param season ids
	* @return json array
	*/
	public function question_trade_count($post_data){
    	if(empty($post_data['question_ids'])){
    		return array();
    	}
		$current_date = format_date();
 		$this->db->select(" SQ.question_id,
		 SUM(CASE WHEN matchup_id = 0 THEN 1 ELSE 0 END) as total_unmatched",FALSE);

		if(isset($post_data['user_id']) && $post_data['user_id']){
			$this->db->select("SUM(CASE WHEN matchup_id > 0 AND UT.user_id = '".$post_data['user_id']."' THEN 1 ELSE 0 END) as user_matched_trade,
		SUM(CASE WHEN matchup_id = 0 AND UT.user_id = '".$post_data['user_id']."' THEN 1 ELSE 0 END) as user_unmatched_trade,
		SUM(CASE WHEN UT.user_id = '".$post_data['user_id']."' THEN 1 ELSE 0 END) as user_total_trade",FALSE);
		}else{
			$this->db->select("SUM(CASE WHEN matchup_id > 0  THEN 1 ELSE 0 END) as total_matched,
			SUM(CASE WHEN user_team_id THEN 1 ELSE 0 END) as total_trade",FALSE);
		}

		 $this->db->from(SEASON_QUESTION." AS SQ")
		 	->join(USER_TEAM." UT", "UT.question_id = SQ.question_id", "LEFT")
			->where("UT.status !=",1)
			->where_in("SQ.question_id",$post_data['question_ids'])
			->group_by("SQ.question_id");
			
		$record_list = $this->db->get()->result_array();
		return $record_list;
	}


	/**
	* This function used for the get trade activity
	* @param question_id
	* @return json array
	*/
	public function get_trade_activity($post_data){
    	if(empty($post_data['question_id'])){
    		return array();
    	}
		
		$page = get_pagination_data($post_data);
		
		$this->db->select("ut1.user_team_id, ut1.user_id, ut1.answer, ut1.entry_fee, ut1.matchup_id");
		$this->db->select("IFNULL(ut2.user_id,'') AS m_user_id,
		(CASE
        WHEN ut1.answer = 1 THEN 2
        WHEN ut1.answer = 2 THEN 1
        ELSE 1
    	END) AS m_answer,IFNULL(ut2.entry_fee,10-ut1.entry_fee) AS m_entry_fee");
		$this->db->from(USER_TEAM.' AS ut1');
		$this->db->join(USER_TEAM.' AS ut2', 'ut1.matchup_id = ut2.user_team_id', 'left');
		$this->db->where('ut1.question_id',$post_data['question_id']);
		
		if(isset($post_data['user_id']) && $post_data['user_id']){
			$this->db->where('ut1.user_id',$post_data['user_id']);
			//$this->db->where("(ut1.answer = 1 or ut1.matchup_id = 0 or ut1.matchup_id != 0)");
		}else{
			$this->db->where("(ut1.answer = 1 or ut1.matchup_id = 0)");
		}

		$this->db->where("ut1.status !=",1);
		$this->db->order_by('ut1.matchup_id','AES');
		
		$tempdb = clone $this->db; //to get rows for pagination
		$tempdb = $tempdb->select("count(ut1.user_team_id) as total");
		$temp_q = $tempdb->get()->row_array();
		$total = isset($temp_q['total']) ? $temp_q['total'] : 0;

		$this->db->limit($page['limit'],$page['offset']);
		$result = $this->db->get()->result_array();
		
		return array('result'=>$result,'total'=>$total);
	}

	/**
	* This function used for the get order book
	* @param question_id
	* @return json array
	*/
	public function get_order_book($question_id){
    	if(empty($question_id)){
    		return array();
    	}
		
		$this->db->select('entry_fee,answer,COUNT(entry_fee) as total,SUM(CASE WHEN matchup_id = 0 THEN 1 ELSE 0 END) AS unmatched,SUM(CASE WHEN matchup_id != 0 THEN 1 ELSE 0 END) AS matched');
		$this->db->from(USER_TEAM.' AS UT');
		$this->db->where('question_id',$question_id);
		$this->db->where("UT.status !=",1);
		$this->db->group_by('entry_fee,answer');
		$this->db->order_by('entry_fee','AES');
		$result = $this->db->get()->result();

		return $result;
	}
	
	/**
	* This function used for the completed answer
	* @param
	* @return json array
	*/
	public function total_user_data($post_data) {
    	if(empty($post_data)){
    		return array();
    	}
		$return = array();
		$current_date = format_date();
		$today_date = date('Y-m-d',strtotime($current_date));
 		$this->db->select("
		 max(CASE WHEN SQ.currency_type = 1 THEN 1 ELSE 0 END) as currency_real,
		 max(CASE WHEN SQ.currency_type = 2 THEN 1 ELSE 0 END) as currency_coin,
		 SUM(CASE WHEN SQ.currency_type = 1 THEN UT.entry_fee ELSE 0 END) as total_real_invest,
		 SUM(CASE WHEN SQ.currency_type = 2 THEN UT.entry_fee ELSE 0 END) as total_coin_invest,
		 SUM(CASE WHEN SQ.currency_type = 1 AND UT.status = 2 THEN UT.winning ELSE 0 END) as total_real_winning,
		 SUM(CASE WHEN SQ.currency_type = 2 AND UT.status = 2 THEN UT.winning ELSE 0 END) as total_coin_winning,
		 SUM( CASE WHEN SQ.scheduled_date BETWEEN '".$today_date." 00:00:00' AND '".$today_date." 23:59:59' AND SQ.currency_type = 1  THEN  UT.entry_fee ELSE  0  END ) AS today_real_invest,
		 SUM( CASE WHEN SQ.scheduled_date BETWEEN '".$today_date." 00:00:00' AND '".$today_date." 23:59:59' AND SQ.currency_type = 2  THEN  UT.entry_fee ELSE  0  END ) AS today_coin_invest,
		 SUM( CASE WHEN SQ.scheduled_date BETWEEN '".$today_date." 00:00:00' AND '".$today_date." 23:59:59' AND SQ.currency_type = 1 AND UT.status = 2 THEN  UT.winning ELSE  0  END ) AS today_real_winning,
		 SUM( CASE WHEN SQ.scheduled_date BETWEEN '".$today_date." 00:00:00' AND '".$today_date." 23:59:59' AND SQ.currency_type = 2 AND UT.status = 2 THEN  UT.winning ELSE  0  END ) AS today_coin_winning,
		 SUM(CASE WHEN WEEK(SQ.scheduled_date,1) = WEEK(NOW(),1) AND SQ.currency_type = 1 THEN UT.entry_fee ELSE 0 END) AS week_real_invest,
		 SUM(CASE WHEN WEEK(SQ.scheduled_date,1) = WEEK(NOW(),1) AND SQ.currency_type = 2 THEN UT.entry_fee ELSE 0 END) AS week_coin_invest,
		 SUM(CASE WHEN WEEK(SQ.scheduled_date,1) = WEEK(NOW(),1) AND SQ.currency_type = 1 AND UT.status = 2 THEN UT.winning ELSE 0 END) AS week_real_winning,
		 SUM(CASE WHEN WEEK(SQ.scheduled_date,1) = WEEK(NOW(),1) AND SQ.currency_type = 2 AND UT.status = 2 THEN UT.winning ELSE 0 END) AS week_coin_winning,
		 SUM(CASE WHEN MONTH(SQ.scheduled_date) = MONTH(NOW()) AND SQ.currency_type = 1 THEN UT.entry_fee ELSE 0 END) AS month_real_invest,
		 SUM(CASE WHEN MONTH(SQ.scheduled_date) = MONTH(NOW()) AND SQ.currency_type = 2 THEN UT.entry_fee ELSE 0 END) AS month_coin_invest,
		 SUM(CASE WHEN MONTH(SQ.scheduled_date) = MONTH(NOW()) AND SQ.currency_type = 1 AND UT.status = 2 THEN UT.winning ELSE 0 END) AS month_real_winning,
		 SUM(CASE WHEN MONTH(SQ.scheduled_date) = MONTH(NOW()) AND SQ.currency_type = 2 AND UT.status = 2 THEN UT.winning ELSE 0 END) AS month_coin_winning",FALSE)


		->from(USER_TEAM." AS UT")
		->join(SEASON_QUESTION." SQ", "SQ.question_id = UT.question_id", "INNER")
		->where("UT.user_id",$this->user_id)
		->where_in("UT.status",array(2,3));
		if(isset($post_data['sports_id']) && !empty($post_data['sports_id'])){
			$this->db->where("UT.sports_id",$post_data['sports_id']);
		}
		
		$result = $this->db->get()->row_array();
		return $result;
	}

	/**
	* This function used for the completed answer
	* @param season_id
	* @return json array
	*/
	public function get_completed_question($season_id) {
    	if(empty($season_id)){
    		return array();
    	}
		
 		$this->db->select("SQ.question_id,SQ.question,SUM(UT.entry_fee) as entry_fee ,SUM(UT.winning) as winning,SQ.currency_type",FALSE)
		->from(SEASON_QUESTION." AS SQ")
		->join(USER_TEAM." UT", "UT.question_id = SQ.question_id", "INNER")
		->where("UT.user_id",$this->user_id)
		->where("SQ.season_id",$season_id)
		->where_in("UT.status",array(2,3));
		$this->db->group_by("SQ.question_id");
		$this->db->order_by("SQ.scheduled_date","DESC");
		$result = $this->db->get()->result_array();
		return $result;
	}

	/**
	* This function used for the get trade count question wise
	* @param season ids
	* @return json array
	*/
	public function question_trade_count_for_node($data){
    	if(empty($data['question_id'])){
    		return array();
    	}

		$this->db->select("SQ.question_id,UT.user_id,SUM(CASE WHEN matchup_id > 0 THEN 1 ELSE 0 END) as user_matched_trade,
		SUM(CASE WHEN matchup_id = 0 THEN 1 ELSE 0 END) as user_unmatched_trade,
		SUM(CASE WHEN user_team_id THEN 1 ELSE 0 END) as user_total_trade",FALSE);
		
		 $this->db->from(SEASON_QUESTION." AS SQ")
		 	->join(USER_TEAM." UT", "UT.question_id = SQ.question_id", "LEFT")
			->where("UT.status !=",1)
			->where("SQ.question_id",$data['question_id'])
			->where_in("UT.user_id",$data['user_ids'])
			->group_by("SQ.question_id,UT.user_id");
			
		$record_list = $this->db->get()->result_array();
		return $record_list;
	}
}
