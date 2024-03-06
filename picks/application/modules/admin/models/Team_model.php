<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Team_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
     * Used for get sports wise team list 
     * @param array $post_data
     * @return json array
     */
	public function get_team_list($post_data)
	{

		$sort_field = 'team_name';
		$sort_order = 'DESC';
		$page = 0;
		if (isset($post_data['limit']) &&  $post_data['limit'] == "all") {
			$limit = '100';
		}else{
		    $limit = RECORD_LIMIT;
		}
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('team_name','team_abbr','sports_name')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		if(isset($post_data['items_perpage']) && $post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']) && $post_data['current_page'])
		{
			$page = $post_data['current_page'];
		}
		$offset	= get_pagination_offset($page,$limit);
      	
		$sql = $this->db->select("T.team_id,T.team_uid,T.team_name,T.team_abbr,IFNULL(T.flag,'') as flag,IFNULL(T.jersey,'') as jersey,S.name as sports_name",FALSE)
					->from(TEAM.' AS T')
					->join(SPORTS." AS S", "S.sports_id = T.sports_id", 'INNER')
					// ->order_by($sort_field, $sort_order);
					 ->order_by('T.team_name', 'asc');

		if(isset($post_data['sports_id']) && $post_data['sports_id']!="")
		{
			$this->db->where("T.sports_id",$post_data['sports_id']);
		}

		if (!empty($post_data['search_text'])) 
		{
			$this->db->like('LOWER(CONCAT(IFNULL(T.team_name,""),IFNULL(T.team_abbr,""),IFNULL(T.team_abbr,"")))', strtolower($post_data['search_text']));
		}
      
		$this->db->order_by($sort_field, $sort_order);
		$this->db->group_by("T.team_id");

		$tempdb = clone $this->db;
		$query  = $this->db->get();
		$total  = $query->num_rows();

		$result = $tempdb->limit($limit,$offset)->get()->result_array();
		return array('result'=>$result,'total'=>$total);
	}


    /**
	 * Used for delete player/team
	 * @param int $team_id
	 * @return boolean
	 */
	public function delete_team($team_id)
	{
		$this->db->where("team_id",$team_id);
		$this->db->delete(TEAM);
		$is_deleted = $this->db->affected_rows();		
		return $is_deleted;
	}


	/**
     * Used for get league wise team list 
     * @param array $post_data
     * @return json array
     */
	public function get_team_by_league_id_list($league_id)
	{      	
	    $sql = $this->db->select("TL.*,T.team_name,T.flag",FALSE)
				->from(TEAM_LEAGUE.' AS TL')
				->join(LEAGUE." AS L", "L.league_id = TL.league_id", 'INNER')
				->join(TEAM." AS T", "T.team_id = TL.team_id", 'INNER')
				 ->order_by('T.team_name', 'asc')
				->where("TL.league_id",$league_id);					
				$result = $this->db->get()->result_array();
	
		return $result;	
	}

	/**
	 * Used for delete player/team 
	 * @param int $team_id ,league_id
	 * @return boolean
	 */
	public function delete_league_team($team_id,$league_id)
	{
		$this->db->where(array("league_id" => $league_id,"team_id"=>$team_id));
		$this->db->delete(TEAM_LEAGUE);
		$is_deleted = $this->db->affected_rows();		
		return $is_deleted;
	}

	/**
	 * get_team_name_by_id
	 * @param int $team_id
	 * @return boolean
	 */
	public function get_team_name_by_id($team_id)
	{	
		$rs = $this->db->select("T.*", FALSE)
				->from(TEAM . " AS T")		
				->where("T.team_id", $team_id)
				->get();
		$res = $rs->row_array();
		return $res;
	}


}