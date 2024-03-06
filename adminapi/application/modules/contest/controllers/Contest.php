<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contest extends MYREST_Controller {

    public function __construct() {
        parent::__construct();
        $_POST = $this->input->post();
        $this->load->model('Contest_model');
        $this->admin_lang = $this->lang->line('Contest');
        $this->admin_roles_manage($this->admin_id,'dfs');
		
    }

    /**
    * Function used for create contest from template
    * @param array $post_data
    * @return array
    */
    public function create_template_contest_post() {
        $this->form_validation->set_rules('collection_master_id', 'collection master id', 'trim|required');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $cm_id = $post_data['collection_master_id'];
        if(!isset($post_data['selected_templates']) || empty($post_data['selected_templates'])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Please select atleast one template.";
            $this->api_response();
        }

        $this->load->model(array('contest/Contest_template_model','season/Season_model'));
        $fixture_detail = $this->Season_model->get_fixture_detail($cm_id);
        if(empty($fixture_detail)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid collection id. please provide valid id.";
            $this->api_response();
        }else if(strtotime($fixture_detail['season_scheduled_date']) <= strtotime($current_date)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_start_contest_error');
            $this->api_response();
        }
        
        $post_data['sports_id'] = $fixture_detail['sports_id'];
        $post_data['template_ids'] = $post_data['selected_templates'];
        $template_list = $this->Contest_template_model->get_template_list_for_create_contest($post_data);
        if(empty($template_list)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Template details not found or contest already created.";
            $this->api_response();
        }
        $sports_id = $post_data['sports_id'];
        $allow_2nd_inning = isset($this->app_config['allow_2nd_inning']) ? $this->app_config['allow_2nd_inning']['key_value'] : 0;
        $fixture_detail['format'] = 0;
        if($allow_2nd_inning == 1 && $fixture_detail['season_game_count'] == 1){
            $season_info = $this->Season_model->get_fantasy_single_row("season_id,format",SEASON,array("season_id"=>$fixture_detail['season_ids']));
            if(!empty($season_info) && isset($season_info['format'])){
                $fixture_detail['format'] = $season_info['format'];
            }
        }
        $template_contest = array();
        $cm_2nd_inning_date = "";
        foreach($template_list as $game_data) {
            $game_data['contest_unique_id'] = random_string('alnum', 9);
            $game_data['league_id'] = $fixture_detail['league_id'];
            $game_data['contest_name'] = $game_data['template_name'];
            $game_data['contest_title'] = isset($game_data['template_title']) ? $game_data['template_title'] : "";
            $game_data['collection_master_id'] = $cm_id;
            $game_data['season_scheduled_date'] = $fixture_detail['season_scheduled_date'];
            $game_data['status'] = 0;
            $game_data['added_date'] = $current_date;
            $game_data['modified_date'] = $current_date;
            if($allow_2nd_inning == 1 && $game_data['is_2nd_inning'] == '1' && in_array($fixture_detail['format'],array(1,3)) )
            {
                $game_data['is_2nd_inning'] = 1;
                if($fixture_detail['2nd_inning_date'] != ""){
                    $secong_inning_date = $fixture_detail['2nd_inning_date'];
                }else{
                    $second_inning_interval = second_inning_game_interval($fixture_detail['format']);
                    $secong_inning_date = date("Y-m-d H:i:s",strtotime($fixture_detail['season_scheduled_date'].' +'.$second_inning_interval.' minutes'));
                    $cm_2nd_inning_date = $secong_inning_date;
                }
                $game_data['season_scheduled_date'] = $secong_inning_date;
            }else{
                $game_data['is_2nd_inning'] = 0;
            }

            $payout_data = json_decode($game_data['prize_distibution_detail'],TRUE);
            $max_prize_pool = 0;
            //change values for percentage case
            if(isset($game_data['max_prize_pool']) && $game_data['max_prize_pool'] > 0){
                $max_prize_pool = $game_data['max_prize_pool'];
            }else{
                foreach($payout_data as $key=>$prize){
                    $amount = $prize['amount'];
                    if(isset($prize['prize_type']) && $prize['prize_type'] == 1){
                        if(isset($prize['max_value'])){
                            $mx_amount = $prize['max_value'];
                        }else{
                            $mx_amount = $amount;
                        }
                        $max_prize_pool = $max_prize_pool + $mx_amount;
                    }
                }
            }
            $game_data['max_prize_pool'] = $max_prize_pool;
            $game_data['base_prize_details'] = NULL;
            if($game_data['is_auto_recurring'] == 1) {
                $game_data['base_prize_details'] = json_encode(array("prize_pool" => $game_data['prize_pool'], "prize_distibution_detail" => $game_data['prize_distibution_detail']));
            }

            $tmp_game_data = $game_data;
            $tmp_game_data['total_user_joined'] = $tmp_game_data['minimum_size'];
            $current_prize = reset_contest_prize_data($tmp_game_data);
            $game_data['current_prize'] = json_encode($current_prize);
            unset($game_data['template_name']);
            unset($game_data['template_title']);
            unset($game_data['template_description']);
            unset($game_data['auto_published']);
            $template_contest[] = $game_data;
        }
        //echo "<pre>";print_r($template_contest);die;
        if(!empty($template_contest)){
            $result = $this->Contest_template_model->save_template_contest($template_contest);
            if($result){
                if($cm_2nd_inning_date != ""){
                    $this->Contest_model->update(COLLECTION_MASTER,array("2nd_inning_date"=>$cm_2nd_inning_date),array("collection_master_id"=>$cm_id));
                }
                //delete lobby upcoming section file
                $input_arr = array();
                $input_arr['lang_file'] = '0';
                $input_arr['ignore_cache'] = '1';
                $input_arr['file_name'] = 'lobby_fixture_list_';
                $input_arr['sports_ids'] = array($sports_id);
                $this->delete_cache_and_bucket_cache($input_arr);

                $this->api_response_arry['message'] = "Contest created for selected template.";
                $this->api_response();
            }else {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('something_went_wrong');
                $this->api_response();
            }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('something_went_wrong');
            $this->api_response();
        }
    }

    /**
    * Function used for create h2h contest from template
    * @param array $post_data
    * @return array
    */
    public function save_fixture_h2h_template_post(){
        $this->form_validation->set_rules('collection_master_id','collection master id','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $h2h_challenge = isset($this->app_config['h2h_challenge']) ? $this->app_config['h2h_challenge']['key_value'] : 0;
        if($h2h_challenge != "1"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Sorry this module disabled by admin.";
            $this->api_response();
        }
        $post_data = $this->input->post();
        $current_date = format_date();
        $cm_id = $post_data['collection_master_id'];
        if(!isset($post_data['selected_templates']) || empty($post_data['selected_templates'])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Please select atleast one template.";
            $this->api_response();
        }

        $this->load->model(array('contest/Contest_template_model','season/Season_model'));
        $fixture_detail = $this->Season_model->get_fixture_detail($cm_id);
        if(empty($fixture_detail)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid collection id. please provide valid id.";
            $this->api_response();
        }else if(strtotime($fixture_detail['season_scheduled_date']) <= strtotime($current_date)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_start_contest_error');
            $this->api_response();
        }

        $post_data['sports_id'] = $fixture_detail['sports_id'];
        $post_data['template_ids'] = $post_data['selected_templates'];
        $template_list = $this->Contest_template_model->get_fixture_h2h_template_for_create_contest($post_data);
        if(empty($template_list)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Template details not found or contest already created.";
            $this->api_response();
        }

        $template_contest = array();
        foreach($template_list as $row){
            $tmp_arr = array();
            $tmp_arr['collection_master_id'] = $cm_id;
            $tmp_arr['contest_template_id'] = $row['contest_template_id'];
            $template_contest[] = $tmp_arr;
        }

        //echo "<pre>";print_r($template_contest);die;
        if(!empty($template_contest)){
            $h2h = array();
            if($fixture_detail['is_h2h'] == 0){
                $h2h['collection_master_id'] = $cm_id;
            }
            $result = $this->Contest_template_model->save_fixture_h2h_template($template_contest,$h2h);
            if($result){
                //delete lobby upcoming section file
                $input_arr = array();
                $input_arr['lang_file'] = '0';
                $input_arr['ignore_cache'] = '1';
                $input_arr['file_name'] = 'lobby_fixture_list_';
                $input_arr['sports_ids'] = array($post_data['sports_id']);
                $this->delete_cache_and_bucket_cache($input_arr);
                
                $this->delete_cache_data("fixture_h2h_".$cm_id);

                $this->api_response_arry['message'] = $this->lang->line('match_h2h_template_save_success');
                $this->api_response();
            }else {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('something_went_wrong');
                $this->api_response();
            }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('something_went_wrong');
            $this->api_response();
        }
    }

    public function get_fixture_contest_post() {  
        $post_data = $this->input->post();
        $this->form_validation->set_rules('collection_master_id', 'collection master id', 'trim|required');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $cm_id = $post_data['collection_master_id'];
        $this->load->model(array('contest/Contest_template_model','season/Season_model'));
        $fixture_detail = $this->Season_model->get_fixture_detail($cm_id);
        if(empty($fixture_detail)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid collection id. please provide valid id.";
            $this->api_response();
        }

        $a_rookie = isset($this->app_config['allow_rookie_contest']) ? $this->app_config['allow_rookie_contest']['key_value'] : 0;
        $rookie_group_id = 0;
        if($a_rookie == 1)
        {
            $rookie_data = $this->app_config['allow_rookie_contest']['custom_data'];
            if(isset($rookie_data['group_id'])){
                $rookie_group_id = $rookie_data['group_id'];
            }
        }
        $h2h_challenge = isset($this->app_config['h2h_challenge']) ? $this->app_config['h2h_challenge']['key_value'] : 0;
        $h2h_group_id = 0;
        if($h2h_challenge == 1){
            $h2h_data = $this->app_config['h2h_challenge']['custom_data'];
            if(isset($h2h_data['group_id'])){
                $h2h_group_id = $h2h_data['group_id'];
            }
        }

        
        $system_user_limit = (isset($this->app_config['pl_allow']) && isset($this->app_config['pl_allow']['custom_data']['match_limit'])) ? $this->app_config['pl_allow']['custom_data']['match_limit'] : 0;
        $stats = array("total"=>"0","open"=>"0","cancel"=>"0","close"=>"0","completed"=>"0","total_entries"=>"0","paid_entries"=>"0","free_entries"=>"0","system_user_entries"=>"0","system_user_limit"=>$system_user_limit,"system_user_deadline"=>SYSTEM_USER_CONTEST_DEADLINE,"game_type"=>"dfs");
        if($fixture_detail['season_game_count'] > 1){
            $stats['game_type'] = "multigame";
            $stats['system_user_limit'] = 0;
            $stats['system_user_deadline'] = 0;
        }
        $result = $this->Contest_model->get_fixture_contest($post_data);
        //echo "<pre>";print_r($result);die;
        $contest_list = array();
        if(!empty($result)){
            $ct_status = array_count_values(array_column($result,"status"));
            $stats['total'] = count($result);
            $stats['open'] = isset($ct_status['0']) ? $ct_status['0'] : 0;
            $stats['cancel'] = isset($ct_status['1']) ? $ct_status['1'] : 0;
            $stats['close'] = isset($ct_status['2']) ? $ct_status['2'] : 0;
            $stats['completed'] = isset($ct_status['3']) ? $ct_status['3'] : 0;
            $stats['total_entries'] = array_sum(array_column($result,"total_user_joined"));
            $stats['system_user_entries'] = array_sum(array_column($result,"total_system_user"));
            $bench_player = isset($this->app_config['bench_player']) ? $this->app_config['bench_player']['key_value'] : 0;
            $stats['bench'] = array();
            if($bench_player == "1" && $cm_id != ""){
                $stats['bench'] = $this->Contest_model->get_fixture_bench_stats($cm_id);
                if(!empty($stats['bench'])){
                    $used = array_unique(explode(",",$stats['bench']['bench_used']));
                    $stats['bench']['bench_used'] = count(array_diff($used,array("0")));
                }
            }

            foreach($result as $contest) {
                if (!isset($contest_list[$contest['group_id']]) || empty($contest_list[$contest['group_id']])) {
                    $contest_list[$contest['group_id']] = array("group_id" => $contest['group_id'], "group_name" => $contest['group_name'], "contest_list" => array());
                }
                $contest['is_rookie'] = 0;
                if($rookie_group_id == $contest['group_id']){
                    $contest['is_rookie'] = 1;
                }
                $contest['is_h2h'] = 0;
                if($h2h_group_id == $contest['group_id']){
                    $contest['is_h2h'] = 1;
                }
                $contest['prize_distibution_detail'] = json_decode($contest['prize_distibution_detail']);
                $contest_list[$contest['group_id']]['contest_list'][] = $contest;
                if($contest['entry_fee'] > 0){
                    $stats['paid_entries'] = $stats['paid_entries'] + $contest['total_user_joined'];
                }else{
                    $stats['free_entries'] = $stats['free_entries'] + $contest['total_user_joined'];
                }
            }
        }

        $contest_list = array_values($contest_list);
        $this->api_response_arry['data'] = array("stats"=>$stats,"contest_list"=>$contest_list);
        $this->api_response();
    }

    public function cancel_contest_post() {
        $this->form_validation->set_rules('contest_unique_id', 'contest unique id', 'trim|required');
        $this->form_validation->set_rules('cancel_reason', 'reason', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        
        $this->load->helper('queue_helper');
        $post_data = $this->post();
        $post_data['action'] = 'cancel_game';
        add_data_in_queue($post_data, 'game_cancel');        
        $this->api_response_arry['message'] = $this->admin_lang['successfully_cancel_contest'];
        $this->api_response();
    }

    public function cancel_collection_post() {
        $this->form_validation->set_rules('collection_master_id', 'collection id', 'trim|required');
        $this->form_validation->set_rules('cancel_reason', 'reason', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $this->load->helper('queue_helper');
        $post_data = $this->post();
        $post_data['action'] = 'cancel_collection';
        add_data_in_queue($post_data, 'game_cancel');

        $this->api_response_arry['message'] = $this->admin_lang['successfully_cancel_collection'];
        $this->api_response();
    }

    public function delete_contest_post()
    {
        $this->form_validation->set_rules('contest_id', 'Contest Id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $result = $this->Contest_model->delete_contest($post_data);
        if($result)
        {   
            $this->api_response_arry['message'] = "Contest deleted successfully.";
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Something went wrong while delete contest or users joined this contest.";
            $this->api_response();
        }
    }

    public function mark_pin_contest_post()
    {
        $this->form_validation->set_rules('collection_master_id', 'collection master Id', 'trim|required');
        $this->form_validation->set_rules('contest_id', 'Contest Id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $result = $this->Contest_model->mark_pin_contest($post_data);
        if($result){
            $this->api_response_arry['message'] = "Contest mark pin successfully.";
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('no_change');
            $this->api_response();
        }
    }

    public function create_contest_post() {
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('group_id', $this->lang->line('group'), 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('contest_name', $this->lang->line('game_name'), 'trim|required|max_length[50]');
        $this->form_validation->set_rules('minimum_size', 'minimum size', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('size', 'size', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('multiple_lineup', 'multiple lineup', 'trim|required');
        $this->form_validation->set_rules('entry_fee', $this->lang->line('entry_fee'), 'trim|required');
        $this->form_validation->set_rules('site_rake', $this->lang->line('site_rake'), 'trim|required');
        $this->form_validation->set_rules('max_bonus_allowed', $this->lang->line('max_bonus_allowed'), 'trim|required');
        $this->form_validation->set_rules('prize_type', $this->lang->line('prize_type'), 'trim|required');
        $this->form_validation->set_rules('prize_pool', $this->lang->line('prize_pool'), 'trim|required');
        $this->form_validation->set_rules('entry_fee_type', 'entry fee type', 'trim|required');
        $this->form_validation->set_rules('is_scratchwin', $this->lang->line('scratchwin'), 'trim');
        $this->form_validation->set_rules('is_2nd_inning', $this->lang->line('2nd_inning'), 'trim');
        $game_data = $this->input->post();
        if(isset($game_data['set_sponsor']) && $game_data['set_sponsor'] == 1)
        {
            $this->form_validation->set_rules('sponsor_name', $this->lang->line("sponsor_name"),'trim|max_length[60]');
            $this->form_validation->set_rules('sponsor_logo', $this->lang->line("sponsor_logo"),'trim|required|max_length[50]');
            $this->form_validation->set_rules('sponsor_contest_dtl_image', $this->lang->line("sponsor_contest_dtl_image"),'trim|required|max_length[50]');
            $this->form_validation->set_rules('sponsor_link', $this->lang->line("sponsor_link"),'trim|max_length[255]');
        }
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $current_date = format_date();
        $cm_id = $game_data['collection_master_id'];
        $is_2nd_inning = isset($game_data['is_2nd_inning']) ? $game_data['is_2nd_inning'] : 0;

        $this->load->model('season/Season_model');
        $fixture_detail = $this->Season_model->get_fixture_detail($cm_id);
        //echo "<pre>";print_r($fixture_detail);die;
        if(empty($fixture_detail)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid collection id. please provide valid id.";
            $this->api_response();
        }else if(strtotime($fixture_detail['season_scheduled_date']) <= strtotime($current_date)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_start_contest_error');
            $this->api_response();
        }
        $game_data['sports_id'] = $fixture_detail['sports_id'];
        $game_data['league_id'] = $fixture_detail['league_id'];
        $game_data['is_2nd_inning'] = 0;
        $game_data['season_scheduled_date'] = $fixture_detail['season_scheduled_date'];
        $allow_2nd_inning = isset($this->app_config['allow_2nd_inning']) ? $this->app_config['allow_2nd_inning']['key_value'] : 0;

        if($allow_2nd_inning == 1 && $is_2nd_inning == 1 && $fixture_detail['2nd_inning_date'] != "")
        {
            $game_data['is_2nd_inning'] = 1;
            $game_data['season_scheduled_date'] = $fixture_detail['2nd_inning_date'];
        }

        $multiple_lineup = $game_data['multiple_lineup'];
        $prize_pool_type = $game_data['prize_pool_type'];
        $prize_pool = 0;
        $guaranteed_prize = '0';
        if($game_data['prize_pool_type'] == '1') {
            $guaranteed_prize = '1';
        }else if ($game_data['prize_pool_type'] == '2') {
            $guaranteed_prize = '2';
            $game_data['site_rake'] = 0;
        }

        if ($multiple_lineup && $multiple_lineup > $game_data['size']) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('invalid_game_multiple_lineup_size');
            $this->api_response();
        }

        $prize_details_inputs['entry_fee'] = $game_data['entry_fee'];
        $prize_details_inputs['site_rake'] = $game_data['site_rake'];
        if ($prize_pool_type == '0') {
            $game_data['is_custom_prize_pool'] = 0;
        } else if ($prize_pool_type == '1' || $prize_pool_type == '2') {
            $game_data['is_custom_prize_pool'] = 1;
            $game_data['master_contest_type_id'] = 0;
        }

        $prize_details_inputs['size'] = $game_data['minimum_size'];
        $prize_details_inputs['league_number_of_winner_id'] = $game_data['master_contest_type_id'];
        $payout_data = isset($game_data['payout_data']) ? $game_data['payout_data'] : array();
        $max_winners = array_column($payout_data, "max");
        $max_winners = max($max_winners);
        if($max_winners > $game_data['size']){
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "You can't define winner more then size.";
            $this->api_response();
        }
            
        if($game_data['prize_pool_type'] == '1' || $game_data['prize_pool_type'] == '2')
        {
            $prize_pool = $game_data['prize_pool'];
            $payout_data = isset($game_data['payout_data']) ? $game_data['payout_data'] : array();
        }
        $merchandise_ids = array_column($payout_data, "prize_type");
        if(in_array("3", $merchandise_ids)){
            if($game_data['is_tie_breaker'] != 1){
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('invalid_tie_breaker_status');
                $this->api_response();
            }
        }

        //change values for percentage case
        $max_prize_pool = 0;
        foreach($payout_data as $key=>$prize){
            $amount = $prize['amount'];
            if(isset($game_data['prize_value_type']) && $game_data['prize_value_type'] == 1 && $prize['prize_type'] != "3"){
                $payout_data[$key]['per'] = $prize['amount'];
                $person_count = ($prize['max'] - $prize['min']) + 1;
                $per_person = ((($prize_pool * $prize['amount']) / 100) / $person_count);
                $amount = $per_person;
                $payout_data[$key]["amount"] = number_format($per_person,"2",".","");
            }
            if(isset($prize['prize_type']) && $prize['prize_type'] == 1){
                if(isset($prize['max_value'])){
                    $mx_amount = $prize['max_value'];
                }else{
                    $mx_amount = $amount;
                }
                $max_prize_pool = $max_prize_pool + $mx_amount;
            }
        }
        $payout_data = json_encode($payout_data);
        $salary_cap = SALARY_CAP;
        $contest_unique_id = random_string('alnum', 9);
        $contest_data = array(
                "contest_unique_id"         => $contest_unique_id,
                "sports_id"                 => $game_data['sports_id'],
                "league_id"                 => $game_data['league_id'],
                "group_id"                  => $game_data['group_id'],
                "collection_master_id"      => $cm_id,
                "contest_name"              => $game_data['contest_name'],
                "contest_title"             => isset($game_data['contest_title']) ? $game_data['contest_title'] : "",
                "minimum_size"              => $game_data['minimum_size'],
                "size"                      => $game_data['size'],
                "multiple_lineup"           => $multiple_lineup,
                "entry_fee"                 => $game_data['entry_fee'],
                "site_rake"                 => $game_data['site_rake'],
                "max_bonus_allowed"         => $game_data['max_bonus_allowed'],
                "currency_type"             => $game_data['entry_fee_type'],
                "prize_type"                => $game_data['prize_type'],
                "prize_pool"                => $prize_pool,
                "max_prize_pool"            => $max_prize_pool,
                "prize_distibution_detail"  => $payout_data,
                "season_scheduled_date"     => $game_data['season_scheduled_date'],
                "master_contest_type_id"    => $game_data['master_contest_type_id'],
                "guaranteed_prize"          => $guaranteed_prize,
                "is_custom_prize_pool"      => $game_data['is_custom_prize_pool'],
                "is_auto_recurring"         => isset($game_data['is_auto_recurring']) && $game_data['is_auto_recurring'] ? 1 : 0,
                "is_pin_contest"            => isset($game_data['is_pin_contest']) && $game_data['is_pin_contest'] ? 1 : 0,
                "is_tie_breaker"            => $game_data['is_tie_breaker'],
                "prize_value_type"          => isset($game_data['prize_value_type']) ? $game_data['prize_value_type'] : 0,
                "salary_cap"                => $salary_cap,
                "status"                    => 0,
                "added_date"                => format_date(),
                "is_scratchwin"             => isset($game_data['is_scratchwin']) && $game_data['is_scratchwin']=='1' ? 1 : 0,
                "is_2nd_inning"             => $game_data['is_2nd_inning']
            );

        $tmp_game_data = $contest_data;
        $tmp_game_data['total_user_joined'] = $tmp_game_data['minimum_size'];
        $current_prize = reset_contest_prize_data($tmp_game_data);
        $contest_data['current_prize'] = json_encode($current_prize);
        //add sponsor if checked 
        if(isset($game_data['set_sponsor']) && $game_data['set_sponsor'] == 1)
        {
            $contest_data['sponsor_name'] = $game_data['sponsor_name'];
            $contest_data['sponsor_logo'] = $game_data['sponsor_logo'];
            $contest_data['sponsor_contest_dtl_image'] = $game_data['sponsor_contest_dtl_image'];
            $contest_data['sponsor_link'] = (isset($game_data['sponsor_link']) && $game_data['sponsor_link'] != '') ? $game_data['sponsor_link'] : NULL;
        }
        if ($contest_data['is_auto_recurring'] == 1)
        {
            $contest_data['base_prize_details'] = json_encode(array("prize_pool" => $prize_pool, "prize_distibution_detail" => $payout_data));
        }

        if($contest_data['entry_fee'] == 0 && $prize_pool > 0 && $contest_data['is_auto_recurring'] == 1) 
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('auto_recurrent_create_error');
            $this->api_response();
        }
        
        //echo "<pre>";print_r($contest_data);die;
        $contest_id = $this->Contest_model->save_contest($contest_data);
        if($contest_id){
            //delete lobby upcoming section file
            $input_arr = array();
            $input_arr['lang_file'] = '0';
            $input_arr['ignore_cache'] = '1';
            $input_arr['file_name'] = 'lobby_fixture_list_';
            $input_arr['sports_ids'] = array($contest_data['sports_id']);
            $this->delete_cache_and_bucket_cache($input_arr);
            
            $this->api_response_arry['data'] = $contest_id;
            $this->api_response_arry['message'] = "Contest added successfully";
            $this->api_response();
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('something_went_wrong');
            $this->api_response();
        }
    }

    public function get_contest_detail_post()
    {
        $this->form_validation->set_rules('contest_unique_id', "Contest id", 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $contest_detail = $this->Contest_model->get_contest_detail($post_data['contest_unique_id']);
        if(empty($contest_detail)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('contest_detail_not_found');
            $this->api_response();
        }

        $this->load->model('season/Season_model');
        $season_ids = array_unique(explode(",",$contest_detail['season_ids']));
        if($contest_detail['is_tour_game'] == 1 && $contest_detail['sports_id'] == TENNIS_SPORTS_ID){
            $match_list = $this->Season_model->get_tennis_season_detail($season_ids);
        }else if($contest_detail['is_tour_game'] == 1){
            $match_list = $this->Season_model->get_tour_season_detail($season_ids);
        }else{
            $match_list = $this->Season_model->get_fixture_season_detail($season_ids,"status");
        }
        $contest_detail['match_list'] = $match_list;
        $contest_detail['prize_distibution_detail'] = json_decode($contest_detail['prize_distibution_detail'],TRUE);
        $contest_detail['league'] = $this->Season_model->get_league_detail_by_id($contest_detail['league_id']);
        $contest_detail['creator'] = array();
        if($contest_detail['user_id'])
        {
            $this->load->model('user/User_model');
            $user_info = $this->User_model->get_single_row("user_name",USER,array("user_id"=>$contest_detail['user_id']));
            if(!empty($user_info)){
                $contest_detail['creator']['user_name'] = $user_info['user_name'];
            }
        }
        $contest_detail['merchandise_list'] = array();
        if($contest_detail['is_tie_breaker'] == 1){
            $tmp_ids = array();
            foreach($contest_detail['prize_distibution_detail'] as $prize){
                if(isset($prize['prize_type']) && $prize['prize_type'] == 3){
                    $tmp_ids[] = $prize['amount'];
                }
            }
            if(!empty($tmp_ids)){
                $this->load->model(array('merchandise/Merchandise_model'));
                $contest_detail['merchandise_list'] = $this->Merchandise_model->get_merchandise_list($tmp_ids);
            }
        }

        $this->api_response_arry['data'] = $contest_detail;
        $this->api_response();
    }

    public function get_contest_users_post()
    {
        $this->form_validation->set_rules('contest_id', "Contest id", 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $result = $this->Contest_model->get_contest_users($post_data);
        if(isset($result['result']) && !empty($result['result'])){
            $this->load->model('user/User_model');
            $user_ids = array_unique(array_column($result['result'], "user_id"));
            $user_data = $this->User_model->get_users_by_ids($user_ids);
            $user_data = array_column($user_data,NULL,"user_id");
            foreach($result['result'] as &$row){
                $row['user_unique_id'] = isset($user_data[$row['user_id']]) ? $user_data[$row['user_id']]['user_unique_id'] : "";
            }
        }
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
    * Function used for get user team players list
    * @param array $post_data
    * @return array
    */
    public function get_user_contest_team_post()
    {
        $this->form_validation->set_rules('lineup_master_contest_id', 'lineup master contest id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $lineup_master_contest_id = $post_data['lineup_master_contest_id'];
        $team_info = $this->Contest_model->get_user_contest_team_detail($lineup_master_contest_id);
        if(empty($team_info)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "No details found.";
            $this->api_response();
        }
        $team_info['team_data'] = json_decode($team_info['team_data'],TRUE);
        $team_info = $this->Contest_model->get_user_team_with_score($team_info);
        //echo "<pre>";print_r($team_info);die;

        //get season id
        $players_list = $this->Contest_model->get_fixture_rosters($team_info['collection_master_id'],1);
        $player_list_array = array();
        if(!empty($players_list)){
            $player_list_array = array_column($players_list, NULL, 'player_team_id');
        }

        $bench_player = isset($this->app_config['bench_player']) ? $this->app_config['bench_player']['key_value'] : 0;
        $bench_players = array();
        $bench_pl_ids = array();
        if($bench_player == "1"){
            $pl_list = $this->Contest_model->get_team_bench_players($team_info['lineup_master_id']);
            $bench_pl_ids = array_column($pl_list,"player_id");
            foreach($pl_list as $row){
                $player_id = $row['player_id'];
                if($row['status'] == "1" && $row['out_player_id'] != "0"){
                    $player_id = $row['out_player_id'];
                }
                $player_info = $player_list_array[$player_id];
                if(!empty($player_info)){
                    $tmp_arr = array();
                    $tmp_arr['priority'] = $row['priority'];
                    $tmp_arr['status'] = $row['status'];
                    $tmp_arr['reason'] = $row['reason'];
                    $tmp_arr['player_in_id'] = ($row['status'] == "1") ? $row['player_id'] : "0";
                    $tmp_arr['player_id'] = $player_info['player_id'];
                    $tmp_arr['player_team_id'] = $player_info['player_team_id'];
                    $tmp_arr['full_name'] = $player_info['full_name'];
                    $tmp_arr['display_name'] = $player_info['display_name'];
                    $tmp_arr['jersey'] = $player_info['jersey'];
                    $tmp_arr['team_abbr'] = $player_info['team_abbr'];
                    $tmp_arr['position'] = $player_info['position'];
                    $tmp_arr['player_uid'] = $player_info['player_uid'];
                    $tmp_arr['salary'] = $player_info['salary'];
                    $bench_players[] = $tmp_arr;
                }
            }
        }

        $final_player_list = array();
        $playing_announce = 0;
        $team_data = $team_info['team_data'];
        if(!empty($team_data['pl'])){
            foreach($team_data['pl'] as $player_team_id=>$score) {
                if(isset($player_list_array[$player_team_id])){
                    $player_info = $player_list_array[$player_team_id];
                    if(isset($player_info['playing_announce']) && $player_info['playing_announce'] == "1"){
                        $playing_announce = $player_info['playing_announce'];
                    }
                    $captain = 0;
                    if($player_team_id == $team_data['c_id']){
                        $captain = 1;
                    }else if($player_team_id == $team_data['vc_id']){
                        $captain = 2;
                    }
                    $sub_in = 0;
                    if(!empty($bench_pl_ids) && in_array($player_team_id,$bench_pl_ids)){
                        $sub_in = 1;
                    }
                    $lineup = array();
                    $lineup['player_id'] = $player_info['player_id'];
                    $lineup['player_team_id'] = $player_info['player_team_id'];
                    $lineup['full_name'] = $player_info['full_name'];
                    $lineup['display_name'] = $player_info['display_name'];
                    $lineup['jersey'] = $player_info['jersey'];
                    $lineup['team_abbr'] = $player_info['team_abbr'];
                    $lineup['position'] = $player_info['position'];
                    $lineup['player_uid'] = $player_info['player_uid'];
                    $lineup['salary'] = $player_info['salary'];
                    $lineup['captain'] = $captain;
                    $lineup['is_playing'] = $player_info['is_playing'];
                    $lineup['is_sub'] = $player_info['is_sub'];
                    $lineup['playing_announce'] = $player_info['playing_announce'];
                    $lineup['lmp'] = $player_info['lmp'];
                    $lineup['score'] = $score;
                    $lineup['sub_in'] = $sub_in;
                    $final_player_list[] = $lineup;
                }
            }
        }

        $booster = array();
        if(isset($team_info["booster_id"]) && $team_info["booster_id"] != "0"){
            $this->load->model("booster/Booster_model");
            $booster = $this->Booster_model->get_match_booster_detail($team_info["booster_id"],$team_info['collection_master_id']);
            if(!empty($booster)){
                unset($booster['booster_id']);
                unset($booster['points']);
                $booster['score'] = $contest_info["booster_points"];
            }
        }

        $team_info['bench'] = $bench_players;
        $team_info['booster'] = $booster;
        $team_info['playing_announce'] = $playing_announce;
        $team_info['lineup'] = $final_player_list;
        unset($team_info['team_data']);
        $this->api_response_arry['data'] = $team_info;
        $this->api_response();      
    }

    public function get_contest_filter_post(){
        $this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $this->load->model('common/Common_model');
        $league_list = $this->Contest_model->get_fixture_league_list($sports_id);
        $group_list = $this->Common_model->get_all_group_list($post_data);
        
        $status_list = array();
        $status_list[] = array("label"=>"Select Status","value"=>"");
        $status_list[] = array("label"=>"Current Contest","value"=>"current_game");
        $status_list[] = array("label"=>"Completed Contest","value"=>"completed_game");
        $status_list[] = array("label"=>"Cancelled Contest","value"=>"cancelled_game");
        $status_list[] = array("label"=>"Upcoming Contest","value"=>"upcoming_game");

        //contest type array
        $contest_type_list = array();
        $contest_type_list[] = array("label"=> "All","value"=>"");
        $contest_type_list[] = array("label"=> $this->lang->line('classic'),"value" => 0);
        $allow_2nd_inning = isset($this->app_config['allow_2nd_inning']) ? $this->app_config['allow_2nd_inning']['key_value'] : 0;
        if($allow_2nd_inning == 1)
        {
            $contest_type_list[] = array("label"=> $this->lang->line('2nd_inning'),"value"=>2);
        }

        $result = array();
        $result['league_list'] = $league_list;
        $result['group_list'] = $group_list;
        $result['status_list'] = $status_list;
        $result['contest_type_list'] = $contest_type_list;

        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    public function get_league_fixture_post(){
        $this->form_validation->set_rules('league_id', 'league id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $fixture_list = $this->Contest_model->get_league_fixture_list($post_data['league_id']);
        $this->api_response_arry['data'] = $fixture_list;
        $this->api_response();
    }

    public function get_contest_list_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $data = $this->Contest_model->get_contest_list($post_data);
        //echo "<pre>";print_r($data);die;
        $data['match_list'] = array();
        if(isset($data['result']) && !empty($data['result'])){
            $this->load->model('season/Season_model');
            $season_ids = array_unique(array_column($data['result'], "season_id"));
            $data['match_list'] =  $this->Season_model->get_fixture_season_detail($season_ids);
        }
        $this->api_response_arry['data']= $data;
        $this->api_response();
    }

	public function do_upload_post(){
		$file_field_name	= $this->post('name');
		$dir				= ROOT_PATH.UPLOAD_DIR;
		$subdir				= ROOT_PATH.FEATURE_CONTEST_DIR;
		$temp_file			= $_FILES['file']['tmp_name'];
		$ext				= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		$vals = 			@getimagesize($temp_file);
		$width = $vals[0];
		$height = $vals[1];
		if ($height != '140' || $width != '325') {
			
			$invalid_size = str_replace("{max_height}",'140',$this->admin_lang['ad_image_invalid_size']);
			$invalid_size = str_replace("{max_width}",'325',$invalid_size);
			$this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>$invalid_size) , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
			$this->check_folder_exist($subdir);
		}

		$file_name = time().".".$ext ;
        $filePath     = FEATURE_CONTEST_DIR.$file_name;
		if( strtolower( IMAGE_SERVER ) == 'remote' )
		{
            try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $data = array( 'image_name' => $file_name ,'image_url'=> IMAGE_PATH.$filePath);
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['data'] = $data;
                    $this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('image_upload_error');
                $this->api_response();
            }
		} else {
			$config['allowed_types']	= 'jpg|png|jpeg|gif';
			$config['max_size']			= '5000';
			$config['max_width']		= '325';
			$config['max_height']		= '140';
			$config['upload_path']		= $subdir;
			$config['file_name']		= time();
			$this->load->library('upload', $config);
			if ( ! $this->upload->do_upload('file'))
			{
				$error = $this->upload->display_errors();
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = strip_tags($error);
                $this->api_response();
			}
			else
			{
				$upload_data = $this->upload->data();
                $this->api_response_arry['message'] = "";
                $this->api_response_arry['data'] = array('image_name' =>IMAGE_PATH.FEATURE_CONTEST_DIR.$file_name ,'image_url'=> $subdir);
                $this->api_response();
			}
		}		
	}

	public function do_upload_merchandise_post(){
	
		$file_field_name	= $this->post('name');
		$dir				= ROOT_PATH.UPLOAD_DIR;
		$subdir				= ROOT_PATH.MERCHANDISE_CONTEST_DIR;
		$temp_file			= $_FILES['file']['tmp_name'];
		$ext				= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		$vals = 			@getimagesize($temp_file);
		$width = $vals[0];
		$height = $vals[1];
		if ($height != '100' || $width != '100') {
			
			$invalid_size = str_replace("{max_height}",'100',$this->admin_lang['ad_image_invalid_size']);
			$invalid_size = str_replace("{max_width}",'100',$invalid_size);
			$this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>$invalid_size) , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
			$this->check_folder_exist($subdir);
		}

		$file_name = time().".".$ext ;
        $filePath     = MERCHANDISE_CONTEST_DIR.$file_name;
		if( strtolower( IMAGE_SERVER ) == 'remote' )
		{
            try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $data = array( 'image_name' => $file_name ,'image_url'=> IMAGE_PATH.$filePath);
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['data'] = $data;
                    $this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('image_upload_error');
                $this->api_response();
            }
		} else {

			$config['allowed_types']	= 'jpg|png|jpeg|gif';
			$config['max_size']			= '5000';
			$config['max_width']		= '100';
			$config['max_height']		= '100';
			$config['upload_path']		= $subdir;
			$config['file_name']		= time();

			$this->load->library('upload', $config);
			if ( ! $this->upload->do_upload('file'))
			{
				$error = $this->upload->display_errors();
				$this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>strip_tags($error)) , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
			}
			else
			{

				$upload_data = $this->upload->data();
				$this->response(
						array(
								config_item('rest_status_field_name')=>TRUE,
								'data'=>array('image_name' =>IMAGE_PATH.MERCHANDISE_CONTEST_DIR.$file_name ,'image_url'=> $subdir),
								rest_controller::HTTP_OK
							)
						);
			}
		}		
	}

    /**
     * Used for download contest winners csv file
     * @param int $contest_id
     * @return csv file
     */
    public function export_contest_winners_get()
    {
        $_POST['contest_id'] = $_GET['contest_id'];
        $post_data = $this->input->post();
        $winners_data = $this->Contest_model->export_contest_winner_data($post_data);
        if(!empty($winners_data)){
            $header = array_keys($winners_data[0]);
            $winners_data = array_merge(array($header),$winners_data);
            $this->load->helper('csv');
            array_to_csv($winners_data,'contest_winner_data-'.$_POST['contest_id'].'.csv');
        }
    }

    /**
     * Used for revert contest prize distribution
     * @param int $contest_id
     * @return array
     */
    public function revert_contest_prize_post() {
        $this->form_validation->set_rules('contest_id', 'contest id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        if($prize_cron == "1"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('revert_prize_cron_error');
            $this->api_response();
        }
        $post_data = $this->post();
        $this->load->helper('queue_helper');
        $server_name = get_server_host_name(); 
        $content = array();
        $content['url'] = $server_name."/cron/dfs/revert_contest_prize/".$post_data['contest_id'];
        add_data_in_queue($content,'prizerevert');

        $this->api_response_arry['message'] = $this->admin_lang['contest_prize_revert_success'];
        $this->api_response();
    }

    /**
     * Used for revert contest prize distribution
     * @param int $contest_id
     * @return array
     */
    public function revert_collection_prize_post() {
        $this->form_validation->set_rules('collection_master_id', 'collection id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        if($prize_cron == "1"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('revert_prize_cron_error');
            $this->api_response();
        }

        $post_data = $this->input->post();
        $this->load->helper('queue_helper');
        $server_name = get_server_host_name(); 
        $result = $this->Contest_model->get_collection_revert_prize_contest($post_data['collection_master_id']);
        foreach($result as $row){
            $content = array();
            $content['url'] = $server_name."/cron/dfs/revert_contest_prize/".$row['contest_id'];
            add_data_in_queue($content,'prizerevert');
        }

        $this->api_response_arry['message'] = $this->admin_lang['collection_prize_revert_success'];
        $this->api_response();
    }

    /**
     * Used for get sports and league wise collection list
     * @param array
     * @return array
     */
    public function get_all_collections_post()
    {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('sports_id', 'Sports ID', 'trim|required');
        $this->form_validation->set_rules('league_id', 'League ID', 'trim|required');

        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
     
        $this->load->model('contest/Contest_model');
        $post_api_response = $this->Contest_model->get_collections_by_filter($post_data);
        $this->api_response_arry['data'] = $post_api_response;
        $this->api_response();
    }

    /**
     * Used for collection wise contest list
     * @param array
     * @return array
     */
    public function get_collection_contest_post()
    {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('collection_master_id', 'collection master id', 'trim|required');
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $contest_list = $this->Contest_model->get_collection_contest_list($post_data);

        $this->api_response_arry['data']= $contest_list;
        $this->api_response();
    }

    /**
     * Used for get contest participants list
     * @param array
     * @return array
     */
    public function get_contest_participant_report_post()
    {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('contest_id', 'contest id', 'trim|required');
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $data = $this->Contest_model->get_contest_participant_report($post_data);
        
        $this->api_response_arry['data'] = $data;
        $this->api_response();
    }

    /**
     * used for download joined users team list
     * @param int $sports_id
     * @param int $contest_id
     * @return array
     */
    public function download_contest_teams_post() {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $contest_id = $post_data['contest_id'];
        if (!isset($post_data['limit'])) {
            $post_data['limit'] = 1000;
        }
        $filePath = "lineup/" . $contest_id . ".pdf";
        try{
            $data_arr = array();
            $data_arr['file_path'] = $filePath;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_upload = $upload_lib->get_file_info($data_arr);
            if(!empty($is_upload)){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                $this->api_response_arry['data'] = array('uploaded' => '1', 'file' => IMAGE_PATH . $filePath);
                $this->api_response_arry['message'] = "PDF download successfully.";
                $this->api_response();
            }
        }catch(Exception $e){
            $message = 'Caught exception: '.$e->getMessage()."\n";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $message;
            $this->api_response();
        }
    }

    /**
     * get gamestats of specific user for ploting graph 
     * @param  $user_id
     * @return string
     */
    public function get_game_stats_post() {
        $this->form_validation->set_rules('user_id', 'User id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $user_id = $post_data['user_id'];
        $result = $this->Contest_model->get_user_game_stats($post_data);
        
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    public function get_all_upcoming_collections_post()
	{

		$res   = $this->Contest_model->get_all_upcoming_collections();
       	$this->api_response_arry['data'] = $res;
		$this->api_response();
	}


    public function get_sport_leagues_post()
	{
		$post_params = array(
							'sports_id' => $this->input->post("sports_id"),
							 'admin_list_filter' => true, // get all leagues
							'active' => '1',
							);
							$_POST['admin_list_filter']=TRUE;
							$_POST['active']='1';
		$post_api_response = $this->Contest_model->get_sport_all_recent_leagues($post_params);
		$formats = array();
		if($this->input->post("sports_id") ==7 )
		$formats = array("1"=>"One Day", "2"=>"Test", "3"=>"T20");

		$post_api_response['data']['formats'] = $formats;
	
		$this->api_response_arry['data'] = $post_api_response;
		
		$this->api_response();
		
	}

    public function get_all_collections_by_league_post()
	{
        $post_data = $this->input->post();
		$game_type = 'completed_game';
		if(!empty($this->input->post("game_type"))){
			$game_type = $this->input->post("game_type");
		}
		if(!empty($this->input->post("collection_type"))){
			$collection_type = $this->input->post("collection_type");
		}
        $post_params = array('league_id' => $post_data["league_id"],'sports_id' => $post_data["sports_id"],'fetch_only_completed' => $game_type,'fetch_completed_collection' => $collection_type);

        $this->load->model('contest/Contest_model');
		$post_api_response = $this->Contest_model->get_collections_by_filter($post_params);
		$this->api_response_arry['data'] = $post_api_response;
		$this->api_response();
	}

    public function export_contest_teams_get() {
        $post_data = $_REQUEST;
        $contest = $this->Contest_model->export_contest_teams($post_data['contest_id']);
        //echo "<pre>======";print_r($contest);die;
        $teams_data[] = array("Invalid contest detail");
        $file_name = "contest_teams.csv";
        if(!empty($contest['teams'])){
            $cheader = "Player 1";
            if(isset($contest['c_vc']['c_point']) && $contest['c_vc']['c_point'] > 0){
                $cheader = "Player 1 (C)";
                if($contest['sports_id'] == MOTORSPORT_SPORTS_ID){
                    $cheader = "Player 1 (T)";
                }
            }
            $vcheader = "Player 2";
            if(isset($contest['c_vc']['vc_point']) && $contest['c_vc']['vc_point'] > 0){
                $vcheader = "Player 2 (VC)";
            }
            $total_column = 11;//total player column
            if(isset($contest['teams']['0']) && count($contest['teams']['0']) > 0){
                $pl_cnt = count($contest['teams']['0']);
                $total_column = $pl_cnt - 2;
            }
            // Column headings
            $header = array('User Name', 'Team Name', $cheader, $vcheader);
            for($i=3;$i<=$total_column;$i++){
                $header[] = 'Player '.$i;
            }

            $ct_row = array("Match : ".$contest['collection_name'],"Contest : ".$contest['contest_name'],"Schedule Date : ".$contest['season_scheduled_date']);
            $teams_data = array_merge(array($ct_row),array($header),$contest['teams']);
            $file_name = 'contest_teams-'.strtolower(str_replace(" ","_",$contest['contest_name']))."_".$contest['contest_id']."_".strtotime(format_date()).'.csv';
        }
        $this->load->helper('csv');
        array_to_csv($teams_data,$file_name);
    }
}
