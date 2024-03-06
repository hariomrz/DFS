<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Contest extends Common_Api_Controller {

    public function __construct() {
        parent::__construct();
        $_POST = $this->input->post();
		
    }

    public function create_contest_post() {
        if ($this->input->post()) {
			$this->load->model('admin/Contest_model');
            $game_data = $this->input->post();
            $this->form_validation->set_rules('group_id', $this->lang->line('group'), 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('category_id', $this->lang->line('category'), 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('contest_name', $this->lang->line('game_name'), 'trim|required|max_length[50]');
            $this->form_validation->set_rules('minimum_size', 'minimum size', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('size', 'size', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('multiple_lineup', 'multiple lineup', 'trim|required');
            $this->form_validation->set_rules('entry_fee', $this->lang->line('entry_fee'), 'trim|required');
            $this->form_validation->set_rules('max_bonus_allowed', $this->lang->line('max_bonus_allowed'), 'trim|required');
            $this->form_validation->set_rules('prize_type', $this->lang->line('prize_type'), 'trim|required');
            $this->form_validation->set_rules('prize_pool', $this->lang->line('prize_pool'), 'trim|required');
            $this->form_validation->set_rules('entry_fee_type', 'entry fee type', 'trim|required');
            $this->form_validation->set_rules('site_rake', $this->lang->line('site_rake'), 'trim|required');
			

            if(isset($game_post['set_sponsor']) && $game_post['set_sponsor'] == 1)
            {
                $this->form_validation->set_rules('sponsor_name', $this->lang->line("sponsor_name"),'trim|max_length[60]');
                $this->form_validation->set_rules('sponsor_logo', $this->lang->line("sponsor_logo"),'trim|required|max_length[50]');
                $this->form_validation->set_rules('sponsor_contest_dtl_image', $this->lang->line("sponsor_contest_dtl_image"),'trim|required|max_length[50]');
                $this->form_validation->set_rules('sponsor_link', $this->lang->line("sponsor_link"),'trim|max_length[255]');
            }
            $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');

            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }

            $multiple_lineup = $this->input->post('multiple_lineup');
            $prize_pool_type = $this->input->post('prize_pool_type');
            $prize_pool = 0;
            $guaranteed_prize = '0';
            if ($game_data['prize_pool_type'] == '1') {
                $guaranteed_prize = '1';
            } else if ($game_data['prize_pool_type'] == '2') {
                $guaranteed_prize = '2';
                $game_data['site_rake'] = 0;
              
            }

            if ($multiple_lineup && $multiple_lineup > $game_data['size']) {
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('invalid_game_multiple_lineup_size');
                $this->api_response();
            }

            $prize_details_inputs['entry_fee'] = $game_data['entry_fee'];
          

            if ($prize_pool_type == '0') {
                $game_data['is_custom_prize_pool'] = 0;
            } else if ($prize_pool_type == '1' || $prize_pool_type == '2') {
                $game_data['is_custom_prize_pool'] = 1;
            }

            $payout_data = isset($game_data['payout_data']) ? $game_data['payout_data'] : array();
            $max_winners = array_column($payout_data, "max");
            $max_winners = max($max_winners);
            if($max_winners > $game_data['size']){
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = "You can't define winner more then size.";
                $this->api_response();
            }
            
            if($game_data['prize_pool_type'] == '1' || $game_data['prize_pool_type'] == '2')
            {
                $prize_pool = $game_data['prize_pool'];
                $payout_data = isset($game_data['payout_data']) ? $game_data['payout_data'] : array();
            }
            $merchandise_ids = array_column($payout_data, "prize_type");
            if(in_array("3", $merchandise_ids)){
                if($game_data['is_tie_breaker'] != 1){
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->lang->line('invalid_tie_breaker_status');
                    $this->api_response();
                }
            
            }

			$collection_data = $this->Contest_model->get_single_row('scheduled_date',COLLECTION,array("collection_id"=> $game_data['collection_id']));
			$season_scheduled_date = $collection_data['scheduled_date'];

            $max_prize_pool = 0;
            //change values for percentage case
            foreach($payout_data as $key=>$prize){
                $amount = $prize['amount'];
                if(isset($game_data['prize_value_type']) && $game_data['prize_value_type'] == 1 && $prize['prize_type'] != "3"){
                    $payout_data[$key]['per'] = $prize['amount'];
                    $person_count = ($prize['max'] - $prize['min']) + 1;
                    $per_person = ((($prize_pool * $prize['amount']) / 100) / $person_count);
                    $amount = $per_person;
                    $payout_data[$key]["amount"] = number_format($per_person,"2",".","");
                }
                if(isset($prize['prize_type']) && $prize['prize_type'] == 1){
                    if(isset($prize['max_value'])){
                        $mx_amount = $prize['max_value'];
                    }else{
                        $mx_amount = $amount;
                    }
                    $max_prize_pool = $max_prize_pool + $mx_amount;
                }
            }
            $payout_data = json_encode($payout_data);
			
			if(isset($game_data['contest_unique_id'])){
				$contest_unique_id = $game_data['contest_unique_id'];
			}else{
				$contest_unique_id = random_string('alnum', 9);
			}

            /*brokerage for stock type 4*/
            $brokerage = 0.00;
            if($game_data['brokerage'] > 0){
                $brokerage = $game_data['brokerage'];
            }
			$contest_data = array(
                    "collection_id"             => $game_data['collection_id'],
					"contest_unique_id"			=> $contest_unique_id,
					"category_id"				=> $game_data['category_id'],
					"group_id"					=> $game_data['group_id'],
					"contest_name"				        => $game_data['contest_name'],
                    "contest_title"                     => isset($game_data['contest_title']) ? $game_data['contest_title'] : "",
					"minimum_size"				    => $game_data['minimum_size'],
					"size"					=> $game_data['size'],
					"multiple_lineup"			=> $multiple_lineup,
					"entry_fee"					=> $game_data['entry_fee'],
					"site_rake"					=> $game_data['site_rake'],
					"max_bonus_allowed"			=> $game_data['max_bonus_allowed'],
					"currency_type"				=> $game_data['entry_fee_type'],
					"prize_type"				=> $game_data['prize_type'],
					"prize_pool"				=> $prize_pool,
					"prize_distibution_detail"	=> $payout_data,
					"scheduled_date"		=> $season_scheduled_date,
					"guaranteed_prize"			=> $guaranteed_prize,
					"is_custom_prize_pool"		=> $game_data['is_custom_prize_pool'],
					"is_auto_recurring"			=> isset($game_data['is_auto_recurring']) && $game_data['is_auto_recurring'] ? 1 : 0,
					"is_pin_contest"            => isset($game_data['is_pin_contest']) && $game_data['is_pin_contest'] ? 1 : 0,
                    "is_tie_breaker"            => $game_data['is_tie_breaker'],
                    "prize_value_type"          => isset($game_data['prize_value_type']) ? $game_data['prize_value_type'] : 0,
					"status"					=> 0,
                    "brokerage"                 => $brokerage,
					"added_date"				=> format_date()
				);

            //add sponsor if checked 
            if(isset($game_data['set_sponsor']) && $game_data['set_sponsor'] == 1)
            {
                $contest_data['sponsor_name'] = $game_data['sponsor_name'];
                $contest_data['sponsor_logo'] = $game_data['sponsor_logo'];
                $contest_data['sponsor_contest_dtl_image'] = $game_data['sponsor_contest_dtl_image'];
                $contest_data['sponsor_link'] = (isset($game_data['sponsor_link']) && $game_data['sponsor_link'] != '') ? $game_data['sponsor_link'] : NULL;
            }
            
            if ($contest_data['is_auto_recurring'] == 1)
			{
				$contest_data['base_prize_details'] = json_encode(array("prize_pool" => $prize_pool, "prize_distibution_detail" => $payout_data));
			}
			
			if(!isset($game_data['contest_unique_id']))
			{
				if($game_data['entry_fee'] == 0 && $prize_pool > 0 && $contest_data['is_auto_recurring']==1) 
                {
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['status']          = FALSE;
                    $this->api_response_arry['message']         = $this->lang->line('auto_recurrent_create_error');
                    $this->api_response();
                }
				
				$contest_id = $this->Contest_model->save_contest($contest_data);
				if($contest_id){
					
                    $col_cache_key = 'st_collection_contest_'.$game_data['collection_id'];
                    $this->delete_cache_data($col_cache_key);

                    $col_pin_cache_key = 'st_collection_pin_contest_'.$game_data['collection_id'];
                    $this->delete_cache_data($col_pin_cache_key);

					//delete filters
					$this->delete_cache_data('st_lobby_filters');

					//delete lobby upcoming section file
					$this->delete_cache_data('st_lobby_fixture_list');

					//stock contest add notification
					$collection_name = $this->Contest_model->get_cname($game_data['collection_id'],$game_data['stock_type']);
					if($collection_name['name']){
						$contest_name = $this->input->post('contest_name');
						$category = '';
						switch($this->input->post('category_id'))
						{
							case 1:
								$category = "Daily";
							break;
							case 2:
								$category = "Weekly";
							break;
							case 3:
								$category = "Monthly";
							break;
						}
						$this->load->model('Contest_model');
						
						$contest_content = array(
							"notification_type"             			=> 567, // notification of contest added
							"cname"             		             	=> $collection_name['name'],
							"contest_name"                          	=> $contest_name,
							"category"									=> $category,
							"category_id"								=> $this->input->post('category_id'),
							"collection_id"								=> $this->input->post('collection_id'),
							"stock_type"								=> $collection_name['stock_type'],
						);
						$test_data = json_encode($contest_content);
						$this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
						$this->load->helper('queue_helper');
						add_data_in_queue($contest_content,'stock_push');
					}
				}

				$this->api_response_arry['data']			= $contest_id;
				$this->api_response_arry['message']  	  = "Contest added successfully";

			}
			else
			{
				$post  = $this->post();
		
				$is_update  = $this->Contest_model->update_contest($post);
				
				if($is_update)
				{
					//delete contest cache
					$contest = $this->Contest_model->get_single_row('contest_id',CONTEST,array('contest_unique_id' => $post['contest']["contest_unique_id"]));

					$match_cache_key = 'contest_'.$contest['contest_id']."_match_list";
					$this->delete_cache_data($match_cache_key);

					$filter_cache_key = 'st_lobby_filters';
					$this->delete_cache_data($filter_cache_key);

					$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
					$this->api_response_arry['data']  		  = $post['contest']['contest_unique_id'];
					$this->api_response_arry['message']  	  = "Contest update successfully";
				}
				else
				{
					$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
					$this->api_response_arry['data']  		  = $post['contest_unique_id'];
					$this->api_response_arry['message']  	  = "Contest not update";
				}
			}

			
			$this->api_response_arry['status']			= TRUE;
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['data']			= array();
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status']			= FALSE;
			$this->api_response_arry['message']			= $this->lang->line('invalid_parameter');
			$this->api_response();
		}
	}

    /**
     * @method create_template_contest
     * @uses function to create contest by contest template
     * @param collection_id int
     * 
    */
    public function create_template_contest_post() {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        if (empty($post_data['selected_templates'])) {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please select atleast one template."), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }
		$this->load->model('admin/Contest_model');
        $this->load->model('Contest_template_model');
        $collection_info = $this->Contest_template_model->get_single_row('name,scheduled_date as season_scheduled_date,category_id,stock_type',COLLECTION,array('collection_id' => $post_data['collection_id']));
        if (empty($collection_info)) {
            $this->api_response_arry['global_error'] = "Please select a valid fixture.";
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response();
        }
        
        $collection_id = $post_data['collection_id'];
        $season_scheduled_date = $collection_info['season_scheduled_date'];

        $template_list = $this->Contest_template_model->get_template_details_for_create_contest($post_data);

        if (empty($template_list)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['service_name'] = "create_template_contest";
            $this->api_response_arry['global_error'] = "Template details not found.";
            $this->api_response();
        }

        $result = 0;
        $is_candel_exist = $this->Contest_model->check_candel_exist($collection_id);
        
        foreach ($template_list as $game_data) {
            $game_data['contest_unique_id'] = random_string('alnum', 9);
            $game_data['category_id'] = $collection_info['category_id'];
            $game_data['contest_name'] = $game_data['contest_name'];
            $game_data['contest_title'] = isset($game_data['contest_title']) ? $game_data['contest_title'] : "";
            $game_data['collection_id'] = $collection_id;
            $game_data['scheduled_date'] = $season_scheduled_date;
            $game_data['status'] = 0;
            $game_data['brokerage'] = $game_data['brokerage'];
            $game_data['added_date'] = format_date();

            $payout_data = json_decode($game_data['prize_distibution_detail'],TRUE);
            $max_prize_pool = 0;
            //change values for percentage case
            if(isset($game_data['max_prize_pool']) && $game_data['max_prize_pool'] > 0){
                $max_prize_pool = $game_data['max_prize_pool'];
            }else{
                foreach($payout_data as $key=>$prize){
                    $amount = $prize['amount'];
                    if(isset($prize['prize_type']) && $prize['prize_type'] == 1){
                        if(isset($prize['max_value'])){
                            $mx_amount = $prize['max_value'];
                        }else{
                            $mx_amount = $amount;
                        }
                        $max_prize_pool = $max_prize_pool + $mx_amount;
                    }
                }
            }
            $game_data['max_prize_pool'] = $max_prize_pool;

            if ($game_data['is_auto_recurring'] == 1) {
                $game_data['base_prize_details'] = json_encode(array("prize_pool" => $game_data['prize_pool'], "prize_distibution_detail" => $game_data['prize_distibution_detail']));
            }

            $stock_type = $game_data['stock_type'];
            unset($game_data['template_name']);
            unset($game_data['template_title']);
            unset($game_data['template_description']);
            unset($game_data['stock_type']);

            $result = $this->Contest_model->save_contest($game_data);
            //var_dump($result); die;
        }

        if ($result) {
          	
            $col_cache_key = 'st_collection_contest_'.$collection_id;
            $this->delete_cache_data($col_cache_key);

            $col_pin_cache_key = 'st_collection_pin_contest_'.$collection_id;
            $this->delete_cache_data($col_pin_cache_key);

            //delete filters
            $this->delete_cache_data('st_lobby_filters');

            //delete lobby upcoming section file
            $this->delete_cache_data('st_lobby_fixture_list');

            if($stock_type == 3)
            {
                $contest_content = array(
                    "notification_type" =>623,
                    "collection_id" => $collection_id,
                    "stock_type"=>$collection_info['stock_type']
                );
                if(!$is_candel_exist){
                    $this->load->helper('queue_helper');
                    add_data_in_queue($contest_content,'stock_push');
                }
            }
        
            
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['message'] = "Contest created for selected template.";
            $this->api_response();
        } else {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('no_change');
            $this->api_response();
        }
    }

    function get_collection_details_post()
    {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $this->load->model('admin/Contest_model');
        $collection_info = $this->Contest_model->get_collection_details($post_data['collection_id']);
        if (empty($collection_info)) {
            $this->api_response_arry['global_error'] = "Please select a valid fixture.";
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response();
        }

		$counts = $this->Contest_model->get_match_paid_free_users($post_data['collection_id']);

		$collection_info = array_merge($collection_info,$counts);

		//get week number
		if($collection_info['category_id'] == '2')
		{
			$date = new DateTime($collection_info['scheduled_date']);
			$collection_info['week'] = $date->format("W");
		}
		else if($collection_info['category_id'] == '3')
		{
			//get month number
			$time=strtotime($collection_info['scheduled_date']);
			$collection_info['month'] = date("F",$time);
		}

        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data'] = $collection_info ;
        $this->api_response();
        
    }

    function get_category_list_post()
    {   
        $this->load->model('admin/Contest_model');
        $category_list = $this->Contest_model->get_category_list();
      
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data'] = $category_list ;
        $this->api_response();
    }

    /**
     * Used to get contest filter option
     */
    public function get_contest_filter_post(){
		$this->load->model('admin/Contest_model');
		$group_list = $this->Contest_model->get_all_group_list();
        $category_list = $this->Contest_model->get_category_list();
		$status_list = array();
		$status_list[] = array("label"=>"Select Status","value"=>"");
		$status_list[] = array("label"=>"Current Contest","value"=>"current_game");
		$status_list[] = array("label"=>"Completed Contest","value"=>"completed_game");
		$status_list[] = array("label"=>"Cancelled Contest","value"=>"cancelled_game");
		$status_list[] = array("label"=>"Upcoming Contest","value"=>"upcoming_game");
       

		$result = array(
					'category_list'		=> $category_list,
					'group_list'		=> $group_list,
					'status_list'		=> $status_list
				);

		$a_equity = isset($this->app_config['allow_equity'])?$this->app_config['allow_equity']['key_value']:0;		
		$a_stock = isset($this->app_config['allow_stock_fantasy'])?$this->app_config['allow_stock_fantasy']['key_value']:0;
        $a_predict = isset($this->app_config['allow_stock_predict'])?$this->app_config['allow_stock_predict']['key_value']:0;  

         $a_livefantasy =  isset($this->app_config['allow_livefantasy'])?$this->app_config['allow_livefantasy']['key_value']:0;  
		

		$stock_filter[] = array('label' => 'All','value' => 0);

		if($a_stock)
		{
			$stock_filter[] = array('label' => 'Stock Fantasy','value' => 1);
		}

		if($a_equity)
		{
			$stock_filter[] = array('label' => 'Equity','value' => 2);
		}

        if($a_predict)
        {
            $stock_filter[] = array('label' => 'Stock Predict','value' => 3);
        }

        if($a_livefantasy)
        {
            $stock_filter[] = array('label' => 'Live Stock Fantasy','value' => 4);
        }  

		$result['stock_filter'] = $stock_filter;  

		$this->api_response_arry['data']          = $result;
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response();
	}  
 
    /**
     * Used to get contest report
     */
    public function get_report_post_old() {
		// converting from clientzone to utc
		$this->_get_client_dates('Y-m-d H:i:s',1);

		$filters = $this->input->post();
		$filters["csv"] = FALSE;

		$this->load->model('admin/Contest_model');
		$result	= $this->Contest_model->get_completed_contest_report($filters);
		$contest_detail = $result['result'];
		$total_contest = $result['total'];

        $result_array = array();
		$contest_prize_detail = array();
		if(!empty($contest_detail)) {
			$contest_ids =  array_column($contest_detail, 'contest_id');
			$temp_prize_detail = $this->Contest_model->get_contest_prize_detail($contest_ids);

			$contest_prize_detail = array_column($temp_prize_detail,NULL,'contest_id');
			$contest_unique_ids =  array_column($contest_detail, 'contest_unique_id');
			$promocode_entry_result = $this->Contest_model->get_contest_promo_code_entry($contest_unique_ids);

			$promocode_entry = array();
			if(!empty($promocode_entry_result)) {
				$promocode_entry = array_column($promocode_entry_result,'promocode_entry_fee_real','contest_unique_id');
			}

			$result_array["sum_join_real_amount"] = 0;
			$result_array["sum_join_bonus_amount"] = 0;
			$result_array["sum_join_winning_amount"] = 0;
			$result_array["sum_join_coin_amount"]=0;
			$result_array["sum_win_amount"] = 0;
			$result_array["sum_total_entery_fee"]=0;
			$result_array["sum_profit_loss"] = 0;
			$result_array["sum_entry_fee"] = 0;
			$result_array["sum_site_rake"] = 0;
			$result_array["sum_min"] = 0;
			$result_array["sum_max"] = 0;
			$result_array["sum_total_user_joined"] = 0;

			//$result_array["sum_system_teams"] = 0;
			//$result_array["sum_real_teams"] = 0;

			$result_array["sum_max_bonus_allowed"] = 0;
			$result_array["sum_prize_pool"] = 0;
			$result_array["sum_total_entry_fee"] = 0;

			$result_array["sum_total_entry_fee_real"] = 0;
			//$result_array["sum_botuser_total_real_entry_fee"] = 0;

			$result_array["sum_promocode_entry_fee_real"] = 0;
			$result_array["sum_total_win_coins"] = 0;
			$result_array["sum_total_win_bonus"] = 0;

			$result_array["sum_total_win_amount_to_real_user"] = 0;

			foreach($contest_detail as &$contest) {
				$contest['siterake_amount'] = number_format((($contest['prize_pool']*$contest['site_rake'])/100),2,'.',',');
               /* $contest["total_join_real_amount"] = 0;
                $contest["total_win_winning_amount"] = 0;
                $contest["total_join_winning_amount"] = 0;
                $contest["total_join_bonus_amount"] = 0;
                $contest["total_join_coin_amount"] = 0;
                $contest["total_win_coins"] = 0;
                $contest["total_win_bonus"] = 0;
                $contest["total_win_amount_to_real_user"] = 0;
                */
                
				if(isset($contest_prize_detail[$contest['contest_id']])) {
					$result_array["sum_total_entry_fee_real"]+=$contest_prize_detail[$contest['contest_id']]['total_join_real_amount'];
					$contest = array_merge($contest, $contest_prize_detail[$contest['contest_id']]);
				}
				
				$contest["profit_loss"]	= number_format(($contest["total_join_real_amount"] - $contest["total_win_winning_amount"]),2,'.','');
				//$contest["total_entry_fee"]		= number_format(($contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"] + $contest["total_join_coin_amount"]),2,'.',',');
				
				$contest["promocode_entry_fee_real"] = 0;
				if(isset($promocode_entry[$contest['contest_unique_id']]))
				{
					$contest["promocode_entry_fee_real"] = $promocode_entry[$contest['contest_unique_id']];
					$result_array["sum_promocode_entry_fee_real"] += $contest["promocode_entry_fee_real"];
				}
				$result_array["sum_join_real_amount"] += $contest["total_join_real_amount"];
				$result_array["sum_join_winning_amount"] += $contest["total_join_winning_amount"];
				$result_array["sum_join_bonus_amount"] += $contest["total_join_bonus_amount"];
				$result_array["sum_join_coin_amount"] += $contest["total_join_coin_amount"];
				$result_array["sum_win_amount"] += $contest["total_win_winning_amount"];
				$result_array["sum_total_win_coins"] += $contest["total_win_coins"];
				$result_array["sum_total_win_bonus"] += $contest["total_win_bonus"];
				$result_array["sum_total_entery_fee"] += ($contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_coin_amount"]);
				$result_array["sum_profit_loss"] += $contest["profit_loss"]; 
				$result_array["sum_entry_fee"] += $contest["total_entry_fee"]; 
				$result_array["sum_site_rake"] += $contest["site_rake"]; 
				$result_array["sum_min"] += $contest["minimum_size"]; 
				$result_array["sum_max"] += $contest["size"]; 
				$result_array["sum_total_user_joined"] += $contest["total_user_joined"]; 
				//$result_array["sum_system_teams"] += $contest["system_teams"]; 
				//$result_array["sum_real_teams"] += $contest["real_teams"]; 
				$result_array["sum_max_bonus_allowed"] += $contest["max_bonus_allowed"]; 
				$result_array["sum_prize_pool"] += $contest["prize_pool"]; 
				$result_array["sum_total_entry_fee"] += $contest["total_entry_fee"]; 
				//$result_array["sum_botuser_total_real_entry_fee"] += $contest["botuser_total_real_entry_fee"]; 
				$result_array["sum_total_win_amount_to_real_user"] += $contest["total_win_amount_to_real_user"]; 
				$result_array["result"][] = $contest;

			}
			//unset($contest);
            $result_array["sum_join_real_amount"] = number_format($result_array["sum_join_real_amount"],2,'.',',');
            $result_array["sum_join_winning_amount"] = number_format($result_array["sum_join_winning_amount"],2,'.',',');
            $result_array["sum_join_bonus_amount"] = number_format($result_array["sum_join_bonus_amount"],2,'.',',');
            $result_array["sum_join_coin_amount"] = number_format($result_array["sum_join_coin_amount"],2,'.',',');
            $result_array["sum_win_amount"] = number_format($result_array["sum_win_amount"],2,'.',',');
            $result_array["sum_total_entery_fee"] = number_format($result_array["sum_total_entery_fee"],2,'.',',');
            $result_array["sum_profit_loss"] = number_format($result_array["sum_profit_loss"],2,'.',',');

			$result_array["total"] = $total_contest;
		}
		//$this->response(array(config_item('rest_status_field_name')=>TRUE, 'data'=>$result_array) , rest_controller::HTTP_OK);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $result_array;
		$this->api_response();	
	}

	/**
	 * Used to export exontest report
	 */
	public function contest_report_csv_get_old() {
		$_POST = $this->input->get();

		$filters = $_POST;
		$filters["csv"] = TRUE;
		$this->load->model('admin/Contest_model');
		
        $result	= $this->Contest_model->get_completed_contest_report($filters);
		$contest_detail = $result['result'];

		$result_array = array();
		
		$html_string_f ="<!DOCTYPE html>
		<html>
		<head>
			<title></title>
		</head>
		<body>
			<h1>No Record</h1>
		</body>
		</html>";
		if(!empty($contest_detail)) {
			$contest_ids =  array_column($contest_detail, 'contest_id');
			$temp_prize_detail = $this->Contest_model->get_contest_prize_detail($contest_ids);
			$contest_prize_detail = array_column($temp_prize_detail,NULL,'contest_id');
			
			$contest_unique_ids =  array_column($contest_detail, 'contest_unique_id');
			$promocode_entry_result = $this->Contest_model->get_contest_promo_code_entry($contest_unique_ids);

			$promocode_entry = array();
			if(!empty($promocode_entry_result)) {
				$promocode_entry = array_column($promocode_entry_result,'promocode_entry_fee_real','contest_unique_id');
			}

			$result_array["sum_join_real_amount"] = 0;
			$result_array["sum_join_bonus_amount"] = 0;
			$result_array["sum_join_winning_amount"] = 0;
			$result_array["sum_join_coin_amount"]=0;
			$result_array["sum_win_amount"] = 0;
			$result_array["sum_total_entery_fee"]=0;
			$result_array["sum_profit_loss"] = 0;
			$result_array["sum_entry_fee"] = 0;
			$result_array["sum_site_rake"] = 0;
			$result_array["sum_min"] = 0;
			$result_array["sum_max"] = 0;
			$result_array["sum_total_user_joined"] = 0;
			//$result_array["sum_system_teams"] = 0;
			//$result_array["sum_real_teams"] = 0;
			$result_array["sum_max_bonus_allowed"] = 0;
			$result_array["sum_prize_pool"] = 0;
			$result_array["sum_total_entry_fee"] = 0;
			$result_array["sum_total_entry_fee_real"] = 0;
			//$result_array["sum_botuser_total_real_entry_fee"] = 0;
			$result_array["sum_promocode_entry_fee_real"] = 0;
			$result_array["sum_total_win_coins"] = 0;
			$result_array["sum_total_win_bonus"] = 0;

		

			$html_string_f = "<table>";
			$html_string_f.="
						<thead><tr>
						<th >Contest Type</th>
						<th >Contest Category</th>
						<th >Contest Name</th>
						<th >Min</th>
						<th >Max</th>		
						<th >Entry Fees</th>
						<th >Site Rake (%)</th>						
						<th >Total Team Created</th>						
						<th >Bonus Allowed %</th>
						<th >Prize Pool</th>
						<th >Total Entry Fee</th>
						<th >Entry Fees(Real Money)</th>
						<th >Entry Fees(Bonus)</th>
						<th >Entry Fees(Promo Code)</th>						
						<th >Distribution(Real Money)</th>
						<th >Distribution(Bonus)</th>
						<th >Distribution(Coin)</th>
						<th>Total Win Prize</th>
						<th>Total(Profit/Loss)</th>
						<th>Guaranteed Prize</th>
						<th>Start Time</th>
						<th>Stock Type</th>
						</tr></thead>
						<tbody>
						";
			foreach($contest_detail as $contest)
			{
				/* $contest["total_join_real_amount"] = 0;
                $contest["total_win_winning_amount"] = 0;
                $contest["total_join_winning_amount"] = 0;
                $contest["total_join_bonus_amount"] = 0;
                $contest["total_join_coin_amount"] = 0;
                $contest["total_win_coins"] = 0;
                $contest["total_win_bonus"] = 0;
                $contest["total_win_amount_to_real_user"] = 0;
				*/
				
				$contest['stock_type_text'] = $this->stock_type_map[$contest['stock_type']];
				if(isset($contest_prize_detail[$contest['contest_id']]))
				{
					$result_array["sum_total_entry_fee_real"]+=$contest_prize_detail[$contest['contest_id']]['total_join_real_amount'];
					$contest = array_merge($contest, $contest_prize_detail[$contest['contest_id']]);
				}
				$contest["profit_loss"]	= number_format((($contest["total_join_real_amount"]+$contest["total_join_winning_amount"]) - $contest["total_win_winning_amount"]),2,'.','');
				//$contest["total_entry_fee"]		= number_format(($contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"] + $contest["total_join_coin_amount"]),2,'.',',');
				$contest["promocode_entry_fee_real"] = 0;
				if(isset($promocode_entry[$contest['contest_unique_id']]))
				{
					$contest["promocode_entry_fee_real"] = $promocode_entry[$contest['contest_unique_id']];
					$result_array["sum_promocode_entry_fee_real"] += $contest["promocode_entry_fee_real"];
				}

				//$contest["total_entry_fee"]		= $contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"];
				
				$result_array["sum_join_real_amount"] += $contest["total_join_real_amount"];
				$result_array["sum_join_winning_amount"] += $contest["total_join_winning_amount"];
				$result_array["sum_join_bonus_amount"] += $contest["total_join_bonus_amount"];
				$result_array["sum_join_coin_amount"] += $contest["total_join_coin_amount"];
				$result_array["sum_win_amount"] += $contest["total_win_winning_amount"];
				$result_array["sum_total_win_coins"] += $contest["total_win_coins"];
				$result_array["sum_total_win_bonus"] += $contest["total_win_bonus"];
				$result_array["sum_total_entery_fee"] += ($contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"] + $contest["total_join_coin_amount"]);
				$result_array["sum_profit_loss"] += $contest["profit_loss"]; 
				$result_array["sum_entry_fee"] += $contest["total_entry_fee"]; 
				$result_array["sum_site_rake"] += $contest["site_rake"]; 
				$result_array["sum_min"] += $contest["minimum_size"]; 
				$result_array["sum_max"] += $contest["size"]; 
				$result_array["sum_total_user_joined"] += $contest["total_user_joined"]; 
				//$result_array["sum_system_teams"] += $contest["system_teams"]; 
				//$result_array["sum_real_teams"] += $contest["real_teams"]; 
				$result_array["sum_max_bonus_allowed"] += $contest["max_bonus_allowed"]; 
				$result_array["sum_prize_pool"] += $contest["prize_pool"]; 
				$result_array["sum_total_entry_fee"] += $contest["total_entry_fee"]; 
				//$result_array["sum_botuser_total_real_entry_fee"] += $contest["botuser_total_real_entry_fee"]; 
				// $result_array["result"][] = $contest;

				$html_string_f.="<tr>";
				$html_string_f.="<td>".$contest["category_name"]."</td>";
				$html_string_f.="<td>".$contest["group_name"]."</td>";
				$html_string_f.="<td>".$contest["contest_name"]."</td>";				
				$html_string_f.="<td>".$contest["minimum_size"]."</td>";
				$html_string_f.="<td>".$contest["size"]."</td>";
				$html_string_f.="<td>".$contest["entry_fee"]."</td>";
				$html_string_f.="<td>".$contest["site_rake"]."</td>";
                $html_string_f.="<td>".$contest["total_user_joined"]."</td>";
				$html_string_f.="<td>".$contest["max_bonus_allowed"]."</td>";
				$html_string_f.="<td>".$contest["prize_pool"]."</td>";
				$html_string_f.="<td>".$contest["total_entry_fee"]."</td>";
				$html_string_f.="<td>".$contest["total_join_real_amount"]."</td>";
				$html_string_f.="<td>".$contest["total_join_bonus_amount"]."</td>";
				$html_string_f.="<td>".$contest["promocode_entry_fee_real"]."</td>";
				//$html_string_f.="<td>".$contest["botuser_total_real_entry_fee"]."</td>";
				//$html_string_f.="<td>".$contest["total_join_winning_amount"]."</td>";

				$html_string_f.="<td>".$contest["total_win_winning_amount"]."</td>";
				$html_string_f.="<td>".$contest["total_win_bonus"]."</td>";
				$html_string_f.="<td>".$contest["total_win_coins"]."</td>";
                $html_string_f.="<td>".$contest["total_win_amount_to_real_user"]."</td>";
				$html_string_f.="<td>".$contest["profit_loss"]."</td>";
				$html_string_f.="<td>".$contest["guaranteed_prize"]."</td>";
				$html_string_f.="<td>".$contest["scheduled_date"]."</td>";
				$html_string_f.="<td>".$contest["stock_type_text"]."</td>";
				$html_string_f.="</tr>";
			}
			$html_string_f.="<tr>";
	
			$html_string_f.="<td>&nbsp;</td>";
			$html_string_f.="<td>&nbsp;</td>";
			$html_string_f.="<td>&nbsp;</td>";
            $html_string_f.="<td><b>".$result_array["sum_min"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_max"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_entry_fee"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_site_rake"]."</b></td>";			
			$html_string_f.="<td><b>".$result_array["sum_total_user_joined"]."</b></td>";
			//$html_string_f.="<td><b>".$result_array["sum_system_teams"]."</b></td>";
			//$html_string_f.="<td><b>".$result_array["sum_real_teams"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_max_bonus_allowed"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_prize_pool"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_total_entry_fee"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_join_real_amount"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_join_bonus_amount"]."</b></td>";
            $html_string_f.="<td><b>".$result_array["sum_promocode_entry_fee_real"]."</b></td>";
			//$html_string_f.="<td><b>".$result_array["sum_botuser_total_real_entry_fee"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_win_amount"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_total_win_bonus"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_total_win_coins"]."</b></td>";
            $html_string_f.="<td>&nbsp;</td>";
			$html_string_f.="<td><b>".$result_array["sum_profit_loss"]."</b></td>";
			$html_string_f.="<td>&nbsp;</td>";
            $html_string_f.="<td>&nbsp;</td>";
			$html_string_f.="</tr>";
			$html_string_f.="</body></table>";
		}
		$file='contest_report.xls';
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$file");
		echo $html_string_f;
		exit;
	}

	/**
	 * Used to get contest list
	 */
	public function get_contest_list_post() {
		$post_data = $this->input->post();		
		$this->load->model('admin/Contest_model');
		$data = $this->Contest_model->get_contest_list($post_data);
		$this->api_response_arry['data']= $data;
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response();
	}

	/**
	 * Used to export contest winners
	 */
	public function export_contest_winners_get() {
		$post = $this->input->get();
		$this->load->model('admin/Contest_model');
		$winners_data = $this->Contest_model->export_contest_winner_data();
		if(!empty($winners_data)){
			$header = array_keys($winners_data[0]);			
			$winners_data = array_merge(array($header),$winners_data);			
			$this->load->helper('csv');
			array_to_csv($winners_data,'contest_winner_data-'.$post['contest_id'].'.csv');
		}
	}	

	public function get_fixture_contest_post() {  
        $post_data = $this->input->post();
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();
        $post_data['collection_master_id'] = 0;

        //check collection exist or not for fixture
        // $collection_info = $this->Contest_model->get_fixture_collection_details($post_data);
		
		// if (!empty($collection_info) && $collection_info['collection_master_id'] != "") {
        //     $post_data['collection_master_id'] = $collection_info['collection_master_id'];
        // }
        $this->load->model('admin/Contest_model');
		$data = $this->Contest_model->get_fixture_contest($post_data);
		
        $contest_list = array();
        $show_cancel = 0;
	
	
        if (isset($data['result'])) {
            $contest_status = array_column($data['result'], 'status');
            if(in_array($show_cancel, $contest_status)) {
                $show_cancel = 1;
            }
            foreach ($data['result'] as $contest) {
                if (!isset($contest_list[$contest['group_id']]) || empty($contest_list[$contest['group_id']])) {
                    $contest_list[$contest['group_id']] = array("group_id" => $contest['group_id'], "group_name" => $contest['group_name'], "contest_list" => array());
                }
                
                $contest['prize_distibution_detail'] = json_decode($contest['prize_distibution_detail']);
                $contest_list[$contest['group_id']]['contest_list'][] = $contest;
            }
        }
        $contest_list = array_values($contest_list);
        //.print_r($data); die;
        if (!empty($data)) {
           // $this->api_response_arry['max_match_system_user'] = $this->get_pl_custom_data(1);
            $this->api_response_arry['collection_id'] = $post_data['collection_id'];
            $this->api_response_arry['show_cancel'] = $show_cancel;
            $this->api_response_arry['data'] = $contest_list;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response();
        } else {
            $this->api_response_arry['data'] = array();
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response();
        }
    }

	/**
	 * It is used to cancel contest
	 */
	public function cancel_contest_post() {
        $post_data = $this->post();
        $this->form_validation->set_rules('contest_unique_id', 'contest unique id', 'trim|required');
        $this->form_validation->set_rules('cancel_reason', 'reason', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        
        $this->load->helper('queue_helper');
        $post_data['action'] = 'cancel_game';
		add_data_in_queue($post_data, 'stock_game_cancel');        
        $this->api_response_arry['message'] = $this->lang->line('successfully_cancel_contest');
        $this->api_response();
    }

	/**
	 * It is used to delete contest
	 */
	public function delete_contest_post() {		
		$this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
		}
		
		$data = $this->input->post();
		$this->load->model('admin/Contest_model');
		$contest = $this->Contest_model->delete_contest($data); 
		
		if($contest) {	
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['message']			= $this->lang->line('delete_contest');
			$this->api_response();
		} else {
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $this->lang->line('no_change');
			$this->api_response();
		}
	}

	public function get_game_detail_post()
	{
		$this->form_validation->set_rules('contest_unique_id', 'contest unique id', 'trim');
		$this->form_validation->set_rules('contest_id', 'contest id', 'trim');
        
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

		$data_arr = $this->input->post();
		if(empty($data_arr['contest_id']) && empty($data_arr['contest_unique_id']))
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Please enter a valid contest ID.";
            $this->api_response_arry['service_name'] = "get_game_detail";
            $this->api_response();
		}
		
		$match_list = array();
		$this->load->model('admin/Contest_model');
		$contest_detail = $this->Contest_model->get_game_detail($data_arr);
		
		if($contest_detail)
		{
            $contest_detail['prize_distibution_detail']	= json_decode($contest_detail['prize_distibution_detail']);
			//print_r($contest_detail); die;
			
			
			if($contest_detail['user_id'])
			{
				$this->load->model('user/User_model');
				$user_data = $this->User_model->get_user_detail_by_user_id($contest_detail['user_id']);
			}else{
				$user_data['user_name'] = '';
            }

			$all_position['data'] = array();
            $post_api_response['contest_detail']		=$contest_detail;
			
			$post_api_response['user_data']				=$user_data;
			
			$this->load->model(array('Merchandise_model'));
			$post_api_response['merchandise_list'] = $this->Merchandise_model->get_merchandise_list();

			$this->api_response_arry['data']			= $post_api_response;
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response();
		}

		$this->api_response_arry = $contest_detail;
		$this->api_response();
	}

	public function get_game_lineup_detail_post()
	{

		$this->form_validation->set_rules('game_id', 'Game ID', 'trim|required');
		//$this->form_validation->set_rules('user_id', 'User ID', 'trim|required');
        
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
		$this->load->model('admin/Contest_model');
		$result = $this->Contest_model->get_lineup_by_game();
		foreach ($result['result'] as $key => $value) {
            $result['result'][$key]['prize_data'] = json_decode($value['prize_data'],TRUE);
			$result['result'][$key]['winning_amount'] = isset($value['won_amount']) ? $value['won_amount'] : 0;
		}
		$this->api_response_arry['data']			= $result;
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response();
	}

    private function get_rendered_stock_array($arr,$type)
	{
		$tmp = array();
		foreach($arr as $stock_id => $score)	{
			$tmp[] = array('stock_id' => $stock_id,'score' => $score,'type' => $type );
	  }
	  return $tmp;
	}

	public function get_lineup_detail_post()
	{
		$lineup_master_contest_id = $this->input->post('lineup_master_contest_id');
		
		$post_params = $this->input->post();
		$data_arr = $this->input->post();
		
		$this->load->model('Contest_model');
		$contest_info = $this->Contest_model->get_contest_collection_details_by_lmc_id($lineup_master_contest_id,"CM.collection_id,LM.lineup_master_id,CM.scheduled_date,C.status,CM.is_lineup_processed,LMC.total_score,LMC.game_rank,LM.user_name,LM.team_name,LM.team_data,LMC.prize_data,LMC.is_winner,CM.published_date,CM.end_date,CM.stock_type,C.contest_id,LMC.percent_change");
		
		if(empty($contest_info)){
        	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "No details found.";
            $this->api_response_arry['service_name'] = "get_admin_lineup_detail";
            $this->api_response();
        }

        $team_data = json_decode($contest_info['team_data'],TRUE);
       /* $collection_player_cache_key = "st_collection_stocks_" . $contest_info['collection_id'];
        $stocks_list = $this->get_cache_data($collection_player_cache_key);
        if (!$stocks_list) { */
            $post_data['collection_id'] = $contest_info['collection_id'];
            $post_data['published_date'] = $contest_info['published_date'];
			$post_data['scheduled_date'] = $contest_info['scheduled_date'];
            $post_data['end_date'] = $contest_info['end_date'];
            $stocks_list = $this->Contest_model->get_all_stocks($post_data);
            //set collection players in cache for 2 days
         /*   $this->set_cache_data($collection_player_cache_key, $stocks_list, REDIS_2_DAYS);
        }*/
        $final_stock_list = array();

        $stock_list_array = array_column($stocks_list, NULL, 'stock_id');
        if($contest_info['stock_type'] == 3){
             $post_data = $this->input->post();
          
                $contest_info['total_score'] = $contest_info['percent_change'];
                $result = $this->Contest_model->get_lineup_score_calculation_prediction($contest_info);
                
                if(!empty($result))
                {   foreach ($result as $key=> $stock_data) {
                        $accuracy_percent = !empty($stock_data['accuracy_percent']) ? $stock_data['accuracy_percent'] : '0.00';
                        $open_price  = !empty($stock_data['open_price']) ? $stock_data['open_price'] : '0.00';
                        $close_price = !empty($stock_data['close_price']) ? $stock_data['close_price'] : '0.00';
                        $user_price  = !empty($stock_data['user_price']) ? $stock_data['user_price'] : '0.00';

                        $lineup = array();
                        $lineup['stock_id'] = $stock_data['stock_id'];
                        $lineup['stock_name'] = $stock_data['name'];
                        $lineup['logo'] = $stock_data['logo'];
                        $lineup['display_name'] = $stock_data['display_name'];
                        $lineup['accuracy_percent'] = $accuracy_percent;
                        $lineup['open_price'] = $open_price;
                        $lineup['close_price'] = $close_price;
                        $lineup['user_price'] = $user_price;
                        $final_stock_list[] = $lineup;
                     
                    }              

                }else{
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['data'] = [];
                    $this->api_response_arry['message'] = 'team detail not found';
                    $this->api_response();
                }

        }else{

            if($contest_info['is_lineup_processed'] == "1"){
                $lineup_details = $this->Contest_model->get_lineup_with_score($lineup_master_contest_id, $contest_info);
                $team_data['pl'] = array_column($lineup_details,NULL,"stock_id");
            }else if(in_array($contest_info['is_lineup_processed'],array("2","3"))){
                $completed_team = $this->Contest_model->get_single_row("collection_id,lineup_master_id,team_data",COMPLETED_TEAM, array("collection_id" => $contest_info['collection_id'], "lineup_master_id" => $contest_info['lineup_master_id']));
                $team_data = json_decode($completed_team['team_data'],TRUE);

    			$team_data['pl'] = $this->get_rendered_stock_array($team_data['b'],1);
    			$sell_data = $this->get_rendered_stock_array($team_data['s'],2);
    			$team_data['pl'] = array_merge($team_data['pl'],$sell_data);
    			$team_data['pl'] = array_column($team_data['pl'],NULL,"stock_id");
    			
            }else{

    			$team_data['b'] = array_fill_keys($team_data['b'],"0");
    			$team_data['s'] = array_fill_keys($team_data['s'],"0");

    			$team_data['pl'] = $this->get_rendered_stock_array($team_data['b'],1);
    			$sell_data = $this->get_rendered_stock_array($team_data['s'],2);
    			$team_data['pl'] = array_merge($team_data['pl'],$sell_data);
    			$team_data['pl'] = array_column($team_data['pl'],NULL,"stock_id");
    			
            }


       
            if(!empty($team_data['pl'])){
                foreach ($team_data['pl'] as $stock_id=> $stock_data) {
                    $stock_info = $stock_list_array[$stock_id];
                    if(!empty($stock_info)) {
                        $captain = 0;
                        if($stock_id == $team_data['c_id']){
                            $captain = 1;
                        }
                        $lineup = array();
                        $lineup['stock_id'] = $stock_info['stock_id'];
                        $lineup['stock_name'] = $stock_info['stock_name'];
                        $lineup['logo'] = $stock_info['logo'];
                        $lineup['lot_size'] = $stock_info['lot_size'];
                        $lineup['display_name'] = $stock_info['display_name'];
                        $lineup['captain'] = $captain;
                        $lineup['score'] = $stock_data['score'];
                        $lineup['type'] = $stock_data['type'];
                        $lineup['joining_rate'] = $stock_info['joining_rate'];
                        $lineup['result_rate'] = $stock_info['result_rate'];
                        $lineup['closing_rate'] = $stock_info['closing_rate'];
                        $final_stock_list[] = $lineup;
                    }
                }
            }else{
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->contest_lang['team_detail_not_found'];
                $this->api_response();
            }
        }
      
		
        $prize_data = json_decode($contest_info['prize_data'],TRUE);
		$lineup_result_temp = array('prize_data'=>$prize_data,'game_rank'=>$contest_info['game_rank'],'lineup_master_id'=>$contest_info['lineup_master_id'],'score'=>$contest_info['total_score'],"user_name"=>$contest_info['user_name'],"team_name"=>$contest_info['team_name'],"is_winner"=>$contest_info['is_winner'],"lineup"=>array(),"stock_type"=>$contest_info['stock_type']);
		$lineup_result_temp['lineup'] = $final_stock_list;

		$this->api_response_arry['data'] = $lineup_result_temp;
		$this->api_response();		
	}

	 /**
     * get gamestats of specific user for ploting graph 
     * @param  $user_id
     * @return string
     */
    public function get_game_stats_post() {
        //validation for user id 
        $this->form_validation->set_rules('user_id', 'User id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
		$this->load->model('admin/Contest_model');
        $contest_joined = $this->Contest_model->get_contest_joined();
        $contest_won 	= $this->Contest_model->get_contest_won();
        $freee_paid 	= $this->Contest_model->get_free_paid();
        $this->api_response_arry['data'] = array('contest_joined' => $contest_joined[0]->contest_joined, 'contest_won' => $contest_won[0]->contest_won, 'free_paid' => $freee_paid);
        $this->api_response();
    }

	public function get_promo_code_detail_post()
	{
        $this->load->model("Contest_model");
		$result = $this->Contest_model->get_promo_code_detail();
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= '';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	}

	public function mark_pin_contest_post()
	{
		$this->form_validation->set_rules('contest_id', 'Contest Id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
		$post_data = $this->input->post();
	
		$this->load->model('Contest_model');
		$contest = $this->Contest_model->mark_pin_contest($post_data);
		//delete user profile infor from cache
		$col_cache_key = "st_collection_pin_contest_".$post_data['collection_id'];
		$this->delete_cache_data($col_cache_key);

		
		$col_con_cache_key = "st_collection_contest_".$post_data['collection_id'];
		$this->delete_cache_data($col_con_cache_key);

		

		if($contest)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['message']			= "Contest mark pin successfully.";
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $this->lang->line('no_change');
			$this->api_response();
		}
	}


	public function get_report_post()
    {
     
        // if(!isset($_REQUEST['from_date']))
        // {
        //     $_REQUEST['from_date']= format_date('today','Y-m-d');
        // }
        // if(!isset($_REQUEST['to_date']))
        // {
        //     $_REQUEST['to_date']= format_date('today','Y-m-d');
        // }
        // // converting from clientzone to utc
		// $this->_get_client_dates('Y-m-d H:i:s',1);

		$filters = $_REQUEST;
		$filters["csv"] = FALSE;
		// $this->load->model('Cron_model');
		$this->load->model('admin/Contest_model');
		$result	= $this->Contest_model->get_completed_contest_data();

      
		// $this->load->model('admin/Contest_model');
		// $result	= $this->Contest_model->get_completed_contest_report($filters);
		$contest_detail = $result['result'];
		$total_contest = $result['total'];

        $result_array = array();
		$contest_prize_detail = array();
		if(!empty($contest_detail)) {
			$contest_ids =  array_column($contest_detail, 'contest_id');
			$temp_prize_detail = $this->Contest_model->get_contest_prize_detail($contest_ids);

			$contest_prize_detail = array_column($temp_prize_detail,NULL,'contest_id');
			$contest_unique_ids =  array_column($contest_detail, 'contest_unique_id');
			$promocode_entry_result = $this->Contest_model->get_contest_promo_code_entry($contest_unique_ids);

			$promocode_entry = array();
			if(!empty($promocode_entry_result)) {
				$promocode_entry = array_column($promocode_entry_result,'promocode_entry_fee_real','contest_unique_id');
			}

			$result_array["sum_join_real_amount"] = 0;
			$result_array["sum_join_bonus_amount"] = 0;
			$result_array["sum_join_winning_amount"] = 0;
			$result_array["sum_join_coin_amount"]=0;
			$result_array["sum_win_amount"] = 0;
			$result_array["sum_total_entery_fee"]=0;
			$result_array["sum_profit_loss"] = 0;
			$result_array["sum_entry_fee"] = 0;
			$result_array["sum_site_rake"] = 0;
			$result_array["sum_min"] = 0;
			$result_array["sum_max"] = 0;
			$result_array["sum_total_user_joined"] = 0;

			//$result_array["sum_system_teams"] = 0;
			//$result_array["sum_real_teams"] = 0;

			$result_array["sum_max_bonus_allowed"] = 0;
			$result_array["sum_prize_pool"] = 0;
			$result_array["sum_total_entry_fee"] = 0;

			$result_array["sum_total_entry_fee_real"] = 0;
			//$result_array["sum_botuser_total_real_entry_fee"] = 0;

			$result_array["sum_promocode_entry_fee_real"] = 0;
			$result_array["sum_total_win_coins"] = 0;
			$result_array["sum_total_win_bonus"] = 0;

			$result_array["sum_total_win_amount_to_real_user"] = 0;

			foreach($contest_detail as &$contest) {
				$contest['siterake_amount'] = number_format((($contest['prize_pool']*$contest['site_rake'])/100),2,'.',',');
                $contest["total_join_real_amount"] = 0;
                $contest["total_win_winning_amount"] = 0;
                $contest["total_join_winning_amount"] = 0;
                $contest["total_join_bonus_amount"] = 0;
                $contest["total_join_coin_amount"] = 0;
                $contest["total_win_coins"] = 0;
                $contest["total_win_bonus"] = 0;
                $contest["total_win_amount_to_real_user"] = 0;                
                
				if(isset($contest_prize_detail[$contest['contest_id']])) {
					$result_array["sum_total_entry_fee_real"]+=$contest_prize_detail[$contest['contest_id']]['total_join_real_amount'];
					$contest = array_merge($contest, $contest_prize_detail[$contest['contest_id']]);
				}
				
				$contest["profit_loss"]	= number_format(($contest["total_join_real_amount"] - $contest["total_win_winning_amount"]),2,'.','');
				//$contest["total_entry_fee"]		= number_format(($contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"] + $contest["total_join_coin_amount"]),2,'.',',');
				
				$contest["promocode_entry_fee_real"] = 0;
				if(isset($promocode_entry[$contest['contest_unique_id']]))
				{
					$contest["promocode_entry_fee_real"] = $promocode_entry[$contest['contest_unique_id']];
					$result_array["sum_promocode_entry_fee_real"] += $contest["promocode_entry_fee_real"];
				}
				$result_array["sum_join_real_amount"] += $contest["total_join_real_amount"];
				$result_array["sum_join_winning_amount"] += $contest["total_join_winning_amount"];
				$result_array["sum_join_bonus_amount"] += $contest["total_join_bonus_amount"];
				$result_array["sum_join_coin_amount"] += $contest["total_join_coin_amount"];
				$result_array["sum_win_amount"] += $contest["total_win_winning_amount"];
				$result_array["sum_total_win_coins"] += $contest["total_win_coins"];
				$result_array["sum_total_win_bonus"] += $contest["total_win_bonus"];
				$result_array["sum_total_entery_fee"] += ($contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_coin_amount"]);
				$result_array["sum_profit_loss"] += $contest["profit_loss"]; 
				$result_array["sum_entry_fee"] += $contest["total_entry_fee"]; 
				$result_array["sum_site_rake"] += $contest["site_rake"]; 
				$result_array["sum_min"] += $contest["minimum_size"]; 
				$result_array["sum_max"] += $contest["size"]; 
				$result_array["sum_total_user_joined"] += $contest["total_user_joined"]; 
				//$result_array["sum_system_teams"] += $contest["system_teams"]; 
				//$result_array["sum_real_teams"] += $contest["real_teams"]; 
				$result_array["sum_max_bonus_allowed"] += $contest["max_bonus_allowed"]; 
				$result_array["sum_prize_pool"] += $contest["prize_pool"]; 
				$result_array["sum_total_entry_fee"] += $contest["total_entry_fee"]; 
				//$result_array["sum_botuser_total_real_entry_fee"] += $contest["botuser_total_real_entry_fee"]; 
				$result_array["sum_total_win_amount_to_real_user"] += $contest["total_win_amount_to_real_user"]; 
				$result_array["result"][] = $contest;

			}
			//unset($contest);
            $result_array["sum_join_real_amount"] = number_format($result_array["sum_join_real_amount"],2,'.',',');
            $result_array["sum_join_winning_amount"] = number_format($result_array["sum_join_winning_amount"],2,'.',',');
            $result_array["sum_join_bonus_amount"] = number_format($result_array["sum_join_bonus_amount"],2,'.',',');
            $result_array["sum_join_coin_amount"] = number_format($result_array["sum_join_coin_amount"],2,'.',',');
            $result_array["sum_win_amount"] = number_format($result_array["sum_win_amount"],2,'.',',');
            $result_array["sum_total_entery_fee"] = number_format($result_array["sum_total_entery_fee"],2,'.',',');
            $result_array["sum_profit_loss"] = number_format($result_array["sum_profit_loss"],2,'.',',');

			$result_array["total"] = $total_contest;
		}
		//$this->response(array(config_item('rest_status_field_name')=>TRUE, 'data'=>$result_array) , rest_controller::HTTP_OK);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $result_array;
		$this->api_response();	
    }


	public function contest_report_csv_get()
	{
		$_POST = $this->input->get();

		$this->load->model('admin/Contest_model');

		$result	= $this->Contest_model->get_completed_contest_data($_POST);
						
			if(!empty($result['result'])){
				$result =$result['result'];
				$header = array_keys($result[0]);
				$camelCaseHeader = array_map("camelCaseString", $header);
				$result = array_merge(array($camelCaseHeader),$result);
				$this->load->helper('download');
                                $this->load->helper('csv');
                                $data = array_to_csv($result);

                                //$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "From Date $from_date\nTo Date $to_date\n\n" . html_entity_decode($data);
                                $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
                                $name = 'Contest_report.csv';
								force_download($name, $data);
			}
			else{
				$result = "no record found";
				$this->load->helper('download');
				$this->load->helper('csv');
				$data = array_to_csv($result);
				$name = 'User_deposit_amount.csv';
				force_download($name, $result);

			}
			
	}

	public function test_post()
	{
		// print_r($_POST); die;
	}
}