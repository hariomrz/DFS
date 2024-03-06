<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Report extends Common_Api_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Report_model');
        $_POST = $this->post();

        $allow_livefantasy =  isset($this->app_config['allow_livefantasy'])?$this->app_config['allow_livefantasy']['key_value']:0;
        // if($allow_livefantasy == 0)
        // {
        //     $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        //     $this->api_response_arry['message'] = "Live fantasy not enabled";
        //     $this->api_response_arry['global_error'] = "Module Disable";
        //     $this->api_response();
        // }
	}

    /**
     * @param user_id, source
     * @response array();
     */
    public function get_report_money_paid_by_user_post()
	{
		$result = $this->Report_model->get_report_money_paid_by_user();
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= '';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	}

        /**
     * @param user_id, source
     * @response array();
     */
	public function get_report_money_paid_by_user_get()
	{
		$_POST =  $this->input->get();
		$result = $this->Report_model->get_report_money_paid_by_user();
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
                                $name = 'User_money_paid.csv';
                                force_download($name, $data);
			}
			else{
				$result = "no record found";
				$this->load->helper('download');
				$this->load->helper('csv');
				$data = array_to_csv($result);
				$name = 'User_money_paid.csv';
				force_download($name, $result);
			}
	}

	public function get_all_contest_report_post()
	{	
		$filters = $this->input->post();
		$filters["csv"] = FALSE;
		$this->load->model('Report_model');
		$post_api_response	= $this->Report_model->get_completed_contest_report($filters);
		$result_array = array();
		$prize_detail = array();
		$contest_detail = $post_api_response['result'];
		$total_contest = $post_api_response['total'];

		$contest_prize_detail = array();
		if(!empty($contest_detail))
		{
			$contest_ids =  array_column($post_api_response['result'], 'contest_id');
			$temp_prize_detail = $this->Report_model->get_contest_prize_detail($contest_ids);
			// print_r($temp_prize_detail);exit;
			$contest_prize_detail = array_column($temp_prize_detail,NULL,'contest_id');
			$contest_unique_ids =  array_column($post_api_response['result'], 'contest_unique_id');
			$promocode_entry_result = $this->Report_model->get_contest_promo_code_entry($contest_unique_ids);
			// print_r($promocode_entry_result);exit;
			$promocode_entry = array();
			if(!empty($promocode_entry_result))
			{
				$promocode_entry = array_column($promocode_entry_result,'promocode_entry_fee_real','contest_unique_id');
			}

			$result_array["sum_join_real_amount"] = 0;
			$result_array["sum_join_bonus_amount"] = 0;
			$result_array["sum_join_winning_amount"] = 0;
			$result_array["sum_join_coin_amount"]=0;
			$result_array["sum_win_amount"] = 0;
			// $result_array["sum_total_entery_fee"]=0;
			$result_array["sum_profit_loss"] = 0;
			$result_array["sum_entry_fee"] = 0;
			$result_array["sum_site_rake"] = 0;
			$result_array["sum_min"] = 0;
			$result_array["sum_max"] = 0;
			$result_array["sum_total_user_joined"] = 0;
			$result_array["sum_system_teams"] = 0;
			$result_array["sum_real_teams"] = 0;
			$result_array["sum_max_bonus_allowed"] = 0;
			$result_array["sum_prize_pool"] = 0;
			$result_array["sum_total_entry_fee"] = 0;
			$result_array["sum_total_entry_fee_real"] = 0;
			$result_array["sum_botuser_total_real_entry_fee"] = 0;
			$result_array["sum_promocode_entry_fee_real"] = 0;
			$result_array["sum_total_win_coins"] = 0;
			$result_array["sum_total_win_bonus"] = 0;
			$result_array["sum_total_win_amount_to_real_user"] = 0;

			foreach($contest_detail as &$contest)
			{
				// print_r($contest);exit;
				$contest['siterake_amount'] = number_format((($contest['prize_pool']*$contest['site_rake'])/100),2,'.',',');

				if(isset($contest_prize_detail[$contest['contest_id']]))
				{
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
				$result_array["sum_system_teams"] += $contest["system_teams"]; 
				$result_array["sum_real_teams"] += $contest["real_teams"]; 
				$result_array["sum_max_bonus_allowed"] += $contest["max_bonus_allowed"]; 
				$result_array["sum_prize_pool"] += $contest["prize_pool"]; 
				$result_array["sum_total_entry_fee"] += $contest["total_entry_fee"]; 
				$result_array["sum_botuser_total_real_entry_fee"] += $contest["botuser_total_real_entry_fee"]; 
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
		// print_r($result_array);exit;
		//$this->response(array(config_item('rest_status_field_name')=>TRUE, 'data'=>$result_array) , rest_controller::HTTP_OK);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= '';
		$this->api_response_arry['data']			= $result_array;
		$this->api_response();	
	}

	public function contest_report_csv_get()
	{
		$_POST = $this->input->get();

		$filters = $_POST;
		$filters["csv"] = TRUE;
		// print_r($filters);exit;
		$this->load->model('Report_model');
		$post_api_response	= $this->Report_model->get_completed_contest_report($filters);
		$result_array = array();
		$prize_detail = array();
		$contest_detail = $post_api_response['result'];
		$html_string_f ="<!DOCTYPE html>
		<html>
		<head>
			<title></title>
		</head>
		<body>
			<h1>No Record</h1>
		</body>
		</html>";
		if(!empty($contest_detail))
		{
			$contest_ids =  array_column($post_api_response['result'], 'contest_id');
			$temp_prize_detail = $this->Report_model->get_contest_prize_detail($contest_ids);
			$contest_prize_detail = array_column($temp_prize_detail,NULL,'contest_id');
			
			$contest_unique_ids =  array_column($post_api_response['result'], 'contest_unique_id');
			$promocode_entry_result = $this->Report_model->get_contest_promo_code_entry($contest_unique_ids);

			$promocode_entry = array();
			if(!empty($promocode_entry_result))
			{
				$promocode_entry = array_column($promocode_entry_result,'promocode_entry_fee_real','contest_unique_id');
			}

			$result_array["sum_join_real_amount"] = 0;
			$result_array["sum_join_bonus_amount"] = 0;
			$result_array["sum_join_winning_amount"] = 0;
			$result_array["sum_join_coin_amount"]=0;
			$result_array["sum_win_amount"] = 0;
			//$result_array["sum_total_entery_fee"]=0;
			$result_array["sum_profit_loss"] = 0;
			$result_array["sum_entry_fee"] = 0;
			$result_array["sum_site_rake"] = 0;
			$result_array["sum_min"] = 0;
			$result_array["sum_max"] = 0;
			$result_array["sum_total_user_joined"] = 0;
			$result_array["sum_system_teams"] = 0;
			$result_array["sum_real_teams"] = 0;
			$result_array["sum_max_bonus_allowed"] = 0;
			$result_array["sum_prize_pool"] = 0;
			$result_array["sum_total_entry_fee"] = 0;
			$result_array["sum_total_entry_fee_real"] = 0;
			$result_array["sum_botuser_total_real_entry_fee"] = 0;
			$result_array["sum_promocode_entry_fee_real"] = 0;
			$result_array["sum_total_win_coins"] = 0;
			$result_array["sum_total_win_bonus"] = 0;

		

			$html_string_f = "<table>";
			$html_string_f.="
						<thead><tr>
						<th >Match</th>
						<th >Contest Category</th>
						<th >Contest Name</th>
						<th >Total Join</th>
						<th >Min</th>
						<th >Max</th>		
						<th >Entry Fees</th>
						<th >Site Rake (%)</th>						
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
						</tr></thead>
						<tbody>
						";
			foreach($contest_detail as $contest)
			{
				if(isset($contest_prize_detail[$contest['contest_id']]))
				{
					$result_array["sum_total_entry_fee_real"]+=$contest_prize_detail[$contest['contest_id']]['total_join_real_amount'];
					$contest = array_merge($contest, $contest_prize_detail[$contest['contest_id']]);
				}
				$contest["profit_loss"]	= number_format((($contest["total_join_real_amount"]+$contest["total_join_winning_amount"]) - $contest["total_win_winning_amount"]),2,'.','');
				//$contest["total_entry_fee"]		= number_format(($contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"] + $contest["total_join_coin_amount"]),2,'.',',');
				
				if(isset($promocode_entry[$contest['contest_unique_id']]))
				{
					$contest["promocode_entry_fee_real"] = $promocode_entry[$contest['contest_unique_id']];
					$result_array["sum_promocode_entry_fee_real"] += $contest["promocode_entry_fee_real"];
				}

				$contest["total_entry_fee"]		= $contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"];
				
				$result_array["sum_join_real_amount"] += $contest["total_join_real_amount"];
				$result_array["sum_join_winning_amount"] += $contest["total_join_winning_amount"];
				$result_array["sum_join_bonus_amount"] += $contest["total_join_bonus_amount"];
				$result_array["sum_join_coin_amount"] += $contest["total_join_coin_amount"];
				$result_array["sum_win_amount"] += $contest["total_win_winning_amount"];
				$result_array["sum_total_win_coins"] += $contest["total_win_coins"];
				$result_array["sum_total_win_bonus"] += $contest["total_win_bonus"];
				$result_array["sum_total_entery_fee"] += ($contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"] + $contest["total_join_coin_amount"]);
				$result_array["sum_profit_loss"] += $contest["profit_loss"]; 
				$result_array["sum_entry_fee"] += $contest["entry_fee"]; 
				$result_array["sum_site_rake"] += $contest["site_rake"]; 
				$result_array["sum_min"] += $contest["minimum_size"]; 
				$result_array["sum_max"] += $contest["size"]; 
				$result_array["sum_total_user_joined"] += $contest["total_user_joined"]; 
				$result_array["sum_system_teams"] += $contest["system_teams"]; 
				$result_array["sum_real_teams"] += $contest["real_teams"]; 
				$result_array["sum_max_bonus_allowed"] += $contest["max_bonus_allowed"]; 
				$result_array["sum_prize_pool"] += $contest["prize_pool"]; 
				$result_array["sum_total_entry_fee"] += $contest["total_entry_fee"]; 
				$result_array["sum_botuser_total_real_entry_fee"] += $contest["botuser_total_real_entry_fee"]; 
				// $result_array["result"][] = $contest;

				$html_string_f.="<tr>";
				$html_string_f.="<td>".$contest["collection_name"]."</td>";
				$html_string_f.="<td>".$contest["group_name"]."</td>";
				$html_string_f.="<td>".$contest["contest_name"]."</td>";
				$html_string_f.="<td>".$contest["total_user_joined"]."</td>";
				$html_string_f.="<td>".$contest["minimum_size"]."</td>";
				$html_string_f.="<td>".$contest["size"]."</td>";
				$html_string_f.="<td>".$contest["entry_fee"]."</td>";
				$html_string_f.="<td>".$contest["site_rake"]."</td>";
				$html_string_f.="<td>".$contest["max_bonus_allowed"]."</td>";
				$html_string_f.="<td>".$contest["prize_pool"]."</td>";
				$html_string_f.="<td>".$contest["total_entry_fee"]."</td>";
				$html_string_f.="<td>".$contest["total_join_real_amount"]."</td>";
				$html_string_f.="<td>".$contest["total_join_bonus_amount"]."</td>";
				$html_string_f.="<td>".$contest["promocode_entry_fee_real"]."</td>";
				$html_string_f.="<td>".$contest["total_win_winning_amount"]."</td>";
				$html_string_f.="<td>".$contest["total_win_bonus"]."</td>";
				$html_string_f.="<td>".$contest["total_win_coins"]."</td>";
				$html_string_f.="<td>".$contest["total_win_winning_amount"]."</td>";
				$html_string_f.="<td>".$contest["profit_loss"]."</td>";
				$html_string_f.="</tr>";
			}
			$html_string_f.="<tr>";
	
			$html_string_f.="<td>&nbsp;</td>";
			$html_string_f.="<td>&nbsp;</td>";
			$html_string_f.="<td>&nbsp;</td>";
			$html_string_f.="<td><b>".$result_array["sum_total_user_joined"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_min"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_max"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_entry_fee"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_site_rake"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_max_bonus_allowed"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_prize_pool"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_total_entry_fee"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_join_real_amount"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_join_bonus_amount"]."</b></td>";
			$html_string_f.="<td>&nbsp;</td>";
			$html_string_f.="<td><b>".$result_array["sum_win_amount"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_total_win_bonus"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_total_win_coins"]."</b></td>";
			$html_string_f.="<td>&nbsp;</td>";
			$html_string_f.="<td><b>".$result_array["sum_profit_loss"]."</b></td>";
			$html_string_f.="</tr>";
			$html_string_f.="</body></table>";
		}
		$file='contest_report.xls';
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$file");
		echo $html_string_f;
		exit;
	}

	function export_report_post()
	{
        $this->form_validation->set_rules('report_type','report_type','trim|required');
 
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data  =  $this->post();
      	
      	$this->load->helper('queue_helper');
        add_data_in_queue($post_data,'admin_reports');

		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['message']			= 'Request submitted, you will receive an email shortly once processed';
		$this->api_response();
	}

	public function get_sport_leagues_post()
	{
		$post_params = array(
							'sports_id' => $this->input->post("sports_id"),
							 'admin_list_filter' => true, // get all leagues
							'active' => '1',
							);
							$_POST['admin_list_filter']=TRUE;
							$_POST['active']='1';
		$post_api_response = $this->Report_model->get_sport_all_recent_leagues($post_params);
		$formats = array();
		if($this->input->post("sports_id") ==7 )
		$formats = array("1"=>"One Day", "2"=>"Test", "3"=>"T20");

		$post_api_response['data']['formats'] = $formats;
	
		$this->api_response_arry['data'] = $post_api_response;
		
		$this->api_response();
		
	}

	public function get_conntest_filter_post(){ 
		$this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
		$post_data = $this->input->post();
		$league_list = $this->Report_model->get_sport_leagues();
		$post   = $this->post();
		$group_list = $this->Report_model->get_all_group_list($post);
		$status_list = array();
		$status_list[] = array("label"=>"Select Status","value"=>"");
		$status_list[] = array("label"=>"Current Contest","value"=>"current_game");
		$status_list[] = array("label"=>"Completed Contest","value"=>"completed_game");
		$status_list[] = array("label"=>"Cancelled Contest","value"=>"cancelled_game");
		$status_list[] = array("label"=>"Upcoming Contest","value"=>"upcoming_game");


		//contest type array
		$contest_type_list = array();
		$contest_type_list[] = array("label"=> "All","value"=>"");
		$contest_type_list[] = array("label"=> $this->lang->line('classic'),"value" => 0);

		// if($this->get_app_config_value('allow_reverse_contest') ==1 )
		// {
		// 	$contest_type_list[] = array("label"=> $this->lang->line('reverse'),"value" => 1);
		// }

		// if($this->get_app_config_value('allow_2nd_inning') ==1)
		// {
		// 	$contest_type_list[] = array("label"=> $this->lang->line('2nd_inning'),"value"=>2);
		// }

		$result = array(
					'league_list'		=> isset($league_list['result']) ? $league_list['result'] : array(),
					'group_list'		=> $group_list,
					'status_list'		=> $status_list,
					'contest_type_list' => $contest_type_list
				);

		$this->api_response_arry['data']          = $result;
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response();
	}

	/**
	 * geting completed overs for contest report fixture list dropdown
	 * @param collection_type, league_id, sports_id
	 * @return array
	 */
	public function get_all_collections_by_league_post()
	{
		$game_type         = 'completed_game';
		if(!empty($this->input->post("game_type"))){
			$game_type     = $this->input->post("game_type");
		}
		if(!empty($this->input->post("collection_type"))){
			$collection_type     = $this->input->post("collection_type");
		}
        $post_params 	   = array(
                            	'league_id' 				 => $this->input->post("league_id"),
                            	'sports_id' 				 => $this->input->post("sports_id"),
                            	'fetch_only_completed'		 => $game_type,
                            	'fetch_completed_collection' => $collection_type
                             );

        //$post_api_response       = $this->http_post_request($post_target_url,$post_params,2);
		$post_api_response = $this->Report_model->get_collections_by_filter($post_params);
		$this->api_response_arry['data'] = $post_api_response;
		$this->api_response();
	}


}