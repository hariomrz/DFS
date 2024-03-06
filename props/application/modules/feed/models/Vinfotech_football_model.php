<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
class Vinfotech_football_model extends FEED_Model
{
	private $api;
	public $db;
	public $season_detail = array();
	function __construct()
	{
		parent::__construct();
		$this->db	= $this->load->database('props_db', TRUE);
		$this->api = $this->get_sports_config_detail('football','vinfotech');
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
			}
		}
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
					->from(LEAGUE." AS L")
					->join(MASTER_SPORTS." AS MS","MS.sports_id=L.sports_id AND MS.status = 1")
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
					->from(LEAGUE ." AS L")
					->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.status = 1")
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
			$upcoming_day = date("Y-m-d H:i:s", strtotime(format_date()." +15 days"));	
            //echo "<pre>";print_r($upcoming_day);die;
			foreach ($league_data as $league)
			{
			   	$url = $this->api['api_url']."get_season?league_uid=".$league['league_uid']."&hd=".$hd."&token=".$this->api['access_token'];
				//echo $url ;die;
				$season_data = @file_get_contents($url);
				//echo "<pre>";print_r($league_data);echo "<br><br>";
				$season_array = @json_decode(($season_data), TRUE);	
				
				//echo "<pre>";print_r($season_array);die;
				if(!empty($season_array['response']['data']))
				{
					if($season_array['status'] == 'error')
					{
						exit($season_array['response']);
					}

					$match_data = $temp_match_array	= array();
					$season_keys = array();
					$available_matches = $this->get_available_matches($sports_id);
					foreach ($season_array['response']['data'] as $key => $value) 
					{
						//echo "<pre>";print_r($value);die;
						$tmp_scheduled_date = $value['season_scheduled_date'];
						if ($tmp_scheduled_date < format_date() && $hd == '') 
						{
                        	continue;
                        }

                        if ($tmp_scheduled_date > $upcoming_day && $hd == '') 
						{
                        	continue;
                        }

						$home_id = @$team_ids[$value['home_uid']];
	                    $away_id = @$team_ids[$value['away_uid']];
	                    if($home_id == '' || $away_id == '' )
                       {
                       		continue;
                       }

                       	if(!in_array($value['season_game_uid'],$available_matches["matchids"]) && (in_array($home_id, $available_matches["teamids"]) || in_array($away_id, $available_matches["teamids"])) && $hd == ''){
                            //echo "<pre>";
                            //echo "Match for teams $home_id or $away_id is already in progress ";
                           // log_message("error","TEAM MATCH ALREADY EXISTS | ".$value['season_game_uid']." | ($home_id) | ($away_id)");
                            $available_matches["teamids"][] = $home_id;
	                        $available_matches["teamids"][] = $away_id;
                            continue;
                        }

                   		$season_info = $this->get_single_row("is_updated_playing,delay_by_admin,delay_minute,delay_message", SEASON, array("season_game_uid" => $value['season_game_uid'],"league_id" => $league['league_id']));
						
						
						$season_k = $league['league_id'].'_'.$value['season_game_uid'];	//echo "<pre>";print_r($value);die;
						$temp_match_array[$season_k] = array(
                                            "league_id" 		=> $league['league_id'],
                                            "season_game_uid" 	=> $value['season_game_uid'],
                                            //"title" 				=> $value['title'],
                                            //"subtitle" 				=> $value['subtitle'],
                                            "venue" 				=> $value['venue'],
                                            "year" 				=> $value['year'],
                                            "type" 				=> $value['type'],
                                            //"format" 			=> $value['format'],
                                            "feed_date_time" 	=> $value['feed_date_time'],
                                            "scheduled_date"    => $tmp_scheduled_date,
                                            "home_id" 			=> $home_id, 
                                            "away_id" 			=> $away_id,
                                            //"delay_minute" 		=> @$value['delay_minute'],
                                            //"delay_message" 	=> @$value['delay_message'],
                                    );
						$available_matches["matchids"][] = $value['season_game_uid'];
                        $available_matches["teamids"][] = $home_id;
                        $available_matches["teamids"][] = $away_id;
	                        
						$match_data[$season_k] = $temp_match_array[$season_k];
						$season_keys[] = $season_k;
					}
					if (!empty($match_data))
					{
						//echo "<pre>";print_r(count($match_data));die;
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
			//All team uid with team id
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
				//echo "<pre>";print_r($player_data);die;	
				$player_array = @json_decode(($player_data), TRUE);
				
				//echo "<pre>";print_r($player_array);die;
				if(!empty($player_array['response']['data']))
				{
					if($player_array['status'] == 'error')
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
                        //$player_string = implode(",",$player_uid_arr);
                        $player_string = implode(',',array_map(function($n){return '\''.$n.'\'';},$player_uid_arr));
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
					if($match_array['status'] == 'error')
					{
						exit($match_array['response']);
					}	
					
					
					//echo "<pre>";print_r($match_array);die;
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
	                	$player_uid_array = array_column($match_array['response']['data'], "player_uid");
						//echo "<pre>";print_r($player_uid_array);die;
	                	$player_ids = $this->get_player_data_by_player_uids($sports_id,$player_uid_array);

						foreach ($match_array['response']['data'] as $key => $value) 
						{
							$player_id = @$player_ids[$value['player_uid']];
	                        $team_id = @$team_ids[$value['team_uid']];
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
		                        
		                        "passing_yards" 			=> $value['passing_yards'],
								"passing_touch_downs" 		=> $value['passing_touch_downs'],
								"passing_interceptions" 		=> $value['passing_interceptions'],
								"passing_two_pt" 			=> $value['passing_two_pt'],
								"rushing_yards"                    => $value['rushing_yards'],
								"rushing_touch_downs" 		=> $value['rushing_touch_downs'],
								"rushing_two_pt" 			=> $value['rushing_two_pt'],
								"receiving_yards"                  => $value['receiving_yards'],
								"receptions"                       => $value['receptions'],
								"receiving_touch_downs" 		=> $value['receiving_touch_downs'],
								"receiving_two_pt" 			=> $value['receiving_two_pt'],
								"fumbles_touch_downs" 		=> $value['fumbles_touch_downs'],
								"fumbles_lost" 			=> $value['fumbles_lost'],
								"fumbles_recovered" 			=> $value['fumbles_recovered'],
								"interceptions_yards" 		=> $value['interceptions_yards'],
								"interceptions_touch_downs" 	=> $value['interceptions_touch_downs'],
								"interceptions" 			=> $value['interceptions'],
								"kick_returns_yards" 		=> $value['kick_returns_yards'],
								"kick_returns_touch_downs" 		=> $value['kick_returns_touch_downs'],
								"punt_returns_yards" 		=> $value['punt_returns_yards'],
								"punt_return_touch_downs" 		=> $value['punt_return_touch_downs'],
								"field_goals_made" 			=> $value['field_goals_made'],
								"field_goals_from_1_19_yards" 	=> $value['field_goals_from_1_19_yards'],
								"field_goals_from_20_29_yards" 	=> $value['field_goals_from_20_29_yards'],
								"field_goals_from_30_39_yards" 	=> $value['field_goals_from_30_39_yards'],
								"field_goals_from_40_49_yards" 	=> $value['field_goals_from_40_49_yards'],
								"field_goals_from_50_yards" 	=> $value['field_goals_from_50_yards'],
								"extra_points_made" 			=> $value['extra_points_made'],
								"extra_point_blocked" 		=> $value['extra_point_blocked'],
								"field_goals_blocked" 		=> $value['field_goals_blocked'],
								"defensive_interceptions" 		=> $value['defensive_interceptions'],
								"defensive_fumbles_recovered" 	=> $value['defensive_fumbles_recovered'],
								"defensive_kick_return_touchdowns" => $value['defensive_kick_return_touchdowns'],
								"defensive_punt_return_touchdowns" => $value['defensive_punt_return_touchdowns'],
								"sacks" 				=> $value['sacks'],
								"safeties" 				=> $value['safeties'],
								"defensive_touch_downs" 		=> $value['defensive_touch_downs'],
								"defence_turnovers" 			=> $value['defence_turnovers'],
								"points_allowed" 			=> $value['points_allowed'],

		                        "updated_at" => format_date()
		                    );
							//echo "<pre>";print_r($score_data);die;
						}

						if(!empty($score_data))
						{
							//echo "<pre>";print_r($score_data);die;
							$this->replace_into_batch(STATS_FOOTBALL, array_values($score_data));
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
						if(!empty($match_info['score_data']) && 
							is_string($match_info['score_data']) && is_array(json_decode($match_info['score_data'], true)))
						{
							$final_score_card = array();
							$score_card = json_decode($match_info['score_data'], true);
							//echo "<pre>";print_r($score_card);die;
							if(isset($score_card['home_score']))
							{
								$final_score_card[$home_id] = array(
										"score" => $score_card['home_score']
																);
							}
							if(isset($score_card['away_score']))
							{
								$final_score_card[$away_id] = array(
										"score" => $score_card['away_score']
																);
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

    
	

	
    public function save_props($sports_id,$season_game_uid)
    {
    	$url = "http://".ML_SERVER_API_URL."/cron/football_props/".$season_game_uid;
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
						//echo "<pre>";print_r($key);die;
						//to chamge props name
						$props_name = strtolower($key);
						
						if(!isset($value[$props_name]) && @$value[$props_name] == '')
						{
							continue;
						}	
						//echo "<pre>";print_r($props_name);//die;
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
 				 $this->db->select("L.lineup_id,L.user_team_id,L.season_prop_id,L.type,L.value,SP.prop_id,SP.player_id,MP.fields_name,SF.passing_yards,SF.rushing_yards,SF.receiving_yards,SF.receiving_touch_downs")
	                ->from(LINEUP." AS L")
	                ->join(SEASON_PROPS." AS SP","SP.season_prop_id = L.season_prop_id AND SP.status = 1 AND SP.season_id = ".$season_id,"INNER")
	                ->join(MASTER_PROPS." AS MP","MP.prop_id = SP.prop_id AND MP.sports_id = ".$sports_id,"INNER")
	                ->join(STATS_FOOTBALL." AS SF","SF.player_id = SP.player_id AND SF.season_id = SP.season_id AND SF.season_id = ".$season_id,"LEFT")
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
		        		//passing_yards
		        		if($lp['passing_yards'] != '' && $lp['fields_name'] == 'passing_yards' && $lp['passing_yards'] > $prop_value && $lp['type'] == "1"){
		        			$status = 1;
		        			$stats = $lp['passing_yards'];
		        		}elseif($lp['passing_yards'] != '' && $lp['fields_name'] == 'passing_yards' && $lp['passing_yards'] < $prop_value && $lp['type'] == "2"){
		        			$status = 1;
		        			$stats = $lp['passing_yards'];
		        		}elseif($lp['passing_yards'] != '' && $lp['fields_name'] == 'passing_yards'){
		        			$status = 2;
		        			$stats = $lp['passing_yards'];
		        		}elseif($lp['fields_name'] == 'passing_yards'){
		        			$status = 3;
		        		}

		        		//rushing_yards
		        		if($lp['rushing_yards'] != '' && $lp['fields_name'] == 'rushing_yards' && $lp['rushing_yards'] > $prop_value && $lp['type'] == "1"){
		        			$status = 1;
		        			$stats = $lp['rushing_yards'];
		        		}elseif($lp['rushing_yards'] != '' && $lp['fields_name'] == 'rushing_yards' && $lp['rushing_yards'] < $prop_value  && $lp['type'] == "2"){
		        			$status = 1;
		        			$stats = $lp['rushing_yards'];
		        		}elseif($lp['rushing_yards'] != '' && $lp['fields_name'] == 'rushing_yards'){
		        			$status = 2;
		        			$stats = $lp['rushing_yards'];
		        		}elseif($lp['fields_name'] == 'rushing_yards'){
		        			$status = 3;
		        		}

		        		//receiving_yards
		        		if($lp['receiving_yards'] != '' && $lp['fields_name'] == 'receiving_yards' && $lp['receiving_yards'] > $prop_value && $lp['type'] == "1"){
		        			$status = 1;
		        			$stats = $lp['receiving_yards'];
		        		}elseif($lp['receiving_yards'] != '' && $lp['fields_name'] == 'receiving_yards' && $lp['receiving_yards'] < $prop_value && $lp['type'] == "2"){
		        			$status = 1;
		        			$stats = $lp['receiving_yards'];
		        		}elseif($lp['receiving_yards'] != '' && $lp['fields_name'] == 'receiving_yards'){
		        			$status = 2;
		        			$stats = $lp['receiving_yards'];
		        		}elseif($lp['fields_name'] == 'receiving_yards'){
		        			$status = 3;
		        		}

		        		//receiving_touch_downs
		        		if($lp['receiving_touch_downs'] != '' && $lp['fields_name'] == 'receiving_touch_downs' && $lp['receiving_touch_downs'] > $prop_value && $lp['type'] == "1"){
		        			$status = 1;
		        			$stats = $lp['receiving_touch_downs'];
		        		}elseif($lp['receiving_touch_downs'] != '' && $lp['fields_name'] == 'receiving_touch_downs' && $lp['receiving_touch_downs'] < $prop_value && $lp['type'] == "2"){
		        			$status = 1;
		        			$stats = $lp['receiving_touch_downs'];
		        		}elseif($lp['receiving_touch_downs'] != '' && $lp['fields_name'] == 'receiving_touch_downs'){
		        			$status = 2;
		        			$stats = $lp['receiving_touch_downs'];
		        		}elseif($lp['fields_name'] == 'receiving_touch_downs'){
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