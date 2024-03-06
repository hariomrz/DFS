<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_model extends MY_Model {
    
    public $db_user ;
    public $db_fantasy ;
    public $testingNode = FALSE;

    public function __construct() 
    {
       	parent::__construct();
		$this->db_user		= $this->load->database('db_user', TRUE);
		$this->db_fantasy	= $this->load->database('db_fantasy', TRUE);
	}
	
	public function gst_invoice_report($get_user_info,$filters){

		if(empty($get_user_info)){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['status'] = FALSE;
			$this->api_response_arry['message'] = "No user found";
			$this->api_response();
		}
		
		if(!empty($get_user_info)){

			$user_ids = array_column($get_user_info, 'user_id');
			$filters['user_ids'] = $user_ids;

			$this->db_fantasy->select('DATE_FORMAT(C.modified_date,"%Y-%m-%d") as date,C.contest_name,C.contest_id,LM.user_id,C.entry_fee,C.site_rake,round((`C`.`entry_fee`* `C`.`site_rake`)/100) as platform_fee,LMC.prize_data,LMC.lineup_master_contest_id,LMC.lineup_master_contest_id,S.season_game_uid')->from(CONTEST.' AS C');
			$this->db_fantasy->distinct("LMC.lineup_master_contest_id");

			$this->db_fantasy->join(LINEUP_MASTER." AS LM","LM.collection_master_id=  C.collection_master_id","inner");	
			$this->db_fantasy->join(LINEUP_MASTER_CONTEST." AS LMC","LMC.contest_id=  C.contest_id AND LMC.lineup_master_id = LM.lineup_master_id","inner");
			$this->db_fantasy->join(COLLECTION_SEASON." AS CS","CS.collection_master_id = C.collection_master_id","inner");
			$this->db_fantasy->join(SEASON." AS S","S.season_id = CS.season_id","inner");

			//print_r($post_data['user_ids']);exit;
			if(!empty($filters['user_ids']))
			{
				//print_r($filters['user_ids']);exit;
				$this->db_fantasy->where_in("LM.user_id",$filters['user_ids']);
			}
			
			if(!empty($filters['from_date']) && !empty($filters['to_date'])){
				//$this->db->where("DATE_FORMAT(G.season_scheduled_date, '%Y-%m-%d') BETWEEN '".$filters['from_date']."' AND '".$filters['to_date']."'");
				$this->db_fantasy->where("C.modified_date BETWEEN '{$filters['from_date']}' AND '{$filters['to_date']} 23:59:59'");
			}

			if(!empty($filters['season_game_uid']) && $filters['season_game_uid']>0)
			{
				$this->db_fantasy->where("S.season_game_uid",$filters['season_game_uid']);
			}

			if(!empty($filters['contest_id']) && $filters['contest_id']>0)
			{
				$this->db_fantasy->where("C.contest_id",$filters['contest_id']);
			}

			$this->db_fantasy->where("C.status",3);	

			$get_contest_info_data	= $this->db_fantasy->get()->result_array();

			// $get_contest_info_data = $this->Gst_model->get_contest_by_user($filters);
			// print_r($get_contest_info_data);exit;
			if(empty($get_contest_info_data)){
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['status']			= TRUE;
				$this->api_response_arry['message']			= '';
				$this->api_response_arry['data']			= "No Record";
				$this->api_response();
			}

			$total_entry_fee = 0;
			$total_platform_fee = 0;
			$total_winning_distribute = 0;
			$total_tds = 0;
			$total_tax_able_value = 0;
			$total_igst = 0;
			$total_sgst = 0;
			$total_cgst = 0;
			
			$get_contest_info = $get_contest_info_data;
			$portal_state_id = isset($this->app_config['allow_gst']['custom_data']['state_id']) ? $this->app_config['allow_gst']['custom_data']['state_id'] : 0;
			$tds_percent = isset($this->app_config['allow_tds']['custom_data']['percent']) ? $this->app_config['allow_tds']['custom_data']['percent'] : 0;
			$tds_amount = isset($this->app_config['allow_tds']['custom_data']['amount']) ? $this->app_config['allow_tds']['custom_data']['amount'] : 0;
			foreach ($get_contest_info as $key => $value) {
				
				$username = "";
				$state = "";
				$master_state_id = "";
				
				//Get User info from other oject
				foreach ($get_user_info as $user_key => $user_value) {
					if($user_value['user_id']==$value['user_id']){
						$user_value['user_id'];
						$username = $user_value['user_name'];
						$state = $user_value['state'];
						$master_state_id = $user_value['master_state_id'];
					}
				}


				$get_contest_info[$key]['user_name'] = $username;
				$get_contest_info[$key]['state'] = $state;
				$get_contest_info[$key]['win_amount'] = 0;
				$get_contest_info[$key]['tds'] = 0;
				$get_contest_info[$key]['igst'] = 0;
				$get_contest_info[$key]['sgst'] = 0;
				$get_contest_info[$key]['cgst'] = 0;
				$get_contest_info[$key]['taxable_value'] = 0;
				$get_contest_info[$key]['tax_rate'] = "18%";

				//Get invoice ID from SQL query
				$invoice_id = $this->db_user->select('OD.order_id')->from(ORDER.' AS OD')
				->where("OD.source",'3')
				->where("OD.source_id",$get_contest_info[$key]['lineup_master_contest_id'])
				->get()->row_array();

				// $invoice_id = $this->Gst_model->get_invoice_id(array('lineup_master_contest_id'=>$get_contest_info[$key]['lineup_master_contest_id']));
				$get_contest_info[$key]['invoice_no'] = $invoice_id['order_id']?$invoice_id['order_id']:'';

				//print_r($get_contest_info);exit;

				//SET Wom Amount 
				$prize_json_data = json_decode($get_contest_info[$key]['prize_data']);
				if(!empty($prize_json_data)){
					foreach ($prize_json_data as $p_key => $p_value) {
						if($p_value->prize_type==1){
							$win_amount =  (float) str_replace(',', '', number_format($p_value->amount,0));
							$get_contest_info[$key]['win_amount'] = $win_amount;
							$total_winning_distribute += $win_amount;
						}
					}
				}

				//TDS section Done 
				if($get_contest_info[$key]['win_amount'] >= $tds_amount){
					$get_contest_info[$key]['tds'] = round($get_contest_info[$key]['win_amount']*$tds_percent/100,2);
					$total_tds += $get_contest_info[$key]['tds'];
				}

				//SET taxable_value  Done
				if(number_format($get_contest_info[$key]['platform_fee']) > 0){
					$get_contest_info[$key]['taxable_value'] = round($get_contest_info[$key]['platform_fee']*100/118,2);
					$total_tax_able_value += $get_contest_info[$key]['taxable_value'];
				}

				//SET IGST (Test check)
				//echo $portal_state_id;exit;
				if($master_state_id!= $portal_state_id && $get_contest_info[$key]['taxable_value']>0){
					//=ROUND(L18*18%,0)
					$get_contest_info[$key]['igst'] = round($get_contest_info[$key]['taxable_value']*18/100,2);
					$total_igst += $get_contest_info[$key]['igst'];
				}

				//SET CGST and CGST (Test check)
				if($master_state_id== $portal_state_id && $get_contest_info[$key]['taxable_value']>0){
					//=ROUND(L18*18%,0)
					$sgst_and_cgst = round($get_contest_info[$key]['taxable_value']*9/100,2);

					$get_contest_info[$key]['sgst'] = $sgst_and_cgst;
					$get_contest_info[$key]['cgst'] = $sgst_and_cgst;

					$total_sgst += $sgst_and_cgst;
					$total_cgst += $sgst_and_cgst;
				}

				$total_entry_fee += $get_contest_info[$key]['entry_fee'];
				$total_platform_fee += $get_contest_info[$key]['platform_fee'];
			}

			//Send Total Array 
			$total_sum = array();
			$total_sum['total_entry_fee'] = round($total_entry_fee,2); 
			$total_sum['total_platform_fee'] = round($total_platform_fee,2);
			//$total_sum['total_winning_distribute'] = round($total_winning_distribute,2);
			//$total_sum['total_tds']= round($total_tds,2);
			$total_sum['total_tax_able_value'] = round($total_tax_able_value,2);
			$total_sum['total_igst'] = round($total_igst,2);
			$total_sum['total_sgst'] = round($total_sgst,2);
			$total_sum['total_cgst'] = round($total_cgst,2);

			// unset($get_contest_info_data);
			// $get_contest_info_data['result'] = $get_contest_info;
			// $get_contest_info_data['total_count'] = $total_sum;
			// return array('result'=>$get_contest_info_data);
			return $get_contest_info;
		}
	}

	//Get User info
	public function get_user_info($post_params){
		//print_r($post_params);exit;
		$this->db_user->select('U.user_id,U.pan_no,U.first_name,U.last_name,U.user_name,MS.name as state,U.master_state_id')->from(USER.' AS U');
		$this->db_user->join(MASTER_STATE." AS MS","MS.master_state_id=  U.master_state_id","INNER");
		if(isset($post_params['keyword']) && $post_params['keyword'] != "")
		{
			//print_r($post_params['user_ids']);exit;
			//print_r($post_params['user_ids']);exit;
			$this->db_user->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,""),CONCAT_WS(" ",U.first_name,U.last_name),IFNULL(U.pan_no,"")))', strtolower($post_params['keyword']) );
		}

		$portal_state_id = isset($this->app_config['allow_gst']['custom_data']['state_id']) ? $this->app_config['allow_gst']['custom_data']['state_id'] : 0;
		if(!empty($post_params['state_type']) && $post_params['state_type']!="")
		{
			if($post_params['state_type']=="intra_state"){
					
				$this->db_user->where("`user_id` IN (SELECT U.user_id FROM vi_user as U where U.master_state_id = '".$portal_state_id."')", NULL, FALSE);
			}

			if($post_params['state_type']=="inter_state"){

				$this->db_user->where("`user_id` IN (SELECT U.user_id FROM vi_user as U where NOT U.master_state_id = '".$portal_state_id."')", NULL, FALSE);

				if(!empty($post_params['state']) && $post_params['state']>0)
				{
					$this->db_user->where("`user_id` IN (SELECT U.user_id FROM vi_user as U where U.master_state_id = '".$post_params['state']."')", NULL, FALSE);	
				}
			}
		}else{

			if(!empty($post_params['state']) && $post_params['state']>0)
			{
				$this->db_user->where("`user_id` IN (SELECT U.user_id FROM vi_user as U where U.master_state_id = '".$post_params['state']."')", NULL, FALSE);	
			}

		}

		$users = $this->db_user->get()->result_array();
		// echo $this->db_user->last_query();exit;
		return $users;
	}

	function get_users_win_loss($user_ids = array())
	{
	
       $this->db_fantasy->select("LM.user_id, count(LMC.lineup_master_contest_id ) as matches_played, sum(LMC.is_winner) as matches_won, (count(LMC.lineup_master_contest_id )-sum(LMC.is_winner)) as matches_lost ",FALSE)
		->from(CONTEST." C")
        ->join(LINEUP_MASTER_CONTEST." LMC","LMC.contest_id = C.contest_id","INNER")
		->join(LINEUP_MASTER." LM","LMC.lineup_master_id= LM.lineup_master_id","INNER")
        ->where("LM.user_id>",0)
        ->where_in("C.status",array(2,3));
          
        if(!empty($user_ids))
        {
            $this->db_fantasy->where_in("LM.user_id", $user_ids);
        }

		$this->db_fantasy->group_by("LM.user_id");

		$record = $this->db_fantasy->get()->result_array();
		return $record;					
	}


	function user_money_paid($post_data)
	{
		$sort_field	= 'O.date_added';
		$sort_order	= 'DESC';

		if(!empty($post_data['sort_field']) && in_array($post_data['sort_field'],array('added_date','O.date_added','user_name','email','bouns_money_paid','real_money_paid','bonus_balance','balance','status')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(!empty($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$source = 1; // source fro dfs
		if(isset($post_data['module_name']) && $post_data['module_name'] == "livefantasy")
		{
			$source = 500; // source for livefantasy
		}
     
        $select_field = "U.user_unique_id, U.user_id,IFNULL(U.user_name,'-') AS user_name, CONCAT_WS(' ',IFNULL(U.first_name,'-'),U.last_name) AS name,IFNULL(U.phone_no,'-') AS phone_no,IFNULL(U.email,'-') AS email,TRUNCATE(sum(O.points),2) as coins_paid,U.point_balance,FORMAT(sum(O.real_amount),2) as real_money_paid, U.balance, FORMAT(sum(O.bonus_amount),2) as bouns_money_paid,U.bonus_balance,U.added_date";
      
        $payment_sql = $this->db_user->select($select_field,FALSE)
							->from(USER.' AS U')
							->join(ORDER.' AS O','O.user_id = U.user_id  ','LEFT')
							->where(array("O.type"=>1 , "O.source"=>$source , "O.status"=>1))
							->group_by("U.user_id")
							->order_by($sort_field, $sort_order);

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{	
			$this->db_user->like('CONCAT(IFNULL(U.first_name,""),IFNULL(U.last_name,""),IFNULL(U.email,""),IFNULL(U.user_name,""),CONCAT_WS(" ",U.first_name,U.last_name))', $post_data['keyword']);
		}

		$query = $this->db_user->get();
	
		$result	= $query->result_array();
			
		return  $result;
	}


	public function user_deposit($post_data)
	{
		$sort_field	= 'O.date_added';
		$sort_order	= 'DESC';


		if(!empty($post_data['sort_field']) && in_array($post_data['sort_field'],array('added_date','user_name','email','status','payment_request','O.date_added','payment_gateway_id')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(!empty($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		$sql = $this->db_user->select("U.user_unique_id,U.user_id,IFNULL(U.user_name,'-') AS user_name, CONCAT_WS(' ',IFNULL(U.first_name,'-'),U.last_name) AS name,IFNULL(U.phone_no,'-') AS phone_no,IFNULL(U.email,'-') as email,IFNULL(T.txn_id,'-') AS txn_id,O.real_amount as payment_request,
		(
			CASE 
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
			ELSE 'other' END
			) AS payment_gateway,O.order_unique_id, U.balance,U.bonus_balance,DATE_FORMAT(O.date_added,'".MYSQL_DATE_TIME_FORMAT."') AS order_date_added,U.added_date, 
			CASE 
                 when(O.status=1) THEN 'Completed'
                 when(O.status=2) THEN 'Failed'
                 ELSE
                 'Pending'
			 END as status",FALSE)
                                ->from(USER.' AS U')
                                ->join(ORDER.' AS O','O.user_id = U.user_id  ','LEFT')
                                ->join(TRANSACTION.' AS T','T.transaction_id = O.source_id  ','LEFT')
                                ->where(array("O.type"=>0 , "O.source"=>7 ))
                                ->order_by($sort_field, $sort_order);

		if(isset($post_data['payment_method']) && $post_data['payment_method'] != '')
		{
			$this->db_user->where("T.payment_gateway_id",$post_data['payment_method']);
		}

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{	
			$this->db_user->like('CONCAT(IFNULL(user_unique_id,""),IFNULL(first_name,""),IFNULL(last_name,""),IFNULL(user_name,""),CONCAT_WS(" ",first_name,last_name))', $post_data['keyword']);
		}

		if(isset($post_data['from_date']) && isset($post_data['to_date']) && $post_data['from_date'] != '' && $post_data['to_date'] != '')
		{
			$this->db_user->where("DATE_FORMAT(O.date_added, '%Y-%m-%d') >= '".format_date($post_data['from_date'],'Y-m-d')."' and DATE_FORMAT(O.date_added, '%Y-%m-%d') <= '".format_date($post_data['to_date'],'Y-m-d')."' ");
		}
		
		$query = $this->db_user->get();
		$result	= $query->result_array();
		return $result;
	}

	function referral($post_data)
	{	
		$this->db = $this->db_user;

		$sort_field	= 'user_name';
		$sort_order	= 'ASC';
	
		if(!empty($post_data['sort_field']) && in_array($post_data['sort_field'],array('user_name','email','name')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(!empty($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$from_date 	= isset($post_data['from_date'])?$post_data['from_date']:"";
		$to_date 	= isset($post_data['to_date'])?$post_data['to_date']:"";

		//IFNULL(SUM(PHT.transaction_amount),0) as referal_amount
		$this->db->select("U.user_id,IFNULL(U.user_name,'-') as user_name,CONCAT(U.first_name, ' ', U.last_name) AS name,IFNULL(U.phone_no,'-') AS phone_no,IFNULL(U.email,'-') AS email,count(UAH.user_affiliate_history_id) AS Referral_Registered", FALSE) 
			  	->from(USER . ' U')
			  	->join(USER_AFFILIATE_HISTORY." UAH","U.user_id = UAH.user_id","INNER")
			  	->where_in("UAH.affiliate_type",[1,19,20,21]);
			  	

			if($from_date && $to_date)
			{
				$this->db->where("DATE_FORMAT(UAH.created_date, '%Y-%m-%d') BETWEEN '".$from_date."' AND '".$to_date."'");
			}
				//->where('R.referral_invite_sent', 1)
			$this->db->group_by('U.user_id');

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('CONCAT(IFNULL(U.email,""),IFNULL(U.user_name,""))', $post_data['keyword']);
		}

		$query = $this->db->order_by($sort_field, $sort_order)->get();

		$result	= $query->result_array();
		// echo $this->db->last_query();exit;

		if(!empty($result))
		{

			foreach ($result as $key => $value) 
			{
				//this column is removed after discussion
				// $data 							 = $this->get_referral_friends($result[$key]['user_id']);
				// $result[$key]['Registered'] = (isset($data['total_registered'])) ? $data['total_registered'] : 0 ;

				$sql = $this->db->select("SUM(UAH.user_bonus_cash) AS user_bonus_cash,SUM(UAH.user_real_cash) AS user_real_cash,SUM(UAH.user_coin) AS user_coin")
						->from(USER_AFFILIATE_HISTORY." UAH")
						->where("UAH.is_referral",1)
						->where_in("UAH.affiliate_type",array(1,5,10,11,12,19,20,21))
						->where("UAH.user_id",$result[$key]['user_id'])
						->where("UAH.status",1);
						if($from_date && $to_date)
						{
							$this->db->where("DATE_FORMAT(UAH.created_date, '%Y-%m-%d') BETWEEN '".$from_date."' AND '".$to_date."'");
						}
						
				$total_earned_info = $sql->get()->row_array();
				//same query put direct because of date filter
				// $total_earned_info 				 = $this->get_total_earned_referral_bonus_by_user($result[$key]['user_id']);
				// $result[$key]['referal_amount'] = 0;
				$result[$key]['referal_cash'] = (!empty($total_earned_info['user_real_cash'])) ? $total_earned_info['user_real_cash'] : 0;
				$result[$key]['referal_bonus'] = (!empty($total_earned_info['user_bonus_cash'])) ? $total_earned_info['user_bonus_cash'] : 0 ;
				$result[$key]['referal_coin'] = (!empty($total_earned_info['user_coin'])) ? $total_earned_info['user_coin'] : 0;
				unset($result[$key]['user_id']);
			}
		}

		return $result;
	}


	function get_referral_friends($UserID)
	{	
		
		$sql = $this->db->select("count(UAH.user_affiliate_history_id) as total_sent,SUM(IF(UAH.friend_id,1,0))  AS total_registered",FALSE)
			->from(USER_AFFILIATE_HISTORY." UAH")
			->join(USER." U","U.user_id = UAH.friend_id","LEFT")
			->where("UAH.affiliate_type",1)
			->where("UAH.user_id",$UserID);
		
		$this->db->group_by("UAH.user_id"); 
		$this->db->order_by('U.added_date', 'DESC'); 
		//$this->db->limit($limit, $offset);
		$result = $this->db->get()->row_array();
// echo $this->db->last_query();exit;
        return $result;
	}
       
	function get_earned_contest_bonus($user_id,$friend_id)
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

	function get_total_earned_referral_bonus_by_user($user_id)
	{

		$sql = $this->db->select("SUM(UAH.user_bonus_cash) AS user_bonus_cash,SUM(UAH.user_real_cash) AS user_real_cash,SUM(UAH.user_coin) AS user_coin")
						->from(USER_AFFILIATE_HISTORY." UAH")
						->where("UAH.is_referral",1)
						->where_in("UAH.affiliate_type",array(1,5,10,11,12))
						->where("UAH.user_id",$user_id)
						->where("UAH.status",1)
						->get();
		if($sql->num_rows() > 0)
		{
			$row = $sql->row_array();
			return $row;	
		}
		return array();
	}

	function contest_report($post_data)
	{
		$sort_field = 'season_scheduled_date';
		$sort_order = 'DESC';

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','collection_name','size','entry_fee','season_scheduled_date','prize_pool','guaranteed_prize')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$this->db_fantasy->select("G.contest_id,G.contest_unique_id, G.contest_name, CM.collection_name, G.entry_fee, G.prize_pool,G.site_rake, G.total_user_joined, G.size,G.minimum_size,
		(CASE 
		WHEN G.guaranteed_prize=0 THEN 'No Guarantee'
		WHEN G.guaranteed_prize=1 THEN 'Guaranteed prize custom'
		WHEN G.guaranteed_prize=2 THEN 'Guaranteed'
		END
		) AS guaranteed_prize,
		DATE_FORMAT(G.season_scheduled_date, '".MYSQL_DATE_TIME_FORMAT."') AS season_scheduled_date", FALSE)
						 ->from('vi_contest' . " AS G")
						 ->join(MASTER_GROUP . " AS MG", 'G.group_id = G.group_id', 'INNER')
						 ->join(COLLECTION_MASTER . " AS CM", 'CM.collection_master_id = G.collection_master_id', 'LEFT')
						 ->join(LINEUP_MASTER_CONTEST." AS LMC", 'LMC.contest_id = G.contest_id','LEFT')
						 ->join(LINEUP_MASTER." AS LM", 'LM.lineup_master_id = LMC.lineup_master_id','LEFT')
						 ->where('G.status','3');
		
		$game_type = isset($post_data['game_type'])?$post_data['game_type']:"";

		if(isset($post_data['sports_id']) && $post_data['sports_id'] != '')
		{
			$this->db_fantasy->where('G.sports_id',$post_data['sports_id']);
		}
		if(isset($post_data['league_id']) && $post_data['league_id'] != '')
		{
			$this->db_fantasy->where('G.league_id',$post_data['league_id']);
		}
		if(isset($post_data['contest_name']))
		{
			$this->db_fantasy->like('G.contest_name',$post_data['contest_name']);
		}
		if(isset($post_data['group_id']))
		{
		$this->db_fantasy->like('G.group_id',$post_data['group_id']);
		}
		if(isset($post_data['collection_master_id']))
		{
			$this->db_fantasy->where('G.collection_master_id',$post_data['collection_master_id']);
		}

	    if(!empty($post_data['from_date'])&&!empty($post_data['to_date']))
			$this->db_fantasy->where("DATE_FORMAT(G.season_scheduled_date, '%Y-%m-%d') BETWEEN '".$post_data['from_date']."' AND '".$post_data['to_date']."'");
		

		$this->db_fantasy->group_by('G.contest_unique_id');
		
		if(!empty($sort_field) && !empty($sort_order))
		{
			$this->db_fantasy->order_by($sort_field, $sort_order);
		}

		$sql = $this->db_fantasy->get();
		$result	= $sql->result_array();
		
		$result_array = [];
		if(!empty($result))
		{
			$contest_ids =  array_column($result, 'contest_id');
			$temp_prize_detail = $this->get_contest_prize_detail($contest_ids);
			$contest_prize_detail = array_column($temp_prize_detail,NULL,'contest_id');
			
			$contest_unique_ids =  array_column($result, 'contest_unique_id');
			$promocode_entry_result = $this->get_contest_promo_code_entry($contest_unique_ids);

			$promocode_entry = array();
			if(!empty($promocode_entry_result))
			{
				$promocode_entry = array_column($promocode_entry_result,'promocode_entry_fee_real','contest_unique_id');
			}

			$result_array["sum_join_real_amount"] = 0;
			$result_array["sum_join_bonus_amount"] = 0;
			$result_array["sum_join_winning_amount"] = 0;
			$result_array["sum_join_coin_amount"]=0;
			$result_array["sum_win_amount"] = 0;
			//$result_array["sum_total_entery_fee"]=0;
			$result_array["sum_profit_loss"] = 0;
			$result_array["sum_entry_fee"] = 0;
			$result_array["sum_site_rake"] = 0;
			$result_array["sum_min"] = 0;
			$result_array["sum_max"] = 0;
			$result_array["sum_total_user_joined"] = 0;
			$result_array["sum_system_teams"] = 0;
			$result_array["sum_real_teams"] = 0;
			$result_array["sum_max_bonus_allowed"] = 0;
			$result_array["sum_prize_pool"] = 0;
			$result_array["sum_total_entry_fee"] = 0;
			$result_array["sum_total_entry_fee_real"] = 0;
			$result_array["sum_botuser_total_real_entry_fee"] = 0;
			$result_array["sum_promocode_entry_fee_real"] = 0;
			$result_array["sum_total_win_coins"] = 0;
			$result_array["sum_total_win_bonus"] = 0;

			foreach($result as $contest)
			{
				$contest['siterake_amount'] = number_format((($contest['prize_pool']*$contest['site_rake'])/100),2,'.',',');

				if(isset($contest_prize_detail[$contest['contest_id']]))
				{
					$result_array["sum_total_entry_fee_real"]+=$contest_prize_detail[$contest['contest_id']]['total_join_real_amount'];
					$contest = array_merge($contest, $contest_prize_detail[$contest['contest_id']]);
				}
				$contest["profit_loss"]	= number_format((($contest["total_join_real_amount"]+$contest["total_join_winning_amount"]) - $contest["total_win_winning_amount"]),2,'.','');
				
				$contest["total_entry_fee"]		= $contest["total_join_real_amount"] + $contest["total_join_bonus_amount"] + $contest["total_join_winning_amount"];
				unset($contest['contest_id'],$contest['contest_unique_id']);
				$res[] = $contest;

			}

			// $result_array["sum_join_real_amount"] = number_format($result_array["sum_join_real_amount"],2,'.',',');
			// 	$result_array["sum_join_winning_amount"] = number_format($result_array["sum_join_winning_amount"],2,'.',',');
			// 	$result_array["sum_join_bonus_amount"] = number_format($result_array["sum_join_bonus_amount"],2,'.',',');
			// 	$result_array["sum_join_coin_amount"] = number_format($result_array["sum_join_coin_amount"],2,'.',',');
			// 	$result_array["sum_win_amount"] = number_format($result_array["sum_win_amount"],2,'.',',');
			// 	$result_array["sum_total_entery_fee"] = number_format($result_array["sum_total_entery_fee"],2,'.',',');
			// 	$result_array["sum_profit_loss"] = number_format($result_array["sum_profit_loss"],2,'.',',');
		}
		return $result_array;
	}

	public function get_lineup_master_contest($contest_ids = array())
	{

		$rs = $this->db_fantasy->select("LMC.lineup_master_contest_id,LMC.contest_id,C.user_id AS commis_user_id,is_winner,LM.user_id")
						->from(LINEUP_MASTER_CONTEST." LMC")
						->join(CONTEST.' C','C.contest_id = LMC.contest_id','INNER')
						->join(LINEUP_MASTER.' LM','LM.lineup_master_id = LMC.lineup_master_id','INNER')
						->where_in('LMC.contest_id',$contest_ids)
						->get()
						->result_array();
		return $rs;
	}


	public function get_contest_prize_detail($contest_ids)
	{

		$query = $this->db_user->select("(ROUND(IFNULL(sum(IF(ORD.source=1,ORD.real_amount,0)),'0'),2)+ROUND(IFNULL(sum(IF(ORD.source=1,ORD.winning_amount,0)),'0'),2)) as total_join_real_amount,			

		ROUND(IFNULL(sum(IF(ORD.source=1,ORD.bonus_amount,0)),'0'),2) as total_join_bonus_amount,			

		ROUND(IFNULL(sum(IF(ORD.source=1,ORD.points,0)),'0'),2) as total_join_coin_amount,

		ROUND(IFNULL(sum(IF(ORD.source=3,ORD.winning_amount,0)),'0'),2) as total_win_winning_amount,
		ROUND(IFNULL(sum(IF(ORD.source=3,ORD.points,0)),'0'),2) as total_win_coins,
		ROUND(IFNULL(sum(IF(ORD.source=3,ORD.bonus_amount,0)),'0'),2) as total_win_bonus,
		ROUND(IFNULL(sum(IF(ORD.source=3 AND U.is_systemuser=0,ORD.winning_amount,0)),'0'),2) as total_win_amount_to_real_user,

			ORD.reference_id as contest_id",FALSE)
				->from(ORDER." AS ORD")
				->join(USER.' U','U.user_id=ORD.user_id',"INNER")
				->where('(ORD.source = 1 or ORD.source = 3) AND ORD.status = 1') // join game  AND Debit and success		
				->where_in('ORD.reference_id',$contest_ids)
				->where('U.is_systemuser',0)		
				->get()->result_array();
		
		return $query;					
	}

	public function get_contest_promo_code_entry($contest_uids)
	{
		$query = $this->db_user->select("ROUND(IFNULL(sum(amount_received),'0'),2) as promocode_entry_fee_real,contest_unique_id",FALSE)
				->from(PROMO_CODE_EARNING)
				->where_in('contest_unique_id',$contest_uids)		
				->group_by('contest_unique_id')
				->get()->result_array();
		
		return $query;					
	}


	function user_list_report($post_data)
	{
		$this->db = $this->db_user;

		if(isset($post_data['sort_field']) && !empty($post_data['sort_field']) && in_array($post_data['sort_field'],array('added_date','winning_balance','first_name','user_name','email','balance','status','last_login','bonus_balance','pan_no','pan_verified')))
		{
			$sort_field = $post_data['sort_field'];
		} 
		else
		{
			$sort_field ='added_date';
		}

		if(isset($post_data['sort_order']) && !empty($post_data['sort_order'])  && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}
		else{
			$sort_order ='DESC';
		}

		$this->db->select("U.user_name as UserName,U.device_type as DeviceType, U.first_name as FirstName, U.last_name AS LastName, U.email AS Email,U.phone_no AS Mobile, U.balance AS DepositBalance, U.bonus_balance AS Bonus, U.winning_balance AS WinningBalance, U.pan_no AS PanCardNumber, U.pan_verified AS PanCardStatus, DATE_FORMAT(U.dob,'%d-%b-%Y') AS DateOfBirth,U.gender AS Gender,U.city AS City,U.address AS Street,U.zip_code as ZipCode, DATE_FORMAT(U.added_date,'%d-%b-%Y %H:%i') AS MemberSince, DATE_FORMAT(U.last_login, '%d-%b-%Y %H:%i') as LastLogin,U.status",FALSE)
						->from(USER.' AS U');
						
		
		if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db->where("DATE_FORMAT(U.added_date, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(U.added_date, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
		}

		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db->like('CONCAT(IFNULL(email,""),IFNULL(first_name,""),IFNULL(last_name,""),IFNULL(user_name,""),IFNULL(phone_no,""),CONCAT_WS(" ",first_name,last_name),IFNULL(pan_no,""))', $post_data['keyword']);
		}

		if(!empty($sort_field) && !empty($sort_order))
		{
			$this->db->order_by($sort_field, $sort_order);
		}
		else
		{
			$this->db->order_by('U.added_date', 'DESC');
		}

		$users_data = $this->db->group_by('U.user_id')
		->order_by($sort_field, $sort_order)
		->get()->result_array();

		if(!empty($users_data))
		{
			foreach ($users_data as $key => $value) {

				switch ($value['DeviceType']) {
					
					case 1:
						$device_type = "Android";
						break;
					case 2:
						$device_type = "ios";
						break;
					case 3:
						$device_type = "Web";
						break;
					case 4:
						$device_type = "Mobile";
						break;
				}

				switch ($value['status']) {
					case 0:
						$status = "User Inactive";
						break;
					case 1:
						$status = "User Active";
						break;
					case 2:
						$status = "User Email Not Verified";
						break;
					case 3:
						$status = "User Deleted";
						break;
					case 4:
						$status = "User Activation Pending";
						break;
				}

				switch ($value['PanCardStatus']) {
					case 0:
						$pancard_status = "PAN Card Pending";
						break;
					case 1:
						$pancard_status = "PAN Card Verified";
						break;
					case 2:
						$pancard_status = "PAN Card Refuted";
						break;
				}
				
				$value['DeviceType'] 	= $device_type;
				$value['status']		= $status;
				$value['PanCardStatus']	= $pancard_status;

				$result[] = $value;
			}
		}


		return $result;


	}

	public function match_report($post_data)
	{
		$sort_field = 'season_scheduled_date';
		$sort_order = 'DESC';
		$current_date = format_date();


		$sports_id = ($post_data['sports_id'])? $post_data['sports_id']:'7';
		
		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('contest_name','collection_name','size','entry_fee','season_scheduled_date','prize_pool','guaranteed_prize')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		if(isset($post_data['from_date'])){
			$from_date = date('Y-m-d',strtotime($post_data['from_date'])).' 00:00:00';
		}else{
			$from_date = date('Y-m-d',strtotime($current_date. ' - 10 days')).' 00:00:00';
		}

		if(isset($post_data['to_date'])){
			$to_date = date('Y-m-d',strtotime($post_data['to_date'])).' 23:59:59';
		}
		else{
			$to_date = date('Y-m-d',strtotime($current_date)).' 23:59:59';
		}

		$collections = $this->db_fantasy->select("CS.collection_master_id ,CS.season_scheduled_date, CM.collection_name, C.total_user_joined,C.total_system_user, round(sum(C.site_rake),2) AS total_rake, round(sum(C.prize_pool),2) as total_pool ,round(sum(CASE WHEN C.contest_access_type=1 THEN C.site_rake ELSE 0 END),2) as  private_site_rake,LM.league_id,GROUP_CONCAT(C.contest_unique_id) AS contest_unique_ids, GROUP_CONCAT(C.contest_id) AS contest_ids", false)
                ->from(LINEUP_MASTER_CONTEST . ' LMC')
                ->join(LINEUP_MASTER . ' LM', 'LMC.lineup_master_id = LM.lineup_master_id', 'INNER')
                ->join(COLLECTION_MASTER . ' CM', 'LM.collection_master_id = CM.collection_master_id', 'INNER')
                ->join(COLLECTION_SEASON . ' CS', 'CM.collection_master_id = CS.collection_master_id', 'INNER')
                ->join(CONTEST . ' C', 'C.contest_id = LMC.contest_id', 'INNER')
				->where("LMC.fee_refund", "0")
				->where("C.status >",1)
				->where("C.sports_id", $sports_id)
				->where("C.season_scheduled_date between '{$from_date}' and '{$to_date}'",null,false)
                ->where('CM.season_game_count',1)
				->order_by("C.season_scheduled_date", "DESC")
				->order_by("LMC.game_rank", "ASC")
				->group_by("CS.collection_master_id");
				if(isset($post_data['league_id']) && $post_data['league_id'] !=='')
				{
					$collections = $this->db_fantasy->where('LM.league_id',$post_data['league_id']);
				}

				if(isset($post_data['collection_master_id']) && $post_data['collection_master_id'] !=='')
				{
					$collections = $this->db_fantasy->where('CM.collection_master_id',$post_data['collection_master_id']);
				}

				$collections = $this->db_fantasy->get()->result_array();

				foreach($collections as $key=>$cmid){
					
					// $collections[$key]['contest_ids'] = explode(',',$new_collection_data[$collections[$key]['collection_master_id']]['contest_ids']);
					// $collections[$key]['contest_unique_id'] =explode(',',$new_collection_data[$collections[$key]['collection_master_id']]['contest_unique_ids']);
					
					$sql = $this->db_user->select("sum(O.real_amount+O.winning_amount) as real_entry_fee,sum(O.bonus_amount) as bonus_entry_fee,U.is_systemuser")
					->from(ORDER.' AS O')
					->join(USER.' AS U','O.user_id=U.user_id','left')
					->where_in('reference_id',explode(',',$collections[$key]['contest_ids']))
					->where("O.status",1)
					->where("O.source",1)
					->group_by('U.is_systemuser')
					->order_by('U.is_systemuser','ASC')
					->get()->result_array();
					$collections[$key]['real_user_entry_fee'] = (!empty($sql[0]))? $sql[0]['real_entry_fee']:0;
					$collections[$key]['real_bonus_fee'] = (!empty($sql[0]))? $sql[0]['bonus_entry_fee']:0;
					$collections[$key]['bot_real_fee'] = (!empty($sql[1]))? $sql[1]['real_entry_fee']:0;

					$winning_data = $this->db_user->select("is_systemuser,sum(O.bonus_amount) as bonus_win, sum(O.winning_amount) as winning_win,sum(O.pointS) as point_win")
					->from(ORDER.' AS O')
					->join(USER.' AS U','O.user_id=U.user_id','left')
					->where_in('reference_id',explode(',',$collections[$key]['contest_ids']))
					->where("O.status",1)
					->where("O.source",3)
					->group_by('U.is_systemuser')
					->order_by('U.is_systemuser','ASC')
					->get()->result_array();

					$collections[$key]['real_distribution'] = (!empty($winning_data[0]))? $winning_data[0]['winning_win']:0;
					$collections[$key]['bonus_distribution'] = (!empty($winning_data[0]))? $winning_data[0]['bonus_win']:0;
					$collections[$key]['coin_distribution'] = (!empty($winning_data[0]))? $winning_data[0]['point_win']:0;
					$collections[$key]['bot_real_winning'] = (!empty($winning_data[1]))? $winning_data[1]['winning_win']:0;

					$promocode_data = $this->db_user->select("sum(amount_received) as promocode_discount")
					->from(PROMO_CODE_EARNING)
					->where_in('contest_unique_id',explode(',',$collections[$key]['contest_unique_ids']))
					->where('is_processed','1')
					->get()->row();
					// echo $this->db->last_query();exit;

					$collections[$key]['promocode_discount'] = (!empty($promocode_data))? $promocode_data->promocode_discount:0;
					
					$collections[$key]['match'] 			= $collections[$key]['collection_name'];
					$collections[$key]['real_users'] 		= $collections[$key]['total_user_joined'] - $collections[$key]['total_system_user'];
					$collections[$key]['site_rake'] 		= $collections[$key]['total_rake'];
					$collections[$key]['prize_pool'] 		= $collections[$key]['total_pool'];
					$collections[$key]['bonus_enter_fee'] 	= $collections[$key]['real_bonus_fee'];
					$collections[$key]['revenew'] 			= $collections[$key]['real_user_entry_fee']+$collections[$key]['private_site_rake'];
					$collections[$key]['profit_loss'] 		= $collections[$key]['revenew'] - $collections[$key]['real_distribution'];	
					
					unset(
						$collections[$key]['total_system_user'],
						$collections[$key]['real_bonus_fee'],
						$collections[$key]['total_pool'],
						$collections[$key]['total_rake'],
						$collections[$key]['collection_name'],
						$collections[$key]['contest_unique_ids'],
						$collections[$key]['contest_ids'],
						$collections[$key]['total_user_joined']
					);
				}
				return $collections;
	}

	function get_all_users_contest_revenue($user_ids = array()) 
	{
                $this->db_fantasy->select("LM.user_id, LMC.contest_id, C.site_rake, C.entry_fee",FALSE)
				->from(CONTEST." C")
				->join(LINEUP_MASTER_CONTEST." LMC","LMC.contest_id = C.contest_id","INNER")
				->join(LINEUP_MASTER." LM","LMC.lineup_master_id= LM.lineup_master_id","INNER")
				->where("LM.user_id>",0)
				->where_in("C.status",array(2,3))
				->where_in("C.prize_type",array(0,1));
                if(!empty($user_ids))
                {
                    $this->db_fantasy->where_in("LM.user_id", $user_ids);
                }

		$record = $this->db_fantasy->get()->result_array();
		
                /*echo $this->db_fantasy->last_query(); exit();*/
		return $record;					
	}

	function user_report($post_data){
		$result = array();
		$revenew_result = array();		// print_r(); die;
		$total = 0;

		if(!empty($post_data['sort_field']) && in_array($post_data['sort_field'],array('user_name','email', 'balance', 'bonus_balance', 'winning_balance', 'country_name','dob','added_date', 'last_login', 'first_deposit', 'first_deposit_date', 'last_deposit_date', 'deposit_by_user', 'withdraw_by_user', 'prize_amount_won','first_name')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(!empty($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$userData = $this->db_user->select("U.user_id, U.user_unique_id,IFNULL(U.user_name,'-') AS user_name, CONCAT_WS(' ',IFNULL(U.first_name,'-'),U.last_name) AS name,IFNULL(U.email,'-') as email,IFNULL(U.phone_no,'-') AS phone_no,U.added_date, DATE_FORMAT(U.last_login,'".MYSQL_DATE_TIME_FORMAT."') AS last_login_date,
                        ODF.first_deposit, ODF.date_added as first_deposit_date, DATE_FORMAT(ODF.date_added,'".MYSQL_DATE_TIME_FORMAT."') AS first_deposit_date_format,DATE_FORMAT(ODL.last_deposit_date,'".MYSQL_DATE_TIME_FORMAT."') AS last_deposit_date_format,
                        sum(if(source=7,O.real_amount+O.bonus_amount,0)) as deposit_by_user,
                        sum(if(source=0 AND type=0,O.real_amount+O.bonus_amount+O.winning_amount,0)) as deposit_by_admin,
                        sum(if(source=8,O.real_amount+O.bonus_amount+O.winning_amount,0)) as withdraw_by_user,
                        U.balance, U.winning_balance, U.bonus_balance,
			sum(if(source=3,O.real_amount+O.bonus_amount+O.winning_amount,0)) as prize_amount_won,
                        (sum(if(source=1,O.real_amount+O.bonus_amount+O.winning_amount,0)) -sum(if(source=2,O.real_amount+O.bonus_amount+O.winning_amount,0))) as entry_fee_paid,
                        CASE 
							WHEN U.device_type = 1 THEN 'Android' 
							WHEN U.device_type = 2 THEN 'IOS' 
							WHEN U.device_type = 3 THEN 'WEB' 
							WHEN U.device_type = 4 THEN 'Mobile Browser' 
							ELSE 'Other' 
							END AS device,
						(CASE 
							WHEN U.phone_verfied = 0 THEN 'Not Verfied' 
							WHEN U.phone_verfied = 1 THEN 'Verfied' 
							ELSE 'other' END) AS phone_verfied,
						(CASE 
							WHEN U.phone_verfied = 0 OR U.email = '' OR U.user_name = '' THEN 'profile incomplete' 
							WHEN U.phone_verfied = 1 AND U.email != '' AND U.user_name != '' THEN 'profile complete' 
							ELSE 'other' END) AS profile
                        ", FALSE) 
							->select('0 as matches_played,0 as matches_won,0 as matches_lost')
							->from(USER.' AS U')
							->join(ORDER.' AS O','O.user_id = U.user_id AND O.status = 1 AND O.source IN (0,1,2,3,7,8)','LEFT')
							->join('(SELECT (real_amount+bonus_amount) as first_deposit, date_added, user_id FROM vi_order AS DF WHERE DF.status = 1 AND DF.source = 7 GROUP BY DF.user_id ) AS ODF', 'ODF.user_id = U.user_id', 'LEFT')
                                                        ->join('(SELECT MAX(date_added) AS last_deposit_date, user_id FROM vi_order AS DL WHERE DL.status = 1 AND DL.source = 7 GROUP BY DL.user_id ) AS ODL', 'ODL.user_id = U.user_id', 'LEFT')
							->group_by("U.user_id")
							->order_by($sort_field, $sort_order);
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
			$this->db_user->like('CONCAT(U.user_unique_id,IFNULL(U.email,""),IFNULL(U.phone_no,""),IFNULL(U.user_name,""),CONCAT_WS(" ",U.first_name,U.last_name))', $post_data['keyword']);
		}

		//0-phone, 1-email
		if(LOGIN_FLOW == 1 || LOGIN_FLOW == 2)
		{
			//email_verified : 0->not verfied, 1->verfied
			if(isset($post_data['email_verified']) && in_array($post_data['email_verified'],[0,1]) && $post_data['email_verified']!="" && $post_data['profile_status']=="")
			{ 
				$this->db->where("U.email_verified",$post_data['email_verified']);
			}	
		}

		//phone_verfied : 0->not verfied, 1->verfied
		if(isset($post_data['phone_verfied']) && in_array($post_data['phone_verfied'],[0,1]) && $post_data['phone_verfied']!="" && $post_data['profile_status']=="")
		{ 
			$this->db->where("U.phone_verfied",$post_data['phone_verfied']);
		}
		//device type 1->android, 2->ios, 3->web, 4->mobile site
		if(isset($post_data['device_type']) && in_array($post_data['device_type'],[1,2,3,4]) && $post_data['device_type']!="")
		{ 
			$this->db->where("U.device_type",$post_data['device_type']);
		}

		//profile_status 1->complte & 0->incomplte
		if(isset($post_data['profile_status']) && in_array($post_data['profile_status'],[0,1]) && $post_data['profile_status']!="")
		{ 
			if($post_data['profile_status']==1)
			{
				$profile_status=array(
					"U.phone_verfied"=>"1",
					"U.email !="=>"",
					"U.user_name !="=>"",
				);
				$this->db->where($profile_status);
			}
			else
			{
				$this->db->where('(U.phone_verfied="0" OR U.email = "" OR U.user_name ="")',NULL,false);		
			}
		}

		if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$this->db_user->where("DATE_FORMAT(U.added_date, '%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(U.added_date, '%Y-%m-%d') <= '".$post_data['to_date']."' ");
		}
                
		$userData = $this->db_user->get()->result_array();

		if(!empty($userData))
		{
			// $total = $userData["total"];
			$user_ids =  array_column($userData, 'user_id');
			$post_data['user_ids'] = $user_ids;
			// Get Contest played, win and lost data
			$post_win_loss_response = array();
			$post_revenew_arr = array();

			if(!empty($user_ids))
				{
					$user_id_array = array_chunk($post_data['user_ids'], 1000);
					// print_r($post_data['user_ids']); die;
					// $this->load->model('contest/Contest_model');
					foreach($user_id_array as $user_id){
					$contest_data   = $this->get_users_win_loss($user_id);
					$post_win_loss_response = array_merge($result, $contest_data);
					$revenew_data  = $this->get_all_users_contest_revenue($user_id);
					$post_revenew_arr = array_merge($revenew_result,$revenew_data);
					// print_r($post_revenew_arr);exit;
					}
				}
				$contest_win_loss_data  = array();
				if(!empty($post_win_loss_response))
				{
					foreach($post_win_loss_response as $win_loss_data){
						$contest_win_loss_data[$win_loss_data['user_id']] = $win_loss_data;
					}
				}
				// print_r($contest_win_loss_data);exit;
				// Get user's contest revenue data
				$user_revenue = array();
				foreach($post_revenew_arr as $c_data)
				{
					if($c_data['site_rake'] > 0){
						//$contest_user_data[$c_data['user_id']][] = $c_data['entry_fee'] * ($c_data['site_rake']/100);
						//FORMULA :: SUM(entry fee * siterake % per contest)
						if(isset($user_revenue[$c_data['user_id']])){
							$user_revenue[$c_data['user_id']]['revenue_generated'] += $c_data['entry_fee'] * ($c_data['site_rake']/100);
						}
						else{
							$user_revenue[$c_data['user_id']]['revenue_generated'] = $c_data['entry_fee'] * ($c_data['site_rake']/100);
						}       
					}
				}
				// print_r($user_revenue);exit;
			foreach ($userData as $key => $value) 
			{
				$result_temp = array();
				$value['first_deposit'] = ($value['first_deposit']) ? number_format($value['first_deposit'], 2, '.', ',') : '';
				$value['deposit_by_user'] = number_format($value['deposit_by_user'], 2, '.', ',');
				$value['deposit_by_admin'] = number_format($value['deposit_by_admin'], 2, '.', ',');
				$value['withdraw_by_user'] = number_format($value['withdraw_by_user'], 2, '.', ',');
				$value['winning_balance'] = number_format($value['winning_balance'], 2, '.', ',');
				$value['balance'] = number_format($value['balance'], 2, '.', ',');
				$value['bonus_balance'] = number_format($value['bonus_balance'], 2, '.', ',');
				$value['prize_amount_won'] = number_format($value['prize_amount_won'], 2, '.', ',');
				$value['prize_amount_lost'] = number_format($value['entry_fee_paid'] - $value['prize_amount_won'], 2, '.', ',');
					
					$value['revenue_generated'] = 0;
					if(!empty($user_revenue[$value['user_id']])){
						$value['revenue_generated'] = number_format($user_revenue[$value['user_id']]['revenue_generated'], 2, '.', ',');
					}
					
					if(!empty($contest_win_loss_data[$value['user_id']]))
					{
						$result_temp  = array_merge($value,$contest_win_loss_data[$value['user_id']]);
					}
					else
					{
						$result_temp = $value;
						$result_temp['matches_played'] = 0;
						$result_temp['matches_won'] = 0;
						$result_temp['matches_lost'] = 0;
					}
				$result[] = $result_temp;
			}
		}
		return $result;
	}

	public function get_transaction_report($post_data)
	{
		$sort_field = 'date_added';
        $sort_order = 'DESC';
		$transaction_source_key_map = array();
		$transaction_source_key_map[1] =array('key' => 'contest_name');
		
		$query = $this->db_user->select('USR.user_unique_id, USR.email, USR.user_name,ORD.source,ORD.source_id,
         ORD.type,TXN.bank_txn_id as TransactionId,ORD.real_amount,ORD.bonus_amount,ORD.status,
        ORD.winning_amount,ORD.points as Coins,
        DATE_FORMAT(ORD.date_added,"'.MYSQL_DATE_TIME_FORMAT.'") as OrderDate',FALSE)
		->from(ORDER." AS ORD")
		->join(USER." AS USR","USR.user_id = ORD.user_id")
		->join(TRANSACTION." AS TXN","TXN.transaction_id = ORD.source_id and ORD.source = 7","LEFT");

		if(isset($post_data['type']) && $post_data['type'] != "")
        {
            $this->db_user->where("ORD.type",$post_data['type']);
        }

        if(isset($post_data['status']) && $post_data['status'] != "")
        {
            $this->db_user->where("ORD.status",$post_data['status']);
        }
        
        if(isset($post_data['source']) && $post_data['source'] != "")
        {
            if($post_data['source']=='other')
            {
                $exclude_sources = [0,1,2,3,4,5,6,7,8,381,240,241,242,450,451,452,40,41,50,147,53,144,56,58,99]; //THESE SOURCE ARE TAKEN INDIVIDUALLY IN DISCRIPTION FILTER.
                $this->db_user->where_not_in("ORD.source",$exclude_sources);
            }else{
            $this->db_user->where("ORD.source",$post_data['source']);
            }
        }

        if(isset($post_data['keyword']) && $post_data['keyword'] != "")
        {
            $this->db_user->like('CONCAT(IFNULL(USR.user_name,""),IFNULL(USR.user_unique_id,""),IFNULL(USR.email,""))', $post_data['keyword']);
        }

        if(isset($post_data['from_date']) && $post_data['from_date']!="" && isset($post_data['to_date']) && $post_data['to_date']!="")
        {
            $this->db_user->where("DATE_FORMAT(ORD.date_added,'%Y-%m-%d') >= '".$post_data['from_date']."' and DATE_FORMAT(ORD.date_added,'%Y-%m-%d') <= '".$post_data['to_date']."'");
        }
    
        $query = $this->db_user->get();  
        $result = $query->result_array();
		// echo $this->db_user->last_query();exit;
		if(!empty($result))
		{
			// get transaction messages
            $transaction_msgs =  $this->db_user->select()
			->from(TRANSACTION_MESSAGES)->get()->result_array();
            if(!empty($transaction_msgs))
            {
                $transaction_by_source = array_column($transaction_msgs,NULL,'source');
            }
			
			foreach ($result as $key => $rs) {
				$result[$key]["Event"] = "-";
				$result[$key]["contest_name"] = "-";
				$result[$key]["match_date"] = "-";
				$result[$key]["contest_type"] = "-";

				if($rs['source']==1 || $rs['source']==2 || $rs['source']==3 || $rs['source']==20 || $rs['source']==21)
				{
					$contest_data   = $this->get_content_info($rs['source_id']);
	
					if(!empty($contest_data))
					{
						$result[$key] = array_merge($result[$key],$contest_data);
					}
				}

				// $result[$key]["status"] = $status_arr[$rs['status']];
				// print_r($rs);exit; 
				switch ($rs['source']){
					
					case 0:
						$rs["type"] == 0 && $result[$key]["Event"] = "Amount deposited by admin";
						$rs["type"] == 1 && $result[$key]["Event"] = "Amount transferred to bank";
						break;
					case 30:
					case 31:
					case 32:
						  
							$result[$key]["Event"] = "Promocode Amount Received";
							//For real cash
							if($rs['real_amount'] > 0)
							{
								$result[$key]["Event"] = "Promocode Cash Received";
							}   
							//For bonus cash
							if($rs['bonus_amount'] > 0)
							{
								$result[$key]["Event"] = "Promocode Bonus Received";
							}
					break;
					case 322:
						//$value["trans_desc"] = "You win spin the win";
						//For real cash
						if ($rs['real_amount'] > 0) {
							$result[$key]["Event"] = "Earned real cash from spin the wheel";
						}
						//For bonus cash
						if ($rs['bonus_amount'] > 0) {
							$result[$key]["Event"] = "Earned bonus cash from spin the wheel";
						}
	
						if ($rs['points'] > 0) {
							$result[$key]["Event"] = "Earned coins from spin the wheel";
						}
	
						if(!empty($rs["custom_data"])){
							$result[$key]["Event"] = "Earned prize from spin the wheel";
						}
	
						break; 
					
					default:
						if(isset($transaction_msgs[$rs["source"]]))
						{
							if(in_array($rs["source"], array(59,60,61,62,63,64,65,66,67))) {
								if(INT_VERSION == 1) {
									$transaction_msgs[$rs["source"]]['en_message'] = str_replace("{{p_to_id}}", "ID", $transaction_msgs[$rs["source"]]['en_message']);                                
								} else {
									$transaction_msgs[$rs["source"]]['en_message'] = str_replace("{{p_to_id}}", "PAN", $transaction_msgs[$rs["source"]]['en_message']);
								}
							}
	
							if(isset($transaction_source_key_map[$rs["source"]]))
							{
								// print_r($transaction_source_key_map[$rs["source"]]['key']);exit;
								// $result['result'][$key]['Event'] =  sprintf($transaction_messages[$rs["source"]]['en_message'], $rs[$this->transaction_source_key_map[$rs["source"]]['key']]);
							   $result[$key]['Event'] =  sprintf($transaction_by_source[$rs["source"]]['en_message'], $result[$key][$transaction_source_key_map[$rs["source"]]['key']]);
							}
							else
							{
								$result[$key]['Event'] = $transaction_msgs[$rs["source"]]['en_message'];
							}
						}
						
				}
	
				switch ($rs['type']){
					case 0:
					   $result[$key]['type'] = 'Credit';
					break;
					case 1:
						$result[$key]['type'] = 'Debit';
					break;
				}
				
				 $user_revenue = array();
	
				if($rs['status']==1){
					$result[$key]['status'] = "Completed";
				}else if($rs['status']==2){
					$result[$key]['status'] = "Failed";
				}else{
					$result[$key]['status'] = "Pending";
				}
	
				unset( $result[$key]['source_id'],$result[$key]['source']);
			}
			return $result;
		}
		else{
			echo "else part";exit;
			return array();
		}  
       
	}

	/**
	 * function to get contest info used in transaction report export 
	 * @param lineupMasterContestId
	 */
	public function get_content_info($lmc_id)
	{
        $this->db_fantasy->select("CM.collection_name as contest_name,DATE_FORMAT(C.season_scheduled_date,'".MYSQL_DATE_TIME_FORMAT."') as match_date,MG.group_name as contest_type",FALSE)
		->from(CONTEST." C")
        ->join(COLLECTION_MASTER." CM","CM.collection_master_id = C.collection_master_id","INNER")
        ->join(LINEUP_MASTER_CONTEST." LMC","LMC.contest_id = C.contest_id","INNER")
        ->join(MASTER_GROUP." MG","MG.group_id = C.group_id","INNER");
        $this->db_fantasy->where("LMC.lineup_master_contest_id", $lmc_id);
        $this->db_fantasy->group_by('LMC.lineup_master_contest_id');        
		$record = $this->db_fantasy->get()->row_array();
		return $record;
	}

}