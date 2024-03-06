<?php
 
class Paytm extends Common_Api_Controller {

    public $pg_id = 2;
    function __construct() {
        
        if(isset($this->app_config['allow_paytm']) && $this->app_config['allow_paytm']['key_value'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, Paytm not enabled. please contact admin.";
            $this->api_response();
        }

        parent::__construct();
        
        $this->PG_MODE                = $this->app_config['allow_paytm']['custom_data']['pg_mode'];
        $this->MERCHANT_KEY           = $this->app_config['allow_paytm']['custom_data']['merchant_key'];
        $this->MERCHANT_MID           = $this->app_config['allow_paytm']['custom_data']['merchant_mid'];
        $this->MERCHANT_WEBSITE       = $this->app_config['allow_paytm']['custom_data']['merchant_website'];
        $this->INDUSTRY_TYPE_ID       = $this->app_config['allow_paytm']['custom_data']['industry_type_id'];
        $this->CHANNEL_ID             = $this->app_config['allow_paytm']['custom_data']['channel_id'];
        
        $this->CALLBACK_URL           = USER_API_URL.'paytm/payment_callback';
        
        if($this->PG_MODE=='TEST')
        {
            $this->TXN_URL                = 'https://securegw-stage.paytm.in/order/process';
            $this->ORDER_STATUS_API       = PAYTM_ORDER_STATUS_API_TEST;
        }else{
            $this->TXN_URL                = 'https://securegw.paytm.in/theia/processTransaction';
            $this->ORDER_STATUS_API       = PAYTM_ORDER_STATUS_API_PRO;
        }
        
        $this->load->helper('paytm_helper');
    }

    /**
     * @method deposit cash
     * @uses function to add real and bonus cash to user
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
        $surl = $post_data['surl'];
        $furl = $post_data['furl'];

        $product_info = SITE_TITLE . ' deposit via PayTm';

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
        $_POST['payment_gateway_id'] = $this->pg_id;
        // GET transaction ID from Database after generating an order
        $txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $product_info, $surl, $furl, $promo_code, $deal);

        $gst_data = gst_calculate($amount,$this->app_config);
        $amount = isset($gst_data['amount'])?$gst_data['amount']:$amount;

        $checkSum = "";
        $paramList = array();

        //Create an array having all required parameters for creating checksum.
        $paramList["MID"] = $this->MERCHANT_MID;
        $paramList["ORDER_ID"] = $txnid;
        $paramList["CUST_ID"] = $user_id;
        $paramList["INDUSTRY_TYPE_ID"] = $this->INDUSTRY_TYPE_ID;
        $paramList["CHANNEL_ID"] = $this->CHANNEL_ID;
        $paramList["TXN_AMOUNT"] = $amount;
        $paramList["WEBSITE"] = $this->MERCHANT_WEBSITE;
        $paramList["CALLBACK_URL"] = $this->CALLBACK_URL;
        $paramList["EMAIL"] = $email;
        $paramList["MOBILE_NO"] = $phoneno;

        //Here checksum string will return by getChecksumFromArray() function.
        $checkSum = getChecksumFromArray($paramList, $this->MERCHANT_KEY);
        $paramList["CHECKSUMHASH"] = $checkSum;

        if (LOG_TX) {
            $test_data = json_encode($paramList);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "1"));
        }

        $this->data['paramList'] = $paramList;
        $this->data['url'] = $this->TXN_URL;
        // Post variable to view with HASH and auto submit form there
        $this->data['data'] = $this->load->view('paytm/deposit', $this->data, true);
        $this->api_response_arry['data'] = $this->data['data'];
        $this->api_response();
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
                $this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'by your recent transaction', 2);
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

    /* callback function for paytm return for both success and failed transaction */

    public function payment_callback_post() {
        $paytmChecksum = "";
        $paramList = array();
        $isValidChecksum = "FALSE";

        $post_data = $this->post();
        // echo '<pre>';print_r($_POST);die;
        $this->load->model("finance/Finance_model");
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "2"));
        }

        $paramList = $post_data;
        $paytmChecksum = isset($post_data["CHECKSUMHASH"]) ? $post_data["CHECKSUMHASH"] : ""; //Sent by Paytm pg

        if (empty($paytmChecksum)) {
            redirect(BASE_APP_PATH, 'location', 301);
        }

        //Verify all parameters received from Paytm pg to your application. Like MID received from paytm pg is same as your application’s MID, TXN_AMOUNT and ORDER_ID are same as what was sent by you to Paytm PG for initiating transaction etc.
        $isValidChecksum = verifychecksum_e($paramList, $this->MERCHANT_KEY, $paytmChecksum); //will return TRUE or FALSE string.
       
        if ($isValidChecksum == "TRUE") {

            $updateTransactionData = array(
                'txn_amount' => (isset($post_data['TXNAMOUNT'])) ? $post_data['TXNAMOUNT'] : 0,
                'txn_date' => (isset($post_data['TXNDATE'])) ? date("Y-m-d H:i:s", strtotime($post_data['TXNDATE'])) : format_date(),
                'txn_id' => (isset($post_data['TXNID'])) ? $post_data['TXNID'] : 0,
                'responce_code' => (isset($post_data['RESPCODE'])) ? $post_data['RESPCODE'] : 0
            );

            if ($post_data["STATUS"] == "TXN_SUCCESS") {
                $transaction_details = $this->Finance_model->get_transaction_info($paramList['ORDERID']);
                //For Run Txn Status Query
                // Create an array having all required parameters for status query.
                $txn_pg_amount = number_format($updateTransactionData['txn_amount'], 2, '.', '');
                $real_amount = $transaction_details['real_amount']+$transaction_details['tds'];
                //additional check for amount mismatch
                if($txn_pg_amount == $real_amount){
                    $transaction_record = $this->_update_tx_status($paramList['ORDERID'], 1, $updateTransactionData);
                    redirect($transaction_record['surl'], 'location', 301);
                    //Verify amount & order id received from Payment gateway with your application's order id and amount.
                }else{
                    $transaction_record = $this->_update_tx_status($paramList['ORDERID'], 2, $updateTransactionData);
                    redirect($transaction_record['furl'], 'location', 301);
                }
            } else {
                //log_message('error', 'PayTm Callback Data : ' . format_date() . ' : ' . json_encode($paramList));
                $transaction_record = $this->_update_tx_status($paramList['ORDERID'], 2, $updateTransactionData);
                redirect($transaction_record['furl'], 'location', 301);
            }
        } else {
            redirect(BASE_APP_PATH, 'location', 301);
        }
    }

    /* function for handle s2s(ipn) response */

    public function paytm_s2s_callback_post() {
        $post_data = $this->post();
        //log_message('error', 'Payment Info : ' . format_date() . ' : ' . json_encode($post_data));
    }

    public function payout_callback_post() {
        if($this->input->post()) {
            try {
                $checksum = "";
                $is_valid_checksum = "FALSE";

                $post_data = $this->post();        // echo '<pre>';print_r($_POST);die;
                $checksum = $this->input->get_request_header('x-checksum'); //Sent by Paytm 
                $this->load->model("finance/Finance_model");
                if (LOG_TX) {
                    $post_data_tmp = $post_data;
                    $post_data_tmp['x-checksum'] = $checksum;
                    $post_data_tmp['callback'] = "Callback Data";
                    $test_data = json_encode($post_data_tmp);
                    $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "2"));
                }

                $result 		= isset($post_data['result']) ? $post_data['result'] : array();
               
                $order_unique_id = (isset($result['orderId'])) ? $result['orderId'] : 0;
                if (empty($checksum)) {
                    throw new Exception('Checksum Required orderId = '.$order_unique_id);
                }

                $this->load->helper('paytm_payout_helper');
                //Verify all parameters received from Paytm pg to your application. Like MID received from paytm pg is same as your application’s MID, TXN_AMOUNT and ORDER_ID are same as what was sent by you to Paytm PG for initiating transaction etc.
                $post_data_str = json_encode($post_data, JSON_UNESCAPED_SLASHES);
                $is_valid_checksum = verifySignature($post_data_str, PAYTM_PAYOUT_MERCHANT_KEY, $checksum); //will return TRUE or FALSE string.
            
                if ($is_valid_checksum == "TRUE") {
                    
                    $order_detail = $this->Finance_model->get_single_row('order_id, real_amount, bonus_amount, winning_amount, user_id, source, date_added, withdraw_method', ORDER, array('order_unique_id' => $order_unique_id));
                    
                    if(empty($order_detail)) {
                        throw new Exception('invalid order id');
                    }
                    $paytm_params = array();
                    $paytm_params["orderId"] = $order_unique_id;                  

                    $post_data = json_encode($paytm_params, JSON_UNESCAPED_SLASHES);
                    /*
                    * Generate checksum by parameters we have in body
                    */
                    $checksum = generateSignature($post_data, PAYTM_PAYOUT_MERCHANT_KEY);

                    

                    $x_mid      = PAYTM_PAYOUT_MERCHANT_MID;
                    $x_checksum = $checksum;
                    $headers = array("x-mid: " . $x_mid, "x-checksum: " . $x_checksum);
                    $url = PAYTM_PAYOUT_API_URL."/query";

                    if (LOG_TX) {
                        $test_data_param = $paytm_params;
                        $test_data_param['checksum '] = $checksum;
                        $test_data_param['headers '] = $headers;
                        $test_data_param['url '] = $url;
                        $test_data = json_encode($test_data_param);
                        $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "1"));
                    }

                    
                    $payout_query_response = callPayoutAPI($url, $post_data, $headers);
                    log_message('error', 'Disburse Status​ Query​ ​API - ' . json_encode($payout_query_response));
                    if($payout_query_response) {
                        $status = $payout_query_response['status'];
                        $status_code = $payout_query_response['statusCode'];
                        $status_message = $payout_query_response['statusMessage'];
                        $result = $payout_query_response['result'];

                        $status_code = str_replace('DE_', '', strtoupper($status_code));
                        $status_code    = trim($status_code);
                        $txn_id = (isset($result['paytmOrderId'])) ? $result['paytmOrderId'] : 0;
                        $txn_amount = (isset($result['amount'])) ? $result['amount'] : 0 ;
                        
                        $transaction_status = 0;
                        $status = strtoupper($status);
                        if($status == 'FAILURE') {
                            if(in_array($status_code, array(500, 602, 623, 636, 640, 648, 901))) {
                                $transaction_status = 3;
                            } else {
                                $transaction_status = 4;
                            }
                        }
                        if($status == 'SUCCESS') {
                            $transaction_status = 5;
                        }
                        
                        $update_transaction_data = array(
                            'txn_amount' => $txn_amount,
                            'txn_id' => $txn_id,
                            'responce_code' => $status_code,
                            'transaction_message' => $status_message,
                            'transaction_status' => $transaction_status
                        );
                        $this->Finance_model->update_payout_tx_status($order_detail, $update_transaction_data);
                                                
                    }                    
                } else {
                    throw new Exception('Checksum Verification Failed. Checksum = '.$checksum.' orderId = '.$order_unique_id);
                }
            } catch (Exception $e) {        
                log_message("error", "Paytm Payout Call Back: ".$e->getMessage());
                $this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']		= "Paytm Payout Call Back: ".$e->getMessage();                
                $this->api_response();
                
            }
        } else {
            log_message("error", "Paytm Payout Call Back: ".$this->lang->line('input_invalid_format'));
            $this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']		= "Paytm Payout Call Back: ".$this->lang->line('input_invalid_format');                
            $this->api_response();
        }
    }
}
