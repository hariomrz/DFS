<?php


class Fixed_open_predictor extends Common_Api_Controller 
{

    var $leaderboard_start_date = null;
    var $leaderboard_end_date = null;

	function __construct()
	{
        parent::__construct();
        $allow_fixed_open_predictor = isset($this->app_config['allow_fixed_open_predictor'])?$this->app_config['allow_fixed_open_predictor']['key_value']:0;
        
        if(!$allow_fixed_open_predictor)
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
        $lobby_fixture_cache = 'l_f_list_fixed_op';

        
        $response =array();
        if(!isset($post_data['is_cron_data'])){
            $response = $this->get_cache_data($lobby_fixture_cache);
        }

        if(empty($response['category_list']))
        {
            $this->load->model("fixed_open_predictor/Fixed_open_predictor_model");
            $response['category_list'] = $this->Fixed_open_predictor_model->get_active_category();
        }

        if(isset($post_data['is_cron_data']) && $post_data['is_cron_data'] == 1)
        {
            $this->push_s3_data_in_queue("lobby_category_list_fixed_open_predictor",$response);
        }
        $this->api_response_arry['data'] = $response;
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
            $this->load->model('Fixed_open_predictor_model');
            $result = $this->Fixed_open_predictor_model->get_prediction_participants($prediction_master_id);

            //get own detail
            $own = array();
            if($this->user_id)
            {
                $own_result= $this->Fixed_open_predictor_model->get_prediction_participants($prediction_master_id,$this->user_id);
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
     * [get_fixed_prediction_categories_post description]
     * @uses :- get prediction leaderboard
     * @param Number prediction master id
     */
    public function get_fixed_prediction_categories_post()
    {
        $this->load->model('Fixed_open_predictor_model');
        $result = $this->Fixed_open_predictor_model->get_fixed_prediction_categories();
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
        
        if(isset($post['filter']) && $post['filter'] == 'yesterday')
		{
            
		    $post['from_date'] = date("Y-m-d",strtotime($current_date.' -1 day')).' 00:00:00';
            $post['to_date'] = date('Y-m-d',strtotime($post['from_date'])).' 23:59:59';
        }

        if(isset($post['filter']) && $post['filter'] == 'this_week')
		{
            $date = date('Y-m-d',strtotime($current_date));
            list($post['from_date'], $post['to_date']) = x_week_range($date);
        }
        
        if(isset($post['filter']) && $post['filter'] == 'last_week')
		{
            //get day number and date
            $date = date('Y-m-d',strtotime($current_date.' -7 days'));
            list($post['from_date'], $post['to_date']) = x_week_range($date);
            
        }

        if(isset($post['filter']) && $post['filter'] == 'this_month')
		{
            $post['from_date'] = date('Y-m-01',strtotime($current_date)).' 00:00:00';
            $post['to_date'] = date('Y-m-t',strtotime($current_date)).' 23:59:59';
        }

        if(isset($post['filter']) && $post['filter'] == 'last_month')
		{
            //get day number and date
            $post['from_date'] = date('Y-m-01',strtotime($current_date.' -1 month')).' 00:00:00';
            $post['to_date'] = date('Y-m-t',strtotime($current_date)).' 23:59:59';
        }
        
        $_POST = $post;
        $category_id = $this->input->post('category_id');
        $this->load->model('Fixed_open_predictor_model');

        $top_three= $this->Fixed_open_predictor_model->get_fixed_leaderboard_top_three($category_id);

        $top_user_ids = array();
        if(!empty($top_three))
        {
            $top_user_ids = array_column($top_three,'user_id');
        }

        $own_leaderboard = array();
        if($this->user_id)
        {
            $own_result= $this->Fixed_open_predictor_model->get_fixed_prediction_leaderboard($category_id,$this->user_id);
            if(!empty($own_result['other_list']) && $own_result['other_list'][0]['user_id'] == $this->user_id)
            {
                $own_leaderboard = $own_result['other_list'][0];
            }
        }

        $result = $this->Fixed_open_predictor_model->get_fixed_prediction_leaderboard($category_id);
        
        if(!empty($result['other_list']))
        {
            if(!empty($own_leaderboard))
            {
                if($own_leaderboard['rank_value'] >20 && $own_leaderboard['user_id'] == $this->user_id )
                {
                    $result['other_list'][] = $own_leaderboard;
                }
            }
            $this->load->model('user/User_model');
            $user_ids = array_unique( array_column($result['other_list'],'user_id'));
            $user_ids = array_unique(array_merge($user_ids,$top_user_ids));
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
        else
        {
            $this->load->model('user/User_model');
            $user_details = $this->User_model->get_users_by_ids($top_user_ids);
            if(!empty($user_details))
            {
                $user_details = array_column($user_details,'user_name','user_id');
            }
        }

        foreach($top_three as $key => & $val)
        {
            $val['username'] = '';
            if(isset($user_details[$val['user_id']]))
            {
                $val['user_name'] =  $user_details[$val['user_id']];
            }
        }


        $result['top_three'] =$top_three;

        $result['sponsors'] = $this->Fixed_open_predictor_model->prediction_sponsors();

        if(isset($post['filter']) && ($post['filter'] == 'this_week' || $post['filter'] == 'last_week'))
		{
            $result['start_date'] = $post['from_date'];
            $result['end_date'] = $post['to_date'];
        }

        $this->api_response_arry['service_name'] = 'get_fixed_prediction_leaderboard';
        $this->api_response_arry['response_code'] = 200;
        $this->api_response_arry['data']          = $result;
        $this->api_response();
      
    }

    function get_open_predictor_leaderboard_post()
    {
        $post = $this->input->post();
        $current_date = format_date();
        
        if(!empty($post['category_id']))
        {
            //call fixed leaderborad function
            $this->get_fixed_prediction_leaderboard_post();
        }

        if(isset($post['filter']) && $post['filter'] == 'today')
		{
            //get day number and date
            $day_number = date("z",strtotime($current_date))+1;
		    $day_date = date("Y-m-d",strtotime($current_date)).' 00:00:00';
            $this->get_day_leaderboard($day_number,$day_date);
        }
        
        if(isset($post['filter']) && $post['filter'] == 'yesterday')
		{
            //get day number and date
            $day_number = date("z",strtotime($current_date));
		    $day_date = date("Y-m-d",strtotime($current_date.' -1 day')).' 00:00:00';
            $this->get_day_leaderboard($day_number,$day_date);
        }
        
        if(isset($post['filter']) && $post['filter'] == 'this_week')
		{
            //get day number and date
            $date = date('Y-m-d',strtotime($current_date));
            list($from_date, $to_date) = x_week_range($date);
            $week_number = date("W",strtotime($from_date));
            $week_start_date = $from_date;
            $week_end_date = $to_date;

            $this->leaderboard_start_date = $week_start_date;
            $this->leaderboard_end_date = $week_end_date;
            
            $this->get_week_leaderboard($week_number,$week_start_date);
        }

        if(isset($post['filter']) && $post['filter'] == 'last_week')
		{
            //get day number and date
            $date = date('Y-m-d',strtotime($current_date.' -7 days'));
            list($from_date, $to_date) = x_week_range($date);
            $week_number = date("W",strtotime($from_date));
            $week_start_date = $from_date;
            $week_end_date = $to_date;

            $this->leaderboard_start_date = $week_start_date;
            $this->leaderboard_end_date = $week_end_date;
            
            $this->get_week_leaderboard($week_number,$week_start_date);
        }

        if(isset($post['filter']) && $post['filter'] == 'this_month')
		{
            //get day number and date
            $from_date = date('Y-m-01',strtotime($current_date)).' 00:00:00';
            $to_date = date('Y-m-t',strtotime($current_date)).' 23:59:59';
            $month_number = date("m",strtotime($from_date));
            $month_start_date = $from_date;
            $month_end_date = $to_date;

            $this->leaderboard_start_date = $month_start_date;
            $this->leaderboard_end_date = $month_end_date;

            
            $this->get_month_leaderboard($month_number,$month_start_date);
        }

        if(isset($post['filter']) && $post['filter'] == 'last_month')
		{
            //get day number and date
            $from_date = date('Y-m-01',strtotime($current_date.' -1 month')).' 00:00:00';
            $to_date = date('Y-m-t',strtotime($current_date)).' 23:59:59';
            $month_number = date("m",strtotime($from_date));
            $month_start_date = $from_date;
            $month_end_date = $to_date;

            $this->leaderboard_start_date = $month_start_date;
            $this->leaderboard_end_date = $month_end_date;

            
            $this->get_month_leaderboard($month_number,$month_start_date);
        }


    }

    function get_day_leaderboard($day_number,$day_date)
    {
        $this->load->model('Fixed_open_predictor_model');

        //get top three
        $top_three= $this->Fixed_open_predictor_model->get_day_top_three($day_number,$day_date);

        $top_user_ids = array();
        if(!empty($top_three))
        {
            $top_user_ids = array_column($top_three,'user_id');
        }

        $own_leaderboard = array();
        if($this->user_id)
        {
            $own_result= $this->Fixed_open_predictor_model->get_day_leaderboard($day_number,$day_date,$this->user_id);
            if(!empty($own_result['other_list']) && $own_result['other_list'][0]['user_id'] == $this->user_id)
            {
                $own_leaderboard = $own_result['other_list'][0];
            }
        }

        $result = $this->Fixed_open_predictor_model->get_day_leaderboard($day_number,$day_date);
        
        // echo '<pre>';
        // print_r($own_leaderboard);
        // die('dfd');

        if(!empty($result['other_list']))
        {
            if(!empty($own_leaderboard))
            {
                if($own_leaderboard['rank_value'] >20 && $own_leaderboard['user_id'] == $this->user_id )
                {
                    $result['other_list'][] = $own_leaderboard;
                }
            }
            $this->load->model('user/User_model');
            $user_ids = array_unique( array_column($result['other_list'],'user_id'));
            $user_ids = array_unique(array_merge($user_ids,$top_user_ids));
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
                    if(!empty($own_leaderboard['prize_data']))
                    {
                        $own_leaderboard['prize_data'] = json_decode($own_leaderboard['prize_data'],TRUE);
                    }
                    unset($result['other_list'][$key]);
                }

                if(!empty($val['prize_data']))
                {
                    $val['prize_data'] = json_decode($val['prize_data'],TRUE);
                }
            }

            if(!empty($own_leaderboard))
            {
                if(!empty($own_leaderboard['prize_data']))
                {
                    $own_leaderboard['prize_data'] = json_decode($own_leaderboard['prize_data'],TRUE);
                }
                $result['own'] =$own_leaderboard;
            }
            $result['other_list'] = array_values($result['other_list']);
            
        }
        else
        {
            $this->load->model('user/User_model');
            $user_details = $this->User_model->get_users_by_ids($top_user_ids);
            if(!empty($user_details))
            {
                $user_details = array_column($user_details,'user_name','user_id');
            }
        }

        foreach($top_three as $key => & $val)
        {
            $val['username'] = '';
            if(isset($user_details[$val['user_id']]))
            {
                $val['user_name'] =  $user_details[$val['user_id']];
            }

            if(!empty($val['prize_data']))
            {
                $val['prize_data'] = json_decode($val['prize_data'],TRUE);
            }
        }


        $result['top_three'] =$top_three;

        $result['sponsors'] = $this->Fixed_open_predictor_model->prediction_sponsors();
 //get prize status
         $status_data =  $this->Fixed_open_predictor_model->get_leaderboard_status(1,$day_date);
         $result['status'] = 0;//live;
         if(isset($status_data['status']) && $status_data['status'] >= 2)
         {
             $result['status'] = 3;//completed
         }
        $this->api_response_arry['service_name'] = 'get_fixed_prediction_leaderboard';
        $this->api_response_arry['response_code'] = 200;
        $this->api_response_arry['data']          = $result;
        $this->api_response();


    }

    function get_week_leaderboard($week_number,$week_date)
    {
        $this->load->model('Fixed_open_predictor_model');

        //get top three
        $top_three= $this->Fixed_open_predictor_model->get_week_top_three($week_number,$week_date);

        $top_user_ids = array();
        if(!empty($top_three))
        {
            $top_user_ids = array_column($top_three,'user_id');
        }

        $own_leaderboard = array();
        if($this->user_id)
        {
            $own_result= $this->Fixed_open_predictor_model->get_week_leaderboard($week_number,$week_date,$this->user_id);
            if(!empty($own_result['other_list']) && $own_result['other_list'][0]['user_id'] == $this->user_id)
            {
                $own_leaderboard = $own_result['other_list'][0];
            }
        }

        $result = $this->Fixed_open_predictor_model->get_week_leaderboard($week_number,$week_date);
        
        // echo '<pre>';
        // print_r($own_leaderboard);
        // die('dfd');

        if(!empty($result['other_list']))
        {
            if(!empty($own_leaderboard))
            {
                if($own_leaderboard['rank_value'] >20 && $own_leaderboard['user_id'] == $this->user_id )
                {
                    $result['other_list'][] = $own_leaderboard;
                }
            }
            $this->load->model('user/User_model');
            $user_ids = array_unique( array_column($result['other_list'],'user_id'));
            $user_ids = array_unique(array_merge($user_ids,$top_user_ids));
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
                    if(!empty($own_leaderboard['prize_data']))
                    {
                        $own_leaderboard['prize_data'] = json_decode($own_leaderboard['prize_data'],TRUE);
                    }
                    unset($result['other_list'][$key]);
                }

                if(!empty($val['prize_data']))
                {
                    $val['prize_data'] = json_decode($val['prize_data'],TRUE);
                }
            }

            if(!empty($own_leaderboard))
            {
                if(!empty($own_leaderboard['prize_data']))
                {
                    $own_leaderboard['prize_data'] = json_decode($own_leaderboard['prize_data'],TRUE);
                }
                $result['own'] =$own_leaderboard;
            }
            $result['other_list'] = array_values($result['other_list']);
            
        }
        else
        {
            $this->load->model('user/User_model');
            $user_details = $this->User_model->get_users_by_ids($top_user_ids);
            if(!empty($user_details))
            {
                $user_details = array_column($user_details,'user_name','user_id');
            }
        }

        foreach($top_three as $key => & $val)
        {
            $val['username'] = '';
            if(isset($user_details[$val['user_id']]))
            {
                $val['user_name'] =  $user_details[$val['user_id']];
            }

            if(!empty($val['prize_data']))
            {
                $val['prize_data'] = json_decode($val['prize_data'],TRUE);
            }
        }


        $result['top_three'] =$top_three;
        $result['sponsors'] = $this->Fixed_open_predictor_model->prediction_sponsors();

        $result['start_date'] = $this->leaderboard_start_date;
        $result['end_date'] = $this->leaderboard_end_date;

         //get prize status
         $status_data =  $this->Fixed_open_predictor_model->get_leaderboard_status(2,$week_date);
         $result['status'] = 0;//live;
         if(isset($status_data['status']) && $status_data['status'] >= 2)
         {
             $result['status'] = 3;//completed
         }
        $this->api_response_arry['service_name'] = 'get_fixed_prediction_leaderboard';
        $this->api_response_arry['response_code'] = 200;
        $this->api_response_arry['data']          = $result;
        $this->api_response();


    }

    function get_month_leaderboard($month_number,$month_date)
    {
        $this->load->model('Fixed_open_predictor_model');

        //get top three
        $top_three= $this->Fixed_open_predictor_model->get_month_top_three($month_number,$month_date);

        $top_user_ids = array();
        if(!empty($top_three))
        {
            $top_user_ids = array_column($top_three,'user_id');
        }

        $own_leaderboard = array();
        if($this->user_id)
        {
            $own_result= $this->Fixed_open_predictor_model->get_month_leaderboard($month_number,$month_date,$this->user_id);
            if(!empty($own_result['other_list']) && $own_result['other_list'][0]['user_id'] == $this->user_id)
            {
                $own_leaderboard = $own_result['other_list'][0];
            }
        }

        $result = $this->Fixed_open_predictor_model->get_month_leaderboard($month_number,$month_date);
        
        // echo '<pre>';
        // print_r($own_leaderboard);
        // die('dfd');

        if(!empty($result['other_list']))
        {
            if(!empty($own_leaderboard))
            {
                if($own_leaderboard['rank_value'] >20 && $own_leaderboard['user_id'] == $this->user_id )
                {
                    $result['other_list'][] = $own_leaderboard;
                }
            }
            $this->load->model('user/User_model');
            $user_ids = array_unique( array_column($result['other_list'],'user_id'));
            $user_ids = array_unique(array_merge($user_ids,$top_user_ids));
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
                    if(!empty($own_leaderboard['prize_data']))
                    {
                        $own_leaderboard['prize_data'] = json_decode($own_leaderboard['prize_data'],TRUE);
                    }
                    unset($result['other_list'][$key]);
                }

                if(!empty($val['prize_data']))
                {
                    $val['prize_data'] = json_decode($val['prize_data'],TRUE);
                }
            }

            if(!empty($own_leaderboard))
            {
                if(!empty($own_leaderboard['prize_data']))
                {
                    $own_leaderboard['prize_data'] = json_decode($own_leaderboard['prize_data'],TRUE);
                }
                $result['own'] =$own_leaderboard;
            }
            $result['other_list'] = array_values($result['other_list']);
            
        }
        else
        {
            $this->load->model('user/User_model');
            $user_details = $this->User_model->get_users_by_ids($top_user_ids);
            if(!empty($user_details))
            {
                $user_details = array_column($user_details,'user_name','user_id');
            }
        }

        foreach($top_three as $key => & $val)
        {
            $val['username'] = '';
            if(isset($user_details[$val['user_id']]))
            {
                $val['user_name'] =  $user_details[$val['user_id']];
            }

            if(!empty($val['prize_data']))
            {
                $val['prize_data'] = json_decode($val['prize_data'],TRUE);
            }
        }


        $result['top_three'] =$top_three;

        $result['sponsors'] = $this->Fixed_open_predictor_model->prediction_sponsors();

        $result['start_date'] = $this->leaderboard_start_date;
        $result['end_date'] = $this->leaderboard_end_date;
        
        //get prize status
        $status_data =  $this->Fixed_open_predictor_model->get_leaderboard_status(3,$month_date);
        $result['status'] = 0;//live;
        if(isset($status_data['status']) && $status_data['status'] >= 2)
        {
            $result['status'] = 3;//completed
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
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        $this->load->model('Fixed_open_predictor_model');
        $user_predicted = $this->Fixed_open_predictor_model->get_user_predicted($post['prediction_master_id']);

        if(!empty($user_predicted))
        {
            //already joined
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['global_error']  =$this->lang->line('prediction_joined_err') ;
            $this->api_response();
        }

        //get prediction details
        $this->db= $this->Fixed_open_predictor_model->db_prediction;
        $one_prediction = $this->Fixed_open_predictor_model->get_single_row('*',PREDICTION_MASTER,array('prediction_master_id' => $post['prediction_master_id']));

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
             'prediction_option_id' => $post['prediction_option_id'] ,
             'added_date' => $current_date,
             'updated_date' => $current_date
         );

         $this->Fixed_open_predictor_model->make_user_prediction($save_data);
         $this->Fixed_open_predictor_model->update_prediction_master($post['prediction_master_id']);

         $this->api_response_arry['service_name'] = 'get_fixed_prediction_leaderboard';
         $this->api_response_arry['response_code'] = 200;
         $this->api_response_arry['message']       = $this->lang->line('prediction_joined_success_msg');
         $this->api_response();
    }
    


}