<?php

//require_once "package/Api_credential_model.php";

class Cashfree_model extends MY_Model 
{
	public function __construct()
	{
		parent::__construct();
	}
/**
 * method to get cashfree wallet and bank list
 */
	public function get_wallet_bank_list(){
		$result = array();
		//wallet list
		$result['wallet_list']  = $this->db->select('payment_option,type_code,payment_code,payment_type')
		->from(CASHFREE_WALLET_BANK)
		->where('status',1)
		->where('payment_type',0)
		->get()->result_array();
		//bank list
		$result['bank_list'] = $this->db->select('payment_option,type_code,payment_code,payment_type')
		->from(CASHFREE_WALLET_BANK)
		->where('status',1)
		->where('payment_type',1)
		->get()->result_array();
		return $result;
	}
}
