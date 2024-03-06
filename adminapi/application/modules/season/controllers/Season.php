<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Season extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();
        $this->admin_roles_manage($this->admin_id,'dfs');
		$this->load->model('season/Season_model');
	}

    /**
     * Used for get dfs season list 
     * @param array $post_data
     * @return json array
     */
    public function get_season_list_post()
    {   
        $this->form_validation->set_rules('sports_id','sports id','trim|required');
        $this->form_validation->set_rules('status','status','trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $match_list = $this->Season_model->get_season_list($post_data);
        //echo "<pre>";print_r($match_list);die;
        $this->api_response_arry['data'] = $match_list;
        $this->api_response();
    }

    /**
    * Function used for get match players list
    * @param int $season_id
    * @return array
    */
    public function get_season_players_post()
    {
        $this->form_validation->set_rules('season_id', 'season id', 'trim|required');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $season_id = $post_data['season_id'];
        $result = $this->Season_model->get_season_detail($season_id,"IFNULL(CM.setting,'[]') as setting");
        if(empty($result))
        {
            $this->api_response_arry['message'] = $this->lang->line('fixture_detail_not_found');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $result['setting'] = json_decode($result['setting'],TRUE);
        $result['roster_list'] = $this->Season_model->get_season_player_list($season_id);
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    private function validate_setting($sports_id,$post_data){
        $setting = isset($post_data['setting']) ? $post_data['setting'] : array();
        if(empty($setting) && $sports_id != TENNIS_SPORTS_ID){
            return array();
        }else if(empty($setting) && $sports_id == TENNIS_SPORTS_ID){
            $setting = get_sports_team_config($sports_id,$post_data['type']);
            return $setting;
        }else{
            $is_valid = 1;
            $message = "";
            if(!isset($setting['max_player_per_team'])){
                $is_valid = 0;
                $message = "Max player per team field required.";
            }else if(!isset($setting['team_player_count'])){
                $is_valid = 0;
                $message = "Team player limit field required.";
            }else if(empty($setting['pos'])){
                $is_valid = 0;
                $message = "Position limit data required.";
            }else if($setting['max_player_per_team'] < 1 || $setting['max_player_per_team'] > 10){
                $is_valid = 0;
                $message = "Max player team limit should be 1-10.";
            }else if($setting['team_player_count'] < 1 || $setting['team_player_count'] > 11){
                $is_valid = 0;
                $message = "Team player limit should be 1-11.";
            }else if(!empty($setting['pos'])){
                foreach($setting['pos'] as $pos_key=>$pos_val){
                    if($pos_val < 1 || $pos_val > 8){
                        $is_valid = 0;
                        $message = "Position limit should be 1-8.";
                    }
                }
            }
            if($is_valid == 0){
                $this->api_response_arry['message'] = $message;
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response();
            }

            if($sports_id == TENNIS_SPORTS_ID){
                $setting['vc'] = 0;
            }
            return $setting;
        }
    }

    /**
    * Function used for publish fixture for create contest
    * @param int $season_id
    * @param array $roster_list
    * @return array
    */
    public function publish_fixture_post()
    {
        $this->form_validation->set_rules('season_id', 'season id', 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $season_id = $post_data['season_id'];
        $season_details = $this->Season_model->get_season_detail($season_id);
        //echo "<pre>";print_r($season_details);die;
        if(empty($season_details))
        {
            $this->api_response_arry['message'] = $this->lang->line('fixture_detail_not_found');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $post_data['type'] = $season_details['type'];
        $setting = $this->validate_setting($season_details['sports_id'],$post_data);
        //echo "<pre>";print_r($setting);die;
        //prepare players
        $sport_config = get_sports_player_config($season_details['sports_id']);
        $player_team_arr = array();
        $player_display_name_arr = array();
        if(!empty($post_data['roster_list']))
        {
            foreach($post_data['roster_list'] as $hkey => $hvalue)
            {
                if(empty($hvalue['salary']) || $hvalue['salary'] == 0){
                    $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry["message"] = $this->lang->line('roster_wrong_salary_error');
                    $this->api_response();
                }else if($hvalue['salary'] < $sport_config['min_sal'] || $hvalue['salary'] > $sport_config['max_sal']){
                    $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry["message"] = "Player salary should be between ".$sport_config['min_sal']." and ".$sport_config['max_sal'];
                    $this->api_response();
                }
                $player_team_arr[] = array(
                    "player_team_id" => $hvalue['player_team_id'],
                    "salary" => number_format($hvalue['salary'],2,".",""),  
                    "position" => $hvalue['position'],
                    "is_published" => $hvalue['is_published']
                );

                if(empty($hvalue['display_name'])){
                    $this->api_response_arry['message'] = $this->lang->line('roster_display_name_error');
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response();
                }
                $player_display_name_arr[] = array(
                    "player_id" => $hvalue['player_id'],
                    "display_name" => $hvalue['display_name']
                );
            }
        }
        
        //update player salary 
        if(!empty($player_team_arr)){
            $this->Season_model->update_season_roster_salary($player_team_arr);
        }
        //update player display name 
        if(!empty($player_display_name_arr)){
            $this->Season_model->update_player_display_name($player_display_name_arr);
        }

        $cm_name = $season_details['home']." vs ".$season_details['away'];
        $is_tour_game = 0;
        if(in_array($season_details['sports_id'],$this->tour_game_sports)){
            $is_tour_game = 1;
            $cm_name = $season_details['tournament_name'];
            $teams = $this->Season_model->get_tour_game_sport_teams($season_details['sports_id']);
            $match_team = array_column($teams,"team_abbr","team_id");
        }else{
            $match_team = array();
            $match_team[$season_details['home_id']] = $season_details['home'];
            $match_team[$season_details['away_id']] = $season_details['away'];
        }
        
        $is_valid = 1;
        $position = $sport_config['position'];
        $position_err = array();
        $player_count = $this->Season_model->get_season_player_count_data($season_id);
        foreach($player_count as $player){
            if($player['total'] < $position[$player['position']]){
                $team_name = $match_team[$player['team_id']];
                $is_valid = 0;
                $position_err[] = $position[$player['position']]." ".$player['position']." in ".$team_name;
            }
        }

        if($is_valid == 0 && !empty($position_err)){
            $err_msg = "Please select atleast ".implode(", ", $position_err);
            $this->api_response_arry['message'] = $err_msg;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $cm_id = $season_details['collection_master_id'];
        if($season_details['is_published'] == "0" || $cm_id == "0"){
            $secong_inning_date = "";
            $allow_2nd_inning = isset($this->app_config['allow_2nd_inning']) ? $this->app_config['allow_2nd_inning']['key_value'] : 0;
            if($allow_2nd_inning == 1 && in_array($season_details['format'],array(1,3)) && $season_details['sports_id'] == CRICKET_SPORTS_ID){
                if($season_details['2nd_inning_date'] != ""){
                    $secong_inning_date = $season_details['2nd_inning_date'];
                }else{
                    $second_inning_interval = second_inning_game_interval($season_details['format']);
                    $secong_inning_date = date("Y-m-d H:i:s",strtotime($season_details['season_scheduled_date'].' +'.$second_inning_interval.' minutes'));
                }
            }

            $season_arr = array();
            $season_arr['league_id'] = $season_details['league_id'];
            $season_arr['season_id'] = $season_details['season_id'];
            $season_arr['season_scheduled_date'] = $season_details['season_scheduled_date'];
            $season_arr['secong_inning_date'] = $secong_inning_date;
            $season_arr['collection_name'] = $cm_name;
            $season_arr['is_tour_game'] = $is_tour_game;
            $season_arr['setting'] = $setting;
            //echo "<pre>";print_r($season_arr);die;
            $cm_id = $this->Season_model->save_fixture_publish($season_arr);
            if(!$cm_id){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line("publish_season_error");
                $this->api_response();
            }
        }
        //remove cache
        if($cm_id > 0){
            //update collection master id in tournament for fixture
            $this->load->model('tournament/Tournament_model');
            $this->Tournament_model->update_tournament_cm_id($cm_id,$season_details['season_id']);
                
            $this->delete_collection_cache($cm_id);
        }

        $this->api_response_arry['data'] = array("collection_master_id"=>$cm_id);
        $this->api_response_arry['message'] = $this->lang->line("publish_season_success");
        $this->api_response();
    }

    /**
     * Used for delete collect cache and s3 json file
     * @param int $cm_id
     * @return boolean
    */
    private function delete_collection_cache($cm_id){
        $cache_file = "roster_list_".$cm_id;
        $s3_file = "collection_roster_list_".$cm_id;
        $this->delete_cache_data($cache_file);
        $this->push_s3_data_in_queue($s3_file,array(),"delete");
        return true;
    }

    /**
    * Function used for get season details
    * @param int $season_id
    * @return array
    */
    public function get_season_details_post()
    {
        $this->form_validation->set_rules('season_id', 'season id', 'trim|required');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $season_id = $post_data['season_id'];
        $result = $this->Season_model->get_season_detail($season_id);
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
     * Used for get fixture(collection) details
     * @param int $cm_id
     * @return array
     */
    public function get_fixture_details_post() 
    {
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $collection_master_id = $post_data['collection_master_id'];
        $is_trnt = isset($post_data['is_trnt']) ? $post_data['is_trnt'] : 0;
        $fixture_details = $this->Season_model->get_fixture_detail($collection_master_id);
        if(!empty($fixture_details)){
            $season_ids = array_unique(explode(",",$fixture_details['season_ids']));
            if($fixture_details['is_tour_game'] == 1){
                $match_list = $this->Season_model->get_tour_season_detail($season_ids);
                $fixture_details['match_event'] = 0;
                if(isset($match_list['0']['match_event'])){
                    $fixture_details['match_event'] = $match_list['0']['match_event'];
                }
            }else{
                $match_list = $this->Season_model->get_fixture_season_detail($season_ids,"status,playing_eleven_confirm");
                $fixture_details['playing_eleven_confirm'] = 0;
                if($fixture_details['season_game_count'] == 1 && !empty($match_list)){
                    $fixture_details['playing_eleven_confirm'] = $match_list['0']['playing_eleven_confirm'];
                }

                $fixture_details['allow_bots'] = 0;
                if(in_array($fixture_details['sports_id'],[5,7])){
                    $setting = json_decode($fixture_details['setting'],TRUE);
                    $fixture_details['allow_bots'] = 1;
                    if(!empty($setting)){
                        $fixture_details['allow_bots'] = 0;
                    }
                }
            }
            $fixture_details['match'] = $match_list;
            unset($fixture_details['season_ids']);
            unset($fixture_details['setting']);
        }

        $fixture_details['match_started'] = 0;
        if(strtotime($fixture_details['season_scheduled_date']) <= strtotime(format_date())){
            $fixture_details['match_started'] = 1;
        }

        $fixture_details['tournament_enable'] = 0;
        if($is_trnt == 1){
            $tournament = $this->Season_model->get_fantasy_single_row("COUNT(tournament_season_id) as total",TOURNAMENT_SEASON,array("cm_id" => $collection_master_id));
            if(!empty($tournament) && $tournament['total'] > 0){
                $fixture_details['tournament_enable'] = 1;
            }
        }

        $this->api_response_arry['data'] = $fixture_details;
        $this->api_response();
    }

    /**
    * Function used to pin any fixture to list at top
    * @param int $sports_id. league_id, season_game_uid
    * @return array
    */
    public function pin_fixture_post()
    {
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $result = $this->Season_model->mark_pin_fixture($post_data);
        if($result){
            //remove lobby fixture
            $this->push_s3_data_in_queue("lobby_fixture_list_".$post_data['sports_id'],array(),"delete");

            $this->api_response_arry['message'] = "Fixture mark pin successfully";
            if(isset($post_data['is_pin_fixture']) && $post_data['is_pin_fixture'] == "0")
            {
                $this->api_response_arry['message'] = "Fixture removed from pin successfully";
            }
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('no_change');
            $this->api_response();
        }
    }

    /**
    * Function used for save fixture delay data
    * @param array $post_data
    * @return array
    */
    public function update_fixture_delay_post(){
        $this->form_validation->set_rules('season_id', 'season id', 'trim|required');
        $this->form_validation->set_rules('delay_hour','Hour','trim');
        $this->form_validation->set_rules('delay_minute','Minute','trim');
        $this->form_validation->set_rules('delay_message','Delay Message','trim|required|max_length[160]');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $current_date = format_date();
        $season_id = $post_data['season_id'];
        if(!isset($post_data['delay_hour']) || $post_data['delay_hour'] < 0 || $post_data['delay_hour'] > 47){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_delay_0_48_msg');
            $this->api_response();
        }

        if(!isset($post_data['delay_minute']) || $post_data['delay_minute'] < 0 || $post_data['delay_minute'] > 59){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_delay_0_59_msg');
            $this->api_response();
        }

        $delay_hour = $delay_minute = 0;
        if(isset($post_data['delay_hour']) && $post_data['delay_hour'] != ""){
            $delay_hour = $post_data['delay_hour'];
        }
        if(isset($post_data['delay_minute']) && $post_data['delay_minute'] != ""){
            $delay_minute = $post_data['delay_minute'];
        }

        $delay_minutes = convert_hour_minute_to_minute($delay_hour,$delay_minute);
        if($delay_minutes < 0){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_delay_hour_minute_msg');
            $this->api_response();
        }

        $season_details = $this->Season_model->get_season_detail($season_id);
        if(empty($season_details))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('fixture_detail_not_found');
            $this->api_response();
        }else if(strtotime($season_details['season_scheduled_date']) <= strtotime($current_date)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_start_delay_msg');
            $this->api_response();
        }

        $delay_min_diff = $delay_minutes - $season_details['delay_minute'];
        if($delay_min_diff <= 0){
            $new_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$delay_min_diff.' minutes', strtotime($season_details['season_scheduled_date'])));
            if(strtotime($new_scheduled_date) <= $current_date){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('match_prepond_time_limit_msg');
                $this->api_response();
            }
        }
        
        $season_details['new_delay_minute'] = $delay_minutes;
        $season_details['delay_message'] = $post_data['delay_message'];
        $this->Season_model->update_match_delay_data($season_details);

        $this->flush_cache_data();
        //update lobby fixture file
        $this->push_s3_data_in_queue("lobby_fixture_list_".$season_details['sports_id'],array(),"delete");

        $this->api_response_arry['message'] = $this->lang->line('match_delay_message');
        $this->api_response();
    }

    /**
    * Function used for custom notification for fixture
    * @param array $post_data
    * @return array
    */
    public function update_fixture_custom_message_post(){
        $post_data = $this->input->post();
        $this->form_validation->set_rules('season_id', 'season id', 'trim|required');
        if(empty($post_data['is_remove']) || !isset($post_data['is_remove']))
        {
            $this->form_validation->set_rules('custom_message','Custom Message','trim|required|max_length[160]');
        }
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        
        $current_date = format_date();
        $season_id = $post_data['season_id'];
        $season_details = $this->Season_model->get_season_detail($season_id);
        if(empty($season_details))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('fixture_detail_not_found');
            $this->api_response();
        }

        if(isset($post_data['is_remove']) && $post_data['is_remove'] == 1){
            $post_data['custom_message'] = "";
        }

        $result = $this->Season_model->update_match_custom_message($post_data);
        if($result){
            if(isset($season_details['collection_master_id']) && $season_details['collection_master_id'] > 0){
                $this->delete_cache_data("fixture_".$season_details['collection_master_id']);
            }
            //update lobby fixture file
            $this->push_s3_data_in_queue("lobby_fixture_list_".$season_details['sports_id'],array(),"delete");
        }
        $this->api_response_arry['message'] = $this->lang->line('match_custom_msg_sent');
        $this->api_response();
    }

    /**
    * Function used for get season players list for lineup out
    * @param int $season_id
    * @return array
    */
    public function get_season_teams_and_rosters_post()
    {
        $this->form_validation->set_rules('season_id', 'season id', 'trim|required');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $season_id = $post_data['season_id'];
        $season_details = $this->Season_model->get_season_detail($season_id);
        if(empty($season_details))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('fixture_detail_not_found');
            $this->api_response();
        }
        $season_details['playing_list'] = json_decode($season_details['playing_list'],true); 
        $season_details['substitute_list'] = json_decode($season_details['substitute_list'],true); 
        //echo "<pre>";print_r($season_details);die;
        $team_list = array();
        $team_list[$season_details['home_id']] = array("team_id"=>$season_details['home_id'],"team_abbr"=>$season_details['home'],"team_name"=>$season_details['home_name'],"flag"=>$season_details['home_flag']);
        $team_list[$season_details['away_id']] = array("team_id"=>$season_details['away_id'],"team_abbr"=>$season_details['away'],"team_name"=>$season_details['away_name'],"flag"=>$season_details['away_flag']);
        if(!empty($team_list)){
            $roster_list = $this->Season_model->get_season_player_list($season_id);
            if(!empty($roster_list)){
                foreach ($roster_list as $pkey => $pvalue)
                {
                    $pvalue['is_playing'] = 0;
                    if(!empty($season_details['playing_list']) && $pvalue['player_id'] && in_array($pvalue['player_id'],$season_details['playing_list'])) {
                        $pvalue['is_playing'] = 1; 
                    }
                    $pvalue['is_sub'] = 0;
                    if(!empty($season_details['substitute_list']) && $pvalue['player_id'] && in_array($pvalue['player_id'],$season_details['substitute_list'])) {
                        $pvalue['is_sub'] = 1; 
                    }
                    $team_list[$pvalue['team_id']]['roster_list'][] = $pvalue;
                }
            }
        }
        $season_details['team_list'] = array_values($team_list);
        unset($season_details['playing_list']);
        unset($season_details['substitute_list']);
        $this->api_response_arry['data'] = $season_details;
        $this->api_response();
    }

    /**
    * Function used for save playing11 data
    * @param array $post_data
    * @return array
    */
    public function save_playing11_post()
    {
        $this->form_validation->set_rules('season_id', 'season id', 'trim|required');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $season_id = $post_data['season_id'];
        if(empty($post_data['playing_list'])){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('lineupout_player_empty_error');
            $this->api_response();
        }

        $season_details = $this->Season_model->get_season_detail($season_id);
        if(empty($season_details))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('fixture_detail_not_found');
            $this->api_response();
        }else if(strtotime($season_details['season_scheduled_date']) <= strtotime($current_date)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_start_lineupout_error');
            $this->api_response();
        }

        //echo "<pre>";print_r($season_details);die;
        //update playing11
        $post_data['substitute_list'] = isset($post_data['substitute_list']) ? $post_data['substitute_list'] : array();
        $result = $this->Season_model->save_playing11($post_data);
        if($result) {
            $this->load->helper('queue');
            $server_name = get_server_host_name();
            //push cron for pull teams
            if($season_details['collection_master_id'] > 0 && $season_details['playing_eleven_confirm'] == 1 && in_array($season_details['sports_id'],[CRICKET_SPORTS_ID,SOCCER_SPORTS_ID])){
                $content = array();
                $content['url'] = $server_name."/cron/cron/sync_bot_teams/".$season_details['collection_master_id'];
                add_data_in_queue($content,'pl_teams');

                $content = array();
                $content['url'] = $server_name."/cron/cron/pl_match_teams/".$season_id."?lineup_out=1";
                add_data_in_queue($content,'pl_teams');

                if(PL_LOG_TX){
                    log_message("error","LINEUPOUT TRIGGER FROM ADMIN : MATCH : ".$season_details['season_game_uid']." | TIME : ".format_date());
                }
            }

            //for lineup out push 
            $content = array();
            $content['url'] = $server_name."/cron/cron/process_lineupout_notification_for_game/".$season_details['sports_id']."/".$season_details['season_game_uid'];
            add_data_in_queue($content,'lineupout_game_process');

            $this->delete_collection_cache($season_details['collection_master_id']);
            
            $this->api_response_arry['message'] = $this->lang->line('playing11_success');
            $this->api_response();
        } else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('playing11_error');
            $this->api_response();
        }
    }

    /**
    * function used for get match stats list
    * @param array $post
    * @return array
    */
    public function get_season_stats_post()
    {
        $this->form_validation->set_rules('season_id', 'season Id', 'trim|required');
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $season_id = $post_data['season_id'];
        $season_details = $this->Season_model->get_season_detail($season_id,"score_data,status_overview,match_status");
        if(empty($season_details))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('fixture_detail_not_found');
            $this->api_response();
        }
        $post_data['sports_id'] = $season_details['sports_id'];
        $result = $this->Season_model->get_all_season_stats($post_data);
        if(isset($season_details['score_data']) && $season_details['score_data'] != ""){
            $season_details['score_data'] = json_decode($season_details['score_data'],TRUE);
        }
        unset($season_details['playing_list']);
        $result['season_data'] = $season_details;
        $result['season_data']['match_started'] = 0;
        if(strtotime(format_date()) >= strtotime($result['season_data']['season_scheduled_date'])){
            $result['season_data']['match_started'] = 1;
        }

        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
    * function used for update match manual score update
    * @param array $post_data
    * @return array
    */
    public function update_match_status_post()
    {
        $this->form_validation->set_rules('season_id','season id','trim|required');
        $this->form_validation->set_rules('match_status','Status','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $result = $this->Season_model->update_season_status($post_data);
        if($result){
            $this->api_response_arry['message'] = $this->lang->line('status_upd_success');
            $this->api_response_arry['data'] = array();
            $this->api_response();
        }else{
            $this->api_response_arry['message'] = 'Status update failed. please try again.';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
    }

    /**
    * Function used for update player manual stats
    * @param array $post_data
    * @return array
    */
    public function update_player_match_score_post()
    {
        $this->form_validation->set_rules('season_id','Season id','trim|required');
        $this->form_validation->set_rules('match_inning','Match inning','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        if(empty($post_data['player_data'])){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Player data field required.";
            $this->api_response();
        }
        
        $result = $this->Season_model->update_player_match_score($post_data);
        if($result){
            $this->api_response_arry['message'] = $this->lang->line('player_match_score_updated');
            $this->api_response_arry['data'] = array();
            $this->api_response();
        }else{
            $this->api_response_arry['message'] = 'Status update failed. please try again.';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
    }

    public function recalculate_match_score_post()
    {
        $this->form_validation->set_rules('season_id', 'season Id', 'trim|required');
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $season_id = $post_data['season_id'];
        $season_details = $this->Season_model->get_season_detail($season_id);
        if(empty($season_details))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('fixture_detail_not_found');
            $this->api_response();
        }

        $sports_id = $season_details['sports_id'];
        $season_game_uid = $season_details['season_game_uid'];
        $league_id = $season_details['league_id'];
        $this->load->helper('queue');
        $server_name = get_server_host_name();
        $content = array();
        if($sports_id == CRICKET_SPORTS_ID)
        {
            $content['url'] = $server_name."/cron/cricket/vinfotech/calculated_fantasy_score_by_match_id/".$league_id."/".$season_game_uid;
        }
        if($sports_id == KABADDI_SPORTS_ID)
        {
           $content['url'] = $server_name."/cron/kabaddi/vinfotech/calculated_fantasy_score_by_match_id/".$league_id."/".$season_game_uid;
        }
        if($sports_id == SOCCER_SPORTS_ID)
        {
           $content['url'] = $server_name."/cron/soccer/vinfotech/calculated_fantasy_score_by_match_id/".$league_id."/".$season_game_uid;
        }

        if($sports_id == BASEBALL_SPORTS_ID)
        {
           $content['url'] = $server_name."/cron/baseball/vinfotech/calculated_fantasy_score_by_match_id/".$league_id."/".$season_game_uid;
        }

        add_data_in_queue($content,'point_update_cron');

        //update points in user lineup
        $content_point = array();
        $content_point['url'] = $server_name."/cron/dfs/update_lineup_score/".$sports_id;
        add_data_in_queue($content_point, 'point_update_cron');
        
        $this->api_response_arry['message'] = 'Score recalculate request received. point update in some time.';
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }

    /**
    * function used for move match to live from completed
    * @param array $post
    * @return array
    */
    public function move_match_to_live_post()
    {
        $this->form_validation->set_rules('season_id','season id','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $season_id = $post_data['season_id'];
        $season_details = $this->Season_model->get_season_detail($season_id);
        if(empty($season_details))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('fixture_detail_not_found');
            $this->api_response();
        }
        
        $post_data['cm_id'] = $season_details['collection_master_id'];
        $result = $this->Season_model->move_season_to_live($post_data);
        if($result){
            $this->api_response_arry['message'] = $this->lang->line('status_upd_success');
            $this->api_response_arry['data'] = array();
            $this->api_response();
        }else{
            $this->api_response_arry['message'] = 'Status update failed. please try again.';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
    }

    /**
     * @since June 2021
     * @uses to update 2nd inning date in collection master , season, contest tables
     * @param scheduled_date DATETIME date to be updated
     * @param season_game_uid match unique id
     * @param league_id 
     * **/
    public function update_2nd_inning_date_post()
    {
        $this->form_validation->set_rules('collection_master_id','collection master id','trim|required');
        $this->form_validation->set_rules('scheduled_date', 'Scheduled Date', 'trim|required');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $cm_id = $post_data['collection_master_id'];
        $scheduled_date = $post_data['scheduled_date'];

        //check 2nd inning allowed or not
        $this->check_module_enabled('allow_2nd_inning');

        $fixture_details = $this->Season_model->get_fixture_detail($cm_id);
        if(empty($fixture_details))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('fixture_detail_not_found');
            $this->api_response();
        }else if($fixture_details['season_game_count'] != 1 || empty($fixture_details['2nd_inning_date'])){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Second inning not available for this fixture.";
            $this->api_response();
        }

        //check date
        $current_date = format_date();
        if(strtotime($current_date) > strtotime($fixture_details['2nd_inning_date']))
        {
            $this->api_response_arry['message'] = "You can not update 2nd inning date now.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        if(strtotime($current_date) > strtotime($scheduled_date))
        {
            $this->api_response_arry['message'] = "You can not update past time.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        //echo "<pre>";print_r($fixture_details);die;

        //update 2nd_inning date in collection master,contest
        $post_data['season_id'] = $fixture_details['season_ids'];
        $updated = $this->Season_model->update_2nd_inning_date($post_data);
        if($updated)
        {
            $input_arr = array();
            $input_arr['lang_file'] = '0';
            $input_arr['file_name'] = 'lobby_fixture_list_';
            $input_arr['sports_ids'] = array(CRICKET_SPORTS_ID);
            $this->delete_cache_and_bucket_cache($input_arr);

            $this->api_response_arry['message'] = $this->lang->line('success_2nd_inning_date_updated');;
            $this->api_response();
        }
        else{
            $this->api_response_arry['global_error'] = $this->lang->line('err_problem_while_date_update');;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
    }
}

/* End of file Season.php */
/* Location: /admin/application/controllers/Season.php */

