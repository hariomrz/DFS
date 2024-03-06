<?php

class Paylogic extends Common_Api_Controller {

    public $success_status = array("captured","ok");
    public $failed_status = array("f","declined","acquirer_error","denied","timeout","authentication_unavailable","failed","duplicate","signature_mismatch","cancelled","recurring_payment_unsuccessfull","denied_by_risk","invalid_request","refund_insufficient_balance","txn_failed","failed_at_acquirer","validation_failed","payment_option_not_supported");
    public $pending_status = array("processing","sent_to_bank","auto_reversal");
    public $pg_id = 18;
    public $order_prefix = '';
    public $CALLBACK_URL = USER_API_URL."paylogic/callback";

    function __construct() {
        if(isset($this->app_config['allow_paylogic']) && $this->app_config['allow_paylogic']['key_value'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, Paylogic not enabled. please contact admin.";
            $this->api_response();
        }
        parent::__construct();
        $this->finance_lang = $this->lang->line("finance");
        $this->mode = $this->app_config['allow_paylogic']['custom_data']['mode'];
        $this->SALT = $this->app_config['allow_paylogic']['custom_data']['salt'];
        $this->APP_ID = $this->app_config['allow_paylogic']['custom_data']['app_id'];
        $this->CURRENCY = $this->app_config['allow_paylogic']['custom_data']['currency'];
        $this->order_prefix = isset($this->app_config['order_prefix']) ? $this->app_config['order_prefix']['key_value'] : '';
    }

    /**
     * payload encrypton
     * @param $data, $key
     * @return String
     */
    function encrypt($data, $key){
        $iv = substr($key,0,16);
        $cipher="aes-256-cbc"; 
        if(strlen($iv) >0){   
        $data=openssl_encrypt($data, $cipher, $key, 0, $iv);
        }
        return $data;
    }
    
    /**
     * response decryption
     * @param $data, $key
     * @return Array
     */
    function decrypt($data,$key){
        $iv = substr($key,0,16);
        $cipher="aes-256-cbc";
        if(strlen($iv) >0){   
            $data=openssl_decrypt($data, $cipher, $key, 0, $iv); 
            $final = json_decode($data,true);
        }
        return $final;
    } 

    /**
     * @method deposit cash
     * @uses funtion to add real and bonus cash to user
     * */
    public function deposit_post() {
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|callback_decimal_numeric|callback_validate_deposit_amount');
        $this->form_validation->set_rules('furl', 'furl', 'required');
        $this->form_validation->set_rules('surl', 'surl', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();
        $user_id = $this->user_id;
        if (empty($post_data['product_info'])) {
            $post_data['product_info'] = SITE_TITLE . ' amount deposit';
        }

        $email = isset($this->email) ? $this->email : 'paylogic_user@vinfotech.com';
        $phoneno = isset($this->phone_no) ? $this->phone_no : '1234567890';
        $firstname = isset($this->user_name) ? $this->user_name : 'User';
        $amount = $post_data['amount'];
        // $amount = number_format($amount, 0, '.', '');
        $this->load->model("finance/Finance_model");


        // validate promo code 
        $promo_code = array();
        if (!empty($post_data['promo_code'])) {
            $promo_code = $this->validate_promo($post_data);
        }


        // validate deal
        $deal = array();
        if (!empty($post_data['deal_id'])) {
            $deal = $this->validate_deal($post_data);
        }


        $_POST['payment_gateway_id'] = $this->pg_id;
        
        // GET transaction ID from Database after generating an order
        $txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $post_data['product_info'], $post_data['surl'], $post_data['furl'], $promo_code,$deal);
        if(!empty($txnid)){
            $pg_order_id = $this->order_prefix.$txnid;
            $this->Finance_model->update(TRANSACTION, array("pg_order_id" => $pg_order_id), array('transaction_id' => $txnid));
            $request_data = Array
            (
                "merchantId" => $this->APP_ID,
                "apiKey" => $this->SALT,
                "returnURL" => $this->CALLBACK_URL,
                "type" => "1.1",
                "txnId" => $pg_order_id,
                "txnType" => "DIRECT",
                "amount" => number_format($amount,2,'.',''),
                "dateTime" => format_date(),
                "productId" => "DEFAULT",
                "channelId" => "0",
                "instrumentId" => "NA",
                "isMultiSettlement" => "0",
                "custMobile" => $phoneno,
                "custMail" => $email,
                "cardDetails" => "NA",
                "cardType" => "NA",
                "udf5" => "NA",
                "udf1" => "NA",
                "udf2" => "NA",
                "udf3" => "NA",
                "udf4" => "NA",
            );

            // echo "<pre>"; print_r($request_data);
            $result = json_encode($request_data);
            $this->data['fields'] = [
                "reqData"       => $this->encrypt($result,$this->SALT),
                "merchantId"    => $this->APP_ID
            ];
            // echo "<pre>"; print_r($request);exit;
                // log inpur params
                if (LOG_TX) {
                    error_log("\n\n".format_date().' REQUEST PARAMS: '.json_encode($this->data['fields']).'<br>',3,'/var/www/html/cron/application/logs/paylogic.log');
                }
                
            // $this->data['data'] = $this->load->view('paylogic/deposit', $this->data, true);
            $this->api_response_arry['data'] = $this->load->view('paylogic/deposit', $this->data, true);
            $this->api_response();
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('deposit_error');
        }
    }

    public function callback_post(){
        $furl = FRONT_APP_PATH;
        $response = $_REQUEST;
        $post_data = $this->decrypt($response['respData'],$this->SALT);
        
        // LOG RESPONSE
        if (LOG_TX) {
            error_log("\n\n".format_date().' RESPONSE DATA OF '.$post_data['txn_id'].' : '.json_encode($post_data).'<br>',3,'/var/www/html/cron/application/logs/paylogic.log');
        }

        if (!$this->input->post() || !isset($post_data['merchant_id']) || $post_data['merchant_id'] != $this->APP_ID) {
            redirect($furl, 'location', 301);
        }

        $this->load->model("finance/Finance_model");
        $pg_order_id = $post_data['txn_id'];
        $txn_info = $this->Finance_model->get_single_row('*',TRANSACTION, array('pg_order_id' => $pg_order_id));
        if(empty($txn_info)){
            redirect($furl, 'location', 301);
        }else if($txn_info['transaction_status'] == "1"){
            redirect($txn_info['surl'], 'location', 301);
        }else if($txn_info['transaction_status'] == "2"){
            redirect($txn_info['furl'], 'location', 301);
        }

        //cross check the transaction status
        $txn_data = get_paylogic_txn_status($post_data,$this->APP_ID);
        $txn_data = $this->decrypt($txn_data,$this->SALT);
        
        $txnid = substr($txn_data['txn_id'],5);
        if(isset($txn_data['trans_status']) && in_array(strtolower($txn_data['trans_status']),$this->success_status)){
            // $this->Finance_model->get_transaction_info($txnid);
            $this->_update_tx_status($txnid, 1, $txn_data);
            redirect($txn_info['surl'], 'location', 301);
        }else if(isset($txn_data['trans_status']) && in_array(strtolower($txn_data['trans_status']),$this->failed_status)){
            $this->_update_tx_status($txnid, 2, $txn_data);
            redirect($txn_info['furl'], 'location', 301);
        }else{
            $purl = str_replace("failure","pending",$txn_info['furl']);
            redirect($purl, 'location', 301);
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

        $trnxn_rec = $this->Finance_model->get_transaction($transaction_id);
        if ($status_type == 1) {
            if (!empty($trnxn_rec)) {
                $this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'by your recent transaction', $this->pg_id);
            }
        }
        // CALL Platform API to update transaction
        $data['payment_mode'] = isset($pg_response['payment_mode']) ? $pg_response['payment_mode'] : "";
        $data['bank_txn_id'] = isset($pg_response['bank_ref_id']) ? $pg_response['bank_ref_id'] : "";
        $data['mid'] = isset($pg_response['pg_ref_id']) ? $pg_response['pg_ref_id'] : "";
        $data['txn_id'] = isset($pg_response['txn_id']) ? $pg_response['txn_id'] : "";
        $data['txn_amount'] = isset($pg_response['txn_amount']) ? $pg_response['txn_amount'] : 0;
        $data['txn_date'] = isset($pg_response['resp_date_time']) ? date("Y-m-d H:i:s",$pg_response['resp_date_time']) : NULL;
        $data['gate_way_name'] = "Paylogic";
        $data['transaction_status'] = $status_type;

        
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
            $tmp["subject"] = "paylogic : Deposit amount Failed to credit";
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
