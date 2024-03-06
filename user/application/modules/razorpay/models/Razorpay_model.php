<?php 
class Razorpay_model extends MY_Model 
{
	public function __construct()
	{
		parent::__construct();

	}

public function check_txn_info($data)
	{
		$order_tx_info = $this->db->select("O.status AS o_status,T.transaction_status AS t_status,O.custom_data,O.order_id,O.order_unique_id,T.transaction_id,O.source,O.user_id,O.winning_amount,O.date_added,T.payment_gateway_id")
        ->from(ORDER.' O')
        ->join(TRANSACTION.' T','T.order_id = O.order_id','INNER')
        ->where('T.transaction_id',$data[0])
        ->get()->row_array();
		return $order_tx_info;
        //if order table & transaction table  status
	}

}