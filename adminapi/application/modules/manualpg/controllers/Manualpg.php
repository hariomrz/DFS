<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require_once APPPATH.'/libraries/REST_Controller.php';

class Manualpg extends MYREST_Controller {

    public $form_data = [];
    public $modes = ['wallet','crypto','bank'];
    public $names = ["QR Code / Wallets","Crypto Currency","Bank Transfer"];
    public $prefix = 'MPG_';
    
	public function __construct()
	{
		parent::__construct();
        $this->load->model('Manualpg_model');
        $this->form_data[$this->modes[0]] = array("name"=>$this->names[0],"type"=>"radio");
        $this->form_data[$this->modes[0]]['child']['upi_id']     = array("name"=>"Wallet Add/ ID","type"=>"text");
        $this->form_data[$this->modes[0]]['child']['qr_code']    = array("name"=>"QR Code","type"=>"file");
        $this->form_data[$this->modes[0]]['child']['user_info_txt']    = array("name"=>"User Information","type"=>"textarea");
        $this->form_data[$this->modes[0]]['child']['disclaimer']    = array("name"=>"Disclaimer","type"=>"textarea");

        $this->form_data[$this->modes[1]]   = array("name"=>$this->names[1],"type"=>"radio");
        $this->form_data[$this->modes[1]]['child']['upi_id']     = array("name"=>"Crypto Wallet","type"=>"text");
        $this->form_data[$this->modes[1]]['child']['qr_code']    = array("name"=>"QR Code","type"=>"file");
        $this->form_data[$this->modes[1]]['child']['user_info_txt']    = array("name"=>"User Information","type"=>"textarea");
        $this->form_data[$this->modes[1]]['child']['disclaimer']    = array("name"=>"Disclaimer","type"=>"textarea");

        $this->form_data[$this->modes[2]]       = array("name"=>$this->names[2],"type"=>"radio");
        $this->form_data[$this->modes[2]]['child']['bank']     = array("name"=>"Bank","type"=>"text");
        $this->form_data[$this->modes[2]]['child']['acc_no']     = array("name"=>"Acc No","type"=>"text");
        $this->form_data[$this->modes[2]]['child']['ifsc']    = array("name"=>"IFSC Code","type"=>"text");
        $this->form_data[$this->modes[2]]['child']['user_info_txt']    = array("name"=>"User Information","type"=>"textarea");
        $this->form_data[$this->modes[2]]['child']['disclaimer']    = array("name"=>"Disclaimer","type"=>"textarea");

        $options = [];
        $options[$this->modes[0]] = $this->names[0];
        $options[$this->modes[1]] = $this->names[1];
        $options[$this->modes[2]] = $this->names[2];
        $this->form_data['types']      = array("name"=>"Mode Type","type"=>"select","options"=>$options);

        $this->admin_lang = $this->lang->line('manualpg');
    }

    /**
     * to get active payment mode for this admin
     */
    public function get_type_list_post()
    {
        $type_list = $this->Manualpg_model->get_all_data('type_id,key,custom_data,status',DEPOSIT_TYPE);
        foreach($type_list as $key=>$val)
        {
            $type_list[$key]['custom_data'] = json_decode($val['custom_data'],true);
        }
        $this->api_response_arry['data']['data']      = $type_list;
        $this->api_response_arry['data']['form_data'] = $this->form_data;
        $this->api_response();
    }

    /**
     * API to add / updated payment mode for the admin.
     * @param key string
     * @param custom_data json
     */
    public function update_type_list_post()
    {
        $post_data = $this->input->post();
        if ($post_data) {
            $this->form_validation->set_rules('key', 'key', "trim|required|in_list[".implode(',',$this->modes)."]");
            if (!$this->form_validation->run()) 
            {
                $this->send_validation_errors();
            }
            if(empty($post_data['custom_data']))
            {
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']    = $this->admin_lang['invalid_type_detail'];
                $this->api_response();  // Final Output 
            }

            $valid_keys = array_keys($this->form_data);
            $key_exist = $this->Manualpg_model->get_single_row('key',DEPOSIT_TYPE,['key'=>$post_data['key']]);
            if(!empty($key_exist) && $key_exist['key']==$post_data['key'])
            {
                $post_data['custom_data'] = json_encode($post_data['custom_data']);
                $this->Manualpg_model->update_type($post_data);
                $this->api_response_arry['message']         = $this->admin_lang["success_update"];
            }elseif(in_array($post_data['key'],$valid_keys))
            {
                $post_data['custom_data'] = json_encode($post_data['custom_data']);
                $post_data['added_date'] = format_date();
                $data = $this->Manualpg_model->add_type($post_data);
                $this->api_response_arry['message']         = $this->admin_lang["success_add"];
            }else{
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']    = $this->admin_lang['invalid_key'];
            }
        } else {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']    = $this->admin_lang['no_data'];
        }   
        $this->api_response();  // Final Output 
    }

    public function get_manual_txn_post()
    {
        $post_data = $this->input->post();
        if ($post_data) {
            $this->form_validation->set_rules('from_date', 'From Date', 'trim|required');
            $this->form_validation->set_rules('to_date', 'To Date', 'trim|required');
            if (!$this->form_validation->run()) 
            {
                $this->send_validation_errors();
            }
            $result = $this->Manualpg_model->get_menual_txn();
            $this->api_response_arry['status']			= TRUE;
            $this->api_response_arry['message']			= '';
            $this->api_response_arry['data']			= $result;

        } else {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']    = $this->admin_lang['no_data'];
        }   
        $this->api_response();  // Final Output 
    }

    public function get_menual_txn_get()
    {
        $_POST = $this->input->get();
        if(!isset($post_data['csv']))
		{
			$post_data["csv"] = TRUE;
		}

		$result = $this->Manualpg_model->get_menual_txn();
		
					
			if(!empty($result['result'])){
				$result =$result['result'];
				$header = array_keys($result[0]);
				$camelCaseHeader = array_map("camelCaseString", $header);
				$result = array_merge(array($camelCaseHeader),$result);
				$this->load->helper('download');
                $this->load->helper('csv');
                $data = array_to_csv($result);
                $data = "Created on " . format_date('today', 'Y-m-d') . "\n\n"  . html_entity_decode($data);
                $name = 'Manual_deposit_amount.csv';
                force_download($name, $data);
			}
			else{
				$result = "no record found";
				$this->load->helper('download');
				$this->load->helper('csv');
				$data = array_to_csv($result);
				$name = 'Manual_deposit_amount.csv';
				force_download($name, $result);

			}
    }

    public function update_transaction_post()
    {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('ref_id', 'Reference ID', 'trim|required|callback_valid_ref_id');
        $this->form_validation->set_rules('status', 'Status', 'trim|in_list[1,2]');
        // if(isset($post_data['status']) && in_array($post_data['status'],[2,3]))
        // {
        //     $this->form_validation->set_rules('reason', 'Reason', 'trim|required');
        // }
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        } 
        // $key_exist = $this->Manualpg_model->get_single_row('status',DEPOSIT_TYPE,['ref_id'=>$post_data['key']]); 

        $update_data = [];
        $update_data['modified_date']   = format_date();
        $update_data['ref_id']          = $post_data['ref_id'];
        
        if(isset($post_data['amount']) && $post_data['amount']!=0)
        {
            $update_data['amount'] = $post_data['amount'];
        }

        if(isset($post_data['status']) && $post_data['status']!=0)
        {
            $update_data['status'] = $post_data['status'];
        }else{
            
            $status = $this->Manualpg_model->get_single_row('ref_id,status',DEPOSIT_TXN,['ref_id'=>$post_data['ref_id']]);
            if(!empty($status))
            {
                $update_data['status'] = $status['status'];
            }else{
                $update_data['status'] = 0;
            }
        }

        switch($update_data['status'])
        {
            case 1:
                //if success mark success

                $result = $this->mark_success($update_data);
                break;
            case 2:
            case 3:
                $update_data['reason'] =  $post_data['reason'];
                $result = $this->Manualpg_model->update_deposit_txn($update_data);
                $this->api_response_arry['message']         = $this->admin_lang["success_update"];
            case 0:
                $result = $this->Manualpg_model->update_deposit_txn($update_data);
                $this->api_response_arry['message']         = $this->admin_lang["success_update"];
            break;
            default:
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error']    = $this->admin_lang['invalid_status'];
            break;
        }

        $this->api_response(); 

    }

    public function valid_ref_id($ref_if)
    {
        $ref = $this->Manualpg_model->get_single_row('ref_id,status',DEPOSIT_TXN,['ref_id'=>$ref_if]);
        // echo $this->db->last_query();die;
        if(!empty($ref) && $ref['status']==1)
        {
            $this->form_validation->set_message('valid_ref_id', $this->admin_lang["success_will_not_change"]);
            return false;
        }
        if(!$ref)
        {
            $this->form_validation->set_message('valid_ref_id', $this->admin_lang["invalid_ref"]);
            return false;
        }
        return true;
    }

    public function mark_success($update_data)
    {
        if(isset($update_data['ref_id']))
        {
            $order_info = $this->Manualpg_model->get_single_row('order_id',ORDER,['remark'=>$this->prefix.$update_data['ref_id']]);
            if(!empty($order_info))
            {
                $this->api_response_arry['response_code'] 	= 500;
                $this->api_response_arry['message']  		= $this->admin_lang['order_already_updated'];
                $this->api_response(); 
            }

            $txn_info = $this->Manualpg_model->get_single_row('*',DEPOSIT_TXN,['ref_id'=>$update_data['ref_id']]);
            $user_info = $this->Manualpg_model->get_single_row('*',USER,['user_id'=>$txn_info['user_id']]);
        }
		if(empty($this->input->post()))
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['message']  		= $this->admin_lang['enter_required_field'];
			$this->api_response();
		}

        $admin_details = array();
		$admin_details["first_name"] = $this->first_name;

        $order_object = array();
		$order_object['user_id']	    = $user_info['user_id'];
		$order_object['amount']		    = $update_data['amount'] ? $update_data['amount'] : $txn_info['amount'];
        $order_object['cash_type']      = 0; // for real amount
        $order_object['source']		    = 7;
        $order_object['source_id']	    = 0;
        $order_object['plateform']	    = 0;
        $order_object['remark']   = $this->prefix.$update_data['ref_id']; // primary key of deposit_txn
        $order_object['reason']	        = $this->admin_lang["success_approve"];
        $order_object['custom_data']    = json_encode($admin_details,TRUE);
        
		$order_id = $this->Manualpg_model->create_order($order_object);
        $transaction = array(
            'order_id' => $order_id,
            'payment_gateway_id' =>  28,
            'email' => $user_info['email'],
            'phone' => $user_info['phone_no'],
            'description' =>  $this->admin_lang["success_approve"],
            'transaction_status' => 1,
            'withdraw_type' => 0,
            'bank_txn_id' => $txn_info['bank_ref'],
            'gate_way_name'=>'Manual',
            'pg_order_id'=>$this->prefix.$update_data['ref_id'],
            'txn_date' => format_date()
        );
        $txn_id = $this->create_transaction($transaction);
        $sql = "UPDATE " . $this->db->dbprefix(ORDER) . " AS O
                INNER JOIN " . $this->db->dbprefix(TRANSACTION) . " AS T ON T.order_id = O.order_id
                INNER JOIN " . $this->db->dbprefix(DEPOSIT_TXN) . " AS D ON D.ref_id = ".$update_data['ref_id']."
                SET D.status = T.transaction_status,O.source_id = $txn_id
                WHERE T.transaction_id = $txn_id";
        $this->db->query($sql);

            $tmp = array();
            $notify_data["amount"] = $order_object['amount'];
            $notify_data["reason"] =  $order_object['reason'];
            $tmp["notification_type"] = 6; // 6-Deposit
            $tmp["source_id"] = $txn_id;
            $tmp["notification_destination"] = 7; //  Web, Push, Email
            $tmp["user_id"] = $order_object["user_id"];
            $tmp["to"] = $user_info['email'];
            $tmp["user_name"] = $user_info['user_name'];
            $tmp["added_date"] = format_date();
            $tmp["modified_date"] = format_date();
            $tmp["content"] = json_encode($notify_data);
            $tmp["subject"] = $this->admin_lang['deposit_success_subject'];

            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($tmp);
            if(isset($user_info['campaign_code']) && $user_info['campaign_code']!='' && $this->app_config['new_affiliate']['key_value']==1)
            {
                // "visit_code"=>$post_data['visit_code'],
                $user_data = array(
                    "campaign_code"=>$user_info['campaign_code'],
                    "user_id"=>$user_info['user_id'],
                    "name"=>'deposit',
                    "ref_id"=>$order_id,
                    "entity_id"=>$txn_id,
                    "amount"=>$order_object['amount'],
                 );
                 
                 $this->load->helper('queue_helper');
                 add_data_in_queue($user_data, 'af_deposit_user');
            }
            
            $user_affiliate =  $this->Manualpg_model->get_single_row('*', USER_AFFILIATE_HISTORY, array("friend_id" => $order_object["user_id"], "status" => 1, 'affiliate_type in (1,19,20,21)' => null));
            if (!empty($user_affiliate)) {//referral user case
                if (!empty($user_info) && !empty($user_affiliate['user_id'])) {
                    $this->add_bonus($user_info, $user_affiliate['user_id'], 14, 5, $order_object['amount']);
                }
            }
            $affiliate_member =  $this->Manualpg_model->get_single_row('*', USER_AFFILIATE_HISTORY, array("friend_id" => $order_object["user_id"], "status" => 1, 'affiliate_type' => 6,'is_affiliate'=>1));
            if(!empty($affiliate_member['user_id']) && $affiliate_member['user_id']!=0 && !empty($user_info)){
                $affiliate_commission =  $this->Manualpg_model->get_single_row('commission_type,deposit_commission,user_name',USER,array('user_id'=>$affiliate_member['user_id'],'is_affiliate'=>1,'status'=>1));
                if(!empty($affiliate_commission)){
                    $friend_username =  $this->Manualpg_model->get_single_row('user_name',USER,array('user_id'=>$order_object["user_id"]));
                    $orderData["winning_amount"]=$order_object['amount']*$affiliate_commission['deposit_commission']*.01;
                    $deposit_data_friend = array(
                        "user_id" 	=> $affiliate_member['user_id'],
                        "amount"  	=> round($orderData["winning_amount"],2),
                        "source" 	=> 321,// for commission of affiliate against amount deposit
                        "source_id" => $order_id, 
                        "plateform" => 1, 
                        "cash_type" => $affiliate_commission['commission_type'],//either 0 as real or 4 for winning amount
                        "reason" => "Commission for user deposit through affiliate program.", 
                        "link" 	=> FRONT_APP_PATH.'my-wallet',
                    );
                    $custom_data = array(
                        'user_id'=>$deposit_data_friend["user_id"],
                        'user_name'=>$friend_username['user_name'],
                        'amount' => $deposit_data_friend['amount'],
                    );
                    $deposit_data_friend['custom_data'] = json_encode($custom_data);
                    $this->load->model('userfinance/Userfinance_model');
                    $order_id = $this->Userfinance_model->deposit_fund($deposit_data_friend);
                }
            }
		
		if( !empty($order_id))
		{
			$user_balance['balance'] = 0;
			$user_balance['bonus_balance'] = 0;
			$user_balance['winning_balance'] = 0;

			$user_balance 	= $this->Manualpg_model->get_user_balance($order_object['user_id']);
			$real_bal 		= $user_balance['real_amount'];
			$bonus_bal 		= $user_balance['bonus_amount'];
			$winning_bal 	= $user_balance['winning_amount'];
			$point_bal 		=  $user_balance['point_balance'];   // update point balance

            $balance_cache_key = 'user_balance_'.$user_info['user_id'];
			$this->delete_cache_data($balance_cache_key);

			$this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
			$this->api_response_arry['message']  		= 'Amount credited successfully to the user’s wallet';
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

    public function create_transaction($data) {
        $this->db->insert(TRANSACTION, $data);
        return $this->db->insert_id();
    }

    public function change_type_status_post()
    {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('key', 'Type key', 'trim|required');
        $this->form_validation->set_rules('status', 'Status', 'trim|required|in_list[1,2]');
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $is_success = $this->Manualpg_model->update(DEPOSIT_TYPE,['status'=>$post_data['status']],['key'=>$post_data['key']]);
        if($is_success)
        $this->api_response_arry['message']  		= $this->admin_lang["success_update"];
        else
        $this->api_response_arry['message']  		= "not updated";
		$this->api_response();
    }

}