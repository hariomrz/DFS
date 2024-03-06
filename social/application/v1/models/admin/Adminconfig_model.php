<?php  
class adminconfig_model extends Admin_Common_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function setConfigurationSetting(){
        
        $this->load->driver('cache');
        if(!($GlobalSettings = $this->cache->file->get(GLOBALSETTINGS))){
            $ConfigData = array();
            $this->load->model(array('admin/configuration_model'));

            $configurationData = $this->configuration_model->getAdminConfigurationSettings();

            foreach($configurationData as $config){
                $ConfigData[$config['ConfigurationName']] = $config['ConfigValue'];
                if(in_array($config['ConfigurationName'],array('FacebookURL','TwitterURL','LinkedinURL','GooglePlusURL')))
                {
                    if(!$config['IsActive'])
                    {
                        $ConfigData[$config['ConfigurationName']] = '';
                    }
                }
            }

            $PageSetting = simplexml_load_string($ConfigData['PageSetting']);
            $page_size = (int)$PageSetting->Pagination->PageSize;
            $PaginationLinks = (int)$PageSetting->Pagination->PaginationLinks;

            $Header = json_decode( json_encode(simplexml_load_string($ConfigData['Header'])),1);
            $Footer = json_decode( json_encode(simplexml_load_string($ConfigData['Footer'])),1);
            $SocialMedia = json_decode( json_encode(simplexml_load_string($ConfigData['SocialMedia'])),1);
            $TopNavigationDate = json_decode( json_encode(simplexml_load_string($ConfigData['TopNavigationDate'])),1);
            $Navigation = json_decode( json_encode(simplexml_load_string($ConfigData['Navigation'])),1);   

            //For top navigation date options
            $TopNavigationDateArr = array();
            $i = 1;
            foreach($TopNavigationDate['TopNavigationDate'] as $topnavdata){
                $TopNavigationDateArr[$i] = array('name' => $topnavdata['Name'], 'parameters' => $topnavdata['Parameters'], 'visible' => $topnavdata['Visible']);
                $i++;
            }
            
            
            $checkDisabledSubMenus = [
                "newsletter_subscriber" => 35,
                
                "group_permission" => 1,
                "list_group" => 1,
                                
                "pageflag" => 18,
                "list_pages" => 18,
                
                "event_tool" => 14,
                
            ];
            
            
            //For navigation array
            $NavigationArr = array();
            foreach($Navigation['Navigation'] as $navigation){
                if(isset($navigation['MenuKey']))$MenuKey = $navigation['MenuKey']; else $MenuKey = '';
                
                $cildrenArr = [];
                $navData = array('name' => $navigation['Name'], 'url' => ADMIN_BASE_URL.$navigation['Url'],'menu_id' => $navigation['MenuId'], 'menu_key' => $MenuKey);                
                if(isset($navigation['SubNavigation'])){
                    $navData['icon'] = '<i class="ficon-arrow-down"></i>'; 
                    foreach($navigation['SubNavigation']['Navigation'] as $subnavigation){
                        if(!empty($subnavigation)){
                            if(isset($subnavigation['MenuKey']))$SubNavMenuKey = $subnavigation['MenuKey']; else $SubNavMenuKey = '';
                            
                            //$MenuKey== "UsersTab" && 
                            if (!empty($checkDisabledSubMenus[$SubNavMenuKey])  && $this->settings_model->isDisabled($checkDisabledSubMenus[$SubNavMenuKey])) {
                                continue;
                            }
                            $cildrenArr = array('name' => $subnavigation['Name'], 'url' => ADMIN_BASE_URL.$subnavigation['Url'],'menu_id' => $subnavigation['MenuId'], 'menu_key' => $SubNavMenuKey);
                            if(isset($subnavigation['SubNavigation'])){
                                $cildrenArr['icon'] = '<i class="icon-rightarrow"></i>';
                                foreach($subnavigation['SubNavigation']['Navigation'] as $subnavigation1){
                                    if(!empty($subnavigation1)){
                                        if(isset($subnavigation1['MenuKey']))$SubNavMenuKey1 = $subnavigation1['MenuKey']; else $SubNavMenuKey1 = '';
                                        $cildrenArr['children'][] = array('name' => $subnavigation1['Name'], 'url' => ADMIN_BASE_URL.$subnavigation1['Url'],'menu_id' => $subnavigation1['MenuId'], 'menu_key' => $SubNavMenuKey1);
                                    }
                                }
                            }
                            $navData['children'][] = $cildrenArr;
                        }
                    }

                }
                
                $remove_menus_without_childrens = ["PageTab"];
                
                if(in_array($MenuKey, $remove_menus_without_childrens) && empty($cildrenArr)) {
                    continue;
                }
                
                
                $NavigationArr[] = $navData;
            }

            //Set Global Config as an array and Asign it to system's config variable
            $global_setting_array = array();

            //Global Config for date_format
            $global_setting_array['date_format'] = $ConfigData['DateFormat'];

            //Global Config for time_format
            $global_setting_array['time_format'] = $ConfigData['TimeFormat'];

            //Global Config for week_start_on
            $global_setting_array['week_start_on'] = $ConfigData['WeekStartOn'];        

            //Global Config for auto_logout_time in minutes
            $global_setting_array['auto_logout_time'] = $ConfigData['AutoLogOutTime'];
            
            //Global Config for auto logout Enable or Disable
            $global_setting_array['auto_logout'] = $ConfigData['AutoLogOut'];

            //Global Config for culture_info
            $global_setting_array['culture_info'] = $ConfigData['CultureInfo'];

            //Global Config for Currency_format
            if(isset($ConfigData['CurrencyFormat'])){
                $global_setting_array['Currency_format'] = $ConfigData['CurrencyFormat'];
            }else{
                $global_setting_array['Currency_format'] = "C";
            }

            //Global Config for decimal_places
            $global_setting_array['decimal_places'] = $ConfigData['DecimalPlaces'];

            //Global Config for global_email_sending
            $global_setting_array['global_email_sending'] = $ConfigData['GlobalEmailSending'];

            //Global Config for number_format
            if(isset($ConfigData['NumberFormat'])){
                $global_setting_array['number_format'] = $ConfigData['NumberFormat'];
            }else{
                $global_setting_array['number_format'] = "";
            }

            //Global Config for percent_format
            if(isset($ConfigData['PercentFormat'])){
                $global_setting_array['percent_format'] = $ConfigData['PercentFormat'];
            }else{
                $global_setting_array['percent_format'] = "";
            }

            //Global Config for send_mail_via_mandrill
            $global_setting_array['send_mail_via_mandrill'] = $ConfigData['SendMailViaMandrill'];
            
            //Mandrill API Key and other details
            $global_setting_array['mandrill_api_key'] = $ConfigData['ManDrillAPIKey'];
            $global_setting_array['mandrill_from_email'] = $ConfigData['ManDrillFromEmail'];
            $global_setting_array['mandrill_from_name'] = $ConfigData['ManDrillFromName'];

            //Global Config for site_down_for_maintenance
            $global_setting_array['site_down_for_maintenance'] = $ConfigData['SiteDownForMaintenance'];
            
            //Global Config for date_format
            $global_setting_array['beta_invite_enabled'] = $ConfigData['BetaInviteEnabled'];

            //Global Config for page_setting
            $global_setting_array['page_setting'] = array('pagination'=>array('page_size' => $page_size),
                                                          'pagination_links'=>array('links'=> $PaginationLinks));
            
             //Reminder default time
            $global_setting_array['reminder_default_time'] = $ConfigData['ReminderTime'];

            //Post type
            $global_setting_array['post_type'] = $ConfigData['ShowPostType'];

            //Global Config for Header
            if(isset($Header['Logo'])) $HeaderLogo = $Header['Logo']; else $HeaderLogo = '';
            if(isset($Header['Name'])) $HeaderName = $Header['Name']; else $HeaderName = '';
            if(isset($Header['Title'])) $HeaderTitle = $Header['Title']; else $HeaderTitle = '';
            if(isset($Header['Favicon'])) $HeaderFavicon = $Header['Favicon']; else $HeaderFavicon = '';            
            $global_setting_array['header'] = array(
                                                        'logo' => str_replace("{BASE_URL}", base_url(),$HeaderLogo),
                                                        'name' => $HeaderName,
                                                        'title' => $HeaderTitle,
                                                        'favicon' => str_replace("{BASE_URL}", base_url(),$HeaderFavicon),
                                                    );

            //Global Config for Footer
            if(isset($Footer['Logo'])) $FooterLogo = $Footer['Logo']; else $FooterLogo = '';
            if(isset($Footer['CopyRight'])) $CopyRight = $Footer['CopyRight']; else $CopyRight = ''; 
            $CopyRight = str_replace('{SITE_NAME}',SITE_NAME,$CopyRight);           
            $global_setting_array['footer'] = array(
                                                        'copyright' => str_replace('{Y}',date('Y'),$CopyRight),
                                                        'logo' => str_replace("{BASE_URL}", base_url(),$FooterLogo),
                                                    );


            //Global Config for SocialMedia
            if(isset($SocialMedia['Facebook'])) $Facebook = $SocialMedia['Facebook']; else $Facebook = '';
            if(isset($SocialMedia['Twitter'])) $Twitter = $SocialMedia['Twitter']; else $Twitter = '';
            if(isset($SocialMedia['GooglePlus'])) $GooglePlus = $SocialMedia['GooglePlus']; else $GooglePlus = '';
            if(isset($SocialMedia['LinkedIn'])) $LinkedIn = $SocialMedia['LinkedIn']; else $LinkedIn = '';
            $global_setting_array['social_media']= array(
                                                            'facebook' => '<a href="'.$Facebook.'" target="_blank">FACEBOOK/</a>',
                                                            'twitter' => '<a href="'.$Twitter.'" target="_blank"> TWITTER/</a>',
                                                            'googleplus' => '<a href="'.$GooglePlus.'" target="_blank"> GOOGLE PLUS/</a>',
                                                            'linkedin' => '<a href="'.$LinkedIn.'" target="_blank"> LINKEDIN</a>',
                                                        );

            //Global Config for Navigations
            $global_setting_array['navigations'] = $NavigationArr;

            //Global Config for top_navigation_date
            $global_setting_array['top_navigation_date'] = $TopNavigationDateArr;

            //Global Config for facebook_url
            $global_setting_array['facebook_url'] = isset($ConfigData['FacebookURL']) ? $ConfigData['FacebookURL'] : "";
            //Global Config for twitter_url
            $global_setting_array['twitter_url'] = isset($ConfigData['TwitterURL']) ? $ConfigData['TwitterURL'] : "";
            //Global Config for linkedin_url
            $global_setting_array['linkedin_url'] = isset($ConfigData['LinkedinURL']) ? $ConfigData['LinkedinURL'] : "";
            //Global Config for google_plus_url
            $global_setting_array['google_plus_url'] = isset($ConfigData['GooglePlusURL']) ? $ConfigData['GooglePlusURL'] : ""; 
            
            
            $global_setting_array['NoOfPost'] = isset($ConfigData['NoOfPost']) ? $ConfigData['NoOfPost'] : 0; 
            $global_setting_array['NoOfFriends'] = isset($ConfigData['NoOfFriends']) ? $ConfigData['NoOfFriends'] : 0; 
            
            if(!empty($global_setting_array)){
                $this->cache->file->save(GLOBALSETTINGS,$global_setting_array, 31536000);
                $GlobalSettings = $this->cache->file->get(GLOBALSETTINGS);
            }    
            
        }
        return $GlobalSettings;
        
    }
    
    /**
     * Function for get allowes ip list
     * Parameters : $IpFor(1 = Admin, 0 = User)
     * Return : ip array
     */
    public function setAllowedIpsList() {
        $this->load->driver('cache');
        if(!($IpSettings = $this->cache->file->get('IpSettings'))){
            $this->db->select('A.IP AS ip', FALSE);
            $this->db->select('A.IsForAdmin AS IsForAdmin', FALSE);
            $this->db->from(ALLOWEDIPS . " as A ");
            $this->db->where('A.StatusID ',2);
            $query = $this->db->get();
            $results = $query->result_array();
            
            $ipsArr = array();
            $ipsArr['AdminIps'] = array();
            $ipsArr['UserIps'] = array();
            foreach($results as $ip){
                if($ip['IsForAdmin'] == 1){
                    $ipsArr['AdminIps'][] = $ip['ip'];
                }else{
                    $ipsArr['UserIps'][] = $ip['ip'];
                }                
            }    
            
            if(!empty($ipsArr)){
                $this->cache->file->save('IpSettings',$ipsArr, 31536000);
                $IpSettings = $this->cache->file->get('IpSettings');
            }
        }
        
        return $IpSettings;        
    }
    
    /**
     * Function for get smtp details
     * Parameters : void
     * Return : smtp setting array
     */
    public function setSmtpSettingDetails() {
        
        $this->load->driver('cache');
        if(!($SmtpSettings = $this->cache->file->get('SmtpSettings'))){
            $smtpArr = array();
            
            //For Default Setting
            $this->db->select('ES.FromEmail AS FromEmail', FALSE);
            $this->db->select('ES.FromName AS FromName', FALSE);
            $this->db->select('ES.ServerName AS ServerName', FALSE);
            $this->db->select('ES.SPortNo AS SPortNo', FALSE);
            $this->db->select('ES.UserName AS UserName', FALSE);
            $this->db->select('ES.Password AS Password', FALSE);
            $this->db->select('ES.ReplyTo AS ReplyTo', FALSE);
            $this->db->select('ES.StatusID AS SmtpStatusID', FALSE);

            $this->db->from(EMAILSETTINGS . "  ES ");
            
            $default_query = $this->db->get();
            $defaultSmtp = $default_query->row_array();
            
            $smtpArr['default'] = array(
                                'EmailType' => "Default SMTP",
                                'FromEmail' => $defaultSmtp['FromEmail'],
                                'FromName' => $defaultSmtp['FromName'],
                                'ServerName' => $defaultSmtp['ServerName'],
                                'SPortNo' => $defaultSmtp['SPortNo'],
                                'UserName' => $defaultSmtp['UserName'],
                                'Password' => $defaultSmtp['Password'],
                                'ReplyTo' => $defaultSmtp['ReplyTo'],
                                'SmtpStatusID' => $defaultSmtp['SmtpStatusID']
                            );            
            
            $this->db->select('ET.EmailTypeID AS EmailTypeID', FALSE);
            $this->db->select('ET.Name AS EmailType', FALSE);
            $this->db->select('ES.FromEmail AS FromEmail', FALSE);
            $this->db->select('ES.FromName AS FromName', FALSE);
            $this->db->select('ES.ServerName AS ServerName', FALSE);
            $this->db->select('ES.SPortNo AS SPortNo', FALSE);
            $this->db->select('ES.UserName AS UserName', FALSE);
            $this->db->select('ES.Password AS Password', FALSE);
            $this->db->select('ES.ReplyTo AS ReplyTo', FALSE);
            $this->db->select('ES.StatusID AS SmtpStatusID', FALSE);

            $this->db->join(EMAILSETTINGS . " AS ES", ' ES.EmailSettingID = ET.EmailSettingID', 'inner');
            $this->db->from(EMAILTYPES . "  ET ");
            $this->db->where('ET.StatusID',2);

            $query = $this->db->get();
            //echo $this->db->last_query();die;
            $results = $query->result_array();

            foreach($results as $smtp){
                $smtpArr[$smtp['EmailTypeID']] = array(
                                                        'EmailType' => $smtp['EmailType'],
                                                        'FromEmail' => $smtp['FromEmail'],
                                                        'FromName' => $smtp['FromName'],
                                                        'ServerName' => $smtp['ServerName'],
                                                        'SPortNo' => $smtp['SPortNo'],
                                                        'UserName' => $smtp['UserName'],
                                                        'Password' => $smtp['Password'],
                                                        'ReplyTo' => $smtp['ReplyTo'],
                                                        'SmtpStatusID' => $smtp['SmtpStatusID']
                                                    );

            }
            if(!empty($smtpArr)){
                $this->cache->file->save('SmtpSettings',$smtpArr, 31536000);
                $SmtpSettings = $this->cache->file->get('SmtpSettings');
            }
        }
        return $SmtpSettings;
    }
    
}
