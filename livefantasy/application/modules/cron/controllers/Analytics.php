<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

set_time_limit(0);
class Analytics extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Analytics_model'); 

    }

    public function index()
	{	
		$post=array();	

		$this->benchmark->mark('code_start');
            
		$post['StartDate'] = isset($_GET['StartDate'])?$_GET['StartDate']:date("Y-m-d");
		$post['EndDate'] = isset($_GET['EndDate'])?$_GET['EndDate']:date("Y-m-d");      
		
		// Set timezone
		date_default_timezone_set('UTC');
  
		// Start date
		$date = $post['StartDate'];
		// End date
		$end_date = $post['EndDate'];  

	   if(strtotime($end_date) > strtotime(date("Y-m-d"))){
		echo "Wrong date".strtotime($end_date) .'>='. strtotime(date("Y-m-d")); die;
	   }

	   $ret = array();
		while (strtotime($date) <= strtotime($end_date)) {
			//use +1 month to loop through months between start and end dates
			$post['StartDate'] = $date;
			$post['EndDate'] = $date;   
			$ret[] = $this->update_analytics($post);
			$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
		}
		echo "Analytics Updated ".date("d/m/Y");
		$this->benchmark->mark('code_end');
		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
		// echo "<pre>"; print_r($ret);
		die;
    }

     /**
     * [update_analytics description]
     * Summary :-
     * @param  [type] $post [description]
     * @return [type]            [description]
     */
    public function update_analytics($post)
    {
		
		if (isset($post['StartDate']))
		$startDate = date("Y-m-d", strtotime($post['StartDate']));
		else
			$startDate = date("Y-m-d");
		if (isset($post['EndDate']))
			$endDate = date("Y-m-d", strtotime($post['EndDate']));
		else
			$endDate = date("Y-m-d");

			$post['StartDate'] = $startDate;
			$post['EndDate'] = $endDate;

        // $res = $this->get_summary($post);
        
        $insertArray = array();
        $insertArray['created'] = $startDate;

        $siterake = $this->get_siterake($post);
		$insertArray['site_rake'] = isset($siterake['grandTotalSiterake']) ? $siterake['grandTotalSiterake'] : 0;
		$insertArray['site_rake_users'] = isset($siterake['grandTotalUser']) ? $siterake['grandTotalUser'] : 0;

		
		$freepaidusers = $this->get_freepaidusers($post);
		$insertArray['paid_users'] = isset($freepaidusers['paidusers']['data']['grandTotalUser']) ? $freepaidusers['paidusers']['data']['grandTotalUser'] : 0;
		$insertArray['free_users'] = isset($freepaidusers['freeusers']['data']['grandTotalFreeUser']) ? $freepaidusers['freeusers']['data']['grandTotalFreeUser'] : 0;
		
		$this->Analytics_model->insert($insertArray);
		return $insertArray;
    }

    public function get_siterake($post)
	{	
		$data =  $this->Analytics_model->get_siterake($post);
		return $data['data'];
	}

	public function get_freepaidusers($post)
	{
		$result['paidusers'] =  $this->Analytics_model->get_freepaidusers($post);
		$result['freeusers'] =  $this->Analytics_model->get_contest_usercount($post);
		return $result;
	}
	
}
