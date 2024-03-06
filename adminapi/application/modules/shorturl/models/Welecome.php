<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database('user_db');
		//Do your magic here
	}
	
}

/* End of file Finance_model.php */
/* Location: ./application/models/User_model.php */