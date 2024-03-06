<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
class Crictech_model extends MY_Model
{
	private $api;
	public $db;
	public $season_detail = array();
	function __construct()
	{
		parent::__construct();
		$this->db	= $this->load->database('livefantasy_db', TRUE);
	}

	public function get_access_token()
	{
		$data = array(
			'username' => '',
			'password' => ''
		);
		$livefantasy_feed_url = '';
		$url = $livefantasy_feed_url.'/login';
		$post_data_json = json_encode($data);
        $header = array("Content-Type:application/json", "Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (ENVIRONMENT !== 'production'){
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }

        $output = curl_exec($ch);
        curl_close($ch);
        $return =  json_decode($output, true);
        if(@$return['token'] == '' || @$return['token'] == NULL)
        {
        	exit('Please check token');
        }else{
        	return $return['token'];	
        }	
        
    }


    private function get_feed_data($api_url)
    {
    	$token = $this->get_access_token();
    	//setup the request, you can also use CURLOPT_URL
		$ch = curl_init($api_url);
		// Returns the data/output as a string instead of raw data
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//Set your auth headers
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		   'Content-Type: application/json',
		   'Authorization: Bearer ' . $token
		   ));
		// get stringified data/output. See CURLOPT_RETURNTRANSFER
		$data = curl_exec($ch);
		// get info about the request
		$info = curl_getinfo($ch);
		// close curl resource to free up system resources
		curl_close($ch);
		return $data;
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

    	$api_url = LIVEFANTASY_FEED_URL."/getcompetition/1";
    	$league_data = $this->get_feed_data($api_url);
	    if (!$league_data)
		{
			exit;
		}
		$league_array = @json_decode(($league_data), TRUE);
		//echo "<pre>";print_r($league_array);die;
		if(!empty($league_array['competitionList']))
		{
			foreach ($league_array['competitionList'] as $key => $value) 
			{
				/*if(!$value['league_last_date'])
				{
					continue;
				}*/

				$data[] = array(
								"league_uid" 	=> @$value['competitionId'],
								"league_abbr" 	=> str_replace(" ", "", @$value['competitionName']),
								"league_name" 	=> @$value['competitionName'],
								"league_display_name" => @$value['competitionName'],
								//"league_schedule_date" => @$value['league_schedule_date'],
								//"league_last_date" => @$value['league_last_date'],
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
		//$league_data = $this->get_all_table_data("league_id,league_uid, sports_id", LEAGUE, array('sports_id' => $sports_id, "active" => '1',"league_last_date >= "=>$current_date));
		
		$this->db->select("league_id,league_uid,league_name,sports_id");
		$this->db->from(LEAGUE);
		$this->db->where("active", 1);
		$this->db->where("sports_id", $sports_id);
		//$this->db->where("league_uid",'770');
		$this->db->group_by("league_name");
		$this->db->order_by("league_uid","DESC");
		$query = $this->db->get();
		$league_data = $query->result_array();
		//echo "<pre>";print_r( $league_data);die;
		if (!empty($league_data))
		{
			foreach ($league_data as $league)
			{
			   	///getevent/{competitionId}
			   	$api_url = LIVEFANTASY_FEED_URL."/getevent/".$league['league_uid'];
    			$season_data = $this->get_feed_data($api_url);
				//$season_data = '{"competitionId":"4","eventList":[{"eventId":231,"eventName":"Islamabad United vs Peshawar Zalmi","countryName":"","venueName":"","venueLocation":"","startedAt":"2021-02-27T14:00:00Z","matchFormat":""},{"eventId":233,"eventName":"Multan Sultans vs Peshawar Zalmi","countryName":"","venueName":"","venueLocation":"","startedAt":"2021-02-23T14:00:00Z","matchFormat":""},{"eventId":250,"eventName":"Islamabad United vs Multan Sultans","countryName":"","venueName":"","venueLocation":"","startedAt":"2021-02-21T14:00:00Z","matchFormat":""},{"eventId":263,"eventName":"Lahore Qalandars vs Peshawar Zalmi","countryName":"","venueName":"","venueLocation":"","startedAt":"2021-02-21T09:00:00Z","matchFormat":""},{"eventId":266,"eventName":"Lahore Qalandars vs Quetta Gladiators","countryName":"","venueName":"","venueLocation":"","startedAt":"2021-02-22T14:00:00Z","matchFormat":""},{"eventId":267,"eventName":"Karachi Kings vs Islamabad United","countryName":"","venueName":"","venueLocation":"","startedAt":"2021-02-24T14:00:00Z","matchFormat":""},{"eventId":275,"eventName":"Lahore Qalandars vs Multan Sultans","countryName":"","venueName":"","venueLocation":"","startedAt":"2021-02-26T10:00:00Z","matchFormat":""},{"eventId":276,"eventName":"Peshawar Zalmi vs Quetta Gladiators","countryName":"","venueName":"","venueLocation":"","startedAt":"2021-02-26T15:00:00Z","matchFormat":""},{"eventId":277,"eventName":"Karachi Kings vs Multan Sultans","countryName":"","venueName":"","venueLocation":"","startedAt":"2021-02-27T09:00:00Z","matchFormat":""},{"eventId":309,"eventName":"Karachi Kings vs Quetta Gladiators","countryName":"","venueName":"","venueLocation":"","startedAt":"2021-02-20T15:00:00Z","matchFormat":""},{"eventId":323,"eventName":"US Presidential Election","countryName":"","venueName":"","venueLocation":"","startedAt":"2021-03-09T09:00:44.000044Z","matchFormat":""}]}';
				if (!$season_data)
				{
					continue;
				}
				$season_array = @json_decode(($season_data), TRUE);
				//echo "<pre>";print_r($season_array);die;
				if(!empty($season_array['eventList']))
				{
					$match_data = $temp_match_array	= array();
					foreach ($season_array['eventList'] as $key => $value) 
					{
						//The match format - Test, ODI, T20, T10 ( only v2 )
						//$api_scheduled_date = date("Y-m-d H:i:s",strtotime('+1 year 1 month',strtotime($value['startedAt'])));
						//echo "<pre>";print_r($value);//die;
						$api_scheduled_date = $value['startedAt'];
						$date = new DateTime($api_scheduled_date);
						$tz = new DateTimeZone(DEFAULT_TIME_ZONE);
						$date->setTimezone($tz);

						$match_scheduled_date	= $date->format('Y-m-d H:i:s');
						$match_date	= $date->format('Y-m-d');
						$match_year = $date->format('Y');
						//echo "<pre>";print_r($match_scheduled_date);die;
						if ($match_scheduled_date < format_date()) 
						{
                        	//continue;
                        }
                  

                        //https://api.crictech.io/v1/geteventinfo/6578
					   	$api_url = LIVEFANTASY_FEED_URL."/geteventinfo/".$value['eventId'];
		    			$fixture_detail_data = $this->get_feed_data($api_url);
		    			$fixture = @json_decode(($fixture_detail_data), TRUE);

                        $match_format = 3;
						if(strtoupper($fixture['matchFormat']) == 'ODI')
						{
							$match_format = CRICKET_ONE_DAY;
						}
						elseif(strtoupper($fixture['matchFormat']) == 'T20I' || 
								strtoupper($fixture['matchFormat']) == 'T20')
						{
							$match_format = CRICKET_T20;
						}
						elseif(strtoupper($fixture['matchFormat']) == 'T10')
						{
							$match_format = CRICKET_T10;
						}

						if($match_format == NULL || $match_format == '')
                        {
                        	continue;
                        }	

                        /*$team_names = explode("vs", @$value['eventName']);
                        if(count($team_names) < 2)
                        {
                        	continue;
                        }*/

                        //echo "<pre>";print_r($team_names);//die;
                        //$home_team_name = trim($team_names[0]);
                        //$away_team_name = trim($team_names[1]);

                        $home_team_name = trim(@$fixture['homeTeam']);
                        $away_team_name = trim(@$fixture['awayTeam']);
                        if($home_team_name == '' || $away_team_name == '')
                        {
                        	continue;
                        }
                        $home = str_split($home_team_name,3);
                        $home = strtoupper($home[0]);
                   		$away = str_split($away_team_name,3);
                   		$away = strtoupper($away[0]);
                   		
                   		$this->create_team($sports_id,$home,$home_team_name);
                   		$this->create_team($sports_id,$away,$away_team_name);
						$home_uid = $this->get_team_uid($sports_id,$home,$home_team_name);
               			$away_uid = $this->get_team_uid($sports_id,$away,$away_team_name);	
                   		//echo "<pre>";print_r($home_uid);die;

               			//for delay match
               			$value['delay_minute'] 		= NULL;
               			$value['delay_message'] 	= NULL;
               			$value['custom_message'] 	= NULL;
               			$season_info = $this->get_single_row("home,away,scoring_alert,delay_by_admin,notify_by_admin,delay_minute,delay_message,custom_message", SEASON, array("season_game_uid" => $value['eventId'],"league_id" => $league['league_id']));
               			//if match delay set from feed then modify match schedule date time
						if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 1){
							$value['delay_minute'] = $season_info['delay_minute'];
							$value['delay_message'] = $season_info['delay_message'];
						}

						if(isset($value['delay_minute']) && $value['delay_minute'] > 0){
							$match_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$value['delay_minute'].' minutes', strtotime($match_scheduled_date)));
						}

						if(isset($season_info['notify_by_admin']) && $season_info['notify_by_admin'] == 1){
							$value['custom_message'] = $season_info['custom_message'];
						}


						$temp_match_array = array(
                                            "league_id" 		=> $league['league_id'],
                                            "season_game_uid" 	=> $value['eventId'],
                                            "year" 				=> $match_year,
                                            "type" 				=> 'REG',
                                            "format" 			=> $match_format,
                                            "feed_date_time" 	=> $value['startedAt'],
                                            "season_scheduled_date" => $match_scheduled_date,
                                            "scheduled_date" 	=> $match_date,
                                            "home_uid" 			=> $home_uid, 
                                            "away_uid" 			=> $away_uid,
                                            "home" 				=> $home,
                                            "away" 				=> $away,
                                            "delay_minute" 		=> $value['delay_minute'],
                                            "delay_message" 	=> $value['delay_message'],
                                            "custom_message" 	=> $value['custom_message'],
                                            
                                    );
						$match_data[] = $temp_match_array;
					}
					if (!empty($match_data))
					{
						//echo "<pre>";print_r($match_data); die;
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


    private function get_team_uid($sports_id,$team_abbr,$team_name)
	{
		$sql = $this->db->select('T.team_uid')
				->from(TEAM . " AS T")
				->where('T.sports_id', $sports_id)
				->where('T.team_abbr', $team_abbr)
				->where('T.team_name', $team_name)
				->get();
		//echo $this->db->last_query();		
		$result = $sql->row('team_uid');
		return $result;
	}

     /**
     * function used for fetch all team from feed
     * @param int $sports_id
     * @return boolean
     */
    private function create_team($sports_id,$team_abbr,$team_name)
    {
    	$team_data[] = array(
                                "sports_id" 	=> $sports_id,
                                "team_abbr" 	=> $team_abbr,
                                "team_name" 	=> $team_name,
                                "display_team_abbr" => @$team_abbr,
                                "display_team_name" => @$team_name,
                               
                        	);
   		$this->replace_into_batch(TEAM, $team_data);
   		$this->set_auto_increment_key('team','team_id');
   		//update team uid
   		$this->db->where('team_uid', "");
		$this->db->set('team_uid','team_id',FALSE);
		$this->db->update(TEAM);
		return true;
    }


    

    
}	