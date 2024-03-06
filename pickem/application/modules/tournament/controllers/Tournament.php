<?php
class Tournament extends Common_Api_Controller 
{   
    var $self_exclusion_limit = 0;
	function __construct()
	{
        parent::__construct();
	}

    /**
     * Used for get tournament list
     * @param int $sports_id
     * @return array
     */
    public function get_lobby_tournament_list_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|max_length[2]');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        if(empty($this->input->post('sports_id'))){
            $sports_id = 0;
        }else{
            $sports_id = $this->input->post('sports_id');
        }


        //$lobby_tournament_cache_key = 'lobby_tournament_'.$sports_id;
        //$tournament_list = $this->get_cache_data($lobby_tournament_cache_key);
       // if(!$tournament_list){
            $this->load->model("tournament/Tournament_model");
            $tournament_list = $this->Tournament_model->get_tournament_list($sports_id);
            //echo '<pre>';print_r($tournament_list);die;
            if(!empty($tournament_list)){
                // foreach ($tournament_list as $key => $value) {
                //     if($this->user_id && $this->user_id==$value['user_id']){
                //         unset($tournament_list[$key]);
                //     }
                //     unset($tournament_list[$key]['joined_count']);
                //     unset($tournament_list[$key]['user_id']);
                // }
                $tournament_list = array_values($tournament_list);
               // $this->set_cache_data($lobby_tournament_cache_key,$tournament_list,REDIS_5_MINUTE);
            //}

        }

        $this->api_response_arry['data'] = $tournament_list;
        $this->api_response();
    }


     /**
     * Used for get tournament list
     * @param int $sports_id
     * @return array
     */
    public function get_featured_tournament_list_post()
    {
        $this->form_validation->set_rules('league_id', $this->lang->line('league_id'), 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        if(empty($this->input->post('league_id'))){
            $league_id = 0;
        }else{
            $league_id = $this->input->post('league_id');
        }


            $this->load->model("tournament/Tournament_model");
            $tournament_list = $this->Tournament_model->get_featured_tournament_list($league_id);
            //echo '<pre>';print_r($tournament_list);die;
            if(!empty($tournament_list)){
                // foreach ($tournament_list as $key => $value) {
                //     if($this->user_id && $this->user_id==$value['user_id']){
                //         unset($tournament_list[$key]);
                //     }
                //     unset($tournament_list[$key]['joined_count']);
                //     unset($tournament_list[$key]['user_id']);
                // }
                $tournament_list = array_values($tournament_list);
             

        }

        $this->api_response_arry['data'] = $tournament_list;
        $this->api_response();
    }


    /**
     * @method Get My Lobby tournament
     * @param sports_id
     * @return json array
     */
    public function get_my_lobby_tournament_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|max_length[2]');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $sports_id = $this->input->post('sports_id');
        $this->load->model("tournament/Tournament_model");
        $user_tournament = array();
        if($this->user_id){
            $user_tournament = $this->Tournament_model->get_user_tournament($sports_id);
        }
        $this->api_response_arry['data']['tournament'] = $user_tournament;
        $this->api_response();

    }


   /**
    * Used for get my joined tournaments
    * @param int $tournament_id
    * @return array
    */
    public function get_my_contest_tournament_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|max_length[2]');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("Tournament_model");
        $tournament_list = $this->Tournament_model->get_my_tournament($post_data);

        $this->api_response_arry['data'] = $tournament_list;
        $this->api_response();
    
    }

   /**
    * Used for get tournament details
    * @param int $tournament_id
    * @return array
    */
    public function get_tournament_details_post() 
    {
        $this->form_validation->set_rules('tournament_id', $this->lang->line('tournament_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $tournament_id = $post_data['tournament_id'];
        $cache_key = "tournament_id_".$tournament_id;
        //$tournament_details = $this->get_cache_data($cache_key);
        //if(!$tournament_details){
            $this->load->model("tournament/Tournament_model");
            $tournament_details = $this->Tournament_model->get_tournament_details($tournament_id);
            if(!empty($tournament_details)){
                $season_ids = array_unique(explode(",",$tournament_details['season_ids']));
                $match_list = $this->Tournament_model->get_fixture_season_detail($season_ids);
                $match_list = array_column($match_list,NULL, 'season_id');

                // match_list with prediction percentasge
                $season_ids =array_column($match_list, 'season_id');
                $team_percentage_list = $this->Tournament_model->get_team_prediction_percent($tournament_id,$season_ids); 
                $team_percentage_list = array_column($team_percentage_list, NULL,'season_id'); 
                
                if(empty($team_percentage_list)){
                     $season_ids =  explode(',', $tournament_details['season_ids']); 
                     foreach ($season_ids as $key => $value) {
                        $team_percentage_list[$value]['home_count'] = 0;
                        $team_percentage_list[$value]['away_count'] = 0;
                        $team_percentage_list[$value]['draw_count'] = 0;
                        $team_percentage_list[$value]['total_season_count'] = 0;
                     }
                }    
                    //echo '<pre>';print_r($team_percentage_list);die;

                $user_tournament_details = $this->Tournament_model->get_prediction_detail($tournament_id);
                $user_tournament_details = array_column($user_tournament_details, NULL,'season_id');

                $tournament_details['match'] = array_values(array_replace_recursive($match_list,$team_percentage_list,$user_tournament_details)); //merge both the arrays
                
                //echo '<pre>';print_r($tournament_details);die;
                $points =array();
                $points['correct'] = $this->app_config['allow_pickem_tournament']['custom_data']['correct'];
                $points['wrong']   = $this->app_config['allow_pickem_tournament']['custom_data']['wrong'];
                $tournament_details['points'] = $points;
            }
            unset($tournament_details['season_ids']);
            //$this->set_cache_data($cache_key,$tournament_details,REDIS_5_MINUTE);
        //}

        $this->api_response_arry['data'] = !empty($tournament_details)?$tournament_details:[];
        $this->api_response();
    }

   /**
    * Submit Pickem tournament answer
    * @param  season_id,team_id,user_tournament_id
    * @return string message
    */
    public function submit_pickem_post()
    {
        $this->form_validation->set_rules('user_tournament_id', $this->lang->line('user_tournament_id'), 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('tournament_id', $this->lang->line('tournament_id'), 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('team_id', $this->lang->line('team_id'), 'trim|required');
        $this->form_validation->set_rules('score_predict', $this->lang->line('score_predict'), 'trim');
        
        $post_data = $this->input->post();
        $is_reset = isset($post_data['is_reset'])?$post_data['is_reset']:0;
        if(isset($post_data['score_predict']) && !empty($post_data['score_predict'])){
            $this->form_validation->set_rules('home_predict', $this->lang->line('home_predict'), 'trim|required');
            $this->form_validation->set_rules('away_predict', $this->lang->line('away_predict'), 'trim|required');
        }

        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        //$tournament_cache_key = "tournament_id_".$post_data['tournament_id'];
        
        $this->load->model('tournament/Tournament_model');
        $season_data = $this->Tournament_model->get_single_row('scheduled_date',SEASON,['season_id'=>$post_data['season_id']]);
        $current_date = format_date();
        if(!empty($season_data) && $current_date > $season_data['scheduled_date']){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR; 
            $this->api_response_arry['message'] = $this->lang->line('tournament_already_started'); 
            $this->api_response(); 
        }

        $check_exist = $this->Tournament_model->get_single_row('user_team_id,team_id',USER_TEAM,['season_id'=>$post_data['season_id'],'user_tournament_id'=>$post_data['user_tournament_id']]);
        if(!empty($check_exist)) {
            $delete =  $this->Tournament_model->delete_row(USER_TEAM,['user_team_id'=>$check_exist['user_team_id']]);
            if(!empty($is_reset) || (empty($post_data['score_predict'])  && $post_data['team_id'] == $check_exist['team_id']) ){
                //$this->delete_cache_data($tournament_cache_key);
                $this->api_response_arry['message'] = $this->lang->line('pickem_success'); 
                $this->api_response();
            }
        }
        $user_team = array();
        if(isset($post_data['score_predict']) && !empty($post_data['score_predict'])){
            $user_team['home_predict']=isset($post_data['home_predict'])?$post_data['home_predict']:0;
            $user_team['away_predict']=isset($post_data['away_predict'])?$post_data['away_predict']:0;
        }
        $user_team['user_tournament_id'] = $post_data['user_tournament_id'];
        $user_team['season_id'] = $post_data['season_id'];
        $user_team['team_id'] = $post_data['team_id'];
        $user_team['created_date']  = format_date();
        $user_team['modified_date'] = format_date();
        
        $insert_id = $this->Tournament_model->save_record(USER_TEAM,$user_team);
        $this->api_response_arry['data']['user_team_id'] = $insert_id;
         
        //$this->delete_cache_data($tournament_cache_key);
        $this->api_response_arry['message'] = $this->lang->line('pickem_success');
        $this->api_response();
    }

    /**
     * Save tie breaker answer
     * @param user_tournament_id
     * @return string message
     */
    public function save_tie_breaker_answer_post()
    {
        $this->form_validation->set_rules('user_tournament_id', $this->lang->line('user_tournament_id'), 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('tournament_id', $this->lang->line('tournament_id'), 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('answer', 'Answer', 'trim|required');
       
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();

        $this->load->model('tournament/Tournament_model');
        $update = $this->Tournament_model->update(USER_TOURNAMENT,['tie_breaker_answer'=>$post_data['answer']],['user_tournament_id'=>$post_data['user_tournament_id']] );

        //$tournament_cache_key = "tournament_id_".$post_data['tournament_id'];
        //$this->delete_cache_data($tournament_cache_key);

         $this->api_response_arry['message'] = $this->lang->line('pickem_success');
         $this->api_response();
    }


    /**
     * used for validate tournament join data
     * @param array $tournament
     * @param array $post_data
     * @return array
     */
    public function validation_for_join_tournament($tournament) {
        if (empty($tournament)) {
            $this->message = $this->tournament_lang['invalid_tournament'];
            return 0;
        }

        //for manage collection wise deadline
        $current_date = format_date();
        //check for match schedule date
        // if (strtotime($tournament['start_date']) < strtotime($current_date)) {
        //     $this->message = $this->lang->line('tournament_already_started');
        //     return 0;
        // }

        //if tournament closed
        if ($tournament['status'] !== '0') {
            $this->message = $this->lang->line('tournament_closed');
            return 0;
        }

        if ($this->entry_fee == '0') {
            return 1;
        }

        $already_joined = $this->Tournament_model->get_single_row('user_tournament_id',USER_TOURNAMENT,['tournament_id'=>$tournament['tournament_id'],'user_id'=>$this->user_id]);
        if(!empty($already_joined)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('tournament_already_joined');
            $this->api_response();
        }

        $this->load->model("user/User_model");
        $balance_arr = $this->User_model->get_user_balance($this->user_id);
        $this->user_bonus_bal = $balance_arr['bonus_balance'];
        $this->user_bal       = $balance_arr['balance'];
        $this->winning_bal    = $balance_arr['winning_balance'];
        $this->point_balance  = $balance_arr['point_balance'];
       
        if ($this->currency_type == 2 ) { // for Conins 
            if ($this->entry_fee > $this->point_balance) {
                $this->message = $this->lang->line('not_enough_coins');
                $this->enough_coins = 0;
                return 0;
            }
            $this->tournament_entry['coin'] = $this->entry_fee;
        } else {
            //get user balance
            $bonus_amount = $max_bonus = 0;

            if($this->currency_type != "2" && $this->entry_fee > 0){
                $banned_state = $this->get_banned_state();
                $banned_state_ids = array_column($banned_state,"state_id");
                if(!isset($balance_arr['master_state_id']) || $balance_arr['master_state_id'] == ""){
                    $this->message = $this->tournament_lang['state_required_error'];
                    return 0;
                }else if(in_array($balance_arr['master_state_id'], $banned_state_ids)){
                    $state_str = implode(", ",$banned_state);
                    $this->message = str_replace("{{STATE_LIST}}",$state_str,$this->lang->line('state_banned_error'));
                    return 0;
                }
            }
            
            if($this->currency_type == "2"){
                if ($this->entry_fee > $this->point_balance) {
                    $this->message = $this->lang->line('not_enough_balance');
                    $this->enough_balance = 0;
                    return 0;
                }
            }else{
                //$max_bonus_percentage = MAX_BONUS_PERCEN_USE;
                if ($tournament['max_bonus']) {
                    $max_bonus_percentage = $tournament['max_bonus'];
                    $max_bonus = ($this->entry_fee * $max_bonus_percentage) / 100;

                    $bonus_amount = $max_bonus;
                }
                if ($max_bonus > $this->user_bonus_bal) {
                    $bonus_amount = $this->user_bonus_bal;
                }
               
                if(MAX_CONTEST_BONUS > 0 && $bonus_amount > MAX_CONTEST_BONUS) {
                    $bonus_amount = MAX_CONTEST_BONUS;
                }
                //echo $this->entry_fee.'--'.$bonus_amount.'--'.$this->user_bal.'--'.$this->winning_bal;die;
                if ($this->entry_fee > ($bonus_amount + $this->user_bal + $this->winning_bal)) {
                    $this->message = $this->lang->line('not_enough_balance');
                    $this->enough_balance = 0;
                    return 0;
                }

                $amount = $this->entry_fee - $bonus_amount;
                if($amount > $this->user_bal) {
                    $real = $this->user_bal;
                    $amount = $amount - $real;
                }else{
                    $real = $amount;
                    $amount = $amount - $real;
                }
                    $winning = $amount;
                    $this->tournament_entry['bonus'] = $bonus_amount;
                    $this->tournament_entry['real'] = $real;
                    $this->tournament_entry['winning'] = $winning;
                }
            }

        if($this->currency_type == "1"){
            $this->self_exclusion_limit = 0;
            $allow_self_exclusion = isset($this->app_config['allow_self_exclusion'])?$this->app_config['allow_self_exclusion']['key_value']:0;    

            if($allow_self_exclusion) {
                $custom_data = $this->app_config['allow_self_exclusion']['custom_data'];
                $default_max_limit = $custom_data['default_limit'];
                $tournament_ids = $this->Tournament_model->user_join_tournament_ids($this->user_id);   

                if(!empty($tournament_ids)) {
                    $this->load->model("user/User_model");
                    $self_exclusion_data = array('user_id' => $this->user_id, 'tournament_ids' => $tournament_ids, 'max_limit' => $default_max_limit, 'entry_fee' => $this->entry_fee);
                    $this->message = 'Self Exclusion limit reached';
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
     * @method used for Join Tournament
     * @param tournament_id
     * @return user_tournament_id with string message
     */
    public function join_tournament_post() {
        $this->form_validation->set_rules('tournament_id', $this->lang->line('tournament_id'), 'trim|required|numeric');
       
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $tournament_id = $this->input->post('tournament_id');
        $current_date = format_date();
        //get Tournament details
        $this->load->model("Tournament_model");
        $tournament_details =$this->Tournament_model->get_tournament_details($tournament_id);
        $this->entry_fee = $tournament_details['entry_fee'];
        $this->currency_type = $tournament_details['currency_type'];
        $this->tournament_entry = array("real"=>0,"winning"=>"0","bonus"=>"0","coin"=>"0");

        $is_valid = $this->validation_for_join_tournament($tournament_details);
        if (!$is_valid) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->message;
            $this->api_response();
        }

        $this->load->model('user/User_model');

        if ($is_valid) {
            if ($is_valid || $this->entry_fee == 0) {
                $joined = $this->Tournament_model->join_tournament($tournament_details);
                if (isset($joined['user_tournament_id']) && $this->entry_fee > 0) {
                    $withdraw = array();
                    $custom_data = ['name'=>$tournament_details['name'],'entry_fee'=>$this->entry_fee];
                    $withdraw["source"] = TOURNAMENT_JOIN_SOURCE;
                    $withdraw["source_id"] = $joined['user_tournament_id'];
                    $withdraw["reason"] = "for tournament joining";
                    $withdraw["user_id"] = $this->user_id;
                    $withdraw["currency_type"] = $this->currency_type;
                    $withdraw["reference_id"] = $tournament_id;
                    $withdraw['real_amount'] = $this->tournament_entry['real'];
                    $withdraw['bonus_amount'] = $this->tournament_entry['bonus'];
                    $withdraw['winning_amount'] = $this->tournament_entry['winning'];
                    $withdraw['points'] = $this->tournament_entry['coin'];
                    $withdraw["custom_data"] = $custom_data;
                    $this->load->model("user/User_model");
                    $join_result = $this->User_model->deduct_entry_fee($withdraw);
                    if (empty($join_result) || $join_result['result'] == 0) {
                        //remove user joined entry
                        $this->Tournament_model->remove_joined_tournament( $joined['user_tournament_id']);
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] =  $this->lang->line('problem_while_join_game');
                        $this->api_response();
                    }

                    //delete user balance data
                    $balance_cache_key = 'user_balance_'.$this->user_id;
                    $this->delete_cache_data($balance_cache_key);

                }
                //Send Notification
                $input = array('name' => $tournament_details['name'],'tournament_id' => $tournament_id,'start_date'=>$tournament_details['start_date'],'entry_fee'=>$this->entry_fee);
                $notify_data = array();
                $notify_data["notification_type"] = TOURNAMENT_JOIN_NOTIFY; //650-JoinGame, 
                $notify_data["source_id"] = $joined['user_tournament_id'];
                $notify_data["notification_destination"] = 5; //Web,Email
                $notify_data["user_id"] = $this->user_id;
                $notify_data["to"] = $this->email;
                $notify_data["user_name"] = $this->user_name;
                $notify_data["added_date"] = $current_date;
                $notify_data["modified_date"] = $current_date;
                $notify_data["content"] = json_encode($input);
                $notify_data["subject"] = 'Joined Tournament';
                $this->load->model('user/User_nosql_model');
                $this->User_nosql_model->send_notification($notify_data);

            }
        }

        $cache_key = "tournament_id_".$tournament_id;
        $this->delete_cache_data($cache_key);

        $this->api_response_arry['message'] = $this->lang->line('join_tournament_success');
        $this->api_response_arry['data'] = $joined;
        $this->api_response();
    }

    /**
    * Function used for get tournament leaderboard
    * @param array $post_data
    * @return array
    */
    public function get_leaderboard_post()
    {
        $this->form_validation->set_rules('tournament_id', 'Tournament id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $this->load->model("tournament/Tournament_model");
        $result = $this->Tournament_model->get_leaderboard($post_data);
        $own_result = $this->Tournament_model->get_single_row("user_tournament_id,user_id,total_score,IF(game_Rank=0,'-',game_Rank) as game_rank,is_winner,bonus,amount,coin,merchandise,'' as name,'' as user_name,'' as image,user_id",USER_TOURNAMENT,array("tournament_id"=>$post_data['tournament_id'],"user_id"=>$this->user_id));
        //echo "<pre>";print_r($result);die;
        $own_user_data = array();
        if(!empty($result)){
            $result = array_column($result,NULL,"user_id");
            $user_ids = array_keys($result);
            if(!empty($own_result)){
                $user_ids[] = $this->user_id;    
            }
            $this->load->model('user/User_model');
            $user_data = $this->User_model->get_user_detail_by_user_id($user_ids);
            $user_data = array_column($user_data,NULL,"user_id");
            if(!empty($user_data)){
                if(isset($user_data[$this->user_id])){
                    $own_user_data = $user_data[$this->user_id];
                }
                if(!array_key_exists($this->user_id,$result)){
                    unset($user_data[$this->user_id]);
                }
                $result = array_values(array_replace_recursive($result,$user_data));
            }
        // echo '<pre>';print_r($result);die;
        }
        
        if(!empty($own_result)){
            $own_result['image'] = $own_user_data['image'];
        }
        
        //echo "<pre>";print_r($result);die;
        $this->api_response_arry['data']['users'] = $result;
        $this->api_response_arry['data']['own'] = $own_result;
        $this->api_response();
    }



    /**
    * Function used for get user points details
    * @param int $leaderboard_id
    * @return string
    */
    public function get_user_team_detail_post()
    {
        $this->form_validation->set_rules('user_tournament_id', "User Tournament Id", 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $user_tournament_id = $this->input->post('user_tournament_id');
        $this->load->model("tournament/Tournament_model");
        $record_info = $this->Tournament_model->get_user_tournament_detail($user_tournament_id);
        
        $this->api_response_arry['data'] = $record_info;
        $this->api_response();
    }

   /**
    * Get tournament details for Footer leaderboard
    * @param void
    */
    public function get_tournament_leaderboard_post()
    {   
        $this->form_validation->set_rules('sports_id',$this->lang->line('sports_id'), 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $sports_id = $this->input->post('sports_id');
        $post_data = $this->input->post();
        $is_previous=isset($post_data['is_previous'])?$post_data['is_previous']:0;
        $this->load->model("tournament/Tournament_model");
        $record_info = $this->Tournament_model->get_tournament_leaderboard_detail($sports_id,$is_previous);
        $this->api_response_arry['data'] = $record_info;
        $this->api_response(); 
    }

    /**
    * Get sport list for Footer leaderboard
    * @param void
    */
    public function get_sports_list_leaderboard_post()
    {   
        $sports_list_cache = 'pickem_tournament_sports_list';
        $sports_ids = $this->get_cache_data($sports_list_cache);
        if(!$sports_ids){
            $this->load->model("tournament/Tournament_model");
            $sports_ids = $this->Tournament_model->get_sports_list_leaderboard();
            $this->set_cache_data($sports_list_cache,$sports_ids,3600); // 1 hour cache set
        }
        
        $this->api_response_arry['data'] = $sports_ids;
        $this->api_response(); 
    }

    /**
    * Used for get tournament fixture participants
    * @param int $tournament_id
    * @param int $season_id
    * @return array
    */
    public function get_fixture_users_post() 
    {
        $this->form_validation->set_rules('tournament_id', $this->lang->line('tournament_id'), 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $tournament_id = $post_data['tournament_id'];
        $season_id = $post_data['season_id'];
        $this->load->model("tournament/Tournament_model");
        $season_data = $this->Tournament_model->get_single_row('scheduled_date',TOURNAMENT_SEASON,['tournament_id'=>$tournament_id,'season_id'=>$season_id]);
        if(empty($season_data)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid fixture id.";
            $this->api_response();
        }else if(!empty($season_data) && $season_data['scheduled_date'] > $current_date){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Please wait till the match start to view participants detail.";
            $this->api_response();
        }
        
        $cache_key = "pkem_fixture_".$tournament_id."_".$season_id;
        $fixture_users = $this->get_cache_data($cache_key);
        if(!$fixture_users){
            $fixture_users = $this->Tournament_model->get_fixture_users($post_data);
            //echo "<pre>";print_r($fixture_users);die;
            if(!empty($fixture_users)){
                $fixture_users = array_column($fixture_users,NULL,"user_id");
                $user_ids = array_keys($fixture_users);
                $this->load->model('user/User_model');
                $user_data = $this->User_model->get_user_detail_by_user_id($user_ids);
                $user_data = array_column($user_data,NULL,"user_id");
                if(!empty($user_data)){
                    $fixture_users = array_values(array_replace_recursive($fixture_users,$user_data));
                    $fixture_users = array_values($fixture_users);
                }
            }
            $this->set_cache_data($cache_key,$fixture_users,REDIS_24_HOUR);
        }

        $this->api_response_arry['data'] = $fixture_users;
        $this->api_response();
    }

    /**
    * Function used for get user match history
    * @param int $user_tournament_id
    * @return string
    */
    public function get_user_team_history_post()
    {
        $this->form_validation->set_rules('user_tournament_id', "id", 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $user_tournament_id = $post_data['user_tournament_id'];
        $this->load->model("tournament/Tournament_model");
        $record_info = $this->Tournament_model->get_user_tournament_details($user_tournament_id);
        if(!empty($record_info)){
            $record_info['match'] = $this->Tournament_model->get_user_history_details($user_tournament_id);

            $this->load->model('user/User_model');
            $record_info['user_data'] = $this->User_model->get_user_detail_by_user_id($record_info['user_id']);
        }
        $this->api_response_arry['data'] = $record_info;
        $this->api_response();
    }

}
