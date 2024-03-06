<?php 
class Crypto_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /** 
     * Used to get transaction details  
     * @param $tran_id
     * @param $client_tran_id
     * @return array
     **/
    function get_transaction_info($tran_id,$client_tran_id,$column='*'){
        $this->db->select($column);
        $this->db->where(['bank_txn_id'=>$tran_id,'pg_order_id'=>$client_tran_id]);
        $row = $this->db->get(TRANSACTION)->row_array();
        return $row;
    }
}
?>