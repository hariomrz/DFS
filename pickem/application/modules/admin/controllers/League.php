<?php defined('BASEPATH') OR exit('No direct script access allowed');

class League extends Common_Api_Controller{

	public function __construct()
	{
		parent::__construct();
	}

	/**
     * Used for get sports list 
     * @param int $sports_id
     * @return json array
     */   
	public function get_sport_list_post()
	{
        $sports_list = $this->get_sports_list();
		$this->api_response_arry['data'] = $sports_list;
		$this->api_response();
	}

	/**
     * Used for get league detail 
     * @param int $league_id
     * @return json array
     */   
	public function get_league_detail_post()
	{
		$this->form_validation->set_rules('league_id', 'league id','trim|required');
		if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
		
		$post_data = $this->input->post();
		$league_id = $post_data['league_id'];
		$this->load->model('admin/League_model');
		$result = $this->League_model->get_single_row("league_id,league_uid,league_abbr,IFNULL(display_name,league_name) AS league_name,start_date,end_date",LEAGUE, array("league_id" => $league_id));
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	/**
     * Used for upload media file
     * @param array $post_data
     * @return json array
     */
	public function do_upload_post() 
	{
		$post_data = $this->input->post();
		$type = isset($post_data['type']) ? $post_data['type'] : "";
		$media_config = get_media_setting($type);
		if(empty($media_config)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid media config.";
            $this->api_response();
		}

		$field_name	= 'file_name';
		$dir = ROOT_PATH.$media_config['path']."/";
		$s3_dir = $media_config['path']."/";
		$temp_file = $_FILES[$field_name]['tmp_name'];
		$size = number_format(($_FILES[$field_name]['size'] / 1000000),"2",".","");
		$ext = pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION);
		$vals = @getimagesize($temp_file);
		$width = $vals[0];
		$height = $vals[1];
		//media type validation
		if(!in_array($ext,$media_config['type'])){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{media_type}",implode(",",$media_config['type']),$this->lang->line('image_invalid_ext'));
            $this->api_response();
		}

		//media size
		if($height < $media_config['max_h'] || $width < $media_config['max_w']){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{max_width}x{max_height}",$media_config['max_w']."x".$media_config['max_h'],$this->lang->line('image_invalid_dim'));
            $this->api_response();
		}

		if($size > $media_config['size']){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{size}",$media_config['size'],$this->lang->line('image_invalid_size'));
            $this->api_response();
		}
		
		$file_name = time().".".$ext;
		$filePath = $s3_dir.$file_name;
		try{
            $data_arr = array();
            $data_arr['file_path'] = $filePath;
            $data_arr['source_path'] = $temp_file;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_uploaded = $upload_lib->upload_file($data_arr);
            if($is_uploaded){
	            $this->api_response_arry['data'] = array('image_url'=>  IMAGE_PATH.$filePath,'image_name'=> $file_name);
	            $this->api_response();
            }
        }catch(Exception $e){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('file_upload_error');
            $this->api_response();
        }		
	}

	/**
     * Used for remove media file from bucket
     * @param array $post_data
     * @return json array
     */
	public function remove_media_post()
	{
		$post_data = $this->input->post();
		$type = isset($post_data['type']) ? $post_data['type'] : "";
		$media_config = get_media_setting($type);
		if(empty($media_config)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid media config.";
            $this->api_response();
		}

		$file_name	= $post_data['file_name'];
		$file_path = $media_config['path']."/".$file_name;
		try{
            $data_arr = array();
            $data_arr['file_path'] = $file_path;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_deleted = $upload_lib->delete_file($data_arr);
            if($is_deleted){
	            $this->api_response_arry['message'] = $this->lang->line('media_removed');
	            $this->api_response();
            }
        }catch(Exception $e){
        	$error_msg = 'Caught exception: '.  $e->getMessage(). "\n";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $error_msg;
            $this->api_response();
        }
	}


	/**
     * Used for get sports wise league list 
     * @param int $sports_id
     * @return json array
     */
	public function get_leagues_list_post()
	{
		$this->form_validation->set_rules('sports_id', 'sports id','trim|required');
		if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
		
		$post_data = $this->input->post();
		$this->load->model('admin/League_model');
		$result = $this->League_model->get_active_league_list($post_data);
		if(!empty($result)){
			$featured = $this->League_model->get_featured_league_count($post_data['sports_id']);
			$result['count_featured'] = isset($featured['total']) ? $featured['total'] : 0;
		}
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	/**
	* Function used for update league featured status
	* @param int $sports_id
	* @param int $league_id
	* @param int $is_featured
	* @return array
	*/ 
	public function update_is_featured_post()
	{
		$this->load->model('admin/League_model');
		$this->form_validation->set_rules('sports_id', 'sports id','trim|required');
		$this->form_validation->set_rules('league_id', 'league id','trim|required');
		$this->form_validation->set_rules('is_featured', 'is featured','trim|required');
		if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
		
		$post_data = $this->input->post();
	    $is_featured = $post_data['is_featured'];
	    $featured = $this->League_model->get_featured_league_count($post_data['sports_id']);
	    $total_featured = isset($featured['total']) ? $featured['total'] : 0;
       	if($total_featured >= ALLOW_FEATURED_LEAGUE && $is_featured == 1) {
       	   $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "You can mark upto 3 leagues in featured.";
            $this->api_response();     
        }		

        // $this->League_model->update_outdated_featured_status();
		$result = $this->League_model->update_leagues_featured_status($post_data);
		if($result){
			//check for update sports hub featured leagues
			$league_info = $this->League_model->get_league_detail($post_data['league_id']);
			if(!empty($league_info)){
					$this->load->model('user/User_model');
				$league_info['is_featured'] = $is_featured;
				$this->User_model->save_featured_league($league_info);
			}
			$this->delete_cache_data('lobby_fixture_list_'.$post_data['sports_id']);

			//delete lobby upcoming section file
			$input_arr = array();
			$input_arr['lang_file'] = '0';
			$input_arr['file_name'] = 'lobby_fixture_list_';
			$input_arr['sports_ids'] = array($post_data['sports_id']);
			$this->delete_cache_and_bucket_cache($input_arr);
			$this->push_s3_data_in_queue('app_master_data',array(),"delete");

			$this->api_response_arry['message'] = "Data updated successfully.";
	        $this->api_response();
		}else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = "Something went wrong while update status. please try again.";
            $this->api_response();
		}
	}
}