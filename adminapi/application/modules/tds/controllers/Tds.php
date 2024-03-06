<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tds extends MYREST_Controller {

	public $doc_id_txt = "PAN Card";
	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();

		if((!$this->app_config['allow_tds']['key_value'] && !$this->app_config['allow_tds']['key_value'])){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "TDS module is off please contact to admin.";
			$this->api_response();
		}
		if($this->app_config['int_version']['key_value']){
			$this->doc_id_txt = "ID Card";
		}
	}

	/**
     * Used for get filter data list
     * @param array $post_data
     * @return array
     */
	public function get_filter_list_post(){
		$post_data = $this->input->post();
		$tds_info = $this->app_config['allow_tds']['custom_data'];
		$filter = array();
		$filter['fy'] = get_financial_years();
		$filter['type'] = array();
		$filter['type'][] = array("id"=>"1","name"=>"All");
		$filter['type'][] = array("id"=>"2","name"=>$this->doc_id_txt." Available");
		$filter['type'][] = array("id"=>"3","name"=>$this->doc_id_txt." Missing");
    	$filter['tds_type'] = array();
		$filter['tds_type'][] = array("id"=>"1","name"=>"All");
		if(isset($tds_info['indian']) && $tds_info['indian'] == "1"){
			$filter['tds_type'][] = array("id"=>"2","name"=>"Withdrawal");
			$filter['tds_type'][] = array("id"=>"3","name"=>"FY Settlement");
        }else{
        	$filter['tds_type'][] = array("id"=>"4","name"=>"Winning TDS");
        }
		$this->api_response_arry['data'] = $filter;
		$this->api_response();
	}

	/**
     * Used for get report data list
     * @param array $post_data
     * @return array
     */
	public function get_report_post(){
		$this->form_validation->set_rules('fy','Financial Year','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
		$post_data = $this->input->post();
		if(empty($post_data['format_date']) && empty($post_data['to_date']) && !empty($post_data['fy'])){
			$fy = get_financial_years();
			$post_data['format_date'] = $fy[$post_data['fy']]['start'];
			$post_data['to_date'] = $fy[$post_data['fy']]['end'];
		}

		$table_field = array('user_name'=>"User Name",'name'=>"Full Name",'email'=>"Email","type"=>"Type",'amount'=>"Txn Amount",'net_winning'=>"Net Winning",'tds_rate'=>"TDS Rate(%)",'tds'=>'TDS Deduction','txn_amount'=>"Bank/Wallet Credit",'pan_no'=>$this->doc_id_txt,'section'=>"Section",'date_added'=>"Date/Time");
		$this->load->model('tds/Tds_model');
		$report_list = $this->Tds_model->get_tds_report($post_data);
		$result = array();
		$result['table_field'] = $table_field;
		$result['result'] = $report_list['result'];
		$result['total'] = $report_list['total'];

		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	/**
     * Used for download tds reports
     * @param array $get_data
     * @return array
     */
	public function get_report_get(){
		$post_data = $this->input->get();
		if(!isset($post_data['csv']))
		{
			$post_data["csv"] = TRUE;
		}
		if(empty($post_data['format_date']) && empty($post_data['to_date']) && !empty($post_data['fy'])){
			$fy = get_financial_years($post_data['fy']);
			$post_data['format_date'] = $fy[$post_data['fy']]['start'];
			$post_data['to_date'] = $fy[$post_data['fy']]['end'];
		}

		$this->load->model('tds/Tds_model');
		$report_list = $this->Tds_model->get_tds_report($post_data);
		//echo "<pre>";print_r($report_list);die;
		$name = 'tds_report_'.format_date('today', 'Y-m-d').'.csv';
		if(!empty($report_list['result']))
		{
			$table_field = array('user_name'=>"User Name",'name'=>"Full Name",'email'=>"Email","type"=>"Type",'fy'=>"FY",'amount'=>"Withdrawal Amount",'net_winning'=>"Net Winning",'tds_rate'=>"TDS Rate(%)",'tds'=>'TDS Deduction','txn_amount'=>"Bank/Wallet Credit",'pan_no'=>$this->doc_id_txt,'section'=>"Section",'date_added'=>"Date/Time");
			$header = array(array_values($table_field));
			$report_data = array_merge($header,$report_list['result']);

			$this->load->helper('download');
			$this->load->helper('csv');
			$data = array_to_csv($report_data);
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
     * Used for get tds document list
     * @param array $post_data
     * @return array
     */
	public function get_tds_document_post(){
		$this->form_validation->set_rules('fy','Financial Year','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

		$post_data = $this->input->post();
		$table_field = array('user_name'=>"User Name",'name'=>"Full Name",'gov_id'=>$this->doc_id_txt,"phone_no"=>"Mobile",'fy'=>'FY','date_added'=>"Updated Date");
		$this->load->model('tds/Tds_model');
		$record_list = $this->Tds_model->get_tds_document($post_data);
		$result = array();
		$result['table_field'] = $table_field;
		$result['result'] = $record_list['result'];
		$result['total'] = $record_list['total'];
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	/**
     * Used for delete uploaded tds document
     * @param array $post_data
     * @return array
     */
	public function delete_tds_document_post(){
		$this->form_validation->set_rules('id','Document ID','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

		$post_data = $this->input->post();
		$id = $post_data['id'];
		$this->load->model('tds/Tds_model');
		$result = $this->Tds_model->delete_tds_document($id);
		if($result){
			$this->api_response_arry['data'] = array();
			$this->api_response_arry['message'] = "Document deleted successfully.";
			$this->api_response();
		}else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Some thing went wrong while delete document. please try again.";
			$this->api_response();
		}
	}

	/**
     * Used for save tds document
     * @param array $post_data
     * @return array
     */
	public function save_tds_document_post(){
		$this->form_validation->set_rules('gov_id','Gov ID','trim|required');
		$this->form_validation->set_rules('fy','Financial Year','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

		$post_data = $this->input->post();
		if(empty($post_data['document'])){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Please upload atleast one document.";
			$this->api_response();
		}
		$post_data['gov_id'] = strtoupper($post_data['gov_id']);
		$this->load->model('tds/Tds_model');
		$user_info = $this->Tds_model->get_single_row('user_id,pan_no',USER,array('UPPER(pan_no)' => $post_data['gov_id'],"pan_verified"=>"1"));
		if(empty($user_info)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Provided ".$this->doc_id_txt." does not exist in our system ";
			$this->api_response();
		}
		$user_id = $user_info['user_id'];
		$success = 0;
		$error = array();
		foreach($post_data['document'] as $file_name){
			$tmp_arr = array();
			$tmp_arr['user_id'] = $user_id;
			$tmp_arr['fy'] = $post_data['fy'];
			$tmp_arr['gov_id'] = $post_data['gov_id'];
			$tmp_arr['file_name'] = $file_name;
			$tmp_arr['date_added'] = format_date();
			$result = $this->Tds_model->save_tds_document($tmp_arr);
			if($result){
				$success = 1;
			}else{
				$error[] = $file_name;
			}
		}

		if($success){
			if(!empty($error)){
				$err_msg = "There is some problem to save record of ".implode(",",$error)." Document(s)";
				$this->api_response_arry['error'] = $err_msg;
			}
			$this->api_response_arry['data'] = array();
			$this->api_response_arry['message'] = "Document saved successfully.";
			$this->api_response();
		}else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Some thing went wrong while save tds document. please try again.";
			$this->api_response();
		}
	}
}
/* End of file Report.php */
/* Location: ./application/controllers/Gst.php */