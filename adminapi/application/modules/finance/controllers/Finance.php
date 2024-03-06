<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Finance extends MYREST_Controller {
     public $finance_lang;
     public $transaction_source_key_map = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Finance_model');
		$_POST = $this->input->post();
        $this->finance_lang = $this->lang->line("finance");
        //1=> join game
        $this->transaction_source_key_map[1] =array('key' => 'contest_name');
        $this->transaction_source_key_map[370] =array('key' => 'name');
        $this->transaction_source_key_map[371] =array('key' => 'name');
        $this->transaction_source_key_map[460] =array('key' => 'contest_name');
        $this->admin_roles_manage($this->admin_id,'manage_finance');
	}

    public function index()
	{
		$this->load->view('layout/layout', $this->data, FALSE);
	}

    public function get_all_withdrawal_request_post()
    {
        $result = $this->Finance_model->get_all_withdrawal_request();
        $result['summary'] = $this->Finance_model->get_withdrawal_summary();
        $result['auto_withdrawal'] = isset($this->app_config['auto_withdrawal'])?$this->app_config['auto_withdrawal']['key_value']:0;
        if($this->input->post('csv'))
        {
            if(!empty($result['result'])){
                $result =$result['result'];
                $header = array_keys($result[0]);
                $camelCaseHeader = array_map("camelCaseString", $header);
                $result = array_merge(array($camelCaseHeader),$result);
                $this->load->helper('csv');

                $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                $this->api_response_arry['status']          = TRUE;
                $this->api_response_arry['message']         = '';
                $this->api_response_arry['data']            = array_to_csv($result);
                $this->api_response();
            }
            //$this->load->helper('download');
            //$data = $this->dbutil->csv_from_result($query);
            //$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "From Date $from_date\nTo Date $to_date\n\n" . html_entity_decode($data);
            //$name = 'file.csv';
            //force_download($name, $data);
        }
        else
        {
            $this->api_response_arry['service_name']  = "get_all_withdrawal_request";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data']          = $result;
            $this->api_response();
        }
    }

    public function get_all_withdrawal_request_get()
    {
        $_POST = $this->input->get();
        
        //convert date to client timezone
        $result = $this->Finance_model->get_all_withdrawal_request();

            if(!empty($result['result'])){
                $result =$result['result'];
                $header = array_keys($result[0]);
                $camelCaseHeader = array_map("camelCaseString", $header);
                $result = array_merge(array($camelCaseHeader),$result);
                $this->load->helper('csv');
                array_to_csv($result,'Withdraw_list.csv');
                
            }
    }

    public function change_withdrawal_status_post()
    {
        $this->form_validation->set_rules('order_id', 'Payment Withdraw order id', 'trim|required');
        $this->form_validation->set_rules('status', 'Status', 'trim|required');

        if($this->input->post("status") == 2){
            $this->form_validation->set_rules('reason', 'Reason', 'trim|required');
        }
        
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }
        $status = $this->input->post("status");
        $post = array('order_id'=>$this->input->post("order_id"),"status"=>$this->input->post("status"),"source_id"=>0,"reason"=>$this->input->post("reason"));

        $order_result = $this->Finance_model->get_order_detail($this->input->post("order_id"));
        $user_id      = $order_result['user_id'];       
        $user_data    = $this->Finance_model->get_single_row('balance, winning_balance,email,user_name', USER, array("user_id"=> $user_id));
        
        $this->Finance_model->change_withdrawal_status($post);
        if($status !=1 && $order_result['status'] !=2) {
            $this->Finance_model->update_withdraw_reject_balance($order_result['order_id']);
            
            //delete user profile infor from cache
            $user_cache_key = "user_balance_".$user_id;
            $this->delete_cache_data($user_cache_key);
        }
        
        if($order_result)
        {
            
            $msg_content = array("amount"=>$order_result['winning_amount'],"reason"=>$post["reason"],"user_id"=>$order_result['user_id'],"cash_type"=>"0","plateform"=>"1","source"=>"7","source_id"=>"0","date_added"=>$order_result['date_added']);
            if($order_result["withdraw_method"] == 1)
            { 
                $msg_content["payment_option"] = 'Bank'; 
            }
            if($order_result["withdraw_method"] == 2)
            {
                $msg_content["payment_option"] = 'PayTm'; 
            }
            if($order_result["withdraw_method"] == 3)
            { 
                $msg_content["payment_option"] = 'Paypal';
            }
          
            // SOME CONFUSING BECAUSE STATUS IN DB IS DIFFER
            $status == 1 && $tmp["notification_type"] = 25; // 25-ApproveWithdrawRequest
            $status == 2 && $tmp["notification_type"] = 26; // 26-RejectWtihdrawRequest

            $tmp["source_id"] = 0;
            $tmp["notification_destination"] = 7; //  Web, Push, Email
            $tmp["user_id"] =  $order_result['user_id'];
            $tmp["to"] = $user_data['email'];
            $tmp["user_name"] = $user_data['user_name'];
            $tmp["added_date"] = date("Y-m-d H:i:s");
            $tmp["modified_date"] = date("Y-m-d H:i:s");
            $tmp["content"] = json_encode($msg_content);

            if($status == 1)
            {
                $tmp["subject"] = $this->finance_lang["withdraw_email_approve_subject"];
                
            }else{
                $tmp["subject"] = $this->finance_lang["withdraw_email_reject_subject"];
            }

            $this->load->model('user/User_nosql_model');
		    $this->User_nosql_model->send_notification($tmp);


            $this->api_response_arry['service_name']  = "change_withdrawal_status";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['message']       = $this->lang->line('update_status_success');
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['service_name']  = "change_withdrawal_status";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']       = $this->lang->line('no_change');
            $this->api_response();
        }
    }

    public function get_filter_data_post()
    {
        $withdrawal_type = array(
            "Bank" => "1",
            //"Paypal" => "3",
            "Mpesa" => "5",
            "Razorpay" => "8",
            "Cashfree" => "17"
        );
        $withdrawal_type_list = array(
            "All"       => "",
            "Manual"    => "0"
        );
        if($this->app_config['auto_withdrawal']['key_value'] == 1)
        {
            $withdrawal_type_list['Auto'] = "1";
        }
        $withdrawal_status = array(
            "Pending"           => "0",
            "Approved"          => "1",
            "Rejected"          => "2",
            "Instant Pending"   => "3",
            "Instant Reject"    => "4",
            "Instant Approved"  => "5"
        );
        $manual_status = array(
            "Pending"   => "0",
            "Approved"  => "1",
            "Rejected"  => "2"
        );

        $result = array('withdrawal_type'=>$withdrawal_type,'status'=>$withdrawal_status);

        $result['withdrawal_method'] = $withdrawal_type_list;
        $result['manual_status'] = $manual_status;

        $this->api_response_arry['data'] = $result;
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['message'] = '';
        $this->api_response();
    }

    public function dummy_email_get($status=1)
    {
        $status        = $status;
        $user_language = 'portuguese';

        //$this->load->lang('english');
        $email_action = ($status == 1) ? $this->lang->line('withdrawal_request_approved_'.$user_language) : $this->lang->line('withdrawal_request_rejected_'.$user_language);

        //send email regarding withdrawal request to user's email
        $data['full_name']     = "Vinfo User";
        $data['email']         = 'rahulp.vinfotech@gmail.com';
        $data['user_language'] = 'portuguese';
        $data['status']        =  $status;
        $data['message']       = str_replace("{action}", $email_action, $this->lang->line('withdrawal_request_message_'.$user_language));
        $data['message']       = str_replace("{withdrawal_amount}", 200,$data['message']);
        $message               = $this->load->view('emailer/withdrawal_request',$data,true);
        $to                    = "rahulp.vinfotech@gmail.com";
        $subject               = str_replace("{action}", $email_action,$this->lang->line('withdrawal_request_subject_'.$user_language));;
        $message               = $message;
        //echo $message;exit;
        //$this->send_email($to,$subject,$message);
        echo $message;exit;
    }

    private function get_transaction_msg()
    {
        $cache_key = "transaction_msg_list";
        $transaction_by_source= array();
        $transaction_by_source = $this->get_cache_data($cache_key);
        if(empty($transaction_by_source))
        {
            $this->load->model('auth/Auth_nosql_model');
            $transaction_msgs =  $this->Auth_nosql_model->select_nosql(COLL_TRANSACTION_MESSAGES);
            if(!empty($transaction_msgs))
            {
                $transaction_by_source = array_column($transaction_msgs,NULL,'source');
            }
            $this->set_cache_data($cache_key,$transaction_by_source, REDIS_30_DAYS);
        }
       
        return $transaction_by_source;
       
    }

//  Payment Transaction

    public function get_all_transaction_post()
    {   
        $result     = $this->Finance_model->get_all_transaction();
        $final_result = array();
        $lineup_master_contest_ids = array();
        $transaction_msg = array();
        if(!empty($result['result']))
        {
            $transaction_messages = $this->get_transaction_msg();
            if(!empty($transaction_messages)){
                $trxn_msg = array_column($transaction_messages,'en_message','source');
                $r_source_keys = array_flip(array_unique(array_column($result['result'],'source')));
                $transaction_msg = array_intersect_key($trxn_msg,$r_source_keys);
            }
            
            $final_result = $result['result'];
            
            foreach ($result['result'] as $key => $rs) {
                $result['result'][$key]['merchandise'] = '-';
                $transaction_event = isset($transaction_msg[$rs['source']]) ? $transaction_msg[$rs['source']] : '';
                $customData = json_decode($rs['custom_data'],TRUE);
                $result['result'][$key]['custom_data']=$customData;
                if(isset($customData['merchandise'])){
                    $result['result'][$key]['merchandise'] = $customData['merchandise'];
                }
                
                if(!empty($transaction_event)){
                    // replace {{somthing}}
                    $transaction_event = preg_replace_callback('/\{\{([\w]+)\}\}/', function($matches) use ($customData) {
                        $key = $matches[1];
                        if (isset($customData[$key])) {
                            return $customData[$key];
                        }
                        return $matches[0];
                    }, $transaction_event);
                }
                $result['result'][$key]['trans_desc']=$transaction_event;
                $final_result[$key] = $result['result'][$key];
            }
        }
        
        $result['result'] = $final_result;
        $result['total'] = $result['total'];
        
        if(isset($result['result']))
        {
            $this->api_response_arry['service_name']  = "get_all_transaction";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data']          = $result;
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['service_name']  = "get_all_transaction";
            $this->api_response_arry['data']          = $result;
            $this->api_response();
        }

    }


     public function get_all_transaction_get()
    {

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');


        $_POST      = $this->input->get();
        
        $result     = $this->Finance_model->export_transaction(); 
        $this->load->model('contest/Contest_model');
        $transaction_msg = array();
        $transaction_messages = $this->get_transaction_msg();
        if(!empty($transaction_messages)){
            $trxn_msg = array_column($transaction_messages,'en_message','source');
            $r_source_keys = array_flip(array_unique(array_column($result['result'],'source')));
            $transaction_msg = array_intersect_key($trxn_msg,$r_source_keys);
        }
        
        foreach ($result['result'] as $key => $rs) {
        
            $result['result'][$key]["Event"] = "-";
            $result['result'][$key]["fixture_name"] = "-";
            $result['result'][$key]["contest_name"] = "-";
            $result['result'][$key]["match_date"] = "-";
            $result['result'][$key]["contest_type"] = "-";
            $result['result'][$key]['merchandise'] = '-';
            
            $transaction_source = isset($transaction_msg[$rs['source']]) ? $transaction_msg[$rs['source']] : "";
            if(!empty($rs['custom_data']))
            {
                $customData = json_decode($rs['custom_data'],TRUE);
                $rs['custom_data']=$customData;

                if(isset($customData['merchandise'])){
                    $result['result'][$key]['merchandise'] = $customData['merchandise'];
                }


                
                $event_msg = '-';
                if(!empty($transaction_source)){
                    // replace {{somthing}}
                    $transaction_source = preg_replace_callback('/\{\{([\w]+)\}\}/', function($matches) use ($customData) {
                        $key = $matches[1];
                        if (isset($customData[$key])) {
                            return $customData[$key];
                        }
                        return $matches[0];
                    }, $transaction_source);
                }
                $result['result'][$key]['Event']=$transaction_source;
            }else{
                $result['result'][$key]['Event']=$transaction_source;
            }

            switch ($rs['type']){
                case 0:
                   $result['result'][$key]['type'] = 'Credit';
                break;
                case 1:
                    $result['result'][$key]['type'] = 'Debit';
                break;
            }
            
             $user_revenue = array();

            if($rs['status']==1){
                $result['result'][$key]['status'] = "Completed";
            }else if($rs['status']==2){
                $result['result'][$key]['status'] = "Failed";
            }else{
                $result['result'][$key]['status'] = "Pending";
            }
            unset( $result['result'][$key]['source_id']);
            unset( $result['result'][$key]['source']);
            unset( $result['result'][$key]['custom_data']);
        }
        
        
        if(!empty($result['result'])){
            $result =$result['result'];
            $header = array_keys($result[0]);
            $camelCaseHeader = array_map("camelCaseString", $header);
            $result = array_merge(array($camelCaseHeader),$result);
            $this->load->helper('csv');
            array_to_csv($result,'Transaction_list.csv');
        }
    }
    public function get_all_descriptions_post()
    {

        $result = $this->Finance_model->get_all_descriptions();
        $this->response(array(config_item('rest_status_field_name')=>TRUE, 'data'=>$result) , rest_controller::HTTP_OK);
    }

    public function update_withdrawal_status_post() {
        //$this->form_validation->set_rules('order_id', 'Payment Withdraw order id', 'trim|required');
        $this->form_validation->set_rules('status', 'Status', 'trim|required|in_list[1,2,3]');
        //1-success,2-Failed,3-Instant Pending  
        if($this->input->post("status") == 2){
            $this->form_validation->set_rules('reason', 'Reason', 'trim|required');
        }
        
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }
        try {
            $post_input = $this->input->post();

            $status = $post_input['status'];
            $order_ids = isset($post_input['order_id']) ? $post_input['order_id'] : array();
            $reason = isset($post_input['reason']) ? $post_input['reason'] : '';
            if(empty($order_ids)) {
                throw new Exception('Please provide payment withdraw order ids');
            }
            if(!is_array($order_ids)) {
                throw new Exception('Please provide payment withdraw order ids');
            }
            $this->load->helper('queue_helper');
            foreach ($order_ids as $key => $order_id)
            {
                $order_result = $this->Finance_model->get_order_detail($order_id);
                $user_detail = $this->Finance_model->get_user_by_id($order_result['user_id']);
                $txn_id =  $this->Finance_model->get_single_row('transaction_id', TRANSACTION, array("order_id"=> $order_id));
                $txn_id = $txn_id['transaction_id']; 
                $order_result['custom_data'] = json_decode($order_result["custom_data"], true);
                $post = array('order_id'=>$order_id, "status"=>$status, "source_id"=>0, "reason"=>$reason);

                if($status == 3 && isset($order_result['custom_data']['is_auto_withdrawal']) && $order_result['custom_data']['is_auto_withdrawal'] == 1)
                { //for instant approve   
                    
                    $amount = $order_result['winning_amount'];
                    $tds = isset($order_result['tds']) ? $order_result['tds'] : 0;
                    if(isset($tds) && $tds > 0){
                        $amount = $amount - $tds;
                    }
                    $amount = number_format($amount, 2, '.', '');

                    $this->Finance_model->change_withdrawal_status($post);
                    if(isset($this->app_config['auto_withdrawal']['custom_data']['payout']) && strtolower($this->app_config['auto_withdrawal']['custom_data']['payout'])=="cashfree")
                    {
                        $cashfree_data = array();
                        // input data
                        $cashfree_data['order_id'] = $order_result['order_id'];
                        $cashfree_data['order_unique_id'] = $order_result['order_unique_id'];
                        $cashfree_data['amount'] = $amount;

                        $cashfree_data["first_name"] = $order_result['custom_data']['first_name'];
                        $cashfree_data["ac_number"] = $order_result['custom_data']['ac_number'];
                        $cashfree_data["ifsc_code"] = $order_result['custom_data']['ifsc_code'];
                        $cashfree_data["bank_name"] = $order_result['custom_data']['bank_name'];
                        $cashfree_data["beneficiary_id"] = $order_result['custom_data']['beneficiary_id'];
                        $cashfree_data["phone_no"] = $order_result['custom_data']['phone_no'];
                        
                        $cashfree_data['address'] = $user_detail['address'] ? $user_detail['address'] : "dumy address";
                        $cashfree_data['city'] = $user_detail['city'] ? $user_detail['city'] : "Indore";
                        $cashfree_data['zip_code'] = $user_detail['zip_code'] ? $user_detail['zip_code'] : "452001";
                        $cashfree_data['state'] = $user_detail['state'] ? $user_detail['state'] : "Madhya Pradesh";
                        $cashfree_data['email'] = $order_result['email'] ? $order_result['email'] : 'vtech@gmail.com';
                        $cashfree_data['user_winning_balance'] = $user_detail['winning_balance'];
                        $cashfree_data['custom_data'] = $order_result['custom_data'];
                        $cashfree_data['transaction_id']= $txn_id;
                        $cashfree_data['user_id']= $order_result['user_id'];
                        $cashfree_data['date_added']= $order_result['date_added'];
                        $result = $this->cashfree_transfer($cashfree_data);
                    }
                    elseif(isset($this->app_config['auto_withdrawal']['custom_data']['payout']) && strtolower($this->app_config['auto_withdrawal']['custom_data']['payout'])=="juspay") {
                        $juspay_data = array();
                        // input data
                        $juspay_data['order_id']        = $order_result['order_id'];
                        $juspay_data['order_unique_id'] = $order_result['order_unique_id'];
                        $juspay_data['amount']          = $amount;
                        
                        $juspay_data["first_name"]      = $order_result['custom_data']['first_name'];
                        $juspay_data["ac_number"]       = $order_result['custom_data']['ac_number'];
                        $juspay_data["ifsc_code"]       = $order_result['custom_data']['ifsc_code'];
                        $juspay_data["bank_name"]       = $order_result['custom_data']['bank_name'];
                        $juspay_data["phone_no"]        = $order_result['custom_data']['phone_no'];
                        
                        $juspay_data['address']                 = $user_detail['address'] ? $user_detail['address'] : "dumy address";
                        $juspay_data['city']                    = $user_detail['city'] ? $user_detail['city'] : "Indore";
                        $juspay_data['zip_code']                = $user_detail['zip_code'] ? $user_detail['zip_code'] : "452001";
                        $juspay_data['state']                   = $user_detail['state'] ? $user_detail['state'] : "Madhya Pradesh";
                        $juspay_data['email']                   = $order_result['email'] ? $order_result['email'] : 'vtech@gmail.com';
                        $juspay_data['user_winning_balance']    = $user_detail['winning_balance'];
                        $juspay_data['custom_data']             = $order_result['custom_data'];
                        $juspay_data['transaction_id']          = $txn_id;
                        $juspay_data['user_id']                 = $order_result['user_id'];
                        $juspay_data['date_added']              = $order_result['date_added'];
                        $result = $this->juspay_transfer($juspay_data);
                    }
                    elseif(isset($this->app_config['auto_withdrawal']['custom_data']['payout']) && strtolower($this->app_config['auto_withdrawal']['custom_data']['payout'])=="mpesa"){
                        $mpesa_data = array();
                        $mpesa_data["first_name"] = $order_result['custom_data']['first_name'];
                        $mpesa_data["phone_no"] = $order_result['custom_data']['phone_no'];
                        $mpesa_data["amount"] = $amount;
                        $mpesa_data["email"] = $order_result['custom_data']['phone_no'];
                        $mpesa_data["order_id"] = $order_result['order_id'];
                        $mpesa_data["transaction_id"] = $txn_id;
                        $mpesa_data["user_id"] = $order_result['user_id'];
                        $mpesa_data["isIW"] = $order_result['custom_data']['isIW'];
                        // print_r($mpesa_data);die;
                        $result = $this->mpesa_transfer($mpesa_data);
                    }elseif (isset($this->app_config['auto_withdrawal']['custom_data']['payout']) && strtolower($this->app_config['auto_withdrawal']['custom_data']['payout']) == "payumoney") {
                        $this->load->library('payu_payout',$this->app_config['auto_withdrawal']['custom_data']);
                        $this->payu_payout->check_ifsc($order_result['custom_data']['ifsc_code']);
                        $this->payu_payout->get_balance($amount);
                        $p_mode = "IMPS";
                       
                        //for testing purpose
                        // $order_result['custom_data']["ac_number"] = 41234567890; // fail
                        // $order_result['custom_data']["ac_number"] = 51234567890; // success
                        // $order_result['custom_data']["ac_number"] = 61234567890; // pending
                        // $order_result['winning_amount'] = 1;

                        $beneficiary = '[
                                            {
                                            "beneficiaryAccountNumber": "'.$order_result['custom_data']['ac_number'].'",
                                            "beneficiaryIfscCode": "'.$order_result['custom_data']['ifsc_code'].'",
                                            "beneficiaryName": "'.$order_result['custom_data']['first_name'].'",
                                            "beneficiaryEmail": "'.$order_result['email'].'",
                                            "beneficiaryMobile": "'.$order_result['custom_data']['phone_no'].'",
                                            "purpose": "withdraw from payumoney",
                                            "amount": '.$amount.',
                                            "batchId": "1",
                                            "merchantRefId": "'.$txn_id.'_'.$order_result['order_unique_id'].'",
                                            "paymentType": "'.$p_mode.'",
                                            "retry" : false
                                            }
                                        ]';
                        $result = $this->payu_payout->transfer($beneficiary);
                        
                    }elseif (isset($this->app_config['auto_withdrawal']['custom_data']['payout']) && strtolower($this->app_config['auto_withdrawal']['custom_data']['payout']) == "razorpayx") {
                        $this->load->library('razorpayx',$this->app_config['auto_withdrawal']['custom_data']);
                        $p_mode     = "IMPS";
                        $currency   = "INR";
                        $payout_req = array(
                            // merchent account number
                            "account_number"    => $this->app_config['auto_withdrawal']['custom_data']['shortcode'],
                            "fund_account_id"   => $order_result['custom_data']['beneficiary_id'],
                            "amount"            => $amount*100, // 100 pese
                            "currency"          => $currency,
                            "mode"              => $p_mode,
                            "purpose"           => "payout",
                            "queue_if_low_balance"=> false,
                            "reference_id"      => $txn_id.'_'.$order_result['order_unique_id'],
                            "narration"         => "Withdraw from Razorpayx"
                            
                        );
                        // print_r($payout_req);die;
                        // this function used for the create payout request
                        $result  = $this->razorpayx->create_payout_request($payout_req);
                        $txn_id = explode("_",$result['reference_id']);
                        $txn_id = $txn_id[0];
                        $new_result = array();
                        if(!empty($result) && isset($result['id']))
                        {
                            $new_result["gate_way_name"]           = "Razorpay X Payout";
                            $new_result['pg_order_id'] = $result['id'];
                            $new_result['currency'] = $result['currency'];
                            $new_result['payment_mode'] = $result['mode'];
                            $new_result["txn_id"]   = $txn_id;
                            $this->load->model("finance/Finance_model");
                            $this->Finance_model->update_transaction($new_result,$txn_id);
                        }else{
                            throw new Exception("unable to to request for payout from razorpay side, try after some time.");
                        }

                    }else{
                        throw new Exception("no payout is set, please contact support");
                    }
                }
                else
                {
                    $user_id      = $order_result['user_id'];
                    $user_data    = $this->Finance_model->get_single_row('balance, winning_balance,email,user_name', USER, array("user_id"=> $user_id));
                    $this->Finance_model->change_withdrawal_status($post);
                    if($status !=1 && $order_result['status'] !=2)
                    {
                        $this->Finance_model->update_withdraw_reject_balance($order_result['order_id']);

                        $user_cache_key = "user_balance_".$user_id;
                        $this->delete_cache_data($user_cache_key);
                    }
                    if($order_result)
                    {
                        $msg_content = array("amount"=>$order_result['winning_amount'],"reason"=>$reason,"user_id"=>$order_result['user_id'],"cash_type"=>"0","plateform"=>"1","source"=>"7","source_id"=>"0","date_added"=>$order_result['date_added']);
                        if($order_result["withdraw_method"] == 1) { 
                            $msg_content["payment_option"] = 'Bank'; 
                        } else if($order_result["withdraw_method"] == 2) {
                            $msg_content["payment_option"] = 'PayTm'; 
                        } else if($order_result["withdraw_method"] == 3) { 
                            $msg_content["payment_option"] = 'Paypal';
                        } else if($order_result["withdraw_method"] == 17) { 
                            $msg_content["payment_option"] = 'Cashfree';
                        }
                        // SOME CONFUSING BECAUSE STATUS IN DB IS DIFFER
                        $status == 1 && $tmp["notification_type"] = 25; // 25-ApproveWithdrawRequest
                        $status == 2 && $tmp["notification_type"] = 26; // 26-RejectWtihdrawRequest

                        $tmp["source_id"] = 0;
                        $tmp["notification_destination"] = 7; //  Web, Push, Email
                        $tmp["user_id"] =  $order_result['user_id'];
                        $tmp["to"] = $user_data['email'];
                        $tmp["user_name"] = $user_data['user_name'];
                        $tmp["added_date"] = date("Y-m-d H:i:s");
                        $tmp["modified_date"] = date("Y-m-d H:i:s");
                        $tmp["content"] = json_encode($msg_content);
                        if($status == 1) {
                            $tmp["subject"] = $this->finance_lang["withdraw_email_approve_subject"];                        
                        } else {
                            $tmp["subject"] = $this->finance_lang["withdraw_email_reject_subject"];
                        }
                        $this->load->model('user/User_nosql_model');
                        $this->User_nosql_model->send_notification($tmp);
                    }
                }
            }

            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['message']       = $this->lang->line('update_status_success');
        } catch (Exception $e) {                
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']     = $e->getMessage();              
            
        }
        $this->api_response();
    }

    public function get_withdrawal_summary_post()
    {
        $result = $this->Finance_model->get_withdrawal_summary();

        $this->api_response_arry['service_name']  = "get_withdrawal_summary";
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data']          = $result;
        $this->api_response();
    }

    public function juspay_transfer($juspay_data)
    {
        $txn_status =  $this->Finance_model->get_single_row('transaction_status', TRANSACTION, array("order_id"=> $juspay_data['order_id']));
        
        if(in_array($txn_status['transaction_status'],[1,2,4,5]))
        {
            throw new Exception("Status already updated");    
        }
        $jp_pending_status = ["CREATED", "READY_FOR_FULFILLMENT", "FULFILLMENTS_SCHEDULED"];
        $jp_fail_status    = ["FULFILLMENTS_FAILURE"];
        $jp_success_status = ["FULFILLMENTS_SUCCESSFUL"];

        // these status is for transaction table only, for order it will be 0,1,2
        $pending_status = 3;
        $fail_status    = 4;
        $success_status = 5;

        $this->load->library('Juspay_payout');
        $JP = new Juspay_payout($this->app_config['auto_withdrawal']['custom_data']);
        $balances = $JP->get_balance();
        if(!isset($balances) || empty($balances)) {
            throw new Exception("Error while balance fetching");
        }
        $balance = $balances['RAZORPAY_IMPS']['balance'];

        $notify_data = array(
            "user_id"       => $juspay_data['user_id'],
            "amount"        => $juspay_data['amount'],
            "email"         => $juspay_data['email'],
            "first_name"    => $juspay_data['first_name'],
            "order_id"      => $juspay_data['order_id'],
            "transaction_id"=> $juspay_data['transaction_id'],
            "date_added"    => date("Y-m-d H:i:s"),
            "cash_type"     => "0",
            "plateform"     => "1",
            "source"        => "7",
            "source_id"     => "0",
        );

        //check 1
        if($balance < $juspay_data['amount']) {
            throw new Exception("insufficient fund");                    
        } 
        // Make OrderId
        //$jp_order_id = $juspay_data['order_unique_id'].$juspay_data['order_id'];
        $jp_order_id = "JP_".$juspay_data['transaction_id'];
        $amount = $juspay_data['amount'];

        $mode = $this->app_config['auto_withdrawal']['custom_data']['mode'];
        if($mode == 'TEST') {
            $benDetails =  [
                "name"      => $juspay_data['first_name'], 
                "account"   => $juspay_data['ac_number'], 
                "ifsc"      => $juspay_data['ifsc_code']
            ];
        } else {
            $benDetails =  [
                "name"      => $juspay_data['first_name'], 
                "account"   => $juspay_data['ac_number'], 
                "ifsc"      => $juspay_data['ifsc_code']
            ];
        }
        $fulfillments = [
                            [
                                "preferredMethodList" => ["RAZORPAY_IMPS"], 
                                "amount" => (float)$amount, 
                                "beneficiaryDetails" => ["details" =>$benDetails, "type" => "ACCOUNT_IFSC" ], 
                                "additionalInfo" => ["remark" => "Payout Transaction"] 
                            ]  
                        ]; 

        $payload =  [
                        "orderId" => $jp_order_id, "amount" => (float)$amount,
                        "customerId" => $juspay_data['user_id'], "customerEmail" => $juspay_data['email'],
                        "customerPhone" => $juspay_data['phone_no'], "type" => "FULFILL_ONLY", "fulfillments"=>$fulfillments 
                    ];
        //Juspay Transfer Request
        $transfer_req = $JP->req_transfer($payload);

        //print_r($transfer_req); exit;

        if(isset($transfer_req['error']) && ($transfer_req['error'])) {
            $notify_data["status"]              = $fail_status;
            $notify_data["transaction_status"]  = $fail_status;
            $notify_data["reason"]              = $transfer_req['errorMessage'];
            $this->Finance_model->update(ORDER, ['status' => 2,'reason'=>$transfer_req['errorMessage']], ['order_id' => $juspay_data['order_id']]);
            $this->Finance_model->update_transaction(["transaction_status" => $fail_status, "txn_id" => $juspay_data['transaction_id']], $juspay_data['transaction_id']);
            $this->Finance_model->update_withdraw_reject_balance($juspay_data['order_id']);
            $user_cache_key = "user_balance_" . $juspay_data['user_id'];
            $this->delete_cache_data($user_cache_key);
            $this->notify_user($notify_data);
            return true;
        }

        $cross_check_status = $JP->get_transfer_status($jp_order_id);
        //print_r($cross_check_status); exit;
        if(in_array($transfer_req['status'], $jp_pending_status)) {
            if(in_array($cross_check_status['status'], $jp_success_status)) {
                $updated_data["gate_way_name"]         = "Juspay Payout";
                $updated_data["transaction_message"]   = $cross_check_status['fulfillments'][0]['transactions'][0]['responseMessage'];
                $updated_data["txn_amount"]            = $cross_check_status['amount'];
                $updated_data["bank_txn_id"]           = $cross_check_status['fulfillments'][0]['id'];
                $updated_data["transaction_status"]    = $success_status;
                $updated_data["payment_mode"]          = $cross_check_status['fulfillments'][0]['transactions'][0]['fulfillmentMethod'];
                $updated_data["txn_id"]                = $juspay_data['transaction_id'];
                $custom_data = $juspay_data['custom_data'];
                $custom_data['jp_ifsc_code'] = $cross_check_status['fulfillments'][0]['beneficiaryDetails'][0]['details']['ifsc'];
                $custom_data['jp_ac_number'] = $cross_check_status['fulfillments'][0]['beneficiaryDetails'][0]['details']['account'];
                $custom_data = json_encode($custom_data);
                $this->Finance_model->update(ORDER, ['custom_data' => $custom_data, 'status' => 1], ['order_id' => $juspay_data['order_id']]);
                $notify_data["status"] = $success_status;
                $notify_data["reason"] = 'success';
                $this->Finance_model->update_transaction($updated_data, $juspay_data['transaction_id']);
            }
            elseif(in_array($transfer_req['status'], $jp_fail_status)) {
                $notify_data["status"]              = $fail_status;
                $notify_data["transaction_status"]  = $fail_status;
                $notify_data["reason"]              = "";
                $this->Finance_model->update(ORDER, ['status' => 2,'reason'=>""], ['order_id' => $juspay_data['order_id']]);
                $this->Finance_model->update_transaction(["transaction_status" => $fail_status, "txn_id" => $juspay_data['transaction_id']], $juspay_data['transaction_id']);
                $this->Finance_model->update_withdraw_reject_balance($juspay_data['order_id']);
                $user_cache_key = "user_balance_" . $juspay_data['user_id'];
                $this->delete_cache_data($user_cache_key);
            } else {
                $notify_data["status"] = $pending_status;
                $notify_data["transaction_status"] = $pending_status;
                $notify_data["reason"] = $cross_check_status['status'];
                $this->Finance_model->update_transaction(["transaction_status" => $pending_status, "txn_id" => $juspay_data['transaction_id']], $juspay_data['transaction_id']);
            }
        }
        elseif(in_array($transfer_req['status'], $jp_fail_status)) {
                $notify_data["status"]              = $fail_status;
                $notify_data["transaction_status"]  = $fail_status;
                $notify_data["reason"]              = "";
                $this->Finance_model->update(ORDER, ['status' => 2,'reason'=>""], ['order_id' => $juspay_data['order_id']]);
                $this->Finance_model->update_transaction(["transaction_status" => $fail_status, "txn_id" => $juspay_data['transaction_id']], $juspay_data['transaction_id']);
                $this->Finance_model->update_withdraw_reject_balance($juspay_data['order_id']);
                $user_cache_key = "user_balance_" . $juspay_data['user_id'];
                $this->delete_cache_data($user_cache_key);
        }
        else {
                $notify_data["status"]              = $pending_status;
                $notify_data["reason"]              = "";
                $updated_data["transaction_status"] = $pending_status;
                $notify_data["transaction_status"]  = $pending_status;
                $notify_data["date_added"]          = "";
                $updated_data["txn_id"]             = $juspay_data['transaction_id'];
                $this->Finance_model->update_transaction($updated_data, $juspay_data['transaction_id']);
        }
        if(in_array($transfer_req['status'], $jp_fail_status) || in_array($cross_check_status['status'], $jp_success_status)) {
            $this->notify_user($notify_data);
        }
        return true;
    }

    public function cashfree_transfer($cashfree_data)
    {
        // these status is for transaction table only, for order it will be 0,1,2
        $pending_status = 3;
        $fail_status = 4;
        $success_status = 5;

        $this->load->library('Cashfree_payout');
        $CF = new Cashfree_payout($this->app_config['auto_withdrawal']['custom_data']);
        $balance = $CF->get_balance();
        $notify_data = array(
            "user_id" => $cashfree_data['user_id'],
            "amount" => $cashfree_data['amount'],
            "email" => $cashfree_data['email'],
            "first_name" => $cashfree_data['first_name'],
            "order_id" => $cashfree_data['order_id'],
            "transaction_id" => $cashfree_data['transaction_id'],
            "date_added" => date("Y-m-d H:i:s"),
            "cash_type" => "0",
            "plateform" => "1",
            "source" => "7",
            "source_id" => "0",
        );

        //check 1
        if ($balance < $cashfree_data['amount']) {
            throw new Exception("insufficient fund");
        }
        $get_beneficiary = $CF->get_bene($cashfree_data['beneficiary_id']);
        if ($get_beneficiary['subCode'] != 200 && $get_beneficiary['subCode'] == 404) {
            $bene_data = array(
                "beneId" => $cashfree_data['beneficiary_id'],
                "name" => $cashfree_data['first_name'],
                "phone" => $cashfree_data['phone_no'],
                "bankAccount" => $cashfree_data['ac_number'],
                "ifsc" => $cashfree_data['ifsc_code'],
                "email" => $cashfree_data['email'],
                "address1" => $cashfree_data['address'],
                "city" => $cashfree_data['city'],
                "state" => $cashfree_data['state'],
                "pincode" => $cashfree_data['zip_code']
            );
            $add_beneficiary = $CF->add_bene(json_encode($bene_data));

            if ($add_beneficiary['subCode'] == 422 || $add_beneficiary['subCode'] == 409) {
                $txn_status =  $this->Finance_model->get_single_row('transaction_status', TRANSACTION, array("order_id"=> $cashfree_data['order_id']));
                
                if(in_array($txn_status['transaction_status'],[1,2,4,5]))
                {
                    throw new Exception("Status already updated");    
                }

                $this->Finance_model->update(ORDER, ['status' => 2,'reason'=>$add_beneficiary['message']], ['order_id' => $cashfree_data['order_id']]);
                $this->Finance_model->update_transaction(["transaction_status" => $fail_status, "txn_id" => $cashfree_data['transaction_id']], $cashfree_data['transaction_id']);
                
                $this->Finance_model->update_withdraw_reject_balance($cashfree_data['order_id']);

                $user_cache_key = "user_balance_" . $cashfree_data['user_id'];
                $this->delete_cache_data($user_cache_key);
                $notify_data["status"] = 4;
                $notify_data["reason"] = $add_beneficiary['message'];
                $this->notify_user($notify_data);
                throw new Exception($add_beneficiary['message']);
            }

            if ($add_beneficiary['subCode'] != 200) {
                throw new Exception("Can't add Beneficiary try again.");
            }
            $get_beneficiary = $CF->get_bene($cashfree_data['beneficiary_id']);
            if ($get_beneficiary['subCode'] != 200) {
                throw new Exception($get_beneficiary['message']);
            }
        }
        if ($get_beneficiary['subCode'] == 200) {
            $cf_order_id = $cashfree_data['order_unique_id'] . $cashfree_data['order_id'];
            $amount = $cashfree_data['amount'];

            $data = array(
                "beneId" => $cashfree_data['beneficiary_id'],
                "amount" => $amount,
                "transferId" => $cf_order_id
            );
            $transfer_req = $CF->req_transfer($data);
            $updated_data = array(
                'pg_order_id' => $transfer_req['data']['referenceId'],
            );
            if ($transfer_req['subCode'] == 200 && strtolower($transfer_req['status']) == "success") {
                $cross_check_status = $CF->get_transfer_status($transfer_req['data']['referenceId'], $cf_order_id);
                if ($cross_check_status['subCode'] == 200 && strtolower($cross_check_status['status']) == "success") {
                    $reason = $transfer_req['message'];
                    $updated_data["gate_way_name"] = "Cashfree Payout";
                    $updated_data["transaction_message"] = $transfer_req['message'];
                    $updated_data["txn_amount"] = $cross_check_status['data']['transfer']['amount'];
                    $updated_data["bank_txn_id"] = $cross_check_status['data']['transfer']['utr'];
                    $updated_data["transaction_status"] = $success_status;
                    $updated_data["payment_mode"] = $cross_check_status['data']['transfer']['transferMode'];
                    $updated_data["txn_id"] = $cashfree_data['transaction_id'];

                    // $custom_data = $this->Finance_model->get_single_row('custom_data', ORDER,["order_id"=>$cashfree_data['order_id']]);
                    $custom_data = $cashfree_data['custom_data'];
                    $custom_data['cf_phone_no'] = $cross_check_status['data']['transfer']['phone'];
                    $custom_data['cf_ifsc_code'] = $cross_check_status['data']['transfer']['ifsc'];
                    $custom_data['cf_ac_number'] = $cross_check_status['data']['transfer']['bankAccount'];
                    $custom_data['cf_beneficiary_id'] = $cross_check_status['data']['transfer']['beneId'];
                    $custom_data = json_encode($custom_data);

                    $this->Finance_model->update(ORDER, ['custom_data' => $custom_data, 'status' => 1], ['order_id' => $cashfree_data['order_id']]);

                    $notify_data["status"] = $success_status;
                    $notify_data["reason"] = $cross_check_status['message'];
                    $this->Finance_model->update_transaction($updated_data, $cashfree_data['transaction_id']);
                }
                elseif (strtolower($cross_check_status['status']) == "failed") {
                    $notify_data["status"] = $fail_status;
                    $notify_data["transaction_status"] = $fail_status;
                    $notify_data["reason"] = $cross_check_status['message'];

                    $this->Finance_model->update(ORDER, ['status' => 2,'reason'=>$cross_check_status['message']], ['order_id' => $cashfree_data['order_id']]);
                    $this->Finance_model->update_transaction(["transaction_status" => $fail_status, "txn_id" => $cashfree_data['transaction_id']], $cashfree_data['transaction_id']);

                    $this->Finance_model->update_withdraw_reject_balance($cashfree_data['order_id']);

                    $user_cache_key = "user_balance_" . $cashfree_data['user_id'];
                    $this->delete_cache_data($user_cache_key);

                }
                else {
                    $notify_data["status"] = $pending_status;
                    $notify_data["transaction_status"] = $pending_status;
                    $notify_data["reason"] = $cross_check_status['message'];
                    $this->Finance_model->update_transaction(["transaction_status" => $pending_status, "txn_id" => $cashfree_data['transaction_id']], $cashfree_data['transaction_id']);
                }
            }
            elseif($transfer_req['subCode']==422)
            {
                $notify_data["status"] = $fail_status;
                $notify_data["transaction_status"] = $fail_status;
                $notify_data["reason"] = $transfer_req['message'];

                $this->Finance_model->update(ORDER, ['status' => 2,'reason'=>$transfer_req['message']], ['order_id' => $cashfree_data['order_id']]);
                $this->Finance_model->update_transaction(["transaction_status" => $fail_status, "txn_id" => $cashfree_data['transaction_id']], $cashfree_data['transaction_id']);

                $this->Finance_model->update_withdraw_reject_balance($cashfree_data['order_id']);
                
                $user_cache_key = "user_balance_" . $cashfree_data['user_id'];
                $this->delete_cache_data($user_cache_key);
            }
            else {
                $notify_data["status"] = $pending_status;
                $notify_data["reason"] = $transfer_req['message'];
                $updated_data["transaction_status"] = $pending_status;
                $updated_data["txn_id"] = $cashfree_data['transaction_id'];
                $this->Finance_model->update_transaction($updated_data, $cashfree_data['transaction_id']);
            }
        }
        elseif (strtolower($get_beneficiary['status']) == "failed") {
            $notify_data["status"] = $pending_status;
            $notify_data["transaction_status"] = $pending_status;
            $notify_data["reason"] = $get_beneficiary['message'];
            $this->Finance_model->update_transaction(["transaction_status" => $pending_status, "txn_id" => $cashfree_data['transaction_id']], $cashfree_data['transaction_id']);
        }

        $this->notify_user($notify_data);

        return true;
    }

    public function notify_user($notify_data)
    {
        $notify_data["status"] == 5 && $tmp["notification_type"] = 25; // 25-ApproveWithdrawRequest
        $notify_data["status"] == 4 && $tmp["notification_type"] = 26; // 26-RejectWtihdrawRequest

        $tmp["source_id"] = 0;
        $tmp["notification_destination"] = 7; //  Web, Push, Email
        $tmp["user_id"] = $notify_data['user_id'];
        $tmp["to"] = $notify_data['email'];
        $tmp["user_name"] = $notify_data['first_name'];
        $tmp["added_date"] = $notify_data['date_added'];
        $tmp["modified_date"] = date("Y-m-d H:i:s");
        $tmp["content"] = json_encode($notify_data);
        if ($notify_data["status"] == 5) {
            $tmp["subject"] = $this->finance_lang["withdraw_email_approve_subject"];
        }
        else {
            $tmp["subject"] = $this->finance_lang["withdraw_email_reject_subject"];
        }
        $this->load->model('user/User_nosql_model');
        $this->User_nosql_model->send_notification($tmp);
    }

    public function mpesa_transfer($data)
    {
        $this->load->library('Mpesa_lib',$this->app_config['auto_withdrawal']['custom_data']);
        $lib = new Mpesa_lib($this->app_config['auto_withdrawal']['custom_data']);
        $notify_data = array(
            "amount"=>$data['amount'],
            "email"=>$data['email'],
            "first_name"=>$data['first_name'],
            "order_id"=> $data['order_id'],
            "transaction_id"=>$data['transaction_id'],
            "user_id"=>$data['user_id'],
        );
        // phone_no
        $amount = $data['amount'];
        if($data['isIW']==1)
        {
            $amount = $this->deduct_pgfee($amount);
        }
        if($amount<1)
        {
            throw new Exception("Try with some greater amount");
        }
        // print_r($data);die;
        // echo "reached", $order_data;die;
        $result = $lib->transfer($amount,$data['user_id'],$data);
        return true;
    }

    /**
     * common function to diduct pg fee from amount
     */
    public function deduct_pgfee($amount)
    {
        if(strstr($this->app_config['auto_withdrawal']['custom_data']['pg_fee'],'%'))
        {
            $pg_char_per = (float) trim(str_replace('%','',$this->app_config['auto_withdrawal']['custom_data']['pg_fee']), ' ');
            if($pg_char_per > 0)
            {
                $pg_fee    = ($pg_char_per*$amount)/100;
                $amount = $amount - $pg_fee;
            }
        }else{
            $pg_char_per = $this->app_config['auto_withdrawal']['custom_data']['pg_fee'];
            if($pg_char_per > 0)
            {
                $amount = $amount - $pg_char_per;
            }
        }
        return $amount;
    }

    /**
     * Used for download single txn with user detail
     * @param int $order_id
     * @return PDF report
     */
    public function user_txn_report_get()
    {
        $order_id = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : "";
        if(!isset($order_id) || empty($order_id)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Transaction id field required.";
            $this->api_response();
        }

        $result = $this->Finance_model->get_user_txn_detail($order_id);
        if(empty($result)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid transaction id.";
            $this->api_response();
        }
        
        //echo "<pre>";print_r($result);die;
        if(!empty($result))
        {
            $gateway_list = get_active_payment_list($this->app_config);
            $status_arr = array("0"=>"Pending","1"=>"Active","2"=>"NotVerified","3"=>"Deleted","4"=>"Blocked");
            $txn_status_arr = array("0"=>"Pending","1"=>"Success","2"=>"Failed");
            $result['user_status'] = $status_arr[$result['user_status']];
            $result['status'] = $txn_status_arr[$result['status']];
            $result['gate_way_name'] = isset($gateway_list[$result['payment_gateway_id']]) ? $gateway_list[$result['payment_gateway_id']] : $result['gate_way_name'];
            $result['timezone'] = $this->app_config['timezone']['key_value'];
            $html = $this->load->view('finance/user_txn',array("data"=>$result),TRUE);
            ini_set('memory_limit', '-1');
            $this->load->helper('dompdf_helper');
            $file_name = "Txn-".$result['transaction_id'];
            generate_pdf($file_name,$html,"landscape");
        }

        $this->api_response_arry['message'] = "File downloaded";
        $this->api_response();
    }

}
/* End of file User.php */
/* Location: ./application/controllers/User.php */
