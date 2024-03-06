<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Merchandise extends MYREST_Controller{

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
		$this->admin_lang = $this->lang->line('merchandise');
		$this->load->model('Merchandise_model');
		//Do your magic here
	}

	/**
     * For uploading merchadise image
     * @param
     * @return json array
     */
	public function do_upload_post()
	{	
        $post_data = $this->input->post();
		$this->form_validation->set_rules('source', $this->admin_lang["source"],'trim|required');

		if($post_data['source'] == 'edit'){
			$this->form_validation->set_rules('merchandise_id', $this->admin_lang["merchandise_id"],'trim|required');
		}

        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }


		$dir				= APP_ROOT_PATH.UPLOAD_DIR;
		
		$temp_file_image	= $_FILES['file']['tmp_name'];
		$ext				= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

		if( strtolower( IMAGE_SERVER ) == 'remote' ){
			$file_name = $this->do_upload_process($ext);
			$temp_file = $dir.$file_name;
		}
		//echo "file name : ".$temp_file;die;

		$vals = @getimagesize($temp_file_image);
		$width = $vals[0];
		$height = $vals[1];
		
		/*$ratio 			= $width/$height;
		$ratio 				= number_format((float)$ratio, 2, '.', '');
		$ratio 			    = ceil($ratio*2)/2;*/

		$subdir				= ROOT_PATH.MERCHANDISE_IMAGE_DIR;
		$s3_dir 			= MERCHANDISE_IMAGE_DIR;
		if ($height > '151' || $width > '151') {//72x120
		
			$invalid_size = str_replace("{max_height}",'150',$this->admin_lang['invalid_image_size']);
			$invalid_size = str_replace("{max_width}",'150',$invalid_size);
			//$this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>$invalid_size) , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry["message"] = $invalid_size;
			$this->api_response();
		}

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
			$this->check_folder_exist($subdir);
		}

		$file_name = time().".".$ext ;
		$filePath     = $s3_dir.$file_name;
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
					@unlink($temp_file);
					if($post_data['source'] == 'edit'){
						$merchandise_data = $this->Merchandise_model->get_product_by_id($post_data);
						$post_data['image_name'] = $merchandise_data['image_name'];
						$post_data['new_image_name'] = $file_name;
						$this->remove_merchandise_image($post_data);
					}

					if(!empty($post_data['previous_image']) && $post_data['source'] == 'add'){
						$post_data['image_name'] = $post_data['previous_image'];
						$this->remove_merchandise_image($post_data);
					}

					$this->api_response_arry["data"] = $data;
					$this->api_response_arry["response_code"] = rest_controller::HTTP_OK;
					$this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response();
            }
			/*--End amazon server upload code--*/
		} else {

			$config['allowed_types']	= 'jpg|png|jpeg|gif|PNG';
			$config['max_size']			= '2000';
			$config['max_width']		= '2400';
			$config['max_height']		= '1200';
			$config['min_width']		= '64';
			$config['min_height']		= '42';
			$config['upload_path']		= $dir;
			$config['file_name']		= time();

			$this->load->library('upload', $config);
			if ( ! $this->upload->do_upload('file'))
			{
				$error = $this->upload->display_errors();
				$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry["message"] = strip_tags($error);
				$this->api_response();
			}
			else
			{
				$upload_data = $this->upload->data();
				$this->api_response_arry["data"] = array('image_name' =>IMAGE_PATH.$s3_dir.$file_name ,'image_url'=> $subdir);
				$this->api_response();
			}
		}		
	}

	/**
    * [do_upload_process description]
    * Summary :- internal function used to upload merchandise to local folder.
    */
	public function do_upload_process($ext)
	{
		$dir						= APP_ROOT_PATH.UPLOAD_DIR;
		$config['image_library'] 	= 'gd2';
		$config['allowed_types']	= 'jpg|png|jpeg|gif|PNG';
		$config['max_size']			= '2000';
		$config['min_width']		= '36';//64
		$config['min_height']		= '36';//42
		$config['max_width']		= '2400';
		$config['max_height']		= '1200';
		$config['upload_path']		= $dir;
		$config['file_name']		= rand(1,1000).time().'.'.$ext;

		$this->load->library('upload', $config);

		
		if ( ! $this->upload->do_upload('file'))
		{
			
			$error = $this->upload->display_errors();
			$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry["message"] = strip_tags($error);
			$this->api_response();
		}
		else
		{
			$upload_data = $this->upload->data();
			$config1['image_library']	= 'gd2';
			$config1['source_image']	= $dir.$config['file_name'];
			//$config['create_thumb']		= TRUE;
			$config1['maintain_ratio']	= TRUE;
			$config1['width']			= 200;
			$config1['height']			= 200;
			
			$this->load->library('image_lib', $config1);
			if ( !$this->image_lib->resize())
			{
				$error = $this->image_lib->display_errors();
		        $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		        $this->api_response_arry["message"] = strip_tags($error);
				$this->api_response();
			}
			return $config['file_name'];

		}
	}

	 /**
     * For saving merchandise 
     * @param
     * @return json array
     */

	public function add_merchandise_post()
	{
		$this->form_validation->set_rules('name', $this->admin_lang["merchandise_name"], 'trim|required|max_length[50]');
        $this->form_validation->set_rules('price', $this->admin_lang["merchandise_price"], 'trim|numeric|max_length[7]');
        $this->form_validation->set_rules('image_name', $this->admin_lang["merchandise_image"],'trim|required');

        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();

        $post_data['price'] = isset($post_data['price']) ? $post_data['price'] : 0 ;

        $data_inserted = $this->Merchandise_model->insert_merchandise($post_data);

        if($data_inserted){
        	$this->api_response_arry["message"] = $this->admin_lang['success_add_merchandise'];
			$this->api_response_arry["response_code"] = rest_controller::HTTP_OK;
			$this->api_response();
        }else{
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["error"] = $this->admin_lang['error_in_add_merchandise'];
			$this->api_response();
        }
	}

	 /**
     * Get merchandise info by merchandise ID 
     * @param
     * @return json array
     */

	public function get_merchandise_by_id_post()
	{
		$this->form_validation->set_rules('merchandise_id', $this->admin_lang["merchandise_id"], 'trim|required');
        
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();

        $merchadise_info = $this->Merchandise_model->get_product_by_id($post_data);

        if(!empty($merchadise_info)){
        	$this->api_response_arry["data"] = $merchadise_info;
			$this->api_response_arry["response_code"] = rest_controller::HTTP_OK;
			$this->api_response();
        }else{
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["error"] = $this->admin_lang['merchandise_not_found'];
			$this->api_response();
        }
	}

	 /**
     * Get list of all merchandises 
     * @param
     * @return json array
     */

	public function get_all_merchandise_post()
	{
		$post_data = $this->input->post();
		$list = $this->Merchandise_model->get_all_merchandise($post_data);

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $list;
		$this->api_response();
	}

	/**
     * internal funcation to Remove uploaded merchandise image and remove from table as well 
     * @param
     * @return json array
     */

	public function remove_merchandise_image($post_data)
	{
		$image_name = $post_data['image_name'];
		$dir = ROOT_PATH.MERCHANDISE_IMAGE_DIR;
		$s3_dir = MERCHANDISE_IMAGE_DIR;
		$dir_path    = $s3_dir.$image_name;
		if( strtolower( IMAGE_SERVER ) == 'remote' )
		{
			try{
                $data_arr = array();
                $data_arr['file_path'] = $dir_path;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_deleted = $upload_lib->delete_file($data_arr);
                if(!$is_deleted){
                    return false;
                }
            }catch(Exception $e){
                return false;
            }
		}
		// for removing the team name from DB
		if(!empty($post_data['merchandise_id']))
		{
			$update_data = array("image_name" => $post_data['new_image_name']);
			$this->Merchandise_model->update_merchandise_by_id($update_data,$post_data['merchandise_id']);
		}

		return true;
	}

	/**
     * update merchandise
     * @param
     * @return json array
     */

	public function update_merchandise_post()
	{
		$this->form_validation->set_rules('merchandise_id', $this->admin_lang["merchandise_id"],'trim|required');
		$this->form_validation->set_rules('name', $this->admin_lang["merchandise_name"], 'trim|required|max_length[50]');
        $this->form_validation->set_rules('price', $this->admin_lang["merchandise_price"], 'trim|numeric|max_length[7]');
        $this->form_validation->set_rules('image_name', $this->admin_lang["merchandise_image"],'trim|required');

        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();

        $update_data = array(
        					"image_name" => $post_data['image_name'],
        					"name" => $post_data['name'],
        					"price" => (!empty($post_data['price'])) ? $post_data['price'] : 0,
        					"updated_date" => format_date()
    					);
		$updated = $this->Merchandise_model->update_merchandise_by_id($update_data,$post_data['merchandise_id']);
		if($updated){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['message']			= $this->admin_lang['merchandise_updated'];
			$this->api_response();
		}else{
			$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["error"] = $this->admin_lang['update_error'];
			$this->api_response();
		}
	}
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */