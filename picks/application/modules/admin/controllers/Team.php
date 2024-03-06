<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Team extends Common_Api_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/Team_model');
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
		$response = $this->Team_model->get_team_list($post_data);
		$this->api_response_arry['data'] = $response;
		$this->api_response();
	}

	/**
     * Used for save team details
     * @param array $post_data
     * @return json array
     */
	public function save_team_post()
	{
		$this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
		$this->form_validation->set_rules('team_name', 'team name', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('team_abbr', 'team abbrivation', 'trim|required|max_length[15]');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $sports_id = trim($post_data['sports_id']);
        $team_name = trim($post_data['team_name']);
        $team_abbr = trim($post_data['team_abbr']);
        $team_id = isset($post_data['team_id']) ? $post_data['team_id'] : "";
        $check_exist = $this->Team_model->get_single_row("team_id",TEAM,array("LOWER(team_name)" => strtolower($team_name),"sports_id"=>$sports_id));
        $check_abbr = $this->Team_model->get_single_row("team_id",TEAM,array("LOWER(team_abbr)" => strtolower($team_abbr),"sports_id"=>$sports_id));
        if(!empty($check_exist) && $check_exist['team_id'] != $team_id){
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("team_name_exist");
			$this->api_response();
        }else if(!empty($check_abbr) && $check_abbr['team_id'] != $team_id){
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("team_abbr_exist");
			$this->api_response();
        }else{
        	if($team_id != ""){
                
            $this->load->model('season_model'); 
                   
	        $season_info = $this->season_model->check_team_exit($team_id);

	        $home_id = array_column($season_info,"home_id");

	        if(in_array($team_id, $home_id))
			{
			  	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;		
		        $this->api_response_arry["message"] = $this->lang->line("not_able_to_delete");
				$this->api_response();
			}
			$away_id = array_column($season_info,"away_id");

	        if(in_array($team_id, $away_id))
			{
			  	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;		
		        $this->api_response_arry["message"] = $this->lang->line("not_able_to_delete");
				$this->api_response();
			}	

        		$data_arr = array();
	        	$data_arr['team_abbr'] = $team_abbr;
	        	$data_arr['team_name'] = $team_name;
	        	if(isset($post_data['flag']) && $post_data['flag'] != ""){
	        		$data_arr['flag'] = $post_data['flag'];
	        	}
	        	if(isset($post_data['jersey']) && $post_data['jersey'] != ""){
	        		$data_arr['jersey'] = $post_data['jersey'];
	        	}
	        	$data_arr['modified_date'] = $current_date;
	        	$result = $this->Team_model->update(TEAM,$data_arr,array("team_id"=>$team_id,"sports_id"=>$sports_id));
	        }else{
	        	$team_uid = generate_entity_uid($team_name);
	        	$data_arr = array();
	        	$data_arr['sports_id'] = $sports_id;
	        	$data_arr['team_uid'] = $team_uid;
	        	$data_arr['team_abbr'] = $team_abbr;
	        	$data_arr['team_name'] = $team_name;
	        	if(isset($post_data['flag']) && $post_data['flag'] != ""){
	        		$data_arr['flag'] = $post_data['flag'];
	        	}
	        	if(isset($post_data['jersey']) && $post_data['jersey'] != ""){
	        		$data_arr['jersey'] = $post_data['jersey'];
	        	}
	        	$data_arr['created_date'] = $current_date;
	        	$data_arr['modified_date'] = $current_date;
	        	$result = $this->Team_model->save_record(TEAM,$data_arr);
	        }
	        if($result){
	        	$this->api_response_arry["message"] = $this->lang->line("team_save_success");
				$this->api_response();
	        }else{
	        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		        $this->api_response_arry["message"] = $this->lang->line("something_went_wrong");
				$this->api_response();
	        }
        }
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
		if($height > $media_config['max_h'] || $width > $media_config['max_w']){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{max_width}x{max_height}",$media_config['max_w']."x".$media_config['max_h'],$this->lang->line('image_invalid_dim'));
            $this->api_response();
		}

		if($size > $media_config['size']){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = str_replace("{size}",$media_config['size'],$this->lang->line('image_invalid_size_error'));
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
     * Used for delete players 
     * @param array $post_data
     * @return json array
     */
	public function delete_team_post()
	{
		$this->form_validation->set_rules('team_id',  'Team id', 'trim|required|integer');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $team_id = trim($post_data['team_id']);

        $this->load->model('season_model');        
        $season_info = $this->season_model->check_team_exit($team_id); 
        
        if(count($season_info)>0)
		{
		  	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;		
	        $this->api_response_arry["message"] = $this->lang->line("not_able_to_delete");
			$this->api_response();
		}			

        $this->load->model('team_model'); 
        $record_info = $this->team_model->get_single_row("*",TEAM,array("team_id" => $team_id));
        if(empty($record_info)){
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("detail_not_found");
			$this->api_response();
        }else{        	
        	
        	$result = $this->Team_model->delete_team($team_id);

        	if($result){
        		$this->api_response_arry["response_code"] = rest_controller::HTTP_OK;
	        	$this->api_response_arry["message"] = "Team/player delete successfully";
	        	$this->api_response();
	        }else{
	        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		        $this->api_response_arry["message"] = $this->lang->line("something_went_wrong");
				$this->api_response();
	        }
        }
	}


	/**
     * Used for get league wise team list 
     * @param int $sports_id
     * @return json array
     */ 
	public function get_team_by_league_id_list_post()
	{
		$this->form_validation->set_rules('league_id', 'league id','trim|required');
		if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
      	$response = $this->Team_model->get_team_by_league_id_list($post_data['league_id']); 
		$this->api_response_arry['data'] = $response;
		$this->api_response();
	}


	/**
     * Used for save team details
     * @param array $post_data
     * @return json array
     */
	public function save_league_team_post()
	{
		$this->form_validation->set_rules('league_id', 'League ID', 'trim|required');		
		$this->form_validation->set_rules('team_id', 'Team ID', 'trim|required');		

        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $league_id = trim($post_data['league_id']);     
        $team_id = isset($post_data['team_id']) ? $post_data['team_id'] : "";

        $check_league_exist = $this->Team_model->get_single_row("*",LEAGUE,array("league_id" => $league_id));

        if(empty($check_league_exist)){
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("league_not_exist");
			$this->api_response();
        }


        $check_team_exist = $this->Team_model->get_single_row("*",TEAM,array("team_id" => $team_id));

        if(empty($check_team_exist)){
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("team_not_exist");
			$this->api_response();
        }

        $check_exist = $this->Team_model->get_single_row("team_id,league_id",TEAM_LEAGUE,array("league_id" => $league_id,"team_id"=>$team_id));

        if(!empty($check_exist)){
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("team_id_exist");
			$this->api_response();
        }

      
    	$data_arr = array();
    	$data_arr['league_id'] = $league_id;
    	$data_arr['team_id'] = $team_id;    	
    	$result = $this->Team_model->save_record(TEAM_LEAGUE,$data_arr);
      
        if($result){
        	$this->api_response_arry["message"] = $this->lang->line("team_save_success");
			$this->api_response();
        }else{
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("something_went_wrong");
			$this->api_response();
        }
    }


    /**
     * Used for delete league players 
     * @param array $post_data
     * @return json array
     */
	public function delete_league_player_post()
	{
		$this->form_validation->set_rules('team_id',  'Team ID', 'trim|required');
		$this->form_validation->set_rules('league_id',  'League ID', 'trim|required');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $team_id = trim($post_data['team_id']);
        $league_id = trim($post_data['league_id']);
        $this->load->model('league_model');        
        $league_info = $this->league_model->check_league_exit($league_id); 
        
        if(count($league_info)>0)
		{
		  	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;		
	        $this->api_response_arry["message"] = $this->lang->line("not_able_to_delete_league");
			$this->api_response();
		}	
        $this->load->model('team_model'); 
        $record_info = $this->team_model->get_single_row("*",TEAM_LEAGUE,array("team_id" => $team_id,"league_id"=>$league_id));

        if(empty($record_info)){
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("detail_not_found");
			$this->api_response();
        }else{        	
        	
        	$result = $this->Team_model->delete_league_team($team_id,$league_id);

        	if($result){
        		$this->api_response_arry["response_code"] = rest_controller::HTTP_OK;
	        	$this->api_response_arry["message"] = $this->lang->line("team_delete");
	        	$this->api_response();
	        }else{
	        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		        $this->api_response_arry["message"] = $this->lang->line("something_went_wrong");
				$this->api_response();
	        }
        }
	}
	
}
