<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_user		= $this->load->database('user_db', TRUE);

		
	}

    /**
	 * [get_report_money_paid_by_user description]
	 * @MethodName get_report_money_paid_by_user
	 * @Summary This function used for get all money paid by users
	 * @param      boolean  [User List or Return Only Count]
	 * @return     [type]
	 */
	public function get_report_money_paid_by_user($count_only=FALSE)
	{
		$sort_field	= 'U.added_date';
		$sort_order	= 'DESC';
		$limit		= 10;
		$page		= 0;
		$post_data = $this->input->post();

		if($post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if($post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
		}

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('added_date','first_name','user_name','email','country','login_date_time','login_count','bouns_money_paid','real_money_paid','bonus_balance','balance','status','coins_paid','point_balance')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
                
		if($this->input->post('csv'))
		{
			$select_field = "U.user_unique_id, U.user_id,IFNULL(U.user_name,'-') AS user_name, CONCAT_WS(' ',IFNULL(U.first_name,'-'),U.last_name) AS name,IFNULL(U.phone_no,'-') AS phone_no,IFNULL(U.email,'-') AS email,TRUNCATE(sum(O.points),2) as coins_paid,U.point_balance,TRUNCATE(sum(O.real_amount),2) as real_money_paid, U.balance, TRUNCATE(sum(O.bonus_amount),2) as bouns_money_paid,U.bonus_balance, DATE_FORMAT(U.added_date,'".MYSQL_DATE_FORMAT."') AS member_since";
		}
		else
		{
			$select_field = "U.user_unique_id, U.user_id,IFNULL(U.user_name,'-') AS user_name, CONCAT_WS(' ',IFNULL(U.first_name,'-'),U.last_name) AS name,IFNULL(U.phone_no,'-') AS phone_no,IFNULL(U.email,'-') AS email,IFNULL(U.image,'default_user.png') AS image, U.added_date , U.balance,U.bonus_balance,U.point_balance,TRUNCATE(sum(O.real_amount),2) as real_money_paid, TRUNCATE(sum(O.bonus_amount),2) as bouns_money_paid,TRUNCATE(sum(O.points),2) as coins_paid, O.status";
		}

		$payment_sql = $this->db_user->select($select_field,FALSE)
							->from(USER.' AS U')
							->join(ORDER.' AS O','O.user_id = U.user_id  ','LEFT')
							->where(array("O.type"=>1 , "O.source"=>500 , "O.status"=>1))
							->group_by("U.user_id")
							->order_by($sort_field, $sort_order);

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{	
			//IFNULL(email,""),
			$this->db_user->like('CONCAT(IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.email,""),IFNULL(U.user_name,""),CONCAT_WS(" ",U.first_name,U.last_name))', $post_data['keyword']);
		}

		$tempdb = clone $this->db_user;
		$total = 0;
		
		
		if(!$this->input->post('csv'))
		{
			$query = $this->db_user->get();
			$total = $query->num_rows();
			$tempdb->limit($limit,$offset);
		}

		
		
		$sql = $tempdb->get();
		$payment_result	= $sql->result_array();
		// echo $this->db_user->last_query();exit;		
		return array('result'=>$payment_result,'total'=>$total);
	}


	/**
	 * [contest_list description]
	 * @MethodName contest_list
	 * @Summary This function used for get all contest List
	 * @return     [array]
	 */
	public function get_completed_contest_report($post_params)
	{ 
		$sort_field = 'C.season_scheduled_date';
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

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','collection_name','size','entry_fee','season_scheduled_date','prize_pool','guaranteed_prize','total_user_joined','minimum_size','site_rake')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		
		$this->db->select("C.currency_type,CONCAT(CM.collection_name,' Inning ',SUBSTR(CM.inn_over,1,1),' Over ',SUBSTR(CM.inn_over,3,1)) collection_name,C.season_scheduled_date,C.contest_id,C.contest_unique_id, C.contest_name, C.entry_fee,C.site_rake,C.group_id,C.minimum_size,C.size,C.total_user_joined,
		C.max_bonus_allowed,C.prize_pool, C.entry_fee*C.total_user_joined as total_entry_fee",false)
		->from(CONTEST." AS C")
		->join(COLLECTION." AS CM","CM.collection_id = C.collection_id","LEFT")
		->join(USER_CONTEST." AS UC", 'UC.contest_id = C.contest_id','LEFT')
		->join(USER_TEAM." AS UT", 'UT.user_team_id = UC.user_team_id','LEFT')
		->where('C.status','3');

		if(isset($post_data['sports_id']) && $post_data['sports_id'] != '')
		{
			$this->db->where('C.sports_id',$post_data['sports_id']);
		}
		if(isset($post_data['league_id']) && $post_data['league_id'] != '')
		{
			$this->db->where('C.league_id',$post_data['league_id']);
		}
		if(isset($post_data['contest_name']))
		{
			$this->db->like('C.contest_name',$post_data['contest_name']);
		}
		if(isset($post_data['group_id']))
		{
		$this->db->like('C.group_id',$post_data['group_id']);
		}
		if(isset($post_data['collection_id']) && $post_data['collection_id']!="")
		{
			$this->db->where('C.collection_id',$post_data['collection_id']);
		}

		$this->db->group_by('C.contest_unique_id');

	    if(!empty($post_data['from_date'])&&!empty($post_data['to_date']))
		{
			$this->db->where("DATE_FORMAT(C.season_scheduled_date,'%Y-%m-%d') >= '".format_date($post_data['from_date'],'Y-m-d')."' and DATE_FORMAT(C.season_scheduled_date,'%Y-%m-%d') <= '".format_date($post_data['to_date'],'Y-m-d')."'");
		}
		
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
		// echo $this->db->last_query();die;
		return array('result'=>$result, 'total'=>$total);
	}

	public function get_contest_prize_detail($contest_ids)
	{

		$query = $this->db_user->select("(ROUND(IFNULL(sum(IF(ORD.source=500,ORD.real_amount,0)),'0'),2)+ROUND(IFNULL(sum(IF(ORD.source=500,ORD.winning_amount,0)),'0'),2)) as total_join_real_amount,			

		ROUND(IFNULL(sum(IF(ORD.source=500,ORD.bonus_amount,0)),'0'),2) as total_join_bonus_amount,			

		ROUND(IFNULL(sum(IF(ORD.source=500,ORD.points,0)),'0'),2) as total_join_coin_amount,
		ROUND(IFNULL(sum(IF(ORD.source=500,ORD.winning_amount,0)),'0'),2) as total_join_winning_amount,

		ROUND(IFNULL(sum(IF(ORD.source=502,ORD.winning_amount,0)),'0'),2) as total_win_winning_amount,
		ROUND(IFNULL(sum(IF(ORD.source=502,ORD.points,0)),'0'),2) as total_win_coins,
		ROUND(IFNULL(sum(IF(ORD.source=502,ORD.bonus_amount,0)),'0'),2) as total_win_bonus,
		ROUND(IFNULL(sum(IF(ORD.source=502 AND U.is_systemuser=0,ORD.winning_amount,0)),'0'),2) as total_win_amount_to_real_user,


			ORD.reference_id as contest_id",FALSE)
				->from(ORDER." AS ORD")
				->join(USER.' U','U.user_id=ORD.user_id',"INNER")
				->where('(ORD.source = 500 or ORD.source = 502) AND ORD.status = 1') // join game  AND Debit and success		
				->where_in('ORD.reference_id',$contest_ids)
				//->where('U.is_systemuser',0)		
				->group_by('ORD.reference_id')
				->get()->result_array();
		// echo $this->db_user->last_query();exit;
		return $query;					
	}

	public function get_contest_promo_code_entry($contest_uids)
	{
		$query = $this->db_user->select("ROUND(IFNULL(sum(amount_received),'0'),2) as promocode_entry_fee_real,contest_unique_id",FALSE)
				->from(PROMO_CODE_EARNING)
				->where_in('contest_unique_id',$contest_uids)		
				->group_by('contest_unique_id')
				->get()->result_array();
		// echo $this->db->last_query();exit;
		return $query;					
	}

	/**
	 * [description]
	 * @MethodName get_sport_leagues
	 * @Summary This function used for get all league for selected sport
	 * @param      na
	 * @return     [array]
	 */
	public function get_sport_all_recent_leagues()
	{
		$sort_field	= 'league_abbr';
		$sort_order	= 'ASC';
               
		$post_data = $this->input->post();

		$Current_DateTime = format_date();
		//L.image,show_global_leaderboard,max_player_per_team,L.is_promote
		$this->db->select('L.league_uid,L.league_id, L.league_name, L.league_abbr,L.active AS status,league_schedule_date,L.sports_id')
						->from(LEAGUE." AS L")
						->join(MASTER_SPORTS . " AS MS", "MS.sports_id = L.sports_id", 'INNER')
						->join(SEASON . " AS S", "L.league_id = S.league_id", 'left');
						
						if(!isset($post_data['admin_list_filter']))
						{
							$this->db->where('S.season_scheduled_date >',$Current_DateTime);//will be uncomment in case or real data;
						}

						$this->db->where('MS.active', '1');
						$this->db->where('L.active', '1');
						// $this->db->where('L.archive_status', '0');
        if(!empty($post_data['sports_id']) || isset($post_data['sports_id']))
        {
            $this->db->where('L.sports_id', $post_data['sports_id']);
        }

        if(!empty($post_data['league_ids']) || isset($post_data['league_ids']))
        {
            $this->db->where_in('L.league_id', $post_data['league_ids']);
        }

        if(!empty($post_data['not_league']) || isset($post_data['not_league']))
        {
            $this->db->where_not_in('L.league_id', $post_data['not_league']);
        }

        if(isset($post_data['active']))
        {
            $this->db->where('L.active', $post_data['active']);
        }
						
		$sql = $this->db->order_by($sort_field, $sort_order);
		$sql = $this->db->group_by("L.league_id");

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

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('league_abbr','league_uid','L.active','league_schedule_date', 'league_last_date','max_player_per_team','L.is_promote')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		
		$this->db->select('IFNULL(league_display_name,league_name) AS league_name, IFNULL(league_display_name,league_name) AS league_abbr,season_scheduled_date,L.league_id,COUNT(season_id) as total_count,is_published')
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
                // remove 6 month old league from list // to enable check, uncomment condition
                //$this->db->where("DATE_FORMAT ( L.league_last_date ,'%Y-%m-%d' ) >= DATE_SUB('" . $Current_Date . "' , INTERVAL 6 MONTH)");

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

	/** 
     * common function used to get group list
     * @param array $data
     * @return	array
     */
	public function get_all_group_list($data = array())
	{
		$is_private = "";
		if(isset($data['list_type']) && $data['list_type'] != ""){
			$is_private = $data['list_type'];
		}

		$sql = $this->db->select('*')
						->from(MASTER_GROUP)
						->where('status','1')
						->order_by('sort_order','ASC');
		if(isset($is_private) && $is_private != ""){
			$sql->where_in('is_private',[0,$is_private]);
		}else{
			$sql->where('is_private','0');
		}

		//check if rookie OFF or ON
		// $allow_rookie_contest= isset($this->app_config['allow_rookie_contest'])?$this->app_config['allow_rookie_contest']['key_value']:0;
		// if(!$allow_rookie_contest)
		// {
		// 	$rookie_group_id = $this->app_config['allow_rookie_contest']['custom_data']['group_id'];
		// 	$this->db->where('group_id<>',$rookie_group_id);
		// }


		$sql = $sql->get();
		$result = $sql->result_array();
		return ($result) ? $result : array();
	}

	/**
	*@method get_collections_by_filter
	*@uses function to get collection list by league uid with pagination
	****/
	public function get_collections_by_filter($post_data)
	{
		
	    $this->db->select("CONCAT(CM.collection_name,' Inning ',SUBSTR(CM.inn_over,1,1),' Over ',SUBSTR(CM.inn_over,3,1)) collection_name,CM.collection_id,DATE_FORMAT(CM.season_scheduled_date, '%d-%b-%Y %H:%i') AS season_schedule_date",FALSE)
								->from(COLLECTION.' AS CM')
								->join(CONTEST." AS C","C.collection_id = CM.collection_id","INNER")
								->where('CM.status','2')
								->where('C.status >','1');
		
		if($this->input->post("league_id"))
		{
			$this->db->where("CM.league_id",$this->input->post("league_id"));
		}

		if($this->input->post("sports_id"))
		{
			$this->db->where("C.sports_id",$this->input->post("sports_id"));
		}					

		$collection_data = $this->db->order_by('CM.season_scheduled_date','ASC')
								->group_by("CM.collection_id")
								->get()->result_array();
		// die($this->db->last_query());
		return $collection_data;
	}	


}