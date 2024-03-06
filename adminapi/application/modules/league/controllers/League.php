<?php defined('BASEPATH') OR exit('No direct script access allowed');

class League extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('League_model');
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
		$this->load->model('league/League_model');
        $position_list = $this->League_model->get_all_position($sports_id);
		$this->api_response_arry['data'] = $position_list;
		$this->api_response();
	}

    /**
	* Used for get sports wise league list
	* @param int $sports_id
	* @return array
	*/    
	public function get_sport_leagues_post()
	{	
		$this->form_validation->set_rules('sports_id', 'sports id','trim|required');
		if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
		
		$post_data = $this->input->post();
		$result = $this->League_model->get_sport_league_list($post_data);
		
		$this->api_response_arry['data'] = $result;
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
		$result = $this->League_model->get_league_detail($league_id);
		$this->api_response_arry['data'] = $result;
		$this->api_response();
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

        //  $this->League_model->update_outdated_featured_status(); 
		$result = $this->League_model->update_leagues_featured_status($post_data);
		if($result){
			//check for update sports hub featured leagues
			$league_info = $this->League_model->get_league_detail($post_data['league_id']);
			if(!empty($league_info)){
				$league_info['is_featured'] = $is_featured;
				$this->League_model->save_featured_league($league_info);
			}

			$this->delete_cache_data('lobby_fixture_list_'.$post_data['sports_id']);

			//delete lobby upcoming section file
			$input_arr = array();
			$input_arr['lang_file'] = '0';
			$input_arr['file_name'] = 'lobby_fixture_list_';
			$input_arr['sports_ids'] = array($post_data['sports_id']);
			$this->delete_cache_and_bucket_cache($input_arr);

			$this->push_s3_data_in_queue('app_master_data',array(),"delete");
			
			// $this->flush_cache_data();

			$this->api_response_arry['message'] = "Data updated successfully.";
	        $this->api_response();
		}else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = "Something went wrong while update status. please try again.";
            $this->api_response();
		}
	}
	
	public function sports_config_get(){
		$key_pass = md5("VFW30AdmIn001");
		$act_key = isset($_REQUEST['key']) ? $_REQUEST['key'] : "";
		$Sessionkey = isset($_REQUEST['Sessionkey']) ? $_REQUEST['Sessionkey'] : "";
		$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "";
		if($act_key != $key_pass || $Sessionkey == ""){
			echo "<h3 style='color:#ff0000;text-align:center;font-size:28px; padding:20px'>Unauthorized Access</h3>";die;
		}
        $action = WEBSITE_URL."adminapi/save_sports_config?key=".$act_key."&Sessionkey=".$Sessionkey;
		$sports_list = $this->League_model->get_config_sports_list();
		if(empty($sports_list)){
			echo "<h3 style='color:#ff0000;text-align:center;font-size:28px; padding:20px'>Sports not enabled.</h3>";die;
		}
		$data = array("key"=>$act_key,"Sessionkey"=>$Sessionkey,"status"=>$status,"action"=>$action);
		$data['sports_id'] = isset($_REQUEST['sports_id']) ? $_REQUEST['sports_id'] : $sports_list['0']['sports_id'];
		$data['position_list'] = $this->League_model->get_sports_position_config($data['sports_id']);
		$sports_list = array_column($sports_list,NULL,"sports_id");
		$data['sports_list'] = $sports_list;
		$data['max_player_per_team'] = $sports_list[$data['sports_id']]['max_player_per_team'];
		//echo "<pre>";print_r($data);die;
        $this->load->view('sports_config', $data);
    }

    public function save_sports_config_post(){
		$key_pass = md5("VFW30AdmIn001");
		$act_key = isset($_REQUEST['key']) ? $_REQUEST['key'] : "";
		$Sessionkey = isset($_REQUEST['Sessionkey']) ? $_REQUEST['Sessionkey'] : "";
		if($act_key != $key_pass || $Sessionkey == ""){
			echo "<h3 style='color:#ff0000;text-align:center;font-size:28px; padding:20px'>Unauthorized Access</h3>";die;
		}
		$action = WEBSITE_URL."adminapi/sports_config";
		$post_data = $_POST;
		//echo "<pre>";print_r($config_data);die;
		if(!empty($post_data) && isset($post_data['sports_id'])){
			$this->League_model->save_sports_config($post_data);

			$this->flush_cache_data();

			$redirect = $action."?status=1&key=".$act_key."&Sessionkey=".$Sessionkey."&sports_id=".$post_data['sports_id'];
			header("Location:".$redirect);
		}else{
			//die("errrr");
			$redirect = $action."?status=0&key=".$act_key."&Sessionkey=".$Sessionkey;
			header("Location:".$redirect);
		}
    }


	/**
	* Function used for update league featured status	
	* @param int $league_id
	* @param int $auto_published
	* @return array
	*/ 
	public function update_auto_publish_status_post()
	{		
		$this->form_validation->set_rules('league_id', 'league id','trim|required');
		$this->form_validation->set_rules('auto_published', 'auto published','trim|required');
		if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }		
		$post_data = $this->input->post();	  
		$result = $this->League_model->update_auto_publish_status($post_data);
		if($result){	
			$this->api_response_arry['message'] = "Auto publish status update successfully.";
	        $this->api_response();
		}else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = "Something went wrong while update status. please try again.";
            $this->api_response();
		}
	}

}
