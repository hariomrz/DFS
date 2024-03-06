<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tournament extends MYREST_Controller {

    public function __construct() {
        parent::__construct();
        $this->admin_roles_manage($this->admin_id,'dfs');
		$this->load->model('tournament/Tournament_model');
    }

    /**
     * Used for get tournament master data
     * @param void
     * @return json array
     */
    public function get_master_data_post()
    {
        $prize_type = array();
        $prize_type[] = array("label"=>"Bonus Cash","value"=>"0");
        $prize_type[] = array("label"=>"Real Cash","value"=>"1");
        $allow_coin_system =  isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
        if($allow_coin_system == 1){
            $prize_type[] = array("label"=>"Coins","value"=>"2");
        }
        $prize_type[] = array("label"=>"Merchandise","value"=>"3");
        
        $result = array('prize_type'=> $prize_type);

        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
     * Used for get sports wise league list 
     * @param int $sports_id
     * @return json array
     */   
    public function get_sport_leagues_post()
    {
        $this->form_validation->set_rules('sports_id', 'sports id','trim|required');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $result = $this->Tournament_model->get_sport_league_list($post_data);
        
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
     * Used for get league published fixture
     * @param int $league_id
     * @return json array
     */
    public function get_published_fixtures_post()
    {
        $this->form_validation->set_rules('league_id', 'league id','trim|required');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $league_info = $this->Tournament_model->get_single_row('sports_id',LEAGUE,array('league_id' => trim($post_data['league_id'])));
        if(!empty($league_info)){
            $post_data['sports_id'] = $league_info['sports_id'];
        }
        $result = $this->Tournament_model->get_published_fixtures($post_data);
        
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
     * Used for save tournament
     * @param int $league_id
     * @param string $name
     * @param array $season_ids
     * @return json array
     */
    public function save_tournament_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('league_id', 'League id', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('name', 'Tournament name', 'trim|required');
        $this->form_validation->set_rules('no_of_fixture', 'Fixture Type', 'trim|required');
        $this->form_validation->set_rules('is_top_team', 'Fixture Team', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        //echo "<pre>";print_r($post_data);die;
        $no_of_fixture = isset($post_data['no_of_fixture']) ? $post_data['no_of_fixture'] : 0;
        $is_top_team = isset($post_data['is_top_team']) ? $post_data['is_top_team'] : 0;
        $season_ids = isset($post_data['season_ids']) ? $post_data['season_ids'] : array();
        $end_date = isset($post_data['end_date']) ? $post_data['end_date'] :'';
        if(empty($season_ids) || count($season_ids) < 1) { 
            $this->api_response_arry['message'] = 'Please select at least one fixtures.';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        if(!isset($post_data['prize_detail']) || empty($post_data['prize_detail'])) { 
            $this->api_response_arry['message'] = "Prize distribution details can't empty.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        if(isset($post_data['banner_images']) && !empty($post_data['banner_images']) && count($post_data['banner_images']) > 5) { 
            $this->api_response_arry['message'] = "Max 5 banner images allowed.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        if($no_of_fixture > 0 && $is_top_team == 0){
            $this->api_response_arry['message'] = "All teams not allowed for nth fixture option.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        
        $record_info = $this->Tournament_model->get_single_row('*',TOURNAMENT,array('name' => trim($post_data['name'])));
        if(!empty($record_info)){
            $this->api_response_arry['message'] = "Tournament name already exist.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $match_list = $this->Tournament_model->get_fixture_collection($season_ids);
        if(count($match_list) != count($season_ids)){
            $this->api_response_arry['message'] = "Invalid selected match ids.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $schedule_dates = array_column($match_list,"season_scheduled_date");
        
        
        if(!empty($end_date) && $end_date > max($schedule_dates)){
            $post_data['end_date'] = $end_date;
        }else{
            $post_data['end_date'] = max($schedule_dates);
        }
        
        $post_data['start_date'] = min($schedule_dates);
        $post_data['match_list'] = $match_list;
        $post_data['no_of_fixture'] = $no_of_fixture;
        $post_data['is_top_team'] = $is_top_team;
        //echo "<pre>";print_r($post_data);die;
        $tournament_id = $this->Tournament_model->save_tournament($post_data);
        if(!$tournament_id){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("tournament_save_error");
            $this->api_response();
        }

        $tmnt_sports_list_cache_key = "dfs_tournament_sports_list";
        $this->delete_cache_data($tmnt_sports_list_cache_key);

        $this->api_response_arry['message'] = $this->lang->line("tournament_save_success");
        $this->api_response_arry['data'] = array("tournament_id"=>$tournament_id);
        $this->api_response();
    }

    /**
     * Used for get dfs tournament list 
     * @param array $post_data
     * @return json array
     */
    public function get_tournament_list_post()
    {   
        $this->form_validation->set_rules('sports_id','sports id','trim|required');
        $this->form_validation->set_rules('status','status','trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $tournament_list = $this->Tournament_model->get_tournament_list($post_data);
        //echo "<pre>";print_r($tournament_list);die;
        $this->api_response_arry['data'] = $tournament_list;
        $this->api_response();
    }

    /**
     * Used for get published tournament fixture
     * @param int $league_id
     * @return json array
     */
    public function get_tournament_fixtures_post()
    {
        $this->form_validation->set_rules('tournament_id', 'tournament id','trim|required');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $result = $this->Tournament_model->get_tournament_fixtures($post_data['tournament_id']);
        
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
     * Used for add new published fixture in tournament
     * @param int $league_id
     * @param int $league_id
     * @return json array
     */
    public function save_tournament_fixtures_post()
    {
        $this->form_validation->set_rules('tournament_id', 'tournament id','trim|required');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $season_ids = isset($post_data['season_ids']) ? $post_data['season_ids'] : array();
        if(empty($season_ids) || count($season_ids) < 1) { 
            $this->api_response_arry['message'] = 'Please select at least one fixtures.';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $match_list = $this->Tournament_model->get_fixture_collection($season_ids);
        if(count($match_list) != count($season_ids)){
            $this->api_response_arry['message'] = "Invalid selected match ids.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $post_data['match_list'] = $match_list;
        $result = $this->Tournament_model->save_tournament_fixture($post_data);
        if($result){
            $cache_key = "tournament_".$post_data['tournament_id'];
            $this->delete_cache_data($cache_key);
            
            $this->delete_cache_data("trnt_fixture_".$post_data['tournament_id']);

            $this->api_response_arry['message'] = "Tournament details saved successfully.";
            $this->api_response();
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("tournament_save_error");
            $this->api_response();
        }
    }

    /**
    * Function used for get tournament details
    * @param array $post_data
    * @return array
    */
    public function get_tournament_detail_post()
    {
        $this->form_validation->set_rules('tournament_id', 'Tournament id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $tournament_id = $post_data['tournament_id'];
        $tournament = $this->Tournament_model->get_tournament_detail($tournament_id);
        if(!empty($tournament)){
            $season_ids = array_unique(explode(",",$tournament['season_ids']));
            if(in_array($tournament['sports_id'],$this->tour_game_sports)){
                $tournament['is_tour_game'] = "1";
                $match_list = $this->Tournament_model->get_tour_game_sports_fixtures($season_ids,"S.status");
            }else{
                $tournament['is_tour_game'] = "0";
                $match_list = $this->Tournament_model->get_fixture_season_detail($season_ids,"S.status");

            }
            $tournament['match'] = $match_list;
        }
        //echo "<pre>";print_r($tournament);die;
        $this->api_response_arry['data'] = $tournament;
        $this->api_response();
    }

    /**
    * Function used for get tournament participants list
    * @param array $post_data
    * @return array
    */
    public function get_tournament_users_post()
    {
        $this->form_validation->set_rules('tournament_id', 'Tournament id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $result = $this->Tournament_model->get_tournament_users($post_data);
        if(!empty($result['result'])){
            $result['result'] = array_column($result['result'],NULL,"user_id");
            $user_ids = array_keys($result['result']);
            $user_data = $this->Tournament_model->get_user_detail_by_user_id($user_ids);
            $user_data = array_column($user_data,NULL,"user_id");
            if(!empty($user_data)){
                $result['result'] = array_values(array_replace_recursive($result['result'],$user_data));
            }
        }
        //echo "<pre>";print_r($result);die;
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
    * Function used for get user points details
    * @param int $leaderboard_id
    * @return string
    */
    public function get_user_team_detail_post()
    {
        $this->form_validation->set_rules('history_id', "id", 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $record_info = $this->Tournament_model->get_single_row("history_id,user_id,total_score,IF(rank_value=0,'-',rank_value) as rank_value,is_winner,IFNULL(prize_data,'[]') as prize_data,tournament_id",TOURNAMENT_HISTORY,array("history_id"=>$post_data['history_id']));
        if(!empty($record_info)){
            $user_data = $this->Tournament_model->get_user_detail_by_user_id($record_info['user_id']);
            $record_info = array_merge($record_info,$user_data);
            
            $record_info['match'] = array();            
            $user_id = $record_info['user_id'];
            $tournament_id = $record_info['tournament_id'];
            $record_info['match'] = $this->Tournament_model->get_user_match_list($tournament_id,$user_id);
            $record_info['total_match'] = count($record_info['match']);
        }
        $this->api_response_arry['data'] = $record_info;
        $this->api_response();
    }

    /**
    * Function used for mark pin tournament
    * @param array $post_data
    * @return array
    */
    public function mark_pin_post(){

        $this->form_validation->set_rules('tournament_id', 'tournament id', 'trim|required');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $tournament_id = $post_data['tournament_id'];
        $current_date = format_date();
        $record_info = $this->Tournament_model->get_single_row("tournament_id,sports_id,is_pin",TOURNAMENT,array("tournament_id"=>$tournament_id));
        if(empty($record_info)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid tournament id. please provide valid id.";
            $this->api_response();
        }

        $is_pin = 1;
        if(isset($record_info['is_pin']) && $record_info['is_pin'] == "1"){
            $is_pin = 0;
        }
        $result = $this->Tournament_model->update(TOURNAMENT,array("is_pin"=>$is_pin,"modified_date"=>$current_date),array("tournament_id"=>$tournament_id));
        if($result)
        {
            if($is_pin == "1"){
                $this->Tournament_model->update(TOURNAMENT,array("is_pin"=>0,"modified_date"=>$current_date),array("is_pin"=>"1","tournament_id !="=>$tournament_id,"sports_id"=>$record_info['sports_id']));
            }

            //delete cache
            $pin_tournament_cache = 'pin_tournament_'.$record_info['sports_id'];
            $this->delete_cache_data($pin_tournament_cache);

            $this->api_response_arry['message'] = "Tournament pin status updated successfully.";
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
    * Function used for mark cancel tournament
    * @param array $post_data
    * @return array
    */
    public function mark_cancel_post(){

        $this->form_validation->set_rules('tournament_id', 'tournament id', 'trim|required');
        $this->form_validation->set_rules('cancel_reason', 'cancel reason', 'trim|required');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $tournament_id = $post_data['tournament_id'];
        $cancel_reason = isset($post_data['cancel_reason']) ? $post_data['cancel_reason'] : "";
        $current_date = format_date();
        $record_info = $this->Tournament_model->get_single_row("tournament_id,sports_id,status",TOURNAMENT,array("tournament_id"=>$tournament_id));
        if(empty($record_info)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid tournament id. please provide valid id.";
            $this->api_response();
        }else if(!empty($record_info) && $record_info['status'] >= "2"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Tournament already closed.";
            $this->api_response();
        }else if(!empty($record_info) && $record_info['status'] == "1"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Tournament already cancelled.";
            $this->api_response();
        }

        $result = $this->Tournament_model->update(TOURNAMENT,array("status"=>"1","cancel_reason"=>$cancel_reason,"modified_date"=>$current_date),array("tournament_id"=>$tournament_id));
        if($result)
        {
            //delete cache
            $pin_tournament_cache = 'pin_tournament_'.$record_info['sports_id'];
            $this->delete_cache_data($pin_tournament_cache);

            $cache_key = "tournament_".$tournament_id;
            $this->delete_cache_data($cache_key);

            $tmnt_sports_list_cache_key = "dfs_tournament_sports_list";
            $this->delete_cache_data($tmnt_sports_list_cache_key);

            $fixture = $this->Tournament_model->get_all_table_data("*",TOURNAMENT_SEASON,array("tournament_id"=>$tournament_id,"season_scheduled_date >= "=>$current_date,"contest_id != "=>"0"));
            foreach($fixture as $row){
                $this->delete_cache_data("fixture_trnt_".$row['cm_id']);
            }

            $this->api_response_arry['message'] = "Tournament has been cancelled successfully.";
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
    * Function used for get fixture tournament for contest
    * @param int $collection_master_id
    * @return array
    */
    public function get_fixture_tournament_post()
    {
        $this->form_validation->set_rules('collection_master_id', 'Collection master id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $cm_id = $post_data['collection_master_id'];
        $result = $this->Tournament_model->get_fixture_tournament($cm_id);
        //echo "<pre>";print_r($result);die;
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
    * Function used for mark pin tournament
    * @param array $post_data
    * @return array
    */
    public function save_contest_tournament_post(){
        $this->form_validation->set_rules('collection_master_id', 'collection master id', 'trim|required');
        $this->form_validation->set_rules('contest_id', 'contest id', 'trim|required');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $tournament_ids = isset($post_data['tournament_ids']) ? $post_data['tournament_ids'] : array();
        if(empty($tournament_ids) || count($tournament_ids) < 1) { 
            $this->api_response_arry['message'] = 'Please select at least one tournament.';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $tournament_ids = $post_data['tournament_ids'];
        $cm_id = $post_data['collection_master_id'];
        $contest_id = $post_data['contest_id'];
        $current_date = format_date();
        $record_info = $this->Tournament_model->get_all_table_data("*",TOURNAMENT_SEASON,array("tournament_id IN(".implode(",",$tournament_ids).")"=>NULL,"cm_id"=>$cm_id,"contest_id"=>"0"));
        if(count($record_info) != count($tournament_ids)){
            $this->api_response_arry['message'] = "Contest already assigned to selected tournament.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        //echo "<pre>";print_r($record_info);die;
        $result = $this->Tournament_model->update(TOURNAMENT_SEASON,array("contest_id"=>$contest_id),array("tournament_id IN(".implode(",",$tournament_ids).")"=>NULL,"cm_id"=>$cm_id,"contest_id"=>"0"));
        if($result)
        {
            //remove fixture tournament cache
            $this->delete_cache_data("fixture_trnt_".$cm_id);

            foreach($tournament_ids as $t_id){
                $this->delete_cache_data("trnt_fixture_".$t_id);
            }

            $tournament_list = $this->Tournament_model->get_fixture_tournament($cm_id);
            $this->api_response_arry['data'] = $tournament_list;
            $this->api_response_arry['message'] = "Tournament saved for selected contest.";
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('no_change');
            $this->api_response();
        }
    }
}