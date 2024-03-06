<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report extends Common_Api_Controller{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/Report_model');

	}

      /**
     * Used for get dfs season list 
     * @param array $post_data
     * @return json array
     */
    public function get_opinion_report_post()
    {   
        $post_data = $this->input->post();   
        $match_list = $this->Report_model->get_opinion_report($post_data);
        // echo "<pre>";print_r($match_list);die;
        $this->api_response_arry['data'] = $match_list;
        $this->api_response();
    }

    public function get_opinion_report_get(){
		$_POST =  $this->input->get();
		$_POST["csv"] = TRUE;
		$result = array();
		$revenew_result = array();		// print_r(); die;
		$total = 0;
		$userData = $this->Report_model->get_opinion_report($_POST);	
		
		if ($userData['result']) {
		$result = $userData["result"];
		$header = array_keys($result[0]);
        $camelCaseHeader = array_map("camelCaseString", $header);
        $result = array_merge(array($camelCaseHeader),$result);

			$this->load->helper('download'); 
			$this->load->helper('csv');
			$data = array_to_csv($result);
			//$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "From Date $from_date\nTo Date $to_date\n\n" . html_entity_decode($data);
			$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
			$name = 'opinion_report.csv';
			force_download($name, $data);
		}else{
			$result = "no record found";
			$this->load->helper('download');
			$this->load->helper('csv');
			$data = array_to_csv($result);
			$name = 'opinion_report.csv';
			force_download($name, $result);
		}
	}

  

	



}