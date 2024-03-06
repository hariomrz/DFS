<?php

class Paypal extends Common_Api_Controller {

    public $paypal_status = array('completed','authorized','s');
    public $pg_id = 6;
    public $PAYPAL_PG_MODE='';
    public $PAYPAL_CLIENT_ID='';
    public $PAYPAL_SECRET_KEY='';
    public $PAYPAL_METHOD='';
    public $PAYPAL_EMAIL='';
    public $PAYPAL_USERNAME='';
    public $PAYPAL_PASSWORD='';
    public $PAYPAL_SIGNATURE='';
    public $currency='';
    
    function __construct() {
        parent::__construct();

        if(isset($this->app_config['allow_paypal']) && $this->app_config['allow_paypal']['key_value'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, paypal not enabled. please contact admin.";
            $this->api_response();
        }

        $this->PAYPAL_PG_MODE       = $this->app_config['allow_paypal']['custom_data']['pg_mode'];
        $this->PAYPAL_CLIENT_ID     =$this->app_config['allow_paypal']['custom_data']['client_id'];
        $this->PAYPAL_SECRET_KEY    =$this->app_config['allow_paypal']['custom_data']['secret_key'];
        $this->PAYPAL_METHOD        =$this->app_config['allow_paypal']['custom_data']['method'];
        $this->PAYPAL_EMAIL         =$this->app_config['allow_paypal']['custom_data']['email'];
        $this->PAYPAL_USERNAME      =$this->app_config['allow_paypal']['custom_data']['username'];
        $this->PAYPAL_PASSWORD      =$this->app_config['allow_paypal']['custom_data']['password'];
        $this->PAYPAL_SIGNATURE     =$this->app_config['allow_paypal']['custom_data']['signature'];
        $this->currency             = $this->app_config['currency_abbr']['key_value'];//[EX : INR,USD]

        $this->finance_lang = $this->lang->line("finance");
    }

    function index() {
        redirect();
    }

    /**
     * @Method deposit_post
     * @uses method to deposit with paypal
     * @since 15th May 2017
     * @author Ankit Patidar <>
     * *** */
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
        $phoneno = isset($post_data['phone_no']) ? $post_data['phone_no'] : '1234567890';
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

        // GET transaction ID from Database after generating an order
        //echo $this->pg_id;exit;
          $_POST['payment_gateway_id']= $this->pg_id;   
        $txnid = $this->Finance_model->_generate_order_and_tx($amount, $user_id, $email, $phoneno, $post_data['product_info'], $post_data['surl'], $post_data['furl'], $promo_code,$deal);
        
        $gst_data = gst_calculate($amount,$this->app_config);
        $amount = isset($gst_data['amount'])?$gst_data['amount']:$amount;

        $return_url = USER_API_URL.'paypal/express_checkout';
        $cancel_url = USER_API_URL.'paypal/cancel?cm='.$txnid.'&amt='.$amount.'&cc='.$this->currency;
        $notify_url = USER_API_URL.'paypal/ipn';

        $param = array(
            'return' => $return_url,
            'cancel_return' => $cancel_url,
            'business' => $this->PAYPAL_EMAIL,
            'item_name' => $post_data['product_info'],
            'item_number' => $txnid,
            'amount' => $amount,
            'currency_code' => $this->currency,
            'custom' => $txnid,
            'notify_url'=>WEBSITE_URL.'user/paypal/process_ipn'
        );
        //echo "<pre>";print_r($param);die;
        $this->load->library('PaypalStandard',$param);
        $paypal_standard = new PaypalStandard($param);
        $redirectUrl = $paypal_standard->get_redirect_url($this->PAYPAL_PG_MODE);
        $this->api_response_arry['data']["payment_link"] = $redirectUrl;
        $this->api_response();
    }
    //function to complete transaction after payment success.
    public function express_checkout_get() {
        //echo "<pre>reached";print_r($_REQUEST);die;
        $post_data = $_REQUEST;
        $post_data['txn_date']=format_date('today');
        $furl = FRONT_APP_PATH;
        if (empty($post_data)) {
            redirect($furl, 'location', 301);
        }
        $this->load->model("finance/Finance_model");
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }
        $payment_status = trim(strtolower($post_data['payment_status']));	

        if (in_array($payment_status, $this->paypal_status)) {
            $txnid = $post_data['item_number'];	
            $payment_status = $post_data['payment_status'];	
            $bnktxnid = $post_data['txn_id'];
            $transaction_details = $this->Finance_model->get_single_row('surl,furl,transaction_status', TRANSACTION, array("transaction_id" => $txnid));
            if (!empty($transaction_details)) {
                $furl = $transaction_details['furl'];
            }

            //check txnid is valid or not
            if(!empty($transaction_details) && $transaction_details['transaction_status'] == 1) {
                //echo "here";exit;
                redirect($transaction_details['surl'], 'location', 301);
            }

            $transaction_info = $this->is_valid_transaction($txnid);
            if (!$transaction_info) {
                redirect($furl, 'location', 301);
            }

            // update transaction id in the transaction table beacouse of encase order not completed then we will check trasaction status from cron
            if(!empty($transaction_details) && $transaction_details['transaction_status'] == 0 && !empty($bnktxnid)) {
                $data= array();
                $data['bank_txn_id'] = $bnktxnid;
                $res = $this->Finance_model->update_transaction($data, $txnid);
            }

            $response_data =array();
            $response_data['cm']    = $post_data['item_number'];
            $response_data['tx']    = $post_data['txn_id'];
            $response_data['amt']   = $post_data['payment_gross'];
            $response_data['cc']    = $post_data['mc_currency'];
            $response_data['txn_date'] = format_date('today');


            if(strtolower($this->PAYPAL_METHOD)=='signature' || strtolower($this->PAYPAL_METHOD)=='secret'){
                $success_ack = 'ACK=Success';
                $failure_ack = 'ACK=Failure';
                $config=array(
                    "p_mode"=>$this->PAYPAL_PG_MODE,
                    "p_username"=>$this->PAYPAL_USERNAME,
                    "p_password"=>$this->PAYPAL_PASSWORD,
                    "p_signature"=>$this->PAYPAL_SIGNATURE,
                    "p_client_id"=>$this->PAYPAL_CLIENT_ID,
                    "p_secret"=>$this->PAYPAL_SECRET_KEY,
                );
                $paypal_transaction_info = signature_paypal_validate_transaction($post_data['txn_id'],$config);
                if(strpos($paypal_transaction_info, $success_ack) !== false){
                    $transaction_record = $this->_update_tx_status($txnid, 1, $response_data,$paypal_transaction_info);
                    redirect($transaction_details['surl'], 'location', 301);
                }elseif(strpos($paypal_transaction_info, $failure_ack) == false){
                    redirect($transaction_details['furl'], 'location', 301);
                } else{
                    $transaction_record = $this->_update_tx_status($txnid, 2, $response_data,$paypal_transaction_info);
                    redirect($transaction_details['furl'], 'location', 301);
                }
            } 
        }else {
            $transaction_record = $this->_update_tx_status($txnid, 2, $response_data);
        }
        return true;
    }
    // function to cancel transaction
    public function cancel_get(){
        $furl = FRONT_APP_PATH;
        if (!$this->input->get()) {
            redirect($furl, 'location', 301);
        }
        $this->load->model("finance/Finance_model");
        $post_data = $this->get();
        $post_data['txn_date']=format_date('today');
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }
            //if (in_array($payment_status, $this->paypal_status)) {
        $txnid = $post_data['cm'];
            //$bnktxnid = $post_data['tx'];
        $transaction_details = $this->Finance_model->get_single_row('surl,furl,transaction_status', TRANSACTION, array("transaction_id" => $txnid));
        if (!empty($transaction_details)) {
            $furl = $transaction_details['furl'];
        }
        $transaction_info = $this->is_valid_transaction($txnid);
        if (!$transaction_info) {
            redirect($furl, 'location', 301);
        }
        $transaction_record = $this->_update_tx_status($txnid, 2, $post_data);
        redirect($furl, 'location', 301);
    }
    
    //function to complete when transaction is success but did not return back to site.
    // parameter : as ipn post request
    public function process_ipn_post(){
        $response = $this->input->post();
        $furl = FRONT_APP_PATH;
        if (empty($response)) {
            return false;
        }
        $this->load->model("finance/Finance_model");

        if (LOG_TX) { 
            $this->db->insert(TEST,array('data' => json_encode($response),'data_type'=>3,'added_date' => format_date())); 
        }
        
        $txnid = $response['item_number'];
        $bnktxnid = $response['txn_id'];
        $transaction_details = $this->Finance_model->get_single_row('surl,furl,transaction_status', TRANSACTION, array("transaction_id" => $txnid));
        if (!empty($transaction_details)) {
            $furl = $transaction_details['furl'];
        }

         //check txnid already exist
         if(!empty($transaction_details) && $transaction_details['transaction_status'] == 1) {
            return true;
        };

        $transaction_info = $this->is_valid_transaction($txnid);
        if (!$transaction_info) {
            return false;
        }

        // update transaction id in the transaction table beacouse of encase order not completed then we will check trasaction status from cron
        if(!empty($transaction_details) && $transaction_details['transaction_status'] == 0 && !empty($bnktxnid)) {
            $data= array();
            $data['bank_txn_id'] = $bnktxnid;
            $res = $this->Finance_model->update_transaction($data, $txnid);
        }
        
        $response_data =array();
        $response_data['cm']    = $response['item_number'];
        $response_data['tx']    = $response['txn_id'];
        $response_data['amt']   = $response['payment_gross'];
        $response_data['cc']    = $response['mc_currency'];
        $response_data['txn_date'] = format_date('today');
        $payment_status = trim(strtolower($response['payment_status']));
        // $response_data['txn_date'] = $response['payment_date'];

         // check payment status is complete then update status on the table
         if($payment_status != 'completed'){
            return false; 
        }

        if(strtolower($this->PAYPAL_METHOD)=='signature' || strtolower($this->PAYPAL_METHOD)=='secret'){
            $success_ack = 'ACK=Success';
            $failure_ack = 'ACK=Failure';
            $config=array(
                "p_mode"=>$this->PAYPAL_PG_MODE,
                "p_username"=>$this->PAYPAL_USERNAME,
                "p_password"=>$this->PAYPAL_PASSWORD,
                "p_signature"=>$this->PAYPAL_SIGNATURE,
                "p_client_id"=>$this->PAYPAL_CLIENT_ID,
                "p_secret"=>$this->PAYPAL_SECRET_KEY,
            );
            $paypal_transaction_info = signature_paypal_validate_transaction($response['txn_id'],$config);
            if(strpos($paypal_transaction_info, $success_ack) !== false){
                $transaction_record = $this->_update_tx_status($txnid, 1, $response_data,$paypal_transaction_info);
            }elseif(strpos($paypal_transaction_info, $failure_ack) == false){
                return false;
            } else{
                $transaction_record = $this->_update_tx_status($txnid, 2, $response_data,$paypal_transaction_info);
            }
        }
        else {
            $transaction_record = $this->_update_tx_status($txnid, 2, $response_data);
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


    private function _update_tx_status($transaction_id, $status_type, $paypal_response,$paypal_ack_response='') {
        
        $trnxn_rec = $this->Finance_model->get_transaction($transaction_id);
        
        if ($status_type == 1) {
            if (!empty($trnxn_rec) && $trnxn_rec['transaction_status'] == 0) {
                $txn_id = isset($paypal_response['tx']) ? $paypal_response['tx'] : "";
                $this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'by your recent transaction', 6, $txn_id,$paypal_ack_response);
            }
        }
        // CALL Platform API to update transaction
        $data = array();
        $data['transaction_status'] = $status_type;
        $data['currency']=$paypal_response['cc'];
        $data['gate_way_name'] = "Paypal";
        $data['is_checksum_valid'] = "1";
        $data['txn_id'] = isset($paypal_response['cm']) ? $paypal_response['cm'] : "";
        $data['bank_txn_id'] = isset($paypal_response['tx']) ? $paypal_response['tx'] : "";
        $data['txn_amount'] = isset($paypal_response['amt']) ? $paypal_response['amt'] : 0;
        if ($trnxn_rec['transaction_status'] == 0) {
            $res = $this->Finance_model->update_transaction($data, $transaction_id);
        }
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

    public function express_checkout_post() {
        //echo "<pre>reached";print_r($_REQUEST);die;
        $post_data = $_REQUEST;
        $post_data['txn_date']=format_date('today');
        $furl = FRONT_APP_PATH;
        if (empty($post_data)) {
            redirect($furl, 'location', 301);
        }
        $this->load->model("finance/Finance_model");
        if (LOG_TX) {
            $test_data = json_encode($post_data);
            $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date()));
        }
        $payment_status = trim(strtolower($post_data['payment_status']));	

        if (in_array($payment_status, $this->paypal_status)) {
            $txnid = $post_data['item_number'];	
            $payment_status = $post_data['payment_status'];	
            $bnktxnid = $post_data['txn_id'];
            $transaction_details = $this->Finance_model->get_single_row('surl,furl,transaction_status', TRANSACTION, array("transaction_id" => $txnid));
            if (!empty($transaction_details)) {
                $furl = $transaction_details['furl'];
            }

            //check txnid is valid or not
            if(!empty($transaction_details) && $transaction_details['transaction_status'] == 1) {
                //echo "here";exit;
                redirect($transaction_details['surl'], 'location', 301);
            }

            $transaction_info = $this->is_valid_transaction($txnid);
            if (!$transaction_info) {
                redirect($furl, 'location', 301);
            }

            // update transaction id in the transaction table beacouse of encase order not completed then we will check trasaction status from cron
            if(!empty($transaction_details) && $transaction_details['transaction_status'] == 0 && !empty($bnktxnid)) {
                $data= array();
                $data['bank_txn_id'] = $bnktxnid;
                $res = $this->Finance_model->update_transaction($data, $txnid);
            }

            $response_data =array();
            $response_data['cm']    = $post_data['item_number'];
            $response_data['tx']    = $post_data['txn_id'];
            $response_data['amt']   = $post_data['payment_gross'];
            $response_data['cc']    = $post_data['mc_currency'];
            $response_data['txn_date'] = format_date('today');


            if(strtolower($this->PAYPAL_METHOD)=='signature' || strtolower($this->PAYPAL_METHOD)=='secret'){
                $success_ack = 'ACK=Success';
                $failure_ack = 'ACK=Failure';
                $config=array(
                    "p_mode"=>$this->PAYPAL_PG_MODE,
                    "p_username"=>$this->PAYPAL_USERNAME,
                    "p_password"=>$this->PAYPAL_PASSWORD,
                    "p_signature"=>$this->PAYPAL_SIGNATURE,
                    "p_client_id"=>$this->PAYPAL_CLIENT_ID,
                    "p_secret"=>$this->PAYPAL_SECRET_KEY,
                );
                $paypal_transaction_info = signature_paypal_validate_transaction($post_data['txn_id'],$config);
                if(strpos($paypal_transaction_info, $success_ack) !== false){
                    $transaction_record = $this->_update_tx_status($txnid, 1, $response_data,$paypal_transaction_info);
                    redirect($transaction_details['surl'], 'location', 301);
                }elseif(strpos($paypal_transaction_info, $failure_ack) == false){
                    redirect($transaction_details['furl'], 'location', 301);
                } else{
                    $transaction_record = $this->_update_tx_status($txnid, 2, $response_data,$paypal_transaction_info);
                    redirect($transaction_details['furl'], 'location', 301);
                }
            } 
        }else {
            $transaction_record = $this->_update_tx_status($txnid, 2, $response_data);
        }
        return true;
    }
}

/* End of file paypal.php */
/* Location: ./application/controllers/paypal.php */
