<?php 
class Mpesa_model extends MY_Model 
{
	public function __construct()
	{
		parent::__construct();

	}

	public function insert_withdraw_history($data)
	{
		$this->db->insert(WITHDRAW_HISTORY,$data);
		return true;
	}
	public function update_withdraw_history_by_orderid($update_data,$order_id)
	{
		$this->db->update(WITHDRAW_HISTORY, $update_data, array('order_id' => $order_id));
	}
    
    public function get_userdata_by_orderid($order_id)
    {
    	$sql = $this->db->select("U.user_id,U.email,U.user_name,U.phone_no,U.winning_balance,U.balance,U.system_email")
    			->from(ORDER." AS O")
    			->join(USER." AS U","O.user_id = U.user_id","INNER")
    			->where("O.order_id",$order_id)
    			->get();
    	$result = $sql->row_array();
    	return $result;
    }

	public function check_txn_info($data)
	{
		$order_tx_info = $this->db->select("O.status AS o_status,T.transaction_status AS t_status,O.custom_data,O.order_id,T.transaction_id,O.source,O.user_id,O.winning_amount,O.date_added,T.payment_gateway_id")
        ->from(ORDER.' O')
        ->join(TRANSACTION.' T','T.order_id = O.order_id','INNER')
        ->where('T.transaction_id',$data['tx'])
        ->get()->row_array();
		return $order_tx_info;
        //if order table & transaction table  status
	}
}