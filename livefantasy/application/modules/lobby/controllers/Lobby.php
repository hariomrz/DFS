<?php
class Lobby extends Common_Api_Controller 
{
    var $self_exclusion_limit = 0;
	function __construct()
	{
        parent::__construct();
        $this->contest_lang = $this->lang->line('contest');
	}

	/**
     * Used for get lobby fixture list
     * @param int $sports_id
     * @return array
     */
    public function get_lobby_fixture_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|is_natural_no_zero|max_length[2]');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data["sports_id"];
        $this->load->model("lobby/Lobby_model");
        $result = $this->Lobby_model->get_lobby_fixture_list($sports_id);
        //echo "<pre>";print_r($result);die;
        $fixture_list = array();
        if(!empty($result)){
            $game_keys = array_column($result,"game_key");
            $post_data['game_keys'] = array_unique($game_keys);
            $match_list = $this->Lobby_model->get_season_detail_by_ids($post_data);
            $match_list = array_column($match_list, NULL, "season_game_uid");
            //echo "<pre>";print_r($match_list);die;
            foreach($result as $collection){
            	$match_info = $match_list[$collection['season_game_uid']];
                $delay_text = "";
                if($match_info['dm'] > 0){
                    $fixture_delay = convert_minute_to_hour_minute($match_info['dm']);
                    if($fixture_delay['hour'] > 0){
                        $delay_text = $fixture_delay['hour']." Hour ";
                    }
                    if($fixture_delay['minute'] > 0){
                        $delay_text.= $fixture_delay['minute']." Min ";
                    }
                }
                $match_info['dtxt'] = $delay_text;
                $match_info['game_starts_in'] = (strtotime($match_info['season_scheduled_date']))*1000;
            	if(!isset($fixture_list[$collection['game_key']]) || empty($fixture_list[$collection['game_key']])){
            		$fixture_list[$collection['game_key']] = $match_info;
            	}
            	$fixture_list[$collection['game_key']]["game"][] = array("collection_id"=>$collection['collection_id'],"inning"=>$collection['inning'],"over"=>$collection['overs'],"prize_pool"=>$collection['prize_pool'],"status"=>$collection['status'],"timer_date"=>strtotime($collection['timer_date']));
            } 
        }
        $fixture_list = array_values($fixture_list);
        $this->api_response_arry['data'] = $fixture_list;
        $this->api_response();
    }

    /**
     * Used for get fixture(match) details
     * @param int $sports_id
     * @param int $collection_id
     * @return array
     */
    public function get_fixture_details_post() 
    {
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $collection_id = $post_data['collection_id'];
        $this->load->model("lobby/Lobby_model");
        $result = $this->Lobby_model->get_collection_fixture_details($collection_id);
        if(!empty($result)){
            $result['game_starts_in'] = (strtotime($result['season_scheduled_date']))*1000;
            $result['score_data'] = json_decode($result['score_data'],TRUE);
            $result['timer_date'] = strtotime($result['timer_date']);
        }

        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
     * Used for get fixture(match) contest listing
     * @param int $sports_id
     * @param int $collection_id
     * @return array
     */
    public function get_fixture_contest_post() 
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|is_natural_no_zero|max_length[2]');
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $this->load->model("lobby/Lobby_model");
        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $collection_id = $post_data['collection_id'];
        $user_id = intval($this->user_id);
        $group_cache_key = "lf_group_list";
        $group_list = $this->get_cache_data($group_cache_key);
        if(!$group_list){
            $group_list = $this->Lobby_model->get_all_group_list();
            $this->set_cache_data($group_cache_key,$group_list,REDIS_2_HOUR);
        }
        $group_list_arr = array_column($group_list, NULL, "group_id");
        $result = $this->Lobby_model->get_collection_contests($post_data);

        $user_contest = array();
        if($this->user_id){
            $user_contest_ids = $this->Lobby_model->get_user_joined_contests($collection_id);
            if(isset($user_contest_ids['ids']) && !empty($user_contest_ids['ids'])){
                $user_contest = explode(",",$user_contest_ids['ids']);
            }
        }
        //echo "<pre>";print_r($result);die;        
        $group_list_data = array();
        foreach ($result as $key => $value) 
        {
            if(!empty($user_contest) && in_array($value['contest_id'], $user_contest)){
                continue;
            }
            $value["prize_detail"] = json_decode($value['prize_detail'],true);
            if(!isset($group_list_data[$value['group_id']])){
                $group_list_data[$value['group_id']] = $group_list_arr[$value['group_id']];
            }
            $value['is_confirmed'] = 0;
            if($value['guaranteed_prize'] != 2 && $value['size'] > $value['minimum_size'] && $value['entry_fee'] > 0){
                $value['is_confirmed'] = 1;
            }
            $group_list_data[$value['group_id']]['contest'][] = $value;
            $group_list_data[$value['group_id']]['total'] = count($group_list_data[$value['group_id']]['contest']);
        }
        $final_contest_data = array();
        foreach($group_list as $group){
            if(isset($group_list_data[$group['group_id']])){
                $final_contest_data[] = $group_list_data[$group['group_id']];
            }
        }
        
        $post_data['pin_contest'] = 1;
        $pin_contest = $this->Lobby_model->get_collection_contests($post_data);
        $pin_contest_list = array();
        foreach ($pin_contest as $key => $value) 
        {
            if(!empty($user_contest) && in_array($value['contest_id'], $user_contest)){
                continue;
            }
            $value["prize_detail"] = json_decode($value['prize_detail'],TRUE);
            if($value["prize_detail"] == null){
                $value["prize_detail"] = array();
            }
            $value['is_confirmed'] = 0;
            if($value['guaranteed_prize'] != 2 && $value['size'] > $value['minimum_size'] && $value['entry_fee'] > 0){
                $value['is_confirmed'] = 1;
            }
            $pin_contest_list[] = $value;
        }
        
        $total_contest = array_sum(array_column($group_list_data,'total'));
        $total_contest = $total_contest + count($pin_contest);
        $this->api_response_arry['data']['pin'] = $pin_contest_list;
        $this->api_response_arry['data']['contest'] = $final_contest_data;
        $this->api_response_arry['data']['total'] = $total_contest;
        $this->api_response_arry['data']['user_joined'] = count($user_contest);
        $this->api_response();
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

        $post_data = $this->input->post();
        $contest_id = $post_data['contest_id'];
        $this->load->model("lobby/Lobby_model");
        $contest = $this->Lobby_model->get_contest_detail($post_data);
        if (empty($contest)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }

        if ($contest['contest_creater'] != 0)
        {
            $this->load->model("user/User_model");
            $creator_details = $this->User_model->get_users_by_ids($contest['contest_creater']);
            if (!empty($creator_details))
            {
                $contest['creator_details'] = $creator_details[0];
            }
        }

        //contest matches list data in cache
        $collection_id = $contest['collection_id'];
        $collection = $this->Lobby_model->get_collection_fixture_details($collection_id);
        $contest = array_merge($contest, $collection);
        unset($contest['home_uid']);
        unset($contest['away_uid']);
        $contest['game_starts_in'] = (strtotime($contest['season_scheduled_date'])) * 1000;
        $contest['timer_date'] = strtotime($contest['timer_date']);
        if (!empty($contest['prize_detail'])) {
            $contest['prize_detail'] = json_decode($contest['prize_detail'], true);
            if ($contest['prize_detail'] == null) {
                $contest['prize_detail'] = array();
            }
        }

        $contest['merchandise'] = array();
        if(isset($contest['is_tie_breaker']) && $contest['is_tie_breaker'] == 1){
            $tmp_ids = array();
            foreach($contest['prize_detail'] as $prize){
                if(isset($prize['prize_type']) && $prize['prize_type'] == 3){
                    $tmp_ids[] = $prize['amount'];
                }
            }
            if(!empty($tmp_ids)){
                $merchandise_list = $this->Lobby_model->get_merchandise_list($tmp_ids);
                $contest['merchandise'] = $merchandise_list;
            }
        }

        if(!empty($contest['consolation_prize']))
        {
            $contest['consolation_prize'] = json_decode($contest['consolation_prize'], true);
        }

        $contest['user_join_count'] = 0;
        $contest['is_confirmed'] = 0;
        if($contest['guaranteed_prize'] != 2 && $contest['size'] > $contest['minimum_size'] && $contest['entry_fee'] > 0){
            $contest['is_confirmed'] = 1;
        }
        $this->api_response_arry['data'] = $contest;
        $this->api_response();
    }

    /**
     * Used for get public contest details
     * @param string $contest_unique_id
     * @return array
     */
    public function get_public_contest_post() {
        $this->form_validation->set_rules('contest_unique_id', $this->lang->line('contest_unique_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("lobby/Lobby_model");
        $contest = $this->Lobby_model->get_contest_detail($post_data);
        if (empty($contest)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }
        //contest matches list data in cache
        $collection_id = $contest['collection_id'];
        $collection = $this->Lobby_model->get_collection_fixture_details($collection_id);
        $contest = array_merge($contest, $collection);
        unset($contest['home_uid']);
        unset($contest['away_uid']);
        $contest['game_starts_in'] = (strtotime($contest['season_scheduled_date'])) * 1000;
        $contest['timer_date'] = strtotime($contest['timer_date']);
        if (!empty($contest['prize_detail'])) {
            $contest['prize_detail'] = json_decode($contest['prize_detail'], true);
            if ($contest['prize_detail'] == null) {
                $contest['prize_detail'] = array();
            }
        }

        $contest['merchandise'] = array();
        if(isset($contest['is_tie_breaker']) && $contest['is_tie_breaker'] == 1){
            $tmp_ids = array();
            foreach($contest['prize_detail'] as $prize){
                if(isset($prize['prize_type']) && $prize['prize_type'] == 3){
                    $tmp_ids[] = $prize['amount'];
                }
            }
            if(!empty($tmp_ids)){
                $merchandise_list = $this->Lobby_model->get_merchandise_list($tmp_ids);
                $contest['merchandise'] = $merchandise_list;
            }
        }

        if(!empty($contest['consolation_prize']))
        {
            $contest['consolation_prize'] = json_decode($contest['consolation_prize'], true);
        }

        $contest['is_confirmed'] = 0;
        if($contest['guaranteed_prize'] != 2 && $contest['size'] > $contest['minimum_size'] && $contest['entry_fee'] > 0){
            $contest['is_confirmed'] = 1;
        }

        $this->api_response_arry['data'] = $contest;
        $this->api_response();
    }

    /**
     * Used for get contest joined users list
     * @param int $collection_id
     * @param int $contest_id
     * @return array
     */
    public function get_contest_users_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("lobby/Lobby_model");
        $participant_list = $this->Lobby_model->get_contest_joined_users($post_data);
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
            $contest_info = $this->Lobby_model->get_single_row("total_user_joined,size", CONTEST, array("contest_id" => $post_data['contest_id']));
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
     * Used for join contest
     * @param int $contest_id
     * @return array
     */
    public function join_game_post() { 
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $this->load->model("lobby/Lobby_model");
        $contest = $this->Lobby_model->get_contest_detail($post_data);
        // Apply PromoCode 
        $this->entry_fee = $contest['entry_fee'];
        $this->contest_unique_id = $contest['contest_unique_id'];
        $this->currency_type = $contest['currency_type'];
        $is_promo_code = 0;
        if($this->currency_type != "2"){
            $this->apply_contest_promo_code($post_data);
            $is_promo_code = 1;
        }
        $is_valid = $this->validation_for_join_game($contest);
        if ($is_valid) {
            if ($is_valid || $contest['entry_fee'] == 0) {
                $joined = $this->Lobby_model->join_game($contest);
                $joined_status = 1;
                if (isset($joined['joined_count']) && $contest['entry_fee'] > 0) {
                    $cash_type = "2";
                    if($this->currency_type == "2"){
                        $cash_type = "3";
                    }
                    $withdraw = array();
                    $withdraw["source"] = 500;
                    $withdraw["source_id"] = $joined['user_contest_id'];
                    $withdraw["reason"] = FANTASY_CONTEST_NOTI1;
                    $withdraw["cash_type"] = $cash_type;
                    $withdraw["user_id"] = $this->user_id;
                    $withdraw["amount"] = $this->entry_fee;
                    $withdraw["currency_type"] = $this->currency_type;
                    $withdraw["max_bonus_allowed"] = $contest['max_bonus_allowed'];
                    $withdraw["site_rake"] = $contest['site_rake'];
                    $withdraw["reference_id"] = $contest['contest_id'];
                    $withdraw["is_promo_code"] = $is_promo_code;
                    $withdraw["custom_data"] = array("match"=>$contest['collection_name'],"over"=>$contest['overs'],"contest"=>$contest['contest_name'],"inning"=>$contest['inning']);
                    $this->load->model("user/User_model");
                    $join_result = $this->User_model->withdraw($withdraw);
                    if (empty($join_result) || $join_result['result'] == 0) {
                        //remove user joined entry
                        $this->Lobby_model->remove_joined_game($contest, $joined['user_contest_id']);

                        $joined_status = 0;
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = isset($join_result['message']) ? $join_result['message'] : $this->contest_lang['problem_while_join_game'];
                        $this->api_response();
                    }
                    //update contest promo code earning status
                    if($this->currency_type != "2" && !empty($post_data['promo_code'])){
                        $promo_code_detail = $this->User_model->check_promo_code_details($post_data['promo_code']);
                        $where_condition = array("contest_unique_id" => $contest['contest_unique_id'], "user_id" => $this->user_id, "promo_code_id" => $promo_code_detail['promo_code_id'], "lmc_id" => 0);
                        // $this->load->model("user/User_model");
                        $earn_info = $this->User_model->get_user_promo_code_earn_info($where_condition);
                        if (!empty($earn_info) && $earn_info["is_processed"] == 0) {
                            $code_arr = array("is_processed" => "1", "order_id"=>$join_result['order_id'], "lmc_id" => $joined['user_contest_id']);
                            $this->User_model->update_promo_code_earning_details($code_arr, $earn_info["promo_code_earning_id"]);
                        }
                    }   
                }
                //join game notification
                if (isset($joined['joined_count']) && $joined_status == 1) {
                    //create auto recurring contest
                    if (isset($joined['joined_count']) && $joined['joined_count'] == $contest['size'] && $contest['is_auto_recurring'] == '1') {
                        $this->load->helper('queue_helper');
                        $contest_queue = array("action" => "auto_recurring", "data" => array("contest_unique_id" => $contest['contest_unique_id']));
                        add_data_in_queue($contest_queue, 'lf_contest');
                    }

                    $input = array(
                        'contest_name' => $contest['contest_name'],
                        'contest_unique_id' => $contest['contest_unique_id'],
                        'contest_id' => $contest['contest_id'],
                        'entry_fee' => $contest['entry_fee'],
                        'prize_pool' => $contest['prize_pool'],
                        'prize_type' => $contest['prize_type'],
                        'currency_type' => $contest['currency_type'],
                        'prize_distibution_detail' => json_decode($contest['prize_detail'],TRUE),
                        'season_scheduled_date' => $contest["season_scheduled_date"],
                        "collection_name" => (!empty($contest['collection_name'])) ? $contest['collection_name'] : $contest['contest_name'],
                        'inning' => $contest['inning'],
                        'over' => $contest['overs']
                    );

                    $notify_data = array();
                    $notify_data["notification_type"] = 620; //JoinGame, 
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
                    $notify_data["subject"] = "Your Contest Joining is Confirmed!";
                    $this->load->model('user/User_nosql_model');
                    $this->User_nosql_model->send_notification($notify_data);

                    //delete user balance data
                    $user_balance_cache_key = 'user_balance_' . $this->user_id;
                    $this->delete_cache_data($user_balance_cache_key);

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
     * used for validate contest promo code
     * @param array $data
     * @return array
     */
    public function apply_contest_promo_code($data) {
        $final_entry_fee = $this->entry_fee;
        if (isset($data['promo_code']) && $data['promo_code'] != "" && !empty($data['promo_code'])) {
            $this->load->model("user/User_model");
            $promo_code_detail = $this->User_model->check_promo_code_details($data['promo_code']);
            if ($promo_code_detail) {
                $used_count = $this->User_model->get_promo_used_count($promo_code_detail['promo_code_id']);
                if ( isset($promo_code_detail['max_usage_limit']) && $promo_code_detail['max_usage_limit'] != "0")
                {
                    if ($used_count['total_used'] >= $promo_code_detail['max_usage_limit'])
                    {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $this->contest_lang["max_usage_limit_code"];
                        $this->api_response();
                    }
                }
                if ($promo_code_detail['type'] != CONTEST_JOIN_TYPE) {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->contest_lang["invalid_promo_code"];
                    $this->api_response();
                } else if ($promo_code_detail['total_used'] >= $promo_code_detail['per_user_allowed']) {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->contest_lang["allowed_limit_exceed"];
                    $this->api_response();
                }

                if (isset($promo_code_detail['contest_unique_id']) && $promo_code_detail['contest_unique_id'] != "0")
                {
                    if ($promo_code_detail['contest_unique_id'] != $this->contest_unique_id)
                    {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $this->contest_lang["invalid_promo_code"];
                        $this->api_response();
                    }
                }

                if ($promo_code_detail['value_type'] == "1") {
                    $total_discount = ($this->entry_fee * $promo_code_detail['discount']) / 100;
                    if ($total_discount > $promo_code_detail['benefit_cap']) {
                        $total_discount = $promo_code_detail['benefit_cap'];
                    }
                } else {
                    $total_discount = $promo_code_detail['discount'];
                }

                $promo_code_id = $promo_code_detail['promo_code_id'];
                $final_entry_fee = $this->entry_fee - $total_discount;
                $final_entry_fee = truncate_number($final_entry_fee);
                if ($final_entry_fee < 0) {
                    $final_entry_fee = 0;
                }
                $this->entry_fee = $final_entry_fee;

                //Update promoce earning
                $config['promo_code_id'] = $promo_code_id;
                $config['source'] = 1;
                $config['source_id'] = $this->contest_unique_id;
                $config['user_id'] = $this->user_id;
                $config['amount_received'] = $total_discount;

                $where_condition = array("promo_code_id" => $promo_code_id, "user_id" => $this->user_id, "contest_unique_id" => $this->contest_unique_id, "lmc_id" => 0);
                $earn_info = $this->User_model->get_user_promo_code_earn_info($where_condition);
                if (!empty($earn_info) && $earn_info["is_processed"] == 0) {
                    $code_arr = array("amount_received" => $total_discount);
                    $this->User_model->update_promo_code_earning_details($code_arr, $earn_info["promo_code_earning_id"]);
                } else if (empty($earn_info)) {
                    $promo_code = array();
                    $promo_code['promo_code_id']        = $promo_code_id;
                    $promo_code['contest_unique_id']    = $this->contest_unique_id;
                    $promo_code['user_id']              = $this->user_id;
                    $promo_code['order_id']             = 0;
                    $promo_code['amount_received']      = $total_discount;
                    $promo_code['added_date']           = format_date();
                    $promo_code['lmc_id']               = 0;
                    $this->User_model->save_promo_code_earning_details($promo_code);
                }
                $this->promocodeid = $promo_code_detail['promo_code_id'];
            } else {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->contest_lang["promo_code_exp_used"];
                $this->api_response();
            }
        }
    }

    /**
     * used for validate contest join data
     * @param array $contest
     * @return array
     */
    public function validation_for_join_game($contest) {
        if (empty($contest)) {
            $this->message = $this->contest_lang['invalid_contest'];
            return 0;
        }

        $this->load->model("user/User_model");
        $balance_arr = $this->User_model->get_user_balance($this->user_id);
        if(empty($balance_arr)){
            $this->message = $this->contest_lang['not_enough_balance'];
            return 0;
        }
        
        $current_date = format_date();
        //check for match schedule date
        if ($contest['over_status'] != "0") {
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

        //check for multi lineup
        if ($contest['is_joined'] == 1) {
            $this->message = $this->contest_lang['you_already_joined_to_max_limit'];
            return 0;
        }
        
        if ($this->entry_fee == '0') {
            return 1;
        }

        $this->user_bonus_bal = $balance_arr['bonus_amount'];
        $this->user_bal = $balance_arr['real_amount'];
        $this->winning_bal = $balance_arr['winning_amount'];
        $this->point_balance = $balance_arr['point_balance'];
        
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
            $allow_self_exclusion = isset($this->app_config['allow_self_exclusion'])?$this->app_config['allow_self_exclusion']['key_value']:0;
            if($allow_self_exclusion) {
                $custom_data = $this->app_config['allow_self_exclusion']['custom_data'];
                $default_max_limit = $custom_data['default_limit'];
                $contest_ids = $this->Lobby_model->user_join_contest_ids($this->user_id);
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

    public function get_my_lobby_fixtures_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $this->load->model("lobby/Lobby_model");
        $result = $this->Lobby_model->get_my_joined_fixtures($post_data);
        //echo "<pre>";print_r($result);die;
        $fixture_list = array();
        if(!empty($result)){
            $game_keys = array_column($result,"game_key");
            $post_data['game_keys'] = array_unique($game_keys);
            $match_list = $this->Lobby_model->get_season_detail_by_ids($post_data);
            $match_list = array_column($match_list, NULL, "season_game_uid");
            //echo "<pre>";print_r($match_list);die;
            foreach($result as $value){
                $match_info = $match_list[$value['season_game_uid']];
                unset($match_info['dm']);
                unset($match_info['dmsg']);
                unset($match_info['cmsg']);
                unset($match_info['sa']);
                unset($match_info['pin']);
                unset($value['game_key']);
                unset($value['tmp']);
                $value['timer_date'] = strtotime($value['timer_date']);
                $match_info['game_starts_in'] = (strtotime($match_info['season_scheduled_date']))*1000;
                $fixture_list[] = array_merge($value,$match_info);
            } 
        }
        $this->api_response_arry['data'] = $fixture_list;
        $this->api_response();
    }

    /**
     * used for get user joined match list
     * @param int $status
     * @param int $sports_id
     * @return array
     */
    public function get_user_joined_fixture_by_status_post() {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        $this->form_validation->set_rules('status', $this->lang->line('match_status'), 'trim|required|callback_check_collection_status');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $status = $post_data['status'];
        $this->load->model("lobby/Lobby_model");
        $current_date = format_date();
        $result = $this->Lobby_model->get_user_joined_fixtures($post_data);
        $fixture_list = array();
        if(!empty($result)) 
        {
            $game_keys = array_column($result,"game_key");
            $post_data['game_keys'] = array_unique($game_keys);
            $match_list = $this->Lobby_model->get_season_detail_by_ids($post_data);
            $match_list = array_column($match_list, NULL, "season_game_uid");
            //echo "<pre>";print_r($match_list);die;
            foreach($result as $value){
                $match_info = $match_list[$value['season_game_uid']];
                unset($match_info['pin']);
                $match_info['game_starts_in'] = (strtotime($match_info['season_scheduled_date']))*1000;
                if(!isset($fixture_list[$value['game_key']]) || empty($fixture_list[$value['game_key']])){
                    if($match_info['is_live_score'] == "1"){
                        $match_info['score_data'] = json_decode($match_info['score_data'],TRUE);
                    }else{
                        $match_info['score_data'] = array();
                    }
                    $match_info['contest_count'] = 0;
                    $match_info['won_amt'] = $match_info['won_bonus'] = $match_info['won_coins'] = 0;
                    $match_info['won_marchandise'] = '';
                    $fixture_list[$value['game_key']] = $match_info;
                }
                $fixture_list[$value['game_key']]['contest_count'] = $fixture_list[$value['game_key']]['contest_count'] + $value['contest_count'];
                $fixture_list[$value['game_key']]['won_amt'] = $fixture_list[$value['game_key']]['won_amt'] + $value['won_amt'];
                $fixture_list[$value['game_key']]['won_bonus'] = $fixture_list[$value['game_key']]['won_bonus'] + $value['won_bonus'];
                $fixture_list[$value['game_key']]['won_coins'] = $fixture_list[$value['game_key']]['won_coins'] + $value['won_coins'];
                if($value['won_marchandise'] != ""){
                    $fixture_list[$value['game_key']]['won_marchandise']= $fixture_list[$value['game_key']]['won_marchandise'].",".$value['won_marchandise'];
                }
                $fixture_list[$value['game_key']]['won_marchandise'] = trim($fixture_list[$value['game_key']]['won_marchandise'],",");
                $fixture_list[$value['game_key']]["game"][] = array("collection_id"=>$value['collection_id'],"inning"=>$value['inning'],"over"=>$value['overs'],"total_score"=>$value['total_score'],"status"=>$value['status']);
            }
            $fixture_list = array_values($fixture_list);
        }

        $this->api_response_arry['data'] = $fixture_list;
        $this->api_response();
    }

    /**
     * used for get user joined contest list
     * @param int $status
     * @param int $collection_id
     * @param int $sports_id
     * @return array
     */
    public function get_user_contest_by_status_post() {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('status', $this->lang->line('match_status'), 'trim|required|callback_check_collection_status');
        if(isset($post_data['page_type']) && $post_data['page_type']=='1'){
            if(empty($post_data['collection_id'])) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = "collection id field is required.";
                $this->api_response();
            }
        }else{
            $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        }
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $status = $post_data['status'];
        $sports_id = $post_data['sports_id'];
        $collection_id = $post_data['collection_id'];

        $this->load->model("lobby/Lobby_model");
        $contest_list = $this->Lobby_model->get_user_fixture_contest($collection_id, $status, $sports_id);
        $users = array();
        if(!empty($contest_list))
        {
            $user_ids = array_unique(array_column($contest_list,'contest_creater'));
            $this->load->model("user/User_model");
            $users = $this->User_model->get_users_by_ids($user_ids);
            if(!empty($users))
            {
                $users = array_column($users,NULL,'user_id');
            }
        }
        foreach ($contest_list as &$contest) {
            $contest['is_confirmed'] = 0;
            if($contest['guaranteed_prize'] != 2 && $contest['size'] > $contest['minimum_size'] && $contest['entry_fee'] > 0){
                $contest['is_confirmed'] = 1;
            }
            $contest['prize_detail'] = json_decode($contest["prize_detail"]);
            
            $is_winner = $contest["is_winner"];
            if ($status == 1 && !empty($contest["prize_detail"])) {//LIVE
                $prize_details = json_decode($contest["prize_detail"], TRUE);
                $last_element = end($prize_details);

                if (!empty($last_element['max']) && $last_element['max'] >= $contest["game_rank"]) {
                    $is_winner = 1;
                }
            }

            if(isset($contest["prize_data"]) && $contest["prize_data"] != 'null'){
                $contest["prize_data"] = json_decode($contest["prize_data"], TRUE);
            }

            if(isset($users[$contest['contest_creater']]))
            {
                $contest['user_name'] =$users[$contest['contest_creater']]['user_name'];
                $contest['image'] =$users[$contest['contest_creater']]['image'];
            }
            $contest['timer_date'] = strtotime($contest['timer_date']);
        }
        $this->api_response_arry['data'] = $contest_list;
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
        $this->load->model("lobby/Lobby_model");
        $contest = $this->Lobby_model->get_contest_detail($post_data);
        if (empty($contest)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['contest_not_found'];
            $this->api_response();
        }
        //check for existence
        $exist = $this->Lobby_model->get_single_row("contest_id,code", INVITE, array("contest_id" => $contest['contest_id'], "season_type" => $season_type, "user_id" => 0));
        if (empty($exist)) {
            $code = $this->Lobby_model->_generate_contest_code();
            $invite_data = array(
                "contest_id" => $contest['contest_id'],
                "contest_unique_id" => $contest["contest_unique_id"],
                "invite_from" => $this->user_id,
                "code" => $code,
                "season_type" => $season_type,
                "expire_date" => date(DATE_FORMAT, strtotime($contest['season_scheduled_date'])),
                "created_date" => format_date(),
                "status" => 1
            );
            $this->Lobby_model->save_invites(array($invite_data));
        } else {
            $code = $exist['code'];
        }

        $this->api_response_arry['data'] = $code;
        $this->api_response();
    }

    /**
     * used for have a league code
     * @param int $join_code
     * @param int $contest_id
     * @return array
     */
    public function check_eligibility_for_contest_post() 
    {
        $this->form_validation->set_rules('join_code', $this->lang->line('join_code'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $join_code = $post_data['join_code'];
        $this->load->model("lobby/Lobby_model");
        $where = "(code ='{$join_code}' AND email= '{$this->email}' ) OR (code='{$join_code}' AND user_id = 0 ) AND season_type = 1 ";
        $row = $this->Lobby_model->get_single_row("contest_id", INVITE, $where);
        //echo "<pre>";print_r($row);die;
        if (empty($row)) 
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest_code'];
            $this->api_response();
        }

        $contest_id = $row['contest_id'];
        $post_data['contest_id'] = $contest_id;
        $contest = $this->Lobby_model->get_contest_detail($post_data);
        
        $contest['game_starts_in'] = (strtotime($contest['season_scheduled_date'])) * 1000;
        if (!empty($contest['prize_detail'])) {
            $contest['prize_detail'] = json_decode($contest['prize_detail'], TRUE);
            if ($contest['prize_detail'] == null) {
                $contest['prize_detail'] = array();
            }
        }

        $contest['game_type'] = "livefantasy";
        $contest['user_join_count'] = 0;
        $this->api_response_arry['data'] = $contest;
        $this->api_response();    
    }

    /**
     * Used for get contest joined users list by rank
     * @param int $contest_id
     * @return array
     */
    public function get_contest_leaderboard_post()
    {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $page_type = isset($post_data['type']) ? $post_data['type'] : "0";
        $this->load->model("lobby/Lobby_model");
        $result = $this->Lobby_model->get_contest_leaderboard($post_data);
        //echo "<pre>";print_r($result);die;
        $return_arr = array();
        if($page_type != 1){
            $return_arr = array("own" => array(),"top_three" => array(),"other_list" => array());
        }
        $top_three = array();
        $own_record = array();
        $other_record = array();
        if(!empty($result))
        {
            $this->load->model('user/User_model');
            $user_ids = array_unique(array_column($result,'user_id'));
            $user_details = $this->User_model->get_users_by_ids($user_ids);
            $user_images = array();
            if(!empty($user_details))
            {
                $user_images = array_column($user_details,'image','user_id');
                $user_details = array_column($user_details,'user_name','user_id');
            }

            $i = 0;
            foreach($result as $row)
            {
                unset($row['user_team']);
                if(isset($user_details[$row['user_id']]))
                {
                    $row['user_name'] = $user_details[$row['user_id']];
                    $row['image'] = $user_images[$row['user_id']];
                }
                if(!empty($row['prize_data']))
                {
                    $row['prize_data'] = json_decode($row['prize_data'],TRUE);
                }

                if($page_type == 1){
                    $return_arr[] = $row;
                }else{
                    if($row['user_id'] == $this->user_id)
                    {
                        $return_arr['own'][] = $row;
                    }
                    if($i < 3 && $row['game_rank'] <= 3){
                        $return_arr['top_three'][] = $row;
                        $i++;
                    }else if($row['user_id'] != $this->user_id){
                        $return_arr['other_list'][] = $row;
                    }
                }
                
            }
        }
        if(!empty($return_arr['top_three'])){
            $keys = array_column($return_arr['top_three'], 'game_rank');
            array_multisort($keys, SORT_ASC, $return_arr['top_three']);
        }
        $this->api_response_arry['data'] = $return_arr;
        $this->api_response();
    }

    /**
     * used for get team details with score
     * @param int $lineup_master_contest_id
     * @return array
     */
    public function get_linpeup_with_score_post() {
        $this->form_validation->set_rules('user_contest_id', 'user contest id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $user_contest_id = $post_data["user_contest_id"];
        $this->load->model("lobby/Lobby_model");
        $result = $this->Lobby_model->get_linpeup_with_score($user_contest_id);
        
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
     * used for get over data
     * @param int $collection_id
     * @return array
     */
    public function get_match_over_ball_post() {
        $this->form_validation->set_rules('collection_id', 'user contest id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $collection_id = $post_data["collection_id"];
        $this->load->model("lobby/Lobby_model");
        $collection = $this->Lobby_model->get_single_row("*,CONVERT(SUBSTRING(inn_over,3), SIGNED INTEGER) as overs",COLLECTION, array("collection_id" => $collection_id));
        if(empty($collection)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }
        $user_team = $this->Lobby_model->get_single_row("*",USER_TEAM, array("collection_id" => $collection_id,"user_id"=>$this->user_id));
        if(empty($user_team)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('user_team_not_found');
            $this->api_response();
        }
        $user_team_id = $user_team['user_team_id'];
        $overs = $collection['overs'];
        $result = $this->Lobby_model->get_match_over_ball($collection_id);
        $user_predict = $this->Lobby_model->get_user_predict_data($user_team_id);
        $user_predict = array_column($user_predict,NULL,"market_id");
        $odds_arr = array_column($result,NULL,"ball");
        //echo "<pre>";print_r($odds_arr);die;
        $tmp_arr = array("market_id"=>"0","over_ball"=>"","result"=>"0","score"=>"0","predict_id"=>"","is_correct"=>"","points"=>"","btext"=>"0");
        $ball_arr = array($overs."_10"=>array(),$overs."_20"=>array(),$overs."_30"=>array(),$overs."_40"=>array(),$overs."_50"=>array(),$overs."_60"=>array());
        $final_odds_arr = array();
        foreach($odds_arr as $key=>$val){
            if($val['ball'] == 0 || $val['ball'] == ""){
                continue;
            }
            $val['predict_id'] = "";
            $val['is_correct'] = "";
            $val['points'] = "";
            $val['btext'] = $val['score'];
            if(isset($user_predict[$val["market_id"]])){
                $user_arr = $user_predict[$val["market_id"]];
                $val['predict_id'] = $user_arr['predict_id'];
                $val['is_correct'] = $user_arr['is_correct'];
                $val['points'] = $user_arr['points'];
            }
            if($val['result'] == "7" && isset($val['extra_score_id']) && $val['extra_score_id'] > 0){
                $val['btext'] = get_extra_ball_name($val['extra_score_id']);
            }else if($val['result'] == "6"){
                $val['btext'] = "W";
            }
            unset($val['ball']);
            unset($val['extra_score_id']);
            unset($val['collection_id']);
            unset($val['season_game_uid']);
            unset($val['league_id']);
            unset($val['inn_over']);
            $val['over_ball'] = trim_trailing_zeroes($val['over_ball']);
            $final_odds_arr[$key] = $val;
        }
        $d_ball = array_keys($ball_arr);
        $o_ball = array_keys($final_odds_arr);
        $final_ball = array_diff($d_ball,$o_ball);
        if(!empty($final_ball)){
            foreach($final_ball as $over_ball){
                $over_ball = str_replace("_",".",$over_ball);
                $tmp_ball = $tmp_arr;
                $tmp_ball['over_ball'] = trim_trailing_zeroes($over_ball);
                $final_odds_arr[$over_ball] = $tmp_ball;
            }
        }
        $over_ball = array_values($final_odds_arr);
        //echo "<pre>";print_r($ball_arr);die;
        $return_arr = array("collection_id"=>$collection['collection_id'],"season_game_uid"=>$collection['season_game_uid'],"league_id"=>$collection['league_id'],"inn_over"=>$collection['inn_over'],"user_team_id"=>$user_team_id);
        $return_arr['over_ball'] = $over_ball;
        $this->api_response_arry['data'] = $return_arr;
        $this->api_response();
    }

    /**
     * used for save user predict answer
     * @param int $collection_id
     * @param int $user_team_id
     * @param int $odds_id
     * @return array
     */
    public function save_user_prediction_post() {
        $this->form_validation->set_rules('collection_id', 'user contest id', 'trim|required');
        $this->form_validation->set_rules('user_team_id', 'user team id', 'trim|required');
        $this->form_validation->set_rules('market_id', 'market id', 'trim|required');
        $this->form_validation->set_rules('odds_id', 'odds id', 'trim|required');
        $this->form_validation->set_rules('over_ball', 'over ball', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $collection_id = $post_data["collection_id"];
        $user_team_id = $post_data["user_team_id"];
        $market_id = $post_data["market_id"];
        $odds_id = $post_data["odds_id"];
        $second_odds_id = isset($post_data["second_odds_id"]) ? $post_data["second_odds_id"] : "0";
        $over_ball = $post_data["over_ball"];
        $this->load->model("lobby/Lobby_model");
        $collection = $this->Lobby_model->get_single_row("*",COLLECTION, array("collection_id" => $collection_id));
        $market_odds = $this->Lobby_model->get_single_row("*",MARKET_ODDS, array("market_id" => $market_id));
        $deadline_limit = $collection['over_time'] + 3;
        $deadline_time = date('Y-m-d H:i:s', strtotime('-'.$deadline_limit.' seconds', strtotime($current_date)));
        if(empty($market_odds)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("inavlid_market_id");
            $this->api_response();
        }else if(strtotime($market_odds['market_date']) < strtotime($deadline_time)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("predict_time_over");
            $this->api_response();
        }

        if($second_odds_id != "" && $odds_id == $second_odds_id){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "First and second odds id should be different.";
            $this->api_response();
        }
        $answer_time = strtotime($current_date) - strtotime($market_odds['market_date']);
        $answer_time = $answer_time - 3;
        $dtime = $deadline_limit - 3;
        if($answer_time < 0){
            $answer_time = $dtime;
        }else if($answer_time > $dtime){
            $answer_time = $dtime;
        }
        $data_arr = array();
        $data_arr['user_team_id'] = $user_team_id;
        $data_arr['market_id'] = $market_id;
        $data_arr['odds_id'] = $odds_id;
        if($second_odds_id != ""){
            $data_arr['second_odds_id'] = $second_odds_id;
        }else{
            $data_arr['second_odds_id'] = 0;
        }
        $data_arr['over_ball'] = $over_ball;
        $data_arr['answer_time'] = $answer_time;
        $data_arr['date_modified'] = $current_date;
        if(isset($post_data['predict_id']) && $post_data['predict_id'] != ""){
            $predict_id = $post_data['predict_id'];
            $this->Lobby_model->update(USER_PREDICTION,$data_arr,array("predict_id"=>$predict_id));
        }else{
            $check_prediction = $this->Lobby_model->get_single_row("*",USER_PREDICTION, array("market_id" => $market_id,"user_team_id"=>$user_team_id));
            if(!empty($check_prediction)){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line("already_predict");
                $this->api_response();
            }

            $data_arr['date_added'] = $current_date;
            $predict_id = $this->Lobby_model->save_record(USER_PREDICTION,$data_arr);
        }
        if($predict_id){
            $return = array();
            $return['predict_id'] = $predict_id;
            $return['market_id'] = $market_id;
            $return['over_ball'] = trim_trailing_zeroes($over_ball);
            $return['result'] = "0";
            $return['score'] = "0";
            $return['is_correct'] = "";
            $return['points'] = "";
            $return['btext'] = "0";
            $this->api_response_arry['data'] = $return;
            $this->api_response_arry['message'] = "Your answer saved successfully.";
            $this->api_response();
        }
    }

    /**
     * used for get over ball odds list
     * @param int $collection_id
     * @param int $user_team_id
     * @param int $odds_id
     * @return array
     */
    public function get_over_ball_odds_post() {
        $this->form_validation->set_rules('collection_id', 'user contest id', 'trim|required');
        $this->form_validation->set_rules('user_team_id', 'user team id', 'trim|required');
        $this->form_validation->set_rules('market_id', 'market id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $collection_id = $post_data["collection_id"];
        $user_team_id = $post_data["user_team_id"];
        $market_id = $post_data["market_id"];
        $this->load->model("lobby/Lobby_model");
        $odds_details = $this->Lobby_model->get_match_over_ball_odds($post_data);
        
        $master_odds = $this->Lobby_model->get_master_odds();
        $master_odds = array_column($master_odds,NULL,'odds_id');
        $odds_point = json_decode($odds_details['market_odds']);
        $final_odds = array();
        if(!empty($odds_point)){
            foreach($odds_point as $id=>$point){
                $odds_arr = isset($master_odds[$id]) ? $master_odds[$id] : array();
                if(!empty($odds_arr)){
                    $tmp = array();
                    $tmp['odds_id'] = $id;
                    $tmp['name'] = $odds_arr['name'];
                    $tmp['point'] = $point;
                    $final_odds[] = $tmp;
                }
            }
        }
        $odds_details['market_odds'] = $final_odds;
        $odds_details['collection_id'] = $collection_id;
        $odds_details['over_ball'] = trim_trailing_zeroes($odds_details['over_ball']);
        $odds_details['time'] = strtotime(format_date());
        $odds_details['market_date_time'] = strtotime($odds_details['market_date']);
        //echo "<pre>";print_r($odds_details);die;
        $this->api_response_arry['data'] = $odds_details;
        $this->api_response();
    }

    /**
     * used for get over ball odds list
     * @param int $collection_id
     * @param int $user_team_id
     * @param int $odds_id
     * @return array
     */
    public function get_match_players_post() {
        $this->form_validation->set_rules('collection_id', 'user contest id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $collection_id = $post_data["collection_id"];
        $this->load->model("lobby/Lobby_model");
        $collection = $this->Lobby_model->get_single_row("collection_id,league_id,season_game_uid", COLLECTION, array("collection_id" => $collection_id));
        if(empty($collection)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }
        $players = $this->Lobby_model->get_match_players($collection['season_game_uid'],$collection['league_id']);
        //echo "<pre>";print_r($players);die;
        $this->api_response_arry['data'] = $players;
        $this->api_response();
    }

    /**
     * used for get user match stats data
     * @param int $collection_id
     * @return array
     */
    public function get_user_match_stats_post() {
        $this->form_validation->set_rules('collection_id', 'user contest id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $collection_id = $post_data["collection_id"];
        $this->load->model("lobby/Lobby_model");
        
        $collection_cache_key = "lf_cm_".$collection_id;
        $collection = $this->get_cache_data($collection_cache_key);
        if(!$collection){
            $collection = $this->Lobby_model->get_single_row("collection_id,league_id,season_game_uid,collection_name,season_scheduled_date,status,inn_over,CONVERT(SUBSTRING(inn_over,1,1), SIGNED INTEGER) as inning,CONVERT(SUBSTRING(inn_over,3), SIGNED INTEGER) as overs",COLLECTION, array("collection_id" => $collection_id));
            $this->set_cache_data($collection_cache_key,$collection,REDIS_5_MINUTE);
        }
        
        if(empty($collection)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }
        $user_team = $this->Lobby_model->get_single_row("*",USER_TEAM, array("collection_id" => $collection_id,"user_id"=>$this->user_id));
        if(empty($user_team)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('user_team_not_found');
            $this->api_response();
        }
        $user_team_id = $user_team['user_team_id'];
        $overs = $collection['overs'];
        
        $over_ball_cache_key = "over_ball_".$collection_id;
        $result = $this->get_cache_data($over_ball_cache_key);
        if(!$result){
            $result = $this->Lobby_model->get_match_over_ball($collection_id);
            $this->set_cache_data($over_ball_cache_key,$result,REDIS_5_MINUTE);
        }
        $user_predict = $this->Lobby_model->get_user_predict_data($user_team_id);
        $user_predict = array_column($user_predict,NULL,"market_id");
        $odds_arr = array_column($result,NULL,"ball");
        //echo "<pre>";print_r($odds_arr);die;
        $final_odds_arr = array();
        foreach($odds_arr as $key=>$val){
            if($val['ball'] == 0 || $val['ball'] == ""){
                continue;
            }
            $val['predict_id'] = "";
            $val['is_correct'] = "";
            $val['points'] = "";
            $val['btext'] = $val['score'];
            if(isset($user_predict[$val["market_id"]])){
                $user_arr = $user_predict[$val["market_id"]];
                $val['predict_id'] = $user_arr['predict_id'];
                $val['is_correct'] = $user_arr['is_correct'];
                $val['points'] = $user_arr['points'];
            }
            if($val['result'] == "7" && isset($val['extra_score_id']) && $val['extra_score_id'] > 0){
                $val['btext'] = get_extra_ball_name($val['extra_score_id']);
            }else if($val['result'] == "6"){
                $val['btext'] = "W";
            }
            unset($val['ball']);
            unset($val['extra_score_id']);
            unset($val['collection_id']);
            unset($val['season_game_uid']);
            unset($val['league_id']);
            unset($val['inn_over']);
            $val['over_ball'] = trim_trailing_zeroes($val['over_ball']);
            $final_odds_arr[$key] = $val;
        }
        $over_ball = array_values($final_odds_arr);

        $contest_list = $this->Lobby_model->get_user_match_joined_contest($collection_id);
        $prize_status = 1;
        $game_status = array_column($contest_list,"status");
        if(in_array("0",$game_status) || in_array("2",$game_status)){
            $prize_status = 0;
        }
        $collection['user_team_id'] = $user_team['user_team_id'];
        $collection['total_score'] = $user_team['total_score'];
        $collection['over_ball'] = $over_ball;
        $collection['contest_list'] = $contest_list;
        $collection['prize_status'] = $prize_status;
        $this->api_response_arry['data'] = $collection;
        $this->api_response();
    }

    /**
     * used for get user match stats data
     * @param int $collection_id
     * @return array
     */
    public function get_next_over_post() {
        $this->form_validation->set_rules('collection_id', 'user contest id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $collection_id = $post_data["collection_id"];
        $cache_key = "collect_next_over_".$collection_id;
        $next_over = $this->get_cache_data($cache_key);
        if(!$next_over){
            $this->load->model("lobby/Lobby_model");
            $collection = $this->Lobby_model->get_single_row("*",COLLECTION, array("collection_id" => $collection_id));
            if(empty($collection)){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
                $this->api_response();
            }
            $next_over = $this->Lobby_model->get_next_over($collection);
            if(!empty($next_over)){
                unset($next_over['inn_over_val']);
            }
            $this->set_cache_data($cache_key,$next_over,REDIS_5_MINUTE);
        }
        
        $this->api_response_arry['data'] = $next_over;
        $this->api_response();
    }

    /**
     * used for validate contest promo code
     * @param int $contest_id
     * @param string $promo_code
     * @return array
     */
    public function validate_contest_promo_code_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        $this->form_validation->set_rules('promo_code', $this->lang->line('promo_code'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $contest_id = $post_data['contest_id'];
        $this->load->model("lobby/Lobby_model");
        $contest_info = $this->Lobby_model->get_contest_detail($post_data);
        if (empty($contest_info)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }

        $this->load->model("user/User_model");
        $code_detail = $this->User_model->check_promo_code_details($post_data['promo_code']);
        $used_count = $this->User_model->get_promo_used_count($code_detail['promo_code_id']);
        if (empty($code_detail)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang["invalid_promo_code"];
            $this->api_response();
        } else if ($code_detail['type'] != CONTEST_JOIN_TYPE) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang["invalid_promo_code"];
            $this->api_response();
        } else if ($code_detail['total_used'] >= $code_detail['per_user_allowed']) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang["exceed_promo_used_count"];
            $this->api_response();
        } else if ($code_detail['max_usage_limit'] != 0 && ($used_count['total_used'] >= $code_detail['max_usage_limit'])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang["max_usage_limit_code"];
            $this->api_response();
        } else {
                if (isset($code_detail['contest_unique_id']) && $code_detail['contest_unique_id'] != "0")
                {
                    if ($code_detail['contest_unique_id'] != $contest_info['contest_unique_id'])
                    {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $this->contest_lang["invalid_promo_code"];
                        $this->api_response();
                    }
                }
                if ($code_detail['value_type'] == "1") {
                    $bonus_amount = ($contest_info['entry_fee'] * $code_detail['discount']) / 100;
                    if ($bonus_amount > $code_detail['benefit_cap']) {
                        $bonus_amount = $code_detail['benefit_cap'];
                    }
                } else {
                    $bonus_amount = $code_detail['discount'];
                }

                $result_data = array('promo_code_id' => $code_detail['promo_code_id'], 'discount' => $code_detail['discount'], "amount" => $bonus_amount, "promo_code" => $code_detail['promo_code'], "cash_type" => $code_detail['cash_type'], "value_type" => $code_detail['value_type']);
                $this->api_response_arry['data'] = $result_data;
                $this->api_response();
            }
    }

    /**
     * used for get over data
     * @param int $collection_id
     * @return array
     */
    public function get_user_team_stats_post() {
        $this->form_validation->set_rules('user_team_id', 'user team id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $user_team_id = $post_data["user_team_id"];
        $this->load->model("lobby/Lobby_model");
        $user_predict = $this->Lobby_model->get_user_team_stats($user_team_id);
        if(empty($user_predict)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Inavlid team id.";
            $this->api_response();
        }
        $collection_id = $user_predict['0']['collection_id'];
        $result = $this->Lobby_model->get_match_over_ball($collection_id);
        $user_predict = array_column($user_predict,NULL,"market_id");
        $odds_arr = array_column($result,NULL,"ball");
        foreach($odds_arr as $key=>$val){
            if($val['ball'] == 0 || $val['ball'] == ""){
                continue;
            }
            $val['predict_id'] = "";
            $val['is_correct'] = "";
            $val['points'] = "";
            $val['btext'] = $val['score'];
            if(isset($user_predict[$val["market_id"]])){
                $user_arr = $user_predict[$val["market_id"]];
                $val['predict_id'] = $user_arr['predict_id'];
                $val['is_correct'] = $user_arr['is_correct'];
                $val['points'] = $user_arr['points'];
            }
            if($val['result'] == "7" && isset($val['extra_score_id']) && $val['extra_score_id'] > 0){
                $val['btext'] = get_extra_ball_name($val['extra_score_id']);
            }else if($val['result'] == "6"){
                $val['btext'] = "W";
            }
            unset($val['ball']);
            unset($val['extra_score_id']);
            unset($val['collection_id']);
            unset($val['season_game_uid']);
            unset($val['league_id']);
            unset($val['inn_over']);
            $val['over_ball'] = trim_trailing_zeroes($val['over_ball']);
            $final_odds_arr[$key] = $val;
        }
        $over_ball = array_values($final_odds_arr);
        //echo "<pre>";print_r($ball_arr);die;
        $this->api_response_arry['data'] = $over_ball;
        $this->api_response();
    }

    public function get_user_live_overs_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $this->load->model("lobby/Lobby_model");
        $result = $this->Lobby_model->get_user_live_overs($post_data);
        //echo "<pre>";print_r($result);die;
        $fixture_list = array();
        if(!empty($result)){
            $game_keys = array_column($result,"game_key");
            $post_data['game_keys'] = array_unique($game_keys);
            $match_list = $this->Lobby_model->get_season_detail_by_ids($post_data);
            $match_list = array_column($match_list, NULL, "season_game_uid");
            //echo "<pre>";print_r($match_list);die;
            foreach($result as $value){
                $match_info = $match_list[$value['season_game_uid']];
                unset($match_info['dm']);
                unset($match_info['dmsg']);
                unset($match_info['cmsg']);
                unset($match_info['sa']);
                unset($match_info['pin']);
                unset($match_info['score_data']);
                unset($match_info['is_live_score']);
                unset($match_info['match_status']);
                unset($value['game_key']);
                $fixture_list[] = array_merge($value,$match_info);
            } 
        }
        $this->api_response_arry['data'] = $fixture_list;
        $this->api_response();
    }
}