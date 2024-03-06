<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_segmentation_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		//$this->load->database();
		//Do your magic here
	}


	public function insert_filter($data)
	{
		$this->db->insert(USER_SEGMENTATION,$data);
		return $this->db->insert_id();
	}

	public function insert_recent_communication($data)
	{
		$this->db->insert(CD_RECENT_COMMUNICATION,$data);
		return $this->db->insert_id();
	}

	public function replace_recent_communication($data)
	{
		$this->db->replace(CD_RECENT_COMMUNICATION,$data);
		return true;
	}

	public function get_filter_list()
	{
		$result =$this->db->select("*")
		->from(MASTER_USER_SEGMENTATION)
		->get()->result_array();

		return $result;
	}

	// public function update_cd_balance($post)
	// {
	// 	$is_update = false;
	// 	if(!empty($post['email_count']))
	// 	{
	// 		$is_update = true;
	// 		$this->db->set('email_balance', 'email_balance-'.(int)$post['email_count'], FALSE);
	// 	}

	// 	if(!empty($post['sms_count']))
	// 	{
	// 		$is_update = true;
	// 		$this->db->set('sms_balance', 'sms_balance-'.(int)$post['sms_count'], FALSE);
	// 	}

	// 	if(!empty($post['notification_count']))
	// 	{
	// 		$is_update = true;
	// 		$this->db->set('notification_balance', 'notification_balance-'.(int)$post['notification_count'], FALSE);
	// 	}

	// 	if($is_update)
	// 	{
	// 		$this->db->where('cd_balance_id',1);
	// 		$this->db->update(CD_BALANCE);
	// 	}
	// 	log_message('error',"QUERY: ".$this->db->last_query());
	// 	return true;
	// }

	public function update_sent_count($post)
	{
		$is_update = false;
		$sql="UPDATE ".$this->db->dbprefix(CD_EMAIL_SENT)." ";
		$set_condition_array = array();
		$where_str = " WHERE cd_sent_id =1";
		if(!empty($post['email_count']))
		{
			$is_update = true;
			$set_condition_array[]=" email_sent=email_sent+".$post['email_count'];
		}

		if(!empty($post['sms_count']))
		{
			$is_update = true;
			$set_condition_array[]=" sms_sent=sms_sent+".$post['sms_count'];
		}

		if(!empty($post['notification_count']))
		{
			$is_update = true;
			$set_condition_array[]=" notification_sent=notification_sent+".$post['notification_count'];
		}

		if($is_update)
		{
			$sql.=' SET '.implode(',',$set_condition_array).$where_str;
			$this->db->query($sql);
		}
		log_message('error',"QUERY: ".$sql);
		return true;
	}



	public function add_balance_history($cd_balance_history_arr)
	{
		$history_arr  =array_chunk($cd_balance_history_arr, 400);

		foreach ($history_arr as $key => $one_arr) 
		{
			
			$this->db->insert_batch(CD_BALANCE_DEDUCT_HISTORY,$one_arr);
		}	

	}

	public function get_user_segmentation_list()
	{
		$this->db->select("*")
		->from(USER_SEGMENTATION.' US')
		->join(MASTER_USER_SEGMENTATION.' MUS',"US.master_user_segmentation_id=MUS.master_user_segmentation_id","INNER");

		$post = $this->input->post();
		if(!empty($post['login']))
		{
			$this->db->where('activity_type',1);
		}

		if(!empty($post['signup']))
		{
			$this->db->where('activity_type',2);
		}

		$result =$this->db->get()->result_array();
		return $result;
	}

	public function get_segementation_template_list()
	{
		$this->db->select("*")
		->from(CD_EMAIL_TEMPLATE.' ET');
		// ->join(CD_EMAIL_CATEGORY. ' EC','EC.category_id=ET.category_id','left');
		$this->db->where('type',1);
		$result =$this->db->get()->result_array();
		return $result;
	}

	function delete_segment($filter_id)
	{
		$this->db->where('user_segmentation_id',$filter_id)
		->delete(USER_SEGMENTATION);
	}

	function save_balance_history($data)
	{
		$this->db->insert(CD_BALANCE_HISTORY,$data);
	}

	function save_balance_entry()
	{
		$this->db->insert(CD_BALANCE,array('email_balance' => 0,
			'sms_balance' => 0,
			'notification_balance' => 0,
			'added_date' => format_date()));
	}

	function update_notify_balance($function_name,$value,$cond)
	{
		$this->$function_name($value);
		$this->db->where($cond);
		$this->db->update(CD_BALANCE);
		return true;
	}

	public function update_email_balance($value)
	{
		$this->db->set('email_balance', 'email_balance+'.$value, FALSE);
	}

	public function update_sms_balance($value)
	{
		
		$this->db->set('sms_balance', 'sms_balance+'.$value, FALSE);
	}

	public function update_notification_balance($value)
	{
		$this->db->set('notification_balance', 'notification_balance+'.$value, FALSE);
	}


	public function get_login_user_count_by_filter($value)
	{
		
		$current_date = format_date();
		$min_date = substr($current_date, -19, -9).' 00:00:00';
		$max_date = substr($current_date, -19, -9).' 23:59:59';
		// $min_date = date('Y-m-d',strtotime($current_date)).' 00:00:00';
		// $max_date = date('Y-m-d',strtotime($current_date)).' 23:59:59';
		$this->db->select("DISTINCT AL.user_id",FALSE)
		->from(ACTIVE_LOGIN.' AL')
		->join(USER.' U','U.user_id=AL.user_id and U.is_systemuser=0');

		if(!empty($value['from_date']))
		{	
		$min_date = date('Y-m-d',strtotime($value['from_date'])).' 00:00:00';
		}

		if(!empty($value['to_date']))
		{
		$max_date = date('Y-m-d',strtotime($value['to_date'])).' 23:59:59';
		}

		if(isset($value['mobile']) && $value['mobile']==1){

			if(isset($value['notification_on']) && $value['notification_on']==1){
				$this->db->where("U.app_notification_setting",1);
			}
			
			if(isset($value['notification_off']) && $value['notification_off']==1){
				$this->db->where("U.app_notification_setting",0);
			}
			
			$this->db->where_in("AL.device_type",[1,2]);
			$this->db->where("U.uninstall_date IS NULL");
		}
		
		if(isset($value['web']) && $value['web']==1){
			$this->db->where_in("AL.device_type",[3,4]);
		}

		$this->db->where("AL.date_created>=",$min_date)
		->where("AL.date_created<=",$max_date);
		$result =  $this->db->get()->result_array();
		return $result;
	}

	public function get_non_login_user_count_by_filter($value)
	{
		/**SELECT U.user_id,`AL`.`date_created`,AL.keys_id
FROM `vi_user` `U`
LEFT JOIN `vi_active_login` `AL` ON `AL`.`user_id`=`U`.`user_id` AND `AL`.`role`=1
AND  `AL`.`date_created` >= '2021-01-01 00:00:00'
AND `AL`.`date_created` <= '2021-01-08 23:59:59' 

WHERE `AL`.`keys_id` IS NULL
GROUP BY `U`.`user_id`**/
		$current_date = format_date();
		$min_date = substr($current_date, -19, -9).' 00:00:00';
		$max_date = substr($current_date, -19, -9).' 23:59:59';
		// $min_date = date('Y-m-d',strtotime($current_date)).' 00:00:00';
		// $max_date = date('Y-m-d',strtotime($current_date)).' 23:59:59';

		if(!empty($value['from_date']))
		{	
			$min_date = date('Y-m-d',strtotime($value['from_date'])).' 00:00:00';
		}
		if(!empty($value['to_date']))
		{
		$max_date = date('Y-m-d',strtotime($value['to_date'])).' 23:59:59';
		}
		$this->db->select("U.user_id",FALSE)
		->from(USER .' U')
		->join(ACTIVE_LOGIN.' AL',"U.user_id=AL.user_id and AL.role=1 AND AL.date_created>='{$min_date}' AND AL.date_created<='{$max_date}'",'LEFT');

		$this->db->where("AL.keys_id IS NULL");
		$this->db->where("U.is_systemuser",0);
		$this->db->group_by("U.user_id");

		$result =  $this->db->get()->result_array();
		$post = $this->input->post();
		if(!empty($post['is_debug']) && $post['is_debug'] =='1')
		{
			echo $this->db->last_query();die();
		}
		return $result;
	}


	public function get_signup_user_count_by_filter($value)
	{

		$current_date = format_date();
		$min_date = substr($current_date, -19, -9).' 00:00:00';
		$max_date = substr($current_date, -19, -9).' 23:59:59';
		// $min_date = date('Y-m-d',strtotime($current_date)).' 00:00:00';
		// $max_date = date('Y-m-d',strtotime($current_date)).' 23:59:59';
		$this->db->select("U.user_id")
		->from(ACTIVE_LOGIN.' AL')
		->join(USER.' U','U.user_id=AL.user_id and U.is_systemuser=0')
		->where('U.is_systemuser',0)
		->where('U.status',1);
		if(!empty($value['from_date']))
		{	
		$min_date = date('Y-m-d',strtotime($value['from_date'])).' 00:00:00';
		}

		if(!empty($value['to_date']))
		{
		$max_date = date('Y-m-d',strtotime($value['to_date'])).' 23:59:59';
		}

		if(isset($value['mobile']) && $value['mobile']==1){

			if(isset($value['notification_on']) && $value['notification_on']==1){
				$this->db->where("U.app_notification_setting",1);
			}
			
			if(isset($value['notification_off']) && $value['notification_off']==1){
				$this->db->where("U.app_notification_setting",0);
			}
			
			$this->db->where_in("AL.device_type",[1,2]);
			$this->db->where("U.uninstall_date IS NULL");
		}
		
		if(isset($value['web']) && $value['web']==1){
			$this->db->where_in("AL.device_type",[3,4]);
		}

		$this->db->where("added_date>=",$min_date)
		->where("added_date<=",$max_date);

		$result =  $this->db->get()->result_array();

		// echo $this->db->last_query();die('dfd');
		return $result;


	}

	public function get_scheduled_list()
	{
		$limit		= 10;
		$page		= 0;
		$sort_field='recent_communication_id';
		$sort_order= 'DESC';
		$post_data = $this->input->post();

		if(!empty($post_data['pageSize']) && $post_data['pageSize'])
		{
			$limit = $post_data['pageSize'];
		}

		if(!empty($post_data['schedule_page']) && $post_data['schedule_page'])
		{
			$page = $post_data['schedule_page']-1;
		}

		if(!empty($post_data['sort_field']) && $post_data['sort_field'])
		{
			$sort_field = $post_data['sort_field'];
		}


		if(!empty($post_data['sort_order']) && $post_data['sort_order'])
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;


		$this->db->select(" 
			RC.recent_communication_id,
			RC.user_details AS sch_user_detail,
			RC.activity_type,
			(CASE 
			WHEN RC.userbase =1 THEN 'All User' 
			WHEN RC.userbase =2 THEN 'Login' 
			WHEN RC.userbase =2 THEN 'Sign Up'
			ELSE 'Login' END) as userbase_label  	
			,
			RC.source_id,
			RC.userbase,
			RC.email_template_id,
			RC.email_count,
			RC.sms_count,
			RC.notification_count,
			RC.notification_delivered_count,
			RC.notification_viewed_count,
			RC.added_date,
			RC.updated_date,
			RC.from_date,
			RC.to_date,
			RC.sms_content,
			RC.notification_content,
			RC.is_processed,
			RC.schedule_date,
			ND.message as notification_message,
			UBL.list_name AS user_details,
			RC.user_base_list_id,
			ET.cd_email_template_id,
			ET.category_id,
			ET.template_name,
			ET.subject,
			ET.notification_type,
			ET.status,
			ET.type,
			ET.message_body,
			ET.message_url,
			ET.redirect_to,
			ET.message_type,
			ET.display_label,
			ET.date_added,
			ET.modified_date,
			UBL.list_name",FALSE)
		->from(CD_RECENT_COMMUNICATION.' RC')
		->join(CD_EMAIL_TEMPLATE.' ET',"ET.cd_email_template_id=RC.email_template_id")
		->join(NOTIFICATION_DESCRIPTION.' ND',"ND.notification_type=ET.notification_type","LEFT")
		->join(CD_USER_BASED_LIST.' UBL',"RC.user_base_list_id=UBL.user_base_list_id",'LEFT');
		$this->db->where('RC.noti_schedule','2');

		$recent_communication_id = $this->input->post('recent_communication_id');
		if(!empty($recent_communication_id))
		{
			$this->db->select('ET.email_body');
			$this->db->where('RC.recent_communication_id',$recent_communication_id);
		}

		//$this->db->order_by('RC.recent_communication_id','DESC')
		$this->db->order_by('RC.'.$sort_field,$sort_order);
		$this->db->group_by('RC.recent_communication_id');

		$tempdb = clone $this->db;
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows();
		
		$result = $this->db->limit($limit,$offset)->get()->result_array();
		return array('result' => $result,'total' => $total);
	}

	public function get_recent_communication_list()
	{
		$limit		= 10;
		$page		= 0;
		$sort_field='recent_communication_id';
		$sort_order= 'DESC';
		$post_data = $this->input->post();

		if(!empty($post_data['pageSize']) && $post_data['pageSize'])
		{
			$limit = $post_data['pageSize'];
		}

		if(!empty($post_data['currentPage']) && $post_data['currentPage'])
		{
			$page = $post_data['currentPage']-1;
		}

		if(!empty($post_data['sort_field']) && $post_data['sort_field'])
		{
			$sort_field = $post_data['sort_field'];
		}


		if(!empty($post_data['sort_order']) && $post_data['sort_order'])
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;


		$this->db->select(" 
			RC.recent_communication_id,
			RC.user_details,
			RC.activity_type,
			(CASE 
			WHEN RC.userbase =1 THEN 'All User' 
			WHEN RC.userbase =2 THEN 'Login' 
			WHEN RC.userbase =2 THEN 'Sign Up'
			ELSE 'Login' END) as userbase_label  	
			,
			RC.source_id,
			RC.userbase,
			RC.email_template_id,
			RC.email_count,
			RC.sms_count,
			RC.notification_count,
			RC.notification_delivered_count,
			RC.notification_viewed_count,
			RC.added_date,
			RC.updated_date,
			RC.from_date,
			RC.to_date,
			RC.sms_content,
			RC.notification_content,
			ND.message as notification_message,
			UBL.list_name,
			RC.user_base_list_id,
	ET.cd_email_template_id,
ET.category_id,
ET.template_name,
ET.subject,
ET.notification_type,
ET.status,
ET.type,
ET.message_body,
ET.message_url,
ET.redirect_to,
ET.message_type,
ET.display_label,
ET.date_added,
ET.modified_date",FALSE)
		->from(CD_RECENT_COMMUNICATION.' RC')
		->join(CD_EMAIL_TEMPLATE.' ET',"ET.cd_email_template_id=RC.email_template_id")
		->join(NOTIFICATION_DESCRIPTION.' ND',"ND.notification_type=ET.notification_type","LEFT")
		->join(CD_USER_BASED_LIST.' UBL',"RC.user_base_list_id=UBL.user_base_list_id",'LEFT');
		$this->db->where('RC.noti_schedule','1');

		$recent_communication_id = $this->input->post('recent_communication_id');
		if(!empty($recent_communication_id))
		{
			$this->db->select('ET.email_body');
			$this->db->where('RC.recent_communication_id',$recent_communication_id);
		}

		//$this->db->order_by('RC.recent_communication_id','DESC')
		$this->db->order_by('RC.'.$sort_field,$sort_order);
		$this->db->group_by('RC.recent_communication_id');

		$tempdb = clone $this->db;
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows();
		
		$result = $this->db->limit($limit,$offset)->get()->result_array();
		return array('result' => $result,'total' => $total);
	}

	public function get_sent_count()
	{
		$sent_count_arr = $this->db->select("*")
		->from(CD_EMAIL_SENT)
		->get()->row_array();

		if(empty($sent_count_arr))
		{
			$sent_count_arr = array();
		}
		return $sent_count_arr;
	}

	public function export_filter_data($user_ids)
	{
		$this->db->select("U.user_name as UserName,U.device_type as DeviceType, U.first_name as FirstName, U.last_name AS LastName, U.email AS Email,U.phone_no AS Mobile, MC.country_name AS CountryName, MS.name as StateName, U.city AS City,U.address AS Street,U.zip_code as ZipCode, DATE_FORMAT(U.added_date,'%d-%b-%Y %H:%i') AS MemberSince, DATE_FORMAT(U.last_login, '%d-%b-%Y %H:%i') as LastLogin",FALSE)
		->from(USER.' AS U')
		->join(MASTER_COUNTRY." AS MC","MC.master_country_id=  U.master_country_id","left")
		->join(MASTER_STATE." AS MS","MS.master_state_id=  U.master_state_id","left");
		
		$this->db->where_in('U.user_id',$user_ids);
		
		$sql =  $this->db->order_by('U.added_date', 'DESC')
		->group_by('U.user_id')
						//->order_by($sort_field, $sort_order)
		->get()->result_array();
		return $sql;

		/*$this->load->dbutil();
		$this->load->helper('download');
		$data = $this->dbutil->csv_from_result($sql);
		$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" .html_entity_decode($data);
		$name = 'UserList.csv';
		force_download($name, $data);*/
		//return exit;
	}

	public function get_deposit_promocodes($filters)
	{
		
		$current_date = format_date();
		$arr = $this->db->select("*")
		->from(PROMO_CODE);
		if(isset($filters['promocode_type']) && in_array($filters['promocode_type'],[0,1,2,3]))
		{
		$this->db->where('type',$filters['promocode_type']);
		}else
		{
		$this->db->where_in('type',[0,1,2])
		->where('value_type',1);
		}
		$arr = $this->db->where('status','1')
		->where('expiry_date>',$current_date)
		->get()->result_array();
		return $arr;
	}

	public function get_preference_list(){
		$arr = $this->db->select("*")
		->from(CD_SPORTS_PREFERENCE)
		->where('status',1)
		->get()->result_array();
		return $arr;
	}
	public function update_preference_list($post_data){
		$this->db->truncate(CD_SPORTS_PREFERENCE);
		$this->db->insert_batch(CD_SPORTS_PREFERENCE,$post_data);
		return $this->db->insert_id();
	}
	public function get_city_names(){
		$cities = $this->db->select('city')
		->distinct()
		->where('city!=',NULL)
		->where('city!=','')
		->from(USER)
		->order_by('city')
		->get()
		->result_array();
		return $cities;
	}

	public function create_user_base_list($list_data){
		if(isset($list_data) && !empty($list_data)){
			$this->db->insert(CD_USER_BASED_LIST,$list_data);
			return $this->db->insert_id();
		}
			return false;
	}

	public function update_user_base_list($list_data){
		if(isset($list_data) && !empty($list_data)){
			$update = $this->db->where('user_base_list_id',$list_data['user_base_list_id'])
			->update(CD_USER_BASED_LIST,$list_data);
			if($update){
				return true;
			}
		}
		return false;
	}

	public function delete_user_base_list($user_base_list_id){
		if(isset($user_base_list_id) && !empty($user_base_list_id)){
			$update = array('status'=>0);
			$this->db->where('user_base_list_id',$user_base_list_id)
			->update(CD_USER_BASED_LIST,$update);
			return true;
		}

		return false;
	}

	public function get_single_user_base_list($user_base_list_id){
		$list_data = $this->db->select('*')
		->where('user_base_list_id',$user_base_list_id)
		->get(CD_USER_BASED_LIST)->result_array();
		return $list_data;
	}

	public function get_user_base_list(){
		$user_data = $this->db->select('UBL.user_base_list_id,UBL.list_name,UBL.count')
		->from(CD_USER_BASED_LIST.' UBL')
		->where('status',1)
		->get()->result_array();
		return $user_data;
	}

	private function get_referral_user($lists){

		if($lists['referral']['status']==1){
				// $list = json_decode($list['referral'],true)[0];
		if($lists['referral']['min_value'] > 0 || $lists['referral']['max_value'] > 0){
			$result = $this->db->select('UAH.user_id')
			->from(USER_AFFILIATE_HISTORY.' AS UAH')
			->join(USER.' AS U','U.user_id = UAH.user_id and U.is_systemuser=0','INNER')
			->where_in('UAH.affiliate_type',[1,19,20,21])
			->where('UAH.status',1)
			->having('count(UAH.user_id) >=',$lists['referral']['min_value'])
			->having('count(UAH.user_id) <=',$lists['referral']['max_value']);
			$resutl = $this->db->group_by('UAH.user_id');
					// ->get()->num_rows();
			$result = $this->db->get()->result_array();
			$result = array_column($result, 'user_id');
		} elseif($lists['referral']['min_value'] =='0' && $lists['referral']['max_value'] =='0'){
			$result = $this->db->select('U.user_id')
			->from(USER.' AS U')
			->join(USER_AFFILIATE_HISTORY.' AS UAH','UAH.user_id=U.user_id and UAH.status = 1 and UAH.affiliate_type in(1,19,20,21)','LEFT')
			->where('U.is_systemuser',0)
			->where('UAH.user_affiliate_history_id IS NULL')
			->group_by('U.user_id');
			
			$result = $this->db->get()->result_array();
			$result = array_column($result, 'user_id');
		}
		}
		else{
			$result = array();
		}
		return $result;
	}


	private function get_user_data($lists){
	        // $this->db = $this->db_user;

		if($lists['age_group']['status']==1 || $lists['profile_status']['status']==1 || $lists['location']['status']==1 || $lists['gender']['status']==1){
			$current_date = format_date('today','Y-m-d');
			$current_year = format_date('today','Y');
			$result= $this->db->select('U.user_id')
			->from(USER.' U')
			->where('U.status',1)
			->where('U.is_systemuser',0);
	            //age group condition
			if(isset($lists['age_group']) && $lists['age_group']['status']==1){
				$age_cond = $current_year." -
				YEAR(dob) -
				IF(STR_TO_DATE(CONCAT(YEAR(".$current_date."), '-', MONTH(dob), '-', DAY(dob)) ,'%Y-%c-%e') > ".$current_date.", 1, 0)";
				$this->db->where($age_cond.'>=',$lists['age_group']['min_value']);
				$this->db->where($age_cond.'<=',$lists['age_group']['max_value']);
			}
	        //profile status
			if(isset($lists['profile_status']) && $lists['profile_status']['status']==1){

				if($lists['profile_status']['verified']==1 && $lists['profile_status']['not_verified']==0){
					$varification_criteria = array("pan_verified"=>1,"phone_verfied"=>1,"email_verified"=>1,"is_bank_verified"=>1);
					$this->db->where($varification_criteria);
				}
				elseif ($lists['profile_status']['not_verified']==1 && $lists['profile_status']['verified']==0) {
					$this->db->where("(pan_verified = '0' OR phone_verfied = '0' OR email_verified = '0' OR is_bank_verified = '0')",NULL,FALSE);
				}
	                // }
			}
	            //location filter
			if(isset($lists['location']) && $lists['location']['status']==1){
				$location_arr = array_column($lists['location']['location'],'name');
	                    // print_r($location_arr);exit;
				$this->db->where_in('city',$location_arr);
	                // }
			}
	            //gender filter
			if(isset($lists['gender']) &&  $lists['gender']['status']==1){
				$gender_arr = array_column($lists['gender']['gender'], 'name');
				$this->db->where_in('gender',$gender_arr);
			}
			$result = $this->db->get()->result_array();
			$result = array_column($result, 'user_id');
		}
		else{
			$result = array();
		}
		return $result;
	}

	private function get_money_won($lists){
	        // foreach($lists as $key=>$value){ $lists['money_deposit']['status']==1 && $lists['money_won']['status']==1
	            // print_r($value);exit;
		if(isset($lists['money_won']) && $lists['money_won']['status']==1){
				$result = $this->db->select('O.user_id')
				->from(ORDER.' AS O')
				->join(USER.' AS U','O.user_id=U.user_id and U.is_systemuser=0','INNER')
				->where('U.status',1)
				->where('O.status',1)
				->where('O.source',3);
				// $list = json_decode($value['money_won'],TRUE)[0];
				$this->db->having('sum(O.winning_amount) >=',$lists['money_won']['min_value'])
				->having('sum(O.winning_amount) <=',$lists['money_won']['max_value']);
				$this->db->group_by('O.user_id')
				->order_by("O.user_id","ASC");
				$result = $this->db->get()->result_array();
				$result = array_column($result, 'user_id');
		}
		else{
			$result = array();
		}
	        // }
	        	// echo $this->db->last_query();exit;
		return $result;
	}
	private function get_money_deposit($lists){
	        // foreach($lists as $key=>$value){
	        //    $money_deposit_data[$key]['user_base_list_id']= $value['user_base_list_id'];

	            // //MONEY DEPOSIT fileter
		// print_r($lists['money_deposited']['status']);exit;
		if(isset($lists['money_deposit']) && $lists['money_deposit']['status']==1){

			if($lists['money_deposit']['min_value'] > 0 || $lists['money_deposit']['max_value']>0)
			{
				$result = $this->db->select('O.user_id')
				->from(ORDER.' AS O')
				->join(USER.' AS U','O.user_id=U.user_id and U.is_systemuser=0','INNER')
				->where('U.status',1)
				->where('O.status',1)
				->where('O.source',7);
					// $list = json_decode($value['money_deposit'],TRUE)[0];
				$this->db->having('sum(O.real_amount) >=',$lists['money_deposit']['min_value'])
				->having('sum(O.real_amount) <=',$lists['money_deposit']['max_value']);
				$this->db->group_by('O.user_id');
				$result = $this->db->get()->result_array();
				// echo $this->db->last_query();exit;
				$result = array_column($result, 'user_id');
			}
			else if($lists['money_deposit']['min_value'] == '0' && $lists['money_deposit']['max_value'] == '0'){
					$result = $this->db->select('U.user_id')
					->from(USER.' AS U')
					->join(ORDER.' AS O','U.user_id=O.user_id and O.source=7 AND O.status=1','LEFT')
					->where('U.status',1)
					->where('O.order_id IS NULL')
					->where('U.is_systemuser',0);
						
					$this->db->group_by('U.user_id');
					$result = $this->db->get()->result_array();
					$result = array_column($result, 'user_id');
			}

			
			
		}
		else{
			$result = array();
		}
	        // echo $this->db->last_query();exit;
	        // }
		$money_won_data = $this->get_money_won($lists);
	        // print_r($money_won_data);exit;
		$common_user_id= array();
	        // foreach($money_won_data as $key=>$value){
		if(!empty($money_won_data) && !empty($result)){
			$common_user_id = array_intersect($money_won_data, $result);
		}
		elseif(!empty($result) && empty($money_won_data)){
			if($lists['money_won']['status']==0){
			$common_user_id =$result;
			}
			else{
			$common_user_id = array_intersect($money_won_data, $result);
			}
		}
		elseif(empty($result) && !empty($money_won_data)){
			if($lists['money_deposit']['status']==0){
			$common_user_id =$money_won_data;
			}
			else{
			$common_user_id = array_intersect($money_won_data, $result);
			}
		}
	        // }
		return $common_user_id;

	}
	private function get_point_redeem($lists){
	            // print_r($value);exit;
	            // $coin_redeem_data[$key]['user_base_list_id']= $value['user_base_list_id'];
		if(isset($lists['coin_redeem']) && $lists['coin_redeem']['status']==1){
			if($lists['coin_redeem']['min_value'] > 0 || $lists['coin_redeem']['max_value'] >0){
				$result= $this->db->select('O.user_id')
				->from(ORDER.' AS O')
				->join(USER.' AS U','O.user_id=U.user_id and U.is_systemuser=0','INNER')
				->where('U.status',1)
				->where('O.status',1)
				->where('O.source',146);
				// $list = json_decode($list['coin_redeem'],TRUE)[0];
			$this->db->having('sum(O.winning_amount) >=',$lists['coin_redeem']['min_value'])
			->having('sum(O.winning_amount) <=',$lists['coin_redeem']['max_value']);
			$this->db->group_by('O.user_id')
			->order_by("O.user_id","ASC");
			$result = $this->db->get()->result_array();
			}elseif($lists['coin_redeem']['min_value'] =='0' && $lists['coin_redeem']['max_value'] =='0'){
				$result = $this->db->select('U.user_id')
				->from(USER.' AS U')
				->join(ORDER.' AS O','O.user_id = U.user_id and O.status=1 and O.source=146','left')
				->where(["U.status"=>1,"U.is_systemuser"=>0])
				->where("O.order_id IS NULL")
				->group_by('U.user_id')
				->get()->result_array();
			}
			// echo $this->db->last_query();exit;
			$result = array_column($result, 'user_id');
		}
		else{
			$result = array();
		}
	        // echo $this->db->last_query();exit;

		return $result;
	}

	private function get_point_earn($lists){
		if(isset($lists['coin_earn']) &&  $lists['coin_earn']['status']==1){
			$result = $this->db->select('O.user_id')
			->from(ORDER.' AS O')
			->join(USER.' AS U','O.user_id=U.user_id and U.is_systemuser=0','INNER')
			->where('U.status',1)
			->where('O.status',1);
	                // $list = json_decode($vlaue['coin_earn'],TRUE)[0];
			$this->db->having('sum(O.points) >=',$lists['coin_earn']['min_value'])
			->having('sum(O.points) <=',$lists['coin_earn']['max_value']);
			$this->db->group_by('user_id');
			$result = $this->db->get()->result_array();
			$result= array_column($result, 'user_id');
		}
		else{
			$result = array();
		}
	        // echo $this->db->last_query();exit;
	        // }
		$redeem_data = $this->get_point_redeem($lists);
		$common_user_id= array();
	        // foreach($redeem_data as $key=>$value){
		if(!empty($redeem_data) && !empty($result)){
			$common_user_id = array_intersect($redeem_data, $result);
		}
		elseif(!empty($result) && empty($redeem_data)){
			if($lists['coin_redeem']['status']==0){
			$common_user_id =$result;
			}
			else{
				$common_user_id = array_intersect($redeem_data, $result);
			}
		}
		elseif(empty($result) && !empty($redeem_data)){
	                // $common_user_id[$key]['user_base_list_id'] = $value['user_base_list_id'];
			if($lists['coin_earn']['status']==0){
			$common_user_id =$redeem_data;
			}
			else{
				$common_user_id = array_intersect($redeem_data, $result);
			}
		}
	        // }
		return $common_user_id;
	}
	
	    //for admin created contest join lost
	private function get_acc_lost($lists){
		$this->db = $this->db_fantasy;
	                // print_r($value['admin_created_contest_lost']); exit;
	            // $ac_contest_lost_data[$key]['user_base_list_id']= $value['user_base_list_id'];
		if($lists['sport_preference']['status']==1 || $lists['admin_created_contest_lost']['status']==1){
			$result =$this->db->select("LM.user_id")
			->from(LINEUP_MASTER_CONTEST . " LMC")
			->join(LINEUP_MASTER . " LM", "LM.lineup_master_id=LMC.lineup_master_id", "INNER")
			->join(CONTEST.' C','C.contest_id=LMC.contest_id','INNER');

	             //admin created contest join
			if(isset($lists['sport_preference']) && $lists['sport_preference']['status']==1){
	                // $list = json_decode($value['sport_id'],TRUE)[0];
				$sports_id = array_column($lists['sport_preference']['sport_preference'],'id');
				$this->db->where_in('C.sports_id',$sports_id);    
			}
	         //admin created contest won
			if(isset($lists['admin_created_contest_lost']) &&  $lists['admin_created_contest_lost']['status']==1){
					// $list = json_decode($value['admin_created_contest_lost'],TRUE)[0];
					$this->db->where('C.contest_access_type',0);

					if($lists['admin_created_contest_lost']['min_value'] > 0 || $lists['admin_created_contest_lost']['max_value'] > 0){
						$this->db->where('LMC.is_winner',0)
						->having('count(LMC.is_winner) >=',$lists['admin_created_contest_lost']['min_value'])
						->having('count(LMC.is_winner) <=',$lists['admin_created_contest_lost']['max_value']);
					}elseif($lists['admin_created_contest_lost']['min_value'] =='0' && $lists['admin_created_contest_lost']['max_value']=='0'){
						$this->db->where('LMC.is_winner',1)
						->having('SUM(IF(LMC.is_winner = 0,1,0)) =',$lists['admin_created_contest_lost']['min_value']);
					}
			}

			$this->db->group_by('LM.user_id')
			->order_by("LM.user_id","ASC");
			$result = $this->db->get()->result_array();
	            // $ac_contest_lost_data[$key]['user_id'] = $result;
			$result = array_column($result, 'user_id');
		}else{
			$result = array();
		}
		return $result;
	}


	    //for admin created contest join and won
	private function get_acc_join_won($lists){

		if($lists['sport_preference']['status']==1 || $lists['admin_created_contest_join']['status']==1 || $lists['admin_created_contest_won']['status']==1){
			$this->db = $this->db_fantasy;
			$result = $this->db->select("LM.user_id")
			->from(LINEUP_MASTER_CONTEST . " LMC")
			->join(LINEUP_MASTER . " LM", "LM.lineup_master_id=LMC.lineup_master_id", "INNER")
			->join(CONTEST.' C','C.contest_id=LMC.contest_id','INNER');


			if(isset($lists['sport_preference']) && $lists['sport_preference']['status']==1){
	                // $list = json_decode($value['sport_id'],TRUE)[0];
				$sports_id = array_column($lists['sport_preference']['sport_preference'],'id');
				$this->db->where_in('C.sports_id',$sports_id);    
			}

			if($lists['admin_created_contest_join']['status']==1 || $lists['admin_created_contest_won']['status']==1){
				$this->db->where('C.contest_access_type',0);
			}
	            //admin created contest join
			if(isset($lists['admin_created_contest_join']) && $lists['admin_created_contest_join']['status']==1){
				if($lists['admin_created_contest_join']['min_value'] >0 || $lists['admin_created_contest_join']['max_value'] >0){
				$this->db->having('count(LM.user_id) >=',$lists['admin_created_contest_join']['min_value'])
				->having('count(LM.user_id) <=',$lists['admin_created_contest_join']['max_value']);
				}elseif($lists['admin_created_contest_join']['min_value'] =='0' && $lists['admin_created_contest_join']['max_value'] =='0'){
					$this->load->model('user/User_model');
					$user_user_ids = $this->User_model->get_user_user_ids();
					$all_user_ids =($user_user_ids)?array_column($user_user_ids,'user_id'):array();
					$this->db->where(['LMC.is_winner'=>'0','C.entry_fee'=>'0']);
				}
			}

	            //admin created contest won
	            // print_r($lists['admin_created_contest_won']['min_value']);exit;
			if(isset($lists['admin_created_contest_won']) && $lists['admin_created_contest_won']['status']==1){
					// $list = json_decode($lists['admin_created_contest_won'],TRUE)[0];
					if($lists['admin_created_contest_won']['min_value'] > 0 || $lists['admin_created_contest_won']['max_value'] > 0){
						$this->db->where('LMC.is_winner',1)
						->having('count(LMC.is_winner) >=',$lists['admin_created_contest_won']['min_value'])
						->having('count(LMC.is_winner) <=',$lists['admin_created_contest_won']['max_value']);
					}elseif($lists['admin_created_contest_won']['min_value'] =='0' && $lists['admin_created_contest_won']['max_value'] =='0')
					{
						$this->db->where('LMC.is_winner',0)
						->having('SUM(IF(LMC.is_winner = 1,1,0)) =',$lists['admin_created_contest_won']['min_value']);
					}
			}

			$this->db->group_by('LM.user_id')
			->order_by("LM.user_id","ASC");
			$pre_result = $this->db->get()->result_array();
	            // echo $this->db->last_query();exit;
			$pre_result = array_column($pre_result, 'user_id');
			if($lists['admin_created_contest_join']['min_value'] =='0' && $lists['admin_created_contest_join']['max_value'] =='0'){
				$result = array_diff($all_user_ids,$pre_result);
			}else{
				$result = $pre_result;
			}
		}
		else{
			$result = array();
		}


		$contest_lost = $this->get_acc_lost($lists);
		$common_user_id= array();

	        // foreach($contest_lost as $key=>$value){
		if(!empty($contest_lost) && !empty($result)){
			$common_user_id = array_intersect($contest_lost, $result);
		}
		elseif(!empty($result) && empty($contest_lost)){
			if($lists['admin_created_contest_lost']['status']==0){
			$common_user_id =$result;
			}
			else{
				$common_user_id = array_intersect($contest_lost, $result);
			}
		}
		elseif(empty($result) && !empty($contest_lost)){
	                // $common_user_id[$key]['user_base_list_id'] = $value['user_base_list_id'];
			if($lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0){
			$common_user_id =$contest_lost;
			}
			else{
				$common_user_id = array_intersect($contest_lost, $result);
			}
		}
	        // }
	            // print_r($common_user_id);exit;
		return $common_user_id;

	}

	//for admin PRIVATE contest join lost
	private function get_private_contest_lost($lists){

		if($lists['sport_preference']['status']==1 || $lists['private_contest_lost']['status']==1){
			$this->db = $this->db_fantasy;
			$result = $this->db->select("LM.user_id")
			->from(LINEUP_MASTER_CONTEST . " LMC")
			->join(LINEUP_MASTER . " LM", "LM.lineup_master_id=LMC.lineup_master_id", "INNER")
			->join(CONTEST.' C','C.contest_id=LMC.contest_id','INNER');


	             //admin created contest join
			if(isset($lists['sport_preference']) && $lists['sport_preference']['status']==1){
	                // $list = json_decode($value['sport_id'],TRUE)[0];
				$sports_id = array_column($lists['sport_preference']['sport_preference'],'id');
				$this->db->where_in('C.sports_id',$sports_id);    
			}
	         //admin created contest won
			if(isset($lists['private_contest_lost']) && $lists['private_contest_lost']['status']==1){
					// $list = json_decode($value['private_contest_lost'],TRUE)[0];
					$this->db->where('C.contest_access_type',1);

					if($lists['private_contest_lost']['min_value'] > 0 || $lists['private_contest_lost']['max_value'] > 0){
						$this->db->where('LMC.is_winner',0)
						->having('count(LMC.is_winner) >=',$lists['private_contest_lost']['min_value'])
						->having('count(LMC.is_winner) <=',$lists['private_contest_lost']['max_value']);
					} elseif($lists['private_contest_lost']['min_value'] == '0' &&  $lists['private_contest_lost']['max_value'] =='0'){
						$this->db->where('LMC.is_winner',1)
						->having('SUM(IF(LMC.is_winner = 0,1,0)) =',$lists['private_contest_lost']['min_value']);
					}
			}

			$this->db->group_by('LM.user_id')
			->order_by("LM.user_id","ASC");
			$result = $this->db->get()->result_array();
			$result = array_column($result, 'user_id');
	            // echo $this->db->last_query();exit;
		}
		else{
			$result = array();
		}

	    // print_r($private_contest_lost_data);exit;
		return $result;
	}

	//for admin private_contest join and won
	private function get_private_contest_join_won($lists){
		$this->db = $this->db_fantasy;
	                // print_r($value);exit;
	            // $private_contest_data[$key]['user_base_list_id']= $value['user_base_list_id'];
		if($lists['sport_preference']['status']==1 || $lists['private_contest_join']['status']==1 || $lists['private_contest_won']['status']==1){
			$result = $this->db->select("LM.user_id")
			->from(LINEUP_MASTER_CONTEST . " LMC")
			->join(LINEUP_MASTER . " LM", "LM.lineup_master_id=LMC.lineup_master_id", "INNER")
			->join(CONTEST.' C','C.contest_id=LMC.contest_id',"INNER");

			if(isset($lists['sport_preference']) && $lists['sport_preference']['status']==1){
	                // $list = json_decode($value['sport_id'],TRUE)[0];
				$sports_id = array_column($lists['sport_preference']['sport_preference'],'id');
				$this->db->where_in('C.sports_id',$sports_id);    
			}
			if($lists['private_contest_join']['status']==1 || $lists['private_contest_won']['status']==1){
				$this->db->where('C.contest_access_type',1);
			}
	            //admin created contest join
			if(isset($lists['private_contest_join']) && $lists['private_contest_join']['status']==1){
				if($lists['private_contest_join']['min_value'] > 0 || $lists['private_contest_join']['max_value'] >0){
				$this->db->having('count(LM.user_id) >=',$lists['private_contest_join']['min_value'])
				->having('count(LM.user_id) <=',$lists['private_contest_join']['max_value']);
				}elseif($lists['private_contest_join']['min_value'] =='0' && $lists['private_contest_join']['max_value'] =='0'){
					$this->load->model('user/User_model');
					$user_user_ids = $this->User_model->get_user_user_ids();
					$all_user_ids =($user_user_ids)?array_column($user_user_ids,'user_id'):array();
					$this->db->where(['LMC.is_winner'=>'0','C.entry_fee'=>'0']);
				}
			}

	            //admin created contest won
			if(isset($lists['private_contest_won']) &&  $lists['private_contest_won']['status']==1){
				if($lists['private_contest_won']['min_value'] > 0 || $lists['private_contest_won']['max_value'] > 0){
				$this->db->where('LMC.is_winner',1)
				->having('count(LMC.is_winner) >=',$lists['private_contest_won']['min_value'])
				->having('count(LMC.is_winner) <=',$lists['private_contest_won']['max_value']);
				}elseif($lists['private_contest_won']['min_value'] =='0' && $lists['private_contest_won']['max_value'] =='0'){
				$this->db->where('LMC.is_winner',0)
				->having('SUM(IF(LMC.is_winner = 1,1,0)) >=',$lists['private_contest_won']['min_value']);
				}
			}

			$this->db->group_by('LM.user_id')
			->order_by("LM.user_id","ASC");
			$pre_result = $this->db->get()->result_array();
			$pre_result = array_column($pre_result, 'user_id');
			if($lists['private_contest_join']['min_value'] =='0' && $lists['private_contest_join']['max_value'] =='0'){
				$result = array_diff($all_user_ids,$pre_result);
			}else{
				$result = $pre_result;
			}
	            // echo $this->db->last_query();exit;
		}
		else{
			$result = array();
		}
		$private_contest_lost = $this->get_private_contest_lost($lists);
		$common_user_id= array();
		if(!empty($private_contest_lost) && !empty($result)){
	                // $common_user_id[$key]['user_base_list_id'] = $value['user_base_list_id'];
			$common_user_id = array_intersect($private_contest_lost, $result);
		}
		elseif(!empty($result) && empty($private_contest_lost)){
	                // $common_user_id[$key]['user_base_list_id'] = $value['user_base_list_id'];
			if($lists['private_contest_lost']['status']==0){
			$common_user_id =$result;
			}
			else{
			$common_user_id = array_intersect($private_contest_lost, $result);
			}
		}
		elseif(empty($result) && !empty($private_contest_lost)){
	                // $common_user_id[$key]['user_base_list_id'] = $value['user_base_list_id'];
			if($lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0){
			$common_user_id =$private_contest_lost;
			}
			else{
				$common_user_id = array_intersect($private_contest_lost, $result);
			}
		}
	            // print_r($common_user_id);exit;
		return $common_user_id;
	}

	public function get_user_count($lists){
		$referral_user_id = $this->get_referral_user($lists);
		$user_data = $this->get_user_data($lists);
		$money_deposit_user_id = $this->get_money_deposit($lists);
		$coin_earn_user_id = $this->get_point_earn($lists);
        $ac_contest_data = $this->get_acc_join_won($lists);//checked with user : 21,31 on 192.168.0.202
        $private_contest_data = $this->get_private_contest_join_won($lists);// checked on user 14
		// print_r($lists);exit;

		        // print_r($ac_contest_data);exit;
		        $coin_contest=array();
		        // foreach($coin_earn_user_id as $key=>$value){
		        if(!empty($coin_earn_user_id) && !empty($ac_contest_data) && !empty($private_contest_data)){
		        	$coin_contest = array_intersect($coin_earn_user_id, $ac_contest_data,$private_contest_data);
		        }
		        elseif(empty($coin_earn_user_id) && !empty($ac_contest_data) && !empty($private_contest_data)){
		        	if($lists['coin_earn']['status']==0){
		        	$coin_contest = array_intersect($ac_contest_data,$private_contest_data);
			        }else{
		        	$coin_contest = array_intersect($ac_contest_data,$private_contest_data,$coin_earn_user_id);
			        }
		        }
		        elseif(!empty($coin_earn_user_id) && empty($ac_contest_data) && !empty($private_contest_data)){
		        	if($lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0 && $lists['admin_created_contest_lost']['status']==0){
		        	$coin_contest = array_intersect($coin_earn_user_id,$private_contest_data);
			        }
			        else{
		        	$coin_contest = array_intersect($ac_contest_data,$private_contest_data,$coin_earn_user_id);
			        }
		        }
		        elseif(!empty($coin_earn_user_id) && !empty($ac_contest_data) && empty($private_contest_data)){
		        	if($lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0 && $lists['private_contest_lost']['status']==0){
		        	$coin_contest = array_intersect($coin_earn_user_id,$ac_contest_data);
		        	}
		        	else{
		        	$coin_contest = array_intersect($ac_contest_data,$private_contest_data,$coin_earn_user_id);
			        }
		        }
		        elseif(empty($coin_earn_user_id) && empty($ac_contest_data) && !empty($private_contest_data)){
		        	if($lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0 && $lists['admin_created_contest_lost']['status']==0 && $lists['coin_earn']['status']==0){
		        	$coin_contest = $private_contest_data;
			        }
			        elseif($lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0 && $lists['admin_created_contest_lost']['status']==0 && $lists['coin_earn']['status']==1){
			        	$coin_contest = array_intersect($private_contest_data,$coin_earn_user_id);
			        }
			        elseif(($lists['admin_created_contest_join']['status']==1 || $lists['admin_created_contest_won']['status']==1 || $lists['admin_created_contest_lost']['status']==1) && $lists['coin_earn']['status']==0){
			        	$coin_contest = array_intersect($ac_contest_data,$private_contest_data);
			        }
			        else{
		        	$coin_contest = array_intersect($ac_contest_data,$private_contest_data,$coin_earn_user_id);
			        }
		        }
		        elseif(!empty($coin_earn_user_id) && empty($ac_contest_data) && empty($private_contest_data)){
		        	if($lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0 && $lists['admin_created_contest_lost']['status']==0 && $lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0 && $lists['private_contest_lost']['status']==0){
		        	$coin_contest = $coin_earn_user_id;
			        }
			        elseif($lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0 && $lists['admin_created_contest_lost']['status']==0 && ($lists['private_contest_join']['status']==1 || $lists['private_contest_won']['status']==1 || $lists['private_contest_lost']['status']==1)){
			        	$coin_contest = array_intersect($private_contest_data,$coin_earn_user_id);
			        }
			        elseif(($lists['admin_created_contest_join']['status']==1 || $lists['admin_created_contest_won']['status']==1 || $lists['admin_created_contest_lost']['status']==1) && ($lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0 && $lists['private_contest_lost']['status']==0)){
			        	$coin_contest = array_intersect($ac_contest_data,$coin_earn_user_id);
			        }
			        else{
		        	$coin_contest = array_intersect($ac_contest_data,$private_contest_data,$coin_earn_user_id);
			        }
		        }
		        elseif(empty($coin_earn_user_id) && !empty($ac_contest_data) && empty($private_contest_data)){
		        	if($lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0 && $lists['private_contest_lost']['status']==0 && $lists['coin_earn']['status']==0){
		        	$coin_contest = $ac_contest_data;
			        }
			        elseif($lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0 && $lists['private_contest_lost']['status']==0 && $lists['coin_earn']['status']==1){
			        	$coin_contest = array_intersect($ac_contest_data,$coin_earn_user_id);
			        }
			        elseif(($lists['private_contest_join']['status']==1 || $lists['private_contest_won']['status']==1 || $lists['private_contest_lost']['status']==1) && $lists['coin_earn']['status']==0){
			        	$coin_contest = array_intersect($ac_contest_data,$private_contest_data);
			        }
			        else{
		        	$coin_contest = array_intersect($ac_contest_data,$private_contest_data,$coin_earn_user_id);
			        }
		        	$coin_contest = $ac_contest_data;
		        }
		        // }
		        // print_r($coin_contest);exit;  //tested with user id 21

		        $user_referral_money=array();
		        // foreach($user_data as $key=>$value){
		        if(!empty($user_data) && !empty($referral_user_id) && !empty($money_deposit_user_id)){
		        	$user_referral_money = array_intersect($user_data, $referral_user_id,$money_deposit_user_id);
		        }
		        elseif(empty($user_data) && !empty($referral_user_id) && !empty($money_deposit_user_id)){
		        	if($lists['age_group']['status']==0 && $lists['profile_status']['status']==0 && $lists['location']['status']==0 && $lists['gender']['status']==0){
			        	$user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id);
				        }
				        else{
				        $user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id,$user_data);	
				        }
		        }
		        elseif(!empty($user_data) && empty($referral_user_id) && !empty($money_deposit_user_id)){
		        	if($lists['referral']['status']==0){
		        	$user_referral_money = array_intersect($user_data,$money_deposit_user_id);
		        	}
		        	else{
		        		$user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id,$user_data);	
		        	}
		        }
		        elseif(!empty($user_data) && !empty($referral_user_id) && empty($money_deposit_user_id)){
		        	if($lists['money_deposit']['status']==0 && $lists['money_won']['status']==0){
		        	$user_referral_money = array_intersect($user_data,$referral_user_id);
		        	}
		        	else{
	        		$user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id,$user_data);	
		        	}
		        }
		        elseif(empty($user_data) && empty($referral_user_id) && !empty($money_deposit_user_id)){
		        	if($lists['age_group']['status']==0 && $lists['profile_status']['status']==0 && $lists['location']['status']==0 && $lists['gender']['status']==0 && $lists['referral']['status']==0){
			        	$user_referral_money = $money_deposit_user_id;
				        }
				        elseif($lists['age_group']['status']==0 && $lists['profile_status']['status']==0 && $lists['location']['status']==0 && $lists['gender']['status']==0 && $lists['referral']['status']==1){
					        $user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id);		
				        }
				        elseif(($lists['age_group']['status']==1 || $lists['profile_status']['status']==1 || $lists['location']['status']==1 || $lists['gender']['status']==1) && $lists['referral']['status']==0){
					        $user_referral_money = array_intersect($user_data,$money_deposit_user_id);		
				        }
				        else{
				        $user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id,$user_data);	
				        }
		        }
		        elseif(!empty($user_data) && empty($referral_user_id) && empty($money_deposit_user_id)){
		        	if($lists['money_deposit']['status']==0 && $lists['money_won']['status']==0 && $lists['referral']['status']==0){
		        	$user_referral_money = $user_data;
		        	}
		        	elseif($lists['money_deposit']['status']==0 && $lists['money_won']['status']==0 && $lists['referral']['status']==1){
					        $user_referral_money = array_intersect($referral_user_id,$user_data);		
				        }
				        elseif(($lists['money_deposit']['status']==1 || $lists['money_won']['status']==1) && $lists['referral']['status']==0){
					        $user_referral_money = array_intersect($user_data,$money_deposit_user_id);		
				        }
		        	else{
	        		$user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id,$user_data);	
		        	}
		        }
		        elseif(empty($user_data) && !empty($referral_user_id) && empty($money_deposit_user_id)){
		        	if($lists['money_deposit']['status']==0 && $lists['money_won']['status']==0 && $lists['age_group']['status']==0 && $lists['profile_status']['status']==0 && $lists['location']['status']==0 && $lists['gender']['status']==0){
		        	$user_referral_money = $referral_user_id;
		        	}
		        	elseif(($lists['money_deposit']['status']==0 && $lists['money_won']['status']==0) && ($lists['age_group']['status']==1 || $lists['profile_status']['status']==1 || $lists['location']['status']==1 || $lists['gender']['status']==1)){
					        $user_referral_money = array_intersect($referral_user_id,$user_data);		
				        }
				        elseif(($lists['money_deposit']['status']==1 || $lists['money_won']['status']==1) && ($lists['age_group']['status']==0 && $lists['profile_status']['status']==0 && $lists['location']['status']==0 && $lists['gender']['status']==0)){
					        $user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id);		
				        }
		        	else{
	        		$user_referral_money = array_intersect($referral_user_id,$money_deposit_user_id,$user_data);	
		        	}
		        }
		        // }
		        // print_r($user_referral_money);exit; //tested with user id 39

		        $final_user_list = array();
		        if(!empty($coin_contest) && !empty($user_referral_money)){
		        	$final_user_list = array_intersect($user_referral_money, $coin_contest);
		        }
		        elseif(!empty($user_referral_money) && empty($coin_contest)){
		        	if($lists['coin_earn']['status']==0 && $lists['admin_created_contest_join']['status']==0 && $lists['admin_created_contest_won']['status']==0 && $lists['admin_created_contest_lost']['status']==0 && $lists['private_contest_join']['status']==0 && $lists['private_contest_won']['status']==0 && $lists['private_contest_lost']['status']==0){
		        	$final_user_list =$user_referral_money;
		        	}
		        	else{
		        		$final_user_list = array_intersect($user_referral_money, $coin_contest);
		        	}
		        }
		        elseif(empty($user_referral_money) && !empty($coin_contest)){
			        	if($lists['age_group']['status']==0 && $lists['profile_status']['status']==0 && $lists['location']['status']==0 && $lists['gender']['status']==0 && $lists['referral']['status']==0 && $lists['money_deposit']['status']==0 && $lists['money_won']['status']==0){
			        		$final_user_list =$coin_contest;
			        	}else{
			        		$final_user_list = array_intersect($user_referral_money, $coin_contest);
			        	}
		        }
		        	$user_collection=array();
		        if(!empty($final_user_list)){
		        	$user_collection['user_count'] = count($final_user_list);
		        	$user_collection['user_ids'] = $final_user_list;
		        	return $user_collection;
		        }
		        else{
		        	$user_collection['user_ids'] = array();
		        	$user_collection['user_count'] = count($final_user_list);
		        	return $user_collection;
		        }

		    }


		    public function get_template_category($category_id=''){

				$this->config->load('cd_config');
				$exclude_categories = $this->config->item('exclude_categories');
		    	$category = $this->db->select('*')
		    	->from(CD_EMAIL_CATEGORY.' EC')
		    	->where_not_in('category_id',$exclude_categories);
		    	if($category_id!=''){
		    		$this->db->where('category_id',$category_id);
		    	}
		    	$category = $this->db->where('status',1)
		    	->get()->result_array();
		    	// echo $this->db->last_query();exit;
		    	return $category;
		    }

		    public function create_new_category($post_data){
		    	$data['category_name'] =$post_data['category_name'] ;
		    	$data['status'] = '1';
		    	$data['added_date'] = format_date();
		    	$this->db->insert(CD_EMAIL_CATEGORY,$data);
		    	return $this->db->insert_id();
		    }

		    public function category_availablity($category_name){
		    	$result = $this->db->select('category_name')
		    	->where('category_name',$category_name)
		    	->get(CD_EMAIL_CATEGORY)->num_rows();
		    	return $result;
		    }

		    public function template_availablity($category_name){
		    	$result = $this->db->select('template_name')
		    	->where('template_name',$category_name)
		    	->get(CD_EMAIL_CATEGORY)->num_rows();
		    	return $result;
		    }

		    public function create_new_template($post_data){
		    	$this->db->insert(CD_EMAIL_TEMPLATE,$post_data);
		    	return $this->db->insert_id();
		    }

		    public function get_custome_template($post_data=''){
		// print_r($post_data);exit;
		    	$template = $this->db->select('*')
		    	->from(CD_EMAIL_TEMPLATE.' ET');
		    	if(isset($post_data['template_id']) &&$post_data['template_id']!=''){
		    		$this->db->where('cd_email_template_id',$post_data['template_id']);
		    	}
		    	if(isset($post_data['category_id']) &&$post_data['category_id']!=''){
		    		$this->db->where('category_id',$post_data['category_id']);
		    	}
		    	if(isset($post_data['message_type']) &&$post_data['message_type']!=''){
		    		$this->db->where_in('message_type',[$post_data['message_type'],0]);
				}
				
				$this->db->where_not_in('notification_type',array(127,128,129));
		    	$template = $this->db->where('status',1)
		    	->get()->result_array();
				// echo $this->db->last_query();exit;
		    	return $template;
		    }

		   public function temp_update_template_email_body($post_data){
				// print_r($post_data);exit;
				$template_id = $post_data['cd_email_template_id'];
				$email_body = $post_data['email_body'];
				$this->db->where('cd_email_template_id',$template_id)
				->update(CD_EMAIL_TEMPLATE,$post_data);
			}
			public function get_promocode_detail($promocode_id){
				$result = $this->db->select('*')
				->from(PROMO_CODE.' AS PC')
				->where('status','1')
				->where('promo_code_id',$promocode_id)
				->get()->row_array();
				return $result;
			}
			public function get_all_contest($sports_id=''){
				$result = $this->db_fantasy->select('CM.collection_name,CM.season_scheduled_date,C.contest_id,C.status,C.contest_unique_id,C.contest_template_id,C.collection_master_id,C.league_id,C.group_id,C.season_scheduled_date,C.minimum_size,C.size,C.contest_name,C.currency_type,C.entry_fee,C.prize_pool,C.total_user_joined,C.prize_distibution_detail,C.multiple_lineup,C.prize_type, C.guaranteed_prize,C.is_auto_recurring,C.is_pin_contest,MG.group_name,C.total_system_user,C.sponsor_name,C.sponsor_logo,C.sponsor_link,C.video_link', FALSE)
			->from(CONTEST." AS C")
			->join(MASTER_GROUP." AS MG", 'MG.group_id = C.group_id','INNER')
			->join(COLLECTION_MASTER." AS CM", 'CM.collection_master_id = C.collection_master_id','INNER');
			if($sports_id!=''){
			$this->db_fantasy->where('C.sports_id',$sports_id);
			}
			$result = $this->db_fantasy->where('C.status',0)
			->get()->result_array();
			return $result;
			}

			public function get_fixtureinfo_by_sguid($season_game_uid=''){
				$result = $this->db_fantasy->select('CM.collection_master_id,CM.collection_name,S.delay_minute,S.season_scheduled_date',FALSE)
				->from(SEASON.' S')
				->join(COLLECTION_SEASON.' CS','CS.season_id=S.season_id','INNER')
				->join(COLLECTION_MASTER.' CM','CS.collection_master_id=CM.collection_master_id AND CM.league_id=S.league_id','INNER')
				->where('S.season_game_uid',$season_game_uid)
				->get()->result_array();
				return $result;
			}

		

			public function update_sms_template($post_data,$condition){

		    	$this->db->where($condition);
		    	$this->db->update(SMS_TEMPLATE,$post_data);
		    	return $this->db->affected_rows();
		    }

			

			

		public function get_sms_template_list($post_data=''){
				
			$template = $this->db->select('ST.*')
			->from(SMS_TEMPLATE.' ST');
			
			if(isset($post_data['notification_types']) ){
				$this->db->where_in('ST.reference_id',$post_data['notification_types']);
			}
			$template = $this->db->where('ST.status',1)->where('ST.module_type',0)
			->get()->result_array();
			// echo $this->db->last_query();exit;
			return $template;
		}			

		public function get_scheduled_data($post_data)
		{
			$result = $this->db->select('custom_data')
			->from(CD_RECENT_COMMUNICATION)
			->where('recent_communication_id',$post_data['recent_communication_id'])
			->get()->row_array();
			return $result;
		}

}