<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Coins_package extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Coins_package_model');
		$_POST = $this->input->post();
	}

	/**
	 * [package_list_post description]
	 * Summary :- get Package list
	 * @return [type] [description]
	 */
	public function package_list_post()
	{
		$result = $this->Coins_package_model->get_all_package();
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;
		$this->api_response();
	}

	/**
	 * [add_roles_post description]
	 * Summary :- Use for insert new admin
	 * @param   : firstname,lastname,email
	 * @return  [type]
	 */
	public function add_package_post()
	{
	
		$this->form_validation->set_rules('package_name', 'Package Name', 'trim|required|is_unique['.$this->db->dbprefix(COIN_PACKAGE).'.package_name]',array(
			'required'      => 'You have not provided %s.',
			'is_unique'     => 'This package name is already as Package List.'
		));
	
		$this->form_validation->set_rules('amount', 'Amount', 'trim|required');
		$this->form_validation->set_rules('coins', 'Coins', 'trim|required|min_length[1]|max_length[2000]');
	
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$data = $this->input->post(); 

		
		$check_package =  $this->Coins_package_model->get_single_row(
			 "amount",
			 COIN_PACKAGE,
			 array("status"=>'1',"amount"=> $data['amount'])
		);

		if(!empty($check_package)) {
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = "Amount ".$data['amount'].' Package already active';
            $this->api_response(); 
		}
		
		$data['status'] = '1';

		$insert_id = $this->Coins_package_model->add_package($data);
		if($insert_id)
		{	
			$this->api_response_arry['service_name']	= 'add_package';
			$this->api_response_arry['message']			= 'Package added successfully!';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']			= array('id'=>$insert_id);
			$this->api_response();	
		}

		$this->api_response_arry['service_name']	= 'add_package';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		$this->api_response_arry['global_error']	= "Package not added";
		$this->api_response();	

	}


	/**
	 * [package_update description]
	 * Summary :- Use for update admin info
	 * @param   : admin_id,firstname,lastname
	 * @return  [type]
	 */
	public function package_update_post()
	{
		$data = array();
		$this->form_validation->set_rules('package_id', 'Package ID', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$data = $this->input->post(); 
		$check_package =  $this->Coins_package_model->get_single_row(
			 "amount",
			 COIN_PACKAGE,
			 array("coin_package_id"=> $data['package_id'])
		);
		
		if(empty($check_package)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = 'Package ID not exists';
            $this->api_response();
		}

		

		if($data['status']==1){

			//Check Package same amount actived or not
			$package_activeted =  $this->Coins_package_model->get_single_row(
				 "amount",
				 COIN_PACKAGE,
				 array("amount"=> $check_package['amount'],"status" =>"1")
			);

			if(!empty($package_activeted)){
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	            $this->api_response_arry['error'] = array();
	            $this->api_response_arry['global_error'] = 'Package already activated with same price';
	            $this->api_response();
			}

			$this->Coins_package_model->update_package($data['package_id'],array('status'=>'1'));
			$this->api_response_arry['service_name']	= 'package_update';
			$this->api_response_arry['message']			= 'Package active successfully!';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']			= array();
			$this->api_response();

		}else{
			$this->Coins_package_model->update_package($data['package_id'],array('status'=>'0'));
			$this->api_response_arry['service_name']	= 'package_update';
			$this->api_response_arry['message']			= 'Package inactive successfully!';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']			= array();
			$this->api_response();
		}

	}

	/**
	 * [package_list_post description]
	 * Summary :- get Package list
	 * @return [type] [description]
	 */
	public function package_redeem_list_post()
	{
		$this->form_validation->set_rules('package_id', 'Package ID', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$_POST['csv'] = false;
		
		$result = $this->Coins_package_model->package_redeem_list();
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;
		$this->api_response();
	}

	/**
	 * [all_coin_package_get description]
	 * Summary :- get Package list
	 * @return [type] [description]
	 */
	public function download_package_redeem_get()
    {
        $_POST = $this->input->get();
        $_POST['csv'] = true;
        $result = $this->Coins_package_model->package_redeem_list();

            if(!empty($result['result'])){
                $result =$result['result'];
                $header = array_keys($result[0]);
                $camelCaseHeader = array_map("camelCaseString", $header);
                $result = array_merge(array($camelCaseHeader),$result);
                $this->load->helper('csv');
                array_to_csv($result,'Package_redeem.csv');
                
            }
        
    }

}
/* End of file User.php */
/* Location: ./application/controllers/Coins_package.php */
