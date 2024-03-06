<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
set_time_limit(0);
class Vinfotech_model extends MY_Model
{
    private $api;
    public $db;
	public $atleast_minutes_played = 55;
	public $season_detail = array();
	function __construct()
	{
		parent::__construct();
        $this->api = $this->get_soccer_config_detail('vinfotech');
        $this->db	= $this->load->database('db_fantasy', TRUE);
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
        $sql = $this->db->select("GSS.*,PT.position", FALSE)
                ->from(GAME_STATISTICS_SOCCER . " AS GSS")
                ->join(PLAYER . " AS P", "P.player_id = GSS.player_id AND P.sports_id = $sports_id ", 'INNER')
                ->join(PLAYER_TEAM . " AS PT", "PT.player_id = P.player_id AND PT.player_id = GSS.player_id AND PT.season_id = '".$season_id."' ", 'INNER')
                ->where("GSS.season_id", $season_id)
                ->where("GSS.league_id", $league_id)
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
		if(!empty($league_array['response']['data']))
		{
			$league_keys = array();
			foreach ($league_array['response']['data'] as $key => $value) 
			{
				$key = $sports_id.'_'.$value['league_uid'];
				$data[$key] = array(
							"league_uid" 	=> @$value['league_uid'],
							"league_abbr" 	=> @$value['league_abbr'],
							"league_name" 	=> @$value['league_name'],
							"league_display_name" => @$value['league_display_name'],
							"league_schedule_date" => @$value['league_schedule_date'],
							"league_last_date" => (@$value['league_last_date'] != '' ) ? $value['league_last_date'] : NULL,
							"image" => (@$value['image'] != '' ) ? $value['image'] : NULL,
							"sports_id" 	=> $sports_id
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
	                //set/update team cache
		            //$team_cache = 'team_'.$sports_id;
		            //$team_ids = $this->get_team_id_with_team_uid($sports_id);
					//$this->set_cache_data($team_cache,$team_ids,REDIS_24_HOUR);
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
			foreach ($league_data as $league)
			{
			   	$url = $this->api['api_url']."get_season?league_uid=".$league['league_uid']."&token=".$this->api['access_token'];
				//echo $url ;die;
				$season_data = @file_get_contents($url);
				//echo "<pre>";print_r($league_data);die;	
				if (!$season_data)
				{
					continue;
				}
				$season_array = @json_decode(($season_data), TRUE);
				if(@$season_array['status'] == 'error')
				{
					exit("Feed token expire");
				}
				//echo "<pre>";print_r($season_array['response']['data']);die;
				if(!empty($season_array['response']['data']))
				{
					$match_data = $temp_match_array = array();
					$season_keys = array();
					foreach ($season_array['response']['data'] as $key => $value) 
					{
                        $tmp_scheduled_date = $value['season_scheduled_date'];
						if(isset($value['delay_minute']) && $value['delay_minute'] > 0)
						{
							$tmp_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$value['delay_minute'].' minutes', strtotime($value['season_scheduled_date'])));
						}
						if ($tmp_scheduled_date < format_date()) 
						{
                        	continue;
                        }
                        
                        //$team_ids = $this->get_team_id_with_team_uid($sports_id,'',array($value['home_uid'],$value['away_uid']));
						//echo "<pre>";print_r($team_ids);die;
						$home_id = @$team_ids[$value['home_uid']];
	                    $away_id = @$team_ids[$value['away_uid']];
                   		$season_info = $this->get_single_row("is_updated_playing,scoring_alert,delay_by_admin,notify_by_admin,delay_minute,delay_message,custom_message,2nd_inning_date,second_inning_update,season_scheduled_date", SEASON, array("season_game_uid" => $value['season_game_uid'],"league_id" => $league['league_id']));
						
						//if match delay set from feed then modify match schedule date time
						if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 1){
							$value['delay_minute'] = $season_info['delay_minute'];
							$value['delay_message'] = $season_info['delay_message'];
						}

						if(isset($value['delay_minute']) && $value['delay_minute'] > 0){
							$value['season_scheduled_date'] = date('Y-m-d H:i:s', strtotime('+'.$value['delay_minute'].' minutes', strtotime($value['season_scheduled_date'])));
							$value['scheduled_date'] = date('Y-m-d', strtotime($value['season_scheduled_date']));
						}

						if(isset($season_info['notify_by_admin']) && $season_info['notify_by_admin'] == 1){
							$value['custom_message'] = $season_info['custom_message'];
						}

						//status update 
						$status = 0;
						$status_overview = 0;
						$match_closure_date = NULL;
						if($value['status'] == 4 )
						{
							$status = 2;
							$status_overview = 3;
							$match_closure_date = format_date();
						}
                      
						$playing_eleven_confirm = 0;
						if(isset($value['playing_eleven_confirm'])){
							$playing_eleven_confirm = $value['playing_eleven_confirm'];
						}
                        $season_k = $league['league_id'].'_'.$value['season_game_uid'];

						$temp_match_array = array(
                                            "league_id" 		=> $league['league_id'],
                                            "season_game_uid" 	=> $value['season_game_uid'],
                                            "title" 			=> $value['title'],
                                            "subtitle" 			=> $value['subtitle'],
                                            "venue" 			=> $value['venue'],
                                            "year" 				=> $value['year'],
                                            "type" 				=> 'REG',
                                            "format" 			=> $value['format'],
                                            "feed_date_time" 	=> $value['feed_date_time'],
                                            "season_scheduled_date" => $value['season_scheduled_date'],
                                            "scheduled_date" 	=> $value['scheduled_date'],
                                            "home_id" 			=> $home_id, 
                                            "away_id" 			=> $away_id,
                                            "playing_eleven_confirm" => $playing_eleven_confirm,
                                            "delay_minute" 		=> @$value['delay_minute'],
                                            "delay_message" 	=> @$value['delay_message'],
                                            "custom_message" 	=> @$value['custom_message'],
                                            "status" => 0,
                                            "status_overview" => 0,
                                            "week" => '0',
                                            "api_week" => @$value['api_week'],
                                            "status" => $status,
											"status_overview" => $status_overview,
											"match_closure_date" => $match_closure_date,
                                    );	
						$match_data[$season_k] = $temp_match_array;
						$season_keys[] = $season_k;
					}

					if (!empty($match_data))
					{
						//echo "<pre>";print_r($match_data);die;
                        $concat_key = 'CONCAT(league_id,"_",season_game_uid)';
                        $this->insert_or_update_on_exist($season_keys,$match_data,$concat_key,SEASON,'season_id');
                        //Update weekseason week table
                        //$this->create_season_week($league['league_id'], 'REG',$this->api['year']);
			            //$this->update_season_week($league['league_id'], 'REG', $this->api['year']);
			            //$this->update_season_week_rescheduled($league['league_id'], 'REG', $this->api['year']);
			            //$this->update_season_week($league['league_id'], 'REG', $this->api['year']);
						echo "<br>League id " .$league['league_id']." Matches (seasons) inserted.<br>";
					}
				}	
			}
		}	
		exit("Season updated successfully!");	
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

						if(!isset($value['last_match_played'])){
							$value['last_match_played'] = 0;
						}
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
											"feed_verified"	=> $value['feed_verified'],
											"last_match_played"	=> $value['last_match_played'],
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
                        $player_string = implode(",",$player_uid_arr);
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
									$player_team_arr['last_match_played'] = $player_team[$player['player_uid'].'|'.$player['sports_id'].'|'.$season_id]['last_match_played'];
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
									$player_team_arr['last_match_played'] = $player_team[$player['player_uid'].'|'.$player['sports_id'].'|'.$season_id]['last_match_played'];
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
	                        "league_id" 			=> $league_id,
	                        "season_id" 			=> $season_id,
	                        "team_id" 				=> $team_id,
	                        "player_id"				=> $player_id,
	                        "scheduled_date" 		=> $scheduled_date,
	                        "week" 					=> $value['week'],
	                        "team_goals" 			=> $value['team_goals'],
	                        "position"				=> @$value['position'],
	                        "minutes"				=> $value['minutes_played'],
	                        "goals"					=> $value['goals'],
	                        "goals_minutes"			=> $value['goals_minutes'],
	                        "assists"				=> $value['assists'],
	                        "shots"					=> $value['shots_total'],
	                        "saves"					=> $value['saves'],
	                        "shots_on_goal"			=> $value['shots_on_goal'],
	                        "yellow_cards"			=> $value['yellow_cards'],
	                        "red_cards"				=> $value['red_cards'],
	                        "yellow_red_cards"		=> $value['yellow_red_cards'],
	                        "own_goals"				=> $value['own_goals'],
	                        "penalty_misses"		=> $value['penalty_misses'],
	                        "penalty_saves"			=> $value['penalty_saves'],
	                        "passes_completed"		=> $value['passes_completed'],
	                        "tackles_won"			=> $value['tackles_won'],
	                        "player_in_time"		=> $value['player_in_time'],
	                        "player_out_time"		=> $value['player_out_time'],
	                        "home_team_goal"		=> $value['home_team_goal'],
	                        "away_team_goal"		=> $value['away_team_goal'],
	                        "own_goals_minutes"		=> $value['own_goals_minutes'],
	                        "chancecreated"			=> $value['chancecreated'],
	                        "starting11"			=> $value['starting11'],
	                        "substitute"			=> $value['substitute'],
	                        "blockedshot"			=> $value['blockedshot'],
	                        "interceptionwon"		=> $value['interceptionwon'],
	                        "clearance"					=> $value['clearance']
                        );

						//echo "<pre>";print_r($score_data);die;
					}
					if(!empty($score_data))
					{
						//echo "<pre>";print_r($score_data);die;
						$this->replace_into_batch(GAME_STATISTICS_SOCCER, array_values($score_data));
						//delete player minute 0 minutes
						$this->db->where(array("season_id" => $season_id,"league_id" => $league_id,"minutes" => 0));
						$this->db->delete(GAME_STATISTICS_SOCCER);
						
						$this->get_clean_sheet($season_id,$league_id);
						$this->get_goals_conceded($season_id,$league_id);
						echo "<pre>";  echo "Match [<b>$season_id</b>] score updated";
					}
				}
				//status
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
						$status = 6;
						$status_overview = 1;
					}elseif($match_status == 4 )
					{
						$status = 2;
						$status_overview = 3;
						$match_closure_date = format_date();
					}elseif($match_status == 2 )
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
					$season_update_array = array("status" => $status,"status_overview" => $status_overview,"score_data"=>$match_info['score']);
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
     * function used for calculate clean sheet
     * @param string $season_game_uid
     * @param int $league_id
     * @return boolean
     */
    private function get_clean_sheet($season_id,$league_id)
    {
        $api_config = $this->get_soccer_config_detail('vinfotech');
        // clean sheet
        $query = " (SELECT PT.position,P.player_id FROM ".$this->db->dbprefix(PLAYER)." AS P
                        INNER JOIN ".$this->db->dbprefix(PLAYER_TEAM)." AS PT ON PT.player_id = P.player_id AND PT.season_id = '".$season_id."'
                        INNER JOIN ".$this->db->dbprefix(TEAM)." AS T ON T.team_id = PT.team_id AND T.year = ".$api_config['year']."
                        )";
        $rs = $this->db->select("GSS.player_id,GSS.team_id,GSS.minutes,GSS.player_in_time,GSS.player_out_time,GSS.goals_minutes,GSS.team_goals,S.home_id,S.away_id")
                            ->select("P.position")
                            ->from(GAME_STATISTICS_SOCCER ." AS GSS")
                            ->join($query." AS P","P.player_id = GSS.player_id", 'INNER')
                            ->join(SEASON." AS S","S.season_id = GSS.season_id AND S.league_id = GSS.league_id", 'INNER')
                            ->where("GSS.season_id", $season_id)
                            ->where("GSS.league_id", $league_id)
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
                    
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(GSS.goals_minutes), '0') AS goals_minutes",FALSE)
                                 ->from(GAME_STATISTICS_SOCCER ." AS GSS")
                                 ->where("GSS.season_id", $season_id)
                                 ->where("GSS.league_id", $league_id)
                                 ->where("GSS.team_id", $player['away_id'])
                                 ->where("GSS.goals_minutes >", 0)
                                 ->get();
                            //echo $this->db->last_query();     
                            $res = $rs->row_array();
                            
                    $all_goals_minutes  = $res['goals_minutes'];
                    // for own goal
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(GSS.own_goals_minutes), '0') AS own_goals_minutes",FALSE)
                                 ->from(GAME_STATISTICS_SOCCER ." AS GSS")
                                 ->where("GSS.season_id", $season_id)
                                 ->where("GSS.league_id", $league_id)
                                 ->where("GSS.team_id", $player['home_id'])
                                 ->where("GSS.own_goals_minutes >", 0)
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
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(GSS.goals_minutes), '0') AS goals_minutes",FALSE)
                                 ->from(GAME_STATISTICS_SOCCER ." AS GSS")
                                 ->where("GSS.season_id", $season_id)
                                 ->where("GSS.league_id", $league_id)
                                 ->where("GSS.team_id", $player['home_id'])
                                 ->where("GSS.goals_minutes >", 0)
                                 ->get();
                            //echo $this->db->last_query();     
                            $res = $rs->row_array();
                    $all_goals_minutes  = $res['goals_minutes'];
                    // for own goal
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(GSS.own_goals_minutes), '0') AS own_goals_minutes",FALSE)
                                 ->from(GAME_STATISTICS_SOCCER ." AS GSS")
                                 ->where("GSS.season_id", $season_id)
                                 ->where("GSS.league_id", $league_id)
                                 ->where("GSS.team_id", $player['away_id'])
                                 ->where("GSS.own_goals_minutes >", 0)
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
                                        ".$this->db->dbprefix(GAME_STATISTICS_SOCCER)."
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
                                            ".$this->db->dbprefix(GAME_STATISTICS_SOCCER)."
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
        $api_config = $this->get_soccer_config_detail('vinfotech');
        $query = " (SELECT PT.position,P.player_id FROM ".$this->db->dbprefix(PLAYER)." AS P
                        INNER JOIN ".$this->db->dbprefix(PLAYER_TEAM)." AS PT ON PT.player_id = P.player_id AND PT.season_id = '".$season_id."'
                        INNER JOIN ".$this->db->dbprefix(TEAM)." AS T ON T.team_id = PT.team_id AND T.year = ".$api_config['year']."
                         )";
        $rs = $this->db->select("GSS.player_id,GSS.team_id,S.home_id,S.away_id,GSS.minutes,GSS.player_in_time,GSS.player_out_time,GSS.goals_minutes,GSS.team_goals,
                                GSS.red_cards,GSS.yellow_red_cards")
                            ->select("P.position")
                            ->from(GAME_STATISTICS_SOCCER ." AS GSS")
                            ->join($query." AS P","P.player_id = GSS.player_id", 'INNER')
                            ->join(SEASON." AS S","S.season_id = GSS.season_id AND S.league_id = GSS.league_id", 'INNER')
                            ->where("GSS.season_id", $season_id)
                            ->where("GSS.league_id", $league_id)
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
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(GSS.goals_minutes), '0') AS goals_minutes",FALSE)
                                 ->from(GAME_STATISTICS_SOCCER ." AS GSS")
                                 ->where("GSS.season_id", $season_id)
                                 ->where("GSS.league_id", $league_id)
                                 ->where("GSS.team_id", $player['away_id'])
                                 ->where("GSS.own_goals" , 0)
                                 ->where("GSS.goals_minutes >", 0)
                                 ->get();
                            //echo $this->db->last_query();     
                            $res = $rs->row_array();
                    
                    $all_goals_minutes  = $res['goals_minutes'];
                    // for own goal
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(GSS.own_goals_minutes), '0') AS own_goals_minutes",FALSE)
                                 ->from(GAME_STATISTICS_SOCCER ." AS GSS")
                                 ->where("GSS.season_id", $season_id)
                                 ->where("GSS.league_id", $league_id)
                                 ->where("GSS.team_id", $player['home_id'])
                                 ->where("GSS.own_goals_minutes >", 0)
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
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(GSS.goals_minutes), '0') AS goals_minutes",FALSE)
                                 ->from(GAME_STATISTICS_SOCCER ." AS GSS")
                                 ->where("GSS.season_id", $season_id)
                                 ->where("GSS.league_id", $league_id)
                                 ->where("GSS.team_id", $player['home_id'])
                                 ->where("GSS.own_goals" , 0)
                                 ->where("GSS.goals_minutes >", 0)
                                 ->get();
                            //echo $this->db->last_query();     
                            $res = $rs->row_array();
                    $all_goals_minutes  = $res['goals_minutes'];
                    // for own goal
                    $rs = $this->db->select("IFNULL(GROUP_CONCAT(GSS.own_goals_minutes), '0') AS own_goals_minutes",FALSE)
                                 ->from(GAME_STATISTICS_SOCCER ." AS GSS")
                                 ->where("GSS.season_id", $season_id)
                                 ->where("GSS.league_id", $league_id)
                                 ->where("GSS.team_id", $player['away_id'])
                                 ->where("GSS.own_goals_minutes >", 0)
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
                                        ".$this->db->dbprefix(GAME_STATISTICS_SOCCER)."
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
                                                    ".$this->db->dbprefix(GAME_STATISTICS_SOCCER)."
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
     * function used for calculate player fantasy points
     * @param int $sports_id
     * @return array
     */
	public function calculated_fantasy_score($sports_id)
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
				$this->calculate_soccer_fantasy_points($sports_id,$season_game,$formula);	   
			}// End of Team loop  
		} // End of Empty team check
		exit();
	}

	private function calculate_soccer_fantasy_points($sports_id,$season_game,$formula)
	{
		//echo "<pre>";print_r($season_game);die;
		// Get All Scoring Rules
		$league_id = $season_game['league_id'];
		$season_game_uid		= $season_game['season_game_uid'];
		$season_id				= $season_game['season_id'];
		$all_player_scores		= array();
		$all_player_testing_var	= array();
        $total_final_breakdown = array();
        $player_final_breakdown = array();
		
		// Get Match Scoring Statistics to Calculate Fantasy Score
		$game_stats = $this->get_game_statistics_by_season_id($season_id,$sports_id,$league_id);
		//echo "<pre>";print_r($game_stats);die;
		if (!empty($game_stats))
		{
			foreach($game_stats as $stats)
            {
                $score              = 0.0;
                $bonus_score        = 0.0;
                $clean_sheet        = 0.0;
                $normal_score       = 0.0;
                $each_player_score  = array();
                $score_key          = array();

                // for breakdown dated 30 may
                $total_final_breakdown = array();

                $final_break_down = array();
                $final_break_down["playing_time"]    = array();
                $final_break_down["attack"]          = array();
                $final_break_down["defense"]         = array();
                $final_break_down["card_penalties"]  = array();

                $each_player_score['season_id']   = $stats['season_id'];
                $each_player_score['player_id']        = $stats['player_id'];
                $each_player_score['scheduled_date']    = $stats['scheduled_date'];
                $each_player_score['minutes_played']    = $stats['minutes'];
                $each_player_score['week']              = $stats['week'];
                $each_player_score['league_id']         = $league_id;
                $each_player_score['normal_score']      = $normal_score;
                $each_player_score['clean_sheet']       = $clean_sheet;
                $each_player_score['score']             = $score;

                /* ####### NORMAL SCORING RULE ########### */
                //Starting 11
                if($stats['starting11'] == 1 && $stats['substitute'] == 0)
                {
                    $score                      = $score +  $formula['normal']['STARTING_11'];
                    $score_key['STARTING_11']    = $formula['normal']['STARTING_11'];

                    $final_break_down["playing_time"] = $formula['normal']['STARTING_11'];
                    $total_final_breakdown["playing_time"] = (!empty($total_final_breakdown["playing_time"])) ? $formula['normal']['STARTING_11'] + $total_final_breakdown["playing_time"] : $formula['normal']['STARTING_11'];
                }

                //Subtitute
                if($stats['substitute'] == 1 && $stats['starting11'] == 0)
                {
                    $score                          = $score +  $formula['normal']['SUBSTITUTE'];
                    $score_key['SUBSTITUTE']  = $formula['normal']['SUBSTITUTE'];

                    $final_break_down["playing_time"] = $formula['normal']['SUBSTITUTE'];
                    $total_final_breakdown["playing_time"] = (!empty($total_final_breakdown["playing_time"])) ? $formula['normal']['SUBSTITUTE'] + $total_final_breakdown["playing_time"] : $formula['normal']['SUBSTITUTE'];
                }

                //Goal by Striker
                if( $stats['position'] == 'FW' && $stats['goals'] > 0)
                {
                    $score                  = $score + ($stats['goals'] * $formula['normal']['GOAL_STRIKER'] );
                    $score_key['GOAL_STRIKER']   = ($stats['goals'] * $formula['normal']['GOAL_STRIKER'] );

                    $final_break_down["attack"]["GOAL"] = ($stats['goals'] * $formula['normal']['GOAL_STRIKER'] );
                    $total_final_breakdown["attack"]["GOAL"] = (!empty($total_final_breakdown["attack"]["GOAL"])) ? ($stats['goals'] * $formula['normal']['GOAL_STRIKER'] ) + $total_final_breakdown["attack"]["GOAL"] : ($stats['goals'] * $formula['normal']['GOAL_STRIKER'] );
                }   
               
                //Goal for Mid fielder
                if($stats['position'] == 'MF' && $stats['goals'] > 0)
                {
                    $score                  = $score + ($stats['goals'] * $formula['normal']['GOAL_MID_FIELDER'] );
                    $score_key['GOAL_MID_FIELDER']   = ($stats['goals'] * $formula['normal']['GOAL_MID_FIELDER'] );

                    $final_break_down["attack"]["GOAL"] = ($stats['goals'] * $formula['normal']['GOAL_MID_FIELDER'] );
                    $total_final_breakdown["attack"]["GOAL"] = (!empty($total_final_breakdown["attack"]["GOAL"])) ? ($stats['goals'] * $formula['normal']['GOAL_MID_FIELDER'] ) + $total_final_breakdown["attack"]["GOAL"] : ($stats['goals'] * $formula['normal']['GOAL_MID_FIELDER'] );
                }

                //Gaol by Defender 
                if($stats['position'] == 'DF' && $stats['goals'] > 0)
                {
                    $score                      = $score + ($stats['goals'] * $formula['normal']['GOAL_DEF_GK'] );
                    $score_key['GOAL_DEF_GK']    = ($stats['goals'] * $formula['normal']['GOAL_DEF_GK'] );

                    $final_break_down["attack"]["GOAL"] = ($stats['goals'] * $formula['normal']['GOAL_DEF_GK'] );
                    $total_final_breakdown["attack"]["GOAL"] = (!empty($total_final_breakdown["attack"]["GOAL"])) ? ($stats['goals'] * $formula['normal']['GOAL_DEF_GK'] ) + $total_final_breakdown["attack"]["GOAL"] : ($stats['goals'] * $formula['normal']['GOAL_DEF_GK'] );
                }

                //Gaol by Goalkeeper
                if($stats['position'] == 'GK' && $stats['goals'] > 0)
                {
                    $score                      = $score + ($stats['goals'] * $formula['normal']['GOAL_DEF_GK'] );
                    $score_key['GOAL_DEF_GK']    = ($stats['goals'] * $formula['normal']['GOAL_DEF_GK'] );

                    $final_break_down["attack"]["GOAL"] = ($stats['goals'] * $formula['normal']['GOAL_DEF_GK'] );
                    $total_final_breakdown["attack"]["GOAL"] = (!empty($total_final_breakdown["attack"]["GOAL"])) ? ($stats['goals'] * $formula['normal']['GOAL_DEF_GK'] ) + $total_final_breakdown["attack"]["GOAL"] : ($stats['goals'] * $formula['normal']['GOAL_DEF_GK'] );
                }
                
                // assists
                if($stats['assists'] > 0)
                { 
                    $score                      = $score + ($stats['assists'] * $formula['normal']['ASSIST'] ); 
                    $score_key['ASSIST']   = ( $stats['assists'] * $formula['normal']['ASSIST'] ); 

                    $final_break_down["attack"]["ASSIST"] = ( $stats['assists'] * $formula['normal']['ASSIST'] );
                    $total_final_breakdown["attack"]["ASSIST"] = (!empty($total_final_breakdown["attack"]["ASSIST"])) ? ( $stats['assists'] * $formula['normal']['ASSIST'] ) + $total_final_breakdown["attack"]["ASSIST"] : ( $stats['assists'] * $formula['normal']['ASSIST'] );
                }

                //shot on target
                if($stats['shots_on_goal'] > 0)
                { 
                    $shot_on_target = $stats['shots_on_goal'];
                    $score  = $score + ( $shot_on_target * $formula['normal']['SHOT_ON_TARGET']) ;
                    $score_key['SHOT_ON_TARGET']    = ( $shot_on_target * $formula['normal']['SHOT_ON_TARGET']) ;

                    $final_break_down["attack"]["SHOT_ON_TARGET"] = ( $shot_on_target * $formula['normal']['SHOT_ON_TARGET']);
                    $total_final_breakdown["attack"]["SHOT_ON_TARGET"] = (!empty($total_final_breakdown["attack"]["SHOT_ON_TARGET"])) ? ( $shot_on_target * $formula['normal']['SHOT_ON_TARGET']) + $total_final_breakdown["attack"]["SHOT_ON_TARGET"] : ( $shot_on_target * $formula['normal']['SHOT_ON_TARGET']);
                }

                //Chance Created
                if($stats['chancecreated'] > 0)
                { 
                    $chance_created = $stats['chancecreated'];
                    $score  = $score + ( $chance_created * $formula['normal']['CHANCE_CREATED']) ;
                    $score_key['CHANCE_CREATED']    = ( $chance_created * $formula['normal']['CHANCE_CREATED']) ;

                    $final_break_down["attack"]["CHANCE_CREATED"] = ( $chance_created * $formula['normal']['CHANCE_CREATED']);
                    $total_final_breakdown["attack"]["CHANCE_CREATED"] = (!empty($total_final_breakdown["attack"]["CHANCE_CREATED"])) ? ( $chance_created * $formula['normal']['CHANCE_CREATED']) + $total_final_breakdown["attack"]["CHANCE_CREATED"] : ( $chance_created * $formula['normal']['CHANCE_CREATED']);
                }

                // Pass completed
                if($stats['passes_completed'] >= 5)
                {
                    $passes_completed                   = floor( $stats['passes_completed'] / 5  );
                    $score                          = $score + ( $passes_completed * $formula['normal']['PASSES_COMPLETED']) ;
                    $score_key['PASSES_COMPLETED']  = ( $passes_completed * $formula['normal']['PASSES_COMPLETED']) ;

                    $final_break_down["attack"]["PASSES_COMPLETED"] = ( $passes_completed * $formula['normal']['PASSES_COMPLETED']);
                    $total_final_breakdown["attack"]["PASSES_COMPLETED"] = (!empty($total_final_breakdown["attack"]["PASSES_COMPLETED"])) ? ( $passes_completed * $formula['normal']['PASSES_COMPLETED']) + $total_final_breakdown["attack"]["PASSES_COMPLETED"] : ( $passes_completed * $formula['normal']['PASSES_COMPLETED']);
                }

                //Tackle Won
                if($stats['tackles_won'] > 0)
                { 
                    $tackles = $stats['tackles_won'];
                    $score                      = $score + ( $tackles * $formula['normal']['TACKLE_WON']) ;
                    $score_key['TACKLE_WON']    = ($tackles * $formula['normal']['TACKLE_WON']) ;

                    $final_break_down["defense"]["TACKLE_WON"] = ($tackles * $formula['normal']['TACKLE_WON']);
                    $total_final_breakdown["defense"]["TACKLE_WON"] = (!empty($total_final_breakdown["defense"]["TACKLE_WON"])) ? ($tackles * $formula['normal']['TACKLE_WON']) + $total_final_breakdown["defense"]["TACKLE_WON"] : ($tackles * $formula['normal']['TACKLE_WON']);
                }

                //Interception won
                if($stats['interceptionwon'] > 0)
                { 
                    $tackles = $stats['interceptionwon'];
                    $score                      = $score + ( $tackles * $formula['normal']['INTERCEPTION_WON']) ;
                    $score_key['INTERCEPTION_WON']    = ($tackles * $formula['normal']['INTERCEPTION_WON']) ;

                    $final_break_down["defense"]["INTERCEPTION_WON"] = ($tackles * $formula['normal']['INTERCEPTION_WON']);
                    $total_final_breakdown["defense"]["INTERCEPTION_WON"] = (!empty($total_final_breakdown["defense"]["INTERCEPTION_WON"])) ? ($tackles * $formula['normal']['INTERCEPTION_WON']) + $total_final_breakdown["defense"]["INTERCEPTION_WON"] : ($tackles * $formula['normal']['INTERCEPTION_WON']);
                }

                //saves_GK
                if($stats['position'] == 'GK' && $stats['saves'] > 0)
                {
                    $saves_gk  = $stats['saves'];
                    $score                      = $score + ($saves_gk * $formula['normal']['SAVES_GK']);  
                    $score_key['SAVES_GK']      = ($saves_gk * $formula['normal']['SAVES_GK']); 

                    $final_break_down["defense"]["SHOT_SAVED"] = ($saves_gk * $formula['normal']['SAVES_GK']);
                    $total_final_breakdown["defense"]["SHOT_SAVED"] = (!empty($total_final_breakdown["defense"]["SHOT_SAVED"])) ? ($saves_gk * $formula['normal']['SAVES_GK']) + $total_final_breakdown["defense"]["SHOT_SAVED"] : ($saves_gk * $formula['normal']['SAVES_GK']);
                }

                //penalty_save_GK
                if($stats['position'] == 'GK' && $stats['penalty_saves'] > 0)
                {
                    $score = $score + ($stats['penalty_saves'] * $formula['normal']['PENALTY_SAVED_GK'] );  
                    $score_key['PENALTY_SAVED_GK']   = ($stats['penalty_saves'] * $formula['normal']['PENALTY_SAVED_GK'] ); 

                    $final_break_down["defense"]["PENALTY_SAVED_GK"] = ($stats['penalty_saves'] * $formula['normal']['PENALTY_SAVED_GK']);
                    $total_final_breakdown["defense"]["PENALTY_SAVED_GK"] = (!empty($total_final_breakdown["defense"]["PENALTY_SAVED_GK"])) ? ($stats['penalty_saves'] * $formula['normal']['PENALTY_SAVED_GK'] ) + $total_final_breakdown["defense"]["PENALTY_SAVED"] : ($stats['penalty_saves'] * $formula['normal']['PENALTY_SAVED_GK'] );
                }

                //clean sheet goalkeeper
                if($stats['position'] == 'GK' && $stats['clean_sheets'] > 0)
                {
                    $score  = $score + ($stats['clean_sheets'] * $formula['normal']['CLEAN_SHEET_GK_DEF']) ;
                    $score_key['CLEAN_SHEET_GK_DEF'] = ($stats['clean_sheets'] * $formula['normal']['CLEAN_SHEET_GK_DEF']) ;

                    $final_break_down["defense"]["CLEAN_SHEET"] = ($stats['clean_sheets'] * $formula['normal']['CLEAN_SHEET_GK_DEF']);
                    $total_final_breakdown["defense"]["CLEAN_SHEET"] = (!empty($total_final_breakdown["defense"]["CLEAN_SHEET"])) ? ($stats['clean_sheets'] * $formula['normal']['CLEAN_SHEET_GK_DEF']) + $total_final_breakdown["defense"]["CLEAN_SHEET"] : ($stats['clean_sheets'] * $formula['normal']['CLEAN_SHEET_GK_DEF']);
                }

                //clean sheet Defender
                if($stats['position'] == 'DF' && $stats['clean_sheets'] > 0)
                {
                    $score              = $score + ($stats['clean_sheets'] * $formula['normal']['CLEAN_SHEET_GK_DEF']) ;
                    $score_key['CLEAN_SHEET_GK_DEF'] = ($stats['clean_sheets'] * $formula['normal']['CLEAN_SHEET_GK_DEF']) ;

                    $final_break_down["defense"]["CLEAN_SHEET"] = ($stats['clean_sheets'] * $formula['normal']['CLEAN_SHEET_GK_DEF']);
                    $total_final_breakdown["defense"]["CLEAN_SHEET"] = (!empty($total_final_breakdown["defense"]["CLEAN_SHEET"])) ? ($stats['clean_sheets'] * $formula['normal']['CLEAN_SHEET_GK_DEF']) + $total_final_breakdown["defense"]["CLEAN_SHEET"] : ($stats['clean_sheets'] * $formula['normal']['CLEAN_SHEET_GK_DEF']);
                }

                // red_card
                if($stats['red_cards'] > 0)
                {
                    $score  = $score + ($stats['red_cards'] * $formula['normal']['RED_CARD'] );
                    $score_key['RED_CARD']      = ($stats['red_cards'] * $formula['normal']['RED_CARD']);

                    $final_break_down["card_penalties"]["RED_CARD"] = ($stats['red_cards'] * $formula['normal']['RED_CARD'] );
                    $total_final_breakdown["card_penalties"]["RED_CARD"] = (!empty($total_final_breakdown["card_penalties"]["RED_CARD"])) ? ($stats['red_cards'] * $formula['normal']['RED_CARD']) + $total_final_breakdown["card_penalties"]["RED_CARD"] : ($stats['red_cards'] * $formula['normal']['RED_CARD']);
                }elseif($stats['yellow_cards'] > 0)
                {
                    $score  = $score + ($stats['yellow_cards'] * $formula['normal']['YELLOW_CARD'] ); 
                    $score_key['YELLOW_CARD']   = ($stats['yellow_cards'] * $formula['normal']['YELLOW_CARD']);

                    $final_break_down["card_penalties"]["YELLOW_CARD"] = ($stats['yellow_cards'] * $formula['normal']['YELLOW_CARD']);
                    $total_final_breakdown["card_penalties"]["YELLOW_CARD"] = (!empty($total_final_breakdown["card_penalties"]["YELLOW_CARD"])) ? ($stats['yellow_cards'] * $formula['normal']['YELLOW_CARD']) + $total_final_breakdown["card_penalties"]["YELLOW_CARD"] : ($stats['yellow_cards'] * $formula['normal']['YELLOW_CARD']);
                }

                // Own goals
                if($stats['own_goals'] > 0)
                {
                    $score  = $score + ($stats['own_goals'] * $formula['normal']['OWN_GOAL'] );
                    $score_key['OWN_GOAL'] = ($stats['own_goals'] * $formula['normal']['OWN_GOAL']);

                    $final_break_down["card_penalties"]["OWN_GOAL"] = ($stats['own_goals'] * $formula['normal']['OWN_GOAL']);
                    $total_final_breakdown["card_penalties"]["OWN_GOAL"] = (!empty($total_final_breakdown["card_penalties"]["OWN_GOAL"])) ? ($stats['own_goals'] * $formula['normal']['OWN_GOAL']) + $total_final_breakdown["card_penalties"]["OWN_GOAL"] : ($stats['own_goals'] * $formula['normal']['OWN_GOAL']);
                }

                //Goal Conceded by Goalkeeper
                if($stats['position'] == 'GK' && $stats['goals_conceded'] > 0)
                {
                    $goals_conceded = $stats['goals_conceded'];
                    $score                          = $score + ( $goals_conceded * $formula['normal']['GOAL_CONCEDED_GK_DEF']) ;
                    $score_key['GOAL_CONCEDED_GK_DEF'] = ($goals_conceded * $formula['normal']['GOAL_CONCEDED_GK_DEF']) ;

                    $final_break_down["card_penalties"]["GOAL_CONCEDED"] = ($goals_conceded * $formula['normal']['GOAL_CONCEDED_GK_DEF']);
                    $total_final_breakdown["card_penalties"]["GOAL_CONCEDED"] = (!empty($total_final_breakdown["card_penalties"]["GOAL_CONCEDED"])) ? ($goals_conceded * $formula['normal']['GOAL_CONCEDED_GK_DEF']) + $total_final_breakdown["card_penalties"]["GOAL_CONCEDED"] : ($goals_conceded * $formula['normal']['GOAL_CONCEDED_GK_DEF']);
                }

                //Goal Conceded by Defender
                if($stats['position'] == 'DF' && $stats['goals_conceded'] > 0)
                {
                    $goals_conceded = $stats['goals_conceded'];
                    $score                          = $score + ( $goals_conceded * $formula['normal']['GOAL_CONCEDED_GK_DEF']) ;
                    $score_key['GOAL_CONCEDED_GK_DEF'] = ($goals_conceded * $formula['normal']['GOAL_CONCEDED_GK_DEF']) ;

                    $final_break_down["card_penalties"]["GOAL_CONCEDED"] = ($goals_conceded * $formula['normal']['GOAL_CONCEDED_GK_DEF']);
                    $total_final_breakdown["card_penalties"]["GOAL_CONCEDED"] = (!empty($total_final_breakdown["card_penalties"]["GOAL_CONCEDED"])) ? ($goals_conceded * $formula['normal']['GOAL_CONCEDED_GK_DEF']) + $total_final_breakdown["card_penalties"]["GOAL_CONCEDED"] : ($goals_conceded * $formula['normal']['GOAL_CONCEDED_GK_DEF']);
                }

                //penalty missed
                if($stats['penalty_misses'] > 0)
                {
                    $score = $score + ($stats['penalty_misses'] * $formula['normal']['PENALTY_MISSED'] );  
                    $score_key['PENALTY_MISSED']  = ($stats['penalty_misses'] * $formula['normal']['PENALTY_MISSED'] ); 

                    $final_break_down["card_penalties"]["PENALTY_MISSED"] = ($stats['penalty_misses'] * $formula['normal']['PENALTY_MISSED'] );
                    $total_final_breakdown["card_penalties"]["PENALTY_MISSED"] = (!empty($total_final_breakdown["card_penalties"]["PENALTY_MISSED"])) ? ($stats['penalty_misses'] * $formula['normal']['PENALTY_MISSED'] ) + $total_final_breakdown["card_penalties"]["PENALTY_MISSED"] : ($stats['penalty_misses'] * $formula['normal']['PENALTY_MISSED'] );
                }

                /* ####### NORMAL SCORING RULE  END ########### */
                
                $each_player_score['score']         = $clean_sheet+$score;
                $each_player_score['normal_score']  = $score;
                $each_player_score['clean_sheet']   = $clean_sheet;
                $each_player_score['break_down']    = json_encode($score_key);

				//booster
				$booster_arr = array();				
				$booster_arr = $this->get_booster_break_down($score_key);				
				$each_player_score['booster_break_down'] = json_encode($booster_arr);
				//log_message("error", "Player UID => ".$each_player_score['player_uid'])	;
				//log_message("error", "booster_break_down => ".$each_player_score['booster_break_down']);	

                //For all breakdown Dated 30 May
                $each_player_score['final_break_down'] = json_encode($total_final_breakdown);
              
                $all_player_scores[] = $each_player_score;
            }

		}// End  of  Empty of game_ stats

		if(!empty($all_player_scores))
		{
            //echo "<pre>";print_r($all_player_scores);die;
            //Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
            //Update score first zero then update
			$this->db->where('season_id', $season_id);
			$this->db->where('league_id', $league_id);
			$this->db->update(GAME_PLAYER_SCORING,array("normal_score"=>"0","bonus_score"=>"0","score"=>"0","break_down"=>NULL,"final_break_down"=>NULL,"booster_break_down"=>NULL));
			//Update all player score
			$this->save_player_scoring($all_player_scores);

			//Trasaction End
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE )
            {
                $this->db->trans_rollback();
            }
            else
            {
                $this->db->trans_commit();
            }
			echo "Calculate fantasy Point for Match [<b>$season_id</b>]<br>";
		} 
	}

	/**
     * function used for Save individual player score on table based on game id
     * @param array $player_score
     * @return boolean
     */
	protected function save_player_scoring($player_score)
	{
		$table_value = array();
		$sql = "REPLACE INTO " . $this->db->dbprefix(GAME_PLAYER_SCORING) . " (season_id, player_id, week, scheduled_date, normal_score, score, league_id, break_down,final_break_down,booster_break_down)
							VALUES ";

		foreach ($player_score as $player_unique_id => $value)
		{
			$main_score = $value['normal_score'];

			$str = " ('" . $value['season_id'] . "','" . $value['player_id'] . "','" . $value['week'] . "','" . $value['scheduled_date'] . "','" . $value['normal_score'] . "','" . $main_score . "','" . $value['league_id'] . "','" . $value['break_down'] . "','". $value['break_down'] . "','". $value['booster_break_down'] . "' )";

			$table_value[] = $str;
		}

		$sql .= implode(", ", $table_value);

		$this->db->query($sql);
		return true;
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
			//echo "DB -<pre>";print_r($team_ids);die;

            $home_id = $team_ids[$value['home_uid']];
            $away_id = $team_ids[$value['away_uid']];
            if($home_id == '' || $away_id == '' )
	        {
	        	exit();
	        }

       		$match_info = array(
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
                                "scoring_alert"		=> (isset($value['scoring_alert'])) ? $value['scoring_alert'] : 0,
								"squad_verified"	=> 	(isset($value['squad_verified'])) ? $value['squad_verified'] : 0
                        );

			$season_info = $this->get_single_row("season_id,is_updated_playing,scoring_alert,delay_by_admin,notify_by_admin,delay_minute,season_scheduled_date", SEASON, array("season_game_uid" => $season_game_uid,"league_id" => $league_info['league_id']));
			$season_id = $season_info['season_id'];
			
			if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 0){
				$match_info['delay_minute'] = $value['delay_minute'];
				$match_info['delay_message'] = $value['delay_message'];
				if(isset($match_info['delay_minute']) && $match_info['delay_minute'] > 0 && $season_info['season_scheduled_date'] > format_date()){
					$match_info['season_scheduled_date'] = date('Y-m-d H:i:s', strtotime('+'.$match_info['delay_minute'].' minutes', strtotime($value['season_scheduled_date'])));
					$match_info['scheduled_date'] = date('Y-m-d', strtotime($value['season_scheduled_date']));
				}elseif($value['season_scheduled_date'] > format_date()){
					$match_info['season_scheduled_date'] = $value['season_scheduled_date'];
					$match_info['scheduled_date'] = $value['scheduled_date'];
				}
			}else if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 1){
				$match_info['season_scheduled_date'] = $season_info['season_scheduled_date'];
				$match_info['scheduled_date'] = date('Y-m-d', strtotime($match_info['season_scheduled_date']));
			}elseif($value['season_scheduled_date'] > format_date()){
				$match_info['season_scheduled_date'] = $value['season_scheduled_date'];
				$match_info['scheduled_date'] = $value['scheduled_date'];
			}else{
				exit('Time over');
			}
			if(isset($season_info['notify_by_admin']) && $season_info['notify_by_admin'] == 0){
				$match_info['custom_message'] = $value['custom_message'];
			}
			if(isset($season_info['is_updated_playing']) && $season_info['is_updated_playing'] == 0){
				if(isset($value['playing_list']) && $value['playing_list'] != "" && count(json_decode($value['playing_list'])) > 0){
					$playing_11_player = $this->get_player_data_by_player_uid($season_id,$sports_id,json_decode($value['playing_list']));
					$match_info['playing_list'] = json_encode(array_column($playing_11_player,'player_id'));
					$match_info['playing_announce'] = $value['playing_announce'];
					$match_info['lineup_announced_at'] = format_date();
				}
				if(isset($value['substitute_list']) && $value['substitute_list'] != "" && count(json_decode($value['substitute_list'])) > 0){
					$subtitute_11_player = $this->get_player_data_by_player_uid($season_id,$sports_id,json_decode($value['substitute_list']));
					$match_info['substitute_list'] = json_encode(array_column($subtitute_11_player,'player_id'));
				}
			}
			
			//$match_data[] = $match_info;
			$match_keys = array();
            $match_k = $match_info['league_id'].'_'.$match_info['season_game_uid'];
            $match_data[$match_k] = $match_info;
            $match_keys[] = $match_k; 
			//echo "<pre>";print_r($match_data);die;
			if (!empty($match_data))
			{
                $concat_key = 'CONCAT(league_id,"_",season_game_uid)';
                $this->insert_or_update_on_exist($match_keys,$match_data,$concat_key,SEASON,'season_id');

                //update match cache and bucket files
		        $this->db->select("CS.collection_master_id")
		                ->from(COLLECTION_SEASON . " CS")
		                ->join(COLLECTION_MASTER.' as CM', 'CM.collection_master_id = CS.collection_master_id',"INNER")
		                ->where('CM.league_id', $league_info['league_id'])
		                ->where('CS.season_id', $season_id);
		        $sql = $this->db->get();
		        $collection_info = $sql->row_array();
				if(!empty($collection_info)){
					$this->load->helper('queue');
		            $server_name = get_server_host_name();

					$file_data_arr = array();
					$collection_master_id = $collection_info['collection_master_id'];
					//update match delay data
					if($season_info['delay_by_admin'] == 0 && isset($match_info['delay_minute']) && $match_info['delay_minute'] > 0){
						$match_info['collection_master_id'] = $collection_master_id;
						if(isset($match_info['season_scheduled_date']))
						{	
							$this->update_match_delay_time($match_info);
							$file_data_arr['cache'][] = "fixture_".$collection_master_id;
						}
					}

					if($season_info['notify_by_admin'] == 0 && isset($match_info['custom_message']) && $match_info['custom_message'] != ""){
						$file_data_arr['cache'][] = "fixture_".$collection_master_id;
		        	}
					
					if(isset($match_info['playing_announce']) && $match_info['playing_announce'] == 1){
						//Start update bot user player with lineup out player
						$content = array();
			    		$content['url'] = $server_name."/cron/cron/sync_bot_teams/".$collection_master_id;
			    		add_data_in_queue($content,'pl_teams');
						//End
						
						$content = array();
			    		$content['url'] = $server_name."/cron/cron/pl_match_teams/".$season_id."?lineup_out=1";
			    		add_data_in_queue($content,'pl_teams');
			    		if(PL_LOG_TX){
			    			log_message("error","LINEUPOUT TRIGGER : MATCH : $season_game_uid | TIME : ".format_date());
			    		}

						$file_data_arr['cache'][] = "roster_list_".$collection_master_id;
						$file_data_arr['bucket'][] = "collection_roster_list_".$collection_master_id;
		        		
		        		$file_data_arr['cache'][] = "fixture_".$collection_master_id;
					}

		        	//scoring alert
		        	if(isset($season_info['scoring_alert']) && $season_info['scoring_alert'] != $match_info['scoring_alert']){
		        		$file_data_arr['cache'][] = "fixture_".$collection_master_id;
		        	}

		        	if(!empty($file_data_arr)){
						$this->remove_cache_bucket_data($file_data_arr);
		        	}

					//push data in queue for delete contest files
		            $content = array();
		            $content['url'] = $server_name."/cron/cron/update_lobby_fixture_data/".$sports_id;
		            add_data_in_queue($content,'cron');
		            //add reschduled api
					$content = array();
		    		$content['url'] = $server_name."/cron/dfs/contest_rescheduled";
		    		add_data_in_queue($content,'season_cron');
				}

                echo "<br>Fixture id " .$value['season_game_uid']." Matches (seasons) inserted.<br>";
			}
		}
		exit();	
    }

    /**
     * This function used for calculate player fantasy point for single match
     * @param int $sports_id
     * @param int $league_id
     * @param string $season_game_uid
     * @return boolean
     */
	public function calculated_fantasy_score_by_match_id($sports_id,$league_id,$season_game_uid)
	{
		// Get All Live Games List 
		$season_game = $this->get_season_match_details($season_game_uid,$league_id);
		//echo "<pre>"; print_r($season_game);die;
		if (!empty($season_game))
		{
			$formula = $this->get_scoring_rules($sports_id);
			$this->calculate_soccer_fantasy_points($sports_id,$season_game,$formula);	
		} // End of Empty team check
		exit();
	}

	 /************Update week api for weekly game***************/
	private function create_season_week($league_id , $season , $year )
	{
		$sql = $this->db->select("MAX(season_scheduled_date) AS max_date ,MIN(season_scheduled_date) AS min_date")
						->from(SEASON)
						->where("league_id",$league_id)
						->where("type",$season)
						->where("year",$year) 
						->group_by('api_week')
						->order_by('api_week','ASC')
						->get();	
		$result = $sql->result_array();		
		
		if(!empty($result))
		{
			foreach ($result as $key => $value) 
			{
				$data[] = array(
									'type'          			    => trim($season),
									'season_week'          			=> trim($key+1),
									'season_week_start_date_time' 	=> trim($value['min_date']),
									'season_week_end_date_time'     => trim($value['max_date']),
									'season_week_close_date_time'   => date( "Y-m-d H:i:s", strtotime( trim($value['max_date']). " +1 day" ) ),
									'league_id'         			=> $league_id,
									'year'							=> $year
								);
				if( array_key_exists( $key+1 , $result))
				{
					$data[$key]['season_week_close_date_time'] =  date("Y-m-d H:i:s", strtotime("-30 minutes", strtotime( $result[$key+1]['min_date'] )));
				}
			}
		}
		if(!empty($data))
		{
			//echo "<pre>";print_r($data);die;
			$this->db->delete(SEASON_WEEK,array('league_id' => $league_id,'type'=>$season, 'year' => $year));
			$this->replace_into_batch(SEASON_WEEK, $data);
		}	
	}
	/**
	 * @Summary: This function for use update season week in season table from season_week table  
	 * @access: protected
	 * @param: $league_id,$season_type,$season_year
	 * @return: 
	 */
	
	protected function update_season_week($league_id,$season_type,$season_year)
	{	
		$sql = "SELECT 
						season_game_uid,season_scheduled_date
					FROM 
						".$this->db->dbprefix(SEASON)."	
					WHERE
						league_id = $league_id	
					AND
						week = '0'
					AND
						type = '".$season_type."'
					AND
						year = '".$season_year."'
					";
			
		$rs = $this->db->query($sql);
		$result = $rs->result_array();
		//echo "<pre>";print_r($result);	die;
		if(!empty($result))
		{
			foreach($result as $key=>$value)
			{
				$sql = "SELECT 
							season_week
						FROM 
							".$this->db->dbprefix(SEASON_WEEK)."	
						WHERE
							league_id = $league_id	
						AND
							DATE_FORMAT(season_week_start_date_time,'%Y-%m-%d') <= '".$value['season_scheduled_date']."'
						AND
							type = '".$season_type."'
						AND
							year = '".$season_year."'	
						ORDER BY 
							season_week DESC
						LIMIT 1			
						";
				$rs2 = $this->db->query($sql);
				$result2 = $rs2->row_array();
				// echo "<pre>";print_r($result2['season_week']);	die;
				if(!empty($result2))
				{
					$update_sql = "UPDATE ".$this->db->dbprefix(SEASON)." 
									SET 
										week = ".$result2['season_week']." 
									WHERE 
										league_id = $league_id	
									AND
										type = '".$season_type."'
									AND
										year = '".$season_year."'
									AND	
										season_game_uid = '".$value['season_game_uid']."' 
							 	";
					$rs = $this->db->query($update_sql);
				}
			}
		}
	}
	protected function update_season_week_rescheduled($league_id , $season , $year )
	{
		$sql = $this->db->select("season_week_start_date_time AS start_date,season_week_end_date_time AS end_date")
						->from(SEASON_WEEK)
						->where("league_id",$league_id)
						->where("type",$season)
						->order_by('season_week','ASC')
						->get();   
		$result = $sql->result_array();       
		//echo "<pre>";print_r($result);die;
		if(!empty($result))
		{
			$previoid_end_date = "";
			foreach ($result as $key => $value)
			{
				//echo $key.'-';echo $value['start_date'];echo "--";print_r($value['end_date']);echo "<pre>" ;
				//echo $previoid_end_date.'-'.$value['end_date'];echo "<pre>";
				if(strtotime($previoid_end_date) > strtotime($value['start_date']))
				{
					//echo "<pre>" ;echo $key;echo "<pre>";die;
					$sql = "SELECT
								DATE_FORMAT(season_scheduled_date,'%Y-%m-%d') as scheduled_date
							FROM
								".$this->db->dbprefix(SEASON)."   
							WHERE
								league_id = $league_id   
							AND
								type =     '".$season."'
							AND
								year =     $year   
							AND
								week = $key
							AND
								season_scheduled_date < '".$value['start_date']."'
							ORDER BY
								season_scheduled_date DESC
							LIMIT 1           
						   ";
					//echo $sql;
					//echo "<pre>";      
					$rs = $this->db->query($sql);
					$result1 = $rs->row_array();
					//print_r($result1);echo "<pre>";//continue;die;     
					if(!empty($result1))
					{   
					  $update_sql = "UPDATE
										".$this->db->dbprefix(SEASON_WEEK)."
									SET
										season_week_end_date_time     = '".trim($result1['scheduled_date'].' 23:59:59')."',
										season_week_close_date_time = '".trim($result1['scheduled_date'].' 23:59:59')."'
									WHERE
										`type` =  '".$season."'
									AND
										`season_week` = $key
									AND 
										`league_id` = $league_id
									";
						//echo "<pre>";
						$this->db->query($update_sql);
						//echo "<pre>";print_r($result);die;
					}
					
				}
				$previoid_end_date = $value['end_date'];   
			}                           
		}
	}

	private function get_booster_break_down($final_break_down){
		$booster_arr = array("OWN_GOAL"=>"0", "GOAL"=>"0", "SHARP_SHOOTER"=>"0", "SAVER"=>"0", "NO_RED_ZONE"=>"0");
		if(isset($final_break_down["OWN_GOAL"])){
			$booster_arr["OWN_GOAL"] = $final_break_down["OWN_GOAL"];
		}
		if(isset($final_break_down["GOAL_DEF_GK"])){
			$booster_arr["GOAL"] = $booster_arr["GOAL"] + $final_break_down["GOAL_DEF_GK"];
		}
		if(isset($final_break_down["GOAL_MID_FIELDER"])){
			$booster_arr["GOAL"] = $booster_arr["GOAL"] + $final_break_down["GOAL_MID_FIELDER"];
		}
		if(isset($final_break_down["GOAL_STRIKER"])){
			$booster_arr["GOAL"] = $booster_arr["GOAL"] + $final_break_down["GOAL_STRIKER"];
		}
		if(isset($final_break_down["SHOT_ON_TARGET"])){
			$booster_arr["SHARP_SHOOTER"] = $final_break_down["SHOT_ON_TARGET"];
		}
		if(isset($final_break_down["PENALTY_SAVED_GK"])){
			$booster_arr["SAVER"] = $booster_arr["GOAL"] + $final_break_down["PENALTY_SAVED_GK"];
		}
		if(isset($final_break_down["SAVES_GK"])){
			$booster_arr["SAVER"] = $booster_arr["GOAL"] + $final_break_down["SAVES_GK"];
		}
		if(isset($final_break_down["RED_CARD"])){
			$booster_arr["NO_RED_ZONE"] = $final_break_down["RED_CARD"];
		}

		return $booster_arr;
	}
}	
