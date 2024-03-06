<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'/libraries/REST_Controller.php';

class Report extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();
		$_POST				= $this->input->post();
		$this->load->model('Report_model');
		$this->admin_lang = $this->lang->line('promo_code');
		//Do your magic here
		$this->admin_roles_manage($this->admin_id,'report');
	}

	public function get_report_money_paid_by_user_post()
	{
		$result = $this->Report_model->get_report_money_paid_by_user();
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= '';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	}

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

	public function get_all_user_report_get(){
		$_POST =  $this->input->get();

		$_POST["csv"] = TRUE;
		$result = array();
		$revenew_result = array();		// print_r(); die;
		$total = 0;
		$userData = $this->Report_model->get_all_user_report();

		if(!empty($userData))
		{
			foreach ($userData["result"] as $key => $value) 
			{
				$userData["result"][$key]['match_lost'] = $value['match_played'] - $value['match_won'];
				$userData["result"][$key]['deposit_by_user'] = number_format($value['deposit_by_user'], 2, '.', ',');
				$userData["result"][$key]['deposit_by_admin'] = number_format($value['deposit_by_admin'], 2, '.', ',');
				$userData["result"][$key]['withdraw_by_user'] = number_format($value['withdraw_by_user'], 2, '.', ',');
				$userData["result"][$key]['winning_balance'] = number_format($value['winning_balance'], 2, '.', ',');
				$userData["result"][$key]['balance'] = number_format($value['balance'], 2, '.', ',');
				$userData["result"][$key]['bonus_balance'] = number_format($value['bonus_balance'], 2, '.', ',');
				$userData["result"][$key]['prize_amount_won'] = number_format($value['prize_amount_won'], 2, '.', ',');
				$userData["result"][$key]['prize_amount_lost'] = number_format($value['entry_fee_paid'] - $value['prize_amount_won'], 2, '.', ',');
			}
		}
		$result = $userData["result"];
		$header = array_keys($result[0]);
        $camelCaseHeader = array_map("camelCaseString", $header);
        $result = array_merge(array($camelCaseHeader),$result);
		 
		$this->load->helper('download');
		$this->load->helper('csv');
		$data = array_to_csv($result);

		//$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "From Date $from_date\nTo Date $to_date\n\n" . html_entity_decode($data);
        $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
		$name = 'User_report.csv';
		force_download($name, $data);

	}

	public function get_all_user_report_post()
	{
		$post_data = $this->input->post();
		$result = array();
        $total = 0;
		$userData = $this->Report_model->get_all_user_report();
		//echo "<pre>";print_r($userData);die;
        if(!empty($userData))
		{
			$user_ids = array_unique(array_column($userData['result'],"user_id"));
			$user_txn = array();
			if(!empty($user_ids)){
				$user_txn = $this->Report_model->get_user_txn_data($user_ids);
				$user_txn = array_column($user_txn,NULL,"user_id");
				//echo "<pre>";print_r($user_txn);die;
			}
        	$total = $userData["total"];
			foreach($userData["result"] as $key => &$value) 
			{
				if(isset($user_txn[$value['user_id']])){
                	$value = array_merge($value,$user_txn[$value['user_id']]);
                }
				$userData["result"][$key]['match_lost'] = $value['match_played'] - $value['match_won'];
				$userData["result"][$key]['deposit_by_user'] 	= number_format($value['deposit_by_user'], 2, '.', ',');
				$userData["result"][$key]['deposit_by_admin'] 	= number_format($value['deposit_by_admin'], 2, '.', ',');
				$userData["result"][$key]['withdraw_by_user'] 	= number_format($value['withdraw_by_user'], 2, '.', ',');
				$userData["result"][$key]['winning_balance'] 	= number_format($value['winning_balance'], 2, '.', ',');
                $userData["result"][$key]['balance'] 			= number_format($value['balance'], 2, '.', ',');
                $userData["result"][$key]['bonus_balance'] 	= number_format($value['bonus_balance'], 2, '.', ',');
                $userData["result"][$key]['prize_amount_won'] 	= number_format($value['total_win_amt'], 2, '.', ',');
                $userData["result"][$key]['prize_amount_lost'] = number_format((float)$value['total_entry_fee'] - (float)$value['total_win_amt'], 2, '.', ',');
			}
		}
		$result = $userData['result'];
        $this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
        $this->api_response_arry['status']		= TRUE;
        $this->api_response_arry['message']		= '';
        $this->api_response_arry['data']		= array('result'=>$result,'total'=>$total);
        $this->api_response();
	}

	public function get_report_user_deposit_amount_post()
	{
		$result = $this->Report_model->get_report_user_deposit_amount();
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= '';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	}

	public function get_report_user_deposit_amount_get()
	{
		$_POST = $this->input->get();
		//convert date to client timezone		

		$result = $this->Report_model->get_report_user_deposit_amount();
					
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
                                $name = 'User_deposit_amount.csv';
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

	public function get_deposit_amount_filter_data_post()
	{		
		$result = get_active_payment_list($this->app_config);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= '';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	}

	public function referal_report_post()
	{
		$result = $this->Report_model->get_referral_report();
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= '';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	}

	public function referral_friends_post()
	{
		$post_data = $this->input->post();
		$result = $this->Report_model->get_referral_friends($post_data['user_id']);		
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= '';
		$this->api_response_arry['data']			= $result;
		$this->api_response();

		
	}

	public function referal_report_get()
	{
		$_POST = $this->input->get();
		$result = $this->Report_model->get_referral_report();
			if(!empty($result['result'])){
				$result =$result['result'];
				$header = array_keys($result[0]);
				$camelCaseHeader = array_map("camelCaseString", $header);
				$result = array_merge(array($camelCaseHeader),$result);
				$this->load->helper('download');
                                $this->load->helper('csv');
                                $data = array_to_csv($result);
                                $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
                                $name = 'User_referral_report.csv';
								force_download($name, $data);
			}
			else{
				$result = "no record found";
				$this->load->helper('download');
				$this->load->helper('csv');
				$data = array_to_csv($result);
				$name = 'User_referral_report.csv';
				force_download($name, $result);
			}
	}

	public function get_all_contest_report_post_old()
	{	
		$filters = $this->input->post();
		$filters["csv"] = FALSE;
		$this->load->model('contest/Contest_model');
		$post_api_response	= $this->Contest_model->get_completed_contest_report($filters);
		$result_array = array();
		$prize_detail = array();
		$contest_detail = $post_api_response['result'];
		$total_contest = $post_api_response['total'];

		$contest_prize_detail = array();
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
			$result_array["sum_total_win_amount_to_real_user"] = 0;

			foreach($contest_detail as &$contest)
			{
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
				// $result_array["sum_total_entery_fee"] += ($contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_coin_amount"]);
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
				// $result_array["sum_total_entery_fee"] = number_format($result_array["sum_total_entery_fee"],2,'.',',');
				$result_array["sum_profit_loss"] = number_format($result_array["sum_profit_loss"],2,'.',',');

			$result_array["total"] = $total_contest;
		}
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

		// echo "<pre>";
		// print_r($_POST);die;

		// $filters["csv"] = TRUE;
		$this->load->model('contest/Contest_model');
		// $post_api_response	= $this->Contest_model->get_completed_contest_report($filters);
		$post_api_response	= $this->Report_model->get_complet_contest_report($_POST);
		//echo "<pre>";print_r($post_api_response);die;
		$prize_detail = array();
		$contest_detail = $post_api_response['result'];
		$html_string_f = "<!DOCTYPE html>
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

			

			$result_array = array("total_user_joined","system_teams","real_teams","prize_pool","total_entry_fee","total_join_real_amount","total_join_bonus_amount","promocode_entry_fee_real","botuser_total_real_entry_fee","total_win_winning_amount","total_win_bonus","total_win_coins","profit_loss");
			$result_array = array_fill_keys($result_array,0);

			$html_string_f = "<table>";
			$html_string_f.="
						<thead><tr>
						<th>Match</th>
						<th>Feature Type</th>
						<th>Contest Category</th>
						<th>Contest Name</th>
						<th>Collection Name</th>
						<th>Total Join</th>
						<th>Min</th>
						<th>Max</th>		
						<th>Entry Fees</th>
						<th>Site Rake (%)</th>						
						<th>Total Team Created</th>
						<th>Total Bot user</th>
						<th>Total Real user</th>
						<th>Bonus Allowed %</th>
						<th>Prize Pool</th>
						<th>Total Entry Fee</th>
						<th>Entry Fees(Real Money)</th>
						<th>Entry Fees(Bonus)</th>
						<th>Entry Fees(Promo Code)</th>
						<th>Bot User Entry(Real Money)</th>
						<th>Distribution(Real Money)</th>
						<th>Distribution(Bonus)</th>
						<th>Distribution(Coin)</th>
						<th>Total(Profit/Loss)</th>
						<th>Guaranteed Prize</th>
						<th>Total Win Prize</th> 
						
						</tr></thead>
						<tbody>
						";
			foreach($contest_detail as &$contest)
			{
				// $contest["total_join_real_amount"] = 0;
				// $contest["total_win_winning_amount"] = 0;
				// $contest["total_join_real_amount"] = 0;
				// $contest["total_join_winning_amount"] = 0;
				// $contest["total_join_bonus_amount"] = 0;
				// $contest["total_join_coin_amount"] = 0;
				// $contest["total_win_coins"] = 0;
				// $contest["total_win_bonus"] = 0;
				// $contest["total_entry_fee"]=0;
				// $contest["bot_entry_fee"]=0;
				
				$contest["total_win_amount_to_real_user"]=0;
				$contest['siterake_amount'] = number_format((($contest['prize_pool']*$contest['site_rake'])/100),2,'.',',');
				if(isset($contest_prize_detail[$contest['contest_id']]))
				{
					$contest = array_merge($contest, $contest_prize_detail[$contest['contest_id']]);
				}
				$contest["profit_loss"]	= number_format((($contest["total_join_real_amount"]+$contest["total_join_winning_amount"]) -($contest["bot_entry_fee"])- $contest["total_win_amount_to_real_user"]),2,'.','');

				$contest["promocode_entry_fee_real"] = 0;
				if(isset($promocode_entry[$contest['contest_unique_id']]))
				{
					$contest["promocode_entry_fee_real"] = $promocode_entry[$contest['contest_unique_id']];
				}

				$contest["total_entry_fee"] = $contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"];
                
				$contest["total_join_real_amount"] = $contest["total_join_real_amount"]+$contest["total_join_winning_amount"];

				
				$html_string_f.="<tr>";
				$html_string_f.="<td>".$contest["collection_name"]."</td>";
				$html_string_f.="<td>".$contest["feature_type"]."</td>";
				$html_string_f.="<td>".$contest["group_name"]."</td>";
				$html_string_f.="<td>".$contest["contest_name"]."</td>";
				$html_string_f.="<td>".$contest["collection_name"]."</td>";
				$html_string_f.="<td>".$contest["total_user_joined"]."</td>";
				$html_string_f.="<td>".$contest["minimum_size"]."</td>";
				$html_string_f.="<td>".$contest["size"]."</td>";
				$html_string_f.="<td>".$contest["entry_fee"]."</td>";
				$html_string_f.="<td>".$contest["site_rake"]."</td>";
				$html_string_f.="<td>".$contest["total_user_joined"]."</td>";
				$html_string_f.="<td>".$contest["system_teams"]."</td>";
				$html_string_f.="<td>".$contest["real_teams"]."</td>";
				$html_string_f.="<td>".$contest["max_bonus_allowed"]."</td>";
				$html_string_f.="<td>".$contest["prize_pool"]."</td>";
				$html_string_f.="<td>".$contest["total_entry_fee"]."</td>";
				$html_string_f.="<td>".$contest["total_join_real_amount"]."</td>";
				$html_string_f.="<td>".$contest["total_join_bonus_amount"]."</td>";
				$html_string_f.="<td>".$contest["promocode_entry_fee_real"]."</td>";
				$html_string_f.="<td>".$contest["bot_entry_fee"]."</td>";
				$html_string_f.="<td>".$contest["total_win_winning_amount"]."</td>";
				$html_string_f.="<td>".$contest["total_win_bonus"]."</td>";
				$html_string_f.="<td>".$contest["total_win_coins"]."</td>";
				$html_string_f.="<td>".$contest["profit_loss"]."</td>";
				$html_string_f.="<td>".$contest["guaranteed_prize"]."</td>";
				$html_string_f.="<td>".$contest["total_win_amount_to_real_user"]."</td>"; 
				// $html_string_f.="<td>".$contest["scheduled_date"]."</td>";
				$html_string_f.="</tr>";
			}
	
			$html_string_f.="</body></table>";
		}

		// echo "<pre>";
		// print_r($html_string_f);die;
		$file='contest_report.xls';
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$file");
		echo $html_string_f;
		exit;
	}

	/**
	 * Method to get match report from vi_match_report table
	 * @param sports id
	 * @return array
	 */

	public function get_match_report_post()
	{
	   $this->form_validation->set_rules('sports_id', 'Sports Id', 'trim|required');
	   if(!$this->form_validation->run())
	   {
		   $this->send_validation_errors();
	   }

	   try{
		   $filters = $this->input->post();
		   $data = $this->Report_model->get_match_report($filters);
		   if(!empty($data)){
		   $this->api_response_arry['message']				= 'get report successfully';
		   $this->api_response_arry['data']				= $data;
		   }else{
		   $this->api_response_arry['message']				= $this->admin_lang = $this->lang->line('not_found');
		   $this->api_response_arry['data']				= array();
		   }
		   
	   }catch(Exception $e)
	   {
		   $this->api_response_arry['global_error'] = "some error";
	   }
	   $this->api_response();

	}



	/**
	* Method to get exported match report from vi_match_report table
	* @param sports id
	* @return array
	*/

   public function get_match_report_get()
   {
	  try{
		  $_POST = $this->input->get();
		   $filters = $_POST;
		   
		  if(!isset($filters['csv'])){
		   $filters['csv']=true;
		   }
		  $final_data = $this->Report_model->get_match_report($filters);
		  
		  if(!empty($final_data)){
			   $result =$final_data;
			   $header = array_keys($result[0]);
			   $camelCaseHeader = array_map("camelCaseString", $header);
			   $result = array_merge(array($camelCaseHeader),$result);
			   $this->load->helper('download');
			   $this->load->helper('csv');
			   $data = array_to_csv($result);
			   $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
			   $name = 'Match_Report.csv';
			   force_download($name, $data);
		   }
		   else{
			   $result = "no record found";
			   $this->load->helper('download');
			   $this->load->helper('csv');
			   $data = array_to_csv($result);
			   $name = 'Match_Report.csv';
			   force_download($name, $result);
		   }
		  
	  }catch(Exception $e)
	  {
		  $this->api_response_arry['global_error'] = "some error";
		  $this->api_response();
	  }

   }

   public function export_referral_list_by_user_get(){
	$_POST = $this->input->get();
	if(empty($this->input->get('csv'))){
		$_POST['csv']=true;
	}
	
	$result = $this->Report_model->get_referral_friends($this->input->get('user_id'));
	if(!empty($result['referral_data'])){
		$new_result = array();
		foreach($result['referral_data'] as $key => $res){
			$new_result[$key]['friend_id'] 				=$res['friend_id'];			
			$new_result[$key]['first_name'] 			=$res['first_name'];		
			$new_result[$key]['last_name'] 				=$res['last_name'];			
			$new_result[$key]['user_name'] 				=$res['user_name'];			
			$new_result[$key]['registration_date'] 		=$res['added_date'];		
			$new_result[$key]['friend_email'] 			=$res['friend_email'];		
			$new_result[$key]['friend_bonus_cash'] 		=$res['friend_bonus_cash'];	
			$new_result[$key]['earned_bonus'] 			=$res['earned_bonus'];		
			$new_result[$key]['earned_real'] 			=$res['earned_real'];		
			$new_result[$key]['earned_coin'] 			=$res['earned_coin'];
			if($res['status']==1){
			$new_result[$key]['status'] 			="Joined";
		}else{
			$new_result[$key]['status'] 			="Not yet";
			}
		}
		$result =$new_result;
		$header = array_keys($result[0]);
		$camelCaseHeader = array_map("camelCaseString", $header);
		$result = array_merge(array($camelCaseHeader),$result);
		// print_r($result);exit;
		$this->load->helper('download');
						$this->load->helper('csv');
						$data = array_to_csv($result);

						$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
						$name = 'User_Referral_List.csv';
						force_download($name, $data);
	}
	else{
		$result = "no record found";
		$this->load->helper('download');
		$this->load->helper('csv');
		$data = array_to_csv($result);
		$name = 'User_Referral_List.csv';
		force_download($name, $result);

	}
}


	/** OLD METHODS NEED TO FILTER AKR */

	/**
	 * [userlocation_post description]
	 * Summary :- functions for user location section under reporting 
	 * @return [type] [description]
	 */
	public function userlocation_post()
	{
		if($this->input->post('csv'))
		{
			$result = $this->Report_model->get_all_userlocation_csv();

			if(!empty($result['result'])){
				$result =$result['result'];
				$header = array_keys($result[0]);
				$camelCaseHeader = array_map("camelCaseString", $header);
				$result = array_merge(array($camelCaseHeader),$result);
				$this->load->helper('csv');
				
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['status']			= TRUE;
				$this->api_response_arry['message']			= '';
				$this->api_response_arry['data']			= array_to_csv($result);
				$this->api_response();
			}
			
		}
		else
		{
			$result = $this->Report_model->get_all_userlocation();
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['status']			= TRUE;
			$this->api_response_arry['message']			= '';
			$this->api_response_arry['data']			= $result;
			$this->api_response();
		}		
	}
	
	/**
	 * [get_userlocation_filter_data_post description]
	 * Summary :- functions for user location section filter data under reporting  
	 * @return [type] [description]
	 */
	public function get_userlocation_filter_data_post()
	{
		$country = $this->Report_model->get_all_country();
		$state 	 = $this->Report_model->get_all_state();
		
		
		$result['country']		= $country;
		$result['state']		= $state;
		
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= '';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	}

	public function get_state_by_country_post()
	{
		//$this->load->model('User_model');
		$master_country_id = $this->input->post('master_country_id') ?: '';
		if($master_country_id!='')
		{
			$data['state'] = $this->Report_model->get_all_state_by_country($master_country_id);

			if($data)
			{
				$response 		= array(
								config_item('rest_status_field_name')=>TRUE,
								'data'	=> $data 
								);
				$this->response($response, rest_controller::HTTP_OK);
			}
			else
			{
				$response 		= array(
								config_item('rest_status_field_name')=>FALSE,
								'error'	=> 'State not found' 
								);
				$this->response($response, rest_controller::HTTP_INTERNAL_SERVER_ERROR);
			}
		}
		else
		{
			$response 		= array(
								config_item('rest_status_field_name')=>FALSE,
								'error'	=> 'Please select country' 
								);
				$this->response($response, rest_controller::HTTP_INTERNAL_SERVER_ERROR);
		}
	}
    public function get_user_bonus_filter_data_post()
	{		
		//$result = $this->lang->line('payment_method_opt');
		$result = get_active_payment_list($this->app_config);
		
		$this->response(array(config_item('rest_status_field_name')=>TRUE, 'data'=>$result), rest_controller::HTTP_OK);
	}
	public function get_report_user_bonus_post()
	{
		$result = $this->Report_model->get_report_user_bonus();
                
                $bouns_result = array();
                if(!empty($result['result']))
                {
                    $result_array = array_chunk($result['result'], 3);
                    
                    foreach($result_array as $res)
                    {
                        $user_ids = array_column($res, 'user_id');
                        
                        $util_bonus_data = $this->Report_model->get_users_utilized_bonus($user_ids);
                        $util_bonus_data = array_column($util_bonus_data, 'bonus_amount', 'user_id');
                        
                        foreach($res as $b_res)
                        {
                            $b_res["admin_bonus"] = number_format($b_res['admin_bonus'], 2, '.', ',');
                            $b_res["signup_bonus"] = number_format($b_res['signup_bonus'], 2, '.', ',');
                            $b_res["refferal_bonus"] = number_format($b_res['refferal_bonus'], 2, '.', ',');
                            $b_res["signup_bonus_pending"] = number_format($b_res['signup_bonus_pending'], 2, '.', ',');
                            $b_res["refferal_bonus_pending"] = number_format($b_res['refferal_bonus_pending'], 2, '.', ',');
                            
                            $b_res['utilized_bonus'] = 0;
                            if(isset($util_bonus_data[$b_res['user_id']]))
                            {
                                $b_res['utilized_bonus'] = number_format($util_bonus_data[$b_res['user_id']], 2, '.', ',');
                            }
                            $bouns_result[] = $b_res;
                        }
                    }
                    //print_r($bouns_result);
                    $result['result'] = $bouns_result;
                    
                }
		
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= '';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	}

	public function get_report_user_bonus_get()
	{
		$_POST = $this->input->get();
		$result = $this->Report_model->get_report_user_bonus();
		
		$result_data = array();			
                if(!empty($result['result'])){
                    
                    $result_array = array_chunk($result['result'], 3);
                    
                    foreach($result_array as $res)
                    {
                        $user_ids = array_column($res, 'user_id');
                        
                        $util_bonus_data = $this->Report_model->get_users_utilized_bonus($user_ids);
                        $util_bonus_data = array_column($util_bonus_data, 'bonus_amount', 'user_id');
                        
                        foreach($res as $b_res)
                        {
                            $temp["user_unique_id"] = $b_res["user_unique_id"];
                            $temp["user_name"] = $b_res["user_name"];
                            $temp["name"] = $b_res["name"];
                            $temp["email"] = $b_res["email"];
                            $temp["admin_bonus"] = number_format($b_res['admin_bonus'], 2, '.', ',');
                            $temp["signup_bonus"] = number_format($b_res['signup_bonus'], 2, '.', ',');
                            $temp["refferal_bonus"] = number_format($b_res['refferal_bonus'], 2, '.', ',');
                            $temp["signup_bonus_pending"] = number_format($b_res['signup_bonus_pending'], 2, '.', ',');
                            $temp["refferal_bonus_pending"] = number_format($b_res['refferal_bonus_pending'], 2, '.', ',');
                            
                            $temp['utilized_bonus'] = 0.00;
                            if(isset($util_bonus_data[$b_res['user_id']]))
                            {
                                $temp['utilized_bonus'] = number_format($util_bonus_data[$b_res['user_id']], 2, '.', ',');
                            }
                            $temp['member_since'] = $b_res['member_since'];
                            $result_data[] = $temp;
                        }
                    }
                }
                $header = array_keys($result_data[0]);
                $camelCaseHeader = array_map("camelCaseString", $header);
                $result_data = array_merge(array($camelCaseHeader),$result_data);
		 
		$this->load->helper('download');
		$this->load->helper('csv');
		$data = array_to_csv($result_data);

		//$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "From Date $from_date\nTo Date $to_date\n\n" . html_entity_decode($data);
		$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . html_entity_decode($data);
		$name = 'User_Bonus.csv';
		force_download($name, $data);
			
	}
########################
	public function get_all_contest_users_report_post()
	{
		$result = $this->Report_model->get_all_contest_users_report();
		
		$this->response(array(config_item('rest_status_field_name')=>TRUE, 'data'=>$result) , rest_controller::HTTP_OK);
	}


	/**
	*@method revenue_report
	*types: Daily (sunday, mon,tuesday...saturday)
	*       weekly(1-7,8-14...) for last 6 weeks
			quaterly(Jan-Mar,Apr-Jun,Jul-Sep,Oct-Dec)
			Monthly(jan,feb,march....dec)
	**/
	public function revenue_report_post()
	{
		$filters = $this->input->post();

		$post_target_url   = 'contest/get_completed_contest';
		
		$post_api_response       = $this->http_post_request($post_target_url,$filters,2);

		$lineup_master_contest_id = array_column($post_api_response['data'], 'lineup_master_contest_id');
		$site_rake = array_column($post_api_response['data'], 'site_rake');

		$lineup_master_contest_ids_chunk = array_chunk($lineup_master_contest_id,50);
		$site_rake_chunk = array_chunk($site_rake,50);

		$result = array();
		$result_chunk = array();
		
		$duration 	= !empty($filters['duration'])?$filters['duration']:"yearly";
		$total_profit = 0;
                $profit = array();
		$column = array();
                $series = array();
                $report_month = array();
                
                $duration 	= !empty($filters['duration'])?$filters['duration']:"yearly";
                switch ($duration) 
		{
			case 'monthly':
				
                                $last_month 	= date('m', strtotime('last day of last month'));
                                $year 		= date('Y', strtotime('last day of last month'));
                                $no_of_days 	=  cal_days_in_month(CAL_GREGORIAN, $last_month , $year);
                                
                                $month_dates = array();
                                for ($i=1; $i <=$no_of_days ; $i++) { 
                                        $month_dates[] = $i;
                                        $profit[] = 0;
                                }
                                $column = $month_dates;
				break;
			case 'weekly':
                                $no_of_days = 7;
                                $days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
                                $profit = array(0,0,0,0,0,0,0);
                                $column = $days;
                                break;
			case 'quarterly':
                                $column = array('Jan-Mar','Apr-Jun','Jul-Sep','Oct-Dec');
                                $profit = array(0,0,0,0);
                                break;
			default:
				break;	
		}
                
		foreach ($lineup_master_contest_ids_chunk as $key => $lineup_master_contest_ids) {
			$filters['site_rake'] = $site_rake_chunk[$key];
			$filters['source_id'] = $lineup_master_contest_ids;
			$result_chunk = $this->Report_model->revenue_report($filters);
                        
			$column			= $result_chunk['column'];
			$series			= $result_chunk['series'];
			$report_month	= $result_chunk['report_month'];

			$total_profit = $total_profit + $result_chunk['total_profit'];
			
			foreach ($result_chunk['profit'][0] as $key => $value) {
                            $profit_val = 0;
                            if(isset($profit[$key])){
                                $profit_val = $profit[$key];
                            }
                            $profit[$key] = number_format($profit_val + $value,2,".","");
			}
		}
                
		$result['column'] = $column;
		$result['profit'][0] = $profit;
		$result['total_profit'] = number_format($total_profit,2,".",",");
		$result['series'] = $series;
		$result['report_month'] = $report_month;

		//$data = '{"column":["January","February","March","April","May","June","July","August","September","October","November","December"],"profit":[[10,0,0,0,0,1,0,0,0,0,0,0],[18,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0]],"total_profit":29,"series":["H2H","Multiple user","50\/50","Uncapped"],"report_month":""}';
		//$result = json_decode($data);
		
                // Prepare data for table listing
                $result_list = array();
                if(!empty($result['column']))
                {
                    foreach($result['column'] as $c_key => $col)
                    {
                        $result_list[] = array('column' => $col, 'profit' => !empty($profit[$c_key]) ? $profit[$c_key] : 0);
                    }
                }
                $result['result_list'] = $result_list;
//              print_r($result);
//		die;
		$this->response(array(config_item('rest_status_field_name')=>TRUE, 'data'=>$result) , rest_controller::HTTP_OK);
	}
	public function revenue_report_get()
	{
		$_POST = $this->input->get();
                $filters = $this->input->post();

		$post_target_url   = 'contest/get_completed_contest';
		
		$post_api_response       = $this->http_post_request($post_target_url,$filters,2);

		$lineup_master_contest_id = array_column($post_api_response['data'], 'lineup_master_contest_id');
		$site_rake = array_column($post_api_response['data'], 'site_rake');

		$lineup_master_contest_ids_chunk = array_chunk($lineup_master_contest_id,50);
		$site_rake_chunk = array_chunk($site_rake,50);



		$result = array();
		$result_chunk = array();
		
		$duration 	= !empty($filters['duration'])?$filters['duration']:"yearly";
		
		/*if($duration=='monthly')
		$profit = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
		else
		$profit = array(0,0,0,0,0,0,0,0,0,0,0,0);*/	

		$total_profit = 0;
                $profit = array();
		$column = array();
                $series = array();
                $report_month = array();
                
                $duration 	= !empty($filters['duration'])?$filters['duration']:"yearly";
                switch ($duration) 
		{
			case 'monthly':
				
                                $last_month 	= date('m', strtotime('last day of last month'));
                                $year 		= date('Y', strtotime('last day of last month'));
                                $no_of_days 	=  cal_days_in_month(CAL_GREGORIAN, $last_month , $year);
                                
                                $month_dates = array();
                                for ($i=1; $i <=$no_of_days ; $i++) { 
                                        $month_dates[] = $i;
                                        $profit[] = 0;
                                }
                                $column = $month_dates;
				break;
			case 'weekly':
                                $no_of_days = 7;
                                $days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
                                $profit = array(0,0,0,0,0,0,0);
                                $column = $days;
                                break;
			case 'quarterly':
                                $column = array('Jan-Mar','Apr-Jun','Jul-Sep','Oct-Dec');
                                $profit = array(0,0,0,0);
                                break;
			case 'yearly':
				$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
                                $profit = array(0,0,0,0,0,0,0,0,0,0,0,0);
                                $column = $months;
				break;
			default:
				break;	
		}
                
		foreach ($lineup_master_contest_ids_chunk as $key => $lineup_master_contest_ids) {
			$filters['site_rake'] = $site_rake_chunk[$key];
			$filters['source_id'] = $lineup_master_contest_ids;
			$result_chunk = $this->Report_model->revenue_report($filters);
                        
			$column			= $result_chunk['column'];
			$series			= $result_chunk['series'];
			$report_month	= $result_chunk['report_month'];

			$total_profit = $total_profit + $result_chunk['total_profit'];
			
			foreach ($result_chunk['profit'][0] as $key => $value) {
                            $profit_val = 0;
                            if(isset($profit[$key])){
                                $profit_val = $profit[$key];
                            }
                            $profit[$key] = number_format($profit_val + $value,2,".","");
			}
			
			
		}
                
		$result['column'] = $column;
		$result['profit'][0] = $profit;
		$result['total_profit'] = number_format($total_profit,2,".",",");
		$result['series'] = $series;
		$result['report_month'] = $report_month;

		//$data = '{"column":["January","February","March","April","May","June","July","August","September","October","November","December"],"profit":[[10,0,0,0,0,1,0,0,0,0,0,0],[18,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0],[0,0,0,0,0,0,0,0,0,0,0,0]],"total_profit":29,"series":["H2H","Multiple user","50\/50","Uncapped"],"report_month":""}';
		//$result = json_decode($data);
		
                // Prepare data for table listing
                $result_list = array();
                if(!empty($result['column']))
                {
                    foreach($result['column'] as $c_key => $col)
                    {
                        $result_list[] = array('column' => $col, 'profit' => !empty($profit[$c_key]) ? $profit[$c_key] : 0);
                    }
                }
                $result['result_list'] = $result_list;
//              print_r($result);
//		die;
		if(!empty($result_list)){
                    
                    $header = array_keys($result_list[0]);
                    $camelCaseHeader = array_map("camelCaseString", $header);
                    $result_list = array_merge(array($camelCaseHeader),$result_list);
                    
                    $this->load->helper('download');
                    $this->load->helper('csv');
                    $data = array_to_csv($result_list);
                    
                    $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "Duration: $duration\n\n" . html_entity_decode($data);
                    $name = 'Revenue Report.csv';
                    force_download($name, $data);
                    
                    
				}
				else{
					$result = "no record found";
					$this->load->helper('download');
					$this->load->helper('csv');
					$data = array_to_csv($result);
					$name = 'Revenue Report.csv';
					force_download($name, $result);
				}
	}

	public function get_completed_contest_post()
	{
		$filters = $this->input->post();
		$result = $this->Report_model->revenue_report($filters);

		$post_target_url   = 'contest/contest_list';
		
		$post_api_response       = $this->http_post_request($post_target_url,$post_params,2);

		$result = array();
		
		$this->response(array(config_item('rest_status_field_name')=>TRUE, 'data'=>$result) , rest_controller::HTTP_OK);
	}
		
	

	public function referal_report_details_csv_get()
	{
            $_POST = $this->input->get();
            $this->Report_model->get_referral_report_details_xls();
	
	}

	public function get_contest_by_league_post()
	{
		$post_params = $this->input->post();
		$post_params['status'] = 3 ; // for prize distribution 

		$post_target_url   = 'contest/contest_list';
		
		$post_api_response  = $this->http_post_request($post_target_url,$post_params,2);

		$result =$post_api_response['data']['result'];
		
		$this->response(array(config_item('rest_status_field_name')=>TRUE, 'data'=>$result) , rest_controller::HTTP_OK);
	}

        public function get_all_contest_overlay_report_post()
	{
		//print_r($this->input->post());die();
		$filters = $this->input->post();
		$filters["csv"] = FALSE;
		$post_target_url	= 'contest/get_completed_contest_report';
		$post_api_response	= $this->http_post_request($post_target_url,$filters,2);
		$result_array = array();
		$prize_detail = array();
		$contest_detail = $post_api_response['data']['result'];
		$total_contest = $post_api_response['data']['total'];

		if(!empty($contest_detail))
		{
			$contest_ids =  array_column($post_api_response['data']['result'], 'contest_id');

			if(!empty($contest_ids))
			{
				foreach($contest_ids as $contest_id)
				{
					$post_target_url					= 'contest/get_lineup_master_contest';
					$post_params['contest_ids']			= array($contest_id);
					$api_lineup_master_contest_response	= $this->http_post_request($post_target_url,$post_params,2);
					$lineup_detail = $api_lineup_master_contest_response["data"];
					$temp_prize_detail = $this->Report_model->get_contest_prize_detail($contest_id,$lineup_detail);

					$prize_detail[$contest_id] = $temp_prize_detail;
				}

			}

			$result_array["sum_join_real_amount"] = 0;
			$result_array["sum_join_bonus_amount"] = 0;
			$result_array["sum_join_winning_amount"] = 0;

			$result_array["sum_win_amount"] = 0;

			$result_array["sum_profit_loss"] = 0;

			foreach($contest_detail as $contest)
			{
				$contest = array_merge($contest, $prize_detail[$contest['contest_id']]);
				$contest["profit_loss"]	= ($contest["total_join_real_amount"]+$contest["total_join_winning_amount"]) - $contest["total_win_winning_amount"];

				$contest["total_entry_fee"]		= $contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"];
				
				$result_array["sum_join_real_amount"] += $contest["total_join_real_amount"];
				$result_array["sum_join_bonus_amount"] += $contest["total_join_bonus_amount"];
				$result_array["sum_join_winning_amount"] += $contest["total_join_winning_amount"];
				
				$result_array["sum_win_amount"] += $contest["total_win_winning_amount"];
				
				$result_array["sum_profit_loss"] += $contest["profit_loss"];
				
				$result_array["result"][] = $contest;

			}
			$result_array["total"] = $total_contest;
		}
		$this->response(array(config_item('rest_status_field_name')=>TRUE, 'data'=>$result_array) , rest_controller::HTTP_OK);
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


	public function contest_report_csv_gets()
	{
		$_POST = $this->input->get();

		$result	= $this->Report_model->get_complet_contest_report($_POST);
						
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



	public function get_all_contest_report_post()
	{	
		$filters = $this->input->post();
		$filters["csv"] = FALSE;
		$this->load->model('contest/Contest_model');
		$this->load->model('report/Cron_model');
		// $post_api_response	= $this->Contest_model->get_complet_contest_report($filters);
		$post_api_response	= $this->Report_model->get_complet_contest_report($filters);
		
		// echo "<pre>";
		// print_r($post_api_response);die;
		$result_array = array();
		$prize_detail = array();
		$contest_detail = $post_api_response['result'];
		$total_contest = $post_api_response['total']?$post_api_response['total']:0;

		// echo "<pre>";
		// print_r($contest_detail);die;

		$contest_prize_detail = array();
		if(!empty($contest_detail))
		{
			$this->load->model('Report_model');
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
			$result_array["sum_total_entery_fee"]=0;
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
			// $result_array["sum_botuser_total_real_entry_fee"] = 0;
			$result_array["sum_bot_entry_fee"] = 0;
			$result_array["sum_promocode_entry_fee_real"] = 0;
			$result_array["sum_total_win_coins"] = 0;
			$result_array["sum_total_win_bonus"] = 0;
			$result_array["sum_total_win_amount_to_real_user"] = 0;
			

			foreach($contest_detail as &$contest)
			{
				$contest["total_join_real_amount"] = 0;
				$contest["total_win_winning_amount"] = 0;
				$contest["total_join_real_amount"] = 0;
				$contest["total_join_winning_amount"] = 0;
				$contest["total_join_bonus_amount"] = 0;
				$contest["total_join_coin_amount"] = 0;
				$contest["total_win_coins"] = 0;
				$contest["total_win_bonus"] = 0;
				$contest["total_entry_fee"]=0;
				$contest["total_win_amount_to_real_user"]=0;
				$contest['siterake_amount'] = number_format((($contest['prize_pool']*$contest['site_rake'])/100),2,'.',',');

				if(isset($contest_prize_detail[$contest['contest_id']]))
				{
					$result_array["sum_total_entry_fee_real"]+=$contest_prize_detail[$contest['contest_id']]['total_join_real_amount'];
					$contest = array_merge($contest, $contest_prize_detail[$contest['contest_id']]);
				}
				
				$contest["profit_loss"]	= number_format(($contest["total_join_real_amount"] +$contest["total_join_winning_amount"]- $contest["total_win_winning_amount"]),2,'.','');
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
				$result_array["sum_bot_entry_fee"] += $contest["bot_entry_fee"]; 
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
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= '';
		$this->api_response_arry['data']			= $result_array;
		$this->api_response();	
	}



	

}

/* End of file Report.php */
/* Location: ./application/controllers/Report.php */