<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class League_model extends MY_Model{

  	function __construct()
  	{
	  	parent::__construct();
	}

	/**
	 * Function used for get league list
	 * @param array $post_data
	 * @return array
	 */
	public function get_league_list($post_data)
	{		
		$format_date = format_date();
		$page = get_pagination_data($post_data);
		$sort_field	= 'league_name';
		$sort_order	= 'DESC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('league_abbr','league_uid','league_name','league_schedule_date', 'league_last_date')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		$this->db->select('L.league_id,IFNULL(display_name,league_name) AS league_name,L.league_abbr,L.start_date,L.end_date,L.status,L.auto_published')
			 	->from(LEAGUE." AS L")
				->join(SEASON.' S','S.league_id = L.league_id','INNER')
				->group_by("L.league_id")	
			 	->where('L.sports_id',$post_data['sports_id']);

        if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('CONCAT(IFNULL(L.display_name,""),IFNULL(L.league_name,""),L.league_abbr)', $post_data['keyword']);
		}
	

		if(!empty($post_data['from_date'])&&!empty($post_data['to_date'])){
		  $this->db->where("DATE_FORMAT(L.start_date,'%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(L.start_date,'%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."'");

		}
	
		$sql = $this->db->order_by($sort_field, $sort_order);
		$tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();
		$sql = $tempdb->limit($page['limit'],$page['offset'])->get();
		$result = $sql->result_array();
		
		return array('result'=>$result,'total'=>$total);
	}

	/**
     * Used for get sports wise team list 
     * @param array $post_data
     * @return json array
     */
	public function get_team_list($post_data)
	{
		$page = get_pagination_data($post_data);
		$sort_field = 'team_name';
		$sort_order = 'DESC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('team_name','team_abbr','sports_name')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
      	
		$sql = $this->db->select("T.team_id,T.team_uid,IFNULL(display_name,team_name) as team_name,IFNULL(display_abbr,team_abbr) as team_abbr,IFNULL(T.flag,T.feed_flag) AS flag,IFNULL(T.jersey,T.feed_jersey) as jersey,MS.sports_name",FALSE)
				->from(TEAM.' AS T')
				->join(MASTER_SPORTS." AS MS", "MS.sports_id = T.sports_id", 'INNER')
				->where("T.sports_id",$post_data['sports_id'])
			 	->order_by('T.team_name', 'ASC');

		if (!empty($post_data['keyword'])) 
		{
			$this->db->like('CONCAT(IFNULL(T.team_name,""),IFNULL(T.team_abbr,""),IFNULL(T.display_abbr,""),IFNULL(T.display_name,""))', trim($post_data['keyword']));
		}
      
		$this->db->order_by($sort_field, $sort_order);
		$this->db->group_by("T.team_id");
		$tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();
		$sql = $tempdb->limit($page['limit'],$page['offset'])->get();
		$result = $sql->result_array();     
		return array('result'=>$result,'total'=>$total);
	}


	/**
     * Used for get sports wise team list 
     * @param array $post_data
     * @return json array
     */
	public function get_template_list($post_data)
	{
		$page = get_pagination_data($post_data);
		$sort_field = 'template_id';
		$sort_order = 'DESC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('template_id','sports_name')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
      	
		$sql = $this->db->select("MT.template_id,MS.sports_name,MT.name,MT.status",FALSE)
				->from(MASTER_TEMPLATE.' AS MT')
				->join(MASTER_SPORTS." AS MS", "MS.sports_id = MT.sports_id", 'INNER')			
				->where("MT.sports_id",$post_data['sports_id']);
			 	// ->order_by('T.team_name', 'ASC');

		// if (!empty($post_data['keyword'])) 
		// {
		// 	$this->db->like('CONCAT(IFNULL(T.team_name,""),IFNULL(T.team_abbr,""),IFNULL(T.display_abbr,""),IFNULL(T.display_name,""))', trim($post_data['keyword']));
		// }
      
		$this->db->order_by($sort_field, $sort_order);
		// $this->db->group_by("T.team_id");
		$tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();
		$sql = $tempdb->limit($page['limit'],$page['offset'])->get();
		$result = $sql->result_array();     
		return array('result'=>$result,'total'=>$total);
	}

	public function get_fixture_list_by_league_id($league_id)
	{
		$sql = $this->db->select("S.season_id,S.title,T1.display_abbr as home_name,T2.display_abbr as away_name,S.scheduled_date", FALSE);
			$this->db->from(SEASON . " AS S")
			->join(TEAM.' T1','T1.team_id=S.home_id','left')		
			->join(TEAM.' T2','T2.team_id=S.away_id','left');
			// if ($league_id!='') {					
				$this->db->where("S.league_id", $league_id);
			// }			
			return $this->db->get()->result_array();
		
	}

	/**
	 * Function used for get league list
	 * @param array $post_data
	 * @return array
	 */
	public function get_all_league_list($post_data)
	{		
		
		$this->db->select('L.league_id,IFNULL(display_name,league_name) AS league_name,L.league_abbr,L.start_date,L.end_date,L.status,L.auto_published')
			 	->from(LEAGUE." AS L")
				->join(SEASON.' S','S.league_id = L.league_id','INNER')
			 	->where('L.sports_id',$post_data['sports_id'])
				->group_by("S.league_id");	
				 
		$query = $this->db->get();
	
		$result = $query->result_array();
		return $result;
	}


}
