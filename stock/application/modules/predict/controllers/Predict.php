<?php

class Predict extends Common_Api_Controller 
{
    public $stock_type = 3;
    public $deadline_time = 10;//time in minute
    public $call_from_api =true;
	function __construct()
	{
        parent::__construct();
        $this->lineup_lang = $this->lang->line('lineup');
	}

    /**
     * Used for get lobby filters
     * @param int $sports_id
     * @return array
     */
    public function get_lobby_filter_post() {
        $post_data = $this->input->post();
        $filter_list = array();
        $filter_list['time'] =  array("9:15"=>"11:00","11:01"=>"12:00","12:01"=>"13:00","13:01"=>"14:00","14:01"=>"15:00","15:01"=>"15:30");
        $filter_list['entry_fee'] = array("1"=>"50","51"=>"100","101"=>"1000","1000"=>"1000000");
        $filter_list['entries'] = array("1"=>"50","51"=>"100","101"=>"1000","1000"=>"1000000");
        $filter_list['winning'] = array("1"=>"5000","5000"=>"10000","10000"=>"100000","100000"=>"10000000");
        
        $this->api_response_arry['data'] = $filter_list;
        $this->api_response();
    }

    /**
     * Used for get lobby page contest list
     * @param int 
     * @return array
     */
    public function get_contest_list_post() 
    {
        $post_data = $this->input->post();
        $this->load->model("predict/Predict_model");
        $user_id = intval($this->user_id);
        $result = $this->Predict_model->get_contests_list($post_data);
        $total_contest = 0;
        $contest_list = array();
        $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;

        foreach ($result as $key => $value) 
        {   
            $value['game_starts_in'] = (strtotime($value['scheduled_date']) - ($deadline_time * 60))*1000;
            $value['user_joined_count'] = $value['lm_count'];
            $value["prize_distibution_detail"] = json_decode($value['prize_distibution_detail'],true);
            $value['is_confirmed'] = 0;
            if($value['guaranteed_prize'] != 2 && $value['size'] > $value['minimum_size'] && $value['entry_fee'] > 0){
                $value['is_confirmed'] = 1;
            }
            unset($value['lm_count']);
            $contest_list[] = $value;
            $total_contest++;
        }

        $this->api_response_arry['data']['contest'] = $contest_list;
        $this->api_response_arry['data']['total_contest'] = $total_contest;
        $this->api_response();
    }


    /**
     * Used for get lineup master data
     * @param int $collection_master_id
     * @return array
    */
    public function get_lineup_master_data_post()
    {
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $collection_id = $post_data['collection_id'];
        $this->load->model("predict/Predict_model");
        $collection_data = $this->Predict_model->get_single_row("*", COLLECTION,array("collection_id" => $collection_id));
        $this->check_match_status($collection_data);
      
        $stock_type_data = $this->Predict_model->get_single_row('stock_limit,config_data',STOCK_TYPE,array("status" => 1,"type" => $this->stock_type,"market_id" => 1));
        //print_r($stock_type_data);die;
        $stock_type_data['min_stock'] = 1;
        $stock_type_data['max_stock'] = 10;
        if(!empty($stock_type_data))
        {
            $stock_type_data['config_data'] = json_decode($stock_type_data['config_data'],TRUE);
            if(!empty($stock_type_data['config_data'])){
                $stock_type_data['min_stock'] = $stock_type_data['config_data']['min'];
                $stock_type_data['max_stock'] = $stock_type_data['config_data']['max'];
            }
            unset($stock_type_data['config_data']);
        }
        $stock_type_data['c_point'] = CAPTAIN_POINT;
        $stock_type_data['vc_point'] = VICE_CAPTAIN_POINT;
        $stock_type_data['type'] = $this->stock_type;

        //team name
        $team_result = $this->Predict_model->get_single_row("COUNT(lineup_master_id) as team_count", LINEUP_MASTER,array("collection_id" => $collection_id,"user_id"=>$this->user_id));
       
        //date("dMy",strtotime($collection_data['scheduled_date']))."-".date("Hi",strtotime($collection_data['scheduled_date']))."-".date("Hi",strtotime($collection_data['end_date']));
        $team_short_name = "P1";
        if(!empty($team_result))
        {
            $team_short_name = "P".($team_result['team_count']+1);
        }

        $collection_data['scheduled_date'] = convert_date_to_time_zone($collection_data['scheduled_date'],'UTC',STOCK_TIME_ZONE);
        $collection_data['end_date'] = convert_date_to_time_zone($collection_data['end_date'],'UTC',STOCK_TIME_ZONE);
        
        $pl_name = get_team_name($collection_data['scheduled_date'],$collection_data['end_date'],$team_short_name);
        
        $stock_type_data['team_name'] = strtoupper($pl_name);

        $this->api_response_arry['data'] = $stock_type_data;
        $this->api_response();
    }

    /**
	 * used for save user team
	 * @param int $league_id
	 * @param int $collection_master_id
	 * @param array $lineup
	 * @return
	 */
	public function lineup_proccess_post()
	{	
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        $this->form_validation->set_rules('stocks', $this->lang->line('stocks'), 'callback_validate_stocks');
        $this->form_validation->set_rules('team_name', $this->lang->line('team_name'), 'trim|max_length[20]');
        //$this->form_validation->set_rules('c_id', $this->lang->line('captain'), 'trim|max_length[20]');
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $collection_id = $post_data['collection_id'];
		$stock_ids = $post_data['stocks'];
      
        if($this->user_name == ""){
        	$this->api_response_arry['message'] = $this->lineup_lang['username_empty_error'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

		
        $this->load->model("Predict_model");
		$collection_data = $this->Predict_model->get_single_row("scheduled_date,category_id,published_date,end_date", COLLECTION,array("collection_id" => $collection_id));
        if(empty($collection_data)){
    		$this->api_response_arry['message'] = $this->lineup_lang['match_detail_not_found'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
    	}
        //die(';dfd');
		$this->check_match_status($collection_data);

		//collection players set in cache
	    $collection_stock_cache_key = "sp_collection_stocks_".$collection_id;
		$stock_list = $this->get_cache_data($collection_stock_cache_key);
        if(!$stock_list)
        {
			$post_data['published_date'] = $collection_data['published_date'];
			$post_data['end_date'] = $collection_data['end_date'];
			$post_data['scheduled_date'] = $collection_data['scheduled_date'];

			$stock_list = $this->Predict_model->get_all_stocks($post_data);
        	//set collection team in cache for 2 hours
        	$this->set_cache_data($collection_stock_cache_key,$stock_list,REDIS_2_HOUR);
        }

		$stocks_price = array_column($stock_list,'current_price', "stock_id");
			
        // echo "<pre>";print_r($stocks_price);die;
		$this->check_lineup_validate();
	
     
		
		$this->stock_type = 2;
		if(isset($post_data['lineup_master_id']) && !empty($post_data['lineup_master_id']))
        {
            $lineup_master_id = $post_data['lineup_master_id'];
            $lineup_exist = $this->Predict_model->get_team_by_lineup_matser_id($lineup_master_id,$collection_id);
            if(empty($lineup_exist))
            {
                $this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']			= $this->lineup_lang['lineup_not_exist'];
                $this->api_response();	
            }

            $update_lineup_master_data = array();
            // if(!empty($post_data['team_name']))
            // {
            // 	$update_lineup_master_data['modified_date'] = $current_date;
            //     $update_lineup_master_data['team_name'] = $post_data['team_name'];
            // }

            // //check duplicate team name
            // $check_team = $this->Predict_model->get_single_row("lineup_master_id",LINEUP_MASTER,array("collection_id" => $collection_id,"user_id" => $this->user_id,"LOWER(team_name)"=>strtolower($update_lineup_master_data['team_name']),"lineup_master_id != " => $lineup_master_id));
            // if(!empty($check_team)){
            // 	$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            //     $this->api_response_arry['message']			= $this->lineup_lang['team_name_already_exist'];
            //     $this->api_response();
            // }

            //this function used for validate user team already exist or not with same lineup
            $this->validate_duplicate_user_team($post_data['stocks'],$collection_id,$lineup_master_id);
            
           $this->update_lineup($lineup_master_id,$update_lineup_master_data,$collection_id);
        }

        //add validation for team limit
        $check_team = $this->Predict_model->get_single_row("count(lineup_master_id) as total",LINEUP_MASTER,array("collection_id" => $collection_id,"user_id" => $this->user_id));
        if(!empty($check_team) && $check_team['total'] >= ALLOWED_USER_TEAM){
        	$team_msg = str_replace('%s',ALLOWED_USER_TEAM,$this->lineup_lang["allow_team_limit_error"]);
        	$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']			= $team_msg;
            $this->api_response();
        }
        //prepare lineup master data
        $save_data = array();
        $save_data['collection_id'] = $collection_id;
        $save_data['user_id'] = $this->user_id;
        $save_data['user_name'] = @$this->user_name;
		$save_data['added_date'] = $current_date;
		
		$team_short_name = '';
		$team_result = $this->Predict_model->get_all_old_manual_teams($collection_id);
		if(!empty($team_result))
		{
			$team_short_name = "P".($team_result['team_count']+1);
		}

		$save_data['team_short_name'] = $team_short_name;

       

        if(!empty($post_data['team_name']))
        {
            $save_data['team_name'] = $post_data['team_name'];				
        }
        else{
            $collection_data['scheduled_date'] = convert_date_to_time_zone($collection_data['scheduled_date'],'UTC',STOCK_TIME_ZONE);
            $collection_data['end_date'] = convert_date_to_time_zone($collection_data['end_date'],'UTC',STOCK_TIME_ZONE);
            $save_data['team_name'] = get_team_name($collection_data['scheduled_date'],$collection_data['end_date'],$team_short_name);
        }
        //check duplicate team name
        $check_team = $this->Predict_model->get_single_row("lineup_master_id",LINEUP_MASTER,array("collection_id" => $collection_id,"user_id" => $this->user_id,"LOWER(team_name)" => strtolower($save_data['team_name'])));
        if(!empty($check_team)){
        	$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']			= $this->lineup_lang['team_name_already_exist'];
            $this->api_response();
        }
	

        //this function used for validate user team already exist or not with same lineup
        $this->validate_duplicate_user_team($post_data['stocks'],$collection_id);


        
        //echo "<pre>";print_r($save_data);print_r($lineup);die;
        $this->create_lineup($save_data);
	}

    function get_team_name($scheduled_date,$end_date,$team_short_name)
    {
        
    }

    public function create_lineup($save_data)
	{	
		$stocks = $this->input->post("stocks");
	
		$team_data = array('stocks' => $stocks);
		$save_data['team_data'] = json_encode($team_data);
		$save_data['modified_date']= format_date();
		$lineup_master_id = $this->Predict_model->save_new_lineup($save_data);
		
		$user_teams_cache_key = "sp_user_teams_".$save_data['collection_id']."_".$this->user_unique_id;
		$this->delete_cache_data($user_teams_cache_key);
		
        if($this->call_from_api)
		{
			$this->api_response_arry['data']   = array('lineup_master_id'=>$lineup_master_id);
			
			$this->api_response_arry['message']       = $this->lineup_lang['lineup_success'];
			$this->api_response();	
		}
		else
		{
			return true;
		}
	}

    public function update_lineup($lineup_master_id,$update_lineup_master_data,$collection_id)
	{
		
		$stocks = $this->input->post("stocks");
		$team_data = array("stocks" => $stocks);

    	$update_lineup_master_data['team_data'] = json_encode($team_data);
    	//update lineup master
        if(!empty($update_lineup_master_data)){
        	$this->Predict_model->update(LINEUP_MASTER,$update_lineup_master_data,array('lineup_master_id'=>$lineup_master_id));
        }

        $user_teams_cache_key = "sp_user_teams_".$collection_id."_".$this->user_unique_id;
		$this->delete_cache_data($user_teams_cache_key);
        $this->api_response_arry['message']			= $this->lineup_lang['lineup_update_success'];
        $this->api_response_arry['data']			= array();
        $this->api_response();
	}

/**
	 * validate duplicate team data
	 * @param array $lineup
	 * @param int $collection_master_id
	 * @param int $lineup_master_id
	 * @return
	*/
	public function validate_duplicate_user_team($stocks,$collection_id,$lineup_master_id=""){
		if(ALLOW_DUPLICATE_TEAM == 1){
			return true;
		}
		
		$post_data = $this->input->post();
		$team_players = $this->get_render_list($stocks);
		$result_data = $this->Predict_model->get_all_table_data("lineup_master_id,team_data",LINEUP_MASTER,array("collection_id"=>$collection_id,"user_id"=>$this->user_id));
		if(!empty($result_data)){
			foreach($result_data as $team){
				if(isset($lineup_master_id) && $lineup_master_id == $team['lineup_master_id']){
					continue;
				}
                // echo '<pre>';
                // print_r($team);
                // die('dfd');    
				$team_data = json_decode($team['team_data'],TRUE);
				$tmp_players = $this->get_render_list($team_data['stocks']);

				// $st = array_merge($team_data['b'],$team_data['s']);
				// $tmp_players = isset($st) ? $st : array();
		
				sort($team_players);
				sort($tmp_players);
				if($team_players == $tmp_players){
					$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	                $this->api_response_arry['message']			= $this->lineup_lang["already_created_same_team"];
	                $this->api_response();
				}
			}
		}
		return true;
	}

    private function get_render_list($stocks)
    {
       
        $stock_arr = array();
        foreach($stocks as $key => $stock)
        {
            $stock_arr[] = $key.'_'.$stock;
        }
       
        return $stock_arr;
    }

   /**
	 * validate lineup data
	 * @param int $league_id
	 * @param array $lineup
	 * @param array $position_array
	 * @return
	*/
	private function check_lineup_validate()
	{
		$post_data = $this->input->post();
		$post_stocks = $post_data['stocks'];
		$msg ="";
        
		$st_ids = array_keys($post_stocks);
       
		$stock_type = $this->Predict_model->get_stock_type_config(3);
		$stock_type['config_data'] = json_decode($stock_type['config_data'],TRUE);
        $stock_count = count($st_ids);

       
		if($stock_count < $stock_type['config_data']['min'] || $stock_count > $stock_type['config_data']['max']  )
		{
          
			$msg = str_replace('%s', $stock_type['config_data']['min'] , $this->lineup_lang["lineup_max_limit"]);
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $msg;
			$this->api_response();
		}

		if($msg!='')
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error']			= $msg;
			$this->api_response();
		}

	}

    /**
     * used for get user joined contest list
     * @param int $status
     * @param int $collection_master_id
     * @param int $sports_id
     * @return array
     */
    public function get_user_contest_by_status_post() {
        $this->form_validation->set_rules('status', $this->lang->line('match_status'), 'trim|required|callback_check_collection_status');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $status = $post_data['status'];
        //get 
        $this->load->model('Predict_model');
        $contest_list = $this->Predict_model->get_user_fixture_contest($status);
        
        $users = array();
        if(!empty($contest_list)) {
            $user_ids = array_unique(array_column($contest_list,'contest_creater'));
            $this->load->model("user/User_model");
            $users = $this->User_model->get_users_by_ids($user_ids);
            if(!empty($users)) {
                $users = array_column($users,NULL,'user_id');
            }
        }

        $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
        $fixture_contest = array();
        foreach ($contest_list as $contest) {
            if (!array_key_exists($contest['contest_id'], $fixture_contest)) {
                $contest['is_confirmed'] = 0;
                if($contest['guaranteed_prize'] != 2 && $contest['size'] > $contest['minimum_size'] && $contest['entry_fee'] > 0){
                    $contest['is_confirmed'] = 1;
                }
                $fixture_contest[$contest['contest_id']] = array(
                    "contest_id" => $contest["contest_id"],
                    "contest_unique_id" => $contest["contest_unique_id"],
                    "contest_name" => $contest["contest_name"],
                    "contest_creater" => $contest["contest_creater"],
                    "contest_title" => $contest["contest_title"],         
                    "size" => $contest["size"],
                    "minimum_size" => $contest["minimum_size"],
                    "total_user_joined" => $contest["total_user_joined"],
                    "entry_fee" => $contest["entry_fee"],
                    "prize_pool" => $contest["prize_pool"],
                    "prize_type" => $contest["prize_type"],
                    "prize_distibution_detail" => json_decode($contest["prize_distibution_detail"]),
                    "status" => $contest["status"],
                    "multiple_lineup" => $contest["multiple_lineup"],
                    "user_joined_count" => $contest["user_joined_count"],
                    "max_bonus_allowed" => $contest["max_bonus_allowed"],
                    "is_private_contest" => $contest["is_private_contest"],
                    "group_name" => $contest["group_name"],
                    "contest_access_type" => $contest["contest_access_type"],
                    "currency_type" => $contest["currency_type"],
                    "guaranteed_prize" => $contest["guaranteed_prize"],
                    "is_confirmed" => $contest["is_confirmed"],
                    "scheduled_date" => $contest["scheduled_date"],
                    "collection_id" => $contest["collection_id"],
                    "end_date" => $contest["end_date"],
                    'game_starts_in'=>  (strtotime($contest['scheduled_date']) - ($deadline_time * 60)) * 1000
                );

                if(isset($users[$contest['contest_creater']])) {
                    $fixture_contest[$contest['contest_id']]['user_name'] =$users[$contest['contest_creater']]['user_name'];
                    $fixture_contest[$contest['contest_id']]['image'] =$users[$contest['contest_creater']]['image'];
                }
            }

            $is_winner = $contest["is_winner"];
            if ($status == 1 && !empty($contest["prize_distibution_detail"])) {//LIVE
                $prize_details = json_decode($contest["prize_distibution_detail"], TRUE);
                $last_element = end($prize_details);
                if (!empty($last_element['max']) && $last_element['max'] >= $contest["game_rank"]) {
                    $is_winner = 1;
                }
            }

            $prize_data = array();
            if(isset($contest["prize_data"]) && $contest["prize_data"]!='null'){
                $prize_data = json_decode($contest["prize_data"], TRUE);
            }
            $fixture_contest[$contest['contest_id']]["teams"][$contest['lineup_master_contest_id']] = array(
                "lineup_master_id" => $contest['lineup_master_id'],
                "team_name" => $contest["team_name"],
                "lineup_master_contest_id" => $contest["lineup_master_contest_id"],
                "total_score" => $contest["total_score"] ? $contest["total_score"] : 0,
                "last_score" => $contest["last_score"] ? $contest["last_score"] : 0,
                "game_rank" => $contest["game_rank"] ? $contest["game_rank"] : 0,
                "is_winner" => $is_winner,
                "prize_data" => $prize_data,
                "percent_change" => $contest["percent_change"] ? $contest["percent_change"] : 0,
                "last_percent_change" => $contest["last_percent_change"] ? $contest["last_percent_change"] : 0,
            );
        }

        if (!empty($fixture_contest)) {
            //array_multisort($fixture_contest, SORT_ASC);
            $fixture_contest = array_values($fixture_contest);
        }

        foreach($fixture_contest as &$contest) {
            ksort($contest['teams']);
            $contest['teams']  = array_values($contest['teams']);
        }
        $this->api_response_arry['data'] = $fixture_contest;
        $this->api_response();
    }

    /**
	 * used for get user team player list
	 * @param int $lineup_master_id
	 * @param int $collection_id
	 * @return array
	*/
	public function get_user_lineup_post()
	{
		$this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');
		$this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
		$lineup_master_id = $post_data['lineup_master_id'];
		$collection_id = $post_data['collection_id'];

		$this->load->model("predict/Predict_model");
		$lineup_result = $this->Predict_model->get_single_row("*",LINEUP_MASTER,array("collection_id"=>$collection_id,"lineup_master_id"=>$lineup_master_id,"user_id"=>$this->user_id));
		
		$collection_player_cache_key = "sp_collection_stocks_".$collection_id;
		$stock_list = $this->get_cache_data($collection_player_cache_key);
        if(!$stock_list)
        {
        	$collection_data = $this->Predict_model->get_single_row("category_id,published_date,end_date,scheduled_date", COLLECTION,array("collection_id" => $collection_id));
        	$post_data['published_date'] = isset($collection_data['published_date']) ? $collection_data['published_date'] : "";
        	$post_data['end_date'] = isset($collection_data['end_date']) ? $collection_data['end_date'] : "";
			$post_data['scheduled_date'] = $collection_data['scheduled_date'];
			$stock_list = $this->Predict_model->get_all_stocks($post_data);
        	//set collection team in cache for 2 hours
        	$this->set_cache_data($collection_player_cache_key,$stock_list,REDIS_1_MINUTE);
        }
        $stock_list_array = array_column($stock_list,NULL,'stock_id');
        $final_stock_list = array();
        $team_data = json_decode($lineup_result['team_data'],TRUE);
		$stock_info = array();

		$this->load->model("wishlist/Wishlist_model");
        $wishlist_stock_ids  = $this->Wishlist_model->fetch_wishlist_stock_ids($this->user_id, TRUE);

        foreach($team_data['stocks'] as $stock_id => $user_price){
			$stock_info = $stock_list_array[$stock_id];
			if(!empty($stock_info)){
			
			
				$stock_info['user_price'] = $user_price;//buy
				if(in_array($stock_id, $wishlist_stock_ids)) {
					$stock_info['is_wish'] = 1;
				}
				$final_stock_list[] = $stock_info;
			}
		}
	
        $this->api_response_arry['data']['lineup']		 = $final_stock_list;
		$this->api_response_arry['data']['team_name']	 = $lineup_result['team_name'];
		$this->api_response();
	}

    public function get_collection_statics_post() {
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $this->load->model("predict/Predict_model");
        $data = $this->input->post();
        $collection_id = $data['collection_id'];
        $collection_data = $this->Predict_model->get_single_row('published_date, scheduled_date, end_date', COLLECTION, array('collection_id' => $collection_id));
        if(empty($collection_data)){
            $this->lineup_lang = $this->lang->line('lineup');
    		$this->api_response_arry['message'] = $this->lineup_lang['match_detail_not_found'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
    	}

        $type = isset($post_data['type']) ? $post_data['type'] : 0;
        $data = array_merge($data, $collection_data);
        $data['user_id'] = $this->user_id;
        $statics = array();
        if(empty($type)) {
            $data['type'] = 1;
            $statics['gainers'] = $this->Predict_model->statics($data);

            $data['type'] = 2;
            $statics['losers'] = $this->Predict_model->statics($data);
        } else {
            $data['page'] = 1;
            $statics = $this->Predict_model->statics($data);
        }
        
        $this->api_response_arry['data'] = $statics;
        $this->api_response();
    }  

    /**
     * Used for get logged in user created team list
     * @param int $collection_id
     * @return array
     */
    public function get_user_lineup_list_post()
    {
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $collection_id = $post_data['collection_id'];
        $user_unique_id = $this->user_unique_id;

        $user_teams_cache_key = "sp_user_teams_".$collection_id."_".$user_unique_id;
        $user_lineup_list = $this->get_cache_data($user_teams_cache_key);

        if(!$user_lineup_list){
            $user_lineup_list = array();
            $this->load->model("Predict_model");

            $collection  = $this->Predict_model->get_single_row('published_date,end_date,scheduled_date',COLLECTION,array(
                "collection_id" => $collection_id
            ));
            
            $lineup_data = $this->Predict_model->get_all_user_lineup_list($collection_id);
            
            if(!empty($lineup_data))
            {
                
                 //newtwork allow
                $team_count_array = array();
              
                foreach ($lineup_data as $key => $value)
                {
                    if(!empty($team_count_array) && array_key_exists($value['lineup_master_id'],$team_count_array))
                    {
                        $value['total_joined'] = $value['total_joined'] + $team_count_array[$value['lineup_master_id']];
                    }    

                    $user_lineup_list[] = $value;
                }

            }
            $this->set_cache_data($user_teams_cache_key,$user_lineup_list,REDIS_2_HOUR);
        }

        $this->api_response_arry['data'] = $user_lineup_list;
        $this->api_response();
    }

    public function get_compare_teams_post() {
        $this->form_validation->set_rules('u_lineup_master_contest_id', $this->lang->line('lineup_master_contest_id'), 'trim|required');
        $this->form_validation->set_rules('o_lineup_master_contest_id', $this->lang->line('lineup_master_contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $current_date = format_date();
        $post_data = $this->input->post();
        $lineup_master_contest_ids = array(
            "you"=>$post_data["u_lineup_master_contest_id"],
            "oponent"=>$post_data["o_lineup_master_contest_id"]
        );
        $final_data=array();
        $follow_data=[];
        foreach($lineup_master_contest_ids as $key=>$lmcid){
            $team_data = $this->_common_team_detail($lmcid);

            $allow_social = isset($this->app_config['allow_social']['key_value']) ? $this->app_config['allow_social']['key_value'] : 0;
            if($key == 'oponent' && !empty($allow_social)) {
                $this->load->model('Predict_model');
                if(!empty($this->user_id_arr))
                {    $this->load->model('Predict_model');
                    $follow_data['UserId']    =   $this->user_id_arr[0];
                    $this->load->model("user/User_model");
                    $users = $this->User_model->get_user_detail_by_user_id($this->user_id_arr[1]);
                    $follow_data['UserGUID']  =  $users['user_unique_id'];
                    $follow_data['is_follow'] =  "1"; //To get user following

                     $follow_info =$this->Predict_model->notify_to_social_server('api/follow/index',$follow_data);    
                     if(!empty($follow_info))
                     {
                        $team_data['team_info']['follow'] = $follow_info['follow'];
                     }
                      
                }
            }
            $final_data[$key]=$team_data;
        }

		if(!empty($this->user_id_arr))
		{
			$this->load->model('user/User_model');
            $user_details = $this->User_model->get_users_by_ids($this->user_id_arr);
			$user_images = array_column($user_details,'image','user_id');

			foreach($final_data as &$row)
			{
				$row['team_info']['image'] = $user_images[$row['team_info']['user_id']];
			}
		}
        $this->api_response_arry['data'] = $final_data;
        $this->api_response();
    }

	private function _common_team_detail($lmcid){
        $lineup_master_contest_id = $lmcid;
        $this->load->model("contest/Contest_model");
        $team_info = $this->Contest_model->get_contest_collection_details_by_lmc_id($lineup_master_contest_id);
        if (empty($team_info)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['contest_not_found'];
            $this->api_response();
        }

        $team_data = json_decode($team_info['team_data'],TRUE);
        $collection_player_cache_key = "sp_collection_stocks_" . $team_info['collection_id'];
        $stocks_list = $this->get_cache_data($collection_player_cache_key);
        if (!$stocks_list) {
            $this->load->model("lineup/Lineup_model");
            $post_data['collection_id'] = $team_info['collection_id'];
            $post_data['published_date'] = $team_info['published_date'];
            $post_data['end_date'] = $team_info['end_date'];
            $post_data['scheduled_date'] = $team_info['scheduled_date'];
            $post_data['category_id'] = isset($team_info['category_id']) ? $team_info['category_id'] : "";
            $stocks_list = $this->Lineup_model->get_all_stocks($post_data);
            //set collection players in cache for 2 days
            $this->set_cache_data($collection_player_cache_key, $stocks_list, REDIS_1_MINUTE);
        }
        $stock_list_array = array_column($stocks_list, NULL, 'stock_id');
        $final_player_list = array();
        if($team_info['is_lineup_processed'] == "1"){
            $this->load->model("Predict_model");
            $lineup_details = $this->Predict_model->get_lineup_with_score($lineup_master_contest_id, $team_info);
            $team_data['sl'] = array_column($lineup_details,NULL,"stock_id");
        }else if(in_array($team_info['is_lineup_processed'],array("2","3"))){
            $completed_team = $this->Contest_model->get_single_row("collection_id,lineup_master_id,team_data",COMPLETED_TEAM, array("collection_id" => $team_info['collection_id'], "lineup_master_id" => $team_info['lineup_master_id']));
            $team_data = json_decode($completed_team['team_data'],TRUE);
            $team_data['sl'] = $this->get_rendered_stock_complted_fixture($team_data);
			$team_data['sl'] = array_column($team_data['sl'],NULL,"stock_id");
        }

		$lineup_total = $team_info['remaining_cap'];
        if(!empty($team_data['sl'])){
            foreach ($team_data['sl'] as $stock_id=>$score_data) {
                $stock_info = $stock_list_array[$stock_id];
                if(!empty($stock_info)) {
					$lineup = array();

                    $openprice =$this->Contest_model->get_single_row('open_price',COLLECTION_STOCK,array('collection_id'=>$team_info['collection_id'],'stock_id'=>$stock_id));
                   
					/*$lineup['price_sum'] = $stock_info['current_price']*$score_data['user_lot_size'];*/
                    $lineup['stock_id'] = $stock_info['stock_id'];
                    $lineup['stock_name'] = $stock_info['stock_name'];
                    $lineup['logo'] = $stock_info['logo'];
                    $lineup['accuracy_percent'] = $score_data['accuracy_percent'];
                    $lineup['user_price'] = $score_data['user_price'];
                    $lineup['close_price'] = $score_data['close_price'];
                    $lineup['open_price'] = $openprice['open_price'];
                    /*$lineup_total+=$lineup['price_sum'];*/
                    $final_stock_list[] = $lineup;
                }
            }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['team_detail_not_found'];
            $this->api_response();
        }
        

        $result_data = array();
        $result_data["team_info"] = array(
            "total_score" => $team_info["total_score"],
            "team_name" => $team_info["team_name"],
            "lineup_master_id" => $team_info["lineup_master_id"],
			"lineup_total" => $lineup_total,
			"game_rank" => $team_info['game_rank'],
			"user_name" => $team_info['user_name'],
			"score_updated_date" => $team_info['score_updated_date'],
			"user_id" => $team_info['user_id'],
			"team_short_name" => $team_info['team_short_name'],
			"remaining_cap" => $team_info['remaining_cap']
        );

		$this->user_id_arr[]= $team_info['user_id'];
        $result_data["lineup"] = $final_stock_list;
        return $result_data;
    }

	private function get_rendered_stock_array($arr,$type)
	{
		$tmp = array();
		foreach($arr as $stock_id => $user_lot_size)	{
			$tmp[] = array('stock_id' => $stock_id,'user_lot_size' => $user_lot_size,'type' => $type );
	  }
	  return $tmp;
	}

    private function get_rendered_stock_complted_fixture($arr)
	{
		$tmp = array();
		foreach($arr as $stock_id => $score_data)	{
			$tmp[] = array('stock_id' => $stock_id,
                           'user_price' => !empty($score_data[0]['user_price']) ? $score_data[0]['user_price'] :  $score_data['user_price'],
                           'accuracy_percent' =>  !empty($score_data[0]['accuracy_percent']) ? $score_data[0]['accuracy_percent'] : $score_data['accuracy_percent'],
                           'close_price'      =>  !empty($score_data[0]['close_price']) ? $score_data[0]['close_price'] : $score_data['close_price']
                        );
	  }
	  return $tmp;
	}

    function get_my_lobby_contest_post() {

        $post_data = $this->input->post();
        $this->load->model('Predict_model');
        $result = $this->Predict_model->get_my_joined_contests($post_data);
        //echo "<pre>";print_r($result);die;
        $final_data = array();
        $upcoming =array();
        $live =array();
        $completed =array();
        if (!empty($result)) { 
            foreach ($result as $fixture) {
                $collection_status = $fixture['collection_status'];
                $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
                unset($fixture['collection_status']);
                $fixture['game_starts_in'] = (strtotime($fixture['scheduled_date']) - ($deadline_time * 60)) * 1000;

                $fixture["prize_distibution_detail"] = json_decode($fixture['prize_distibution_detail'],true);
                               
                if($fixture['is_live'] == 1) {
                    $live[] =  $fixture;
                }

                if($fixture['is_upcoming'] == 1) {
                    $upcoming[] =  $fixture;
                }

                if($collection_status == 1) {
                    $completed[] =  $fixture;
                }
            }

//           Live order-> Recent live matches should show first => DESC
            $live_scheduled_date = array_column($live, 'scheduled_date');
            array_multisort($live_scheduled_date, SORT_DESC, $live);

//           Upcoming-> Recent Upcoming Matches should show first => ASC
            $up_scheduled_date = array_column($upcoming, 'scheduled_date');
            array_multisort($up_scheduled_date, SORT_ASC, $upcoming);

//           Completed-> Recent Completed Matches should show first. => DESC
            $c_scheduled_date = array_column($completed, 'scheduled_date');
            array_multisort($c_scheduled_date, SORT_DESC, $completed);

            // krsort() for descending order
            // ksort() for ascending order
            //krsort($live);
            //ksort($upcoming);
            //krsort($completed);  
        }

        //Live, Upcoming and Last 7 days Completed Fixture
        $final_data = array_merge($live,$upcoming,$completed);
        
        $this->api_response_arry['data'] = $final_data;
        $this->api_response();        
    }

    /**
    * Get Prediction Score Calculation list
    * @param lineup_master_id,collection_id,contest_id
    * @return Json array
    */
    public function get_lineup_score_calculation_post()
    {
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("contest/Contest_model");
        $row = $this->Contest_model->get_team_percent_score($post_data['collection_id'],$post_data['lineup_master_id'],$post_data['contest_id']);

      
        $this->load->model('Predict_model');
        $result = $this->Predict_model->get_lineup_score_calculation_prediction($post_data);
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data']['lineup'] = $result;
        $this->api_response_arry['data']['percent_change'] = $row['percent_change'];

        $this->api_response();
    }

    /**
    * Follow or Unfollow the opponent user
    * The API will call social API to update data on social db
    * @param INT from_user to_user
    * @return INT status flag
    */    
    function update_follow_user_post()
    {
        $this->form_validation->set_rules('from_user', $this->lang->line('from_user'), 'trim|required');
        $this->form_validation->set_rules('to_user', $this->lang->line('to_user'), 'trim|required');
      
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
         $post_data = $this->input->post();
         
         $this->load->model("user/User_model");
         $users = $this->User_model->get_user_detail_by_user_id($post_data['to_user']);
         $follow_data['UserId']    =  $post_data['from_user'];
         $follow_data['UserGUID']  =  $users['user_unique_id'];

         $this->load->model('Predict_model');
         $follow_info = $this->Predict_model->notify_to_social_server('api/follow/index',$follow_data);
          
         if(!empty($follow_info))
         { 
            $this->api_response_arry['data']['follow']  = $follow_info['follow'];
            $this->api_response_arry['message'] = $follow_info['Message'];
         }
        $this->api_response();
    }    
}