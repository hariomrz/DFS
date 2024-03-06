<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Social extends MYREST_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('Social_model');
		$_POST = $this->input->post();	

		$allow_social = isset($this->app_config['allow_social'])?$this->app_config['allow_social']['key_value']:0;
       
        if(!$allow_social) {
            
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['global_error']  ="Module not activated." ;
            $this->api_response();
        }
	}
	
	public function index() {
		//$this->load->view('layout/layout', $this->data, FALSE);
	}

	function login_post(){
		$admin_user_id = $this->app_config['allow_social']['custom_data']['admin_user_id']? $this->app_config['allow_social']['custom_data']['admin_user_id']:0;

		$api_key = $this->Social_model->login($admin_user_id);		
		 if(empty($api_key)) {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= 'Social admin user not exist';
            $this->api_response();
        } else {
			$website_url = $this->app_config['allow_social']['custom_data']['website_url']? $this->app_config['allow_social']['custom_data']['website_url']:0;
			//$response[AUTH_KEY] = $api_key;
			$response['Url'] = $website_url.'admin/login/auth/'.$api_key;
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']			= $response;
		}	
		
		$this->api_response();	
	}

}
/* End of file Social.php */