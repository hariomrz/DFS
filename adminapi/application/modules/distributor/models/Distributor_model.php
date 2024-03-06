<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Distributor_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database('user_db');
		
	}
	/*
      * function : add_admin
      * def: create distributor
      * @params : int id
      * @return : array admin detail
      */
	  public function add_admin($post_data)
	  {
		if(isset($post_data['admin_id']) && $post_data['admin_id']!=""){
			$admin_id =$post_data['admin_id'];
			unset($post_data['admin_id']);
			unset($post_data['balance']);
			$this->db->where('admin_id', $admin_id)
			->update(ADMIN, $post_data); 
			return $admin_id;
		} else {

		  $this->db->insert(ADMIN,$post_data);
		//   /echo $this->db->last_query(); die;
		  return $this->db->insert_id();
		}			  
		  
	  }
	/*
      * function : get_admin_by_id
      * def: get admin detial by id
      * @params : int id
      * @return : array admin detail
      */
	  public function get_admin_by_id($id)
	  {
		  $sql = $this->db->select('admin_id, email, fullname, username,role,mobile,balance,commission_percent,created_by,state_id,country_id,address,city,status',FALSE)
						  ->from(ADMIN.' as A')
						  ->where('A.admin_id',$id)
						  ->get();
		  return $sql->row_array();
	  }
	/*
      * function : get_admin_list
      * def: get_admin_list
      * @params : int id
      * @return : array admin 
      */
	  public function get_admin_list($is_total)
	  {	
		    $sort_field	= 'admin_id';
			$sort_order	= 'DESC';

		    $post_data = $this->input->post(); 


	    	if($post_data['sort_field'] && in_array($post_data['sort_field'],array('firstname','lastname','status','username','email')))
			{
				$sort_field = $post_data['sort_field'];
			}

			if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
			{
				$sort_order = $post_data['sort_order'];
			}

		    $sql = $this->db->select('admin_id, email, fullname, username,role,mobile,city,commission_percent,balance',FALSE)
						  ->from(ADMIN.' as A')
						  ->order_by('A.'.$sort_field, $sort_order);
						  
			if(isset($post_data['created_by']) && $post_data['created_by'] != "")
			{
				$this->db->where('created_by',$post_data['created_by'] );
			} 
			if(isset($post_data['role']) && $post_data['role'] != "")
			{
				$this->db->where('role',$post_data['role'] );
			}

			if(isset($post_data['keyword']) && $post_data['keyword'] != "")
			{
				$this->db->like('LOWER( CONCAT(IFNULL(A.email,""),IFNULL(A.firstname,""),IFNULL(A.lastname,""),IFNULL(A.username,""),IFNULL(A.mobile,""),CONCAT_WS(" ",A.firstname,A.lastname)))', strtolower($post_data['keyword']) );
			}

			if (!isset($post_data['limit']) && $post_data['csv']== FALSE)
			{
				$post_data['limit'] = 10;
			}
			if (!isset($post_data['current_page']))
			{
				$post_data['current_page'] = 1;
			}

		




			if ($is_total === FALSE && $post_data['csv']== FALSE)
			{
				$this->db->limit($post_data['limit'], $post_data['limit']*($post_data['current_page']-1));
			}

			if ($is_total == FALSE)
			{
				return $sql->get()->result_array();
			}
			else
			{
				return $sql->get()->num_rows();
			}

			
	  }

	  /*
      * function : do_recharge
      * def: do_recharge
      * @params : int id
      * @return : $id
      */
	  public function do_recharge($post_data)
	  {
		  $this->db->insert(ADMIN_RECHARGE,$post_data);
		  return $this->db->insert_id();			  
		  
	  }
	  /*
      * function : get_recharge_list
      * def: get_recharge_list
      * @params : int id
      * @return : array admin 
      */
	  public function get_recharge_list($is_total)
	  {
		    $post_data = $this->input->post(); 

		    $sql = $this->db->select('U.user_name,U.first_name,U.last_name,AT.user_id,AT.amount,AT.created_date,AT.admin_id',FALSE)
						  ->from(ADMIN_TRANSACTION.' as AT')
						  ->join(USER.' U','U.user_id=AT.user_id');
						  
			if(isset($post_data['admin_id']) && $post_data['admin_id'] != "")
			{
				$this->db->where('admin_id',$post_data['admin_id'] );
			}
			
			if(isset($post_data['keyword']) && $post_data['keyword'] != "")
			{
				$this->db->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name)))', strtolower($post_data['keyword']) );
			}
			
			$this->db->order_by('AT.dtransaction_id','desc' );

			if (!isset($post_data['limit']))
			{
				$post_data['limit'] = 10;
			}
			if (!isset($post_data['current_page']))
			{
				$post_data['current_page'] = 1;
			}

			if ($is_total === FALSE  and $post_data['csv']==FALSE)
			{
				$this->db->limit($post_data['limit'], $post_data['limit']*($post_data['current_page']-1));
			}

			if ($is_total === FALSE)
			{
				return $sql->get()->result_array();
			}
			else
			{
				return $sql->get()->num_rows();
			}

	  }

	  /*
      * function : do_recharge
      * def: do_recharge
      * @params : int id
      * @return : $id
      */
	  public function approve_recharge($post_data,$recharge_id)
	  {
		  $this->db->where('recharge_id', $recharge_id)
		  ->update(ADMIN_RECHARGE, $post_data); 
		  return $this->db->affected_rows();
		  
	  }
	  /*
      * function : change_status
      * def: change_status
      * @params : int id
      * @return : $id
      */
	  public function change_status($post_data,$admin_id)
	  {
		  $this->db->where('admin_id', $admin_id)->update(ADMIN, $post_data); 

		  return $this->db->affected_rows();
		  
	  }

	  /*
      * function : get_recharge_by_id
      * def: get recharge by id
      * @params : int id
      * @return : array 
      */
	  public function get_recharge_by_id($recharge_id)
	  {
		  $sql = $this->db->select('recharge_id, amount, from_admin_id,to_admin_id,status,reference_id, upload_reciept',FALSE)
						  ->from(ADMIN_RECHARGE.' as AR')
						  ->where('AR.recharge_id',$recharge_id)
						  ->get();
		  return $sql->row_array();
	  }

	  
	 /*
      * function : update_balance
      * def: update balance
      * @params :$admin_id,$amount,$type
      * @return : bolean
      */
	  public function update_balance($admin_id,$amount,$type = 'credit')
	  {		
		 
		$this->db->where('admin_id', $admin_id);
		
		if($type == "credit") { 
		  $this->db->set('balance', '`balance`+ '.$amount, FALSE);
		} else {
			$this->db->set('balance', '`balance`- '.$amount, FALSE);
		}
		
		$this->db->update(ADMIN);  
		return $this->db->affected_rows();
		  
	  }

	  /*
      * function : get_search_user
      * def: search user
      * @params :$post_data
      * @return : array
      */
	  public function get_search_user($post_data)
	  {		
		 
		$sql =  $this->db->select('user_name as label,user_unique_id as value',FALSE)
						  ->from(USER.' as U');

				if(isset($post_data['keyword']) && $post_data['keyword'] != "")
				{
					$this->db->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name),IFNULL(U.pan_no,"")))', strtolower($post_data['keyword']) );
				}else{
					$this->db->where('user_name!=','');
				}
				
				$this->db->where('is_systemuser','0');
		return $sql->get()->result_array();
		  
	  }
	  /*
      * function : recharge_user
      * def: recharge_user
      * @params : int id
      * @return : $id
      */
	  public function recharge_user($post_data)
	  {
  	 	  $current_date = format_date();
 		  $post_data['created_date'] = $current_date;
		  $this->db->insert(ADMIN_TRANSACTION,$post_data);
		  return $this->db->insert_id();
	  }

	   /*
      * function : get_user_by_unique_id
      * def: get_user_by_unique_id
      * @params :$user_unique_id
      * @return : array
      */
	  public function get_user_by_unique_id($user_unique_id)
	  {		
		 
		$sql =  $this->db->select('user_name,email,first_name,last_name,user_id,user_unique_id',FALSE)
						  ->from(USER.' as U');
		$this->db->where('U.user_unique_id', $user_unique_id);
		return $sql->get()->row_array();
		  
	  }

	  /*
      * function : get_recharge_request_list
      * def: get_recharge_request_list
      * @params : int id
      * @return : array admin 
      */
	  public function get_recharge_request_list($is_total)
	  {
			$post_data = $this->input->post();
			$select = "";
			if((isset($post_data['from_admin_id']) && $post_data['from_admin_id'] != "")  || (isset($post_data['from_admin_id']) && $post_data['from_admin_id'] != ""))
			{	
				$select = "A.firstname,A.lastname,A.fullname,ATO.firstname as firstname_to,ATO.lastname as lastname_to,ATO.fullname as fullname_to,";  
			}
			
			
		    $sql = $this->db->select($select.'AR.recharge_id, AR.amount, AR.from_admin_id,AR.to_admin_id,AR.status,AR.reference_id, AR.upload_reciept',FALSE)
							->from(ADMIN_RECHARGE.' as AR');
						  
			if(isset($post_data['from_admin_id']) && $post_data['from_admin_id'] != "")
			{	
				$this->db->join(ADMIN.' A','A.admin_id=AR.from_admin_id','LEFT');
				$this->db->join(ADMIN.' ATO','ATO.admin_id=AR.to_admin_id','LEFT');
				$this->db->or_where('to_admin_id',$post_data['from_admin_id'] );
				$this->db->or_where('from_admin_id',$post_data['from_admin_id'] );
			}
			
			if(isset($post_data['status']) && $post_data['status'] != "")
			{	
				//$this->db->where('AR.status',$post_data['status'] );
			}
			$this->db->order_by('AR.recharge_id','desc' );
			
			/* if(isset($post_data['keyword']) && $post_data['keyword'] != "")
			{
				$this->db->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name)))', strtolower($post_data['keyword']) );
			} */

			if (!isset($post_data['limit']))
			{
				$post_data['limit'] = 10;
			}
			if (!isset($post_data['current_page']))
			{
				$post_data['current_page'] = 1;
			}

			if ($is_total === FALSE && $post_data['csv']==FALSE)
			{
				$this->db->limit($post_data['limit'], $post_data['limit']*($post_data['current_page']-1));
			}

			if ($is_total === FALSE)
			{
				return $sql->get()->result_array(); //echo $this->db->last_query(); die;
			}
			else
			{
				return $sql->get()->num_rows();
			}

	  }

	/**
	 * [get_admin_last_active_key description]
	 * @MethodName get_admin_last_active_key
	 * @Summary This function used to get last active key
 	 * @param  admin_id
	 * @param  [array] [data_array]
	*/
	public function admin_last_active_key($admin_id){

		$result =$this->db->select("AL.key",FALSE)
						->from(ACTIVE_LOGIN." AS AL")
						->where("user_id", $admin_id)
						->order_by("keys_id", "desc")
						->get()->result_array();
		return $result;
	}

	/**
	 * [delete_distributor_role_login description]
	 * @MethodName delete_distributor_role_login
	 * @Summary This function used to delete active 
 	 * @param  admin_id
	 * @return  [boolean]
	*/
	public function block_distributor_role_login($admin_id)
	{
		$this->db->where("user_id", $admin_id)->delete(ACTIVE_LOGIN);
		return TRUE;
	}

	public function check_username()
	{		
	   $email = $this->input->post('email');
	   $mobile = $this->input->post('mobile');
	  $sql =  $this->db->select('username,role',FALSE)
			->from(ADMIN.' as A')
			 ->where('email',$email)
			 ->or_where('mobile',$mobile);
	  return $sql->get()->row_array();
		
	}

}

/* End of file User_model.php */
