<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Point_system extends MYREST_Controller{

	public function __construct()
	{
		parent::__construct();
		$_POST				= $this->input->post();
		$this->load->model('Point_system_model');
		$this->admin_lang = $this->lang->line('ps');

		//Do your magic here
	}

	public function create_ps_post()
	{	
	  	$this->form_validation->set_rules('description', 'description', 'trim|required');
		$this->form_validation->set_rules('points', 'points', 'trim|required|is_natural_no_zero');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
		$data_post			= $this->post();
		$post_target_url	= 'point_system/create_ps';
		$result		= $this->http_post_request($post_target_url,$data_post,2);

		if ($result['response_code'] == rest_controller::HTTP_OK)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['message']			= $result['message'];
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code']	= $result['response_code'];
			$this->api_response_arry['message']			= $result['message'];
			$this->api_response();
		}
	}



	public function get_point_system_post()
	{
		$data_post = $this->post();
		$post_target_url	= 'point_system/get_point_system';
		$data		= $this->http_post_request($post_target_url,$data_post,2);
		$this->api_response_arry	= $data;
		$this->api_response();
	}

	public function update_status_post()
	{		
		$this->form_validation->set_rules('status', 'status', 'required');
		$this->form_validation->set_rules('point_system_id', 'point_system_id', 'trim|required');
		
		if(!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$data_post = $this->post();
		$post_target_url	= 'point_system/update_status';
		$data				= $this->http_post_request($post_target_url,$data_post,2);

		$this->api_response_arry = $data;
		$this->api_response();
	}
	

	public function change_ps_value_post()
	{		
		$this->form_validation->set_rules('points', 'points', 'required');
		$this->form_validation->set_rules('point_system_id', 'point_system_id', 'trim|required');
		
		if(!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$data_post = $this->post();
		$post_target_url	= 'point_system/change_ps_value';
		$data				= $this->http_post_request($post_target_url,$data_post,2);

		$this->api_response_arry = $data;
		$this->api_response();
	}

	public function get_point_value_post()
	{
		$data_post = $this->post();
		$post_target_url	= 'point_system/get_point_value';
		$data		= $this->http_post_request($post_target_url,$data_post,2);
		$this->api_response_arry	= $data;
		$this->api_response();
	}

	public function ps_detail_post()
	{
		$data_post = $this->post();
		$post_target_url	= 'point_system/ps_detail';
		$data		= $this->http_post_request($post_target_url,$data_post,2);
		$this->api_response_arry	= $data;
		$this->api_response();
	}

	public function update_point_value_post()
	{		
		$this->form_validation->set_rules('real_money', 'real_money', 'required');
		$this->form_validation->set_rules('coin_value', 'coin_value', 'trim|required');
		
		if(!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
		$data_post = $this->post();
		$post_target_url	= 'point_system/update_point_value';
		$data				= $this->http_post_request($post_target_url,$data_post,2);

		$this->api_response_arry = $data;
		$this->api_response();
	}

}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */