<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Finance_erp extends MYREST_Controller{

	public $expenses = 0;
	public $income = 1;
	public $liabilities = 2;
	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
		$this->load->model('Finance_erp_model');
		//Do your magic here
		$this->admin_roles_manage($this->admin_id,'finance_erp');
	}

	public function get_master_data_post()
	{
		$post_data = $this->post();
		$result = array();
		$result['type'] = array();
		$result['type'][] = array("id"=>"0","name"=>"Expenses");
		$result['type'][] = array("id"=>"1","name"=>"Income");
		$result['type'][] = array("id"=>"2","name"=>"Liabilities");
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	public function get_dashboard_data_post()
	{
		$this->form_validation->set_rules('from_date', 'from date', 'trim');
		$this->form_validation->set_rules('to_date', 'to date', 'trim');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->post();
		$result_arr = array();
		$result_arr['expenses'] = array("current"=>"0","past"=>"0");
		$result_arr['income'] = array("current"=>"0","past"=>"0");
		$result_arr['profit'] = array("current"=>"0","past"=>"0");
		$result_arr['revenue'] = 0;
		$result_arr['refund_entry'] = 0;
		$result_arr['amount_disbursed'] = 0;
		$result_arr['bonus_cash'] = 0;
		$result_arr['bonus_expired'] = 0;
		$result_arr['expenses_list'] = array();
		$result_arr['income_list'] = array();
		$result_arr['liabilities_list'] = array();

		//total withdrawal
		$where_arr = array("status"=>"1","source"=>"8");
		if(isset($post_data['from_date']) && $post_data['from_date'] != ""){
			$where_arr["DATE_FORMAT(date_added,'%Y-%m-%d') >="] = $post_data['from_date'];
		}
		if(isset($post_data['to_date']) && $post_data['to_date'] != ""){
			$where_arr["DATE_FORMAT(date_added,'%Y-%m-%d') <="] = $post_data['to_date'];
		}
		$withdrawal = $this->Finance_erp_model->get_single_row('IFNULL(SUM(winning_amount),0) as total',ORDER,$where_arr);
		if(!empty($withdrawal)){
			$result_arr['amount_disbursed'] = $withdrawal['total'];
		}

		//total bonus cash expired
		$bonus_arr = array("is_expired"=>"2");
		if(isset($post_data['from_date']) && $post_data['from_date'] != ""){
			$bonus_arr["DATE_FORMAT(bonus_date,'%Y-%m-%d') >="] = $post_data['from_date'];
		}
		if(isset($post_data['to_date']) && $post_data['to_date'] != ""){
			$bonus_arr["DATE_FORMAT(bonus_date,'%Y-%m-%d') <="] = $post_data['to_date'];
		}
		$bonus_expired = $this->Finance_erp_model->get_single_row('IFNULL(SUM(total_bonus),0) as total',USER_BONUS_CASH,$bonus_arr);
		if(!empty($bonus_expired)){
			$result_arr['bonus_expired'] = $bonus_expired['total'];
		}

		//total revenue
		$where_revenue = array("status"=>"1","source IN(1,2)"=>NULL);
		if(isset($post_data['from_date']) && $post_data['from_date'] != ""){
			$where_revenue["DATE_FORMAT(date_added,'%Y-%m-%d') >="] = $post_data['from_date'];
		}
		if(isset($post_data['to_date']) && $post_data['to_date'] != ""){
			$where_revenue["DATE_FORMAT(date_added,'%Y-%m-%d') <="] = $post_data['to_date'];
		}
		$revenue = $this->Finance_erp_model->get_single_row('IFNULL(SUM(CASE WHEN source=1 THEN (real_amount + winning_amount) ELSE 0 END),0) as total_entry,IFNULL(SUM(CASE WHEN source=2 THEN (real_amount + winning_amount) ELSE 0 END),0) as cancel_entry',ORDER,$where_revenue);
		if(!empty($revenue)){
			$result_arr['revenue'] = $revenue['total_entry'];
			$result_arr['refund_entry'] = $revenue['cancel_entry'];
		}

		$report_data = $this->Finance_erp_model->get_expenses_income($post_data);
		$report_data = array_column($report_data,"total","type");
		if(isset($report_data[$this->expenses])){
			$result_arr['expenses']['current'] = $report_data[$this->expenses];
		}
		if(isset($report_data[$this->income])){
			$result_arr['income']['current'] = $report_data[$this->income];
		}
		//dashbaord past data
		if(isset($post_data['from_date']) && $post_data['from_date'] != "" && isset($post_data['to_date']) && $post_data['to_date'] != ""){
			$datediff = strtotime($post_data['to_date']) - strtotime($post_data['from_date']);
			$days = round($datediff / (60 * 60 * 24));
			$days = $days + 1;
			$last_from_date = date('Y-m-d', strtotime($post_data['from_date'].' -'.$days.' day'));
			$last_to_date = date('Y-m-d', strtotime($post_data['to_date'].' -'.$days.' day'));
			$past_post = array("from_date"=>$last_from_date,"to_date"=>$last_to_date);
			$past_data = $this->Finance_erp_model->get_expenses_income($past_post);
			$past_data = array_column($past_data,"total","type");
			if(isset($past_data[$this->expenses])){
				$result_arr['expenses']['past'] = $past_data[$this->expenses];
			}
			if(isset($past_data[$this->income])){
				$result_arr['income']['past'] = $past_data[$this->income];
			}
		}

		$result_arr['profit']['current'] = $result_arr['income']['current'] - $result_arr['expenses']['current'];
		$result_arr['profit']['past'] = $result_arr['income']['past'] - $result_arr['expenses']['past'];

		$result_arr['expenses_list'] = $this->Finance_erp_model->get_expenses_income_record($post_data,$this->expenses);
		$result_arr['income_list'] = $this->Finance_erp_model->get_expenses_income_record($post_data,$this->income);

		$balance_info = $this->Finance_erp_model->get_single_row('SUM(balance) as balance,SUM(winning_balance) as winning_balance,SUM(bonus_balance) as bonus_balance',USER,array());
		$pending_withdrawal = $this->Finance_erp_model->get_single_row('SUM(winning_amount) as total',ORDER,array("source"=>"8","type"=>"1","status"=>"0"));
		if(!empty($pending_withdrawal)){
			$balance_info['winning_balance'] = $balance_info['winning_balance'] + $pending_withdrawal['total'];
		}
		$liabilities_category = $this->Finance_erp_model->get_all_table_data('category_id,name,is_custom,0 as total',FINANCE_CATEGORY,array('type'=> $this->liabilities));
		$liabilities_category = array_column($liabilities_category,NULL,"category_id");
		$liabilities_category['13']['total'] = $balance_info['winning_balance'];
		$liabilities_category['14']['total'] = $balance_info['balance'];
		$result_arr['liabilities_list'] = array_values($liabilities_category);

		//user active bonus cash
		if(isset($balance_info['bonus_balance']) && $balance_info['bonus_balance'] > 0){
			$result_arr['bonus_cash'] = $balance_info['bonus_balance'];
		}
		//echo "<pre>";print_r($report_data);die;
		$this->api_response_arry['data'] = $result_arr;
		$this->api_response();
	}

	public function get_category_list_post()
	{
		$post_data = $this->post();
		$result = $this->Finance_erp_model->get_category_list($post_data);
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	public function save_category_post()
	{
		$this->form_validation->set_rules('name', 'name', 'trim|required');
		$this->form_validation->set_rules('type', 'type', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
	  	$post_data = $this->post();
	  	$current_date = format_date();
	  	$check_exist = $this->Finance_erp_model->get_single_row('category_id',FINANCE_CATEGORY,array("LOWER(name)" => strtolower($post_data['name']),"type"=>$post_data['type']));
	  	if(!empty($check_exist)){
	  		$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line("erp_category_exists");
			$this->api_response();
	  	}else{
		  	$category_data = array();
		  	$category_data['name'] = $post_data['name'];
		  	$category_data['type'] = $post_data['type'];
		  	$category_data['is_custom'] = 1;
		  	$category_data['added_date'] = $current_date;
		  	$category_data['modified_date'] = $current_date;
			$result = $this->Finance_erp_model->save_category($category_data);
			if ($result)
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
				$this->api_response_arry['message'] = $this->lang->line("erp_category_save_success");
				$this->api_response();
			}
			else
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = $this->lang->line("erp_save_error");
				$this->api_response();
			}
		}
	}

	public function update_category_post()
	{
		$this->form_validation->set_rules('category_id', 'category id', 'trim|required');
		$this->form_validation->set_rules('name', 'name', 'trim|required');
		$this->form_validation->set_rules('type', 'type', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
	  	$post_data = $this->post();
	  	$current_date = format_date();
	  	$check_exist = $this->Finance_erp_model->get_single_row('category_id',FINANCE_CATEGORY,array("LOWER(name)" => strtolower($post_data['name']),"type"=>$post_data['type'],"category_id != "=>$post_data['category_id']));
	  	if(!empty($check_exist)){
	  		$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line("erp_category_exists");
			$this->api_response();
	  	}else{
		  	$category_data = array();
		  	$category_data['name'] = $post_data['name'];
		  	$category_data['type'] = $post_data['type'];
		  	$category_data['modified_date'] = $current_date;
			$result = $this->Finance_erp_model->update_category($category_data,$post_data['category_id']);
			if ($result)
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
				$this->api_response_arry['message'] = $this->lang->line("erp_category_save_success");
				$this->api_response();
			}
			else
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = $this->lang->line("erp_save_error");
				$this->api_response();
			}
		}
	}

	public function get_transaction_list_post()
	{
		$post_data = $this->post();
		$result = $this->Finance_erp_model->get_transaction_list($post_data);
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	public function save_transaction_post()
	{
		$this->form_validation->set_rules('category_id', 'category id', 'trim|required');
		$this->form_validation->set_rules('amount', 'amount', 'trim|required');
		$this->form_validation->set_rules('description', 'description', 'trim|required');
		$this->form_validation->set_rules('record_date', 'record date', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
	  	$post_data = $this->post();
	  	$current_date = format_date();

	  	$category_info = $this->Finance_erp_model->get_single_row('category_id,is_custom',FINANCE_CATEGORY,array("category_id" => $post_data['category_id']));
	  	if(!empty($category_info) && $category_info['is_custom'] == "0"){
	  		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['message'] = $this->lang->line("erp_system_category_txn_save_error");
			$this->api_response();
	  	}else{
		  	$txn_data = array();
		  	$txn_data['category_id'] = $post_data['category_id'];
		  	$txn_data['amount'] = $post_data['amount'];
		  	$txn_data['description'] = $post_data['description'];
		  	$txn_data['record_date'] = date("Y-m-d",strtotime($post_data['record_date']));
		  	$txn_data['added_date'] = $current_date;
		  	$txn_data['modified_date'] = $current_date;
			$result = $this->Finance_erp_model->save_transaction($txn_data);
			if ($result)
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
				$this->api_response_arry['message'] = $this->lang->line("erp_txn_save_success");
				$this->api_response();
			}
			else
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = $this->lang->line("erp_save_error");
				$this->api_response();
			}
		}
	}

	public function update_transaction_post()
	{
		$this->form_validation->set_rules('finance_id', 'finance id', 'trim|required');
		$this->form_validation->set_rules('category_id', 'category id', 'trim|required');
		$this->form_validation->set_rules('amount', 'amount', 'trim|required');
		$this->form_validation->set_rules('description', 'description', 'trim|required');
		$this->form_validation->set_rules('record_date', 'record date', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
	  	$post_data = $this->post();
	  	$current_date = format_date();
	  	$category_info = $this->Finance_erp_model->get_single_row('category_id,is_custom',FINANCE_CATEGORY,array("category_id" => $post_data['category_id']));
	  	if(!empty($category_info) && $category_info['is_custom'] == "0"){
	  		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['message'] = $this->lang->line("erp_system_category_txn_save_error");
			$this->api_response();
	  	}else{
		  	$txn_data = array();
		  	$txn_data['category_id'] = $post_data['category_id'];
		  	$txn_data['amount'] = $post_data['amount'];
		  	$txn_data['description'] = $post_data['description'];
		  	$txn_data['record_date'] = date("Y-m-d",strtotime($post_data['record_date']));
		  	$txn_data['modified_date'] = $current_date;
			$result = $this->Finance_erp_model->update_transaction($txn_data,$post_data['finance_id']);
			if ($result)
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
				$this->api_response_arry['message'] = $this->lang->line("erp_txn_save_success");
				$this->api_response();
			}
			else
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = $this->lang->line("erp_save_error");
				$this->api_response();
			}
		}
	}

	public function delete_transaction_post()
	{
		$this->form_validation->set_rules('finance_id', 'finance id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
	  	$post_data = $this->post();
		$result = $this->Finance_erp_model->delete_transaction($post_data['finance_id']);
		if ($result)
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['message'] = $this->lang->line("erp_txn_delete_success");
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line("erp_save_error");
			$this->api_response();
		}
	}

	public function update_finance_data_get()
	{
		$post_data = $_REQUEST;
		$this->benchmark->mark('code_start');
        $this->Finance_erp_model->update_finance_data($post_data);
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
	}

    public function export_dashboard_data_get(){
        $post_data = $_REQUEST;
        $result_arr = array();
        $result_arr['expenses'] = $this->Finance_erp_model->get_expenses_income_record($post_data,$this->expenses);
		$result_arr['income'] = $this->Finance_erp_model->get_expenses_income_record($post_data,$this->income);

		$balance_info = $this->Finance_erp_model->get_single_row('SUM(balance) as balance,SUM(winning_balance) as winning_balance,SUM(bonus_balance) as bonus_balance',USER,array());
		$pending_withdrawal = $this->Finance_erp_model->get_single_row('SUM(winning_amount) as total',ORDER,array("source"=>"8","type"=>"1","status"=>"0"));
		if(!empty($pending_withdrawal)){
			$balance_info['winning_balance'] = $balance_info['winning_balance'] + $pending_withdrawal['total'];
		}
		$liabilities_category = $this->Finance_erp_model->get_all_table_data('category_id,name,is_custom,0 as total',FINANCE_CATEGORY,array('type'=> $this->liabilities));
		$liabilities_category = array_column($liabilities_category,NULL,"category_id");
		$liabilities_category['13']['total'] = $balance_info['winning_balance'];
		$liabilities_category['14']['total'] = $balance_info['balance'];
		$result_arr['liabilities'] = array_values($liabilities_category);
		//echo "<pre>";print_r($result_arr);die;
        if(!empty($result_arr)){
			$header = array("Category","Name","Amount");
			$camelCaseHeader = array_map("camelCaseString", $header);
			$report_arr = array();
			foreach($result_arr as $category=>$data_arr){
				foreach($data_arr as $row){
					$tmp_arr = array();
					$tmp_arr['category'] = $category;
					$tmp_arr['name'] = $row['name'];
					$tmp_arr['amount'] = $row['total'];
					$report_arr[] = $tmp_arr;
				}
			}
			$result = array_merge(array($camelCaseHeader),$report_arr);
			$this->load->helper('download');
            $this->load->helper('csv');
            $data = array_to_csv($result);
            $data = html_entity_decode($data);
            $name = 'Finance_ERP_Dashboard.csv';
            force_download($name, $data);
        }
        else{
            $header = "No record found";
			$camelCaseHeader = array_map("camelCaseString", $header);
			$result = array_merge(array($camelCaseHeader),$result_arr);
			$this->load->helper('download');
            $this->load->helper('csv');
            $data = array_to_csv($result);
            $data = html_entity_decode($data);
            $name = 'Finance_ERP_Dashboard.csv';
            force_download($name, $data);
        }
    }

    public function export_transaction_get(){
        $post_data = $_REQUEST;
        //echo "<pre>";print_r($post_data);die;
        $post_data['csv'] = 1;
        $result = $this->Finance_erp_model->get_transaction_list($post_data);
		//echo "<pre>";print_r($result);die;
        if(isset($result['result']) && !empty($result['result'])){
			$header = array("Date","Amount","Description","CategoryType","Category");
			$camelCaseHeader = array_map("camelCaseString", $header);
			$report_arr = array();
			foreach($result['result'] as $row){
				$type = "Expenses";
				if($row['type'] == "1"){
					$type = "Income";
				}else if($row['type'] == "2"){
					$type = "Liabilities";
				}
				$tmp_arr = array();
				$tmp_arr['date'] = date("d-M-Y",strtotime($row['record_date']));
				$tmp_arr['amount'] = $row['amount'];
				$tmp_arr['description'] = $row['description'];
				$tmp_arr['type'] = $type;
				$tmp_arr['category_name'] = $row['category_name'];
				$report_arr[] = $tmp_arr;
			}
			$result = array_merge(array($camelCaseHeader),$report_arr);
			$this->load->helper('download');
            $this->load->helper('csv');
            $data = array_to_csv($result);
            $data = html_entity_decode($data);
            $name = 'Finance_ERP_Transaction.csv';
            force_download($name, $data);
        }
        else{
            $header = "No record found";
			$camelCaseHeader = array_map("camelCaseString", $header);
			$result = array_merge(array($camelCaseHeader),$result_arr);
			$this->load->helper('download');
            $this->load->helper('csv');
            $data = array_to_csv($result);
            $data = html_entity_decode($data);
            $name = 'Finance_ERP_Transaction.csv';
            force_download($name, $data);
        }
    }
}