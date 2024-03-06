<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// use PhpAmqpLib\Connection\AMQPStreamConnection;
// use PhpAmqpLib\Message\AMQPMessage;

class Analytics_model extends MY_Model {
    
    public $db_user;
    public function __construct() 
    {
      parent::__construct();
      $this->db_user = $this->load->database('user_db', TRUE);
      $this->db_livefantasy = $this->load->database('livefantasy_db', TRUE);
    }

   

    public function insert($insertArray){
        $created = $insertArray['created'];
        $this->db_livefantasy->select('created');
        $this->db_livefantasy->from(ANALYTICS . " A");
        $this->db_livefantasy->where("created BETWEEN '".$insertArray['created']."' AND '".$insertArray['created']."'");
        $sql = $this->db_livefantasy->get();
		$res = $sql->result_array();
		$this->db_livefantasy->reset_query();
        
       //echo $this->db_livefantasy->last_query(); die;
        if(!empty($res)){
        	unset($insertArray['created']); 
            $this->db_livefantasy->set($insertArray, FALSE);
            $this->db_livefantasy->where("created BETWEEN '".$created."' AND '".$created."'");
			$this->db_livefantasy->update(ANALYTICS); 
			$this->db_livefantasy->reset_query();
            
        } 
        else{

			$this->db_livefantasy->insert(ANALYTICS,$insertArray);
			$this->db_livefantasy->reset_query();
            
        }
       
        return true;

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
		 
		/*$query = $this->db_livefantasy->query('SELECT t1.monthyear, COALESCE(SUM(t1.usercount+t2.usercount), 0) AS usercount, COALESCE(SUM(t1.totalsiterakeamount+t2.totalsiterakeamount), 0) AS totalsiterakeamount
	
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
	      ORDER BY t1.monthyear ASC');*/
		  $query = $this->db_livefantasy->select('DATE_FORMAT(season_scheduled_date, "%Y-%m-%d") AS monthyear,sum(IFNULL(total_user_joined,0)) as usercount, site_rake,entry_fee,total_user_joined , sum((site_rake/100)*(entry_fee * total_user_joined)) as totalsiterakeamount') 
		  ->from(CONTEST)
		  ->where('site_rake!=',0)
		  ->where_in('status',[2,3])
		  ->where("DATE_FORMAT(season_scheduled_date,'%Y-%m-%d') >= '".format_date($startDate,'Y-m-d')."' and DATE_FORMAT(season_scheduled_date, '%Y-%m-%d') <= '".format_date($endDate,'Y-m-d')." 23:59:59' ")
		  ->group_by('monthyear')
		  ->get();
		 $result = $query->result();
		//  echo $this->db_livefantasy->last_query(); die;
			 if(!empty($result)){
		  
				// $userdata=array();
				// $monthyear=array();
				// $totalsiterakeamount =array();
				$grandTotalUser=0;
				$grandTotalSiterake=0;
				foreach($result as $depositeuser){
				  
				//   $userdata[] = $depositeuser->usercount;
				//   $monthyear[] = $depositeuser->monthyear;
				//   $totalsiterakeamount[] = $depositeuser->totalsiterakeamount;
				  
				  $grandTotalSiterake+=$depositeuser->totalsiterakeamount;
				  $grandTotalUser+=$depositeuser->usercount;
				}
			   $Return['data']= array('grandTotalSiterake'=>round($grandTotalSiterake),'grandTotalUser'=>$grandTotalUser);	
			   
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
		
		/*$query = $this->db_user->query('SELECT t1.monthyear, COALESCE(SUM(t1.usercount+t2.usercount), 0) AS usercount

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

			  WHERE `date_added` BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59"  and `type`=1 and `source`=500 

			  GROUP BY `monthyear`

			)t2
			
			ON t2.monthyear = t1.monthyear
			GROUP BY t1.monthyear
			ORDER BY t1.monthyear ASC');*/
			$query = $this->db_user->select('DATE_FORMAT(date_added, "%Y-%m-%d") AS monthyear,COUNT(user_id) AS usercount')
			->from(ORDER)
			->where('type',1)
			->where_in('source',500)
			->where("DATE_FORMAT(date_added,'%Y-%m-%d') >= '".format_date($startDate,'Y-m-d')."' and DATE_FORMAT(date_added, '%Y-%m-%d') <= '".format_date($endDate,'Y-m-d')." 23:59:59' ")
			->group_by('monthyear')
			->get();
		 $result = $query->result();
		//  echo $this->db_user->last_query(); die;
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
		 
		  
		  /*$query = $this->db_livefantasy->query('SELECT t1.monthyear, COALESCE(SUM(t1.usercount+t2.usercount), 0) AS usercount
	
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
	 
			WHERE season_scheduled_date BETWEEN "'.$startDate.'" AND "'.$endDate.'  23:59:59"  
			and entry_fee=0
	
			GROUP BY monthyear
	
		  )t2
		  
		  ON t2.monthyear = t1.monthyear
		  
	
		  GROUP BY t1.monthyear
	
		  ORDER BY t1.monthyear ASC');*/
		  $query = $this->db_livefantasy->select('DATE_FORMAT(season_scheduled_date, "%Y-%m-%d") AS monthyear,sum(total_user_joined) as usercount, site_rake,entry_fee,total_user_joined , sum((site_rake/100)*(entry_fee * total_user_joined)) as totalsiterakeamount') 
		  ->from(CONTEST)
		  ->where('entry_fee',0)
		  ->where("DATE_FORMAT(season_scheduled_date,'%Y-%m-%d') >= '".format_date($startDate,'Y-m-d')."' and DATE_FORMAT(season_scheduled_date, '%Y-%m-%d') <= '".format_date($endDate,'Y-m-d')." 23:59:59' ")
		  ->group_by('monthyear')
		  ->get();
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

  
}
