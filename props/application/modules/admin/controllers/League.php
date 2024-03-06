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
     * Used for get sports wise props list 
     * @param int $sports_id
     * @return json array
     */
	public function get_props_list_post()
	{
		$this->form_validation->set_rules('sports_id', 'Sports Id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
		$sports_id = $post_data['sports_id'];
		$props_list = $this->get_props_list($sports_id);
		$this->api_response_arry['data'] = $props_list;
		$this->api_response();
	}

	/**
     * Used for get payout list 
     * @param void
     * @return json array
     */
	public function get_payout_list_post()
	{
		$post_data = $this->input->post();
        $payout_type = array();
		if($this->props_config['flexplay'] == "1"){
			$payout_type[] = 1;
		}
		if($this->props_config['powerplay'] == "1"){
            $payout_type[] = 2;
		}
        $payout_list = array();
        if(!empty($payout_type)){
            $payout_list = $this->League_model->get_payout_list($payout_type);
        }

        $data['max_bet'] = $this->props_config['max_bet'];
        $data['min_bet'] = $this->props_config['min_bet'];


		$this->api_response_arry['data']['payout'] = $payout_list;
		$this->api_response_arry['data']['props_config'] = $data;
		$this->api_response();
	}

	/**
	* Function used for update payout data
	* @param array $post_data
	* @return array
	*/
	public function update_payout_post()
    {
        $post_data = $this->input->post();
        if(empty($post_data)){
        	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Please update atleast one payout data.";
            $this->api_response();
        }
        // echo "<pre>";print_r($post_data);die;
        foreach($post_data as $row){
        	$data_arr = array();
	        $data_arr['points'] = $row['points'];
	        $data_arr['modified_date'] = format_date();
	        $this->League_model->update(MASTER_PAYOUT,$data_arr,array("payout_id"=>$row['payout_id']));
        }
		$this->delete_cache_data("props_payout_1");
		$this->delete_cache_data("props_payout_2");

        $this->api_response_arry['message'] = $this->lang->line("payout_update_success");
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }

    /**
    * Function used for update league status
    * @param int $league_id
    * @param int $status
    * @return array
    */
    public function update_payout_status_post()
    {
        $this->form_validation->set_rules('payout_id', 'payout id', 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $payout_id = $post_data['payout_id'];
        $record_info = $this->League_model->get_single_row('payout_id,payout_type,status',MASTER_PAYOUT,array('payout_id' => trim($payout_id)));
        if(empty($record_info)){
            $this->api_response_arry['message'] = $this->lang->line("invalid_payout_id");
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $status = 1;
        if($record_info['status'] == "1"){
            $status = 0;
        }
        $data_arr = array();
        $data_arr['status'] = $status;
        $data_arr['modified_date'] = format_date();
        $result = $this->League_model->update(MASTER_PAYOUT,$data_arr,array("payout_id"=>$payout_id));
        if(!$result){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("payout_status_error");
            $this->api_response();
        }

        $this->delete_cache_data("props_payout_".$record_info['payout_type']);

        $this->api_response_arry['message'] = $this->lang->line("payout_status_success");
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }

	/**
     * Used for get league list 
     * @param int $sports_id
     * @return json array
     */   
	public function get_league_list_post()
	{
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
        $record_info = $this->League_model->get_single_row('league_id,status',LEAGUE,array('league_id' => trim($league_id)));
        if(empty($record_info)){
            $this->api_response_arry['message'] = $this->lang->line("invalid_league_id");
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $status = 1;
        if($record_info['status'] == "1"){
        	$status = 0;
        }
        $data_arr = array();
        $data_arr['status'] = $status;
        $data_arr['updated_date'] = format_date();
        $result = $this->League_model->update(LEAGUE,$data_arr,array("league_id"=>$league_id));
        if(!$result){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("league_status_error");
            $this->api_response();
        }

        $this->api_response_arry['message'] = $this->lang->line("league_status_success");
        $this->api_response_arry['data'] = array();
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
     * Used for get sports wise players list 
     * @param int $sports_id
     * @return json array
     */ 
	public function get_player_list_post()
	{
		$this->form_validation->set_rules('sports_id', 'sports id','trim|required');
		if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
		$response = $this->League_model->get_player_list($post_data);
		$this->api_response_arry['data'] = $response;
		$this->api_response();
	}

	/**
     * Function used for update player details
     * @param array $post_data
     * @return json array
     */
    public function save_player_image_post()
    {
        $this->form_validation->set_rules('player_id', 'Player ID','trim|required');
        $this->form_validation->set_rules('image', 'image','trim|required');
		if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $player_id = $post_data['player_id'];
        $image = $post_data['image'];
		$result = $this->League_model->update(PLAYER,array("image"=>$image),array("player_id"=>$player_id));
		if($result)
        {
            $this->api_response_arry["message"] = $this->lang->line("player_edit_success");
			$this->api_response();
        }
        else
        {
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("player_edit_failure");
			$this->api_response();
        }
    }

   /**
    * Function used to save min and max bet
    * @param min_bet,max_bet
    * string message
    */
    public function save_min_max_bet_post()
    {
        $this->form_validation->set_rules('min_bet', 'Min Bet','trim|required');
        $this->form_validation->set_rules('max_bet', 'Max Bet','trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $min_bet = $post_data['min_bet'];
        $max_bet = $post_data['max_bet'];

        if($min_bet >= $max_bet  || $max_bet <$min_bet){
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry["message"] = 'Min bet should be less than max bet';
            $this->api_response(); 
        }

        $this->props_config['min_bet'] = $min_bet;
        $this->props_config['max_bet'] = $max_bet;        

        $this->League_model->update_config($this->props_config);

        $this->delete_cache_data('app_config');
        $this->push_s3_data_in_queue('app_master_data',array(),"delete");
        
        $this->api_response_arry["message"] = 'Min,Max bet saved Successfully.';
        $this->api_response();
    }
}