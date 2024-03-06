<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Feedback extends MYREST_Controller{

	public function __construct()
	{
		parent::__construct();
		$_POST				= $this->input->post();
		$this->admin_lang = $this->lang->line('feedback');
		$this->load->model('Feedback_model');
		//Do your magic here
	}

	public function send_feedback_post()
	{
		if($this->input->post())
		{
			$this->form_validation->set_rules('title','Title', 'trim|required');
			$this->form_validation->set_rules('description','Description', 'trim|required');
			if ($this->form_validation->run()) 
			{
                                $post_data = $this->input->post();
                                
                                $content = array(
                                                    "title" => $post_data['title'],
                                                    "description" => $post_data['description'],
                                                    "image" => !empty($post_data['image']) ? $post_data['image'] : "",
                                                    "email" => $this->session->userdata("email"),
                                                    "full_name" => $this->session->userdata("full_name"),
                                                    "site_url"  => BASE_APP_PATH,
                                                    "post_date" => format_date('today', 'd-M-Y h:i A')
                                            );
                                
                                $email_content              = array();
                                $email_content['email']     = FEEDBACK_EMAIL;
                                $email_content['subject']   = SITE_TITLE.' Feedback: '.$post_data['title'];
                                $email_content['user_name'] = "";
                                $email_content['content']   = $content;
                                $email_content['notification_type'] = 100;
                                
                                $this->rabbit_mq_push($email_content, 'email');
				
                                $this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
                                $this->api_response_arry['status']			= TRUE;
                                $this->api_response_arry['message']		   = $this->admin_lang['feedback_send_success'];
                                $this->api_response();
				
			}
			$this->send_validation_errors();
		}
	}

	public function do_upload_post(){
		
		$file_field_name	= $this->post('name');
		$dir				= ROOT_PATH.UPLOAD_DIR;
		$subdir				= ROOT_PATH.FEEDBACK_IMAGE_DIR;
		$temp_file			= $_FILES['file']['tmp_name'];
		$ext				= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		$vals = 			@getimagesize($temp_file);
		$width = $vals[0];
		$height = $vals[1];

		//check minimum dimension condition.
		if ($height < 350 || $width < 670) {
			$invalid_size = str_replace("{max_height}",'350',$this->admin_lang['feedback_image_invalid_size']);
			$invalid_size = str_replace("{max_width}",'670',$invalid_size);
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status']			= FALSE;
			$this->api_response_arry['message']			= $invalid_size;
			$this->api_response();
		}

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
			$this->check_folder_exist($subdir);
		}

		$file_name = time().".".$ext ;
		$filePath     = FEEDBACK_IMAGE_DIR.$file_name;
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
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['data'] = $data;
                    $this->api_response();
                }
            }catch(Exception $e){
            	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response();
            }
			/*--End amazon server upload code--*/
		} 
		else 
		{
			$config['allowed_types']	= 'jpg|png|jpeg|gif';
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
				$this->api_response_arry['message']		   = strip_tags($error);
				$this->api_response();
			}
			else
			{
				$uploaded_data = $this->upload->data();
				$image_path =   '../'.FEEDBACK_IMAGE_DIR. $uploaded_data['file_name'];
				$data = array(
                                            'image_url'=> $image_path,
                                            'image_name'=> $uploaded_data['file_name']
					);
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']			= $data;
				$this->api_response_arry['status']			= TRUE;
				$this->api_response();
				
			}
		}
	}

	/**
	* @Summary: check if folder exists otherwise create new
	* @create_date: 24 july, 2015
	*/
	private function check_folder_exist($dir)
	{	
		if(!is_dir($dir))
			return mkdir($dir, 0777);
		return TRUE;
	}
}

/* End of file Feedback.php */
/* Location: ./application/controllers/Feedback.php */