<?php defined('BASEPATH') OR exit('No direct script access allowed');

class App_banner extends MYREST_Controller{

	public function __construct()
	{
		parent::__construct();
		$_POST				= $this->input->post();
		$this->load->model('App_banner_model');
		$this->admin_lang = $this->lang->line('app_banner');
		$this->admin_roles_manage($this->admin_id,'content_management');
		//Do your magic here
		$this->admin_roles_manage($this->admin_id,'content_management');
	}

	public function add_app_banner_post()
	{
		  
			$this->form_validation->set_rules('banner_title', 'Title', 'trim|required');
			$this->form_validation->set_rules('banner_link', 'Link', 'trim|required');
			$this->form_validation->set_rules('banner_image', 'Image', 'trim|required');
			$this->form_validation->set_rules('image_name', 'Image Name', 'trim|required');
			if (!$this->form_validation->run()) 
			{
				$this->send_validation_errors();
			}
			
			$data_post	= $this->input->post();
			$update_data = $this->App_banner_model->set_all_banners_inactive();

			$result = $this->App_banner_model->create_app_banner($data_post);

			if($result)
			{
				//for delete s3 bucket file
				$this->deleteS3BucketFile("app_master_data.json");

				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['message']			= $this->admin_lang['banner_added_successfully'];
				$this->api_response();
			}
			else
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']			= $this->admin_lang["error_adding_banner"];
				$this->api_response();
			}
	}


	function do_upload_post() 
	{
		$pos_type = $this->post('post_type');

		$data_post			= $this->post();

		$file_field_name	= 'userfile';
		$dir				= ROOT_PATH.APP_BANNER_IMAGE_DIR;
		$s3_dir				= APP_BANNER_IMAGE_DIR;

		$temp_file	= $_FILES['userfile']['tmp_name'];
		$ext		= pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
		$vals		= @getimagesize($temp_file);
		$width		= $vals[0];
		$height		= $vals[1];
		
		/*if ($height != $type_detail['height'] || $width != $type_detail['width'])
		{
			
			$invalid_size = str_replace("{max_height}",$type_detail['height'],$this->admin_lang['ad_image_invalid_size']);
			$invalid_size = str_replace("{max_width}",$type_detail['width'],$invalid_size);

			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $invalid_size;
			$this->api_response();
		}*/

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
				$image_path =  APP_BANNER_IMAGE_DIR . $uploaded_data['file_name'];
				
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

	public function remove_image_post()
	{
		$image_name = $this->input->post('image_name');
		$dir = ROOT_PATH.APP_BANNER_IMAGE_DIR;
		$s3_dir = APP_BANNER_IMAGE_DIR;
		$dir_path    = $s3_dir.$image_name;
		if( strtolower( IMAGE_SERVER ) == 'remote' )
		{
			try{
	            $data_arr = array();
                $data_arr['file_path'] = $dir_path;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_deleted = $upload_lib->delete_file($data_arr);
                if($is_deleted){
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		            $this->api_response_arry['message'] = $this->admin_lang['image_removed'];
		            $this->api_response();
                }
	        }catch(Exception $e){
	        	$error_msg = 'Caught exception: '.  $e->getMessage(). "\n";
	            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $error_msg;
                $this->api_response();
	        }
		}
		@unlink($dir. $image_name);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['message']			= $this->admin_lang['image_removed'];
		$this->api_response();
	}

	public function get_app_banners_post()
	{
		$limit = 10;
		$data_post = $this->post();

		$start = 0;
		$fieldname = $data_post['sort_field'];
		$order = $data_post['sort_order'];

		if ($data_post['items_perpage'])
		{
			$limit = $data_post['items_perpage'];
		}

		if(isset($data_post['current_page']))
		{
			$start = $data_post['current_page']-1;
		}
		
		$offset = $limit * $start;

		$config['limit'] = $limit;
		$config['start'] = $offset;
		$config['fieldname'] = $fieldname;
		$config['order'] = $order;

		$app_banners = $this->App_banner_model->get_app_banners($config, FALSE);
		if(!empty($app_banners))
		{
			foreach($app_banners as $key => $value)
			{
				$app_banners[$key]['image_url'] = IMAGE_PATH.APP_BANNER_IMAGE_DIR.$value['banner_image'];
			}
		}
		//echo $this->db->last_query();
		//exit();
		$config['count_only'] = TRUE;

		$total = $this->App_banner_model->get_app_banners($config, TRUE);
		$order_sequence = $order == 'ASC' ? 'DESC' : 'ASC';
		$data = array(
							'result' => $app_banners,
							'current_page' => $offset,
							'total' => $total,
							'sort_field' => $fieldname,
							'sort_order' => $order_sequence
				  );
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $data;
		$this->api_response();
	}

	public function update_status_post()
	{		
			$this->form_validation->set_rules('status', 'Status', 'required');
			$this->form_validation->set_rules('app_banner_id', 'App Banner_id', 'trim|required');
			
			if(!$this->form_validation->run()) 
			{
				$this->send_validation_errors();
			}

			$dataArr = array("status" => $this->input->post('status'));
			if($dataArr['status'] == 1)
			{
				$update_data = $this->App_banner_model->set_all_banners_inactive();
			}
			$id = $this->input->post('app_banner_id');
			$result = $this->App_banner_model->update_banner_status_by_id($id, $dataArr);
			if ($result) 
			{
				//for delete s3 bucket file
				$this->deleteS3BucketFile("app_master_data.json");

				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['message']			= $this->admin_lang['banner_status_updated'];
				$this->api_response();
			} 
			else 
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']			= $this->admin_lang['unable_status_update'];
				$this->api_response();
			}
	}

	public function delete_app_banner_post()
	{
		$this->form_validation->set_rules('app_banner_id', 'App Banner ID', 'required');
			
		if(!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$result = $this->App_banner_model->delete_banner($post_data['app_banner_id']);
		if ($result) 
		{
			//for delete s3 bucket file
			$this->deleteS3BucketFile("app_master_data.json");

			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['message']			= $this->admin_lang['banner_deleted'];
			$this->api_response();
		} 
		else 
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $this->admin_lang['unable_to_delete_banner'];
			$this->api_response();
		}
	} 
	
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */