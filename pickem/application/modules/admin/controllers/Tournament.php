<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tournament extends Common_Api_Controller {

    public function __construct() {
        parent::__construct();
        $this->admin_roles_manage($this->admin_id,'pickem_tournament');
		$this->load->model('admin/Tournament_model');
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
        $currency_code = isset($this->app_config['currency_code'])?$this->app_config['currency_code']['key_value']:'';
        $currency_type = array(array("label"=>$currency_code,"value"=>"1"));
        $allow_coin_system =  isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
        if($allow_coin_system == 1){
            $prize_type[] = array("label"=>"Coins","value"=>"2");
            $currency_type[] = array("label"=>"Coins","value"=>"2");
        }
        $prize_type[] = array("label"=>"Merchandise","value"=>"3");
        
        $result = array('prize_type'=> $prize_type,'currency_type'=>$currency_type);

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
     * Used for get league fixture list
     * @param int $league_id
     * @return json array
     */
    public function get_fixture_list_post()
    {
        $this->form_validation->set_rules('league_id', 'league id','trim|required');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $result = $this->Tournament_model->get_fixture_list($post_data['league_id']);
        
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
        $this->form_validation->set_rules('currency_type', 'Currency Type', 'trim|required');

        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();

        $end_date = isset($post_data['end_date']) ? $post_data['end_date'] :'';

        $season_ids = isset($post_data['season_ids']) ? $post_data['season_ids'] : array();
        try{

            if(empty($season_ids) || count($season_ids) < 1) { 
                throw new Exception('Please select at least one fixtures.'); 
            }

            if(!isset($post_data['prize_detail']) || empty($post_data['prize_detail'])) { 
                 throw new Exception("Prize distribution details can't empty.");  
            }

            if(!empty($post_data['banner_images']) && count($post_data['banner_images']) > 5 ) {
                throw new Exception("Tournament banner images can not be more than 5"); 
            }
           
            $record_info = $this->Tournament_model->get_single_row('*',TOURNAMENT,array('name' => trim($post_data['name'])));
            if(!empty($record_info)){
                throw new Exception("Tournament name already exist."); 
            }

            $match_list = $this->Tournament_model->get_all_table_data("season_id,scheduled_date",SEASON,array("season_id IN (".implode(',',$season_ids).")"=>NULL),array("scheduled_date"=>"ASC"));
            if(count($match_list) != count($season_ids)){
                throw new Exception("Invalid selected match ids."); 
            }          

            $match_list = array_column($match_list,"scheduled_date","season_id");
            $schedule_dates = array_values($match_list);

            if(!empty($end_date) && $end_date > max($schedule_dates)){
                $post_data['end_date'] = $end_date;
            }else{
                $post_data['end_date'] = max($schedule_dates);
            }

            $post_data['start_date'] = min($schedule_dates);
            // $post_data['end_date'] = max($schedule_dates);
            $post_data['season_ids'] = $match_list;
            
            if($post_data['sports_id'] == SOCCER_SPORTS_ID){
                $pickem_custom_data = !empty($this->app_config['allow_pickem_tournament'])?$this->app_config['allow_pickem_tournament']['custom_data']:array();
                $post_data['is_score_predict']=isset($pickem_custom_data['score_predictor'])?$pickem_custom_data['score_predictor']:0;
            }
            
            // echo "<pre>";print_r($post_data);die;
            $tournament_id = $this->Tournament_model->save_tournament($post_data);
            if(!$tournament_id){
                throw new Exception($this->lang->line("tournament_save_error")); 
            }
        $lobby_tournament_cache_key = 'lobby_tournament_'.$post_data['sports_id'];
        $this->delete_cache_data($lobby_tournament_cache_key);

        $sports_list_cache_key = "pickem_tournament_sports_list";
        $this->delete_cache_data($sports_list_cache_key);
        
        $this->api_response_arry['message'] = $this->lang->line("tournament_save_success");
        $this->api_response_arry['data'] = array("tournament_id"=>$tournament_id);
        $this->api_response();
      }catch(Exception $e){
        $this->api_response_arry['message'] = $e->getMessage();
        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response();
      }
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
        $record_info = $this->Tournament_model->get_single_row("tournament_id,is_pin,sports_id",TOURNAMENT,array("tournament_id"=>$tournament_id));
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

        $match_list = $this->Tournament_model->get_all_table_data("season_id,scheduled_date",SEASON,array("season_id IN (".implode(',',$season_ids).")"=>NULL),array("scheduled_date"=>"ASC"));
        if(count($match_list) != count($season_ids)){
            $this->api_response_arry['message'] = "Invalid selected match ids.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $match_list = array_column($match_list,"scheduled_date","season_id");
        $post_data['season_ids'] = $match_list;
        $result = $match_list = $this->Tournament_model->save_tournament_fixture($post_data);
        if($result){
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
            $match_list = $this->Tournament_model->get_fixture_season_detail($season_ids,"S.status");
            $match_list = array_column($match_list,NULL, 'season_id'); // match_list

            // match_list with prediction percentasge
            $season_ids =array_column($match_list, 'season_id');
            $team_percentage_list = $this->Tournament_model->get_team_prediction_percent($tournament_id,$season_ids); 
            $team_percentage_list = array_column($team_percentage_list, NULL,'season_id');

            if(empty($team_percentage_list)){
                $season_ids =  explode(',', $tournament['season_ids']); 
                foreach ($season_ids as $key => $value) {
                    $team_percentage_list[$value]['home_count'] = 0;
                    $team_percentage_list[$value]['away_count'] = 0;
                    $team_percentage_list[$value]['draw_count'] = 0;
                    $team_percentage_list[$value]['total_season_count'] = 0;
                 }
            }   
            $tournament['match'] = array_values(array_replace_recursive($match_list,$team_percentage_list)); //merge both the arrays
            
            $pickem_custom_data = !empty($this->app_config['allow_pickem_tournament'])?$this->app_config['allow_pickem_tournament']['custom_data']:array();
            if(!empty($pickem_custom_data))
            {
                $sp_config = array();
                $sp_config['pickem_win_goal'] = isset($pickem_custom_data['winning_and_goal'])?$pickem_custom_data['winning_and_goal']:0;
                $sp_config['pickem_win_goal_diff'] = isset($pickem_custom_data['winning_and_goal_difference'])?$pickem_custom_data['winning_and_goal_difference']:0;
                $sp_config['pickem_win_only'] = isset($pickem_custom_data['winning_only'])?$pickem_custom_data['winning_only']:0;
                $tournament['score_predictor_point'] = $sp_config;
            }
        }
        //echo "<pre>";print_r($tournament);die;
        $this->api_response_arry['data'] = $tournament;
        $this->api_response();
    }

   /**
    * Function user to cancel Tournament
    * @param tournament_id
    * @return string message
    */
    public function cancel_tournament_post()
    {
        $this->form_validation->set_rules('tournament_id', 'Tournament id', 'trim|required');
        $this->form_validation->set_rules('cancel_reason', 'Cancel Reason', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $tournament_id = $post_data['tournament_id'];
        $tournament = $this->Tournament_model->get_tournament_detail($tournament_id); 

        if(empty($tournament) || $tournament['status'] != '0')
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'Invalid Tournament';
            $this->api_response();
        }

        if($tournament['user_count'] == 0)
        {
            $this->Tournament_model->delete_row(TOURNAMENT,['tournament_id'=>$tournament_id]);
        }
        //update tournament if free entry
        elseif($tournament['entry_fee'] == 0){
            $this->Tournament_model->update(TOURNAMENT,array('status' =>1,'cancel_reason'=>$post_data['cancel_reason']),array('tournament_id'=>$tournament_id));
        }else{
            $tournament['cancel_reason'] = $post_data['cancel_reason'];
            $this->load->helper('queue');
            add_data_in_queue($tournament, 'pickem_cancel_tournament');
        }

        $sports_list_cache_key = "pickem_tournament_sports_list";
        $this->delete_cache_data($sports_list_cache_key);

        $this->api_response_arry['message'] = 'Tournament cancelled.';
        $this->api_response();

    }

    /**
    * Function use to get Tournament participants details
    * @param tournament_id
    * @return string message
    */
    public function get_join_partcipants_list_post()
    {
        $this->form_validation->set_rules('tournament_id', 'Tournament id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $result = $this->Tournament_model->get_join_partcipants_list($post_data);

        if(!empty($result['result']))
        {
            $result['result'] = array_column($result['result'],NULL,"user_id");
            $user_ids = array_keys($result['result']);
            $this->load->model('user/User_model');
            $user_data = $this->User_model->get_user_detail_by_user_id($user_ids);
            $user_data = array_column($user_data,NULL,"user_id");
            
            if(!empty($user_data)){
                $result['result'] = array_values(array_replace_recursive($result['result'],$user_data));
            }

        }

        $this->api_response_arry['data'] = $result;
        $this->api_response();

    }

    /**
    * Function use to get Tournament participants details
    * @param tournament_id
    * @return string message
    */
    public function get_partcipants_detail_post()
    {
        $this->form_validation->set_rules('user_tournament_id', 'User Tournament Id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $user_tournament_id = $this->input->post('user_tournament_id');
        $user_detail = $this->Tournament_model->get_partcipants_detail($user_tournament_id);

        $this->api_response_arry['data'] = $user_detail;
        $this->api_response();

    }

   /**
    * Function used to mark pickem answer
    * @param tournament_id, season_id
    * @return string message
    */
   public function submit_pickem_answer_post()
   {
    
        $season_data = $this->input->post('season_data');
        if(empty($season_data)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'Invalid Season Id';
            $this->api_response_arry['message'] = 'Please provide season data';
            $this->api_response();
        } 
 

        if(isset($this->app_config['allow_pickem_tournament']['key_value'])  && $this->app_config['allow_pickem_tournament']['key_value'] == 1){
            $correct = $this->app_config['allow_pickem_tournament']['custom_data']['correct'];
            $wrong   = $this->app_config['allow_pickem_tournament']['custom_data']['wrong'];
        }else{
           $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
           $this->api_response_arry['message'] = 'Please set correct and worng answer confriguation';
           $this->api_response();  
        }

        $pickem_config = !empty($this->app_config['allow_pickem_tournament'])?$this->app_config['allow_pickem_tournament']['custom_data']:array();
        $win_goal = isset($pickem_config['winning_and_goal'])?$pickem_config['winning_and_goal']:0;
        $win_goal_diff = isset($pickem_config['winning_and_goal_difference'])?$pickem_config['winning_and_goal_difference']:0;
        $win_only = isset($pickem_config['winning_only'])?$pickem_config['winning_only']:0;

       foreach ($season_data as $key => $value) {
            $score_predict = isset($value['is_score_predict'])?$value['is_score_predict']:0;
            // score predictor is on
            $score_data = Null;
            $away_score = $home_score = 0;
            $season_id = $value['season_id'];
            $team_id = $value['team_id'];
            
            if(!empty($score_predict)){
                $home_score = $value['home_score'];
                $away_score = $value['away_score'];
                $score_data = json_encode(array('home_score'=>$home_score,'away_score'=>$away_score));

                $away_home=$away_score-$home_score;
                $home_away=$home_score-$away_score;
                $update_score = "UPDATE  ".$this->db->dbprefix(USER_TEAM)." as UT SET is_correct=CASE WHEN UT.team_id =".$team_id." THEN 1 ELSE 2 END,score = CASE 
                WHEN UT.team_id=".$team_id." and UT.away_predict=".$away_score." and UT.home_predict=".$home_score." THEN ".$win_goal." 
                WHEN UT.team_id=".$team_id." and ((UT.away_predict-UT.home_predict=".$away_home.") or (UT.home_predict-UT.away_predict=".$home_away.")) THEN ".$win_goal_diff."
                WHEN UT.team_id=".$team_id." THEN ".$win_only."
                ELSE 0 END where UT.season_id =".$season_id.""; //and UT.is_correct=0

            }else{
                $update_score = "UPDATE  ".$this->db->dbprefix(USER_TEAM)." as UT SET score = CASE WHEN UT.team_id=".$team_id." THEN ".$correct." ELSE -(".$wrong.")  END,is_correct=CASE WHEN UT.team_id =".$team_id." THEN 1 ELSE 2 END where UT.season_id =".$season_id.""; //and UT.is_correct=0
            }
            // echo $this->db->last_query(); die;
            $this->db->query($update_score);
            $this->Tournament_model->update(SEASON,['status'=>2,'match_closure_date'=>format_date(),'winning_team_id'=>$team_id,'modified_date'=>format_date(),'score_data'=>$score_data],['season_id'=>$season_id]);
        }
        $this->load->helper('queue');
        $season_ids = array_column($season_data, 'season_id');
        $content = ['season_id'=>$season_ids];
        add_data_in_queue($content, 'pickem_score');

        $this->api_response_arry['message'] = 'Pickem answer marked successfully';
        $this->api_response();

   }
 

   /**
    * Function used to mark complete status
    * @param tournament_id
    * @return string message
    */
   public function mark_tournament_complete_post()
   {
       $this->form_validation->set_rules('tournament_id', 'Tournament Id', 'trim|required');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
       $tournament_id = $this->input->post('tournament_id');
       $current_date =  format_date();
    
       $tournament = $this->Tournament_model->get_tournament_fixture_status($tournament_id);
       if(!empty($tournament) && !empty($tournament['match_closure_date']))
       {
         if(!empty($tournament['tie_breaker_question']) && $tournament['tie_breaker_answer'] == 0){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'Please mark tie breaker answer';
            $this->api_response();
         }

         if($tournament['tournament_status'] == 2 || $tournament['tournament_status'] == 3){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'Tournament already marked completed';
            $this->api_response();
         }

         $dateTime = new DateTime($tournament['match_closure_date']);
         $dateTime->modify('+5 minutes');
         $closure_Date = $dateTime->format("Y-m-d H:i:s");

          $update =$this->Tournament_model->update(TOURNAMENT,['status'=>2,'modified_date'=>format_date()],['tournament_id'=>$tournament_id]);



        if($closure_Date > $current_date)
        {
            $update =$this->Tournament_model->update(TOURNAMENT,['end_date'=>format_date()],['tournament_id'=>$tournament_id]);

         }

        // if($current_date > $closure_Date)
        // {
        //      $update =$this->Tournament_model->update(TOURNAMENT,['status'=>2,'modified_date'=>format_date()],['tournament_id'=>$tournament_id]);


        //  }else{
        //     $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        //     $this->api_response_arry['message'] = 'Please wait for 5 minutes to mark tournament completed as scoring is in progress';
        //     $this->api_response();
        //  }
        
       }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'Please mark all fixture answer';
            $this->api_response();
       }

        $this->api_response_arry['message'] = 'Tournament marked completed successfully';
        $this->api_response();
    } 

   /* update Tie Breaker answer
    * @param int season_id
    * @return string message
    */
    public function save_tie_breaker_answer_post(){
        $this->form_validation->set_rules('tournament_id', 'Tournament Id', 'trim|required');
        $this->form_validation->set_rules('answer', 'Answer', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();  
        $answer = $this->Tournament_model->get_single_row('tie_breaker_answer',TOURNAMENT,['tournament_id'=>$post_data['tournament_id']]);
        if($answer['tie_breaker_answer'] > 0 ){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'You have already mark tie breaker answer';
            $this->api_response();
        }

        $update = $this->Tournament_model->update(TOURNAMENT,['tie_breaker_answer'=>$post_data['answer']],['tournament_id'=>$post_data['tournament_id']]);
        if($update){
            $this->load->helper('queue');
            $content = ['tournament_id'=>$post_data['tournament_id']];
            add_data_in_queue($content, 'pickem_score');
            $this->api_response_arry['message'] = 'Tie breaker answer updated successfully.';
            $this->api_response_arry['data'] =$post_data['answer'] ;
            $this->api_response();
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'Error in updating answer';
            $this->api_response();
        }
    }
   
    /**
    * Function user to cancel fixture
    * @param tournament_id,season_id
    * @return string message
    */
    public function cancel_fixture_post()
    {
        $this->form_validation->set_rules('tournament_id', 'Tournament id', 'trim|required');
        $this->form_validation->set_rules('season_id', 'Season id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $tournament_id = $post_data['tournament_id'];
        $season_id = $post_data['season_id'];
        $tournament = $this->Tournament_model->get_fixture_detail($tournament_id,$season_id); 
       
        if(empty($tournament) || $tournament['status'] != '0')
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'Invalid Tournament';
            $this->api_response();
        }
        
        if($tournament['match_count'] == 1)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'This fixture cannot be deleted. A tournament must have at least one fixture.';
            $this->api_response();
        }
       
        $row_affect=$this->Tournament_model->delete_row(TOURNAMENT_SEASON,['tournament_id'=>$tournament_id,"season_id"=>$season_id]);
        if(!empty($row_affect)){
            $match_count=$tournament['match_count']-1;
            $this->Tournament_model->update(TOURNAMENT,['match_count'=>$match_count],['tournament_id'=>$tournament_id]);
        }
        
        if(!empty($tournament['user_team_id'])){
            $this->Tournament_model->delete_user_pickem_answer($season_id,$tournament['user_team_id']);
        }
        
        $this->api_response_arry['message'] = 'Fixture cancelled.';
        $this->api_response();

    }

}