<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Userfinance_model extends MY_Model 
{
	var $source_notification_map = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model('notification/Notify_nosql_model');
		//source => notification type
		$this->source_notification_map[59]=59;
		$this->source_notification_map[60]=60;
		$this->source_notification_map[61]=61;
		$this->source_notification_map[62]=62;
		$this->source_notification_map[63]=63;
		$this->source_notification_map[64]=64;
		$this->source_notification_map[65]=65;
		$this->source_notification_map[66]=66;
		$this->source_notification_map[67]=67;
		$this->source_notification_map[86]=86;
		$this->source_notification_map[87]=87;
		$this->source_notification_map[88]=88;
		$this->source_notification_map[89]=89;
		$this->source_notification_map[90]=90;
		$this->source_notification_map[91]=91;
		$this->source_notification_map[92]=92;
		$this->source_notification_map[93]=93;
		$this->source_notification_map[94]=94;
		$this->source_notification_map[132]=142;
		$this->source_notification_map[133]=143;
		$this->source_notification_map[134]=144;
		$this->source_notification_map[138]=145;
		$this->source_notification_map[139]=146;
		$this->source_notification_map[140]=147;
		$this->source_notification_map[141]=148;
		$this->source_notification_map[142]=149;
		$this->source_notification_map[143]=150;
		$this->source_notification_map[151]=151;
		
	}

	/**
	 * Function to add user balance
	 *	Params: $user_id,$real_balance,$bonus_balance
	 *	
	 */
	public function deposit_fund($input)
	{
		
      	$order_id = $this->create_order($input);
      	
		if($order_id)
		{	// Add notification
				
			$today 	= format_date();
			$tmp 	= array(); 
			$user_detail 		= $this->get_single_row('email, user_name,is_systemuser', USER, array("user_id"=>$input["user_id"]));
			if($user_detail['is_systemuser'] != 1){
				$input["user_name"]	= $user_detail['user_name'];
				$tmp["subject"] 	= $this->lang->line("deposit_email_subject");
				$tmp["source_id"] 	= $input["source_id"];				
				$tmp['notification_destination'] 	= 7;
				if(isset($input["notification_destination"]) && !empty($input["notification_destination"])) {
					$tmp["notification_destination"] = $input["notification_destination"];	
				}

				$tmp["user_id"] 		= $input['user_id'];
				$tmp["to"] 				= $user_detail['email'];
				$tmp["user_name"] 		= $user_detail['user_name'];
				$tmp["added_date"] 		= $today;
				$tmp["modified_date"] 	= $today;						
				$tmp['notification_type'] 			= 6;
				$tmp['subject'] 					= $this->lang->line('deposit_email_subject');

				$tmp['custom_notification_text'] = isset($input["custom_notification_text"]) ? $input["custom_notification_text"] : '';
				$tmp['custom_notification_subject'] = isset($input["custom_notification_subject"]) ? $input["custom_notification_subject"] : '';
				$tmp['device_ids'] = isset($input["device_ids"]) ? $input["device_ids"] : '';
				$tmp['ios_device_ids'] = isset($input["ios_device_ids"]) ? $input["ios_device_ids"] : '';
				
				unset($input["custom_notification_text"]);
				unset($input["custom_notification_subject"]);
				unset($input["device_ids"]);

				$tmp["content"] 		= json_encode($input);
        		$this->Notify_nosql_model->send_notification($tmp);
			}
		}
		else
		{
			return FALSE;
		}

		return $order_id;
	}	


	public function deposit_any_fund($input)
	{
		
      	$order_id = $this->create_order($input);
      	
		if($order_id)
		{	// Add notification
				
				$today 	= format_date();
				$tmp 	= array(); 
				$user_detail 		= $this->get_single_row('email, user_name', USER, array("user_id"=>$input["user_id"]));
				$input["user_name"]	= $user_detail['user_name'];

				$tmp["subject"] 	= $this->lang->line("deposit_email_subject");
				$tmp["source_id"] 	= $input["source_id"];
				$tmp["user_id"] 		= $input['user_id'];
				$tmp["to"] 				= $user_detail['email'];
				$tmp["user_name"] 		= $user_detail['user_name'];
				$tmp["added_date"] 		= $today;
				$tmp["modified_date"] 	= $today;
					
				
				if(isset($this->source_notification_map[$input["source"]]))	
				{
					$tmp['notification_type'] 			= $this->source_notification_map[$input["source"]];
				}
				else{
					$tmp['notification_type'] 			= 6;
				}
				$tmp['subject'] 					= $this->lang->line('deposit_email_subject');
				$tmp['notification_destination'] 	= 1;
				if(isset($input["notification_destination"]) && !empty($input["notification_destination"])) {
					$tmp["notification_destination"] = $input["notification_destination"];	
				}
				$tmp['custom_notification_text'] = isset($input["custom_notification_text"]) ? $input["custom_notification_text"] : '';
				$tmp['custom_notification_subject'] = isset($input["custom_notification_subject"]) ? $input["custom_notification_subject"] : '';
				$tmp['device_ids'] = isset($input["device_ids"]) ? $input["device_ids"] : '';
				$tmp['ios_device_ids'] = isset($input["ios_device_ids"]) ? $input["ios_device_ids"] : '';
				
				unset($input["custom_notification_text"],$input["custom_notification_subject"],$input["device_ids"],$input["ios_device_ids"]);

				$tmp["content"] 		= json_encode($input);	

        		$this->Notify_nosql_model->send_notification($tmp);
			
		}
		else
		{
			return FALSE;
		}

		return $order_id;
	}	
	/**
	 * Function to create order
	 *	Params: $user_id,$amount
	 *	
	 */
	public function create_order($input) {

		$today 		= format_date();
		$orderData 	= array();
		$orderData["user_id"] 			= $input['user_id'];
		$orderData["source"] 			= $input['source'];
		$orderData["source_id"] 		= $input['source_id'];
		$orderData["type"] 				= 0;
		$orderData["date_added"] 		= $today;
		$orderData["modified_date"] 	= $today;
		$orderData["status"] 			= 0;
		$orderData["real_amount"] 		= 0;
		$orderData["bonus_amount"] 		= 0;
		$orderData["winning_amount"]	= 0;
		$orderData["points"] 			= 0;
		$orderData["remark"]			= isset($input['remark'])?$input['remark']:'';
		$orderData["reason"] 			= isset($input['reason'])?$input['reason']:'';
		if(isset($input['custom_data']) && !empty($input['custom_data'])){
			$orderData["custom_data"] = $input['custom_data'];
		}

		$amount = $input['amount'];

		switch ($input['cash_type']) {
			case 0:
				$orderData["real_amount"] 	= $amount; // Real Money
				break;
			case 1:
				$orderData["bonus_amount"] 	= $amount; // Bonus Money 
				break;     
			case 2:
				$orderData["points"] 		= $amount; // Point Balance
				break;    
			case 4:
				$orderData["winning_amount"] = $amount; // Winning Balance 
				break;
			default:
				return FALSE;
				break;
		}

		/* Add source for which status will be set to one*/
		$status_one_srs = [0];
        $orderData["status"] = 1;	
		
		$this->db->trans_start();
		$orderData['order_unique_id'] = $this->_generate_order_key();
		$this->db->insert(ORDER, $orderData);
		$order_id = $this->db->insert_id();
		
		if (!$order_id) {            
			return FALSE;
		}

		$user_balance 	= $this->get_user_balance($orderData["user_id"]);
		$real_bal 		= $user_balance['real_amount'] + $orderData["real_amount"];
		$bonus_bal 		= $user_balance['bonus_amount'] + $orderData["bonus_amount"];
		$winning_bal 	= $user_balance['winning_amount'] + $orderData["winning_amount"];
		$point_bal 		=  $user_balance['point_balance'] + $orderData["points"];   // update point balance
		// Update User balance for order with completed status .
		if($orderData["status"] == 1)
		{
			$this->update_user_balance($orderData["user_id"], $real_bal, $bonus_bal,$winning_bal,$point_bal);
			
			if($orderData["bonus_amount"] > 0) {
				$this->load->helper('queue_helper');
				$bonus_data = array('oprator' => 'add', 'user_id' => $orderData["user_id"], 'total_bonus' => $orderData['bonus_amount'], 'bonus_date' => format_date("today", "Y-m-d"));
				add_data_in_queue($bonus_data, 'user_bonus');	
			}

			if($orderData["points"] > 0) {
				$this->load->helper('queue_helper');
				$coin_data = array('oprator' => 'add', 'user_id' => $orderData["user_id"], 'total_coins' => $orderData["points"], 'bonus_date' => format_date("today", "Y-m-d"));
				add_data_in_queue($coin_data, 'user_coins');	
			}
		}

		$this->db->trans_complete();
		
		return $order_id;
	}

	/**
	 * Function to withdraw user balance
	 *	Params: $user_id,$real_balance,$bonus_balance
	 *	
	 */
	public function withdraw($input){

		$orderData                  = array();
		$orderData["user_id"]       = $input['user_id'];
		$orderData["source"]        = $input['source'];
		$orderData["status"]        = $input['status'];
		$orderData["source_id"]     = $input['source_id'];
		$orderData["type"]          = 1;
		$orderData["date_added"]    = format_date();
		$orderData["modified_date"] = format_date();
		$orderData["plateform"]     = $input['plateform'];
		$orderData["reason"]     = $input['reason'];
		$orderData["custom_data"] 		= $input['custom_data']?$input['custom_data']:'NULL';
		
		if($input['cash_type']==0) {
			$orderData['real_amount'] = $input["amount"];
		}
		if($input['cash_type']==1) {
			$orderData['bonus_amount'] = $input["amount"];
		}
		if($input['cash_type']==2) {
			$orderData['points'] = $input["amount"];
		}
		if($input['cash_type']==4) {
			$orderData['winning_amount'] = $input["amount"];
		}

		$user_balance = $this->get_user_balance($orderData["user_id"]);
		$orderData['order_unique_id'] = $this->_generate_order_key();
		$this->db->insert(ORDER, $orderData);
		$order_id = $this->db->insert_id();
		if($order_id){
			$source = $input['source'];
			$real_bal = $user_balance['real_amount'];
			$bonus_bal = $user_balance['bonus_amount'];
			$winning_bal = $user_balance['winning_amount'];
			$point_bal = $user_balance['point_balance'];
			

			if($input['cash_type']==0) {
			$real_bal = $user_balance['real_amount'] - $input["amount"];
			}
			if($input['cash_type']==1) {
				$bonus_bal = $user_balance['bonus_amount'] - $input["amount"];

				if($input["amount"] > 0) {
					$this->load->helper('queue_helper');
					$bonus_data = array('oprator' => 'withdraw', 'user_id' => $orderData["user_id"], 'total_bonus' => $input['amount'], 'bonus_date' => format_date("today", "Y-m-d"));
					add_data_in_queue($bonus_data, 'user_bonus');	
				}
			}
			if($input['cash_type']==2) {
				$point_bal = $user_balance['point_balance'] - $input["amount"];

				if($input["amount"] > 0) {
					$this->load->helper('queue_helper');
					$coin_data = array('oprator' => 'withdraw', 'user_id' => $orderData["user_id"], 'total_coins' => $input['amount'], 'bonus_date' => format_date("today", "Y-m-d"));
					add_data_in_queue($coin_data, 'user_coins');
				}
			}
			if($input['cash_type']==4) {
				$winning_bal = $user_balance['winning_amount'] - $input["amount"];
			}

			//remove source 8 condition because of we have deduct user balance on withdraw request
			// update user balance!
			$this->update_user_balance($orderData["user_id"], $real_bal, $bonus_bal,$winning_bal,$point_bal);
			$withdraw_method = 'NA'; //for admin
			$input['payment_option'] = $withdraw_method;
			$tmp["notification_type"] = 184; // admin-fund-withdraw	 
			$subject ="Admin Withdraw";
			$input["reason"] = 'Admin Withdraw';
			if(isset($orderData['reason']) && $orderData['reason'] != ""){
				$input['reason'] = $orderData['reason'];
			}
			$tmp["source_id"] = $orderData['source_id'];
			$tmp["notification_destination"] = 7;   
			$tmp["user_id"] =  $orderData["user_id"];
			$tmp["to"] = $input['email'];
			$tmp["user_name"] = $input['user_name'];
			$tmp["added_date"] = format_date();
			$tmp["modified_date"] = format_date();
			$tmp["content"] = json_encode($input);
			$tmp["subject"] = $subject;
			$this->Notify_nosql_model->send_notification($tmp);
		}
		else
		{
			return FALSE;
		}

		return $order_id;

	}

	/**
	 * Function to get user balance
	 *	Params: $user_id,$real_balance,$bonus_balance
	 *	
	 */
	public function get_user_balance($user_id)
	{
		$result =	$this->db->select("user_id,balance as real_amount, bonus_balance as bonus_amount, winning_balance as winning_amount,point_balance")
			->where(array("user_id" => $user_id))
			->get(USER)
			->row_array();
		return array(
						"bonus_amount" => $result["bonus_amount"]?$result["bonus_amount"]:0,
						"real_amount" => $result["real_amount"]?$result["real_amount"]:0,
						"winning_amount" => $result["winning_amount"]?$result["winning_amount"]:0,
						"point_balance" => $result["point_balance"]?$result["point_balance"]:0
					);
	}

	/**
	 * Function to Update user balance
	 *	Params: $user_id,$real_balance,$bonus_balance
	 *	
	 */
	Public function update_user_balance($user_id,$real_bal,$bonus_bal,$winning_bal='',$point_bal="")
	{
		$data = array(
						"balance"		=> $real_bal,
						"bonus_balance"	=> $bonus_bal
					);
		if(is_numeric($winning_bal)){
			$data["winning_balance"] =  $winning_bal;
		}
		if($point_bal!==""){
			$data["point_balance"] = $point_bal;
		}

		$this->db->where('user_id', $user_id)
			->update(USER,$data);
		return $this->db->affected_rows();	
	}

	/**
	 * Function to genrate key
	 *	Params: 
	 *	
	 */
	public function _generate_order_key() 
	{
		$this->load->helper('security');
        $salt = do_hash(time() . mt_rand());
        $new_key = substr($salt, 0, 20);
        return $new_key;
	}
	/**
	 * Function to check exist genrate key
	 *	Params: 
	 *	
	 */
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



}