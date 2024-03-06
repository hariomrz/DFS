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
		$this->api = $this->get_cricket_config_detail('vinfotech');
		
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
        $sql = $this->db->select("GSC.*,PT.position", FALSE)
                ->from(GAME_STATISTICS_CRICKET . " AS GSC")
                ->join(PLAYER . " AS P", "P.player_id = GSC.player_id AND P.sports_id = $sports_id ", 'INNER')
                ->join(PLAYER_TEAM . " AS PT", "PT.player_id = P.player_id AND PT.player_id = GSC.player_id AND PT.season_id = '".$season_id."' ", 'INNER')
                ->where("GSC.season_id", $season_id)
                ->where("GSC.league_id", $league_id)
                ->where("GSC.innings > ", "0")
				->group_by("GSC.innings")
				->group_by("P.player_id")
                ->get();
                //echo $this->db->last_query();die;
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
	                //set/update team cache
		            //$team_cache = 'team_'.$sports_id;
		           // $team_ids = $this->get_team_id_with_team_uid($sports_id);
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
			//echo "<pre>";print_r($team_ids);die;
			foreach ($league_data as $league)
			{
			   	$url = $this->api['api_url']."get_league_season?league_uid=".$league['league_uid']."&token=".$this->api['access_token'];
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
	                    if($home_id == '' || $away_id == '' )
	                    {
	                       	continue;
	                    }

                   		$season_info = $this->get_single_row("is_updated_playing,scoring_alert,delay_by_admin,notify_by_admin,delay_minute,delay_message,custom_message,2nd_inning_date,second_inning_update,season_scheduled_date", SEASON, array("season_game_uid" => $value['season_game_uid'],"league_id" => $league['league_id']));

                   		$secong_inning_date = NULL;
                   		$allow_2nd_inning = isset($this->app_config['allow_2nd_inning']) ? $this->app_config['allow_2nd_inning']['key_value'] : 0;
                   		
                   		//echo "<pre>";print_r($allow_2nd_inning);die;
			            				
						//if match delay set from feed then modify match schedule date time
						if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 1)
						{
							$value['delay_minute'] = $season_info['delay_minute'];
							$value['delay_message'] = $season_info['delay_message'];
							$value['season_scheduled_date'] = date('Y-m-d H:i:s', strtotime('+'.$season_info['delay_minute'].' minutes', strtotime($season_info['season_scheduled_date'])));
							$value['scheduled_date'] = date('Y-m-d', strtotime($value['season_scheduled_date']));
							if($allow_2nd_inning == 1 && in_array($value['format'],array(1,3)))
				            {
			                    $second_inning_interval = second_inning_game_interval($value['format']);
			                    $secong_inning_date = date("Y-m-d H:i:s",strtotime($season_info['season_scheduled_date'].' +'.$second_inning_interval.' minutes'));
				            }


						}else{
							if($allow_2nd_inning == 1 && in_array($value['format'],array(1,3)))
				            {
			                    $second_inning_interval = second_inning_game_interval($value['format']);
			                    $value['season_scheduled_date'] = date('Y-m-d H:i:s', strtotime('+'.$value['delay_minute'].' minutes', strtotime($value['season_scheduled_date'])));
			                    $secong_inning_date = date("Y-m-d H:i:s",strtotime($value['season_scheduled_date'].' +'.$second_inning_interval.' minutes'));
				            }
						}

						if(isset($season_info['2nd_inning_date']) && $season_info['second_inning_update'] == 1)
						{
							$secong_inning_date = $season_info['2nd_inning_date'];
						}

						if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 0 && $value['delay_minute'] && $value['delay_minute'] > 0){
							$value['season_scheduled_date'] = date('Y-m-d H:i:s', strtotime('+'.$value['delay_minute'].' minutes', strtotime($value['season_scheduled_date'])));
							$value['scheduled_date'] = date('Y-m-d', strtotime($value['season_scheduled_date']));
						}

						if(isset($season_info['notify_by_admin']) && $season_info['notify_by_admin'] == 1){
							$value['custom_message'] = $season_info['custom_message'];
						}
						
						$playing_eleven_confirm = 0;
						if(isset($value['playing_eleven_confirm'])){
							$playing_eleven_confirm = $value['playing_eleven_confirm'];
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

						
						$season_k = $league['league_id'].'_'.$value['season_game_uid'];

						$temp_match_array = array(
                                            "league_id" 		=> $league['league_id'],
                                            "season_game_uid" 	=> $value['season_game_uid'],
                                            "title" 				=> $value['title'],
                                            "subtitle" 				=> $value['subtitle'],
                                            "venue" 				=> $value['venue'],
                                            "year" 				=> $value['year'],
                                            "type" 				=> 'REG',
                                            "format" 			=> $value['format'],
                                            "feed_date_time" 	=> $value['feed_date_time'],
                                            "season_scheduled_date" => $value['season_scheduled_date'],
                                            "scheduled_date" 	=> $value['scheduled_date'],
                                            "home_id" 			=> $home_id, 
                                            "away_id" 			=> $away_id,
                                            
                                            "playing_eleven_confirm" => $playing_eleven_confirm,
                                            "delay_minute" 		=> $value['delay_minute'],
                                            "delay_message" 	=> $value['delay_message'],
                                            "custom_message" 	=> $value['custom_message'],
                                            "scoring_alert"		=> (isset($value['scoring_alert'])) ? $value['scoring_alert'] : 0,
											"squad_verified"	=> 	(isset($value['squad_verified'])) ? $value['squad_verified'] : 0 ,
											"2nd_inning_date" => $secong_inning_date,
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
    public function get_scores($sports_id,$producer_score_data = array())
    {
    	$season_game_uid = "";
    	$producer_data = array();
    	if(!empty($producer_score_data))
    	{
    		$producer_data = json_decode($producer_score_data,TRUE);
    		$season_game_uid = $producer_data['response']['match_info']['match_id'];
    	}	
    	
    	//echo '<pre>Producer: ';print_r($producer_data);die;
    	//$season_game_uid = "48291";
    	$current_game = $this->get_season_match_for_score($sports_id,$season_game_uid);
 		//echo '<pre>Live Match : ';print_r($current_game);die;
		if (!empty($current_game))
		{
			//trasaction start
			$this->db->trans_strict(TRUE);
        	$this->db->trans_start();
			$this->load->helper('queue');
			foreach ($current_game as $season_game)
			{
				$home_id = $season_game['home_id'];
				$away_id = $season_game['away_id'];
				$team_ids[$season_game['home_uid']] = $home_id;
				$team_ids[$season_game['away_uid']] = $away_id;
				$season_game_uid = $season_game['season_game_uid'];
				$season_id = $season_game['season_id'];
				$league_id = $season_game['league_id'];
				$format = $season_game['format'];
				if(empty($producer_data))
				{	
					$url = $this->api['api_url']."get_scores?match_id=".$season_game_uid."&token=".$this->api['access_token'];
					//echo $url ;die;
					$match_data = @file_get_contents($url);
					if(!$match_data)
					{
						continue;
					}	
					
					$match_array = @json_decode(($match_data), TRUE);
				}else{
					$match_array = $producer_data;
				}	

				//echo "<pre>";print_r($match_array);die;
				if(@$match_array['status'] == 'error')
				{
					exit("Feed token expire");
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
				$score_keys = array();
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
	                        "season_id" 				=> $season_id,
	                        "team_id" 				    => $team_id,
	                        "player_id" 				=> $player_id,
	                        "scheduled_date" 			=> $scheduled_date,
	                        "game_status" 				=> $match_status,
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
	                        "man_of_match"				=> $value['man_of_match']
	                    );
	                    
					}

					//echo "<pre>";print_r($score_data);die;
					if(!empty($score_data))
					{
						$this->replace_into_batch(GAME_STATISTICS_CRICKET, array_values($score_data));

						//delete player playing_11 0
						$this->db->where(array("season_id" => $season_id,"league_id" => $league_id,"innings" => 1,"playing_11" => 0));
						$this->db->delete(GAME_STATISTICS_CRICKET);
						echo "<pre>";  echo "Match [<b>$season_id</b>] score updated";
					}
				}
				
				//match info
				//echo "<pre>";print_r($match_info);//die;	
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
					$season_update_array = array("status" => $status,"status_overview" => $status_overview,"score_data"=>$match_info['score'],"result_info"=>$match_info['result_info']);
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

					// "winner": 6995, "decision": 2, Decision-1=>Batting, Decision-2=>Fielding 

					$toss = json_decode($match_info['toss'],TRUE);
					//echo "<pre>";print_r($toss);//die;
					//echo "<pre>";print_r($season_game);die;
					if(isset($toss['winner']) && $toss['winner'] == $season_game['home_uid'])
					{
						$toss['winner'] = $season_game['home_id'];
					}elseif(isset($toss['winner']) && $toss['winner'] == $season_game['away_uid'])
					{
						$toss['winner'] = $season_game['away_id'];
					}
					//echo "<pre>";print_r($toss);//die;
					$season_update_array['2nd_inning_team_id'] = null;
					if(@$toss['decision'] == '2')
					{
						$season_update_array['2nd_inning_team_id'] = $toss['winner'];
					}
					elseif (@$toss['decision'] == '1' && $toss['winner'] == $season_game['home_id'] ) 
					{
						$season_update_array['2nd_inning_team_id'] = $season_game['away_id'];
					}elseif (@$toss['decision'] == '1' && $toss['winner'] == $season_game['away_id'] ) 
					{
						$season_update_array['2nd_inning_team_id'] = $season_game['home_id'];
					}

					//echo "<pre>";print_r($match_info);die;

					if(isset($match_info['batting_team_uid']))
					{
						if($match_info['batting_team_uid'] == $season_game['home_uid'])
						{
							$season_update_array['batting_team_id'] = $season_game['home_id'];
						}elseif($match_info['batting_team_uid'] == $season_game['away_uid'])
						{
							$season_update_array['batting_team_id'] = $season_game['away_id'];
						}	
					}

					if(isset($match_info['batting_order']) && !empty($match_info['batting_order']))
					{
						$bo = json_decode($match_info['batting_order'],TRUE);
						$team_id_0 = @$team_ids[$bo[0]['team_uid']];
						$team_id_1 = @$team_ids[$bo[1]['team_uid']];
						$team_id_2 = @$team_ids[$bo[2]['team_uid']];
						$team_id_3 = @$team_ids[$bo[3]['team_uid']];
						//echo "<pre>";print_r(count($bo));die;
						$bo[0]['team_id'] = $team_id_0;
						if(count($bo) >= 2)
						{
							$bo[1]['team_id'] = $team_id_1;
						}

						if(count($bo) >= 3)
						{
							$bo[2]['team_id'] = $team_id_2;
						}
						if(count($bo) == 4)
						{
							$bo[3]['team_id'] = $team_id_3;
						}
						//echo "<pre>";print_r($bo);continue;die;
						$season_update_array['team_batting_order'] = $match_info['batting_order'];
						
					}

					
					//for second inning date update if allow 
					$allow_2nd_inning = isset($this->app_config['allow_2nd_inning']) ? $this->app_config['allow_2nd_inning']['key_value'] : 0;
					if($allow_2nd_inning == 1 && in_array($format,array(1,3)) && $season_game['second_inning_update'] == 0)
				    {
				    	$second_inning_delay = 20;
				    	$cdt = date('Y-m-d H:i:s', strtotime('+'.$second_inning_delay.' minutes', strtotime(format_date())));
				    	if($match_info['latest_inning_number'] <= 1 && $cdt > $season_game['2nd_inning_date'] && $season_game['2nd_inning_date'] > format_date() )
				    	{
				    		$season_update_array['2nd_inning_date'] = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(format_date())));
				    	}elseif($match_info['latest_inning_number'] > 1 && $season_game['2nd_inning_date'] >= format_date())
				    	{
				    		$season_update_array['2nd_inning_date'] = format_date();
				    	}

				    	$this->load->helper('queue');
		            	$server_name = get_server_host_name();
				    	$content        = array();
				        $content['url'] = $server_name."/cron/cricket/vinfotech/second_inning_contest_rescheduled/".$season_id;
				        add_data_in_queue($content, "season_cron");
				    }	

					//echo "<pre>";print_r($season_update_array);die;	

					$this->db->where("league_id",$league_id);
					$this->db->where("season_game_uid",$season_game_uid);
					$this->db->update(SEASON,$season_update_array);
					//Foll of wicket inofrmation save in table 	
					if(!empty($match_info['fall_of_wickets']) && 
						is_string($match_info['fall_of_wickets']) && is_array(json_decode($match_info['fall_of_wickets'], true))
						)
					{	
						$fow[] =  array(
										"season_id" => $season_id,
										"fall_of_wickets" => $match_info['fall_of_wickets'],
									);
						//echo "<pre>";print_r($fow);die;
						$this->replace_into_batch(CRICKET_FOW, array_values($fow));
					}
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
		if(empty($producer_data))
		{
			exit();	
		}else{
			return true;
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
			//print_r($formulas);
			foreach ($current_game as $season_game)
			{   
				//echo "<pre>"; print_r($season_game);die;
				if($season_game["format"] == CRICKET_TEST)
				{
					$this->calculate_fantasy_points_for_test($sports_id,$season_game);
				}elseif($season_game["format"] == CRICKET_ONE_DAY)
				{
					$this->calculate_fantasy_points_for_odi($sports_id,$season_game);
				}elseif($season_game["format"] == CRICKET_T20)
				{
					$this->calculate_fantasy_points_for_ttwenty($sports_id,$season_game);
				}elseif($season_game["format"] == CRICKET_T10)
				{
					$this->calculate_fantasy_points_for_tten($sports_id,$season_game);
				}	
			}// End of current game loop  
		} // End of Empty check
		exit();
	}

	// CALCULATE FANTASY POINT FOR TEST MATCH
	private function calculate_fantasy_points_for_test($sports_id,$season_game)
	{
		// Get All Scoring Rules
		$season_id				= $season_game['season_id'];
		$season_game_uid		= $season_game['season_game_uid'];
		$league_id				= $season_game['league_id'];
		$all_player_scores		= array();
		$all_player_testing_var	= array();
        $total_final_breakdown = array();
        $player_final_breakdown = array();

		// Get Match Scoring Statistics to Calculate Fantasy Score
		$game_stats = $this->get_game_statistics_by_season_id($season_id,$season_game['sports_id'],$season_game['league_id']);
		//echo "<pre>";print_r($game_stats);die;
		if (!empty($game_stats))
		{
			foreach ($game_stats as $player_stats)
			{
				$formulas = $this->get_scoring_rules($sports_id, $season_game['format']);
				//echo "<pre>"; print_r($formulas);die;
				if(empty($formulas))
				{
					continue;
				}
				$normal_score		= 0;
				$bonus_score		= 0;
				$economy_rate_score	= 0;
				$strike_rate_score	= 0;
				$score				= 0;
		        
		        $final_break_down = array();
				$final_break_down["starting_points"] 	= 0;
				$final_break_down["batting"] 			= array();
				$final_break_down["bowling"] 			= array();
				$final_break_down["fielding"] 			= array();

				$each_player_score = array(
							"season_id"				=> $player_stats['season_id'],
							"player_id"				=> $player_stats['player_id'],
							"week"					=> $player_stats['week'],
							"scheduled_date"		=> $player_stats['scheduled_date'],
							"league_id"				=> $league_id,
							"normal_score"			=> 0,
							"bonus_score"			=> 0,
							"economy_rate_score"	=> 0,
							"strike_rate_score"		=> 0,
							"score"					=> 0
				);
				//echo "<pre>";print_r($each_player_score);continue;
				$normal_testing_var = $bonus_testing_var = $economy_rate_testing_var = $strike_rate_testing_var = array();
				/* ####### NORMAL SCORING RULE ########### */
				// STARTING_11 
				if ($player_stats['innings'] == 1 && $player_stats['playing_11'] == '1')
				{
					$normal_score = $normal_score + (1 * $formulas['normal']['STARTING_11'] );
					$normal_testing_var['STARTING_11'] = ( 1 * $formulas['normal']['STARTING_11'] );
		                                        
		            $final_break_down["starting_points"] = (1 * $formulas['normal']['STARTING_11']);
					$total_final_breakdown[$player_stats['player_id']]["starting_points"] = (!empty($total_final_breakdown[$player_stats['player_id']]["starting_points"])) ? (1 * $formulas['normal']['STARTING_11']) + $total_final_breakdown[$player_stats['player_id']]["starting_points"] : (1 * $formulas['normal']['STARTING_11']);
				}

				// Runs
				if ($player_stats['batting_runs'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['batting_runs'] * $formulas['normal']['RUN'] );
					$normal_testing_var['RUN'] = ( $player_stats['batting_runs'] * $formulas['normal']['RUN'] );
		                                        
		            $final_break_down["batting"]["RUN"] = ($player_stats['batting_runs'] * $formulas['normal']['RUN']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]["RUN"] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]["RUN"])) ? ($player_stats['batting_runs'] * $formulas['normal']['RUN']) + $total_final_breakdown[$player_stats['player_id']]["batting"]["RUN"] : ($player_stats['batting_runs'] * $formulas['normal']['RUN']);
					//echo "<pre>";print_r($total_final_breakdown);//die;
				}

				//Wickets
				if ($player_stats['bowling_wickets'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET'] );
					$normal_testing_var['WICKET'] = ( $player_stats['bowling_wickets'] * $formulas['normal']['WICKET'] );
		                                        
		            $final_break_down["bowling"]["WICKET"] = ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]["WICKET"] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]["WICKET"])) ? ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET'] ) + $total_final_breakdown[$player_stats['player_id']]["bowling"]["WICKET"] : ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET']);
				}

				//Catch
				if ($player_stats['catch'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['catch'] * $formulas['normal']['CATCH'] );
					$normal_testing_var['CATCH'] = ( $player_stats['catch'] * $formulas['normal']['CATCH'] );
		                                        
		            $final_break_down["fielding"]['CATCH'] = ($player_stats['catch'] * $formulas['normal']['CATCH']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['CATCH'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['CATCH'])) ? ($player_stats['catch'] * $formulas['normal']['CATCH']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['CATCH'] : ($player_stats['catch'] * $formulas['normal']['CATCH']);
				}

				//Stumping
				if ($player_stats['stumping'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['stumping'] * $formulas['normal']['STUMPING'] );
					$normal_testing_var['STUMPING'] = ( $player_stats['stumping'] * $formulas['normal']['STUMPING'] );
		                                        
		            $final_break_down["fielding"]['STUMPING'] = ($player_stats['stumping'] * $formulas['normal']['STUMPING']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['STUMPING'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['STUMPING'])) ? ($player_stats['stumping'] * $formulas['normal']['STUMPING']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['STUMPING'] : ($player_stats['stumping'] * $formulas['normal']['STUMPING']);
				}

				//Runout Direct Hit
				if ($player_stats['run_out'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT'] );
					$normal_testing_var['RUN_OUT_DIRECT_HIT'] = ( $player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT'] );
		                                        
		            $final_break_down["fielding"]['RUN_OUT_DIRECT_HIT'] = ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_DIRECT_HIT'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_DIRECT_HIT'])) ? ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_DIRECT_HIT'] : ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT']);
				}

				//Runout run_out_throw
				if ($player_stats['run_out_throw'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER'] );
					$normal_testing_var['RUN_OUT_THROWER'] = ( $player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER'] );
		                                        
		            $final_break_down["fielding"]['RUN_OUT_THROWER'] = ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_THROWER'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_THROWER'])) ? ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_THROWER'] : ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER']);
				}

				//Runout run_out_catch
				if ($player_stats['run_out_catch'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER'] );
					$normal_testing_var['RUN_OUT_CATCHER'] = ( $player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER'] );
		                                        
		            $final_break_down["fielding"]['RUN_OUT_CATCHER'] = ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_CATCHER'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_CATCHER'])) ? ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_THROWER']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_CATCHER'] : ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER']);
				}

				//Dismissal for duck (batsmen, wicket-keeper and all-rounders)
				if ($player_stats['batting_runs'] == 0 && $player_stats['dismissed'] == 1 && $player_stats['position'] != 'BOW')
				{
					$normal_score = $normal_score + (1 * $formulas['normal']['DUCK'] );
					$normal_testing_var['DUCK'] = ( 1 * $formulas['normal']['DUCK'] );
		                                        
		            $final_break_down["batting"]['DUCK'] = (1 * $formulas['normal']['DUCK']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]['DUCK'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['DUCK'])) ? (1 * $formulas['normal']['DUCK']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['DUCK'] : (1 * $formulas['normal']['DUCK']);
				}

				//echo $normal_score;die;
				/* ####### NORMAL SCORING RULE END ########### */


				/* ####### Bonus SCORING ########### */
				// Fours Bonus
				if ($player_stats['batting_fours'] > 0)
				{
					$bonus_score = $bonus_score + ($player_stats['batting_fours'] * $formulas['bonus']['FOUR'] );
					$bonus_testing_var['FOUR'] = ( $player_stats['batting_fours'] * $formulas['bonus']['FOUR'] );
		                                        
		            $final_break_down["batting"]['FOUR'] = ($player_stats['batting_fours'] * $formulas['bonus']['FOUR']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]['FOUR'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['FOUR'])) ? ($player_stats['batting_fours'] * $formulas['bonus']['FOUR']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['FOUR'] : ($player_stats['batting_fours'] * $formulas['bonus']['FOUR']);
				}

				// Sixes Bonus
				if ($player_stats['batting_sixes'] > 0)
				{
					$bonus_score = $bonus_score + ($player_stats['batting_sixes'] * $formulas['bonus']['SIX'] );
					$bonus_testing_var['SIX'] = ( $player_stats['batting_sixes'] * $formulas['bonus']['SIX'] );
		                                        
		            $final_break_down["batting"]['SIX'] = ($player_stats['batting_sixes'] * $formulas['bonus']['SIX']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]['SIX'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['SIX'])) ? ($player_stats['batting_sixes'] * $formulas['bonus']['SIX']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['SIX'] : ($player_stats['batting_sixes'] * $formulas['bonus']['SIX']);
				}
				
				//For HALF_CENTURY & CENTURY
				if ($player_stats['batting_runs'] >= 50 && $player_stats['batting_runs'] < 100)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['HALF_CENTURY'] );
					$bonus_testing_var['HALF_CENTURY'] = ( $formulas['bonus']['HALF_CENTURY'] );
	                                            
	                $final_break_down["batting"]['HALF_CENTURY'] = ($formulas['bonus']['HALF_CENTURY']);

					$total_final_breakdown[$player_stats['player_id']]["batting"]['HALF_CENTURY'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['HALF_CENTURY'])) ? ($formulas['bonus']['HALF_CENTURY']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['HALF_CENTURY'] : ($formulas['bonus']['HALF_CENTURY']);
				}
				else if ($player_stats['batting_runs'] >= 100 )
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['CENTURY'] );
					$bonus_testing_var['CENTURY'] = ( $formulas['bonus']['CENTURY'] );
	                                            
	                $final_break_down["batting"]['CENTURY'] = ($formulas['bonus']['CENTURY']);

					$total_final_breakdown[$player_stats['player_id']]["batting"]['CENTURY'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['CENTURY'])) ? ($formulas['bonus']['CENTURY']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['CENTURY'] : ($formulas['bonus']['CENTURY']);
				}
					
				//For FOUR_WICKET & FIVE_WICKET HAUL
				if ($player_stats['bowling_wickets'] == 4)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['FOUR_WICKET'] );
					$bonus_testing_var['FOUR_WICKET'] = ( $formulas['bonus']['FOUR_WICKET'] );
	                                            
	                $final_break_down["bowling"]['FOUR_WICKET'] = ($formulas['bonus']['FOUR_WICKET']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['FOUR_WICKET'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['FOUR_WICKET'])) ? ($formulas['bonus']['FOUR_WICKET']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['FOUR_WICKET'] : ($formulas['bonus']['FOUR_WICKET']);
				}
				else if ($player_stats['bowling_wickets'] >= 5)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['FIVE_WICKET'] );
					$bonus_testing_var['FIVE_WICKET'] = ( $formulas['bonus']['FIVE_WICKET'] );
	                                            
	                $final_break_down['bowling']['FIVE_WICKET'] = ($formulas['bonus']['FIVE_WICKET']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['FIVE_WICKET'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['FIVE_WICKET'])) ? ($formulas['bonus']['FIVE_WICKET']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['FIVE_WICKET'] : ($formulas['bonus']['FIVE_WICKET']);
				}

				//Bowled and LBW Bonus
				if (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) > 0)
				{
					$bonus_score = $bonus_score + (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED'] );
					$bonus_testing_var['LBW_BOWLED'] = ( ($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED'] );
		                                        
		            $final_break_down["bowling"]['LBW_BOWLED'] = (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED']);
		            
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['LBW_BOWLED'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['LBW_BOWLED'])) ? (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['LBW_BOWLED'] : (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED']);
				}
				
				
				/* ####### Bonus SCORING END  ########### */

				//booster
				$booster_arr = array();
				if(isset($total_final_breakdown[$player_stats['player_id']])){
					$booster_arr = $this->get_booster_break_down($total_final_breakdown[$player_stats['player_id']]);
				}
				$each_player_score['booster_break_down'] = $all_player_scores[$player_stats['player_id']]['booster_break_down'] = json_encode($booster_arr);

				
							  
				$each_player_score['normal_score']			= floatval($normal_score);
				$each_player_score['bonus_score']			= floatval($bonus_score);
				$each_player_score['economy_rate_score']	= floatval($economy_rate_score);
				$each_player_score['strike_rate_score']		= floatval($strike_rate_score);
				$all_player_testing_var[$player_stats['player_id']]['normal'][$player_stats['innings']]		= $normal_testing_var;
				$all_player_testing_var[$player_stats['player_id']]['bonus'][$player_stats['innings']]			= $bonus_testing_var;
				$all_player_testing_var[$player_stats['player_id']]['economy_rate'][$player_stats['innings']]	= $economy_rate_testing_var;
				$all_player_testing_var[$player_stats['player_id']]['strike_rate'][$player_stats['innings']]	= $strike_rate_testing_var;
		                                
				$player_final_breakdown[$player_stats['player_id']][$player_stats['innings']]	= $final_break_down;
				//echo '<pre>';print_r($player_final_breakdown); die;
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('normal_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['normal_score'] = $all_player_scores[$player_stats['player_id']]['normal_score'] = $all_player_scores[$player_stats['player_id']]['normal_score'] + $each_player_score['normal_score'];
				}
				
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('bonus_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['bonus_score'] = $all_player_scores[$player_stats['player_id']]['bonus_score'] = $all_player_scores[$player_stats['player_id']]['bonus_score'] + $each_player_score['bonus_score'];
				}
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('economy_rate_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['economy_rate_score'] = $all_player_scores[$player_stats['player_id']]['economy_rate_score'] = $all_player_scores[$player_stats['player_id']]['economy_rate_score'] + $each_player_score['economy_rate_score'];
				}
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('strike_rate_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['strike_rate_score'] = $all_player_scores[$player_stats['player_id']]['strike_rate_score'] = $all_player_scores[$player_stats['player_id']]['strike_rate_score'] + $each_player_score['strike_rate_score'];
				}

				
				$all_player_scores[$player_stats['player_id']] = $each_player_score;

				$all_player_scores[$player_stats['player_id']]['break_down'] = json_encode($all_player_testing_var[$player_stats['player_id']]);
		                                
		        $all_player_scores[$player_stats['player_id']]['final_break_down'] = json_encode(array('total' => $total_final_breakdown[$player_stats['player_id']], 'innings'=> $player_final_breakdown[$player_stats['player_id']]));
				$all_player_scores[$player_stats['player_id']]['match_format'] = $season_game['format'];
				$all_player_scores[$player_stats['player_id']]['second_inning_score'] = 0;
			}
		}// End  of  Empty of game_ stats

		//echo "<pre>";print_r($all_player_scores);die;
		if(!empty($all_player_scores))
		{
			//Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
			//Update score first zero then update
			$this->db->where('season_id', $season_id);
			$this->db->where('league_id', $league_id);
			$this->db->update(GAME_PLAYER_SCORING,array("normal_score"=>"0","bonus_score"=>"0","economy_rate_score"=>"0","strike_rate_score"=>"0","score"=>"0","break_down"=>NULL,"final_break_down"=>NULL,"booster_break_down"=>NULL));
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

	// CALCULATE FANTASY POINT FOR ODI MATCH
	private function calculate_fantasy_points_for_odi($sports_id,$season_game)
	{
		// Get All Scoring Rules
		$season_id				= $season_game['season_id'];
		$season_game_uid		= $season_game['season_game_uid'];
		$league_id				= $season_game['league_id'];
		$all_player_scores		= array();
		$all_player_testing_var	= array();
        $total_final_breakdown = array();
        $player_final_breakdown = array();

		// Get Match Scoring Statistics to Calculate Fantasy Score
		$game_stats = $this->get_game_statistics_by_season_id($season_id,$sports_id,$league_id);
		//echo "<pre>";print_r($game_stats);die;
		if (!empty($game_stats))
		{
			foreach ($game_stats as $player_stats)
			{
				$formulas = $this->get_scoring_rules($sports_id, $season_game['format']);
				//echo "<pre>"; print_r($formulas);die;
				if(empty($formulas))
				{
					continue;
				}
				$normal_score		= 0;
				$bonus_score		= 0;
				$economy_rate_score	= 0;
				$strike_rate_score	= 0;
				$score				= 0;
				//2nd inning score
				$second_inning_score= 0;
		                                
		        $final_break_down = array();
				$final_break_down["starting_points"] 	= 0;
				$final_break_down["batting"] 			= array();
				$final_break_down["bowling"] 			= array();
				$final_break_down["fielding"] 			= array();

				$each_player_score = array(
							"season_id"				=> $player_stats['season_id'],
							"player_id"				=> $player_stats['player_id'],
							"week"					=> $player_stats['week'],
							"scheduled_date"		=> $player_stats['scheduled_date'],
							"league_id"				=> $league_id,
							"normal_score"			=> 0,
							"bonus_score"			=> 0,
							"economy_rate_score"	=> 0,
							"strike_rate_score"		=> 0,
							"score"					=> 0
				);
				//echo "<pre>";print_r($each_player_score);continue;
				$normal_testing_var = $bonus_testing_var = $economy_rate_testing_var = $strike_rate_testing_var = array();
				/* ####### NORMAL SCORING RULE ########### */
				// STARTING_11 
				if ($player_stats['innings'] == 1 && $player_stats['playing_11'] == '1')
				{
					$normal_score = $normal_score + (1 * $formulas['normal']['STARTING_11'] );
					$normal_testing_var['STARTING_11'] = ( 1 * $formulas['normal']['STARTING_11'] );
		                                        
		            $final_break_down["starting_points"] = (1 * $formulas['normal']['STARTING_11']);
					$total_final_breakdown[$player_stats['player_id']]["starting_points"] = (!empty($total_final_breakdown[$player_stats['player_id']]["starting_points"])) ? (1 * $formulas['normal']['STARTING_11']) + $total_final_breakdown[$player_stats['player_id']]["starting_points"] : (1 * $formulas['normal']['STARTING_11']);
				}

				// Runs
				if ($player_stats['batting_runs'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['batting_runs'] * $formulas['normal']['RUN'] );
					$normal_testing_var['RUN'] = ( $player_stats['batting_runs'] * $formulas['normal']['RUN'] );
		                                        
		            $final_break_down["batting"]["RUN"] = ($player_stats['batting_runs'] * $formulas['normal']['RUN']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]["RUN"] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]["RUN"])) ? ($player_stats['batting_runs'] * $formulas['normal']['RUN']) + $total_final_breakdown[$player_stats['player_id']]["batting"]["RUN"] : ($player_stats['batting_runs'] * $formulas['normal']['RUN']);
					//echo "<pre>";print_r($total_final_breakdown);//die;
				}

				//Wickets
				if ($player_stats['bowling_wickets'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET'] );
					$normal_testing_var['WICKET'] = ( $player_stats['bowling_wickets'] * $formulas['normal']['WICKET'] );
		                                        
		            $final_break_down["bowling"]["WICKET"] = ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]["WICKET"] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]["WICKET"])) ? ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET'] ) + $total_final_breakdown[$player_stats['player_id']]["bowling"]["WICKET"] : ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET']);
				}

				//Catch
				if ($player_stats['catch'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['catch'] * $formulas['normal']['CATCH'] );
					$normal_testing_var['CATCH'] = ( $player_stats['catch'] * $formulas['normal']['CATCH'] );
		                                        
		            $final_break_down["fielding"]['CATCH'] = ($player_stats['catch'] * $formulas['normal']['CATCH']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['CATCH'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['CATCH'])) ? ($player_stats['catch'] * $formulas['normal']['CATCH']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['CATCH'] : ($player_stats['catch'] * $formulas['normal']['CATCH']);
				}

				//Stumping
				if ($player_stats['stumping'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['stumping'] * $formulas['normal']['STUMPING'] );
					$normal_testing_var['STUMPING'] = ( $player_stats['stumping'] * $formulas['normal']['STUMPING'] );
		                                        
		            $final_break_down["fielding"]['STUMPING'] = ($player_stats['stumping'] * $formulas['normal']['STUMPING']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['STUMPING'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['STUMPING'])) ? ($player_stats['stumping'] * $formulas['normal']['STUMPING']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['STUMPING'] : ($player_stats['stumping'] * $formulas['normal']['STUMPING']);
				}

				//Runout Direct Hit
				if ($player_stats['run_out'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT'] );
					$normal_testing_var['RUN_OUT_DIRECT_HIT'] = ( $player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT'] );
		                                        
		            $final_break_down["fielding"]['RUN_OUT_DIRECT_HIT'] = ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_DIRECT_HIT'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_DIRECT_HIT'])) ? ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_DIRECT_HIT'] : ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT']);
				}

				//Runout run_out_throw
				if ($player_stats['run_out_throw'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER'] );
					$normal_testing_var['RUN_OUT_THROWER'] = ( $player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER'] );
		                                        
		            $final_break_down["fielding"]['RUN_OUT_THROWER'] = ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_THROWER'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_THROWER'])) ? ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_THROWER'] : ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER']);
				}

				//Runout run_out_catch
				if ($player_stats['run_out_catch'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER'] );
					$normal_testing_var['RUN_OUT_CATCHER'] = ( $player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER'] );
		                                        
		            $final_break_down["fielding"]['RUN_OUT_CATCHER'] = ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_CATCHER'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_CATCHER'])) ? ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_THROWER']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_CATCHER'] : ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER']);
				}

				//Dismissal for duck (batsmen, wicket-keeper and all-rounders)
				if ($player_stats['batting_runs'] == 0 && $player_stats['dismissed'] == 1 && $player_stats['position'] != 'BOW')
				{
					$normal_score = $normal_score + (1 * $formulas['normal']['DUCK'] );
					$normal_testing_var['DUCK'] = ( 1 * $formulas['normal']['DUCK'] );
		                                        
		            $final_break_down["batting"]['DUCK'] = (1 * $formulas['normal']['DUCK']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]['DUCK'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['DUCK'])) ? (1 * $formulas['normal']['DUCK']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['DUCK'] : (1 * $formulas['normal']['DUCK']);
				}

				//echo $normal_score;die;
				/* ####### NORMAL SCORING RULE END ########### */


				/* ####### Bonus SCORING ########### */
				// Catch bonus
				if ($player_stats['catch'] >= 3)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['CATCH_3'] );
					$bonus_testing_var['CATCH_3'] = ( $formulas['bonus']['CATCH_3'] );
	                                            
	                $final_break_down["bowling"]['CATCH_3'] = ($formulas['bonus']['CATCH_3']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['CATCH_3'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['CATCH_3'])) ? ($formulas['bonus']['CATCH_3']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['CATCH_3'] : ($formulas['bonus']['CATCH_3']);
				}

				// Fours Bonus
				if ($player_stats['batting_fours'] > 0)
				{
					$bonus_score = $bonus_score + ($player_stats['batting_fours'] * $formulas['bonus']['FOUR'] );
					$bonus_testing_var['FOUR'] = ( $player_stats['batting_fours'] * $formulas['bonus']['FOUR'] );
		                                        
		            $final_break_down["batting"]['FOUR'] = ($player_stats['batting_fours'] * $formulas['bonus']['FOUR']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]['FOUR'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['FOUR'])) ? ($player_stats['batting_fours'] * $formulas['bonus']['FOUR']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['FOUR'] : ($player_stats['batting_fours'] * $formulas['bonus']['FOUR']);
				}

				// Sixes Bonus
				if ($player_stats['batting_sixes'] > 0)
				{
					$bonus_score = $bonus_score + ($player_stats['batting_sixes'] * $formulas['bonus']['SIX'] );
					$bonus_testing_var['SIX'] = ( $player_stats['batting_sixes'] * $formulas['bonus']['SIX'] );
		                                        
		            $final_break_down["batting"]['SIX'] = ($player_stats['batting_sixes'] * $formulas['bonus']['SIX']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]['SIX'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['SIX'])) ? ($player_stats['batting_sixes'] * $formulas['bonus']['SIX']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['SIX'] : ($player_stats['batting_sixes'] * $formulas['bonus']['SIX']);
				}
				
				//For HALF_CENTURY & CENTURY
				if ($player_stats['batting_runs'] >= 50 && $player_stats['batting_runs'] < 100)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['HALF_CENTURY'] );
					$bonus_testing_var['HALF_CENTURY'] = ( $formulas['bonus']['HALF_CENTURY'] );
	                                            
	                $final_break_down["batting"]['HALF_CENTURY'] = ($formulas['bonus']['HALF_CENTURY']);

					$total_final_breakdown[$player_stats['player_id']]["batting"]['HALF_CENTURY'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['HALF_CENTURY'])) ? ($formulas['bonus']['HALF_CENTURY']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['HALF_CENTURY'] : ($formulas['bonus']['HALF_CENTURY']);
				}
				else if ($player_stats['batting_runs'] >= 100 )
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['CENTURY'] );
					$bonus_testing_var['CENTURY'] = ( $formulas['bonus']['CENTURY'] );
	                                            
	                $final_break_down["batting"]['CENTURY'] = ($formulas['bonus']['CENTURY']);

					$total_final_breakdown[$player_stats['player_id']]["batting"]['CENTURY'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['CENTURY'])) ? ($formulas['bonus']['CENTURY']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['CENTURY'] : ($formulas['bonus']['CENTURY']);
				}
					
				
				if ($player_stats['bowling_maiden_overs'] > 0)
				{
					$bonus_score = $bonus_score + ($player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER'] );
					$bonus_testing_var['MAIDEN_OVER'] = ( $player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER'] );
                                                    
                    $final_break_down["bowling"]['MAIDEN_OVER'] = ($player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['MAIDEN_OVER'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['MAIDEN_OVER'])) ? ($player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['MAIDEN_OVER'] : ($player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER']);
				}

				//For FOUR_WICKET & FIVE_WICKET HAUL
				if ($player_stats['bowling_wickets'] == 4)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['FOUR_WICKET'] );
					$bonus_testing_var['FOUR_WICKET'] = ( $formulas['bonus']['FOUR_WICKET'] );
	                                            
	                $final_break_down["bowling"]['FOUR_WICKET'] = ($formulas['bonus']['FOUR_WICKET']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['FOUR_WICKET'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['FOUR_WICKET'])) ? ($formulas['bonus']['FOUR_WICKET']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['FOUR_WICKET'] : ($formulas['bonus']['FOUR_WICKET']);
				}
				else if ($player_stats['bowling_wickets'] >= 5)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['FIVE_WICKET'] );
					$bonus_testing_var['FIVE_WICKET'] = ( $formulas['bonus']['FIVE_WICKET'] );
	                                            
	                $final_break_down['bowling']['FIVE_WICKET'] = ($formulas['bonus']['FIVE_WICKET']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['FIVE_WICKET'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['FIVE_WICKET'])) ? ($formulas['bonus']['FIVE_WICKET']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['FIVE_WICKET'] : ($formulas['bonus']['FIVE_WICKET']);
				}

				//Bowled and LBW Bonus
				if (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) > 0)
				{
					$bonus_score = $bonus_score + (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED'] );
					$bonus_testing_var['LBW_BOWLED'] = ( ($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED'] );
		                                        
		            $final_break_down["bowling"]['LBW_BOWLED'] = (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED']);
		            
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['LBW_BOWLED'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['LBW_BOWLED'])) ? (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['LBW_BOWLED'] : (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED']);
				}
				
				/* ####### Bonus SCORING END  ########### */

				/* ####### ECONOMY RATE START  ########### */
				if ($player_stats['bowling_overs'] >= $formulas['economy_rate']['MINIMUM_BOWLING_BOWLED_OVER'])
				{
					if ($player_stats['bowling_economy_rate'] < 2.5)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_BELOW_25'] );
						$economy_rate_testing_var['ECONOMY_BELOW_25'] = ( $formulas['economy_rate']['ECONOMY_BELOW_25'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ($formulas['economy_rate']['ECONOMY_BELOW_25']);
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ($formulas['economy_rate']['ECONOMY_BELOW_25']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ($formulas['economy_rate']['ECONOMY_BELOW_25']);
					}                               
					elseif ($player_stats['bowling_economy_rate'] >= 2.5 && $player_stats['bowling_economy_rate'] <= 3.49)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_25_349'] );
						$economy_rate_testing_var['ECONOMY_25_349'] = ( $formulas['economy_rate']['ECONOMY_25_349'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ($formulas['economy_rate']['ECONOMY_25_349']);
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ($formulas['economy_rate']['ECONOMY_25_349']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ($formulas['economy_rate']['ECONOMY_25_349']);
					}
					elseif ($player_stats['bowling_economy_rate'] >= 3.5 && $player_stats['bowling_economy_rate'] <= 4.5)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_35_45'] );
						$economy_rate_testing_var['ECONOMY_35_45'] = ( $formulas['economy_rate']['ECONOMY_35_45'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ( $formulas['economy_rate']['ECONOMY_35_45'] );
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ( $formulas['economy_rate']['ECONOMY_35_45'] ) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ( $formulas['economy_rate']['ECONOMY_35_45'] );
					}
					elseif ($player_stats['bowling_economy_rate'] >= 7 && $player_stats['bowling_economy_rate'] <= 8)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_7_8'] );
						$economy_rate_testing_var['ECONOMY_7_8'] = ( $formulas['economy_rate']['ECONOMY_7_8'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ( $formulas['economy_rate']['ECONOMY_7_8'] );
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ( $formulas['economy_rate']['ECONOMY_7_8'] ) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ( $formulas['economy_rate']['ECONOMY_7_8'] );
					}
					elseif ($player_stats['bowling_economy_rate'] >= 8.01 && $player_stats['bowling_economy_rate'] <= 9)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_801_9'] );
						$economy_rate_testing_var['ECONOMY_801_9'] = ( $formulas['economy_rate']['ECONOMY_801_9'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ($formulas['economy_rate']['ECONOMY_801_9']);
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ($formulas['economy_rate']['ECONOMY_801_9']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ($formulas['economy_rate']['ECONOMY_801_9']);
					}
					elseif ($player_stats['bowling_economy_rate'] > 9)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_ABOVE_9'] );
						$economy_rate_testing_var['ECONOMY_ABOVE_9'] = ( $formulas['economy_rate']['ECONOMY_ABOVE_9'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ( $formulas['economy_rate']['ECONOMY_ABOVE_9'] );
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ( $formulas['economy_rate']['ECONOMY_ABOVE_9'] ) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ( $formulas['economy_rate']['ECONOMY_ABOVE_9'] );
					}
				} 

				/* ####### ECONOMY RATE SCORING END  ########### */

				/* ####### STRIKE RATE SCORING START  ########### */
				if($player_stats['position'] != 'BOW')
				{  
					if ($player_stats['batting_balls_faced'] >= $formulas['strike_rate']['MINIMUM_BALL_PLAYED'])
					{
						if ($player_stats['batting_strike_rate'] > 140)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_140'] );
							$strike_rate_testing_var['STRIKE_RATE_ABOVE_140'] = ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_140'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_140'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_140'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_140'] );
						}
						elseif ($player_stats['batting_strike_rate'] >= 120.01 && $player_stats['batting_strike_rate'] <= 140)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_12001_140'] );
							$strike_rate_testing_var['STRIKE_RATE_12001_140'] = ( $formulas['strike_rate']['STRIKE_RATE_12001_140'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_12001_140'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_12001_140'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_12001_140'] );
						}
						elseif ($player_stats['batting_strike_rate'] >= 100 && $player_stats['batting_strike_rate'] <= 120)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_100_120'] );
							$strike_rate_testing_var['STRIKE_RATE_100_120'] = ( $formulas['strike_rate']['STRIKE_RATE_100_120'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_100_120'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_100_120'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_100_120'] );
						}
						elseif ($player_stats['batting_strike_rate'] >= 40 && $player_stats['batting_strike_rate'] <= 50)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_40_50'] );
							$strike_rate_testing_var['STRIKE_RATE_40_50'] = ( $formulas['strike_rate']['STRIKE_RATE_40_50'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_40_50'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_40_50'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_40_50'] );
						}
						elseif ($player_stats['batting_strike_rate'] >= 30 && $player_stats['batting_strike_rate'] <= 39.99)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_30_3999'] );
							$strike_rate_testing_var['STRIKE_RATE_30_3999'] = ( $formulas['strike_rate']['STRIKE_RATE_30_3999'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_30_3999'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_30_3999'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_30_3999'] );
						}
						elseif ($player_stats['batting_strike_rate'] < 30)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_BELOW_30'] );
							$strike_rate_testing_var['STRIKE_RATE_BELOW_30'] = ( $formulas['strike_rate']['STRIKE_RATE_BELOW_30'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_BELOW_30'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_BELOW_30'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_BELOW_30'] );
						}
					}
				} 

				/* ####### STRIKE RATE SCORING END  ########### */

				//booster
				$booster_arr = array();
				if(isset($total_final_breakdown[$player_stats['player_id']])){
					$booster_arr = $this->get_booster_break_down($total_final_breakdown[$player_stats['player_id']]);
				}
				$each_player_score['booster_break_down'] = $all_player_scores[$player_stats['player_id']]['booster_break_down'] = json_encode($booster_arr);
				
				//2nd inning score start
				$second_inning_arr = array();
				if(!empty($season_game["2nd_inning_team_id"]))
				{	
					$second_inning_arr["starting_11"] = $formulas['normal']['STARTING_11'];
					$points = 0;
					if($player_stats["team_id"] == $season_game["2nd_inning_team_id"])
					{
						$second_inning_arr["batting"] = $final_break_down["batting"];
						if(!empty($second_inning_arr["batting"])){
							$points = array_sum(array_values($second_inning_arr["batting"]));
						}
					}else if($player_stats["team_id"] != $season_game["2nd_inning_team_id"])
					{
						$second_inning_arr["bowling"] = $final_break_down["bowling"];
						$second_inning_arr["fielding"] = $final_break_down["fielding"];
						if(!empty($second_inning_arr["bowling"])){
							$points = array_sum(array_values($second_inning_arr["bowling"]));
						}
						if(!empty($second_inning_arr["fielding"])){
							$points = $points + array_sum(array_values($second_inning_arr["fielding"]));
						}
					}

					$second_inning_score = isset($all_player_scores[$player_stats['player_id']]['second_inning_score']) ? $all_player_scores[$player_stats['player_id']]['second_inning_score'] : 0;
					$second_inning_score = $second_inning_score + $points + $second_inning_arr["starting_11"];
				}

				$each_player_score['second_inning_score'] =	$all_player_scores[$player_stats['player_id']]['second_inning_score'] = $second_inning_score;
				$final_break_down["2nd_inning"] = $second_inning_arr;
				//2nd inning score end

							  
				$each_player_score['normal_score']			= floatval($normal_score);
				$each_player_score['bonus_score']			= floatval($bonus_score);
				$each_player_score['economy_rate_score']	= floatval($economy_rate_score);
				$each_player_score['strike_rate_score']		= floatval($strike_rate_score);
				$all_player_testing_var[$player_stats['player_id']]['normal'][$player_stats['innings']]		= $normal_testing_var;
				$all_player_testing_var[$player_stats['player_id']]['bonus'][$player_stats['innings']]			= $bonus_testing_var;
				$all_player_testing_var[$player_stats['player_id']]['economy_rate'][$player_stats['innings']]	= $economy_rate_testing_var;
				$all_player_testing_var[$player_stats['player_id']]['strike_rate'][$player_stats['innings']]	= $strike_rate_testing_var;
		                                
				$player_final_breakdown[$player_stats['player_id']][$player_stats['innings']]	= $final_break_down;
				//echo '<pre>';print_r($player_final_breakdown); die;
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('normal_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['normal_score'] = $all_player_scores[$player_stats['player_id']]['normal_score'] = $all_player_scores[$player_stats['player_id']]['normal_score'] + $each_player_score['normal_score'];
				}
				
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('bonus_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['bonus_score'] = $all_player_scores[$player_stats['player_id']]['bonus_score'] = $all_player_scores[$player_stats['player_id']]['bonus_score'] + $each_player_score['bonus_score'];
				}
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('economy_rate_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['economy_rate_score'] = $all_player_scores[$player_stats['player_id']]['economy_rate_score'] = $all_player_scores[$player_stats['player_id']]['economy_rate_score'] + $each_player_score['economy_rate_score'];
				}
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('strike_rate_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['strike_rate_score'] = $all_player_scores[$player_stats['player_id']]['strike_rate_score'] = $all_player_scores[$player_stats['player_id']]['strike_rate_score'] + $each_player_score['strike_rate_score'];
				}

				
				$all_player_scores[$player_stats['player_id']] = $each_player_score;

				$all_player_scores[$player_stats['player_id']]['break_down'] = json_encode($all_player_testing_var[$player_stats['player_id']]);
		                                
		        $all_player_scores[$player_stats['player_id']]['final_break_down'] = json_encode(array('total' => $total_final_breakdown[$player_stats['player_id']], 'innings'=> $player_final_breakdown[$player_stats['player_id']]));
				$all_player_scores[$player_stats['player_id']]['match_format'] = $season_game['format'];	
			}
		}// End  of  Empty of game_ stats

		//echo "<pre>";print_r($all_player_scores);die;
		if(!empty($all_player_scores))
		{
			//Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
			//Update score first zero then update
			$this->db->where('season_id', $season_id);
			$this->db->where('league_id', $league_id);
			$this->db->update(GAME_PLAYER_SCORING,array("normal_score"=>"0","bonus_score"=>"0","economy_rate_score"=>"0","strike_rate_score"=>"0","score"=>"0","2nd_inning_score"=>"0","break_down"=>NULL,"final_break_down"=>NULL,"booster_break_down"=>NULL));
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


	// CALCULATE FANTASY POINT FOR T20 MATCH
	private function calculate_fantasy_points_for_ttwenty($sports_id,$season_game)
	{
		// Get All Scoring Rules
		$season_id				= $season_game['season_id'];
		$season_game_uid		= $season_game['season_game_uid'];
		$league_id				= $season_game['league_id'];
		$all_player_scores		= array();
		$all_player_testing_var	= array();
        $total_final_breakdown = array();
        $player_final_breakdown = array();

        //echo "<pre>";print_r($season_game);die;
		// Get Match Scoring Statistics to Calculate Fantasy Score
		$game_stats = $this->get_game_statistics_by_season_id($season_id,$sports_id,$league_id);
		//echo "<pre>";print_r($game_stats);die;
		if (!empty($game_stats))
		{
			foreach ($game_stats as $player_stats)
			{
				$formulas = $this->get_scoring_rules($sports_id, $season_game['format']);
				//echo "<pre>"; print_r($formulas);die;
				if(empty($formulas))
				{
					continue;
				}
				$normal_score		= 0;
				$bonus_score		= 0;
				$economy_rate_score	= 0;
				$strike_rate_score	= 0;
				$score				= 0;
				//2nd inning score
				$second_inning_score= 0;
		                                
		        $final_break_down = array();
				$final_break_down["starting_points"] 	= 0;
				$final_break_down["batting"] 			= array();
				$final_break_down["bowling"] 			= array();
				$final_break_down["fielding"] 			= array();

				$each_player_score = array(
							"season_id"				=> $player_stats['season_id'],
							"player_id"				=> $player_stats['player_id'],
							"week"					=> $player_stats['week'],
							"scheduled_date"		=> $player_stats['scheduled_date'],
							"league_id"				=> $league_id,
							"normal_score"			=> 0,
							"bonus_score"			=> 0,
							"economy_rate_score"	=> 0,
							"strike_rate_score"		=> 0,
							"score"					=> 0
				);
				//echo "<pre>";print_r($each_player_score);continue;
				$normal_testing_var = $bonus_testing_var = $economy_rate_testing_var = $strike_rate_testing_var = array();
				/* ####### NORMAL SCORING RULE ########### */
				// STARTING_11 
				if ($player_stats['innings'] == 1 && $player_stats['playing_11'] == '1')
				{
					$normal_score = $normal_score + (1 * $formulas['normal']['STARTING_11'] );
					
					$normal_testing_var['STARTING_11'] = ( 1 * $formulas['normal']['STARTING_11'] );
		                                        
		            $final_break_down["starting_points"] = (1 * $formulas['normal']['STARTING_11']);
					$total_final_breakdown[$player_stats['player_id']]["starting_points"] = (!empty($total_final_breakdown[$player_stats['player_id']]["starting_points"])) ? (1 * $formulas['normal']['STARTING_11']) + $total_final_breakdown[$player_stats['player_id']]["starting_points"] : (1 * $formulas['normal']['STARTING_11']);
				}

				// Runs
				if ($player_stats['batting_runs'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['batting_runs'] * $formulas['normal']['RUN'] );
					$normal_testing_var['RUN'] = ( $player_stats['batting_runs'] * $formulas['normal']['RUN'] );
		                                        
		            $final_break_down["batting"]["RUN"] = ($player_stats['batting_runs'] * $formulas['normal']['RUN']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]["RUN"] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]["RUN"])) ? ($player_stats['batting_runs'] * $formulas['normal']['RUN']) + $total_final_breakdown[$player_stats['player_id']]["batting"]["RUN"] : ($player_stats['batting_runs'] * $formulas['normal']['RUN']);
					//echo "<pre>";print_r($total_final_breakdown);//die;
				}

				//Wickets
				if ($player_stats['bowling_wickets'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET'] );
					$normal_testing_var['WICKET'] = ( $player_stats['bowling_wickets'] * $formulas['normal']['WICKET'] );
		                                        
		            $final_break_down["bowling"]["WICKET"] = ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]["WICKET"] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]["WICKET"])) ? ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET'] ) + $total_final_breakdown[$player_stats['player_id']]["bowling"]["WICKET"] : ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET']);
				}

				//Catch
				if ($player_stats['catch'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['catch'] * $formulas['normal']['CATCH'] );
					$normal_testing_var['CATCH'] = ( $player_stats['catch'] * $formulas['normal']['CATCH'] );
		                                        
		            $final_break_down["fielding"]['CATCH'] = ($player_stats['catch'] * $formulas['normal']['CATCH']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['CATCH'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['CATCH'])) ? ($player_stats['catch'] * $formulas['normal']['CATCH']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['CATCH'] : ($player_stats['catch'] * $formulas['normal']['CATCH']);
				}

				//Stumping
				if ($player_stats['stumping'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['stumping'] * $formulas['normal']['STUMPING'] );
					$normal_testing_var['STUMPING'] = ( $player_stats['stumping'] * $formulas['normal']['STUMPING'] );
		                                        
		            $final_break_down["fielding"]['STUMPING'] = ($player_stats['stumping'] * $formulas['normal']['STUMPING']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['STUMPING'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['STUMPING'])) ? ($player_stats['stumping'] * $formulas['normal']['STUMPING']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['STUMPING'] : ($player_stats['stumping'] * $formulas['normal']['STUMPING']);
				}

				//Runout Direct Hit
				if ($player_stats['run_out'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT'] );
					$normal_testing_var['RUN_OUT_DIRECT_HIT'] = ( $player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT'] );
		                                        
		            $final_break_down["fielding"]['RUN_OUT_DIRECT_HIT'] = ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_DIRECT_HIT'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_DIRECT_HIT'])) ? ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_DIRECT_HIT'] : ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT']);
				}

				//Runout run_out_throw
				if ($player_stats['run_out_throw'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER'] );
					$normal_testing_var['RUN_OUT_THROWER'] = ( $player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER'] );
		                                        
		            $final_break_down["fielding"]['RUN_OUT_THROWER'] = ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_THROWER'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_THROWER'])) ? ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_THROWER'] : ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER']);
				}

				//Runout run_out_catch
				if ($player_stats['run_out_catch'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER'] );
					$normal_testing_var['RUN_OUT_CATCHER'] = ( $player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER'] );
		                                        
		            $final_break_down["fielding"]['RUN_OUT_CATCHER'] = ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_CATCHER'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_CATCHER'])) ? ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_THROWER']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_CATCHER'] : ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER']);
				}

				//Dismissal for duck (batsmen, wicket-keeper and all-rounders)
				if ($player_stats['batting_runs'] == 0 && $player_stats['dismissed'] == 1 && $player_stats['position'] != 'BOW')
				{
					$normal_score = $normal_score + (1 * $formulas['normal']['DUCK'] );
					$normal_testing_var['DUCK'] = ( 1 * $formulas['normal']['DUCK'] );
		                                        
		            $final_break_down["batting"]['DUCK'] = (1 * $formulas['normal']['DUCK']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]['DUCK'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['DUCK'])) ? (1 * $formulas['normal']['DUCK']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['DUCK'] : (1 * $formulas['normal']['DUCK']);
				}

				//echo $normal_score;die;
				/* ####### NORMAL SCORING RULE END ########### */


				/* ####### Bonus SCORING ########### */
				// Catch bonus
				if ($player_stats['catch'] >= 3)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['CATCH_3'] );
					$bonus_testing_var['CATCH_3'] = ( $formulas['bonus']['CATCH_3'] );
	                                            
	                $final_break_down["bowling"]['CATCH_3'] = ($formulas['bonus']['CATCH_3']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['CATCH_3'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['CATCH_3'])) ? ($formulas['bonus']['CATCH_3']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['CATCH_3'] : ($formulas['bonus']['CATCH_3']);
				}

				// Fours Bonus
				if ($player_stats['batting_fours'] > 0)
				{
					$bonus_score = $bonus_score + ($player_stats['batting_fours'] * $formulas['bonus']['FOUR'] );
					$bonus_testing_var['FOUR'] = ( $player_stats['batting_fours'] * $formulas['bonus']['FOUR'] );
		                                        
		            $final_break_down["batting"]['FOUR'] = ($player_stats['batting_fours'] * $formulas['bonus']['FOUR']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]['FOUR'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['FOUR'])) ? ($player_stats['batting_fours'] * $formulas['bonus']['FOUR']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['FOUR'] : ($player_stats['batting_fours'] * $formulas['bonus']['FOUR']);
				}

				// Sixes Bonus
				if ($player_stats['batting_sixes'] > 0)
				{
					$bonus_score = $bonus_score + ($player_stats['batting_sixes'] * $formulas['bonus']['SIX'] );
					$bonus_testing_var['SIX'] = ( $player_stats['batting_sixes'] * $formulas['bonus']['SIX'] );
		                                        
		            $final_break_down["batting"]['SIX'] = ($player_stats['batting_sixes'] * $formulas['bonus']['SIX']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]['SIX'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['SIX'])) ? ($player_stats['batting_sixes'] * $formulas['bonus']['SIX']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['SIX'] : ($player_stats['batting_sixes'] * $formulas['bonus']['SIX']);
				}
				
				//For 30_BONUS HALF_CENTURY & CENTURY
				if ($player_stats['batting_runs'] >= 30 && $player_stats['batting_runs'] < 50)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['30_BONUS'] );
					$bonus_testing_var['30_BONUS'] = ( $formulas['bonus']['30_BONUS'] );
	                                            
	                $final_break_down["batting"]['30_BONUS'] = ($formulas['bonus']['30_BONUS']);

					$total_final_breakdown[$player_stats['player_id']]["batting"]['30_BONUS'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['30_BONUS'])) ? ($formulas['bonus']['30_BONUS']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['30_BONUS'] : ($formulas['bonus']['30_BONUS']);
				}
				elseif ($player_stats['batting_runs'] >= 50 && $player_stats['batting_runs'] < 100)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['HALF_CENTURY'] );
					$bonus_testing_var['HALF_CENTURY'] = ( $formulas['bonus']['HALF_CENTURY'] );
	                                            
	                $final_break_down["batting"]['HALF_CENTURY'] = ($formulas['bonus']['HALF_CENTURY']);

					$total_final_breakdown[$player_stats['player_id']]["batting"]['HALF_CENTURY'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['HALF_CENTURY'])) ? ($formulas['bonus']['HALF_CENTURY']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['HALF_CENTURY'] : ($formulas['bonus']['HALF_CENTURY']);
				}
				elseif ($player_stats['batting_runs'] >= 100 )
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['CENTURY'] );
					$bonus_testing_var['CENTURY'] = ( $formulas['bonus']['CENTURY'] );
	                                            
	                $final_break_down["batting"]['CENTURY'] = ($formulas['bonus']['CENTURY']);

					$total_final_breakdown[$player_stats['player_id']]["batting"]['CENTURY'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['CENTURY'])) ? ($formulas['bonus']['CENTURY']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['CENTURY'] : ($formulas['bonus']['CENTURY']);
				}
					
				
				if ($player_stats['bowling_maiden_overs'] > 0)
				{
					$bonus_score = $bonus_score + ($player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER'] );
					$bonus_testing_var['MAIDEN_OVER'] = ( $player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER'] );
                                                    
                    $final_break_down["bowling"]['MAIDEN_OVER'] = ($player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['MAIDEN_OVER'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['MAIDEN_OVER'])) ? ($player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['MAIDEN_OVER'] : ($player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER']);
				}
				
				//For THREE_WICKET & FOUR_WICKET & FIVE_WICKET HAUL
				if ($player_stats['bowling_wickets'] == 3)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['THREE_WICKET'] );
					$bonus_testing_var['THREE_WICKET'] = ( $formulas['bonus']['THREE_WICKET'] );
	                                            
	                $final_break_down["bowling"]['THREE_WICKET'] = ($formulas['bonus']['THREE_WICKET']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['THREE_WICKET'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['THREE_WICKET'])) ? ($formulas['bonus']['THREE_WICKET']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['THREE_WICKET'] : ($formulas['bonus']['THREE_WICKET']);
				}
				elseif ($player_stats['bowling_wickets'] == 4)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['FOUR_WICKET'] );
					$bonus_testing_var['FOUR_WICKET'] = ( $formulas['bonus']['FOUR_WICKET'] );
	                                            
	                $final_break_down["bowling"]['FOUR_WICKET'] = ($formulas['bonus']['FOUR_WICKET']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['FOUR_WICKET'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['FOUR_WICKET'])) ? ($formulas['bonus']['FOUR_WICKET']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['FOUR_WICKET'] : ($formulas['bonus']['FOUR_WICKET']);
				}
				elseif ($player_stats['bowling_wickets'] >= 5)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['FIVE_WICKET'] );
					$bonus_testing_var['FIVE_WICKET'] = ( $formulas['bonus']['FIVE_WICKET'] );
	                                            
	                $final_break_down['bowling']['FIVE_WICKET'] = ($formulas['bonus']['FIVE_WICKET']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['FIVE_WICKET'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['FIVE_WICKET'])) ? ($formulas['bonus']['FIVE_WICKET']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['FIVE_WICKET'] : ($formulas['bonus']['FIVE_WICKET']);
				}

				//Bowled and LBW Bonus
				if (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) > 0)
				{
					$bonus_score = $bonus_score + (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED'] );
					$bonus_testing_var['LBW_BOWLED'] = ( ($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED'] );
		                                        
		            $final_break_down["bowling"]['LBW_BOWLED'] = (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED']);
		            
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['LBW_BOWLED'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['LBW_BOWLED'])) ? (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['LBW_BOWLED'] : (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED']);
				}
				
				/* ####### Bonus SCORING END  ########### */

				/* ####### ECONOMY RATE START  ########### */
				if ($player_stats['bowling_overs'] >= $formulas['economy_rate']['MINIMUM_BOWLING_BOWLED_OVER'])
				{
					if ($player_stats['bowling_economy_rate'] < 5)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_BELOW_5'] );
						$economy_rate_testing_var['ECONOMY_BELOW_5'] = ( $formulas['economy_rate']['ECONOMY_BELOW_5'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ($formulas['economy_rate']['ECONOMY_BELOW_5']);
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ($formulas['economy_rate']['ECONOMY_BELOW_5']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ($formulas['economy_rate']['ECONOMY_BELOW_5']);
					}                               
					elseif ($player_stats['bowling_economy_rate'] >= 5 && $player_stats['bowling_economy_rate'] <= 5.99)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_5_599'] );
						$economy_rate_testing_var['ECONOMY_5_599'] = ( $formulas['economy_rate']['ECONOMY_5_599'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ($formulas['economy_rate']['ECONOMY_5_599']);
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ($formulas['economy_rate']['ECONOMY_5_599']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ($formulas['economy_rate']['ECONOMY_5_599']);
					}
					elseif ($player_stats['bowling_economy_rate'] >= 6 && $player_stats['bowling_economy_rate'] <= 7)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_6_7'] );
						$economy_rate_testing_var['ECONOMY_6_7'] = ( $formulas['economy_rate']['ECONOMY_6_7'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ( $formulas['economy_rate']['ECONOMY_6_7'] );
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ( $formulas['economy_rate']['ECONOMY_6_7'] ) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ( $formulas['economy_rate']['ECONOMY_6_7'] );
					}
					elseif ($player_stats['bowling_economy_rate'] >= 10 && $player_stats['bowling_economy_rate'] <= 11)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_10_11'] );
						$economy_rate_testing_var['ECONOMY_10_11'] = ( $formulas['economy_rate']['ECONOMY_10_11'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ( $formulas['economy_rate']['ECONOMY_10_11'] );
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ( $formulas['economy_rate']['ECONOMY_10_11'] ) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ( $formulas['economy_rate']['ECONOMY_10_11'] );
					}
					elseif ($player_stats['bowling_economy_rate'] >= 11.01 && $player_stats['bowling_economy_rate'] <= 12)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_1101_12'] );
						$economy_rate_testing_var['ECONOMY_1101_12'] = ( $formulas['economy_rate']['ECONOMY_1101_12'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ($formulas['economy_rate']['ECONOMY_1101_12']);
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ($formulas['economy_rate']['ECONOMY_1101_12']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ($formulas['economy_rate']['ECONOMY_1101_12']);
					}
					elseif ($player_stats['bowling_economy_rate'] > 12)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_ABOVE_12'] );
						$economy_rate_testing_var['ECONOMY_ABOVE_12'] = ( $formulas['economy_rate']['ECONOMY_ABOVE_12'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ( $formulas['economy_rate']['ECONOMY_ABOVE_12'] );
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ( $formulas['economy_rate']['ECONOMY_ABOVE_12'] ) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ( $formulas['economy_rate']['ECONOMY_ABOVE_12'] );
					}
				} 

				/* ####### ECONOMY RATE SCORING END  ########### */

				/* ####### STRIKE RATE SCORING START  ########### */
				if($player_stats['position'] != 'BOW')
				{  
					if ($player_stats['batting_balls_faced'] >= $formulas['strike_rate']['MINIMUM_BALL_PLAYED'])
					{
						if ($player_stats['batting_strike_rate'] > 170)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_170'] );
							$strike_rate_testing_var['STRIKE_RATE_ABOVE_170'] = ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_170'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_170'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_170'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_170'] );
						}
						elseif ($player_stats['batting_strike_rate'] >= 150.01 && $player_stats['batting_strike_rate'] <= 170)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_15001_170'] );
							$strike_rate_testing_var['STRIKE_RATE_15001_170'] = ( $formulas['strike_rate']['STRIKE_RATE_15001_170'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_15001_170'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_15001_170'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_15001_170'] );
						}
						elseif ($player_stats['batting_strike_rate'] >= 130 && $player_stats['batting_strike_rate'] <= 150)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_130_150'] );
							$strike_rate_testing_var['STRIKE_RATE_130_150'] = ( $formulas['strike_rate']['STRIKE_RATE_130_150'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_130_150'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_130_150'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_130_150'] );
						}
						elseif ($player_stats['batting_strike_rate'] >= 60 && $player_stats['batting_strike_rate'] <= 70)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_60_70'] );
							$strike_rate_testing_var['STRIKE_RATE_60_70'] = ( $formulas['strike_rate']['STRIKE_RATE_60_70'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_60_70'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_60_70'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_60_70'] );
						}
						elseif ($player_stats['batting_strike_rate'] >= 50 && $player_stats['batting_strike_rate'] <= 59.99)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_50_5999'] );
							$strike_rate_testing_var['STRIKE_RATE_50_5999'] = ( $formulas['strike_rate']['STRIKE_RATE_50_5999'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_50_5999'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_50_5999'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_50_5999'] );
						}
						elseif ($player_stats['batting_strike_rate'] < 50)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_BELOW_50'] );
							$strike_rate_testing_var['STRIKE_RATE_BELOW_50'] = ( $formulas['strike_rate']['STRIKE_RATE_BELOW_50'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_BELOW_50'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_BELOW_50'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_BELOW_50'] );
						}
					}
				} 

				/* ####### STRIKE RATE SCORING END  ########### */

				//booster
				$booster_arr = array();
				if(isset($total_final_breakdown[$player_stats['player_id']])){
					$booster_arr = $this->get_booster_break_down($total_final_breakdown[$player_stats['player_id']]);
				}
				$each_player_score['booster_break_down'] = $all_player_scores[$player_stats['player_id']]['booster_break_down'] = json_encode($booster_arr);
				
				//2nd inning score start
				$second_inning_arr = array();
				if(!empty($season_game["2nd_inning_team_id"]))
				{	
					$second_inning_arr["starting_11"] = $formulas['normal']['STARTING_11'];
					$points = 0;
					if($player_stats["team_id"] == $season_game["2nd_inning_team_id"])
					{
						$second_inning_arr["batting"] = $final_break_down["batting"];
						if(!empty($second_inning_arr["batting"])){
							$points = array_sum(array_values($second_inning_arr["batting"]));
						}
					}else if($player_stats["team_id"] != $season_game["2nd_inning_team_id"])
					{
						$second_inning_arr["bowling"] = $final_break_down["bowling"];
						$second_inning_arr["fielding"] = $final_break_down["fielding"];
						if(!empty($second_inning_arr["bowling"])){
							$points = array_sum(array_values($second_inning_arr["bowling"]));
						}
						if(!empty($second_inning_arr["fielding"])){
							$points = $points + array_sum(array_values($second_inning_arr["fielding"]));
						}
					}

					$second_inning_score = isset($all_player_scores[$player_stats['player_id']]['second_inning_score']) ? $all_player_scores[$player_stats['player_id']]['second_inning_score'] : 0;
					$second_inning_score = $second_inning_score + $points + $second_inning_arr["starting_11"];
				}

				$each_player_score['second_inning_score'] =	$all_player_scores[$player_stats['player_id']]['second_inning_score'] = $second_inning_score;
				$final_break_down["2nd_inning"] = $second_inning_arr;
				//2nd inning score end
							  
				$each_player_score['normal_score']			= floatval($normal_score);
				
				$each_player_score['bonus_score']			= floatval($bonus_score);
				$each_player_score['economy_rate_score']	= floatval($economy_rate_score);
				$each_player_score['strike_rate_score']		= floatval($strike_rate_score);
				$all_player_testing_var[$player_stats['player_id']]['normal'][$player_stats['innings']]		= $normal_testing_var;
				$all_player_testing_var[$player_stats['player_id']]['bonus'][$player_stats['innings']]			= $bonus_testing_var;
				$all_player_testing_var[$player_stats['player_id']]['economy_rate'][$player_stats['innings']]	= $economy_rate_testing_var;
				$all_player_testing_var[$player_stats['player_id']]['strike_rate'][$player_stats['innings']]	= $strike_rate_testing_var;
		                                
				$player_final_breakdown[$player_stats['player_id']][$player_stats['innings']]	= $final_break_down;
				//echo '<pre>';print_r($player_final_breakdown); die;
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('normal_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['normal_score'] = $all_player_scores[$player_stats['player_id']]['normal_score'] = $all_player_scores[$player_stats['player_id']]['normal_score'] + $each_player_score['normal_score'];
				}
				
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('bonus_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['bonus_score'] = $all_player_scores[$player_stats['player_id']]['bonus_score'] = $all_player_scores[$player_stats['player_id']]['bonus_score'] + $each_player_score['bonus_score'];
				}
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('economy_rate_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['economy_rate_score'] = $all_player_scores[$player_stats['player_id']]['economy_rate_score'] = $all_player_scores[$player_stats['player_id']]['economy_rate_score'] + $each_player_score['economy_rate_score'];
				}
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('strike_rate_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['strike_rate_score'] = $all_player_scores[$player_stats['player_id']]['strike_rate_score'] = $all_player_scores[$player_stats['player_id']]['strike_rate_score'] + $each_player_score['strike_rate_score'];
				}

				
				$all_player_scores[$player_stats['player_id']] = $each_player_score;

				$all_player_scores[$player_stats['player_id']]['break_down'] = json_encode($all_player_testing_var[$player_stats['player_id']]);
		                                
		        $all_player_scores[$player_stats['player_id']]['final_break_down'] = json_encode(array('total' => $total_final_breakdown[$player_stats['player_id']], 'innings'=> $player_final_breakdown[$player_stats['player_id']]));
				$all_player_scores[$player_stats['player_id']]['match_format'] = $season_game['format'];
					
			}
		}// End  of  Empty of game_ stats

		//echo "<pre>";print_r($all_player_scores);die;
		if(!empty($all_player_scores))
		{
			//Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
			//Update score first zero then update
			$this->db->where('season_id', $season_id);
			$this->db->where('league_id', $league_id);
			$this->db->update(GAME_PLAYER_SCORING,array("normal_score"=>"0","bonus_score"=>"0","economy_rate_score"=>"0","strike_rate_score"=>"0","score"=>"0","2nd_inning_score"=>"0","break_down"=>NULL,"final_break_down"=>NULL,"booster_break_down"=>NULL));
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

	// CALCULATE FANTASY POINT FOR T10 MATCH
	private function calculate_fantasy_points_for_tten($sports_id,$season_game)
	{
		// Get All Scoring Rules
		$season_id				= $season_game['season_id'];
		$season_game_uid		= $season_game['season_game_uid'];
		$league_id				= $season_game['league_id'];
		$all_player_scores		= array();
		$all_player_testing_var	= array();
        $total_final_breakdown = array();
        $player_final_breakdown = array();

		// Get Match Scoring Statistics to Calculate Fantasy Score
		$game_stats = $this->get_game_statistics_by_season_id($season_id,$sports_id,$league_id);
		//echo "<pre>";print_r($game_stats);die;
		if (!empty($game_stats))
		{
			foreach ($game_stats as $player_stats)
			{
				$formulas = $this->get_scoring_rules($sports_id, $season_game['format']);
				//echo "<pre>"; print_r($formulas);die;
				if(empty($formulas))
				{
					continue;
				}
				$normal_score		= 0;
				$bonus_score		= 0;
				$economy_rate_score	= 0;
				$strike_rate_score	= 0;
				$score				= 0;
				//2nd inning score
				//$second_inning_score= 0;
		                                
		        $final_break_down = array();
				$final_break_down["starting_points"] 	= 0;
				$final_break_down["batting"] 			= array();
				$final_break_down["bowling"] 			= array();
				$final_break_down["fielding"] 			= array();

				$each_player_score = array(
							"season_id"				=> $player_stats['season_id'],
							"player_id"				=> $player_stats['player_id'],
							"week"					=> $player_stats['week'],
							"scheduled_date"		=> $player_stats['scheduled_date'],
							"league_id"				=> $league_id,
							"normal_score"			=> 0,
							"bonus_score"			=> 0,
							"economy_rate_score"	=> 0,
							"strike_rate_score"		=> 0,
							"score"					=> 0
				);
				//echo "<pre>";print_r($each_player_score);continue;
				$normal_testing_var = $bonus_testing_var = $economy_rate_testing_var = $strike_rate_testing_var = array();
				/* ####### NORMAL SCORING RULE ########### */
				// STARTING_11 
				if ($player_stats['innings'] == 1 && $player_stats['playing_11'] == '1')
				{
					$normal_score = $normal_score + (1 * $formulas['normal']['STARTING_11'] );
					$normal_testing_var['STARTING_11'] = ( 1 * $formulas['normal']['STARTING_11'] );
		                                        
		            $final_break_down["starting_points"] = (1 * $formulas['normal']['STARTING_11']);
					$total_final_breakdown[$player_stats['player_id']]["starting_points"] = (!empty($total_final_breakdown[$player_stats['player_id']]["starting_points"])) ? (1 * $formulas['normal']['STARTING_11']) + $total_final_breakdown[$player_stats['player_id']]["starting_points"] : (1 * $formulas['normal']['STARTING_11']);
				}

				// Runs
				if ($player_stats['batting_runs'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['batting_runs'] * $formulas['normal']['RUN'] );
					$normal_testing_var['RUN'] = ( $player_stats['batting_runs'] * $formulas['normal']['RUN'] );
		                                        
		            $final_break_down["batting"]["RUN"] = ($player_stats['batting_runs'] * $formulas['normal']['RUN']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]["RUN"] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]["RUN"])) ? ($player_stats['batting_runs'] * $formulas['normal']['RUN']) + $total_final_breakdown[$player_stats['player_id']]["batting"]["RUN"] : ($player_stats['batting_runs'] * $formulas['normal']['RUN']);
					//echo "<pre>";print_r($total_final_breakdown);//die;
				}

				//Wickets
				if ($player_stats['bowling_wickets'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET'] );
					$normal_testing_var['WICKET'] = ( $player_stats['bowling_wickets'] * $formulas['normal']['WICKET'] );
		                                        
		            $final_break_down["bowling"]["WICKET"] = ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]["WICKET"] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]["WICKET"])) ? ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET'] ) + $total_final_breakdown[$player_stats['player_id']]["bowling"]["WICKET"] : ($player_stats['bowling_wickets'] * $formulas['normal']['WICKET']);
				}

				//Catch
				if ($player_stats['catch'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['catch'] * $formulas['normal']['CATCH'] );
					$normal_testing_var['CATCH'] = ( $player_stats['catch'] * $formulas['normal']['CATCH'] );
		                                        
		            $final_break_down["fielding"]['CATCH'] = ($player_stats['catch'] * $formulas['normal']['CATCH']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['CATCH'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['CATCH'])) ? ($player_stats['catch'] * $formulas['normal']['CATCH']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['CATCH'] : ($player_stats['catch'] * $formulas['normal']['CATCH']);
				}

				//Stumping
				if ($player_stats['stumping'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['stumping'] * $formulas['normal']['STUMPING'] );
					$normal_testing_var['STUMPING'] = ( $player_stats['stumping'] * $formulas['normal']['STUMPING'] );
		                                        
		            $final_break_down["fielding"]['STUMPING'] = ($player_stats['stumping'] * $formulas['normal']['STUMPING']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['STUMPING'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['STUMPING'])) ? ($player_stats['stumping'] * $formulas['normal']['STUMPING']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['STUMPING'] : ($player_stats['stumping'] * $formulas['normal']['STUMPING']);
				}

				//Runout Direct Hit
				if ($player_stats['run_out'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT'] );
					$normal_testing_var['RUN_OUT_DIRECT_HIT'] = ( $player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT'] );
		                                        
		            $final_break_down["fielding"]['RUN_OUT_DIRECT_HIT'] = ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_DIRECT_HIT'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_DIRECT_HIT'])) ? ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_DIRECT_HIT'] : ($player_stats['run_out'] * $formulas['normal']['RUN_OUT_DIRECT_HIT']);
				}

				//Runout run_out_throw
				if ($player_stats['run_out_throw'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER'] );
					$normal_testing_var['RUN_OUT_THROWER'] = ( $player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER'] );
		                                        
		            $final_break_down["fielding"]['RUN_OUT_THROWER'] = ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_THROWER'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_THROWER'])) ? ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_THROWER'] : ($player_stats['run_out_throw'] * $formulas['normal']['RUN_OUT_THROWER']);
				}

				//Runout run_out_catch
				if ($player_stats['run_out_catch'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER'] );
					$normal_testing_var['RUN_OUT_CATCHER'] = ( $player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER'] );
		                                        
		            $final_break_down["fielding"]['RUN_OUT_CATCHER'] = ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER']);
					$total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_CATCHER'] = (!empty($total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_CATCHER'])) ? ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_THROWER']) + $total_final_breakdown[$player_stats['player_id']]["fielding"]['RUN_OUT_CATCHER'] : ($player_stats['run_out_catch'] * $formulas['normal']['RUN_OUT_CATCHER']);
				}

				//Dismissal for duck (batsmen, wicket-keeper and all-rounders)
				if ($player_stats['batting_runs'] == 0 && $player_stats['dismissed'] == 1 && $player_stats['position'] != 'BOW')
				{
					$normal_score = $normal_score + (1 * $formulas['normal']['DUCK'] );
					$normal_testing_var['DUCK'] = ( 1 * $formulas['normal']['DUCK'] );
		                                        
		            $final_break_down["batting"]['DUCK'] = (1 * $formulas['normal']['DUCK']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]['DUCK'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['DUCK'])) ? (1 * $formulas['normal']['DUCK']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['DUCK'] : (1 * $formulas['normal']['DUCK']);
				}

				//echo $normal_score;die;
				/* ####### NORMAL SCORING RULE END ########### */


				/* ####### Bonus SCORING ########### */
				// Catch bonus
				if ($player_stats['catch'] >= 3)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['CATCH_3'] );
					$bonus_testing_var['CATCH_3'] = ( $formulas['bonus']['CATCH_3'] );
	                                            
	                $final_break_down["bowling"]['CATCH_3'] = ($formulas['bonus']['CATCH_3']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['CATCH_3'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['CATCH_3'])) ? ($formulas['bonus']['CATCH_3']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['CATCH_3'] : ($formulas['bonus']['CATCH_3']);
				}

				// Fours Bonus
				if ($player_stats['batting_fours'] > 0)
				{
					$bonus_score = $bonus_score + ($player_stats['batting_fours'] * $formulas['bonus']['FOUR'] );
					$bonus_testing_var['FOUR'] = ( $player_stats['batting_fours'] * $formulas['bonus']['FOUR'] );
		                                        
		            $final_break_down["batting"]['FOUR'] = ($player_stats['batting_fours'] * $formulas['bonus']['FOUR']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]['FOUR'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['FOUR'])) ? ($player_stats['batting_fours'] * $formulas['bonus']['FOUR']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['FOUR'] : ($player_stats['batting_fours'] * $formulas['bonus']['FOUR']);
				}

				// Sixes Bonus
				if ($player_stats['batting_sixes'] > 0)
				{
					$bonus_score = $bonus_score + ($player_stats['batting_sixes'] * $formulas['bonus']['SIX'] );
					$bonus_testing_var['SIX'] = ( $player_stats['batting_sixes'] * $formulas['bonus']['SIX'] );
		                                        
		            $final_break_down["batting"]['SIX'] = ($player_stats['batting_sixes'] * $formulas['bonus']['SIX']);
					$total_final_breakdown[$player_stats['player_id']]["batting"]['SIX'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['SIX'])) ? ($player_stats['batting_sixes'] * $formulas['bonus']['SIX']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['SIX'] : ($player_stats['batting_sixes'] * $formulas['bonus']['SIX']);
				}
				
				//For 30_BONUS HALF_CENTURY & CENTURY
				if ($player_stats['batting_runs'] >= 30 && $player_stats['batting_runs'] < 50)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['30_BONUS'] );
					$bonus_testing_var['30_BONUS'] = ( $formulas['bonus']['30_BONUS'] );
	                                            
	                $final_break_down["batting"]['30_BONUS'] = ($formulas['bonus']['30_BONUS']);

					$total_final_breakdown[$player_stats['player_id']]["batting"]['30_BONUS'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['30_BONUS'])) ? ($formulas['bonus']['30_BONUS']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['30_BONUS'] : ($formulas['bonus']['30_BONUS']);
				}
				elseif ($player_stats['batting_runs'] >= 50)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['HALF_CENTURY'] );
					$bonus_testing_var['HALF_CENTURY'] = ( $formulas['bonus']['HALF_CENTURY'] );
	                                            
	                $final_break_down["batting"]['HALF_CENTURY'] = ($formulas['bonus']['HALF_CENTURY']);

					$total_final_breakdown[$player_stats['player_id']]["batting"]['HALF_CENTURY'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['HALF_CENTURY'])) ? ($formulas['bonus']['HALF_CENTURY']) + $total_final_breakdown[$player_stats['player_id']]["batting"]['HALF_CENTURY'] : ($formulas['bonus']['HALF_CENTURY']);
				}
									
				
				if ($player_stats['bowling_maiden_overs'] > 0)
				{
					$bonus_score = $bonus_score + ($player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER'] );
					$bonus_testing_var['MAIDEN_OVER'] = ( $player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER'] );
                                                    
                    $final_break_down["bowling"]['MAIDEN_OVER'] = ($player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['MAIDEN_OVER'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['MAIDEN_OVER'])) ? ($player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['MAIDEN_OVER'] : ($player_stats['bowling_maiden_overs'] * $formulas['bonus']['MAIDEN_OVER']);
				}
				
				//For TWO_WICKET & THREE_WICKET HAUL
				if ($player_stats['bowling_wickets'] == 2)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['TWO_WICKET'] );
					$bonus_testing_var['TWO_WICKET'] = ( $formulas['bonus']['TWO_WICKET'] );
	                                            
	                $final_break_down["bowling"]['TWO_WICKET'] = ($formulas['bonus']['TWO_WICKET']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['TWO_WICKET'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['TWO_WICKET'])) ? ($formulas['bonus']['TWO_WICKET']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['TWO_WICKET'] : ($formulas['bonus']['TWO_WICKET']);
				}
				elseif ($player_stats['bowling_wickets'] >= 3)
				{
					$bonus_score = $bonus_score + ( $formulas['bonus']['THREE_WICKET'] );
					$bonus_testing_var['THREE_WICKET'] = ( $formulas['bonus']['THREE_WICKET'] );
	                                            
	                $final_break_down["bowling"]['THREE_WICKET'] = ($formulas['bonus']['THREE_WICKET']);
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['THREE_WICKET'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['THREE_WICKET'])) ? ($formulas['bonus']['THREE_WICKET']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['THREE_WICKET'] : ($formulas['bonus']['THREE_WICKET']);
				}
				

				//Bowled and LBW Bonus
				if (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) > 0)
				{
					$bonus_score = $bonus_score + (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED'] );
					$bonus_testing_var['LBW_BOWLED'] = ( ($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED'] );
		                                        
		            $final_break_down["bowling"]['LBW_BOWLED'] = (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED']);
		            
					$total_final_breakdown[$player_stats['player_id']]["bowling"]['LBW_BOWLED'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['LBW_BOWLED'])) ? (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['LBW_BOWLED'] : (($player_stats['bowling_bowled']+$player_stats['bowling_lbw']) * $formulas['bonus']['LBW_BOWLED']);
				}
				
				/* ####### Bonus SCORING END  ########### */

				/* ####### ECONOMY RATE START  ########### */
				if ($player_stats['bowling_overs'] >= $formulas['economy_rate']['MINIMUM_BOWLING_BOWLED_OVER'])
				{
					if ($player_stats['bowling_economy_rate'] < 7)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_BELOW_7'] );
						$economy_rate_testing_var['ECONOMY_BELOW_7'] = ( $formulas['economy_rate']['ECONOMY_BELOW_7'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ($formulas['economy_rate']['ECONOMY_BELOW_7']);
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ($formulas['economy_rate']['ECONOMY_BELOW_7']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ($formulas['economy_rate']['ECONOMY_BELOW_7']);
					}                               
					elseif ($player_stats['bowling_economy_rate'] >= 7 && $player_stats['bowling_economy_rate'] <= 7.99)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_7_799'] );
						$economy_rate_testing_var['ECONOMY_7_799'] = ( $formulas['economy_rate']['ECONOMY_7_799'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ($formulas['economy_rate']['ECONOMY_7_799']);
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ($formulas['economy_rate']['ECONOMY_7_799']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ($formulas['economy_rate']['ECONOMY_7_799']);
					}
					elseif ($player_stats['bowling_economy_rate'] >= 8 && $player_stats['bowling_economy_rate'] <= 9)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_8_9'] );
						$economy_rate_testing_var['ECONOMY_8_9'] = ( $formulas['economy_rate']['ECONOMY_8_9'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ( $formulas['economy_rate']['ECONOMY_8_9'] );
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ( $formulas['economy_rate']['ECONOMY_8_9'] ) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ( $formulas['economy_rate']['ECONOMY_8_9'] );
					}
					elseif ($player_stats['bowling_economy_rate'] >= 14 && $player_stats['bowling_economy_rate'] <= 15)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_14_15'] );
						$economy_rate_testing_var['ECONOMY_14_15'] = ( $formulas['economy_rate']['ECONOMY_14_15'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ( $formulas['economy_rate']['ECONOMY_14_15'] );
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ( $formulas['economy_rate']['ECONOMY_14_15'] ) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ( $formulas['economy_rate']['ECONOMY_14_15'] );
					}
					elseif ($player_stats['bowling_economy_rate'] >= 15.01 && $player_stats['bowling_economy_rate'] <= 16)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_1501_16'] );
						$economy_rate_testing_var['ECONOMY_1501_16'] = ( $formulas['economy_rate']['ECONOMY_1501_16'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ($formulas['economy_rate']['ECONOMY_1501_16']);
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ($formulas['economy_rate']['ECONOMY_1501_16']) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ($formulas['economy_rate']['ECONOMY_1501_16']);
					}
					elseif ($player_stats['bowling_economy_rate'] > 16)
					{
						$economy_rate_score = $economy_rate_score + ( $formulas['economy_rate']['ECONOMY_ABOVE_16'] );
						$economy_rate_testing_var['ECONOMY_ABOVE_16'] = ( $formulas['economy_rate']['ECONOMY_ABOVE_16'] );
                                                            
                        $final_break_down['bowling']['ECONOMY_RATE'] = ( $formulas['economy_rate']['ECONOMY_ABOVE_16'] );
						$total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'])) ? ( $formulas['economy_rate']['ECONOMY_ABOVE_16'] ) + $total_final_breakdown[$player_stats['player_id']]["bowling"]['ECONOMY_RATE'] : ( $formulas['economy_rate']['ECONOMY_ABOVE_16'] );
					}
				} 

				/* ####### ECONOMY RATE SCORING END  ########### */

				/* ####### STRIKE RATE SCORING START  ########### */
				if($player_stats['position'] != 'BOW')
				{  
					if ($player_stats['batting_balls_faced'] >= $formulas['strike_rate']['MINIMUM_BALL_PLAYED'])
					{
						if ($player_stats['batting_strike_rate'] > 190)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_190'] );
							$strike_rate_testing_var['STRIKE_RATE_ABOVE_190'] = ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_190'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_190'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_190'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_ABOVE_190'] );
						}
						elseif ($player_stats['batting_strike_rate'] >= 170.01 && $player_stats['batting_strike_rate'] <= 190)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_17001_190'] );
							$strike_rate_testing_var['STRIKE_RATE_17001_190'] = ( $formulas['strike_rate']['STRIKE_RATE_17001_190'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_17001_190'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_17001_190'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_17001_190'] );
						}
						elseif ($player_stats['batting_strike_rate'] >= 150 && $player_stats['batting_strike_rate'] <= 170)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_150_170'] );
							$strike_rate_testing_var['STRIKE_RATE_150_170'] = ( $formulas['strike_rate']['STRIKE_RATE_150_170'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_150_170'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_150_170'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_150_170'] );
						}
						elseif ($player_stats['batting_strike_rate'] >= 70 && $player_stats['batting_strike_rate'] <= 80)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_70_80'] );
							$strike_rate_testing_var['STRIKE_RATE_70_80'] = ( $formulas['strike_rate']['STRIKE_RATE_70_80'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_70_80'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_70_80'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_70_80'] );
						}
						elseif ($player_stats['batting_strike_rate'] >= 60 && $player_stats['batting_strike_rate'] <= 69.99)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_60_6999'] );
							$strike_rate_testing_var['STRIKE_RATE_60_6999'] = ( $formulas['strike_rate']['STRIKE_RATE_60_6999'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_60_6999'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_60_6999'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_60_6999'] );
						}
						elseif ($player_stats['batting_strike_rate'] < 60)
						{
							$strike_rate_score = $strike_rate_score + ( $formulas['strike_rate']['STRIKE_RATE_BELOW_60'] );
							$strike_rate_testing_var['STRIKE_RATE_BELOW_60'] = ( $formulas['strike_rate']['STRIKE_RATE_BELOW_60'] );
                                                                
                            $final_break_down['batting']['STRIKE_RATE'] = ( $formulas['strike_rate']['STRIKE_RATE_BELOW_60'] );
							$total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] = (!empty($total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'])) ? ( $formulas['strike_rate']['STRIKE_RATE_BELOW_60'] ) + $total_final_breakdown[$player_stats['player_id']]["batting"]['STRIKE_RATE'] : ( $formulas['strike_rate']['STRIKE_RATE_BELOW_60'] );
						}
					}
				} 

				/* ####### STRIKE RATE SCORING END  ########### */

				//booster
				$booster_arr = array();
				if(isset($total_final_breakdown[$player_stats['player_id']])){
					$booster_arr = $this->get_booster_break_down($total_final_breakdown[$player_stats['player_id']]);
				}
				$each_player_score['booster_break_down'] = $all_player_scores[$player_stats['player_id']]['booster_break_down'] = json_encode($booster_arr);

								  
				$each_player_score['normal_score']			= floatval($normal_score);
				$each_player_score['bonus_score']			= floatval($bonus_score);
				$each_player_score['economy_rate_score']	= floatval($economy_rate_score);
				$each_player_score['strike_rate_score']		= floatval($strike_rate_score);
				$all_player_testing_var[$player_stats['player_id']]['normal'][$player_stats['innings']]		= $normal_testing_var;
				$all_player_testing_var[$player_stats['player_id']]['bonus'][$player_stats['innings']]			= $bonus_testing_var;
				$all_player_testing_var[$player_stats['player_id']]['economy_rate'][$player_stats['innings']]	= $economy_rate_testing_var;
				$all_player_testing_var[$player_stats['player_id']]['strike_rate'][$player_stats['innings']]	= $strike_rate_testing_var;
		                                
				$player_final_breakdown[$player_stats['player_id']][$player_stats['innings']]	= $final_break_down;
				//echo '<pre>';print_r($player_final_breakdown); die;
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('normal_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['normal_score'] = $all_player_scores[$player_stats['player_id']]['normal_score'] = $all_player_scores[$player_stats['player_id']]['normal_score'] + $each_player_score['normal_score'];
				}
				
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('bonus_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['bonus_score'] = $all_player_scores[$player_stats['player_id']]['bonus_score'] = $all_player_scores[$player_stats['player_id']]['bonus_score'] + $each_player_score['bonus_score'];
				}
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('economy_rate_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['economy_rate_score'] = $all_player_scores[$player_stats['player_id']]['economy_rate_score'] = $all_player_scores[$player_stats['player_id']]['economy_rate_score'] + $each_player_score['economy_rate_score'];
				}
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('strike_rate_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['strike_rate_score'] = $all_player_scores[$player_stats['player_id']]['strike_rate_score'] = $all_player_scores[$player_stats['player_id']]['strike_rate_score'] + $each_player_score['strike_rate_score'];
				}

				
				$all_player_scores[$player_stats['player_id']] = $each_player_score;

				$all_player_scores[$player_stats['player_id']]['break_down'] = json_encode($all_player_testing_var[$player_stats['player_id']]);
		                                
		        $all_player_scores[$player_stats['player_id']]['final_break_down'] = json_encode(array('total' => $total_final_breakdown[$player_stats['player_id']], 'innings'=> $player_final_breakdown[$player_stats['player_id']]));
				$all_player_scores[$player_stats['player_id']]['match_format'] = $season_game['format'];
				$all_player_scores[$player_stats['player_id']]['second_inning_score'] = 0;	
			}
		}// End  of  Empty of game_ stats

		//echo "<pre>";print_r($all_player_scores);die;
		if(!empty($all_player_scores))
		{
			//Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
			//Update score first zero then update
			$this->db->where('season_id', $season_id);
			$this->db->where('league_id', $league_id);
			$this->db->update(GAME_PLAYER_SCORING,array("normal_score"=>"0","bonus_score"=>"0","economy_rate_score"=>"0","strike_rate_score"=>"0","score"=>"0","break_down"=>NULL,"final_break_down"=>NULL,"booster_break_down"=>NULL));
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

	private function get_booster_break_down($final_break_down){
		$booster_arr = array("FOUR"=>"0","SIX"=>"0","WICKET"=>"0","RUN_OUT"=>"0");
		if(isset($final_break_down["batting"]['FOUR'])){
			$booster_arr["FOUR"] = $final_break_down["batting"]['FOUR'];
		}
		if(isset($final_break_down["batting"]['SIX'])){
			$booster_arr["SIX"] = $final_break_down["batting"]['SIX'];
		}
		if(isset($final_break_down["bowling"]["WICKET"])){
			$booster_arr["WICKET"] = $final_break_down["bowling"]["WICKET"];
		}
		if(isset($final_break_down["fielding"]["RUN_OUT_THROWER"])){
			$booster_arr["RUN_OUT"] = $booster_arr["RUN_OUT"] + $final_break_down["fielding"]["RUN_OUT_THROWER"];
		}
		if(isset($final_break_down["fielding"]["RUN_OUT_CATCHER"])){
			$booster_arr["RUN_OUT"] = $booster_arr["RUN_OUT"] + $final_break_down["fielding"]["RUN_OUT_CATCHER"];
		}
		if(isset($final_break_down["fielding"]["RUN_OUT_DIRECT_HIT"])){
			$booster_arr["RUN_OUT"] = $booster_arr["RUN_OUT"] + $final_break_down["fielding"]["RUN_OUT_DIRECT_HIT"];
		}

		return $booster_arr;
	}

	/**
     * function used for Save individual player score on table based on game id
     * @param array $player_score
     * @return boolean
     */
	protected function save_player_scoring($player_score)
	{
		//echo "<pre>";print_r($player_score);die;
		$table_value = array();
		$sql = "REPLACE INTO ".$this->db->dbprefix(GAME_PLAYER_SCORING)." (season_id, match_format, player_id, week, scheduled_date, normal_score, bonus_score, economy_rate_score, strike_rate_score, score, league_id, break_down,final_break_down,2nd_inning_score,booster_break_down)
							VALUES ";

		foreach ($player_score as $player_unique_id => $value)
		{
			$main_score = $value['normal_score'] + $value['bonus_score'] + $value['economy_rate_score'] + $value['strike_rate_score'];

			$str = " ('" . $value['season_id'] . "','" . $value['match_format'] . "','" . $value['player_id'] . "','" . $value['week'] . "','" . $value['scheduled_date'] . "','" . $value['normal_score'] . "','" . $value['bonus_score'] . "','" . $value['economy_rate_score'] . "','" . $value['strike_rate_score'] . "','" . $main_score . "','" . $value['league_id'] . "','" . $value['break_down'] . "','". $value['final_break_down'] . "', '" . $value['second_inning_score'] . "','". $value['booster_break_down'] . "' )";

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
				$filePath     = $s3_dir.$feed_img_name;
				
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
	public function get_season_details($season_game_uid,$sports_id,$producer_season_data = array())
    {
    	$producer_data = array();
    	if(!empty($producer_season_data))
    	{
    		$producer_data = json_decode($producer_season_data,TRUE);
    	}

    	if(empty($producer_data))
		{	
			$url = $this->api['api_url']."get_season_details?season_game_uid=".$season_game_uid."&token=".$this->api['access_token'];
			$season_data = @file_get_contents($url);
			if (!$season_data)
			{
				exit;
			}
			$season_array = @json_decode(($season_data), TRUE);
		}else{
			$season_array = $producer_data;
		}	
		//echo "<pre>";print_r($season_array);die;
		//All team uid with team id
		$team_uid_array = array($season_array['response']['data']['home_uid'],$season_array['response']['data']['away_uid']);
		//echo "<pre>";print_r($team_uid_array);die;
        $team_ids = $this->get_team_id_with_team_uid($sports_id,'',$team_uid_array);
		//echo "DB -<pre>";print_r($team_ids);die;
		if(!empty($season_array['response']['data']))
		{
			$value = $season_array['response']['data'];
			$league_info = $this->get_single_row("league_id", LEAGUE, array("league_uid" => $value['league_uid']));
			if(empty($league_info)){
				exit;
			}
			$home_id = @$team_ids[$value['home_uid']];
            $away_id = @$team_ids[$value['away_uid']];
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

			$season_info = $this->get_single_row("season_id,is_updated_playing,scoring_alert,delay_by_admin,notify_by_admin,delay_minute,season_scheduled_date,2nd_inning_date,second_inning_update", SEASON, array("season_game_uid" => $season_game_uid,"league_id" => $league_info['league_id']));
			$season_id = @$season_info['season_id'];

			$secong_inning_date = NULL;
            $allow_2nd_inning = isset($this->app_config['allow_2nd_inning']) ? $this->app_config['allow_2nd_inning']['key_value'] : 0;
                   		

			if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 0)
			{
				$match_info['delay_minute'] = $value['delay_minute'];
				$match_info['delay_message'] = $value['delay_message'];
				if(isset($match_info['delay_minute']) && $match_info['delay_minute'] > 0 && $season_info['season_scheduled_date'] > format_date())
				{
					$match_info['season_scheduled_date'] = date('Y-m-d H:i:s', strtotime('+'.$match_info['delay_minute'].' minutes', strtotime($value['season_scheduled_date'])));
					$match_info['scheduled_date'] = date('Y-m-d', strtotime($value['season_scheduled_date']));
					if($allow_2nd_inning == 1 && in_array($value['format'],array(1,3)) && (isset($season_info['second_inning_update']) && $season_info['second_inning_update'] == 0 && $season_info['2nd_inning_date'] > format_date()))
		            {
	                    $second_inning_interval = second_inning_game_interval($value['format']);
	                    $secong_inning_date = date("Y-m-d H:i:s",strtotime($match_info['season_scheduled_date'].' +'.$second_inning_interval.' minutes'));
	                }    

				}elseif($value['season_scheduled_date'] > format_date())
				{
					$match_info['season_scheduled_date'] = $value['season_scheduled_date'];
					$match_info['scheduled_date'] = $value['scheduled_date'];
					if($allow_2nd_inning == 1 && in_array($value['format'],array(1,3)) && (isset($season_info['second_inning_update']) && $season_info['second_inning_update'] == 0 && $season_info['2nd_inning_date'] > format_date()))
		            {
	                    $second_inning_interval = second_inning_game_interval($value['format']);
	                    $secong_inning_date = date("Y-m-d H:i:s",strtotime($match_info['season_scheduled_date'].' +'.$second_inning_interval.' minutes'));
	                }  
				}
			}else if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 1)
			{
				$match_info['season_scheduled_date'] = $season_info['season_scheduled_date'];
				$match_info['scheduled_date'] = date('Y-m-d', strtotime($match_info['season_scheduled_date']));
				if($allow_2nd_inning == 1 && in_array($value['format'],array(1,3)) 
					&& (isset($season_info['second_inning_update']) && $season_info['second_inning_update'] == 0 && $season_info['2nd_inning_date'] > format_date()))
	            {
                    $second_inning_interval = second_inning_game_interval($value['format']);
                    $secong_inning_date = date("Y-m-d H:i:s",strtotime($match_info['season_scheduled_date'].' +'.$second_inning_interval.' minutes'));
                }   
			}elseif($value['season_scheduled_date'] > format_date())
			{
				$match_info['season_scheduled_date'] = $value['season_scheduled_date'];
				$match_info['scheduled_date'] = $value['scheduled_date'];
				if($allow_2nd_inning == 1 && in_array($value['format'],array(1,3)) 
					&& (isset($season_info['second_inning_update']) && $season_info['second_inning_update'] == 0 && $season_info['2nd_inning_date'] > format_date()))
	            {
                    $second_inning_interval = second_inning_game_interval($value['format']);
                    $secong_inning_date = date("Y-m-d H:i:s",strtotime($match_info['season_scheduled_date'].' +'.$second_inning_interval.' minutes'));
                } 
			}else{ 
				exit('Time over');
			}
			if(isset($season_info['notify_by_admin']) && $season_info['notify_by_admin'] == 0){
				$match_info['custom_message'] = $value['custom_message'];
			}
			if(isset($season_info['is_updated_playing']) && $season_info['is_updated_playing'] == 0 && (isset($season_id) && $season_id > 0)){
				if(isset($value['playing_list']) && $value['playing_list'] != "" && count(json_decode($value['playing_list'])) > 0)
				{
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

			//add update second inning date tile
			if(isset($season_info['2nd_inning_date']) && $season_info['second_inning_update'] == 1 && $season_info['2nd_inning_date'] > format_date())
			{
				$match_info['2nd_inning_date'] = $season_info['2nd_inning_date'];
			}elseif(isset($secong_inning_date) && $secong_inning_date != '' && $secong_inning_date > format_date())
			{
				$match_info['2nd_inning_date'] = $secong_inning_date;
			}
			if(isset($season_info['2nd_inning_date']) && $season_info['2nd_inning_date'] <= format_date())
			{
				unset($match_info['2nd_inning_date']);
			}	
			
			//$match_data[] = $match_info;
			$match_keys = array();
            $match_k = $match_info['league_id'].'_'.$match_info['season_game_uid'];
            $match_data[$match_k] = $match_info;
            $match_keys[] = $match_k; 

            //batting order from toss
            $toss = json_decode(@$value['toss'],TRUE);
			//echo "<pre>";print_r($toss);//die;
			//echo "<pre>";print_r($match_data[$match_k]['season_scheduled_date']);
			//echo "<pre>";print_r($value);die;

			if(isset($match_data[$match_k]['season_scheduled_date']) && isset($toss['winner']) && $toss['winner'] == $value['home_uid'])
			{
				if($toss['decision'] == 1)
				{
					$batting_order[0] = array(
												"team_uid" => $value['home_uid'],
												"team_id" => $home_id,
												"inning" => 1	
											);
				
					$batting_order[1] = array(
												"team_uid" => $value['away_uid'],
												"team_id" => $away_id,
												"inning" => 1	
											);
				}else{
					$batting_order[0] = array(
												"team_uid" => $value['away_uid'],
												"team_id" => $away_id,
												"inning" => 1
											);
				
					$batting_order[1] = array(
												"team_uid" => $value['home_uid'],
												"team_id" => $home_id,
												"inning" => 1		
											);	
				}
			}elseif(isset($match_data[$match_k]['season_scheduled_date']) && isset($toss['winner']) && $toss['winner'] == $value['away_uid'])
			{
				if($toss['decision'] == 1)
				{
					$batting_order[0] = array(
												"team_uid" => $value['away_uid'],
												"team_id" => $away_id,
												"inning" => 1
											);
				
					$batting_order[1] = array(
												"team_uid" => $value['home_uid'],
												"team_id" => $home_id,
												"inning" => 1		
											);
				}else{

					$batting_order[0] = array(
												"team_uid" => $value['home_uid'],
												"team_id" => $home_id,
												"inning" => 1	
											);
				
					$batting_order[1] = array(
												"team_uid" => $value['away_uid'],
												"team_id" => $away_id,
												"inning" => 1	
											);
				}
			}
			if(isset($batting_order) && !empty($batting_order))
			{
				$match_data[$match_k]['team_batting_order'] = json_encode($batting_order);
			}	
			//echo "<pre>";print_r($batting_order);die;
			//echo "<pre>";print_r($match_data);die;
			if (!empty($match_data))
			{
				$concat_key = 'CONCAT(league_id,"_",season_game_uid)';
                $this->insert_or_update_on_exist($match_keys,$match_data,$concat_key,SEASON,'season_id');

				//update match cache and bucket files
		        $this->db->select("CS.collection_master_id")
		                ->from(COLLECTION_SEASON . " CS")
		                ->join(COLLECTION_MASTER.' as CM', 'CM.collection_master_id = CS.collection_master_id',"INNER")
		                ->where('CM.season_game_count', 1)
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

			    		//for lineup out push 
			    		$content = array();
			    		$content['url'] = $server_name."/cron/cron/process_lineupout_notification_for_game/".$season_id;
			    		add_data_in_queue($content,'lineupout_game_process');
						
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
		if(empty($producer_data))
		{
			exit();	
		}else{
			return true;
		}
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
		//echo "<pre>dsf"; print_r($season_game);die;
		if (!empty($season_game))
		{
			if($season_game["format"] == CRICKET_TEST)
			{
				$this->calculate_fantasy_points_for_test($sports_id,$season_game);
			}elseif($season_game["format"] == CRICKET_ONE_DAY)
			{
				$this->calculate_fantasy_points_for_odi($sports_id,$season_game);
			}elseif($season_game["format"] == CRICKET_T20)
			{
				$this->calculate_fantasy_points_for_ttwenty($sports_id,$season_game);
			}elseif($season_game["format"] == CRICKET_T10)
			{
				$this->calculate_fantasy_points_for_tten($sports_id,$season_game);
			}	
		} // End of Empty team check
		exit();
	}


	public function second_inning_contest_rescheduled($season_id)
	{
		$this->db->select("CM.collection_master_id,CM.league_id,IFNULL(CM.2nd_inning_date,'') as 2nd_inning_date,CS.season_id,CS.season_id,L.sports_id,S.format,S.2nd_inning_date as match_date")
		    ->from(COLLECTION_MASTER. " CM")
		    ->join(COLLECTION_SEASON. " CS","CS.collection_master_id = CM.collection_master_id")
		    ->join(LEAGUE." as L","L.league_id = CM.league_id")
		    ->join(SEASON." as S","S.season_id = CS.season_id")
		    ->where("CS.season_id",$season_id)
		  	->where("S.2nd_inning_date != CM.2nd_inning_date")
		    ->where("CM.2nd_inning_date > ", format_date());
		$query = $this->db->get();
		$collection_list = $query->result_array();
		//echo "<pre>";print_r($collection_list);die;
		if(!empty($collection_list))
		{
			  $allow_2nd_inning = isset($this->app_config['allow_2nd_inning']) ? $this->app_config['allow_2nd_inning']['key_value'] : 0;
			  foreach($collection_list as $row)
			  {
			    $sports_id = $row['sports_id'];
			    $cm_id = $row['collection_master_id'];
			    $season_id = $row['season_id'];
			    $second_inning_date = $row['2nd_inning_date'];
			    $match_date = $row['match_date'];
			    if($second_inning_date != $match_date)
			    {
				    if($allow_2nd_inning == 1 && in_array($row['format'],array(1,3)))
				    {
				         
				      //Update collection master table
				      $this->db->set("2nd_inning_date",$match_date);
				      $this->db->where("status",0);
				      $this->db->where("collection_master_id",$cm_id);
				      $this->db->update(COLLECTION_MASTER); 
				      if($allow_2nd_inning == 1){
				        //Update Contest table
				        $this->db->set("season_scheduled_date",$match_date);
				        $this->db->where("status",0);
				        $this->db->where("is_2nd_inning",1);
				        $this->db->where("collection_master_id",$cm_id);
				        $this->db->update(CONTEST);
				      }

				      $this->delete_cache_data('fixture_'.$cm_id);
				      $this->delete_s3_bucket_file("lobby_fixture_list_".$sports_id.".json");
			    	}
				}
			  }
		} 
		exit("Second inning cron run");   
    }

	
}	