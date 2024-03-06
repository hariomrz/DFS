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
		$result = $this->League_model->get_all_table_data("sports_id,name,is_default,status,created_date",SPORTS,['status'=>1]);
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	/**
     * Used for save sports details
     * @param array $post_data
     * @return json array
     */
	public function save_sports_post()
	{
		$this->form_validation->set_rules('name', 'sports name', 'trim|required|max_length[100]');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $name = trim($post_data['name']);
        $sports_id = isset($post_data['sports_id']) ? $post_data['sports_id'] : "";
        $check_exist = $this->League_model->get_single_row("sports_id",SPORTS,array("LOWER(name)" => strtolower($name),"status != "=>"2"));
        if(!empty($check_exist) && $check_exist['sports_id'] != $sports_id){
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("sports_name_exist");
			$this->api_response();
        }else{
        	if($sports_id != ""){
        		$data_arr = array();
	        	$data_arr['name'] = $name;
	        	$data_arr['modified_date'] = $current_date;
	        	$result = $this->League_model->update(SPORTS,$data_arr,array("sports_id"=>$sports_id,"is_default"=>"0"));
	        }else{
	        	$data_arr = array();
	        	$data_arr['name'] = $name;
	        	$data_arr['created_date'] = $current_date;
	        	$data_arr['modified_date'] = $current_date;
	        	$result = $this->League_model->save_record(SPORTS,$data_arr);
	        }

	        if($result){
	        	$this->api_response_arry["message"] = $this->lang->line("sports_save_success");
				$this->api_response();
	        }else{
	        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		        $this->api_response_arry["message"] = $this->lang->line("something_went_wrong");
				$this->api_response();
	        }
        }
	}

	/**
     * Used for delete sports detail
     * @param array $post_data
     * @return json array
     */
	public function delete_sports_post()
	{
		$this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = trim($post_data['sports_id']);
        $sports_info = $this->League_model->check_sports_exit($sports_id); 
        
        if(count($sports_info)>0)
		{
		  	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;		
	        $this->api_response_arry["message"] = 'You are not able to delete Sports.';
			$this->api_response();
		}
        $record_info = $this->League_model->get_single_row("*",SPORTS,array("sports_id" => $sports_id));
        if(empty($record_info)){
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("detail_not_found");
			$this->api_response();
        }else if(!empty($record_info) && $record_info['is_default'] == "1"){
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("default_sports_delete_error");
			$this->api_response();
        }else{
        	$data_arr = array();
        	$data_arr['status'] = "2";
        	$data_arr['modified_date'] = format_date();
        	$result = $this->League_model->update(SPORTS,$data_arr,array("sports_id"=>$sports_id,"is_default"=>"0"));
        	if($result){
	        	$this->api_response_arry["message"] = $this->lang->line("sports_delete_success");
				$this->api_response();
	        }else{
	        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		        $this->api_response_arry["message"] = $this->lang->line("something_went_wrong");
				$this->api_response();
	        }
        }
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
        $sports_id = trim($post_data['sports_id']);
		// $result = $this->League_model->get_all_table_data("league_id,sports_id,league_uid,league_abbr,IFNULL(display_name,league_name) as league_name",LEAGUE, array("status"=>"1","sports_id"=>$sports_id));
		$result = $this->League_model->get_league_list($post_data);
		$row['player_list'] = array();
		foreach ($result as $key => &$value) {		
			$this->load->model('team_model'); 
			$value['player_list'] = $this->team_model->get_team_by_league_id_list($value['league_id']);			
		} 
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	/**
     * Used for save league details
     * @param array $post_data
     * @return json array
     */
	public function save_league_post()
	{
		$this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
		$this->form_validation->set_rules('name', 'league name', 'trim|required|max_length[100]');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $sports_id = trim($post_data['sports_id']);
        $name = trim($post_data['name']);
        $league_abbr = strtoupper(str_replace(" ","_",$name));
        $league_id = isset($post_data['league_id']) ? $post_data['league_id'] : "";
        $check_exist = $this->League_model->get_single_row("league_id",LEAGUE,array("LOWER(league_name)" => strtolower($name),"sports_id"=>$sports_id,"status != "=>"2"));
        if(!empty($check_exist) && $check_exist['league_id'] != $league_id){
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("league_name_exist");
			$this->api_response();
        }else{
        	if($league_id != ""){
        		$data_arr = array();
	        	$data_arr['league_abbr'] = $league_abbr;
	        	$data_arr['league_name'] = $name;
	        	$data_arr['display_name'] = $name;
	        	$data_arr['modified_date'] = $current_date;
	        	$result = $this->League_model->update(LEAGUE,$data_arr,array("league_id"=>$league_id,"sports_id"=>$sports_id));
	        }else{
	        	$league_uid = generate_entity_uid($name);
	        	$data_arr = array();
	        	$data_arr['sports_id'] = $sports_id;
	        	$data_arr['league_uid'] = $league_uid;
	        	$data_arr['league_abbr'] = $league_abbr;
	        	$data_arr['league_name'] = $name;
	        	$data_arr['display_name'] = $name;
	        	$data_arr['created_date'] = $current_date;
	        	$data_arr['modified_date'] = $current_date;
	        	$result = $this->League_model->save_record(LEAGUE,$data_arr);
	        }
	        if($result){
	        	$this->api_response_arry["message"] = $this->lang->line("league_save_success");
				$this->api_response();
	        }else{
	        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		        $this->api_response_arry["message"] = $this->lang->line("something_went_wrong");
				$this->api_response();
	        }
        }
	}

	/**
     * Used for delete league detail
     * @param array $post_data
     * @return json array
     */
	public function delete_league_post()
	{
		$this->form_validation->set_rules('league_id', 'league id', 'trim|required');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $league_id = trim($post_data['league_id']);       

        $league_info = $this->League_model->check_league_exit($league_id); 
        //echo $this->db->last_query();die;
        if(count($league_info)> 0)
		{
		  	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;		
	        $this->api_response_arry["message"] = $this->lang->line("not_able_to_delete_league");
			$this->api_response();
		}

        $record_info = $this->League_model->get_single_row("*",LEAGUE,array("league_id" => $league_id));
        if(empty($record_info)){
        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	        $this->api_response_arry["message"] = $this->lang->line("detail_not_found");
			$this->api_response();
        }else{
        	$data_arr = array();
        	$data_arr['status'] = "2";
        	$data_arr['modified_date'] = format_date();
        	$result = $this->League_model->update(LEAGUE,$data_arr,array("league_id"=>$league_id));
        	if($result){
	        	$this->api_response_arry["message"] = $this->lang->line("league_delete_success");
				$this->api_response();
	        }else{
	        	$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		        $this->api_response_arry["message"] = $this->lang->line("something_went_wrong");
				$this->api_response();
	        }
        }
	}

	/**
     * Used for Active inactive sports 
     * @param array $post_data
     * @return string message
     */
	public function update_sports_status_post()
	{
		$this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
		$this->form_validation->set_rules('status', 'Status', 'trim|required');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

        $sports_id = $this->input->post('sports_id');
        $status = $this->input->post('status');
        $this->League_model->update(SPORTS,['status'=>$status],['sports_id'=>$sports_id]);

		$this->api_response_arry["message"] = 'Sports Status has been updated successfully';
		$this->api_response();
	}
}