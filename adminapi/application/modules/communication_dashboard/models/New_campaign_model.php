<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'modules/communication_dashboard/models/User_segmentation_model.php';

class New_campaign_model extends User_segmentation_model 
{
	public function get_segementation_templates($type)
	{
		$this->db->select("ET.*,ND.message as notification_message")
		->from(CD_EMAIL_TEMPLATE.' ET')
		->join(NOTIFICATION_DESCRIPTION.' ND','ND.notification_type=ET.notification_type','INNER');
		if(!empty($type))
		{
			$this->db->where_in('type',array($type,6));
		}
		
		$cd_email_template_id = $this->input->post('cd_email_template_id');

		if(!empty($cd_email_template_id))
		{
			$this->db->where('cd_email_template_id='.$cd_email_template_id);
		}
		
		$result =$this->db->get()->result_array();
		return $result;
	}

			public function filter_system_users($user_id){
				$user_collection=array();
				if(!empty($user_id['user_ids'])){
				$result = $this->db->select('user_id')
				->from(USER.' AS U')
				->where('U.is_systemuser',0)
				->where('U.status',1)
				->where_in('U.user_id',$user_id['user_ids'])
				->get()->result_array();
				$result = array_column($result,'user_id');
	        	$user_collection['user_count'] = count($result);
	        	$user_collection['user_ids'] = $result;
				}
				else{
				$result = array();
				$user_collection['user_count'] = count($result);
	        	$user_collection['user_ids'] = implode(',',$result);
				}
				return $user_collection;
			}

	public function update_recent_communication($data,$where)
	{
		$this->db->update(CD_RECENT_COMMUNICATION,$data,$where);
		// echo $this->db->last_query();exit;
		return $this->db->affected_rows();
	}

	public function get_delas_list($filters)
	{
		$result = $this->db->select()
		->from(DEALS)
		->where('status',1)
		->where('is_deleted',0);

		if(isset($filters['deal_id']) && $filters['deal_id']!='')
		{
			$this->db->where('deal_id',$filters['deal_id']);
		}
		$result = $this->db->get()->result_array();

		return $result;
	}
	
}