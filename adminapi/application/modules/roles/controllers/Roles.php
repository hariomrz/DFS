<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Roles_model');
		$_POST = $this->input->post();
		$this->admin_roles_manage($this->admin_id,'admin_role_management');
	}

	/**
	 * [roles_list_post description]
	 * Summary :- Use for get all admin role list
	 * @return [type] [description]
	 */
	public function roles_list_post()
	{
		$result = $this->Roles_model->get_all_admin();
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;
		$this->api_response();
	}

	/**
	 * [get_roles_detail_post description]
	 * Summary :- Use for get single admin details
	 * @param   : admin_id
	 * @return  [type]
	 */
	public function get_roles_detail_post()
	{
		$user_id = $this->post('admin_id'); 
		$result = $this->Roles_model->get_roles_detail_by_id($user_id);
		//get referral user name
		
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;
		$this->api_response();
	}

	/**
	 * [add_roles_post description]
	 * Summary :- Use for insert new admin
	 * @param   : firstname,lastname,email
	 * @return  [type]
	 */
	public function add_roles_post()
	{
		$this->form_validation->set_rules('firstname', 'First Name', 'trim|required|min_length[3]|max_length[30]');
		$this->form_validation->set_rules('lastname', 'Last Name', 'trim|required|min_length[3]|max_length[30]');
		$this->form_validation->set_rules('access_list', 'Admin Roles', 'trim');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|max_length[200]');
		$this->form_validation->set_rules('email', 'Email', 'trim|required');
		$this->form_validation->set_rules('two_fa', '2 FA', 'trim');
		// $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique['.$this->db->dbprefix(ADMIN).'.email]',array(
		// 	'required'      => 'You have not provided %s.',
		// 	'is_unique'     => 'This email id is already registered.'
		// ));
	
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$data = $this->input->post(); 

		
		
		 $email = explode('@', $data['email']);
		 $user_name = isset($email[0])?$email[0]:'';
		 $check_username =  $this->Roles_model->get_single_row(
			 "username,role",
			 ADMIN,
			 array("email"=>$data['email'])
		 );
		 $role = '';
		 switch($check_username['role']){
			case '1':
				$role = "Admin";
				break;
            case '2':
				$role = "Master Distributor";
				break;
			case '3':
				$role = "Distributor";
				break;
			case '4':
				$role = "Agent";
				break;
			default:
				break;
		 }
		 if(!empty($check_username)) {
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = 'This email id is already as '.$role.'-Role';
            $this->api_response(); 
		 }
		$data["username"] = $user_name;
		$admin_role = json_encode($this->post("access_list"));
		//print_r($admin_role);exit;

		$data['status'] = '1'; //Email not verified		
		$data['access_list'] = $admin_role;
		$data['password'] = md5($data['password']);
		
		// $this->db->insert(USER,$data);
		// $insert_id = $this->db->insert_id();
		$insert_id = $this->Roles_model->registration($data);
		if($insert_id)
		{	
			$this->api_response_arry['service_name']	= 'add_user';
			$this->api_response_arry['message']			= 'User added successfully!';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']			= array('id'=>$insert_id);
			$this->api_response();	
		}

		$this->api_response_arry['service_name']	= 'add_user';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		$this->api_response_arry['global_error']	= "User not added";
		$this->api_response();	

	}


	/**
	 * [update_roles_post description]
	 * Summary :- Use for update admin info
	 * @param   : admin_id,firstname,lastname
	 * @return  [type]
	 */
	public function update_roles_post()
	{
		$data = array();
		$this->form_validation->set_rules('firstname', 'First Name', 'trim|required|min_length[3]|max_length[30]');
		$this->form_validation->set_rules('lastname', 'Last Name', 'trim|required|min_length[3]|max_length[30]');

		$admin_id = $this->input->post("admin_id");

		$get_admin_details = $this->Roles_model->get_roles_detail_by_id($admin_id);
		
		if(empty($get_admin_details)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = 'Admin id not exists.';
            $this->api_response();

		}


		if($get_admin_details['email'] != $this->input->post('email')){
			//$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique['.$this->db->dbprefix(ADMIN).'.email]');
			$data["email"] = $this->input->post("email");

			$check_username =  $this->Roles_model->get_single_row(
			 "email",
			 ADMIN,
			 array("email"=>$data["email"])
			 );
			if(!empty($check_username)) {
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	            $this->api_response_arry['error'] = array();
	            $this->api_response_arry['global_error'] = 'This email id is already as Admin-Role';
	            $this->api_response(); 
			 }
		}
		
		$this->form_validation->set_rules('access_list', 'Access List', 'trim');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		
		$admin_role = json_encode($this->post("access_list"));
		
		$data["firstname"]			= $this->input->post("firstname");
		$data["lastname"]			= $this->input->post("lastname");
		
		$data["access_list"]		= $admin_role;
		$data["two_fa"]			= $this->input->post("two_fa");
		if(!empty($this->input->post('password'))){
			$data['password'] = md5($this->input->post('password'));
		}

		// print_r($data); die;
		$this->db->where('admin_id',$admin_id)->update(ADMIN,$data);
		$insert_id = $this->db->affected_rows();

		$this->api_response_arry['service_name']	= 'update_roles';
		$this->api_response_arry['message']			= 'Role Updated successfully!';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= array('admin_id'=>$admin_id);
		$this->api_response();	
	}

	/**
	 * [admin_roles_key_post description]
	 * Summary :- List of all roles
	 * @return  [type]
	 */
	public function admin_roles_key_post(){
		//echo "fsfdsf";exit;
		$result = get_sub_admin_menu_keys($this->app_config);
		$this->api_response_arry['service_name']	= 'admin_roles_key';
		$this->api_response_arry['message']			= 'Admin Roles';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $result;
		$this->api_response();	
	}

	/**
	 * [delete_roles_post description]
	 * Summary :- Use for delete admin
	 * @param   : admin_id
	 * @return  [type]
	 */
	public function delete_roles_post()
	{
		$this->form_validation->set_rules('admin_id', 'Admin ID', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$admin_id = $this->input->post("admin_id");

		$check_admin_role =  $this->Roles_model->get_single_row(
			 "admin_id",
			 ADMIN,
			 array("admin_id"=>$admin_id,"role"=>1)
		 );

	 	if(empty($check_admin_role)) {
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = 'Admin Role not Exists.';
            $this->api_response(); 
		}

		$admin_key_array = $this->Roles_model->get_admin_last_active_key($admin_id);
		//print_r($admin_key_array);exit;
		$this->load->model("auth/Auth_nosql_model");
		if(!empty($admin_key_array)){
			foreach ($admin_key_array as $key => $value) {
				$this->Auth_nosql_model->delete_nosql(ADMIN_ACTIVE_LOGIN, array(AUTH_KEY => $value['key']));
			}
			$this->Roles_model->delete_active_role_login($admin_id);
		}else{
			$this->Roles_model->delete_active_role_login($admin_id);
		}	


		$this->api_response_arry['service_name']	= 'delete_roles';
		$this->api_response_arry['message']			= 'Role Deleted successfully!';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= "";
		$this->api_response();	
	}

}
/* End of file User.php */
/* Location: ./application/controllers/Role.php */
