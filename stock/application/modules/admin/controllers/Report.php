<?php

class Report extends Common_Api_Controller {

	function __construct() {
        parent::__construct();        
	}

    /**
     * Used to export report and send it on mail
     */
    function export_report_post() {
        $this->form_validation->set_rules('report_type','report_type','trim|required');
 
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data  =  $this->post();
      	
      	$this->load->helper('queue_helper');
        add_data_in_queue($post_data,'stock_admin_reports');

		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['message']			= 'Request submitted, you will receive an email shortly once processed';
		$this->api_response();
	}
}