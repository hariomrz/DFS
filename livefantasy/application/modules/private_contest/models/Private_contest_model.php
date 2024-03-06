<?php 

class Private_contest_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		// $this->db_user = $this->load->database('db_user', TRUE);
		$this->user_db = $this->load->database('user_db', TRUE);
	}

	/** 
     * common function used to get single record from any table
     * @param string    $select
     * @param string    $table
     * @param array/string $where
     * @return	array
     */
    function get_single_row_from_user($select = '*', $table, $where = "") {
        $this->user_db->select($select, FALSE);
        $this->user_db->from($table);
        if ($where != "") {
            $this->user_db->where($where);
        }
        $this->user_db->limit(1);
        $query = $this->user_db->get();
        return $query->row_array();
    }

    /**
     * used to save user contest
     * @param array $contest_data
     * @return int
    */
    public function save_contest($contest_data)
	{
		$contest_data['added_date'] = format_date();
		$contest_data['modified_date'] = format_date();

		$this->db->trans_start();
		$this->db->insert(CONTEST,$contest_data);
		$contest_id = $this->db->insert_id();
		$this->db->trans_complete();
		$this->db->trans_strict(FALSE);

		if ($this->db->trans_status() === FALSE)
		{
		    $this->db->trans_rollback();
			return false;
		}
		else
		{
			$this->db->trans_commit();
			return $contest_id;
		}
	}
}