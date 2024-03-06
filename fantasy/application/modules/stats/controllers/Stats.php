<?php
class Stats extends Common_Api_Controller {

    public function __construct() {
        parent::__construct();
        $this->contest_lang = $this->lang->line('contest');
    }

    /**
    * Used for get fixture scorevard and stats
    * @param int $collection_master_id
    * @return array
    */
    public function get_match_stats_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports Id', 'trim|required');
        $this->form_validation->set_rules('collection_master_id', 'Collection Master Id', 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $cm_id = $post_data['collection_master_id'];
        $sports_id = $post_data['sports_id'];
        $st_cache_key = "fixture_stats_".$cm_id;
        $fixture_stats = $this->get_cache_data($st_cache_key);
        if(!$fixture_stats) 
        {
            $this->load->model("stats/Stats_model");
            $match_info = $this->Stats_model->get_collection_details($cm_id,$sports_id);
            if(!empty($match_info)){
                $match_info['score_data'] = json_decode($match_info['score_data'],TRUE);
                $match_info['fall_of_wickets'] = json_decode($match_info['fall_of_wickets'],TRUE);
                $match_info['team_batting_order'] = json_decode($match_info['team_batting_order'],TRUE);
            }
            $fall_of_wickets = $match_info['fall_of_wickets'];
            $player_list = $this->Stats_model->get_match_rosters($match_info['season_id']);
            //echo '<pre>';print_r($player_list);die;
            
            //re-order the data for team
            $team_wise_stats = array();
            if($sports_id == CRICKET_SPORTS_ID){
                $score_card = $this->Stats_model->get_fixture_scorecard($match_info['season_id']);
                if(!empty($match_info['team_batting_order'])){
                    foreach($match_info['team_batting_order'] as $bt){
                        $team_wise_stats[$bt['inning']][$bt['team_uid']] = array();
                    }
                }
                if(!empty($score_card)){
                    $player_data = array_column($player_list,NULL,"player_id");
                    foreach($score_card as $stats){
                        $team_uid = $stats['team_uid'];
                        if(!in_array($team_uid,[$match_info['home_uid'],$match_info['away_uid']])){
                            continue;
                        }
                        $inning = $stats['innings'];
                        $pl_obj = $player_data[$stats['player_id']];
                        $stats['player_name'] = $pl_obj['full_name'];
                        $stats['position'] = $pl_obj['position'];
                        $stats['team_name'] = $pl_obj['team_name'];
                        $stats['out_string'] = ucfirst($stats['out_string']);
                        if($stats['batting_order'] > 0){
                            $team_wise_stats[$inning][$team_uid]['batting'][$stats['batting_order']] = $stats;
                        }
                        if($stats['bowling_order'] > 0){
                            $team_wise_stats[$inning][$team_uid]['bowling'][$stats['bowling_order']] = $stats;
                        }

                        if(isset($fall_of_wickets[$inning][$team_uid]) && !empty($fall_of_wickets[$inning][$team_uid]))
                        {
                            $team_wise_stats[$inning][$team_uid]['fall_of_wickets'] = @$fall_of_wickets[$inning][$team_uid];    
                        }else{
                            $team_wise_stats[$inning][$team_uid]['fall_of_wickets'] = array();
                        }
                        if(!empty($team_wise_stats[$inning][$team_uid]['bowling'])){
                            ksort($team_wise_stats[$inning][$team_uid]['bowling']);
                        }
                        if(!empty($team_wise_stats[$inning][$team_uid]['batting'])){
                            ksort($team_wise_stats[$inning][$team_uid]['batting']);
                        }
                    }
                }
            }

            $teams = $this->Stats_model->get_single_row("count(lineup_master_id) as total", LINEUP_MASTER,array("collection_master_id" => $cm_id));
            if(!empty($teams) && $teams['total'] > 0){
                $total_teams = $teams['total'];
                $team_players = $this->Stats_model->get_players_selection_count($cm_id);
                $team_players = array_column($team_players,'total','id');
                $user_teams = $this->Stats_model->get_user_team_list($cm_id);
                $user_players = array();
                foreach($user_teams as $team){
                    $tm_data = json_decode($team['team_data'],TRUE);
                    $user_players = array_merge($user_players,$tm_data['pl']);
                }
                $user_players = array_unique($user_players);
                foreach($player_list as &$row){
                    if(in_array($row['player_team_id'],$user_players)){
                        $row['user_selected'] = 1;
                    }

                    $total_in_lineup = isset($team_players[$row['player_team_id']]) ? $team_players[$row['player_team_id']] : 0;
                    $row['selected_by'] = number_format(($total_in_lineup / $total_teams) * 100,2);
                }
            }

            unset($match_info['fall_of_wickets']);
            //unset($match_info['team_batting_order']);
            $fixture_stats = array("fixture_details"=>$match_info,"scorecard"=>$team_wise_stats,"stats"=>$player_list);
            if($match_info['status'] == 2)
            {    
                $this->set_cache_data($st_cache_key, $fixture_stats, REDIS_30_DAYS);
            }
        }

        $this->api_response_arry['data'] = $fixture_stats;
        $this->api_response();
    }

    /**
    * Used for get season scorevard and stats
    * @param int $season_id
    * @return array
    */
    public function get_season_stats_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports Id', 'trim|required');
        $this->form_validation->set_rules('season_id', 'Season Id', 'trim|required');
        $this->form_validation->set_rules('collection_master_id', 'Collection Master Id', 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $season_id = $post_data['season_id'];
        $cm_id = $post_data['collection_master_id'];
        $sports_id = $post_data['sports_id'];
        $st_cache_key = "fixture_stats_m_".$season_id;
        $fixture_stats = $this->get_cache_data($st_cache_key);
        if(!$fixture_stats) 
        {
            $this->load->model("stats/Stats_model");
            $match_info = $this->Stats_model->get_season_details($season_id,$sports_id);
            if(!empty($match_info)){
                $match_info['score_data'] = json_decode($match_info['score_data'],TRUE);
                $match_info['fall_of_wickets'] = json_decode($match_info['fall_of_wickets'],TRUE);
            }
            $fall_of_wickets = $match_info['fall_of_wickets'];
            $player_list = $this->Stats_model->get_match_rosters($match_info['season_id']);
            //echo '<pre>';print_r($player_list);die;
            
            //re-order the data for team
            $team_wise_stats = array();
            if($sports_id == CRICKET_SPORTS_ID){
                $score_card = $this->Stats_model->get_fixture_scorecard($match_info['season_id']);
                if(!empty($score_card)){
                    $player_data = array_column($player_list,NULL,"player_id");
                    foreach($score_card as $stats){
                        $team_uid = $stats['team_uid'];
                        if(!in_array($team_uid,[$match_info['home_uid'],$match_info['away_uid']])){
                            continue;
                        }
                        $inning = $stats['innings'];
                        $pl_obj = $player_data[$stats['player_id']];
                        $stats['player_name'] = $pl_obj['full_name'];
                        $stats['position'] = $pl_obj['position'];
                        $stats['team_name'] = $pl_obj['team_name'];
                        $stats['out_string'] = ucfirst($stats['out_string']);
                        if($stats['batting_order'] > 0){
                            $team_wise_stats[$inning][$team_uid]['batting'][$stats['batting_order']] = $stats;
                        }
                        if($stats['bowling_order'] > 0){
                            $team_wise_stats[$inning][$team_uid]['bowling'][$stats['bowling_order']] = $stats;
                        }

                        if(isset($fall_of_wickets[$inning][$team_uid]) && !empty($fall_of_wickets[$inning][$team_uid]))
                        {
                            $team_wise_stats[$inning][$team_uid]['fall_of_wickets'] = @$fall_of_wickets[$inning][$team_uid];    
                        }else{
                            $team_wise_stats[$inning][$team_uid]['fall_of_wickets'] = array();
                        }
                        if(!empty($team_wise_stats[$inning][$team_uid]['bowling'])){
                            ksort($team_wise_stats[$inning][$team_uid]['bowling']);
                        }
                        if(!empty($team_wise_stats[$inning][$team_uid]['batting'])){
                            ksort($team_wise_stats[$inning][$team_uid]['batting']);
                        }
                    }
                }
            }

            $teams = $this->Stats_model->get_single_row("count(lineup_master_id) as total", LINEUP_MASTER,array("collection_master_id" => $cm_id));
            if(!empty($teams) && $teams['total'] > 0){
                $total_teams = $teams['total'];
                $team_players = $this->Stats_model->get_players_selection_count($cm_id);
                $team_players = array_column($team_players,'total','id');
                $user_teams = $this->Stats_model->get_user_team_list($cm_id);
                $user_players = array();
                foreach($user_teams as $team){
                    $tm_data = json_decode($team['team_data'],TRUE);
                    $user_players = array_merge($user_players,$tm_data['pl']);
                }
                $user_players = array_unique($user_players);
                foreach($player_list as &$row){
                    if(in_array($row['player_team_id'],$user_players)){
                        $row['user_selected'] = 1;
                    }

                    $total_in_lineup = isset($team_players[$row['player_team_id']]) ? $team_players[$row['player_team_id']] : 0;
                    $row['selected_by'] = number_format(($total_in_lineup / $total_teams) * 100,2);
                }
            }

            unset($match_info['fall_of_wickets']);
            $fixture_stats = array("fixture_details"=>$match_info,"scorecard"=>$team_wise_stats,"stats"=>$player_list);
            if($match_info['status'] == 2)
            {    
                $this->set_cache_data($st_cache_key, $fixture_stats, REDIS_30_DAYS);
            }
        }

        $this->api_response_arry['data'] = $fixture_stats;
        $this->api_response();
    }
}