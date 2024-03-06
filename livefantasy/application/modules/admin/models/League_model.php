<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class League_model extends MY_Model{

  	function __construct()
  	{
	  	parent::__construct();
	}

	/**
	 * Used for get league list by sports id
	 * @param int $sports_id
	 * @return array
	 */
	public function get_sport_league_list($sports_id)
	{
		$current_date = format_date();
		$this->db->select('L.league_id,IFNULL(league_display_name,league_name) AS league_name, IFNULL(league_display_name,league_name) AS league_abbr,COUNT(season_id) as total_count,season_scheduled_date,is_published')
		 	->from(LEAGUE." AS L")
			->join(SEASON." AS S", "L.league_id = S.league_id", 'LEFT')
			->where('L.active','1')
			->where('L.sports_id', $sports_id)
			->where('L.league_last_date >= ', $current_date)
			->group_by('L.league_id');

        $sql = $this->db->get();
		$result = $sql->result_array();
		return $result;
	}

	/**
	 * [description]
	 * @MethodName get_sport_leagues
	 * @Summary This function used for get all league for selected sport
	 */
	public function get_sport_leagues()
	{ 
		$sort_field	= 'league_schedule_date';
		$sort_order	= 'DESC';
                
		$Current_DateTime = format_date();
		$Current_Date = format_date('today', 'Y-m-d');
		
		$post_data = $this->input->post();

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('league_abbr','league_uid','L.active','league_schedule_date', 'league_last_date','max_player_per_team','L.is_promote')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$this->db->select('IFNULL(league_display_name,league_name) AS league_name, IFNULL(league_display_name, league_name) AS league_abbr,season_scheduled_date,L.league_id,COUNT(season_id) as total_count, is_published,format,L.league_id')
			->from(LEAGUE." AS L")
			->join(SEASON . " AS S", "L.league_id = S.league_id", 'left')
			->group_by('L.league_id');

		if(isset($post_data['for_collection']))
        {				
			$this->db->where('S.season_scheduled_date >',$Current_DateTime);
			$this->db->where('S.is_published', '1');
			$this->db->having("total_count >=2");
		}
				

        if(!empty($post_data['sports_id'])&&!is_array($post_data['sports_id']))
        {
            $this->db->where('L.sports_id', $post_data['sports_id']);
        }
        elseif(!empty($post_data['sports_id'])&&is_array($post_data['sports_id']))
        {
            $this->db->where_in('L.sports_id', $post_data['sports_id']);
        }

        if(isset($post_data['active']))
        {
            $this->db->where('L.active', $post_data['active']);
        }
                
        if(isset($post_data['league_status']))
        {
            if($post_data['league_status'] == 0 && $post_data['league_status'] != '') // Upcoming
            {
                $this->db->where('L.league_schedule_date > ', $Current_DateTime);
            }
            if($post_data['league_status'] == 1) // Live
            {
                $this->db->where('L.league_schedule_date < ', $Current_DateTime);
                $this->db->where('L.league_last_date > ', $Current_Date);
            }
            if($post_data['league_status'] == 2) // Completed
            {
                $this->db->where('L.league_last_date < ', $Current_Date);
			}
			if($post_data['league_status'] == 3) // Live and Upcomming leagues used for fantasy leaderboard
            {
                $this->db->where('L.league_last_date > ', $Current_Date);
            }
        }
		$sql = $this->db->order_by($sort_field, $sort_order);

		$tempdb = clone $this->db;
		$query  = $this->db->get();
		$total  = $query->num_rows();

        if(isset($limit) && isset($page))
        {
            $offset	= $limit * $page;
            
			$tempdb->limit($limit,$offset); 
			
        }
        $sql = $tempdb->get();
		 
		$result = $sql->result_array();
		return array('result'=>$result,'total'=>$total);
	}

	public function get_over_format($league_id){
		$this->db->select('format')
				->from(SEASON)
				->where('league_id',$league_id);
		$over_formate = $this->db->get()->row_array();	
		return $over_formate;
	}

	public function get_league_detail_by_id($league_id)
	{
		$league_detail =    $this->db->select("league_id,league_uid,league_name,league_abbr,sports_id")
			 ->from(LEAGUE)
			 ->where("league_id", $league_id)
			 ->where("active", '1')
			 ->get()
			 ->row_array();
		return ($league_detail) ? $league_detail : array();
	}

}
