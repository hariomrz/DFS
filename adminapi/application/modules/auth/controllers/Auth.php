<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Auth extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
		
		$this->load->model('Auth_model');
		//Do your magic here
	}

	function get_master_setting()
	{
		//get module setting
        $modules_data = $this->Auth_model->get_all_table_data("module_setting_id,name,status",MODULE_SETTING);

        if(!empty($modules_data))
        {
			$config_name = array(
				"allow_coin"=>"allow_coin_system",
				"allow_prediction"=>"allow_prediction_system",
				"allow_open_predictor"=>"allow_open_predictor",
				"allow_fixed_open_predictor"=>"allow_fixed_open_predictor",
			);
            foreach($modules_data as $module)
            {
				if(!$module['status'] || !isset($config_name[$module['name']]) || !$this->app_config[$config_name[$module['name']]]['key_value']){
				$data_arr[$module['name']] = 0;
				}else{
				$data_arr[$module['name']] = 1;
				}
			}
			if(!$data_arr['allow_coin'])
			{
				$data_arr['allow_prediction']=$data_arr['allow_open_predictor']=$data_arr['allow_fixed_open_predictor']=0;
			}
		}

		$this->load->model('setting/Setting_model');
		$self_exclusion = array("allow_self_exclusion");  
		$self_exclusion_data = $this->Setting_model->get_app_config_data($self_exclusion);
		$data_arr['allow_self_exclusion'] =  0;
		if(isset($self_exclusion_data[0])) {
			$data_arr['allow_self_exclusion'] = $self_exclusion_data[0]['key_value'];
		}

		$data_arr['allow_private_contest'] = 0;
		if (isset($this->app_config['allow_private_contest']))
		{
			if ($this->app_config['allow_private_contest']['key_value'] == "1" || $this->app_config['allow_private_contest']['key_value'] == "2")
			{
				$data_arr['allow_private_contest'] = $this->app_config['allow_private_contest']['key_value'];
			}
		}

		$data_arr['allow_private_contest'] = 0;
		if (isset($this->app_config['allow_private_contest']))
		{
			if ($this->app_config['allow_private_contest']['key_value'] == "1" || $this->app_config['allow_private_contest']['key_value'] == "2")
			{
				$data_arr['allow_private_contest'] = $this->app_config['allow_private_contest']['key_value'];
			}
		}

		$data_arr['lf_private_contest'] = 0;
        if (isset($this->app_config['lf_private_contest']))
        {
            if ($this->app_config['lf_private_contest']['key_value'] == 1 || $this->app_config['lf_private_contest']['key_value'] == 2)
            {
                $data_arr['lf_private_contest'] = $this->app_config['lf_private_contest']['key_value'];
            }
        }

		$data_arr['asf'] = isset($this->app_config['allow_stock_fantasy']['key_value']) ? $this->app_config['allow_stock_fantasy']['key_value'] : 0;
		$data_arr['allow_equity'] = isset($this->app_config['allow_equity']['key_value']) ? $this->app_config['allow_equity']['key_value'] : 0;
		$data_arr['allow_stock_predict'] = isset($this->app_config['allow_stock_predict']['key_value']) ? $this->app_config['allow_stock_predict']['key_value'] : 0;
		$data_arr['allow_live_stock_fantasy'] = isset($this->app_config['allow_live_stock_fantasy']['key_value']) ? $this->app_config['allow_live_stock_fantasy']['key_value'] : 0;
		
		$data_arr['login_flow'] 			= LOGIN_FLOW;
		$data_arr['auto_kyc_enable'] 	= isset($this->app_config['auto_kyc'])?$this->app_config['auto_kyc']['key_value']:0;
		$data_arr['allow_auto_withdrawal'] 	= isset($this->app_config['auto_withdrawal'])?$this->app_config['auto_withdrawal']['key_value']:0;
		return $data_arr;
       
	}

	private function get_role_module_access($data){
		//print_r($data);exit;
		if(isset($data['access_list']) && $data['access_list']=='null'){
			return get_admin_menu_keys($this->app_config);
		}else{
			return json_decode($data['access_list']);
		}
		
	}

	/**
     * App master data used for get application settings
     * @param
     * @return json array
     */
    public function get_app_master_list_post() {
		// print_r($this->app_config['site_title']['key_value']);exit;
    	$post_data = $this->post();

        $data_arr = array();
        $data_arr['currency_code'] = CURRENCY_CODE;
        $data_arr['int_version'] = INT_VERSION;
		$data_arr['coin_only'] = COIN_ONLY;
        $data_arr['bonus_percentage'] = MAX_BONUS_PERCEN_USE;
        $data_arr['min_withdrawal_amount'] = isset($this->app_config['min_withdrawl'])?$this->app_config['min_withdrawl']['key_value']:0;
        $data_arr['max_withdrawal_amount'] = isset($this->app_config['max_withdrawl'])?$this->app_config['max_withdrawl']['key_value']:0;
        $data_arr['allow_prediction'] = isset($this->app_config['allow_prediction_system'])?$this->app_config['allow_prediction_system']['key_value']:0;
		$data_arr['auto_withdrawal_limit'] = !empty($this->app_config['auto_withdrawal']['custom_data']['auto_withdrawal_limit'])?$this->app_config['auto_withdrawal']['custom_data']['auto_withdrawal_limit']:0;
        $data_arr['pl_allow'] = isset($this->app_config['pl_allow'])?$this->app_config['pl_allow']['key_value']:0;
        $data_arr['pl_version'] = isset($this->app_config['pl_allow']['custom_data']['version'])?$this->app_config['pl_allow']['custom_data']['version']:'v1';
		$data_arr['a_ref_leaderboard'] = !empty($this->app_config['allow_referral_leaderboard'])?$this->app_config['allow_referral_leaderboard']['key_value']:0;
        $sub_module_data = $this->get_submodule_settings();
		$module_data = $this->get_master_setting();
		$data_arr = array_merge($data_arr,array_merge($module_data,$sub_module_data));
		//ALLOW_NETWORK_FANTASY -> netwrork fantasy setting on off
		
		$data_arr['allow_dfs'] = isset($this->app_config['allow_dfs'])?$this->app_config['allow_dfs']['key_value']:0;
		$data_arr['dfs_auto_publish'] = 0;
		if($data_arr['allow_dfs'] == 1){
			$data_arr['dfs_auto_publish'] = isset($this->app_config['allow_dfs']['custom_data']['auto_publish']) ? $this->app_config['allow_dfs']['custom_data']['auto_publish'] : 0;
		}

		$data_arr['allow_picks'] = isset($this->app_config['allow_picks'])?$this->app_config['allow_picks']['key_value']:0;
		
        $data_arr['allow_network_fantasy'] = ALLOW_NETWORK_FANTASY;
		$data_arr['allow_spin'] = isset($this->app_config['allow_spin'])?$this->app_config['allow_spin']['key_value']:0;

		$affiliate_module = isset($this->app_config['affiliate_module'])?$this->app_config['affiliate_module']['key_value']:0;
        
		$data_arr['allow_affiliate'] = $affiliate_module;

		$allow_site_rake_commission = $this->app_config['affiliate_module']['custom_data']['site_commission'];

		$data_arr['allow_affiliate_commssion'] = $allow_site_rake_commission;

		$allow_distributor =  isset($this->app_config['allow_distributor'])?$this->app_config['allow_distributor']['key_value']:0;
		$data_arr['allow_distributor'] = $allow_distributor;
        
        $allow_free_to_play =  isset($this->app_config['allow_free_to_play'])?$this->app_config['allow_free_to_play']['key_value']:0;
		$data_arr['allow_free_to_play'] = $allow_free_to_play;
        
        $allow_pickem_tournament = isset($this->app_config['allow_pickem_tournament'])?$this->app_config['allow_pickem_tournament']['key_value']:0;
		$data_arr['allow_pickem_tournament'] = $allow_pickem_tournament;

        $allow_dfs_tournament =  isset($this->app_config['allow_dfs_tournament'])?$this->app_config['allow_dfs_tournament']['key_value']:0;
		$data_arr['allow_dfs_tournament'] = $allow_dfs_tournament;
		
		$allow_reverse_contest =  isset($this->app_config['allow_reverse_contest'])?$this->app_config['allow_reverse_contest']['key_value']:0;
		$data_arr['allow_reverse_contest'] = $allow_reverse_contest;
		
		$allow_multigame =  isset($this->app_config['allow_multigame'])?$this->app_config['allow_multigame']['key_value']:0;
		$data_arr['allow_multigame'] = $allow_multigame;

		if($data_arr['allow_coin'] == 1) {
			$data_arr['allow_xp_point'] = isset($this->app_config['allow_xp_point'])?$this->app_config['allow_xp_point']['key_value']:0;
		} else {
			$data_arr['allow_xp_point'] = 0;
		}		
        
		$data_arr['allow_2nd_inning'] = isset($this->app_config['allow_2nd_inning'])?$this->app_config['allow_2nd_inning']['key_value']:0;

		$hub_list= $this->Auth_model->get_sports_hub('en');
		$data_arr['hub_list'] = $this->filter_hub_data($hub_list,$module_data);

        $this->load->model("common/Common_model");
        $data_arr['sports_list'] = $this->Common_model->get_all_sport($post_data);
        $data_arr['default_sport'] = DEFAULT_SPORTS_ID;

        $this->load->config('vconfig');
		$data_arr['language_list'] = $this->config->item('language_list');
		
		$data_arr['allow_scratchwin'] = isset($this->app_config['allow_scratchwin'])?$this->app_config['allow_scratchwin']['key_value']:0;
		$data_arr['is_ga4'] = isset($this->app_config['is_ga4'])?$this->app_config['is_ga4']['key_value']:0;
		
		$data_arr['allow_gst'] = isset($this->app_config['allow_gst'])?$this->app_config['allow_gst']['key_value']:0;
		$data_arr['allow_gst_type'] = isset($this->app_config['allow_gst'])?$this->app_config['allow_gst']['custom_data']['type']:0;
		$data_arr['allow_tds'] = isset($this->app_config['allow_tds'])?$this->app_config['allow_tds']['key_value']:0; 
		$data_arr['allow_deal'] = isset($this->app_config['allow_deal'])?$this->app_config['allow_deal']['key_value']:0;
		$data_arr['site_title'] = isset($this->app_config['site_title'])?$this->app_config['site_title']['key_value']:'';
		$data_arr['allow_buy_coin'] = (isset($this->app_config['allow_buy_coin']) && $data_arr['allow_coin']==1)?$this->app_config['allow_buy_coin']['key_value']:0;
		$data_arr['booster'] = isset($this->app_config['allow_booster'])?$this->app_config['allow_booster']['key_value']:0;
		$data_arr['bench_player'] = isset($this->app_config['bench_player'])?$this->app_config['bench_player']['key_value']:0;
		 
		$data_arr['tds_india'] = isset($this->app_config['allow_tds']['custom_data']['indian']) ? $this->app_config['allow_tds']['custom_data']['indian'] : 0;

	
		$allow_score_stats =  isset($this->app_config['allow_score_stats'])?$this->app_config['allow_score_stats']['key_value']:0;
		//leaderboard
		$data_arr['leaderboard'] = $this->get_leaderboard_type_list();
		//subscription module
		$allow_subscription =  ($data_arr['allow_coin']==1 && isset($this->app_config['allow_subscription']))?$this->app_config['allow_subscription']['key_value']:0;
		$data_arr['allow_subscription'] = $allow_subscription;

		$data_arr['allow_rookie_contest'] = isset($this->app_config['allow_rookie_contest'])?$this->app_config['allow_rookie_contest']['key_value']:0;
		
		$data_arr['allow_quiz'] = isset($this->app_config['allow_quiz'])?$this->app_config['allow_quiz']['key_value']:0;
		$data_arr['allow_stock_predict'] = isset($this->app_config['allow_stock_predict'])?$this->app_config['allow_stock_predict']['key_value']:0;

		$data_arr['h2h_challenge'] = isset($this->app_config['h2h_challenge'])?$this->app_config['h2h_challenge']['key_value']:0;
		$data_arr['h2h_group_id'] = isset($this->app_config['h2h_challenge']['custom_data']['group_id']) ? $this->app_config['h2h_challenge']['custom_data']['group_id'] : 0;

		$data_arr['a_crypto'] = isset($this->app_config['allow_crypto'])?$this->app_config['allow_crypto']['key_value']:0;
		$data_arr['allow_lf'] = isset($this->app_config['allow_livefantasy']) ? $this->app_config['allow_livefantasy']['key_value']:0;

		$data_arr['allow_social'] = isset($this->app_config['allow_social'])?$this->app_config['allow_social']['key_value']:0;
		$data_arr['a_btcpay'] = isset($this->app_config['allow_btcpay'])?$this->app_config['allow_btcpay']['key_value']:0;

		$data_arr['allow_lsf'] = isset($this->app_config['allow_live_stock_fantasy'])?$this->app_config['allow_live_stock_fantasy']['key_value']:0;

        $this->api_response_arry['data'] = $data_arr;
		$data_arr['new_affiliate'] = isset($this->app_config['new_affiliate'])?$this->app_config['new_affiliate']['key_value']:0;
		
		$data_arr['allow_auto_withdrawal'] = isset($this->app_config['auto_withdrawal'])?$this->app_config['auto_withdrawal']['key_value']:0;
		$data_arr['allow_picks'] = isset($this->app_config['allow_picks']) ? $this->app_config['allow_picks']['key_value']:0;


		 $android_app_key = isset($this->app_config['android_app']['key_value'])?$this->app_config['android_app']['key_value']:0;
		 $ios_app_key = isset($this->app_config['ios_app']['key_value'])?$this->app_config['ios_app']['key_value']:0;

		  if($android_app_key == 1 || $ios_app_key == 1)
        {
            $data_arr['allow_app_qr'] = 1;
        }else{
			$data_arr['allow_app_qr'] = 0;
		}

		$data_arr['allow_aadhar'] = isset($this->app_config['allow_aadhar'])?$this->app_config['allow_aadhar']['key_value']:0;
		$data_arr['allow_bank'] = isset($this->app_config['allow_bank_flow'])?$this->app_config['allow_bank_flow']['key_value']:0;
		$data_arr['allow_pan'] = isset($this->app_config['allow_pan_flow'])?$this->app_config['allow_pan_flow']['key_value']:0;

		$data_arr['allow_bs'] = isset($this->app_config['allow_bs'])?$this->app_config['allow_bs']['key_value']:0;
		
		$data_arr['prediction_feed']['feed_server'] = SPORT_PREDICTOR_FEED;
		$data_arr['prediction_feed']['feed_client'] = 0;
		$data_arr['a_offpg'] = isset($this->app_config['allow_offpg'])?$this->app_config['allow_offpg']['key_value']:0;
		// if(isset($data_arr['allow_prediction']) && $data_arr['allow_prediction'] == 1){
		// 	$data_arr['prediction_feed']['feed_client'] = $this->app_config['allow_prediction_system']['custom_data']['allow_feed'];
		// }
		$allow_props =  isset($this->app_config['allow_props'])?$this->app_config['allow_props']['key_value']:0;
		$data_arr['allow_props'] = $allow_props;

		$allow_opinion_trade =  isset($this->app_config['opinion_trade'])?$this->app_config['opinion_trade']['key_value']:0;
		$data_arr['allow_opinion_trade'] = $allow_opinion_trade;
		$tz_arr = get_timezone();
        $data_arr['timezone'] = $tz_arr[$this->app_config['timezone']['key_value']];
		$this->api_response_arry['data'] = $data_arr;
        $this->api_response();
    }


	/**
	 * [dologin description]
	 * @MethodName dologin
	 * @Summary This function used to login user into the system
	 * @return   boolean
	 */
	public function dologin_post()
	{
		$post_data = $this->input->post();
		$this->form_validation->set_rules('email', 'Login', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');
		//$this->form_validation->set_rules('type', 'Type', 'trim|required');
		//$type = isset($post_data['type'])?$post_data['type']:'login';
		$type = isset($post_data['type'])?$post_data['type']:'';


		if(!empty($type) && $type == 'otp'){
			$this->form_validation->set_rules('otp', 'Otp', 'trim|required');
			$this->form_validation->set_rules('hash', 'Hash', 'trim|required');
		}


		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$data = $this->Auth_model->admin_login($this->input->post('email'), $this->input->post('password'));
		
		if ($data != NULL)
		{
			if ($data['status'] == 0) {
				
				$this->api_response_arry['response_code'] 	= 500;
				$this->api_response_arry['error']  			= array('email'=>'Your account is inactive!');
				$this->api_response();	
			}

			if(!empty($type) && ($type == 'login') && ($data['two_fa'])){
				$res_arry = $this->send_otp($data);
				$res_data = array();
				//$res_data['two_fa']['otp'] = isset($res_arry['otp'])?$res_arry['otp']:'';
				$res_data['two_fa']['hash'] = isset($res_arry['hash'])?$res_arry['hash']:'';
				$res_data['two_fa']['next'] = 'otp';
				$msg = 'OTP sent on your email address is valid for 30 seconds only.';

				$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
				$this->api_response_arry['data'] = $res_data;
				$this->api_response_arry['message'] = $msg;
				$this->api_response();
			}

			if(!empty($type) && ($type == 'otp') && ($data['two_fa'])){
				
				$input_data = array();
				$input_data['type'] = 'd';
				$input_data['otp'] = isset($post_data['otp'])?$post_data['otp']:'';
				$input_data['hash'] = isset($post_data['hash'])?$post_data['hash']:'';
				$input_data['entity_no'] = $post_data['email'];
				$data_hash = generate_verify_otp($input_data);
				if($data_hash['status'] != '200'){
					$this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
					$this->api_response_arry['error']           = $data_hash['message'];
					$this->api_response();
				}
			}
			

			$key = $this->generate_active_login_key($data['admin_id']); //Generate Active Login Key
			$data[AUTH_KEY] = $key;

			$data['admin_privilege'] = json_decode($data['privilege']);
			
			if ($data['admin_privilege']) {
				$data['admin_privilege'] = array_combine(array_keys(array_flip($data['admin_privilege'])), $data['admin_privilege']);	
			}
			
			//$this->seesion_initialization($data); // Initialize User Session
			// if (($this->admin_role == SUBADMIN_ROLE)) 
			// {
			// 	$this->admin_privilege = $this->session->userdata('admin_privilege');
			// 	$this->check_subadmin_acess_privilege();		
			// }

			if( $this->admin_role == DISTRIBUTOR_ROLE || $this->admin_role  == SUB_DISTRIBUTOR_ROLE)
			{
				$this->redirect_after_login = 'affiliates';
			}

			if($data['role']  == MASTER_DISTRIBUTOR_ROLE)
			{
				$this->redirect_after_login = 'distributors';
			} else if( $data['role'] == DISTRIBUTOR_ROLE)
			{
				$this->redirect_after_login = 'distributors';
			}else if( $data['role'] == AGENT_ROLE)
			{
				$this->redirect_after_login = 'distributors';
			} else {
				$this->redirect_after_login = 'dashboard';
			}

			
			//get submodule setting and set in redis cache
			$sub_module_data = $this->get_submodule_settings();
			$module_data = $this->get_master_setting();
			
			$this->api_response_arry['response_code'] 	= 200;
			$this->api_response_arry['error']  			= array();
			$this->api_response_arry['data']  			= array(AUTH_KEY=>$key,'redirect_after_login'=>$this->redirect_after_login);
			
			$this->api_response_arry['data']['module_access'] = $this->get_role_module_access($data);
			$this->api_response_arry['data']['module_setting'] = array_merge($module_data,$sub_module_data);
			$this->api_response_arry['data']['role'] = $data['role'];
			$this->api_response_arry['data']['admin_id'] = $data['admin_id'];
			$this->api_response_arry['data']['createdby'] = $data['created_by'];

			$this->api_response_arry['data']['setting'] = array("int_version"=>INT_VERSION,"coin_only"=>COIN_ONLY,"currency_code"=>CURRENCY_CODE);

			$hub_list= $this->Auth_model->get_sports_hub('en');
			$module_data = $this->get_master_setting();
			$this->api_response_arry['data']['hub_list'] = $this->filter_hub_data($hub_list,$module_data);
			$this->api_response();
		}
		else
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['error']  			= array('email'=>'Incorrect credentials!');
			$this->api_response_arry['message']  			="Incorrect credentials!";
			$this->api_response();
		}
	}

	/**
	 * [logout description]
	 * @MethodName logout
	 * @Summary This function used to logout user from the system
	 * @return   boolean
	 */
	public function logout_post() 
	{
		$key = $this->input->get_request_header(AUTH_KEY);
		//this condition for uc browser lowecase issue
        if(!$key){
        	$key = $this->input->get_request_header(strtolower(AUTH_KEY));
        }
		$this->delete_active_login_key($key);

		$this->admin_id			= "";
		$this->admin_fullname	= "";
		$this->admin_email		= "";
		$this->session->sess_destroy();
		//$this->session->sess_regenerate();
		return TRUE;
	}
	
	public function logout_get()
	{
		$this->logout_post();
		redirect('auth', 'refresh');
	}

	/**
	 * [ reset_password description]
	 * @MethodName  reset_password
	 * @Summary This function used to reset password and send email
	 * @return   boolean
	 */
	public function reset_password_post(){
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$email = $this->input->post('email');

		$check_email = $this->Auth_model->get_single_row('admin_id,email,username,',ADMIN,array('email'=> $email));

		if(empty($check_email)){

			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['error']  			= array('email'=>'This email not exists');
			$this->api_response_arry['message'] = "This email not exists";
			$this->api_response();
		}
		$data = array();

		$password = random_password( $length = 8, $characters = true, $numbers = true, $case_sensitive = true, $hash = true );
		$data['password'] =md5(md5($password));
		$data['admin_id'] = $check_email['admin_id'];

		//Update Password
		$password_update = $this->Auth_model->update_password($data);

		if($password_update){
			/* Send Email Notifications*/

			$content = array(
				"email" => $check_email['email'],
				"username" => $check_email["username"],
				"site_url"  => BASE_APP_PATH."admin/",
				"password"  => $password,
				"post_date" => format_date('today', 'd-M-Y h:i A')
			);

			$email_content                     	= array();
			$email_content['email']            	= $check_email['email'];
			$email_content['username']         	= $check_email["username"];
			$email_content['subject'] 			= $this->lang->line('admin_reset_password_subject');
			$email_content['notification_type'] = '404';
			$email_content['content']           = $content;
			$this->load->helper('queue_helper');
			add_data_in_queue($email_content, 'email');

			$this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
            $this->api_response_arry['message']  	= $this->lang->line('success_reset_password');
            $this->api_response_arry['global_error'] = "";
            $this->api_response();

		}

		$this->api_response_arry['response_code'] 	= 500;
		$this->api_response_arry['global_error'] =  $this->lang->line('invalid_parameter');
		$this->api_response();
	}


	/**
	 * This function used for the opt send to admin login email address
	 * params: email and password
	 * return:json
	 */
	public function send_otp(){
        $post_data = $this->input->post();
        $user_profile   = $this->Auth_model->get_single_row("admin_id,username,email", ADMIN, array('email' => $post_data['email']));
        $user_data = array();
		
		$user_data['user_id'] = $user_profile['admin_id'];
		$user_data['email'] = strtolower($user_profile['email']);
		$user_data['user_name'] = $user_profile['username'];
		
		if(is_null($user_data['user_name']) || $user_data['user_name'] == "") {
			$email_arr = explode("@", $user_data['email']);
			$user_data['user_name'] = isset($email_arr[0]) ? $email_arr[0] : "";
		}

		$input_data['hash'] = '';
        $input_data['type'] = 'e';
		$input_data['entity_no'] = $user_data['email'];
		$data_hash = generate_verify_otp($input_data);
		$otp 	= isset($data_hash['otp'])?$data_hash['otp']:'1234';
		
		$content        = array('otp' => $otp, 'email' => $user_data['email']);
		$notify_data    = array();
		$notify_data['queue_name'] = "email_otp";
		$notify_data['notification_type']           = 133;
		$notify_data['notification_destination']    = 4;
		$notify_data["source_id"]   = 1;
		$notify_data["user_id"]     = $user_data['user_id'];
		$notify_data["user_name"]   = $user_data['user_name'];
		$notify_data["to"]          = $user_data["email"];
		$notify_data["added_date"]  = format_date();
		$notify_data["modified_date"] = format_date();
		$notify_data["subject"] = 'Admin login OTP';
		$notify_data["content"] = json_encode($content);
		$this->load->model('notification/Notify_nosql_model');
		$this->Notify_nosql_model->send_notification($notify_data);
		return $data_hash;
    }

	/**
	 * This function used for the resend 2fa code on email address
	 * return: json
	 */
	public function resend_otp_post() {
        $this->form_validation->set_rules('email', $this->lang->line("email"), 'trim|required|valid_email');
        if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
        $post_data  = $this->input->post();
        $res_data = array();
		$res_arry = $this->send_otp($post_data);
		$res_data['otp'] = isset($res_arry['otp'])?$res_arry['otp']:'';
		$res_data['hash'] = isset($res_arry['hash'])?$res_arry['hash']:'';
		$msg = 'OTP sent on your email address is valid for 30 seconds only.';
        
        $this->api_response_arry['data'] = $res_data;
        $this->api_response_arry['message'] = $msg;
        $this->api_response();
    }
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */