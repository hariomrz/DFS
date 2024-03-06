<?php 
class Juspay_model extends MY_Model 
{
	public function __construct()
	{
		parent::__construct();

	}

	public function check_txn_info($txnid)
	{
		$order_tx_info = $this->db->select("O.status AS o_status,T.transaction_status AS t_status,O.custom_data,O.order_id,T.transaction_id,O.source,O.user_id,O.winning_amount,O.date_added,T.payment_gateway_id")
        ->from(ORDER.' O')
        ->join(TRANSACTION.' T','T.order_id = O.order_id','INNER')
        ->where('T.transaction_id',$txnid)
        ->get()->row_array();
		return $order_tx_info;
        //if order table & transaction table  status
	}

	public function get_user_details($user_id)
	{
		$sql = $this->db->select("first_name, email, phone_no")
						->from(USER)
						->where("user_id", $user_id)
						->get();
		return $sql->row_array();
	}

}