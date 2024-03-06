<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Season_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
	}

	/** 
     * common function used to get single record from any table
     * @param string    $select
     * @param string    $table
     * @param array/string $where
     * @return	array
     */
    public function get_fantasy_single_row($select = '*', $table, $where = "") {
        $this->db_fantasy->select($select, FALSE);
        $this->db_fantasy->from($table);
        if ($where != "") {
            $this->db_fantasy->where($where);
        }
        $this->db_fantasy->limit(1);
        $query = $this->db_fantasy->get();
        return $query->row_array();
    }

	/**
	 * Used for get season list
	 * @param array $post_data
	 * @return array
	 */
	public function get_season_list($post_data)
	{
		$current_date = format_date();
		$sort_field	= 'S.season_scheduled_date';
		$sort_order	= 'DESC';
		$pagination = get_pagination_data($post_data);
		$sports_id = $post_data['sports_id'];
		$status = isset($post_data['status']) ? $post_data['status'] : "upcoming";
		$col_join = "LEFT";
		$where = array("S.season_scheduled_date > " => $current_date);
		if($status == "live"){
			$col_join = "INNER";
			$where = array("S.season_scheduled_date <= " => $current_date,"S.is_published"=>"1","CM.status"=>"0","CM.season_game_count"=>"1");
		}else if($status == "completed"){
			$col_join = "INNER";
			$where = array("S.season_scheduled_date <= " => $current_date,"S.is_published"=>"1","CM.status"=>"1","CM.season_game_count"=>"1");
		}else{
			$sort_order	= 'ASC';
		}

		$this->db_fantasy->select("S.season_id,S.season_game_uid,S.league_id,S.format,S.season_scheduled_date,S.status,S.status_overview,S.is_published,S.is_salary_changed,S.delay_minute,IFNULL(S.delay_message,'') as delay_message,IFNULL(S.custom_message,'') as custom_message,S.scoring_alert,IFNULL(S.tournament_name,'') as tournament_name,S.match_event,S.is_tour_game,IFNULL(S.end_scheduled_date,S.season_scheduled_date) as end_scheduled_date,S.2nd_inning_date,IFNULL(L.league_display_name,L.league_name) AS league_name,IFNULL(CM.collection_master_id,0) as collection_master_id,IFNULL(CM.is_pin,0) as is_pin,IFNULL(L.image,'') as league_image",false)
			->from(SEASON." AS S")
			->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
			->join(COLLECTION_SEASON.' CS','CS.season_id = S.season_id',$col_join)
			->join(COLLECTION_MASTER.' CM','CM.collection_master_id = CS.collection_master_id AND CM.season_game_count=1',$col_join)
			->where("L.sports_id",$post_data['sports_id'])
			->where($where)
			->group_by("S.season_id")
			->order_by($sort_field, $sort_order);

		if(!in_array($sports_id,$this->tour_game_sports)){
			$like_where_str = ',CONCAT_WS(" vs ",IFNULL(T1.display_team_abbr,T1.team_abbr),IFNULL(T2.display_team_abbr,T2.team_abbr))';
			$this->db_fantasy->select("IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag",false)
			->join(TEAM.' T1','T1.team_id = S.home_id','INNER')
			->join(TEAM.' T2','T2.team_id = S.away_id','INNER');
		}

		if(isset($post_data['league_id']) && $post_data['league_id'] != "")
		{
			$this->db_fantasy->where("S.league_id",$post_data['league_id']);
		}

		if(isset($post_data['published']) && $post_data['published'] == "0")
		{
			$this->db_fantasy->having("collection_master_id","0");
		}else if(isset($post_data['published']) && $post_data['published'] == "1")
		{
			$this->db_fantasy->having("collection_master_id >","0");
		}

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db_fantasy->like('LOWER(CONCAT(IFNULL(L.league_display_name,""),IFNULL(L.league_name,"")'.$like_where_str.'))', strtolower($post_data['keyword']) );
			
		}

		$tempdb_fantasy = clone $this->db_fantasy;
		$query = $this->db_fantasy->get();
		$total = $query->num_rows();

		$sql = $tempdb_fantasy->limit($pagination['limit'],$pagination['offset'])->get();
		$result	= $sql->result_array();
		// echo $this->db_fantasy->last_query(); die;
		$result = isset($result) ? $result : array();
		return array('result' => $result,'total' => $total);
	}

	/**
	 * Used for get season detail by season id
	 * @param int $season_id
	 * @return array
	 */
	public function get_season_detail($season_id,$select="")
	{
		if($select != ""){
            $this->db_fantasy->select($select,false);    
        }
		$this->db_fantasy->select("S.season_id,S.season_game_uid,S.league_id,S.format,S.type,S.season_scheduled_date,S.home_id,S.away_id,S.status,S.is_published,S.is_salary_changed,IFNULL(S.tournament_name,'') as tournament_name,S.match_event,S.playing_announce,IFNULL(S.playing_list,'[]') as playing_list,IFNULL(S.substitute_list,'[]') as substitute_list,S.playing_eleven_confirm,S.delay_minute,IFNULL(S.delay_message,'') as delay_message,IFNULL(S.custom_message,'') as custom_message,S.scoring_alert,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,L.sports_id,L.league_abbr,IFNULL(L.league_display_name,L.league_name) AS league_name,IFNULL(L.image,'') as league_image,IFNULL(CM.collection_master_id,0) as collection_master_id,IFNULL(CM.is_pin,0) as is_pin,S.is_tour_game,IFNULL(S.end_scheduled_date,S.season_scheduled_date) as end_scheduled_date,IFNULL(S.2nd_inning_date,'') as 2nd_inning_date,IFNULL(T1.display_team_name,T1.team_name) AS home_name,IFNULL(T2.display_team_name,T2.team_name) AS away_name",false)
			->from(SEASON." AS S")
			->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
			->join(TEAM.' T1','T1.team_id = S.home_id','LEFT')
			->join(TEAM.' T2','T2.team_id = S.away_id','LEFT')
			->join(COLLECTION_SEASON.' CS','CS.season_id = S.season_id',"LEFT")
			->join(COLLECTION_MASTER.' CM','CM.collection_master_id = CS.collection_master_id AND CM.season_game_count=1',"LEFT")
			->where("S.season_id",$season_id)
			->group_by("S.season_id");

		$sql = $this->db_fantasy->get();
		$result = $sql->row_array();
		return ($result) ? $result : array();
	}

	/**
    * Function used for get season players list
    * @param int $season_id
    * @return array
    */
	public function get_season_player_list($season_id,$lineup_data=0)
	{ 
		$this->db_fantasy->select("PT.player_team_id,PT.position,PT.last_match_played,IFNULL(PT.salary,0) as salary,PT.feed_verified,PT.is_published,PT.is_new,P.player_id,P.player_uid,P.full_name,P.display_name,IFNULL(P.image,'') as player_image,T.team_id,IFNULL(T.display_team_name,T.team_name) AS team_name,IFNULL(T.display_team_abbr,T.team_abbr) AS team_abbr,IFNULL(T.jersey,T.feed_jersey) AS jersey", FALSE);
        $this->db_fantasy->from(PLAYER_TEAM." AS PT");
		$this->db_fantasy->join(PLAYER." AS P","P.player_id = PT.player_id","INNER");
        $this->db_fantasy->join(TEAM. " AS T","T.team_id = PT.team_id", "INNER");
		$this->db_fantasy->where('PT.season_id',$season_id);
		$this->db_fantasy->where("PT.is_deleted","0");
		$this->db_fantasy->where("PT.player_status","1");
        $this->db_fantasy->order_by("PT.salary","DESC");
        $this->db_fantasy->group_by("P.player_id");

        if($lineup_data == 1){
        	$this->db_fantasy->select('(CASE WHEN JSON_SEARCH(S.playing_list,"one",P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_playing,S.playing_announce',FALSE);
        	$this->db_fantasy->select('(CASE WHEN JSON_SEARCH(S.substitute_list,"one",P.player_id) IS NOT NULL THEN 1 ELSE 0 END) as is_sub',FALSE);
        	$this->db_fantasy->join(SEASON.' S','S.season_id = PT.season_id','INNER');
        }

		$query = $this->db_fantasy->get(); 
		$player_list = $query->result_array();
        return $player_list;
	}

	/**
    * Function used for update season players publish data
    * @param array $update_data
    * @return boolean
    */
	public function update_season_roster_salary($update_data)
	{
		$this->db_fantasy->update_batch(PLAYER_TEAM,$update_data,'player_team_id');
		$result = $this->db_fantasy->affected_rows();
		return $result;
	}

	/**
    * Function used for update players display name
    * @param array $update_data
    * @return boolean
    */
	public function update_player_display_name($display_names_data)
	{
		$this->db_fantasy->update_batch(PLAYER,$display_names_data,'player_id');
		$result = $this->db_fantasy->affected_rows();
		return $result;
	}

	/**
    * Function used for get season players team wise count
    * @param int $season_id
    * @return array
    */
	public function get_season_player_count_data($season_id)
	{ 
		$this->db_fantasy->select("PT.team_id,PT.position,SUM(CASE WHEN PT.is_published=1 THEN 1 ELSE 0 END) as total", FALSE);
        $this->db_fantasy->from(PLAYER_TEAM." AS PT");
		$this->db_fantasy->where('PT.season_id',$season_id);
		$this->db_fantasy->where("PT.is_deleted","0");
		$this->db_fantasy->where("PT.player_status","1");
        $this->db_fantasy->group_by("PT.position");
        $this->db_fantasy->group_by("PT.team_id");
		$query = $this->db_fantasy->get(); 
		$result = $query->result_array();
        return $result;
	}

	/**
	 * used to get tour game sports teams list
	 * @param int $sports_id
	 * @return array
	*/
	public function get_tour_game_sport_teams($sports_id)
	{
		$this->db_fantasy->select("T.team_id,T.team_uid,IFNULL(T.display_team_abbr,T.team_abbr) as team_abbr,IFNULL(T.display_team_name,T.team_name) AS team_name,IFNULL(T.jersey,T.feed_jersey) as jersey,IFNULL(T.flag,T.feed_flag) as flag",FALSE)
			->from(TEAM.' AS T')
			->where("T.sports_id",$sports_id)
			->group_by("T.team_id")
			->order_by("T.team_name", "DESC");

		$sql = $this->db_fantasy->get();
		$result = $sql->result_array();
		return $result;
	}

	/**
    * Function used for publish fixtire
    * @param array $post_data
    * @return array
    */
	public function save_fixture_publish($post_data)
	{
		$season_id = $post_data['season_id'];
		$this->db_fantasy->select("CM.collection_master_id", FALSE);
        $this->db_fantasy->from(COLLECTION_SEASON." AS CS");
		$this->db_fantasy->join(COLLECTION_MASTER." AS CM","CM.collection_master_id = CS.collection_master_id","INNER");
		$this->db_fantasy->where('CS.season_id',$season_id);
		$this->db_fantasy->where("CM.season_game_count","1");
		$query = $this->db_fantasy->get(); 
		$check_exist = $query->row_array();
		if(empty($check_exist)){
			try{
				//Start Transaction
	            $this->db_fantasy->trans_strict(TRUE);
	            $this->db_fantasy->trans_start();

	            $current_date = format_date();
	            $collection = array();
	            $collection["league_id"] = $post_data['league_id'];
	            $collection["collection_name"] = $post_data['collection_name'];
	            $collection["season_scheduled_date"] = $post_data['season_scheduled_date'];
				$collection["deadline_time"] = '0';
	            $collection["added_date"] = $current_date;
	            $collection["modified_date"] = $current_date;
	            $collection["is_tour_game"] = isset($post_data['is_tour_game']) ? $post_data['is_tour_game'] : 0;
	            if(isset($post_data['secong_inning_date']) && $post_data['secong_inning_date'] != ""){
	            	$collection['2nd_inning_date'] = $post_data['secong_inning_date'];
	            }
	            if(isset($post_data['setting']) && !empty($post_data['setting'])){
	            	$collection['setting'] = json_encode($post_data['setting']);
	            }
				$this->db_fantasy->insert(COLLECTION_MASTER,$collection);
				$cm_id = $this->db_fantasy->insert_id();
				if($cm_id){
					$collection_season_data = array();
					$collection_season_data['collection_master_id'] = $cm_id;
					$collection_season_data['season_id'] = $season_id;
					$collection_season_data['season_scheduled_date'] = $post_data['season_scheduled_date'];
					$collection_season_data['added_date'] = $current_date;
					$collection_season_data['modified_date'] = $current_date;
					$this->db_fantasy->insert(COLLECTION_SEASON,$collection_season_data);

					$update_data = array("is_published"=>1,"is_salary_changed"=>1,'modified_date'=>$current_date);
					$this->db_fantasy->where("season_id",$season_id);
	        		$this->db_fantasy->update(SEASON,$update_data);

	        		//Trasaction End
		            $this->db_fantasy->trans_complete();
		            if ($this->db_fantasy->trans_status() === FALSE )
		            {
		                $this->db_fantasy->trans_rollback();
						return false;
		            }
		            else
		            {
		                $this->db_fantasy->trans_commit();
		                return $cm_id;
		            }
				}else{
					return false;
				}
			}
			catch(Exception $e){
				$this->db_fantasy->trans_rollback();
		        return false;
	      	}
		}
		return false;
	}

	/**
     * used to get fixture detail
     * @param int $collection_master_id
     * @return array
    */
    public function get_fixture_detail($cm_id) {
        if(!$cm_id){
            return false;
        }

        $this->db_fantasy->select("CM.collection_master_id,CM.collection_name,CM.season_scheduled_date,IFNULL(CM.2nd_inning_date,'') as 2nd_inning_date,CM.status,CM.season_game_count,CM.is_gc,CM.is_pin,CM.is_tour_game,GROUP_CONCAT(DISTINCT CS.season_id) as season_ids,IFNULL(L.league_display_name,L.league_name) AS league_name,IFNULL(L.image,'') as league_image,L.sports_id,L.league_id,CM.is_h2h,CM.collection_name as tournament_name,IFNULL(CM.setting,'[]') as setting", FALSE);
        $this->db_fantasy->from(COLLECTION_MASTER." as CM");
        $this->db_fantasy->join(COLLECTION_SEASON.' as CS', 'CM.collection_master_id = CS.collection_master_id', "INNER");
        $this->db_fantasy->join(LEAGUE.' as L', 'L.league_id = CM.league_id', "INNER");
        $this->db_fantasy->where("CM.collection_master_id", $cm_id);
        $this->db_fantasy->group_by("CM.collection_master_id");
        $result = $this->db_fantasy->get()->row_array();
        return $result;
    }

    /**
     * Used for get season detail by season ids
     * @param array $season_ids
     * @param string $select
     * @return array
     */
    public function get_fixture_season_detail($season_ids,$select="")
    {
        if(empty($season_ids)){
            return false;
        }

        if($select != ""){
            $this->db_fantasy->select($select,false);    
        }
        $this->db_fantasy->select("S.season_id,S.season_game_uid,S.league_id,S.format,S.season_scheduled_date,S.playing_announce,S.delay_minute,IFNULL(S.delay_message,'') as delay_message,IFNULL(S.custom_message,'') as custom_message,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IFNULL(T1.display_team_name,T1.team_name) AS home_name,IFNULL(T2.display_team_name,T2.team_name) AS away_name,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag",false)
            ->from(SEASON." AS S")
            ->join(TEAM.' T1','T1.team_id = S.home_id','INNER')
            ->join(TEAM.' T2','T2.team_id = S.away_id','INNER')
            ->where_in("S.season_id",$season_ids)
            ->group_by("S.season_id")
            ->order_by("S.season_scheduled_date","ASC");

        $sql = $this->db_fantasy->get();
        $result = $sql->result_array();
        return ($result) ? $result : array();
    }

    /**
     * Used for get tennis season detail by season ids
     * @param array $season_ids
     * @return array
     */
    public function get_tennis_season_detail($season_ids)
    {
        if(empty($season_ids)){
            return false;
        }

        $this->db_fantasy->select("SM.season_match_id,SM.season_id,SM.match_id,SM.home_id,SM.away_id,SM.scheduled_date as season_scheduled_date,IFNULL(P1.display_name,P1.full_name) AS home,IFNULL(P2.display_name,P2.full_name) AS away",false)
            ->from(SEASON_MATCH." AS SM")
            ->join(PLAYER.' P1','P1.player_id = SM.home_id','INNER')
            ->join(PLAYER.' P2','P2.player_id = SM.away_id','INNER')
            ->where_in("SM.season_id",$season_ids)
            ->group_by("SM.season_match_id")
            ->order_by("SM.scheduled_date","ASC");

        $sql = $this->db_fantasy->get();
        $result = $sql->result_array();
        return ($result) ? $result : array();
    }

    /**
     * Used for get tour game season detail by season id
     * @param int $season_id
     * @return array
     */
    public function get_tour_season_detail($season_ids)
    {
        if(empty($season_ids)){
            return false;
        }
        $this->db_fantasy->select("S.season_id,S.league_id,S.scheduled_date,S.delay_minute,IFNULL(S.delay_message,'') as delay_message,IFNULL(S.custom_message,'') as custom_message,S.tournament_name,S.match_event,IFNULL(S.end_scheduled_date,S.season_scheduled_date) as end_scheduled_date,S.is_tour_game",false)
            ->from(SEASON." AS S")
            ->where_in("S.season_id",$season_ids)
            ->group_by("S.season_id")
            ->order_by("S.scheduled_date","ASC");
        $sql = $this->db_fantasy->get();
        $result = $sql->result_array();
        return ($result) ? $result : array();
    }

    /**
	 * Function used for mark pin contest
	 * @param array $post_data
	 * @return array
	 */
	public function mark_pin_fixture($post_data)
	{
		if(empty($post_data)){
			return false;
		}

		$is_pin_fixture = 1;
		if(isset($post_data['is_pin_fixture'])){
			$is_pin_fixture = $post_data['is_pin_fixture'];
		}

		//update status in database
		$this->db_fantasy->where('collection_master_id',$post_data['collection_master_id']);
		$this->db_fantasy->set('is_pin', $is_pin_fixture);
		$this->db_fantasy->update(COLLECTION_MASTER);
		return true;
	}

    /**
    * Function used for save fixtire delay
    * @param array $post_data
    * @return array
    */
	public function update_match_delay_data($post_data){
        try{
			//Start Transaction
            $this->db_fantasy->trans_strict(TRUE);
            $this->db_fantasy->trans_start();

            $current_date = format_date();
            $collection_master_id = $post_data['collection_master_id'];
            $delay_minute = $post_data['new_delay_minute'] - $post_data['delay_minute'];
			$season_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$delay_minute.' minutes', strtotime($post_data['season_scheduled_date'])));

			//update season
	        $upd_data = array('season_scheduled_date'=>$season_scheduled_date,'delay_minute' => $post_data['new_delay_minute'],'delay_message' => $post_data['delay_message'],"delay_by_admin"=>1,"modified_date"=>$current_date);
	        $this->db_fantasy->where('season_id', $post_data['season_id']);
        	$this->db_fantasy->update(SEASON, $upd_data);

        	if($collection_master_id > 0){
        		//Update schedule date in collection table
	            $this->db_fantasy->where('collection_master_id', $collection_master_id);
	            $this->db_fantasy->update(COLLECTION_MASTER,array("season_scheduled_date"=>$season_scheduled_date,"modified_date"=>$current_date));

				//Update schedule date in collection season table
	            $this->db_fantasy->where('collection_master_id', $collection_master_id);
	            $this->db_fantasy->update(COLLECTION_SEASON,array("season_scheduled_date"=>$season_scheduled_date,"modified_date"=>$current_date));

	            //Update schedule date in contest table
	            $this->db_fantasy->where('collection_master_id', $collection_master_id);
				$this->db_fantasy->update(CONTEST,array("season_scheduled_date"=>$season_scheduled_date,"modified_date"=>$current_date));
			}

			//Trasaction End
            $this->db_fantasy->trans_complete();
            if ($this->db_fantasy->trans_status() === FALSE )
            {
                $this->db_fantasy->trans_rollback();
				return false;
            }
            else
            {
                $this->db_fantasy->trans_commit();
                return true;
            }
        }
		catch(Exception $e){
			$this->db_fantasy->trans_rollback();
	        return false;
      	}
	}

	/**
    * Function used for save fixtire custom message
    * @param array $post_data
    * @return array
    */
	public function update_match_custom_message($post_data){
		try{
			$custom_message = $post_data['custom_message'];
			if(isset($post_data['is_remove']) && $post_data['is_remove'] == 1){
				$custom_message = "";
			}
			$upd_data = array('custom_message' => $custom_message,"notify_by_admin"=>1,"modified_date"=>format_date());
	        $this->db_fantasy->where('season_id', $post_data['season_id']);
	        $this->db_fantasy->update(SEASON, $upd_data);
			return true;
		}
		catch(Exception $e){
	        return false;
      	}
	}

	/**
    * Function used for save fixtire custom message
    * @param array $post_data
    * @return array
    */
	public function save_playing11($post_data){
		try{
			$current_date = format_date();
			$upd_data = array('playing_announce' => 1,'is_updated_playing' => 1,'playing_list' => json_encode($post_data['playing_list']),"substitute_list"=>json_encode($post_data['substitute_list']),'lineup_announced_at'=> $current_date,"modified_date"=>$current_date);
			$this->db_fantasy->where('season_id', $post_data['season_id']);
	        $this->db_fantasy->update(SEASON, $upd_data);
			return true;
		}
		catch(Exception $e){
	        return false;
      	}
	}

	/**
    * Function used for get league detail
    * @param int $league_id
    * @return array
    */
	public function get_league_detail_by_id($league_id)
	{
		$result = $this->db_fantasy->select("league_id,league_uid,league_name,league_abbr,sports_id,IFNULL(image,'') as image",FALSE)
					->from(LEAGUE)
					->where("league_id", $league_id)
					->where("active", '1')
					->get()
					->row_array();
		return ($result) ? $result : array();
	}

	/**
	 * function used for get season stats data
	 * @param array $post_data
	 * @return array
	 */
	public function get_all_season_stats($post_data)
	{
		$sport_id = $post_data['sports_id'];
        $sport_fields = $this->config->item('season_stats_field');
        $fields = $sport_fields[$sport_id];
        $column = implode(',', $fields);
        unset($fields["home_team_score"]);
        unset($fields["away_team_score"]);
		$tableheader = array_keys($fields);

		$sort_field = 'PT.position';
		$sort_order = 'DESC';
		switch ($sport_id) {
			case CRICKET_SPORTS_ID:
				$table_name = GAME_STATISTICS_CRICKET;
				break;
            case SOCCER_SPORTS_ID:
				$table_name = GAME_STATISTICS_SOCCER;
				break;
			case KABADDI_SPORTS_ID:
				$table_name = GAME_STATISTICS_KABADDI;
				break;
			case BASEBALL_SPORTS_ID:
				$table_name = GAME_STATISTICS_BASEBALL;
				break;	
			case BASKETBALL_SPORTS_ID:
				$table_name = GAME_STATISTICS_BASKETBALL;
				break;
			case FOOTBALL_SPORTS_ID:
				$table_name = GAME_STATISTICS_FOOTBALL;
				break;
			case MOTORSPORT_SPORTS_ID:
				$table_name = GAME_STATISTICS_MOTORSPORT;
				break;
			case TENNIS_SPORTS_ID:
				$table_name = GAME_STATISTICS_TENNIS;
				break;
			default:
				break;
		}

		$this->db_fantasy->select("$column", FALSE)
			->from($table_name." T1")
			->join(PLAYER_TEAM." PT","PT.season_id = T1.season_id AND T1.player_id=PT.player_id","INNER" )
			->join(PLAYER." P","P.player_id = PT.player_id","INNER")
			->join(TEAM." T","T.team_id = PT.team_id","INNER" )
			->where('T1.season_id', $post_data['season_id'])
			->where('P.sports_id',$sport_id)
			->group_by("T1.player_id")
			->order_by($sort_field, $sort_order);

		if($sport_id == BASEBALL_SPORTS_ID)
		{
			$this->db_fantasy->group_by("T1.scoring_type");
		}
		if(isset($post_data['match_inning']) && $sport_id == CRICKET_SPORTS_ID)
		{
			$this->db_fantasy->where("T1.innings",$post_data['match_inning']);
		}
		
		$sql = $this->db_fantasy->get();
		$result	= $sql->result_array();
		$result = ($result) ? $result : array();
		return array('fields'=>$tableheader, 'result'=>$result, 'total'=>count($result));
	}

	/**
	 * function used for update season details
	 * @param array $post_data
	 * @return boolean
	 */
    public function update_season_status($post_data){
    	$update_data = array('match_status' => $post_data['match_status'], 'modified_date' => format_date());
        //if status is publish then set match status closed
        if($post_data['match_status'] == 2){
            $update_data['status'] = 2;
            $update_data['status_overview'] = 4;
        }
        
        $this->db_fantasy->where('season_id', $post_data['season_id']);
        $this->db_fantasy->update(SEASON, $update_data);
        return true;
    }

    /**
	 * function used for update player score data
	 * @param array $post_data
	 * @return boolean
	 */
    public function update_player_match_score($post_data){
        $sports_id = $post_data['sports_id'];
        $season_id = $post_data['season_id'];
    	$player_id = $post_data['player_data']['player_id'];
        //for cricket
        if($sports_id == CRICKET_SPORTS_ID){
            $innings = $post_data['match_inning'];
            unset($post_data['player_data']['player_id']);
            unset($post_data['player_data']['player_uid']);
            unset($post_data['player_data']['player_name']);
            unset($post_data['player_data']['position']);
            unset($post_data['player_data']['team_name']);
            unset($post_data['player_data']['scheduled_date']);
            unset($post_data['player_data']['display_name']);
            $data_arr = $post_data['player_data'];

            $this->db_fantasy->where('player_id', $player_id);
            $this->db_fantasy->where('season_id', $season_id);
            $this->db_fantasy->where('innings', $innings);
            $this->db_fantasy->update(GAME_STATISTICS_CRICKET, $data_arr);

        }else if($post_data['sports_id'] == KABADDI_SPORTS_ID){
            unset($post_data['player_data']['player_id']);
            unset($post_data['player_data']['player_uid']);
            unset($post_data['player_data']['player_name']);
            unset($post_data['player_data']['position']);
            unset($post_data['player_data']['team_name']);
            unset($post_data['player_data']['scheduled_date']);
            unset($post_data['player_data']['display_name']);
            $data_arr = $post_data['player_data'];

            $this->db_fantasy->where('player_id', $player_id);
            $this->db_fantasy->where('season_id', $season_id);
            $this->db_fantasy->update(GAME_STATISTICS_KABADDI, $data_arr);
        }else if($post_data['sports_id'] == SOCCER_SPORTS_ID){//for soccer
            unset($post_data['player_data']['player_id']);
            unset($post_data['player_data']['player_uid']);
            unset($post_data['player_data']['player_name']);
            unset($post_data['player_data']['position']);
            unset($post_data['player_data']['team_name']);
            unset($post_data['player_data']['scheduled_date']);
            unset($post_data['player_data']['display_name']);
            $data_arr = $post_data['player_data'];

            $this->db_fantasy->where('player_id', $player_id);
            $this->db_fantasy->where('season_id', $season_id);
            $this->db_fantasy->update(GAME_STATISTICS_SOCCER, $data_arr);
        }else if($post_data['sports_id'] == BASEBALL_SPORTS_ID){//for baseball

        	$scoring_type = $post_data['player_data']['scoring_type'];
            unset($post_data['player_data']['player_id']);
            unset($post_data['player_data']['player_uid']);
            unset($post_data['player_data']['player_name']);
            unset($post_data['player_data']['position']);
            unset($post_data['player_data']['team_name']);
            unset($post_data['player_data']['scheduled_date']);
            unset($post_data['player_data']['scoring_type']);
            unset($post_data['player_data']['display_name']);
            $data_arr = $post_data['player_data'];

            $this->db_fantasy->where('player_id', $player_id);
            $this->db_fantasy->where('season_id', $season_id);
            $this->db_fantasy->where('scoring_type', $scoring_type);
            $this->db_fantasy->update(GAME_STATISTICS_BASEBALL, $data_arr);
        }else if($post_data['sports_id'] == TENNIS_SPORTS_ID){//for tennis
            unset($post_data['player_data']['player_id']);
            unset($post_data['player_data']['player_uid']);
            unset($post_data['player_data']['player_name']);
            unset($post_data['player_data']['position']);
            unset($post_data['player_data']['team_name']);
            unset($post_data['player_data']['scheduled_date']);
            unset($post_data['player_data']['display_name']);
            $data_arr = $post_data['player_data'];

            $this->db_fantasy->where('player_id', $player_id);
            $this->db_fantasy->where('season_id', $season_id);
            $this->db_fantasy->update(GAME_STATISTICS_TENNIS, $data_arr);
        }
        return true;
    }

    /**
	 * function used for update season status and date
	 * @param array $post_data
	 * @return boolean
	 */
    public function move_season_to_live($post_data){
    	$update_data = array('match_status' => 0,'status' => 0,'status_overview' => 0, 'match_closure_date' => NULL, 'modified_date' => format_date());
        $this->db_fantasy->where('season_id', $post_data['season_id']);
        $this->db_fantasy->update(SEASON, $update_data);

        if(isset($post_data['cm_id']) && $post_data['cm_id'] > 0){
        	$this->db_fantasy->set('status', '0');
	        $this->db_fantasy->set('is_lineup_processed', '1');
	        $this->db_fantasy->where("status",'1');
	        $this->db_fantasy->where("collection_master_id",$post_data['cm_id']);
	        $this->db_fantasy->update(COLLECTION_MASTER);
        }
        return true;
    }

    public function update_2nd_inning_date($post_data)
	{	
		$season_data = array('2nd_inning_date' => $post_data['scheduled_date'],'second_inning_update' => 1);
		$this->db_fantasy->trans_start();
		
		//udpate season
		$this->db_fantasy->where('season_id',$post_data['season_id']);
		$this->db_fantasy->update(SEASON,$season_data);
		
		//update collection 
		$update_collection_data = array('2nd_inning_date' => $post_data['scheduled_date']);
		$this->db_fantasy->where('collection_master_id',$post_data['collection_master_id']);
		$this->db_fantasy->update(COLLECTION_MASTER,$update_collection_data);

		//update contest
		$update_contest_data = array('season_scheduled_date' => $post_data['scheduled_date']);
		$this->db_fantasy->where('collection_master_id',$post_data['collection_master_id']);
		$this->db_fantasy->where('is_2nd_inning',1);
		$this->db_fantasy->update(CONTEST,$update_contest_data);

		$this->db_fantasy->trans_complete();
		$this->db_fantasy->trans_strict(FALSE);

		if ($this->db_fantasy->trans_status() === FALSE)
		{
		    $this->db_fantasy->trans_rollback();
			return false;
		}
		else
		{
			$this->db_fantasy->trans_commit();
			return true;
		}
	}



	/*
	*@method get_lobby_season_matches
	*@uses function to get upcoming matches of one month,include last 3 days from current date
	******/
	public function get_season_matches_by_ids($post_data)
	{	
		$this->load->helper('cron_helper');
	    $current_date_time = format_date();
		$this->db_fantasy->select("S.home_id,S.away_id, S.api_week, S.week, S.season_game_uid, S.league_id, S.season_scheduled_date, S.format, L.sports_id,IFNULL(L.league_display_name,L.league_name) AS league_abbr,IFNULL(L.league_display_name,L.league_name) AS league_name,IFNULL(L.image,'') as image,(CASE WHEN format = 1 THEN 'ONE-DAY' WHEN format = 2 THEN 'TEST' WHEN format = 3 THEN 'T20' WHEN format = 4 THEN 'T10' ELSE '' END) AS format_str,S.status,S.status_overview,S.score_data,S.is_tour_game",false);
		$this->db_fantasy->from(SEASON . " AS S")
				->join(LEAGUE . " AS L", "L.league_id = S.league_id", "INNER");

		if(!in_array($post_data['sports_id'],$this->tour_game_sports))
		{
			$this->db_fantasy->select("IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,T1.team_id as home_id,IFNULL(T1.flag,T1.feed_flag) AS home_flag,T2.team_id as away_id,IFNULL(T2.flag,T2.feed_flag) AS away_flag,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away",false);
			$this->db_fantasy->join(TEAM.' T1','T1.team_id=S.home_id');
			$this->db_fantasy->join(TEAM.' T2','T2.team_id=S.away_id');
		}
		$this->db_fantasy->where("L.active",1);
		if(!empty($post_data['sports_id']))
		{
			$this->db_fantasy->where("L.sports_id", $post_data['sports_id']);
		}

		if(!empty($post_data['season_game_uids']))
		{	 
			$this->db_fantasy->where_in("S.season_game_uid ", $post_data['season_game_uids']);
		}

		if(!empty($post_data['q']) &&  $post_data['search'] == TRUE)
		{
			$q = $post_data['q'];
			 $this->db_fantasy->group_start();
			 $this->db_fantasy->like("T1.team_name",$q,"both");
			 $this->db_fantasy->or_like("T2.team_name",$q,"both");
			 $this->db_fantasy->or_like("T1.team_abbr",$q,"both");
			 $this->db_fantasy->or_like("T2.team_abbr",$q,"both");
			 $this->db_fantasy->group_end();
		}

		if(!empty($post_data['team_uid']))
		{
			$this->db_fantasy->where("(home_uid IN ('".$post_data['team_uid']."') OR away_uid IN ('".$post_data['team_uid']."'))");
		}

		$this->db_fantasy->group_by("S.season_game_uid");
		$this->db_fantasy->order_by("S.season_scheduled_date","ASC");
		$sql = $this->db_fantasy->get();
		$matches = $sql->result_array();
		foreach ($matches as $key => $rs) {
			
			$matches[$key]["game_starts_in"] = strtotime($matches[$key]["season_scheduled_date"])*1000;
			$matches[$key]["today"] = strtotime(format_date())*1000;
			$matches[$key]["current_timestamp"] = strtotime(format_date())*1000;
			$matches[$key]["scheduled_timestamp"] = strtotime($matches[$key]["season_scheduled_date"])*1000;
			//$matches[$key]["image"] = get_image(2,$matches[$key]["image"]);
			//$matches[$key]['home_flag'] = get_image(0,$matches[$key]['home_flag']);
            //$matches[$key]['away_flag'] = get_image(0,$matches[$key]['away_flag']);
		}
		return $matches;
	}


	public function get_sport_id_by_cmid($collection_master_id){
		$rs = $this->db_fantasy->select("MS.sports_name", FALSE)
						->from(CONTEST . " AS C")
						->join(MASTER_SPORTS.' AS MS','MS.sports_id=C.sports_id','left')
						->where("C.collection_master_id", $collection_master_id)
						->limit(1) 
						->get();
		$result = $rs->result_array()[0];
		return $result;
	}


}
/* End of file Season_model.php */
/* Location: ./application/models/Season_model.php */