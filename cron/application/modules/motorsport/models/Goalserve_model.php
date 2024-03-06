<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
class Goalserve_model extends MY_Model {

    public $time_zone = "America/New_York"; //For EST
    public $time_in_time_zone = "-05:00";
    //public $match_event = array("1"=>"first_practice","2"=>"second_practice","3"=>"last_practice","4"=>"first_qualification","5"=>"second_qualification","6"=>"third_qualification","7"=>"qualification","8"=>"race");
    public $match_event = array("1"=>"first_qualification","2"=>"second_qualification","3"=>"third_qualification","4"=>"qualification","5"=>"race");
    public $dr_salary = array("1"=>"21.5","2"=>"21.5","3"=>"20","4"=>"20","5"=>"19","6"=>"19","7"=>"19","8"=>"15.5","9"=>"15.5","10"=>"15.5","11"=>"11.5","12"=>"11.5","13"=>"11.5","14"=>"11.5","15"=>"11.5","16"=>"8","17"=>"8","18"=>"8","19"=>"8","20"=>"8");
    public $cr_salary = array("1"=>"25","2"=>"22.5","3"=>"19","4"=>"19","5"=>"11.5","6"=>"11.5","7"=>"8","8"=>"8","9"=>"8","10"=>"8");

    public function __construct() 
    {
        parent::__construct();
        $this->api = $this->get_motorsport_config_detail('goalserve');
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
        
            $league_array = array("f1"=>"Formula 1");
            $league_keys = array();
            foreach ($league_array as $league_key => $value) 
            {
                //echo "<pre>";print_r($value);die;
                $key = $sports_id.'_'.$league_key;
                $data[$key] = array(
                                "league_uid"    => @$league_key,
                                "league_abbr"   => @$league_key,
                                "league_name"   => @ucfirst($value),
                                "league_display_name" => @ucfirst($value),
                                "league_schedule_date" => '2023-03-01',
                                "league_last_date" => '2023-12-01',
                                "image" => 'f1.png',
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
        exit(); 
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
                //http://www.goalserve.com/getfeed/4bd6592cac4d492d1f2a08d729fa32b7/f1/f1-teams
                $url = $this->api['api_url'].$this->api['subscription_key']."/".$league['league_uid']."/teams";
                //$url = "http://".$_SERVER['SERVER_NAME']."/cron/motorsport/teams.xml";
                //echo $url;die;
                $team_data = @file_get_contents($url);
                if (!$team_data){
                    exit;
                }
                $team_array = xml2array($team_data);
                //echo "<pre>";print_r($team_array);die;
                $data  = array();
                $team_league  = array();
                $team_key = array();  
                foreach (@$team_array['standings']['teams'] as $key => $team) 
                {
                    foreach (@$team as $key => $value) 
                    {
                        // echo "<pre>";print_r($value);die;
                        $team_abbr =  @strtoupper(str_replace(" ", "", $value['attr']['name']));
                        $team_uid = trim($value['attr']['id']);
                        // Prepare team Data
                        $team_k = $league['sports_id'].'_'.$team_uid;
                        $data[$team_k] = array(
                                        "sports_id"     => $league['sports_id'],
                                        "team_uid"      => $team_uid,
                                        "team_abbr"     => $team_abbr,
                                        "team_name"     => @$value['attr']['name'],
                                        "display_team_abbr" => $team_abbr,
                                        "display_team_name" => @$value['attr']['name'],
                                        "year"          => $this->api['year']
                                    );
                        $team_key[] = $team_k;
                    }    
                }
                
                //echo "<pre>";print_r($data);die;
                if(isset($data) && count($data) > 0)
                {
                  // Insert Team data 
                    $concat_key = 'CONCAT(sports_id,"_",team_uid)';
                    $this->insert_or_update_on_exist($team_key,$data,$concat_key,TEAM,'team_id');
                    echo "<br>League uid " . $league['league_uid'] . " Teams inserted.";
                }

            }
        }
        exit();        
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
        //echo "<pre>";print_r($league_data);die;
        if (!empty($league_data))
        {
            
            foreach ($league_data as $league)
            {
                $league_id = $league['league_id'];
                //http://www.goalserve.com/getfeed/4bd6592cac4d492d1f2a08d729fa32b7/f1/f1-shedule

                $url = $this->api['api_url'].$this->api['subscription_key']."/".$league['league_abbr']."/".$league['league_abbr']."-shedule";
                 //$url = "http://".$_SERVER['SERVER_NAME']."/cron/motorsport/f1-results.xml";
                //echo $url;die;
                $shedule_data = @file_get_contents($url);
                if (!$shedule_data){
                    exit;
                }
                $shedule_array = xml2array($shedule_data);
                $tournament = @$shedule_array['scores']['tournament'];
                //echo "<pre>";print_r($shedule_array['scores']['tournament']);die;
                $match_data = $temp_match_array = array();
                $season_keys = array();
                foreach ($tournament as $key => $value) 
                {
                    //echo "<pre>";print_r($value[$this->match_event]);die;
                    //Start Match event count and start and end date time
                    $match_event = 0;
                    $track_name = NULL;
                    $date_time_array = array();
                    foreach ($this->match_event as $key => $me) 
                    {
                       //echo "<pre>";print_r($value);die;
                       if(isset($value[$me]) && $value[$me] != '')
                       { 
                           $event_date = @$value[$me]['attr']['date'];
                           $event_time  = @$value[$me]['attr']['time'];
                           $date_time = date('Y-m-d H:i:s', strtotime($event_date.$event_time));
                           $match_event += 1;
                           $date_time_array[] = $date_time;
                           $track_name = @$value[$me]['attr']['track'];
                        }   
                    }
                    if($match_event > 0)
                    {    
                        sort($date_time_array);
                        $start_date = $date_time_array[0];
                        $end_date = $date_time_array[$match_event-1];
                        //echo "<pre>";print_r(($date_time_array));die;
                    }    
                    //end Match event count and start and end date time
                    //echo "<pre>";print_r(($value));die;
                    $tournament_name = @$value['attr']['name'];
                    
                    $season_game_uid = @$value['attr']['id'];
                    //echo "<pre>";print_r($value['attr']['name']);die;
                    if(!isset($start_date))
                    {
                        continue;
                    }    
                    
                    @date_default_timezone_set($this->time_zone);
                    $scheduled_date_time = date('Y-m-d H:i:s', strtotime($start_date));
                    $temp_date = explode(" ", $scheduled_date_time);
                    $scheduled_date_time = $temp_date[0] . 'T' . $temp_date[1] . $this->time_in_time_zone;

                    $end_date_time = date('Y-m-d H:i:s', strtotime($end_date));
                    $temp_date = explode(" ", $end_date_time);
                    $end_date_time = $temp_date[0] . 'T' . $temp_date[1] . $this->time_in_time_zone;

                    @date_default_timezone_set(DEFAULT_TIME_ZONE);
                    $season_scheduled_date = date('Y-m-d H:i:s', strtotime($scheduled_date_time));
                    $end_scheduled_date = date('Y-m-d H:i:s', strtotime($end_date_time));
                    $scheduled_date = date('Y-m-d', strtotime($season_scheduled_date));
                    $year = date('Y', strtotime($season_scheduled_date));
                    if ($season_scheduled_date < format_date()) 
                    {
                        continue;
                    }

                    $season_info = $this->get_single_row("season_scheduled_date,delay_by_admin,delay_minute", SEASON, array("season_game_uid" => $season_game_uid,"league_id" => $league['league_id']));
                    //if match delay set from feed then modify match schedule date time
                    if(isset($season_info['delay_by_admin']) && $season_info['delay_by_admin'] == 1)
                    {
                        $season_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$season_info['delay_minute'].' minutes', strtotime($season_info['season_scheduled_date'])));
                        $scheduled_date = date('Y-m-d', strtotime('+'.$season_info['delay_minute'].' minutes', strtotime($season_info['season_scheduled_date'])));
                    }

                    

                    $season_k = $league['league_id'].'_'.$season_game_uid;

                    $temp_match_array = array(
                                        "league_id"         => $league['league_id'],
                                        "season_game_uid"   => $season_game_uid,
                                        "title"             => $tournament_name,
                                        "subtitle"          => $tournament_name,
                                        "tournament_name"   => $tournament_name,
                                        "track_name"        => $track_name,
                                        "year"              => $year,
                                        "type"              => 'REG',
                                        "feed_date_time"    => $scheduled_date_time,
                                        "season_scheduled_date" => $season_scheduled_date,
                                        "scheduled_date"    => $scheduled_date,
                                        "end_scheduled_date" => $end_scheduled_date,
                                        "match_event"      => $match_event,
                                        "is_tour_game"     => '1'                                     
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
        $day = 30;
        $next_date_time = date("Y-m-d H:i:s",strtotime('+'.$day.' day',strtotime(format_date())));
        $rs = $this->db->select("L.league_id,L.league_uid,L.sports_id,S.season_id,S.is_published,S.home_id,S.away_id,S.season_game_uid,L.league_abbr", FALSE)
                            ->from(LEAGUE." AS L")
                            ->join(SEASON." AS S", "S.league_id = L.league_id", 'INNER')
                            ->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.active = 1")
                            ->where("L.active", 1)
                            ->where("L.sports_id", $sports_id)
                            ->where('S.season_scheduled_date >', format_date())
                            ->where('S.season_scheduled_date <', $next_date_time)
                            ->get();
        //echo $this->db->last_query();     
        $current_game = $rs->result_array();
        //echo '<pre>Live Match : ';print_r($current_game);die;
        if (!empty($current_game))
        {
            //All team uid with team id
            $team_ids = $this->get_team_id_with_team_uid($sports_id);
               
            foreach ($current_game as $key => $game) 
            {
                $league_abbr = $game['league_abbr'];
                $league_id = $game['league_id'];
                $season_game_uid = $game['season_game_uid'];
                $season_id = $game['season_id'];
                                     
                $pl_team_league_data = array();
                $data = array();
                $player_team = array();
                $match_player_uid = array();
                $player_key = $player_team_key = array();
                $player_id_arr = $player_uid_arr = $team_id_arr = array();
                
                //http://www.goalserve.com/getfeed/4bd6592cac4d492d1f2a08d729fa32b7/f1/f1-drivers
              
                $url = $this->api['api_url'].$this->api['subscription_key']."/".$game['league_abbr']."/drivers";
                //$url = "http://".$_SERVER['SERVER_NAME']."/cron/motorsport/drivers.xml";
               //echo $url;die;
                $player_data = @file_get_contents($url);
                if (!$player_data){
                    exit;
                }
                $player_data = xml2array($player_data);
                $player_array = @$player_data['standings']['drivers']['driver'];
                //echo "<pre>";print_r($player_array);die;
                foreach ($player_array as $key => $value) 
                {
                    //echo "<pre>";print_r($value);die;
                    $player_uid = $value['attr']['driver_id'];
                    $team_uid = $value['attr']['team_id'];

                    $plyer_name = explode("(", $value['attr']['name']);
                    //echo "<pre>";print_r($plyer_name); //die;
                    $plyer_country = explode(")", $plyer_name[1]);
                    //echo "<pre>";print_r($plyer_country[0]);  die;
                    //$player_name = trim($plyer_name[0]);
                    $player_name = trim($value['attr']['name']);

                    if(empty($team_ids[$team_uid])) 
                    {
                        continue;
                    }    

                    // Prepare Player data
                    $player_k = $sports_id.'_'.$player_uid;
                    $data[$player_k] = array(
                                    "player_uid"    => $player_uid,
                                    "sports_id"     => $sports_id,
                                    "full_name"     => $player_name,
                                    "first_name"    => $player_name,
                                    "last_name"     => "",
                                    "middle_name"   => "",
                                    "nick_name"     => $player_name,
                                    "position"      => "DR",
                                );

                    $player_key[] = $player_k;
                    $player_uid_arr[] = $player_uid;

                    $match_player_uid[] = $player_uid;
                    
                    // Prepare Player Team Relation Data
                    $pt_key = $player_uid.'|'.$sports_id.'|'.$season_id;
                    $player_team[$pt_key] = array(
                                        "player_id"     => 0,
                                        "team_id"       => $team_ids[$team_uid],
                                        "position"      => "DR",
                                        "is_deleted"    => 0,
                                        "is_published"  => 1,
                                        "player_status" => 1,
                                        "season_id"     => $season_id,
                                        "salary"        => $this->dr_salary[$value['attr']['pos']],
                                        "rank_number"   => $value['attr']['pos']
                                    );
                }
                
                if (!empty($data))
                {
                    //echo "<pre>";print_r($data);echo "<pre>";print_r($player_team);die;
                    $concat_key = 'CONCAT(sports_id,"_",player_uid)';
                    $update_ignore = array();
                    $player_string = implode(",",$player_uid_arr);
                    $where = 'sports_id ='.$sports_id.  
                                ' AND player_uid IN ('.$player_string.')';
                    $this->insert_or_update_on_exist($player_key,$data,$concat_key,PLAYER,'player_id',$update_ignore,$where);
                    // Get all Team of this sports of current year to map team_id in Team_League relation
                    $player_data = $this->get_player_team_data_by_game_id($season_id, $sports_id, $match_player_uid);
                    //echo "<pre>";print_r($player_data);die;
                    //echo "<pre>";print_r($player_data);die;
                    //$player_data = $this->get_all_table_data("player_id, sports_id, player_uid", PLAYER, array('sports_id' => $sports_id));
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
                            
                            
                                $player_team_k = $player_team_arr['season_id'].'_'.$player_team_arr['player_id'];
                                $player_team_data[$player_team_k] = $player_team_arr;
                                $player_team_key[] = $player_team_k; 

                                $player_id_arr[] = $player_team_arr['player_id'];
                                $team_id_arr[] = $player_team_arr['team_id'];
                            }else{
                                
                                $player_team_arr = $player_team[$player['player_uid'].'|'.$player['sports_id'].'|'.$season_id];
                                $player_team_arr['player_id'] = $player['player_id'];
                               
                                $player_team_k = $player_team_arr['season_id'].'_'.$player_team_arr['player_id'];

                               $player_team_data[$player_team_k] = $player_team_arr;
                                $player_team_key[] = $player_team_k; 

                                $player_id_arr[] = $player_team_arr['player_id'];
                                $team_id_arr[] = $player_team_arr['team_id']; 
                            }
                        }
                    }

                    //echo "<pre>";print_r($player_team_data);die;
                    
                    // Insert Team League Data
                    if (!empty($player_team_data))
                    {
                        $concat_key = 'CONCAT(season_id,player_id)';
                        
                        $update_ignore = array("position","salary");

                        //where in
                        $team_string = implode(",",array_unique($team_id_arr));
                        $player_string = implode(",",array_unique($player_id_arr));
                        $where = 'season_id ='.$season_id.  
                                ' AND team_id IN ('.$team_string.') AND player_id IN ('.$player_string.')'; 
                        $this->insert_or_update_on_exist($player_team_key,$player_team_data,$concat_key,PLAYER_TEAM,'player_team_id',$update_ignore,$where);
                    }
                    
                    echo "<br>Players inserted for [<b>".$league_id.' - '.$season_id."</b>]";
                }
            }
        }        
        
        //Make diffency team

        //Update display name if its empty or null
            $this->db->where('display_name', NULL);
            $this->db->set('display_name','full_name',FALSE);
            $this->db->update(PLAYER);
            $this->get_team_players($sports_id);
            echo "Player inserted";exit();

    }

    /**
     * @Summary: This function for use fetch the player data from third party API and store our local
     * database.
     * @access: public
     * @param:
     * @return:
     */
     
    public function get_team_players($sports_id)
    {   
        $day = 30;
        $next_date_time = date("Y-m-d H:i:s",strtotime('+'.$day.' day',strtotime(format_date())));
        $rs = $this->db->select("L.league_id,L.league_uid,L.sports_id,S.season_id,S.is_published,S.home_id,S.away_id,S.season_game_uid,L.league_abbr", FALSE)
                            ->from(LEAGUE." AS L")
                            ->join(SEASON." AS S", "S.league_id = L.league_id", 'INNER')
                            ->join(MASTER_SPORTS ." AS MS","MS.sports_id=L.sports_id AND MS.active = 1")
                            ->where("L.active", 1)
                            ->where("L.sports_id", $sports_id)
                            ->where('S.season_scheduled_date >', format_date())
                            ->where('S.season_scheduled_date <', $next_date_time)
                            ->get();
        //echo $this->db->last_query();     
        $current_game = $rs->result_array();
        //echo '<pre>Live Match : ';print_r($current_game);die;
        if (!empty($current_game))
        {
            //All team uid with team id
            $team_ids = $this->get_team_id_with_team_uid($sports_id);
                
            foreach ($current_game as $key => $game) 
            {
                $league_abbr = $game['league_abbr'];
                $league_id = $game['league_id'];
                $season_game_uid = $game['season_game_uid'];
                $season_id = $game['season_id'];
                                     
                $pl_team_league_data = array();
                $data = array();
                $player_team = array();
                $match_player_uid = array();
                $player_key = $player_team_key = array();
                $player_id_arr = $player_uid_arr = $team_id_arr = array();
                
                //http://www.goalserve.com/getfeed/4bd6592cac4d492d1f2a08d729fa32b7/f1/teams

                $url = $this->api['api_url'].$this->api['subscription_key']."/".$game['league_abbr']."/teams";
               // $url = "http://".$_SERVER['SERVER_NAME']."/cron/motorsport/teams.xml";
                //echo $url;die;
                $player_data = @file_get_contents($url);
                if (!$player_data){
                    exit;
                }
                $player_data = xml2array($player_data);
                $player_array = @$player_data['standings']['teams']['team'];
                //echo "<pre>";print_r($player_array);die;
                foreach ($player_array as $key => $value) 
                {
                    //echo "<pre>";print_r($value);die;
                    $player_uid = $value['attr']['id'];
                    $team_uid = $value['attr']['id'];

                    $player_name = trim($value['attr']['name']);

                    // Prepare Player data
                    $player_k = $sports_id.'_'.$player_uid;
                    $data[$player_k] = array(
                                    "player_uid"    => $player_uid,
                                    "sports_id"     => $sports_id,
                                    "full_name"     => $player_name,
                                    "first_name"    => $player_name,
                                    "last_name"     => "",
                                    "middle_name"   => "",
                                    "nick_name"     => $player_name,
                                    "position"      => "CR",
                                );

                    $player_key[] = $player_k;
                    $player_uid_arr[] = $player_uid;

                    $match_player_uid[] = $player_uid;
                    
                    // Prepare Player Team Relation Data
                    $pt_key = $player_uid.'|'.$sports_id.'|'.$season_id;
                    $player_team[$pt_key] = array(
                                        "player_id"         => 0,
                                        "team_id"    => $team_ids[$team_uid],
                                        "position"          => "CR",
                                        "is_deleted"        => 0,
                                        "is_published"      => 1,
                                        "player_status"     => 1,
                                        "season_id"   => $season_id,
                                        "salary"            => $this->cr_salary[$value['attr']['post']],
                                        "rank_number"       => $value['attr']['post']
                                    );
                }
                
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
                    $player_data = $this->get_player_team_data_by_game_id($season_game_uid, $sports_id, $match_player_uid);
                    //echo "<pre>";print_r($player_data);die;
                    //echo "<pre>";print_r($player_data);die;
                    //$player_data = $this->get_all_table_data("player_id, sports_id, player_uid", PLAYER, array('sports_id' => $sports_id));
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
                            
                            
                                $player_team_k = $player_team_arr['season_id'].'_'.$player_team_arr['player_id'];
                                $player_team_data[$player_team_k] = $player_team_arr;
                                $player_team_key[] = $player_team_k; 

                                $player_id_arr[] = $player_team_arr['player_id'];
                                $team_id_arr[] = $player_team_arr['team_id'];
                            }else{
                                
                                $player_team_arr = $player_team[$player['player_uid'].'|'.$player['sports_id'].'|'.$season_id];
                                $player_team_arr['player_id'] = $player['player_id'];
                               
                                $player_team_k = $player_team_arr['season_id'].'_'.$player_team_arr['player_id'];

                               $player_team_data[$player_team_k] = $player_team_arr;
                                $player_team_key[] = $player_team_k; 

                                $player_id_arr[] = $player_team_arr['player_id'];
                                $team_id_arr[] = $player_team_arr['team_id']; 
                            }
                        }
                    }

                    //echo "<pre>";print_r($player_team_data);die;
                    
                    // Insert Team League Data
                    if (!empty($player_team_data))
                    {
                        $concat_key = 'CONCAT(season_id,player_id)';
                        
                        $update_ignore = array("position","salary");

                        //where in
                        $team_string = implode(",",array_unique($team_id_arr));
                        $player_string = implode(",",array_unique($player_id_arr));
                        $where = 'season_id ='.$season_id.  
                                ' AND team_id IN ('.$team_string.') AND player_id IN ('.$player_string.')'; 
                        $this->insert_or_update_on_exist($player_team_key,$player_team_data,$concat_key,PLAYER_TEAM,'player_team_id',$update_ignore,$where);
                    }
                    
                    echo "<br>Players inserted for [<b>".$league_id.' - '.$season_id."</b>]";
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

    /**
     * [get_player_team_data_by_game_id :- GET GAME PLAYER TEAM FROM DATABASE FOR PARTICULAR GAME ID ]
     * @param  [type] $season_game_uid [description]
     * @return [type]          [description]
     */
    private function get_player_team_data_by_game_id($season_id, $sports_id, $match_player_uid) 
    {
        $rs = $this->db->select("P.player_id, P.sports_id, P.player_uid,PT.is_published,PT.feed_verified,PT.is_new", FALSE)
                ->from(PLAYER . " AS P")
                ->join(PLAYER_TEAM . " AS PT", "PT.player_id = P.player_id AND PT.season_id = '".$season_id."' ", 'LEFT')
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
    public function get_motorsports_season_match_for_score($sports_id = '')
    {
        $current_date_time = format_date();
        $this->db->select("S.season_id,S.year,S.week,S.season_game_uid,S.league_id,S.type, S.feed_date_time,S.season_scheduled_date,S.format,L.sports_id,L.league_abbr,S.end_scheduled_date")
            ->from(SEASON . " AS S")
            ->join(LEAGUE . " AS L", "L.league_id = S.league_id", "INNER")
            ->where("S.season_scheduled_date <='" . $current_date_time . "'");

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
        $current_game = $this->get_motorsports_season_match_for_score($sports_id);
        //echo '<pre>Live Match : ';print_r($current_game);die;
        if (!empty($current_game))
        {
            //All team uid with team id
            $team_ids = $this->get_team_id_with_team_uid($sports_id);
            //echo "DB -<pre>";print_r($team_ids);die;  
        
            //trasaction start
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
            $this->load->helper('queue');
            foreach ($current_game as $season_game)
            {
                $season_game_uid = $season_game['season_game_uid'];
                $league_id = $season_game['league_id'];
                $season_id = $season_game['season_id'];
                $league_abbr = $season_game['league_abbr'];
                //http://www.goalserve.com/getfeed/4bd6592cac4d492d1f2a08d729fa32b7/f1/f1-live
                $url = $this->api['api_url'].$this->api['subscription_key']."/".$league_abbr."/live";
                //$url = "http://".$_SERVER['SERVER_NAME']."/cron/motorsport/live.xml";
                //echo $url;die;
                $live_data = @file_get_contents($url);
                $live_data = xml2array($live_data);
                //echo '<pre>';print_r($live_data);die;
                if(!empty($live_data))
                {
                    $scores = @$live_data['scores']['tournament'];
                    if(isset($scores) && !empty($scores))
                    {
                        
                        $match_id = $scores['attr']['id'];
                        if($match_id == $season_game_uid)
                        {
                            $comm_data = array(
                                    "league_id"         => $league_id,
                                    "season_id"         => $season_id,
                                    "week"              => $season_game['week'],
                                    "scheduled_date"    => $season_game['season_scheduled_date'],
                                    "updated_at"        => format_date()                                      
                                            );

                            $r_player_data = array();
                            $score_keys = array();
                            //echo '1<pre>';print_r($scores);die;
                            //All player id with player id by season
                            $player_ids = $this->get_player_data_by_season_id($sports_id,$season_id);
                            //echo '1<pre>';print_r($player_ids);die;
                            foreach ($this->match_event as $key => $me) 
                            {
                                if(isset($scores[$me]) && $scores[$me] != '')
                                { 
                                    //echo '2<pre>';print_r($scores[$me]);die;
                                    $status = @$scores[$me]['attr']['status'];
                                    $distance = @$scores[$me]['attr']['distance'];
                                    $total_laps = @$scores[$me]['attr']['total_laps'];
                                    $fastest_lap_id = @$scores[$me]['attr']['fastest_lap_id'];
                                    $fastest_lap_time = @$scores[$me]['attr']['fastest_lap'];
                                    $laps_running = @$scores[$me]['attr']['laps_running'];
                                    if($me == 'race')
                                    {
                                        $prefix = "f";
                                    }elseif($me == 'first_qualification') 
                                    {
                                        $prefix = "q1";
                                    }elseif($me == 'second_qualification') 
                                    {
                                        $prefix = "q2";
                                    } elseif($me == 'third_qualification') 
                                    {
                                        $prefix = "q3";
                                    } elseif($me == 'qualification') 
                                    {
                                        $prefix = "q";
                                    }     

                                    $dr_array = array();
                                    $default_player_data = $this->_player_default_stats();
                                    foreach ($scores[$me]['results']['driver'] as $key => $driver) 
                                    {
                                       //echo '3<pre>';print_r($player_data);die;
                                        $player_uid = $driver['attr']['driver_id'];
                                        $player_id = @$player_ids[$player_uid];
                                        $team_id = @$team_ids[$driver['attr']['team_id']];
                                        if($player_id == '' || $team_id == '' )
                                        {
                                            continue;
                                        } 
                                        $score_k = $league_id.'_'.$season_id.'_'.$player_id;
                                       $player_data = array(
                                        "player_id" => $player_id,
                                        "team_id" => $team_id, 
                                        $prefix."_fastest_lap_id" => $fastest_lap_id,
                                        $prefix."_fastest_lap_time" => $fastest_lap_time,     
                                        $prefix."_laps" => intval($driver['attr']['laps']), 
                                        $prefix."_position" => intval($driver['attr']['pos']), 
                                        $prefix."_grid" => intval($driver['attr']['grid']), 
                                        $prefix."_pitstop_count" => intval($driver['attr']['pit']), 
                                        $prefix."_time" => $driver['attr']['time'], 
                                        $prefix."_status" => $status,
                                        $prefix."_total_laps" => $total_laps,

                                                        );
                                        $score_keys[] = $score_k;

                                        if(!empty($r_player_data[$player_id]))
                                        {
                                            $r_player_data[$player_id] = array_merge($r_player_data[$player_id],$player_data);
                                        }else{
                                            $r_player_data[$player_id] = array_merge($comm_data,$default_player_data,$player_data);
                                        }    
                                        
                                              
                                        //echo '<pre>';print_r($r_player_data);die;
                                    }
                                        
                                }    
                            } 
                            //echo '<pre>';print_r($r_player_data);die;
                            if(!empty($r_player_data))
                            {
                                $this->replace_into_batch(GAME_STATISTICS_MOTORSPORT, array_values($r_player_data));
                                //echo "<pre>";print_r($score_data);die;
                                echo "<pre>";echo "Match [<b>$season_id</b>]  data inserted";
                                if(strtolower($scores['race']['attr']['status']) == 'finished')
                                {
                                    $update_array['status'] = 2;
                                    $update_array['status_overview'] = 4;
                                    $update_array['match_closure_date'] = format_date();
                                    $this->db->where("league_id",$league_id);
                                    $this->db->where("season_id",$season_id);
                                    $this->db->update(SEASON,$update_array);
                                }    
                            }   
                        }    
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
        exit('Score cron run');     
    }

    public function get_old_scores($sports_id)
    {
        $current_game = $this->get_motorsports_season_match_for_score($sports_id);
        //echo '<pre>Live Match : ';print_r($current_game);die;
        if (!empty($current_game))
        {
            //All team uid with team id
            $team_ids = $this->get_team_id_with_team_uid($sports_id);
            //echo "DB -<pre>";print_r($team_ids);die;
            //trasaction start
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
            $this->load->helper('queue');
            foreach ($current_game as $season_game)
            {
                $season_game_uid = $season_game['season_game_uid'];
                $league_id = $season_game['league_id'];
                $season_id = $season_game['season_id'];
                $league_abbr = $season_game['league_abbr'];
                //http://www.goalserve.com/getfeed/4bd6592cac4d492d1f2a08d729fa32b7/f1/f1-results
                $url = $this->api['api_url'].$this->api['subscription_key']."/".$league_abbr."/".$league_abbr."-results";
                //$url = "http://".$_SERVER['SERVER_NAME']."/cron/motorsport/f1-results.xml";
                //echo $url;die;
                $live_data = @file_get_contents($url);
                $live_data = xml2array($live_data);
                //echo '<pre>';print_r($live_data);die;
                if(!empty($live_data))
                {
                    $tournament = @$live_data['scores']['tournament'];
                    if(isset($tournament) && !empty($tournament))
                    {
                       foreach ($tournament as $key => $scores) 
                       {
                            //echo '<pre>';print_r($scores);die;
                            $match_id = $scores['attr']['id'];
                            if($match_id == $season_game_uid)
                            {
                                $comm_data = array(
                                        "league_id"         => $league_id,
                                        "season_id"   => $season_id,
                                        "week"              => $season_game['week'],
                                        "scheduled_date"    => $season_game['season_scheduled_date'],
                                        "updated_at"        => format_date()
                                                                                        
                                                );

                                $r_player_data = array();
                                $score_keys = array();
                                //echo '1<pre>';print_r($scores);die;
                                //All player id with player id by season
                                $player_ids = $this->get_player_data_by_season_id($sports_id,$season_id);
                                foreach ($this->match_event as $key => $me) 
                                {
                                    if(isset($scores[$me]) && $scores[$me] != '')
                                    { 
                                        //echo '2<pre>';print_r($scores[$me]);die;
                                        $status = @$scores[$me]['attr']['status'];
                                        $distance = @$scores[$me]['attr']['distance'];
                                        $total_laps = @$scores[$me]['attr']['total_laps'];
                                        $fastest_lap_id = @$scores[$me]['attr']['fastest_lap_id'];
                                        $fastest_lap_time = @$scores[$me]['attr']['fastest_lap'];
                                        $laps_running = @$scores[$me]['attr']['laps_running'];
                                        if($me == 'race')
                                        {
                                            $prefix = "f";
                                        }elseif($me == 'first_qualification') 
                                        {
                                            $prefix = "q1";
                                        }elseif($me == 'second_qualification') 
                                        {
                                            $prefix = "q2";
                                        } elseif($me == 'third_qualification') 
                                        {
                                            $prefix = "q3";
                                        } elseif($me == 'qualification') 
                                        {
                                            $prefix = "q";
                                        }     

                                        $dr_array = array();
                                        $default_player_data = $this->_player_default_stats();
                                        $driver_list = @$scores[$me]['results']['driver'];
                                        if(!empty($driver_list))
                                        {    
                                            foreach ($driver_list as $key => $driver) 
                                            {
                                               //echo '3<pre>';print_r($player_data);die;
                                                $player_uid = $driver['attr']['driver_id'];
                                                $player_id = @$player_ids[$player_uid];
                                                $team_id = @$team_ids[$driver['attr']['team_id']];
                                                if($player_id == '' || $team_id == '' )
                                                {
                                                    continue;
                                                } 
                                                $score_k = $league_id.'_'.$season_id.'_'.$player_id;
                                               $player_data = array(
                                                "player_id" => $player_id,
                                                "team_id" => $team_id, 
                                                $prefix."_fastest_lap_id" => $fastest_lap_id,
                                                $prefix."_fastest_lap_time" => $fastest_lap_time,     
                                                $prefix."_laps" => intval($driver['attr']['laps']), 
                                                $prefix."_position" => intval($driver['attr']['pos']), 
                                                $prefix."_grid" => intval($driver['attr']['grid']), 
                                                $prefix."_pitstop_count" => intval($driver['attr']['pit']), 
                                                $prefix."_time" => $driver['attr']['time'], 
                                                $prefix."_status" => $status,
                                                $prefix."_total_laps" => $total_laps,

                                                                );
                                                $score_keys[] = $score_k;

                                                if(!empty($r_player_data[$player_id]))
                                                {
                                                    $r_player_data[$player_id] = array_merge($r_player_data[$player_id],$player_data);
                                                }else{
                                                    $r_player_data[$player_id] = array_merge($comm_data,$default_player_data,$player_data);
                                                }    
                                                
                                                      
                                                //echo '<pre>';print_r($r_player_data);die;
                                            }
                                        }
                                            
                                    }    
                                } 
                                //echo '<pre>';print_r($r_player_data);die;
                                if(!empty($r_player_data))
                                {
                                    $this->replace_into_batch(GAME_STATISTICS_MOTORSPORT, array_values($r_player_data));
                                    //echo "<pre>";print_r($score_data);die;
                                    echo "<pre>";echo "Match [<b>$season_id</b>]  data inserted";
                                    if(strtolower($scores['race']['attr']['status']) == 'finished')
                                    {
                                        $update_array['status'] = 2;
                                        $update_array['status_overview'] = 4;
                                        $update_array['match_closure_date'] = format_date();
                                        $this->db->where("league_id",$league_id);
                                        $this->db->where("season_id",$season_id);
                                        $this->db->update(SEASON,$update_array);
                                    }    
                                }   
                            }
                        }        
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
        exit();     
    }

    

    private function _player_default_stats()
    {
        $default_array = array();
        $default_array["q1_fastest_lap_time"] = ""; 
        $default_array["q1_laps"] = 0;
        $default_array["q1_position"] = 0;
        $default_array["q1_grid"] = 0;
        $default_array["q1_pitstop_count"] = 0;
        $default_array["q1_time"] =  "";
        $default_array["q1_status"] = "";
        $default_array["q2_fastest_lap_time"] = ""; 
        $default_array["q2_laps"] = 0;
        $default_array["q2_position"] = 0;
        $default_array["q2_grid"] = 0;
        $default_array["q2_pitstop_count"] = 0;
        $default_array["q2_time"] = "";
        $default_array["q2_status"] = "";
        $default_array["q3_fastest_lap_time"] = ""; 
        $default_array["q3_laps"] = 0;
        $default_array["q3_position"] = 0;
        $default_array["q3_grid"] = 0 ;
        $default_array["q3_pitstop_count"] = 0;
        $default_array["q3_time"] = "";
        $default_array["q3_status"] = "";
        $default_array["f_fastest_lap_time"] = 0;
        $default_array["f_laps"] = 0;
        $default_array["f_position"] = 0;
        $default_array["f_grid"] = 0;
        $default_array["f_pitstop_count"] = 0;
        $default_array["f_time"] = "";
        $default_array["q_fastest_lap_id"] = 0;
        $default_array["q1_fastest_lap_id"] =  0;
        $default_array["q2_fastest_lap_id"] = 0;
        $default_array["q3_fastest_lap_id"] =  0;
        $default_array["f_fastest_lap_id"] =  0;
        $default_array["q_total_laps"] =  0;
        $default_array["q1_total_laps"] =  0;
        $default_array["q2_total_laps"] =  0;
        $default_array["q3_total_laps"] =  0;
        $default_array["f_total_laps"] =  0;
        return $default_array;
    }

    /**
     * function used for calculate player fantasy points
     * @param int $sports_id
     * @return array
     */
    public function calculated_fantasy_score($sports_id)
    {
        // Get All Live Games List 
        $current_game = $this->get_motorsports_season_match_for_calculate_points($sports_id);
        //echo "<pre>"; print_r($current_game);die;
        if (!empty($current_game))
        {
            $formula = $this->get_scoring_rules($sports_id);
            //echo "<pre>";print_r($formula);die;
            foreach ($current_game as $season_game)
            {   
                $this->calculate_motorsports_fantasy_points($sports_id,$season_game,$formula);      
            }// End of Team loop  
        } // End of Empty team check
        exit();
    }

    private function calculate_motorsports_fantasy_points($sports_id,$season_game,$formula)
    {
        // Get All Scoring Rules
        $league_id = $season_game['league_id'];
        $season_game_uid        = $season_game['season_game_uid'];
        $season_id        = $season_game['season_id'];
        $all_player_scores      = array();
        $all_player_testing_var = array();
        $total_final_breakdown = array();
        $player_final_breakdown = array();
        
        // Get Match Scoring Statistics to Calculate Fantasy Score
        $player_game_stats = $this->get_game_statistics_by_game_id($season_id,$sports_id,$league_id);
        $team_game_stats = $this->get_game_statistics_team_by_game_id($season_id,$sports_id,$league_id);
        //echo "<pre>";print_r($team_game_stats);die;
        $game_stats = array_merge($player_game_stats,$team_game_stats);
        //$game_stats = $team_game_stats;
        //echo "<pre>";print_r($game_stats);die;
        if (!empty($game_stats))
        {   $all_player_scores = array();
            foreach($game_stats as $stats)
            {
                $score              = 0.0;
                $bonus_score        = 0.0;
                $normal_score       = 0.0;
                $each_player_score  = array();
                   
                //$score_key          = array();
                $final_score_key    = array();
                  
                $score_key = json_decode(@$all_player_scores[$stats['player_id']]['break_down'],TRUE);
                // for breakdown dated 30 may
                $total_final_breakdown = array();

                $final_break_down = array();
                $final_break_down["qualifying"]          = array();
                $final_break_down["race"]         = array();
                
                $each_player_score['season_id']         = $stats['season_id'];
                $each_player_score['player_id']         = $stats['player_id'];
                $each_player_score['scheduled_date']    = $stats['scheduled_date'];
                $each_player_score['week']              = $stats['week'];
                $each_player_score['league_id']         = $league_id;
                $each_player_score['normal_score']      = $normal_score;
                $each_player_score['score']             = $score;

              /* # Qualifying SCORING RULE ## */
        
               
                $q = array_column($game_stats, "q_status",'q_status');
                $q1 = array_column($game_stats, "q1_status",'q1_status');
                $q2 = array_column($game_stats, "q2_status",'q2_status');
                $q3 = array_column($game_stats, "q3_status",'q3_status');
                $f = array_column($game_stats, "f_status",'f_status');
                                               
                $race_array = array("q","q1","q2","q3","f");
                foreach ($race_array as $key => $race_type) 
                {
                    $type = "qualifying";
                    if($race_type == 'f')
                    {
                        $type = "race";
                    } 

                    //qualifying finish
                    if(isset($$race_type['Finished']) && strtolower($stats[$race_type.'_status']) == 'finished' && (str_contains(strtolower($stats[$race_type.'_time']),"dnf") != 1 && str_contains(strtolower($stats[$race_type.'_time']),"lap") != 1))
                    {   
                        $score                     = $score +  $formula[$type][$race_type.'_FINISH'];
                        
                        if (array_key_exists($stats['player_id'], $all_player_scores))
                        {
                            $break_down = json_decode($all_player_scores[$stats['player_id']]['break_down'],TRUE);
                            
                            //$score_key = $break_down;
                            //if($stats['player_id'] == 1024){echo $stats['team_id']."if me aya type | $type | key ".$race_type.'_FINISH <br>';}
                            //echo "<pre>";print_r($break_down);die;
                            $score_key[$type][$race_type.'_FINISH']    = intval(@$break_down[$type][$race_type.'_FINISH']) + $formula[$type][$race_type.'_FINISH'];
                            //echo "<pre>";print_r($break_down);//die;
                            //$score_key = array_merge($score_key,$break_down);
                            

                        }else{
                            //if($stats['player_id'] == 1024){echo $stats['team_id']."else aya type | $type | key ".$race_type.'_FINISH <br>';}
                            $score_key[$type][$race_type.'_FINISH']    = $formula[$type][$race_type.'_FINISH'];
                            
                        }    

                        
                    } 
                }

                //only for qyalifying or race
                $race_array = array("q","q3","f");
                foreach ($race_array as $key => $race_type) 
                {
                    $type = "qualifying";
                    if($race_type == 'f')
                    {
                        $type = "race";
                        $q_race_type = "f";
                    }

                    if($race_type == 'q3')
                    {
                        $q_race_type = "q";
                    }     

                    // not qualified
                    //echo $race_type;die;
                    if(isset($$race_type['Finished']) && $stats['position'] == 'DR' && strtolower($$race_type['Finished']) == 'finished' && ($stats[$race_type.'_time'] == "" && $stats[$race_type.'_status'] == "") )
                    {
                        $score                      = $score +  $formula[$type][$q_race_type.'_NOT_QUALIFY'];
                        $score_key[$type][$q_race_type.'_NOT_QUALIFY']    = $formula[$type][$q_race_type.'_NOT_QUALIFY'];

                        
                    }
                    //Position with team mate
                    if(isset($$race_type['Finished']) && $stats['position'] == 'DR')
                    {
                        //check position with team mate
                        $team_mate_position = $this->get_game_statistics_for_team_mate_by_game_id($stats['team_id'],$season_id,$sports_id,$league_id,$race_type);
                        //echo "<pre>";print_r($team_mate_position);continue;die;
                        if($team_mate_position['0']['player_id'] == $stats['player_id'] && count($team_mate_position) >= 2)
                        {
                          
                            $score                      = $score +  $formula[$type][$q_race_type.'_AHEAD_TEAM_MATE'];
                            $score_key[$type][$q_race_type.'_AHEAD_TEAM_MATE']    = $formula[$type][$q_race_type.'_AHEAD_TEAM_MATE'];
                        }    
                    }

                   //position rank for all type of race
                    //echo "<pre>";echo $race_type;
                    $position = intval(@$stats[$race_type.'_position']);
                     
                    if($position > '0' && $position <= '10')
                    {
                        $pos = $q_race_type.'_POS_'.$position;
                        $score                      = $score +  $formula[$type][$pos];
                        if (array_key_exists($stats['player_id'], $all_player_scores) )
                        {
                            $break_down = json_decode($all_player_scores[$stats['player_id']]['break_down'],TRUE);
                            
                            

                            if($stats['position'] == 'CR')
                            {
                                $score_key[$type]['POS']     = @$break_down[$type]['POS'] +  $formula[$type][$pos];
                            }else{
                                $score_key[$type]['POS']     = @$break_down[$type]['POS'] + ($formula[$type][$pos]);
                            }
                            
                        }else{
                            
                            if($stats['position'] == 'CR')
                            {
                                $score_key[$type]['POS']     = $formula[$type][$pos];
                            }else{
                                $score_key[$type][$pos]     = $formula[$type][$pos];
                            }    
                           
                        }


                        
                    }

                    //fastest lap 
                    if($stats['position'] == 'DR' && $stats['player_id'] == $stats[$race_type.'_fastest_lap_id'] )
                    {
                        $score                = $score +  $formula[$type][$q_race_type.'_FASTEST_LAP'];
                        $score_key[$type][$q_race_type.'_FASTEST_LAP'] = $formula[$type][$q_race_type.'_FASTEST_LAP'];

                        
                    }  

                    //
                   /* if($stats['position'] == 'DR' && $stats['player_id'] == $stats[$race_type.'_fastest_lap_id'] && $type == 'race')
                    {
                        $score                = $score +  $formula[$type]['FASTEST_LAP'];
                        $score_key['FASTEST_LAP'] = $formula[$type]['FASTEST_LAP'];
   
                    }  */

                }

                //start and finish pos

                if(strtolower($stats['f_status']) == 'finished' && (str_contains(strtolower($stats['f_time']),"dnf") != 1 && str_contains(strtolower($stats['f_time']),"lap") != 1))
                {   
                    $type = 'race';
                    $max_pos_lost = "10";
                    $lost_position = 0;
                    if(($stats['q3_position'] > '0' && $stats['q3_position'] <= '10') 
                        && ($stats['q3_position'] - $stats['f_position']) < 0)
                    {
                        $lost_position = ($stats['f_position']-$stats['q3_position'])*$formula[$type][$race_type.'_START_RACE_WITHIN_10'];
                    }    
                    if(abs($lost_position) >= $max_pos_lost)
                    {
                        $lost_position = $max_pos_lost;
                    }    
                    if($lost_position != '0')
                    {    

                        $score                     = $score +  $formula[$type][$race_type.'_START_RACE_WITHIN_10'];
                        $score_key[$type][$q_race_type.'_START_RACE_WITHIN_10']    = $formula[$type][$race_type.'_START_RACE_WITHIN_10'];
                    }    
                } 
                
                
                $each_player_score['score']         = $score;
                $each_player_score['normal_score']  = $score;
                $each_player_score['break_down']    = json_encode($score_key);
                $each_player_score['final_break_down'] = json_encode($score_key);

                //$player_final_breakdown[$stats['player_id']]  = $final_break_down;
                if (array_key_exists($stats['player_id'], $all_player_scores) && array_key_exists('normal_score', $all_player_scores[$stats['player_id']]))
                {
                    $each_player_score['normal_score'] = $all_player_scores[$stats['player_id']]['normal_score'] = $all_player_scores[$stats['player_id']]['normal_score'] + $each_player_score['normal_score'];
                    $each_player_score['score'] = $all_player_scores[$stats['player_id']]['score'] = $all_player_scores[$stats['player_id']]['score'] + $each_player_score['score'];

                    $each_player_score['break_down'] = json_encode(
                                    array_merge(
                                        json_decode($all_player_scores[$stats['player_id']]['break_down'], true),
                                        json_decode($each_player_score['break_down'], true)
                                    )
                                );

                    $each_player_score['final_break_down'] = json_encode(
                                    array_merge(
                                        json_decode($all_player_scores[$stats['player_id']]['break_down'], true),
                                        json_decode($each_player_score['break_down'], true)
                                    )
                                );
                }

                $all_player_scores[$stats['player_id']] = $each_player_score;
  
              
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
            $this->db->update(GAME_PLAYER_SCORING,array("normal_score"=>"0","bonus_score"=>"0","score"=>"0","break_down"=>NULL,"final_break_down"=>NULL));
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
     * function used for get season list for player point calculation
     * @param int $sports_id
     * @return array
     */
    public function get_motorsports_season_match_for_calculate_points($sports_id = '') {
        $current_date_time = format_date();
        $past_time = date("Y-m-d H:i:s", strtotime($current_date_time . " -".MATCH_SCORE_CLOSE_DAYS." days"));
        $close_date_time = date("Y-m-d H:i:s", strtotime($current_date_time . " -".CONTEST_CLOSE_INTERVAL." minutes"));
        $this->db->select("S.season_id,S.home_id,S.away_id,S.year,S.api_week,S.week, S.season_game_uid,S.league_id,S.feed_date_time,S.season_scheduled_date, S.format, S.type, L.sports_id")
                ->from(SEASON . " AS S")
                ->join(LEAGUE . " AS L", "L.league_id = S.league_id", "left")
                ->where("L.active", '1')
                ->where("S.match_status", '0')
                ->where("S.season_scheduled_date <='".$current_date_time."'")
                ->where("(S.match_closure_date IS NULL OR S.match_closure_date > '".$close_date_time."')")
                ->where("S.season_scheduled_date >= ", $past_time);
        
        if (!empty($sports_id)) {
            $this->db->where("L.sports_id", $sports_id);
        }
        
        $this->db->group_by("S.season_game_uid");
        $sql = $this->db->get();
        $matches = $sql->result_array();
        return $matches;
    }  

    /**
     * function used for Save individual player score on table based on game id
     * @param array $player_score
     * @return boolean
     */
    protected function save_player_scoring($player_score)
    {
        $table_value = array();
        $sql = "REPLACE INTO ".$this->db->dbprefix(GAME_PLAYER_SCORING)." (season_id, player_id, week, scheduled_date, normal_score, score, league_id, break_down,final_break_down)
                            VALUES ";

        foreach ($player_score as $player_unique_id => $value)
        {
            $main_score = $value['normal_score'];

            $str = " ('".$value['season_id']."','".$value['player_id']."','".$value['week'] ."','".$value['scheduled_date']."','".$value['normal_score']."','".$main_score."','".$value['league_id']."','".$value['break_down']."','".$value['final_break_down']."')";

            $table_value[] = $str;
        }

        $sql .= implode(", ", $table_value);

        $this->db->query($sql);
        return true;
    }

    /**
     * function used for get match statistics data by game id
     * @param string $season_game_uid
     * @param int $sports_id
     * @param int $league_id
     * @return array
     */
    private function get_game_statistics_by_game_id($season_id,$sports_id,$league_id)
    {
        $sql = $this->db->select("GSM.*,PT.position", FALSE)
                ->from(GAME_STATISTICS_MOTORSPORT . " AS GSM")
                ->join(PLAYER . " AS P", "P.player_id = GSM.player_id AND P.sports_id = $sports_id ", 'INNER')
                ->join(PLAYER_TEAM . " AS PT", "PT.player_id = P.player_id AND PT.season_id = '".$season_id."' ", 'INNER')
                ->where("GSM.season_id", $season_id)
                ->where("GSM.league_id", $league_id)
                //->where("GSM.player_uid","1024")
                ->group_by("P.player_id")
                ->get();

        $result = $sql->result_array();
        return $result;
    }

    private function get_game_statistics_team_by_game_id($season_id,$sports_id,$league_id)
    {
        $sql = $this->db->select("GSM.*,P.player_id,'CR' AS position", FALSE)
                ->from(GAME_STATISTICS_MOTORSPORT . " AS GSM")
                ->join(PLAYER_TEAM . " AS PT", "PT.player_id = GSM.player_id AND PT.season_id = '".$season_id."' ", 'INNER')
                ->join(TEAM . " AS T", "T.team_id = PT.team_id", 'INNER')
                ->join(PLAYER . " AS P", "P.player_uid = T.team_uid AND P.sports_id = $sports_id ", 'LEFT')
                ->where("GSM.season_id", $season_id)
                ->where("GSM.league_id", $league_id)
                //->where("GSM.team_uid","1024")
                //->group_by("P.player_id")
                ->get();

        $result = $sql->result_array();
        return $result;
    }


    private function get_game_statistics_for_team_mate_by_game_id($team_id,$season_id,$sports_id,$league_id,$stage)
    {
        $this->db->select("GSM.player_id", FALSE)
                ->from(GAME_STATISTICS_MOTORSPORT . " AS GSM")
                ->join(PLAYER . " AS P", "P.player_id = GSM.player_id AND P.sports_id = $sports_id ", 'INNER')
                ->join(PLAYER_TEAM . " AS PT", "PT.player_id = P.player_id AND PT.season_id = '".$season_id."' ", 'INNER')
                ->where("GSM.season_id", $season_id)
                ->where("GSM.league_id", $league_id)
                ->where("GSM.team_id", $team_id);
        if($stage == 'q')
        {
            $this->db->order_by("GSM.q_position", "ASC");
        }
        if($stage == 'q1')
        {
            $this->db->order_by("GSM.q1_position", "ASC");
        }
        if($stage == 'q2')
        {
            $this->db->order_by("GSM.q2_position", "ASC");
        }
        if($stage == 'q3')
        {
            $this->db->order_by("GSM.q3_position", "ASC");
        }
        if($stage == 'f')
        {
            $this->db->order_by("GSM.f_position", "ASC");
        }
        //$this->db->group_by("GSM.team_uid");      
        $sql = $this->db->get();
        $result = $sql->result_array();
        //echo "<pre>";print_r($result);die;
        return $result;
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
            $this->calculate_motorsports_fantasy_points($sports_id,$season_game,$formula);  
        } // End of Empty team check
        exit();
    }

    public function get_season_match_details($season_game_uid,$league_id)
    {
        $this->db->select("S.season_id,S.home_id,S.away_id,S.year,S.api_week,S.week, S.season_game_uid,S.league_id,S.type,S.feed_date_time, S.season_scheduled_date, S.format, L.sports_id,L.league_abbr")
                ->from(SEASON." AS S")
                ->join(LEAGUE." AS L", "L.league_id = S.league_id", "left");

        if(!empty($season_game_uid))
        {
            $this->db->where("S.season_game_uid", $season_game_uid);
        }
        if(!empty($league_id))
        {
            $this->db->where("S.league_id", $league_id);
        }
        $this->db->where("L.active", '1');
        $this->db->group_by("S.season_game_uid");
        $sql = $this->db->get();
        //echo $this->db->last_query();die;
        $matches = $sql->row_array();

        return $matches;
    }

    
}

/* End of file  */
/* Location: ./application/modules/motorsport/ */
