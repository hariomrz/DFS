<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Team_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
		
	} 
	 
	/**
	 * function used for get team list by sports id
	 * @param array $post_data
	 * @return array
	 */
	public function get_all_team_by_sport($post_data)
	{
		$sort_field = 'team_name';
		$sort_order = 'DESC';
		if(isset($post_data['items_perpage']) && $post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']) && $post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('team_name','team_abbr','sports_name')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
      
		$this->db_fantasy->select("T.team_id,IFNULL(T.display_team_name,T.team_name) as team_name,IFNULL(T.display_team_abbr,T.team_abbr) as team_abbr,IFNULL(T.display_team_abbr,T.team_abbr) as team_abbreviation,IFNULL(T.flag,T.feed_flag) AS flag,IFNULL(T.jersey,T.feed_jersey) as jersey,MS.sports_name",FALSE)
				->from(TEAM.' AS T')
				->join(MASTER_SPORTS." AS MS", "MS.sports_id = T.sports_id", 'INNER')
				->order_by($sort_field, $sort_order)
				->group_by("T.team_id");

		if(isset($post_data['sports_id']) && $post_data['sports_id']!="")
		{
			$this->db_fantasy->where("T.sports_id",$post_data['sports_id']);
		}

		if (!empty($post_data['search_text'])) 
		{
			$this->db_fantasy->like('LOWER( CONCAT(IFNULL(T.team_name,""),IFNULL(T.team_abbr,""),IFNULL(T.display_team_abbr,""),IFNULL(T.display_team_name,"")))', strtolower($post_data['search_text']));
		}
      
		$tempdb = clone $this->db_fantasy;
		$query  = $this->db_fantasy->get();
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

	/**
	 * function used for update team detail
	 * @param array $post_data
	 * @return boolean
	 */
	public function edit_team_by_id($post_data)
	{
		$this->db_fantasy->where('team_id', $post_data["team_id"]);
		$this->db_fantasy->update(TEAM, $post_data["team_data"]);
		return true;
	}

}