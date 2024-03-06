<?php
class Contest extends Common_Api_Controller {
    public function __construct() {
        parent::__construct();
        $this->contest_lang = $this->lang->line('contest');
    }

    /**
     * Used for get sports scoring rules
     * @param int $sports_id
     * @return array
     */
    public function get_scoring_master_data_post() {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $lang_key = "en";
        if ($this->lang_abbr) {
            $lang_key = $this->lang_abbr;
        }
        //sports scoring rules
        $scoring_rules_cache_key = $lang_key."_scoring_rules_".$sports_id;
        $scoring_rules = $this->get_cache_data($scoring_rules_cache_key);
        if (!$scoring_rules) {
            $input_score = array('sports_id' => $sports_id, "lang_key" => $lang_key);
            $this->load->model("contest/Contest_model");
            $scoring_rules = $this->Contest_model->get_scoring_rules_by_category_format($input_score);
            $this->set_cache_data($scoring_rules_cache_key, $scoring_rules, REDIS_30_DAYS);
        }

        if (!empty($scoring_rules)) {
            if($sports_id == CRICKET_SPORTS_ID) {
                foreach ($scoring_rules as $key => $item) {
                    $arr[$item['format']][$key] = $item;
                }
                ksort($arr, SORT_NUMERIC);
                foreach ($arr[1] as $arg) {
                    if (empty($one_day[$arg['master_scoring_category_id']])) {
                        $category_name = strtoupper(str_replace("_", " ", $arg['scoring_category_name']));
                        $one_day[$arg['master_scoring_category_id']] = array("name" => $category_name, "rules" => array());
                    }
                    $one_day[$arg['master_scoring_category_id']]['rules'][] = $arg;
                }

                foreach ($arr[2] as $arg) {
                    if (empty($test[$arg['master_scoring_category_id']])) {
                        $category_name = strtoupper(str_replace("_", " ", $arg['scoring_category_name']));
                        $test[$arg['master_scoring_category_id']] = array("name" => $category_name, "rules" => array());
                    }
                    $test[$arg['master_scoring_category_id']]['rules'][] = $arg;
                }

                foreach ($arr[3] as $arg) {
                    if (empty($tt[$arg['master_scoring_category_id']])) {
                        $category_name = strtoupper(str_replace("_", " ", $arg['scoring_category_name']));
                        $tt[$arg['master_scoring_category_id']] = array("name" => $category_name, "rules" => array());
                    }
                    $tt[$arg['master_scoring_category_id']]['rules'][] = $arg;
                }
                foreach ($arr[4] as $arg) {
                    if (empty($t10[$arg['master_scoring_category_id']])) {
                        $category_name = strtoupper(str_replace("_", " ", $arg['scoring_category_name']));
                        $t10[$arg['master_scoring_category_id']] = array("name" => $category_name, "rules" => array());
                    }
                    $t10[$arg['master_scoring_category_id']]['rules'][] = $arg;
                }
                $final_rules = array('one_day' => $one_day, 'test' => $test, 'tt' => $tt, 't10' => $t10);
            } else {
                $tmp_rules = array();
                foreach($scoring_rules as $row){
                    if(!isset($tmp_rules[$row['master_scoring_category_id']])){
                        $tmp_rules[$row['master_scoring_category_id']] = array('name' => ucfirst($row['scoring_category_name']), 'rules' => array());
                    }
                    $tmp_rules[$row['master_scoring_category_id']]['rules'][] = $row;
                }
                $final_rules = array_values($tmp_rules);
            }

            //for upload data on s3 bucket
            $this->push_s3_data_in_queue("scoring_master_data_".$sports_id."_".$lang_key, $final_rules);

            $this->api_response_arry['data'] = $final_rules;
            $this->api_response();
        }

        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }

    /**
     * Used for get contest details
     * @param int $contest_id
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
        $contest = $this->Contest_model->get_contest_detail($contest_id);
        if(empty($contest)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }
        if($contest['user_id'] != 0)
        {
            $this->load->model("user/User_model");
            $user_data = $this->User_model->get_users_by_ids($contest['user_id']);
            if (!empty($user_data))
            {
                unset($user_data['user_id']);
                unset($user_data['user_unique_id']);
                $contest['creator'] = $user_data;
            }
        }

        //contest matches list data in cache
        $season_ids = array_unique(explode(",",$contest['season_ids']));
        if($contest['is_tour_game'] == 1){
            $match_list = $this->Contest_model->get_tour_season_detail($season_ids);
            $contest['format'] = 0;
        }else{
            $match_list = $this->Contest_model->get_fixture_season_detail($season_ids);
            $contest['format'] = $match_list['0']['format'];
        }
        $contest['match'] = $match_list;

        //sports scoring rules with format
        $lang_key = "en";
        if ($this->lang_abbr) {
            $lang_key = $this->lang_abbr;
        }
        $scoring_rules_cache_key = $lang_key."_scoring_rules_".$contest['sports_id'] . "_".$contest['format'];
        $scoring_rules = $this->get_cache_data($scoring_rules_cache_key);
        if(!$scoring_rules) {
            $input_score = array('sports_id' => $contest['sports_id'], 'format' => $contest['format'], "lang_key" => $lang_key,"no_of_sets" => $contest['no_of_sets']);
            $this->load->model("contest/Contest_model");
            $scoring_rules = $this->Contest_model->get_scoring_rules_by_category_format($input_score);
            //set scoring rules in cache for 7 days
            $this->set_cache_data($scoring_rules_cache_key, $scoring_rules, REDIS_7_DAYS);
        }
        $contest['scoring_rules'] = $scoring_rules;
        $contest['merchandise'] = array();
        if(isset($contest['is_tie_breaker']) && $contest['is_tie_breaker'] == 1){
            $tmp_ids = array();
            $prize_detail = json_decode($contest['prize_distibution_detail'],TRUE);
            foreach($prize_detail as $prize){
                if(isset($prize['prize_type']) && $prize['prize_type'] == 3){
                    $tmp_ids[] = $prize['amount'];
                }
            }
            if(!empty($tmp_ids)){
                $this->load->model("contest/Contest_model");
                $merchandise_list = $this->Contest_model->get_merchandise_list($tmp_ids);
                $contest['merchandise'] = $merchandise_list;
            }
        }
        
        $contest['is_booster'] = '0';
        $allow_booster = isset($this->app_config['allow_booster'])?$this->app_config['allow_booster']['key_value']:0;
        if($allow_booster == "1"){
            $booster = $this->Contest_model->get_single_row("COUNT(id) as total",BOOSTER_COLLECTION,array("collection_master_id" => $contest['collection_master_id']));
            if(!empty($booster) && $booster['total'] > 0){
                $contest['is_booster'] = '1';
            }
        }
        $contest['salary_cap'] = SALARY_CAP;
        $contest['current_prize'] = json_decode($contest['current_prize']);
        if(!isset($contest['current_prize']) || empty($contest['current_prize'])){
            $tmp_contest = $contest;
            if($tmp_contest['total_user_joined'] < $contest['minimum_size']){
                $tmp_contest['total_user_joined'] = $contest['minimum_size'];
            }
            $contest['current_prize'] = reset_contest_prize_data($tmp_contest);
        }
        $contest['current_prize'] = json_encode($contest['current_prize']);

        $c_vc = array();
        $c_vc['c_point'] = CAPTAIN_POINT;
        $c_vc['vc_point'] = VICE_CAPTAIN_POINT;
        //dynamic team setting
        $setting = json_decode($contest['setting'],TRUE);
        $sports_id = $contest['sports_id'];
        if(!empty($setting)){
            if($setting['c'] == "0"){
                $c_vc['c_point'] = "0";
            }
            if($setting['vc'] == "0"){
                $c_vc['vc_point'] = "0";
            }
            $contest['team_player_count'] = $setting['team_player_count'];
            $contest['max_player_per_team'] = $setting['max_player_per_team'];
        }else{
            $sports_list = $this->get_sports_list();
            $sports_list = array_column($sports_list,NULL,"sports_id");
            $contest['team_player_count'] = $sports_list[$sports_id]['team_player_count'];
            $contest['max_player_per_team'] = $sports_list[$sports_id]['max_player_per_team'];
        }
        if(in_array($sports_id,[MOTORSPORT_SPORTS_ID,TENNIS_SPORTS_ID])){
            $c_vc['vc_point'] = 0;
        }
        $contest['c_vc'] = $c_vc;
        unset($contest['setting']);
        unset($contest['season_ids']);
        $this->api_response_arry['data'] = $contest;
        $this->api_response();
    }

    /**
     * Used for get public contest details
     * @param int $collection_master_id
     * @param int $contest_id
     * @return array
     */
    public function get_public_contest_post() {
        $this->form_validation->set_rules('contest_unique_id', $this->lang->line('contest_unique_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("contest/Contest_model");
        $contest = $this->Contest_model->get_single_row("contest_id,contest_unique_id", CONTEST, array("contest_unique_id" => $post_data['contest_unique_id']));
        if(empty($contest)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }
        $contest_id = $contest['contest_id'];
        $contest = $this->Contest_model->get_contest_detail($contest_id);
        //contest matches list data in cache
        $season_ids = array_unique(explode(",",$contest['season_ids']));
        if($contest['is_tour_game'] == 1){
            $match_list = $this->Contest_model->get_tour_season_detail($season_ids);
        }else{
            $match_list = $this->Contest_model->get_fixture_season_detail($season_ids);
        }
        $contest['match'] = $match_list;
        unset($contest['season_ids']);
        $contest['salary_cap'] = SALARY_CAP;
        $contest['current_prize'] = json_decode($contest['current_prize']);
        if(!isset($contest['current_prize']) || empty($contest['current_prize'])){
            $tmp_contest = $contest;
            if($tmp_contest['total_user_joined'] < $contest['minimum_size']){
                $tmp_contest['total_user_joined'] = $contest['minimum_size'];
            }
            $contest['current_prize'] = reset_contest_prize_data($tmp_contest);
        }
        $contest['current_prize'] = json_encode($contest['current_prize']);

        $c_vc = array();
        $c_vc['c_point'] = CAPTAIN_POINT;
        $c_vc['vc_point'] = VICE_CAPTAIN_POINT;
        //dynamic team setting
        $setting = json_decode($contest['setting'],TRUE);
        $sports_id = $contest['sports_id'];
        if(!empty($setting)){
            if($setting['c'] == "0"){
                $c_vc['c_point'] = "0";
            }
            if($setting['vc'] == "0"){
                $c_vc['vc_point'] = "0";
            }
            $contest['team_player_count'] = $setting['team_player_count'];
            $contest['max_player_per_team'] = $setting['max_player_per_team'];
        }else{
            $sports_list = $this->get_sports_list();
            $sports_list = array_column($sports_list,NULL,"sports_id");
            $contest['team_player_count'] = $sports_list[$sports_id]['team_player_count'];
            $contest['max_player_per_team'] = $sports_list[$sports_id]['max_player_per_team'];
        }
        if(in_array($sports_id,[MOTORSPORT_SPORTS_ID,TENNIS_SPORTS_ID])){
            $c_vc['vc_point'] = 0;
        }
        $contest['c_vc'] = $c_vc;
        unset($contest['setting']);
        $this->api_response_arry['data'] = $contest;
        $this->api_response();
    }

    /**
     * Used for get contest joined users list
     * @param int $cm_id
     * @param int $contest_id
     * @return array
     */
    public function get_contest_users_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("contest/Contest_model");
        $contest_users = $this->Contest_model->get_contest_joined_users($post_data);
        if(!empty($contest_users)) {
            $allow_xp_point = isset($this->app_config['allow_xp_point']['key_value']) ? $this->app_config['allow_xp_point']['key_value'] : 0;
            $contest_users = array_column($contest_users,NULL,"user_id");
            $user_id_array = array_keys($contest_users);

            $this->load->model("user/User_model");
            $user_list = $this->User_model->get_participant_user_details($user_id_array,$allow_xp_point);
            $user_list = array_column($user_list,NULL,"user_id");
            $contest_users = array_values(array_replace_recursive($contest_users,$user_list));
        }

        $this->api_response_arry['data'] = $contest_users;
        $this->api_response();
    }

    /**
     * Used for get user joined count
     * @param int $contest_id
     * @return array
     */
    public function get_user_contest_join_count_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("contest/Contest_model");
        $result = $this->Contest_model->get_user_contest_join_count($post_data['contest_id']);
        $joined_count = 0;
        if(isset($result['user_joined_count'])) {
            $joined_count = $result['user_joined_count'];
        }
        $this->api_response_arry['data'] = array("user_joined_count" => $joined_count);
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
        $post_data = $this->input->post();
        $contest_id = $post_data['contest_id'];
        $this->load->model("contest/Contest_model");
        $contest = $this->Contest_model->get_single_row("contest_id,contest_unique_id,season_scheduled_date", CONTEST, array("contest_id" => $contest_id,"status"=>"0"));
        if(empty($contest)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['contest_not_found'];
            $this->api_response();
        }
        //check for existence
        $exist = $this->Contest_model->get_single_row("contest_id,code",INVITE,array("contest_id" => $contest['contest_id'], "season_type" => 1, "user_id" => 0,"network_contest" => 0));
        if (empty($exist)) {
            $code = $this->Contest_model->_generate_contest_code();
            $invite_data = array(
                "contest_id" => $contest['contest_id'],
                "contest_unique_id" => $contest["contest_unique_id"],
                "invite_from" => $this->user_id,
                "code" => $code,
                "season_type" => 1,
                "expire_date" => $contest['season_scheduled_date'],
                "created_date" => format_date(),
                "status" => 1
            );
            $this->Contest_model->save_record(INVITE,$invite_data);
        } else {
            $code = $exist['code'];
        }
        $this->api_response_arry['data'] = $code;
        $this->api_response();
    }

    /**
     * Function used for validate contest code
     * @param int $join_code
     * @return array
     */
    public function validate_contest_code_post() 
    {
        $this->form_validation->set_rules('join_code', $this->lang->line('join_code'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $join_code = $post_data['join_code'];
        $this->load->model("contest/Contest_model");
        $where = "(code ='{$join_code}' AND email= '{$this->email}' ) OR (code='{$join_code}' AND user_id = 0 ) AND season_type = 1 ";
        $contest = $this->Contest_model->get_single_row("contest_id,network_contest", INVITE, $where);
        //echo "<pre>";print_r($contest);die;
        if(empty($contest)) 
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest_code'];
            $this->api_response();
        }

        $this->api_response_arry['data'] = $contest;
        $this->api_response();
    }

    /**
     * Used for join contest
     * @param int $lineup_master_id
     * @param int $contest_id
     * @return array
     */
    public function join_game_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $contest_id = $post_data['contest_id'];
        $lm_id = $post_data['lineup_master_id'];
        $this->load->model("contest/Contest_model");
        $contest = $this->Contest_model->get_contest_detail($contest_id);
        if(empty($contest)) {
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }else if(strtotime($current_date) >= strtotime($contest['season_scheduled_date'])){
            $this->api_response_arry['message'] = $this->contest_lang['contest_already_started'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        //Call validate banned state api to allow free contest 
        if($contest['entry_fee'] > 0){
            $this->validate_banned_state($contest['entry_fee']);
        }

        $this->entry_fee = $contest['entry_fee'];
        $this->currency_type = $contest['currency_type'];
        $this->contest_unique_id = $contest['contest_unique_id'];
        $this->contest_entry = array("real"=>"0","winning"=>"0","bonus"=>"0","coin"=>"0","cb_amount"=>0);
        $this->promo_code_id = 0;
        $is_promo_code = 0;
        if($this->currency_type != "2"){
            $this->apply_contest_promo_code($post_data);
            $is_promo_code = 1;
        }

        $contest['lm_id'] = $lm_id;
        $is_valid = $this->validation_for_join_game($contest);
        if($is_valid) {
            $joined = $this->Contest_model->join_game($contest);
            if(isset($joined['joined_count']) && isset($joined['lmc_id']) && $joined['lmc_id'] != "") {
                //create auto recurring contest
                if(isset($joined['joined_count']) && $joined['joined_count'] == $contest['size'] && $contest['is_auto_recurring'] == '1') {
                    $this->load->helper('queue_helper');
                    $contest_queue = array("action" => "auto_recurring", "data" => array("contest_unique_id" => $contest['contest_unique_id']));
                    add_data_in_queue($contest_queue, 'contest');
                }

                //adding affiliate history
                if($this->campaign_code && $this->campaign_code != '')
                {
                    $user_data = array(
                        "user_id"       =>$this->user_id,
                        "campaign_code" =>$this->campaign_code,
                        "name"          =>$contest['contest_name'],
                        "ref_id"        =>$joined['lmc_id'],
                        "entity_id"     =>$contest['contest_id'],
                        "currency_type" =>$contest['currency_type'],
                        "amount"        =>$contest["entry_fee"],
                    );
                    $this->load->helper('queue_helper');
                    add_data_in_queue($user_data, 'af_game_user');
                }

                //promocode
                if($this->currency_type != "2" && !empty($post_data['promo_code']) && $is_promo_code == 1 && $contest['entry_fee'] > 0 && $this->promo_code_id > 0){
                    $where_condition = array("contest_unique_id" => $contest['contest_unique_id'], "user_id" => $this->user_id, "promo_code_id" => $this->promo_code_id, "lmc_id" => 0);
                    $earn_info = $this->User_model->get_user_promo_code_earn_info($where_condition);
                    if(!empty($earn_info) && $earn_info["is_processed"] == 0) {
                        $code_arr = array("is_processed" => "1", "order_id"=>$joined['order_id'], "lmc_id" => $joined['lmc_id']);
                        $this->User_model->update_promo_code_earning_details($code_arr, $earn_info["promo_code_earning_id"]);
                    }
                }

                $input = array(
                    'contest_name' => $contest['contest_name'],
                    'contest_unique_id' => $contest['contest_unique_id'],
                    'contest_id' => $contest['contest_id'],
                    'entry_fee' => $contest['entry_fee'],
                    'prize_pool' => $contest['prize_pool'],
                    'currency_type' => $contest['currency_type'],
                    'prize_distibution_detail' => json_decode($contest['prize_distibution_detail'],TRUE),
                    'season_scheduled_date' => $contest["season_scheduled_date"],
                    "collection_name" => (!empty($contest['collection_name'])) ? $contest['collection_name'] : $contest['contest_name']
                );
                $input['int_version'] = $this->app_config['int_version']['key_value'];
                $notify_data = array();
                $notify_data["notification_type"] = 1; //1-JoinGame, 
                $notify_data["source_id"] = $joined['lmc_id'];
                $allow_join_email = isset($this->app_config['allow_join_email']) ? $this->app_config['allow_join_email']['key_value'] : 0;
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
                $notify_data["content"] = json_encode($input);
                $notify_data["subject"] = $this->contest_lang['join_game_email_subject'];
                $this->load->model('user/User_nosql_model');
                $this->User_nosql_model->send_notification($notify_data);

                //delete user balance data
                $balance_cache_key = 'user_balance_'.$this->user_id;
                $this->delete_cache_data($balance_cache_key);

                //delete user joined count
                $user_ct_cache = "user_ct_".$contest["collection_master_id"]."_".$this->user_id."_".$contest['is_2nd_inning'];
                $this->delete_cache_data($user_ct_cache);

                //delete user team list cache
                $teams_cache_key = "user_teams_".$contest["collection_master_id"]."_".$this->user_id;
                $this->delete_cache_data($teams_cache_key);

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
            }else{
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->contest_lang['problem_while_join_game'];
                $this->api_response();
            }
        }else {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->message;
            $this->api_response();
        }
    }

    /**
     * used for validate contest join data
     * @param array $contest
     * @param array $post_data
     * @return array
     */
    protected function validation_for_join_game($contest) {

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

        //user joined data
        $join_data = $this->Contest_model->get_user_contest_join_count($contest['contest_id']);
        $joined_count = isset($join_data['user_joined_count']) ? $join_data['user_joined_count'] : 0;

        //check for multi lineup
        if($contest['multiple_lineup'] == $joined_count) {
            $this->message = $this->contest_lang['you_already_joined_to_max_limit'];
            return 0;
        }else if($contest['multiple_lineup'] == 1 && $joined_count > 0) {
            $this->message = $this->contest_lang['join_multiple_time_error'];
            return 0;
        }

        //check for valid lineup master id or not
        $user_lineup = $this->Contest_model->get_single_row("lineup_master_id,collection_master_id,is_2nd_inning",LINEUP_MASTER, array("lineup_master_id" => $contest['lm_id'], "user_id" => $this->user_id));
        if(empty($user_lineup)){
            $this->message = $this->contest_lang['provide_a_valid_lineup_master_id'];
            return 0;
        }

        if ($contest['collection_master_id'] != $user_lineup['collection_master_id'] || $contest['is_2nd_inning'] != $user_lineup['is_2nd_inning'])  {
            $this->message = $this->contest_lang['not_a_valid_team_for_contest'];
            return 0;
        }

        //check for already joined for same lineup
        $contest_joined = $this->Contest_model->get_single_row("lineup_master_contest_id", LINEUP_MASTER_CONTEST, array("contest_id" => $contest['contest_id'], "lineup_master_id" => $contest['lm_id']));
        if (!empty($contest_joined)) {
            $this->message = $this->contest_lang['you_already_joined_this_contest'];
            return 0;
        }

        //user wallet balance check
        $this->load->model("user/User_model");
        $balance = $this->User_model->get_user_balance($this->user_id);
        $this->user_bonus_bal = $balance['bonus_balance'];
        $this->user_bal = $balance['balance'];
        $this->winning_bal = $balance['winning_balance'];
        $this->point_balance = $balance['point_balance'];
        $this->campaign_code = $balance['campaign_code'];
        $this->cb_balance = $balance['cb_balance'];

        //check for rookie contest
        $this->validate_for_rookie_contest($contest,$balance);

        if($this->entry_fee == '0') {
            return 1;
        }

        //for Coins
        if($this->currency_type == 2) { 
            if($this->entry_fee > $this->point_balance) {
                $this->message = $this->contest_lang['not_enough_coins'];
                return 0;
            }
            $this->contest_entry['coin'] = $this->entry_fee;
        }else{
            //get user balance
            $bonus_amount = $max_bonus = $cb_amount = 0;
            if($contest['max_bonus_allowed']) {
                $max_bonus_percentage = $contest['max_bonus_allowed'];
                $max_bonus = ($this->entry_fee * $max_bonus_percentage) / 100;
                $bonus_amount = $max_bonus;
            }

            // cb balance 
            $allow_gst = isset($this->app_config['allow_gst']['key_value'])?$this->app_config['allow_gst']['key_value']:0;
            $gst_type = isset($this->app_config['allow_gst']['custom_data']['type'])?$this->app_config['allow_gst']['custom_data']['type']:'old';
            $gst_rate = isset($this->app_config['allow_gst']['custom_data']['gst_rate'])?$this->app_config['allow_gst']['custom_data']['gst_rate']:0;
            if($this->cb_balance > 0 && $allow_gst == 1 && $gst_type == 'new'){
                
                $bonus_amount = $max_bonus = 0;
                $amount_per = ($this->user_bal < $this->entry_fee)?$this->user_bal:$this->entry_fee;
                $cb_amount = ($amount_per * $gst_rate) / 100;

                if($cb_amount > $this->cb_balance){
                    $cb_amount = $this->cb_balance;
                }
            }

            if($max_bonus > $this->user_bonus_bal) {
                $bonus_amount = $this->user_bonus_bal;
            }

            $max_bonus = MAX_CONTEST_BONUS;
            if($max_bonus > 0 && $bonus_amount > $max_bonus) {
                $bonus_amount = $max_bonus;
            }
            if ($this->entry_fee > ($bonus_amount + $this->user_bal + $this->winning_bal + $this->cb_balance)) {
                $this->message = $this->contest_lang['not_enough_balance'];
                return 0;
            }

            $amount = $this->entry_fee - $bonus_amount;
            $amount = $amount - $cb_amount;
            if($amount > $this->user_bal) {
                $real = $this->user_bal;
                $amount = $amount - $real;
            }else{
                $real = $amount;
                $amount = $amount - $real;
            }
            $winning = $amount;
            $this->contest_entry['bonus'] = $bonus_amount;
            $this->contest_entry['real'] = $real;
            $this->contest_entry['winning'] = $winning;
            $this->contest_entry['cb_amount'] = $cb_amount;
        }
        return true;
    }

    /**
     * used for validate rookie user data for contest
     * @param array $contest
     * @param array $balance_arr
     * @return array
     */
    private function validate_for_rookie_contest($contest,$balance_arr)
    {
        $a_rookie = isset($this->app_config['allow_rookie_contest']) ? $this->app_config['allow_rookie_contest']['key_value'] : 0;
        if(!$a_rookie)
        {
            return true;
        }

        $custom_data = $this->app_config['allow_rookie_contest']['custom_data'];
        if($contest['group_id'] !== $custom_data['group_id'])
        {
            return true;
        }

        $this->load->model('user/User_model');
        if($custom_data['winning_amount'] > 0 && $balance_arr['total_winning'] > $custom_data['winning_amount'])
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang["rookie_user_not_allowed_for_this_contest"];
            $this->api_response();
        }

        $current_date = format_date();
        $month_interval = get_date_diff_in_months($current_date,$balance_arr['added_date']);
        if($custom_data['month_number'] > 0 && $month_interval> $custom_data['month_number'])
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang["rookie_user_not_allowed_for_this_contest"];
            $this->api_response();
        }

        return true;
    }

    /**
     * used for validate contest promo code
     * @param array $data
     * @return array
     */
    protected function apply_contest_promo_code($data) {
        if(isset($data['promo_code']) && $data['promo_code'] != "" && !empty($data['promo_code'])) {
            $this->load->model("user/User_model");
            $promo_code = $this->User_model->get_promo_code_details($data['promo_code']);
            if($promo_code){
                if($promo_code['type'] != CONTEST_JOIN_TYPE) {
                    $this->api_response_arry['message'] = $this->contest_lang["invalid_promo_code"];
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response();
                }else if($promo_code['total_used'] >= $promo_code['max_usage_limit'])
                {
                    $this->api_response_arry['message'] = $this->contest_lang["max_usage_limit_code"];
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response();
                }else if ($promo_code['user_used'] >= $promo_code['per_user_allowed']) {
                    $this->api_response_arry['message'] = $this->contest_lang["allowed_limit_exceed"];
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response();
                }

                if(isset($promo_code['contest_unique_id']) && $promo_code['contest_unique_id'] != "0" && $promo_code['contest_unique_id'] != $this->contest_unique_id)
                {
                    $this->api_response_arry['message'] = $this->contest_lang["invalid_promo_code"];
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response();
                }

                if($promo_code['value_type'] == "1") {
                    $total_discount = ($this->entry_fee * $promo_code['discount']) / 100;
                    if ($total_discount > $promo_code['benefit_cap']) {
                        $total_discount = $promo_code['benefit_cap'];
                    }
                } else {
                    $total_discount = $promo_code['discount'];
                }

                $promo_code_id = $promo_code['promo_code_id'];
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
                if(!empty($earn_info) && $earn_info["is_processed"] == 0) {
                    $code_arr = array("amount_received" => $total_discount);
                    $this->User_model->update_promo_code_earning_details($code_arr, $earn_info["promo_code_earning_id"]);
                }else if(empty($earn_info)){
                    $promo_code = array();
                    $promo_code['promo_code_id'] = $promo_code_id;
                    $promo_code['contest_unique_id'] = $this->contest_unique_id;
                    $promo_code['user_id'] = $this->user_id;
                    $promo_code['order_id'] = 0;
                    $promo_code['amount_received'] = $total_discount;
                    $promo_code['added_date'] = format_date();
                    $promo_code['lmc_id'] = 0;
                    $this->User_model->save_promo_code_earning_details($promo_code);
                }
                $this->promo_code_id = $promo_code['promo_code_id'];
            }else{
                $this->api_response_arry['message'] = $this->contest_lang["promo_code_exp_used"];
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response();
            }
        }
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
        $this->load->model("contest/Contest_model");
        $contest = $this->Contest_model->get_single_row("contest_id,contest_unique_id,season_scheduled_date,entry_fee", CONTEST, array("contest_id" => $contest_id,"status"=>"0"));
        if (empty($contest)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }

        $this->load->model("user/User_model");
        $code_detail = $this->User_model->check_promo_code_details($post_data['promo_code']);
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
        }
        $used_count = $this->User_model->get_promo_used_count($code_detail['promo_code_id']);
        if($code_detail['max_usage_limit'] != 0 && ($used_count['total_used'] >= $code_detail['max_usage_limit'])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang["max_usage_limit_code"];
            $this->api_response();
        } else {
            if (isset($code_detail['contest_unique_id']) && $code_detail['contest_unique_id'] != "0")
            {
                if ($code_detail['contest_unique_id'] != $contest['contest_unique_id'])
                {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->contest_lang["invalid_promo_code"];
                    $this->api_response();
                }
            }
            if ($code_detail['value_type'] == "1") {
                $bonus_amount = ($contest['entry_fee'] * $code_detail['discount']) / 100;
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
     * used for validate contest join data
     * @param array $contest
     * @param array $post_data
     * @return array
     */
    public function validation_for_multiple_join_game($contest) {
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

        //user joined data
        $join_data = $this->Contest_model->get_user_contest_join_count($contest['contest_id']);
        $joined_count = isset($join_data['user_joined_count']) ? $join_data['user_joined_count'] : 0;

        //check for multi lineup
        if($contest['multiple_lineup'] == $joined_count) {
            $this->message = $this->contest_lang['you_already_joined_to_max_limit'];
            return 0;
        }else if($contest['multiple_lineup'] == 1 && $joined_count > 0) {
            $this->message = $this->contest_lang['join_multiple_time_error'];
            return 0;
        }

        if($contest['multiple_lineup'] < ($joined_count + count($contest['lm_ids']))){
            $ml_limit_msg = str_replace("{TEAM_LIMIT}",$contest['multiple_lineup'],$this->contest_lang['contest_max_allowed_team_limit_exceed']);
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $ml_limit_msg;
            $this->api_response();
        }

        //check for valid lineup master id or not
        $user_lineup = $this->Contest_model->get_single_row("count(lineup_master_id) as total,GROUP_CONCAT(DISTINCT collection_master_id) as collection_master_id,GROUP_CONCAT(DISTINCT is_2nd_inning) as is_2nd_inning", LINEUP_MASTER, array("lineup_master_id IN(".implode(',',$contest['lm_ids']).")" => NULL, "user_id" => $this->user_id));
        if(empty($user_lineup) || $user_lineup['total'] != count($contest['lm_ids'])) {
            $this->message = $this->contest_lang['provide_a_valid_lineup_master_id'];
            return 0;
        }

        $user_lineup['collection_master_id'] = explode(",",$user_lineup['collection_master_id']);
        $user_lineup['is_2nd_inning'] = explode(",",$user_lineup['is_2nd_inning']);
        if (count($user_lineup['collection_master_id']) != "1" || !in_array($contest['collection_master_id'],$user_lineup['collection_master_id']) || !in_array($contest['is_2nd_inning'],$user_lineup['is_2nd_inning'])) {
            $this->message = $this->contest_lang['not_a_valid_team_for_contest'];
            return 0;
        }

        //check for already joined for same lineup
        $contest_joined = $this->Contest_model->get_single_row("count(lineup_master_contest_id) as total", LINEUP_MASTER_CONTEST, array("contest_id" => $contest['contest_id'], "lineup_master_id IN(".implode(',',$contest['lm_ids']).")"=>NULL));
        if (!empty($contest_joined) && $contest_joined['total'] > 0) {
            $this->message = $this->contest_lang['you_already_joined_this_contest'];
            return 0;
        }

        $total_entry_fee = $this->entry_fee * count($contest['lm_ids']);
        //user wallet balance check
        $this->load->model("user/User_model");
        $balance = $this->User_model->get_user_balance($this->user_id);
        $this->user_bonus_bal = $balance['bonus_balance'];
        $this->user_bal = $balance['balance'];
        $this->winning_bal = $balance['winning_balance'];
        $this->point_balance = $balance['point_balance'];
        $this->campaign_code = $balance['campaign_code'];

        //check for rookie contest
        $this->validate_for_rookie_contest($contest,$balance);

        if($this->entry_fee == '0') {
            return 1;
        }

        //for Coins
        if($this->currency_type == 2) { 
            if($total_entry_fee > $this->point_balance) {
                $this->message = $this->contest_lang['not_enough_coins'];
                return 0;
            }
            $this->contest_entry['coin'] = $total_entry_fee;
        }else{
            //get user balance
            $bonus_amount = $max_bonus = 0;
            if($contest['max_bonus_allowed']) {
                $max_bonus_percentage = $contest['max_bonus_allowed'];
                $max_bonus = ($total_entry_fee * $max_bonus_percentage) / 100;
                $bonus_amount = $max_bonus;
            }

            if($max_bonus > $this->user_bonus_bal) {
                $bonus_amount = $this->user_bonus_bal;
            }

            $max_bonus = MAX_CONTEST_BONUS;
            if($max_bonus > 0 && $bonus_amount > $max_bonus) {
                $bonus_amount = $max_bonus;
            }
            if ($total_entry_fee > ($bonus_amount + $this->user_bal + $this->winning_bal)) {
                $this->message = $this->contest_lang['not_enough_balance'];
                return 0;
            }

            $amount = $total_entry_fee - $bonus_amount;
            if($amount > $this->user_bal) {
                $real = $this->user_bal;
                $amount = $amount - $real;
            }else{
                $real = $amount;
                $amount = $amount - $real;
            }
            $winning = $amount;
            $this->contest_entry['bonus'] = $bonus_amount;
            $this->contest_entry['real'] = $real;
            $this->contest_entry['winning'] = $winning;
        }
        return true;
    }


    /**
     * Used for join contest
     * @param int $lineup_master_id
     * @param int $contest_id
     * @return array
     */
    public function multiteam_join_game_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $allow_multi_team = isset($this->app_config['allow_multi_team']) ? $this->app_config['allow_multi_team']['key_value']:0;
        if($allow_multi_team != 1){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('module_disable_error');
            $this->api_response();
        }

        $post_data = $this->input->post();
        if(!isset($post_data['lineup_master_id']) || empty($post_data['lineup_master_id'])){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['select_min_one_team'];
            $this->api_response();
        }
        $current_date = format_date();
        $contest_id = $post_data['contest_id'];
        $lm_ids = $post_data['lineup_master_id'];
        $this->load->model("contest/Contest_model");
        $contest = $this->Contest_model->get_contest_detail($contest_id);
        if(empty($contest)) {
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }else if(strtotime($current_date) >= strtotime($contest['season_scheduled_date'])){
            $this->api_response_arry['message'] = $this->contest_lang['contest_already_started'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        //Call validate banned state api to allow free contest 
        if($contest['entry_fee'] > 0){
            $this->validate_banned_state($contest['entry_fee']);
        }

        $this->entry_fee = $contest['entry_fee'];
        $this->currency_type = $contest['currency_type'];
        $this->contest_unique_id = $contest['contest_unique_id'];
        $this->contest_entry = array("real"=>"0","winning"=>"0","bonus"=>"0","coin"=>"0");
        $this->promo_code_id = 0;
        $contest['lm_ids'] = $lm_ids;
        $is_valid = $this->validation_for_multiple_join_game($contest);
        //echo "=====".$is_valid;
        //echo "<pre>";print_r($contest);die;
        if($is_valid) {
            $contest_entry = $this->contest_entry;
            $is_success = 0;
            $team_error = array();
            foreach($lm_ids as $lineup_master_id){
                $ct_entry = $this->entry_fee;
                $tmp_entry = array("real"=>"0","winning"=>"0","bonus"=>"0","coin"=>"0");
                if($ct_entry > 0){
                    if($this->currency_type == 2) {
                        $tmp_entry['coin'] = $ct_entry;
                    }else{
                        if($contest['max_bonus_allowed'] > 0){
                            $ct_bonus = ($ct_entry * $contest['max_bonus_allowed']) / 100;
                            if($ct_bonus > $contest_entry['bonus']){
                                $ct_bonus = $contest_entry['bonus'];
                            }
                            if(MAX_CONTEST_BONUS > 0 && $ct_bonus > MAX_CONTEST_BONUS){
                                $ct_bonus = MAX_CONTEST_BONUS;
                            }
                            $tmp_entry['bonus'] = $ct_bonus;
                            $contest_entry['bonus'] = $contest_entry['bonus'] - $tmp_entry['bonus'];
                        }
                        $ct_entry = $ct_entry - $tmp_entry['bonus'];
                        if($ct_entry > $contest_entry['real']){
                            $tmp_entry['real'] = $contest_entry['real'];
                            $ct_entry = $ct_entry - $tmp_entry['real'];
                        }else{
                            $tmp_entry['real'] = $ct_entry;
                            $ct_entry = $ct_entry - $tmp_entry['real'];
                        }
                        $tmp_entry['winning'] = $ct_entry;
                        $contest_entry['real'] = $contest_entry['real'] - $tmp_entry['real'];
                        $contest_entry['winning'] = $contest_entry['winning'] - $tmp_entry['winning'];
                    }
                }
                $this->contest_entry = $tmp_entry;
                $contest['lm_id'] = $lineup_master_id;
                $joined = $this->Contest_model->join_game($contest);
                if(isset($joined['joined_count']) && isset($joined['lmc_id']) && $joined['lmc_id'] != ""){
                    //create auto recurring contest
                    if(isset($joined['joined_count']) && $joined['joined_count'] == $contest['size'] && $contest['is_auto_recurring'] == '1') {
                        $this->load->helper('queue_helper');
                        $contest_queue = array("action" => "auto_recurring", "data" => array("contest_unique_id" => $contest['contest_unique_id']));
                        add_data_in_queue($contest_queue, 'contest');
                    }

                    //adding affiliate history
                    if($this->campaign_code && $this->campaign_code != '')
                    {
                        $user_data = array(
                            "user_id"       =>$this->user_id,
                            "campaign_code" =>$this->campaign_code,
                            "name"          =>$contest['contest_name'],
                            "ref_id"        =>$joined['lmc_id'],
                            "entity_id"     =>$contest['contest_id'],
                            "currency_type" =>$contest['currency_type'],
                            "amount"        =>$contest["entry_fee"],
                        );
                        $this->load->helper('queue_helper');
                        add_data_in_queue($user_data, 'af_game_user');
                    }

                    $input = array(
                        'contest_name' => $contest['contest_name'],
                        'contest_unique_id' => $contest['contest_unique_id'],
                        'contest_id' => $contest['contest_id'],
                        'entry_fee' => $contest['entry_fee'],
                        'prize_pool' => $contest['prize_pool'],
                        'currency_type' => $contest['currency_type'],
                        'prize_distibution_detail' => json_decode($contest['prize_distibution_detail'],TRUE),
                        'season_scheduled_date' => $contest["season_scheduled_date"],
                        "collection_name" => (!empty($contest['collection_name'])) ? $contest['collection_name'] : $contest['contest_name']
                    );
                    $input['int_version'] = $this->app_config['int_version']['key_value'];
                    $notify_data = array();
                    $notify_data["notification_type"] = 1; //1-JoinGame, 
                    $notify_data["source_id"] = $joined['lmc_id'];
                    $allow_join_email = isset($this->app_config['allow_join_email']) ? $this->app_config['allow_join_email']['key_value'] : 0;
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
                    $notify_data["content"] = json_encode($input);
                    $notify_data["subject"] = $this->contest_lang['join_game_email_subject'];
                    $this->load->model('user/User_nosql_model');
                    $this->User_nosql_model->send_notification($notify_data);

                    $is_success = 1;
                    $ct_info = $this->Contest_model->get_single_row("contest_id,total_user_joined,prize_distibution_detail,current_prize", CONTEST, array("contest_id" => $contest_id));
                    $contest['total_user_joined'] = $ct_info['total_user_joined'];
                    $contest['prize_distibution_detail'] = $ct_info['prize_distibution_detail'];
                    $contest['current_prize'] = $ct_info['current_prize'];
                }else{
                    $team_error[] = $lineup_master_id;
                }
            }

            if($is_success == 1){
                //delete user balance data
                $balance_cache_key = 'user_balance_'.$this->user_id;
                $this->delete_cache_data($balance_cache_key);

                //delete user joined count
                $user_ct_cache = "user_ct_".$contest["collection_master_id"]."_".$this->user_id."_".$contest['is_2nd_inning'];
                $this->delete_cache_data($user_ct_cache);

                //delete user team list cache
                $teams_cache_key = "user_teams_".$contest["collection_master_id"]."_".$this->user_id;
                $this->delete_cache_data($teams_cache_key);

                if(isset($post_data['ct']) && $post_data['ct'] == "1"){
                    $total_join_contest = $this->Contest_model->get_user_total_contest_join_count();
                    $count_data = $total_join_contest['total_join_contest'];
                    $c_count = 1;
                    if(!empty($count_data) && $count_data > 0){
                        $c_count = $count_data;
                    }
                    $this->api_response_arry['data']['ct'] = $c_count;
                }

                $tm_count = count($lm_ids) - count($team_error);
                $join_msg = str_replace("{TEAM_COUNT}",$tm_count,$this->contest_lang['multiteam_join_game_success']);
                $this->api_response_arry['message'] = $join_msg;
                $this->api_response();
            }else{
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->contest_lang['problem_while_join_game'];
                $this->api_response();
            }
        } else {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->message;
            $this->api_response();
        }
    }

    /**
     * Function used to get user joined fixture
     * @param int $sports_id
     * @return array
     */
    public function get_lobby_joined_fixtures_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $this->load->model('contest/Contest_model');
        $result = $this->Contest_model->get_lobby_joined_fixtures($sports_id);
        //echo "<pre>";print_r($result);die;
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
     * used for get user joined match list
     * @param int $sports_id
     * @param int $status
     * @return array
     */
    public function get_user_joined_fixture_post() {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        $this->form_validation->set_rules('status', $this->lang->line('match_status'), 'trim|required|callback_check_collection_status');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $status = $post_data['status'];
        $this->load->model('contest/Contest_model');
        $current_date = format_date();
        $result = $this->Contest_model->get_user_joined_fixtures($post_data);
        //echo "<pre>";print_r($result);die;
        $result_arr = array();
        if(!empty($result)){
            $season_ids = array_unique(explode(",",implode(",",array_column($result,"season_ids"))));
            if(in_array($sports_id,$this->tour_game_sports)){
                $match_list = $this->Contest_model->get_tour_season_detail($season_ids);
            }else{
                $select = "S.home_id,S.away_id,IFNULL(S.score_data,'[]') as score_data,IFNULL(S.team_batting_order,'[]') as team_batting_order,S.status,S.status_overview";
                $match_list = $this->Contest_model->get_fixture_season_detail($season_ids,$select);
            }

            //boster module
            $booster_list = array();
            $allow_booster = isset($this->app_config['allow_booster'])?$this->app_config['allow_booster']['key_value']:0;
            if($allow_booster == 1 && in_array($sports_id, array(BASEBALL_SPORTS_ID,NFL_SPORTS_ID,BASKETBALL_SPORTS_ID,SOCCER_SPORTS_ID,CRICKET_SPORTS_ID))){
                $cm_ids = array_column($result,"collection_master_id");
                $this->load->model("booster/Booster_model");
                $booster_list = $this->Booster_model->get_lobby_collection_booster($cm_ids);
                $booster_list = array_column($booster_list,"name","collection_master_id");
            }

            $result_arr['fixture'] = $result;
            $result_arr['match'] = $match_list;
            $result_arr['booster'] = $booster_list;
        }
        $this->api_response_arry['data'] = $result_arr;
        $this->api_response();
    }

    /**
     * used for get user joined contest list
     * @param int $status
     * @param int $cm_id
     * @return array
     */
    public function get_user_joined_contest_post() {
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $current_date = format_date();
        $post_data = $this->input->post();
        $cm_id = $post_data['collection_master_id'];
        $this->load->model("contest/Contest_model");
        $contest_list = $this->Contest_model->get_user_joined_contest($cm_id);
        $user_data = array();
        if(!empty($contest_list))
        {
            $user_ids = array_unique(array_column($contest_list,'user_id'));
            if(!empty($user_ids)){
                $this->load->model("user/User_model");
                $user_data = $this->User_model->get_user_detail_by_user_id($user_ids);
                if(!empty($user_data))
                {
                    $user_data = array_column($user_data,NULL,'user_id');
                }
            }
        }

        $bench_player = isset($this->app_config['bench_player']) ? $this->app_config['bench_player']['key_value'] : 0;
        $bench_teams = array();
        if($bench_player == "1" && !empty($contest_list)){
            $team_ids = array_unique(array_column($contest_list,"lineup_master_id"));
            $bench_teams = $this->Contest_model->get_team_bench_players_count($team_ids);
            if(!empty($bench_teams)){
                $bench_teams = array_column($bench_teams,"bench_applied","lineup_master_id");
            }
        }

        
        $fixture_contest = array();
        foreach($contest_list as $contest) {
            $prize_distibution_detail = json_decode($contest['prize_distibution_detail'],TRUE);
            if(!array_key_exists($contest['contest_id'], $fixture_contest)) {
                $tmp_arr = $contest;
                unset($tmp_arr['lineup_master_contest_id']);
                unset($tmp_arr['total_score']);
                unset($tmp_arr['game_rank']);
                unset($tmp_arr['is_winner']);
                unset($tmp_arr['amount']);
                unset($tmp_arr['bonus']);
                unset($tmp_arr['coin']);
                unset($tmp_arr['merchandise']);
                unset($tmp_arr['lineup_master_id']);
                unset($tmp_arr['team_name']);
                unset($tmp_arr['is_pl_team']);
                $tmp_arr['creator'] = array();
                if(isset($user_data[$contest['user_id']]))
                {
                    $user_arr = $user_data[$contest['user_id']];
                    unset($user_arr['user_unique_id']);
                    $tmp_arr['creator'] = $user_arr;
                }
                $fixture_contest[$contest['contest_id']] = $tmp_arr;
            }

            $is_winner = $contest["is_winner"];
            if($contest['status'] != 3 && $contest['season_scheduled_date'] >= $current_date && !empty($prize_distibution_detail)) {
                $last_obj = end($prize_distibution_detail);
                if (!empty($last_obj['max']) && $last_obj['max'] >= $contest["game_rank"]) {
                    $is_winner = 1;
                }
            }
            $fixture_contest[$contest['contest_id']]["teams"][] = array(
                "lineup_master_id" => $contest['lineup_master_id'],
                "team_name" => $contest["team_name"],
                "lineup_master_contest_id" => $contest["lineup_master_contest_id"],
                "total_score" => $contest["total_score"],
                "game_rank" => $contest["game_rank"],
                "is_winner" => $is_winner,
                "amount" => $contest["amount"],
                "bonus" => $contest["bonus"],
                "coin" => $contest["coin"],
                "merchandise" => $contest["merchandise"],
                "is_pl_team" => $contest['is_pl_team'],
                "booster_id" => $contest['booster_id'],
                "bench_applied" => isset($bench_teams[$contest['lineup_master_id']]) ? $bench_teams[$contest['lineup_master_id']] : 0
            );
        }
        $fixture_contest = array_values($fixture_contest);
        $this->api_response_arry['data'] = $fixture_contest;
        $this->api_response();
    }

    /**
     * used for get user team list for switch
     * @param int $sports_id
     * @param int $contest_id
     * @return array
     */
    public function get_user_switch_team_list_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("contest/Contest_model");
        $team_data = $this->Contest_model->get_fixture_contest_free_teams($post_data['contest_id']);
        $this->api_response_arry['data'] = $team_data;
        $this->api_response();
    }

    /**
     * used for swith team in joined contest
     * @param int $contest_id
     * @param int $lineup_master_id
     * @param int $lineup_master_contest_id
     * @return array
     */
    public function switch_team_contest_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');
        $this->form_validation->set_rules('lineup_master_contest_id', $this->lang->line('lineup_master_contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['user_id'] = $this->user_id;
        $this->load->model("contest/Contest_model");
        $check_team = $this->Contest_model->check_valid_user_previous_team($post_data);
        if(empty($check_team)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_previous_team_for_collecton'];
            $this->api_response();
        }

        $check_valid = $this->Contest_model->get_single_row("lineup_master_contest_id", LINEUP_MASTER_CONTEST, array("contest_id" => $post_data['contest_id'], "lineup_master_id" => $post_data['lineup_master_id']));
        if (!empty($check_valid)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['you_already_joined_this_contest'];
            $this->api_response();
        }

        $is_valid = $this->Contest_model->check_valid_team_for_contest($post_data);
        if ($is_valid) {
            $team_arr = array();
            $team_arr['lineup_master_id'] = $post_data['lineup_master_id'];
            $this->Contest_model->update(LINEUP_MASTER_CONTEST, $team_arr, array('lineup_master_contest_id' => $post_data['lineup_master_contest_id']));

            $this->api_response_arry['message'] = $this->contest_lang['team_switch_success'];
            $this->api_response();
        } else {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_team_for_collecton'];
            $this->api_response();
        }
    }

    /**
     * used for get user joined contest list
     * @param int $collection_master_id
     * @return array
     */
    public function get_user_match_contest_post() {
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $cm_id = $post_data['collection_master_id'];
        $cache_key = "user_fixture_ct_".$cm_id."_".$this->user_id;
        $user_contest = $this->get_cache_data($cache_key);
        if(!$user_contest) 
        {
            $this->load->model("contest/Contest_model");
            $contest_list = $this->Contest_model->get_user_match_contest($cm_id);
            $fixture_contest = array();
            $user_teams = array();
            if(!empty($contest_list)){
                foreach($contest_list as $contest) {
                    $prize_detail = json_decode($contest['prize_distibution_detail'],TRUE);
                    $post_data['sports_id'] = $contest['sports_id'];
                    $post_data['league_id'] = $contest['league_id'];
                    if(!array_key_exists($contest['contest_id'], $fixture_contest)) {
                        $tmp_arr = $contest;
                        unset($tmp_arr['lineup_master_contest_id']);
                        unset($tmp_arr['total_score']);
                        unset($tmp_arr['game_rank']);
                        unset($tmp_arr['is_winner']);
                        unset($tmp_arr['lineup_master_id']);
                        unset($tmp_arr['team_name']);
                        unset($tmp_arr['is_pl_team']);
                        unset($tmp_arr['team_data']);
                        $tmp_arr['merchandise'] = array();
                        if(!empty($contest['merchandise'])){
                            $tmp_arr['merchandise'][] = $contest['merchandise'];
                        }
                        $fixture_contest[$contest['contest_id']] = $tmp_arr;
                    }else{
                        $fixture_contest[$contest['contest_id']]['amount'] = $fixture_contest[$contest['contest_id']]['amount'] + $contest["amount"];
                        $fixture_contest[$contest['contest_id']]['bonus'] = $fixture_contest[$contest['contest_id']]['bonus'] + $contest["bonus"];
                        $fixture_contest[$contest['contest_id']]['coin'] = $fixture_contest[$contest['contest_id']]['coin'] + $contest["coin"];
                        if(!empty($contest['merchandise'])){
                            $fixture_contest[$contest['contest_id']]['merchandise'][] = $contest['merchandise'];
                        }
                    }

                    $is_winner = $contest["is_winner"];
                    if($contest['status'] != 3){
                        $last_obj = end($prize_detail);
                        if (!empty($last_obj['max']) && $last_obj['max'] >= $contest["game_rank"]) {
                            $is_winner = 1;
                        }
                    }

                    $team = array(
                        "lineup_master_id" => $contest['lineup_master_id'],
                        "team_name" => $contest["team_name"],
                        "lineup_master_contest_id" => $contest["lineup_master_contest_id"],
                        "total_score" => $contest["total_score"] ? $contest["total_score"] : 0,
                        "game_rank" => $contest["game_rank"] ? $contest["game_rank"] : 0,
                        "is_winner" => $is_winner,
                        "amount" => $contest['amount'],
                        "bonus" => $contest['bonus'],
                        "coin" => $contest['coin'],
                        "merchandise" => $contest['merchandise'],
                        "is_pl_team" => $contest['is_pl_team']
                    );

                    $fixture_contest[$contest['contest_id']]["teams"][$contest['lineup_master_contest_id']] = $team;
                    $user_teams[$contest['lineup_master_id']] = array("lineup_master_id"=>$contest['lineup_master_id'],"lineup_master_contest_id"=>$contest['lineup_master_contest_id'],"score"=>$contest["total_score"],"team_name"=>$contest['team_name'],"team_data"=>json_decode($contest['team_data'],TRUE));
                }
                if(!empty($user_teams)){
                    $position_list = $this->get_position_list($post_data['sports_id']);
                    $player_position = array_fill_keys(array_column($position_list,'position_name'),0);
                    
                    $roster_cache_key = "roster_list_".$cm_id;
                    $roster_list = $this->get_cache_data($roster_cache_key);
                    if(!$roster_list)
                    {
                        $this->load->model("lineup/Lineup_model");
                        $roster_list = $this->Lineup_model->get_fixture_rosters($cm_id);
                        $this->set_cache_data($roster_cache_key,$roster_list,REDIS_2_DAYS);
                    }
                    $players_list = array_column($roster_list,NULL,"player_team_id");
                    $players_pos = array_column($roster_list,"position","player_team_id");
                    $roster_team = array_column($roster_list,"team_abbr","player_team_id");
                    $roster_name = array_column($roster_list,"display_name","player_team_id");
                    foreach($user_teams as &$team){
                        $other_pl = array();
                        $c_id = $team['team_data']['c_id'];
                        $vc_id = $team['team_data']['vc_id'];
                        $pl_array = $team['team_data']['pl'];

                        $pl_fill_arr = array_fill_keys($pl_array,"0");
                        $pos_arr = array_intersect_key($players_pos,$pl_fill_arr);
                        $team['position'] = array_count_values($pos_arr);
                        $team['team'] = array_count_values(array_intersect_key($roster_team,$pl_fill_arr));
                        if($c_id != "" && $post_data['sports_id'] == MOTORSPORT_SPORTS_ID){
                            $pos_arr_cr = array_flip($pos_arr);
                            $vc_id = isset($pos_arr_cr['CR']) ? $pos_arr_cr['CR'] : "";
                        }
                        //return other player if c or vc not available
                        if($c_id == "" || $vc_id == ""){
                            $tmp_pl = array_flip($pl_array);
                            unset($tmp_pl[$c_id]);
                            unset($tmp_pl[$vc_id]);
                            $other_pl = array_values(array_intersect_key($roster_name,$tmp_pl));
                        }
                        $team['other_pl'] = $other_pl;
                        $team['c_data'] = $team['vc_data'] = array();
                        if(isset($players_list[$c_id])){
                            $cdata = $players_list[$c_id];
                            $team['c_data'] = array("name"=>$cdata['display_name'],"team"=>$cdata['team_abbr'],"position"=>$cdata['position'],"jersey"=>$cdata['jersey']);
                        }
                        if(isset($players_list[$vc_id])){
                            $vcdata = $players_list[$vc_id];
                            $team['vc_data'] = array("name"=>$vcdata['display_name'],"team"=>$vcdata['team_abbr'],"position"=>$vcdata['position'],"jersey"=>$vcdata['jersey']);
                        }
                        unset($team['team_data']);
                    }
                }
                $user_teams = array_values($user_teams);
                $fixture_contest = array_values($fixture_contest);
            }

            $user_contest = array("contest"=>$fixture_contest,"teams"=>$user_teams);
            if(!empty($contest_list)){
                $ct_st = array_diff(array_unique(array_column($contest_list,"status")),[3]);
                if(empty($ct_st)){
                    $this->set_cache_data($cache_key, $user_contest, REDIS_7_DAYS);
                }
            }
        }

        $this->api_response_arry['data'] = $user_contest;
        $this->api_response();
    }

    /**
     * Used for download gst invoice as PDF
     * @param int $lineup_master_contest_id
     * @return PDF report
     */
    public function gst_invoice_get()
    {
        $lmc_id = isset($_REQUEST['lmc_id']) ? $_REQUEST['lmc_id'] : "";
        if(!isset($lmc_id) || empty($lmc_id)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Contest team id field required.";
            $this->api_response();
        }
        $this->load->model("contest/Contest_model");
        $result = $this->Contest_model->get_gst_contest_detail($lmc_id);
        if(empty($result)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid contest team id.";
            $this->api_response();
        }else if($result['is_gst_report'] != "1"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "GST Invoice not available yet. please try again after some time.";
            $this->api_response();
        }
        
        $this->load->model("user/User_model");
        $invoice = $this->User_model->get_gst_invoice_detail($lmc_id);
        if(!empty($invoice))
        {
            $currency_code = isset($this->app_config['currency_code'])?$this->app_config['currency_code']['key_value']:'₹';
            $master_state_id = $invoice['master_state_id'];
            $invoice_data = array();
            $invoice_data['company_name'] = $this->app_config['allow_gst']['custom_data']['firm_name'];
            $invoice_data['company_address'] = $this->app_config['allow_gst']['custom_data']['firm_address'];
            $invoice_data['company_contact'] = $this->app_config['allow_gst']['custom_data']['contact_no'];
            $invoice_data['currency'] = $currency_code;

            $invoice_data['date'] = $invoice['txn_date'];
            $invoice_data['invoice_no'] = $invoice['invoice_id'];
            $invoice_data['match_name']= $invoice['match_name'];
            $invoice_data['entry_fee'] = $invoice['entry_fee'];
            $invoice_data['contest_name'] = $invoice['contest_name'];

            $invoice_data['phone_no'] = $invoice['phone_code'].$invoice['phone_no'];
            $invoice_data['user_name'] = $invoice['user_name'];
            $invoice_data['full_name'] = $invoice['first_name']." ".$invoice['last_name'];
            $invoice_data['email'] = $invoice['email'];
            $invoice_data['address'] = $invoice['address'];
            $invoice_data['city'] = $invoice['city'];
            $invoice_data['zip_code'] = $invoice['zip_code'];
            $invoice_data['taxable_value'] = $invoice['rake_amount'];
            $invoice_data['cgst'] = $invoice['cgst'];
            $invoice_data['sgst'] = $invoice['sgst'];
            $invoice_data['igst'] = $invoice['igst'];
            $invoice_data['state'] = "";
            if($master_state_id == 0){
                $master_state_id = $this->app_config['allow_gst']['custom_data']['state_id'];
            }

            if(isset($master_state_id) && $master_state_id > "0")
            {
                $state_info = $this->User_model->get_state_detail($master_state_id);
                if(!empty($state_info)){
                    $invoice_data['state'] = $state_info['name'];
                }
            }

            //echo "<pre>";print_r($invoice_data);die;
            if(!empty($invoice_data))
            {
                $html = $this->load->view('contest/invoice',array("data"=>$invoice_data),TRUE);
                ini_set('memory_limit', '-1');
                $this->load->helper('dompdf_helper');
                $file_name = str_replace(" ","_","Invoice-".$invoice['invoice_id']."_".strtolower(str_replace(" ","-",$invoice['match_name']))."_".strtolower(str_replace(" ","",$result['team_name'])));
                generate_pdf($file_name,$html);
            }
        }

        $this->api_response_arry['message'] = "File downloaded";
        $this->api_response();
    }

    /**
     * used for download joined users team list
     * @param int $contest_id
     * @return array
     */
    public function download_contest_teams_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $contest_id = $post_data['contest_id'];
        $filePath = "lineup/".$contest_id.".pdf";
        try{
            $data_arr = array();
            $data_arr['file_path'] = $filePath;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_upload = $upload_lib->get_file_info($data_arr);
            if(!empty($is_upload)){
                $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                $this->api_response_arry['data'] = array('uploaded' => '1', 'file' => IMAGE_PATH.$filePath);
                $this->api_response();
            }
        }catch(Exception $e){
            
        }

        $this->load->model("contest/Contest_model");
        $contest_info = $this->Contest_model->get_single_row("contest_id,is_pdf_generated", CONTEST, array("contest_id" => $contest_id));
        if(empty($contest_info)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['contest_not_found'];
            $this->api_response();
        }

        if($contest_info['is_pdf_generated'] == 0) {
            $this->load->helper('queue_helper');
            $server_name = get_server_host_name();
            $content = array();
            $content['url'] = $server_name."/cron/dfs/generate_contest_pdf/" . $contest_info['contest_id'];
            add_data_in_queue($content,'contestpdf');

            //update push status
            $this->Contest_model->update(CONTEST, array("is_pdf_generated" => "1"), array("contest_id" => $contest_info['contest_id']));
        }

        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['message'] = $this->contest_lang['process_contest_pdf'];
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
        $contest_id = $post_data['contest_id'];
        $this->load->model("contest/Contest_model");
        $contest_details = $this->Contest_model->get_single_row("is_tie_breaker",CONTEST,array('contest_id' => $contest_id));
        $post_data['is_tie_breaker'] = $contest_details['is_tie_breaker'];

        $result = $this->Contest_model->get_contest_leaderboard($post_data);
        $user_ids = $user_result = array();
        if(isset($post_data['page_no']) && $post_data['page_no'] == "1"){
            $post_data['user_id'] = $this->user_id;
            $user_result = $this->Contest_model->get_contest_leaderboard($post_data);
            $user_ids[] = $this->user_id;
        }
        if(!empty($result)){
            $tmp_users = array_column($result,"user_id");
            $user_ids = array_unique(array_merge($tmp_users,$user_ids));
        }
        $user_data = array();
        if(!empty($user_ids)){
            $this->load->model("user/User_model");
            $user_data = $this->User_model->get_user_detail_by_user_id($user_ids);
            if(!empty($user_data))
            {
                $user_data = array_column($user_data,NULL,'user_id');
            }
        }

        foreach($result as &$row){
            $row['image'] = $user_data[$row['user_id']]['image'];
            $row['name'] = trim($user_data[$row['user_id']]['name']);
            unset($row['user_id']);
        }

        if(!empty($user_result)){
            foreach($user_result as &$row){
                $row['image'] = $user_data[$row['user_id']]['image'];
                $row['name'] = trim($user_data[$row['user_id']]['name']);
                unset($row['user_id']);
            }
        }

        //return contest prize data
        if(isset($post_data['page_no']) && $post_data['page_no'] == "1"){
            $contest_details = $this->Contest_model->get_single_row("guaranteed_prize,total_user_joined,entry_fee,site_rake,size,minimum_size,prize_distibution_detail,current_prize,IF(user_id > 0,'1','0') as is_private,host_rake",CONTEST,array('contest_id' => $contest_id));
            if(!empty($contest_details['prize_distibution_detail']))
            {
                $prize_data = json_decode($contest_details['prize_distibution_detail'],TRUE);
            }
            if($contest_details['guaranteed_prize'] != "2"){
                $contest_details['current_prize'] = json_decode($contest_details['current_prize'],TRUE);
                if(!isset($contest_details['current_prize']) || empty($contest_details['current_prize'])){
                    $tmp_contest = $contest_details;
                    if($tmp_contest['total_user_joined'] < $contest_details['minimum_size']){
                        $tmp_contest['total_user_joined'] = $contest_details['minimum_size'];
                    }
                    $tmp_contest['prize_distibution_detail'] = json_encode($tmp_contest['prize_distibution_detail']);
                    $contest_details['current_prize'] = reset_contest_prize_data($tmp_contest);
                }
                foreach($contest_details['current_prize'] as &$row){
                    if($row['prize_type'] != "3"){
                        $no_user = $row['max'] - $row['min'] + 1;
                        $row['amount'] = $row['min_value'] = number_format(($row['amount'] / $no_user),2,".","");
                        $row['max_value'] = number_format(($row['max_value'] / $no_user),2,".","");
                    }
                }
                $prize_data = $contest_details['current_prize'];
            }
        }
        $return_arr = array("own" => $user_result, "other" => $result,"prize_data"=>$prize_data);
        $this->api_response_arry['data'] = $return_arr;
        $this->api_response();
    }

    /**
     * Used for get contest compare team details
     * @param int $collection_master_id
     * @param int $u_lmc_id
     * @param int $o_lmc_id
     * @return array
     */
    public function get_compare_teams_post() {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        $this->form_validation->set_rules('u_lmc_id', $this->lang->line('lineup_master_contest_id'), 'trim|required');
        $this->form_validation->set_rules('o_lmc_id', $this->lang->line('lineup_master_contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $current_date = format_date();
        $post_data = $this->input->post();
        $sports_id = $post_data["sports_id"];
        $cm_id = $post_data["collection_master_id"];
        $u_lmc_id = $post_data["u_lmc_id"];
        $o_lmc_id = $post_data["o_lmc_id"];
        $this->load->model("contest/Contest_model");
        $fixture = $this->Contest_model->get_single_row("*",COLLECTION_MASTER,array('collection_master_id' => $cm_id));
        if(empty($fixture)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['contest_not_found'];
            $this->api_response();
        }
        $bench_player = isset($this->app_config['bench_player']) ? $this->app_config['bench_player']['key_value'] : 0;
        if($bench_player == "1" && $fixture['is_lineup_processed'] == "0" && $fixture['bench_processed'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('bench_process_waiting_error');
            $this->api_response();
        }

        $lmc_ids = array($u_lmc_id,$o_lmc_id);
        $lmc_info = $this->Contest_model->get_contest_teams_by_lmc_ids($lmc_ids);
        if(empty($lmc_info) || count($lmc_info) != 2){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('team_detail_not_found');
            $this->api_response();
        }
        $roster_cache_key = "roster_list_".$cm_id;
        $roster_list = $this->get_cache_data($roster_cache_key);
        if(!$roster_list)
        {
            $this->load->model("lineup/Lineup_model");
            $roster_list = $this->Lineup_model->get_fixture_rosters($cm_id);
            $this->set_cache_data($roster_cache_key,$roster_list,REDIS_2_DAYS);
        }
        $roster_list = array_column($roster_list,NULL,'player_team_id');
        $booster_ids = array_unique(array_column($lmc_info,"booster_id"));
        $booster_ids = array_diff($booster_ids, [0]); 
        $booster_list = array();
        if(!empty($booster_ids)){
            $this->load->model("booster/Booster_model");
            $booster_list = $this->Booster_model->get_match_booster_detail($cm_id,$booster_ids);
            $booster_list = array_column($booster_list,NULL,"booster_id");
        }
        $is_lineup_processed = $fixture['is_lineup_processed'];
        $final_data = array("oponent"=>array(),"you"=>array());
        foreach($lmc_info as $team){
            $team_data = json_decode($team['team_data'],TRUE);
            $team_data['pl'] = array_fill_keys($team_data['pl'],"0");
            if($is_lineup_processed == 1){
                $lineup_tbl = "vi_".LINEUP."_".$cm_id;
                $lineup_details = $this->Contest_model->get_all_table_data("player_team_id,ROUND(IFNULL(score,0),1) AS score",$lineup_tbl, array("lineup_master_id" => $team['lineup_master_id']));
                $team_data['pl'] = array_column($lineup_details,"score","player_team_id");
            }else if(in_array($is_lineup_processed,array("2","3"))){
                $completed_team = $this->Contest_model->get_single_row("lineup_master_id,team_data",COMPLETED_TEAM, array("collection_master_id" => $cm_id, "lineup_master_id" => $team['lineup_master_id']));
                $team_data = json_decode($completed_team['team_data'],TRUE);
            }

            $bench_pl_ids = array();
            $bench_players = array();
            if($bench_player == "1"){
                $pl_list = $this->Contest_model->get_all_table_data("priority,player_id,out_player_id,status,IFNULL(reason,'') as reason",BENCH_PLAYER,array("lineup_master_id"=>$team['lineup_master_id']),array("priority"=>"ASC"));
                $bench_pl_ids = array_column($pl_list,"player_id");
                foreach($pl_list as $row){
                    $player_info = $roster_list[$row['player_id']];
                    if(!empty($player_info)){
                        $tmp_arr = array();
                        $tmp_arr['player_id'] = $player_info['player_id'];
                        $tmp_arr['player_team_id'] = $player_info['player_team_id'];
                        $tmp_arr['team_id'] = $player_info['team_id'];
                        $tmp_arr['team_uid'] = $player_info['team_uid'];
                        $tmp_arr['full_name'] = $player_info['full_name'];
                        $tmp_arr['display_name'] = $player_info['display_name'];
                        $tmp_arr['jersey'] = $player_info['jersey'];
                        $tmp_arr['team_abbr'] = $player_info['team_abbr'];
                        $tmp_arr['position'] = $player_info['position'];
                        $tmp_arr['salary'] = $player_info['salary'];
                        $tmp_arr['is_playing'] = $player_info['is_playing'];
                        $tmp_arr['is_sub'] = $player_info['is_sub'];
                        $tmp_arr['priority'] = $row['priority'];
                        $bench_players[] = $tmp_arr;
                    }
                }
            }

            $team_pl = array();
            foreach($team_data['pl'] as $player_team_id=>$score) {
                $player_info = $roster_list[$player_team_id];
                if(!empty($player_info)) {
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
                    $lineup['player_id'] = $player_info['player_id'];
                    $lineup['player_team_id'] = $player_info['player_team_id'];
                    $lineup['team_id'] = $player_info['team_id'];
                    $lineup['team_uid'] = $player_info['team_uid'];
                    $lineup['full_name'] = $player_info['full_name'];
                    $lineup['display_name'] = $player_info['display_name'];
                    $lineup['jersey'] = $player_info['jersey'];
                    $lineup['team_abbr'] = $player_info['team_abbr'];
                    $lineup['position'] = $player_info['position'];
                    $lineup['salary'] = $player_info['salary'];
                    $lineup['captain'] = $captain;
                    $lineup['is_playing'] = $player_info['is_playing'];
                    $lineup['is_sub'] = $player_info['is_sub'];
                    $lineup['score'] = $score;
                    $lineup['sub_in'] = $sub_in;
                    $team_pl[] = $lineup;
                }
            }
            $team['lineup'] = $team_pl;
            $team['bench'] = $bench_players;
            $team['booster'] = isset($booster_list[$team['booster_id']]) ? $booster_list[$team['booster_id']] : array();
            if(!empty($team['booster'])){
                unset($team['booster']['points']);
                $team['booster']['score'] = $team["booster_points"];
            }
            unset($team['team_data']);
            unset($team['booster_id']);
            unset($team['booster_points']);
            if($u_lmc_id == $team['lineup_master_contest_id']){
                $final_data["you"] = $team;
            }else if($o_lmc_id == $team['lineup_master_contest_id']){
                $final_data["oponent"] = $team;
            }
        }
        $final_data['position'] = $this->get_position_list($sports_id);

        $c_vc = array();
        $c_vc['c_point'] = CAPTAIN_POINT;
        $c_vc['vc_point'] = VICE_CAPTAIN_POINT;
        //dynamic team setting
        $setting = json_decode($fixture['setting'],TRUE);
        if(!empty($setting)){
            if($setting['c'] == "0"){
                $c_vc['c_point'] = "0";
            }
            if($setting['vc'] == "0"){
                $c_vc['vc_point'] = "0";
            }
        }
        $final_data['c_vc'] = $c_vc;
        //echo "<pre>";print_r($final_data);die;
        $this->api_response_arry['data'] = $final_data;
        $this->api_response();
    }

    /**
     * used for get user joined contest leaderboard
     * @param int $contest_id
     * @return array
     */
    public function get_contest_user_leaderboard_teams_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("contest/Contest_model");
        $result = $this->Contest_model->get_contest_user_leaderboard_teams($post_data['contest_id']);
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }
}




