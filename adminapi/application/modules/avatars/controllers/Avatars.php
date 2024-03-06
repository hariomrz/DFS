<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Avatars extends MYREST_Controller{

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
		$this->admin_lang = $this->lang->line('avatar');
		$this->load->model('Avatars_model');
		//Do your magic here
	}

	/**
     * For uploading Avatar image
     * @param
     * @return  array
     */
	public function do_upload_post(){
		
		$file_field_name	= $this->post('name');
		$dir				= ROOT_PATH.UPLOAD_DIR;
		$subdir				= ROOT_PATH.PROFILE_IMAGE_THUMB_UPLOAD_PATH;
		$temp_file			= $_FILES['file']['tmp_name'];
		$ext				= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		$ext 				= strtolower($ext);
		$vals = 			@getimagesize($temp_file);
		$width = $vals[0];
		$height = $vals[1];

		//check minimum and maximum dimension condition.
		if ($height < 86 || $width < 86 || $height > 172 || $width > 172) {
			$invalid_size = str_replace("{height}",'(86-172)',$this->admin_lang['avatar_image_invalid_size']);
			$invalid_size = str_replace("{width}",'(86-172)',$invalid_size);
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status']			= FALSE;
			$this->api_response_arry['global_error']			= $invalid_size;
			$this->api_response();
		}

		if ($ext == 'png') {

			$file_name = time().".".$ext ;
			$filePath     = PROFILE_IMAGE_THUMB_UPLOAD_PATH.$file_name;
			$update_profile_avatar = $this->Avatars_model->insert_avatar_image_post($file_name);
			if($update_profile_avatar!=TRUE){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status']			= FALSE;
			$this->api_response_arry['global_error']			= $invalid_image_format;
			$this->api_response();	
			}

		}
		else{
			//echo $ext;exit;
			$invalid_image_format = $this->admin_lang['avatar_image_invalid_format'];
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status']			= FALSE;
            $this->api_response_arry['global_error'] = $invalid_image_format;
            $this->api_response();
		}

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
			$this->check_folder_exist($subdir);
		}

		

		/*--Start amazon server upload code--*/
		if( strtolower( IMAGE_SERVER ) == 'remote' )
		{
			try{
	            $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
		            $data = array( 'image_name' => $file_name ,'image_url'=> IMAGE_PATH.$filePath);
		            
		            /*Uploading same avatar on profile folder starts*/
			        $data_arr = array();
	                $data_arr['file_path'] = PROFILE_IMAGE_UPLOAD_PATH.$file_name;
	                $data_arr['source_path'] = $temp_file;
	                $this->load->library('Uploadfile');
	                $upload_lib = new Uploadfile();
	                $is_uploaded = $upload_lib->upload_file($data_arr);
	                /*Uploading same avatar on profile folder ends*/

                    $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		            $this->api_response_arry['error'] = array();
		            $this->api_response_arry['message'] = $this->admin_lang["avatar_upload_success"];
		            $this->api_response_arry['data'] = $data;
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
			$config['allowed_types']	= 'png|PNG';
			$config['max_size']			= '4048';
			// $config['max_width']		= '365';
			// $config['max_height']		= '160';
			$config['upload_path']		= $subdir;
			$config['file_name']		= time();

			$this->load->library('upload', $config);
			if(!$this->upload->do_upload('file'))
			{
				$error = $this->upload->display_errors();
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['status']			= FALSE;
				$this->api_response_arry['global_error']		   = strip_tags($error);
				$this->api_response();
			}
			else
			{
				$uploaded_data = $this->upload->data();
				$image_path =   PROFILE_IMAGE_THUMB_UPLOAD_PATH. $uploaded_data['file_name'];
				
				/*Uploading same avatar on profile folder starts*/
				copy(ROOT_PATH.$image_path, ROOT_PATH.PROFILE_IMAGE_UPLOAD_PATH.$uploaded_data['file_name']);
				/*Uploading same avatar on profile folder ends*/
				
				$data = array(
							'image_url'=> $image_path,
							'image_name'=> $uploaded_data['file_name']
					);
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']			= $data;
				$this->api_response_arry['message']			= $this->admin_lang["avatar_upload_success"];
				$this->api_response();
			}
		}
	}



	 /**
     * Get all avatars
     * @return  array
     * @param : status
     */

	public function get_all_avatars_post()
	{
		$this->form_validation->set_rules('status', $this->admin_lang["avatar_status"], 'trim|required');
        

        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

		$post_data = $this->input->post('status');
		$list = $this->Avatars_model->get_all_avatars();
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $list;
		$this->api_response();
	}

/**
     * this method change avatar status as well set a new default avatar to user profile if status changing avatar is using in somecody's profile
     * @return  true/false
     * @param : id,status
     */
	public function change_avatar_status_post(){

		$this->form_validation->set_rules('id', $this->admin_lang["avatar_id"], 'trim|required');
		$this->form_validation->set_rules('status', $this->admin_lang["avatar_status"], 'trim|required');
        

        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $avatar_id = $this->input->post('id');
        $status = $this->input->post('status');

        if($status==1){

        // //select avatar name
        $name = $this->Avatars_model->get_single_row('name',AVATARS,array('id'=> $avatar_id));
        $user_ids  = $this->Avatars_model->select_users($name);
        $user_ids = array_column($user_ids, 'user_id');
        $update_data = array();
        foreach($user_ids as $key=>$id){
        	$random_avatar = $this->Avatars_model->get_first_rendom_avatar();
        	$update_data[$key]['user_id'] = $id;
        	$update_data[$key]['image']= $random_avatar['name'];
        }
        if(!empty($update_data)){
        $update_profile_image = $this->Avatars_model->update_user_profile($update_data);
        }
	    }
	    
        $change = $this->Avatars_model->change_avatar_status($avatar_id,$status);
        if($change==TRUE){


		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['message']  		= $this->admin_lang["status_update_success"];
		$this->api_response();
        }
        $this->api_response_arry['response_code'] 	= 500;
		$this->api_response_arry['error']  		= $this->admin_lang["status_update_error"];
		$this->api_response();
	}



}
/* End of file Avatars.php */
/* Location: ./application/controllers/Avatars.php */
?>