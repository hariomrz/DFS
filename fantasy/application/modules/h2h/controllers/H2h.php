<?php
require_once APPPATH . 'modules/contest/controllers/Contest.php';
class H2h extends Contest 
{
    public $h2h_group_id = 0;
    public $h2h_data = array();
	function __construct()
	{
        parent::__construct();
        $this->contest_lang = $this->lang->line('contest');
        $h2h_challenge = isset($this->app_config['h2h_challenge']) ? $this->app_config['h2h_challenge']['key_value'] : 0;
        if($h2h_challenge == 0)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('module_not_activated');
            $this->api_response();
        }
        else{
            $h2h_data = $this->app_config['h2h_challenge']['custom_data'];
            $this->h2h_group_id = isset($h2h_data['group_id']) ? $h2h_data['group_id'] : 0;
            $this->h2h_data = $h2h_data;
        }
	}

    /**
     * Used for get h2h cms banner list
     * @param int $collection_master_id
     * @return array
     */
    public function get_h2h_cms_post()
    {
        $post_data = $this->input->post();
        $cache_key = "h2h_cms";
        $cms_list = $this->get_cache_data($cache_key);
        if(!$cms_list){
            $this->load->model("h2h/H2h_model");
            $cms_list = $this->H2h_model->get_all_table_data("name,image_name,bg_image",H2H_CMS,array(),array("id"=>"ASC"));
            $this->set_cache_data($cache_key, $cms_list, REDIS_30_DAYS);
        }
        $this->api_response_arry['data'] = $cms_list;
        $this->api_response();
    }

    /**
     * Used for get fixture(match) contest listing
     * @param int $sports_id
     * @param int $collection_master_id
     * @return array
     */
    public function get_h2h_games_post() 
    {
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $cm_id = $post_data['collection_master_id'];
        $cache_key = "fixture_h2h_".$cm_id;
        $h2h_games = $this->get_cache_data($cache_key);
        if(!$h2h_games){
            $this->load->model("h2h/H2h_model");
            $post_data['h2h_group_id'] = $this->h2h_group_id;
            $h2h_games = $this->H2h_model->get_h2h_template($post_data);
            $this->set_cache_data($cache_key,$h2h_games,REDIS_24_HOUR);
        }
        $this->api_response_arry['data'] = $h2h_games;
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
        $lm_id = $post_data['lineup_master_id'];
        $this->load->model("h2h/H2h_model");
        $team_info = $this->H2h_model->get_h2h_team_detail($post_data['lineup_master_id']);
        if(empty($team_info)){
            $this->api_response_arry['message'] = $this->lang->line('invalid_team_for_match');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }else if(strtotime($current_date) >= strtotime($team_info['season_scheduled_date'])){
            $this->api_response_arry['message'] = $this->contest_lang['contest_already_started'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $post_data['collection_master_id'] = $team_info['collection_master_id'];
        $join_data = $this->H2h_model->get_user_h2h_contest_join_count($team_info['collection_master_id']);
        if(!empty($join_data) && isset($join_data['user_joined_count']) && $join_data['user_joined_count'] >= $this->h2h_data['contest_limit']){
            $this->api_response_arry['message'] = str_replace('{CONTEST_LIMIT}',$this->h2h_data['contest_limit'],$this->contest_lang['h2h_game_join_limit_error']);
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $user_win = $this->H2h_model->get_single_row("*",H2H_USERS,array("user_id" => $this->user_id));
        $user_win_count = 0;
        if(!empty($user_win) && isset($user_win['total_win'])){
            $user_win_count = $user_win['total_win'];
        }
        $min = 0;
        $max = 0;
        if($user_win_count >= $this->h2h_data['amateur_min'] && $user_win_count <= $this->h2h_data['amateur_max']){
            $max = $this->h2h_data['amateur_max'];
        }else if($user_win_count >= $this->h2h_data['mid_min'] && $user_win_count <= $this->h2h_data['mid_max']){
            $min = $this->h2h_data['mid_min'];
            $max = $this->h2h_data['mid_max'];
        }else if($user_win_count >= $this->h2h_data['pro_min'] && $user_win_count <= $this->h2h_data['pro_max']){
            $min = $this->h2h_data['pro_min'];
            $max = $this->h2h_data['pro_max'];
        }
        $post_data['min'] = $min;
        $post_data['max'] = $max;
        $contest = $this->H2h_model->check_opponent_game($post_data);
        //echo "<pre>";print_r($contest);die;
        if(empty($contest)){
            $template = $this->H2h_model->get_single_row("*",CONTEST_TEMPLATE,array("contest_template_id" => $post_data['contest_id']));
            $template['contest_unique_id'] = random_string('alnum', 9);
            $template['league_id'] = $team_info['league_id'];
            $template['contest_name'] = $template['template_name'];
            $template['contest_title'] = isset($template['template_title']) ? $template['template_title'] : "";
            $template['collection_master_id'] = $team_info['collection_master_id'];
            $template['season_scheduled_date'] = $team_info['season_scheduled_date'];
            $template['status'] = '0';
            $template['added_date'] = format_date();
            $template['modified_date'] = format_date();
            $payout_data = json_decode($template['prize_distibution_detail'],TRUE);
            $max_prize_pool = 0;
            //change values for percentage case
            if(isset($template['max_prize_pool']) && $template['max_prize_pool'] > 0){
                $max_prize_pool = $template['max_prize_pool'];
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
            $template['max_prize_pool'] = $max_prize_pool;

            if ($template['is_auto_recurring'] == 1) {
                $template['base_prize_details'] = json_encode(array("prize_pool" => $template['prize_pool'], "prize_distibution_detail" => $template['prize_distibution_detail']));
            }
            unset($template['template_name']);
            unset($template['template_title']);
            unset($template['template_description']);
            unset($template['auto_published']);
            $contest_id = $this->H2h_model->save_record(CONTEST,$template);
            if($contest_id){
                $contest = $template;
                $contest['contest_id'] = $contest_id;
                $contest['total_user_joined'] = 0;
                $contest['is_2nd_inning'] = 0;
            }else{
                $this->api_response_arry['message'] = $this->contest_lang['problem_while_join_game'];
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response();
            }
        }
        //echo "<pre>";print_r($contest);die;
        $contest['collection_name'] = $team_info['collection_name'];
        $post_data['contest_id'] = $contest['contest_id'];
        $contest_id = $contest['contest_id'];

        $this->entry_fee = $contest['entry_fee'];
        $this->currency_type = $contest['currency_type'];
        $this->contest_unique_id = $contest['contest_unique_id'];
        $this->contest_entry = array("real"=>"0","winning"=>"0","bonus"=>"0","coin"=>"0");
        $this->promo_code_id = 0;
        $is_promo_code = 0;
        if($this->currency_type != "2"){
            $this->apply_contest_promo_code($post_data);
            $is_promo_code = 1;
        }
        //echo "<pre>";print_r($contest);die;
        $contest['lm_id'] = $lm_id;
        $this->load->model("contest/Contest_model");
        $is_valid = $this->validation_for_join_game($contest);
        if ($is_valid) {
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

                if(isset($joined['joined_count']) && $joined['joined_count'] == $contest['size']) {
                    $joined_users = $this->H2h_model->get_h2h_contest_users($contest['contest_id']);
                    if(!empty($joined_users) && count($joined_users) == 2){
                        $i = 0;
                        foreach($joined_users as $user){
                            $op_usr = $joined_users[0];
                            if($i == 0){
                                $op_usr = $joined_users[1];
                            }
                            $input = array(
                                'contest_name' => $contest['contest_name'],
                                'contest_unique_id' => $contest['contest_unique_id'],
                                'contest_id' => $contest['contest_id'],
                                'entry_fee' => $contest['entry_fee'],
                                'prize_pool' => $contest['prize_pool'],
                                'currency_type' => $contest['currency_type'],
                                'user_name' => $op_usr['user_name'],
                                "collection_name" => (!empty($contest['collection_name'])) ? $contest['collection_name'] : $contest['contest_name']
                            );

                            $notify_data = array();
                            $notify_data["notification_destination"] = 3;//Web
                            $notify_data["notification_type"] = 600; 
                            $notify_data["source_id"] = $joined['lmc_id'];
                            $notify_data["user_id"] = $user['user_id'];
                            $notify_data["added_date"] = $current_date;
                            $notify_data["modified_date"] = $current_date;
                            $notify_data["content"] = json_encode($input);
                            $this->load->model('user/User_nosql_model');
                            $this->User_nosql_model->send_notification($notify_data);
                            $i++;
                        }
                    }
                }else{
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
                }

                //delete user balance data
                $balance_cache_key = 'user_balance_'.$this->user_id;
                $this->delete_cache_data($balance_cache_key);

                //delete user joined count
                $user_ct_cache = "user_ct_".$contest["collection_master_id"]."_".$this->user_id."_0";
                $this->delete_cache_data($user_ct_cache);

                //delete user team list cache
                $teams_cache_key = "user_teams_".$contest["collection_master_id"]."_".$this->user_id;
                $this->delete_cache_data($teams_cache_key);

                $this->api_response_arry['message'] = $this->contest_lang['join_game_success'];
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
     * used for get user joined h2h contest list
     * @param int $collection_master_id
     * @return array
     */
    public function get_user_h2h_contest_post() {
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $cm_id = $post_data['collection_master_id'];

        $this->load->model("h2h/H2h_model");
        $contest_list = $this->H2h_model->get_user_h2h_contest($cm_id);
        //echo "<pre>";print_r($contest_list);die;
        if(!empty($contest_list))
        {
            $user_ids = array_unique(array_column($contest_list,'user_id'));
            $opp_user_ids = array_unique(array_column($contest_list,'opp_user_id'));
            $user_ids = array_unique(array_merge($user_ids,$opp_user_ids));
            $user_ids = array_diff($user_ids,[0]);
            $this->load->model("user/User_model");
            $users = $this->User_model->get_users_by_ids($user_ids);
            if(!empty($users))
            {
                $users = array_column($users,NULL,'user_id');
            }
        }
        //echo "<pre>";print_r($users);die;
        foreach($contest_list as &$contest) {
            $contest["prize_distibution_detail"] = json_decode($contest["prize_distibution_detail"]);
            $own = $users[$contest['user_id']];
            $contest['own'] = array("user_id"=>$own['user_id'],"user_name"=>$own['user_name'],"image"=>$own['image'],"team_name"=>$contest['team_name']);
            $contest['opponent'] = array();
            if(isset($contest['opp_user_id']) && $contest['opp_user_id'] > 0 && isset($users[$contest['opp_user_id']])){
                $oppnt = $users[$contest['opp_user_id']];
                $contest['opponent'] = array("user_id"=>$oppnt['user_id'],"user_name"=>$oppnt['user_name'],"image"=>$oppnt['image'],"team_name"=>$contest['opp_team_name'],"total"=>$contest['total'],"win"=>$contest['total_win']);
            }
            unset($contest["user_id"]);
            unset($contest["team_name"]);
            unset($contest["opp_user_id"]);
            unset($contest["opp_team_name"]);
        }
        $this->api_response_arry['data'] = $contest_list;
        $this->api_response();
    }
}
