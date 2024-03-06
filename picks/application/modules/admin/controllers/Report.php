<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Report extends Common_Api_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Report_model');
	}

    /**
	 *  use for getting contest report 
	 * 
	 */
	public function get_all_contest_report_post()
	{		
		$filters = $this->input->post();
		$filters["csv"] = FALSE;
		$this->load->model('Contest_model');
		$post_api_response	= $this->Contest_model->get_completed_contest_report($filters);
		// echo "<pre>";
		// print_r($post_api_response);die();
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
				$contest['siterake_amount'] = number_format((($contest['prize_pool']*$contest['site_rake'])/100),2,'.',',');

				if(isset($contest_prize_detail[$contest['contest_id']]))
				{
					$result_array["sum_total_entry_fee_real"]+=$contest_prize_detail[$contest['contest_id']]['total_join_real_amount'];
					$contest = array_merge($contest, $contest_prize_detail[$contest['contest_id']]);
				}
				
				$contest["profit_loss"]	= number_format(($contest["total_join_real_amount"] - $contest["total_win_winning_amount"]),2,'.','');		
				
			
				$result_array["sum_join_real_amount"] += $contest["total_join_real_amount"];
				$result_array["sum_join_winning_amount"] += $contest["total_join_winning_amount"];
				$result_array["sum_join_bonus_amount"] += $contest["total_join_bonus_amount"];
				$result_array["sum_join_coin_amount"] += $contest["total_join_coin_amount"];
				$result_array["sum_win_amount"] += $contest["total_win_winning_amount"];
				$result_array["sum_total_win_coins"] += $contest["total_win_coins"];
				$result_array["sum_total_win_bonus"] += $contest["total_win_bonus"];
				// $result_array["sum_total_entery_fee"] += ($contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_coin_amount"]);
				$result_array["sum_profit_loss"] += $contest["profit_loss"]; 
				$result_array["sum_entry_fee"] += $contest["entry_fee"]; 
				$result_array["sum_site_rake"] += $contest["site_rake"]; 
				$result_array["sum_min"] += $contest["minimum_size"]; 
				$result_array["sum_max"] += $contest["size"]; 
				$result_array["sum_total_user_joined"] += $contest["total_user_joined"]; 
				// $result_array["sum_system_teams"] += $contest["system_teams"]; 
				$result_array["sum_real_teams"] += $contest["real_teams"]; 
				$result_array["sum_max_bonus_allowed"] += $contest["max_bonus_allowed"]; 
				$result_array["sum_prize_pool"] += $contest["prize_pool"]; 
				$result_array["sum_total_entry_fee"] += $contest["total_entry_fee"]; 
				// $result_array["sum_botuser_total_real_entry_fee"] += $contest["botuser_total_real_entry_fee"]; 
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

		// echo "<pre>";
		// print_r($result_array);die();


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
		$this->load->model('Contest_model');
		$post_api_response	= $this->Contest_model->get_completed_contest_report($filters);	
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
			// $result_array["sum_system_teams"] = 0;
			// $result_array["sum_real_teams"] = 0;
			$result_array["sum_max_bonus_allowed"] = 0;
			$result_array["sum_prize_pool"] = 0;
			$result_array["sum_total_entry_fee"] = 0;
			$result_array["sum_total_entry_fee_real"] = 0;
			// $result_array["sum_botuser_total_real_entry_fee"] = 0;
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
				$contest["total_join_real_amount"] = 0;
				// $contest["sum_total_entery_fee"] = 0;
                $contest["total_win_winning_amount"] = 0;
                $contest["total_join_winning_amount"] = 0;
                $contest["total_join_bonus_amount"] = 0;
                $contest["total_join_coin_amount"] = 0;
                $contest["total_win_coins"] = 0;
                $contest["total_win_bonus"] = 0;
                $contest["total_win_amount_to_real_user"] = 0;
				$contest['siterake_amount'] = number_format((($contest['prize_pool']*$contest['site_rake'])/100),2,'.',',');

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
				// $result_array["sum_total_entery_fee"] += ($contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"] + $contest["total_join_coin_amount"]);
				$result_array["sum_profit_loss"] += $contest["profit_loss"]; 
				$result_array["sum_entry_fee"] += $contest["entry_fee"]; 
				$result_array["sum_site_rake"] += $contest["site_rake"]; 
				$result_array["sum_min"] += $contest["minimum_size"]; 
				$result_array["sum_max"] += $contest["size"]; 
				$result_array["sum_total_user_joined"] += $contest["total_user_joined"]; 
				// $result_array["sum_system_teams"] += $contest["system_teams"]; 
				// $result_array["sum_real_teams"] += $contest["real_teams"]; 
				$result_array["sum_max_bonus_allowed"] += $contest["max_bonus_allowed"]; 
				$result_array["sum_prize_pool"] += $contest["prize_pool"]; 
				$result_array["sum_total_entry_fee"] += $contest["total_entry_fee"]; 
				// $result_array["sum_botuser_total_real_entry_fee"] += $contest["botuser_total_real_entry_fee"]; 
				// $result_array["result"][] = $contest;

				$html_string_f.="<tr>";
				$html_string_f.="<td>".$contest["match"]."</td>";
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
				// $html_string_f.="<td>".$contest["promocode_entry_fee_real"]."</td>";
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
			$html_string_f.="<td><b>".$result_array["sum_total_win_bonus"]."</b></td>";
			$html_string_f.="<td><b>".$result_array["sum_total_win_coins"]."</b></td>";	
			$html_string_f.="<td><b>".$result_array["sum_win_amount"]."</b></td>";
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


}