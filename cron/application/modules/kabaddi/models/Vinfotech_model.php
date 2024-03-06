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
		$this->api = $this->get_kabaddi_config_detail('vinfotech');
		$this->db	= $this->load->database('db_fantasy', TRUE);
	}

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
		if(@$league_array['status'] == 'error')
		{
			exit("Feed token expire");
		}
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
                                            "delay_minute" 		=> @$value['delay_minute'],
                                            "delay_message" 	=> @$value['delay_message'],
                                            "custom_message" 	=> @$value['custom_message'],
                                            "status" => 0,
                                            "status_overview" => 0,
                                            "week" => '0',
                                            "api_week" => @$value['api_week']
                                            //"match_closure_date" => @$value['match_closure_date']

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
                            $player_string = implode(',',array_map(function($n){return '\''.$n.'\'';},array_unique($player_id_arr)));
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
				//echo "<pre>";print_r($match_data);die;
				$match_array = @json_decode(($match_data), TRUE);
				if(@$match_array['status'] == 'error')
				{
					exit("Feed token expire");
				}
				//echo "<pre>";print_r($match_array);die;
				$match_info = $match_array['response']['match_info'];
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
	                        "season_id" 				=> $season_id,
	                        "team_id" 					=> $team_id,
	                        "player_id" 				=> $player_id,
	                        "scheduled_date" 			=> $scheduled_date,
	                        "game_status" 				=> $match_status,
	                        "green_card" 				=> $value['green_card'],
	                        "played" 				    => $value['played'],
	                        "starter"				    => $value['starter'],
	                        "red_card"				    => $value['red_card'],
	                        "yellow_card"				=> $value['yellow_card'],
	                        "raid_points_bonus"			=> $value['raid_points_bonus'],
	                        "raid_points_total"			=> $value['raid_points_total'],
	                        "raid_points_touch"			=> $value['raid_points_touch'],
	                        "tackle_points_bonus"		=> $value['tackle_points_bonus'],
	                        "tackle_points_capture"		=> $value['tackle_points_capture'],
	                        "tackle_points_tackle"		=> $value['tackle_points_tackle'],
	                        "tackle_points_total"		=> $value['tackle_points_total'],
	                        "points_total"				=> $value['points_total'],
	                        "raids_empty"				=> $value['raids_empty'],
	                        "raids_success"				=> $value['raids_success'],
	                        "raids_super_raids"			=> $value['raids_super_raids'],
	                        "raids_total"				=> $value['raids_total'],
	                        "raids_unsuccess"			=> $value['raids_unsuccess'],
	                        "tackles_success"			=> $value['tackles_success'],
	                        "tackles_super_tackles"		=> $value['tackles_super_tackles'],
	                        "tackles_total"				=> $value['tackles_total'],
	                        "tackles_unsuccess"			=> $value['tackles_unsuccess'],
	                        "all_outs"					=> $value['all_outs'],
	                        "points_all_out"			=> $value['points_all_out'],
	                        "pushing_all_out"			=> $value['pushing_all_out'],
	                        "captain"					=> $value['captain'],
	                        "man_of_match"				=> $value['man_of_match']
                        );

						//echo "<pre>";print_r($score_data);die;
					}
					if(!empty($score_data))
					{
						//echo "<pre>";print_r($score_data);die;
						$this->replace_into_batch(GAME_STATISTICS_KABADDI, array_values($score_data));
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
		exit();		
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
        $sql = $this->db->select("GSK.*,PT.position", FALSE)
                ->from(GAME_STATISTICS_KABADDI . " AS GSK")
                ->join(PLAYER . " AS P", "P.player_id = GSK.player_id AND P.sports_id = $sports_id ", 'INNER')
                ->join(PLAYER_TEAM . " AS PT", "PT.player_id = P.player_id AND PT.player_id = GSK.player_id AND PT.season_id = '".$season_id."' ", 'INNER')
                ->where("GSK.season_id", $season_id)
                ->where("GSK.league_id", $league_id)
                ->group_by("P.player_id")
                ->get();

        $result = $sql->result_array();
        return $result;
    }
  

    /* @Summary: This function for use calculate player fantasy score with the help of fetch actual player score fron API
	 *           or football NFL
	 * database.
	 * @access: public
	 * @param:
	 * @return:
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
				$this->calculate_kabaddi_fantasy_points($sports_id,$season_game,$formula);	   
			}// End of Team loop  
		} // End of Empty team check
		exit();
	}

	public function calculate_kabaddi_fantasy_points($sports_id,$season_game,$formula)
	{
		// Get All Scoring Rules
		$league_id = $season_game['league_id'];
		$season_game_uid		= $season_game['season_game_uid'];
		$season_id		= $season_game['season_id'];
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
				$normal_score		= 0;
				$score				= 0;
                                        
                $final_break_down = array();
				
				$each_player_score = array(
												"season_id"		=> $player_stats['season_id'],
												"player_id"			=> $player_stats['player_id'],
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
				$normal_testing_var = array();
				/* ####### NORMAL SCORING RULE ########### */
				// starter 7
				if($player_stats['starter'] == 1 && $player_stats['played'] == 1)
				{
					$normal_score = $normal_score + (1 * $formula['normal']['STARTING_7'] );
					$normal_testing_var['STARTING_7'] = ( 1 * $formula['normal']['STARTING_7'] );
                                                
                    $final_break_down["STARTING_7"] = (1 * $formula['normal']['STARTING_7']);
					
				}
				// Making a substitute appearance
				if($player_stats['starter'] == 0 && $player_stats['played'] == 1)
				{
					$normal_score = $normal_score + (1 * $formula['normal']['SUBSTITUTE'] );
					$normal_testing_var['SUBSTITUTE'] = ( 1 * $formula['normal']['SUBSTITUTE'] );
                                                
                    $final_break_down["SUBSTITUTE"] = (1 * $formula['normal']['SUBSTITUTE']);
					
				}
				// Each successful raid touch point
				if($player_stats['raid_points_touch'] > 0 )
				{
					$normal_score = $normal_score + ($player_stats['raid_points_touch'] * $formula['normal']['SUCCESSFUL_RAID_TOUCH'] );
					$normal_testing_var['SUCCESSFUL_RAID_TOUCH'] = ( $player_stats['raid_points_touch'] * $formula['normal']['SUCCESSFUL_RAID_TOUCH'] );
                                                
                    $final_break_down["SUCCESSFUL_RAID_TOUCH"] = ($player_stats['raid_points_touch'] * $formula['normal']['SUCCESSFUL_RAID_TOUCH']);
					
				}
				// Raid Bonus
				if($player_stats['raid_points_bonus'] > 0 )
				{
					$normal_score = $normal_score + ($player_stats['raid_points_bonus'] * $formula['normal']['RAID_BONUS'] );
					$normal_testing_var['RAID_BONUS'] = ( $player_stats['raid_points_bonus'] * $formula['normal']['RAID_BONUS'] );
                                                
                    $final_break_down["RAID_BONUS"] = ($player_stats['raid_points_bonus'] * $formula['normal']['RAID_BONUS']);
					
				}

				// Each successful tackle
				if($player_stats['tackles_success'] > 0 )
				{
					$normal_score = $normal_score + ($player_stats['tackles_success'] * $formula['normal']['SUCCESSFUL_TACKLE'] );
					$normal_testing_var['SUCCESSFUL_TACKLE'] = ( $player_stats['tackles_success'] * $formula['normal']['SUCCESSFUL_TACKLE'] );
                                                
                    $final_break_down["SUCCESSFUL_TACKLE"] = ($player_stats['tackles_success'] * $formula['normal']['SUCCESSFUL_TACKLE']);
					
				}

				// Super tackle
				if($player_stats['tackles_super_tackles'] > 0 )
				{
					$normal_score = $normal_score + ($player_stats['tackles_super_tackles'] * $formula['normal']['SUPER_TACKLE'] );
					$normal_testing_var['SUPER_TACKLE'] = ( $player_stats['tackles_super_tackles'] * $formula['normal']['SUPER_TACKLE'] );
                                                
                    $final_break_down["SUPER_TACKLE"] = ($player_stats['tackles_super_tackles'] * $formula['normal']['SUPER_TACKLE']);
					
				}

				// Pushing all out (starting 7)
				if($player_stats['pushing_all_out'] > 0 && $player_stats['starter'] == 1)
				{
					$normal_score = $normal_score + ($player_stats['pushing_all_out'] * $formula['normal']['PUSHING_ALL_OUT_7'] );
					$normal_testing_var['PUSHING_ALL_OUT_7'] = ( $player_stats['pushing_all_out'] * $formula['normal']['PUSHING_ALL_OUT_7'] );
                                                
                    $final_break_down["PUSHING_ALL_OUT_7"] = ($player_stats['pushing_all_out'] * $formula['normal']['PUSHING_ALL_OUT_7']);
					
				}

				// Getting all out (starting 7)
				if($player_stats['getting_all_out'] > 0 && $player_stats['starter'] == 1)
				{
					$normal_score = $normal_score + ($player_stats['getting_all_out'] * $formula['normal']['GETTING_ALL_OUT_7'] );
					$normal_testing_var['GETTING_ALL_OUT_7'] = ( $player_stats['getting_all_out'] * $formula['normal']['GETTING_ALL_OUT_7'] );
                                                
                    $final_break_down["GETTING_ALL_OUT_7"] = ($player_stats['getting_all_out'] * $formula['normal']['GETTING_ALL_OUT_7']);
					
				}

				// Each unsuccessful raid
				if($player_stats['raids_unsuccess'] > 0 )
				{
					$normal_score = $normal_score + ($player_stats['raids_unsuccess'] * $formula['normal']['UNSUCCESSFUL_RAID'] );
					$normal_testing_var['UNSUCCESSFUL_RAID'] = ( $player_stats['raids_unsuccess'] * $formula['normal']['UNSUCCESSFUL_RAID'] );
                                                
                    $final_break_down["UNSUCCESSFUL_RAID"] = ($player_stats['raids_unsuccess'] * $formula['normal']['UNSUCCESSFUL_RAID']);
					
				}

				// Red Card , Yellow Card, Green Card
				if($player_stats['red_card'] > 0 )
				{
					$normal_score = $normal_score + ($player_stats['red_card'] * $formula['normal']['RED_CARD'] );
					$normal_testing_var['RED_CARD'] = ( $player_stats['red_card'] * $formula['normal']['RED_CARD'] );
                                                
                    $final_break_down["RED_CARD"] = ($player_stats['red_card'] * $formula['normal']['RED_CARD']);
					
				}elseif($player_stats['yellow_card'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['yellow_card'] * $formula['normal']['YELLOW_CARD'] );
					$normal_testing_var['YELLOW_CARD'] = ( $player_stats['yellow_card'] * $formula['normal']['YELLOW_CARD'] );
                                                
                    $final_break_down["YELLOW_CARD"] = ($player_stats['yellow_card'] * $formula['normal']['YELLOW_CARD']);
				}
				elseif($player_stats['green_card'] > 0)
				{
					$normal_score = $normal_score + ($player_stats['green_card'] * $formula['normal']['GREEN_CARD'] );
					$normal_testing_var['GREEN_CARD'] = ( $player_stats['green_card'] * $formula['normal']['GREEN_CARD'] );
                                                
                    $final_break_down["GREEN_CARD"] = ($player_stats['green_card'] * $formula['normal']['GREEN_CARD']);
				}
				
				//echo $normal_score;die;
				/* ####### NORMAL SCORING RULE END ########### */

		
				$each_player_score['normal_score']			= floatval($normal_score);
				
				$all_player_testing_var[$player_stats['player_id']]['normal']		= $normal_testing_var;
				
                                        
				$player_final_breakdown[$player_stats['player_id']]	= $final_break_down;
				//echo '<pre>';print_r($player_final_breakdown); die;
				if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('normal_score', $all_player_scores[$player_stats['player_id']]))
				{
					$each_player_score['normal_score'] = $all_player_scores[$player_stats['player_id']]['normal_score'] = $all_player_scores[$player_stats['player_id']]['normal_score'] + $each_player_score['normal_score'];
				}
				
				$all_player_scores[$player_stats['player_id']] = $each_player_score;
				$all_player_scores[$player_stats['player_id']]['break_down'] = json_encode($all_player_testing_var[$player_stats['player_id']]);
			}

		}// End  of  Empty of game_ stats

		if(!empty($all_player_scores))
		{
			//Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
			//echo "<pre>";print_r($all_player_scores);die;
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
		
		return;
	}


	/* Save individual player score on table based on game id */

	protected function save_player_scoring($player_score)
	{
		$table_value = array();
		$sql = "REPLACE INTO " . $this->db->dbprefix(GAME_PLAYER_SCORING) . " (season_id, player_id, week, scheduled_date, normal_score, bonus_score, economy_rate_score, strike_rate_score, score, league_id, break_down,final_break_down)
							VALUES ";

		foreach ($player_score as $player_unique_id => $value)
		{
			$main_score = $value['normal_score'] + $value['bonus_score'] + $value['economy_rate_score'] + $value['strike_rate_score'];

			$str = " ('" . $value['season_id'] . "','" . $value['player_id'] . "','" . $value['week'] . "','" . $value['scheduled_date'] . "','" . $value['normal_score'] . "','" . $value['bonus_score'] . "','" . $value['economy_rate_score'] . "','" . $value['strike_rate_score'] . "','" . $main_score . "','" . $value['league_id'] . "','" . $value['break_down'] . "','". $value['break_down'] . "' )";

			$table_value[] = $str;
		}

		$sql .= implode(", ", $table_value);

		$this->db->query($sql);
	}


	/*
	*This function upload team's flags and jersys from feed s3 to project s3 
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
					$this->load->helper('queue');
		            $server_name = get_server_host_name();
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


    /* @Summary: This function for use calculate player fantasy score with the help of fetch actual player score fron API
	 *           or football NFL
	 * database.
	 * @access: public
	 * @param:
	 * @return:
	 */
	public function calculated_fantasy_score_by_match_id($sports_id,$league_id,$season_game_uid)
	{
		// Get All Live Games List 
		$season_game = $this->get_season_match_details($season_game_uid,$league_id);
		//echo "<pre>"; print_r($season_game);die;
		if (!empty($season_game))
		{
			$formula = $this->get_scoring_rules($sports_id);
			$this->calculate_kabaddi_fantasy_points($sports_id,$season_game,$formula);	
		} // End of Empty team check
		exit();
		exit();
	}


}	