<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Shorturl extends MYREST_Controller
{
	function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
		$this->load->model("Shorturl_model");
	}

	public function index_get()
	{
		$this->response(array(config_item('rest_status_field_name')=>FALSE), rest_controller::HTTP_NOT_FOUND);
	}

	public function index_post()
	{
		$this->response(array(config_item('rest_status_field_name')=>FALSE), rest_controller::HTTP_NOT_FOUND);
	}

	public function get_shortened_url_post()
	{
		
		$res = $this->Shorturl_model->get_shortened_url();

		$result = array(); 
		$result["short_urls"] = $res; 

		$this->api_response_arry['response_code']	= 200;
		$this->api_response_arry['service_name']	= 'get_shortened_url';
		$this->api_response_arry['data']			= $result;
		$this->api_response_arry['message']			= "";
		$this->api_response();
	}


	public function get_shortened_url_by_id_post()
	{
		$this->form_validation->set_rules('shortened_id', 'shortened_id', 'trim|required');

		if (!$this->form_validation->run())
		{
			$this->send_validation_errors();
		}

		$res = $this->Shorturl_model->get_shortened_url_by_id();
		$result = array();
		$result["short_url"] = $res; 

		$this->api_response_arry['response_code']	= 200;
		$this->api_response_arry['service_name']	= 'get_shortened_url';
		$this->api_response_arry['data']			= $result;
		$this->api_response_arry['message']			= "";
		$this->api_response();
	}


	public function save_shortened_url_post()
	{
		
		$res = $this->Shorturl_model->save_shortened_url();

		$result = array(); 
		$result["short_urls"] = $res; 

		$this->api_response_arry['response_code']	= 200;
		$this->api_response_arry['service_name']	= 'get_shortened_url';
		$this->api_response_arry['data']			= $result;
		$this->api_response_arry['message']			= "";
		$this->api_response();
	}


}