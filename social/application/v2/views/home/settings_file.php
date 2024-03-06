<?php

$settingsArr = array(
    'base_url' => base_url(),
    'TimeZone' => get_user_time_zone($this->session->userdata('UserID')),
    'profile_picture' => base_url(),
    'login_user_name' => '',
    'time_zone_offset' => 0,
    'LoginSessionKey' => '',
    'TomorrowDate' => '',
    'NextWeekDate' => '',
    'DisplayTomorrowDate' => '',
    'DisplayNextWeekDate' => '',
    'app_version' => version_control(TRUE),
    'IsAdminView' => 0,
    'accept_language' => $this->config->item("language"),
    'image_server_path' => IMAGE_SERVER_PATH,
    'site_name' => SITE_NAME,
    'ENVIRONMENT' => ENVIRONMENT,
    'Auth_Key' => AUTH_KEY,
    'Custom_Headers' => [AUTH_KEY => $this->session->userdata('LoginSessionKey'), 'Accept-Language' => $this->config->item("language")],
    'NodeAddr' => NODE_ADDR,
    'AssetBaseUrl' => ASSET_BASE_URL,
    'IsFileTab' => (isset($_GET['files'])) ? 1 : 0,
    'LoggedInUserGUID' => $this->session->userdata('UserGUID'),
    'LoggedInFirstName' => $this->session->userdata('FirstName'),
    'LoggedInLastName' => $this->session->userdata('LastName'),
    'LoggedInPicture' => $this->session->userdata('ProfilePicture'),
    'superAdminID' => $this->session->userdata('superAdminID'),
    'LoggedInUserID' => $this->session->userdata('UserID'),
    'visibleBaner' => (isset($CoverImageState) && $CoverImageState != 1) ? false : true,
    'settings_data' => $this->moduleSettings,
    'privacy_change_timelimit' => PRIVACY_CHANGE_TIMELIMIT * 60,
    'UserGUID' => $this->session->userdata('UserGUID'),
    'UserID' => $this->session->userdata('UserID'),
    'FacebookAppId' => defined('FACEBOOK_APP_ID') ? FACEBOOK_APP_ID : null,
    'site_url' => site_url(),
    'google_client_id' => defined('CLIENT_ID') ? CLIENT_ID : null,
    'google_scope' => defined('SCOPE') ? SCOPE : null,
    'google_api_key' => defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : null,
    'Dragging' => false,
    'siteUrl' => site_url(),
    'user_url' => ''
);





    $global_settings = $this->config->item('global_settings');
if ($this->session->userdata('LoginSessionKey') != '') {
    $reminder_time = $global_settings['reminder_default_time'];
    $tomorrow = date("Y-m-d " . ltrim($reminder_time, 0) . ":00 A", strtotime('tomorrow'));
    $display_tomorrow = date("D, " . $reminder_time . " A", strtotime('tomorrow'));

    $next_monday = date("Y-m-d " . ltrim($reminder_time, 0) . ":00 A", strtotime('next monday'));
    $display_next_monday = date("D, " . $reminder_time . " A", strtotime('next monday'));

    $settingsArr['user_url'] = get_entity_url($this->session->userdata("UserID"));
    $settingsArr['profile_picture'] = $this->session->userdata("ProfilePicture");
    $settingsArr['login_user_name'] = $this->session->userdata("FirstName") . ' ' . $this->session->userdata("LastName");
    $settingsArr['time_zone_offset'] = ($this->session->userdata("TimeZoneOffset") / 60) * -1;
    $settingsArr['LoginSessionKey'] = $this->session->userdata('LoginSessionKey');
    $settingsArr['TomorrowDate'] = $tomorrow;

    $settingsArr['NextWeekDate'] = $next_monday;
    $settingsArr['DisplayTomorrowDate'] = $display_tomorrow;
    $settingsArr['DisplayNextWeekDate'] = $display_next_monday;
}
    $settingsArr['ShowPostType'] = isset($global_settings['post_type']) ? $global_settings['post_type'] : '';

$outputJs = '';

if (empty($this->isJSRequest)) {
    $outputJs = json_encode($settingsArr);
} else {
    
    
    $settingsArr['settings_data'] = json_decode($settingsArr['settings_data']);
    
   $outputJs = json_encode($settingsArr);
    
    
   $outputJs =  "     
      
        
      
      var userSettingsObj = $outputJs;
      
      for(var objKey in userSettingsObj) {          
          
          window[objKey] = userSettingsObj[objKey];
      }      
    ";

}

echo $outputJs;

?>
