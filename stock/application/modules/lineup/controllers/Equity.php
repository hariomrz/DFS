<?php 
require_once APPPATH . 'modules/lineup/controllers/Lineup.php';
class Equity extends Lineup {

	private $lineup_lang = array();
	public $call_from_api = true;
	public $user_id_arr = array();

	public function __construct()
	{
		parent::__construct();
		$this->lineup_lang = $this->lang->line('lineup');
	}

	

	 /**
	 * Used for validate match start status
	 * @param array $collection_datar
	 * @return array
	*/
	public function check_match_status($collection_data = array())
	{
		$current_date =  format_date();
		$post_data = $this->input->post();
		if(empty($collection_data)){
			$collection_data = $this->Lineup_model->get_single_row("scheduled_date", COLLECTION,array("collection_id" => $post_data['collection_id']));
		}
		if(!empty($collection_data))
		{
			//for manage collection wise deadline
            $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
			$contest_date = date(DATE_FORMAT,strtotime($collection_data['scheduled_date'].'-'.$deadline_time.' minute'));
			$current_date = strtotime($current_date) * 1000;
			$contest_date = strtotime($contest_date) * 1000;
             
			if($current_date > $contest_date)
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']			= $this->lineup_lang['contest_started'];
				$this->api_response();
			}
			return true;
		}

		$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		$this->api_response_arry['message']			= $this->lineup_lang['contest_not_found'];
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
		$collection_data = $this->Lineup_model->get_single_row("scheduled_date,category_id,published_date,end_date", COLLECTION,array("collection_id" => $collection_id));
        if(empty($collection_data)){
    		$this->api_response_arry['message'] = $this->lineup_lang['match_detail_not_found'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
    	}

		$this->check_match_status($collection_data);

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
        	$this->set_cache_data($collection_stock_cache_key,$stock_list,REDIS_2_HOUR);
        }

		$stocks_price = array_column($stock_list,'current_price', "stock_id");
			

		$this->check_lineup_validate($stocks_price);
		
		$buy_stocks =array_keys($post_data['stocks']['b']);
		$sell_stocks =array_keys($post_data['stocks']['s']);

		$user_stocks = array_merge($buy_stocks,$sell_stocks);
		$post_data['user_stocks'] = $user_stocks;
		
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
		$this->stock_type = 2;
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
        $save_data['user_name'] = @$this->user_name;
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
	 * validate duplicate team data
	 * @param array $lineup
	 * @param int $collection_master_id
	 * @param int $lineup_master_id
	 * @return
	*/
	public function validate_duplicate_user_team1($stocks,$collection_id,$lineup_master_id=""){
		if(ALLOW_DUPLICATE_TEAM == 1){
			return true;
		}
		
		$post_data = $this->input->post();
		$team_players = $this->get_rendered_team_players($post_data['stocks'],2);
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
				$tmp_players = $this->get_rendered_team_players($team_data,2);

				// $st = array_merge($team_data['b'],$team_data['s']);
				// $tmp_players = isset($st) ? $st : array();
				$tmp_players[] = $team_data['c_id']."_1";
				if(VICE_CAPTAIN_POINT>0)
				{
					$team_players[] = $team_data['vc_id']."_2";
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
	 * validate lineup data
	 * @param int $league_id
	 * @param array $lineup
	 * @param array $position_array
	 * @return
	*/
	private function check_lineup_validate($stocks_price)
	{
		$post_data = $this->input->post();
		$post_stocks = $post_data['stocks'];
		$msg ="";
		/**
		 * Commented on 20Feb 2023
		 * The logic is all about your need purchase atlease one buy or Sell stock in portfolio
		 */
		/*if(empty($post_stocks['b']))
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
		}*/

		if(empty($post_stocks['b']) && empty($post_stocks['s'])){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error']	= $this->lineup_lang['err_empty_stocks'];
			$this->api_response();
		}
		/*******End*******/
		
		$st_ids = array_merge($post_stocks['b'],$post_stocks['s']);
		$stock_type = $this->Lineup_model->get_stock_type_config(2);

		$stock_type['config_data'] = json_decode($stock_type['config_data'],TRUE);
		if(count($st_ids) != $stock_type['config_data']['min'] )
		{
			$msg = str_replace('%s', $stock_type['config_data']['min'] , $this->lineup_lang["lineup_max_limit"]);
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

		$min_stock_cap = (int)$this->Lineup_model->get_stock_price_cap('min_cap_per_stock');
		$max_stock_cap = (int)$this->Lineup_model->get_stock_price_cap('max_cap_per_stock');

		$price_sum = 0;
		foreach( $post_stocks["b"] as $key => $value)
		{
			if(empty($value))
			{
				$msg = $this->lineup_lang['err_invalid_lot_size'];
			}

			if(empty($key))
			{
				$msg = $this->lineup_lang['err_invalid_stock'];
			}

			$stock_lot_price =($stocks_price[$key]*$value);
			
			if($stock_lot_price < $min_stock_cap || $stock_lot_price > $max_stock_cap)
			{
				// echo $key.'***';
				// echo $stock_lot_price;die;
				// $msg = "price should be between $min_stock_cap and $max_stock_cap";
				$msg = $this->lineup_lang['err_min_max_cap'];
				$msg = str_replace('##min_value##',$min_stock_cap,$msg);
				$msg = str_replace('##max_value##',$max_stock_cap,$msg);
			}
			
			$price_sum+=$stock_lot_price;

		}



		foreach( $post_stocks["s"] as $key => $value)
		{
			if(empty($value))
			{
				$msg = $this->lineup_lang['err_invalid_lot_size'];
			}

			if(empty($key))
			{
				$msg = $this->lineup_lang['err_invalid_stock'];
			}

			$stock_lot_price =($stocks_price[$key]*$value);
			
			if($stock_lot_price < $min_stock_cap || $stock_lot_price > $max_stock_cap)
			{
				// echo $key.'***';
				// echo $stock_lot_price;die;
				//$msg = "Price should be between $min_stock_cap and $max_stock_cap";
				$msg = $this->lineup_lang['err_min_max_cap'];
				$msg = str_replace('##min_value##',$min_stock_cap,$msg);
				$msg = str_replace('##max_value##',$max_stock_cap,$msg);
			}
			
			$price_sum+=$stock_lot_price;
		}

		$cap = $this->Lineup_model->get_stock_price_cap('salary_cap');
		
		if($price_sum > $cap)
		{
			$msg ="Price sum can not be greater than $cap";//$this->lineup_lang['err_price_sum_cap_exceed'];
			$msg =$this->lineup_lang['err_price_sum_cap_exceed'];
			$msg = str_replace('##max_cap##',$cap,$msg);
			$msg = str_replace('##tc##',$stock_type['config_data']['tc'],$msg);
		}

		if($msg!='')
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error']			= $msg;
			$this->api_response();
		}

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
        foreach($lineup_master_contest_ids as $key=>$lmcid){
            $team_data = $this->_common_team_detail($lmcid);
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
        $collection_player_cache_key = "st_collection_stocks_" . $team_info['collection_id'];
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
            if(isset($team_data['captain_player_team_id'])){
                $team_data['c_id'] = $team_data['captain_player_team_id'];
            }
    
            $lineup_details = $this->Contest_model->get_lineup_with_score($lineup_master_contest_id, $team_info);

            $team_data['sl'] = array_column($lineup_details,NULL,"stock_id");
        }else if(in_array($team_info['is_lineup_processed'],array("2","3"))){
            $completed_team = $this->Contest_model->get_single_row("collection_id,lineup_master_id,team_data",COMPLETED_TEAM, array("collection_id" => $team_info['collection_id'], "lineup_master_id" => $team_info['lineup_master_id']));
            $team_data = json_decode($completed_team['team_data'],TRUE);

            $team_data['sl'] = $this->get_rendered_stock_complted_fixture($team_data['b'],1);
			$sell_data = $this->get_rendered_stock_complted_fixture($team_data['s'],2);
			$team_data['sl'] = array_merge($team_data['sl'],$sell_data);
			$team_data['sl'] = array_column($team_data['sl'],NULL,"stock_id");
        }else{

			$team_data['sl'] = $this->get_rendered_stock_array($team_data['b'],1);
			$sell_data = $this->get_rendered_stock_array($team_data['s'],2);
			$team_data['sl'] = array_merge($team_data['sl'],$sell_data);
			$team_data['sl'] = array_column($team_data['sl'],NULL,"stock_id");
        }

		$lineup_total = $team_info['remaining_cap'];
        if(!empty($team_data['sl'])){
            foreach ($team_data['sl'] as $stock_id=>$score_data) {
                $stock_info = $stock_list_array[$stock_id];
                if(!empty($stock_info)) {
					$lineup = array();
					$lineup['price_sum'] = $stock_info['current_price']*$score_data['user_lot_size'];
                    $captain = 0;
                    if($stock_id == $team_data['c_id']){
                        $captain = 1;
						$lineup['price_sum'] = $stock_info['current_price']*$score_data['user_lot_size']*CAPTAIN_POINT;
                    }

                    if(isset($team_data['vc_id']) && $stock_id == $team_data['vc_id']){
                        $captain = 2;
						$lineup['price_sum'] = $stock_info['current_price']*$score_data['user_lot_size']*VICE_CAPTAIN_POINT;
                    }
                  
                    $lineup['stock_id'] = $stock_info['stock_id'];
                    $lineup['stock_name'] = $stock_info['stock_name'];
                    $lineup['logo'] = $stock_info['logo'];
                    $lineup['player_role'] = $captain;
                    $lineup['score'] = $score_data['score'];
                    $lineup['type'] = $score_data['type'];
                    $lineup['user_lot_size'] = $score_data['user_lot_size'];
                    $lineup['current_price'] = $stock_info['current_price'];
                    $lineup['last_price'] = $stock_info['last_price'];
                    $lineup['price_diff'] = $stock_info['price_diff'];
					$lineup_total+=$lineup['price_sum'];
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

	private function get_rendered_stock_complted_fixture($arr,$type)
	{
		$tmp = array();
		foreach($arr as $stock_id => $score_data)	{
			$tmp[] = array('stock_id' => $stock_id,'user_lot_size' => $score_data['user_lot_size'],'score' => $score_data['score'],'type' => $type );
	  }
	  return $tmp;
	}

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

        $post_data['published_date'] = $row['published_date'];
        $post_data['end_date'] = $row['end_date'];
        $post_data['scheduled_date'] = $row['scheduled_date'];
        $result = $this->Contest_model->get_lineup_score_calculation_equity($post_data);
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data']['lineup'] = $result;
        $this->api_response_arry['data']['percent_change'] = $row['percent_change'];
        $this->api_response_arry['data']['total_score'] = $row['total_score'];
        $this->api_response_arry['data']['remaining_cap'] = $row['remaining_cap'];
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
        foreach($team_data['b'] as $stock_id => $user_lot_size){
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
				$stock_info['user_lot_size'] = $user_lot_size;//buy
				if(in_array($stock_id, $wishlist_stock_ids)) {
					$stock_info['is_wish'] = 1;
				}
				$final_stock_list[] = $stock_info;
			}
		}

		$stock_info = array();
		foreach($team_data['s'] as $stock_id => $user_lot_size) {
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
				$stock_info['user_lot_size'] = $user_lot_size;
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