<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends MY_Model {

	public function __construct()
	{

		parent::__construct();
		$this->load->database('user_db');
		$this->db_analytics		= $this->load->database('db_analytics', TRUE);
				
	}
	function getFirstTimeUserAmt($param){
		$endDate   = $param['endDate'];
		$startDate = $param['startDate'];
		$return    =  array('totalDeposit'=>0,'totalUser'=>0);
		$query = $this->db->query('SELECT 
		sum(real_amount) as totalDeposit,
		SUM(total_count) as totalUser 
		from ( 
			SELECT order_id,real_amount,count(order_id) as total_count FROM `vi_order` 
			WHERE  source=7 and status=1 
			and `date_added` BETWEEN "'.$startDate.'" AND "'.$endDate.'"
				group by user_id HAVING total_count = 1
			) as Temp');

		$result = $query->result();
		if(!empty($result)){
			//print_r($result[0]->totalDeposit); die;
			$return['totalDeposit'] = $result[0]->totalDeposit;
			$return['totalUser'] =   $result[0]->totalUser;
		}
		return $return;
	}
	/**
	* Method : get_devices
	* Output : All device user data to plot graphs
	* Input : Filters of date range , type like weekly , monthly, yearly
	* Date : 7 June 2019
	**/
	public function get_devices($post)
	{
			
       
       

        if (isset($post['StartDate']))
            $startDate = date("Y-m-d", strtotime($post['StartDate']));
        else
            $startDate = date('Y-m-d', strtotime('-10 day'));
        if (isset($post['EndDate']))
            $endDate = date("Y-m-d", strtotime($post['EndDate']));
        else
            $endDate = date("Y-m-d");
		 
			
			$query = $this->db->query('SELECT count(user_id) as devicetypecount ,device_type FROM `vi_user` 
			WHERE `added_date` BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59" GROUP BY device_type');

		 $result = $query->result();
		// echo $this->db->last_query(); die;
		
		 if(!empty($result)){
			$devices=array();
			$deviceNames = array('1'=>'Android','2'=>'Iphone','3'=>'Web');
			foreach($result as $devicetype){
			  
			   $devices[] = array('y'=>(int)$devicetype->devicetypecount,'name'=>$deviceNames[$devicetype->device_type]);

			}
			 
         	$Return['data']= $devices;	
         	
         }else{
			$Return['data']= array();	
         }
		
		 return $Return;

	}
	/**
	* Method : get_leaderboard
	* Output : Top 5 users with deposit and winning
	* Input : Filters of date range , type like weekly , monthly, yearly
	* Date : 13 June 2019
	**/
	public function get_leaderboard($post)
	{
			
       
       

        if (isset($post['startDate']))
            $startDate = date("Y-m-d", strtotime($post['startDate']));
        else
            $startDate = date('Y-m-d', strtotime('-10 day'));
        if (isset($post['endDate']))
            $endDate = date("Y-m-d", strtotime($post['endDate']));
        else
            $endDate = date("Y-m-d");
		 
			
			$query = $this->db->query('SELECT vi_user.user_unique_id,vi_user.user_name,vi_user.added_date,vi_user.first_name,vi_user.last_name, vi_user.user_id,SUM(CASE WHEN source IN (7) THEN real_amount ELSE 0 END) as totalDeposit,SUM(CASE WHEN source=1 THEN real_amount ELSE 0 END) as totalWinning FROM `vi_order` LEFT JOIN vi_user ON vi_user.user_id=vi_order.user_id WHERE `added_date` BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59"  and type=0 and vi_order.status=1 GROUP BY user_id ORDER BY totalDeposit DESC LIMIT 0,5'); 

		$depositWinningresult = $query->result(); 
		 //echo $this->db->last_query(); die;
	
		if(!empty($depositWinningresult)){
		$leaderboardData=array();
		foreach($depositWinningresult as $depositWinningresultRow){
			
			$leaderboardData[] = array('user_unique_id'=>$depositWinningresultRow->user_unique_id,'name'=>$depositWinningresultRow->user_name,'totalDeposit'=>$depositWinningresultRow->totalDeposit,'totalWinning'=>$depositWinningresultRow->totalWinning,'added_date'=>$depositWinningresultRow->added_date);

		}
			
		$Return['data']= $leaderboardData;	
		
		}else{
		$Return['data']= array();	
		}
		
		 return $Return;

	}
	/**
	* Method : get_calculated_summary 
	* Output : All Counts of visitors, signup,Wallet balance and users of diposited users, firsttime deposite users count and amount 
	* Input : Filters of date range , type like weekly , monthly, yearly
	* Date : 18 june 2019
	**/
	public function get_calculated_summary($post)
	{
			
		if (isset($post['startDate']))
            $startDate = date("Y-m-d", strtotime($post['startDate']));
        else
            $startDate = date('Y-m-d', strtotime('-10 day'));
        if (isset($post['endDate']))
            $endDate = date("Y-m-d", strtotime($post['endDate']));
        else
            $endDate = date("Y-m-d");
		
		//compare from last intreval
		$start = strtotime($startDate); $end =  strtotime($endDate); 
		$days_between = ceil(abs($start - $end) / 86400);
		$last_startdate = date('Y-m-d', strtotime("-$days_between days",$start));
		$last_enddate = date('Y-m-d', strtotime("-1 days",$start)); //$endDate;
		$active_users = $this->get_active_passive_users($post);
		// print_r($active_users);exit;
		$this->db_analytics->select("
									SUM(signup) as signup,
									SUM(wallet_balance) as wallet_balance,
									SUM(wallet_balance_users) as wallet_balance_users,
									SUM(first_time_deposit) as first_time_deposit,
									SUM(first_time_depositors) as first_time_depositors,
									SUM(deposit) AS deposit,
									sum(deposit_users) AS deposit_users,
									SUM(site_rake) as site_rake,
									SUM(site_rake_users) as site_rake_users,
									SUM(free_users) as free_users,
									SUM(paid_users) as paid_users,
									SUM(referral_amount_distributed) as referral_amount_distributed,
									SUM(no_of_referrals) as no_of_referrals,
									")
		->from(ANALYTICS)
		->where("created BETWEEN '".$startDate."' AND '".$endDate."'");
		
		
			
		 $res = $this->db_analytics->get()->result_array();
		/// print_r( $result); die;
		 $result=$res[0];
		 $return = array();
		 $return['signup'] = ($result['signup'])?$result['signup']:0;
		 $return['wallet_balance'] = ($result['wallet_balance'])?$result['wallet_balance']:0;
		 $return['wallet_balance_users'] = ($result['wallet_balance_users'])?$result['wallet_balance_users']:0;
		 $return['first_time_deposit'] = ($result['first_time_deposit'])?$result['first_time_deposit']:0;
		 $return['first_time_depositors'] = ($result['first_time_depositors'])?$result['first_time_depositors']:0;
		 $return['deposit'] = ($result['deposit'])?$result['deposit']:0;
		 $return['deposit_users'] = ($result['deposit_users'])?$result['deposit_users']:0;
		 $return['site_rake'] = ($result['site_rake'])?$result['site_rake']:0;
		 $return['site_rake_users'] = ($result['site_rake_users'])?$result['site_rake_users']:0;
		 $return['free_users'] = ($result['free_users'])?$result['free_users']:0;
		 $return['paid_users'] = ($result['paid_users'])?$result['paid_users']:0;
		 $return['referral_amount_distributed'] = ($result['referral_amount_distributed'])?$result['referral_amount_distributed']:0;
		 $return['no_of_referrals'] = ($result['no_of_referrals'])?$result['no_of_referrals']:0;
		 $return['active_users'] = ($active_users['count']['active'])?$active_users['count']['active']:0;
		 $return['passive_users'] = ($active_users['count']['passive'])?$active_users['count']['passive']:0;
		 $return['percent_active'] = ($active_users['percent_active'])?$active_users['percent_active']:0;
		 $return['percent_passive'] = ($active_users['percent_passive'])?$active_users['percent_passive']:0;

		 // data to test only 
			$return['before_active_users']	= $active_users['before_active_users'];
			$return['after_active_users']	= $active_users['after_active_users'];
			$return['before_passive_users']	= $active_users['before_passive_users'];
			$return['after_passive_users']	= $active_users['after_passive_users'];
			$return['after_signup_users'] =  $return['signup'];
			$return['before_signup_users'] =  0;


		 $query_interval = $this->db_analytics->query("SELECT SUM(signup) as signup,SUM(first_time_deposit) as first_time_deposit,SUM(deposit) AS deposit,SUM(active_users) AS total_active_users,SUM(passive_users) AS total_passive_users From vi_".ANALYTICS." WHERE  created BETWEEN '".$last_startdate."' AND '".$last_enddate."'");
		 $interval_res = $query_interval->result_array();
		 
		 //$new_signup = round($return['signup'] -	$interval_res[0]['signup']);
		 //echo $return['signup'].'==='.$interval_res[0]['signup'];

		 if($interval_res[0]['signup']>0){ 
		 	$return['before_signup_users'] =  $interval_res[0]['signup'];
		 	$return['percent_signup'] = (round( (($return['signup']-$interval_res[0]['signup'])/($interval_res[0]['signup']))*100 ))?round( (($return['signup']-$interval_res[0]['signup'])/($interval_res[0]['signup']))*100 ):0;
		 } else{
			$return['percent_signup'] ='--';
		 }
		 if($interval_res[0]['deposit']>0 && $return['deposit']>0){ 	
		 	$return['percent_firsttime'] = (round( ((1-($return['deposit']-$return['first_time_deposit'])/$return['deposit']))*100 ))?round( ((1-($return['deposit']-$return['first_time_deposit'])/$return['deposit']))*100 ):0;
		 } else{
			$return['percent_firsttime'] ='--';
		 }
		 
		return $return;

	}
	/**
	 * function to get json extract and count unique user id as active and passive users.
	 */
	/**
	 * formula :   				(AFTER USERS - BEFORE USERS) x100
	*                           __________________________________
	*                                     BEFORE USERS
	*  FOR SIGNUP, ACTIVE , PASSIVE USER PERCENTAGE
	*/

	public function get_active_passive_users($post){
		
		if (isset($post['startDate']))
            $startDate = date("Y-m-d", strtotime($post['startDate']));
        else
            $startDate = date('Y-m-d', strtotime('-10 day'));
        if (isset($post['endDate']))
            $endDate = date("Y-m-d", strtotime($post['endDate']));
        else
			$endDate = date("Y-m-d");

			//compare from last intreval
			$start = strtotime($startDate); $end =  strtotime($endDate); 
			$days_between = ceil(abs($start - $end) / 86400);
			$last_startdate = date('Y-m-d', strtotime("-$days_between days",$start));
			$last_enddate =  date('Y-m-d', strtotime("-1 days",$start)); //$endDate;
			$return = array();

			$result = $this->db_analytics->select("id,json_extract(active_users, '$.user_ids') as active_users,json_extract(passive_users, '$.user_ids') as passive_users")
			->from(ANALYTICS)
		->where("created BETWEEN '".$startDate."' AND '".$endDate."'");
		 $result = $this->db_analytics->get()->result_array();
		 $data = array(
			 "active"=>array(),
			 "passive"=>array()
		 );
		 foreach($result as $res){
			$active_users = json_decode($res['active_users']);
			$passive_users = json_decode($res['passive_users']);
			if(is_array($active_users)){
				$data['active'] = array_merge($data['active'],json_decode($res['active_users']));
			}
			if(is_array($passive_users)){
				$data['passive'] = array_merge($data['passive'],json_decode($res['passive_users']));
			}
		 }
		 $data['active'] = count(array_unique($data['active']));
		 $data['passive'] = count(array_unique($data['passive']));
		 $return['count']=$data;
		// print_r($return);exit;
		 $query_interval = $this->db_analytics->select("id,json_extract(active_users, '$.user_ids') as active_users,json_extract(passive_users, '$.user_ids') as passive_users")
		 ->from(ANALYTICS)
	 	 ->where("created BETWEEN '".$last_startdate."' AND '".$last_enddate."'");
		 $interval_res = $query_interval->get()->result_array();

		 $interval_data = array(
			"active"=>array(),
			"passive"=>array()
		 );
		 foreach($interval_res as $int_res){
			$inter_active_users = json_decode($int_res['active_users']);
			$inter_passive_users = json_decode($int_res['passive_users']);
			if(is_array($inter_active_users)){
				array_push($interval_data['active'],$inter_active_users[0]);
			}
			if(is_array($inter_passive_users)){
				array_push($interval_data['passive'],$inter_passive_users[0]);
			}
		 }
		 $interval_data['active'] = count(array_unique($interval_data['active']));
		 $interval_data['passive'] = count(array_unique($interval_data['passive']));
			
		 if($interval_data['active']>0){ 
			$return['percent_active'] = (round( (($data['active'] - $interval_data['active'])/$interval_data['active'])*100 ))?round( (($data['active'] - $interval_data['active'])/$interval_data['active'])*100 ):0;
		 } else{
			$return['percent_active'] ='--';
		 }

		if($interval_data['passive']>0){ 
			$return['percent_passive'] = (round( (($data['passive'] - $interval_data['passive'])/$interval_data['passive'])*100 ))?round( (($data['passive'] - $interval_data['passive'])/$interval_data['passive'])*100 ):0;
		} else{
			$return['percent_passive'] ='--';
		}
		$return['before_active_users'] = $interval_data['active'];
		$return['after_active_users'] = $data['active'];
		$return['before_passive_users'] = $interval_data['passive'];
		$return['after_passive_users'] = $data['passive'];

		 return $return;
	}
	/**
	* Method : get_timelines 
	* Output : All timelines data to plot graphs
	* Input : Filters of date range , type like weekly , monthly, yearly
	* Date : 5 June 2019
	**/
	public function get_timelines($post){

		if (isset($post['startDate']))
		$startDate = date("Y-m-d", strtotime($post['startDate']));
		else
			$startDate = date('Y-m-d', strtotime('-10 day'));
		if (isset($post['endDate']))
			$endDate = date("Y-m-d", strtotime($post['endDate']));
		else
			$endDate = date("Y-m-d");

		$filter_type = $post['filtertype']; //daily weekly monthly
		if($filter_type=='daily'){
			$this->db_analytics->select("deposit,deposit_users,created")
							   ->from(ANALYTICS)
							   ->where("created BETWEEN '".$startDate."' AND '".$endDate."'")
							   ->group_by("created");
			$depositresult = $this->db_analytics->get()->result();				   
		}
		if($filter_type=='weekly'){


		
		$query = $this->db_analytics->query("SELECT t1.week_name,t1.monthyear, t1.week_no, concat_ws(' ',t1.week_no,t1.d1,t1.d2) as created, t1.year, COALESCE(SUM(t1.deposit+t2.deposit), 0) AS deposit,COALESCE(SUM(t1.deposit_users+t2.deposit_users), 0) AS deposit_users

        FROM

        (

          SELECT

          DATE_FORMAT(a.Date,'%W') AS week_name,

          DATE_FORMAT(a.Date,'%Y-%m-%d') AS monthyear,

          DATE_FORMAT(a.Date,'week %u') AS week_no,
		  DATE_ADD(a.Date, INTERVAL(-WEEKDAY(a.Date)) DAY) as d1,
		  a.Date as d2,

          WEEK(a.date) AS weekid,

          DATE_FORMAT(a.Date, '%u-%Y') AS md,

          DATE_FORMAT(a.Date, '%Y') AS YEAR,

          '0' AS  deposit, '0' AS  deposit_users

          FROM (

            SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE

            FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a

            CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b

            CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c

          ) a

          WHERE a.Date BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'

          GROUP BY md

        )t1

        LEFT JOIN

        (

          SELECT DATE_FORMAT(created, '%u') AS WEEK, SUM(deposit) AS deposit ,DATE_FORMAT(created, '%u-%Y') AS md,SUM(deposit_users) AS deposit_users

          FROM vi_analytics

          WHERE created BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'

          GROUP BY md

        )t2

        ON t2.md = t1.md

        GROUP BY t1.md

        ORDER BY t1.year ASC,t1.week_no ASC");
 		$depositresult = $query->result();
		}
		if($filter_type=='monthly'){


		
			$query = $this->db_analytics->query("SELECT t1.month,
	
			t1.year,
	
			coalesce(SUM(t1.deposit+t2.deposit), 0) AS deposit, coalesce(SUM(t1.deposit_users+t2.deposit_users), 0) AS deposit_users,monthyear as created
	
			from
	
			(
	
			  SELECT DATE_FORMAT(a.Date,'%b') as month,
	
			MONTH(a.date) as monthid,
	
			  DATE_FORMAT(a.Date, '%m-%Y') as md,
	
			 DATE_FORMAT(a.Date, '%Y') as year,
			 DATE_FORMAT(a.Date, '%b-%Y') as monthyear,
	
			  '0' as  deposit,'0' as deposit_users
	
			  from (
	
				select curdate() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as Date
	
				from (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as a
	
				cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as b
	
				cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as c
	
			  ) a
	
			  
			  WHERE a.Date BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
			  group by md
	
			)t1
	
			left join
	
			(
	
			  SELECT DATE_FORMAT(created, '%b') AS month, sum(deposit) as deposit,sum(deposit_users) as deposit_users ,DATE_FORMAT(created, '%m-%Y') as md
	
			  FROM vi_analytics
	
			  WHERE created BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
	
			  GROUP BY md
	
			)t2
	
			on t2.md = t1.md
	
			group by t1.md
	
			order by t1.year ASC,t1.monthid ASC");
			 $depositresult = $query->result();

			// echo 1111; die;
			}
		
		if(!empty($depositresult)){
			$userdepositedata=array();
			$userdata=array();
			$monthyear=array();
			$grandTotalDepositAmt=0;
			$grandTotalUser=0;
			foreach($depositresult as $depositeuser){
			$userdepositedata[] = $depositeuser->deposit;
			$userdata[] = $depositeuser->deposit_users;
			$monthyear[] = $depositeuser->created;
			$grandTotalDepositAmt+=$depositeuser->deposit;
			$grandTotalUser+=$depositeuser->deposit_users;
			}
			$Return['data']= array('userdepositedata'=>$userdepositedata,'userdata'=>$userdata,'monthyear'=>$monthyear,'grandTotalDepositAmt'=>$grandTotalDepositAmt,'grandTotalUser'=>$grandTotalUser);	
		
		}else{
		
		$Return['data']= array('userdepositedata'=>array(),'userdata'=>array(),'monthyear'=>array(),'grandTotalDepositAmt'=>0,'grandTotalUser'=>0);
		}
	return $Return;

	}
	public function get_siterake($post){ 

		if (isset($post['startDate']))
		$startDate = date("Y-m-d", strtotime($post['startDate']));
		else
			$startDate = date('Y-m-d', strtotime('-10 day'));
		if (isset($post['endDate']))
			$endDate = date("Y-m-d", strtotime($post['endDate']));
		else
			$endDate = date("Y-m-d");
	 
			$filter_type = $post['filtertype']; //daily weekly monthly
			if($filter_type=='daily'){
				$this->db_analytics->select("site_rake,site_rake_users,created")
								   ->from(ANALYTICS)
								   ->where("created BETWEEN '".$startDate."' AND '".$endDate."'")
							   		->group_by("created");
				$result = $this->db_analytics->get()->result();				   
			}
			if($filter_type=='weekly'){
	
	
			
				$query = $this->db_analytics->query("SELECT t1.week_name,t1.monthyear, t1.week_no, concat_ws(' ',t1.week_no,t1.d1,t1.d2) as created, t1.year, coalesce(SUM(t1.site_rake+t2.site_rake), 0) AS site_rake,coalesce(SUM(t1.site_rake_users+t2.site_rake_users), 0) AS site_rake_users
		
				FROM
		
				(
		
				SELECT
		
				DATE_FORMAT(a.Date,'%W') AS week_name,
		
				DATE_FORMAT(a.Date,'%Y-%m-%d') AS monthyear,
		
				DATE_FORMAT(a.Date,'week %u') AS week_no,
				DATE_ADD(a.Date, INTERVAL(-WEEKDAY(a.Date)) DAY) as d1,
				a.Date as d2,
    			
		
				WEEK(a.date) AS weekid,
		
				DATE_FORMAT(a.Date, '%u-%Y') AS md,
		
				DATE_FORMAT(a.Date, '%Y') AS YEAR,
		
				'0' AS  site_rake, '0' AS  site_rake_users
		
				FROM (
		
					SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
		
					FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
		
					CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
		
					CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
		
				) a
		
				WHERE a.Date BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
		
				GROUP BY md
		
				)t1
		
				LEFT JOIN
		
				(
		
				SELECT DATE_FORMAT(created, '%u') AS WEEK, SUM(site_rake) AS site_rake ,DATE_FORMAT(created, '%u-%Y') AS md,SUM(site_rake_users) AS site_rake_users
		
				FROM vi_analytics
		
				WHERE created BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
		
				GROUP BY md
		
				)t2
		
				ON t2.md = t1.md
		
				GROUP BY t1.md
		
				ORDER BY t1.year ASC,t1.week_no ASC");
				 $result = $query->result();
				// echo $this->db_analytics->last_query(); die;
			}
			if($filter_type=='monthly'){
	
	
			
				$query = $this->db_analytics->query("SELECT t1.month,
		
				t1.year,
		
				coalesce(SUM(t1.site_rake+t2.site_rake), 0) AS site_rake,coalesce(SUM(t1.site_rake_users+t2.site_rake_users), 0) AS site_rake_users, monthyear as created
		
				from
		
				(
		
				  SELECT DATE_FORMAT(a.Date,'%b') as month,
		
				  MONTH(a.date) as monthid,
		
				  DATE_FORMAT(a.Date, '%m-%Y') as md,
		
				 DATE_FORMAT(a.Date, '%Y') as year,
				 DATE_FORMAT(a.Date, '%b-%Y') as monthyear,
		
				  '0' as  site_rake,'0' as site_rake_users
		
				  from (
		
					select curdate() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as Date
		
					from (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as a
		
					cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as b
		
					cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as c
		
				  ) a
		
				  WHERE a.Date BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
		
				  group by md
		
				)t1
		
				left join
		
				(
		
				  SELECT DATE_FORMAT(created, '%b') AS month, sum(site_rake) as site_rake,sum(site_rake_users) as site_rake_users ,DATE_FORMAT(created, '%m-%Y') as md
		
				  FROM vi_analytics
		
				  WHERE created BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
		
				  GROUP BY md
		
				)t2
		
				on t2.md = t1.md
		
				group by t1.md
		
				order by t1.year ASC,t1.monthid ASC");
				 $result = $query->result();
					//echo $this->db_analytics->last_query(); die;
				
				}
		if(!empty($result)){
        
			$userdata=array();
			$monthyear=array();
			$totalsiterakeamount =array();
			$grandTotalUser=0;
			$grandTotalSiterake=0;
			foreach($result as $row){
			  
			  $userdata[] = $row->site_rake_users;
			  $totalsiterakeamount[] = $row->site_rake;
			  $monthyear[] = $row->created;

			  
			  $grandTotalUser+=$row->site_rake_users;
			  $grandTotalSiterake+=$row->site_rake;	
			}
		   $Return['data']= array('userdata'=>$userdata,'monthyear'=>$monthyear,'totalsiterakeamount'=>$totalsiterakeamount,'grandTotalSiterake'=>round($grandTotalSiterake),'grandTotalUser'=>$grandTotalUser);	
		   
		 }else{
			$Return['data']= array('userdata'=>array(),'monthyear'=>array(),'totalsiterakeamount'=>array(),'grandTotalSiterake'=>0,'grandTotalUser'=>0);
		 }
	return $Return;

	}
	/**
	* Method : get_freepaidusers
	* Output : All free paid user data to plot graphs
	* Input : Filters of date range , type like weekly , monthly, yearly
	* Date : 7 June 2019
	**/
	public function get_freepaidusers($post){ 

		if (isset($post['startDate']))
		$startDate = date("Y-m-d", strtotime($post['startDate']));
		else
			$startDate = date('Y-m-d', strtotime('-10 day'));
		if (isset($post['endDate']))
			$endDate = date("Y-m-d", strtotime($post['endDate']));
		else
			$endDate = date("Y-m-d");
	 
			$filter_type = $post['filtertype']; //daily weekly monthly
			if($filter_type=='daily'){
				$this->db_analytics->select("free_users,paid_users,created")
								   ->from(ANALYTICS)
								   ->where("created BETWEEN '".$startDate."' AND '".$endDate."'")
							   		->group_by("created");
				$result = $this->db_analytics->get()->result();				   
			}
			if($filter_type=='weekly'){
	
	
			
				$query = $this->db_analytics->query("SELECT t1.week_name,t1.monthyear, t1.week_no, concat_ws(' ',t1.week_no,t1.d1,t1.d2) as created, t1.year, coalesce(SUM(t1.free_users+t2.free_users), 0) AS free_users,coalesce(SUM(t1.paid_users+t2.paid_users), 0) AS paid_users
		
				FROM
		
				(
		
				SELECT
		
				DATE_FORMAT(a.Date,'%W') AS week_name,
		
				DATE_FORMAT(a.Date,'%Y-%m-%d') AS monthyear,
		
				DATE_FORMAT(a.Date,'week %u') AS week_no,
				DATE_ADD(a.Date, INTERVAL(-WEEKDAY(a.Date)) DAY) as d1,
				a.Date as d2,
		
				WEEK(a.date) AS weekid,
		
				DATE_FORMAT(a.Date, '%u-%Y') AS md,
		
				DATE_FORMAT(a.Date, '%Y') AS YEAR,
		
				'0' AS  free_users, '0' AS  paid_users
		
				FROM (
		
					SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
		
					FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
		
					CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
		
					CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
		
				) a
		
				WHERE a.Date BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
		
				GROUP BY md
		
				)t1
		
				LEFT JOIN
		
				(
		
				SELECT DATE_FORMAT(created, '%u') AS WEEK, SUM(free_users) AS free_users ,DATE_FORMAT(created, '%u-%Y') AS md,SUM(paid_users) AS paid_users
		
				FROM vi_analytics
		
				WHERE created BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
		
				GROUP BY md
		
				)t2
		
				ON t2.md = t1.md
		
				GROUP BY t1.md
		
				ORDER BY t1.year ASC,t1.week_no ASC");
			 	$result = $query->result();
			}
			if($filter_type=='monthly'){
	
	
			
				$query = $this->db_analytics->query("SELECT t1.month,
		
				t1.year,
		
				coalesce(SUM(t1.free_users+t2.free_users), 0) AS free_users,coalesce(SUM(t1.paid_users+t2.paid_users), 0) AS paid_users, monthyear as created
		
				from
		
				(
		
				  SELECT DATE_FORMAT(a.Date,'%b') as month,
		
				MONTH(a.date) as monthid,
		
				  DATE_FORMAT(a.Date, '%m-%Y') as md,
		
				 DATE_FORMAT(a.Date, '%Y') as year,
				 DATE_FORMAT(a.Date, '%b-%Y') as monthyear,
		
				  '0' as  free_users,'0' as paid_users
		
				  from (
		
					select curdate() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as Date
		
					from (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as a
		
					cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as b
		
					cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as c
		
				  ) a
		
				  WHERE a.Date  BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
		
				  group by md
		
				)t1
		
				left join
		
				(
		
				  SELECT DATE_FORMAT(created, '%b') AS month, sum(free_users) as free_users,sum(paid_users) as paid_users ,DATE_FORMAT(created, '%m-%Y') as md
		
				  FROM vi_analytics
		
				  WHERE created BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
		
				  GROUP BY md
		
				)t2
		
				on t2.md = t1.md
		
				group by t1.md
		
				order by t1.year ASC,t1.monthid ASC");
				 $result = $query->result();
	
				
				}
		if(!empty($result)){
			
			$paid_users=array();
			$free_users=array();
			$monthyear=array();
			$grandTotalPaidUser=0;
			$grandTotalFreeUser=0;
			foreach($result as $row){

			   $monthyear[] = $row->created;
			   $paid_users[] = $row->paid_users;
			   $grandTotalPaidUser+=$row->paid_users;

			   $free_users[] = $row->free_users;
			   $grandTotalFreeUser+=$row->free_users;

			   }
			$Return['data']= array('paid_users'=>$paid_users,'free_users'=>$free_users,'monthyear'=>$monthyear,'grandTotalPaidUser'=>$grandTotalPaidUser,'grandTotalFreeUser'=>$grandTotalFreeUser);	
			
		}else{
			$Return['data']= array('paid_users'=>array(),'free_users'=>array(),'monthyear'=>array(),'grandTotalPaidUser'=>0,'grandTotalFreeUser'=>0);
		}
	return $Return;

	}
	public function get_referral($post){ 

		if (isset($post['startDate']))
		$startDate = date("Y-m-d", strtotime($post['startDate']));
		else
			$startDate = date('Y-m-d', strtotime('-10 day'));
		if (isset($post['endDate']))
			$endDate = date("Y-m-d", strtotime($post['endDate']));
		else
			$endDate = date("Y-m-d");
	 
			$filter_type = $post['filtertype']; //daily weekly monthly
			if($filter_type=='daily'){
				$this->db_analytics->select("referral_amount_distributed,no_of_referrals,created")
								   ->from(ANALYTICS)
								   ->where("created BETWEEN '".$startDate."' AND '".$endDate."'")
							   		->group_by("created");
				$result = $this->db_analytics->get()->result();				   
			}
			if($filter_type=='weekly'){
	
	
			
				$query = $this->db_analytics->query("SELECT t1.week_name,t1.monthyear, t1.week_no, concat_ws(' ',t1.week_no,t1.d1,t1.d2) as created, t1.year, coalesce(SUM(t1.referral_amount_distributed+t2.referral_amount_distributed), 0) AS referral_amount_distributed,coalesce(SUM(t1.no_of_referrals+t2.no_of_referrals), 0) AS no_of_referrals
		
				FROM
		
				(
		
				SELECT
		
				DATE_FORMAT(a.Date,'%W') AS week_name,
		
				DATE_FORMAT(a.Date,'%Y-%m-%d') AS monthyear,
		
				DATE_FORMAT(a.Date,'week %u') AS week_no,
				DATE_ADD(a.Date, INTERVAL(-WEEKDAY(a.Date)) DAY) as d1,
				a.Date as d2,
		
				WEEK(a.date) AS weekid,
		
				DATE_FORMAT(a.Date, '%u-%Y') AS md,
		
				DATE_FORMAT(a.Date, '%Y') AS YEAR,
		
				'0' AS  referral_amount_distributed, '0' AS  no_of_referrals
		
				FROM (
		
					SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
		
					FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
		
					CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
		
					CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
		
				) a
		
				 WHERE a.Date BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
		
				GROUP BY md
		
				)t1
		
				LEFT JOIN
		
				(
		
				SELECT DATE_FORMAT(created, '%u') AS WEEK, SUM(referral_amount_distributed) AS referral_amount_distributed ,DATE_FORMAT(created, '%u-%Y') AS md,SUM(no_of_referrals) AS no_of_referrals
		
				FROM vi_analytics
		
				WHERE created BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
		
				GROUP BY md
		
				)t2
		
				ON t2.md = t1.md
		
				GROUP BY t1.md
		
				ORDER BY t1.year ASC,t1.week_no ASC");
			 	$result = $query->result();
			}
			if($filter_type=='monthly'){
	
	
			
				$query = $this->db_analytics->query("SELECT t1.month,
		
				t1.year,
		
				coalesce(SUM(t1.referral_amount_distributed+t2.referral_amount_distributed), 0) AS referral_amount_distributed,coalesce(SUM(t1.no_of_referrals+t2.no_of_referrals), 0) AS no_of_referrals, monthyear as created
		
				from
		
				(
		
				  SELECT DATE_FORMAT(a.Date,'%b') as month,
		
				MONTH(a.date) as monthid,
		
				  DATE_FORMAT(a.Date, '%m-%Y') as md,
		
				 DATE_FORMAT(a.Date, '%Y') as year,
				 DATE_FORMAT(a.Date, '%b-%Y') as monthyear,
		
				  '0' AS  referral_amount_distributed, '0' AS  no_of_referrals
		
				  from (
		
					select curdate() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as Date
		
					from (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as a
		
					cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as b
		
					cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as c
		
				  ) a
		
				  WHERE a.Date BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
		
				  group by md
		
				)t1
		
				left join
		
				(
		
				  SELECT DATE_FORMAT(created, '%b') AS month, SUM(referral_amount_distributed) AS referral_amount_distributed,SUM(no_of_referrals) AS no_of_referrals ,DATE_FORMAT(created, '%m-%Y') as md
		
				  FROM vi_analytics
		
				  WHERE created BETWEEN '".$startDate."' AND '".$endDate."  23:59:59'
		
				  GROUP BY md
		
				)t2
		
				on t2.md = t1.md
		
				group by t1.md
		
				order by t1.year ASC,t1.monthid ASC");
				 $result = $query->result();
	
				
				}
		
		if(!empty($result)){
			$totalUserCount=array();
			$totalRefAmt=array();
			$monthyear=array();
			$grandTotalAmount=0;
			$grandTotalUser=0;
			//hack
			if($filter_type=='weekly'){  $result[0]->created = $startDate; }

			foreach($result as $referral){
			   
			   $totalUserCount[] = $referral->no_of_referrals;
			   $totalRefAmt[] = $referral->referral_amount_distributed;
			   $monthyear[] = $referral->created;
			   
			   $grandTotalAmount+=$referral->referral_amount_distributed;
			   $grandTotalUser+=$referral->no_of_referrals;
			}
			$Return['data'] = array('totalRefAmt'=>$totalRefAmt,'totalUserCount'=>$totalUserCount,'monthyear'=>$monthyear,'grandTotalAmount'=>$grandTotalAmount
			,'grandTotalUser'=>$grandTotalUser);
			
	   }else{
		$Return['data']= array('totalRefAmt'=>array(),'totalUserCount'=>array(),'monthyear'=>array(),'grandTotalAmount'=>0,'grandTotalUser'=>0);
	   }
	return $Return;

	}
	public function get_active_users($post)
	{
	    $endDate = date("Y-m-d");
	    $this->db_analytics->select("daily_visitors,daily_loggedInusers,monthly_visitors,monthly_loggedInusers")
		->from(ANALYTICS)
		->where("created BETWEEN '".$endDate."' AND '".$endDate."'");
		
		 $res = $this->db_analytics->get()->result_array();
		 //print_r( $res); die;
		 $result=$res[0];
		 $return = array();
		 $return['daily_visitors'] = ($result['daily_visitors'])?$result['daily_visitors']:0;
		 $return['daily_loggedInusers'] = ($result['daily_loggedInusers'])?$result['daily_loggedInusers']:0;
		 $return['monthly_visitors'] = ($result['monthly_visitors'])?$result['monthly_visitors']:0;
		 $return['monthly_loggedInusers'] = ($result['monthly_loggedInusers'])?$result['monthly_loggedInusers']:0;
	
		 
		return $return;

	}

	public function get_referral_rank()
	{
		$sort_field = 'rank_value';
		$sort_order = 'ASC';
		$limit = 50;
		$page = 0;
		$current_date = format_date(); 
		
		$from_date = date('Y-m-d',strtotime($current_date)).' 00:00:00';
		$to_date = date('Y-m-d',strtotime($current_date. ' + 7 days')).' 23:59:59';

		if(!empty(isset($post_data['to_date'])) && !empty(isset($post_data['from_date'])) && $post_data['to_date'] != '' && $post_data['from_date'] != '' )
		{
			$from_date = $post_data['from_date'];
			$to_date = $post_data['to_date'];
		}

		$post_data = $this->input->post();

		if(!empty($post_data['from_date']) && !empty($post_data['to_date'])){
			
			$from_date = $post_data['from_date'];
			$to_date = $post_data['to_date'];
		}

		if(isset($post_data['items_perpage']))
		{
			$limit = $post_data['items_perpage'];
		}

		if(isset($post_data['current_page']))
		{
			$page = $post_data['current_page']-1;
		}

		if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('COUNT(user_affiliate_history_id)','user_name','rank_value')))
		{
			$sort_field = $post_data['sort_field'];
		}

		if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $post_data['sort_order'];
		}

		$offset	= $limit * $page;
		
		switch($post_data['leaderboard']){
		case 'referral':
			$sum_arr = 'total_referral';
			if(isset($post_data['csv'])){
				$result =$this->db->select("U.user_unique_id, CONCAT_WS(' ',IFNULL(U.first_name,'-'),IFNULL(U.last_name,'-')) AS name,U.user_name,UAH.user_id,COUNT(UAH.user_affiliate_history_id) as total_referral,(RANK() OVER (ORDER BY COUNT(UAH.user_affiliate_history_id) DESC, MIN(created_date) ASC)) as rank_value,IFNULL(U.email,'—') as email,IFNULL(U.city,'—') as city,IFNULL(U.phone_no,'—') as phone");
			}else{
				$result =$this->db->select("U.user_unique_id, CONCAT_WS(' ',IFNULL(U.first_name,'-'),IFNULL(U.last_name,'-')) AS name,U.image,U.user_name,UAH.user_id,COUNT(UAH.user_affiliate_history_id) as total_referral,(RANK() OVER (ORDER BY COUNT(UAH.user_affiliate_history_id) DESC, MIN(created_date) ASC)) as rank_value,IFNULL(U.email,'—') as email,IFNULL(U.city,'—') as city,IFNULL(U.phone_no,'—') as phone");
			}

			$result = $this->db->from(USER_AFFILIATE_HISTORY.' UAH')
			->join(USER.' U','U.user_id=UAH.user_id','left')
			->where_in("UAH.affiliate_type",[1,19,20,21])
			->where('U.is_systemuser',0)
			// ->where("UAH.created_date between '{$from_date}' and '{$to_date}'",null,false)
			->where("DATE_FORMAT(UAH.created_date, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(UAH.created_date, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ")

			->group_by('UAH.user_id')
			->order_by($sort_field, $sort_order);
		break;
		case 'deposit':
			$sum_arr = 'total_deposit';
			if(isset($post_data['csv'])){
				$result =$this->db->select("U.user_unique_id, CONCAT_WS(' ',IFNULL(U.first_name,'-'),IFNULL(U.last_name,'-')) AS name,O.user_id,U.user_name,IFNULL(U.email,'—') as email,IFNULL(U.city,'—') as city,IFNULL(U.phone_no,'—') as phone,sum(O.real_amount) as total_deposit,RANK() OVER (ORDER BY sum(real_amount) DESC
				) as rank_value");
			}else{
				$result =$this->db->select("U.user_unique_id, CONCAT_WS(' ',IFNULL(U.first_name,'-'),IFNULL(U.last_name,'-')) AS name,U.image,O.user_id,U.user_name,IFNULL(U.email,'—') as email,IFNULL(U.city,'—') as city,IFNULL(U.phone_no,'—') as phone,sum(O.real_amount) as total_deposit,RANK() OVER (ORDER BY sum(real_amount) DESC
				) as rank_value");
			}
			$result = $this->db->from(ORDER.' O')
			->join(USER.' U','U.user_id=O.user_id','left')
			->where("O.source",7)
			->where("O.status",1)
			->where('U.is_systemuser',0)
			// ->where("O.date_added between '{$from_date}' and '{$to_date}'",null,false)
			->where("DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ")
			->group_by('O.user_id')
			->order_by($sort_field, $sort_order);
		break;
		case 'time_spent':
			$sum_arr = 'time_spent';
			if(isset($post_data['csv'])){
				$result =$this->db->select("U.user_unique_id, CONCAT_WS(' ',IFNULL(U.first_name,'-'),IFNULL(U.last_name,'-')) AS name,DAS.user_id,U.user_name,IFNULL(U.email,'—') as email,IFNULL(U.city,'—') as city,IFNULL(U.phone_no,'—') as phone,sum(DAS.total_seconds) as time_spent,RANK() OVER (ORDER BY sum(DAS.total_seconds) DESC
				) as rank_value");
			}
			else{
				$result =$this->db->select("U.user_unique_id, CONCAT_WS(' ',IFNULL(U.first_name,'-'),IFNULL(U.last_name,'-')) AS name,U.image,DAS.user_id,U.user_name,IFNULL(U.email,'—') as email,IFNULL(U.city,'—') as city,IFNULL(U.phone_no,'—') as phone,sum(DAS.total_seconds) as time_spent,RANK() OVER (ORDER BY sum(DAS.total_seconds) DESC
				) as rank_value");
			}
			$result =$this->db->from(DAILY_ACTIVE_SESSION.' DAS')
			->join(USER.' U','U.user_id=DAS.user_id','left')
			->where('U.is_systemuser',0)
			// ->where("DAS.session_date between '{$from_date}' and '{$to_date}'",null,false)
			->where("DATE_FORMAT(DAS.session_date, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(DAS.session_date, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ")
			->group_by('DAS.user_id')
			->order_by($sort_field, $sort_order);
		break;
		case 'withdrawal':
			$sum_arr = 'total_withdrawal';
			if(isset($post_data['csv'])){
			$result =$this->db->select("U.user_unique_id, CONCAT_WS(' ',IFNULL(U.first_name,'-'),IFNULL(U.last_name,'-')) AS name,U.image,O.user_id,U.user_name,IFNULL(U.email,'—') as email,IFNULL(U.city,'—') as city,IFNULL(U.phone_no,'—') as phone,sum(O.winning_amount) as total_withdrawal,RANK() OVER (ORDER BY sum(winning_amount) DESC
			) as rank_value");
		}else{
			$result =$this->db->select("U.user_unique_id, CONCAT_WS(' ',IFNULL(U.first_name,'-'),IFNULL(U.last_name,'-')) AS name,U.image,O.user_id,U.user_name,IFNULL(U.email,'—') as email,IFNULL(U.city,'—') as city,IFNULL(U.phone_no,'—') as phone,sum(O.winning_amount) as total_withdrawal,RANK() OVER (ORDER BY sum(winning_amount) DESC
			) as rank_value");
			}

			$result = $this->db->from(ORDER.' O')
			->join(USER.' U','U.user_id=O.user_id','left')
			->where("O.source",8)
			->where("O.type",1)
			->where("O.status",1)
			->where('U.is_systemuser',0)
			// ->where("O.date_added between '{$from_date}' and '{$to_date}'",null,false)
			->where("DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') >= '".$from_date."' and DATE_FORMAT(O.date_added, '%Y-%m-%d %H:%i:%s') <= '".$to_date."' ")
			->group_by('O.user_id')
			->order_by($sort_field, $sort_order);
		break;
		default:
		break;
		}
		// ->order_by('COUNT(user_affiliate_history_id)','DESC')
		if(isset($post_data['keyword']) && $post_data['keyword'] != "")
		{
		$this->db->like('LOWER( CONCAT(IFNULL(U.email,""),IFNULL(U.city,""),IFNULL(U.user_name,""),IFNULL(U.phone_no,"")))', strtolower($post_data['keyword']) );
		}
		$tempdb = clone $this->db; //to get rows for pagination
		if(!isset($post_data['csv']) || $post_data['csv'] == false)
        {
			$temp_q = $tempdb->get(); //->result_array();
			$total = $temp_q->num_rows();
			// echo $tempdb->last_query();die;
        }else{
			$total = 0;
		}


		if(!empty($result)){
			if(!isset($post_data['csv']) || $post_data['csv'] == false)
			{
			$result = $this->db->limit($limit,$offset);
			}
			$result = $result->get()->result_array();
			$grand_total = array_sum(array_column($result,$sum_arr));
			// echo $this->db->last_query();exit;
			if($post_data['leaderboard']=='time_spent')
			{
				foreach($result as $key=>$res){
				$t = $res['time_spent'];
				$result[$key]['time_spent'] = sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
				}
				$grand_total = sprintf('%02d:%02d:%02d', ($grand_total/3600),($grand_total/60%60), $grand_total%60);
			}
			return array('result'=>$result,'total'=>$total,$sum_arr=>$grand_total);
		}
		else{
			return array();
		}

		// echo $sql;die();
		// exit();
		}

		public function get_userdetails_by_userid($user_ids){
			$post_data = $this->input->post();
			if(!empty($user_ids)){

				$result = $this->db->select("user_unique_id,image,user_id,IFNULL(email,'--') AS email,IFNULL(phone_no,'--') as phone_no,IFNULL(city,'--') as city")
				->from(USER)
				->where_in('user_id', $user_ids)
				->where('status', '1')
				->where('is_systemuser',0);
				if(isset($post_data['keyword']) && $post_data['keyword'] != "")
				{
					$this->db->like('LOWER( CONCAT(IFNULL(email,""),IFNULL(city,""),IFNULL(user_name,""),IFNULL(phone_no,"")))', strtolower($post_data['keyword']) );
				}
				$result = $this->db->get()
			->result_array();
			//echo $this->db->last_query();exit;
			return (!empty($result)) ? $result : array();
			}else{
				return array();
			}
		}

		public function fetch_active_session_records($start, $end)
		{

		$this->load->library('mongo_db');
		// $this->load->model('auth/Auth_nosql_model','nosql_model');
		// $mongo_date_start = $this->nosql_model->normal_to_mongo_date($start);
		// $mongo_date_end = $this->nosql_model->normal_to_mongo_date($end);
		if(isset($_POST['user_id']) && $_POST['user_id']!=''){
			$user_id = 	array('user_id'=>(string)$_POST['user_id']);
			$result = $this->nosql_model->select_nosql('session_track',$user_id);
		}
		else{
			$this->mongo_db->where_gt('start_time', $start);
			$this->mongo_db->where_lt('end_time', $end);
			$result = $this->nosql_model->select_nosql('session_track');
		}


		return $result;
		}

		public function get_user_time_spent_rank(){
			$post_data = $this->input->post();
			// $pre_query = $this->db->get_compiled_select();
			$pq = 'SELECT `DAS`.`user_id`, sum(DAS.total_seconds) as time_spent, RANK() OVER (ORDER BY sum(DAS.total_seconds) DESC ) as rank_value FROM `vi_daily_active_session` `DAS` GROUP BY `DAS`.`user_id` ORDER BY `rank_value`';
			$sql ="select R.rank_value,R.time_spent,U.is_notification,U.user_id from ($pq) as R left join ".$this->db->dbprefix(USER)." as U on U.user_id = R.user_id WHERE R.user_id=".$post_data['user_id'];
			// echo $sql;exit;
			$query = $this->db->query($sql);
			$result = $query->result_array();
			return $result[0];
			}

		public function get_install_uninstall_date(){
			$sql ="select (SELECT count(user_id) FROM ".$this->db->dbprefix(USER)." WHERE ios_install_date IS NOT NULL or  android_install_date IS NOT NULL) as install_count, (select count(user_id) from ".$this->db->dbprefix(USER)." where uninstall_date IS NOT NULL) AS uninstall_count";
			$query = $this->db->query($sql);
			$result = $query->row_array();
			return $result;
		}
	
}
