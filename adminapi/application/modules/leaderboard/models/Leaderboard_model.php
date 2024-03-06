<?php

class Leaderboard_model extends MY_Model {

	public function __construct() {
		parent::__construct();
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
	}

	public function get_leaderboard_type() {
		$this->db->select("category_id,name")
                ->from(LEADERBOARD_CATEGORY)
                ->where("status","1")
                ->order_by("display_order","ASC");
        $result = $this->db->get()->result_array();
        return $result;
    }

	

	public function save_prizes($post_data)
	{
		$this->db->insert(LEADERBOARD_PRIZE,$post_data);
      	return $this->db->insert_id();
	}

	public function update_prizes($update_data,$condition)
	{
		$this->db->where($condition);
		$this->db->update(LEADERBOARD_PRIZE,$update_data);
		return true;
	}

	/**
	 * this function to get list of all active leaderboards
	 * @param from_date,to_date
	 * 
	 */
	public function get_leaderboard_list($post_data)
	{
		$sort_field	= 'LP.prize_id';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;
		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}
		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}
		$offset	= $limit * $page;
		
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('LP.prize_id','prize_detail','prize_date','is_win_notify','start_date','end_date')))
		{
			$sort_field = $post_data['sort_field'];
		}
		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$result = $this->db->select("LP.*,LC.name as category_name,L.leaderboard_id,IFNULL(L.status,0) as l_status",FALSE)
		->from(LEADERBOARD_PRIZE." AS LP")
		->join(LEADERBOARD_CATEGORY." LC", "LC.category_id = LP.category_id and LC.status=1", "INNER")
		->join(LEADERBOARD.' L',"L.prize_id = LP.prize_id","LEFT")
		->order_by($sort_field,$sort_order)
		->group_by("LP.prize_id");

		if(isset($post_data['name']) && $post_data['name']!='')
		{
			$this->db->like('LP.name',$post_data['name']);
		}

		$tempdb = clone $this->db;
		$total = $tempdb->get()->num_rows();

		$result = $this->db->limit($limit,$offset)->get()->result_array();
		// echo $this->db->last_query();exit;
		return ["result"=>$result,"total"=>$total];
	}

	public function change_leaderboard_status($post)
	{
		$result = $this->db->update(LEADERBOARD_PRIZE,["status"=>$post['status']],["prize_id"=>$post['prize_id']]);
		$row_afected = $this->db->affected_rows();
		if($row_afected)
		{
			return true;
		}
		return false;
	}

	/**
	 * get leaderboard prize detail for leaderboard detail page
	 */
	public function get_leaderboard_prize_details($prize_id)
	{
		$prize_detail = $this->db->select("LP.category_id,LC.name AS leaderboard_type,LP.type,LP.status,LP.allow_prize,LP.name,LP.prize_detail AS prize_data_master")
		->from(LEADERBOARD_PRIZE.' LP')
		->join(LEADERBOARD_CATEGORY.' LC',"LP.category_id = LC.category_id",'INNER')
		->where(["LP.prize_id"=>$prize_id])
		->get()->row_array();

		$leaderboards = $this->db->select("L.leaderboard_id,L.name,L.start_date,L.end_date,L.status,L.prize_date,L.status,L.is_win_notify,IF(L.status=3,L.prize_detail,LP.prize_detail) as prize_detail")
		->from(LEADERBOARD.' L')
		->join(LEADERBOARD_PRIZE.' LP',"L.prize_id = LP.prize_id","LEFT")
		->where("L.prize_id",$prize_id)
		->order_by("L.prize_date", "DESC")
		->limit(3)
		->get()->result_array();
		$prize_detail['leaderboard'] = $leaderboards;
		return $prize_detail;
	}

	/**
     * to get record of users participated and goes under ranking.
     */
	public function get_leaderboard_user_list($post_data)
	{
		$sort_field	= 'LH.history_id';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;
		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}
		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}
		$offset	= $limit * $page;
		
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('U.user_name','LH.history_id','LH.total_value','LH.rank_value')))
		{
			$sort_field = $post_data['sort_field'];
		}
		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$result = $this->db->select("LH.rank_value,LH.user_id,LH.total_value,LH.is_winner,LH.prize_data,U.user_name,U.image,U.user_unique_id")
		->from(LEADERBOARD_HISTORY.' LH')
		->join(USER.' U',"U.user_id=LH.user_id","LEFT")
		->where(["LH.leaderboard_id"=>$post_data['leaderboard_id']])
		->order_by('LH.rank_value','ASC')
		->group_by('LH.user_id');

		$tmpdb = clone $this->db;
		$total = $tmpdb->get()->num_rows();

		$result = $this->db->limit($limit,$offset)->get()->result_array();
		return ['result'=>$result,'total'=>$total];

	}

	/**
     * method to get all live and upcomming leagues
     */
	public function get_sport_leagues($post_data)
	{ 
		$Current_Date = format_date('today', 'Y-m-d');
		$this->db_fantasy->select('IFNULL(league_display_name,league_name) AS league_name, IFNULL(league_display_name,league_name) AS league_abbr,season_scheduled_date,L.league_id,COUNT(season_id) as total_count,is_published')
						 ->from(LEAGUE." AS L")
						 ->join(SEASON . " AS S", "L.league_id = S.league_id", 'left')
						 ->group_by('L.league_id');
		$this->db_fantasy->where('L.league_last_date > ', $Current_Date);
		if(isset($post_data['sports_id']))
		{
			$this->db_fantasy->where('L.sports_id', $post_data['sports_id']);
		}
						 
		$sql = $this->db_fantasy->order_by('league_schedule_date','ASC');
        $sql = $this->db_fantasy->get();
		$result = $sql->result_array();
		return array('result'=>$result);
	}

	/**
     * method to get detail of a single leaderboard in case of edit
     */
	public function get_prize_detail($prize_id)
	{
		$prizes = $this->db->select("prize_id,category_id,type,reference_id,name,prize_detail,allow_prize,status,custom_data")
		->from(LEADERBOARD_PRIZE)
		->where(["prize_id"=>$prize_id])
		->get()->row_array();
		if(!$prizes) return array();
		$prizes['prize_detail'] = json_decode($prizes['prize_detail'],true);
		return $prizes;
	}

	/**
    * Function used for get league leaderboard info
    * @param int $prize_id
    * @return array
    */
	public function get_league_leaderboard_details($prize_id)
	{
		$result = $this->db->select("LP.prize_id,LP.category_id,LP.type,LP.reference_id,LP.name,LP.is_complete,LP.status,LP.prize_detail,L.leaderboard_id,L.status,IFNULL(L.end_date,'') as end_date")
				->from(LEADERBOARD_PRIZE.' LP')
				->join(LEADERBOARD.' L',"L.prize_id = LP.prize_id",'LEFT')
				->where("LP.type","4")
				->where("LP.prize_id",$prize_id)
				->get()->row_array();
		return $result;
	}

	/**
    * Function used for update leaderboard complete status
    * @param int $prize_id
    * @return boolean
    */
	public function mark_complete_leaderboard($prize_id)
	{
		$this->db->update(LEADERBOARD_PRIZE,array("is_complete"=>1),array("prize_id"=>$prize_id,"type"=>"4"));
		$row_afected = $this->db->affected_rows();
		if($row_afected)
		{
			return true;
		}
		return false;
	}

	/**
    * Function used for update leaderboard complete status
    * @param int $prize_id
    * @return boolean
    */
	public function mark_cancel_leaderboard($leaderboard_id,$prize_id)
	{
		$this->db->update(LEADERBOARD,array("status"=>1),array("leaderboard_id"=>$leaderboard_id));
		$row_afected = $this->db->affected_rows();
		if($row_afected)
		{
			$this->db->update(LEADERBOARD_PRIZE,array("is_complete"=>0,"status"=>"0"),array("prize_id"=>$prize_id,"type"=>"4"));
			return true;
		}
		return false;
	}

	/**
	* Function used for update leaderboard status
	* @param int $league_id
	* @param int $status
	* @return boolean
	*/
	public function update_league_data($league_id,$status=1)
	{ 
		$this->db_fantasy->update(LEAGUE,array("ldb"=>$status),array("league_id"=>$league_id,"ldb"=>"0"));
		return true;
	}

}