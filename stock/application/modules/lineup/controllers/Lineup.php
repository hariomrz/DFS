<?php 

class Lineup extends Common_Api_Controller {

	private $lineup_lang = array();
	public $sports_id = CRICKET_SPORTS_ID;
	public $call_from_api = true;
	public $stock_type = 1;

	public function __construct()
	{
		parent::__construct();
		$this->lineup_lang = $this->lang->line('lineup');
		if($this->input->post("sports_id"))
        {
        	$sports_list = array_column($this->global_sports, NULL,'sports_id');
            
        }
	}

    /**
	 * Used for get user auto generated team name
	 * @param int $collection_master_id
	 * @return array
	*/
	public function get_user_match_team_data_post()
	{
		$this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
        $collection_master_id = $post_data['collection_id'];
        
        $team_name = "Portfolio 1";
		$this->load->model("lineup/Lineup_model");
		$team_result = $this->Lineup_model->get_all_old_manual_teams($collection_master_id);
		if(!empty($team_result))
		{
			$team_name = "Portfolio ".($team_result['team_count']+1);
		}
		$team_data = array("team_name"=>$team_name);

		$this->api_response_arry['data'] = $team_data;
		$this->api_response();
	}

    	/**
	 * Used for get collection stock list
	 * @param int $sports_id
	 * @param int $league_id
	 * @param int $collection_master_id
	 * @return array
	*/
	public function get_all_stocks_post()
	{
		$this->form_validation->set_rules('collection_id', $this->lang->line('collection_master_id'), 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
		$collection_id = $post_data['collection_id'];
		$collection_player_cache_key = "st_collection_player_".$collection_id;
		$players_list = $this->get_cache_data($collection_player_cache_key);
        if(!$players_list)
        {
        	$this->load->model("lineup/Lineup_model");

            //get collection
            $collection  = $this->Lineup_model->get_single_row('published_date,end_date,scheduled_date',COLLECTION,array(
                "collection_id" => $collection_id
            ));

            $post_data['published_date'] = $collection['published_date'];
            $post_data['end_date'] = $collection['end_date'];
			$post_data['scheduled_date'] = $collection['scheduled_date'];
			$players_list = $this->Lineup_model->get_all_stocks($post_data);
        	//set collection team in cache for 2 hours
        	$this->set_cache_data($collection_player_cache_key,$players_list,REDIS_1_MINUTE);
        }

		//for upload lineup data on s3 bucket
       // $this->push_s3_data_in_queue("st_collection_player_".$collection_id,$players_list);

		$this->load->model("wishlist/Wishlist_model");
        $wishlist_stock_ids  = $this->Wishlist_model->fetch_wishlist_stock_ids($this->user_id, TRUE);
		foreach($players_list as &$player) {
			if(in_array($player['stock_id'], $wishlist_stock_ids)) {
				$player['is_wish'] = 1;
			}
        }

		$this->api_response_arry['data'] = $players_list;
		$this->api_response();
	}

    /**
	 * Used for get lineup master data
	 * @param int $sports_id
	 * @param int $league_id
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
        $this->load->model("lineup/Lineup_model");
		$collection_data = $this->Lineup_model->get_single_row("scheduled_date", COLLECTION,array("collection_id" => $collection_id));

		$this->check_match_status($collection_data);
      
		$stock_type = 1;
		if(!empty($post_data['stock_type']) && $post_data['stock_type'] =='2')
		{
			$stock_type = $post_data['stock_type'];
		}

        $stock_type_data = $this->Lineup_model->get_stock_type_config($stock_type);//stock fantasy
        
        if(isset($stock_type_data['config_data']))
        {
            $stock_type_data['config_data'] = json_decode($stock_type_data['config_data'],TRUE);
        }

		if($stock_type==2)
		{
			$stock_type_data['salary_cap'] = $this->Lineup_model->get_stock_price_cap('salary_cap');
			$stock_type_data['min_cap_per_stock'] = (int)$this->Lineup_model->get_stock_price_cap('min_cap_per_stock');
			$stock_type_data['max_cap_per_stock'] = (int)$this->Lineup_model->get_stock_price_cap('max_cap_per_stock');
		}
		
		$stock_type_data['c_point'] = CAPTAIN_POINT;
        $stock_type_data['vc_point'] = VICE_CAPTAIN_POINT;
       
		//for upload lineup data on s3 bucket
        $this->push_s3_data_in_queue("st_lineup_master_data_".$collection_id,$stock_type_data);
       
		$this->api_response_arry['data'][] = $stock_type_data;
       
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
        $this->form_validation->set_rules('c_id', $this->lang->line('captain'), 'trim|max_length[20]');
        
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

		
        $this->load->model("lineup/Lineup_model");

		$this->check_lineup_validate();

        $collection_data = $this->Lineup_model->get_single_row("scheduled_date,category_id,published_date,end_date", COLLECTION,array("collection_id" => $collection_id));
        if(empty($collection_data)){
    		$this->api_response_arry['message'] = $this->lineup_lang['match_detail_not_found'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
    	}
    
		$this->check_match_status($collection_data);

		$user_stocks = array_merge($post_data['stocks']['b'],$post_data['stocks']['s']);
		$post_data['user_stocks'] = $user_stocks;

		
    	//collection players set in cache
	    $collection_stock_cache_key = "st_collection_stocks_".$collection_id;
		$stock_list = $this->get_cache_data($collection_stock_cache_key);
        if(!$stock_list)
        {
			$post_data['published_date'] = $collection_data['published_date'];
			$post_data['end_date'] = $collection_data['end_date'];
			$post_data['scheduled_date'] = $collection_data['scheduled_date'];
			$stock_list = $this->Lineup_model->get_all_stocks($post_data);
        	//set collection team in cache for 2 hours
        	$this->set_cache_data($collection_stock_cache_key,$stock_list,REDIS_1_MINUTE);
        }
		
		$lineup = array();
        if(!empty($stock_list)){
			$player_stock_id_list = array_column($stock_list,NULL, "stock_id");
			foreach($user_stocks as $stock_id)
			{
				if(isset($player_stock_id_list[$stock_id]))
				{
					$lineup[] = $player_stock_id_list[$stock_id];
				}
			}
		}
		//echo "<pre>";print_r($stock_list);die;
		
		if(isset($post_data['lineup_master_id']) && !empty($post_data['lineup_master_id']))
        {
            $lineup_master_id = $post_data['lineup_master_id'];
            $lineup_exist = $this->Lineup_model->get_team_by_lineup_matser_id($lineup_master_id,$collection_id);
            if(empty($lineup_exist))
            {
                $this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']			= $this->lineup_lang['lineup_not_exist'];
                $this->api_response();	
            }

            $update_lineup_master_data = array();
            if(!empty($post_data['team_name']))
            {
            	$update_lineup_master_data['modified_date'] = $current_date;
                $update_lineup_master_data['team_name'] = $post_data['team_name'];
            }

            //check duplicate team name
            $check_team = $this->Lineup_model->get_single_row("lineup_master_id",LINEUP_MASTER,array("collection_id" => $collection_id,"user_id" => $this->user_id,"LOWER(team_name)"=>strtolower($update_lineup_master_data['team_name']),"lineup_master_id != " => $lineup_master_id));
            if(!empty($check_team)){
            	$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']			= $this->lineup_lang['team_name_already_exist'];
                $this->api_response();
            }

            //this function used for validate user team already exist or not with same lineup
            $this->validate_duplicate_user_team($post_data['stocks'],$collection_id,$lineup_master_id);
            
           $this->update_lineup($lineup_master_id,$update_lineup_master_data,$lineup,$collection_id);
        }

        //add validation for team limit
        $check_team = $this->Lineup_model->get_single_row("count(lineup_master_id) as total",LINEUP_MASTER,array("collection_id" => $collection_id,"user_id" => $this->user_id));
        if(!empty($check_team) && $check_team['total'] >= ALLOWED_USER_TEAM){
        	$team_msg = str_replace('%s',ALLOWED_USER_TEAM,$this->lineup_lang["allow_team_limit_error"]);
        	$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']			= $team_msg;
            $this->api_response();
        }
        //prepare lineup master data
        $save_data = array();
        if(!empty($post_data['team_name']))
        {
            $save_data['team_name'] = $post_data['team_name'];				
        }
        //check duplicate team name
        $check_team = $this->Lineup_model->get_single_row("lineup_master_id",LINEUP_MASTER,array("collection_id" => $collection_id,"user_id" => $this->user_id,"LOWER(team_name)" => strtolower($save_data['team_name'])));
        if(!empty($check_team)){
        	$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']			= $this->lineup_lang['team_name_already_exist'];
            $this->api_response();
        }
	
        $save_data['collection_id'] = $collection_id;
        $save_data['user_id'] = $this->user_id;
        $save_data['user_name'] = $this->user_name;
		$save_data['added_date'] = $current_date;
		
		$team_short_name = '';
		$team_result = $this->Lineup_model->get_all_old_manual_teams($collection_id);
		if(!empty($team_result))
		{
			$team_short_name = "P".($team_result['team_count']+1);
		}

		$save_data['team_short_name'] = $team_short_name;
        //this function used for validate user team already exist or not with same lineup
        $this->validate_duplicate_user_team($post_data['stocks'],$collection_id);
        //echo "<pre>";print_r($save_data);print_r($lineup);die;
        $this->create_lineup($save_data,$lineup);
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
		if(empty($post_stocks['b']))
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error']			= $this->lineup_lang['err_select_buy_stocks'];
			$this->api_response();
		}

		if(empty($post_stocks['s']))
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error']			= $this->lineup_lang['err_select_sale_stocks'];
			$this->api_response();
		}
		
		$st_ids = array_merge($post_stocks['b'],$post_stocks['s']);
		$stock_type = $this->Lineup_model->get_stock_type_config(1);
		$stock_type['config_data'] = json_decode($stock_type['config_data'],TRUE);
		if(count($st_ids) != $stock_type['config_data']['tc'] )
		{
			$msg = str_replace('%s', $stock_type['config_data']['tc'] , $this->lineup_lang["lineup_max_limit"]);
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $msg;
			$this->api_response();
		}

		$buy_arr = $post_stocks['b'];
		$sell_arr = $post_stocks['s'];

	

		if(count($buy_arr) > $post_stocks["b"])
		{
			$msg = $this->lineup_lang['err_max_buy_stocks_exceeded'];
		}

		if(count($sell_arr) > $post_stocks["s"])
		{
			$msg = $this->lineup_lang['err_max_sell_stocks_exceeded'];
		}

		if($msg!='')
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error']			= $msg;
			$this->api_response();
		}

	}

	/**
	 * used for update user team
	 * @param int $lineup_master_id
	 * @param array $update_lineup_master_data
	 * @param array $lineup
	 * @param array $position_array
	 * @return
	*/
	public function update_lineup($lineup_master_id,$update_lineup_master_data,$lineup,$collection_id)
	{
		$st_id_array = array_column($lineup,'stock_id');
		
		$c_id = $this->input->post("c_id");
		$vc_id = $this->input->post("vc_id");
		$stocks = $this->input->post("stocks");
		$team_stocks = $this->get_merged_stocks($stocks,$this->stock_type);
		$this->check_captain_exist($team_stocks,$c_id);
       
		if(VICE_CAPTAIN_POINT>0)
        {
            $vc_pl_id = $this->check_vice_captain_exist($team_stocks,$vc_id);
        }
		$team_data = array("c_id"=>$c_id,"vc_id"=>$vc_id,"b" => $stocks['b'],"s" => $stocks['s']);

    	$update_lineup_master_data['team_data'] = json_encode($team_data);
    	//update lineup master
        if(!empty($update_lineup_master_data)){
        	$this->Lineup_model->update(LINEUP_MASTER,$update_lineup_master_data,array('lineup_master_id'=>$lineup_master_id));
        }

        $user_teams_cache_key = "st_user_teams_".$collection_id."_".$this->user_unique_id;
		$this->delete_cache_data($user_teams_cache_key);
        $this->api_response_arry['message']			= $this->lineup_lang['lineup_update_success'];
        $this->api_response_arry['data']			= array();
        $this->api_response();
	}


		/**
	 * used for check captain exist or not
	 * @param array $player_rol_array
	 * @return
	 */
	private function check_captain_exist($stock_id_array,$c_stock_id)
	{
        if(CAPTAIN_POINT <= 0){
            return 0;
        }
		$captain_role =  array_search($c_stock_id,$stock_id_array,true);

		if($captain_role === FALSE)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $this->lineup_lang['lineup_captain_error'];
			$this->api_response();
		}

		return $captain_role;
	}

	/**
	 * used for check vice-captain exist or not
	 * @param array $player_rol_array
	 * @return
	 */
	private function check_vice_captain_exist($stock_id_array,$vc_stock_id)
	{
        if(VICE_CAPTAIN_POINT <= 0){
            return 0;
        }
		$vice_captain_role =  array_search($vc_stock_id,$stock_id_array,true);
		if($vice_captain_role === FALSE)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $this->lineup_lang['lineup_vice_captain_error'];
			$this->api_response();
		}
		return $vice_captain_role;
	}

	/**
	 * used for save user team
	 * @param array $save_data
	 * @param array $lineup
	 * @param array $position_array
	 * @return
	*/
	public function create_lineup($save_data,$lineup)
	{	
		$st_id_array = array_column($lineup,'stock_id');
		
		
		$c_id = $this->input->post("c_id");
		$vc_id = $this->input->post("vc_id");
		$stocks = $this->input->post("stocks");
		$team_stocks = $this->get_merged_stocks($stocks,$this->stock_type);
		$this->check_captain_exist($team_stocks,$c_id);
       
		if(VICE_CAPTAIN_POINT>0)
        {
            $vc_pl_id = $this->check_vice_captain_exist($team_stocks,$vc_id);
        }

		$team_data = array("c_id"=>$c_id,"vc_id"=>$vc_id,"b" => $stocks['b'],"s" => $stocks['s']);
		$save_data['team_data'] = json_encode($team_data);
		$save_data['modified_date']= format_date();
		$lineup_master_id = $this->Lineup_model->save_new_lineup($save_data);
		
		$user_teams_cache_key = "st_user_teams_".$save_data['collection_id']."_".$this->user_unique_id;
		$this->delete_cache_data($user_teams_cache_key);

		//delete user joined count
		$user_ct_cache = "user_ct_".$save_data["collection_id"]."_".$this->user_id;
		$this->delete_cache_data($user_ct_cache);
		
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

	// public function  get_rendered_team_players($stocks)
	// {
	// 	$team_players = array();
	// 	foreach($stocks['b'] as $buy)
	// 	{
	// 		$team_players[] = $buy.'_b';
	// 	}

	// 	foreach($stocks['s'] as $sell)
	// 	{
	// 		$team_players[] = $sell.'_s';
	// 	}

	// 	return $team_players;
	// }

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
		$team_players = $this->get_rendered_team_players($stocks);
		$team_players[] = $post_data['c_id']."_1";

		if(VICE_CAPTAIN_POINT>0)
		{
			$team_players[] = $post_data['vc_id']."_2";
		}
		$result_data = $this->Lineup_model->get_all_table_data("lineup_master_id,team_data",LINEUP_MASTER,array("collection_id"=>$collection_id,"user_id"=>$this->user_id));
		if(!empty($result_data)){
			foreach($result_data as $team){
				if(isset($lineup_master_id) && $lineup_master_id == $team['lineup_master_id']){
					continue;
				}

				$team_data = json_decode($team['team_data'],TRUE);
				$tmp_players = $this->get_rendered_team_players($team_data);

				// $st = array_merge($team_data['b'],$team_data['s']);
				// $tmp_players = isset($st) ? $st : array();
				$tmp_players[] = $team_data['c_id']."_1";
				if(VICE_CAPTAIN_POINT>0)
				{
					$tmp_players[] = $team_data['vc_id']."_2";
				}

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

		$this->load->model("lineup/Lineup_model");
		$lineup_result = $this->Lineup_model->get_single_row("*",LINEUP_MASTER,array("collection_id"=>$collection_id,"lineup_master_id"=>$lineup_master_id,"user_id"=>$this->user_id));
		
		$collection_player_cache_key = "st_collection_stocks_".$collection_id;
		$stock_list = $this->get_cache_data($collection_player_cache_key);
        if(!$stock_list)
        {
        	$collection_data = $this->Lineup_model->get_single_row("category_id,published_date,end_date,scheduled_date", COLLECTION,array("collection_id" => $collection_id));
        	$post_data['published_date'] = isset($collection_data['published_date']) ? $collection_data['published_date'] : "";
        	$post_data['end_date'] = isset($collection_data['end_date']) ? $collection_data['end_date'] : "";
			$post_data['scheduled_date'] = $collection_data['scheduled_date'];
			$stock_list = $this->Lineup_model->get_all_stocks($post_data);
        	//set collection team in cache for 2 hours
        	$this->set_cache_data($collection_player_cache_key,$stock_list,REDIS_1_MINUTE);
        }
        $stock_list_array = array_column($stock_list,NULL,'stock_id');
        $final_stock_list = array();
        $team_data = json_decode($lineup_result['team_data'],TRUE);
		$stock_info = array();

		$this->load->model("wishlist/Wishlist_model");
        $wishlist_stock_ids  = $this->Wishlist_model->fetch_wishlist_stock_ids($this->user_id, TRUE);

		//action 1 => Buy, 2 => sell
        foreach($team_data['b'] as $stock_id){
			$stock_info = $stock_list_array[$stock_id];
			if(!empty($stock_info)){
				$captain = 0;
				if($stock_id == $team_data['c_id']){
					$captain = 1;
				}

				if(isset($team_data['vc_id']) && $stock_id == $team_data['vc_id']){
					$captain = 2;
				}
			
				$stock_info['player_role'] = $captain;
				$stock_info['action'] = 1;//buy
				if(in_array($stock_id, $wishlist_stock_ids)) {
					$stock_info['is_wish'] = 1;
				}
				$final_stock_list[] = $stock_info;
			}
		}

		$stock_info = array();
		foreach($team_data['s'] as $stock_id){
			$stock_info = $stock_list_array[$stock_id];
			if(!empty($stock_info)){
				$captain = 0;
				if($stock_id == $team_data['c_id']){
					$captain = 1;
				}

				if(isset($team_data['vc_id']) && $stock_id == $team_data['vc_id']){
					$captain = 2;
				}
			
				$stock_info['player_role'] = $captain;
				$stock_info['action'] = 2;//sell
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
}