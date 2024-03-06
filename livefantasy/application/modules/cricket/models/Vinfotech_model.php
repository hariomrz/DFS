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
		$this->db	= $this->load->database('livefantasy_db', TRUE);
		$this->api = $this->get_cricket_config_detail('vinfotech');
		
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
		if(!empty($league_array['response']['data']))
		{
			foreach ($league_array['response']['data'] as $key => $value) 
			{
				if(!$value['league_last_date']){
					continue;
				}
				$data[] = array(
								"league_uid" 	=> @$value['league_uid'],
								"league_abbr" 	=> @$value['league_abbr'],
								"league_name" 	=> @$value['league_name'],
								"league_display_name" => @$value['league_display_name'],
								"league_schedule_date" => @$value['league_schedule_date'],
								"league_last_date" => @$value['league_last_date'],
								"sports_id" => $sports_id
							);
			}
			if (!empty($data))
			{
				//echo "<pre>";print_r($data);die;
				$this->replace_into_batch(LEAGUE, $data);
				//For primary key setting
				$this->set_auto_increment_key('league','league_id');
				echo "All leagues (series) are inserted.";
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
				//echo "<pre>";print_r($season_array['response']['data']);die;
				if(!empty($season_array['response']['data']))
				{
					$match_data = $temp_match_array	= array();
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

                        $home = $value['home'];
                   		$away = $value['away'];
						$home_uid = $value['home_uid'];
                   		$away_uid = $value['away_uid'];
                   		$season_info = $this->get_single_row("home,away,scoring_alert,delay_by_admin,notify_by_admin,delay_minute,delay_message,custom_message", SEASON, array("season_game_uid" => $value['season_game_uid'],"league_id" => $league['league_id']));
						if(!empty($season_info) && $season_info['home'] != "TBA" && $season_info['away'] != "TBA" && $season_info['home'] != "" && $season_info['away'] != ""){
							$home = $season_info['home'];
							$away = $season_info['away'];
						}
                  		
						//if match delay set from feed then modify match schedule date time
						if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 1){
							$value['delay_minute'] = $season_info['delay_minute'];
							$value['delay_message'] = $season_info['delay_message'];
						}

						if(isset($value['delay_minute']) && $value['delay_minute'] > 0){
							$value['season_scheduled_date'] = date('Y-m-d H:i:s', strtotime('+'.$value['delay_minute'].' minutes', strtotime($value['season_scheduled_date'])));
						}

						if(isset($season_info['notify_by_admin']) && $season_info['notify_by_admin'] == 1){
							$value['custom_message'] = $season_info['custom_message'];
						}

						$temp_match_array = array(
                                            "league_id" 		=> $league['league_id'],
                                            "season_game_uid" 	=> $value['season_game_uid'],
                                            "year" 				=> $value['year'],
                                            "type" 				=> 'REG',
                                            "format" 			=> $value['format'],
                                            "feed_date_time" 	=> $value['feed_date_time'],
                                            "season_scheduled_date" => $value['season_scheduled_date'],
                                            "scheduled_date" 	=> $value['scheduled_date'],
                                            "home_uid" 			=> $home_uid, 
                                            "away_uid" 			=> $away_uid,
                                            "home" 				=> $home,
                                            "away" 				=> $away,
                                            "delay_minute" 		=> $value['delay_minute'],
                                            "delay_message" 	=> $value['delay_message'],
                                            "custom_message" 	=> $value['custom_message'],
                                            "scoring_alert"		=> (isset($value['scoring_alert'])) ? $value['scoring_alert'] : 0
                                    );
						$match_data[] = $temp_match_array;
					}
					if (!empty($match_data))
					{
						//echo "<pre>";print_r($match_data); continue;die;
						$this->replace_into_batch(SEASON, $match_data);
						//For primary key setting
						$this->set_auto_increment_key('season','season_id');
						echo "<br>League id " .$league['league_id']." Matches (seasons) inserted.<br>";
					}
				}	
			}
		}	
		exit();	
    }

     /**
     * function used for fetch all team from feed
     * @param int $sports_id
     * @return boolean
     */
    public function get_team($sports_id)
    {
    	$current_date = format_date();
		$this->db->select("league_id,league_uid, sports_id")
					->from(LEAGUE)
					->where("sports_id", $sports_id)
					->where("active", 1)
					->where("league_last_date >= ", $current_date)
					->order_by("league_id","DESC");
				$sql = $this->db->get();
		$league_data = $sql->result_array();
		//echo "<pre>";print_r( $league_data);die;
		if (!empty($league_data))
		{
			$team_img_arr = array();
			foreach ($league_data as $league)
			{
			   	$url = $this->api['api_url']."get_teams?league_uid=".$league['league_uid']."&token=".$this->api['access_token'];
				//echo $url ;die;
				$team_data = @file_get_contents($url);
				//echo "<pre>";print_r($league_data);continue;die;	
				if (!$team_data)
				{
					continue;
				}
				$team_array = @json_decode(($team_data), TRUE);
				//echo "<pre>";print_r($team_array);die;
				if(!empty($team_array['response']['data']))
				{
					//array that will contain team flag and jersey url from feed.
					$data  = array();
					foreach ($team_array['response']['data'] as $key => $value) 
					{
						// Prepare team Data
                        $data[] = array(
                                        "sports_id" 	=> $league['sports_id'],
                                        "team_abbr" 	=> $value['team_abbr'],
                                        "team_name" 	=> $value['team_name'],
                                        "display_team_abbr" => @$value['display_team_abbr'],
                                        "display_team_name" => @$value['display_team_name'],
                                        "feed_flag" 	=> $value['flag'],
                                        "feed_jersey" 	=> $value['jersey'],
                                        "team_uid"      => $value['team_uid']
                                	);

                        //if flag_url given from feed then add url in team img array
                        if(!empty($value['flag_url']))
                        {
                        	$team_img_arr[] = array('type'=>'flag','url'=>$value['flag_url']); 
                        }	

                        //if jersey_url given from feed then add url in team img array
                        if(!empty($value['jersey_url']))
                        {
                        	$team_img_arr[] = array('type'=>'jersey','url'=>$value['jersey_url']); 
                        }

                        
					}
					
					if (!empty($data))
					{
						//echo "<pre>";print_r($data);die;
						// Insert Team data 
						$this->replace_into_batch(TEAM, $data);
						//For primary key setting
						$this->set_auto_increment_key('team','team_id');
						echo "<br>League uid " . $league['league_uid'] . " Teams inserted.";
					}
				}		
			}

			//Images process
			if(!empty($team_img_arr))
			{	
				//echo "<pre>";print_r($team_img_arr);die;
				$this->process_team_image_data_from_feed($team_img_arr);
			}
		}	
		exit();	
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

	public function get_player($sports_id)
    {
		$rs = $this->db->select("L.league_id,L.league_uid,L.sports_id,S.season_game_uid", FALSE)
							->from(LEAGUE." AS L")
							->join(SEASON." AS S", "S.league_id = L.league_id", 'INNER')
							->where("L.active", 1)
							->where("L.sports_id", $sports_id)
							->where("S.season_scheduled_date >= ", format_date())
							->get();
		$league_data = $rs->result_array();
		
		//echo "<pre>";print_r( $league_data);die;
		if (!empty($league_data))
		{
			
			foreach ($league_data as $league)
			{
				$this->season_detail = $league;
			   	$url = $this->api['api_url']."get_players?league_uid=".$league['league_uid']."&match_id=".$league['season_game_uid']."&token=".$this->api['access_token'];
				$player_data = @file_get_contents($url);
				//echo "<pre>";print_r($player_data);continue;die;	
				if (!$player_data)
				{
					continue;
				}
				$player_array = @json_decode(($player_data), TRUE);
				//echo "<pre>";print_r($player_array);continue;die;
				if(!empty($player_array['response']['data']))
				{
					$pl_team_league_data = array();
					$data = array();
					$player_team = array();
					$match_player_uid = array();
					foreach ($player_array['response']['data'] as $key => $value) 
					{
						//echo "<pre>";print_r($value);die;
						// Prepare Player data
						$data[] = array(
											"player_uid" 	=> $value['player_uid'],
											"sports_id" 	=> $sports_id,
											"name" 	=> trim($value['full_name']),
										);
						$match_player_uid[] = $value['player_uid'];
						if(!isset($value['last_match_played'])){
							$value['last_match_played'] = 0;
						}
						// Prepare Player Team Relation Data
						$pt_key = $value['player_uid'].'|'.$sports_id.'|'.$league['season_game_uid'];
						$player_team[$pt_key] = array(
											"player_id" 		=> 0,
											"position" 			=> $value['position'],
											"is_deleted" 		=> 0,
											"salary" 			=> $value['salary'],
											"season_game_uid"	=> $league['season_game_uid'],
											"last_match_played"	=> $value['last_match_played'],
											"is_published"      => 1,
											"team_uid"			=> $value['team_uid']
										);
					}
					
					if (!empty($data))
					{
						//echo "<pre>";print_r($player_team);die;
						$this->replace_into_batch(PLAYER, $data);
						//For primary key setting
						$this->set_auto_increment_key('player','player_id');
						// Get all Team of this sports of current year to map team_id in Team_League relation
						$player_data = $this->get_player_team_data_by_game_id($league['season_game_uid'], $sports_id, $match_player_uid);
						//echo "<pre>";print_r($player_data);die;
						$player_team_data = array();
						$update_player_team_data = array();
						if (!empty($player_data))
						{
							foreach ($player_data as $player)
							{
								// Map team_id with Team_League data
								$player_team_arr = $player_team[$player['player_uid'].'|'.$player['sports_id'].'|'.$league['season_game_uid']];
								$player_team_arr['player_id'] = $player['player_id'];
								$player_team_data[] = $player_team_arr;
								
							}
						}
						// Insert Team League Data
						if (!empty($player_team_data))
						{
							$this->insert_ignore_into_batch(PLAYER_TEAM, $player_team_data);
							//For primary key setting
							$this->set_auto_increment_key('player_team','player_team_id');
						}

						//Update display name if its empty or null
						$this->db->where('display_name', NULL);
						$this->db->set('display_name','name',FALSE);
						$this->db->update(PLAYER);
				   		echo "<br>Players inserted for [<b>".$league['league_id'].' - '.$league['season_game_uid']."</b>]";
					}
				}		
			}
		}	
		exit();	
    }


    /**
     * [get_player_team_data_by_game_id :- GET GAME PLAYER TEAM FROM DATABASE FOR PARTICULAR GAME ID ]
     * @param  [type] $season_game_uid [description]
     * @return [type]          [description]
     */
	private function get_player_team_data_by_game_id($season_game_uid, $sports_id, $match_player_uid) 
    {
        $rs = $this->db->select("P.player_id, P.sports_id, P.player_uid,PT.is_published", FALSE)
                ->from(PLAYER . " AS P")
                ->join(PLAYER_TEAM . " AS PT", "PT.player_id = P.player_id AND PT.season_game_uid = '".$season_game_uid."' ", 'LEFT')
				->where("P.sports_id", $sports_id)
                ->where_in("P.player_uid", $match_player_uid)
                ->get();
        //echo $this->db->last_query(); die;
        $res = $rs->result_array();
        return $res;
    }


    /**
     * function used for get season list for score update
     * @param int $sports_id
     * @return array
     */
	public function get_season_match_for_score($sports_id = '')
	{
		$current_date_time = format_date();
		$this->db->select("S.home, S.home_uid, S.away, S.away_uid, S.year, S.week, S.season_game_uid, S.league_id,S.type, S.feed_date_time, S.season_scheduled_date, S.format, L.sports_id,L.league_abbr")
			->from(SEASON . " AS S")
			->join(LEAGUE . " AS L", "L.league_id = S.league_id", "left")
			->where("DATE_FORMAT ( S.season_scheduled_date ,'%Y-%m-%d %H:%i:%s' ) <='" . $current_date_time . "'");

		if(!empty($sports_id))
		{
			$this->db->where("L.sports_id", $sports_id);
		}
		$this->db->where("L.active", '1');
		$this->db->where("S.match_status", '0');
		$this->db->where_in("S.status", array(0,1,3));
		$this->db->group_by("S.season_game_uid");
		$sql = $this->db->get();
		$result = $sql->result_array();
		return $result;
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
				$url = $this->api['api_url']."get_scores?match_id=".$season_game_uid."&token=".$this->api['access_token'];
				//echo $url ;die;
				$match_data = @file_get_contents($url);
				if(!$match_data)
				{
					continue;
				}	
				
				$match_array = @json_decode(($match_data), TRUE);
				//echo "<pre>";print_r($match_array);die;
				$match_info = @$match_array['response']['match_info'];
				//echo "<pre>";print_r($match_info);die;
				if(!empty($match_info))
				{
					$scheduled_date = @$match_info['scheduled_date'];
					$match_status = @$match_info['status']; 
				
					//0-Not Started, 1-Live, 2-Completed, 3-Delay, 4-Canceled
					$status = 0;
					$status_overview = 0;
					$match_closure_date = "";
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
					}elseif($match_status == 2)
					{
						$status = 2;
						$status_overview = 4;
						$match_closure_date = format_date();
							
					}

					//if match closure date provided from feed then update this on client db
					//$season_update_array = array("status" => $status,"status_overview" => $status_overview,"score_data"=>$match_info['score']);
					$season_update_array = array("status" => $status,"status_overview" => $status_overview);
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

					
					//echo "<pre>";print_r($match_info);die;

					if(isset($match_info['batting_team_uid']))
					{
						$season_update_array['batting_team_uid'] = $match_info['batting_team_uid'];
					}	
					//echo "<pre>";print_r($season_update_array);die;	

					$this->db->where("league_id",$league_id);
					$this->db->where("season_game_uid",$season_game_uid);
					$this->db->update(SEASON,$season_update_array);

					echo "<pre>";  echo "Match [<b>$season_game_uid</b>] score updated";
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

    
}	