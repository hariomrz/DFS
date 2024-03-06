<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
class Vinfotech_model extends MY_Model
{
	private $api;
	public $db;
	public $season_detail = array();
	function __construct()
	{
		parent::__construct();
		$this->db	= $this->load->database('db_fantasy', TRUE);
		$this->api = $this->get_nfl_config_detail('vinfotech');
	}

	
	/**
     * function used for get match statistics data by game id
     * @param string $season_game_uid
     * @param int $sports_id
     * @param int $league_id
     * @return array
     */

	private function get_game_statistics_by_season_id($season_id,$sports_id,$league_id)
    {
        $sql = $this->db->select("GSF.*,PT.position", FALSE)
                ->from(GAME_STATISTICS_FOOTBALL . " AS GSF")
                ->join(PLAYER." AS P", "P.player_id = GSF.player_id AND P.sports_id = $sports_id ", 'INNER')
                ->join(PLAYER_TEAM." AS PT", "PT.player_id = P.player_id AND PT.player_id = GSF.player_id AND PT.season_id = '".$season_id."' ", 'INNER')
                ->where("GSF.season_id", $season_id)
                ->where("GSF.league_id", $league_id)
                ->group_by("P.player_id")
                ->get();

        $result = $sql->result_array();
        return $result;
    }




	/**
     * function used for fetch all recent league from feed
     * @param int $sports_id
     * @return boolean
     */
	public function get_recent_league($sports_id)
    {
    	//check sports active or not
    	$sports_data = $this->get_single_row("sports_id", MASTER_SPORTS, array('sports_id' => $sports_id,"active" => '1'));
    	if(!isset($sports_data['sports_id']) || $sports_data['sports_id'] == '')
    	{
    		exit('Sport not active');
    	}
    	
    	$url = $this->api['api_url']."get_recent_league?token=".$this->api['access_token'];
		//echo $url ;die;
		$league_data = @file_get_contents($url);	
		if (!$league_data)
		{
			exit;
		}
		$league_array = @json_decode(($league_data), TRUE);
		if($league_array['status'] == 'error')
		{
			exit("Feed token expire");
		}
		//echo "<pre>";print_r($league_array);die;
		if(!empty($league_array['response']['data']))
		{
			
			$league_keys = array();
			foreach ($league_array['response']['data'] as $key => $value) 
			{
				if(!$value['league_last_date']){
					continue;
				}

				$key = $sports_id.'_'.$value['league_uid'];
				$data[$key] = array(
								"league_uid" 	=> @$value['league_uid'],
								"league_abbr" 	=> @$value['league_abbr'],
								"league_name" 	=> @$value['league_name'],
								"league_display_name" => @$value['league_display_name'],
								"league_schedule_date" => @$value['league_schedule_date'],
								"league_last_date" => @$value['league_last_date'],
								"image" => @$value['image'],
								"sports_id" => $sports_id
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
		exit();	
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
					->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.active = 1")
					->where("L.sports_id", $sports_id)
					->where("L.active", 1)
					->where("L.league_last_date >= ", $current_date);
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
	                //echo "<br> ".$url." <br>" ;continue;
	                $team_data = @file_get_contents($url);
	                $team_array = @json_decode(($team_data), TRUE);
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
	                            "display_team_abbr" => $value['display_team_abbr'],
	                            "display_team_name" => $value['display_team_name'],
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
		}	
		exit();	
    }

    /**
     * function used for fetch schedule from feed
     * @param int $sports_id
     * @return boolean
     */
    public function get_season($sports_id)
    {
    	$current_date = format_date();
		$league_data = $this->get_all_table_data("league_id,league_uid, sports_id", LEAGUE, array('sports_id' => $sports_id, "active" => '1',"league_last_date >= "=>$current_date));
		//echo "<pre>";print_r( $league_data);die;
		if (!empty($league_data))
		{
			//All team uid with team id
			
			$team_ids = $this->get_team_id_with_team_uid($sports_id);
			//echo "<pre>";print_r($team_ids);die;
			$next_date_time = date("Y-m-d H:i:s", strtotime(format_date()." +15 days"));
			foreach ($league_data as $league)
			{
			   	$url = $this->api['api_url']."get_season?league_uid=".$league['league_uid']."&token=".$this->api['access_token'];
				// echo $url ;die;
				$season_data = @file_get_contents($url);
				//echo "<pre>";print_r($league_data);echo "<br><br>";	
				if (!$season_data)
				{
					continue;
				}
				$season_array = @json_decode(($season_data), TRUE);
				//echo "<pre>";print_r($season_array);die;
				if(@$season_array['status'] == 'error')
				{
					exit("Feed token expire");
				}
				//echo "<pre>";print_r($season_array['response']['data']);die;
				if(!empty($season_array['response']['data']))
				{
					$match_data = $temp_match_array	= array();
					$season_keys = array();
					foreach ($season_array['response']['data'] as $key => $value) 
					{
						//echo "<pre>";print_r($value);die;
						$tmp_scheduled_date = $value['season_scheduled_date'];
						
						if ($tmp_scheduled_date < format_date()) 
						{
                        	continue;
                        }

                        if($tmp_scheduled_date > $next_date_time)
                        {
                            continue;
                        } 
                        //$team_ids = $this->get_team_id_with_team_uid($sports_id,'',array($value['home_uid'],$value['away_uid']));
						//echo "<pre>";print_r($team_ids);die;
						$home_id = @$team_ids[$value['home_uid']];
	                    $away_id = @$team_ids[$value['away_uid']];
	                    if($home_id == '' || $away_id == '' )
	                    {
	                       	continue;
	                    }

                   		$season_info = $this->get_single_row("is_updated_playing,scoring_alert,delay_by_admin,notify_by_admin,delay_minute,delay_message,custom_message,2nd_inning_date,second_inning_update,season_scheduled_date", SEASON, array("season_game_uid" => $value['season_game_uid'],"league_id" => $league['league_id']));
												
						$season_k = $league['league_id'].'_'.$value['season_game_uid'];

						$temp_match_array = array(
                                            "league_id" 		=> $league['league_id'],
                                            "season_game_uid" 	=> $value['season_game_uid'],
                                            "year" 				=> $value['year'],
                                            "type" 				=> 'REG',
                                            "feed_date_time" 	=> $value['feed_date_time'],
                                            "season_scheduled_date" => $value['season_scheduled_date'],
                                            "scheduled_date" 	=> $value['scheduled_date'],
                                            "home_id" 			=> $home_id, 
                                            "away_id" 			=> $away_id,
                                            "api_week"          => $value['api_week']
                                    );
						$match_data[$season_k] = $temp_match_array;
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
		exit();	
    }

    

    /**
     * function used for fetch players from feed
     * @param int $sports_id
     * @return boolean
     */
    public function get_players($sports_id)
    {
		$rs = $this->db->select("L.league_id,L.league_uid,L.sports_id,S.season_id,S.is_published,S.home_id,S.away_id,S.season_game_uid,T1.team_uid AS home_uid,T2.team_uid AS away_uid", FALSE)
							->from(LEAGUE." AS L")
							->join(SEASON." AS S", "S.league_id = L.league_id", 'INNER')
							->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.active = 1")
							->join(TEAM." AS T1", "T1.team_id = S.home_id", 'INNER')
							->join(TEAM." AS T2", "T2.team_id = S.away_id", 'INNER')
							->where("L.active", 1)
							->where("L.sports_id", $sports_id)
							->where("S.season_scheduled_date >= ", format_date())
							->get();
		$league_data = $rs->result_array();
		//echo $this->db->last_query();die;
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
				$this->season_detail = $league;
			   	$url = $this->api['api_url']."get_players?league_uid=".$league['league_uid']."&match_id=".$league['season_game_uid']."&token=".$this->api['access_token'];
				$player_data = @file_get_contents($url);
				//echo "<pre>";print_r($player_data);die;	
				if (!$player_data)
				{
					continue;
				}
				$player_array = @json_decode(($player_data), TRUE);
				if(@$player_array['status'] == 'error')
				{
					exit("Feed token expire");
				}
				//echo "<pre>";print_r($player_array);continue;die;
				if(!empty($player_array['response']['data']))
				{
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

						// Prepare Player Team Relation Data
						$match_player_uid[] = $value['player_uid'];
						$pt_key = $value['player_uid'].'|'.$sports_id.'|'.$season_id;
						$player_team[$pt_key] = array(
											"player_id" 	=> 0,
											"season_id" 	=> $season_id,
											"position" 		=> $value['position'],
											"is_deleted" 	=> 0,
											"player_status" => 1,
											"salary" 		=> $value['salary'],
											"feed_verified"	=> 1,
											"team_id"	=> $team_ids[$value['team_uid']]
										);
					}
					
					//echo "<pre>";print_r($data);
					//echo "<pre>";print_r($player_team);die;
					if (!empty($data))
					{
						//echo "<pre>";print_r($data);
						//echo "<pre>";print_r($player_team);  die;
						$concat_key = 'CONCAT(sports_id,"_",player_uid)';
                        $update_ignore = array();
                        $player_string = implode(',',array_map(function($n){return '\''.$n.'\'';},$player_uid_arr));
                        $where = 'sports_id ='.$sports_id.  
                                    ' AND player_uid IN ('.$player_string.')';
                        $this->insert_or_update_on_exist($player_key,$data,$concat_key,PLAYER,'player_id',$update_ignore,$where);
						// Get all Team of this sports of current year to map team_id in Team_League relation
						//Trasaction start
						$this->db->trans_strict(TRUE);
			        	$this->db->trans_start();

						$player_data = $this->get_player_data_by_player_uid($season_id,$sports_id, $match_player_uid);

						//echo "<pre>";print_r($player_data);die;
						
						$player_team_data = array();
						if (!empty($player_data))
						{
							foreach ($player_data as $player)
							{
								//echo "<pre>";print_r($player_team);die;
								// Map team_id with Team_League data
								if (!empty($player_team[$player['player_uid'].'|'.$player['sports_id'].'|'.$season_id]) && $player['is_published'] == 0)
								{
									$player_team_arr = $player_team[$player['player_uid'].'|'.$player['sports_id'].'|'.$season_id];
									$player_team_arr['player_id'] = $player['player_id'];
									$player_team_arr['is_new'] = $player['is_new'];
									$player_team_arr['is_published'] = $player['is_published'];
									$new_player_count = 0;
									if($player_team_arr['feed_verified'] == 1 && $league['is_published'] == 1 && $player['feed_verified'] == 0 && $player['is_published'] == 0){
										$player_team_arr['is_new'] = 1;
										$player_team_arr['feed_verified'] = 1;
										$player_team_arr['is_published'] = 1;
										$new_player_count = 1;
									}

									$player_team_k = $season_id.'_'.$player['player_id'];
                                    $player_team_data[$player_team_k] = $player_team_arr;
                                    $player_team_key[] = $player_team_k; 

                                    $player_id_arr[] = $player_team_arr['player_id'];
                                }else{
									
									$player_team_arr = $player_team[$player['player_uid'].'|'.$player['sports_id'].'|'.$season_id];
									$player_team_arr['player_id'] = $player['player_id'];
									if($player['is_published'] != 0)
									{
										$player_team_arr['salary'] = $player['salary'];	
									}
									$player_team_k = $season_id.'_'.$player['player_id'];

                                   $player_team_data[$player_team_k] = $player_team_arr;
                                    $player_team_key[] = $player_team_k; 

                                    $player_id_arr[] = $player_team_arr['player_id']; 
                                }
							}
						}

						//echo "<pre>";print_r($player_team_data);die;
						
						// Insert Team League Data
						if (!empty($player_team_data))
						{
							$concat_key = 'CONCAT(season_id,"_",player_id)';
                            
                            $update_ignore = array("position","is_deleted","player_status","is_published");

                            //where in
                            $player_string = implode(",",array_unique($player_id_arr));
                            $where = 'season_id ='.$season_id.  
                                    ' AND player_id IN ('.$player_string.')'; 
                            $this->insert_or_update_on_exist($player_team_key,$player_team_data,$concat_key,PLAYER_TEAM,'player_team_id',$update_ignore,$where);
						}

						//Update display name if its empty or null
						$this->db->where('display_name', NULL);
						$this->db->set('display_name','nick_name',FALSE);
						$this->db->update(PLAYER);
						//new player update reset cache nd json file for roster list
						if(isset($new_player_count) && $new_player_count == 1)
						{
							$this->db->select("CS.collection_master_id")
					                ->from(COLLECTION_SEASON . " CS")
					                ->join(COLLECTION_MASTER.' as CM', 'CM.collection_master_id = CS.collection_master_id',"INNER")
					                ->where('CM.season_game_count', 1)
					                ->where('CM.league_id', $league['league_id'])
					                ->where('CS.season_id', $season_id);
			                $sql = $this->db->get();
					        $collection_info = $sql->row_array();
							if(!empty($collection_info))
							{
								$collection_master_id = $collection_info['collection_master_id'];
								$file_data_arr['cache'][] = "roster_list_".$collection_master_id;
								$file_data_arr['bucket'][] = "collection_roster_list_".$collection_master_id;
								$this->remove_cache_bucket_data($file_data_arr);
							}	
						}
						//Trasaction end
                        $this->db->trans_complete();
                        if ($this->db->trans_status() === FALSE )
                        {
                            $this->db->trans_rollback();
                        }
                        else
                        {
                            $this->db->trans_commit();
                            echo "<br>Players inserted for [<b>".$league['league_id'].' - '.$season_id."</b>]";
                        }  
				   		
				   	}
				}		
			}
		}	
		exit();	
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
			//trasaction start
			$this->db->trans_strict(TRUE);
	        $this->db->trans_start();
			$this->load->helper('queue');
			foreach ($current_game as $season_game)
			{
				$season_game_uid = $season_game['season_game_uid'];
				$week = $season_game['week'];
				$league_id = $season_game['league_id'];
				$season_id = $season_game['season_id'];
				$home_id = $season_game['home_id'];
				$away_id = $season_game['away_id'];
				$team_ids[$season_game['home_uid']] = $home_id;
				$team_ids[$season_game['away_uid']] = $away_id;

				$url = $this->api['api_url']."get_scores?match_id=".$season_game_uid."&token=".$this->api['access_token'];
				//echo $url ;die;
				$match_data = @file_get_contents($url);
				if(!$match_data)
				{
					continue;
				}

				$match_array = @json_decode(($match_data), TRUE);
				if(@$match_array['status'] == 'error')
				{
					exit("Feed token expire");
				}
				//echo "<pre>";print_r($match_array);die;
				$match_info = @$match_array['response']['match_info'];
				//echo "<pre>";print_r($match_info);die;
				if(!empty($match_info))
				{
					$scheduled_date = $match_info['scheduled_date'];
					$match_status = @$match_info['status']; 
				}

				$match_score = array();
				if(!empty($match_array['response']['data']))
				{	
					//All player id with player id by season
	                $player_ids = $this->get_player_data_by_season_id($sports_id,$season_id);
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
		                        "week"            			=> $week,
		                        "scheduled_date"            => $value['scheduled'],
		                        "scheduled"                 => $value['scheduled'],
		                        "home_score"            	=> $value['home_score'],
		                        "away_score"            	=> $value['away_score'],
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
								"defensive_punt_return_touchdowns"=> $value['defensive_punt_return_touchdowns'],
								"sacks" 				=> $value['sacks'],
								"safeties" 				=> $value['safeties'],
								"defensive_touch_downs" 		=> $value['defensive_touch_downs'],
								"defence_turnovers" 			=> $value['defence_turnovers'],
								"points_allowed" 			=> $value['points_allowed'],
		                        "update_at" => format_date()
                        );

						//echo "<pre>";print_r($score_data);die;
					}
					if(!empty($score_data))
					{
						//echo "<pre>";print_r($score_data);die;
						$this->replace_into_batch(GAME_STATISTICS_FOOTBALL, array_values($score_data));
						echo "<pre>";  echo "Match [<b>$season_id</b>] score updated";
					}
				}
				//status
				//echo "<pre>";print_r($match_info);die;
				if(!empty($match_info))
				{
					//0-Not Started, 1-Live, 2-Completed, 3-Delay, 4-Canceled
					$status = 0;
					$status_overview = 0;
					$match_closure_date = NULL;
					if($match_status == 1 )
					{
						$status = 1;
					}
					elseif($match_status == 3 )
					{
						$status = 1;
						$status_overview = 1;
					}elseif($match_status == 4 )
					{
						$status = 2;
						$status_overview = 3;
						$match_closure_date = format_date();
					}elseif($match_status == 2)
					{
						$player_count = count($this->get_game_statistics_by_season_id($season_id,$sports_id,$league_id));
						if($player_count >= 14)
						{
							$status = 2;
							$status_overview = 4;
							$match_closure_date = format_date();
						}
					}

					//if match closure date provided from feed then update this on client db
					$season_update_array = array("status" => $status,"status_overview" => $status_overview,"score_data"=>$match_info['score_data']);
					if(isset($match_info['scoring_alert']))
					{
						$season_update_array['scoring_alert'] = $match_info['scoring_alert'];
					}

					if(isset($match_info['score_verified']))
					{
						$season_update_array['score_verified'] = $match_info['score_verified'];
					}

					if(isset($match_closure_date) && $match_closure_date != "")
					{
						$season_update_array['match_closure_date'] = $match_closure_date;
					}

					$this->db->where("league_id",$league_id);
					$this->db->where("season_id",$season_id);
					$this->db->update(SEASON,$season_update_array);
					
					echo "<br> Scores updated for the match : ".$season_id." <br>";
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
		exit("<br>Scores updated");		
    }

     /**
     * function used for calculate player fantasy points
     * @param int $sports_id
     * @return array
     */
    public function save_calculated_scores($sports_id)
    {
        // Get All Live Games List 
        $current_game = $this->get_season_match_for_calculate_points($sports_id);
        //echo "<pre>"; print_r($current_game);die;
        if (!empty($current_game))
        {
            $formula = $this->get_scoring_rules($sports_id);
            //echo "<pre>";print_r($formula);die;
            foreach ($current_game as $season_game)
            {   
                $this->calculate_nfl_fantasy_points($sports_id,$season_game,$formula);    
            }// End of Team loop  
        } // End of Empty team check
        exit();
    }

    /**
    * This method calculate playeys score minute by minute 
    * 
    * @param [int] $league_id
    * @return null
    */
    public function calculate_nfl_fantasy_points($sports_id,$season_game,$formula)
    {
        $season_game_uid        = $season_game['season_game_uid'];
        $league_id              = $season_game['league_id'];
        $season_id              = $season_game['season_id'];
        $formula                = $formula['normal'];
        //echo "<pre>";print_r($formula);die;
        // Get game stats of every minute by season_game_unique_id
        $game_stats = $this->get_game_statistics_by_season_id($season_id,$sports_id,$league_id);
        //echo "<pre>";print_r($game_stats);die;
        if(!empty($game_stats))
        {
            $all_player_scores = array();

            foreach($game_stats as $stats)
            {
                $score              = 0.0;
                $normal_score       = 0.0;
                $each_player_score  = array();
                $score_key          = array();

                $each_player_score['season_id']   = $season_id;
                $each_player_score['player_id']        = $stats['player_id'];
                $each_player_score['scheduled_date']    = $stats['scheduled_date'];
                $each_player_score['league_id']         = $league_id;
                $each_player_score['normal_score']      = $normal_score;
                $each_player_score['score']             = $score;

                /* ####### NORMAL SCORING RULE ########### */
                
                /**** PASSING ****/
                if($stats['passing_yards'] != 0)
                {
                    $total_score_yards = $stats['passing_yards'];
                    $score = $score + ($total_score_yards * $formula['PASSING_YARDS']) ;
                    $score_key['PASSING_YARDS'] = ($total_score_yards * $formula['PASSING_YARDS']) ;
                }

                if($stats['passing_touch_downs'] != 0)
                {
                    $score = $score + ($stats['passing_touch_downs'] * $formula['PASSING_TOUCHDOWNS']) ;
                    $score_key['PASSING_TOUCHDOWNS'] = ($stats['passing_touch_downs'] * $formula['PASSING_TOUCHDOWNS']) ;
                }

                if($stats['passing_interceptions'] != 0)
                {
                    $score = $score + ($stats['passing_interceptions'] * $formula['PASSING_INTERCEPTIONS']) ;
                    $score_key['PASSING_INTERCEPTIONS'] = ($stats['passing_interceptions'] * $formula['PASSING_INTERCEPTIONS']) ;
                }

                if($stats['passing_two_pt'] != 0)
                {
                    $score = $score + ($stats['passing_two_pt'] * $formula['PASSING_TWO_POINT']) ;
                    $score_key['PASSING_TWO_POINT'] = ($stats['passing_two_pt'] * $formula['PASSING_TWO_POINT']) ;
                }

                 /**** RUSHING ****/

                if($stats['rushing_yards'] != 0)
                {
                    $total_score_yards = $stats['rushing_yards'];
                    $score = $score + ($total_score_yards * $formula['RUSHING_YARDS']) ;
                    $score_key['RUSHING_YARDS'] = ($total_score_yards * $formula['RUSHING_YARDS']) ;
                }

                if($stats['rushing_touch_downs'] != 0)
                {
                    $score = $score + ($stats['rushing_touch_downs'] * $formula['RUSHING_TOUCHDOWNS']) ;
                    $score_key['RUSHING_TOUCHDOWNS'] = ($stats['rushing_touch_downs'] * $formula['RUSHING_TOUCHDOWNS']) ;
                }

                if($stats['rushing_two_pt'] != 0)
                {
                    $score = $score + ($stats['rushing_two_pt'] * $formula['RUSHING_TWO_POINT']) ;
                    $score_key['RUSHING_TWO_POINT'] = ($stats['rushing_two_pt'] * $formula['RUSHING_TWO_POINT']) ;
                }


                /**** RECEVING ****/

                if($stats['receiving_yards'] != 0)
                {
                    $total_score_yards = $stats['receiving_yards'];
                    $score = $score + ($total_score_yards * $formula['RECEIVING_YARDS']) ;
                    $score_key['RECEIVING_YARDS'] = ($total_score_yards * $formula['RECEIVING_YARDS']) ;
                }

                if($stats['receptions'] != 0)
                {
                    $score = $score + ($stats['receptions'] * $formula['RECEPTIONS']) ;
                    $score_key['RECEPTIONS'] = ($stats['receptions'] * $formula['RECEPTIONS']) ;
                }

                if($stats['receiving_touch_downs'] != 0)
                {
                    $score = $score + ($stats['receiving_touch_downs'] * $formula['RECEIVING_TOUCHDOWNS']) ;
                    $score_key['RECEIVING_TOUCHDOWNS'] = ($stats['receiving_touch_downs'] * $formula['RECEIVING_TOUCHDOWNS']) ;
                }

                if($stats['receiving_two_pt'] != 0)
                {
                    $score = $score + ($stats['receiving_two_pt'] * $formula['RECEVING_TWO_POINT']) ;
                    $score_key['RECEVING_TWO_POINT'] = ($stats['receiving_two_pt'] * $formula['RECEVING_TWO_POINT']) ;
                }

                /**** fumbal ****/

                if($stats['fumbles_lost'] != 0)
                {
                    $score = $score + ($stats['fumbles_lost'] * $formula['FUMBLES_LOST']) ;
                    $score_key['FUMBLES_LOST'] = ($stats['fumbles_lost'] * $formula['FUMBLES_LOST']) ;
                }

                if($stats['fumbles_touch_downs'] != 0)
                {
                    $score = $score + ($stats['fumbles_touch_downs'] * $formula['DEFENSE_FUMBLES_RECOVERY_TD']) ;
                    $score_key["defense"]['DEFENSE_FUMBLES_RECOVERY_TD'] = ($stats['fumbles_touch_downs'] * $formula['DEFENSE_FUMBLES_RECOVERY_TD']) ;
                }

                if($stats['kick_returns_touch_downs'] != 0)
                {
                    $score = $score + ($stats['kick_returns_touch_downs'] * $formula['KICK_RETURN_TOUCHDOWNS']) ;
                    $score_key['KICK_RETURN_TOUCHDOWNS'] = ($stats['kick_returns_touch_downs'] * $formula['KICK_RETURN_TOUCHDOWNS']) ;
                }


                if($stats['punt_return_touch_downs'] != 0)
                {
                    $score = $score + ($stats['punt_return_touch_downs'] * $formula['PUNT_RETURN_TOUCHDOWNS']) ;
                    $score_key['PUNT_RETURN_TOUCHDOWNS'] = ($stats['punt_return_touch_downs'] * $formula['PUNT_RETURN_TOUCHDOWNS']) ;
                }

                /****Defense ****/
                if($stats['position'] == 'DEF')
                { 


                    //default points for defensive team
                    //$score = $score + ($formula['DEFENSE_DEFAULT_POINTS']);
                    //$score_key["defense"]['DEFENSE_DEFAULT_POINTS'] = ($formula['DEFENSE_DEFAULT_POINTS']) ;

                    if($stats['sacks'] != 0)
                    {
                        $score = $score + ($stats['sacks'] * $formula['DEFENSE_SACK']) ;
                        $score_key["defense"]['DEFENSE_SACK']   = ($stats['sacks'] * $formula['DEFENSE_SACK']) ;
                    }

                    if($stats['defensive_interceptions'] != 0)
                    {
                        $score = $score + ($stats['defensive_interceptions'] * $formula['DEFENSE_INTERCEPTIONS']) ;
                        $score_key["defense"]['DEFENSE_INTERCEPTIONS']  = ($stats['defensive_interceptions'] * $formula['DEFENSE_INTERCEPTIONS']) ;
                    }

                    if($stats['defensive_fumbles_recovered'] != 0)
                    {
                        $score = $score + ($stats['defensive_fumbles_recovered'] * $formula['DEFENSE_FUMBLES_RECOVERED']) ;
                        $score_key["defense"]['DEFENSE_FUMBLES_RECOVERED']  = ($stats['defensive_fumbles_recovered'] * $formula['DEFENSE_FUMBLES_RECOVERED']) ;
                    }

                    if($stats['safeties'] != 0)
                    {
                        $score = $score + ($stats['safeties'] * $formula['DEFENSE_SAFETIES']) ;
                        $score_key["defense"]['DEFENSE_SAFETIES']  = ($stats['safeties'] * $formula['DEFENSE_SAFETIES']) ;
                    }

                     if($stats['defensive_kick_return_touchdowns'] != 0)
                    {
                        $score = $score + ($stats['defensive_kick_return_touchdowns'] * $formula['DEFENSE_KICK_RETURN_TOUCHDOWNS']) ;
                        $score_key["defense"]['DEFENSE_KICK_RETURN_TOUCHDOWNS']  = ($stats['defensive_kick_return_touchdowns'] * $formula['DEFENSE_KICK_RETURN_TOUCHDOWNS']) ;
                    }


                     if($stats['defensive_punt_return_touchdowns'] != 0)
                    {
                        $score = $score + ($stats['defensive_punt_return_touchdowns'] * $formula['DEFENSE_PUNT_RETURN_TOUCHDOWNS']) ;
                        $score_key["defense"]['DEFENSE_PUNT_RETURN_TOUCHDOWNS']  = ($stats['defensive_punt_return_touchdowns'] * $formula['DEFENSE_PUNT_RETURN_TOUCHDOWNS']) ;
                    }
                    /*if($stats['defence_turnovers'] > 0)
                    {
                        $score = $score + ($stats['defence_turnovers'] * $formula['DEFENSE_TURNOVER']) ;
                        $score_key["defense"]['DEFENSE_TURNOVER']  = ($stats['defence_turnovers'] * $formula['DEFENSE_TURNOVER']) ;
                    }*/

                    //Points AllowedByDefenseSpecialTeams
                    if($stats['points_allowed'] == 0)
                    {
                        $score = $score + ($formula['DEFENSE_POINTS_ALLOWED_0']) ;
                        $score_key["defense"]['DEFENSE_POINTS_ALLOWED_0']   = ($formula['DEFENSE_POINTS_ALLOWED_0']) ;
                    }
                    
                    //PointsAllowedByDefenseSpecialTeams 1 To 6
                    if($stats['points_allowed'] >= 1 && $stats['points_allowed'] <= 6)
                    {
                        $score = $score + ($formula['DEFENSE_POINTS_ALLOWED_1_6']) ;
                        $score_key["defense"]['DEFENSE_POINTS_ALLOWED_1_6'] = ($formula['DEFENSE_POINTS_ALLOWED_1_6']) ;
                    }

                    //PointsAllowedByDefenseSpecialTeams 7 To 13
                    if($stats['points_allowed'] >= 7 && $stats['points_allowed'] <= 13)
                    {
                        $score = $score + ($formula['DEFENSE_POINTS_ALLOWED_7_13']) ;
                        $score_key["defense"]['DEFENSE_POINTS_ALLOWED_7_13']    = ($formula['DEFENSE_POINTS_ALLOWED_7_13']) ;
                    }

                    //PointsAllowedByDefenseSpecialTeams 14 To 20
                    if($stats['points_allowed'] >= 14 && $stats['points_allowed'] <= 20)
                    {
                        $score = $score + ($formula['DEFENSE_POINTS_ALLOWED_14_20']) ;
                        $score_key["defense"]['DEFENSE_POINTS_ALLOWED_14_20']=($formula['DEFENSE_POINTS_ALLOWED_14_20']) ;
                    }

                    //PointsAllowedByDefenseSpecialTeams 21 To 27
                    if($stats['points_allowed'] >= 21 && $stats['points_allowed'] <= 27)
                    {
                        $score = $score + ($formula['DEFENSE_POINTS_ALLOWED_21_27']) ;
                        $score_key["defense"]['DEFENSE_POINTS_ALLOWED_21_27']= ($formula['DEFENSE_POINTS_ALLOWED_21_27']) ;
                    }

                    //PointsAllowedByDefenseSpecialTeams 28 To 34
                    if($stats['points_allowed'] >= 28 && $stats['points_allowed'] <= 34)
                    {
                        $score = $score + ($formula['DEFENSE_POINTS_ALLOWED_28_34']) ;
                        $score_key["defense"]['DEFENSE_POINTS_ALLOWED_28_34']   = ($formula['DEFENSE_POINTS_ALLOWED_28_34']) ;
                    }

                    //PointsAllowedByDefenseSpecialTeams 35+
                    if($stats['points_allowed'] >= 35)
                    {
                        $score = $score + ($formula['DEFENSE_POINTS_ALLOWED_35plus']) ;
                        $score_key["defense"]['DEFENSE_POINTS_ALLOWED_35plus']  = ($formula['DEFENSE_POINTS_ALLOWED_35plus']) ;
                    }

                   
                    //defensive_touch_downs
                    if($stats['defensive_touch_downs'] != 0)
                    {
                        $score = $score + ($stats['defensive_touch_downs'] * $formula['DEFENSE_TOUCHDOWNS']) ;
                        $score_key["defense"]['DEFENSE_TOUCHDOWNS']  = ($stats['defensive_touch_downs'] * $formula['DEFENSE_TOUCHDOWNS']) ;
                    }

                    

                } 

                if($stats['position'] == 'K')
                { 
                    /*if($stats['field_goals_made'] > 0)
                    {
                        $score = $score + ($stats['field_goals_made'] * $formula['KICKER_FIELD_GOAL']) ;
                        $score_key['KICKER_FIELD_GOAL'] = ($stats['field_goals_made'] * $formula['KICKER_FIELD_GOAL']) ;
                    }*/

                    if($stats['field_goals_blocked'] != 0)
                    {
                        $score = $score + ($stats['field_goals_blocked'] * $formula['KICKER_FIELD_GOAL_BLOCKED']) ;
                        $score_key['KICKER_FIELD_GOAL_BLOCKED'] = ($stats['field_goals_blocked'] * $formula['KICKER_FIELD_GOAL_BLOCKED']);
                    }

                    if($stats['extra_points_made'] != 0)
                    {
                        $score = $score + ($stats['extra_points_made'] * $formula['KICKER_EXTRA_PT_MADE']) ;
                        $score_key['KICKER_EXTRA_PT_MADE'] = ($stats['extra_points_made'] * $formula['KICKER_EXTRA_PT_MADE']);
                    }

                    if($stats['extra_point_blocked'] != 0)
                    {
                        $score = $score + ($stats['extra_point_blocked'] * $formula['KICKER_EXTRA_PT_BLOCKED']) ;
                        $score_key['KICKER_EXTRA_PT_BLOCKED'] = ($stats['extra_point_blocked'] * $formula['KICKER_EXTRA_PT_BLOCKED']);
                    }

                    if($stats['field_goals_from_1_19_yards'] != 0)
                    {
                        $score = $score + ($stats['field_goals_from_1_19_yards'] * $formula['KICKER_FG_0_19']) ;
                        $score_key['KICKER_FG_0_19'] = ($stats['field_goals_from_1_19_yards'] * $formula['KICKER_FG_0_19']);
                    }

                    if($stats['field_goals_from_20_29_yards'] != 0)
                    {
                        $score = $score + ($stats['field_goals_from_20_29_yards'] * $formula['KICKER_FG_20_29']) ;
                        $score_key['KICKER_FG_20_29'] = ($stats['field_goals_from_20_29_yards'] * $formula['KICKER_FG_20_29']);
                    }

                    if($stats['field_goals_from_30_39_yards'] != 0)
                    {
                        $score = $score + ($stats['field_goals_from_30_39_yards'] * $formula['KICKER_FG_30_39']) ;
                        $score_key['KICKER_FG_30_39'] = ($stats['field_goals_from_30_39_yards'] * $formula['KICKER_FG_30_39']);
                    }

                    if($stats['field_goals_from_40_49_yards'] != 0)
                    {
                        $score = $score + ($stats['field_goals_from_40_49_yards'] * $formula['KICKER_FG_40_49']) ;
                        $score_key['KICKER_FG_40_49'] = ($stats['field_goals_from_40_49_yards'] * $formula['KICKER_FG_40_49']);
                    }


                    if($stats['field_goals_from_50_yards'] != 0)
                    {
                        $score = $score + ($stats['field_goals_from_50_yards'] * $formula['KICKER_FG_50PLUS']) ;
                        $score_key['KICKER_FG_50PLUS'] = ($stats['field_goals_from_50_yards'] * $formula['KICKER_FG_50PLUS']);
                    }



                } 

                if(array_key_exists($stats['player_id'], $all_player_scores) && !empty($all_player_scores[$stats['player_id']]))
                {
                    $json_data  = (array) json_decode($all_player_scores[$stats['player_id']]['break_down']);
                    
                    $each_player_score['score']         = $all_player_scores[$stats['player_id']]['score'] + $score;
                    $each_player_score['week']          = $stats["week"];
                    $each_player_score['normal_score']  = $all_player_scores[$stats['player_id']]['normal_score'] + $score;
                   
                    $each_player_score['break_down']    = json_encode(array_merge($score_key,$json_data));


                   // $all_player_scores[$stats['player_uid']] = $each_player_score;

                }
                else
                {
                    $each_player_score['score']         = $score;
                    $each_player_score['week']          = $stats["week"];
                    $each_player_score['normal_score']  = $score;
                    $each_player_score['break_down']    = json_encode($score_key);
                   // $all_player_scores[$stats['player_uid']] = $each_player_score;
               }

               //booster
                $booster_arr = array();
                $booster_arr = $this->get_booster_break_down($each_player_score['break_down']);
                
                $each_player_score['booster_break_down'] = json_encode($booster_arr);
                $all_player_scores[$stats['player_id']] = $each_player_score;
                
            }
           
            //echo "<pre>";print_r($all_player_scores);die;
            if(!empty($all_player_scores))
            {
                $all_player_scores = array_values($all_player_scores);
                //Update score first zero then update
                $this->db->where('season_id', $season_id);
                $this->db->where('league_id', $league_id);
                $this->db->update(GAME_PLAYER_SCORING,array("normal_score"=>"0","bonus_score"=>"0","score"=>"0","break_down"=>NULL,"final_break_down"=>NULL));

                $this->replace_into_batch(GAME_PLAYER_SCORING, $all_player_scores,TRUE);
                echo "<pre> Game id :-  " .$season_id . '<br>' ;
            }
        } // End  of  Empty of game_ stats
        
    }


    /**
     * This function used for calculate player fantasy point for single match
     * @param int $sports_id
     * @param int $league_id
     * @param string $season_game_uid
     * @return boolean
     */
    public function save_calculated_scores_by_match_id($sports_id,$league_id,$season_game_uid)
    {
        // Get All Live Games List 
        $season_game = $this->get_season_match_details($season_game_uid,$league_id);
        //echo "<pre>"; print_r($season_game);die;
        if (!empty($season_game))
        {
            $formula = $this->get_scoring_rules($sports_id);
            $this->calculate_nfl_fantasy_points($sports_id,$season_game,$formula); 
        } // End of Empty team check
        exit();
    }

    private function get_booster_break_down($final_break_down){
        $final_break_down = json_decode($final_break_down, TRUE);
		$booster_arr = array("NEW_KICKS"=>"0","GLADIATOR"=>"0","IRON_WALL"=>"0","HOT_POTATO"=>"0","RUNNING_BOMB"=>"0","NO_FUMBLE"=>"0","CAPTAIN"=>"0","IM_OPEN"=>"0");
		if(isset($final_break_down["KICKER_FG_50PLUS"])){
			$booster_arr["NEW_KICKS"] = $final_break_down["KICKER_FG_50PLUS"];
		}

        if(isset($final_break_down["defense"]['DEFENSE_TOUCHDOWNS'])){
			$booster_arr["GLADIATOR"] = $final_break_down["defense"]['DEFENSE_TOUCHDOWNS'];
		}

        if(isset($final_break_down["defense"]['DEFENSE_POINTS_ALLOWED_35plus'])){
			$booster_arr["IRON_WALL"] = $final_break_down["defense"]['DEFENSE_POINTS_ALLOWED_35plus'];
		}

        if(isset($final_break_down["defense"]['DEFENSE_FUMBLES_RECOVERED'])){
			$booster_arr["HOT_POTATO"] = $final_break_down["defense"]['DEFENSE_FUMBLES_RECOVERED'];
		}

        if(isset($final_break_down["RECEIVING_TOUCHDOWNS"])){
			$booster_arr["RUNNING_BOMB"] = $final_break_down["RECEIVING_TOUCHDOWNS"];
		}

        if(isset($final_break_down["FUMBLES_LOST"])){
			$booster_arr["NO_FUMBLE"] = $final_break_down["FUMBLES_LOST"];
		}

        if(isset($final_break_down["PASSING_INTERCEPTIONS"])){
			$booster_arr["CAPTAIN"] = $booster_arr["CAPTAIN"] + $final_break_down["PASSING_INTERCEPTIONS"];
		}
        if(isset($final_break_down["RUSHING_YARDS"])){
			$booster_arr["CAPTAIN"] = $booster_arr["CAPTAIN"] + $final_break_down["RUSHING_YARDS"];
		}
        if(isset($final_break_down["RUSHING_TOUCHDOWNS"])){
			$booster_arr["CAPTAIN"] = $booster_arr["CAPTAIN"] + $final_break_down["RUSHING_TOUCHDOWNS"];
		}
        
		if(isset($final_break_down["RECEIVING_TOUCHDOWNS"])){
			$booster_arr["IM_OPEN"] = $final_break_down["RECEIVING_TOUCHDOWNS"];
		}

		return $booster_arr;
	}

	/**
     * This function upload team's flags and jersys from feed s3 to project s3
     * @param array $team_img_arr
     * @return boolean
     */
	private function process_team_image_data_from_feed($team_img_arr)
	{
		if(empty($team_img_arr))
		{
			return TRUE;
		}

		$default_img_names = array("flag_default.jpg","jersey_default.png");
		$uploaded_img_arr  = array();
		
		//process all flags and jerseys from feed
		foreach ($team_img_arr as $img_key => $img_arr)
		{
			$feed_img_name = basename($img_arr['url']);
			try
			{
				$local_dir = ROOT_PATH.UPLOAD_DIR;

				//Create temp file on local -> /upload
				$temp_file_path = $local_dir. $feed_img_name;
				$temp_file = fopen($temp_file_path, "w");
				$feed_file_contents = file_get_contents($img_arr['url']);
				$tempFile = file_put_contents($temp_file_path, $feed_file_contents);

				//upload local file to s3
				$s3_dir = ($img_arr['type'] == 'flag') ? FLAG_CONTEST_DIR : JERSEY_CONTEST_DIR;
				$filePath = $s3_dir.$feed_img_name;

				$data_arr = array();
				$data_arr['file_path'] = $filePath;
				$data_arr['source_path'] = $temp_file_path;
				$this->load->library('Uploadfile');
				$upload_lib = new Uploadfile();
				$is_uploaded = $upload_lib->upload_file($data_arr);
				if($is_uploaded){
				  	$uploaded_img_arr[] = IMAGE_PATH.$filePath;
					@unlink($temp_file_path);
				}
			} 
			catch (Exception $e)
			{
				continue;
			}
		}//FOREACH END.

		return TRUE;
	}


	
}	