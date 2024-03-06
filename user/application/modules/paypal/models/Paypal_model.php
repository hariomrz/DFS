<?php

//require_once "package/Api_credential_model.php";
class Paypal_model extends MY_Model 
{
	public function __construct()
	{
		parent::__construct();
	}
	
	
	public function get_payment_config()
	{
		$sql = $this->db->select("*")
						->from(PAYMENT_CONFIG)
						->get();
		$rs =  $sql->result_array();
		return $rs;
	}
}

/* End of file Finance_model.php */
/* Location: ./application/models/Finance_model.php */