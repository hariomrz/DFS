<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Banner extends MYREST_Controller{

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
		$this->admin_lang = $this->lang->line('adv');
		$this->load->model('Banner_model');
		//Do your magic here
		$this->admin_roles_manage($this->admin_id,'content_management');
	}

	/**
	 * [get_banner_type_post description]
	 * Summary :- get banner type list
	 * @return [json] [description]
	 */
	public function get_banner_type_post()
	{
		$banner_type = $this->Banner_model->get_banner_type();
		$stock_key = array();

		 if(isset($this->app_config['allow_equity']['key_value']) && $this->app_config['allow_equity']['key_value']==1)
	      {
	        array_push($stock_key,"allow_equity");
	      }

	      if(isset($this->app_config['allow_stock_predict']['key_value']) && $this->app_config['allow_stock_predict']['key_value']==1)
	      {
	        array_push($stock_key,"allow_stock_predict");
	      }

	      if(isset($this->app_config['allow_live_stock_fantasy']['key_value']) && $this->app_config['allow_live_stock_fantasy']['key_value']==1)
	      {
	        array_push($stock_key,"allow_live_stock_fantasy");
	      }

	      if(isset($this->app_config['allow_stock_fantasy']['key_value']) && $this->app_config['allow_stock_fantasy']['key_value']==1)
	      {
	        array_push($stock_key,"allow_stock_fantasy");
	      }

		// $stock_key = array('allow_equity','allow_stock_predict','allow_live_stock_fantasy','allow_stock_fantasy');

		$get_active_stock_key= $this->Banner_model->get_active_stock_key($stock_key);	


		$game_type = $this->Banner_model->get_sports_type($get_active_stock_key);	


		foreach ($game_type as  &$value) {
			if ($value['game_key']== 'allow_stock_fantasy' || $value['game_key']== 'allow_equity' || $value['game_key']== 'allow_stock_predict' || $value['game_key']== 'allow_live_stock_fantasy' ){

				$value['en_title'] = 'Stock Game Only';
			}			
		}
		

		$res_data = array(
			"game_type"=>$game_type,
			"banner_type" => $banner_type
		);

		$this->api_response_arry['data'] = $res_data;	

		$this->api_response();
	}

	public function get_banners_post()
	{
		$data_post = $this->post();
		$data = $this->Banner_model->get_banner_list($data_post);
		foreach ($data as  &$value) {
			if ($value['game_key']== 'allow_stock_fantasy' || $value['game_key']== 'allow_equity' || $value['game_key']== 'allow_stock_predict' || $value['game_key']== 'allow_live_stock_fantasy' ){

				$value['sports_name'] = 'Stock Game Only';
			}
		}		
		$this->api_response_arry['data']	= $data;
		$this->api_response();
	}

	public function create_banner_post()
	{
	  	$data_post = $this->post();
		$this->form_validation->set_rules('name', 'name', 'trim|required');
		$this->form_validation->set_rules('game_type_id', 'game type', 'trim|required');
		$this->form_validation->set_rules('banner_type_id', 'banner type', 'trim|required');
		$this->form_validation->set_rules('image', 'image', 'trim|required');

		//for fixture type
		if($data_post['banner_type_id'] == 1){
			$this->form_validation->set_rules('collection_master_id', 'fixture id', 'trim|required');
		}

		//for other type
		if($data_post['banner_type_id'] == 4 || $data_post['banner_type_id'] == 7 || $data_post['banner_type_id'] == 8){
			$this->form_validation->set_rules('target_url', 'target_url', 'trim|required');
		}
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$result = $this->Banner_model->create_banner($data_post);

		//delte master json for banner type 7 & 8
		if($data_post['banner_type_id'] == 7 || $data_post['banner_type_id'] == 8){
			$this->load->model('page/Page_model');
			$this->Page_model->delete_s3_bucket_file("app_master_data.json");
		}

		$this->delete_banner_cache();
				$allow_pickem =  isset($this->app_config['allow_pickem'])?$this->app_config['allow_pickem']['key_value']:0;
                if($allow_pickem && $data_post['banner_type_id'] == 4){
                    $this->push_s3_data_in_queue('lobby_banner_list_pickem',array(),"delete");
                    $this->delete_cache_data('lobby_banner_list_pickem');
                }
		if ($result)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['message']			= 'Banner added';
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code']	= 500;
			$this->api_response_arry['message']			= 'Error';
			$this->api_response();
		}
	}

	private function delete_banner_cache()
	{
		$sports_ids = array(CRICKET_SPORTS_ID,SOCCER_SPORTS_ID,KABADDI_SPORTS_ID,FOOTBALL_SPORTS_ID,BASKETBALL_SPORTS_ID,BASEBALL_SPORTS_ID);
		$input_arr = array();
		$input_arr['lang_file'] = '1';
		$input_arr['file_name'] = 'lobby_banner_list_';
		$input_arr['sports_ids'] = $sports_ids;
		$this->delete_cache_and_bucket_cache($input_arr);
	}

	function do_upload_post() 
	{
		$banner_type_id = $this->post('banner_type_id');
		$data_post			= $this->post();
		
		$file_field_name	= 'userfile';
		$dir				= ROOT_PATH.BANNER_IMAGE_DIR;
		$s3_dir				= BANNER_IMAGE_DIR;

		$temp_file	= $_FILES['userfile']['tmp_name'];
		$ext		= pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
		$vals		= @getimagesize($temp_file);
		$width		= $vals[0];
		$height		= $vals[1];
		$image_height = "240";//"60";
		$image_width = "1300";//"340";

		$image_type	= $this->post('banner_type_id');
		
		if ($height != $image_height || $width != $image_width)
		{
			
			$invalid_size = str_replace("{max_height}",$image_height,$this->admin_lang['ad_image_invalid_size']);
			$invalid_size = str_replace("{max_width}",$image_width,$invalid_size);

			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $invalid_size;
			$this->api_response();
		}

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
		}
		
		$file_name = time() . "." . $ext;
		$filePath = $s3_dir . $file_name;
		
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
				$image_path =  BANNER_IMAGE_DIR . $uploaded_data['file_name'];
				
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
		$data_post			= $this->post();
		$result = $this->Banner_model->remove_image($data_post);
		$this->api_response_arry['data']	= $data;
		$this->api_response();
	}

	public function update_status_post()
	{		
		$this->form_validation->set_rules('status', 'status', 'required');
		$this->form_validation->set_rules('banner_id', 'banner_id', 'trim|required');
		
		if(!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$data_post = $this->post();
		$dataArr = array("status" => $this->input->post('status'));
		$id = $this->input->post('banner_id');
		$banner_type_id = $this->input->post('banner_type_id');
		$result = $this->Banner_model->update_banner_by_id($id, $dataArr);
		//delte master json for banner type 7 & 8
		if($banner_type_id == 7 || $banner_type_id == 8){
			$this->load->model('page/Page_model');
			$this->Page_model->delete_s3_bucket_file("app_master_data.json");
		}
		//delete banner infor from cache
		$banner_cache_key = "banner_detail_".$banner_type_id;
		$this->delete_cache_data($banner_cache_key);

		$this->delete_banner_cache();
				$allow_pickem =  isset($this->app_config['allow_pickem'])?$this->app_config['allow_pickem']['key_value']:0;
                if($allow_pickem && $banner_type_id == 4){
                    $this->push_s3_data_in_queue('lobby_banner_list_pickem',array(),"delete");
                    $this->delete_cache_data('lobby_banner_list_pickem');
                }

		$this->api_response_arry['data']	= $result;
		$this->api_response_arry['message']	= ($this->input->post('status')==1)? 'Activated':'Deactivated';
		$this->api_response();
	}

	public function delete_banner_post()
	{
		$this->form_validation->set_rules('banner_id', 'banner_id', 'trim|required');
		if(!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$data_post = $this->post();
		$dataArr = array("is_deleted" => "1");
		$id = $this->input->post('banner_id');
		$data = $this->Banner_model->update_banner_by_id($id, $dataArr);

		$this->delete_banner_cache();
				$allow_pickem =  isset($this->app_config['allow_pickem'])?$this->app_config['allow_pickem']['key_value']:0;
                if($allow_pickem){
                    $this->push_s3_data_in_queue('lobby_banner_list_pickem',array(),"delete");
                    $this->delete_cache_data('lobby_banner_list_pickem');
                }
		//delte master json for banner type 7 & 8
		$this->load->model('page/Page_model');
		$this->Page_model->delete_s3_bucket_file("app_master_data.json");

		$this->api_response_arry['data']	= $data;
		$this->api_response();
	}
	
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */