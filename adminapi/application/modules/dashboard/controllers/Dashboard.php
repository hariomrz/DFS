<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MYREST_Controller{

	public function __construct()
	{
		parent::__construct();
		$_POST				= $this->input->post();
		$this->admin_lang = $this->lang->line('blog');
		$this->load->model('Dashboard_model');
		$this->admin_roles_manage($this->admin_id,'dashboard');
	}

	public function get_timelines_post()
	{
		$post = $this->input->post();
		$result =  $this->Dashboard_model->get_timelines($post);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'information get successfully';
		$this->api_response_arry['data']			= $result['data'];
		$this->api_response();
	}

	public function test_post(){
		$this->load->library('Ga4_library');
		$obj = new Ga4_library();
		$result = $obj->activeUsers();
	}

	public function get_segregation_post()
	{
		$is_ga4 = $this->app_config['is_ga4']['key_value'];
			if($is_ga4){
				@$filename = ROOT_PATH.$this->app_config['GA4_credentials']['key_value']; //GOOGLE_APPLICATION_CREDENTIALS;
				if(file_exists($filename)==TRUE){
				$post = $this->input->post();
				$current_date = format_date();
				$start_date = ($post['startDate']) ? date("Y-m-d", strtotime($post['startDate'])):date ("Y-m-d", strtotime("-7 day", strtotime($current_date)));
				$end_date = ($post['endDate'])? date("Y-m-d", strtotime($post['endDate'])):'today';
				$this->load->library('Ga4_library');
				$obj = new Ga4_library(["app_credentials"=>"$filename","cloud_project_id"=>$this->app_config['GA4_cloud_project_id']['key_value']]);
				$result = $obj->eventDetails($start_date,$end_date);

			}else{
					$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
					$this->api_response_arry['data']			= '';
					$this->api_response();
				}

			}else{
			@$filename = ROOT_PATH.$this->app_config['GA3_file']['key_value']; //GA_PRIVATE_KEY_LOCATION;
				if(file_exists($filename)==TRUE){
				$post = $this->input->post();
				$this->load->library('Google_analytics_dashboard');
				$google_analytics_dashboard = new Google_analytics_dashboard(["private_key_location"=>"$filename"]); 
				$result  = $google_analytics_dashboard->eventDetails(date("Y-m-d", strtotime($post['startDate'])),date("Y-m-d", strtotime($post['endDate'])));
				//$result['appUsage']  = $google_analytics_dashboard->appUsageResults(date("Y-m-d", strtotime($post['startDate'])),date("Y-m-d", strtotime($post['endDate'])));
				//$result['browserUsage']  = $google_analytics_dashboard->browserUsageResults(date("Y-m-d", strtotime($post['startDate'])),date("Y-m-d", strtotime($post['endDate'])));
				}
				else{
					$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
					$this->api_response_arry['data']			= '';
					$this->api_response();
				}
			}
		$ins_unins_date = $this->Dashboard_model->get_install_uninstall_date();
		$result['install_count'] = $ins_unins_date['install_count'];
		$result['uninstall_count'] = $ins_unins_date['uninstall_count'];
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'information get successfully';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	
	
	}
	public function get_active_users_post()
	{
		$post = $this->input->post();
		$is_ga4 = $this->app_config['is_ga4']['key_value'];
			if($is_ga4){
				@$filename = $this->app_config['GA4_credentials']['key_value'];
				if(file_exists($filename)==TRUE){
					$current_date = format_date();
					$config = array(
						"app_credentials"=>"$filename",
						"cloud_project_id"=>$this->app_config['GA4_cloud_project_id']['key_value']
					);
					$this->load->library('Ga4_library');
					$obj = new Ga4_library($config);
					$result['daily'] 	= $obj->activeUsers(date("Y-m-d", strtotime($post['startDate'])),date("Y-m-d", strtotime($post['endDate'])));
					$result['monthly']  = $obj->activeUsers(date("Y-m-d", strtotime('-30 days')),date("Y-m-d"));
			}else{
					$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
					$this->api_response_arry['data']			= '';
					$this->api_response();
				}

			}else{
				@$filename = ROOT_PATH.$this->app_config['GA3_file']['key_value']; //GA_PRIVATE_KEY_LOCATION;
				if(file_exists($filename)==TRUE){
					$post=$this->input->post();
				//get direct data from google 
				$this->load->library('Google_analytics_dashboard');
				$google_analytics_dashboard = new Google_analytics_dashboard(); 
				$result['daily']  = $google_analytics_dashboard->activeUsers(date("Y-m-d", strtotime($post['startDate'])),date("Y-m-d", strtotime($post['endDate'])));
				$result['monthly']  = $google_analytics_dashboard->activeUsers(date("Y-m-d", strtotime('-30 days')),date("Y-m-d"));
			}
			else{
				$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
				$this->api_response_arry['data']			= '';
				$this->api_response();
			}
		}
		//get  data from database  
		/* $post = $this->input->post();
		$result =  $this->Dashboard_model->get_active_users($post); */
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'information get successfully';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
		
	}

	public function get_freepaidusers_post()
	{
		$post = $this->input->post();
		$result =  $this->Dashboard_model->get_freepaidusers($post);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'information get successfully';
		$this->api_response_arry['data']		= $result['data'];
		
		$this->api_response();
	}
	public function get_devices_post()
	{
		$post = $this->input->post();
		$result =  $this->Dashboard_model->get_devices($post);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'information get successfully';
		$this->api_response_arry['data']			= $result['data'];
		$this->api_response();
	}
	public function get_siterake_post()
	{	
		$post = $this->input->post();
		$result =  $this->Dashboard_model->get_siterake($post);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'information get successfully';
		$this->api_response_arry['data']			= $result['data'];
		$this->api_response();
	}
	public function get_leaderboard_post()
	{	
		$post = $this->input->post();
		$result =  $this->Dashboard_model->get_leaderboard($post);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'information get successfully';
		$this->api_response_arry['data']			= $result['data'];
		$this->api_response();
	}
	public function get_referral_post()
	{	
		$post = $this->input->post();
		$result =  $this->Dashboard_model->get_referral($post);
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'information get successfully';
		$this->api_response_arry['data']			= $result['data'];
		$this->api_response();
	}

	public function get_calculated_summary_post()
	{
		$post = $this->input->post();
		
		

		$result =  $this->Dashboard_model->get_calculated_summary($post);
/* 		$this->load->library('Google_analytics_dashboard');
		$google_analytics_dashboard = new Google_analytics_dashboard(); 
		$result['segrigation']  = $google_analytics_dashboard->eventDetails(date("Y-m-d", strtotime($post['startDate'])),date("Y-m-d", strtotime($post['endDate']))); */
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'information get successfully';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	}

	/**
	 * get referral rank by custome date
	 *  */	
	public function get_referral_rank_post(){
		$post_data= $this->input->post();
		if($post_data['leaderboard']=='deposit' || $post_data['leaderboard']=='referral' || $post_data['leaderboard']=='time_spent' || $post_data['leaderboard']=='withdrawal'){
			$result = $this->Dashboard_model->get_referral_rank();
		}

		if($post_data['leaderboard']=='winning' || $post_data['leaderboard']=='team'){
			$this->load->model('contest/contest_model','contest_model');
			if($post_data['leaderboard']=='winning'){
				$ranks = $this->contest_model->get_contest_rank(); 
			}
			if($post_data['leaderboard']=='team')
			{
				$ranks = $this->contest_model->get_team_create_rank(); 
			}  
			$user_ids = array_column($ranks['result'],'user_id');
			$user_detail = $this->Dashboard_model->get_userdetails_by_userid($user_ids);
			$final_user_detail = array();
			$final_data = array();
				foreach($user_detail as $key=>$detail){
					$final_user_detail[$detail['user_id']]=$detail;
				}
				$i=1;
			foreach($ranks['result'] as $key=>$result){
				if(isset($final_user_detail[$result['user_id']]['user_unique_id'])){
				$final_data[$i]=$result;
				$final_data[$i]['user_unique_id']= ($final_user_detail[$result['user_id']])? $final_user_detail[$result['user_id']]['user_unique_id']:'--';
				$final_data[$i]['image']= ($final_user_detail[$result['user_id']])? $final_user_detail[$result['user_id']]['image']:'';
				$final_data[$i]['email']= ($final_user_detail[$result['user_id']])? $final_user_detail[$result['user_id']]['email']:'--';
				$final_data[$i]['phone']= ($final_user_detail[$result['user_id']])? $final_user_detail[$result['user_id']]['phone_no']:'--';
				$final_data[$i]['city']= ($final_user_detail[$result['user_id']])? $final_user_detail[$result['user_id']]['city']:'--';
				$i++;
				}
			}
			if($post_data['leaderboard']=='winning'){
				$result = array("result"=>$final_data,"total"=>count($user_detail),"winning"=>$ranks['winning'],"fee"=>$ranks['fee'],"site_rake"=>$ranks['site_rake'],"total_contest"=>$ranks['total_contest']);
			}
			if($post_data['leaderboard']=='team')
			{
				$result = array("result"=>$final_data,"total"=>count($user_detail));
			} 
		}
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['status']			= TRUE;
		$this->api_response_arry['message']			= 'information get successfully';
		$this->api_response_arry['data']			= $result;
		$this->api_response();
	}

	public function get_referral_rank_get(){ 
		$_POST['csv']=true;
		$_POST = $post_data = $this->input->get();
		//convert date to client timezone
	
		if($post_data['leaderboard']=='deposit' || $post_data['leaderboard']=='referral' || $post_data['leaderboard']=='time_spent' || $post_data['leaderboard']=='withdrawal'){
			$result = $this->Dashboard_model->get_referral_rank();
			foreach($result['result'] as $res){
				unset($res['image']);
			}
		}

		if($post_data['leaderboard']=='winning' || $post_data['leaderboard']=='team'){
			$this->load->model('contest/contest_model','contest_model');
			if($post_data['leaderboard']=='winning'){
				$ranks = $this->contest_model->get_contest_rank(); 
			}
			if($post_data['leaderboard']=='team')
			{
				$ranks = $this->contest_model->get_team_create_rank(); 
			}
			$user_ids = array_column($ranks['result'],'user_id');
			$user_detail = $this->Dashboard_model->get_userdetails_by_userid($user_ids);
			$final_user_detail = array();
			$final_data = array();
				foreach($user_detail as $key=>$detail){
					$final_user_detail[$detail['user_id']]=$detail;
				}
				$i=0;
			foreach($ranks['result'] as $key=>$result){
				if(isset($final_user_detail[$result['user_id']]['user_unique_id'])){
				$final_data[$i]=$result;
				$final_data[$i]['user_unique_id']= ($final_user_detail[$result['user_id']])? $final_user_detail[$result['user_id']]['user_unique_id']:'--';
				$final_data[$i]['email']= ($final_user_detail[$result['user_id']])? $final_user_detail[$result['user_id']]['email']:'--';
				$final_data[$i]['phone']= ($final_user_detail[$result['user_id']])? $final_user_detail[$result['user_id']]['phone_no']:'--';
				$final_data[$i]['city']= ($final_user_detail[$result['user_id']])? $final_user_detail[$result['user_id']]['city']:'--';
				$i++;
				}
			}
			$result = array("result"=>$final_data,"total"=>count($user_detail));
		}
		
		if(!empty($result['result'])){
			$result =$result['result'];
			$header = array_keys($result[0]);
			$camelCaseHeader = array_map("camelCaseString", $header);
			$result = array_merge(array($camelCaseHeader),$result);
			$this->load->helper('csv');
			array_to_csv($result,$post_data['leaderboard'].'.csv');
		}	else{
		        $result = "no record found";
				$this->load->helper('download');
				$this->load->helper('csv');
				$data = array_to_csv($result);
				$name = $post_data['leaderboard'].'.csv';
				force_download($name, $result);
	}
	
	}

	public function get_app_usage_data_post(){
		$this->load->model('auth/Auth_nosql_model','nosql_model');
		
		$post_data = $this->input->post();
		//it is for user
		if(isset($post_data['user_id']) && $post_data['user_id'] !=''){
			$user_data = array();
			$user_data = $this->Dashboard_model->get_user_time_spent_rank();
			$install_dates = $this->Dashboard_model->get_single_row('ios_install_date,android_install_date',USER,array('user_id'=>$post_data['user_id']));
			$currentDateStart = date('Y-m-d') . " 00:00:00";
			$currentDateEnd = date('Y-m-d') . " 23:59:59";
		}
		else{
			$this->form_validation->set_rules('from_date', 'From Date', 'trim|required');
			$this->form_validation->set_rules('to_date', 'To Date', 'trim|required');
			
			if (!$this->form_validation->run()) 
			{
				$this->send_validation_errors();
			}
			$from_date = date("Y-m-d",strtotime($post_data['from_date']));
			$to_date = date("Y-m-d",strtotime($post_data['to_date']));
			$currentDateStart = date($from_date) . " 00:00:00";
			$currentDateEnd = date($to_date) . " 23:59:59";
		}

		$mongo_date_start = $this->nosql_model->normal_to_mongo_date($currentDateStart);
		$mongo_date_end = $this->nosql_model->normal_to_mongo_date($currentDateEnd);
		// $mongo_date_start = $this->nosql_model->normal_to_mongo_date('2021-06-01 00:00:00');
		// $mongo_date_end = $this->nosql_model->normal_to_mongo_date('2021-06-01 23:59:59');

		// echo $mongo_date_start; exit;
		$all_records = $this->Dashboard_model->fetch_active_session_records($mongo_date_start, $mongo_date_end);
		if (isset($all_records) && count($all_records) > 0)
        {
			$graph_data = array();
			$ios_count=0;
			$android_count=0;
			$web_count = 0;
			$android_tab =0;
			$android_mobile_web = 0;
			$android_app =0;
			$ipad =0;
			$ios_browser=0;
			$ios_app=0;
			$android_install_date = $ios_install_date = '';
			$ios_device_name = $ios_device_version =array();
			
			$desktop = $total_plateform = $device_total = $mobile = $tablet = $ios_per = $android_per = $web_per = $mobile_per = $tablet_per = $desktop_per =0;
            foreach ($all_records as $key => $value)
            {
                                if($value['platform']==1){
										$android_count++;
										if($value['is_tablet']==1 && $value['is_browser']==1){
											$android_tab++;
										}elseif($value['is_tablet']==0 && $value['is_browser']==1){
											$android_mobile_web++;
										}elseif($value['is_tablet']==0 && $value['is_browser']==0){
											$android_app++;
										}

										if(!empty($value['device_name'])){
											$android_device_name[] = $value['device_name'];
										}
										if(!empty($value['device_os_version'])){
											$android_device_version[] = $value['device_os_version'];
										}
                                }elseif($value['platform']==2){
										$ios_count++;
										if($value['is_tablet']==1 && $value['is_browser']==1){
											$ipad++;
										}elseif($value['is_tablet']==0 && $value['is_browser']==1){
											$ios_browser++;
										}elseif($value['is_tablet']==0 && $value['is_browser']==0){
											$ios_app;
										}
										
										if(!empty($value['device_name'])){
											$ios_device_name[] = $value['device_name'];
										}
										if(!empty($value['device_os_version'])){
											$ios_device_version[] = $value['device_os_version'];
										}
                                }elseif($value['platform']==3){
										$web_count++;
										if($value['is_tablet']==0 && $value['is_browser']==1){
											$desktop++;
										}
                                }
			}
			$android_device_name = $android_device_name ? $android_device_name[max(array_keys($android_device_name))]:'';
			$android_device_version = $android_device_version ? $android_device_version[max(array_keys($android_device_version))]:'';
			$ios_device_name = $ios_device_name ?$ios_device_name[max(array_keys($ios_device_name))]:'';
			$ios_device_version = $ios_device_version ? $ios_device_version[max(array_keys($ios_device_version))]:'';

			$total_plateform = $ios_count+$android_count+$web_count;
			$ios_per = round(($ios_count*100)/$total_plateform,2);
			$android_per = round(($android_count*100)/$total_plateform,2);
			$web_per = round(($web_count*100)/$total_plateform,2);

			$mobile = $android_app+$android_mobile_web+$ios_app+$ios_browser;
			$tablet = $android_tab+$ipad;
			$device_total = $mobile+$tablet+$desktop;
			
			if($mobile>0){
				$mobile_per = round(($mobile*100)/$device_total,2);
			}
			if($mobile>0){
				$tablet_per = round(($tablet*100)/$device_total,2);
			}
			if($mobile>0){
				$desktop_per = round(($desktop*100)/$device_total,2);
			}

			$platforms= array(
				"ios"=>($ios_count)?$ios_count:0,
				"android"=>($android_count)?$android_count:0,
				"web"=>($web_count)?$web_count:0,
				"ios_per"=>($ios_per)?$ios_per:0,
				"android_per"=>($android_per)?$android_per:0,
				"web_per"=>($web_per)?$web_per:0,
				"android_tab"=>($android_tab)?round(($android_tab*100)/$tablet,2):0,
				"ipad"=>($ipad)?round(($ipad*100)/$tablet,2):0,
				"android_mobile_web"=>($android_mobile_web)?round(($android_mobile_web*100)/$mobile,2):0,
				"android_app"=>($android_app)?round(($android_app*100)/$mobile,2):0,
				"ios_browser"=>($ios_browser)?round(($ios_browser*100)/$mobile,2):0,
				"ios_app"=>($ios_app)?round(($ios_app*100)/$mobile,2):0,
				"desktop"=>($desktop)?$desktop:0,
				"mobile_per"=>($mobile_per)?$mobile_per:0,
				"tablet_per"=>($tablet_per)?$tablet_per:0,
				"desktop_per"=>($desktop_per)?$desktop_per:0,
			);
			array_push($graph_data,array(
				"name"=>"Mobile",
				"color"=>"#3F0008",
				"y"=>($mobile_per)?$mobile_per:0,
			));

			array_push($graph_data,array(
				"name"=>"Tablet",
				"color"=>"#E55D6E",
				"y"=>($tablet_per)?$tablet_per:0,
			));

			array_push($graph_data,array(
				"name"=>"Desktop",
				"color"=>"#4BC08F",
				"y"=>($desktop_per)?$desktop_per:0,
			));
			if(isset($post_data['user_id']) && $post_data['user_id'] !=''){
			$user_detail= array(
				"rank"=>($user_data['rank_value'])?$user_data['rank_value']:'--',
				"total_session_time"=>($user_data['time_spent'])?sprintf('%02d:%02d:%02d', ($user_data['time_spent']/3600),($user_data['time_spent']/60%60), $user_data['time_spent']%60):'--',
				"notification_status"=>($user_data['is_notification'])?$user_data['is_notification']:'--',
				"ios_install_date"=>($install_dates['ios_install_date'])?$install_dates['ios_install_date']:'',
				"android_install_date"=>($install_dates['android_install_date'])?$install_dates['android_install_date']:'',
				"ios_device"=>($ios_device_name)?$ios_device_name:'--',
				"ios_version"=>($ios_device_version)?$ios_device_version:'--',
				"android_device"=>($android_device_name)?$android_device_name:'--',
				"android_version"=>($android_device_version)?$android_device_version:'--',
			);
			$this->api_response_arry['data']['user_detail']			= $user_detail;
			}
				
			$this->api_response_arry['response_code']				= rest_controller::HTTP_OK;
			$this->api_response_arry['status']						= TRUE;
			$this->api_response_arry['message']						= 'information get successfully';
			$this->api_response_arry['data']						= $platforms;
			$this->api_response_arry['data']['series_data']			= $graph_data;
			
			$this->api_response();
			
		}
		else{
			//$this->api_response_arry['response_code']				= rest_controller::HTTP_OK;
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
            $this->api_response_arry['message']  		= '';
			$this->api_response_arry['data']			= array();
            $this->api_response();
        }
	}
}

/* End of file Auth.php */
/* Location: ./application/controllers/dashboard/Dashboard.php */
