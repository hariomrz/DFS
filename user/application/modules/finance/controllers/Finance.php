<?php

class Finance extends Common_Api_Controller {
    public $transaction_source_key_map = array();
    function __construct() {
        parent::__construct();
        $this->finance_lang = $this->lang->line("finance");
        $this->transaction_source_key_map[1] =array('key' => 'contest_name');
        $this->transaction_source_key_map[370] =array('key' => 'name');
        $this->transaction_source_key_map[371] =array('key' => 'name');
        $this->transaction_source_key_map[460] =array('key' => 'contest_name');
    }

    /**
     * Used to get user balance
     */
    function get_user_balance_post() {
        $user_id = $this->user_id;
        $this->load->model("Finance_model");
        $user_balance_cache_key = 'user_balance_' . $user_id;
        $user_balance = $this->get_cache_data($user_balance_cache_key);

        if (!$user_balance) {
            $user_balance = $this->Finance_model->get_user_balance($user_id);

            $user_balance['bonus_amount'] = number_format($user_balance['bonus_amount'], 2, '.', '');
            $user_balance['real_amount'] = number_format($user_balance['real_amount'], 2, '.', '');
            $user_balance['winning_amount'] = number_format($user_balance['winning_amount'], 2, '.', '');
            $user_balance['point_amount'] = $user_balance['point_balance'];

            $user_balance['rookie'] = array();
            $a_rookie = isset($this->app_config['allow_rookie_contest'])?$this->app_config['allow_rookie_contest']['key_value']:0;
            if($a_rookie)
            {
                $month = number_format(((strtotime(format_date()) - strtotime($user_balance['added_date'])) / 2592000),2,'.','');
                $user_balance['rookie']['month'] = $month;
                $user_balance['rookie']['winning'] = $user_balance['total_winning'];
            }
            $this->set_cache_data($user_balance_cache_key, $user_balance, REDIS_2_HOUR);
        }
        $wallet_content  = $this->Finance_model->get_wallet_content();
        $this->api_response_arry['data'] = array('user_balance' => $user_balance,
            "allowed_bonus_percantage" => MAX_BONUS_PERCEN_USE,"wallet_content"=>$wallet_content);
        
        $post_data = $this->post();
        $show_bonus_expire    = isset($post_data['be']) ? $post_data['be'] : 0;
        $show_coins_expire    = isset($post_data['ce']) ? $post_data['ce'] : 0;
        if($show_bonus_expire == 1) {
            $bonus_exiry_limit = -isset($this->app_config['bonus_expiry_limit'])?$this->app_config['bonus_expiry_limit']['key_value']:0;
            $bonus_expire = array('exvldt' => $bonus_exiry_limit, 'total' => 0, 'data' => array());
            $bonus_expire['total'] = $this->Finance_model->get_user_bonus_cash_going_to_expire($user_id, '+7', TRUE);
            if($bonus_expire['total'] > 0) {
                $bonus_expire['data'] = $this->Finance_model->get_user_bonus_cash_going_to_expire($user_id, '+7', FALSE);
            }
            $this->api_response_arry['data']['bonus_expire'] = $bonus_expire;
        }

        if($show_coins_expire==1 && $this->app_config['allow_coin_expiry']['key_value']==1)
        {
            $coin_expire = array(
                                'cexvldt'   =>$this->app_config['allow_coin_expiry']['custom_data']['ce_days_limit'],
                                'total'     =>0,
                                'data'      =>array(),
                                );
            $coin_expire['total'] = $this->Finance_model->get_user_coin_going_to_expire($user_id,$day ='+7', $count=TRUE);
            if($coin_expire['total'] > 0)
            {
                $coin_expire['data'] = $this->Finance_model->get_user_coin_going_to_expire($user_id,$day ='+7', $count=FALSE);
            }
            $this->api_response_arry['data']['coin_expire'] = $coin_expire;
        }
        
        $this->api_response();
    }

    public function get_pending_withdraw_post()
    {
        $user_id = $this->user_id;
        $this->load->model("Finance_model");
        $result_data = $this->Finance_model->get_pending_withdrawal($user_id);
        if(!empty($result_data))
        {
            $this->api_response_arry['data']['pending_request'] = $result_data;
            $this->api_response_arry['data']['allow_withdraw'] =0;
        }
        else{
            $this->api_response_arry['data']['pending_request'] = array();
            $this->api_response_arry['data']['allow_withdraw'] =1;
        }
        $this->api_response();
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

    /**
     * Used to get transaction history
     */
    function get_transaction_history_post() {
        $post_data = $this->post(); 

        $user_id = $this->user_id;

        $this->load->model("Finance_model");
        $season_type = isset($post_data['season_type']) ? $post_data['season_type'] : 1;
        $page_no    = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit      = isset($post_data['page_size']) ? $post_data['page_size'] : 10;
        $offset     = get_pagination_offset($page_no, $limit);
        $result_data = $this->Finance_model->get_transaction_history($user_id, $offset, $limit);
        
        $history = array();
        $transaction_msg = array();
        $transaction_messages = $this->get_transaction_msg();
        if(!empty($transaction_messages)){
            $trxn_msg = array_column($transaction_messages,$this->lang_abbr.'_message','source');
            $r_source_keys = array_flip(array_unique(array_column($result_data,'source')));
            $transaction_msg = array_intersect_key($trxn_msg,$r_source_keys);
        }
        
        $result = array();
        foreach($result_data as $key => $rs) {
            $event_msg = '-';
            $result_data[$key]['merchandise'] = ''; 
            $transaction_event = isset($transaction_msg[$rs['source']]) ? $transaction_msg[$rs['source']] : "";
            if(!empty($transaction_event)){
                $customData = json_decode($rs['custom_data'],TRUE);
                $result_data[$key]['custom_data'] = $customData;
                if(isset($customData['merchandise'])){
                    $result_data[$key]['merchandise'] = $customData['merchandise'];
                }
                // replace {{somthing}}
                $transaction_event = preg_replace_callback('/\{\{([\w]+)\}\}/', function($matches) use ($customData) {
                    $key = $matches[1];
                    if (isset($customData[$key])) {
                        return $customData[$key];
                    }
                    return $matches[0];
                }, $transaction_event);
            }
            $result_data[$key]['trans_desc'] = $transaction_event;
            $result[$key] = $result_data[$key];
        }

        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }
    
    private function filter_pickem_data($trans_msg,$value)
    {
        if(!empty($value['home']))
        {
            $trans_msg = str_replace('{{home}}',$value['home'],$trans_msg);
        }

        if(!empty($value['away']))
        {
            $trans_msg = str_replace('{{away}}',$value['away'],$trans_msg);
        }

        if(!empty($value['season_scheduled_date']))
        {
            $trans_msg = str_replace('{{match_date}}',$value['season_scheduled_date'],$trans_msg);
        }

        if(!empty($value['match_date']))
        {
            $trans_msg = str_replace('{{match_date}}',$value['match_date'],$trans_msg);
        }

        if(!empty($value['level_number']))
        {
            $trans_msg = str_replace('{{level_number}}',$value['level_number'],$trans_msg);
        }

        if(!empty($value['scheduled_date']))
        {
            $trans_msg = str_replace('{{scheduled_date}}',$value['scheduled_date'],$trans_msg);
        }

        return $trans_msg;
    }

    /**
     * Used to validate promo code
     */
    function validate_promo_code_post() {
        $this->form_validation->set_rules('amount', $this->lang->line("amount"), 'trim|required');
        $this->form_validation->set_rules('promo_code', $this->lang->line("promo_code"), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $this->load->model("Finance_model");
        $post_data = $this->post();
        $code_detail = $this->Finance_model->check_promo_code_details($post_data);
        $used_count = $this->Finance_model->get_promo_used_count($code_detail['promo_code_id']);
        if (empty($code_detail) || $code_detail['type'] == CONTEST_JOIN_TYPE) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->finance_lang["invalid_promo_code"];
            $this->api_response();
        } else if ($code_detail['type'] == DEPOSIT_RANGE_TYPE && ($post_data['amount'] < $code_detail['min_amount'] || $post_data['amount'] > $code_detail['max_amount'])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->finance_lang["promo_code_amount_range_invalid"];
            $this->api_response();
        } else if ($code_detail['type'] == FIRST_DEPOSIT_TYPE && $code_detail['total_used'] > 0) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->finance_lang["first_deposit_already_used"];
            $this->api_response();
        } else if ($code_detail['total_used'] >= $code_detail['per_user_allowed']) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->finance_lang["allowed_limit_exceed"];
            $this->api_response();
        } else if ($code_detail['max_usage_limit'] != 0 && ($used_count['total_used'] >= $code_detail['max_usage_limit'])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->finance_lang["max_usage_limit_code"];
            $this->api_response();
        } else {
            if ($code_detail['type'] == FIRST_DEPOSIT_TYPE) {
                $order_info = $this->Finance_model->get_single_row('count(order_id) as total', ORDER, array("source" => "7", "user_id" => $this->user_id, "source_id != " => "0"));
                if (!empty($order_info) && $order_info['total'] > 0) {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->finance_lang["first_deposit_already_used"];
                    $this->api_response();
                }
            }
            if ($code_detail['value_type'] == "1") {
                $bonus_amount = ($post_data['amount'] * $code_detail['discount']) / 100;
                if ($bonus_amount > $code_detail['benefit_cap']) {
                    $bonus_amount = $code_detail['benefit_cap'];
                }
            } else {
                $bonus_amount = $code_detail['discount'];
            }
        }
        $this->api_response_arry['data'] = array('promo_code_id' => $code_detail['promo_code_id'], 'discount' => $bonus_amount, "amount" => $post_data['amount'], "promo_code" => $post_data['promo_code'], "cash_type" => $code_detail['cash_type']);
        $this->api_response();
    }


    /**
     * Used to submit withdraw request
     */
    function withdraw_post() {
        if($this->input->post()) {
            $this->form_validation->set_rules('amount', $this->lang->line("amount"), 'trim|required|callback_decimal_numeric|callback_greater_than_zero');
            if (!$this->form_validation->run()) {
                $this->send_validation_errors();
            }
            try {
                $post_input = $this->input->post();
                $user_id = $this->user_id;
                $amount = $post_input['amount'];

                $validate_otp_res=$this->validate_wdl_2fa();
                
                if ($amount < $this->app_config['min_withdrawl']['key_value']) {
                    throw new Exception(sprintf($this->finance_lang["min_withdraw_value_error"], $this->app_config['min_withdrawl']['key_value']));                    
                }

                if ($amount > $this->app_config['max_withdrawl']['key_value']) {
                    throw new Exception(sprintf($this->finance_lang["max_withdraw_value_error"], $this->app_config['max_withdrawl']['key_value']));                    
                }

                $this->load->model("Finance_model");
                $result_data = $this->Finance_model->get_pending_withdrawal($user_id);
                if(!empty($result_data))
                {
                    throw new Exception($this->lang->line("multiple_withdraw_error"));
                }
                $user_bank_detail = $this->Finance_model->get_single_row('first_name,last_name,bank_name,ac_number,ifsc_code,micr_code,bank_document', USER_BANK_DETAIL, array('user_id' => $user_id));

                $post_input["user_id"]       = $user_id;
                $post_input["source"]        = 8;
                $post_input["source_id"]     = 0;        
                $post_input["status"]        = 0;
                $post_input["auto_withdrawal"] = array("is_auto_withdrawal" => 0); // for when Auto-withdrawal is DISABLED
                $post_input["bank_detail"] =  array("bank_name"=>$user_bank_detail['bank_name'],"ac_number"=>$user_bank_detail['ac_number'],"micr_code"=>$user_bank_detail['micr_code'],"ifsc_code"=>$user_bank_detail['ifsc_code'],"bank_document" => $user_bank_detail['bank_document']); 

                $this->Finance_model->withdraw($post_input);

                //remove wallet cache
                $this->delete_cache_data("user_balance_".$user_id);
                
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
    
    
    public function get_deals_post()
	{
        $cache_key = "deal_list";
        $deal_list = $this->get_cache_data($cache_key);
        if(empty($deal_list))
        {
            $this->load->model("Finance_model");  
            $deal_list = $this->Finance_model->get_deals();
            $this->set_cache_data($cache_key,$deal_list, REDIS_30_DAYS);
        }
       
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $deal_list;
		$this->api_response();
    }	
    
    public function withdraw_coins_post() { 
		$this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
		$this->form_validation->set_rules('amount', 'Amount', 'trim|required|callback_decimal_numeric|callback_greater_than_zero');
		$this->form_validation->set_rules('source', 'Source', 'trim|integer|required');
		$this->form_validation->set_rules('source_id', 'Source Id', 'trim|integer|required');
		$this->form_validation->set_rules('plateform', 'Plateform', 'trim|integer|required');
		$this->form_validation->set_rules('cash_type', 'Cash Type', 'trim|integer|required');
		// cash Type 0-real cash, 1-bonus cash, 2 for both(bonus cash and real cash), 3-Coins, 4- Winning 

		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
		}
		$amount = $this->input->post("amount");
		$post_input = $this->input->post();
        $this->load->model("Finance_model");  

        switch ($this->input->post("source")) 
		{
			//make prediction
			case 40:
			case 220://open predictor make prediction
				$post_input["status"] = 1;
				break;
        }

        $data = $this->Finance_model->withdraw_coins($post_input);

        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['service_name'] = 'withdraw_coin';
		$this->api_response_arry['data'] = $data;
		$this->api_response();


		
    }
    
    public function get_promo_codes_post() {       
        $this->load->model("Finance_model");  
        $promo_codes = $this->Finance_model->get_promo_codes();        
		$this->api_response_arry['data'] = $promo_codes;
		$this->api_response();
    }


    function sync_transaction_messages_post() {
        $this->load->model('finance/finance_model');
        $this->finance_model->sync_transaction_messages();
    }

    /**
     * This function used for the send withdrawal otp to the given flow enable(email or mobile) 
    * Params: 
    * Return: json arrayy
    */
    public function generate_wdl_otp_post(){
        
        $withdrawal_2fa=isset($this->app_config['allow_withdrawal_2fa'])?$this->app_config['allow_withdrawal_2fa']['key_value']:0;

        if($withdrawal_2fa){
            if(empty($this->email) && LOGIN_FLOW == 1){
                $error = array('phone_no'=>$this->lang->line('no_account_found'));
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['error']           = $error;
                $this->api_response();
            }elseif(empty($this->phone_no) && LOGIN_FLOW == 0){
                $error = array('phone_no'=>$this->lang->line('no_account_found'));
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['error']           = $error;
                $this->api_response();
            }   

            $input_data = $data_hash = array();
            $input_data['hash'] = '';
            $input_data['type'] = 'e';
            $hash = $otp= '';
            $message = $this->lang->line('resend_otp_send_success');
            $this->load->model('finance/finance_model');
            if(!empty($this->email) && LOGIN_FLOW == 1){
                $input_data['entity_no'] = $this->email;
                $data_hash= generate_verify_otp($input_data);
                $hash = isset($data_hash['hash'])?$data_hash['hash']:'';

                $userdata = $this->finance_model->get_single_row("user_name,user_id,email", USER, array('email' =>$this->email));
                $data_hash['user_name'] = isset($userdata['user_name'])?$userdata['user_name']:'';
                $data_hash['user_id'] = isset($userdata['user_id'])?$userdata['user_id']:'';
                $data_hash['email'] = isset($userdata['email'])?$userdata['email']:'';
                $otp=$this->send_otp_on_email($data_hash);

                $message = $this->lang->line('email_otp_send_success');
            }elseif(!empty($this->phone_no) && LOGIN_FLOW == 0){
                $input_data['entity_no'] = $this->phone_no;
                $data_hash= generate_verify_otp($input_data);
                
                $hash = isset($data_hash['hash'])?$data_hash['hash']:'';

                $userdata = $this->finance_model->get_single_row("user_name,phone_no,phone_code", USER, array('phone_no' =>$this->phone_no));
                $data_hash['phone_code'] = isset($userdata['phone_code'])?$userdata['phone_code']:'91';
                $data_hash['phone_no'] = isset($userdata['phone_no'])?$userdata['phone_no']:'';
                
                $otp=$this->send_otp_on_mobile($data_hash);
                $message = $this->lang->line('otp_send_success');
            }

            $res = array();
            $res['hash']=$hash;
            //$res['otp']=$otp;
            
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['message'] = $message;
            $this->api_response_arry['data'] = $res;
            $this->api_response();
        }else{
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error']           = $this->finance_lang['wdl_2fa_disable'];
            $this->api_response();
        }
       
    }

    /**
     * used for send otp to email
     * @param
     * @return json array
     */
    public function send_otp_on_email($data) {
        
        $otp = $data['otp'];
        $this->load->model(array('notification/Notify_nosql_model'));
        $data['otp'] = $otp;
        $content        = array('otp' => $otp, 'email' => $data['email']);
        $notify_data    = array();
        $notify_data['queue_name'] = "email_otp";
        $notify_data['notification_type']           = 133;
        $notify_data['notification_destination']    = 4;
        $notify_data["source_id"]   = 1;
        $notify_data["user_id"]     = $data['user_id'];
        $notify_data["user_name"]   = $data['user_name'];
        $notify_data["to"]          = $data["email"];
        $notify_data["added_date"]  = format_date();
        $notify_data["modified_date"] = format_date();
        $notify_data["subject"] = $this->lang->line('signup_email_subject');
        $notify_data["content"] = json_encode($content);
        $this->Notify_nosql_model->send_notification($notify_data);

        return $otp;
    }

    /**
     * used for send otp to mobile
     * @param
     * @return json array
     */
    public function send_otp_on_mobile($data){
        
        $where = $data['phone_no'];
        $otp = isset($data['otp']) ? $data['otp'] : "";
        $phone_code = ($data['phone_code']) ? $data['phone_code'] : DEFAULT_PHONE_CODE;

        $data['otp'] = $otp;
        $sms_data = array();
        $sms_data['otp'] = $otp;
        $sms_data['mobile'] = $data['phone_no'];
        $sms_data['phone_code'] = $phone_code;
        $otp_message = 'Your OTP is {OTP}. Please enter this to verify your mobile. Thank you for choosing '.SITE_TITLE;
        if(isset($this->app_config['otp_message']) && !empty($this->app_config['otp_message'])){
            $otp_message = $this->app_config['otp_message']['key_value'];
        }
        $otp_message = str_replace('{OTP}',$otp,$otp_message);
        $sms_data['message'] = $otp_message;

        $otp_message = str_replace('{HASH}','',$otp_message);
        $sms_data['message'] = $otp_message;

        $this->load->helper('queue_helper');
        add_data_in_queue($sms_data, 'sms');
        return $otp;
    }

    /**
     * This function used for the validate dwl otp
     * params: post
     * return: true/false
     */
    public function validate_wdl_2fa(){
        
        $withdrawal_2fa=isset($this->app_config['allow_withdrawal_2fa'])?$this->app_config['allow_withdrawal_2fa']['key_value']:0;

        if($withdrawal_2fa){
            $post_input = $this->input->post();
            $input_data['type'] = 'd';
            $input_data['hash'] = isset($post_input['hash'])?$post_input['hash']:'';
            $input_data['otp']  = isset($post_input['otp'])?$post_input['otp']:'';
            
            if(!empty($this->email) && LOGIN_FLOW == 1){
                $input_data['entity_no']=$this->email;
            }elseif(!empty($this->phone_no) && LOGIN_FLOW == 0){
                $input_data['entity_no']=$this->phone_no;
            }
            
            $data_hash= generate_verify_otp($input_data);
            
            if($data_hash['status'] != '200'){
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']         = $data_hash['message'];
                $this->api_response_arry['data']            = array('otp_error'=>1);
                $this->api_response();
            }

            if($data_hash['status'] == '200'){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }

    /*
     * Used for get tds details
     * @param array $post_data
     * @return array
     */
    public function get_tds_detail_post(){
        $post_data = $this->input->post();
        $user_id = $this->user_id;
        $fy_arr = get_financial_year();

        $post_data['user_id'] = $user_id;
        $post_data['start_date'] = $fy_arr['start_date'];
        $post_data['end_date'] = $fy_arr['end_date'];
        //echo "<pre>";print_r($post_data);die;

        $this->load->model("Finance_model");
        $record_list = $this->Finance_model->get_tds_detail($post_data);
        $this->api_response_arry['data'] = $record_list;
        $this->api_response();
    }

    /**
     * Used for get tds document list
     * @param array $post_data
     * @return array
     */
    public function get_tds_document_post(){
        $post_data = $this->input->post();
        $user_id = $this->user_id;
        $fy_arr = get_financial_year('last');
        $this->load->model("Finance_model");
        $record_list = $this->Finance_model->get_tds_document($user_id);
        $record_list['fy'] = $fy_arr['fy'];
        $this->api_response_arry['data'] = $record_list;
        $this->api_response();
    }

    /**
     * Used for download tds report
     * @param array $post_data
     * @return array
     */
    public function get_tds_report_get(){
        $post_data = $this->input->get();
        $user_id = $this->user_id;
        $fy_arr = get_financial_year('last');
        $post_data['user_id'] = $user_id;
        $post_data['start_date'] = $fy_arr['start_date'];
        $post_data['end_date'] = $fy_arr['end_date'];
        $this->load->model("Finance_model");
        $report_list = $this->Finance_model->get_tds_report($post_data);
        //echo "<pre>";print_r($report_list);die;
        $name = 'TDS_Report_FY_'.$fy_arr['fy']."_".format_date('today','Y-m-d').'.csv';
        if(!empty($report_list))
        {
            $table_field = array("Game Name","ScheduleDate","Total Entry Fee","Total Winnings","Net Winnings");
            $header = array($table_field);
            $report_data = array_merge($header,$report_list);
            $total_entry = array_sum(array_column($report_list,'total_entry'));
            $total_winning = array_sum(array_column($report_list,'total_winning'));
            $total_net_winning = array_sum(array_column($report_list,'net_winning'));
            $report_data[] = array("","Total",$total_entry,$total_winning,$total_net_winning);
            $this->load->helper('download');
            $data = array_to_csv($report_data);
            $data = "Username :".$this->user_name." | Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
            force_download($name, $data);
        }
        else
        {
            $result = "no record found";
            $this->load->helper('download');
            $data = array_to_csv($result);
            force_download($name, $result);
        }
    }

    /**
     * Used for download gst report
     * @param array $post_data
     * @return array
     */
    public function get_gst_report_get(){
        $post_data = $this->input->get();
        $user_id = $this->user_id;
        $fy_arr = get_financial_year('current');
        $post_data['user_id'] = $user_id;
        $post_data['start_date'] = $fy_arr['start_date'];
        $post_data['end_date'] = $fy_arr['end_date'];
        $this->load->model("Finance_model");
        
        $report_list = $this->Finance_model->get_gst_report($post_data);
        $name = 'GST_Report_FY_'.$fy_arr['fy']."_".format_date('today','Y-m-d').'.csv';
        if(!empty($report_list))
        {
            $table_field = array_keys($report_list[0]);
            $header = array($table_field);
            
            $report_data = array_merge($header,$report_list);
            $total_cgst = array_sum(array_column($report_list,'cgst'));
            $total_sgst = array_sum(array_column($report_list,'sgst'));
            $total_igst = array_sum(array_column($report_list,'igst'));
            $report_data[] = array("","","","Total",$total_cgst,$total_sgst,$total_igst);
            $this->load->helper('download');
            $data = array_to_csv($report_data);
            $data = "Username :".$this->user_name." | Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
            force_download($name, $data);
        }
        else
        {
            $result = "no record found";
            $this->load->helper('download');
            $data = array_to_csv($result);
            force_download($name, $result);
        }
    }

    /**
     * Used for generating gst report as PDF
     * @param array $invoice_id
     * @return PDF report
     */
	public function gst_invoice_download_get()
	{ 
        $get_data 		= $this->get();

        $order_unique_id = isset($_REQUEST['order_unique_id']) ? $_REQUEST['order_unique_id'] : "";
        if(!isset($order_unique_id) || empty($order_unique_id)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Order unique id field required.";
            $this->api_response();
        }

        $user_id = $this->user_id;
        $this->load->model("Finance_model");
        if(isset($order_unique_id) && !empty($order_unique_id)){
            $order_info 	= $this->Finance_model->get_single_row('order_id,',ORDER,array('order_unique_id' => $order_unique_id,'user_id' =>$user_id));
            
            $order_id = isset($order_info['order_id'])?$order_info['order_id']:'';
            $invoice_info 	= $this->Finance_model->get_single_row('invoice_id,invoice_type,user_id,txn_date,txn_amount,gst_number,sgst,cgst,igst,match_name,gst_rate',GST_REPORT,array('user_id' =>$user_id,'order_id'=>$order_id));
        	
            if(empty($invoice_info)){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = "GST Invoice not available yet. please try again after some time.";
                $this->api_response();
            }


            if(!empty($invoice_info))
            {
                $get_user_info 	= $this->Finance_model->get_single_row('phone_no,phone_code,user_name,first_name,last_name,email,address,city,zip_code,master_state_id',USER,array('user_id' => $invoice_info['user_id']));
                $invoice_data = array();
                $invoice_data['company_name'] 		= $this->app_config['allow_gst']['custom_data']['firm_name'];
                $invoice_data['company_address'] 	= $this->app_config['allow_gst']['custom_data']['firm_address'];
                $invoice_data['company_contact'] 	= $this->app_config['allow_gst']['custom_data']['contact_no'];
                $invoice_data['currency'] 			= CURRENCY_CODE_HTML;
                $invoice_data['date'] 				= $invoice_info['txn_date'];
                $invoice_data['invoice_no'] 		= $invoice_info['invoice_id'];
                $invoice_data['invoice_type'] 		= $invoice_info['invoice_type'];

                $invoice_data['phone_no'] 			= $get_user_info['phone_code'].$get_user_info['phone_no'];
                $invoice_data['user_name'] 			= $get_user_info['user_name'];
                $invoice_data['full_name'] 			= $get_user_info['first_name']." ".$get_user_info['last_name'];
                $invoice_data['email'] 				= $get_user_info['email'];
                $invoice_data['address'] 			= $get_user_info['address'];
                $invoice_data['city'] 				= $get_user_info['city'];
                $invoice_data['zip_code'] 			= $get_user_info['zip_code'];
                $invoice_data['gst_number'] 		= $invoice_info['gst_number'];
                $master_state_id 					= $get_user_info['master_state_id'];
                if(isset($master_state_id) && $master_state_id != "")
                {
                    $get_state_name = $this->Finance_model->get_single_row('name',MASTER_STATE,array('master_state_id' => $master_state_id));
                    $invoice_data['state'] = $get_state_name['name'];
                }
                else
                {
                    $get_state_name = $this->Finance_model->get_single_row('name',MASTER_STATE,array('master_state_id' => $this->app_config['allow_gst']['custom_data']['state_id']));
                    $invoice_data['state'] = $get_state_name['name'];
                }
                $gst_rate = number_format($invoice_info['gst_rate'],"0",".","");
                $cgst_rate = number_format(($gst_rate / 2),"0",".","");
                $sgst_rate = number_format(($gst_rate / 2),"0",".","");
                $total_gst = $invoice_info['sgst']+$invoice_info['cgst']+$invoice_info['igst'];
                $invoice_info['taxable_value'] = 0;
                if($invoice_data['invoice_type'] == "1"){
                    $invoice_info['taxable_value'] = $invoice_info['txn_amount'];
                }else{
                    $invoice_info['taxable_value'] = $invoice_info['rake_amount'];
                }
                $total = number_format(($total_gst + $invoice_info['taxable_value']),"2",".","");
                $data_arr = array();
                $data_arr['heading'] = $invoice_info['match_name'];
                $data_arr['taxable_value'] = $invoice_info['taxable_value'];
                $data_arr['total_gst'] = $total_gst;
                $data_arr['fields'] = array();
                if($invoice_data['invoice_type'] == 1){
                    $data_arr['fields'][] = array("contest_name"=>"Name","entry_fee"=>"Amount(INR)","taxable_value"=>"Taxable Value <br>(INR)*","sgst"=>"SGST<br>@".$sgst_rate."% (INR)","cgst"=>"CGST<br>@".$cgst_rate."% (INR)","igst"=>"IGST<br>@".$gst_rate."% (INR)","total"=>"Total");
                    $data_arr['fields'][] = array("contest_name"=>$invoice_info['match_name'],"entry_fee"=>$invoice_info['txn_amount'],"taxable_value"=>$invoice_info['taxable_value'],"sgst"=>$invoice_info['sgst'],"cgst"=>$invoice_info['cgst'],"igst"=>$invoice_info['igst'],"total"=>$total);
                    $data_arr['fields'][] = array("contest_name"=>"Total","entry_fee"=>$invoice_info['txn_amount'],"taxable_value"=>$invoice_info['taxable_value'],"sgst"=>$invoice_info['sgst'],"cgst"=>$invoice_info['cgst'],"igst"=>$invoice_info['igst'],"total"=>$total);
                }
                $invoice_data['data_list'] = $data_arr;
                if(!empty($invoice_data))
                {
                    $gst_pdf_html_data['data'] = $invoice_data;
                    $html = $this->load->view('invoice',$gst_pdf_html_data,true);
                    ini_set('memory_limit', '-1');
                    $this->load->helper('dompdf_helper');
                    generate_pdf('gst_invoice_download',$html);
                }
            }
        }
	}
}