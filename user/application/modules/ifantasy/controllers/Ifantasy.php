<?php

class Ifantasy extends Common_Api_Controller{

	public $pg_id =14;
    public $success_status = array("SUCCESSFUL");
    public $failed_status = array("FAILED");
    public $prefix = 'IFNTSY';

	function __construct(){
		parent::__construct();

       if(!$this->app_config['allow_ifantasy']['key_value']){
        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['status'] = FALSE;
        $this->api_response_arry['message'] = "Sorry, ifantasy not enabled. please contact admin.";
        $this->api_response();
        }

    }

     /**
     * @Method deposit_post
     * @uses deposti money using ifantasy
     * @since December 2021
     * *** */
	public function deposit_post(){
        $this->load->helper('form');
		$this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|callback_decimal_numeric|callback_validate_deposit_amount');
		$this->form_validation->set_rules('furl', 'furl', 'required');
		$this->form_validation->set_rules('surl', 'surl', 'required');
		if ($this->form_validation->run() == FALSE) {
			$this->send_validation_errors();
		}
		$post_data = $this->post();
		$user_id = $this->user_id;
		$surl = $post_data['surl'];
		$furl = $post_data['furl'];
		$product_info = SITE_TITLE . ' deposit via ifantasy';
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

        $mode = $this->app_config['allow_ifantasy']['custom_data']['mode'];
        $key = $this->app_config['allow_ifantasy']['custom_data']['key'];
        $member_id = $this->app_config['allow_ifantasy']['custom_data']['member_id'];

            if($this->app_config['allow_ifantasy']['custom_data']['mode']=='PROD')
            {
                $url = IFANTASY_PROD_URL;
            }else{
                $url = IFANTASY_TEST_URL;
            }

        // till now for me order id and invoice number are same txnid as billow
		$_POST['payment_gateway_id'] = $this->pg_id;
		$txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $product_info, $surl, $furl, $promo_code, $deal);
        $txnid = $this->prefix.$txnid;
		$fields =[
                    "me_id"=>$member_id, 
                    "environment"=> IFANTASY_NOTIFY_URL, // return url 
                    "redirect_url" => IFANTASY_CALLBACK_URL,
                    "order_id"=> $txnid,
                    "amount"=> $amount,
                    "customer_name"=> $firstname,
                    "mobile"=> $phoneno,
                    "email"=> $email,
                ];
        // $this->data['fields'] = $fields;

        //CURL EXECUTION
            try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));  //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $headers = [
                'APIKey: '.$key,
                'Content-Type: application/json',
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $request = curl_exec($ch);
            curl_close($ch);
            $request_url = json_decode($request, true);
            $this->data['pay_url'] = '';
            if (strtoupper($request_url['status']) == 'SUCCESS') {
                $this->api_response_arry['data']['payment_link'] = $request_url['reason'];
            }else{
                $this->api_response_arry['data']['payment_link'] = "";
            }
            $this->api_response();

        }
        catch(Exception $e){
            $this->data['data'] = $this->load->view('paystack/deposit', $this->data, true);
            $this->api_response_arry['error'] = 'Error : '.$e;
            $this->api_response();
        }
            

        }

    public function callback_post()
    {
        $furl = FRONT_APP_PATH;
        $post_data = $this->input->post();

        if (!$this->input->post() || !isset($post_data['order_id'])) {
            redirect($furl, 'location', 301);
        }

        $this->load->model("finance/Finance_model");
        error_log("\n".format_date().' Ifantasy_return: '.json_encode($post_data).'<br>',3,'/var/www/html/cron/application/logs/payment.log');

        $order_id = substr($post_data['order_id'],6);
        $txn_info = $this->Finance_model->get_single_row('*',TRANSACTION, array('transaction_id' => $order_id));
        if(empty($txn_info)){
            redirect($furl, 'location', 301);
        }else if($txn_info['transaction_status'] == "1"){
            redirect($txn_info['surl'], 'location', 301);
        }else if($txn_info['transaction_status'] == "2"){
            redirect($txn_info['furl'], 'location', 301);
        }

        $data = array(
            "txnid"     => $post_data['order_id'],
            "key"       => $this->app_config['allow_ifantasy']['custom_data']['key'],
            "member_id" => $this->app_config['allow_ifantasy']['custom_data']['member_id'],
        );
        $txn_data = get_ifantasy_txn_status($data);
        // echo "<pre>";print_r($txn_data);die;
        if(isset($txn_data['status']) && isset($txn_data['Txt_Ref']) && in_array(strtoupper($txn_data['status']),$this->success_status)){
            $txnid = $txn_info['transaction_id'];
            $pg_response = $txn_data;
            $pg_response['txn_amount'] = number_format($txn_data['txn_amount'],2,'.','');
            $transaction_details = $this->Finance_model->get_transaction_info($txnid);
            if($pg_response['txn_amount'] == $transaction_details['real_amount']){
                $transaction_record = $this->_update_tx_status($txnid, 1, $pg_response);
                // Redirect to Failure URL
                redirect($transaction_record['surl'], 'location', 301);
            }else{
                $txnid = $txn_info['transaction_id'];
                $pg_response = $txn_data;
                $pg_response['txn_amount'] = number_format($txn_data['txn_amount'],2,'.','');
                $transaction_record = $this->_update_tx_status($txnid, 2, $pg_response);
                redirect($txn_info['furl'], 'location', 301);
            }
        }elseif(isset($txn_data['status']) && in_array(strtoupper($txn_data['status']),$this->failed_status)){
            
            // $transaction_record = $this->_update_tx_status($txnid, 2, $pg_response);
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
        $data['pg_order_id'] = $this->prefix.$transaction_id;
        $data['bank_txn_id'] = (isset($pg_response['Txt_Ref']) && $status_type == 1) ? $pg_response['Txt_Ref'] : "";
        $data['txn_amount'] = isset($pg_response['txn_amount']) ? $pg_response['txn_amount'] : 0;
        $data['txn_date'] = isset($pg_response['txn_date']) ? date("Y-m-d H:i:s",$pg_response['txn_date']) : NULL;
        $data['gate_way_name'] = "Ifantasy";
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

    public function notify_post()
    {
        $furl = FRONT_APP_PATH;
        $post_data = $this->input->post();

        if (!$this->input->post() || !isset($post_data['order_id'])) {
            redirect($furl, 'location', 301);
        }
        $this->load->model("finance/Finance_model");
        error_log("\n".format_date().' Ifantasy_notify: '.json_encode($post_data).'<br>',3,'/var/www/html/cron/application/logs/payment.log');
        $order_id = substr($post_data['order_id'],6);
        $txn_info = $this->Finance_model->get_single_row('*',TRANSACTION, array('transaction_id' => $order_id));
        if(empty($txn_info)){
            redirect($furl, 'location', 301);
        }else if($txn_info['transaction_status'] == "1"){
            redirect($txn_info['surl'], 'location', 301);
        }else if($txn_info['transaction_status'] == "2"){
            redirect($txn_info['furl'], 'location', 301);
        }

        $data = array(
            "txnid"     => $post_data['order_id'],
            "key"       => $this->app_config['allow_ifantasy']['custom_data']['key'],
            "member_id" => $this->app_config['allow_ifantasy']['custom_data']['member_id'],
        );
        $txn_data = get_ifantasy_txn_status($data);
        // echo "<pre>";print_r($txn_data);die;
        if(isset($txn_data['status']) && isset($txn_data['Txt_Ref']) && in_array(strtoupper($txn_data['status']),$this->success_status)){
            $txnid = $txn_info['transaction_id'];
            $pg_response = $txn_data;
            $pg_response['txn_amount'] = number_format($txn_data['txn_amount'],2,'.','');
            $transaction_details = $this->Finance_model->get_transaction_info($txnid);
            if($pg_response['txn_amount'] == $transaction_details['real_amount']){
                $transaction_record = $this->_update_tx_status($txnid, 1, $pg_response);
                // Redirect to Failure URL
            }else{
                $txnid = $txn_info['transaction_id'];
                $pg_response = $txn_data;
                $pg_response['txn_amount'] = number_format($txn_data['txn_amount'],2,'.','');
                $transaction_record = $this->_update_tx_status($txnid, 2, $pg_response);
            }
        }elseif(isset($txn_data['status']) && in_array(strtoupper($txn_data['status']),$this->failed_status)){
            
            $txnid = $txn_info['transaction_id'];
            $pg_response = $txn_data;
            $pg_response['txn_amount'] = number_format($txn_data['txn_amount'],2,'.','');
            $transaction_record = $this->_update_tx_status($txnid, 2, $pg_response);
        }
        return true;
    }
}	

    ?>