<?php 
class Private_contest_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}

    /**
     * used to save user contest
     * @param array $contest_data
     * @return int
    */
    public function save_user_contest($contest_data)
    {
        $contest_data['added_date'] = format_date();
        $contest_data['modified_date'] = format_date();
        try {
            $this->db->trans_start();
            $this->db->insert(CONTEST,$contest_data);
            $contest_id = $this->db->insert_id();

            $this->db->trans_complete();
            $this->db->trans_strict(FALSE);

            if($this->db->trans_status() === FALSE)
            {
                $this->db->trans_rollback();
                return false;
            }
            else
            {
                $this->db->trans_commit();
                return $contest_id;
            }
        }catch(Exception $e){
            $this->db->trans_rollback();
            return false;
        }
    }
}