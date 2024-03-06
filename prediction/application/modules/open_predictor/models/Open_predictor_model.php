<?php

class Open_predictor_model extends MY_Model {

    public function __construct() {
		parent::__construct();
		$this->db_prediction		= $this->load->database('open_predictor_db', TRUE);
    }

    /*
	 * [get_season_by_season_game_uids description]
	 * Summary :-
	 * @param  [type] $status [description]
	 * @return [type]         [description]
	 */
	public function get_season_by_season_game_uids($post, $offset)
	{

		$current_date = format_date();
		$year = format_date($current_date,'Y');
		$season_game_uids = $post['season_game_uid'];
		$sports_id = $this->input->post('sports_id');

		$table_name = "";
		$cricket_where = "";
		$second_inning_table_name = "";
		$second_inning_select = "";
		$score_select = "0 as home_team_score,0 as away_team_score,0 as home_overs,0 as away_overs,0 as home_wickets,0 as away_wickets";
		switch ($sports_id) {
			case '7':
				$cricket_where = " AND ST.innings=1";
				$score_select = "IFNULL(ST.home_team_score,0) as home_team_score,IFNULL(ST.away_team_score,0) as away_team_score,IFNULL(ROUND(ST.home_overs,1),0) as home_overs,IFNULL(ROUND(ST.away_overs,1),0) as away_overs,IFNULL(ST.home_wickets,0) as home_wickets,IFNULL(ST.away_wickets,0) as away_wickets";
				$second_inning_select = "IFNULL(ST1.home_team_score,0) as home_team_score_2,IFNULL(ST1.away_team_score,0) as away_team_score_2,IFNULL(ROUND(ST1.home_overs,1),0) as home_overs_2,IFNULL(ROUND(ST1.away_overs,1),0) as away_overs_2,IFNULL(ST1.home_wickets,0) as home_wickets_2,IFNULL(ST1.away_wickets,0) as away_wickets_2";
				$table_name = $second_inning_table_name = GAME_STATISTICS_CRICKET;
				break;
            case '5':
            	$score_select = "IFNULL(ST.home_team_goal,0) as home_team_score,IFNULL(ST.away_team_goal,0) as away_team_score";
				$table_name = GAME_STATISTICS_SOCCER;
				break;
			case '8':
				$score_select = "IFNULL(ST.home_team_score,0) as home_team_score,IFNULL(ST.away_team_score,0) as away_team_score";
				$table_name = GAME_STATISTICS_KABADDI;
				break;
			default:
				break;
		}
		
                $select = "S.season_game_uid, S.league_id, L.league_abbr,L.league_name,L.sports_id, S.week, S.season_scheduled_date  AS season_scheduled_date, HT.team_abbr as home,AT.team_abbr as away, S.season_scheduled_date AS match_date,IFNULL(HT.flag,HT.feed_flag) AS home_flag,IFNULL(AT.flag,AT.feed_flag) AS away_flag, S.status,S.format,
					CASE WHEN format = 1 THEN 'ONE-DAY' 
						 WHEN format = 2 THEN 'TEST'
						 WHEN format = 3 THEN 'T20' END AS format_str,L.image,IF(S.season_scheduled_date < '{$current_date}' AND (S.status = 0 OR S.status = '1'),'1','0') as match_status_flag,S.score_data";

					$this->db->select($select)
						   	->from(SEASON. " S")
						   	->join(LEAGUE. " L","L.league_id = S.league_id", "INNER")
							->join(TEAM.' HT','HT.team_id=S.home_id','INNER')
							->join(TEAM.' AT','AT.team_id=S.away_id','INNER');

			   	//for get home and away team scores for second inning in test
			   	if($second_inning_table_name != ""){
					$this->db->select($second_inning_select,FALSE);
					$this->db->join($second_inning_table_name." ST1", "ST1.season_game_uid = S.season_game_uid AND ST1.innings=2","LEFT");
				}

			   	//for get home and away team scores
				if($table_name != ""){
					$this->db->select($score_select,FALSE);
					$this->db->join($table_name." ST", "ST.season_game_uid = S.season_game_uid ".$cricket_where,"LEFT");
				}

                if(!empty($season_game_uids)){
                	if(is_array($season_game_uids))
                    	$this->db->where_in('S.season_game_uid', $season_game_uids);
                    else
                    {
                    	$this->db->where_in('S.season_game_uid', explode(',', $season_game_uids));
                    }
                }

                if(!empty($post['league_id']))
                {
                	$this->db->where('S.league_id', $post['league_id']);
                }

                if(!empty($post['sports_id']))
                {
                	$this->db->where('L.sports_id', $post['sports_id']);
                }
				
				
				// if(isset($post['status']) && $post['status'] == 0)//upcoming
				// 	{
				// 		$this->db->where('S.status', 0);
				// 		$this->db->where("S.season_scheduled_date >= '{$current_date}'");
				// 	}

				// if(empty($season_game_uids))
				// {	
				// 	if(isset($post['status']) && $post['status'] == 1)//live
				// 	{
				// 		//$this->db->where_in('S.status',array(0,1));
				// 		$this->db->where("S.season_scheduled_date < '{$current_date}'");
				// 	}
				// }

                $sort_order = "ASC";
                if(!empty($post['for_prediction']))
                {
                	$sort_order = "DESC";

                }
				   
				$this->db->group_by("S.season_game_uid");				   
			   $result = $this->db->order_by('S.season_scheduled_date',$sort_order)
			   ->limit(MATCH_LIMIT, $offset)
			   ->get()->result_array();

			   //echo $this->db->last_query();die('dfd');
		return ($result) ? $result : array();				   				 
	}

	function get_active_category()
	{
		$result = $this->db_prediction->select('C.*')
        ->from(PREDICTION_MASTER.' PM')
        ->join(CATEGORY.' C','C.category_id=PM.category_id')
		->where('PM.status',0)
		->where('C.status',1)
		->group_by('PM.category_id')
		->get()
		->result_array();


		return $result;

	}

	/**
     * [get_prediction_participants description]
     * @uses :- get participants
     * @param Number prediction master id,user_id
     */
	public function get_prediction_participants($prediction_master_id,$user_id="")
	{
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['page_size']))
		{
			$limit = $post['page_size'];
		}

		$page = 0;
		if(isset($post['page_no'])) {
			$page = $post['page_no']-1;
		}

         $offset	= $limit * $page;
		$pre_query ="(SELECT SUM(UP.bet_coins) as option_total,GROUP_CONCAT(UP.user_id) as user_ids,PO.prediction_option_id
FROM ".$this->db_prediction->dbprefix(PREDICTION_MASTER)." PM 
INNER JOIN ".$this->db_prediction->dbprefix(PREDICTION_OPTION)." PO ON PM.prediction_master_id=PO.prediction_master_id 
INNER JOIN ".$this->db_prediction->dbprefix(USER_PREDICTION)." UP ON UP.prediction_option_id=PO.prediction_option_id  
GROUP BY PO.prediction_option_id)";

		 $this->db_prediction->select("PM.prediction_master_id,PM.prize_pool,UP.bet_coins,PM.total_pool,
		 CAST((PM.prize_pool*(UP.bet_coins/TR.option_total)) as UNSIGNED) as estimated_winning,UP.user_id,PO.`option`",FALSE)
		->from(PREDICTION_MASTER.' PM')
		->join(PREDICTION_OPTION.' PO',"PO.prediction_master_id=PM.prediction_master_id")
		->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
		->join($pre_query.' TR',"TR.prediction_option_id=UP.prediction_option_id")
		->where("PO.prediction_master_id",$prediction_master_id);
		
		if(!empty($user_id))
		{
			$this->db_prediction->where('UP.user_id',$user_id);
		}
		else if($this->user_id)
		{
			$this->db_prediction->where('UP.user_id<>',$this->user_id);
		}
		
		$this->db_prediction->group_by('UP.user_id')
		->order_by("PO.prediction_option_id","ASC");

		if(empty($user_id))
		{
			$this->db_prediction->limit($limit,$offset);
		}
		$sql = $this->db_prediction->get();
		$prediction_data	= $sql->result_array();

		return array(
		 'other_list' => $prediction_data,
		);
	}

	public function get_prediction_leaderboard($prediction_master_id,$user_id='')
	{
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['page_size']))
		{
			$limit = $post['page_size'];
		}

		$page = 0;
		if(isset($post['page_no'])) {
			$page = $post['page_no']-1;
		}

         $offset	= $limit * $page;
		$this->db_prediction->select("PM.prediction_master_id,PM.total_user_joined,PM.prize_pool,UP.bet_coins,PM.total_pool,UP.user_id,PO.`option`,UP.win_coins,(RANK() OVER (ORDER BY UP.win_coins DESC)) AS user_rank,PM.entry_type,PM.entry_fee,PM.win_prize",FALSE)
	   ->from(PREDICTION_MASTER.' PM')
	   ->join(PREDICTION_OPTION.' PO',"PO.prediction_master_id=PM.prediction_master_id")
	   ->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
	   ->where("PO.prediction_master_id",$prediction_master_id)
	   //->where("PM.status",2)
	   ->group_by('UP.user_id');

	//    if(!empty($user_id))
	//    {
	// 	   $this->db_prediction->where('UP.user_id',$user_id);
	//    }
	//    else 
	   if(empty($user_id) && $this->user_id)
	   {
		  // $this->db_prediction->where('UP.user_id<>',$this->user_id);
		  $offset+=1;
	   }

	   if($this->user_id)
	   {
		   $this->db_prediction->order_by("FIELD ( UP.user_id, ".$this->user_id." ) DESC");

	   }

	   $this->db_prediction->order_by("user_rank","ASC");

		if(empty($user_id))
		{
			$this->db_prediction->limit($limit,$offset);
		}
		else{
			$this->db_prediction->limit(1,0);
		}
	
	   $sql = $this->db_prediction->get();
	   $prediction_data	= $sql->result_array();
	   $total = 0;
	   if(!empty($prediction_data))
		{	
			$total = $prediction_data[0]['total_user_joined'];
		}
		
	   //print_r($prediction_data);
	   return array(
		'other_list' => $prediction_data,
		'total' => $total
	   );
	}


	function get_fixed_prediction_categories()
	{
		$this->db_prediction->select("C.*",FALSE)
		->from(PREDICTION_MASTER.' PM')
		->join(CATEGORY.' C',"C.category_id=PM.category_id")
		->join(PREDICTION_OPTION.' PO',"PO.prediction_master_id=PM.prediction_master_id")
		->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
		->where("PM.status",2)
		->where("PM.entry_type",1)//fixed type
		->group_by('PM.category_id');

		$this->db_prediction->order_by("PM.category_id","ASC");	 
		 $sql = $this->db_prediction->get();
		$data	= $sql->result_array();
 
		//echo $this->db_prediction->last_query();die();
		return $data;
	}

	public function get_fixed_prediction_leaderboard($category_id='',$user_id='')
	{
		$post = $this->input->post();
		$limit = 10;
		$offset = 0;
		if(isset($post['page_size']))
		{
			$limit = $post['page_size'];
		}

		$page = 0;
		if(isset($post['page_no'])) {
			$page = $post['page_no']-1;
		}

         $offset	= $limit * $page;
		$this->db_prediction->select("PM.prize_pool,UP.bet_coins,PM.total_pool,UP.user_id,PO.`option`,
		SUM(IF(PM.prediction_master_id and PO.is_correct=1,1,0) ) as
total_wins,SUM(UP.win_coins) as win_coins,(RANK() OVER (ORDER BY SUM(UP.win_coins) DESC)) AS user_rank,PM.entry_type,PM.entry_fee,PM.win_prize",FALSE)
	   ->from(PREDICTION_MASTER.' PM')
	   ->join(PREDICTION_OPTION.' PO',"PO.prediction_master_id=PM.prediction_master_id")
	   ->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
	   ->where("PM.status",2)
	   ->where("PM.entry_type",1)
	   ->group_by('UP.user_id');

	   if(!empty($category_id))
	   {
		   $this->db_prediction->where('PM.category_id',$category_id);
	   }

	   if(!empty($user_id))
	   {
		   $this->db_prediction->order_by("FIELD ( UP.user_id, ".$this->user_id." ) DESC");	  
		   //$this->db_prediction->where('UP.user_id<>',$this->user_id);
	   }
		

	   if(!empty($post['from_date']) && !empty($post['to_date']))
	   {
		$this->db_prediction->where("PM.deadline_date>=",$post['from_date'])
		->where("PM.deadline_date<=",$post['to_date']);
	
	   }


	   $this->db_prediction->order_by("user_rank","ASC");

		if(empty($user_id))
		{
			$this->db_prediction->limit($limit,$offset);
		}
		else{
			$this->db_prediction->limit(1,0);

		}
	
		$sql = $this->db_prediction->get();
	   $prediction_data	= $sql->result_array();

	//    if(!empty($user_id))
	//    {	
	// 	   echo $this->db_prediction->last_query();die();
	// 	 //log_message('error', $this->db_prediction->last_query());
	// 	}
	   $total = 0;
	   
	   //print_r($prediction_data);
	   return array(
		'other_list' => $prediction_data,
		'total' => $total
	   );
	}

	public function make_user_prediction($save_data)
	{
		$this->db_prediction->insert(USER_PREDICTION,$save_data);
		return $this->db_prediction->insert_id();
	}

	public function update_prediction_master($prediction_master_id,$bet_coins,$user_amount)
	{
		$this->db_prediction->set('total_user_joined', 'total_user_joined + 1', FALSE);
		$this->db_prediction->set('total_pool', 'total_pool +'.$bet_coins, FALSE);
		$this->db_prediction->set('prize_pool', 'prize_pool + '.$user_amount, FALSE);
		$this->db_prediction->where('prediction_master_id', $prediction_master_id);
        $this->db_prediction->update(PREDICTION_MASTER);
        return $this->db_prediction->affected_rows(); 
	}

	public function get_user_predicted($prediction_master_id)
	{
		/**
		 * var sql_query=`SELECT PM.* 
                FROM `+CONSTANTS.PREDICTION_MASTER+` PM 
                INNER JOIN `+CONSTANTS.PREDICTION_OPTION+` PO ON PO.prediction_master_id = PM.prediction_master_id
                INNER JOIN `+CONSTANTS.USER_PREDICTION+` UP ON UP.prediction_option_id=PO.prediction_option_id
                WHERE PM.prediction_master_id=`+req.body.prediction_master_id+` AND UP.user_id=`+req.body.currect_user_id+` GROUP BY PM.prediction_master_id`;
		 * 
		 * 
		 * 
		*/
	  return $result = $this->db_prediction->select("PM.*")
		->from(PREDICTION_MASTER.' PM')
	   ->join(PREDICTION_OPTION.' PO',"PO.prediction_master_id=PM.prediction_master_id")
	   ->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id")
	   ->where("PM.prediction_master_id",$prediction_master_id)
	   ->where("UP.user_id",$this->user_id)
	   ->group_by('PM.prediction_master_id')->get()->row_array();



	}

}