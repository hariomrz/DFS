<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth_model extends MY_Model{

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * This funciton used for check admin authentication
	 * @param      [array]  [for login email and password]
	 * @return     [array]
	 */
	public function affiliate_login($email,$password)
	{
		$sql = $this->db->select("A.*",FALSE)
						->from(AFFILIATE." AS A")
						->where("email",$email)						
						->where("password",md5($password))
						->get();
		
		$result = $sql->row_array();
		return ($result)?$result:array();
	}

	public function save_key($login_data)
	{
		if(!empty($login_data))
		{
			$this->db->insert(ACTIVE_LOGIN,$login_data);
			return $this->db->insert_id();
		}
	}

	public function delete_active_login_key($key, $device_type = "1")
	{
		$this->db->where('key', $key)->delete(ACTIVE_LOGIN);
		echo $this->db->last_query();exit;
	}
}