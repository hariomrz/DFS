<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		//Do your magic here
		//$this->admin_id = $this->session->userdata('admin_id');
		// $this->user_db = $this->load->database('user_db',true);
	}

	/**
	 * [get_all_userlocation description]
	 * @MethodName get_all_suserlocation
	 * @Summary This function used for get all user list location wise under reporting section 
	 * @param      boolean  [User List or Return Only Count]
	 * @return     [type]
	 */
	public function get_all_userlocation($count_only=FALSE)
	{
		$sort_field	= 'added_date';
		$sort_order	= 'DESC';
		$limit		= 10;
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

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('added_date','first_name','user_name','email','country','state','city','balance','bonus_balance','status','last_login')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		// DATE_FORMAT(U.added_date,'".MYSQL_DATE_FORMAT."') AS member_since   DATE_FORMAT(U.last_login, '".MYSQL_DATE_TIME_FORMAT."') as last_login

		$sql = $this->db->select("U.user_unique_id, U.user_id, CONCAT_WS(' ',U.first_name,U.last_name) AS name,U.image,MC.country_name,MS.name AS state_name,U.balance,U.bonus_balance,U.email,U.status,U.city,U.added_date,U.user_name,U.last_login",FALSE)
						->from(USER.' AS U')
						->join(MASTER_COUNTRY." AS MC","MC.master_country_id=  U.master_country_id","left")
						->join(MASTER_STATE." AS MS","MS.master_state_id=  U.master_state_id","left")
						->order_by($sort_field, $sort_order);
		if(isset($post_data['country']) && $post_data['country']!="")
		{
			$this->db->where("U.master_country_id",$post_data['country']);
		}
		if(isset($post_data['state']) && $post_data['state'] != '')
		{
			$this->db->where("U.master_state_id",$post_data['state']);
		}

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('city',$post_data['keyword']);
		}
		$tempdb = clone $this->db;
		$query = $this->db->get();
		$total = $query->num_rows();

		$sql = $tempdb->order_by($sort_field, $sort_order)
						->limit($limit,$offset)
						->get();
		$result	= $sql->result_array();

		$records	= array();
		foreach($result as $rs)
		{
			if($rs['image'] == "")
			{
				$rs['image'] = base_url()."assets/images/default_user.png";
			}
			$records[] = $rs;
		}
		
		$result=($records)?$records:array();
		return array('result'=>$result,'total'=>$total);
	}

	public function get_all_userlocation_csv()
	{
		$post_data = $this->input->post();

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('added_date','first_name','user_name','email','country','state','city','balance','bonus_balance','status','last_login')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$sql = $this->db->select("U.user_name,CONCAT_WS(' ',U.first_name,U.last_name) AS name,U.email,
			MC.country_name,MS.name AS state_name,U.city,U.balance,U.bonus_balance,
			DATE_FORMAT(U.added_date,'".MYSQL_DATE_FORMAT."') AS member_since",FALSE)
						->from(USER.' AS U')
						->join(MASTER_COUNTRY." AS MC","MC.master_country_id=  U.master_country_id","left")
						->join(MASTER_STATE." AS MS","MS.master_state_id=  U.master_state_id","left")
						->order_by($sort_field, $sort_order);
		if(isset($post_data['country']) && $post_data['country']!="")
		{
			$this->db->where("U.master_country_id",$post_data['country']);
		}
		if(isset($post_data['state']) && $post_data['state'] != '')
		{
			$this->db->where("U.master_state_id",$post_data['state']);
		}

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('city',$post_data['keyword']);
		}

		$tempdb = clone $this->db;
		//$query = $this->db->get();
		//$total = $query->num_rows();

		$sql = $tempdb->order_by($sort_field, $sort_order)
						->get();
		$result	= $sql->result_array();
		
		//,'total'=>$total
		return array('result'=>$result,'total'=>'');


	}

	/**
	 * [get_all_userlocation description]
	 * @MethodName get_all_suserlocation
	 * @Summary This function used for get all user list location wise under reporting section 
	 * @param      boolean  [User List or Return Only Count]
	 * @return     [type]
	 */
	public function get_all_useractivity($count_only=FALSE)
	{
		$sort_field	= 'last_login';
		$sort_order	= 'DESC';
		$limit		= 10;
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

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('added_date','first_name','user_name','email','country','login_date_time','login_by','balance','bonus_balance','status')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		// DATE_FORMAT(U.added_date,'".MYSQL_DATE_FORMAT."') AS member_since   DATE_FORMAT(U.last_login, '".MYSQL_DATE_TIME_FORMAT."') as last_login

		$sql = $this->db->select("U.user_unique_id, U.user_id, CONCAT_WS(' ',U.first_name,U.last_name) AS name,U.image,MC.country_name,U.balance,U.bonus_balance,U.email,U.status,U.added_date,U.user_name, U.last_login",FALSE)
						->from(USER.' AS U')
						->join(MASTER_COUNTRY." AS MC","MC.master_country_id=  U.master_country_id","left")
						// ->join(ACTIVE_LOGIN." AS AU","AU.user_id=  U.user_id","left")
						->group_by('U.user_id')
						->order_by($sort_field, $sort_order);
		

		if(isset($post_data['activity_duration']) && $post_data['activity_duration']!="")
		{
			
			//if today
			if($post_data['activity_duration'] == 1){
				$today = date('Y-m-d');
				//$today = format_date('today','Y-m-d');
				$this->db->where("DATE_FORMAT(last_login, ('%Y-%m-%d')) = '$today'", NULL, FALSE);
			}

			//if this month
			if($post_data['activity_duration'] == 2){
				$year = date('Y');
				$month = date('m');
				$this->db->where("YEAR(last_login) = '$year' AND MONTH(last_login) = '$month'");
			}

			//if this year
			if($post_data['activity_duration'] == 3){
				$year = date('Y');
				$this->db->where("YEAR(last_login) = '$year'");
			}


			$fromdate	= isset($post_data['fromdate']) ? $post_data['fromdate'] : "";
			$todate		= isset($post_data['todate']) ? $post_data['todate'] : "";
			//if custom date range
			if($post_data['activity_duration'] == 4 && $fromdate != '' && $todate != ''){
				
				$this->db->where("DATE_FORMAT(last_login,'%Y-%m-%d') >= '".$fromdate."' and DATE_FORMAT(last_login,'%Y-%m-%d') <= '".$todate."'");
			}


		}

		
		$tempdb = clone $this->db;
		$total = 0;
		if(isset($post_data['csv']) && $post_data['csv'] == false)
		{
			$query = $this->db->get();
			$total = $query->num_rows();
		}

		if(isset($post_data['csv']) && $post_data['csv'] == false)
		{
			$tempdb->limit($limit,$offset);
		}

		$sql = $tempdb->get();
		$result	= $sql->result_array();
		//echo $this->db->last_query();exit;

		$records	= array();
		foreach($result as $rs)
		{
			if($rs['image'] == "")
			{
				$rs['image'] = base_url()."assets/images/default_user.png";
			}
			$records[] = $rs;
		}
		
		$result=($records)?$records:array();
		return array('result'=>$result,'total'=>$total);
	}

	/**
	 * [get_report_money_paid_by_user description]
	 * @MethodName get_report_money_paid_by_user
	 * @Summary This function used for get all money paid by users
	 * @param      boolean  [User List or Return Only Count]
	 * @return     [type]
	 */
	public function get_report_money_paid_by_user($count_only=FALSE)
	{
		$sort_field	= 'U.added_date';
		$sort_order	= 'DESC';
		$limit		= 10;
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

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('added_date','first_name','user_name','email','country','login_date_time','login_count','bouns_money_paid','real_money_paid','bonus_balance','balance','status','coins_paid','point_balance')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
                
		if($this->input->post('csv'))
		{
			$tz_diff = get_tz_diff($this->app_config['timezone']);
			$select_field = "U.user_unique_id, U.user_id,IFNULL(U.user_name,'-') AS user_name, CONCAT_WS(' ',IFNULL(U.first_name,'-'),U.last_name) AS name,IFNULL(U.phone_no,'-') AS phone_no,IFNULL(U.email,'-') AS email,TRUNCATE(sum(O.points),2) as coins_paid,U.point_balance,TRUNCATE(sum(if(source=1 AND O.type=1,O.real_amount+O.cb_amount,0)),2) as real_money_paid, U.balance, TRUNCATE(sum(if(source=1 AND O.type=1,O.bonus_amount,0)),2) as bouns_money_paid,U.bonus_balance, CONVERT_TZ(U.added_date, '+00:00', '".$tz_diff."') AS member_since,sum(if(source=2 AND O.type=0,O.real_amount+O.cb_amount,0)) as refund_amount,sum(if(source=2 AND O.type=0,O.bonus_amount,0)) as refund_bonus,sum(if(source=2 AND O.type=0,O.points,0)) as refund_coin";
		}
		else
		{
			$select_field = "U.user_unique_id, U.user_id,IFNULL(U.user_name,'-') AS user_name, CONCAT_WS(' ',IFNULL(U.first_name,'-'),U.last_name) AS name,IFNULL(U.phone_no,'-') AS phone_no,IFNULL(U.email,'-') AS email,IFNULL(U.image,'default_user.png') AS image, U.added_date, U.balance,U.bonus_balance,U.point_balance,TRUNCATE(sum(if(source=1 AND O.type=1,O.real_amount+O.cb_amount,0)),2) as real_money_paid, TRUNCATE(sum(if(source=1 AND O.type=1,O.bonus_amount,0)),2) as bouns_money_paid,TRUNCATE(sum(if(source=1 AND O.type=1,O.points,0)),2) as coins_paid, O.status,sum(if(source=2 AND O.type=0,O.real_amount+O.cb_amount,0)) as refund_amount,sum(if(source=2 AND O.type=0,O.bonus_amount,0)) as refund_bonus,sum(if(source=2 AND O.type=0,O.points,0)) as refund_coin";
		}		

		$payment_sql = $this->db->select($select_field,FALSE)
							->from(USER.' AS U')
							->join(ORDER.' AS O','O.user_id = U.user_id  ','LEFT')
							->where("O.status",1)
							->group_by("U.user_id")
							->order_by($sort_field, $sort_order);

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{	
			//IFNULL(email,""),
			$this->db->like('CONCAT(IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.email,""),IFNULL(U.user_name,""),CONCAT_WS(" ",U.first_name,U.last_name))', $post_data['keyword']);
		}
		if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db->where("DATE_FORMAT(U.added_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(U.added_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
		}

		$tempdb = clone $this->db;
		$total = 0;
		
		
		if(!$this->input->post('csv'))
		{
			$query = $this->db->get();
			$total = $query->num_rows();
			$tempdb->limit($limit,$offset);
		}

		$sql = $tempdb->get();
		$payment_result	= $sql->result_array();
		// echo $tempdb->last_query();exit;	
		return array('result'=>$payment_result,'total'=>$total);
	}


	/**
	 * [get_report_user_deposit_amount description]
	 * @MethodName get_report_user_deposit_amount
	 * @Summary This function used for get all deposited by user and which payment method
	 * @param      boolean  [User List or Return Only Count]
	 * @return     [type]
	 */
	public function get_report_user_deposit_amount($count_only=FALSE)
	{
		$sort_field	= 'U.added_date';
		$sort_order	= 'DESC';
		$limit		= 10;
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

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('added_date','U.first_name','user_name','email','country','login_date_time','login_count','status','payment_request','O.date_added','payment_gateway_id')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		if($post_data['csv']){
			$sql = $this->db->select("U.user_unique_id, U.user_id,IFNULL(U.user_name,'-') AS user_name, CONCAT_WS(' ',IFNULL(U.first_name,'-'),U.last_name) AS name,IFNULL(U.phone_no,'-') AS phone,IFNULL(U.email,'-') AS email,IFNULL(T.txn_id,T.transaction_id) AS txn_id, O.real_amount as payment_request,(CASE 
				WHEN T.payment_gateway_id =1 THEN 'payumoney' 
				WHEN T.payment_gateway_id = 2 THEN 'Paytm' 
				WHEN T.payment_gateway_id = 3 THEN 'Mpesa' 
				WHEN T.payment_gateway_id = 5 THEN 'Ipay' 
				WHEN T.payment_gateway_id = 6 THEN 'Paypal' 
				WHEN T.payment_gateway_id = 7 THEN 'Paystack' 
				WHEN T.payment_gateway_id = 8 THEN 'Razorpay' 
				WHEN T.payment_gateway_id = 10 THEN 'Stripe' 
				WHEN T.payment_gateway_id = 13 THEN 'vPay' 
				WHEN T.payment_gateway_id = 14 THEN 'Ifantasy' 
				WHEN T.payment_gateway_id = 15 THEN 'Crypto' 
				WHEN T.payment_gateway_id = 16 THEN 'Cashierpay'				
				WHEN T.payment_gateway_id = 17 THEN 'Cashfree' 
				WHEN T.payment_gateway_id = 18 THEN 'Paylogic' 
				WHEN T.payment_gateway_id = 19 THEN 'BTCpay' 
				WHEN T.payment_gateway_id = 27 THEN 'Directpay' 
				WHEN T.payment_gateway_id = 28 THEN 'Manual'
				WHEN T.payment_gateway_id = 33 THEN 'Phonepe'
				WHEN T.payment_gateway_id = 34 THEN 'Juspay' 
				ELSE 'other' END) AS payment_gateway,O.order_unique_id, U.balance,U.bonus_balance,(CASE WHEN O.status=0 THEN 'PENDING' WHEN O.status=1 THEN 'SUCCESS' WHEN O.status=2 THEN 'FAIL' END) AS status",FALSE);
				if (isset($post_data['csv']) && $post_data['csv'] == true) 	
				{
					$tz_diff = get_tz_diff($this->app_config['timezone']);
					$this->db->select("CONVERT_TZ(U.added_date, '+00:00', '".$tz_diff."') AS added_date,CONVERT_TZ(O.date_added, '+00:00', '".$tz_diff."') AS order_date_added");
				}else{
					$this->db->select("O.date_added AS order_date_added,U.added_date");
				}
		}else{
			$sql = $this->db->select("U.user_unique_id, U.user_id,IFNULL(U.user_name,'-') AS user_name, CONCAT_WS(' ',IFNULL(U.first_name,'-'),U.last_name) AS name,IFNULL(U.phone_no,'-') AS phone,IFNULL(U.email,'-') AS email,IFNULL(T.txn_id,T.transaction_id) AS txn_id, O.real_amount as payment_request,
			(CASE 
			WHEN T.payment_gateway_id =1 THEN 'payumoney' 
			WHEN T.payment_gateway_id = 2 THEN 'Paytm' 
			WHEN T.payment_gateway_id = 3 THEN 'Mpesa' 
			WHEN T.payment_gateway_id = 5 THEN 'Ipay' 
			WHEN T.payment_gateway_id = 6 THEN 'Paypal' 
			WHEN T.payment_gateway_id = 7 THEN 'Paystack' 
			WHEN T.payment_gateway_id = 8 THEN 'Razorpay' 
			WHEN T.payment_gateway_id = 10 THEN 'Stripe' 
			WHEN T.payment_gateway_id = 13 THEN 'vPay' 
			WHEN T.payment_gateway_id = 14 THEN 'Ifantasy' 
			WHEN T.payment_gateway_id = 15 THEN 'Crypto' 
			WHEN T.payment_gateway_id = 16 THEN 'Cashierpay' 
			WHEN T.payment_gateway_id = 17 THEN 'Cashfree' 
			WHEN T.payment_gateway_id = 18 THEN 'Paylogic'  
			WHEN T.payment_gateway_id = 19 THEN 'BTCpay'  
			WHEN T.payment_gateway_id = 27 THEN 'Directpay'  
			WHEN T.payment_gateway_id = 28 THEN 'Manual'
			WHEN T.payment_gateway_id = 33 THEN 'Phonepe'
			WHEN T.payment_gateway_id = 34 THEN 'Juspay'  
			ELSE 'other' END) AS payment_method, U.balance,U.bonus_balance,O.status,U.added_date,
			O.date_added AS order_date_added,T.payment_gateway_id,O.order_unique_id,IFNULL(U.image,'default_user.png') AS image",FALSE);	
		}
                                $sql = $this->db->from(USER.' AS U')
                                ->join(ORDER.' AS O','O.user_id = U.user_id  ','LEFT')
                                ->join(TRANSACTION.' AS T','T.transaction_id = O.source_id  ','LEFT')
                                ->where(array("O.type"=>0 , "O.source"=>7 ))
                                ->order_by($sort_field, $sort_order);

		if(isset($post_data['payment_method']) && $post_data['payment_method'] != '')
		{
			$this->db->where("T.payment_gateway_id",$post_data['payment_method']);
		}

		$status = array('0','1','2');
		if(isset($post_data['status']) && in_array($post_data['status'],$status)){
			$this->db->where("O.status",$post_data['status']);
		}

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{	
			//IFNULL(email,""),
			$this->db->like('LOWER( CONCAT(IFNULL(user_unique_id,""),IFNULL(phone_no,""),IFNULL(first_name,""),IFNULL(last_name,""),IFNULL(user_name,""),CONCAT_WS(" ",first_name,last_name)))', strtolower($post_data['keyword']));
		}

		if(isset($post_data['from_date']) && isset($post_data['to_date']) && $post_data['from_date'] != '' && $post_data['to_date'] != '')
		{
			$this->db->where("DATE_FORMAT(O.date_added,'%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
		}
		
		$tempdb = clone $this->db;
		$total = 0;
		$query = $this->db->get();

		if($this->input->post('csv') == false)
		{
			$total = $query->num_rows();
		}
		
		$detail_rs = $query->result_array();

		// echo $this->db->last_query();exit();

		if($this->input->post('csv') == false)
		{
			$sql = $tempdb->limit($limit,$offset);
		}

		$sql = $tempdb->get();
		$result	= $sql->result_array();
		// echo $this->db->last_query();exit;
		$total_deposit = 0;
		foreach($detail_rs as $deposit){
			if($deposit['status']==1){
				$total_deposit+=$deposit['payment_request'];		
			}
		}
		$total_deposit = number_format($total_deposit,2,".",",");
		//$total_deposit = array_sum(array_column($detail_rs, 'payment_request'));
		// $records	= array();

		// if($this->input->post('csv') == false)
		// {
		// 	foreach($result as $rs)
		// 	{
		// 		if($rs['image'] == "")
		// 		{
		// 			$rs['image'] = base_url()."assets/images/default_user.png";
		// 		}
		// 		$records[] = $rs;
		// 	}
		// 	$result=($records)?$records:array();
		// }
		return array('result'=>$result,'total'=>$total,'total_deposit'=>$total_deposit);
	}
        
        /**
	 * [get_report_user_bonus description]
	 * @MethodName get_report_user_bonus
	 * @Summary This function used for get all earned user bonus 
	 * @param      boolean  [User List or Return Only Count]
	 * @return     [type]
         * 
         * SELECT * FROM `vi_order` WHERE `real_amount` = 0 AND `winning_amount` = 0 AND `source` IN (0,4,12) AND `type` = 0 ORDER BY `vi_order`.`user_id` ASC
	 */
	public function get_report_user_bonus($count_only=FALSE)
	{
		$sort_field = 'user_name';
		$sort_order	= 'ASC';
		$limit		= 10;
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

		if($post_data['sort_field'] && in_array($post_data['sort_field'],array('added_date','first_name','user_name','email','admin_bonus','refferal_bonus','refferal_bonus_pending','signup_bonus','signup_bonus_pending')))
		{
			$sort_field = $post_data['sort_field'];
                        
//                        if($post_data['sort_field'] == 'email')
//                        {
//                            $sort_field = "U.email";
//                            $sort_field_pre = "pre.email";
//                        }
		}

		if($post_data['sort_order'] && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
                
		$this->db->select("U.user_unique_id, U.user_id,U.user_name, U.first_name, CONCAT_WS(' ',U.first_name,U.last_name) AS name,U.balance,U.bonus_balance,U.email,U.added_date AS member_since,O.status,O.source, SUM(O.bonus_amount) AS bonus_amount",FALSE)
                                ->from(USER.' AS U')
                                ->join(ORDER.' AS O','O.user_id = U.user_id  ','LEFT')
                                ->where("O.type",0, FALSE) // Credit
                                ->where_in("O.source", array(0,4,12), FALSE) // 0-Admin, 4-FriendRefferalBonus, 12-Signup Bonus,
                                ->where_in("O.status", array(0,1), FALSE)
                                ->group_by("U.user_id, O.status, O.source", FALSE);
                                //->order_by($sort_field, $sort_order, FALSE);

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{	
			//IFNULL(email,""),
			$this->db->like('CONCAT(IFNULL(first_name,""),IFNULL(last_name,""),IFNULL(user_name,""),IFNULL(U.email,""),CONCAT_WS(" ",first_name,last_name))', $post_data['keyword']);
		}

		if(isset($post_data['from_date']) && isset($post_data['to_date']) && $post_data['from_date'] != '' && $post_data['to_date'] != '')
		{
			$this->db->where("DATE_FORMAT(U.added_date, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(U.added_date, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
		}
                
                $pre_query = $this->db->get_compiled_select();
  
                $query_str = "select pre.*,
                                COALESCE(SUM(CASE when (pre.source = '0' AND pre.status = '1') THEN bonus_amount end), 0) as admin_bonus,
                                COALESCE(SUM(CASE when (pre.source = '0' AND pre.status = '0') THEN bonus_amount end), 0) as admin_bonus_pending,
                                COALESCE(SUM(CASE WHEN (pre.source = '4' AND pre.status = '1') THEN bonus_amount END), 0) AS refferal_bonus,
                                COALESCE(SUM(CASE WHEN (pre.source = '4' AND pre.status = '0') THEN bonus_amount END), 0) AS refferal_bonus_pending,
                                COALESCE(SUM(CASE WHEN (pre.source = '12' AND pre.status = '1') THEN bonus_amount END), 0) AS signup_bonus,
                                COALESCE(SUM(CASE WHEN (pre.source = '12' AND pre.status = '0') THEN bonus_amount END), 0) AS signup_bonus_pending
                                from
                                ($pre_query) as pre
                                group by pre.user_id ORDER BY $sort_field $sort_order";
               
		$temp_query = $query_str;
		$total = 0;
		$query = $this->db->query($query_str);

		if($this->input->post('csv') == false)
		{
			$total = $query->num_rows();
		}
		
		$detail_rs = $query->result_array();

//		echo $this->db->last_query();
//		exit();

		if($this->input->post('csv') == false)
		{
			//$sql = $tempdb->limit($limit,$offset);
			$temp_query .= " LIMIT $offset,$limit";
		}

		//$sql = $tempdb->get();
                $sql = $this->db->query($temp_query);
		$result	= $sql->result_array();
		//echo $this->db->last_query();exit;
		$total_deposit = 0;
		$total_deposit = array_sum(array_column($detail_rs, 'payment_request'));
//		$records	= array();
//
//		foreach($result as $rs)
//		{
//			if($rs['image'] == "")
//			{
//				$rs['image'] = base_url()."assets/images/default_user.png";
//			}
//			$records[] = $rs;
//		}
//		
//		$result=($records)?$records:array();
		return array('result'=>$result,'total'=>$total,'total_deposit'=>$total_deposit);
	}
        
        public function get_users_utilized_bonus($user_ids)
	{
		$post_data = $this->input->post();

		$this->db->select("O.user_id, O.status, O.source, SUM(O.bonus_amount) AS bonus_amount",FALSE)
                                ->from(ORDER.' AS O')
                                ->where("O.type",1) // Debit
                                ->where_in("O.user_id", $user_ids)
                                ->where("O.status",1)
                                ->group_by("O.user_id");
                
                $sql = $this->db->get();
		$result	= $sql->result_array();

		return $result;
	}
	
	
	





	/**
	 * [get_all_contest_report description]
	 * @MethodName get_all_contest_report
	 * @Summary This function used get all contest report by from to To date list
	 * @return     array
	 */
	public function get_all_contest_report()
	{
		$sort_field	= 'first_name';
		$sort_order	= 'DESC';
		$limit		= 10;
		$page		= 0;
		$post_data = $this->input->post();

		if(($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(($post_data['sort_field']) && in_array($post_data['sort_field'],array('user_name', 'first_name', 'balance', 'country_name', 'name', 'league_abbr', 'entry_fee', 'size')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		$from_date = $post_data['from_date'];
		$to_date = $post_data['to_date'];

		$this->db->select("CONCAT(U.first_name, ' ', U.last_name) AS name, U.user_name, MC.country_name, G.contest_unique_id, G.size, G.entry_fee, L.league_abbr, LSC.salary_cap, MNW.master_contest_type_desc AS prize_type,
					CASE
						WHEN G.is_cancel = '1' THEN 'Cancelled'
						WHEN G.prize_distributed = '1' THEN 'Completed'
						ELSE 'In Progress' 
						END AS game_status,
					CASE
						WHEN LM.is_winner = '0' THEN 'No'
						WHEN LM.is_winner = '1' THEN 'Yes'
						END AS won ,
			PC.promo_code, PCE.amount_received, (G.entry_fee - PCE.amount_received) AS promo_code_benefit", FALSE)
				->from(USER." AS U")
				->join(LINEUP_MASTER." LM", "LM.user_id = U.user_id", "inner")
				->join(MASTER_COUNTRY." MC","MC.master_country_id = U.master_country_id", "inner")
				->join(CONTEST." G", "G.contest_unique_id = LM.contest_unique_id", "inner")
				->join(LEAGUE_DURATION . " AS LD", 'LD.league_duration_id = G.league_duration_id', 'LEFT')
				->join(MASTER_DURATION . " AS MD", 'MD.duration_id = LD.duration_id', 'LEFT')
				->join(LEAGUE_SALARY_CAP . " AS LSC", 'LSC.league_salary_cap_id = G.league_salary_cap_id', 'LEFT')
				->join(LEAGUE_CONTEST_TYPE . " AS LNW", 'LNW.league_contest_type_id = G.league_contest_type_id', 'LEFT')
				->join(MASTER_CONTEST_TYPE . " AS MNW", 'MNW.master_contest_type_id = LNW.master_contest_type_id', 'LEFT')
				->join(LEAGUE . " AS L", 'L.league_id = G.league_id', 'LEFT')
				->join(PROMO_CODE . " AS PC", 'PC.promo_code_id = PCE.promo_code_id`', 'LEFT')
				->where("DATE_FORMAT(G.season_scheduled_date, '%Y-%m-%d') BETWEEN '".$from_date."' AND '".$to_date."'");

		$tempdb = clone $this->db;
		$query = $this->db->get();

		$total = $query->num_rows();

		$sql = $tempdb->order_by($sort_field, $sort_order)
						->limit($limit,$offset)
						->get();
		if(!$post_data['csv'])
		{
			$result	= $sql->result_array();
			$result = ($result) ? $result : array();
			return array('result'=>$result,'total'=>$total);
		}
		else
		{			
			$this->load->dbutil();
			$this->load->helper('download');
			$data = $this->dbutil->csv_from_result($query);
			$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "From Date $from_date\nTo Date $to_date\n\n" . html_entity_decode($data);
			$name = 'file.csv';
			force_download($name, $data);
			return exit;
		}
	}


	/**
	 * [get_all_contest_users_report description]
	 * @MethodName get_all_contest_users_report
	 * @Summary This function used get all contest users report by from to to date list
	 * @return     array
	 */
	// public function get_all_contest_users_report()
	// {
	// 	$sort_field	= 'first_name';
	// 	$sort_order	= 'DESC';
	// 	$limit		= 10;
	// 	$page		= 0;
	// 	$post_data = $this->input->post();

	// 	if(($post_data['items_perpage']))
	// 	{
	// 		$limit = $post_data['items_perpage'];
	// 	}

	// 	if(($post_data['current_page']))
	// 	{
	// 		$page = $post_data['current_page']-1;
	// 	}

	// 	$offset	= $limit * $page;
	// 	$from_date = $post_data['from_date'];
	// 	$to_date = $post_data['to_date'];

	// 	#Get total contest created
	// 	$total_contest_created = "SELECT COUNT(contest_id) FROM ".$this->db->dbprefix(CONTEST)." AS G1 WHERE DATE_FORMAT(G1.created_date, '%Y-%m-%d') BETWEEN 
	// 	'".$from_date."' AND '".$to_date."' AND CASE
	// 								WHEN contest_created_by = 'Admin' THEN G1.user_id = '0'
	// 								WHEN contest_created_by = 'User' THEN G1.user_id != '0'
	// 							END";

	// 	#Get total contest completed
	// 	$total_contest_completed = "SELECT COUNT(contest_id) FROM ".$this->db->dbprefix(CONTEST)." AS G2 WHERE  DATE_FORMAT(G2.created_date, '%Y-%m-%d') BETWEEN 
	// 	'".$from_date."' AND '".$to_date."' AND
	// 								CASE
	// 									WHEN contest_created_by = 'Admin' THEN G2.prize_distributed = '1' AND G2.user_id = '0'
	// 									WHEN contest_created_by = 'User' THEN G2.prize_distributed = '1' AND G2.user_id != '0'
	// 								END";

	// 	#Get total contest cancelled
	// 	$total_contest_cancelled = "SELECT COUNT(contest_id) FROM ".$this->db->dbprefix(CONTEST)." AS G3 WHERE DATE_FORMAT(G3.created_date, '%Y-%m-%d') BETWEEN 
	// 	'".$from_date."' AND '".$to_date."' AND
	// 								CASE
	// 									WHEN contest_created_by = 'Admin' THEN G3.is_cancel = '1' AND G3.user_id = '0'
	// 									WHEN contest_created_by = 'User' THEN G3.is_cancel = '1' AND G3.user_id != '0'
	// 								END";

	// 	#Get total contest in progress
	// 	$total_contest_in_progress = "SELECT COUNT(contest_id) FROM ".$this->db->dbprefix(CONTEST)." AS G4 WHERE DATE_FORMAT(G4.created_date, '%Y-%m-%d') BETWEEN 
	// 	'".$from_date."' AND '".$to_date."' AND
	// 								CASE
	// 									WHEN contest_created_by = 'Admin' THEN G4.is_cancel = '0' AND G4.prize_distributed = '0' AND G4.user_id = '0'
	// 									WHEN contest_created_by = 'User' THEN G4.is_cancel = '0' AND G4.prize_distributed = '0' AND G4.user_id != '0'
	// 								END";
		
	// 	$this->db->select("
	// 					CASE
	// 						WHEN G.user_id = '0' THEN 'Admin'
	// 						WHEN G.user_id != '0' THEN 'User'
	// 					END AS contest_created_by, ($total_contest_created) as total_contest_created, ($total_contest_completed) as total_contest_completed, ($total_contest_cancelled) as total_contest_cancelled, ($total_contest_in_progress) as total_contest_in_progress", FALSE)
	// 			->from(CONTEST." AS G")
	// 			->group_by('contest_created_by');

	// 	$tempdb = clone $this->db;
	// 	$query = $this->db->get();

	// 	$total = $query->num_rows();

	// 	$sql = $tempdb->limit($limit,$offset)
	// 				->get();
	// 	if(!$post_data['csv'])
	// 	{
	// 		$result	= $sql->result_array();
	// 		$result = ($result&&$to_date!=''&&$from_date!='') ? $result : array();
	// 		return array('result'=>$result,'total'=>$total);
	// 	}
	// 	else
	// 	{			
	// 		$this->load->dbutil();
	// 		$this->load->helper('download');
	// 		$data = $this->dbutil->csv_from_result($query);
	// 		$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "From Date $from_date\nTo Date $to_date\n\n" . html_entity_decode($data);
	// 		$name = 'file.csv';
	// 		force_download($name, $data);
	// 		return exit;
	// 	}
	// }
	// 
	/**
	 * [revenue report]
	 * @MethodName - revenue_report
	 * @Summary This function used for get all deposited by user and which payment method
	 * @param      boolean  [User List or Return Only Count]
	 * @return     [type]
	 */
	public function revenue_report1($filters=array())
	{
		$duration 	= !empty($filters['duration'])?$filters['duration']:"yearly";
		$sport 		= !empty($filters['sport'])?$filters['sport']:"";
		$game_type 	= isset($filters['game_type'])?$filters['game_type']:"";

		$this->db->select("SUM(transaction_amount) AS amount,YEAR(created_date) AS Year,MONTH(created_date) AS Month,DAY(created_date) AS Day,DAYNAME(created_date) AS day_name,C.site_rake,C.contest_unique_id,C.contest_type",FALSE);
		$this->db->from(PAYMENT_HISTORY_TRANSACTION." AS PHT");

		$this->db->join(CONTEST." AS C","C.contest_unique_id =  PHT.contest_unique_id and C.prize_distributed='1'","INNER");

		$this->db->join(LEAGUE." AS L","L.league_id =  C.league_id");
		$this->db->join(MASTER_SPORTS." AS MS","MS.sports_id =  L.sports_id");
		$this->db->where('master_description_id',1);
		if($game_type!='')
		{
			$this->db->where('contest_type',$game_type);
		}
		if(!empty($sport))
		{
			$this->db->where('MS.sports_id',$sport);
		}

		$report_month = "";

		switch ($duration) 
		{
			case 'monthly':
				$start_date = date('Y-m-d 00-00-00', strtotime('first day of last month'));
				$end_date 	= date('Y-m-d 23-59-59', strtotime('last day of last month'));
				$report_month = date('M-Y', strtotime('first day of last month'));
				$this->db->where('PHT.created_date BETWEEN "'.$start_date.'" AND  "'.$end_date.'"',null,false);
				$this->db->group_by('C.contest_unique_id');
				break;
			case 'weekly':
				$start_date = date('Y-m-d 00-00-00', strtotime('last week monday'));
				$end_date 	= date('Y-m-d 23-59-59', strtotime('last week sunday'));
				$this->db->where('PHT.created_date BETWEEN "'.$start_date.'" AND  "'.$end_date.'"',null,false);
				$this->db->group_by('C.contest_unique_id');
				break;
			case 'quarterly':
			case 'yearly':
				$this->db->group_by('C.contest_unique_id');
				break;
			default:
				break;	
		}
		$this->db->order_by('MONTH(created_date)');
		$result	= $this->db->get()->result_array();
		//print_r($result);die;
		//echo $this->db->last_query();die;

		

		if($duration=='yearly')
		{
			$result = $this->get_year_wise_data($result);
		}
		else if($duration=='quarterly')
		{
			$result = $this->get_quarterly_data($result);
		}
		else if($duration=='monthly')
		{
			$result = $this->get_month_or_week_wise_data($result);
		}
		else if($duration=='weekly')
		{
			$result = $this->get_month_or_week_wise_data($result,TRUE);
		}
		if(!empty($result))
		{
			$series = array('Contest');
			$result['series'] = $series;
		}

		$result['report_month'] = $report_month;
		return $result;
	}

	public function revenue_report($filters=array())
	{
		$duration 	= !empty($filters['duration'])?$filters['duration']:"monthly";
		$sport 		= !empty($filters['sport'])?$filters['sport']:"";
		$game_type 	= isset($filters['game_type'])?$filters['game_type']:"";
		$source_id 	= isset($filters['source_id'])?$filters['source_id']:"";

		$this->db->select("SUM(real_amount) AS amount,YEAR(date_added) AS Year,MONTH(date_added) AS Month,DAY(date_added) AS Day,DAYNAME(date_added) AS day_name,O.source,O.source_id",FALSE);
		$this->db->from(ORDER." AS O");
		$this->db->where('source',1);
		if($source_id!='')
		{
			$this->db->where_in('source_id',$source_id);
		}

		$report_month = "";

		switch ($duration) 
		{
			case 'monthly':
				$start_date = date('Y-m-d 00-00-00', strtotime('first day of last month'));
				$end_date 	= date('Y-m-d 23-59-59', strtotime('last day of last month'));
				$report_month = date('M-Y', strtotime('first day of last month'));
				$this->db->where('O.date_added BETWEEN "'.$start_date.'" AND  "'.$end_date.'"',null,false);
				$this->db->group_by('O.source_id');
				break;
			case 'weekly':
				$start_date = date('Y-m-d 00-00-00', strtotime('last week monday'));
				$end_date 	= date('Y-m-d 23-59-59', strtotime('last week sunday'));
				$this->db->where('O.date_added BETWEEN "'.$start_date.'" AND  "'.$end_date.'"',null,false);
				$this->db->group_by('O.source_id');
				break;
			case 'quarterly':
			case 'yearly':
				$this->db->group_by('O.source_id');
				break;
			default:
				break;	
		}
		$this->db->order_by('MONTH(date_added)');
		$result	= $this->db->get()->result_array();
		//print_r($result);die;
		//echo $this->db->last_query();die;

		

		if($duration=='yearly')
		{
			$result = $this->get_year_wise_data($result,$filters);
		}
		else if($duration=='quarterly')
		{
			$result = $this->get_quarterly_data($result,$filters);
		}
		else if($duration=='monthly')
		{
			$result = $this->get_month_or_week_wise_data($result,FALSE,$filters);
		}
		else if($duration=='weekly')
		{
			$result = $this->get_month_or_week_wise_data($result,TRUE,$filters);
		}
		if(!empty($result))
		{
			$series = array('H2H','Multiple user','50/50','Uncapped');
			$series = array('Contest');
			$result['series'] = $series;
			
		}
                
		$result['report_month'] = $report_month;
		return $result;
	}

	function get_year_wise_data($data,$filters)
	{
		$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
		$return_array = array('column'=>$months,'profit'=>array());
		$temp_array = array();
		$total_profit = 0;
		foreach ($months as $key => $value) 
		{
			$temp_profit1 = 0;
			$temp_profit2 = 0;
			$temp_profit3 = 0;
			$temp_profit4 = 0;
			if($data){
				foreach ($data as $k => $v) 
				{
					if($v['Month']== ($key+1))
					{
						$source_key = array_search($v['source_id'], $filters['source_id']);
						$site_rake = $filters['site_rake'][$source_key];
						$temp_profit1 = $temp_profit1 + (($v['amount']*$site_rake)/100);	
					}
					/*else if($v['Month']== ($key+1) && $v['contest_type']==1)
					{
						$temp_profit2 = $temp_profit2 + (($v['amount']*$v['site_rake'])/100);	
					}
					else if($v['Month']== ($key+1) && $v['contest_type']==2)
					{
						$temp_profit3 = $temp_profit3 + (($v['amount']*$v['site_rake'])/100);	
					}
					else if($v['Month']== ($key+1) && $v['contest_type']==3)
					{
						$temp_profit4 = $temp_profit4 + (($v['amount']*$v['site_rake'])/100);	
					}*/
				}
			}
			//$total_profit = ($total_profit + $temp_profit1 + $temp_profit2 + $temp_profit3 + $temp_profit4);
			$total_profit = ($total_profit + $temp_profit1 );
			//$temp_array['profit'][] = array($temp_profit1,$temp_profit2,$temp_profit3,$temp_profit4);	
			$temp_array['profit'][] = array(number_format($temp_profit1,2,".",""));	
		}
		foreach ($temp_array['profit'] as $k => $v) 
		{
			$return_array['profit'][0][] = $v[0];
			/*$return_array['profit'][1][] = $v[1];
			$return_array['profit'][2][] = $v[2];
			$return_array['profit'][3][] = $v[3];*/	
			
		}
		array_values($return_array['profit']);
		$return_array['total_profit'] = $total_profit;
		return $return_array;
	}

	function get_quarterly_data($data,$filters)
	{
		$quarterly = array(array(1,2,3),array(4,5,6),array(7,8,9),array(10,11,12));
		$return_array = array('column'=>array(1,2,3,4),'profit'=>array());
		$temp_array = array();
		$total_profit = 0;
		foreach ($quarterly as $key => $value) 
		{
			$temp_profit1 = 0;
			$temp_profit2 = 0;
			$temp_profit3 = 0;
			$temp_profit4 = 0;
			foreach ($data as $k => $v) 
			{
				if(in_array($v['Month'], $value) )
				{
					$source_key = array_search($v['source_id'], $filters['source_id']);
					$site_rake = $filters['site_rake'][$source_key];
					$temp_profit1 = $temp_profit1 + (($v['amount']*$site_rake)/100);	
				}
				/*else if(in_array($v['Month'], $value) && $v['contest_type']==1)
				{
					$temp_profit2 = $temp_profit2 + (($v['amount']*$v['site_rake'])/100);	
				}
				else if(in_array($v['Month'], $value) && $v['contest_type']==2)
				{
					$temp_profit3 = $temp_profit3 + (($v['amount']*$v['site_rake'])/100);	
				}
				else if(in_array($v['Month'], $value) && $v['contest_type']==3)
				{
					$temp_profit4 = $temp_profit4 + (($v['amount']*$v['site_rake'])/100);	
				}*/
			}
			//$total_profit = ($total_profit + $temp_profit1 + $temp_profit2 + $temp_profit3 + $temp_profit4);
			$total_profit = ($total_profit + $temp_profit1 );
			//$temp_array['profit'][] = array($temp_profit1,$temp_profit2,$temp_profit3,$temp_profit4);	
			$temp_array['profit'][] = array(number_format($temp_profit1,2,".",""));	
		}
		foreach ($temp_array['profit'] as $k => $v) 
		{
			$return_array['profit'][0][] = $v[0];
			/*$return_array['profit'][1][] = $v[1];
			$return_array['profit'][2][] = $v[2];
			$return_array['profit'][3][] = $v[3];	*/
		}
		array_values($return_array['profit']);
		$return_array['total_profit'] = $total_profit;

		$return_array['column'] = array('Jan-Mar','Apr-Jun','Jul-Sep','Oct-Dec');

		return $return_array;
	}

	function get_month_or_week_wise_data($data,$is_week_data=false,$filters)
	{
		if($is_week_data)
		{
			$no_of_days = 7;
			$days = array(
			    'Sunday',
			    'Monday',
			    'Tuesday',
			    'Wednesday',
			    'Thursday',
			    'Friday',
			    'Saturday',
			);
		}
		else
		{
			$last_month 	= date('m', strtotime('last day of last month'));
			$year 			= date('Y', strtotime('last day of last month'));
			$no_of_days 	=  cal_days_in_month(CAL_GREGORIAN, $last_month , $year);
		}
		$month_dates = array();
		for ($i=1; $i <=$no_of_days ; $i++) { 
			$month_dates[] = $i;
		}
		$return_array = array('column'=>$month_dates,'profit'=>array());
		if($is_week_data)
		{
			$return_array = array('column'=>$days,'profit'=>array());
		}
		$temp_array = array();
		$total_profit = 0;
		if($is_week_data)
		{
			foreach ($days as $key => $value) 
			{
				$temp_profit1 = 0;
				/*$temp_profit2 = 0;
				$temp_profit3 = 0;
				$temp_profit4 = 0;*/
				foreach ($data as $k => $v) 
				{
					if($v['day_name']== $value )
					{
						$source_key = array_search($v['source_id'], $filters['source_id']);
						$site_rake = $filters['site_rake'][$source_key];
						$temp_profit1 = $temp_profit1 + (($v['amount']*$site_rake)/100);
					}
					/*else if($v['day_name']== $value && $v['contest_type']==1)
					{
						$temp_profit2 = $temp_profit2 + (($v['amount']*$v['site_rake'])/100);	
					}
					else if($v['day_name']== $value && $v['contest_type']==2)
					{
						$temp_profit3 = $temp_profit3 + (($v['amount']*$v['site_rake'])/100);	
					}
					else if($v['day_name']== $value && $v['contest_type']==3)
					{
						$temp_profit4 = $temp_profit4 + (($v['amount']*$v['site_rake'])/100);	
					}*/
				}
				//$total_profit = ($total_profit + $temp_profit1 + $temp_profit2 + $temp_profit3 + $temp_profit4);
				$total_profit = ($total_profit + $temp_profit1 );
				//$temp_array['profit'][] = array($temp_profit1,$temp_profit2,$temp_profit3,$temp_profit4);	
				$temp_array['profit'][] = array(number_format($temp_profit1,2,".",""));	
			}
		}
		else
		{
			foreach ($month_dates as $key => $value) 
			{
				$temp_profit1 = 0;
				/*$temp_profit2 = 0;
				$temp_profit3 = 0;
				$temp_profit4 = 0;*/
				foreach ($data as $k => $v) 
				{
					if($v['Day']== ($key+1) )
					{
						//$temp_profit1 = $temp_profit1 + (($v['amount']*$v['site_rake'])/100);	
						$source_key = array_search($v['source_id'], $filters['source_id']);
						$site_rake = $filters['site_rake'][$source_key];
						$temp_profit1 = $temp_profit1 + (($v['amount']*$site_rake)/100);
					}
					/*else if($v['Day']== ($key+1) && $v['contest_type']==1)
					{
						$temp_profit2 = $temp_profit2 + (($v['amount']*$v['site_rake'])/100);	
					}
					else if($v['Day']== ($key+1) && $v['contest_type']==2)
					{
						$temp_profit3 = $temp_profit3 + (($v['amount']*$v['site_rake'])/100);	
					}
					else if($v['Day']== ($key+1) && $v['contest_type']==3)
					{
						$temp_profit4 = $temp_profit4 + (($v['amount']*$v['site_rake'])/100);	
					}*/
				}
				$total_profit = ($total_profit + $temp_profit1 );
				$temp_array['profit'][] = array(number_format($temp_profit1,2,".",""));	
			}
		}
		
		foreach ($temp_array['profit'] as $k => $v) 
		{
			$return_array['profit'][0][] = $v[0];
			/*$return_array['profit'][1][] = $v[1];
			$return_array['profit'][2][] = $v[2];
			$return_array['profit'][3][] = $v[3];	*/
			
		}
		array_values($return_array['profit']);
		$return_array['total_profit'] = $total_profit;
		return $return_array;
	}

/**
 * NEW FUNCTION
 */
	public function get_referral_report()
	{
		$sort_field	= 'user_name';
		$sort_order	= 'ASC';
		$limit		= 10;
		$page		= 0;
		$post_data = $this->input->post();

		if(($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(($post_data['sort_field']) && in_array($post_data['sort_field'],array('user_name','email','name')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		$from_date = $post_data['from_date'];
		$to_date = $post_data['to_date'];

		$this->db->select("U.user_id,U.user_unique_id,CONCAT(U.first_name, ' ', U.last_name) AS name, U.user_name,U.email,U.phone_no,count(UAH.user_affiliate_history_id) AS registered,SUM(UAH.user_bonus_cash) AS user_bonus_cash,SUM(UAH.user_real_cash) AS user_real_cash,SUM(UAH.user_coin) AS user_coin", FALSE) 
		->from(USER . ' U')
		->join(USER_AFFILIATE_HISTORY." UAH","U.user_id = UAH.user_id","INNER")
		->where_in("UAH.affiliate_type",[1,19,20,21]);

		if($from_date && $to_date)
		{
			$this->db->where("DATE_FORMAT(UAH.created_date,'%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(UAH.created_date,'%Y-%m-%d %H:%i:%s') <= '".$to_date."'");
		}
		$this->db->group_by('U.user_id');

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('CONCAT(IFNULL(U.email,""),IFNULL(U.user_name,""))', $post_data['keyword']);
		}

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

	public function get_referral_ids($keyword)
	{
		$this->db->select("U.user_id", FALSE) 
			  	->from(USER . ' U')
			  	//->join(REFERRAL . ' RU', 'U.user_id = RU.user_id', 'inner')
			  	->join(USER_AFFILIATE_HISTORY." UAH","U.user_id = UAH.friend_id","INNER");
			  	
		$post_data = $this->input->post();
		$from_date = $post_data['from_date'];
		$to_date = $post_data['to_date'];	
		if($from_date && $to_date){

			$this->db->where("DATE_FORMAT(UAH.created_date, '%Y-%m-%d %H:%i:%s') BETWEEN '".$from_date."' AND '".$to_date."'");
		}

			$this->db->where("UAH.affiliate_type",1);
				//->where('R.referral_invite_sent', 1)
			$this->db->group_by('U.user_id');

		if(isset($keyword) && $keyword != "")
		{
			$this->db->like('CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""))', $keyword);
		}

		$result = $this->db->get()->result_array();
		
		if(!empty($result))
		{
			$result = array_column($result, "user_id");
		}
		
		return $result;
	}

	public function get_referral_report_xls()
	{
		$post_data = $this->input->post();
		$from_date = $post_data['from_date'];
		$to_date = $post_data['to_date'];

		$html_string_f = "<table>";
		$html_string_f.="<th>User Name</th>
						<th>Full Name</th>
						<th>Phone</th>
						<th>Email</th>
						<th>Referral Registered</th>
						<th>Referral Cash</th>						
						<th>Referral Bonus</th>						
						<th>Referral Coin</th>						
								  
            			";
			$this->db->select("U.user_id, IFNULL(U.user_name,'-') as user_name,CONCAT(U.first_name, ' ', U.last_name) AS name,IFNULL(U.phone_no,'-') AS phone_no,IFNULL(U.email,'-') AS email,count(UAH.user_affiliate_history_id) AS total_invited,SUM(IF(UAH.friend_id,1,0))  AS total_registered", FALSE) 
			  	->from(USER . ' U')
			  	->join(USER_AFFILIATE_HISTORY." UAH","U.user_id = UAH.user_id","INNER")
			  	->where_in("UAH.affiliate_type",[1,19,20,21])
				->group_by('U.user_id')
				->order_by('U.user_name','DESC'); 

				if($from_date && $to_date)
			{
				$this->db->where("DATE_FORMAT(UAH.created_date,'%Y-%m-%d') >= '".format_date($from_date,'Y-m-d')."' and DATE_FORMAT(UAH.created_date,'%Y-%m-%d') <= '".format_date($to_date,'Y-m-d')."'");
			}

		$query = $this->db->get();
		
		$result = $query->result_array();

		foreach ($result as $key => $value) 
			{
				$html_string = '';
				
				// $data 							 = $this->get_referral_friends($result[$key]['user_id']);
				// $result[$key]['referral_detail'] = $data['referral_data'];
				// $result[$key]['TotalRegistered'] = (isset($data['total_registered'])) ? $data['total_registered'] : 0 ;
				$result[$key]['TotalReferral']   = $value['total_invited'];

				$total_earned_info  = $this->get_total_earned_referral_bonus_by_user($result[$key]['user_id']);

				$result[$key]['referal_bonus_amount'] = (!empty($total_earned_info['user_bonus_cash'])) ? $total_earned_info['user_bonus_cash'] : 0 ;
				$result[$key]['referal_real_amount'] = (!empty($total_earned_info['user_real_cash'])) ? $total_earned_info['user_real_cash'] : 0;
				$result[$key]['referal_coin_amount'] = (!empty($total_earned_info['user_coin'])) ? $total_earned_info['user_coin'] : 0;


				$html_string	.="<tr>";
				$html_string	.="<td>".$value['user_name']."</td>";
				$html_string	.="<td>".$value['name']."</td>";
				$html_string	.="<td>".$value['phone_no']."</td>";
				$html_string	.="<td>".$value['email']."</td>";
				$html_string	.="<td>".$result[$key]['TotalReferral']."</td>";
				// $html_string	.="<td>".$result[$key]['TotalRegistered'] ."</td>";
				$html_string	.="<td>".$result[$key]['referal_real_amount']."</td>";
				$html_string	.="<td>".$result[$key]['referal_bonus_amount']."</td>";
				$html_string	.="<td>".$result[$key]['referal_coin_amount']."</td>";
				$html_string	.="</tr>";

				$html_string_f .= $html_string;
			}

			$html_string_f .= "</table>";

			//echo $html_string_f;die;

			$file='referral_report.xls';
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=$file");

			// echo "<pre>";
			echo $html_string_f;
			exit;
	
	}

	public function get_referral_report_details_xls()
	{

		$html_string_f = "<table>";
		$html_string_f.="<th>User Name</th>
						<th>Registered</th>
    					<th>Date</th>
    					<th>Earned Bonus</th>
    					<th>Earned Real Cash</th>
    					<th>Earned Coins</th>
            					
            			";

			$this->db->select("U.user_id,CONCAT(U.first_name, ' ', U.last_name) AS name, U.user_name,U.email,count(UAH.user_affiliate_history_id) AS registered", FALSE) 
			  	->from(USER . ' U')
			  	->join(USER_AFFILIATE_HISTORY." UAH","U.user_id = UAH.user_id","INNER")
			  	->where("UAH.affiliate_type",1)
				->group_by('U.user_id')
				->order_by('U.user_name','DESC');

		$query = $this->db->get();
		$result = $query->result_array();
		$user_ids = array_column($result, 'user_id');
		$user_details = array_column($result, NULL,'user_id');
		$data = $this->get_referral_friends_by_user_ids($user_ids,$user_details);

			foreach ($data as $key => $value) 
			{
				$html_string = '';
				$html_string	.="<tr>";
				$html_string	.="<td>".$value['user_name']."</td>";
				$html_string	.="<td>".$value['friend_user_name']."</td>";
				$html_string	.="<td>".$value['added_date']."</td>";
				//$html_string	.="<td>".$result[$key]['TotalRegistered'] ."</td>";
				$html_string	.="<td>".$value['friend_bonus_cash']."</td>";
				$html_string	.="<td>".$value['earned_real']."</td>";
				$html_string	.="<td>".$value['earned_coin']."</td>";
				$html_string	.="</tr>";

				$html_string_f .= $html_string;
			}

			$html_string_f .= "</table>";

			//echo $html_string_f;die;

			$file='referral_report.xls';
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=$file");

			// echo "<pre>";
			echo $html_string_f;
			exit;
	
	}

	public function get_referral_friends($UserID)
	{
		$post_data = $this->input->post();
		$from_date = $post_data['from_date'];
		$to_date = $post_data['to_date'];
		
		$sql = $this->db->select("UAH.user_affiliate_history_id,IFNULL(UAH.friend_id,'') AS friend_id,UAH.status,IFNULL(U.first_name,'') as first_name,IFNULL(U.last_name,'') as last_name,IFNULL(U.user_name,'') as user_name,U.image,IFNULL(U.added_date,'') as added_date,IF(UAH.friend_email!='',UAH.friend_email,U.email) as friend_email,UAH.friend_bonus_cash",FALSE)
			->from(USER_AFFILIATE_HISTORY." UAH")
			->join(USER." U","U.user_id = UAH.friend_id","LEFT")
			->where_in("UAH.affiliate_type",[1,19,20,21])
			->where("UAH.user_id",$UserID);
			if($from_date && $to_date)
			{
				$this->db->where("DATE_FORMAT(UAH.created_date,'%Y-%m-%d') >= '".format_date($from_date,'Y-m-d')."' and DATE_FORMAT(UAH.created_date,'%Y-%m-%d') <= '".format_date($to_date,'Y-m-d')."'");
			}
		$this->db->group_by("UAH.friend_id"); 
		$this->db->order_by('U.added_date', 'DESC'); 
		//$this->db->limit($limit, $offset);
		$result = $this->db->get()->result_array();
		// echo $this->db->last_query();die;
		$total_registered = 0;
		$total_sent = count($result);
		$referral_data = array();

		// echo '<pre>';print_r($result);die;
        if(!empty($result))
        {
        	foreach($result as $rkey => $rvalue)
        	{
        		if(empty($rvalue['friend_id']))
        		{
        			$rvalue['status'] 		= 0;
        			$rvalue['earned_bonus'] = 0;
        			$rvalue['earned_real'] = 0;
        			$rvalue['earned_coin'] = 0;
        		}	
        		else
        		{
        			$total_registered++;
        			$earned_bonus_info = $this->get_earned_contest_bonus($UserID,$rvalue['friend_id']);
        			//$rvalue['earned_bonus'] = $this->get_earned_contest_bonus($UserID,$rvalue['friend_id']);

        			$rvalue['earned_bonus']=(!empty($earned_bonus_info['friend_bonus_cash'])) ? $earned_bonus_info['friend_bonus_cash']:0;
        			$rvalue['earned_real']=(!empty($earned_bonus_info['friend_real_cash'])) ? $earned_bonus_info['friend_real_cash']:0;
        			$rvalue['earned_coin']=(!empty($earned_bonus_info['friend_coin'])) ? $earned_bonus_info['friend_coin']:0;
        			// $rvalue['status'] = ($value['status']) ? $value['status']:1;
        		}	
        		$rvalue['added_date'] = date(PHP_DATE_FORMAT,strtotime($rvalue['added_date']));
        		$referral_data[] = $rvalue;
        	}	
        }

       	$return_arr = array('total_sent'=>$total_sent,'total_registered'=>$total_registered,'referral_data'=>$referral_data);
       //echo '<pre>';print_r($return_arr);die;
        return $return_arr;
	}


	public function get_referral_friends_by_user_ids($UserIDs,$user_details)
	{
		
		$sql = $this->db->select("UAH.user_affiliate_history_id,IFNULL(UAH.friend_id,'') AS friend_id,UAH.user_id,UAH.status,IFNULL(U.first_name,'') as first_name,IFNULL(U.last_name,'') as last_name,IFNULL(U.user_name,'') as user_name,U.image,IFNULL(U.added_date,'') as added_date,IF(UAH.friend_email!='',UAH.friend_email,U.email) as friend_email,UAH.friend_bonus_cash",FALSE)
			->from(USER_AFFILIATE_HISTORY." UAH")
			->join(USER." U","U.user_id = UAH.friend_id","LEFT")
			->where("UAH.affiliate_type",1)
			->where_in("UAH.user_id",$UserIDs);
		
		$this->db->group_by("UAH.friend_id"); 
		$this->db->order_by('U.added_date', 'DESC'); 
		
		$result = $this->db->get()->result_array();


		$total_registered = 0;
		$total_sent = count($result);
		$referral_data = array();

		$friend_ids = array_column($result, 'friend_id');
		$friend_details = array_column($result, NULL,'friend_id');

		$total_data = $this->get_earned_contest_bonus_by_user_ids($UserIDs,$friend_ids);

        if(!empty($result))
        {
        	
        	foreach($total_data  as $rkey => $rvalue)
        	{
        		if(empty($rvalue['friend_id']))
        		{
        			$rvalue['status'] 		= 0;
        			$rvalue['earned_bonus'] = 0;
        			$rvalue['earned_real'] = 0;
        			$rvalue['earned_coin'] = 0;
        		}	
        		else
        		{
        			$total_registered++;
        			
        			$rvalue['earned_bonus']=(!empty($rvalue['friend_bonus_cash'])) ? $rvalue['friend_bonus_cash']:0;
        			$rvalue['earned_real']=(!empty($rvalue['friend_real_cash'])) ? $rvalue['friend_real_cash']:0;
        			$rvalue['earned_coin']=(!empty($rvalue['friend_coin'])) ? $rvalue['friend_coin']:0;
        			$rvalue['status'] = 1;
        		}	
        		$rvalue['added_date'] = date(PHP_DATE_FORMAT,strtotime($rvalue['created_date']));

        		if(!empty($user_details[$rvalue['user_id']]))
        		{
        			$rvalue['user_name'] = $user_details[$rvalue['user_id']]['user_name'];
        		}

        		if(!empty($user_details[$rvalue['friend_id']]))
        		{
        			$rvalue['friend_user_name'] = $user_details[$rvalue['friend_id']]['user_name'];
        		}

        		if(!empty($friend_details[$rvalue['user_id']]))
        		{
        			$rvalue['user_name'] = $friend_details[$rvalue['user_id']]['user_name'];
        		}

        		if(!empty($friend_details[$rvalue['friend_id']]))
        		{
        			$rvalue['friend_user_name'] = $friend_details[$rvalue['friend_id']]['user_name'];
        		}
        		$referral_data[] = $rvalue;
        	}	
        }
        
        return $referral_data;
	}

	public function get_earned_contest_bonus($user_id,$friend_id)
	{
		$sql = $this->db->select("SUM(UAH.friend_bonus_cash) AS friend_bonus_cash,SUM(UAH.friend_real_cash) AS friend_real_cash,SUM(UAH.friend_coin) AS friend_coin")
						->from(USER_AFFILIATE_HISTORY." UAH")
						->where_in("UAH.affiliate_type",array(1,5,10,11,12))
						->where("UAH.user_id",$user_id)
						->where("UAH.friend_id",$friend_id)
						->where("UAH.is_referral",1)
						->get();
		if($sql->num_rows() > 0)
		{
			$row = $sql->row_array();
			return $row;
		}
		return $row;				
	}

	public function get_earned_contest_bonus_by_user_ids($user_ids,$friend_ids)
	{
		$sql = $this->db->select("SUM(UAH.friend_bonus_cash) AS friend_bonus_cash,SUM(UAH.friend_real_cash) AS friend_real_cash,SUM(UAH.friend_coin) AS friend_coin,UAH.user_id,UAH.friend_id,UAH.created_date")
						->from(USER_AFFILIATE_HISTORY." UAH")
						->where_in("UAH.affiliate_type",array(1,5,10,11,12))
						->where_in("UAH.user_id",$user_ids)
						->where_in("UAH.friend_id",$friend_ids)
						->where("UAH.is_referral",1)
						->group_by("UAH.user_id")
						->group_by("UAH.friend_id")
						->order_by("UAH.created_date","DESC")
						->get();
		
		return $sql->result_array();				
	}

	public function get_total_earned_referral_bonus_by_user($user_id)
	{

		$post_data = $this->input->post();
		$from_date = ($post_data['from_date'])?$post_data['from_date']:'';
		$to_date = ($post_data['to_date'])?$post_data['to_date']:'';

		$sql = $this->db->select("SUM(UAH.user_bonus_cash) AS user_bonus_cash,SUM(UAH.user_real_cash) AS user_real_cash,SUM(UAH.user_coin) AS user_coin")
						->from(USER_AFFILIATE_HISTORY." UAH")
						->where("UAH.is_referral",1)
						->where_in("UAH.affiliate_type",array(1,5,10,11,12,19,20,21))
						->where("UAH.user_id",$user_id)
						->where("UAH.status",1);
						if($from_date && $to_date)
						{
							$this->db->where("DATE_FORMAT(UAH.created_date, '%Y-%m-%d %H:%i:%s') BETWEEN '".$from_date."' AND '".$to_date."'");
						}
						$sql = $this->db->get();
		if($sql->num_rows() > 0)
		{
			$row = $sql->row_array();
			// echo $this->db->last_query();exit;
			return $row;	
		}
		return array();
	}

	public function get_all_friend_referral($UserID)
	{
			$result = $this->db->select('U.email,U.first_name,U.last_name,U.user_name,R.*, DATE_FORMAT(R.date, "'.MYSQL_DATE_FORMAT.'") AS registration_date',false)
						   ->from(REFERRAL . ' R')
						   ->join(USER . ' U', 'U.user_id = R.friend_id', 'LEFT')
						   ->where('R.user_id', $UserID)
						   // ->where('R.friend_id>',0 )
						   //->where('R.referral_invite_sent', 1)
						   //->limit(REFERRAL_FRIEND_LIMIT, $offset)
						   ->get()->result_array();
		//echo $this->db->last_query();
		//exit();				   
		if(empty($result)) return array();
						   
		return $result;
	}

	public function get_contest_prize_detail($contest_ids)
	{

		// +ROUND(IFNULL(sum(IF(ORD.source=1,ORD.winning_amount,0)),'0'),2)

		$query = $this->db->select("(ROUND(IFNULL(sum(IF(ORD.source=1,ORD.real_amount+ORD.cb_amount,0)),'0'),2)) as total_join_real_amount,			

		ROUND(IFNULL(sum(IF(ORD.source=1,ORD.bonus_amount,0)),'0'),2) as total_join_bonus_amount,			

		ROUND(IFNULL(sum(IF(ORD.source=1,ORD.points,0)),'0'),2) as total_join_coin_amount,
		ROUND(IFNULL(sum(IF(ORD.source=1,ORD.winning_amount,0)),'0'),2) as total_join_winning_amount,

		ROUND(IFNULL(sum(IF(ORD.source=3,ORD.winning_amount,0)),'0'),2) as total_win_winning_amount,
		ROUND(IFNULL(sum(IF(ORD.source=3,ORD.points,0)),'0'),2) as total_win_coins,
		ROUND(IFNULL(sum(IF(ORD.source=3,ORD.bonus_amount,0)),'0'),2) as total_win_bonus,
		ROUND(IFNULL(sum(IF(ORD.source=3 AND U.is_systemuser=0,ORD.winning_amount,0)),'0'),2) as total_win_amount_to_real_user,


			ORD.reference_id as contest_id",FALSE)
				->from(ORDER." AS ORD")
				->join(USER.' U','U.user_id=ORD.user_id',"INNER")
				->where('(ORD.source = 1 or ORD.source = 3) AND ORD.status = 1') // join game  AND Debit and success		
				->where_in('ORD.reference_id',$contest_ids)
				//->where('U.is_systemuser',0)		
				->group_by('ORD.reference_id')
				->get()->result_array();
		// echo $this->db->last_query();exit;
		return $query;					
	}

	public function get_contest_promo_code_entry($contest_uids)
	{
		$query = $this->db->select("ROUND(IFNULL(sum(amount_received),'0'),2) as promocode_entry_fee_real,contest_unique_id",FALSE)
				->from(PROMO_CODE_EARNING)
				->where_in('contest_unique_id',$contest_uids)
				->where('is_processed',"1")		
				->group_by('contest_unique_id')
				->get()->result_array();
		// echo $this->db->last_query();exit;
		return $query;					
	}

	public function get_season_report_data($contest_data)
	{
		//echo "<pre>";print_r($contest_data);
		$report_data = array();
		$report_data['total_contest'] = count($contest_data['contest_ids']);
		$report_data['total_entries'] = count($contest_data['lmc_ids']);
		$report_data['total_entry_fee'] = 0;
		$report_data['total_refund_fee'] = 0;
		$report_data['total_real_fee'] = 0;
		$report_data['total_bonus_fee'] = 0;
		$report_data['total_winning_fee'] = 0;
		$report_data['total_winning'] = 0;
		$report_data['total_cancel_fee'] = 0;
		$report_data['total_cancel_bonus_fee'] = 0;
		$report_data['total_cancel_winning_fee'] = 0;
		$report_data['net_profit'] = 0;

		if(!empty($contest_data['lmc_ids'])){
			$result = $this->db->select("ROUND(sum(IF(ORD.source=1,ORD.real_amount,0)),2) as total_join_real_amount,
				ROUND(sum(IF(ORD.source=1,ORD.bonus_amount,0)),2) as total_join_bonus_amount,
				ROUND(sum(IF(ORD.source=1,ORD.winning_amount,0)),2) as total_join_winning_amount,
				ROUND(sum(IF(ORD.source=2,ORD.real_amount,0)),2) as total_refund_real_amount,
				ROUND(sum(IF(ORD.source=2,ORD.bonus_amount,0)),2) as total_refund_bonus_amount,
				ROUND(sum(IF(ORD.source=2,ORD.winning_amount,0)),2) as total_refund_winning_amount,
				ROUND(sum(IF(ORD.source=3,ORD.winning_amount,0)),2) as total_winning_amount,
				",FALSE)
					->from(ORDER." AS ORD")
					->where('(ORD.source = 1 or ORD.source = 2 or ORD.source = 3) AND ORD.status = 1')
					->where_in('ORD.source_id',$contest_data['lmc_ids'])		
					->get()->row_array();
			
			if(!empty($result)){
				$report_data['total_real_fee'] = $result['total_join_real_amount'];
				$report_data['total_bonus_fee'] = $result['total_join_bonus_amount'];
				$report_data['total_winning_fee'] = $result['total_join_winning_amount'];
				$report_data['total_winning'] = $result['total_winning_amount'];
				$report_data['total_cancel_fee'] = $result['total_refund_real_amount'];
				$report_data['total_cancel_bonus_fee'] = $result['total_refund_bonus_amount'];
				$report_data['total_cancel_winning_fee'] = $result['total_refund_winning_amount'];

				$report_data['total_entry_fee'] = number_format(($report_data['total_real_fee'] + $report_data['total_bonus_fee'] + $report_data['total_winning_fee']),2,".","");
				$report_data['total_refund_fee'] = number_format(($report_data['total_cancel_fee'] + $report_data['total_cancel_bonus_fee'] + $report_data['total_cancel_winning_fee']),2,".","");
				$report_data['net_profit'] = number_format(($report_data['total_entry_fee'] + $report_data['total_winning_fee']) - ($report_data['total_bonus_fee'] + $report_data['total_winning'] + $report_data['total_cancel_fee'] + $report_data['total_cancel_winning_fee']),2,".","");
			}
		}
		return $report_data;
	}

	/**
	 * get metch report 
	 * @param sports id
	 * @return array
	 */

	 public function get_match_report($post_data)
	 {
		$limit = isset($post_data['items_perpage']) ? $post_data['items_perpage'] : 50;
		$current_date = format_date();
		$page_no=1;
		$sort_field = 'schedule_date';
		$sort_order = 'DESC';

		if(isset($post_data['current_page']))
		{
			$page_no = $post_data['current_page']-1;
		}

		if(isset($post_data['from_date'])){
			$from_date = $post_data['from_date'];
		}else{
			$from_date = date('Y-m-d',strtotime($current_date. ' - 10 days')).' 00:00:00';
		}

		if(isset($post_data['to_date'])){
			$to_date = $post_data['to_date'];
		}
		else{
			$to_date = date('Y-m-d',strtotime($current_date)).' 23:59:59';
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('league_id','collection_master_id','match_name','schedule_date','entry_real','entry_bonus','entry_coins','site_rake','site_rake_private','prize_pool','prize_pool_real','prize_pool_bonus','prize_pool_coins','promo_discount','revenue','profit')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$result = $this->db->select('league_id,collection_master_id,match_name,schedule_date,total_user,real_user,entry_real,entry_bonus,entry_coins,site_rake,site_rake_private,prize_pool,prize_pool_real,prize_pool_bonus,prize_pool_coins,promo_discount,bots_entry,bots_winning,revenue,profit')
		->from(MATCH_REPORT.' MR')
		->where("MR.schedule_date between '{$from_date}' and '{$to_date}'",null,false)
		->order_by($sort_field,$sort_order);

		if(isset($post_data['league_id']) && $post_data['league_id'] !=='')
		{
		$this->db->where('MR.league_id',$post_data['league_id']);
		}

		if(isset($post_data['collection_master_id']) && $post_data['collection_master_id'] !=='')
		{
			$this->db->where('MR.collection_master_id',$post_data['collection_master_id']);
		}

		if(isset($post_data['sports_id']) && $post_data['sports_id'] !=='')
		{
			$this->db->where('MR.sports_id',$post_data['sports_id']);
		}

		$tempdb = clone $this->db;
		$total = $tempdb->get()->num_rows();	
				   
        if (isset($limit) && isset($page_no) && $post_data['csv']==false ) {
			$offset	= $limit * $page_no;
            $this->db->limit($limit, $offset);
		}
		$result = $this->db->get()->result_array();
		// echo $this->db->last_query();exit;
		if (isset($limit) && isset($offset) && $post_data['csv']==false ) {
		return ['result'=>$result,'total'=>$total];
		}else{
		return $result;
		}
	 }


	 /**
	 * [contest_list description]
	 * @MethodName contest_list
	 * @Summary This function used for get all contest List
	 * @return     [array]
	 */
	// public function get_complet_contest_report($post_params)
	// { 
	// 	$sort_field = 'G.season_scheduled_date';
	// 	$sort_order = 'DESC';
	// 	$limit      = 50;
	// 	$page       = 0;
		
	// 	$post_data = $post_params;
	// 	// print_r($data_arr);die();
	// 	if(isset($post_data['items_perpage']))
	// 	{
	// 		$limit = $post_data['items_perpage'];
	// 	}

	// 	if(isset($post_data['current_page']))
	// 	{
	// 		$page = $post_data['current_page']-1;
	// 	}

	// 	if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','collection_name','size','entry_fee','season_scheduled_date','prize_pool','guaranteed_prize','total_user_joined','minimum_size','site_rake')))
	// 	{
	// 		$sort_field = $post_data['sort_field'];
	// 	}

	// 	if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
	// 	{
	// 		$sort_order = $post_data['sort_order'];
	// 	}

	// 	$offset	= $limit * $page;
	// 	//SUM(IF(LM.is_systemuser=1,1,0)) as system_teams,SUM(IF(LM.is_systemuser=0,1, 0)) as real_teams,(SUM(IF(LM.is_systemuser=1,1,0))*G.entry_fee) as botuser_total_real_entry_fee need to add ,(G.total_user_joined - G.total_system_user) AS real_teams,G.total_system_user AS system_teams
	// 	$classic = $this->lang->line('classic');
	// 	$reverse = $this->lang->line('reverse');
	// 	$second_inning = $this->lang->line('2nd_inning');
	// 	$this->db_fantasy->select("G.group_id, G.group_id,CM.collection_name,G.contest_id,G.contest_unique_id, G.contest_name, G.entry_fee, G.prize_pool,G.site_rake, G.total_user_joined, G.size,G.minimum_size, 
	//    G.guaranteed_prize,DATE_FORMAT(G.season_scheduled_date, '".MYSQL_DATE_TIME_FORMAT."') AS season_scheduled_date,SUM(IF(LM.is_systemuser=1,1,0)) as system_teams,SUM(IF(LM.is_systemuser=0,1, 0)) as real_teams,G.currency_type,G.max_bonus_allowed,G.entry_fee*G.total_user_joined as total_entry_fee,G.entry_fee*G.total_system_user AS botuser_total_real_entry_fee,
	// 	 (CASE WHEN G.is_2nd_inning=1 THEN '{$second_inning}'
	// 	 WHEN G.is_reverse =1 THEN '{$reverse}'
	// 	 WHEN G.is_reverse=0 AND G.is_2nd_inning =0 THEN '{$classic}' 		

	// 	END) AS feature_type,MG.group_name",false)
	// 	->from(CONTEST_REPORT." AS G");
	// 	// ->join(MASTER_GROUP." AS MG","MG.group_id = G.group_id","INNER")
	// 	// ->join(COLLECTION_MASTER." AS CM","CM.collection_master_id = G.collection_master_id","LEFT")
	// 	// ->join(LINEUP_MASTER_CONTEST." AS LMC", 'LMC.contest_id = G.contest_id','LEFT')
	// 	// ->join(LINEUP_MASTER." AS LM", 'LM.lineup_master_id = LMC.lineup_master_id','LEFT')
	// 	// ->where('G.status','3');
		

	// 	$game_type = isset($post_data['game_type'])?$post_data['game_type']:"";

	// 	if(isset($post_data['sports_id']) && $post_data['sports_id'] != '')
	// 	{
	// 		$this->db_fantasy->where('G.sports_id',$post_data['sports_id']);
	// 	}
	// 	if(isset($post_data['league_id']) && $post_data['league_id'] != '')
	// 	{
	// 		$this->db_fantasy->where('G.league_id',$post_data['league_id']);
	// 	}
	// 	if(isset($post_data['contest_name']))
	// 	{
	// 		$this->db_fantasy->like('G.contest_name',$post_data['contest_name']);
	// 	}
	// 	if(isset($post_data['group_id']))
	// 	{
	// 	$this->db_fantasy->like('G.group_id',$post_data['group_id']);
	// 	}
	// 	// if(isset($post_data['collection_master_id']) && $post_data['collection_master_id']!="")
	// 	// {
	// 	// 	$this->db_fantasy->where('G.collection_master_id',$post_data['collection_master_id']);
	// 	// }

	// 	// if($this->get_app_config_value('allow_reverse_contest') ==1 &&  $post_data['feature_type']==1  )//reverse
 	// 	// {
	// 	// 	$this->db_fantasy->where('G.is_reverse',1);
	// 	// }
	
	// 	// if($this->get_app_config_value('allow_2nd_inning') ==1&& $post_data['feature_type']==2 )//2nd Inning
	// 	// {
	// 	// 	$this->db_fantasy->where('G.is_2nd_inning',1);
	// 	// }

	// 	// if(isset($post_data['feature_type']) && $post_data['feature_type']=="0")//Classic
	// 	// {
	// 	// 	$this->db_fantasy->where('G.is_2nd_inning',0);
	// 	// 	$this->db_fantasy->where('G.is_reverse',0);
	// 	// }


	// 	$this->db_fantasy->group_by('G.contest_unique_id');

	//     if(!empty($post_data['from_date'])&&!empty($post_data['to_date']))
	// 		$this->db_fantasy->where("DATE_FORMAT(G.season_scheduled_date,'%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(G.season_scheduled_date,'%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."'");
		
	// 	 $tempdb = clone $this->db_fantasy;
	// 	$temp_q = $tempdb->get();
	// 	$total = $temp_q->num_rows(); 

	// 	// echo $temp_q->last_query(); die;

	// 	if(!empty($sort_field) && !empty($sort_order))
	// 	{
	// 		$this->db_fantasy->order_by($sort_field, $sort_order);
	// 	}

	// 	if(!empty($limit) && !$post_data["csv"])
	// 	{
	// 		$this->db_fantasy->limit($limit, $offset);
	// 	}
	// 	$sql = $this->db_fantasy->get();
	// 	$result	= $sql->result_array();
	// 	// echo $this->db_fantasy->last_query();die;
	// 	return array('result'=>$result, 'total'=>$total);
	// }





	 /**
	 * [contest_list description]
	 * @MethodName contest_list
	 * @Summary This function used for get all contest List
	 * @return     [array]
	 */
	public function get_complet_contest_report($post_params)
	{ 
		$this->db = $this->load->database('db_user', TRUE);
		$sort_field = 'G.schedule_date';
		$sort_order = 'DESC';
		$limit      = 50;
		$page       = 0;
		
		$post_data = $post_params;

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','collection_name','size','entry_fee','season_scheduled_date','prize_pool','guaranteed_prize','total_user_joined','minimum_size','site_rake')))
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
		if (isset($post_data['csv']) && $post_data['csv'] == true) 	
		{
		$this->db->select("G.currency_type,G.game_type,G.contest_id,G.contest_unique_id,G.sports_id,G.league_id,G.max_bonus_allowed,G.size,G.minimum_size,G.match_name,G.contest_name,G.guaranteed_prize,G.total_user_joined,G.real_user,G.entry_real,G.entry_bonus,G.entry_coins,G.site_rake,G.prize_pool,G.system_teams,G.real_teams,G.bot_entry_fee,G.real_prize,G.coin_prize,G.bonous_prize,G.promo_entry,G.profit,G.feature_type,G.group_id, G.group_id,G.match_name as collection_name,G.contest_id,G.contest_unique_id, G.contest_name, G.prize_pool,G.site_rake, G.total_user_joined, G.size,G.minimum_size,G.entry_fee,group_name",false);

		
	
			$tz_diff = get_tz_diff($this->app_config['timezone']);
	
			$this->db->select("CONVERT_TZ(G.schedule_date, '+00:00', '".$tz_diff."') AS schedule_date");
		}else{
			
			$this->db->select("G.currency_type,G.game_type,G.contest_id,G.contest_unique_id,G.sports_id,G.league_id,G.max_bonus_allowed,G.size,G.minimum_size,G.match_name,G.contest_name,G.guaranteed_prize,G.total_user_joined,G.real_user,G.entry_real,G.entry_bonus,G.entry_coins,G.site_rake,G.prize_pool,G.system_teams,G.real_teams,G.bot_entry_fee,G.real_prize,G.coin_prize,G.bonous_prize,G.promo_entry,G.profit,G.feature_type,G.group_id, G.group_id,G.match_name as collection_name,G.contest_id,G.contest_unique_id, G.contest_name, G.prize_pool,G.site_rake, G.total_user_joined, G.size,G.minimum_size,G.entry_fee,group_name",false);

			$this->db->select("G.schedule_date");
		}

		$this->db->from(CONTEST_REPORT." AS G");
		

		$game_type = isset($post_data['game_type'])?$post_data['game_type']:"";		

		if(isset($post_data['sports_id']) && $post_data['sports_id'] != '')
		{
			$this->db->where('G.sports_id',$post_data['sports_id']);
		}
		if(isset($post_data['league_id']) && $post_data['league_id'] != '')
		{
			$this->db->where('G.league_id',$post_data['league_id']);
		}
		if(isset($post_data['contest_name']))
		{
			$this->db->like('G.contest_name',$post_data['contest_name']);
		}
		if(isset($post_data['group_id']))
		{
		$this->db->like('G.group_id',$post_data['group_id']);
		}
		if(isset($post_data['collection_master_id']) && $post_data['collection_master_id']!="")
		{
			$this->db->where('G.collection_master_id',$post_data['collection_master_id']);
		}

		if($this->get_app_config_value('allow_reverse_contest') ==1 &&  $post_data['feature_type']==1  )//reverse
 		{
			$this->db->where('G.is_reverse',1);
		}
	
		if($this->get_app_config_value('allow_2nd_inning') ==1&& $post_data['feature_type']==2 )//2nd Inning
		{
			$this->db->where('G.is_2nd_inning',1);
		}

		if(isset($post_data['feature_type']) && $post_data['feature_type']=="0")//Classic
		{
			$this->db->where('G.is_2nd_inning',0);
			$this->db->where('G.is_reverse',0);
		}

		if(isset($post_data['keyword']))
		{
			$this->db->like('G.contest_name',$post_data['keyword']);
		}


		$this->db->group_by('G.contest_unique_id');

		// echo "<pre>";
		// print_r($post_data);die;

	    if(!empty($post_data['from_date'])&&!empty($post_data['to_date']))
			$this->db->where("DATE_FORMAT(G.schedule_date,'%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(G.schedule_date,'%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."'");
		
		 $tempdb = clone $this->db;
		$temp_q = $tempdb->get();
		$total = $temp_q->num_rows(); 

		// echo $temp_q->last_query(); die;

		if(!empty($sort_field) && !empty($sort_order))
		{
			$this->db->order_by($sort_field, $sort_order);
		}

		if(!empty($limit) && !$post_data["csv"])
		{
			$this->db->limit($limit, $offset);
		}
		$sql = $this->db->get();
		$result	= $sql->result_array();
		// echo $this->db->last_query();die;
		return array('result'=>$result, 'total'=>$total);
	}

	/**
	 * [get_all_user_report description]
	 * @MethodName get_all_user_report
	 * @Summary This function used get all user report by from to to date list
	 * @return     array
	 */
	public function get_all_user_report()
	{
		$sort_field	= 'first_name';
		$sort_order	= 'DESC';
		$post_data = $this->input->post();
		// print_r($post_data);die;
		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(($post_data['sort_field']) && in_array($post_data['sort_field'],array('user_name','first_name','email', 'balance', 'bonus_balance', 'winning_balance', 'country_name','dob','name', 'added_date', 'last_login', 'first_deposit', 'first_deposit_date', 'last_deposit_date', 'deposit_by_user', 'withdraw_by_user', 'prize_amount_won','deposit_by_admin')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		if($this->input->post('csv') == false)
		{
		$offset	= $limit * $page;
		}

		$payment_sql = $this->db->select("U.user_unique_id,IFNULL(U.user_name,'-') AS user_name, CONCAT_WS(' ',IFNULL(U.first_name,'-'),U.last_name) AS name,IFNULL(U.email,'-') as email,IFNULL(U.phone_no,'-') AS phone_no,U.balance, U.winning_balance, U.bonus_balance,U.user_id,
		IFNULL(UMR.module_type,1) AS module_type,
		IFNULL(COUNT(DISTINCT UMR.entity_id),0) AS match_played,
		IFNULL(SUM(IF(UMR.match_won > 0,1,0)),0) AS match_won,
		0 AS match_lost,
		IFNULL(SUM(DISTINCT UMR.total_entry_fee),0) AS total_entry_fee,
		IFNULL(SUM(DISTINCT UMR.coin_entry),0) AS coin_entry,
		IFNULL(SUM(DISTINCT UMR.total_bonus_used),0) AS total_bonus_used,
		IFNULL(SUM(DISTINCT UMR.total_win_amt),0) AS total_win_amt,
		IFNULL(SUM(DISTINCT UMR.coin_winning),0) AS coin_winning,
		IFNULL(SUM(DISTINCT UMR.revenue),0) AS revenue,0 as deposit_by_user,0 as deposit_by_admin,0 as withdraw_by_user,U.device_type,U.phone_verfied,U.added_date,U.last_login", FALSE);
		if (isset($post_data['csv']) && $post_data['csv'] == true) 	
		{
			// print_r( $this->app_config['timezone']['key_value']); die;
			$tz_diff = get_tz_diff($this->app_config['timezone']);
			// echo $tz_diff; die;
			$this->db->select("CONVERT_TZ(U.added_date, '+00:00', '".$tz_diff."') AS added_date,CONVERT_TZ(U.last_login, '+00:00', '".$tz_diff."') AS last_login_date");
		}
		$this->db->from(USER.' AS U')
		 	->join(USER_MATCH_REPORT.' AS UMR','U.user_id = UMR.user_id','LEFT')
			//->join(ORDER.' AS O','O.user_id = U.user_id AND O.status = 1 AND O.source IN (0,1,2,3,7,8)','LEFT')
			->group_by("U.user_id")
			->order_by($sort_field, $sort_order)
			->order_by("U.user_id","ASC");
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('CONCAT(U.user_unique_id,IFNULL(U.email,""),IFNULL(U.phone_no,""),IFNULL(U.user_name,""),CONCAT_WS(" ",U.first_name,U.last_name))', $post_data['keyword']);
		}

		//0-phone, 1-email
		if(LOGIN_FLOW == 1 || LOGIN_FLOW == 2)
		{
			//email_verified : 0->not verfied, 1->verfied
			if(isset($post_data['email_verfied']) || isset($post_data['profile_status']))
			{
				if (isset($post_data['email_verfied']) && $post_data['email_verfied'] == 0  && ((isset($post_data['profile_status']) && $post_data['profile_status'] == "") || !isset($post_data['profile_status'])))
				{
					$this->db->where("U.email_verified", 0);
				}
				else if (isset($post_data['email_verfied']) && $post_data['email_verfied'] == 1)
				{
					$this->db->where("U.email_verified", 1);
					if (isset($post_data['profile_status']) && $post_data['profile_status'] != "" && $post_data['profile_status'] == "0")
					{
						$this->db->where('(U.email = "" OR U.user_name ="" OR U.user_name IS NULL)',NULL,false);
					}
					else if (isset($post_data['profile_status']) && $post_data['profile_status'] != "" && $post_data['profile_status'] == "1")
					{
						$this->db->where('(U.email != "" AND U.user_name != "")',NULL,false);
					}
				}
				else if (isset($post_data['profile_status']) && $post_data['profile_status'] != "" && $post_data['profile_status'] == "1" && ((isset($post_data['email_verfied']) && $post_data['email_verfied'] == "") || !isset($post_data['email_verfied'])))
				{
					$profile_status=array(
						"U.email_verified"=>"1",
						"U.email !="=>"",
						"U.user_name !="=>"",
					);
					$this->db->where($profile_status);
				}
				else if (isset($post_data['profile_status']) && $post_data['profile_status'] != "" && $post_data['profile_status'] == "0")
				{
					if (isset($post_data['email_verfied']) && $post_data['email_verfied'] != "" && $post_data['email_verfied'] == "0")
					{
						$this->db->where('(U.email_verified = "0" OR U.email = "" OR U.user_name = "")',NULL,false);
					}
					else if (isset($post_data['email_verfied']) && $post_data['email_verfied'] != "" && $post_data['email_verfied'] == "1")
					{
						$this->db->where("U.email_verified", 1);
						$this->db->where('(U.email = "" OR U.user_name = "")',NULL,false);
					}
					else if (!isset($post_data['email_verfied']))
					{
						$this->db->where('(U.email_verified = "0" OR U.email = "" OR U.user_name = "")',NULL,false);
					}
				}
			}
		}
		else
		{
			//phone_verfied : 0->not verfied, 1->verfied
			if(isset($post_data['phone_verfied']) || isset($post_data['profile_status']))
			{
				if (isset($post_data['phone_verfied']) && $post_data['phone_verfied'] == 0  && ((isset($post_data['profile_status']) && $post_data['profile_status'] == "") || !isset($post_data['profile_status'])))
				{
					$this->db->where("U.phone_verfied", 0);
				}
				else if (isset($post_data['phone_verfied']) && $post_data['phone_verfied'] == 1)
				{
					$this->db->where("U.phone_verfied", 1);
					if (isset($post_data['profile_status']) && $post_data['profile_status'] != "" && $post_data['profile_status'] == "0")
					{
						$this->db->where('(U.email = "" OR U.user_name ="")',NULL,false);
					}
					else if (isset($post_data['profile_status']) && $post_data['profile_status'] != "" && $post_data['profile_status'] == "1")
					{
						$this->db->where('(U.email != "" AND U.user_name != "")',NULL,false);
					}
				}
				else if (isset($post_data['profile_status']) && $post_data['profile_status'] != "" && $post_data['profile_status'] == "1" && ((isset($post_data['phone_verfied']) && $post_data['phone_verfied'] == "") || !isset($post_data['phone_verfied'])))
				{
					$profile_status=array(
						"U.phone_verfied"=>"1",
						"U.email !="=>"",
						"U.user_name !="=>"",
					);
					$this->db->where($profile_status);
				}
				else if (isset($post_data['profile_status']) && $post_data['profile_status'] != "" && $post_data['profile_status'] == "0")
				{
					if (isset($post_data['phone_verfied']) && $post_data['phone_verfied'] != "" && $post_data['phone_verfied'] == "0")
					{
						$this->db->where('(U.phone_verfied = "0" OR U.email = "" OR U.user_name = "")',NULL,false);
					}
					else if (isset($post_data['phone_verfied']) && $post_data['phone_verfied'] != "" && $post_data['phone_verfied'] == "1")
					{
						$this->db->where("U.phone_verfied", 1);
						$this->db->where('(U.email = "" OR U.user_name = "")',NULL,false);
					}
					else if (!isset($post_data['phone_verfied']))
					{
						$this->db->where('(U.phone_verfied = "0" OR U.email = "" OR U.user_name = "")',NULL,false);
					}
				}
			}
		}

		if(isset($post_data['device_type']) && in_array($post_data['device_type'],[1,2,3,4]) && $post_data['device_type']!="")
		{ 
			$this->db->where("U.device_type",$post_data['device_type']);
		}

		if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db->where("DATE_FORMAT(UMR.schedule_date, '%Y-%m-%d %H:%i:%s') >= '".$post_data['from_date']."' and DATE_FORMAT(UMR.schedule_date, '%Y-%m-%d %H:%i:%s') <= '".$post_data['to_date']."' ");
		}
		//to get total records
		$tempdb = clone $this->db;
		$total = 0;
		if($this->input->post('csv') == false)
		{
		// if($post_data['current_page'] == 1){
			$query = $this->db->get();
        	$total = $query->num_rows();
		// }
	     }

		if($this->input->post('csv') == false)
		{
            $sql = $tempdb->limit($limit,$offset);
		}

		$sql = $tempdb->get();
		$result	= $sql->result_array();

		// $result = $tempdb->limit($limit,$offset)
						// ->get()->result_array();
						//echo $this->db->last_query();die;
                
		if (isset($post_data['csv']) && $post_data['csv'] == true) 	
		{
			$result = $sql->result_array();
		}	
                //echo $total; print_r($result); die;
		return array('result'=>$result ,'total'=> $total);
						
	}

	public function get_user_txn_data($user_ids){
		if(empty($user_ids)){
			return false;
		}

		$this->db->select("O.user_id,sum(if(source=7,O.real_amount+O.bonus_amount,0)) as deposit_by_user,sum(if(source=0 AND type=0,O.real_amount+O.bonus_amount+O.winning_amount,0)) as deposit_by_admin,sum(if(source=8,O.real_amount+O.bonus_amount+O.winning_amount,0)) as withdraw_by_user", FALSE);
		$this->db->from(ORDER.' AS O');
		$this->db->where("O.status","1");
		$this->db->where_in("O.source",[0,7,8]);
		$this->db->where_in("O.user_id",$user_ids);
		$this->db->group_by("O.user_id");
		$result = $this->db->get()->result_array();
		return $result;
	}
}
/* End of file Transaction_model.php */ //$value['contest_ids'] $value['contest_unique_id']   $result[$key]['real_bonus_fee'],
/* Location: ./application/models/Transaction_model.php */