<?php

class Mpesa extends Common_Api_Controller {

    public $mode = 'TEST';
    public $consumer_key = '';
    public $consumer_secret = '';
    public $shortcode = '';
    public $passkey = '';
    public $country_code = '254';
    public $curr = 'INR';

    public $prefix = 'IFNTSY';
    public $pg_id = 3;

    function __construct() {
        parent::__construct();

        if(isset($this->app_config['allow_mpesa']) && $this->app_config['allow_mpesa']['key_value'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, Mpesa not enabled. please contact admin.";
            $this->api_response();
        }
        $this->finance_lang = $this->lang->line("finance");

        $this->mode                   = $this->app_config['allow_mpesa']['custom_data']['mode'];
        $this->consumer_key           = $this->app_config['allow_mpesa']['custom_data']['consumer_key'];
        $this->consumer_secret        = $this->app_config['allow_mpesa']['custom_data']['consumer_secret'];
        $this->shortcode              = $this->app_config['allow_mpesa']['custom_data']['shortcode'];
        $this->passkey                = $this->app_config['allow_mpesa']['custom_data']['passkey'];
        $this->country_code           = $this->app_config['phone_code']['key_value'];
        $this->curr                   = $this->app_config['currency_abbr']['key_value']; // VALUES ARE "INR","USD"
        $this->prefix                       = $this->app_config['order_prefix']['key_value']; // should be exact 5 later order prefix

        // $this->pout_mode                   = $this->app_config['allow_mpesa']['custom_data']['mode'];
        // $this->pout_consumer_key           = $this->app_config['allow_mpesa']['custom_data']['consumer_key'];
        // $this->pout_consumer_secret        = $this->app_config['allow_mpesa']['custom_data']['consumer_secret'];
        // $this->shortcode              = $this->app_config['allow_mpesa']['custom_data']['shortcode'];
        // $this->passkey                = $this->app_config['allow_mpesa']['custom_data']['passkey'];
    }

    public function get_token()
    {
        $this->load->helper('payment');
        $mod = ($this->mode=="TEST") ? "sandbox":"api";
        $url = str_replace('{{mode}}',$mod,MPESA_ACCESS_TOKEN_URL);
        $header = base64_encode($this->consumer_key . ':' . $this->consumer_secret);
        $curl_data = array(
            "url"=>$url,
            "header"=>array('Authorization: Basic ' .$header),
            "header_flag"=>false,
            "ssl_flag"=>false,
            "returtransfer"=>1
        );
        $response = _curl_exe($curl_data);
        return $response['access_token'];
    }
    public function php_curl($url,$post_data)
    {
        
        $curl_data = array(
            "url"=>$url,
            "header"=>array(
                    'Authorization: Bearer '.$this->get_token(),
                    'Content-Type: application/json'
            ),
            "post"=>0,
            "post_data"=>json_encode($post_data),
            "returtransfer"=>1,
            "header_flag"=>false,
            "ssl_flag"=>false,
        );
        $response = _curl_exe($curl_data);
        return $response;
    }
    
    /**
     * @method deposit cash
     * @uses funtion to add real cash to user
     * */
    public function deposit_post() {
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'callback_decimal_numeric|callback_validate_deposit_amount');
        $this->form_validation->set_rules('furl', 'furl', 'required');
		$this->form_validation->set_rules('surl', 'surl', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();

        $user_id = $this->user_id;
        $email = isset($this->email) ? $this->email : '';
        $phoneno = $this->app_config['phone_code']['key_value'].$this->phone_no;

        if (!$phoneno) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("phone_no_not_found");
            $this->api_response();
        }

        $product_info = SITE_TITLE . ' deposit via Mpesa';
        $this->load->model("finance/Finance_model");
        $promo_code = array();
        if (!empty($post_data['promo_code'])) {
            $promo_code = $this->validate_promo($post_data);
        }

        $deal = array();
        if (!empty($post_data['deal_id'])) {
            $deal = $this->validate_deal($post_data);
        }

        $_POST['payment_gateway_id'] = $this->pg_id;
        $amount = $post_data['amount'];
		// $amount = number_format($amount, 2, '.', '');
        $surl = $post_data['surl'];
		$furl = $post_data['furl'];

        $timestamp = format_date('today', "YmdHms");
		$txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $product_info, $surl, $furl, $promo_code, $deal);
        $txnid = $this->prefix.$txnid;

        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        $mod = ($this->mode=="TEST") ? "sandbox":"api";
        $url = str_replace('{{mode}}',$mod,MPESA_REQUEST_URL);

        $curl_post_data = array(
            //Fill in the request parameters with valid values (all parameters are required)
            'BusinessShortCode'     => $this->shortcode,
            'Password'              => $password,
            'Timestamp'             => $timestamp,
            'TransactionType'       => MPESA_TRANSACTION_TYPE,
            'Amount'                => $amount,
            'PartyA'                => $phoneno, //'254705378676',
            'PartyB'                => $this->shortcode,
            'PhoneNumber'           => $phoneno, //'254705378676',
            'CallBackURL'           => USER_API_URL . 'mpesa/callback',
            'AccountReference'      => $txnid,
            'TransactionDesc'       => 'Account deposit'
        );
        // error_log("\n\n\n".format_date().'### MPESA REQUEST  : '.json_encode($curl_post_data),3,'/var/www/html/cron/application/logs/cashierpay.log');
        $response = $this->php_curl($url,$curl_post_data);
        // error_log("\n\n\n".format_date().'### Mpesa ACK  : '.json_encode($response),3,'/var/www/html/cron/application/logs/cashierpay.log');
        
        if (!empty($response)) {
            if (isset($response['ResponseCode'])) {
                if ($response['ResponseCode'] === 0 || $response['ResponseCode'] === '0') {
                        $update_data = array("txn_id" => $response['MerchantRequestID']);
                        $this->Finance_model->update_transaction($update_data, substr($txnid,5));
                        $this->api_response_arry['message'] = $response['CustomerMessage'];
                        $this->api_response_arry['data'] = $response;
                        $this->api_response();
                } else {
                    $is_mark_cancel = 1;
                    $message = $response['CustomerMessage'];
                }
            } else {
                $is_mark_cancel = 1;
                $message = $response['errorMessage'];
            }
        } else {
            $is_mark_cancel = 1;
            $message = $this->lang->line("error_occured");
        }
        if ($is_mark_cancel && $is_mark_cancel == 1) {
            $message = array("transaction_message" => 'Unable to initiate transaction');
            $transaction_record = $this->_update_tx_status(substr($txnid,5), 2, $message);
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $message;
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
                $this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, '', 3);
            }
        }
        // CALL Platform API to update transaction
        $data = array();
        $data['transaction_status'] = $status_type;
        if (!empty($update_data)) {
            $data = array_merge($data, $update_data);
        }

        if ($trnxn_rec['transaction_status'] == 0) {
            $res = $this->Finance_model->update_transaction($data, $transaction_id);
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

    /* callback function for mpesa return for both success and failed transaction */

    public function callback_post()
    {
        $response = $_POST;
        
        if (!$response){
            return false;
        }

        if(!empty($response['Body']) && isset($response['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'])) 
        {
           $this->paycallback($response);
        }
        else if(!empty($response['Result']) && isset($response['Result']['ResultParameters']['ResultParameter'][12]['Value']))
        {
            $this->paymentcron($response);
        }
        return true;
    }

    public function paycallback($callbackData) {
        if (LOG_TX){
            error_log("\n\n\n".format_date().'### MPESA CALL DATA  : '.json_encode($callbackData),3,'/var/www/html/cron/application/logs/cashierpay.log');
        }

        if ($callbackData) {
            $resultCode = isset($callbackData['Body']['stkCallback']['ResultCode']) ? $callbackData['Body']['stkCallback']['ResultCode'] : '';
            $resultDesc = isset($callbackData['Body']['stkCallback']['ResultDesc']) ? $callbackData['Body']['stkCallback']['ResultDesc'] : '';
            $merchantRequestID = isset($callbackData['Body']['stkCallback']['MerchantRequestID']) ? $callbackData['Body']['stkCallback']['MerchantRequestID'] : '';
            $checkoutRequestID = isset($callbackData['Body']['stkCallback']['CheckoutRequestID']) ? $callbackData['Body']['stkCallback']['CheckoutRequestID'] : '';
            
            $amount = isset($callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value']) ? $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'] : '';
            $mpesaReceiptNumber = isset($callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value']) ? $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'] : '';
            //$balance=$callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][2]['Value'];
            $transactionDate = isset($callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value']) ? date("Y-m-d H:i:s", strtotime($callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'])) : '';
            $phoneNumber = isset($callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value']) ? $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'] : '';

            if ($merchantRequestID != '') {
                $this->load->model("finance/Finance_model");
                $txnid = $merchantRequestID;
                $txnData = $this->Finance_model->get_transaction_by_txn_id($txnid);
            }
            if ($txnData) {

                if ($resultCode === 0 || $resultCode === '0') {
                    $updateTransactionData = array(
                        'txn_amount' => $amount,
                        'txn_date' => $transactionDate,
                        'pg_order_id' => $mpesaReceiptNumber,
                        'responce_code' => $resultCode,
                        'transaction_message' => $resultDesc
                    );
                    $transaction_record = $this->_update_tx_status($txnData['transaction_id'], 1, $updateTransactionData);

                } else {
                    $updateTransactionData = array(
                        'responce_code' => $resultCode,
                        'transaction_message' => $resultDesc
                    );
                    $transaction_record = $this->_update_tx_status($txnData['transaction_id'], 2, $updateTransactionData);
                }
            }
        }
    }

    /**
     * payment cron callback method
     */
    public function paymentcron($callbackData)
    {
        if (LOG_TX){
            error_log("\n\n\n".format_date().'### MPESA STATUS CRON CALLBACK DATA  : '.json_encode($callbackData),3,'/var/www/html/cron/application/logs/cashierpay.log');
        }
        

        $callbackData = $callbackData['Result'];
        $result = $callbackData['ResultParameters']['ResultParameter'];
        $pg_order_id = $result[12]['Value'];
        if ($pg_order_id != '') {
            $this->load->model("finance/Finance_model");
            $txnData = $this->Finance_model->get_single_row('*',TRANSACTION,array('pg_order_id'=>$pg_order_id));
        }
        if(isset($callbackData['ResultCode']) && ($callbackData['ResultCode']==0 || $callbackData['ResultCode']=='0'))
        {
            $amount = $result[10]['Value'] ? $result[10]['Value'] : '';
            $transactionDate = $result[9]['Value'] ? date("Y-m-d H:i:s", strtotime($result[9]['Value'])) : '';
            $mpesaReceiptNumber = $result[12]['Value'] ? $result[12]['Value'] : '';

            $updateTransactionData = array(
                'txn_amount' => $amount,
                'txn_date' => $transactionDate,
                'pg_order_id' => $mpesaReceiptNumber,
                'responce_code' => $callbackData['ResultCode'],
                'transaction_message' => $callbackData['ResultDesc']
            );
            // error_log("\n\n\n".format_date().'### Mpesa payment cron callback should mark success  : ',3,'/var/www/html/cron/application/logs/cashierpay.log');
            $transaction_record = $this->_update_tx_status($txnData['transaction_id'], 1, $updateTransactionData);

        }else 
        {
            $updateTransactionData = array(
                'responce_code' => $callbackData['ResultCode'],
                'transaction_message' => $callbackData['ResultDesc']
            );
            // error_log("\n\n\n".format_date().'### Mpesa payment cron callback should mark fail  : ',3,'/var/www/html/cron/application/logs/cashierpay.log');
            $transaction_record = $this->_update_tx_status($txnData['transaction_id'], 2, $updateTransactionData);
        }
    }

    // ----------------------------------------- PAYOUT METHODS START ----------------------------------------------------------

    public function get_mpesa_token_post()
    {
        $this->load->helper('payment');
        $mod = ($this->mode=="TEST") ? "sandbox":"api";
        $url = str_replace('{{mode}}',$mod,MPESA_ACCESS_TOKEN_URL);
        $consumer_key = $this->app_config['auto_withdrawal']['custom_data']['c_id'];
        $consumer_secret = $this->app_config['auto_withdrawal']['custom_data']['s_id'];
        $header = base64_encode($consumer_key . ':' . $consumer_secret);
        $curl_data = array(
            "url"=>$url,
            "header"=>array('Authorization: Basic ' .$header),
            "header_flag"=>false,
            "ssl_flag"=>false,
            "returtransfer"=>1
        );
        $response = _curl_exe($curl_data);
        echo $response['access_token'];die;
    }

    public function payout_callback_post()
    {
        $response = $_POST;
        // error_log("\n\n\n".format_date().'###>>>>>>>>#### Mpesa payout response'.json_encode($response).'  : ',3,'/var/www/html/cron/application/logs/payout.log');
        $get_data = $_GET;
        // echo $response['Result']['ResultParameters']['ResultParameter'][1]['Value'];die;
        
        $this->load->model("Mpesa_model","mm");
        if(isset($get_data['tx']) && $get_data['tx']!='')
        {
            $txn_check = $this->mm->check_txn_info($get_data);
            if($txn_check)
            {
                $pending_status = 3;
                $fail_status = 4;
                $success_status = 5;

                if(!in_array($txn_check['t_status'],[0,3]) && $txn_check['o_status']!=0)
                {
                    return true;
                }
                //success case
                if($response['Result']['ResultCode']==0 || $response['Result']['ResultCode']=='0')
                {
                    //check status
                    // $txn_check['status'] = $success_status;
                    // add_data_in_queue($txn_check,'payout');
                    $res_data = $response['Result']['ResultParameters']['ResultParameter'];
                    $update_transaction_data = array();
                    $update_transaction_data["gate_way_name"]           = "Mpesa Payout";
                    $update_transaction_data["transaction_message"]     = $response['Result']['ResultDesc'];
                    $update_transaction_data["txn_amount"]              = $res_data[0]['Value'];
                    $update_transaction_data["bank_txn_id"]             = $res_data[1]['Value'];
                    $update_transaction_data["txn_id"]                  = $txn_check['transaction_id'];
                    $update_transaction_data["transaction_status"]      = $success_status;
                    $this->mm->_common_update_payout_tx_status($txn_check, $update_transaction_data);

                }else{
                    //mark failure
                    $txn_check['response'] = $response;
                    $txn_check['action'] = 'mpesa';
                    $this->load->helper('queue_helper');
                    $txn_check['status'] = $fail_status;
                    add_data_in_queue($txn_check,'payout');
                }

            }
        }

    }


    // this function is for testing purpose only
    public function get_mpesa_txn_status_post()
    {
        $response = $_POST;
        
        $response['Result']['ResultParameters']['ResultParameter'][1]['Value'];
        $this->load->helper('payment_helper');
        $config=array(
            "mode" => 'PROD',
            "password" => '@Africarocks254%',
            "key" => "M8GQFGWLhL6gfEPSdoQ8VfaoO98IAAWD",
            "secret" => "LPS0ArfwfcU4GBf0",
            "short_code" => "3034405",
            "initiator" => "b2capi",
          );
        //   print_r($config);die;
        // echo $response['Result']['ResultParameters']['ResultParameter'][1]['Value'];die;
        $txn_status  = get_mpesa_txn_status($response['Result']['ResultParameters']['ResultParameter'][1]['Value'],$config);
        print_r($txn_status);die;
    }

    
}   
