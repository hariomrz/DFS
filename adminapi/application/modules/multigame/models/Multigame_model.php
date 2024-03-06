<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Multigame_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
	}

	public function get_fixture_list($post_data)
	{
		$current_date = format_date();
		$pagination = get_pagination_data($post_data);
		$sports_id = $post_data['sports_id'];
		$status = $post_data['status'];
		$league_id = isset($post_data['league_id']) ? $post_data['league_id'] : "";
		
		$this->db_fantasy->select("CM.collection_master_id,CM.league_id,CM.collection_name,CM.season_game_count,CM.status,CS.season_scheduled_date,IFNULL(L.league_display_name,L.league_name) AS league_name,GROUP_CONCAT(DISTINCT CS.season_id) as season_ids",false)
			->from(COLLECTION_MASTER." AS CM")
			->join(COLLECTION_SEASON.' CS','CS.collection_master_id	 = CM.collection_master_id	','INNER')
			->join(LEAGUE.' L','L.league_id = CM.league_id','INNER')
			->where('CM.season_game_count > ',1)
			->where('L.sports_id',$sports_id)
			->group_by("CS.collection_master_id");
		
		if($status == "completed")
		{
			$this->db_fantasy->where("CM.status",1);
			$this->db_fantasy->order_by("CM.season_scheduled_date","DESC");
		}else if($status=='live'){
			$this->db_fantasy->where("CM.status",0);
			$this->db_fantasy->where("CM.season_scheduled_date <= ",$current_date);
			$this->db_fantasy->order_by("CM.season_scheduled_date","DESC");
		}else{
			$this->db_fantasy->where("CM.status",0);
			$this->db_fantasy->where("CM.season_scheduled_date > ",$current_date);
			$this->db_fantasy->order_by("CM.season_scheduled_date","ASC");
		}

		if($league_id != "")
		{
			$this->db_fantasy->where("CM.league_id",$league_id);
		}

		$tempdb_fantasy = clone $this->db_fantasy;
		$query = $this->db_fantasy->get();
		$total = $query->num_rows();

		$sql = $tempdb_fantasy->limit($pagination['limit'],$pagination['offset'])->get();
		$result	= $sql->result_array();
		$result = isset($result) ? $result : array();
		return array('result' => $result,'total' => $total);
	}

	/**
	 * Used for get league list by sports id
	 * @param int $sports_id
	 * @return array
	 */
	public function get_leagues_list($sports_id)
	{
		$current_date = format_date();
		$this->db_fantasy->select('L.league_id,L.league_uid,L.league_abbr,IFNULL(league_display_name,league_name) AS league_name,COUNT(season_id) as fixture_count',FALSE)
		 	->from(LEAGUE." AS L")
		 	->join(SEASON." AS S", "L.league_id = S.league_id", 'INNER')
			->where('L.active','1')
			->where('L.sports_id',$sports_id)
			->where('S.is_published','1')
			->where('S.season_scheduled_date > ',$current_date)
			->group_by('L.league_id')
			->having("fixture_count >= 2");

        $sql = $this->db_fantasy->get();
		$result = $sql->result_array();
		return $result;
	}

	/**
	 * Used for get league wise published fixture list
	 * @param int $league_id
	 * @return array
	 */
	public function get_published_fixtures($league_id)
	{	
		$current_date = format_date();
		$this->db_fantasy->select("S.league_id,S.season_id,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,S.season_scheduled_date",false)
			->from(SEASON." AS S")
			->join(LEAGUE.' L','L.league_id = S.league_id',"INNER")
			->join(TEAM.' as T1', 'T1.team_id = S.home_id',"INNER")
			->join(TEAM.' as T2', 'T2.team_id = S.away_id',"INNER")
			->where("S.is_published","1")
			->where("S.league_id",$league_id)
			->where("S.season_scheduled_date > ",$current_date)
			->order_by("S.season_scheduled_date","ASC")
			->group_by('S.season_id');

		$result = $this->db_fantasy->get()->result_array();
		return $result;
	}

	/**
	 * function used to check collection name exist or not
	 * @param string $collection_name
	 * @return array
	 */
	public function check_collection($collection_name)
	{	
		$this->db_fantasy->select('collection_name') 
				->from(COLLECTION_MASTER . " AS C")
				->where("LOWER(collection_name)", strtolower($collection_name));
		$sql = $this->db_fantasy->get();
		$result = $sql->row_array();
		return $result;	
	}

	/**
	 * function used to check collection name exist or not
	 * @param string $collection_name
	 * @return array
	 */
	public function get_season_detail_by_ids($season_ids)
	{	
		$this->db_fantasy->select('season_id,season_scheduled_date') 
				->from(SEASON . " AS S")
				->where_in("S.season_id",$season_ids)
				->order_by("S.season_scheduled_date","ASC");
		$sql = $this->db_fantasy->get();
		$result = $sql->result_array();
		return $result;	
	}

	/**
	 * Used for check collection exist
	 * @param array $season_ids
	 * @return array
	 */
	public function check_collection_exist($season_ids)
	{	
		$count = count($season_ids);
	 	$this->db_fantasy->select("CM.collection_master_id,CM.season_game_count,count(CS.collection_master_id) as total",false)
			->from(COLLECTION_SEASON." AS CS")
			->join(COLLECTION_MASTER.' AS CM','CM.collection_master_id = CS.collection_master_id','INNER')
			->where_in("CS.season_id",$season_ids)
			->where("CM.season_game_count",$count)
			->group_by("CM.collection_master_id")
			->having("total = season_game_count");

		$result	= $this->db_fantasy->get()->row_array();
		return $result;
	}

	/**
    * Function used for save collection data
    * @param array $post_data
    * @return array
    */
	public function save_collection_data($post_data)
	{
		try{
			//Start Transaction
            $this->db_fantasy->trans_strict(TRUE);
            $this->db_fantasy->trans_start();

            $current_date = format_date();
            $season_ids = $post_data['season_ids'];
            $scheduled_dates = array_values($season_ids);
            $season_scheduled_date = min($scheduled_dates);
            $collection = array();
            $collection["league_id"] = $post_data['league_id'];
            $collection["collection_name"] = trim($post_data['collection_name']);
            $collection["season_scheduled_date"] = $season_scheduled_date;
            $collection["season_game_count"] = count($season_ids);
			$collection["deadline_time"] = '0';
            $collection["added_date"] = $current_date;
            $collection["modified_date"] = $current_date;
			$this->db_fantasy->insert(COLLECTION_MASTER,$collection);
			$collection_master_id = $this->db_fantasy->insert_id();
			if($collection_master_id){
				foreach($season_ids as $season_id=>$scheduled_date){
					$collection_season_data = array();
					$collection_season_data['collection_master_id'] = $collection_master_id;
					$collection_season_data['season_id'] = $season_id;
					$collection_season_data['season_scheduled_date'] = $scheduled_date;
					$collection_season_data['added_date'] = $current_date;
					$collection_season_data['modified_date'] = $current_date;
					$this->db_fantasy->insert(COLLECTION_SEASON,$collection_season_data);
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
	                return $collection_master_id;
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
	 * Used for get league list by sports id
	 * @param int $sports_id
	 * @return array
	 */
	public function get_fixture_league_list($sports_id)
	{
		$current_date = format_date();
		$this->db_fantasy->select('L.league_id,L.league_uid,L.league_abbr,IFNULL(league_display_name,league_name) AS league_name',FALSE)
		 	->from(LEAGUE." AS L")
		 	->join(COLLECTION_MASTER.' AS CM','CM.league_id = L.league_id','INNER')
			->where('L.active','1')
			->where('CM.season_game_count > ','1')
			->where('L.sports_id',$sports_id)
			->group_by('L.league_id')
			->order_by('L.league_schedule_date','DESC');
        $sql = $this->db_fantasy->get();
		$result = $sql->result_array();
		return $result;
	}

	/**
	* Function used for get league fixture list for contest dashboard
	* @param int $league_id
	* @return array
	*/
	public function get_league_fixture_list($league_id)
	{
		$this->db_fantasy->select('CM.collection_master_id,CM.collection_name,CM.season_scheduled_date')
			->from(COLLECTION_MASTER." AS CM")
			->where('CM.season_game_count > ',1)
			->where('CM.league_id', $league_id);
		$result = $this->db_fantasy->get()->result_array();
		return $result;
	}

	/**
	 * Function used for get contest list on contest dashboard page
	 * @param array $post_data
	 * @return [type] [description]
	 */
	public function get_contest_list($post_data)
	{
		$current_date = format_date();
		$limit = RECORD_LIMIT;
		$page = 1;
		$status = isset($post_data['status']) ? $post_data['status'] : "";
		$sort_field = 'C.contest_id';
		$sort_order = 'DESC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','entry_fee','minimum_size','size','total_user_joined','prize_pool','max_bonus_allowed','spot_left','current_earning','potential_earning')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		if(!empty($post_data['pageSize']) && $post_data['pageSize'])
		{
			$limit = $post_data['pageSize'];
		}

		if(!empty($post_data['currentPage']) && $post_data['currentPage'])
		{
			$page = $post_data['currentPage'];
		}
		$offset	= $limit * ($page-1);

		$this->db_fantasy->select('C.contest_id,C.contest_unique_id,C.collection_master_id,C.league_id,C.group_id,C.season_scheduled_date,C.minimum_size,C.size,C.contest_name,C.currency_type,C.entry_fee,C.prize_pool,C.total_user_joined,C.multiple_lineup,C.prize_type,C.guaranteed_prize,C.is_auto_recurring,C.is_pin_contest,GROUP_CONCAT(DISTINCT CS.season_id) as season_ids,C.max_bonus_allowed,(C.size-C.total_user_joined) as spot_left,(C.entry_fee*C.total_user_joined) as current_earning,((C.size-C.total_user_joined)*C.entry_fee) as potential_earning,IFNULL(C.contest_title,"") as contest_title,C.status,C.prize_distibution_detail', FALSE)
			->from(CONTEST." AS C")
			->join(COLLECTION_MASTER." AS CM", 'CM.collection_master_id = C.collection_master_id','INNER')
			->join(COLLECTION_SEASON." AS CS", 'CS.collection_master_id = CM.collection_master_id','INNER')
			->where('C.sports_id',$post_data['sports_id'])
			->where('CM.season_game_count > ',1)
			->group_by('C.contest_id');

		if(isset($post_data['league_id']) && $post_data['league_id'] != "")
		{
			$this->db_fantasy->where('C.league_id', $post_data['league_id']);
		}

		if(isset($post_data['collection_master_id']) && $post_data['collection_master_id'] != "")
		{
			$this->db_fantasy->where('C.collection_master_id', $post_data['collection_master_id']);
		}
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db_fantasy->like('C.contest_name', $post_data['keyword']);
		}

		if(isset($post_data['group_id']) && $post_data['group_id'] != "")
		{
			$this->db_fantasy->where('C.group_id', $post_data['group_id']);
		}

		switch ($status)
		{
			case 'current_game':
				$this->db_fantasy->where('C.status','0');
				$this->db_fantasy->where("C.season_scheduled_date <= ",$current_date);
				break;
			case 'completed_game':
				$this->db_fantasy->where('C.status >','1');
				break;
			case 'cancelled_game':
				$this->db_fantasy->where('C.status','1');
				break;
			case 'upcoming_game':
				$this->db_fantasy->where('C.status','0');
				$this->db_fantasy->where("C.season_scheduled_date > ",$current_date);
				break;
			default:
				break;
		}
		$total = 0;
		if($page == 1){
			$tempdb = clone $this->db_fantasy;
	        $temp_q = $tempdb->get();
			$total = $temp_q->num_rows();
		}

		$this->db_fantasy->order_by($sort_field, $sort_order);
		$result = $this->db_fantasy->limit($limit,$offset)->get()->result_array();
		//echo $this->db_fantasy->last_query();die('dfd');
		return array('result' => $result, 'total' => $total);
	}
}