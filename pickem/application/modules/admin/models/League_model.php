<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class League_model extends MY_Model{

  	function __construct()
  	{
	  	parent::__construct();
	}

	/**
	 * Used for get active sports list
	 * @param void
	 * @return array
	 */
	public function get_sport_list()
	{
		$this->db->select('MS.sports_id,MS.sports_name,MS.order')
                ->from(MASTER_SPORTS." MS")
                ->where('MS.status', '1')
                ->order_by("MS.order", "ASC");
        $sql = $this->db->get();
		$result = $sql->result_array();
		return $result;
	}


	/**
	 * Function used for get active league list
	 * @param int $sports_id
	 * @return array
	 */
	public function get_active_league_list($post_data)
	{
		$format_date = format_date();
		$current_date = date("Y-m-d",strtotime($format_date));
		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}
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
		$this->db->select('L.league_id,IFNULL(display_name,league_name) AS league_name,IFNULL(display_name,league_name) AS league_abbr,L.start_date as league_schedule_date,L.end_date as league_last_date,L.is_featured')
			 	->from(LEAGUE." AS L")
			 	->where('L.status',1)
			 	->where('L.sports_id',$post_data['sports_id']);
				if(isset($post_data['is_featured']) && $post_data['is_featured'] == 1){
			        $this->db->where('is_featured',1);
		        }else{
			        $this->db->where('L.start_date <= ', $format_date);
			        $this->db->where('L.end_date > ', $current_date);
	 	        }
			 	// ->where('L.start_date <= ', $format_date)
			 	// ->where('L.end_date > ', $current_date);

        if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('CONCAT(IFNULL(L.display_name,""),IFNULL(L.league_name,""))', $post_data['keyword']);
		}
		$sql = $this->db->order_by($sort_field, $sort_order);
		$tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();
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
	 * Function used for get featured league count
	 * @param int $sports_id
	 * @return array
	 */
	public function get_featured_league_count($sports_id)
	{
		$post_data = $this->input->post();	
		$format_date = format_date();
		$current_date = date("Y-m-d",strtotime($format_date));
		$result = $this->db->select('count(league_id) as total')
						->from(LEAGUE)
						->where('is_featured',1)
						->where('sports_id',$sports_id);
						// if(isset($post_data['is_featured']) && $post_data['is_featured'] != 1){
						// $this->db->where('start_date <= ', $format_date);
						// $this->db->where('end_date > ', $current_date);
						// }
					return	$this->db->get()->row_array();
		// return $result;
	}

		/**
	 * Function used for update old leagues
	 * @param void
	 * @return array
	 */
	public function update_outdated_featured_status()
	{
		$format_date = format_date();
		$this->db->where('start_date < ', $format_date);
        $this->db->where('end_date < ', $format_date);
        $this->db->where('is_featured',1);
		$this->db->update(LEAGUE,array("is_featured"=>0));
		return $this->db->affected_rows() || true;
	}

	/**
	 * Function used for update league featured status
	 * @param array $post_data
	 * @return array
	 */
	public function update_leagues_featured_status($post_data)
	{
		$update_arr = array("is_featured" => $post_data['is_featured']);
		$this->db->where("league_id",$post_data['league_id']);
		$this->db->update(LEAGUE, $update_arr);
		return $this->db->affected_rows() || true;
	}

	/**
	 * Used for get league detail by id
	 * @param int $league_id
	 * @return array
	 */
	public function get_league_detail($league_id)
	{
		$this->db->select('L.league_id,L.sports_id,L.league_uid,L.league_abbr,IFNULL(display_name,league_name) AS league_name',FALSE)
		 	->from(LEAGUE." AS L")
			->where('L.status','1')
			->where('L.league_id',$league_id);

        $sql = $this->db->get();
		$result = $sql->row_array();
		return $result;
	}
}
