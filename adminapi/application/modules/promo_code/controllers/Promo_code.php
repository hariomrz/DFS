<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Promo_code extends MYREST_Controller{
	public $promo_code_types = array();
	public function __construct() {
		parent::__construct();
		$_POST				= $this->input->post();
		$this->load->model('Promocode_model');
		$this->admin_lang = $this->lang->line('promo_code');
		//Do your magic here
		$this->admin_roles_manage($this->admin_id,'marketing');
		$this->promo_code_types = array(
									"0" => array('n' => 'First Deposit', 'v' => '0', 'ad' => 'Apply {promo_code} on your first deposit and get extra benefits of {discount} {cash_type}.', 'pd' => 'Apply {promo_code} on your first deposit and get extra benefits of {discount}% {cash_type} max upto {benefit_cap} {cash_type}.'),
									"1" => array('n' => 'Deposit Range', 'v' => '1', 'ad' => 'Apply {promo_code} on deposit of {desposit_range} range & get extra benefits of {discount} {cash_type}.', 'pd' => 'Apply {promo_code} on deposit of {desposit_range} range & get extra benefits of {discount}% {cash_type} max upto {benefit_cap} {cash_type}.'),
									"2" => array('n' => 'Deposit', 'v' => '2', 'ad' => 'Apply {promo_code} on deposit and get extra benefits of {discount} {cash_type}.', 'pd' => 'Apply {promo_code} on deposit and get extra benefits of {discount}% {cash_type} max upto {benefit_cap} {cash_type}.'),
									"3" => array('n' => 'Contest Join', 'v' => '3', 'ad' => '', 'pd' => '')
								);
		$allow_stock_fantasy =  isset($this->app_config['allow_stock_fantasy'])?$this->app_config['allow_stock_fantasy']['key_value']:0;
		$allow_equity =  isset($this->app_config['allow_equity'])?$this->app_config['allow_equity']['key_value']:0;
		$allow_stock_predict =  isset($this->app_config['allow_stock_predict'])?$this->app_config['allow_stock_predict']['key_value']:0;

		if($allow_stock_fantasy==1 || $allow_equity==1 || $allow_stock_predict==1){
			$this->promo_code_types["5"] =  array('n' => 'Stock Contest Join', 'v' => '5', 'ad' => '', 'pd' => '');
		}
		
		$allow_livefantasy =  isset($this->app_config['allow_livefantasy'])?$this->app_config['allow_livefantasy']['key_value']:0;
		if($allow_livefantasy==1){
			$this->promo_code_types["6"] =  array('n' => 'Live Fantasy Contest Join', 'v' => '6', 'ad' => '', 'pd' => '');
		}
	}

	public function get_master_data_post() {
		
		$this->api_response_arry['data']['promo_code_type']			= $this->promo_code_types;
		$this->api_response_arry['data']['mode']			= array(
						array('n'=>'Public','v'=>'0'),
						array('n'=>'Private','v'=>'1'),	
																);
		$this->api_response();
	}

	/**
	 * [get_promo_code_dates_post description]
	 * Summary :- get promocode start and end date 1 year from today 
	 * @return [type] [description]
	 */
	public function get_promo_code_dates_post() {
		$start_date	= format_date('today','Y-m-d');
		$end_date	= date("Y-m-d", strtotime(date("Y-m-d", strtotime($start_date)) . " + 1 year"));
		$result		= array('start_date' => $start_date, "expiry_date" => $end_date);

		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	}

	public function new_promo_code_post() {
		$post_data = $this->input->post();
		$this->form_validation->set_rules('promo_code_type',"Type",'trim|required');
		$this->form_validation->set_rules('promo_code',$this->admin_lang['promo_code'],'trim|required');
		$this->form_validation->set_rules('cash_type',"Bonus Type", 'trim|required');
		$this->form_validation->set_rules('value_type',"Discount Type", 'trim|required');
		$this->form_validation->set_rules('discount',$this->admin_lang['discount'],'trim|required');
		$this->form_validation->set_rules('start_date',$this->admin_lang['start_date'],'trim|required');
		$this->form_validation->set_rules('expiry_date',$this->admin_lang['expiry_date'],'trim|required');
		$this->form_validation->set_rules('mode',$this->admin_lang['mode'],'trim|in_list[0,1]');
		$this->form_validation->set_rules('description',$this->admin_lang['description'],'trim|min_length[10]|max_length[50]');

		if($post_data['value_type'] == 1){
			$this->form_validation->set_rules('benefit_cap',$this->admin_lang['benefit_cap'],'trim|required');
		}else{
			$post_data['benefit_cap'] = isset($post_data['discount'])?$post_data['discount']:'';
		}

		if($post_data['promo_code_type'] == 1){
			$this->form_validation->set_rules('min_amount',"min amount",'trim|required|is_natural_no_zero');
			$this->form_validation->set_rules('max_amount',"max amount",'trim|required|is_natural_no_zero');
		}else if($post_data['promo_code_type'] == 2){
			$this->form_validation->set_rules('per_user_allowed',"Allowed Per User",'trim|required|is_natural_no_zero');
		}else if($post_data['promo_code_type'] == 3){
			$this->form_validation->set_rules('contest_unique_id',"Contest unique ID",'trim|required|callback_check_contest_validity');
		}

		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
		}

		if(strtotime($post_data['start_date']) > strtotime($post_data['expiry_date'])){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status']			= FALSE;
			$this->api_response_arry['message']			= "Start date should be less then or equal to expiry date.";
			$this->api_response_arry['data']			= array();
			$this->api_response();
		} else if(strtotime($post_data['start_date']) < strtotime(date('Y-m-d H:i:s'))){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status']			= FALSE;
			$this->api_response_arry['message']			= "Start date should not be less than current date time.";
			$this->api_response_arry['data']			= array();
			$this->api_response();
		}

		$promo_detail = $this->Promocode_model->get_single_row('promo_code_id,status', PROMO_CODE, array('promo_code' => $post_data['promo_code']));
		//'type'=>$post_data['promo_code_type'],'cash_type'=>$post_data['cash_type'],"status"=>"1"
		//var_dump($promo_detail); die('stops');
		if(!empty($promo_detail) && $promo_detail!=null) {
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status']			= FALSE;
			$this->api_response_arry['message']			= $this->admin_lang['duplicate_code'];
			$this->api_response_arry['data']			= array();
			$this->api_response();
		}
		
		$promo_code_type = $post_data['promo_code_type'];
		$value_type = $post_data['value_type'];
		$mode = 1;
		$description = '';
		if(in_array($promo_code_type, array(0,1,2))) {
			$mode = isset($post_data['mode']) ? $post_data['mode'] : 1;
			$description = isset($post_data['description']) ? trim($post_data['description']) : '';
			if(empty($description)) {
				if($value_type == 1) {
					$description = $this->promo_code_types[$promo_code_type]['pd'];
				} else {
					$description = $this->promo_code_types[$promo_code_type]['ad'];
				}				
			}
		}
		$data_array = array(
				"type"				=> $promo_code_type,
				"mode"				=> $mode,
				"description"		=> $description,
				"cash_type"			=> $post_data['cash_type'],
				"value_type"		=> $value_type,
				"promo_code"		=> strtoupper($post_data['promo_code']),
				"discount"			=> $post_data['discount'],
				"benefit_cap"		=> $post_data['benefit_cap'],
				"start_date"		=> date('Y-m-d H:i:s', strtotime($post_data['start_date'])),
				"expiry_date"		=> date('Y-m-d H:i:s', strtotime($post_data['expiry_date'])),
				"min_amount"		=> isset($post_data['min_amount']) ? $post_data['min_amount'] : NULL,
				"max_amount"		=> isset($post_data['max_amount']) ? $post_data['max_amount'] : NULL,
				"per_user_allowed"	=> isset($post_data['per_user_allowed']) ? $post_data['per_user_allowed'] : 1,
				"status"			=> '1',
				"added_date"		=> format_date(),
				"max_usage_limit"	=> isset($post_data['max_usage_limit']) ? $post_data['max_usage_limit'] : 0,
			);
		if($post_data['promo_code_type'] == 3)
		{
			$data_array['contest_unique_id'] = $post_data['contest_unique_id'];
		}
		$result = $this->Promocode_model->new_promo_code($data_array);
		if($result)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['status']			= TRUE;
			$this->api_response_arry['message']			= $this->admin_lang['create_promo_code'];
			$this->api_response_arry['data']			= array();
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status']			= FALSE;
			$this->api_response_arry['message']			= $this->admin_lang['promo_insertion_failed'];
			$this->api_response_arry['data']			= array();
			$this->api_response();
		}
	}

	public function get_promo_codes_post()
	{
		$result = $this->Promocode_model->get_promo_codes();
		
		$promocode = $this->Promocode_model->get_single_row ('category_id',CD_EMAIL_CATEGORY, $where = ["category_name"=>'Promotion for Promocode']);
		$result['category_id'] = $promocode['category_id'] ? $promocode['category_id'] : '';
		foreach($result['result'] as $key=>$promo_code)
		{
			switch($promo_code['type'])
			{
				case 3:
					$promo_code['template_name'] = "contest_join_promocode";
				break;
				case 2:
					$promo_code['template_name'] = "deposit_promocode";
				break;
				case 1:
					$promo_code['template_name'] = "deposit_range_promocode";
				break;
				case 0:
					$promo_code['template_name'] = "first_deposit_promocode";
				break;
			}

			$template_id = $this->Promocode_model->get_single_row ('cd_email_template_id,template_name',CD_EMAIL_TEMPLATE, $where = ["template_name"=>$promo_code['template_name']]);
			$result['result'][$key]['category_template_id'] = $template_id['cd_email_template_id'] ? $template_id['cd_email_template_id'] : '';
			$result['result'][$key]['template_name'] = $template_id['template_name'] ? $template_id['template_name'] : '';
		}
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= '';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	}

	public function get_promo_codes_get()
	{
		$_POST =  $this->input->get();
		$result = $this->Promocode_model->get_promo_codes();
		//echo "<pre>";print_r($result);die;
		if(!empty($result['result'])){
			$result =$result['result'];
			$final_list = array();
			foreach($result as $row){
				$temp_arr = array();
				$temp_arr['promo_code'] = $row['promo_code'];
				if($row['type'] == "0"){
					$temp_arr['type'] = "First Deposit";
				}else if($row['type'] == "1"){
					$temp_arr['type'] = "Deposit Range";
				}else if($row['type'] == "2"){
					$temp_arr['type'] = "Promo Code";
				}else if($row['type'] == "3"){
					$temp_arr['type'] = "Contest Join";
				}
				if($row['cash_type'] == "0"){
					$temp_arr['bonus_type'] = "Bonus";
				}else if($row['cash_type'] == "1"){
					$temp_arr['bonus_type'] = "Real";
				}
				if($row['value_type'] == "0"){
					$temp_arr['discount_type'] = "Amount";
				}else{
					$temp_arr['discount_type'] = "Percentage";
				}
				$temp_arr['discount'] = $row['discount'];
				$temp_arr['benefit_cap'] = $row['benefit_cap'];
				$temp_arr['start_date'] = $row['start_date'];
				$temp_arr['expiry_date'] = $row['expiry_date'];
				$temp_arr['allowed_per_user'] = $row['per_user_allowed'];
				$temp_arr['min_amount'] = $row['min_amount'];
				$temp_arr['max_amount'] = $row['max_amount'];
				$temp_arr['status'] = $row['status'];
				$temp_arr['added_date'] = $row['added_date'];
				$temp_arr['amount_received'] = $row['amount_received'];
				$temp_arr['mode'] = $row['mode'];
				$temp_arr['description'] = $row['description'];


				$final_list[] = $temp_arr;
			}
			$header = array_keys($final_list[0]);
			$camelCaseHeader = array_map("camelCaseString", $header);
			$final_list = array_merge(array($camelCaseHeader),$final_list);
			$this->load->helper('csv');
			array_to_csv($final_list,'Promocode.csv');
		}

		$this->load->helper('download');
		$data = $this->dbutil->csv_from_result($query);
		$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "From Date $from_date\nTo Date $to_date\n\n" . html_entity_decode($data);
		$name = 'Promocode.csv';
		force_download($name, $data);
	}

	public function change_promo_status_post()
	{
		$this->form_validation->set_rules('promo_code_id', 'Promo Code ID', 'trim|required');
		$this->form_validation->set_rules('status', 'status', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$promo_code_id = $this->input->post('promo_code_id');
		$promo_detail = $this->Promocode_model->get_single_row('promo_code_id,status', PROMO_CODE, array('promo_code_id'=>$promo_code_id));

		if(!$promo_detail)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status']			= FALSE;
			$this->api_response_arry['message']			= 'Invalid promo code detail';
			$this->api_response_arry['data']			= array();
			$this->api_response();
		}

		$status = $promo_detail['status'];
		$promo_update = array(
			'status' => ($status=='0')?'1':'0'
		);

		$this->db->where("promo_code_id",$promo_code_id)
				->update(PROMO_CODE, $promo_update);

		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'Promo code status updated successfully';
		$this->api_response_arry['data']			= $promo_update;
		$this->api_response();
	}

	public function get_promo_code_detail_post()
	{
		$result = $this->Promocode_model->get_promo_code_detail();
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= '';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	}

	public function delete_promo_code_post()
	{
		$this->form_validation->set_rules('promo_code_id', 'Promo Code ID', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$promo_code_id = $this->input->post('promo_code_id');
		$promo_detail = $this->Promocode_model->get_single_row('promo_code_id,status', PROMO_CODE, array('promo_code_id'=>$promo_code_id));

		if(!$promo_detail)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status']			= FALSE;
			$this->api_response_arry['message']			= 'Invalid promo code detail';
			$this->api_response_arry['data']			= array();
			$this->api_response();
		}

		$promo_earning = $this->Promocode_model->get_single_row('count(promo_code_earning_id) as total_used', PROMO_CODE_EARNING, array('promo_code_id'=>$promo_code_id));
		if(!$promo_earning || $promo_earning['total_used'] > 0)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status']			= FALSE;
			$this->api_response_arry['message']			= "Someone already used this code, so you can't delete it.";
			$this->api_response_arry['data']			= array();
			$this->api_response();
		}

		$this->db->where("promo_code_id",$promo_code_id)
				->delete(PROMO_CODE);

		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'Promo code deleted successfully';
		$this->api_response_arry['data']			= array();
		$this->api_response();
	}

	/**
	 * Used to update promo code end date
	 */
	public function update_end_date_post() {
		$this->form_validation->set_rules('promo_code_id', 'Promo Code ID', 'trim|required');
		$this->form_validation->set_rules('expiry_date',$this->admin_lang['expiry_date'],'trim|required|callback_validate_date[Y-m-d]');

		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
				
		$promo_code_id = $post_data['promo_code_id'];
		$promo_detail = $this->Promocode_model->get_single_row('promo_code_id', PROMO_CODE, array('promo_code_id'=>$promo_code_id));

		if(!$promo_detail) {
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status']			= FALSE;
			$this->api_response_arry['message']			= 'Invalid promo code detail';
			$this->api_response_arry['data']			= array();
			$this->api_response();
		}

		$promo_update = array(
			'status' => '1',
			"expiry_date" => $post_data['expiry_date'],
		);

		$this->db->where("promo_code_id",$promo_code_id)
				->update(PROMO_CODE, $promo_update);

		$this->api_response_arry['message']			= 'Promo code end date updated successfully.';
		$this->api_response();
	}

	/**
	 * Used to get promo code analytics
	 */
	public function get_promo_code_analytics_post() {
		$this->form_validation->set_rules('promo_code',$this->admin_lang['promo_code'],'trim|required');
		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
		}
		$result = $this->Promocode_model->get_promo_code_analytics();
		if(!empty($result) && isset($result['promo_code_id'])) {
			if(in_array($result['type'], array(0,1,2))) {
				$result['type_value'] = $this->promo_code_types[$result['type']]['n'];

				$graph_data = $this->Promocode_model->get_promo_code_usage_graph($result['promo_code_id']);
				$categories = array();
				$rcd = array();
				$ad = array();
				if(!empty($graph_data)) {
					foreach($graph_data as $key => $value) {
						$categories[] = $value['added_date'];
						$ad[] = (float)$value['d_amt'];
						$rcd[] = (float)$value['r_amt']; 
						
					}
				}			

				$result['graph_data']['categories'] = $categories;
				$result['graph_data']['rcd'] 	= $rcd;
				$result['graph_data']['ad'] 	= $ad;
				$this->api_response_arry['data']	= $result;
			}
		}
		
		$this->api_response();
	}

	function validate_date($date, $format = 'Y-m-d H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        if($d && $d->format($format) == $date) {
          	return true;  
        } else {
			$this->form_validation->set_message('validate_date', $this->admin_lang['valid_value']);
        	return false;
        }
	}
	
	/*
     * check_contest_validity to validate social id
     * @param
     * @return json array
     */
    public function check_contest_validity()
    {
        $post_data = $this->post();
        if (!isset($post_data['contest_unique_id']))
        {
        	$this->form_validation->set_message('check_contest_validity', "The Contest unique ID field is required.");
        	return FALSE;
        }

        if ($post_data['contest_unique_id'] == "0")
        {
            return TRUE;
        }
        else if (!empty($post_data['contest_unique_id']) && $post_data['contest_unique_id'] != "0")
        {
        	$result = $this->Promocode_model->check_contest_validity($post_data['contest_unique_id']);
        	if ($result)
        	{
            	return TRUE;
        	}
        	else
        	{
        		$this->form_validation->set_message('check_contest_validity', "Please enter a valid contest ID");
        		return FALSE;
        	}
        }
        $this->form_validation->set_message('check_contest_validity', "Please enter a valid contest ID");
        return FALSE;
    }

}

/* End of file Promo_code.php */
/* Location: ./application/controllers/Promo_code.php */