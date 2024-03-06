<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manualpg_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
        $this->load->database();
    }

    public function update_txn_detail($txn_data)
    {
        $this->db->insert(DEPOSIT_TXN,$txn_data);
        $type_id = $this->db->insert_id();
        return $type_id;
    }

    public function get_last_txns($user_id,$limit)
    {
        $txns = $this->db->select('*')
        ->from(DEPOSIT_TXN)
        ->where('user_id',$user_id)
        ->limit($limit)
        ->order_by('ref_id',"DESC")
        ->get()
        ->result_array();
        return $txns;
    }


}