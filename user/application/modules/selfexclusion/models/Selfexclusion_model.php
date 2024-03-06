<?php
class Selfexclusion_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
	 * Used to set self exclusion for user
	 */
	public function set_self_exclusion() {
		$post_data = $this->input->post();
		$max_limit = $post_data['max_limit'];		
		$set_by = 1;
		$this->db->select('user_self_exclusion_id');
        $this->db->from(USER_SELF_EXCLUSION);
        $this->db->where('user_id', $this->user_id);
        $this->db->limit(1);
        $query = $this->db->get();
		$modified_date = format_date();
		$data = array();            
		$data['modified_date']  = $modified_date;
		$data['max_limit']      = $max_limit;
		$data['set_by']         = $set_by;
		if($query->num_rows() > 0) {
            $row = $query->row_array();
			$user_self_exclusion_id    = $row['user_self_exclusion_id'];
           
            $this->db->where('user_self_exclusion_id', $user_self_exclusion_id);
			$this->db->update(USER_SELF_EXCLUSION,$data);
			
		} else {
			$data['user_id']      = $this->user_id;
			$this->db->insert(USER_SELF_EXCLUSION, $data);
		}		
	}

	/**
	 * Used to get user self exclusion records
	 */
	public function get_self_exclusion(){
		$this->db->select('max_limit, reason, document, set_by, requested_max_limit');
        $this->db->from(USER_SELF_EXCLUSION);
        $this->db->where('user_id', $this->user_id);
        $this->db->limit(1);
        $query = $this->db->get();
		$result = array();
		if($query->num_rows() > 0) {
            $result = $query->row_array();		
		} 
		return $result;		
	}

}
