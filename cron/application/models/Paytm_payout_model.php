<?php 
class Paytm_payout_model extends MY_Model {
    
    public $db_user ;
    public function __construct()  {
       	parent::__construct();
        $this->db_user		= $this->load->database('db_user', TRUE);
        $this->lang->load('general', $this->config->item('language'));
    }
    
    public function process_pending_paytm_withdraw_order() {
		$current_date = format_date();
		$process_date_time = date('Y-m-d H:i:s',strtotime('-3 minutes',strtotime($current_date)));
        $last_date_time = date('Y-m-d H:i:s',strtotime('-24 hours',strtotime($current_date)));
        $this->db = $this->db_user;
        $this->db->select("O.order_id, O.order_unique_id, O.winning_amount, O.user_id, O.source, O.date_added, O.withdraw_method, T.transaction_id, T.txn_date")
                    ->from(ORDER. " O")
                    ->join(TRANSACTION. " T","T.order_id = O.order_id AND T.transaction_status=3 AND T.payment_gateway_id=2")
                    ->where("O.status",0)
                    ->where("O.source",8)
                    ->where("O.type",1)
                    ->where("O.date_added <= ",$process_date_time)
                    ->where("O.date_added >= ",$last_date_time)
                    ->order_by("O.order_id","DESC")
                    ->limit(10);

        $query = $this->db->get();
		$result = $query->result_array();
		$paytm_params = array();
        
        $this->load->helper('queue_helper');
		foreach($result as $order) {
            $order['action'] = "disburse_status_query";
			add_data_in_queue($order, 'paytm_payout'); 
		}
    }
    
    /**
     * used to check order disburse status​ 
     * 
     * @param int $order order DETAILS 
     */
    function disburse_status_query($order) {
        $this->db = $this->db_user;
        $transaction_data = $this->get_single_row('transaction_id', TRANSACTION, array("transaction_id" => $order["transaction_id"], "transaction_status" => 3, "payment_gateway_id" => 2));
        if(!empty($transaction_data)) {
            $this->load->helper('paytm_payout_helper');
            $paytm_params["orderId"] = $order['order_unique_id'];

            $txn_date = $order['txn_date'];
            $post_data = json_encode($paytm_params, JSON_UNESCAPED_SLASHES);
            /*
            * Generate checksum by parameters we have in body
            */
            $checksum = generateSignature($post_data, PAYTM_PAYOUT_MERCHANT_KEY);

            
            $x_mid      = PAYTM_PAYOUT_MERCHANT_MID;
            $x_checksum = $checksum;

            $headers = array("x-mid: " . $x_mid, "x-checksum: " . $x_checksum);
            $url = PAYTM_PAYOUT_API_URL."/query";
            
            if (LOG_TX) {
                $test_data_param = $paytm_params;
                $test_data_param['checksum '] = $checksum;
                $test_data_param['headers '] = $headers;
                $test_data_param['url '] = $url;
                $test_data = json_encode($test_data_param);
                $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "1"));
            }

            
            $payout_query_response = callPayoutAPI($url, $post_data, $headers);
            log_message('error', 'Cron Disburse Status Query ​API - ' . json_encode($payout_query_response));
            if($payout_query_response) {
                $status 		= $payout_query_response['status'];
                $status_code 	= $payout_query_response['statusCode'];
                $status_message = $payout_query_response['statusMessage'];
                $result 		= $payout_query_response['result'];

                $status_code 	= str_replace('DE_', '', strtoupper($status_code));
                $status_code    = trim($status_code);
                $txn_id 		= (isset($result['paytmOrderId'])) ? $result['paytmOrderId'] : 0;
                $txn_amount 	= (isset($result['amount'])) ? $result['amount'] : 0 ;
                $transaction_status = 0;
                $status = strtoupper($status);
                if($status == 'FAILURE') {
                    if(in_array($status_code, array(500, 602, 623, 636, 640, 648, 901))) {
                        $transaction_status = 3;
                    } else {
                        $transaction_status = 4;
                    }
                }
                if($status == 'SUCCESS') {
                    $transaction_status = 5;
                }

                if(!empty($txn_date) && $transaction_status == 0) {
                    $timestamp1 = strtotime($current_date);
                    $timestamp2 = strtotime($txn_date);
                    $hour = abs($timestamp1 - $timestamp2)/(60*60);
                    if($hour > 24) {
                        $transaction_status = 4;
                    }
                }
                
                $update_transaction_data = array(
                    'txn_amount' => $txn_amount,
                    'txn_id' => $txn_id,
                    'responce_code' => $status_code,
                    'transaction_message' => $status_message,
                    'transaction_status' => $transaction_status
                );
                $this->_update_payout_tx_status($order, $update_transaction_data);                                    
            }
        }		
    } 
    
    /**
     * used to create bank transfer order​ 
     * 
     * @param int $order order DETAILS 
     */
    function paytm_payout($data) {

        $this->db = $this->db_user;
        $this->db->select("O.order_id, O.order_unique_id, O.winning_amount, O.user_id, O.source, O.date_added, O.custom_data, T.transaction_id, T.txn_date")
                    ->from(ORDER. " O")
                    ->join(TRANSACTION. " T","T.order_id = O.order_id AND T.transaction_status=3 AND T.payment_gateway_id=2")
                    ->where("O.status",0)
                    ->where("O.source",8)
                    ->where("O.type",1)
                    ->where("O.order_id",$data['order_id'])
                    ->limit(1);

        $query = $this->db->get();
        $num = $query->num_rows();
        if ($num > 0) {
            $order_data = $query->row_array();
            $custom_data = $order_data['custom_data'];
            $bank_details = json_decode($custom_data,TRUE);
            $ac_number = isset($bank_details['ac_number']) ? $bank_details['ac_number'] : '';
            $ifsc_code = isset($bank_details['ifsc_code']) ? $bank_details['ifsc_code'] : '';

            if(empty($ac_number) || empty($ifsc_code)) {
                log_message('error', 'Bank Transfer API: Bank detail empty for Order ID - '.$order_data['order_id']);
                return true;
            }

            $paytm_params = array();

            $paytm_params["subwalletGuid"]      = PAYTM_PAYOUT_DISBURSAL_ACCOUNT_GUID;
            $paytm_params["orderId"]            = $order_data['order_unique_id'];
            $paytm_params["transferMode"]       = 'IMPS';
            $paytm_params["purpose"]            = "OTHERS";
            $paytm_params["date"]               = date("Y-m-d",strtotime(format_date()));
            $paytm_params["callbackUrl"]        = 'https://'.SERVER_NAME.PROJECT_FOLDER_NAME.'/user/paytm/payout_callback';
            $paytm_params["amount"]             = ($bank_details['pg_fee'] > 0 && $bank_details['isIW']==1) ? ($order_data['winning_amount']-$bank_details['pg_fee']) : $order_data['winning_amount'];

            $paytm_params["beneficiaryAccount"] = $ac_number;
            $paytm_params["beneficiaryIFSC"]    = $ifsc_code;
            
            $post_data = json_encode($paytm_params, JSON_UNESCAPED_SLASHES);
            
            $this->load->helper('paytm_payout_helper');

            /*
            * Generate checksum by parameters we have in body
            */
            $checksum = generateSignature($post_data, PAYTM_PAYOUT_MERCHANT_KEY);        

            $x_mid      = PAYTM_PAYOUT_MERCHANT_MID;
            $x_checksum = $checksum;
            $headers = array("x-mid: " . $x_mid, "x-checksum: " . $x_checksum);
            $url = PAYTM_PAYOUT_API_URL."/bank";
            if (LOG_TX) {
                $test_data_param = $paytm_params;
                $test_data_param['checksum '] = $checksum;
                $test_data_param['headers '] = $headers;
                $test_data_param['url '] = $url;
                $test_data = json_encode($test_data_param);
                $this->db->insert(TEST, array('data' => $test_data, 'added_date' => format_date(), "data_type" => "1"));
            }
            
            $payout_response = callPayoutAPI($url, $post_data, $headers);
            log_message('error', 'Bank Transfer API - ' . json_encode($payout_response));

            $status = isset($payout_response['status']) ? $payout_response['status'] : 'FAILURE';
            $status_message = isset($payout_response['statusMessage']) ? $payout_response['statusMessage'] : 'Blank Response from Paytm';
            $status_code = isset($payout_response['statusCode']) ? $payout_response['statusCode'] : 'Blank status code from Paytm';
            $status = trim(strtoupper($status));
            $status_array = array('SUCCESS', 'ACCEPTED');
            if(!in_array($status, $status_array)) {
                $status_code = str_replace('DE_', '', strtoupper($status_code));
                $status_code    = trim($status_code);            
                $transaction_status = 4;
                $update_transaction_data = array(
                    'responce_code' => $status_code,
                    'transaction_message' => $status_message,
                    'transaction_status' => $transaction_status
                );
                $this->_update_payout_tx_status($order_data, $update_transaction_data);
                log_message('error', 'Bank Transfer API: Failed for order ID - '.$order_data['order_id']);
            }  
        } else {
                log_message('error', 'Bank Transfer API: Order detail empty for order ID - '.$data['order_id']);
                return true;
        }
    }

	/**
     * update order status to platform
     * 
     * @param int $order_detail order DETAILS 
     * @param array $update_data update data
     * @return bool Status updated or not
     */
    private function _update_payout_tx_status($order_detail, $update_data = array()) {
        $order_id = $order_detail['order_id'];
        $this->_update_transaction($update_data, $order_detail['transaction_id']);
        $status_type = $update_data['transaction_status'];
        if(!empty($status_type)) {
            $reason = "";
            if ($status_type == 1 || $status_type == 5) {
                $status_type = 1;
            } else if ($status_type == 3) {
                $status_type = 0;
            } else if ($status_type == 4 && $order_detail['source'] == 8) {
                $reason = $this->lang->line('withdraw_failed');
                $status_type = 2;
                $this->update_user_balance($order_detail["user_id"], $order_detail, 'add');

                $user_cache_key = "user_balance_".$order_detail["user_id"];
                $this->delete_cache_data($user_cache_key);
            }
    
            $source_id = 0;

            $this->_update_order_status($order_id, $status_type, $source_id, 'by your recent withdraw');
        
            $user_data = $this->get_single_row('user_name, email', USER, array("user_id" => $order_detail["user_id"]));
                
            $msg_content = array(
                "amount"    => $order_detail['winning_amount'],
                "reason"    => $reason, 
                "user_id"   => $order_detail['user_id'],
                "cash_type" => "0",
                "plateform" => "1",
                "source"    => "7",
                "source_id" => "0",
                "date_added"=> $order_detail['date_added']
            );
            $msg_content["payment_option"] = 'PayTm'; 
           
        
            // SOME CONFUSING BECAUSE STATUS IN DB IS DIFFER
            if($status_type == 1 || $status_type == 5) {
                $notify_data["notification_type"] = 25; // 25-ApproveWithdrawRequest
            }
            
            if($status_type == 2 || $status_type == 4) {
                $notify_data["notification_type"] = 26; // 26-RejectWtihdrawRequest
            }
            $today = format_date();
            $notify_data["source_id"] = 0;
            $notify_data["notification_destination"] = 7; //  Web, Push, Email
            $notify_data["user_id"] =  $order_detail['user_id'];
            $notify_data["to"] = $user_data['email'];
            $notify_data["user_name"] = $user_data['user_name'];
            $notify_data["added_date"] = $today;
            $notify_data["modified_date"] = $today;
            $notify_data["content"] = json_encode($msg_content);

            if($status_type == 1 || $status_type == 5) {
                $notify_data["subject"] = $this->lang->line("withdraw_email_approve_subject");                
            } else {
                $notify_data["subject"] = $this->lang->line("withdraw_email_reject_subject");
            }
			
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($notify_data);            
        }
	}
	
	 /**
     * Used to update transaction data
     * @param array $data
     * @param int $transaction_id
     * @return int
     */
    private function _update_transaction($data, $transaction_id) {

        $this->db_user->where('transaction_id', $transaction_id)->update(TRANSACTION, $data);
	}
	

	/**
     * Function to Update user balance
     *  Params: $user_id,$real_balance,$bonus_balance
     *  
     */
    function update_user_balance($user_id, $balance_arr, $oprator='add')
    {
        if(empty($balance_arr)){
            return false;
        }
        if(isset($balance_arr['real_amount']) && $balance_arr['real_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('balance', 'balance - '.$balance_arr['real_amount'], FALSE);
            }else{
                $this->db_user->set('balance', 'balance + '.$balance_arr['real_amount'], FALSE);
            }
            if(isset($balance_arr['source']) && $balance_arr['source'] == "7" && $oprator == 'add'){
                $this->db->set('total_deposit', 'total_deposit + '.$balance_arr['real_amount'], FALSE);
            }
        }
        if(isset($balance_arr['bonus_amount']) && $balance_arr['bonus_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('bonus_balance', 'bonus_balance - '.$balance_arr['bonus_amount'], FALSE);
            }else{
                $this->db_user->set('bonus_balance', 'bonus_balance + '.$balance_arr['bonus_amount'], FALSE);
            }
            $this->load->helper('queue_helper');
            $bonus_data = array('oprator' => $oprator, 'user_id' => $user_id, 'total_bonus' => $balance_arr['bonus_amount'], 'bonus_date' => format_date("today", "Y-m-d"));
            add_data_in_queue($bonus_data, 'user_bonus');
        }
        if(isset($balance_arr['winning_amount']) && $balance_arr['winning_amount'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('winning_balance', 'winning_balance - '.$balance_arr['winning_amount'], FALSE);
            }else{
                $this->db_user->set('winning_balance', 'winning_balance + '.$balance_arr['winning_amount'], FALSE);
            }
            if(isset($balance_arr['source']) && $balance_arr['source'] == "3" && $oprator == 'add'){
                $this->db_user->set('total_winning', 'total_winning + '.$balance_arr['winning_amount'], FALSE);
            }
        }
        if(isset($balance_arr['points']) && $balance_arr['points'] > 0 ){
            if($oprator=='withdraw'){
                $this->db_user->set('point_balance', 'point_balance - '.$balance_arr['points'], FALSE);
            }else{
                $this->db_user->set('point_balance', 'point_balance + '.$balance_arr['points'], FALSE);
            }
            $this->load->helper('queue_helper');
            $coin_data = array('oprator' => $oprator, 'user_id' => $user_id, 'total_coins' => $balance_arr['points'], 'bonus_date' => format_date("today", "Y-m-d"));
            add_data_in_queue($coin_data, 'user_coins');
        }
        $this->db_user->where('user_id', $user_id);
        $this->db_user->update(USER);
        return $this->db_user->affected_rows();  
	}
	
	/**  Used to update order status from pending to failed or complete
     * @param int $order_id
     * @param int $status
     * @param int $source_id
     * @param string $reason
     * @return int
     */
    private function _update_order_status($order_id, $status, $source_id = 0, $reason = '') {
        $data = array(
            "status" => $status,
            "source_id" => $source_id,
            "modified_date" => format_date(),
            "reason" => $reason
        );
        $this->db_user->where('order_id', $order_id)
                ->update(ORDER, $data);
    }
}