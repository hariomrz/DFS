<?php

class Razorpay extends Common_Api_Controller {

    public $success_status = array("captured","paid");
    public $failed_status = array("failure","failed");
    public $pg_id = 8;
    public $prefix = 'IFNTSY';
    function __construct() {
        parent::__construct();
        $this->finance_lang = $this->lang->line("finance");

        if(isset($this->app_config['allow_razorpay']) && $this->app_config['allow_razorpay']['key_value'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, Razorpay not enabled. please contact admin.";
            $this->api_response();
        }

        $this->RAZORPAY_MODE        = $this->app_config['allow_razorpay']['custom_data']['mode'];
        $this->RAZORPAY_KEY         = $this->app_config['allow_razorpay']['custom_data']['key'];
        $this->RAZORPAY_SECRET      = $this->app_config['allow_razorpay']['custom_data']['secret'];
        $this->RAZORPAY_CURRENCY    = $this->app_config['currency_abbr']['key_value']; //[EX : INR,USD]
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
        $txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $post_data['product_info'], $post_data['surl'], $post_data['furl'], $promo_code,$deal);

        $gst_data = gst_calculate($amount,$this->app_config);
        $amount = isset($gst_data['amount'])?$gst_data['amount']:$amount;


        $pg_txn_data = array("txn_id"=>$txnid,"amount"=>$amount);
        $config = array(
            "r_key"=>$this->RAZORPAY_KEY,
            "r_secret"=>$this->RAZORPAY_SECRET,
            "r_currency"=>$this->RAZORPAY_CURRENCY,
        );
        $this->load->library('razor_pay',$config);
        $razorpay = new Razor_pay($config);
        $result = $razorpay->razorpay_txn_order($pg_txn_data);
        //echo "<pre>";print_r($result);die;
        if(!empty($result) && isset($result['id'])){
            $pg_order_id = $result['id'];
            $this->Finance_model->update(TRANSACTION, array("pg_order_id" => $pg_order_id), array('transaction_id' => $txnid));

            $this->data['action'] = USER_API_URL.'razorpay/callback';
            $this->data['image'] = IMAGE_PATH."assets/img/logo.png";
            $this->data['key'] = $this->RAZORPAY_KEY;
            $this->data['site_name'] = SITE_TITLE;
            $this->data['order_id'] = $pg_order_id;
            $this->data['amount'] = $amount;
            $this->data['currency'] = $this->RAZORPAY_CURRENCY;
            $this->data['merchant_order_id'] = $this->prefix.$txnid;
            $this->data['name'] = $firstname;
            $this->data['description'] = $post_data['product_info'];
            $this->data['prefill'] = array("name"=>$firstname,"email"=>$email,"contact"=>$phoneno);

            if (LOG_TX) {
                $this->db->insert(TEST, array('data' => json_encode($this->data), 'added_date' => format_date(), 'user_id' => 0));
            }

            $this->api_response_arry['data'] = $this->data;
            $this->api_response();
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('deposit_error');
        }
        
    }

    public function callback_post(){
        $furl = FRONT_APP_PATH;
        $post_data = $this->post();
        //echo "<pre>";print_r($post_data);die;
        if(isset($post_data['error']['metadata']) && !empty($post_data['error']['metadata'])){
            $this->failed_transaction($post_data);
        }
        if (!$this->input->post() || !isset($post_data['razorpay_order_id']) || !isset($post_data['razorpay_payment_id']) || !isset($post_data['razorpay_signature'])) {
            redirect($furl, 'location', 301);
        }

        $this->load->model("finance/Finance_model");
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }

        $pg_order_id = $post_data['razorpay_order_id'];
        $txn_info = $this->Finance_model->get_single_row('*',TRANSACTION, array('pg_order_id' => $pg_order_id));
        if(empty($txn_info)){
            redirect($furl, 'location', 301);
        }else if($txn_info['transaction_status'] == "1"){
            redirect($txn_info['surl'], 'location', 301);
        }else if($txn_info['transaction_status'] == "2"){
            redirect($txn_info['furl'], 'location', 301);
        }

        //validate razorpay txn
        $config = array(
            "r_key"=>$this->RAZORPAY_KEY,
            "r_secret"=>$this->RAZORPAY_SECRET,
            "r_currency"=>$this->RAZORPAY_CURRENCY,
        );
        $txn_data = get_razorpay_txn_status($post_data['razorpay_order_id'],$config);
        //echo "<pre>";print_r($txn_data);die;
        if(isset($txn_data['items']) && isset($txn_data['items']['0']) && in_array(strtolower($txn_data['items']['0']['status']),$this->success_status)){
            $txnid = $txn_info['transaction_id'];
            $pg_response = $txn_data['items']['0'];
            $pg_response['amount'] = number_format(($pg_response['amount'] / 100),2,'.','');
            $transaction_details = $this->Finance_model->get_transaction_info($txnid);
            $real_amount = $transaction_details['real_amount']+$transaction_details['tds'];
            if($pg_response['amount'] == $real_amount){
                $transaction_record = $this->_update_tx_status($txnid, 1, $pg_response);
                // Redirect to Failure URL
                redirect($transaction_record['surl'], 'location', 301);
            }else{
                redirect($txn_info['furl'], 'location', 301);
            }
        }else{
            redirect($txn_info['furl'], 'location', 301);
        }
    }

    public function failed_transaction($post_data){
        $furl = FRONT_APP_PATH;
        if(isset($post_data['error']['metadata']) && !empty($post_data['error']['metadata'])){
            
        }
        if (!$this->input->post() || !isset($post_data['error']['metadata']) || empty($post_data['error']['metadata'])) {
            redirect($furl, 'location', 301);
        }

        $this->load->model("finance/Finance_model");
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }
        $pg_data = json_decode($post_data['error']['metadata'],TRUE);
        $pg_order_id = $pg_data['order_id'];
        $txn_info = $this->Finance_model->get_single_row('*',TRANSACTION, array('pg_order_id' => $pg_order_id));
        if(empty($txn_info)){
            redirect($furl, 'location', 301);
        }else if($txn_info['transaction_status'] == "1"){
            redirect($txn_info['surl'], 'location', 301);
        }else if($txn_info['transaction_status'] == "2"){
            redirect($txn_info['furl'], 'location', 301);
        }
        
        //validate razorpay txn
        $config = array(
            "r_key"=>$this->RAZORPAY_KEY,
            "r_secret"=>$this->RAZORPAY_SECRET,
            "r_currency"=>$this->RAZORPAY_CURRENCY,
        );
        $txn_data = get_razorpay_txn_status($txn_info['pg_order_id'],$config);
        if(isset($txn_data['items']) && isset($txn_data['items']['0']) && in_array(strtolower($txn_data['items']['0']['status']),$this->failed_status)){
            $txnid = $txn_info['transaction_id'];
            $pg_response = $txn_data['items']['0'];
            $pg_response['amount'] = number_format(($pg_response['amount'] / 100),2,'.','');
            $transaction_record = $this->_update_tx_status($txnid, 2, $pg_response);
            // Redirect to Failure URL
            redirect($transaction_record['furl'], 'location', 301);
        }else{
            redirect($txn_info['furl'], 'location', 301);
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
        $data['payment_mode'] = isset($pg_response['method']) ? $pg_response['method'] : "";
        $data['mid'] = isset($pg_response['card_id']) ? $pg_response['card_id'] : "";
        $data['txn_id'] = isset($pg_response['id']) ? $pg_response['id'] : "";
        $data['bank_txn_id'] = isset($pg_response['card_id']) ? $pg_response['card_id'] : "";
        $data['txn_amount'] = isset($pg_response['amount']) ? $pg_response['amount'] : 0;
        $data['txn_date'] = isset($pg_response['created_at']) ? date("Y-m-d H:i:s",$pg_response['created_at']) : NULL;
        $data['gate_way_name'] = "Razorpay";
        $data['is_checksum_valid'] = "1";
        $data['transaction_status'] = $status_type;
        $data['transaction_message'] = isset($pg_response['error_reason']) ? $pg_response['error_reason'] : "";
        
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
            $tmp["subject"] = "Deposit amount Failed to credit";
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
