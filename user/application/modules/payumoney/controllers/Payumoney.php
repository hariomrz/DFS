<?php

class Payumoney extends Common_Api_Controller {

    public $payumoney_success_status = array("money with payumoney", "settlement in process", "completed", "money settled","success");
    public $payumoney_status = "success";
    public $payumoney_failed_status = "failure";
    public $pg_id = 1;
    function __construct() {
        if(isset($this->app_config['allow_payumoney']) && $this->app_config['allow_payumoney']['key_value'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, Payumoney not enabled. please contact admin.";
            $this->api_response();
        }
        parent::__construct();
        $this->finance_lang = $this->lang->line("finance");

        $this->PG_MODE      = $this->app_config['allow_payumoney']['custom_data']['pg_mode'];
        $this->MERCHANT_KEY = $this->app_config['allow_payumoney']['custom_data']['merchant_key'];
        $this->SALT         = $this->app_config['allow_payumoney']['custom_data']['salt'];
        $this->AUTH_HEADER  = $this->app_config['allow_payumoney']['custom_data']['auth_header'];
        $this->VERSION  = $this->app_config['allow_payumoney']['custom_data']['version'];
        if($this->PG_MODE=='TEST')
        {
            $this->BASE_URL                = 'https://sandboxsecure.payu.in';
            $this->TXN_VALIDATE_BASE_URL       = PAYU_TXN_VALIDATE_BASE_URL_TEST;
        }else{
            $this->BASE_URL                = 'https://secure.payu.in';
            if($this->VERSION=='NEW')
            {
                $this->TXN_VALIDATE_BASE_URL       = NEW_PAYU_TXN_VALIDATE_BASE_URL_PRO;    
            }else{
                $this->TXN_VALIDATE_BASE_URL       = PAYU_TXN_VALIDATE_BASE_URL_PRO;
            }
        }
    }

    /**
     * @method deposit cash
     * @uses funtion to add real and bonus cash to user
     * */
    public function deposit_post() {
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|callback_decimal_numeric|callback_validate_deposit_amount');
        $this->form_validation->set_rules('furl', 'furl', 'required');
        $this->form_validation->set_rules('surl', 'surl', 'required');
        $this->form_validation->set_rules('gst', $this->lang->line('gst_number'), 'trim|callback_validate_gst_number');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();
        $user_id = $this->user_id;

        if (empty($post_data['product_info'])) {
            $post_data['product_info'] = SITE_TITLE . ' amount deposit';
        }

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
        $user_info = $this->Finance_model->get_single_row('user_id,user_name,phone_no,email', USER, array("user_id" => $user_id));
        $email = isset($user_info['email']) ? $user_info['email'] : "";
        $phoneno = isset($user_info['phone_no']) ? $user_info['phone_no'] : "";
        $firstname = isset($user_info['user_name']) ? $user_info['user_name'] : "";
        $_POST['payment_gateway_id'] = $this->pg_id;
        // GET transaction ID from Database after generating an order
        $txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $post_data['product_info'], $post_data['surl'], $post_data['furl'], $promo_code,$deal);
        
        $gst_data = gst_calculate($amount,$this->app_config);
        $amount = isset($gst_data['amount'])?$gst_data['amount']:$amount;

        $user_info = $this->Finance_model->get_single_row('user_id,user_name,phone_no,email', USER, array("user_id" => $user_id));
        $posted = [
            'key' => $this->MERCHANT_KEY,
            'txnid' => (string) $txnid,
            'amount' => $amount,
            'productinfo' => $post_data['product_info'],
            'firstname' => $firstname,
            'email' => $email,
            'phone' => $phoneno
        ];

        // Hash Sequence
        $hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
        $hashVarsSeq = explode('|', $hashSequence);
        $hash_string = '';

        foreach ($hashVarsSeq as $hash_var) {
            $hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] . '|' : '|';
        }

        $hash_string .= $this->SALT;
        $hash = strtolower(hash('sha512', $hash_string));
        $action = $this->BASE_URL . '/_payment';

        $this->data = $posted;
        $this->data['action'] = $action;
        $this->data['hash'] = $hash;
        $this->data['surl'] = FRONT_APP_PATH . 'user/deposit/payumoney/success';
        $this->data['furl'] = FRONT_APP_PATH . 'user/deposit/payumoney/failure';

        if (LOG_TX) {
            $this->db->insert(TEST, array('data' => json_encode($this->data), 'added_date' => format_date(), 'user_id' => 0));
        }

        // Post variable to view with HASH and auto submit form there
        //$this->data['data'] = $this->load->view('payumoney/deposit', $this->data, true);
        if(isset($post_data['is_mobile']) && $post_data['is_mobile'] == "1"){
            unset($this->data['surl'],$this->data['furl']);
            $this->data['data'] = $this->data;
        }else{
            $this->data['data'] = $this->load->view('payumoney/deposit', $this->data, true);
        }
        $this->api_response_arry['data'] = $this->data['data'];
        $this->api_response();
    }

    /**
     * @method Transaction successful
     * @uses funtion to add real and bonus cash to user
     * */
    public function success_post() {
        $furl = FRONT_APP_PATH;
        if (!$this->input->post()) {
            redirect($furl, 'location', 301);
        }

        $this->load->model("finance/Finance_model");
        $post_data = $this->post();
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }

        $payu_response = $post_data;
        $payment_status = $post_data['status'];

        if (trim(strtolower($payment_status)) == $this->payumoney_status) {
            $txnid = $post_data['txnid'];
            $transaction_details = $this->Finance_model->get_transaction_info($txnid);
            if (empty($transaction_details)) {
                $furl = $transaction_details['furl'];
            }

            // if ($this->is_payu_response_valid($payu_response)) {
                //check txnid is valid or not
                if (!empty($transaction_details) && $transaction_details['transaction_status'] == 1) {
                    redirect($transaction_details['surl'], 'location', 301);
                };
                $transaction_info = $this->is_valid_transaction($txnid);

                if (!$transaction_info) {
                    redirect($furl, 'location', 301);
                }
                $pay_transaction_info = $this->validate_transaction($txnid);
                //status = 0 means success and -1 = failure
                if($this->VERSION=='NEW'){
                    if($pay_transaction_info['status'] == "SUCCESS" && !empty($pay_transaction_info['result']['transaction_details'][$txnid]) && in_array(strtolower($pay_transaction_info['result']['transaction_details'][$txnid]['status']),$this->payumoney_success_status)){
                        
                        $txn_pg_amount = $pay_transaction_info['result']['transaction_details'][$txnid]['amt'];
                        $txn_pg_amount = number_format($txn_pg_amount, 2, '.', '');
                        $real_amount = $transaction_details['real_amount']+$transaction_details['tds'];
                        //additional check for amount mismatch
                        if($txn_pg_amount == $real_amount){
                            // Update status=success to transaction table by calling API
                            $res_data = $pay_transaction_info['result']['transaction_details'][$txnid];

                            $payu_response = array(
                                            "mode"=>isset($res_data['mode']) ? $res_data['mode'] : "",
                                            "mihpayid"=>isset($res_data['txnid']) ? $res_data['txnid'] : "",
                                            "bank_ref_num"=>isset($res_data['bank_ref_num']) ? $res_data['bank_ref_num'] : "",
                                            "amount"=>isset($res_data['amt']) ? $res_data['amt'] : 0,
                                            "addedon"=>isset($res_data['addedon']) ? $res_data['addedon'] : NULL,
                                            "error_Message"=>isset($res_data['error_Message']) ? $res_data['error_Message'] : "",
                                            "key"=>"",
                            );
                            $transaction_record = $this->_update_tx_status($txnid, 1, $payu_response);
                            // Redirect to Failure URL
                            redirect($transaction_record['surl'], 'location', 301);
                        }else{
                            redirect($furl, 'location', 301);
                        }  
                    } else {
                        redirect($furl, 'location', 301);
                    }
                }else{
                    if ($pay_transaction_info['status'] == "SUCCESS" && !empty($pay_transaction_info['result']) && $pay_transaction_info['result']['status'] == "0" && in_array(strtolower($pay_transaction_info['result']['result']['0']->status), $this->payumoney_success_status)) {
                        $txn_pg_amount = $pay_transaction_info['result']['result']['0']->amount;
                        $txn_pg_amount = number_format($txn_pg_amount, 2, '.', '');
                        $real_amount = $transaction_details['real_amount']+$transaction_details['tds'];
                        //additional check for amount mismatch
                        if($txn_pg_amount == $real_amount){
                            // Update status=success to transaction table by calling API
                            $transaction_record = $this->_update_tx_status($txnid, 1, $payu_response);
                            // Redirect to Failure URL
                            redirect($transaction_record['surl'], 'location', 301);
                        }else{
                            redirect($furl, 'location', 301);
                        }
                    } else {
                        redirect($furl, 'location', 301);
                    }
                }
                
            // } else {
            //     redirect($furl, 'location', 301);
            // }
        }
    }

    /**
     * @method Transaction unsuccessful
     * @uses funtion to add real and bonus cash to user
     * */
    public function failure_post() {
        $furl = FRONT_APP_PATH;
        if (!$this->input->post()) {
            redirect($furl, 'location', 301);
        }

        $this->load->model("finance/Finance_model");
        $post_data = $this->post();

        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }
        $payu_response = $post_data;
        $txnid = $post_data['txnid'];
        // if ($this->is_payu_response_valid($payu_response)) {
            $transaction_details = $this->Finance_model->get_single_row('surl,furl', TRANSACTION, array("transaction_id" => $txnid));
            if (!empty($transaction_details)) {
                $furl = $transaction_details['furl'];
            }
            //check txnid is valid or not
            $transaction_info = $this->is_valid_transaction($txnid);
            if (!$transaction_info) {
                redirect($furl, 'location', 301);
            }
            // Update status=failure to transaction table by calling API
            $transaction_record = $this->_update_tx_status($txnid, 2, $payu_response);
            // Redirect to Failure URL
            redirect($transaction_record['furl'], 'location', 301);
        // } else {
        //     redirect($furl, 'location', 301);
        // }
    }

    /**
     * update order status to platform
     * 
     * @param int $transaction_id Transaction ID
     * @param int $status_type Status Type
     * @return bool Status updated or not
     */
    private function _update_tx_status($transaction_id, $status_type, $payu_response) {

        $trnxn_rec = $this->Finance_model->get_transaction($transaction_id);

        if ($status_type == 1) {
            if (!empty($trnxn_rec)) {
                $this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'by your recent transaction', 1);
            }
        }
        // CALL Platform API to update transaction
        $data['payment_mode'] = isset($payu_response['mode']) ? $payu_response['mode'] : "";
        $data['mid'] = isset($payu_response['key']) ? $payu_response['key'] : "";
        $data['txn_id'] = isset($payu_response['mihpayid']) ? $payu_response['mihpayid'] : "";
        $data['bank_txn_id'] = isset($payu_response['bank_ref_num']) ? $payu_response['bank_ref_num'] : "";
        $data['txn_amount'] = isset($payu_response['amount']) ? $payu_response['amount'] : 0;
        $data['txn_date'] = isset($payu_response['addedon']) ? $payu_response['addedon'] : NULL;
        $data['gate_way_name'] = "Payumoney";
        $data['is_checksum_valid'] = "1";
        $data['transaction_status'] = $status_type;
        $data['transaction_message'] = isset($payu_response['field9']) ? $payu_response['field9'] : "";

        $res = $this->Finance_model->update_transaction($data, $transaction_id);
        // When Transaction has been failed , order status will also become fails
        if ($status_type == 2) {
            $sql = "UPDATE " . $this->db->dbprefix(ORDER) . " AS O
                    INNER JOIN " . $this->db->dbprefix(TRANSACTION) . " AS T ON T.order_id = O.order_id
                    SET O.status = T.transaction_status
                    WHERE T.transaction_id = $transaction_id AND O.status = 0";

            $this->db->query($sql);
        }
        if ($res) {
            return $trnxn_rec;
        }
        return FALSE;
    }

    private function is_valid_transaction($txnid) {
        if (empty($txnid)) {
            return false;
        }
        $txnid = trim($txnid);
        $where = array(
            'transaction_id' => $txnid,
            'transaction_status !=' => 1 //for pending      
        );
        $transaction_info = $this->Finance_model->get_single_row('transaction_id,order_id', TRANSACTION, $where);
        return $transaction_info;
    }

    private function validate_transaction($txnid) {
        if (empty($txnid)) {
            return false;
        }

        $ch = curl_init();

        if($this->VERSION=='NEW')
        {
            $post_url = $this->TXN_VALIDATE_BASE_URL.'/merchant/postservice?form=2';
            $data = $this->MERCHANT_KEY.'|verify_payment|'.$txnid.'|'.$this->AUTH_HEADER;
            $hash = urlencode(hash("sha512", $data));
            $data = 'key='.$this->MERCHANT_KEY.'&command=verify_payment&var1='.$txnid.'&hash='.$hash;
            
            $header = array(
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded'
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        }else{
            $postData = array();
            $postData['merchantKey'] = $this->MERCHANT_KEY;
            $postData['merchantTransactionIds'] = $txnid;
            $postNow = http_build_query($postData);
            $post_url = $this->TXN_VALIDATE_BASE_URL . "/payment/payment/chkMerchantTxnStatus?" . $postNow;

            $header = array(
                'Authorization: ' . $this->AUTH_HEADER
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, TRUE);
        }
        
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $out = curl_exec($ch);
        //if got error
        if (curl_errno($ch)) {
            $c_error = curl_error($ch);
            if (empty($c_error)) {
                $c_error = 'Some server error';
            }
            return array('status' => 'FAILURE', 'error' => $c_error);
        }
        $out = json_decode(trim($out),true);
        return array('status' => 'SUCCESS', 'result' => $out);
    }

    /**
     * marke succss payment in case when using payumone sdk and data comes from frontend 
     * it is allowed only with session key as compare to success_post
     */
    public function mark_success_post() {
        $furl = FRONT_APP_PATH;
        $url = FRONT_APP_PATH;
        if (!$this->input->post()) {
            $url = $furl;
        }
        $post_data = $this->post();
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }

        if(empty($post_data['result']) || $post_data['result']== null){
            $this->api_response_arry['error']	= 'No transaction to update ';
            $this->api_response_arry['response_code']	= REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        if($post_data['result'] && $post_data['result']!= null)
        {
            $post_data = $post_data['result'];
        }

        $payu_response = $post_data;
        $payment_status = $post_data['status'];

        if (trim(strtolower($payment_status)) == $this->payumoney_status) {
            $txnid = $post_data['txnid'];
            $this->load->model("finance/Finance_model");
            $is_processed = $this->Finance_model->filter_multiple_hit($txnid);
            
            if($is_processed['payout_processed']>=1){
                $this->api_response_arry['error']	= 'Tansaction already processed';
                $this->api_response_arry['response_code']	= REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response();
            }

            $transaction_details = $this->Finance_model->get_transaction_info($txnid);
           
            if (!empty($transaction_details) && $transaction_details['status'] == 2) {
                $this->api_response_arry['data']['url']	= $transaction_details['furl'];
                $this->api_response();
            }elseif (!empty($transaction_details) && $transaction_details['status'] == 1) {
                $this->api_response_arry['data']['url']	= $transaction_details['surl'];
                $this->api_response();
            }

            if (!empty($transaction_details) && $transaction_details['transaction_status'] == 1) {
                $url = $transaction_details['surl'];
            }
            $transaction_info = $this->is_valid_transaction($txnid);

            if (!$transaction_info) {
                $url = $transaction_details['furl'];
            }
            $pay_transaction_info = $this->validate_transaction($txnid);
            //status = 0 means success and -1 = failure
            if($this->VERSION=='NEW'){
                if($pay_transaction_info['status'] == "SUCCESS" && !empty($pay_transaction_info['result']['transaction_details'][$txnid]) && in_array(strtolower($pay_transaction_info['result']['transaction_details'][$txnid]['status']),$this->payumoney_success_status)){
                    
                    $txn_pg_amount = $pay_transaction_info['result']['transaction_details'][$txnid]['amt'];
                    $txn_pg_amount = number_format($txn_pg_amount, 2, '.', '');
                    $real_amount = $transaction_details['real_amount']+$transaction_details['tds'];
                    //additional check for amount mismatch
                    if($txn_pg_amount == $real_amount){
                        // Update status=success to transaction table by calling API
                        $res_data = $pay_transaction_info['result']['transaction_details'][$txnid];

                            $payu_response = array(
                                            "mode"=>isset($res_data['mode']) ? $res_data['mode'] : "",
                                            "mihpayid"=>isset($res_data['txnid']) ? $res_data['txnid'] : "",
                                            "bank_ref_num"=>isset($res_data['bank_ref_num']) ? $res_data['bank_ref_num'] : "",
                                            "amount"=>isset($res_data['amt']) ? $res_data['amt'] : 0,
                                            "addedon"=>isset($res_data['addedon']) ? $res_data['addedon'] : NULL,
                                            "error_Message"=>isset($res_data['error_Message']) ? $res_data['error_Message'] : "",
                                            "key"=>"",
                            );
                            $transaction_record = $this->_update_tx_status($txnid, 1, $payu_response);
                            // Redirect to Failure URL
                            // redirect($transaction_record['surl'], 'location', 301);
                            $url = $transaction_record['surl'];
                        }else{
                            $url = $furl;
                        }  
                    } else {
                        $url = $furl;
                    }
                }else{
                    if ($pay_transaction_info['status'] == "SUCCESS" && !empty($pay_transaction_info['result']) && $pay_transaction_info['result']['status'] == "0" && in_array(strtolower($pay_transaction_info['result']['result']['0']->status), $this->payumoney_success_status)) {
                        $txn_pg_amount = $pay_transaction_info['result']['result']['0']->amount;
                        $txn_pg_amount = number_format($txn_pg_amount, 2, '.', '');
                        $real_amount = $transaction_details['real_amount']+$transaction_details['tds'];
                        //additional check for amount mismatch
                        if($txn_pg_amount == $real_amount){
                            // Update status=success to transaction table by calling API
                            $transaction_record = $this->_update_tx_status($txnid, 1, $payu_response);
                            // Redirect to Failure URL
                            $url = $transaction_record['surl'];
                        }else{
                            $url = $furl;
                        }
                    } else {
                        $url = $furl;
                    }
                }
        }
        $this->api_response_arry['data']['url']	= $url;
		$this->api_response();
    }

}
