<?php

use ReceiptValidator\GooglePlay\Validator as PlayValidator;
use ReceiptValidator\iTunes\AbstractResponse;
use ReceiptValidator\iTunes\Validator as iTunesValidator;

class Inapp_purchase extends Common_Api_Controller {


    public $pg_id = 9;
    function __construct() {
        parent::__construct();

        if(isset($this->app_config['inapp_purchase']) && $this->app_config['inapp_purchase']['key_value'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, inapp purchase not enabled. please contact admin.";
            $this->api_response();
        }

        $this->G_APPLICATION_NAME     =$this->app_config['inapp_purchase']['custom_data']['g_app_name'];
        $this->SHARED_SECRET    =$this->app_config['inapp_purchase']['custom_data']['shared_secret'];
    }

    /**
     * @method deposit cash
     * @uses function to add real and bonus cash to user
     * */
    public function deposit_post() {
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|callback_decimal_numeric|callback_validate_deposit_amount');
        $this->form_validation->set_rules('coins', 'Coin', 'required');
        $this->form_validation->set_rules('currency', 'Currency', 'required');
        // $this->form_validation->set_rules('surl', 'surl', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();
        $user_id = $this->user_id;
        $surl = WEBSITE_DOMAIN.'payment-method?status=success';
        $furl = WEBSITE_DOMAIN.'payment-method?status=failure';

        $product_info = SITE_TITLE . ' Inapp purchase';

        $email = isset($this->email) ? $this->email : '';
        $phoneno = isset($this->phone_no) ? $this->phone_no : '1234567890';
        $firstname = isset($this->user_name) ? $this->user_name : 'User';
        $real_amount = $post_data['amount'];
        $real_amount = number_format($real_amount, 2, '.', '');
        $coin_amount = $post_data['coins'];
        $amount = array(
            "real_amount"=>$real_amount,
            "coin_amount"=>$coin_amount
        );
        $amount = json_encode($amount);
        $this->load->model("finance/Finance_model");

        // promocode will not be applied

        $promo_code = array(); 
        // if (!empty($post_data['promo_code'])) {
        //     $promo_code = $this->validate_promo($post_data);
        // }

        //deal offer will not be applied 

        $deal = array();
        // if (!empty($post_data['deal_id'])) {
        //     $deal = $this->validate_deal($post_data);
        // }

        $_POST['payment_gateway_id'] = $this->pg_id;
        // GET transaction ID from Database after generating an order
        $txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $product_info, $surl, $furl, $promo_code, $deal);

        $checkSum = "";
        $paramList = array();

        //Create an array having all required parameters for creating checksum.
        $paramList["order_id"] = $txnid;
        $paramList["user_id"] = $user_id;
        $paramList["amount"] = $real_amount;
        $paramList["coins"] = $coin_amount;
        $paramList["email"] = $email;
        $paramList["mobile"] = $phoneno;
        // $paramList["MID"] = PAYTM_MERCHANT_MID;
        // $paramList["INDUSTRY_TYPE_ID"] = PAYTM_INDUSTRY_TYPE_ID;
        // $paramList["CHANNEL_ID"] = PAYTM_CHANNEL_ID;
        // $paramList["WEBSITE"] = PAYTM_MERCHANT_WEBSITE;
        // $paramList["CALLBACK_URL"] = PAYTM_CALLBACK_URL;

        //Here checksum string will return by getChecksumFromArray() function.
        // $checkSum = getChecksumFromArray($paramList, PAYTM_MERCHANT_KEY);
        // $paramList["CHECKSUMHASH"] = $checkSum;

        if (LOG_TX) {
            $test_data = json_encode($paramList);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "1"));
        }

        $this->data['paramList'] = $paramList;
        // Post variable to view with HASH and auto submit form there
        // $this->data['data'] = $this->load->view('paytm/deposit', $this->data, true);
        $this->api_response_arry['data'] = $paramList;
        $this->api_response();
    }

    /**
     * update transaction status and order table 
     */
    private function _update_tx_status($transaction_id, $status_type, $update_data = array()) {
        $trnxn_rec = $this->Finance_model->get_transaction($transaction_id);
        // print_r($trnxn_rec);exit;
        if (!empty($trnxn_rec) && $status_type == 1) {   // GET order_id from transaction ID
            
                $this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'by your recent transaction', 9);
            
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

    
    /**
     * function for receipt validation for both ios and android
     */
    public function validate_receipt_post() {
        
        $this->form_validation->set_rules('deviceType', 'Device Type', 'trim|required');
        $this->form_validation->set_rules('purchaseToken', 'Purchase Token', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();
        // echo '<pre>';print_r($_POST);die;
        $this->load->model("finance/Finance_model");
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "2"));
        }
        
        if (empty($post_data['purchaseToken'])) {
            redirect(BASE_APP_PATH, 'location', 301);
        }


        // $this->load->model("inapp_purchase_model");
        // $user_details = $this->inapp_purchase_model->get_single_row('device_type', USER,["user_id"=>$this->user_id]);
        $data = array();
        $data['number']= $post_data['purchaseToken'];
        $user_balance_cache_key = 'user_balance_' . $this->user_id;
        $user_status = $this->Finance_model->get_single_row('transaction_status', TRANSACTION,["transaction_id"=>$post_data['order_id']]);
        
        if($user_status['transaction_status']==1 || $user_status['transaction_status']==2){
            $this->api_response_arry['service_name']          = "inapp_purchase/validate_receipt";
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['message']         = $this->lang->line("already_update");
                    $this->api_response();
        }

        if($post_data['deviceType']==1){
            $data['card_id']= $post_data['productId'];
            $data['package_name']= $post_data['packageName'];
            //ANDROID RECEIPT VALIDATION
            $validate = $this->validate_tx_andriod($data);

            if (!empty($validate) && !empty($validate['pg_order_id'])) {
                // print_r($validate);exit;
                if($validate['purchaseState']==2){
                    $this->api_response_arry['service_name']          = "inapp_purchase/validate_receipt";
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['message']         = $this->lang->line("pending_txn");
                    $this->api_response();
                }else{                
    
                    if ($validate["status"] == 1 && $validate['purchaseState']==0) {   // have to change condition and put by $validation variable.
                        // print_r($updateTransactionData);exit;
                        
                        $updateTransactionData = array(
                            'txn_amount' => (isset($post_data['txn_amount'])) ? $post_data['txn_amount'] : 0,
                            'responce_code' => (isset($post_data['purchaseToken'])) ? $post_data['purchaseToken'] : 0,
                            'txn_id' => $post_data['order_id'],
                            'txn_date' => (isset($validate['txn_date'])) ? $validate['txn_date'] : 0,
                            'pg_order_id' => (isset($validate['pg_order_id'])) ? $validate['pg_order_id'] : 0,
                            'bank_name' => (isset($validate['bank_name'])) ? $validate['bank_name'] : 0,
                            // 'product_id' => $post_data['productId'],
                            // 'status' => $post_data['status']
                        );

                        $transaction_record = $this->_update_tx_status($post_data['order_id'], 1, $updateTransactionData);
                        $this->delete_cache_data($user_balance_cache_key);
                        $this->api_response_arry['service_name']          = "inapp_purchase/validate_receipt";
                        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                        $this->api_response_arry['message']         = $this->lang->line("purchase_success");
                        $this->api_response();
                    } else {
                        $transaction_record = $this->_update_tx_status($post_data['order_id'], 2, $updateTransactionData);
                        $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['service_name']          = "inapp_purchase/validate_receipt";
                        $this->api_response_arry['global_error']         = $this->lang->line("purchase_error");
                        $this->api_response();
                    }
                }
            } else {
                $transaction_record = $this->_update_tx_status($post_data['order_id'], 2, $updateTransactionData);
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['service_name']          = "inapp_purchase/validate_receipt";
                $this->api_response_arry['global_error']         = $this->lang->line("purchase_error");
                $this->api_response();
            }
            
        }
        else if($post_data['deviceType']==2){
            //IOS  RECEIPT VALIDATION
            $validate = $this->validate_tx_ios($data);
            // print_r($validate['response']['txn_id']);exit;
            if(!empty($validate['response'])){
                $transaction_record = $this->_update_tx_status($validate['response']['txn_id'], 1,$validate['response']);
                $this->delete_cache_data($user_balance_cache_key);
                    $this->api_response_arry['service_name']          = "inapp_purchase/validate_receipt";
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['message']         = $this->lang->line("purchase_success");
                    $this->api_response();
            }
            else {
                $transaction_record = $this->_update_tx_status($validate['response']['txn_id'], 1,$validate['response']);
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['service_name']          = "inapp_purchase/validate_receipt";
                $this->api_response_arry['global_error']         = $this->lang->line("purchase_error");
                $this->api_response();
            }
        }
        else{
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']         = $this->lang->line("wrong_device");
            $this->api_response();
        }
        
       
    }
    /**
     * android receipt validation method by library
     */
    private function validate_tx_andriod($data) {
        $receiptBase64Data = $data['number'];
        $res = array('bank_txn_id' => $receiptBase64Data);
        $configLocation = ROOT_PATH . 'framwork-iap-27c0efc480e8.json'; //service account credentials
        try {
            $googleClient = new \Google_Client();
            $googleClient->setScopes([\Google_Service_AndroidPublisher::ANDROIDPUBLISHER]);
            $googleClient->setApplicationName($this->G_APPLICATION_NAME);
            $googleClient->setAuthConfig($configLocation);
            $googleAndroidPublisher = new \Google_Service_AndroidPublisher($googleClient);
            $subscription = $googleAndroidPublisher->purchases_products->get($data['package_name'], $data['card_id'], $receiptBase64Data); //cricjam_101
            // print_r($subscription);exit;
            if (!empty($subscription) && !empty($subscription->orderId)) {
                $res['txn_date'] = date("Y-m-d H:i:s", ($subscription->purchaseTimeMillis / 1000));
                $res['pg_order_id'] = $subscription->orderId;
                $res['purchaseState'] = $subscription->purchaseState;
                $res['bank_name'] = $data['package_name'];
                $res['product_id'] = $data['card_id'];
                $res['status'] = 1;
            } else {
                $res['message'] = "Error validating transaction.";
                $res['status'] = 2;
            }
        } catch (Exception $e) {
            $res['message'] = $e->getMessage();
        }
        return $res;
    }

    /**
     * IOS receipt validator from library
     */
    private function validate_tx_ios($data) {
        $receiptBase64Data = $data['number'];
        $res = array('bank_txn_id' => $receiptBase64Data);
        if (ENVIRONMENT == 'production') {
            $validator = new iTunesValidator(iTunesValidator::ENDPOINT_PRODUCTION);
        } else {
            $validator = new iTunesValidator(iTunesValidator::ENDPOINT_SANDBOX);
        }
        $validator->setSharedSecret($this->SHARED_SECRET);
        try {
            $response = $validator->setReceiptData($receiptBase64Data)->validate(); // use setSharedSecret() if for recurring subscriptions
            
            if ($response->isValid()) {
                $res = $response->getReceipt();
                // echo "<pre>";print_r($data);exit;
                // 'product_name' => ($res['in_app'][0]['product_id']) ? $res['in_app'][0]['product_id'] : '',
                $updateTransactionData = array(
                'bank_name' => (isset($res['bundle_id'])) ? $res['bundle_id'] : '',
                'bank_txn_id' =>  ($res['in_app'][0]['transaction_id']) ? $res['in_app'][0]['transaction_id'] : '',
                'txn_amount' => $_POST['txn_amount'],
                'txn_id' => $_POST['order_id'],
                'txn_date' => ($res['in_app'][0]['purchase_date']) ? $res['in_app'][0]['purchase_date'] : '',
                'mid' => $_POST['purchaseToken'],
                );
                $output['response']= $updateTransactionData;
            } else {
                $output['message'] = $response->getResultCode();
            }
        } catch (Exception $e) {
            $output['message'] = $e->getMessage();
        }
        return $output;
    }


}
