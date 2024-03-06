<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Common_Api_Controller{

	public function __construct()
	{
		parent::__construct();
		$_POST				= $this->post();
		$this->load->model('Dashboard_model');
		// $this->admin_roles_manage($this->admin_id,'dashboard');
	}


    public function get_siterake_post()
	{	
		$post = $this->input->post();
		$result =  $this->Dashboard_model->get_siterake($post);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'information get successfully';
		$this->api_response_arry['data']			= $result['data'];
		$this->api_response();
	}

    public function get_freepaidusers_post()
	{
		$post = $this->input->post();
		$result =  $this->Dashboard_model->get_freepaidusers($post);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'information get successfully';
		$this->api_response_arry['data']		= $result['data'];
		
		$this->api_response();
	}

}