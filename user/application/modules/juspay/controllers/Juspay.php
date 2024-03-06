<?php
class Juspay extends Common_Api_Controller {
    public $success_status = array("CHARGED");
    public $failed_status  = array("AUTHENTICATION_FAILED", "AUTHORIZATION_FAILED");
    public $pending_status = array("PENDING_VBV");
    public $pg_id = 34;

    function __construct() { 
        parent::__construct();
        $this->finance_lang = $this->lang->line("finance");

        if(isset($this->app_config['allow_juspay']) && $this->app_config['allow_juspay']['key_value'] == "0") {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['message']         = "Sorry, Juspay not enabled. please contact admin.";
            $this->api_response();
        }

        $this->JUSPAY_MODE                      = $this->app_config['allow_juspay']['custom_data']['mode'];
        $this->JUSPAY_APIKEY                    = $this->app_config['allow_juspay']['custom_data']['api_key'];
        //$this->JUSPAY_PAYMENT_PAGE_CLIENT_ID    = $this->app_config['allow_juspay']['custom_data']['payment_page_client_id'];
        $this->JUSPAY_CLIENT_ID                 = isset($this->app_config['allow_juspay']['custom_data']['client_id']) ? $this->app_config['allow_juspay']['custom_data']['client_id'] : '';
        $this->ord_prefix                       = $this->app_config['order_prefix']['key_value']; 
        $this->load->helper('payment');
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
        
        $post_data  = $this->post();
        $user_id    = $this->user_id;
        $email      = isset($this->email) ? $this->email : '';
        $phoneno    = isset($this->phone_no) ? $this->phone_no : '1234567890';
        $firstname  = isset($this->user_name) ? $this->user_name : 'User';
        $amount     = $this->input->post("amount");
        $amount     = number_format($amount, 2, '.', '');
        

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
        //GET transaction ID from Database after generating an order
        $txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $post_data['product_info'] = "", $post_data['surl'], $post_data['furl'], $promo_code,$deal);
        
        $gst_data = gst_calculate($amount,$this->app_config);
        $amount = isset($gst_data['amount'])?$gst_data['amount']:$amount;

        if($this->JUSPAY_MODE == "TEST") {
            $endpointUrl = JUSPAY_PAY_TEST_URL;
        } else {
            $endpointUrl = JUSPAY_PAY_PROD_URL;
        }

        $juspayMerchantId       =   $this->JUSPAY_APIKEY; 
        $basicAuth              =   base64_encode($juspayMerchantId);
        $surl                   =   USER_API_URL."juspay/callback"; 
        $payment_page_client_id =   $this->JUSPAY_CLIENT_ID;
        $action                 =   "paymentPage";   

        $is_mobile              =   !empty($this->input->post('is_mobile')) ? $this->input->post('is_mobile') : 0;


        // //$requestPayload = "order_id=".$txnid."&amount=".$amount."&return_url=".$surl;
        // $requestPayload = json_encode([
        //     "order_id" => $this->ord_prefix.$txnid, "amount" => $amount,
        //     "return_url" => $surl, 'customer_id' => $user_id, 
        //     "customer_email" => $email, "customer_phone" => $phoneno,
        //     "payment_page_client_id" => $payment_page_client_id, "action" => $action 
        // ]);

        if(!empty($is_mobile ) && $is_mobile==1) { 
            $first_name             = !empty($this->input->post("first_name")) ? $this->input->post("first_name") : '';
            $last_name              = !empty($this->input->post("last_name")) ? $this->input->post("last_name"): '';
            $requestPayload = json_encode([
                "order_id" => $this->ord_prefix.$txnid, "amount" => $amount,
                "return_url" => $surl, 'customer_id' => $user_id, 
                "customer_email" => $email, "customer_phone" => $phoneno,
                "payment_page_client_id" => $payment_page_client_id, "action" => $action, 'first_name'=>$first_name, 'last_name'=>$last_name
            ]);
        } else {
            //$requestPayload = "order_id=".$txnid."&amount=".$amount."&return_url=".$surl;
            $requestPayload = json_encode([
                "order_id" => $this->ord_prefix.$txnid, "amount" => $amount,
                "return_url" => $surl, 'customer_id' => $user_id, 
                "customer_email" => $email, "customer_phone" => $phoneno,
                "payment_page_client_id" => $payment_page_client_id, "action" => $action
            ]);
        }

        $curl_data['url']           = $endpointUrl;
        $curl_data['header']        = ["Content-Type: application/json","authorization:  Basic $basicAuth", "x-merchantid: $juspayMerchantId"];
        $curl_data['returtransfer'] = true;
        $curl_data['post']          = true;
        $curl_data['post_data']     = $requestPayload;
        $res                        = _curl_exe($curl_data);
        
        if($res) {
            if($res['status'] == "NEW") {
                $resStatus              = $res['status'];
                $sdkPayLoad             = $res['sdk_payload']['payload'];
                $this->db->update(TRANSACTION, array('responce_code'=> $resStatus, 'txn_amount'=>$amount), array('transaction_id'=>$txnid, 'payment_gateway_id' => $this->pg_id));
                //$this->api_response_arry['data'] =  ['redirectUrl'=>$res['payment_links']['web']];
                 if(!empty($is_mobile ) && $is_mobile==1) { 
                    $this->api_response_arry['data'] =  ['sdk_payload'=>$sdkPayLoad];
                } else {
                    $this->api_response_arry['data'] =  ['redirectUrl'=>$res['payment_links']['web']];
                }

                $this->api_response();
            } 
            else {
                $this->api_response_arry['error']           = $res['status'];
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']    = $this->lang->line('deposit_error');
                $this->api_response();
            }
        } else {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']    = $this->lang->line('deposit_error');
            $this->api_response_arry['error']           = $res['status'];
            $this->api_response();
        }
    }

    /**
     * Handle callback
     * @return void
     */
    public function callback_get() {
        $get_data   = $this->get();
        $furl       = FRONT_APP_PATH;
        $txn_id     = $this->input->get('order_id');
        $txn_id     = substr($txn_id,5);

        //echo '<pre>'; print_r($get_data); exit;

        if(!isset($txn_id) && empty($txn_id)){
            redirect($furl, 'location', 301);
        }
        if(isset($get_data['status']) && in_array($get_data['status'],$this->failed_status)) {
            $this->failed_transaction($get_data, $txn_id);
        }
        if(!$this->input->get() || !isset($get_data['signature']) || !isset($get_data['signature_algorithm'])) {
            redirect($furl, 'location', 301);
        }
        $this->load->model("finance/Finance_model");
        if (LOG_TX) {
            $test_data = json_encode($get_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }
        
        //$txn_id     =  $get_data['order_id'];
        $txn_info   = $this->Finance_model->get_single_row('transaction_status,surl,furl,transaction_id,order_id',TRANSACTION, array('transaction_id' => $txn_id));

        if(empty($txn_info)) { 
            redirect($furl, 'location', 301);
        }else if($txn_info['transaction_status'] == "1"){
            redirect($txn_info['surl'], 'location', 301);
        }else if($txn_info['transaction_status'] == "2"){
            redirect($txn_info['furl'], 'location', 301);
        }
         $this->checkTransactionAndRedirect($txn_info, $txn_id); 
    }

    /**
     * Handle failed case
     * @param array $post_data
     * @param int $txn_id
     * @return void
     */
    public function failed_transaction($post_data, $txn_id) {
        $furl = FRONT_APP_PATH;
        $this->load->model("finance/Finance_model");

        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }

        $txn_info = $this->Finance_model->get_single_row('order_id,transaction_status,surl,furl,transaction_id',TRANSACTION, array('transaction_id' => $txn_id));
        if ($txn_info) {
            $ord_info = $this->Finance_model->get_single_row('promo_code_earning_id',PROMO_CODE_EARNING, array('order_id' => $txn_info['order_id']));
            $code_arr = array("is_processed" => "2");
            if($ord_info) {
                $this->Finance_model->update_promo_code_earning_details($code_arr, $ord_info["promo_code_earning_id"]);
            }
        }
        if(empty($txn_info)){
            redirect($furl, 'location', 301);
        }else if($txn_info['transaction_status'] == "1"){
            redirect($txn_info['surl'], 'location', 301);
        }else if($txn_info['transaction_status'] == "2"){
            redirect($txn_info['furl'], 'location', 301);
        }
        $this->checkTransactionAndRedirect($txn_info, $txn_id);
    }

    /**
     * Transaction Status
     * @param int $txnid
     * @param array $config
     * @return array
     */
    private function get_juspay_txn_status($txnid) {   
        $merchant_id    =   $this->JUSPAY_APIKEY; 
        $transactionId  =   $txnid;

        if(!isset($merchant_id) || empty($merchant_id)) {
            redirect($furl, 'location', 301);
        }
        if(!isset($transactionId) || empty($transactionId)) {
            redirect($furl, 'location', 301);
        }


        if($this->JUSPAY_MODE == "TEST") {
            $endpointUrl = JUSPAY_ORDER_TEST_URL;
        } else {
            $endpointUrl = JUSPAY_ORDER_TEST_URL;
        }
        
        $phonepe_status_url =   $endpointUrl.$transactionId;
        $juspayMerchantId       =   $this->JUSPAY_APIKEY; 
        $basicAuth              =   base64_encode($juspayMerchantId);
        
        $curl_data['url']           = $phonepe_status_url;
        $curl_data['header']        = ["Content-Type: application/x-www-form-urlencoded","authorization:  Basic $basicAuth", "x-merchantid: $juspayMerchantId"];
        $curl_data['returtransfer'] = true;
        $response                   = _curl_exe($curl_data);
        return $response;
    }

    /**
     * update order status to platform
     * 
     * @param int $transaction_id
     * @param int $status_type 
     * @param array $pg_response
     * @return bool  
     */
    private function _update_tx_status($transaction_id, $status_type, $pg_response) {
        $trnxn_rec = $this->Finance_model->get_transaction($transaction_id);
        if ($status_type == 1) {
            if (!empty($trnxn_rec)) {
                $this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'by your recent transaction', $this->pg_id);
            }
        }
        $data['payment_mode']           = isset($pg_response['payment_method_type']) ? $pg_response['payment_method_type'] : "";
        $data['mid']                    = isset($pg_response['merchant_id']) ? $pg_response['merchant_id'] : "";
        $data['txn_id']                 = isset($pg_response['txn_id']) ? $pg_response['txn_id'] : "";
        $data['bank_txn_id']            = isset($pg_response['txn_uuid']) ? $pg_response['txn_uuid'] : "";
        $data['txn_amount']             = isset($pg_response['amount']) ? $pg_response['amount'] : 0;
        $data['txn_date']               = isset($pg_response['date_created']) ? date("Y-m-d H:i:s",strtotime($pg_response['date_created'])) : NULL;
        $data['gate_way_name']          = "Juspay";
        $data['is_checksum_valid']      = "1";
        $data['transaction_status']     = $status_type;
        $data['transaction_message']    = isset($pg_response['message']) ? $pg_response['message'] : "";
        $data['currency']               = isset($pg_response['currency']) ? $pg_response['currency'] : "";
        $res            = $this->Finance_model->update_transaction($data, $transaction_id);
        $order_detail   = $this->Finance_model->get_single_row("user_id,real_amount", ORDER, array("order_id" => $trnxn_rec["order_id"]));
        $user_data      = $this->Finance_model->get_single_row("user_name,email", USER, array("user_id" => $order_detail["user_id"]));

        // When Transaction has been failed , order status will also become fails
        if ($status_type == 2) {
            $sql = "UPDATE " . $this->db->dbprefix(ORDER) . " AS O
                    INNER JOIN " . $this->db->dbprefix(TRANSACTION) . " AS T ON T.order_id = O.order_id
                    SET O.status = T.transaction_status
                    WHERE T.transaction_id = $transaction_id";

            $this->db->query($sql);
            $tmp = array();
            $tmp["notification_type"]           = 42;
            $tmp["source_id"]                   = 0;
            $tmp["notification_destination"]    = 7; //  Web, Push, Email
            $tmp["user_id"]                     = $order_detail["user_id"];
            $tmp["to"]                          = $user_data["email"];
            $tmp["user_name"]                   = $user_data["user_name"];
            $tmp["added_date"]                  = format_date();
            $tmp["modified_date"]               = format_date();
            $tmp["subject"]                     = "Deposit amount Failed to credit";
            $input                              = array("amount" => $order_detail["real_amount"]);
            $tmp["content"]                     = json_encode($input);
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($tmp);
        }
        if ($res) {
            return $trnxn_rec;
        }
        return FALSE;
    }

    /**
     * Check transaction status and redirect
     * @param array $txn_info
     * @param int $txn_id
     * @return void
    */ 
    private function checkTransactionAndRedirect($txn_info, $txn_id) {
        $txn_data       = $this->get_juspay_txn_status($this->ord_prefix.$txn_id);
        $pg_response    = $txn_data;
        $txnid                  = $txn_info['transaction_id'];
        $transaction_details    = $this->Finance_model->get_transaction_info($txnid);

        if(isset($txn_data['payment_method_type']) && in_array($txn_data['status'],$this->success_status)) {
            $real_amount = $transaction_details['real_amount']+$transaction_details['tds'];
            if($pg_response['amount'] == $real_amount) {
                $transaction_record = $this->_update_tx_status($txnid, 1, $pg_response);
                redirect($transaction_record['surl'], 'location', 301);
            } else {
                redirect($txn_info['furl'], 'location', 301);
            }
        } else if(isset($txn_data['status']) && in_array($txn_data['status'],$this->failed_status)) {
            $transaction_record     =   $this->_update_tx_status($txnid, 2, $pg_response);
            // Redirect to Failure URL
            redirect($transaction_record['furl'], 'location', 301); 
        }else {
            $purl = str_replace('failure','pending',$transaction_details['furl']);
            redirect($purl, 'location', 301);
        }
    }

    /**
     * Handle callback
     * @return void
     */
    public function webhook_post() {
        $response  = $this->post();
        log_message('error',' == jusypay webhook == '.json_encode($response));

        $furl = FRONT_APP_PATH;
        if (empty($response)) {
            return false;
        }
        $this->load->model("finance/Finance_model");

        if (LOG_TX) { 
            $this->db->insert(TEST,array('data' => json_encode($response),'data_type'=>3,'added_date' => format_date())); 
        }

        $txnid = substr($response['content']['order']['order_id'], 5);
        $transaction_details = $this->Finance_model->get_transaction_info($txnid);

        if (!empty($transaction_details)) {
            $furl = $transaction_details['furl'];
        }

         //check txnid already exist
         if(!empty($transaction_details) && $transaction_details['transaction_status'] == 1) {
            return true;
        }

        $transaction_info = $this->is_valid_transaction($txnid);
        if (!$transaction_info) {
            return false;
        }

        $pg_response['payment_method_type']    = isset($response['order']['payment_method_type']) ? $response['order']['payment_method_type'] : "";
        $pg_response['mid']                    = isset($response['order']['merchant_id']) ? $response['order']['merchant_id'] : "";
        $pg_response['txn_id']                 = isset($response['order']['txn_id']) ? $response['order']['txn_id'] : "";
        $pg_response['bank_txn_id']            = isset($response['order']['txn_uuid']) ? $response['order']['txn_uuid'] : "";
        $pg_response['txn_amount']             = isset($response['order']['amount']) ? $response['order']['amount'] : 0;
        $pg_response['txn_date']               = isset($response['order']['date_created']) ? date("Y-m-d H:i:s",strtotime($response['order']['date_created'])) : NULL;
        $pg_response['transaction_message']    = isset($response['order']['message']) ? $response['order']['message'] : "";
        $pg_response['currency']               = isset($response['order']['currency']) ? $response['order']['currency'] : "";

        if(isset($response['order']['payment_method_type']) && in_array($response['order']['status'],$this->success_status)) {
            $txnid                         = $transaction_details['transaction_id'];
            $real_amount = $transaction_details['real_amount']+$transaction_details['tds'];
            if($response['order']['amount'] == $real_amount) {
                $transaction_record = $this->_update_tx_status($txnid, 1, $pg_response);
            } else {
                return false;
            }
        } else if(isset($response['order']['status']) && $response['order']['status'] == $this->failed_status) {
            $transaction_record     =   $this->_update_tx_status($txnid, 2, $pg_response);
            return false;
        } else {
            return true;
        }
        return true;
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

     /**
     * Handle callback
     * @return void
     */
    public function wdl_webhook_post() {
        $response  = $this->post();
        log_message('error',' == jusypay webhook == '.json_encode($response));

        $furl = FRONT_APP_PATH;
        if (empty($response)) {
            return false;
        }
        $this->load->model("finance/Finance_model");

        if (LOG_TX) { 
            $this->db->insert(TEST,array('data' => json_encode($response),'data_type'=>3,'added_date' => format_date())); 
        }

        $this->load->model("juspay_model","jm");
        $txnid = substr($response['info']['merchantOrderId'], 3);
        $txn_check = $this->jm->check_txn_info($txnid);

        $user_id = $txn_check['user_id'];
        $user_info = $this->jm->get_user_details($user_id);

        $notify_data = array(
            "user_id" => $user_id,
            "amount"=>$txn_check['winning_amount'],
            "email"=>$user_info['email'],
            "first_name"=>$user_info['first_name'],
            "order_id"=> $txn_check['order_id'],
            "transaction_id"=>$txn_check['transaction_id']
        );
        $notify_data['pg_id'] = 34;

        if($txn_check)
        {
            $pending_status = 3;
            $fail_status = 4;
            $success_status = 5;

            if(!in_array($txn_check['t_status'],[0,3]) && $txn_check['o_status']!=0)
            {
                return true;
            }

            $jp_success_status = ["FULFILLMENTS_SUCCESSFUL"];
            $jp_fail_status    = ["FULFILLMENTS_FAILURE"];
            $this->load->library('Juspay_payout');
            $JP = new Juspay_payout($this->app_config['auto_withdrawal']['custom_data']);
            $cross_check_status = $JP->get_transfer_status("JP_".$txnid);

            if(in_array($cross_check_status['status'], $jp_success_status)) {
                $updated_data["gate_way_name"]         = "Juspay Payout";
                $updated_data["transaction_message"]   = isset($cross_check_status['fulfillments'][0]['transactions'][0]['responseMessage']) ? $cross_check_status['fulfillments'][0]['transactions'][0]['responseMessage'] : '';
                $updated_data["txn_amount"]            = $cross_check_status['amount'];
                $updated_data["bank_txn_id"]           = isset($cross_check_status['fulfillments'][0]['id']) ? $cross_check_status['fulfillments'][0]['id'] : '';
                $updated_data["transaction_status"]    = $success_status;
                $updated_data["payment_mode"]          = isset($cross_check_status['fulfillments'][0]['transactions'][0]['fulfillmentMethod']) ? $cross_check_status['fulfillments'][0]['transactions'][0]['fulfillmentMethod'] : '';
                $updated_data["txn_id"]                = $txn_check['transaction_id'];
                
                $custom_data = $this->Finance_model->get_single_row('custom_data', ORDER,["order_id"=>$txn_check['order_id']]);
                // error_log("\n\n".format_date().'before : '.$custom_data.'<br>',3,'/var/www/html/cron/application/logs/payout.log');
                $custom_data = json_decode($custom_data['custom_data'],true);
                $custom_data['jp_ifsc_code'] = isset($cross_check_status['fulfillments'][0]['beneficiaryDetails'][0]['details']['ifsc']) ? $cross_check_status['fulfillments'][0]['beneficiaryDetails'][0]['details']['ifsc'] : '';
                $custom_data['jp_ac_number'] = isset($cross_check_status['fulfillments'][0]['beneficiaryDetails'][0]['details']['account']) ?  $cross_check_status['fulfillments'][0]['beneficiaryDetails'][0]['details']['account'] : '';
                //$custom_data['cf_beneficiary_id'] = $cross_check_status['data']['transfer']['beneId'];
                $custom_data = json_encode($custom_data);
                // error_log("\n\n".format_date().'after '.$order_custom_data.'<br>',3,'/var/www/html/cron/application/logs/payout.log');
                
                $order_arr = array(
                    "custom_data"=>$custom_data,
                    "status"=>1,
                    "order_id"=>$txn_check['order_id']
                );
                $this->Finance_model->update_ord($order_arr);

                $notify_data["status"] = $success_status;
                $notify_data["reason"] =  'success';
                $this->Finance_model->update_transaction($updated_data, $txn_check['transaction_id']);
            }
            elseif(in_array($cross_check_status['status'], $jp_fail_status)) {
                    $notify_data["status"]              = $fail_status;
                    $notify_data["transaction_status"]  = $fail_status;
                    $notify_data["operation"]           = "add";
                    $notify_data["reason"]              = "";//$cross_check_status['message'];
            }else {
                $notify_data["status"]              = $pending_status;
                $notify_data["transaction_status"]  = $pending_status;
                $notify_data["reason"]              = $cross_check_status['status'];
                $this->Finance_model->update_transaction(["transaction_status"=>$pending_status,"txn_id"=>$txn_check['transaction_id']], $txn_check['transaction_id']);
            }

            //echo '<pre>'; print_r($notify_data); exit;
            if(in_array($notify_data['status'],[1,2,4,5]))
            {
                $this->notify_user($notify_data);
                $user_cache_key = "user_balance_".$user_id;
                $this->delete_cache_data($user_cache_key);
            }
            if(isset($result['error']))
            {
                throw new Exception($result['reason']);
            }
        }
    }

    //send notification
    public function notify_user($notify_data)
    {
        $fail_status = 4;
        $success_status = 5;
        //quite critical case please handle carefully, if we mark failure then money will be added back to user account.
        if($notify_data['status'] == $fail_status)
        {
            $net_winning = $this->Finance_model->get_single_row('JSON_UNQUOTE(JSON_EXTRACT(custom_data, "$.net_winning")) AS net_winning', ORDER, array('user_id' => $notify_data['user_id'],'order_id'=>$notify_data['order_id']));
            if(!empty($notify_data) && isset($notify_data['amount']) && isset($net_winning['net_winning']))
            {
                $this->Finance_model->update_user_balance($notify_data['user_id'],["winning_amount"=>$notify_data['amount'],"net_winning"=>$net_winning['net_winning'], 'source'=>8], 'add');
            }
            $user_cache_key = "user_balance_".$notify_data['user_id'];
            $this->delete_cache_data($user_cache_key);
            $this->Finance_model->update_order_status($notify_data['order_id'],2,'',$notify_data['reason']);
            $this->Finance_model->update_transaction(["transaction_status"=>$notify_data['transaction_status']], $notify_data['transaction_id']);
        }
        $user_info = $this->Finance_model->get_user_detail_by_id($notify_data['user_id']);
        
        $msg_content = array(
            "amount"    => $notify_data['amount'],
            "reason"    =>  $notify_data['reason'],
            "user_id"   => $notify_data['user_id'],
            "cash_type" => "0",
            "plateform" => "1",
            "source"    => "7",
            "source_id" => "0",
            "date_added"=> isset($notify_data['date_added']) ? $notify_data['date_added'] : ''
        );
        if(isset($notify_data['pg_id']) && $notify_data['pg_id'] == 34) {
            $msg_content["payment_option"] = 'Juspay';
        } else {
            $msg_content["payment_option"] = 'Cashfree';
        }

        $notify_arr = array();

        if($notify_data['status'] == $success_status) {
            $notify_arr["notification_type"] = 25; // 25-ApproveWithdrawRequest
            $notify_arr["subject"] = $this->lang->line("withdraw_email_approve_subject");                
        }
        if($notify_data['status'] == $fail_status) {
            $notify_arr["notification_type"] = 26; // 26-RejectWtihdrawRequest
            $notify_arr["subject"] = $this->lang->line("withdraw_email_reject_subject");
        }

        $today = format_date();
        $notify_arr["source_id"] = 0;
        $notify_arr["notification_destination"] = 7; //  Web, Push, Email
        $notify_arr["user_id"] =  $notify_data['user_id'];
        $notify_arr["to"] =  $notify_data['email'];
        $notify_arr["user_name"] = $notify_data['first_name'];
        $notify_arr["deviceIDS"] = isset($user_info['device_ids']) ? $user_info['device_ids'] : '' ;
        $notify_arr["ios_device_ids"] = isset($user_info['ios_device_ids']) ? $user_info['ios_device_ids'] : '';
        $notify_arr["added_date"] = $today;
        $notify_arr["modified_date"] = $today;
        $notify_arr["content"] = json_encode($msg_content);
        
        $this->load->model('notification/Notify_nosql_model');
        $this->Notify_nosql_model->send_notification($notify_arr); 
        return true;
    }

    function update_txn_status_post() {
        //exit("Called=====update_txn_status_post");
        $this->form_validation->set_rules('orderid', 'OrderId', 'required');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $txn_id     = substr($this->input->post('orderid'), 5);
        $this->load->model("finance/Finance_model");
        $furl = FRONT_APP_PATH;
        $txn_info   = $this->Finance_model->get_single_row('transaction_status,surl,furl,transaction_id,order_id',TRANSACTION, array('transaction_id' => $txn_id));
        if(empty($txn_info)) {
            $this->api_response_arry['data'] =  ['url'=>$furl, 'status'=>2];
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }else if($txn_info['transaction_status'] == "1"){
            $this->api_response_arry['data'] =  ['url'=>$txn_info['surl'], 'status'=>1];
            $this->api_response();
        }else if($txn_info['transaction_status'] == "2"){
            $this->api_response_arry['data'] =  ['url'=>$txn_info['furl'], 'status'=>2];
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $res = $this->checkTransactionAndRedirectMobile($txn_info, $txn_id); 
        $url = isset($res['url']) ? $res['url']:''; 
        $this->api_response_arry['data'] =  ['url'=>$url, 'status'=>$res['status']];
        $this->api_response();
    }
    
    function checkTransactionAndRedirectMobile($txn_info, $txn_id) {  
        $txn_data       = $this->get_juspay_txn_status($this->ord_prefix.$txn_id);
        $pg_response    = $txn_data;
        $txnid                  = $txn_info['transaction_id'];
        $transaction_details    = $this->Finance_model->get_transaction_info($txnid);
        if(isset($txn_data['payment_method_type']) && in_array($txn_data['status'],$this->success_status)) {
            $real_amount = $transaction_details['real_amount']+$transaction_details['tds'];
            if($pg_response['amount'] == $real_amount) {
                $transaction_record = $this->_update_tx_status($txnid, 1, $pg_response);
                //redirect($transaction_record['surl'], 'location', 301);
                $res_arr['url'] = $transaction_record['surl'];
                $res_arr['status'] = 1;
                return $res_arr;
            } else {
                //redirect($txn_info['furl'], 'location', 301);
                $res_arr['url'] = $txn_info['furl'];
                $res_arr['status'] = 2;
                return $res_arr;
            }
        } else if(isset($txn_data['status']) && in_array($txn_data['status'],$this->failed_status)) {
            $transaction_record     =   $this->_update_tx_status($txnid, 2, $pg_response);
            // Redirect to Failure URL
            //redirect($transaction_record['furl'], 'location', 301); 
            $res_arr['url'] = $txn_info['furl'];
            $res_arr['status'] = 2;
            return $res_arr;
        }else { 
            $purl = str_replace('failure','pending',$transaction_details['furl']);
            //redirect($purl, 'location', 301);
            $res_arr['url'] = $purl;
            $res_arr['status'] = 0;
            return $res_arr; 
        }
    }
}