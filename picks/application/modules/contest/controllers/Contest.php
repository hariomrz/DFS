<?php

class Contest extends Common_Api_Controller {


	//var for message
    var $message = "";
    var $entry_fee = 0;
    var $currency_type = 1;
    var $contest_unique_id = '';
    var $enough_balance = 1;
    var $enough_coins = 1;
    var $self_exclusion_limit = 0;

	public function __construct() {
        parent::__construct();
        $this->contest_lang = $this->lang->line('contest');
    }

     /**
     * Used for get contest details
     * @param int $contest_id
     * @param string $contest_unique_id
     * @return array
     */
    public function get_contest_detail_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $current_date = format_date();
        $post_data = $this->input->post();
        $contest_id = $post_data['contest_id'];
        $this->load->model("contest/Contest_model");
        $contest = $this->Contest_model->get_contest_detail($post_data);
        if (empty($contest)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }
       
       
        if (is_null($contest['prize_distibution_detail'])) {
            $contest['prize_distibution_detail'] = array();
        }
        
        if(isset($contest['is_tie_breaker']) && $contest['is_tie_breaker'] == 1){
            $tmp_ids = array();
            foreach($contest['prize_distibution_detail'] as $prize){
                if(isset($prize['prize_type']) && $prize['prize_type'] == 3){
                    $tmp_ids[] = $prize['amount'];
                }
            }

        }
       
        $contest['user_join_count'] = 0;
        $contest['is_confirmed'] = 0;
        if($contest['guaranteed_prize'] != 2 && $contest['size'] > $contest['minimum_size'] && $contest['entry_fee'] > 0){
            $contest['is_confirmed'] = 1;
        }

       
        
        if(!isset($contest['current_prize']) || empty($contest['current_prize'])){
            $contest['current_prize'] = json_decode($contest['current_prize']);
            $tmp_contest = $contest;
            if($tmp_contest['total_user_joined'] < $contest['minimum_size']){
                $tmp_contest['total_user_joined'] = $contest['minimum_size'];
            }
            
            $contest['current_prize'] = json_encode(reset_contest_prize_data($tmp_contest));
        }

        $this->load->helper('default');
        $picks_master_data = $this->app_config['allow_picks']['custom_data'];
        $master_data = get_picks_master_data($contest,$picks_master_data );
        $contest['scoring_rules']  = $master_data;

        // unset($contest['question']);
        // unset($contest['correct']);
        // unset($contest['wrong']);

        $this->api_response_arry['data'] = $contest;
        $this->api_response();
    }

     /**
     * Used for get contest joined users list
     * @param int $season_id
     * @param int $contest_id
     * @return array
     */
    public function get_contest_users_post() {
        $this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required');
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("contest/Contest_model");
        $participant_list = $this->Contest_model->get_contest_joined_users($post_data);

        $joined_users_list = array();
        if (!empty($participant_list)) {
            $user_id_array = array_column($participant_list, "user_id");
            $user_with_join_count = array_column($participant_list, "user_count", "user_id");

            $allow_xp_point = isset($this->app_config['allow_xp_point'])?$this->app_config['allow_xp_point']['key_value']:0;

            $this->load->model("user/User_model");
            $user_list = $this->User_model->get_participant_user_details($user_id_array,$allow_xp_point);
            $user_data_array = array_column($user_list,NULL,"user_id");
            foreach ($participant_list as $value) {
                if (isset($user_data_array[$value['user_id']])) {
                    if(!isset($value['user_name']) || $value['user_name'] == ""){
                        $value['user_name'] = $user_data_array[$value['user_id']]['name'];
                    }
                    $value['user_join_count'] = $value['user_count'];
                    $value["name"] = $value['user_name'];
                    $value["image"] = $user_data_array[$value['user_id']]['image'];

                    if($allow_xp_point ==1)
                    {
                        $value['level_number'] = $user_data_array[$value['user_id']]['level_number'];
                        $value['badge_id'] = $user_data_array[$value['user_id']]['badge_id'];
                        $value['badge_name'] = $user_data_array[$value['user_id']]['badge_name'];
                        $value['badge_icon'] = $user_data_array[$value['user_id']]['badge_icon'];
                    }
                    unset($value['user_count']);
                    $joined_users_list[] = $value;
                }
            }
        }
        $return_arr = array("users" => $joined_users_list);
        if (isset($post_data['page_no']) && $post_data['page_no'] == 1) {
            $contest_info = $this->Contest_model->get_single_row("total_user_joined,size", CONTEST, array("contest_id" => $post_data['contest_id']));
            $total_user_joined = 0;
            if(!empty($contest_info)){
                $total_user_joined = $contest_info['total_user_joined'];
                if($contest_info['total_user_joined'] > $contest_info['size']){
                    $total_user_joined = $contest_info['size'];
                }
            }
            
            if ($total_user_joined == 0) {
                $total_user_joined = count($joined_users_list);
            }
            $return_arr['total_user_joined'] = $total_user_joined;
        }

        $this->api_response_arry['data'] = $return_arr;
        $this->api_response();
    }


    /**
     * used for validate contest join data
     * @param array $contest
     * @param array $post_data
     * @return array
     */
    public function validation_for_join_game($contest, $post_data) {
        if (empty($contest)) {
            $this->message = $this->contest_lang['invalid_contest'];
            return 0;
        }

        $this->load->model("user/User_model");
        $balance_arr = $this->User_model->get_user_balance($this->user_id);

        //for manage collection wise deadline
        $current_date = format_date();
        $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
        if (isset($contest['deadline_time']) && $contest['deadline_time'] >= 0) {
            $deadline_time = $contest['deadline_time'];
        }
        $current_time = date(DATE_FORMAT, strtotime($current_date . " +" . $deadline_time . " minute"));

        //check for match schedule date
        if (strtotime($contest['scheduled_date']) < strtotime($current_time)) {
            $this->message = $this->contest_lang['contest_already_started'];
            return 0;
        }

        //check for full contest
        if ($contest['total_user_joined'] >= $contest['size']) {
            $this->message = $this->contest_lang['contest_already_full'];
            return 0;
        }

        //if contest closed
        if ($contest['status'] !== '0') {
            $this->message = $this->contest_lang['contest_closed'];
            return 0;
        }

        $user_join_data = $this->Contest_model->get_user_contest_join_count($contest);
        
        $user_join_count = 0;
        if (isset($user_join_data['user_joined_count'])) {
            $user_join_count = $user_join_data['user_joined_count'];
        }
        //check for multi lineup
        if ($contest['multiple_lineup'] > 0 && $contest['multiple_lineup'] == $user_join_count) {
            $this->message = $this->contest_lang['you_already_joined_to_max_limit'];
            return 0;
        } else if ($contest['multiple_lineup'] == 0 && $user_join_count > 0) {
            $this->message = $this->contest_lang['join_multiple_time_error'];
            return 0;
        }

        //check for already joined for same lineup
        $contest_joined = $this->Contest_model->get_single_row("user_contest_id", USER_CONTEST, array("contest_id" => $post_data['contest_id'], "user_team_id" => $post_data['user_team_id']));
        if (!empty($contest_joined)) {
            $this->message = $this->contest_lang['you_already_joined_this_contest'];
            return 0;
        }

        //check for valid line up of user
        $user_lineup = $this->Contest_model->get_single_row("user_team_id,season_id", USER_TEAM, array("user_team_id" => $post_data['user_team_id'], "user_id" => $this->user_id));
        if (empty($user_lineup)) {
            $this->message = $this->contest_lang['provide_a_valid_user_team_id'];
            return 0;
        }

        if ($contest['season_id'] != $user_lineup['season_id'])  {
            $this->message = $this->contest_lang['not_a_valid_team_for_contest'];
            return 0;
        }
       
        $this->user_bonus_bal = $balance_arr['bonus_amount'];
        $this->user_bal = $balance_arr['real_amount'];
        $this->winning_bal = $balance_arr['winning_amount'];
        $this->point_balance = $balance_arr['point_balance'];

        if($this->entry_fee == '0') {
            return 1;
        }
        
        if ($contest['prize_type'] == 2 || $contest['prize_type'] == 3) { // for Conins 
            if ($this->entry_fee > $this->point_balance) {
                $this->message = $this->contest_lang['not_enough_coins'];
                $this->enough_coins = 0;
                return 0;
            }
        } else {
            //get user balance
            $bonus_amount = $max_bonus = 0;

            if($this->currency_type != "2" && $this->entry_fee > 0){
                $banned_state = $this->get_banned_state();
                $banned_state_ids   = array_column($banned_state,"state_id");
                $banned_state_names = array_column($banned_state,"name");
                if(!isset($balance_arr['master_state_id']) || $balance_arr['master_state_id'] == ""){
                    $this->message = $this->contest_lang['state_required_error'];
                    return 0;
                }else if(in_array($balance_arr['master_state_id'], $banned_state_ids)){
                    $state_str = implode(", ",$banned_state_names);
                    $this->message = str_replace("{{STATE_LIST}}",$state_str,$this->contest_lang['state_banned_error']);
                    return 0;
                }
            }
            
            if($this->currency_type == "2"){
                if ($this->entry_fee > $this->point_balance) {
                    $this->message = $this->contest_lang['not_enough_balance'];
                    $this->enough_balance = 0;
                    return 0;
                }
            }else{
                //$max_bonus_percentage = MAX_BONUS_PERCEN_USE;
                if ($contest['max_bonus_allowed']) {
                    $max_bonus_percentage = $contest['max_bonus_allowed'];
                    $max_bonus = ($this->entry_fee * $max_bonus_percentage) / 100;
                    $bonus_amount = $max_bonus;
                }
                if ($max_bonus > $this->user_bonus_bal) {
                    $bonus_amount = $this->user_bonus_bal;
                }
                
                if(MAX_CONTEST_BONUS > 0 && $bonus_amount > MAX_CONTEST_BONUS) {
                    $bonus_amount = MAX_CONTEST_BONUS;
                }
                if ($this->entry_fee > ($bonus_amount + $this->user_bal + $this->winning_bal)) {
                    $this->message = $this->contest_lang['not_enough_balance'];
                    $this->enough_balance = 0;
                    return 0;
                }
            }
        }
        if($this->currency_type == "1"){
            $this->self_exclusion_limit = 0;
            //$this->get_app_config_data();
            $allow_self_exclusion = isset($this->app_config['allow_self_exclusion'])?$this->app_config['allow_self_exclusion']['key_value']:0;        
            if($allow_self_exclusion) {
                $custom_data = $this->app_config['allow_self_exclusion']['custom_data'];
                $default_max_limit = $custom_data['default_limit'];
                $contest_ids = $this->Contest_model->user_join_contest_ids($this->user_id);   

                if(!empty($contest_ids)) {
                    $this->load->model("user/User_model");
                    $self_exclusion_data = array('user_id' => $this->user_id, 'contest_ids' => $contest_ids, 'max_limit' => $default_max_limit, 'entry_fee' => $this->entry_fee);
                    $this->message = $this->contest_lang['self_exclusion_limit_reached'];
                    $flag =  $this->User_model->check_self_exclusion($self_exclusion_data);
                    if(empty($flag)) {
                        $this->self_exclusion_limit = 1;
                    }
                    return $flag;
                }
            }
        }
        return 1;
    }

    /**
     * Used for join contest
     * @param int $user_team_id
     * @param int $contest_id
     * @return array
     */
    public function join_game_post() { 
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        $this->form_validation->set_rules('user_team_id', $this->lang->line('user_team_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $this->load->model("contest/Contest_model");
        $contest = $this->Contest_model->get_contest_detail($post_data);
        
        $this->entry_fee = $contest['entry_fee'];
        //Call validate banned state api to allow free contest 
        $this->validate_banned_state($this->entry_fee);
        $this->contest_unique_id = $contest['contest_unique_id'];
        $this->currency_type = $contest['currency_type'];
  

        $is_valid = $this->validation_for_join_game($contest, $post_data);
  		
        if ($is_valid) {
            if ($is_valid || $contest['entry_fee'] == 0) {
                $joined = $this->Contest_model->join_game($contest, $post_data);

                $joined_status = 1;
                if (isset($joined['joined_count']) && $contest['entry_fee'] > 0) {
                    $cash_type = "2";
                    if($this->currency_type == "2"){
                        $cash_type = "3";
                    }

                    $custom_data = ['match'=>$contest['match'],'contest'=>!empty($contest['contest_title']) ? $contest['contest_title'] : $contest['contest_name']];
                    $withdraw = array();
                    $withdraw["source"] = CONTEST_JOIN_SOURCE;
                    $withdraw["source_id"] = $joined['user_contest_id'];
                    $withdraw["reason"] = 	'for joining contest';
                    $withdraw["cash_type"] = $cash_type;
                    $withdraw["user_id"] = $this->user_id;
                    $withdraw["amount"] = $this->entry_fee;
                    $withdraw["currency_type"] = $this->currency_type;
                    $withdraw["max_bonus_allowed"] = $contest['max_bonus_allowed'];
                    $withdraw["site_rake"] = $contest['site_rake'];
                    $withdraw["reference_id"] = $contest['contest_id'];
                    $withdraw["contest_name"] = $contest['contest_name'];
                    $withdraw["custom_data"] = $custom_data;
                    $this->load->model("user/User_model");
                    $join_result = $this->User_model->withdraw($withdraw);
                    if (empty($join_result) || $join_result['result'] == 0) {
                        //remove user joined entry
                        $this->Contest_model->remove_joined_game($contest, $joined['user_contest_id']);

                        $joined_status = 0;
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = isset($join_result['message']) ? $join_result['message'] : $this->contest_lang['problem_while_join_game'];
                        $this->api_response();
                    }
                    
                }
                //join game notification
                if (isset($joined['joined_count']) && $joined_status == 1) {
                    //create auto recurring contest
                    if (isset($joined['joined_count']) && $joined['joined_count'] == $contest['size'] && $contest['is_auto_recurring'] == '1') {
                        $this->load->helper('queue_helper');
                        $contest_queue = array("action" => "auto_recurring", "data" => array("contest_unique_id" => $contest['contest_unique_id']));
                        add_data_in_queue($contest_queue, 'picks_contest');
                    }

                    $input = array(
                        'contest_name' => $contest['contest_name'],
                        'contest_unique_id' => $contest['contest_unique_id'],
                        'contest_id' => $contest['contest_id'],
                        'entry_fee' => $contest['entry_fee'],
                        'prize_pool' => $contest['prize_pool'],
                        'prize_type' => $contest['prize_type'],
                        'currency_type' => $contest['currency_type'],
                        'prize_distibution_detail' => json_decode($contest['prize_distibution_detail'],TRUE),
                        'scheduled_date' => $contest["scheduled_date"],
                        'match'=>$contest['match'],
                    );

                    $notify_data = array();
                    $notify_data["notification_type"] = CONTEST_JOIN_NOTIFY; //-JoinGame, 
                    $notify_data["source_id"] = $joined['user_contest_id'];

                    $allow_join_email = isset($this->app_config['allow_join_email'])?$this->app_config['allow_join_email']['key_value']:0;
        
                    $notify_data["notification_destination"] = 3; //Web,Push,Email
                    if($allow_join_email)
                    {
                        $notify_data["notification_destination"] = 7; //Web,Push
                    }

                    $notify_data["user_id"] = $this->user_id;
                    $notify_data["to"] = $this->email;
                    $notify_data["user_name"] = $this->user_name;
                    $notify_data["added_date"] = $current_date;
                    $notify_data["modified_date"] = $current_date;
                    $input['int_version'] = $this->app_config['int_version']['key_value'];
                    $notify_data["content"] = json_encode($input);
                    $notify_data["subject"] = $this->contest_lang['join_game_email_subject'];
                    $this->load->model('user/User_nosql_model');
                    $this->User_nosql_model->send_notification($notify_data);

                    //delete user balance data
                    $user_balance_cache_key = 'user_balance_' . $this->user_id;
                    $this->delete_cache_data($user_balance_cache_key);

                    //delete contest cache
                    //$this->delete_cache_data($contest_cache_key);

                    //delete joined data
                  /*  $user_contest_cache_key = 'user_contest_' . $contest["season_id"] . "_" . $this->user_id;
                    $this->delete_cache_data($user_contest_cache_key);*/

                    //season contest
                    $season_contest_cache_key = "picks_season_contest_" . $contest["season_id"];
                    $this->delete_cache_data($season_contest_cache_key);

                    $user_teams_cache_key = "picks_user_teams_".$contest['season_id']."_".$this->user_unique_id;
					$this->delete_cache_data($user_teams_cache_key);

                    
                    //collection pin contest
/*                    if(isset($contest["is_pin_contest"]) && $contest["is_pin_contest"] == 1){
                        $season_pin_contest_cache_key = "picks_season_pin_contest_" . $contest["season_id"];
                        $this->delete_cache_data($season_pin_contest_cache_key);
                      
                    }*/

                    $user_device_ids = [];
                    if ($contest['is_private'] == 1 && isset($post_data['device_type']))
                    {
                        $device_data = $this->Contest_model->get_user_device_ids($this->user_id);
                        if (!empty($device_data))
                        {
                            $user_device_ids = $device_data;
                        }
                    }
                    $this->api_response_arry['data']['user_device_ids'] = $user_device_ids;
                    
                    if(isset($post_data['ct']) && $post_data['ct'] == "1"){
                        $total_join_contest = $this->Contest_model->get_user_total_contest_join_count();
                        $count_data = $total_join_contest['total_join_contest'];
                        $c_count = 1;
                        if(!empty($count_data) && $count_data > 0){
                            $c_count = $count_data;
                        }
                        $this->api_response_arry['data']['ct'] = $c_count;
                    }
                    $this->api_response_arry['message'] = $this->contest_lang['join_game_success'];
                    $this->api_response();
                } else {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->contest_lang['problem_while_join_game'];
                    $this->api_response();
                }
            } else {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->message;
                $this->api_response();
            }
        } else {
            $this->api_response_arry['data']['self_exclusion_limit'] = $this->self_exclusion_limit;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->message;
            $this->api_response();
        }
    }


    /**
     * used for get user joined match list
     * @param int $status
     * @param int $sports_id
     * @return array
     */
    public function get_user_joined_fixture_by_status_post() {
       
        $this->form_validation->set_rules('status', $this->lang->line('match_status'), 'trim|required|in_list[0,1,2]');
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $status = $post_data['status'];
        $this->load->model("contest/Contest_model");
        $current_date = format_date();
        $result = $this->Contest_model->get_user_joined_fixtures($post_data);
        $final_data = array();
        if (!empty($result)) 
        {	
        	foreach ($result as &$fixture) {
	           	$post_data['season_id'] = $fixture['season_id'];
	           	$post_data['league_id'] = $fixture['league_id'];
	        	$match_list_detail = $this->Contest_model->get_lobby_season_detail($post_data);
              
	        	$match_info = isset($match_list_detail) ? $match_list_detail : array();
                 
	        	$data = array_merge($fixture, $match_info);
                
                if(isset($fixture['season_id']))
                {
                    $final_data[] = $data;
                }
        	}
            
        }

        if (!empty($final_data)) 
        {
            $scheduled_date = array_column($final_data, 'scheduled_date');

            if ($status == 2 || $status == 1) 
            {
                array_multisort($scheduled_date, SORT_DESC, $final_data);
            }
            else
            {
                array_multisort($scheduled_date, SORT_ASC, $final_data);
            }

        }

        $this->api_response_arry['data'] = $final_data;
        $this->api_response();
    }


    /**
     * used for get user joined contest list
     * @param int $status
     * @param int $season_id
     * @param int $sports_id
     * @return array
     */
    public function get_user_contest_by_status_post() {
        $this->form_validation->set_rules('status', $this->lang->line('match_status'), 'trim|required|in_list[0,1,2]');
        $this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $status = $post_data['status'];
        $season_id = $post_data['season_id'];

        $this->load->model("contest/Contest_model");
        $contest_list = $this->Contest_model->get_user_fixture_contest($season_id, $status);
        $fixture_contest = array();
        if(!empty($contest_list))
        {
	        foreach ($contest_list as $contest) {

	            if (!array_key_exists($contest['contest_id'], $fixture_contest)) {
	                $contest['is_confirmed'] = 0;
	                if($contest['guaranteed_prize'] != 2 && $contest['size'] > $contest['minimum_size'] && $contest['entry_fee'] > 0){
	                    $contest['is_confirmed'] = 1;
	                }
	                $fixture_contest[$contest['contest_id']] = array(
	                    "contest_id" => $contest["contest_id"],
	                    "contest_unique_id" => $contest["contest_unique_id"],
	                    "season_id" => $contest["season_id"],
	                    "contest_name" => $contest["contest_name"],
	                    "contest_title" => $contest["contest_title"],
	                    "sports_id" => $contest["sports_id"],
	                    "league_id" => $contest["league_id"],
	                    "group_id" => $contest["group_id"],
	                    "size" => $contest["size"],
	                    "minimum_size" => $contest["minimum_size"],
	                    "total_user_joined" => $contest["total_user_joined"],
	                    "entry_fee" => $contest["entry_fee"],
	                    "prize_pool" => $contest["prize_pool"],
	                    "prize_type" => $contest["prize_type"],
	                    "prize_distibution_detail" => $contest["prize_distibution_detail"],
	                    "status" => $contest["status"],
	                    "multiple_lineup" => $contest["multiple_lineup"],
	                    "user_joined_count" => $contest["user_joined_count"],
	                    "max_bonus_allowed" => $contest["max_bonus_allowed"],
	                    "group_name" => $contest["group_name"],
	                    "currency_type" => $contest["currency_type"],
	                    "guaranteed_prize" => $contest["guaranteed_prize"],
	                    "is_confirmed" => $contest["is_confirmed"],
	                    "is_tie_breaker" => $contest["is_tie_breaker"],
	                    "prize_amount" => $contest["prize_amount"],
                        "league_name" => $contest["league_name"]
	                );

	            }
	            
	            $is_winner = $contest["is_winner"];
	            if ($status == 1 && !empty($contest["prize_distibution_detail"])) {//LIVE
	                $prize_details = json_decode($contest["prize_distibution_detail"], TRUE);
	                $last_element = end($prize_details);

	                if (!empty($last_element['max']) && $last_element['max'] >= $contest["game_rank"]) {
	                    $is_winner = 1;
	                }
	            }

	            $fixture_contest[$contest['contest_id']]["teams"][$contest['user_contest_id']] = array(
	                "user_team_id" => $contest['user_team_id'],
	                "team_name" => $contest["team_name"],
	                "user_contest_id" => $contest["user_contest_id"],
	                "total_score" => $contest["total_score"] ? $contest["total_score"] : 0,
	                "game_rank" => $contest["game_rank"] ? $contest["game_rank"] : 0,
	                "is_winner" => $is_winner,
	                "won_prize" => ($contest['is_winner'] == 1 && isset($contest["won_amount"]) && $contest["prize_type"] != 3) ? $contest["won_amount"] : "",
	                "prize_data" => !empty($contest["prize_data"])? $contest["prize_data"] : [],
	              
	            );
	        }

	        if (!empty($fixture_contest)) {
	            array_multisort($fixture_contest, SORT_ASC);
	        }
	        $sort= [];
	        foreach($fixture_contest as $key=>&$contest)
	        {
	           // ksort($contest['teams']);
	            $contest['teams']  = array_values($contest['teams']);
	            $sort['prize_amount'][$key] = $contest['prize_amount'];
	            unset($contest['prize_amount']);
	        }
	        if(!empty($sort['prize_amount'])){
	            array_multisort($sort['prize_amount'], SORT_DESC,$fixture_contest);
	        }

    	}
       
        $this->api_response_arry['data'] = $fixture_contest;
        $this->api_response();
    }


    /**
     * @method [get_contest_leaderboard]
     * @param  contest_id
     */
    public function get_contest_leaderboard_post()
    {
    	$this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        $current_date = format_date();
       	$result['own'] = array();
       	$result['other'] = array();
       	$result['top_three'] = array();

        $league_id = $this->input->post('league_id');
        $this->load->model('Contest_model');

        $result['top_three']= $this->Contest_model->get_contest_leaderboard_top_three($post);
        /*Own List*/
        if($this->user_id)
        {
            $own_result= $this->Contest_model->get_contest_leaderboard($post ,$this->user_id);
            foreach($own_result as $key => $row)
            {
                if($row['user_id'] == $this->user_id)
                {
                    $result['own'][] = $row;
                }
            }
        }
        /*Other User list*/
        $result['other'] = $this->Contest_model->get_contest_leaderboard($post );

        $contest_details= $this->Contest_model->get_single_row("guaranteed_prize,total_user_joined,entry_fee,site_rake,size,minimum_size,prize_distibution_detail,current_prize,IF(user_id > 0,'1','0') as is_private,host_rake",CONTEST,array('contest_id' => $post['contest_id']) );

        if(!empty($contest_details['prize_distibution_detail']))
        {
            $result['prize_data'] = $contest_details['prize_distibution_detail'];
        }
        if($contest_details['guaranteed_prize'] != "2"){
            $contest_details['current_prize'] = json_decode($contest_details['current_prize'],TRUE);
            if(!isset($contest_details['current_prize']) || empty($contest_details['current_prize'])){
                $tmp_contest = $contest_details;
                if($tmp_contest['total_user_joined'] < $contest_details['minimum_size']){
                    $tmp_contest['total_user_joined'] = $contest_details['minimum_size'];
                }
                
                $contest_details['current_prize'] = reset_contest_prize_data($tmp_contest);
            }
            foreach($contest_details['current_prize'] as &$row){
                if($row['prize_type'] != "3"){
                    $no_user = $row['max'] - $row['min'] + 1;
                    $row['amount'] = $row['min_value'] = number_format(($row['amount'] / $no_user),2,".","");
                    $row['max_value'] = number_format(($row['max_value'] / $no_user),2,".","");
                }
            }
            $result['prize_data'] = json_encode($contest_details['current_prize']);
        }

        $this->api_response_arry['data']  = $result;
        $this->api_response();
       
    }

   /**
    * @method get contest team count
    * @param season_id and sports_id
    * @return json array
    */
    public function get_my_contest_team_count_post(){

        $this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $this->load->model('Contest_model');
        $data = $this->Contest_model->get_my_contest_team_count($post_data);

        $this->api_response_arry['data'] = $data;
        $this->api_response();
    

    }

   /**
    * used for get contest invite code
    * @param int $contest_id
    * @return array
    */
    public function get_contest_invite_code_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $season_type = '1';
        $post_data = $this->input->post();
        $contest_id = $post_data['contest_id'];
        $this->load->model("contest/Contest_model");
        $contest = $this->Contest_model->get_contest_detail($post_data);
        if (empty($contest)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['contest_not_found'];
            $this->api_response();
        }
        //check for existence
        $this->load->model("contest/Contest_model");
        $exist = $this->Contest_model->get_single_row("contest_id,code", INVITE, array("contest_id" => $contest['contest_id'], "season_type" => $season_type, "user_id" => 0,"network_contest" => 0));
        if (empty($exist)) {
            $code = $this->Contest_model->_generate_contest_code();
            $invite_data = array(
                "contest_id" => $contest['contest_id'],
                "contest_unique_id" => $contest["contest_unique_id"],
                "invite_from" => $this->user_id,
                "code" => $code,
                "season_type" => $season_type,
                "expire_date" => date(DATE_FORMAT, strtotime($contest['scheduled_date'])),
                "created_date" => format_date(),
                "status" => 1
            );
            $this->Contest_model->save_invites(array($invite_data));
        } else {
            $code = $exist['code'];
        }

        $this->api_response_arry['data'] = $code;
        $this->api_response();
    }


    public function get_my_lobby_fixtures_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $this->load->model('Contest_model');
        $result = $this->Contest_model->get_my_joined_fixtures($post_data);
        //echo "<pre>";print_r($result);die;
        $final_data = array();
        $upcoming =array();
        $live =array();
        $completed =array();
        if (!empty($result)) 
        {
           
            $match_list_detail = array();
            foreach ($result as &$fixture) {
              $post_data['season_id'] = $fixture['season_id'];
            //   echo "<pre>";
            //   print_r($post_data['season_id']);die;
              $match_list_detail = $this->Contest_model->get_lobby_season_detail($post_data);
                echo "<pre>";
              print_r($match_list_detail);die;
              $match_list_detail = !empty($match_list_detail)? $match_list_detail:array();
              $data = array_merge($fixture, $match_list_detail);
              $final_data[] = $data;
            }
 


        }

        foreach ($final_data as $fixture) 
        {
          
            if($fixture['is_live'] == '1')
            {
                $live[strtotime($fixture['scheduled_date'])] =  $fixture;
            }

            if($fixture['is_upcoming'] == '1')
            {
                $upcoming[strtotime($fixture['scheduled_date'])] =  $fixture;
            }

            if($fixture['contest_status'] == '2' || $fixture['contest_status'] == '3')
            {
                $completed[strtotime($fixture['scheduled_date'])] =  $fixture;
            }
        }

            krsort($live);
            ksort($upcoming);
            krsort($completed); 
            //Live, Upcoming and Last 7 days Completed Fixture
            $final_data = array_merge($live,$upcoming,$completed);

        

       $this->api_response_arry['data'] = $final_data;
        $this->api_response();

    }

    /**
     * used for get user team list for switch
     * @param int $sports_id
     * @param int $contest_id
     * @return array
     */
    public function get_user_switch_team_list_post() {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['user_id'] = $this->user_id;
        $this->load->model("contest/Contest_model");
        $team_data = $this->Contest_model->get_season_contest_free_teams($post_data);

        $this->api_response_arry['data'] = $team_data;
        $this->api_response();
    }

    /**
     * used for swith team in joined contest
     * @param int $sports_id
     * @param int $contest_id
     * @param int $user_team_id
     * @param int $user_contest_id
     * @return array
     */
    public function switch_team_contest_post() {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        $this->form_validation->set_rules('user_team_id', $this->lang->line('user_team_id'), 'trim|required');
        $this->form_validation->set_rules('user_contest_id', $this->lang->line('user_contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['user_id'] = $this->user_id;
        $this->load->model("contest/Contest_model");
        $check_valid_previous_team = $this->Contest_model->check_valid_user_previous_team($post_data);
        if (empty($check_valid_previous_team)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_previous_team_for_season'];
            $this->api_response();
        }

        $check_valid = $this->Contest_model->get_single_row("user_contest_id", USER_CONTEST, array("contest_id" => $post_data['contest_id'], "user_team_id" => $post_data['user_team_id']));
        if (!empty($check_valid)) {
            $message = $this->contest_lang['you_already_joined_this_contest'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $message;
            $this->api_response();
        }
        $is_valid = $this->Contest_model->check_valid_team_for_contest($post_data);
        if ($is_valid) {
            $team_arr = array();
            $team_arr['user_team_id'] = $post_data['user_team_id'];
            $this->Contest_model->update(USER_CONTEST, $team_arr, array('user_contest_id' => $post_data['user_contest_id']));

            $this->api_response_arry['message'] = $this->contest_lang['team_switch_success'];
            $this->api_response();
        } else {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_team_for_season'];
            $this->api_response();
        }
    }

}