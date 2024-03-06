<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Deals extends MYREST_Controller{

	public function __construct()
	{
		parent::__construct();
		
		$_POST = $this->input->post();
		$this->load->model('Deal_model');
		$this->admin_roles_manage($this->admin_id,'deals');
		//Do your magic here
		$this->admin_roles_manage($this->admin_id,'deals');
	}

	public function get_deals_post()
	{
		$deal = $this->Deal_model->get_single_row ('category_id',CD_EMAIL_CATEGORY, $where = ["category_name"=>'Promotion for Deal']);
		$template_id = $this->Deal_model->get_single_row ('cd_email_template_id',CD_EMAIL_TEMPLATE, $where = ["template_name"=>'deal_template',"notification_type"=>"434"]);
		$data_post = $this->post();
		$data = $this->Deal_model->get_deal_list($data_post);
		$this->api_response_arry['data']['result']	= $data;
		$this->api_response_arry['data']['total']	= $this->Deal_model->get_deal_list($data_post,TRUE);
		$this->api_response_arry['data']['category_id']	= $deal['category_id'] ? $deal['category_id'] : '';
		$this->api_response_arry['data']['category_template_id']	= $template_id['cd_email_template_id'] ? $template_id['cd_email_template_id'] : '';
		$this->api_response();
	}

	function validate_deal_amount($amount)
	{
		if ($amount <= 5) { 
			$this->form_validation->set_message('validate_deal_amount', $this->lang->line('deal_amount_msg')); 
			return false;
		}
		else 
		{ 
			return true; 
		} 
	}

	public function create_deal_post()
	{
		
	  	$data_post = $this->post();
		$this->form_validation->set_rules('amount', 'Amount', 'trim|required|callback_validate_deal_amount');
		$this->form_validation->set_rules('real', 'Real', 'trim|required');
		$this->form_validation->set_rules('bonus', 'Bonus', 'trim|required');
		if($this->app_config['allow_coin']['key_value']==1){
		$this->form_validation->set_rules('coins', 'Coins', 'trim|required');
		}
		
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$deal_available = $this->Deal_model->get_deal_availiblity();
		if($deal_available==FALSE){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']	= 'You should inactive/delete atleast one deal to create new.';
		}
		else{
			$result = $this->Deal_model->create_deal($data_post);

			if ($result)
			{
				$this->delete_cache_data('deal_list');
				$this->api_response_arry['message']			= 'Deal added';
			}
			else
			{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']			= 'Error';
			}
		}
				$this->api_response();
	}



	

	public function update_deal_status_post()
	{		
		$this->form_validation->set_rules('status', 'status', 'required');
		$this->form_validation->set_rules('deal_unique_id', 'deal id', 'trim|required');
		
		if(!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$data_post = $this->post();
		$dataArr = array("status" => $this->input->post('status'));
		$id = $this->input->post('deal_unique_id');
		$deal_available = $this->Deal_model->get_deal_availiblity();
		if($deal_available==FALSE && $this->input->post('status')==1){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']	= 'Only 7 deals can active at a time.';
		}
		else{
			$result = $this->Deal_model->update_deal_by_id($id, $dataArr);
			$this->delete_cache_data('deal_list');
			$this->api_response_arry['data']	= $result;
			$this->api_response_arry['message']	= 'Deal status Updated.';
		}
		$this->api_response();
	}

	public function delete_deal_post()
	{
		$this->form_validation->set_rules('deal_unique_id', 'banner_id', 'trim|required');
		if(!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}


		
		$data_post = $this->post();
		$deal_used = $this->Deal_model->check_deal_used($data_post['deal_unique_id']);
		if($deal_used)
		{
			$this->api_response_arry['response_code']	= 500;
			$this->api_response_arry['global_error']    = 'Deal already used';
			$this->api_response();
		}
		$dataArr = array("is_deleted" => "1");
		$id = $this->input->post('deal_unique_id');
		$data = $this->Deal_model->update_deal_by_id($id, $dataArr);
		$this->delete_cache_data('deal_list');

		$this->api_response_arry['data']	= $data;
		$this->api_response_arry['message']	= "Deal Deleted Successfully.";
		$this->api_response();
	}


	public function get_deals_detail_post()
	{

	   $this->form_validation->set_rules('deal_unique_id', 'Deal Unique ID', 'trim|required');
		
		if(!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->post();

		$deal = $this->Deal_model->get_deals_detail($post_data);

		$data['deal_detail'] = $deal;

		if (!empty($deal)) {

			$user_deal_data = $this->Deal_model->get_user_by_deal_id($deal['deal_id']);
		}

		$data['user_deal_data'] = $user_deal_data['result'];
		$data['total_count'] = $user_deal_data['total'];

		$this->api_response_arry['data']	= $data;
		$this->api_response();
	}
	
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */