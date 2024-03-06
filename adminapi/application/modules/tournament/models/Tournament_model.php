<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tournament_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
	}

	public function get_single_row($select = '*', $table, $where = "") {
		$this->db_fantasy->select($select);
		$this->db_fantasy->from($table);
		if ($where != "") {
			$this->db_fantasy->where( $where );
		}
		$query = $this->db_fantasy->get();
		return $query->row_array();
	}

	public function get_all_table_data($column,$table,$league_id=array())
	{
		$this->db_fantasy->select($column)
						->from($table);

		if(!empty($league_id))
		{
			$this->db_fantasy->where($league_id);	
		}
		$sql = $this->db_fantasy->get();
		return $sql->result_array();
	}

	/**
     * Updates whole row [unlike update_field()]
     * @param array $data
     * @param int   $id
     */
    public function update($table = "", $data, $where = "") {
        $return_flag = FALSE;
        if (!is_array($data)) {
            log_message('error', 'Supposed to get an array!');
        } else if ($table == "") {
            log_message('error', 'Got empty table name');
        } else if ($where == "") {
            log_message('error', 'Got empty where condition');
        } else {
            $this->db_fantasy->where($where);
            $this->db_fantasy->update($table, $data);
            $return_flag = TRUE;
        }
        return $return_flag;
    }

	/**
	 * Used for get league list by sports id
	 * @param int $sports_id
	 * @return array
	 */
	public function get_sport_league_list($post_data)
	{
		$current_date = format_date();
		$this->db_fantasy->select('L.league_id,L.league_uid,L.league_abbr,IFNULL(league_display_name,league_name) AS league_name',FALSE)
		 	->from(LEAGUE." AS L")
		 	->join(SEASON.' S','S.league_id = L.league_id','INNER')
			->where('L.active','1')
			->where('L.sports_id',$post_data['sports_id'])
			//->where('S.is_published','1')
			->where('S.season_scheduled_date > ',$current_date)
			->group_by('L.league_id');

        $sql = $this->db_fantasy->get();
		$result = $sql->result_array();
		return $result;
	}

	/**
	 * Used for get league published fixture
	 * @param array $post_data
	 * @return array
	 */
	public function get_published_fixtures($post_data)
	{
		$current_date = format_date();
		$sports_id = $post_data['sports_id'];
		$league_id = $post_data['league_id'];
		$this->db_fantasy->select("S.season_id,S.season_game_uid,S.season_scheduled_date,IFNULL(S.tournament_name,'') as tournament_name,S.match_event,1 as is_tour_game",false)
			->from(SEASON." AS S")
			->where("S.league_id",$league_id)
			->where('S.season_scheduled_date > ',$current_date)
			//->where('S.is_published','1')
			->group_by("S.season_id")
			->order_by("S.season_scheduled_date","ASC");

		if(!in_array($sports_id,$this->tour_game_sports)){
			$this->db_fantasy->select("0 as is_tour_game,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag",false)
			->join(TEAM.' T1','T1.team_id = S.home_id','INNER')
			->join(TEAM.' T2','T2.team_id = S.away_id','INNER');
        }
		$result	= $this->db_fantasy->get()->result_array();
		return $result;
	}

	/**
    * Function used for get user match list
    * @param array $post_data
    * @return string
    */
    public function get_fixture_collection($season_ids){
        //get match and collection
        $this->db_fantasy->select("S.season_id,IFNULL(CM.collection_master_id,'0') as cm_id,S.season_scheduled_date", FALSE)
                    ->from(SEASON.' AS S')
                    ->join(COLLECTION_SEASON.' AS CS', 'CS.season_id = S.season_id','LEFT')
                    ->join(COLLECTION_MASTER.' AS CM', 'CM.collection_master_id = CS.collection_master_id AND CM.league_id=S.league_id AND CM.season_game_count = 1','LEFT')
                    ->where_in("S.season_id",$season_ids);
        $this->db_fantasy->group_by("S.season_id");
        $result = $this->db_fantasy->get()->result_array();
        return $result;
    }

	/**
    * Function used for save tournament data
    * @param array $post_data
    * @return array
    */
	public function save_tournament($post_data)
	{
		try{
			//Start Transaction
            $this->db_fantasy->trans_strict(TRUE);
            $this->db_fantasy->trans_start();

            $current_date = format_date();
            $match_list = $post_data['match_list'];
            $data_arr = array();
            $data_arr["sports_id"] = $post_data['sports_id'];
            $data_arr["league_id"] = $post_data['league_id'];
            $data_arr["name"] = $post_data['name'];
            $data_arr["image"] = isset($post_data['image']) ? $post_data['image'] : "";
            $data_arr["start_date"] = $post_data['start_date'];
            $data_arr["end_date"] = $post_data['end_date'];
            $data_arr["no_of_fixture"] = $post_data['no_of_fixture'];
            $data_arr["is_top_team"] = $post_data['is_top_team'];
            $data_arr["prize_detail"] = json_encode($post_data['prize_detail']);
            if(isset($post_data['banner_images']) && !empty($post_data['banner_images'])){
            	$data_arr['banner_images'] = json_encode($post_data['banner_images']);
            }
            $data_arr["match_count"] = count($match_list);
			$data_arr["is_tie_breaker"] = '1';
            $data_arr["added_date"] = $current_date;
            $data_arr["modified_date"] = $current_date;
			$this->db_fantasy->insert(TOURNAMENT,$data_arr);
			$tournament_id = $this->db_fantasy->insert_id();
			if($tournament_id){
				foreach($match_list as $row){
					$season_data = array();
					$season_data['tournament_id'] = $tournament_id;
					$season_data['season_id'] = $row['season_id'];
					$season_data['cm_id'] = $row['cm_id'];
					$season_data['season_scheduled_date'] = $row['season_scheduled_date'];
					$season_data['added_date'] = $current_date;
					$this->db_fantasy->insert(TOURNAMENT_SEASON,$season_data);
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
	                return $tournament_id;
	            }
			}else{
				return false;
			}
		}
		catch(Exception $e){
			$this->db_fantasy->trans_rollback();
	        return false;
      	}
		return false;
	}

	/**
	 * Used for get tournament list
	 * @param array $post_data
	 * @return array
	 */
	public function get_tournament_list($post_data)
	{
		$current_date = format_date();
		$sort_field	= 'start_date';
		$sort_order	= 'DESC';
		$pagination = get_pagination_data($post_data);
		$status = isset($post_data['status']) ? $post_data['status'] : "upcoming";
		$where = array("T.start_date > " => $current_date);
		if($status == "live"){
			$where = array("T.start_date <= " => $current_date,"T.status"=>"0");
		}else if($status == "completed"){
			$where = array("T.start_date <= " => $current_date,"T.status >= "=>"1");
		}else{
			$sort_order	= 'ASC';
		}

		$this->db_fantasy->select("T.tournament_id,T.league_id,T.name,IFNULL(T.image,'') as image,T.start_date,T.end_date,T.match_count,T.prize_detail,T.is_pin,T.status,T.no_of_fixture,T.is_top_team,IFNULL(L.league_display_name,L.league_name) AS league_name",false)
			->from(TOURNAMENT." AS T")
			->join(LEAGUE.' L','L.league_id = T.league_id','INNER')
			->where("L.sports_id",$post_data['sports_id'])
			->where($where)
			->group_by("T.tournament_id")
			->order_by($sort_field, $sort_order);

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db_fantasy->where("(T.name LIKE '%".$post_data['keyword']."%')");
		}
		if(isset($post_data['league_id']) && $post_data['league_id'] != "")
		{
			$this->db_fantasy->where("T.league_id",$post_data['league_id']);
		}
		if(isset($post_data['start_date']) && $post_data['start_date'] != "")
		{
			$this->db_fantasy->where("T.start_date >= ",$post_data['start_date']);
		}

		if(isset($post_data['end_date']) && $post_data['end_date'] != "")
		{
			$this->db_fantasy->where("T.start_date <= ",$post_data['end_date']);
		}

		$tempdb = clone $this->db_fantasy;
		$query = $this->db_fantasy->get();
		$total = $query->num_rows();

		$sql = $tempdb->limit($pagination['limit'],$pagination['offset'])->get();
		$result	= $sql->result_array();
		// echo $this->db_fantasy->last_query(); die;
		$result = isset($result) ? $result : array();
		return array('result' => $result,'total' => $total);
	}

	/**
	 * Used for get league published fixture
	 * @param array $post_data
	 * @return array
	 */
	public function get_tournament_fixtures($tournament_id)
	{
		$current_date = format_date();
		$this->db_fantasy->select("S.season_id,S.league_id,S.season_scheduled_date,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IF(IFNULL(TS.tournament_season_id,0) > 0,1,0) as is_added,IFNULL(S.tournament_name,'') as tournament_name,S.match_event,S.is_tour_game",false)
			->from(TOURNAMENT." AS T")
			->join(SEASON." AS S","S.league_id=T.league_id","INNER")
			->join(TEAM.' T1','T1.team_id = S.home_id','LEFT')
			->join(TEAM.' T2','T2.team_id = S.away_id','LEFT')
			->join(TOURNAMENT_SEASON.' TS','TS.tournament_id = T.tournament_id AND TS.season_id=S.season_id','LEFT')
			->where("T.tournament_id",$tournament_id)
			//->where('S.is_published','1')
			->group_by("S.season_id")
			->having("(is_added=1 OR S.season_scheduled_date > '".$current_date."')")
			->order_by("S.season_scheduled_date","ASC");

		$result	= $this->db_fantasy->get()->result_array();
		return $result;
	}

	/**
    * Function used for save tournament fixture data
    * @param array $post_data
    * @return array
    */
	public function save_tournament_fixture($post_data)
	{
		try{
			$new_match_list = array_column($post_data['match_list'],NULL,"season_id");
			$tournament_id = $post_data['tournament_id'];
			$match_list = $this->get_all_table_data("season_id,season_scheduled_date,cm_id",TOURNAMENT_SEASON,array("tournament_id"=>$tournament_id),array("season_scheduled_date"=>"ASC"));
			$match_list = array_column($match_list,NULL,"season_id");
			$match_list = array_diff_key($new_match_list,$match_list);
			
			//Start Transaction
            $this->db_fantasy->trans_strict(TRUE);
            $this->db_fantasy->trans_start();

            $current_date = format_date();
            foreach($match_list as $row){
				$season_data = array();
				$season_data['tournament_id'] = $tournament_id;
				$season_data['season_id'] = $row['season_id'];
				$season_data['cm_id'] = $row['cm_id'];
				$season_data['season_scheduled_date'] = $row['season_scheduled_date'];
				$season_data['added_date'] = $current_date;
				$this->db_fantasy->insert(TOURNAMENT_SEASON,$season_data);
			}

			$record_info = $this->get_tournament_end_date($tournament_id);
			
			if(!empty($record_info)){
				$this->db_fantasy->where("tournament_id",$tournament_id);
            	$this->db_fantasy->update(TOURNAMENT, $record_info);
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
                return $tournament_id;
            }
		}
		catch(Exception $e){
			$this->db->trans_rollback();
	        return false;
      	}
		return false;
	}

	/**
     * Used for get season detail by season id
     * @param int $season_id
     * @return array
     */
    public function get_fixture_season_detail($season_ids)
    {
        if(empty($season_ids)){
            return false;
        }

        $this->db_fantasy->select("S.season_id,S.season_scheduled_date,S.status,S.status_overview,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag",false)
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
     * Used for get season detail by season id for tour game sports
     * @param int $season_id
     * @return array
     */
    public function get_tour_game_sports_fixtures($season_ids)
    {
        if(empty($season_ids)){
            return false;
        }

        $this->db_fantasy->select("S.season_id,S.season_scheduled_date,S.status,S.status_overview,1 as is_tour_game,IFNULL(S.tournament_name,'') as tournament_name,S.match_event,IFNULL(L.image,'') as league_image",false)
            ->from(SEASON." AS S")
            ->join(LEAGUE.' L','L.league_id=S.league_id','INNER')
            ->where_in("S.season_id",$season_ids)
            ->group_by("S.season_id")
            ->order_by("S.season_scheduled_date","ASC");

        $sql = $this->db_fantasy->get();
        $result = $sql->result_array();
        return ($result) ? $result : array();
    }

	/**
    * Function used for get tournament details
    * @param int $tournament_id
    * @return array
    */
	public function get_tournament_detail($tournament_id)
	{
		$this->db_fantasy->select("T.tournament_id,T.sports_id,T.league_id,T.name,IFNULL(T.image,'') as image,T.start_date,T.end_date,T.match_count,T.is_tie_breaker,T.prize_detail,T.banner_images,T.is_pin,T.status,T.no_of_fixture,T.is_top_team,T.added_date,IFNULL(T.cancel_reason,'') as cancel_reason,IFNULL(L.league_display_name,L.league_name) AS league_name,GROUP_CONCAT(DISTINCT TS.season_id) as season_ids",false)
			->from(TOURNAMENT." AS T")
			->join(LEAGUE.' L','L.league_id = T.league_id','INNER')
			->join(TOURNAMENT_SEASON." AS TS", 'TS.tournament_id = T.tournament_id','INNER')
			->where('T.tournament_id',$tournament_id)
			->group_by('T.tournament_id');
		$sql = $this->db_fantasy->get();
		$result	= $sql->row_array();
		return $result;
	}

	/**
    * Function used for get tournament users
    * @param int $tournament_id
    * @return array
    */
	public function get_tournament_users($post_data)
	{
		$pagination = get_pagination_data($post_data);
		$this->db_fantasy->select("TH.history_id,TH.user_id,TH.total_score,IF(TH.rank_value=0,'-',TH.rank_value) as rank_value,TH.is_winner,IFNULL(TH.prize_data,'[]') as prize_data,'' as name,'' as user_name,'' as user_unique_id,'' as image",FALSE)
			->from(TOURNAMENT_HISTORY." AS TH")
			->where("TH.tournament_id", $post_data['tournament_id'])
			->order_by('TH.rank_value','ASC')
			->order_by('TH.total_score','DESC')
			->order_by('TH.history_id','ASC')
			->group_by('TH.user_id');

		$tmpdb = clone $this->db_fantasy;
		$total = $tmpdb->get()->num_rows();

		$result = $this->db_fantasy->limit($pagination['limit'],$pagination['offset'])->get()->result_array();
		return array('result'=>$result,'total'=>$total);
	}

	/**
    * Function used for get user match list
    * @param array $post_data
    * @return string
    */
    public function get_user_match_list($tournament_id,$user_id){

		$this->db_fantasy->select("COUNT(DISTINCT THT.lm_id) as team_count,SUM(THT.score) as total,THT.is_included,CM.collection_name as name,CM.season_scheduled_date", FALSE)
			->from(TOURNAMENT_HISTORY_TEAMS." AS THT")
			->join(COLLECTION_MASTER.' AS CM', 'CM.collection_master_id = THT.cm_id')
			->where('THT.tournament_id',$tournament_id)
            ->where('THT.user_id',$user_id)
            ->group_by('THT.cm_id');
        $result = $this->db_fantasy->get()->result_array();
        return $result;
    }

    /**
     * Used for get user detail by user id
     * @param int $user_id
     * @return array
     */
    public function get_user_detail_by_user_id($user_id)
    {
        $this->db->select("U.user_id,U.user_unique_id,IFNULL(TRIM(CONCAT(U.first_name,' ',U.last_name)),U.user_name) as name,IFNULL(U.image,'') as image,U.user_name",FALSE)
            ->from(USER." AS U");
        if(is_array($user_id)){
            $this->db->where_in("U.user_id",$user_id);
            $result = $this->db->get()->result_array();
        }else{
            $this->db->where("U.user_id",$user_id);
            $result = $this->db->get()->row_array();
        }
        return ($result) ? $result:array();
    }

    /**
    * Function used for get fixture tournament list
    * @param int $cm_id
    * @return array
    */
	public function get_fixture_tournament($cm_id)
	{
		$this->db_fantasy->select("T.tournament_id,T.name,TS.season_id,TS.contest_id",false)
			->from(TOURNAMENT_SEASON." AS TS")
			->join(TOURNAMENT.' T','T.tournament_id = TS.tournament_id','INNER')
			->where('TS.cm_id',$cm_id)
			->group_by('T.tournament_id');
		$sql = $this->db_fantasy->get();
		$result	= $sql->result_array();
		return $result;
	}

	/**
    * Function used for get tournament end date
    * @param int $trnt_id
    * @return array
    */
	public function get_tournament_end_date($trnt_id)
	{
		$this->db_fantasy->select("MIN(TS.season_scheduled_date) as start_date, (CASE WHEN T.end_date > MAX(TS.season_scheduled_date) THEN T.end_date ELSE MAX(TS.season_scheduled_date) END) as end_date,COUNT(DISTINCT TS.season_id) as match_count",false)
			->from(TOURNAMENT_SEASON." AS TS")
			->join(TOURNAMENT.' T','T.tournament_id = TS.tournament_id','INNER')
			->where('TS.tournament_id',$trnt_id);
		$sql = $this->db_fantasy->get();
		$result	= $sql->row_array();
		return $result;
	}

	public function update_tournament_cm_id($cm_id,$season_id){
		$this->db_fantasy->where("season_id",$season_id);
		$this->db_fantasy->where("cm_id","0");
    	$this->db_fantasy->update(TOURNAMENT_SEASON,array("cm_id"=>$cm_id));
    	return true;
	}
	
}