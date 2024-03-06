<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Logs extends CI_Controller {

	public function __construct() {

		parent::__construct();
		/*if(!isset($this->session->userdata['adminId'])) {
			redirect();
		}*/
	}
	public function show()
	{
		
		$this->load->helper( array( 'spark_url', 'fire_log' ) );
		$this->lang->load( 'fire_log' , 'english' );
		$this->config->load( 'fire_log' );
		$this->load->library( 'fire_log' );
		$this->load->spark( 'logs' );
	}
}

/* End of file logs.php */
/* Location: ./application/controllers/logs.php */