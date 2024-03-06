<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
* All common process like : 
* @package    Common
* @author     Ashwin soni(25-09-2014)
* @version    1.0
*/
class Common extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
                
                /* Gather Inputs - starts */
		if($this->input->post()){
			$JSONInput=$this->post();
		}else{
			$Handle = fopen('php://input','r');
			$JSONInput = fgets($Handle);
		}
		$this->post_data=@json_decode($JSONInput,true);
        /* Gather Inputs - ENDS */
	}
	
	public function index()
	{		
	}
	
}//End of file common.php