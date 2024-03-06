<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
class Vinfotech_soccer_model extends FEED_Model
{
	private $api;
	public $db;
	public $atleast_minutes_played = 55;
	public $season_detail = array();
	function __construct()
	{
		parent::__construct();
		$this->db	= $this->load->database('trade_db', TRUE);
		$this->api = $this->get_sports_config_detail('soccer','vinfotech');
	}

	
	/**
     * function used for fetch all recent league from feed
     * @param int $sports_id
     * @return boolean
     */
	public function get_recent_league($sports_id)
    {
    	//check sports active or not
    	$sports_data = $this->get_single_row("sports_id", MASTER_SPORTS, array('sports_id' => $sports_id,"status" => '1'));
    	if(!isset($sports_data['sports_id']) || $sports_data['sports_id'] == '')
    	{
    		exit('Sport not active');
    	}
    	
    	$url = $this->api['api_url']."get_recent_league?token=".$this->api['access_token'];
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
    public function get_team($sports_id,$league_id)
    {
    	$current_date = format_date();
		$this->db->select("L.league_id,L.league_uid,L.sports_id")
					->from(LEAGUE. " AS L")
					->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.status = 1")
					->where("L.sports_id", $sports_id)
					->where("L.status", 1)
					->where("L.end_date >= ", $current_date);
				if($league_id != '')
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
			if (!empty($league_data)) 
	        {
	            $team_img_arr = array();
	            $data = array();    
	            $team_key = array();
	            $get_all_team_data = $this->get_all_table_data("team_uid,flag,jersey", TEAM, array('sports_id' => $sports_id)); 
	            $team_flag = array_column($get_all_team_data, 'flag','team_uid');
	            $team_jersey = array_column($get_all_team_data, 'jersey','team_uid');
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
	                            "flag" => $value['flag'],
	                            "jersey" => $value['jersey'],
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
		}	
		return;	
    }

    /**
     * function used for fetch schedule from feed
     * @param int $sports_id
     * @return boolean
     */
    public function get_season($sports_id)
    {
    	$current_date = format_date();
		$this->db->select("L.league_id,L.league_uid,L.sports_id")
					->from(LEAGUE." AS L")
					->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.status = 1")
					->where("L.sports_id", $sports_id)
					->where("L.status", 1)
					->where("L.end_date >= ", $current_date)
					->order_by("L.league_id","DESC");
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
			   	$url = $this->api['api_url']."get_season?league_uid=".$league['league_uid']."&token=".$this->api['access_token'];
				//echo $url ;die;
				$season_data = @file_get_contents($url);
				//echo "<pre>";print_r($league_data);echo "<br><br>";
				$season_array = @json_decode(($season_data), TRUE);	
				
				//echo "<pre>";print_r($season_array['response']['data']);die;
				if(!empty($season_array['response']['data']))
				{
					if($season_array['status'] == 'error')
					{
						exit($season_array['response']);
					}

					$match_data = $temp_match_array	= array();
					$season_keys = array();
					foreach ($season_array['response']['data'] as $key => $value) 
					{
						//echo "<pre>";print_r($value);die;
						$tmp_scheduled_date = $value['season_scheduled_date'];
						if(isset($value['delay_minute']) && $value['delay_minute'] > 0)
						{
							$tmp_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$value['delay_minute'].' minutes', strtotime($value['season_scheduled_date'])));
						}

						if ($tmp_scheduled_date < format_date()) 
						{
                        	continue;
                        }
						$home_id = @$team_ids[$value['home_uid']];
	                    $away_id = @$team_ids[$value['away_uid']];
	                    if($home_id == '' || $away_id == '' )
                       {
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
						
						$season_k = $league['league_id'].'_'.$value['season_game_uid'];	//echo "<pre>";print_r($value);die;
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
                                            "delay_minute" 		=> @$value['delay_minute'],
                                            "delay_message" 	=> @$value['delay_message'],
                                    );
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
    public function get_players($sports_id)
    {
		$rs = $this->db->select("L.league_id,L.league_uid,L.sports_id,S.season_id,S.is_published,S.home_id,S.away_id,S.season_game_uid,T1.team_uid AS home_uid,T2.team_uid AS away_uid,S.is_published", FALSE)
							->from(LEAGUE." AS L")
							->join(SEASON." AS S", "S.league_id = L.league_id", 'INNER')
							->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.status = 1")
							->join(TEAM." AS T1", "T1.team_id = S.home_id", 'INNER')
							->join(TEAM." AS T2", "T2.team_id = S.away_id", 'INNER')
							->where("L.status", 1)
							->where("L.sports_id", $sports_id)
							->where("S.scheduled_date >= ", format_date())
							->get();
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
                        $player_string = implode(",",$player_uid_arr);
                        $where = 'sports_id ='.$sports_id.  
                                    ' AND player_uid IN ('.$player_string.')';
                        $this->insert_or_update_on_exist($player_key,$data,$concat_key,PLAYER,'player_id',$update_ignore,$where);
                        echo "<pre>";echo "Players inserted for season id:<b>".$season_id."</b>";
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

					$team_ids = array();
			        $team_ids[$season_game['home_uid']] = $home_id;
			        $team_ids[$season_game['away_uid']] = $away_id;
					$goals = array();
					$goals_min = array();
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
		                        
		                        "team_goals" 				=> $value['team_goals'],
		                        "minutes"					=> $value['minutes_played'],
		                        "goals"						=> $value['goals'],
		                        "goals_minutes"				=> $value['goals_minutes'],
		                        "assists"					=> $value['assists'],
		                        "shots"						=> $value['shots_total'],
		                        "saves"						=> $value['saves'],
		                        "shots_on_goal"				=> $value['shots_on_goal'],
		                        "yellow_cards"				=> $value['yellow_cards'],
		                        "red_cards"					=> $value['red_cards'],
		                        "yellow_red_cards"			=> $value['yellow_red_cards'],
		                        "own_goals"					=> $value['own_goals'],
		                         "penalty_misses"			=> $value['penalty_misses'],
		                        "penalty_saves"				=> $value['penalty_saves'],
		                        "passes_completed"			=> $value['passes_completed'],
		                        "tackles_won"				=> $value['tackles_won'],
		                        "player_in_time"			=> $value['player_in_time'],
		                        "player_out_time"			=> $value['player_out_time'],
		                        "home_team_goal"			=> $value['home_team_goal'],
		                        "away_team_goal"			=> $value['away_team_goal'],
		                        "own_goals_minutes"			=> $value['own_goals_minutes'],
		                        "chancecreated"				=> $value['chancecreated'],
		                        "starting11"				=> $value['starting11'],
		                        "substitute"				=> $value['substitute'],
		                        "blockedshot"				=> $value['blockedshot'],
		                        "interceptionwon"			=> $value['interceptionwon'],
		                        "clearance"					=> $value['clearance'],
		                        "updated_at" => format_date()
		                    );
		                    $goals[$team_id] = $value['team_goals'];
		                    if(!empty($value['goals_minutes'])){
		                    	$goals_min = array_merge($goals_min,explode(",",$value['goals_minutes']));
		                    }
							//echo "<pre>";print_r($score_data);die;
						}

						if(!empty($score_data))
						{
							//echo "<pre>";print_r($score_data);die;
							$this->replace_into_batch(STATS_SOCCER, array_values($score_data));
							//delete player minute 0 minutes
							$this->db->where(array("season_id" => $season_id,"league_id" => $league_id,"minutes" => 0));
							$this->db->delete(STATS_SOCCER);
							
							$this->get_clean_sheet($season_id,$league_id);
							$this->get_goals_conceded($season_id,$league_id);
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

						$score_stats = array();
						$score_stats['win_team'] = @$team_ids[$match_info['winning_team_id']];
						$score_stats['goals'] = $goals;
						$score_stats['goal_minute'] = 0;
						if(!empty($goals_min)){
							$score_stats['goal_minute'] = min($goals_min);
						}
						$season_update_array['score_stats'] = json_encode($score_stats);

						//echo "<pre>";print_r($season_update_array);die;	

						$this->db->where("league_id",$league_id);
						$this->db->where("season_id",$season_id);
						$this->db->update(SEASON,$season_update_array);

						if($status == 2){
							$this->load->helper('queue');
			      			$server_name = get_server_host_name();
			      			$content = array();
							$content['url'] = $server_name."/trade/cron/update_question_status/".$season_id;
							add_data_in_queue($content,'cron');
						}
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
     * function used for calculate clean sheet
     * @param string $season_game_uid
     * @param int $league_id
     * @return boolean
     */
    private function get_clean_sheet($season_id,$league_id)
    {
        // clean sheet
         $rs = $this->db->select("P.position,P.player_id,SS.player_id,SS.team_id,SS.minutes,SS.player_in_time,SS.player_out_time,SS.goals_minutes,SS.team_goals,S.home_id,S.away_id")
                            ->from(STATS_SOCCER." AS SS")
                            ->join(PLAYER." AS P","P.player_id = SS.player_id", 'INNER')
                            ->join(SEASON." AS S","S.season_id = SS.season_id AND S.league_id = SS.league_id", 'INNER')
                            ->where("SS.season_id", $season_id)
                            ->where("SS.league_id", $league_id)
                            ->where_in("P.position", array('GK','MF','DF'))
                            ->get();
        $res = $rs->result_array();
        
        //echo "<pre>";print_r($res);die;
        if(isset($res) && !empty($res))
        {   
            $player_info = array();
            $player_info2 = array();
            foreach ($res as $key => $player) 
            {
                //check player goal
                if($player['team_id'] == $player['home_id'] )
                {
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(SS.goals_minutes), '0') AS goals_minutes",FALSE)
                                 ->from(STATS_SOCCER ." AS SS")
                                 ->where("SS.season_id", $season_id)
                                 ->where("SS.league_id", $league_id)
                                 ->where("SS.team_id", $player['away_id'])
                                 ->where("SS.goals_minutes >", 0)
                                 ->get();
                            //echo $this->db->last_query();     
                            $res = $rs->row_array();
                            
                    $all_goals_minutes  = $res['goals_minutes'];
                    // for own goal
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(SS.own_goals_minutes), '0') AS own_goals_minutes",FALSE)
                                 ->from(STATS_SOCCER ." AS SS")
                                 ->where("SS.season_id", $season_id)
                                 ->where("SS.league_id", $league_id)
                                 ->where("SS.team_id", $player['home_id'])
                                 ->where("SS.own_goals_minutes >", 0)
                                 ->get();
                            //echo $this->db->last_query();     
                            $res = $rs->row_array();
                    $all_own_goals_minutes  = $res['own_goals_minutes'];
                    
                    if( $all_goals_minutes == '0')
                    {
                        $all_goals = $all_own_goals_minutes;
                    }
                    elseif( $all_own_goals_minutes == '0')
                    {
                        $all_goals = $all_goals_minutes;
                    }
                    else
                    {
                        $all_goals = $all_goals_minutes.','.$all_own_goals_minutes;
                    }   

                    $goal_minutes = (explode(",", $all_goals));         

                    sort($goal_minutes);    
                    //echo "<pre>";print_r(($goal_minutes));die;
                    if($player['minutes'] >= $this->atleast_minutes_played)
                    {   
                        if($goal_minutes[0] == '0')
                        {
                            if($player['minutes'] >= $this->atleast_minutes_played)
                            {
                                $player_info[$player['player_id']] = $player['player_id'];
                            }

                            //echo "<pre>"; echo $player['minutes'];//die;
                        }
                        else
                        {   
                            foreach ($goal_minutes as $key => $value) 
                            {
                                //echo $value;die;
                                if($player['minutes'] >= $this->atleast_minutes_played)
                                {
                                    //echo "<pre>"; echo $player['player_in_time'].' - '.$player['player_out_time'];
                                    if($player['player_out_time'] == 0)
                                    {
                                        $player['player_out_time'] = 120;
                                    }   
                                    $arr = range($player['player_in_time'], $player['player_out_time']);
                                    //print_r($arr); die;
                                    if(!in_array($value, $arr))
                                    {
                                        $player_info[$player['player_id']] = $player['player_id'];
                                    }
                                    if(in_array($value, $arr))
                                    {
                                        //unset($player_info[$player['player_unique_id']]);
                                        $player_info2[$player['player_id']] = $player['player_id'];
                                    }
                                }   
                            }
                        }
                    }       
                }
                
                // away team
                if($player['team_id'] == $player['away_id'] )
                {   
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(SS.goals_minutes), '0') AS goals_minutes",FALSE)
                                 ->from(STATS_SOCCER ." AS SS")
                                 ->where("SS.season_id", $season_id)
                                 ->where("SS.league_id", $league_id)
                                 ->where("SS.team_id", $player['home_id'])
                                 ->where("SS.goals_minutes >", 0)
                                 ->get();
                            //echo $this->db->last_query();     
                            $res = $rs->row_array();
                    $all_goals_minutes  = $res['goals_minutes'];
                    // for own goal
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(SS.own_goals_minutes), '0') AS own_goals_minutes",FALSE)
                                 ->from(STATS_SOCCER ." AS SS")
                                 ->where("SS.season_id", $season_id)
                                 ->where("SS.league_id", $league_id)
                                 ->where("SS.team_id", $player['away_id'])
                                 ->where("SS.own_goals_minutes >", 0)
                                 ->get();
                            //echo $this->db->last_query();     
                            $res = $rs->row_array();
                    $all_own_goals_minutes  = $res['own_goals_minutes'];
                    
                    if( $all_goals_minutes == '0')
                    {
                        $all_goals = $all_own_goals_minutes;
                    }
                    elseif( $all_own_goals_minutes == '0')
                    {
                        $all_goals = $all_goals_minutes;
                    }
                    else
                    {
                        $all_goals = $all_goals_minutes.','.$all_own_goals_minutes;
                    }   

                    $goal_minutes = (explode(",", $all_goals)); 
                    sort($goal_minutes);    
                    //echo "<pre>";print_r(($goal_minutes));die;
                    if($player['minutes'] >= $this->atleast_minutes_played)
                    {
                        if($goal_minutes[0] == '0')
                        {
                            if($player['minutes'] >= $this->atleast_minutes_played)
                            {
                                $player_info[$player['player_id']] = $player['player_id'];
                            }   
                            //echo "yes";die;
                        }
                        else
                        {   
                            foreach ($goal_minutes as $key => $value) 
                            {
                                //echo $value;die;
                                if($player['minutes'] >= $this->atleast_minutes_played)
                                {
                                    if($player['player_out_time'] == 0)
                                    {
                                        $player['player_out_time'] = 120;
                                    }
                                    //echo "<pre>"; echo $player['player_in_time'].' - '.$player['player_out_time'];
                                    $arr = range($player['player_in_time'], $player['player_out_time']);
                                    //print_r($arr); 
                                    if(!in_array($value, $arr))
                                    {
                                        $player_info[$player['player_id']] = $player['player_id'];
                                    }
                                    if(in_array($value, $arr))
                                    {
                                        $player_info2[$player['player_id']] = $player['player_id'];
                                    }
                                }   
                            }
                        }
                    }       
                }
            }
            
            $player_info  = array_diff($player_info,$player_info2);
            //echo "<pre>";print_r($player_info);die;
            //first cleansheet update then add
            $update_clean_sheet = "UPDATE 
                                        ".$this->db->dbprefix(STATS_SOCCER)."
                                    SET 
                                        clean_sheets = 0
                                    WHERE
                                        season_id = '".$season_id."'
                                    AND
                                        league_id = ".$league_id."
                                  ";
            //echo "<br>";                    
            $this->db->query($update_clean_sheet);  

            //echo "<pre>";print_r($player_info);die;
            if(isset($player_info) && !empty($player_info))
            {
                //echo "<pre>";print_r($player_info);die;
                $player_unique_ids  = @implode($player_info,"','");
                //echo "<pre>";print_r($player_unique_ids);die;
                $update_clean_sheet = "UPDATE 
                                            ".$this->db->dbprefix(STATS_SOCCER)."
                                        SET 
                                            clean_sheets = 1
                                        WHERE
                                            player_id IN ('".$player_unique_ids."')
                                        AND
                                            season_id = '".$season_id."'
                                        AND
                                            league_id = ".$league_id."  
                                     ";
                $this->db->query($update_clean_sheet);  
            }               
        }
    }

    /**
     * function used for calculate goals conceded
     * @param string $season_game_uid
     * @param int $league_id
     * @return boolean
     */
    private function get_goals_conceded($season_id,$league_id)
    {
        $rs = $this->db->select("P.position,P.player_id,SS.player_id,SS.team_id,S.home_id,S.away_id,SS.minutes,SS.player_in_time,SS.player_out_time,SS.goals_minutes,SS.team_goals,SS.red_cards,SS.yellow_red_cards")
                            ->from(STATS_SOCCER ." AS SS")
                            ->join(PLAYER." AS P","P.player_id = SS.player_id", 'INNER')
                            ->join(SEASON." AS S","S.season_id = SS.season_id AND S.league_id = SS.league_id", 'INNER')
                            ->where("SS.season_id", $season_id)
                            ->where("SS.league_id", $league_id)
                            ->where_in("P.position", array('GK','DF'))
                            ->get();
        $res = $rs->result_array();
        //echo "<pre>";print_r($res);die;
        if(isset($res) && !empty($res))
        {   
            $player_info = array();
            $player_info2 = array();
            $goal_conceded = 0;
            foreach ($res as $key => $player) 
            {
                //check player goal
                if($player['team_id'] == $player['home_id'] )
                {
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(SS.goals_minutes), '0') AS goals_minutes",FALSE)
                                 ->from(STATS_SOCCER ." AS SS")
                                 ->where("SS.season_id", $season_id)
                                 ->where("SS.league_id", $league_id)
                                 ->where("SS.team_id", $player['away_id'])
                                 ->where("SS.own_goals" , 0)
                                 ->where("SS.goals_minutes >", 0)
                                 ->get();
                            //echo $this->db->last_query();     
                            $res = $rs->row_array();
                    
                    $all_goals_minutes  = $res['goals_minutes'];
                    // for own goal
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(SS.own_goals_minutes), '0') AS own_goals_minutes",FALSE)
                                 ->from(STATS_SOCCER ." AS SS")
                                 ->where("SS.season_id", $season_id)
                                 ->where("SS.league_id", $league_id)
                                 ->where("SS.team_id", $player['home_id'])
                                 ->where("SS.own_goals_minutes >", 0)
                                 ->get();
                            //echo $this->db->last_query();     
                            $res = $rs->row_array();
                    $all_own_goals_minutes  = $res['own_goals_minutes'];
                    
                    if( $all_goals_minutes == '0')
                    {
                        $all_goals = $all_own_goals_minutes;
                    }
                    elseif( $all_own_goals_minutes == '0')
                    {
                        $all_goals = $all_goals_minutes;
                    }
                    else
                    {
                        $all_goals = $all_goals_minutes.','.$all_own_goals_minutes;
                    }   

                    $goal_minutes = (explode(",", $all_goals)); 

                    sort($goal_minutes);    
                    //echo "<pre>";print_r(($goal_minutes));die;
                    
                    if(!empty($goal_minutes) && $goal_minutes['0'] !== '0')
                    {
                        
                        foreach ($goal_minutes as $key => $goal) 
                        {
                            if($player['player_out_time'] == 0 || $player['red_cards'] == 1 || $player['yellow_red_cards'] == 1)
                            {
                                $player['player_out_time'] = 120;
                            }   
                            $arr = range($player['player_in_time'], $player['player_out_time']);
                            
                            if(in_array($goal, $arr))
                            {
                                
                                if(array_key_exists($player['player_id'], $player_info))
                                {
                                    $player_info[$player['player_id']] = $player_info[$player['player_id']] + 1;
                                }else
                                {   
                                    $player_info[$player['player_id']] = $goal_conceded + 1;
                                }   
                            }
                        }
                    }
                }

                // away team
                if($player['team_id'] == $player['away_id'] )
                {
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(SS.goals_minutes), '0') AS goals_minutes",FALSE)
                                 ->from(STATS_SOCCER ." AS SS")
                                 ->where("SS.season_id", $season_id)
                                 ->where("SS.league_id", $league_id)
                                 ->where("SS.team_id", $player['home_id'])
                                 ->where("SS.own_goals" , 0)
                                 ->where("SS.goals_minutes >", 0)
                                 ->get();
                            //echo $this->db->last_query();     
                            $res = $rs->row_array();
                    $all_goals_minutes  = $res['goals_minutes'];
                    // for own goal
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(SS.own_goals_minutes), '0') AS own_goals_minutes",FALSE)
                                 ->from(STATS_SOCCER ." AS SS")
                                 ->where("SS.season_id", $season_id)
                                 ->where("SS.league_id", $league_id)
                                 ->where("SS.team_id", $player['away_id'])
                                 ->where("SS.own_goals_minutes >", 0)
                                 ->get();
                            //echo $this->db->last_query();     
                            $res = $rs->row_array();
                    $all_own_goals_minutes  = $res['own_goals_minutes'];
                    
                    if( $all_goals_minutes == '0')
                    {
                        $all_goals = $all_own_goals_minutes;
                    }
                    elseif( $all_own_goals_minutes == '0')
                    {
                        $all_goals = $all_goals_minutes;
                    }
                    else
                    {
                        $all_goals = $all_goals_minutes.','.$all_own_goals_minutes;
                    }   

                    $goal_minutes = (explode(",", $all_goals)); 
                    sort($goal_minutes);    
                    //echo "<pre>";print_r(($goal_minutes));die;
                    if(!empty($goal_minutes) && $goal_minutes['0'] !== '0')
                    {
                        foreach ($goal_minutes as $key => $goal) 
                        {
                            if($player['player_out_time'] == 0 || $player['red_cards'] == 1 || $player['yellow_red_cards'] == 1)
                            {
                                $player['player_out_time'] = 120;
                            }  
                            $arr = range($player['player_in_time'], $player['player_out_time']);
                            
                            if(in_array($goal, $arr))
                            {
                                
                                if(array_key_exists($player['player_id'], $player_info))
                                {
                                    $player_info[$player['player_id']] = $player_info[$player['player_id']] + 1;
                                }else
                                {   
                                    $player_info[$player['player_id']] = $goal_conceded + 1;
                                }   
                            }
                        }   
                    }       
                }
            }
            
            //echo "<pre>";print_r($player_info);die;
            //goal conceded
            $update_goal_conceded = "UPDATE 
                                        ".$this->db->dbprefix(STATS_SOCCER)."
                                    SET 
                                        goals_conceded = 0
                                    WHERE
                                        season_id = '".$season_id."'
                                    AND
                                        league_id = ".$league_id."  
                                    ";
            //echo "<br>";                    
            $this->db->query($update_goal_conceded);    

            //echo "<pre>";print_r($player_info);die;
            if(isset($player_info) && !empty($player_info))
            {
                foreach ($player_info as $key => $player) 
                {
                    $update_goal_conceded = "UPDATE 
                                                    ".$this->db->dbprefix(STATS_SOCCER)."
                                                SET 
                                                    goals_conceded = $player
                                                WHERE
                                                    player_id =  '".$key."' 
                                                AND
                                                    season_id = '".$season_id."'
                                                AND
                                                    league_id = ".$league_id."      
                                            ";
                    //echo "<br>";                
                    $this->db->query($update_goal_conceded);    
                }   
            }               
        }
        
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
           	$team_cache = 'team_'.$sports_id;
			$team_ids = $this->get_cache_data($team_cache);
			//echo "cache - <pre>";print_r($team_ids);die;
			if(empty($team_ids))
			{
				$team_ids = $this->get_team_id_with_team_uid($sports_id);
				$this->set_cache_data($team_cache,$team_ids,REDIS_24_HOUR);
				//echo "DB -<pre>";print_r($team_ids);die;
			}	

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

			$season_info = $this->get_single_row("season_id,is_updated_playing,delay_by_admin,delay_minute,scheduled_date,", SEASON, array("season_game_uid" => $season_game_uid,"league_id" => $league_info['league_id']));


			if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 0){
				$match_info[$season_k]['delay_minute'] = $value['delay_minute'];
				$match_info[$season_k]['delay_message'] = $value['delay_message'];
				if(isset($match_info[$season_k]['delay_minute']) && $match_info[$season_k]['delay_minute'] > 0 && $season_info['scheduled_date'] > format_date()){
					$match_info[$season_k]['scheduled_date'] = date('Y-m-d H:i:s', strtotime('+'.$match_info[$season_k]['delay_minute'].' minutes', strtotime($value['season_scheduled_date'])));
					$match_info[$season_k]['scheduled_date'] = date('Y-m-d', strtotime($value['season_scheduled_date']));
				}elseif($value['season_scheduled_date'] > format_date()){
					$match_info[$season_k]['scheduled_date'] = $value['season_scheduled_date'];
				}
			}else if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 1){
				$match_info[$season_k]['scheduled_date'] = date('Y-m-d H:i:s', strtotime('+'.$season_info['delay_minute'].' minutes', strtotime($match_info[$season_k]['scheduled_date'])));
				$match_info[$season_k]['scheduled_date'] = date('Y-m-d', strtotime($match_info[$season_k]['scheduled_date']));
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
}