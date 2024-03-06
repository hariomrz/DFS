<?php

class Paystack extends Common_Api_Controller{

	public $pg_id =7;

	function __construct(){
		parent::__construct();

		if(isset($this->app_config['allow_paystack']) && $this->app_config['allow_paystack']['key_value'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, paystack not enabled. please contact admin.";
            $this->api_response();
        }

        $this->PAYSTACK_MODE        = $this->app_config['allow_paystack']['custom_data']['pg_mode'];
        $this->PAYSTACK_SECRET         = $this->app_config['allow_paystack']['custom_data']['secret'];
        $this->PAYSTACK_PUBLIC      = $this->app_config['allow_paystack']['custom_data']['public'];
        // $this->PAYSTACK_CURRENCY    = $this->app_config['currency_abbr']['key_value']; //[EX : INR,USD]
    }

	/**
     * @Method deposit_post
     * @uses deposti money using paystack
     * @since june 2020
     * @author Akhilesh Rathore <>
     * *** */
	public function deposit_post(){
        $this->load->helper('form');
		$this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|callback_decimal_numeric|callback_validate_deposit_amount');
		$this->form_validation->set_rules('furl', 'furl', 'required');
		$this->form_validation->set_rules('surl', 'surl', 'required');
		if ($this->form_validation->run() == FALSE) {
			$this->send_validation_errors();
		}
		$post_data = $this->post();
		$user_id = $this->user_id;
		$surl = $post_data['surl'];
		$furl = $post_data['furl'];
		$product_info = SITE_TITLE . ' deposit via Paystack';
		$email = isset($this->email) ? $this->email : '';
		$phoneno = isset($this->phone_no) ? $this->phone_no : '1234567890';
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
		$fields =[
                "email"=> $email,//email address of the customer
                "amount"=> $amount*100, //amount of money to be transacted by the customer
                "reference"=> (string) $txnid,//ORDER ID from your MERCHANT WEBSITE
                "metadata"=> array(
                	"phone"=>$phoneno,
                	"firstname"=>$firstname,
                ),
            ];
               
            if (LOG_TX) {
            	$test_data = json_encode($fields);
            	$this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "1"));
            }
            $this->data['fields'] = $fields;

            //CURL EXECUTION
            $url = 'https://api.paystack.co/transaction/initialize';

            try{

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));  //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $headers = [
                'Authorization: Bearer '.$this->PAYSTACK_SECRET,
                'Content-Type: application/json',
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $request = curl_exec($ch);

            curl_close($ch);

            if ($request) {

                $request_url = json_decode($request, true);
                $this->data['fields']['access_code']=$request_url['data']['access_code'];
                $this->data['fields']['reference']=$request_url['data']['reference'];
            }

            //CURL EXECUTION
            // Post variable to view with HASH and auto submit form there
            $this->data['data'] = $this->load->view('paystack/deposit', $this->data, true);
            $this->api_response_arry['data'] = $this->data['data'];
            $this->api_response();

        }
        catch(Exception $e){
            $this->data['data'] = $this->load->view('paystack/deposit', $this->data, true);
            $this->api_response_arry['error'] = 'Error : '.$e;
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
         private function _update_tx_status($transaction_id, $status_type, $update_data = array()) {
         	$trnxn_rec = $this->Finance_model->get_transaction($transaction_id);
        if ($status_type == 1) {   // GET order_id from transaction ID
        	if (!empty($trnxn_rec)) {
        		$this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'by your recent transaction', $this->pg_id);
        	}
        }
        // CALL Platform API to update transaction
        $update_data['amount']= $update_data['amount']/100;
        $data = array();
        $data['transaction_status'] = $status_type;
        $data['payment_gateway_id']=$this->pg_id;
        $data['currency']=$this->app_config['currency_abbr']['key_value'];
        $data['gate_way_name'] = "Paystack";
        $data['is_checksum_valid'] = "1";
        if (!empty($update_data)) {

        	//$data['payment_mode'] = isset($payu_response['mode']) ? $payu_response['mode'] : "";
        	$data['bank_name'] = $update_data['bank_name'];
        	// $data['responce_code'] = $update_data['signature'];
        	$data['payment_mode'] = $update_data['channel'];
        	$data['transaction_id'] = isset($update_data['transaction_id']) ? $update_data['transaction_id'] : "";
        	$data['bank_txn_id'] = isset($update_data['authorization_code']) ? $update_data['authorization_code'] : "";
        	$data['txn_amount'] = isset($update_data['amount']) ? $update_data['amount'] : 0;
        	$data['phone'] = isset($update_data['phone']) ? $update_data['phone'] : 0;
        	$data['transaction_message'] = isset($payu_response['error_Message']) ? $payu_response['error_Message'] : "";
        	$data['txn_date'] = format_date();
        	//$data = array_merge($data, $update_data);
        }

        if ($trnxn_rec['transaction_status'] == 0) {
        	$res = $this->Finance_model->update_transaction($data, $transaction_id);
        	// echo $this->db->last_query();exit;
        }

        // When Transaction has been failed , order status will also become fails
        if ($status_type == 2) {
        	$sql = "UPDATE " . $this->db->dbprefix(ORDER) . " AS O
        	INNER JOIN " . $this->db->dbprefix(TRANSACTION) . " AS T ON T.order_id = O.order_id
        	SET O.status = T.transaction_status
        	WHERE T.transaction_id = $transaction_id AND O.status = 0 ";

        	$this->db->query($sql);
        }
        if ($res) {
        	return $trnxn_rec;
        } else {
            //return $trnxn_rec;
        }

        return FALSE;
    }
    public function express_checkout_get(){
    	$furl = FRONT_APP_PATH;
    	if (!$this->input->get()) {
    		redirect($furl, 'location', 301);
    	}
    	$transaction_reference_id = $this->input->get('reference');

    	$result = array();
//The parameter after verify/ is the transaction reference to be verified
    	$url = PAYSTACK_STATUS_URL. $transaction_reference_id;

    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt(
    		$ch, CURLOPT_HTTPHEADER, [
    			'Authorization: Bearer '.$this->PAYSTACK_SECRET]
    		);
    	$request = curl_exec($ch);
    	curl_close($ch);
    	if ($request) {
    		$result = json_decode($request, true);
    	}
    	// print_r($result);exit;
    	if (array_key_exists('data', $result) && array_key_exists('status', $result['data']) && ($result['data']['status'] === 'success')) {
    		$updateTransectionData = array();
    		$updateTransectionData['authorization_code'] = $result['data']['authorization']['authorization_code'];
    		$updateTransectionData['signature'] = $result['data']['authorization']['signature'];
    		$updateTransectionData['channel'] = $result['data']['authorization']['channel'];
    		$updateTransectionData['bank_name'] = $result['data']['authorization']['bank'];
    		$updateTransectionData['int_status'] = $result['status'];
    		$updateTransectionData['text_status'] =$result['data']['status'];
    		$updateTransectionData['phone'] =$result['data']['metadata']['phone'];
    		$updateTransectionData['transaction_id'] =$transaction_reference_id;
    		$updateTransectionData['currency'] =$result['data']['currency'];
    		$updateTransectionData['amount'] =$result['data']['amount'];
    		$updateTransectionData['reference_id']= $result['data']['reference'];
    		$this->load->model("finance/Finance_model");
    		if (LOG_TX) {
    			$test_data = json_encode($updateTransectionData);
    			$this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "2"));
    		}
    		if(trim($updateTransectionData['text_status'])=== 'success'){
				//layer two check start	
    			$transection_id = trim($updateTransectionData['transaction_id']);
                // $this->load->model('paystack_model');
                $server_tr_id = $this->Finance_model->get_single_row('surl,furl,transaction_status,transaction_id', TRANSACTION, array("transaction_id" => $transection_id));
    			if($transection_id === $server_tr_id['transaction_id']){
    				$transection_record = $this->_update_tx_status($transection_id,1,$updateTransectionData);
    				redirect($server_tr_id['surl'], 'location', 301);
    			}
    			else {
    				$transection_record = $this->_update_tx_status($transection_id,2,$updateTransectionData);
    				redirect($server_tr_id['furl'], 'location', 301);
    			}
    		}
    		else {
    			redirect(BASE_APP_PATH, 'location', 301);
    		}
    	}else{
    		redirect($furl, 'location', 301);
    	}
    	// print_r($result);exit;

    }




//currently this method is not in use as payment gateway not provide cancel url it manage itself
    public function cancel_get(){
        $furl = FRONT_APP_PATH;
        if (!$this->input->get()) {
            redirect($furl, 'location', 301);
        }
        $this->load->model("finance/Finance_model");
        $post_data['transaction_id'] = $this->input->get('reference');
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }
            //if (in_array($payment_status, $this->paypal_status)) {
        $txnid = $this->input->get('reference');
            //$bnktxnid = $post_data['tx'];
        $transaction_details = $this->Finance_model->get_single_row('surl,furl,transaction_status', TRANSACTION, array("transaction_id" => $txnid));
        if (!empty($transaction_details) && $transaction_status==0) {
            $furl = $transaction_details['furl'];
        }
        $transaction_info = $this->is_valid_transaction($txnid);
        if (!$transaction_info) {
            redirect($furl, 'location', 301);
        }
        $transaction_record = $this->_update_tx_status($txnid, 2, $post_data);
        redirect($furl, 'location', 301);
    }


}

/* End of file Paystack.php */
/* Location: ROOT/user/application/controllers/Paystack.php */
?>
