<?php

use ReceiptValidator\GooglePlay\Validator as PlayValidator;
use ReceiptValidator\iTunes\AbstractResponse;
use ReceiptValidator\iTunes\Validator as iTunesValidator;

class Subscription extends Common_Api_Controller {


    public $pg_id = 11;
    function __construct() {
        parent::__construct();
        $is_subscription = isset($this->app_config['allow_subscription'])?$this->app_config['allow_subscription']['key_value']:0;
        $is_coin = isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
        if(!$is_subscription || !$is_coin){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['status'] = FALSE;
                $this->api_response_arry['message'] = $this->lang->line("subscription_module_enable");
                $this->api_response();
        }

        $this->load->model("Subscription_model",'sm');
    }

    /**
     * @method deposit cash
     * @uses function to add real and bonus cash to user
     * */
    public function deposit_post() {
        $this->form_validation->set_rules('subscription_id', 'Subscription ID', 'trim|required');
        $this->form_validation->set_rules('type', 'Subscription type', 'trim|required|in_list[1,2]');
        // $this->form_validation->set_rules('surl', 'surl', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();

        $check_subscription = $this->sm->check_subscription($post_data);
        if(!$check_subscription)
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']         = $this->lang->line('no_subscription_package');
            $this->api_response();
        }

        $check_already_subscriped = $this->sm->check_already_subscribed($post_data);
        if($check_already_subscriped==1)
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']         = $this->lang->line('already_subscribed');
            $this->api_response();
        }else if($check_already_subscriped==2)
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']         = $this->lang->line('other_package_subscribed');
            $this->api_response();
        }

        $user_id = $this->user_id;
        $surl = WEBSITE_DOMAIN.'payment-method?status=success';
        $furl = WEBSITE_DOMAIN.'payment-method?status=failure';
        $product_info = SITE_TITLE . ' Inapp subscription';
        $email = isset($this->email) ? $this->email : '';
        $phoneno = isset($this->phone_no) ? $this->phone_no : '1234567890';
        $firstname = isset($this->user_name) ? $this->user_name : 'User';
        $real_amount = $check_subscription['amount'];
        $real_amount = number_format($real_amount, 2, '.', '');
        $coin_amount = $check_subscription['coins'];
        $amount = array(
            "amount"=>$real_amount,
            "coins"=>$coin_amount,
            "subscription_id"=>$post_data['subscription_id'],
            "type"=>$post_data['type'],
            "subscribed_date" => format_date(),
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
        $paramList['subscription_id'] = $check_subscription['subscription_id'];
        if($post_data['type']==1)
        {
            $paramList['product_id'] = $check_subscription['android_id'];
        }else{
            $parmaList['product_id'] = $check_subscription['ios_id'];
        }
        $paramList['type'] = $post_data['type'];

        if (LOG_TX) {
            $test_data = json_encode($paramList);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "1"));
        }

        $this->data['paramList'] = $paramList;
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
            
                $res = $this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'In app Subscription', 11);
                if($res)
                {
                    $this->sm->update_subscription($trnxn_rec["order_id"],$update_data);
                }
        }
        
        // CALL Platform API to update transaction
        $data = array();
        $data['transaction_status'] = $status_type;
        if (!empty($update_data)) {
            $data = array_merge($data, $update_data);
            unset($data['new_exp_date']);
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
            $this->api_response_arry['service_name']          = "subscription/validate_receipt";
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
                    $this->api_response_arry['service_name']          = "subscription/validate_receipt";
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['message']         = $this->lang->line("pending_txn");
                    $this->api_response();
                }else{                
    
                    if ($validate["status"] == 1 && $validate['purchaseState']==0) {   // have to change condition and put by $validation variable.
                        // print_r($updateTransactionData);exit;
                        
                        $updateTransactionData = array(
                            'responce_code' => (isset($post_data['purchaseToken'])) ? $post_data['purchaseToken'] : 0,
                            'txn_id' => $post_data['order_id'],
                            'txn_date' => (isset($validate['txn_date'])) ? $validate['txn_date'] : 0,
                            'pg_order_id' => (isset($validate['pg_order_id'])) ? $validate['pg_order_id'] : 0,
                            'bank_name' => (isset($validate['bank_name'])) ? $validate['bank_name'] : 0,
                            'new_exp_date' => $validate['txn_expiry_date'] ? $validate['txn_expiry_date']:null,
                        );

                        $transaction_record = $this->_update_tx_status($post_data['order_id'], 1, $updateTransactionData);
                        $this->delete_cache_data($user_balance_cache_key);
                        $this->api_response_arry['service_name']          = "subscription/validate_receipt";
                        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                        $this->api_response_arry['message']         = $this->lang->line("purchase_success");
                        $this->api_response();
                    } else {
                        $transaction_record = $this->_update_tx_status($post_data['order_id'], 2, $updateTransactionData);
                        $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['service_name']          = "subscription/validate_receipt";
                        $this->api_response_arry['global_error']         = $this->lang->line("purchase_error");
                        $this->api_response();
                    }
                }
            } else {
                $transaction_record = $this->_update_tx_status($post_data['order_id'], 2, $updateTransactionData);
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['service_name']          = "subscription/validate_receipt";
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
                    $this->api_response_arry['service_name']          = "subscription/validate_receipt";
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                    $this->api_response_arry['message']         = $this->lang->line("purchase_success");
                    $this->api_response();
            }
            else {
                $transaction_record = $this->_update_tx_status($validate['response']['txn_id'], 1,$validate['response']);
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['service_name']          = "subscription/validate_receipt";
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
        $receiptBase64Data = $data['number']; // purchase token
        //card_id is product_id
        //package_name is app_name from config
        $res = array('bank_txn_id' => $receiptBase64Data);
        $configLocation = ROOT_PATH . $this->app_config['allow_subscription']['custom_data']['json_name']; //service account credentials
        try {
            $googleClient = new \Google_Client();
            $googleClient->setScopes([\Google_Service_AndroidPublisher::ANDROIDPUBLISHER]);
            $googleClient->setApplicationName($this->app_config['allow_subscription']['custom_data']['app_name']);
            $googleClient->setAuthConfig($configLocation);
            $googleAndroidPublisher = new \Google_Service_AndroidPublisher($googleClient);
            // print_r($data);exit;
            $subscription = $googleAndroidPublisher->purchases_subscriptions->get($data['package_name'], $data['card_id'], $receiptBase64Data);
            // echo "try";print_r($subscription);exit;

            if (is_null($subscription)) {
                $res['message'] = $this->lang->line('validation_error');
                $res['txn_expiry_date'] = NULL;
            } elseif (isset($subscription['error']['code'])) {
                $res['message'] = $this->lang->line('validation_error');
                $res['txn_expiry_date'] = NULL;
            } elseif (!isset($subscription['expiryTimeMillis'])) {
                $res['message'] = $this->lang->line('validation_error');
                $res['txn_expiry_date'] = NULL;
            } else {
                $res['txn_expiry_date'] = date("Y-m-d H:i:s", ($subscription->expiryTimeMillis / 1000));
            }

            $res['txn_id'] = $subscription->orderId;
            $original_transaction_id = strstr($subscription->orderId, '..', true);
            if ($original_transaction_id === FALSE) {
                $res['mid'] = $subscription->orderId;
            } else {
                $res['mid'] = $original_transaction_id;
            }
            $res['bank_name'] = $data['package_name'];
            $res['currency'] = $subscription->priceCurrencyCode;
            $res['original_transaction_date'] = date("Y-m-d H:i:s", ($subscription->startTimeMillis / 1000));
            $res['txn_date'] = date("Y-m-d H:i:s", ($subscription->startTimeMillis / 1000));
            $res['pg_order_id'] = $subscription->orderId;
            $res['purchaseState'] = $subscription->purchaseState;
            $res['txn_amount'] = ($subscription->priceAmountMicros)/1000000;
            $res['product_id'] = $data['card_id'];
            $res['status'] = 1;
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
        $validator->setSharedSecret(SHARED_SECRET);
        try {
            $response = $validator->setReceiptData($receiptBase64Data)->validate(); // use setSharedSecret() if for recurring subscriptions
            
            if ($response->isValid()) {

                $purchase = $response->getLatestReceiptInfo()[0];
                if (!is_null($purchase)) {
                    if ($purchase->getExpiresDate() != null) {
                        $res['txn_expiry_date'] = $purchase->getExpiresDate()->toDateTimeString();
                    } else {
                        $res['txn_expiry_date'] = NULL;
                    }

                    $res['mid'] = $purchase->getOriginalTransactionId();
                    //$res['product_id'] = $purchase->getProductId();
                    $res['txn_id'] = $purchase->getTransactionId();
                    if ($purchase->getPurchaseDate() != null) {
                        $res['txn_date'] = $purchase->getPurchaseDate()->toDateTimeString();
                    }
                    if ($purchase->getOriginalPurchaseDate() != null) {
                        $res['original_transaction_date'] = $purchase->getOriginalPurchaseDate()->toDateTimeString();
                    }
                } else {
                    $res['txn_expiry_date'] = NULL;
                }
                $result = $response->getReceipt();
                // echo "<pre>";print_r($data);exit;
                // 'product_name' => ($res['in_app'][0]['product_id']) ? $res['in_app'][0]['product_id'] : '',
                // $updateTransactionData = array(
                $res['bank_name'] = (isset($result['bundle_id'])) ? $result['bundle_id'] : '';
                $res['bank_txn_id'] =  ($result['in_app'][0]['transaction_id']) ? $result['in_app'][0]['transaction_id'] : '';
                $res['txn_date'] = ($result['in_app'][0]['purchase_date']) ? $result['in_app'][0]['purchase_date'] : '';
                $res['txn_amount'] = $_POST['txn_amount'];
                $res['txn_id'] = $_POST['order_id'];
                $res['mid'] = $_POST['purchaseToken'];
                // );
                $output['response']= $updateTransactionData;
            } else {
                $output['message'] = $response->getResultCode();
            }
        } catch (Exception $e) {
            $output['message'] = $e->getMessage();
        }
        return $output;
    }

    /**
     * method for subscription cancel
     */
    public function cancel_subscription_post()
    {
        $this->form_validation->set_rules('subscription_id', 'Subscription ID', 'trim|required');
        // $this->form_validation->set_rules('type', 'Subscription type', 'trim|required|in_list[1,2]');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();

        $unsubscribe = $this->sm->cancel_subscription($post_data);
        if($unsubscribe)
        {
            $tmp = array();
            $user_detail = $this->sm->get_single_row('user_id, user_name, phone_no, email', USER, array("user_id" => $this->user_id));
            $notify_data["amount"] = $unsubscribe["amount"];
            $notify_data["name"] = $unsubscribe['name'];
            $tmp["notification_type"] = 438; // 438-cancel subscription
            $tmp["notification_destination"] = 1; //  Web
            $tmp["user_id"] = $this->user_id;
            $tmp["to"] = $user_detail['email'];
            $tmp["user_name"] = $user_detail['user_name'];
            $tmp["added_date"] = format_date();
            $tmp["modified_date"] = format_date();
            $tmp["content"] = json_encode($notify_data);
            $tmp["source_id"] = $post_data['subscription_id'];
            $tmp["subject"] = "Cancel subscription";
            
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($tmp);

            $this->api_response_arry['response_code']           = rest_controller::HTTP_OK;
            $this->api_response_arry['service_name']            = "inapp_subscription/cancel_subscription";
            $this->api_response_arry['data']                    = $unsubscribe;
            $this->api_response_arry['message']                 = $this->lang->line('package_canceled');
            $this->api_response();
        }else{
            $this->api_response_arry['response_code']           = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['service_name']            = "inapp_subscription/cancel_subscription";
            $this->api_response_arry['global_error']            = $this->lang->line('package_canceled_error');
            $this->api_response();
        }
    }

    /**
     * method to get lilst of package at user end.
     */
    public function get_package_list_post()
    {
        $post = $this->input->post();
        $result = $this->sm->check_subscription($post);

        if($result)
        {
        $this->api_response_arry['service_name']            = "subscription/validate_receipt";
        $this->api_response_arry['response_code']           = rest_controller::HTTP_OK;
        $this->api_response_arry['data']                    = $result;
        $this->api_response_arry['message']                 = $this->lang->line('get_packages');
        $this->api_response();
        }
        $this->api_response_arry['response_code']           = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['global_error']            = $this->lang->line('no_package');
        $this->api_response();
    }


}

//Location : uer/applicatio/modules/subscription/controllers/Subscription.php
?>
