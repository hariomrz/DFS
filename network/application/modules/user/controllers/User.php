<?php

class User extends Common_Api_Controller {
   
    function __construct() {
        parent::__construct();
        
    }

    /**
     * Used to get user balance
     */
    function get_user_balance_post() {
        $user_id = $this->user_id;
        $this->load->model("User_model");
        $user_balance_cache_key = 'user_balance_' . $user_id;
        $user_balance = $this->get_cache_data($user_balance_cache_key);

        if (!$user_balance) {
            $user_balance = $this->User_model->get_user_balance($user_id);

            $user_balance['bonus_amount'] = number_format($user_balance['bonus_amount'], 2, '.', '');
            $user_balance['real_amount'] = number_format($user_balance['real_amount'], 2, '.', '');
            $user_balance['winning_amount'] = number_format($user_balance['winning_amount'], 2, '.', '');
            $user_balance['point_amount'] = $user_balance['point_balance'];
            $this->set_cache_data($user_balance_cache_key, $user_balance, REDIS_2_HOUR);
        }
        $this->api_response_arry['data'] = array('user_balance' => $user_balance,
            "allowed_bonus_percantage" => MAX_BONUS_PERCEN_USE);
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

        $transaction_messages = $this->get_transaction_msg();

        if (!empty($result_data)) {
            if ($season_type == 1) {
                $contest_array = array_filter($result_data, function ($var) {
                    return (in_array($var["source"], array(1, 2, 3)));
                });
                if (!empty($contest_array)) {
                    $contest_ids = array_column($contest_array, 'source_id');
                    $contest_ids = array_unique($contest_ids);
                    $this->load->model("fantasy/Fantasy_model");
                    $contest_res = $this->Fantasy_model->get_collection_by_lineup_master_contest_id($contest_ids);

                    if (!empty($contest_res)) {
                        $contest_arr = array_column($contest_res, NULL, 'lineup_master_contest_id');
                    }
                }
            }

            foreach ($result_data as $value) {
                $contest = array();
                if (in_array($value["source"], array(1, 2, 3))) {
                    switch ($season_type) {
                        case 1:
                            $contest = isset($contest_arr[$value["source_id"]]) ? $contest_arr[$value["source_id"]] : array();

                            if(isset($contest['contest_name']))
                            {
                                $value['contest_name'] = $contest['contest_name'];
                            }

                          
                            break;
                        default:
                            # code...
                            break;
                    }
                }

                $contest_name = isset($contest["contest_name"]) ? $contest["contest_name"] : '';
                $collection_name = isset($contest["collection_name"]) ? $contest["collection_name"] : '';
                
                $collection_info = $collection_name != '' ? $collection_name : $contest_name;

                switch ($value["source"]) {
                    /* By Admin */
                    case 0:
                        if ($value["type"] == 0) {
                            $value["real_amount"] > 0 && $value["trans_desc"] = $this->finance_lang["admin_deposit"];
                            $value["bonus_amount"] > 0 && $value["trans_desc"] = $this->finance_lang["admin_deposit_bonus"];
                            $value["winning_amount"] > 0 && $value["trans_desc"] = $this->finance_lang["admin_deposit_winning"];
                            $value["points"] > 0 && $value["trans_desc"] = $this->finance_lang["admin_deposit_points"];
                        }

                        if ($value["type"] == 1) {
                            $value["real_amount"] > 0 && $value["trans_desc"] = $this->finance_lang["admin_withdrawal"];
                            $value["bonus_amount"] > 0 && $value["trans_desc"] = $this->finance_lang["admin_withdrawal_bonus"];
                        }
                        break;
                    /* JoinGame */
                    // case 1:
                    //     $value["trans_desc"] = (!empty($collection_info)) ? sprintf($this->finance_lang["entry_fee_for"], $collection_info) : $this->finance_lang["entry_fee_for_contest"];
                    //     break;
                    // /* GameCancel */
                    // case 2:
                    //     $value["trans_desc"] = $this->finance_lang["refund_entry_fee_contest"];
                    //     break;
                    // /* GameWon */
                    // case 3:
                    //     $value["trans_desc"] = $this->finance_lang["won_contest_prize"];
                    //     break;
                    // /* FriendRefferalBonus */
                    // case 4:
                    //     $value["trans_desc"] = $this->finance_lang["firend_refferal"];
                    //     break;
                    // /* BonusExpired */
                    // case 5:
                    //     $value["trans_desc"] = $this->finance_lang["bonus_expired"];
                    //     break;
                    // /* Promocode */
                    // case 6:
                    //     $value["trans_desc"] = $this->finance_lang["promocode"];
                    //     break;
                    // /* Deposit */
                    // case 7:
                    //     $value["trans_desc"] = $this->finance_lang["amount_deposit"];
                    //     break;
                    // /* Withdraw */
                    // case 8:
                    //     $value["trans_desc"] = $this->finance_lang["amount_withdrawal"];
                    //     break;
                    // /* Withdraw */
                    // case 9:
                    //     $value["trans_desc"] = $this->finance_lang["bonus_on_deposit"];
                    //     break;
                    // /* Deposit Coins */
                    // case 10:
                    //     $value["trans_desc"] = $this->finance_lang["coin_deposit"];
                    //     break;
                    // /* TDS Withdraw */
                    // case 11:
                    //     $value["trans_desc"] = $this->finance_lang["total_tds_deducted"];
                    //     break;
                    // /* signup bonus */
                    // case 12:
                    // case 50:
                    //     $value["trans_desc"] = $this->finance_lang["signup_bonus"];
                    //     break;
                    // case 13:
                    //     $value["trans_desc"] = $this->finance_lang["referral_bonus_friend_mobile_verified"];
                    //     break;
                    // /* Referral Contest */
                    // case 14:
                    //     $value["trans_desc"] = $this->finance_lang["referral_bonus_pan_verification"];
                    //     break;
                    // case 15:
                    //     $value["trans_desc"] = $this->finance_lang["referral_contest"];
                    //     break;
                    // case 21:
                    //     $value["trans_desc"] = $this->finance_lang["redeemed_from_store"];
                    //     break;
                    // case 40:
                    //     $value["trans_desc"] = $this->finance_lang["bet_for_prediction"];
                    //     break;
                    // case 41:
                    //     $value["trans_desc"] = $this->finance_lang["prediction_won"];
                    //     break;
                    // case 102:
                    //     $value["trans_desc"] = $this->finance_lang["order_cancel"];
                    //     break;
                    // case 135:
                    //     $value["trans_desc"] = $this->finance_lang["deal_redeem_bonus_text"];
                    //     break;
                    // case 136:
                    //     $value["trans_desc"] = $this->finance_lang["deal_redeem_cash_text"];
                    //     break;
                    // case 137:
                    //     $value["trans_desc"] = $this->finance_lang["deal_redeem_coin_text"];
                    //     break;
                    //For promocode section
                    case 30:
                    case 31:
                    case 32:
                        $value["trans_desc"] = $this->lang->line("promocode_default_desc");
                        //For real cash
                        if ($value['real_amount'] > 0) {
                            $value["trans_desc"] = $this->lang->line("promocode_cash_desc");
                        }
                        //For bonus cash
                        if ($value['bonus_amount'] > 0) {
                            $value["trans_desc"] = $this->lang->line("promocode_bonus_desc");
                        }
                        break;
                    default:{

                        if(isset($transaction_messages[$value["source"]]))
                        {
                            if(isset($this->transaction_source_key_map[$value["source"]]))
                            {
                                $value['trans_desc'] =  sprintf($transaction_messages[$value["source"]][$this->lang_abbr.'_message'], $value[$this->transaction_source_key_map[$value["source"]]['key']]);

                               
                            }
                            else
                            {
                                $value['trans_desc'] = $transaction_messages[$value["source"]][$this->lang_abbr.'_message'];
                            }
                            
                        }
                    }    
                }
                

                if (!isset($value["trans_desc"]) || $value["trans_desc"] == "") {
                    $value["trans_desc"] = $value["reason"];
                }
                $history[] = $value;
            }
        }

        $this->api_response_arry['data'] = $history;
        $this->api_response();
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

                if ($amount < WITHDRAWAL_LIMIT) {
                    throw new Exception(sprintf($this->finance_lang["min_withdraw_value_error"], WITHDRAWAL_LIMIT));                    
                }

                $this->load->model("Finance_model");
                $result_data = $this->Finance_model->get_pending_withdrawal($user_id);
                if(!empty($result_data))
                {
                    throw new Exception($this->lang->line("multiple_withdraw_error"));
                }

                $post_input["user_id"]       = $user_id;
                $post_input["source"]        = 8;
                $post_input["source_id"]     = 0;        
                $post_input["status"]        = 0;

                $this->Finance_model->withdraw($post_input);
                
                //delete user balance cache data
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
				$post_input["status"] = 1;
				break;
        }

       $data = $this->Finance_model->withdraw_coins($post_input);

       $user_cache_key = "user_balance_".$post_input['user_id'];
       $this->delete_cache_data($user_cache_key);

        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['service_name'] = 'withdraw_coin';
		$this->api_response_arry['data'] = $data;
		$this->api_response();


		
	}

    function sync_transaction_messages_post() {
        $this->load->model('finance/finance_model');
        $this->finance_model->sync_transaction_messages();
    }
}