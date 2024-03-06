
<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'/libraries/REST_Controller.php';

class CommonDashboard extends MYREST_Controller {
    
	public function __construct()
	{

		parent::__construct();
		//$this->load->model('Dashboard_model'); 
		$_POST = $this->input->post();
		$this->admin_roles_manage($this->admin_id,'marketing');				
	}

	/*public function index()
	{
		$this->load->view('layout/layout', $this->data, FALSE);
	}*/

	public function get_summary_post()
	{	
        
		$post = $this->input->post();
		//$response = $this->Dashboard_model->get_summary($post);

		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'get info';
		$this->api_response_arry['data']			= array();
		//print_r($this->api_response_arry); die; 

		$this->api_response();
	
	}

}
