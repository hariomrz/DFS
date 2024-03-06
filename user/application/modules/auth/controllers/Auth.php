<?php
/**
 * Auth for user authentication
 * @package Auth
 * @category Auth
 */
class Auth extends Common_Api_Controller {

    function __construct() {
        parent::__construct();
    }

    public function index_get() {
        $this->response(array(config_item('rest_status_field_name') => FALSE), rest_controller::HTTP_NOT_FOUND);
    }

    public function index_post() {
        $this->response(array(config_item('rest_status_field_name') => FALSE), rest_controller::HTTP_NOT_FOUND);
    }

    /**
     * App master data used for get application settings
     * @param
     * @return json array
     */
    public function get_app_version_post() {
        
        $data_arr = array();
        $data_arr['android'] = $data_arr['ios'] = array();
        if(!empty(ANDROID_APP_LINK))
        {
            $data_arr['android'] =array('min_ver'=>ANDROID_MIN_VER, 'current_ver'=>ANDROID_CURRENT_VER, 'app_url'=>ANDROID_APP_LINK, 'msg'=>ANDROID_UPDATE_MSG); 
        }

        if(!empty(IOS_APP_LINK))
        {
            $data_arr['ios'] = array('min_ver'=>IOS_MIN_VER, 'current_ver'=>IOS_CURRENT_VER, 'app_url'=>IOS_APP_LINK, 'msg'=>IOS_UPDATE_MSG);
        }

        $data_arr['bs_a'] = 0;
        $data_arr['bs_fs'] = 0;
        $data_arr['bs_tm'] = '';
        if(isset($this->app_config['allow_bs']) && $this->app_config['allow_bs']['key_value'] == "1"){
            $bs_info = $this->app_config['allow_bs']['custom_data'];
            $data_arr['bs_a'] = 1;
            $data_arr['bs_fs'] = $bs_info['force_loc'];
            $data_arr['bs_tm'] = $bs_info['loc_time'];
        }

        //for upload app data on s3 bucket
        $this->push_s3_data_in_queue("app_version", $data_arr);
        $this->api_response_arry['data'] = $data_arr;
        $this->api_response();
    }
     
    /**
     * App master data used for get application settings
     * @param
     * @return json array
     */
    public function get_app_master_list_post() {
        
        $data_arr = array();
        $data_arr['l_list'] = array_column($this->config->item('app_language_list'),'value');
 /*       $banned_states = $this->get_banned_state("5");
        if (!$banned_states)
        {
            $data_arr['banned_state'] = NULL;
        }
        else
        {
            $final_result = array();
            foreach ($banned_states as $key => $value)
            {
                $final_result[$value['state_id']] = $value['name'];
            }
            $data_arr['banned_state'] = implode(',',$final_result);
        }*/
        $bs_info = $this->app_config['allow_bs']['custom_data'];
        $data_arr['a_country'] = isset($bs_info['country_code_allowed']) ? explode(',',$bs_info['country_code_allowed']): [];
        $data_arr['default_lang'] = DEFAULT_LANG;
        $data_arr['currency'] = $this->app_config['currency_abbr']['key_value'] ? $this->app_config['currency_abbr']['key_value']:'INR';
        $data_arr['int_version'] = INT_VERSION;
        $data_arr['a_st_tg'] = isset($this->app_config['state_tagging']) ? $this->app_config['state_tagging']['key_value'] : 0;
        $data_arr['coin_only'] = COIN_ONLY;
        $data_arr['c_point'] = CAPTAIN_POINT;
        $data_arr['vc_point'] = VICE_CAPTAIN_POINT;

        $data_arr['a_teams'] = ALLOWED_USER_TEAM;
        $data_arr['support_id'] = isset($this->app_config['support_id']) ? $this->app_config['support_id']['key_value']:"";

        $data_arr['login_flow'] = LOGIN_FLOW;
        $data_arr['a_collection'] = 0;
        $data_arr['support_id'] = isset($this->app_config['support_id']) ? $this->app_config['support_id']['key_value']:"";
        $data_arr['auto_kyc_enable'] = isset($this->app_config['auto_kyc']) ? $this->app_config['auto_kyc']['key_value']:0;
        $data_arr['auto_kyc_limit'] = isset($this->app_config['auto_kyc']) ? $this->app_config['auto_kyc']['custom_data']['attempt']:0;
        $data_arr['allow_auto_withdrawal']  = isset($this->app_config['auto_withdrawal'])?$this->app_config['auto_withdrawal']['key_value']:0;
        $data_arr['pg_fee']                 = !empty($this->app_config['auto_withdrawal']['custom_data']['pg_fee'])?$this->app_config['auto_withdrawal']['custom_data']['pg_fee']:0;
        $data_arr['auto_withdrawal_limit'] = !empty($this->app_config['auto_withdrawal']['custom_data']['auto_withdrawal_limit'])?$this->app_config['auto_withdrawal']['custom_data']['auto_withdrawal_limit']:"999999"; // set default withdarwal limit to 1 less 10 lac if user not set it.
        
        $pg_name_arr = array();
        $data_arr['pg'] = array("credit_debit_card" => "","wallet" => "","upi" => "","net_banking" => "");
        if(isset($this->app_config['credit_debit_card']['key_value']) && $this->app_config['credit_debit_card']['key_value'] == "1"){
            $data_arr['pg']['credit_debit_card'] = $this->app_config['credit_debit_card']['custom_data']['gateway'];
            $pg_name_arr[] = "allow_".$data_arr['pg']['credit_debit_card'];
        }
        if(isset($this->app_config['paytm_wallet']['key_value']) && $this->app_config['paytm_wallet']['key_value'] == "1"){
            $data_arr['pg']['wallet'] = $this->app_config['paytm_wallet']['custom_data']['gateway'];
            $pg_name_arr[] = "allow_".$data_arr['pg']['wallet'];
        }
        if(isset($this->app_config['payment_upi']['key_value']) && $this->app_config['payment_upi']['key_value'] == "1"){
            $data_arr['pg']['upi'] = $this->app_config['payment_upi']['custom_data']['gateway'];
            $pg_name_arr[] = "allow_".$data_arr['pg']['upi'];
        }
        if(isset($this->app_config['net_banking']['key_value']) && $this->app_config['net_banking']['key_value'] == "1"){
            $data_arr['pg']['net_banking'] = $this->app_config['net_banking']['custom_data']['gateway'];
            $pg_name_arr[] = "allow_".$data_arr['pg']['net_banking'];
        }

        $this->load->model("auth/Auth_model");
        $pg_list = array();
        if(!empty($pg_name_arr)){
            $pg_list = $this->Auth_model->get_all_table_data(MASTER_PG,"pg_key,title,description,image_name",array("pg_key IN('".implode("', '",array_unique($pg_name_arr))."')"=>NULL));
        }
        $data_arr['pg_list'] = $pg_list;
        
        
        $data_arr['min_deposit'] = isset($this->app_config['min_deposit']['key_value']) ? $this->app_config['min_deposit']['key_value']:5;
        $data_arr['max_deposit'] = isset($this->app_config['max_deposit']['key_value']) ? $this->app_config['max_deposit']['key_value']:0;
        $data_arr['min_withdrawal'] = isset($this->app_config['min_withdrawl'])?$this->app_config['min_withdrawl']['key_value']:0;
        $data_arr['max_withdrawal'] = isset($this->app_config['max_withdrawl'])?$this->app_config['max_withdrawl']['key_value']:0;
        
        
        $data_arr['currency_code']=CURRENCY_CODE;
        $data_arr['app_cache_version'] = isset($this->app_config['app_cache_version'])?$this->app_config['app_cache_version']['key_value']:0;
        
        $data_arr['private_contest'] = 0;
        if (isset($this->app_config['allow_private_contest']))
        {
            if ($this->app_config['allow_private_contest']['key_value'] == 1 || $this->app_config['allow_private_contest']['key_value'] == 2)
            {
                $data_arr['private_contest'] = $this->app_config['allow_private_contest']['key_value'];
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

        $data_arr['a_pan_flow'] = isset($this->app_config['allow_pan_flow'])?$this->app_config['allow_pan_flow']['key_value']:0;
        $data_arr['a_bank_flow'] = isset($this->app_config['allow_bank_flow'])?$this->app_config['allow_bank_flow']['key_value']:0;
        $data_arr['a_aadhar'] = isset($this->app_config['allow_aadhar']['key_value']) && INT_VERSION == 0 ? $this->app_config['allow_aadhar']['key_value']:0;
        $data_arr['adr_mode'] = isset($this->app_config['allow_aadhar']['custom_data']['is_auto_mode']) ? $this->app_config['allow_aadhar']['custom_data']['is_auto_mode']:0;
        $data_arr['adr_deposit'] = isset($this->app_config['allow_aadhar']['custom_data']['deposit']) ? $this->app_config['allow_aadhar']['custom_data']['deposit']:0;
      
        $data_arr['a_deal'] = isset($this->app_config['allow_deal'])?$this->app_config['allow_deal']['key_value']:0;
        $data_arr['a_age_limit'] = isset($this->app_config['allow_age_limit'])?$this->app_config['allow_age_limit']['key_value']:0;
        
        $data_arr['pl_website_id'] = isset($this->app_config['pl_allow']['custom_data']['pl_website_id']) ? $this->app_config['pl_allow']['custom_data']['pl_website_id'] : 0;
        $data_arr['a_guru'] = isset($this->app_config['pl_allow']['custom_data']['allow_guru'])?$this->app_config['pl_allow']['custom_data']['allow_guru']:0;

        $data_arr['a_module'] = isset($this->app_config['affiliate_module'])?$this->app_config['affiliate_module']['key_value']:0;

        $data_arr['a_reverse'] = isset($this->app_config['allow_reverse_contest'])?$this->app_config['allow_reverse_contest']['key_value']:0;
        $data_arr['a_pickem_tournament'] = isset($this->app_config['allow_pickem_tournament'])?$this->app_config['allow_pickem_tournament']['key_value']:0;
        $data_arr['a_dfs'] = isset($this->app_config['allow_dfs'])?$this->app_config['allow_dfs']['key_value']:0;
        $data_arr['a_multigame'] = isset($this->app_config['allow_multigame'])?$this->app_config['allow_multigame']['key_value']:0;
        $data_arr['a_free_to_play'] = isset($this->app_config['allow_free_to_play'])?$this->app_config['allow_free_to_play']['key_value']:0;
        
        $data_arr['a_dfst'] = isset($this->app_config['allow_dfs_tournament'])?$this->app_config['allow_dfs_tournament']['key_value']:0;

        $data_arr['a_stats'] = isset($this->app_config['allow_score_stats'])?$this->app_config['allow_score_stats']['key_value']:0;
        
        $data_arr['max_contest_bonus'] = isset($this->app_config['max_contest_bonus'])?$this->app_config['max_contest_bonus']['key_value']:0;

        $data_arr['a_xp_point'] = isset($this->app_config['allow_xp_point'])?$this->app_config['allow_xp_point']['key_value']:0;
        $data_arr['a_2nd_inning'] = isset($this->app_config['allow_2nd_inning'])?$this->app_config['allow_2nd_inning']['key_value']:0;
        
        $data_arr['a_rookie'] = isset($this->app_config['allow_rookie_contest'])?$this->app_config['allow_rookie_contest']['key_value']:0;
        if($data_arr['a_rookie'])
        {
            $data_arr['rookie_setting'] = $this->app_config['allow_rookie_contest']['custom_data'];
        }
        
        
        if(!empty($this->app_config['allow_hub_icon']))
        {
            $data_arr['hub_icon'] = !empty($this->app_config['allow_hub_icon'])?$this->app_config['allow_hub_icon']['custom_data']['image']:"";
            $data_arr['a_hub_icon'] = !empty($this->app_config['allow_hub_icon'])?$this->app_config['allow_hub_icon']['key_value']:0;

        }

        if(!empty($this->app_config['allow_hub_banner']))
        {
            $data_arr['hub_banner'] = !empty($this->app_config['allow_hub_banner'])?$this->app_config['allow_hub_banner']['custom_data']['image']:"";
            $data_arr['a_hub_banner'] = !empty($this->app_config['allow_hub_banner'])?$this->app_config['allow_hub_banner']['key_value']:0;
        }


        if(!empty($this->app_config['allow_prize_bnr']))
        {
            $data_arr['prize_bnr'] = !empty($this->app_config['allow_prize_bnr'])?$this->app_config['allow_prize_bnr']['custom_data']['image']:"";
            $data_arr['a_prize_bnr'] = !empty($this->app_config['allow_prize_bnr'])?$this->app_config['allow_prize_bnr']['key_value']:0;

        }

        if(!empty($this->app_config['allow_prediction_pool_bnr']))
        {
            $data_arr['prediction_pool_bnr'] = !empty($this->app_config['allow_prediction_pool_bnr'])?$this->app_config['allow_prediction_pool_bnr']['custom_data']['image']:"";
            $data_arr['allow_prediction_pool_bnr'] = !empty($this->app_config['allow_prediction_pool_bnr'])?$this->app_config['allow_prediction_pool_bnr']['key_value']:0;
        }

        if(!empty($this->app_config['allow_prediction_lboard_bnr']))
        {
            $data_arr['prediction_lboard_bnr'] = !empty($this->app_config['allow_prediction_lboard_bnr'])?$this->app_config['allow_prediction_lboard_bnr']['custom_data']['image']:"";
            $data_arr['a_prediction_lboard_bnr'] = !empty($this->app_config['allow_prediction_lboard_bnr'])?$this->app_config['allow_prediction_lboard_bnr']['key_value']:0;

        }

        $data_arr['allow_bonus_cash_expiry'] = isset($this->app_config['allow_bonus_cash_expiry'])?$this->app_config['allow_bonus_cash_expiry']['key_value']:0;
        $data_arr['bonus_expiry_limit'] = isset($this->app_config['bonus_expiry_limit'])?$this->app_config['bonus_expiry_limit']['key_value']:0;
        $data_arr['allow_self_exclusion'] = isset($this->app_config['allow_self_exclusion'])?$this->app_config['allow_self_exclusion']['key_value']:0;

        if(!empty($this->app_config['allow_sports_prediction_bnr']))
        {
            $data_arr['sports_prediction_bnr'] = !empty($this->app_config['allow_sports_prediction_bnr'])?$this->app_config['allow_sports_prediction_bnr']['custom_data']['image']:"";
            $data_arr['a_sports_prediction_bnr'] = !empty($this->app_config['allow_sports_prediction_bnr'])?$this->app_config['allow_sports_prediction_bnr']['key_value']:0;
        }

        if(!empty($this->app_config['allow_dfs_bnr']))
        {
            $data_arr['dfs_bnr'] = !empty($this->app_config['allow_dfs_bnr'])?$this->app_config['allow_dfs_bnr']['custom_data']['image']:"";
            $data_arr['a_dfs_bnr'] = !empty($this->app_config['allow_dfs_bnr'])?$this->app_config['allow_dfs_bnr']['key_value']:0;

        }

        if(!empty($this->app_config['allow_ref_friend_bnr']))
        {
            
            $data_arr['ref_friend_bnr'] = !empty($this->app_config['allow_ref_friend_bnr'])?$this->app_config['allow_ref_friend_bnr']['custom_data']['image']:"";
            $data_arr['a_ref_friend_bnr'] = !empty($this->app_config['allow_ref_friend_bnr'])?$this->app_config['allow_ref_friend_bnr']['key_value']:0;
        }

        $data_arr['app_version'] = array();
        $android_app_key = isset($this->app_config['android_app']['key_value'])?$this->app_config['android_app']['key_value']:0;
        if($android_app_key == 1 && !empty(ANDROID_APP_LINK))
        {
            $data_arr['app_version']['android'] =array('min_ver'=>ANDROID_MIN_VER, 'current_ver'=>ANDROID_CURRENT_VER, 'app_url'=>ANDROID_APP_LINK, 'msg'=>ANDROID_UPDATE_MSG); 
        }

        $ios_app_key = isset($this->app_config['ios_app']['key_value'])?$this->app_config['ios_app']['key_value']:0;
        if($ios_app_key == 1 && !empty(IOS_APP_LINK))
        {
            $data_arr['app_version']['ios'] = array('min_ver'=>IOS_MIN_VER, 'current_ver'=>IOS_CURRENT_VER, 'app_url'=>IOS_APP_LINK, 'msg'=>IOS_UPDATE_MSG);
        }

        $data_arr['a_ref_leaderboard'] = !empty($this->app_config['allow_referral_leaderboard'])?$this->app_config['allow_referral_leaderboard']['key_value']:0;
        $data_arr['allow_fb'] = !empty($this->app_config['FB_Login'])?$this->app_config['FB_Login']['key_value']:0;
        $data_arr['allow_google'] = !empty($this->app_config['G_Login'])?$this->app_config['G_Login']['key_value']:0;
        
        $single_country         = !empty($this->app_config['single_country'])?$this->app_config['single_country']['key_value']:0;
        $phone_code             = !empty($this->app_config['phone_code'])?$this->app_config['phone_code']['key_value']:DEFAULT_PHONE_CODE;
        $country_code           = !empty($this->app_config['country_code'])?$this->app_config['country_code']['key_value']:'IN';
        $country_id             = !empty($this->app_config['country_id'])?$this->app_config['country_id']['key_value']:'101';
        $data_arr['login_data'] = $single_country."_".$phone_code."_".$country_code."_".$country_id;

        $data_arr['a_guru'] = isset($this->app_config['allow_guru'])?$this->app_config['allow_guru']['key_value']:0;

        $this->load->model("fantasy/Fantasy_model");
        $sports_list = $this->Fantasy_model->get_sports_list($data_arr['l_list']);
        
        $data_arr['fantasy_list'] =$sports_list;
        $data_arr['default_sport'] = DEFAULT_SPORTS_ID;

        $this->load->model("auth/Auth_model");
        $featured_l_list = $this->Auth_model->get_featured_league_list($data_arr['l_list']);

         $data_arr['featured_l_list'] = $featured_l_list;

        //banner data
        $this->load->model("auth/Auth_model");
        $banner_data = $this->Auth_model->get_single_row("app_banner_id,banner_title,banner_image,banner_link", APP_BANNER, array('status' => "1"));
        $data_arr['banner'] = $banner_data;
        $data_arr['bg_image'] = FRONT_BG_IMAGE_PATH;
        $data_arr['m_e_p_b'] = !empty($this->app_config['m_e_p_b'])?$this->app_config['m_e_p_b']['key_value']:"0_0_0_0";

        //whats new
        $whats_new = $this->Auth_model->get_single_row("count(id) as total", WHATS_NEW, array('status' => "1"));
        $data_arr['whats_new'] = 0;
        if(!empty($whats_new['total']) && $whats_new['total'] > 0){
            $data_arr['whats_new'] = 1;
        }


        $data_arr['bs_a'] = 0;
        $data_arr['bs_fs'] = 0;
        $data_arr['bs_tm'] = '';
        $data_arr['bs_sa'] = 0;
        if(isset($this->app_config['allow_bs']) && $this->app_config['allow_bs']['key_value'] == "1"){
            $data_arr['bs_a'] = 1;
            $data_arr['bs_fs'] = $bs_info['force_loc'];
            $data_arr['bs_tm'] = $bs_info['loc_time'];
            $data_arr['bs_sa'] = $bs_info['site_access'];
        }


        $data_arr['a_mbl'] = !empty($this->app_config['allow_email_mbl']) ? $this->app_config['allow_email_mbl']['key_value']:0;
        $data_arr['a_eml'] = !empty($this->app_config['allow_mobile_email']) ? $this->app_config['allow_mobile_email']['key_value']:0;

        $data_arr['a_mt'] = isset($this->app_config['allow_multi_team']) ? $this->app_config['allow_multi_team']['key_value']:0;
        
        $data_arr['new_affiliate'] = isset($this->app_config['new_affiliate'])?$this->app_config['new_affiliate']['key_value']:0;

       

       $allow_site_rake_commission = isset($this->app_config['affiliate_module']['custom_data']['site_commission'])?$this->app_config['affiliate_module']['custom_data']['site_commission']:0;

		$data_arr['allow_affiliate_commssion'] = $allow_site_rake_commission;
        
        
        $fcm_key = isset($this->app_config['fcm_key'])?$this->app_config['fcm_key']['key_value']:"";
        $data_arr['fc1'] = $data_arr['fc2'] = "";
        if(!empty($fcm_key)){
            $splt_limit = round(strlen($fcm_key)/2);
            $fcm_arr = str_split($fcm_key,$splt_limit);
            if(!empty($fcm_arr)){
                $data_arr['fc1'] = $fcm_arr['0'];
                $data_arr['fc2'] = $fcm_arr['1'];
            }
        }

        $data_arr['allow_tds'] = array();
        if(isset($this->app_config['allow_tds']) && $this->app_config['allow_tds']['key_value'] == "1"){
            $tds_info = $this->app_config['allow_tds']['custom_data'];
            $data_arr['allow_tds'] = array("ind"=>$tds_info['indian'],"percent"=>$tds_info['percent'],"amt"=>$tds_info['amount']);
        }

        $data_arr['allow_gst'] = 0;
        if(isset($this->app_config['allow_gst']) && $this->app_config['allow_gst']['key_value'] == "1"){
            $data_arr['allow_gst'] = 1;
            $data_arr['gst_rate'] = isset($this->app_config['allow_gst']['custom_data']['gst_rate'])?$this->app_config['allow_gst']['custom_data']['gst_rate']:0;
            $data_arr['gst_type'] = isset($this->app_config['allow_gst']['custom_data']['type'])?$this->app_config['allow_gst']['custom_data']['type']:'old';
            $data_arr['gst_bonus'] = isset($this->app_config['allow_gst']['custom_data']['gst_bonus'])?$this->app_config['allow_gst']['custom_data']['gst_bonus']:0;
        }

        //active cms pages
        $cms_cache_key = 'cms_page_keys';
        $cms_page = $this->get_cache_data($cms_cache_key);
        if(!$cms_page){
            $page_result = $this->Auth_model->get_single_row("group_concat(page_alias) as pages",CMS_PAGES,array("status"=>"1"));
            if(!empty($page_result) && isset($page_result['pages'])){
                $cms_page = explode(",",$page_result['pages']);
            }
            $this->set_cache_data($cms_cache_key, $cms_page, REDIS_30_DAYS);
        }
        $data_arr['cms_page'] = $cms_page;
        
        //get module setting
        $modules_data = $this->Auth_model->get_all_table_data(MODULE_SETTING,"module_setting_id,name,status");
        
        if(!empty($modules_data))
        {
            foreach($modules_data as $module)
            {
                $mname = str_replace('llow','',$module['name']);
                $data_arr[$mname] = $module['status'];
            }
        }

        //live fantasy
        $data_arr['allow_lf'] = isset($this->app_config['allow_livefantasy']) ? $this->app_config['allow_livefantasy']['key_value']:0;

        if($data_arr['allow_lf'] == "1"){
            $data_arr['lf_predict_time'] = isset($this->app_config['allow_livefantasy']['custom_data']['predict_time'])?$this->app_config['allow_livefantasy']['custom_data']['predict_time']:10;
        }

    
        //get sports hub
        //ALLOW_NETWORK_FANTASY -> netwrork fantasy setting
        $data_arr['allow_network_fantasy'] = ALLOW_NETWORK_FANTASY;
        $data_arr['sports_hub'] = $this->Auth_model->get_sports_hub($data_arr['l_list']);
        
        $data_arr =$this->filter_modules($data_arr,$modules_data);

        $data_arr['pickem_min'] = "10";
        $data_arr['pickem_max'] = "1000";
        $data_arr['correct_pick_pts'] = 10;
        $data_arr['incorrect_pick_pts'] = 5;
        if($data_arr['a_pickem_tournament'])
        {
            $pickem_custom_data = !empty($this->app_config['allow_pickem_tournament'])?$this->app_config['allow_pickem_tournament']['custom_data']:array();

            if(!empty($pickem_custom_data))
            {

                $data_arr['correct_pick_pts'] = $pickem_custom_data['correct'];
                $data_arr['incorrect_pick_pts'] = $pickem_custom_data['wrong'];
                $data_arr['pickem_score_predictor'] = isset($pickem_custom_data['score_predictor'])?$pickem_custom_data['score_predictor']:0;
                $data_arr['pickem_max_goal'] = isset($pickem_custom_data['max_goals'])?$pickem_custom_data['max_goals']:0;
                $data_arr['pickem_win_goal'] = isset($pickem_custom_data['winning_and_goal'])?$pickem_custom_data['winning_and_goal']:0;
                $data_arr['pickem_win_goal_diff'] = isset($pickem_custom_data['winning_and_goal_difference'])?$pickem_custom_data['winning_and_goal_difference']:0;
                $data_arr['pickem_win_only'] = isset($pickem_custom_data['winning_only'])?$pickem_custom_data['winning_only']:0;
            }
        }
        $data_arr['allow_buy_coin'] = isset($this->app_config['allow_buy_coin'])?$this->app_config['allow_buy_coin']['key_value']:0;
        $data_arr['booster'] = isset($this->app_config['allow_booster'])?$this->app_config['allow_booster']['key_value']:0;
        $data_arr['bench_player'] = isset($this->app_config['bench_player'])?$this->app_config['bench_player']['key_value']:0;

        $data_arr['allow_social'] = isset($this->app_config['allow_social'])?$this->app_config['allow_social']['key_value']:0;
        
        $data_arr['asf'] = isset($this->app_config['allow_stock_fantasy'])?$this->app_config['allow_stock_fantasy']['key_value']:0;

        $data_arr['a_equity'] = isset($this->app_config['allow_equity'])?$this->app_config['allow_equity']['key_value']:0;
        $data_arr['a_stock_predict'] = isset($this->app_config['allow_stock_predict'])?$this->app_config['allow_stock_predict']['key_value']:0;

        if($data_arr['asf'] ==1)
        {
            $this->app_config['allow_stock_fantasy']['custom_data']['contest_publish_time'] = $this->get_update_publish_time($this->app_config['allow_stock_fantasy']['custom_data']['contest_publish_time']) ;
            $data_arr['asf_setting'] = $this->app_config['allow_stock_fantasy']['custom_data'];
        }

        if($data_arr['a_equity'] ==1)
        {
            $this->app_config['allow_equity']['custom_data']['contest_publish_time'] = $this->get_update_publish_time($this->app_config['allow_equity']['custom_data']['contest_publish_time']) ;
            $data_arr['ae_setting'] = $this->app_config['allow_equity']['custom_data'];
        }

        $data_arr['a_live_stock_fantasy'] = isset($this->app_config['allow_live_stock_fantasy'])?$this->app_config['allow_live_stock_fantasy']['key_value']:0;
        if($data_arr['a_live_stock_fantasy'] ==1)
        {

            $data_arr['alsf_setting'] = $this->app_config['allow_live_stock_fantasy']['custom_data'];
        }

        //leaderboard
        $data_arr['leaderboard'] = $this->get_leaderboard_type_list();
        //subscription module
        $allow_subscription =  ($this->app_config['allow_coin_system'] && isset($this->app_config['allow_subscription']))?$this->app_config['allow_subscription']['key_value']:0;
		$data_arr['a_subscription'] = $allow_subscription;
        $allow_coin_exp = ($this->app_config['allow_coin_system']['key_value']==1 && isset($this->app_config['allow_coin_expiry']))?$this->app_config['allow_coin_expiry']['key_value']:0;
        if($allow_coin_exp==1)
        {
            $coin_exp_limit = $this->app_config['allow_coin_expiry']['custom_data'];
            $data_arr['coin_expiry_limit'] = isset($coin_exp_limit['ce_days_limit'])? $coin_exp_limit['ce_days_limit'] : 0;
        }else{
            $data_arr['coin_expiry_limit'] = 0;
        }

        $data_arr['allow_gc'] = isset($this->app_config['allow_game_center'])?$this->app_config['allow_game_center']['key_value']:0;

        $data_arr['h2h_challenge'] = isset($this->app_config['h2h_challenge'])?$this->app_config['h2h_challenge']['key_value']:0;
        if($data_arr['h2h_challenge'] == "1"){
            $h2h_data = $this->app_config['h2h_challenge']['custom_data'];
            $data_arr['h2h_data'] = array("group_id"=>$h2h_data['group_id'],"climit"=>$h2h_data['contest_limit']);
        }

        $data_arr['allow_props'] = 0;
        if(isset($this->app_config['allow_props']['key_value']) && $this->app_config['allow_props']['key_value'] == "1"){
            $data_arr['allow_props'] = !empty($this->app_config['allow_props']['custom_data']) ? $this->app_config['allow_props']['custom_data'] : 0;
        }

        $data_arr['opinion_trade'] = 0;
        if(isset($this->app_config['opinion_trade']['key_value']) && $this->app_config['opinion_trade']['key_value'] == "1"){
            $data_arr['opinion_trade'] = !empty($this->app_config['opinion_trade']['key_value']) ? $this->app_config['opinion_trade']['key_value'] : 0;
        }
        
        $data_arr['allow_picks'] = isset($this->app_config['allow_picks']) ? $this->app_config['allow_picks']['key_value']:0;

        $hub_banner_list = $this->Auth_model->get_hub_banner_list();
        $data_arr['sp_hub_banner'] = $hub_banner_list;
        
        //DFS with MUltigame
        $data_arr['dfs_multi'] = DFS_MULTI;
        
        $sub_modules_data = $this->Auth_model->get_all_table_data(SUBMODULE_SETTING,"submodule_key as name,status");

        if(!empty($sub_modules_data))
        {
            foreach($sub_modules_data as $module)
            {
                $data_arr[$module['name']] = $module['status'];
            }
        }      

        $data_arr['wdl_2fa'] = isset($this->app_config['allow_withdrawal_2fa']) ? $this->app_config['allow_withdrawal_2fa']['key_value']:0;
        $data_arr['a_offpg'] = isset($this->app_config['allow_offpg'])?$this->app_config['allow_offpg']['key_value']:0;

        //get sports hub
        //ALLOW_NETWORK_FANTASY -> netwrork fantasy setting
        $data_arr['allow_network_fantasy'] = ALLOW_NETWORK_FANTASY;
        $data_arr['sports_hub'] = $this->Auth_model->get_sports_hub($data_arr['l_list']);
        $data_arr =$this->filter_modules($data_arr,$modules_data);
        $tz_list = get_timezone();
        $tz_abbr = $this->app_config['timezone']['key_value'];
        $data_arr['timezone'] = ["key"=>$tz_abbr,'value'=>$tz_list[$tz_abbr]]; 
        //for upload app data on s3 bucket
        $this->push_s3_data_in_queue("app_master_data", $data_arr);
        $this->api_response_arry['data'] = $data_arr;
        $this->api_response();
    }

    private function get_update_publish_time($publish_time)
    {
        $publish_time = date('H:i:s',strtotime($publish_time.' +45 minute'));
        return $publish_time;
    }
    
    /** /
    *@method filter_modules 
    *@uses to filter modules
    *@param Array all modules details
    */
    private function filter_modules($data_arr,$modules_data)
    {
        $data_arr['a_quiz'] = isset($this->app_config['allow_quiz'])?$this->app_config['allow_quiz']['key_value']:0;
        
        $allow_coin_system = isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
        $allow_prediction_system =  isset($this->app_config['allow_prediction_system'])?$this->app_config['allow_prediction_system']['key_value']:0;
        $data_arr['a_prediction'] = $allow_prediction_system;
        if($allow_coin_system == 0 || $data_arr['a_coin'] == 0){
            $data_arr['a_coin'] = $allow_coin_system;
            $data_arr['a_prediction'] = "0";
            $data_arr['a_open_predictor'] = "0";
            $data_arr['a_fixed_open_predictor'] = "0";
            $data_arr['a_xp_point'] = "0";
            $data_arr['a_quiz'] = "0";
        }

        $data_arr['a_spin'] = isset($this->app_config['allow_spin'])?$this->app_config['allow_spin']['key_value']:0;
        
        $data_arr['a_scratchwin'] = isset($this->app_config['allow_scratchwin'])?$this->app_config['allow_scratchwin']['key_value']:0;

        $data_arr['min_bet_coins'] =  isset($this->app_config['min_bet_coins'])?$this->app_config['min_bet_coins']['key_value']:0;
        $data_arr['max_bet_coins'] =  isset($this->app_config['max_bet_coins'])?$this->app_config['max_bet_coins']['key_value']:0;

      
       
        $allow_open_predictor =  isset($this->app_config['allow_open_predictor'])?$this->app_config['allow_open_predictor']['key_value']:0;
        if($allow_open_predictor == 0){
            $data_arr['a_open_predictor'] = $allow_open_predictor;
        }

        $allow_fixed_open_predictor =  isset($this->app_config['allow_fixed_open_predictor'])?$this->app_config['allow_fixed_open_predictor']['key_value']:0;
        if($allow_fixed_open_predictor == 0){
            $data_arr['a_fixed_open_predictor'] = $allow_fixed_open_predictor;
        }

        $allow_pickem_tournament = isset($this->app_config['allow_pickem_tournament'])?$this->app_config['allow_pickem_tournament']['key_value']:0;
        if($allow_pickem_tournament ==0){
            $data_arr['a_pickem_tournament'] = $allow_pickem_tournament;
        }

        $allow_dfs =  isset($this->app_config['allow_dfs'])?$this->app_config['allow_dfs']['key_value']:0;
        if($allow_dfs == 0){
            $data_arr['a_dfs'] = $allow_dfs;
        }

        $allow_multigame =  isset($this->app_config['allow_multigame'])?$this->app_config['allow_multigame']['key_value']:0;
        if($allow_multigame == 0){
            $data_arr['a_multigame'] = $allow_multigame;
        }

        $allow_distributor =  isset($this->app_config['allow_distributor'])?$this->app_config['allow_distributor']['key_value']:0;
        if($allow_distributor == 0){
            $data_arr['a_distributor'] = $allow_distributor;
        }
        $allow_free_to_play =  isset($this->app_config['allow_free_to_play'])?$this->app_config['allow_free_to_play']['key_value']:0;
        if($allow_free_to_play == 0){
            $data_arr['a_free_to_play'] = $allow_free_to_play;
        }
        
        $allow_stock_fantasy = isset($this->app_config['allow_stock_fantasy'])?$this->app_config['allow_stock_fantasy']['key_value']:0;

        $allow_equity = isset($this->app_config['allow_equity'])?$this->app_config['allow_equity']['key_value']:0;
        
        $allow_livefantasy = isset($this->app_config['allow_livefantasy']) ? $this->app_config['allow_livefantasy']['key_value']:0;

        $allow_picks = isset($this->app_config['allow_picks']) ? $this->app_config['allow_picks']['key_value']:0;


        $allow_stock_predict = isset($this->app_config['allow_stock_predict'])?$this->app_config['allow_stock_predict']['key_value']:0;

        $allow_live_stock_fantasy = isset($this->app_config['allow_live_stock_fantasy'])?$this->app_config['allow_live_stock_fantasy']['key_value']:0;

        $allow_props =  isset($this->app_config['allow_props'])?$this->app_config['allow_props']['key_value']:0;
        $opinion_trade =  isset($this->app_config['opinion_trade'])?$this->app_config['opinion_trade']['key_value']:0;
        

        $data_arr['a_crypto'] = isset($this->app_config['allow_crypto'])?$this->app_config['allow_crypto']['key_value']:0;
        if($data_arr['a_crypto'] == 1){
            $apk_data = $this->app_config['allow_crypto']['custom_data'];
            $dp_str = isset($apk_data['dp']) ? $apk_data['dp'] : "";
            $curr_arr = get_crypto_currencies();
            $dp_str = explode("_",$dp_str);
            $data_arr['crypto_cur'] = array();
            foreach($dp_str as $cr){
                $data_arr['crypto_cur'][$cr] = $curr_arr[$cr];
            }

            $wd_str = isset($apk_data['wd']) ? $apk_data['wd'] : "";
            $wd_str = explode("_",$wd_str);
            $data_arr['crypto_wd'] = array();
            foreach($wd_str as $cr){
                $data_arr['crypto_wd'][$cr] = $curr_arr[$cr];
            }
        }else{
            $data_arr['crypto_cur'] = $data_arr['crypto_wd'] = array();
        }

        $data_arr['a_btcpay'] = isset($this->app_config['allow_btcpay'])?$this->app_config['allow_btcpay']['key_value']:0;

        $sports_ids = array_column($data_arr['fantasy_list'],"sports_id");

        $modules_data = array_column($modules_data,'status','name');
        foreach($data_arr['sports_hub'] as $key =>  &$hub)
        {
            $allowed_sports_arr = array();
            $allowed_sports = json_decode($hub['allowed_sports'],TRUE);
            if(!empty($allowed_sports)){
                $allowed_sports_arr = array_values(array_intersect($sports_ids,$allowed_sports));
            }
            $data_arr['sports_hub'][$key]['allowed_sports'] = $allowed_sports_arr;
            if($hub['game_key'] == 'allow_prediction' && (!$modules_data['allow_prediction'] || !$data_arr['a_prediction']) )
            {
                unset($data_arr['sports_hub'][$key]); 
            }

            if($hub['game_key'] == 'allow_open_predictor' && (!$modules_data['allow_open_predictor']  || !$data_arr['a_open_predictor']) )
            {
                unset($data_arr['sports_hub'][$key]);
            }

            if($hub['game_key'] == 'allow_fixed_open_predictor' && (!$modules_data['allow_fixed_open_predictor']  || !$data_arr['a_fixed_open_predictor']) )
            { 
                unset($data_arr['sports_hub'][$key]);
            }

            if($hub['game_key'] == 'pickem_tournament' && empty($data_arr['a_pickem_tournament']))
            {
                unset($data_arr['sports_hub'][$key]);
            }

            if($hub['game_key'] == 'allow_free2play' && empty($data_arr['a_free_to_play']))
            {
            unset($data_arr['sports_hub'][$key]);
            }

            if($hub['game_key'] == 'allow_multigame' && empty($data_arr['a_multigame']))
            {
                unset($data_arr['sports_hub'][$key]);
            }

            if($hub['game_key'] == 'allow_stock_fantasy' && $allow_stock_fantasy!=1)
            {
                unset($data_arr['sports_hub'][$key]);
            }

            if($hub['game_key'] == 'allow_equity' && $allow_equity!=1)
            {
                unset($data_arr['sports_hub'][$key]);
            }
            if($hub['game_key'] == 'allow_stock_predict' && $allow_stock_predict!=1)
            {
                unset($data_arr['sports_hub'][$key]);
            }
            if($hub['game_key'] == 'live_fantasy' && $allow_livefantasy!=1)
            {
                unset($data_arr['sports_hub'][$key]);
            }
            if($hub['game_key'] == 'allow_live_stock_fantasy' && $allow_live_stock_fantasy!=1)
            {
                unset($data_arr['sports_hub'][$key]);
            }
            if($hub['game_key'] == 'picks_fantasy' && $allow_picks != 1)
            {
                unset($data_arr['sports_hub'][$key]);
            }

            if($hub['game_key'] == 'allow_dfs' && empty($data_arr['a_dfs']))
            {
                unset($data_arr['sports_hub'][$key]);
            }

            if($hub['game_key'] == 'props_fantasy' && $allow_props != 1)
            {
                unset($data_arr['sports_hub'][$key]);
            }

            if($hub['game_key'] == 'opinion_trade_fantasy' && $opinion_trade != 1)
            {
                unset($data_arr['sports_hub'][$key]);
            }
        }

        $data_arr['sports_hub'] = array_values($data_arr['sports_hub']);
        return $data_arr;

    }

    /**
     * used for get signup referral data
     * @param
     * @return json array
     */
    public function get_signup_referral_data_post() {
        //referral amount data
        $affiliate_master_id = SIGNUP_BANNER_AFF_ID;//without referral
        $affiliate_cache_key = 'affiliate_master_' . $affiliate_master_id;//without referral
        $affiliate_master_id_referral = LOBBY_REFER_BANNER_AFF_ID;//with referral
        $affiliate_cache_key_referral = 'affiliate_master_' . $affiliate_master_id_referral;//without referral
        $referral_data = $this->get_cache_data($affiliate_cache_key_referral);
        $wo_referral_data = $this->get_cache_data($affiliate_cache_key);
        if(!$referral_data || !$wo_referral_data) {
            $this->load->model("auth/Auth_model");
            $ref_data = $this->Auth_model->get_all_table_data(AFFILIATE_MASTER,"*"," affiliate_master_id IN(".$affiliate_master_id.",".$affiliate_master_id_referral.") ");
            $ref_data = array_column($ref_data,NULL,'affiliate_master_id');

            $referral_data = $ref_data[$affiliate_master_id_referral];
            $wo_referral_data = $ref_data[$affiliate_master_id];

            $this->set_cache_data($affiliate_cache_key, $wo_referral_data , REDIS_24_HOUR);
            $this->set_cache_data($affiliate_cache_key_referral, $referral_data, REDIS_24_HOUR);
        }

        $referral_amount = 0;
        $currency_type = $this->app_config['currency_abbr']['key_value'];
        if (!empty($referral_data)) {
            if (isset($referral_data['user_real']) && $referral_data['user_real'] > 0) {
                $referral_amount = $referral_data['user_real'];
            } else if (isset($referral_data['user_bonus']) && $referral_data['user_bonus'] > 0) {
                $referral_amount = $referral_data['user_bonus'];
                $currency_type = "Bonus";
            } else if (isset($referral_data['user_coin']) && $referral_data['user_coin'] > 0) {
                $referral_amount = $referral_data['user_coin'];
                $currency_type = "Coin";
            }
        }

        $response_data = array();
        if(!empty($referral_data))
        {
            $response_data['referral_data'] = array(
                'bonus_amount' => $referral_data['user_bonus'],
                'real_amount' => $referral_data['user_real'],
                'coins' => $referral_data['user_coin']
            );
        }    

        if(!empty($wo_referral_data))
        {
            $response_data['without_referral_data'] = array(
                'bonus_amount' => $wo_referral_data['user_bonus'],
                'real_amount' => $wo_referral_data['user_real'],
                'coins' => $wo_referral_data['user_coin']
            );
        }    


        $this->api_response_arry['data'] = $response_data;
        $this->api_response();
    }

    /**
     * used for save otp in db
     * @param
     * @return json array
     */
    public function send_otp($data) {
        $is_systemuser = 0;
        if (isset($data['is_systemuser']) && $data['is_systemuser'] == 1) {
            $is_systemuser = 1;
        }
        $where = $data['phone_no'];
        $otp_hash = isset($data['otp_hash']) ? $data['otp_hash'] : "";
        $phone_code = ($data['phone_code']) ? $data['phone_code'] : DEFAULT_PHONE_CODE;

        $this->load->model("auth/Auth_nosql_model");
        $otp = $this->Auth_nosql_model->send_otp($data, 0, $is_systemuser);
        $data['otp'] = $otp;
        if ($is_systemuser == 0) {
            $sms_data = array();
            $sms_data['otp'] = $otp;
            $sms_data['mobile'] = $data['phone_no'];
            $sms_data['phone_code'] = $phone_code;
            $otp_message = 'Your OTP is {OTP}. Please enter this to verify your mobile. Thank you for choosing '.SITE_TITLE;
            if(isset($this->app_config['otp_message']) && !empty($this->app_config['otp_message'])){
                $otp_message = $this->app_config['otp_message']['key_value'];
            }
            $otp_message = str_replace('{OTP}',$otp,$otp_message);
            $otp_message = str_replace('{HASH}',$otp_hash,$otp_message);
            $sms_data['message'] = $otp_message;

            $this->load->helper('queue_helper');
            add_data_in_queue($sms_data, 'sms');
        }

        return $otp;
    }

    /**
     * Used for validate user social token
     * @param
     * @return json array
     */
    protected function validate_user_social_id($data = array(), $user_data = array()) {
        if (empty($user_data)) {
            $user_data = $this->Auth_model->get_single_row("user_id,user_unique_id,facebook_id,google_id", USER, array('user_id' => $data['user_id']));
        }

        $valid = TRUE;
        if (!empty($user_data['facebook_id']) && !empty($data['facebook_id']) && $data['facebook_id'] != $user_data['facebook_id']) {
            $valid = FALSE;
        } else if (!empty($user_data['google_id']) && !empty($data['google_id']) && $data['google_id'] != $user_data['google_id']) {
            $valid = FALSE;
        }

        if (!$valid) {
            $error = array('facebook_id' => $this->lang->line('mobile_already_attached_to_other'));
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = $error;
            $this->api_response();
        }

        return $valid;
    }

    /**
     * Used for validate google captcha
     * @param array $post_data
     * @return json array
     */
    public function validate_google_captcha($post_data){
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $user_ip = get_user_ip_address();
        $data = array(
                    'secret' => $this->app_config['allow_google_captcha']['custom_data']['google_captcha_secret'],
                    'response' => $post_data['token'],
                    'remoteip' => $user_ip
                );

        $option = array(
                        'http' => array(
                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                            'method' => 'POST',
                            'content' => http_build_query($data)
                        )
                    );
                 
        $context = stream_context_create($option);
        $response = file_get_contents($url,false,$context);
        $result = json_decode($response,true);
        return $result;
    }

    /**
     * Used for user login
     * @param
     * @return json array
     */
    public function login_post() {
        $validation_rule    =   array(                                
                                    array(
                                        'field' => 'phone_code',
                                        'label' => $this->lang->line("phone_code"),
                                        'rules' => 'trim|required'
                                    ),
                                    array(
                                        'field' => 'phone_no',
                                        'label' => $this->lang->line("phone_no"),
                                        'rules' => 'trim|required'
                                    ),
                                    array(
                                        'field' => 'device_type',
                                        'label' => $this->lang->line("device_type"),
                                        'rules' => 'trim|callback_check_device_type'
                                    ), 
                                    array(
                                        'field' => 'device_id',
                                        'label' => $this->lang->line("device_id"),
                                        'rules' => 'trim|callback_check_device_id'
                                    ),
                                    array(
                                        'field' => 'facebook_id',
                                        'label' => $this->lang->line("facebook_id"),
                                        'rules' => 'callback_social_required|callback_facebook_valid'
                                    ),
                                    array(
                                        'field' => 'google_id',
                                        'label' => $this->lang->line("google_id"),
                                        'rules' => 'callback_social_required|callback_google_valid'
                                    )
                                );
        if($this->app_config['allow_google_captcha']['key_value']){
            $validation_rule[] = array(
                                        'field' => 'token',
                                        'label' => "token",
                                        'rules' => 'trim|required'
                                    );
        }
        $this->form_validation->set_rules($validation_rule); 
        if($this->form_validation->run() == FALSE)  { //validate post parameter
            $this->send_validation_errors(); 
        }
        
        $post_data = $this->post();
        if($this->app_config['allow_google_captcha']['key_value']){
            $captcha = $this->validate_google_captcha($post_data);
            if(!isset($captcha['success']) || $captcha['success'] != true){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = "Sorry, your request blocked due to suspicious activity.";
                $this->api_response();
            }
        }
        //for check user account blocked status
        $this->check_user_account_blocked_status();

        $single_country = !empty($this->app_config['single_country'])?$this->app_config['single_country']['key_value']:0;
        $phone_code = !empty($this->app_config['phone_code'])?$this->app_config['phone_code']['key_value']:DEFAULT_PHONE_CODE;
        if($single_country == 1 && $post_data['phone_code'] != $phone_code){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("disable_country_signup_error");
            $this->api_response();
        }
        
        $device_type = isset($post_data['device_type']) ? $post_data['device_type'] : 3;        
        $current_date = format_date();
        $this->load->model("auth/Auth_model");
        $user_profile = $this->Auth_model->get_single_row("user_id,user_unique_id,phone_no,phone_code,phone_verfied,IFNULL(facebook_id,'') as facebook_id,IFNULL(google_id,'') as google_id,IFNULL(user_name,'') as user_name,IFNULL(email,'') as email,status,is_systemuser,referral_code,bs_status,IFNULL(image,'') as image,IFNULL(master_state_id,'') as master_state_id", USER, array('phone_no' => $post_data['phone_no']));

        $is_user_exist = 0; //0=Not exist,1=User already exist.
        $is_profile_complete = 0; // 0 = Not complete,1=complete
        $response = array();
        $user_data = array();
        $image = '';
        //If user already exist than return user id with profile related information.
        if (!empty($user_profile)) {
            $image = $user_profile['image'];
            //if admin inactivate account then show error
            if (isset($user_profile['status']) && $user_profile['status'] == 0) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line("your_account_deactivated");
                $this->api_response();
            }

            //for check given mobile attached with social account or not
            $this->validate_user_social_id($post_data, $user_profile);

            $is_user_exist = 1;
            if ($user_profile['phone_verfied'] == 1 && !empty($user_profile['user_name']) && !empty($user_profile['email'])) {
                $is_profile_complete = 1;
            }
            
            $user_data['user_id']       = $user_profile['user_id'];
            $user_data['user_unique_id'] = $user_profile['user_unique_id'];
            $user_data['phone_no'] = $user_profile['phone_no'];
            $user_data['phone_code'] = $user_profile['phone_code'];
            $user_data['user_name'] = $user_profile['user_name'];
            $user_data['email'] = $user_profile['email'];
            $user_data['is_systemuser'] = $user_profile['is_systemuser'];
            $tmp_user = array("user_id"=>$user_profile['user_id'],"user_unique_id"=>$user_profile['user_unique_id'],"phone_no"=>$user_profile['phone_no'],"user_name"=>$user_profile['user_name'],"email"=>$user_profile['email'],"facebook_id"=>$user_profile['facebook_id'],"google_id"=>$user_profile['google_id'],"phone_verfied"=>$user_profile['phone_verfied'],"status"=>$user_profile['status'],"referral_code"=>$user_profile['referral_code'],"bs_status"=>$user_profile['bs_status'],"image"=>$user_profile['image'],"master_state_id"=>$user_profile['master_state_id']);
            $user_data['user_detail'] = $tmp_user;
            if(in_array($device_type,[1,2]) && (isset($post_data['otp_hash']) && $post_data['otp_hash']!=null))
            {
                $user_data['otp_hash'] = $post_data['otp_hash'];
            }
            $message = $this->lang->line("otp_send_success");
            $otp = $this->send_otp($user_data);
        }
        //if new user than create user_id and return.
        else {
            
            $allow_bs = isset($this->app_config['allow_bs'])?$this->app_config['allow_bs']['key_value']:0;
            if ($allow_bs == "1") {
                $app_data = isset($this->app_config['allow_bs']['custom_data']) ? $this->app_config['allow_bs']['custom_data'] : array();
                $api_key = isset($app_data['api_key']) ? $app_data['api_key'] : "";
                $user_location = $this->input->get_request_header('Ult');
                $loc_query = get_user_ip_address();
                if ($user_location) {
                    $loc_query = base64_decode($user_location);
                }
                $api_arr = array("key"=>$api_key,"query"=>$loc_query);
                // $api_arr = array("key"=>"89b9edaa50807c4ebd630273edfd568d","query"=>"22.080550,78.164949");  //done done
                $geo_result = validate_location_api($api_arr);
                $response['geo_country'] = $geo_result['data'][0]['country'] ? $geo_result['data'][0]['country']:"NA";
            }



            $tracking_source = array(
                "cm" => isset($post_data['campaign']) ? $post_data['campaign'] : '',
                "md" => isset($post_data['medium']) ? $post_data['medium'] : '',
                "sr" => isset($post_data['source']) ? $post_data['source'] : '',
                "tm" => isset($post_data['term']) ? $post_data['term'] : '',
                "cn" => isset($post_data['content']) ? $post_data['content'] : '',
            );
            $data = array();
            $user_unique_id = $this->Auth_model->_generate_key();
            $referral_code = $this->Auth_model->_generate_referral_code();
            $avatar_image = $this->Auth_model->get_first_rendom_avatar();
            $image = $avatar_image['name'];
            
            $data['image'] = $avatar_image['name'] ;
            $data['user_unique_id'] = $user_unique_id;
            $data['referral_code'] = $referral_code;
            $data['last_login'] = $current_date;
            $data['added_date'] = $current_date;
            $data['modified_date'] = $current_date;
            $data['last_ip'] = get_user_ip_address();
            $data['status'] = '2';
            $data["phone_no"] = $post_data['phone_no'];
            $data["phone_code"] = $post_data['phone_code'];
            $data['phone_verfied'] = 0; //default not verified
            $data['device_type'] = $device_type;
            $data["is_systemuser"] = isset($post_data['is_systemuser']) ? $post_data['is_systemuser'] : 0;
            $data['tracking'] = $tracking_source ? json_encode($tracking_source):null;
            $user_id = $this->Auth_model->registration($data);
            if ($user_id) {
                $user_data['user_id']       = $user_id;
                $user_data['user_unique_id'] = $user_unique_id;
                $user_data['phone_no'] = $post_data['phone_no'];
                $user_data['phone_code'] = $post_data['phone_code'];
                $user_data['user_name'] = $post_data['email'] = "";
                $user_data['is_systemuser'] = $data["is_systemuser"];
                $tmp_user = array("user_id"=>(string)$user_id,"user_unique_id"=>$user_unique_id,"phone_no"=>$post_data['phone_no'],"user_name"=>"","email"=>"","facebook_id"=>"","google_id"=>"","phone_verfied"=>"0","status"=>$data['status'],"referral_code"=>$referral_code,"image"=>$image,"master_state_id"=>"");
                $user_data['user_detail'] = $tmp_user;
                
                $message = $this->lang->line("otp_send_success");
                if(in_array($device_type,[1,2]) && (isset($post_data['otp_hash']) && $post_data['otp_hash']!=null))
                {
                    $user_data['otp_hash'] = $post_data['otp_hash'];
                }
                $otp = $this->send_otp($user_data);
                
            }
        }

        // add user data in feed DB
        if($user_data['user_id']) {
            $user_sync_data = array();
            $user_sync_data['data'] = array(
                "Action" => "Signup",
                "UserID" => $user_data['user_id'],
                "UserGUID" => $user_data['user_unique_id'],
                "PhoneNumber" => $user_data['phone_no'],
                "FirstName" => $user_data["user_name"],
                "LastName" => '',
                "ProfilePicture" => $image,
                "IPAddress" => get_user_ip_address(),
                "DeviceTypeID" => $device_type
            );
            $this->load->helper('queue_helper');
            add_data_in_queue($user_sync_data, 'user_sync');
        }

        unset($user_data['user_id']);
        unset($user_data['user_detail']);
        unset($user_data['email']);
        unset($user_data['user_name']);
        unset($user_data['user_unique_id']);
        $response['user_profile'] = $user_data;

        
        $response['is_user_exist'] = $is_user_exist;
        $response['is_profile_complete'] = $is_profile_complete;
        $this->api_response_arry['data'] = $response;
        $this->api_response_arry['message'] = $message;
        $this->api_response();
    }

    /*
     * Validate phone OTP
     * For validate user mobile OTP
     */
    public function validate_otp_post() {
        $response = array();
        $post_data = $this->post();
        $this->form_validation->set_rules('otp', $this->lang->line("confirmation_code"), 'trim|required|max_length[6]');
        $this->form_validation->set_rules('phone_no', $this->lang->line("phone_no"), 'trim|required');
        // $this->form_validation->set_rules('install_date', $this->lang->line("install_date"), 'trim|required');
        $this->form_validation->set_rules('device_type', $this->lang->line("device_type"), 'trim|required');
        $this->form_validation->set_rules('facebook_id', $this->lang->line("facebook_id"), 'callback_social_required|callback_facebook_valid');
        $this->form_validation->set_rules('google_id', $this->lang->line("google_id"), 'callback_social_required|callback_google_valid');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        //for check user account blocked status
        $this->check_user_account_blocked_status();

        $this->load->model("auth/Auth_model");
        $result = $this->Auth_model->check_otp();
        if (empty($result) || empty($result['status'])) {
            $this->api_response_arry["message"] = $result['message'];
            $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $user_data = $result['data'];
        $device_type = $post_data['device_type'];
        $device_id = $post_data['device_id'];
        $device_type = (!empty($device_type)) ? $device_type : "3";
        $device_id = (!empty($device_id)) ? $device_id : "";
        $api_key = $this->Auth_model->generate_active_login_key($user_data, $device_type, $device_id);

        $a_eml = !empty($this->app_config['allow_mobile_email']) ? $this->app_config['allow_mobile_email']['key_value']:0;
        $is_profile_complete = 0; // 0 = Not complete,1=complete
        $next_step = "login_success";
        //If user already exist than return user id with profile related information.
        if (!empty($user_data['phone_no']) && $user_data['phone_verfied'] == 1 && !empty($user_data['user_name']) && (($a_eml == 1 && !empty($user_data['email'])) || $a_eml == 0)) {
            $is_profile_complete = 1;
        }

        //check profile incomplete and referral code used or not and set next step
        if ($is_profile_complete == 0) {
            if(isset($post_data['affcd'])){
                $next_step = "";    
            }
            else{
            $next_step = "referral";
            }
            $affililate_history = $this->Auth_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY, array("friend_id" => $user_data['user_id'], "affiliate_type" => 1));
            if (!empty($affililate_history) || !empty($user_data['email']) || !empty($user_data['user_name'])) {
                $next_step = "";
            }

            if ($next_step == "" && empty($user_data['email']) && $a_eml == "1") {
                $next_step = "email";
            } else if ($next_step == "" && empty($user_data['user_name'])) {
                $next_step = "pick-username";
            }
        }

        //subscribe to fcm lineuout queue
        if(isset($device_id) && $device_id != ""){
            $this->load->helper('queue');

            //Add device ids in stock topic through queue
            $content = array();
            if(!empty($this->app_config['allow_stock_fantasy']['key_value']) || !empty($this->app_config['allow_equity']['key_value']) || !empty($this->app_config['allow_stock_predict']['key_value']) ||  !empty($this->app_config['allow_live_stock_fantasy']['key_value']) )
            {
                $content = array("type"=>"subscribe","topic"=>FCM_TOPIC."stock","ids"=>array($device_id));
            }
            add_data_in_queue($content,'fcm_topic');
        }

        // add user data in feed DB
        $user_sync_data = array();
        $user_sync_data['data'] = array(
            "Action" => "Login",
            "UserID" => $user_data['user_id'],
            "LoginSessionKey" => $api_key,
            "IPAddress" => get_user_ip_address(),
            "DeviceToken" => $device_id,            
            "DeviceTypeID" => $device_type
        );
        $this->load->helper('queue_helper');
        add_data_in_queue($user_sync_data, 'user_sync');


        $message = $this->lang->line('phone_verified_success');
        unset($user_data['user_id']);
        unset($user_data['otp_code']);
        unset($user_data['created_date']);
        unset($user_data['status']);
        unset($user_data['phone_verfied']);
        $user_data['device_id'] = $device_id;
        $response['user_profile'] = $user_data;
        $response[AUTH_KEY] = $api_key;
        $response['is_profile_complete'] = $is_profile_complete;
        $response['next_step'] = $next_step;
        $this->api_response_arry['data'] = $response;
        $this->api_response_arry["message"] = $message;
        $this->api_response();
    }

    /**
     * Resend OTP on register mobile
     * phone number and phone code
     */
    public function resend_otp_post() {
        $this->form_validation->set_rules('phone_no', $this->lang->line("phone_no"), 'trim|required|callback_is_digits|min_length[8]|max_length[10]');
        $this->form_validation->set_rules('phone_code', $this->lang->line("phone_code"), 'trim|required|callback_is_digits');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        //for check user account blocked status
        $this->check_user_account_blocked_status();
        
        $post_data = $this->post();
        $this->load->model("auth/Auth_model");
        $userdata = $this->Auth_model->get_single_row("user_id,user_unique_id,phone_no,phone_code,phone_verfied,IFNULL(facebook_id,'') as facebook_id,IFNULL(google_id,'') as google_id,IFNULL(user_name,'') as user_name,IFNULL(email,'') as email,status,is_systemuser,referral_code,bs_status", USER, array('phone_no' => $post_data['phone_no']));
        if (!$userdata) {
            $error = array('phone_no' => $this->lang->line('no_account_found'));
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = $error;
            $this->api_response();
        }

        $phone_no = $post_data['phone_no'];
        $phone_code = $post_data['phone_code'];

        $this->load->model("auth/Auth_nosql_model");
        //check for phone number existed
        $record = $this->Auth_nosql_model->select_one_nosql(MANAGE_OTP, array("phone_no" => $phone_no));

        if (!empty($record)) {
            $created_date = $record['updated_at'];
            $now = strtotime(format_date());
            $time = strtotime($created_date . ' +30 second');

            if ($time > $now) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line('otp_multiple_request');
                $this->api_response();
            }
        }
        if (empty($userdata['phone_code'])) {
            $userdata['phone_code'] = $phone_code;
        }
        $tmp_user = array("user_id"=>$userdata['user_id'],"user_unique_id"=>$userdata['user_unique_id'],"phone_no"=>$userdata['phone_no'],"user_name"=>$userdata['user_name'],"email"=>$userdata['email'],"facebook_id"=>$userdata['facebook_id'],"google_id"=>$userdata['google_id'],"phone_verfied"=>$userdata['phone_verfied'],"status"=>$userdata['status'],"referral_code"=>$userdata['referral_code'],"bs_status"=>$userdata['bs_status']);
        $userdata['user_detail'] = $tmp_user;
        if((isset($post_data['device_type']) && in_array($post_data['device_type'],[1,2])) && (isset($post_data['otp_hash']) && $post_data['otp_hash']!=null))
        {
            $userdata['otp_hash'] = $post_data['otp_hash'];
        }
        $this->send_otp($userdata);
        $response['phone_no'] = substr($userdata['phone_no'], -4);

        $this->api_response_arry['data'] = $response;
        $this->api_response_arry['message'] = $this->lang->line('resend_otp_send_success');
        $this->api_response();
    }

    /**
     * Social login Validate 
     * social element
     */
    public function social_login_post() {
        $validation_rule    =   array(                                
                                    array(
                                        'field' => 'device_type',
                                        'label' => $this->lang->line("device_type"),
                                        'rules' => 'trim|callback_check_device_type'
                                    ), 
                                    array(
                                        'field' => 'device_id',
                                        'label' => $this->lang->line("device_id"),
                                        'rules' => 'trim|callback_check_device_id'
                                    ),
                                    array(
                                        'field' => 'facebook_id',
                                        'label' => $this->lang->line("facebook_id"),
                                        'rules' => 'callback_social_required|callback_facebook_valid'
                                    ),
                                    array(
                                        'field' => 'google_id',
                                        'label' => $this->lang->line("google_id"),
                                        'rules' => 'callback_social_required|callback_google_valid'
                                    )
                                );
        $this->form_validation->set_rules($validation_rule); 
        if($this->form_validation->run() == FALSE)  { //validate post parameter
            $this->send_validation_errors(); 
        }
        
        $post_data = $this->post();
        $inputdata = array();
        $social_data = array();
        $inputdata["facebook_id"] = isset($post_data['facebook_id']) ? $post_data['facebook_id'] : FALSE;
        $inputdata["google_id"] = isset($post_data['google_id']) ? $post_data['google_id'] : FALSE;

        if ($inputdata["facebook_id"]) {
            $social_data = array("facebook_id" => $inputdata["facebook_id"]);
        } elseif ($inputdata["google_id"]) {
            $social_data = array("google_id" => $inputdata["google_id"]);
        }

        if (empty($social_data)) {
            $error = array('facebook_id' => $this->lang->line('social_required'));
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = $error;
            $this->api_response();
        }

        $this->load->model("auth/Auth_model");
        $device_type = $post_data['device_type'];
        $device_id = $post_data['device_id'];
        $user_profile = $this->Auth_model->get_single_row("user_id,user_unique_id,phone_no,phone_code,phone_verfied,facebook_id,google_id,IFNULL(user_name,'') AS user_name,email,referral_code,bs_status", USER, $social_data);

        $is_user_exist = 0; //0=Not exist,1=User already exist.
        $is_profile_complete = 0; // 0 = Not complete,1=complete
        $next_step = "phone";
        $user_data = array();
        $api_key = "";
        //If user already exist than return user id with profile related information.
        if (!empty($user_profile)) {
            $next_step = "login_success";
            $is_user_exist = 1;
            if ($user_profile['phone_verfied'] == 1 && !empty($user_profile['user_name']) && !empty($user_profile['email'])) {
                $is_profile_complete = 1;
            }

            $user_data['user_id'] = $user_profile['user_id'];
            $user_data['user_unique_id'] = $user_profile['user_unique_id'];
            $user_data['phone_no'] = $user_profile['phone_no'];
            $user_data['phone_code'] = $user_profile['phone_code'];
            $user_data['user_name'] = $user_profile['user_name'];
            $user_data['email'] = $user_profile['email'];

            //check profile incomplete and referral code used or not and set next step
            if ($is_profile_complete == 0) {
                $next_step = "referral";
                $affililate_history = $this->Auth_model->get_single_row('user_affiliate_history_id', USER_AFFILIATE_HISTORY, array("friend_id" => $user_profile['user_id'], "affiliate_type" => 1));
                if (!empty($affililate_history) || !empty($user_data['email']) || !empty($user_data['user_name'])) {
                    $next_step = "";
                }

                if ($next_step == "" && empty($user_data['phone_no'])) {
                    $next_step = "phone";
                }else if ($next_step == "" && empty($user_data['email'])) {
                    $next_step = "email";
                } else if ($next_step == "" && empty($user_data['user_name'])) {
                    $next_step = "pick-username";
                }
            }

            //save default username
            if(!isset($user_profile['user_name']) || $user_profile['user_name'] == ""){
                $user_data['user_name'] = $user_profile['user_name'] = $this->Auth_model->generate_user_name($user_profile['email']);
            }
            $device_type = (!empty($device_type)) ? $device_type : "3";
            $device_id = (!empty($device_id)) ? $device_id : "";
            $api_key = $this->Auth_model->generate_active_login_key($user_profile, $device_type, $device_id);
        }

        $response['user_profile'] = $user_data;
        $response[AUTH_KEY] = $api_key;
        $response['is_user_exist'] = $is_user_exist;
        $response['is_profile_complete'] = $is_profile_complete;
        $response['next_step'] = $next_step;
        $this->api_response_arry['data'] = $response;
        $this->api_response();
    }

    /**
     * facebook_auth to check facebook access token
     * @param
     * @return json array
     */
    public function facebook_auth() {
        $fb = new Facebook\Facebook([
            'app_id' => $this->app_config['FB_Login']['custom_data']['api_key'],
            'app_secret' => $this->app_config['FB_Login']['custom_data']['secret_key'],
            'default_graph_version' => 'v2.8'
        ]);

        $response = FALSE;
        $error = FALSE;

        $post_data = $this->post();
        $accessToken = $post_data['facebook_access_token'];

        if (!isset($accessToken)) {
            return FALSE;
        }
        // Logged in!
        $accessToken = (string) $accessToken;

        // OAuth 2.0 client handler
        $oAuth2Client = $fb->getOAuth2Client();

        try {
            // Exchanges a short-lived access token for a long-lived one
            $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            return $e->getMessage();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            return $e->getMessage();
        }

        // Sets the default fallback access token so we don't have to pass it to each request
        $fb->setDefaultAccessToken($longLivedAccessToken);

        try {
            // Get the \Facebook\GraphNodes\GraphUser object for the current user.
            // If you provided a 'default_access_token', the '{access-token}' is optional.
            $fbnode = $fb->get('/me', $accessToken);
            $response = $fbnode->getGraphUser();
            $facebook_id = $response->getId();
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            return $e->getMessage();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            return $e->getMessage();
        }

        if ($response && isset($facebook_id) && $facebook_id) {
            return $facebook_id;
        }
        return FALSE;
    }

    /**
     * google_auth to check google access token
     * @param
     * @return json array
     */
    public function google_auth_get() {
        $this->app_config['G_Login']['custom_data']['api_key'];
        $id_token = $this->input->post('google_access_token');
        $client_id = $this->app_config['G_Login']['custom_data']['web_client_id'];
        if ($this->input->post('device_type') == ANDROID) {
            $client_id = "";
        } elseif ($this->input->post('device_type') == IOS) {
            $client_id = "";
        }

        $client = new Google_Client();
        $client->setApplicationName($this->app_config['G_Login']['custom_data']['app_name']);
        $client->setClientId($client_id);
        $client->fetchAccessTokenWithAuthCode($id_token);
        $attributes = $client->verifyIdToken($id_token, $client_id);
        if (isset($attributes["sub"])) {
            return $attributes["sub"];
        }
        return FALSE;
    }

    /**
     * logout will flush session
     * @param
     * @return json array
     */
    public function logout_post() {
        $this->form_validation->set_rules(AUTH_KEY, $this->lang->line("session_key"), 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $key = $this->input->post(AUTH_KEY);

        $this->load->model("auth/Auth_model");
        $key_detail = $this->Auth_model->get_single_row('device_type,device_id', ACTIVE_LOGIN,array("key" => $key, "device_id IS NOT NULL" => NULL, "role" => 1));

        $this->Auth_model->remove_active_login($key);

        // add user data in feed DB
        $user_sync_data = array();
        $user_sync_data['data'] = array(
            "Action" => "Logout",
            "LoginSessionKey" => $key
        );
         //unsubscribe to fcm lineuout queue
        if(isset($key_detail) && !empty($key_detail) && !empty($key_detail['device_id'])){
            $this->load->helper('queue');

            //Add device ids in stock topic through queue
            $content = array();
            if(!empty($this->app_config['allow_stock_fantasy']['key_value']) || !empty($this->app_config['allow_equity']['key_value']) || !empty($this->app_config['allow_stock_predict']['key_value']) ||  !empty($this->app_config['allow_live_stock_fantasy']['key_value']) )
            {
               $content = array("type"=>"unsubscribe","topic"=>FCM_TOPIC."stock","ids"=>array($key_detail['device_id']));
            }
            add_data_in_queue($content,'fcm_topic');
        }

        $this->load->helper('queue_helper');
        add_data_in_queue($user_sync_data, 'user_sync');

        $this->api_response_arry['message'] = $this->lang->line('logout_successfully');
        $this->api_response();
    }

    /**
     * send_applink to send app link on mobile
     * @param
     * @return json array
     */
    public function send_applink_post() {
        $this->form_validation->set_rules('phone_no', $this->lang->line("phone_no"), 'trim|required|callback_is_digits|min_length[7]|max_length[13]');

        if($this->app_config['allow_google_captcha']['key_value']){
            $this->form_validation->set_rules('token','token','trim|required');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();
        if($this->app_config['allow_google_captcha']['key_value']){
            $captcha = $this->validate_google_captcha($post_data);
            if(!isset($captcha['success']) || $captcha['success'] != true){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = "Sorry, your request blocked due to suspicious activity.";
                $this->api_response();
            }
        }

        $apk_message = '';
        $apk_template_id = '';
        if(isset($this->app_config['apk_sms']) && !empty($this->app_config['apk_sms'])){
            $apk_data = $this->app_config['apk_sms']['custom_data'];
            if(isset($apk_data['sms_text']) && $apk_data['sms_text'] != ""){
                $apk_message = $apk_data['sms_text'];
            }
            if(isset($apk_data['template_id']) && $apk_data['template_id'] != ""){
                $apk_template_id = $apk_data['template_id'];
            }
        }
        if($apk_message != "" && $apk_template_id != ""){
            $phone_no = $post_data['phone_no'];
            $phone_code = isset($post_data['phone_code']) ? $post_data['phone_code'] : DEFAULT_PHONE_CODE;
            $source_str = (isset($post_data['source_str']) && ($post_data['source_str'] !='null')) ? $post_data['source_str'] : "";
            $short_url = "";
            if(isset($source_str) && $source_str!='')
            {
                $url = $source_str;
                $short_url = $this->get_short_url($url);
                $short_url = "?surl=".$short_url;
            }

            $apk_message = str_replace('{APP_LINK}',ANDROID_APP_PAGE,$apk_message);
            $apk_message = str_replace('{SCODE}',$short_url,$apk_message);
            $sms_data = array();
            $sms_data['otp'] = "";
            $sms_data['template_id'] = $apk_template_id;
            $sms_data['mobile'] = $phone_no;
            $sms_data['phone_code'] = $phone_code;
            $sms_data['message'] = $apk_message;
            $this->load->helper('queue_helper');
            add_data_in_queue($sms_data, 'sms');
        }

        $this->api_response_arry['message'] = $this->lang->line('download_apk_link');
        $this->api_response();
    }

    /**
     * activate_account to activate account
     * @param
     * @return json array
     */
    public function activate_account_post() {
        $post_data = $this->post();
        $this->form_validation->set_rules('key', $this->lang->line('email_activation_key'), 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $this->load->model("auth/Auth_model");
        $key = $post_data['key'];
        $key = base64_decode($key);
        $data = $this->Auth_model->get_new_email_key($key);

        if (!$data) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array('key' => $this->lang->line('invalid_activation_key'));
            $this->api_response();
        }
        if ($data['email_verified'] == 1) {
            $this->api_response_arry['message'] = $this->lang->line('account_already_verified');
            $this->api_response();
        }

        $time = strtotime($data['new_email_requested'] . " +24 hours");
        $current_time = strtotime(format_date());

        if ($time < $current_time) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array('key' => $this->lang->line('invalid_link'));
            $this->api_response();
        }

        $this->db->update(USER, array('status' => '1', 'email_verified' => '1', 'new_email_requested' => NULL, 'new_email_key' => NULL), array('user_id' => $data['user_id']));

        $this->api_response_arry['message'] = $this->lang->line('account_confirm');
        $this->api_response();
    }

    /**
     * social_required to validate social id
     * @param
     * @return json array
     */
    public function social_required($str, $field) {
        $post_data = $this->post();

        if (!empty($post_data['facebook_id']) || !empty($post_data['google_id']) || !empty($post_data['phone_no']) || !empty($post_data['otp']) || !empty($post_data['email'])) {
            return TRUE;
        }

        $this->form_validation->set_message('social_required', $this->lang->line("social_required"));
        return FALSE;
    }

    /**
     * facebook_valid to validate facebook id
     * @param
     * @return json array
     */
    public function facebook_valid() {
        $post_data = $this->post();

        if (empty($post_data['facebook_id'])) {
            return TRUE;
        }

        if (!$post_data['facebook_access_token']) {
            $this->form_validation->set_message('facebook_valid', $this->lang->line("access_token_required"));
            return FALSE;
        }
        $api_facebook_id = $this->facebook_auth();

        if ($post_data['facebook_id'] == $api_facebook_id) {
            return TRUE;
        }

        $message = $this->lang->line("invalid_facebook_id");

        if ($api_facebook_id) {
            $message = $api_facebook_id;
        }
        $this->form_validation->set_message('facebook_valid', $message);
        return FALSE;
    }

    /**
     * google_valid to validate google id
     * @param
     * @return json array
     */
    public function google_valid() {
        $post_data = $this->post();
        if (empty($post_data['google_id'])) {
            return TRUE;
        }

        if (!$post_data['google_access_token']) {
            $this->form_validation->set_message('google_valid', $this->lang->line("access_token_required"));
            return FALSE;
        }
        $api_google_id = $this->google_auth_get();

        if ($post_data['google_id'] == $api_google_id) {
            return TRUE;
        }
        $this->form_validation->set_message('google_valid', $this->lang->line("invalid_google_id"));
        return FALSE;
    }

    /**
     * To check Email Or Username is exist or not
     * @param
     * @return json array
     */
    public function check_email_username() {
        $post_data = $this->post();
        if ($post_data['email'] || $post_data['user_name']) {
            return TRUE;
        } else if ($post_data['facebook_id'] || $post_data['google_id']) {
            return TRUE;
        }
        $this->form_validation->set_message('check_email_username', $this->lang->line("user_name_required"));
        return FALSE;
    }

     /**
     * To update user track record and pass user track id
     * @param
     * @return json array
     */
    public function get_user_track_id_post()
    {
        if(empty(ACTIVE_USER_TRACKING)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $this->form_validation->set_rules('affiliate_reference_id', $this->lang->line("affiliate_reference_id"), 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("auth/Auth_model");
        $insert_data = array(
                    "affiliate_reference_id" => $post_data['affiliate_reference_id'],
                    "landing_date" => format_date()
                );
        $track_id = $this->Auth_model->insert_track_record($insert_data);
        $response = array();
        $response['user_track_id'] = $track_id;

        if($track_id){
            $this->api_response_arry['data'] = $response;
            $this->api_response(); 
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
    }
    
    public function get_short_url($url)
    {
        $data = array(
            "short_id"          =>generateRandomString(6),
            "url"               =>$url,
            "url_type"          =>0,
            "added_date"        =>format_date('today'),
        );

        $this->load->model('Auth_model');
        $result = $this->Auth_model->add_short_url_data($data);
        return $result;
    }

    public function get_source_url_post()
    {
        $this->form_validation->set_rules('surl', "short code", 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }
        $short_code = $this->input->post("surl");
        $this->load->model("auth/Auth_model","am");
        $result = $this->am->get_single_row("*", SHORTENED_URLS, array('short_id' => $short_code));
        $this->api_response_arry['data'] = $result['url'];
        $this->api_response();
    }

    /**
     * App master data used for get banned state list
     * @param
     * @return json array
     */
    public function get_banned_state_post() {
        
        $result = $this->get_banned_state();
        $final_result = array();
        foreach ($result as $key => $value)
        {
            $final_result[$value['state_id']] = $value['name'];
        }
        
        $this->api_response_arry['data'] = $final_result;
        $this->api_response();
    }

    /**
     * function to add visit record at first time
     */
    public function add_visit_post()
    {
        $this->form_validation->set_rules('campaign_code', 'Campaign Code', 'trim|required');
        $this->form_validation->set_rules('visit_code', 'Visit Code', 'trim|required');
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        // $is_visit_code_exist = $this->Affiliate_model->get_single_row('visit_id',VISIT,["visit_code"=>$post_data['visit_code']]); 
        $visit_data = array(
            "visit_code"      =>$post_data['visit_code'],
            "campaign_code"     =>$post_data['campaign_code'],
            "date_added"      =>format_date(),
            "date_modified"   =>format_date(),
            );
        $this->load->helper('queue_helper');
        add_data_in_queue($visit_data, 'af_visit');
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['global_error'] = "Visit added successfully";
        $this->api_response();
    }

    /**
     * Used for get lobby banners list
     * @param int $sports_id
     * @return array
     */
    public function get_lobby_banner_list_post()
    {
        $this->form_validation->set_rules('sports_id', "sports id", 'trim|max_length[2]');//required|is_natural_no_zero
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $lobby_banner_cache = 'lobby_banner_list_'.$sports_id.'_'.$this->lang_abbr;
        $banner_list_data = $this->get_cache_data($lobby_banner_cache);
        if(!$banner_list_data){
            $this->load->model("auth/Auth_model");
            $banner_list = $this->Auth_model->get_lobby_banner_list($post_data);
            $banner_type_ids = array_column($banner_list, "banner_type_id");
            $affiliate_data = array();
            if(in_array("2", $banner_type_ids) || in_array("3", $banner_type_ids)){
                $lobby_banner_aff_cache = 'lobby_banner_referral';
                $affiliate_data = $this->get_cache_data($lobby_banner_aff_cache);
                if(!$affiliate_data){
                    $affiliate_data = $this->Auth_model->get_lobby_banner_referral_data();
                    $this->set_cache_data($lobby_banner_aff_cache,$affiliate_data,REDIS_30_DAYS);
                }
            }
            $collection = array();
            if(in_array("1", $banner_type_ids)){
                $cm_ids = array_column($banner_list, "collection_master_id");
                $cm_ids = array_diff($cm_ids, [0]);
                if(!empty($cm_ids)){
                    $this->load->model("fantasy/Fantasy_model");
                    $collection = $this->Fantasy_model->get_banner_collection($cm_ids);
                    if(!empty($collection)){
                        $collection = array_column($collection,NULL,"collection_master_id");
                    }
                }
            }
            //echo "<pre>";print_r($collection);die;
            $banner_list_data = array();
            foreach($banner_list as $banner){
                $banner['season_game_uid'] = $banner['home'] = $banner['away'] = "";
                if(isset($banner['collection_master_id']) && $banner['collection_master_id'] > 0 && isset($collection[$banner['collection_master_id']])){
                    $collection_info = $collection[$banner['collection_master_id']];
                    $banner['season_game_uid'] = $collection_info['season_game_uid'];
                    $banner['home'] = $collection_info['home'];
                    $banner['away'] = $collection_info['away'];
                }
                $banner['amount'] = '';
                $banner['currency_type'] = '';
                unset($banner['deadline_time']);
                if(in_array($banner['banner_type_id'], array("2","3")) && isset($affiliate_data[$banner['banner_type_id']])){
                    $banner['amount'] = $affiliate_data[$banner['banner_type_id']]['amount'];
                    $banner['currency_type'] = $affiliate_data[$banner['banner_type_id']]['currency_type'];
                    if($banner['amount'] > 0){
                        $banner_list_data[] = $banner;
                    }
                }else{
                    $banner_list_data[] = $banner;
                }
            }

            $this->set_cache_data($lobby_banner_cache,$banner_list_data,REDIS_24_HOUR);
        }

        //for upload app data on s3 bucket
        $this->push_s3_data_in_queue("lobby_banner_list_".$sports_id."_".$this->lang_abbr,$banner_list_data);

        $this->api_response_arry['data'] = $banner_list_data;
        $this->api_response();
    }

    /**
     * Used for get whats new record list
     * @param void
     * @return array
     */
    public function get_whats_new_list_post()
    {
        $post_data = $this->input->post();

        $cache_key = 'whats_new_list';
        $result = $this->get_cache_data($cache_key);
        if(!$result){
            $this->load->model("auth/Auth_model");
            $result = $this->Auth_model->get_all_table_data(WHATS_NEW,"name,description,image",array("status"=>"1"),array("id"=>"ASC"));
            $this->set_cache_data($cache_key,$result,REDIS_30_DAYS);
        }

        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

}
