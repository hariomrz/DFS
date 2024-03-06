<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Season_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}


	/**
     * used to get collection list
     * @param array $post_data
     * @return array
     */
    public function get_all_season_schedule($post_data) {
    	$current_time = format_date();
		$limit = 200;
		$page = 0;		
		$sort_field = '';
		$sort_order = 'DESC';
		if(isset($post_data['sort_field']))
		{
			$sort_field = $post_data['sort_field'];
			
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		if(!empty($post_data['page_size']) && $post_data['page_size'])
		{
			$limit = $post_data['page_size'];
		}

		if(!empty($post_data['current_page']) && $post_data['current_page'])
		{
			$page = $post_data['current_page'];
		}
		$offset	= $limit * ($page-1);

		$status		= isset($post_data['status']) ? $post_data['status'] : "";
      
        $sql = $this->db->select("S.season_id,S.season_game_uid,S.league_id,S.match,S.scheduled_date,S.question,S.correct,S.wrong,S.status,IFNULL(L.display_name,L.league_name) as league_name,T1.flag as home_flag,T2.flag as away_flag,T1.team_abbr as home,T2.team_abbr as away,L.sports_id,S.published,IF(C.contest_id,1,0) as is_contest,S.delay_message,S.delay_minute,S.is_pin_fixture", FALSE)
                ->from(SEASON." AS S")
                ->join(LEAGUE.' as L',"L.league_id=S.league_id")
			    ->join(TEAM.' T1','T1.team_id=S.home_id','left')		
			    ->join(TEAM.' T2','T2.team_id=S.away_id','left')
			    ->join(CONTEST." C","S.season_id=C.season_id","LEFT")
                ->where("L.sports_id",$post_data['sports_id']);

                 //upcoming
                if($status ==='0')
				{			
					$this->db->where("DATE_FORMAT(S.scheduled_date,'%Y-%m-%d %H:%i:%s') >= '".$current_time."'");
					$this->db->where("S.status","0");
				}
				else if($status === '1'){
					//live				
					$this->db->where("DATE_FORMAT(S.scheduled_date,'%Y-%m-%d %H:%i:%s') <= '".$current_time."'");
					$this->db->where("S.status","0");
				}
				else if($status === '2'){
					//completed		
					$this->db->where("DATE_FORMAT(S.scheduled_date,'%Y-%m-%d %H:%i:%s') <= '".$current_time."'");
					$this->db->where("S.status","2");
				}
        $this->db->group_by('S.season_id');      
		$tempdb = clone $this->db;		
		$query = $this->db->get();
		$total = $query->num_rows();		
		$sql = $tempdb->limit($limit, $offset)
						->order_by($sort_field,$sort_order)
						->get();
		// echo $tempdb->last_query(); die();
		$result	= $sql->result_array();
		$result = ($result) ? $result : array();
		return array('result'=>$result, 'total'=>$total);
    }


    /**
	 * This function used to create template contest
	 * @param array $contest_data
	 * @return int
	 */
	public function insert_question_option($option_data)
	{
		$this->db->insert_batch(PICKS,$option_data);
		return true;
	}


    /**
	 * get season by id
	 * @param array $season_game_uid
	 * @param array $league_id
	 * @return int
	 */
	public function get_season_by_game_id($season_game_uid,$league_id)
	{
		$rs = $this->db->select("S.*,L.sports_id", FALSE)
				->from(SEASON . " AS S")
				->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
				->where("S.season_game_uid", $season_game_uid)
				->where("S.league_id", $league_id)
				->get();
		$res = $rs->row_array();
		return $res;
	}

    /**
	 * get season by id
	 * @param array $season_game_uid
	 * @param array $league_id
	 * @return int
	 */
	public function update_match_delay_data($post_data){

		$delay_minute = $post_data['new_delay_minute'] - $post_data['delay_minute'];

		$season_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$delay_minute.' minutes', strtotime($post_data['scheduled_date'])));

        $upd_data = array('scheduled_date'=>$season_scheduled_date,'delay_minute' => $post_data['new_delay_minute'],'delay_message' => $post_data['delay_message'],"delay_by_admin"=>1);
        $this->db->where('season_game_uid', $post_data['season_game_uid']);
        $this->db->where('league_id', $post_data['league_id']);
        $this->db->update(SEASON, $upd_data);

		if(isset($post_data['season_id']) && !empty($post_data['season_id'])){	

            //Update schedule date in contest table     

	        $this->db->where('season_id', $post_data['season_id']);
	        $this->db->where('league_id', $post_data['league_id']);		
            $this->db->update(CONTEST,array("scheduled_date"=>$season_scheduled_date));
		}
		return true;
	}


	/**
	 * get question by id
	 * @param array $season_id
	 * @param 
	 * @return array()
	 */
	public function get_exist_question($season_id)
	{
		$rs = $this->db->select("*", FALSE)
				->from(PICKS . " AS P")				
				->where("P.season_id", $season_id)
				->get();
		$res = $rs->result_array();
		return $res;
	}


	/**
	 * get season by id
	 * @param array $season_game_uid
	 * @param array $league_id
	 * @return int
	 */
	public function get_fixture_by_league_id($league_id = '')
	{
		$rs = $this->db->select("S.season_id,S.season_game_uid,S.match", FALSE)
			->from(SEASON . " AS S");			
			$this->db->where("S.league_id", $league_id);						
			return $this->db->get()->result_array();
		
	}

	/**
	 * update by season by id
	 * @param array $season_id
	 * 
	 * @return int
	 */

	public function update_question_option($options_arr,$season_id){

		$this->db->update_batch(PICKS,$options_arr, $season_id); 
        return true;
	}

     /**
	 * check team exist
	 * @param $team_id	 
	 * @return array
	 */
	public function check_team_exit($team_id){
		$this->db->select("*", FALSE)
        ->from(SEASON." AS S")
        ->where('S.home_id',$team_id)
        ->or_where('S.away_id',$team_id);	
	    return $this->db->get()->result_array();	  	
	}

    /**
	 * get_right_wrong
	 * @param array $season_id
	 * @return array
	 */
	public function get_right_wrong($season_id)
	{
		$rs = $this->db->select("S.correct,S.wrong", FALSE)
				->from(SEASON . " AS S")				
				->where("S.season_id", $season_id)
				->get();
		$res = $rs->row_array();
		return $res;
	}

	/**
	 * get season by id
	 * @param Int Season_id
	 * @return int
	 */
	public function get_season_sport_id($season_id)
	{
		$rs = $this->db->select("S.season_id,S.is_pin_fixture,L.sports_id", FALSE)
				->from(SEASON . " AS S")
				->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
				->where("S.season_id", $season_id)
				->get();
		$res = $rs->row_array();
		return $res;
	}
}
/* End of file Season_model.php */
/* Location: ./application/models/Season_model.php */