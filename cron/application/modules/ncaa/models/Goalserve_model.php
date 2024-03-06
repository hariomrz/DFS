<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Goalserve_model extends MY_Model {

    public $position_array = array("QB","WR","RB");
    public $api_credential = array();

    public $time_zone = "America/New_York"; //For EST
    public $time_in_time_zone = "-04:00";   


    public function __construct() 
    {
        parent::__construct();
        $this->api = $this->get_ncaa_config_detail('goalserve');
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
                //echo "<pre>";print_r( $league);die;
                $this->api = $this->get_ncaa_config_detail('goalserve');
                $this->api = $this->api[$league['league_abbr']];
                
                $url = $this->api['api_url'].$this->api['subscription_key']."/football/".$this->api['league_abbr']."-shedule";
                //echo $url;die;
                $shedule_data = @file_get_contents($url);
                if (!$shedule_data){
                    exit;
                }
                $shedule_array = xml2array($shedule_data);
                 $team_data_league_array = array();
                 $data = array();
                if (isset($shedule_array['shedules']))
                {
                    $shedules = $shedule_array['shedules'];
                     
                    if (isset($shedules['tournament']))
                    {
                        $tournaments = $shedules['tournament'];
                        
                        if(!array_key_exists('0', $tournaments))
                        {
                            $temp = $tournaments;
                            $tournaments = array();
                            $tournaments[0] = $temp; 
                        }
                        //echo "<pre>";print_r($tournament['attr']['name']);die;
                        foreach ($tournaments as $tournament)
                        {
                           //echo "<pre>";print_r($tournament['attr']['name']);die;
                           if(in_array($tournament['attr']['name'], $this->api['stage_name']))
                            {
                                $weeks = $tournament['week'];
                                $attr = $tournament['attr'];
                                break;
                            }
                        }
                        
                        if(!in_array($tournament['attr']['name'], $this->api['stage_name']))
                        {
                            exit;
                        }  
                        //echo "<pre>";print_r($weeks);die;
                        if(!array_key_exists('0', $weeks))
                        {
                            $temp = $weeks;
                            $weeks = array();
                            $weeks[0] = $temp; 
                        }
                        foreach ($weeks as $week)
                        {
                            echo "<pre>";
                            if(isset($week['matches']))
                            {
                                $matchesArr = $week['matches'];     
                                if(!array_key_exists('0', $matchesArr))
                                {
                                    $temp = $matchesArr;
                                    $matchesArr = array();
                                    $matchesArr[0] = $temp; 
                                }
                                foreach ($matchesArr as $matches)
                                {
                                    $matcharr = $matches['match'];
                                    if(!array_key_exists('0', $matcharr))
                                    {
                                        $temp = $matcharr;
                                        $matcharr = array();
                                        $matcharr[0] = $temp; 
                                    }
                                    //echo "<pre>";print_r($matcharr);die;
                                    foreach ($matcharr as $key => $value) 
                                    {
                                        $abbr = $this->create_team_abbr($value['hometeam']['attr']['name']);
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
                        $temp_team_league["league_id"]  = $league['league_id'];
                        $team_unique_id = $sports_id.'|'.trim(@$value['hometeam']['attr']['name']).'|'.$this->api['year'].'|'.$abbr;
                        $team_data_league_array[$team_unique_id] = $temp_team_league;
                                            }
                                    
                                }
                            }

                        }
                    }
                    
                }
                
                if(isset($data) && count($data) > 0)
                {
                    //echo "<pre>";print_r($data);die;
                    //echo "<pre>";print_r($team_data_league_array);die;
                    $this->replace_into_batch(TEAM, array_values($data) );
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
                            $team_unique_id = $sports_id.'|'.$team['team_name'].'|'. $team['year'].'|'. $team['team_abbr'];
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

                    echo "<pre>";echo "Teams inserted for league: ".$league['league_abbr'];
                                        
                }

            }
        }
        exit();        
    }


    private function create_team_abbr($team_name)
    {
        $team_array = array('LAC' => 'Los Angeles Chargers','LAR' => 'Los Angeles Rams',"NYG"=>"New York Giants","NYJ"=>"New York Jets","WSH"=>"Washington Redskins");
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
                $this->api = $this->get_ncaa_config_detail('goalserve');
                $this->api = $this->api[$league['league_abbr']];
                $league_id = $league['league_id'];
                $url = $this->api['api_url'].$this->api['subscription_key']."/football/".$this->api['league_abbr']."-shedule";
                //echo $url;die;
                //http://www.goalserve.com/getfeed/f379c35e113a4c70b4dd18d241cfdb74/football/nfl-shedule
                $shedule_data = @file_get_contents($url);
                if (!$shedule_data){
                    exit;
                }
                $shedule_array = xml2array($shedule_data);
                
                if (isset($shedule_array['shedules']))
                {
                    $shedules = $shedule_array['shedules'];
                     
                    if (isset($shedules['tournament']))
                    {
                        $tournaments = $shedules['tournament'];
                        
                        if(!array_key_exists('0', $tournaments))
                        {
                            $temp = $tournaments;
                            $tournaments = array();
                            $tournaments[0] = $temp; 
                        }
                        //echo "<pre>";print_r($tournaments);die;
                        foreach ($tournaments as $tournament)
                        {
                            if(in_array($tournament['attr']['name'], $this->api['stage_name']))
                            {
                                $weeks = $tournament['week'];
                                $attr = $tournament['attr'];
                                break;
                            }
                        }
                        
                        if(!in_array($tournament['attr']['name'], $this->api['stage_name']))
                        {
                            exit;
                        }  
                        
                        if(!array_key_exists('0', $weeks))
                        {
                            $temp = $weeks;
                            $weeks = array();
                            $weeks[0] = $temp; 
                        }
                        foreach ($weeks as $week)
                        {
                            if($week['attr']['name'] == "Hall of Fame Weekend")
                            {
                                continue;
                            }    
                            //echo "<pre>";
                            if(isset($week['matches']))
                            {
                                $matchesArr = $week['matches'];     
                                if(!array_key_exists('0', $matchesArr))
                                {
                                    $temp = $matchesArr;
                                    $matchesArr = array();
                                    $matchesArr[0] = $temp; 
                                }
                                foreach ($matchesArr as $matches)
                                {
                                    $matcharr = $matches['match'];
                                    if(!array_key_exists('0', $matcharr))
                                    {
                                        $temp = $matcharr;
                                        $matcharr = array();
                                        $matcharr[0] = $temp; 
                                    }
                                    //echo "<pre>";print_r($matcharr);die;
                                    foreach($matcharr as $match)
                                    {
                                       if($match['attr']['time']=='TBA' or $match['attr']['time']=='')
                                        {
                                            //$match['attr']['time'] = '00.00.00';
                                            continue;
                                        }
                                        if(!trim($match['attr']['contestID'])) continue;
                                        
                                        $home_uid = trim($match['hometeam']['attr']['id']);
                                        $home = $this->get_team_abbr_by_league_id($sports_id, $league_id, $home_uid);
                           
                                        $away_uid = trim($match['awayteam']['attr']['id']);
                                        $away = $this->get_team_abbr_by_league_id($sports_id, $league_id, $away_uid);

                                        
                                        $sdate                 = $match['attr']['formatted_date'] . " " . $match['attr']['time'];
                                        @date_default_timezone_set($this->time_zone);
                                        $scheduled_date_time = date('Y-m-d H:i:s', strtotime($sdate));
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

                                        if(!isset($match['hometeam'])||!isset($match['hometeam']['attr'])||!isset($match['hometeam']['attr']['name'])||!$match['hometeam']['attr']['name']) continue;

                                        
                                        $data[] = array(
                                        "league_id"                 => $league_id,
                                        "season_game_uid"           => trim($match['attr']['contestID']),
                                        "year"                      => $this->api['year'],
                                        "type"                      => trim($this->api['season']),
                                        "week"                      => 0,
                                        "feed_date_time"            => $scheduled_date_time,
                                        "season_scheduled_date"     => $season_scheduled_date,
                                        "scheduled_date"            => $scheduled_date,
                                        "home"                      => trim($home),
                                        "away"                      => trim($away),
                                        "home_uid"                  => trim($home_uid),
                                        "away_uid"                  => trim($away_uid),
                                        "api_week"                  => trim(str_replace("Week ", "", $week['attr']['name']))
                                                        );
                                    }
                                }
                            }

                        }
                    }
                    
                }
                
                if(isset($data) && count($data) > 0)
                {
                    //echo "<pre>";print_r($data);die;
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
                    
                }
                echo "<pre>";echo "Season insert for league: ".$league['league_abbr'];
            }
        }
        exit();

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



    /**
     * @Summary: This function for use fetch the player data from third party API and store our local
     * database.
     * @access: public
     * @param:
     * @return:
     */
     
    public function get_players($sports_id)
    {   
        $day = 15;
        $next_date_time = date("Y-m-d H:i:s",strtotime('+'.$day.' day',strtotime(format_date())));
        $sql = $this->db->select('S.season_game_uid,S.home_uid,S.away_uid,
                                L.league_uid,L.league_id,L.league_abbr')
                ->from(SEASON . " AS S")
                ->join(LEAGUE . " AS L", "L.league_id = S.league_id AND L.sports_id = $sports_id ", 'INNER')
                //->where('S.league_id', $league_id)
                ->where('S.season_scheduled_date >', format_date())
                ->where('S.season_scheduled_date <', $next_date_time)
                //->where('S.season_game_uid','86375')
                ->get();
        //echo $this->db->last_query();     
        $current_game = $sql->result_array();
        //echo '<pre>Live Match : ';print_r($current_game);die;
        if (!empty($current_game))
        {
            foreach ($current_game as $key => $game) 
            {
                $league_abbr = $game['league_abbr'];
                $league_id = $game['league_id'];
                $teams_id_array = array($game['home_uid'],$game['away_uid']);

                $player_array = array();
                $player_league_array = array();
                $player_uids = array();
                $temp_player = array();
                $temp_player_league = array();
                foreach ($teams_id_array as $team_uid) 
                {
                    $team_league_id = $this->get_team_league_id($sports_id,$league_id,$team_uid,$league_abbr);
                    //echo "<pre>";print_r($team_uid);die;
                    if($team_league_id == '' || $team_league_id == '0')
                    {
                        continue;
                    }

                    $this->api = $this->get_ncaa_config_detail('goalserve');
                    $this->api = $this->api[$league_abbr];
                    //echo "<pre>";print_r($this->api);die;
                    $url = $this->api['api_url'].$this->api['subscription_key']."/football/".$team_uid."_rosters";
                    //echo $url."<br>";die;
                    $player_data = @file_get_contents($url);
                    if (!$player_data)
                    {
                        continue;
                    }
                    //echo $url;die;
                    $player_array = xml2array($player_data);
                    //echo "<pre>";print_r($player_array);die;
                    $team_id = @$player_array['team']['attr']['id'];
                    $team_position = @$player_array['team']['position'];
                    //echo "<pre>";print_r($team_position);die;
                    
                    if (!empty($team_position))
                    {
                        if(!array_key_exists('0', $team_position))
                        {
                            $temp = $team_position;
                            $team_position = array();
                            $team_position[0] = $temp; 
                        }

                        foreach ($team_position as $key => $position)
                        {
                            
                            if (!empty($position['player']))
                            {
                                $players = $position['player'];

                                if(!array_key_exists('0', $players))
                                {
                                    $temp = $players;
                                    $players = array();
                                    $players[0] = $temp; 
                                }
                                
                                foreach ($players as $player)
                                {
                                    //for salary calculation 
                                    /*
                                    $salarycap = intval(preg_replace('~[($,)]~', '', @$player['attr']['salarycap']));
                                    if($salarycap < 4000000)
                                    {
                                        $salary = 7;
                                    }elseif($salarycap >= 4000000 && $salarycap < 8000000 )
                                    {
                                        $salary = 8;
                                    }elseif($salarycap >= 8000000 && $salarycap < 120000000 )
                                    {
                                        $salary = 9;
                                    }elseif($salarycap >= 120000000 && $salarycap < 160000000 )
                                    {
                                        $salary = 10;
                                    }elseif($salarycap >= 160000000 && $salarycap < 200000000 )
                                    {
                                        $salary = 11;
                                    }elseif($salarycap >= 200000000 && $salarycap < 240000000 )
                                    {
                                        $salary = 12;
                                    }elseif($salarycap >= 240000000 && $salarycap < 280000000 )
                                    {
                                        $salary = 13;
                                    }elseif($salarycap >= 280000000 )
                                    {
                                        $salary = 14;
                                    }
                                    */
                                   // end for salary calculation
                                    //echo "<pre>";print_r($player['attr']);die;
                                    $player_name = $player['attr']['name'];
                                    $position = $player['attr']['position'];
                                    if($position == 'P' || $position == 'PK')
                                    {
                                        $position = "K";
                                    }    
                                    
                                   // echo "<pre>";print_r($position);die;
                                    if (in_array($position, $this->position_array) && $position !='' ) 
                                    {
                                        $data[] = array(
                                            "player_uid"        => trim($player['attr']['id']),
                                            "sports_id"         => trim($sports_id),
                                            "full_name"         => (trim($player_name)),
                                            "first_name"        => (trim(strtok($player_name,' '))),
                                            "last_name"         => (trim(substr($player_name, strrpos($player_name, ' ') + 1)))                    
                                                         );

                                        
                                        $player_uids[] = $player['attr']['id'];
                                        $player_array[] = $data;
                                        //prepait team league data

                                        $unique_id      = $player['attr']['id'].'|'.$sports_id.'|'.$game['season_game_uid'];
                                        $temp_player_league["player_id"]        = $player['attr']['id'];
                                        $temp_player_league["team_league_id"]   = $team_league_id;
                                        $temp_player_league["position"]         = $position;
                                        $temp_player_league["jersey_number"]    = intval($player['attr']['number']);
                                        //$temp_player_league["player_status"]    = 1;
                                        $temp_player_league["is_deleted"]       = 0;
                                        $temp_player_league["season_game_uid"] = $game['season_game_uid'];
                                        $temp_player_league["feed_verified"] = 1;
                                        $temp_player_league["player_status"] = 1;
                                        //$temp_player_league["salary"] = $salary;
                                        $player_league_array[$unique_id] =  $temp_player_league;
                                    }
                                }
                            }
                        }

                        
                    }
                }
                
                //echo "<pre>";print_r($player_league_array);die;
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
                                //unset($val['salary']);   
                                return $val;
                            },  $player_team_data
                                                    );
                        
                        $this->replace_into_batch(PLAYER_TEAM, $update_data);
                        
                        //For primary key setting
                        $this->set_auto_increment_key('player_team','player_team_id');
                    }
                }

            }
        }        
        
        //Make diffency team

        //Update display name if its empty or null
            $this->db->where('display_name', NULL);
            $this->db->set('display_name','full_name',FALSE);
            $this->db->update(PLAYER);
            echo "Player inserted";exit();

    }

    private function get_team_league_id($sports_id,$league_id,$team_uid,$league_abbr)
    {
        $this->api = $this->get_ncaa_config_detail('goalserve');
        $this->api = $this->api[$league_abbr];

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
        $api_config = $this->api_credential;

        $sql = $this->db->select("T.team_abbr,T.team_name,TL.team_league_id,TL.team_uid")
                        ->from(TEAM." T")
                        ->join(TEAM_LEAGUE." TL","TL.team_id=T.team_id")
                        ->where("TL.league_id",$league_id)
                        ->where("T.year",$api_config["year"])
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
        $current_teams  = $this->get_current_season_match($sports_id,'',$custom_where);
        echo "<pre>";print_r($current_teams);//die;
        if(empty($current_teams))
        {   
            exit("No Live Games");
        }

        $date_array = array();
        $legue_array = array();
        foreach ($current_teams as $key => $value)
        {
            $league_abbr = $value['league_abbr'];
            $date_array[date('d.m.Y', strtotime(substr($value['feed_date_time'],0,10)))] = '';
            $date_array[date('d.m.Y', strtotime($value['feed_date_time']))] = '';
            $legue_array[$league_abbr] = $date_array;
        }
        
        $this->load->helper('queue');
        //echo "<pre>";print_r($legue_array);die;
        foreach ($legue_array as $date_key => $date_array) 
        {
            //echo "<pre>";print_r($date_key);//die;
            //echo "<pre>";print_r($date_value);die;
        
            foreach ($date_array as $current_date_time => $value)
            {
                $this->api = $this->get_ncaa_config_detail('goalserve');
                $this->api = $this->api[$date_key];

                $url = $this->api['api_url'].$this->api['subscription_key']."/football/".$this->api['league_abbr']."-scores?date=".$current_date_time;
                //echo "<pre>";echo $url;continue;die;
                $score_data = @file_get_contents($url);
                if (!$score_data)
                {
                    continue;
                }
                $score_data = xml2array($score_data);
                //echo '<pre>';print_r($score_data);die;

                if(!empty($current_teams))
                {
                    foreach ($current_teams AS $current_team)
                    {
                        //echo "<pre>";print_r($score_data['scores']['category']['match']);die;
                        $matchs = @$score_data['scores']['category']['match'];
                        if(isset($matchs) && !empty($matchs))
                        {
                            if(!array_key_exists('0', $matchs))
                            {
                                $temp = $matchs;
                                $matchs = array();
                                $matchs[0] = $temp; 
                            }

                           //echo "<pre>";print_r($matchs);die;
                            foreach ($matchs as $key => $match)
                            {
                                $season_game_uid  = $match['attr']['contestID'];
                                
                                @date_default_timezone_set($this->time_zone);
                                $scheduled = date('Y-m-d H:i:s', strtotime($match['attr']['formatted_date'] . ' ' . $match['attr']['time']));
                                @date_default_timezone_set(DEFAULT_TIME_ZONE);
                                $scheduled              = $scheduled;
                                $week                   = $current_team['week'];
                                $hometeam = $match['hometeam'];
                                $awayteam = $match['awayteam'];
                                $home_uid = $hometeam["attr"]["id"];
                                $away_uid = $awayteam["attr"]["id"];
                                $home_score = $hometeam["attr"]["totalscore"];
                                $away_score = $awayteam["attr"]["totalscore"];

                                $hometeam_abbr = $current_team['home'];
                                $awayteam_abbr = $current_team['away'];
                                
                                $hometeam_id        =  $hometeam['attr']['id'];
                                $awayteam_id        =  $awayteam['attr']['id'];
                                //echo "<pre>";print_r($match_detail);die;
                                $defensive_kick_return_td = array();
                                $defensive_punt_return_td = array();
                                
                                
                                $common_player_array = array();
                                //$common_player_array = $this->_player_default_stats();
                                if($current_team['season_game_uid'] == $season_game_uid)
                                {
                                    $team_list = array('hometeam','awayteam');
                                    foreach ($team_list as $key => $team)
                                    {
                                        
                                        $team_id = $team. "_id";
                                        $common_player_array["league_id"] = $current_team['league_id'];
                                        $common_player_array["season_game_uid"] = $season_game_uid;
                                        $common_player_array["week"] = $week;
                                        $common_player_array["scheduled"] = $scheduled;
                                        $common_player_array["scheduled_date"] = date("Y-m-d H:i:s",strtotime($scheduled));
                                        $common_player_array['home_uid'] = $home_uid;
                                        $common_player_array['home_score'] = $home_score;
                                        $common_player_array['away_uid'] = $away_uid;
                                        $common_player_array['away_score'] = $away_score;
                                        $common_player_array["team_uid"] = $$team_id;

                                        //For passing
                                        $passing = @$match['passing'][$team]['player'];
                                        if(isset($passing) && !empty($passing))
                                        {
                                            if(!array_key_exists('0', $passing))
                                            {
                                                $temp = $passing;
                                                $passing = array();
                                                $passing[0] = $temp; 
                                            } 
                                           //echo "<pre>";print_r($passing);die;
                                            $player_array = array();
                                            foreach($passing AS $key => $value)
                                            {
                                                
                                                $passing_array["player_uid"] = @$value['attr']['id'];
                                                $passing_array["passing_yards"] = @$value['attr']['yards'];
                                                $passing_array["passing_touch_downs"] = @$value['attr']['passing_touch_downs'];
                                                $passing_array["passing_interceptions"] = @$value['attr']['interceptions'];
                                                $passing_array["passing_two_pt"] = @$value['attr']['two_pt'];

                                                $player_array[] = array_merge($common_player_array, $passing_array);
                                                $this->replace_into_batch(GAME_STATISTICS_NCAA, $player_array);
                                            }
                                        }

                                        //echo "<pre>";print_r($player_array);die;
                                        //For rushing
                                        $rushing = @$match['rushing'][$team]['player'];
                                        if(isset($rushing) && !empty($rushing))
                                        {
                                            if(!array_key_exists('0', $rushing))
                                            {
                                                $temp = $rushing;
                                                $rushing = array();
                                                $rushing[0] = $temp; 
                                            }
                                            $player_array = array();
                                            foreach($rushing AS $key => $value)
                                            {
                                                
                                                $rushing_array["player_uid"] = @$value['attr']['id'];
                                                $rushing_array["rushing_yards"] = @$value['attr']['yards'];
                                                $rushing_array["rushing_touch_downs"] = @$value['attr']['rushing_touch_downs'];
                                                $rushing_array["rushing_two_pt"] = @$value['attr']['two_pt'];

                                                $player_array[] = array_merge($common_player_array, $rushing_array);
                                                $this->replace_into_batch(GAME_STATISTICS_NCAA, $player_array);
                                            }
                                        }

                                        //For receiving
                                        $receiving = @$match['receiving'][$team]['player'];
                                        if(isset($receiving) && !empty($receiving))
                                        {
                                            if(!array_key_exists('0', $receiving))
                                            {
                                                $temp = $receiving;
                                                $receiving = array();
                                                $receiving[0] = $temp; 
                                            }
                                            $player_array = array();
                                            foreach($receiving AS $key => $value)
                                            {
                                                
                                                $receiving_array["player_uid"] = @$value['attr']['id'];
                                                $receiving_array["receiving_yards"] = @$value['attr']['yards'];
                                                $receiving_array["receptions"] = @$value['attr']['total_receptions'];
                                                $receiving_array["receiving_touch_downs"] = @$value['attr']['receiving_touch_downs'];
                                                $receiving_array["receiving_two_pt"] = @$value['attr']['two_pt'];

                                                $player_array[] = array_merge($common_player_array, $receiving_array);
                                                $this->replace_into_batch(GAME_STATISTICS_NCAA, $player_array);
                                            }
                                        }

                                    
                                        //echo "<pre>";print_r($player_array);die;
                                    }

                                   echo "<pre>";echo "Score For Game Id: ".$season_game_uid;
                                    $season_update_arr = array();
                                    $season_update_arr['status'] = $current_team['status'];
                                    $season_update_arr['feed_status'] = @$match['attr']['status'];
                                    $season_update_arr['home_score']  = $home_score;
                                    $season_update_arr['away_score']  = $away_score;
                                    if((@$match['attr']['status'] == 'Full-time' || @$match['attr']['status'] == "Final"))
                                    {   
                                        $player_count = count($this->get_game_statistics_by_game_id($season_game_uid,$sports_id,$current_team['league_id']));
                                        if($player_count >= 14)
                                        {
                                            $season_update_arr['status'] = 2;
                                            $season_update_arr['status_overview'] = 4;
                                            $season_update_arr['match_closure_date'] = format_date();
                                        }    
                                    }

                                    $team_score_data = array(
                                                                "home_score" => $home_score,
                                                                "away_score" => $away_score
                                                            );
                                    $season_update_arr['score_data'] = json_encode($team_score_data);

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
                                }

                            }
                            
                        }
                   }
                }
            }
                
        }    

        exit();
    }

    
    private function _player_default_stats()
    {
        $default_array = array();
        $default_array["passing_yards"]                     = 0;
        $default_array["passing_touch_downs"]               = 0;
        $default_array["passing_interceptions"]             = 0;
        $default_array["passing_two_pt"]                    = 0;
        $default_array["rushing_yards"]                     = 0;
        $default_array["rushing_touch_downs"]               = 0;
        $default_array["rushing_two_pt"]                    = 0;
        $default_array["receiving_yards"]                   = 0;
        $default_array["receptions"]                        = 0;
        $default_array["receiving_touch_downs"]             = 0;
        $default_array["receiving_two_pt"]                  = 0;
        $default_array["fumbles_touch_downs"]               = 0;
        $default_array["fumbles_lost"]                      = 0;
        $default_array["fumbles_recovered"]                 = 0;
        $default_array["interceptions_yards"]               = 0;
        $default_array["interceptions_touch_downs"]         = 0;
        $default_array["interceptions"]                     = 0;
        $default_array["kick_returns_yards"]                = 0;
        $default_array["kick_returns_touch_downs"]          = 0;
        $default_array["punt_returns_yards"]                = 0;
        $default_array["punt_return_touch_downs"]           = 0;
        $default_array["field_goals_made"]                  = 0;
        $default_array["field_goals_from_1_19_yards"]       = 0;
        $default_array["field_goals_from_20_29_yards"]      = 0;
        $default_array["field_goals_from_30_39_yards"]      = 0;
        $default_array["field_goals_from_40_49_yards"]      = 0;
        $default_array["field_goals_from_50_yards"]         = 0;
        $default_array["extra_points_made"]                 = 0;
        $default_array["defensive_interceptions"]           = 0;
        $default_array["defensive_fumbles_recovered"]       = 0;
        $default_array["sacks"]                             = 0;
        $default_array["safeties"]                          = 0;
        $default_array["defensive_touch_downs"]             = 0;
        $default_array["points_allowed"]                    = 0;

        return $default_array;
    }

    #Scoring Calculation start
     
    /**
     * [GET_NCAA_SCORING_FORMULES :- GETTING FORMULAS FROM DATABASE ]
     * @return [array] $formula [description]
     */
    private function get_ncaa_scoring_formules($sports_id)
    {
        $rs = $this->db->select('ms.score_points, ms.master_scoring_category_id, ms.meta_key, msc.scoring_category_name')
                        ->from(MASTER_SCORING_RULES." AS ms")
                        ->join(MASTER_SCORING_CATEGORY." AS msc", "msc.master_scoring_category_id= ms.master_scoring_category_id","left")
                        ->where('msc.sports_id',$sports_id)
                        ->get();
                //echo $this->db->last_query(); die;
        $raw_formula_data = $rs->result_array();
        $formula = array();
        if(!empty($raw_formula_data))
        {
            foreach ($raw_formula_data as $val)
            {
                $formula[$val['scoring_category_name']][$val['meta_key']] = $val['score_points'];
            }
        }
        return $formula;
    }

    /**
     * [GET_GAME_STATISTICS_BY_GAME_ID :- GET GAME STATICS FROM DATABASE FOR PARTICULAR GAME ID ]
     * @param  [string] $season_game_unique_id 
     * @return [array]  $res
     */
     private function get_game_statistics_by_game_id($season_game_uid,$sports_id,$league_id)
    {
        $rs = $this->db->select("GSF.*,PT.position", FALSE)
                ->from(GAME_STATISTICS_NCAA . " AS GSF")
                ->join(PLAYER . " AS P", "P.player_uid = GSF.player_uid AND P.sports_id = $sports_id ", 'INNER')
                ->join(PLAYER_TEAM . " AS PT", "PT.player_id = P.player_id AND PT.season_game_uid = $season_game_uid ", 'INNER')
                ->where("GSF.season_game_uid", $season_game_uid)
                ->where("GSF.league_id", $league_id)
                ->group_by("P.player_uid")
                ->get();
        //echo $this->db->last_query(); die;
        $res = $rs->result_array();
        return $res;
    }

    /**
    * This method calculate playeys score minute by minute 
    * 
    * @param [int] $league_id
    * @return null
    */
    public function save_calculated_scores($sports_id)
    {
        ## Get Current season matches list
        $season_matchs = $this->get_current_season_match($sports_id);
        //echo "<pre>";print_r($season_matchs);die;
        if(!empty($season_matchs))
        {
            ## Get All Scoring Rules Formula
            $formulas   = $this->get_ncaa_scoring_formules($sports_id);
            //echo "<pre>";print_r($formulas);die;
            $formula    = $formulas['normal'];
            foreach ($season_matchs as $season_match)
            {
                $season_game_uid    = $season_match['season_game_uid'];
                $league_id          = $season_match['league_id'];
                // Get game stats of every minute by season_game_unique_id
                $game_stats = $this->get_game_statistics_by_game_id($season_game_uid,$sports_id,$league_id);
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

                        $each_player_score['season_game_uid']   = $season_game_uid;
                        $each_player_score['player_uid']        = $stats['player_uid'];
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

                        
                        /**** RECEVING ****/

                        if($stats['receiving_yards'] != 0)
                        {
                            $total_score_yards = $stats['receiving_yards'];
                            $score = $score + ($total_score_yards * $formula['RECEIVING_YARDS']) ;
                            $score_key['RECEIVING_YARDS'] = ($total_score_yards * $formula['RECEIVING_YARDS']) ;
                        }

                        
                        if($stats['receiving_touch_downs'] != 0)
                        {
                            $score = $score + ($stats['receiving_touch_downs'] * $formula['RECEIVING_TOUCHDOWNS']) ;
                            $score_key['RECEIVING_TOUCHDOWNS'] = ($stats['receiving_touch_downs'] * $formula['RECEIVING_TOUCHDOWNS']) ;
                        }

                        if(array_key_exists($stats['player_uid'], $all_player_scores) && !empty($all_player_scores[$stats['player_uid']]))
                        {
                            $json_data  = (array) json_decode($all_player_scores[$stats['player_uid']]['break_down']);
                            
                            $each_player_score['score']         = $all_player_scores[$stats['player_uid']]['score'] + $score;
                            $each_player_score['week']          = $stats["week"];
                            $each_player_score['normal_score']  = $all_player_scores[$stats['player_uid']]['normal_score'] + $score;
                           
                            $each_player_score['break_down']    = json_encode(array_merge($score_key,$json_data));


                            $all_player_scores[$stats['player_uid']] = $each_player_score;

                        }
                        else
                        {
                            $each_player_score['score']         = $score;
                            $each_player_score['week']          = $stats["week"];
                            $each_player_score['normal_score']  = $score;
                            $each_player_score['break_down']    = json_encode($score_key);
                            $all_player_scores[$stats['player_uid']] = $each_player_score;
                       }
                        
                    }
                    // echo "<pre> Game id :-  " .$season_game_uid . '<br>' ;
                    //echo "<pre>";print_r(array_values($all_player_scores)); echo "</pre> <HR>";die;
                    //echo "<pre>";print_r($all_player_scores);die;
                    if(!empty($all_player_scores))
                    {
                        $all_player_scores = array_values($all_player_scores);
                        //Update score first zero then update
                        $this->db->where('season_game_uid', $season_game_uid);
                        $this->db->where('league_id', $league_id);
                        $this->db->update(GAME_PLAYER_SCORING,array("normal_score"=>"0","bonus_score"=>"0","score"=>"0","break_down"=>NULL,"final_break_down"=>NULL));

                        $this->replace_into_batch(GAME_PLAYER_SCORING, $all_player_scores,TRUE);
                        echo "<pre> Game id :-  " .$season_game_uid . '<br>' ;
                    }
                } // End  of  Empty of game_ stats
            } // End of season match loop  
        } // End of empty season match check
        exit();
    }


    /**
    * This method calculate playeys score minute by minute 
    * 
    * @param [int] $league_id
    * @return null
    */
    public function save_calculated_scores_by_match_id($sports_id,$league_id,$season_game_uid)
    {
        ## Get Current season matches list
        $season_match = $this->get_season_match_details($season_game_uid,$league_id);
       //echo "<pre>";print_r($season_match);die;
        if(!empty($season_match))
        {
            ## Get All Scoring Rules Formula
            $formulas   = $this->get_ncaa_scoring_formules($sports_id);
            //echo "<pre>";print_r($formulas);die;
            $formula    = $formulas['normal'];
            
            $season_game_uid    = $season_match['season_game_uid'];

            // Get game stats of every minute by season_game_unique_id
            $game_stats = $this->get_game_statistics_by_game_id($season_game_uid,$sports_id,$league_id);
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

                    $each_player_score['season_game_uid']   = $stats['season_game_uid'];
                    $each_player_score['player_uid']        = $stats['player_uid'];
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

                    


                    /**** RECEVING ****/

                    if($stats['receiving_yards'] != 0)
                    {
                        $total_score_yards = $stats['receiving_yards'];
                        $score = $score + ($total_score_yards * $formula['RECEIVING_YARDS']) ;
                        $score_key['RECEIVING_YARDS'] = ($total_score_yards * $formula['RECEIVING_YARDS']) ;
                    }

                    

                    if($stats['receiving_touch_downs'] != 0)
                    {
                        $score = $score + ($stats['receiving_touch_downs'] * $formula['RECEIVING_TOUCHDOWNS']) ;
                        $score_key['RECEIVING_TOUCHDOWNS'] = ($stats['receiving_touch_downs'] * $formula['RECEIVING_TOUCHDOWNS']) ;
                    }

                    
                    if(array_key_exists($stats['player_uid'], $all_player_scores) && !empty($all_player_scores[$stats['player_uid']]))
                    {
                        $json_data  = (array) json_decode($all_player_scores[$stats['player_uid']]['break_down']);
                        
                        $each_player_score['score']         = $all_player_scores[$stats['player_uid']]['score'] + $score;
                        $each_player_score['week']          = $stats["week"];
                        $each_player_score['normal_score']  = $all_player_scores[$stats['player_uid']]['normal_score'] + $score;
                       
                        $each_player_score['break_down']    = json_encode(array_merge($score_key,$json_data));


                        $all_player_scores[$stats['player_uid']] = $each_player_score;

                    }
                    else
                    {
                        $each_player_score['score']         = $score;
                        $each_player_score['week']          = $stats["week"];
                        $each_player_score['normal_score']  = $score;
                        $each_player_score['break_down']    = json_encode($score_key);
                        $all_player_scores[$stats['player_uid']] = $each_player_score;
                   }
                    
                }
                // echo "<pre> Game id :-  " .$season_game_unique_id . '<br>' ;
                //echo "<pre>";print_r(array_values($all_player_scores)); echo "</pre> <HR>";die;
                //echo "<pre>";print_r($all_player_scores);die;
                if(!empty($all_player_scores))
                {
                    $all_player_scores = array_values($all_player_scores);
                    //Update score first zero then update
                        $this->db->where('season_game_uid', $season_game_uid);
                        $this->db->where('league_id', $league_id);
                        $this->db->update(GAME_PLAYER_SCORING,array("normal_score"=>"0","bonus_score"=>"0","score"=>"0","break_down"=>NULL,"final_break_down"=>NULL));
                    $this->replace_into_batch(GAME_PLAYER_SCORING, $all_player_scores,TRUE);
                    //echo "<pre> Game id :-  " .$season_game_uid . '<br>' ;
                }
            } // End  of  Empty of game_ stats
             
        } // End of empty season match check
        return TRUE;
    }
}

/* End of file  */
/* Location: ./application/modules/Soccer/ */