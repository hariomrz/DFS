<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
class Vinfotech_cricket_model extends FEED_Model
{
	private $api;
	public $db;
	public $season_detail = array();
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('props_db', TRUE);
		$this->api = $this->get_sports_config_detail('cricket','vinfotech');
	}

	
	/**
     * function used for fetch all recent league from feed
     * @param int $sports_id
     * @return boolean
     */
	public function get_recent_league($sports_id,$hd)
    {
    	//check sports active or not
    	$sports_data = $this->get_single_row("sports_id", MASTER_SPORTS, array('sports_id' => $sports_id,"status" => '1'));
    	if(!isset($sports_data['sports_id']) || $sports_data['sports_id'] == '')
    	{
    		exit('Sport not active');
    	}
    	
    	$url = $this->api['api_url']."get_recent_league?hd=".$hd."&token=".$this->api['access_token'];
		//echo $url ;die;
		$league_data = @file_get_contents($url);	
		$league_array = @json_decode(($league_data), TRUE);
		//echo "<pre>";print_r($league_array);die;
		if(!empty($league_array['response']['data']))
		{
			if($league_array['status'] == 'error')
			{
				exit($league_array['response']);
			}
			$league_keys = array();
			foreach ($league_array['response']['data'] as $key => $value) 
			{
				if(!$value['league_last_date']){
					continue;
				}

				$key = $sports_id.'_'.$value['league_uid'];
				$data[$key] = array(
								"sports_id" => $sports_id,
								"league_uid" 	=> @$value['league_uid'],
								"league_abbr" 	=> @$value['league_abbr'],
								"league_name" 	=> @$value['league_name'],
								"display_name" => @$value['league_display_name'],
								"start_date" => @$value['league_schedule_date'],
								"end_date" => @$value['league_last_date']
							);
				$league_keys[] = $key;
			}
			if (!empty($data))
			{
				//echo "<pre>";print_r($data);die;
				$concat_key = 'CONCAT(sports_id,"_",league_uid)';
                $this->insert_or_update_on_exist($league_keys,$data,$concat_key,LEAGUE,'league_id');
				echo "All leagues (series) are inserted.";
				$this->get_league_tournament_type($sports_id,$hd);
			}
		}
		return;
    }


    private function get_league_tournament_type($sports_id,$hd)
    {
    	$current_date = format_date();
		$this->db->select("L.league_id,L.league_uid,L.sports_id")
					->from(LEAGUE. " AS L")
					->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.status = 1")
					->where("L.sports_id", $sports_id);
				if($hd == '1')
				{
					$past_date_time = date("Y-m-d H:i:s", strtotime($current_date." -110 days"));
					$this->db->where("L.end_date >= ", $past_date_time);
				}else
				{
					$this->db->where("L.end_date >= ", $current_date);
				}
		$this->db->order_by("L.league_id","DESC");
		$sql = $this->db->get();
		$league_data = $sql->result_array();
		//echo "<pre>";print_r($league_data);die;
		if (!empty($league_data))
		{
			$league_keys = array();
			$url = "http://".PL_API_URL."/cron/cron/get_league_list/".$sports_id;
	    	$league_list_data = @file_get_contents($url);
			$league_list_data= @json_decode(($league_list_data), TRUE);
			$league_array = @$league_list_data;
			$tournament_type = array_column($league_array['data'],"tournament_type","league_uid");
			//echo "<pre>";print_r($tournament_type);die;
			if(empty($tournament_type))
			{
				return true;
			}
			foreach ($league_data as $key => $value) 
			{
				$key = $sports_id.'_'.$value['league_uid'];
				$data[$key] = array(
								"sports_id" 		=> $sports_id,
								"league_uid" 		=> @$value['league_uid'],
								"tournament_type" 	=> @$tournament_type[$value['league_uid']],
							);
				$league_keys[] = $key;
			}
			
			if (!empty($data))
			{
				//echo "<pre>";print_r($data);die;
				$concat_key = 'CONCAT(sports_id,"_",league_uid)';
                $this->insert_or_update_on_exist($league_keys,$data,$concat_key,LEAGUE,'league_id');
				echo "<pre>";echo "All leagues (tournament_type) are updated.";
			}
	    }
	   //exit(); 
	   return;     
    }

    /**
     * function used for fetch all team from feed
     * @param int $sports_id
     * @return boolean
     */
    public function get_team($sports_id,$hd,$league_id)
    {
    	$current_date = format_date();
		$this->db->select("L.league_id,L.league_uid,L.sports_id")
					->from(LEAGUE. " AS L")
					->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.status = 1")
					->where("L.sports_id", $sports_id);					
				if($hd == '')
				{
					$this->db->where("L.end_date >= ", $current_date);
					$this->db->where("L.status", 1);
				}elseif($hd == '1' && $league_id != '')
				{
					$this->db->where("league_id", $league_id);
				}
		$this->db->order_by("L.league_id","DESC");
		$sql = $this->db->get();
		$league_data = $sql->result_array();
		//echo "<pre>";print_r( $league_data);die;
		if (!empty($league_data)) 
        {
            $team_img_arr = array();
            $data = array();    
            $team_key = array();
            $get_all_team_data = $this->get_all_table_data("team_uid,feed_flag,feed_jersey", TEAM, array('sports_id' => $sports_id)); 
            $team_flag = array_column($get_all_team_data, 'feed_flag','team_uid');
            $team_jersey = array_column($get_all_team_data, 'feed_jersey','team_uid');
            if($league_id != '')
            {
            	$team_flag = array();
            	$team_jersey = array();
            }
            //echo "Flag<pre>";print_r($team_flag);//die;
            //echo "Jersey<pre>";print_r($team_jersey);die;
            foreach ($league_data as $league) 
            {
                $url = $this->api['api_url'] . "get_teams?league_uid=" . $league['league_uid'] . "&token=" . $this->api['access_token'];
                //echo "<br> ".$url." <br>" ;
                $team_data = @file_get_contents($url);
                $team_array = @json_decode(($team_data), TRUE);
                //echo "<pre>";print_r($team_data);die;	
                
                //echo "<pre>";print_r($team_array);die;
                if (!empty($team_array['response']['data'])) 
                {
                    if($team_array['status'] == 'error') 
	                {
	                    exit($team_array['response']);
	                }
                    //array that will contain team flag and jersey url from feed.
                        
                    foreach ($team_array['response']['data'] as $key => $value) 
                    {
                        //echo "<pre>";print_r($value);die;
                        // Prepare team Data
                        $team_k = $league['sports_id'].'_'.$value['team_uid'];
                        $data[$team_k] = array(
                            "team_uid" => $value['team_uid'],
                            "sports_id" => $league['sports_id'],
                            "team_abbr" => $value['team_abbr'],
                            "team_name" => $value['team_name'],
                            "display_abbr" => $value['display_team_abbr'],
                            "display_name" => $value['display_team_name'],
                            "feed_flag" => $value['flag'],
                            "feed_jersey" => $value['jersey'],
                            "year" => $this->api['year']
                        );
                        $team_key[] = $team_k; 

                        $flag_url = explode(FEED_IMAGE_URL."/upload/flag/", $value['flag_url']);
                        $flag = $flag_url[1];
                        $jersey_url = explode(FEED_IMAGE_URL."/upload/jersey/", $value['jersey_url']);
                        $jersey = $jersey_url[1];
                        	

                        //if flag_url given from feed then add url in team img array
                        if (!empty(@$value['flag_url']) && (isset($team_flag) && @$team_flag[$value['team_uid']] != $flag) )
                        {
                            $team_img_arr[] = array('type' => 'flag', 'url' => $value['flag_url']);
                        }

                        //if jersey_url given from feed then add url in team img array
                        if (!empty(@$value['jersey_url']) && (isset($team_jersey) && @$team_jersey[$value['team_uid']] != $jersey) ) 
                        {
                            $team_img_arr[] = array('type' => 'jersey', 'url' => $value['jersey_url']);
                        }
                        
                    }
                }
            }

            //echo "<pre>";print_r($data);die;
            if(!empty($data)) 
            {
			    $concat_key = 'CONCAT(sports_id,"_",team_uid)';
                $this->insert_or_update_on_exist($team_key,$data,$concat_key,TEAM,'team_id');
                echo "<br>Teams inserted.";
			}	

            //call function which will upload team's flags and jerseys from feed s3 to project s3
            //echo "<pre>";print_r($team_img_arr);die;
            if(!empty($team_img_arr))
            {    
                $this->process_team_image_data_from_feed($team_img_arr);
            }    
        }
			
		return;	
    }

    /**
     * function used for fetch schedule from feed
     * @param int $sports_id
     * @return boolean
     */
    public function get_season($sports_id,$hd)
    {
    	$current_date = format_date();
		$this->db->select("L.league_id,L.league_uid,L.sports_id")
					->from(LEAGUE." AS L")
					->join(MASTER_SPORTS." AS MS","MS.sports_id=L.sports_id AND MS.status = 1")
					->where("L.sports_id", $sports_id);
				if($hd == 1)
				{
					$past_date_time = date("Y-m-d H:i:s", strtotime(format_date()." -110 days"));
					$this->db->where("L.end_date >= ", $past_date_time);
				}else{
					$this->db->where("L.end_date >= ", $current_date);
					$this->db->where("L.status", 1);
				}	
				
		$this->db->order_by("L.league_id","DESC");
		$sql = $this->db->get();
		$league_data = $sql->result_array();	
		//echo "<pre>";print_r( $league_data);die;
		if (!empty($league_data))
		{
			//All team uid with team id
			$team_ids = $this->get_team_id_with_team_uid($sports_id);
	        //echo "<pre>";print_r($team_ids);die;
			foreach ($league_data as $league)
			{
			   	$url = $this->api['api_url']."get_season?league_uid=".$league['league_uid']."&hd=".$hd."&token=".$this->api['access_token'];
				//echo $url ;die;
				$season_data = @file_get_contents($url);
				$season_array = @json_decode(($season_data), TRUE);
				
				if(!empty($season_array['response']['data']))
				{
					if($season_array['status'] == 'error')
					{
						exit($season_array['response']);
					}
					$match_data = $temp_match_array	= array();
					$season_keys = array();

					//Available match
					$available_matches = $this->get_available_matches($sports_id);
                    //echo "<pre>";print_r($available_matches);die;
					//echo "<pre>";print_r($season_array['response']['data']);die;

					foreach ($season_array['response']['data'] as $key => $value) 
					{
						//echo "<pre>";print_r($value);die;
						$tmp_scheduled_date = $value['season_scheduled_date'];
						if(isset($value['delay_minute']) && $value['delay_minute'] > 0)
						{
							$tmp_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$value['delay_minute'].' minutes', strtotime($value['season_scheduled_date'])));
						}
						if ($tmp_scheduled_date < format_date() && $hd == '') 
						{
                        	continue;
                        }
						$home_id = @$team_ids[$value['home_uid']];
	                    $away_id = @$team_ids[$value['away_uid']];
	                    if($home_id == '' || $away_id == '' )
                       {
                       		continue;
                       }

                       if($value['format'] != 4 && !in_array($value['season_game_uid'],$available_matches["matchids"]) && (in_array($home_id, $available_matches["teamids"]) || in_array($away_id, $available_matches["teamids"])) && $hd == ''){
                            //echo "<pre>";
                            //echo "Match for teams $home_id or $away_id is already in progress ";
                           // log_message("error","TEAM MATCH ALREADY EXISTS | ".$value['season_game_uid']." | ($home_id) | ($away_id)");
                            $available_matches["teamids"][] = $home_id;
	                        $available_matches["teamids"][] = $away_id;
                            continue;
                        }

                   		$season_info = $this->get_single_row("is_updated_playing,delay_by_admin,delay_minute,delay_message", SEASON, array("season_game_uid" => $value['season_game_uid'],"league_id" => $league['league_id']));
						
                  		
						//if match delay set from feed then modify match schedule date time
						if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 1){
							$value['delay_minute'] = $season_info['delay_minute'];
							$value['delay_message'] = $season_info['delay_message'];
						}

						if(isset($value['delay_minute']) && $value['delay_minute'] > 0){
							$value['season_scheduled_date'] = date('Y-m-d H:i:s', strtotime('+'.$value['delay_minute'].' minutes', strtotime($value['season_scheduled_date'])));
						}

						//for test matches
						if($value['format'] == 2)
						{
							continue;
						}			


						$season_k = $league['league_id'].'_'.$value['season_game_uid'];	

						$temp_match_array[$season_k] = array(
                                            "league_id" 		=> $league['league_id'],
                                            "season_game_uid" 	=> $value['season_game_uid'],
                                            "title" 				=> $value['title'],
                                            "subtitle" 				=> $value['subtitle'],
                                            "venue" 				=> $value['venue'],
                                            "year" 				=> $value['year'],
                                            "type" 				=> 'REG',
                                            "format" 			=> $value['format'],
                                            "feed_date_time" 	=> $value['feed_date_time'],
                                            "scheduled_date" => $value['season_scheduled_date'],
                                            "home_id" 			=> $home_id, 
                                            "away_id" 			=> $away_id,
                                            "delay_minute" 		=> $value['delay_minute'],
                                            "delay_message" 	=> $value['delay_message'],
                                    );

						
                            if($value['format'] != 4 ){
	                            $available_matches["matchids"][] = $value['season_game_uid'];
	                            $available_matches["teamids"][] = $home_id;
	                            $available_matches["teamids"][] = $away_id;
	                        }
                        

						$match_data[$season_k] = $temp_match_array[$season_k];
						$season_keys[] = $season_k;
					}
					if (!empty($match_data))
					{
						//echo "<pre>";print_r($match_data);die;
						$concat_key = 'CONCAT(league_id,"_",season_game_uid)';
                        $this->insert_or_update_on_exist($season_keys,$match_data,$concat_key,SEASON,'season_id');
						echo "<br>League id " .$league['league_id']." Matches (seasons) inserted.<br>";
					}
				}	
			}
		}	
		return;	
    }

    

    /**
     * function used for fetch players from feed
     * @param int $sports_id
     * @return boolean
     */
    public function get_players($sports_id,$hd)
    {
		$this->db->select("L.league_id,L.league_uid,L.sports_id,S.season_id,S.is_published,S.home_id,S.away_id,S.season_game_uid,T1.team_uid AS home_uid,T2.team_uid AS away_uid,S.is_published", FALSE)
							->from(LEAGUE." AS L")
							->join(SEASON." AS S", "S.league_id = L.league_id", 'INNER')
							->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.status = 1")
							->join(TEAM." AS T1", "T1.team_id = S.home_id", 'INNER')
							->join(TEAM." AS T2", "T2.team_id = S.away_id", 'INNER')
							->where("L.sports_id", $sports_id);
					if($hd == '')
					{
						$this->db->where("S.scheduled_date >= ", format_date());
						$this->db->where("L.status", 1);
					}		
							
		$rs = 	$this->db->get();
		$league_data = $rs->result_array();
		//echo "<pre>";print_r( $league_data);die;
		if (!empty($league_data))
		{
			$team_uid_array = array_merge(array_column($league_data, "home_uid"),array_column($league_data, "away_uid"));
			//echo "<pre>";print_r($team_uid_array);die;
            $team_ids = $this->get_team_id_with_team_uid($sports_id,'',$team_uid_array);
			//echo "<pre>";print_r($team_ids);die;
			foreach ($league_data as $league)
			{
				$season_game_uid = $league['season_game_uid'];
				$published = $league['is_published'];
				//echo "<pre>";print_r($league);die;
				$this->season_detail = $league;
			   	$url = $this->api['api_url']."get_players?league_uid=".$league['league_uid']."&match_id=".$season_game_uid."&token=".$this->api['access_token'];
			   	
				$player_data = @file_get_contents($url);
				$player_array = @json_decode(($player_data), TRUE);
				
				
				//echo "<pre>";print_r($player_array);die;
				if(!empty($player_array['response']['data']))
				{
					if ($player_array['status'] == 'error')
					{
						exit($player_array['response']);
					}
					$season_id = $league['season_id'];
                    $season_player_data = $data = array();
                    $player_key = $season_player_key = array();
                    $match_player_uid = array();
                    $player_id_arr = $player_uid_arr = array(); 
					foreach ($player_array['response']['data'] as $key => $value) 
					{
						if(empty($team_ids[$value['team_uid']])) 
						{
							continue;
						}

						// Prepare Player data
						$player_k = $sports_id.'_'.$value['player_uid'];
						$data[$player_k] = array(
										"player_uid" 	=> $value['player_uid'],
										"sports_id" 	=> $sports_id,
										"full_name" 	=> trim($value['full_name']),
										"first_name"	=> trim($value['first_name']),
										"last_name" 	=> trim($value['last_name']),
										"middle_name" 	=> trim(@$value['middle_name']),
										"nick_name" 	=> trim($value['display_name']),
										"country" 		=> trim(strtoupper(@$value['country'])),
										"position" 		=> trim(@$value['position']),
									);
						$player_key[] = $player_k;
						$player_uid_arr[] = $value['player_uid']; 
					}
					
					if(!empty($data)) 
                    {
                        //echo "<pre>";print_r($data);die;
                        $concat_key = 'CONCAT(sports_id,"_",player_uid)';
                        $update_ignore = array();
                        $player_string = implode(",",$player_uid_arr);
                        $where = 'sports_id ='.$sports_id.  
                                    ' AND player_uid IN ('.$player_string.')';

                        $this->insert_or_update_on_exist($player_key,$data,$concat_key,PLAYER,'player_id',$update_ignore,$where);
                       
                        echo "<pre>";echo "Players inserted for season id:<b>".$season_id."</b>";
                        //Save props
                        if($published == "0" && $hd == '')
                        {
                        	$this->save_props($sports_id,$season_game_uid);
                        }	
                    }
                    
				}		
			}

			//Update display name if its empty or null
			$this->db->where('display_name', NULL);
			$this->db->set('display_name','nick_name',FALSE);
			$this->db->update(PLAYER);
		}	
		return;	
    }

    /**
     * function used for fetch live games score from feed
     * @param int $sports_id
     * @return boolean
     */
    public function get_scores($sports_id)
    {
    	$current_game = $this->get_season_match_for_score($sports_id);
 		//echo '<pre>Live Match : ';print_r($current_game);die;
		if (!empty($current_game))
		{
			
			foreach ($current_game as $season_game)
			{
				//trasaction start
				$this->db->trans_strict(TRUE);
		        $this->db->trans_start();

					$home_id = $season_game['home_id'];
					$away_id = $season_game['away_id'];
					$season_id = $season_game['season_id'];
					$season_game_uid = $season_game['season_game_uid'];
					$league_id = $season_game['league_id'];
					$team_ids[$season_game['home_uid']] = $home_id;
					$team_ids[$season_game['away_uid']] = $away_id;
					$url = $this->api['api_url']."get_scores?match_id=".$season_game_uid."&token=".$this->api['access_token'];
					//echo $url ;die;
					$match_data = @file_get_contents($url);
					$match_array = @json_decode(($match_data), TRUE);
					//echo "<pre>";print_r($match_array);die;
					if($match_array['status'] == 'error')
					{
						exit($match_array['response']);
					}	
					
					$match_info = @$match_array['response']['match_info'];
					//echo "<pre>";print_r($match_info);die;
					if(!empty($match_info))
					{
						$scheduled_date = @$match_info['scheduled_date'];
						$match_status = @$match_info['status']; 
					}

					$score_data = array();
					if(!empty($match_array['response']['data']))
					{	
						//All player id with player id by season
						//echo "<pre>";print_r($match_array['response']['data']);die;
						$player_uid_array = array_column($match_array['response']['data'], "player_uid");
						//echo "<pre>";print_r($player_uid_array);die;
	                	$player_ids = $this->get_player_data_by_player_uids($sports_id,$player_uid_array);
	                	//echo "<pre>";print_r($player_ids);//die;
						foreach ($match_array['response']['data'] as $key => $value) 
						{
							$player_id = @$player_ids[$value['player_uid']];
							$team_id = @$team_ids[$value['team_uid']];
							//echo "<pre>";echo $key;echo "<pre>";print_r($player_id);die;
	                        if($player_id == '' || $team_id == '' )
	                        {
	                           continue;
	                        }  
							//echo "<pre>";print_r($value);die;
							$score_data[] = array(
		                        "league_id" 				=> $league_id,
		                        "season_id" 			    => $season_id,
		                        "team_id" 				    => $team_id,
		                        "player_id" 				=> $player_id,
		                        "innings" 					=> $value['innings'],
		                        "playing_11" 				=> $value['playing_11'],
		                        "is_captain" 				=> $value['is_captain'],
		                        "batting_runs"				=> $value['batting_runs'],
		                        "batting_dots"				=> $value['batting_dots'],
		                        "batting_balls_faced"		=> $value['batting_balls_faced'],
		                        "batting_fours"				=> $value['batting_fours'],
		                        "batting_sixes"				=> $value['batting_sixes'],
		                        "batting_strike_rate"		=> $value['batting_strike_rate'],
		                        "bowling_overs"				=> $value['bowling_overs'],
		                        "bowling_runs_given"		=> $value['bowling_runs_given'],
		                        "bowling_runs_extra"		=> $value['bowling_runs_extra'],
		                        "bowling_balls_delivered"	=> $value['bowling_balls_delivered'],
		                        "bowling_maiden_overs"		=> $value['bowling_maiden_overs'],
		                        "bowling_wickets"			=> $value['bowling_wickets'],
		                        "bowling_bowled"			=> $value['bowling_bowled'],
		                        "bowling_lbw"				=> $value['bowling_lbw'],
		                        "bowling_wides"				=> $value['bowling_wides'],
		                        "bowling_noballs"			=> $value['bowling_noballs'],
		                        "bowling_economy_rate"		=> $value['bowling_economy_rate'],
		                        "catch"						=> $value['catch'],
		                        "stumping"					=> $value['stumping'],
		                        "run_out"					=> $value['run_out'],
		                        "run_out_throw"				=> $value['run_out_throw'],
		                        "run_out_catch"				=> $value['run_out_catch'],
		                        "dismissed"					=> $value['dismissed'],
		                        "out_string"				=> $value['out_string'],
		                        "batting_order"				=> $value['batting_order'],
		                        "bowling_order"				=> $value['bowling_order'],
		                        "man_of_match"				=> $value['man_of_match'],
		                        "updated_at" => format_date()
		                    );
							//echo "<pre>";print_r($score_data);die;
						}

						if(!empty($score_data))
						{
							//echo "<pre>";print_r($score_data);die;
							$this->replace_into_batch(STATS_CRICKET, array_values($score_data));
							//delete player minute 0 minutes
							$this->db->where(array("season_id" => $season_id,"league_id" => $league_id,"innings" => 1,"playing_11" => 0));
							$this->db->delete(STATS_CRICKET);
							echo "<pre>";  echo "Match [<b>$season_id</b>] score updated";
						}
					}
					
					if(!empty($match_info))
					{
						//0-Not Started, 1-Live, 2-Completed, 3-Delay, 4-Canceled
						$status = 0;
						$match_closure_date = "";
						if($match_status == 1 )
						{
							$status = 1;
						}
						elseif($match_status == 3 )
						{
							$status = 1;
						}elseif($match_status == 4 )
						{
							$status = 4;
							$match_closure_date = format_date();
						}elseif($match_status == 2)
						{
							$player_count = count($this->get_player_data_by_player_uids($sports_id,$player_uid_array));
							if($player_count >= 14)
							{
								$status = 2;
								$match_closure_date = format_date();
							}	
						}

						//if match closure date provided from feed then update this on client db
						$season_update_array = array("status" => $status);
						if(!empty($match_info['score']) && 
							is_string($match_info['score']) && is_array(json_decode($match_info['score'], true)))
						{
							$final_score_card = array();
							$score_card = json_decode($match_info['score'], true);
							foreach ($score_card as $key => $sc) 
							{
								//echo $key;echo "<pre>";print_r($sc);//die;
								if(isset($sc['home_team_score']) && isset($sc['home_overs']) && isset($sc['home_wickets']))
								{
									$final_score_card[$key][$home_id] = array(
											"score" => $sc['home_team_score'],
											"overs" => $sc['home_overs'],
											"wickets" => $sc['home_wickets'],
																	);
								}
								if(isset($sc['away_team_score']) && isset($sc['away_overs']) && isset($sc['away_wickets']))
								{
									$final_score_card[$key][$away_id] = array(
											"score" => $sc['away_team_score'],
											"overs" => $sc['away_overs'],
											"wickets" => $sc['away_wickets'],
																	);
								}	
							}
							//echo "<pre>";print_r($final_score_card);die;	
							$season_update_array['score_data'] = json_encode($final_score_card);
						}


						if(isset($match_closure_date) && $match_closure_date != "")
						{
							$season_update_array['match_closure_date'] = $match_closure_date;
						}

						

						//echo "<pre>";print_r($season_update_array);die;	

						$this->db->where("league_id",$league_id);
						$this->db->where("season_id",$season_id);
						$this->db->update(SEASON,$season_update_array);
						//Foll of wicket inofrmation save in table 	
						/*if(!empty($match_info['fall_of_wickets']) && 
							is_string($match_info['fall_of_wickets']) && is_array(json_decode($match_info['fall_of_wickets'], true))
							)
						{	
							$fow[] =  array(
											"season_id" => $season_id,
											"fall_of_wickets" => $match_info['fall_of_wickets'],
										);
							//echo "<pre>";print_r($fow);die;
							$this->replace_into_batch(CRICKET_FOW, array_values($fow));
						}*/	
					}
				//trasaction end
			    $this->db->trans_complete();
		        if ($this->db->trans_status() === FALSE )
		        {
		            $this->db->trans_rollback();
		        }
		        else
		        {
		            $this->db->trans_commit();
		            
		        }		
			}
		}
		return;		
    }

	/**
     * This function used for get single match details from feed
     * @param string $season_game_uid
     * @param int $sports_id
     * @return boolean
     */
	public function get_season_details($season_game_uid,$sports_id)
    {
		$url = $this->api['api_url']."get_season_details?season_game_uid=".$season_game_uid."&token=".$this->api['access_token'];
		$season_data = @file_get_contents($url);
		if (!$season_data)
		{
			exit;
		}
		$season_array = @json_decode(($season_data), TRUE);
		//echo "<pre>";print_r($season_array['response']['data']);die;
		if(!empty($season_array['response']['data']))
		{
			$value = $season_array['response']['data'];
			$league_info = $this->get_single_row("league_id", LEAGUE, array("league_uid" => $value['league_uid']));
			if(empty($league_info)){
				exit;
			}

			//All team uid with team id
           	$team_uid_array = array($season_array['response']['data']['home_uid'],$season_array['response']['data']['away_uid']);
			//echo "<pre>";print_r($team_uid_array);die;
       		$team_ids = $this->get_team_id_with_team_uid($sports_id,'',$team_uid_array);
				

            $home_id = $team_ids[$value['home_uid']];
            $away_id = $team_ids[$value['away_uid']];
            if($home_id == '' || $away_id == '' )
	        {
	        	exit();
	        }

            $season_k = $league_info['league_id'].'_'.$value['season_game_uid'];
       		$match_info[$season_k] = array(
                                "league_id" 		=> $league_info['league_id'],
                                "season_game_uid" 	=> $value['season_game_uid'],
                                "title" 			=> $value['title'],
                                "subtitle" 			=> $value['subtitle'],
                                "venue" 			=> $value['venue'],
                                "year" 				=> $value['year'],
                                "type" 				=> 'REG',
                                "format" 			=> $value['format'],
                                "feed_date_time" 	=> $value['feed_date_time'],
                                "home_id" 			=> $home_id, 
                                "away_id" 			=> $away_id,
                        );
       		$season_keys[] = $season_k;

			$season_info = $this->get_single_row("season_id,is_updated_playing,delay_by_admin,delay_minute,scheduled_date", SEASON, array("season_game_uid" => $season_game_uid,"league_id" => $league_info['league_id']));


			if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 0){
				$match_info[$season_k]['delay_minute'] = $value['delay_minute'];
				$match_info[$season_k]['delay_message'] = $value['delay_message'];
				if(isset($match_info[$season_k]['delay_minute']) && $match_info[$season_k]['delay_minute'] > 0 && $season_info['scheduled_date'] > format_date()){
					$match_info[$season_k]['scheduled_date'] = date('Y-m-d H:i:s', strtotime('+'.$match_info[$season_k]['delay_minute'].' minutes', strtotime($value['season_scheduled_date'])));
				}elseif($value['season_scheduled_date'] > format_date()){
					$match_info[$season_k]['scheduled_date'] = $value['season_scheduled_date'];
				}
			}else if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 1){
				$match_info[$season_k]['scheduled_date'] = date('Y-m-d H:i:s', strtotime('+'.$season_info['delay_minute'].' minutes', strtotime($match_info[$season_k]['scheduled_date'])));
			}elseif($value['season_scheduled_date'] > format_date()){
				$match_info[$season_k]['scheduled_date'] = $value['season_scheduled_date'];
			}else{
				exit('Time over');
			}
			
			if(isset($season_info['is_updated_playing']) && $season_info['is_updated_playing'] == 0){
				if(isset($value['playing_list']) && $value['playing_list'] != "" && $value['playing_list'] != "[]"){
					$playing_11_player = $this->get_player_data_by_player_uids($sports_id,json_decode($value['playing_list']));
					$match_info[$season_k]['playing_list'] = json_encode(array_values($playing_11_player));
					$match_info[$season_k]['playing_announce'] = $value['playing_announce'];
					$match_info[$season_k]['playing_announce_date'] = format_date();
				}
				if(isset($value['substitute_list']) && $value['substitute_list'] != "" && $value['substitute_list'] != "[]"){
					$subtitute_11_player = $this->get_player_data_by_player_uids($sports_id,json_decode($value['substitute_list']));
					$match_info[$season_k]['substitute_list'] = json_encode(array_values($subtitute_11_player));
				}
			}

			$match_data = $match_info;
			//echo "<pre>";print_r($match_data);die;
			if (!empty($match_data))
			{
				$concat_key = 'CONCAT(league_id,"_",season_game_uid)';
                $this->insert_or_update_on_exist($season_keys,$match_data,$concat_key,SEASON,'season_id');
				
				echo "<br>Fixture id " .$value['season_game_uid']." Matches (seasons) inserted.<br>";
			}
		}
		return;	
    }

	   

    public function save_props($sports_id,$season_game_uid)
    {
    	
    	$url = "http://".ML_SERVER_API_URL."/cron/cricket_props/".$season_game_uid;
    	//echo $url ;die;
		$props_data = @file_get_contents($url);
		$props_array = @json_decode(($props_data), TRUE);
		$props = @$props_array['data']['props_list'];
		//echo "<pre>";print_r($props_array);die;
		if(!empty($props))
		{	
			$current_date = format_date();
			$player_uid_array = array_column($props, "player_uid");
			//echo "<pre>";print_r($player_uid_array);die;
	        $player_ids = $this->get_player_data_by_player_uids($sports_id,$player_uid_array);
			$team_uid_array = array_column($props, "team_uid");
			//echo "<pre>";print_r($team_uid_array);die;
            $team_ids = $this->get_team_id_with_team_uid($sports_id,'',$team_uid_array);
            //echo "<pre>";print_r($team_ids);die;
	        $this->db->select("S.season_id")
					->from(SEASON." AS S")
					->join(LEAGUE." AS L","L.league_id=S.league_id AND L.sports_id =".$sports_id)
					->where("L.status", 1)
					->where("S.season_game_uid", $season_game_uid)
					->where("S.scheduled_date >= ", $current_date);
				$sql = $this->db->get();
			$season_id = $sql->row("season_id");	
	        //echo "<pre>";print_r($player_ids);die;
	        if(!empty($team_ids) && !empty($player_ids) && !empty($season_id))
	        {
	        	$props_data = array();
	        	$props_key = array();
	        	$prop_id = "";
	        	$points = "0";
	        	$prop_ids = $this->get_master_props($sports_id); 
	        	//echo "<pre>";print_r($prop_ids);die;
	        	foreach($props as $key => $value) 
				{
					$player_id = @$player_ids[$value['player_uid']];
					$team_id = @$team_ids[$value['team_uid']];
					//echo "<pre>";print_r($value);die;
					if($player_id == "" || $team_id == "")
					{
						continue;
					}	
					foreach ($prop_ids as $key => $prop) 
					{
						$props_name = strtolower($key);
						
						if(!isset($value[$props_name]) && @$value[$props_name] == '')
						{
							continue;
						}	
						//echo "<pre>";print_r($prop);die;
						$props_k = $season_id."_".$player_id."_".$prop;
						$props_data[$props_k] = array(
											"season_id" => $season_id,
											"player_id" => $player_id,
											"prop_id" => $prop,
											"team_id" => $team_id,
											"position" => $value['position'],
											"points" => intval($value[$props_name])+0.5,
										);
						$props_key[]  = $props_k;

					}
					//echo "<pre>";print_r($props_data);die;	
				}
				//echo "<pre>";print_r($props_data);die;
				if(!empty($props_data))
				{
					$concat_key = 'CONCAT(season_id,"_",player_id,"_",prop_id)';
            		$this->insert_or_update_on_exist($props_key,$props_data,$concat_key,SEASON_PROPS,'season_prop_id');
            		
            		//mark fixture published
                	$this->db->where('season_id',$season_id);
                	$this->db->where('is_published',"0");
					$this->db->set('is_published','1');
					$this->db->update(SEASON);	
                	echo "<pre>";echo "Season props added for season id: ".$season_id;	
				}	
	        }
		}
		return true;	
    }

    /*Update lineup with value and check true or false*/
    public function update_lineup($sports_id) 
    {
        $current_game = $this->get_match_for_update_lineup($sports_id);
 		//echo '<pre>';print_r($current_game);die;
 		if(!empty($current_game))
 		{
 			foreach ($current_game as $key => $value) 
 			{
 				$season_game_uid = $value['season_game_uid'];
 				$season_id = $value['season_id'];
 				$season_status = $value['status'];
 				 $this->db->select("L.lineup_id,L.user_team_id,L.season_prop_id,L.type,L.value,SP.prop_id,SP.points,SP.player_id,SUM(SC.batting_balls_faced) AS ball_faced,SUM(SC.batting_runs) AS runs,SUM(SC.batting_fours) AS fours,SUM(SC.batting_sixes) AS sixes,SUM(SC.bowling_overs) AS overs,SUM(SC.bowling_wickets) AS wickets,MP.fields_name")
	                ->from(LINEUP." AS L")
	                ->join(SEASON_PROPS." AS SP","SP.season_prop_id = L.season_prop_id AND SP.status = 1 AND SP.season_id = ".$season_id,"INNER")
	                ->join(MASTER_PROPS." AS MP","MP.prop_id = SP.prop_id AND MP.sports_id = ".$sports_id,"INNER")
	                ->join(STATS_CRICKET." AS SC","SC.player_id = SP.player_id AND SC.season_id = SP.season_id AND SC.season_id = ".$season_id,"LEFT")
	                ->group_by("L.lineup_id");
                $sql = $this->db->get();
		        $lineups = $sql->result_array();
		        //echo '<pre>';print_r($lineups);die;
		        if(!empty($lineups))
		        {
		        	$lineup_arr = array();
		        	foreach ($lineups as $key => $lp) 
		        	{
		        		//echo '<pre>';print_r($lp);die;
		        		$prop_value = $lp['value'];
		        		$status = 0;
		        		$stats = 0;
		        		//runs
		        		if($lp['ball_faced'] >= 1 && $lp['fields_name'] == 'batting_runs' && $lp['runs'] > $prop_value && $lp['type'] == "1"){
		        			$status = 1;
		        			$stats = $lp['runs'];
		        		}elseif($lp['ball_faced'] >= 1 && $lp['fields_name'] == 'batting_runs' && $lp['runs'] < $prop_value && $lp['type'] == "2"){
		        			$status = 1;
		        			$stats = $lp['runs'];
		        		}elseif($lp['runs'] >= 0 && $lp['ball_faced'] >= 1 && $lp['fields_name'] == 'batting_runs'){
		        			$status = 2;
		        			$stats = $lp['runs'];
		        		}elseif($lp['fields_name'] == 'batting_runs'){
		        			$status = 3;
		        		}
		        		//six
		        		
		        		if($lp['ball_faced'] >= 1 && $lp['fields_name'] == 'batting_sixes' && $lp['sixes'] > $prop_value && $lp['type'] == "1"){
		        			$status = 1;
		        			$stats = $lp['sixes'];
		        		}else if($lp['ball_faced'] >= 1 && $lp['fields_name'] == 'batting_sixes' && $lp['sixes'] < $prop_value && $lp['type'] == "2"){
		        			$status = 1;
		        			$stats = $lp['sixes'];
		        		}elseif($lp['sixes'] >= 0 && $lp['ball_faced'] >= 1 && $lp['fields_name'] == 'batting_sixes'){
		        			$status = 2;
		        			$stats = $lp['sixes'];
		        		}elseif($lp['fields_name'] == 'batting_sixes'){
		        			$status = 3;
		        		}
		        		//four
		        		
		        		if($lp['ball_faced'] >= 1 && $lp['fields_name'] == 'batting_fours' && $lp['fours'] > $prop_value && $lp['type'] == "1"){
		        			$status = 1;
		        			$stats = $lp['fours'];
		        		}else if($lp['ball_faced'] >= 1 && $lp['fields_name'] == 'batting_fours' && $lp['fours'] < $prop_value && $lp['type'] == "2"){
		        			$status = 1;
		        			$stats = $lp['fours'];
		        		}elseif($lp['fours'] >= 0 && $lp['ball_faced'] >= 1 && $lp['fields_name'] == 'batting_fours'){
		        			$status = 2;
		        			$stats = $lp['fours'];
		        		}elseif($lp['fields_name'] == 'batting_fours'){
		        			$status = 3;
		        		}
		        		//wickets
		        		
		        		if($lp['overs'] > 0 && $lp['fields_name'] == 'bowling_wickets' && $lp['wickets'] > $prop_value && $lp['type'] == "1"){
		        			$status = 1;
		        			$stats = $lp['wickets'];
		        		}else if($lp['overs'] > 0 && $lp['fields_name'] == 'bowling_wickets' && $lp['wickets'] < $prop_value && $lp['type'] == "2"){
		        			$status = 1;
		        			$stats = $lp['wickets'];
		        		}elseif($lp['wickets'] >= 0 && $lp['overs'] > 0 && $lp['fields_name'] == 'bowling_wickets'){
		        			$status = 2;
		        			$stats = $lp['wickets'];
		        		}elseif($lp['fields_name'] == 'bowling_wickets'){
		        			$status = 3;
		        		}

		        		$lineup_arr[] = array("season_prop_id"=>$lp['season_prop_id'],"user_team_id"=>$lp['user_team_id'],"status"=>$status,"stats"=>$stats);
		        	}
		        	//echo "<pre>=====";print_r($lineup_arr);die;
		        	if(!empty($lineup_arr)){
		        		$lineup_data = array_chunk($lineup_arr, 999);
			          	foreach($lineup_data as $l_data){
				            $this->replace_into_batch(LINEUP, $l_data);
			          	}
		        	}
		        	echo "<pre>";echo "Lineup update for seasin id: ".$season_id;
		        }
		        //update status in season table for complete matches
	        	if($season_status == "2" || $season_status == "4")
	        	{
	        		$this->db->where('season_id', $season_id);
	        		$this->db->where('props_status',"0");
					$this->db->set('props_status','1');
					$this->db->update(SEASON);
	        	}	
 			}
 		}
 		return true;
    }

}