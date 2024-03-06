<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MYREST_Controller {
	public $finance_lang;
	public $transaction_source_key_map = array();
	public $download_app_source=471;
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_model');
		$_POST = $this->input->post();	
		//1=> join game
        $this->transaction_source_key_map[1] =array('key' => 'contest_name');
        $this->transaction_source_key_map[370] =array('key' => 'name');
        $this->transaction_source_key_map[371] =array('key' => 'name');
		$this->finance_lang = $this->lang->line('finance');		
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
		$this->admin_roles_manage($this->admin_id,'user_management');
	}

	/** METHODS ADDED WHILE OPTIMIZATION */
	public function users_post()
	{
		$this->form_validation->set_rules('from_date', 'From Date', 'trim|required');
		$this->form_validation->set_rules('to_date', 'To Date', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$result = $this->User_model->get_all_user();
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;
		$pending_pan = $this->User_model->get_pending_pancard_count();
		$this->api_response_arry['data']['pending_pan_total']  = $pending_pan['pending_pan_cards'];
		$this->api_response();
	}

	public function get_user_basic_post()
	{
     	$user_id = $this->post('user_unique_id'); 
		$result = $this->User_model->get_user_basic_by_id($user_id);
		$_POST['user_id']=$result['user_id'];
		$_POST['items_perpage']=1;
		$_POST['current_page']=1;
		$_POST['csv']=false;
		$_POST['status']="0";

		if ($this->post('withdraw_method') == "")
		{
			$_POST['withdraw_method'] = $this->app_config['auto_withdrawal']['key_value'];
		}

		// $this->load->model('finance/finance_model');
		$fin_data = $this->User_model->get_user_pending_withdrawal_request();
		// $fin_data = $this->finance_model->get_all_withdrawal_request(true);
		$result['is_withdraw_request']= ($fin_data['total']>0)?true: false;
		$result['withdraw_data'] = ($fin_data['total']>0)?$fin_data['result']: array();
		$result['bs_status'] = explode("_",$result['bs_status'])[0];
		if (isset($result['withdraw_data']['custom_data']))
		{
			$temp_custom_data = json_decode($result['withdraw_data']['custom_data'], TRUE);
        	$result['withdraw_data']['is_auto_withdrawal'] = $temp_custom_data['is_auto_withdrawal'];
		}
		//$result['image']= get_image(0,$result['image']);

		$coins_data =  $this->User_model->get_earned_and_redeem_coins($result['user_id']);

		if(!empty($coins_data))
		{
			$coins_data = array_column($coins_data,'coin_sums','types');
			if(isset($coins_data[0]))
			{
				$result['earned_coins'] = $coins_data[0];
			}

			if(isset($coins_data[1]))
			{
				$result['redeem_coins'] = $coins_data[1];
			}
		}
		
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;
		$this->api_response();
	}

	public function get_user_nosql_data_post()
	{
		$this->form_validation->set_rules('user_id', 'user_id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
		$this->load->model('User_nosql_model');
		$user_nosql_data = $this->User_nosql_model->get_user_nosql_data();	
		if(empty($user_nosql_data)){
			$user_nosql_data = array();
			$user_nosql_data['balance'] = 0;
			$user_nosql_data['deposit_rank'] = 0;
			$user_nosql_data['total_joined'] = 0;
			$user_nosql_data['total_joined_rank'] = 0;
			$user_nosql_data['total_referral'] = 0;
			$user_nosql_data['total_referral_rank'] = 0;
			$user_nosql_data['total_referral_amount'] = 0;
			$user_nosql_data['total_withdraw'] = 0;
			$user_nosql_data['winning_balance'] = 0;
			$user_nosql_data['winning_rank'] = 0;
			$user_nosql_data['winning_rank'] = 0;


		}
		else{
			$user_nosql_data['total_referral_amount'] = round($user_nosql_data['total_referral_amount'],2,PHP_ROUND_HALF_UP);
		}	
		$this->api_response_arry['data']			= $user_nosql_data;
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

	public function user_transaction_history_post()
	{
		$this->form_validation->set_rules('user_id', 'User ID', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data	= $this->input->post();

		$tmp_res = $this->User_model->user_transaction_history();
		$result  = $tmp_res["result"];

		if (isset($post_data['from_withdraw_popup']) && $post_data['from_withdraw_popup'] == "1")
		{
			$tmp_res = [];
			$result  = [];
			$tmp_res = $this->User_model->get_withdrawal_popup_data();
			$result  = $tmp_res["result"];
		}
		$transaction_msg = array();
        $transaction_messages = $this->get_transaction_msg();
        if(!empty($transaction_messages)){
			$trxn_msg = array_column($transaction_messages,'en_message','source');
	        $r_source_keys = array_flip(array_unique(array_column($result,'source')));
	        $transaction_msg = array_intersect_key($trxn_msg,$r_source_keys);
        }
		$data['transaction_msg'] = $transaction_msg;

		foreach ($result as $key => $rs) {
			$result[$key]['merchandise'] = '-'; 
			$transaction_event = isset($transaction_msg[$rs['source']]) ? $transaction_msg[$rs['source']] : '';
			$customData = json_decode($rs['custom_data'],TRUE);
			$result[$key]['custom_data']=$customData;
			if(isset($customData['merchandise'])){
				$result[$key]['merchandise'] = $customData['merchandise'];
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
			$result[$key]['trans_desc']=$transaction_event;
		}

		$data['result'] = $result;
		$data['total'] = $tmp_res["total"];
		if (isset($post_data['from_withdraw_popup']) && $post_data['from_withdraw_popup'] == "1")
		{
			$data['total_deposit'] 		= $tmp_res["total_deposit"];
			$data['total_withdrawal'] 	= $tmp_res["total_withdrawal"];
		}

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $data;
		$this->api_response();
	}

	private function filter_pickem_data($trans_msg,$value)
    {    	
		if(!is_array($value['custom_data']))
		{
			$value['custom_data'] = json_decode($value['custom_data'],TRUE);
		}
		if($value['custom_data']==""){
			$value['custom_data'] = array();
		}
		
		$value = array_merge($value,$value['custom_data']);
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
            $trans_msg = str_replace('{{match}}',$value['scheduled_date'],$trans_msg);
        }
        if(in_array($value['source'],array(500,501,502))){
        	if(isset($value['custom_data'])){
        		foreach ($value['custom_data'] as $ck => $cv) {
	            	if (str_contains($trans_msg,"{{".$ck."}}")) {
	              		$trans_msg = str_replace("{{".$ck."}}",$cv,$trans_msg);
	            	}
        		}
        	}
    	}

        return $trans_msg;
    }

	public function change_user_status_post()
	{
		$this->form_validation->set_rules('user_unique_id', 'User Unique Id', 'trim|required');
		$this->form_validation->set_rules('status', 'Status', 'trim|required');
		$this->form_validation->set_rules('wdl_status', 'Withdrawal Status', 'trim');
		$status = $this->input->post("status");
		$wdl_status = $this->input->post("wdl_status");
		if($status == 0 || ($status == 1 && $wdl_status==2))
		{
			$this->form_validation->set_rules('reason', 'Reason', 'trim|required');
		}
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
		$user_unique_id = $this->input->post('user_unique_id');
		$reason = $this->input->post("reason")?$this->input->post("reason"):"";
		$data_arr = array(
						"status"		=> $status,
						"status_reason"	=> $reason,
						"wdl_status"	=> $wdl_status ? $wdl_status : 1,
					);
		$result = $this->User_model->update_user_detail($user_unique_id,$data_arr);

		if($result)
		{
			$user = $this->User_model->get_single_row("user_id,email,user_name",USER,array("user_unique_id"=>$user_unique_id));
			if($status == 0)
			{
				$this->User_model->delete_active_login($user['user_id']);
				$this->load->model('User_nosql_model');
				$this->User_nosql_model->delete_nosql_active_login_data($user['user_id']);

			
				$tmp = array();
				$tmp["notification_type"] 			= 137; //block account
				$tmp["source_id"] 				 	= 0;
				$tmp["notification_destination"] 	= 5; //  Web,Email
				$tmp["user_id"] 					= $user['user_id'];
				$tmp["to"] 							= $user['email'];
				$tmp["subject"] 					= $this->lang->line('blocked_subject');
				$tmp["user_name"] 					= $user['user_name'];
				$tmp["added_date"] 					= date("Y-m-d H:i:s");
				$tmp["modified_date"] 				= date("Y-m-d H:i:s");
				$update_arr['message'] 				= $reason;
				$tmp["content"] 					= json_encode($update_arr);
				/*  $this->add_notification($tmp); */
				$this->load->model('User_nosql_model');
				$this->User_nosql_model->send_notification($tmp);
			}		
			$this->api_response_arry['response_code'] 	= 200;
			$this->api_response_arry['message']  		= $this->lang->line('update_status_success');
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= $this->lang->line('no_change');
			$this->api_response();
		}	
	}

	public function get_user_detail_post()
	{
		$user_id = $this->post('user_unique_id'); 
		$result = $this->User_model->get_user_detail_by_id($user_id);
		$result["bank_data"] = $this->User_model->get_single_row('first_name,last_name,bank_name,ac_number,ifsc_code,micr_code', USER_BANK_DETAIL, array('user_id'=>$result["user_id"]));
		
		//get referral user name
		$result["referee"] = $this->User_model->get_user_referee($result['user_id']);

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;
		$this->api_response();
	}

	function get_user_bank_data_post(){
		$this->form_validation->set_rules('user_unique_id', 'user_unique_id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$user_bank_data = $this->User_model->get_user_bank_data();		
			
		
		$this->api_response_arry['service_name']	= 'get_user_bank_data';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $user_bank_data;
		$this->api_response();	
	}

	/**
	 * Used to get self exclusion  for user
	 */
	public function get_user_self_exclusion_post() {
		$this->form_validation->set_rules('user_id', 'user id', 'trim|required');
		if (!$this->form_validation->run())  {
			$this->send_validation_errors();
		}  
		//get user self exclusion 
		$post_data = $this->input->post();
		$user_id = $post_data['user_id'];
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $this->User_model->get_user_self_exclusion($user_id);
		$this->api_response();
	}

	public function get_pending_counts_post()
	{
		//get pending pan cards counts
		$pancard_result =  $this->User_model->get_pending_pancard_count();
		
		//get pending bank document couns
		$bank_result =  $this->User_model->get_pending_bank_document_count();

		$submodule_setting = $this->get_submodule_settings();

		$feedback_pending_count = 0;
		if(isset($submodule_setting['feedback']) && $submodule_setting['feedback'])
		{
			$this->load->model('promotions/Feedback_model');
			$feedback_pending_count= $this->Feedback_model->get_feedback_pending_count();
		}

		$this->api_response_arry['data']['pending_pan_card_count'] =$pancard_result['pending_pan_cards'] ? (int)$pancard_result['pending_pan_cards'] : 0;
		$this->api_response_arry['data']['pending_bank_document_count'] =$bank_result['pending_bank_documents'] ? (int)$bank_result['pending_bank_documents'] :0;
		$this->api_response_arry['data']['feedback_pending_count'] =(int)$feedback_pending_count;
		$this->api_response();	
		//get pending feedback requests
	}

	public function user_game_history_post()
	{	
		$this->form_validation->set_rules('user_id', 'User ID', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$_POST['is_user_history'] = "1";
		
		$data = $this->User_model->contest_list_by_user_id();
				
		foreach ($data['result'] as $key => $value) {
			$prize_data = explode("|#",$value['prize_data']);
			$prize_arr = array();
			foreach($prize_data as $prow){
				$prow = json_decode($prow,TRUE);
				if(!empty($prow)){
					$prize_arr = array_merge($prize_arr,$prow);
				}
			}
			$winnings = $this->User_model->get_contest_winning_amount($value['lineup_master_contest_ids'],$value['user_id']);
			$data['result'][$key]['winning_amount'] = $winnings['winning_amount'] ?$winnings['winning_amount'] : 0;
			$data['result'][$key]['winning_bonus'] = $winnings['winning_bonus'] ? $winnings['winning_bonus'] : 0;
			$data['result'][$key]['winning_coins'] = $winnings['winning_coins'] ? $winnings['winning_coins'] : 0;
			$data['result'][$key]['prize_data'] = $prize_arr;
		}
		
		$result = $data;
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;
		$this->api_response();
	}

	public function user_game_history_export_get()
	{
		$result = array();
		$_POST['is_user_history'] = "1";
		
		$data = $this->User_model->get_contest_list_by_user_id();
		foreach ($data['result'] as $key => $value) {
			$winnings = $this->User_model->get_contest_winning_amount($value['lineup_master_contest_ids'],$value['user_id']);
			$data['result'][$key]['merchandise_winning']='';
			$custom_data = explode("|#",$value['prize_data']);
			foreach($custom_data as $prizes=>$singlep)
			{
				$singlep = json_decode($singlep,true);
				if(!empty($singlep) && !empty($singlep[0]['prize_type']) && $singlep[0]['prize_type']=='3'){	
					$data['result'][$key]['merchandise_winning'] = $singlep[0]['name'].','.$data['result'][$key]['merchandise_winning'];	
				}
			}
			
			$custom_data = isset($winnings['custom_data'])?json_decode($winnings['custom_data'],TRUE):array();	
			$custom_data = isset($custom_data[0])?$custom_data[0]:array();	
				
			
			$data['result'][$key]['winning_amount'] = $winnings['winning_amount'] ?$winnings['winning_amount'] : 0;
			$data['result'][$key]['winning_bonus'] = $winnings['winning_bonus'] ? $winnings['winning_bonus'] : 0;
			$data['result'][$key]['winning_coins'] = $winnings['winning_coins'] ? $winnings['winning_coins'] : 0;
			unset($data['result'][$key]['prize_data']);
		}
		
		if(!empty($data['result'])){
			$header = array_keys($data['result'][0]);
			$result = array_merge(array($header),$data['result']);
			$this->load->helper('csv');
			array_to_csv($result,'User_game_history_list.csv');
		}
		else{
			$result = "no record found";
			$this->load->helper('download');
			$this->load->helper('csv');
			$data = array_to_csv($result);
			$name = 'User_game_history_list.csv';
			force_download($name, $result);
			}

	}

	public function get_user_referral_data_post(){
		$this->form_validation->set_rules('user_id', 'user_id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$user_ref_data = $this->User_model->get_referral_data();	
		$this->api_response_arry['data'] = $user_ref_data;
		$this->api_response();	
	}
	public function export_referral_data_get(){
		$_POST['from_date']=$_GET['from_date']; 
		$_POST['to_date']=$_GET['to_date']; 
		$user_ref_data = $this->User_model->export_referral_data();	
		//print_R($user_ref_data); die;
		//$user_ref_data = $this->User_model->get_user_referral_trend();		
		if(!empty($user_ref_data['referral_list']['result'])){
			$header = array_keys($user_ref_data['referral_list']['result'][0]);
			
			$user_ref_data = array_merge(array($header),$user_ref_data['referral_list']['result']);
			
			$this->load->helper('csv');
			array_to_csv($user_ref_data,'ref_user_data.csv');
		}
		else{
			$result = "no record found";
			$this->load->helper('download');
			$this->load->helper('csv');
			$data = array_to_csv($result);
			$name = 'ref_user_data.csv';
			force_download($name, $result);

		}
		/* $this->api_response_arry['data'] = $user_ref_data;
		$this->api_response();	 */
	}

	function add_note_post(){
		
		$this->form_validation->set_rules('user_unique_id', 'user_unique_id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$this->load->model('User_nosql_model');
		$response = $this->User_nosql_model->add_note();
		$post = $this->input->post();
		$user_unique_id=$post['user_unique_id'];
		
		if($post['is_flag'] == 1) {		
			$data_arr = array('is_flag'=>1);			
		
		}else{
			$data_arr = array('is_flag'=>0);

		}
		$this->User_model->update_user_detail($user_unique_id,$data_arr);
		$this->api_response_arry['data'] = $response;
		$this->api_response();	
		
		
	}
	function get_notes_post(){
		
		$this->form_validation->set_rules('user_unique_id', 'user_unique_id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$this->load->model('User_nosql_model');
		$response = $this->User_nosql_model->get_notes();
		$this->api_response_arry['data'] = $response;
		$this->api_response();	
		
		
	}
	public function add_user_balance_post()
	{   
		$this->load->model('userfinance/Userfinance_model');
		if(empty($this->input->post()))
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= $this->lang->line('enter_required_field');
			$this->api_response();
		}
		$this->form_validation->set_rules('user_unique_id', 'User Unique Id', 'trim|required');
		$this->form_validation->set_rules('amount', 'Amount', 'trim|required|max_length[7]|numeric');
		$this->form_validation->set_rules('transaction_type','Transaction Type' ,'trim|required');
		$this->form_validation->set_rules('transaction_amount_type','Transaction Amount Type' ,'trim|required');
        $this->form_validation->set_rules('user_balance_reason','Reason' ,'trim|max_length[255]');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$data_arr = array();
		
		$user_unique_id		= $this->input->post('user_unique_id');
		$amount				= $this->input->post("amount");
		$transaction_type	= $this->input->post("transaction_type");
		$transaction_ampunt_type	= $this->input->post("transaction_amount_type");
        $balance_reason          	= $this->input->post("user_balance_reason");

		$user_detail = $this->User_model->get_user_detail_by_id($user_unique_id);
		
		$order_object = array();
		$order_object['user_id']	= $user_detail['user_id'];
		$order_object['user_name']	= $user_detail['user_name'];
		$order_object['email']	= $user_detail['email'];
		$order_object['amount']		= $amount;

		$admin_details = array();
		$admin_details["first_name"] = $this->first_name;
		
		if(!empty($transaction_type) && $transaction_type == 'CREDIT')
		{
                    $order_object['cash_type']  = 0;
					$amt_type = 'Amount';
                    if($transaction_ampunt_type=='BONUS_CASH')
                    {
                        $order_object['cash_type'] = 1;
                        $order_object['transaction_amount_type'] = $transaction_ampunt_type;
						$amt_type = 'Bonus';
                    }else if($transaction_ampunt_type=='COINS')
                    {
                        $order_object['cash_type'] = 2;
                        $order_object['transaction_amount_type'] = $transaction_ampunt_type;
						$amt_type = 'Points';
                    }
                    elseif($transaction_ampunt_type == 'WINNING_CASH')
                    {
                        $order_object['cash_type'] = 4;
						$amt_type = 'Winning amount';
                    }

                    $order_object['source']		= 0;
                    $order_object['source_id']	= 0;
                    $order_object['plateform']	= 1;
                    $order_object['reason']	= $balance_reason;//ADMIN_USER_NOTI;
                    $admin_details['type'] = 'deposited';
                    $admin_details['amt_type'] = $amt_type;
					$order_object['custom_data'] = json_encode($admin_details,TRUE);

					$order_object['banner_image'] = 'reward-icon.png';    
					$order_object['custom_notification_subject'] = 'Wallet Credited 🤩';
					$order_object['custom_notification_text'] = 'You have a gift from admin. Check now 🎁🎁';
					$order_object['notification_destination'] = 7;
					$this->load->model('notification/Notification_model');
					$device_ids = $this->Notification_model->get_all_device_id(array($user_detail['user_id']));
					$android_device_ids = $ios_device_ids = array();
					foreach ($device_ids as $key => $single_id) {
						if (isset($single_id['device_type']) && $single_id['device_type'] == '1') {
							$android_device_ids[] = $single_id['device_id'];
						}

						if (isset($single_id['device_type']) && $single_id['device_type'] == '2') {
							$ios_device_ids[] = $single_id['device_id'];
						}
					}
					$order_object['device_ids'] = $android_device_ids;
					$order_object["ios_device_ids"] = $ios_device_ids;
                    $order_id =  $this->Userfinance_model->deposit_fund($order_object);
		}

		if(!empty($transaction_type) && $transaction_type == 'DEBIT')
		{
			$order_object['cash_type']  = 0;
			$amt_type = 'Amount';
			if($transaction_ampunt_type=='BONUS_CASH')
			{
				$order_object['cash_type'] = 1;
				$current_balance = $user_detail['bonus_balance'];
				$amt_type = 'Bonus';
			}else if($transaction_ampunt_type=='COINS')
            {
                $order_object['cash_type'] = 2;
                $current_balance = $user_detail['point_balance'];
				$amt_type = 'Points';
            }
			else if($transaction_ampunt_type=='WINNING_CASH')
			{
				$order_object['cash_type'] = 4;
				$order_object['withdraw_method'] = 4;
				$current_balance = $user_detail['winning_balance'];
				$amt_type = 'Winning amount';
			}
			else
			{
				$current_balance = $user_detail['balance'];
			}

			if($current_balance < $amount )
			{
				//show error
				$this->api_response_arry['error']  		=array();
				$this->api_response_arry['message'] =$this->lang->line("no_enough_balance");
				$this->api_response_arry['response_code'] =rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response();	
			}
			$admin_details['type'] = 'withdrawal';
            $admin_details['amt_type'] = $amt_type;
			
			$order_object['transaction_amount_type'] = $transaction_ampunt_type;
			$order_object['source']		= 0;
			$order_object['status']		= 1;
			$order_object['source_id']	= 0;
			$order_object['plateform']	= 1;
			$order_object['is_admin_request']	= 1;
			$order_object['reason']	= $balance_reason;//ADMIN_USER_NOTI;
			$order_object['custom_data'] = json_encode($admin_details,TRUE);
			
			$order_id =  $this->Userfinance_model->withdraw($order_object);
		}

		
		if( !empty($order_id))
		{


			$user_balance['balance'] = 0;
			$user_balance['bonus_balance'] = 0;
			$user_balance['winning_balance'] = 0;

			$user_balance 	= $this->Userfinance_model->get_user_balance($order_object['user_id']);
			$real_bal 		= $user_balance['real_amount'];
			$bonus_bal 		= $user_balance['bonus_amount'];
			$winning_bal 	= $user_balance['winning_amount'];
			$point_bal 		=  $user_balance['point_balance'];   // update point balance

            $balance_cache_key = 'user_balance_'.$user_detail['user_id'];
			$this->delete_cache_data($balance_cache_key);

			$this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
			$this->api_response_arry['message']  		= 'Transaction complete.';
			$this->api_response_arry['data'] = array( 
				'order_id'=>$order_id,
				'balance'=>$real_bal,
				'bonus_balance'=>$bonus_bal,
				'winning_balance'=>$winning_bal
                );
			
			$this->api_response();	
		}

		$this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		$this->api_response_arry['message']  		= $this->lang->line('no_change');
		$this->api_response();

	}

















































































	/** OLD METHODS NEED TO FILTER OUT AKR */

	

	

	function send_notification_post(){
		
		$this->form_validation->set_rules('user_id', 'user_id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		//function to send email or sms 
		$post_data = $this->post();
		$is_sms = isset($post_data['is_sms'])?$post_data['is_sms']:0;
		$user_id = $post_data['user_id'];
		$user_data = $this->User_model->get_user_basic_by_id($user_id);
		
        if ($is_sms == 1) {
            $sms_data = array();
            $sms_data['mobile'] = $user_data['phone_no'];
            $sms_data['phone_code'] = $user_data['phone_code'];
            $sms_data['message'] =$post_data['message'];

            $this->load->helper('queue_helper');
			add_data_in_queue($sms_data, 'sms');
			$response=true;
		
		} else {
			
			$today = format_date();
			
			$notify_data = array();
			$notify_data['notification_type'] = 14;
			$notify_data['notification_destination'] = 4;
			$notify_data["source_id"] = 1;

			$notify_data["user_id"] = $user_data['user_id'];
			$notify_data["user_name"] = $user_data['user_name'];
			$notify_data["to"] = $user_data['email'];
			$notify_data["added_date"] = $today;
			$notify_data["modified_date"] = $today;
			$notify_data["subject"] = $post_data['subject'];
			
			$content = $post_data['message'];

			$notify_data["content"] = json_encode($content);

			$this->load->model('User_nosql_model');
			$response = $this->User_nosql_model->send_notification($notify_data);
			

		}
		$this->api_response_arry['data'] = $response;
		$this->api_response();	
		
		
	}

	public function change_all_user_status_post()
	{
		$this->form_validation->set_rules('status', 'Status', 'trim|required');
		$status = $this->input->post("status");
		if($status == 0)
		{
			$this->form_validation->set_rules('reason', 'Reason', 'trim|required');
		}
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$user_unique_ids = $this->input->post('user_unique_id');

		if(empty($user_unique_ids))
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= $this->lang->line('invalid_parameter');
			$this->api_response();
		}
		$reason = $this->input->post("reason")?$this->input->post("reason"):"";

		foreach ($user_unique_ids as $user_unique_id) 
		{
			$data_arr[] = array(
							"status"			=> $status,
							"status_reason"		=> $reason,
							"user_unique_id"	=> $user_unique_id
						);
		}

		$result = $this->User_model->update_all_user_status($data_arr);
		
		if($result)
		{
			$this->api_response_arry['response_code'] 	= 200;
			$this->api_response_arry['message']  		= $this->lang->line('update_status_success');
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= $this->lang->line('no_change');
			$this->api_response();
		}
	}

	

	public function get_filter_data_post()
	{
		$country = $this->User_model->get_all_country();
		$value	= $this->User_model->get_max_min_user_balance();
		
		$result['country']		= $country;
		$result['min_value']	= round($value['min_value']);
		$result['max_value']	= round($value['max_value']);

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;
		$this->api_response();
	}

	public function export_users_get()
	{		
		//increase time for export user will take time 
		ini_set('max_execution_time','0');	
		ini_set('memory_limit', '-1');
		$_POST = $this->input->get();
		if(isset($_POST['from_date']) && isset($_POST['to_date']))
		{
			$this->_get_client_dates();
		}
		$users_data = $this->User_model->export_users();
		$result = array();
		foreach ($users_data as $key => $value) {

			switch ($value['DeviceType']) {
				
				case 1:
					$device_type = "Android";
					break;
				case 2:
					$device_type = "ios";
					break;
				case 3:
					$device_type = "Web";
					break;
				case 4:
					$device_type = "Mobile";
					break;
			}

			switch ($value['status']) {
				case 0:
					$status = "User Blocked";
					break;
				case 1:
					$status = "User Active";
					break;
				case 2:
					$status = "User Email Not Verified";
					break;
				case 3:
					$status = "User Deleted";
					break;
				case 4:
					$status = "User Activation Pending";
					break;
			}

			switch ($value['PanCardStatus']) {
				case 0:
					$pancard_status = "PAN Card Pending";
					break;
				case 1:
					$pancard_status = "PAN Card Verified";
					break;
				case 2:
					$pancard_status = "PAN Card Refuted";
					break;
			}

			switch ($value['BankStatus']) {
				case 0:
					$bank_status = "Bank Pending";
					break;
				case 1:
					$bank_status = "Bank Verified";
					break;
				case 2:
					$bank_status = "Bank Rejected";
					break;
			}

			switch ($value['AadharStatus']) {
				case 0:
					$aadhaar_status = "Aadhar Pending";
					break;
				case 1:
					$aadhaar_status = "AAdhar Verified";
					break;
				case 2:
					$aadhaar_status = "Aadhar Rejected";
					break;
			}
			
			$value['DeviceType'] 	= $device_type;
			$value['status']		= $status;
			$value['PanCardStatus']	= $pancard_status;
			$value['BankStatus']	= $bank_status;
			$value['AadharStatus']	= $aadhaar_status;
			
			// if(!empty($team_result))
			// {
			// 	$team_key		= array_search( $value['team_id'], array_column($team_result, 'team_id'));
			// 	$team_data = $team_result[$team_key];
			// 	$result[]	= array_merge($value,$team_result[$team_key]);
			// }
			// else
			/*{
				$result[] = $value;
			}*/

			$result[] = $value;
		}

		if(!empty($result)){
			$header = array_keys($result[0]);
			$result = array_merge(array($header),$result);
			$this->load->helper('csv');
			array_to_csv($result,'User_list_'.time().'.csv');
		}

	}
	
	/**
	*@method add_balance_to_all_users_post
	*uses function to manage balance for all users
	**/
	// public function add_balance_to_all_users_post()
	// {
    //         if(empty($this->input->post()))
    //         {
    //                 $this->api_response_arry['response_code'] 	= 500;
    //                 $this->api_response_arry['message']  		= $this->lang->line('enter_required_field');
    //                 $this->api_response();
    //         }

    //         $this->form_validation->set_rules('amount', 'Amount', 'trim|required|max_length[7]|numeric');
    //         $this->form_validation->set_rules('transaction_type','Transaction Type' ,'trim|required');
    //         $this->form_validation->set_rules('transaction_amount_type','Transaction Amount Type' ,'trim|required');
    //         $this->form_validation->set_rules('user_balance_reason','Reason' ,'trim|max_length[255]');

    //         if (!$this->form_validation->run()) 
    //         {
    //                 $this->send_validation_errors();
    //         }

    //         $result = $this->User_model->get_all_data("user_id,email",USER,array("status" => '1'));

    //         $amount						= $this->input->post("amount");
    //         $transaction_type			= $this->input->post("transaction_type");
    //         $transaction_amount_type	= $this->input->post("transaction_amount_type");
    //         $user_balance_reason	= $this->input->post("user_balance_reason");
    //         $where = '';
    //         $this->db->trans_start();

    //         if($transaction_amount_type=='BONUS_CASH')
    //         {
    //                 $amount_type = 'bonus_amount';
    //                 $balance_type = 'bonus_balance';
    //         }

    //         if($transaction_amount_type=='REAL_CASH')
    //         {
    //                 $amount_type = 'real_amount';
    //                 $balance_type = 'balance';
    //         }

    //         if($transaction_amount_type=='WINNING_CASH')
    //         {
    //                 $amount_type = 'winning_amount';
    //                 $balance_type = 'winning_balance';
    //         }

    //         if(!empty($transaction_type) && $transaction_type == 'CREDIT')
    //         {
    //                 $type	= 0;
    //                 $update_user_data = "{$balance_type} = {$balance_type} + {$amount}";
    //         }

    //         if(!empty($transaction_type) && $transaction_type == 'DEBIT')
    //         {
    //                 $type	= 1;
    //                 $update_user_data = "{$balance_type} = {$balance_type} - {$amount}";
    //                 $where = " AND {$balance_type} >= {$amount}";
    //         }

    //         $order_object_final = array();
    //         foreach ($result as $key => $value) 
    //         {
    //                 $order_object				= array();
    //                 $order_object['user_id']	= $value['user_id'];
    //                 $order_object['source']		= 0;
    //                 $order_object['source_id']	= 0;
    //                 $order_object['plateform']	= 1;
    //                 $order_object['status']		= 1;
    //                 $order_object[$amount_type]	= $amount;
    //                 $order_object['type']		= $type;
    //                 $order_object['reason']		= $user_balance_reason;
    //                 $order_object['date_added']	= format_date();
    //                 $order_object['order_unique_id'] = $this->_generate_order_key();

    //                 $order_object_final[] = $order_object;
    //         }

    //         $this->db->insert_batch(ORDER, $order_object_final); 


    //         //echo "UPDATE `vi_user` SET {$balance_type} = {$balance_type}+{$amount} WHERE `status` = 1";
    //         $this->db->query("UPDATE `vi_user` SET $update_user_data WHERE `status` = '1' $where ");
			

    //         $this->db->trans_complete();
		
    //         if ($this->db->trans_status() === FALSE)
    //         {
    //             $this->api_response_arry['response_code'] 	= HTTP_INTERNAL_SERVER_ERROR;
    //             $this->api_response_arry['message']  		= "Please do try again";
    //             $this->api_response();
    //         }

    //         $this->api_response_arry['response_code'] 	= 200;
    //         $this->api_response_arry['message']  		= $this->lang->line('balance_update_success');
    //         $this->api_response();	
	// }
        
        public function _generate_order_key() 
        {
            $this->load->helper('security');
	        $salt = do_hash(time() . mt_rand());
	        $new_key = substr($salt, 0, 20);
	        return $new_key;
        }

        private function _order_key_exists($key)
        {
			
			$this->db->select('order_unique_id');
			$this->db->where('order_unique_id', $key);
			$this->db->limit(1);
			$query = $this->db->get(ORDER);
			$num = $query->num_rows();
			if($num > 0){
				return true;
			}
			return false;

        }

	/**
	*@method add_balance_to_selected_users_post
	*uses function to manage balance for selected users
	**/
	public function add_balance_to_selected_users_post()
	{
		/*echo  $this->input->post("selected_user_unique_id");
		exit('hi');*/
		if(empty($this->input->post()))
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= $this->lang->line('enter_required_field');
			$this->api_response();
		}
		
		$this->form_validation->set_rules('selected_user_unique_id', 'user unique id', 'trim|required');
		$this->form_validation->set_rules('amount', 'Amount', 'trim|required|max_length[7]|numeric');
		$this->form_validation->set_rules('transaction_type','Transaction Type' ,'trim|required');
		$this->form_validation->set_rules('transaction_amount_type','Transaction Amount Type' ,'trim|required');
		$this->form_validation->set_rules('balance_reason','Reason' ,'trim|max_length[255]');

		if (!$this->form_validation->run()) 
		{

			$this->send_validation_errors();
		}

		$user_unique_ids	= explode(",", $this->input->post("selected_user_unique_id"));		

		$user_unique_ids	=  implode(',', array_map('add_quotes', $user_unique_ids));

		//echo $user_unique_ids;die;
		$amount						= $this->input->post("amount");
		$transaction_type			= $this->input->post("transaction_type");
		$transaction_amount_type	= $this->input->post("transaction_amount_type");
		$balance_reason          	= !empty($this->input->post("balance_reason")) ? $this->input->post("balance_reason") : '';
         
		$this->db->trans_start();
		$where = '';
 		$result = $this->User_model->get_all_data("user_id,email,status,user_name",USER,"user_unique_id IN(".$user_unique_ids.") AND status = '1'" );
 		
 			if($transaction_amount_type=='BONUS_CASH')
			{
				$amount_type = 'bonus_amount';
				$balance_type = 'bonus_balance';
			}

			if($transaction_amount_type=='REAL_CASH')
			{
				$amount_type = 'real_amount';
				$balance_type = 'balance';
			}
                        
                        if($transaction_amount_type=='WINNING_CASH')
			{
				$amount_type = 'winning_amount';
				$balance_type = 'winning_balance';
			}

			if(!empty($transaction_type) && $transaction_type == 'CREDIT')
			{
				$type	= 0;
				$update_user_data = "{$balance_type} = {$balance_type} + {$amount}";
			}

			if(!empty($transaction_type) && $transaction_type == 'DEBIT')
			{
				$type	= 1;
				$update_user_data = "{$balance_type} = {$balance_type} - {$amount}";
				$where = " AND {$balance_type} >= {$amount}";
			}

			$order_object_final = array();
			$temp_array         = array();
			// echo ($amount);
			// echo ($balance_reason);
			//print_r($result); die('result');
			foreach ($result as $key => $value) 
			{
				$order_object				= array();
				$order_object['user_id']	= $value['user_id'];
				$order_object['source']		= 0;
				$order_object['source_id']	= 0;
				$order_object['plateform']	= 1;
				$order_object['status']		= 1;
				$order_object[$amount_type]	= $amount;
				$order_object['type']		= $type;
				$order_object['reason']		= $balance_reason;
				$order_object['date_added']	= format_date();
                                $order_object['order_unique_id'] = $this->_generate_order_key();

				$order_object_final[] = $order_object;

				// $input = $value;
				$input['amount']                 = $amount;
				$input['reason']                 = $balance_reason;
				$input['transaction_amount_type'] = $transaction_amount_type;
				$tmp                             = array();
				$tmp['subject']					 = "Amount deposited.";//$this->lang->line("deposit_email_subject");
				$tmp["notification_type"]        = 6;
				$tmp["source_id"]                = 0;
				$tmp["notification_destination"] = 7; //  Web, Push, Email
				$tmp["user_id"]                  = $value['user_id'];
				$tmp["to"]                       = $value['email'];
				$tmp["email"]                    = $value['email'];
				$tmp["user_name"]                = $value['user_name'];
				$tmp["added_date"]               = format_date();
				$tmp["modified_date"]            = format_date();
				$tmp["content"]                  = json_encode($input);	
				$temp_array[]                    = $tmp;

			}
			
			if(!empty($order_object_final))
			{
				//echo '<pre>';print_r($order_object_final);die;
				$this->db->insert_batch(ORDER, $order_object_final); 
				$this->db->query("UPDATE `vi_user` SET $update_user_data WHERE `status` = '1' AND user_unique_id IN($user_unique_ids) $where  ");
			}
			

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
	       	$this->api_response_arry['response_code'] 	= HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']  		= "Please do try again";
			$this->api_response();
		}
        
		$this->rabbit_mq_push($temp_array, 'invite_email');

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['message']  		= $this->lang->line('balance_update_success');
		$this->api_response();
	}

	

	public function remove_cpf_post()
	{
		$this->form_validation->set_rules('user_unique_id', 'User Detail', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$this->load->model('User_model');
		$this->User_model->remove_cpf($this->input->post('user_unique_id'));
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['message']  		= 'CPF No removed successfully.';
		$this->api_response();
	}

	/**
	* get all country list
	*/
	public function get_country_list_post()
	{
		$county_list = $this->User_model->get_country_list();

		$this->api_response_arry['service_name']	= 'get_country_list';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= array('country_list'=>$county_list);
		$this->api_response();
	}

	/**
	* get all state list by country id
	*/
	public function get_state_list_post()
	{
		$this->form_validation->set_rules('master_country_id', 'country id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$state_list = $this->User_model->get_state_list_by_country_id($this->input->post('master_country_id'));
		if(!empty($state_list))
		{
			$this->api_response_arry['service_name']	= 'get_state_list';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']			= array('state_list'=>$state_list);
			$this->api_response();			
		}
		else
		{
			$this->api_response_arry['service_name']	= 'get_state_list';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error']	= "Invalid Country id";
			$this->api_response();	
		}
		
	}

	public function add_user_post()
	{
		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique['.$this->db->dbprefix(USER).'.email]',array(
			'required'      => 'You have not provided %s.',
			'is_unique'     => 'This %s already exists.'
	));
		$this->form_validation->set_rules('phone_no', 'Phone Number', 'trim|is_unique['.$this->db->dbprefix(USER).'.phone_no]',array(
			'required'      => 'You have not provided %s.',
			'is_unique'     => 'This %s already exists.'
	));
		
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$data = $this->input->post(); 
		
		 $email = explode('@', $data['email']);
		 $user_name = isset($email[0])?$email[0]:'';
		 $check_username =  $this->User_model->get_single_row(
			 "user_name",
			 USER,
			 array("user_name"=>$user_name)
		 );
		 if(!empty($check_username)) {
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = 'Email must contain unique value.';
            $this->api_response(); 
		 }
		$profile_image = 'avatar'.rand(1,10).'.png';
		$data["user_name"] = $user_name;
		$user_unique_id = $this->User_model->_generate_key();
		$data["user_unique_id"] = $user_unique_id;
		$data['added_date']     = date('Y-m-d H:i:s');
		$data['modified_date']  = date('Y-m-d H:i:s');
		$data['status']              = '1'; //Email not verified		
		$data['is_systemuser'] = '0'; //this user will be treated as real user.
		$data['image'] = $profile_image; //this user will be treated as real user.
		// print_r($data); die;
		// $this->db->insert(USER,$data);
		// $insert_id = $this->db->insert_id();
		$insert_id = $this->User_model->registration($data);
		if($insert_id)
		{	
			//function to send email or sms 
			$user_data = $this->User_model->get_user_basic_by_id($data["user_unique_id"]);
			
			$sms_data = array();
			$sms_data['mobile'] = $user_data['phone_no'];
			$sms_data['phone_code'] = $user_data['phone_code'];
			$sms_data['message'] ='You are registered as a user on ' . WEBSITE_URL;
			$this->load->helper('queue_helper');
			add_data_in_queue($sms_data, 'sms');
			
			$this->api_response_arry['service_name']	= 'add_user';
			$this->api_response_arry['message']			= 'User added successfully!';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']			= array('id'=>$insert_id);
			$this->api_response();	
		}
			$this->api_response_arry['service_name']	= 'add_user';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error']	= "User not added";
			$this->api_response();	

	}

	public function update_user_post()
	{
		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('dob', 'Date of birth', 'trim|required');
		$this->form_validation->set_rules('phone_no', 'Phone No', 'trim');
		// $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique['.$this->db->dbprefix(USER).'.email]');
		 //$this->form_validation->set_rules('user_name', 'UserName', 'trim|required|is_unique['.$this->db->dbprefix(USER).'.user_name]');
		$this->form_validation->set_rules('master_country_id', 'Country id', 'trim|integer');
		$this->form_validation->set_rules('master_state_id', 'State id', 'trim|integer');
		$this->form_validation->set_rules('city', 'City', 'trim');
		$this->form_validation->set_rules('zip_code', 'Zipcode', 'trim|integer');
		$this->form_validation->set_rules('pan_no', 'PAN No', 'trim');
		$this->form_validation->set_rules('bank_name', 'Bank Name', 'trim');
		$this->form_validation->set_rules('ifsc_code', 'IFSC code', 'trim');
		$this->form_validation->set_rules('micr_code', 'MICR code', 'trim');
		$this->form_validation->set_rules('ac_number', 'Account Number', 'trim|integer');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		if($this->input->post("phone_no") && $this->User_model->get_single_row("user_id",USER,array("phone_no"=>$this->input->post("phone_no"),"user_id !="=>$this->input->post("user_id"))))
		{
			$this->api_response_arry['service_name']	= 'update_user';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']	= "Phone number already exist!";
			$this->api_response_arry['global_error']	= $this->api_response_arry['message'];
			$this->api_response();	
		}

		if($this->input->post("user_name") && $this->User_model->get_single_row("user_name",USER,array("user_name"=>$this->input->post("user_name"),"user_id !="=>$this->input->post("user_id"))))
		{
			$this->api_response_arry['service_name']	= 'update_user';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']	= "user_name already exist!";
			$this->api_response_arry['global_error']	= $this->api_response_arry['message'];
			$this->api_response();	
		}

		$user_id = $this->input->post("user_id");
		$data						=array();
		$data["first_name"]			= $this->input->post("first_name");
		$data["last_name"]			= $this->input->post("last_name");
		$data["dob"]				= $this->input->post("dob");
		$data["gender"]				= $this->input->post("gender");
		$data["phone_no"]			= $this->input->post("phone_no");
		$data["user_name"]			= $this->input->post("user_name");
		$data["master_country_id"]	= $this->input->post("master_country_id");
		$data["master_state_id"]	= $this->input->post("master_state_id");
		$data["city"]				= $this->input->post("city");
		$data["zip_code"]			= $this->input->post("zip_code");
		$data["image"]				= $this->input->post("image");

		$data["address"]			= $this->input->post("address");
		$data["pan_no"]				= $this->input->post("pan_no");

		$bank_data = array();
		$bank_data["bank_name"] =$this->input->post("bank_name");
		$bank_data["ac_number"]=$this->input->post("ac_number");
		$bank_data["ifsc_code"]=$this->input->post("ifsc_code");
		$bank_data["micr_code"]=$this->input->post("micr_code");

		$user_unique_id		= $this->input->post("user_unique_id"); 
		// print_r($data); die;
		$this->db->where('user_unique_id',$user_unique_id)->update(USER,$data);
		$insert_id = $this->db->affected_rows();

		$bank_details = $this->User_model->get_single_row("user_bank_detail_id",USER_BANK_DETAIL,array('user_id'=>$user_id));
		
		// Update If bank detail exist 
		if($bank_details)
		{
			$this->db->where('user_id',$user_id)->update(USER_BANK_DETAIL,$bank_data);
		}
		else 
		{
			$bank_data["user_id"]		= $user_id;
			$bank_data["first_name"]	= $data["first_name"];
			$bank_data["last_name"]		= $data["last_name"];
			$bank_data["added_date"]	= format_date();
			$bank_data["modified_date"]	= format_date();
			$this->db->insert(USER_BANK_DETAIL,$bank_data);			
		}

		$this->api_response_arry['service_name']	= 'update_user';
		$this->api_response_arry['message']			= 'User Updated successfully!';
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= array('id'=>$insert_id);
		$this->api_response();
	}

	public function genrate_add_password_key()
	{

	}

	public function get_edit_user_details_post()
	{
		$this->form_validation->set_rules('user_unique_id', 'user_unique_id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$select = "
					user_id, 
					user_unique_id, 
					first_name, 
					last_name, 
					referral_code, 
					user_name, 
					dob, 
					gender, 
					email, 
					image, 
					address, 
					master_country_id, 
					master_state_id, 
					city, 
					balance, 
					phone_no,
					phone_verfied,
					added_date,
					pan_no,
					pan_verified,
					status,
					is_notification, 
					password
					";
		// $data = $this->User_model->get_single_row($select,USER,array("user_unique_id"=>$this->input->post("user_unique_id")));
		$user_profile = $this->User_model->get_single_row('', USER, array("user_unique_id"=>$this->input->post("user_unique_id")));
		$user_bank_detail = $this->User_model->get_single_row('first_name,last_name,bank_name,ac_number,ifsc_code,micr_code', USER_BANK_DETAIL, array('user_id'=>$user_profile["user_id"]));		
		if($user_profile)
		{
			$this->api_response_arry['service_name']	= 'get_edit_user_details';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
			$this->api_response_arry['data']			= array("user_profile"=>$user_profile,"bank_details" => $user_bank_detail);
			$this->api_response();	
		}
			$this->api_response_arry['service_name']	= 'get_edit_user_details';
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error']	= "Invalid User!";
			$this->api_response();	
	}

	public function do_upload_post()
	{
		$file_field_name = 'file';
	
		if(!isset($_FILES[$file_field_name]))
		{
			$this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['error']  	= array($file_field_name=>$this->lang->line('file_not_found'));
			$this->api_response_arry['message']  	= $this->lang->line('file_not_found');
			$this->api_response();
		}

		$dir       = PROFILE_IMAGE_UPLOAD_PATH;
		$temp_file = $_FILES[$file_field_name]['tmp_name'];
		$ext       = pathinfo($_FILES[$file_field_name]['name'], PATHINFO_EXTENSION);
		$vals      = @getimagesize($temp_file);
		$width     = $vals[0];
		$height    = $vals[1];

		if(!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > 4194304 )
		{
			$this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['error']  	= array($file_field_name=>$this->lang->line('invalid_image_size'));
			$this->api_response_arry['message']  	= $this->lang->line('invalid_image_size');
			$this->api_response();
		}

		$file_name = time().".".$ext;

		$allowed_ext = array('jpg', 'jpeg', 'png');

		if(!in_array( strtolower($ext) , $allowed_ext))
		{
			$error_msg	=  sprintf( $this->lang->line('invalid_image_ext'), implode(', ', $allowed_ext)) ;
			
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['error']			= array($file_field_name=>$error_msg);
			$this->api_response_arry['message']  		= $error_msg;
			$this->api_response();

		}


		$filePath     = $dir.$file_name;
		/*--Start amazon server upload code--*/
		if(strtolower(IMAGE_SERVER)=='remote')
		{
			try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                	$data =  array('image_path'=>PROFILE_IMAGE_PATH.$file_name, 'file_name'=>$file_name);
					$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
					$this->api_response_arry['data'] = $data;
					$this->api_response();
                }
            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response();
            }
		}
		else
		{
			$config[ 'allowed_types' ] = 'jpg|png|jpeg|gif';
			$config[ 'max_size' ]      = '4096';//204800
			$config[ 'upload_path' ]   = $dir;
			$config[ 'file_name' ]     = $file_name;

			//$config[ 'max_width' ]     = '1024';
			//$config[ 'max_height' ]    = '1000';

			$this->load->library('upload', $config);

			if (!$this->upload->do_upload($file_field_name))
			{
				$this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['global_error']  	= strip_tags($this->upload->display_errors());
				$this->api_response();
			}
			else
			{
				$uploaded_data = $this->upload->data();
				$thumb_path = PROFILE_IMAGE_PATH.$uploaded_data['file_name'];
				$this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']  	= array('image_path'=>$thumb_path, 'file_name'=>$uploaded_data['file_name']);
			}
		}


		
		// if($this->user_id)
		// {
		// 	$this->load->model('Auth_model');
		// 	$data  = $this->api_response_arry['data'];
		// 	$image = $data['image_path'];
		// 	$this->Auth_model->update(USER, array("image"=>$image), array('user_id'=>$this->user_id));
		// }
		$this->api_response();
	}


	public function verify_user_pancard_post()
	{
		$status = 0;
		$status = $this->input->post('pan_verified');
		
		$this->form_validation->set_rules('user_unique_id', 'User Detail', 'trim|required');
		$this->form_validation->set_rules('pan_verified', 'Pan Status', 'trim|required');
		if($status==2){
			$this->form_validation->set_rules('pan_rejected_reason', 'Reject Reason', 'trim|required|max_length[255]');
		}
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$this->load->model('User_model');
		$user_unique_id = $this->input->post('user_unique_id');
		$pan_verified = $this->input->post('pan_verified');
		$pan_rejected_reason = $this->input->post('pan_rejected_reason');
		$update_arr = array(
			'pan_verified' => $pan_verified,
			'pan_rejected_reason' => $pan_rejected_reason
		);	
		$message = $pan_verified == 1 ? $this->lang->line('pan_card_verified') : $this->lang->line('pan_card_reject');

		if($pan_verified ==1)
		{
			$update_arr['kyc_date'] = format_date();
		}

		$result = $this->User_model->update_user_detail($user_unique_id, $update_arr);

		$user_detail = $this->User_model->get_single_row("user_id,user_name,email,phone_no,first_name,last_name",USER,array("user_unique_id"=>$this->input->post("user_unique_id")));

		$this->delete_user_sessions($user_detail['user_id']);
		
		if($pan_verified==1)
		{	
			$check_affililate_history = $this->User_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$user_detail['user_id'],"status" => 1,"affiliate_type in (1,19,20,21)" => null,"user_id != " => "0"));
			if(empty($check_affililate_history))
			{
				//[NRS - PAN verification to user w/o referral]
				$this->pan_verification_bonus_for_non_referral_users($user_detail);
			}else{
				//[NRS - PAN verification to user with referral]
				$this->pan_verification_bonus_for_referral_users($user_detail);
			}

		}


		if($pan_verified != 1)
		{
			
			$tmp = array();
			$tmp["notification_type"] 			= 44; //Pan card reject
			$tmp["source_id"] 				 	= 0;
	        $tmp["notification_destination"] 	= 5; //  Web,Email
	        $tmp["user_id"] 					= $user_detail['user_id'];
	        $tmp["to"] 							= $user_detail['email'];
	        $tmp["subject"] 					= $this->lang->line('pan_card_reject_subject');
	        $tmp["user_name"] 					= $user_detail['user_name'];
	        $tmp["added_date"] 					= date("Y-m-d H:i:s");
	        $tmp["modified_date"] 				= date("Y-m-d H:i:s");
			$update_arr['int_version'] 			= INT_VERSION;
	        $update_arr['message'] 				= $this->lang->line('pan_card_reject_subject').'</br> Reason : '.$pan_rejected_reason;
	        $tmp["content"] 					= json_encode($update_arr);
		   /*  $this->add_notification($tmp); */
		   $this->load->model('User_nosql_model');
		   $this->User_nosql_model->send_notification($tmp);
		}

		

		$this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']  	= $update_arr;
		$this->api_response_arry['message']  	= $message;
		$this->api_response();						
		// $this->response(array(config_item('rest_status_field_name')=>TRUE, 'message'=>$message ,'data'=>$update_arr), rest_controller::HTTP_OK);
	}

	public function verify_user_bank_post()
	{
		$status = 0;
		$status = $this->input->post('bank_verified');

		$this->form_validation->set_rules('user_unique_id', 'User Detail', 'trim|required');
		$this->form_validation->set_rules('bank_verified', 'Bank Status', 'trim|required');
		if($status==2){
			$this->form_validation->set_rules('bank_rejected_reason', 'Reject Reason', 'trim|required|max_length[255]');
		}
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$this->load->model('User_model');
		$user_unique_id = $this->input->post('user_unique_id');
		$bank_verified = $this->input->post('bank_verified');
		$bank_rejected_reason = $this->input->post('bank_rejected_reason');
		$update_arr = array(
			'is_bank_verified' => $bank_verified,
			'bank_rejected_reason' => $bank_rejected_reason
		);	
		$message = $bank_verified == 1 ?'Bank verified successfully' : 'Bank rejected successfully';

		
		$user_detail = $this->User_model->get_single_row("user_id,user_name,email,phone_no,first_name,last_name",USER,array("user_unique_id"=>$this->input->post("user_unique_id")));

		if($bank_verified==1)
		{
			$user_bank_details = $this->User_model->get_single_row("ac_number, ifsc_code,upi_id,bank_name", USER_BANK_DETAIL, array("user_id" => $user_detail['user_id']));
			$is_duplicate = $this->User_model->check_duplicate_account($user_bank_details);

			if ($is_duplicate)
			{
                $this->api_response_arry['data']['auto_bank_attempted'] = 0;
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['error'] = array();
                $this->api_response_arry['global_error'] = $this->lang->line('duplicate_bank_details');
                $this->api_response();
			}
	    }

		if($bank_verified ==1)
		{
			$update_arr['kyc_date'] = format_date();
		}

	    $this->User_model->update_user_detail($user_unique_id, $update_arr);

		//delete user profile infor from cache
		$user_cache_key = "user_profile_".$user_detail['user_id'];
		$this->delete_cache_data($user_cache_key);
		$user_balance_cache_key = 'user_balance_' . $user_detail['user_id'];
		$this->delete_cache_data($user_balance_cache_key);
		$this->delete_user_sessions($user_detail['user_id']);

		if($bank_verified==1)
		{
			$check_affililate_history = $this->User_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$user_detail['user_id'],"status" => 1,"affiliate_type in (1,19,20,21)" => null,"user_id != " => "0"));
			if(empty($check_affililate_history))
			{
				//[ bank verification to user w/o referral]
				$this->bank_verification_bonus_for_non_referral_users($user_detail);
			}else{
				//[bank verification to user with referral]
				$this->bank_verification_bonus_for_referral_users($user_detail);
			}

			if($this->app_config['allow_crypto']['key_value']==1)
			{
				$message = "Crypto verified successfully";
				$approve_arr = array(
					'is_bank_verified' => $bank_verified,
					'crypto_name'	=>$user_bank_details['bank_name'],
					'wallet_address'	=>$user_bank_details['upi_id'],
				);

				$tmp = array();
				$tmp["notification_type"] 			= 595; //crypto details approved
				$tmp["source_id"] 				 	= 0;
				$tmp["notification_destination"] 	= 4; //  Email only
				$tmp["user_id"] 					= $user_detail['user_id'];
				$tmp["to"] 							= $user_detail['email'];
				$tmp["subject"] 					= $this->lang->line('crypto_approve_subject');
				$tmp["user_name"] 					= $user_detail['user_name'];
				$tmp["added_date"] 					= date("Y-m-d H:i:s");
				$tmp["modified_date"] 				= date("Y-m-d H:i:s");
				$approve_arr['message'] 			= 'You’ve whitelisted the below  '.$user_bank_details['bank_name'].' wallet address: '.$user_bank_details['upi_id'];
				$tmp["content"] 					= json_encode($approve_arr);
				//$this->add_notification($tmp);
				$this->load->model('User_nosql_model');
				$response = $this->User_nosql_model->send_notification($tmp);
			}

		}

		if($bank_verified != 1)
		{
			$message = $this->lang->line('bank_reject_subject');
			if($this->app_config['allow_crypto']['key_value']==1)
			{
			$message = $this->lang->line('crypto_reject_subject');
			}

			$tmp = array();
			$tmp["notification_type"] 			= 136; //bank details reject
			$tmp["source_id"] 				 	= 0;
	        $tmp["notification_destination"] 	= 5; //  Web,Email
	        $tmp["user_id"] 					= $user_detail['user_id'];
	        $tmp["to"] 							= $user_detail['email'];
	        $tmp["subject"] 					= $message;
	        $tmp["user_name"] 					= $user_detail['user_name'];
	        $tmp["added_date"] 					= date("Y-m-d H:i:s");
	        $tmp["modified_date"] 				= date("Y-m-d H:i:s");
	        $update_arr['message'] 				= $bank_rejected_reason;
	        $update_arr['allow_crypto'] 		= $this->app_config['allow_crypto']['key_value'];
	        $tmp["content"] 					= json_encode($update_arr);
			//$this->add_notification($tmp);
			$this->load->model('User_nosql_model');
			$response = $this->User_nosql_model->send_notification($tmp);
		}

		$this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']  	= $update_arr;
		$this->api_response_arry['message']  	= $message;
		$this->api_response();						
		// $this->response(array(config_item('rest_status_field_name')=>TRUE, 'message'=>$message ,'data'=>$update_arr), rest_controller::HTTP_OK);
	}

	public function get_lineup_detail_post()
	{
		$lineup_master_contest_id = $this->input->post('lineup_master_contest_id');
		$league_id = $this->input->post('league_id');
		$post_params = $this->input->post();
		$data_arr = $this->input->post();
		
		$this->load->model('contest/Contest_model');
		$contest_info = $this->Contest_model->get_contest_collection_details_by_lmc_id($lineup_master_contest_id,"CM.collection_master_id,CM.league_id,LM.lineup_master_id,sports_id,CM.season_scheduled_date,C.status,CM.is_lineup_processed,LMC.total_score,LMC.game_rank,LMC.won_amount,LM.user_name,LM.team_name,LM.team_data,IFNULL(LMC.prize_data,'[]') as prize_data,LMC.is_winner,LM.booster_id,LMC.booster_points,LM.is_pl_team");
		
		if(empty($contest_info)){
        	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "No details found.";
            $this->api_response_arry['service_name'] = "get_admin_lineup_detail";
            $this->api_response();
        }
    	$contest_info['is_tour_game'] = 0;
    	if(in_array($contest_info['sports_id'],$this->tour_game_sports)){
        	$contest_info['is_tour_game'] = 1;
        }
        $team_data = json_decode($contest_info['team_data'],TRUE);
        $collection_player_cache_key = "ad_roster_list_" . $contest_info['collection_master_id'];
        $players_list = $this->get_cache_data($collection_player_cache_key);
        if (!$players_list) {
            $this->load->model("roster/Roster_model");
            $post_data['collection_master_id'] = $contest_info['collection_master_id'];
            $post_data['league_id'] = isset($contest_info['league_id']) ? $contest_info['league_id'] : "";
            $post_data['sports_id'] = isset($contest_info['sports_id']) ? $contest_info['sports_id'] : "";
            $players_list = $this->Roster_model->get_all_rosters($post_data);
            //set collection players in cache for 2 days
            $this->set_cache_data($collection_player_cache_key, $players_list, REDIS_2_DAYS);
        }
        $player_list_array = array_column($players_list, NULL, 'player_team_id');
        if($contest_info['is_lineup_processed'] == "1"){
    		if(isset($team_data['captain_player_team_id'])){
                $team_data['c_id'] = $team_data['captain_player_team_id'];
            }
            if(isset($team_data['vice_captain_player_team_id'])){
                $team_data['vc_id'] = $team_data['vice_captain_player_team_id'];
            }
            $lineup_details = $this->Contest_model->get_lineup_with_score($lineup_master_contest_id, $contest_info);
            $team_data['pl'] = array_column($lineup_details,"score","player_team_id");
        }else if(in_array($contest_info['is_lineup_processed'],array("2","3"))){
            $completed_team = $this->Contest_model->get_single_row("collection_master_id,lineup_master_id,team_data",COMPLETED_TEAM, array("collection_master_id" => $contest_info['collection_master_id'], "lineup_master_id" => $contest_info['lineup_master_id']));
            $team_data = json_decode($completed_team['team_data'],TRUE);
        }else{
            $team_data['pl'] = array_fill_keys($team_data['pl'],"0");
        }

        $bench_player = isset($this->app_config['bench_player'])?$this->app_config['bench_player']['key_value']:0;
        $bench_players = array();
        $bench_pl_ids = array();
        if($bench_player == "1"){
            $pl_list = $this->Contest_model->get_team_bench_players($contest_info['lineup_master_id']);
            $bench_pl_ids = array_column($pl_list,"player_id");
            foreach($pl_list as $row){
                $player_id = $row['player_id'];
                if($row['status'] == "1" && $row['out_player_id'] != "0"){
                    $player_id = $row['out_player_id'];
                }
                $player_info = $player_list_array[$player_id];
                if(!empty($player_info)){
                    $tmp_arr = array();
                    $tmp_arr['priority'] = $row['priority'];
                    $tmp_arr['status'] = $row['status'];
                    $tmp_arr['reason'] = $row['reason'];
                    $tmp_arr['player_in_id'] = ($row['status'] == "1") ? $row['player_id'] : "0";
                    $tmp_arr['player_id'] = $player_info['player_id'];
                    $tmp_arr['player_team_id'] = $player_info['player_team_id'];
                    $tmp_arr['full_name'] = $player_info['full_name'];
                    $tmp_arr['jersey'] = $player_info['jersey'];
                    $tmp_arr['team_abbr'] = $player_info['team_abbreviation'];
                    $tmp_arr['season_game_uid'] = $player_info['season_game_uid'];
                    $tmp_arr['position'] = $player_info['position'];
                    $tmp_arr['player_uid'] = $player_info['player_uid'];
                    $tmp_arr['salary'] = $player_info['salary'];
                    $bench_players[] = $tmp_arr;
                }
            }
        }

        $final_player_list = array();
        $playing_announce = 0;
        if(!empty($team_data['pl'])){
            foreach ($team_data['pl'] as $player_team_id=>$score) {
                $player_info = $player_list_array[$player_team_id];
                if(!empty($player_info)) {
                	if(isset($player_info['playing_announce']) && $player_info['playing_announce'] == "1"){
                		$playing_announce = $player_info['playing_announce'];
                	}
                    $captain = 0;
                    if($player_team_id == $team_data['c_id']){
                        $captain = 1;
                    }else if($player_team_id == $team_data['vc_id']){
                        $captain = 2;
                    }
                    $sub_in = 0;
                    if(!empty($bench_pl_ids) && in_array($player_team_id,$bench_pl_ids)){
                        $sub_in = 1;
                    }
                    $lineup = array();
                    $lineup['player_id'] = $player_info['player_id'];
                    $lineup['player_team_id'] = $player_info['player_team_id'];
                    $lineup['full_name'] = $player_info['full_name'];
                    $lineup['jersey'] = $player_info['jersey'];
                    $lineup['team_abbr'] = $player_info['team_abbreviation'];
                    $lineup['season_game_uid'] = $player_info['season_game_uid'];
                    $lineup['position'] = $player_info['position'];
                    $lineup['player_uid'] = $player_info['player_uid'];
                    $lineup['salary'] = $player_info['salary'];
                    $lineup['captain'] = $captain;
                    $lineup['is_playing'] = $player_info['is_playing'];
                    $lineup['playing_announce'] = $player_info['playing_announce'];
                    $lineup['lmp'] = $player_info['lmp'];
                    $lineup['score'] = $score;
                    $lineup['sub_in'] = $sub_in;
                    $final_player_list[] = $lineup;
                }
            }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['team_detail_not_found'];
            $this->api_response();
        }

        $booster = array();
        if(isset($contest_info["booster_id"]) && $contest_info["booster_id"] != "0"){
            $this->load->model("booster/Booster_model");
            $booster = $this->Booster_model->get_match_booster_detail($contest_info["booster_id"],$contest_info['collection_master_id']);
            if(!empty($booster)){
                unset($booster['booster_id']);
                unset($booster['points']);
                $booster['score'] = $contest_info["booster_points"];
            }
        }
		
        $prize_data = json_decode($contest_info['prize_data'],TRUE);
		$lineup_result_temp = array('prize_data'=>$prize_data,'won_amount'=>$contest_info['won_amount'],'game_rank'=>$contest_info['game_rank'],'lineup_master_id'=>$contest_info['lineup_master_id'],'score'=>$contest_info['total_score'],"user_name"=>$contest_info['user_name'],"team_name"=>$contest_info['team_name'],"is_winner"=>$contest_info['is_winner'],"is_pl_team"=>$contest_info['is_pl_team'],"playing_announce"=>$playing_announce,"lineup"=>array(),"booster"=>$booster);
		$lineup_result_temp['lineup'] = $final_player_list;
		$lineup_result_temp["bench"] = $bench_players;

		$this->api_response_arry['data'] = $lineup_result_temp;
		$this->api_response();		
	}

	/**
	* For Pan verification
	**/
	private function set_user_referral($user_id)
	{
		$affililate_detail = $this->Auth_model->get_single_row('*', AFFILIATE_MASTER,array("affiliate_type"=>5));
		$affililate_history = $this->Auth_model->get_single_row('user_id,friend_email, user_affiliate_history_id,source_type', USER_AFFILIATE_HISTORY,array("friend_id"=>$user_id,"affiliate_type"=>1));

		$check_affililate_history = $this->Auth_model->get_single_row('user_id,friend_email, user_affiliate_history_id,source_type', USER_AFFILIATE_HISTORY,array("friend_id"=>$user_id,"affiliate_type"=>5));
		
		if(!empty($affililate_history) && empty($check_affililate_history))
		{
			$friend_detail = $this->Auth_model->get_single_row('user_name', USER,array("user_id"=>$user_id));
			
			if($affililate_detail["bonus_amount"] > 0)
			{	
				$data_post = array();
				$data_post["friend_id"] 		= $user_id;
				$data_post["friend_email"] 		= $affililate_history["friend_email"];
				$data_post["user_id"] 			= $affililate_history["user_id"];
				$data_post["source_type"]		= $affililate_history["source_type"];
				$data_post["affiliate_type"]	= 5;
				$data_post["user_bonus_cash"]	= $affililate_detail["bonus_amount"];
				$data_post["amount_type"]		= 1; // 1= Bonus 2 = Real Cash

				$post_target_url	= 'affiliate/add_affiliate_activity';
				$response		= $this->http_post_request($post_target_url,$data_post,3);
				$affililate_history_id  = $response["data"];

				$post_target_url	= 'finance/deposit';
				$deposit_data_friend = array(
									"user_id" 		=> $affililate_history["user_id"], 
									"amount" 		=> $affililate_detail["bonus_amount"], 
									"source" 		=> 14, // Pancard Verified
									"source_id" 	=> $affililate_history_id, 
									"plateform" 	=> 1, 
									"cash_type" 	=> 1, 
									"friend_name"	=> $friend_detail["user_name"],
									"profile_type"	=> 'Pancard',
									"link"			=> BASE_APP_PATH
								);
				$response		= $this->http_post_request($post_target_url,$deposit_data_friend,3);
			}
		}
	}

	//[NRS - PAN verification bonus/coins/real cash to user w/o referral]
	private function pan_verification_bonus_for_non_referral_users($user_detail)
	{
		if(empty($user_detail))
		{
			return TRUE;
		}

		$user_id = $user_detail['user_id'];
		//echo '<pre>';print_r($user_detail);die;	

		//check if affiliate master entry availalbe for pan verification bonus w/o referral
		$affililate_master_detail = $this->User_model->get_single_row('*', AFFILIATE_MASTER,array("affiliate_type"=>9));
		//if no details available then return true.
		if(empty($affililate_master_detail))
		{
			return TRUE;
		}

		//echo '<pre>';print_r($affililate_master_detail);die;	

		//check if signup bonus already given to this user.
		$user_affililate_history = $this->User_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$user_id,"affiliate_type"=>9));
		if(!empty($user_affililate_history))
		{
			return TRUE;
		}

		//echo '<pre>';print_r($user_affililate_history);die;

		$bouns_condition = array();
		$data_post = array();
		$data_post["friend_id"] 		= $user_id;
		$data_post["friend_mobile"] 	= (!empty($user_detail['phone_no'])) ? $user_detail['phone_no'] : NULL;
		$data_post["user_id"] 			= 0;//FOR WITHOUT REFERRAL CASE
		$data_post["status"]	 		= 1;
		$data_post["source_type"]		= 0;
		//$data_post["amount_type"]		= 0;
		$data_post["affiliate_type"]	= 9;
		$data_post["is_referral"]		= 0;
	
		//for w/o referral case use only friend bonus/real/coin balance
		$data_post["friend_bonus_cash"]	= $affililate_master_detail["user_bonus"];
		$data_post["friend_real_cash"]	= $affililate_master_detail["user_real"];
		$data_post["friend_coin"]	    = $affililate_master_detail["user_coin"];

		$data_post["bouns_condition"]	= json_encode($bouns_condition);

		$this->load->model('useraffiliate/Useraffiliate_model');	
		
		$affililate_history_id =$this->Useraffiliate_model->add_affiliate_activity($data_post);
		
		$this->load->model('userfinance/Userfinance_model');	

		if(INT_VERSION == 1) {
            $custom_data['p_to_id'] = "ID";
        }else{  
            $custom_data['p_to_id'] = "PAN";
        }
		//Entry on order table for bonus cash type
		if($affililate_master_detail["user_bonus"] > 0){
		
			$deposit_data_friend = array(
								"user_id" 	=> $user_id, 
								"amount"  	=> $affililate_master_detail["user_bonus"], 
								"source" 	=> 59, //pan verification - bonus cash
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 1,// for bonus cash 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"custom_data" =>json_encode($custom_data)
							);

			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);				
		}

		//Entry on order table for real cash type
		if($affililate_master_detail["user_real"] > 0)
		{
			$deposit_data_friend = array(
								"user_id" 	=> $user_id, 
								"amount"  	=> $affililate_master_detail["user_real"], 
								"source" 	=> 60, //pan verification - real cash
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 0,//for real cash 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"custom_data" =>json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
		}

		//Entry on order table for coins type
		if($affililate_master_detail["user_coin"] > 0)
		{
			$deposit_data_friend = array(
								"user_id" 	=> $user_id, 
								"amount"  	=> $affililate_master_detail["user_coin"], 
								"source" 	=> 61, //pan verification - coins(points)
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 2,//for coins(point balance) 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"custom_data" =>json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);				
		}

		return TRUE;
	}

	//[NRS - PAN verification bonus/coins/real cash to referral users]
	private function pan_verification_bonus_for_referral_users($friend_detail)
	{
		/*  
			$user_detail = for user who sent referral,
			$friend_detail = for user who used referral code 
		*/
		if(empty($friend_detail))
		{
			return TRUE;
		}	

		$friend_name = "Friend";
		if(!empty($friend_detail['first_name']) && !empty($friend_detail['last_name']))
		{
			$friend_name = $friend_detail['first_name'].' '.$friend_detail['last_name'];
		}	
		elseif(!empty($friend_detail['user_name']))
		{
			$friend_name = $friend_detail['user_name'];
		}

		$affililate_master_detail = $this->User_model->get_single_row('*', AFFILIATE_MASTER,array("affiliate_type"=>5));

		//echo '<pre>';print_r($affililate_detail);die("called...1");

		if(empty($affililate_master_detail))
		{
			return TRUE;
		}	
		
		//check user is referred user or not 
		$is_affiliate_user = $this->User_model->get_single_row('*', USER_AFFILIATE_HISTORY,array("friend_id"=>$friend_detail['user_id'],"status" =>1,"affiliate_type in (1,19,20,21)" => null));
		if(empty($is_affiliate_user))
		{
			return TRUE;
		}

		//check pan verification bonus already given to this user 
		$affililate_history = $this->User_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$friend_detail['user_id'],"affiliate_type"=>5));
		if(!empty($affililate_history))
		{
			return TRUE;
		}

		//echo '<pre>';print_r($affililate_history);die("called...2");


		$bouns_condition = array();
		$data_post = array();
		$data_post["friend_id"] 		= $friend_detail["user_id"];
		$data_post["friend_mobile"] 	= (!empty($friend_detail['phone_no'])) ? $friend_detail['phone_no'] : NULL;
		$data_post["user_id"] 			= $is_affiliate_user["user_id"];
		$data_post["status"]	 		= 1;
		$data_post["source_type"]		= $is_affiliate_user['source_type'];
		//$data_post["amount_type"]		= 0;
		$data_post["affiliate_type"]	= 5;
		$data_post["is_referral"]		= 1;
	
	   //for user who used referral code
		$data_post["friend_bonus_cash"]	= $affililate_master_detail["user_bonus"];
		$data_post["friend_real_cash"]	= $affililate_master_detail["user_real"];
		$data_post["friend_coin"]	    = $affililate_master_detail["user_coin"];

		//for user who sent referral(refer code)
		$data_post["user_bonus_cash"]	= $affililate_master_detail["bonus_amount"];
		$data_post["user_real_cash"]	= $affililate_master_detail["real_amount"];
		$data_post["user_coin"]	    	= $affililate_master_detail["coin_amount"];


		$data_post["bouns_condition"]	= json_encode($bouns_condition);

		$this->load->model('useraffiliate/Useraffiliate_model');	
		
		$data_post["created_date"]	    	= date("Y-m-d H:i:s");
		

		$affililate_history_id =$this->Useraffiliate_model->add_affiliate_activity($data_post);	
		
		$this->load->model('userfinance/Userfinance_model');	
	/*############ Generate transactions for user who sent referral(referral code) #########*/	

		if(INT_VERSION == 1) {
			$custom_data['p_to_id'] = "ID";
		}else{  
			$custom_data['p_to_id'] = "PAN";
		}
		//Entry on order table for bonus cash type
		if($affililate_master_detail["bonus_amount"] > 0){
			//$post_target_url	= 'finance/deposit';
			$deposit_data_friend = array(
								"user_id" 	=> $is_affiliate_user["user_id"], 
								"amount"  	=> $affililate_master_detail["bonus_amount"], 
								"source" 	=> 62,//pan verification with referral - bonus cash
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 1,// for bonus cash 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"friend_name"=>$friend_name,
								"custom_data" =>json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
			
		}

		//Entry on order table for real cash type
		if($affililate_master_detail["real_amount"] > 0)
		{
			
			//$post_target_url	= 'finance/deposit';
			$deposit_data_friend = array(
								"user_id" 	=> $is_affiliate_user["user_id"], 
								"amount"  	=> $affililate_master_detail["real_amount"], 
								"source" 	=> 63,//pan verification with referral - real cash
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 0,//for real cash 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"friend_name"=>$friend_name,
								"custom_data" =>json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
			
		}

		//Entry on order table for coins type
		if($affililate_master_detail["coin_amount"] > 0)
		{
			
			//$post_target_url	= 'finance/deposit';
			$deposit_data_friend = array(
								"user_id" 	=> $is_affiliate_user["user_id"], 
								"amount"  	=> $affililate_master_detail["coin_amount"], 
								"source" 	=> 64,//pan verification with referral - coins(points)
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 2,//for coins(point balance) 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"friend_name"=>$friend_name,
								"custom_data" =>json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
			
		}


	/*## Generate transactions for user who used referral code ###*/	

		//Entry on order table for bonus cash type
		if($affililate_master_detail["user_bonus"] > 0){
			$post_target_url	= 'finance/deposit';
			$deposit_data_friend = array(
								"user_id" 	=> $friend_detail["user_id"], 
								"amount"  	=> $affililate_master_detail["user_bonus"], 
								"source" 	=> 65,//pan verification with referral - bonus cash
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 1,// for bonus cash 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"custom_data" =>json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
			
		}

		//Entry on order table for real cash type
		if($affililate_master_detail["user_real"] > 0)
		{
			
			//$post_target_url	= 'finance/deposit';
			$deposit_data_friend = array(
								"user_id" 	=> $friend_detail["user_id"], 
								"amount"  	=> $affililate_master_detail["user_real"], 
								"source" 	=> 66,//pan verification with referral - real cash
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 0,//for real cash 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"custom_data" =>json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
			
		}

		//Entry on order table for coins type
		if($affililate_master_detail["user_coin"] > 0)
		{
			//$post_target_url	= 'finance/deposit';
			$deposit_data_friend = array(
								"user_id" 	=> $friend_detail["user_id"], 
								"amount"  	=> $affililate_master_detail["user_coin"], 
								"source" 	=> 67,//pan verification with referral - coins(points)
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 2,//for coins(point balance) 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"custom_data" =>json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
			
		}


		return TRUE;
	}

	//[NRS - BANK verification bonus/coins/real cash to user w/o referral]
	private function bank_verification_bonus_for_non_referral_users($user_detail)
	{
		if(empty($user_detail))
		{
			return TRUE;
		}

		$user_id = $user_detail['user_id'];
		//check if affiliate master entry availalbe for bank verification bonus w/o referral
		$affililate_master_detail = $this->User_model->get_single_row('*', AFFILIATE_MASTER,array("affiliate_type"=>16));
		//if no details available then return true.
		if(empty($affililate_master_detail))
		{
			return TRUE;
		}

		//echo '<pre>';print_r($affililate_master_detail);die;	

		//check if signup bonus already given to this user.
		$user_affililate_history = $this->User_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$user_id,"affiliate_type"=>16));
		if(!empty($user_affililate_history))
		{
			return TRUE;
		}

		//echo '<pre>';print_r($user_affililate_history);die;

		$bouns_condition = array();
		$data_post = array();
		$data_post["friend_id"] 		= $user_id;
		$data_post["friend_mobile"] 	= (!empty($user_detail['phone_no'])) ? $user_detail['phone_no'] : NULL;
		$data_post["user_id"] 			= 0;//FOR WITHOUT REFERRAL CASE
		$data_post["status"]	 		= 1;
		$data_post["source_type"]		= 0;
		//$data_post["amount_type"]		= 0;
		$data_post["affiliate_type"]	= 16;
		$data_post["is_referral"]		= 0;
	
		//for w/o referral case use only friend bonus/real/coin balance
		$data_post["friend_bonus_cash"]	= $affililate_master_detail["user_bonus"];
		$data_post["friend_real_cash"]	= $affililate_master_detail["user_real"];
		$data_post["friend_coin"]	    = $affililate_master_detail["user_coin"];

		$data_post["bouns_condition"]	= json_encode($bouns_condition);

		$this->load->model('useraffiliate/Useraffiliate_model');	
		
		$affililate_history_id =$this->Useraffiliate_model->add_affiliate_activity($data_post);
		
		$this->load->model('userfinance/Userfinance_model');	

		$custom_data = array();
		if($this->app_config['allow_crypto']['key_value']==1){
			$custom_data['b_to_c']='crypto wallet';
		}else{
			$custom_data['b_to_c']='bank';
		}

		//Entry on order table for bonus cash type
		if($affililate_master_detail["user_bonus"] > 0){
		
			$deposit_data_friend = array(
								"user_id" 	=> $user_id, 
								"amount"  	=> $affililate_master_detail["user_bonus"], 
								"source" 	=> 132, //bank verification - bonus cash
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 1,// for bonus cash 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"custom_data"=> json_encode($custom_data)
							);

			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);				
		}

		//Entry on order table for real cash type
		if($affililate_master_detail["user_real"] > 0)
		{
			$deposit_data_friend = array(
								"user_id" 	=> $user_id, 
								"amount"  	=> $affililate_master_detail["user_real"], 
								"source" 	=> 133, //bank verification - real cash
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 0,//for real cash 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"custom_data"=> json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
		}

		//Entry on order table for coins type
		if($affililate_master_detail["user_coin"] > 0)
		{
			$deposit_data_friend = array(
								"user_id" 	=> $user_id, 
								"amount"  	=> $affililate_master_detail["user_coin"], 
								"source" 	=> 134, //bank verification - coins(points)
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 2,//for coins(point balance) 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"custom_data"=> json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);				
		}

		return TRUE;
	}

	private function bank_verification_bonus_for_referral_users($friend_detail)
	{
		/*  
			$user_detail = for user who sent referral,
			$friend_detail = for user who used referral code 
		*/
		if(empty($friend_detail))
		{
			return TRUE;
		}	

		$friend_name = "Friend";
		if(!empty($friend_detail['first_name']) && !empty($friend_detail['last_name']))
		{
			$friend_name = $friend_detail['first_name'].' '.$friend_detail['last_name'];
		}	
		elseif(!empty($friend_detail['user_name']))
		{
			$friend_name = $friend_detail['user_name'];
		}

		$affililate_master_detail = $this->User_model->get_single_row('*', AFFILIATE_MASTER,array("affiliate_type"=>17));

		if(empty($affililate_master_detail))
		{
			return TRUE;
		}	
		
		//check user is referred user or not 
		$is_affiliate_user = $this->User_model->get_single_row('*', USER_AFFILIATE_HISTORY,array("friend_id"=>$friend_detail['user_id'],"status" =>1,"affiliate_type in (1,19,20,21)" => null));
		if(empty($is_affiliate_user))
		{
			return TRUE;
		}

		//check pan verification bonus already given to this user 
		$affililate_history = $this->User_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY,array("friend_id"=>$friend_detail['user_id'],"affiliate_type"=>17));
		if(!empty($affililate_history))
		{
			return TRUE;
		}

		$bouns_condition = array();
		$data_post = array();
		$data_post["friend_id"] 		= $friend_detail["user_id"];
		$data_post["friend_mobile"] 	= (!empty($friend_detail['phone_no'])) ? $friend_detail['phone_no'] : NULL;
		$data_post["user_id"] 			= $is_affiliate_user["user_id"];
		$data_post["status"]	 		= 1;
		$data_post["source_type"]		= $is_affiliate_user['source_type'];
		//$data_post["amount_type"]		= 0;
		$data_post["affiliate_type"]	= 17;
		$data_post["is_referral"]		= 1;
	
	   //for user who used referral code
		$data_post["friend_bonus_cash"]	= $affililate_master_detail["user_bonus"];
		$data_post["friend_real_cash"]	= $affililate_master_detail["user_real"];
		$data_post["friend_coin"]	    = $affililate_master_detail["user_coin"];

		//for user who sent referral(refer code)
		$data_post["user_bonus_cash"]	= $affililate_master_detail["bonus_amount"];
		$data_post["user_real_cash"]	= $affililate_master_detail["real_amount"];
		$data_post["user_coin"]	    	= $affililate_master_detail["coin_amount"];


		$data_post["bouns_condition"]	= json_encode($bouns_condition);

		$this->load->model('useraffiliate/Useraffiliate_model');	
		
		$affililate_history_id =$this->Useraffiliate_model->add_affiliate_activity($data_post);	
		
		$this->load->model('userfinance/Userfinance_model');	

		$custom_data = array();
        if($this->app_config['allow_crypto']['key_value']==1){
            $custom_data['b_to_c']='crypto wallet';
        }else{
            $custom_data['b_to_c']='bank';
        }

		//Entry on order table for bonus cash type //referred by
		if($affililate_master_detail["bonus_amount"] > 0){
			$deposit_data_friend = array(
								"user_id" 	=> $is_affiliate_user["user_id"], 
								"amount"  	=> $affililate_master_detail["bonus_amount"], 
								"source" 	=> 138,//bank verification referral - bonus cash
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 1,// for bonus cash 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"friend_name"=>$friend_name,
								"custom_data"=> json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
		}

		//Entry on order table for real cash type
		if($affililate_master_detail["real_amount"] > 0)
		{
			
			$deposit_data_friend = array(
								"user_id" 	=> $is_affiliate_user["user_id"], 
								"amount"  	=> $affililate_master_detail["real_amount"], 
								"source" 	=> 139,//bank verification referral - real cash
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 0,//for real cash 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"friend_name"=>$friend_name,
								"custom_data"=> json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
		}

		//Entry on order table for coins type
		if($affililate_master_detail["coin_amount"] > 0)
		{
			
			$deposit_data_friend = array(
								"user_id" 	=> $is_affiliate_user["user_id"], 
								"amount"  	=> $affililate_master_detail["coin_amount"], 
								"source" 	=> 140,//bank verification with referral - coins(points)
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 2,//for coins(point balance) 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"friend_name"=>$friend_name,
								"custom_data"=> json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
		}


	/*## Generate transactions for user who used referral code ###*/	

		//Entry on order table for bonus cash type
		if($affililate_master_detail["user_bonus"] > 0){
			$deposit_data_friend = array(
								"user_id" 	=> $friend_detail["user_id"], 
								"amount"  	=> $affililate_master_detail["user_bonus"], 
								"source" 	=> 141,//bank verification with referred - bonus cash
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 1,// for bonus cash 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"custom_data"=> json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
		}

		//Entry on order table for real cash type
		if($affililate_master_detail["user_real"] > 0)
		{
			$deposit_data_friend = array(
								"user_id" 	=> $friend_detail["user_id"], 
								"amount"  	=> $affililate_master_detail["user_real"], 
								"source" 	=> 142,//bank verification referred - real cash
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 0,//for real cash 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"custom_data"=> json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
		}

		//Entry on order table for coins type
		if($affililate_master_detail["user_coin"] > 0)
		{
			$deposit_data_friend = array(
								"user_id" 	=> $friend_detail["user_id"], 
								"amount"  	=> $affililate_master_detail["user_coin"], 
								"source" 	=> 143,//bank verification  referred - coins(points)
								"source_id" => $affililate_history_id, 
								"plateform" => 1, 
								"cash_type" => 2,//for coins(point balance) 
								"link" 		=> FRONT_APP_PATH.'my-wallet',
								"custom_data"=> json_encode($custom_data)
							);
			$this->Userfinance_model->deposit_any_fund($deposit_data_friend);
		}

		return TRUE;
	}

	public function get_user_private_contests_list_post()
	{
		$this->form_validation->set_rules('user_id', 'User ID', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data	= $this->input->post();
		$user_id = $post_data['user_id'];

		$contest_data = $this->User_model->get_user_private_contests($user_id, $post_data);

		$list = array();
		if(isset($contest_data['contests_list']))
		{
			$this->load->model('auth/Auth_nosql_model');
			foreach($contest_data['contests_list'] as $contest)
			{ 
					$contest['prize_distibution_detail'] = json_decode($contest['prize_distibution_detail']);
            		$contest['new_user_joined'] = $this->Auth_nosql_model->count('private_contest_new_users',array('contest_unique_id'=> $contest['contest_unique_id'],"contest_type"=>"1"));
					$list[] = $contest;
			}
		}
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']['result'] 	= $list;
		$this->api_response_arry['data']['total']  	= $contest_data['total_private_contest_created'];
		$this->api_response();
	}

	public function get_user_private_contests_data_post()
	{
		$this->form_validation->set_rules('user_id', 'User ID', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data	= $this->input->post();
		$user_id = $post_data['user_id'];

		$contest_data = $this->User_model->get_user_private_contests($user_id);

		$this->load->model('auth/Auth_nosql_model');
		foreach ($contest_data['contests_list'] as $key => $value)
		{
			$contest_data['contests_list'][$key]['host_earning'] = (($value['entry_fee']*$value['host_rake'])/100)*$value['total_user_joined'];
			$contest_data['contests_list'][$key]['admin_earning'] = (($value['entry_fee']*$value['site_rake'])/100)*$value['total_user_joined'];
			$contest_data['contests_list'][$key]['new_signup'] = $this->Auth_nosql_model->count('private_contest_new_users',array('contest_unique_id'=> $value['contest_unique_id'],"contest_type"=>"1"));
		}

		$admin_earning_arr = array_column($contest_data['contests_list'], "admin_earning");
        $admin_earning_sum = array_sum($admin_earning_arr);

        $host_earning_arr = array_column($contest_data['contests_list'], "host_earning");
        $host_earning_sum = array_sum($host_earning_arr);

        $new_signup_arr = array_column($contest_data['contests_list'], "new_signup");
        $new_signup_sum = array_sum($new_signup_arr);

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']['total_admin_earning'] = $admin_earning_sum;
		$this->api_response_arry['data']['total_new_user_signups'] = $new_signup_sum;
		$this->api_response_arry['data']['total_user_earning'] = $host_earning_sum;
		$this->api_response_arry['data']['total_private_contest_created'] = $contest_data['total_private_contest_created'];
		$this->api_response();
	}

	/**
	 * method to unblock otp blocked users
	 */
	public function update_otp_blocked_users_post(){ 
		$this->form_validation->set_rules('user_id', 'user_id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$_POST['otp_attempt_count']=0;
		$_POST['blocked_date']= null;
		$update = $this->User_model->update_otp_blocked_users();
		if($update){
		$this->api_response_arry['message']	= $this->lang->line("unblock_success");
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= array();
		$this->api_response();
		}
		$this->api_response_arry['error']  		=array();
		$this->api_response_arry['global_error'] =$this->lang->line("unblock_error");
		$this->api_response_arry['response_code'] =rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		$this->api_response();
	}

	/**
	 * Used to get self exclusion user list and default configuration
	 */
	public function self_exclusion_post() { 
		$post_data = $this->input->post();
		$current_page = isset($post_data['current_page']) ? $post_data['current_page'] : 1;
		$self_exclusion_data = array();
		if($current_page == 1) {
			$this->load->model('setting/Setting_model');
			$self_exclusion = array("allow_self_exclusion");  
			$self_exclusion_data = $this->Setting_model->get_app_config_data($self_exclusion);
			if (!empty($self_exclusion_data)) {				
				unset($self_exclusion_data['image']);
			}
		}
		
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= $this->User_model->get_self_exclusions();
		$this->api_response_arry['data']['self_exclusion']			= $self_exclusion_data;
		$this->api_response();

	}

	/**
	 * Used to upload self exclusion supporting document
	 */
	public function self_exclusion_document_upload_post()
	{
		$file_field_name = 'file';
	
		if(!isset($_FILES[$file_field_name])) {
			$this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['error']  	= array($file_field_name=>$this->lang->line('file_not_found'));
			$this->api_response_arry['message']  	= $this->lang->line('file_not_found');
			$this->api_response();
		}

		$dir       = SELF_EXCLUSION_DOCUMENT_DIR;
		$temp_file = $_FILES[$file_field_name]['tmp_name'];
		$ext       = pathinfo($_FILES[$file_field_name]['name'], PATHINFO_EXTENSION);
		$vals      = @getimagesize($temp_file);
		$width     = $vals[0];
		$height    = $vals[1];

		if(!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > 4194304 )
		{
			$this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['error']  	= array($file_field_name=>$this->lang->line('invalid_image_size'));
			$this->api_response_arry['message']  	= $this->lang->line('invalid_file_size');
			$this->api_response();
		}

		$file_name = time().".".$ext;

		$allowed_ext = array('jpg', 'jpeg', 'png', 'doc', 'docx', 'pdf');

		if(!in_array( strtolower($ext) , $allowed_ext))
		{
			$error_msg	=  sprintf( $this->lang->line('invalid_file_ext'), implode(', ', $allowed_ext)) ;
			
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['error']			= array($file_field_name=>$error_msg);
			$this->api_response_arry['message']  		= $error_msg;
			$this->api_response();

		}

		if( strtolower( IMAGE_SERVER ) == 'local') {
			$this->check_folder_exist($dir);
		}
		$filePath     = $dir.$file_name;
		/*--Start amazon server upload code--*/
		if(strtolower(IMAGE_SERVER)=='remote')
		{
			if(BUCKET_TYPE=='DO'){
				try{
					$configuration = ['key'=>BUCKET_ACCESS_KEY,'secret'=>BUCKET_SECRET_KEY,'region'=>BUCKET_REGION,'bucket'=>BUCKET ];
					$this->load->library('space');
					$space = new Space($configuration);
					$is_do_upload = $space->space_upload($filePath,$temp_file);

					if($is_do_upload){
						$data =  array('file_path'=>SELF_EXCLUSION_DOCUMENT_DIR.$file_name, 'file_name'=>$file_name);
						$this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
						$this->api_response_arry['data']            = $data;
						$this->api_response();
					}
				}catch(Exception $e){
			        //$result = 'Caught exception: '.  $e->getMessage(). "\n";
					$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
					$this->api_response_arry['global_error'] = $this->admin_lang['ad_try_again'];
					$this->api_response(); 
				}
			}
			else{
				$this->load->library('S3');

			//if upload on s3 is enabled
			//instantiate the class
				$s3 = new S3(array("access_key"=>BUCKET_ACCESS_KEY,"secret_key"=>BUCKET_SECRET_KEY,"region"=>BUCKET_REGION,"use_ssl"=>BUCKET_USE_SSL,"verify_peer"=>BUCKET_VERIFY_PEER));
				$s3_upload_data = $s3->putObjectFile($temp_file, BUCKET, $filePath, S3::ACL_PUBLIC_READ);

				if($s3_upload_data['result']=='error')
				{
					$this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
					$this->api_response_arry['global_error']  	= $s3_upload_data['data'];
					$this->api_response();
				}
				else
				{
					$return_array = array('image_path'=>SELF_EXCLUSION_DOCUMENT_DIR.$file_name, 'file_name'=>$file_name);
					$this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
					$this->api_response_arry['data']  	= $return_array;
				// print_r($return_array); die;
				}
				/*--End amazon server upload code--*/
			}
		}
		else
		{
			$config[ 'allowed_types' ] = 'jpg|png|jpeg|gif|doc|docx|pdf';
			$config[ 'max_size' ]      = '4096';//204800
			$config[ 'upload_path' ]   = $dir;
			$config[ 'file_name' ]     = $file_name;

			//$config[ 'max_width' ]     = '1024';
			//$config[ 'max_height' ]    = '1000';

			$this->load->library('upload', $config);

			if (!$this->upload->do_upload($file_field_name))
			{
				$this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['global_error']  	= strip_tags($this->upload->display_errors());
				$this->api_response();
			}
			else
			{
				$uploaded_data = $this->upload->data();
				$thumb_path = SELF_EXCLUSION_DOCUMENT_DIR.$uploaded_data['file_name'];
				$this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']  	= array('file_path'=>$thumb_path, 'file_name'=>$uploaded_data['file_name']);
			}
		}
		$this->api_response();
	}

	/**
	 * Used to set self exclusion default value
	 */
	public function set_self_exclusion_post(){
		$this->form_validation->set_rules('max_limit', 'max limit', 'trim|required|integer');
		$this->form_validation->set_rules('user_id', 'user id', 'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}  
			  
		
		$result = $this->User_model->set_self_exclusion();
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= array();
		$this->api_response_arry['message']		   = $this->lang->line('self_exclusion_success');
		$this->api_response();
	}
	public function update_pan_info_post()
    {
        $this->load->model("User_model");

        $vconfig = array(
			array(
			'field' => 'first_name',
			'label' => $this->lang->line('first_name'),
			'rules' => 'required|trim|min_length[2]|max_length[50]'
			),
			array(
			'field' => 'last_name',
			'label' => $this->lang->line('first_name'),
			'rules' => 'trim|min_length[2]|max_length[50]'
			),
			array(
			'field' => 'dob',
			'label' => $this->lang->line('dob'),
			'rules' => 'required|callback_eighteen_years_old'
			),
			array(
			'field' => 'pan_no',
			'label' => $this->lang->line('pan_no'),
			'rules' => 'trim|required'
			),
			array(
			'field' => 'pan_image',
			'label' => $this->lang->line('pan_image'),
			'rules' => 'trim|required'
			),
			array(
			'field' => 'user_unique_id',
			'label' => $this->lang->line('pan_image'),
			'rules' => 'trim|required'
			)
			);
        $this->form_validation->set_rules($vconfig);
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_values = array();
        $post_data = $this->post();

        $check_exist = $this->User_model->get_single_row('user_id', USER, array("pan_no" => trim($post_data['pan_no']),"pan_verified"=>"1","user_unique_id!="=>$post_data['user_unique_id']));
		
		if(!empty($check_exist)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = $this->lang->line('duplicate_pan_no'); 
            $this->api_response();
        }

        $user_data = $this->User_model->get_single_row('user_id,pan_verified,pan_no', USER, array("user_unique_id" => $post_data['user_unique_id']));
		//if pancard varified then first name ,last name and ,dob can not be update 
		//I am removing this check becouse admin can update user document even after varified documents.
        // if(!empty($user_data['pan_verified']) && $user_data['pan_verified'] == '1')
        // {
        //     $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        //     $this->api_response_arry['error'] = array();
        //     $this->api_response_arry['global_error'] = $this->lang->line('err_update_post_pan_verify'); 
        //     $this->api_response();   
        // }

        $current_date = format_date();
		$name = $this->_split_name($post_data['first_name']);
        $post_values['first_name'] = $name['first_name'];
        $post_values['last_name'] = $name['last_name'];
        $post_values['pan_no'] = $post_data['pan_no'];
        $post_values['pan_verified'] = 0; //I have kept status as it is in db , becouse if I change it to 0 then on next approval user will again get benifit of pan approval
        $post_values['pan_image'] = $post_data['pan_image'];
        $post_values['dob'] = date('Y-m-d', strtotime($post_data['dob']));
      
        $post_values['last_ip'] = $this->input->ip_address();
		$post_values['modified_date'] = $current_date;
		
        $this->db->update(USER, $post_values, array('user_unique_id' => $post_data['user_unique_id']));
		//delete user profile infor from cache
        $user_cache_key = "user_profile_" . $user_data['user_id'];
        $this->delete_cache_data($user_cache_key);
        $this->api_response_arry['message'] = $this->lang->line('pan_info_updated');
        $this->api_response();
	}

	private function _split_name($name) {
		$name = trim($name);
		$last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
		$first_name = trim( preg_replace('#'.preg_quote($last_name,'#').'#', '', $name ) );
		return array('first_name'=>$first_name, 'last_name'=>$last_name);
	}

	/**
     * update_bank_ac_detail to update bank detail
     * @param
     * @return json array
     */
    public function update_bank_ac_detail_post() {

        $this->form_validation->set_rules('first_name', $this->lang->line('first_name'), 'trim|required|max_length[100]|required');
        $this->form_validation->set_rules('last_name', $this->lang->line('last_name'), 'trim|max_length[100]');
        $this->form_validation->set_rules('bank_name', $this->lang->line('bank_name'), 'trim|max_length[100]|required');
        $this->form_validation->set_rules('ac_number', $this->lang->line('ac_number'), 'trim|numeric|max_length[50]|required');
        if(INT_VERSION != 1) {
            $this->form_validation->set_rules('ifsc_code', $this->lang->line('ifsc_code'), 'trim|alpha_numeric|max_length[100]|required');
        }
		
		$this->form_validation->set_rules('bank_document', $this->lang->line('bank_document'), 'trim|required');
		$this->form_validation->set_rules('user_unique_id', $this->lang->line('user_unique_id'), 'trim|required');

        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();
        $user_detail = $this->User_model->get_single_row('user_id,is_bank_verified', USER, array('user_unique_id' => $post_data['user_unique_id']));
		//commented billow code becouse admin can update document even after varification.
        // if (isset($user_detail['is_bank_verified']) && $user_detail['is_bank_verified'] == 1) {
        //     $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        //     $this->api_response_arry['message'] = $this->lang->line('bank_detail_change_error');
        //     $this->api_response();
        // }

        $user_bank_detail = $this->User_model->get_single_row('user_id,upi_id', USER_BANK_DETAIL, array('user_id' => $user_detail['user_id']));

        $bank_data = array();
        $today = format_date();
        $bank_data['first_name'] = $post_data['first_name'];
        $bank_data['last_name'] = isset($post_data['last_name']) ? $post_data['last_name'] : "";
        $bank_data['bank_name'] = $post_data['bank_name'];
        $bank_data['ac_number'] = $post_data['ac_number'];
        $bank_data['ifsc_code'] = isset($post_data['ifsc_code']) ? $post_data['ifsc_code'] : NULL;
		$bank_data['bank_document'] = $post_data['bank_document'];
		$bank_data['upi_id'] = isset($post_data['upi_id'])?$post_data['upi_id']:$user_bank_detail['upi_id'];
        $bank_data['modified_date'] = $today;

        $user_data = array();

        $message = $this->lang->line('bank_detail_added_success');
        if ($user_bank_detail) {
            $this->db->update(USER_BANK_DETAIL, $bank_data, array('user_id' => $user_detail['user_id']));
            $this->db->update(USER, array('is_bank_verified' => 0), array('user_id' => $user_detail['user_id']));
        
            $message = $this->lang->line('bank_detail_update_success');
        } else {
            $bank_data['added_date'] = $today;
            $bank_data['user_id'] = $user_detail['user_id'];
            $this->db->insert(USER_BANK_DETAIL, $bank_data);
        }

        //delete user profile infor from cache
        $user_cache_key = "user_profile_" . $user_detail['user_id'];
        $this->delete_cache_data($user_cache_key);

        $this->api_response_arry['message'] = $message;
        $this->api_response();
    }

    // public function delete_bank_details_post()
    // {
    //     $this->load->model("Profile_model");
    //     $this->Profile_model->delete_row(USER_BANK_DETAIL,array('user_id'=> $this->user_id));
    //     $this->Profile_model->update(USER,array('is_bank_verified'=> 0),array('user_id' => $this->user_id));
    //      //delete user profile infor from cache
    //     $user_cache_key = "user_profile_" . $this->user_id;
    //     $this->delete_cache_data($user_cache_key);
    //     $this->api_response_arry['message'] = $this->lang->line('bank_detail_deleted');
    //     $this->api_response();

    // }

	/**
	 * to remove image of PAN 
	 */
	public function remove_kyc_image_post()
    {
		if(empty($this->input->post())){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('er_delete_kyc_image');
            $this->api_response();
		}
		$post_data = $this->input->post();
		if(isset($post_data['panfile']) && $post_data['panfile']!=''){
			$image_name = $post_data['panfile'];
			$dir = ROOT_PATH.PAN_IMAGE_UPLOAD_PATH;
			$s3_dir = PAN_IMAGE_UPLOAD_PATH;
		}
		else if(isset($post_data['bank_document']) && $post_data['bank_document']!=''){
			$image_name = $post_data['bank_document'];
			$dir = ROOT_PATH.BANK_DOCUMENT_IMAGE_UPLOAD_PATH;
			$s3_dir = BANK_DOCUMENT_IMAGE_UPLOAD_PATH;
		}
		else{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('er_delete_kyc_image');
            $this->api_response();
		}
    	$dir_path    = $s3_dir.$image_name;
    	if( strtolower( IMAGE_SERVER ) == 'remote' )
    	{
    		try{
                $data_arr = array();
                $data_arr['file_path'] = $dir_path;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_deleted = $upload_lib->delete_file($data_arr);
                if($is_deleted){
                    return true;
                }else{
                	return false;
                }
            }catch(Exception $e){
                return false;
            }
    	}
		return true;
    }
    /**
     *  Used to upload pan card
     * @param
     * @return json array
     */
    public function upload_pan_post() {
        $file_field_name = 'panfile';
        if (!isset($_FILES[$file_field_name])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('file_not_found'));
            $this->api_response();
		}
        $dir = PAN_IMAGE_UPLOAD_PATH;
        $temp_file = $_FILES[$file_field_name]['tmp_name'];
        $ext = pathinfo($_FILES[$file_field_name]['name'], PATHINFO_EXTENSION);
               
        if (!in_array(strtolower($ext), array('jpg', 'jpeg', 'png', 'pdf'))) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_image_ext'));
            $this->api_response();
        }

        if (!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > '4194304') {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_image_size'));
            $this->api_response();
        }

        $file_name = time() . "." . $ext;
        /* --Start amazon server upload code-- */
        if (strtolower(IMAGE_SERVER) == 'remote') {

            $filePath = $dir . $file_name;
            try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $image_path = PAN_IMAGE_PATH . $file_name;
                    $return_array = array('image_path' => $image_path, 'file_name' => $file_name);
                    $this->api_response_arry['data'] = $return_array;
                }

            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response(); 
            }
        } else {
            $config['allowed_types'] = 'jpg|png|jpeg|pdf';
            $config['max_size'] = '4096'; //KB
            $config['upload_path'] = $dir;
            $config['file_name'] = $file_name;

            $this->load->library('upload', $config);
            if (!$this->upload->do_upload($file_field_name)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = strip_tags($this->upload->display_errors());
                $this->api_response();
            } else {
                $uploaded_data = $this->upload->data();
                $thumb_path = PAN_IMAGE_PATH . $uploaded_data['file_name'];
                $this->api_response_arry['data'] = array('image_path' => $thumb_path, 'file_name' => $uploaded_data['file_name']);
            }
        }
        if ($this->user_id && $this->input->post("is_save")) {
            $data = $this->api_response_arry['data'];
            $image = $data['image_path'];
            $this->load->model("User_model");
            $this->User_model->update(USER, array("pan_image" => $image, 'pan_verified' => 0), array('user_id' => $this->user_id));
        }

        $user_cache_key = "user_profile_" . $this->user_id;
        $this->delete_cache_data($user_cache_key);
        $this->api_response();
    }

    /**
     *  Used to upload bank documents
     * @param
     * @return json array
     */
    public function upload_bank_document_post() {
        $file_field_name = 'bank_document';
        if (!isset($_FILES[$file_field_name])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('file_not_found'));
            $this->api_response();
        }

        $dir = BANK_DOCUMENT_IMAGE_UPLOAD_PATH;
        $temp_file = $_FILES[$file_field_name]['tmp_name'];
        $ext = pathinfo($_FILES[$file_field_name]['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($ext), array('jpg', 'jpeg', 'png', 'pdf'))) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_image_ext'));
            $this->api_response();
        }

        if (!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > '4194304') {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_image_size'));
            $this->api_response();
        }

        $file_name = time() . "." . $ext;

        /* --Start amazon server upload code-- */
        if (strtolower(IMAGE_SERVER) == 'remote') {
            $filePath = $dir . $file_name;
            try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $image_path = BANK_DOCUMENT_PATH . $file_name;
                    $return_array = array('image_path' => $image_path, 'file_name' => $file_name);
                    $this->api_response_arry['data'] = $return_array;
                }

            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response(); 
            }
        } else {
            $config['allowed_types'] = 'jpg|png|jpeg|pdf';
            $config['max_size'] = '4096'; //KB
            $config['upload_path'] = $dir;
            $config['file_name'] = $file_name;

            $this->load->library('upload', $config);
            if (!$this->upload->do_upload($file_field_name)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = strip_tags($this->upload->display_errors());
                $this->api_response();
            } else {
                $uploaded_data = $this->upload->data();
                $thumb_path = BANK_DOCUMENT_PATH . $uploaded_data['file_name'];
                $this->api_response_arry['data'] = array('image_path' => $thumb_path, 'file_name' => $uploaded_data['file_name']);
            }
        }

        if ($this->user_id && $this->input->post("is_save")) {
            $data = $this->api_response_arry['data'];
            $image = $data['image_path'];
            $this->load->model("User_model");
            $this->User_model->update(USER_BANK_DETAIL, array("bank_document" => $image), array('user_id' => $this->user_id));
        }

        $this->api_response();
    }
	
	public function eighteen_years_old() {
        if($this->app_config['allow_age_limit']['key_value'] == 0){
            return TRUE;
        }
        $post_data = $this->post();
        if (!$post_data['dob']) {
            return TRUE;
        }
        // $then will first be a string-date
        $then = strtotime($post_data['dob']);
        //The age to be over, over +18
        $min = strtotime('+18 years', $then);

        if (time() < $min) {
            $this->form_validation->set_message('eighteen_years_old', $this->lang->line("eighteen_years_old"));
            return FALSE;
        }
        return TRUE;
    }

    // public function validate_for_unique_pan() {
    //     $post_data = $this->post();

    //     $user_data = $this->Profile_model->get_single_row('user_id,pan_no,pan_verified', USER, array("pan_no" => $post_data['pan_no'], "pan_verified" => 1));

    //     if (!$user_data || ($user_data["pan_no"] == $post_data['pan_no'] && $user_data["user_id"] == $this->user_id)) {
    //         return TRUE;
    //     }
    //     $this->form_validation->set_message('validate_for_unique_pan', $this->lang->line("pan_already_exists"));
    //     return FALSE;
    // }

	/**
	 * method to unblock user on location based
	 * @param user_unique_id
	 * @response boolean
	 */

	 public function update_user_location_status_post()
	 {
		$this->form_validation->set_rules('user_unique_id', 'user_unique_id', 'trim|required');
		$this->form_validation->set_rules('status', 'status', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$duration = $this->app_config['allow_bs']['custom_data']['loc_time']? $this->app_config['allow_bs']['custom_data']['loc_time']:360;
		$current_time = strtotime(format_date());
		$expiry_time = strtotime("+".$duration." minutes", $current_time);

		$update_data = array();
		$update_data['bs_status'] = $post_data['status']."_".$expiry_time;
		$this->load->model("auth/Auth_nosql_model");
		// print_r($post_data);exit;
		$this->Auth_nosql_model->update_all_nosql(ACTIVE_LOGIN,array("user_unique_id"=>$post_data['user_unique_id']),$update_data);
		$this->User_model->update(USER, $update_data, ['user_unique_id' => $post_data['user_unique_id']]);
		$updated_rows = $this->db->affected_rows();
		//14369
		if(!$updated_rows):
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error'] = "No change";
			$this->api_response();
        endif;
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['global_error'] = "User updated Successfully";
		$this->api_response();
	 }


	/**
     *  Used to upload pan card
     * @param
     * @return json array
     */
    public function upload_aadhar_document_post() {
        $file_field_name = 'userfile';
        if (!isset($_FILES[$file_field_name])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('file_not_found'));
            $this->api_response();
        }
        $dir = AADHAR_IMAGE_UPLOAD_PATH;
        $temp_file = $_FILES[$file_field_name]['tmp_name'];
        $ext = pathinfo($_FILES[$file_field_name]['name'], PATHINFO_EXTENSION);
               
        if (!in_array(strtolower($ext), array('jpg', 'jpeg', 'png', 'pdf'))) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_ext'));
            $this->api_response();
        }

        if (!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > '4194304') {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_image_size'));
            $this->api_response();
        }

        $file_name = time() . "." . $ext;
        /* --Start amazon server upload code-- */
        if (strtolower(IMAGE_SERVER) == 'remote') {

            $filePath = $dir . $file_name;
            try{
                $data_arr = array();
                $data_arr['file_path'] = $filePath;
                $data_arr['source_path'] = $temp_file;
                $this->load->library('Uploadfile');
                $upload_lib = new Uploadfile();
                $is_uploaded = $upload_lib->upload_file($data_arr);
                if($is_uploaded){
                    $image_path = AADHAR_IMAGE_PATH . $file_name;
                    $return_array = array('image_path' => $image_path, 'file_name' => $file_name);
                    $this->api_response_arry['data'] = $return_array;
                }

            }catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
                $this->api_response(); 
            }
        } else {
            $config['allowed_types'] = 'jpg|png|jpeg|pdf';
            $config['max_size'] = '4096'; //KB
            $config['upload_path'] = $dir;
            $config['file_name'] = $file_name;

            $this->load->library('upload', $config);
            if (!$this->upload->do_upload($file_field_name)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = strip_tags($this->upload->display_errors());
                $this->api_response();
            } else {
                $uploaded_data = $this->upload->data();
                $thumb_path = AADHAR_IMAGE_PATH . $uploaded_data['file_name'];
                $this->api_response_arry['data'] = array('image_path' => $thumb_path, 'file_name' => $uploaded_data['file_name']);
            }
        }

        $this->api_response();
    }

    /**
     * Aadhar status update by admin
     * @param user_unique_id,aadhar_verified,aadhar_rejected_reason
     * @return string message
     */
    public function verify_user_aadhar_post()
	{
		$this->form_validation->set_rules('user_unique_id', 'User Unique Id', 'trim|required');
		$this->form_validation->set_rules('aadhar_status', 'Addhar Status', 'trim|required');
		if($this->input->post('aadhar_status')==2){
			$this->form_validation->set_rules('aadhar_rejected_reason', 'Reject Reason', 'trim|required|max_length[255]');
		}
		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
		}
		
		$post_data = $this->input->post();
		$update_arr = array(
			'aadhar_status' => $post_data['aadhar_status'],
			'aadhar_rejected_reason' => $post_data['aadhar_rejected_reason']
		);
		if(!empty($post_data['master_state_id'])){
			$update_arr['master_state_id'] = $post_data['master_state_id'];
		}
		$this->load->model('User_model');
		$user_detail = $this->User_model->get_single_row("user_id,email,user_name",USER,array("user_unique_id"=>$post_data['user_unique_id']));
		$aadhaar_detail = $this->User_model->get_single_row("user_id,aadhar_number",USER_AADHAR,array("user_id"=>$user_detail['user_id']));
		if($post_data['aadhar_status'] == 1){
			$check_exist = $this->User_model->get_single_row("user_id",USER_AADHAR,array("aadhar_number" =>$aadhaar_detail['aadhar_number'],'status'=>1));
			if(!empty($check_exist)){
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message'] = 'Sorry, this Aadhaar number already approved. Please reject this verification request.'; 
				$this->api_response();
			}
		}
		$this->User_model->update(USER, $update_arr,array('user_unique_id'=>$post_data['user_unique_id'])) ;
		$this->User_model->update(USER_AADHAR, array('status'=>$post_data['aadhar_status']), array('user_id' => $user_detail['user_id']));

		if($post_data['aadhar_status'] != 1)
		{
			
			$tmp = array();
			$tmp["notification_type"] 			= 532; //Aadhaar card reject
			$tmp["source_id"] 				 	= 0;
	        $tmp["notification_destination"] 	= 5; //  Web,Email
	        $tmp["user_id"] 					= $user_detail['user_id'];
	        $tmp["to"] 							= $user_detail['email'];
	        $tmp["subject"] 					= 'Your Aadhaar card has been rejected';
	        $tmp["user_name"] 					= $user_detail['user_name'];
	        $tmp["added_date"] 					= date("Y-m-d H:i:s");
	        $tmp["modified_date"] 				= date("Y-m-d H:i:s");
	        $update_arr['message'] 				= 'Your Aadhaar card has been rejected'.'</br> Reason : '.$post_data['aadhar_rejected_reason'];
	        $tmp["content"] 					= json_encode($update_arr);
		    $this->load->model('User_nosql_model');
		    $this->User_nosql_model->send_notification($tmp);
		}

		//delete user profile infor from cache
		$user_cache_key = "user_profile_".$user_detail['user_id'];
		$this->delete_cache_data($user_cache_key);

		//remove aadhar key
		$this->delete_cache_data('user_aadhar_'.$user_detail['user_id']);

		$message = $post_data['aadhar_status'] == 1 ? 'Aadhaar Id verified successfully' : 'Aadhaar Id rejected';
		$this->api_response_arry['data']  		= $update_arr;
		$this->api_response_arry['message']  	= $message;
		$this->api_response();						
	
	}

	/**
	 * Update aadhar info
	 * @param name ,aadhar_no,image
	 */
	public function update_aadhar_info_post()
    {
        $form_config = array(
            array(
                'field' => 'name',
                'label' => "name",
                'rules' => 'required|trim|min_length[2]|max_length[250]'
            ),
            array(
                'field' => 'aadhar_number',
                'label' =>  'Aadhaar number',
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'front_image',
                'label' => 'front image',
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'back_image',
                'label' => 'back image',
                'rules' => 'trim|required'
            ),
            array(
			'field' => 'user_id',
			'label' => 'User Id',
			'rules' => 'trim|required'
			)
        );
        $this->form_validation->set_rules($form_config);
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $post_values = array();
        $post_data = $this->post();
        $user_id = $post_data['user_id'];
        $this->load->model("User_model");

        $check_exist = $this->User_model->get_single_row('user_id', USER_AADHAR, array("aadhar_number" => trim($post_data['aadhar_number']),"user_id"=>$user_id,'status'=>1));
        if(!empty($check_exist)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('duplicate_aadhar_no'); 
            $this->api_response();
        }

        $user_data = $this->User_model->get_single_row('user_id,aadhar_status', USER, array("user_id" => $user_id));
        //if pancard varified then first name ,last name and ,dob can not be update    
        if(!empty($user_data['aadhar_status']) && $user_data['aadhar_status'] == '1')
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('err_update_post_aadhar_verify'); 
            $this->api_response();   
        }

        $aadhar_detail = $this->User_model->get_single_row('user_id', USER_AADHAR, array('user_id' => $user_id));

        $current_date = format_date();
        $aadhar_data = array();
        $aadhar_data['name'] = $post_data['name'];
        $aadhar_data['aadhar_number'] = $post_data['aadhar_number'];
        $aadhar_data['front_image'] = $post_data['front_image'];
        $aadhar_data['back_image'] = $post_data['back_image'];
         $aadhar_data['status'] = 0;
        $aadhar_data['modified_date'] = $current_date;
        if($aadhar_detail) {
            $this->User_model->update(USER_AADHAR, $aadhar_data, array('user_id' => $user_id));
            $this->User_model->update(USER, array('aadhar_status' => 0), array('user_id' => $user_id));
        
            $message = 'Aadhaar detail updated successfully.';
        } else {
            $aadhar_data['user_id'] = $user_id;
            $aadhar_data['added_date'] = $current_date;
            $this->db->insert(USER_AADHAR, $aadhar_data);
            
            $message = 'Aadhaar detail added successfully.';
        }

        //delete user profile infor from cache
        $user_cache_key = "user_profile_" . $user_id;
        $this->delete_cache_data($user_cache_key);

        //remove aadhar key
		$this->delete_cache_data('user_aadhar_'.$user_id);

        $this->api_response_arry['message'] = $message;
        $this->api_response();
    }
	
	/**
	 * Used to set self exclusion value for user
	 */
	public function set_default_self_exclusion_post(){
		$this->form_validation->set_rules('user_id', 'user id', 'trim|required');
		if (!$this->form_validation->run())  {
			$this->send_validation_errors();
		}  
			  
		
		$result = $this->User_model->set_default_self_exclusion();
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= array();
		$this->api_response_arry['message']		   = $this->lang->line('self_exclusion_success');
		$this->api_response();
	}

	

	/**
	 * Used to get user promo code data
	 */
	public function get_user_promo_code_data_post() {
		$this->form_validation->set_rules('user_id', 'user id', 'trim|required');
		if (!$this->form_validation->run())  {
			$this->send_validation_errors();
		}  
		$post_data = $this->input->post();
		$user_id = $post_data['user_id'];
		$this->api_response_arry['data'] = $this->User_model->get_user_promo_code_data($user_id);
		$this->api_response();
	}

	function get_download_app_graph_post()
    {
      
        $post = $this->input->post();
       
        //get spin counts
       $result = $this->User_model->get_download_app_graph($post);

	   $final_data = array();
	   $final_data['graph_data'] = array('main_values' => array(), 'series' => array());
		if(!empty($result['result']))
		{
			$final_data['total_downloads'] = array_sum(array_column($result['result'],'data_value')) ;
			$graph_data = get_lineup_graph_data($post['from_date'],$post['to_date'],$result['result']);
			$final_data['graph_data'] =$graph_data; 
		}

	   $counts=$this->User_model->get_download_app_counts($post);
	   $final_data['coins_distributed'] = 0;
	   $final_data['new_users'] = 0;
	   if(!empty($counts))
	   {
			$final_data['counts']['coins_distributed'] = $counts['coins_distributed'];
			$final_data['counts']['new_users'] = $counts['new_users'];
	   }
	
		$this->api_response_arry['service_name']    = 'get_download_app_graph';
		$this->api_response_arry['data']  = $final_data;
		$this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
		$this->api_response();

    }

	function get_download_app_leaderboard_post()
    {
        $post = $this->input->post();
        
        $result =  $this->User_model->get_download_app_leaderboard($post);
      
        $this->api_response_arry['data']  = $result;
        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response();
    }


	/** METHODS ADDED WHILE OPTIMIZATION */
	public function user_tds_report_post()
	{
		$this->form_validation->set_rules('from_date', 'From Date', 'trim|required');
		$this->form_validation->set_rules('to_date', 'To Date', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$result = $this->User_model->get_user_tds_report();
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;	
		$this->api_response();
	}

	public function export_user_tds_report_get(){
		$_POST['from_date']=$_GET['from_date']; 
		$_POST['to_date']=$_GET['to_date']; 
		$_POST['user_id']=$_GET['user_id']; 

		
		$result = $this->User_model->get_user_tds_report($csv='true');
	
		
		if(!empty($result['result'])){
			$header = array_keys($result['result'][0]);
			
			$user_tds_data = array_merge(array($header),$result['result']);
			
			
			$this->load->helper('csv');
			array_to_csv($user_tds_data,'user_tds_data.csv');
		}
		else{
			$result = "no record found";
			$this->load->helper('download');
			$this->load->helper('csv');
			$data = array_to_csv($result);
			$name = 'ref_user_data.csv';
			force_download($name, $result);

		}
		/* $this->api_response_arry['data'] = $user_ref_data;
		$this->api_response();	 */
	}
}
/* End of file User.php */
/* Location: ./application/controllers/User.php */