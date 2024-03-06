<?php
/*
* This model is used for invite related functionality
* @package    Build_network_model
* @author     Vinfotech Team
* @version    1.0
*/

class Build_network_model extends Common_Model {
	function __construct() {
		parent::__construct();
	}

	/**
	* @Summary: Get registeres user
	* @create_date: monday, July 14, 2014
	* @last_update_date:
	* @access: public
	* @param: emails[]
	* @return: registered_emails[]
	*/
	public function get_registered_users($email_arry) {
		$rs = $this->db->select('UserID,FirstName,LastName,StatusID,Email')
				->from(USERS)
				->where_in('Email',$email_arry,NULL,FALSE)
				->where('StatusID','2')
				->get();
				$registered_email = $rs->result_array();
		$new_email = array();
		$old_email = array();
		foreach($registered_email as $email) {
			$old_email[$email['UserID']] = $email['Email'];
		}
		
		// Get all new emails
		$new_email = array_values(array_diff($email_arry,$old_email));

		$output['old_email'] = $old_email;
		$output['new_email'] = $new_email;
		return $output;
	}
	
	/**
	* @Summary: Get invited users
	* @create_date: monday, July 14, 2014
	* @last_update_date:
	* @access: public
	* @param: UserID, emails[]
	* @return: invited_emails[]
	*/
	public function get_all_invited_user($user_id,$new_emails='') {
		$output = array();
		$rs = $this->db->select('UserSocialID')
			->from(INVITATION)
			->where('UserID',$user_id)
			->where('InviteType','1')
			->where_in('UserSocialID',$new_emails)
			->get();

		$output = $rs->result_array();
		return $output;
	}

	/**
	* @Summary: Add Google Invitation
	* @create_date: monday, July 14, 2014
	* @last_update_date:
	* @access: public
	* @param: UserID, Token
	* @return: 
	*/
	public function addGoogleInvitation($user_id,$Token) {
		$data['UserID'] = $user_id;
		$data['InviteType'] = '4';
		$data['UserSocialID'] = '';
		$data['EntityType'] = '1';
		$data['EntityID'] = '0';
		$data['Token'] = $Token;
		$data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
		$data['IsRegistered'] = '0';
		$this->db->insert(INVITATION,$data);
	}

	/**
	* @Summary: Add Google Invitation
	* @create_date: monday, July 14, 2014
	* @last_update_date:
	* @access: public
	* @param: [[user_id,invited_type,user_social_id,token,created_date,is_registered,EntityType,EntityID],...[]]
	* @return: [Token1,Token2,Token3]
	*/
	function save_native_invitation($Data,$user_id=0) {
		$token = array();
		$inviteData = false;
		$i=0;
		foreach($Data as $value) {
			if($value['user_social_id']!='') {
				$existing_dataq = $this->db->get_where(USERLOGINS,array('LoginKeyword'=>$value['user_social_id'],'SourceID'=>$value['invite_type']));
				if($existing_dataq->num_rows()==0) {
					$existing_data = $this->db->get_where(INVITATION,array('InviteType'=>$value['invite_type'],'UserSocialID'=>$value['user_social_id'],'UserID'=>$value['user_id']));
					if($existing_data->num_rows()==0){
						$inviteData[$i]['UserID']		= $value['user_id'];
						$inviteData[$i]['InviteType']	= $value['invite_type'];
						$inviteData[$i]['UserSocialID']	= $value['user_social_id'];
						$inviteData[$i]['Token']		= $value['token'];
						$inviteData[$i]['CreatedDate']	= $value['created_date'];
						$inviteData[$i]['IsRegistered']	= $value['is_registered'];
						$inviteData[$i]['EntityType']	= $value['EntityType'];
						$inviteData[$i]['EntityID']		= $value['EntityID'];

						$token[] = $value['token'];
						$i++;
					} else {
						$val = $existing_data->row_array();
						$token[] = $val['Token'];
					}
				}
				else
				{
					if($user_id)
					{
						$existing_dataq_row = $existing_dataq->row_array();
						if($existing_dataq_row['UserID'] != $user_id)
						{
	                		$this->load->model('users/friend_model');
	                		$this->friend_model->sendFriendRequest($existing_dataq_row['UserID'],$user_id);
						}
					}
				}
			}	
		}
			//Insert all invitation data
		if($inviteData){
			$this->db->insert_batch(INVITATION,$inviteData);
		}
		return $token;
	}

	/**
	* @Summary: Check Social IDs
	* @create_date: monday, July 14, 2014
	* @last_update_date:
	* @access: public
	* @param: social_type, social_ids
	* @return: [[UserID,LoginKeyword,social_id,FirstName,LastName,UserGUID]...[]]
	*/
	public function check_social_ids($social_type, $social_ids) {

		$rs = $this->db->select('ul.UserID, ul.LoginKeyword AS social_id, u.FirstName, u.LastName, u.UserGUID')
				->from(USERLOGINS. " AS ul")
				->join(USERS." AS  u", 'ul.UserID = u.UserID', 'left')
				->where('ul.SourceId', $social_type)
				->where('ul.UserID !=', $this->UserID)
				->where_in('ul.LoginKeyword' , $social_ids)
				->get();
		return $rs->result_array();
	}


	//Not in use now
	public function AddConnectionViaSocial($result) {		
		$follow_arry     = array();
		$type_entity_ids = array();

		foreach($result as $val) {
			$temp = array();
			if($this->UserID != $val['UserID']) {
				$temp['UserID']       = $this->UserID;
				$temp['TypeEntityID'] = $val['UserID'];
				$temp['Type']         = 1;
				$type_entity_ids[]    = $val['UserID'];
				$follow_arry[ $temp['TypeEntityID'] ] = $temp;
			}			
		}

		$rs = $this->db->select('TypeEntityID')
			->from(FOLLOW)
			->where('UserID', $this->UserID)
			->where('Type','1')
			->where_in('TypeEntityID', $type_entity_ids)
			->get();

		$data = $rs->result_array();

		if(!empty($data)) {
			foreach($data as $val) {
				unset($follow_arry[$val['TypeEntityID']]);
			}
		}
		if(!empty($follow_arry)){
			$this->db->insert_batch(FOLLOW, $follow_arry);
		}
	}

	//Not in use now
	public function follow($current_user_id , $data) {
		$all_ids = array_keys($data);

		$rs = $this->db->select('TypeEntityID')
			->from(FOLLOW)
			->where('UserID', $current_user_id)
			->where('Type', '1')
			->where_in('TypeEntityID', $all_ids)
			->get();
		$result = $rs->result_array();

		$already_follow_user = array();

		foreach($result as $val) {
			$already_follow_user[] = $val['TypeEntityID'];
		}
		$final_arry = array_diff($all_ids, $already_follow_user);

		$insert_arry = array();

		if(!empty($final_arry)) {
			foreach($final_arry as $id) {
				$temp                 = array();
				$temp['UserID']       = $current_user_id;
				$temp['TypeEntityID'] = $id;
				$temp['Type']         = 1;
				$temp['DateTime']     = get_current_date('%Y-%m-%d %H:%i:%s');

				$insert_arry[] = $temp;
			}
			$this->db->insert_batch(FOLLOW, $insert_arry);
		}

	}

	/**
	* @Summary: Check User Exists
	* @create_date: monday, July 14, 2014
	* @last_update_date:
	* @access: public
	* @param: Email
	* @return: UserID or False
	*/
	public function checkUserExists($Email){
		$query = $this->db->get_where(USERLOGINS,array('LoginKeyword'=>$Email));
		if($query->num_rows()){
			$row = $query->row();
			return $row->UserID;
		} else {
			return false;
		}
	}
}