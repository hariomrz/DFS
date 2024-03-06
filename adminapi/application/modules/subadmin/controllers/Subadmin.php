<?php if (!defined('BASEPATH'))	exit('No direct script access allowed');

class Subadmin extends MYREST_Controller 
{
	public $admin_id = "";

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
		$this->load->model('Subadmin_model');		
		$this->admin_id =$this->session->userdata('admin_id');	
		//Do your magic here
		$this->admin_lang = $this->lang->line('Subadmin');
	}

	public function save_admin_post()
	{

		if ($this->input->post())
		{
			$this->form_validation->set_rules('firstname', 'Name', 'trim|required');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique['.ADMIN.'.email]');
			$this->form_validation->set_rules('username', 'Username', 'trim|required|is_unique['.ADMIN.'.username]');
            $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[5]');
			$this->form_validation->set_rules('confpassword', 'Confirm password', 'trim|required|matches[password]');

			if (!$this->form_validation->run())
			{ 
				$this->send_validation_errors();   
			}
			else
			{
				$post_values = array();
                $post_values['firstname']       = $this->input->post('firstname');
				$post_values['email']           = strtolower($this->input->post('email'));
				$post_values['username']        = $this->input->post('username');
				$post_values['password']        = md5($this->input->post('password'));
                $post_values['updated_by']      = $this->admin_id;
                $post_values['updated_date']    = format_date();
                $post_values['role']            = SUBADMIN_ROLE;
                $post_values['status']          = '1';
                $cat_id = $this->input->post('selected_group');                                
                $grouplist = $this->Subadmin_model->group_detail_by_id(implode(',', $cat_id));
                $post_values['privilege']       = json_encode($grouplist, JSON_NUMERIC_CHECK);
                                              
			    $this->db->insert(ADMIN, $post_values);
                //$this->db->last_query();
           
                /*
                 * Send email with login detail
                 */ 
                $data['name']       = $this->input->post('firstname');
                $data['email']      = $this->input->post('email');
                $data['password']   = $this->input->post('password');
                $data['link']       = site_url('admin');
              
                $this->api_response_arry['message']       = $this->admin_lang["add_admin_success"];
				$this->api_response_arry['data']['next_url']  		  = site_url('manageadmin');
				$this->api_response();
			}
		}
	}
  
    /**
    *@method get_admin_group_post
    *@uses function to get admin access group list
    **/
  	public function get_admin_group_post()
  	{
		$grouplist = $this->Subadmin_model->get_subadmin_group_list();		
		$result['grouplist'] = $grouplist;
		$this->api_response_arry['response_code']  = rest_controller::HTTP_OK;
				$this->api_response_arry['data']  = $result;
				$this->api_response();
  	}

   /**
   *@method group_detail_by_id_post
   *@uses function to get subgrouplist by category id
   ***/	
   public function group_detail_by_id_post()
   {
		$post_data = $this->input->post();
		$sub_grouplist = $this->Subadmin_model->group_detail_by_id(implode(',', $post_data['category_id']));		
		$result['sub_grouplist'] = $sub_grouplist;

		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data'] = $result;
		$this->api_response();
   }

   /**
   *@method change_user_status_post
   *@uses function to change sudadmin status
   **/
   public function change_user_status_post()
   {
	    $this->form_validation->set_rules('user_unique_id', 'User Unique Id', 'trim|required');
	    $this->form_validation->set_rules('status', 'Status', 'trim|required');
	    $status = $this->input->post("status");
    
	    if (!$this->form_validation->run()) 
	    {
	      $this->send_validation_errors();
	    }		
    
	    $user_unique_id = $this->input->post('user_unique_id');   
	    $data_arr = array(
	            "status"    => $status
	          );
	    $this->db->where('admin_id', $user_unique_id)
	                  ->update(ADMIN, $data_arr); 
    
	    if($user_unique_id && $status != '')
	    {
	      	$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['message']       = $this->admin_lang['update_status_success'];
			$this->api_response();
	    }
	    else
	    {
	      	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']       = $this->lang->line('no_change');
			$this->api_response();
	    } 
  	}  

    /**
    *@method change_all_user_status_post
    *@uses function to change multiple subadmin status
    **/
    public function change_all_user_status_post()
	{
		$this->form_validation->set_rules('status', 'Status', 'trim|required');
		$status = $this->input->post("status");
		
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$user_unique_ids = $this->input->post('user_unique_id');
		if(empty($user_unique_ids))
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']       = $this->lang->line('invalid_parameter');
			$this->api_response();
		}

		$reason = $this->input->post("reason")?$this->input->post("reason"):"";
		
		$data_arr = array(
						"status"			=> $status							
					);
		$this->db->where_in('admin_id', $user_unique_ids)
              ->update(ADMIN, $data_arr); 
		$result = $this->db->affected_rows();
		
		if($result)
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['message']       = $this->admin_lang['update_status_success'];
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']       = $this->lang->line('no_change');
			$this->api_response();
		}
	}

	public function send_email_selected_user_post()
	{
		$this->form_validation->set_rules('subject', 'Subject', 'trim|required');
		$this->form_validation->set_rules('message', 'Message', 'trim|required|max_length[500]');
		$this->form_validation->set_rules('selected_emails','Selected emails' ,'trim|required');

		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$this->load->helper('mail_helper');
		$post_data = $this->input->post();
		
		$this->send_email($post_data['selected_emails'], $post_data['subject'], $post_data['message']);
		//send_email($post_data['selected_emails'], $post_data['subject'], $post_data['message']);
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['message']       = $this->lang->line('email_send');
		$this->api_response();
	}

	/**
	*@Method : get_admin_list_post
	*@uses : function to get admin list
	*@modifiedBy: Ankit Patidar <ankit.patidar@vinfotech.com>
	*@modified on 24th Feb 2017 12:01 PM
	**/
     public function get_admin_list_post()
	{
		
		$data_post   = $this->input->post();

		$start       = $data_post['start'];
		$filter_name = (isset($data_post['search_keyword'])) ? $data_post['search_keyword'] : '';
		$limit       = (!empty($data_post['limit'])) ? $data_post['limit'] : 10;
		$fieldname   = $data_post['field'];
		$order       = $data_post['order'];
		$offset      = $start;

		$config['limit']       = $limit;
		$config['dataparam']   = $data_post['dataparam'];
		$config['start']       = $start;
		$config['filter_name'] = $filter_name;
		$config['fieldname']   = $fieldname;
		$config['order']       = $order;
		$config['is_csv']      = false;

		$admin_list = array();
		$admin_result = $this->Subadmin_model->get_admin_list($config, false);
        
        $total = $admin_result['total'];       
        $admin_list = $admin_result['result'];   

        if(!empty($admin_list)){
            
            foreach($admin_list as $key=>$admin) {
                
				$privilege_obj                    = json_decode($admin['privilege']);              
				$admin_list[$key]['privilege_obj'] = $privilege_obj;
            }
        }
                
		$order_sequence = $order == 'ASC' ? 'DESC' : 'ASC';
                
		$result = array(
						'adminlist'      => $admin_list,
						'start'          => $offset,
						'total'          => $total,
						'field_name'     => $fieldname,
						'order_sequence' => $order_sequence
		);
                
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']          = $result;
		$this->api_response_arry['message']       = "";
		$this->api_response();
                
	}
        
	public function get_admin_by_id_post()
	{
		if ($this->input->post())
		{
			$id = $this->input->post('adminid');
			$this->form_validation->set_rules('adminid', 'Admin key', 'trim|required');
			if ($this->form_validation->run())
			{
				$result        = $this->Subadmin_model->get_admin_by_id($id);	
				$prev_arr      = json_decode($result['privilege']);				
				
				$selected_abbr = implode( ',' , array_map( function( $n ){ return '\''.$n.'\''; } ,  $prev_arr) );
				$result['category_ids'] = $this->Subadmin_model->group_detail_by_abbr($selected_abbr);
				
				if ($result)
				{					
					$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
					$this->api_response_arry['data']          = $result;
					$this->api_response_arry['message']       = validation_errors();
					$this->api_response();
				}
			}
			else
			{
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry['message']       = validation_errors();
				$this->api_response();
			}

			
		}
	}
        
    /**
     * This method update admin info
     */
    public function update_admin_post()
	{
		if ($this->input->post())
		{   
            $this->form_validation->set_rules('id', 'Admin key', 'trim|required');
			$this->form_validation->set_rules('firstname', 'Name', 'trim|required');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|callback_admin_unique_email');
			$this->form_validation->set_rules('username', 'Username', 'trim|required|callback_admin_unique_username');
            $this->form_validation->set_rules('password', 'Password', 'trim|min_length[5]');
			$this->form_validation->set_rules('confpassword', 'Confirm password', 'trim|matches[password]');

			if ($this->form_validation->run())
			{
				$post_values = array();
                $post_values['firstname']       = $this->input->post('firstname');
				$post_values['email']           = strtolower($this->input->post('email'));
				$post_values['username']        = $this->input->post('username');
				
                $post_values['updated_by']      = $this->admin_id;
                $cat_id = $this->input->post('selected_group');                                
                $grouplist = $this->Subadmin_model->group_detail_by_id(implode(',', $cat_id));
                $post_values['privilege']       = json_encode($grouplist, JSON_NUMERIC_CHECK);
                $post_values['updated_date']    = format_date();
                
                if(!empty($this->input->post('password'))){
                        $post_values['password']= md5($this->input->post('password'));
                }
                                
				$id = $this->input->post('id');				
				$result = $this->Subadmin_model->update_admin_by_id($id, $post_values);               
                $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
				$this->api_response_arry['next_url']      = site_url('manageadmin');
				$this->api_response_arry['message']       = $this->admin_lang['admin_update_success'];
				$this->api_response();              
			} 
			else
			{
			    $this->send_validation_errors();
			}
		}
	}

		/**
     * This method checks for unique email in case of edit info
     * @param string $email
     * @return boolean
     */
    function admin_unique_email($email)
    {
    	
        $id = $this->input->post('id');
        $this->db->select('admin_id',FALSE)
                ->from(ADMIN.' as A')
                ->where('A.email',$email);
        
        if(!empty($id)){
                $this->db->where("A.admin_id != $id");
        }
        
        $sql = $this->db->get();
        $result = $sql->num_rows();
        
        if(empty($result)){
            return true;
        } 
        else {
            $this->form_validation->set_message('admin_unique_email', $this->admin_lang["email_must_unique"]);

            return false;
        }
    }
        
    /**
     * This method checks for unique username in case of edit info
     * @param string $username
     * @return boolean
     */
     function admin_unique_username($username)
    {
    	
        $id = $this->input->post('id');
        //echo '$username: '.$username.' :: '.$id;
        $this->db->select('admin_id',FALSE)
                ->from(ADMIN.' as A')
                ->where('A.username',$username);
        
        if(!empty($id)){
                $this->db->where("A.admin_id != $id");
        }
        
        $sql = $this->db->get();
        $result = $sql->num_rows();
        
        if(empty($result)){
            return TRUE;
        } 
        else {

            $this->form_validation->set_message('admin_unique_username', $this->admin_lang["username_must_unique"]);

            return false;
        }
    }

       /**
	 * @Summary		: Export List all admin list
	 * @access 		: public
	 * @param            : Null
	 */
	public function export_users_post()
	{
		$sql = $this->db->select('firstname AS Name,email AS Emails')
						->from(ADMIN)
						->where('status', '1')
						->where('role', '2')
						->get();
		
		$this->load->dbutil();
		$this->load->helper('download');
		$data = $this->dbutil->csv_from_result($sql);
		$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" .html_entity_decode($data);
		$name = 'AdminList.csv';
		force_download($name, $data);
		exit();
	}

	public function send_email_all_user_post()
	{
		$this->form_validation->set_rules('subject', 'Subject', 'trim|required');
		$this->form_validation->set_rules('message', 'Message', 'trim|required|max_length[500]');
		$this->load->helper('mail_helper');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
		$sql = $this->db->select('GROUP_CONCAT(email) as emails')
						->from(ADMIN)
						->where('status', '1')
						->get();
		$result = $sql->row_array();
		
		$user_list  = $result['emails'];
		if($user_list)
		{
			$this->send_email($user_list, $post_data['subject'], $post_data['message']);
		}

		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['message']       = $this->lang->line('email_send');
		$this->api_response();      
	}

 /**
 * Subscription controller adminapi/application/modules/subscription/models/Subscription_model.php
 */      
}