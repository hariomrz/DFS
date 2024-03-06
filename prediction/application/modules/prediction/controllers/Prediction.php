<?php
class Prediction extends Common_Api_Controller 
{
	function __construct()
	{
        parent::__construct();
        $allow_prediction_system = isset($this->app_config['allow_prediction_system']) ? $this->app_config['allow_prediction_system']['key_value'] : 0;
        if(!$allow_prediction_system)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;;
            $this->api_response_arry['global_error']  ="Module not activated." ;
            $this->api_response();
        }
    }

     /**
     * @method get_lobby_fixture
     * @uses Used for get lobby fixture list for prediction
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
        $current_date = format_date();
        $sports_id = $post_data["sports_id"];
        $lobby_fixture_cache = 'l_f_list_p_'.$sports_id;
        $collection_result_arr = array();
        $match_list = array();
        if(!isset($post_data['is_cron_data'])){
            $match_list = $this->get_cache_data($lobby_fixture_cache);
        }

        if(!$match_list){
            $this->load->model("prediction/prediction_model");
            $season_list = $this->prediction_model->get_prediction_season_list($sports_id);
            if(!empty($season_list)){
                $season_game_uids = array_unique(array_column($season_list,"season_game_uid"));
                if(!empty($season_game_uids)){
                    $match_list = $this->prediction_model->get_fixture_season_detail($sports_id,$season_game_uids);
                    foreach($match_list as &$match){
                        $delay_text = "";
                        if($match['delay_minute'] > 0){
                            $fixture_delay = convert_minute_to_hour_minute($match['delay_minute']);
                            if($fixture_delay['hour'] > 0){
                                $delay_text = $fixture_delay['hour']." Hour ";
                            }
                            if($fixture_delay['minute'] > 0){
                                $delay_text.= $fixture_delay['minute']." Min ";
                            }
                        }
                        $match['delay_text'] = $delay_text;
                        $match['game_starts_in'] = (strtotime($match['season_scheduled_date']) - ($match['delay_minute'] * 60))*1000;
                        $match['current_time'] = strtotime($current_date)*1000;
                    }

                    $this->set_cache_data($lobby_fixture_cache,$match_list,300);
                }
            }
        }
        
        $response = array();
        $response['match_list'] = $match_list;

        //for upload app data on s3 bucket
        if(isset($post_data['is_cron_data']) && $post_data['is_cron_data'] == 1){
            $this->push_s3_data_in_queue("lobby_fixture_list_prediction_".$sports_id,$response);
        }

        $this->api_response_arry['data'] = $response;
        $this->api_response();
    }

    public function get_season_by_season_uid_post()
    {
        $post_data = $this->input->post();
        $this->load->model("prediction/prediction_model");
        $post_data['for_prediction'] = 1;
		$result_array =  $this->prediction_model->get_season_by_season_game_uids($post_data,0);
		foreach($result_array as $key => $rs) {
			$result_array[$key]["game_starts_in"] = strtotime($result_array[$key]["season_scheduled_date"])*1000;
            $result_array[$key]["today"] = strtotime(format_date())*1000;
            if (!empty($result_array[$key]['score_data'])) {
                $result_array[$key]['score_data'] = json_decode($result_array[$key]['score_data'], TRUE);
            }
		}
		
        $this->api_response_arry['data'] = ($result_array) ? $result_array : array();
		$this->api_response();
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

            //get own detail
            $own = array();
            if($this->user_id)
            {
                $own_result= $this->Prediction_model->get_prediction_participants($prediction_master_id,$this->user_id);
                if(!empty($own_result['other_list']))
                {
                    $result['other_list'][] = $own_result['other_list'][0];
                }
            }
            if(!empty($result['other_list']))
            {
                $this->load->model('user/User_model');
                $user_ids = array_unique( array_column($result['other_list'],'user_id'));
                $user_details = $this->User_model->get_users_by_ids($user_ids);
                if(!empty($user_details))
                {
                    $user_details = array_column($user_details,'user_name','user_id');
                }

                foreach($result['other_list'] as $key =>  & $val)
                {
                    $val['username'] = '';
                    if(isset($user_details[$val['user_id']]))
                    {
                        $val['user_name'] =  $user_details[$val['user_id']];
                    }

                    if($val['user_id'] == $this->user_id)
                    {
                        $own = $result['other_list'][$key];
                        unset($result['other_list'][$key]);
                    }
    
                }
            }

            

            if(!empty($own))
            {
                $result['own'] =$own;
            }
            $result['other_list'] = array_values($result['other_list']);
            $this->api_response_arry['service_name'] = 'get_season_by_season_uid';
            $this->api_response_arry['response_code'] = 200;
            $this->api_response_arry['data']          = $result;
            $this->api_response();
          
            
    }

    public function get_prediction_leaderboard_post()
    {
        $this->form_validation->set_rules('prediction_master_id', 'Prediction ID', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $prediction_master_id = $this->input->post('prediction_master_id');
        $this->load->model('Prediction_model');
        $result = $this->Prediction_model->get_prediction_leaderboard($prediction_master_id);
        $own_leaderboard = array();
        if($this->user_id)
        {
            $own_result= $this->Prediction_model->get_prediction_leaderboard($prediction_master_id,$this->user_id);
            if(!empty($own_result['other_list']))
            {
                $result['other_list'][] = $own_result['other_list'][0];
            }
        }


        if(!empty($result['other_list']))
        {
            $this->load->model('user/User_model');
            $user_ids = array_unique( array_column($result['other_list'],'user_id'));
            $user_details = $this->User_model->get_users_by_ids($user_ids);
            if(!empty($user_details))
            {
                $user_details = array_column($user_details,'user_name','user_id');
            }

            foreach($result['other_list'] as $key => & $val)
            {
                $val['username'] = '';
                if(isset($user_details[$val['user_id']]))
                {
                    $val['user_name'] =  $user_details[$val['user_id']];
                }

                if($val['user_id'] == $this->user_id)
                {
                    $own_leaderboard = $result['other_list'][$key];
                    unset($result['other_list'][$key]);
                }

            }

            if(!empty($own_leaderboard))
            {
                $result['own'] =$own_leaderboard;
            }

            $result['other_list'] = array_values($result['other_list']);
        }

        $this->api_response_arry['service_name'] = 'get_prediction_leaderboard';
        $this->api_response_arry['response_code'] = 200;
        $this->api_response_arry['data']          = $result;
        $this->api_response();
      

    }

     /**
     * @method make_prediction_post
     * @uses function to make prediction
     * **/
    function make_prediction_post()
    {
       
        $this->form_validation->set_rules('prediction_master_id', 'Prediction ID', 'trim|required');
        $this->form_validation->set_rules('prediction_option_id', 'Prediction Option ID', 'trim|required');
        $this->form_validation->set_rules('bet_coins', 'Bet Coins', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post = $this->input->post();

        $min_bet_coins =  isset($this->app_config['min_bet_coins'])?$this->app_config['min_bet_coins']['key_value']:0;
        if($post['bet_coins'] < $min_bet_coins)
        {
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['global_error']  =str_replace('#min_bet_coins#',$min_bet_coins,$this->lang->line('prediction_min_coin_error'));  ;
            $this->api_response();
        }

        $max_bet_coins =  isset($this->app_config['max_bet_coins'])?$this->app_config['max_bet_coins']['key_value']:0;
        if($post['bet_coins'] > $max_bet_coins)
        {
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['global_error']  = str_replace('#max_bet_coins#',$max_bet_coins,$this->lang->line('prediction_max_coin_error'));
            $this->api_response();
        }

        if($min_bet_coins>0)
        {
            $user_balance = $this->get_user_balance();
            if($user_balance['point_balance'] < $post['bet_coins'])
            {
                $this->api_response_arry['response_code'] = 500;
                $this->api_response_arry['global_error']  =$this->lang->line('prediction_insufficent_coins_err') ;
                $this->api_response();
            }
        }
     

        $this->load->model('Prediction_model');
        $one_prediction = $this->Prediction_model->get_user_predicted($post['prediction_master_id']);
      
       /*  echo '<pre>';
        var_dump($user_predicted);die(); */
        if(!empty($one_prediction['user_prediction_id']))
        {
            //already joined
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['global_error']  =$this->lang->line('prediction_joined_err') ;
            $this->api_response();
        }

        //get prediction details
        if(!empty($one_prediction) && ($one_prediction['status'] == 1 && $one_prediction['status'] == 4))
        {
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['global_error']  =$this->lang->line('prediction_closed_err') ;
            $this->api_response();
        }

        $current_date = format_date();
     
        if(strtotime($one_prediction['deadline_date']) < strtotime($current_date))
        {
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['global_error']  =$this->lang->line('prediction_closed_err') ;
            $this->api_response();
        }

         $save_data = array(
             'user_id' => $this->user_id,
             'bet_coins' => $post['bet_coins'],
             'prediction_option_id' => $post['prediction_option_id'] ,
             'added_date' => $current_date,
             'updated_date' => $current_date
         );

         $insert_id = $this->Prediction_model->make_user_prediction($save_data);
        

         
            //withdraw coins
        $withdraw_arr = array(
            "user_id"   =>$this->user_id,
            "amount"    => $post['bet_coins'],
            "source"    =>40,//make prediction
            "source_id" =>$insert_id,
            "plateform" =>1,//fantasy
            "cash_type" =>3
        );

        $site_rake = 0;

        if(empty($min_bet_coins))
        {
            $min_bet_coins = $post['bet_coins'];
        }

        if($min_bet_coins > 0)
        {
            $this->withdraw_coins($withdraw_arr);
        }
        
        $user_amount_except_site_rake = ((100-$site_rake)*$post['bet_coins'])/100;
        $this->Prediction_model->update_prediction_master($post['prediction_master_id'],$post['bet_coins'],$user_amount_except_site_rake);

         $this->api_response_arry['service_name'] = 'get_fixed_prediction_leaderboard';
         $this->api_response_arry['response_code'] = 200;
         $this->api_response_arry['message']       = $this->lang->line('prediction_joined_success_msg');
        
         $this->api_response();
    }

    public function withdraw_coins($post_input) { 
		
		
        $this->load->model("user/User_model");  
        switch ($post_input["source"]) 
		{
			//make prediction
			case 40://prediction make prediction
				$post_input["status"] = 1;
				break;
        }

       
       $data = $this->User_model->withdraw_coins($post_input);
       $user_cache_key = "user_balance_".$post_input['user_id'];
       $this->delete_cache_data($user_cache_key);
    
       
	}



}