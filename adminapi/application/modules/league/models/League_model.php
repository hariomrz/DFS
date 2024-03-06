<?php defined('BASEPATH') OR exit('No direct script access allowed');

class League_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
	}

	/**
     * for get sports list
     * @param
     * @return array
     */
    public function get_sports_list($post_data) {
		$language = 'en';
		if(!empty($post_data['language'])){
			$language = strtolower($post_data['language']);
		}
        $result = $this->db_fantasy->select('MS.order,MSF.sports_id,MSF.display_name,IFNULL(MSF.'.$language.'_display_name,MSF.display_name) AS '.$language.'_display_name')
                    ->from(MASTER_SPORTS . " MS")
                    ->join(MASTER_SPORTS_FORMAT . " MSF", "MSF.sports_id=MS.sports_id")
                    ->where('MS.active', '1')
                    ->where('MSF.status', '1')
                    ->order_by("MS.order", "ASC")
                    ->get()
					->result_array();
		return $result;
	}

	public function update_sports_list($data_arr) {
		$order_update = array();
		foreach($data_arr as $key=>$data){
			$order_update[$key]['order'] = $data['order'];
			$order_update[$key]['sports_id'] = $data['sports_id'];
			unset($data_arr[$key]['order']);
		}
		$sports_order = $this->db_fantasy->select('sports_id,order')
		->from(MASTER_SPORTS . " MS")
		->order_by("MS.order", "ASC")
		->get()->result_array();
		$this->db_fantasy->update_batch(MASTER_SPORTS,$order_update,'sports_id');
		$this->db_fantasy->update_batch(MASTER_SPORTS_FORMAT,$data_arr, 'display_name');
        return $sports_order;
	}

	/**
	 * Used for get league list by sports id
	 * @param int $sports_id
	 * @return array
	 */
	public function get_sport_league_list($post_data)
	{
		$current_date = format_date();
		$this->db_fantasy->select('L.league_id,L.league_uid,L.league_abbr,IFNULL(league_display_name,league_name) AS league_name',FALSE)
		 	->from(LEAGUE." AS L")
			->where('L.active','1')
			->where('L.sports_id',$post_data['sports_id'])
			->group_by('L.league_id');

		if(isset($post_data['status']) && $post_data['status'] == "upcoming"){
			$this->db_fantasy->where('L.league_schedule_date >',$current_date);
		}else if(isset($post_data['status']) && $post_data['status'] == "live"){
			$this->db_fantasy->where('L.league_schedule_date <=',$current_date);
			$this->db_fantasy->where('L.league_last_date >=',$current_date);
		}else if(isset($post_data['status']) && $post_data['status'] == "completed"){
			$this->db_fantasy->where('L.league_last_date <',$current_date);
		}

        $sql = $this->db_fantasy->get();
		$result = $sql->result_array();
		return $result;
	}

	/**
	 * Used for get league detail by id
	 * @param int $league_id
	 * @return array
	 */
	public function get_league_detail($league_id)
	{
		$this->db_fantasy->select('L.league_id,L.sports_id,L.league_uid,L.league_abbr,IFNULL(league_display_name,league_name) AS league_name',FALSE)
		 	->from(LEAGUE." AS L")
			->where('L.active','1')
			->where('L.league_id',$league_id);

        $sql = $this->db_fantasy->get();
		$result = $sql->row_array();
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
		$this->db_fantasy->select('L.league_id,IFNULL(league_display_name,league_name) AS league_name,IFNULL(league_display_name,league_name) AS league_abbr,L.league_schedule_date,L.league_last_date,L.is_featured,L.auto_published')
			 	->from(LEAGUE." AS L")
			 	->where('L.active',1)
			 	->where('L.sports_id',$post_data['sports_id']);
		if(isset($post_data['is_featured']) && $post_data['is_featured'] == 1){
			$this->db_fantasy->where('is_featured',1);
		}else{
			$this->db_fantasy->where('L.league_schedule_date <= ', $format_date);
			$this->db_fantasy->where('L.league_last_date > ', $current_date);
		}

        if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db_fantasy->like('CONCAT(IFNULL(L.league_display_name,""),IFNULL(L.league_name,""))', $post_data['keyword']);
		}
		$sql = $this->db_fantasy->order_by($sort_field, $sort_order);
		$tempdb = clone $this->db_fantasy;
		$query = $this->db_fantasy->get();
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
		$result = $this->db_fantasy->select('count(league_id) as total')
						->from(LEAGUE)
						->where('is_featured',1)
						->where('sports_id',$sports_id);
						// if(isset($post_data['is_featured']) && $post_data['is_featured'] == 0){
						// $this->db_fantasy->where('league_schedule_date <= ', $format_date);
						// $this->db_fantasy->where('league_last_date > ', $current_date);
						// }				
						return $this->db_fantasy->get()->row_array();
						
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
		$this->db_fantasy->where('league_schedule_date < ', $format_date);
        $this->db_fantasy->where('league_last_date < ', $format_date);
        $this->db_fantasy->where('is_featured',1);
		$this->db_fantasy->update(LEAGUE,array("is_featured"=>0));
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
		$this->db_fantasy->where("league_id",$post_data['league_id']);
		$this->db_fantasy->update(LEAGUE, $update_arr);
		return $this->db->affected_rows() || true;
	}

	/** 
     * common function used to get group list
     * @param array $data
     * @return	array
     */
	public function get_config_sports_list()
	{
		$sql = $this->db_fantasy->select('MS.sports_id,MS.sports_name,MS.max_player_per_team')
				->from(MASTER_SPORTS." MS")
				->where('MS.active', '1')
				->group_by('MS.sports_id')
				->order_by('MS.order', 'ASC')
				->get();
		$result = $sql->result_array();
		return $result;
	}

	/** 
     * common function used to get group list
     * @param array $data
     * @return	array
     */
	public function get_sports_position_config($sports_id)
	{
		$sql = $this->db_fantasy->select('MLP.master_lineup_position_id as mpl_id,MLP.position_name as position,MLP.number_of_players as min_player,MLP.max_player_per_position as max_player')
				->from(MASTER_LINEUP_POSITION." MLP")
				->where('MLP.sports_id', $sports_id)
				->where('MLP.position_name !=',"FLEX")
				->group_by('MLP.position_name')
				->order_by('MLP.position_order', 'ASC')
				->get();
		$result = $sql->result_array();
		return $result;
	}

	/** 
     * common function used to save sports config
     * @param array $post_data
     * @return	array
     */
	public function save_sports_config($post_data)
	{
		$sports_id = $post_data['sports_id'];
		$max_player_per_team = isset($post_data['max_player_per_team']) ? $post_data['max_player_per_team'] : 7;
		$this->db_fantasy->where("sports_id",$post_data['sports_id']);
		$this->db_fantasy->update(MASTER_SPORTS,array("max_player_per_team"=>$max_player_per_team));

		$pos_list = $this->get_sports_position_config($sports_id);
		foreach($pos_list as $pos){
			if(isset($post_data['position_min'][$pos['mpl_id']]) && $post_data['position_max'][$pos['mpl_id']]){
				$data_arr = array("number_of_players"=>$post_data['position_min'][$pos['mpl_id']],"max_player_per_position"=>$post_data['position_max'][$pos['mpl_id']]);
				$this->db_fantasy->where("master_lineup_position_id",$pos['mpl_id']);
				$this->db_fantasy->update(MASTER_LINEUP_POSITION,$data_arr);
			}
		}

		unset($post_data['submit']);
		unset($post_data['key']);
		$h_data = array("admin_id"=>$this->admin_id,"ip_address"=>get_user_ip_address(),"date_created"=>format_date(),"data"=>json_encode($post_data));
		$this->db_fantasy->insert('sports_config_history',$h_data);
		return true;
	}

	/**
	* FUNCTIONS USED IN BACK-END API'S(FROM ADMIN) 
	*/
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

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('league_abbr','league_uid','L.active','league_schedule_date', 'league_last_date','L.is_promote')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		//SELECT season_scheduled_date,league_id,COUNT(season_id) as total_count,
		//is_published FROM `vi_season` WHERE season_scheduled_date >= '2020-02-18' 
		//AND is_published=1 GROUP BY league_id HAVING total_count > 2
		$this->db_fantasy->select('IFNULL(league_display_name,league_name) AS league_name, IFNULL(league_display_name,league_name) AS league_abbr,season_scheduled_date,L.league_id,COUNT(season_id) as total_count,is_published')
						 ->from(LEAGUE." AS L")
						 ->join(SEASON . " AS S", "L.league_id = S.league_id", 'left')
						 ->group_by('L.league_id');
						 

				if(isset($post_data['for_collection']))
                {
					/* $this->db_fantasy->join(COLLECTION_MASTER . " AS CM", "L.league_id = CM.league_id", 'INNER')
					->where('CM.season_game_count >=', 2);
					//MAX(CS1.season_scheduled_date) as max_season_scheduled_date
					$this->db_fantasy->where('S.season_scheduled_date >',$Current_DateTime); */
					$this->db_fantasy->where('S.season_scheduled_date >',$Current_DateTime);
					$this->db_fantasy->where('S.is_published', '1');
					$this->db_fantasy->having("total_count >=2");
				}
				

                if(!empty($post_data['sports_id'])&&!is_array($post_data['sports_id']))
                {
                    $this->db_fantasy->where('L.sports_id', $post_data['sports_id']);
                }
                elseif(!empty($post_data['sports_id'])&&is_array($post_data['sports_id']))
                {
                        $this->db_fantasy->where_in('L.sports_id', $post_data['sports_id']);
                }

                if(isset($post_data['active']))
                {
                    $this->db_fantasy->where('L.active', $post_data['active']);
                }
                
                if(isset($post_data['league_status']))
                {
                    if($post_data['league_status'] == 0 && $post_data['league_status'] != '') // Upcoming
                    {
                        $this->db_fantasy->where('L.league_schedule_date > ', $Current_DateTime);
                    }
                    if($post_data['league_status'] == 1) // Live
                    {
                        $this->db_fantasy->where('L.league_schedule_date < ', $Current_DateTime);
                        $this->db_fantasy->where('L.league_last_date > ', $Current_Date);
                    }
                    if($post_data['league_status'] == 2) // Completed
                    {
                        $this->db_fantasy->where('L.league_last_date < ', $Current_Date);
					}
					if($post_data['league_status'] == 3) // Live and Upcomming leagues used for fantasy leaderboard
                    {
                        $this->db_fantasy->where('L.league_last_date > ', $Current_Date);
                    }
                }
                // remove 6 month old league from list // to enable check, uncomment condition
                //$this->db->where("DATE_FORMAT ( L.league_last_date ,'%Y-%m-%d' ) >= DATE_SUB('" . $Current_Date . "' , INTERVAL 6 MONTH)");

		$sql = $this->db_fantasy->order_by($sort_field, $sort_order);

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
	 * Function used for update league featured status
	 * @param array $post_data
	 * @return array
	 */
	public function update_auto_publish_status($post_data)
	{

		// echo '<pre>';
		// print_r($post_data);die;
		$update_arr = array("auto_published" => $post_data['auto_published']);
		$this->db_fantasy->where("league_id",$post_data['league_id']);
		$this->db_fantasy->update(LEAGUE, $update_arr);
		return $this->db->affected_rows() || true;
	}


}