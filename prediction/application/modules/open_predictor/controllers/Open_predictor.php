<?php


class Open_predictor extends Common_Api_Controller 
{

	function __construct()
	{
        parent::__construct();

        $allow_open_predictor = isset($this->app_config['allow_open_predictor'])?$this->app_config['allow_open_predictor']['key_value']:0;
        
        if(!$allow_open_predictor)
        {
            
            $this->api_response_arry['response_code'] = 500;
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
        $post_data = $this->input->post();
        $sports_id = $post_data["sports_id"];
        $lobby_fixture_cache = 'l_f_list_op';

        
        $response =array();
        if(!isset($post_data['is_cron_data'])){
            $response = $this->get_cache_data($lobby_fixture_cache);
        }

        if(empty($response['category_list']))
        {
            $this->load->model("open_predictor/open_predictor_model");
            $response['category_list'] = $this->open_predictor_model->get_active_category();
        }

        if(isset($post_data['is_cron_data']) && $post_data['is_cron_data'] == 1)
        {
            $this->push_s3_data_in_queue("lobby_category_list_open_predictor",$response);
        }

        $this->api_response_arry['data'] = $response;
        $this->api_response();
    }

    public function get_season_by_season_uid_post()
    {
        $post = $this->input->post();
        $this->load->model("prediction/open_predictor_model");
        $post['for_prediction'] = 1;
		$result_array =  $this->open_predictor_model->get_season_by_season_game_uids($post,0);
		
		foreach ($result_array as $key => $rs) {
			$result_array[$key]["game_starts_in"] = strtotime($result_array[$key]["season_scheduled_date"])*1000;
            $result_array[$key]["today"] = strtotime(format_date())*1000;
            
            if (!empty($result_array[$key]['score_data'])) {
                $result_array[$key]['score_data'] = json_decode($result_array[$key]['score_data'], TRUE);
            }
		}
		
		$this->api_response_arry['service_name'] = 'get_season_by_season_uid';
		$this->api_response_arry['response_code'] = 200;
        $this->api_response_arry['data']          = ($result_array)?$result_array:array();
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
/**
     * [get_prediction_participants description]
     * @uses :- get participants
     * @param Number prediction master id
     */      $prediction_master_id = $this->input->post('prediction_master_id');
            $this->load->model('open_predictor_model');
            $result = $this->open_predictor_model->get_prediction_participants($prediction_master_id);

            //get own detail
            $own = array();
            if($this->user_id)
            {
                $own_result= $this->open_predictor_model->get_prediction_participants($prediction_master_id,$this->user_id);
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

/**
     * [get_prediction_leaderboard_post description]
     * @uses :- get prediction leaderboard
     * @param Number prediction master id
     */
    public function get_prediction_leaderboard_post()
    {
        $this->form_validation->set_rules('prediction_master_id', 'Prediction ID', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $prediction_master_id = $this->input->post('prediction_master_id');
        $this->load->model('open_predictor_model');
        $result = $this->open_predictor_model->get_prediction_leaderboard($prediction_master_id);
        $own_leaderboard = array();
        if($this->user_id)
        {
            $own_result= $this->open_predictor_model->get_prediction_leaderboard($prediction_master_id,$this->user_id);
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
     * [get_fixed_prediction_categories_post description]
     * @uses :- get prediction leaderboard
     * @param Number prediction master id
     */
    public function get_fixed_prediction_categories_post()
    {
        $this->load->model('open_predictor_model');
        $result = $this->open_predictor_model->get_fixed_prediction_categories();
        $this->api_response_arry['service_name'] = 'get_fixed_prediction_categories';
        $this->api_response_arry['response_code'] = 200;
        $this->api_response_arry['data']          = $result;
        $this->api_response();
    }

    /**
     * [get_fixed_prediction_leaderboard description]
     * @uses :- get fixed prediction leaderboard
     * @param Number prediction master id
     */
    public function get_fixed_prediction_leaderboard_post()
    {

        $post = $this->input->post();
        $current_date = format_date();
        if(isset($post['filter']) && $post['filter'] == 'today')
		{
            $post['from_date'] = date('Y-m-d',strtotime($current_date)).' 00:00:00';
            $post['to_date'] = date('Y-m-d',strtotime($current_date)).' 23:59:59';
		}

        if(isset($post['filter']) && $post['filter'] == 'this_week')
		{
            $date = date('Y-m-d',strtotime($current_date));
            list($post['from_date'], $post['to_date']) = x_week_range($date);
		}

        if(isset($post['filter']) && $post['filter'] == 'this_month')
		{
            $post['from_date'] = date('Y-m-01',strtotime($current_date)).' 00:00:00';
            $post['to_date'] = date('Y-m-t',strtotime($current_date)).' 23:59:59';
        }
        
        $_POST = $post;
        $category_id = $this->input->post('category_id');
        $this->load->model('open_predictor_model');
        $own_leaderboard = array();
        if($this->user_id)
        {
            $own_result= $this->open_predictor_model->get_fixed_prediction_leaderboard($category_id,$this->user_id);
            if(!empty($own_result['other_list']) && $own_result['other_list'][0]['user_id'] == $this->user_id)
            {
                $own_leaderboard = $own_result['other_list'][0];
            }
        }

        $result = $this->open_predictor_model->get_fixed_prediction_leaderboard($category_id);
        
        if(!empty($result['other_list']))
        {
            if(!empty($own_leaderboard))
            {
                if($own_leaderboard['user_rank'] >20 && $own_leaderboard['user_id'] == $this->user_id )
                {
                    $result['other_list'][] = $own_leaderboard;
                }
            }
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

        $this->api_response_arry['service_name'] = 'get_fixed_prediction_leaderboard';
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
            $this->api_response_arry['global_error']  =$this->lang->line('prediction_max_coin_error') ;
            $this->api_response();
        }


        //
        $this->load->model('user/User_model');
        $user_balance = $this->User_model->get_user_balance($this->user_id);
        if($user_balance['point_balance'] < $post['bet_coins'])
        {
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['global_error']  =$this->lang->line('prediction_insufficent_coins_err') ;
            $this->api_response();
        }

        $this->load->model('Open_predictor_model');
        $user_predicted = $this->Open_predictor_model->get_user_predicted($post['prediction_master_id']);

        if(!empty($user_predicted))
        {
            //already joined
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['global_error']  =$this->lang->line('prediction_joined_err') ;
            $this->api_response();
        }

        //get prediction details
        $this->db= $this->Open_predictor_model->db_prediction;
        $one_prediction = $this->Open_predictor_model->get_single_row('*',PREDICTION_MASTER,array('prediction_master_id' => $post['prediction_master_id']));

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

         $insert_id = $this->Open_predictor_model->make_user_prediction($save_data);

            //withdraw coins
        $withdraw_arr = array(
            "user_id"   =>$this->user_id,
            "amount"    => $post['bet_coins'],
            "source"    =>220,//make prediction
            "source_id" =>$insert_id,
            "plateform" =>1,//fantasy
            "cash_type" =>3,
            "entry_type"=>$one_prediction['entry_type']
        );
        $site_rake = 0;

        $min_bet_coins =  isset($this->app_config['min_bet_coins'])?$this->app_config['min_bet_coins']['key_value']:0;

        if(empty($min_bet_coins))
        {
            $min_bet_coins = $post['bet_coins'];
        }

        if($min_bet_coins > 0)
        {
            $this->withdraw_coins($withdraw_arr);
        }
        $user_amount_except_site_rake = ((100-$site_rake)*$post['bet_coins'])/100;
        $this->Open_predictor_model->update_prediction_master($post['prediction_master_id'],$post['bet_coins'],$user_amount_except_site_rake);

         $this->api_response_arry['service_name'] = 'get_fixed_prediction_leaderboard';
         $this->api_response_arry['response_code'] = 200;
         $this->api_response_arry['message']       = $this->lang->line('prediction_joined_success_msg');
         $this->api_response();
    }

    public function withdraw_coins($post_input) { 
		// $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
		// $this->form_validation->set_rules('amount', 'Amount', 'trim|required|callback_decimal_numeric|callback_greater_than_zero');
		// $this->form_validation->set_rules('source', 'Source', 'trim|integer|required');
		// $this->form_validation->set_rules('source_id', 'Source Id', 'trim|integer|required');
		// $this->form_validation->set_rules('plateform', 'Plateform', 'trim|integer|required');
		// $this->form_validation->set_rules('cash_type', 'Cash Type', 'trim|integer|required');
		// cash Type 0-real cash, 1-bonus cash, 2 for both(bonus cash and real cash), 3-Coins, 4- Winning 

		// if (!$this->form_validation->run()) {
		// 	$this->send_validation_errors();
		// }
		
        $this->load->model("user/User_model");  
        switch ($post_input["source"]) 
		{
			//make prediction
			case 40:
			case 220://open predictor make prediction
				$post_input["status"] = 1;
				break;
        }

       
       $data = $this->User_model->withdraw_coins($post_input);
       $user_cache_key = "user_balance_".$post_input['user_id'];
       $this->delete_cache_data($user_cache_key);
    
       
	}



}