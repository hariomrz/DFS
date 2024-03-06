<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Props_model extends MY_Model{

  	function __construct()
  	{
	  	parent::__construct();
	}

	/**
	 * Function used for get props match list for filter
	 * @param int $sports_id
	 * @return array
	 */
	public function get_props_match_list($sports_id)
	{
		$current_date = format_date();
		$this->db->select("S.season_id,S.scheduled_date,IFNULL(T1.display_abbr,T1.team_abbr) as home,IFNULL(T2.display_abbr,T2.team_abbr) as away",FALSE)
				->from(SEASON." AS S")
	            ->join(LEAGUE." AS L", "L.league_id = S.league_id", "INNER")
	            ->join(SEASON_PROPS." AS SP", "SP.season_id = S.season_id", "INNER")
	            ->join(TEAM." AS T1", "T1.team_id = S.home_id", "INNER")
	            ->join(TEAM." AS T2", "T2.team_id = S.away_id", "INNER")
			 	->where('S.is_published',"1")
			 	->where('L.sports_id',$sports_id)
			 	->where("S.scheduled_date > ",$current_date)
			 	->group_by('S.season_id')
			 	->order_by("S.scheduled_date","ASC");
		$result = $this->db->get()->result_array();
		return $result;
	}

	/**
	 * Function used for get user report list
	 * @param array $post_data
	 * @return array
	 */
	public function get_player_props_list($post_data)
	{
		$current_date = format_date();
		$page = get_pagination_data($post_data);
		$sort_field	= 'display_name';
		$sort_order	= 'DESC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('display_name')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		$this->db->select("SP.season_prop_id,SP.season_id,SP.player_id,SP.prop_id,SP.team_id,SP.position,SP.points,SP.status,S.scheduled_date,P.full_name,P.display_name,CONCAT(IFNULL(T1.display_abbr,T1.team_abbr),' vs ',IFNULL(T2.display_abbr,T2.team_abbr)) as match_name",FALSE)
				->from(SEASON." AS S")
	            ->join(LEAGUE." AS L", "L.league_id = S.league_id", "INNER")
	            ->join(SEASON_PROPS." AS SP", "SP.season_id = S.season_id", "INNER")
	            ->join(PLAYER." AS P", "P.player_id = SP.player_id", "INNER")
	            ->join(TEAM." AS T1", "T1.team_id = S.home_id", "INNER")
	            ->join(TEAM." AS T2", "T2.team_id = S.away_id", "INNER")
			 	->where('L.sports_id',$post_data['sports_id'])
			 	->where("S.scheduled_date > ",$current_date)
			 	->group_by('SP.season_prop_id');

	 	if(isset($post_data['prop_id']) && $post_data['prop_id'] != "")
		{
			$this->db->where("SP.prop_id",$post_data['prop_id']);
		}
		if(isset($post_data['season_id']) && $post_data['season_id'] != "")
		{
			$this->db->where("S.season_id",$post_data['season_id']);
		}
        if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('CONCAT(IFNULL(P.display_name,""),IFNULL(P.full_name,""))', $post_data['keyword']);
		}
		// if(isset($post_data['from_date']) && $post_data['from_date'] != ""){
		if(!empty($post_data['from_date'])&&!empty($post_data['to_date'])){
			// $this->db->where("DATE_FORMAT(S.scheduled_date,'%Y-%m-%d %H:%i:%s') >=",date("Y-m-d", strtotime($post_data['from_date'])));
			$this->db->where("DATE_FORMAT(S.scheduled_date,'%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(S.scheduled_date,'%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."'");

		}
		// if(isset($post_data['to_date']) && $post_data['to_date'] != ""){
		// 	$this->db->where("DATE_FORMAT(S.scheduled_date,'%Y-%m-%d %H:%i:%s') <= ",date("Y-m-d", strtotime($post_data['to_date'])));
		// }
		$sql = $this->db->order_by($sort_field, $sort_order);
		$tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();
		$sql = $tempdb->limit($page['limit'],$page['offset'])->get();
		$result = $sql->result_array();
		//echo $this->db->last_query();die;
		return array('result'=>$result,'total'=>$total);
	}

	/**
	 * Function used for update player props status
	 * @param array $post_data
	 * @return array
	 */
	public function update_player_props_status($post_data)
	{
		if(empty($post_data))
		{
			return true;
		}
		$this->db->update_batch(SEASON_PROPS,$post_data,'season_prop_id');
		return true;
	}
}
