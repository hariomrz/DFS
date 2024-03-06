<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Distributor extends MYREST_Controller {
	public function __construct()
	{
		parent::__construct();
		//check collection module enabled or not
		$allow_distributor =  isset($this->app_config['allow_distributor'])?$this->app_config['allow_distributor']['key_value']:0;
		if($allow_distributor == 0)
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Distributor not enabled";
			$this->api_response();
		}	
		$this->load->model('Distributor_model'); 
		$this->admin_lang = $this->lang->line('distributor');
		$this->admin_roles_manage($this->admin_id,'distributors');
	}
	/*
	* function : add_admin
	* def: add admin 
	* @params : $array
	* @return : array admin
	*/
	public function add_admin_post()
	{
		/*$this->form_validation->set_rules('firstname', 'First Name', 'trim|required');
		$this->form_validation->set_rules('lastname', 'Last Name', 'trim|required'); */
		$this->form_validation->set_rules('fullname', 'Full Name', 'trim|required');
		
		if(!$this->input->post('admin_id')){
		
		$this->form_validation->set_rules('email', 'Email', 'trim|required');
		$this->form_validation->set_rules('mobile', 'Mobile Number', 'trim|required');

		// $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique['.$this->db->dbprefix(ADMIN).'.email]',array(
		// 	'required'      => 'You have not provided %s.',
		// 	'is_unique'     => 'This %s already exists.'
		// 	));
		
		// $this->form_validation->set_rules('mobile', 'Mobile Number', 'trim|is_unique['.$this->db->dbprefix(ADMIN).'.mobile]',array(
		// 	'required'      => 'You have not provided %s.',
		// 	'is_unique'     => 'This %s already exists.'
		// 	));
		}

		$this->form_validation->set_rules('address', 'Address', 'trim|required');
		$this->form_validation->set_rules('city', 'City', 'trim|required');
		$this->form_validation->set_rules('state_id', 'State', 'trim|required');
		$this->form_validation->set_rules('role', 'Type', 'trim|required');
		$this->form_validation->set_rules('commission_percent', 'Commission Percent', 'trim|required|greater_than[0]|less_than_equal_to[100]');


		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$data = $this->input->post(); 
		if(!$this->input->post('admin_id')){
			 $email = explode('@', $data['email']);
			 $username = isset($email[0])?$email[0]:'';
			// $check_username =  $this->Distributor_model->get_single_row(
			// 	 "username,role",
			// 	 ADMIN,
			// 	 array("email"=>$data['email']," or mobile"=>$data['mobile'])
			//  );
			 $check_username = $this->Distributor_model->check_username();
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

			$data['added_date']     = date('Y-m-d H:i:s');
			
			$data['status']              = '1'; //Email not verified	
			$password = random_password( $length = 8, $characters = true, $numbers = true, $case_sensitive = true, $hash = true );
			$data['password'] =md5(md5($password));	
			$data['username'] =$username;	
			$data['balance'] =0;	
			$data['country_id'] =101;
			$data['access_list'] ='["distributors"]';
		}

		$insert_id = $this->Distributor_model->add_admin($data);
		
		if($insert_id)
		{	
			
			$user_data = $this->Distributor_model->get_admin_by_id($insert_id);
			
			/* $sms_data = array();
			$sms_data['mobile'] = $user_data['mobile'];
			$sms_data['phone_code'] = '+91'; //$user_data['phone_code'];
			$sms_data['message'] ='You are registered as a admin on ' . WEBSITE_URL;
			$this->load->helper('queue_helper');
			add_data_in_queue($sms_data, 'sms'); */

			/* Send Email Notifications*/
			if(!$this->input->post('admin_id')){
				$content = array(
					"email" => $data['email'],
					"username" => $data["username"],
					//"password" => $data['password'],
					"role" => $data['role'],
					"site_url"  => BASE_APP_PATH."admin/",
					"password"  => $password,
					"post_date" => format_date('today', 'd-M-Y h:i A')
				);

				$email_content                      = array();
				$email_content['email']             = $data['email'];
				$email_content['username']         = $data["username"];
				$email_content['subject'] 			= $this->lang->line('welcome_email_subject');
				$email_content['notification_type'] = '301';
				$email_content['content']           = $content;
				$this->load->helper('queue_helper');
				add_data_in_queue($email_content, 'email');
			}
			$this->api_response_arry['service_name']	= 'add_user';
			if(!$this->input->post('admin_id')){

				$this->api_response_arry['message']			= 'Admin added successfully!';
			} else {
				$this->api_response_arry['message']			= 'Admin updated successfully!';
			}
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']			= array('id'=>$insert_id);
			$this->api_response();	
		} else {
			$this->api_response_arry['service_name']	= 'add_user';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error']	= "Admin not added";
			$this->api_response();	
		}	
	}
	/*
	* function : get_admin_list
	* def: get_admin_list
	* @params : $array
	* @return : array admin list
	*/
	public function get_admin_list_post()
	{
		$this->form_validation->set_rules('created_by', 'created by', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}	
		
		$user_data = $this->Distributor_model->get_admin_list($is_total=false);
		
			$this->api_response_arry['service_name']	= 'get_admin_list';
			$this->api_response_arry['message']			= 'information get successfully!';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']['admin_list']			= $user_data;
			$this->api_response_arry['data']['total']	= $this->Distributor_model->get_admin_list($is_total=true);
			$this->api_response();	
		

	}

	public function get_admin_list_get(){
		$_POST =  $this->input->get();
		$result = $this->Distributor_model->get_admin_list($is_total=false);
		if(!empty($result)){

				$report_array = array();
				foreach ($result as $key => $value) {
					$report_array[$key]['email'] = $value['email'];
					$report_array[$key]['fullname'] = $value['fullname'];
					$report_array[$key]['username'] = $value['username'];
					$report_array[$key]['mobile'] = $value['mobile'];
					$report_array[$key]['city'] = $value['city'];
					$report_array[$key]['commission_percent'] = $value['commission_percent'];
					$report_array[$key]['balance'] = $value['balance'];

					$role = "";
					switch ($value['role']) {
						case 1:
							$role ="admin";
							break;
						case 2:
							$role ="master-distributor";
							break;
						case 3:
							$role ="distributor";
							break;
						case 4:
							$role ="agent";
							break;
						default:
							# code...
							break;
					}

					$report_array[$key]['role'] = $role;
				}

				$header = array_keys($report_array[0]);
				$camelCaseHeader = array_map("camelCaseString", $header);
				$report_array = array_merge(array($camelCaseHeader),$report_array);
				$this->load->helper('csv');
				array_to_csv($report_array,'Admin_list.csv');

		}
		else{
			$result[0]['result']= "no record Found";
			$header = array_keys($result[0]);
			$camelCaseHeader = array_map("camelCaseString", $header);
			$result = array_merge(array($camelCaseHeader),$result);
			$this->load->helper('csv');
			array_to_csv($result,'Admin_list.csv');
	}

	}

	/*
	* function : do_recharge
	* def: do_recharge
	* @params : $array
	* @return : array admin list
	*/
	public function do_recharge_post()
	{	  
		$this->form_validation->set_rules('amount', 'Amount', 'trim|required');
		$this->form_validation->set_rules('to_admin_id', 'to_admin_id', 'trim|required');
		$this->form_validation->set_rules('from_admin_id', 'from_admin_id', 'trim|required');
		$this->form_validation->set_rules('upload_reciept', 'Upload reciept', 'trim|required');
		//reference_id optional
		//status =0
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}	

		$data = $this->input->post(); 
		$data['created_date']     = date('Y-m-d H:i:s');
		$data['status']              = '0'; //pending request will mark as approved later	
		//function to do_recharge
		$user_data = $this->Distributor_model->do_recharge($data);
		
		$this->api_response_arry['service_name']	= 'do_recharge';
		$this->api_response_arry['message']			= 'Recharge requested successfully!';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $user_data;
		$this->api_response();	
		

	}
	/*
	* function : do_upload
	* def: upload reciept
	* @params : $array
	* @return : array 
	*/
	function do_upload_post() 
	{
		$data_post			= $this->post();
		$file_field_name	= 'userfile';
		$dir				= ROOT_PATH.RECHARE_SLIP_IMAGE_DIR;
		$s3_dir				= RECHARE_SLIP_IMAGE_DIR;
		if(empty($_FILES['userfile'])) {
			
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $this->admin_lang['userfile_empty'];
			$this->api_response();

		}
		$temp_file	= $_FILES['userfile']['tmp_name'];
		$ext		= pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
		$vals		= @getimagesize($temp_file);
		$width		= $vals[0];
		$height		= $vals[1];
		
		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
		}
		
		$file_name = time() . "." . $ext;
		$filePath     = $s3_dir.$file_name;
		
		//Start amazon server upload code
		if (strtolower(IMAGE_SERVER) == 'remote')
		{
			try{
	            $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		            $return_array = array('image_url'=>  IMAGE_PATH.$filePath,'file_name'=> $file_name);
		            $this->api_response_arry['data'] = $return_array;
		            $this->api_response();
                }
	        }catch(Exception $e){
	            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response();
	        }
		}
		else
		{
			$config['allowed_types'] = 'jpg|png|jpeg|gif';
			$config['max_size'] = '4048'; //204800
			$config['max_width'] = '1024';
			$config['max_height'] = '1000';
			$config['upload_path'] = $dir;
			$config['file_name'] = $file_name;

			$this->load->library('upload', $config);

			if (!$this->upload->do_upload($file_field_name))
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']		   = strip_tags($this->upload->display_errors());
				$this->api_response();
			}
			else
			{
				$uploaded_data = $this->upload->data();
				$image_path =  RECHARE_SLIP_IMAGE_DIR . $uploaded_data['file_name'];
				
				$data = array(
							'image_url'=> $image_path,
							'file_name'=> $uploaded_data['file_name']
					);
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']			= $data;
				$this->api_response();
				
			}
			
		}		
	}

	/*
	* function : get_recharge_list
	* def: get recharge list
	* @params : $array
	* @return : array 
	*/
	public function get_recharge_list_post()
	{
		
		//function to get recharge list
		$user_data = $this->Distributor_model->get_recharge_list($is_total=false);
			
			$this->api_response_arry['service_name']	= 'get_recharge_list';
			$this->api_response_arry['message']			= 'information get successfully!';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']["transactionList"]			= $user_data;
			$this->api_response_arry['data']['total']	= $this->Distributor_model->get_recharge_list($is_total=true);
			$this->api_response();	

	}

	public function get_recharge_list_get(){
		$_POST =  $this->input->get();
		$result = $this->Distributor_model->get_recharge_list($is_total=false);
		if(!empty($result)){
				$header = array_keys($result[0]);
				$camelCaseHeader = array_map("camelCaseString", $header);
				$result = array_merge(array($camelCaseHeader),$result);
				$this->load->helper('csv');
				array_to_csv($result,'Transaction_list.csv');
		}
		else{
			$result[0]['result']= "no record Found";
			$header = array_keys($result[0]);
			$camelCaseHeader = array_map("camelCaseString", $header);
			$result = array_merge(array($camelCaseHeader),$result);
			$this->load->helper('csv');
			array_to_csv($result,'Transaction_list.csv');
	}

	}


	/*
	* function : approve_recharge
	* def: approve_recharge
	* @params : $array
	* @return : boolean
	*/
	public function approve_recharge_post()
	{	  
		$this->form_validation->set_rules('recharge_id', 'Recharge id', 'trim|required');
		$this->form_validation->set_rules('to_admin_id', 'To admin id', 'trim|required');


		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}	
		$data = $this->input->post(); 
		$admin_id =  $data['to_admin_id'];
		$input =array();
		$input['status'] = 1; 
		$recharge_id = $data['recharge_id']; 
		$recharge = $this->Distributor_model->get_recharge_by_id($recharge_id);
		
		$response =array();
		if(!empty($recharge)) {
			if($recharge['to_admin_id'] != $data['to_admin_id']) { //check owner
				
				$this->api_response_arry['service_name']	= 'do_recharge';
				$this->api_response_arry['message']			= 'You are not owner of this recharge.';
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response();	
			
			}
			
			if($recharge['status']==0) {
				
				$admin_detail  = $this->Distributor_model->get_admin_by_id($recharge['to_admin_id']);
				
				if(($admin_detail['balance']<$recharge['amount'] ) && $admin_detail['role'] >1  ) {  //check admin balance 
					$this->api_response_arry['service_name']	= 'approve_recharge';
					$this->api_response_arry['message']			= 'You do not have sufficient balance for this recharge.!';
					$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
					$this->api_response();


				}
				$response['recharge_data'] = $this->Distributor_model->approve_recharge($input,$recharge_id);
				 
				$ub = $this->Distributor_model->update_balance($recharge['from_admin_id'],$recharge['amount'],'credit');
				if($admin_id !="1") { //not for superadmin
					$this->Distributor_model->update_balance($admin_id,$recharge['amount'],'debit');
				}
				
				$this->api_response_arry['service_name']	= 'do_recharge';
				$this->api_response_arry['message']			= 'Recharge done successfully!';
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']			= $response;
				$this->api_response();
				
			} else {

				$this->api_response_arry['service_name']	= 'do_recharge';
				$this->api_response_arry['message']			= 'This recharge allready done!';
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response();	

			}	
		} else {

		$this->api_response_arry['service_name']	= 'do_recharge';
		$this->api_response_arry['message']			= 'Record not found!';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		$this->api_response();	


		}
	
	}

	/*
	* function : change_status
	* def: change_status
	* @params : $array
	* @return : boolean
	*/
	public function change_status_post()
	{	  
		$this->form_validation->set_rules('status', 'status', 'trim|required');
		$this->form_validation->set_rules('admin_id', 'admin id', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}	
		$data = $this->input->post(); 
		$admin_id =  $data['admin_id'];
		$input =array();
		$input['status'] = $data['status']; 
		
		$recharge = $this->Distributor_model->change_status($input,$admin_id);
		
			if($recharge) {


				if($data['status']=='0'){
					$admin_key_array = $this->Distributor_model->admin_last_active_key($admin_id);
					$this->load->model("auth/Auth_nosql_model");
					if(!empty($admin_key_array)){
						foreach ($admin_key_array as $key => $value) {
							$this->Auth_nosql_model->delete_nosql(ADMIN_ACTIVE_LOGIN, array(AUTH_KEY => $value['key']));
						}
						$this->Distributor_model->block_distributor_role_login($admin_id);
					}else{
						$this->Distributor_model->block_distributor_role_login($admin_id);
					}
				}
				
				$this->api_response_arry['service_name']	= 'change_status';
				$this->api_response_arry['message']			= 'Status updated successfully!';
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response();
				
			} else {

				$this->api_response_arry['service_name']	= 'change_status';
				$this->api_response_arry['message']			= ' Status not updated!';
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response();	

			}	
	}

	/*
	* function : get_search_user
	* def: get_search_user
	* @params : $array
	* @return : array admin list
	*/
	public function get_search_user_post()
	{	  
		//$this->form_validation->set_rules('keyword', 'keyword', 'trim|required');
		
		/* if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}	 */
		$data = $this->input->post(); 
		$users = $this->Distributor_model->get_search_user($data);
		
		
		if(!empty($users)) {
			
				
				$this->api_response_arry['service_name']	= 'get_search_user';
				$this->api_response_arry['message']			= 'users get successfully!';
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']			= $users;
				$this->api_response();
	
		} else {

				$this->api_response_arry['service_name']	= 'get_search_user';
				$this->api_response_arry['message']			= 'Record not found!';
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response();	


		}
	
	}

	/*
	* function : recharge_user
	* def: recharge_user
	* @params : $array
	* @return : boolean
	*/
	public function recharge_user_post()
	{	  
		$this->form_validation->set_rules('user_unique_id', 'User unique id', 'trim|required');
		$this->form_validation->set_rules('amount', 'Amount', 'trim|required');
		$this->form_validation->set_rules('admin_id', 'admin_id', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}	
		$data = $this->input->post(); 
		$response = array();
		$user_unique_id = $data['user_unique_id'];
		$user = $this->Distributor_model->get_user_by_unique_id($user_unique_id);
		
		if(!empty($user['user_id'])){
			$data['user_id'] = $user['user_id'];
			unset($data['user_unique_id']);
		}

		$admin_detail  = $this->Distributor_model->get_admin_by_id($data['admin_id']);
				
		if(($admin_detail['balance']<$data['amount'] ) && $admin_detail['role'] >1  ) {  //check admin balance 
			$this->api_response_arry['service_name']	= 'approve_recharge';
			$this->api_response_arry['message']			= 'You do not have sufficient balance for this recharge.!';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response();
		}

		$response['recharge_data'] = $this->Distributor_model->recharge_user($data);
		
		//$response["user_data"] = $this->http_post_request('index.php/user/add_user_balance', $post_data, 3);//for debug add true as lasr param

		// Add new code for deposit fund to customer 
		$this->load->model('user/User_model');
		$this->load->model('userfinance/Userfinance_model');

		$user_detail = $this->User_model->get_user_detail_by_id($user_unique_id);
		
		$order_object = array();
		$order_object['user_id']	= $user_detail['user_id'];
		$order_object['user_name']	= $user_detail['user_name'];
		$order_object['email']	= $user_detail['email'];
		$order_object['amount']		= $data['amount'];

		$admin_details = array();
		$first_name = $this->first_name;

		if($first_name!=""){
			$admin_details["first_name"] = $this->first_name;
		}else{
			$admin_details["first_name"] = $this->username;
		}
		
        $order_object['cash_type']  = 0;
        $order_object['source']		= 0;
        $order_object['source_id']	= 0;
        $order_object['plateform']	= 1;
		$order_object['reason']	= "";//ADMIN_USER_NOTI;
		$order_object['custom_data'] = json_encode($admin_details,TRUE);
        $order_id =  $this->Userfinance_model->deposit_fund($order_object);
        // Add new code for deposit fund to customer end


		if($order_id) {
			
			$ubal = $this->Distributor_model->update_balance($data['admin_id'],$data['amount'],'debit');
			if($ubal>0) {
				$this->api_response_arry['service_name']	= 'recharge_user';
				$this->api_response_arry['message']			= 'Recharge done successfully!';
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']			= $response;
				$this->api_response();
			
			} else{

				$this->api_response_arry['service_name']	= 'recharge_user';
				$this->api_response_arry['message']			= 'Issue in update balance!';
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['data']			= $response;
				$this->api_response();
			}
		
		} else {

		$this->api_response_arry['service_name']	= 'recharge_user';
		$this->api_response_arry['message']			= 'Please try again!';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		$this->api_response();	

		}	
		
	
	}

	/**
	 * [get_all_country description]
	 * @MethodName get_all_country
	 * @Summary This function used to get all master country
	 * @return     [type]
	 */

	public function get_all_country_post()
	{	
		$response = $this->Distributor_model->get_all_country();
		$this->api_response_arry['service_name']	= 'get_all_country';
		$this->api_response_arry['message']			= 'Get country list successfully!';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $response;
		$this->api_response();
	}

	/**
	 * [get_all_state_by_country description]
	 * @MethodName get_all_state_by_country
	 * @Summary This function used to get all master states
	 * @return     [type]
	 */
	public function get_all_state_by_country_post()
	{	
		$this->form_validation->set_rules('master_country_id', 'master country id', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}	
		$country = $this->input->post('master_country_id'); 	
		$response = $this->Distributor_model->get_all_state_by_country($country);
		$this->api_response_arry['service_name']	= 'get_all_state_by_country';
		$this->api_response_arry['message']			= 'get states list successfully!';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $response;
		$this->api_response();
	}
	/*
	* function : get_recharge_request_list
	* def: get recharge requestlist
	* @params : $array
	* @return : array 
	*/
	public function get_recharge_request_list_post()
	{
		
		//function to get recharge list
		$user_data = $this->Distributor_model->get_recharge_request_list($is_total=false);
		
			$this->api_response_arry['service_name']	= 'get_recharge_request_list';
			$this->api_response_arry['message']			= 'information get successfully!';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']['list']			= $user_data;
			$this->api_response_arry['data']['total']	= $this->Distributor_model->get_recharge_request_list($is_total=true);
			$this->api_response();	

	}

	public function get_recharge_request_list_get(){
		$_POST =  $this->input->get();
		$result = $this->Distributor_model->get_recharge_request_list($is_total=false);
		if(!empty($result)){
				$header = array_keys($result[0]);
				$camelCaseHeader = array_map("camelCaseString", $header);
				$result = array_merge(array($camelCaseHeader),$result);
				$this->load->helper('csv');
				array_to_csv($result,'Recharge_list.csv');

		}
		else{
			$result[0]['result']= "no record Found";
			$header = array_keys($result[0]);
			$camelCaseHeader = array_map("camelCaseString", $header);
			$result = array_merge(array($camelCaseHeader),$result);
			$this->load->helper('csv');
			array_to_csv($result,'Recharge_list.csv');
	}
	}
	/*
	* function : get_admin_detail
	* def: get_admin_detail
	* @params : $array
	* @return : array admin list
	*/
	public function get_admin_detail_post()
	{
		$this->form_validation->set_rules('admin_id', 'distributor id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}	
		
		$user_data = $this->Distributor_model->get_admin_by_id($this->input->post('admin_id'));
		
		$this->api_response_arry['service_name']	= 'get_admin_detail';
		$this->api_response_arry['message']			= 'information get successfully!';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $user_data;
		$this->api_response();	
		

	}

}
/* End of file Distributor.php */
