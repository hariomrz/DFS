<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
class Goalserve_model extends MY_Model 
{
	public $position_array = array(
                                    "F" => "F",
                                    "G" => "G",
                                    "C" => "C",
                                );
	public $season_type = array("1"=>"REG", "2"=>"PRE", "3"=>"POST");
	public $default_salary = 8.5;
	public $time_zone = "America/New_York"; //For EST
    public $time_in_time_zone = "-05:00";   

	public function __construct() 
	{
		parent::__construct();
        $this->api = $this->get_ncaa_basketball_config_detail('goalserve');
	}

    
	/**
	 * [get_teams description]
	 * @param  [type] $league_id [description]
	 * @return [type]            [description]
	 */
	public function get_team($sports_id)
	{  
		$current_date = format_date();
        $league_data = $this->get_all_table_data("league_id,league_uid,league_abbr,sports_id", LEAGUE, array('sports_id' => $sports_id, "active" => '1'));
        //echo "<pre>";print_r( $league_data);die;
        if (!empty($league_data))
        {
            foreach ($league_data as $league)
            {
                //echo "<pre>";print_r($this->api);die;
        		$url = $this->api['api_url'].$this->api['subscription_key']."/bsktbl/".strtolower($league['league_abbr'])."-shedule";
                //echo $url;die;
                //http://www.goalserve.com/getfeed/xxxxxxxxxxxxxxxxx/bsktbl/nba-shedule
                $schedule_array = @file_get_contents($url);
                $schedule_array = xml2array($schedule_array);
                if (!$schedule_array||!is_array($schedule_array)){
                    exit;
                }
                if(!isset($schedule_array['shedules'])) exit;
                if(!isset($schedule_array['shedules']['matches'])) exit;
                $matches = $schedule_array['shedules']['matches'];
                if(!is_array($matches)) exit;

                $data = array();
                $team_data_league_array = array();
                $league_id = $league['league_id'];
                foreach ($matches as $key => $matchesDetail) 
                {
                    if(isset($matchesDetail['match'])&&is_array($matchesDetail['match']))
                    {
                        $match = $matchesDetail['match'];
                        foreach ($match as $key => $value) 
                        {
                            //echo "<pre>";print_r($value);die;
                            $abbr = @$this->create_team_abbr($value['hometeam']['attr']['name']);
                            if($abbr == '')
                            {
                                continue;
                            }    
                            $data[$value['hometeam']['attr']['id']] = array(
                                    'sports_id'     => $sports_id,
                                    'team_name'     => trim(@$value['hometeam']['attr']['name']),
                                    'team_abbr'     => $abbr,
                                    'display_team_name'     => trim(@$value['hometeam']['attr']['name']),
                                    'display_team_abbr'     => $abbr,
                                    'year'          => $this->api['year']
                                                                );

                                        $team_abbr[] = $abbr;
                        //prepair tem leageu data
                            $temp_team_league["team_id"]    = 0;
                            $temp_team_league["team_uid"]   = @$value['hometeam']['attr']['id'];
                            $temp_team_league["league_id"]  = $league_id;
                            $team_unique_id = $sports_id.'|'.trim(@$value['hometeam']['attr']['name']).'|'.$this->api['year'].'|'.$abbr;
                            $team_data_league_array[$team_unique_id] = $temp_team_league;

                            //for away team
                             $abbr = @$this->create_team_abbr($value['awayteam']['attr']['name']);
                            if($abbr == '')
                            {
                                continue;
                            }    
                            $data[$value['awayteam']['attr']['id']] = array(
                                    'sports_id'     => $sports_id,
                                    'team_name'     => trim(@$value['awayteam']['attr']['name']),
                                    'team_abbr'     => $abbr,
                                    'display_team_name'     => trim(@$value['awayteam']['attr']['name']),
                                    'display_team_abbr'     => $abbr,
                                    'year'          => $this->api['year']
                                                                );

                                        $team_abbr[] = $abbr;
                        //prepair tem leageu data
                            $temp_team_league["team_id"]    = 0;
                            $temp_team_league["team_uid"]   = @$value['awayteam']['attr']['id'];
                            $temp_team_league["league_id"]  = $league_id;
                            $team_unique_id = $sports_id.'|'.trim(@$value['awayteam']['attr']['name']).'|'.$this->api['year'].'|'.$abbr;
                            $team_data_league_array[$team_unique_id] = $temp_team_league;
                            
                        }
                    }
                }
                
                if(isset($data) && count($data) > 0)
                {
                    //echo "<pre>";print_r($data);die;
                    //echo "<pre>";print_r($team_data_league_array);die;
                    $this->replace_into_batch(TEAM, array_values($data));
                    //For primary key setting
                    $this->set_auto_increment_key('team','team_id');
                    // Get all Team of this sports of current year to map team_id in Team_League relation
                    $team_data = $this->get_all_teams($sports_id,$this->api['year'],$team_abbr );
                    //echo "<pre>";print_r($team_data);die;
                    $team_league_data = array();
                    if (!empty($team_data))
                    {
                        foreach ($team_data as $team)
                        {
                            // Map team_id with Team_League data
                            $team_unique_id = $team['sports_id'].'|'.$team['team_name'].'|'. $team['year'].'|'. $team['team_abbr'];
                            if (!empty($team_data_league_array[$team_unique_id]))
                            {
                                $team_league_arr = $team_data_league_array[$team_unique_id];
                                $team_league_arr['team_id'] = $team['team_id'];
                                $team_league_data[] = $team_league_arr;
                            }
                        }
                    }

                    // Insert Team League Data
                    if (!empty($team_league_data))
                    {
                        //echo "<pre>";print_r($team_league_data);die;
                        $this->replace_into_batch(TEAM_LEAGUE, $team_league_data);
                        //For primary key setting
                        $this->set_auto_increment_key('team_league','team_league_id');
                    }

                    echo "Teams inserted.";

                }    
            }
        }
     exit();             

	}


	private function create_team_abbr($team_name)
	{
	    $team_array = array('LAC' => 'Los Angeles Clippers','LAL' => 'Los Angeles Lakers', 'OKC' => 'Oklahoma City Thunder','PHX' => 'Phoenix Suns',
                            'POR' => 'Portland Trail Blazers','UTAH' => 'Utah Jazz','WSH' => 'Washington Wizards', 'NJ' => 'Brooklyn Nets');
	    $abbr = array_search($team_name, $team_array);
    	if($abbr) return $abbr; 
	    $word_count = str_word_count($team_name);
	    if ($word_count > 2) {
	        $te = explode(' ', $team_name);
	        $team_abbr = substr($te[0], 0, 1) . substr($te[1], 0, 1);
	    } else {
	        $te = explode(' ', $team_name);
	        if (strlen($te[0]) == 2) {
	            $team_abbr = substr($te[0], 0, 2) . substr($te[1], 0, 1);
	        } else {
	            $team_abbr = substr(strtok($te[0], " "), 0, 3);
	        }
	    }
	    return strtoupper($team_abbr);
	}



	private function get_team_abbr_by_league_id($sports_id, $league_id, $team_uid) 
    {
        $sql = $this->db->select('IFNULL(T.display_team_abbr,T.team_abbr) AS team_abbr', FALSE)
                ->from(TEAM . " AS T")
                ->join(TEAM_LEAGUE . " AS TL", "TL.team_id = T.team_id AND TL.league_id = $league_id AND TL.team_uid = $team_uid ", 'INNER')
                ->join(LEAGUE . " AS L", "L.league_id = TL.league_id AND L.active = 1 ", 'INNER')
                ->where('T.sports_id', $sports_id)
                ->where('T.year', $this->api['year'])
                ->get();
        //echo $this->db->last_query();     
        $result = $sql->row('team_abbr');
        return $result;
    }

    /*
	 * @Summary: This function for use fetch the seson data from third party API and store our local
     * database.
     * @access: public
     * @param:
     * @return:
     */
    public function get_season($sports_id)
    {
        $current_date = format_date();
        $league_data = $this->get_all_table_data("league_id,league_uid,league_abbr,sports_id", LEAGUE, array('sports_id' => $sports_id, "active" => '1'));
        //echo "<pre>";print_r( $league_data);die;
        if (!empty($league_data))
        {
            foreach ($league_data as $league)
            {
                $url = $this->api['api_url'].$this->api['subscription_key']."/bsktbl/".strtolower($league['league_abbr'])."-shedule";
                //echo $url;die;
                //http://www.goalserve.com/getfeed/xxxxxxxxxxxxxxxxx/bsktbl/nba-shedule
                $schedule_array = @file_get_contents($url);
                $schedule_array = xml2array($schedule_array);
                if (!$schedule_array||!is_array($schedule_array)){
                    exit;
                }
                if(!isset($schedule_array['shedules'])) exit;
                if(!isset($schedule_array['shedules']['matches'])) exit;
                $matches = $schedule_array['shedules']['matches'];
                if(!is_array($matches)) exit;

                $data = array();
                $league_id = $league['league_id'];
                foreach ($matches as $key => $matchesDetail) 
                {
                    if(isset($matchesDetail['match'])&&is_array($matchesDetail['match']))
                    {
                        $match = $matchesDetail['match'];
                        foreach ($match as $key => $matchValue) 
                        {
                            //echo "<pre>";print_r(trim($matchValue['attr']['status']));die;
                            $year                  = $this->api['year'];
                            $type                  = $this->api['season'];
                            $status                = @$matchValue['attr']['status']; 

                            if(!isset($matchValue['attr'])||!isset($matchValue['attr']['id'])||!$matchValue['attr']['id']) continue;
                            $season_game_uid = trim($matchValue['attr']['id']);

                            if(!isset($matchValue['attr'])||!isset($matchValue['attr']['formatted_date'])||!$matchValue['attr']['formatted_date']) continue;
                            $formatted_date = trim($matchValue['attr']['formatted_date']);

                            if(!isset($matchValue['attr'])||!isset($matchValue['attr']['time'])||!$matchValue['attr']['time']) continue;
                            $time = trim($matchValue['attr']['time']);

                            if($time=='TBA') $time = '';

                            $date_time = date('Y-m-d H:i:s', strtotime($formatted_date.$time));

                            @date_default_timezone_set($this->time_zone);
                            $scheduled_date_time = date('Y-m-d H:i:s', strtotime($date_time));
                            $temp_date = explode(" ", $scheduled_date_time);
                            $scheduled_date_time = $temp_date[0] . 'T' . $temp_date[1] . $this->time_in_time_zone;
                            @date_default_timezone_set(DEFAULT_TIME_ZONE);
                            $season_scheduled_date = date('Y-m-d H:i:s', strtotime($scheduled_date_time));
                            $scheduled_date = date('Y-m-d', strtotime($season_scheduled_date));

                            $dt = strtotime('-24 hours',strtotime(format_date()));
                            $dt = date('Y-m-d',$dt);
                            //echo "<pre>";
                            //$scheduled_date = date('Y-m-d', strtotime($match_date_time_utc));
                            if( $scheduled_date < $dt )
                            {
                                continue;
                            }

                            if(!isset($matchValue['hometeam'])||!isset($matchValue['hometeam']['attr'])||!isset($matchValue['hometeam']['attr']['name'])||!$matchValue['hometeam']['attr']['name']) continue;
                            //$home = $this->create_team_abbr(trim($matchValue['hometeam']['attr']['name']));
                            $home_uid = trim($matchValue['hometeam']['attr']['id']);

                            $home = $this->get_team_abbr_by_league_id($sports_id, $league_id, $home_uid);
                           
                            if(!isset($matchValue['awayteam'])||!isset($matchValue['awayteam']['attr'])||!isset($matchValue['awayteam']['attr']['name'])||!$matchValue['awayteam']['attr']['name']) continue;
                            //$away = $this->create_team_abbr(trim($matchValue['awayteam']['attr']['name']));
                            $away_uid = trim($matchValue['awayteam']['attr']['id']);
                            $away = $this->get_team_abbr_by_league_id($sports_id, $league_id, $away_uid);

                            $seasonDate['year']                  = $year;
                            $seasonDate['type']                  = $type;
                            //$seasonDate['status']                = $status;
                            $seasonDate['season_game_uid']       = $season_game_uid;
                            $seasonDate['feed_date_time']        = $scheduled_date_time;
                            $seasonDate['season_scheduled_date'] = $season_scheduled_date;
                            $seasonDate['scheduled_date']        = $scheduled_date;
                            $seasonDate['home']                  = $home;
                            $seasonDate['away']                  = $away;
                            $seasonDate['home_uid']              = $home_uid;
                            $seasonDate['away_uid']              = $away_uid;
                            $seasonDate['league_id']             = $league_id;
                            $data[] = $seasonDate;
                        }
                    }
                }
                
                if(isset($data) && count($data) > 0)
                {
                   	//echo "<pre>";print_r($data);die;
                   	$this->replace_into_batch(SEASON, $data);
        		   	//For primary key setting
                    $this->set_auto_increment_key('season', 'season_id');
            		//$this->set_season_week($league_id, $this->api['season'], $this->api['year']);
                    //$this->update_season_week($league_id, $this->api['season'], $this->api['year']);
        			//Update all league for last date
                    $this->db->where('league_id', $league_id);
                    $this->db->set("league_schedule_date"," (SELECT season_scheduled_date FROM ".$this->db->dbprefix(SEASON)." WHERE league_id = ".$league_id."   ORDER BY season_scheduled_date ASC LIMIT 1)", FALSE);
                    $this->db->set("league_last_date"," (SELECT season_scheduled_date FROM ".$this->db->dbprefix(SEASON)."  WHERE league_id = ".$league_id."    ORDER BY season_scheduled_date DESC LIMIT 1)", FALSE);
                    $this->db->update(LEAGUE);
                    echo "Season Inserted!";
                    
                } 
            } 
        } 
        exit();      
    }

    protected function set_season_week($league_id,$season_type,$season_year)
    {
       define('MASTER_LEAGUE_WEEK', 'master_league_week');
       $this->load->model('common_model');
        $sql = "SELECT 
                    MAX(scheduled_date) AS max_date ,MIN(scheduled_date) AS min_date 
                FROM 
                    ".$this->db->dbprefix(SEASON)." 
                WHERE
                    league_id = $league_id
                AND
                    type = '".$season_type."'
               ";
        $rs = $this->db->query($sql);
        $result = $rs->row_array();     
        //AND  year = '".$season_year."'  
        //echo "<pre>";print_r($result);die;   
        //echo "<pre>";print_r($result['min_date']);
        //echo "<pre>";print_r($result['max_date']);
        //delete week from table
        if(!empty($result))
        {
            $this->common_model->delete(SEASON_WEEK,array('league_id' => $league_id,'type'=>$season_type));
            
            $start_day = '';
            $end_day = '';
            //get start & end day in week from DB
            $query = $this->db->get_where(MASTER_LEAGUE_WEEK,array('league_id'=>$league_id));
            if($query->num_rows() > 0)
            {
                $res = $query->row_array();
                $start_day = $res['start_week_day'];
                $end_day = $res['end_week_day'];
            }
            
            $predate = strtotime($result['min_date']);
            $nextdate = strtotime($result['max_date']);
            //echo strtoupper(date('l', $predate));die;
            if(strtoupper(date('l', $predate)) != strtoupper($start_day))
                $previous_date = date('Y-m-d', strtotime('previous '.$start_day, strtotime($result['min_date'])));
            else
                $previous_date = $result['min_date'];
            if(strtoupper(date('l', $nextdate)) != strtoupper($end_day))        
                $next_date = date('Y-m-d', strtotime('next '.$end_day, strtotime($result['max_date'])));
            else
                $next_date = $result['max_date'];

            $startdate = strtotime($previous_date);
            $enddate = strtotime($next_date);
            
            $weeks = array();
            
            while ($startdate < $enddate)
            {  
                $weeks[] = date('W', $startdate); 
                $startdate += strtotime('+1 week', 0);
            }
            
            $season_week                    = 1;
            $season_week_start_date_time    = date('Y-m-d 00:00:00',strtotime($previous_date));
            $season_week_end_date_time      = date('Y-m-d 23:59:59', strtotime('next '.$end_day, strtotime($previous_date)));
            $season_week_close_date_time    = date('Y-m-d 23:59:59', strtotime('next '.$end_day, strtotime($previous_date)));
            //echo "<pre>";print_r($weeks);die; 
            for($i=1;$i <= count($weeks);$i++)
            {
                //echo "<pre>";
                //echo $season_week.'-'.$season_week_start_date_time.'-'.$season_week_end_date_time.'-'.$season_week_close_date_time;
                $data = array(
                                'type'                          => trim($season_type),
                                'season_week'                   => trim($season_week),
                                'season_week_start_date_time'   => trim($season_week_start_date_time),
                                'season_week_end_date_time'     => trim($season_week_end_date_time),
                                'season_week_close_date_time'   => trim($season_week_close_date_time),
                                'league_id'                     => $league_id,
                                'year'                     => $season_year
                            );  
                
                //Insert data into database
                $this->common_model->insert_data(SEASON_WEEK, $data);
                
                $season_week                    = $season_week+1;
                $season_week_start_date_time    = date('Y-m-d 00:00:00', strtotime('next '.$start_day, 
                                                                         strtotime($season_week_start_date_time)));
                $season_week_end_date_time      = date('Y-m-d 23:59:59', strtotime('next '.$end_day, 
                                                                         strtotime($season_week_end_date_time)));
                $season_week_close_date_time    = date('Y-m-d 23:59:59', strtotime('next '.$end_day, 
                                                                         strtotime($season_week_close_date_time)));
            }
        }   
    }  

    protected function update_season_week($league_id,$season_type,$season_year)
    {
        $sql = "SELECT 
                    DATE_FORMAT(season_week_start_date_time,'%Y-%m-%d') AS start_date,
                    DATE_FORMAT(season_week_end_date_time,'%Y-%m-%d') AS end_date 
                FROM 
                    ".$this->db->dbprefix(SEASON_WEEK)."    
                WHERE
                    league_id = $league_id
                AND
                    type = '".$season_type."'
                ORDER BY 
                    season_week     
               ";
        $rs = $this->db->query($sql);
        $result = $rs->result_array();
        //echo "<pre>";print_r($result);    
        if(!empty($result))
        {
            foreach($result as $key=>$value)
            {
                $sql = "SELECT 
                            season_game_uid
                        FROM 
                            ".$this->db->dbprefix(SEASON)." 
                        WHERE
                            league_id = $league_id  
                        AND
                            scheduled_date BETWEEN  '".$value['start_date']."' AND '".$value['end_date']."'
                        AND
                            type = '".$season_type."'
                        AND
                            year = '".$season_year."'
                       ";
                $rs2 = $this->db->query($sql);
                $result2 = $rs2->result_array();
                //echo "<pre>";print_r($result2);   
                if(!empty($result2))
                {
                    $season_game_uid = array();
                    for($i=0;$i<count($result2);$i++)
                    {
                        $season_game_uid[] =  $result2[$i]['season_game_uid'];
                    }
                    $i = $key+1;

                    $update_sql = "UPDATE ".$this->db->dbprefix(SEASON)." SET week=".$i." 
                                    WHERE 
                                        league_id = $league_id  
                                    AND
                                        type = '".$season_type."'
                                    AND
                                        year = '".$season_year."'
                                    AND 
                                        season_game_uid in (".implode( ',' , array_map( function( $n ){ return '\''.$n.'\''; } , $season_game_uid ) ).")";
                    $rs = $this->db->query($update_sql);
                }
            }
        }
    }




    public function get_players($sports_id)
    {   
        $day = 15;
        $next_date_time = date("Y-m-d H:i:s",strtotime('+'.$day.' day',strtotime(format_date())));
        $sql = $this->db->select('S.season_game_uid,S.home_uid,S.away_uid,
                                L.league_uid,L.league_id')
                ->from(SEASON . " AS S")
                ->join(LEAGUE . " AS L", "L.league_id = S.league_id AND L.sports_id = $sports_id ", 'INNER')
                ->where('L.sports_id', $sports_id)
                ->where('S.season_scheduled_date >', format_date())
                ->where('S.season_scheduled_date <', $next_date_time)
                //->where('S.season_game_uid','148997')
                ->get();
        //echo $this->db->last_query();     
        $current_game = $sql->result_array();
        //echo '<pre>Live Match : ';print_r($current_game);die;
        if (!empty($current_game))
        {
            foreach ($current_game as $key => $game) 
            {
                $teams_id_array = array($game['home_uid'],$game['away_uid']);
                
                $player_array = array();
                $player_league_array = array();
                $player_uids = array();
                $temp_player = array();
                $temp_player_league = array();
                $league_id = $game['league_id'];
                    
                foreach ($teams_id_array as $team_uid) 
                {
                    $team_league_id = $this->get_team_league_id($sports_id,$league_id,$team_uid);
                    //echo "<pre>";print_r($team_league_id);continue;die;
                    if($team_league_id == '' || $team_league_id == '0')
                    {
                        continue;
                    }
                    $url = $this->api['api_url'].$this->api['subscription_key']."/bsktbl/".$team_uid."_rosters";
                    //echo $url;die;
                    $player_data = @file_get_contents($url);
                    if (!$player_data)
                    {
                        continue;
                    }
                    $player_array = xml2array($player_data);
                    //echo "<pre>";print_r($player_array);die;
                    $team_id = @$player_array['team']['attr']['id'];
                    $team_player = @$player_array['team']['player'];
                    //echo "<pre>";print_r($team_position);die;

                    if (!empty($team_player))
                    {
                        if(!array_key_exists('0', $team_player))
                        {
                            $temp = $team_player;
                            $team_player = array();
                            $team_player[0] = $temp; 
                        }
                        // echo "<pre>";print_r($team_player);die;
                        
                        foreach ($team_player as $key => $player)
                        {
                            //echo "<pre>";print_r($player['attr']);die;
                            $player_name = $player['attr']['name'];
                            $position    = $player['attr']['position'];
                            //echo "<pre>";print_r($position);
                            //echo "<pre>";print_r($this->position_array[$position]); die;
                            if (in_array($position, $this->position_array) && $position !='' ) 
                            {
                                $data[] = array(
                                                    "player_uid"        => trim($player['attr']['id']),
                                                    "sports_id"         => trim($sports_id),
                                                    "full_name"         => (trim($player_name)),
                                                    "first_name"        => (trim(strtok($player_name,' '))),
                                                    "nick_name"        => (trim(strtok($player_name,' '))),
                                                    "last_name"         => (trim(substr($player_name, strrpos($player_name, ' ') + 1)))                    
                                                 );

                                
                                $player_uids[] = $player['attr']['id'];
                                $player_array[] = $data;
                                //prepait team league data
                                $unique_id                    = $player['attr']['id'].'|'.$sports_id.'|'.$game['season_game_uid'];
                                $temp_player_league["player_id"]        = $player['attr']['id'];
                                $temp_player_league["team_league_id"]   = $team_league_id;
                                $temp_player_league["position"]         = $this->position_array[$position];
                                $temp_player_league["jersey_number"]    = intval($player['attr']['number']);
                                $temp_player_league["season_game_uid"]  = $game['season_game_uid'];
                                $temp_player_league["player_status"]  = 1;
                                $temp_player_league["is_deleted"]     = 0;
                                $temp_player_league["feed_verified"]     = 1;
                                $temp_player_league["salary"]       = $this->default_salary;
                                $player_league_array[$unique_id] =  $temp_player_league;
                            }
                        }
                    }
                }
                //echo "<pre>";print_r($player_league_array);die;
                //echo "<pre>";print_r($player_league_array);continue;die;
                if (isset($data) && count($data) > 0) 
                {
                    $this->replace_into_batch(PLAYER, $data);
                    //For primary key setting
                    $this->set_auto_increment_key('player','player_id');    
                    // Get all Team of this sports of current year to map team_id in Team_League relation
                    $player_data = $this->get_all_table_data("player_id, sports_id, player_uid", PLAYER, array('sports_id' => $sports_id));
                    //echo "<pre>";print_r($player_data);die;
                    $player_team_data = array();
                    $temp_player_ids = array();
                    if (!empty($player_data))
                    {
                        foreach ($player_data as $player)
                        {
                            $player_uid = $player['player_uid'].'|'.$player['sports_id'].'|'.$game['season_game_uid'];
                            // Map team_id with Team_League data                        
                            if(!empty($player_league_array[$player_uid]))
                            {
                                $player_league_array[$player_uid]['player_id'] = $player['player_id'];
                                $player_team_data[] = $player_league_array[$player_uid];
                                $temp_player_ids[] = $player['player_id'];
                            }
                        }
                    }
                    

                    // Insert Team League Data
                    if (!empty($player_team_data))
                    {
                        //echo "<pre>";print_r($player_team_data);die;
                        //for delete all old player of team
                        $this->db->where('team_league_id',$team_league_id);
                        $this->db->update(PLAYER_TEAM,array("is_deleted" => "1"));
                        
                        //Save ignore players(if new player comes it will insert
                        $this->insert_ignore_into_batch(PLAYER_TEAM, $player_team_data);
                        //For primary key setting
                        $this->set_auto_increment_key('player_team','player_team_id');
                        //for active players of team
                        $this->db->where('team_league_id',$team_league_id);
                        $this->db->where_in('player_id', $temp_player_ids);
                        $this->db->update(PLAYER_TEAM,array("is_deleted" => "0"));
                    
                        $update_data = array_map(function($val){ 
                                                    unset($val['position']);
                                                    unset($val['salary']);  
                                                    return $val;
                                                     },  $player_team_data
                                                    );
                        //echo "<pre>";print_r($update_data);die;
                        $this->replace_into_batch(PLAYER_TEAM, $update_data);
                        //For primary key setting
                        $this->set_auto_increment_key('player_team','player_team_id');
                    }
                }    
            }
        }            
            //Update display name if its empty or null
            $this->db->where('display_name', NULL);
            $this->db->set('display_name','full_name',FALSE);
            $this->db->update(PLAYER);
            echo "Player inserted";exit();

    }

    private function get_team_league_id($sports_id,$league_id,$team_uid)
    {
        $sql = $this->db->select('TL.team_league_id')
                ->from(TEAM . " AS T")
                ->join(TEAM_LEAGUE . " AS TL", "TL.team_id = T.team_id AND TL.league_id = $league_id AND TL.team_uid = $team_uid ", 'INNER')
                ->join(LEAGUE . " AS L", "L.league_id = TL.league_id AND L.active = 1 ", 'INNER')
                ->where('T.sports_id', $sports_id)
                ->where('T.year', $this->api['year'])
                ->get();
        //echo $this->db->last_query();     
        $result = $sql->row('team_league_id');
        return $result;
    }

    private function _get_teams($league_id)
	{
		$sql = $this->db->select("IFNULL(T.display_team_abbr,T.team_abbr) AS team_abbr, TL.team_league_id,TL.team_uid")
						->from(TEAM." T")
						->join(TEAM_LEAGUE." TL","TL.team_id=T.team_id")
						->where("TL.league_id",$league_id)
						->where("T.year",$this->api["year"])
						->get();
		//echo $this->db->last_query();die;				
		return $sql->result_array();
	}
	
	


	private function _get_all_players($league_id)
	{
		$sql = $this->db->select("P.player_uid,PT.position")
						->from(PLAYER_TEAM." PT")
						->join(TEAM_LEAGUE." TL","TL.team_league_id = PT.team_league_id","INNER")
						->join(PLAYER." P","P.player_id = PT.player_id","INNER")
						->where("TL.league_id",$league_id)
						->get();
		return array_combine(array_column($sql->result_array(), "player_uid"), array_column($sql->result_array(), "position"));
	}

	
    public function get_scores($sports_id)
    {
        $custom_where = array("S.match_status"=>0);//To check scores update is automatic/feed not manual
        $current_teams  = $this->get_current_season_match($sports_id,"",$custom_where);
        //echo '<pre>';print_r($current_teams);die;
        if(empty($current_teams))
        {   
            exit("No Live Games");
        } 

        $date_array = array();
        foreach ($current_teams as $key => $value)
        {
            $date_array[date('d.m.Y', strtotime(substr($value['feed_date_time'],0,10)))] = '';
            $date_array[date('d.m.Y', strtotime($value['feed_date_time']))] = '';
        }

        $this->load->helper('queue');
        foreach ($date_array as $current_date_time => $value)
        {
            $url = $this->api['api_url'].$this->api['subscription_key']."/bsktbl/".$this->api['league_abbr']."-scores?date=".$current_date_time;
            //echo $url;die;
            $score_data = @file_get_contents($url);
            if (!$score_data)
            {
                exit;
            }
            $score_data = xml2array($score_data);

            if(!empty($current_teams))
            {
                foreach ($current_teams AS $current_team)
                {
                    $matchs = @$score_data['scores']['category']['match'];
                    if(isset($matchs) && !empty($matchs))
                    {
                        if(!array_key_exists('0', $matchs))
                        {
                            $temp = $matchs;
                            $matchs = array();
                            $matchs[0] = $temp; 
                        }

                        foreach ($matchs as $key => $match)
                        {
                            //echo "<pre>";print_r($match);die;
                            $season_game_uid  = $match['attr']['id'];
                            @date_default_timezone_set($this->time_zone);
                            $scheduled = date('Y-m-d H:i:s', strtotime($match['attr']['formatted_date'] . ' ' . $match['attr']['time']));
                            @date_default_timezone_set(DEFAULT_TIME_ZONE);
                            $scheduled              = $scheduled;
                            $status                 = @$match['attr']['status'];
                            $week                   = $current_team['week'];

                            $home_team_arr = $match['hometeam']['attr'];
                            $away_team_arr = $match['awayteam']['attr'];
                            $home_uid = $current_team['home_uid'];
                            $away_uid = $current_team['away_uid'];
                            $hometeam_score = @$home_team_arr['totalscore'];
                            $awayteam_score = @$away_team_arr['totalscore'];

                            $hometeam_id =  $home_team_arr['id'];
                            $awayteam_id =  $away_team_arr['id'];
                            $hometeam_point =  $hometeam_score;
                            $awayteam_point =  $awayteam_score;
                            
                            $player_array = array();
                            if($current_team['season_game_uid'] == $season_game_uid)
                            {
                                $team_list = array('hometeam','awayteam');
                                foreach ($team_list as $key => $team)
                                {
                                    //For starters
                                    $starters_player = @$match['player_stats'][$team]['starters']['player'];
                                    if(isset($starters_player) && !empty($starters_player))
                                    {
                                        if(!array_key_exists('0', $starters_player))
                                        {
                                            $temp = $starters_player;
                                            $starters_player = array();
                                            $starters_player[0] = $temp; 
                                        } 
                                        //echo "<pre>";print_r($starters_player);die;
                                        foreach($starters_player AS $key => $value)
                                        {
                                            $two_pointers_made = floatval(@$value['attr']['field_goals_made']) - floatval(@$value['attr']['threepoint_goals_made']);
                                            if(!isset($two_pointers_made) || $two_pointers_made <= 0 || $two_pointers_made == ""){
                                                $two_pointers_made = 0;
                                            }
                                            //echo "<pre>";print_r($value);die;
                                            $team_id = $team. "_id";
                                            $team_points = $team. "_point";
                                            $temp_player_array = array();
                                            $temp_player_array["league_id"] = $league_id;
                                            $temp_player_array["season_game_uid"] = $season_game_uid;
                                            $temp_player_array["week"] = $week;
                                            $temp_player_array["scheduled_date"] = $scheduled;
                                            $temp_player_array['home_uid'] = $home_uid;
                                            $temp_player_array['away_uid'] = $away_uid;
                                            $temp_player_array['status'] = $status;
                                            $temp_player_array["team_uid"] = $$team_id;
                                            $temp_player_array["team_points"] = $$team_points;
                                            $temp_player_array["scoring_type"] = "starters";
                                            $temp_player_array["player_uid"] = @$value['attr']['id'];
                                            $temp_player_array["minutes"] = @$value['attr']['minutes'];
                                               

                                            /*======= Unused scoring parameters ======*/
                                                /*$temp_player_array["three_pointers_made"] = @$value['attr']['threepoint_goals_made'];
                                                $temp_player_array["three_pointers_attempted"] = @$value['attr']['threepoint_goals_attempts'];
                                                $temp_player_array["two_pointers_made"] = $two_pointers_made;
                                                $temp_player_array["offensive_rebounds"] = @$value['attr']['offence_rebounds'];
                                                $temp_player_array["defensive_rebounds"] = @$value['attr']['defense_rebounds'];
                                                $temp_player_array["personal_fouls"] = @$value['attr']['personal_fouls'];*/
                                            /*======= Unused scoring parameters ======*/

                                            $temp_player_array["field_goals_made"] = floatval(@$value['attr']['field_goals_made']);
                                            $temp_player_array["field_goals_attempted"] = floatval(@$value['attr']['field_goals_attempts']);
                                            //calculation for field goals missed
                                            $temp_player_array["field_goals_missed"] = 0;
                                            if(!empty($temp_player_array["field_goals_attempted"]))
                                            {
                                                $temp_player_array["field_goals_missed"] = floatval(@$value['attr']['field_goals_attempts']) - floatval(@$value['attr']['field_goals_made']);
                                            } 

                                          
                                           $temp_player_array["free_throws_made"] = floatval(@$value['attr']['freethrows_goals_made']);
                                            $temp_player_array["free_throws_attempted"] = floatval(@$value['attr']['freethrows_goals_attempts']);

                                            //calculation for field goals missed
                                            $temp_player_array["free_throws_missed"] = 0;
                                            if(!empty($temp_player_array["free_throws_attempted"]))
                                            {
                                                $temp_player_array["free_throws_missed"] = floatval(@$value['attr']['freethrows_goals_attempts']) - floatval(@$value['attr']['freethrows_goals_made']);
                                            }

                                            $temp_player_array["rebounds"] = floatval(@$value['attr']['total_rebounds']);
                                            $temp_player_array["assists"] = floatval(@$value['attr']['assists']);
                                            $temp_player_array["steals"] = floatval(@$value['attr']['steals']);
                                            $temp_player_array["blocked_shots"] = floatval(@$value['attr']['blocks']);
                                            $temp_player_array["turnovers"] = floatval(@$value['attr']['turnovers']);
                                            $temp_player_array["points"] = floatval(@$value['attr']['points']);
                                            $temp_player_array['updated_at'] = format_date();

                                            $player_array[] = $temp_player_array;
                                        }
                                    }

                                    //For bench
                                    $bench_player = @$match['player_stats'][$team]['bench']['player'];
                                    if(isset($bench_player) && !empty($bench_player))
                                    {
                                        if(!array_key_exists('0', $bench_player))
                                        {
                                            $temp = $bench_player;
                                            $bench_player = array();
                                            $bench_player[0] = $temp; 
                                        }

                                        foreach($bench_player AS $key => $value)
                                        {
                                            $two_pointers_made = floatval(@$value['attr']['field_goals_made']) - floatval(@$value['attr']['threepoint_goals_made']);
                                            if(!isset($two_pointers_made) || $two_pointers_made <= 0 || $two_pointers_made == ""){
                                                $two_pointers_made = 0;
                                            }

                                            $team_id = $team. "_id";
                                            $team_points = $team. "_point";
                                            $temp_player_array = array();
                                            $temp_player_array["league_id"] = $league_id;
                                            $temp_player_array["season_game_uid"] = $season_game_uid;
                                            $temp_player_array["week"] = $week;
                                            $temp_player_array["scheduled_date"] = $scheduled;
                                            $temp_player_array['home_uid'] = $home_uid;
                                            $temp_player_array['away_uid'] = $away_uid;
                                            $temp_player_array['status'] = $status;
                                            $temp_player_array["team_uid"] = $$team_id;
                                            $temp_player_array["team_points"] = $$team_points;
                                            $temp_player_array["scoring_type"] = "bench";
                                            $temp_player_array["player_uid"] = @$value['attr']['id'];
                                            $temp_player_array["minutes"] = @$value['attr']['minutes'];
                                            
                                            /*======= Unused scoring parameters ======*/
                                                /*$temp_player_array["three_pointers_made"] = @$value['attr']['threepoint_goals_made'];
                                                $temp_player_array["three_pointers_attempted"] = @$value['attr']['threepoint_goals_attempts'];
                                                $temp_player_array["two_pointers_made"] = $two_pointers_made;
                                                $temp_player_array["offensive_rebounds"] = @$value['attr']['offence_rebounds'];
                                                $temp_player_array["defensive_rebounds"] = @$value['attr']['defense_rebounds'];
                                                $temp_player_array["personal_fouls"] = @$value['attr']['personal_fouls'];*/
                                            /*======= Unused scoring parameters ======*/

                                            $temp_player_array["field_goals_made"] = floatval(@$value['attr']['field_goals_made']);
                                            $temp_player_array["field_goals_attempted"] = floatval(@$value['attr']['field_goals_attempts']);

                                            //calculation for field goals missed
                                            $temp_player_array["field_goals_missed"] = 0;
                                            if(!empty($temp_player_array["field_goals_attempted"]))
                                            {
                                                $temp_player_array["field_goals_missed"] = floatval(@$value['attr']['field_goals_attempts']) - floatval(@$value['attr']['field_goals_made']);
                                            }
                                            
                                            $temp_player_array["free_throws_made"] = floatval(@$value['attr']['freethrows_goals_made']);
                                            $temp_player_array["free_throws_attempted"] = floatval(@$value['attr']['freethrows_goals_attempts']);
                                            //calculation for field goals missed
                                            $temp_player_array["free_throws_missed"] = 0;
                                            if(!empty($temp_player_array["free_throws_attempted"]))
                                            {
                                                $temp_player_array["free_throws_missed"] = floatval(@$value['attr']['freethrows_goals_attempts']) - floatval(@$value['attr']['freethrows_goals_made']);
                                            }
                                            
                                            
                                            $temp_player_array["rebounds"] = floatval(@$value['attr']['total_rebounds']);
                                            $temp_player_array["assists"] = floatval(@$value['attr']['assists']);
                                            $temp_player_array["steals"] = floatval(@$value['attr']['steals']);
                                            $temp_player_array["blocked_shots"] = floatval(@$value['attr']['blocks']);
                                            $temp_player_array["turnovers"] = floatval(@$value['attr']['turnovers']);
                                            
                                            $temp_player_array["points"] = floatval(@$value['attr']['points']);
                                            $temp_player_array['updated_at'] = format_date();

                                            $player_array[] = $temp_player_array;
                                        }
                                    }
                                }

                                //echo "<pre>";print_r($player_array);die;
                                if(!empty($player_array))
                                {
                                    $this->replace_into_batch(GAME_STATISTICS_NCAA_BASKETBALL, $player_array);
                                }

                                //for update match score
                                if($hometeam_score == ""){
                                    $hometeam_score = 0;
                                }
                                if($awayteam_score == ""){
                                    $awayteam_score = 0;
                                }
                                
                                $season_update_arr = array();
                                $season_update_arr['status'] = $current_team['status'];
                                $season_update_arr['feed_status'] = @$match['attr']['status'];
                                $season_update_arr['home_score']  = $hometeam_score;
                                $season_update_arr['away_score']  = $awayteam_score;
                                $final_match_status = explode("/",@$match['attr']['status']);
                                $final_match_status = $final_match_status['0'];
                                $feed_final_status_arr = array("Final/1","Final/2","Final/3","Final/4 ","Final/5","Final/6","Final/7","Final/8","Final/9","Final/10","Final/11","Final/12","Final/13","Final/14","Final/15","Final/OT","Final/2OT");
                                if(($final_match_status == 'Full-time' || $final_match_status == "Final" || in_array($final_match_status,$feed_final_status_arr) ))
                                {  
                                    $player_count = count($this->get_game_statistics_by_game_id($season_game_uid,$sports_id,$league_id));
                                    if($player_count >= 14)
                                    {
                                        $season_update_arr['status'] = 2;
                                        $season_update_arr['status_overview'] = 4;
                                        $season_update_arr['match_closure_date'] = format_date();
                                    }
                                }

                                $team_score_data = array(
                                                            "home_score" => $hometeam_score,
                                                            "away_score"=> $awayteam_score
                                                        );
                                $season_update_arr['score_data'] = json_encode($team_score_data);

                               // echo '<pre>';print_r($season_update_arr);echo $league_id." ".$season_game_uid." <br>";
                                $this->db->where('league_id', $current_team['league_id']);
                                $this->db->where('season_game_uid', $season_game_uid);
                                $this->db->update(SEASON, $season_update_arr);

                                $notify_node_data = array(
                                    "action" => 'update_score', 
                                    "league_id" => $current_team['league_id'], 
                                    "season_game_uid" => $season_game_uid, 
                                    "season_scheduled_date" => $current_team['season_scheduled_date'],
                                    "home_uid" => $home_uid,
                                    "home" => $current_team['home'],
                                    "away_uid" => $away_uid,
                                    "away" => $current_team['away'],
                                    "score_data" => $season_update_arr['score_data'],
                                    "status" => $season_update_arr['status']
                                );
                                add_data_in_queue($notify_node_data,'notify_node');

                                echo "<pre>";echo "Score inserted for game id: ".$season_game_uid." <br>";
                            }
                        }
                    }
                }
            }
        }
        exit();
    }

    /**
     * [get_game_statistics_by_game_id :- GET GAME STATICS FROM DATABASE FOR PARTICULAR GAME ID ]
     * @param  [type] $season_game_uid [description]
     * @return [type]          [description]
     */
    private function get_game_statistics_by_game_id($season_game_uid,$sports_id,$league_id)
    {
        $rs = $this->db->select("GSB.*,PT.position", FALSE)
                ->from(GAME_STATISTICS_NCAA_BASKETBALL . " AS GSB")
                ->join(PLAYER . " AS P", "P.player_uid = GSB.player_uid AND P.sports_id = $sports_id ", 'INNER')
                ->join(PLAYER_TEAM . " AS PT", "PT.player_id = P.player_id AND PT.season_game_uid = $season_game_uid ", 'INNER')
                ->where("GSB.season_game_uid", $season_game_uid)
                ->where("GSB.league_id", $league_id)
                ->group_by("P.player_uid")
                ->get();
        //echo $this->db->last_query(); die;
        $res = $rs->result_array();
        return $res;
    }


    //Calculate player score after score ad in database through API
    /**
     * @Summary: This function for use calculate player fantasy score with the help of fetch actual player score fron API
     *           or football NFL
     * database.
     * @access: public
     * @param:
     * @return:
     */
    public function save_calculated_scores($sports_id)
    {
        // Get All Live Games List 
        $current_game = $this->get_current_season_match($sports_id);
        //echo "<pre>"; print_r($current_game);die;
        if (!empty($current_game))
        {
            //print_r($formulas);
            foreach ($current_game as $season_game)
            {   
                // Get All Scoring Rules
                $season_game_uid        = $season_game['season_game_uid'];
                $league_id              = $season_game['league_id'];
                $all_player_scores      = array();
                $all_player_testing_var = array();
                
                // Get Match Scoring Statistics to Calculate Fantasy Score
                $game_stats = $this->get_game_statistics_by_game_id($season_game_uid,$sports_id,$league_id);
                //echo "<pre>";print_r($game_stats);die;
                if (!empty($game_stats))
                {
                    $formulas = $this->get_scoring_rules($sports_id, $season_game['format']);
                    //echo "<pre>"; print_r($formulas);die;
                    foreach ($game_stats as $player_stats)
                    {
                        
                        if(empty($formulas))
                        {
                            continue;
                        }
                        $normal_score       = 0;
                       
                        $score              = 0;
                        $each_player_score = array(
                                    "season_game_uid"       => $player_stats['season_game_uid'],
                                    "player_uid"            => $player_stats['player_uid'],
                                    "week"                  => $player_stats['week'],
                                    "scheduled_date"        => $player_stats['scheduled_date'],
                                    "league_id"             => $league_id,
                                    "normal_score"          =>  0,
                                     "score"                =>  0
                        );
                        //echo "<pre>";print_r($each_player_score);continue;
                        $normal_testing_var  = array();
                        /* ####### NORMAL SCORING RULE ########### */
                       

                        //field goals missed : FIELD_GOALS_MISSED
                        if ($player_stats['field_goals_missed'] > 0)
                        {
                            $normal_score = $normal_score + ($player_stats['field_goals_missed'] * $formulas['normal']['FIELD_GOALS_MISSED'] );
                            $normal_testing_var['FIELD_GOALS_MISSED'] = ( $player_stats['field_goals_missed'] * $formulas['normal']['FIELD_GOALS_MISSED'] );
                        }
                        //free_throws_missed : FREE_THROWS_MISSED
                        if ($player_stats['free_throws_missed'] > 0)
                        {
                            $normal_score = $normal_score + ($player_stats['free_throws_missed'] * $formulas['normal']['FREE_THROWS_MISSED'] );
                            $normal_testing_var['FREE_THROWS_MISSED'] = ( $player_stats['free_throws_missed'] * $formulas['normal']['FREE_THROWS_MISSED'] );
                        }

                        //rebounds : REBOUNDS
                        if ($player_stats['rebounds'] > 0)
                        {
                            $normal_score = $normal_score + ($player_stats['rebounds'] * $formulas['normal']['REBOUNDS'] );
                            $normal_testing_var['REBOUNDS'] = ( $player_stats['rebounds'] * $formulas['normal']['REBOUNDS'] );
                        }
                        //assists 
                        if ($player_stats['assists'] > 0)
                        {
                            $normal_score = $normal_score + ($player_stats['assists'] * $formulas['normal']['ASSISTS'] );
                            $normal_testing_var['ASSISTS'] = ( $player_stats['assists'] * $formulas['normal']['ASSISTS'] );
                        }
                        
                        //blocked_shots
                        if ($player_stats['blocked_shots'] > 0)                   
                        {                           
                            $normal_score = $normal_score + ($player_stats['blocked_shots'] * $formulas['normal']['BLOCKED_SHOT'] );
                            $normal_testing_var['BLOCKED_SHOT'] = ( $player_stats['blocked_shots'] * $formulas['normal']['BLOCKED_SHOT'] );
                        }
                        //steals
                        if ($player_stats['steals'] > 0)
                        {
                            $normal_score = $normal_score + ($player_stats['steals'] * $formulas['normal']['STEALS'] );
                            $normal_testing_var['STEALS'] = ( $player_stats['steals'] * $formulas['normal']['STEALS'] );
                        }
                        //turnovers
                        if ($player_stats['turnovers'] > 0)
                        {
                            $normal_score = $normal_score + ($player_stats['turnovers'] * $formulas['normal']['TURNOVERS'] );
                            $normal_testing_var['TURNOVERS'] = ( $player_stats['turnovers'] * $formulas['normal']['TURNOVERS'] );
                        }

                        //each points
                        if ($player_stats['points'] > 0)
                        {
                            $normal_score = $normal_score + ($player_stats['points'] * $formulas['normal']['EACH_POINT'] );
                            $normal_testing_var['EACH_POINT'] = ( $player_stats['points'] * $formulas['normal']['EACH_POINT'] );
                        }
                        //echo $normal_score;die;
                        
                        $each_player_score['normal_score'] = floatval($normal_score);
                        $all_player_testing_var[$player_stats['player_uid']]['normal'] = $normal_testing_var;
                                                
                        if (array_key_exists($player_stats['player_uid'], $all_player_scores) && array_key_exists('normal_score', $all_player_scores[$player_stats['player_uid']]))
                        {
                            $each_player_score['normal_score'] = $all_player_scores[$player_stats['player_uid']]['normal_score'] = $all_player_scores[$player_stats['player_uid']]['normal_score'] + $each_player_score['normal_score'];
                        }
                        
                        $all_player_scores[$player_stats['player_uid']] = $each_player_score;
                        $all_player_scores[$player_stats['player_uid']]['break_down'] = json_encode($all_player_testing_var[$player_stats['player_uid']]);
                    }
                }// End  of  Empty of game_ stats
                //echo "<pre>";print_r(array_values($all_player_scores)); echo "</pre> <HR>";die;
                if(!empty($all_player_scores))
                {
                    //Update score first zero then update
                    $this->db->where('season_game_uid', $season_game_uid);
                    $this->db->where('league_id', $league_id);
                    $this->db->update(GAME_PLAYER_SCORING,array("normal_score"=>"0","bonus_score"=>"0","score"=>"0","break_down"=>NULL,"final_break_down"=>NULL));
                    $this->save_player_scoring($all_player_scores);
                    echo "Calculate fantasy Point for Match [<b>$season_game_uid</b>]<br>";
                }
            }// End of Team loop  
        } // End of Empty team check
        exit();
    }



    public function save_calculated_scores_by_match_id($sports_id,$league_id,$season_game_uid)
    {
        ## Get Current season matches list
        $season_game = $this->get_season_match_details($season_game_uid,$league_id);
        //echo "<pre>"; print_r($current_game);die;
        if (empty($season_game))
        {
            return true;
        }
              
        // Get All Scoring Rules
        $season_game_uid        = $season_game['season_game_uid'];
        $league_id              = $season_game['league_id'];
        $all_player_scores      = array();
        $all_player_testing_var = array();
        
        // Get Match Scoring Statistics to Calculate Fantasy Score
        $game_stats = $this->get_game_statistics_by_game_id($season_game_uid,$sports_id,$league_id);
        //echo "<pre>";print_r($game_stats);die;
        if (!empty($game_stats))
        {
            $formulas = $this->get_scoring_rules($sports_id, $season_game['format']);
            //echo "<pre>"; print_r($formulas);die;
            foreach ($game_stats as $player_stats)
            {
                
                if(empty($formulas))
                {
                    continue;
                }
                $normal_score       = 0;
               
                $score              = 0;
                $each_player_score = array(
                            "season_game_uid"       => $player_stats['season_game_uid'],
                            "player_uid"            => $player_stats['player_uid'],
                            "week"                  => $player_stats['week'],
                            "scheduled_date"        => $player_stats['scheduled_date'],
                            "league_id"             => $league_id,
                            "normal_score"          =>  0,
                             "score"                =>  0
                );
                //echo "<pre>";print_r($each_player_score);continue;
                $normal_testing_var  = array();
                /* ####### NORMAL SCORING RULE ########### */
               

                //field goals missed : FIELD_GOALS_MISSED
                if ($player_stats['field_goals_missed'] > 0)
                {
                    $normal_score = $normal_score + ($player_stats['field_goals_missed'] * $formulas['normal']['FIELD_GOALS_MISSED'] );
                    $normal_testing_var['FIELD_GOALS_MISSED'] = ( $player_stats['field_goals_missed'] * $formulas['normal']['FIELD_GOALS_MISSED'] );
                }
                //free_throws_missed : FREE_THROWS_MISSED
                if ($player_stats['free_throws_missed'] > 0)
                {
                    $normal_score = $normal_score + ($player_stats['free_throws_missed'] * $formulas['normal']['FREE_THROWS_MISSED'] );
                    $normal_testing_var['FREE_THROWS_MISSED'] = ( $player_stats['free_throws_missed'] * $formulas['normal']['FREE_THROWS_MISSED'] );
                }

                //rebounds : REBOUNDS
                if ($player_stats['rebounds'] > 0)
                {
                    $normal_score = $normal_score + ($player_stats['rebounds'] * $formulas['normal']['REBOUNDS'] );
                    $normal_testing_var['REBOUNDS'] = ( $player_stats['rebounds'] * $formulas['normal']['REBOUNDS'] );
                }
                //assists 
                if ($player_stats['assists'] > 0)
                {
                    $normal_score = $normal_score + ($player_stats['assists'] * $formulas['normal']['ASSISTS'] );
                    $normal_testing_var['ASSISTS'] = ( $player_stats['assists'] * $formulas['normal']['ASSISTS'] );
                }
                
                //blocked_shots
                if ($player_stats['blocked_shots'] > 0)                   
                {                           
                    $normal_score = $normal_score + ($player_stats['blocked_shots'] * $formulas['normal']['BLOCKED_SHOT'] );
                    $normal_testing_var['BLOCKED_SHOT'] = ( $player_stats['blocked_shots'] * $formulas['normal']['BLOCKED_SHOT'] );
                }
                //steals
                if ($player_stats['steals'] > 0)
                {
                    $normal_score = $normal_score + ($player_stats['steals'] * $formulas['normal']['STEALS'] );
                    $normal_testing_var['STEALS'] = ( $player_stats['steals'] * $formulas['normal']['STEALS'] );
                }
                //turnovers
                if ($player_stats['turnovers'] > 0)
                {
                    $normal_score = $normal_score + ($player_stats['turnovers'] * $formulas['normal']['TURNOVERS'] );
                    $normal_testing_var['TURNOVERS'] = ( $player_stats['turnovers'] * $formulas['normal']['TURNOVERS'] );
                }

                //each points
                if ($player_stats['points'] > 0)
                {
                    $normal_score = $normal_score + ($player_stats['points'] * $formulas['normal']['EACH_POINT'] );
                    $normal_testing_var['EACH_POINT'] = ( $player_stats['points'] * $formulas['normal']['EACH_POINT'] );
                }
                //echo $normal_score;die;
                
                $each_player_score['normal_score'] = floatval($normal_score);
                $all_player_testing_var[$player_stats['player_uid']]['normal'] = $normal_testing_var;
                                        
                if (array_key_exists($player_stats['player_uid'], $all_player_scores) && array_key_exists('normal_score', $all_player_scores[$player_stats['player_uid']]))
                {
                    $each_player_score['normal_score'] = $all_player_scores[$player_stats['player_uid']]['normal_score'] = $all_player_scores[$player_stats['player_uid']]['normal_score'] + $each_player_score['normal_score'];
                }
                
                $all_player_scores[$player_stats['player_uid']] = $each_player_score;
                $all_player_scores[$player_stats['player_uid']]['break_down'] = json_encode($all_player_testing_var[$player_stats['player_uid']]);
            }
        }// End  of  Empty of game_ stats
        //echo "<pre>";print_r(array_values($all_player_scores)); echo "</pre> <HR>";die;
        if(!empty($all_player_scores))
        {
            //Update score first zero then update
            $this->db->where('season_game_uid', $season_game_uid);
            $this->db->where('league_id', $league_id);
            $this->db->update(GAME_PLAYER_SCORING,array("normal_score"=>"0","bonus_score"=>"0","score"=>"0","break_down"=>NULL,"final_break_down"=>NULL));
            $this->save_player_scoring($all_player_scores);
            $this->save_player_scoring($all_player_scores);
            //echo "Calculate fantasy Point for Match [<b>$season_game_uid</b>]<br>";
        }
            
       
        return true;
    }

    /* Save individual player score on table based on game id */
    protected function save_player_scoring($player_score)
    {
        $table_value = array();
        $sql = "REPLACE INTO " . $this->db->dbprefix(GAME_PLAYER_SCORING) . " (season_game_uid, player_uid, week, scheduled_date, normal_score,  score, league_id, break_down)
                            VALUES ";

        foreach ($player_score as $player_unique_id => $value)
        {
            $main_score = $value['normal_score'] ;

            $str = " ('" . $value['season_game_uid'] . "','" . $value['player_uid'] . "','" . $value['week'] . "','" . $value['scheduled_date'] . "','" . $value['normal_score'] . "','" . $main_score . "','" . $value['league_id'] . "','" . $value['break_down'] . "' )";

            $table_value[] = $str;
        }

        $sql .= implode(", ", $table_value);

        $this->db->query($sql);
    }
	

}

/* End of file  */
