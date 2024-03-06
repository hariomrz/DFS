<?php

class Cashierpay extends Common_Api_Controller{

	public $pg_id =16;
    public $success_code = array(000);
    public $pending_code = array(003,006,011);
    public $order_prefix = '';

	function __construct(){
		parent::__construct();

       if(!$this->app_config['allow_cashierpay']['key_value']){
        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['status'] = FALSE;
        $this->api_response_arry['message'] = "Sorry, cashierpay not enabled. please contact admin.";
        $this->api_response();
        }

        $this->order_prefix = isset($this->app_config['order_prefix']) ? $this->app_config['order_prefix']['key_value'] : '';

    }

     /**
     * @Method deposit_post
     * @uses deposti money using ifantasy
     * @since December 2021
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
		$product_info = SITE_TITLE . ' deposit via cashierpay';
		$email = isset($this->email) ? $this->email : 'user@vinfotech.com';
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
        $mode = $this->app_config['allow_cashierpay']['custom_data']['mode'];
        $payId = $this->app_config['allow_cashierpay']['custom_data']['payId'];
        $secretKey = $this->app_config['allow_cashierpay']['custom_data']['secretKey'];
        $currency = $this->app_config['allow_cashierpay']['custom_data']['currency'];

            if($mode == 'PROD')
            {
                $url = CASHIERPAY_PBASE_URL.CASHIERPAY_PAY_URL;
            }else{
                $url = CASHIERPAY_TBASE_URL.CASHIERPAY_PAY_URL;
            }

		$_POST['payment_gateway_id'] = $this->pg_id;
		$txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $product_info, $surl, $furl, $promo_code, $deal);
        $txnid = $this->order_prefix.$txnid;


        $req_data = array(
            "PAY_ID"			=>$payId,
            "ORDER_ID"			=>$txnid,
            "RETURN_URL"		=>CASHIERPAY_CALLBACK_URL,
            "CUST_NAME"			=>$firstname,
            "CURRENCY_CODE"		=>$currency,
            "CUST_EMAIL"		=>$email,
            "AMOUNT"			=>$amount*100,
            "TXNTYPE"           =>'SALE',
            "CUST_PHONE"		=>$phoneno,
            );
            $req_data['HASH'] = get_cashierpay_hash($req_data,$secretKey);
            $this->data['request']= $req_data;
            $this->data['url']=$url;
            $this->load->helper('form');
            $this->data['data'] = $this->load->view('cashierpay/deposit', $this->data, true);
            // print_r($this->data['data']);exit;
            $this->api_response_arry['data'] = $this->data['data'];
            $this->api_response();
        }

        /**
         * call back function
         */
    public function callback_post()
    {
        $furl = FRONT_APP_PATH;
        $post_data = $this->input->post();
        if (LOG_TX) {
            error_log("\n\n".format_date().' RESPONSE DATA : '.json_encode($post_data).'<br>',3,'/var/www/html/cron/application/logs/cashierpay.log');
        }
        if (!$this->input->post() || !isset($post_data['ORDER_ID'])) {
            redirect($furl, 'location', 301);
        }
        // print_r($post_data);exit;
        $this->load->model("finance/Finance_model");
        $order_id = substr($post_data['ORDER_ID'],5);
        $txn_info = $this->Finance_model->get_single_row('*',TRANSACTION, array('transaction_id' => $order_id));
        if(empty($txn_info)){
            
            redirect($furl, 'location', 301);
        }else if($txn_info['transaction_status'] == "1"){
            redirect($txn_info['surl'], 'location', 301);
        }else if($txn_info['transaction_status'] == "2"){
            redirect($txn_info['furl'], 'location', 301);
        }

        $mode = $this->app_config['allow_cashierpay']['custom_data']['mode'];
        $payId = $this->app_config['allow_cashierpay']['custom_data']['payId'];
        $secretKey = $this->app_config['allow_cashierpay']['custom_data']['secretKey'];
        $currency = $this->app_config['allow_cashierpay']['custom_data']['currency'];

        if($mode == 'PROD')
        {
            $url = "https://enquiry.cashierpay.online/".CASHIERPAY_STATUS_URL;
        }else{
            $url = "https://enquiry.cashierpay.online/".CASHIERPAY_STATUS_URL;
        }
        $status_req = array(
            "ORDER_ID"          => $this->order_prefix.$order_id,
            "AMOUNT"            => $post_data['AMOUNT'],
            "TXNTYPE"           => 'STATUS',
            "CURRENCY_CODE"     => $currency,
            "PAY_ID"            => $payId,
        );
        $status_req['HASH'] = get_cashierpay_hash($status_req,$secretKey);
        $txn_data = get_cashierpay_txn_status($status_req,$url);
        if (LOG_TX) {
            error_log("\n\n".format_date().' STATUS CHECK RESPONSE DATA : '.json_encode($txn_data).'<br>',3,'/var/www/html/cron/application/logs/cashierpay.log');
        }
        if(
            isset($txn_data['RESPONSE_CODE']) && in_array(strtoupper($txn_data['RESPONSE_CODE']),$this->success_code) && 
            isset($txn_data['STATUS']) && strtoupper($txn_data['STATUS'])=='CAPTURED'
            ){
            $txnid = $txn_info['transaction_id'];
            $pg_response = $txn_data;
            $pg_response['txn_amount'] = number_format(($txn_data['AMOUNT']/100),2,'.','');
            $transaction_details = $this->Finance_model->get_transaction_info($txnid);
            if($pg_response['txn_amount'] == $transaction_details['real_amount']){
                $transaction_record = $this->_update_tx_status($txnid, 1, $pg_response);
                redirect($transaction_record['surl'], 'location', 301);
            }else{
                $txnid = $txn_info['transaction_id'];
                $pg_response = $txn_data;
                $pg_response['txn_amount'] = number_format($txn_data['txn_amount'],2,'.','');
                $transaction_record = $this->_update_tx_status($txnid, 2, $pg_response);
                redirect($txn_info['furl'], 'location', 301);
            }
        }elseif(isset($txn_data['RESPONSE_CODE']) && in_array($txn_data['RESPONSE_CODE'],$this->pending_code)){
            $purl = str_replace("failure","pending",$txn_info['furl']);
            redirect($purl, 'location', 301);
        }else{
            $transaction_record = $this->_update_tx_status($txn_info['transaction_id'], 2, $pg_response);
            redirect($transaction_record['furl'], 'location', 301);
        }
    }

    /**
     * update order status to platform
     * 
     * @param int $transaction_id Transaction ID
     * @param int $status_type Status Type
     * @return bool Status updated or not
     */
    private function _update_tx_status($transaction_id, $status_type, $pg_response) {

        // echo "<pre>";print_r($pg_response);exit;
        unset($pg_response['HASH']);
        $secretKey = $this->app_config['allow_cashierpay']['custom_data']['secretKey'];
        $HASH = get_cashierpay_hash($pg_response,$secretKey);
        $trnxn_rec = $this->Finance_model->get_transaction($transaction_id);
        if ($status_type == 1) {
            if (!empty($trnxn_rec)) {
                $this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'by your recent transaction', $this->pg_id);
            }
        }
        // CALL Platform API to update transaction
        $data['pg_order_id'] = $this->order_prefix.$transaction_id;
        $data['bank_txn_id'] = (isset($pg_response['RRN']) && $status_type == 1) ? $pg_response['RRN'] : "";
        $data['txn_amount'] = isset($pg_response['AMOUNT']) ? ($pg_response['AMOUNT']/100) : 0;
        $data['txn_date'] = isset($pg_response['RESPONSE_DATE_TIME']) ? $pg_response['RESPONSE_DATE_TIME'] : NULL;
        $data['gate_way_name'] = "Cashierpay";
        $data['transaction_status'] = $status_type;
        $data['currency'] = isset($pg_response['CURRENCY_CODE']) ? $pg_response['CURRENCY_CODE'] : "";
        $data['bank_name'] = isset($pg_response['CARD_MASK']) ? $pg_response['CARD_MASK'] : "";
        $data['payment_mode'] = isset($pg_response['PAYMENT_TYPE']) ? $pg_response['PAYMENT_TYPE'] : "";
        $data['transaction_message'] = isset($pg_response['HASH']) ? $pg_response['HASH'] : "";
        
        $res = $this->Finance_model->update_transaction($data, $transaction_id);
        $order_detail = $this->Finance_model->get_single_row("user_id,real_amount", ORDER, array("order_id" => $trnxn_rec["order_id"]));
        $user_data = $this->Finance_model->get_single_row("user_name,email", USER, array("user_id" => $order_detail["user_id"]));

        // When Transaction has been failed , order status will also become fails
        if ($status_type == 2) {
            $sql = "UPDATE " . $this->db->dbprefix(ORDER) . " AS O
                    INNER JOIN " . $this->db->dbprefix(TRANSACTION) . " AS T ON T.order_id = O.order_id
                    SET O.status = T.transaction_status
                    WHERE T.transaction_id = $transaction_id";

            $this->db->query($sql);
            $tmp = array();
            $tmp["notification_type"] = 42;
            $tmp["source_id"] = 0;
            $tmp["notification_destination"] = 7; //  Web, Push, Email
            $tmp["user_id"] = $order_detail["user_id"];
            $tmp["to"] = $user_data["email"];
            $tmp["user_name"] = $user_data["user_name"];
            $tmp["added_date"] = format_date();
            $tmp["modified_date"] = format_date();
            $tmp["subject"] = "Deposit amount Failed to credit";
            $input = array("amount" => $order_detail["real_amount"]);
            $tmp["content"] = json_encode($input);
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($tmp);
        }
        if ($res) {
            return $trnxn_rec;
        }
        return FALSE;
    }
}	
    ?>