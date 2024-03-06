<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Contest_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		
	}

	/**
	 * [get_fixture_contest description]
	 * Summary :- 
	 * @return [type] [description]
	 */
	public function get_fixture_contest($post_data)
	{ 
		$sort_field = 'C.group_id';
		$sort_order = 'ASC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','minimum_size','size','entry_fee','prize_pool','is_auto_recurring','guaranteed_prize','max_bonus_allowed','added_date')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		
		$this->db->select('C.contest_id,C.status,C.contest_unique_id,C.contest_template_id,C.collection_id,C.league_id,C.group_id,C.season_scheduled_date,C.minimum_size,C.size,C.contest_name,C.currency_type,C.entry_fee,C.prize_pool,C.total_user_joined,C.prize_distibution_detail,C.multiple_lineup,C.prize_type, C.guaranteed_prize,C.is_auto_recurring,C.is_pin_contest,MG.group_name,C.total_system_user,C.sponsor_name,C.sponsor_logo,C.sponsor_link,C.video_link,IFNULL(C.contest_title,"") as contest_title,C.max_bonus_allowed', FALSE)
			->from(CONTEST." AS C")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = C.group_id','INNER')
			->join(COLLECTION." AS CM", 'CM.collection_id = C.collection_id','INNER')
			->where('C.league_id',$post_data['league_id']);
			if(isset($post_data['collection_id']) && $post_data['collection_id']!="")
			{
				$this->db->where('C.collection_id',$post_data['collection_id']);
			}			
			// ->where('CM.season_game_count',1)
			$this->db->group_by('C.contest_id');
			$this->db->where('CM.season_game_uid',$post_data['season_game_uid']);

		$sql = $this->db->order_by($sort_field, $sort_order)->get();
		$result	= $sql->result_array();
		// print_r($this->db->last_query());die();
		return array('result' => $result, 'total' => count($result));
	}

	/**
	*@method delete_contest
	*@uses function to delete contest
	****/
	public function delete_contest($data_arr)
	{
		$this->db->where("contest_id",$data_arr['contest_id']);
		$this->db->where("total_user_joined","0");
		$this->db->delete(CONTEST);
		$is_deleted = $this->db->affected_rows();
		return $is_deleted;
	}

	/**
	 * [get_contest_list description]
	 * Summary :- 
	 * @return [type] [description]
	 */
	public function get_contest_list($post_data)
	{
		$current_time = format_date();
		$limit = 2;
		$page = 0;
		$status = isset($post_data['status'])?$post_data['status']:"";
		$sort_field = 'C.contest_id';
		$sort_order = 'DESC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_id','contest_name','entry_fee','minimum_size','size','total_user_joined','prize_pool','max_bonus_allowed','spot_left','system_teams','real_teams','current_earning','potential_earning')))
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

		$this->db->select('C.status,C.contest_id,C.contest_unique_id,C.contest_template_id,C.collection_id,C.league_id,C.group_id,C.season_scheduled_date,C.minimum_size,C.size,C.contest_name,C.currency_type,C.entry_fee,C.prize_pool,C.total_user_joined,C.multiple_lineup,C.prize_type, C.guaranteed_prize,C.is_auto_recurring,C.is_pin_contest,CM.season_game_uid,C.prize_distibution_detail,C.consolation_prize,C.max_bonus_allowed,(C.size-C.total_user_joined) as spot_left,
		,(C.entry_fee*total_system_user) as current_earning,(C.size-C.total_user_joined)*C.entry_fee as potential_earning,IFNULL(C.contest_title,"") as contest_title,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs', FALSE)
			->from(CONTEST." AS C")
			->join(COLLECTION." AS CM", 'CM.collection_id = C.collection_id','INNER')			
			->where('C.sports_id',$post_data['sports_id'])
			->group_by('C.contest_id');

		if(isset($post_data['season_game_uid']) && $post_data['season_game_uid'] != "")
		{
			$this->db->where('CM.season_game_uid', $post_data['season_game_uid']);
		}
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('C.contest_name', $post_data['keyword']);
		}

		if(isset($post_data['league_id']) && $post_data['league_id'] != "")
		{
			$this->db->where('C.league_id', $post_data['league_id']);
		}
		
		if(isset($post_data['group_id']) && $post_data['group_id'] != "")
		{
			$this->db->where('C.group_id', $post_data['group_id']);
		}
		if(isset($post_data['over']) && $post_data['over'] != "")
		{
			$this->db->like('CM.inn_over', '_'.$post_data['over']);
		}
		switch ($status)
		{
			case 'current_game':
				$this->db->where('C.status','0');
				$this->db->where('CM.status','1');
				break;
			case 'completed_game':
				$this->db->where('C.status >','1');
				break;
			case 'cancelled_game':
				$this->db->where('C.status','1');
				break;
			case 'upcoming_game':
				$this->db->where('C.status','0');
				$this->db->where('CM.status','0');
				// $this->db->where("C.season_scheduled_date > DATE_ADD('{$current_time}', INTERVAL CM.deadline_time MINUTE)");
				break;
			default:
				break;
		}
		$tempdb = clone $this->db;
        $temp_q = $tempdb->get();

		
		$total = $temp_q->num_rows();
		
		$this->db->order_by($sort_field, $sort_order);
		$result = $this->db->limit($limit,$offset)->get()->result_array();
		//echo $this->db->last_query();die('dfd');
		return array('result' => $result, 'total' => $total);
	}
	/**
	 * [save_contest description]
	 * @MethodName save_contest
	 * @Summary This function used to create new contest
	 * @param      array  data array
	 * @return     int
	 */
	public function save_contest($data)
	{	
		$collection							= $data['collection'];
		$contest_data						= $data['contest'];
		$season_games						= $data['season_games'];
		$season_scheduled_date				= $data['season_scheduled_date'];
		$collection['season_game_count']	= count($season_games);
		$collection['added_date']			= format_date();
		$collection['modified_date']		= format_date();

		$this->db->trans_start();
		// $contest_data['collection_id'] = $collection_master_id;

		$this->db->insert(CONTEST,$contest_data);

		$contest_id = $this->db->insert_id();
		$this->db->trans_complete();
		$this->db->trans_strict(FALSE);

		if ($this->db->trans_status() === FALSE)
		{
		    $this->db->trans_rollback();
			return false;
		}
		else
		{
			$this->db->trans_commit();
			$league_id = isset($contest_data['league_id']) ? $contest_data['league_id'] : "";
			//duplicate collection
			$season_game_uids = implode(",", $season_games);
			return $contest_id;
		}
	}

	/**
	 * function to get collection master id for dfs scheduled push
	 * @param season_game_uid
	 */
	public function get_cmid($season_game_uid,$league_id) {

		$result = $this->db->select("CS.collection_id,S.home,S.away")
		->from(COLLECTION." as CS")
		->join(SEASON .' S',"CS.season_game_uid = S.season_game_uid",'INNER')
		->where("CS.season_game_uid",$season_game_uid)
		->where("S.league_id", $league_id)
		->get()->row_array();
		return $result;
    }

	public function get_collection(){
		$post_data = $this->input->post();
		$this->db->select('collection_id,league_id,season_game_uid,CONVERT(SUBSTRING(inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(inn_over,3), SIGNED INTEGER) as overs,collection_name,season_scheduled_date,status')
				->from(COLLECTION)
				->where(array('collection_id'=>$post_data['collection_id'],'league_id'=>$post_data['league_id']));
		return $this->db->get()->row_array();
	}

	/**
	*@method mark_pin_contest
	*@uses this function used for mark contest as pin from admin panel
	**/
	public function mark_pin_contest($post_data)
	{
		if(empty($post_data)){
			return false;
		}

		$is_pin_contest = 1;
		if(isset($post_data['is_pin_contest'])){
			$is_pin_contest = $post_data['is_pin_contest'];
		}

		//update status in database
		$this->db->where('contest_id',$post_data['contest_id']);
		$this->db->set('is_pin_contest', $is_pin_contest);
		$this->db->update(CONTEST);

		return true;
	}

	public function get_game_detail($game_unique_id)
	{
		$sql = $this->db->select('G.max_bonus_allowed,G.prize_distibution_detail,G.user_id,G.collection_id,G.sports_id,G.season_scheduled_date,G.contest_id, G.contest_unique_id, G.contest_name, G.league_id, DATE_FORMAT(G.season_scheduled_date,"'.MYSQL_DATE_TIME_FORMAT.'") as scheduled_date, G.size, G.entry_fee,G.currency_type,G.prize_pool,G.status,DATE_FORMAT(G.added_date,"'.MYSQL_DATE_TIME_FORMAT.'") as added_date,G.contest_description,G.site_rake, G.total_user_joined,G.guaranteed_prize,G.guaranteed_prize as prize_pool_type,G.is_pin_contest,G.prize_type,G.is_custom_prize_pool,G.multiple_lineup,IFNULL(G.contest_title,"") as contest_title,G.is_tie_breaker,CM.collection_name,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,') 
						->from(CONTEST . " AS G")
						->join(COLLECTION." AS CM","CM.collection_id = G.collection_id","LEFT")
						->where('G.contest_unique_id', $game_unique_id)
						->get();
                // echo $this->db->last_query();die;
		$result = $sql->row_array();
		return $result;
	}

	public function get_sport_detail_by_id($con = array())
	{
		$data = $this->db->select('sports_id,sports_name')
				->order_by('order', 'ASC')
				->where('active', '1')
				->where($con)
				->get(MASTER_SPORTS)
				->row_array();
		return $data;
	}

	public function getMatchDetailByCollection_id($collection_id)
	{
		$this->db->select("season_game_uid")
		->from(COLLECTION)
		->where('collection_id', $collection_id);
		$result = $this->db->get()->result_array();
		 $season_ids = array_column($result, 'season_game_uid');
		  // echo '<pre>'; print_r($season_ids); die;

		return $season_ids;
	}

	public function get_lineup_by_game()
	{
		$limit      = 10;
		$page       = 0;
		$post_data = $this->input->post();
		$sort_field = "LMC.total_score";
		$sort_order = "DESC";
		
		if(isset($post_data['sort_field']))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']))
		{
			$sort_order = $post_data['sort_order'];
		}

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}
		
		$offset	= $limit * $page;
		$this->db->select("LM.user_team_id,LM.user_name,LM.team_name,LM.collection_id,LM.user_id,LMC.total_score,LMC.game_rank,LMC.user_contest_id,LMC.prize_data,LMC.is_winner,CONVERT(SUBSTRING(inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(inn_over,3), SIGNED INTEGER) as overs",FALSE)
			->from(USER_CONTEST." AS LMC")
			->join(USER_TEAM.' AS LM','LMC.user_team_id = LM.user_team_id','INNER')
			->join(COLLECTION.' AS C','C.collection_id=LM.collection_id','INNER')
			->where("LMC.contest_id", $post_data['contest_id']);
		if(isset($post_data['user_id']) && $post_data['user_id'] != ""){
			$this->db->where("LM.user_id", $post_data['user_id']);
		}
		$tempdb = clone $this->db;
		//$total = $this->get_total('LM.lineup_master_id');
		$query = $this->db->get();
		$total = $query->num_rows();
		
		if(isset($post_data['user_id']) && $post_data['user_id'] != ""){
			$sql = $tempdb->limit(ALLOWED_USER_TEAM)
						->order_by($sort_field,$sort_order)
						->get();
		}else{
			$sql = $tempdb->limit($limit, $offset)
						->order_by($sort_field,$sort_order)
						->get();
		}
		//echo $tempdb->last_query();
		$result	= $sql->result_array();

		$result = ($result) ? $result : array();
		return array('result'=>$result, 'total'=>$total);
	}

	public function get_constest_details($contest_unique_id){
		$responce = $this->db->select("C.contest_unique_id,C.contest_name,C.entry_fee,C.season_scheduled_date")
			->from(CONTEST. " C")
			->where_in('C.contest_unique_id',$contest_unique_id)
			->get()->result_array();
		return $responce;
	}



}
