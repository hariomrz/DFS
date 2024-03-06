<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsnew extends MYREST_Controller{

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
		$this->load->model('Whatsnew_model');
		$this->admin_roles_manage($this->admin_id,'settings');
	}

	/**
     * Used for get whats new added data
     * @param array $post_data
     * @return json array
     */
	public function get_record_list_post()
	{
		$post_data = $this->post();
		$result = $this->Whatsnew_model->get_record_list($post_data);
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	/**
     * Used for save whats new record
     * @param array $post_data
     * @return json array
     */
    public function save_record_post()
    {
        $this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[4]|max_length[30]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|min_length[15]|max_length[150]');
        $this->form_validation->set_rules('image', 'Image', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        //echo "<pre>";print_r($post_data);die;
        $data_arr = array();
        $data_arr['name'] = $post_data['name'];
        $data_arr['description'] = $post_data['description'];
        $data_arr['image'] = $post_data['image'];
        $data_arr['status'] = 1;
        $data_arr['added_date'] = $current_date;
        $data_arr['modified_date'] = $current_date;
        //echo "<pre>";print_r($post_data);die;
        $record = $this->Whatsnew_model->count_record();

        if ($record['total_record'] >= 20) {

            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "You can add max 20 page.";
            $this->api_response();
           
        }
        
        $result = $this->Whatsnew_model->save_record($data_arr);
        if(!$result){
        	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("whatsnew_save_error");
            $this->api_response();
        }

        //for delete s3 bucket file
		$this->deleteS3BucketFile("app_master_data.json");
		$this->delete_cache_data('whats_new_list');

        $this->api_response_arry['message'] = $this->lang->line("whatsnew_save_success");
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }

	/**
     * Used for update whats new record
     * @param array $post_data
     * @return json array
     */
    public function update_record_post()
    {
        $this->form_validation->set_rules('id', 'id', 'trim|required');
        $this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[4]|max_length[30]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|min_length[15]|max_length[150]');
        $this->form_validation->set_rules('image', 'Image', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        //echo "<pre>";print_r($post_data);die;
        $data_arr = array();
        $data_arr['name'] = $post_data['name'];
        $data_arr['description'] = $post_data['description'];
        $data_arr['image'] = $post_data['image'];
        $data_arr['modified_date'] = $current_date;
        $result = $this->Whatsnew_model->save_record($data_arr,$post_data['id']);
        if(!$result){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("whatsnew_save_error");
            $this->api_response();
        }

        //for delete s3 bucket file
		$this->deleteS3BucketFile("app_master_data.json");
		$this->delete_cache_data('whats_new_list');

        $this->api_response_arry['message'] = $this->lang->line("whatsnew_save_success");
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }

	/**
     * Used for update whats new status
     * @param array $post_data
     * @return json array
     */
    public function update_status_post()
    {
        $this->form_validation->set_rules('id', 'id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $record_info = $this->Whatsnew_model->get_single_row('status',WHATS_NEW,array('id' => trim($post_data['id'])));
        if(empty($record_info)){
            $this->api_response_arry['message'] = "Whats new record details not found.";
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
        $result = $this->Whatsnew_model->save_record($data_arr,$post_data['id']);
        if(!$result){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("whatsnew_status_error");
            $this->api_response();
        }

        //for delete s3 bucket file
		$this->deleteS3BucketFile("app_master_data.json");
		$this->delete_cache_data('whats_new_list');

        $this->api_response_arry['message'] = $this->lang->line("whatsnew_status_success");
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }

	/**
     * Used for delete whats new record
     * @param array $post_data
     * @return json array
     */
    public function delete_record_post()
    {
        $this->form_validation->set_rules('id', 'id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $record_info = $this->Whatsnew_model->get_single_row('status',WHATS_NEW,array('id' => trim($post_data['id'])));
        if(empty($record_info)){
            $this->api_response_arry['message'] = "Whats new record details not found.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $result = $this->Whatsnew_model->delete_record($post_data['id']);
        if(!$result){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line("whatsnew_delete_error");
            $this->api_response();
        }

        //for delete s3 bucket file
		$this->deleteS3BucketFile("app_master_data.json");
		$this->delete_cache_data('whats_new_list');

        $this->api_response_arry['message'] = $this->lang->line("whatsnew_delete_success");
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }
}