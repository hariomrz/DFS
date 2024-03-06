<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
class Goalserve_model extends MY_Model {

    public $time_zone = "America/New_York"; //For EST
    public $time_in_time_zone = "-00:00";
   
    public function __construct() 
    {
        parent::__construct();
        $this->api = $this->get_tennis_config_detail('goalserve');
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
        //http://www.goalserve.com/getfeed/a28fc23ec9e947cc94fed2334b6ce52c/tennis_scores/leagues
        $url = $this->api['api_url'].$this->api['subscription_key']."/tennis_scores/leagues";
        //echo $url;die;
        $league_data = @file_get_contents($url);
        if (!$league_data){
            exit;
        }
        $league_array = xml2array($league_data);
        $leagues = @$league_array['leagues']['league'];
        //echo "<pre>";print_r($leagues);die;
        if(!empty($leagues))
        {    
            $league_keys = array();
            foreach ($leagues as $key => $value) 
            {
                $season         = $value['attr']['season'];
                $country        = $value['attr']['country'];
                $league_name    = $value['attr']['name'];
                if($value['attr']['location'] != "")
                {
                    $league_name    = $value['attr']['name'].','.$value['attr']['location'];
                }    
                $league_uid     = $value['attr']['id'];
                if($season == $this->api['year'] && (!str_contains($country, 'Doubles') && !str_contains($country, 'Mix')) && (str_contains($country, 'Atp') || str_contains($country, 'Wta')))
                {    
                    //for images
                    $league_image = "mix_tennis.png";
                    if(str_contains($country, 'Atp'))
                    {
                        $league_image = "tennis_man.png";
                    }elseif(str_contains($country, 'Wta'))
                    {
                        $league_image = "tennis_women.png";
                    }    

                    //echo "<pre>";print_r($value);die;
                    $key = $sports_id.'_'.$league_uid;
                    $data[$key] = array(
                                    "league_uid"    => @$league_uid,
                                    "league_abbr"   => @$league_name,
                                    "league_name"   => @$league_name,
                                    "league_display_name" => @$league_name,
                                    "league_schedule_date" => format_date(),
                                    //"league_last_date" => '',
                                    "image" => $league_image,
                                    "sports_id" => $sports_id,
                                    "active" => 0
                                );
                    $league_keys[] = $key;
               }     
            }
            if (!empty($data))
            {
                //echo "<pre>";print_r($data);die;
                $concat_key = 'CONCAT(sports_id,"_",league_uid)';
                $update_ignore = array("active","league_schedule_date");
                $this->insert_or_update_on_exist($league_keys,$data,$concat_key,LEAGUE,'league_id',$update_ignore);

                //Update league active flag
                $sql = $this->db->select("updated_date")
                        ->from(LEAGUE)
                        ->where("sports_id", $sports_id)
                        ->where("active", 1)
                        ->order_by("updated_date",'DESC')
                        ->limit(1)
                        ->get();
                $updated_date = $sql->row('updated_date');
                //echo "<pre>";print_r($updated_date);die;
                $this->db->where('sports_id',$sports_id);
                $this->db->where('updated_date >',$updated_date);
                $this->db->where('active', 0);
                $this->db->set('active',1);
                $this->db->update(LEAGUE);
                
                echo "All leagues (series) are inserted.";
            }
        }    
        exit(); 
    }


     /**
     * function used for fetch all players from feed
     * @param int $sports_id
     * @return boolean
     */
    public function get_players($sports_id)
    {
        //check sports active or not
        $sports_data = $this->get_single_row("sports_id", MASTER_SPORTS, array('sports_id' => $sports_id,"active" => '1'));
        if(!isset($sports_data['sports_id']) || $sports_data['sports_id'] == '')
        {
            exit('Sport not active');
        }
        //https://www.goalserve.com/getfeed/a28fc23ec9e947cc94fed2334b6ce52c/tennis_scores/atp
        //https://www.goalserve.com/getfeed/a28fc23ec9e947cc94fed2334b6ce52c/tennis_scores/wta
        $tennis_association = array("atp","wta");
        foreach ($tennis_association as $key => $association) 
        {
            $url = $this->api['api_url'].$this->api['subscription_key']."/tennis_scores/".$association;
            //echo $url;die;
            $player_data = @file_get_contents($url);
            if (!$player_data){
                exit;
            }
            
            $player_array = xml2array($player_data);
            $player_array = @$player_array['standings']['player'];
            //echo "<pre>";print_r($player_array);die;
            if(!empty($player_array))
            {    
                $data  = array();
                $player_keys = array();
                $team_data  = array();
                $team_keys = array();
                foreach ($player_array as $key => $value) 
                {
                    //echo "<pre>";print_r($value);die;
                    $player_uid = $value['attr']['id'];
                    if(empty($player_uid)){continue;}
                    $key = $sports_id.'_'.$player_uid;
                    $data[$key] = array(
                                    "player_uid"    => $player_uid,
                                    "sports_id"     => $sports_id,
                                    "full_name"     => $value['attr']['name'],
                                    "first_name"    => $value['attr']['name'],
                                    "nick_name"     => $value['attr']['name'],
                                    "country"       => $value['attr']['country'],
                                    "rank"          => $value['attr']['rank'],
                                    "points"        => $value['attr']['points'],
                                );
                        $player_keys[] = $key;
                    //for Team
                        $team_uid = ucfirst($value['attr']['country']);
                        if($team_uid ==""){$team_uid = $value['attr']['name'];}
                        $team_k = $sports_id.'_'.$team_uid;
                        $team_data[$team_k] = array(
                                        "sports_id"     => $sports_id,
                                        "team_uid"      => $team_uid,
                                        "team_abbr"     => strtoupper($team_uid),
                                        "team_name"     => $team_uid,
                                        "display_team_abbr" => strtoupper($team_uid),
                                        "display_team_name" => $team_uid,
                                        "year"          => $this->api['year']
                                    );
                        $team_keys[] = $team_k;    
                }
                //echo "<pre>";print_r($team_data);die;
                if (!empty($data))
                {
                    //echo "<pre>";print_r($data);die;
                    $concat_key = 'CONCAT(sports_id,"_",player_uid)';
                    $this->insert_or_update_on_exist($player_keys,$data,$concat_key,PLAYER,'player_id');
                    echo "<pre>";echo "All Player are inserted for: ".$association;
                }

                if(isset($team_data) && count($team_data) > 0)
                {
                  // Insert Team data 
                    $concat_key = 'CONCAT(sports_id,"_",team_uid)';
                    $this->insert_or_update_on_exist($team_keys,$team_data,$concat_key,TEAM,'team_id');
                    echo "<pre>";echo "Teams inserted for: ".$association;
                }
            }
        } 

        //Update display name if its empty or null
            $this->db->where('sports_id', $sports_id);
            $this->db->where('display_name', NULL);
            $this->db->set('display_name','full_name',FALSE);
            $this->db->update(PLAYER);
        //Upload player images
        $this->get_players_image($sports_id);    
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
        $sql = $this->db->select('league_id,league_uid,league_name,league_abbr,sports_id')
                    ->from(LEAGUE)
                    ->where('sports_id', $sports_id)
                    ->where('active', 1)
                    ->order_by('league_schedule_date', "DESC")
                    ->group_start()
                    ->or_where('league_last_date', NULL)
                    ->or_where('league_last_date >=', format_date())
                    ->group_end()
                    ->get();
        $league_data = $sql->result_array();

        //echo "<pre>";print_r($league_data);die;
        if (!empty($league_data))
        {
            
            foreach ($league_data as $league)
            {
                $league_id = $league['league_id'];
                $league_uid = $league['league_uid'];
                //http://www.goalserve.com/getfeed/a28fc23ec9e947cc94fed2334b6ce52c/tennis_scores/16126
                $url = $this->api['api_url'].$this->api['subscription_key']."/tennis_scores/".$league_uid;
                
                //echo $url;die;
                $shedule_data = @file_get_contents($url);
                if (!$shedule_data){
                    continue;
                }
                $season_array = xml2array($shedule_data);
                $season_week = @$season_array['results']['tournament']['week'];
                //echo "<pre>";print_r($season_week);die;
                if(!empty($season_week))
                {
                    $game_ids = array();
                    if(!array_key_exists('0', $season_week))
                    {
                        $temp = $season_week;
                        $season_week = array();
                        $season_week[0] = $temp; 
                    }
                    //echo "<pre>";print_r($season_week);die;
                    foreach ($season_week as $key => $season_mtches) 
                    {
                        if(!empty($season_mtches['match']))
                        {   
                            $match_array = array();
                            $season_match_array = array();
                            $match_keys = array();
                            $season_match_keys = array();
                            $final_match_array = array();
                            $player_team = array();
                            $player_team_key = array();
                            $game_times = array();

                            if(!array_key_exists('0', $season_mtches['match']))
                            {
                                $temp = $season_mtches['match'];
                                $season_mtches['match'] = array();
                                $season_mtches['match'][0] = $temp; 
                            }

                            //echo "<pre>";print_r($season_mtches['match']);continue;die;
                            $tournament_name = @$season_mtches['attr']['number'];
                            $qualification = @$season_mtches['attr']['qualification'];
                            $first_match = $season_mtches['match'][0]['attr']['id'];
                            $season_game_uid = md5(str_replace(" ","",strtolower($tournament_name.$qualification.$first_match)));
                            $type = "GS";
                            /*if(str_contains(strtolower($tournament_name),'quarter-finals'))
                            {
                                $type = "QF";
                            }elseif(str_contains(strtolower($tournament_name),'semi-finals'))
                            {
                                $type = "SF";
                            }elseif(str_contains(strtolower($tournament_name),'final')  && !str_contains(strtolower($tournament_name),'finals'))
                             {
                                $type = "FL";
                            } */  


                            //echo "<pre>";print_r($season_mtches['match']);die;
                            foreach ($season_mtches['match'] as $key => $value) 
                            {
                               
                               //get all player
                                $player_uids = array(@$value['player']['0']['attr']['id'],@$value['player']['1']['attr']['id']); 
                                $player_list = $this->get_player_id_by_player_uid($sports_id,$player_uids);
                                //echo "<pre>";print_r($player_list);die;
                                //echo "<pre>";print_r($value);die;
                                if(@$value['attr']['id'] == ""){continue;}
                                $start_date = @$value['attr']['date'].' '.@$value['attr']['time'];                               
                                @date_default_timezone_set($this->time_zone);
                                $scheduled_date_time = date('Y-m-d H:i:s', strtotime($start_date));
                                $temp_date = explode(" ", $scheduled_date_time);
                                $scheduled_date_time = $temp_date[0] . 'T' . $temp_date[1] . $this->time_in_time_zone;
                                @date_default_timezone_set(DEFAULT_TIME_ZONE);
                                $season_scheduled_date = date('Y-m-d H:i:s', strtotime($scheduled_date_time));
                                $scheduled_date = date('Y-m-d', strtotime($season_scheduled_date));
                                $year = date('Y', strtotime($season_scheduled_date));
                                
                                if ($season_scheduled_date < format_date()) 
                                {
                                   continue;
                                }
                                $match_id = @$value['attr']['id'];
                                $game_ids[$match_id]   = $match_id.'_'.strtotime($season_scheduled_date);
                                
                                $home_id = @$player_list[$value['player']['0']['attr']['id']];
                                $away_id = @$player_list[$value['player']['1']['attr']['id']];
                                if($home_id == '')
                                {
                                   $player_name = $value['player']['0']['attr']['name'];
                                   $this->db->insert(PLAYER,array("sports_id"=>$sports_id,"player_uid"=>$value['player']['0']['attr']['id'] , "full_name"=>$player_name,"first_name"=>$player_name,"nick_name"=>$player_name,"country"=>"World","display_name"=>$player_name));
                                    $home_id = $this->db->insert_id();
                                } 
                                if($away_id == '')
                                {
                                   $player_name = $value['player']['1']['attr']['name'];
                                   $this->db->insert(PLAYER,array("sports_id"=>$sports_id,"player_uid"=>$value['player']['1']['attr']['id'] , "full_name"=>$player_name,"first_name"=>$player_name,"nick_name"=>$player_name,"country"=>"World","display_name"=>$player_name));
                                   $away_id = $this->db->insert_id();
                                }   

                                $season_match_array[$match_id] =  array(
                                                            "home_id" => $home_id,
                                                            "away_id" => $away_id,     
                                                            "match_id" => $match_id,
                                                            "scheduled_date" => $season_scheduled_date,
                                                            "deleted" => 0,
                                                        );
                                
                                $game_times[] = $season_scheduled_date;
                           
                            } 
                            if(!empty($game_times)) 
                            {   
                                //echo "<pre>";print_r($game_times);
                                sort($game_times);
                                $match_event = count($game_times);
                                if($match_event >= 5)
                                {
                                    $type = "GS";
                                }elseif($match_event >= 4)
                                {
                                    $type = "QF";
                                }elseif($match_event >= 2)
                                {
                                    $type = "SF";
                                }elseif($match_event == 1)
                                {
                                    $type = "FL";
                                } 

                                /* Code update for game uid issue and count when match annonced continue and same time  */
                                if(empty($game_times))
                                {
                                    //continue;
                                }

                                $old_season_info = $this->db->select("S.season_id,S.season_game_uid,
                                    S.season_scheduled_date,S.is_published")
                                                    ->from(SEASON." AS S")
                                                    ->join(SEASON_MATCH." AS SM","SM.season_id=S.season_id")
                                                    ->where_in("SM.match_id", array_column($season_match_array,"match_id"))
                                                    ->group_by("S.season_id")
                                                    ->order_by("S.season_id")
                                                    ->get()
                                                    ->row_array();
                                //echo "<pre>";print_r($old_season_info); 
                                if(!empty($old_season_info))
                                {
                                    $season_game_uid = $old_season_info["season_game_uid"];
                                    if ($old_season_info["season_scheduled_date"] < format_date()) 
                                    {
                                       continue;
                                    }

                                }

                                $is_published = 0;
                                if(!empty($old_season_info) && $old_season_info["is_published"] == "1")
                                {
                                   $is_published = 1;     
                                }    

                                //echo "<pre>";print_r($game_times);                  
                                //echo "<pre>";print_r($season_game_uid);
                                //echo "<pre>";print_r(array_column($season_match_array,"match_id"));continue;die;
                                //echo "<pre>";print_r($game_times);continue;die;
                                /*end match issue */

                                //if(current($game_times) > format_date())
                                //{    
                                    $matchkeys = $league_id.'_'.$season_game_uid;
                                    $match_array[$matchkeys] = array(
                                                "league_id"         => $league_id,
                                                "season_game_uid"   => $season_game_uid,
                                                "title"             => $tournament_name,
                                                "subtitle"          => $tournament_name,
                                                "tournament_name"   => $tournament_name,
                                                "year"              => $year,
                                                "type"              => $type,
                                                "feed_date_time"    => current($game_times),
                                                "season_scheduled_date" => current($game_times),
                                                "scheduled_date"    => date('Y-m-d', strtotime(current($game_times))),
                                                "end_scheduled_date" => end($game_times),
                                                "is_tour_game"     => '1',
                                                "match_event" => intval($match_event)     
                                                    );
                                    $match_keys[] = $matchkeys;
                                    //echo "<pre>";print_r($match_array);continue;die;
                                    //insert season in table and get season_id for next table
                                    if (!empty($match_array))
                                    {
                                        $concat_key = 'CONCAT(league_id,"_",season_game_uid)';
                                        //$update_ignore = array("match_event");
                                        $this->insert_or_update_on_exist($match_keys,$match_array,$concat_key,SEASON,'season_id');
                                        echo "<pre>";echo "Season inserted for: ".$league['league_name'];
                                        $season_info = $this->get_all_table_data("season_id", SEASON, array('league_id' => $league_id, "season_game_uid" => $season_game_uid));
                                        $season_id = $season_info[0]['season_id'];
                                        //echo "<pre>";print_r($season_id);
                                        //echo "<pre>";print_r($season_match_array);die;
                                        foreach ($season_match_array as $key => $value) 
                                        {
                                            //echo "<pre>";print_r($value);//die;
                                            $seasonmatchkeys = $season_id.'_'.$key;
                                            $final_match_array[$seasonmatchkeys] = $value;
                                            $final_match_array[$seasonmatchkeys]['season_id'] = $season_id;
                                            $season_match_keys[] = $seasonmatchkeys;

                                            //Player team data
                                            $player_team_info = $this->get_player_team_info_by_player_id($sports_id,$value['home_id']);
                                            $salary = $this->get_salary(intval(@$player_team_info['rank']),count($season_match_array));
                                            //echo "<pre>";print_r(($salary));die;
                                            $playerteamkey = $season_id.'_'.$value['home_id'];
                                            $player_team[$playerteamkey] = array(
                                                            "season_id" => $season_id,
                                                            "player_id" => $value['home_id'],
                                                            "position"  => "ALL",
                                                            "rank_number" => intval(@$player_team_info['rank']),
                                                            "team_id" => $player_team_info['team_id'],
                                                            "player_status" => 1,
                                                            "feed_verified"  => 1,
                                                            "is_published" => $is_published,
                                                            "salary" => $salary
                                                                    );
                                            $player_team_key[] = $playerteamkey;
                                            $player_team_info = $this->get_player_team_info_by_player_id($sports_id,$value['away_id']);
                                             $salary = $this->get_salary(intval(@$player_team_info['rank']),count($season_match_array));
                                            $playerteamkey = $season_id.'_'.$value['away_id'];
                                            $player_team[$playerteamkey] = array(
                                                        "season_id" => $season_id,
                                                        "player_id" => $value['away_id'],
                                                        "position"  => "ALL",
                                                        "rank_number" => intval(@$player_team_info['rank']),
                                                        "team_id" => $player_team_info['team_id'],
                                                        "player_status" => 1,
                                                        "feed_verified"  => 1,
                                                        "is_published" => $is_published,
                                                        "salary" => $salary
                                                                    );
                                            $player_team_key[] = $playerteamkey;
                                        }
                                         //echo "<pre>";print_r($season_match_keys);die;
                                        //echo "<pre>";print_r(($player_team));die;
                                        //echo "<pre>";print_r($season_match_keys);die;
                                        //echo "<pre>";print_r($final_match_array);continue;die;
                                        if(!empty($final_match_array))
                                        {
                                            $concat_key = 'CONCAT(season_id,"_",match_id)';
                                            $this->insert_or_update_on_exist($season_match_keys,$final_match_array,$concat_key,SEASON_MATCH,'season_match_id');
                                            //delete seson match if deleted =1
                                            $this->db->where(array("deleted" => 1));
                                            $this->db->delete(SEASON_MATCH);
                                            echo "<pre>";echo "Matches inserted for season match table for: ".$league['league_name'];
                                        } 

                                        //Player team insert 
                                        if(!empty($player_team))
                                        {
                                            $concat_key = 'CONCAT(season_id,"_",player_id)';
                                            $update_ignore = array("salary");
                                            $this->insert_or_update_on_exist($player_team_key,$player_team,$concat_key,PLAYER_TEAM,'player_team_id',$update_ignore);
                                            echo "<pre>";echo "Player team inserted for seasin id: ".$season_id;
                                        }  
                                    }
                                   
                                    echo "<pre>";echo "Season insert for league : ".$league['league_name'];
                                   
                                //}  
                               }   
                            }
                    }
                    
                    //set league last date
                    $this->update_league_last_date(array($league_id));
                }    
            
            }

        }
        return true;
    }

    public function get_player_id_by_player_uid($sports_id,$player_uids=array())
    {
        $this->db->select("P.player_id,P.player_uid")
                ->from(PLAYER." AS P")
                ->where("P.sports_id", $sports_id);
                if(!empty($player_uids)) 
                {
                    $this->db->where_in("P.player_uid",$player_uids);
                }  
            $rs = $this->db->get();
        $player_data = $rs->result_array();
        $player_ids = array();
        if(!empty($player_data))
        {
            $player_ids = array_column($player_data, "player_id","player_uid");
        }
        return $player_ids;
    }

    public function get_player_team_info_by_player_id($sports_id,$player_id="")
    {
        $this->db->select("P.player_id,P.player_uid,P.rank,T.team_id")
                ->from(PLAYER." AS P")
                ->join(TEAM." AS T","T.team_uid = P.country AND T.sports_id=".$sports_id)
                ->where("P.sports_id", $sports_id)
                ->where("P.player_id",$player_id);
        $rs = $this->db->get();
        //echo $this->db->last_query();die;
        $player_data = $rs->row_array();
        return $player_data;
    }

    /**
     * function used for fetch live games score from feed
     * @param int $sports_id
     * @return boolean
     */
    public function get_scores($sports_id)
    {
        $current_game = $this->get_tennis_season_match_for_score($sports_id);
        //echo '<pre>Live Match:';print_r($current_game);die;
        if(!empty($current_game))
        {
            $final_score_data = array();
            foreach($current_game as $key => $value) 
            {
                //echo '<pre>';print_r($value);die;
                $league_id  = $value['league_id'];
                $league_uid = $value['league_uid'];
                $season_id  = $value['season_id'];
                $match_id   = $value['match_id'];
                $home_id    = $value['home_id']; // player id
                $away_id    = $value['away_id']; // player id
                $season_match_id = $value['season_match_id'];
                $season_game_uid  = $value['season_game_uid'];
                $match_event = $value['match_event'];
                $scheduled_date = $value['scheduled_date'];
                $no_of_sets = $value['no_of_sets'];

                //update player addition stats like Aces, Double fault etc
                $this->get_player_stats_scores($sports_id,$season_id,$match_id);
                //http://www.goalserve.com/getfeed/a28fc23ec9e947cc94fed2334b6ce52c/tennis_scores/41944
                $url = $this->api['api_url'].$this->api['subscription_key']."/tennis_scores/".$league_uid;
                //echo $url;die;
                $score_data = @file_get_contents($url);
                if (!$score_data){
                    continue;
                }
                //player information with team
                $player_info = $this->get_player_team_data_by_game_id($sports_id,$season_id); 
                //echo "<pre>";print_r($player_info);die;
                $score_data = xml2array($score_data);
                $score_array = @$score_data['results']['tournament']['week'];
                //echo "<pre>";print_r($score_array);die;
                if(!empty($score_array))
                {
                    
                    foreach($score_array as $key => $value) 
                    {
                        $matches = @$value['match'];
                        if(!array_key_exists('0', $matches))
                        {
                            $temp = $matches;
                            $seamatches = array();
                            $matches[0] = $temp; 
                        }
                        //echo "<pre>";print_r($matches);die;
                        $tournament_name = @$value['attr']['number'];
                        $qualification = @$value['attr']['qualification'];
                        $first_match = $matches[0]['attr']['id'];
                        $game_uid = md5(str_replace(" ","",strtolower($tournament_name.$qualification.$first_match)));
                        //echo "<pre>";print_r($game_uid);die;
                        //if($season_game_uid == $game_uid)
                        //{
                            //echo "<pre>";print_r($matches);die;
                            if(!empty($matches))
                            {
                                foreach($matches as $key => $match_data) 
                                {
                                   if($match_id == @$match_data['attr']['id'])
                                   {
                                        $players = @$match_data['player'];
                                        //echo "<pre>";print_r($players);die;
                                        foreach (@$players as $key => $pvalue) 
                                        {
                                            $player_uid = $pvalue['attr']['id'];
                                            if($player_uid != @$player_info[$player_uid]['player_uid'])
                                            {
                                                continue;
                                            }    
                                            //echo "<pre>";print_r($pvalue);die;
                                            $final_score_data[] = array(
                                                "league_id" => $league_id,
                                                "season_id" => $season_id,
                                                "season_match_id" => $season_match_id,
                                                "team_id" => $player_info[$player_uid]['team_id'],
                                                "player_id" => $player_info[$player_uid]['player_id'],
                                                "s1" => floatval($pvalue['attr']['s1']),
                                                "s2" => floatval($pvalue['attr']['s2']),
                                                "s3" => floatval($pvalue['attr']['s3']),
                                                "s4" => floatval($pvalue['attr']['s4']),
                                                "s5" => floatval($pvalue['attr']['s5']),
                                                "total_score" => intval($pvalue['attr']['totalscore']),
                                                "winner" => (strtolower($pvalue['attr']['winner']) == 'true') ? '1' : '0',
                                                "updated_at" => format_date(),
                                                "scheduled_date" => $scheduled_date,
                                                                    );
                                        }
                                        if(!empty($final_score_data))
                                        {   
                                            //echo "<pre>";print_r($final_score_data);die;
                                            $this->replace_into_batch(GAME_STATISTICS_TENNIS, array_values($final_score_data));
                                            echo "<pre>";echo "Score update for season_match_id: ".$season_match_id;
                                        
                                            //echo "<pre>";print_r($final_score_data);die;
                                            $status = $this->match_status(@$match_data['attr']['status']);
                                            if(strtolower(@$match_data['attr']['status']) == "retired")
                                            {
                                                $this->get_retired_score($season_id,$season_match_id,$no_of_sets);
                                            }    
                                            //update match status

                                            //Store score in season match
                                            $match_score = array();
                                            $this->db->select("player_id,s1,s2,s3,s4,s5")
                                                ->from(GAME_STATISTICS_TENNIS)
                                                ->where("season_match_id", $season_match_id)
                                                ->where("season_id", $season_id);
                                                //->group_by()
                                            $sql = $this->db->get();
                                            //echo $this->db->last_query();
                                            $final_match_score = $sql->result_array();
                                            if(!empty($final_match_score))
                                            {
                                                foreach ($final_match_score as $key => $value) 
                                                {
                                                    $match_score[$value['player_id']] = $value;
                                                    unset($match_score[$value['player_id']]['player_id']);
                                                }

                                            }    
                                            //echo "<pre>";print_r(json_encode($match_score));die;

                                            $this->db->set("status",$status);
                                            $this->db->set("score",json_encode($match_score));
                                            $this->db->where("season_match_id",$season_match_id);
                                            $this->db->where("match_id",$match_id);
                                            $this->db->update(SEASON_MATCH);
                                        }
                                   } 
                                   
                                }
                            }    
                        //}    
                    }
                } 

                //Season status update when all match closed 
                $this->db->select("count(season_match_id) AS counter")
                    ->from(SEASON_MATCH)
                    ->where_in("status", array(2,4,5))
                    ->where("season_id", $season_id);
                $sql = $this->db->get();
                $total_match = $sql->row('counter');
                if($total_match == $match_event)
                {
                    $this->db->set("status",2);
                    $this->db->set("status_overview",4);
                    $this->db->set("match_closure_date",format_date());
                    $this->db->where("season_id",$season_id);
                    $this->db->update(SEASON);
                    echo "<pre>";echo "Final Season status update for season_id: ".$season_id; 
                }    

            }
        }
        exit();
    }

    /**
     * function used for get season list for score update
     * @param int $sports_id
     * @return array
     */
    public function get_tennis_season_match_for_score($sports_id,$season_id='',$match_id='')
    {
        $current_date_time = format_date();
        $this->db->select("S.season_id,S.year,S.season_game_uid,S.league_id,L.league_uid,S.type,S.season_scheduled_date,S.format,L.sports_id,S.end_scheduled_date,SM.match_id,SM.home_id,SM.away_id,SM.scheduled_date,SM.season_match_id,S.match_event,L.no_of_sets")
            ->from(SEASON." AS S")
            ->join(LEAGUE." AS L", "L.league_id = S.league_id", "INNER")
            ->join(SEASON_MATCH." AS SM", "SM.season_id = S.season_id", "INNER")
            ->where("S.season_scheduled_date <='".$current_date_time."'")
            ->where("SM.scheduled_date <='".$current_date_time."'")
            ->where("L.sports_id", $sports_id)
            ->where("L.active", '1')
            ->where("S.match_status", '0')
            ->where_in("S.status", array(0,1,3))
            ->where_in("SM.status", array(0,1,3));
            if($season_id != '' && $match_id != '')
            {
                $this->db->where("S.season_id", $season_id);
                $this->db->where("SM.match_id", $match_id);
            }    

            
        //$this->db->where("S.season_id", "13366");
        //$this->db->where("SM.match_id", "2063757");

        $this->db->group_by("SM.match_id");
        $sql = $this->db->get();
        //echo $this->db->last_query();
        $result = $sql->result_array();
        return $result;
    }

    /**
     * [get_player_team_data_by_game_id :- GET GAME PLAYER TEAM FROM DATABASE FOR PARTICULAR GAME ID ]
     * @param  [type] $season_game_uid [description]
     * @return [type]          [description]
     */
    private function get_player_team_data_by_game_id($sports_id,$season_id) 
    {
        $rs = $this->db->select("P.player_id, P.player_uid,PT.team_id", FALSE)
                ->from(PLAYER." AS P")
                ->join(PLAYER_TEAM." AS PT", "PT.player_id = P.player_id AND PT.season_id = '".$season_id."' ", 'INNER')
                ->where("P.sports_id", $sports_id)
                ->get();
        //echo $this->db->last_query(); die;
        $res = $rs->result_array();
        $player_uids = array();
        foreach (@$res as $key => $value) 
        {
            $player_uids[$value['player_uid']] = $value;
        }
        return $player_uids;
    }

    private function match_status($status_string)
    {
        $status = 0;
        $status_string = strtolower($status_string);
        if($status_string == "cancelled" || $status_string == "suspended" 
            || $status_string == "abandoned" || $status_string == "postponed")
        {
            $status = 4;
        }elseif($status_string == "finished")
        {
            $status = 2;
        }elseif($status_string == "retired" || $status_string == "awarded" || $status_string == "walk over")
        {
            $status = 5;
        }                
                 
        return $status;                     
    }


    /**
     * function used for fetch live games score from feed
     * @param int $sports_id
     * @return boolean
     */
    public function get_player_stats_scores($sports_id,$season_id,$match_id)
    {
        $current_game = $this->get_tennis_season_match_for_score($sports_id,$season_id,$match_id);
        //echo '<pre>Live Match:';print_r($current_game);die;
        if(!empty($current_game))
        {
            $final_score_data = array();
            foreach($current_game as $key => $value) 
            {
                //echo '<pre>';print_r($value);die;
                $league_id  = $value['league_id'];
                $league_uid = $value['league_uid'];
                $season_id  = $value['season_id'];
                $match_id   = $value['match_id'];
                $home_id    = $value['home_id']; // player id
                $away_id    = $value['away_id']; // player id
                $season_match_id = $value['season_match_id'];
                $season_game_uid  = $value['season_game_uid'];
                $match_event = $value['match_event'];
                $scheduled_date = $value['scheduled_date'];
                //https://www.goalserve.com/getfeed/a28fc23ec9e947cc94fed2334b6ce52c/tennis_scores/d-1_gamestats
                //https://www.goalserve.com/getfeed/a28fc23ec9e947cc94fed2334b6ce52c/tennis_scores/home_gamestats
                $url = $this->api['api_url'].$this->api['subscription_key']."/tennis_scores/home_gamestats";
                //echo $url;die;
                $score_data = @file_get_contents($url);
                if (!$score_data){
                    continue;
                }
                //player information with team
                $player_info = $this->get_player_team_data_by_game_id($sports_id,$season_id); 
                //echo "<pre>";print_r($player_info);die;
                $score_data = xml2array($score_data);
                $category_array = @$score_data['statistics']['category'];
                //echo "<pre>";print_r($category_array);die;
                if(!array_key_exists('0', $category_array))
                {
                    $temp = $category_array;
                    $category_array = array();
                    $category_array[0] = $temp; 
                }
                if(!empty($category_array))
                {
                    foreach($category_array as $key => $category)
                    {
                        //echo "<pre>";print_r($category['match']);die;
                        $category_match = $category['match'];
                        if(!array_key_exists('0', $category_match))
                        {
                            $temp = $category_match;
                            $category_match = array();
                            $category_match[0] = $temp; 
                        } 
                        foreach($category_match as $key => $value) 
                        {
                            //echo "<pre>";print_r($value);die;
                            if($match_id == @$value['attr']['id'])
                            {
                                $players = @$value['player'];
                                //echo "<pre>";print_r($players);die;
                                foreach (@$players as $key => $pvalue) 
                                {
                                    $player_uid = $pvalue['attr']['id'];
                                    if($player_uid != @$player_info[$player_uid]['player_uid'])
                                    {
                                        continue;
                                    }    
                                    //echo "<pre>";print_r($pvalue);die;
                                    $final_score_data[] = array(
                                        "league_id" => $league_id,
                                        "season_id" => $season_id,
                                        "season_match_id" => $season_match_id,
                                        "team_id" => $player_info[$player_uid]['team_id'],
                                        "player_id" => $player_info[$player_uid]['player_id'],
                                        "service_aces" => intval(@$pvalue['stats']['period']['type'][0]['stat'][0]['attr']['value']), //Aces
                                        "service_df" => intval(@$pvalue['stats']['period']['type'][0]['stat'][1]['attr']['value']),//Double Faults
                                        //"service_bps" => intval(@$pvalue['stats']['period']['type'][0]['stat'][5]['attr']['total']), //Break Points Saved

                                        //"return_bpc" => intval(@$pvalue['stats']['period']['type'][1]['stat'][2]['attr']['total']), //Break Points Converted

                                        //"points_tpw" => intval(@$pvalue['stats']['period']['type'][2]['stat'][3]['attr']['total']), //Total Points Won

                                        //"games_tgw" => intval(@$pvalue['stats']['period']['type'][3]['stat'][3]['attr']['total']), //Total Games Won

                                        "updated_at" => format_date(),
                                        
                                                           );
                                }
                                
                                if(!empty($final_score_data))
                                {   
                                    //echo "<pre>";print_r($final_score_data);die;
                                    $this->replace_into_batch(GAME_STATISTICS_TENNIS, array_values($final_score_data));
                                    echo "<pre>";echo "Details Score update for season_match_id: ".$season_match_id;
                                }
                            }     
                        }
                    }    
                } 
            }
        }
        return true;
    }

    private function get_salary($rank,$counter)
    {
        $salary = 0;
        if($counter > 8)
        {
            if($rank >= 1 && $rank <=10)
            {
                $salary = 14.5;
            }elseif($rank >= 11 && $rank <= 20)
            {
                $salary = 13.0;
            }elseif($rank >= 21 && $rank <= 50)
            {
                $salary = 11.5;
            }elseif($rank >= 51 && $rank <= 100)
            {
                $salary = 10.0;
            }elseif($rank >= 101 && $rank <= 300)
            {
                $salary = 8.5;
            }elseif($rank >= 301 && $rank <= 500)
            {
                $salary = 8.0;
            }elseif($rank >= 501 && $rank <= 800)
            {
                $salary = 7.5;
            }elseif($rank >= 801 && $rank <= 1200)
            {
                $salary = 7.0;
            }elseif($rank >=  1201 && $rank <= 1700)
            {
                $salary = 6.5;
            }elseif($rank >=  1701 )
            {
                $salary = 6.0;
            } else
            {
                $salary = 11.5;
            }      
        }else{
            if($rank >= 1 && $rank <=10)
            {
                $salary = 22.5;
            }elseif($rank >= 11 && $rank <= 20)
            {
                $salary = 20.0;
            }elseif($rank >= 21 && $rank <= 50)
            {
                $salary = 18.5;
            }elseif($rank >= 51 && $rank <= 100)
            {
                $salary = 17.0;
            }elseif($rank >= 101 && $rank <= 300)
            {
                $salary = 16.5;
            }elseif($rank >= 301 && $rank <= 500)
            {
                $salary = 14.0;
            }elseif($rank >= 501 && $rank <= 800)
            {
                $salary = 13.5;
            }elseif($rank >= 801 && $rank <= 1200)
            {
                $salary = 13.0;
            }elseif($rank >=  1201 && $rank <= 1700)
            {
                $salary = 12.5;
            }elseif($rank >=  1701 )
            {
                $salary = 12.0;
            } else
            {
                $salary = 19.5;
            }
        }
        return $salary;    
    }

    public function calculated_fantasy_score($sports_id)
    {
        // Get All Live Games List 
        $current_game = $this->get_tennis_season_match_for_calculate_points($sports_id);
        //echo "<pre>"; print_r($current_game);die;
        if (!empty($current_game))
        {
            $formula = $this->get_scoring_rules($sports_id);
            //echo "<pre>";print_r($formula);die;
            foreach ($current_game as $season_game)
            {   
                $this->calculate_tennis_fantasy_points($sports_id,$season_game,$formula);      
            }// End of Team loop  
        } // End of Empty team check
        exit();
    }

    private function calculate_tennis_fantasy_points($sports_id,$season_game,$formula)
    {
        // Get All Scoring Rules
        $league_id              = $season_game['league_id'];
        $season_game_uid        = $season_game['season_game_uid'];
        $season_id              = $season_game['season_id'];
        $season_match_id        = $season_game['season_match_id'];
        $status                 = $season_game['status'];
        $no_of_sets             = $season_game['no_of_sets'];
        $all_player_scores      = array();
        $all_player_testing_var = array();
        $total_final_breakdown = array();
        $player_final_breakdown = array();
        
        //echo "<pre>";print_r($formula);die;
        // Get Match Scoring Statistics to Calculate Fantasy Score
        $game_stats = $this->get_game_statistics_by_game_id($season_id,$sports_id,$league_id,$season_match_id);
        $player_id_array = array_column($game_stats, "player_id");
        //echo "<pre>";print_r($game_stats);die;
        if(!empty($game_stats))
        {   
            $all_player_scores = array();
            foreach($game_stats as $key => $player_stats)
            {
                $score              = 0.0;
                $bonus_score        = 0.0;
                $normal_score       = 0.0;
                $each_player_score  = array();
                $break_down = array();
                $total_final_breakdown = array();
                $final_break_down = array();
                $normal_testing_var = array();
                               
                $each_player_score['season_id']         = $player_stats['season_id'];
                $each_player_score['player_id']         = $player_stats['player_id'];
                $each_player_score['scheduled_date']    = $player_stats['scheduled_date'];
                $each_player_score['league_id']         = $league_id;
                $each_player_score['normal_score']      = $normal_score;
                $each_player_score['week']              = 0;
                $each_player_score['score']             = $score;


                $total_sets = 'Best_of_3_sets';
                if($no_of_sets == 5)
                {
                    $total_sets = 'Best_of_5_sets';
                }    

                if(($game_stats[0]['s1'] > 0 || $game_stats[1]['s1'] > 0))
                {   
                    
                    if($player_stats['winner'] == 1)
                    {
                        $match_won = $formula[$total_sets]['MATCH_OWN']; 

                        $normal_score += $match_won;
                        $normal_testing_var['MATCH_OWN'] = $match_won;
                        $final_break_down[$total_sets]["MATCH_OWN"] = $match_won;
                        $total_final_breakdown[$player_stats['player_id']][$total_sets]["MATCH_OWN"] = (!empty($total_final_breakdown[$player_stats['player_id']][$total_sets]["MATCH_OWN"])) ? ($match_won) + $total_final_breakdown[$player_stats['player_id']][$total_sets]["MATCH_OWN"] : ($match_won);
                    }

                    if($player_stats['service_aces'] > 0 && $status != 5)
                    {
                        $aces = $formula[$total_sets]['ACES'] * $player_stats['service_aces']; 
                        $normal_score += $aces;
                        $normal_testing_var['ACES'] = $aces;
                        $final_break_down[$total_sets]["ACES"] = $aces;
                        $total_final_breakdown[$player_stats['player_id']][$total_sets]["ACES"] = (!empty($total_final_breakdown[$player_stats['player_id']][$total_sets]["ACES"])) ? ($aces) + $total_final_breakdown[$player_stats['player_id']][$total_sets]["ACES"] : ($aces);
                    }

                    if($player_stats['service_df'] > 0 && $status != 5)
                    {
                        $service_df = $formula[$total_sets]['DOUBLE_FAULTS'] * $player_stats['service_df']; 

                        $normal_score += $service_df;
                        $normal_testing_var['DOUBLE_FAULTS'] = $service_df;
                        $final_break_down[$total_sets]["DOUBLE_FAULTS"] = $service_df;
                        $total_final_breakdown[$player_stats['player_id']][$total_sets]["DOUBLE_FAULTS"] = (!empty($total_final_breakdown[$player_stats['player_id']][$total_sets]["DOUBLE_FAULTS"])) ? ($service_df) + $total_final_breakdown[$player_stats['player_id']][$total_sets]["DOUBLE_FAULTS"] : ($service_df);
                    }

                    //sets for games
                    for($i=1;$i<=5;$i++)
                    {
                        $set_no = 's'.$i;
                        
                        //Winnig game for first player
                        if($key == 0 && $player_stats[$set_no] > 0 && $player_stats[$set_no] >= 6)
                        {
                            $games = $player_stats[$set_no] - $game_stats[$key+1][$set_no];
                            
                            if($games > 0)
                            {    
                                if(is_float($games))
                                {
                                    $winning_set_games = 'WINNING_SET_TIE_BREAK';
                                }else{
                                   $winning_set_games = 'WINNING_SET_'.$games.'_GAMES'; 
                                }    
                                
                                //echo $winning_set_games;die;
                                $set_score = $formula[$total_sets][$winning_set_games]; 

                                if (array_key_exists($player_stats['player_id'], $total_final_breakdown) && array_key_exists($winning_set_games, $total_final_breakdown[$player_stats['player_id']]))
                                {
                                    $set_score += $formula[$total_sets][$winning_set_games];
                                }else{
                                    $set_score = $formula[$total_sets][$winning_set_games];
                                }   

                                $normal_score += $formula[$total_sets][$winning_set_games];

                                $normal_testing_var[$winning_set_games] = $normal_score;
                                $final_break_down[$total_sets][$winning_set_games] = $set_score;
                                $total_final_breakdown[$player_stats['player_id']][$winning_set_games][$winning_set_games] = (!empty($total_final_breakdown[$player_stats['player_id']][$winning_set_games][$winning_set_games])) ? ($games) + $total_final_breakdown[$player_stats['player_id']][$winning_set_games][$winning_set_games] : ($set_score);
                            }    
                        }
                        // Losing games
                        if($key == 0 && $player_stats[$set_no] >= 0 && 
                            ($player_stats[$set_no] < 6 || $player_stats[$set_no] < $game_stats[$key+1][$set_no] )
                           ) 
                        {
                            
                            $games = ($player_stats[$set_no] - $game_stats[$key+1][$set_no]);
                            
                            if(abs($games) >=1)
                            {   
                                
                                if(is_float($games))
                                {
                                    $losing_set_games = 'LOSING_SET_TIE_BREAK';
                                }else{
                                   $losing_set_games = 'LOSING_SET_'.abs($games).'_GAMES';
                                }   

                                if (array_key_exists($player_stats['player_id'], $total_final_breakdown) && array_key_exists($losing_set_games, $total_final_breakdown[$player_stats['player_id']]))
                                {
                                    $set_score += $formula[$total_sets][$losing_set_games];
                                }else{
                                    $set_score = $formula[$total_sets][$losing_set_games]; 
                                } 

                                $normal_score += $formula[$total_sets][$losing_set_games]; 
                                $normal_testing_var[$losing_set_games] = $set_score;
                                $final_break_down[$total_sets][$losing_set_games] = $set_score;
                                $total_final_breakdown[$player_stats['player_id']][$losing_set_games][$losing_set_games] = (!empty($total_final_breakdown[$player_stats['player_id']][$losing_set_games][$losing_set_games])) ? ($games) + $total_final_breakdown[$player_stats['player_id']][$losing_set_games][$losing_set_games] : ($set_score);
                            }    
                        }

                        //for second player_id
                        //Winnig game
                        if($key == 1 && $player_stats[$set_no] > 0 && $player_stats[$set_no] >= 6)
                        {
                            $games = $player_stats[$set_no] - $game_stats[$key-1][$set_no];
                            
                            if($games > 0)
                            {    
                                if(is_float($games))
                                {
                                    $winning_set_games = 'WINNING_SET_TIE_BREAK';
                                }else{
                                   $winning_set_games = 'WINNING_SET_'.$games.'_GAMES'; 
                                }  

                                if (array_key_exists($player_stats['player_id'], $total_final_breakdown) && array_key_exists($winning_set_games, $total_final_breakdown[$player_stats['player_id']]))
                                {
                                    $set_score += $formula[$total_sets][$winning_set_games];
                                }else{
                                    $set_score = $formula[$total_sets][$winning_set_games];
                                }   

                                $normal_score += $formula[$total_sets][$winning_set_games];

                                $normal_testing_var[$winning_set_games] = $set_score;
                                $final_break_down[$total_sets][$winning_set_games] = $set_score;
                                $total_final_breakdown[$player_stats['player_id']][$winning_set_games][$winning_set_games] = (!empty($total_final_breakdown[$player_stats['player_id']][$winning_set_games][$winning_set_games])) ? ($games) + $total_final_breakdown[$player_stats['player_id']][$winning_set_games][$winning_set_games] : ($set_score);
                            }    
                        }
                        // Losing games
                        if($key == 1 && $player_stats[$set_no] >= 0 && ($player_stats[$set_no] < 6 || $player_stats[$set_no] < $game_stats[$key-1][$set_no] )) 
                        {
                            $games = ($player_stats[$set_no] - $game_stats[$key-1][$set_no]);
                            if(abs($games) >=1)
                            {
                                if(is_float($games))
                                {
                                    $losing_set_games = 'LOSING_SET_TIE_BREAK';
                                }else{
                                   $losing_set_games = 'LOSING_SET_'.abs($games).'_GAMES';
                                } 

                                if (array_key_exists($player_stats['player_id'], $total_final_breakdown) && array_key_exists($losing_set_games, $total_final_breakdown[$player_stats['player_id']]))
                                {
                                    $set_score += $formula[$total_sets][$losing_set_games];
                                }else{
                                    $set_score = $formula[$total_sets][$losing_set_games]; 
                                } 

                                $normal_score += $formula[$total_sets][$losing_set_games];

                                $normal_testing_var[$losing_set_games] = $set_score;
                                $final_break_down[$total_sets][$losing_set_games] = $set_score;
                                $total_final_breakdown[$player_stats['player_id']][$losing_set_games][$losing_set_games] = (!empty($total_final_breakdown[$player_stats['player_id']][$losing_set_games][$losing_set_games])) ? ($games) + $total_final_breakdown[$player_stats['player_id']][$losing_set_games][$losing_set_games] : ($set_score);
                            }    
                        }

                    } 

                   
                }

                elseif($status == 5 && $player_stats['winner'] == 1)
                {    
                    $retirement_won = $formula[$total_sets]['WIN_BY_RETIREMENT']; 

                    $normal_score += $retirement_won;
                    $normal_testing_var['WIN_BY_RETIREMENT'] = $retirement_won;
                    $final_break_down[$total_sets]["WIN_BY_RETIREMENT"] = $retirement_won;
                    $total_final_breakdown[$player_stats['player_id']][$total_sets]["WIN_BY_RETIREMENT"] = (!empty($total_final_breakdown[$player_stats['player_id']][$total_sets]["WIN_BY_RETIREMENT"])) ? ($retirement_won) + $total_final_breakdown[$player_stats['player_id']][$total_sets]["WIN_BY_RETIREMENT"] : ($retirement_won);                        
                }   
                


                $each_player_score['normal_score']  = $normal_score;
                $all_player_testing_var[$player_stats['player_id']]= $normal_testing_var;
                $player_final_breakdown[$player_stats['player_id']] = $final_break_down;

                if (array_key_exists($player_stats['player_id'], $all_player_scores) && array_key_exists('normal_score', $all_player_scores[$player_stats['player_id']]))
                {
                    $each_player_score['normal_score'] = $all_player_scores[$player_stats['player_id']]['normal_score'] = $all_player_scores[$player_stats['player_id']]['normal_score'] + $each_player_score['normal_score'];
                }

                $all_player_scores[$player_stats['player_id']] = $each_player_score;
                $all_player_scores[$player_stats['player_id']]['break_down'] = json_encode($player_final_breakdown[$player_stats['player_id']]);
                $all_player_scores[$player_stats['player_id']]['final_break_down'] = json_encode($player_final_breakdown[$player_stats['player_id']]);
            }

        }// End  of  Empty of game_ stats

        if(!empty($all_player_scores))
        {
            //echo "<pre>";print_r($all_player_scores);//die;
            //Start Transaction
            $this->db->trans_strict(TRUE);
            $this->db->trans_start();
            //Update score first zero then update
            $this->db->where('season_id', $season_id);
            $this->db->where('league_id', $league_id);
            $this->db->where_in('player_id',$player_id_array);
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
    public function get_tennis_season_match_for_calculate_points($sports_id)
    {
        $current_date_time = format_date();
        $past_time = date("Y-m-d H:i:s", strtotime($current_date_time . " -".MATCH_SCORE_CLOSE_DAYS." days"));
        $close_date_time = date("Y-m-d H:i:s", strtotime($current_date_time . " -".CONTEST_CLOSE_INTERVAL." minutes"));
        $this->db->select("S.season_id,S.home_id,S.away_id,S.year,S.api_week,S.week, S.season_game_uid,S.league_id,S.feed_date_time,S.season_scheduled_date, S.format, S.type, L.sports_id,SM.match_id,SM.season_match_id,
            SM.status,L.no_of_sets")
                ->from(SEASON. " AS S")
                ->join(LEAGUE. " AS L", "L.league_id = S.league_id", "INNER")
                 ->join(SEASON_MATCH." AS SM", "SM.season_id = S.season_id", "INNER")
                ->where("L.active", '1')
                ->where("S.match_status", '0')
                ->where("S.season_scheduled_date <='".$current_date_time."'")
                ->where("(S.match_closure_date IS NULL OR S.match_closure_date > '".$close_date_time."')")
                ->where("S.season_scheduled_date >= ", $past_time)
                ->where("L.sports_id", $sports_id);

        //$this->db->where("S.season_id","16769");
        //$this->db->where("SM.season_match_id","418");
        

        $this->db->group_by("SM.match_id");
        $this->db->group_by("S.season_game_uid");
        $sql = $this->db->get();
        //echo $this->db->last_query();die;
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
    private function get_game_statistics_by_game_id($season_id,$sports_id,$league_id,$season_match_id)
    {
        $sql = $this->db->select("GST.*,PT.position", FALSE)
                ->from(GAME_STATISTICS_TENNIS." AS GST")
                ->join(PLAYER." AS P", "P.player_id = GST.player_id AND P.sports_id = $sports_id ", 'INNER')
                ->join(PLAYER_TEAM." AS PT", "PT.player_id = P.player_id AND PT.season_id = '".$season_id."' ", 'INNER')
                ->where("GST.season_id", $season_id)
                ->where("GST.season_match_id", $season_match_id)
                ->where("GST.league_id", $league_id)
                ->group_by("P.player_id")
                ->get();
        $result = $sql->result_array();
        return $result;
    }

    public function get_retired_score($season_id,$season_match_id,$no_of_sets)
    {
        if($season_id == '' || $season_match_id == '')
        {
            return true;
        }

        $sql = $this->db->select("GST.*", FALSE)
                ->from(GAME_STATISTICS_TENNIS." AS GST")
                ->where("GST.season_id", $season_id)
                ->where("GST.season_match_id", $season_match_id)
                ->group_by("GST.player_id")
                ->get();
        $rpd = $sql->result_array();
        //echo "<pre>";print_r($rpd); //die;
        $s1 = $s2 = $s3 = $S4= $s5 = $player_1_score = $player_2_score = 0;
        $player_array = $final_player_data = array();
        if(!empty($rpd))
        {    
            
            foreach($rpd as $key => $value) 
            {
                if($rpd[$key]['winner'] == 1 && ($rpd[$key]['s1'] > 0 || $rpd['1']['s1'] > 0))
                {
                    $player_1 = $rpd[$key]['player_id'];
                    
                    //s1
                    if($rpd[$key]['s1'] >= 6 && $rpd[$key]['s1'] > $rpd['1']['s1']) 
                    {
                        $player_array[$player_1]['s1'] = $rpd[$key]['s1'];
                        $player_1_score += 1;
                    }elseif($rpd[$key]['s1'] < 6 && $rpd['1']['s1'] >= 6) 
                    {
                        $player_array[$player_1]['s1'] = $rpd[$key]['s1'];
                        $player_2_score += 1;
                    }elseif($rpd[$key]['s1'] < 6 && $rpd['1']['s1'] < 6) 
                    {
                        $player_array[$player_1]['s1'] = 6;
                        $player_1_score += 1;
                    } 

                    //s2
                    if($rpd[$key]['s2'] >= 6 && $rpd[$key]['s2'] > $rpd['1']['s2'])
                    {
                        $player_array[$player_1]['s2'] = $rpd[$key]['s2'];
                        $player_1_score += 1;
                    }elseif($rpd[$key]['s2'] < 6 && $rpd['1']['s2'] >= 6) 
                    {
                        $player_array[$player_1]['s2'] = $rpd[$key]['s2'];
                        $player_2_score += 1;
                    }elseif($rpd[$key]['s2'] < 6 && $rpd['1']['s2'] < 6) 
                    {
                        $player_array[$player_1]['s2'] = 6;
                        $player_1_score += 1;
                    }

                    if(abs($player_1_score-$player_2_score) < 1 || $no_of_sets != 3 )
                    {
                        //s3
                        if($rpd[$key]['s3'] >= 6 && $rpd[$key]['s3'] > $rpd['1']['s3'])
                        {
                            $player_array[$player_1]['s3'] = $rpd[$key]['s3'];
                            $player_1_score += 1;
                        }elseif($rpd[$key]['s3'] < 6 && $rpd['1']['s3'] >= 6) 
                        {
                            $player_array[$player_1]['s3'] = $rpd[$key]['s3'];
                            $player_2_score += 1;
                        }elseif($rpd[$key]['s3'] < 6 && $rpd['1']['s3'] < 6) 
                        {
                            $player_array[$player_1]['s3'] = 6;
                            $player_1_score += 1;
                        }
                    } 

                    //for 5 sets    
                    if(abs($player_1_score-$player_2_score) < 2 && $no_of_sets ==5)
                    {
                        //s4
                        if($rpd[$key]['s4'] >= 6 && $rpd[$key]['s4'] > $rpd['1']['s4'])
                        {
                            $player_array[$player_1]['s4'] = $rpd[$key]['s4'];
                            $player_1_score += 1;
                        }elseif($rpd[$key]['s4'] < 6 && $rpd['1']['s4'] >= 6) 
                        {
                            $player_array[$player_1]['s4'] = $rpd[$key]['s4'];
                            $player_2_score += 1;
                        }elseif($rpd[$key]['s4'] < 6 && $rpd['1']['s4'] < 6) 
                        {
                            $player_array[$player_1]['s4'] = 6;
                            $player_1_score += 1;
                        }
                    }

                    if(abs($player_1_score-$player_2_score) < 2 && $no_of_sets ==5)
                    {
                        //s5
                        if($rpd[$key]['s5'] >= 6 && $rpd[$key]['s5'] > $rpd['1']['s5'])
                        {
                            $player_array[$player_1]['s5'] = $rpd[$key]['s5'];
                            $player_1_score += 1;
                        }elseif($rpd[$key]['s5'] < 6 && $rpd['1']['s5'] >= 6) 
                        {
                            $player_array[$player_1]['s5'] = $rpd[$key]['s5'];
                            $player_2_score += 1;
                        }elseif($rpd[$key]['s5'] < 6 && $rpd['1']['s5'] < 6) 
                        {
                            $player_array[$player_1]['s5'] = 6;
                            $player_1_score += 1;
                        }
                    }

                   

                }
            
                $total_score = sprintf("player_%u_score",$key+1);
                if(!empty($player_array[$value['player_id']]))
                {
                    $final_player_data[] = array_merge($value,$player_array[$value['player_id']]);
                }if(empty($player_array[$value['player_id']])){
                    $final_player_data[] = $value;
                } 
                //for final total score
                $final_player_data[$key]['total_score'] = $$total_score;
                  
            }
            //echo "<pre>";print_r($final_player_data); die; 
            if(!empty($final_player_data))
            {
                $this->replace_into_batch(GAME_STATISTICS_TENNIS, array_values($final_player_data));
            }    

        }
        return true;   
    }


    /**
     * This function used for calculate player fantasy point for single match
     * @param int $sports_id
     * @param int $league_id
     * @param string $season_game_uid
     * @return boolean
     */
    public function calculated_fantasy_score_by_match_id($sports_id,$league_id,$season_game_uid,$season_match_id)
    {
        // Get All Live Games List 
        $season_game = $this->get_tennis_season_match_details($sports_id,$league_id,$season_game_uid,$season_match_id);
        //echo "<pre>"; print_r($season_game);die;
        if (!empty($season_game))
        {
            $formula = $this->get_scoring_rules($sports_id);
            $this->calculate_tennis_fantasy_points($sports_id,$season_game,$formula);   
        } // End of Empty team check
        exit();
    }

    public function get_tennis_season_match_details($sports_id,$league_id,$season_game_uid,$season_match_id)
    {
        $current_date_time = format_date();
        $this->db->select("S.season_id,S.home_id,S.away_id,S.year,S.api_week,S.week, S.season_game_uid,S.league_id,S.feed_date_time,S.season_scheduled_date, S.format, S.type, L.sports_id,SM.match_id,SM.season_match_id,
            SM.status,L.no_of_sets")
                ->from(SEASON. " AS S")
                ->join(LEAGUE. " AS L", "L.league_id = S.league_id", "INNER")
                 ->join(SEASON_MATCH." AS SM", "SM.season_id = S.season_id", "INNER")
                ->where("L.active", '1')
                ->where("S.match_status", '0')
                ->where("S.season_scheduled_date <='".$current_date_time."'")
                ->where("L.sports_id", $sports_id);

        $this->db->where("S.season_game_uid",$season_game_uid);
        $this->db->where("S.league_id",$league_id);
        $this->db->where("SM.season_match_id",$season_match_id);
        //$this->db->group_by("SM.match_id");
       //$this->db->group_by("S.season_game_uid");
        $sql = $this->db->get();
        //echo $this->db->last_query();die;
        $matches = $sql->row_array();

        return $matches;
    }

    /*Player images*/
    /**
     * @Summary: This function for use get players profile image and save in file system
     * And update database 
     * database.
     * @access: public
     * @param:
     * @return:
     */
    public function get_players_image($sports_id) 
    {
        $dir_path = ROOT_PATH.JERSEY_CONTEST_DIR;
        $s3_dir_path = JERSEY_CONTEST_DIR;

        $rs = $this->db->select("P.player_uid,P.first_name")
                ->from(PLAYER." AS P")
                ->where("P.image",NULL)
                ->where("P.sports_id",$sports_id)
                //->where("P.player_uid","86159")
                ->get();
        $players = $rs->result_array();
        //echo "<pre>";print_r($players);die;
        $player_data_arr = array();
        $player_keys = array();
        foreach ($players as $player) 
        {
            if(isset($player['player_uid']) && $player['player_uid'] != "")
            {
                $url = $this->api['api_url'].$this->api['subscription_key']."/tennis_scores/profile?id=".$player['player_uid'];
                //echo $url;die;
                $player_data = @file_get_contents($url);
                $player_array = xml2array($player_data);
                
                if(isset($player_array['profiles']['player'])) 
                {
                    $player_info = $player_array['profiles']['player'];
                    if(isset($player_info['image']['value']) && !empty($player_info['image']['value']))
                    {
                        $player_image_name = "ply_".$player['player_uid'].".jpeg";
                        $image_data = base64_decode($player_info['image']['value']);
                        $file_path = $dir_path.$player_image_name;
                        //fopen($dir_path.$player_image_name, "w");
                        file_put_contents($file_path, $image_data);
                     

                        $data_arr = array();
                        $data_arr['file_path'] = $s3_dir_path.$player_image_name;
                        $data_arr['source_path'] = $dir_path.$player_image_name;
                        //echo "<pre>";print_r($data_arr);//die;
                        $this->load->library('Uploadfile');
                        $upload_lib = new Uploadfile();
                        $is_uploaded = $upload_lib->upload_file($data_arr);
                        if($is_uploaded){
                           @unlink($dir_path.$player_image_name);
                        }
                        
                        $key = $sports_id.'_'.$player['player_uid'];

                        $player_data_arr[$key] = array(
                                                "sports_id"     => $sports_id,
                                                "player_uid"    => $player['player_uid'],
                                                "image"         => $player_image_name,
                                            );
                        $player_keys[] = $key;
                    }
                    //echo "<pre>";print_r($player_data_arr);die;                   
                }
            }
            
        }
        //echo "<pre>";print_r($player_data_arr);die;
        if(!empty($player_data_arr))
        {
            $concat_key = 'CONCAT(sports_id,"_",player_uid)';
            $this->insert_or_update_on_exist($player_keys,$player_data_arr,$concat_key,PLAYER,'player_id');
            echo "<pre>";echo "Player images uploaded";
        }

        exit();
        
    }
        
    
}

/* End of file  */
/* Location: ./application/modules/tennis/ */
