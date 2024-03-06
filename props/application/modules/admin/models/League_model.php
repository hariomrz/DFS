<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class League_model extends MY_Model{

  	function __construct()
  	{
	  	parent::__construct();
	  	$this->user_db = $this->load->database('user_db', TRUE);
	}

	/*
     * used for get payout list
     * @param void
     * @return array
    */
    public function get_payout_list($payout_type)
    {
        $this->db->select('payout_id,payout_type,picks,correct,points,status')
            ->from(MASTER_PAYOUT)
            ->where_in('payout_type',$payout_type)
            ->order_by('payout_type','ASC')
            ->order_by('picks','ASC')
            ->order_by('correct','DESC');
        $sql = $this->db->get();
        $result = $sql->result_array();
        return ($result) ? $result : array();
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
		$this->db->select('L.league_id,IFNULL(display_name,league_name) AS league_name,L.league_abbr,L.start_date,L.end_date,L.status')
			 	->from(LEAGUE." AS L")
			 	->where('L.sports_id',$post_data['sports_id']);

        if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('CONCAT(IFNULL(L.display_name,""),IFNULL(L.league_name,""),L.league_abbr)', $post_data['keyword']);
		}
		// if(isset($post_data['from_date']) && $post_data['from_date'] != ""){
		// 	$this->db->where("L.start_date >= ",$post_data['from_date']);
		// }
		// if(isset($post_data['to_date']) && $post_data['to_date'] != ""){
		// 	$this->db->where("L.start_date <= ",$post_data['to_date']);
		// }

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
     * Used for get sports wise players list 
     * @param array $post_data
     * @return json array
     */
	public function get_player_list($post_data)
	{
		$page = get_pagination_data($post_data);
		$sort_field = 'team_name';
		$sort_order = 'DESC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('full_name','display_name','player_uid','position')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
      	
		$sql = $this->db->select("P.player_id,P.player_uid,P.full_name,P.display_name,P.position,P.country,IFNULL(P.image,'') as image ",FALSE)
				->from(PLAYER.' AS P')
				->where("P.sports_id",$post_data['sports_id'])
			 	->order_by('P.display_name', 'ASC');

		if (!empty($post_data['keyword'])) 
		{
			$this->db->like('CONCAT(IFNULL(P.full_name,""),IFNULL(P.display_name,""))', trim($post_data['keyword']));
		}
      
		$this->db->order_by($sort_field, $sort_order);
		$tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();
		$sql = $tempdb->limit($page['limit'],$page['offset'])->get();
		$result = $sql->result_array();
		// if (!empty($post_data['keyword'])) 
		// {
        //    	$result = $query->result_array();
		// }else{

		// 	$result = $sql->result_array();
		// }
		return array('result'=>$result,'total'=>$total);
	}

	public function update_config($data)
	{

		$this->user_db->where('key_name','allow_props');
		$this->user_db->update(APP_CONFIG,array('custom_data'=>json_encode($data)));
		return true;
	}
}
