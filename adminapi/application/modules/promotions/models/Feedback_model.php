<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Feedback_model extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->db_fantasy		= $this->load->database('db_fantasy', TRUE);
		$this->db_user		= $this->load->database('db_user', TRUE);
	}
	
	public function get_feedback_pending_count()
	{
		$this->load->model('auth/Auth_nosql_model');
		$question_cond = array();
        $question_cond['status'] = 0;
        //get qusetion comments
		$pending_count = $this->Auth_nosql_model->count(COLL_FEEDBACK_QUESTION_ANSWERS,$question_cond); 
		return $pending_count;
	}
}