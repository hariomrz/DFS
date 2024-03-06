<?php
class Useraffiliate_model extends MY_Model 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('notification/Notify_nosql_model');
	}
	
	 /**
     * Used to insert affiliate data
     * @param array $data_array
     * @return int affiliate id
     */
    function add_affiliate_activity($data_array) {
        $this->db->insert(USER_AFFILIATE_HISTORY, $data_array);
        return $this->db->insert_id();
    }    

}
