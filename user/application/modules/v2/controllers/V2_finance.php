<?php
/**
 * finance for withdraw
 * @package V2_finance
 * @category V2_finance
 */

require_once APPPATH.'modules/finance/controllers/Finance.php';
class V2_finance extends Finance {

    public $pending_status = 3;
    public $fail_status = 4;
    public $success_status = 5;

    function __construct() {
        parent::__construct();
    }
    
    /**
     * Used to submit withdraw request
     */
    function withdraw_post() {
        if($this->input->post()) {
            if(isset($this->app_config['auto_withdrawal']) && $this->app_config['auto_withdrawal']['key_value'] == "0"){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['status'] = FALSE;
                $this->api_response_arry['message'] = "No payout is available right now, please contact to admin.";
                $this->api_response();
            }

            $this->form_validation->set_rules('amount', $this->lang->line("amount"), 'trim|required|callback_decimal_numeric|callback_greater_than_zero');
            $this->form_validation->set_rules('isIW', "Instant Withdraw Charge", 'trim|required|in_list[1,2]');
            $this->form_validation->set_rules('p_mode', "Payment Mode", 'trim');

            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }
            try {
                $this->load->model("finance/Finance_model");
                $post_input = $this->input->post();
                $user_id = $this->user_id;
                
                $validate_otp_res=$this->validate_wdl_2fa();
                
                
                $daily_limit = $this->app_config['auto_withdrawal']['custom_data']['daily_txn'];
                $txn_of_day = $this->Finance_model->get_txn_of_day();
                if($txn_of_day >= $this->app_config['auto_withdrawal']['custom_data']['daily_txn']) 
                {
                    throw new Exception(sprintf($this->finance_lang["daily_withdraw_limit"], $this->app_config['auto_withdrawal']['custom_data']['daily_txn']));                    
                }

                $post_input['pg_fee']=0;
                if(isset($post_input['isIW']) && $post_input['isIW']==1)
                {
                    $post_input['pg_fee'] = $this->app_config['auto_withdrawal']['custom_data']['pg_fee'] ? $this->app_config['auto_withdrawal']['custom_data']['pg_fee']:0;
                    if($post_input['pg_fee']<0) throw new Exception("pg fee can not be less then 0");
                    $amount = $this->deduct_pgfee($post_input['amount']);
                    if($amount<1)
                    {
                        throw new Exception("Try with some greater amount");
                    }
                }
                $amount = number_format($post_input['amount'], 2, '.', '');

                if ($amount < $this->app_config['min_withdrawl']['key_value']) {
                    throw new Exception(sprintf($this->finance_lang["min_withdraw_value_error"], $this->app_config['min_withdrawl']['key_value']));                    
                }
                if ($amount > $this->app_config['max_withdrawl']['key_value']) {
                    throw new Exception(sprintf($this->finance_lang["max_withdraw_value_error"], $this->app_config['max_withdrawl']['key_value']));                    
                }

                $result_data = $this->Finance_model->get_pending_withdrawal($user_id);
                if(!empty($result_data)) {
                    // $this->db->set(['O.status'=>2,"T.transaction_status"=>5])->update(ORDER." O")->join(TRANSACTION." T","O.order_id = T.order_id","INNER")->where("O.order_unique_id",$result_data['order_unique_id']);
                    throw new Exception($this->lang->line("multiple_withdraw_error"));
                }

                $user_bank_detail = $this->Finance_model->get_single_row('first_name,ac_number, ifsc_code, bank_name,beneficiary_id', USER_BANK_DETAIL, array('user_id' => $user_id));
                if(empty($user_bank_detail)){      
                    throw new Exception($this->lang->line("update_bank_details"));
                }
                
                $user_detail = $this->Finance_model->get_user_by_id($user_id);
                if($user_detail['is_bank_verified'] != 1){
                    throw new Exception($this->lang->line("pending_bank_verification"));
                }

                $post_input['address']          = isset($user_detail['address']) ? $user_detail['address'] : "dumy address";
                $post_input['city']             = isset($user_detail['city']) ? $user_detail['city'] : "Indore";
                $post_input['zip_code']         = isset($user_detail['zip_code']) ? $user_detail['zip_code'] : "452001";
                $post_input['state']            = isset($user_detail['state']) ? $user_detail['state'] : "Madhya Pradesh";
                $email                          = isset($this->email) ? $this->email : 'vtech@gmail.com';
                $phone_no                       = isset($this->phone_no) ? $this->phone_no : '';
                $user_bank_detail['phone_no']   = $phone_no;
                $post_input['amount']           = $amount;
                $post_input['email']            = $email;

                $post_input["auto_withdrawal"] = array("is_auto_withdrawal" => 1,"pg_fee"=>$post_input['pg_fee'],"isIW"=>$post_input['isIW']); // for when Auto-withdrawal is ENABLED
                // testing use
                //$this->app_config['auto_withdrawal']['custom_data']['payout'] = 'Razorpayx';
                
                if(isset($this->app_config['auto_withdrawal']['custom_data']['payout']) && strtolower($this->app_config['auto_withdrawal']['custom_data']['payout'])!="")
                {
                    // echo "one".$this->app_config['auto_withdrawal']['custom_data']['payout'];
                    switch(strtolower($this->app_config['auto_withdrawal']['custom_data']['payout']))
                    {
                        case 'cashfree' :
                            if(!isset($user_bank_detail['beneficiary_id']))
                            {
                                $user_bank_detail['beneficiary_id'] = $this->Finance_model->generate_cashfree_user_id();
                            }
                            $post_input['withdraw_method'] = 17;
                            break;
                        case 'mpesa' :
                            if(!isset($this->phone_no) && $this->phone_no=='')
                            {
                                throw new Exception("Phone number is not yet set, please set phone number first");
                            }
                            $post_input['withdraw_method'] = 3;
                            break;
                            case 'payumoney':
                                $this->load->library('payu_payout',$this->app_config['auto_withdrawal']['custom_data']);
                                $this->payu_payout->check_ifsc($user_bank_detail['ifsc_code']);
                                $this->payu_payout->get_balance($amount);
                                $post_input['withdraw_method'] = 1;
                        break;
                        case 'razorpayx':
                            $post_input['withdraw_method'] = 8;
                            $this->load->library('razorpayx',$this->app_config['auto_withdrawal']['custom_data']);
                            if(!isset($user_bank_detail['beneficiary_id']) || empty($user_bank_detail['beneficiary_id']))
                            {
                                $user_bank_detail['beneficiary_id'] = '';
                                $user_bank_detail['beneficiary_id'] = $this->razorpayx->generate_contacts($post_input,$user_bank_detail);
                                $this->Finance_model->update(USER_BANK_DETAIL,array('beneficiary_id'=>$user_bank_detail['beneficiary_id']), array('user_id' => $user_id));
                            }
                        break;
                        case 'juspay':
                            $post_input['withdraw_method'] = 34;
                            break;
                    }
                    
                }else{
                    throw new Exception($this->finance_lang["no_pout_active"]);
                }
               
                $post_input['bank_detail'] = $user_bank_detail;


                $order_data = $this->Finance_model->_generate_withdraw_order_and_tx($user_id, $email, $phone_no, $post_input);

                if($post_input['isIW']==1){
                    $post_input['tds'] = isset($order_data['tds']) ? $order_data['tds'] : 0;
                    if(isset($this->app_config['auto_withdrawal']['custom_data']['payout']) && strtolower($this->app_config['auto_withdrawal']['custom_data']['payout'])!="")
                    {
                        switch(strtolower($this->app_config['auto_withdrawal']['custom_data']['payout']))
                        {
                        case 'cashfree' :
                        $result = $this->cashfree_transfer($post_input,$order_data);
                        if(in_array($result['status'],[1,2,4,5]))
                        {
                            $this->notify_user($result);
                            $user_cache_key = "user_balance_".$user_id;
                            $this->delete_cache_data($user_cache_key);
                        }
                        if(isset($result['reason']) && $result['reason'] != 'success')
                        {
                            throw new Exception($result['reason']);
                        }
                        break;
                        case 'juspay':
                            $result = $this->juspay_transfer($post_input,$order_data);
                            $message = $this->lang->line('withdraw_success_in_few_min');
                            if(in_array($result['status'],[1,2,4,5]))
                            {
                                $this->notify_user($result);
                                $user_cache_key = "user_balance_".$user_id;
                                $this->delete_cache_data($user_cache_key);
                            }
                            if(isset($result['error']))
                            {
                                throw new Exception($result['reason']);
                            }
                        break;
                        case 'mpesa' :
                        $result = $this->mpesa_transfer($post_input,$order_data);
                        $message = $this->lang->line('withdraw_success_in_few_min');
                            break;
                        case 'payumoney':
                                $p_mode = $post_input['p_mode'] ? $post_input['p_mode'] : "IMPS";
                            // $user_bank_detail["acc_no"] = 41234567890; // fail
                            // $user_bank_detail["acc_no"] = 51234567890; // success
                            // $user_bank_detail["acc_no"] = 61234567890; // pending
                            if($post_input['isIW']==1)
                            {
                                $amount = $this->deduct_pgfee($amount);
                            }
                            //tds amount deduct
                            if(isset($post_input['tds']) && $post_input['tds'] > 0){
                                $amount = $amount - $post_input['tds'];
                            }
                            $amount = number_format($amount, 2, '.', '');
                            $banificiary = '[
                                                {
                                                "beneficiaryAccountNumber": "'.$user_bank_detail["ac_number"].'",
                                                "beneficiaryIfscCode": "'.$user_bank_detail["ifsc_code"].'",
                                                "beneficiaryName": "'.$user_bank_detail["first_name"].'",
                                                "beneficiaryEmail": "'.$email.'",
                                                "beneficiaryMobile": "'.$phone_no.'",
                                                "purpose": "withdraw from payumoney",
                                                "amount": '.$amount.',
                                                "batchId": "1",
                                                "merchantRefId": "'.$order_data['transaction_id'].'_'.$order_data['order_unique_id'].'",
                                                "paymentType": "'.$p_mode.'",
                                                "retry" : false
                                                }
                                            ]';
                            $result = $this->payu_payout->transfer($banificiary);
                            $message = $this->lang->line('withdraw_success_in_few_min');
                            break;
                        case 'razorpayx':
                            $p_mode     = "IMPS";
                            $currency   = "INR";
                            $f_id=isset($post_input['bank_detail']['beneficiary_id'])?$post_input['bank_detail']['beneficiary_id']:'';
                            // create payout request params
                            if($post_input['isIW']==1)	
                            {	
                                $amount = $this->deduct_pgfee($amount);	
                            }
                            //tds amount deduct
                            if(isset($post_input['tds']) && $post_input['tds'] > 0){
                                $amount = $amount - $post_input['tds'];
                            }
                            $amount = number_format($amount, 2, '.', '');
                            $payout_req = array(
                                // merchent account number
                                "account_number"    => $this->app_config['auto_withdrawal']['custom_data']['shortcode'],
                                "fund_account_id"   => $f_id,
                                "amount"            => $amount*100, // 100 pese
                                "currency"          => $currency,
                                "mode"              => $p_mode,
                                "purpose"           => "payout",
                                "queue_if_low_balance"=> false,
                                "reference_id"      => $order_data['transaction_id'].'_'.$order_data['order_unique_id'],
                                "narration"         => "Withdraw from Razorpayx"
                                
                            );
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
                            }
                            $message = $this->lang->line('withdraw_success_in_few_min');
                        break;
                        }
                    }
                    else{
                        throw new Exception($this->finance_lang["no_pout_active"]);
                    }
                }

                $this->api_response_arry['message'] = $message ? $message :$this->lang->line('withdraw_success');
                $user_cache_key = "user_balance_" . $user_id;
                $this->delete_cache_data($user_cache_key);
                $this->api_response_arry['data'] = array();
                $this->api_response();
            } catch (Exception $e) {  
                $this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']		= $e->getMessage();                
                $this->api_response();
            }
        } else {
            $this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;            
            $this->api_response_arry['message']		= $this->lang->line('input_invalid_format');
            $this->api_response();
        }
    }

    public function juspay_transfer($post_input,$order_data)
    {
         $jp_pending_status = ["CREATED", "READY_FOR_FULFILLMENT", "FULFILLMENTS_SCHEDULED"];
         $jp_fail_status    = ["FULFILLMENTS_FAILURE"];
         $jp_success_status = ["FULFILLMENTS_SUCCESSFUL"];

        // these status is for transaction table only, for order it will be 0,1,2
        $pending_status = 3;
        $fail_status = 4;
        $success_status = 5;

        $this->load->library('Juspay_payout');
        $JP = new Juspay_payout($this->app_config['auto_withdrawal']['custom_data']);
        $balances = $JP->get_balance();
        //print_r($balances); exit;
        if(!isset($balances) || empty($balances)) {
            throw new Exception("Error while balance fetching");
        }
        $balance = $balances['RAZORPAY_IMPS']['balance'];
        $notify_data = array(
            "user_id" => $this->user_id,
            "amount"=>$post_input['amount'],
            "email"=>$post_input['email'],
            "first_name"=>$post_input['bank_detail']['first_name'],
            "order_id"=> $order_data['order_id'],
            "transaction_id"=>$order_data['transaction_id']
        );
        $notify_data['pg_id'] = 34;

        //check 1
        if($balance < $post_input['amount']) {
            throw new Exception($this->finance_lang["cf_insufficient_bal"]);                    
        } 
        
        //$jp_order_id = $order_data['order_unique_id'].$order_data['order_id'];
        $jp_order_id = "JP_".$order_data['transaction_id'];
        $amount = $post_input['amount'];
        
        if($post_input['isIW']==1)
        {
            $amount = $this->deduct_pgfee($amount);
        }

        //tds amount deduct
        if(isset($post_input['tds']) && $post_input['tds'] > 0){
            $amount = $amount - $post_input['tds'];
        }
        
        $mode = $this->app_config['auto_withdrawal']['custom_data']['mode'];
        if($mode == 'TEST') {
            $benDetails =  [
                "name"      => $post_input['bank_detail']['first_name'], 
                "account"   => $post_input['bank_detail']['ac_number'], 
                "ifsc"      => $post_input['bank_detail']['ifsc_code']
            ];
        } else {
            $benDetails =  [
                "name"      => $post_input['bank_detail']['first_name'], 
                "account"   => $post_input['bank_detail']['ac_number'], 
                "ifsc"      => $post_input['bank_detail']['ifsc_code']
            ];
        }
        $fulfillments = 
        [
            [
                "preferredMethodList" => ["RAZORPAY_IMPS"], 
                "amount" => (float)$amount, 
                "beneficiaryDetails" => ["details" =>$benDetails, "type" => "ACCOUNT_IFSC" ], 
                "additionalInfo" => ["remark" => "Payout Transaction"] 
            ]  
        ]; 

        $data = [
                    "orderId" => $jp_order_id, "amount" => (float)$amount,
                    "customerId" => $this->user_id, "customerEmail" => $post_input['email'],
                    "customerPhone" => $post_input['bank_detail']['phone_no'], "type" => "FULFILL_ONLY", "fulfillments"=>$fulfillments 
                ]; 
        $transfer_req = $JP->req_transfer($data);

        if(isset($transfer_req['error'])) {
            $notify_data["status"]              = $fail_status;
            $notify_data["transaction_status"]  = $fail_status;
            $notify_data["operation"]           = "add";
            $notify_data["reason"]              = $transfer_req['errorMessage'];
            $notify_data["error"]               = $transfer_req['error'];
            $notify_data["date_added"]          = "";
            return $notify_data;
        }
        
        if(in_array($transfer_req['status'], $jp_pending_status)) {
            $cross_check_status = $JP->get_transfer_status($jp_order_id);
            if(in_array($cross_check_status['status'], $jp_success_status)) {
                $updated_data["gate_way_name"]         = "Juspay Payout";
                $updated_data["transaction_message"]   = isset($cross_check_status['fulfillments'][0]['transactions'][0]['responseMessage']) ? $cross_check_status['fulfillments'][0]['transactions'][0]['responseMessage'] : '';
                $updated_data["txn_amount"]            = $cross_check_status['amount'];
                $updated_data["bank_txn_id"]           = isset($cross_check_status['fulfillments'][0]['id']) ? $cross_check_status['fulfillments'][0]['id'] : '';
                $updated_data["transaction_status"]    = $success_status;
                $updated_data["payment_mode"]          = isset($cross_check_status['fulfillments'][0]['transactions'][0]['fulfillmentMethod']) ? $cross_check_status['fulfillments'][0]['transactions'][0]['fulfillmentMethod'] : '';
                $updated_data["txn_id"]                = $order_data['transaction_id'];
                
                $custom_data = $this->Finance_model->get_single_row('custom_data', ORDER,["order_id"=>$order_data['order_id']]);
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
                    "order_id"=>$order_data['order_id']
                );
                $this->Finance_model->update_ord($order_arr);

                $notify_data["status"] = $success_status;
                $notify_data["reason"] =  'success';
                $this->Finance_model->update_transaction($updated_data, $order_data['transaction_id']);
            }
            elseif(in_array($transfer_req['status'], $jp_fail_status)) {
                    $notify_data["status"]              = $fail_status;
                    $notify_data["transaction_status"]  = $fail_status;
                    $notify_data["operation"]           = "add";
                    $notify_data["reason"]              = "";//$cross_check_status['message'];
            }else {
                $notify_data["status"]              = $pending_status;
                $notify_data["transaction_status"]  = $pending_status;
                $notify_data["reason"]              = $cross_check_status['status'];
                $this->Finance_model->update_transaction(["transaction_status"=>$pending_status,"txn_id"=>$order_data['transaction_id']], $order_data['transaction_id']);
            }
        }
        elseif(in_array($transfer_req['status'], $jp_fail_status))
        {
            $notify_data["status"]              = $fail_status;
            $notify_data["transaction_status"]  = $fail_status;
            $notify_data["operation"]           = "add";
            $notify_data["reason"]              = "";
            return $notify_data;
        }
        else {
                $notify_data["status"]              = $pending_status;
                $notify_data["reason"]              = "";
                $updated_data["transaction_status"] = $pending_status;
                $notify_data["transaction_status"]  = $pending_status;
                $notify_data["date_added"]          = "";
                $updated_data["txn_id"]             = $order_data['transaction_id'];
                $this->Finance_model->update_transaction($updated_data, $order_data['transaction_id']);
        }
        return $notify_data;
    }
    
    public function cashfree_transfer($post_input,$order_data)
    {
        // these status is for transaction table only, for order it will be 0,1,2
        $pending_status = 3;
        $fail_status = 4;
        $success_status = 5;

        $this->load->library('Cashfree_payout');
        $CF = new Cashfree_payout($this->app_config['auto_withdrawal']['custom_data']);
        $balance = $CF->get_balance();
        $notify_data = array(
            "user_id" => $this->user_id,
            "amount"=>$post_input['amount'],
            "date_added"=>$post_input['date_added'],
            "email"=>$post_input['email'],
            "first_name"=>$post_input['bank_detail']['first_name'],
            "order_id"=> $order_data['order_id'],
            "transaction_id"=>$order_data['transaction_id']
        );

        //check 1
        if($balance < $post_input['amount'])
        {
            throw new Exception($this->finance_lang["cf_insufficient_bal"]);                    
        }
        $get_beneficiary = $CF->get_bene($post_input['bank_detail']['beneficiary_id']);
        if($get_beneficiary['subCode']!=200 && $get_beneficiary['subCode']==404)
        {
            $bene_data = array(
                    "beneId"        => $post_input['bank_detail']['beneficiary_id'],
                    "name"          => $post_input['bank_detail']['first_name'],
                    "phone"         => $post_input['bank_detail']['phone_no'],
                    "bankAccount"   => $post_input['bank_detail']['ac_number'],
                    "ifsc"          => $post_input['bank_detail']['ifsc_code'],
                    "email"         => $post_input['email'],
                    "address1"      => $post_input['address'],
                    "city"          => $post_input['city'],
                    "state"         => $post_input['state'],
                    "pincode"       => $post_input['zip_code']
            );
            $add_beneficiary = $CF->add_bene(json_encode($bene_data));
            
            if($add_beneficiary['subCode']==422 || $add_beneficiary['subCode']==409)
            {
                $notify_data["status"]              = $fail_status;
                $notify_data["transaction_status"]  = $fail_status;
                $notify_data["operation"]           = "add";
                $notify_data["reason"]              = $add_beneficiary['message'];
                return $notify_data;
            }


            if($add_beneficiary['subCode']!=200)
            {
               throw new Exception($add_beneficiary['message']); 
            }
            $get_beneficiary = $CF->get_bene($post_input['bank_detail']['beneficiary_id']);
            if($get_beneficiary['subCode']!=200)
            {
               throw new Exception($get_beneficiary['message']); 
            }
        }
        if($get_beneficiary['subCode']==200)
        {
            $cf_order_id = $order_data['order_unique_id'].$order_data['order_id'];
            $amount = $post_input['amount'];
            
            if($post_input['isIW']==1)
            {
                $amount = $this->deduct_pgfee($amount);
            }

            //tds amount deduct
            if(isset($post_input['tds']) && $post_input['tds'] > 0){
                $amount = $amount - $post_input['tds'];
            }

            $data = array(
                    "beneId"    => $post_input['bank_detail']['beneficiary_id'],
                    "amount"    => $amount,
                    "transferId"=> $cf_order_id
            );
            $transfer_req = $CF->req_transfer($data);
            $updated_data = array(
                'pg_order_id' => $transfer_req['data']['referenceId'],
            );
            if($transfer_req['subCode']==200 && strtolower($transfer_req['status'])== "success")
            {
                $cross_check_status = $CF->get_transfer_status($transfer_req['data']['referenceId'],$cf_order_id);
                if($cross_check_status['subCode']==200 && strtolower($cross_check_status['status'])== "success")
                {
                    $reason = $transfer_req['message'];
                    $updated_data["gate_way_name"]         = "Cashfree Payout";
                    $updated_data["transaction_message"]   = $transfer_req['message'];
                    $updated_data["txn_amount"]            = $cross_check_status['data']['transfer']['amount'];
                    $updated_data["bank_txn_id"]           = $cross_check_status['data']['transfer']['utr'];
                    $updated_data["transaction_status"]    = $success_status;
                    $updated_data["payment_mode"]          = $cross_check_status['data']['transfer']['transferMode'];
                    $updated_data["txn_id"]                = $order_data['transaction_id'];
                    
                    $custom_data = $this->Finance_model->get_single_row('custom_data', ORDER,["order_id"=>$order_data['order_id']]);
                    // error_log("\n\n".format_date().'before : '.$custom_data.'<br>',3,'/var/www/html/cron/application/logs/payout.log');
                    $custom_data = json_decode($custom_data['custom_data'],true);
                    $custom_data['cf_phone_no'] = $cross_check_status['data']['transfer']['phone'];
                    $custom_data['cf_ifsc_code'] = $cross_check_status['data']['transfer']['ifsc'];
                    $custom_data['cf_ac_number'] = $cross_check_status['data']['transfer']['bankAccount'];
                    $custom_data['cf_beneficiary_id'] = $cross_check_status['data']['transfer']['beneId'];
                    $custom_data = json_encode($custom_data);
                    // error_log("\n\n".format_date().'after '.$order_custom_data.'<br>',3,'/var/www/html/cron/application/logs/payout.log');
                    
                    $order_arr = array(
                        "custom_data"=>$custom_data,
                        "status"=>1,
                        "order_id"=>$order_data['order_id']
                    );
                    $this->Finance_model->update_ord($order_arr);

                    $notify_data["status"] = $success_status;
                    $notify_data["reason"] =  'success';
                    $this->Finance_model->update_transaction($updated_data, $order_data['transaction_id']);
                }
                elseif(strtolower($cross_check_status['status'])== "failed"){
                        $notify_data["status"]              = $fail_status;
                        $notify_data["transaction_status"]  = $fail_status;
                        $notify_data["operation"]           = "add";
                        $notify_data["reason"]              = $cross_check_status['message'];
                }else{
                    $notify_data["status"]              = $pending_status;
                    $notify_data["transaction_status"]  = $pending_status;
                    $notify_data["reason"]              = $cross_check_status['message'];
                    $this->Finance_model->update_transaction(["transaction_status"=>$pending_status,"txn_id"=>$order_data['transaction_id']], $order_data['transaction_id']);
                }
            }
            elseif($transfer_req['subCode']==422)
            {
                $notify_data["status"]              = $fail_status;
                $notify_data["transaction_status"]  = $fail_status;
                $notify_data["operation"]           = "add";
                $notify_data["reason"]              = $transfer_req['message'];
                return $notify_data;
            }
            else{
                    $notify_data["status"]              = $pending_status;
                    $notify_data["reason"]              = $transfer_req['message'];
                    $updated_data["transaction_status"]  = $pending_status;
                    $updated_data["txn_id"]                = $order_data['transaction_id'];
                    $this->Finance_model->update_transaction($updated_data, $order_data['transaction_id']);
            }
        }elseif(strtolower($get_beneficiary['status'])== "failed"){
                $notify_data["status"]              = $pending_status;
                $notify_data["transaction_status"]  = $pending_status;
                $notify_data["reason"]              = $get_beneficiary['message'];
                $this->Finance_model->update_transaction(["transaction_status"=>$pending_status,"txn_id"=>$order_data['transaction_id']], $order_data['transaction_id']);
        }
        return $notify_data;
    }

    //send notification
    public function notify_user($notify_data)
    {
        $fail_status = 4;
        $success_status = 5;
        //quite critical case please handle carefully, if we mark failure then money will be added back to user account.
        if($notify_data['status'] == $fail_status)
        {
            $this->Finance_model->update_user_balance($notify_data['user_id'],["winning_amount"=>$notify_data['amount']], 'add');
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
            "date_added"=> $notify_data['date_added']
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

     public function mpesa_transfer($post_input,$order_data)
    {
        $this->load->library('Mpesa_lib',$this->app_config['auto_withdrawal']['custom_data']);
        $lib = new Mpesa_lib($this->app_config['auto_withdrawal']['custom_data']);
        $notify_data = array(
            "user_id" => $this->user_id,
            "amount"=>$post_input['amount'],
            "email"=>$post_input['email'],
            "first_name"=>$post_input['bank_detail']['first_name'],
            "order_id"=> $order_data['order_id'],
            "transaction_id"=>$order_data['transaction_id']
        );

        $amount = $post_input['amount'];
        if($post_input['isIW']==1)
        {
            $amount = floor($this->deduct_pgfee($amount));
        }

        //tds amount deduct
        if(isset($post_input['tds']) && $post_input['tds'] > 0){
            $amount = $amount - $post_input['tds'];
        }

        if($amount<1)
        {
            throw new Exception("Try with some greater amount");
        }
        $result = $lib->transfer($amount,$this->phone_no,$order_data);
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
        return number_format($amount,2,".","");
    }
}