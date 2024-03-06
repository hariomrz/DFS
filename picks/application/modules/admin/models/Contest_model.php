<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Contest_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * [save_contest description]
	 * @MethodName save_contest
	 * @Summary This function used to create new contest
	 * @param      array  data array
	 * @return     int
	 */
	public function save_contest($data)
	{

		$this->db->trans_start();

		$this->db->insert(CONTEST,$data['contest']);

		$contest_id = $this->db->insert_id();
		$this->db->trans_complete();
		$this->db->trans_strict(FALSE);

		if ($this->db->trans_status() === FALSE)
		{
		    $this->db->trans_rollback();
			return false;
		}
		else
		{
			$this->db->trans_commit();		
			return $contest_id;
		}
	}

	/**
	 * This function used to create template contest
	 * @param array $contest_data
	 * @return int
	 */
	public function save_template_contest($contest_data)
	{
		$this->db->trans_start();

		$this->db->insert(CONTEST,$contest_data);
		$contest_id = $this->db->insert_id();
		
		$this->db->trans_complete();
		$this->db->trans_strict(FALSE);
		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			return false;
		}
		else
		{
			$this->db->trans_commit();
			return $contest_id;
		}
	}


	/**
	 * [get_fixture_contest description]
	 * Summary :- 
	 * @return [type] [description]
	 */
	public function get_fixture_contest($post_data)
	{	
		$sort_field = 'C.group_id';
		$sort_order = 'ASC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('template_name','minimum_size','size','entry_fee','added_date','prize_pool','is_auto_recurring','guaranteed_prize','max_bonus_allowed')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		
		$this->db->select('C.*,MG.group_name,DATE_FORMAT(C.added_date, "%d-%b-%Y %H:%i") AS added_date,IFNULL(C.contest_title,"") as template_title', FALSE)
			->from(CONTEST." AS C")
			// ->join(CONTEST_TEMPLATE." AS CT" , 'CT.group_id = CT.group_id','INNER')
			->join(MASTER_GROUP." AS MG", 'MG.group_id = C.group_id','INNER')
			->where('C.season_id',$post_data['season_id']);
	
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('C.template_name', $post_data['keyword']);
		}

		// $this->db->group_by('C.contest_template_id');
		$sql = $this->db->order_by($sort_field, $sort_order)
					->get();

		$result	= $sql->result_array();
		return array('result' => $result, 'total' => count($result));
	}


	 /**
	 * get game detail
	 */
    public function get_game_detail($game_unique_id)
	{      
		
		$sql = $this->db->select('C.max_bonus_allowed,C.league_id,C.prize_distibution_detail,C.user_id,C.season_id,C.sports_id,C.is_auto_recurring,C.scheduled_date,C.contest_id, C.contest_unique_id, C.contest_name, C.league_id, DATE_FORMAT(C.scheduled_date,"'.MYSQL_DATE_TIME_FORMAT.'") as scheduled_date, C.size, C.entry_fee,C.currency_type,C.prize_pool,C.status,DATE_FORMAT(C.added_date,"'.MYSQL_DATE_TIME_FORMAT.'") as added_date,C.site_rake, C.total_user_joined,C.guaranteed_prize,C.guaranteed_prize as prize_pool_type,C.is_pin_contest,C.prize_type,C.is_custom_prize_pool,C.multiple_lineup,C.completed_date,IFNULL(C.contest_title,"") as contest_title,C.is_tie_breaker')
						->from(CONTEST . " AS C")					
						->join(SEASON." AS S","S.season_id = C.season_id","LEFT")		
						->where('C.contest_unique_id', $game_unique_id)
						->get();
        // echo $this->db->last_query();die;
		$result = $sql->row_array();
		return $result;
	}



	/**
     * Used for get match by season ID 
     * @param array $post_data
     * @return json array
     */
	public function get_match_by_season_id($season_id)
	{   
 	
	    $sql = $this->db->select("*",FALSE)
			->from(SEASON.' AS S')				
			->join(TEAM." AS T1", "T1.team_id = S.home_id", 'INNER')
			->join(TEAM." AS T2", "T2.team_id = S.away_id", 'INNER')
			->where("S.season_id",$season_id);					
			 $result = $this->db->get()->result_array();
	
		return $result;	
	}

	/**
     *get_join_contest_user
     * @param contest_id
     * @return array
    */    

	public function get_join_contest_user($contest_id)
	{
		$prize_data = "JSON_UNQUOTE(JSON_EXTRACT(C.prize_data, '$[0].amount'))";
		$sql = $this->db->select("C.user_contest_id, C.contest_id,UT.user_id,UT.user_name,C.total_score,C.game_rank,C.is_winner,C.prize_data,C.fee_refund,C.added_date,C.modified_date,JSON_EXTRACT(C.prize_data, '$[0].amount') as winning_amount,UT.team_name", FALSE)
		    ->from(USER_CONTEST." C")
		    ->join(USER_TEAM." UT","UT.user_team_id=C.user_team_id")
			->where("C.contest_id",$contest_id)
			->order_by('C.game_rank','ASC'); 				
			$result = $sql->get()->result_array(); 
			// echo $this->db->last_query();die;      
            return $result;
	}


	/**
     *get_lineup_detail
     * @param contest_user_id
     * @return array
    */
	public function get_lineup_detail($season_id,$contest_user_id){		
		$result =   $this->db->select("UTP.pick_id,UTP.answer as user_predict,UTP.score,UTP.is_captain,UTP.is_vc,UTP.status,P.name,P.option_1,P.option_2,P.option_3,P.option_4,P.answer as correct_answer", FALSE)
		            ->from(USER_TEAM_PICKS." UTP")
		            ->join(PICKS. ' P',"P.pick_id =UTP.pick_id",'LEFT')
		            ->join(USER_CONTEST. ' UC',"UC.user_team_id=UTP.user_team_id",'LEFT')
		            ->join(USER_TEAM. ' UT',"UT.user_team_id=UC.user_team_id",'LEFT')
		           
					->where("UC.user_contest_id",$contest_user_id)			
					->where("UT.season_id",$season_id)		
					->get()
					->result_array();
        return $result;        
	}


	/**
	 * 
	 * get_match_paid_free_users
	 */
	public function get_match_paid_free_users($season_id)
    {
        $rs = $this->db->select("COUNT(UL.user_contest_id) as total_users,IFNULL(SUM(IF(C.entry_fee=0,1,0)),0) as free_users,
        IFNULL(SUM(IF(C.entry_fee>0,1,0)),0)  as paid_users", FALSE)
                        ->from(USER_CONTEST .' UL')
                        ->join(CONTEST. ' C',"UL.contest_id=C.contest_id")
                        ->where("C.season_id", $season_id)
                        ->where_in("C.status", array(0,2,3))
                        ->where('C.total_user_joined>',0)
                        // ->group_by('UL.user_id')
                        ->get();                    
        $result = $rs->result_array();
        return $result;
    }

    /**
	 * [get_contest_list description]
	 * Summary :- 
	 * @return [type] [description]
	 */
	public function get_contest_list($post_data)
	{		
		$current_time = format_date();
		$limit = 2;
		$page = 0;
		$status = isset($post_data['status'])?$post_data['status']:"";
		$sort_field = 'C.contest_id';
		$sort_order = 'DESC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_id','contest_name','entry_fee','minimum_size','size','total_user_joined','prize_pool','max_bonus_allowed','spot_left','real_teams','current_earning','potential_earning')))
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
	
		$this->db->select('C.status,C.contest_id,C.contest_unique_id,C.contest_template_id,C.league_id,C.group_id,C.scheduled_date,C.minimum_size,C.size,C.contest_name,C.currency_type,C.entry_fee,C.prize_pool,C.total_user_joined,C.multiple_lineup,C.prize_type, C.guaranteed_prize,C.is_auto_recurring,C.is_pin_contest,S.season_game_uid,S.match,C.prize_distibution_detail,C.max_bonus_allowed,(C.size-C.total_user_joined) as spot_left,(C.entry_fee*C.total_user_joined) as current_earning,(C.size-C.total_user_joined)*C.entry_fee as potential_earning,S.home_id,S.away_id,T1.flag as home_flag,T2.flag as away_flag,COUNT(UC.user_contest_id) as real_teams', FALSE)
			->from(CONTEST." AS C")		
			->join(USER_CONTEST." AS UC",'UC.contest_id = C.contest_id',"LEFT")		
			->join(SEASON." AS S", 'S.season_id = C.season_id','INNER')		
			->join(TEAM.' T1','T1.team_id=S.home_id','left')		
			 ->join(TEAM.' T2','T2.team_id=S.away_id','left')	
			->where('C.sports_id',$post_data['sports_id'])		
			->group_by('C.contest_id');

		if(isset($post_data['season_id']) && $post_data['season_id'] != "")
		{
			$this->db->where('S.season_id', $post_data['season_id']);
		}
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('C.contest_name', $post_data['keyword']);
		}

		if(isset($post_data['league_id']) && $post_data['league_id'] != "")
		{
			$this->db->where('C.league_id', $post_data['league_id']);
		}		

		if(isset($post_data['group_id']) && $post_data['group_id'] != "")
		{
			$this->db->where('C.group_id', $post_data['group_id']);
		}

		//check if rookie OFF or ON
		
		switch ($status)
		{
			case 'current_game':
				$this->db->where('C.status','0');
				$this->db->where("DATE_FORMAT(C.scheduled_date,'%Y-%m-%d %H:%i:%s') <= '".$current_time."'");;
				break;
			case 'completed_game':
				$this->db->where('C.status >','1');
				break;
			case 'cancelled_game':
				$this->db->where('C.status','1');
				break;
			case 'upcoming_game':
				$this->db->where('C.status','0');
				$this->db->where("DATE_FORMAT(C.scheduled_date,'%Y-%m-%d %H:%i:%s') >= '".$current_time."'");


				break;
			default:
				break;
		}
		// echo "string"; die();
		$tempdb = clone $this->db;
        $temp_q = $tempdb->get();

		
		$total = $temp_q->num_rows();

		// echo $total; die();
		
		$this->db->order_by($sort_field, $sort_order);
		$result = $this->db->limit($limit,$offset)->get()->result_array();
		// echo $this->db_fantasy->last_query();die('dfd');
		return array('result' => $result, 'total' => $total);
	}

    /**
	 * use for export contest winner
	 * 
	 */
	public function export_contest_winner_data()
	{	
		$post = $this->input->get();		
		$contest_id = $post['contest_id'];
		$return = array(
						'contest_name'=> '',
						'winner_name'=> '',
						'rank'=> 0,
						'entry_fee'=> 0,
						'total_entries'=> 0,
						'winning_amount'=> 0,
						"winning_bonus" => 0,
						"winning_coins" => 0						
						);
		$result = $this->db->select("C.contest_name,UT.user_name as winner_name,UC.game_rank as rank_value,C.entry_fee,UC.total_score,C.total_user_joined as total_entries,
		(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=0 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].amount')) ELSE 0 END) as winning_amount,

		(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=1 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].amount')) ELSE 0 END) as winning_bonus,
		(CASE WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[0].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[1].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[2].amount')) WHEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].prize_type'))=2 THEN JSON_UNQUOTE(json_extract(UC.prize_data, '$[3].amount')) ELSE 0 END) as winning_coins",FALSE)
				->from(CONTEST . " AS C")
				->join(USER_CONTEST . " AS UC", "UC.contest_id = C.contest_id", "INNER")
				->join(USER_TEAM.' UT', 'UT.user_team_id = UC.user_team_id','LEFT')
				//->where('LMC.is_winner',1)
				->where('C.contest_id',$contest_id)
				->order_by('rank_value','ASC')
				->get()->result_array();		
				
				
		if(empty($result))
		{
			return $return;
		}

		return $result;

	}

	/**
	 * [contest_list description]
	 * @MethodName contest_list
	 * @Summary This function used for get all contest List
	 * @return     [array]
	 */
	public function get_completed_contest_report($post_params)
	{ 
		$sort_field = 'G.scheduled_date';
		$sort_order = 'DESC';
		$limit      = 50;
		$page       = 0;
		
		$post_data = $post_params;
		// print_r($data_arr);die();
		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','match','size','entry_fee','scheduled_date','prize_pool','guaranteed_prize','total_user_joined','minimum_size','site_rake')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;	
		
		$this->db->select("G.group_id,MG.group_name,S.match,G.contest_id,G.contest_unique_id, G.contest_name, G.entry_fee, G.prize_pool,G.site_rake, G.total_user_joined, G.size,G.minimum_size, (CASE 
		WHEN G.guaranteed_prize=0 THEN 'No Guarantee'
		 WHEN G.guaranteed_prize=1 THEN 'Guaranteed prize custom'
		 WHEN G.guaranteed_prize=2 THEN 'Guaranteed'
		 END
		 ) AS guaranteed_prize,G.scheduled_date,count(UC.user_contest_id) as real_teams,G.currency_type,G.max_bonus_allowed,G.entry_fee*G.total_user_joined as total_entry_fee",false)
		->from(CONTEST." AS G")
		->join(SEASON." AS S","S.season_id = G.season_id","LEFT")
		->join(USER_CONTEST." AS UC", 'UC.contest_id = G.contest_id','LEFT')
		->join(USER_TEAM." AS UT", 'UT.user_team_id = UC.user_team_id','LEFT')
		->join(MASTER_GROUP." AS MG", 'MG.group_id = G.group_id','LEFT')
		->where('G.status','3');

		if (isset($post_data['csv']) && $post_data['csv'] == true) 	
		{
			$tz_diff = get_tz_diff($this->app_config['timezone']);
	
			$this->db->select("CONVERT_TZ(G.scheduled_date, '+00:00', '".$tz_diff."') AS scheduled_date");
		}else{
			$this->db->select("G.scheduled_date");
		}
		

		$game_type = isset($post_data['game_type'])?$post_data['game_type']:"";

		if(isset($post_data['sports_id']) && $post_data['sports_id'] != '')
		{
			$this->db->where('G.sports_id',$post_data['sports_id']);
		}
		if(isset($post_data['league_id']) && $post_data['league_id'] != '')
		{
			$this->db->where('G.league_id',$post_data['league_id']);
		}
		if(isset($post_data['contest_name']))
		{
			$this->db->like('G.contest_name',$post_data['contest_name']);
		}
		if(isset($post_data['group_id']))
		{
		$this->db->like('G.group_id',$post_data['group_id']);
		}
		if(isset($post_data['season_id']) && $post_data['season_id']!="")
		{
			$this->db->where('G.season_id',$post_data['season_id']);
		}

		$this->db->group_by('G.contest_unique_id');

	    if(!empty($post_data['from_date'])&&!empty($post_data['to_date']))
			$this->db->where("DATE_FORMAT(G.scheduled_date,'%Y-%m-%d') >= '".format_date($post_data['from_date'],'Y-m-d')."' and DATE_FORMAT(G.scheduled_date,'%Y-%m-%d') <= '".format_date($post_data['to_date'],'Y-m-d')."'");
		
		$tempdb = clone $this->db;
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows(); 

		//echo $temp_q->last_query(); die;

		if(!empty($sort_field) && !empty($sort_order))
		{
			$this->db->order_by($sort_field, $sort_order);
		}

		if(!empty($limit) && !$post_data["csv"])
		{
			$this->db->limit($limit, $offset);
		}
		$sql = $this->db->get();
		$result	= $sql->result_array();

		foreach($result as $key=>$contest)
		{
			$group = $this->db->select('group_name')->from(MASTER_GROUP)->where('group_id',$contest['group_id'])->get()->row_array();
			$result[$key]['group_name'] = $group['group_name'];
		}
		// echo $this->db_fantasy->last_query();die;
		return array('result'=>$result, 'total'=>$total);
	}

	
	/**
	*@method delete_contest
	*@uses function to delete contest
	****/
	public function delete_contest($data_arr)
	{
		$this->db->where("contest_id",$data_arr['contest_id']);
		$this->db->where("total_user_joined","0");
		$this->db->delete(CONTEST);
		$is_deleted = $this->db->affected_rows();
		return $is_deleted;
	}


}
