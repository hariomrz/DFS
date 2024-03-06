<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Goalserve_model extends MY_Model {

    public $position_array = array("K","QB","WR","RB","TE");
    public $api_credential = array();

    public $time_zone = "America/New_York"; //For EST
    public $time_in_time_zone = "-05:00";   


    public function __construct() 
    {
        parent::__construct();
        $this->api = $this->get_nfl_config_detail('goalserve');
    }

    /**
     * [get_teams description]
     * @param  [type] $league_id [description]
     * @return [type]            [description]
     */
    public function get_team($sports_id)
    {
        $current_date = format_date();
        $current_date = format_date();
        $this->db->select("L.league_id,L.league_uid,L.sports_id,L.league_abbr")
                    ->from(LEAGUE. " AS L")
                    ->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.active = 1")
                    ->where("L.sports_id", $sports_id)
                    ->where("L.active", 1)
                    ->where("L.league_last_date >= ", $current_date)
                    ->order_by("L.league_id","DESC");
                $sql = $this->db->get();
        $league_data = $sql->result_array();
       //echo "<pre>";print_r( $league_data);die;
        if (!empty($league_data))
        {
            foreach ($league_data as $league)
            {
                //echo "<pre>";print_r( $league);die;
                $this->api = $this->get_nfl_config_detail('goalserve');
                $this->api = $this->api[$league['league_abbr']];
                
                $url = $this->api['api_url'].$this->api['subscription_key']."/football/".$this->api['league_abbr']."-shedule";
                //echo $url;die;
                $shedule_data = @file_get_contents($url);
                if (!$shedule_data){
                    exit;
                }
                $shedule_array = xml2array($shedule_data);
                $team_key = array();
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
                                        $team_uid = @$value['hometeam']['attr']['id'];
                                        $team_k = $league['sports_id'].'_'.$team_uid;

                            $data[$team_k] = array(
                                    'team_uid'     => $team_uid, 
                                    'sports_id'     => $sports_id,
                                    'team_name'     => trim(@$value['hometeam']['attr']['name']),
                                    'team_abbr'     => $abbr,
                                    'display_team_name'     => trim(@$value['hometeam']['attr']['name']),
                                    'display_team_abbr'     => $abbr,
                                    'year'          => $this->api['year']
                                                                );
                                    $team_key[] = $team_k; 
                                        
                                    }
                                    
                                }
                            }

                        }
                    }
                    
                }
                
                if(isset($data) && count($data) > 0)
                {
                    //echo "<pre>";print_r($data);die;
                    $concat_key = 'CONCAT(sports_id,"_",team_uid)';
                    $this->insert_or_update_on_exist($team_key,$data,$concat_key,TEAM,'team_id');
                    echo "<br>Teams inserted.";
                    //Make defence team as player
                    $this->make_defence_team($sports_id,$league['league_id']);
                    //set/update team cache
                    //$team_cache = 'team_'.$sports_id;
                    //$team_ids = $this->get_team_id_with_team_uid($sports_id);
                    //$this->set_cache_data($team_cache,$team_ids,REDIS_24_HOUR);
                    
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
        $league_data = $this->get_all_table_data("league_id,league_uid, sports_id,league_abbr", LEAGUE, array('sports_id' => $sports_id, "active" => '1',"league_last_date >= "=>$current_date));
        //echo "<pre>";print_r( $league_data);die;
        if (!empty($league_data))
        {
            //All team uid with team id
            $team_ids = $this->get_team_id_with_team_uid($sports_id);
            
            $season_keys = array();
            $next_date_time = date("Y-m-d H:i:s", strtotime(format_date()." +95 days"));
            foreach ($league_data as $league)
            {
                
                $this->api = $this->get_nfl_config_detail('goalserve');
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
                            //echo "<pre>";print_r($tournament['attr']['name']);//die;
                            if(in_array($tournament['attr']['name'], $this->api['stage_name']))
                            {
                                //echo "<pre>";print_r($tournament['attr']);die;
                                $weeks = $tournament['week'];
                                $attr = $tournament['attr'];
                                //break;
                            }else{
                                continue;
                            }
                        
                            //echo "<pre>";print_r($weeks);die;
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
                            //echo "<pre>";print_r($weeks);die;
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
                                        $matcharr = @$matches['match'];
                                        if($matcharr == '')
                                        {
                                            $matcharr = array();
                                        }    
                                        if(!array_key_exists('0', $matcharr))
                                        {
                                            $temp = $matcharr;
                                            $matcharr = array();
                                            $matcharr[0] = $temp; 
                                        }
                                        //echo "<pre>";print_r($matcharr);die;
                                        foreach($matcharr as $match)
                                        {
                                           if(@$match['attr']['time']=='TBA' or @$match['attr']['time']=='')
                                            {
                                                //$match['attr']['time'] = '00.00.00';
                                                continue;
                                            }
                                            if(!trim($match['attr']['contestID'])) continue;
                                            
                                            //$team_ids = $this->get_team_id_with_team_uid($sports_id,'',array(trim($match['hometeam']['attr']['id']),trim($match['awayteam']['attr']['id'])));
                                            //echo "<pre>";print_r($team_ids);die;

                                            $home_id = @$team_ids[trim($match['hometeam']['attr']['id'])];
                                            $away_id = @$team_ids[trim($match['awayteam']['attr']['id'])];

                                            
                                            $sdate  = $match['attr']['formatted_date']." ".$match['attr']['time'];
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

                                            if($scheduled_date > $next_date_time)
                                            {
                                                continue;
                                            } 

                                            $season_game_uid = trim($match['attr']['contestID']);
                                            $season_info = $this->get_single_row("season_scheduled_date,delay_by_admin,delay_minute", SEASON, array("season_game_uid" => $season_game_uid,"league_id" => $league_id));
                                            //if match delay set from feed then modify match schedule date time
                                            if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 1)
                                            {
                                                $season_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$season_info['delay_minute'].' minutes', strtotime($season_info['season_scheduled_date'])));
                                                $scheduled_date = date('Y-m-d', strtotime('+'.$season_info['delay_minute'].' minutes', strtotime($season_info['season_scheduled_date'])));
                                            }



                                            if(!isset($match['hometeam'])||!isset($match['hometeam']['attr'])||!isset($match['hometeam']['attr']['name'])||!$match['hometeam']['attr']['name']) continue;

                                            $season_k = $league_id.'_'.$match['attr']['contestID'];

                                            $data[$season_k] = array(
                                            "league_id"                 => $league_id,
                                            "season_game_uid"           => trim($match['attr']['contestID']),
                                            "year"                      => $this->api['year'],
                                            "type"                      => trim($this->api['season']),
                                            "week"                      => 0,
                                            "feed_date_time"            => $scheduled_date_time,
                                            "season_scheduled_date"     => $season_scheduled_date,
                                            "scheduled_date"            => $scheduled_date,
                                            "home_id"                  => trim($home_id),
                                            "away_id"                  => trim($away_id),
                                            "api_week"                  => intval(trim(str_replace("Week ", "", $week['attr']['name']))
                                                            ));
                                            $season_keys[] = $season_k;
                                        }
                                    }
                                }

                            }
                        }    
                    }
                    
                }
                
                if(isset($data) && count($data) > 0)
                {
                    //echo "<pre>";print_r($data);die;
                    $concat_key = 'CONCAT(league_id,"_",season_game_uid)';
                        $this->insert_or_update_on_exist($season_keys,$data,$concat_key,SEASON,'season_id');
                    echo "<pre>";echo "Season insert for league: ".$league['league_abbr']; 
                }
                
            }
        }
        exit();

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
        $next_date_time = date("Y-m-d H:i:s", strtotime(format_date()." +95 days"));
        $sql = $this->db->select("L.league_id,L.league_uid,L.sports_id,S.season_id,S.is_published,S.home_id,S.away_id,S.season_game_uid,L.league_abbr,T1.team_uid AS home_uid,T2.team_uid AS away_uid", FALSE)
                            ->from(LEAGUE." AS L")
                            ->join(SEASON." AS S", "S.league_id = L.league_id", 'INNER')
                            ->join(TEAM." AS T1", "T1.team_id = S.home_id", 'INNER')
                            ->join(TEAM." AS T2", "T2.team_id = S.away_id", 'INNER')
                            ->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.active = 1")
                            ->where("L.active", 1)
                            ->where("L.sports_id", $sports_id)
                            ->where("S.season_scheduled_date >= ", format_date())
                            ->get();
        //echo $this->db->last_query();     
        $current_game = $sql->result_array();
        //echo '<pre>Live Match: ';print_r($current_game);die;
        if (!empty($current_game))
        {
            //All team uid with team id
            $team_ids = $this->get_team_id_with_team_uid($sports_id);
                
            foreach ($current_game as $key => $game) 
            {
                $season_id = $game['season_id'];
                $league_abbr = $game['league_abbr'];
                $league_id = $game['league_id'];
                $teams_id_array = array($game['home_uid'],$game['away_uid']);

                $season_player_data = $data = array();
                $player_key = $season_player_key = array();
                $match_player_uid = array();
                $player_id_arr = $player_uid_arr = array(); 
                $def_player_league_array = array();
                //echo "<pre>";print_r($teams_id_array);die;
                foreach ($teams_id_array as $team_uid) 
                {
                    if(empty($team_ids[$team_uid])) 
                    {
                        continue;
                    }

                    $this->api = $this->get_nfl_config_detail('goalserve');
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
                                   // end for salary calculation
                                    $player_name = $player['attr']['name'];
                                    $position = $player['attr']['position'];
                                    $player_uid = trim($player['attr']['id']);
                                    if($position == 'P' || $position == 'PK')
                                    {
                                        $position = "K";
                                    }    
                                    
                                   // echo "<pre>";print_r($position);die;
                                    if (in_array($position, $this->position_array) && $position !='' ) 
                                    {
                                        $player_k = $sports_id.'_'.$player_uid;
                                        $data[$player_k] = array(
                                            "player_uid"        => $player_uid,
                                            "sports_id"         => trim($sports_id),
                                            "full_name"         => (trim($player_name)),
                                            "first_name"        => (trim(strtok($player_name,' '))),
                                            "last_name"         => (trim(substr($player_name, strrpos($player_name, ' ') + 1)))                    
                                                         );
                                        $player_key[] = $player_k;
                                        $player_uid_arr[] = $player_uid;
                                        //$player_uids[] = $player['attr']['id'];
                                        $match_player_uid[] = $player_uid;
                                       // Prepare Player Team Relation Data
                                        
                                        $pt_key = $player_uid.'|'.$sports_id.'|'.$season_id;
                                        $player_team[$pt_key] = array(
                                                            "player_id"     => 0,
                                                            "season_id"     => $season_id,
                                                            "position"      => $position,
                                                            "is_deleted"    => 0,
                                                            "player_status" => 1,
                                                            "salary"        => $salary,
                                                            "feed_verified" => 1,
                                                            "team_id"   => $team_ids[$team_id],
                                                            "jersey_number" => intval($player['attr']['number'])
                                                        );
                                                    }
                                                }
                                            }
                                        }

                        //Add team as a player in def position
                            $def_player_data = $this->get_all_table_data("player_id, sports_id, player_uid", PLAYER, array('player_uid' => md5($team_id),'sports_id' => $sports_id));
                            //echo "<pre>";print_r($def_player_data);die;
                            $def_unique_id      = $def_player_data['0']['player_uid'].'|'.$sports_id.'|'.$game['season_id'];
                            $def_player_league["player_id"]        = $def_player_data['0']['player_id'];
                            $def_player_league["team_id"]   = $team_ids[$team_id];
                            $def_player_league["position"]         = 'DEF';
                            $def_player_league["jersey_number"]    = intval($player['attr']['number']);
                            $def_player_league["is_deleted"]       = 0;
                            $def_player_league["feed_verified"]       = 1;
                            $def_player_league["player_status"] = 1;
                            $def_player_league["salary"] = 9;
                            $def_player_league["season_id"] = $game['season_id'];
                            $def_player_league_array[$def_unique_id] =  $def_player_league;

                            $player_uid_arr[] = $def_player_data['0']['player_uid'];
                            $match_player_uid[] = $def_player_data['0']['player_uid'];
                            
                    }
                }

                //echo "<pre>";print_r($data);
                //echo "<pre>";print_r($player_team);
                //echo "<pre>";print_r($def_player_league_array);die;
                $player_team = array_merge($def_player_league_array,$player_team);
                //echo "<pre>";print_r($match_player_uid);die;
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
                                    if($player_team_arr['feed_verified'] == 1 && $game['is_published'] == 1 && $player['feed_verified'] == 0 && $player['is_published'] == 0){
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
                            $player_string = implode(',',array_map(function($n){return '\''.$n.'\'';},array_unique($player_id_arr)));
                            $where = 'season_id ='.$season_id.  
                                    ' AND player_id IN ('.$player_string.')'; 
                            $this->insert_or_update_on_exist($player_team_key,$player_team_data,$concat_key,PLAYER_TEAM,'player_team_id',$update_ignore,$where);
                        }

                        //Update display name if its empty or null
                        $this->db->where('display_name', NULL);
                        $this->db->set('display_name','full_name',FALSE);
                        $this->db->update(PLAYER);
                        //new player update reset cache nd json file for roster list
                        if(isset($new_player_count) && $new_player_count == 1)
                        {
                            $this->db->select("CS.collection_master_id")
                                    ->from(COLLECTION_SEASON . " CS")
                                    ->join(COLLECTION_MASTER.' as CM', 'CM.collection_master_id = CS.collection_master_id',"INNER")
                                    ->where('CM.season_game_count', 1)
                                    ->where('CM.league_id', $game['league_id'])
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
                            echo "<br>Players inserted for [<b>".$game['league_id'].' - '.$season_id."</b>]";
                        }  
                        
                    }
            }
        }        
        
        exit();

    }

    
  
    /**
     * @Summary: This function for use creat defence team player.
     * database.
     * @access: public
     * @param:
     * @return:
     */
    private function make_defence_team($sports_id)
    {
        $rs = $this->db->select("T.team_id,T.team_uid,T.team_abbr,T.team_name")
                        ->from(TEAM." T")
                        ->where("T.sports_id", $sports_id)
                        ->group_by("T.team_uid")
                        ->get();
        $result = $rs->result_array();
        //echo "<pre>";print_r($result);die;
        $def_player_array = array();
        $def_player_league_array = array();
        $def_player_uids = array();
        $player_key = array();
        foreach ($result as $key => $value) 
        {
            $player_uid = md5($value['team_uid']);
            $def_temp_player = array();
            $def_temp_player_league = array();

            $player_k = $sports_id.'_'.$player_uid;
            $def_temp_player["player_uid"]      = $player_uid; 
            $def_temp_player["sports_id"]       = $sports_id;
            $def_temp_player["full_name"]       = $value['team_name'] . " DEF";
            $def_temp_player["first_name"]      = $value['team_name'];
            $def_temp_player["last_name"]       = "DEF";
            $def_temp_player["nick_name"]       = $value['team_name'] . " DEF";
            $def_temp_player["injury_status"]   = "";           
            $def_player_uids[] = $player_uid;
            $def_player_array[$player_k] = $def_temp_player;
            $player_key[] = $player_k;
        }

        if (!empty($def_player_array))
        {
            //echo "<pre>";print_r($def_player_array);die;
            $concat_key = 'CONCAT(sports_id,"_",player_uid)';
            $this->insert_or_update_on_exist($player_key,$def_player_array,$concat_key,PLAYER,'player_id');
        }
    }


    
    public function get_scores($sports_id)
    {
        $current_teams = $this->get_season_match_for_score($sports_id);
        //echo "<pre>";print_r($current_teams);die;
        if(empty($current_teams))
        {   
            exit("No Live Games");
        }

        //All team uid with team id
        $team_ids = $this->get_team_id_with_team_uid($sports_id);

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
                $this->api = $this->get_nfl_config_detail('goalserve');
                $this->api = $this->api[$date_key];

                $url = $this->api['api_url'].$this->api['subscription_key']."/football/".$this->api['league_abbr']."-scores?date=".$current_date_time;
                //$url = "http://local.framework.com/cron/nfl-scores2.xml";
                //echo "<pre>";echo $url;die;
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
                        //echo '<pre>';print_r($current_team);die;
                        $season_id = $current_team['season_id'];
                        $league_id = $current_team['league_id'];
                        $home_id = $current_team['home_id'];
                        $away_id = $current_team['away_id'];
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
                                $home_id = @$team_ids[$hometeam["attr"]["id"]];
                                $away_id = @$team_ids[$awayteam["attr"]["id"]];
                                $home_score = $hometeam["attr"]["totalscore"];
                                $away_score = $awayteam["attr"]["totalscore"];
                                
                                $hometeam_id        =  $hometeam['attr']['id'];
                                $awayteam_id        =  $awayteam['attr']['id'];
                                //echo "<pre>";print_r($match_detail);die;
                                $defensive_kick_return_td = array();
                                $defensive_punt_return_td = array();
                                
                                
                                $common_player_array = array();
                                //$common_player_array = $this->_player_default_stats();
                                if($current_team['season_game_uid'] == $season_game_uid)
                                {
                                    //All player id with player id by season
                                    $player_ids = $this->get_player_data_by_season_id($sports_id,$current_team['season_id']);
                                    $team_list = array('hometeam','awayteam');
                                    foreach ($team_list as $key => $team)
                                    {
                                        
                                        $team_id = $team. "_id";
                                        $common_player_array["league_id"] = $current_team['league_id'];
                                        $common_player_array["season_id"] = $current_team['season_id'];
                                        $common_player_array["week"] = $week;
                                        $common_player_array["scheduled"] = $scheduled;
                                        $common_player_array["scheduled_date"] = date("Y-m-d H:i:s",strtotime($scheduled));
                                        
                                        $common_player_array['home_score'] = $home_score;
                                        
                                        $common_player_array['away_score'] = $away_score;
                                        $common_player_array["team_id"] = @$team_ids[$$team_id];
                                        //echo "<pre>";print_r($$team_id);continue;die;
                                        //$defensive_team_player_uid = md5($$team_id);  
                                        $defensive_team_player_uid  = @$player_ids[md5($$team_id)];
                                        $defensive_kick_return_td[$defensive_team_player_uid] = 0;
                                        $defensive_punt_return_td[$defensive_team_player_uid] = 0;

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
                                                $player_id = @$player_ids[$value['attr']['id']];
                                                
                                                if($player_id == '')
                                                {
                                                    continue;
                                                } 
                                                $passing_array["player_id"] = $player_id;
                                                $passing_array["passing_yards"] = @$value['attr']['yards'];
                                                $passing_array["passing_touch_downs"] = @$value['attr']['passing_touch_downs'];
                                                $passing_array["passing_interceptions"] = @$value['attr']['interceptions'];
                                                $passing_array["passing_two_pt"] = @$value['attr']['two_pt'];

                                                $player_array[] = array_merge($common_player_array, $passing_array);
                                                //echo "<pre>";print_r($player_array);die;
                                                $this->replace_into_batch(GAME_STATISTICS_FOOTBALL, $player_array);
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
                                                $player_id = @$player_ids[$value['attr']['id']];
                                                
                                                if($player_id == '')
                                                {
                                                    continue;
                                                } 
                                                $rushing_array["player_id"] = $player_id;
                                                $rushing_array["rushing_yards"] = @$value['attr']['yards'];
                                                $rushing_array["rushing_touch_downs"] = @$value['attr']['rushing_touch_downs'];
                                                $rushing_array["rushing_two_pt"] = @$value['attr']['two_pt'];

                                                $player_array[] = array_merge($common_player_array, $rushing_array);
                                                $this->replace_into_batch(GAME_STATISTICS_FOOTBALL, $player_array);
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
                                                $player_id = @$player_ids[$value['attr']['id']];
                                                
                                                if($player_id == '')
                                                {
                                                    continue;
                                                } 
                                                $receiving_array["player_id"] = $player_id;
                                                $receiving_array["receiving_yards"] = @$value['attr']['yards'];
                                                $receiving_array["receptions"] = @$value['attr']['total_receptions'];
                                                $receiving_array["receiving_touch_downs"] = @$value['attr']['receiving_touch_downs'];
                                                $receiving_array["receiving_two_pt"] = @$value['attr']['two_pt'];

                                                $player_array[] = array_merge($common_player_array, $receiving_array);
                                                $this->replace_into_batch(GAME_STATISTICS_FOOTBALL, $player_array);
                                            }
                                        }

                                         //For fumbles
                                        $fumbles = @$match['fumbles'][$team]['player'];
                                        if(isset($fumbles) && !empty($fumbles))
                                        {
                                            if(!array_key_exists('0', $fumbles))
                                            {
                                                $temp = $fumbles;
                                                $fumbles = array();
                                                $fumbles[0] = $temp; 
                                            }
                                            $player_array = array();
                                            foreach($fumbles AS $key => $value)
                                            {
                                                $player_id = @$player_ids[$value['attr']['id']];
                                                
                                                if($player_id == '')
                                                {
                                                    continue;
                                                } 
                                                $fumbles_array["player_id"] = $player_id;
                                                $fumbles_array["fumbles_touch_downs"] = @$value['attr']['rec_td'];
                                                $fumbles_array["fumbles_lost"] = @$value['attr']['lost'];
                                                $fumbles_array["fumbles_recovered"] = @$value['attr']['rec'];

                                                $player_array[] = array_merge($common_player_array, $fumbles_array);
                                                $this->replace_into_batch(GAME_STATISTICS_FOOTBALL, $player_array);
                                            }
                                        }

                                        //For interceptions
                                        $interceptions = @$match['interceptions'][$team]['player'];
                                        if(isset($interceptions) && !empty($interceptions))
                                        {
                                            if(!array_key_exists('0', $interceptions))
                                            {
                                                $temp = $interceptions;
                                                $interceptions = array();
                                                $interceptions[0] = $temp; 
                                            }
                                            $player_array = array();
                                            foreach($interceptions AS $key => $value)
                                            {
                                               $player_id = @$player_ids[$value['attr']['id']];
                                                
                                                if($player_id == '')
                                                {
                                                    continue;
                                                } 
                                               $interceptions_array["player_id"] = $player_id;
                                                $interceptions_array["interceptions_yards"] = @$value['attr']['yards'];
                                                $interceptions_array["interceptions_touch_downs"] = @$value['attr']['intercepted_touch_downs'];
                                                $interceptions_array["interceptions"] = @$value['attr']['total_interceptions'];

                                                 $player_array[] = array_merge($common_player_array, $interceptions_array);
                                                $this->replace_into_batch(GAME_STATISTICS_FOOTBALL, $player_array);
                                            }
                                        }

                                        //For kick_returns
                                        $kick_returns = @$match['kick_returns'][$team]['player'];
                                        if(isset($kick_returns) && !empty($kick_returns))
                                        {
                                            if(!array_key_exists('0', $kick_returns))
                                            {
                                                $temp = $kick_returns;
                                                $kick_returns = array();
                                                $kick_returns[0] = $temp; 
                                            }
                                            $player_array = array();
                                            foreach($kick_returns AS $key => $value)
                                            {
                                               $player_id = @$player_ids[$value['attr']['id']];
                                                
                                                if($player_id == '')
                                                {
                                                    continue;
                                                } 
                                                $kick_returns_array["player_id"] = $player_id;
                                                $kick_returns_array["kick_returns_yards"] = @$value['attr']['yards'];
                                                $kick_returns_array["kick_returns_touch_downs"] = @$value['attr']['td'];

                                                //for defensive team calculation
                                                $defensive_kick_return_td[$defensive_team_player_uid] = ($defensive_kick_return_td[$defensive_team_player_uid]) + intval(@$value['attr']['kick_return_td']); 


                                                $player_array[] = array_merge($common_player_array, $kick_returns_array);
                                                $this->replace_into_batch(GAME_STATISTICS_FOOTBALL, $player_array);
                                            }
                                        }

                                        //For punt_returns
                                        $punt_returns = @$match['punt_returns'][$team]['player'];
                                        if(isset($punt_returns) && !empty($punt_returns))
                                        {
                                            if(!array_key_exists('0', $punt_returns))
                                            {
                                                $temp = $punt_returns;
                                                $punt_returns = array();
                                                $punt_returns[0] = $temp; 
                                            }
                                            $player_array = array();
                                            foreach($punt_returns AS $key => $value)
                                            {
                                                $player_id = @$player_ids[$value['attr']['id']];
                                                
                                                if($player_id == '')
                                                {
                                                    continue;
                                                }
                                                $punt_returns_array["player_id"] = $player_id;
                                                $punt_returns_array["punt_returns_yards"] = @$value['attr']['yards'];
                                                $punt_returns_array["punt_return_touch_downs"] = @$value['attr']['td'];
                                                
                                                //for defensive team calculation
                                                $defensive_punt_return_td[$defensive_team_player_uid] = ($defensive_punt_return_td[$defensive_team_player_uid]) + intval(@$value['attr']['td']);


                                                $player_array[] = array_merge($common_player_array, $punt_returns_array);
                                                $this->replace_into_batch(GAME_STATISTICS_FOOTBALL, $player_array);
                                            }
                                        }

                                        //For kicking
                                        $kicking = @$match['kicking'][$team]['player'];
                                        if(isset($kicking) && !empty($kicking))
                                        {
                                            if(!array_key_exists('0', $kicking))
                                            {
                                                $temp = $kicking;
                                                $kicking = array();
                                                $kicking[0] = $temp; 
                                            }
                                            $player_array = array();
                                            foreach($kicking AS $key => $value)
                                            {
                                                $player_id = @$player_ids[$value['attr']['id']];
                                                
                                                if($player_id == '')
                                                {
                                                    continue;
                                                }
                                                $extra_point_made = "0";
                                                $extra_point_blocked = "0";
                                                if(array_key_exists('extra_point', $value['attr']))
                                                {
                                                    $field_goals_made_arr = explode("/", $value['attr']['extra_point']);
                                                    $extra_point_made = floatval($field_goals_made_arr['0']);
                                                    $extra_point_blocked = floatval($field_goals_made_arr['1']) - floatval($field_goals_made_arr['0']);
                                                }

                                                $field_goals_made = "0";
                                                $field_goals_blocked = "0";
                                                if(array_key_exists('field_goals', $value['attr']))
                                                {
                                                    $field_goals_made_arr = explode("/", $value['attr']['field_goals']);
                                                    $field_goals_made = floatval($field_goals_made_arr['0']);
                                                    $field_goals_blocked = floatval($field_goals_made_arr['1']) - floatval($field_goals_made_arr['0']);
                                                }   

                                                
                                                $kicking_array["player_id"] = $player_id;
                                                
                                                $kicking_array["field_goals_blocked"] = $field_goals_blocked;
                                                $kicking_array["field_goals_made"] = $field_goals_made;
                                                $kicking_array["field_goals_from_1_19_yards"] = @$value['attr']['field_goals_from_1_19_yards'];
                                                $kicking_array["field_goals_from_20_29_yards"] = @$value['attr']['field_goals_from_20_29_yards'];
                                                $kicking_array["field_goals_from_30_39_yards"] = @$value['attr']['field_goals_from_30_39_yards'];
                                                $kicking_array["field_goals_from_40_49_yards"] = @$value['attr']['field_goals_from_40_49_yards'];
                                                $kicking_array["field_goals_from_50_yards"] = @$value['attr']['field_goals_from_50_yards'];
                                                $kicking_array["extra_points_made"] = $extra_point_made;
                                                $kicking_array["extra_point_blocked"] = $extra_point_blocked;

                                                //echo '<pre>';print_r($kicking_array);die;

                                                $player_array[] = array_merge($common_player_array, $kicking_array);
                                                $this->replace_into_batch(GAME_STATISTICS_FOOTBALL, $player_array);
                                            }
                                        }

                                        //For defense for team
                                        $defense = @$match['team_stats'][$team];
                                        if(isset($defense) && !empty($defense))
                                        {
                                            if(!array_key_exists('0', $defense))
                                            {
                                                $temp = $defense;
                                                $defense = array();
                                                $defense[0] = $temp; 
                                            } 
                                            $player_array = array();
                                            foreach($defense AS $key => $value)
                                            {
                                                // echo "<pre>"; print_r(($$team_id));die;

                                                $player_id = @$player_ids[md5($$team_id)];
                                                
                                                if($player_id == '')
                                                {
                                                    continue;
                                                }
                                                $defense_array["player_id"] = $player_id;
                                                
                                                $defense_array["defensive_interceptions"] = floatval(@$value['interceptions']['attr']['total']);
                                                $defense_array["defensive_fumbles_recovered"] = floatval(@$value['fumbles_recovered']['attr']['total']);
                                                $defense_array["sacks"] = floatval(@$value['sacks']['attr']['total']);
                                                $defense_array["safeties"] = floatval(@$value['safeties']['attr']['total']);
                                                $defense_array["defensive_touch_downs"] = floatval(@$value['int_touchdowns']['attr']['total']);
                                                $defense_array["points_allowed"] = floatval(@$value['points_against']['attr']['total']);
                                                //$defense_array["defence_turnovers"] = intval(@$value['turnovers']['attr']['total']); 
                                                //echo "<pre>"; print_r($defense_array);continue;die;
                                                $player_array[] = array_merge($common_player_array, $defense_array);
                                                $this->replace_into_batch(GAME_STATISTICS_FOOTBALL, $player_array);
                                            }
                                        }

                                        //For defense for team
                                        $defensive = @$match['defensive'][$team]['player'];
                                        if(isset($defensive) && !empty($defensive))
                                        {
                                            if(!array_key_exists('0', $defensive))
                                            {
                                                $temp = $defensive;
                                                $defensive = array();
                                                $defensive[0] = $temp; 
                                            } 
                                            
                                            foreach($defensive AS $key => $value)
                                            {
                                                
                                                //for defensive team calculation
                                                $defensive_kick_return_td[$defensive_team_player_uid] = ($defensive_kick_return_td[$defensive_team_player_uid]) + intval(@$value['attr']['kick_return_td']);
                                            }
                                        }


                                        //defensive kick return td  
                                        $player_array = array();    
                                        if(!empty($defensive_kick_return_td[$defensive_team_player_uid]))
                                        {
                                            $defensive_kick_return_array["player_id"] = $defensive_team_player_uid;
                                            $defensive_kick_return_array["defensive_kick_return_touchdowns"] = intval($defensive_kick_return_td[$defensive_team_player_uid]);
                                            $player_array[] = array_merge($common_player_array, $defensive_kick_return_array);
                                            $this->replace_into_batch(GAME_STATISTICS_FOOTBALL, $player_array);
                                        } 

                                        //defensive punt return td 
                                        $player_array = array();    
                                        if(!empty($defensive_punt_return_td[$defensive_team_player_uid]))
                                        {
                                            $defensive_punt_return_array["player_id"] = $defensive_team_player_uid;
                                            $defensive_punt_return_array["defensive_punt_return_touchdowns"] = intval($defensive_punt_return_td[$defensive_team_player_uid]);
                                            $player_array[] = array_merge($common_player_array, $defensive_punt_return_array);
                                            $this->replace_into_batch(GAME_STATISTICS_FOOTBALL, $player_array);
                                        } 
                                        //echo "<pre>";print_r($player_array);die;
                                    }

                                   echo "<pre>";echo "Score For Game Id: ".$season_game_uid;
                                    $season_update_arr = array();
                                    
                                    $season_update_arr['feed_status'] = @$match['attr']['status'];
                                    
                                    if((@$match['attr']['status'] == 'Full-time' || @$match['attr']['status'] == "Final" || @$match['attr']['status'] == "After Over Time"))
                                    {   
                                        $player_count = count($this->get_game_statistics_by_season_id($current_team['season_id'],$sports_id,$league_id));
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
                                    echo "<pre>";print_r($season_update_arr);//die;
                                    $this->db->where('league_id', $current_team['league_id']);
                                    $this->db->where('season_game_uid', $season_game_uid);
                                    $this->db->update(SEASON, $season_update_arr);                                    
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
                ->join(PLAYER . " AS P", "P.player_id = GSF.player_id AND P.sports_id = $sports_id ", 'INNER')
                ->join(PLAYER_TEAM . " AS PT", "PT.player_id = P.player_id AND PT.player_id = GSF.player_id AND PT.season_id = '".$season_id."' ", 'INNER')
                ->where("GSF.season_id", $season_id)
                ->where("GSF.league_id", $league_id)
                ->group_by("P.player_id")
                ->get();

        $result = $sql->result_array();
        return $result;
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
}

/* End of file  */
/* Location: ./application/modules/nfl/ */
