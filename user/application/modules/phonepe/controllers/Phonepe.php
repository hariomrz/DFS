<?php
class Phonepe extends Common_Api_Controller {
    public $success_status = array("PAYMENT_SUCCESS");
    public $pg_id = 33;

    function __construct() {
        parent::__construct();
        $this->finance_lang = $this->lang->line("finance");

        if(isset($this->app_config['allow_phonepe']) && $this->app_config['allow_phonepe']['key_value'] == "0") {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status']          = FALSE;
            $this->api_response_arry['message']         = "Sorry, Phonepe not enabled. please contact admin.";
            $this->api_response();
        }

        $this->PHONEPE_MODE             = $this->app_config['allow_phonepe']['custom_data']['mode'];
        $this->PHONE_PAY_MERCHANTID     = $this->app_config['allow_phonepe']['custom_data']['merchent_key'];
        $this->SALT_KEY                 = $this->app_config['allow_phonepe']['custom_data']['salt'];
        $this->KEY_INDEX                 = $this->app_config['allow_phonepe']['custom_data']['key_index'];
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
        $this->form_validation->set_rules('is_mobile', '', '');
        $this->form_validation->set_rules('device_type', '', '');

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

        
        $is_mobile      = $this->input->post("is_mobile");
        $device_type    = strtoupper($this->input->post("device_type"));

        if($this->PHONEPE_MODE == "TEST") {
            $endpoint_url = PHONEPE_PAY_TEST_URL;
            switch($device_type) {
                case "ANDROID":
                    $target_app     = "com.phonepe.simulator";
                    break;
                case "IOS":
                    $target_app     = "PHONEPE";
                    break;
            }

        } else {
            $endpoint_url = PHONEPE_PAY_PROD_URL;
            switch($device_type) {
                case "ANDROID":
                    $target_app     = "com.phonepe.app";
                    break;
                case "IOS":
                    $target_app     = "PHONEPE";
                    break;
            }
        }

        //Prepare the request payload
        // $request_payload = [
        //     'merchantId'                    =>  $this->PHONE_PAY_MERCHANTID, // REQUIRED
        //     'merchantTransactionId'         =>  (string)$txnid, // REQUIRED
        //     'merchantUserId'                =>  (string)$this->user_id, // USER ID WHO IS PAYING // REQUIRED
        //     'amount'                        =>  $amount*100, // REQUIRED
        //     'redirectMode'                  =>  "POST", // REQUIRED
        //     'redirectUrl'                   =>  USER_API_URL ."phonepe/callback?token=".base64_encode($txnid), // REQUIRED
        //     'callbackUrl'                   =>  USER_API_URL ."phonepe/callback?token=".base64_encode($txnid), // REQUIRED
        //     'paymentInstrument'             =>  ["type" => "PAY_PAGE"] // REQUIRED
        // ];

        // Is Mobile Condition
        if($is_mobile == 1) {
            //echo '<pre>'; print_r($this->input->post()); exit;
                 $request_payload = [
                     'merchantId'                    =>  $this->PHONE_PAY_MERCHANTID, // REQUIRED
                     'merchantTransactionId'         =>  (string)$txnid, // REQUIRED
                     'merchantUserId'                =>  (string)$this->user_id, // USER ID WHO IS PAYING // REQUIRED
                     'amount'                        =>  $amount*100, // REQUIRED
                     'redirectMode'                  =>  "POST", // REQUIRED
                     'callbackUrl'                   =>  USER_API_URL."phonepe/callback?token=".base64_encode($txnid), // REQUIRED
                     'mobileNumber'                  =>  $phoneno,
                     'deviceContext'                 =>  ["deviceOS"=>$device_type],
                     'paymentInstrument'             =>  ["type" => "PAY_PAGE", "targetApp" => $target_app] // REQUIRED
                 ];
         } else {
                 //Prepare the request payload
                 $request_payload = [
                     'merchantId'                    =>  $this->PHONE_PAY_MERCHANTID, // REQUIRED
                     'merchantTransactionId'         =>  (string)$txnid, // REQUIRED
                     'merchantUserId'                =>  (string)$this->user_id, // USER ID WHO IS PAYING // REQUIRED
                     'amount'                        =>  $amount*100, // REQUIRED
                     'redirectMode'                  =>  "POST", // REQUIRED
                     'redirectUrl'                   =>  USER_API_URL."phonepe/redirect?token=".base64_encode($txnid), // REQUIRED
                     'callbackUrl'                   =>  USER_API_URL."phonepe/callback?token=".base64_encode($txnid), // REQUIRED
                     'paymentInstrument'             =>  ["type" => "PAY_PAGE"] // REQUIRED
                 ];
         } 
        
        //BASE 64 PAYLOAD                    
        $base64encode_payload = base64_encode(json_encode($request_payload));
        $salt_index         = $this->KEY_INDEX;
        $string             = $base64encode_payload."/pg/v1/pay".$this->SALT_KEY;   
        $sha256             = hash('sha256', $string);
        $finalXHeader       = $sha256.'###'.$salt_index;

         // Response if is_mobile condition = 1
         if($is_mobile == 1) {
            $this->api_response_arry['data'] =  ['bs_key' => $base64encode_payload, 'hash_key' => $finalXHeader, 'txn_id' => $txnid];
            $this->api_response();
        }


        //FINAL REQUEST
        $request['request'] = $base64encode_payload;
       
        
        // MAKE CURL CALL
        $curl_data['url']           = $endpoint_url;
        $curl_data['header']        = ["Content-Type: application/json","X-VERIFY:  $finalXHeader"];
        $curl_data['returtransfer'] = true;
        $curl_data['post']          = true;
        $curl_data['post_data']     = json_encode($request);
        $res                        = _curl_exe($curl_data);

        

        // PROCESS RESPONSE DATA
        if($res) {
            if($res['code'] == "PAYMENT_INITIATED") {
                $merchantId             = $res['data']['merchantId'];
                $merchantTransactionId  = $res['data']['merchantTransactionId'];
                $resStatus              = $res['success'];
                $this->db->update(TRANSACTION, array('mid'=>$merchantId, 'txn_id'=>$merchantTransactionId, 'responce_code'=> $resStatus, 'txn_amount'=>$amount, 'transaction_message'=>$res['message']), array('transaction_id'=>$txnid, 'payment_gateway_id' => $this->pg_id));
                $this->api_response_arry['data'] =  ['redirectUrl'=>$res['data']['instrumentResponse']['redirectInfo']['url']];
                $this->api_response();
            } 
            else {
                $this->api_response_arry['error']           = $res['code'];
                $this->api_response_arry['message']         = $res['message'];
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']    = $this->lang->line('deposit_error');
                $this->api_response();
            }
        } else {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']    = $this->lang->line('deposit_error');
            $this->api_response();
        }
    }

     /**
     * Handle callback for mobile
     * @return void
     */
    public function callback_post() {
        $post_data  = $this->post();
        $txn_id     = base64_decode($this->input->get('token'));
        $furl       = FRONT_APP_PATH;
        
        if(isset($post_data['success']) && $post_data['success'] == false) {
            $res = $this->failed_transaction($post_data, $txn_id, 1);
            //$this->api_response_arry['data'] =  ['url'=>$res['url'], 'status'=>$res['status']];
            $this->api_response_arry['data'] = ['status'=>$res['status']];
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        if(!$this->input->post() || !isset($post_data['merchantId']) || !isset($post_data['transactionId']) || !isset($post_data['checksum'])) {
            //redirect($furl, 'location', 301);
            //$this->api_response_arry['data'] =  ['url'=>$furl, 'status'=>2];
            $this->api_response_arry['data'] = ['status'=>2];
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        
        $this->load->model("finance/Finance_model");
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            //$this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }
        
        $txn_id     =  isset($post_data['merchantOrderId'])?$post_data['merchantOrderId']:$post_data['transactionId'];
        $txn_info   = $this->Finance_model->get_single_row('transaction_status,surl,furl,transaction_id,order_id',TRANSACTION, array('transaction_id' => $txn_id));
        
        
        if(empty($txn_info)) {
            //redirect($furl, 'location', 301);
            //$this->api_response_arry['data'] =  ['url'=>$txn_info['furl'], 'status'=>2];
            $this->api_response_arry['data'] = ['status'=>2];
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }else if($txn_info['transaction_status'] == "1"){
            //redirect($txn_info['surl'], 'location', 301);
            //$this->api_response_arry['data'] =  ['url'=>$txn_info['surl'], 'status'=>1];
            $this->api_response_arry['data'] = ['status'=>1];
            $this->api_response();
        }else if($txn_info['transaction_status'] == "2"){
            //redirect($txn_info['furl'], 'location', 301);
            //$this->api_response_arry['data'] =  ['url'=>$txn_info['furl'], 'status'=>2];
            $this->api_response_arry['data'] = ['status'=>2];
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        // type: (1 callback,2 redirect)
        $res = $this->checkTransactionAndRedirect($txn_info, $txn_id,1);
        $url = isset($res['url']) ? $res['url']:''; 
        $this->api_response_arry['data'] =  ['url'=>$url, 'status'=>$res['status']];
        $this->api_response();
    }


    
    /**
     * Handle callback
     * @return void
     */
    public function redirect_post(){
        $post_data = $this->post();
        
        $furl = FRONT_APP_PATH;
        $token = $this->input->get('token');
        if(!isset($token) && empty($token)){
            redirect($furl, 'location', 301);
        }
        
        $txn_id = base64_decode($this->input->get('token'));
        
        if(isset($post_data['success']) && $post_data['success'] == false) {
            $res = $this->failed_transaction($post_data, $txn_id,2);
            $url = isset($res['url']) ? $res['url'] : '';
            redirect($url, 'location', 301);
        }
        if(!$this->input->post() || !isset($post_data['merchantId']) || !isset($post_data['transactionId']) || !isset($post_data['checksum'])) {
            redirect($furl, 'location', 301);
        }
        
        $this->load->model("finance/Finance_model");
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            //$this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }
        
        $txn_id     =  isset($post_data['merchantOrderId'])?$post_data['merchantOrderId']:$post_data['transactionId'];
        $txn_info   = $this->Finance_model->get_single_row('transaction_status,surl,furl,transaction_id,order_id',TRANSACTION, array('transaction_id' => $txn_id));
        
        if(empty($txn_info)) {
            redirect($furl, 'location', 301);
        }else if($txn_info['transaction_status'] == "1"){
            redirect($txn_info['surl'], 'location', 301);
        }else if($txn_info['transaction_status'] == "2"){
            redirect($txn_info['furl'], 'location', 301);
        }
        $res = $this->checkTransactionAndRedirect($txn_info, $txn_id,2); 
        
        $url = isset($res['url']) ? $res['url']:'';
        redirect($url, 'location', 301); 
    }

    function update_txn_status_post() { 
        $this->form_validation->set_rules('orderid', 'OrderId', 'required');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        } 
        //$txn_id     = base64_decode($this->input->post('orderid'));
        $txn_id     = $this->input->post('orderid');
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
        $res = $this->checkTransactionAndRedirect($txn_info, $txn_id); 
        $url = isset($res['url']) ? $res['url']:''; 
        $this->api_response_arry['data'] =  ['url'=>$url, 'status'=>$res['status']];
        $this->api_response(); 
    }

    /**
     * Handle failed case
     * @param array $post_data
     * @param int $txn_id
     * @return void
     */
    public function failed_transaction($post_data, $txn_id, $type=1) {
        $res_arr = ['status'=>2, 'type'=>$type, 'url'=>'']; // type: (1=callback,2=redirect)
        $furl = FRONT_APP_PATH;
        $this->load->model("finance/Finance_model");

        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }

        $txn_info = $this->Finance_model->get_single_row('order_id,transaction_status,surl,furl,transaction_id',TRANSACTION, array('txn_id' => $txn_id));
        if ($txn_info) {
            $ord_info = $this->Finance_model->get_single_row('promo_code_earning_id',PROMO_CODE_EARNING, array('order_id' => $txn_info['order_id']));
            $code_arr = array("is_processed" => "2");
            if($ord_info) {
                $this->Finance_model->update_promo_code_earning_details($code_arr, $ord_info["promo_code_earning_id"]);
            }
        }
       
        if(empty($txn_info)){
            //redirect($furl, 'location', 301);
            $res_arr['url'] = $txn_info['furl'];
            $res_arr['status'] = 2;
            return $res_arr;
        }else if($txn_info['transaction_status'] == "1"){
            //redirect($txn_info['surl'], 'location', 301);
            $res_arr['url'] = $txn_info['surl'];
            $res_arr['status'] = 1;
            return $res_arr;
        }else if($txn_info['transaction_status'] == "2"){
             //redirect($txn_info['furl'], 'location', 301);
             $res_arr['url'] = $txn_info['surl'];
             $res_arr['status'] = 1;
             return $res_arr;
        }
         // type: (1 callback,2 redirect)
         return $this->checkTransactionAndRedirect($txn_info, $txn_id,$type);
         //$url = isset($res['url'])?$res['url']:'';
         //redirect($url, 'location', 301); 
    }

    /**
     * Transaction Status
     * @param int $txnid
     * @param array $config
     * @return array
     */
    private function get_phonepe_txn_status($txnid, $config, $type=1) {
        $res_arr = ['status'=>2, 'type'=>$type, 'url'=>'']; // type: (1=callback,2=redirect)   
        $merchant_id    =   $config['m_id']; 
        $transactionId  =   $txnid;

        if(!isset($merchant_id) || empty($merchant_id)) {
             //redirect($furl, 'location', 301);
             $res_arr['url'] = $furl;
             $res_arr['status'] = 2;
             return $res_arr;
        }
        if(!isset($transactionId) || empty($transactionId)) {
            //redirect($furl, 'location', 301);
            $res_arr['url'] = $furl;
            $res_arr['status'] = 2;
            return $res_arr;
        }


        if($this->PHONEPE_MODE == "TEST") {
            $endpoint_url = PHONEPE_STATUS_TEST_URL;
        } else {
            $endpoint_url = PHONEPE_STATUS_PROD_URL;
        }

        // CHECK PAYMENT STATUS
        $phonepe_status_url =   $endpoint_url.$merchant_id.'/'.$transactionId;

        $salt_index     =   $this->KEY_INDEX;
        $string         =   "/pg/v1/status/".$merchant_id.'/'.$transactionId.''.$config['m_salt'];   
        $sha256         =   hash('sha256', $string);
        $finalXHeader   =   $sha256.'###'.$salt_index;

        
        $curl_data['url']           = $phonepe_status_url;
        $curl_data['header']        = ["Content-Type: application/json", "X-VERIFY:  $finalXHeader", "X-MERCHANT-ID: $merchant_id"];
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
        $data['payment_mode']           = isset($pg_response['payment_mode']) ? $pg_response['payment_mode'] : "";
        $data['mid']                    = isset($pg_response['mid']) ? $pg_response['mid'] : "";
        $data['txn_id']                 = isset($pg_response['txn_id']) ? $pg_response['txn_id'] : "";
        $data['bank_name']             = isset($pg_response['bank_name']) ? $pg_response['bank_name'] : "";
        $data['bank_txn_id']            = isset($pg_response['bank_txn_id']) ? $pg_response['bank_txn_id'] : "";
        $data['txn_amount']             = isset($pg_response['txn_amount']) ? $pg_response['txn_amount'] : 0;
        $data['txn_date']               = format_date();
        $data['gate_way_name']          = "Phonepe";
        $data['is_checksum_valid']      = "1";
        $data['transaction_status']     = $status_type;
        $data['transaction_message']    = isset($pg_response['message']) ? $pg_response['message'] : "";
        
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
    private function checkTransactionAndRedirect($txn_info, $txn_id, $type=1) {
        $config = array(
            "m_id"      =>  $this->PHONE_PAY_MERCHANTID,
            "m_salt"    =>  $this->SALT_KEY
        );
        
        $purl = str_replace("failure","pending",$txn_info['furl']);
        $txn_data = $this->get_phonepe_txn_status($txn_id,$config, $type);
        $res_arr = ['status'=>2, 'type'=>$type, 'url'=>$purl]; // type: (1 callbacl,2redirect)

        
        $pg_response = array();
        if(isset($txn_data['data'])){
            $phonepe_arr = $txn_data['data'];
            $type = isset($phonepe_arr['paymentInstrument']['type'])?$phonepe_arr['paymentInstrument']['type']:'';
            $pg_response = array(
                'payment_mode' => $type,
                'mid' => isset($phonepe_arr['merchantId']) ? $phonepe_arr['merchantId'] : "",
                'txn_id' => isset($phonepe_arr['transactionId']) ? $phonepe_arr['transactionId'] : "",
                'txn_amount' => isset($phonepe_arr['amount']) ? $phonepe_arr['amount']:0,
                'gate_way_name'=> "Phonepe",
                'txn_date'=> format_date(),
                'message'=> isset($txn_data['message'])?$txn_data['message']:''
            );

            $payment_optn = isset($phonepe_arr['paymentInstrument']) ? $phonepe_arr['paymentInstrument'] : array();
            // card
            if(!empty($type) && $type == 'CARD')
            {
                $pg_response['bank_name'] = isset($payment_optn['bankId']) ? $payment_optn['bankId'] : "";
                $pg_response['bank_txn_id'] = isset($payment_optn['pgTransactionId']) ? $payment_optn['pgTransactionId'] : "";
                $pg_response['pg_order_id'] = isset($payment_optn['bankTransactionId']) ? $payment_optn['bankTransactionId'] : "";
            }elseif(!empty($type) && $type == 'UPI')
            {
                $pg_response['bank_txn_id'] = isset($payment_optn['utr']) ? $payment_optn['utr'] : "";
            }elseif(!empty($type) && $type == 'NETBANKING')
            {
                $pg_response['bank_name'] = isset($payment_optn['bankId']) ? $payment_optn['bankId'] : "";
                $pg_response['bank_txn_id'] = isset($payment_optn['pgTransactionId']) ? $payment_optn['pgTransactionId'] : "";
                $pg_response['pg_order_id'] = isset($payment_optn['pgServiceTransactionId']) ? $payment_optn['pgServiceTransactionId'] : "";
            }
        }else{
            //redirect($txn_info['furl'], 'location', 301);
            $res_arr['url'] = $txn_info['furl'];
            $res_arr['status'] = 2;
            return $res_arr;
        }
        if(isset($txn_data['data']) && in_array($txn_data['code'],$this->success_status)) {
            
            $txnid = $txn_info['transaction_id'];
            $pg_response['txn_amount'] = number_format(($pg_response['txn_amount'] / 100), 2, '.' ,'');

            //$this->db->where('order_id', $txn_info['order_id'])->update(ORDER, array("custom_data" => json_encode($pg_response)));
            $transaction_details = $this->Finance_model->get_transaction_info($txnid);
            $real_amount = $transaction_details['real_amount']+$transaction_details['tds'];
            if($pg_response['txn_amount'] == $real_amount) {
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
        }elseif(isset($txn_data['success']) && $txn_data['success'] == false){
            $txnid                  =   $txn_info['transaction_id'];
            $transaction_record     =   $this->_update_tx_status($txnid, 2, $pg_response);
            // Redirect to Failure URL
            //redirect($transaction_record['furl'], 'location', 301); 
            $res_arr['url'] = $transaction_record['furl'];
            $res_arr['status'] = 2;
            return $res_arr;
        }else{
            $res_arr['url'] = $purl;
            $res_arr['status'] = 2;
            return $res_arr;
        }
    }

    /**
     * Transaction Status
     * @param int $txnid
     * @param array $config
     * @return array
     */
    public function test_txn_status_post() {   
        $post_data = $this->post();
        //print_R($post_data);die;
        $merchant_id    =   ''; 
        $m_salt = '';

        $rev= array(
            "merchantId"=>$post_data['merchantId'],
            "transactionId"=>$post_data['transactionId'],
            "amount"=>$post_data['amount'],
            "providerReferenceId"=>$post_data['providerReferenceId']
        );

    }
}