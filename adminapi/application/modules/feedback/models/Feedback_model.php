<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Feedback_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		//Do your magic here
		$this->admin_id = $this->session->userdata('admin_id');
	}

}
/* End of file Contest_model.php */
/* Location: ./application/models/Contest_model.php */