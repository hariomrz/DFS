<?php

class Cashfree extends Common_Api_Controller {

    public $cashfree_status = array('success', 'failed', 'cancelled');
    public $cashfree_success_status = array("success");
    public $cashfree_fail_status = array("failed","user_dropped","cancelled","void","user_dropped");
    public $cashfree_pending_status = array("pending","incomplete","flagged","not_attempted");



    // case 'INCOMPLETE'   :
    //     case 'PENDING'      :
    //     case 'FLAGGED'      :
    //     case 'NOT_ATTEMPTED':
    //         $payment_data['status'] = $trans_data['transaction_status'] = 0;
    //         $status_page = 'lineup-players-pool';
    //     break;
    //     case 'FAILED'   :
    //     case 'CANCELLED' :
    //     case 'VOID'     :
    //     case 'USER_DROPPED' :



    public $pg_id = 17;
    public $mode = '';
    public $app_id = '';
    public $secret_key = '';
    function __construct() {
        parent::__construct();

        if(!$this->app_config['allow_cashfree']['key_value']){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, cashfree not enabled. please contact admin.";
            $this->api_response();
            }
            $this->mode = $this->app_config['allow_cashfree']['custom_data']['mode'];
            $this->app_id = $this->app_config['allow_cashfree']['custom_data']['app_id'];
            $this->secret_key = $this->app_config['allow_cashfree']['custom_data']['secret_key'];
            $this->ord_prefix = $this->app_config['order_prefix']['key_value'];
            $this->app_version = $this->app_config['allow_cashfree']['custom_data']['app_version'];
    }

    function index() {
        redirect();
    }

    /**
     * Used for deposit fund via cashfree
     * @param
     * @return json array
     */
    public function deposit_post() {
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'trim|required|numeric|callback_validate_deposit_amount');
        $this->form_validation->set_rules('paymentOption', 'Payment Option', 'trim|required');
        // $this->form_validation->set_rules('paymentCode', $this->lang->line('amount'), 'trim|required|numeric');
        $this->form_validation->set_rules('gst', $this->lang->line('gst_number'), 'trim|callback_validate_gst_number');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        //setting order expiry time as 20 at cashfree end
        $current_date = format_date();
        $pay_expiry_time = date('Y-m-d H:i:s', strtotime($current_date. ' +5 hours +50 minutes'));
        $datetime = new DateTime($pay_expiry_time);
        $pay_expiry_time = $datetime->format(DateTime::ISO8601); // Updated ISO860
        $pay_expiry_time =  substr($pay_expiry_time,0,19).'+05:30';

        $post_data = $this->input->post();
        $user_id = $this->user_id;
        $email = isset($this->email) ? $this->email : '';
        $phoneno = isset($this->phone_no) ? $this->phone_no : '';
        $firstname = isset($this->user_name) ? $this->user_name : 'User';
        $returnurl = USER_API_URL . 'cashfree/success';
        $notify_url = USER_API_URL . 'cashfree/cashfree_ipn';
        
        if (!$user_id) {
            $msg = "";
            if (!$user_id) {
                $msg .= $this->lang->line('user_id_required');
            }

            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = $msg;
            $this->api_response();
            return;
        }

        if (isset($post_data['product_info'])) {
            $product_info = $post_data['product_info'];
        } else {
            $product_info = SITE_TITLE . ' cashfree deposit';
        }
        $paymentOption = $post_data['paymentOption'];
        

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
        $txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $product_info, $post_data['surl'], $post_data['furl'], $promo_code,$deal);

        $gst_data = gst_calculate($amount,$this->app_config);
        $amount = isset($gst_data['amount'])?$gst_data['amount']:$amount;

        if ($txnid) {
            if(isset($post_data['upi_intent']) && $post_data['upi_intent'] !="")
            {
                $this->perform_upi_intent($post_data,$txnid);
            }
            $paymentUrl = CASHFREE_TESTPAY_URL.'orders';
            if ($this->mode == 'PRODUCTION') {
                $paymentUrl = CASHFREE_PRODPAY_URL.'orders';
            }
            $cf_request = array();
            $payment_data = array();
            $payment_data['order_token']= "";
            switch($paymentOption){
                case "nb":
                $payment_data['payment_method']['netbanking']['channel'] = 'link';
                $payment_data['payment_method']['netbanking']['netbanking_bank_code'] = $post_data['paymentCode'];
                break;
                case "card":
                $payment_data['payment_method']['card']['channel'] = 'link';
                $payment_data['payment_method']['card']['card_number'] = $post_data['card_number'];
                $payment_data['payment_method']['card']['card_holder_name'] = $post_data['card_holder'];
                $payment_data['payment_method']['card']['card_expiry_mm'] = substr($post_data['card_expiry'],0,2);
                $payment_data['payment_method']['card']['card_expiry_yy'] = substr($post_data['card_expiry'],2);
                $payment_data['payment_method']['card']['card_cvv'] = $post_data['card_cvv'];
                break;
                case "~upi":
                    /**
                     * change upi to ~upi for this reason 
                     * As upi returns a url compitable to application only not for web view 
                     * [example :payment_link: "tez://upi/pay?pa=cashfreevinfote@yesbank&pn=Vinfotech&tr=791453651&am=5.00&cu=INR&mode=00&purpose=00&mc=5816&tn=791453651"] 
                     * so this case is skipped and default case will be executed  */

                $upi_arr = ["link","qrcode","collect"];
                
                if(in_array($post_data['upiMode'],$upi_arr) && $post_data['upiMode'] == 'qrcode'){
                    $payment_data['payment_method']['upi']['channel'] = 'qrcode';
                }
                else if(in_array($post_data['upiMode'],$upi_arr) && $post_data['upiMode'] == 'collect'){
                    $payment_data['payment_method']['upi']['channel'] = 'collect';
                    $payment_data['payment_method']['upi']['upi_id'] = $post_data['upi_id'];
                }
                else {
                    $payment_data['payment_method']['upi']['channel'] = 'link';
                }
                break;
                case "apps":
                    $app_arr = [
                        "4001"=>"freecharge", 
                        "4002"=>"mobikwik", 
                        "4003"=>"ola", 
                        "4004"=>"jio",
                        "4005"=>"gpay", 
                        "4006"=>"airtel", 
                        "4007"=>"paytm", 
                        "4008"=>"amazon", 
                        "4009"=>"phonepe", 
                    ];
                    if(isset($app_arr[$post_data['paymentCode']])){
                    $payment_data['payment_method']['app']['channel'] = $app_arr[$post_data['paymentCode']];
                    }else{
                    $payment_data['payment_method']['app']['channel'] = "4005";
                    }
                    $payment_data['payment_method']['app']['phone'] = $post_data['phone'];
                break;
                case "cashfree":
                break;
                }
            $cf_request["order_id"] = $this->ord_prefix.$txnid;
            $cf_request["order_amount"] = $amount;
            $cf_request["order_currency"] = $this->app_config['currency_abbr']['key_value'] ? $this->app_config['currency_abbr']['key_value']:'INR';
            $cf_request['order_expiry_time']= (string)$pay_expiry_time;
            $cf_request['customer_details'] = array(
                "customer_email"=>$email,
                "customer_phone"=>$phoneno,
                "customer_id"=>$this->user_id,
            );
            $cf_request['order_meta'] = array(
                "return_url" => USER_API_URL."cashfree/success?cf_id={order_id}&cf_token={order_token}",
                "notify_url" => USER_API_URL. "cashfree/cashfree_ipn",
                "payment_methods" => null,
            );
            //new verson set
            if($this->app_version=='2022-09-01')
            {
                $cf_request['order_meta']['return_url'] = USER_API_URL. "cashfree/success?cf_id={order_id}";
            }

            // print_r($cf_request);die;

            // $generate_token = $this->prepaid_curl($paymentUrl,$cf_request);
            // $order_token = isset($generate_token['order_token']) ? $generate_token['order_token'] : "";
            // $payment_data['order_token'] = $order_token;
            
            $cf_base_url = CASHFREE_TESTPAY_URL;
            if ($this->mode == 'PRODUCTION') {
                $cf_base_url = CASHFREE_PRODPAY_URL;
            }

            // for new version
            $payment_url = $cf_base_url.'orders/pay'; //https://api.cashfree.com/pg/orders/pay
            if($this->app_version=='2022-09-01')
            {
                $payment_url = $cf_base_url.'orders';
            }
            //CURL execution
            $curl = curl_init($payment_url); 
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'x-api-version: '.$this->app_version,
                'x-client-id: '.$this->app_id,
                'x-client-secret: '.$this->secret_key,
                'Content-Type: application/json'
            ));
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($cf_request)); 
            $jsonResponse = curl_exec($curl);
            $response = json_decode($jsonResponse, TRUE);
            $response_data = array(
                "order_id" => $response['order_id'],
                "payment_session_id" => $response['payment_session_id'],
                "order_meta" => ["return_url"=>$response['order_meta']['return_url']],
                "prod" => ($this->mode == 'PRODUCTION') ? 1 : 0,
            );
            // print_r($response);die;
            // $this->data['result'] = $response;
            // $this->data['result']['url'] = $generate_token['payment_link'];

            // if(isset($response['payment_method']))
            // {
            //     switch($response['payment_method'])
            //     {
            //         case 'upi':
            //             $this->data['result']['url'] = $response['data']['payload']['gpay'];
            //             $this->data['result']['url2'] = $response['data']['payload']['qrcode'] ? $response['data']['payload']['qrcode'] : '';
            //         break;
            //         case 'nb':
            //             $this->data['result']['url'] = $generate_token['payment_link'];
            //         break;
            //         case 'apps':
            //             $this->data['result']['url'] = $generate_token['payment_link'];
            //         break;

            //         default:
            //         $this->data['result']['url'] = $generate_token['payment_link'];
            //     }
            // }
            // $this->load->helper('form');
            // $this->data['data'] = $this->load->view('cashfree/deposit', $this->data, true);
            // $this->api_response_arry['data']['payment_link'] = $this->data['result']['url'] ? $this->data['result']['url'] : $this->data['result']['url2'];
            $this->api_response_arry['data'] = $response_data;
            $this->api_response();
        }

        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['message'] = $this->lang->line('deposit_error');
        $this->api_response_arry['data']['prod'] = ($this->mode == 'PRODUCTION') ? 1 : 0;
        $this->api_response();
    }
    
    /**
     * Used for update order status on success
     * @param
     * @return redirect on page
     */
    public function success_get() {
        $furl = FRONT_APP_PATH;
        if (!$this->input->get()) {
            redirect($furl, 'location', 301);
        }

        $response = $this->input->get();
        $post_data = $response;
        error_log("\n".'Cashfree callback : '.json_encode($post_data).'<br>',3,'/var/www/html/cron/application/logs/cashfree.log');
        $current_time = format_date();
        $this->load->model("finance/Finance_model");
        $payment_response = $post_data;
        $txnid = substr($response['cf_id'],5);
        $transaction_details = $this->Finance_model->get_transaction_info($txnid);
        if($transaction_details['status']==1)
        {
            redirect($transaction_details['surl'], 'location', 301);
        }
        $transaction_info = $this->is_valid_transaction($txnid);
        
        if (!$transaction_details) {
            redirect($transaction_details['furl'], 'location', 301);
        }
        // Verify done transaction
        $pay_transaction_info = $this->validate_transaction($this->ord_prefix.$txnid);
        // echo "<pre>";print_r($pay_transaction_info['captured_amount']);exit;
        if (!empty($pay_transaction_info['result']) && isset($pay_transaction_info['result']['payment_status'])) {
            $pay_transaction_info['result']['real_amount'] = $transaction_details['real_amount'];
            $pay_transaction_info['result']['tds'] = $transaction_details['tds'];
            $trans_status = "";
            $trans_status = $this->_update_tx_status($txnid, $pay_transaction_info['result']);
            switch($trans_status)
            {
                case '1' :
                    redirect($transaction_details['surl'], 'location', 301);
                break;
                case '2':
                    redirect($transaction_details['furl'], 'location', 301);
                break;
                case '0'   :
                    $purl = str_replace('failure','pending',$transaction_details['furl']);
                    redirect($purl, 'location', 301);
                    break;
                default :
                    $purl = str_replace('failure','pending',$transaction_details['furl']);
                    redirect($purl, 'location', 301);
            }
        }else{
            if(!empty($transaction_details) && $transaction_details['furl'] != ""){
                $furl = str_replace('failure','pending',$transaction_details['furl']);
            }
            redirect($furl, 'location', 301);
        }
    }

    /**
     * Used for check transaction data
     * @param int $txnid
     * @return json array
     */
    private function is_valid_transaction($txnid) {
        if (empty($txnid)) {
            return false;
        }

        $txnid = trim($txnid);
        $where = array(
            'transaction_id' => $txnid,
            'transaction_status' => 0
        );
        // $this->load->model("finance/Finance_model",'fm');
        $transaction_info = $this->Finance_model->get_single_row('transaction_id,order_id', TRANSACTION, $where);
        return $transaction_info;
    }

    /**
     * Used for validate transaction data
     * @param int $txnid
     * @return json array
     */
    private function validate_transaction($txnid) {
        if (empty($txnid)) {
            return false;
        }

        $tx_staus_url = CASHFREE_TESTPAY_URL . 'orders/'.$txnid.'/payments';
        if ($this->mode == 'PRODUCTION') {
            $tx_staus_url = CASHFREE_PRODPAY_URL . 'orders/'.$txnid.'/payments';
        }

        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $tx_staus_url, 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HEADER =>1,
            CURLOPT_HTTPHEADER => array(
                'X-Client-Id: '.$this->app_id,
                'X-Client-Secret: '.$this->secret_key,
                'x-api-version: '.$this->app_version,
                'Content-Type: application/json'
            ),
            ));

            $response = curl_exec($curl);
            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $header = (int)substr($response, 9, 3);
            $body = substr($response, $header_size);
            $response = json_decode($body,true);
            $is_success= false;
            error_log("\n".'Cashfree txn status : '.json_encode($response).'<br>',3,'/var/www/html/cron/application/logs/cashfree.log');
            if(!empty($response[0]))
            {
                if(count($response) > 1)
                {
                    foreach($response as $key=>$res)
                    {
                        if($res['payment_status']=='SUCCESS')
                        {
                            $is_success=true;
                            $response = $response[$key];
                            break;
                        }
                    }
                    if($is_success==false)
                    {
                        $response = $response[0];
                    }
                }
                else{
                    $response = $response[0];
                }
            }
            
            curl_close($curl);
            return array('result' => $response);

        }catch(Exception $e){
           return false;
        }
    }

    /**
     * Used for update transaction status and add balance in user account
     * @param int $transaction_id
     * @param array $ipn_data
     * @return json array
     */
    private function _update_tx_status($transaction_id, $ipn_data) {

        $trnxn_rec = $this->Finance_model->get_transaction($transaction_id);
        //completed
        if (isset($ipn_data) && !empty($trnxn_rec)) { 
            
            if (isset($ipn_data['payment_status']) && in_array(strtolower($ipn_data['payment_status']), $this->cashfree_success_status)) {
                
                $txn_pg_amount = isset($ipn_data['order_amount']) ? $ipn_data['order_amount'] : 0;
                $txn_pg_amount = number_format($txn_pg_amount, 2, '.', '');
                $real_amount = $ipn_data['real_amount']+$ipn_data['tds'];
                if($txn_pg_amount == $real_amount){
                    $status = 1;
                    $this->update_order_status($trnxn_rec["order_id"], $status, $transaction_id, 'by your recent transaction', 17);
                    $data['transaction_status'] = $status;
                }else{
                    $data['transaction_status'] = 0; //pending    
                }
                //return TRUE;
            } elseif (isset($ipn_data['payment_status']) && in_array(strtolower($ipn_data['payment_status']),$this->cashfree_fail_status)) {
                    $data['transaction_status'] = 2; //failed
                    $this->Finance_model->update_transaction($data, $transaction_id);
                    $sql = "UPDATE " . $this->db->dbprefix(ORDER) . " AS O
                        INNER JOIN " . $this->db->dbprefix(TRANSACTION) . " AS T ON T.order_id = O.order_id
                        SET O.status = T.transaction_status, T.txn_id = '{$transaction_id}'
                        WHERE T.transaction_id = $transaction_id";
                        $this->db->query($sql);
                    
            }elseif (isset($ipn_data['payment_status']) && in_array(strtolower($ipn_data['payment_status']),$this->cashfree_pending_status)) {
                $data['transaction_status'] = 0; //pending
            }
                
                // if($data['transaction_status']!=0)
                // {
                // $data['gate_way_name'] = "Cashfree_upgraded";
                // $data['txn_amount'] = $ipn_data['order_amount'] ? $ipn_data['order_amount']:'';
                // $data['bank_txn_id'] = isset($ipn_data['bank_reference']) ? $ipn_data['bank_reference'] : NULL;
                // $data['txn_date'] = isset($ipn_data['payment_time']) ? $ipn_data['payment_time'] : NULL;
                // $data['transaction_message'] = isset($ipn_data['payment_message']) ? $ipn_data['payment_message'] : '';
                // $data['currency'] = isset($ipn_data['payment_currency']) ? $ipn_data['payment_currency'] : '';
                // $data['mid'] = isset($ipn_data['cf_payment_id']) ? $ipn_data['cf_payment_id'] : "";
                // $data['pg_order_id'] = isset($ipn_data['order_id']) ? $ipn_data['order_id'] : "";

                // $res = $this->Finance_model->update_transaction($data, $transaction_id);
                // // When Transaction has been failed , order status will also become fails
                // $txn_id = isset($ipn_data['referenceId']) ? $ipn_data['referenceId'] : '';
                // $sql = "UPDATE " . $this->db->dbprefix(ORDER) . " AS O
                //         INNER JOIN " . $this->db->dbprefix(TRANSACTION) . " AS T ON T.order_id = O.order_id
                //         SET O.status = T.transaction_status, T.txn_id = '{$txn_id}'
                //         WHERE T.transaction_id = $transaction_id";
                // $this->db->query($sql);
                // }
                return $data['transaction_status'];
            }
        else {
            return 0;
        }
    }

    public function prepaid_curl($url,$post_data)
    {
        // $url = 'https://sandbox.cashfree.com/pg/orders';//CASHFREE_SUBSCRIPTION_URL.$url;
        try {
            $curl = curl_init($url); 
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'X-Client-Id: '.$this->app_id,
                'X-Client-Secret: '.$this->secret_key,
                'x-api-version: '.$this->app_version,
                'Content-Type: application/json'
            ));
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data)); 
            $jsonResponse = curl_exec($curl);
            $response = json_decode($jsonResponse, TRUE);
            if(!$response){
                return false;
            }
            return $response;

        }catch(Exception $e){
           return false;
        }
    }

    /**
     * Used for process payment gateway response data
     * @param post data
     * @return json array
     */
    public function cashfree_ipn_post() {

        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            exit;
        }

        $post_data = $this->input->post();
        error_log("\n".'Cashfree callback : '.json_encode($post_data).'<br>',3,'/var/www/html/cron/application/logs/cashfree.log');
        if (LOG_TX) {
            $post_data['source'] = 'ipn';
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => $current_time));
        }

        $this->load->model('cashfree_model','cfm');
        $order_status = $this->cfm->get_single_row('status', ORDER, array("order_id" => $post_data['orderId']));
        
        if($order_status['status']==1)
        {
            return true;
        }
        $paymentUrl = CASHFREE_TESTPAY_URL.'orders/'.$post_data['orderId'].'/payments?payment_status=SUCCESS';
        if ($this->mode == 'PRODUCTION') {
            $paymentUrl = CASHFREE_PRODPAY_URL.'orders/'.$post_data['orderId'].'/payments?payment_status=SUCCESS';
        }

        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $paymentUrl, 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HEADER =>1,
            CURLOPT_HTTPHEADER => array(
                'X-Client-Id: '.$this->app_id,
                'X-Client-Secret: '.$this->secret_key,
                'x-api-version: '.$this->app_version,
                'Content-Type: application/json'
            ),
            ));

            $response = curl_exec($curl);
            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $header = (int)substr($response, 9, 3);
            $body = substr($response, $header_size);
            $response = json_decode($body,true);
            $is_success= false;
            error_log("\n".'Cashfree txn status : '.json_encode($response).'<br>',3,'/var/www/html/cron/application/logs/cashfree.log');
            if(!empty($response[0]))
            {
                if(count($response) > 1)
                {
                    foreach($response as $key=>$res)
                    {
                        if($res['payment_status']=='SUCCESS')
                        {
                            $is_success=true;
                            $response = $response[$key];
                            break;
                        }
                    }
                    if($is_success==false)
                    {
                        $response = $response[0];
                    }
                }
                else{
                    $response = $response[0];
                }
            }
            
            curl_close($curl);

        }catch(Exception $e){
           return false;
        }

        $current_time = format_date();
        $this->load->model("finance/Finance_model");
        $payment_response = $post_data;
        $txnid = substr($response['cf_id'],5);
        $transaction_details = $this->Finance_model->get_transaction_info($txnid);
        if($transaction_details['status']==1)
        {
            return true;
        }
        $transaction_info = $this->is_valid_transaction($txnid);
        
        if (!$transaction_info) {
            redirect($transaction_details['furl'], 'location', 301);
        }
        // Verify done transaction
        $pay_transaction_info = $this->validate_transaction($this->ord_prefix.$txnid);
        // echo "<pre>";print_r($pay_transaction_info['captured_amount']);exit;
        if (!empty($pay_transaction_info['result']) && isset($pay_transaction_info['result']['payment_status'])) {
            $pay_transaction_info['result']['real_amount'] = $transaction_details['real_amount'];
            $pay_transaction_info['result']['tds'] = $transaction_details['tds'];
            $trans_status = "";
            $this->_update_tx_status($txnid, $pay_transaction_info['result']);
        return true;
       
        }
    }

    public function get_wallet_bank_list_post(){

        $cashfree_list_cache_key = "cashfree_list_data";
        $cashfree_list_data = $this->get_cache_data($cashfree_list_cache_key);

        if(empty($affiliate_master_data))
        {
        $this->load->model('cashfree_model','gm');
        $cashfree_list_data = $this->gm->get_wallet_bank_list();
        $this->set_cache_data($cashfree_list_cache_key,$cashfree_list_data,REDIS_2_DAYS); 
        }

        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK; 
        $this->api_response_arry['data']			= $cashfree_list_data;
		$this->api_response();
        
    }

    /**
     * function to get callback from upi intent payment
     */
    public function upi_intent_callback_post()
    {
        $this->form_validation->set_rules('orderId', "Transaction ID", 'trim|required');
        $this->form_validation->set_rules('txStatus', "status", 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
      $post_data = $this->input->post();
      if(LOG_TX) error_log("\n\n\n".format_date().'<<<< CASHFREE UPI INTENT CALLBACK >>>> : '.json_encode($post_data),3,'/var/www/html/cron/application/logs/cashfree.log');
    //   if(LOG_TX) error_log("\n\n\n".format_date().'<<<< CASHFREE UPI INTEST CALLBACK >>>> : '.json_encode($post_data),3,'/var/www/html/framework/cron/application/logs/cashfree.log');

      if(!isset($post_data['orderId']) || $post_data['orderId']=="")
      {
        $curl = FRONT_APP_PATH;
        redirect($curl, 'location', 301);
      }
        $txnid = substr($post_data['orderId'],5);
        $pg_txnid = $post_data['orderId'];
        $this->load->model("finance/Finance_model");
        $transaction_details = $this->Finance_model->get_transaction_info($txnid);
        if (!$transaction_details) {
            redirect($transaction_details['furl'], 'location', 301);
        }

        if ($transaction_details['transaction_status']!=0) {
            redirect($transaction_details['furl'], 'location', 301);
        }

        if($transaction_details['status']==1)
        {
            redirect($transaction_details['surl'], 'location', 301);
        }elseif($transaction_details['status']==2)
        {
            redirect($transaction_details['furl'], 'location', 301);
        }
        
        
        // Verify done transaction
        $pay_transaction_info = $this->validate_transaction($pg_txnid);

        if(LOG_TX) error_log("\n\n\n".format_date().'<<<< CASHFREE UPI INTENT STATUS RESPONSE >>>> : '.json_encode($pay_transaction_info),3,'/var/www/html/cron/application/logs/cashfree.log');
        if (!empty($pay_transaction_info['result']) && isset($pay_transaction_info['result']['payment_status'])) {
            $pay_transaction_info['result']['real_amount'] = $transaction_details['real_amount'];
            $pay_transaction_info['result']['tds'] = $transaction_details['tds'];
            $trans_status = "";
            $trans_status = $this->_update_tx_status($txnid, $pay_transaction_info['result']);

            $url = str_replace('failure','pending',$transaction_details['furl']);
            switch($trans_status)
            {
                case '1' :
                    $url = $transaction_details['surl'];
                break;
                case '2':
                    $url = $transaction_details['furl'];
                break;
                case '0'   :
                    $url = str_replace('failure','pending',$transaction_details['furl']);
                default :
                    $url = str_replace('failure','pending',$transaction_details['furl']);
            }
        }else{
            if(!empty($transaction_details) && $transaction_details['furl'] != ""){
                $url = str_replace('failure','pending',$transaction_details['furl']);
            }
        }
        $this->api_response_arry['data']['url']	= $url;
		$this->api_response();
    }

    /**
     * method to perform upi intent
     */
    public function perform_upi_intent($post_data,$txnid)
    {
        if(!isset($post_data))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('deposit_error');
            $this->api_response(); 
        }

        $token_url = "https://test.cashfree.com/api/v2/cftoken/order";
        if ($this->mode == 'PRODUCTION') {
            $token_url = "https://api.cashfree.com/api/v2/cftoken/order";
        }
        $req_data = array(
                "orderId"=>$this->ord_prefix.$txnid,
                "orderAmount"=>$post_data['amount'],
                "orderCurrency"=>$this->app_config['currency_abbr']['key_value']
        );

        //generate token
        $result = $this->prepaid_curl($token_url,$req_data);
        if(!isset($result['cftoken']) || $result==false)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('token_error');
            $this->api_response();
        }

        $res_data = array(
            "tokenData"         =>$result['cftoken'],
            "orderId"           =>$this->ord_prefix.$txnid,
            "orderAmount"       =>$post_data['amount'],
            "orderCurrency"     =>$this->app_config['currency_abbr']['key_value'] ? $this->app_config['currency_abbr']['key_value']:'INR',
            "orderNote"         =>"pay via upi intent",
            "customerName"      =>isset($this->user_name) ? $this->user_name : 'User',
            "customerPhone"     =>isset($this->phone_no) ? $this->phone_no : '1234567890',
            "customerEmail"     =>isset($this->email) ? $this->email : 'user@vinfotech.com'
        );
        $this->api_response_arry['data'] = $res_data;
        $this->api_response();
    }
}
