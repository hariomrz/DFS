<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Gst extends MYREST_Controller {

	public $country_id = 101;//India
	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();

		if((!$this->app_config['allow_gst']['key_value'] && !$this->app_config['int_version']['key_value']) || $this->app_config['int_version']['key_value']){
			$message = "Either international version is on or GST module is off please check.";
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $message;
			$this->api_response();
		}
		$this->load->model('Gst_model');
	}

	/**
     * Used for get filter data list
     * @param array $post_data
     * @return array
     */
	public function get_filter_list_post(){
		$post_data = $this->input->post();
		$state_id = isset($this->app_config['allow_gst']['custom_data']['state_id']) ? $this->app_config['allow_gst']['custom_data']['state_id'] : 0;
		$filter = array();
		$filter['state_id'] = $state_id;
		$filter['invoice_type'] = array();
		$filter['invoice_type'][] = array("id"=>"0","name"=>"Old GST");
		$filter['invoice_type'][] = array("id"=>"1","name"=>"New GST");
		$filter['module_type'] = array();
		$filter['module_type'][] = array("id"=>"1","name"=>"DFS");
		if(isset($this->app_config['allow_livefantasy']) && $this->app_config['allow_livefantasy']['key_value']==1)
		{
			$filter['module_type'][] = array("id"=>"2","name"=>"LiveFantasy");
		}
		if(isset($this->app_config['allow_picks']) && $this->app_config['allow_picks']['key_value']==1)
		{
			$filter['module_type'][] = array("id"=>"3","name"=>"PicksFantasy");
		}
		$filter['report_type'] = array();
		$gst_allow_check = isset($this->app_config['allow_gst']['key_value']) ? $this->app_config['allow_gst']['key_value'] : 0;
		$tds_allow_check = isset($this->app_config['allow_tds']['key_value']) ? $this->app_config['allow_tds']['key_value'] : 0;

		if ($gst_allow_check === "1") {
		 		   
		    $filter['report_type'][] = array("id"=>"1","name"=>"GST Report");
		}
		$filter['state_type'] = array();
		$filter['state_type'][] = array("id"=>"1","name"=>"All");
		$filter['state_type'][] = array("id"=>"2","name"=>"Intra State");
		$filter['state_type'][] = array("id"=>"3","name"=>"Inter State");
		$filter['state_list'][] = array("master_state_id"=>"0","state_name"=>"All");
		$state_list = $this->Gst_model->get_all_table_data('master_state_id,name as state_name',MASTER_STATE,array('master_country_id'=> $this->country_id));
		$filter['state_list'] = array_merge($filter['state_list'],$state_list);
		
		$this->api_response_arry['data'] = $filter;
		$this->api_response();
	}

	/**
     * Used for get completed match list
     * @param int $match_id
     * @return array
     */
	public function get_gst_completed_match_post(){
        $this->form_validation->set_rules('from_date','From Date','trim|required');
        $this->form_validation->set_rules('to_date','To Date','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $match_list = $this->Gst_model->get_gst_completed_fixture($post_data);
		$this->api_response_arry['data'] = $match_list;
		$this->api_response();
	}

	/**
     * Used for get completed contest list
     * @param int $match_id
     * @return array
     */
	public function get_gst_completed_contest_post(){
        $this->form_validation->set_rules('match_id','Match ID','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $module_type = isset($post_data['module_type']) ? $post_data['module_type'] : 1;
		$contest_list = $this->Gst_model->get_gst_completed_contest($post_data['match_id'],$module_type);
		$this->api_response_arry['data'] = $contest_list;
		$this->api_response();
	}

	/**
     * Used for get report data list
     * @param array $post_data
     * @return array
     */
	public function gst_report_post(){
		$post_data = $this->input->post();
		$portal_state_id = isset($this->app_config['allow_gst']['custom_data']['state_id']) ? $this->app_config['allow_gst']['custom_data']['state_id'] : 0;
		$tds_percent = isset($this->app_config['allow_tds']['custom_data']['percent']) ? $this->app_config['allow_tds']['custom_data']['percent'] : 0;
		$tds_amount = isset($this->app_config['allow_tds']['custom_data']['amount']) ? $this->app_config['allow_tds']['custom_data']['amount'] : 0;
		$post_data['portal_state_id'] = $portal_state_id;
		$report_type = isset($post_data['report_type']) ? $post_data['report_type'] : 1;
		$module_type = isset($post_data['module_type']) ? $post_data['module_type'] : 1;

		$invoice_type = isset($post_data['invoice_type']) ? $post_data['invoice_type'] : 1;
		$post_data['invoice_type'] = $invoice_type;

		if($invoice_type == 1){
			$table_field = array('invoice_id','user_name','pan_no','state_name','event','txn_date','txn_amount','cgst','sgst','igst','paid_by','user_gst','download_report');
		}else{
			$table_field = array('invoice_id','user_name','pan_no','state_name','match_name','contest_name','scheduled_date','txn_date','entry_fee','site_rake','txn_amount','rake_amount','cgst','sgst','igst','min_size','max_size','total_user_joined','download_report');
		}
		$report_list = $this->Gst_model->get_gst_report($post_data);
		$total_sum = array("total_entry"=>"0","total_taxable"=>"0","cgst"=>"0","sgst"=>"0","igst"=>"0");
		if(isset($report_list['result']) && !empty($report_list['result'])){
			$rs_data = $report_list['result'];
			$total_sum['total_entry'] = round(array_sum(array_column($rs_data,"txn_amount")),2);
			$total_sum['total_taxable'] = round(array_sum(array_column($rs_data,"rake_amount")),2);
			$total_sum['cgst'] = round(array_sum(array_column($rs_data,"cgst")),2);
			$total_sum['sgst'] = round(array_sum(array_column($rs_data,"sgst")),2);
			$total_sum['igst'] = round(array_sum(array_column($rs_data,"igst")),2);
		}
		if($invoice_type == 1){
			unset($total_sum['total_taxable']);
		}

		$result = array();
		$result['table_field'] = $table_field;
		$result['result'] = $report_list['result'];
		$result['total'] = $report_list['total'];
		$result['total_count'] = $total_sum;

		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	/**
     * Used for download reports
     * @param array $get_data
     * @return report file
     */
	public function gst_report_get(){
		$post_data = $this->input->get();
		if(!isset($post_data['csv']))
		{
			$post_data["csv"] = TRUE;
		}
		$portal_state_id = isset($this->app_config['allow_gst']['custom_data']['state_id']) ? $this->app_config['allow_gst']['custom_data']['state_id'] : 0;
		$tds_percent = isset($this->app_config['allow_tds']['custom_data']['percent']) ? $this->app_config['allow_tds']['custom_data']['percent'] : 0;
		$tds_amount = isset($this->app_config['allow_tds']['custom_data']['amount']) ? $this->app_config['allow_tds']['custom_data']['amount'] : 0;
		$post_data['portal_state_id'] = $portal_state_id;
		if(isset($post_data['state_id']) && $post_data['state_id'] == "undefined"){
			$post_data['state_id'] = "";
		}
		
		$invoice_type = isset($post_data['invoice_type']) ? $post_data['invoice_type'] : 1;
		$post_data['invoice_type'] = $invoice_type;
		$report_list = $this->Gst_model->get_gst_report($post_data); 
		$name = 'gst_report_' . format_date('today', 'Y-m-d') . '.csv';
		if(!empty($report_list['result']))
		{
			$result =$report_list['result'];
			$header = array_keys($result[0]);
			$camelCaseHeader = array_map("camelCaseString", $header);
			$result = array_merge(array($camelCaseHeader),$result);

			$this->load->helper('download');
			$this->load->helper('csv');
			$data = array_to_csv($result);

			$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
			force_download($name, $data);
		}
		else
		{
			$result = "no record found";
			$this->load->helper('download');
			$this->load->helper('csv');
			$data = array_to_csv($result);
			force_download($name, $result);
		}
	}

	/**
     * Used for generating gst report as PDF
     * @param array $invoice_id
     * @return PDF report
     */
	public function gst_invoice_download_get()
	{
        $get_data 		= $this->get();
        $invoice_info 	= $this->Gst_model->get_invoice_data($get_data['invoice_id']);
        $get_user_info 	= $this->Gst_model->get_single_row('phone_no,phone_code,user_name,first_name,last_name,email,address,city,zip_code,master_state_id',USER,array('user_id' => $invoice_info['user_id']));
        $winning_info = $this->Gst_model->get_winning_info($invoice_info['lmc_id']);
        if(!empty($invoice_info))
        {
        	$invoice_data = array();
        	$invoice_data['company_name'] 		= $this->app_config['allow_gst']['custom_data']['firm_name'];
	        $invoice_data['company_address'] 	= $this->app_config['allow_gst']['custom_data']['firm_address'];
			$invoice_data['company_contact'] 	= $this->app_config['allow_gst']['custom_data']['contact_no'];
			$invoice_data['currency'] 			= CURRENCY_CODE_HTML;

			$invoice_data['date'] 				= $invoice_info['txn_date'];
			$invoice_data['invoice_no'] 		= $invoice_info['invoice_id'];
			$invoice_data['invoice_type'] 		= $invoice_info['invoice_type'];
			$invoice_data['collection_name'] 	= $invoice_info['match_name'];
			$invoice_data['entry_fee'] 			= $invoice_info['entry_fee'];
			$invoice_data['contest_name'] 		= $invoice_info['contest_name'];

			$invoice_data['phone_no'] 			= $get_user_info['phone_code'].$get_user_info['phone_no'];
			$invoice_data['user_name'] 			= $get_user_info['user_name'];
			$invoice_data['full_name'] 			= $get_user_info['first_name']." ".$get_user_info['last_name'];
			$invoice_data['email'] 				= $get_user_info['email'];
			$invoice_data['address'] 			= $get_user_info['address'];
			$invoice_data['city'] 				= $get_user_info['city'];
			$invoice_data['zip_code'] 			= $get_user_info['zip_code'];
			$master_state_id 					= $get_user_info['master_state_id'];
			$invoice_data['gst_number'] 		= $invoice_info['gst_number'];

			$invoice_data['win_amount'] 	= 0;
			$invoice_data['tds'] 			= 0;
			$invoice_data['igst'] 			= 0;
			$invoice_data['sgst'] 			= 0;
			$invoice_data['cgst'] 			= 0;
			$invoice_data['taxable_value'] 	= 0;
			$invoice_info['taxable_value'] = 0;
			//SET taxable_value // platform_fee = round((`C`.`txn_amount`* `C`.`site_rake`)/100)
			if(number_format((($invoice_info['txn_amount']/100)*$invoice_info['site_rake']),2,".","") > 0)
			{
				$platform_fee = number_format((($invoice_info['txn_amount']/100)*$invoice_info['site_rake']),2,".","");
				$invoice_data['taxable_value'] = number_format((($platform_fee*100)/118),2,".","");
			}

			if($invoice_data['invoice_type'] == "1"){
				$invoice_info['taxable_value'] = $invoice_info['txn_amount'];
			}

			//SET IGST
			if($master_state_id != $this->app_config['allow_gst']['custom_data']['state_id'] && $invoice_data['taxable_value'] > 0)
			{
				$invoice_data['igst'] = number_format((($invoice_data['taxable_value']/100)*18),2,".","");
			}
			//SET SGST and CGST
			if($master_state_id == $this->app_config['allow_gst']['custom_data']['state_id'] && $invoice_data['taxable_value'] > 0)
			{
				$sgst_and_cgst = round($invoice_data['taxable_value']*9/100,2);
				$invoice_data['sgst'] = $sgst_and_cgst;
				$invoice_data['cgst'] = $sgst_and_cgst;
			}

			if(isset($master_state_id) && $master_state_id != "")
			{
				$get_state_name = $this->Gst_model->get_single_row('name',MASTER_STATE,array('master_state_id' => $master_state_id));
				$invoice_data['state'] = $get_state_name['name'];
			}
			else
			{
				$get_state_name = $this->Gst_model->get_single_row('name',MASTER_STATE,array('master_state_id' => $this->app_config['allow_gst']['custom_data']['state_id']));
				$invoice_data['state'] = $get_state_name['name'];
			}

			$invoice_data['txn_type'] = $invoice_info['txn_type'];
			$gst_rate = number_format($invoice_info['gst_rate'],"0",".","");
			$cgst_rate = number_format(($gst_rate / 2),"0",".","");
			$sgst_rate = number_format(($gst_rate / 2),"0",".","");
			$total_gst = $invoice_info['sgst']+$invoice_info['cgst']+$invoice_info['igst'];
			
			$total = number_format(($total_gst + $invoice_info['taxable_value']),"2",".","");
			$data_arr = array();
			$data_arr['heading'] = $invoice_info['match_name'];
			$data_arr['taxable_value'] = $invoice_info['taxable_value'];
			$data_arr['total_gst'] = $total_gst;
			$data_arr['fields'] = array();
			if($invoice_data['invoice_type'] == 1){
				$data_arr['fields'][] = array("contest_name"=>"Name","entry_fee"=>"Amount(INR)","taxable_value"=>"Taxable Value <br>(INR)*","sgst"=>"SGST<br>@".$sgst_rate."% (INR)","cgst"=>"CGST<br>@".$cgst_rate."% (INR)","igst"=>"IGST<br>@".$gst_rate."% (INR)","total"=>"Total");
				$data_arr['fields'][] = array("contest_name"=>$invoice_info['match_name'],"entry_fee"=>$invoice_info['txn_amount'],"taxable_value"=>$invoice_info['taxable_value'],"sgst"=>$invoice_info['sgst'],"cgst"=>$invoice_info['cgst'],"igst"=>$invoice_info['igst'],"total"=>$total);
				$data_arr['fields'][] = array("contest_name"=>"Total","entry_fee"=>$invoice_info['txn_amount'],"taxable_value"=>$invoice_info['taxable_value'],"sgst"=>$invoice_info['sgst'],"cgst"=>$invoice_info['cgst'],"igst"=>$invoice_info['igst'],"total"=>$total);
			}else{
				$data_arr['fields'][] = array("contest_name"=>"Contest Name","entry_fee"=>"Entry Amount(INR)","taxable_value"=>"Taxable Value <br>(Platform Fee) (INR)*","sgst"=>"SGST<br>@".$sgst_rate."% (INR)","cgst"=>"CGST<br>@".$cgst_rate."% (INR)","igst"=>"IGST<br>@".$gst_rate."% (INR)","total"=>"Total");
				$data_arr['fields'][] = array("contest_name"=>$invoice_info['contest_name'],"entry_fee"=>$invoice_info['entry_fee'],"taxable_value"=>$invoice_info['taxable_value'],"sgst"=>$invoice_info['sgst'],"cgst"=>$invoice_info['cgst'],"igst"=>$invoice_info['igst'],"total"=>$total);
				$data_arr['fields'][] = array("contest_name"=>"Total","entry_fee"=>$invoice_info['entry_fee'],"taxable_value"=>$invoice_info['taxable_value'],"sgst"=>$invoice_info['sgst'],"cgst"=>$invoice_info['cgst'],"igst"=>$invoice_info['igst'],"total"=>$total);
			}
			$invoice_data['data_list'] = $data_arr;

			if(!empty($invoice_data))
	        {
	        	$gst_pdf_html_data['data'] = $invoice_data;
	        	$html = $this->load->view('gst/invoice',$gst_pdf_html_data,true);
	        	ini_set('memory_limit', '-1');
		      	$this->load->helper('dompdf_helper');
		        generate_pdf('gst_invoice_download',$html);
	        }
		}
	}
}
/* End of file Report.php */
/* Location: ./application/controllers/Gst.php */