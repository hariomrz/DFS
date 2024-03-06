<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends MY_Model {

	public function __construct()
	{

		parent::__construct();
		$this->load->database('user_db');
		// $this->db_analytics		= $this->load->database('db_analytics', TRUE);
				
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
            $this->db->select("site_rake,site_rake_users,created")
                            ->from(ANALYTICS)
                            ->where("created BETWEEN '".$startDate."' AND '".$endDate."'")
                                ->group_by("created");
            $result = $this->db->get()->result();				   
        }
        if($filter_type=='weekly'){


        
            $query = $this->db->query("SELECT t1.week_name,t1.monthyear, t1.week_no, concat_ws(' ',t1.week_no,t1.d1,t1.d2) as created, t1.year, coalesce(SUM(t1.site_rake+t2.site_rake), 0) AS site_rake,coalesce(SUM(t1.site_rake_users+t2.site_rake_users), 0) AS site_rake_users

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
            // echo $this->db->last_query(); die;
        }
        if($filter_type=='monthly'){


        
            $query = $this->db->query("SELECT t1.month,

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
                //echo $this->db->last_query(); die;
            
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
				$this->db->select("free_users,paid_users,created")
								   ->from(ANALYTICS)
								   ->where("created BETWEEN '".$startDate."' AND '".$endDate."'")
							   		->group_by("created");
				$result = $this->db->get()->result();				   
			}
			if($filter_type=='weekly'){
	
	
			
				$query = $this->db->query("SELECT t1.week_name,t1.monthyear, t1.week_no, concat_ws(' ',t1.week_no,t1.d1,t1.d2) as created, t1.year, coalesce(SUM(t1.free_users+t2.free_users), 0) AS free_users,coalesce(SUM(t1.paid_users+t2.paid_users), 0) AS paid_users
		
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
	
	
			
				$query = $this->db->query("SELECT t1.month,
		
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

}