<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

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
			//print_r($post); die;   
			$ret[] = $this->update_analytics($post);
			
			$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
			
		}
		echo "Analytics Updated ".date("d/m/Y");
		$this->benchmark->mark('code_end');
		echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
			

		//echo "<pre>"; print_r($ret);
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

        $res = $this->get_summary($post);
        
        $insertArray = array();
        $insertArray['signup'] = ($res['TotalUsers']) ? $res['TotalUsers'] : 0;
        $insertArray['wallet_balance'] =( $res['TotalBalance']) ? $res['TotalBalance']:0;
        $insertArray['wallet_balance_users'] = ($res['TotalBalanceUserCount']) ? $res['TotalBalanceUserCount']:0;
        $insertArray['first_time_deposit'] = ($res['FirstTimeUser']['totalDeposit']) ? $res['FirstTimeUser']['totalDeposit']:0;
        $insertArray['first_time_depositors'] = ($res['FirstTimeUser']['totalUser']) ? $res['FirstTimeUser']['totalUser']:0;
        $insertArray['created'] = $startDate;

		
		$timeline = $this->get_deposit($post); 
		$insertArray['deposit'] = $timeline['grandTotalDepositAmt'];
		$insertArray['deposit_users'] = $timeline['grandTotalUser'];

		
		$siterake = $this->get_siterake($post);
		$insertArray['site_rake'] = $siterake['grandTotalSiterake'];
		$insertArray['site_rake_users'] = $siterake['grandTotalUser'];

		$referral = $this->get_referral($post);
		$insertArray['referral_amount_distributed'] = $referral['grandTotalAmount'];
		$insertArray['no_of_referrals'] = $referral['grandTotalUser'];

		$freepaidusers = $this->get_freepaidusers($post);
		$insertArray['paid_users'] = $freepaidusers['paidusers']['data']['grandTotalUser'];
		$insertArray['free_users'] = $freepaidusers['freeusers']['data']['grandTotalFreeUser'];
		
		$activeusers = $this->get_active_users_new($post);
		$active_user_ids = array("user_ids"=>$activeusers['totalActiveUsers']);
		$insertArray['active_users'] = json_encode($active_user_ids);

		$passiveusers = $this->get_passive_users($post);
		$passive_user_ids = array("user_ids"=>$passiveusers['totalPassiveUsers']);
		$insertArray['passive_users'] = json_encode($passive_user_ids);
		//uncomment to get data from google for daily monthly 

		/* $active_users  = $this->get_active_users($post);
		$insertArray['daily_visitors'] = $active_users['daily']['Visitors'];
		$insertArray['daily_loggedInusers'] = $active_users['daily']['loggedInusers'];
		$insertArray['monthly_visitors'] = $active_users['monthly']['Visitors'];
		$insertArray['monthly_loggedInusers'] = $active_users['monthly']['loggedInusers']; */
		
		$this->Analytics_model->insert($insertArray);
		return $insertArray;
		
        
    }
    public function get_summary($post)
	{
		
		$result =  $this->Analytics_model->get_summary($post);
		return $result;
	}

	public function get_deposit($post)
	{
		
		$result =  $this->Analytics_model->get_deposit($post);
		return $result['data'];
	}
	public function get_freepaidusers($post)
	{
		
		$result['paidusers'] =  $this->Analytics_model->get_freepaidusers($post);
		$result['freeusers'] =  $this->Analytics_model->get_contest_usercount($post);
		
		return $result;
	}
	public function get_devices($post)
	{
		
		$result =  $this->Analytics_model->get_devices($post);
		return $data['data'];
	}
	public function get_siterake($post)
	{	
		$data		=  $this->Analytics_model->get_siterake($post);
		return $data['data'];
		
	}
	public function get_leaderboard($post)
	{	
		$result =  $this->Analytics_model->get_leaderboard($post);
		return $result;
	}
	public function get_referral($post)
	{	
		
		$result =  $this->Analytics_model->get_referral($post);
		return $result['data'];
	}

	/**
     * active users acc to google : The number of distinct users who visited your site or app.
     * I am taking country wise unique users in given date and sum up them as active users
     */
	public function get_active_users($post)
	{
		if($is_ga4){
			$config = array(
				"app_credentials"=>$this->app_config['GA4_credentials']['key_value'],
				"cloud_project_id"=>$this->app_config['GA4_cloud_project_id']['key_value']
			);
			$this->load->library('Ga4_library');
			$obj = new Ga4_library($config);
			$result['daily']  = $obj->activeUsers($post['EndDate'],$post['EndDate']);
			$result['monthly']  = $obj->activeUsers(date("Y-m-d", strtotime($post['EndDate'] . " -30 days")),$post['EndDate']);
			return $result;
		}else{
			$this->load->library('Google_analytics_dashboard');
			$google_analytics_dashboard = new Google_analytics_dashboard(); 
			//date("Y-m-d"),date("Y-m-d")
			$result['daily']  = $google_analytics_dashboard->activeUsers($post['EndDate'],$post['EndDate']);
			//print_r($post); die;
			$result['monthly']  = $google_analytics_dashboard->activeUsers(date("Y-m-d", strtotime($post['EndDate'] . " -30 days")),$post['EndDate']);
			//print_r($result); die;
			return $result;
		}
	}

	public function get_active_users_new($post){
		$result  = $this->Analytics_model->get_active_users_new($post);
		return $result['data'];
	}

	public function get_passive_users($post){
		$result  = $this->Analytics_model->get_passive_users($post);
		return $result['data'];
	}
    
}
