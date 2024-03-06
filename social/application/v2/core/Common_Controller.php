<?php
class Common_Controller extends CI_Controller {

    public $section = '';
    public $login_session_key = '';
    public $first_name = '';
    public $last_name = '';
    public $DeviceTypeID = 1;
    public $IsApp = 0;

    function __construct() {
        parent::__construct();
        
        //Load Memcached
        if(CACHE_ENABLE)
        {
            $this->load->driver('cache', array('adapter' => CACHE_ADAPTER, 'backup' => 'file'));
        }
        $this->data['IsGroup']  = 0;
        $this->data['IsPage']   = 0;
        $this->data['IsEvent']  = 0;
        $this->data['IsAdmin']  = 0;
        $this->data['IsRating']  = 0;
        $this->data['IsMedia'] = 0;
        $this->data['IsLoggedIn'] = false;
                      
        $this->load->model(array(
            'users/login_model', 'users/user_model', 'admin/adminconfig_model',
            'admin/analytics_model','admin/betainvite_model','forum/forum_model',
            'settings_model'
        ));
       
        $this->configData = $configData = $this->adminconfig_model->setConfigurationSetting();
        $this->config->set_item('global_settings', $configData);

        $LoggedUserID = $this->session->userdata('UserID');

        //For redirect to maintenance page when site down for maintenance
        if($configData['site_down_for_maintenance'] == 1){
            redirect('site_maintenance');
        }        
        
        //For get Beta Invite key and validate
        $BetaInviteGUID = $this->input->get('BetaInviteKey', TRUE);        
        if(isset($BetaInviteGUID) && $BetaInviteGUID!=""){
            $verify = $this->betainvite_model->verifyBetaInvitationGuId($BetaInviteGUID);
            if($verify["result"] == "valid"){
                
                //For save beta invite log
                if(!$this->session->userdata('BetaInviteGUID') && $this->session->userdata('BetaInviteGUID') ==""){
                    $sid =  $this->session->userdata('session_id');
                    $InviteLogArr['BetaInviteID'] = $verify['BetaInviteID'];
                    $InviteLogArr['SessionID'] = $sid;
                    $InviteLogArr['IPAddress'] = getRealIpAddr();
                    $InviteLogArr['IsAccessByCode'] = '0';
                    $InviteLogArr['CreatedDate'] = date('Y-m-d H:i:s');

                    if($InviteLogArr['IPAddress'] == '127.0.0.1')
                        $InviteLogArr['IPAddress'] = DEFAULT_IP_ADDRESS;

                    if ($InviteLogArr['IPAddress'] != '')
                    {                        
                        $this->load->helper('location');
                        $locationData = get_ip_location_details($InviteLogArr['IPAddress']);
                    }

                    if ($locationData['statusCode'] == "OK") {
                        $InviteLogArr['Location'] = $locationData['CityName'].' '.$locationData['StateName'].', '.$locationData['CountryName'];
                    } else {
                        $InviteLogArr['Location'] = '';
                    }                

                    $this->betainvite_model->saveBetaInviteLogs($InviteLogArr);
                }
                
                $this->session->set_userdata('IsBetaVerify', "1");
                $this->session->set_userdata('BetaInviteGUID', $BetaInviteGUID);
                
            }else if($verify["result"] == "invalid"){
                $this->session->unset_userdata('IsBetaVerify');
                $this->session->unset_userdata('BetaInviteGUID');
            }
        }
        
        $IsBetaVerify = $this->session->userdata('IsBetaVerify');
        
        //For redirect to betainvite page when site only for beta testing users
        if(!$IsBetaVerify && $configData['beta_invite_enabled'] == 1){
            redirect('betainvite');
        }
        
        //For set smtp setting details
        $smtpData = $this->adminconfig_model->setSmtpSettingDetails();
        $this->config->set_item('smtp_settings', $smtpData);
        
        //For allowed/blocked ips list
        $IpSetting = $this->adminconfig_model->setAllowedIpsList();
        if($_SERVER['REQUEST_URI']!= '/cron/zencoder_notification' && !in_array(getClientIP(), $IpSetting['UserIps']) && !in_array(GLOBAL_IP, $IpSetting['UserIps'])){
            redirect('blockedip');
        }
        
        //For set google analytics code for frontend
        $analyticData = $this->analytics_model->getAnalyticsProviders();
        $analyticArr = array();
        foreach($analyticData as $analytic){
            $analyticArr[] = $analytic['AnalyticsData'];
        }
        if(is_array($analyticArr)){
            $this->config->set_item('AnalyticsCode', $analyticArr);
        }
        
        $this->moduleSettings = $this->settings_model->getModuleSettings();
        
        $language = $this->session->userdata('language');
        if($language != $this->config->item('language') && !empty($language)) {
            $this->config->set_item('language', $language);
            $loaded = $this->lang->is_loaded;
            $this->lang->is_loaded = array();
            foreach($loaded as $key=>$val) {
                $this->load->language(str_replace("_lang.php","",$key), $language);
            }
        }//End language code

        //echo "language ==  ".$this->config->item('language');

        $this->layout = $this->config->item("default_layout");
        $this->section = 'dashboard';
        $this->data['wall_url'] = $this->data['profile_url'] = base_url();
        $currentClass = $this->router->fetch_class();
        $currentMethod = $this->router->fetch_method();
        
        if($currentClass != 'settings' && $currentClass != 'cron' && $currentClass != 'home') {
            redirect('admin');
        }
        
        $this->login_session_key = $this->session->LoginSessionKey;
        $this->first_name = $this->session->FirstName;
        $this->last_name = $this->session->LastName;
        $this->LoggedInGUID = $this->session->UserGUID;

        if (empty($this->login_session_key)) {
            //$this->login_model->check_remember_me();
            
            /* $cookie = array(
                        'name'   => 'dische',
                        'value'  => '1',
                        'expire' => '600'  // 90 days expiration time
                        );
            $this->input->set_cookie($cookie);
             * 
             */
        
            $classes = array('myaccount', 'user_profile', 'messages', 'pages', 'events', 'group', 'search','notification', 'forum', 'community','signup','StaticPage','staticpages');
            
            if(in_array($currentClass, $classes) && $currentMethod!='LogIn') 
            { 
                $this->load->helper('url');
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $rb_url = $_SERVER['HTTP_REFERER'];
                } else {
                    $rb_url = current_url();
                }
                // die(current_url());
                if (strpos($rb_url, '?') !== false) {
                    $rb_url = explode('?', $rb_url);
                    if($rb_url && count($rb_url)==2)
                    {
                        $rb_url = $rb_url[0];
                    }
                }
                $current_url = @explode('.', $rb_url);
                $current_url = @end($current_url);
                //print_r($current_url);
                if ($current_url != 'css' && $current_url != 'js' && $current_url != 'map' && $rb_url != site_url() . 'signin' && $rb_url != site_url()) {
                    $this->session->set_userdata('redirect_back', $rb_url);
                } else {
                    $this->session->set_userdata('redirect_back', site_url());
                }
            }

            if(!($currentClass == 'settings' || $currentClass == 'sitemap' || $currentClass == 'StaticPage' || $currentClass == 'confirm' || $currentClass == 'cron' || $currentClass == 'home' || $currentClass == 'signup' || ($currentClass == 'user_profile' && !isset($_GET['files']) && !isset($_GET['links'])) || ($currentClass == 'group' && ($currentMethod=='wall' || $currentMethod=='index' || $currentMethod=='members' || $currentMethod=='media' || $currentMethod=='event' || $currentMethod=='files' || $currentMethod=='links')) || ($currentClass == 'community' && ($currentMethod=='index' || $currentMethod=='media')) || ($currentClass == 'forum' && ($currentMethod=='index' || $currentMethod=='media')) || ($currentClass == 'pages' && $currentMethod=='pageDetails') || ($currentClass == 'events' && ($currentMethod=='wall' || $currentMethod=='index' || $currentMethod=='about' || $currentMethod=='media' || $currentMethod=='members'))))
            {
                if(!$this->login_model->is_user_profile($currentClass))
                {
                    if($currentMethod!='feeds')
                    {
                        redirect(site_url());
                    }
                }
            }
        } else {
            //delete_cookie('dische');
            $this->data['IsLoggedIn'] = true;            
            if($this->login_model->is_password_changed($this->login_session_key)){				
                    if($currentClass!='signup' && $currentClass!='myaccount'){
                        redirect(site_url('myaccount'));
                    }
            }
            
            //$isProfileCompleted = $this->user_model->isProfileCompleted($this->session->userdata('UserID'));
            if(!$this->login_model->is_user_activated($this->login_session_key)){
                if($currentClass!='signup'){
                    $this->login_model->sign_out($this->login_session_key);
                    redirect(site_url('signin'));
                }
            }
            $this->data['wall_url'] = $this->data['profile_url'] = get_entity_url($this->session->userdata('UserID'));
            $this->data['forum_count'] = $this->forum_model->forum_count();           
            
	}
  }
}
