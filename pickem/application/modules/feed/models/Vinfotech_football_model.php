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
		$this->db	= $this->load->database('db_pickem', TRUE);
		$this->api = $this->get_sports_config_detail('football','vinfotech');
		
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
		//echo "<pre>";print_r($league_array);die;
		if($league_array['status'] == 'error')
		{
			exit($league_array['response']);
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
								"sports_id" => $sports_id,
								"league_uid" 	=> @$value['league_uid'],
								"league_abbr" 	=> @$value['league_abbr'],
								"league_name" 	=> @$value['league_name'],
								"display_name" => @$value['league_display_name'],
								"start_date" => @$value['league_schedule_date'],
								"end_date" => @$value['league_last_date'],
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
	                //echo "<br> ".$url." <br>" ;continue;
	                $team_data = @file_get_contents($url);
	                $team_array = @json_decode(($team_data), TRUE);
	                //echo "<pre>";print_r($team_data);die;	
	                if($team_array['status'] == 'error') 
	                {
	                    exit($team_array['response']);
	                }
	                

	                //echo "<pre>";print_r($team_array);continue;die;
	                if (!empty($team_array['response']['data'])) 
	                {
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
					->from(LEAGUE ." AS L")
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
			$team_cache = 'team_'.$sports_id;
			$team_ids = $this->get_cache_data($team_cache);
			//echo "cache - <pre>";print_r($team_ids);die;
			if(empty($team_ids))
			{
				$team_ids = $this->get_team_id_with_team_uid($sports_id);
				$this->set_cache_data($team_cache,$team_ids,REDIS_24_HOUR);
				//echo "DB -<pre>";print_r($team_ids);die;
			}		
            //echo "<pre>";print_r($team_ids);die;
			foreach ($league_data as $league)
			{
			   	$url = $this->api['api_url']."get_season?league_uid=".$league['league_uid']."&token=".$this->api['access_token'];
				//echo $url ;die;
				$season_data = @file_get_contents($url);
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
                       /*
                   		$season_info = $this->get_single_row("delay_by_admin,notify_by_admin,delay_minute,delay_message,custom_message", SEASON, array("season_game_uid" => $value['season_game_uid'],"league_id" => $league['league_id']));
						
                  		
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
						}*/

						$season_k = $league['league_id'].'_'.$value['season_game_uid'];	

						$temp_match_array[$season_k] = array(
                                            "league_id" 		=> $league['league_id'],
                                            "season_game_uid" 	=> $value['season_game_uid'],
                                            "year" 				=> $value['year'],
                                            "type" 				=> 'REG',
                                            //"format" 			=> $value['format'],
                                            "feed_date_time" 	=> $value['feed_date_time'],
                                            "scheduled_date" => $value['season_scheduled_date'],
                                            "home_id" 			=> $home_id, 
                                            "away_id" 			=> $away_id,
                                            
                                            //"delay_minute" 		=> $value['delay_minute'],
                                            //"delay_message" 	=> $value['delay_message'],
                                            //"custom_message" 	=> $value['custom_message'],
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
					//echo "<pre>";print_r($team_ids);die;
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
					
					if(!empty($match_info))
					{
						$match_status = @$match_info['status']; 
						//0-Not Started, 1-Live, 2-Completed, 3-Delay, 4-Canceled
						$status = 0;
						$match_closure_date = "";
						$winning_team_id = 0;
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
						}elseif($match_status == 2 )
						{
							$status = 2;
							$match_closure_date = format_date();
						}

						//if match closure date provided from feed then update this on client db
						$season_update_array = array("status" => $status);
						
						if(isset($match_closure_date) && $match_closure_date != "")
						{
							$season_update_array['match_closure_date'] = $match_closure_date;
						}
						//echo "<pre>";print_r($match_info);die;	
						$score = json_decode(@$match_info['score_data'],TRUE);
						//echo "<pre>";print_r($score);die;	
						if(isset($score['away_score']) && isset($score['home_score']))
						{
							if($score['home_score'] > $score['away_score'])
							{
								$winning_team_uid = $match_info['home_uid'];
							}
							if($score['away_score'] > $score['home_score'])
							{
								$winning_team_uid = $match_info['away_uid'];
							}
							if(isset($winning_team_uid) && $winning_team_uid != '')	
							{	
								$season_update_array['winning_team_id'] = @$team_ids[$winning_team_uid];
							}	
						}

						if(isset($match_info['score_data']) && $match_info['score_data'] != "" && $match_info['score_data'] != "[]")
						{
							$season_update_array['score_data'] = $match_info['score_data'];
						}

						//echo "<pre>";print_r($season_update_array);die;	

						$this->db->where("league_id",$league_id);
						$this->db->where("season_id",$season_id);
						$this->db->update(SEASON,$season_update_array);
						//Foll of wicket inofrmation save in table 	
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
		            if(isset($status) && $status == 2){
						$this->load->helper('queue');
						$server_name = get_server_host_name();
						$content = array();
						$content['url'] = $server_name."/pickem/cron/tournament/update_tournament_score/".$season_id;
						add_data_in_queue($content, 'pickem_cron');
					}
		        }		
			}
		}
		return;		
    }

	
}	