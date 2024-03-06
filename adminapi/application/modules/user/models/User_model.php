<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database('user_db');
	}

	public function get_users_by_ids($user_ids)
    {
        if(empty($user_ids))
        {
            return array();
        }
        $this->db->select("user_id,user_unique_id,email,phone_no,user_name,IFNULL(image,'') as image",FALSE)
                ->from(USER)
                ->where_in('user_id', $user_ids);
        $result = $this->db->get()->result_array();
        return $result;
    }

	/**
	 * [get_all_site_user_detail description]
	 * @MethodName get_all_site_user_detail
	 * @Summary This function used for get all user list and return filter user list 
	 * @param      boolean  [User List or Return Only Count]
	 * @return     [type]
	 */
	public function get_all_user($count_only=FALSE)
	{  	
		$allow_bank_flow = isset($this->app_config['allow_bank_flow'])?$this->app_config['allow_bank_flow']['key_value']:0; 
		$sort_field	= 'added_date';
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
		}

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('city','user_name','email','status','pan_verified')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
	   
		$offset	= $limit * $page;
		$sql = $this->db->select("SUM(UT.net_winning) as net_winning,U.dob,U.pan_verified,U.auto_pan_attempted,IFNULL(U.is_bank_verified,0) AS is_bank_verified,U.auto_bank_attempted,U.phone_verfied,U.email_verified,U.is_flag,NULL as bank_document,U.user_unique_id, U.user_id,U.first_name,U.last_name,U.email,U.status,U.user_name,U.phone_no,IFNULL(U.pan_image,'') as pan_image,U.pan_no,U.pan_rejected_reason,city,U.user_unique_id,U.aadhar_status,U.wdl_status",FALSE)
				->from(USER.' AS U')
				->join(USER_TDS_REPORT.' AS UT','UT.user_id=U.user_id',"left")
				->where("U.is_systemuser","0")
				->order_by('U.'.$sort_field, $sort_order);
		
		if(isset($post_data['status']) && in_array($post_data['status'],[0,1,2,3]))
		{	
			if($post_data['status']==3){	
				$post_data['status'] = 1;	
				$this->db->where("U.wdl_status",2);	
			}
			$this->db->where("U.status",$post_data['status']);
		}
		//var_dump($post_data['keyword']); die('ddd');
		if(isset($post_data['is_flag']) && (int)$post_data['is_flag']==1 && $post_data['keyword'] == "")
		{ 
			$this->db->where("U.is_flag",$post_data['is_flag']);
		}

		if(isset($post_data['is_flag']) && (int)$post_data['is_flag']== 0 && isset($post_data['pending_pan_approval']) && (int)$post_data['pending_pan_approval'] == 0)
		{ 
			if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != ''  && $post_data['keyword'] == "")
			{
				$this->db->where("DATE_FORMAT(U.added_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(U.added_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
			}
	    }
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name),IFNULL(U.pan_no,"")))', strtolower($post_data['keyword']) );
		}
		

		$doc_where_str = "";
		if($allow_bank_flow == "1"){
			$this->db->select("UBD.bank_document,UBD.type");
			$this->db->join(USER_BANK_DETAIL.' AS UBD','UBD.user_id=U.user_id',"left");
			$doc_where_str = "AND UBD.bank_document IS NOT NULL ";

		}

		$allow_aadhar = isset($this->app_config['allow_aadhar'])?$this->app_config['allow_aadhar']['key_value']:0; 

		$this->db->select("UA.name as aadhar_name,UA.aadhar_number,UA.front_image as aadhar_front_image,UA.back_image as aadhar_back_image");
		$this->db->join(USER_AADHAR.' AS UA','UA.user_id=U.user_id',"left");


		//  var_dump( $post_data['pending_pan_approval']);die;

		if(isset($post_data['pending_pan_approval']) && $post_data['pending_pan_approval'] == '1' && $post_data['keyword'] == "")
		{
           if ($allow_aadhar) {
			
			   $allow_adhar_str = "OR (UA.aadhar_number IS NOT NULL AND UA.name IS NOT NULL AND U.aadhar_status=0)" ;
		   }else{
			   $allow_adhar_str ='';
		   }
			
			$this->db->where("(
				(U.pan_no IS NOT NULL AND U.pan_image IS NOT NULL AND `pan_verified` =0)
			OR 			
			((U.is_bank_verified IS NULL OR U.is_bank_verified =0) ".$doc_where_str.") 
			".$allow_adhar_str." )");		
		}


		
		$tempdb = clone $this->db; //to get rows for pagination
		$tempdb = $tempdb->select("count(*) as total");
		$temp_q = $tempdb->get()->row_array();
		$total = isset($temp_q['total']) ? $temp_q['total'] : 0; 

		$result = array();
		if(!$count_only){
			$sql = $this->db->group_by("U.user_id")
							->limit($limit,$offset)
							->get();
			$result	= $sql->result_array();
			$result=($result)?$result:array();
		}

		// echo $this->db->last_query(); die;
		return array('result'=>$result,'total'=>$total);
	}

	/**
	 * [get_user_basic_by_id description]
	 * @MethodName get_user_basic_by_id
	 * @Summary This function used for get user basic info
	 * @param      [int]  [User Id]
	 * @return     [array]
	 */
	public function get_user_basic_by_id($user_id)
	{
       
		$result = $this->db->select("U.cb_balance,U.otp_attempt_count,U.added_date,U.pan_no,UBD.bank_document,U.is_bank_verified,U.auto_bank_attempted,U.is_flag,U.status,U.address,U.user_unique_id,U.user_id,U.first_name,U.last_name,U.image,U.balance,U.bonus_balance,U.winning_balance,U.email,U.pan_verified,U.auto_pan_attempted,U.phone_no,U.phone_code,U.user_name,U.point_balance,IFNULL(U.bs_status,0) AS bs_status,UBD.type,U.wdl_status",FALSE)
							->from(USER." AS U")
							->join(USER_BANK_DETAIL.' AS UBD','UBD.user_id=U.user_id',"left")
							->where("U.user_unique_id",$user_id)
							->get()->row_array();

							if($result['otp_attempt_count'] < WRONG_OTP_LIMIT){
								$result['otp_attempt_count']=0;
								}else{
								$result['otp_attempt_count']=1;
								}
							

		$number = $this->get_profit($result['user_id']);				
							$number = round($number,2,PHP_ROUND_HALF_UP);
												$length = strlen($number);
												switch($length){
													case 7:
														$result['total_profit'] = substr($number,-7,1).','.substr($number,1);
														break;
													case 8:
														$result['total_profit'] = substr($number,-8,2).','.substr($number,2);
														break;
													case 9:
														$result['total_profit'] = substr($number,-9,1).','.substr($number,-8,2).','.substr($number,3);
														break;
													case 10:
														$result['total_profit'] = substr($number,-10,2).','.substr($number,-8,2).','.substr($number,4);
														break;
													case 11:
														$result['total_profit'] = substr($number,-11,1).','.substr($number,-10,2).','.substr($number,-8,2).','.substr($number,5);
														break;
													case 12:
														$result['total_profit'] = substr($number,-12,2).','.substr($number,-10,2).','.substr($number,-8,2).','.substr($number,6);
														break;
												}
				
		return ($result)?$result:array();
	}

	/**
	 * [get_user_detail_by_id description]
	 * @MethodName get_user_detail_by_id
	 * @Summary This function used for get user Detail
	 * @param      [int]  [User Id]
	 * @return     [array]
	 */
	public function get_user_detail_by_id($user_id)
	{
		// DATE_FORMAT(U.added_date,'".MYSQL_DATE_FORMAT."') AS member_since
		$result = $this->db->select("U.email_verified,UBD.bank_document,U.is_bank_verified,U.aadhar_status,U.auto_bank_attempted,U.master_state_id,U.master_country_id,U.facebook_id,U.address,U.user_unique_id,U.user_id,U.first_name,U.last_name,U.image,U.balance,U.bonus_balance,U.winning_balance,U.point_balance,U.email,DATE_FORMAT(U.dob,'%d-%b-%Y') as dob,U.city,U.language,U.status,U.added_date,U.user_name,IFNULL(U.zip_code,'--') As zip_code,IFNULL(U.phone_no,'--') AS phone_no,IFNULL(U.gender,'--') As gender,U.phone_verfied,U.pan_verified,U.auto_pan_attempted,U.last_login,U.last_active,MC.country_name,MS.name as state_name,IFNULL(U.pan_image,'') as pan_image,U.pan_no,U.pan_rejected_reason,UA.name as aadhar_name,UA.aadhar_number,UA.front_image as aadhar_front_image,UA.back_image as aadhar_back_image",FALSE)
						->from(USER." AS U")
						->join(USER_BANK_DETAIL.' AS UBD','UBD.user_id=U.user_id','left')
						->join(USER_AADHAR.' AS UA','UA.user_id=U.user_id','left')
						->join(MASTER_COUNTRY." AS MC","MC.master_country_id=  U.master_country_id","left")
						->join(MASTER_STATE." AS MS","MS.master_state_id=  U.master_state_id","left")
						->where("U.user_unique_id",$user_id)
						->get()->row_array();
						//echo $this->db->last_query();die;
		return ($result)?$result:array();
	}

	public function get_user_pending_withdrawal_request()
	{
		$sort_field = 'ORD.date_added';
        $sort_order = 'DESC';
        $limit      = 10;
        $page       = 0;
        $offset 	= $limit * $page;
		$post_data  = $this->input->post();

		$this->db->select('USR.user_unique_id, USR.user_id, TXN.transaction_id, TXN.txn_id, TXN.address, TXN.withdraw_type, TXN.payment_gateway_id, TXN.transaction_message, TXN.transaction_status as status, ORD.status as order_status, 
                ORD.order_unique_id, ORD.order_id, ORD.type, ORD.source, ORD.source_id, ORD.real_amount, ORD.bonus_amount, ORD.winning_amount,
                ORD.custom_data, ORD.plateform, 
                DATE_FORMAT(ORD.date_added,"'.MYSQL_DATE_TIME_FORMAT.'") as order_date_added,
                DATE_FORMAT(ORD.modified_date,"'.MYSQL_DATE_TIME_FORMAT.'") as order_modified_date,           
                USR.phone_no,USR.user_name,CONCAT(USR.first_name," ",USR.last_name) AS full_name,USR.address as user_address,USR.user_unique_id,USR.pan_verified,USR.email,USR.total_deposit, USR.pan_no, USR.winning_balance,UBD.bank_name,UBD.ac_number,UBD.ifsc_code,UBD.micr_code,UBD.upi_id,ORD.tds',FALSE);

		$txn_join = "LEFT";
        $this->db->from(ORDER." AS ORD")
                ->join(TRANSACTION. " TXN","TXN.order_id = ORD.order_id", $txn_join)
                ->join(USER_BANK_DETAIL." AS UBD","UBD.user_id = ORD.user_id and ORD.withdraw_method = 1","LEFT")
                ->join(USER." AS USR","USR.user_id = ORD.user_id")
                ->where("ORD.type", 1)
                ->where("ORD.source", 8)
                ->where("ORD.user_id", $post_data['user_id'])
                ->where("ORD.status", 0)
                ->order_by($sort_field, $sort_order);

        $tempdb = clone $this->db;
        $total = 0;

        $query = $this->db->get();
        $total = $query->num_rows();
        $tempdb->limit($limit,$offset);
        $sql = $tempdb->get();
        $result = $sql->row_array();

        return array('result'=>$result,'total'=>$total);
	}

	public function get_earned_and_redeem_coins($user_id)
	{
		$result= $this->db->select('SUM(points) as coin_sums,type',FALSE)
		->from(ORDER)
		->where("user_id",$user_id)
		->where("points>",0)
		->where("source>",0)
		->group_by('type')
		->get()->result_array();

		return $result;
	}

	/**
	 * [user_transaction_history description]
	 * @MethodName user_transaction_history
	 * @Summary This function used for get all transaction history
	 * @return     [array]
	 */
	public function user_transaction_history($api_data = array())
	{
		$post_data	= $this->input->post();
		$sort_pw_order = SORT_DESC;
		$sort_pw_field = 'ORD.order_id';

		$user_id = $post_data['user_id'];
		$page_no = 1;
        $limit = 10;
        $offset = 0;
		if(isset($post_data["current_page"])) {
			
			$page_no = $post_data["current_page"];
		}
		if(isset($post_data["items_perpage"])) {
			$limit = $post_data["items_perpage"];
			$offset = ($page_no - 1) * $limit;
		}
		
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array("status","trans_desc","real_amount","bonus_amount","type","date_added")))
		{
			$sort_pw_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
			$sort_pw_order = $post_data['sort_order'] == 'DESC' ? SORT_DESC:SORT_ASC ;
		}

		//REF.refferal_id
		$this->db->select('ORD.date_added,ORD.order_id,ORD.type,ORD.source,ORD.source_id,IFNULL(ORD.cb_amount+ORD.real_amount, 0) as real_amount,ORD.bonus_amount,ORD.winning_amount,
		ORD.winning_amount,ORD.status,ORD.plateform,ORD.order_unique_id as transaction_id,CONCAT(USR.first_name," ",USR.last_name) as friend_name,ORD.points,ORD.reason,ORD.custom_data,ORD.reference_id,IFNULL(ORD.real_amount, 0) as r_amount,IFNULL(ORD.cb_amount, 0) as cb_amount',FALSE)
				->from(ORDER." AS ORD")
				->join(TRANSACTION." AS TXN","TXN.transaction_id = ORD.source_id and ORD.source IN (7,8)","LEFT")
				//->join(REFFERAL." AS REF","REF.refferal_id = ORD.source_id and ORD.source = 4","LEFT")
				->join(USER." AS USR","USR.user_id = ORD.user_id","LEFT")
				->where('ORD.user_id',$user_id);
			
				if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
				{ 
					$this->db->where("DATE_FORMAT(ORD.date_added, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(ORD.date_added, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."'");
				}

				if(isset($post_data['type']) && $post_data['type'] != "")
				{
					$this->db->where("ORD.type",$post_data['type']);
				}
				//echo $post_data['status'] != ""; die;
				if(isset($post_data['status']))
				{ 
					$this->db->where("ORD.status",$post_data['status']);
				}

				if(isset($post_data['source']) && $post_data['source'] != "")
				{
					$this->db->where("ORD.source",$post_data['source']);
				}

				if(isset($post_data['game_type']) && $post_data['game_type']!= "")
				{
					if($post_data['game_type']==1){
						$this->db->where_not_in("ORD.source",array(500,501,502));
					}
					elseif ($post_data['game_type']==2) {
						$this->db->where_in("ORD.source",array(500,501,502));
					}
				}

				if(isset($post_data['keyword']) && $post_data['keyword'] != "")
				{
					$this->db->like('CONCAT(IFNULL(USR.user_name,""),IFNULL(USR.user_unique_id,""),IFNULL(USR.email,""))', $post_data['keyword']);
				}

				$tempdb = clone $this->db;
				$query = $this->db->get();
				$total = $query->num_rows();

				$sql = $tempdb->limit($limit, $offset)
						->order_by($sort_pw_field, $sort_order)
						->get();
				//echo $tempdb->last_query();die;
				$result	= $sql->result_array();
		
		$result = ($result) ? $result : array();
		return array('result'=>$result,'total'=>$total);
	}
	
	public function get_withdrawal_popup_data()
    {
		$post_data	= $this->input->post();
		$sort_pw_order = SORT_DESC;
		$sort_pw_field = 'ORD.order_id';

		$user_id = $post_data['user_id'];
		$page_no = 1;
        $limit = 25;
        $offset = 0;
		if(isset($post_data["current_page"])) {
			
			$page_no = $post_data["current_page"];
		}
		if(isset($post_data["items_perpage"])) {
			$limit = $post_data["items_perpage"];
			$offset = ($page_no - 1) * $limit;
		}
		
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array("status","trans_desc","real_amount","bonus_amount","type","date_added")))
		{
			$sort_pw_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
			$sort_pw_order = $post_data['sort_order'] == 'DESC' ? SORT_DESC:SORT_ASC ;
		}

		//REF.refferal_id
		$this->db->select('ORD.date_added,ORD.order_id,ORD.type,ORD.source,ORD.source_id,ORD.real_amount,ORD.bonus_amount,ORD.winning_amount,
		ORD.winning_amount,ORD.status,ORD.plateform,ORD.order_unique_id as transaction_id,CONCAT(USR.first_name," ",USR.last_name) as friend_name,ORD.points,ORD.reason,ORD.custom_data',FALSE)
			->from(ORDER." AS ORD")
			->join(TRANSACTION." AS TXN","TXN.transaction_id = ORD.source_id and ORD.source IN (7,8)","LEFT")
			//->join(REFFERAL." AS REF","REF.refferal_id = ORD.source_id and ORD.source = 4","LEFT")
			->join(USER." AS USR","USR.user_id = ORD.user_id","LEFT")
			->where('ORD.user_id',$user_id);

			$this->db->where_in("ORD.source", array("3","7","8","462"));
			$this->db->where("ORD.status",$post_data['status']);
			$this->db->where("ORD.bonus_amount", "0.00");
			$this->db->where("ORD.points", "0");
			$this->db->where("(ORD.winning_amount != '0.00' OR ORD.real_amount != '0.00')", NULL);

			$tempdb = clone $this->db;
			$query = $this->db->get();
			$total = $query->num_rows();

			$sql = $tempdb->limit($limit, $offset)
					->order_by($sort_pw_field, $sort_order)
					->get();
			//echo $tempdb->last_query();
			$result	= $sql->result_array();

		// $deposit_query = $this->db->select('IFNULL(SUM(ORD.real_amount), 0.00 AS total_deposit',FALSE)
		$deposit_query = $this->db->select(' IFNULL(SUM(ORD.real_amount),0) AS total_deposit',FALSE)
					         ->from(ORDER." AS ORD")
					         ->where("ORD.user_id", $user_id)
					         ->where("ORD.type", 0)
					         ->where("ORD.status", 1)
					         ->where("ORD.source", 7);
        $total_deposit = $deposit_query->get()->row_array();

        $withdrawal_query = $this->db->select('SUM(ORD.winning_amount) AS total_withdrawal',FALSE)
					         ->from(ORDER." AS ORD")
					         ->where("ORD.user_id", $user_id)
					         ->where("ORD.type", 1)
					         ->where("ORD.status", 1)
					         ->where("ORD.source", 8);
        $total_withdrawal = $withdrawal_query->get()->row_array();
		
		$result = ($result) ? $result : array();
		return array('result' => $result, 'total' => $total, 'total_deposit' => $total_deposit['total_deposit'], 'total_withdrawal' => $total_withdrawal['total_withdrawal']);
	}

	/**
	 * [change_user_status description]
	 * @MethodName update_user_detail
	 * @Summary This function used to update user detail
	 * @param      [varchar]  [User Unique ID]
	 * @param      [array]
	 * @return     [boolean]
	 */
	public function update_user_detail($user_unique_id,$data_arr)
	{
		$this->db->where("user_unique_id",$user_unique_id)
				->update(USER,$data_arr);
		
		return true;
	}

	public function delete_active_login($user_id)
	{
		$this->db->where("user_id", $user_id)
				->delete(ACTIVE_LOGIN);

		return TRUE;
	}

	public function get_user_bank_data()
	{	
		$post_data = $this->input->post();
		$result =$this->db->select("first_name,last_name,bank_name,ac_number,ifsc_code,micr_code,upi_id,type")
							->where('user_id',$post_data['user_unique_id'])
							->get(USER_BANK_DETAIL)
							->row_array();
		return $result?$result:array();	
	}

	/**
	 * Used to get user self exclusion records
	 */
	public function get_user_self_exclusion($user_id){
		$this->db->select('max_limit, reason, document, set_by');
        $this->db->from(USER_SELF_EXCLUSION);
        $this->db->where('user_id', $user_id);
        $this->db->limit(1);
        $query = $this->db->get();
		$result = array();
		if($query->num_rows() > 0) {
            $result = $query->row_array();		
		} 
		return $result;		
	}

	public function get_pending_pancard_count()
	{
		$result= $this->db->select('count(user_id) as pending_pan_cards',FALSE)
		->from(USER)
		->where("pan_no IS NOT NULL")
		->where("pan_image IS NOT NULL")
		->where('pan_verified',0)->get()->row_array();

		return $result;
	}

	public function get_pending_bank_document_count()
	{
		$result= $this->db->select('count(U.user_id) as pending_bank_documents',FALSE)
		->from(USER.' U')
		->join(USER_BANK_DETAIL.' UBD','U.user_id=UBD.user_id')
		->where("UBD.bank_document IS NOT NULL")
		->where("(U.is_bank_verified IS NULL OR U.is_bank_verified='')")->get()->row_array();

		return $result;
	}

	public function export_referral_data()
	{	
		
		$post = $this->input->get();
		
		$user_id = $post['user_id'];

		if (isset($post['from_date']))
            $startDate = date("Y-m-d", strtotime($post['from_date']));
        else
            $startDate = date('Y-m-d', strtotime('-10 days'));
        if (isset($post['to_date']))
            $endDate = date("Y-m-d", strtotime($post['to_date']));
        else
			$endDate = date("Y-m-d");
		
		$return = array(
						'deposited_by_referred_users'=> 0,
						'deposited_by_secondary_referrals'=> 0,
						'direct_referral'=> 0,
						'referral_earned_cash'=> 0,
						'secondary_referrals'=> 0,
						'referral_bonus_earned'=> 0
						);

		$direct_ref = 0; $secondary_ref = 0;
		$direct_ref_usr_ids  = $this->get_referral_user_ids($user_id,$startDate,$endDate);
		
		
		if($direct_ref_usr_ids !=NULL) {
			$secondary_ref_usr_ids  = $this->get_referral_user_ids($direct_ref_usr_ids,$startDate,$endDate);
		} else{
			$secondary_ref_usr_ids=NULL;
		}
		
		if($direct_ref_usr_ids !=NULL) {
			
			$return['deposited_by_referred_users'] =  $this->get_user_deposit($direct_ref_usr_ids,$startDate,$endDate);
		}

		if($secondary_ref_usr_ids !=NULL) {
			$return['deposited_by_secondary_referrals'] =  $this->get_user_deposit($secondary_ref_usr_ids,$startDate,$endDate);
		} 

		$primary = $this->get_user_referral_primary($user_id,$startDate,$endDate);
		$return['direct_referral'] = ($primary['direct_referral'])?$primary['direct_referral']:0;
		$return['referral_earned_cash'] = ($primary['referral_earned_cash'])?$primary['referral_earned_cash']:0;

		if($direct_ref_usr_ids !=NULL) {
			$secondary = $this->get_user_referral_secondary($direct_ref_usr_ids,$startDate,$endDate);
			$return['secondary_referrals'] = ($secondary['secondary_referrals'])?$secondary['secondary_referrals']:0;
			$return['referral_bonus_earned'] = ($secondary['referral_bonus_earned'])?$secondary['referral_bonus_earned']:0;
		}

		$return['referral_trend'] = $this->get_user_referral_trend($user_id,$startDate,$endDate);
		
		$return['referral_list'] = $this->get_user_referral_list($user_id,$startDate,$endDate,TRUE);
		$referral=array();
                foreach($return['referral_list']['result'] as $key=>$value){
                        unset($value['user_id']);
                        unset($value['bouns_condition']);
                        $value['_']=$value['secondary_referral']['direct_referral'];
                        $referral[]=$value;
                }
		$return['referral_list']['result']=$referral;

		return $return;

	}

































































































	/** OLD METHODS NEED TO FILTER OUT AKR */

	

	

	/**
	 * [get_referral_data description]
	 * @MethodName get_referral_data
	 * @Summary This function used for get all user referral data 
	 * @param      boolean  [$user_id]
	 * @return     [array]
	 */

	public function get_referral_data()
	{	
		
		$post = $this->input->post();
		
		$user_id = $post['user_id'];

		if (isset($post['from_date']))
            $startDate = date("Y-m-d", strtotime($post['from_date']));
        else
            $startDate = date('Y-m-d', strtotime('-10 days'));
        if (isset($post['to_date']))
            $endDate = date("Y-m-d", strtotime($post['to_date']));
        else
			$endDate = date("Y-m-d");
		//	echo $endDate.'====='.$startDate; die;
		$return = array(
						'deposited_by_referred_users'=> 0,
						'deposited_by_secondary_referrals'=> 0,
						'direct_referral'=> 0,
						'referral_earned_cash'=> 0,
						'secondary_referrals'=> 0,
						'referral_bonus_earned'=> 0
						);

		$direct_ref = 0; $secondary_ref = 0;
		$direct_ref_usr_ids  = $this->get_referral_user_ids($user_id,$startDate,$endDate);
		
		
		if($direct_ref_usr_ids !=NULL) {
			$secondary_ref_usr_ids  = $this->get_referral_user_ids($direct_ref_usr_ids,$startDate,$endDate);
		} else{
			$secondary_ref_usr_ids=NULL;
		}
		//print_r($secondary_ref_usr_ids);die;

		if($direct_ref_usr_ids !=NULL) {
			
			$return['deposited_by_referred_users'] =  $this->get_user_deposit($direct_ref_usr_ids,$startDate,$endDate);
		}

		if($secondary_ref_usr_ids !=NULL) {
			$return['deposited_by_secondary_referrals'] =  $this->get_user_deposit($secondary_ref_usr_ids,$startDate,$endDate);
		} 

		$primary = $this->get_user_referral_primary($user_id,$startDate,$endDate);
		$return['direct_referral'] = ($primary['direct_referral'])?$primary['direct_referral']:0;
		$return['referral_earned_cash'] = ($primary['referral_earned_cash'])?$primary['referral_earned_cash']:0;
		$return['referral_bonus_earned'] = ($primary['referral_bonus_earned'])?$primary['referral_bonus_earned']:0;
		if($direct_ref_usr_ids !=NULL) {
			$secondary = $this->get_user_referral_secondary($direct_ref_usr_ids,$startDate,$endDate);
			
			$return['secondary_referrals'] = ($secondary['secondary_referrals'])?$secondary['secondary_referrals']:0;
			//$return['referral_bonus_earned'] = ($secondary['referral_bonus_earned'])?$secondary['referral_bonus_earned']:0;
		}

		$return['referral_trend'] = $this->get_user_referral_trend($user_id,$startDate,$endDate);
		//print_r($return['referral_trend']); die;
		$return['referral_list'] = $this->get_user_referral_list($user_id,$startDate,$endDate);

		return $return;

	}
	
	public function get_user_referral_primary($user_id,$startDate,$endDate)
	{ ////count(user_id) direct_referral,
       
		$result = $this->db->select("COUNT(DISTINCT friend_id) direct_referral,
		ROUND(sum(user_real_cash),2) AS referral_earned_cash,
		ROUND(sum(user_bonus_cash),2) AS referral_bonus_earned")
						->from(USER_AFFILIATE_HISTORY)
						->where("user_id",$user_id)
						->where('is_referral',1)
						->where("created_date BETWEEN '{$startDate}' AND '{$endDate} 23:59:59'")
						->get()->row_array();
				
		return ($result)?$result:array();
	}

	public function get_user_referral_secondary($user_ids,$startDate,$endDate)
	{ //and affiliate_type = 1,19(5th),20(10th),21(15th referral) //count(user_id) secondary_referrals
	 $query = $this->db->query("SELECT COUNT(distinct friend_id) secondary_referrals,sum(user_bonus_cash) referral_bonus_earned FROM vi_".USER_AFFILIATE_HISTORY." where user_id IN ($user_ids)  and created_date BETWEEN  '{$startDate}' AND '{$endDate} 23:59:59' and is_referral=1 group by 'friend_id'");
	 $result = $query->row_array();
	 //echo $this->db->last_query();die;
	 return ($result)?$result:0;
	}

	public function get_referral_user_ids($user_id,$startDate,$endDate)
	{
        //where commented temp
		$result = $this->db->select("GROUP_CONCAT(DISTINCT (friend_id)) as friend_ids")
						->from(USER_AFFILIATE_HISTORY)
						->where_in("user_id",$user_id)
						->where('friend_id !=',$user_id)
						//->where_in('affiliate_type',[1,19,20,21])
						->where('is_referral',1)
						->where("created_date BETWEEN '{$startDate}' AND '{$endDate} 23:59:59'")
						->get()->result();

						//echo $this->db->last_query(); die;
		return ($result)?$result[0]->friend_ids:NULL;
	}

	public function get_user_referral_trend($user_id,$startDate,$endDate)
	{
		$result = $this->db->select("count(user_id) referral_count,created_date")
						->from(USER_AFFILIATE_HISTORY)
						->where("user_id",$user_id)
						->where('friend_id !=',$user_id)
						->where('is_referral',1)
						->where("created_date BETWEEN '{$startDate}' AND '{$endDate} 23:59:59'")
						->group_by('created_date','Desc')
						->get()->result_array();
		//echo $this->db->last_query(); print_r($result); die;			
		return ($result)?$result:array();
	}
	public function get_user_referral_list($user_id,$startDate,$endDate,$csv=0)
	{  	 
		$sort_field	= 'created_date';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;
		$post_data = $this->input->post();

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('user_name')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;

		$sql = $this->db->select("U.user_id,U.user_unique_id,U.user_name,U.first_name,U.last_name,UAH.user_affiliate_history_id,UAH.source_type,UAH.affiliate_type,UAH.friend_id,UAH.collection_id,	
		UAH.contest_id,
		UAH.status,UAH.user_bonus_cash,UAH.user_real_cash,UAH.friend_bonus_cash,UAH.user_coin,UAH.friend_real_cash,	
		
		UAH.friend_coin,UAH.is_referral,UAH.bouns_condition,UAH.user_investment,UAH.user_remaining_bonus,UAH.friend_remaining_bonus,
		
		UAH.friend_email,UAH.friend_mobile,
		ROUND(sum(UAH.friend_real_cash),2)  as total_friend_real_cash,
		ROUND(sum(UAH.user_real_cash),2)  as total_user_real_cash,
		",FALSE);
		// ,UAH.created_date as created_date

			if (isset($csv) && $csv == true) 	
			{				
				$tz_diff = get_tz_diff($this->app_config['timezone']);
				
				$this->db->select("CONVERT_TZ(UAH.created_date, '+00:00', '".$tz_diff."') AS created_date");
			}else{
				$this->db->select("UAH.created_date");
			}

			       $this->db->from(USER_AFFILIATE_HISTORY.' AS UAH')
						->join(USER.' AS U','UAH.friend_id=U.user_id')
						->where('UAH.user_id',$user_id)
						->where('UAH.friend_id !=',$user_id)
						->where('UAH.is_referral',1)
						->group_by('UAH.friend_id')
						->order_by($sort_field, $sort_order);
		
		if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db->where("DATE_FORMAT(UAH.created_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(UAH.created_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
		}

		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows();
		if($csv==0){
			$this->db->limit($limit,$offset);
		}
		$result	= $sql->get()->result_array();
		//print_r($result); die;
		$response_array =array();
		foreach($result as  $key => $res ){
		$response_array[$key]=$res; 
		$response_array[$key]['deposit'] =$this->get_user_deposit($res['user_id'],$startDate,$endDate);	
		$response_array[$key]['secondary_referral'] =$this->get_user_referral_primary($res['user_id'],$startDate,$endDate);	
		}
		return array('result'=>$response_array,'total'=>$total);
	
	}                                                                                                                                                                                  
	function get_user_deposit($user_id,$startDate,$endDate){
		$result = $this->db->select("sum(real_amount) as deposited")
							->from(ORDER)
							->where("user_id in  ($user_id)")
							->where("status",1)
							->where("type",0)
							->where("source",7)
							//->where("date_added BETWEEN '{$startDate}' AND '{$endDate} 23:59:59'")
							->get()->result();
							//echo $this->db->last_query(); die;
		return ($result[0]->deposited)?$result[0]->deposited:0;
	}

	

	public function get_all_user_counts($post_data){
		
		$this->db->select("count(U.user_id) as total",FALSE)
        ->from(USER.' AS U');

        if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db->where("DATE_FORMAT(U.added_date, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(U.added_date, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
		}

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name),IFNULL(U.pan_no,"")))', strtolower($post_data['keyword']) );
		}

		if(isset($post_data['pending_pan_approval']) && $post_data['pending_pan_approval'] == '1')
		{	
			$this->db->join(USER_BANK_DETAIL.' AS UBD','UBD.user_id=U.user_id',"left");
			$this->db->where("U.pan_no IS NOT NULL AND U.pan_image IS NOT NULL AND pan_verified = 0");
			$this->db->or_where("U.is_bank_verified IS NULL and UBD.bank_document IS NOT NULL");
			$this->db->or_where("U.is_bank_verified = 0 and UBD.bank_document IS NOT NULL");
		}

		if(isset($post_data['status']) && $post_data['status']==0)
		{
			//$this->db->where("U.status","'".$post_data['status']."'",FALSE);
			$this->db->where("U.status",$post_data['status']);
		} else if(isset($post_data['status']) && $post_data['status']==1){

			$this->db->where_in("U.status",array(1));
		}

		if(isset($post_data['is_flag']))
		{
			$this->db->where("U.is_flag",$post_data['is_flag']);
		}


        $query = $this->db->get();
        $result = $query->result_array();

        //echo $this->db->last_query(); die;
        return ($result[0]['total'])?$result[0]['total']:0;
	}
	
	

	function get_profit($user_id){
		//SELECT ROUND(SUM(json_extract(custom_data, '$.profit')), 2) as total_profit FROM vi_order where user_id=28924
		/* $this->db_fantasy = $this->load->database('db_fantasy', TRUE);
		$this->db_fantasy->select("sum(C.site_rake) as total_site_rake")
						 ->from(CONTEST . ' as C')
						 ->join(LINEUP_MASTER_CONTEST .' as LMC',"C.contest_id = LMC.contest_id")	
						 ->join(LINEUP_MASTER .' as LM',"LMC.lineup_master_id = LM.lineup_master_id")
						 ->where("LM.user_id",$user_id);	
		$query = $this->db_fantasy->get();
		//echo $this->db_fantasy->last_query();die;
		$data = $query->row_array();
		return ($data)?$data['total_site_rake']:array(); */
		//ROUND(SUM(json_extract(custom_data, '$.profit')), 2) as total_profit
		$this->db->select("
		ROUND(SUM(IF(source=1, json_extract(custom_data, '$.profit'), 0)), 2) as total_profit,
		ROUND(SUM(IF(source=2, json_extract(custom_data, '$.profit'), 0)), 2) as cancel_profit,
		")
		->from(ORDER." AS ORD")
		->where("user_id",$user_id);	
		$query = $this->db->get();
		$data = $query->row_array();
		$profit=0;
		if(!empty($data)) {
			$profit = $data['total_profit'] -$data['cancel_profit']; 
 		}
		return $profit;


	}

	public function get_user_referee($user_id)
	{

		$result = $this->db->select("CONCAT(U.first_name,' ',U.last_name) as full_name,U.email,U.user_name",FALSE)
						->from(USER." AS U")
						->join(USER_AFFILIATE_HISTORY." AS UAH","U.user_id = UAH.user_id","left")
							
						//->join(TEAM." AS T","T.team_id=  U.team_id","left")
						->where("UAH.friend_id",$user_id)
						->get()->row_array();
		return ($result)?$result:array();
	}

	/**
	 * [get_user_detail_by_user_id description]
	 * @MethodName get_user_detail_by_user_id
	 * @Summary This function used for get user Detail
	 * @param      [int]  [User Id]
	 * @return     [array]
	 */
	public function get_user_detail_by_user_id($user_id)
	{
		$result = $this->db->select("''as 'team_name',U.master_state_id,U.master_country_id,U.facebook_id,U.address,U.user_unique_id,U.user_id,U.first_name,U.last_name,U.image,MC.country_name,U.balance,U.email,DATE_FORMAT(U.dob,'%d-%b-%Y') as dob,U.city,U.language,U.status,U.added_date,U.user_name,MS.name as state_name,IFNULL(U.zip_code,'--') As zip_code,IFNULL(U.phone_no,'--') AS phone_no,IFNULL(U.gender,'--') As gender",FALSE)
						->from(USER." AS U")
						->join(MASTER_COUNTRY." AS MC","MC.master_country_id = U.master_country_id","left")
						->join(MASTER_STATE." AS MS","MS.master_state_id = U.master_state_id","left")						
						//->join(TEAM." AS T","T.team_id=  U.team_id","left")
						->where("U.user_id",$user_id)
						->get()->row_array();
		return ($result)?$result:array();
	}

	

	/**
	 * [game_history_by_user description]
	 * @MethodName game_history_by_user
	 * @Summary This function used for get all created by user and join game list
	 * @param  [array] [Data Array]
	 * @return     [array]
	 */
	public function game_history_by_user()
	{
		$sort_field = 'season_scheduled_date';
		$sort_order = 'DESC';
		$limit      = 10;
		$page       = 0;
		$user_id = $this->input->post('user_id');
		if(($this->input->post('items_perpage')))
		{
			$limit = $this->input->post('items_perpage');
		}

		if(($this->input->post('current_page')))
		{
			$page = $this->input->post('current_page')-1;
		}

		if(($this->input->post('sort_field')) && in_array($this->input->post('sort_field'),array('game_name', 'size', 'entry_fee', 'prize_pool', 'season_scheduled_date')))
		{
			$sort_field = $this->input->post('sort_field');
		}

		if(($this->input->post('sort_order')) && in_array($this->input->post('sort_order'),array('DESC','ASC')))
		{
			$sort_order = $this->input->post('sort_order');
		}

		$offset	= $limit * $page;
		$user_id = $this->input->post('user_id');
		$this->db->select('G.contest_unique_id, G.contest_name, G.contest_access_type, G.entry_fee, G.prize_pool, G.total_user_joined, G.size,
							G.status,LM.lineup_master_id, DATE_FORMAT(G.season_scheduled_date, "%d-%b-%Y %H:%i") AS season_scheduled_date', FALSE)
						->from(CONTEST . " AS G")
						->join(LINEUP_MASTER." AS LM","LM.contest_id=G.contest_id","left")
						->where("LM.user_id",$user_id);

		$tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();
		$sql = $tempdb->order_by($sort_field, $sort_order)
						->limit($limit,$offset)
						->get();
		$result	= $sql->result_array();

		$result = ($result) ? $result : array();
		return array('result'=>$result,'total'=>$total);
	}

	

	/**
	 * [update_all_user_status description]
	 * @MethodName update_all_user_status
	 * @Summary This function used to change all user status
	 * @param      [array]  [data_array]
	 * @return     [boolean]
	 */
	public function update_all_user_status($data_arr)
	{
		$this->db->update_batch(USER,$data_arr,"user_unique_id");
		return true;
	}
	
	

	

	/**
	 * [get_max_user_balance description]
	 * @MethodName get_max_user_balance
	 * @Summary This function used to get user max balance
	 * @return  int
	 */
	public function get_max_min_user_balance()
	{
		$data_array = array();
		$this->db->select_max('balance','max_value');
		$this->db->select_min('balance','min_value');
		$result = $this->db->get(USER)->row();
		$data_array["max_value"] = $result->max_value;
		$data_array["min_value"] = $result->min_value;
		return $data_array;
	}

	/**
	 * [get_all_user_list description]
	 * @MethodName get_all_user_list
	 * @Summary This function used to get all user list
	 * @return     array
	 */
	public function get_all_user_list()
	{
		$sql = $this->db->select('email')
						->from(USER)
						->where('status', '1')
						->get();
		$rs = $sql->result_array();

		$rs = array_column($rs, "email");
		
		return $rs;
	}

	



	public function export_users()
	{
		$allow_bank_flow = isset($this->app_config['allow_bank_flow'])?$this->app_config['allow_bank_flow']['key_value']:0; 


		$post_data = $this->input->get();

		// echo "<pre>";
		// print_r($post_data);die;

		if(isset($post_data['sort_field']) && !empty($post_data['sort_field']) && in_array($post_data['sort_field'],array('added_date','winning_balance','point_balance','first_name','user_name','email','country','balance','status','last_login','bonus_balance','MC.country_name','pan_no','pan_verified')))
		{
			$sort_field = $post_data['sort_field'];
		} 
		else{
			$sort_field ='added_date';
		}

		if(isset($post_data['sort_order']) && !empty($post_data['sort_order'])  && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		else{
			$sort_order ='DESC';
		}

		$tz_diff = get_tz_diff($this->app_config['timezone']);                    
		// $this->db->select("CONVERT_TZ(ORD.date_added, '+00:00', '".$tz_diff."') AS order_date_added");

		$this->db->select("U.user_name as UserName,U.device_type as DeviceType, U.email AS Email,U.phone_no AS Mobile,IFNULL(SUM(UT.net_winning),0) as net_winning, U.balance AS DepositBalance, U.bonus_balance AS Bonus, U.winning_balance AS WinningBalance,U.point_balance AS CoinBalance, U.pan_no AS PanCardNumber, U.pan_verified AS PanCardStatus,UBD1.ac_number AS BankAccountNumber,UBD1.ifsc_code AS IfscNumber,U.is_bank_verified AS BankStatus, DATE_FORMAT(U.dob,'%d-%b-%Y') AS DateOfBirth,U.gender AS Gender, MC.country_name AS CountryName, MS.name as StateName, U.city AS City,U.address AS Street,U.zip_code as ZipCode,CONVERT_TZ(U.added_date, '+00:00', '".$tz_diff."') AS MemberSince, CONVERT_TZ(U.last_login, '+00:00', '".$tz_diff."') AS LastLogin,U.status,RU.referral_code,RU.user_name AS ReferredByUsername,RU.phone_no AS ReferredByPhone,U.last_ip AS LastAccessIP,U.aadhar_status as AadharStatus",FALSE)
						->from(USER.' AS U')
						->join(USER_TDS_REPORT.' AS UT','UT.user_id=U.user_id',"left")
						->join(USER_AFFILIATE_HISTORY.' AS UAH',"UAH.friend_id=U.user_id and UAH.affiliate_type=1","left")
						->join(MASTER_COUNTRY." AS MC","MC.master_country_id=  U.master_country_id","left")
						->join(USER.' AS RU',"RU.user_id=UAH.user_id","left")
						->join(MASTER_STATE." AS MS","MS.master_state_id=  U.master_state_id","left")
						->join(USER_BANK_DETAIL.' AS UBD1',"UBD1.user_id=U.user_id","left")
						->where("U.is_systemuser","0");

						// if (isset($post_data['csv']) && $post_data['csv'] == true) 	
						// {
						// 	// print_r( $this->app_config['timezone']['key_value']); die;
						// 	$tz_diff = get_tz_diff($this->app_config['timezone']);
						// 	// echo $tz_diff; die;
						// 	$this->db->select("CONVERT_TZ(U.last_login, '+00:00', '".$tz_diff."') AS LastLogin,CONVERT_TZ(U.added_date, '+00:00', '".$tz_diff."') AS MemberSince");
						// }else{
						// 	$this->db->select("U.added_date, U.last_login");
						// }
						

		if(isset($post_data['country']) && $post_data['country']!="")
		{
			$this->db->where("U.master_country_id",$post_data['country']);
		}

		if(isset($post_data['status']) && $post_data['status']!="")
		{
			$this->db->where("U.status",$post_data['status']);
		}
		if(isset($post_data['is_flag']) && (int)$post_data['is_flag']==1 && $post_data['keyword'] == "")
		{ 
			$this->db->where("U.is_flag",$post_data['is_flag']);
		}

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name),IFNULL(U.pan_no,"")))', strtolower($post_data['keyword']) );
		}

		$doc_where_str = "";
		if($allow_bank_flow == "1"){
			$this->db->select("UBD.bank_document,UBD.type");
			$this->db->join(USER_BANK_DETAIL.' AS UBD','UBD.user_id=U.user_id',"left");
			$doc_where_str = "AND UBD.bank_document IS NOT NULL ";

		}

		$allow_aadhar = isset($this->app_config['allow_aadhar'])?$this->app_config['allow_aadhar']['key_value']:0; 
  
        $this->db->select("UA.name as aadhar_name,UA.aadhar_number,UA.front_image as aadhar_front_image,UA.back_image as aadhar_back_image");
		$this->db->join(USER_AADHAR.' AS UA','UA.user_id=U.user_id',"left");


		if(isset($post_data['is_pending_pan_approval']) && $post_data['is_pending_pan_approval'] == '1')
		{	

			if ($allow_aadhar) {
			
			   $allow_adhar_str = "OR (UA.aadhar_number IS NOT NULL AND UA.name IS NOT NULL AND U.aadhar_status=0)" ;
		   }else{
			   $allow_adhar_str ='';
		   }
			
			$this->db->where("(
				(U.pan_no IS NOT NULL AND U.pan_image IS NOT NULL AND  U.pan_verified =0)
			OR 			
			((U.is_bank_verified IS NULL OR U.is_bank_verified =0) ".$doc_where_str.") 
			".$allow_adhar_str." )");
			
		}

		if(isset($post_data['frombalance']) && isset($post_data['tobalance']))
		{
			$this->db->where("U.balance >= ".$post_data['frombalance']." and U.balance <= ".$post_data['tobalance']."");
		}
	
		if(($post_data['is_flag']== '' || $post_data['is_flag']== '0' ) &&  ($post_data['is_pending_pan_approval'] == '' || $post_data['is_pending_pan_approval'] == '0'))
		{ 
		
		if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db->where("DATE_FORMAT(U.added_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(U.added_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
		}
	  }


		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name),IFNULL(U.pan_no,""))', $post_data['keyword']);
		}
						$sql =  $this->db->order_by('U.'.$sort_field, $sort_order)
						->group_by('U.user_id')
						// ->order_by($sort_field, $sort_order)
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

	/**
	 * [make_payment_transaction description]
	 * @MethodName make_payment_transaction
	 * @Summary This function used to debit or credit user balance
	 * @param      array  $data_arr
	 * @return     int
	 */
	public function make_payment_transaction($data_arr)
	{
		$this->db->insert(PAYMENT_HISTORY_TRANSACTION,$data_arr);
		return $this->db->insert_id();
	}

	public function remove_cpf($user_unique_id=FALSE)
	{
		$condition = array('user_unique_id'=>$user_unique_id);
		$data = array('cpf_no'=>NULL);
		$this->db->where($condition);
		$this->db->update(USER, $data);
		return $this->db->affected_rows();
	}

	/**
	*@method get_all_country
	*@uses to get all country list
	****/

	public function get_country_list()
	{
		$result =$this->db->select("master_country_id,country_name,abbr")
							->order_by('country_name,abbr','ASC')
							->get(MASTER_COUNTRY)
							->result_array();
		return $result?$result:array();			
	}

	public function get_state_list_by_country_id($master_country_id)
	{
		
		$result =$this->db->select("master_state_id,name as state_name")
		->where('master_country_id',$master_country_id);
		
		$result = $this->db->order_by('name','ASC')
							->get(MASTER_STATE)
							->result_array();
		return $result?$result:array();	
	}

	
	



	public function _generate_key() 
	{
		$this->load->helper('security');

		do {
			$salt = do_hash(time() . mt_rand());
			$new_key = substr($salt, 0, 10);
		}

		// Already in the DB? Fail. Try again
		while (self::_key_exists($new_key));

		return $new_key;
	}

	private function _key_exists($key)
	{
		$this->db->select('user_unique_id');
        $this->db->where('user_unique_id', $key);
        $this->db->limit(1);
        $query = $this->db->get(USER);
        $num = $query->num_rows();
        if($num > 0){
            return true;
        }
		return false;
		
	}

	public function _generate_referral_code() 
	{
		$this->load->helper('security');

		do {
			$salt = do_hash(time() . mt_rand());
			$new_key = substr($salt, 0, 5);
			$new_key = strtoupper($new_key);
		}

		// Already in the DB? Fail. Try again
		while (self::_referral_code_exists($new_key));

		return $new_key;
	}

	private function _referral_code_exists($key)
	{
		
		$this->db->select('referral_code');
        $this->db->where('referral_code', $key);
        $this->db->limit(1);
        $query = $this->db->get(USER);
        $num = $query->num_rows();
        if($num > 0){
            return true;
        }
        return false;
	}	

	public function registration($post)
	{
		$post['referral_code'] = self::_generate_referral_code();
		$this->db->insert(USER, $post);
		return $this->db->insert_id();
	}

	public function get_contest_winning_amount($lineup_master_contest_ids,$user_id)
	{
		$result = $this->db->select('sum(winning_amount) as winning_amount,SUM(bonus_amount) as winning_bonus,sum(points) as winning_coins')
						   ->from(ORDER)
						   ->where('user_id', $user_id)
						   ->where('status', '1')
						   ->where('type', '0')
						   ->where('source', '3')
						   ->where("source_id in($lineup_master_contest_ids)")
						   ->get()
						   ->row_array();
						   //echo $this->db->last_query();
		return (!empty($result)) ? $result : array("winning_amount" => 0,"winning_bonus" => 0,"winning_coins"=>0);
	}

	

	public function contest_list_by_user_id()
	{	
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
		$sort_field = 'season_scheduled_date';
		$sort_order = 'DESC';
		$limit      = 10;
		$page       = 0;
		$is_user_history = 0;

		$post_data = $this->input->post();

		if(isset($post_data['is_user_history']))
		{
			$is_user_history = $post_data['is_user_history'];
		}

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','size','entry_fee','season_scheduled_date','prize_pool', 'is_feature','auto_recurrent_id','guaranteed_prize','is_uncapped', 'is_turbo_lineup')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		
		$offset	= $limit * $page;

		$classic = $this->lang->line('classic');
		$reverse = $this->lang->line('reverse');
		$second_inning = $this->lang->line('2nd_inning');
		$this->db_fantasy->select("CM.season_game_count,G.entry_fee,G.entry_fee*count(LM.lineup_master_id) as total_entry_fee,G.sports_id, G.contest_unique_id,G.season_game_uid,G.contest_name, G.contest_access_type,G.prize_pool, G.total_user_joined, G.size, G.is_feature,G.status,LM.lineup_master_id, DATE_FORMAT(G.season_scheduled_date, '%Y-%m-%d %H:%i:%s') AS season_schedule_date,G.guaranteed_prize,G.is_uncapped, G.is_auto_recurring,LMC.is_winner,
		SUM(LMC.is_winner) as constest_won,SUM(G.contest_id) as constest_played,group_concat(LMC.lineup_master_contest_id) as lineup_master_contest_ids,group_concat(LMC.lineup_master_id) as lineup_master_ids,LM.user_id,G.league_id,G.contest_id,max(LMC.game_rank) as game_rank,G.prize_type,IFNULL(S.title,S.tournament_name) as title,G.group_id,GROUP_CONCAT(LMC.prize_data SEPARATOR '|#') as prize_data,G.currency_type,
		(CASE WHEN G.is_2nd_inning=1 THEN '{$second_inning}'
		WHEN G.is_reverse =1 THEN '{$reverse}'
		WHEN G.is_reverse=0 AND G.is_2nd_inning =0 THEN '{$classic}' 		
	
  END) AS feature_type", FALSE)
						->from(LINEUP_MASTER . " AS LM")
						->join(LINEUP_MASTER_CONTEST." AS LMC","LMC.lineup_master_id=LM.lineup_master_id","INNER")
						->join(CONTEST." AS G","LMC.contest_id=G.contest_id","INNER")
						->join(COLLECTION_SEASON." AS CS","G.collection_master_id=CS.collection_master_id","LEFT")
						->join(COLLECTION_MASTER." AS CM","G.collection_master_id=CM.collection_master_id","LEFT")
						->join(SEASON." AS S","CS.season_id=S.season_id AND S.league_id=G.league_id","LEFT")
						->where('LM.user_id',$post_data["user_id"])
						->group_by('LMC.contest_id');
		$game_type = isset($post_data['game_type'])?$post_data['game_type']:"";

		if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db_fantasy->where("DATE_FORMAT(G.season_scheduled_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(G.season_scheduled_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
		}
		switch ($game_type)
		{
			case 'current_game':
				$this->db_fantasy->where('G.status','0');
				break;
			case 'completed_game':
				$this->db_fantasy->where('G.status >','1');
				break;
			case 'cancelled_game':
				$this->db_fantasy->where('G.status','1');
				break;
			case 'upcoming_game':
				$season_scheduled_date = format_date('today','Y-m-d');
				$this->db_fantasy->where('G.season_scheduled_date >',$season_scheduled_date);

				break;
			default:
				break;
		}       
    
                
        // Check Contest Type condition
        if(!empty($post_data['contest_feature']))
		{
			switch ($post_data['contest_feature'])
            {
                case '1':
                        $this->db_fantasy->where('G.is_feature','1');
                        break;
                case '2':
                        $this->db_fantasy->where('G.is_auto_recurring','1');
                        break;
                case '3':
                        $this->db_fantasy->where('G.is_uncapped','1');
                        break;
                case '4':
                        $this->db_fantasy->where('G.guaranteed_prize','1');
                        break;
                case '5':
                        $this->db_fantasy->where('G.is_turbo_lineup', '1');
                        break;
                case '6':
                        $this->db_fantasy->where('G.is_live_substitution', '1');
                        break;
                default:
                        break;
            }
		}

		/* if(isset($is_user_history) && $is_user_history == "1"){
			$this->db_fantasy->group_by('LMC.lineup_master_contest_id');
		} */
		//$this->db_fantasy->group_by('G.contest_unique_id');
		$tempdb = clone $this->db_fantasy;
		$temp_q = $tempdb->get();
		//echo $tempdb->last_query(); die;
		$total = $temp_q->num_rows();
		
		

		if(!empty($sort_field) && !empty($sort_order))
		{
			$this->db_fantasy->order_by('G.'.$sort_field, $sort_order);
		}

		if(!empty($limit) )
		{
			$this->db_fantasy->limit($limit, $offset);
		}
		$sql = $this->db_fantasy->get();
		
		$result	= $sql->result_array();
		//echo $this->db_fantasy->last_query();die;
		return array('result'=>$result, 'total'=>($total)?$total:0);
		//get sport pref
		//SELECT vi_master_sports.sports_name, vi_contest.sports_id,count(vi_contest.contest_id) as no_of_played FROM `vi_contest` LEFT JOIN vi_master_sports ON vi_master_sports.sports_id=vi_contest.sports_id GROUP BY vi_contest.sports_id
	}


	public function get_contest_list_by_user_id()
	{	
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
		$sort_field = 'season_scheduled_date';
		$sort_order = 'DESC';
		$limit      = 10;
		$page       = 0;
		$is_user_history = 0;

		$post_data = $this->input->get();
		$csv = 1;

		if(isset($post_data['is_user_history']))
		{
			$is_user_history = $post_data['is_user_history'];
		}

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','size','entry_fee','season_scheduled_date','prize_pool', 'is_feature','auto_recurrent_id','guaranteed_prize','is_uncapped', 'is_turbo_lineup')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		
		$offset	= $limit * $page;

		$classic = $this->lang->line('classic');
		$reverse = $this->lang->line('reverse');
		$second_inning = $this->lang->line('2nd_inning');
		$tz_diff = get_tz_diff($this->app_config['timezone']);                    
                    
		$this->db_fantasy->select("G.sports_id, G.contest_unique_id,G.season_game_uid,G.contest_unique_id, G.contest_name, G.contest_access_type, G.entry_fee, G.prize_pool, G.total_user_joined, G.size, G.is_feature,G.status,LM.lineup_master_id,G.guaranteed_prize,G.is_uncapped, G.is_auto_recurring,LMC.is_winner,
		SUM(LMC.is_winner) as constest_won,SUM(G.contest_id) as constest_played,group_concat(LMC.lineup_master_contest_id) as lineup_master_contest_ids,group_concat(LMC.lineup_master_id) as lineup_master_ids,LM.user_id,G.league_id,G.contest_id,max(LMC.game_rank) as game_rank,G.prize_type,IFNULL(S.title,S.tournament_name) as title,GROUP_CONCAT(LMC.prize_data SEPARATOR '|#') as prize_data,
		(CASE WHEN G.is_2nd_inning=1 THEN '{$second_inning}'
		WHEN G.is_reverse =1 THEN '{$reverse}'
		WHEN G.is_reverse=0 AND G.is_2nd_inning =0 THEN '{$classic}' 		
	
  END) AS feature_type", FALSE)
		->select("CONVERT_TZ(G.season_scheduled_date, '+00:00', '".$tz_diff."') AS season_schedule_date")
						->from(LINEUP_MASTER . " AS LM")
						->join(LINEUP_MASTER_CONTEST." AS LMC","LMC.lineup_master_id=LM.lineup_master_id","INNER")
						->join(CONTEST." AS G","LMC.contest_id=G.contest_id","INNER")
						->join(COLLECTION_SEASON." AS CS","G.collection_master_id=CS.collection_master_id","LEFT")
						->join(SEASON." AS S","CS.season_id=S.season_id","LEFT")
						->where('LM.user_id',$post_data["user_id"]);
		$game_type = isset($post_data['game_type'])?$post_data['game_type']:"";

		if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db_fantasy->where("DATE_FORMAT(G.season_scheduled_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(G.season_scheduled_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
		}

		switch ($game_type)
		{
			case 'current_game':
				$this->db_fantasy->where('G.status','0');
				break;
			case 'completed_game':
				$this->db_fantasy->where('G.status >','1');
				break;
			case 'cancelled_game':
				$this->db_fantasy->where('G.status','1');
				break;
			case 'upcoming_game':
				$season_scheduled_date = format_date('today','Y-m-d');
				$this->db_fantasy->where('G.season_scheduled_date >',$season_scheduled_date);

				break;
			default:
				break;
		}       
    
                
        // Check Contest Type condition
        if(!empty($post_data['contest_feature']))
		{
			switch ($post_data['contest_feature'])
            {
                case '1':
                        $this->db_fantasy->where('G.is_feature','1');
                        break;
                case '2':
                        $this->db_fantasy->where('G.is_auto_recurring','1');
                        break;
                case '3':
                        $this->db_fantasy->where('G.is_uncapped','1');
                        break;
                case '4':
                        $this->db_fantasy->where('G.guaranteed_prize','1');
                        break;
                case '5':
                        $this->db_fantasy->where('G.is_turbo_lineup', '1');
                        break;
                case '6':
                        $this->db_fantasy->where('G.is_live_substitution', '1');
                        break;
                default:
                        break;
            }
		}

		if(isset($is_user_history) && $is_user_history == "1"){
			$this->db_fantasy->group_by('LMC.lineup_master_contest_id');
		}
		$this->db_fantasy->group_by('G.contest_unique_id');
		$tempdb = clone $this->db_fantasy;
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows();
		
		

		if(!empty($sort_field) && !empty($sort_order))
		{
			$this->db_fantasy->order_by('G.'.$sort_field, $sort_order);
		}

		if(!empty($limit) && (!isset($csv) || $csv ==0))
		{
			$this->db_fantasy->limit($limit, $offset);
		}
		$sql = $this->db_fantasy->get();
		
		$result	= $sql->result_array();
		// echo $this->db_fantasy->last_query();die;
		return array('result'=>$result, 'total'=>($total)?$total:0);
		//get sport pref
		//SELECT vi_master_sports.sports_name, vi_contest.sports_id,count(vi_contest.contest_id) as no_of_played FROM `vi_contest` LEFT JOIN vi_master_sports ON vi_master_sports.sports_id=vi_contest.sports_id GROUP BY vi_contest.sports_id
	}


	public function get_user_private_contests($user_id, $post_data = array())
	{
		$limit = 10;
		$page = 1;
		$sort_field = 'C.contest_id';
		$sort_order = 'DESC';
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_id','contest_name','entry_fee','minimum_size','size','total_user_joined','prize_pool','multiple_lineup')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		if(!empty($post_data['pageSize']) && $post_data['pageSize'])
		{
			$limit = $post_data['pageSize'];
		}

		if(!empty($post_data['currentPage']) && $post_data['currentPage'])
		{
			$page = $post_data['currentPage'];
		}
		$offset	= $limit * ($page-1);

		$this->db_fantasy->select('C.status, C.contest_id, C.contest_unique_id, C.league_id, C.contest_name, C.contest_description, C.season_scheduled_date, C.minimum_size, C.size, C.total_user_joined, C.multiple_lineup, C.entry_fee, C.site_rake, C.host_rake, C.prize_pool, C.prize_type, C.prize_distibution_detail,CM.collection_name AS match_name')
         ->from(CONTEST.' as C')
         ->join(COLLECTION_MASTER." AS CM", 'CM.collection_master_id = C.collection_master_id','INNER')
		//  ->join(COLLECTION_SEASON." AS CS", 'CS.collection_master_id = CM.collection_master_id','INNER')
         ->where('C.contest_access_type',1)
         // ->where_in('C.user_id', array(316,105))
         ->where('C.user_id', $user_id)
         ->where('C.status != ', 1);

        $tempdb = clone $this->db_fantasy;
        $temp_q = $tempdb->get();
		$total = $temp_q->num_rows();
		
		$this->db_fantasy->order_by($sort_field, $sort_order);
		$result = $this->db_fantasy->limit($limit,$offset)->get()->result_array();
		return array('total_private_contest_created' => $total, 'contests_list' => $result);
	}

	/**
	 * method to unblock otp blocked users
	 */
	public function update_otp_blocked_users(){
		$post_data = $this->input->post();
		$user_id= $post_data['user_id'];
		unset($post_data['user_id']);
		$update = $this->db->update(USER,$post_data,['user_id'=>$user_id]);
		if($update){
			return true;
		}
		return false;
	}

	/**
	 * Used to get self exclusion user list
	 */
	public function get_self_exclusions() {  	 
		$sort_field	= 'modified_date';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;
		$post_data = $this->input->post();
		if($post_data['items_perpage']) {
			$limit = $post_data['items_perpage'];
		}

		if($post_data['current_page']) {
			$page = $post_data['current_page']-1;
		}

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('modified_date','user_name','max_limit'))) {
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		$offset	= $limit * $page;
		$sql = $this->db->select("U.user_unique_id, U.user_id, U.user_name, USE.max_limit, USE.set_by, USE.modified_date",FALSE)
								->from(USER_SELF_EXCLUSION.' AS USE')
								->join(USER.' AS U','U.user_id=USE.user_id');

		
		$this->db->order_by($sort_field, $sort_order);	

		$tempdb = clone $this->db; //to get rows for pagination
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows(); 

		$sql = $this->db->limit($limit,$offset)
						->get();
		$result	= $sql->result_array();
		$result=($result)?$result:array();
		return array('result'=>$result,'total'=>$total);
	}

	/**
	 * Used to set self exclusion for user
	 */
	public function set_self_exclusion(){
		$post_data = $this->input->post();
		$user_id = $post_data['user_id'];
		$max_limit = $post_data['max_limit'];
		$reason = isset($post_data['reason']) ? $post_data['reason'] : '';
		$document = isset($post_data['document']) ? $post_data['document'] : '';
		$set_by = 2;

		$this->db->select('user_self_exclusion_id');
        $this->db->from(USER_SELF_EXCLUSION);
        $this->db->where('user_id', $user_id);
        $this->db->limit(1);
        $query = $this->db->get();
		$modified_date = format_date();
		$data = array();            
		$data['modified_date']    = $modified_date;
		$data['reason']      = $reason;
		$data['document']      = $document;
		$data['max_limit']      = $max_limit;
		$data['requested_max_limit']      = $max_limit;
		$data['set_by']      = $set_by;
		if($query->num_rows() > 0) {
            $row = $query->row_array();
			$user_self_exclusion_id    = $row['user_self_exclusion_id'];
           
            $this->db->where('user_self_exclusion_id', $user_self_exclusion_id);
			$this->db->update(USER_SELF_EXCLUSION,$data);
			
		} else {
			$data['user_id']      = $user_id;
			$this->db->insert(USER_SELF_EXCLUSION, $data);
		}		
	}

	

public function get_contest_details($contest_id_arr){
	$result = $this->db->select('O.reference_id,sum(O.real_amount+O.winning_amount) as real_amount,sum(O.bonus_amount) as bonus_amount,sum(O.points) as coin_amount')
	->from(ORDER.' AS O')
	->join(USER.' AS U','U.user_id = O.user_id','left')
	->where_in('reference_id',$contest_id_arr)
	->where('source',1)
	->where('U.is_systemuser',0)
	->group_by('reference_id')
	->get()->result_array();
	// echo $this->db->last_query();exit;
	return $result;
}

	/**
	 * Used to set default self exclusion for user
	 */
	public function set_default_self_exclusion(){
		$post_data = $this->input->post();
		$user_id = $post_data['user_id'];
		$this->db->where('user_id', $user_id);
        $this->db->delete(USER_SELF_EXCLUSION);	
		return true;
	}

	public function check_duplicate_account($user_bank_details)
	{
		$where = array();
		if($this->app_config['allow_crypto']['key_value'])
		{
			$where = ['UBD.upi_id'=>$user_bank_details["upi_id"]];
		}else{
			$where = [
					'UBD.ac_number'=>$user_bank_details["ac_number"],
					'UBD.ifsc_code'=>$user_bank_details["ifsc_code"],
					];
		}
		$this->db->select('UBD.user_id, U.is_bank_verified')
				->from(USER_BANK_DETAIL ." AS UBD")
				->join(USER ." AS U", "UBD.user_id=U.user_id", "INNER")
				->where($where)
				->where('U.is_bank_verified',1);

		$record = $this->db->get()->result_array();

		if (!empty($record))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function get_user_user_ids(){
        $user_ids = $this->db->select("O.user_id")
        ->from(ORDER.' AS O')
        ->join(USER.' AS U','U.user_id = O.user_id','inner')
        ->where_not_in('source',[1,2,3])
        ->where('U.is_systemuser',0)
        ->group_by('O.user_id')
        ->get()->result_array();
        //echo $this->db->last_query();exit;
        return $user_ids;
        }

    

	

	/**
	 * Used to get user promo code data
	 */
	public function get_user_promo_code_data($user_id) {
        $this->db->select("COUNT(DISTINCT PCE.promo_code_id) AS p_total, SUM(O.real_amount) AS real_amount, SUM(O.bonus_amount) AS bonus_amount",FALSE)
		->from(ORDER." AS O")
		->join(PROMO_CODE_EARNING." PCE", "PCE.promo_code_earning_id = O.source_id and PCE.is_processed='1'","inner")
		->join(PROMO_CODE." PC", "PC.promo_code_id = PCE.promo_code_id","inner")
		->where('O.user_id', $user_id, FALSE)
		->where_in('O.source', array(30,31,32));
		$this->db->order_by('PCE.promo_code_id', 'ASC');		
		$query = $this->db->get();
		$result	= $query->row_array();		
		return $result;
    }

	function get_download_app_graph($post)
    {
        $this->db->select('DATE_FORMAT(IF(U.android_install_date IS NULL,U.ios_install_date,U.android_install_date),"%Y-%m-%d") as main_date,COUNT(DISTINCT U.user_id) as data_value',FALSE)
        ->from(USER.' U')
        ->where('U.status',1)
        ->where("(U.android_install_date IS NOT NULL OR U.ios_install_date IS NOT NULL)");

        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
			$this->db->where("(DATE_FORMAT(U.android_install_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(U.android_install_date, '%Y-%m-%d') <= '".$post['to_date']."') OR (DATE_FORMAT(U.ios_install_date, '%Y-%m-%d') >= '".$post['from_date']."' and DATE_FORMAT(U.ios_install_date, '%Y-%m-%d') <= '".$post['to_date']."')  ");
		}

        $result = $this->db->group_by('main_date')
        ->order_by('main_date','ASC')
        ->get()
        ->result_array();

        if(!empty($post['is_debug']) && $post['is_debug']=='1')
        {
            echo "<pre>";
            echo $this->db->last_query();
            die('dfdf');
        }
        //echo $this->db->last_query();die;
        return  array('result' => $result,
       
    	);    
    }

	function get_download_app_counts($post)
    {
		$this->db->select('COUNT(DISTINCT U.user_id) as new_users,IFNULL(SUM(O.points),0) as coins_distributed',FALSE)
        ->from(USER.' U')
		->join(ORDER.' O',"U.user_id=O.user_id AND O.source=$this->download_app_source","LEFT")
        ->where('U.status',1);
        //->where("(U.android_install_date IS NOT NULL OR U.ios_install_date IS NOT NULL)");

		if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
            $from_date = $post['from_date'].' 00:00:00';
            $to_date = $post['to_date'].' 23:59:59';
            $this->db->where("DATE_FORMAT(U.added_date, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(U.added_date, '%Y-%m-%d %H:%i:%s') <= '".$to_date."'");
        }

		return $result = $this->db->get()->row_array();
	}

	function get_download_app_leaderboard($post)
    {
        $limit		= 50;
		$page		= 0;

        if(isset($post['items_perpage']) && $post['items_perpage'])
		{
			$limit = $post['items_perpage'];
		}

		if(isset($post['current_page']) && $post['current_page'])
		{
			$page = $post['current_page']-1;
		}

        $offset	= $limit * $page;
        $date_str = "";
		$where = array();
        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
            $from_date = $post['from_date'].' 00:00:00';
            $to_date = $post['to_date'].' 23:59:59';
            $where[]="DATE_FORMAT(U.added_date, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(U.added_date, '%Y-%m-%d %H:%i:%s') <= '".$to_date."'";
        }

		$where[] = "(U.android_install_date IS NOT NULL OR U.ios_install_date IS NOT NULL)"; 

		$sql_str = implode(' AND ',$where);
        $pre_sql = "(SELECT U.point_balance,U.user_name,IFNULL(CONCAT_WS(U.first_name,' ',U.last_name),U.user_name) as full_name,U.user_id,U.first_name,U.last_name,U.phone_no,U.email,U.pan_no,
        (RANK() OVER (ORDER BY point_balance DESC)) as rank_value,DATE_FORMAT(IF(U.android_install_date IS NULL,U.ios_install_date,U.android_install_date),'%Y-%m-%d') as download_on,U.added_date,U.user_unique_id
        FROM ".$this->db->dbprefix(USER)." U
        WHERE $sql_str
		AND (U.android_install_date IS NOT NULL OR U.ios_install_date IS NOT NULL)
        GROUP BY U.user_id
        ORDER BY rank_value ASC) 
        ";

        $sql = "SELECT RR.point_balance,RR.user_name,IFNULL(CONCAT_WS(RR.first_name,' ',RR.last_name),RR.user_name) as full_name,RR.user_id,RR.rank_value,RR.download_on,RR.user_unique_id
        FROM $pre_sql RR  ";

        // $this->db->select("RR.cash,
        // RR.bonus,RR.coins,RR.user_name,IFNULL(CONCAT_WS(RR.first_name,' ',RR.last_name),RR.user_name) as full_name,RR.user_id
        // ",FALSE )
        // ->from($pre_sql.' AS RR');
       $where =array();
        if(!empty(isset($post['to_date'])) && !empty(isset($post['from_date'])) && $post['to_date'] != '' && $post['from_date'] != '' )
		{
            $from_date = $post['from_date'].' 00:00:00';
            $to_date = $post['to_date'].' 23:59:59';
			//$this->db->where("DATE_FORMAT(RR.date_added, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(RR.date_added, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ");

            $where[] = "DATE_FORMAT(RR.added_date, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(RR.added_date, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ";
		}

        if(!empty($where))
        {
            $sql.=" WHERE ".implode(' AND  ',$where);
        }

        if(isset($post['keyword']) && $post['keyword'] != "")
		{
			//$this->db->like('LOWER( CONCAT(IFNULL(RR.email,""),IFNULL(RR.first_name,""),IFNULL(RR.last_name,""),IFNULL(RR.user_name,""),IFNULL(RR.phone_no,""),CONCAT_WS(" ",RR.first_name,RR.last_name),IFNULL(RR.pan_no,"")))', strtolower($post['keyword']) );
            $keyword =strtolower($post['keyword']);
            $sql.=" AND LOWER( CONCAT(IFNULL(RR.email,''),IFNULL(RR.first_name,''),IFNULL(RR.last_name,''),IFNULL(RR.user_name,''),IFNULL(RR.phone_no,''),CONCAT_WS(' ',RR.first_name,RR.last_name),IFNULL(RR.pan_no,''))) LIKE '%".$keyword."%' ";
		}
        
        $tempsql = $sql; //to get rows for pagination
		$tempdb = $this->db->query($tempsql);
		$temp_q = $tempdb->result_array();
        $num_rows = $tempdb->num_rows(); 
        $total = isset($num_rows) ? $num_rows : 0; 

		$result = array();
        //$sql.=" GROUP BY RR.user_id ORDER BY RR.$filter_by DESC  ";
        $sql.=" LIMIT $offset,$limit";
        $result = $this->db->query($sql)->result_array();
		//$sql = $this->db->limit($limit,$offset)->get();
			//$result	= $sql->result_array();
			$result=($result)?$result:array();

          // echo $sql;die();


           return array('result'=>$result,'total'=>$total);
		


    }




	/**
	 * [get_all_site_user_detail description]
	 * @MethodName get_all_site_user_detail
	 * @Summary This function used for get all user list and return filter user list 
	 * @param      boolean  [User List or Return Only Count]
	 * @return     [type]
	 */
	public function get_user_tds_report($csv ='0')
	{  	
		
		$sort_field	= 'scheduled_date';
		$sort_order	= 'DESC';
		$limit		= 50;
		$page		= 0;
		$post_data = $this->input->post();
		if($csv=='0'){
			if($post_data['items_perpage'])
			{
				$limit = $post_data['items_perpage'];
			}

			if($post_data['current_page'])
			{
				$page = $post_data['current_page']-1;
			}

			if($post_data['sort_field'] && in_array($post_data['sort_field'],array('module_type','user_id','entity_name')))
			{
				$sort_field = $post_data['sort_field'];
			}

			if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
			{
				$sort_order = $post_data['sort_order'];
			}		
		
			$offset	= $limit * $page;
	    }
		if(isset($csv) && $csv == true){ 
			$tz_diff = get_tz_diff($this->app_config['timezone']);
			$sql = $this->db->select("CASE
			WHEN UTR.module_type = '1' THEN 'DFS'
			WHEN UTR.module_type = '2' THEN 'DFS tournament'
			WHEN UTR.module_type = '3' THEN 'Marketing Leaderboard'
			END AS module_type,UTR.entity_name as match_name,CONVERT_TZ(UTR.scheduled_date, '+00:00', '".$tz_diff."') AS scheduled_date,UTR.total_entry,UTR.total_winning,UTR.net_winning",FALSE);

		}else{
			$sql = $this->db->select("UTR.module_type,UTR.user_id,UTR.entity_name,UTR.total_entry,UTR.total_winning,UTR.net_winning,UTR.scheduled_date",FALSE);
		}
		
		   $this->db->from(USER_TDS_REPORT.' AS UTR')
				->where("UTR.user_id",$post_data['user_id'])
				->order_by('UTR.'.$sort_field, $sort_order);
	
			if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '')
			{
				$this->db->where("DATE_FORMAT(UTR.scheduled_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(UTR.scheduled_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
			}
	    

		
		$tempdb = clone $this->db; //to get rows for pagination
		$tempdb = $tempdb->select("count(*) as total");
		$temp_q = $tempdb->get()->row_array();
		$total = isset($temp_q['total']) ? $temp_q['total'] : 0; 

		// echo $csv; die;

	
		if(isset($csv) && $csv == false){ 
			$this->db->limit($limit,$offset);
		}
		$result= $this->db->get()->result_array();
			// $result	= $sql->result_array();
			$result=($result)?$result:array();
	

		// echo $this->db->last_query(); die;
		return array('result'=>$result,'total'=>$total);
	}


}

/* End of file User_model.php */
/* Location: ./application/models/User_model.php */
