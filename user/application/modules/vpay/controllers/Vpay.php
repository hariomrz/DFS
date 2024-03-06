<?php
class Vpay extends Common_Api_Controller {
    public $success_status_arr = array("success");
    public $success_status = "success";
    public $failed_status = "failed";
    public $pg_id = 13;
    function __construct() {
        parent::__construct();
        $this->finance_lang = $this->lang->line("finance");

        if(isset($this->app_config['allow_vpay']) && $this->app_config['allow_vpay']['key_value'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, vpay not enabled. please contact admin.";
            $this->api_response();
        }

        $this->VPAY_PG_MODE       = $this->app_config['allow_vpay']['custom_data']['pg_mode'];
        $this->VPAY_MERCHANT_KEY     =$this->app_config['allow_vpay']['custom_data']['key'];
        $this->VPAY_MERCHANT_MID    =$this->app_config['allow_vpay']['custom_data']['mid'];
        $this->currency             = $this->app_config['currency_abbr']['key_value'];//[EX : INR,USD]

        $this->VPAY_BASE_URL       = ($this->VPAY_PG_MODE =='TEST') ? VPAY_BASE_URL_TEST:VPAY_BASE_URL_PRO;
        
    }

    private function generate_hash($data_arr){
        //Please update hash keys
        $hash_format = "mid|txnid|amount|refid|title|email|mobile|ud1|ud2|ud3|ud4|ud5";
        $secret_key = $this->VPAY_MERCHANT_KEY;
        $secret_mid = $data_arr['mid'];
        $hash_vars = explode('|', $hash_format);
        $hash_str = '';
        foreach($hash_vars as $hvar) {
            $hash_str .= isset($data_arr[$hvar]) ? $data_arr[$hvar].'|':'|';
        }

        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_mid), 0, 16);
        $hash = base64_encode(openssl_encrypt($hash_str, $encrypt_method, $key, 0, $iv));
        return $hash;
    }

    private function validate_transaction($txnid) {
        if (empty($txnid)) {
            return false;
        }

        $postData = array();
        $postData['mid'] = $this->VPAY_MERCHANT_MID;
        $postData['txnid'] = $txnid;
        $post_url = $this->VPAY_BASE_URL.'/api/get_txn';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_URL, $post_url);
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
        $out = (array) json_decode(trim($out));
        return array('status' => 'SUCCESS', 'result' => $out);
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
            'mid' => $this->VPAY_MERCHANT_MID,
            'txnid' => (string) $txnid,
            'refid' => $user_id,
            'amount' => $amount,
            'title' => $post_data['product_info'],
            'mobile' => $phoneno,
            'email' => $email
        ];
        $hash = $this->generate_hash($posted);
        $action = $this->VPAY_BASE_URL.'/api/order';
        $this->data = $posted;
        $this->data['action'] = $action;
        $this->data['hash'] = $hash;
        $this->data['surl'] = USER_API_URL.'vpay/success';
        $this->data['furl'] = USER_API_URL.'vpay/failure';

        if (LOG_TX) {
            $this->db->insert(TEST, array('data' => json_encode($this->data), 'added_date' => format_date(), 'user_id' => 0));
        }

        $this->data['data'] = $this->load->view('vpay/deposit', $this->data, true);
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
        //echo "<pre>";print_r($post_data);die;
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }
        $payment_status = $post_data['status'];
        if (trim(strtolower($payment_status)) == $this->success_status) {
            $txnid = $post_data['txnid'];
            $transaction_details = $this->Finance_model->get_transaction_info($txnid);
            if (!empty($transaction_details)) {
                $furl = $transaction_details['furl'];
            }

            //check txnid is valid or not
            if (!empty($transaction_details) && $transaction_details['transaction_status'] == 1) {
                redirect($transaction_details['surl'], 'location', 301);
            };
            $txn_info = $this->validate_transaction($txnid);
            //status = 0 means success and -1 = failure
            if($txn_info['status'] == "SUCCESS" && !empty($txn_info['result']) && $txn_info['result']['code'] == "200" && $txn_info['result']['data']->status == "success") {
                $txn_info = (array)$txn_info['result']['data'];
                $pg_amount = $txn_info['amount'];
                $pg_amount = number_format($pg_amount, 2, '.', '');
                $real_amount = $transaction_details['real_amount']+$transaction_details['tds'];
                //additional check for amount mismatch
                if($pg_amount == $real_amount){
                    // Update status=success to transaction table by calling API
                    $transaction_record = $this->_update_tx_status($txnid, 1, $txn_info);
                    // Redirect to Failure URL
                    redirect($transaction_record['surl'], 'location', 301);
                }else{
                    redirect($furl, 'location', 301);
                }
            } else {
                redirect($furl, 'location', 301);
            }
        }else {
            if(isset($post_data['txnid']) && $post_data['txnid'] != ""){
                $txn_info = $this->Finance_model->get_single_row('*',TRANSACTION, array('transaction_id' => $post_data['txnid']));
                if(!empty($txn_info)){
                    $furl = $txn_info['furl'];
                }
            }
            redirect($furl, 'location', 301);
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
        //echo "<pre>";print_r($post_data);die;
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }
        $payment_status = $post_data['status'];
        if (trim(strtolower($payment_status)) == $this->failed_status) {
            $txnid = $post_data['txnid'];
            $transaction_details = $this->Finance_model->get_transaction_info($txnid);
            if (!empty($transaction_details)) {
                $furl = $transaction_details['furl'];
            }
            // Update status=failure to transaction table by calling API
            $transaction_record = $this->_update_tx_status($txnid, 2, $post_data);
            // Redirect to Failure URL
            redirect($transaction_record['furl'], 'location', 301);
        }else {
            if(isset($post_data['txnid']) && $post_data['txnid'] != ""){
                $txn_info = $this->Finance_model->get_single_row('*',TRANSACTION, array('transaction_id' => $post_data['txnid']));
                if(!empty($txn_info)){
                    $furl = $txn_info['furl'];
                }
            }
            redirect($furl, 'location', 301);
        }
    }

    /**
     * update order status to platform
     * 
     * @param int $transaction_id Transaction ID
     * @param int $status_type Status Type
     * @return bool Status updated or not
     */
    private function _update_tx_status($transaction_id, $status_type, $txn_response) {
        $trnxn_rec = $this->Finance_model->get_transaction($transaction_id);
        if ($status_type == 1) {
            if (!empty($trnxn_rec)) {
                $this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'by your recent transaction', $this->pg_id);
            }
        }
        // CALL Platform API to update transaction
        $data['payment_mode'] = isset($txn_response['mode']) ? $txn_response['mode'] : "";
        $data['txn_id'] = isset($txn_response['pgtxnid']) ? $txn_response['pgtxnid'] : "";
        $data['bank_txn_id'] = isset($txn_response['bank_txn_id']) ? $txn_response['bank_txn_id'] : "";
        $data['txn_amount'] = isset($txn_response['amount']) ? $txn_response['amount'] : 0;
        $data['txn_date'] = isset($txn_response['txn_date']) ? $txn_response['txn_date'] : NULL;
        $data['gate_way_name'] = "vPay";
        $data['transaction_status'] = $status_type;
        $data['transaction_message'] = isset($txn_response['status_msg']) ? $txn_response['status_msg'] : NULL;
        
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
}
