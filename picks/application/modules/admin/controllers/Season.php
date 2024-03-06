<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Season extends Common_Api_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Season_model');
        $_POST = $this->post();
   
	}


     /**
     * Add fixture
     * @param POST
     * @return json array
     */

    public function add_fixture_post(){
        $post_data = $this->input->post();
        $this->form_validation->set_rules('league_id','League Id', 'trim|required');
        $this->form_validation->set_rules('home_id', 'Home Id', 'trim|required');
        $this->form_validation->set_rules('away_id', 'Away Id', 'trim|required');
        $this->form_validation->set_rules('scheduled_date', 'Scheduled Date', 'trim|required');
        $this->form_validation->set_rules('correct', 'Correct Answer', 'trim|required');
        $this->form_validation->set_rules('wrong', 'Wrong Answer', 'trim|required');        

        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();

        if($post_data['question']< 2){
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry["message"] = "Select Question More than One";
            $this->api_response();
        }  


        if(strtotime($post_data['scheduled_date']) < strtotime(format_date())){
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['message']         = "Fixture date should not be less than current date time.";
            $this->api_response_arry['data']            = array();
            $this->api_response();
        }

        $league_id = trim($post_data['league_id']);

        $check_league_exist = $this->Season_model->get_single_row("*",LEAGUE,array("league_id" => $league_id));       

        if(empty($check_league_exist)){
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry["message"] = $this->lang->line("league_not_exist");
            $this->api_response();
        }


        $where = ['league_id'=>$post_data['league_id'],"scheduled_date"=>$post_data['scheduled_date']];

        if($post_data['home_id'] == $post_data['away_id']) {

            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR; 
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['message']         = "Team 1 and Team 2 will not be Same";
            $this->api_response();          
        }

        $check_minus_limit = $post_data['correct']-$post_data['wrong'];     

        if ($check_minus_limit < 0) {


            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['message']         = "Minus marking  is wrong insert";
            $this->api_response();
            
        }

        $season_game_uid = generate_entity_uid();

        $home_name = $this->get_team_name_by_id($post_data['home_id']);
        $away_name = $this->get_team_name_by_id($post_data['away_id']);        

        $data_arr = array(
                                "league_id"     => $post_data['league_id'],
                                "season_game_uid"   => $season_game_uid,
                                "home_id" =>     $post_data['home_id'],
                                "away_id" =>     $post_data['away_id'],
                                "match" =>     $home_name.' vs '.$away_name , 
                                "scheduled_date" =>     $post_data['scheduled_date'],
                                "question" => $post_data['question'],
                                "correct"=> $post_data['correct'],
                                "wrong" => $post_data['wrong'],
                                "modified_date" => format_date()
                            );


        $this->load->model('season_model'); 
        $fixture_id = $this->season_model->save_record(SEASON,$data_arr);

        if ($fixture_id) {
            $this->api_response_arry['data'] = $fixture_id;
            $this->api_response_arry['message'] = 'Fixture added successfully.';
            $this->api_response();
        }else{

            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Something went wrong.";
            $this->api_response();
        }       
    }

    /**
     * Used for get team name
     * @param int $team_id
     * @return team name
     */ 
    public function get_team_name_by_id($team_id)
    {
        $this->load->model('team_model'); 
        $response = $this->team_model->get_team_name_by_id($team_id);  
        return strtoupper($response['team_abbr']);      
    }


      /**
     * Used for get collection list
     * @param int $sports_id
     * @param int $type
     * @return array
     */
    public function get_all_season_schedule_post() {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        // $this->form_validation->set_rules('type', "type", 'trim|required');

        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $current_date = format_date();
        $final_array =array();
        $post_data = $this->input->post();
        $this->load->model("season_model");
        $result = $this->season_model->get_all_season_schedule($post_data);     

        $final_array['fixture'] =  $result['result'];

        $final_array['total'] = $result['total'];
        $this->api_response_arry['data']= $final_array;
        $this->api_response();
    }
    
    /**
     * Save season question
     * @param POST
     * @return json array
     */

    public function save_season_qustion_post(){


        $this->form_validation->set_rules('season_id', 'Season Id', 'trim|required');
        

        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();

        $this->load->model('Contest_template_model'); 

         $question_limit= $this->Contest_template_model->get_single_row("question",SEASON,array("season_id" => $post_data['season_id']));
    
         $question_arr   =  $post_data['question'];

         if($question_limit['question'] != count($question_arr)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Please Select ".$question_limit['question']." question";
            $this->api_response();  
        }

        $opt_1 = array_filter(array_column($question_arr,"option_1"));
        $opt_2 = array_filter(array_column($question_arr,"option_2"));

        if(count($opt_1) != $question_limit['question'] || count($opt_2) != $question_limit['question']){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Option 1 and Option 2 is mandatory.";
            $this->api_response();  
        }
        $options_arr = array();

        foreach ($post_data['question'] as $value)
        {             
   
            $options_arr[] = array(
             'season_id'      => $post_data['season_id'],
             'name'           => $value['name'],
             'details'        => $value['details'],
             'option_1'       => $value['option_1'],
             'option_2'       => $value['option_2'],
             'option_3'       => $value['option_3'],
             'option_4'       => $value['option_4'],
             'created_date'   => format_date(),
             'modified_date'  => format_date()
            );
         }

           if(!empty($post_data))
             {
                 $this->season_model->insert_question_option($options_arr);
             }   

             if ($this->db->trans_status() === FALSE)
             {
            
                 $this->db->trans_rollback();
                 $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                 $this->api_response_arry['message'] = "Something went wrong.";
                 $this->api_response();
             }
             else
             {
             
                 $this->db->trans_commit();
             }


             $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
             $this->api_response_arry['message']         = 'Question Added Successfully';
             $this->api_response();
    }


    /**
    * Function used for publish fixture for create contest
    * @param int $season_id
    * @param int $league_id
    * @param string $season_game_uid
    * @param array $question_list
    * @return array
    */
    public function publish_fixture_post()
    {
        $this->form_validation->set_rules('season_id', 'Season Id', 'trim|required');
        

        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();

        $this->load->model('season_model'); 

         $checked_published = $this->season_model->get_single_row("published",SEASON,array("season_id" => $post_data['season_id']));

         if ($checked_published['published'] == 1) {
             $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'fixture already published';
            $this->api_response(); 
         }

        $question_limit= $this->season_model->get_single_row("question",SEASON,array("season_id" => $post_data['season_id']));
    
        if(empty($question_limit)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('detail_not_found');
            $this->api_response();  
        }
         $question_arr   =  $post_data['question'];


         if($question_limit['question'] != count($question_arr)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Please Select ".$question_limit['question']." question";
            $this->api_response();  
        }

        $opt_1 = array_filter(array_column($question_arr,"option_1"));
        $opt_2 = array_filter(array_column($question_arr,"option_2"));
        $name = array_filter(array_column($question_arr,"name"));


        if(count($name) != $question_limit['question']){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Question Field is mandatory.";
            $this->api_response();  
        }
        

        if(count($opt_1) != $question_limit['question'] || count($opt_2) != $question_limit['question']){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Option 1 and Option 2 is mandatory.";
            $this->api_response();  
        }
        $options_arr = array();

        $result = $this->season_model->get_exist_question($post_data['season_id']);
        if (empty($result)) {    
    

            foreach ($post_data['question'] as $key=> $value)
            {             
       
                $options_arr[] = array(
                 'season_id'      => $post_data['season_id'],
                 'name'           => $value['name'],
                 'details'        => $value['details'],
                 'option_1'       => $value['option_1'],
                 'option_2'       => $value['option_2'],
                 'option_3'       => $value['option_3'],
                 'option_4'       => $value['option_4'],
                 'created_date'   => format_date(),
                 'modified_date'  => format_date(),
                 'stats_text'     => isset($value['stats_text'])?$value['stats_text']:''
                );

                $options_arr[$key]['option_images'] = '{}';
                if(!empty($value['option_images'])){
                    $options_arr[$key]['option_images'] = json_encode($value['option_images']);
                }

                $options_arr[$key]['option_stats'] = '{}';
                if(!empty($value['option_stats'])){
                    $options_arr[$key]['option_stats'] = json_encode($value['option_stats']);
                }
            }

            if(!empty($post_data))
            { 
                 $this->season_model->insert_question_option($options_arr);
            }   

            if ($this->db->trans_status() === FALSE)
            {
            
                 $this->db->trans_rollback();
                 $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                 $this->api_response_arry['message'] = "Something went wrong.";
                 $this->api_response();
            }
            else
            {
                $data_arr['published'] = 1;
                $data_arr['modified_date'] = format_date();
                if(!empty($post_data['tie_breaker_question'])){
                    $data_arr['tie_breaker_question'] = json_encode($post_data['tie_breaker_question']);
                }
                $result = $this->season_model->update(SEASON,$data_arr,array("season_id"=>$post_data['season_id']));             
                $this->db->trans_commit();
            }

             $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
             $this->api_response_arry['message']         = 'published Successfully';
             $this->api_response();
        }
        else{
 
            foreach ($post_data['question'] as $key=> $value)
            {       
                $options_arr = array(              
                 'name'           => $value['name'],
                 'details'        => $value['details'],
                 'option_1'       => $value['option_1'],
                 'option_2'       => $value['option_2'],
                 'option_3'       => $value['option_3'],
                 'option_4'       => $value['option_4'],                 
                 'modified_date'  => format_date(),
                 'stats_text'     => isset($value['stats_text'])?$value['stats_text']:''
                ); 

                if(!empty($value['option_images'])){
                    $options_arr['option_images'] = json_encode($value['option_images']);
                }
                if(!empty($value['option_stats'])){
                    $options_arr['option_stats'] = json_encode($value['option_stats']);
                }

                if (isset($value['pick_id']) && $value['pick_id'] != "") {        

                    $result = $this->season_model->update(PICKS,$options_arr,array("pick_id"=>$value['pick_id']));
                }else{

                    $options_arr['season_id'] = $post_data['season_id'];                    
                    $options_arr['created_date'] = format_date();                    
                    $result = $this->season_model->save_record(PICKS,$options_arr);
                }        

            } 
            $data_arr['published'] = 1;
            $data_arr['modified_date'] = format_date();
            if(!empty($post_data['tie_breaker_question'])){
                $data_arr['tie_breaker_question'] = json_encode($post_data['tie_breaker_question']);
            }
             $this->season_model->update(SEASON,$data_arr,array("season_id"=>$post_data['season_id'])); 

            $sports_cache_key = 'picks_sports_list';
            $this->delete_cache_data($sports_cache_key);

             $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
             $this->api_response_arry['message']         = 'published Successfully';
             $this->api_response();
        }
      
    }


     /**
    * Function used for save draft contest  
    * @return array
    */
    public function save_draft_post()
    {
        $this->form_validation->set_rules('season_id', 'Season Id', 'trim|required');       

        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        //echo '<pre>';print_r($post_data);die;
        $this->load->model('season_model'); 

        $question_limit= $this->season_model->get_single_row("question",SEASON,array("season_id" => $post_data['season_id']));



        if(empty($question_limit)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('detail_not_found');
            $this->api_response();  
        }
    
        $question_arr   =  $post_data['question'];     
        if(count($question_arr) < 1){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Please Select Minimum One Question";
            $this->api_response();  
        }

        $opt_1 = array_filter(array_column($question_arr,"option_1"));
        $opt_2 = array_filter(array_column($question_arr,"option_2"));
        $name = array_filter(array_column($question_arr,"name"));     

        if(count($name) != count($question_arr)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Question Field is mandatory.";
            $this->api_response();  
        }

        if(count($opt_1) != count($question_arr) || count($opt_2) != count($question_arr)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("option_mandatory");
            $this->api_response();  
        }

        $options_arr = array();

        foreach ($post_data['question'] as $key=>$value)
        {             
            
            $options_arr[] = array(
             'season_id'      => $post_data['season_id'],
             'pick_id'        => $value['pick_id'],
             'name'           => $value['name'],
             'details'        => $value['details'],
             'option_1'       => $value['option_1'],
             'option_2'       => $value['option_2'],
             'option_3'       => $value['option_3'],
             'option_4'       => $value['option_4'],
             'created_date'   => format_date(),
             'modified_date'  => format_date(),
             'stats_text'     => isset($value['stats_text'])?$value['stats_text']:''
            );

            $options_arr[$key]['option_images'] = json_encode((object) array());
            $options_arr[$key]['option_stats'] = json_encode((object) array());
            if(!empty($value['option_images'])){
                 $options_arr[$key]['option_images'] = json_encode($value['option_images']);
            }
            if(!empty($value['option_stats'])){
                 $options_arr[$key]['option_stats'] = json_encode($value['option_stats']);
            }

         }

        $this->db->trans_start();
        $picks_ids = array_column($post_data['question'], 'pick_id');
        if(!empty($picks_ids))
        {
            
            $this->db->where_not_in('pick_id',$picks_ids);
            $this->db->where('season_id',$post_data['season_id']);
            $this->db->delete(PICKS);
        }

        $this->season_model->replace_into_batch(PICKS,$options_arr);
           

        if ($this->db->trans_status() === FALSE)
        {            
             $this->db->trans_rollback();
             $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
             $this->api_response_arry['message'] = "Something went wrong.";
             $this->api_response();
        }
        else
        {
            $data_arr['published'] = 0;
            $data_arr['modified_date'] = format_date();
            if(!empty($post_data['tie_breaker_question'])){
                $data_arr['tie_breaker_question'] = json_encode($post_data['tie_breaker_question']);
                $result = $this->season_model->update(SEASON,$data_arr,array("season_id"=>$post_data['season_id']));   
                 
            }         
            $this->db->trans_commit();
        }
         
         $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
         $this->api_response_arry['message']         = $this->lang->line("save_in_draft");
         $this->api_response();
      
    }


    /**
    * fetch question list by season id
    * @param int season_id
    * @return array
    */
    public function get_question_list_by_id_post(){
        $this->form_validation->set_rules('season_id', 'season id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();

        $this->load->model('season_model'); 

        $question_info = $this->season_model->get_all_table_data('pick_id,season_id,name,option_1,option_2,option_3,option_4,option_images,option_stats,details,answer,explaination,explaination_image,stats_text',PICKS,['season_id'=>$post_data['season_id']]);
 
        $tie_breaker =  $this->season_model->get_single_row('tie_breaker_question,tie_breaker_answer',SEASON,['season_id'=>$post_data['season_id']]);

        $this->api_response_arry['data']['question_info'] = $question_info;
        $this->api_response_arry['data']['tie_breaker'] = $tie_breaker;
        $this->api_response();

    }
     
    /**
    * update answer by Id
    * @param int pick_id
    * @return array
    */
    public function update_answer_by_id_post(){
        $this->form_validation->set_rules('pick_id', 'pick id', 'trim|required');
        $this->form_validation->set_rules('answer', 'Answer', 'trim|required|in_list[1,2,3,4]');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();

          $question= $this->Season_model->get_single_row("*",PICKS,array("pick_id"=>$post_data['pick_id']));

          $season_data= $this->Season_model->get_single_row("*",SEASON,array("season_id"=>$question['season_id']));

          if($season_data['scheduled_date'] > format_date()) {
            $this->api_response_arry["message"] = 'You can tick answer after start Match.';
              $this->api_response();               
          }    
          
          if($post_data['answer'] ==1) {
            $one = $question['option_1'];            
            if (empty($one)) {
              $this->api_response_arry["message"] = $this->lang->line("option_blank");
              $this->api_response();   
            } 
          }elseif ($post_data['answer'] ==2) {
            $two = $question['option_2'];            
            if (empty($two)) {
              $this->api_response_arry["message"] = $this->lang->line("option_blank");
              $this->api_response();   
            } 
          }elseif ($post_data['answer'] ==3) {
            $three = $question['option_3'];            
            if (empty($three)) {
              $this->api_response_arry["message"] = $this->lang->line("option_blank");
              $this->api_response();   
            } 
          }elseif ($post_data['answer'] ==4) {
              $four = $question['option_4'];            
            if (empty($four)) {
              $this->api_response_arry["message"] = $this->lang->line("option_blank");
              $this->api_response();   
            } 
          }else{
            $this->api_response_arry["message"] = $this->lang->line("something_went_wrong");
              $this->api_response(); 
          }       
       
        $data_arr['answer'] = $post_data['answer'];
        $data_arr['modified_date'] = format_date();

        $result = $this->Season_model->update(PICKS,$data_arr,array("pick_id"=>$post_data['pick_id'])); 

        if ($result) {  
            if(isset($post_data['update_ans']) && $post_data['update_ans'] ==1) {

                $this->api_response_arry["message"] = $this->lang->line("answer_update");
                 
              }else{
                 $this->api_response_arry["message"] = "Answer Marked Successfully.";

              }

            $this->load->helper('queue_helper');
            $server_name = get_server_host_name(); 
            $content = array();
            $content['url'] = $server_name."/picks/cron/update_scores_in_picks_by_season";
            add_data_in_queue($content, 'picks_score_update_cron');    
            $this->api_response();            
        }else{
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry["message"] = $this->lang->line("something_went_wrong");
            $this->api_response();
        }
    }

    /**
    * update answer by Id
    * @param int pick_id
    * @return array
    */
    public function mark_completed_post(){
     
        $this->form_validation->set_rules('season_id', 'season id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $this->load->model('season_model');

        $post_data = $this->input->post();

        $question_info = $this->season_model->get_all_table_data('pick_id,season_id,name,option_1,option_2,option_3,option_4,details,answer',PICKS,['season_id'=>$post_data['season_id']]);

        $answer = array_column($question_info ,"answer");   

        if (in_array("0", $answer)){         
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry["message"] =  $this->lang->line("mark_all_answer");
            $this->api_response();
        }


        $data_arr['status'] = 2;
        $data_arr['status_overview'] = 4;
        $data_arr['modified_date'] = format_date();

        $result = $this->season_model->update(SEASON,$data_arr,array("season_id"=>$post_data['season_id'])); 

        if ($result) {
            $this->load->helper('queue_helper');
            $server_name = get_server_host_name(); 
            $content = array();
            $content['url'] = $server_name."/picks/cron/update_contest_status";
            add_data_in_queue($content, 'picks_contest_close');

            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response_arry["message"] = $this->lang->line("mark_complete_answer");
            $this->api_response();            
        }else{
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry["message"] = $this->lang->line("something_went_wrong");
            $this->api_response();
        }
  
    }
    
    /**
    * update fixture delay
    * @param int season_game_uid
    * @return array
    */

     public function update_fixture_delay_post(){

        $this->form_validation->set_rules('season_game_uid','Season game uid','trim|required');
        $this->form_validation->set_rules('delay_hour','Hour','trim');
        $this->form_validation->set_rules('delay_minute','Minute','trim');
        $this->form_validation->set_rules('delay_message','Delay Message','trim|required|max_length[160]');
        $this->form_validation->set_rules('league_id','League ID','trim|required');
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();

        if(!isset($post_data['delay_hour']) || $post_data['delay_hour'] < 0 || $post_data['delay_hour'] > 47){           
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']       = $this->lang->line('match_delay_0_48_msg');
            $this->api_response();
        }
        if(!isset($post_data['delay_minute']) || $post_data['delay_minute'] < 0 || $post_data['delay_minute'] > 59){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']       = $this->lang->line('match_delay_0_59_msg');
            $this->api_response();
        }
  

        $delay_hour = $delay_minute = 0;
        if(isset($post_data['delay_hour']) && $post_data['delay_hour'] != ""){
            $delay_hour = $post_data['delay_hour'];
        }
        if(isset($post_data['delay_minute']) && $post_data['delay_minute'] != ""){
            $delay_minute = $post_data['delay_minute'];
        }

        $delay_minutes = convert_hour_minute_to_minute($delay_hour,$delay_minute);

        if($delay_minutes < 0){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']       = $this->lang->line('match_delay_hour_minute_msg');
            $this->api_response();
        }

        $season_info = $this->Season_model->get_season_by_game_id($post_data['season_game_uid'],$post_data['league_id']);


        $current_date = strtotime(format_date());

        $fixture_schedule = strtotime($season_info['scheduled_date']);
       
        if($fixture_schedule <= $current_date){       
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']       = $this->lang->line('match_start_delay_msg');
            $this->api_response();
        }      

        $delay_min_diff = $delay_minutes - $season_info['delay_minute'];

        if($delay_min_diff <= 0){
            //$delay_min_diff = $delay_min_diff - 5;
            $new_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$delay_min_diff.' minutes', strtotime($season_info['scheduled_date'])));
            if(strtotime($new_scheduled_date) <= $current_date){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('match_prepond_time_limit_msg');
                $this->api_response();
            }
        }
        
        $season_info['new_delay_minute'] = $delay_minutes;
        $season_info['delay_message'] = $post_data['delay_message']; 
   
        //update match cache and bucket files
  
        $this->Season_model->update_match_delay_data($season_info);       
        if(!empty($season_info)){
            $this->flush_cache_data();
        }

        //update lobby fixture file
        $this->push_s3_data_in_queue("lobby_fixture_list_".$season_info['sports_id'],array(),"delete");
        $this->api_response_arry['message'] = $this->lang->line('match_delay_message');
        $this->api_response();

    } 


    /**
    * 
    *Delete Fixture
    * 
    */  
    public function delete_fixture_post() {
        $post_data = $this->post();
        $this->form_validation->set_rules('season_id', 'season id', 'trim|required');

        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $this->db->where('season_id',$post_data['season_id']);
        $delete = $this->db->delete(SEASON);

        if($delete)
        {
            $this->db->where('season_id',$post_data['season_id']);
            $delete = $this->db->delete(PICKS);
        }


        $this->api_response_arry['message'] = 'Fixture deleted successfully.';
        $this->api_response();

    }

    /**
    * Function used for mark pin fixture
    * @param season_id
    * @return string message
    */
    public function mark_pin_post(){

        $this->form_validation->set_rules('season_id', 'Season id', 'trim|required');
        if(!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $season_id = $post_data['season_id'];
        $current_date = format_date();
        $record_info = $this->Season_model->get_season_sport_id($season_id);
        if(empty($record_info)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid Season id. please provide valid id.";
            $this->api_response();
        }

        $is_pin_fixture = 1;
        if(isset($record_info['is_pin_fixture']) && $record_info['is_pin_fixture'] == "1"){
            $is_pin_fixture = 0;
        }
        $result = $this->Season_model->update(SEASON,array("is_pin_fixture"=>$is_pin_fixture,"modified_date"=>$current_date),array("season_id"=>$season_id));
        if($result)
        {
            $lobby_fixture_cache_key = 'picks_lobby_fixture_list_'.$record_info['sports_id'];
            $this->delete_cache_data($lobby_fixture_cache_key);

            $lobby_feature_fixture_cache_key = 'picks_lobby_fixture_list_0';
            $this->delete_cache_data($lobby_feature_fixture_cache_key);

            $this->api_response_arry['message'] = "Fixture pin status updated successfully.";
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'No change';
            $this->api_response();
        }
    }

   /* update Tie Breaker answer
    * @param int season_id
    * @return string message
    */
    public function save_tie_breaker_answer_post(){
        $this->form_validation->set_rules('season_id', 'Season Id', 'trim|required');
        $this->form_validation->set_rules('answer', 'Answer', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();  
        $answer = $this->Season_model->get_single_row('tie_breaker_answer',SEASON,['season_id'=>$post_data['season_id']]);
        if($answer['tie_breaker_answer'] > 0 ){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'You have already mark tie breaker answer';
            $this->api_response();
        }

        $update = $this->Season_model->update(SEASON,['tie_breaker_answer'=>$post_data['answer']],['season_id'=>$post_data['season_id']]);
        if($update){
            $this->load->helper('queue_helper');
            $server_name = get_server_host_name(); 
            $content['url'] = $server_name."/picks/cron/update_scores_in_picks_by_season";
            add_data_in_queue($content, 'picks_score_update_cron');    
            $this->api_response_arry['message'] = 'Tie breaker answer updated successfully.';
            $this->api_response();
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = 'Error in updating answer';
            $this->api_response();
        }
    }

    /**
    * update answer by Id
    * @param int pick_id
    * @return array
    */
    public function save_explaination_post(){
        $this->form_validation->set_rules('pick_id', 'pick id', 'trim|required');
        $this->form_validation->set_rules('explaination', 'Explaination', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $data_arr = array();
        $data_arr['explaination'] = $post_data['explaination'];

        $explaination_image = isset($post_data['explaination_image'])?$post_data['explaination_image']:'';
        $data_arr['explaination_image'] = $explaination_image;
        $data_arr['modified_date'] = format_date();

        $result = $this->Season_model->update(PICKS,$data_arr,array("pick_id"=>$post_data['pick_id'])); 

        if ($result) {  
            $this->api_response_arry["message"] = "Explaination added successfully.";
            $this->api_response();            
        }else{
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry["message"] = $this->lang->line("something_went_wrong");
            $this->api_response();
        }
    }

}

/* End of file Season.php */
/* Location: /admin/application/controllers/Season.php */
