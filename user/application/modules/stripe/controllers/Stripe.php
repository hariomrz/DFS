<?php

class Stripe extends Common_Api_Controller{

	public $pg_id =10;
	public $currency = 'INR';

	function __construct(){
		parent::__construct();
		$this->load->helper('form');
	}
	//function to add real and bonas cash to user
	public function deposit_post(){
		// print_r($this->app_config['allow_stripe']['custom_data']['p_key']);exit;
		$this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|callback_decimal_numeric|callback_check_equal_greater[5]');
		$this->form_validation->set_rules('furl', 'furl', 'trim|required');
		$this->form_validation->set_rules('surl', 'surl', 'trim|required');
		$this->form_validation->set_rules('source', 'source token', 'trim|required');
		if ($this->form_validation->run() == FALSE) {
			$this->send_validation_errors();
		}
		$post_data = $this->post();
		$user_id = $this->user_id;
		$surl = $post_data['surl'];
		$furl = $post_data['furl'];
		$product_info = SITE_TITLE . ' deposit via Stripe';
		$email = isset($this->email) ? $this->email : '';
		$phoneno = isset($this->phone_no) ? $this->phone_no : '';
		$firstname = isset($this->user_name) ? $this->user_name : 'User';
		$amount = $post_data['amount'];
		$amount = number_format($amount, 2, '.', '');
		$this->load->model("finance/Finance_model");
		$promo_code = array();
		if (!empty($post_data['promo_code'])) {
			$promo_code = $this->validate_promo($post_data);
		}
		$deal = array();
		if (!empty($post_data['deal_id'])) {
			$deal = $this->validate_deal($post_data);
		}
        // till now for me order id and invoice number are same txnid as billow
        $_POST['payment_gateway_id'] = $this->pg_id;
		$txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $product_info, $surl, $furl, $promo_code, $deal);
		$this->currency =$this->app_config['allow_stripe']['custom_data']['s_currency'];
		$fields =[
					'amount' => $amount * 100,
					'currency' => $this->currency,
					'source' => $post_data['source'],
					'description' => 'Deposit via stripe',
					'metadata' => ['txn_id' => $txnid, 'user_id' => $this->user_id]
				];
		try{
			$data = array(
				"p_key"=>$this->app_config['allow_stripe']['custom_data']['p_key'],
				"s_key"=>$this->app_config['allow_stripe']['custom_data']['s_key'],
			);
			$this->load->library('new_stripe',$data);
			\Stripe\Stripe::setVerifySslCerts(false);
			$response = \Stripe\Charge::create($fields);
			$response = json_decode(json_encode($response),true);
			if($response['paid']==1 && $response['amount']==$fields['amount'] && $response['metadata']['user_id'] == $this->user_id)
			{
				if (LOG_TX) {
					$test_data = json_encode($fields);
					$res_data = json_encode($response);
					$this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "1"));
					$this->db->insert(TEST, array('data' => $res_data, 'added_date' => format_date(), "data_type" => "2"));
				}
				$transection_record = $this->_update_tx_status($response['metadata']['txn_id'],1,$response);
				$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
				$this->api_response_arry['message'] = "Payment success";
				$this->api_response_arry['data'] = array("surl"=>$surl,"status"=>'1');
				$this->api_response();
				// redirect($server_tr_id['surl'], 'location', 301);
				
			}else {
				$transection_record = $this->_update_tx_status($txnid,0,array());
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['global_error'] = "Some problem in payment";
				$this->api_response_arry['data'] = array("furl"=>str_replace('success','pending',$surl),"status"=>'0');
				$this->api_response();
				// redirect($server_tr_id['furl'], 'location', 301);
			}
		}catch(Exception $e)
		{
			$body = $e->getJsonBody();
			$update_data = [
				"receipt_url"	=>$body['error']['message'],
				"amount"		=>$amount*100,
			];
			$transection_record = $this->_update_tx_status($txnid,0,$update_data);
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error'] = $body['error']['message'];
			$this->api_response_arry['data'] = array("furl"=>str_replace('success','pending',$surl),"status"=>'0');
			$this->api_response();
		}

        }
         /**
     * update order status to platform 
     * 
     * @param int $transaction_id Transaction ID
     * @param int $status_type Status Type
     * @return bool Status updated or not
     */
		private function _update_tx_status($transaction_id, $status_type, $update_data = array()) 
		{
         	$trnxn_rec = $this->Finance_model->get_transaction($transaction_id);
			if ($status_type == 1) {   // GET order_id from transaction ID
				if (!empty($trnxn_rec) && isset($update_data['id'])) {
					$this->db->update(TRANSACTION,['pg_order_id'=>$update_data['id']],["transaction_id"=>$transaction_id]);
					$this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'by your recent transaction', $this->pg_id);
				}
			}

			$data = array();
			$data['transaction_status'] = $status_type;
			$data['payment_gateway_id']=$this->pg_id;
			$data['currency']=$this->currency;
			$data['gate_way_name'] = "Stripe";

			if (!empty($update_data)) {
				$data['txn_id'] = isset($update_data['metadata']['txn_id']) ? $update_data['metadata']['txn_id'] : NULL;
				$data['txn_amount'] = isset($update_data['amount']) ? ($update_data['amount']/100) : 0;
				$data['phone'] = isset($update_data['phone']) ? $update_data['phone'] : '';
				$data['txn_date'] = isset($update_data['created']) ? date( 'Y-m-d H:i:s' , $update_data['created']) : format_date();
				$data['bank_txn_id'] = isset($update_data['balance_transaction']) ? $update_data['balance_transaction'] : "";
				$data['transaction_message'] = isset($update_data['receipt_url']) ? $update_data['receipt_url'] : "";
			}
			
			if ($trnxn_rec['transaction_status'] == 0) {
				$res = $this->Finance_model->update_transaction($data, $transaction_id);
				return true;
				//echo $this->db->last_query();exit;
			}

			// When Transaction has been failed , order status will also become fails
			if ($status_type == 2) {
				$sql = "UPDATE " . $this->db->dbprefix(ORDER) . " AS O
				INNER JOIN " . $this->db->dbprefix(TRANSACTION) . " AS T ON T.order_id = O.order_id
				SET O.status = T.transaction_status
				WHERE T.transaction_id = $transaction_id AND O.status = 0 ";
				$this->db->query($sql);
			}
        return FALSE;
		}    
	}
		?>
