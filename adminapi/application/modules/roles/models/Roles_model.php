<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Roles_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database('user_db');
		//Do your magic here
	}

	/**
	* [get_all_admin description]
	* @Summary : Use to get list of all admin
	* @return array
	*/
	public function get_all_admin()
	{  	 
		$sort_field	= 'firstname';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;
		$post_data = $this->input->post();
		if($post_data['items_perpage'])
		{
			$limit = $post_data['items_perpage'];
		}

		if($post_data['current_page'])
		{
			$page = $post_data['current_page']-1;
			// if($post_data['current_page']==1) {
			// 	$total = $this->get_all_user_counts($post_data); 
			// }
		}

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('firstname','email','status','lastname')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		$offset	= $limit * $page;
		$sql = $this->db->select("U.admin_id,U.firstname,U.lastname,U.email,U.status,U.access_list,U.two_fa",FALSE)
								->from(ADMIN.' AS U')
								->order_by('U.'.$sort_field, $sort_order);
		
		if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != ''  && $post_data['keyword'] == "")
		{
			$this->db->where("DATE_FORMAT(U.added_date, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(U.added_date, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
		}
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.firstname,""),IFNULL(U.lastname,""),,CONCAT_WS(" ",U.firstname,U.lastname)))', strtolower($post_data['keyword']) );
		}
	
		if(isset($post_data['status']) && $post_data['status']==0)
		{
			$this->db->where("U.status",$post_data['status']);
		} else if(isset($post_data['status']) && $post_data['status']==1){
			$this->db->where_in("U.status",array(1));
		}
		$this->db->where_in("U.role",1);
		$this->db->where_not_in('U.admin_id',1);
	
		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows(); 

		$sql = $this->db->group_by("U.admin_id")
						->limit($limit,$offset)
						->get();
		$result	= $sql->result_array();
		

		$result=($result)?$result:array();
		if(!empty($result)){
			foreach ($result as $key => $value) {
				//print_r($value);exit;
				if(is_null($value['access_list']) || $value['access_list']=='null'){
					$result[$key]['access_list'] = get_admin_menu_keys($this->app_config);
				}else{
					$result[$key]['access_list'] = json_decode($value['access_list']);
				}
			}
		}
		//echo "<pre>";print_r($result);exit;
		return array('result'=>$result,'total'=>$total);
	}

	
	/**
	 * [get_roles_detail_by_id description]
	 * @Summary This function used for get admin role list
	 * @param  [int]  [admin_id]
	 * @return [array]
	 */
	public function get_roles_detail_by_id($admin_id)
	{       
		$result = $this->db->select("U.admin_id,U.firstname,U.lastname,U.email,U.access_list,two_fa",FALSE)
						->from(ADMIN." AS U")
						->where("U.admin_id",$admin_id)
						->where("U.role",1)
						->get()->row_array();
						//echo $this->db->last_query();die;
		if(!empty($result)){
			//print_r($result['access_list']);exit;
				if($result['access_list']=='null'){
					$result['access_list'] = get_admin_menu_keys($this->app_config);
				}else{
					$result['access_list'] = json_decode($result['access_list']);
				}	
		}
		return ($result)?$result:array();
	}


	/**
	 * [update_admin_detail description]
	 * @Summary This function used to update admin detail
	 * @param      [varchar]  [admin_id]
	 * @return     [boolean]
	 */
	public function update_admin_detail($admin_id,$data_arr)
	{
		$this->db->where("admin_id",$admin_id)
				->update(USER,$data_arr);
		
		return true;
	}

	/**
	 * [get_all_admin_list description]
	 * @MethodName get_all_admin_list
	 * @Summary This function used to get all admin list
	 * @return     array
	 */
	public function get_all_admin_list()
	{
		$sql = $this->db->select('email')
						->from(ADMIN)
						->get();
		$rs = $sql->result_array();
		$rs = array_column($rs, "email");

		return $rs;
	}

	

	/**
	 * [registration description]
	 * @MethodName registration
	 * @Summary This function used to register new admin
 	 * @param  [array] [data_array]
	 * @return array
	*/
	public function registration($post)
	{
		$this->db->insert(ADMIN, $post);
		return $this->db->insert_id();
	}


	/**
	 * [delete_active_role_login description]
	 * @MethodName delete_active_role_login
	 * @Summary This function used to delete active 
 	 * @param  admin_id
	 * @return  [boolean]
	*/
	public function delete_active_role_login($admin_id)
	{
		$this->db->where("admin_id", $admin_id)
				->delete(ADMIN);

		$this->db->where("user_id", $admin_id)->where("role", '2')
				->delete(ACTIVE_LOGIN);

		return TRUE;
	}

	/**
	 * [get_admin_last_active_key description]
	 * @MethodName get_admin_last_active_key
	 * @Summary This function used to get last active key
 	 * @param  admin_id
	 * @param  [array] [data_array]
	*/
	public function get_admin_last_active_key($admin_id){

		$result =$this->db->select("AL.key",FALSE)
						->from(ACTIVE_LOGIN." AS AL")
						
						->where("AL.role",2)
						->where("user_id", $admin_id)
						->order_by("keys_id", "desc")
						->get()->result_array();
		return $result;
	}

}
/* End of file Roles_model.php */
/* Location: ./application/models/Roles_model.php */
