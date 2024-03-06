<?php defined('BASEPATH') OR exit('No direct script access allowed');

class League extends Common_Api_Controller{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/League_model');

	}

	/**
     * Used for get sports list 
     * @param 
     * @return json array
     */   
	public function get_sports_list_post()
	{
		$post_data = $this->input->post();
		$sports_list = $this->get_sports_list();
		$this->api_response_arry['data'] = $sports_list;
		$this->api_response();
	}

	/**
     * Used for get sports wise positiom list 
     * @param int $sports_id
     * @return json array
     */
	public function get_position_list_post()
	{
		$this->form_validation->set_rules('sports_id', 'Sports Id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
		$sports_id = $post_data['sports_id'];
		$position_list = $this->get_position_list($sports_id);
		$this->api_response_arry['data'] = $position_list;
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
		$keep_name = isset($post_data['keep_name']) ? $post_data['keep_name'] : "0";
		$prefix = isset($post_data['prefix']) ? $post_data['prefix'] : "";
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
		$width = $height = "";
		if($ext != "pdf"){
			$vals = @getimagesize($temp_file);
			$width = $vals[0];
			$height = $vals[1];
		}
		//media type validation
		if(!in_array($ext,$media_config['type'])){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{media_type}",implode(",",$media_config['type']),$this->lang->line('image_invalid_ext'));
            $this->api_response();
		}

		//media size
		if($height > $media_config['max_h'] || $width > $media_config['max_w']){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{max_width}x{max_height}",$media_config['max_w']."x".$media_config['max_h'],$this->lang->line('image_invalid_dim'));
            $this->api_response();
		}

		if($height < $media_config['min_h'] || $width < $media_config['min_w']){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{max_width}x{max_height}",$media_config['max_w']."x".$media_config['max_h'],$this->lang->line('image_invalid_dim'));
            $this->api_response();
		}

		if($size > $media_config['size']){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{size}",$media_config['size'],$this->lang->line('image_invalid_size_error'));
            $this->api_response();
		}

		// echo "this" ; die;
		
		if($keep_name == 1){
			$org_file_name = pathinfo($_FILES[$field_name]['name'], PATHINFO_FILENAME);
			$file_name = str_replace(" ","_",$org_file_name);
			$file_name = $file_name."_".time().".".$ext;
		}else{
			$file_name = time().".".$ext;
			if(isset($prefix) && $prefix != ""){
				$file_name = $prefix.$file_name;
			}
		}
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
            $this->api_response_arry['message'] = "Upload Successfully.";
	            $this->api_response();
            }
        }catch(Exception $e){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('image_file_upload_error');
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
     * Used for get league list 
     * @param int $sports_id
     * @return json array
     */   
	public function get_league_list_post()
	{
		// echo "here";die;
		$this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
		$result = $this->League_model->get_league_list($post_data);
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}


	/**
	* Function used for update league status
	* @param int $league_id
	* @param int $status
	* @return array
	*/
	public function update_status_post()
    {
        $this->form_validation->set_rules('league_id', 'league id', 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $league_id = $post_data['league_id'];
        $record_info = $this->League_model->get_single_row('league_id,status,auto_published',LEAGUE,array('league_id' => trim($league_id)));
        if(empty($record_info)){
            $this->api_response_arry['message'] = $this->lang->line("invalid_league_id");
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $status = 1;
        if($record_info['auto_published'] == "1"){
        	$status = 0;
        }
        $data_arr = array();
        $data_arr['auto_published'] = $status;
        $data_arr['updated_date'] = format_date();
        $result = $this->League_model->update(LEAGUE,$data_arr,array("league_id"=>$league_id));
        if(!$result){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("league_status_error");
            $this->api_response();
        }

        $this->api_response_arry['message'] = "Auto Publish Status Update Successfully";
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }

	/**
     * Used for get sports wise team list 
     * @param int $sports_id
     * @return json array
     */ 
	public function get_team_list_post()
	{
		$this->form_validation->set_rules('sports_id', 'sports id','trim|required');
		if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
		$response = $this->League_model->get_team_list($post_data);
		$this->api_response_arry['data'] = $response;
		$this->api_response();
	}


		/**
     * Function used for update team details
     * @param array $post_data
     * @return json array
     */
    public function save_team_details_post()
    {
        $this->form_validation->set_rules('team_id', 'Team ID','trim|required');
        $this->form_validation->set_rules('jersey', 'jersey','trim');
        $this->form_validation->set_rules('flag', 'flag','trim');
		if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $team_id = $post_data['team_id'];
		$team_data = array();
        if(!empty($post_data['flag']))
        {
        	$team_data['flag'] = $post_data['flag']; 
        }

        if(!empty($post_data['jersey']))
        {
        	$team_data['jersey'] = $post_data['jersey']; 
        }
        if(empty($team_data)){
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = "Please provide flag or jersey media file name.";
			$this->api_response();
        }

		$result = $this->League_model->update(TEAM,$team_data,array("team_id"=>$team_id));
		if($result)
        {
            $this->api_response_arry["message"] = $this->lang->line("team_edit_success");
			$this->api_response();
        }
        else
        {
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("team_edit_failure");
			$this->api_response();
        }
    }

	/**
     * Used for get template wise team list 
     * @param int $sports_id
     * @return json array
     */ 
	public function get_template_list_post()
	{
		$this->form_validation->set_rules('sports_id', 'sports id','trim|required');
		if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
		$response = $this->League_model->get_template_list($post_data);
		$this->api_response_arry['data'] = $response;
		$this->api_response();
	}

	/**
	* Function used for update league status
	* @param int $league_id
	* @param int $status
	* @return array
	*/
	public function update_template_status_post()
    {
        $this->form_validation->set_rules('template_id', 'Template id', 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $template_id = $post_data['template_id'];
        $record_info = $this->League_model->get_single_row('*',MASTER_TEMPLATE,array('template_id' => trim($template_id)));
        if(empty($record_info)){
            $this->api_response_arry['message'] = "Invalid Template Id";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $status = 1;
        if($record_info['status'] == "1"){
        	$status = 0;
        }
        $data_arr = array();
        $data_arr['status'] = $status;    
        $result = $this->League_model->update(MASTER_TEMPLATE,$data_arr,array("template_id"=>$template_id));
        if(!$result){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Error on updating Status";
            $this->api_response();
        }

        $this->api_response_arry['message'] = "Status Update Successfully";
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }

    /**
     * Used for get league list 
     * @param int $sports_id
     * @return json array
     */   
	public function get_fixture_list_post()
	{
		// echo "here";die;
		$this->form_validation->set_rules('league_id', 'league id', 'trim');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $league_id = $post_data['league_id'];
		$result = $this->League_model->get_fixture_list_by_league_id($league_id);
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

    /**
     * Used for get league list 
     * @param int $sports_id
     * @return json array
     */   
	public function get_all_league_list_post()
	{
		// echo "here";die;
		$this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
		$result = $this->League_model->get_all_league_list($post_data);
		$this->api_response_arry['data']['result'] = $result;
		$this->api_response();
	}


}