<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth_model extends MY_Model{

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * [admin_login description]
	 * @MethodName admin_login
	 * @Summary This funciton used for check admin authentication
	 * @param      [array]  [for login email and password]
	 * @return     [array]
	 */
	public function admin_login($email,$password)
	{
		//$sql = $this->db->select("AD.admin_id,AD.privilege,AD.status,AD.email,AD.role,CONCAT_WS(' ', AD.firstname, AD.lastname) AS full_name,ARR.right_ids",FALSE)
		$sql = $this->db->select("AD.admin_id,AD.privilege,AD.status,AD.email,AD.role,CONCAT_WS(' ', AD.firstname, AD.lastname) AS full_name,AD.access_list,AD.created_by,two_fa",FALSE)
						->from(ADMIN." AS AD")
						->join(ADMIN_ROLES_RIGHTS." AS ARR","ARR.role_id = AD.role","inner")
						->where("email",$email)						
						->where("password",md5($password))
						->get();
		
		$result = $sql->row_array();
		return ($result)?$result:array();
	}

	/**
	 * [check_user_key description]
	 * @MethodName check_user_key
	 * @Summary This function used for check user key exist
	 * @param      [varchar]  [Login key]
	 * @return     [array]
	 */
	public function check_user_key($key)
	{
		$sql = $this->db->select("*")
						->from(ACTIVE_LOGIN)
						->where("key",$key)
						->where("role",2)
						->get();
		$result = $sql->row_array();
		return ($result)?$result:array();
	}

	/**
	 * [update_password description]
	 * @MethodName update_password
	 * @Summary This funciton used for update admin password
	 * @param      [array]  [admin_id,password]
	 * @return     [array]
	 */
	public function update_password($data)
    {
       $this->db->where('admin_id',$data['admin_id'])->update(ADMIN,array('password'=> $data['password']));
       return $this->db->affected_rows();    
    }

}