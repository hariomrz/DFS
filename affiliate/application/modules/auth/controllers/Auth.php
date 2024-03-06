<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends Common_Api_Controller {

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->post();
		
		$this->load->model('Auth_model');
		//Do your magic here
	}

	/**
	 * [dologin description]
	 * @MethodName dologin
	 * @Summary This function used to login user into the system
	 * @return   boolean
	 */
	public function dologin_post()
	{
		$this->form_validation->set_rules('email', 'Login', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$data = $this->Auth_model->affiliate_login($post_data['email'], $post_data['password']);
		//echo "<pre>";print_r($data);die;
		if ($data != NULL)
		{
			if ($data['status'] == 2) {
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = 'Your account is inactive!';
				$this->api_response();	
			}
			
			$current_date = format_date();
			$input_str = $this->auth_key_role."_".$data['affiliate_id']."_".$data['name']."_".$data['email']."_".$current_date;
			$key = generate_data_hash($input_str,"e");
			$key = rtrim($key,"=");
			$data[AUTH_KEY] = $key;
			
			$login_data = array(
				"user_id"=>$data['affiliate_id'],
				"key"=>$key,
				"role"=>$this->auth_key_role,
				"date_created"=>format_date()
			);
			$this->Auth_model->save_key($login_data);

			$this->api_response_arry['data'] = array(AUTH_KEY=>$key);
			$this->api_response_arry['data']['role'] = $this->auth_key_role;
			$this->api_response_arry['data']['admin_id'] = $data['affiliate_id'];
			$this->api_response_arry['data']['name'] = $data['name'];
			$this->api_response_arry['message'] = "Login successfully";
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] ="Incorrect credentials!";
			$this->api_response();
		}
	}

	/**
	 * @Summary function to logout affiliate user
	 * @MethodName logout
	 * @return   boolean
	 */
	public function logout_post() 
	{
		$key = $this->input->get_request_header(AUTH_KEY);
		
        if(!$key){
        	$key = $this->input->get_request_header(strtolower(AUTH_KEY));
        }
		$this->Auth_model->delete_active_login_key($key);

		$this->auth_key_role	= "";
		//$this->session->sess_regenerate();
		return TRUE;
	}
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */