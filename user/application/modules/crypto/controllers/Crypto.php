<?php
 
class Crypto extends Common_Api_Controller {

    public $pg_id = 15;
    function __construct() {
        parent::__construct();

        if(isset($this->app_config['allow_crypto']) && $this->app_config['allow_crypto']['key_value'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = "Sorry, crypto not enabled. please contact admin.";
            $this->api_response();
        }
        $this->finance_lang = $this->lang->line("finance");
        $this->crypto_lang = $this->lang->line('crypto');
    }

    /**
     * @method deposit cash
     * @uses function to add real and bonus cash to user
     * */
    public function deposit_post() {
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|callback_decimal_numeric|callback_validate_deposit_amount');
        $this->form_validation->set_rules('furl', 'furl', 'required');
        $this->form_validation->set_rules('surl', 'surl', 'required');
        $this->form_validation->set_rules('currency_type', 'currency_type', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }


        $post_data = $this->post();
        $surl = $post_data['surl'];
        $furl = $post_data['furl'];
        $cryp = $post_data['currency_type'];
        $amount = $post_data['amount'];
        
        $apk_data = $this->app_config['allow_crypto']['custom_data'];
        $dp_str = isset($apk_data['dp']) ? $apk_data['dp'] : "";
        $cur_key = explode("_",$dp_str);
        if(!in_array($cryp, $cur_key)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = $this->crypto_lang['invalid_currency'];
            $this->api_response();
        }

        //Checking for minimum amount
        // $min_amount = get_crypto_min_amount();
        // if($amount < $min_amount[$cryp]){
            
        //     $msg = str_replace('{min_amount}', $min_amount[$cryp], $this->crypto_lang['crypto_min_amount']);
        //     $msg = str_replace('{currency}', $cryp, $msg);

        //     $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        //     $this->api_response_arry['status'] = FALSE;
        //     $this->api_response_arry['message'] = $msg;
        //     $this->api_response();
        // }

        if(is_float($amount) || strpos($amount, '.') !== false){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = $this->crypto_lang['decimal_no_not_allowed'];
            $this->api_response();
        }

        $product_info = SITE_TITLE . ' deposit via Crypto';

        $email = isset($this->email) && !empty($this->email) ? $this->email : 'test@mail.com';
        $phoneno = isset($this->phone_no) ? $this->phone_no : '1234567890';
        $firstname = isset($this->user_name) ? $this->user_name : 'User';

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
        $txnid = $this->Finance_model->_generate_order_and_tx($amount, $this->user_id, $email, $phoneno, $product_info, $surl, $furl, $promo_code, $deal);
        //$txnid = 593; //for testing

        //Creating a unique transaction id from our end
        $client_tran_id = 'VFNTSY'.$txnid;//
        
        //Creating URL parameters
        $url_params = "?client_tran_id=$client_tran_id&email=$email&phone=$phoneno&cryp=$cryp&amt_ppm=$amount&notify_url=".USER_API_URL.'crypto/notify_transaction';
        $url = $this->app_config['allow_crypto']['custom_data']['crypto_endpoint'].'deposit_req.php'.$url_params;

        //Curl Execution
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET'
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        
        //If log enabled, then saving logs
        if (LOG_TX) {            
            error_log("\n".format_date().' crypto_return: '.$response.'<br>',3,$_SERVER["DOCUMENT_ROOT"].'/cron/application/logs/payment.log');
        }

        //Decoding response
        $res_arr = json_decode($response,true);
        if(!empty($res_arr['status']) && $res_arr['status']=='SUCCESS'){

            //Updating transaction and order status
            $callback_param = array('amount'=>$amount,'response'=>$response,'transaction_id'=>$txnid);
            $this->payment_callback($callback_param);
            $data_arr['deposit_to_addr']=$res_arr['deposit_to_addr'];
            $data_arr['deposit_crypto_amt']=$res_arr['deposit_crypto_amt'];
            $data_arr['deposit_crypto']=$res_arr['deposit_crypto'];
            $data_arr['qr_code']=$res_arr['qr_code'];

            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['message'] = $this->crypto_lang['pending_status'];
            $this->api_response_arry['data'] = $data_arr;
            $this->api_response();
        }else{
            if(!empty($res_arr['status']) && !empty($res_arr['msg'])){
                $this->api_response_arry['message'] = ucwords(strtolower($res_arr['msg']));
            }else{
                $this->api_response_arry['message'] = $this->crypto_lang['something_went_wrong'];
            }
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response();
        }
    }

    /* function for crypto for both success and failed transaction */
    public function payment_callback($data) {

        $this->load->model("finance/Finance_model");
        
        //Decoding josn response from client
        $response = json_decode($data['response'],true);

        $updateTransactionData = array(
            'txn_amount' => $data['amount'],
            'currency' => $response['deposit_crypto'],
            'txn_date' => format_date(),
            'bank_txn_id' => $response['tran_id'],
            'pg_order_id'=> $response['client_tran_id'],
            'gate_way_name'=> 'crypto'
        );

        if ($response["status"] == "SUCCESS") {
            $transaction_details = $this->Finance_model->get_transaction_info($data['transaction_id']);
            $this->Finance_model->update_transaction($updateTransactionData, $data['transaction_id']);
            //Updating api exact response in order table 
            $this->db->where('order_id', $transaction_details['order_id']);
            $this->db->update(ORDER,['custom_data'=>$data['response']]);
            return true;
        } else {
            $transaction_record = $this->_update_tx_status($data['transaction_id'], 2, $updateTransactionData);
            return true;
        }
        
    }

    /**
     * update order status to platform 
     * 
     * @param int $transaction_id Transaction ID
     * @param int $status_type Status Type
     * @return bool Status updated or not
     */
    private function _update_tx_status($transaction_id, $status_type, $update_data = array()) {
        $this->load->model("finance/Finance_model");
        $trnxn_rec = $this->Finance_model->get_transaction($transaction_id);            
        $oder_details = $this->Finance_model->get_pending_order_detail($trnxn_rec["order_id"]);
        if ($status_type == 1) {   // GET order_id from transaction ID
            if (!empty($trnxn_rec)) {
                $this->update_order_status($trnxn_rec["order_id"], $status_type, $transaction_id, 'by your recent transaction', $this->pg_id);
            }
        }

        $data = array();
        $data['transaction_status'] = $status_type;
        $data['payment_gateway_id']=$this->pg_id;
        $data['is_checksum_valid'] = "1";        

        if ($trnxn_rec['transaction_status'] == 0) {
            $res = $this->Finance_model->update_transaction($data, $transaction_id);

            $ord_cus_data = json_decode($oder_details['custom_data'],true);
            $ord_cus_data['hash_code'] = $update_data['hash_code'];
            $ord_cus_data['issue_ppm'] = $update_data['issue_ppm'];

            $this->db->where('order_id',$oder_details['order_id']);
            $this->db->update(ORDER,array('custom_data'=>json_encode($ord_cus_data)));
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
     * This is webhook, this is executed from client end to update the status of crypto transaction
     **/
    function notify_transaction_post(){
        $tran_id = $this->input->post('tran_id');
        $client_tran_id = $this->input->post('client_tran_id');
        $status = $this->input->post('status');
        $hash_code = $this->input->post('hash_code');
        $issue_ppm = $this->input->post('issue_ppm');

        //Saving response in log file
        $response = json_encode($this->input->post());
        error_log("\n".format_date().' crypto_notify_post: '.$response.'<br>',3,$_SERVER["DOCUMENT_ROOT"].'/cron/application/logs/payment.log');

        $this->load->model("crypto/Crypto_model");
        $transaction_details = $this->Crypto_model->get_transaction_info($tran_id,$client_tran_id,'order_id,transaction_id,transaction_status');

        if($transaction_details['transaction_status']!=0){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = $this->crypto_lang['transaction_already_process'];
            $this->api_response();
        }

        if(!empty($transaction_details['transaction_id'])){
            $transaction_id = $transaction_details['transaction_id'];

            if($status=='SUCCESS'){

               $this->_update_tx_status($transaction_id, 1,array('hash_code'=>$hash_code,'issue_ppm'=>$issue_ppm));

                $this->api_response_arry['message'] = $this->crypto_lang['transaction_updated_successfully'];
                $this->api_response();

            }elseif($status=='FAILED'){
                //Updating status 2 for failed transaction
               $this->_update_tx_status($transaction_id, 2);

                $this->api_response_arry['message'] = $this->crypto_lang['transaction_updated_successfully'];
                $this->api_response();
            }elseif($status=='PENDING'){
                $this->api_response_arry['message'] = $this->crypto_lang['transaction_updated_successfully'];
                $this->api_response();
            }else{
                $this->api_response_arry['message'] = $this->crypto_lang['invalid_status'];
                $this->api_response();
            }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['status'] = FALSE;
            $this->api_response_arry['message'] = $this->crypto_lang['invalid_transaction_id_or_client'];
            $this->api_response();
        }
    }

}
