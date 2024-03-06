<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Contest extends Common_Api_Controller {

	var $allow_2nd_inning=0;
    public function __construct() {
        parent::__construct();
        $_POST = $this->post();
        $this->load->model('Contest_model');
        $this->admin_lang = $this->lang->line('Contest');
        $allow_livefantasy =  isset($this->app_config['allow_livefantasy'])?$this->app_config['allow_livefantasy']['key_value']:0;
        if($allow_livefantasy == 0)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Live fantasy not enabled";
            $this->api_response_arry['global_error'] = "Module Disable";
            $this->api_response();
        }		 
    }

    public function get_fixture_contest_post() {  
        $post_data = $this->input->post();
        $this->form_validation->set_rules('league_id', 'League id', 'trim|required');
        $this->form_validation->set_rules('season_game_uid', 'Season game id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();
        $post_data['collection_master_id'] = 0;

        $data = $this->Contest_model->get_fixture_contest($post_data);
        $contest_list = array();
        $show_cancel = 0;
        $show_revert = 0;
        
        if (isset($data['result'])) {
            $contest_status = array_column($data['result'], 'status');
            if(in_array($show_cancel, $contest_status)) {
                $show_cancel = 1;
            }
            if(in_array("3", $contest_status)) {
                $show_revert = 1;
            }
            foreach ($data['result'] as $contest) {
                if (!isset($contest_list[$contest['group_id']]) || empty($contest_list[$contest['group_id']])) {
                    $contest_list[$contest['group_id']] = array("group_id" => $contest['group_id'], "group_name" => $contest['group_name'], "contest_list" => array());
                }                
                $contest['prize_distibution_detail'] = json_decode($contest['prize_distibution_detail']);
                $contest_list[$contest['group_id']]['contest_list'][] = $contest;
            }
        }
        $contest_list = array_values($contest_list);
        if (!empty($data)) {
            $this->api_response_arry['show_cancel'] = $show_cancel;
            $this->api_response_arry['show_revert'] = $show_revert;
            $this->api_response_arry['data'] = $contest_list;
            $this->api_response();
        } else {
            $this->api_response_arry['data'] = array();
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response();
        }
    }

    public function cancel_contest_post() {
        $post_data = $this->post();
        $this->form_validation->set_rules('contest_unique_id', 'contest unique id', 'trim|required');
        $this->form_validation->set_rules('cancel_reason', 'reason', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        
        $this->load->helper('queue_helper');
        $post_data['action'] = 'cancel_game';
        add_data_in_queue($post_data, 'lf_game_cancel');
        $this->api_response_arry['message'] = $this->lang->line('successfully_cancel_contest');
        $this->api_response();
    }
    

    public function cancel_collection_post() {
        $post_data = $this->post();
        $this->form_validation->set_rules('collection_id', 'collection id', 'trim|required');
        $this->form_validation->set_rules('cancel_reason', 'reason', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $this->load->helper('queue_helper');
        $post_data['action'] = 'cancel_collection';
        add_data_in_queue($post_data, 'lf_game_cancel');
        $this->api_response_arry['message'] = $this->lang->line('successfully_cancel_collection');
        $this->api_response();
    } 

    public function delete_contest_post()
    {   
        $this->form_validation->set_rules('contest_id', 'Contest Id', 'trim|required');
        $this->form_validation->set_rules('collection_id', 'collection id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $data_arr = $this->input->post();
        $contest = $this->Contest_model->delete_contest($data_arr); 
        
        if($contest)
        {   
            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response_arry['message']         = "Contest deleted successfully.";
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']         = isset($contest['message']) ? $contest['message'] : $this->lang->line('no_change');
            $this->api_response();
        }
    }

    public function get_conntest_filter_post(){ 
        $this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $this->load->model('League_model');
        
        $league_list = $this->League_model->get_sport_leagues();        
        $post   = $this->post();
        $group_list = $this->Contest_model->get_all_group_list($post);
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
       
        $result = array(
                    'league_list'       => isset($league_list['result']) ? $league_list['result'] : array(),
                    'group_list'        => $group_list,
                    'status_list'       => $status_list,
                    'contest_type_list' => $contest_type_list
                );

        $this->api_response_arry['data']          = $result;
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response();
    }

    /**
    * [get_contest_list]
    * Summary :- get contest list
    */  
    public function get_contest_list_post()
    {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->post();
        $season_game_uid = $post_data['season_game_uid'];
        $data = $this->Contest_model->get_contest_list($post_data);
        $contest_list = array();
        $total = $data['total'];
       
        $season_game_details = array();
        if(isset($data['result']) && !empty($data['result'])){
            $season_game_uids = array_column($data['result'], "season_game_uid");
            $season_game_uids = array_unique($season_game_uids);

            $this->load->model('Season_model');
            $match_response =  $this->Season_model->get_season_matches_by_ids($post_data);
            if(isset($match_response)){
                $season_game_details = $match_response;
                $season_game_details = array_column($season_game_details, NULL, "season_game_uid");
            }
        }
        
        $contest_list = array();
        
        if(isset($data['result'])){
            foreach($data['result'] as $contest){ 
                $temp_season = isset($season_game_details[$contest['season_game_uid']])?$season_game_details[$contest['season_game_uid']]:array();
                if(!empty($temp_season)) {
                $contest['home'] = $temp_season['home'];
                $contest['away'] = $temp_season['away'];
                $contest['home_uid'] = $temp_season['home_uid'];
                $contest['away_uid'] = $temp_season['away_uid'];
                $contest['home_flag'] = $temp_season['home_flag'];
                $contest['away_flag'] = $temp_season['away_flag'];
                $contest['prize_distibution_detail'] = json_decode($contest['prize_distibution_detail']);
                $contest_list[] = $contest;
                }
            }
        }
        $data['total'] = $total;
        $data['result'] = $contest_list;
        $this->api_response_arry['data']= $data;
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response();
    } 

    public function create_contest_post() {
        if ($this->input->post()) {
            $game_data = $this->input->post();
            $this->form_validation->set_rules('sports_id', $this->lang->line('sport'), 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('group_id', $this->lang->line('group'), 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('league_id', $this->lang->line('league'), 'trim|required|is_natural_no_zero');
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

            if ($this->input->post('prize_pool_type') == '0') {
                $this->form_validation->set_rules('master_contest_type_id', $this->lang->line('number_of_winner_id'), 'trim|required|is_natural_no_zero');
            }

            if(isset($game_data['set_sponsor']) && $game_data['set_sponsor'] == 1)
            {
                $this->form_validation->set_rules('sponsor_name', $this->lang->line("sponsor_name"),'trim|max_length[60]');
                $this->form_validation->set_rules('sponsor_logo', $this->lang->line("sponsor_logo"),'trim|required|max_length[50]');
                $this->form_validation->set_rules('sponsor_contest_dtl_image', $this->lang->line("sponsor_contest_dtl_image"),'trim|required|max_length[50]');
                $this->form_validation->set_rules('sponsor_link', $this->lang->line("sponsor_link"),'trim|max_length[255]');
            }
            $this->form_validation->set_rules('season_game_uid', $this->lang->line('season_game_uid'), 'trim|required');

            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

            $multiple_lineup = $this->input->post('multiple_lineup');
            $prize_pool_type = $this->input->post('prize_pool_type');
            $prize_pool = 0;
            $guaranteed_prize = '0';
            if ($game_data['prize_pool_type'] == '1') {
                $guaranteed_prize = '1';
                
            } else if ($game_data['prize_pool_type'] == '2') {
                $guaranteed_prize = '2';
                $game_data['site_rake'] = 0;

            }

            if ($multiple_lineup && $multiple_lineup > $game_data['size']) {
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
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

            $season_games = $this->input->post('season_game_uid');
            $schedule_dates = $this->input->post('season_scheduled_date');
            $season_scheduled_date = array();
            $season_scheduled_date[] = date("Y-m-d H:i:s", strtotime($schedule_dates));

            $max_prize_pool = 0;
            //change values for percentage case
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
           

            // $collection_result = $this->check_and_validate_collection_post(false);//
            $game_data['collection']['league_id'] = $game_data['league_id'];
            $game_data['collection']['collection_name'] = $game_data['collection_name'];
           
            $game_data['collection']['added_date'] = format_date();
            $game_data['collection']['modified_date'] = format_date();

            if(isset($game_data['contest_unique_id'])){
                $contest_unique_id = $game_data['contest_unique_id'];
            }else{
                $contest_unique_id = random_string('alnum', 9);
            }

            $contest_data = array(
                    "collection_id" =>$game_data['collection_id'],
                    "contest_unique_id"         => $contest_unique_id,
                    "sports_id"                 => $game_data['sports_id'],
                    "league_id"                 => $game_data['league_id'],
                    "group_id"                  => $game_data['group_id'],
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
                    "season_scheduled_date"     => $season_scheduled_date[0],
                    "master_contest_type_id"    => $game_data['master_contest_type_id'],
                    "guaranteed_prize"          => $guaranteed_prize,
                    "is_custom_prize_pool"      => $game_data['is_custom_prize_pool'],
                    "is_auto_recurring"         => isset($game_data['is_auto_recurring']) && $game_data['is_auto_recurring'] ? 1 : 0,
                   
                    "is_pin_contest"            => isset($game_data['is_pin_contest']) && $game_data['is_pin_contest'] ? 1 : 0,
                    "is_tie_breaker"            => $game_data['is_tie_breaker'],
                    "prize_value_type"          => isset($game_data['prize_value_type']) ? $game_data['prize_value_type'] : 0,
                    "status"                    => 0,
                    "added_date"                => format_date()
                    
                );

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

              //consolation prize check
            if(isset($game_data['consolation_prize_type']) && in_array($game_data['consolation_prize_type'],array(0,2)) && !empty($game_data['consolation_prize_value'])  )             
            {
                  $contest_data['consolation_prize'] = json_encode(array(
                      'prize_type' => $game_data['consolation_prize_type'],
                      'value' => $game_data['consolation_prize_value']
                  ));
            }
  

            $collection_data = $game_data['collection'];    
            $collection_data["collection_salary_cap"] = $salary_cap;        
            $collection_data["league_id"]           = $game_data['league_id'];
            $data_array = array();
            $data_array['collection']               = $collection_data;
            $data_array['season_scheduled_date']    = $season_scheduled_date;
            $data_array['contest']                  = $contest_data;
            if(is_array($season_games)){
                $data_array['season_games'] = $season_games;
            }else{
                $data_array['season_games'] = array($season_games);
            }
            
            if(!isset($game_data['contest_unique_id']))
            {
                if($game_data['entry_fee'] == 0 && $prize_pool > 0 && $contest_data['is_auto_recurring']==1) 
                {
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['status']          = FALSE;
                    $this->api_response_arry['message']         = $this->lang->line('auto_recurrent_create_error');
                    $this->api_response();
                }
                
                $contest_id = $this->Contest_model->save_contest($data_array);
                if($contest_id){
                    $sports_id = CRICKET_SPORTS_ID;
                    if(isset($post['sports_id']) && $post['sports_id'] != ""){
                        $sports_id = $post['sports_id'];
                    }
                    //delete filters
                    // $this->delete_lobby_cache_data('lobby_filters_',$sports_id);
                    //delete lobby upcoming section file
                    $input_arr = array();
                    $input_arr['lang_file'] = '0';
                    $input_arr['ignore_cache'] = '1';
                    $input_arr['file_name'] = 'lobby_fixture_list_';
                    $input_arr['sports_ids'] = array($sports_id);
                    // $this->delete_cache_and_bucket_cache($input_arr);
                }

                // code for notification
                if($this->input->post("group_id")!=19)
                {
                    $current_push_process_time = date('Y-m-d H:i:s',strtotime('+10 seconds',strtotime(format_date())));
                    $current_timediff = get_miliseconds(format_date(),$current_push_process_time);
                    if($current_timediff >0){
                        $sports_id = CRICKET_SPORTS_ID;
                    if(isset($post['sports_id']) && $post['sports_id'] != ""){
                        $sports_id = $post['sports_id'];
                    }
                    $this->load->model('Contest_model');
                    $collection_master_id = $this->Contest_model->get_cmid($this->input->post('season_game_uid'),$this->input->post('league_id'));
                    $current_content = array(
                        "notification_type"                         => 443, // notification on publishing the fixture
                        "cname"  => $this->input->post("collection_name"),
                        "contest_name" => $this->input->post("contest_name"),
                        "sports_id" => $sports_id,
                        "season_scheduled_date" => $this->input->post("season_scheduled_date"),
                        "cmid"=> $collection_master_id['collection_master_id'],
                        "home" => $collection_master_id['home'],
                        "away"=> $collection_master_id['away'],
                    );
                    // print_r($current_content);   exit;
                    $test_data = json_encode($current_content);
                    /*$this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
                    $this->load->helper('queue_helper');
                    put_into_delayed_q($current_content,AUTO_PUSH_QUEUE,$current_timediff,'lf_auto_push_exchange');*/
                    }
                }
                $this->api_response_arry['data']            = $contest_id;
                $this->api_response_arry['message']       = "Contest added successfully";
            }
            else
            {
                $post  = $this->post();
        
                $is_update  = $this->Contest_model->update_contest($post);
                
                if($is_update)
                {
                    //delete contest cache
                    $contest = $this->Contest_model->get_single_row('contest_id',CONTEST,array('contest_unique_id' => $post['contest']["contest_unique_id"]));

                    $match_cache_key = 'contest_'.$contest['contest_id']."_match_list";
                    $this->delete_cache_data($match_cache_key);

                    $sports_id = 7;
                    if(isset($post['sports_id']) && $post['sports_id'] != ""){
                        $sports_id = $post['sports_id'];
                    }
                    $filter_cache_key = 'lobby_filters_'.$sports_id;
                    $this->delete_cache_data($filter_cache_key);

                    $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                    $this->api_response_arry['data']          = $post['contest']['contest_unique_id'];
                    $this->api_response_arry['message']       = "Contest update successfully";
                }
                else
                {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['data']          = $post['contest_unique_id'];
                    $this->api_response_arry['message']       = "Contest not update";
                }
            }
            $this->api_response_arry['status']          = TRUE;
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['data']            = array();
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['message']         = $this->lang->line('invalid_parameter');
            $this->api_response();
        }
    }

    public function get_collection_detail_post(){
        $this->form_validation->set_rules('league_id', 'league id', 'trim|required');
        $this->form_validation->set_rules('collection_id', 'collection id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $collection = $this->Contest_model->get_collection();
        if(!empty($collection)){
            $this->api_response_arry['data'] = $collection;
            $this->api_response();
        }else{
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']         = $this->lang->line('collection_not_found');
            $this->api_response();
        }
    }

    public function mark_pin_contest_post()
    {
        $this->form_validation->set_rules('contest_id', 'Contest Id', 'trim|required');
        // $this->form_validation->set_rules('collection_id', 'collection Id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $contest = $this->Contest_model->mark_pin_contest($post_data);
        //delete user profile infor from cache
        $col_cache_key = "collection_pin_contest_".$post_data['collection_id'];
        //$this->delete_cache_data($col_cache_key);

        $col_cache_key = "collection_pin_contest_r_".$post_data['collection_id'];
        //$this->delete_cache_data($col_cache_key);

        $col_con_cache_key = "collection_contest_".$post_data['collection_id'];
        //$this->delete_cache_data($col_con_cache_key);

        $col_con_cache_key = "collection_contest_r_".$post_data['collection_id'];
        //$this->delete_cache_data($col_con_cache_key);
        
        if($contest)
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response_arry['message']         = "Contest mark pin successfully.";
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']         = $this->lang->line('no_change');
            $this->api_response();
        }
    }

    public function get_game_detail_post()
    {
        $this->form_validation->set_rules('contest_unique_id', 'contest_unique_id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $match_list = array();
        $data_arr = $this->input->post();
        $contest_detail = $this->Contest_model->get_game_detail($data_arr['contest_unique_id']);
        // print_r($contest_detail);die();
        $formats = array("1"=>"One Day", "2"=>"Test", "3"=>"T20","4"=>"T10");
        if($contest_detail)
        {
            $league_id = $contest_detail['league_id'];
            $contest_detail['prize_distibution_detail'] = json_decode($contest_detail['prize_distibution_detail']);
            //print_r($contest_detail); die;
            $sport_detail = $this->get_sport_detail_by_id($contest_detail['sports_id']);
            $league_detail = $this->get_league_detail_by_id($league_id);
            $seasonUids = $this->getMatchDetailByCollection_id($contest_detail['collection_id']);
            foreach($seasonUids as $seasonUid)
            {
                $match_detail = $this->get_league_id_by_season_game_uid($seasonUid,$league_id);
                if(isset($match_detail['format']) && $contest_detail['sports_id'] == 7)
                {
                    $match_detail['format'] = $formats[$match_detail['format']];
                }
                $match_list[] = $match_detail;
            }
            
            if($contest_detail['user_id'])
            {
                $this->load->model('user/User_model');
                $user_data = $this->User_model->get_user_detail_by_user_id($contest_detail['user_id']);
            }else{
                $user_data['user_name'] = '';
            }

            $all_position['data'] = array();
            $contest_detail['feature_img_url']  = isset($contest_detail['feature_image']) && !empty($contest_detail['feature_image']) ? IMAGE_PATH.FEATURE_CONTEST_DIR.$contest_detail['feature_image'] : '';
            $contest_detail['feature_img']      = isset($contest_detail['feature_image']) && !empty($contest_detail['feature_image']) ?$contest_detail['feature_image'] : '';
            $post_api_response['contest_detail']        =$contest_detail;
            $post_api_response['sport_detail']          =$sport_detail;
            $post_api_response['league_detail']         =$league_detail;
            $post_api_response['match_detail']          =$match_detail;
            $post_api_response['user_data']             =$user_data;
            $post_api_response['match_list']            =$match_list;

            $this->load->model('Merchandise_model');
            $post_api_response['merchandise_list'] = $this->Merchandise_model->get_merchandise_list();
            $this->api_response_arry['data']            = $post_api_response;
            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response();
        }

        $this->api_response_arry = $contest_detail;
        $this->api_response();
    }

    function get_sport_detail_by_id($sports_id){
        $response =  $this->Contest_model->get_sport_detail_by_id(array('sports_id'=>$sports_id));
        $formats = array();

        if($this->input->post("sports_id") ==7 )
        $formats = array("1"=>"One Day", "2"=>"Test", "3"=>"T20");
        $response['formats'] = $formats;
        return $response;
    }

    function get_league_detail_by_id($league_id){
        $this->load->model('League_model');
        $result = $this->League_model->get_league_detail_by_id($league_id);
        return $result;
    }

    public function getMatchDetailByCollection_id($collection_id)
    {  
        $result = $this->Contest_model->getMatchDetailByCollection_id($collection_id);
        return $result;
    }
    public function get_league_id_by_season_game_uid($season_game_uid,$league_id)
    {
        
        $season_post['season_game_uid'] = $season_game_uid;
        $season_post['league_id'] = $league_id;
        $season_post['rows']            = false;
        
        $rows = true;
        if(isset($season_post['rows']))
        {
            $rows =  $season_post['rows'];
            unset($season_post['rows']);
        }
        $this->load->model('Season_model');
        $result = $this->Season_model->get_season_data($season_post,$rows);

        return $result;
    }

    public function get_game_lineup_detail_post()
    {
        $this->form_validation->set_rules('contest_id','contest id','required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $result = $this->Contest_model->get_lineup_by_game();
        foreach ($result['result'] as $key => $value) {
            $result['result'][$key]['prize_data'] = json_decode($value['prize_data'],TRUE);
            $result['result'][$key]['winning_amount'] = isset($value['won_amount']) ? $value['won_amount'] : 0;
        }
        $this->api_response_arry['data']            = $result;
        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response();
    }

    public function get_contest_detail_post()
    {
        $this->form_validation->set_rules('contest_unique_id[]','contest unique id','required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $contest_unique_id = $this->input->post('contest_unique_id');
        $result = $this->Contest_model->get_constest_details($contest_unique_id);
        $this->api_response_arry['data']            = $result;
        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response();
    }
} 
