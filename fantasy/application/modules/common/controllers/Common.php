<?php
class Common extends Common_Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * used for get player card details
     * @param array $post_data
     * @return array
    */
    public function get_playercard_post()
    {        
        $this->form_validation->set_rules('player_team_id', $this->lang->line('player_team_id'), 'trim|required');
        if (!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model('common/Common_model');
        $player_detail = $this->Common_model->get_roster_detail($post_data['player_team_id']);
        if(!empty($player_detail)){
            if($player_detail['sports_id'] == MOTORSPORT_SPORTS_ID){
                $player_detail['participant'] = 2;
            }
            $post_data['limit'] = 5;
            $post_data['league_id'] = $player_detail['league_id'];
            $post_data['player_id'] = $player_detail['player_id'];
            if($player_detail['is_tour_game'] == "1"){
                $post_data['team_id'] = $player_detail['team_id'];
                $post_data['position'] = $player_detail['position'];
                $post_data['sports_id'] = $player_detail['sports_id'];
                $match_list = $this->Common_model->get_roster_tour_match_history($post_data);
            }else{
                $match_list = $this->Common_model->get_roster_match_history($post_data);
            }
            $player_detail['match'] = $match_list;
            $this->api_response_arry['data'] = $player_detail;
            $this->api_response();
        }else{
            $this->api_response_arry['message'] = $this->lang->line("player_detail_not_found");
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
    }

    /**
     * used for get player card details
     * @param array $post_data
     * @return array
    */
    public function get_player_breakdown_post()
    {        
        $this->form_validation->set_rules('player_team_id', $this->lang->line('player_team_id'), 'trim|required');
        if (!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model('common/Common_model');
        $result = $this->Common_model->get_player_breakdown($post_data['player_team_id']);
        if(empty($result)){
            $this->api_response_arry['message'] = $this->lang->line("player_detail_not_found");
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $result['stats'] = array();
        if($result['is_tour_game'] == 1){
            $result['participant'] = 2;
            if($result['position'] != "CR"){
                $post_data['season_id'] = $result['season_id'];
                $post_data['player_id'] = $result['player_id'];
                $stats = $this->Common_model->get_player_motor_sports_stats($post_data);
                if(!empty($stats)){
                    $result['stats'] = $stats;
                }
            }
            unset($result['home_id']);
            unset($result['away_id']);
        }else{
            $team_ids = array();
            $team_ids[] = $result['home_id'];
            $team_ids[] = $result['away_id'];
            $team_id_str = implode(",",$team_ids);
            $teams = $this->Common_model->get_all_table_data("team_id,IFNULL(display_team_abbr,team_abbr) as name",TEAM,array("team_id IN(".$team_id_str.")"=>NULL));
            $teams = array_column($teams,"name","team_id");
            $result['home'] = $teams[$result['home_id']];
            $result['away'] = $teams[$result['away_id']];
            $result['player_value'] = number_format(($result['score']/$result['salary']),2);
        }
        //additional details
        $score_json = json_decode($result['break_down'],TRUE);
        $break_down = array();
        if($result['sports_id'] == MOTORSPORT_SPORTS_ID && $result['position'] == "CR"){
            foreach($score_json as $bkey=>$brow){
                $break_down[$bkey] = array_sum($brow);
            }
        }else{
            $score_data = array();
            foreach($score_json as $sc_key=>$sc_obj){
                if(is_array($sc_obj)){
                    foreach($sc_obj as $sckey=>$sc_inning){
                        if(is_array($sc_inning)){
                            foreach($sc_inning as $sc_key_str=>$sc_points){
                                if(isset($score_data[$sc_key_str])){
                                    $score_data[$sc_key_str] = $score_data[$sc_key_str] + $sc_points;
                                }else{
                                    $score_data[$sc_key_str] = $sc_points;
                                }
                            }
                        }else{
                            if(isset($score_data[$sckey])){
                                $score_data[$sckey] = $score_data[$sckey] + $sc_inning;
                            }else{
                                $score_data[$sckey] = $sc_inning;
                            }
                        }
                    }
                }else{
                    if(isset($score_data[$sc_key])){
                        $score_data[$sc_key] = $score_data[$sc_key] + $sc_obj;
                    }else{
                        $score_data[$sc_key] = $sc_obj;
                    }
                }

            }
            unset($result['break_down']);
            $input_score = array('sports_id' => $result['sports_id'], "format" => $result['format'],"no_of_sets" => $result['no_of_sets']);
            $category_list = $this->Common_model->get_fantasy_points_category($input_score);
            foreach($category_list as $row){
                $category_name = strtolower(str_replace(" ","_",$row['category_name']));
                $score = "0";
                if(isset($score_data[$row['meta_key']])){
                    $score = number_format($score_data[$row['meta_key']],2);
                }
                if(!in_array($row['meta_key'],array("MINIMUM_BOWLING_BOWLED_OVER","MINIMUM_BALL_PLAYED"))){
                    $break_down[$category_name][] = array("name"=>$row['score_position'],"points"=>$row['score_points'],"score"=>$score);
                }
            }
        }
        $result['break_down'] = $break_down;
        //echo "<pre>";print_r($result);die;
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }
}

/* End of file  */
/* Location: ./application/controllers/ */