<?php
require_once APPPATH.'modules/communication_dashboard/controllers/User_segmentation.php';
class New_campaign extends User_segmentation {

	var $userbase_details = array();
	var $activity_list = array('all_user','login','signup','fixture_participation','user_base_list_id');

	public function __construct()
	{
		parent::__construct();
		ini_set("memory_limit",-1);
		$this->load->model('New_campaign_model');
		$_POST = $this->input->post();	

		$this->userbase_details = array(
			'login' => array('userbase' => 2,
							 'user_details' => 'Login',
							 'activity_type' => 2
							),
			'non_login' => array('userbase' => 5,
							 'user_details' => 'Non Login',
							 'activity_type' => 5
							),
			'signup' => array('userbase' => 3,
							  'user_details' => 'Signup',
							  'activity_type' => 2
							),
			'all_user' => array('userbase' => 1,
								'user_details' => 'All User',
								'activity_type' => 0),
			'fixture_participation' => array('userbase' => 4,
								'user_details' => 'Fixuture Paticipation',
								'activity_type' => 2
							),
			'user_base_list_id' => array('userbase' => 5,
								'user_details' => 'user_base_list_id',
								'activity_type' => 5),
		);
		$this->admin_roles_manage($this->admin_id,'marketing');
	}

	function get_live_upcoming_fixtures_post()
	{

		$this->form_validation->set_rules('sports_id', 'Sport' , 'trim|required');
		if($this->form_validation->run() == FALSE)
		{
			$this->send_validation_errors();
		}
		
		$sports_id = $this->input->post("sports_id");
		//get fixture list
		$list = $this->get_all_season_schedule_post(false);
		
		$fixtures = array();
		if(!empty($list))
		{
			$fixtures = array_merge($list['result']['live_fixture'],$list['result']['upcoming_fixture']);
		}


		$collection_data = array();
		if(!empty($list))
		{
			$league_ids = array_unique(array_column($fixtures, 'league_id')) ;
			$this->load->model('contest/Contest_model');
			$collection_data = $this->Contest_model->get_live_upcoming_collection($sports_id);
			$fixture_list = array_column($fixtures,NULL,'season_id');
			foreach ($collection_data as $key => &$value) {
				if(isset($fixture_list[$value['season_id']]))
				{
					$value = array_merge($value,$fixture_list[$value['season_id']]);
				}
			}
		}
		$this->api_response_arry['data']['collection'] = $collection_data;
		$this->api_response();
	}


	public function get_filter_result_test_post($call_from_api=true)
	{
		$post = $this->input->post();
		if(empty($post['all_user']) && empty($post['login']) && empty($post['non_login']) && empty($post['signup']) && empty($post['user_base_list_id']) && empty($post['fixture_participation']))
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['data']  			= array();
			$this->api_response_arry['global_error']    = "Please select a activity type.";
			$this->api_response();
		}

		if(!empty($post['custom']) && (empty($post['from_date']) || empty($post['to_date'])))
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['data']  			= array();
			$this->api_response_arry['global_error']    = "Please select valid dates.";
			$this->api_response();
		}

		if(!empty($post['from_date']) && !empty($post['to_date']) && strtotime($post['from_date']) >  strtotime($post['to_date'])  )
			{
				$this->api_response_arry['response_code'] 	= 500;
				$this->api_response_arry['data']  			= array();
				$this->api_response_arry['global_error']    = "Please select Valid date range.";
				$this->api_response();
			}



		if(empty($post['added_date']))
		{
			$post['added_date'] = format_date();
		}

		$result = array();
		if(!empty($post))
		{
			if(isset($post['last_7_days']) && $post['last_7_days'] == 1)
			{
				$post['activity_type'] =1;
				$post['from_date'] = date('Y-m-d h:i:s',strtotime($post['added_date'].' -7 days'));
				$post['to_date'] = $post['added_date'];
			}


			if(isset($post['login']) && $post['login'] == '1') //login activity
			{
				unset($post['user_base_list_id']);
				$result_data =  $this->User_segmentation_model->get_login_user_count_by_filter($post);
				$result['total_users'] = 0;

				if(!empty($result_data))
				{
					$user_ids =array_unique(array_column($result_data, 'user_id')) ;
					$result['total_users'] = count($user_ids);
					$result['user_ids'] = $user_ids;
				}
			}

			if(isset($post['non_login']) && $post['non_login'] == '1') //login activity
			{
				unset($post['user_base_list_id']);
				$result_data =  $this->User_segmentation_model->get_non_login_user_count_by_filter($post);
				$result['total_users'] = 0;

				if(!empty($result_data))
				{
					$user_ids =array_unique(array_column($result_data, 'user_id')) ;
					$result['total_users'] = count($user_ids);
					$result['user_ids'] = $user_ids;
				}
			}

			if(isset($post['signup']) && $post['signup'] == '1') //signup activity
			{
				unset($post['user_base_list_id']);
				$result_data =  $this->User_segmentation_model->get_signup_user_count_by_filter($post);
				$result['total_users'] = 0;
				if(!empty($result_data))
				{
					$user_ids =array_unique(array_column($result_data, 'user_id')) ;
					$result['total_users'] = count($user_ids);
					$result['user_ids'] = $user_ids;
				}
			}

			if(isset($post['all_user']) && $post['all_user'] == '1')
			{
				unset($post['user_base_list_id']);
				$result_data = $this->User_segmentation_model->get_all_table_data('user_id',USER,array('is_systemuser'=> 0,'status'=>1));
				$result['total_users'] = 0;

				if(!empty($result_data))
				{
					$user_ids =array_unique(array_column($result_data, 'user_id')) ;
					$result['total_users'] = count($user_ids);
					$result['user_ids'] = $user_ids;
				}

			}

			if(isset($post['fixture_participation']) && $post['fixture_participation'] == '1')
			{
				$users = $this->get_fixture_users($post['season_game_uid']);
				if(!empty($users))
				{
					$result['total_users'] = count($users);
					$user_ids = array_column($users, 'user_id');
					$result['user_ids'] = array_unique($user_ids);
				}

			}
			if(!empty($post['user_base_list_id'])){
				$result_data =  $this->User_segmentation_model->get_single_user_base_list($post['user_base_list_id']);
				$result['total_users'] = 0;
				if(!empty($result_data))
				{
					$result['total_users'] = $result_data[0]['count'];
					$result['user_base_list_id'] = $result_data[0]['user_base_list_id'];
					$result['user_ids'] = explode(',',$result_data[0]['user_ids']);
				}
			}

		}
		if($call_from_api)
		{
			$this->api_response_arry['response_code'] 	= 200;
			$this->api_response_arry['data']  			= $result;
			$this->api_response();
		}
		else
		{
			return $result;
		}
	}

	public function get_fixture_users($season_game_uid='')
	{
		if(empty($season_game_uid))
		{
			return array();
		}

		$this->load->model('contest/Contest_model');
		$data = $this->Contest_model->get_fixture_users($season_game_uid);
		return $data;
	}

	function notify_by_selection_post()
	{
		$post= $this->input->post();
		$category_id = $post['email_template_id'];
		$email_template_id = $post['promo_code_id'];

		if($category_id==9){
			$email_template_id = 13;
		}
		elseif($category_id==10) {
			$email_template_id = 14;
		}

		$is_email = (!empty($post['email']) && $post['email'])?1:0;
		$is_notification = (!empty($post['notification']) && $post['notification'])?1:0;
		if(empty($post['email']) && empty($post['message']) && empty($post['notification']))
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['data']  			= array();
			$this->api_response_arry['global_error']    = "Please select a notify type.";
			$this->api_response();
		}

		$users_result = $this->get_filter_result_test_post(false);
		$post['user_ids'] = $users_result['user_ids'];

		if(empty($email_template_id))
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['data']  			= array();
			$this->api_response_arry['global_error']    = "Please select a template.";
			$this->api_response();
		}
		//send email and notifiction
		$result = $this->New_campaign_model->get_users_device_by_ids($post['user_ids']);
		//get template
		if(getenv('CD_TEST_MODE')==1)
		{
			 $result  = array($result[0]);
		}

		$email_template = $this->New_campaign_model->get_single_row('*',CD_EMAIL_TEMPLATE,array('cd_email_template_id' => $email_template_id ));
		if(!$email_template && $category_id==14)
		{
			$email_template = $this->New_campaign_model->get_single_row('*',CD_EMAIL_TEMPLATE,array('category_id' => $category_id ));
		}
		//get user ids......// this section is commented as we need to send all users not only first deposit
		if($email_template['template_name'] == "promotion-for-deposit" || $email_template['template_name'] == "promotion-for-deposit-second")
		{
			$refer_row= $this->User_segmentation_model->get_promocode_detail($post['promoCodeId']);
			if($refer_row['type']==0){
				$user_ids = array_column($result, 'user_id');
			}
		} 

		$notification_desc = $this->New_campaign_model->get_single_row('*',NOTIFICATION_DESCRIPTION,array('notification_type' => $email_template['notification_type']));
		$chunks = array_chunk($result, 200);
		$current_date = format_date();		
		$message_numbers = array();
		$communication_data = array();
		foreach ($post as $key => $value) {
			if(in_array($key,$this->activity_list) && $post[$key] == true)
			{
				$communication_data = $this->userbase_details[$key];
				break;
			}
		}
		//save communication data
		if(!empty($post['from_date']))
		{
			$communication_data['from_date'] = $post['from_date'];
		}

		if(!empty($post['to_date']))
		{
			$communication_data['to_date'] = $post['to_date'];
		}


		$communication_data['added_date'] = $post['schedule_date'] ? $post['schedule_date']:format_date();
		if(!empty($post['custom']) && $post['custom'] == '1')
		{
			$communication_data['activity_type'] =2;
		}

		if(!empty($post['last_7_days']) && $post['last_7_days'] == 1)
		{
			$communication_data['activity_type'] =1;
			if(!empty($post['non_login']) && $post['non_login'] == 1)
			{
				$communication_data['activity_type'] =5;
			}

			$communication_data['from_date'] = date('Y-m-d h:i:s',strtotime($communication_data['added_date'].' -7 days'));
			$communication_data['to_date'] = $communication_data['added_date'];
		}
		
		if(!empty($post['user_base_list_id']))
		{
			$communication_data['user_base_list_id'] = $post['user_base_list_id'];
		}

		$communication_data['email_template_id'] =$email_template_id;
		$communication_data['email_count'] =0;
		$communication_data['notification_count'] = 0;
		$communication_data['recent_communication_unique_id'] = $this->_generate_key();

		$template_data = array();
		if($email_template['template_name'] !== 'custom-notification')
		{
			if($email_template_id==3){
				$post['affiliate_master_id']=1;
			}
			$except = [0,302,301];//not useing redirect urls 
			if(!in_array($email_template["notification_type"],$except)){
			// if($email_template["notification_type"]!=0 && $email_template["notification_type"]!=302 && $email_template["notification_type"]!=301){
				$function_name =$this->email_template_actions[$email_template["notification_type"]]['detail'];
				$source_id_key =$this->email_template_actions[$email_template["notification_type"]]['source_id_key'];
				if(!empty($post['fixture_id']) && ($category_id==4 ||$category_id==7)){
				$post[$source_id_key]=$post['fixture_id'];
				$this->template_source_id = $post['fixture_id'];
			}
			$template_data = $this->$function_name($post[$source_id_key]);
			}
			//add notification type data
		}
		
		// foreach ($email_template as $key => &$value) {

					if(($email_template['cd_email_template_id'] ==4 || $email_template['cd_email_template_id'] ==11) && !empty($post['season_game_uid'])){
					$season_game_uid = $post['season_game_uid'];
					$this->load->model('season/season_model','season_model');
					$converted_date = get_timezone(strtotime($template_data['season_scheduled_date']),'Y-m-d H:i:s',$this->app_config['timezone'],2);
					$contest_date = $converted_date['date'].' '.'('.$converted_date['tz'].')';

					if($email_template['template_name']=='fixture-delay-info')
					{
						$email_template['email_body'] = str_replace("{{collection_name}} match",$template_data['collection_name'],$email_template['email_body']);
						
						$email_template['message_body'] = str_replace("{{season_scheduled_date}}",$contest_date,$email_template['message_body']);	

						$email_template['message_body'] = str_replace("{{collection_name}}",$template_data['collection_name'],$email_template['message_body']);	
						$email_template['message_body'] = str_replace("{{MINUTES}}",$template_data['delay_minute'],$email_template['message_body']);
					}
					
					$email_template['message_body'] = str_replace("{{home}}",$template_data['home'],$email_template['message_body']);
					$email_template['message_body'] = str_replace("{{away}}",$template_data['away'],$email_template['message_body']);

					$email_template['email_body'] = str_replace("{{home}}",$template_data['home'],$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{away}}",$template_data['away'],$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{season_scheduled_date}}",$contest_date,$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{home_flag}}",$template_data['home_flag'],$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{away_flag}}",$template_data['away_flag'],$email_template['email_body']);
					}
					
					if($email_template['cd_email_template_id'] !=4){
						$email_template['email_body'] = str_replace("{{WEBSITE_URL}}",WEBSITE_URL,$email_template['email_body']);
					}
					$email_template['email_body'] = str_replace("{{BUCKET_URL}}",IMAGE_PATH,$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{SITE_TITLE}}",SITE_TITLE,$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{SITE_URL}}",WEBSITE_URL,$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{WEBSITE_DOMAIN}}",WEBSITE_DOMAIN,$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{year}}",date('Y'),$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{CURRENCY_CODE_HTML}}",CURRENCY_CODE_HTML,$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{user_name}}","Demo",$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{MINUTES}}",$template_data['delay_minute'],$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{collection_name}}","Demo Collection",$email_template['email_body']);
					$recent_communication_detail['email_body'] = str_replace('href="<?php echo ANDROID_APP_LINK; ?>"','href="'.ANDROID_APP_LINK.'"',$recent_communication_detail['email_body']);
					$recent_communication_detail['email_body'] = str_replace('href="<?php echo IOS_APP_LINK; ?>"','href="'.IOS_APP_LINK.'"',$recent_communication_detail['email_body']);

					if(isset($email_template["notification_type"]) && in_array($email_template["notification_type"],[121,131,123,300]))
					{
						$cd_footer = $this->load->view('communication_dashboard/cd_footer','', true);
						$email_template['email_body'] = str_replace("{{Footer}}",$cd_footer,$email_template['email_body']);
					}

					if($email_template["notification_type"] == 121)
					{
						$email_template['email_body'] = str_replace("{{contest_name}}",$template_data['contest_name'],$email_template['email_body']);
					}
					$email_template['email_body'] = str_replace("{{FB_LINK}}",FB_LINK,$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{TWITTER_LINK}}",TWITTER_LINK,$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{INSTAGRAM_LINK}}",INSTAGRAM_LINK,$email_template['email_body']);
					$email_template['email_body'] = str_replace("{{app_name}}",SITE_TITLE,$email_template['email_body']);

					// $email_template['message_body'] = str_replace("{{Date}}",format_date('today','Y-m-d'),$email_template['message_body']);
					$email_template['message_body'] = str_replace("{{SITE_TITLE}}",SITE_TITLE,$email_template['message_body']);

					if(!empty($email_template['cd_email_template_id']) && ($email_template['cd_email_template_id'] == 1 || $email_template['cd_email_template_id'] == 2))
					{ 
						$email_template['email_body'] = str_replace("{{promo_code}}",$refer_row['promo_code'],$email_template['email_body']);
						$email_template['email_body'] = str_replace("{{offer_percentage}}",$refer_row['discount'],$email_template['email_body']);
						$email_template['message_body'] = str_replace("{{promo_code}}",$refer_row['promo_code'],$email_template['message_body']);
						$email_template['message_body'] = str_replace("{{offer_percentage}}",$refer_row['discount'],$email_template['message_body']);
					}
					if(!empty($email_template['cd_email_template_id']) && $email_template['cd_email_template_id'] == 3)
					{
						$refer_row= $this->New_campaign_model->get_single_row('bonus_amount,,real_amount,coin_amount',AFFILIATE_MASTER,array('affiliate_type' => 1));
						$benifit = "";
						$bonus_amount = ($refer_row['bonus_amount'] > 0) ? $refer_row['bonus_amount']." Bonus Cash, ":""; 
						$real_amount = ($refer_row['real_amount'] > 0) ? $refer_row['real_amount']." Real Cash, ":""; 
						$coin_amount = ($refer_row['coin_amount'] > 0) ? $refer_row['coin_amount']." Coins":""; 
						if($this->app_config['int_version']['key_value']==1){
							$benifit = $coin_amount;
						}
						else{
							$benifit = $bonus_amount.$real_amount.$coin_amount;
						}
						$email_template['email_body'] = str_replace("{{referral_benifits}}",$benifit,$email_template['email_body']);
						$email_template['message_body'] = str_replace("{{amount}}",$refer_row['bonus_amount'],$email_template['message_body']);
					}
					
				if($email_template['category_id']>13 && !empty($email_template['message_url'])){
					$email_template['message_body']= $email_template['message_body'].' Visit:'.$email_template['message_url'];
				}

				if(!empty($email_template['cd_email_template_id']) && $email_template['cd_email_template_id'] == 17){
						$email_template['message_url'] = str_replace("{{FRONTEND_BITLY_URL}}",urlencode(WEBSITE_URL.'lobby#'.$post['sports_name']),$email_template['message_url']);
						$email_template['message_body'] = $email_template['message_body'].' Visit:'.$email_template['message_url'];
				}

					$email_template['message_body'] = str_replace("{{MINUTES}}",10,$email_template['message_body']);

					if($email_template["notification_type"] == 121)
					{
						$email_template['message_body'] = str_replace("{{contest_name}}",$template_data['contest_name'],$email_template['message_body']);
						$email_template['message_body'] = str_replace("{{collection_name}}",$template_data['collection_name'],$email_template['message_body']);
					}
					
					$email_template['subject'] = str_replace("{{collection_name}}",'Demo Collection',$email_template['subject']);

				if(!empty($email_template['category_id']) && $email_template['category_id']==12 && $email_template['template_name'] == 'admin-daily-login-earn-coins-notification')
				{
					if(!isset($this->template_source_id))
					{
						$this->template_source_id =    $email_template['notification_type']; //301 for admin daily earn coin notification;
					}
				}

				if(!empty($email_template['category_id']) && $email_template['category_id']==14 && $email_template['template_name'] == 'deal_template')
				{
					if(!isset($this->template_source_id))
					{
						$this->template_source_id =  $post['deal_id'];
					}
					$deal_info = $this->User_segmentation_model->get_single_row('*',DEALS,array('deal_id' =>$post['deal_id'],'status'=>'1'));
					$deal_benifit=0;
					$bonus_amount = ($deal_info['bonus'] > 0) ? $deal_info['bonus']." bonus cash, ":""; 
					$real_amount = ($deal_info['cash'] > 0) ? $deal_info['cash']." real cash, ":""; 
					$coin_amount = ($deal_info['coin'] > 0  && $this->app_config['allow_coin_system']['key_value']==1) ? $deal_info['coin']." coins  ":""; 
					if($this->app_config['int_version']['key_value']==1){
					$deal_benifit = $coin_amount;
					}
					else{
					$deal_benifit = $bonus_amount.$real_amount.$coin_amount;
					}
					$deal_benifit = substr($deal_benifit,0,-2);

					$email_template['message_body'] = str_replace("{{amount}}",$deal_info['amount'],$email_template['message_body']);
					$email_template['message_body'] = str_replace("{{deal_benifit}}",$deal_benifit,$email_template['message_body']);
				}

				if(!empty($email_template['category_id']) && $email_template['category_id']==15 && in_array($email_template['template_name'],['new_promocode_template','contest_join_promocode','deposit_promocode','deposit_range_promocode','first_deposit_promocode']))
				{
					if(!isset($this->template_source_id))
					{
						$this->template_source_id =  $post['promoCodeId'];
					}
					$promocode_info = $this->User_segmentation_model->get_single_row('*',PROMO_CODE,array('promo_code_id' =>$post['promoCodeId'],'status'=>'1'));
					$prmocode_benifit = 0;
					$cash_type = '';
					$benefit_cap = '.';
					$deposit_range = '';

					if($promocode_info['cash_type']==0)
					{
						$cash_type="bonus cash";
					}
					elseif($promocode_info['cash_type']==1)
					{
						// $cash_type = $this->app_config['currency_code']['key_value'];
						$cash_type = 'real cash';
					}

					if($promocode_info['value_type']==0)
					{
						$prmocode_benifit = $promocode_info['discount'].' '.$cash_type;
					}
					elseif($promocode_info['value_type']==1)
					{
						$prmocode_benifit = $promocode_info['discount'].'% '.$cash_type;
					}

					if($promocode_info['value_type']==1 && $promocode_info['benefit_cap']>0)
					{
						$benefit_cap = " max upto ".$promocode_info['benefit_cap'].' '.$cash_type.'.';
					}
	
					if($email_template['template_name']=='deposit_range_promocode' && $promocode_info['min_amount']!=null && $promocode_info['max_amount']!=null && $promocode_info['min_amount']!=0 && $promocode_info['max_amount']!=0)
					{
						$deposit_range = ' on '.$this->app_config['currency_code']['key_value'].$promocode_info['min_amount'].'-'.$this->app_config['currency_code']['key_value'].$promocode_info['max_amount'];
					}

					$email_template['message_body'] = str_replace("{{new_promocode}}",$promocode_info['promo_code'],$email_template['message_body']);
					$email_template['message_body'] = str_replace("{{promocode_benifit}}",$prmocode_benifit,$email_template['message_body']);
					$email_template['message_body'] = str_replace("{{capping_text}}",$benefit_cap,$email_template['message_body']);
					$email_template['message_body'] = str_replace("{{deposit_range}}",$deposit_range,$email_template['message_body']);
				}
				
		
		if(!empty($post['sports_id']) && !empty($post['sports_name'])){
		$template_data['sports_id'] = 	$post['sports_id'];	
		$template_data['sports_name'] = 	$post['sports_name'];	
		}
		$this->load->helper("cron");

		$cd_balance_deduct_history = array();
		$device_ids =array();
		$recent_communication_id=0;
		foreach ($chunks as $key1 => $chunk) {
			$push_notification_data = array();
			$temp_array= array();
			$notification_list = array();
			foreach ($chunk as $key2 => $email) 
			{
				$deduct_history_change = array();
				if($is_email && !empty($email['email']))
				{

					$content = array("subject" => $email_template['subject'],
					"email_body" => $email_template['email_body'],
					"user_name" => $email['user_name'],
					"template_data" => $template_data ,
					'user_id' => $email['user_id'],
					);

                    $email_content 				= array();
                    if(getenv('CD_TEST_MODE')==1)
					{
						 $email_content['email'] = TEST_EMAILS;
					}
					else
					{
                    	$email_content['email'] 			= $email['email'];
					}
                    $email_content['subject'] 			= $email_template['subject'];
                    $email_content['user_name']			= "";
                    $email_content['content'] 			= $content;
                    $email_content['notification_type'] = $email_template['notification_type'];
                    $temp_array[] = $email_content;
                    $communication_data['email_content'] = $email_template['email_body'];

                    $communication_data['email_count']++;

                    if(!empty($email['email']))
                    {
                    	$deduct_history_change['email_id'] = $email['email'];
                    }
				}
				else
				{
					$deduct_history_change['email_id'] = null;
				}
				if($is_notification && !empty($email['device_ids']))
				{
					$template_data['image'] = IMAGE_PATH.'upload/cd/push_header/'.$email_template['header_image'];
					if(!empty($post["custom_notification_header_image"]))
					{
						$template_data['image'] = IMAGE_PATH.'upload/cd/push_header/'.$post["custom_notification_header_image"];
					}

					$template_data['body_image'] = $email_template['body_image'];
					if(!empty($post["custom_notification_body_image"]))
					{
						$template_data['body_image'] = $post["custom_notification_body_image"];
						$template_data['ios_body_image'] = IMAGE_PATH.CD_PUSH_BODY_DIR.$post["custom_notification_body_image"];
					}
					$template_data['redirect_to'] = $email_template['redirect_to'];
						$content = array(
							"user_name" => $email['user_name'],
							"template_data" => $template_data,
							'user_id' => $email['user_id'],
							'SITE_TITLE' => SITE_TITLE,
							'offer_percentage'=> 0,
							'amount' => 0,
							'contest_name'=> '',
							'home' => '',
							'away' => '',
							'device_id' => $email['device_id'],
							'device_type' => $email['device_type']
						);
						if($email_template['template_name'] == 'custom-notification')
						{
							$communication_data['notification_content'] = $post["custom_notification_text"];
							$content['custom_notification_subject'] = $post["custom_notification_subject"];
							$content['custom_notification_text'] = $post["custom_notification_text"];
							$content['template_data']['custom_notification_landing_page'] = $post["custom_notification_landing_page"];
						}
						else{
							if($post["custom_notification_text"]!='' || $post["custom_notification_text"]!='' || $post["custom_notification_landing_page"]!=''){
								$content['notification_subject'] = $post["custom_notification_subject"];
								$content['notification_text'] = $post["custom_notification_text"];
								$content['template_data']['custom_notification_landing_page'] = $post["custom_notification_landing_page"];
								$content['template_data']['notification_landing_page'] = $post["custom_notification_landing_page"];
							}
							else{
								$except = [434,435];//not useing redirect urls 
								if(!in_array($email_template["notification_type"],$except)){
								$noti_function_name =$this->email_template_actions[$email_template["notification_type"]]['notification_content'];
								$content  = $this->$noti_function_name($content ,$email_template);
								}
								if(($email_template['notification_type']==0 || $email_template['notification_type']==302) && $email_template['message_type']==2)
								{
								$communication_data['notification_content'] = $content['custom_notification_text'];
								}
								else
								{
								$communication_data['notification_content'] = $notification_desc["message"];
								$content['notification_text'] = $email_template['message_body'];
								$content['notification_subject'] = $email_template['subject'];
								}
							}
						}
						$notification = array();
						$notification['notification_type']        = $email_template['notification_type'];
						$notification['source_id']                = $email_template['cd_email_template_id'];
						$notification['user_id']                  = (int)$email['user_id'];
						$notification['notification_status']      = '1';
						$notification['content']                  = $content;
						$notification['added_date']               = $current_date;
						$notification['modified_date']            = $current_date;
						$notification_list[] = $notification;

						$unique_device_ids = explode(',',$email['device_ids']);
						$u_device_types = explode(',',$email['device_types']);

						if(!empty($unique_device_ids))
						{
							foreach($unique_device_ids as $d_key => $d_value) 
							{
								if(!empty($d_value) && !in_array($d_value,$device_ids))
								{
									$notification['device_details'][] =array('device_id' => $d_value,
																			'device_type' => $u_device_types[$d_key]) ;

									$device_ids[] = $d_value;
								}
							}
							$notification['redirect_to'] = $email_template['redirect_to'];		
							$push_notification_data[] = $notification;

							$communication_data['notification_count']++;
						}
				} 
				if(!empty($deduct_history_change))
				{
					$deduct_history_change['added_date'] = $current_date;
					$cd_balance_deduct_history[] = $deduct_history_change;
				}
			}
		
			$communication_data['source_id'] = 0;
			if(!empty($this->template_source_id))
			{
				$communication_data['source_id'] =$this->template_source_id;
			}

			$this->db->trans_start();
			if(isset($post['recent_communication_id']) && $post['recent_communication_id']!='')
			{
				$recent_communication_id = $post['recent_communication_id'];
				$communication_data['recent_communication_id'] = $post['recent_communication_id'];
			}
			if($recent_communication_id==0)
			{
				$recent_communication_id = $this->New_campaign_model->insert_recent_communication($communication_data);
			}
			else{
				$this->New_campaign_model->replace_recent_communication($communication_data);
			}
			if(!empty($cd_balance_deduct_history))
			{
				foreach ($cd_balance_deduct_history as $key => &$value) {
					$value['recent_communication_id'] = $recent_communication_id;
				}
		    	$this->New_campaign_model->add_balance_history($cd_balance_deduct_history);
			}
		    if(!empty($notification_list) && ($email_template['message_type']==0 || $email_template['message_type']==2))
			{
				if(!empty($push_notification_data))
				{
					$this->load->helper('queue_helper');
					//$push_notification_data = $this->unique_key($push_notification_data,'device_id');
					$new_push_notification_data['push_notification_data'] = $push_notification_data;
					$new_push_notification_data['recent_communication_unique_id'] = $communication_data['recent_communication_unique_id'];
					if(!empty($post['noti_schedule']) && !empty($post['schedule_date']) && $post['noti_schedule']==2)
					{
						$custom_data = [
							'req_data' => $post,
						];
						$data = ['custom_data'=>json_encode($custom_data),"noti_schedule"=>$post['noti_schedule'],"schedule_date"=>$post['schedule_date']];
						$update = $this->New_campaign_model->update_recent_communication($data,['recent_communication_id'=>$recent_communication_id]);
						$this->load->helper('queue_helper');
						$timediff = get_miliseconds(format_date(),$post['schedule_date']);
						$server_name = get_server_host_name();
						put_into_delayed_q($new_push_notification_data,CD_SCHEDULED_PUSH_QUEUE,$timediff,'scheduled_noti_exchange');
					}
					else{
						$this->load->helper('queue_helper');
						add_data_in_queue($new_push_notification_data, CD_NORMAL_PUSH_QUEUE);
					}
				}
			}
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE)
			{
			    $this->db->trans_rollback();
			    $this->api_response_arry['response_code'] 	= 500;
				$this->api_response_arry['data']  			= array();
				$this->api_response_arry['global_error']    = "Something went Wrong.";
				$this->api_response();
			}
			else
			{
			        $this->db->trans_commit();
			}

			if(!empty($temp_array) && $email_template['message_type']==0)
			{
				// print_r($temp_array[0]['content']['template_data']);exit;
				$email_tracking_url='';
				if($email_template["notification_type"]!=0){
				$email_url_function_name =$this->email_template_actions[$email_template["notification_type"]]['email_tracking_url'];
				$template_data['recent_communication_unique_id'] = $communication_data['recent_communication_unique_id'];
				$url_for_tracking= $this->$email_url_function_name($template_data);
				$email_tracking_url = $this->get_short_url($url_for_tracking);
				}
				foreach($temp_array as $key=>&$value){
				$value['content']['email_body'] = str_replace("{{FRONTEND_BITLY_URL}}",$email_tracking_url,$value['content']['email_body']);

				if($email_template["notification_type"]==121)
				{
					$value['content']['email_body'] = str_replace("{{CONTEST_URL}}",$url_for_tracking,$value['content']['email_body']);
				}
					$value['recent_communication_id'] = $recent_communication_id;
					$value['recent_communication_unique_id'] = $communication_data['recent_communication_unique_id'];
					$value['tracking_url'] = $email_tracking_url;
					if($category_id==4){
						$input_data = $value['content']['template_data'];
						$match_str =strtolower($input_data['home']).'-vs-'.strtolower($input_data['away']).'-'.date('d-m-Y',strtotime($input_data['season_scheduled_date'])).'?rcuid='.$value['recent_communication_unique_id'];
						$contest_url = WEBSITE_URL.$input_data['sports_name'].'/contest-listing/'.$input_data['collection_master_id'].'/'.$match_str;
						$value['content']['email_body'] = str_replace("{{WEBSITE_URL}}",$contest_url,$value['content']['email_body']);
						$value['content']['email_body'] = str_replace("{{user_name}}",$value['content']['user_name'],$email_template['email_body']);
					}
				}
				$this->load->helper('queue_helper');
				add_data_in_queue($temp_array, CD_BULK_EMAIL_QUEUE);
			}
		}
		if(!empty($recent_communication_id))
		{
		$this->db->update(CD_RECENT_COMMUNICATION, array('notification_count'=>$communication_data['notification_count']), array('recent_communication_id' => $recent_communication_id));
		}
		$data_arr = array(
                        'email_count' => $communication_data['email_count'],
                        'notification_count' => $communication_data['notification_count'],   // 0: real amount
                                                );

		$this->New_campaign_model->update_sent_count($data_arr);

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= array();
		$this->api_response_arry['message']  		= "The selected users have been succesfully notified.";
			//die('dfd');
		$this->api_response();
	}

	function unique_key($array,$keyname){

	 $new_array = array();
	 foreach($array as $key=>$value){

	   if(!isset($new_array[$value[$keyname]])){
	     $new_array[$value[$keyname]] = $value;
	   }

	 }
	 $new_array = array_values($new_array);
	 return $new_array;
	}

	public function get_segementation_template_list_post()
	{

		$type = $this->input->post('type');
		$result = $this->New_campaign_model->get_segementation_templates($type);

		foreach ($result as $key => &$value) {
			$value['email_body'] = str_replace("{{BUCKET_URL}}",IMAGE_PATH,$value['email_body']);
			$value['email_body'] = str_replace("{{SITE_TITLE}}",SITE_TITLE,$value['email_body']);
			$value['email_body'] = str_replace("{{WEBSITE_URL}}",WEBSITE_URL,$value['email_body']);
			$value['email_body'] = str_replace("{{SITE_URL}}",WEBSITE_URL,$value['email_body']);
			$value['email_body'] = str_replace("{{WEBSITE_DOMAIN}}",WEBSITE_DOMAIN,$value['email_body']);
			$value['email_body'] = str_replace("{{year}}",date('Y'),$value['email_body']);
			$value['email_body'] = str_replace("{{CURRENCY_CODE_HTML}}",CURRENCY_CODE_HTML,$value['email_body']);
			$value['email_body'] = str_replace("{{user_name}}","Demo",$value['email_body']);
			$value['email_body'] = str_replace("{{MINUTES}}",10,$value['email_body']);
			$value['email_body'] = str_replace("{{collection_name}}","Demo Collection",$value['email_body']);
			$value['email_body'] = str_replace("{{contest_name}}","Demo Contest",$value['email_body']);

			$value['message_body'] = str_replace("{{SITE_TITLE}}",SITE_TITLE,$value['message_body']);
			//$value['message_body'] = str_replace("{{amount}}",10,$value['message_body']);
			//$value['message_body'] = str_replace("{{promo_code}}",'FJDKJF',$value['message_body']);
			//$value['message_body'] = str_replace("{{offer_percentage}}",20,$value['message_body']);
	
			$value['message_body'] = str_replace("{{MINUTES}}",10,$value['message_body']);
			$value['message_body'] = str_replace("{{contest_name}}","Demo Contest",$value['message_body']);
			
			$value['subject'] = str_replace("{{collection_name}}",'Demo Collection',$value['subject']);

		}
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']['result'] = $result;
		$this->api_response();
	}

	public function render_cd_body_post()
	{
		$email_body = $this->input->post('email_body');
		$message_body = $this->input->post('message_body');
		$template_name = $this->input->post('template_name');
		if($template_name == 'admin-refer-a-friend')
		{

			$refer_row= $this->New_campaign_model->get_single_row('bonus_amount,real_amount,coin_amount',AFFILIATE_MASTER,array('affiliate_type' => 1));
			$benifit = "";
			$bonus_amount = ($refer_row['bonus_amount'] > 0) ? $refer_row['bonus_amount']." Bonus Cash, ":""; 
			$real_amount = ($refer_row['real_amount'] > 0) ? $refer_row['real_amount']." Real Cash, ":""; 
			$coin_amount = ($refer_row['coin_amount'] > 0) ? $refer_row['coin_amount']." Coins":""; 
			if($this->app_config['int_version']['key_value']==1){
				$benifit = $coin_amount;
			}
			else{
				$benifit = $bonus_amount.$real_amount.$coin_amount;
			}
			$email_body = str_replace("{{referral_benifits}}",$benifit,$email_body);
			$message_body = str_replace("{{amount}}",$refer_row['bonus_amount'],$message_body);
		}
		
		if($template_name == 'promotion-for-deposit')
		{
			$refer_row= $this->New_campaign_model->get_single_row('discount,promo_code',PROMO_CODE,array('promo_code_id' => $this->input->post('promo_code_id')));
			$email_body = str_replace("{{promo_code}}",$refer_row['promo_code'],$email_body);
			$email_body = str_replace("{{offer_percentage}}",$refer_row['discount'],$email_body);
			$message_body = str_replace("{{promo_code}}",$refer_row['promo_code'],$message_body);
			$message_body = str_replace("{{offer_percentage}}",$refer_row['discount'],$message_body);
		}
		$message_body = str_replace("{{FRONTEND_BITLY_URL}}",FRONTEND_BITLY_URL,$message_body);
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']['email_body'] = $email_body;
		$this->api_response_arry['data']['message_body'] = $message_body;
		$this->api_response();
	}

	/**
	* [get_all_season_schedule description]
	* Summary :- get all seasons/fixtures by league and sports 
	*/
	public function get_all_season_schedule_post($call_from_api=false)
	{
		if($call_from_api)
		{
			$this->form_validation->set_rules('sports_id', 'Sports ID',    'trim|required');
	        if (!$this->form_validation->run()) 
	        {
	            $this->send_validation_errors();
	        }
		}
	       
        $live_fixture = $upcoming_fixture = array();
        $this->load->model("contest/Contest_model");
       	$fixture = $this->Contest_model->get_all_season_schedule();
         if(!empty($fixture)){
            $current_date = format_date();
            
            foreach($fixture as $key => $res){

                $res['home_flag'] = get_image(0,$res['home_flag']);
                $res['away_flag'] = get_image(0,$res['away_flag']);

                $live_date=date('Y-m-d H:i:s',strtotime($res['season_scheduled_date']."-". CONTEST_DISABLE_INTERVAL_MINUTE." minute"));
                if($current_date >= $live_date){
                    $live_fixture[] = $res;
                }
                else{
                    $upcoming_fixture[] = $res;
                }
            }
        }
        $result['total'] = $this->Contest_model->get_all_season_schedule(1);
        $result['result'] = array('live_fixture' => $live_fixture, 'upcoming_fixture' => $upcoming_fixture);

        if($call_from_api)
		{
			$this->api_response_arry['service_name']  = 'get_all_season_schedule';
	        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
	        $this->api_response_arry['data']          = $result;
	        $this->api_response();
		}
		else
		{
			return $result;
		}
      
	}

	function notify_by_selection_counts_post()
	{
		$post= $this->input->post();
		$category_id = $post['email_template_id'];
		$email_template_id = $post['promo_code_id'];

		if($category_id==9){
			$email_template_id = 13;
		}elseif ($category_id==10) {
			$email_template_id = 14;
		}

		$is_email = (!empty($post['email']) && $post['email'])?1:0;
		$is_notification = (!empty($post['notification']) && $post['notification'])?1:0;

		$users_result = $this->get_filter_result_test_post(false);
		$post['user_ids'] = $users_result['user_ids'];

		//send email and notifiction
		$result = $this->New_campaign_model->get_users_by_ids($post['user_ids']);
		//get template

		$email_template = $this->New_campaign_model->get_single_row('*',CD_EMAIL_TEMPLATE,array('cd_email_template_id' => $email_template_id ));

		//get user ids here I have updated template name as currentyly we are not using first deposit promocode so not in use
		if($email_template['template_name'] == "~promotion-for-deposit")
		{
			$user_ids = array_column($result, 'user_id');

			//get user ids with not deposits
			$result = $this->New_campaign_model->get_users_by_ids_first_deposit($user_ids);

			if(empty($result))
			{
				$this->api_response_arry['response_code'] 	= 500;
				$this->api_response_arry['data']  			= array();
				$this->api_response_arry['global_error']    = "No users for first deposit.";
				$this->api_response();
			}
		}


		$notification_desc = $this->New_campaign_model->get_single_row('*',NOTIFICATION_DESCRIPTION,array('notification_type' => $email_template['notification_type']));

		$chunks = array_chunk($result, 999);
		$current_date = format_date();
		
		$message_numbers = array();

		$communication_data = array();

		if($post['all_user'] == '1')
		{
			$communication_data['user_details'] ="All user";
			$communication_data['userbase'] =1;
		}
		else
		{
			$communication_data['user_details'] =$email_template['display_label'];
			if($post['signup'] == '1')
			{
				$communication_data['userbase'] =3;
			}
			elseif($post['login'] == '1')
			{
				$communication_data['userbase'] =2;
			}
			elseif($post['non_login'] == '1')
			{
				$communication_data['userbase'] =5;
			}
		}

		$communication_data['email_template_id'] =$email_template_id;
		$communication_data['email_count'] =0;
		
		$communication_data['notification_count'] =0;

		$this->load->helper("cron");
		foreach ($chunks as $key1 => $chunk) {

			$temp_array= array();
			$notification_list = array();
			foreach ($chunk as $key2 => $email) 
			{
				if($is_email)
				{
                    $communication_data['email_count']++;
				}
				
				if($is_notification)
				{
					$communication_data['notification_count']++;
				} 
			}

		}

		//save communication data
		$communication_data['added_date'] = format_date();

		if(!empty($post['from_date']))
		{
			$communication_data['from_date'] = $post['from_date'];
		}

		if(!empty($post['to_date']))
		{
			$communication_data['to_date'] = $post['to_date'];
		}

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $communication_data;
		$this->api_response_arry['message']  		= "";
		$this->api_response();
	}

	public function get_deals_list_post()
	{
		$filters = $this->input->post();
		$result = $this->New_campaign_model->get_delas_list($filters);

		$this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']  			= $result;
		$this->api_response();
	}

	

}