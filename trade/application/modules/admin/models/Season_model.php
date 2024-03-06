<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Season_model extends MY_Model{

  	function __construct()
  	{
	  	parent::__construct();
		$this->user_db = $this->load->database('user_db', TRUE);
	}

	/**
	 * Used for get season list
	 * @param array $post_data
	 * @return array
	 */
	public function get_season_list($post_data)
	{
		$current_date = format_date();
		$sort_field	= 'S.scheduled_date';
		$sort_order	= 'DESC';
		$pagination = get_pagination_data($post_data);
		$sports_id = $post_data['sports_id'];
		$status = isset($post_data['status']) ? $post_data['status'] : "upcoming";
		$col_join = "LEFT";
		$where = array("S.scheduled_date > " => $current_date);
		// $where = array("S.scheduled_date > " => "2023-08-18 06:07:42");

		// echo $status;die;
		if($status == "live"){
		
			$col_join = "INNER";
			$where = array("S.scheduled_date <= " => $current_date,"S.is_published"=>"1","S.status < "=>"2"); //,"CM.status"=>"0","CM.season_game_count"=>"1"
		}else if($status == "completed"){
			$col_join = "INNER";
			$where = array("S.scheduled_date <= " => $current_date,"S.is_published"=>"1","S.status"=>"2");
		}else{
			$sort_order	= 'ASC';
			// $where = array("S.scheduled_date >= " => $current_date,"S.is_published"=>"0");
		}

	// echo "<pre>";
	// print_r($where);die;

		$this->db->select("S.season_id,S.season_game_uid,S.league_id,S.format,S.scheduled_date,S.status,S.is_published,S.delay_minute,IFNULL(S.delay_message,'') as delay_message,IFNULL(L.display_name,L.league_name) AS league_name,S.is_pin_season",false)
			->from(SEASON." AS S")
			->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
			// ->join(COLLECTION_SEASON.' CS','CS.season_id = S.season_id',$col_join)
			// ->join(COLLECTION_MASTER.' CM','CM.collection_master_id = CS.collection_master_id AND CM.season_game_count=1',$col_join)
			->where("L.sports_id",$post_data['sports_id'])
			->where($where)
			->group_by("S.season_id")
			->order_by($sort_field, $sort_order);

	
			$like_where_str = ',CONCAT_WS(" vs ",IFNULL(T1.team_abbr,T1.team_abbr),IFNULL(T2.team_abbr,T2.team_abbr))';
			$this->db->select("IFNULL(T1.display_abbr,T1.team_abbr) AS home,IFNULL(T2.display_abbr,T2.team_abbr) AS away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag",false)
			->join(TEAM.' T1','T1.team_id = S.home_id','INNER')
			->join(TEAM.' T2','T2.team_id = S.away_id','INNER');

	

	

		if(isset($post_data['league_id']) && $post_data['league_id'] != "")
		{
			$this->db->where("S.league_id",$post_data['league_id']);
		}

		

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('LOWER(CONCAT(IFNULL(L.league_display_name,""),IFNULL(L.league_name,"")'.$like_where_str.'))', strtolower($post_data['keyword']) );
			
		}

		$tempdb_fantasy = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();

		$sql = $tempdb_fantasy->limit($pagination['limit'],$pagination['offset'])->get();
		$result	= $sql->result_array();
		// echo $this->db->last_query(); die;
		$result = isset($result) ? $result : array();
		return array('result' => $result,'total' => $total);
	}

	public function get_season_by_game_id($season_id,$league_id)
	{
		$sql = $this->db->select("S.*,L.league_name,IFNULL(T1.team_abbr,T1.team_abbr) AS home,IFNULL(T2.team_abbr,T2.team_abbr) AS away,L.sports_id,L.league_abbr,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,SUM(CASE WHEN UT.matchup_id > 0 THEN 1 ELSE 0 END) as matched, SUM(CASE WHEN UT.matchup_id = 0 THEN 1 ELSE 0 END) as unmatched,count(distinct(UT.user_id)) as unique_user_joined,count(UT.user_team_id) as total_entered", FALSE)
				->from(SEASON . " AS S")
				->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
				->join(SEASON_QUESTION.' SQ','SQ.season_id = S.season_id','LEFT')						
			    ->join(USER_TEAM.' UT','UT.question_id = SQ.question_id','LEFT')	
				->join(TEAM.' T1','T1.team_id = S.home_id AND L.sports_id=T1.sports_id','LEFT')
				->join(TEAM.' T2','T2.team_id = S.away_id AND L.sports_id=T2.sports_id','LEFT')
				->where("S.season_id", $season_id)
				->where("S.league_id", $league_id)
				->group_by("S.season_id")
				->get();
		$result = $sql->row_array();
		return $result;
	}



	public function update_match_delay_data($post_data){
	
		$league_id = $post_data['league_id'];
		$season_id = $post_data['season_id'];
		$delay_minute = $post_data['new_delay_minute'] - $post_data['delay_minute'];
		$season_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$delay_minute.' minutes', strtotime($post_data['scheduled_date'])));
        $upd_data = array('scheduled_date'=>$season_scheduled_date,'delay_minute' => $post_data['new_delay_minute'],'delay_message' => $post_data['delay_message'],"delay_by_admin"=>1);
        $this->db->where('season_id', $season_id);
        $this->db->where('league_id', $league_id);
        $this->db->update(SEASON, $upd_data);

		return true;
	}


	public function update_match_custom_message($post_data){
		$custom_message = $post_data['custom_message'];
		if(isset($post_data['is_remove']) && $post_data['is_remove'] == 1){
			$custom_message = "";
		}
		$upd_data = array('custom_message' => $custom_message,"notify_by_admin"=>1);
        $this->db->where('season_game_uid', $post_data['season_game_uid']);
        $this->db->where('league_id', $post_data['league_id']);
        $this->db->update(SEASON, $upd_data);
		return true;
	}

	public function add_question($data){
		$this->db->insert(SEASON_QUESTION,$data);
		$id = $this->db->insert_id();
	}

	public function get_season_question($season_id)
	{
		 $this->db->select("SQ.*,count(distinct(UT.user_id)) as participant", FALSE)
				->from(SEASON_QUESTION . " AS SQ")	
				->join(USER_TEAM.' UT','UT.question_id = SQ.question_id','LEFT')		
				->where("season_id", $season_id)
						
				 ->group_by("SQ.question_id");			
				$result = $this->db->get()->result_array();
				
		return $result;
	}

	public function update_answer($post_data){
		// echo"<pre>";
		// print_r($post_data);die;		
		$upd_data = array('answer' => $post_data['answer'],"status"=>2);
        $this->db->where('question_id', $post_data['question_id']);
		$this->db->update(SEASON_QUESTION, $upd_data);
		// user team data update
		$st_sql = "UPDATE ".$this->db->dbprefix(USER_TEAM)." AS UT INNER JOIN ".$this->db->dbprefix(SEASON_QUESTION)." AS SQ ON SQ.question_id=UT.question_id AND SQ.status=2  
			SET UT.status = (CASE WHEN SQ.answer=UT.answer THEN 2 ELSE 3 END) 
			WHERE UT.status = 0 AND UT.matchup_id > 0 AND UT.question_id=".$post_data['question_id'];
		$this->db->query($st_sql);

		return true;
	}

	public function get_question_users($question_id) {
    	if(!$question_id){
    		return array();
    	}
    	$this->db->select("UT.user_team_id,UT.answer,UT.entry_fee,UT.matchup_id,UT.added_date,UT.user_id,UT.status",FALSE)
			->from(USER_TEAM." AS UT")
			// ->join("tmp_users TU", "TU.user_id = UT.user_id", "INNER")
            ->where("UT.question_id",$question_id)
            ->order_by("UT.added_date","DESC")
			->group_by("UT.user_team_id");

		$record_list = $this->db->get()->result_array();
		return $record_list;
	}

	public function get_question_by_season($season_id)
	{
		 $this->db->select("SQ.question_id", FALSE)
				->from(SEASON_QUESTION . " AS SQ")					
				->where("season_id", $season_id);						
				//  ->group_by("SQ.question_id");			
				$result = $this->db->get()->result_array();				
		    return $result;
	}


	/**
	 * Function used for mark pin contest
	 * @param array $post_data
	 * @return array
	 */
	public function mark_pin_season($post_data)
	{
		if(empty($post_data)){
			return false;
		}

		$is_pin_season = 1;
		if(isset($post_data['is_pin_season'])){
			$is_pin_season = $post_data['is_pin_season'];
		}

		//update status in database
		$this->db->where('season_id',$post_data['season_id']);
		$this->db->set('is_pin_season', $is_pin_season);
		$this->db->update(SEASON);
		return true;
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
		
		$this->db->select("ut1.user_team_id, ut1.user_id, ut1.answer, ut1.entry_fee, ut1.matchup_id,ut1.status");
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

		// $this->db->where("ut1.status !=",1); 
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

}
