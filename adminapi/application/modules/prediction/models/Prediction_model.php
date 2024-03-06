<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Prediction_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_prediction		= $this->load->database('db_prediction', TRUE);
		$this->db_user		= $this->load->database('db_user', TRUE);
		$this->db_fantasy		= $this->load->database('db_fantasy', TRUE);
		
	}

	function get_match_details($season_game_uid,$sports_id='')
	{
		$this->db_fantasy->select('S.season_game_uid,S.season_scheduled_date,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away',FALSE)
		->from(SEASON." as S")
		->join(TEAM.' T1','T1.team_id = S.home_id','INNER')
		->join(TEAM.' T2','T2.team_id = S.away_id','INNER')
		->where('S.season_game_uid',$season_game_uid);
        if(isset($sports_id) && $sports_id != ""){
            $this->db_fantasy->join(LEAGUE." as L","L.league_id=S.league_id");
            $this->db_fantasy->where("L.sports_id",$sports_id);
        }
		$result = $this->db_fantasy->get()->row_array();
		return $result;	
	}

	/*
	*@method get_lobby_season_matches
	*@uses function to get upcoming matches of one month,include last 3 days from current date
	******/
	public function get_season_matches_by_ids($post_data)
	{	
		$this->load->helper('cron_helper');
	    $current_date_time = format_date();
		$this->db_fantasy->select("S.home_id,S.api_week, S.week, S.season_game_uid, S.league_id, S.season_scheduled_date, S.format, L.sports_id,IFNULL(L.league_display_name,L.league_name) AS league_abbr,IFNULL(L.league_display_name,L.league_name) AS league_name,IFNULL(L.image,'') as image,(CASE WHEN format = 1 THEN 'ONE-DAY' WHEN format = 2 THEN 'TEST' WHEN format = 3 THEN 'T20' WHEN format = 4 THEN 'T10' ELSE '' END) AS format_str,T1.team_id as home_id,S.status,S.status_overview,score_data,S.is_tour_game",false);
		
		$this->db_fantasy->from(SEASON . " AS S")
				->join(LEAGUE . " AS L", "L.league_id = S.league_id", "INNER");
		if(!in_array($post_data['sports_id'],$this->tour_game_sports))
		{
			$this->db_fantasy->select("IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,T1.team_id as home_id,IFNULL(T1.flag,T1.feed_flag) AS home_flag,T2.team_id as away_id,IFNULL(T2.flag,T2.feed_flag) AS away_flag,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away",false);
			$this->db_fantasy->join(TEAM.' T1','T1.team_id=S.home_id');
			$this->db_fantasy->join(TEAM.' T2','T2.team_id=S.away_id');
		}
		$this->db_fantasy->where("L.active",1);
		if(!empty($post_data['sports_id']))
		{
			$this->db_fantasy->where("L.sports_id", $post_data['sports_id']);
		}

		if(!empty($post_data['season_game_uids']))
		{	 
			$this->db_fantasy->where_in("S.season_game_uid ", $post_data['season_game_uids']);
		}

		if(!empty($post_data['q']) &&  $post_data['search'] == TRUE)
		{
			$q = $post_data['q'];
			 $this->db_fantasy->group_start();
			 $this->db_fantasy->like("T1.team_name",$q,"both");
			 $this->db_fantasy->or_like("T2.team_name",$q,"both");
			 $this->db_fantasy->or_like("T1.team_abbr",$q,"both");
			 $this->db_fantasy->or_like("T2.team_abbr",$q,"both");
			 $this->db_fantasy->group_end();
		}

		if(!empty($post_data['team_uid']))
		{
			$this->db_fantasy->where("(home_uid IN ('".$post_data['team_uid']."') OR away_uid IN ('".$post_data['team_uid']."'))");
		}

		$this->db_fantasy->group_by("S.season_game_uid");
		$this->db_fantasy->order_by("S.season_scheduled_date","ASC");
		$sql = $this->db_fantasy->get();
		$matches = $sql->result_array();
		foreach ($matches as $key => $rs) {
			
			$matches[$key]["game_starts_in"] = strtotime($matches[$key]["season_scheduled_date"])*1000;
			$matches[$key]["today"] = strtotime(format_date())*1000;
			$matches[$key]["current_timestamp"] = strtotime(format_date())*1000;
			$matches[$key]["scheduled_timestamp"] = strtotime($matches[$key]["season_scheduled_date"])*1000;
			//$matches[$key]["image"] = get_image(2,$matches[$key]["image"]);
			//$matches[$key]['home_flag'] = get_image(0,$matches[$key]['home_flag']);
            //$matches[$key]['away_flag'] = get_image(0,$matches[$key]['away_flag']);
		}
		return $matches;
	}

	function check_match_live($season_game_uid)
	{
		$current_date = format_date();
		return $this->db_fantasy->select('season_game_uid,home,away')
		->from(SEASON)
		->where('season_game_uid',$season_game_uid)
		->where('season_scheduled_date<',$current_date)
		->where('status',0)
		->get()->row_array();	
	}

	function get_all_user_data()
	{
		$result = $this->db_user->select('U.user_id,U.user_name,GROUP_CONCAT(AL.device_id) as device_ids,U.email',false)
		->from(USER.' U')
		->join(ACTIVE_LOGIN.' AL','AL.user_id=U.user_id')
		->where('status',1)
		->group_by('U.user_id')	
		->get()->result_array();

		return $result;
	}


	function get_match_prediction_count($season_game_uid)
	{
		$result = $this->db_prediction->select('COUNT(prediction_master_id) as count')
		->from(PREDICTION_MASTER)
		->where('season_game_uid',$season_game_uid)->get()->row_array();

		if(isset($result['count']))
		{
			return $result['count'];
		}

		return 0;
		
	
	}

	function get_prediction_details($prediction_master_id)
	{
		$current_date = format_date();
		$result =  $this->db_prediction->select('PM.*')
		->from(PREDICTION_MASTER.' PM' )
		->where('prediction_master_id',$prediction_master_id)
		->get()->row_array();

		$result['option'] =$this->db_prediction->select('PO.*,
		0 as prediction_count,"" as user_id')
		->from(PREDICTION_OPTION.' PO' )
		->where('PO.prediction_master_id',$prediction_master_id)
		->group_by('PO.prediction_option_id')->get()->result_array();

		$result['deadline_time'] = strtotime($result['deadline_date'])*1000000 ;
		$result['today'] = strtotime($current_date)*1000000 ;

		return $result;	
	}


	public function get_prediction_answer($prediction_master_id)
	{
		return  $this->db_prediction->select('PO.prediction_option_id,PO.is_correct,PM.is_prediction_feed')
		->from(PREDICTION_MASTER.' PM')
		->join(PREDICTION_OPTION.' PO','PM.prediction_master_id=PO.prediction_master_id')
		->where('PM.prediction_master_id',$prediction_master_id)
		->get()->row_array();
	}

	public function update_prediction_results($prediction_option_id)
	{

		$this->db_prediction->where('prediction_option_id',$prediction_option_id)
       ->update(PREDICTION_OPTION,array('is_correct'=> 1));
		return true;	
	}

	public function update_prediction_result_status($prediction_master_id,$update_data)
	{
		$this->db_prediction->where('prediction_master_id',$prediction_master_id)
       ->update(PREDICTION_MASTER,$update_data);
		//$this->db_prediction->update_batch(PREDICTION_MASTER, $prediction_status_data, 'prediction_master_id');
		return true;	
	}

	function get_one_prediction($prediction_master_id)
	{
		return $this->db_prediction->select('*')
		->from(PREDICTION_MASTER)
		->where('prediction_master_id',$prediction_master_id)->get()->row_array();
	}

	function update_prediction_status($status)
    {
       $this->db_user->where('name','allow_prediction')
	   ->update(MODULE_SETTING,array('status'=> $status));
	   
	   $this->db_user->where('game_key','allow_prediction')
       ->update(SPORTS_HUB,array('status'=> $status));
       return $this->db_user->affected_rows();    
	}

	function update_pin_prediction($status,$prediction_master_id)
    {
       $this->db_prediction->where('prediction_master_id',$prediction_master_id)
       ->update(PREDICTION_MASTER,array('is_pin'=> $status));
       return $this->db_prediction->affected_rows();    
	}
	
	function pause_play_prediction($pause,$prediction_master_id)
    {
		if($pause)
		{
			$this->db_prediction->where('prediction_master_id',$prediction_master_id)
			->update(PREDICTION_MASTER,array('status'=> 3));//pause
		}
		else
		{
			$this->db_prediction->where('prediction_master_id',$prediction_master_id)
			->update(PREDICTION_MASTER,array('status'=> 0));//pause
		
		}
       
       return $this->db_prediction->affected_rows();    
	}
	
	function delete_prediction($prediction_master_id)
	{
		$this->db_prediction->where('prediction_master_id',$prediction_master_id)
			->update(PREDICTION_MASTER,array('status'=> 4));//delete
		return $this->db_prediction->affected_rows(); 
	}
	
	/**
	* @method get_all_season_schedule description
	* @uses This function used for get all season schedule
	* @param count
	*/
	
	public function get_all_season_schedule($filter=array())
	{
		$sort_field	= 'S.season_scheduled_date';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;

		$current_date = format_date();

		$post_data = $this->input->post();

		if(isset($post_data['items_perpage']) && $post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']) && ($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && ($post_data['sort_field']) && in_array($post_data['sort_field'],array('year','type','season_scheduled_date','home','away','status','api_week')))
		{
			$sort_field = 'S.'.$this->input->post('sort_field');
		}

		if(isset($post_data['sort_order']) && ($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;

		$league_id	= isset($post_data['league_id']) ? $post_data['league_id'] : "";
		$sports_id	= isset($post_data['sports_id']) ? $post_data['sports_id'] : "";
		$team_id	= isset($post_data['team_id']) ? $post_data['team_id'] : "";
		$week		= isset($post_data['week']) ? $post_data['week'] : "";
		$fromdate	= isset($post_data['fromdate']) ? $post_data['fromdate'] : "";
		$todate		= isset($post_data['todate']) ? $post_data['todate'] : "";
		$status		= isset($post_data['status']) ? $post_data['status'] : "";

	
		$this->db_fantasy->select("S.season_id,S.league_id,S.season_game_uid,S.subtitle,S.format,S.type,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,S.status,S.season_scheduled_date, S.is_published,IFNULL(T1.flag,T1.feed_flag) AS home_flag,IFNULL(T2.flag,T2.feed_flag) AS away_flag,S.format,IFNULL(L.league_display_name,L.league_name) AS league_abbr,S.is_salary_changed,S.is_published,S.is_tour_game,(CASE WHEN format = 1 THEN 'ONE-DAY' WHEN format = 2 THEN 'TEST' WHEN format = 3 THEN 'T20' WHEN format = 4 THEN 'T10' END) AS format_str",false)
			->from(SEASON." AS S")
			->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
			->join(TEAM.' T1','T1.team_id=S.home_id','LEFT')
			->join(TEAM.' T2','T2.team_id=S.away_id','left');
			
		if($sports_id != "")
		{
			$this->db_fantasy->where("L.sports_id", "$sports_id");
		}

		if($league_id != "")
		{
			$this->db_fantasy->where("S.league_id", "$league_id");
		}

		if($team_id != "" && $team_id != 'all')
		{
			$this->db_fantasy->where("(T1.team_id = '".$team_id."' or T2.team_id  = '".$team_id."')");
		}

		if($week != "" && $week!='all')
		{
			$this->db_fantasy->where("week", "$week");
		}
		if($status != "")
		{
			if($status == 'not_complete'){
				$this->db_fantasy->where("S.status !=", 2);
			}
			else{
				$this->db_fantasy->where("S.status", $status);
			}
		}

		if($fromdate != "" && $todate != "")
		{
			$this->db_fantasy->where("DATE_FORMAT(S.season_scheduled_date,'%Y-%m-%d') >= '".$fromdate."' and DATE_FORMAT(S.season_scheduled_date,'%Y-%m-%d') <= '".$todate."'");
		}

		if(isset($filter['match_type']) && $filter['match_type']==1)//live
		{
			$this->db_fantasy->where('S.season_scheduled_date<',$current_date);
			$this->db_fantasy->where("S.season_scheduled_date>DATE_SUB('{$current_date}',INTERVAL 10 DAY)")
			->where_in('S.status',array(0,1));
		}

		if(isset($filter['match_type']) && $filter['match_type']==2)//Upcoming
		{
			$this->db_fantasy->where('S.season_scheduled_date>',$current_date)
			->where('S.status',0);
		}

		if(isset($filter['match_type']) && $filter['match_type']==3)//completed
		{
				$this->db_fantasy->where('S.season_scheduled_date<',$current_date)
				->where('S.status',2);
		}

		$tempdb_fantasy = clone $this->db_fantasy;
		$this->db_fantasy->group_by('S.season_game_uid');
		$query  = $this->db_fantasy->get();

		$total  = $query->num_rows();

		   $tempdb_fantasy->group_by('S.season_game_uid');

		   if(isset($filter['match_type']) && $filter['match_type']==2)//Upcoming
		   {
				$tempdb_fantasy->order_by($sort_field, "ASC"); 
		   }   
		   else
		   {
				$tempdb_fantasy->order_by($sort_field, $sort_order);
		   }


			
			$sql =	$tempdb_fantasy->limit($limit,$offset)
			->get();

		$result	= $sql->result_array();
		//echo $tempdb_fantasy->last_query(); die;
		$result = ($result) ? $result : array();
		return array('result'=>$result,'total'=>$total);
	}

	public function add_prediction($prediction_data)
	{
		$this->db_prediction->insert(PREDICTION_MASTER,$prediction_data);
		return $this->db_prediction->insert_id();
	}

	public function insert_prediction_option($option_data)
	{
		$this->db_prediction->insert_batch(PREDICTION_OPTION,$option_data);
		return true;
	}

	public function update_prediction($prediction_master_id,$prediction_data)
	{
		$this->db_prediction->where('prediction_master_id',$prediction_master_id);
		$this->db_prediction->update(PREDICTION_MASTER,$prediction_data);
		return $this->db_prediction->affected_rows();
	}

	public function update_prediction_option($prediction_master_id,$option_data)
	{
		$this->db_prediction->where('prediction_master_id', $prediction_master_id)
		->delete(PREDICTION_OPTION);
		$this->db_prediction->insert_batch(PREDICTION_OPTION,$option_data);
		return true;
	}

	public function get_season_question_count($sports_id='7',$season_game_uids)
	{
		$current_date=format_date();
		$pre_sql="(SELECT prediction_master_id ,IF(deadline_date<'{$current_date}' AND total_user_joined=0, 0, 1) as valid_prediction FROM  ".$this->db_prediction->dbprefix(PREDICTION_MASTER).")";

		$result = $this->db_prediction->select('COUNT(PM.season_game_uid) as question_count,PM.season_game_uid',FALSE)
		->from(PREDICTION_MASTER.' PM')
		->join($pre_sql." COMMON","COMMON.prediction_master_id=PM.prediction_master_id")
		->where('PM.sports_id',$sports_id)
		->where_in('PM.status',array(0,1,2,3))
		->where_in('PM.season_game_uid',$season_game_uids)
		->where("COMMON.valid_prediction",1)
		->group_by('PM.season_game_uid')->get()->result_array();
		return $result;
	}

	/**
	 * 
	 * @method get_one_prediction_details
	 * @uses This function used for get one prediction details
	 * @param      na
	 * @return     [array]
	 */
	function get_one_prediction_details($prediction_master_id)
	{
		$default_selected_columns = "prediction_master_id,season_game_uid,`desc`,season_game_uid,DATE_FORMAT(deadline_date, '".MYSQL_DATE_TIME_FORMAT."') as deadline_date,status,total_user_joined,site_rake,IFNULL(prize_pool,0) AS prize_pool,total_pool,is_pin,sports_id";
		$sql = $this->db_prediction->select($default_selected_columns,FALSE)
		->from(PREDICTION_MASTER)
		->where('prediction_master_id',$prediction_master_id);

		$query  = $this->db_prediction->get();
		$result = $query->result_array();

		if(!empty($result))
		{
			foreach ($result as $key => $value)
			{
				$selected_option_id      = "";
				$prediction_master_id    = $value['prediction_master_id'];
				$result[$key]['options'] = $this->get_prediction_options($prediction_master_id);
				if(!empty($result[$key]['options']))
				{
					foreach ($result[$key]['options'] as $okey => $ovalue)
					{
					    if(!empty($ovalue['is_correct']))
					    {
					    	$selected_option_id = $ovalue['prediction_option_id'];
					    }		
					}
				}	

				$result[$key]['selected_option_id'] = $selected_option_id;
			}
		}	

		return $result;
	}


	/**
	 * 
	 * @method get_all_prediction
	 * @uses This function used for get predictions
	 * @param      na
	 * @return     [array]
	 */
	public function get_all_prediction()
	{
		$sort_field = 'deadline_date';
		$sort_order = 'ASC';
		

		$post_data  = $this->input->post();
	
		if(isset($post_data['items_perpage']) && $post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']) && $post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('team_name','team_abbr','sports_name','association_name')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$current_date=format_date();
		$pre_sql="(SELECT prediction_master_id ,IF(deadline_date<'{$current_date}' AND total_user_joined=0, 0, 1) as valid_prediction FROM  ".$this->db_prediction->dbprefix(PREDICTION_MASTER).")";

		$default_selected_columns = "PM.prediction_master_id,PM.desc,PM.season_game_uid,DATE_FORMAT(PM.deadline_date, '".MYSQL_DATE_TIME_FORMAT."') as deadline_date,PM.status,PM.total_user_joined,PM.site_rake,IFNULL(PM.prize_pool,0) AS prize_pool,PM.total_pool,PM.is_pin,COMMON.valid_prediction,PM.is_prediction_feed";
		
		$select  = (isset($post_data['select'])) ? $post_data['select'] : $default_selected_columns;
		
      
		$sql = $this->db_prediction->select($select,FALSE)
						->from(PREDICTION_MASTER." PM")
						->join($pre_sql." COMMON","COMMON.prediction_master_id=PM.prediction_master_id")
						->order_by($sort_field, $sort_order);

		if(isset($post_data['season_game_uid']) && $post_data['season_game_uid']!="")
		{
			$this->db_prediction->where("PM.season_game_uid",$post_data['season_game_uid']);
		}

		if(isset($post_data['status']) && in_array($post_data['status'],array(0,1)))
		{
			if($post_data['status'] == 0)
			{
				$this->db_prediction->where_in("PM.status",array(0,3));
				$this->db_prediction->where("COMMON.valid_prediction",1);
			}
			else
			{
				$this->db_prediction->where("PM.status >",0);
			}	
		}

		if(isset($post_data['status']) && in_array($post_data['status'],array(2,4)))//for completed deleted
		{
			$this->db_prediction->where_in("PM.status ",array(2,4));
		}


		if($post_data['is_prediction_feed'] ==1){
			$this->db_prediction->where("PM.is_prediction_feed",1);
		}elseif($post_data['is_prediction_feed'] == 0 && $post_data['is_prediction_feed'] != '' ){
			$this->db_prediction->where("PM.is_prediction_feed",0);
		
		}


		$tempdb = clone $this->db_prediction;
		$query  = $this->db_prediction->get();
		$total  = $query->num_rows();

		$offset = 0;
        if(isset($limit) && isset($page))
        {
            $offset	= $limit * $page;
            $tempdb->limit($limit,$offset);
        }

		$sql = $tempdb->order_by('PM.is_pin','DESC')
		->order_by('PM.added_date','DESC')
		->get();
		$result = $sql->result_array();
		//echo $this->db_prediction->last_query();die();
		//fetch options for predictions
		if(!empty($result))
		{
			foreach ($result as $key => $value)
			{
				$selected_option_id      = "";
				$prediction_master_id    = $value['prediction_master_id'];
				$result[$key]['options'] = $this->get_prediction_options($prediction_master_id);
				if(!empty($result[$key]['options']))
				{
					foreach ($result[$key]['options'] as $okey => $ovalue)
					{
					    if(!empty($ovalue['is_correct']))
					    {
					    	$selected_option_id = $ovalue['prediction_option_id'];
					    }		
					}
				}	

				$result[$key]['selected_option_id'] = $selected_option_id;
			}
		}	

		$next_offset = count($result) + $offset;

		//echo $this->db->last_query();die();
		//  }//foreach
		return array('result'=>$result,'total'=>$total, 'next_offset' => $next_offset );
	}

	public function get_prediction_options($prediction_master_id)
	{

		$optionSql = $this->db_prediction->select("PO.prediction_master_id,PO.prediction_option_id,PO.`option`,PO.is_correct,COUNT(UP.user_id) as prediction_count,SUM(IFNULL(UP.bet_coins,0)) as option_total_coins",FALSE)
						->from(PREDICTION_OPTION.' PO')
						->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id",'LEFT')
						->where("PO.prediction_master_id",$prediction_master_id)
						->group_by('PO.prediction_option_id')
						->order_by("PO.prediction_option_id","ASC")
						->get();

						//echo $this->db_prediction->last_query();die;
		return ($optionSql->num_rows() > 0) ? $optionSql->result_array() : array();				

	}

	public function get_prediction_participants($prediction_master_id)
	{

		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

		$page = 0;
		if(isset($post['current_page'])) {
			$page = $post['current_page']-1;
		}
		

         $offset	= $limit * $page;


		$pre_query ="(SELECT SUM(UP.bet_coins) as option_total,GROUP_CONCAT(UP.user_id) as user_ids,PO.prediction_option_id
FROM ".$this->db_prediction->dbprefix(PREDICTION_MASTER)." PM 
INNER JOIN ".$this->db_prediction->dbprefix(PREDICTION_OPTION)." PO ON PM.prediction_master_id=PO.prediction_master_id 
INNER JOIN ".$this->db_prediction->dbprefix(USER_PREDICTION)." UP ON UP.prediction_option_id=PO.prediction_option_id  
GROUP BY PO.prediction_option_id)";


		 $this->db_prediction->select("PM.prediction_master_id,PM.prize_pool,UP.bet_coins,PM.total_pool,
		 CAST((PM.prize_pool*(UP.bet_coins/TR.option_total)) as UNSIGNED) as estimated_winning,UP.user_id,PO.`option`,UP.win_coins",FALSE)
		->from(PREDICTION_MASTER.' PM')
		->join(PREDICTION_OPTION.' PO',"PO.prediction_master_id=PM.prediction_master_id")
		->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
		->join($pre_query.' TR',"TR.prediction_option_id=UP.prediction_option_id")
		->where("PO.prediction_master_id",$prediction_master_id)
		->group_by('UP.user_id')
		->order_by("PO.prediction_option_id","ASC");

		$tempdb = clone $this->db_prediction; //to get rows for pagination
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows();
		
		$sql = $this->db_prediction->limit($limit,$offset)->get();
		$prediction_data	= $sql->result_array();

		$next_offset = count($prediction_data) + $offset;

		return array('total' => $total,
		 'prediction_participants' => $prediction_data,
		 'next_offset' => $next_offset 	
		);
	}

	public function get_trending_prediction()
	{
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

		$page = 0;
		if(isset($post['current_page'])) {
			$page = $post['current_page']-1;
		}
		
		$current_date=format_date();
		$pre_sql="(SELECT prediction_master_id ,IF(deadline_date<'{$current_date}' AND total_user_joined=0, 0, 1) as valid_prediction FROM  ".$this->db_prediction->dbprefix(PREDICTION_MASTER).")";

		 $default_selected_columns = "PM.prediction_master_id,PM.desc,PM.season_game_uid,DATE_FORMAT(PM.deadline_date, '".MYSQL_DATE_TIME_FORMAT."') as deadline_date,PM.status,PM.total_user_joined,PM.site_rake,IFNULL(PM.prize_pool,0) AS prize_pool,PM.total_pool,PM.is_pin,COMMON.valid_prediction";
		
		 $sql = $this->db_prediction->select($default_selected_columns,FALSE)
						 ->from(PREDICTION_MASTER." PM")
						 ->join($pre_sql." COMMON","COMMON.prediction_master_id=PM.prediction_master_id")
						 ->where('PM.status',0)
						 ->where("COMMON.valid_prediction",1);
						 

		$tempdb = clone $this->db_prediction;
		$query  = $this->db_prediction->get();
		$total  = $query->num_rows();

		$offset = 0;
        if(isset($limit) && isset($page))
        {
            $offset	= $limit * $page;
            $tempdb->limit($limit,$offset);
        }

		$this->config->load('prediction_config');
		$trending_types = $this->config->item('trending_prediction');
		
		if($post['tab_no'] ==2)
		{
			$tempdb->where('PM.total_user_joined>',0);
		}

		$sql = $tempdb->order_by($trending_types[$post['tab_no']]['sort_key'],'DESC')
		->order_by('PM.added_date',"DESC")
		->get();
		$result = $sql->result_array();

		//fetch options for predictions
		if(!empty($result))
		{
			foreach ($result as $key => $value)
			{
				$selected_option_id      = "";
				$prediction_master_id    = $value['prediction_master_id'];
				$result[$key]['options'] = $this->get_prediction_options($prediction_master_id);
				if(!empty($result[$key]['options']))
				{
					foreach ($result[$key]['options'] as $okey => $ovalue)
					{
					    if(!empty($ovalue['is_correct']))
					    {
					    	$selected_option_id = $ovalue['prediction_option_id'];
					    }		
					}
				}	

				$result[$key]['selected_option_id'] = $selected_option_id;
			}
		}	
 
		$next_offset = count($result) + $offset;

		return array('total' => $total,
		 'result' => $result,
		 'next_offset' => $next_offset 	
		);
	}

	public function get_bid_count_prediction($count=FALSE)
	{
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

		$page = 0;
		if(isset($post['current_page'])) {
			$page = $post['current_page']-1;
		}

		$this->config->load('prediction_config');
		$trending_types = $this->config->item('trending_prediction');
		
		$current_date=format_date();
		$pre_sql="(SELECT prediction_master_id ,IF(deadline_date<'{$current_date}' AND total_user_joined=0, 0, 1) as valid_prediction FROM  ".$this->db_prediction->dbprefix(PREDICTION_MASTER).")";

		 $default_selected_columns = "PM.prediction_master_id,PM.desc,PM.season_game_uid,DATE_FORMAT(PM.deadline_date, '".MYSQL_DATE_TIME_FORMAT."') as deadline_date,PM.status,PM.total_user_joined,PM.site_rake,IFNULL(PM.prize_pool,0) AS prize_pool,PM.total_pool,is_pin,COMMON.valid_prediction";
		
		 $sql = $this->db_prediction->select($default_selected_columns,FALSE)
						 ->from(PREDICTION_MASTER." PM")
						 ->join($pre_sql." COMMON","COMMON.prediction_master_id=PM.prediction_master_id")
						 ->where('PM.status',0)
						 ->where('PM.total_user_joined',$trending_types[$post['tab_no']]['bid_count'])
						 ->where("COMMON.valid_prediction",1);
						 

		$tempdb = clone $this->db_prediction;
		$query  = $this->db_prediction->get();
		$total  = $query->num_rows();
		if($count)
		{
			return $total;
		}

		$offset = 0;
        if(isset($limit) && isset($page))
        {
            $offset	= $limit * $page;
            $tempdb->limit($limit,$offset);
        }

		$sql = $tempdb->order_by('PM.added_date','DESC')
		->get();
		$result = $sql->result_array();

		//fetch options for predictions
		if(!empty($result))
		{
			foreach ($result as $key => $value)
			{
				$selected_option_id      = "";
				$prediction_master_id    = $value['prediction_master_id'];
				$result[$key]['options'] = $this->get_prediction_options($prediction_master_id);
				if(!empty($result[$key]['options']))
				{
					foreach ($result[$key]['options'] as $okey => $ovalue)
					{
					    if(!empty($ovalue['is_correct']))
					    {
					    	$selected_option_id = $ovalue['prediction_option_id'];
					    }		
					}
				}	

				$result[$key]['selected_option_id'] = $selected_option_id;
			}
		}	
 
		$next_offset = count($result) + $offset;

		return array('total' => $total,
		 'result' => $result,
		 'next_offset' => $next_offset 	
		);
	}

	public function get_prediction_invested_coins($post)
	{
		$count_result = $this->get_all_coins_invested_counts($post); 
        if(isset($count_result['total']))
        {
            $count = $count_result['total'];
            $total_coins_distributed = $count_result['total_coins_distributed'];
        }
        
        $this->db_user->select('O.points, O.status,O.source,U.user_name,DATE_FORMAT(O.date_added,"%Y-%m-%d") as date_added')
        ->from(ORDER.' O')
        ->join(USER.' U','U.user_id=O.user_id')
        ->where('O.points>',0)
        ->where('O.type',1)
        ->where('O.source',40)
        ->where('O.status',1);


        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_user->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db_user->order_by('date_added','ASC')
        ->group_by("O.source")
        ->group_by("DATE_FORMAT(O.date_added, '%Y-%m-%d')")
        ->get()
        ->result_array();

       // echo $this->db_user->last_query();die;
        return  array('result' => $result,'total' => $count,'total_coins_invested'=>$total_coins_distributed);  
	}

	/**
	 * @method get_prediction_invested_coins_weekly
	 * @uses function to get data weekly
	 * @param Array
	 * **/
	public function get_prediction_invested_coins_weekly($post)
	{
	
		$this->db_user->select('SUM(O.points) as week_points, O.status,O.source,U.user_name,DATE_FORMAT(O.date_added,"%u-%Y") as week_year,DATE_FORMAT(O.date_added,"Week_%u_%y") as week_number,
		concat_ws(" ",DATE_FORMAT(O.date_added,"Week %u"),
		DATE_FORMAT(DATE_ADD(O.date_added, INTERVAL(1-DAYOFWEEK(O.date_added)) DAY),"%Y-%m-%d") ,
		DATE_FORMAT(DATE_ADD(O.date_added, INTERVAL(7-DAYOFWEEK(O.date_added)) DAY),"%Y-%m-%d")) as created
		')
        ->from(ORDER.' O')
        ->join(USER.' U','U.user_id=O.user_id')
        ->where('O.points>',0)
        ->where('O.type',1)
        ->where('O.source',40)
        ->where('O.status',1);


        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_user->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db_user->order_by('date_added','ASC')
		->group_by("week_number")
		->order_by('week_number','ASC')
        ->get()
        ->result_array();

       // echo $this->db_user->last_query();die;
        return  array('result' => $result);  
	}

	/**
	 * @method get_prediction_invested_coins_monthly
	 * @uses function to get data weekly
	 * @param Array
	 * **/
	public function get_prediction_invested_coins_monthly($post)
	{
	
		$this->db_user->select('SUM(O.points) as month_points, O.status,O.source,U.user_name,DATE_FORMAT(O.date_added,"%b-%Y") as month_year
		')
        ->from(ORDER.' O')
        ->join(USER.' U','U.user_id=O.user_id')
        ->where('O.points>',0)
        ->where('O.type',1)
        ->where('O.source',40)
        ->where('O.status',1);


        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_user->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db_user->order_by('date_added','ASC')
		->group_by("month_year")
		->order_by('month_year','ASC')
        ->get()
        ->result_array();

       // echo $this->db_user->last_query();die;
        return  array('result' => $result);  
	}


	public function get_prediction_invested_users($post)
	{
		$count_result = $this->get_all_user_invested_counts($post); 
        if(isset($count_result['total']))
        {
            $count = $count_result['total'];
        }
        
        $this->db_user->select('count( U.user_id ) as user_count ,DATE_FORMAT(O.date_added,"%Y-%m-%d") as date_added')
        ->from(ORDER.' O')
        ->join(USER.' U','U.user_id=O.user_id')
        ->where('O.points>',0)
        ->where('O.type',1)
        ->where('O.source',40)
        ->where('O.status',1);


        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_user->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db_user->order_by('O.date_added','ASC')
        //->group_by("O.user_id")
        ->group_by("DATE_FORMAT(O.date_added, '%Y-%m-%d')")
        ->get()
        ->result_array();

        //echo $this->db_user->last_query();die;
        return  array('result' => $result,'total' => $count);  
	}

	public function get_all_user_invested_counts($post_data){
		
		$this->db_user->select("count(DISTINCT O.user_id) as total",FALSE)
        ->from(ORDER.' O')
        ->join(USER.' U','U.user_id=O.user_id')
        ->where('O.points>',0)
        ->where('O.source',40)
        ->where('O.status',1);

        if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db_user->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
		}

        $query = $this->db_user->get();
        $result = $query->row_array();

        //echo $this->db->last_query(); die;
        return $result;
	}


	/**
	 * @method get_prediction_invested_users_weekly
	 * @uses function for weekly graph
	 * @param Array
	*/
	public function get_prediction_invested_users_weekly($post)
	{
	
        $this->db_user->select('count( U.user_id ) as user_count ,DATE_FORMAT(O.date_added,"%u-%Y") as week_year,DATE_FORMAT(O.date_added,"Week_%u_%y") as week_number,
		concat_ws(" ",DATE_FORMAT(O.date_added,"Week %u"),
		DATE_FORMAT(DATE_ADD(O.date_added, INTERVAL(1-DAYOFWEEK(O.date_added)) DAY),"%Y-%m-%d") ,
		DATE_FORMAT(DATE_ADD(O.date_added, INTERVAL(7-DAYOFWEEK(O.date_added)) DAY),"%Y-%m-%d")) as created')
        ->from(ORDER.' O')
        ->join(USER.' U','U.user_id=O.user_id')
        ->where('O.points>',0)
        ->where('O.type',1)
        ->where('O.source',40)
        ->where('O.status',1);

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_user->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db_user->order_by('O.date_added','ASC')
        ->group_by("week_number")
        ->order_by("week_number",'ASC')
        ->get()
        ->result_array();

		return  array('result' => $result,
	
	);  
	}

	/**
	 * @method get_prediction_invested_users_monthly
	 * @uses function for weekly graph
	 * @param Array
	*/
	public function get_prediction_invested_users_monthly($post)
	{
	
        $this->db_user->select('count( U.user_id ) as user_count ,DATE_FORMAT(O.date_added,"%b-%Y") as month_year')
        ->from(ORDER.' O')
        ->join(USER.' U','U.user_id=O.user_id')
        ->where('O.points>',0)
        ->where('O.type',1)
        ->where('O.source',40)
        ->where('O.status',1);

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db_user->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post['to_date']."' ");
		}

        $result = $this->db_user->order_by('O.date_added','ASC')
        ->group_by("month_year")
        ->order_by("month_year",'ASC')
        ->get()
        ->result_array();

		return  array('result' => $result,
	
	);  
	}


	public function get_all_coins_invested_counts($post_data){
		
		$this->db_user->select("count(O.order_id) as total,SUM(IF(O.type=0,O.points,0)) as total_coins_distributed",FALSE)
        ->from(ORDER.' O')
        ->join(USER.' U','U.user_id=O.user_id')
        ->where('O.points>',0)
        ->where('O.source',40)
        ->where('O.status',1);

        if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db_user->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
		}

        $query = $this->db_user->get();
        $result = $query->row_array();

        //echo $this->db->last_query(); die;
        return $result;
	}

	function get_most_win_leaderboard($post)
	{
		$limit = 30;
        $offset = 0;

        $count = 0;
        if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

        $page = 0;

        if(empty($post['current_page']))
        {
            $post['current_page'] = 1;
        }

        $page = $post['current_page']-1;
		$offset	= $limit * $page;

		$pre_query =" (SELECT USER.user_id,SUM(O.points) as total_points,O.source from ".$this->db_user->dbprefix(USER)." USER INNER JOIN ".$this->db_user->dbprefix(ORDER)." O ON O.user_id=USER.user_id where O.status=1 AND O.type=0 GROUP BY USER.user_id )  ";

        $this->db_user->select("OO.total_points as coin_earned ,U.user_name,RANK() OVER (
            ORDER BY OO.total_points DESC
        ) user_rank",FALSE)
        ->from(USER.' U')
		->join($pre_query.' OO','U.user_id=OO.user_id')
		->where('OO.source',41)->limit($limit,$offset);

        $query = $this->db_user->order_by('coin_earned','desc')->get();
        $result = $query->result_array();

        //echo $this->db->last_query(); die;
        return array('list' =>$result,'next_offset' =>$offset + count($result) );
	}

	function get_most_win_count()
    {

        $pre_query =" (SELECT USER.user_id,SUM(O.points) as total_points,O.source from ".$this->db_user->dbprefix(USER)." USER INNER JOIN ".$this->db_user->dbprefix(ORDER)." O ON O.user_id=USER.user_id where O.status=1 AND O.type=0 GROUP BY USER.user_id )  ";

        $this->db_user->select("COUNT(U.user_id) total",FALSE)
        ->from(USER.' U')
		->join($pre_query.' OO','U.user_id=OO.user_id')
		->where('OO.source',41);

        // $this->db_user->select("COUNT(user_id) total",FALSE)
        // ->from(USER.' U');
        $query = $this->db_user->get();
        $result = $query->row_array();
        return $result;
    }

	function get_most_bid_leaderboard($post)
	{
		$limit = 30;
        $offset = 0;

        $count = 0;
        if(isset($post['items_perpage']))
		{
			$limit = $post['items_perpage'];
		}

        $page = 0;

        if(empty($post['current_page']))
        {
            $post['current_page'] = 1;
        }

        $page = $post['current_page']-1;
		$offset	= $limit * $page;

		$pre_query =" (SELECT USER.user_id,SUM(O.points) as total_points,O.source from ".$this->db_user->dbprefix(USER)." USER INNER JOIN ".$this->db_user->dbprefix(ORDER)." O ON O.user_id=USER.user_id where O.status=1 AND O.type=1 GROUP BY USER.user_id )  ";

        $this->db_user->select("OO.total_points as coin_invested ,U.user_name,RANK() OVER (
            ORDER BY OO.total_points DESC
        ) user_rank",FALSE)
        ->from(USER.' U')
		->join($pre_query.' OO','U.user_id=OO.user_id')
		->where('OO.source',40)->limit($limit,$offset);

        $query = $this->db_user->order_by('coin_invested','desc')->get();
        $result = $query->result_array();

        //echo $this->db->last_query(); die;
        return array('list' =>$result,'next_offset' =>$offset + count($result) );
	}

	function get_most_bid_count()
    {

        $pre_query =" (SELECT USER.user_id,SUM(O.points) as total_points,O.source from ".$this->db_user->dbprefix(USER)." USER INNER JOIN ".$this->db_user->dbprefix(ORDER)." O ON O.user_id=USER.user_id where O.status=1 AND O.type=1 GROUP BY USER.user_id )  ";

        $this->db_user->select("COUNT(U.user_id) total",FALSE)
        ->from(USER.' U')
		->join($pre_query.' OO','U.user_id=OO.user_id')
		->where('OO.source',40);

        // $this->db_user->select("COUNT(user_id) total",FALSE)
        // ->from(USER.' U');
        $query = $this->db_user->get();
        $result = $query->row_array();
        return $result;
    }


	/**
	* @method get_top_seasons description
	* @uses This function used for get top predicted seasons
	* @param count
	*/	
	public function get_top_seasons()
	{
		$season_result = $this->db_prediction->select('PM.season_game_uid ,COUNT(UP.user_id) as user_count')
		->from(PREDICTION_MASTER.' PM')
		->join(PREDICTION_OPTION.' PO','PM.prediction_master_id=PO.prediction_master_id','INNER')
		->join(USER_PREDICTION.' UP','UP.prediction_option_id=PO.prediction_option_id','INNER')
		->group_by('PM.season_game_uid')->get()->result_array();

		return $season_result;
	}

	


}