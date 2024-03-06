<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Booster extends MYREST_Controller{

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
		$this->load->model('Booster_model');
	}

	/**
	* Function used for get boosters list
	* @param int $sports_id
	* @return string
	*/
	public function get_booster_list_post()
	{
		$this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->post();
		$result = $this->Booster_model->get_booster_list($post_data['sports_id']);
		$this->api_response_arry['data']	= $result;
		$this->api_response();
	}

	/**
	* Function used for get position list
	* @param int $sports_id
	* @return string
	*/
	public function get_position_list_post()
	{
		$this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->post();
		$result = $this->Booster_model->get_all_position($post_data['sports_id'],'master_lineup_position_id as position_id,position_name as position');
		$position_list = array();
		if(!empty($result)){
			$position_list[] = array("position_id"=>"0","position"=>"All");
			$position_list = array_merge($position_list,$result);
		}
		$this->api_response_arry['data'] = $position_list;
		$this->api_response();
	}

	/**
	* Function used for save booster
	* @param int $sports_id
	* @return string
	*/
	public function save_booster_post()
	{
	  	$post_data = $this->post();
	  	if(empty($post_data)){
	  		$this->api_response_arry['response_code'] = 500;
			$this->api_response_arry['message'] = 'Booster data required.';
			$this->api_response();
	  	}

	  	$points_val = array_column($post_data,"points");
	  	if(empty($points_val) || min($points_val) < 1 || max($points_val) > 50){
	  		$this->api_response_arry['response_code'] = 500;
			$this->api_response_arry['message'] = 'Booster points should be 1 to 50.';
			$this->api_response();
	  	}
	  	
	  	foreach($post_data as $row){
	  		if(isset($row['booster_id'])){
		  		$data_arr = array();
		  		$data_arr['position_id'] = isset($row['position_id']) ? $row['position_id'] : 0;
		  		$data_arr['image_name'] = isset($row['image_name']) ? $row['image_name'] : '';
		  		$data_arr['points'] = isset($row['points']) ? $row['points'] : '0.00';
		  		$data_arr['status'] = isset($row['status']) ? $row['status'] : 0;
		  		$data_arr['date_modified'] = format_date();
		  		if(isset($row['display_name']) && $row['display_name'] != ""){
		  			$data_arr['display_name'] = $row['display_name'];
		  		}
		  		$this->Booster_model->update_booster_by_id($row['booster_id'], $data_arr);
	  		}
	  	}
	  	$this->delete_wildcard_cache_data("booster_list_");

	  	$this->api_response_arry['message']	= "Boosters details saved successfully.";
		$this->api_response();
	}

	public function do_upload_post() 
	{
		$post_data = $this->post();
		$file_field_name	= 'userfile';
		$dir				= ROOT_PATH.BOOSTER_IMAGE_DIR;
		$s3_dir				= BOOSTER_IMAGE_DIR;
		$temp_file	= $_FILES['userfile']['tmp_name'];
		$ext		= pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
		$vals		= @getimagesize($temp_file);
		$width		= $vals[0];
		$height		= $vals[1];
		$image_height = "74";
		$image_width = "74";
		if($height != $image_height || $width != $image_width)
		{
			$invalid_size = str_replace("{max_height}",$image_height,$this->lang->line('ad_image_invalid_size'));
			$invalid_size = str_replace("{max_width}",$image_width,$invalid_size);
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $invalid_size;
			$this->api_response();
		}

		if (!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > 4194304) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_image_size'));
            $this->api_response();
        }

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
		}
		
		$file_name = time().".".$ext;
		$filePath = $s3_dir.$file_name;
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
			$config['max_size'] = '4048'; //4 mb
			$config['max_width'] = $image_width;
			$config['max_height'] = $image_height;
			$config['upload_path'] = $dir;
			$config['file_name'] = $file_name;
			$this->load->library('upload', $config);
			if (!$this->upload->do_upload($file_field_name))
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = strip_tags($this->upload->display_errors());
				$this->api_response();
			}
			else
			{
				$uploaded_data = $this->upload->data();
				$image_path = BOOSTER_IMAGE_DIR.$uploaded_data['file_name'];
				$data = array(
							'image_url'=> $image_path,
							'file_name'=> $uploaded_data['file_name']
						);
				$this->api_response_arry['data'] = $data;
				$this->api_response();
			}
		}		
	}

	public function remove_image_post()
	{
		$this->form_validation->set_rules('file_name', 'file name', 'required');
		if(!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->post();
		$file_name = $post_data['file_name'];
		$dir = ROOT_PATH.BOOSTER_IMAGE_DIR;
		if (strtolower(IMAGE_SERVER) == 'remote')
		{
			$file_path = BOOSTER_IMAGE_DIR.$file_name;
			$data_arr = array();
            $data_arr['file_path'] = $filePath;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $upload_lib->delete_file($data_arr);
		}else{
			$file_path = $dir.$file_name;
			@unlink($file_path);
		}

		$this->api_response_arry['message'] = "File deleted successfully.";
		$this->api_response();
	}

	/**
	* Function used for get fixture wise booster list
	* @param int $collection_master_id
	* @return string
	*/
	public function get_fixture_apply_booster_post(){
        $this->form_validation->set_rules('collection_master_id','collection master id','trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $current_date = format_date();
        $cm_id = $post_data['collection_master_id'];
        $this->load->model(array('season/Season_model'));
        $fixture_detail = $this->Season_model->get_fixture_detail($cm_id);
        //echo "<pre>";print_r($fixture_detail);die;
        if(empty($fixture_detail)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid collection id. please provide valid id.";
            $this->api_response();
        }else if(strtotime($fixture_detail['season_scheduled_date']) <= strtotime($current_date)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_start_booster_error');
            $this->api_response();
        }
        
        $result = $this->Booster_model->get_fixture_apply_booster($cm_id,$fixture_detail['sports_id']);
        //echo "<pre>";print_r($result);die;
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
	* Function used for get fixture wise booster list
	* @param int $collection_master_id
	* @return string
	*/
	public function save_fixture_booster_post(){
		$this->form_validation->set_rules('collection_master_id','collection master id','trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $current_date = format_date();
        $cm_id = $post_data['collection_master_id'];
        if(empty($post_data['booster'])){
	  		$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = 'Please select atleast one booster.';
			$this->api_response();
	  	}

        $this->load->model(array('season/Season_model'));
        $fixture_detail = $this->Season_model->get_fixture_detail($cm_id);
        //echo "<pre>";print_r($fixture_detail);die;
        if(empty($fixture_detail)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid collection id. please provide valid id.";
            $this->api_response();
        }else if(strtotime($fixture_detail['season_scheduled_date']) <= strtotime($current_date)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_start_booster_error');
            $this->api_response();
        }

        //save booster
        foreach($post_data['booster'] as $booster_id){
        	$check_exist = $this->Booster_model->check_fixture_booster_exist(array("booster_id"=>$booster_id,"collection_master_id"=>$cm_id));
        	if(empty($check_exist)){
        		$boosterInfo = $this->Booster_model->get_booster_detail($booster_id);
	        	$tmp_arr = array();
	        	$tmp_arr['booster_id'] = $booster_id;
	        	$tmp_arr['collection_master_id'] = $cm_id;
	        	$tmp_arr['position_id'] = $boosterInfo['position_id'];
	        	$tmp_arr['points'] = $boosterInfo['points'];
	        	$this->Booster_model->save_fixture_booster($tmp_arr);
	        }
        }

		$this->push_s3_data_in_queue("lobby_fixture_list_".$fixture_detail['sports_id'],array(),"delete");
        $this->delete_cache_data("booster_list_".$cm_id);
		
        $this->api_response_arry['message'] = $this->lang->line('match_booster_save_success');
        $this->api_response();
    }
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */
