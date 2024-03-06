<?php
class Tournament extends Common_Api_Controller 
{
	function __construct()
	{
        parent::__construct();
	}

    /**
     * Used for get lobby tournament list
     * @param int $sports_id
     * @return array
     */
    public function get_lobby_tournament_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|is_natural_no_zero|max_length[2]');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data["sports_id"];
        $pin_tournament_cache = 'pin_tournament_'.$sports_id;
        $pin_tournament = $this->get_cache_data($pin_tournament_cache);
        if(!$pin_tournament){
            $this->load->model("tournament/Tournament_model");
            $pin_tournament = $this->Tournament_model->get_pin_tournament($sports_id);
            $this->set_cache_data($pin_tournament_cache,$pin_tournament,300);
        }
        $user_tournament = array();
        if($this->user_id){
            $user_tournament_cache = 'user_lobby_tr_'.$sports_id.'_'.$this->user_id;
            $user_tournament = $this->get_cache_data($user_tournament_cache);
            if(!$user_tournament){
                $this->load->model("tournament/Tournament_model");
                $user_tournament = $this->Tournament_model->get_user_tournament($sports_id);
                $this->set_cache_data($user_tournament_cache,$user_tournament,600);
            }
        }
        if(!empty($pin_tournament)){
            $pin_tournament['is_joined'] = "0";
            $pin_tournament['rank_value'] = "-";
            if($pin_tournament['contest_id'] > 0 && $this->user_id){
                $this->load->model("tournament/Tournament_model");
                $rank = $this->Tournament_model->get_single_row("IF(rank_value=0,'-',rank_value) as rank_value,rank_value as rk",TOURNAMENT_HISTORY,array("tournament_id"=>$pin_tournament['tournament_id'],"user_id"=>$this->user_id));
                if(!empty($rank) && $rank['rk'] > 0){
                    $pin_tournament['is_joined'] = 1;
                    $pin_tournament['rank_value'] = $rank['rank_value'];
                }
            }
        }
        $this->api_response_arry['data']['pin'] = $pin_tournament;
        $this->api_response_arry['data']['user'] = $user_tournament;
        $this->api_response();
    }

    /**
     * Used for get tournament list
     * @param int $sports_id
     * @return array
     */
    public function get_tournament_list_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|is_natural_no_zero|max_length[2]');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("tournament/Tournament_model");
        $result = $this->Tournament_model->get_tournament_list($post_data);
        $this->api_response_arry['data'] = $result;
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

        $post_data = $this->input->post();
        $this->load->model("tournament/Tournament_model");
        $result = $this->Tournament_model->get_featured_tournament_list($post_data);
        $this->api_response_arry['data'] = $result;
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
        $this->load->model("tournament/Tournament_model");
        $record_info = $this->Tournament_model->get_tournament_details($tournament_id);
        if(!empty($record_info)){
            $record_info['match'] = $this->get_tournament_fixture($tournament_id,$record_info['sports_id']);
            //$record_info['rules'] = $this->get_scoring_rule();
            $contest_ids = array_column($record_info['match'],"contest_id");
            //get data if game closed and save entry in history table
            $user_data = array();
            if($record_info['joined_id'] > 0){
                $user_data = $this->Tournament_model->get_user_joined_data($tournament_id,$this->user_id);
                if(!empty($user_data)){
                    $user_data = array_column($user_data,NULL,"cm_id");
                    $user_contest = array_column($user_data,"contest_id");
                    $contest_ids = array_diff($contest_ids, $user_contest);
                }
            }
            $game_data = array();
            if(!empty($contest_ids)){
                $game_data = $this->Tournament_model->get_user_join_contest_data($contest_ids);
                if(!empty($game_data)){
                    $game_data = array_column($game_data,NULL,"cm_id");
                }
            }
            $record_info['user_game'] = array_replace_recursive($user_data,$game_data);
            $record_info['user_id'] = $this->user_id;
        }

        $this->api_response_arry['data'] = $record_info;
        $this->api_response();
    }

    private function get_scoring_rule(){
        if($this->app_config['int_version']['key_value']==1){ 
           $how_to_join = array("Join the available leaderboard contest of the tournament.","When you join the tournament contest in the match, you automatically become the part of the respective tournament.");
            $scoring_rule = array("A total score of all the valid teams joined is taken.","Leaderboard will be generated based on the fantasy points earned in total for the selected tournament.");
        }else{
            $how_to_join = array("Join the available leaderboard contest of the tournament.","When you join the tournament contest in the match, you automatically become the part of the respective tournament.");
            $scoring_rule = array("A total score of all the valid teams joined is taken.","Leaderboard will be generated based on the fantasy points earned in total for the selected tournament.");
        }
        
        $rules_arr = array();
        $rules_arr['0'] = array();
        $rules_arr['0']['name'] = "What's a DFS Tournament?";
        $rules_arr['0']['rule'] = array("Participants compete against each other in a series of matches with an aim of leading in fantasy points. The participant with highest fantasy points tops the leaderboard");
        $rules_arr['1'] = array();
        $rules_arr['1']['name'] = "How to join?";
        $rules_arr['1']['rule'] = $how_to_join;
        $rules_arr['2'] = array();
        $rules_arr['2']['name'] = "Leaderboard";
        $rules_arr['2']['rule'] = $scoring_rule;
        return $rules_arr;
    }

    private function get_tournament_fixture($tournament_id,$sports_id){
        $cache_key = "trnt_fixture_".$tournament_id;
        $fixture = $this->get_cache_data($cache_key);
        if(!$fixture){
            $this->load->model("tournament/Tournament_model");
            $fixture = $this->Tournament_model->get_tournament_fixture_list($tournament_id,$sports_id);
            $this->set_cache_data($cache_key,$fixture,3600);
        }
        return $fixture;
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
        //echo "<pre>";print_r($result);die;
        if(!empty($result)){
            $result = array_column($result,NULL,"user_id");
            $user_ids = array_keys($result);
            $this->load->model('user/User_model');
            $user_data = $this->User_model->get_user_detail_by_user_id($user_ids);
            $user_data = array_column($user_data,NULL,"user_id");
            if(!empty($user_data)){
                $result = array_values(array_replace_recursive($result,$user_data));
            }
        }
        $own_result = array();
        if(isset($post_data['page_no']) && $post_data['page_no'] == "1"){
            $own_result = $this->Tournament_model->get_single_row("history_id,user_id,total_score,IF(rank_value=0,'-',rank_value) as rank_value,is_winner,IFNULL(prize_data,'[]') as prize_data,'' as name,'' as user_name,'' as image",TOURNAMENT_HISTORY,array("tournament_id"=>$post_data['tournament_id'],"user_id"=>$this->user_id));
            $this->load->model('user/User_model');
            $own_user = $this->User_model->get_user_detail_by_user_id(array($this->user_id));
            $own_user = array_column($own_user,NULL,"user_id");
            if(isset($own_user) && !empty($own_user)){
                $own_user_details=isset($own_user[$this->user_id])?$own_user[$this->user_id]:array();
                $own_result = array_merge($own_result,$own_user_details);
            }
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
        $this->form_validation->set_rules('history_id', "id", 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("tournament/Tournament_model");
        $record_info = $this->Tournament_model->get_user_history_details($post_data['history_id']);
        if(!empty($record_info)){
            $user_id = $record_info['user_id'];
            $tournament_id = $record_info['tournament_id'];
            $sports_id = $record_info['sports_id'];
            $this->load->model('user/User_model');
            $record_info['user_data'] = $this->User_model->get_user_detail_by_user_id($user_id);
            $record_info['match'] = $this->get_tournament_fixture($tournament_id,$sports_id);
            $user_data = $this->Tournament_model->get_user_joined_data($tournament_id,$user_id);
            if(!empty($user_data)){
                $user_data = array_column($user_data,NULL,"cm_id");
            }
            $record_info['user_game'] = $user_data;
        }
        $this->api_response_arry['data'] = $record_info;
        $this->api_response();
    }

    /**
    * Function used for get user points details
    * @param int $leaderboard_id
    * @return string
    */
    public function get_user_fixture_teams_post()
    {
        $this->form_validation->set_rules('tournament_id', "tournament id", 'trim|required');
        $this->form_validation->set_rules('cm_id', "match id", 'trim|required');
        $this->form_validation->set_rules('user_id', "user id", 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $tournament_id = $post_data['tournament_id'];
        $cm_id = $post_data['cm_id'];
        $user_id = $post_data['user_id'];
        $this->load->model("tournament/Tournament_model");
        $team_list = $this->Tournament_model->get_all_table_data("tournament_id,cm_id,lmc_id,lm_id,team_name,score,game_rank,is_included",TOURNAMENT_HISTORY_TEAMS, array("tournament_id" => $tournament_id,"cm_id"=>$cm_id,"user_id"=>$user_id),array("game_rank"=>"ASC"));
        $this->api_response_arry['data'] = $team_list;
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
        $sports_list_cache = 'dfs_tournament_sports_list';
        $sports_ids = $this->get_cache_data($sports_list_cache);
        if(!$sports_ids){
            $this->load->model("tournament/Tournament_model");
            $sports_ids = $this->Tournament_model->get_sports_list_leaderboard();
            $this->set_cache_data($sports_list_cache,$sports_ids,3600); // 1 hour cache set
        }
        
        $this->api_response_arry['data'] = $sports_ids;
        $this->api_response(); 
    }

}
