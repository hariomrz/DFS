<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Prediction extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
		$key =  $this->input->get_request_header(AUTH_KEY);
		$this->season_year = format_date('today','Y');
		$this->admin_lang = $this->lang->line('association');
		//Do your magic here
	}

     /**
     * @since Nov 2019
     * @uses function to get prediction status
     * @method get_prediction_status 
     * 
    */
    function get_prediction_status_post()
    {
       
       
        $result = $this->get_master_setting();
        
        $this->api_response_arry['data']       = $result;
        $this->api_response_arry['response_code']      = rest_controller::HTTP_OK;
        $this->api_response();
      
    }


    /**
     * @since Nov 2019
     * @uses function to update coin status
     * @method update_coins_status_post 
     * 
    */
    function update_prediction_status_post()
    {
        $this->form_validation->set_rules('status', 'Status', 'trim|required');
		
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }

        $post= $this->input->post();
        $this->load->model('Prediction_model');
        $result = $this->Prediction_model->update_prediction_status($post['status']);

        $this->http_post_request("auth/get_app_master_list",array(),2);
        if($result)
        {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
            if($post['status'] =='1')
            {

                $this->api_response_arry['global_error']  	= $this->lang->line('activate_prediction_status_success_msg');
            }
            else
            {

                $this->api_response_arry['global_error']  	= $this->lang->line('deactivated_prediction_status_success_msg');
            }
            $this->api_response();
        }
        else{
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->lang->line('prediction_status_error_msg');
            $this->api_response();
        }

    }

    /**
     * @since jan 2020
     * @uses function to play pause
     * @method pause_play_prediction 
     * 
    */
    function pause_play_prediction_post()
    {
        $this->form_validation->set_rules('pause', 'Pause', 'trim|required');
        $this->form_validation->set_rules('prediction_master_id', 'Pause', 'trim|required');
		
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }

        $post= $this->input->post();

        if(!in_array($post['pause'],array(0,1)))
        {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->lang->line('pause_value_invalid_err');
            $this->api_response();
        }


        $this->load->model('Prediction_model');

        $prediction = $this->Prediction_model->get_one_prediction($post['prediction_master_id']);

        if(!empty($prediction))
        {
            if(in_array($prediction['status'],array(1,2)))//prediction closed or prize distributed
            {
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= $this->lang->line('prediction_processed_error_msg');
                $this->api_response();
            }
        }

        $result = $this->Prediction_model->pause_play_prediction($post['pause'],$post['prediction_master_id']);

       
        if($result)
        {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
            
            $node_data=  array('prediction_master_id' => $post['prediction_master_id'],
            'season_game_uid' => $prediction['season_game_uid']);
            if($post['pause'] ==1)
            {
                $node_data['pause'] = 1;
                $this->api_response_arry['message']  	= $this->lang->line('prediction_pause_success_msg');
            }
            else
            {
                $node_data['pause'] = 0;
                //node data update
                $one_prediction = $this->Prediction_model->get_prediction_details($post['prediction_master_id']);
                $node_data['prediction'] = $one_prediction;
                $this->api_response_arry['message']  	= $this->lang->line('prediction_resume_success_msg');
            }
            if(SPORT_PREDICTOR_FEED == 1){
                $feed_push_params =$post;
                $feed_push_params['action'] = 3;
                $this->load->helper('queue');
                feed_mq_push($feed_push_params);//push pause play to WL client
                
            }

            $node_url = "pausePlayPrediction";
         
            $this->notify_prediction_to_client($node_url,$node_data);
            $this->api_response();
        }
        else{
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->lang->line('prediction_status_error_msg');
            $this->api_response();
        }

    }

    /**
     * @method get_season_list
     * @uses function to get season by match type
     * @param $_POST get  
     * **/
    function get_season_list_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports ID', 'trim|required');
        $this->form_validation->set_rules('match_type', 'Match Type', 'trim|required'); //1=> live , 2=> upcoming,3 =>completed

        if (!$this->form_validation->run()) {
            $this->send_validation_errors('get_season_list');
        }

        //get live match list
        $this->load->model('Prediction_model');
        $sports_id = $this->input->post('sports_id');
        $match_type = $this->input->post('match_type');
        $matches = $this->Prediction_model->get_all_season_schedule(array('match_type' =>$match_type ));
       // $upcoming_matches = $this->Prediction_model->get_all_season_schedule(array('upcoming' => 1));

        $question_arr = array();
        if(!empty($matches['result']))
        {
            $season_game_uids = array_column($matches['result'],'season_game_uid');
            $question_arr =$this->Prediction_model->get_season_question_count($sports_id,$season_game_uids);

            if(!empty($question_arr))
            {
                $question_arr = array_column($question_arr,'question_count','season_game_uid');
            }
        }

        foreach($matches['result'] as &$row)
        {
            if(isset($question_arr[$row['season_game_uid']]))
            {
                $row['question_count'] = $question_arr[$row['season_game_uid']];
            }
            else{
                $row['question_count'] = 0;
            }
        }

        $data = array();
        $data['fixtures'] = $matches;

        $this->response(array(config_item('rest_status_field_name') => TRUE,
        'data' => $data ,
        'response_code'=>rest_controller::HTTP_OK),
         rest_controller::HTTP_OK);
    }

    
    private function check_deadline_time()
    {
        $deadline_time =  $this->input->post('deadline_time');
        $current_date = format_date();

        if(strtotime($current_date) > strtotime($deadline_time))
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "deadline time can not be less then current time.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @method create_prediction
     * @uses function to create prediction
     * @param ARRAY $_POST
     * **/
    public function create_prediction_post()
    {  
        $post_params             = $this->input->post();
         if ($this->input->post()) {
            //$this->form_validation->set_rules('sports_id', 'Sports ID', 'trim|required');
            $this->form_validation->set_rules('season_game_uid', 'Season Game UID', 'trim|required');
            $this->form_validation->set_rules('question', 'Question', 'trim|required|max_length[200]');
            $this->form_validation->set_rules('options[]', 'Option(s)', 'trim|required');
            $this->form_validation->set_rules('deadline_date', 'Closure Date & Time', 'trim|required');
            $this->form_validation->set_rules('site_rake', 'Site Rake', 'trim');
            $this->form_validation->set_rules('sports_id', 'Sports Id', 'trim|required');

            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

           // $this->check_deadline_time();

            $post_data = $this->input->post();
            $options    = array_column($post_data['options'],"text");
            $option_count = count($options);

            foreach($options as $key =>  $option_text)
            {
                if(strlen($option_text) > 30)
                {
                    $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Option length can not be greater than 20 characters",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
                }

                if(empty(trim($option_text)))
                {
                    unset($options[$key]);
                }

            }

            if(empty($options) || $option_count < 2 || $option_count > 4 )
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Invalid options",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
            }    
            

            $feed_date_time = $post_data['deadline_date']." Asia/Kolkata";
                   // echo " old time : ".$feed_date_time;
            $date = new DateTime($feed_date_time);
            $tz = new DateTimeZone(DEFAULT_TIME_ZONE);
            $date->setTimezone($tz);
            //print_r($date);die;
            $deadline_date   = $date->format('Y-m-d H:i:s');
            $is_prediction_feed = isset($post_data['is_prediction_feed']) && $post_data['is_prediction_feed'] ==1 ? 1 : 0;
            $prediction_arr = array(
                'desc'                  => $post_data['question'],
                'season_game_uid'       => $post_data['season_game_uid'],
                //'site_rake'             => !empty($post_data['site_rake'])? $post_data['site_rake']:0 ,
                'deadline_date'         => $deadline_date,
                'added_date'            => format_date(),
                'updated_date'          => format_date(),
                'sports_id'             => $post_data['sports_id'],
                'is_prediction_feed'    => $is_prediction_feed
            );
            
            $this->load->model('Prediction_model');
            $prediction_master_id = $this->Prediction_model->add_prediction($prediction_arr);

            if(empty($prediction_master_id))
            {
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']         = "Prediction Not inserted!Try again.";
                $this->api_response_arry['service_name']    = 'create_prediction';
                $this->api_response();
            }    
    
             //insert options in db
            $options_arr = array();
            foreach ($options as $key => $value)
            {
                if(empty($value))
                {
                    continue;
                }    

                $options_arr[] = array(
                    'option'                => $value,
                    'prediction_master_id'  => $prediction_master_id,
                    'added_date'            => format_date(),
                    'updated_date'          => format_date()
                );
            }

            if(!empty($options_arr))
            {
                $this->Prediction_model->insert_prediction_option($options_arr);
            }  

            $this->push_s3_data_in_queue("lobby_fixture_list_prediction_".$post_data['sports_id'],array(),"delete"); 

            //delete cache
            $prediction_cache_key = 'l_f_list_p_'.$post_data['sports_id'];
            $this->delete_cache_data($prediction_cache_key);

            $allow_game_center =  isset($this->app_config['allow_game_center'])?$this->app_config['allow_game_center']['key_value']:0;
            if($allow_game_center == 1){
                $match_info = $this->Prediction_model->get_sports_fixture_collection($post_data);
                if(!empty($match_info) && $match_info['is_gc'] == "0"){
                    $this->Prediction_model->update_match_gc_status($match_info['collection_master_id']);
                }
            }

            //node data update
            $one_prediction = $this->Prediction_model->get_prediction_details($prediction_master_id);
            $one_prediction['is_pin'] = 0;
            $one_prediction['total_predictions'] = 0;
            $node_url = "newPredictionAlert";
            $node_data=  array('season_game_uid' => $post_data['season_game_uid'],
										'prediction' => $one_prediction);
            $this->notify_prediction_to_client($node_url,$node_data);

         
            //add to queue
            $this->rabbit_mq_push(array(
            'prediction_master_id'=>$prediction_master_id,
            'season_game_uid' => $post_data['season_game_uid'],
            'sports_id'=>$post_data['sports_id'],
            "prediction_action"    => 2 ,
            'question' => $post_data['question']
                ),'prediction');
         
            // Feed code to publish question in whitelabel
            if($is_prediction_feed == 1 && SPORT_PREDICTOR_FEED == 1){

                $this->load->helper('queue');
                $feed_push_params = array(
                    'prediction_master_id'=>$prediction_master_id,
                    'desc'=>$one_prediction['desc'],
                    'season_game_uid' => $one_prediction['season_game_uid'],
                    'sports_id' => $one_prediction['sports_id'],
                    'deadline_date' => $one_prediction['deadline_date'],
                    'total_pool' => $one_prediction['total_pool'],
                    'prize_pool' => $one_prediction['prize_pool'],
                    'action'=>1,
                    'question' => $post_data['question']
                    
                );
                $options_data =[];
                foreach ($one_prediction['option'] as $key => $value) {
                    $options_data[] = array('id'=>$value['prediction_option_id'],'value'=>$value['option']);
                }
                $feed_push_params['options'] = json_encode($options_data);
                feed_mq_push($feed_push_params);
            }

                $this->response(array(config_item('rest_status_field_name') => TRUE, 'message' =>"Prediction has been added successfully." ,'response_code'=>rest_controller::HTTP_OK), rest_controller::HTTP_OK);
           
        } else {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Prediction not added! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
         
    }

    /**
     * @method update_prediction
     * @uses function to create prediction
     * @param ARRAY $_POST
     * **/
    public function update_prediction_post()
    {
        
        $post_params             = $this->input->post();
         if ($this->input->post()) {
            //$this->form_validation->set_rules('sports_id', 'Sports ID', 'trim|required');
            $this->form_validation->set_rules('prediction_master_id', 'Prediction Master ID', 'trim|required');
            $this->form_validation->set_rules('season_game_uid', 'Season Game UID', 'trim|required');
            $this->form_validation->set_rules('question', 'Question', 'trim|required|max_length[200]');
            $this->form_validation->set_rules('options[]', 'Option(s)', 'trim|required');
            $this->form_validation->set_rules('deadline_date', 'Closure Date & Time', 'trim|required');
            $this->form_validation->set_rules('site_rake', 'Site Rake', 'trim');
            $this->form_validation->set_rules('sports_id', 'Sports Id', 'trim|required');

            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

           // $this->check_deadline_time();

           $post_data = $this->input->post();
           $prediction_master_id = $post_data['prediction_master_id'];
           $this->load->model('Prediction_model');
           $this->db = $this->Prediction_model->db_prediction;
           $prediction_row = $this->Prediction_model->get_single_row('total_user_joined',PREDICTION_MASTER,array('prediction_master_id' => $prediction_master_id));

           if(isset($prediction_row['total_user_joined']) && $prediction_row['total_user_joined'] > 0)
           {
               $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "You can not update this Prediction, It is joined by few users.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
           }
            $options    = array_column($post_data['options'],"text");
            $option_count = count($options);

            foreach($options as $key =>  $option_text)
            {
                if(strlen($option_text) > 30)
                {
                    $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Option length can not be greater than 20 characters",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
                }

                if(empty(trim($option_text)))
                {
                    unset($options[$key]);
                }

            }

            if(empty($options) || $option_count < 2 || $option_count > 4 )
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Invalid options",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
            }    
            

            $feed_date_time = $post_data['deadline_date']." Asia/Kolkata";
                   // echo " old time : ".$feed_date_time;
            $date = new DateTime($feed_date_time);
            $tz = new DateTimeZone(DEFAULT_TIME_ZONE);
            $date->setTimezone($tz);
            //print_r($date);die;
            $deadline_date   = $date->format('Y-m-d H:i:s');

            $prediction_arr = array(
                'desc'                  => $post_data['question'],
                'season_game_uid'       => $post_data['season_game_uid'],
                //'site_rake'             => !empty($post_data['site_rake'])? $post_data['site_rake']:0 ,
                'deadline_date'         => $deadline_date,
                'added_date'            => format_date(),
                'updated_date'          => format_date(),
                'sports_id'             => $post_data['sports_id']
            );
            
            
            $affected_count = $this->Prediction_model->update_prediction($prediction_master_id,$prediction_arr);

            if(empty($affected_count))
            {
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']         = "Prediction Not updated!Try again.";
                $this->api_response_arry['service_name']    = 'create_prediction';
                $this->api_response();
            }
    
    
             //insert options in db
            $options_arr = array();
            foreach ($options as $key => $value)
            {
                if(empty($value))
                {
                    continue;
                }    

                $options_arr[] = array(
                    'option'                => $value,
                    'prediction_master_id'  => $prediction_master_id,
                    'added_date'            => format_date(),
                    'updated_date'          => format_date()
                );
            }

            if(!empty($options_arr))
            {
                $this->Prediction_model->update_prediction_option($prediction_master_id,$options_arr);
            }  

            $this->push_s3_data_in_queue("lobby_fixture_list_prediction_".$post_data['sports_id'],array(),"delete"); 

            //node data update
            // $one_prediction = $this->Prediction_model->get_prediction_details($prediction_master_id);
            // $one_prediction['is_pin'] = 0;
            // $one_prediction['total_predictions'] = 0;
            // $node_url = "newPredictionAlert";
            // $node_data=  array('season_game_uid' => $post_data['season_game_uid'],
			// 							'prediction' => $one_prediction);
            // $this->notify_prediction_to_client($node_url,$node_data);

            //add to queue
            // $this->rabbit_mq_push(array('prediction_master_id'=>$prediction_master_id,
            // 'season_game_uid' => $post_data['season_game_uid'],
            // "prediction_action"    => 2 ,
            // 'question' => $post_data['question']
            //     ),'prediction');
                 // Feed code to publish question in whitelabel
            if($post_params['is_prediction_feed'] == 1 && SPORT_PREDICTOR_FEED == 1){
                $one_prediction = $this->Prediction_model->get_prediction_details($prediction_master_id);
                $feed_push_params = array(
                    'prediction_master_id'=>$prediction_master_id,
                    'desc'=>$one_prediction['desc'],
                    'deadline_date' => $one_prediction['deadline_date'],
                    'action'=>5
                    
                );
                $options_data =[];
                foreach ($one_prediction['option'] as $key => $value) {
                    $options_data[] = array('id'=>$value['prediction_option_id'],'value'=>$value['option']);
                }
                $feed_push_params['options'] = json_encode($options_data);
                $this->load->helper('queue');
                feed_mq_push($feed_push_params);
            }
      
                $this->response(array(config_item('rest_status_field_name') => TRUE, 'message' =>"Prediction has been updated successfully." ,'response_code'=>rest_controller::HTTP_OK), rest_controller::HTTP_OK);
           
        } else {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Prediction not updated! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
         
    }
        
        
	public function get_all_prediction_post()
	{
        $this->form_validation->set_rules('season_game_uid', 'Game ID', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $this->load->model('Prediction_model');
        $data = array();
        $data['predictions'] = $this->Prediction_model->get_all_prediction();

		
        $this->response(array(config_item('rest_status_field_name') => TRUE, 
        'data' =>$data ,
        'response_code'=>rest_controller::HTTP_OK), rest_controller::HTTP_OK);
    }
    
    public function update_pin_prediction_post()
    {
        $this->form_validation->set_rules('prediction_master_id', 'Prediction Master Id', 'trim|required');
        $this->form_validation->set_rules('is_pin', 'is_pin', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post= $this->input->post();
        $this->load->model('Prediction_model');
        $result = $this->Prediction_model->update_pin_prediction($post['is_pin'],$post['prediction_master_id']);
        if($result)
        {   
            if(SPORT_PREDICTOR_FEED == 1){
                $feed_push_params = $post;
                $feed_push_params['action'] =4;
                $this->load->helper('queue');
                feed_mq_push($feed_push_params);

            }
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
            $this->api_response_arry['global_error']  	= $this->lang->line('prediction_pin_success_msg');
            $this->api_response();
        }
        else{
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= $this->lang->line('prediction_pin_error_msg');
            $this->api_response();
        }

    }

     private function get_prediction_with_options()
    {
        $post = $this->input->post();
        $this->load->model('Prediction_model');
        $result = $this->Prediction_model->get_one_prediction_details($post['prediction_master_id']);

        $season_data = $this->Prediction_model->get_match_details($result[0]['season_game_uid'],$result[0]['sports_id']);

        $prediction_data = array_merge($result[0],$season_data);
        return $prediction_data;
    }

    public function submit_prediction_answer_post()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('prediction_master_id', 'Prediction Master Id', 'trim|required');
            $this->form_validation->set_rules('prediction_option_id', 'Prediction Option', 'trim|required');
            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

            $this->load->model('Prediction_model');
            $post = $this->input->post();
            $prediction = $this->Prediction_model->get_prediction_answer($post['prediction_master_id']);
    
            if(!empty($prediction))
            {
               
                if(isset($prediction['is_correct']) && $prediction['is_correct']=='1')
                {
                    $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['global_error']  	= $this->lang->line('prediction_processed_error_msg');
                    $this->api_response();
               
                }
                
            }
            else
            { 
                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']  	= $this->lang->line('invalid_prediction');
                $this->api_response();
            }
          
            $prediction_status_arr = array(
                "status"                 => 1, 
                "updated_date"           => format_date()
             );  

            $this->db_prediction		= $this->load->database('db_prediction', TRUE);
            $this->db_prediction->trans_start();
            $this->Prediction_model->update_prediction_results($post['prediction_option_id']);
            $this->Prediction_model->update_prediction_result_status($post['prediction_master_id'],$prediction_status_arr);

            $this->db_prediction->trans_complete();

            if ($this->db_prediction->trans_status() === FALSE)
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Prediction result not udpated! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
              
                    // generate an error... or use the log_message() function to log your error
            }
            else
            {
                //get prediction data
               $prediction_data = $this->get_prediction_with_options();

                $queue_content = array(
                    "prediction_master_id" => $post['prediction_master_id'],
                    "status"               => 1,
                    "added_on_queue"       => format_date(),
                    "prediction_action"    => 1 ,
                    "prediction_data" => $prediction_data
                 );
    
                $this->rabbit_mq_push($queue_content, 'prediction');


                //Push answer data in exchange and broadcast to all WL client
                if(!empty($prediction) && $prediction['is_prediction_feed'] == 1 && SPORT_PREDICTOR_FEED == 1)
                {
                    $feed_push_params = $post;
                    $feed_push_params['action'] = 2;
                    $this->load->helper('queue');
                    feed_mq_push($feed_push_params);

                }

                $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
                $this->api_response_arry['message']  	= $this->lang->line('prediction_result_submited');
                $this->api_response();
            }    

        }
        else 
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Prediction result not udpated! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

	/**
     * [create_leagues_post description]
     * Summary :-
     * @return [type] [description]
     */
    public function submit_prediction_results_post() {
        if ($this->input->post()) {
            $this->form_validation->set_rules('season_game_uid', 'Season Game UID', 'trim|required');
            $this->form_validation->set_rules('prediction_result_data[]', 'Prediction Result', 'trim');
            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

            $prediction_data = $this->input->post();
            

            if(!empty($prediction_data['prediction_result_data']))
            {
                $prediction_result_arr = array();
                $prediction_status_arr = array();
                foreach ($prediction_data['prediction_result_data'] as $pkey => $pvalue) 
                {
                    $selected_option_id = $pvalue['selected_option_id'];
                    if(!empty($pvalue['options']))
                    {
                        foreach ($pvalue['options'] as $okey => $ovalue)
                        {
                          $prediction_result_arr[] = array(
                            "prediction_option_id" => $ovalue['prediction_option_id'],
                            "is_correct"           => ($ovalue['prediction_option_id'] == $selected_option_id) ? 1 : 0,
                          );  
                        }
                    }

                    if(!empty($selected_option_id))
                    {
                        $prediction_status_arr[] = array(
                           "prediction_master_id"   => $pvalue['prediction_master_id'],
                           "status"                 => 1, 
                           "updated_date"           => format_date()
                        );
                    }    

                }
            }
            else
            {
                $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Prediction result not udpated! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);   
            }    



           // echo '<pre>';print_r($prediction_result_arr);die;
           if(!empty($prediction_result_arr))
           {
                $post_add_team_url = 'prediction/submit_prediction_results';
                $params = array('prediction_result_data' => $prediction_result_arr,"prediction_status_data"=>$prediction_status_arr);
                $response = $this->http_post_request($post_add_team_url, $params,2);
                if ($response["response_code"] == 200) {
                    $this->response(array(config_item('rest_status_field_name') => TRUE, 'message' => "Prediction result udpated.",'response_code'=>rest_controller::HTTP_OK), rest_controller::HTTP_OK);
                } else {
                    $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Prediction result not udpated! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
                }
           }    
        } 
        else 
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Prediction result not udpated! Please try again.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * [get_prediction_participants description]
     * @uses :- get participants
     * @param Number prediction master id
     */
    public function get_prediction_participants_post()
	{
        $this->form_validation->set_rules('prediction_master_id', 'Prediction ID', 'trim|required');
            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

            $prediction_master_id = $this->input->post('prediction_master_id');
            $this->load->model('Prediction_model');
            $result = $this->Prediction_model->get_prediction_participants($prediction_master_id);

            if(!empty($result['prediction_participants']))
            {
                $user_ids = array_unique( array_column($result['prediction_participants'],'user_id'));
                $user_details = $this->Prediction_model->get_users_by_ids($user_ids);
                if(!empty($user_details))
                {
                    $user_details = array_column($user_details,'user_name','user_id');
                }

                foreach($result['prediction_participants'] as & $val)
                {
                    $val['username'] = '';
                    if(isset($user_details[$val['user_id']]))
                    {
                        $val['user_name'] =  $user_details[$val['user_id']];
                    }
                }

            }

            $this->response(array(config_item('rest_status_field_name') => TRUE,
             'data' => $result,
             'response_code'=>rest_controller::HTTP_OK),
              rest_controller::HTTP_OK);
            
    }
    
     /**
     * [get_prediction_counts description]
     * @uses :- get prediction counts
     * @param Number sports id
     */

    function get_prediction_counts_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports ID', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $sports_id = $this->input->post('sports_id');

        $this->config->load('prediction_config');
        $trending_types = $this->config->item('trending_prediction');

        $this->load->model('Prediction_model');

        if(!isset($trending_types[3]['func']) || !isset($trending_types[4]['func']))
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please select a valid tab.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }    

        $one_bid_function_name = $trending_types[3]['func'];
        $no_bid_function_name = $trending_types[4]['func'];

        $data = array();
        $_POST['tab_no'] = 3;
        $data['one_bid_count'] = $this->Prediction_model->$one_bid_function_name(TRUE);    
        $_POST['tab_no'] = 4;
        $data['no_bid_count'] = $this->Prediction_model->$no_bid_function_name(TRUE);    

        $this->response(array(config_item('rest_status_field_name') => TRUE,
        'data' => $data,
        'response_code'=>rest_controller::HTTP_OK),
         rest_controller::HTTP_OK);

    }


     /**
     * [get_trending_predictions description]
     * @uses :- get trending predictions,recent, popular, 1 bid no bid
     * @param Number 1,2,3,4 for tab_no, sports id
     */
    public function get_trending_predictions_post()
    {
        $this->form_validation->set_rules('tab_no', 'tab no', 'trim|required');
        $this->form_validation->set_rules('sports_id', 'Sports ID', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $tab_no = $this->input->post('tab_no');
        $sports_id = $this->input->post('sports_id');

        $this->config->load('prediction_config');
        $trending_types = $this->config->item('trending_prediction');

        $this->load->model('Prediction_model');

        if(!isset($trending_types[$tab_no]['func']))
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please select a valid tab.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }    

        $function_name = $trending_types[$tab_no]['func'];

        $result = $this->Prediction_model->$function_name();    

        $season_data = array();
        if(!empty($result['result']))
        {
            //get season ids
            $season_game_uids = array_unique(array_column($result['result'],'season_game_uid'));

            if(!empty($season_game_uids))
            {
                $param['season_game_uids'] = $season_game_uids;
                $param['sports_id'] = $sports_id;
               
                $season_data = $this->Prediction_model->get_season_matches_by_ids($param);
                if(!empty($season_data))
                {
                    $season_data = array_column($season_data,NULL,'season_game_uid'); 
                }

                foreach($result['result'] as &$val)
                {
                    if(isset($season_data[$val['season_game_uid']]))
                    {
                        $val['home'] = $season_data[$val['season_game_uid']]['home'];
                        $val['away'] = $season_data[$val['season_game_uid']]['away'];
                        $val['season_scheduled_date'] = $season_data[$val['season_game_uid']]['season_scheduled_date'];
                    }
                }
            }
        }

        $this->response(array(config_item('rest_status_field_name') => TRUE,
        'data' => $result,
        'response_code'=>rest_controller::HTTP_OK),
         rest_controller::HTTP_OK);

    }

      /**
     * [delete_prediction_post description]
     * @uses :- delete prediction
     * @param Number 1,2,3,4 for tab_no, sports id
     */
    function delete_prediction_post()
    {
        $this->form_validation->set_rules('prediction_master_id', 'Prediction ID', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $prediction_master_id = $this->input->post('prediction_master_id');
        $this->load->model('Prediction_model');
        $prediction_result = $this->Prediction_model->get_one_prediction($prediction_master_id);

        if(empty($prediction_result))
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Not a valid prediction.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
       
        if( $prediction_result['status'] > 0)
        {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Only open prediction can be deleted.",'response_code'=>rest_controller::HTTP_INTERNAL_SERVER_ERROR), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }   


        $delete_result = $this->Prediction_model->delete_prediction($prediction_master_id);

        if($prediction_result['total_user_joined'] > 0)
        {
            //refund process
            $this->rabbit_mq_push(array('prediction_master_id'=>$prediction_master_id,
            "prediction_action"    => 0 
        ),'prediction');
        }

        //Delete the publish question in ALL WL Client
        if(SPORT_PREDICTOR_FEED == 1){
            $feed_push_params = array('prediction_master_id'=>$prediction_master_id,'action'=>6);
            $this->load->helper('queue');
            feed_mq_push($feed_push_params);
        }

        $node_url = "deletePrediction";
            $node_data=  array('season_game_uid' => $prediction_result['season_game_uid'],
										'prediction_master_id' => $prediction_master_id);
            $this->notify_prediction_to_client($node_url,$node_data);

        $this->response(array(config_item('rest_status_field_name') => TRUE,
        'message' => 'Prediction Deleted.',
        'response_code'=>rest_controller::HTTP_OK),
         rest_controller::HTTP_OK);
    }

    /**
     * @method get_coins_vs_users_graph_week
     * @uses function for weekly
     * @param Array from_date and to_date
    */
    function get_coins_vs_users_graph_week_post()
    {
        $post = $this->input->post();
        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d',strtotime(format_date().' -70 days'));
            $post['to_date'] = date('Y-m-d',strtotime(format_date()));
        }

        $this->load->model('Prediction_model');

        $result =  $this->Prediction_model->get_prediction_invested_coins_weekly($post);

        $categories = array();
        $series_data = array();
        $total_coins = 0;
        if(!empty($result['result']))
        {
            $categories = array_column($result['result'],'created');
            $series_data = array_column($result['result'],'week_points');
            $total_coins = array_sum($series_data);
        }

        foreach($series_data as &$val)
        {
            $val = (int)$val;

        }

        //get users count
        $user_result =  $this->Prediction_model->get_prediction_invested_users_weekly($post);
        $user_data = array();
        $total_users = 0;
        if(!empty($user_result['result']))
        {
            $user_data = array_column($user_result['result'],'user_count');
            $total_users = array_sum($user_data);
        }


        foreach($user_data as &$val)
        {
            $val = (int)$val;
        }

        $this->api_response_arry['data']['graph_data'] = array('user_data'=> $user_data,'coin_data' => $series_data);
        $this->api_response_arry['data']['total_user'] 	= $total_users;
        $this->api_response_arry['data']['total_coins'] 	= $total_coins;
        $this->api_response_arry['data']['dates'] 	= $categories;
        $this->api_response(); 
    }

    /**
     * @method get_coins_vs_users_graph_monthly
     * @uses function for monthly
     * @param Array from_date and to_date
    */
    function get_coins_vs_users_graph_monthly_post()
    {
        $post = $this->input->post();
        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d',strtotime(format_date().' -70 days'));
            $post['to_date'] = date('Y-m-d',strtotime(format_date()));
        }

        $this->load->model('Prediction_model');

        $result =  $this->Prediction_model->get_prediction_invested_coins_monthly($post);

        $categories = array();
        $series_data = array();
        $total_coins = 0;
        if(!empty($result['result']))
        {
            $categories = array_column($result['result'],'month_year');
            $series_data = array_column($result['result'],'month_points');
            $total_coins = array_sum($series_data);
        }


        foreach($series_data as &$val)
        {
            $val = (int)$val;

        }
        //get users count
        $user_result =  $this->Prediction_model->get_prediction_invested_users_monthly($post);
        $user_data = array();
        $total_users = 0;
        if(!empty($user_result['result']))
        {
            $user_data = array_column($user_result['result'],'user_count');
            $total_users = array_sum($user_data);
        }

        foreach($user_data as &$val)
        {
            $val = (int)$val;
        }


        $this->api_response_arry['data']['graph_data'] = array('user_data'=> $user_data,'coin_data' => $series_data);
        $this->api_response_arry['data']['total_user'] 	= $total_users;
        $this->api_response_arry['data']['total_coins'] 	= $total_coins;
        $this->api_response_arry['data']['dates'] 	= $categories;
        $this->api_response(); 
    }

    function get_coins_vs_users_graph_post()
    {

       
        $post = $this->input->post();

        if(isset($post['filter']) && $post['filter'] == 'weekly' )
        {
            $this->get_coins_vs_users_graph_week_post();
        }

        if(isset($post['filter']) && $post['filter'] == 'monthly' )
        {
            $this->get_coins_vs_users_graph_monthly_post();
        }


        if(empty($post['from_date']) || empty($post['to_date']))
        {
            $post['from_date'] = date('Y-m-d',strtotime(format_date().' -70 days'));
            $post['to_date'] = date('Y-m-d',strtotime(format_date()));
        }

        $this->load->model('Prediction_model');

        //$data
            /**
             * {
            *  name: 'Tokyo',
            * data: [7.0, 6.9, 9.5, 14.5, 18.4, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
            * }
                    * **/

        $Dates = get_dates_from_range($post['from_date'], $post['to_date']); 

       
        $result =  $this->Prediction_model->get_prediction_invested_coins($post);

        $data = array();
        $str_dates = array();
       foreach($Dates as $oneDate)
       {
           $date = strtotime($oneDate) ;
           $str_dates[] = $date;
           foreach($result['result'] as $row)
           {  
               $main_date = strtotime($row['date_added']);

               if(!in_array($main_date,$str_dates))
               {
                   $data['coin_data'][$date] = 0;
               }
               else
               {
                   if(isset($data['coin_data'][$main_date]))
                    {
                        $data['coin_data'][$main_date] += $row['points'];
                    }
                    else
                    {
                        $data['coin_data'][$main_date] = $row['points'];
                    }
               }  
           }
       }

       $user_result = $this->Prediction_model->get_prediction_invested_users($post);
       $str_dates = array();
       foreach($Dates as $oneDate)
       {
           $date = strtotime($oneDate) ;
           $str_dates[] = $date;
           foreach($user_result['result'] as $row)
           {  
               $main_date = strtotime($row['date_added']);

               if(!in_array($main_date,$str_dates))
               {
                   $data['user_data'][$date] = 0;
               }
               else
               {
                   if(isset($data['user_data'][$main_date]))
                    {
                        $data['user_data'][$main_date] += $row['user_count'];
                    }
                    else
                    {
                        $data['user_data'][$main_date] = $row['user_count'];
                    }
               }  
           }
       }

       foreach($Dates as &$date)
       {
           $date = date('d M',strtotime($date));
       }

        // echo '<pre>';
        // print_r($user_result);
        // print_r($data);
        // print_r($result);
        // die('dfd');

       if(!empty($data['coin_data']))
       {
           $data['coin_data'] = array_values($data['coin_data']);
       }

       if(!empty($data['user_data']))
       {
           $data['user_data'] = array_values($data['user_data']);
       }


        $this->api_response_arry['data']['graph_data'] = $data;
        $this->api_response_arry['data']['total_user'] 	= $user_result['total'];
        $this->api_response_arry['data']['total_coins'] 	= $result['total'];
        $this->api_response_arry['data']['dates'] 	= $Dates;
        $this->api_response(); 


    }

     /**
     * [most_win_leaderboard_post description]
     * @uses :- function to get most win leader board
     * @param NA
     */
    function most_win_leaderboard_post()
    {
        $this->load->model('Prediction_model');
        $post = $this->input->post();
        $result =$this->Prediction_model->get_most_win_leaderboard($post);
        $count_result = $this->Prediction_model->get_most_win_count();
        $this->api_response_arry['data']['list'] = $result['list'];
        $this->api_response_arry['data']['total'] 	= $count_result['total'];
        $this->api_response_arry['data']['next_offset'] = $result['next_offset'];
        $this->api_response(); 

    }

    /**
     * [most_bid_leaderboard_post description]
     * @uses :- function to get most bid leaderboard
     * @param NA
     */
    function most_bid_leaderboard_post()
    {
        $this->load->model('Prediction_model');
        $post = $this->input->post();
        $result =$this->Prediction_model->get_most_bid_leaderboard($post);
        $count_result = $this->Prediction_model->get_most_bid_count();
        $this->api_response_arry['data']['list'] = $result['list'];
        $this->api_response_arry['data']['total'] 	= $count_result['total'];
        $this->api_response_arry['data']['next_offset'] = $result['next_offset'];
        $this->api_response(); 

    }

    function get_top_team_graph_post()
    {
      
        $this->form_validation->set_rules('sports_id', 'Sports ID', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        $this->load->model('Prediction_model');
        $season_result =$this->Prediction_model->get_top_seasons();

        if(!empty($season_result))
		{
			$season_game_uid= array_column($season_result,'season_game_uid');
             $season_map  =array_column($season_result,NULL,'season_game_uid');
			$this->load->model('season/Season_model');

            $season_data = $this->Season_model->get_season_matches_by_ids(array('season_game_uids' => $season_game_uid,
            'sports_id' => $post['sports_id']
        ));

            if(!empty($season_data))
            {
                $season_result_map = array_column($season_data,NULL,'season_game_uid');
            }
		
            $data = array();
            foreach($season_map as $s_key => $match)
            {
                if(isset($season_result_map[$s_key]))
                {
                    if(isset($data['top_team_data']) && isset($data['top_team_data'][$season_result_map[$s_key]['home']]))
                    {
                        $data['top_team_data'][$season_result_map[$s_key]['home']] +=(int)$match['user_count'];

                    }
                    else
                    {
                        
                        $data['top_team_data'][$season_result_map[$s_key]['home']] =(int)$match['user_count'];
                    }

                    if(isset($data['top_team_data'][$season_result_map[$s_key]['away']]))
                    {
                        $data['top_team_data'][$season_result_map[$s_key]['away']] +=(int)$match['user_count'];
                    }
                    else
                    {
                        $data['top_team_data'][$season_result_map[$s_key]['away']] =(int)$match['user_count'];
                    }
                    
                }

            }

            //echo '<pre>';
            aksort($data['top_team_data'],true,true);
            
            $categories = array_keys($data['top_team_data']);
            // print_r($data);
            // print_r($categories);
            // die('dfd');

            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
            $this->api_response_arry['data']['series'] = array_slice(array_values($data['top_team_data']),0,10) ;
            $this->api_response_arry['data']['categories'] = array_slice($categories,0,10) ;
            $this->api_response_arry['data']['team_count'] = count($categories) ;
           // $this->api_response_arry['data']['closing_balance'] = $closing_balance['closing_balance'];
            $this->api_response();   

		}


    }

   /**
    * Add question to feed
    * @param @question data
    * @return string message
    */
    public function add_prediction_feed_post()
    {
       
        $this->form_validation->set_rules('season_game_uid', 'Season Game UID', 'trim|required');
        $this->form_validation->set_rules('prediction_master_id', 'Prediction Master Id', 'trim|required');
        $this->form_validation->set_rules('question', 'Question', 'trim|required|max_length[200]');
        $this->form_validation->set_rules('options[]', 'Option(s)', 'trim|required');
        $this->form_validation->set_rules('deadline_date', 'Closure Date & Time', 'trim|required');
        $this->form_validation->set_rules('sports_id', 'Sports Id', 'trim|required');

        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $this->load->model('Prediction_model');
        $result = $this->Prediction_model->update_prediction($post_data['prediction_master_id'],['is_prediction_feed'=>1]);
        if(!empty($result)){
                $feed_push_params = array(
                    'prediction_master_id'=>$post_data['prediction_master_id'],
                    'desc'=>$post_data['question'],
                    'season_game_uid' => $post_data['season_game_uid'],
                    'sports_id' => $post_data['sports_id'],
                    'deadline_date' => $post_data['deadline_date'],
                    'total_pool' => 0,
                    'prize_pool' => 0,
                    'action'=>1
                    
                );
                $options_data =[];
                foreach ($post_data['options'] as $key => $value) {
                    $options_data[] = array('id'=>$value['id'],'value'=>$value['value']);
                }
                $feed_push_params['options'] = json_encode($options_data);
                $this->load->helper('queue');
                feed_mq_push($feed_push_params);
        }

        $this->api_response_arry['data']['message'] = 'Prediction added to feeds';
        $this->api_response(); 
    }

   

}
