<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Systemuser extends MYREST_Controller {
	public $bot_config = array();
	public function __construct()
	{
		parent::__construct();
		$this->admin_roles_manage($this->admin_id,'user_management');

		$this->load->model('Systemuser_model');
		$this->bot_config = $this->pl_bot_config_details();
		if(empty($this->bot_config)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = 'Module not activated';
            $this->api_response();
		}
	}

	/**
     * function used for get system users with pagination and filter
     * @param array $post_data
     * @return array
     */
	public function get_users_post()
	{
		$result = $this->Systemuser_model->get_all_system_users();
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	/**
     * function used for download sample file
     * @param array $post_data
     * @return array
     */
	public function get_sample_csv_get( $filename='systemuser.csv', $attachment = true, $headers = true) {
		if($attachment) {
			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment;filename='.$filename);
			$fp = fopen('php://output', 'w');
		} else {
			$fp = fopen($filename, 'w');
		}
	
		$data = array(
			   array('username'),
			   array('john'),
			);
	
		$output = fopen("php://output", "w");
		foreach ($data as $row) {
			fputcsv($output, $row);
		}
		fclose($output);
		exit;
	}


	/**
     * function to upload a csv file to add system users
     * @param array $_FILES
     * @return array
     */
	 public function upload_systemuser_post(){
		if(!isset($_FILES)){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Sorry, Please select file.';
			$this->api_response();
		} 
		$tmp_name = $size = $type = '';
		$data = array();
		$skipped = array();
		$tmp_name = $_FILES['file']['tmp_name'];
        $size = $_FILES['file']['size'];
        $type = $_FILES['file']['type'];
        if(size <= 5242880 ){
            $this->load->library('Csvimport');
            $file_data = $this->csvimport->get_array($_FILES["file"]["tmp_name"]);
            // echo "<pre>";print_r($file_data);exit;
            foreach($file_data as $row)
            {
				$check_username =  $this->Systemuser_model->get_single_row("user_name",USER,array("LOWER(user_name)"=>strtolower($row["username"])));
				if(!empty($check_username)){
					$skipped[]= $row["username"];
				}
				else{
					$user_unique_id = $this->Systemuser_model->_generate_key();
					// $amount = number_format(0,2,".","");
					$image = 'avatar'.rand(1,10).'.png';
					$ref_code = $this->Systemuser_model->_generate_referral_code();
					$data[] = array(
						'user_unique_id' => $user_unique_id,
						'user_name' => $row["username"],
						'added_date'     => date('Y-m-d H:i:s'),
						'modified_date'  => date('Y-m-d H:i:s'),
						'status' => '1',
						'is_systemuser' => '1',
						'image' => $image,
						'referral_code'=>$ref_code
					);
				}
            }
            // echo "<pre>";print_r($data);exit;
			$user_id = $this->Systemuser_model->import_system_users($data);
			if($user_id)
			{
				$this->api_response_arry['message'] = 'system user added successfully.';
				$this->api_response_arry['data']['skipped'] = $skipped;
				$this->api_response();
			}else{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = 'The system user already existed, try with different usernames';
				$this->api_response();
			}
        }
 	}

	/**
     * function used for create new system user
     * @param array $post_data
     * @return array
     */
	public function create_user_post()
	{
		$this->form_validation->set_rules('user_name', 'user name', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

	  	$data_post = $this->post();
	  	$user_name = trim($data_post['user_name']);
		$user_unique_id = $this->Systemuser_model->_generate_key();
		$amount = number_format($data_post['balance'],2,".","");
	  	$check_username = $this->Systemuser_model->get_single_row("user_name",USER,array("LOWER(user_name)" => strtolower($user_name)));
	  	if(!empty($check_username)){
	  		$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Sorry, this username already exist.';
			$this->api_response();
	  	}

		$data = array();
		$data["user_unique_id"] = $user_unique_id;
		$data["user_name"] = $user_name;
		$data['added_date']     = date('Y-m-d H:i:s');
		$data['modified_date']  = date('Y-m-d H:i:s');
		$data['status'] = '1';
		$data['is_systemuser'] = '1';
		$data['image'] = 'avatar'.rand(1,10).'.png';
		if (!empty($data_post['image'])) {
            $data['image'] = $data_post['image'];
        }
		$user_id = $this->Systemuser_model->registration($data);
		if($user_id)
		{
			$this->api_response_arry['message'] = 'System user added successfully.';
			$this->api_response();
		}else{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Error while saving user data. please try again.';
			$this->api_response();
		}
	}

	/**
     * function used for create new system user
     * @param array $post_data
     * @return array
     */
	public function update_user_post()
	{
		$this->form_validation->set_rules('user_id', 'user id', 'trim|required');
		$this->form_validation->set_rules('user_name', 'user name', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

	  	$data_post = $this->post();
	  	$user_name = trim($data_post['user_name']);
		$check_username =  $this->Systemuser_model->get_single_row("user_id,user_name",USER,array("LOWER(user_name)"=>strtolower($user_name)));
	  	if(!empty($check_username) && $check_username['user_id'] != $data_post['user_id']){
	  		$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Sorry, this username already exist.';
			$this->api_response();
	  	}

		$data = array();
		$data["user_name"] = $user_name;
		$data['modified_date'] = date('Y-m-d H:i:s');
		if (!empty($data_post['image'])) {
            $data['image'] = $data_post['image'];
        }
		$result = $this->Systemuser_model->update_user($data,$data_post['user_id']);
		if($result)
		{
			$this->api_response_arry['message'] = 'system user updated successfully.';
			$this->api_response();
		}else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Error while update user data. please try again.';
			$this->api_response();
		}
	}

	/**
     * function used for remove system user profile picture
     * @param array $post_data
     * @return array
     */
	public function remove_profile_image_post()
	{
		$this->form_validation->set_rules('user_id', 'user id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

	  	$data_post = $this->post();
	  	$user_info =  $this->Systemuser_model->get_single_row("user_id,image",USER,array("user_id"=>$data_post['user_id']));
	  	if(!empty($user_info) && $user_info['image'] != ""){
	  		$file_name = $user_info['image'];
	  		$image_path = PROFILE_IMAGE_UPLOAD_PATH.$file_name;
	  		$thumb_path = PROFILE_IMAGE_THUMB_PATH . $file_name;
	  		$data = array("image"=>"");
			$result = $this->Systemuser_model->update_user($data,$data_post['user_id']);
			if($result){
		  		@unlink($image_path);
		  		@unlink($thumb_path);

		  		$this->api_response_arry['message'] = 'User profile picture removed successfully.';
				$this->api_response();
		  	}else{
		  		$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = 'Something went wrong in remove image.';
				$this->api_response();
		  	}
	  	}else{
	  		$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'User image detail not found.';
			$this->api_response();
	  	}
	}

	/**
     * function used for create new system user
     * @param array $post_data
     * @return array
     */
	public function delete_user_post()
	{
		$this->form_validation->set_rules('user_id', 'user id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

	  	$data_post = $this->post();
	  	$check_user = $this->Systemuser_model->check_for_delete($data_post['user_id']);
	  	if(isset($check_user['total']) && $check_user['total'] > 0){
	  		$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Sorry, you can't delete this user because of user already joined contest.";
			$this->api_response();
	  	}

		$result = $this->Systemuser_model->delete_user($data_post['user_id']);
		if($result)
		{
			$this->api_response_arry['message'] = 'system user deleted successfully.';
			$this->api_response();
		}else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Error while delete user data. please try again.';
			$this->api_response();
		}
	}

	/**
     * do_upload used to upload profile image or pan card
     * @param
     * @return json array
     */
    public function do_upload_post() {
        $post_data = $this->post();
        $file_field_name = 'userfile';
        if (!isset($_FILES[$file_field_name])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('file_not_found'));
            $this->api_response();
        }

        $dir = PROFILE_IMAGE_UPLOAD_PATH;
        $temp_file = $_FILES[$file_field_name]['tmp_name'];
        $ext = pathinfo($_FILES[$file_field_name]['name'], PATHINFO_EXTENSION);
        $vals = @getimagesize($temp_file);
        
        if (!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > 4194304) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_image_size'));
            $this->api_response();
        }

        $file_name = time() . "." . $ext;
        $allowed_ext = array('jpg', 'jpeg', 'png');
        if (!in_array(strtolower($ext), $allowed_ext)) {
            $error_msg = sprintf($this->lang->line('invalid_image_ext'), implode(', ', $allowed_ext));
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $error_msg);
            $this->api_response();
        }

        /* --Start amazon server upload code-- */
        if (strtolower(IMAGE_SERVER) == 'remote') {
            $config['allowed_types'] = 'jpg|png|jpeg|gif';
            $config['max_size'] = '204800'; //204800
            $config['upload_path'] = ROOT_PATH . $dir;
            $config['file_name'] = $file_name;

            $this->load->library('upload', $config);
            if (!$this->upload->do_upload($file_field_name)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = strip_tags($this->upload->display_errors());
                $this->api_response();
            } else {
                $uploaded_data = $this->upload->data();
                $thumb_path = PROFILE_IMAGE_PATH . $uploaded_data['file_name'];
                $config = array();
                $config['image_library'] = 'gd2';
                $config['source_image'] = ROOT_PATH . PROFILE_IMAGE_UPLOAD_PATH . $uploaded_data['file_name'];
                $config['new_image'] = ROOT_PATH . PROFILE_IMAGE_THUMB_UPLOAD_PATH;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 200;

                $this->load->library('image_lib', $config);

                $this->image_lib->resize();
                $this->api_response_arry['data'] = array('image_path' => $thumb_path, 'file_name' => $uploaded_data['file_name']);

                $thumb_filePath = PROFILE_IMAGE_THUMB_UPLOAD_PATH . $file_name;
                $thumb_source_path = ROOT_PATH . PROFILE_IMAGE_THUMB_UPLOAD_PATH . $file_name;
                $filePath = $dir . $file_name;

                try{
	                $data_arr = array();
	                $data_arr['file_path'] = $filePath;
	                $data_arr['source_path'] = $temp_file;
	                $this->load->library('Uploadfile');
	                $upload_lib = new Uploadfile();
	                $is_uploaded = $upload_lib->upload_file($data_arr);
	                if($is_uploaded){
	                	$data_arr = array();
		                $data_arr['file_path'] = $thumb_filePath;
		                $data_arr['source_path'] = $thumb_source_path;
	                    $is_thumb_upload = $upload_lib->upload_file($data_arr);
	                    if($is_thumb_upload){
		                    @unlink($thumb_source_path);
		                    @unlink(ROOT_PATH . $filePath);
		                    $image_path = PROFILE_IMAGE_THUMB_PATH . $file_name;
		                    $return_array = array('image_path' => $image_path, 'file_name' => $file_name);
		                    $this->api_response_arry['data'] = $return_array;
	                    }
	                }
	            }catch(Exception $e){
	                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
	                $this->api_response();
	            }
            }
        } else {
            $config['allowed_types'] = 'jpg|png|jpeg|gif';
            $config['max_size'] = '4096'; //204800
            $config['upload_path'] = $dir;
            $config['file_name'] = $file_name;

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload($file_field_name)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = strip_tags($this->upload->display_errors());
                $this->api_response();
            } else {
                $uploaded_data = $this->upload->data();
                $thumb_path = PROFILE_IMAGE_PATH . $uploaded_data['file_name'];
                $this->api_response_arry['data'] = array('image_path' => $thumb_path, 'file_name' => $uploaded_data['file_name']);
            }
        }
        $this->api_response();
    }

    public function get_system_user_master_data_post()
	{
		$this->form_validation->set_rules('collection_master_id', 'Collection master id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$cm_id = $post_data['collection_master_id'];
		
		$result = $this->Systemuser_model->get_system_user_fixture_wise_stats($cm_id);
		$result['max_teams_per_user'] = ALLOWED_USER_TEAM;
		$result['max_teams_per_fixture'] = $this->bot_config['match_limit'];

		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	public function get_fixture_system_user_list_post()
	{
		$this->form_validation->set_rules('collection_master_id', 'Collection master id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$result = $this->Systemuser_model->get_fixture_system_user_list($post_data);
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	public function add_system_user_teams_post()
	{
		$this->form_validation->set_rules('collection_master_id', 'Collection master id', 'trim|required');
		$this->form_validation->set_rules('no_of_teams', 'No. of teams', 'trim|required|is_natural_no_zero|greater_than[0]');
		$this->form_validation->set_rules('teams_per_user', 'Teams per user', 'trim|required|is_natural_no_zero|greater_than[0]');
		if(!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
		$current_date = format_date();
		$cm_id = $post_data['collection_master_id'];
		$no_of_teams = $post_data['no_of_teams'];
		$teams_per_user = $post_data['teams_per_user'];
		$repeat_user = (!empty($post_data['repeat_user'])) ? $post_data['repeat_user'] : 0;
		//get pending bot request from cache
		$pending_bot_request = $this->Systemuser_model->get_bot_history("no_of_teams,collection_master_id",array("collection_master_id"=>$cm_id,"status"=>0));
		if (!empty($pending_bot_request['collection_master_id']) && !empty($pending_bot_request['no_of_teams'])) {
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error'] = "Another request is in process to add bots in fixture .So please try after some time.";
			$this->api_response();
        }
		else
		{
			$pending_bot_request = array("collection_master_id"=>$cm_id,"no_of_teams"=>$no_of_teams,"added_date"=>$current_date,"modified_date"=>$current_date);
			$this->Systemuser_model->insert_bot_history($pending_bot_request);
		}

        $fixture_detail = $this->Systemuser_model->get_collection_details($cm_id);
        //echo "<pre>";print_r($fixture_detail);die;
        if(empty($fixture_detail)){
			$this->Systemuser_model->update_bot_history(array("status"=>2,"modified_date"=>format_date()),array("collection_master_id"=>$cm_id,"status"=>0));

            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Fixture details not found.";
            $this->api_response();
        }else if(strtotime($fixture_detail['season_scheduled_date']) <= strtotime($current_date)){
			$this->Systemuser_model->update_bot_history(array("status"=>2,"modified_date"=>format_date()),array("collection_master_id"=>$cm_id,"status"=>0));

            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_start_bots_error');
            $this->api_response();
        }else if(!isset($fixture_detail['playing_eleven_confirm']) || $fixture_detail['playing_eleven_confirm'] == 0){
			$this->Systemuser_model->update_bot_history(array("status"=>2,"modified_date"=>format_date()),array("collection_master_id"=>$cm_id,"status"=>0));

            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Sorry, system users are not allowed for this fixture.";
            $this->api_response();
        }

        //check for match schedule date	
        $current_time = date("Y-m-d H:i:s", strtotime($current_date." +".SYSTEM_USER_CONTEST_DEADLINE." minute"));	
        if (strtotime($fixture_detail['season_scheduled_date']) < strtotime($current_time)) {
			$this->Systemuser_model->update_bot_history(array("status"=>2,"modified_date"=>format_date()),array("collection_master_id"=>$cm_id,"status"=>0));
			
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;	
			$this->api_response_arry['message'] = 'Sorry, Match deadline is over for add system user.';	
			$this->api_response();	
        }	

		//check valid teams per user
		if($teams_per_user > ALLOWED_USER_TEAM)
		{
			$this->Systemuser_model->update_bot_history(array("status"=>2,"modified_date"=>format_date()),array("collection_master_id"=>$cm_id,"status"=>0));
			
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Total ".ALLOWED_USER_TEAM." teams are allowed per user. Please enter accordingly.";
			$this->api_response(); 
		}

		//check valid no of teams and teams per user
		$requested_system_users = $no_of_teams/$teams_per_user;
		if(!is_integer($requested_system_users))
		{
			$this->Systemuser_model->update_bot_history(array("status"=>2,"modified_date"=>format_date()),array("collection_master_id"=>$cm_id,"status"=>0));
			
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Please enter a valid combination in No. of teams and Teams per user.";
			$this->api_response(); 
		}
		//get already used system users 
		$valid_sys_users = array();
		$invalid_sys_users = array();
		$already_added_teams = 0;
		$already_added_users = $this->Systemuser_model->get_already_used_system_users($cm_id);
		if(!empty($already_added_users))
		{
			foreach($already_added_users as $akey=>$ausesr)
			{
				if(empty($ausesr['user_id']) || empty($ausesr['team_count']))
				{
					continue;
				}

				$a_team_count = $ausesr['team_count'];
				$already_added_teams = $already_added_teams + $a_team_count; 
				//if repeated system users allowed then prepare valid / invalid user list accordingly.	
				if($repeat_user == 1 && ALLOWED_USER_TEAM >= ($a_team_count+$teams_per_user))	
				{
					$valid_sys_users[] = $ausesr;
				}
				else
				{
					$invalid_sys_users[] = $ausesr['user_id']; 	
				}
			}
		}

		//calcuate already used valid system users in which remaining allowed teams are greater or equal to requested teams per user.
		$already_added_users =  count($invalid_sys_users);
		$total_allowed = $this->bot_config['match_limit'];
		if(($already_added_teams + $no_of_teams) > $total_allowed)
		{
			$this->Systemuser_model->update_bot_history(array("status"=>2,"modified_date"=>format_date()),array("collection_master_id"=>$cm_id,"status"=>0));
			
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Total $total_allowed teams are allowed in a match and you have already added $already_added_teams teams. So please enter No. of teams accordingly.";
			$this->api_response();
		}

		$available_user_input = array("already_used_user"=>$invalid_sys_users,"requested_users"=>$requested_system_users);
		$available_system_users = $this->Systemuser_model->get_remaining_system_users($available_user_input);
		if(empty($available_system_users))
		{
			$this->Systemuser_model->update_bot_history(array("status"=>2,"modified_date"=>format_date()),array("collection_master_id"=>$cm_id,"status"=>0));
			
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "System users are not available.";
			$this->api_response();
		}

		$db_system_users = count($available_system_users);
		if($requested_system_users > $db_system_users)
		{
			$this->Systemuser_model->update_bot_history(array("status"=>2,"modified_date"=>format_date()),array("collection_master_id"=>$cm_id,"status"=>0));
			
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error'] = "Not enough valid system users available.";
			$this->api_response();
		}
		
		$valid_user_teams = array_column($valid_sys_users,null,"user_id");
		//echo "<pre>";print_r($valid_user_teams);die;
		$lineup_master_data = array();
		$is_valid = 1;
		$fixture_error_msg = array();
		$current_date = format_date();
		foreach($available_system_users as $user){
			$user_team_count = $teams_per_user;
			if(!empty($valid_user_teams[$user['user_id']]['team_count'])){
				$user_team_count = $user_team_count + $valid_user_teams[$user['user_id']]['team_count'];
			}

			if($user_team_count > ALLOWED_USER_TEAM){
				$is_valid = 0;
				$fixture_error_msg[] = "(".$user['user_name'].")";
				continue;
			}
			
			for($i=1;$i<=$teams_per_user;$i++){
				$team_name = "Team ".$i;
				if(!empty($valid_user_teams[$user['user_id']]['team_count'])){
					$tcount = $valid_user_teams[$user['user_id']]['team_count'] + $i;
					$team_name = "Team ".$tcount;
				}
				$tmp_arr = array();
				$tmp_arr['collection_master_id'] = $cm_id;
				$tmp_arr['league_id'] = $fixture_detail['league_id'];
				$tmp_arr['user_id'] = $user['user_id'];
				$tmp_arr['user_name'] = $user['user_name'];
				$tmp_arr['team_name'] = $team_name;
				$tmp_arr['date_added'] = $current_date;
				$tmp_arr['date_modified'] = $current_date;
				$tmp_arr['team_data'] = json_encode(array());
				$tmp_arr['is_systemuser'] = 1;
				$lineup_master_data[] = $tmp_arr;
			}
		}

		if($is_valid == 0 && !empty($fixture_error_msg)){
			$this->Systemuser_model->update_bot_history(array("status"=>2,"modified_date"=>format_date()),array("collection_master_id"=>$cm_id,"status"=>0));
			
			$msg = "Maximum fixture lineup count ".ALLOWED_USER_TEAM." exceeded for the users: ".implode(",", $fixture_error_msg);
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $msg;
			$this->api_response();
		}

		//echo "<pre>";print_r($fixture_detail);
		//echo "<pre>";print_r($lineup_master_data);die;
		$input_arr = array();
		$input_arr['sports_id'] = $fixture_detail['sports_id'];
		$input_arr['league_id'] = $fixture_detail['league_id'];
		$input_arr['season_game_uid'] = $fixture_detail['season_game_uid'];
		$input_arr['total_team_request'] = $no_of_teams;
		$input_arr['max_player_per_team'] = $fixture_detail['max_player_per_team'];
		$team_result = $this->Systemuser_model->generate_match_pl_team($input_arr);
		//echo "<pre>";print_r($team_result);die;
		if(!$team_result || empty($team_result)){
			$this->Systemuser_model->update_bot_history(array("status"=>2,"modified_date"=>format_date()),array("collection_master_id"=>$cm_id,"status"=>0));
			
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Sorry, Team not generated. please contact technical team.';
			$this->api_response();
		}

		$team_list = $team_result['lineups'];
		$player_list = array_column($team_result['player_list'],NULL,"player_team_id");
		$position_post_array = array_column($team_result['position'], 'master_lineup_position_id','position_name');

		$error_arr = array();
		foreach($lineup_master_data as $key=>$team){
			if(isset($team_list[$key])){
				$tmp_data = $team_list[$key];
				$c_id = $player_list[$tmp_data["c_id"]]['player_team_id'];
				$vc_id = $player_list[$tmp_data["vc_id"]]['player_team_id'];
				$player_arr = array();
				foreach($tmp_data["pl"] as $player_uid){
					$player_arr[] = $player_list[$player_uid]['player_team_id'];
				}
				$team_data = array("c_id"=>$c_id,"vc_id"=>$vc_id,"pl"=>$player_arr);
				$team['team_data'] = json_encode($team_data);
				$lineup_master_data[$key] = $team;
			}else{
				$error_arr[] = $team['user_name'];
			}
		}
		
		//echo "<pre>";print_r($lineup_master_data);die;
		$result = $this->Systemuser_model->add_system_user_teams($lineup_master_data);
		if($result)
		{
			$this->Systemuser_model->update_bot_history(array("status"=>1,"modified_date"=>format_date()),array("collection_master_id"=>$cm_id,"status"=>0));
			
			$this->api_response_arry['message'] = 'Teams has been added into fixture.';
			$this->api_response_arry['data'] = $result;
			$this->api_response();
		}
		else
		{
			$this->Systemuser_model->update_bot_history(array("status"=>2,"modified_date"=>format_date()),array("collection_master_id"=>$cm_id,"status"=>0));
			
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error'] = 'Sorry, Teams not added. please contact technical team.';
			$this->api_response();
		}
	}

	/**
     * function used for contest details for add bots
     * @param array $post_data
     * @return array
     */
	public function get_contest_detail_post()
	{
		$this->form_validation->set_rules('contest_unique_id', 'contestunique id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$data_arr = $this->input->post();
		$contest_detail = $this->Systemuser_model->get_contest_detail($data_arr);
		if(!empty($contest_detail)){
			$contest_detail['prize_distibution_detail'] = json_decode($contest_detail['prize_distibution_detail'],TRUE);
		}
		$this->api_response_arry['data']['max_system_user'] = $this->bot_config['contest_limit'];
		$this->api_response_arry['data']['system_user_deadline'] = SYSTEM_USER_CONTEST_DEADLINE;
		$this->api_response_arry['data']['system_user_request_limit'] = SYSTEM_USER_REQUEST_LIMIT;
		$this->api_response_arry['data']['contest_detail'] = $contest_detail;
		$this->api_response();
	}

	/**
	 * function used for join multiple bots in signle request
	 * @param array $post_data
	 * @return array
	 */
	public function join_multiple_users_post()
	{
		$this->form_validation->set_rules('contest_id', 'contest id', 'trim|required');
		$this->form_validation->set_rules('no_of_teams', 'team count', 'trim|required|is_natural_no_zero|greater_than[0]');
		if(!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$this->message = "";
		$current_date = format_date();
		$post_data = $this->input->post();
		$contest_id = $post_data['contest_id'];
		$total_team_request = $post_data['no_of_teams'];
		$contest_detail = $this->Systemuser_model->get_contest_detail($post_data);
		if(empty($contest_detail)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Sorry, Contest details not found.';
			$this->api_response();
		}

		if($total_team_request > SYSTEM_USER_REQUEST_LIMIT){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Total '.SYSTEM_USER_REQUEST_LIMIT.' teams allowed in single request!';
			$this->api_response();
		}

		if($total_team_request > ($contest_detail['size'] - $contest_detail['total_user_joined'])){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Number of system users you are trying to add, is more than available entries in this contest!';
			$this->api_response();
		}
		//echo "<pre>";print_r($contest_detail);die;
		$is_valid = $this->validation_for_join_game($contest_detail);
		if($is_valid){
			$valid_teams_input = array("contest_id"=>$contest_detail['contest_id'],"collection_master_id"=>$contest_detail['collection_master_id'],"no_of_teams"=>$total_team_request);
			$sys_user_teams = $this->Systemuser_model->get_system_user_valid_teams($valid_teams_input);
			if(count($sys_user_teams) < $total_team_request)
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = 'No of teams requested is not available. Please add some teams in fixture.';
				$this->api_response();
			}
			
			//echo "<pre>";print_r($contest_user_list);die;
			$total_bot_joined = $contest_detail['total_system_user'];
			$tmp_total = $total_bot_joined + $total_team_request;
			$system_user_contest_limit = $this->bot_config['contest_limit'];;
			if($tmp_total > $system_user_contest_limit){
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = 'Max '.$system_user_contest_limit." system users allowed per contest.";
				$this->api_response();
			}

			//echo "<pre>";print_r($sys_user_teams);die;
			$contest_detail["total_team_request"] = $total_team_request;
			$joined_rs = $this->Systemuser_model->join_game_with_multiple_system_users($sys_user_teams,$contest_detail);
			if($joined_rs['join_status'] == false){
				$msg = (!empty($joined_rs['msg'])) ? $joined_rs['msg'] : "Something went wrong during contest join. Please contact technical team.";
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = $msg;
				$this->api_response();
			}else{
				//create auto recuring contest
				if($contest_detail['is_auto_recurring'] == '1' && $total_team_request == ($contest_detail['size'] - $contest_detail['total_user_joined'])){
					$this->load->helper('queue');
					$contest_queue = array("action" => "auto_recurring", "data" => array("contest_unique_id" => $contest_detail['contest_unique_id']));
					add_data_in_queue($contest_queue, 'contest');
				}

				$contest_cache_key = "contest_".$contest_detail['contest_id'];
				$this->delete_cache_data($contest_cache_key);

				$this->api_response_arry['message'] = "Contest joined successfully.";
				$this->api_response();
			}
		}else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->message;
			$this->api_response();
		}
	}

	/**
     * function used for validate contest data
     * @param array $contest
     * @return array
     */
	public function validation_for_join_game($contest) {
        if (empty($contest)) {
            $this->message = "Please select a valid contest.";
            return 0;
        }

        //for manage collection wise deadline
        $current_date = format_date();
        $current_time = date("Y-m-d H:i:s", strtotime($current_date." +".SYSTEM_USER_CONTEST_DEADLINE." minute"));
        //check for match schedule date
        if (strtotime($contest['season_scheduled_date']) < strtotime($current_time)) {
            $this->message = "System users join deadline over.";
            return 0;
        }

        //check for full contest
        if ($contest['total_user_joined'] >= $contest['size']) {
            $this->message = "This contest already full.";
            return 0;
        }

        //if contest closed
        if ($contest['status'] !== '0') {
            $this->message = "Contest closed.";
            return 0;
        }

        return 1;
    }

    /**
     * function used for get contest joined users list with team count
     * @param array $post_data
     * @return array
     */
	public function get_contest_joined_system_users_post()
	{
		$this->form_validation->set_rules('contest_id', 'contest id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$result = $this->Systemuser_model->get_system_users_for_contest($post_data);
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

    /**
     * function used for get contest joined users list with team count
     * @param array $post_data
     * @return array
     */
	public function get_system_users_for_contest_post()
	{
		$this->form_validation->set_rules('contest_id', 'contest id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$contest = $this->Systemuser_model->get_contest_detail($post_data);
		if(empty($contest)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Sorry, Contest details not found.';
			$this->api_response();
		}
		$post_data['collection_master_id'] = $contest['collection_master_id'];
		$multiple_lineup = $contest['multiple_lineup'];
		$valid_sys_users = array();
		$result = $this->Systemuser_model->get_available_system_users_for_contest($post_data);
		//echo "$multiple_lineup <pre>";print_r($result);die;
		if(!empty($result))
		{
			foreach($result as $rkey=>$ruser)
			{	
				if($multiple_lineup > $ruser['already_joined_teams'])
				{
					$available_count = abs($multiple_lineup - $ruser['already_joined_teams']);
					if($available_count <= $ruser['available_team_count'])
					{
						$ruser['available_team_count'] = $available_count;
					}
					unset($ruser['already_joined_teams']);
					$valid_sys_users[] = $ruser;
				}
			}
		}
		$this->api_response_arry['data'] = $valid_sys_users;
		$this->api_response();
	}

	/**
     * function used for get contest joined users list with team count
     * @param array $post_data
     * @return array
     */
	public function join_system_users_post()
	{
		$this->form_validation->set_rules('contest_id', 'contest id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$this->message = "";
		$current_date = format_date();
		$post_data = $this->input->post();
		$contest_id = $post_data['contest_id'];
		if(empty($post_data['user_list'])){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Please select atleast 1 user.';
			$this->api_response();
		}
		//echo "<pre>";print_r($post_data);die;
		$post_data['user_list'] = array_column($post_data['user_list'],NULL,"user_id");
		$contest = $this->Systemuser_model->get_contest_detail($post_data);
		if(empty($contest)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Sorry, Contest details not found.';
			$this->api_response();
		}

		$fixture_detail = $this->Systemuser_model->get_collection_details($contest['collection_master_id']);
		if(empty($fixture_detail)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Fixture details not found.";
            $this->api_response();
        }else if(strtotime($fixture_detail['season_scheduled_date']) <= strtotime($current_date)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_start_bots_error');
            $this->api_response();
        }else if(!isset($fixture_detail['playing_eleven_confirm']) || $fixture_detail['playing_eleven_confirm'] == 0){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Sorry, system users are not allowed for this fixture.";
            $this->api_response();
        }

		$total_team_request = 0;
		if(!empty($post_data['user_list'])){
			$total_team_request = array_sum(array_column($post_data['user_list'], "team_count"));
		}

		if($total_team_request > SYSTEM_USER_REQUEST_LIMIT){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Total '.SYSTEM_USER_REQUEST_LIMIT.' teams allowed in single request!';
			$this->api_response();
		}
		
		$contest["total_team_request"] = $total_team_request;
		//echo "<pre>";print_r($contest);die;		
		$is_valid = $this->validation_for_join_game($contest);
        if($is_valid){
        	$total_teams = 0;
			if(!empty($post_data['user_list'])){
				$total_teams = array_column($post_data['user_list'], "team_count","user_id");
				$total_teams = array_sum($total_teams);
			}
			if($total_teams > ($contest['size'] - $contest['total_user_joined'])){
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = 'Number of system users you are trying to add, is more than available entries in this contest!';
				$this->api_response();
			}

			$total_bot_joined = $contest['total_system_user'];
			$total_team_request = array_sum(array_column($post_data['user_list'], "team_count"));
			$tmp_total = $total_bot_joined + $total_team_request;
			$system_user_contest_limit = $this->bot_config['contest_limit'];
			if($tmp_total > $system_user_contest_limit){
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = 'Max '.$system_user_contest_limit." system users allowed per contest.";
				$this->api_response();
			}

			$available_user_cond = array("contest_id"=>$contest_id,"collection_master_id"=>$contest['collection_master_id']);	
			$available_user_list = $this->Systemuser_model->get_available_system_users_for_contest($available_user_cond,1);
			if(empty($available_user_list))
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = "System users not found.";
				$this->api_response();
			}

			$user_ids = array_column($available_user_list,null,"user_id");
			//echo "<pre>";print_r($user_ids);print_r($data_arr['user_list']);
			$team_data = array();
			$is_valid = 1;
			$error_msg = array();
			$current_date = format_date();
			foreach($post_data['user_list'] as $user){
				$requested_team_count = $user['team_count'];
				if(empty($user_ids[$user['user_id']])){
					$is_valid = 0;
					$error_msg[] = "(1-".$user['user_name'].")";
					continue;
				}
				$user_team_info = $user_ids[$user['user_id']];
				if(empty($user_team_info["available_team_count"]) || empty($user_team_info["all_team_ids"]))
				{
					$is_valid = 0;
					$error_msg[] = "(2-".$user['user_name'].")";
					continue;
				}
				$used_team_ids = (!empty($user_team_info["used_team_ids"])) ? explode(",",$user_team_info["used_team_ids"]) : array(); 
				$all_team_ids = (!empty($user_team_info["all_team_ids"])) ? explode(",",$user_team_info["all_team_ids"]) : array(); 
				$user_team_count = count($used_team_ids)+$requested_team_count;
				if($user_team_count > $contest['multiple_lineup']){
					$is_valid = 0;
					$error_msg[] = "(3-".$user['user_name'].")";
				}elseif((count($all_team_ids) - count($used_team_ids)) < $requested_team_count){
					$is_valid = 0;
					$error_msg[] = "(4-".$user['user_name'].")";
				}
				else
				{
					$valid_team_count = 0;
					foreach($all_team_ids as $valid_team)
					{
						if(!in_array($valid_team,$used_team_ids) && $valid_team_count < $requested_team_count)
						{
							$team_data[] = array(
								"user_id" => $user["user_id"],
								"lineup_master_id" => $valid_team
							);
							$valid_team_count++;
						}

					}
					
				}
			}
			if($is_valid == 0 && !empty($error_msg)){
				$msg = "Game joining Failed for the users: ".implode(",", $error_msg);
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = $msg;
				$this->api_response();
			}
			//echo "<pre>";print_r($team_data);die;
			$joined_rs = $this->Systemuser_model->join_game_with_multiple_system_users($team_data,$contest);
			if($joined_rs['join_status'] == false){
				$msg = (!empty($joined_rs['msg'])) ? $joined_rs['msg'] : "Something went wrong during contest join. Please contact technical team.";
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = $msg;
				$this->api_response();
			}else{
				//create auto recuring contest
				if($contest['is_auto_recurring'] == '1' && $total_teams == ($contest['size'] - $contest['total_user_joined'])){
					$this->load->helper('queue');
					$contest_queue = array("action" => "auto_recurring", "data" => array("contest_unique_id" => $contest['contest_unique_id']));
					add_data_in_queue($contest_queue, 'contest');
				}

				$contest_cache_key = "contest_".$contest['contest_id'];
				$this->delete_cache_data($contest_cache_key);

				$this->api_response_arry['message'] = "Contest joined successfully.";
				$this->api_response();
			}
        }else{
        	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->message;
            $this->api_response();
        }
	}

	public function get_system_user_reports_post()
	{
		$this->form_validation->set_rules('sports_id', 'sports_id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
		$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$result = $this->Systemuser_model->get_system_user_reports($post_data);
		//echo "<pre>";print_r($result);die;
		$final_result = array();
		$balance = 0;
		if(!empty($result)){
			$collection_master_ids = array_column($result['result'],"collection_master_id");
			$cmid_arr = array();
			foreach($collection_master_ids as $cmid){
				$cmid_arr[$cmid] = $this->Systemuser_model->get_contest_ids($cmid);
			}
			$bot_prize_data = array("realuser_winnings"=>0,"systemuser_winnings"=>0);
			$contest_data = array();
			foreach($cmid_arr as $key=>$contest_id_arr){
				$this->load->model('user/User_model','user_model');
				if(!empty($contest_id_arr)){
					$con_det[$key] = $this->user_model->get_contest_details($contest_id_arr);
					$real_amount[$key] = array_sum(array_column($con_det[$key],'real_amount'));
					$bonus_amount[$key] = array_sum(array_column($con_det[$key],'bonus_amount'));
					$bot_prize[$key] = $this->Systemuser_model->get_bot_prize($contest_id_arr);
					$sys_user_winning[$key] = array_sum(array_column($bot_prize[$key],'systemuser_winnings'));
					$real_user_winning[$key] = array_sum(array_column($bot_prize[$key],'realuser_winnings'));
				}
				else{
					$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
					$this->api_response_arry['global_error'] = "no record found";
					$this->api_response(); 
				}
			}

			$data = array();
			foreach($result['result'] as $key=>$res){
				$data[$res['collection_master_id']]=$res;
				$data[$res['collection_master_id']]['bonus_loss']= 0;
				$data[$res['collection_master_id']]['real_amount']= 0;
				$data[$res['collection_master_id']]['net_profit']= 0;
				$data[$res['collection_master_id']]['site_rake']= 0;
				$data[$res['collection_master_id']]['realuser_winnings']=floatval(round($real_user_winning[$res['collection_master_id']],2));
				$data[$res['collection_master_id']]['systemuser_winnings']=floatval(round($sys_user_winning[$res['collection_master_id']],2));
				$data[$res['collection_master_id']]['bonus_loss']=floatval(round($bonus_amount[$res['collection_master_id']],2));
				$data[$res['collection_master_id']]['real_amount']=floatval(round($real_amount[$res['collection_master_id']],2));
				$data[$res['collection_master_id']]['net_profit']=floatval(round($real_amount[$res['collection_master_id']]-$real_user_winning[$res['collection_master_id']],2));
				$data[$res['collection_master_id']]['site_rake']=floatval(round($data[$res['collection_master_id']]['net_profit']-$sys_user_winning[$res['collection_master_id']],2));
			}

			foreach($data as $key=>$value){
				if($value['net_profit']>0 ){
					if($value['systemuser_winnings'] >= $value['net_profit']){
						$value['systemuser_winnings'] = $value['net_profit'];
					}
				}
				else{
						$value['systemuser_winnings']=0;
				}
				if($value['site_rake'] < 0){
					$value['site_rake']=0;
				}
				$final_result[]=$value;
			}
			$total_net_profit = array_sum(array_column($final_result,'net_profit'));
			$balance = floatval(round($total_net_profit,2));
		}
		$result['total'] = isset($result['total']) ? $result['total'] : 0;
		$this->api_response_arry['data'] = array("result"=>$final_result,"total"=>$result['total'],"balance"=>$balance);
		$this->api_response();
	}

	public function get_system_user_league_list_post()
    {
		$this->form_validation->set_rules('sports_id', 'sports_id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		//echo "<pre>";print_r($data_arr);die;
		$result = $this->Systemuser_model->get_system_user_league_list($post_data);
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	public function get_system_user_reports_get()
	{
		$data_arr = $this->input->get();
		// echo "<pre>";print_r($data_arr);die;
		$result = $this->Systemuser_model->get_system_user_reports($data_arr);
		$collection_master_ids = array_column($result['result'],"collection_master_id");
		$cmid_arr = array();
		foreach($collection_master_ids as $cmid){
			$cmid_arr[$cmid] = $this->Systemuser_model->get_contest_ids($cmid);
		}
		$bot_prize_data = array("realuser_winnings"=>0,"systemuser_winnings"=>0);
		$contest_data = array();
		foreach($cmid_arr as $key=>$contest_id_arr){
			$this->load->model('user/User_model','user_model');
			if(!empty($contest_id_arr)){
				$con_det[$key] = $this->user_model->get_contest_details($contest_id_arr);
				$real_amount[$key] = array_sum(array_column($con_det[$key],'real_amount'));
				$bonus_amount[$key] = array_sum(array_column($con_det[$key],'bonus_amount'));
				$bot_prize[$key] = $this->Systemuser_model->get_bot_prize($contest_id_arr);
				$sys_user_winning[$key] = array_sum(array_column($bot_prize[$key],'systemuser_winnings'));
				$real_user_winning[$key] = array_sum(array_column($bot_prize[$key],'realuser_winnings'));
			}
			else{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['global_error'] = "no record found";
				$this->api_response(); 
			}
		}
		$data = array();
		$final_result = array();
		foreach($result['result'] as $key=>$res){
			$data[$res['collection_master_id']]=$res;
			$data[$res['collection_master_id']]['real_users']= $res['total_user_joined']-$res['total_system_user'];
			$data[$res['collection_master_id']]['bonus_loss']= 0;
			$data[$res['collection_master_id']]['real_amount']= 0;
			$data[$res['collection_master_id']]['net_profit']= 0;
			$data[$res['collection_master_id']]['site_rake']= 0;
			$data[$res['collection_master_id']]['realuser_winnings']=floatval(round($real_user_winning[$res['collection_master_id']],2));
			$data[$res['collection_master_id']]['systemuser_winnings']=floatval(round($sys_user_winning[$res['collection_master_id']],2));
			$data[$res['collection_master_id']]['bonus_loss']=floatval(round($bonus_amount[$res['collection_master_id']],2));
			$data[$res['collection_master_id']]['real_amount']=floatval(round($real_amount[$res['collection_master_id']],2));
			$data[$res['collection_master_id']]['net_profit']=floatval(round($real_amount[$res['collection_master_id']]-$real_user_winning[$res['collection_master_id']],2));
			$data[$res['collection_master_id']]['site_rake']=floatval(round($data[$res['collection_master_id']]['net_profit']-$sys_user_winning[$res['collection_master_id']],2));
		}
		foreach($data as $key=>$value){
			if($value['net_profit']>0 ){
				if($value['systemuser_winnings'] >= $value['net_profit']){
						$value['systemuser_winnings'] = $value['net_profit'];
				}
			}
			else{
					$value['systemuser_winnings']=0;
			}
			if($value['site_rake'] < 0){
				$value['site_rake']=0;
			}
			$final_result[]=$value;
		}
		
		if(!empty($final_result)){
			$result =$final_result;
			$header = array_keys($result[0]);
			$camelCaseHeader = array_map("camelCaseString", $header);
			$result = array_merge(array($camelCaseHeader),$result);
			$this->load->helper('download');
			$this->load->helper('csv');
			$data = array_to_csv($result);
            $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
            $name = 'bot_user_report.csv';
            force_download($name, $data);
		}
		else{
			$result = "no record found";
			$this->load->helper('download');
			$this->load->helper('csv');
			$data = array_to_csv($result);
			$name = 'bot_user_report.csv';
			force_download($name, $result);
		}
	}
}
/* End of file Systemuser.php */
/* Location: ./application/controllers/Systemuser.php */
