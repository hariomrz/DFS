<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Season extends Common_Api_Controller{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/League_model');

	}

      /**
     * Used for get dfs season list 
     * @param array $post_data
     * @return json array
     */
    public function get_season_list_post()
    {   
        $this->form_validation->set_rules('sports_id','sports id','trim|required');
        $this->form_validation->set_rules('status','status','trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        	$this->load->model('admin/Season_model');
        $match_list = $this->Season_model->get_season_list($post_data);
        // echo "<pre>";print_r($match_list);die;
        $this->api_response_arry['data'] = $match_list;
        $this->api_response();
    }

     public function update_fixture_delay_post(){

        $this->form_validation->set_rules('season_id','Season id','trim|required');
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
            $this->api_response_arry['message'] = $this->lang->line('match_delay_0_59_msg');
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

        $this->load->model('admin/Season_model');
        $season_info = $this->Season_model->get_season_by_game_id($post_data['season_id'],$post_data['league_id']);
       
        $current_date = strtotime(format_date());
        $fixture_schedule = strtotime($season_info['scheduled_date']);
        if($fixture_schedule <= $current_date){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_start_delay_msg');
            $this->api_response();
        }

        $delay_min_diff = $delay_minutes - $season_info['delay_minute'];
        if($delay_min_diff <= 0){
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
        if(!empty($collection_info)){
            $this->flush_cache_data();
        }

        //update lobby fixture file
        $this->push_s3_data_in_queue("lobby_fixture_list_".$season_info['sports_id'],array(),"delete");
        $this->api_response_arry['message'] = $this->lang->line('match_delay_message');
        $this->api_response();
    }


    public function update_fixture_custom_message_post(){
        $this->load->model('admin/Season_model');
        $this->form_validation->set_rules('season_game_uid','Season game uid','trim|required');
        $this->form_validation->set_rules('league_id','League ID','trim|required');
        $post_data = $this->input->post();
        if(empty($post_data['is_remove']) || !isset($post_data['is_remove']))
        {
            $this->form_validation->set_rules('custom_message','Custom Message','trim|required|max_length[160]');
        }
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $season_info = $this->Season_model->get_season_by_game_id($post_data['season_game_uid'],$post_data['league_id']);
        if(empty($season_info)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_not_found_msg');
            $this->api_response();
        }

        $result = $this->Season_model->update_match_custom_message($post_data);
        //update match cache and bucket files
        if($result){
            //update lobby fixture file
            $this->delete_cache_data('lobby_fixture_list_'.$season_info['sports_id']);
            $this->push_s3_data_in_queue("lobby_fixture_list_".$season_info['sports_id'],array(),"delete");
        }

        $this->api_response_arry['message'] = $this->lang->line('match_custom_msg_sent');
        $this->api_response();
    }


    /**
     * Used for get dfs season list 
     * @param array $post_data
     * @return json array
     */
    public function get_season_detail_post()
    {  
        $this->form_validation->set_rules('season_id','Season id','trim|required');
        $this->form_validation->set_rules('league_id','League id','trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $season_id = $post_data['season_id'];
        $league_id = $post_data['league_id'];
        $this->load->model('admin/Season_model');
        $match_list = $this->Season_model->get_season_by_game_id($season_id,$league_id);
        // echo "<pre>";print_r($match_list);die;
        $this->api_response_arry['data'] = $match_list;
        $this->api_response();
    }

    /**
     * Used for adding question
     * @param array $post_data
     * @return json array
     */
    public function add_question_post()
    {   
        $this->form_validation->set_rules('season_id','Season game uid','trim|required');
   
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        

        $question_data =  $post_data['data'];

        $op_custom_data = $this->app_config['opinion_trade']['custom_data'];
        $currency_type = isset($op_custom_data['currency'])?$op_custom_data['currency']:'realcash';
        $currency = 1;
        if($currency_type == 'realcash'){
            $currency = 1;
        }elseif($currency_type == 'coins'){
            $currency = 2;
        }
        
        $post_array = [];
        foreach ($question_data as $key => $value) {           
           
            $post_array['season_id'] = $post_data['season_id'];
            $post_array['template_id'] =0;
            $post_array['question'] = $value['question'];
            $post_array['scheduled_date'] = $value['expire']; 
            $post_array['option1'] = 'Yes';
            $post_array['option2'] = 'No';
            $post_array['currency_type'] = 1;
            $post_array['option1_val'] = 5.0;
            $post_array['option2_val'] = 5.0;
            $post_array['site_rake'] = 0;
            $post_array['cap']= 10;
            $post_array['answer']= '';
            $post_array['status']=  0;
            $post_array['currency_type']=  $currency;
            $post_array['added_date']= format_date();
            $post_array['modified_date'] = format_date();        

            $this->load->model('admin/Season_model');
            $this->Season_model->add_question($post_array);    
            
        }
       
        $output = $this->Season_model->update(SEASON,array("is_published"=>1),array("season_id"=>$post_data['season_id']));        
        $match_list_cache_key = 'match_list_'.$post_data['sports_id'];
         $this->delete_cache_data($match_list_cache_key);
      
        $this->api_response_arry['message'] = "Question added Successfully";
        $this->api_response_arry['data'] = array();
        $this->api_response();    
    }

     /**
     * get season question list
     * @param array $post_data
     * @return json array
     */
    public function get_season_question_post()
    {   
        $this->form_validation->set_rules('season_id','Season id','trim|required');       
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }        
        $post_data = $this->input->post();      
        $season_id = $post_data['season_id'];
        $this->load->model('admin/Season_model');
        $match_list = $this->Season_model->get_season_question($season_id);
       
        $this->api_response_arry['data'] = $match_list;
        $this->api_response();
    }


    /**
     * get season question list
     * @param array $post_data
     * @return json array
     */
    public function update_answer_post()
    {   
        $this->form_validation->set_rules('question_id','Question Id','trim|required');       
        $this->form_validation->set_rules('answer','Answer','trim|required');       
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        
        $post_data = $this->input->post();
     
        
      
        $this->load->model('admin/Season_model');
       $result = $this->Season_model->update_answer($post_data);
      

        $this->api_response_arry['message'] = "Answer Update Successfully";
        $this->api_response();
    }

    /**
     * Used for get participent list
     * @param void
     * @return string
     */
    public function get_question_detail_post_old()
    {
        // $json = file_get_contents('php://input');
        // $post_data = json_decode($json, true);

        // $_POST = $post_data;
        // $is_valid = 0;
        // $msg = "";
        // if(empty($post_data['question_id'])){
        //     $msg = "question id field required.";
        // }else{
        //     $is_valid = 1;
        // }
        // if($is_valid == 1){
        //    $this->load->model('admin/Season_model');
        //     $result = $this->Season_model->get_question_users($post_data['question_id']);
           
        //     if(!empty($result)){
        //         $this->load->model('user/User_model');
        //         $matchup_list = array_column($result,NULL,"user_team_id");

        //         $this->load->model('user/User_model');
        //         $user_ids = array_unique(array_column($result, "user_id"));
        //         $user_data = $this->User_model->get_users_by_ids($user_ids);
        //         $user_data = array_column($user_data,NULL,"user_id");               
               
        //         $mids = array();
        //         $final_list = array();               
        //         foreach($result as $row){
                    
        //             if(in_array($row['matchup_id'],$mids) || in_array($row['user_team_id'],$mids)){
        //                 continue;
        //             }
                 
        //             $tmp_nm = array("user_name"=>"Trader","image"=>"no_user.png","entry_fee"=>"0.0");
        //             $tmp_row = array("status"=>"0","added_date"=>$row['added_date'],"yes"=>$tmp_nm,"no"=>$tmp_nm);
                    
        //             $opp_row = array();
        //             $mids[] = $row['user_team_id'];
        //             $matched=0;
                    
        //             if($row['matchup_id'] > 0){
                      
        //                 $mids[] = $row['matchup_id'];
        //                 $opp_arr = $matchup_list[$row['matchup_id']];
        //                $opp_arr_user_detail = (!empty($user_data[$opp_arr['user_id']])) ? $user_data[$opp_arr['user_id']] : array();
        //                 $tmp_arr = array("user_team_id"=>$opp_arr['user_team_id'],"answer"=>$opp_arr['answer'],"entry_fee"=>$opp_arr['entry_fee'],"user_name"=>$opp_arr_user_detail['user_name'],"image"=>$opp_arr_user_detail['image']);
        //                 // $tmp_arr = '';
        //                 $opp_row = $tmp_arr;
        //                 // $tmp_row['status'] = "1";
        //                 $matched=1;
        //             }
        //             unset($row['added_date']);
        //             unset($row['matchup_id']);

        //              $opp_arr_user_detail = (!empty($user_data[$row['user_id']])) ? $user_data[$row['user_id']] : array();
                      
        //             if($row['answer'] == 1){
        //                 $tmp_row['yes'] = $row;
        //                 $tmp_row['yes']['user_name'] = $opp_arr_user_detail['user_name'];
        //                 $tmp_row['yes']['image'] = $opp_arr_user_detail['image'];
                        
        //                 if(!empty($opp_row)){
        //                     $tmp_row['no'] = $opp_row;
                           
        //                 }else{
        //                     $tmp_row['no']['entry_fee'] = number_format((10 - $row['entry_fee']),"1",".","");
        //                 }
        //             }else{
        //                 $tmp_row['no'] = $row;
        //                 $tmp_row['no']['user_name'] = $opp_arr_user_detail['user_name'];
        //                 $tmp_row['no']['image'] = $opp_arr_user_detail['image'];
        //                 if(!empty($opp_row)){
        //                     $tmp_row['yes'] = $opp_row;
        //                 }else{
        //                     $tmp_row['yes']['entry_fee'] = number_format((10 - $row['entry_fee']),"1",".","");
        //                 }
        //             } 
        //            $tmp_row['status']=  $row['status'];
        //            $tmp_row['matched']=  $matched;
        //             $final_list[] = $tmp_row;   
                                   
        //         }
        //         echo json_encode(array("response_code"=> 200,"status"=>1,"message"=>"","data"=>array_values($final_list)));exit;
        //     }else{
        //         echo json_encode(array("response_code"=> 200,"status"=>1,"message"=>"","data"=>array()));exit;
        //     }
        // }else{
        //     echo json_encode(array("response_code"=> 200,"status"=>0,"message"=>$msg,"data"=>array()));exit;
        // }
    }

      /**
     * Used for get participent list
     * @param void
     * @return string
     */
    public function get_question_detail_post()
    {

         $this->load->model('admin/Season_model');
        $this->form_validation->set_rules('question_id', $this->lang->line('question_id'), 'trim|required');
		if($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
		
		$post_data = $this->input->post();
		$question_id = $post_data['question_id'];
		$activity = $this->Season_model->get_trade_activity($post_data);
		$result = $activity['result'];
		
		// get user data
		$user_arr = array_values(array_unique(array_column($result,'user_id')));
		$m_user_arr = array_values(array_unique(array_column($result,'m_user_id')));
		if($m_user_arr){
			$m_user_arr = array_filter($m_user_arr, function ($value) {
				return !empty($value);
			});
		}
		$m_user_arr = ($m_user_arr)?$m_user_arr:array();
		$user_ids = array_values(array_unique(array_merge($user_arr,$m_user_arr)));
		if($user_ids){
			$user_data = $this->Season_model->get_participant_user_details($user_ids);
			$user_data_arr = array_column($user_data,NULL,"user_id");
			$activity['user_data'] = $user_data_arr;
		}
		
		$activity['result'] = $result;
		
		$this->api_response_arry['data'] = $activity;
        $this->api_response();
       
    }



    public function cancel_season_post() {
        $this->form_validation->set_rules('season_id', 'Season id', 'trim|required');      
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();    
        $this->load->model('admin/Season_model');
        $result = $this->Season_model->get_question_by_season($post_data['season_id']); 
        
        if(!empty($result)){

        $data_arr['status'] = 1;
        $output = $this->Season_model->update(SEASON_QUESTION,$data_arr,array("season_id"=>$post_data['season_id']));     

                 
        foreach($result as $row){
            $question_id = $row['question_id'];  
            $record_info = $this->Season_model->get_single_row('user_team_id,sports_id',USER_TEAM,array('question_id' => $question_id)); 
            $match_list_cache_key = 'match_list_'.$post_data['sports_id'];
            $this->delete_cache_data($match_list_cache_key);
            if(!empty($record_info)){                     
                     $this->load->helper('queue');
                      $server_name = get_server_host_name(); 
                    $content = array();
                    $content['url'] = $server_name."/trade/cron/cancel_question/".$question_id.'/1';              
                    add_data_in_queue($content,'cron');
                 }           
	      	}
		}

        $this->api_response_arry['message'] = "Fixture Cancelled Successfully.";
        $this->api_response();
    }


    public function cancel_question_post() {
         $this->load->model('admin/Season_model');
        $this->form_validation->set_rules('question_id', 'Question id', 'trim|required');       
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->post();
        $question_id = $post_data['question_id'];  
         $data_arr['status'] = 1;
         $output = $this->Season_model->update(SEASON_QUESTION,$data_arr,array("question_id"=>$question_id));        
        $record_info = $this->Season_model->get_single_row('user_team_id,sports_id',USER_TEAM,array('question_id' => $question_id));
        $match_list_cache_key = 'match_list_'.$post_data['sports_id'];
        $this->delete_cache_data($match_list_cache_key);     
        
        
        
        if(!empty($record_info)){         
            $this->load->helper('queue_helper');
            $server_name = get_server_host_name();
            $content = array();
            $content['url'] = $server_name."/trade/cron/cancel_question/".$question_id.'/1';     
            add_data_in_queue($content,'cron'); 
        }
       
        $this->api_response_arry['message'] = "Fixture Cancelled Successfully.";
        $this->api_response();
    }



     /**
    * Function used to pin any fixture to list at top
    * @param int $sports_id. league_id, season_game_uid
    * @return array
    */
    public function mark_pin_season_post()
    {
       $this->form_validation->set_rules('season_id', 'Season Id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
     
        $this->load->model('admin/Season_model');
        $result = $this->Season_model->mark_pin_season($post_data);
        if($result){
            //remove lobby fixture
            $this->push_s3_data_in_queue("lobby_fixture_list_".$post_data['sports_id'],array(),"delete");
            
            $match_list_cache_key = 'match_list_'.$post_data['sports_id'];
            $this->delete_cache_data($match_list_cache_key);

            $this->api_response_arry['message'] = "Fixture mark pin successfully";
            if(isset($post_data['is_pin_season']) && $post_data['is_pin_season'] == "0")
            {
                $this->api_response_arry['message'] = "Fixture removed from pin successfully";
            }
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('no_change');
            $this->api_response();
        }
    }


	



}