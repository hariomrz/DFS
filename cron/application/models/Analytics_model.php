<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Analytics_model extends MY_Model {
    
    public $db_user ;
    public $db_fantasy ;
    public $db_analytics ;
    public $testingNode =FALSE;

    public function __construct() 
    {
       	parent::__construct();
		$this->db_user		= $this->load->database('db_user', TRUE);
		$this->db_fantasy	= $this->load->database('db_fantasy', TRUE);
		$this->db_analytics		= $this->load->database('db_analytics', TRUE);
    }

    /**
     * [get_contest_list description]
     * Summary :-
     * @param  [type] $sports_id [description]
     * @return [type]            [description]
     */
    public function get_analytics()
    {
        $this->db = $this->db_analytics; //db_analytics db instance
        $this->db->select('*')
                ->from(ANALYTICS . " A");
        $sql = $this->db->get();
        $res = $sql->result_array();
        return $res;
    }
    public function insert($insertArray){
        $created = $insertArray['created'];
        $this->db_analytics->select('created');
        $this->db_analytics->from(ANALYTICS . " A");
        $this->db_analytics->where("created BETWEEN '".$insertArray['created']."' AND '".$insertArray['created']."'");
        $sql = $this->db_analytics->get();
		$res = $sql->result_array();
		$this->db_analytics->reset_query();
        
       //echo $this->db_analytics->last_query(); die;
        if(!empty($res)){
        	unset($insertArray['created']); 
            $this->db_analytics->set($insertArray, FALSE);
            $this->db_analytics->where("created BETWEEN '".$created."' AND '".$created."'");
			$this->db_analytics->update(ANALYTICS); 
			$this->db_analytics->reset_query();
            
        } 
        else{

			$this->db_analytics->insert(ANALYTICS,$insertArray);
			$this->db_analytics->reset_query();
            
        }
       
        return true;

    }

    /**
	* Method : get_summary 
	* Output : All Counts of visitors, signup,Wallet balance and users of diposited users, firsttime deposite users count and amount 
	* Input : Filters of date range , type like weekly , monthly, yearly
	* Date : 30 May 2019
	**/
	public function get_summary($post)
	{
		if (isset($post['StartDate']))
            $startDate = date("Y-m-d", strtotime($post['StartDate']));
        else
            $startDate = date('Y-m-d', strtotime('-1 Month'));
        if (isset($post['EndDate']))
            $endDate = date("Y-m-d", strtotime($post['EndDate']));
        else
            $endDate = date("Y-m-d");
        
            $Return['TotalUsers']=0;
            $Return['TotalBalance']=0;
            $Return['TotalBalance']=0;
            $Return['FirstTimeUser']=0;
            
		 $this->db_user->select("(select COUNT(DISTINCT(U.user_id)) AS clicks 
                                        FROM  vi_".USER." U  WHERE   U.added_date BETWEEN '".$startDate."' AND '".$endDate."  23:59:59' ) as TotalUsers",false);
		 $this->db_user->select("(select sum(U.balance) AS balance 
                                        FROM  vi_".USER." U  WHERE   U.added_date BETWEEN '".$startDate."' AND '".$endDate."  23:59:59' ) as TotalBalance",false);

         $this->db_user->select("(select COUNT(DISTINCT(U.user_id)) AS TotalBalanceUserCount  
                                        FROM  vi_".USER." U  WHERE   U.added_date BETWEEN '".$startDate."' AND '".$endDate."  23:59:59' ) as TotalBalanceUserCount",false);
		 $query = $this->db_user->get();
		 $result = $query->result();

		 $param =array('startDate'=>$startDate,'endDate'=>$endDate.' 23:59:59');
         if(!empty($result)){
         	$Return['TotalUsers']= $result['0']->TotalUsers;	
         	$Return['TotalBalance']= $result['0']->TotalBalance;	
			$Return['TotalBalanceUserCount']= $result['0']->TotalBalanceUserCount;
			$Return['FirstTimeUser']= $this->getFirstTimeUserAmt($param);
         }else{
			$Return['TotalUsers']=0;
			$Return['TotalBalance']=0;
			$Return['TotalBalance']=0;
			$Return['FirstTimeUser']= $this->getFirstTimeUserAmt($param);
         }
         
        
		/*$this->db_user->select("player_unique_id")
		->from(FAVOURITE_PLAYER)
		->where("user_id",$this->user_id);

		if(!empty($player_uid) && is_array($player_uid))
		{
			$this->db_user->where_in("player_unique_id",$player_uid);
		}

		return $result = $this->db_user->get()->result_array();*/
		return $Return;

	}
	function getFirstTimeUserAmt($param){
		$endDate   = $param['endDate'];
		$startDate = $param['startDate'];
		$return    =  array('totalDeposit'=>0,'totalUser'=>0);
		$query = $this->db_user->query('SELECT 
		sum(real_amount) as totalDeposit,
		SUM(total_count) as totalUser 
		from ( 
			SELECT order_id,real_amount,count(order_id) as total_count FROM `vi_order` 
			WHERE  source=7 and status=1 
			and `date_added` BETWEEN "'.$startDate.'" AND "'.$endDate.'"
				group by user_id HAVING total_count = 1 and real_amount>0
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
	* Method : get deposit
	* Output : All timelines data to plot graphs
	* Input : Filters of date range , type like weekly , monthly, yearly
	* Date : 5 June 2019
	**/
	public function get_deposit($post)
	{
		if (isset($post['StartDate']))
            $startDate = date("Y-m-d", strtotime($post['StartDate']));
        else
            $startDate = date('Y-m-d', strtotime('-1 month'));
        if (isset($post['EndDate']))
            $endDate = date("Y-m-d", strtotime($post['EndDate']));
        else
            $endDate = date("Y-m-d");
		 
			// $query =	$this->db_user->query('SELECT  user_id,count(user_id) as totaluser,
			// SUM(real_amount) as total_deposit
			// FROM vi_order
			// WHERE   date_added BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59"  GROUP BY user_id '); 

			$query = $this->db_user->query('SELECT t1.monthyear, COALESCE(SUM(t1.usercount+t2.usercount), 0) AS usercount, COALESCE(SUM(t1.real_amount+t2.real_amount), 0) AS total_deposit

			FROM

			(

			  SELECT DATE_FORMAT(a.Date,"%Y-%m-%d") AS monthyear,"0" AS  usercount, "0" as real_amount

			  FROM (

				SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE

				FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a

				CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b

				CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c

			  ) a

			  WHERE a.Date BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59"

			  GROUP BY monthyear

			)t1
			
			LEFT JOIN

			(

			  SELECT  DATE_FORMAT(date_added, "%Y-%m-%d") AS monthyear,COUNT(distinct(user_id)) AS usercount ,SUM(real_amount) as real_amount

			  FROM vi_order

			  WHERE date_added BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59"  AND status=1 and source in (7)  

			  GROUP BY monthyear

			)t2
			
			ON t2.monthyear = t1.monthyear
			GROUP BY t1.monthyear
			ORDER BY t1.monthyear ASC');
		 $result = $query->result();
		 //echo $this->db_user->last_query(); die;
         if(!empty($result)){
			 $userdepositedata=array();
			 $userdata=array();
			 $monthyear=array();
			 $grandTotalDepositAmt=0;
			 $grandTotalUser=0;
			 foreach($result as $depositeuser){
				$userdepositedata[] = $depositeuser->total_deposit;
				$userdata[] = $depositeuser->usercount;
				$monthyear[] = $depositeuser->monthyear;
				$grandTotalDepositAmt+=$depositeuser->total_deposit;
			 	$grandTotalUser+=$depositeuser->usercount;
			 }
         	$Return['data']= array('userdepositedata'=>$userdepositedata,'userdata'=>$userdata,'monthyear'=>$monthyear,'grandTotalDepositAmt'=>$grandTotalDepositAmt,'grandTotalUser'=>$grandTotalUser);	
         	
         }else{
			$Return['data']= array();	
         }
		
		 return $Return;

	}

	/******
     * Method : get site rake for contests 
     * * Param : date param
     * Output : user count and siterake date wise 
     *  */
    function get_siterake($post){

		if (isset($post['StartDate']))
				$startDate = date("Y-m-d", strtotime($post['StartDate']));
			else
				$startDate = date('Y-m-d', strtotime('-1 month'));
			if (isset($post['EndDate']))
				$endDate = date("Y-m-d", strtotime($post['EndDate']));
			else
				$endDate = date("Y-m-d");
		 
		$query = $this->db_fantasy->query('SELECT t1.monthyear, COALESCE(SUM(t1.usercount+t2.usercount), 0) AS usercount, COALESCE(SUM(t1.totalsiterakeamount+t2.totalsiterakeamount), 0) AS totalsiterakeamount
	
		  FROM
	
		  (
	
			SELECT DATE_FORMAT(a.Date,"%Y-%m-%d") AS monthyear, "0" AS  usercount, "0" as totalsiterakeamount
	
			FROM (
	
			SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
	
			FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
	
			CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
	
			CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
	
			) a
	
			WHERE a.Date BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59" 
	
			GROUP BY monthyear
	
		  )t1
		  
		  LEFT JOIN
	
		  (
	
			SELECT  DATE_FORMAT(season_scheduled_date, "%Y-%m-%d") AS monthyear,sum(total_user_joined) as usercount, site_rake,entry_fee,total_user_joined , sum((site_rake/100)*(entry_fee * total_user_joined)) as totalsiterakeamount 
			FROM vi_contest
			WHERE site_rake!=0 and status in(2,3) and season_scheduled_date BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59" 
			GROUP BY monthyear
	
		  )t2
		  
		  ON t2.monthyear = t1.monthyear
		  GROUP BY t1.monthyear
	      ORDER BY t1.monthyear ASC');
		 $result = $query->result();
		 //echo $this->db->last_query(); die;
			 if(!empty($result)){
		  
				$userdata=array();
				$monthyear=array();
				$totalsiterakeamount =array();
				$grandTotalUser=0;
				$grandTotalSiterake=0;
				foreach($result as $depositeuser){
				  
				  $userdata[] = $depositeuser->usercount;
				  $monthyear[] = $depositeuser->monthyear;
				  $totalsiterakeamount[] = $depositeuser->totalsiterakeamount;
				  
				  $grandTotalSiterake+=$depositeuser->totalsiterakeamount;
				  $grandTotalUser+=$depositeuser->usercount;
				}
			   $Return['data']= array('userdata'=>$userdata,'monthyear'=>$monthyear,'totalsiterakeamount'=>$totalsiterakeamount,'grandTotalSiterake'=>round($grandTotalSiterake),'grandTotalUser'=>$grandTotalUser);	
			   
			 }else{
				$Return['data']= array();	
			 }
		
		 return $Return;
	  }


	/**
	* Method : get_freepaidusers
	* Output : All free paid user data to plot graphs
	* Input : Filters of date range , type like weekly , monthly, yearly
	* Date : 7 June 2019
	**/
	public function get_freepaidusers($post)
	{
	    if (isset($post['StartDate']))
            $startDate = date("Y-m-d", strtotime($post['StartDate']));
        else
            $startDate = date('Y-m-d', strtotime('-1 month'));
        if (isset($post['EndDate']))
            $endDate = date("Y-m-d", strtotime($post['EndDate']));
        else
            $endDate = date("Y-m-d");
		
		$query = $this->db_user->query('SELECT t1.monthyear, COALESCE(SUM(t1.usercount+t2.usercount), 0) AS usercount

			FROM

			(

			  SELECT DATE_FORMAT(a.Date,"%Y-%m-%d") AS monthyear,0 as usercount
			  FROM (

				SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE

				FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a

				CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b

				CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c

			  ) a

			  WHERE a.Date BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59"

			  GROUP BY monthyear

			)t1
			
			LEFT JOIN

			(

			  SELECT  DATE_FORMAT(date_added, "%Y-%m-%d") AS monthyear,COUNT(user_id) AS usercount

			  FROM `vi_order`

			  WHERE `date_added` BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59"  and `type`=1 and `source`=1 

			  GROUP BY `monthyear`

			)t2
			
			ON t2.monthyear = t1.monthyear
			GROUP BY t1.monthyear
			ORDER BY t1.monthyear ASC');
		 $result = $query->result();
		 //echo $this->db_user->last_query(); die;
         if(!empty($result)){
			
			 $userdata=array();
			 $monthyear=array();
			 $grandTotalUser=0;
			 foreach($result as $depositeuser){
				
				$userdata[] = $depositeuser->usercount;
				$monthyear[] = $depositeuser->monthyear;
				$grandTotalUser+=$depositeuser->usercount;
				}
         	$Return['data']= array('userdata'=>$userdata,'monthyear'=>$monthyear,'grandTotalUser'=>$grandTotalUser);	
         	
         }else{
			$Return['data']= array();	
         }
		
		 return $Return;

	}
	/******
     * Method : get_contest_usercount
     * * Param : date param
     * Output : contest free user count  date wise 
     *  */
    function get_contest_usercount($post){

		if (isset($post['StartDate']))
				$startDate = date("Y-m-d", strtotime($post['StartDate']));
			else
				$startDate = date('Y-m-d', strtotime('-1 month'));
			if (isset($post['EndDate']))
				$endDate = date("Y-m-d", strtotime($post['EndDate']));
			else
				$endDate = date("Y-m-d");
		 
		  
		  $query = $this->db_fantasy->query('SELECT t1.monthyear, COALESCE(SUM(t1.usercount+t2.usercount), 0) AS usercount
	
		  FROM
	
		  (
	
			SELECT DATE_FORMAT(a.Date,"%Y-%m-%d") AS monthyear, "0" AS  usercount
	
			FROM (
	
			SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
	
			FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
	
			CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
	
			CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
	
			) a
	
			WHERE a.Date BETWEEN "'.$startDate.'" AND "'.$endDate.' 23:59:59"
			GROUP BY monthyear
		  )t1
		  
		  LEFT JOIN
	
		  (
	
			SELECT  DATE_FORMAT(season_scheduled_date, "%Y-%m-%d") AS monthyear,sum(total_user_joined) as usercount 
				  FROM vi_contest
	 
			WHERE season_scheduled_date BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59"  and entry_fee=0
	
			GROUP BY monthyear
	
		  )t2
		  
		  ON t2.monthyear = t1.monthyear
		  
	
		  GROUP BY t1.monthyear
	
		  ORDER BY t1.monthyear ASC');
		 $result = $query->result();
		 //echo $this->db->last_query(); die;
			 if(!empty($result)){
		  
		   $userdata=array();
		   $monthyear=array();
		   $totalsiterakeamount =array();
		   $grandTotalFreeUser=0;
		   foreach($result as $depositeuser){
			
			$userdata[] = $depositeuser->usercount;
			$monthyear[] = $depositeuser->monthyear;
			$grandTotalFreeUser+= $depositeuser->usercount;
		   }
			   $Return['data']= array('userdata'=>$userdata,'monthyear'=>$monthyear,'grandTotalFreeUser'=>$grandTotalFreeUser);	
			   
			 }else{
		  $Return['data']= array();	
			 }
		
		 return $Return;
	  }
	/**
	* Method : get_freepaidusers
	* Output : All free paid user data to plot graphs
	* Input : Filters of date range , type like weekly , monthly, yearly
	* Date : 7 June 2019
	**/
	public function get_devices($post)
	{
		if (isset($post['StartDate']))
            $startDate = date("Y-m-d", strtotime($post['StartDate']));
        else
            $startDate = date('Y-m-d', strtotime('-1 month'));
        if (isset($post['EndDate']))
            $endDate = date("Y-m-d", strtotime($post['EndDate']));
        else
            $endDate = date("Y-m-d");
		
		$query = $this->db_user->query('SELECT count(user_id) as devicetypecount ,device_type FROM `vi_user` 
			WHERE `added_date` BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59" GROUP BY device_type');

		 $result = $query->result();
		// echo $this->db_user->last_query(); die;
		
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
		if (isset($post['StartDate']))
            $startDate = date("Y-m-d", strtotime($post['StartDate']));
        else
            $startDate = date('Y-m-d', strtotime('-1 month'));
        if (isset($post['EndDate']))
            $endDate = date("Y-m-d", strtotime($post['EndDate']));
        else
            $endDate = date("Y-m-d");
		
		$query = $this->db_user->query('SELECT vi_user.added_date,vi_user.first_name,vi_user.last_name, vi_user.user_id,SUM(CASE WHEN source=7 THEN real_amount ELSE 0 END) as totalDeposit,SUM(CASE WHEN source=1 THEN real_amount ELSE 0 END) as totalWinning FROM `vi_order` LEFT JOIN vi_user ON vi_user.user_id=vi_order.user_id WHERE `added_date` BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59"  and type=0 GROUP BY user_id ORDER BY totalDeposit DESC LIMIT 0,5');

		$depositWinningresult = $query->result();
		// echo $this->db_user->last_query(); die;
	
		if(!empty($depositWinningresult)){
		$leaderboardData=array();
		foreach($depositWinningresult as $depositWinningresultRow){
			
			$leaderboardData[] = array('name'=>$depositWinningresultRow->first_name.' '.$depositWinningresultRow->last_name,'totalDeposit'=>$depositWinningresultRow->totalDeposit,'totalWinning'=>$depositWinningresultRow->totalWinning,'added_date'=>$depositWinningresultRow->added_date);

		}
			
		$Return['data']= $leaderboardData;	
		
		}else{
		$Return['data']= array();	
		}
		
		 return $Return;

	}
	/**
	* Method : get_referral
	* Output : Referral amount distributed
	* Input : Filters of date range , type like weekly , monthly, yearly
	* Date : 14 June 2019
	**/
	public function get_referral($post)
	{
		if (isset($post['StartDate']))
            $startDate = date("Y-m-d", strtotime($post['StartDate']));
        else
            $startDate = date('Y-m-d', strtotime('-1 month'));
        if (isset($post['EndDate']))
            $endDate = date("Y-m-d", strtotime($post['EndDate']));
        else
            $endDate = date("Y-m-d");
		 
			
			$query = $this->db_user->query('SELECT t1.monthyear, COALESCE(SUM(t1.totalRefAmt+t2.totalRefAmt), 0) AS totalRefAmt,  COALESCE(SUM(t1.totalUserCount+t2.totalUserCount), 0) AS totalUserCount

			FROM

			(

			  SELECT DATE_FORMAT(a.Date,"%Y-%m-%d") AS monthyear,0 as totalRefAmt,0 as totalUserCount 
			
			  FROM (

				SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE

				FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a

				CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b

				CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c

			  ) a

			  WHERE a.Date BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59"

			  GROUP BY monthyear

			)t1
			
			LEFT JOIN

			(

			  SELECT  DATE_FORMAT(created_date, "%Y-%m-%d") AS monthyear,
			  SUM(user_real_cash) as user_real_cash,
			  SUM(friend_real_cash) as friend_real_cash,
			  (SUM(friend_real_cash) + SUM(user_real_cash)) as totalRefAmt,
              count(user_affiliate_history_id) as totalUserCount

			  FROM `vi_user_affiliate_history`

			  WHERE (user_real_cash !=0 or friend_real_cash !=0)   and `created_date` BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59"

			  GROUP BY `monthyear`

			)t2
			
			ON t2.monthyear = t1.monthyear
			GROUP BY t1.monthyear
			ORDER BY t1.monthyear ASC');

		$referralresult = $query->result();
		 //echo $this->db_user->last_query(); die;
	
		if(!empty($referralresult)){
			 $totalUserCount=array();
			 $totalRefAmt=array();
			 $monthyear=array();
			 $grandTotalAmount=0;
			 $grandTotalUser=0;
			 foreach($referralresult as $referral){
				
				$totalUserCount[] = $referral->totalUserCount;
				$totalRefAmt[] = $referral->totalRefAmt;
				$monthyear[] = $referral->monthyear;
				$grandTotalAmount+=$referral->totalRefAmt;
				$grandTotalUser+=$referral->totalUserCount;
			 }
			 $Return['data'] = array('totalRefAmt'=>$totalRefAmt,'totalUserCount'=>$totalUserCount,'monthyear'=>$monthyear,'grandTotalAmount'=>$grandTotalAmount
			 ,'grandTotalUser'=>$grandTotalUser);
		
		}else{
		$Return['data']= array();	
		}
		
		 return $Return;

	}

	/**
	 * metho dot get active users and criteria is that who have joined any contest in last 7 days will be considered as active user.
	 */
	public function get_active_users_new($post){
		// print_r($this->app_config);
		$source_list = array();
        if($this->app_config['allow_dfs']['key_value'] == 1){
            $source_list[] = 1;
        }
		if($this->app_config['allow_pickem']['key_value'] == 1){
            $source_list[] = 250;
        }
		if($this->app_config['allow_prediction_system']['key_value'] == 1){
            $source_list[] = 40;
        }
		if($this->app_config['allow_fixed_open_predictor']['key_value'] == 1){
            $source_list[] = 220;
        }

		$source_list = implode(",",$source_list);
		// if(!empty($source_list)){
        //     where_in("o.source",$source_list);
        // }

		if (isset($post['StartDate']))
            $startDate = date("Y-m-d", strtotime($post['StartDate']));
        else
            $startDate = date("Y-m-d");
        if (isset($post['EndDate']))
            $endDate = date("Y-m-d", strtotime($post['EndDate']));
        else
			$endDate = date("Y-m-d");
			$seven_day_before = date('Y-m-d', strtotime('-7 day'));
		//  $query = $this->db_fantasy->query('SELECT DISTINCT LM.user_id as active_users
		//  FROM vi_lineup_master_contest LMC INNER JOIN vi_lineup_master LM on LM.lineup_master_id=LMC.lineup_master_id 
		//  WHERE LM.is_systemuser=0 and LMC.created_date BETWEEN "'.$seven_day_before.'" AND "'.$endDate.'  23:59:59"');

		$query = $this->db_user->query('SELECT  DISTINCT O.user_id  active_users FROM vi_order O INNER JOIN vi_user U
		ON U.user_id = O.user_id
		WHERE U.is_systemuser=0 AND O.status=1 AND O.source in('.$source_list.') AND date_added BETWEEN "'.$seven_day_before.'" AND "'.$endDate.'  23:59:59"
		GROUP BY O.user_id');

		 //select DISTINCE O.user_id as active_users from vi_order WHERE  
			
		/*$query = $this->db_fantasy->query('SELECT t1.monthyear, COALESCE(SUM(t1.active_users), 0) AS active_users FROM (SELECT DATE_FORMAT(a.Date,"%Y-%m-%d") AS monthyear,0 as active_users FROM ( SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c ) a WHERE a.Date BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59" GROUP BY monthyear )t1 LEFT JOIN ( SELECT DATE_FORMAT(created_date, "%Y-%m-%d") AS monthyear,count(LM.user_id) as active_users  FROM vi_lineup_master_contest LMC INNER JOIN vi_lineup_master LM on LM.lineup_master_id=LMC.lineup_master_id WHERE LM.is_systemuser=0 and LMC.created_date  BETWEEN "'.$seven_day_before.'" AND "'.$endDate.'  23:59:59" GROUP BY `monthyear`)t2 ON t2.monthyear = t1.monthyear GROUP BY t1.monthyear ORDER BY t1.monthyear ASC');*/

		$activeresult = $query->result();
		if(!empty($activeresult)){
			$totalActiveUsers = array_column($activeresult,'active_users');
			$Return['data'] = array('totalActiveUsers'=>$totalActiveUsers);
		}else{
		$Return['data']= array();	
		}
		
		 return $Return;
	}

	/**
	 * method to get count of passive users
	 */

	 public function get_passive_users($post){

		$source_list = array();
        if($this->app_config['allow_dfs']['key_value'] == 1){
            $source_list[] = 1;
        }
		if($this->app_config['allow_pickem']['key_value'] == 1){
            $source_list[] = 250;
        }
		if($this->app_config['allow_prediction_system']['key_value'] == 1){
            $source_list[] = 40;
        }
		if($this->app_config['allow_fixed_open_predictor']['key_value'] == 1){
            $source_list[] = 220;
        }

		$source_list = implode(",",$source_list);

		
		if (isset($post['StartDate']))
            $startDate = date("Y-m-d", strtotime($post['StartDate']));
        else
            $startDate = date("Y-m-d");
        if (isset($post['EndDate']))
            $endDate = date("Y-m-d", strtotime($post['EndDate']));
        else
			$endDate = date("Y-m-d");
			
			$before_end = date('Y-m-d', strtotime("-1 days",strtotime($startDate)));
			$one_month_before = date('Y-m-d', strtotime('-1 month',strtotime($before_end)));

			$before_query = $this->db_user->query('SELECT DISTINCT O.user_id
			FROM vi_order O INNER JOIN vi_user U ON U.user_id = O.user_id
			WHERE U.is_systemuser=0 AND O.source in('.$source_list.') AND O.date_added BETWEEN "'.$one_month_before.'" AND "'.$before_end.'  23:59:59"
			GROUP by O.user_id
			having COUNT(O.user_id)>10');
			//echo $this->db_user->last_query(); die;

			$before_users = array_column($before_query->result(),'user_id');
			
			$after_query = $this->db_user->query('SELECT DISTINCT O.user_id
			FROM vi_order O INNER JOIN vi_user U ON U.user_id = O.user_id
			WHERE U.is_systemuser=0 AND O.source in('.$source_list.') AND O.date_added BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59"
			GROUP by O.user_id');
			
			$after_users = array_column($after_query->result(),'user_id');
			//echo $this->db_user->last_query();exit;
			$final_users = array();
			if(!empty($after_users)){
				foreach($before_users as $before){
					if(!in_array($before,$after_users)){
						array_push($final_users,$before);
					}
				}
			}else{
					$final_users = $before_users;
				}
				


		//echo $this->db_fantasy->last_query(); die;
		$totalPassiveUserCount=0;
		if(!empty($final_users)){
			$totalPassiveUserCount = array_unique($final_users);
			$Return['data'] = array('totalPassiveUsers'=>$totalPassiveUserCount);
		}else{
		$Return['data']= array('totalPassiveUsers'=>$totalPassiveUserCount);	
		}
		 return $Return;
	 }
  
}
