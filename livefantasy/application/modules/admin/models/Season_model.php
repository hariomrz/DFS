<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Season_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	* This function used for get all season schedule
	* @param array
	* @return array
	*/
	public function get_all_season_schedule()
	{
		$sort_field	= 'season_scheduled_date';
		$sort_order	= 'DESC';
		$limit		= 10;
		$page		= 0;
		$post_data = $this->input->post();
		$current_date = format_date();
		if(($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']) && ($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && ($post_data['sort_field']) && in_array($post_data['sort_field'],array('year','type','season_scheduled_date','home','away','status','api_week')))
		{
			$sort_field = $this->input->post('sort_field');
		}

		if(isset($post_data['sort_order']) && ($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		$league_id	= isset($post_data['league_id']) ? $post_data['league_id'] : "";
		$sports_id	= isset($post_data['sports_id']) ? $post_data['sports_id'] : "";
		$fromdate	= isset($post_data['fromdate']) ? $post_data['fromdate'] : "";
		$todate		= isset($post_data['todate']) ? $post_data['todate'] : "";
		$status		= isset($post_data['status']) ? $post_data['status'] : "";
		$type		= isset($post_data['type']) ? $post_data['type'] : "";

		$this->db->select("S.season_id, S.league_id, S.season_game_uid,S.format,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,S.status,S.status_overview, S.season_scheduled_date,S.season_scheduled_date,IFNULL(T1.feed_flag,T1.flag) AS home_flag,IFNULL(T2.feed_flag,T2.flag) AS away_flag,IFNULL(L.league_display_name,L.league_name) AS league_abbr,S.is_published,S.delay_minute,S.delay_message,S.custom_message,S.scoring_alert,S.is_pin_fixture",false)
			->from(SEASON." AS S")
			->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
			->join(TEAM.' T1','T1.team_uid = S.home_uid AND L.sports_id=T1.sports_id','LEFT')
			->join(TEAM.' T2','T2.team_uid = S.away_uid AND L.sports_id=T2.sports_id','LEFT')
			->where("S.format !=",TEST_FORMAT);
			
		if($sports_id != "")
		{
			$this->db->where("L.sports_id", $sports_id);
		}
		if($league_id != "")
		{
			$this->db->where("S.league_id",$league_id);
		}

		if($status != "")
		{
			if($status == 'not_complete'){
				$this->db->where_not_in("status", array(2));
			}
			else{
				$this->db->where("status", $status);
			}
		}

		if($fromdate != "" && $todate != "")
		{
			$this->db->where("DATE_FORMAT(season_scheduled_date,'%Y-%m-%d') >= '".$fromdate."' and DATE_FORMAT(season_scheduled_date,'%Y-%m-%d') <= '".$todate."'");
		}

		if($type == 2)
		{	
			$this->db->where("DATE_FORMAT(season_scheduled_date,'%Y-%m-%d %H:%i:%s') >= '".$current_date."'");
		} else if($type == 1){
			$this->db->where("S.is_published", 1);
			$this->db->where("DATE_FORMAT(season_scheduled_date,'%Y-%m-%d %H:%i:%s') <= '".$current_date."'");
		}

		$this->db->group_by('S.season_game_uid');
		$tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();

		$sql = $tempdb->order_by($sort_field, $sort_order)
					->limit($limit,$offset)
					->get();
		$result	= $sql->result_array();
		//echo $this->db->last_query(); die;
		$result = ($result) ? $result : array();
		return array('result'=>$result,'total'=>$total);
	}

	public function get_season_by_game_id($season_game_uid,$league_id)
	{
		$sql = $this->db->select("S.*,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,L.sports_id,L.league_abbr,IFNULL(T1.feed_flag,T1.flag) AS home_flag,IFNULL(T2.feed_flag,T2.flag) AS away_flag", FALSE)
				->from(SEASON . " AS S")
				->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
				->join(TEAM.' T1','T1.team_uid = S.home_uid AND L.sports_id=T1.sports_id','LEFT')
				->join(TEAM.' T2','T2.team_uid = S.away_uid AND L.sports_id=T2.sports_id','LEFT')
				->where("S.season_game_uid", $season_game_uid)
				->where("S.league_id", $league_id)
				->get();
		$result = $sql->row_array();
		return $result;
	}

	public function update_match_delay_data($post_data){
		$league_id = $post_data['league_id'];
		$season_game_uid = $post_data['season_game_uid'];
		$delay_minute = $post_data['new_delay_minute'] - $post_data['delay_minute'];
		$season_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$delay_minute.' minutes', strtotime($post_data['season_scheduled_date'])));
        $upd_data = array('season_scheduled_date'=>$season_scheduled_date,'delay_minute' => $post_data['new_delay_minute'],'delay_message' => $post_data['delay_message'],"delay_by_admin"=>1);
        $this->db->where('season_game_uid', $season_game_uid);
        $this->db->where('league_id', $league_id);
        $this->db->update(SEASON, $upd_data);

        $sql = $this->db->select("C.*", FALSE)
				->from(COLLECTION." AS C")
				->where("C.league_id",$league_id)
				->where("C.season_game_uid",$season_game_uid)
				->get();
		$result = $sql->result_array();
		if(isset($result) && !empty($result)){
			$collection_ids = array_column($result,'collection_id');
			if(!empty($collection_ids)){
				//Update schedule date in collection table
	            $this->db->where('league_id', $league_id);
	            $this->db->where('season_game_uid', $season_game_uid);
	            $this->db->where_in('collection_id', $collection_ids);
	            $this->db->update(COLLECTION,array("season_scheduled_date"=>$season_scheduled_date));

	            //Update schedule date in contest table
	            $this->db->where('league_id', $league_id);
	            $this->db->where_in('collection_id', $collection_ids);
				$this->db->update(CONTEST,array("season_scheduled_date"=>$season_scheduled_date));
			}
		}
		return true;
	}

	public function update_match_custom_message($post_data){
		$custom_message = $post_data['custom_message'];
		if(isset($post_data['is_remove']) && $post_data['is_remove'] == 1){
			$custom_message = "";
		}
		$upd_data = array('custom_message' => $custom_message,"notify_by_admin"=>1);
        $this->db->where('season_game_uid', $post_data['season_game_uid']);
        $this->db->where('league_id', $post_data['league_id']);
        $this->db->update(SEASON, $upd_data);
		return true;
	}

	/**
	* This function is used to update season details 
	* @param array $update_data
	* @param array $update_where
	* @return boolean
	*/
	public function update_season_detail($update_data,$update_where)
	{
		$this->db->where($update_where);
		$this->db->update(SEASON,$update_data);
		return true;
	}

	/**
	* This function is used to save collection data 
	* @param array $data
	* @return boolean
	*/
	public function save_collection($data)
	{
		try{
			$this->db->insert(COLLECTION,$data);
			return $this->db->insert_id();
		}catch(Exception $e){
	        //echo 'Caught exception: '.  $e->getMessage(). "\n";
	        return false;
      	}
	}

	public function get_season_details($season_game_uid,$league_id)
	{
		$sql = $this->db->select("S.*,L.sports_id,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IFNULL(T1.feed_flag,T1.flag) AS home_flag,IFNULL(T2.feed_flag,T2.flag) AS away_flag,IFNULL(L.league_display_name,L.league_name) AS league_abbr", FALSE)
				->from(SEASON . " AS S")
				->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
				->join(TEAM.' T1','T1.team_uid = S.home_uid AND L.sports_id=T1.sports_id','LEFT')
				->join(TEAM.' T2','T2.team_uid = S.away_uid AND L.sports_id=T2.sports_id','LEFT')
				->where("S.season_game_uid", $season_game_uid)
				->where("S.league_id", $league_id)
				->get();
		$result = $sql->row_array();
		return $result;
	}

	public function get_overs($season_game_uid,$league_id){
    	$overs = $this->db->select('CL.collection_id,CL.league_id,CONVERT(SUBSTRING(CL.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CL.inn_over,3), SIGNED INTEGER) as overs,CL.collection_name,COUNT(DISTINCT C.contest_id) as total_contest,SUM(C.total_user_joined) as total_user_joined,C.contest_id,CL.status,CL.season_game_uid',false)
    		->from(COLLECTION.' as CL')
    		->join(CONTEST.' as C', 'CL.collection_id = C.collection_id')
    		->where(array('CL.season_game_uid'=>$season_game_uid,'CL.league_id'=>$league_id))
    		->where_in('C.status',array(0,1,2,3))
    		->order_by("inning", "ASC")
        	->order_by("overs", "ASC")
    		->group_by('C.collection_id')
    		->get()->result_array();
    	return $overs;

    }

    public function get_season_collections($season_game_uid,$league_id)
	{
		$sql = $this->db->select("CM.collection_id,CM.league_id,CM.season_game_uid,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,CM.collection_name,CM.season_scheduled_date,CM.status", FALSE)
				->from(COLLECTION." AS CM")
				->where("CM.season_game_uid", $season_game_uid)
				->where("CM.league_id", $league_id)
				->order_by("inning", "ASC")
        		->order_by("overs", "ASC")
				->get();
		$result = $sql->result_array();
		return $result;
	}

	/**
     * used to get match collection details
     * @param array $post_data
     * @return array
    */
    public function get_fixture_collection_details($post_data){
        $this->db->select("CL.collection_id,CL.league_id,CL.collection_name,CL.season_scheduled_date,CL.status,CL.season_game_uid",FALSE);
        $this->db->from(COLLECTION." as CL");
        $this->db->where('CL.season_game_uid', $post_data['season_game_uid']);
        $this->db->where('CL.league_id', $post_data['league_id']);
        $result = $this->db->get()->result_array();
        return $result;
    } 

	public function get_match_paid_free_users($collection_ids)
	{
		$rs = $this->db->select("COUNT(UC.user_contest_id) as total_users,IFNULL(SUM(IF(C.entry_fee=0,1,0)),0) as free_users,	IFNULL(SUM(IF(C.entry_fee>0,1,0)),0)  as paid_users", FALSE)
			->from(USER_CONTEST.' UC')
			->join(CONTEST. ' C',"UC.contest_id=C.contest_id")	
			->where_in("C.collection_id", $collection_ids)
			->where_in("C.status", array(0,2,3))
			->where('C.total_user_joined>',0)
			->get();
		$result = $rs->row_array();
		return $result;
	}

	public function pin_fixture($post_data)
	{
		if(empty($post_data)){
			return false;
		}

		$is_pin_fixture = 1;
		if(isset($post_data['is_pin_fixture'])){
			$is_pin_fixture = $post_data['is_pin_fixture'];
		}

		//update status in database
        $this->db->where("league_id", $post_data['league_id']);
		$this->db->where("season_game_uid", $post_data['season_game_uid']);
		$this->db->set('is_pin_fixture', $is_pin_fixture);
		$this->db->update(SEASON);

        return true;
	}

	/**
	 * function used for update season details
	 * @param array $post_data
	 * @return boolean
	 */
    public function update_season_status($post_data){
    	$update_data = array('match_status' => $post_data['match_status'], 'modified_date' => format_date());
        //if status is publish then set match status closed
        if($post_data['match_status'] == 2){
            $update_data['status'] = 2;
            $update_data['status_overview'] = 4;
        }
        
        $this->db->where('season_game_uid', $post_data['season_game_uid']);
        $this->db->update(SEASON, $update_data);
        return true;
    }

    public function get_season_data($con,$rows = true)
	{

		if(isset($con['role']))
		{
			unset($con['role']);
		}		
		$result = $this->db->select("S.home, S.home_uid, S.away, S.away_uid,S.week, S.season_game_uid, S.league_id, DATE_FORMAT(S.season_scheduled_date,'".MYSQL_DATE_TIME_FORMAT."') as season_scheduled_date, S.format")
		->from(SEASON . " AS S")
		->where($con)->get();

		if($rows)
			$result = $result->result_array();
		else
			$result = $result->row_array();

		return $result;
	}

	/*
	*@method get_lobby_season_matches
	*@uses function to get upcoming matches of one month,include last 3 days from current date
	******/
	public function get_season_matches_by_ids($post_data)
	{	
	    $current_date_time = format_date();
		$this->db->select("S.home_uid,S.away_uid,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,S.away AS away,S.week, S.season_game_uid, S.league_id, S.season_scheduled_date, S.format, L.sports_id,IFNULL(L.league_display_name,L.league_name) AS league_abbr,IFNULL(L.league_display_name,L.league_name) AS league_name,IFNULL(T1.feed_flag,T1.flag) AS home_flag,
			T1.team_id as home_id,S.status,S.status_overview,score_data
			",false);
		if($post_data['sports_id']!=9)
		{
			$this->db->select("T2.team_id as away_id,IFNULL(T2.feed_flag,T2.flag) AS away_flag,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away",false);
		}else
		{
			$this->db->select(" NULL as away_id, NULL as away_flag",false);
		}
		$this->db->from(SEASON . " AS S")
				->join(LEAGUE . " AS L", "L.league_id = S.league_id", "left")
				->join(TEAM.' T1','T1.team_uid=S.home_uid');
				
		if($post_data['sports_id']!=9)
		{
			$this->db->join(TEAM.' T2','T2.team_uid=S.away_uid','left');
		}
		$this->db->where("L.active",1);
		if(!empty($post_data['sports_id']))
		{
			$this->db->where("L.sports_id", $post_data['sports_id']);
		}

		if(!empty($post_data['season_game_uids']))
		{	 
			$this->db->where_in("S.season_game_uid ", $post_data['season_game_uids']);
		}

		if(!empty($post_data['q']) &&  $post_data['search'] == TRUE)
		{
			$q = $post_data['q'];
			 $this->db->group_start();
			 $this->db->like("T1.team_name",$q,"both");
			 $this->db->or_like("T2.team_name",$q,"both");
			 $this->db->or_like("T1.team_abbr",$q,"both");
			 $this->db->or_like("T2.team_abbr",$q,"both");
			 $this->db->group_end();
		}

		if(!empty($post_data['team_uid']))
		{
			$this->db->where("(home_uid IN ('".$post_data['team_uid']."') OR away_uid IN ('".$post_data['team_uid']."'))");
		}

		$this->db->group_by("S.season_game_uid");
		$this->db->order_by("S.season_scheduled_date","ASC");
		$sql = $this->db->get();
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

	public function player_list($season_game_uid){
		$this->db->select('PT.*,P.name,P.display_name,S.home_uid,S.away_uid,IFNULL(S.batting_team_uid,home_uid) AS batting_team_uid',false)
			->from(PLAYER_TEAM.' as PT')
			->join(PLAYER.' as P','PT.player_id=P.player_id')
			->join(SEASON.' as S','S.season_game_uid=PT.season_game_uid')
			->where(array('PT.season_game_uid'=>$season_game_uid,'PT.is_deleted'=>0));
		return $this->db->get()->result_array();
	}

	public function get_markets_odds($post_data){
		$this->db->select('*,TRIM(over_ball)+0 as over_ball',false)
			->from(MARKET_ODDS)
			->where(array('league_id'=>$post_data['league_id'],'season_game_uid'=>$post_data['season_game_uid'],'inn_over'=>$post_data['inn_over']));
		$this->db->order_by('display_order','ASC');
		return $this->db->get()->result_array();
	}

	/**
     * for generate market id
     * @return string
     */
    public function generate_market_id() {
        $this->load->helper('security');        
        $salt = do_hash(time() . mt_rand());
        $new_key = substr($salt, 0, 8);
        $new_key = strtoupper($new_key);
        return $new_key;
    }

    public function get_last_market_odds(){
    	$post_data = $this->input->post();
    	$this->db->select('over_ball,market_odds,market_status,display_order');
    	$this->db->from(MARKET_ODDS);
    	$this->db->where(array('league_id'=>$post_data['league_id'],'season_game_uid'=>$post_data['season_game_uid'],'inn_over'=>$_POST['inn_over']));
    	$this->db->order_by('display_order','DESC');
    	return $this->db->get()->row_array();

    }

    public function update_market_odds_points(){
    	$post_data = $this->input->post();
    	$data = array(
                    "market_odds"=>json_encode($post_data['market_odds']),
                    "bat_player_id"=>$post_data['bat_player_id'],
                    "bow_player_id"=>$post_data['bow_player_id'],
                    "updated_by"=>1,
                    "updated_date"=>format_date(),
                    "market_status"=>"ctd"
                );
    	$this->db->where(array('league_id'=>$post_data['league_id'],'season_game_uid'=>$post_data['season_game_uid'],'market_id'=>$post_data['market_id'],'inn_over'=>$post_data['inn_over'],'over_ball'=>$post_data['over_ball']));
    	$this->db->update(MARKET_ODDS,$data);
    	return $this->db->affected_rows();
    }

    public function get_odds_details(){
    	$post_data = $this->input->post();
    	$this->db->select('*')
    			->from(MARKET_ODDS)
    			->where(array('league_id'=>$post_data['league_id'],'season_game_uid'=>$post_data['season_game_uid'],'inn_over'=>$post_data['inn_over'],'over_ball'=>$post_data['over_ball']));
    	return $this->db->get()->row_array();
    }

    public function get_next_odds_details(){
    	$post_data = $this->input->post();
    	$this->db->select('*')
    			->from(MARKET_ODDS)
    			->where(array('season_game_uid'=>$post_data['season_game_uid'],'inn_over'=>$post_data['inn_over'],'over_ball'=>$post_data['over_ball']));
    	return $this->db->get()->row_array();
    }

    public function get_fixture_ball_points(){
    	$post_data = $this->input->post();
    	$this->db->select('multiplier,capping');
    	$this->db->from(SEASON);
    	$this->db->where('season_game_uid',$post_data['season_game_uid']);
    	$season_details = $this->db->get()->row_array();
    	return $season_details;
    }

    public function ball_point_multiply(){
    	$post_data = $this->input->post();
    	$ball_points = $this->get_fixture_ball_points();
    	if(!empty($ball_points)){
    		$multiplier = $ball_points['multiplier'];
    		$capping 	= $ball_points['capping'];

    		$odds_point = json_decode($post_data['market_odds'],true);
    		$final_odds_point = array();
    		foreach($odds_point as $id=>$point){
    			$new_point = $point*$multiplier;
    			if($new_point>$capping){
    				$new_point = $capping;	
    			}
                $odds_point[$id] = $new_point;
            }
    		$post_data['market_odds'] = json_encode($odds_point);
    	}
   		
    	$this->db->where(array('season_game_uid'=>$post_data['season_game_uid'],'market_id'=>$post_data['market_id'],'inn_over'=>$post_data['inn_over'],'over_ball'=>$post_data['over_ball']));
    	$this->db->update(MARKET_ODDS,array('market_odds'=>$post_data['market_odds']));
    	//return $this->db->affected_rows();
    	return $post_data['market_odds'];
    }



    public function change_to_play(){
    	$post_data = $this->input->post();
    	$this->db->where(array('season_game_uid'=>$post_data['season_game_uid'],'market_id'=>$post_data['market_id'],'inn_over'=>$post_data['inn_over'],'over_ball'=>$post_data['over_ball']));
    	$this->db->update(MARKET_ODDS,array('market_status'=>'stm',"market_date"=>$post_data['market_date']));
    	return $this->db->affected_rows();
    }

    public function update_ball_result(){
    	$post_data = $this->input->post();
    	$extra_score_id = isset($post_data['extra_score_id'])?$post_data['extra_score_id']:0;
    	$data = array(
                    "result"=>$post_data['result'],
                    "market_name"=>$post_data['market_name'],
                    "score"=>$post_data['score'],
                    "extra_score_id"=>$extra_score_id,
                    "updated_by"=>1,
                    "updated_date"=>format_date(),
                    "market_status"=>"cls"
                );
    	$nb_wd = range(1,14); //nb and wd ids
        if( $post_data['result'] == 7 && in_array($post_data['extra_score_id'],$nb_wd ) && $post_data['pre_result']==0 ) {
            $extra_ball = $this->create_new_sub_ball($post_data['over_ball']);
            if($extra_ball){
            	$data['over_ball'] = $extra_ball;
            }
        }
        elseif( $post_data['result'] == 7 && in_array($post_data['extra_score_id'],$nb_wd ) && $post_data['pre_result']!=7 ) {
            $extra_ball = $this->create_new_sub_ball($post_data['over_ball']);
            if($extra_ball){
            	$data['over_ball'] = $extra_ball;
            }
        }
        elseif($post_data['pre_result']==7 && !in_array($post_data['pre_extra_score_id'],$nb_wd) && in_array($post_data['extra_score_id'],$nb_wd)){
        	$extra_ball = $this->create_new_sub_ball($post_data['over_ball']);
            $data['over_ball'] = $extra_ball;
        }
    	$this->db->where(array('league_id'=>$post_data['league_id'],'season_game_uid'=>$post_data['season_game_uid'],'market_id'=>$post_data['market_id'],'inn_over'=>$post_data['inn_over'],'over_ball'=>$post_data['over_ball']));
    	$this->db->update(MARKET_ODDS,$data);
    	return $this->db->affected_rows();
    }
    //create sub ball incase wideball or noball
    public function create_new_sub_ball($over_ball){
    	$post_data = $this->input->post();
    	$over_ball = (float)$over_ball; 
        $next = .1;
        $next_ball = $over_ball+$next;
    	$this->db->select('over_ball');
    	$this->db->from(MARKET_ODDS);
    	$this->db->where(array('season_game_uid'=>$post_data['season_game_uid'],'inn_over'=>$post_data['inn_over'],'league_id'=>$post_data['league_id'],'over_ball >='=>$over_ball,'over_ball <'=>$next_ball));
    	$this->db->order_by('over_ball','DESC');
    	$this->db->order_by('display_order','DESC');
    	$last_sub_ball = $this->db->get()->row_array();
    	if(!empty($last_sub_ball)){
    		$sub_point  = .01;
        	$extra_ball = (float)$last_sub_ball['over_ball']+$sub_point;
        	return $extra_ball;
    	}else{
    		return false;
    	}
    }

    public function get_prediction_details(){
    	$post_data = $this->input->post();
    	$this->db->select("UP.market_id,UP.odds_id,UP.second_odds_id,UP.is_correct,MK.over_ball,UP.points,UP.score,MO.abbr as prediction,IFNULL(MO1.abbr,'') as second_prediction",FALSE)
			->from(USER_PREDICTION.' AS UP')
			->join(MARKET_ODDS.' AS MK','MK.market_id =UP.market_id','INNER')
			->join(MASTER_ODDS.' AS MO','MO.odds_id =UP.odds_id','INNER')
			->join(MASTER_ODDS.' AS MO1','MO1.odds_id =UP.second_odds_id','LEFT')
			->where('user_team_id',$post_data['user_team_id'])
			->group_by("UP.predict_id")
			->order_by("MK.over_ball","ASC");
		$prediction = $this->db->get()->result_array();
		return $prediction;
    }

    public function get_collection_details($collection_id)
	{
		$sql = $this->db->select("CM.*,IFNULL(CM.timer_date,'') as timer_date,S.home_uid,S.away_uid,S.season_game_uid,S.season_scheduled_date,L.sports_id,IFNULL(T1.display_team_abbr,T1.team_abbr) AS home,IFNULL(T2.display_team_abbr,T2.team_abbr) AS away,IFNULL(T1.feed_flag,T1.flag) AS home_flag,IFNULL(T2.feed_flag,T2.flag) AS away_flag,IFNULL(L.league_display_name,L.league_name) AS league_abbr,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,over_time", FALSE)
				->from(COLLECTION. " AS CM")
				->join(SEASON.' S','S.season_game_uid = CM.season_game_uid AND S.league_id = CM.league_id','INNER')
				->join(LEAGUE.' L','L.league_id = S.league_id','INNER')
				->join(TEAM.' T1','T1.team_uid = S.home_uid AND L.sports_id=T1.sports_id','LEFT')
				->join(TEAM.' T2','T2.team_uid = S.away_uid AND L.sports_id=T2.sports_id','LEFT')
				->where("CM.collection_id", $collection_id)
				->get();
		$result = $sql->row_array();
		return $result;
	}

	/**
     * used for get next over details
     * @param array $post_data
     * @return array
     */
    public function get_next_over($post_data) {
        $this->db->select("CM.collection_id,CM.collection_name,CM.season_game_uid,CM.status,CM.league_id,CONVERT(SUBSTRING(CM.inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(CM.inn_over,3), SIGNED INTEGER) as overs,CONVERT(REGEXP_REPLACE(CM.inn_over,'[^0-9]',''),UNSIGNED) as inn_over_val", false)
                ->from(COLLECTION." as CM")
                ->where_in("CM.status",array("0","1"))
                ->where("CM.league_id", $post_data['league_id'])
                ->where("CM.season_game_uid", $post_data['season_game_uid'])
                ->where("CM.inn_over != ", $post_data['inn_over']);
        $this->db->order_by("inning", "ASC");
        $this->db->order_by("overs", "ASC");
        $this->db->order_by("inn_over_val", "ASC");
        $this->db->limit(1);
        $result = $this->db->get()->row_array();
        return $result;
    }

    public function get_over_contests($collection_ids){
    	$this->db->select('collection_id,GROUP_CONCAT( contest_template_id) AS template',FALSE);
    	$this->db->from(CONTEST);
    	$this->db->where_in('collection_id',$collection_ids);
    	$this->db->group_by('collection_id');
    	$contest_list = $this->db->get()->result_array();
    	if(!empty($contest_list)){
    		$contest_list = array_column($contest_list,NULL,'collection_id');
    	}
    	return $contest_list;
    }
	
}
/* End of file Season_model.php */
/* Location: ./application/models/Season_model.php */