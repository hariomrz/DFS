<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Scratchwin extends MYREST_Controller {

    public function __construct()
    {
		parent::__construct();
		$allow_scratchwin = isset($this->app_config['allow_scratchwin']) ? $this->app_config['allow_scratchwin']['key_value'] : 0;
		if(!$allow_scratchwin){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Scratch & win module is disabled";
			$this->api_response();
        }
		$this->admin_lang = $this->lang->line('scratchwin');
        $this->load->model('Scratchwin_model');
	}
	
	public function change_scratch_win_status_post(){
		$this->form_validation->set_rules('status', 'Status', 'trim|required');
		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$this->load->model('contest/Contest_model');
		$result = $this->Contest_model->change_scratch_win_status($post_data);
		if($result){
			$this->api_response_arry['message'] = $this->lang->line('update_status_success');
			$this->api_response();
		}else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line('no_change');
			$this->api_response();
		}
	}

	public function get_scratch_card_list_post(){

		$result = $this->Scratchwin_model->get_scratch_card_list();
		
		$prize_type = array();
		$prize_type[] = array("label"=>"Bonus Cash","value"=>"0");
		$prize_type[] = array("label"=>"Real Cash","value"=>"1");
		$allow_coin_system =  isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
		if($allow_coin_system == 1){
			$prize_type[] = array("label"=>"Coins","value"=>"2");
		}

		if($result){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['data'] = ['result'=>$result['result'],'prize_type'=>$prize_type,'total'=>$result['total']];
			$this->api_response_arry['message'] = $this->lang->line('get_record');
			$this->api_response();

		}else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line('not_found');
			$this->api_response();
		}
 	}

  	public function add_scratch_card_post(){
		$this->form_validation->set_rules('amount', 'Amount', 'trim|required');
		$this->form_validation->set_rules('result_text', 'Result Text', 'trim|required');
		$this->form_validation->set_rules('status', 'Status', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
		}
		
		$post_data = $this->input->post();
		$post_data['created_date'] = format_date('today');
		$result = $this->Scratchwin_model->add_scratch_card($post_data);
		if($result){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['data'] = $result;
			$this->api_response_arry['message'] = $this->lang->line('success_add_scratch');
			$this->api_response();
		}
		else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line('error_in_add_scratch');
			$this->api_response();
		}
  	}

  	public function update_scratch_card_post(){
		$this->form_validation->set_rules('scratch_card_id', 'Scratch Card ID', 'trim|required');
		$this->form_validation->set_rules('amount', 'Amount', 'trim|required');
		$this->form_validation->set_rules('result_text', 'Result Text', 'trim|required');
		$this->form_validation->set_rules('status', 'Status', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
		}
		$result = $this->Scratchwin_model->update_scratch_card();
		if($result){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['data'] = $result;
			$this->api_response_arry['message'] = $this->lang->line('success_update_scratch');
			$this->api_response();
		}
		else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line('no_change');
			$this->api_response();
		}

  	}

  	public function delete_scratch_card_post(){
		$this->form_validation->set_rules('scratch_card_id', 'Scratch Card ID', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
		}
		$result = $this->Scratchwin_model->delete_scratch_card();
		if($result){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['data'] = $result;
			$this->api_response_arry['message'] = $this->lang->line('success_delete_scratch');
			$this->api_response();
		}
		else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = $this->lang->line('error_in_delete_scratch');
			$this->api_response();
		}
  	}
}

?>